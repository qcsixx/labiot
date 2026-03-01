<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\ItemTracking;
use App\Models\Item;
use App\Models\Notification;
use App\Enums\BorrowStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminPelacakanController extends Controller
{
    public function index(Request $request)
    {
        // Query dasar untuk peminjaman yang berstatus pending-return saja
        $query = BorrowRequest::with(['user', 'item'])
                    ->where('status', BorrowStatus::PENDING_RETURN);
        
        // Pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = '%' . $request->search . '%';
            $query->where(function($query) use ($search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', $search);
                })->orWhereHas('item', function($q) use ($search) {
                    $q->where('name', 'like', $search);
                });
            });
        }
        
        // Urutkan dari terlama ke terbaru
        $query->orderBy('created_at', 'asc');
        
        // Paginate hasil
        $borrowRequests = $query->paginate(10)->withQueryString();
        
        return view('admin.pengembalian-barang', compact('borrowRequests'));
    }
    
    public function trackingDetail($requestId)
    {
        // Method ini tidak lagi digunakan, karena tracking-detail.blade.php sudah dihapus
        // dan fitur ini digabung dengan trackingHistory yang menampilkan semua tracking dalam modal
        $borrowRequest = BorrowRequest::with(['user', 'item'])->findOrFail($requestId);
        
        // Ambil data pelacakan terakhir untuk peminjaman ini
        $lastTracking = ItemTracking::where('borrow_request_id', $requestId)
            ->with('reportedBy')
            ->orderBy('tracking_date', 'desc')
            ->first();
        
        if (!$lastTracking) {
            return response()->json([
                'error' => 'Belum ada data pelacakan untuk peminjaman ini.'
            ]);
        }
        
        // Format response
        $response = [
            'item' => [
                'name' => $borrowRequest->item->name,
                'id' => $borrowRequest->item->id
            ],
            'user' => [
                'name' => $borrowRequest->user->name,
                'id' => $borrowRequest->user->id
            ],
            'tracking' => [
                'id' => $lastTracking->tracking_id,
                'location' => $lastTracking->location,
                'notes' => $lastTracking->notes,
                'image_path' => $lastTracking->photo,
                'tracked_by' => $lastTracking->reportedBy ? $lastTracking->reportedBy->name : 'Unknown',
                'formatted_date' => $lastTracking->tracking_date->format('d M Y, H:i')
            ]
        ];
        
        return response()->json($response);
    }
    
    public function trackingHistory($requestId)
    {
        $borrowRequest = BorrowRequest::with(['user', 'item'])->findOrFail($requestId);
        
        // Ambil riwayat pelacakan untuk peminjaman ini
        $trackings = ItemTracking::where('borrow_request_id', $requestId)
            ->with('reportedBy')
            ->orderBy('tracking_date', 'desc')
            ->get();
        
        // Format data untuk JSON response
        $trackingsData = $trackings->map(function($tracking) {
            return [
                'id' => $tracking->tracking_id,
                'location' => $tracking->location,
                'notes' => $tracking->notes,
                'image_path' => $tracking->photo, // Path ke foto tracking
                'tracked_by' => $tracking->reportedBy ? $tracking->reportedBy->name : 'Unknown',
                'formatted_date' => $tracking->tracking_date->format('d M Y, H:i')
            ];
        });
        
        // Data respons JSON
        $response = [
            'item' => [
                'name' => $borrowRequest->item->name,
                'id' => $borrowRequest->item->id
            ],
            'user' => [
                'name' => $borrowRequest->user->name,
                'id' => $borrowRequest->user->id
            ],
            'borrow_date' => $borrowRequest->borrow_date->format('d M Y'),
            'return_deadline' => $borrowRequest->return_deadline->format('d M Y'),
            'status' => $borrowRequest->status->value,
            'status_label' => $borrowRequest->getStatusLabel(),
            'quantity' => $borrowRequest->quantity,
            'purpose' => $borrowRequest->purpose,
            'trackings' => $trackingsData
        ];
        
        return response()->json($response);
    }
    
    public function completeBorrowing(Request $request, $requestId)
    {
        try {
            DB::beginTransaction();
            
            $borrowRequest = BorrowRequest::with('item')->findOrFail($requestId);
            
            // Validasi status
            if ($borrowRequest->status != BorrowStatus::PENDING_RETURN) {
                return redirect()->back()
                    ->with('error', 'Status peminjaman tidak valid untuk diselesaikan');
            }
            
            // Ambil item untuk diperbarui stoknya
            $item = Item::findOrFail($borrowRequest->item_id);
            
            // Catat stok awal untuk log
            $initialStock = $item->quantity;
            
            // Kembalikan stok sesuai jumlah yang dipinjam
            $newStock = $initialStock + $borrowRequest->quantity;
            
            // Update stok item
            $item->quantity = $newStock;
            $item->save();
            
            // Tentukan status akhir
            $finalStatus = BorrowStatus::COMPLETED;
            $returnStatus = 'returned';
            
            // Cek apakah pengembalian terlambat - hari H deadline tidak dianggap terlambat
            $isLate = $borrowRequest->return_deadline->startOfDay() < now()->startOfDay();
                      
            if ($isLate) {
                $returnStatus = 'late';
            }
            
            // Update status dan tanggal pengembalian
            $borrowRequest->status = $finalStatus;
            $borrowRequest->return_date = now()->toDateString();
            $borrowRequest->return_status = $returnStatus;
            
            // Simpan catatan admin jika ada
            if ($request->filled('admin_notes')) {
                $borrowRequest->notes = $request->admin_notes;
            }
            
            $borrowRequest->save();
            
            // Buat notifikasi untuk user
            $status = BorrowStatus::COMPLETED;
            $notificationMessage = "Peminjaman {$item->name} telah selesai dan barang telah dikembalikan";
            
            if ($isLate) {
                $status = BorrowStatus::COMPLETED; // Tetap COMPLETED tetapi akan ditentukan oleh return_status='late'
                $notificationMessage = "Peminjaman {$item->name} telah selesai dengan keterlambatan. Mohon perhatikan batas waktu peminjaman berikutnya.";
            }
            
            Notification::create([
                'user_id' => $borrowRequest->user_id,
                'status' => $status,
                'request_id' => $borrowRequest->request_id,
                'message' => $notificationMessage,
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.pengembalian-barang')
                ->with('success', 'Pengembalian berhasil diselesaikan');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 