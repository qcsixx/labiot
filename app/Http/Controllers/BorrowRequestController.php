<?php

namespace App\Http\Controllers;

use App\Http\Requests\BorrowRequestRequest;
use App\Http\Resources\BorrowRequestResource;
use App\Models\BorrowRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Enums\BorrowStatus;
use App\Helpers\CacheHelper;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;

class BorrowRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = BorrowRequest::with(['user', 'item']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user if not admin
        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        // Sort requests
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->input('per_page', 15);
        $requests = $query->paginate($perPage);

        return BorrowRequestResource::collection($requests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BorrowRequestRequest $request): JsonResponse
    {
        // Check if item is available and has enough quantity
        $item = Item::findOrFail($request->id);

        if ($item->status !== 'available') {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak tersedia untuk dipinjam (status: ' . $item->status . ')',
            ], 400);
        }

        if ($item->quantity < $request->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah barang yang tersedia tidak mencukupi',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $borrowRequest = BorrowRequest::create([
                'user_id' => Auth::id(),
                'item_id' => $request->id,
                'quantity' => $request->quantity,
                'borrow_date' => $request->borrow_date,
                'return_deadline' => $request->return_deadline,
                'purpose' => $request->purpose,
                'status' => BorrowStatus::PENDING,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Permintaan peminjaman berhasil diajukan',
                'data' => new BorrowRequestResource($borrowRequest->load(['user', 'item'])),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengajukan permintaan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BorrowRequest $borrowRequest): JsonResponse
    {
        // Check if user has permission to view this request
        if (Auth::user()->role !== 'admin' && Auth::id() !== $borrowRequest->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk melihat permintaan ini',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => new BorrowRequestResource($borrowRequest->load(['user', 'item'])),
        ]);
    }

    /**
     * Process the approval or rejection of a borrow request.
     *
     * @param BorrowRequest $borrowRequest
     * @param string $status Either 'approved' or 'rejected'
     * @return array Result of the operation with status and message
     */
    private function processBorrowRequestStatus(BorrowRequest $borrowRequest, string $status): array
    {
        // Cek apakah ini request yang pending
        $isPending = false;

        // Jika status adalah objek enum
        if (is_object($borrowRequest->status) && method_exists($borrowRequest->status, 'value')) {
            $isPending = $borrowRequest->status->value === 'pending';
            $currentStatus = $borrowRequest->status->value;
        }
        // Jika status adalah string
        elseif (is_string($borrowRequest->status)) {
            $isPending = $borrowRequest->status === 'pending';
            $currentStatus = $borrowRequest->status;
        }
        // Jika status null atau tipe lain
        else {
            $currentStatus = 'unknown';
        }

        // Proses hanya jika status pending
        if (!$isPending) {
            // Tentukan status text untuk tampilan
            $statusText = 'unknown';

            if (is_object($borrowRequest->status) && method_exists($borrowRequest->status, 'value')) {
                $statusText = match($borrowRequest->status->value) {
                    'approved' => 'disetujui',
                    'rejected' => 'ditolak',
                    'pending-return' => 'dalam pengajuan pengembalian',
                    'completed' => 'selesai',
                    'overdue' => 'melewati deadline',
                    default => $borrowRequest->status->value
                };
            } elseif (is_string($borrowRequest->status)) {
                $statusText = match($borrowRequest->status) {
                    'approved' => 'disetujui',
                    'rejected' => 'ditolak',
                    'pending-return' => 'dalam pengajuan pengembalian',
                    'completed' => 'selesai',
                    'overdue' => 'melewati deadline',
                    default => $borrowRequest->status
                };
            }

            return [
                'success' => false,
                'message' => "Permintaan ini sudah {$statusText}",
                'status_code' => 400
            ];
        }

        try {
            DB::beginTransaction();

            $item = Item::findOrFail($borrowRequest->item_id);

            // If approving, check item availability and quantity
            if ($status === 'approved') {
                if ($item->status === 'maintenance') {
                    return [
                        'success' => false,
                        'message' => 'Barang sedang dalam maintenance',
                        'status_code' => 400
                    ];
                }

                // Simpan stok awal untuk log
                $initialStock = $item->quantity;

                if ($initialStock < $borrowRequest->quantity) {
                    return [
                        'success' => false,
                        'message' => 'Jumlah barang yang tersedia tidak mencukupi',
                        'status_code' => 400
                    ];
                }

                // Update item quantity
                $newQuantity = $initialStock - $borrowRequest->quantity;

                // Pengecekan tambahan untuk pastikan stok tidak bisa negatif
                if ($newQuantity < 0) {
                    $newQuantity = 0;
                }

                // Update stok barang
                $item->quantity = $newQuantity;
                $item->save();
            }

            // Update borrow request status - menggunakan enum
            if ($status === 'approved') {
                $borrowRequest->status = BorrowStatus::APPROVED;
            } else {
                $borrowRequest->status = BorrowStatus::REJECTED;
            }
            $borrowRequest->approval_date = now();

            // Simpan catatan admin jika ada
            if (request()->has('notes')) {
                $borrowRequest->notes = request()->input('notes');
            }

            $borrowRequest->save();

            // Send notification to user
            try {
                if ($status === 'approved') {
                    NotificationHelper::createApprovalNotification(
                        $borrowRequest->user_id,
                        $item->name,
                        $borrowRequest->request_id
                    );
                } else {
                    NotificationHelper::createRejectionNotification(
                        $borrowRequest->user_id,
                        $item->name,
                        $borrowRequest->request_id
                    );
                }
            } catch (\Exception $e) {
                // Lanjutkan proses meskipun notifikasi gagal dibuat
                Log::error('Failed to create notification in processBorrowRequestStatus', [
                    'error' => $e->getMessage(),
                    'borrow_request_id' => $borrowRequest->request_id,
                ]);
            }

            DB::commit();

            // Invalidate cache after status change
            CacheHelper::invalidateAllDashboards();

            return [
                'success' => true,
                'message' => "Permintaan peminjaman berhasil " . ($status === 'approved' ? 'disetujui' : 'ditolak'),
                'data' => $borrowRequest,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    /**
     * API approval handler
     */
    public function update(Request $request, BorrowRequest $borrowRequest): JsonResponse
    {
        // Validate request
        $validatedData = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menyetujui/menolak permintaan',
            ], 403);
        }

        $result = $this->processBorrowRequestStatus($borrowRequest, $validatedData['status']);

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
                'error' => $result['error'] ?? null,
            ], $result['status_code']);
        }

        return response()->json([
            'status' => 'success',
            'message' => $result['message'],
            'data' => new BorrowRequestResource($result['data']->load(['user', 'item'])),
        ]);
    }

    /**
     * Web approval handler
     */
    public function approve($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menyetujui peminjaman');
        }

        try {
            // Gunakan transaksi database untuk memastikan integritas data
            DB::beginTransaction();

            // Ambil data permintaan peminjaman langsung dari database dengan fresh data
            $borrowRequest = BorrowRequest::findOrFail($id);

            // Ambil barang dengan lock untuk menghindari race condition
            $item = Item::where('id', $borrowRequest->item_id)->lockForUpdate()->first();

            if (!$item) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Data barang tidak ditemukan');
            }

            // Simpan stok awal untuk pesan dan log
            $initialStock = $item->quantity;

            // Cek apakah barang tersedia untuk dipinjam (status available)
            if ($item->status !== 'available') {
                DB::rollBack();
                return redirect()->back()->with('error', 'Barang tidak tersedia untuk dipinjam (status: ' . $item->status . ')');
            }

            // Cek apakah stok mencukupi
            if ($initialStock < $borrowRequest->quantity) {
                DB::rollBack();
                return redirect()->back()->with('error', "Jumlah barang tidak mencukupi. Stok tersedia: {$initialStock}, diminta: {$borrowRequest->quantity}");
            }

            // Jumlah stok baru setelah dikurangi jumlah yang dipinjam
            $newQuantity = $initialStock - $borrowRequest->quantity;

            // Pengecekan tambahan untuk pastikan stok tidak bisa negatif
            if ($newQuantity < 0) {
                $newQuantity = 0;
            }

            // Update stok barang
            $item->quantity = $newQuantity;
            $item->save();

            // Update status permintaan
            $borrowRequest->status = BorrowStatus::APPROVED;
            $borrowRequest->approval_date = now();

            // Simpan catatan admin jika ada
            if (request()->has('notes')) {
                $borrowRequest->notes = request()->input('notes');
            }

            $borrowRequest->save();

            // Buat notifikasi untuk peminjam
            $message = "Permintaan peminjaman {$item->name} telah disetujui";

            Notification::create([
                'user_id' => $borrowRequest->user_id,
                'status' => BorrowStatus::APPROVED->value,
                'request_id' => $borrowRequest->request_id,
                'message' => $message,
                'is_read' => false,
            ]);

            DB::commit();

            // Invalidate cache after approval
            CacheHelper::invalidateAllDashboards();

            return redirect()->back()->with('success', "Permintaan peminjaman berhasil disetujui. Stok {$item->name} berkurang dari {$initialStock} menjadi {$newQuantity}");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Web rejection handler
     */
    public function reject($id)
    {
        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menolak peminjaman');
        }

        try {
            // Ambil data permintaan peminjaman langsung dari database
            $borrowRequest = BorrowRequest::findOrFail($id);

            // Update status permintaan
            $borrowRequest->status = BorrowStatus::REJECTED;
            $borrowRequest->approval_date = now();

            // Simpan catatan admin jika ada (alasan penolakan)
            if (request()->has('notes')) {
                $borrowRequest->notes = request()->input('notes');
            }

            $borrowRequest->save();

            // Buat notifikasi untuk peminjam
            $message = "Permintaan peminjaman {$borrowRequest->item->name} telah ditolak";

            Notification::create([
                'user_id' => $borrowRequest->user_id,
                'status' => BorrowStatus::REJECTED->value,
                'request_id' => $borrowRequest->request_id,
                'message' => $message,
                'is_read' => false,
            ]);

            return redirect()->back()->with('success', 'Permintaan peminjaman berhasil ditolak');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a borrow request.
     */
    public function cancel(BorrowRequest $borrowRequest): JsonResponse
    {
        // Check if user has permission to cancel this request
        if (Auth::id() !== $borrowRequest->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk membatalkan permintaan ini',
            ], 403);
        }

        // Check if request is still pending - use enum value property with null check
        if (!$borrowRequest->status || $borrowRequest->status->value !== 'pending') {
            $statusText = $borrowRequest->status ? match($borrowRequest->status->value) {
                'approved' => 'disetujui',
                'rejected' => 'ditolak',
                'pending-return' => 'dalam pengajuan pengembalian',
                'completed' => 'selesai',
                default => $borrowRequest->status->value
            } : 'unknown';

            return response()->json([
                'status' => 'error',
                'message' => "Permintaan tidak dapat dibatalkan karena sudah {$statusText}",
            ], 400);
        }

        // Delete the request
        $borrowRequest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan berhasil dibatalkan',
        ]);
    }

    /**
     * Menampilkan halaman persetujuan peminjaman untuk admin
     */
    public function indexAdmin(Request $request)
    {
        try {
            // Ambil semua data pending terlebih dahulu
            $query = DB::table('borrow_requests as br')
                ->join('users', 'br.user_id', '=', 'users.id')
                ->join('items', 'br.item_id', '=', 'items.id')
                ->select('br.*', 'users.name as user_name', 'items.name as item_name')
                ->where('br.status', '=', 'pending');

            // Tambahkan pencarian jika ada
            if ($request->has('search') && !empty($request->search)) {
                $search = '%' . $request->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('users.name', 'like', $search)
                      ->orWhere('items.name', 'like', $search);
                });
            }

            // Urutkan dari terlama ke terbaru (sort=oldest)
            $query->orderBy('br.created_at', 'asc');

            // Paginate hasil
            $perPage = 10;
            $rawData = $query->paginate($perPage);

            // Ambil data lengkap menggunakan model dengan urutan yang sama
            $requestIds = $rawData->pluck('request_id')->toArray();

            // Buat kumpulan model BorrowRequest dengan eager loading
            $borrowRequests = [];
            foreach($requestIds as $id) {
                $borrowRequests[] = BorrowRequest::with(['user', 'item'])->find($id);
            }

            // Tambahkan informasi paginasi ke hasil
            $borrowRequests = new \Illuminate\Pagination\LengthAwarePaginator(
                $borrowRequests,
                $rawData->total(),
                $rawData->perPage(),
                $rawData->currentPage(),
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('admin.persetujuan-peminjaman', compact('borrowRequests'));
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard-admin')->with('error', 'Terjadi kesalahan saat memuat data persetujuan: ' . $e->getMessage());
        }
    }

    /**
     * Mengambil detail peminjaman untuk modal detail
     */
    public function getRequestDetail($id)
    {
        // Tambahkan logging
        \Log::debug("Detail request received for request ID: {$id}");

        // Cek apakah user adalah admin
        if (Auth::user()->role !== 'admin') {
            \Log::warning("Unauthorized access attempt to detail by user ID: " . Auth::id());
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk melihat detail peminjaman'
            ], 403);
        }

        try {
            // Ambil data lengkap peminjaman dengan eager loading
            // Pastikan mencari berdasarkan request_id
            $borrowRequest = BorrowRequest::with([
                'user',
                'item', // Hapus relasi category karena tidak didefinisikan di model Item
                'approvalAdmin' => function($query) {
                    $query->select('id', 'name');
                },
                'returnAdmin' => function($query) {
                    $query->select('id', 'name');
                },
                'itemTrackings' => function($query) {
                    $query->orderBy('tracking_date', 'desc');
                }
            ])->where('request_id', $id)->firstOrFail();

            \Log::debug("BorrowRequest found with ID: {$id}");

            // Format tanggal jika ada
            if ($borrowRequest->approval_date) {
                $borrowRequest->approval_date = $borrowRequest->approval_date->format('Y-m-d H:i:s');
            }

            if ($borrowRequest->return_date) {
                $borrowRequest->return_date = $borrowRequest->return_date->format('Y-m-d H:i:s');
            }

            if ($borrowRequest->actual_return_date) {
                $borrowRequest->actual_return_date = $borrowRequest->actual_return_date->format('Y-m-d H:i:s');
            }

            return response()->json($borrowRequest);
        } catch (\Exception $e) {
            \Log::error("Error fetching detail for request ID {$id}: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
