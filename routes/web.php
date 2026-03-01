<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BorrowRequestController;
use App\Http\Controllers\BorrowReportController;
use App\Http\Controllers\ItemTrackingController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminPelacakanController;
use App\Http\Controllers\ItemTrackingUserController;
use App\Http\Controllers\WebSchedulerController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\UserMiddleware;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

// Halaman landing page
Route::get('/', [App\Http\Controllers\LandingPageController::class, 'index']);

// Route untuk mendapatkan CSRF token
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

// Route untuk memeriksa status autentikasi user
Route::get('/check-auth', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->check() ? auth()->user() : null
    ]);
});

// Route untuk halaman login, register, dan lupa password
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1') // Max 5 login attempts per minute
    ->name('login.submit');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
Route::get('/lupa-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/lupa-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password-reset-sent', [ForgotPasswordController::class, 'showResetLinkSentPage'])->name('password.sent');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Route untuk logout dengan middleware yang berbeda berdasarkan role
Route::post('/admin/logout', [LoginController::class, 'logout'])
    ->middleware(['session.guard:web_admin', 'clear.cookie:admin_session'])
    ->name('admin.logout');

Route::post('/user/logout', [LoginController::class, 'logout'])
    ->middleware(['session.guard:web_user', 'clear.cookie:user_session'])
    ->name('user.logout');

// Route legacy untuk kompatibilitas
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout']);

// Rute verifikasi email - memastikan mudah diakses
Route::get('/email/verify', [VerificationController::class, 'show'])->middleware(['auth'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
Route::get('/email/verify/success', [VerificationController::class, 'verifySuccess'])->name('verification.success');
Route::get('/email/check-status', [VerificationController::class, 'checkVerificationStatus'])->middleware(['auth'])->name('verification.check');

// Redirect ke halaman sesuai role setelah login
Route::get('/dashboard', function() {
    if (Auth::guard('web_admin')->check()) {
        return redirect('/admin/dashboard-admin');
    }
    if (Auth::guard('web_user')->check()) {
        return redirect('/user/dashboard-user');
    }
    return redirect('/login');
})->name('dashboard');

// **Group untuk User**
Route::prefix('user')->name('user.')->middleware(['auth:web_user', UserMiddleware::class])->group(function () {
    Route::get('/dashboard-user', [UserController::class, 'dashboard'])->name('dashboard-user');
    Route::get('/items', [ItemController::class, 'index'])->name('items');

    // Rute profile
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('update-profile');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('update-password');
    Route::post('/profile/photo', [UserController::class, 'updatePhoto'])->name('update-photo');

    // Rute peminjaman
    Route::get('/peminjaman', [BorrowController::class, 'showForm'])->name('peminjaman');
    Route::post('/peminjaman', [BorrowController::class, 'store'])->name('peminjaman.store');
    Route::get('/status-peminjaman', [BorrowController::class, 'status'])->name('status-peminjaman');
    Route::get('/riwayat-peminjaman', [BorrowController::class, 'history'])->name('riwayat-peminjaman');
    Route::get('/detail-peminjaman/{id}', [BorrowController::class, 'detail'])->name('detail-peminjaman');
    Route::post('/cancel-peminjaman/{id}', [BorrowController::class, 'cancel'])->name('cancel-peminjaman');
    Route::post('/mark-borrowed/{id}', [BorrowController::class, 'markBorrowed'])->name('mark-borrowed');
    Route::post('/request-return/{id}', [BorrowController::class, 'requestReturn'])->name('request-return');

    // Rute pelacakan
    Route::post('/pelacakan/{requestId}/store', [ItemTrackingUserController::class, 'store'])->name('tracking.store');

    // Rute notifikasi
    Route::get('/notifikasi', [NotificationController::class, 'indexUser'])->name('notifikasi');

    // Endpoint untuk auto-refresh status peminjaman
    Route::get('/check-borrow-status', [BorrowController::class, 'checkBorrowStatus'])->name('check-borrow-status');

    // Endpoint untuk auto-refresh riwayat peminjaman
    Route::get('/check-borrow-history', [BorrowController::class, 'checkBorrowHistory'])->name('check-borrow-history');

    // Add logout route inside user group
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/logout', [LoginController::class, 'logout']);
});

// **Group untuk Admin**
Route::prefix('admin')->name('admin.')->middleware(['auth:web_admin', AdminMiddleware::class])->group(function () {
    Route::get('/dashboard-admin', [AdminController::class, 'dashboard'])->name('dashboard-admin');
    Route::get('/items', [ItemController::class, 'index'])->name('items');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/borrow-requests', [BorrowRequestController::class, 'index'])->name('borrow-requests');
    Route::get('/borrow-requests/{id}', [BorrowRequestController::class, 'show'])->name('borrow-requests.show');
    Route::put('/borrow-requests/{id}/approve', [BorrowRequestController::class, 'approve'])->name('borrow-requests.approve');
    Route::put('/borrow-requests/{id}/reject', [BorrowRequestController::class, 'reject'])->name('borrow-requests.reject');
    Route::get('/peminjaman/{id}/detail', [BorrowRequestController::class, 'getRequestDetail'])->name('peminjaman.detail');
    Route::get('/borrow-reports', [BorrowReportController::class, 'index'])->name('borrow-reports');
    Route::get('/item-tracking', [ItemTrackingController::class, 'index'])->name('item-tracking');
    Route::post('/item-tracking', [ItemTrackingController::class, 'store'])->name('item-tracking.store');
    Route::get('/item-tracking/{id}/edit', [ItemTrackingController::class, 'edit'])->name('item-tracking.edit');
    Route::put('/item-tracking/{id}', [ItemTrackingController::class, 'update'])->name('item-tracking.update');
    Route::delete('/item-tracking/{id}', [ItemTrackingController::class, 'destroy'])->name('item-tracking.destroy');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::get('/pengembalian-barang', [AdminPelacakanController::class, 'index'])->name('pengembalian-barang');
    Route::get('/tracking-history/{requestId}', [AdminPelacakanController::class, 'trackingHistory'])->name('tracking.history');
    Route::get('/tracking-detail/{requestId}', [AdminPelacakanController::class, 'trackingDetail'])->name('tracking.detail');
    Route::post('/complete-borrowing/{requestId}', [AdminPelacakanController::class, 'completeBorrowing'])->name('complete-borrowing');
    Route::get('/pengelolaan-user', [AdminController::class, 'userManagement'])->name('pengelolaan-user');
    Route::put('/users/{user}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
    Route::put('/users/{user}/activate', [AdminController::class, 'activateUser'])->name('users.activate');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/persetujuan-peminjaman', [AdminController::class, 'approvalManagement'])->name('persetujuan-peminjaman');
    Route::get('/laporan-peminjaman', [AdminController::class, 'reportManagement'])->name('laporan-peminjaman');
    Route::get('/manajemen-barang', [ItemController::class, 'indexView'])->name('manajemen-barang');
    Route::post('/manajemen-barang', [ItemController::class, 'storeView'])->name('manajemen-barang.store');
    Route::put('/manajemen-barang/{id}', [ItemController::class, 'updateView'])->name('manajemen-barang.update');
    Route::delete('/manajemen-barang/{id}', [ItemController::class, 'destroyView'])->name('manajemen-barang.destroy');
    Route::put('/manajemen-barang/{id}/maintenance', [ItemController::class, 'setMaintenance'])->name('manajemen-barang.maintenance');
    Route::put('/manajemen-barang/{id}/available', [ItemController::class, 'setAvailable'])->name('manajemen-barang.available');
    Route::get('/export-pdf', [AdminController::class, 'exportPdf'])->name('export-pdf');
    Route::get('/export-excel', [AdminController::class, 'exportExcel'])->name('export-excel');

    // Endpoint untuk auto-refresh notifikasi sidebar
    Route::get('/get-notifications', [AdminController::class, 'getNotifications'])->name('get-notifications');

    // Endpoint untuk auto-refresh halaman persetujuan dan pengembalian
    Route::get('/check-pending-requests', [AdminController::class, 'checkPendingRequests'])->name('check-pending-requests');
    Route::get('/check-return-requests', [AdminController::class, 'checkReturnRequests'])->name('check-return-requests');

    // Export laporan routes
    Route::get('/laporan/export-pdf', [BorrowReportController::class, 'exportPDF'])->name('laporan.export-pdf');
    Route::get('/laporan/export-excel', [BorrowReportController::class, 'exportExcel'])->name('laporan.export-excel');

    // Add logout route inside admin group
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/logout', [LoginController::class, 'logout']);
});

// Route untuk menjalankan scheduler secara otomatis via HTTP
// Route ini bisa diakses oleh siapapun dan akan dipanggil oleh JavaScript di frontend
Route::get('/run-scheduler', [WebSchedulerController::class, 'runScheduler'])->name('run-scheduler');

// Route untuk API email status
Route::get('/api/email-status', [App\Http\Controllers\ApiController::class, 'getEmailStatus']);
