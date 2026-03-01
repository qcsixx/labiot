<?php

namespace App\Http\Controllers;

use App\Models\BorrowRequest;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;
use App\Enums\BorrowStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Helpers\CacheHelper;

class UserController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Jalankan middleware untuk semua metode kecuali yang ditentukan
        $this->middleware(function ($request, $next) {
            // Update status peminjaman secara real-time di setiap halaman
            if (Auth::check()) {
                $this->updateBorrowStatuses();
            }
            return $next($request);
        });
    }

    public function showPeminjam()
    {
        try {
            // Ambil data peminjaman aktif
            $activeRequests = BorrowRequest::where('user_id', Auth::id())
                ->whereIn('status', [BorrowStatus::APPROVED, BorrowStatus::PENDING])
                ->with('item')
                ->latest()
                ->take(5)
                ->get();

            // Riwayat peminjaman selesai
            $completedRequests = BorrowRequest::where('user_id', Auth::id())
                ->where('status', BorrowStatus::COMPLETED)
                ->with('item')
                ->latest()
                ->take(5)
                ->get();

            // Ambil notifikasi terbaru dengan eager loading yang lebih hati-hati
            $notificationsQuery = Notification::where('user_id', Auth::id())
                ->latest()
                ->take(5);

            // Tambahkan eager loading yang lebih aman dengan subquery untuk memastikan join yang benar
            $notifications = $notificationsQuery->with('borrowRequest.item')
                ->whereHas('borrowRequest', function($query) {
                    $query->where('status', '!=', BorrowStatus::COMPLETED->value);
                })
                ->get();

            // Statistik peminjaman
            $stats = [
                'total' => BorrowRequest::where('user_id', Auth::id())->count(),
                'pending' => BorrowRequest::where('user_id', Auth::id())->where('status', BorrowStatus::PENDING)->count(),
                'approved' => BorrowRequest::where('user_id', Auth::id())->where('status', BorrowStatus::APPROVED)->count(),
                'completed' => BorrowRequest::where('user_id', Auth::id())->where('status', BorrowStatus::COMPLETED)->count(),
                'rejected' => BorrowRequest::where('user_id', Auth::id())->where('status', BorrowStatus::REJECTED)->count(),
            ];

            // Ambil beberapa barang populer yang tersedia
            $availableItems = Item::where('status', 'available')
                ->where('quantity', '>', 0)
                ->orderBy('name')
                ->get();

            return view('user.dashboard-user', compact(
                'activeRequests',
                'completedRequests',
                'notifications',
                'stats',
                'availableItems'
            ));
        } catch (\Exception $e) {
            // Return with error message
            return view('user.dashboard-user', [
                'error' => __('messages.error.server_error')
            ]);
        }
    }

    /**
     * Menampilkan dashboard user
     */
    public function dashboard()
    {
        try {
            $user = Auth::user();

            // Ambil data peminjaman aktif dengan eager loading
            $activeRequests = BorrowRequest::where('user_id', $user->id)
                ->whereIn('status', [BorrowStatus::APPROVED, BorrowStatus::PENDING])
                ->with('item')
                ->latest()
                ->take(5)
                ->get();

            // Riwayat peminjaman selesai dengan eager loading
            $completedRequests = BorrowRequest::where('user_id', $user->id)
                ->where('status', BorrowStatus::COMPLETED)
                ->with('item')
                ->latest()
                ->take(5)
                ->get();

            // Ambil notifikasi terbaru dengan eager loading
            $notifications = Notification::where('user_id', $user->id)
                ->with('borrowRequest.item')
                ->whereHas('borrowRequest', function($query) {
                    $query->where('status', '!=', BorrowStatus::COMPLETED->value);
                })
                ->latest()
                ->take(20)
                ->get();

            // Cache total items untuk 10 menit
            $totalItems = Cache::remember(
                CacheHelper::ITEMS_AVAILABLE_COUNT,
                CacheHelper::getTTL(CacheHelper::ITEMS_AVAILABLE_COUNT),
                function () {
                return Item::where('status', 'available')->count();
            });

            // Optimize statistics query - single query instead of 5 separate queries
            $statsRaw = BorrowRequest::where('user_id', $user->id)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $stats = [
                'total' => $statsRaw->sum(),
                'pending' => $statsRaw->get(BorrowStatus::PENDING->value, 0),
                'approved' => $statsRaw->get(BorrowStatus::APPROVED->value, 0),
                'completed' => $statsRaw->get(BorrowStatus::COMPLETED->value, 0),
                'rejected' => $statsRaw->get(BorrowStatus::REJECTED->value, 0),
            ];

            // Cache available items untuk 10 menit
            $availableItems = Cache::remember(
                CacheHelper::ITEMS_AVAILABLE_LIST,
                CacheHelper::getTTL(CacheHelper::ITEMS_AVAILABLE_LIST),
                function () {
                return Item::where('quantity', '>', 0)
                    ->orderBy('name')
                    ->get();
            });

            return view('user.dashboard-user', compact(
                'activeRequests',
                'completedRequests',
                'notifications',
                'totalItems',
                'stats',
                'availableItems'
            ));
        } catch (\Exception $e) {
            \Log::error('Error loading user dashboard: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('user.dashboard-user', [
                'error' => 'Terjadi kesalahan saat memuat data dashboard. Tim teknis kami telah diberitahu.'
            ]);
        }
    }

    /**
     * Menampilkan halaman profil user
     */
    public function showProfile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    /**
     * Update profil pengguna
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:15',
        ]);

        try {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->save();

            // Gunakan status query parameter bukan session flash untuk mendukung JavaScript
            return redirect()->route('user.profile', ['status' => 'success', 'message' => __('messages.user.profile_updated')]);
        } catch (\Exception $e) {
            \Log::error('Error updating user profile: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Update password pengguna
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini tidak cocok']);
        }

        try {
            $user->password = Hash::make($request->password);
            $user->save();

            return redirect()->route('user.profile')->with('success', __('messages.user.password_updated'));
        } catch (\Exception $e) {
            \Log::error('Error updating user password: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Update foto profil pengguna
     */
    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // Debug info
            \Log::info('Attempting to update profile photo', [
                'user_id' => $user->id,
                'has_file' => $request->hasFile('profile_photo'),
                'file_valid' => $request->file('profile_photo')->isValid()
            ]);

            // Hapus foto lama jika ada
            if ($user->profile_photo && Storage::disk('public')->exists('profiles/' . $user->profile_photo)) {
                \Log::info('Deleting old photo: ' . $user->profile_photo);
                Storage::disk('public')->delete('profiles/' . $user->profile_photo);
            }

            // Upload dan simpan foto baru
            if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
                $fileName = time() . '_' . $user->id . '.' . $request->profile_photo->extension();

                // Simpan file
                $path = $request->profile_photo->storeAs('profiles', $fileName, 'public');
                \Log::info('File stored at path: ' . $path);

                // Update data user
                $user->profile_photo = $fileName;
            $user->save();

                return redirect()->route('user.profile', ['photo_updated' => '1'])->with('success', __('messages.user.profile_updated'));
            } else {
                \Log::error('Invalid file upload attempt', [
                    'user_id' => $user->id,
                    'has_file' => $request->hasFile('profile_photo')
                ]);

                return redirect()->back()->with('error', 'File tidak valid. Silakan coba lagi.');
            }
        } catch (\Exception $e) {
            \Log::error('Error updating profile photo: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupload foto: ' . $e->getMessage());
        }
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
                    // Kirim notifikasi dashboard (bukan email) untuk keterlambatan
                    $message = "Peminjaman {$request->item->name} telah melewati batas waktu pengembalian ({$request->return_deadline->format('d/m/Y')}). " .
                              "Segera kembalikan untuk menghindari sanksi.";

                    Notification::create([
                        'user_id' => $request->user_id,
                        'status' => BorrowStatus::OVERDUE->value,
                        'request_id' => $request->request_id,
                        'message' => $message,
                    ]);
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
                    // Kirim notifikasi dashboard (bukan email)
                    $message = "Hari ini peminjaman {$request->item->name} harus dikembalikan. " .
                              "Segera kembalikan untuk menghindari status terlambat.";

                    Notification::create([
                        'user_id' => $request->user_id,
                        'status' => BorrowStatus::BORROWED->value,
                        'request_id' => $request->request_id,
                        'message' => $message,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error updating borrow statuses: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
