<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\Item;
use App\Models\Notification;
use App\Models\ItemTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Enums\BorrowStatus;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;

class BorrowController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Jalankan middleware untuk semua metode kecuali yang ditentukan
        $this->middleware(function ($request, $next) {
            // Update status peminjaman secara real-time di setiap halaman
            $this->updateBorrowStatuses();
            return $next($request);
        });
    }

    /**
     * Menampilkan form peminjaman
     */
    public function showForm(Request $request)
    {
        // Start query builder
        $query = Item::where('status', 'available')
            ->where('quantity', '>', 0);

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Apply category filter if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Get filtered items
        $items = $query->orderBy('name')->get();

        return view('user.peminjaman', compact('items'));
    }

    /**
     * Proses peminjaman baru
     */
    public function store(Request $request)
    {
        // Cek apakah user memiliki nomor handphone
        $user = Auth::user();
        if (!$user->phone) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda harus mengisi nomor handphone di profil Anda sebelum dapat mengajukan peminjaman',
                    'redirect' => route('user.profile')
                ], 400);
            }

            return redirect()->route('user.profile')
                ->with('error', 'Anda harus mengisi nomor handphone di profil Anda sebelum dapat mengajukan peminjaman');
        }

        // Validasi request
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'borrow_date' => 'required|date|after_or_equal:today',
            'return_deadline' => 'required|date|after:borrow_date',
            'purpose' => 'required|string|max:500',
        ], [
            'item_id.required' => 'Silakan pilih barang terlebih dahulu',
            'item_id.exists' => 'Barang yang dipilih tidak valid',
            'quantity.required' => 'Jumlah harus diisi',
            'quantity.integer' => 'Jumlah harus berupa angka',
            'quantity.min' => 'Jumlah minimal 1',
            'borrow_date.required' => 'Tanggal pinjam harus diisi',
            'borrow_date.date' => 'Format tanggal pinjam tidak valid',
            'borrow_date.after_or_equal' => 'Tanggal pinjam minimal hari ini',
            'return_deadline.required' => 'Tanggal kembali harus diisi',
            'return_deadline.date' => 'Format tanggal kembali tidak valid',
            'return_deadline.after' => 'Tanggal kembali harus setelah tanggal pinjam',
            'purpose.required' => 'Tujuan peminjaman harus diisi',
            'purpose.max' => 'Tujuan peminjaman maksimal 500 karakter',
        ]);

        // Ambil item
        $item = Item::findOrFail($validated['item_id']);

        // Cek apakah item tersedia (status available)
        if ($item->status !== 'available') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Barang tidak tersedia untuk dipinjam (status: ' . $item->status . ')'
                ], 400);
            }

            return redirect()->route('user.peminjaman')
                ->with('error', 'Barang tidak tersedia untuk dipinjam (status: ' . $item->status . ')')
                ->withInput();
        }

        // Cek apakah stok mencukupi
        if ($item->quantity < $validated['quantity']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Stok tidak mencukupi. Stok tersedia: {$item->quantity}"
                ], 400);
            }

            return redirect()->route('user.peminjaman')
                ->with('error', "Stok tidak mencukupi. Stok tersedia: {$item->quantity}")
                ->withInput();
        }

        try {
            // Lanjutkan dengan pembuatan permintaan peminjaman
            DB::beginTransaction();

            // Buat permintaan peminjaman baru
            $borrowRequest = new BorrowRequest([
                'user_id' => Auth::id(),
                'item_id' => $validated['item_id'],
                'quantity' => $validated['quantity'],
                'borrow_date' => Carbon::parse($validated['borrow_date']),
                'return_deadline' => Carbon::parse($validated['return_deadline']),
                'purpose' => $validated['purpose'],
                'status' => BorrowStatus::PENDING,
            ]);

            $borrowRequest->save();

            // Buat notifikasi untuk admin
            NotificationHelper::createBorrowNotification(
                1, // Admin ID, assuming admin ID is 1
                Auth::user()->name . " mengajukan permintaan peminjaman " . $item->name,
                BorrowStatus::PENDING->value,
                $borrowRequest->request_id
            );

            DB::commit();

            // Jika request adalah AJAX, kembalikan response JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('messages.borrow.request_created'),
                    'data' => [
                        'request_id' => $borrowRequest->request_id,
                        'item_name' => $item->name,
                        'quantity' => $validated['quantity'],
                        'borrow_date' => $validated['borrow_date'],
                        'return_deadline' => $validated['return_deadline']
                    ]
                ], 200);
            }

            return redirect()->route('user.status-peminjaman')
                ->with('success', __('messages.borrow.request_created'));

        } catch (\Exception $e) {
            DB::rollBack();

            // Jika request adalah AJAX, kembalikan response JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan status peminjaman user
     */
    public function status(Request $request)
    {
        $query = BorrowRequest::where('user_id', Auth::id())
            ->with('item');

        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $status = $request->status;

            // Match string status ke enum BorrowStatus
            $statusMap = [
                'pending' => BorrowStatus::PENDING,
                'approved' => BorrowStatus::APPROVED,
                'borrowed' => BorrowStatus::BORROWED,
                'rejected' => BorrowStatus::REJECTED,
                'pending-return' => BorrowStatus::PENDING_RETURN,
                'completed' => BorrowStatus::COMPLETED,
                'overdue' => BorrowStatus::OVERDUE
            ];

            if (isset($statusMap[$status])) {
                $query->where('status', $statusMap[$status]);
            }
        } else {
            // Jika tidak ada filter status, kecualikan status yang sudah selesai/ditolak
            $query->whereNotIn('status', [
                BorrowStatus::COMPLETED,
                BorrowStatus::REJECTED
            ]);
        }

        // Urutkan dari yang terbaru
        $borrowRequests = $query->latest()->paginate(9);

        // Handle AJAX request untuk pembaruan status
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => __('messages.success.updated', ['item' => 'Status peminjaman']),
                'count' => [
                    'total' => $borrowRequests->count(),
                    'overdue' => $borrowRequests->filter(function ($item) {
                        return is_object($item->status) && $item->status === BorrowStatus::OVERDUE;
                    })->count()
                ]
            ]);
        }

        return view('user.status-peminjaman', compact('borrowRequests'));
    }

    /**
     * Pembaruan status peminjaman secara real-time
     *
     * Method ini mengecek status peminjaman dan mengupdate sesuai kondisi:
     * - Borrowed yang melewati deadline menjadi Overdue
     * - Borrowed yang hari ini deadline diberi notifikasi
     */
    protected function updateBorrowStatuses(): void
    {
        try {
            // 1. Dapatkan permintaan peminjaman yang sudah lewat tenggat dan belum dikembalikan
            // Use eager loading to prevent N+1 queries
            $overdueRequests = BorrowRequest::whereIn('status', [BorrowStatus::APPROVED->value, BorrowStatus::BORROWED->value])
                ->whereDate('return_deadline', '<', Carbon::today())
                ->with(['user', 'item'])
                ->get();

            foreach ($overdueRequests as $request) {
                // Ubah status menjadi overdue (status baru untuk melewati deadline)
                $request->update([
                    'status' => BorrowStatus::OVERDUE->value,
                    'return_status' => 'late',
                ]);

                // Cek apakah sudah ada notifikasi overdue untuk hari ini
                $existingNotification = Notification::where('request_id', $request->request_id)
                    ->where('status', BorrowStatus::OVERDUE->value)
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                // Kirim notifikasi hanya jika belum ada notifikasi hari ini
                if (!$existingNotification) {
                    NotificationHelper::createOverdueNotification(
                        $request->user_id,
                        $request->item->name,
                        $request->return_deadline->format('d/m/Y'),
                        $request->request_id
                    );
                }
            }

            // 2. Dapatkan permintaan peminjaman yang deadline-nya hari ini (ganti dari H-1 ke hari H)
            // Use eager loading to prevent N+1 queries
            $todayDeadlineRequests = BorrowRequest::where('status', BorrowStatus::BORROWED)
                ->whereDate('return_deadline', Carbon::today())
                ->with(['user', 'item'])
                ->get();

            foreach ($todayDeadlineRequests as $request) {
                // Cek apakah sudah ada notifikasi deadline untuk hari ini
                $existingNotification = Notification::where('request_id', $request->request_id)
                    ->where('status', BorrowStatus::BORROWED->value)
                    ->whereDate('created_at', Carbon::today())
                    ->whereRaw("message LIKE '%Hari ini peminjaman%'")
                    ->exists();

                // Kirim notifikasi hanya jika belum ada notifikasi hari ini
                if (!$existingNotification) {
                    NotificationHelper::createDeadlineNotification(
                        $request->user_id,
                        $request->item->name,
                        $request->request_id
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error updating borrow statuses: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Detail peminjaman
     */
    public function detail($requestId)
    {
        // Log untuk debugging
        \Log::debug("Detail request received for request ID: " . $requestId);

        $borrowRequest = BorrowRequest::where('user_id', Auth::id())
            ->where('request_id', $requestId)
            ->with(['item', 'itemTrackings' => function($query) {
                $query->orderBy('tracking_date', 'desc');
            }])
            ->firstOrFail();

        // Tambahkan log untuk melihat jumlah tracking yang ditemukan
        \Log::debug("Found " . ($borrowRequest->itemTrackings ? $borrowRequest->itemTrackings->count() : 0) . " tracking entries for request ID: " . $requestId);

        // Transform data pelacakan ke format yang lebih konsisten
        $trackings = [];
        if ($borrowRequest->itemTrackings) {
            foreach ($borrowRequest->itemTrackings as $tracking) {
                $trackings[] = [
                    'tracking_id' => $tracking->tracking_id,
                    'location' => $tracking->location,
                    'notes' => $tracking->notes,
                    'photo' => $tracking->photo,
                    'photo_url' => $tracking->photo_url, // Akses accessor getPhotoUrlAttribute
                    'tracking_date' => $tracking->tracking_date,
                    'tracked_by' => $tracking->tracked_by,
                ];
            }
        }

        // Jika request adalah AJAX, kembalikan response JSON dengan format yang konsisten
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'borrowRequest' => $borrowRequest,
                'trackings' => $trackings // Menambahkan trackings secara terpisah untuk memastikan konsistensi
            ]);
        }

        return view('user.detail-peminjaman', compact('borrowRequest'));
    }

    /**
     * Batalkan permintaan peminjaman yang masih pending
     */
    public function cancel($requestId)
    {
        $borrowRequest = BorrowRequest::where('user_id', Auth::id())
            ->where('request_id', $requestId)
            ->first();

        if (!$borrowRequest) {
            return redirect()->back()
                ->with('error', 'Permintaan peminjaman tidak ditemukan');
        }

        // Convert status to string for comparison
        $statusStr = is_object($borrowRequest->status)
            ? $borrowRequest->status->value
            : $borrowRequest->status;

        if ($statusStr !== 'pending') {
            return redirect()->back()
                ->with('error', 'Hanya permintaan dengan status Menunggu Persetujuan yang dapat dibatalkan');
        }

        try {
            DB::beginTransaction();

            // Import enum untuk status REJECTED
            $rejectedStatus = BorrowStatus::REJECTED;

            // Update status menjadi rejected menggunakan enum
            $borrowRequest->update([
                'status' => $rejectedStatus,
            ]);

            // Buat notifikasi untuk pembatalan
            Notification::create([
                'user_id' => Auth::id(),
                'status' => BorrowStatus::REJECTED->value, // Langsung gunakan status peminjaman
                'request_id' => $borrowRequest->request_id,
                'message' => "Permintaan peminjaman {$borrowRequest->item->name} telah dibatalkan oleh Anda",
            ]);

            DB::commit();

            return redirect()->route('user.status-peminjaman')
                ->with('success', 'Permintaan peminjaman berhasil dibatalkan')
                ->with('toast', 'Permintaan peminjaman berhasil dibatalkan')
                ->with('toast_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membatalkan permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Menandai barang sudah diambil oleh user
     */
    public function markBorrowed($requestId)
    {
        $borrowRequest = BorrowRequest::where('user_id', Auth::id())
            ->where('request_id', $requestId)
            ->where('status', BorrowStatus::APPROVED)
            ->whereNull('borrowed_at')
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Update status menjadi BORROWED dan update borrowed_at
            $borrowRequest->update([
                'status' => BorrowStatus::BORROWED,
                'borrowed_at' => now(),
            ]);

            // Buat notifikasi
            Notification::create([
                'user_id' => Auth::id(),
                'status' => BorrowStatus::BORROWED->value, // Langsung gunakan status peminjaman
                'request_id' => $borrowRequest->request_id,
                'message' => "Barang {$borrowRequest->item->name} telah diambil",
            ]);

            // Buat notifikasi untuk admin juga
            Notification::create([
                'user_id' => 1, // ID admin
                'status' => BorrowStatus::BORROWED->value, // Langsung gunakan status peminjaman
                'request_id' => $borrowRequest->request_id,
                'message' => "Barang {$borrowRequest->item->name} telah diambil oleh " . Auth::user()->name,
            ]);

            DB::commit();

            // Jika request adalah AJAX, kembalikan response JSON
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ayo upload pelacakan pertama untuk barang ini!',
                    'request_id' => $borrowRequest->request_id
                ]);
            }

            // Redirect ke halaman status-peminjaman
            return redirect()->route('user.status-peminjaman')
                ->with('success', 'Ayo upload pelacakan pertama untuk barang ini!')
                ->with('toast', __('messages.success.updated', ['item' => 'Status peminjaman']))
                ->with('toast_type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();

            // Jika request adalah AJAX, kembalikan response JSON
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Mengajukan pengembalian barang
     */
    public function requestReturn(Request $request, $requestId)
    {
        try {
            // Cari peminjaman
            $borrowRequest = BorrowRequest::where('request_id', $requestId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$borrowRequest) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Peminjaman tidak ditemukan'], 404);
                }
                return redirect()->back()->with('error', 'Peminjaman tidak ditemukan');
            }

            // Cek keterlambatan - gunakan nilai yang benar sesuai enum
            $today = now()->startOfDay();
            $deadline = Carbon::parse($borrowRequest->return_deadline)->startOfDay();

            // Terlambat hanya jika:
            // 1. Status sudah overdue, ATAU
            // 2. Tanggal hari ini SETELAH tanggal deadline (bukan pada hari H)
            $isLate = false;

            if (is_object($borrowRequest->status) && $borrowRequest->status->value === 'overdue') {
                $isLate = true; // Sudah status overdue pasti terlambat
            } elseif ($today > $deadline) {
                $isLate = true; // Hari ini SETELAH deadline (tidak termasuk hari H)
            }

            // Hari H deadline tidak dianggap terlambat
            $returnStatus = $isLate ? 'late' : 'returned';

            // Khusus untuk update database
            try {
                // Update dengan nilai return_status yang benar
                DB::table('borrow_requests')
                    ->where('request_id', $requestId)
                    ->update([
                        'status' => 'pending-return',
                        'return_status' => $returnStatus,
                        'updated_at' => now()
                    ]);
            } catch (\Exception $dbError) {
                \Log::error('Error database update: ' . $dbError->getMessage());

                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Gagal mengubah status: ' . $dbError->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Gagal mengubah status peminjaman: ' . $dbError->getMessage());
            }

            // Notifikasi user
            Notification::create([
                'user_id' => Auth::id(),
                'status' => 'pending-return',
                'request_id' => $borrowRequest->request_id,
                'message' => "Anda telah mengajukan pengembalian untuk {$borrowRequest->item->name}",
            ]);

            // Notifikasi admin
            Notification::create([
                'user_id' => 1, // ID admin
                'status' => 'pending-return',
                'request_id' => $borrowRequest->request_id,
                'message' => Auth::user()->name . " mengajukan pengembalian untuk {$borrowRequest->item->name}",
            ]);

            // Respons berdasarkan tipe request
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Pengajuan pengembalian berhasil']);
            }

            // Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Pengajuan pengembalian berhasil dikirim');

        } catch (\Exception $e) {
            \Log::error('Error pada requestReturn: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan, silakan coba lagi'], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan, silakan coba lagi');
        }
    }

    /**
     * Tampilkan riwayat peminjaman user (status COMPLETED atau REJECTED)
     */
    public function history(Request $request)
    {
        $query = BorrowRequest::where('user_id', Auth::id())
            ->whereIn('status', [
                BorrowStatus::COMPLETED,
                BorrowStatus::REJECTED
            ])
            ->with(['item', 'itemTrackings']);

        // Filter pencarian nama barang
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter rentang tanggal peminjaman
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('borrow_date', [$startDate, $endDate]);
        }

        // Filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;

            // Match string status ke enum BorrowStatus
            $statusMap = [
                'completed' => BorrowStatus::COMPLETED,
                'rejected' => BorrowStatus::REJECTED,
                'pending-return' => BorrowStatus::PENDING_RETURN,
                'overdue' => BorrowStatus::OVERDUE
            ];

            if (isset($statusMap[$status])) {
                $query->where('status', $statusMap[$status]);
            }
        }

        // Urutkan dari yang terbaru
        $borrowRequests = $query->latest()->paginate(9);

        return view('user.riwayat-peminjaman', compact('borrowRequests'));
    }

    /**
     * Endpoint API untuk memeriksa perubahan status peminjaman user
     * Digunakan untuk auto-refresh halaman status peminjaman
     */
    public function checkBorrowStatus()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Ambil data peminjaman yang masih aktif (bukan completed/rejected)
        $borrowRequests = BorrowRequest::where('user_id', $user->id)
            ->whereNotIn('status', [
                BorrowStatus::COMPLETED,
                BorrowStatus::REJECTED
            ])
            ->get();

        // Kirim hanya data minimal yang dibutuhkan untuk perbandingan
        $data = $borrowRequests->map(function($request) {
            return [
                'id' => $request->request_id,
                'status' => $request->status->value,
                'updated_at' => $request->updated_at->timestamp
            ];
        });

        return response()->json([
            'count' => $borrowRequests->count(),
            'data' => $data
        ]);
    }

    /**
     * Endpoint API untuk memeriksa perubahan riwayat peminjaman user
     * Digunakan untuk auto-refresh halaman riwayat peminjaman
     */
    public function checkBorrowHistory(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Buat query dasar
        $query = BorrowRequest::where('user_id', $user->id)
            ->whereIn('status', [
                BorrowStatus::COMPLETED,
                BorrowStatus::REJECTED
            ]);

        // Terapkan filter yang sama dengan metode history

        // Filter rentang tanggal peminjaman
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('borrow_date', [$startDate, $endDate]);
        }

        // Filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;

            // Match string status ke enum BorrowStatus
            $statusMap = [
                'completed' => BorrowStatus::COMPLETED,
                'rejected' => BorrowStatus::REJECTED
            ];

            if (isset($statusMap[$status])) {
                $query->where('status', $statusMap[$status]);
            }
        }

        // Ambil data riwayat peminjaman yang telah difilter
        $borrowRequests = $query->get();

        // Kirim hanya data minimal yang dibutuhkan untuk perbandingan
        $data = $borrowRequests->map(function($request) {
            return [
                'id' => $request->request_id,
                'status' => $request->status->value,
                'updated_at' => $request->updated_at->timestamp
            ];
        });

        return response()->json([
            'count' => $borrowRequests->count(),
            'data' => $data
        ]);
    }
}
