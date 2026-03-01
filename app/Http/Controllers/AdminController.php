<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory as ViewFactory;
use App\Models\BorrowRequest;
use App\Models\Item;
use App\Models\ItemTracking;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BorrowRequestsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Enums\BorrowStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Helpers\CacheHelper;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Cache dashboard statistics for 5 minutes to reduce database load
        $stats = Cache::remember(
            CacheHelper::ADMIN_DASHBOARD_STATS,
            CacheHelper::getTTL(CacheHelper::ADMIN_DASHBOARD_STATS),
            function () {
            return [
                'pendingRequests' => BorrowRequest::where('status', 'pending')->count(),
                'approvedRequests' => BorrowRequest::where('status', 'approved')->count(),
                'rejectedRequests' => BorrowRequest::where('status', 'rejected')->count(),
                'borrowedItems' => BorrowRequest::whereIn('status', ['approved', 'borrowed', 'pending_return'])->count(),
                'availableItems' => Item::where('status', 'available')->count(),
            ];
        });

        // Mengambil notifikasi peminjaman terbaru dengan eager loading
        $latestBorrowRequests = BorrowRequest::with(['user', 'item'])
            ->where('status', 'pending')
            ->latest()
            ->take(4)
            ->get();

        // Mengambil notifikasi pengembalian terbaru dengan eager loading
        $latestReturns = BorrowRequest::with(['user', 'item'])
            ->where('status', 'pending-return')
            ->latest()
            ->take(4)
            ->get();

        return view('admin.dashboard-admin', array_merge($stats, [
            'latestBorrowRequests' => $latestBorrowRequests,
            'latestReturns' => $latestReturns
        ]));
    }

    public function userManagement()
    {
        $query = User::where('role', 'user');

        // Filter berdasarkan pencarian
        if(request()->filled('search')) {
            $search = '%' . request('search') . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('phone', 'like', $search);
            });
        }

        // Filter berdasarkan status
        if(request()->filled('status')) {
            $query->where('status', request('status'));
        }

        $users = $query->latest()->paginate(10);

        return view('admin.pengelolaan-user', compact('users'));
    }

    public function suspendUser(User $user)
    {
        $user->update(['status' => 'suspended']);
        return redirect()->route('admin.pengelolaan-user')->with('success', 'User berhasil ditangguhkan');
    }

    public function activateUser(User $user)
    {
        $user->update(['status' => 'active']);
        return redirect()->route('admin.pengelolaan-user')->with('success', 'User berhasil diaktifkan');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.pengelolaan-user')->with('success', __('messages.user.deleted'));
    }

    /**
     * Manage approval of borrow requests
     */
    public function approvalManagement()
    {
        try {
            // Get pending borrow requests with related user and item
            $borrowRequests = BorrowRequest::with(['user', 'item'])
                ->where('status', BorrowStatus::PENDING)
                ->latest()
                ->get();

            return view('admin.persetujuan-peminjaman', compact('borrowRequests'));
        } catch (\Exception $e) {
            \Log::error("Error in approval management: " . $e->getMessage());
            return redirect()->route('admin.dashboard-admin')->with('error', 'Terjadi kesalahan saat memuat data persetujuan: ' . $e->getMessage());
        }
    }

    public function reportManagement()
    {
        // Mengurutkan dari terlama (oldest/asc) ke terbaru (latest/desc)
        $borrowRequests = BorrowRequest::with(['user', 'item'])
            ->orderBy('borrow_date', 'asc')
            ->get();

        $query = BorrowRequest::with(['user', 'item']);
        $hasFilter = false;

        // Memproses filter rentang tanggal jika ada
        if (request()->filled('start_date') && request()->filled('end_date')) {
            $startDate = request('start_date');
            $endDate = request('end_date');

            $query->whereBetween('borrow_date', [$startDate, $endDate]);
            $hasFilter = true;
        }
        // Kompatibilitas dengan frontend jika masih digunakan
        elseif (request()->has('date_range')) {
            $date = request('date');
            $query->whereDate('borrow_date', $date);
            $hasFilter = true;
        }

        // Filter status
        if (request()->filled('status')) {
            $status = strtolower(request('status'));

            // Filter untuk status gabungan
            if ($status === 'dipinjam') {
                $query->where(function($q) {
                    $q->whereIn('status', ['approved', 'borrowed', 'pending-return', 'overdue']);
                });
            }
            // Filter untuk status spesifik
            elseif ($status === 'dikembalikan') {
                $query->where(function($q) {
                    $q->where('status', 'completed');
                });
            }
            // Filter untuk status lainnya
            else {
                $query->where('status', $status);
            }

            $hasFilter = true;
        }

        // Filter pencarian
        if (request()->filled('search')) {
            $search = '%' . request('search') . '%';

            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($sq) use ($search) {
                    $sq->where('name', 'like', $search);
                })->orWhereHas('item', function($sq) use ($search) {
                    $sq->where('name', 'like', $search);
                });
            });

            $hasFilter = true;
        }

        // Jika ada filter yang diterapkan, gunakan hasil query
        if ($hasFilter) {
            $borrowRequests = $query->orderBy('borrow_date', 'asc')->get();
        }

        return view('admin.laporan-peminjaman', compact('borrowRequests'));
    }

    public function trackingManagement()
    {
        $trackings = ItemTracking::with(['reportedBy', 'borrowRequest.item'])
            ->latest()
            ->get();
        return view('admin.pelacakan-barang', compact('trackings'));
    }

    public function exportPdf()
    {
        try {
            // Mengambil request parameter
            $request = request();
            $query = BorrowRequest::with(['user', 'item']);

            // Filter by date range
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $query->whereBetween('borrow_date', [$startDate, $endDate]);
            }
            // Kompatibilitas dengan filter date lama jika masih digunakan
            elseif ($request->filled('date')) {
                $date = $request->date;
                $query->whereDate('borrow_date', $date);
            }

            // Filter by status
            if ($request->filled('status')) {
                $status = strtolower($request->status);

                // Filter untuk status gabungan
                if ($status === 'dipinjam') {
                    $query->where(function($q) {
                        $q->whereIn('status', ['approved', 'borrowed', 'pending-return', 'overdue']);
                    });
                }
                // Filter untuk status spesifik
                elseif ($status === 'dikembalikan') {
                    $query->where(function($q) {
                        $q->where('status', 'completed');
                    });
                }
                // Filter untuk status lainnya
                else {
                    $query->where('status', $status);
                }
            }

            // Filter by search term
            if ($request->filled('search')) {
                $search = '%' . $request->search . '%';
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($sq) use ($search) {
                        $sq->where('name', 'like', $search);
                    })->orWhereHas('item', function($sq) use ($search) {
                        $sq->where('name', 'like', $search);
                    });
                });
            }

            // Mengambil data dengan urutan dari terlama ke terbaru
            $borrowRequests = $query->orderBy('borrow_date', 'asc')->get();

            $pdf = PDF::loadView('admin.laporan-peminjaman-pdf', [
                'borrowRequests' => $borrowRequests
            ]);

            $fileName = 'laporan-peminjaman-' . now()->format('Y-m-d') . '.pdf';
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        // Menggunakan BorrowRequestsExport yang sudah dimodifikasi untuk mengurutkan data
        return Excel::download(new BorrowRequestsExport(request()), 'laporan-peminjaman.xlsx');
    }

    /**
     * Mengambil notifikasi untuk sidebar
     * Digunakan oleh polling JS untuk update badge
     */
    public function getNotifications()
    {
        try {
            // Gunakan cache untuk mencegah terlalu banyak query ke database
            $cacheTime = now()->addSeconds(3); // Cache hanya 3 detik

            $pendingBorrow = \Cache::remember('pending_borrow_count', $cacheTime, function () {
                return BorrowRequest::where('status', BorrowStatus::PENDING)->count();
            });

            $pendingReturn = \Cache::remember('pending_return_count', $cacheTime, function () {
                return BorrowRequest::where('status', BorrowStatus::PENDING_RETURN)->count();
            });

            // Return sebagai JSON
            return response()->json([
                'pending_borrow' => $pendingBorrow,
                'pending_return' => $pendingReturn,
                'timestamp' => now()->timestamp
            ]);
        } catch (\Exception $e) {
            \Log::error("Error fetching notifications: " . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil notifikasi',
                'timestamp' => now()->timestamp
            ], 500);
        }
    }

    /**
     * Endpoint API untuk memeriksa jumlah permintaan peminjaman yang menunggu
     */
    public function checkPendingRequests()
    {
        $count = BorrowRequest::where('status', BorrowStatus::PENDING)->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Endpoint API untuk memeriksa jumlah permintaan pengembalian yang menunggu
     */
    public function checkReturnRequests()
    {
        $count = BorrowRequest::where('status', BorrowStatus::PENDING_RETURN)->count();
        return response()->json(['count' => $count]);
    }
}
