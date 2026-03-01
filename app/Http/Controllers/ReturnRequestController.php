<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\Item;
use App\Models\ItemTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\BorrowStatus;

class ReturnRequestController extends Controller
{
    /**
     * Proses pengembalian barang.
     */
    public function processReturn(Request $request, BorrowRequest $borrowRequest): JsonResponse
    {
        // Validasi request
        $validatedData = $request->validate([
            'return_notes' => 'nullable|string|max:500',
        ]);

        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk memproses pengembalian',
            ], 403);
        }

        // Import status yang valid untuk pengembalian
        $borrowedStatus = BorrowStatus::BORROWED;
        $pendingReturnStatus = BorrowStatus::PENDING_RETURN;
        
        // Cek apakah status peminjaman valid untuk dikembalikan
        $currentStatus = $borrowRequest->status;
        $validStatus = ($currentStatus === $borrowedStatus || $currentStatus === $pendingReturnStatus);
        
        if (!$validStatus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status peminjaman tidak valid untuk pengembalian',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Tentukan apakah pengembalian terlambat
            $actualReturnDate = now();
            $isLate = $actualReturnDate->gt($borrowRequest->return_deadline);
            
            // Tentukan status pengembalian berdasarkan keterlambatan
            $completedStatus = BorrowStatus::COMPLETED;
            $finalStatus = $completedStatus;
            
            // Update data pengembalian
            $borrowRequest->return_date = $actualReturnDate;
            $borrowRequest->return_status = $isLate ? 'late' : 'returned';
            $borrowRequest->notes = $validatedData['return_notes'] ?? null;
            $borrowRequest->status = $finalStatus;
            $borrowRequest->save();

            // Update stok barang - kembalikan jumlah yang dipinjam
            $item = Item::findOrFail($borrowRequest->item_id);
            $item->quantity += $borrowRequest->quantity;
            $item->save();
            
            \Log::info("Item quantity updated after return: Item ID: {$item->id}, New quantity: {$item->quantity}");

            // Catat di item tracking
            ItemTracking::create([
                'item_id' => $borrowRequest->item_id,
                'borrow_request_id' => $borrowRequest->request_id,
                'user_id' => $borrowRequest->user_id,
                'action' => 'return',
                'quantity' => $borrowRequest->quantity,
                'notes' => $isLate ? 'Pengembalian terlambat' : 'Pengembalian tepat waktu',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengembalian barang berhasil diproses',
                'data' => [
                    'borrow_request' => $borrowRequest,
                    'is_late' => $isLate,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error processing return: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses pengembalian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan daftar peminjaman yang belum dikembalikan.
     */
    public function pendingReturns(): JsonResponse
    {
        $borrowedStatus = BorrowStatus::BORROWED;
        $pendingReturnStatus = BorrowStatus::PENDING_RETURN;
        
        $pendingReturns = BorrowRequest::with(['user', 'item'])
            ->whereIn('status', [$borrowedStatus, $pendingReturnStatus])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pendingReturns,
        ]);
    }
} 