<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BorrowRequest;
use App\Models\ItemTracking;
use App\Models\Notification;
use App\Enums\BorrowStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ItemTrackingUserController extends Controller
{
    /**
     * Menyimpan data tracking
     */
    public function store(Request $request, $requestId)
    {
        try {
            // Validasi dasar
            $request->validate([
                'location' => 'required|string|max:255',
                'latitude' => 'nullable|string',  // Tambahkan validasi untuk koordinat
                'longitude' => 'nullable|string', // Tambahkan validasi untuk koordinat
                'notes' => 'nullable|string',
                'photo' => 'required|file|max:2048|mimetypes:image/*',
            ]);
            
            DB::beginTransaction();
            
            // Verifikasi bahwa peminjaman ini milik user yang login
            try {
                $borrowRequest = BorrowRequest::where('user_id', Auth::id())
                    ->where('request_id', $requestId)
                    ->whereIn('status', [BorrowStatus::BORROWED, BorrowStatus::OVERDUE])
                    ->first();
                    
                if (!$borrowRequest) {
                    // Coba dengan status APPROVED tapi sudah dipinjam
                    $borrowRequest = BorrowRequest::where('user_id', Auth::id())
                        ->where('request_id', $requestId)
                        ->where('status', BorrowStatus::APPROVED)
                        ->whereNotNull('borrowed_at')
                        ->first();
                        
                    if (!$borrowRequest) {
                        throw new \Exception("Peminjaman tidak ditemukan atau belum dalam status dipinjam");
                    }
                }
            } catch (\Exception $e) {
                throw $e;
            }
            
            // Periksa jumlah tracking hari ini (maksimal 5)
            $today = Carbon::today();
            $trackingCount = ItemTracking::where('borrow_request_id', $requestId)
                ->whereDate('tracking_date', $today)
                ->count();
                
            if ($trackingCount >= 5) {
                throw new \Exception("Anda hanya dapat melakukan maksimal 5 tracking per hari");
            }
            
            // Upload foto
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                
                if ($file->isValid()) {
                    $fileName = 'tracking_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $photoPath = $file->storeAs('item_tracking', $fileName, 'public');
                } else {
                    throw new \Exception("File tidak valid");
                }
            } else {
                throw new \Exception("Foto wajib diunggah");
            }
            
            // Simpan data ke database
            $tracking = new ItemTracking();
            $tracking->borrow_request_id = $requestId;
            $tracking->location = $request->input('location');
            $tracking->latitude = $request->input('latitude');  // Simpan latitude
            $tracking->longitude = $request->input('longitude'); // Simpan longitude
            $tracking->notes = $request->input('notes');
            $tracking->photo = $photoPath;
            $tracking->tracked_by = Auth::id();
            $tracking->tracking_date = now();
            
            $tracking->save();
            
            // Buat notifikasi untuk admin
            Notification::create([
                'user_id' => 1, // Kirim ke admin
                'status' => $borrowRequest->status->value,
                'request_id' => $requestId,
                'message' => "User " . Auth::user()->name . " telah mengupload pelacakan untuk " . $borrowRequest->item->name,
            ]);
            
            DB::commit();
            
            // Return response sesuai tipe request
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pelacakan berhasil disimpan',
                    'tracking_id' => $tracking->tracking_id
                ]);
            }
            
            // Redirect ke halaman status peminjaman setelah menyimpan data
            return redirect()->route('user.status-peminjaman')
                ->with('success', 'Pelacakan berhasil disimpan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            // Redirect kembali ke halaman sebelumnya dengan pesan error
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
} 