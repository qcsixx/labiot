@extends('layouts.admin.admin-layout')

@section('title', 'Laporan Peminjaman')

@push('styles')
<style>
    /* Root variables */
    :root {
        --primary-rgb: 13, 71, 116; /* RGB value of primary color */
    }

    /* Timeline styling */
    .timeline .absolute {
        z-index: 10;
    }

    /* Modal styling */
    .modal-content-wrapper {
        max-height: 85vh;
        overflow-y: auto;
    }

    /* Scrollbar styling */
    .modal-content-wrapper::-webkit-scrollbar {
        width: 8px;
    }

    .modal-content-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .modal-content-wrapper::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
    }

    .modal-content-wrapper::-webkit-scrollbar-thumb:hover {
        background: #0d3a73;
    }

    /* Item image container styling */
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

    /* Status badge styling */
    .status-badge-modal {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.75rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* Timeline container */
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

    /* Detail modal styling */
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
    }

    #detailModal.flex {
        display: flex !important;
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
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateY(0);
        transition: transform 0.3s ease-out;
    }

    /* Animasi untuk modal */
    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    #detailModal .modal-content-wrapper {
        animation: modalFadeIn 0.3s ease-out;
    }

    /* Sembunyikan scrollbar pada body tapi tetap bisa scroll di modal */
    body.fixed {
        position: fixed;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    /* Hide scrollbar but allow scrolling */
    .scrollbar-hide {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    /* CSS untuk image thumbnail container */
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

    #modal-item-image-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
    }
</style>
@endpush

@section('content')
<!-- Header Utama -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-[var(--primary)] mb-1">Laporan Peminjaman</h1>
    <p class="text-sm text-[var(--secondary)]">Lihat dan ekspor data peminjaman barang di Lab IoT Vokasi UB.</p>
</div>

<!-- Filter dan Pencarian -->
<div class="bg-[var(--bg-color)] rounded-lg p-4 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <!-- Form Filter -->
        <form id="filterForm" action="{{ route('admin.laporan-peminjaman') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="w-auto md:w-72">
                <label class="block text-sm font-medium text-[var(--primary)] mb-1" for="search">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-[var(--primary)] text-lg"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        class="pl-10 w-full rounded-full border border-[var(--primary)] py-2.5 px-4 text-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent"
                        placeholder="Cari nama peminjam atau barang...">
                    <div id="searchSpinner" class="search-spinner"></div>
                </div>
            </div>

            <div class="w-auto">
                <label class="block text-sm font-medium text-[var(--primary)] mb-1" for="date-range">Rentang Tanggal</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar text-[var(--primary)]"></i>
                    </div>
                    <input type="text" id="date-range" class="bg-gray-50 border border-[var(--primary)] pl-10 py-2 pr-10 rounded-md focus:outline-none w-full md:w-[220px] text-gray-900" placeholder="Pilih rentang tanggal" readonly>
                    <!-- Tombol reset tersembunyi yang muncul saat ada tanggal -->
                    <div id="clear-date" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer {{ request('start_date') ? '' : 'hidden' }}">
                        <i class="fas fa-times-circle text-gray-400 hover:text-red-500"></i>
                    </div>
                    <!-- Input tersembunyi untuk menyimpan start_date dan end_date -->
                    <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div>
            </div>

            <div class="w-auto">
                <label class="block text-sm font-medium text-[var(--primary)] mb-1" for="status">Status</label>
                <select name="status" id="status" class="border border-[var(--primary)] p-2 rounded-md focus:outline-none w-full md:w-[180px] text-gray-900 bg-white">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Sedang Dipinjam</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="pending-return" {{ request('status') == 'pending-return' ? 'selected' : '' }}>Pengajuan Pengembalian</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Melewati Deadline</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Semua Dipinjam</option>
                    <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Semua Dikembalikan</option>
                </select>
            </div>
        </form>

        <!-- Export Buttons -->
        <div class="flex items-end gap-3">
            <form action="{{ route('admin.laporan.export-pdf') }}" method="GET" class="inline">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <button type="submit" class="export-button bg-red-600 hover:bg-red-700 flex items-center justify-center w-full">
                    <i class="fas fa-file-pdf text-lg mr-2"></i>
                    <span class="inline-block">Export PDF</span>
                </button>
            </form>
            <form action="{{ route('admin.laporan.export-excel') }}" method="GET" class="inline">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <button type="submit" class="export-button bg-green-600 hover:bg-green-700 flex items-center justify-center w-full">
                    <i class="fas fa-file-excel text-lg mr-2"></i>
                    <span class="inline-block">Export Excel</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Tabel Laporan -->
<div class="bg-[var(--card-bg)] rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto scrollable">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="divide-x divide-gray-200">
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">No</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Peminjam</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Nama Barang</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Jumlah</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Tanggal Pinjam</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Deadline Kembali</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Tujuan Peminjaman</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($borrowRequests as $index => $request)
                    @php
                        $isCompleted = is_string($request->status) ? $request->status == 'completed' : $request->status->value == 'completed';
                        $rowClass = $isCompleted ? 'hover:bg-gray-100 cursor-pointer completed-row' : '';
                    @endphp
                    <tr class="divide-x divide-gray-200 {{ $rowClass }}" @if($isCompleted) data-request-id="{{ $request->request_id }}" @endif>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                            {{ method_exists($borrowRequests, 'currentPage') ?
                                ($borrowRequests->currentPage() - 1) * $borrowRequests->perPage() + $index + 1 :
                                $index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->quantity ?? 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ \Carbon\Carbon::parse($request->borrow_date)->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ \Carbon\Carbon::parse($request->return_deadline)->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->purpose ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-full
                            @if(is_string($request->status) ? $request->status == 'overdue' : $request->status->value == 'overdue')
                                bg-[#F44336] text-white
                            @elseif(is_string($request->status) ? $request->status == 'pending-return' : $request->status->value == 'pending-return')
                                @if(\Carbon\Carbon::parse($request->return_deadline)->startOfDay() < now()->startOfDay())
                                    bg-[#F44336] text-white
                                @else
                                    bg-[#FDB813] text-white
                                @endif
                            @elseif(is_string($request->status) ? $request->status == 'completed' : $request->status->value == 'completed')
                                @if(isset($request->return_status) && $request->return_status == 'late')
                                    bg-orange-500 text-white
                                @else
                                    bg-[#10B981] text-white
                                @endif
                            @elseif(is_string($request->status) ? $request->status == 'approved' : $request->status->value == 'approved')
                                bg-[#3B82F6] text-white
                            @elseif(is_string($request->status) ? $request->status == 'rejected' : $request->status->value == 'rejected')
                                bg-[#EF4444] text-white
                            @elseif(is_string($request->status) ? $request->status == 'borrowed' : $request->status->value == 'borrowed')
                                bg-[#8B5CF6] text-white
                            @elseif(is_string($request->status) ? $request->status == 'pending' : $request->status->value == 'pending')
                                bg-[#F59E0B] text-white
                            @elseif(is_string($request->status) ? $request->status == 'cancelled' : $request->status->value == 'cancelled')
                                bg-[#6B7280] text-white
                            @elseif(is_string($request->status) ? $request->status == 'dipinjam' : $request->status->value == 'dipinjam')
                                bg-[#C026D3] text-white
                            @elseif(is_string($request->status) ? $request->status == 'dikembalikan' : $request->status->value == 'dikembalikan')
                                bg-[#14B8A6] text-white
                            @else
                                bg-gray-100 text-gray-800
                            @endif">
                                {{ $request->getStatusLabel() }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i data-lucide="file-x" class="h-12 w-12 text-gray-300 mb-2"></i>
                                <p>Tidak ada data peminjaman</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(method_exists($borrowRequests, 'hasPages') && $borrowRequests->hasPages())
    <div class="px-6 py-4 bg-white border-t border-gray-200">
        {{ $borrowRequests->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Modal Detail Peminjaman -->
<div id="detailModal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

    <!-- Container dengan padding top untuk memberi jarak dari navbar -->
    <div class="fixed inset-0 flex items-center justify-center z-10 pt-16">
        <div class="modal-content-wrapper bg-white rounded-lg shadow-xl w-full sm:max-w-2xl max-h-[80vh] flex flex-col overflow-hidden m-4">
            <!-- Header modal -->
            <div class="bg-[var(--primary)] px-4 py-3 flex justify-between items-center sticky top-0 z-10">
                <h3 class="text-lg font-medium text-white" id="modal-title">Detail Peminjaman Barang</h3>
                <button type="button" class="modal-close text-white hover:text-gray-200 focus:outline-none" onclick="closeDetailModal()">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Loading state -->
            <div id="modal-loader" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[var(--primary)]"></div>
            </div>

            <!-- Content area dengan scrollbar tersembunyi -->
            <div id="detailContent" class="bg-white p-4 hidden overflow-y-auto scrollbar-hide scrollable" style="max-height: calc(80vh - 56px);">
                <div class="border border-gray-100 rounded-lg p-4 shadow-sm mb-4">
                    <!-- Informasi Barang -->
                    <div class="flex items-center gap-4 border-b border-gray-200 pb-4 mb-4">
                        <!-- Gambar Barang -->
                        <div class="item-image-container bg-gray-100 flex items-center justify-center h-28 w-28 rounded-lg flex-shrink-0" id="modal-item-image-container">
                            <img id="modal-item-image" src="" alt="Gambar Barang" class="w-full h-full object-contain rounded-lg">
                        </div>

                        <!-- Informasi Dasar Barang -->
                        <div class="flex-1">
                            <h3 id="modal-item-name" class="font-semibold text-lg text-[var(--primary)] mb-1"></h3>
                            <div class="flex items-center mb-1">
                                <span class="text-xs text-gray-600 mr-2">ID:</span>
                                <span id="modal-item-id" class="text-xs font-medium text-gray-700"></span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-xs text-gray-600 mr-2">Kategori:</span>
                                <span id="modal-item-category" class="text-xs font-medium bg-gray-100 px-2 py-0.5 rounded-md"></span>
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
                                <span id="modal-borrow-id" class="text-sm text-gray-800"></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">Jumlah:</span>
                                <span id="modal-item-quantity" class="text-sm text-gray-800"></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">Tanggal Pinjam:</span>
                                <span id="modal-borrow-date" class="text-sm text-gray-800"></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">Deadline:</span>
                                <span id="modal-return-deadline" class="text-sm text-gray-800"></span>
                            </div>
                            <div class="flex" id="modal-return-date-container">
                                <span class="w-32 text-sm font-medium text-gray-600">Pengembalian:</span>
                                <span id="modal-return-date" class="text-sm text-gray-800"></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-sm font-medium text-gray-600">Tujuan:</span>
                                <span id="modal-purpose" class="text-sm text-gray-800"></span>
                            </div>
                            <!-- Catatan admin hanya ditampilkan jika ada -->
                            <div id="modal-admin-notes-container" class="flex col-span-2" style="display: none;">
                                <span class="w-32 text-sm font-medium text-gray-600">Catatan Admin:</span>
                                <span id="modal-admin-notes" class="text-sm text-gray-800"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Pelacakan -->
                <div class="border border-gray-100 rounded-lg p-3 shadow-sm mt-4">
                    <h4 class="text-white font-semibold text-base bg-[var(--primary)] rounded-md p-2 mb-3 shadow-sm">
                        Riwayat Pelacakan
                    </h4>
                    <div id="modal-tracking-timeline" class="py-1 min-h-[100px] scrollable">
                        <!-- Placeholder loader untuk menunjukkan loading -->
                        <div class="flex justify-center items-center h-24">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[var(--primary)]"></div>
                        </div>
                        <!-- Konten pelacakan akan diisi via JavaScript -->
                    </div>
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

@push('scripts')
<script>
    // Inisialisasi date range picker
    const dateRangePicker = flatpickr("#date-range", {
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d M Y",
        locale: {
            rangeSeparator: " s/d "
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
                document.getElementById('searchSpinner').classList.add('active');
                document.getElementById('filterForm').submit();
            }
        }
    });

    // Event listener untuk tombol reset tanggal
    document.getElementById('clear-date').addEventListener('click', function() {
        // Reset nilai date range picker
        dateRangePicker.clear();

        // Reset nilai hidden input
        document.getElementById('start_date').value = "";
        document.getElementById('end_date').value = "";

        // Sembunyikan tombol reset
        this.classList.add('hidden');

        // Submit form untuk refresh data
        document.getElementById('searchSpinner').classList.add('active');
        document.getElementById('filterForm').submit();
    });

    // Auto-submit saat input berubah
    document.addEventListener('DOMContentLoaded', function() {
        // Status change
        const statusInput = document.getElementById('status');
        statusInput.addEventListener('change', function() {
            document.getElementById('searchSpinner').classList.add('active');
            document.getElementById('filterForm').submit();
        });

        // Search input dengan debounce
        const searchInput = document.getElementById('search');
        let searchTimer;

        searchInput.addEventListener('input', function() {
            document.getElementById('searchSpinner').classList.add('active');

            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                document.getElementById('filterForm').submit();
            }, 500); // Delay 500ms untuk menghindari terlalu banyak request
        });

        // Enter key pada search
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                document.getElementById('searchSpinner').classList.add('active');
                document.getElementById('filterForm').submit();
            }
        });

        // Tambahkan event listener untuk baris dengan status completed
        const completedRows = document.querySelectorAll('.completed-row');
        completedRows.forEach(row => {
            row.addEventListener('click', function() {
                const requestId = this.getAttribute('data-request-id');
                showDetailModal(requestId);
            });
        });
    });

    // Fungsi untuk menampilkan modal detail
    function showDetailModal(requestId) {
        console.log('Menampilkan detail untuk request ID:', requestId);

        const modal = document.getElementById('detailModal');
        const loader = document.getElementById('modal-loader');
        const detailContent = document.getElementById('detailContent');

        // Tampilkan modal dan loader
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loader.classList.remove('hidden');
        detailContent.classList.add('hidden');

        // Lock scroll pada body
        document.body.classList.add('fixed');

        // Ambil data detail peminjaman
        fetch(`/admin/peminjaman/${requestId}/detail`)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data berhasil diterima:', data);

                // Debug semua properti di data untuk mencari tanggal pengembalian
                console.log('Struktur data lengkap:', JSON.stringify(data, null, 2));

                // Sembunyikan loader, tampilkan konten
                loader.classList.add('hidden');
                detailContent.classList.remove('hidden');

                // Set data item
                const itemName = document.getElementById('modal-item-name');
                const itemId = document.getElementById('modal-item-id');
                const itemCategory = document.getElementById('modal-item-category');
                const itemQuantity = document.getElementById('modal-item-quantity');

                if (itemName) itemName.textContent = data.item.name || 'Tidak ada nama';
                if (itemId) itemId.textContent = data.item_id || data.item.id || 'N/A';
                if (itemCategory) itemCategory.textContent = data.item.category ? data.item.category.name : 'Umum';
                if (itemQuantity) itemQuantity.textContent = (data.quantity || 0) + ' unit';

                // Status peminjaman
                const itemStatus = document.getElementById('modal-item-status');
                let statusLabel = 'Selesai';
                let statusClass = 'bg-green-100 text-green-800 border border-green-300';

                if (itemStatus) {
                    itemStatus.textContent = statusLabel;
                    itemStatus.className = `inline-flex text-sm px-3 py-1 rounded-full font-medium ${statusClass}`;
                }

                // Isi data peminjaman
                const borrowId = document.getElementById('modal-borrow-id');
                const borrowDate = document.getElementById('modal-borrow-date');
                const returnDeadline = document.getElementById('modal-return-deadline');

                if (borrowId) borrowId.textContent = data.request_id || data.id || 'N/A';
                if (borrowDate) borrowDate.textContent = formatDate(data.borrow_date) || 'N/A';
                if (returnDeadline) returnDeadline.textContent = formatDate(data.return_deadline) || 'N/A';

                // Tanggal pengembalian
                const returnDateContainer = document.getElementById('modal-return-date-container');
                const returnDate = document.getElementById('modal-return-date');

                if (returnDateContainer && returnDate) {
                    // Periksa semua kemungkinan field untuk tanggal pengembalian
                    let actualReturnDate = null;

                    // Cek berbagai kemungkinan nama field
                    if (data.actual_return_date) actualReturnDate = data.actual_return_date;
                    else if (data.return_date) actualReturnDate = data.return_date;
                    else if (data.actualReturnDate) actualReturnDate = data.actualReturnDate;

                    console.log('Tanggal pengembalian yang terdeteksi:', actualReturnDate);

                    if (actualReturnDate) {
                        returnDate.textContent = formatDate(actualReturnDate);
                    } else {
                        returnDate.textContent = formatDate(data.return_date || data.actual_return_date || data.borrow_date);
                    }

                    // Selalu tampilkan container
                    returnDateContainer.style.display = 'flex';
                }

                // Tujuan peminjaman
                const purpose = document.getElementById('modal-purpose');
                if (purpose) purpose.textContent = data.purpose || '-';

                // Catatan admin
                const notesContainer = document.getElementById('modal-admin-notes-container');
                const notesElement = document.getElementById('modal-admin-notes');

                if (notesContainer && notesElement) {
                    if (data.notes && data.notes.trim() !== '') {
                        notesElement.textContent = data.notes;
                        notesContainer.style.display = 'flex'; // Tampilkan container
                        console.log('Menampilkan catatan admin');
                    } else {
                        notesContainer.style.display = 'none'; // Sembunyikan jika tidak ada catatan
                        console.log('Tidak ada catatan admin untuk ditampilkan');
                    }
                }

                // Gambar item
                updateItemImage(data);

                // Render tracking data jika tersedia
                const trackingTimeline = document.getElementById('modal-tracking-timeline');
                if (trackingTimeline) {
                    console.log('Mencari data pelacakan dalam respons API...');

                    // Log seluruh data untuk memeriksa struktur
                    console.log('Struktur data lengkap untuk mencari pelacakan:', {
                        data: data,
                        hasTrackings: !!data.trackings,
                        hasItemTrackings: !!data.itemTrackings,
                        hasItem: !!data.item,
                        hasItemWithTrackings: data.item && !!data.item.trackings
                    });

                    // Cari data pelacakan dalam berbagai properti dengan cara yang lebih menyeluruh
                    let trackings = null;

                    // Prioritaskan data pelacakan dari property trackings yang dikirim API
                    if (data.trackings && Array.isArray(data.trackings)) {
                        console.log('Menggunakan data.trackings');
                        trackings = data.trackings;
                    } else if (data.item_trackings && Array.isArray(data.item_trackings)) {
                        console.log('Menggunakan data.item_trackings');
                        trackings = data.item_trackings;
                    } else if (data.itemTrackings && Array.isArray(data.itemTrackings)) {
                        console.log('Menggunakan data.itemTrackings');
                        trackings = data.itemTrackings;
                    } else if (data.item && data.item.trackings && Array.isArray(data.item.trackings)) {
                        console.log('Menggunakan data.item.trackings');
                        trackings = data.item.trackings;
                    } else if (data.item && data.item.item_trackings && Array.isArray(data.item.item_trackings)) {
                        console.log('Menggunakan data.item.item_trackings');
                        trackings = data.item.item_trackings;
                    } else if (data.item && data.item.itemTrackings && Array.isArray(data.item.itemTrackings)) {
                        console.log('Menggunakan data.item.itemTrackings');
                        trackings = data.item.itemTrackings;
                    } else if (data.tracking && Array.isArray(data.tracking)) {
                        console.log('Menggunakan data.tracking');
                        trackings = data.tracking;
                    }

                    console.log('Data pelacakan yang ditemukan:', trackings);
                    console.log('Jumlah data pelacakan:', trackings ? trackings.length : 0);

                    if (trackings && trackings.length > 0) {
                        console.log('Merender timeline dengan data pelacakan yang ditemukan');
                        renderTrackingTimeline(trackings);
                    } else {
                        console.log('Tidak ada data pelacakan yang ditemukan, menampilkan pesan kosong');
                        showEmptyTrackingMessage();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);

                loader.classList.add('hidden');
                detailContent.classList.remove('hidden');

                detailContent.innerHTML = `
                    <div class="text-center text-red-500 py-8">
                        <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                        <p>Gagal memuat data. Silahkan coba lagi.</p>
                        <p class="text-sm mt-2">${error.message}</p>
                    </div>
                `;
            });
    }

    // Render timeline pelacakan
    function renderTrackingTimeline(trackings) {
        console.log('Merender timeline pelacakan dengan data:', trackings);

        const container = document.getElementById('modal-tracking-timeline');
        if (!container) {
            console.error('Container modal-tracking-timeline tidak ditemukan!');
            return;
        }

        try {
            // Validasi data tracking
            if (!Array.isArray(trackings) || trackings.length === 0) {
                showEmptyTrackingMessage();
                return;
            }

            // Urutkan tracking berdasarkan tanggal (terbaru di atas)
            const sortedTrackings = [...trackings].sort((a, b) => {
                const dateA = a.tracking_date || a.date || a.created_at || '';
                const dateB = b.tracking_date || b.date || b.created_at || '';
                return new Date(dateB) - new Date(dateA);
            });

            const totalEntries = sortedTrackings.length;

            // Container timeline dengan header
            let timelineHTML = `
            <div class="bg-gray-50 rounded-lg p-4 shadow-md border border-gray-100">
                <h4 class="font-bold text-[var(--primary)] mb-6 text-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-[#F59E0B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    PROGRESS PELACAKAN BARANG
                </h4>

                <!-- Timeline Container -->
                <div class="flex flex-col">
            `;

            // Tampilkan entries dari atas ke bawah (terbaru di atas)
            sortedTrackings.forEach((tracking, index) => {
                const isFirst = index === 0; // Ini adalah yang terbaru (paling atas)
                const isLast = index === totalEntries - 1; // Ini adalah yang terlama (paling bawah)
                const stepNumber = totalEntries - index; // Angka step dibalik

                // Tentukan status dan warna untuk step ini
                let stepStatus = isFirst ? "current" : "completed";
                // Warna utama adalah biru untuk buletan
                let statusColor = "var(--primary)";
                let stepTitle = isLast ? "Mulai Pelacakan" : (isFirst ? "Pelacakan Terakhir" : `Pelacakan ke-${stepNumber}`);

                // Ambil tanggal tracking dari berbagai kemungkinan field
                const trackingDate = tracking.tracking_date || tracking.date || tracking.created_at || '';

                // Ambil lokasi tracking dari berbagai kemungkinan field
                const trackingLocation = tracking.location || tracking.lokasi || '';

                // Ambil notes tracking dari berbagai kemungkinan field
                const trackingNotes = tracking.notes || tracking.note || tracking.catatan || tracking.deskripsi || tracking.description || '';

                // Tentukan URL foto dengan memeriksa berbagai format
                let photoUrl = null;
                if (tracking.photo) {
                    if (typeof tracking.photo === 'string') {
                        if (tracking.photo.startsWith('http')) {
                            photoUrl = tracking.photo;
                        } else {
                            photoUrl = '/storage/' + tracking.photo;
                        }
                    }
                } else if (tracking.photo_url) {
                    photoUrl = tracking.photo_url;
                } else if (tracking.image_path) {
                    photoUrl = '/storage/' + tracking.image_path.replace(/^storage\//, '');
                } else if (tracking.image) {
                    if (tracking.image.startsWith('http')) {
                        photoUrl = tracking.image;
                    } else {
                        photoUrl = '/storage/' + tracking.image;
                    }
                }

                timelineHTML += `
                    <!-- Step ${stepNumber} -->
                    <div class="flex">
                        <!-- Left - Timeline Indicator -->
                        <div class="flex flex-col items-center mr-4">
                            <!-- Step Circle with Number -->
                            <div class="flex items-center justify-center w-10 h-10 rounded-full
                                bg-[var(--primary)]
                                text-white font-bold text-lg shadow-md z-10">
                                ${stepStatus === "completed" ?
                                    '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>' :
                                    stepNumber
                                }
                            </div>

                            <!-- Connector Line (kecuali di step terakhir) -->
                            ${!isLast ? `
                            <div class="w-1 bg-[#F59E0B] h-full rounded-full mx-auto my-1"></div>
                            ` : ''}
                        </div>

                        <!-- Right - Content Card -->
                        <div class="flex-1 pb-8 ${isLast ? '' : 'mb-2'}">
                            <!-- Step Title -->
                            <div class="flex items-center mb-1">
                                <h5 class="font-bold text-md" style="color: ${statusColor}">
                                    ${stepTitle}
                                </h5>
                                <span class="ml-2 text-xs font-medium text-gray-500">
                                    ${trackingDate ? formatDate(trackingDate, true) : '-'}
                                </span>
                            </div>

                            <!-- Step Content Card -->
                            <div class="bg-white rounded-lg shadow p-4 border-l-4" style="border-left-color: ${statusColor}">
                                <div class="flex flex-col md:flex-row gap-4">
                                    <!-- Tracking Information -->
                                    <div class="flex-1">
                                        ${trackingLocation ? `
                                        <div class="mb-2 flex items-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-[${statusColor}] flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                            <div>
                                                <span class="font-semibold text-gray-700">Lokasi:</span>
                                                <span class="text-gray-600 ml-1">${trackingLocation}</span>
                                            </div>
                                        </div>
                                        ` : ''}

                                        ${trackingNotes ? `
                                        <div class="flex items-start">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-[${statusColor}] flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                                <polyline points="10 9 9 9 8 9"></polyline>
                                            </svg>
                                            <div class="text-gray-600">${trackingNotes}</div>
                                        </div>
                                        ` : ''}
                                    </div>

                                    <!-- Image if exists -->
                                    ${photoUrl ? `
                                    <div class="md:w-1/3 flex-shrink-0">
                                        <div class="image-thumbnail-container" onclick="showImageModal('${photoUrl}')">
                                            <img
                                                src="${photoUrl}"
                                                alt="Foto pelacakan"
                                                onerror="this.onerror=null; this.src='/images/image-placeholder.png';"
                                                class="w-full h-auto shadow-sm object-cover max-h-32 transition-all duration-300 hover:scale-105"
                                            >
                                            <div class="image-zoom-indicator">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline-block mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                Klik untuk lihat
                                            </div>
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            // Tutup container timeline
            timelineHTML += `
                </div>
            </div>`;

            container.innerHTML = timelineHTML;
        } catch (error) {
            console.error('Error rendering tracking timeline:', error);
            showEmptyTrackingMessage('Terjadi kesalahan saat menampilkan riwayat pelacakan.');
        }
    }

    // Menampilkan pesan timeline kosong
    function showEmptyTrackingMessage(message = 'Belum Ada Data Pelacakan') {
        console.log('Menampilkan pesan: tidak ada riwayat pelacakan');

        const container = document.getElementById('modal-tracking-timeline');
        if (!container) {
            console.error('Container modal-tracking-timeline tidak ditemukan!');
            return;
        }

        container.innerHTML = `
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
                <p class="text-gray-600 mb-1 font-medium">${message}</p>
                <p class="text-gray-500 text-sm">Peminjaman ini belum memiliki catatan pelacakan barang.</p>
            </div>
        `;
    }

    // Format tanggal dengan opsi untuk menampilkan waktu
    function formatDate(dateString, includeTime = false) {
        console.log('Formatting date:', dateString, 'type:', typeof dateString);

        if (!dateString) return '-';

        // Jika dateString adalah objek Date
        if (dateString instanceof Date) {
            dateString = dateString.toISOString();
        }

        // Jika dateString adalah objek dengan property date atau time
        if (typeof dateString === 'object' && (dateString.date || dateString.time)) {
            dateString = dateString.date || dateString.time;
        }

        const options = {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        };

        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
        }

        try {
            return new Date(dateString).toLocaleDateString('id-ID', options);
        } catch (e) {
            console.error('Error formatting date:', e);
            return dateString || '-';
        }
    }

    // Update gambar item
    function updateItemImage(data) {
        const imageContainer = document.getElementById('modal-item-image-container');
        if (!imageContainer) return;

        // Coba semua kemungkinan path gambar
        const item = data.item || {};
        let imageUrl = null;

        if (item.image_url) {
            imageUrl = item.image_url;
        } else if (item.imageUrl) {
            imageUrl = item.imageUrl;
        } else if (item.image) {
            if (typeof item.image === 'string') {
                if (item.image.startsWith('http')) {
                    imageUrl = item.image;
                } else {
                    imageUrl = '/storage/items/' + item.image;
                }
            }
        } else if (data.item_image) {
            if (data.item_image.startsWith('http')) {
                imageUrl = data.item_image;
            } else {
                imageUrl = '/storage/items/' + data.item_image;
            }
        }

        // Default placeholder
        imageContainer.innerHTML = `
            <div class="flex items-center justify-center w-full h-full bg-gray-100 rounded-lg">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        `;

        // Jika tidak ada URL gambar, hentikan
        if (!imageUrl) return;

        // Coba memuat gambar
        const img = new Image();
        img.onload = function() {
            imageContainer.innerHTML = `<img src="${imageUrl}" alt="${item.name || 'Gambar Barang'}" class="w-full h-full object-contain rounded-lg">`;
        };
        img.onerror = function() {};
        img.src = imageUrl;
    }

    // Fungsi untuk menutup modal
    function closeDetailModal() {
        const modal = document.getElementById('detailModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // Unlock body scroll
        document.body.classList.remove('fixed');
    }

    // Tutup modal jika klik di luar modal
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('detailModal');

        if (modal && !modal.classList.contains('hidden') && e.target === modal) {
            closeDetailModal();
        }
    });

    // Tutup modal dengan ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailModal();
        }
    });

    // Fungsi untuk menampilkan gambar dalam modal
    function showImageModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const fullSizeImage = document.getElementById('fullSizeImage');

        // Set gambar sumber
        fullSizeImage.src = imageSrc;

        // Tampilkan modal
        modal.classList.remove('hidden');

        // Setup event listener
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeImageModal();
            }
        });

        document.getElementById('closeImageModal').addEventListener('click', closeImageModal);

        // Tambahkan event listener untuk tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    }

    // Fungsi untuk menutup modal gambar
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
    }
</script>
@endpush
@endsection
