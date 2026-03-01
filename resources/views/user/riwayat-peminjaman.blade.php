@extends('layouts.user.user-layout')

@section('title', 'Riwayat Peminjaman - Lab IoT Vokasi UB')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    :root {
        --primary-rgb: 13, 71, 116; /* RGB value of primary color */
    }

    .borrow-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 10px -2px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        background: white;
        position: relative;
    }

    .borrow-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .borrow-card .card-header {
        padding: 1rem;
        background: var(--primary);
        color: white;
    }

    .borrow-card .card-content {
        padding: 1.5rem;
    }

    .borrow-card .card-footer {
        display: flex;
        justify-content: space-between;
        border-top: 1px solid #e5e7eb;
        padding: 1rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.75rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .status-badge:hover {
        transform: scale(1.05);
    }

    .item-image-container {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        background-position: center;
        background-repeat: no-repeat;
        background-size: contain;
    }

    .item-image-container:hover {
        transform: scale(1.1);
    }

    .info-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .info-item i,
    .info-item svg {
        margin-right: 0.5rem;
        color: var(--primary);
    }

    .action-button {
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .action-button:hover {
        transform: translateY(-2px);
    }

    .purpose-container {
        background-color: #f9fafb;
        padding: 0.75rem;
        border-radius: 0.5rem;
        border-left: 4px solid var(--primary);
        margin-top: 0.75rem;
        margin-bottom: 1rem;
    }

    /* Timeline modal styling */
    .timeline-container {
        position: relative;
        padding-left: 2rem;
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 8px;
        width: 3px;
        background-color: var(--primary);
        border-radius: 999px;
    }

    .timeline-point {
        position: absolute;
        left: -4px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background-color: white;
        border: 3px solid var(--primary);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
        z-index: 10;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .timeline-content {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1rem;
        border: 1px solid #edf2f7;
        transition: all 0.2s ease;
    }

    .timeline-content:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .timeline-date {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .timeline-location {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .timeline-note {
        font-size: 0.875rem;
        color: #4b5563;
    }

    /* Tab styling */
    .tab-active {
        color: var(--primary);
        border-color: var(--primary);
    }

    .detail-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .detail-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .detail-info-item {
        display: flex;
        margin-bottom: 0.5rem;
    }

    .detail-info-label {
        min-width: 140px;
        font-weight: 500;
        color: #4b5563;
    }

    .detail-info-value {
        flex: 1;
        color: #1f2937;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .fadeIn {
        animation: fadeIn 0.3s ease forwards;
    }

    /* Hide scrollbar but allow scrolling */
    .scrollbar-hide {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    /* Modal styles for better positioning */
    .modal-content-wrapper {
        max-height: 85vh; /* Limit height on mobile */
        transition: all 0.2s ease;
    }

    /* Improve modal on mobile */
    @media (max-width: 640px) {
        .modal-content-wrapper {
            max-height: 80vh;
        }
    }

    /* Lock scroll pada body saat modal terbuka */
    body.modal-open {
        overflow: hidden !important;
        height: 100vh !important;
        width: 100vw !important;
        position: fixed !important;
        margin: 0 !important;
        padding: 0 !important;
        top: 0 !important;
        left: 0 !important;
    }

    /* Modal styling untuk pemosisian di tengah */
    #detailModal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 50;
        overflow-y: auto;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px) !important;
    }

    #detailModal.show {
        display: flex !important;
        opacity: 1 !important;
    }

    #detailModal > div:first-child {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: -1;
    }

    #detailModal .modal-content-wrapper {
        width: 100%;
        max-width: 42rem; /* 672px */
        max-height: 90vh;
        margin: auto;
        background: white;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0,0,0,0.3) !important;
    }

    #detailModal.show .modal-content-wrapper {
        transform: none !important;
        opacity: 1 !important;
    }

    /* Hapus animasi untuk modal */
    #detailModal .modal-content-wrapper {
        opacity: 1;
        transform: none;
    }

    /* Sembunyikan scrollbar pada body tapi tetap bisa scroll di modal */
    body.fixed {
        position: fixed;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    #modal-item-image-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
    }

    /* Gaya tambahan untuk memperbaiki tampilan modal */
    #detailModal .flex span {
        line-height: 1.3;
    }

    #detailModal .item-image-container {
        min-width: 64px;
        min-height: 64px;
    }

    @media (min-width: 768px) {
        #detailModal .modal-content-wrapper {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
    }

    /* CSS untuk tampilan image thumbnail dengan indikator click */
    .image-thumbnail-container {
        position: relative;
        overflow: hidden;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        cursor: pointer;
    }

    .image-zoom-indicator {
        position: absolute;
        top: 0;
        right: 0;
        background-color: rgba(0,0,0,0.6);
        color: white;
        padding: 2px 6px;
        font-size: 0.75rem;
        border-radius: 0 0.5rem 0 0.5rem;
        z-index: 5;
    }

    .image-thumbnail-container:hover img {
        transform: scale(1.05);
    }

    /* Search spinner styling */
    .search-spinner {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        border: 2px solid rgba(var(--primary-rgb), 0.2);
        border-top-color: var(--primary);
        border-radius: 50%;
        display: none;
    }

    .search-spinner.active {
        display: block;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        0% { transform: translateY(-50%) rotate(0deg); }
        100% { transform: translateY(-50%) rotate(360deg); }
    }

    /* Gaya untuk filter container */
    .filter-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
    }

    .filter-label {
        font-weight: 500;
        color: #374151;
        margin-right: 6px;
        white-space: nowrap;
    }

    .filter-item {
        display: flex;
        align-items: center;
    }

    .filter-input {
        min-width: 240px;
        width: 100%;
    }

    .filter-select {
        min-width: 180px;
        width: 100%;
    }

    /* Responsivitas mobile */
    @media (max-width: 640px) {
        .filter-container {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .filter-item {
            width: 100%;
        }

        .filter-label {
            width: 35%;
            min-width: 100px;
        }

        .filter-input,
        .filter-select {
            min-width: 0;
            flex: 1;
        }

        .borrow-card .card-content {
            padding: 1rem;
        }

        /* Perbaiki tampilan kartu di mobile */
        .borrow-card .card-content > .flex {
            flex-direction: column;
        }

        .borrow-card .card-content .item-image-container {
            width: 100%;
            height: 120px;
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .purpose-container {
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
        }

        /* Peningkatan tampilan modal untuk mobile */
        #detailModal .modal-content-wrapper {
            margin: 0.5rem;
        }

        #modal-tracking-timeline .flex {
            flex-direction: column;
        }

        #modal-tracking-timeline .md\:w-1\/3 {
            width: 100%;
            margin-top: 0.5rem;
        }
    }

    /* Perbaikan untuk tablet */
    @media (min-width: 641px) and (max-width: 768px) {
        .filter-input {
            min-width: 180px;
        }

        .filter-select {
            min-width: 150px;
        }

        .grid.grid-cols-1.md\:grid-cols-2.lg\:grid-cols-3 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('content')
    <!-- Header Section -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[var(--primary)]">Riwayat Peminjaman Barang</h1>
        <p class="text-gray-600">Lihat semua peminjaman yang sudah selesai atau ditolak.</p>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form action="{{ route('user.riwayat-peminjaman') }}" method="GET" id="filterForm">
            <div class="filter-container">
                <div class="filter-item">
                    <span class="filter-label">Rentang Tanggal</span>
                    <div class="relative filter-input">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <input type="text" id="date-range" class="bg-white border border-gray-300 pl-10 py-2 pr-10 rounded-md focus:outline-none w-full focus:border-[var(--primary)] focus:ring focus:ring-[var(--primary)] focus:ring-opacity-20 transition-all duration-200" placeholder="Pilih rentang tanggal" readonly>
                        <!-- Tombol reset tersembunyi yang muncul saat ada tanggal -->
                        <div id="clear-date" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer {{ request('start_date') ? '' : 'hidden' }}">
                            <svg class="w-4 h-4 text-gray-400 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                        <!-- Input tersembunyi untuk menyimpan start_date dan end_date -->
                        <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div>
            </div>

                <div class="filter-item">
                    <span class="filter-label">Status</span>
                    <div class="relative filter-select">
                        <select name="status" id="status" class="bg-white w-full rounded-md border border-gray-300 shadow-sm py-2 pl-3 pr-10 focus:border-[var(--primary)] focus:outline-none focus:ring focus:ring-[var(--primary)] focus:ring-opacity-20 transition-all duration-200 appearance-none text-gray-900" style="color: #111827;">
                            <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Tombol Filter yang akan muncul di mobile -->
                <div class="filter-item md:hidden">
                    <button type="submit" class="w-full px-4 py-2 bg-[var(--primary)] text-white rounded-md">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Daftar Riwayat Peminjaman -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($borrowRequests as $request)
            <div class="borrow-card" data-request-id="{{ $request->request_id }}">
                <div class="card-header">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold truncate mr-2">{{ $request->item->name }}</h3>

                    <!-- Badge Status -->
                        <span class="status-badge whitespace-nowrap
                            @if($request->status->value === 'completed')
                                bg-green-100 text-green-800
                            @elseif($request->status->value === 'rejected')
                                bg-red-100 text-red-800
                            @else
                                bg-gray-100 text-gray-800
                            @endif">
                        @if($request->status->value === 'completed')
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Selesai
                        @elseif($request->status->value === 'rejected')
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Ditolak
                            @else
                                {{ $request->getStatusLabel() }}
                            @endif
                            </span>
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
                                <svg class="w-4 h-4 text-[var(--primary)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-gray-700">Deadline: <span class="font-semibold">{{ \Carbon\Carbon::parse($request->return_deadline)->format('d M Y') }}</span></span>
                        </div>

                            <div class="info-item">
                                <svg class="w-4 h-4 text-[var(--primary)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="text-gray-700">Pelacakan: <span class="font-semibold">{{ $request->itemTrackings ? $request->itemTrackings->count() : '0' }} kali</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi tambahan untuk catatan admin - hanya ditampilkan jika ada catatan -->
                    @if($request->notes && trim($request->notes) !== '')
                    <div class="purpose-container mt-3 mb-2">
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Catatan Admin:</span>
                            {{ $request->notes }}
                        </p>
                    </div>
                    @endif
                </div>

                <div class="card-footer">
                    <button type="button" class="detail-btn action-button bg-[var(--primary)] text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary)] transition w-full">
                        <div class="flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Detail Peminjaman
                        </div>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl p-10 text-center shadow-md">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-xl font-medium text-gray-700 mb-2">Belum Ada Riwayat Peminjaman</h3>
                <p class="text-gray-500 mb-4">Anda belum memiliki riwayat peminjaman yang selesai atau ditolak.</p>
                <a href="{{ route('user.peminjaman') }}" class="inline-flex items-center bg-[var(--primary)] text-white px-5 py-2 rounded-lg hover:bg-[var(--button-hover)] transition duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Ajukan Peminjaman
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($borrowRequests->count() > 0)
        <div class="mt-8">
            {{ $borrowRequests->appends(request()->query())->links() }}
        </div>
    @endif

    <!-- Modal Detail Peminjaman -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Container untuk centering modal di tengah viewport -->
        <div class="fixed inset-0 flex items-center justify-center z-10">
            <div class="modal-content-wrapper bg-white rounded-lg shadow-xl w-full sm:max-w-2xl max-h-[60vh] flex flex-col overflow-hidden m-4 my-8" style="max-height: 60vh;">
                <!-- Header modal -->
            <div class="bg-[var(--primary)] px-4 py-3 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-lg font-medium text-white" id="modal-title">Detail Peminjaman Barang</h3>
                <button type="button" class="modal-close text-white hover:text-gray-200 focus:outline-none" onclick="closeModal()">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

                <!-- Content area dengan scrollbar tersembunyi -->
                <div id="modal-content" class="bg-white p-4 overflow-y-auto scrollbar-hide" style="max-height: calc(60vh - 56px); color: #111827;">
                    <div class="border border-gray-100 rounded-lg p-4 shadow-sm mb-4">
                        <!-- Informasi Barang -->
                        <div class="flex items-center gap-4 border-b border-gray-200 pb-4 mb-4">
                            <!-- Gambar Barang -->
                            <div class="item-image-container bg-gray-100 flex items-center justify-center h-28 w-28 rounded-lg flex-shrink-0" id="modal-item-image-container">
                                <img id="modal-item-image" src="" alt="Gambar Barang" class="w-full h-full object-contain rounded-lg">
                            </div>

                            <!-- Informasi Dasar Barang -->
                            <div class="flex-1">
                                <h3 id="modal-item-name" class="font-semibold text-lg text-[var(--primary)] mb-1" style="color: var(--primary);"></h3>
                                <div class="flex items-center mb-1">
                                    <span class="text-xs text-gray-600 mr-2">ID:</span>
                                    <span id="modal-item-id" class="text-xs font-medium text-gray-700" style="color: #374151;"></span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-xs text-gray-600 mr-2">Kategori:</span>
                                    <span id="modal-item-category" class="text-xs font-medium bg-gray-100 px-2 py-0.5 rounded-md" style="color: #374151;"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Peminjaman -->
                        <div>
                            <!-- Status -->
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-medium text-base text-gray-700">Detail Peminjaman:</h4>
                                <span id="modal-item-status" class="inline-flex text-sm px-3 py-1 rounded-full font-medium"></span>
                    </div>

                        <!-- Informasi Detail -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">ID Peminjaman:</span>
                                <span id="modal-borrow-id" class="text-sm text-gray-800" style="color: #1f2937;"></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">Jumlah:</span>
                                <span id="modal-item-quantity" class="text-sm text-gray-800" style="color: #1f2937;"></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">Tanggal Pinjam:</span>
                                <span id="modal-borrow-date" class="text-sm text-gray-800" style="color: #1f2937;"></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">Deadline:</span>
                                <span id="modal-return-deadline" class="text-sm text-gray-800" style="color: #1f2937;"></span>
                            </div>
                            <div class="flex" id="modal-return-date-container">
                                <span class="w-32 text-sm font-medium text-gray-600">Pengembalian:</span>
                                <span id="modal-return-date" class="text-sm text-gray-800" style="color: #1f2937;"></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">Tujuan:</span>
                                <span id="modal-purpose" class="text-sm text-gray-800" style="color: #1f2937;"></span>
                            </div>
                            <!-- Catatan admin hanya ditampilkan jika ada -->
                            <div id="modal-admin-notes-container" class="flex col-span-2" style="display: none;">
                                <span class="w-32 text-sm font-medium text-gray-600">Catatan Admin:</span>
                                <span id="modal-admin-notes" class="text-sm text-gray-800" style="color: #1f2937;"></span>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Pelacakan -->
                <div class="border border-gray-100 rounded-lg p-3 shadow-sm mt-4">
                    <h4 class="text-white font-semibold text-base bg-[var(--primary)] rounded-md p-2 mb-3 shadow-sm">
                        Riwayat Pelacakan
                    </h4>
                    <div id="modal-tracking-timeline" class="py-1 min-h-[100px]">
                        <!-- Konten pelacakan akan diisi via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk menampilkan gambar dalam ukuran penuh -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="relative max-w-4xl max-h-[90vh] w-full mx-4">
            <button id="closeImageModal" class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-lg text-gray-800 hover:text-gray-600 transition-colors z-30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <img id="fullSizeImage" src="" alt="Gambar Pelacakan" class="max-w-full max-h-[85vh] mx-auto object-contain bg-white p-2 rounded-lg shadow-xl">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="{{ asset('assets/user/js/riwayat-peminjaman.js') }}"></script>
<script>
    // Debug untuk melihat error JavaScript
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.message, 'at', e.filename, 'line', e.lineno);
    });

    // Pastikan semua filter dapat bekerja bersama
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Filter page loaded with parameters:', {
            status: '{{ request('status') }}',
            start_date: '{{ request('start_date') }}',
            end_date: '{{ request('end_date') }}'
        });

        // Inisialisasi filter form
        initFilterForm();
    });

    // Fungsi untuk inisialisasi form filter
    function initFilterForm() {
        // Inisialisasi date range picker
        const dateRangePicker = flatpickr("#date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            locale: {
                rangeSeparator: " - "
            },
            position: "auto",
            // Set nilai awal jika ada
            defaultDate: [
                "{{ request('start_date') }}",
                "{{ request('end_date') }}"
            ].filter(date => date !== ""),
            onChange: function(selectedDates, dateStr) {
                // Perbarui nilai hidden input saat tanggal berubah
                if (selectedDates.length === 1) {
                    // Jika baru memilih satu tanggal, set start_date saja
                    document.getElementById('start_date').value = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                    document.getElementById('end_date').value = "";

                    // Tampilkan tombol reset
                    document.getElementById('clear-date').classList.remove('hidden');
                } else if (selectedDates.length === 2) {
                    // Jika sudah memilih dua tanggal, set start_date dan end_date
                    document.getElementById('start_date').value = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                    document.getElementById('end_date').value = flatpickr.formatDate(selectedDates[1], "Y-m-d");

                    // Tampilkan tombol reset
                    document.getElementById('clear-date').classList.remove('hidden');

                    // Submit form
                    document.getElementById('filterForm').submit();
                }
            }
        });

        // Tampilkan nilai yang dipilih saat ini
        if ("{{ request('start_date') }}" && "{{ request('end_date') }}") {
            const startDate = new Date("{{ request('start_date') }}");
            const endDate = new Date("{{ request('end_date') }}");
            const formattedStartDate = startDate.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});
            const formattedEndDate = endDate.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});

            console.log('Current date range:', formattedStartDate + ' - ' + formattedEndDate);
        }

        // Event listener untuk tombol reset tanggal
        document.getElementById('clear-date').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Reset nilai date range picker
            dateRangePicker.clear();

            // Reset nilai hidden input
            document.getElementById('start_date').value = "";
            document.getElementById('end_date').value = "";

            // Sembunyikan tombol reset
            this.classList.add('hidden');

            // Submit form untuk refresh data
            document.getElementById('filterForm').submit();
        });

        // Event listener untuk status filter
        document.getElementById('status').addEventListener('change', function() {
            // Log nilai status yang dipilih
            console.log('Status filter changed to:', this.value);

            // Submit form
            document.getElementById('filterForm').submit();
        });
    }

    // Auto Refresh Setup
    let historyData = {
        count: {{ $borrowRequests->count() }},
        items: [
            @foreach($borrowRequests as $request)
            {
                id: {{ $request->request_id }},
                status: '{{ $request->status->value }}',
                updated_at: {{ $request->updated_at->timestamp }}
            },
            @if(!$loop->last),@endif
            @endforeach
        ]
    };

    let refreshInterval;
    let isPageActive = true;

    // Mulai polling saat halaman dimuat
    startAutoRefresh();

    // Fungsi untuk melakukan polling data baru
    function startAutoRefresh() {
        refreshInterval = setInterval(checkForHistoryChanges, 10000); // Cek setiap 10 detik
    }

    // Fungsi untuk memeriksa perubahan riwayat
    function checkForHistoryChanges() {
        if (!isPageActive) return; // Jangan refresh jika tab tidak aktif

        // Tambahkan parameter filter yang ada saat ini
        const currentStatus = '{{ request('status') }}';
        const startDate = '{{ request('start_date') }}';
        const endDate = '{{ request('end_date') }}';

        let queryParams = '?_=' + new Date().getTime();
        if (currentStatus && currentStatus !== 'all') {
            queryParams += '&status=' + currentStatus;
        }
        if (startDate) {
            queryParams += '&start_date=' + startDate;
        }
        if (endDate) {
            queryParams += '&end_date=' + endDate;
        }

        fetch('/user/check-borrow-history' + queryParams, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Cek perubahan jumlah
            if (data.count !== historyData.count) {
                document.location.reload();
                return;
            }

            // Cek perubahan status
            let hasChanges = false;

            if (data.data && data.data.length > 0) {
                data.data.forEach(item => {
                    // Cari item yang cocok dari data yang ada
                    let existingItem = historyData.items.find(x => x.id === item.id);

                    // Jika status berubah atau timestamp update berbeda, maka refresh
                    if (!existingItem ||
                        existingItem.status !== item.status ||
                        existingItem.updated_at !== item.updated_at) {
                        hasChanges = true;
                    }
                });
            }

            if (hasChanges) {
                document.location.reload();
            }
        })
        .catch(error => console.error('Error checking for history changes:', error));
    }

    // Deteksi tab aktif/tidak aktif untuk menghemat resource
    document.addEventListener('visibilitychange', function() {
        isPageActive = document.visibilityState === 'visible';

        if (isPageActive) {
            // Jika tab menjadi aktif, cek segera dan mulai polling lagi
            checkForHistoryChanges();
            if (!refreshInterval) startAutoRefresh();
        } else {
            // Jika tab tidak aktif, hentikan polling
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    });
</script>
@endpush
