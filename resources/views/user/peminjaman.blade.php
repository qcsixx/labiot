@extends('layouts.user.user-layout')

@section('title', 'Daftar Barang - Lab IoT Vokasi UB')

@push('styles')
<style>
    /* Styling card barang */
    .item-card {
        transition: all 0.3s ease-in-out;
    }

    .item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--primary);
    }

    /* Styling gambar barang - PERBAIKAN UNTUK GAMBAR SEMPURNA */
    .item-thumbnail {
        height: 200px;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        background-color: #f5f5f5; /* Warna abu-abu yang lebih cerah */
        border-radius: 8px 8px 0 0; /* Tambahkan border radius atas */
        position: relative; /* Untuk positioning absolut jika diperlukan */
    }

    .item-thumbnail img {
        height: 100%;
        width: 100%;
        object-fit: contain; /* Memastikan gambar menyesuaikan container */
        padding: 0.75rem; /* Mengurangi padding untuk memaksimalkan area gambar */
        transition: transform 0.3s ease; /* Animasi hover */
        mix-blend-mode: multiply; /* Menghilangkan background putih pada gambar PNG */
        image-rendering: -webkit-optimize-contrast; /* Meningkatkan ketajaman gambar */
        image-rendering: crisp-edges;
    }

    .item-thumbnail img:hover {
        transform: scale(1.05); /* Sedikit zoom saat hover */
    }

    /* Styling search container */
    .search-container {
        position: relative;
    }

    /* Search spinner styling */
    #searchSpinner {
        transition: opacity 0.2s;
    }

    #searchSpinner.hidden {
        opacity: 0;
        pointer-events: none;
    }

    /* Styling tombol peminjaman */
    .btn-pinjam {
        background-color: var(--primary);
        color: white;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 500;
    }

    .btn-pinjam:hover {
        background-color: #005b9c; /* Warna biru yang lebih gelap */
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Perbaikan modal */
    #peminjamanModal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        display: none !important;
        z-index: 2000 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
        overflow: hidden !important;
        transition: opacity 0.3s ease !important;
        padding: 20px !important;
        align-items: center !important; /* Ubah ke center untuk posisi vertikal tengah */
        justify-content: center !important;
        backdrop-filter: blur(3px) !important;
    }

    #peminjamanModal.show {
        display: flex !important;
        opacity: 1 !important;
    }

    #peminjamanModal .modal {
        background-color: white !important;
        border-radius: 12px !important;
        max-width: 500px !important;
        width: 95% !important;
        margin: auto !important;
        box-shadow: 0 15px 30px rgba(0,0,0,0.3) !important;
        transform: translateY(-20px) scale(0.95) !important;
        opacity: 0 !important;
        transition: all 0.3s ease !important;
        max-height: 90vh !important;
        display: flex !important;
        flex-direction: column !important;
        overflow-y: auto !important;
    }

    #peminjamanModal .modal.show {
        transform: translateY(0) scale(1) !important;
        opacity: 1 !important;
    }

    #peminjamanModal .modal-header {
        background-color: var(--primary) !important;
        color: white !important;
        padding: 15px 20px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-radius: 12px 12px 0 0 !important;
    }

    #peminjamanModal .modal-body {
        padding: 20px !important;
        max-height: calc(90vh - 180px) !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: none !important; /* Firefox */
        -ms-overflow-style: none !important; /* IE and Edge */
    }

    /* Menghilangkan scrollbar tapi tetap bisa scroll */
    #peminjamanModal .modal-body::-webkit-scrollbar {
        width: 0px !important;
        background: transparent !important;
        display: none !important; /* Chrome, Safari, Opera */
    }

    #peminjamanModal .modal-footer {
        padding: 15px 20px !important;
        display: flex !important;
        justify-content: flex-end !important;
        gap: 10px !important;
        border-top: 1px solid #e5e7eb !important;
    }

    /* Class untuk mencegah scroll pada body saat modal terbuka */
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
</style>
@endpush

@section('content')
<!-- Header Halaman -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-[var(--primary)]">Daftar Barang</h1>
    <p class="text-gray-600 mt-2">Lihat semua barang laboratorium dan Ajukan Peminjaman!</p>
</div>

<!-- Meta CSRF untuk AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Filter dan Pencarian -->
<div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
    <div class="flex flex-wrap gap-4 items-end">
        <!-- Pencarian -->
        <div class="w-full md:w-1/3">
            <form id="searchForm" action="{{ route('user.peminjaman') }}" method="GET">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <label class="block text-sm font-medium text-gray-700 mb-1" for="search">Pencarian</label>
                <div class="relative search-container">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-5 w-5 text-[var(--primary)]"></i>
                    </div>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                        class="pl-10 w-full rounded-full border border-[var(--primary)] py-2.5 px-4 text-gray-900 bg-white focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent"
                        placeholder="Cari nama barang...">
                    <div id="searchSpinner" class="absolute right-3 top-1/2 transform -translate-y-1/2 hidden">
                        <svg class="animate-spin h-5 w-5 text-[var(--primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </form>
        </div>

        <!-- Filter Kategori -->
        <div class="flex-1 md:max-w-[250px]">
            <form id="categoryForm" action="{{ route('user.peminjaman') }}" method="GET">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select id="categoryFilter" name="category" class="w-full border border-[var(--primary)] rounded-md py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent text-gray-900 bg-white">
        <option value="">Semua Kategori</option>
                    <option value="Sensor & Aktuator" {{ request('category') == 'Sensor & Aktuator' ? 'selected' : '' }}>Sensor & Aktuator</option>
                    <option value="Modul Komunikasi" {{ request('category') == 'Modul Komunikasi' ? 'selected' : '' }}>Modul Komunikasi</option>
                    <option value="Microcontroller & Development Board" {{ request('category') == 'Microcontroller & Development Board' ? 'selected' : '' }}>Microcontroller & Development Board</option>
                    <option value="Power Supply & Energi" {{ request('category') == 'Power Supply & Energi' ? 'selected' : '' }}>Power Supply & Energi</option>
                    <option value="Jaringan & Cloud" {{ request('category') == 'Jaringan & Cloud' ? 'selected' : '' }}>Jaringan & Cloud</option>
                    <option value="Kabel & Breadboard" {{ request('category') == 'Kabel & Breadboard' ? 'selected' : '' }}>Kabel & Breadboard</option>
                    <option value="Tools & Instrumentasi" {{ request('category') == 'Tools & Instrumentasi' ? 'selected' : '' }}>Tools & Instrumentasi</option>
    </select>
            </form>
        </div>
    </div>
</div>

<!-- Grid Card Barang -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="itemsGrid">
    @forelse($items as $item)
        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform duration-200 hover:shadow-lg item-card"
             data-category="{{ $item->category ?? 'Umum' }}">
            <!-- Card Content -->
            <div class="relative">
                <!-- Thumbnail -->
                <div class="item-thumbnail">
                    @if($item->image)
                        <img src="{{ $item->imageUrl }}" alt="{{ $item->name }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card Info -->
            <div class="p-4 flex flex-col h-[150px]">
                <h3 class="font-bold text-[var(--primary)] text-lg truncate" title="{{ $item->name }}">{{ $item->name }}</h3>

                <!-- Kategori dengan tooltip -->
                <div class="relative group mb-2">
                    <p class="text-gray-600 text-sm truncate">
                        <i class="inline-block w-3.5 h-3.5 mr-1 text-[#FDB813]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                <line x1="7" y1="7" x2="7.01" y2="7"></line>
                            </svg>
                        </i>
                        {{ $item->category ?? 'Umum' }}
                    </p>
                    <!-- Tooltip -->
                    <div class="absolute left-0 bottom-full mb-1 w-auto bg-gray-800 text-white text-xs rounded py-1 px-2 hidden group-hover:block z-10 whitespace-nowrap">
                        {{ $item->category ?? 'Umum' }}
                        <div class="absolute top-full left-3 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                    </div>
                </div>

                <!-- Stok -->
                <p class="text-sm mb-auto flex items-center text-gray-700" style="color: #374151;">
                    <i class="inline-block w-3.5 h-3.5 mr-1 text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </i>
                    Stok: <span class="font-semibold ml-1" style="color: #1f2937;">{{ $item->quantity }}</span>
                </p>

                <!-- Tombol -->
                <button class="btn-pinjam w-full mt-auto"
                        onclick="openForm('{{ $item->id }}', '{{ $item->name }}', {{ $item->quantity }})">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Ajukan Peminjaman
                </button>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white p-6 rounded-xl shadow-md text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <p class="text-gray-600">Tidak ada barang tersedia untuk dipinjam saat ini.</p>
        </div>
    @endforelse
</div>

<!-- Empty State Message -->
<div id="emptyState" class="hidden text-center py-10">
    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
    </svg>
    <p class="text-gray-600">Tidak ada barang dalam kategori ini.</p>
</div>

<!-- Modal Form Peminjaman -->
<div id="peminjamanModal" class="modal-backdrop">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Ajukan Peminjaman</h2>
            <button onclick="closeForm()" class="text-white hover:text-gray-200 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

        <div class="modal-body">
            <form id="formPeminjaman" action="{{ route('user.peminjaman.store') }}" method="POST">
                @csrf
                <input type="hidden" id="item_id" name="item_id">

                <div class="mb-4">
                    <label for="barang" class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
                    <input type="text" id="barang" class="w-full border p-2 rounded-lg bg-gray-50 text-gray-900" readonly>
                </div>

                <div class="mb-4">
                    <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" id="jumlah" name="quantity" class="w-full border p-2 rounded-lg focus:ring focus:ring-[var(--primary)] focus:ring-opacity-50 text-gray-900 bg-white" min="1" required>
                    <div id="stokInfo" class="text-sm text-gray-500 mt-1"></div>
                </div>

                <div class="mb-4">
                    <label for="tglPinjam" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Peminjaman <span class="text-red-500">*</span></label>
                    <input type="date" id="tglPinjam" name="borrow_date" class="w-full border p-2 rounded-lg focus:ring focus:ring-[var(--primary)] focus:ring-opacity-50 text-gray-900 bg-white" required>
                    <div id="tglPinjamError" class="text-sm text-red-500 mt-1 hidden"></div>
                </div>

                <div class="mb-4">
                    <label for="tglKembali" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengembalian <span class="text-red-500">*</span></label>
                    <input type="date" id="tglKembali" name="return_deadline" class="w-full border p-2 rounded-lg focus:ring focus:ring-[var(--primary)] focus:ring-opacity-50 text-gray-900 bg-white" required>
                    <div id="tglKembaliError" class="text-sm text-red-500 mt-1 hidden"></div>
                </div>

                <div class="mb-4">
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Keterangan/Tujuan <span class="text-red-500">*</span></label>
                    <textarea id="purpose" name="purpose" class="w-full border p-2 rounded-lg focus:ring focus:ring-[var(--primary)] focus:ring-opacity-50 text-gray-900 bg-white" rows="3"
                        placeholder="Jelaskan tujuan peminjaman barang" required></textarea>
                    <div id="purposeError" class="text-sm text-red-500 mt-1 hidden"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1" onclick="closeForm()">Batal</button>
                    <button type="submit" class="bg-[var(--primary)] text-white px-4 py-2 rounded-lg hover:bg-[#0d3f6a] hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">Ajukan Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Cek autentikasi saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function() {
        // Atur tanggal minimum untuk tanggal peminjaman (hari ini)
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tglPinjam').min = today;

        // Set default value untuk tanggal peminjaman
        document.getElementById('tglPinjam').value = today;

        // Listener untuk mengatur tanggal pengembalian minimal
        document.getElementById('tglPinjam').addEventListener('change', function() {
            validateBorrowDate();
            const borrowDate = new Date(this.value);
            borrowDate.setDate(borrowDate.getDate() + 1); // minimum 1 hari pinjam
            const minReturnDate = borrowDate.toISOString().split('T')[0];
            document.getElementById('tglKembali').min = minReturnDate;

            // Set default tanggal pengembalian ke hari setelah peminjaman jika belum diatur
            if (!document.getElementById('tglKembali').value) {
                document.getElementById('tglKembali').value = minReturnDate;
            }

            // Jika tanggal pengembalian kurang dari tanggal minimum, update
            if (document.getElementById('tglKembali').value &&
                new Date(document.getElementById('tglKembali').value) < borrowDate) {
                document.getElementById('tglKembali').value = minReturnDate;
            }

            validateReturnDate();
        });

        // Validasi tanggal pengembalian saat diubah
        document.getElementById('tglKembali').addEventListener('change', function() {
            validateReturnDate();
        });

        // Validasi purpose saat diubah
        document.getElementById('purpose').addEventListener('input', function() {
            validatePurpose();
        });

        // Validasi jumlah saat diubah
        document.getElementById('jumlah').addEventListener('input', function() {
            validateQuantity();
        });

        // Validasi form sebelum submit
        document.getElementById('formPeminjaman').addEventListener('submit', function(e) {
            e.preventDefault();

            if (validateForm()) {
                submitRequest();
            }
        });

        // Atur tanggal pengembalian default (hari setelah peminjaman)
        const borrowDate = new Date(document.getElementById('tglPinjam').value);
        borrowDate.setDate(borrowDate.getDate() + 1); // minimum 1 hari pinjam
        const minReturnDate = borrowDate.toISOString().split('T')[0];
        document.getElementById('tglKembali').min = minReturnDate;
        document.getElementById('tglKembali').value = minReturnDate;

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('peminjamanModal');
            if (event.target === modal) {
                closeForm();
            }
        }

        // Tambahkan event listener untuk tombol ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('peminjamanModal');
                if (modal && modal.classList.contains('show')) {
                    closeForm();
                }
            }
        });

        // Handle Search Input dengan debounce
        const searchInput = document.getElementById('searchInput');
        let searchTimer;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                document.getElementById('searchSpinner').classList.remove('hidden');

                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    document.getElementById('searchForm').submit();
                }, 500); // Delay 500ms untuk menghindari terlalu banyak request
            });

            // Enter key pada search
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    document.getElementById('searchSpinner').classList.remove('hidden');
                    document.getElementById('searchForm').submit();
                }
            });
        }

        // Auto-submit saat filter kategori berubah
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function() {
                document.getElementById('categoryForm').submit();
            });
        }

        // Filter barang berdasarkan kategori client-side (untuk backward compatibility)
        const clientSideFilter = function(category) {
            const items = document.querySelectorAll('.item-card');
            let visibleCount = 0;

            items.forEach(item => {
                if (!category || item.dataset.category === category) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Tampilkan/sembunyikan pesan kosong
            const emptyState = document.getElementById('emptyState');
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        };
    });

    // Tampilkan form peminjaman
    function openForm(itemId, itemName, quantity) {
        // Cek apakah user memiliki nomor handphone
        @if(!auth()->user()->phone)
            // Tampilkan pesan error
            window.showToast('error', 'Anda harus mengisi nomor handphone di profil Anda sebelum dapat mengajukan peminjaman');

            // Arahkan ke halaman profil setelah 2 detik
            setTimeout(() => {
                window.location.href = '{{ route('user.profile') }}';
            }, 2000);
            return;
        @endif

        // Reset form
        if (document.getElementById('formPeminjaman')) {
            document.getElementById('formPeminjaman').reset();
        }

        // Nonaktifkan scroll pada body dengan menambahkan class modal-open
        document.body.classList.add('modal-open');

        // Set default tanggal
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tglPinjam').value = today;
        document.getElementById('tglPinjam').min = today;

        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('tglKembali').value = tomorrow.toISOString().split('T')[0];
        document.getElementById('tglKembali').min = tomorrow.toISOString().split('T')[0];

        // Set nilai form
                document.getElementById('item_id').value = itemId;
                document.getElementById('barang').value = itemName;
                document.getElementById('jumlah').max = quantity;
                document.getElementById('jumlah').value = 1;
                document.getElementById('stokInfo').textContent = `Stok tersedia: ${quantity}`;

        // Reset error messages
        const errorElements = document.querySelectorAll('[id$="Error"]');
        errorElements.forEach(el => el.classList.add('hidden'));

        // Scroll to top of page first to ensure modal shows properly
        window.scrollTo(0, 0);

        // Tampilkan modal - metode langsung
        const modal = document.getElementById('peminjamanModal');
        modal.style.display = "flex";
        setTimeout(() => {
            modal.classList.add('show');
            const modalContent = modal.querySelector('.modal');
            modalContent.classList.add('show');
        }, 10);
    }

    // Tutup form peminjaman
    function closeForm() {
        const modal = document.getElementById('peminjamanModal');
        const modalContent = modal.querySelector('.modal');

        modalContent.classList.remove('show');
        modal.classList.remove('show');

        // Aktifkan scroll pada body kembali
        document.body.classList.remove('modal-open');

        setTimeout(() => {
            modal.style.display = "none";
        }, 300);
    }

    // Validasi tanggal peminjaman
    function validateBorrowDate() {
        const borrowDateInput = document.getElementById('tglPinjam');
        const errorElement = document.getElementById('tglPinjamError');
        const selectedDate = new Date(borrowDateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            errorElement.textContent = 'Tanggal peminjaman tidak boleh kurang dari hari ini';
            errorElement.classList.remove('hidden');
            return false;
        }

        errorElement.classList.add('hidden');
        return true;
    }

    // Validasi tanggal pengembalian
    function validateReturnDate() {
        const borrowDateInput = document.getElementById('tglPinjam');
        const returnDateInput = document.getElementById('tglKembali');
        const errorElement = document.getElementById('tglKembaliError');

        if (!returnDateInput.value) {
            errorElement.textContent = 'Tanggal pengembalian wajib diisi';
            errorElement.classList.remove('hidden');
            return false;
        }

        const borrowDate = new Date(borrowDateInput.value);
        const returnDate = new Date(returnDateInput.value);

        if (returnDate <= borrowDate) {
            errorElement.textContent = 'Tanggal pengembalian harus lebih dari tanggal peminjaman';
            errorElement.classList.remove('hidden');
            return false;
        }

        errorElement.classList.add('hidden');
        return true;
    }

    // Validasi tujuan peminjaman
    function validatePurpose() {
        const purposeInput = document.getElementById('purpose');
        const errorElement = document.getElementById('purposeError');

        if (!purposeInput.value.trim()) {
            errorElement.textContent = 'Tujuan peminjaman wajib diisi';
            errorElement.classList.remove('hidden');
            return false;
        }

        if (purposeInput.value.trim().length < 10) {
            errorElement.textContent = 'Tujuan peminjaman minimal 10 karakter';
            errorElement.classList.remove('hidden');
            return false;
        }

        errorElement.classList.add('hidden');
        return true;
    }

    // Validasi jumlah
    function validateQuantity() {
        const quantityInput = document.getElementById('jumlah');
        const maxQuantity = parseInt(quantityInput.max);

        if (quantityInput.value <= 0) {
            quantityInput.value = 1;
            return false;
        }

        if (quantityInput.value > maxQuantity) {
            quantityInput.value = maxQuantity;
            return false;
        }

        return true;
    }

    // Validasi form
    function validateForm() {
        const isValidBorrowDate = validateBorrowDate();
        const isValidReturnDate = validateReturnDate();
        const isValidPurpose = validatePurpose();
        const isValidQuantity = validateQuantity();

        return isValidBorrowDate && isValidReturnDate && isValidPurpose && isValidQuantity;
    }

    // Submit permintaan peminjaman
    function submitRequest() {
        const form = document.getElementById('formPeminjaman');
        const formData = new FormData(form);

        // Show loading indicator
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...</div>';

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Send AJAX request
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            if (data.status === 'success') {
                // Close modal
                    closeForm();

                // Show success message using toast notification
                window.showToast('success', data.message || 'Permintaan peminjaman berhasil diajukan');

                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = '/user/status-peminjaman';
                }, 2000);
            } else {
                // Handle redirect response for phone number validation
                if (data.redirect) {
                    // Show error message
                    window.showToast('error', data.message || 'Terjadi kesalahan saat memproses permintaan');

                    // Redirect to profile page after a short delay
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                    return;
                }

                // Show error message
                window.showToast('error', data.message || 'Terjadi kesalahan saat memproses permintaan');

                // If there are field-specific errors, display them
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorElement = document.getElementById(`${field}Error`);
                        if (errorElement) {
                            errorElement.textContent = data.errors[field][0];
                            errorElement.classList.remove('hidden');
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);

            // Reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;

            // Show error message
            window.showToast('error', 'Terjadi kesalahan saat memproses permintaan');
        });
    }

    // Filter barang berdasarkan kategori - Legacy, now handled by server-side filtering
    function filterItems(category) {
        // Gunakan URL API untuk filter kategori server-side
        window.location.href = `{{ route('user.peminjaman') }}?category=${encodeURIComponent(category)}`;
    }
</script>
@endpush

