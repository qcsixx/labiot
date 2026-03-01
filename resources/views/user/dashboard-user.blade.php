@extends('layouts.user.user-layout')

@section('title', 'Dashboard - Lab IoT Vokasi UB')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/user/css/dashboard.css') }}">
@endpush

@php
use App\Enums\BorrowStatus;
@endphp

@section('content')
<div class="dashboard-user p-1 md:p-2">
    @if(isset($error))
        <div class="w-full bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    @else
        <!-- Header dan Welcome Message -->
        <div class="dashboard-welcome bg-gradient-to-r from-[#0F4D92] to-[#0A3B73] text-white rounded-xl shadow-md mb-8">
            <div class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                <div class="welcome-text">
                    <h1 class="text-2xl font-bold text-white">Selamat Datang, {{ Auth::user()->name }}!</h1>
                    <p class="text-white text-sm mt-1 opacity-90">Sistem Peminjaman Barang Lab IoT Vokasi UB</p>
                </div>
                <div class="quote-container w-full md:w-1/3 bg-[#FFF9E6] rounded-xl p-4 relative overflow-hidden border-2 border-[#E2E8F0]">
                    <div class="absolute -right-2 -top-2 w-16 h-16 bg-[#FDB813] rotate-12 transform opacity-10"></div>
                    <div class="flex items-start gap-3 relative z-10">
                        <div class="flex-shrink-0 bg-[#FDB813] bg-opacity-15 p-2 rounded-full relative">
                            <i data-lucide="megaphone" class="w-8 h-8 text-[#FDB813] megaphone-animation"></i>
                            <div class="absolute inset-0 bg-[#FDB813] bg-opacity-20 rounded-full pulse-animation"></div>
                        </div>
                        <div>
                            <h4 class="text-[#0F4D92] font-bold text-sm mb-1">INGAT!</h4>
                            <p class="text-gray-700 text-sm">Yang dipinjamkan, bukan untuk dimiliki. Cuma ada sampai waktu kembali yang sudah dijanjikan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peringatan Data Belum Lengkap (Kondisional) -->
        @if(!Auth::user()->phone)
        <div class="mb-8" x-data="{ pulse: true }" x-init="setInterval(() => pulse = !pulse, 2000)">
            <a href="{{ route('user.profile') }}" class="block">
                <div class="alert-box bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-lg py-3 px-5 flex items-center justify-between transition-all hover:shadow-sm warning-blink warning-container-shake" 
                     :class="{ 'animate-pulse-short': pulse }">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 bg-amber-100 p-1.5 rounded-full warning-icon-pulse">
                            <i data-lucide="alert-triangle" class="h-4 w-4 text-amber-500 warning-icon-shake"></i>
                        </div>
                        <p class="text-sm text-amber-800 font-medium">
                            Data Anda belum lengkap! Silahkan lengkapi data profile untuk aktifasi peminjaman.
                            </p>
                    </div>
                    <span class="text-amber-700 font-medium text-xs flex items-center hover:text-amber-600">
                        Lengkapi Sekarang <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1 arrow-bounce"></i>
                    </span>
                </div>
            </a>
        </div>
        @endif

        <!-- Statistik Overview -->
        <div class="stats-overview mb-10">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i data-lucide="bar-chart-2" class="w-5 h-5 mr-2 text-[#FDB813]"></i> Statistik Peminjaman
            </h2>
            
            <div class="stats-cards grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card Barang Tersedia -->
                <a href="{{ route('user.peminjaman') }}" class="stat-card group bg-gradient-to-br from-white to-[#E6F0FB] rounded-lg overflow-hidden transition-all hover:shadow-md relative">
                    <div class="absolute top-0 left-0 w-full h-1 bg-[#0F4D92]"></div>
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium mb-1">Barang Tersedia</p>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    <span class="counter-number" data-target="{{ \App\Models\Item::where('status', 'available')->count() }}">0</span>
                                </h3>
                            </div>
                            <div class="icon-circle bg-[#0F4D92] bg-opacity-10 p-2.5 rounded-full">
                                <i data-lucide="package" class="w-6 h-6 text-[#FDB813]"></i>
                            </div>
                        </div>
                    </div>
                </a>
                
                <!-- Card Total Peminjaman - Semua Status -->
                <a href="{{ route('user.riwayat-peminjaman') }}" class="stat-card group bg-gradient-to-br from-white to-[#E6F0FB] rounded-lg overflow-hidden transition-all hover:shadow-md relative">
                    <div class="absolute top-0 left-0 w-full h-1 bg-[#0F4D92]"></div>
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium mb-1">Total Peminjaman</p>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    <span class="counter-number" data-target="{{ \App\Models\BorrowRequest::where('user_id', Auth::id())->count() }}">0</span>
                                </h3>
                            </div>
                            <div class="icon-circle bg-[#0F4D92] bg-opacity-10 p-2.5 rounded-full">
                                <i data-lucide="clipboard-list" class="w-6 h-6 text-[#FDB813]"></i>
                            </div>
                        </div>
                    </div>
                </a>
                
                <!-- Card Peminjaman Aktif - Status Borrowed, Overdue, dan Pending -->
                <a href="{{ route('user.status-peminjaman') }}" class="stat-card group bg-gradient-to-br from-white to-[#E6F0FB] rounded-lg overflow-hidden transition-all hover:shadow-md relative">
                    <div class="absolute top-0 left-0 w-full h-1 bg-[#0F4D92]"></div>
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium mb-1">Peminjaman Aktif</p>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    <span class="counter-number" data-target="{{ \App\Models\BorrowRequest::where('user_id', Auth::id())->whereIn('status', ['borrowed', 'overdue', 'pending'])->count() }}">0</span>
                                </h3>
                            </div>
                            <div class="icon-circle bg-[#0F4D92] bg-opacity-10 p-2.5 rounded-full">
                                <i data-lucide="activity" class="w-6 h-6 text-[#FDB813]"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Konten Utama -->
        <div class="dashboard-content mb-10">
            <!-- Daftar Barang dan Notifikasi dalam satu row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Daftar Barang - 2/3 lebar -->
                <div class="lg:col-span-2">
                    <div class="section-daftar-barang bg-white rounded-xl shadow-sm overflow-hidden h-full flex flex-col">
                        <div class="p-4 border-b flex items-center justify-between bg-[#0F4D92] text-white">
                            <h2 class="text-lg font-bold flex items-center">
                                <i data-lucide="package" class="w-5 h-5 mr-2 text-[#FDB813]"></i>
                                <span>Daftar Barang</span>
                            </h2>
                            <a href="{{ route('user.peminjaman') }}" class="text-sm text-white hover:text-[#FDB813] flex items-center">
                                <span>Lihat Semua</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                            </a>
                        </div>
                        
                        <div class="items-carousel p-5 flex-1 flex items-center justify-center">
                            @if($availableItems->count() > 0)
                                <div class="carousel-container overflow-hidden w-full">
                                    <div class="carousel-track flex gap-6 transition-transform duration-300 ease-out" id="carousel-track">
                                        @foreach($availableItems as $item)
                                            <div class="carousel-slide w-full sm:w-1/2 lg:w-1/3 flex-shrink-0">
                                                <a href="{{ route('user.peminjaman') }}" class="item-card block h-full bg-white border border-gray-200 rounded-xl overflow-hidden transition-all duration-300 group hover:shadow-md hover:border-blue-200">
                                                    <div class="item-image h-36 overflow-hidden relative flex items-center justify-center">
                                                        <img 
                                                            src="{{ $item->image ? asset('storage/items/' . $item->image) : asset('images/default-item.png') }}" 
                                                            alt="{{ $item->name }}" 
                                                            class="object-contain transition-transform group-hover:scale-105"
                                                            onerror="this.src = '{{ asset('images/default-item.png') }}'">
                                                    </div>
                                                    <div class="item-details p-3">
                                                        <h3 class="text-base font-semibold text-gray-800 mb-1 line-clamp-1 group-hover:text-[#0F4D92] transition-colors">{{ $item->name }}</h3>
                                                        
                                                        <!-- Kategori di bawah nama -->
                                                        <div class="flex items-center text-xs text-gray-500 mb-2">
                                                            <i data-lucide="tag" class="w-3.5 h-3.5 mr-1 text-[#FDB813]"></i>
                                                            <span>
                                                                @if(is_object($item->category))
                                                                    {{ $item->category->name }}
                                                                @elseif(is_string($item->category))
                                                                    {{ $item->category }}
                                                                @else
                                                                    Tidak Terkategori
                                                                @endif
                                                            </span>
                                                        </div>
                                                        
                                                        <!-- Status ketersediaan -->
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center text-xs text-gray-600 bg-blue-50 text-blue-700 px-2 py-1 rounded-lg">
                                                                <i data-lucide="layers" class="w-3.5 h-3.5 mr-1 text-blue-500"></i>
                                                                <span>{{ $item->stock }} tersedia</span>
                                                            </div>
                                                            <span class="text-xs text-blue-600 flex items-center">
                                                                Lihat
                                                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 ml-0.5 group-hover:translate-x-1 transition-transform"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="empty-state text-center py-10">
                                    <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto">
                                        <i data-lucide="package-x" class="w-8 h-8 text-gray-400"></i>
                                    </div>
                                    <p class="mt-4 text-gray-500">Tidak ada barang tersedia saat ini</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Notifikasi Penting - 1/3 lebar -->
                <div class="notification-column bg-white rounded-xl shadow-sm overflow-hidden h-full flex flex-col">
                    <div class="p-4 border-b flex items-center justify-between bg-[#0F4D92] text-white">
                        <h2 class="text-lg font-bold flex items-center">
                            <i data-lucide="bell" class="w-5 h-5 mr-2 text-[#FDB813]"></i>
                            <span>Notifikasi Penting</span>
                        </h2>
                    </div>
                    
                    <div class="notifications-list p-4 overflow-y-auto no-scrollbar flex-1 max-h-320">
                        <ul class="space-y-4 w-full">
                            @php
                                // Ambil hanya 5 notifikasi terpenting
                                $prioritizedNotifications = collect($notifications ?? [])
                                    ->filter(function($notification) {
                                        // Filter: Hapus notifikasi yang terkait dengan peminjaman yang sudah selesai
                                        $borrowRequest = isset($notification->request_id) ? 
                                            \App\Models\BorrowRequest::where('request_id', $notification->request_id)->first() : null;
                                        
                                        // Jika peminjaman sudah selesai (completed) atau null, jangan tampilkan notifikasinya
                                        return !($borrowRequest && $borrowRequest->status == 'completed');
                                    })
                                    ->sort(function($a, $b) {
                                        // Ambil status notifikasi masing-masing
                                        $statusA = $a->status ?? 'info';
                                        $statusB = $b->status ?? 'info';
                                        
                                        // Cek informasi request untuk masing-masing notifikasi
                                        $requestA = isset($a->request_id) ? \App\Models\BorrowRequest::where('request_id', $a->request_id)->first() : null;
                                        $requestB = isset($b->request_id) ? \App\Models\BorrowRequest::where('request_id', $b->request_id)->first() : null;
                                        
                                        // Cek apakah deadline peminjaman hari ini
                                        $isDeadlineTodayA = $requestA && $requestA->return_deadline ? 
                                            $requestA->return_deadline->startOfDay()->equalTo(now()->startOfDay()) : false;
                                        $isDeadlineTodayB = $requestB && $requestB->return_deadline ? 
                                            $requestB->return_deadline->startOfDay()->equalTo(now()->startOfDay()) : false;
                                        
                                        // Urutan prioritas: 
                                        // 1. overdue (terlambat)
                                        // 2. deadline hari ini
                                        // 3. Sisanya berdasarkan waktu terbaru sesuai alur natural peminjaman
                                        
                                        // Overdue selalu paling atas
                                        if ($statusA === 'overdue' && $statusB !== 'overdue') {
                                            return -1;
                                        }
                                        if ($statusB === 'overdue' && $statusA !== 'overdue') {
                                            return 1;
                                        }
                                        
                                        // Deadline hari ini di urutan kedua (menggunakan pesan notifikasi untuk identifikasi)
                                        $isDeadlineMessageA = strpos($a->message ?? '', 'Hari ini peminjaman') !== false;
                                        $isDeadlineMessageB = strpos($b->message ?? '', 'Hari ini peminjaman') !== false;
                                        
                                        if ($isDeadlineMessageA && !$isDeadlineMessageB) {
                                            return -1;
                                        }
                                        if ($isDeadlineMessageB && !$isDeadlineMessageA) {
                                            return 1;
                                        }
                                        
                                        // Sisanya urut berdasarkan waktu terbaru
                                        // Ini memastikan notifikasi pengajuan pengembalian tampil sesuai urutan waktu saja
                                        return strtotime($b->created_at) <=> strtotime($a->created_at);
                                    })
                                    ->take(20); // Tampilkan maksimal 20 notifikasi saja
                            @endphp
                            
                            @forelse($prioritizedNotifications as $notification)
                                @php
                                    // Ambil status notifikasi langsung
                                    $borrowStatus = $notification->status ?? 'info';
                                    
                                    // Cek apakah notifikasi terkait deadline hari ini
                                    // Gunakan data asli dari BorrowRequest
                                    $borrowRequest = isset($notification->request_id) ? 
                                        \App\Models\BorrowRequest::where('request_id', $notification->request_id)->first() : null;
                                    
                                    $isDeadlineToday = false;
                                    $isLate = false;
                                    
                                    // Cek deadline dari data peminjaman, bukan dari teks notifikasi
                                    if ($borrowRequest && $borrowRequest->return_deadline) {
                                        // Cek apakah hari ini adalah tanggal deadline
                                        $isDeadlineToday = $borrowRequest->return_deadline->startOfDay()->equalTo(now()->startOfDay());
                                        // Cek keterlambatan
                                        $isLate = $borrowRequest->isLate();
                                    }
                                    
                                    // Gunakan helper untuk mendapatkan gaya yang konsisten
                                    $style = \App\Helpers\NotificationHelper::getBorrowStatusStyle($borrowStatus, $isLate, $isDeadlineToday, $notification->message);
                                    
                                    // Ekstrak variabel dari hasil helper
                                    $icon = $style['icon'];
                                    $isUrgent = $style['isUrgent'] ?? false;
                                    $notificationClass = 'bg-' . explode('-', $style['iconBg'])[1] . '-50 border border-' . explode('-', $style['iconBg'])[1] . '-100';
                                    $textClass = $style['text'];
                                    $badgeClass = $style['badge'];
                                    $badgeText = $style['badgeText'];
                                    $iconBgClass = $style['iconBg'];
                                @endphp
                                
                                <li class="notification-item rounded-lg overflow-hidden mb-2 {{ $notificationClass }}" data-notification-id="{{ $notification->id }}">
                                    <div class="p-4">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 {{ $isUrgent ? 'animate-pulse' : '' }}">
                                                <div class="p-2 rounded-full {{ $iconBgClass }}">
                                                    <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $textClass }}"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between mb-1.5">
                                                    <p class="text-sm font-medium {{ $textClass }}">
                                                        {{ $notification->message }}
                                                    </p>
                                                    <span class="px-2 py-0.5 text-2xs {{ $badgeClass }} rounded-full font-medium ml-2 whitespace-nowrap">{{ $badgeText }}</span>
                                                </div>
                                                <div class="flex items-center justify-between mt-2">
                                                    <div class="text-xs text-gray-500 flex items-center">
                                                        <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <div class="text-center py-8 w-full flex flex-col items-center justify-center">
                                    <div class="bg-gray-100 rounded-full w-14 h-14 flex items-center justify-center mx-auto">
                                        <i data-lucide="bell-off" class="w-7 h-7 text-gray-400"></i>
                                    </div>
                                    <p class="mt-4 text-gray-500 text-sm">Tidak ada notifikasi saat ini</p>
                                </div>
                            @endforelse
                        </ul>
                    </div>
                    
                    <!-- Footer/Pembatas di bagian bawah daftar notifikasi -->
                    <div class="notifications-footer py-3 px-4 bg-gray-50 border-t border-gray-100 mt-auto">
                        <div class="flex items-center justify-center text-xs text-gray-400">
                            <i data-lucide="info" class="w-3 h-3 mr-1"></i>
                            <span>Swipe ke atas untuk melihat lebih banyak</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Peminjaman Aktif dan Pelacakan dalam satu row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <!-- Peminjaman Aktif -->
                <div class="section-peminjaman-aktif bg-white rounded-xl shadow-sm overflow-hidden h-full flex flex-col">
                    <div class="p-4 border-b flex items-center justify-between bg-[#0F4D92] text-white">
                        <h2 class="text-lg font-bold flex items-center">
                            <i data-lucide="clipboard-check" class="w-5 h-5 mr-2 text-[#FDB813]"></i>
                            <span>Peminjaman Aktif</span>
                        </h2>
                        <a href="{{ route('user.status-peminjaman') }}" class="text-sm text-white hover:text-[#FDB813] flex items-center group">
                            <span>Lihat Semua</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                    
                    <div class="p-4 overflow-y-auto no-scrollbar max-h-320 flex-1">
                        @php
                            $borrowedRequests = \App\Models\BorrowRequest::with(['item'])
                                ->where('user_id', Auth::id())
                                ->whereIn('status', [
                                    BorrowStatus::BORROWED->value,
                                    BorrowStatus::OVERDUE->value,
                                    BorrowStatus::PENDING_RETURN->value
                                ])
                                ->latest()
                                ->take(5)
                                ->get();

                            $statusToText = function($status) {
                                if ($status instanceof BorrowStatus) {
                                    return $status->getLabel();
                                }
                                $borrowStatus = BorrowStatus::tryFrom($status);
                                return $borrowStatus ? $borrowStatus->getLabel() : 'Status Tidak Diketahui';
                            };
                        @endphp
                        
                        @forelse($borrowedRequests as $request)
                            <a href="{{ route('user.status-peminjaman', $request->request_id) }}" class="block">
                                <div class="borrow-item flex bg-white border border-gray-200 rounded-lg overflow-hidden mb-4 hover:shadow-md hover:border-blue-200 transition-all">
                                    <div class="item-image w-20 h-20 flex-shrink-0 bg-gray-50 flex items-center justify-center">
                                        <img 
                                            src="{{ $request->item && $request->item->image ? asset('storage/items/' . $request->item->image) : asset('images/default-item.png') }}" 
                                            alt="{{ $request->item ? $request->item->name : 'Item' }}" 
                                            class="max-h-16 max-w-16 object-contain p-1"
                                            onerror="this.src = '{{ asset('images/default-item.png') }}'">
                                    </div>
                                    <div class="item-details flex-1 p-4 min-w-0">
                                        <div class="flex flex-col h-full">
                                            <!-- Nama Barang dan Status -->
                                            <div class="flex items-center justify-between mb-2">
                                                <h3 class="text-sm font-semibold text-gray-800 truncate">
                                                    {{ $request->item ? $request->item->name : 'Item tidak ditemukan' }}
                                                </h3>
                                                @php
                                                    // Ambil status aktual
                                                    $actualStatus = $request->status;
                                                    
                                                    // Cek apakah peminjaman terlambat menggunakan method isLate()
                                                    $isLate = $request->isLate();
                                                    
                                                    // Tentukan kelas dan ikon berdasarkan status
                                                    if($actualStatus === 'overdue') {
                                                        $badgeClass = 'bg-red-100 text-red-800';
                                                        $iconClass = 'text-red-500';
                                                        $icon = 'alert-circle';
                                                        $statusLabel = $statusToText($request->status) . ' (MELEWATI DEADLINE)';
                                                    } elseif($actualStatus === 'pending-return' && $isLate) {
                                                        $badgeClass = 'bg-red-100 text-red-800';
                                                        $iconClass = 'text-red-500';
                                                        $icon = 'alert-circle';
                                                        $statusLabel = $statusToText($request->status) . ' (MELEWATI DEADLINE)';
                                                    } elseif($actualStatus === 'borrowed') {
                                                        // Status borrowed selalu tampil biru untuk konsistensi
                                                        $badgeClass = 'bg-cyan-100 text-cyan-800';
                                                        $iconClass = 'text-cyan-500';
                                                        $icon = 'package-check';
                                                        $statusLabel = "Sedang Dipinjam";
                                                    } else {
                                                        // Default values untuk status lainnya
                                                        $statusBadgeColor = 'amber';
                                                        $icon = 'package-check';
                                                        $badgeClass = "bg-$statusBadgeColor-100 text-$statusBadgeColor-700";
                                                        $iconClass = '';
                                                        $statusLabel = $statusToText($request->status);
                                                    }
                                                @endphp
                                                <span class="badge {{ $badgeClass }} px-2 py-0.5 text-xs font-medium rounded-full whitespace-nowrap flex items-center" data-status="{{ $actualStatus }}">
                                                    <i data-lucide="{{ $icon }}" class="w-3 h-3 mr-1 {{ $iconClass }}"></i> 
                                                    {{ $statusLabel }}
                                                </span>
                                            </div>

                                            <!-- Jumlah Barang -->
                                            <div class="flex items-center text-xs text-gray-600 mb-2">
                                                <i data-lucide="layers" class="w-3.5 h-3.5 mr-1.5 text-[#FDB813]"></i>
                                                <span>Jumlah: <strong>{{ $request->quantity }}</strong> unit</span>
                                            </div>
                                            
                                            <!-- Tanggal Peminjaman -->
                                            <div class="flex items-center text-xs text-gray-600 mb-2">
                                                <i data-lucide="calendar-plus" class="w-3.5 h-3.5 mr-1.5 text-[#FDB813]"></i>
                                                <span>Dipinjam: 
                                                    @if($request->borrow_date)
                                                        {{ \Carbon\Carbon::parse($request->borrow_date)->format('d M Y') }}
                                                    @elseif($request->borrowed_at)
                                                        {{ \Carbon\Carbon::parse($request->borrowed_at)->format('d M Y') }}
                                                    @else
                                                        Belum ditentukan
                                                    @endif
                                                </span>
                                            </div>

                                            <!-- Deadline -->
                                            @php
                                                // Hanya tampilkan deadline merah jika status = overdue
                                                $deadlineClass = ($actualStatus === 'overdue') ? 'deadline-overdue' : 'deadline-normal';
                                                $deadlineIcon = ($actualStatus === 'overdue') ? 'icon-status-overdue' : 'icon-status-borrowed';
                                                $deadlineLabel = $request->return_deadline ? \Carbon\Carbon::parse($request->return_deadline)->format('d M Y') : 'Belum ditentukan';
                                            @endphp
                                            <div class="{{ $deadlineClass }} flex items-center text-xs">
                                                <i data-lucide="calendar-clock" class="w-3.5 h-3.5 mr-1.5 {{ $deadlineIcon }}"></i>
                                                <span>Deadline: <b>{{ $deadlineLabel }}</b></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="empty-state text-center py-8">
                                <div class="bg-gray-100 rounded-full w-14 h-14 flex items-center justify-center mx-auto">
                                    <i data-lucide="file-text" class="w-7 h-7 text-gray-400"></i>
                                </div>
                                <p class="mt-3 text-gray-500 text-sm">Tidak ada peminjaman aktif saat ini</p>
                                <a href="{{ route('user.peminjaman') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg text-xs hover:bg-primary-dark transition-colors">
                                    <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i> Pinjam Barang
                                </a>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Footer/Pembatas di bagian bawah daftar peminjaman -->
                    <div class="peminjaman-footer py-3 px-4 bg-gray-50 border-t border-gray-100 mt-auto">
                        <div class="flex items-center justify-center text-xs text-gray-400">
                            <i data-lucide="clipboard-check" class="w-3 h-3 mr-1"></i>
                            <span>Menampilkan peminjaman aktif </span>
                        </div>
                    </div>
                </div>
                
                <!-- Pelacakan -->
                <div class="section-pelacakan bg-white rounded-xl shadow-sm overflow-hidden h-full flex flex-col">
                    <div class="p-4 border-b flex items-center justify-between bg-[#0F4D92] text-white">
                        <h2 class="text-lg font-bold flex items-center">
                            <i data-lucide="map-pin" class="w-5 h-5 mr-2 text-[#FDB813]"></i>
                            <span>Pelacakan Barang</span>
                        </h2>
                        <a href="{{ route('user.status-peminjaman') }}" class="text-sm text-white hover:text-[#FDB813] flex items-center group">
                            <span>Lihat Semua</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                    
                    <div class="p-4 overflow-y-auto no-scrollbar max-h-320 flex-1">
                        @php
                            $trackableRequests = \App\Models\BorrowRequest::with(['item'])
                                ->where('user_id', Auth::id())
                                ->whereIn('status', [
                                    BorrowStatus::BORROWED->value,
                                    BorrowStatus::OVERDUE->value,
                                    BorrowStatus::PENDING_RETURN->value
                                ])
                                ->latest()
                                ->take(3)
                                ->get();
                        @endphp
                        
                        @forelse($trackableRequests as $request)
                            <a href="{{ route('user.status-peminjaman', ['trackingId' => $request->request_id]) }}" class="block">
                                <div class="tracking-item flex bg-white border border-gray-200 rounded-lg overflow-hidden mb-4 hover:shadow-md hover:border-blue-200 transition-all">
                                    <div class="item-image w-20 h-20 flex-shrink-0 bg-gray-50 flex items-center justify-center">
                                        <img 
                                            src="{{ $request->item && $request->item->image ? asset('storage/items/' . $request->item->image) : asset('images/default-item.png') }}" 
                                            alt="{{ $request->item ? $request->item->name : 'Item' }}" 
                                            class="max-h-16 max-w-16 object-contain p-1"
                                            onerror="this.src = '{{ asset('images/default-item.png') }}'">
                                    </div>
                                    <div class="item-details flex-1 p-4 min-w-0">
                                        <div class="flex flex-col h-full">
                                            <!-- Nama Barang dan Status -->
                                            <div class="flex items-center justify-between mb-2">
                                                <h3 class="text-sm font-semibold text-gray-800 truncate">{{ $request->item ? $request->item->name : 'Item tidak ditemukan' }}</h3>
                                                
                                                @php
                                                    // Status badge untuk pelacakan
                                                    $actualStatus = $request->status;
                                                    $isLate = $request->isLate();
                                                    
                                                    if($actualStatus === 'overdue') {
                                                        $trackingBadgeClass = 'bg-red-100 text-red-800';
                                                        $trackingIcon = 'alert-circle';
                                                        $trackingIconClass = 'text-red-500';
                                                        $trackingLabel = 'MELEWATI DEADLINE';
                                                    } elseif($actualStatus === 'pending-return' && $isLate) {
                                                        $trackingBadgeClass = 'bg-red-100 text-red-800';
                                                        $trackingIcon = 'alert-circle';
                                                        $trackingIconClass = 'text-red-500';
                                                        $trackingLabel = $statusToText($request->status) . ' (MELEWATI DEADLINE)';
                                                    } elseif($actualStatus === 'pending-return') {
                                                        $trackingBadgeClass = 'bg-amber-100 text-amber-700';
                                                        $trackingIcon = 'hourglass';
                                                        $trackingIconClass = 'text-amber-500';
                                                        $trackingLabel = $statusToText($request->status);
                                                    } elseif($actualStatus === 'borrowed') {
                                                        $trackingBadgeClass = 'bg-cyan-100 text-cyan-800';
                                                        $trackingIcon = 'package-check';
                                                        $trackingIconClass = 'text-cyan-500';
                                                        $trackingLabel = "Sedang Dipinjam";
                                                    } else {
                                                        $statusBadgeColor = 'amber';
                                                        $trackingIcon = 'package-check';
                                                        $trackingBadgeClass = "bg-$statusBadgeColor-100 text-$statusBadgeColor-700";
                                                        $trackingIconClass = '';
                                                        $trackingLabel = $statusToText($request->status);
                                                    }
                                                    
                                                    // Dapatkan jumlah pelacakan dari database untuk request ini
                                                    $trackingCount = \App\Models\ItemTracking::where('borrow_request_id', $request->request_id)->count();
                                                    
                                                    // Hanya tampilkan deadline merah jika status adalah overdue
                                                    $deadlineClass = ($actualStatus === 'overdue') ? 'deadline-overdue' : 'deadline-normal';
                                                    $deadlineIcon = ($actualStatus === 'overdue') ? 'text-red-500' : 'text-cyan-500';
                                                    $deadlineLabel = $request->return_deadline ? \Carbon\Carbon::parse($request->return_deadline)->format('d M Y') : 'Belum ditentukan';
                                                @endphp
                                                
                                                <span class="badge {{ $trackingBadgeClass }} px-2 py-0.5 text-xs font-medium rounded-full whitespace-nowrap flex items-center">
                                                    <i data-lucide="{{ $trackingIcon }}" class="w-3 h-3 mr-1 {{ $trackingIconClass }}"></i> 
                                                    {{ $trackingLabel }}
                                                </span>
                                            </div>

                                            <!-- Jumlah/Info Pelacakan -->
                                            <div class="flex items-center text-xs text-gray-600 mb-2">
                                                <i data-lucide="map-pin" class="w-3.5 h-3.5 mr-1.5 text-[#FDB813]"></i>
                                                @if($trackingCount > 0)
                                                    <span>Pelacakan telah dilakukan sebanyak <strong>{{ $trackingCount }}</strong> kali</span>
                                                @else
                                                    <span>Belum ada riwayat pelacakan untuk barang ini</span>
                                                @endif
                                            </div>

                                            <!-- Tanggal Peminjaman -->
                                            <div class="flex items-center text-xs text-gray-600 mb-2">
                                                <i data-lucide="calendar-plus" class="w-3.5 h-3.5 mr-1.5 text-[#FDB813]"></i>
                                                <span>Dipinjam: 
                                                    @if($request->borrow_date)
                                                        {{ \Carbon\Carbon::parse($request->borrow_date)->format('d M Y') }}
                                                    @elseif($request->borrowed_at)
                                                        {{ \Carbon\Carbon::parse($request->borrowed_at)->format('d M Y') }}
                                                    @else
                                                        Belum ditentukan
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            <!-- Deadline -->
                                            <div class="{{ $deadlineClass }} flex items-center text-xs">
                                                <i data-lucide="calendar-clock" class="w-3.5 h-3.5 mr-1.5 {{ $deadlineIcon }}"></i>
                                                <span>Deadline: <b>{{ $deadlineLabel }}</b></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="empty-state text-center py-8">
                                <div class="bg-gray-100 rounded-full w-14 h-14 flex items-center justify-center mx-auto">
                                    <i data-lucide="map" class="w-7 h-7 text-gray-400"></i>
                                </div>
                                <p class="mt-3 text-gray-500 text-sm">Tidak ada barang yang dapat dilacak saat ini</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Footer/Pembatas di bagian bawah daftar pelacakan -->
                    <div class="pelacakan-footer py-3 px-4 bg-gray-50 border-t border-gray-100 mt-auto">
                        <div class="flex items-center justify-center text-xs text-gray-400">
                            <i data-lucide="map-pin" class="w-3 h-3 mr-1"></i>
                            <span>Lacak lokasi barang pinjaman</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Javascript untuk dashboard-user.blade.php
</script>
@endpush