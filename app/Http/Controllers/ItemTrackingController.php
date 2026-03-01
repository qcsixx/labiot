<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemTrackingRequest;
use App\Http\Resources\ItemTrackingResource;
use App\Models\BorrowRequest;
use App\Models\ItemTracking;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ItemTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $trackings = ItemTracking::with(['reportedBy', 'borrowRequest.item'])
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $trackings
            ]);
        }

        return view('admin.pelacakan-barang', compact('trackings'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemTrackingRequest $request): JsonResponse
    {
        // Check if the user is authorized to create tracking
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menambah pelacakan',
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            $tracking = new ItemTracking();
            $tracking->borrow_request_id = $request->borrow_request_id;
            $tracking->location = $request->location;
            $tracking->notes = $request->notes;
            $tracking->tracked_by = Auth::id();
            $tracking->tracking_date = now();
            
            // Handle photo upload if provided
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                
                // Verify the file is actually an image by checking mime type
                $mime = $photo->getMimeType();
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'])) {
                    throw new \Exception('File bukan gambar yang valid');
                }
                
                // Sanitize filename to prevent path traversal
                $originalName = $photo->getClientOriginalName();
                $sanitizedName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $extension = $photo->getClientOriginalExtension();
                $filename = time() . '_' . $sanitizedName . '.' . $extension;
                
                // Limit filesize
                if ($photo->getSize() > 2048000) { // 2MB in bytes
                    throw new \Exception('Ukuran foto melebihi batas maksimum 2MB');
                }
                
                // Store the file in the correct location
                $path = $photo->storeAs('item_tracking', $filename, 'public');
                if (!$path) {
                    throw new \Exception('Gagal menyimpan foto');
                }
                
                $tracking->photo = $path;
            }
            
            $tracking->save();
            
            // Update borrow request status if needed
            if ($request->has('status')) {
                $borrowRequest = BorrowRequest::findOrFail($request->borrow_request_id);
                $borrowRequest->status = $request->status;
                $borrowRequest->save();
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Pelacakan berhasil ditambahkan',
                'data' => new ItemTrackingResource($tracking),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambah pelacakan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemTracking $itemTracking): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $itemTracking
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemTracking $tracking)
    {
        try {
            // Hapus foto dari storage
            if ($tracking->photo && Storage::exists('public/tracking_photos/' . $tracking->photo)) {
                Storage::delete('public/tracking_photos/' . $tracking->photo);
            }

            $tracking->delete();

            // Log aktivitas
            Log::info('Tracking deleted', [
                'tracking_id' => $tracking->tracking_id,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('success', 'Data tracking berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting tracking: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }

    /**
     * Verify tracking location and update status
     */
    public function verify(Request $request, ItemTracking $tracking)
    {
        try {
            if (!Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk verifikasi'
                ], 403);
            }

            $request->validate([
                'action' => 'required|in:approve,reject',
                'rejection_reason' => 'required_if:action,reject|string|max:1000'
            ]);

            if ($request->action === 'approve') {
                $tracking->update([
                    'verified' => true,
                    'verification_status' => 'approved',
                    'verified_by' => Auth::id(),
                    'verified_at' => now()
                ]);

                // Update status peminjaman menjadi completed
                $tracking->borrowRequest->update([
                    'status' => 'completed',
                    'return_date' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Verifikasi pengembalian berhasil disetujui'
                ]);
            } else {
                $tracking->update([
                    'verified' => false,
                    'verification_status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                    'verified_by' => Auth::id(),
                    'verified_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Verifikasi pengembalian ditolak'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error verifying tracking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui status verifikasi'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemTrackingRequest $request, ItemTracking $itemTracking): JsonResponse
    {
        // Check if the user is authorized to update tracking
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengubah pelacakan',
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            // Update tracking data
            $itemTracking->location = $request->location;
            $itemTracking->notes = $request->notes;
            
            // Handle photo upload if provided
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($itemTracking->photo && Storage::disk('public')->exists($itemTracking->photo)) {
                    Storage::disk('public')->delete($itemTracking->photo);
                }
                
                $photo = $request->file('photo');
                
                // Verify the file is actually an image by checking mime type
                $mime = $photo->getMimeType();
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'])) {
                    throw new \Exception('File bukan gambar yang valid');
                }
                
                // Sanitize filename to prevent path traversal
                $originalName = $photo->getClientOriginalName();
                $sanitizedName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $extension = $photo->getClientOriginalExtension();
                $filename = time() . '_' . $sanitizedName . '.' . $extension;
                
                // Limit filesize
                if ($photo->getSize() > 2048000) { // 2MB in bytes
                    throw new \Exception('Ukuran foto melebihi batas maksimum 2MB');
                }
                
                // Store the file
                $path = $photo->storeAs('item_tracking', $filename, 'public');
                if (!$path) {
                    throw new \Exception('Gagal menyimpan foto');
                }
                
                $itemTracking->photo = $path;
            }
            
            $itemTracking->save();
            
            // Update borrow request status if needed
            if ($request->has('status')) {
                $borrowRequest = BorrowRequest::findOrFail($itemTracking->borrow_request_id);
                $borrowRequest->status = $request->status;
                $borrowRequest->save();
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Pelacakan berhasil diperbarui',
                'data' => new ItemTrackingResource($itemTracking),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengubah pelacakan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
