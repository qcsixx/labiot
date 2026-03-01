@extends('layouts.user.user-layout')

@section('title', 'Status Peminjaman - Lab IoT Vokasi UB')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('assets/user/css/status-peminjaman.css') }}">
<!-- Animate.css untuk animasi toast -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css">
<!-- Leaflet CSS untuk peta -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #location-map {
        height: 230px;
        width: 100%;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }

    .leaflet-control-attribution {
        font-size: 8px;
    }

    /* Custom location status styling */
    .location-status-success {
        color: #10B981;
        font-weight: 500;
    }

    .location-status-warning {
        color: #F59E0B;
        font-weight: 500;
    }

    .location-status-error {
        color: #EF4444;
        font-weight: 500;
    }

    /* Input dan button styling */
    .tracking-form-input {
        height: 40px;
        border-radius: 0.375rem;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 0.75rem;
        width: 100%;
        font-size: 0.875rem;
        line-height: 1.25rem;
        box-sizing: border-box;
        color: #111827; /* text-gray-900 untuk visibility */
        background-color: white; /* bg-white untuk consistency */
    }

    /* Button container styling */
    .input-group {
        display: flex;
        align-items: stretch;
    }

    .input-group .tracking-form-input {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-right: 0;
    }

    /* Customize refresh button */
    .refresh-location-btn {
        background-color: #0F4C81;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 40px; /* Match input height */
        border-radius: 0 0.375rem 0.375rem 0;
        box-sizing: border-box;
    }

    .refresh-location-btn:hover {
        background-color: #0c3a61;
    }

    .refresh-location-btn svg {
        color: white;
        width: 1.25rem;
        height: 1.25rem;
    }

    /* Custom marker styling */
    .custom-map-marker {
        background: transparent;
        border: none;
        position: relative;
        z-index: 1000;
    }

    .marker-pin {
        width: 36px;
        height: 36px;
        border-radius: 50% 50% 50% 0;
        background: #F59E0B; /* Warna kuning sesuai tema */
        position: absolute;
        transform: rotate(-45deg);
        left: 50%;
        top: 50%;
        margin: -20px 0 0 -20px;
        box-shadow: 0 0 0 6px rgba(245, 158, 11, 0.5);
        border: 2px solid white;
    }

    /* Style untuk marker-icon (lokasi pin) */
    .marker-icon {
        width: 8px;
        height: 8px;
        background-color: #0F4C81;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(45deg);
    }

    /* Style untuk icon dalam pin (titik lokasi) */
    .marker-pin::before {
        content: "";
        width: 8px;
        height: 8px;
        background: #0F4C81;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Inner circle (bagian putih marker) */
    .marker-pin::after {
        content: '';
        width: 22px;
        height: 22px;
        margin: 7px 0 0 7px;
        background: white;
        position: absolute;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
<!-- Header Section -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-[var(--primary)]">Status Peminjaman Saya</h1>
    <p class="text-gray-600">Pantau dan kelola barang yang sedang kamu pinjam.</p>
</div>

<!-- Daftar Peminjaman -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($borrowRequests as $request)
    @php
        // Deteksi deadline hari ini dan overdue berdasarkan tanggal
        $isToday = false;
        $isOverdue = false;
        $cardClass = '';
        $isBorrowed = is_object($request->status) && $request->status->value == 'borrowed';

        if ($request->return_deadline) {
            $deadline = \Carbon\Carbon::parse($request->return_deadline)->startOfDay();
            $today = \Carbon\Carbon::now()->startOfDay();

            // Overdue jika deadline sudah lewat (terlepas dari status)
            $isOverdue = ($today > $deadline);

            // Hari H jika tanggal deadline tepat hari ini dan masih dalam status dipinjam
            $isToday = ($today->equalTo($deadline) && $isBorrowed);
        }

        // Set class untuk card berdasarkan status dan kondisi deadline
        if (is_object($request->status)) {
            if ($request->status->value == 'overdue' ||
                ($isBorrowed && $isOverdue)) {
                $cardClass = 'overdue';
            } elseif ($isToday && $isBorrowed) {
                $cardClass = 'today-return';
            } elseif ($request->status->value == 'pending-return') {
                // Cek keterlambatan dengan logika yang sama dengan controller
                $isReturnLate = false;

                if ($request->return_status === 'late') {
                    $isReturnLate = true;
                }

                $cardClass = $isReturnLate ? 'pending-return late' : 'pending-return';
            }
        }

        // Tentukan label status yang akan ditampilkan
        $statusLabel = is_object($request->status) ? $request->getStatusLabel() : 'Tidak diketahui';

        // Override label untuk borrowed yang sebenarnya sudah lewat deadline
        if ($isBorrowed && $isOverdue) {
            $statusLabel = 'Melewati Deadline';
        }
    @endphp
    <div class="borrow-card {{ $cardClass }}">
        <div class="card-header">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold truncate mr-2">{{ $request->item->name }}</h3>
                <!-- Badge Status -->
                @if($isToday && is_object($request->status) && $request->status->value == 'borrowed')
                    <span class="custom-deadline-badge">
                        <svg class="w-3 h-3 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        DEADLINE HARI INI
                    </span>
                @elseif((is_object($request->status) && $request->status->value == 'borrowed' && $isOverdue) || (is_object($request->status) && $request->status->value == 'overdue'))
                    <span class="custom-overdue-badge">
                        <svg class="w-3 h-3 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        MELEWATI DEADLINE
                    </span>
                @else
                <span class="status-badge whitespace-nowrap
                    @if(is_object($request->status))
                        @if($request->status->value == 'pending')
                            bg-amber-200 text-amber-800
                        @elseif($request->status->value == 'approved')
                            bg-emerald-200 text-emerald-800
                        @elseif($request->status->value == 'borrowed')
                            bg-sky-200 text-sky-800
                        @elseif($request->status->value == 'pending-return')
                            @if($request->return_status === 'late')
                                bg-orange-400 text-white
                            @else
                                bg-yellow-200 text-yellow-800
                            @endif
                        @elseif($request->status->value == 'completed')
                            bg-green-600 text-white
                        @elseif($request->status->value == 'rejected')
                            bg-red-800 text-white
                        @endif
                    @else
                        bg-gray-500 text-white
                    @endif">
                    @if(is_object($request->status) && $request->status->value == 'pending')
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Menunggu Persetujuan
                    @else
                        {{ $statusLabel }}
                    @endif
                </span>
                @endif
            </div>
        </div>

        <div class="card-content">
            <!-- Gambar dan Info -->
            <div class="flex mb-4">
                <div class="item-image-container bg-gray-200 flex items-center justify-center mr-4">
                    @if($request->item->image)
                        <img src="{{ $request->item->imageUrl }}" alt="{{ $request->item->name }}" class="w-full h-full object-contain">
                    @else
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    @endif
                </div>
                <div>
                    <div class="info-item">
                        <svg class="w-4 h-4 text-[var(--primary)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                        </svg>
                        <span class="text-gray-700">Jumlah: <span class="font-semibold">{{ $request->quantity }} unit</span></span>
                    </div>
                    <div class="info-item">
                        <svg class="w-4 h-4 text-[var(--primary)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-gray-700">Dipinjam: <span class="font-semibold">{{ \Carbon\Carbon::parse($request->borrow_date)->format('d M Y') }}</span></span>
                    </div>
                    <div class="info-item">
                        <svg class="w-4 h-4 @if(is_object($request->status) && (($request->status->value == 'overdue') || $isToday)) text-red-600 @else text-[var(--primary)] @endif mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-gray-700">Deadline: <span class="
                            @if(is_object($request->status) && $request->status->value == 'overdue')
                                text-red-600 font-bold
                            @elseif(is_object($request->status) && $request->status->value == 'pending-return' && $request->return_status === 'late')
                                text-red-600 font-bold
                            @elseif($isToday)
                                deadline-text-highlight
                            @else
                                font-semibold
                            @endif
                        ">{{ \Carbon\Carbon::parse($request->return_deadline)->format('d M Y') }}</span></span>
                    </div>

                    <!-- Total Pelacakan -->
                    <div class="info-item">
                        <svg class="w-4 h-4 text-[var(--primary)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <span class="text-gray-700">Total Pelacakan: <span class="font-semibold">{{ $request->itemTrackings->count() ?? 0 }} kali</span></span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar Status -->
            <div class="progress-bar-container">
                @php
                    $stepStatus = [
                        'pending' => 0,
                        'approved' => 1,
                        'borrowed' => 2,
                        'pending-return' => 3,
                        'completed' => 4
                    ];

                    $currentStep = is_object($request->status) ?
                        (isset($stepStatus[$request->status->value]) ? $stepStatus[$request->status->value] : 0) : 0;

                    $isOverdue = is_object($request->status) && in_array($request->status->value, ['overdue', 'pending-return']);
                @endphp

                <!-- Step 1: Pengajuan -->
                <div class="progress-bar-step">
                    <div class="step-circle @if($currentStep == 0) active @elseif($currentStep > 0) completed @endif" data-tooltip="Pengajuan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Step 2: Disetujui -->
                <div class="progress-bar-step">
                    <div class="step-line @if($currentStep > 0) completed @endif"></div>
                    <div class="step-circle @if($currentStep == 1) active @elseif($currentStep > 1) completed @endif" data-tooltip="Disetujui">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                </div>

                <!-- Step 3: Dipinjam -->
                <div class="progress-bar-step">
                    <div class="step-line @if($currentStep > 1) completed @endif"></div>
                    <div class="step-circle @if($currentStep == 2) active @elseif($currentStep > 2) completed @endif" data-tooltip="Dipinjam">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                    </div>
                </div>

                <!-- Step 4: Pengembalian -->
                <div class="progress-bar-step">
                    <div class="step-line @if($currentStep > 2) completed @endif"></div>
                    <div class="step-circle @if($currentStep == 3) active @elseif($currentStep > 3) completed @endif" data-tooltip="Pengembalian">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>

                <!-- Step 5: Selesai -->
                <div class="progress-bar-step">
                    <div class="step-line @if($currentStep > 3) completed @endif"></div>
                    <div class="step-circle @if($currentStep >= 4) success @endif" data-tooltip="Selesai">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="card-footer">
            @if(is_object($request->status) && $request->status->value == 'pending')
            <!-- Informasi menunggu persetujuan admin -->
            <div class="flex items-center text-amber-600 bg-amber-50 px-4 py-2 rounded-md w-full text-center">
                <div class="mx-auto flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm">Menunggu persetujuan admin</span>
                </div>
            </div>
            @endif

            @if(is_object($request->status) && $request->status->value == 'pending-return')
            <!-- Informasi menunggu persetujuan pengembalian -->
            <div class="flex items-center text-yellow-600 bg-yellow-50 px-4 py-2 rounded-md w-full text-center">
                <div class="mx-auto flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm">Menunggu persetujuan pengembalian</span>
                </div>
            </div>
            @endif

            @if(is_object($request->status) && $request->status->value == 'approved')
            <!-- Tombol Konfirmasi Peminjaman untuk approved request -->
            <button onclick="confirmBorrowed('{{ $request->request_id }}')" class="action-button bg-green-600 text-white px-6 py-2 rounded-full text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 shadow-md">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Konfirmasi Sudah Meminjam
                </div>
            </button>
            <form id="borrow-form-{{ $request->request_id }}" action="{{ route('user.mark-borrowed', $request->request_id) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
            @endif

            @if(is_object($request->status) && ($request->status->value == 'borrowed' || $request->status->value == 'overdue'))
            <!-- Tombol aksi berdasarkan status pelacakan -->
            @php
                // Cek apakah sudah ada pelacakan
                $hasTracking = $request->itemTrackings && $request->itemTrackings->count() > 0;
            @endphp

            @if(!$hasTracking)
                <!-- Jika belum ada pelacakan, tampilkan tombol upload pelacakan -->
                <button onclick="showTrackingModal('{{ $request->request_id }}'); console.log('Tombol Upload Pelacakan diklik untuk ID: {{ $request->request_id }}');" class="action-button bg-[var(--primary)] text-white px-6 py-2 rounded-full text-sm font-medium hover:bg-[#0e447a] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-opacity-50 shadow-md flex-center">
                    <div class="flex items-center justify-center w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Upload Pelacakan
                    </div>
                </button>
            @else
                <!-- Jika sudah ada pelacakan, tampilkan 2 tombol: Update Pelacakan dan Ajukan Pengembalian -->
                <div class="flex flex-col gap-2">
                    <button onclick="showTrackingModal('{{ $request->request_id }}'); console.log('Tombol Update Pelacakan diklik untuk ID: {{ $request->request_id }}');" class="action-button bg-[var(--primary)] text-white px-6 py-2 rounded-full text-sm font-medium hover:bg-[#0e447a] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-opacity-50 shadow-md flex-center">
                        <div class="flex items-center justify-center w-full">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Update Pelacakan
                        </div>
                    </button>
            <button onclick="confirmReturn('{{ $request->request_id }}')" class="action-button bg-yellow-500 text-white px-6 py-2 rounded-full text-sm font-medium hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-50 shadow-md">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Ajukan Pengembalian
                </div>
            </button>
                </div>
            @endif
            <form id="return-form-{{ $request->request_id }}" action="{{ route('user.request-return', $request->request_id) }}" method="POST" class="hidden">
                @csrf
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white p-6 rounded-lg shadow-md">
        <div class="text-center py-8">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <p class="text-gray-500 text-lg mb-4">Kamu belum memiliki riwayat peminjaman apapun.</p>
            <a href="{{ route('user.peminjaman') }}" class="action-button inline-flex items-center bg-[var(--primary)] text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-[#0e447a] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:ring-opacity-50 shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Pinjam Barang Sekarang
            </a>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $borrowRequests->links() }}
</div>

<!-- Modal Upload Pelacakan -->
<div id="tracking-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="modal-content w-full max-w-lg mx-4 bg-white rounded-lg shadow-xl" id="modal-content" style="max-height: 65vh; overflow-y: auto;">
        <div class="modal-header p-3 bg-[var(--primary)] text-white rounded-t-lg flex justify-between items-center">
            <h3 class="modal-title text-lg font-medium" id="modal-title">Upload Pelacakan Barang</h3>
            <button type="button" id="close-modal-btn" class="text-white hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-3 overflow-y-auto">
            <!-- Fallback Form yang akan digunakan jika JavaScript gagal -->
            <form id="fallback-tracking-form" action="{{ url('/user/pelacakan/0/store') }}" method="POST" enctype="multipart/form-data" style="display:none;">
                @csrf
                <input type="hidden" id="fallback_request_id" name="request_id">
                <input type="hidden" name="is_fallback" value="1">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="fallback_location">Lokasi Barang</label>
                    <input type="text" id="fallback_location" name="location" class="tracking-form-input" required>
                    <!-- Tambahkan input tersembunyi untuk koordinat -->
                    <input type="hidden" id="fallback_latitude" name="latitude">
                    <input type="hidden" id="fallback_longitude" name="longitude">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="fallback_notes">Catatan (Opsional)</label>
                    <textarea id="fallback_notes" name="notes" rows="3" class="tracking-form-input"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="fallback_photo">Foto Barang</label>
                    <input type="file" id="fallback_photo" name="photo" class="block w-full" accept="image/*" required>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">
                        Submit Form Tradisional
                    </button>
                </div>
            </form>

            <!-- Form AJAX utama -->
            <form id="tracking-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" id="request_id" name="request_id">

                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="location">Lokasi Barang</label>
                    <div class="input-group">
                        <input type="text" id="location" name="location" class="tracking-form-input flex-grow" placeholder="Mendapatkan lokasi..." readonly>
                        <button type="button" id="refresh-location" class="refresh-location-btn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="location-status" class="text-sm mt-1 text-gray-500">Mencari lokasi Anda...</div>

                    <!-- Hidden inputs for coordinates -->
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                    <!-- Map container -->
                    <div id="location-map" class="h-24 bg-gray-100 rounded-lg mt-1"></div>
                </div>

                <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="notes">Catatan (Opsional)</label>
                    <textarea id="notes" name="notes" rows="2" class="tracking-form-input" placeholder="Kondisi barang, penggunaan, dll"></textarea>
                </div>

                <div class="mb-2" id="photo-upload-container">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Barang</label>

                    <!-- Kamera preview (menjadi default) -->
                    <div id="camera-area" class="mb-2">
                        <div class="flex flex-col items-center justify-center h-32 bg-gray-100 rounded-lg">
                            <p class="text-sm text-gray-600">Kamera akan dimulai saat modal dibuka</p>
                        </div>
                    </div>

                    <!-- Area preview foto -->
                    <div id="image-preview" class="hidden mt-2 mb-2 relative">
                        <div class="relative rounded-lg overflow-hidden">
                            <img id="preview" src="#" alt="Preview" class="w-full h-auto rounded-lg">
                            <button type="button" id="remove-image" class="absolute top-2 right-2 bg-black bg-opacity-50 rounded-full p-1 text-white hover:bg-opacity-70 focus:outline-none transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- File input tersembunyi -->
                    <input type="file" id="photo" name="photo" class="hidden" accept="image/*" required>

                    <canvas id="camera-canvas" class="hidden"></canvas>

                    <!-- Pesan bantuan -->
                    <div class="mt-1 text-sm text-gray-500 text-center">
                        <p>Arahkan kamera ke barang dan ambil foto yang jelas</p>
                        <p class="text-xs mt-1">Pastikan pencahayaan cukup dan barang terlihat jelas</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-3">
                    <button type="button" id="cancel-tracking" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                    <button type="submit" id="submit-tracking" class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Pelacakan
                    </button>
                </div>

                <!-- Tombol fallback jika AJAX gagal -->
                <div class="mt-4 hidden" id="fallback-container">
                    <hr class="my-4">
                    <p class="text-center text-sm text-red-500 mb-4">Jika upload di atas gagal, coba metode upload tradisional:</p>
                    <button type="button" id="use-fallback-btn" class="w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Gunakan Metode Upload Alternatif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Lucide Icons UMD untuk konsistensi dengan admin -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
<!-- Leaflet JS untuk peta -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Inisialisasi variabel global yang dibutuhkan di JS
    window.flashMessages = {
        @if(session('success'))
            success: "{{ session('success') }}",
        @endif
        @if(session('error'))
            error: "{{ session('error') }}",
        @endif
        @if(session('warning'))
            warning: "{{ session('warning') }}",
        @endif
        @if(session('info'))
            info: "{{ session('info') }}",
        @endif
    };

    // Inisialisasi data auto refresh
    window.borrowData = {
        count: {{ $borrowRequests->count() }},
        items: [
            @foreach($borrowRequests->whereNotIn('status', ['completed', 'rejected']) as $request)
            {
                id: {{ $request->request_id }},
                status: '{{ $request->status->value }}',
                updated_at: {{ $request->updated_at->timestamp }}
            },
            @endforeach
        ]
    };

    // Debug informasi user untuk troubleshooting
    console.log('User authenticated:', {{ Auth::check() ? 'true' : 'false' }});
</script>
<script src="{{ asset('assets/user/js/status-peminjaman.js') }}"></script>
@endpush
