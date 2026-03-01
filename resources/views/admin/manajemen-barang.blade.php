@extends('layouts.admin.admin-layout')

@section('title', 'Manajemen Barang')

@push('styles')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endpush

@section('content')
<!-- Header Utama -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-[var(--primary)] mb-1">Manajemen Barang</h1>
    <p class="text-sm text-[var(--secondary)]">Kelola semua barang yang tersedia di Lab IoT Vokasi UB.</p>
</div>

<!-- Filter dan Pencarian -->
<div class="bg-[var(--bg-color)] rounded-lg p-4 mb-5 flex flex-wrap items-center gap-4">
    <!-- Pencarian dan Filter dalam satu form -->
    <form id="filterForm" action="{{ route('admin.manajemen-barang') }}" method="GET" class="flex flex-wrap items-end gap-4 w-full">
    <!-- Pencarian -->
    <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium text-[var(--primary)] mb-1" for="search">Pencarian</label>
            <div class="relative search-container">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-5 w-5 text-[var(--primary)]"></i>
                </div>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    class="pl-10 w-full rounded-full border border-[var(--primary)] py-2.5 px-4 text-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent"
                    placeholder="Cari nama barang..."
                    aria-label="Cari barang berdasarkan nama">
                <div id="searchSpinner" class="search-spinner"></div>
            </div>
    </div>

    <!-- Filter Kategori -->
    <div class="flex-1 md:max-w-[250px]">
            <label class="block text-sm font-medium text-[var(--primary)] mb-1" for="category_filter">Kategori</label>
            <select name="category_filter" id="category_filter"
                class="w-full border border-[var(--primary)] rounded-md py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent text-[var(--primary)]"
                aria-label="Filter barang berdasarkan kategori">
                <option value="">Semua Kategori</option>
                <option value="Sensor & Aktuator" {{ request('category_filter') == 'Sensor & Aktuator' ? 'selected' : '' }}>Sensor & Aktuator</option>
                <option value="Modul Komunikasi" {{ request('category_filter') == 'Modul Komunikasi' ? 'selected' : '' }}>Modul Komunikasi</option>
                <option value="Microcontroller & Development Board" {{ request('category_filter') == 'Microcontroller & Development Board' ? 'selected' : '' }}>Microcontroller & Development Board</option>
                <option value="Power Supply & Energi" {{ request('category_filter') == 'Power Supply & Energi' ? 'selected' : '' }}>Power Supply & Energi</option>
                <option value="Jaringan & Cloud" {{ request('category_filter') == 'Jaringan & Cloud' ? 'selected' : '' }}>Jaringan & Cloud</option>
                <option value="Kabel & Breadboard" {{ request('category_filter') == 'Kabel & Breadboard' ? 'selected' : '' }}>Kabel & Breadboard</option>
                <option value="Tools & Instrumentasi" {{ request('category_filter') == 'Tools & Instrumentasi' ? 'selected' : '' }}>Tools & Instrumentasi</option>
            </select>
        </div>
        </form>

    <!-- Tombol Tambah -->
    <div class="ml-auto">
        <button class="btn btn-primary bg-[var(--primary)] text-white hover:bg-[#0A3B64] transition-colors" onclick="openModal('addItemModal')">
            <i data-lucide="plus" class="h-5 w-5 mr-1"></i> Tambah Barang
        </button>
    </div>
</div>

<!-- Tabel Barang -->
<div class="bg-[var(--card-bg)] rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="divide-x divide-gray-200">
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Gambar</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Nama Barang</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Kategori</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Jumlah</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Status</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($items as $item)
                    <tr class="divide-x divide-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            @if($item->image)
                                <div class="w-16 h-16 flex items-center justify-center bg-[#f5f5f5] rounded mx-auto overflow-hidden">
                                    <img src="{{ $item->imageUrl }}" alt="{{ $item->name }}"
                                         style="width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; image-rendering: -webkit-optimize-contrast; transition: transform 0.3s ease;"
                                         class="hover:scale-105">
                                </div>
                            @else
                                <div class="w-16 h-16 flex items-center justify-center bg-[#f5f5f5] rounded mx-auto">
                                    <i data-lucide="image" class="h-8 w-8 text-gray-400"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item->category }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            <span class="status-badge
                                @if(strtolower($item->status) == 'available')
                                    status-available
                                @elseif(strtolower($item->status) == 'dipinjam')
                                    status-borrowed
                                @elseif(strtolower($item->status) == 'maintenance')
                                    status-maintenance
                                @else
                                    status-unavailable
                                @endif">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                            <div class="flex justify-center gap-2">
                                <button class="btn btn-outline btn-sm"
                                        onclick="editItem('{{ $item->id }}', '{{ $item->name }}', '{{ $item->category }}', '{{ $item->quantity }}', '{{ $item->image }}')"
                                        aria-label="Edit barang {{ $item->name }}">
                                    <i data-lucide="edit" class="h-4 w-4 mr-1" aria-hidden="true"></i> Edit
                                </button>
                                <form action="{{ route('admin.manajemen-barang.destroy', $item->id) }}" method="POST" class="inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="confirmDelete(this.form, '{{ $item->name }}')"
                                            aria-label="Hapus barang {{ $item->name }}">
                                        <i data-lucide="trash-2" class="h-4 w-4 mr-1" aria-hidden="true"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i data-lucide="package-x" class="h-12 w-12 text-gray-300 mb-2"></i>
                                <p>Tidak ada data barang</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(method_exists($items, 'hasPages') && $items->hasPages())
    <div class="px-6 py-4 bg-white border-t border-gray-200">
        {{ $items->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Barang -->
<div id="addItemModal" class="modal">
    <div class="modal-content max-h-[90vh] flex flex-col">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Barang</h2>
            <button type="button" onclick="closeModal('addItemModal')" class="text-white hover:text-gray-200 focus:outline-none">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <div class="modal-body overflow-y-auto no-scrollbar flex-1">
            <form action="{{ route('admin.manajemen-barang.store') }}" method="POST" enctype="multipart/form-data" id="addItemForm" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                <div class="space-y-4">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Barang</label>
                        <input type="text" name="name" id="name" class="form-control text-gray-900 bg-white border-gray-300" required>
                        <div class="form-error text-red-500 text-sm mt-1" id="name-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="category" class="form-label">Kategori</label>
                        <select name="category" id="category" class="form-control text-gray-900 bg-white border-gray-300" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Sensor & Aktuator">Sensor & Aktuator</option>
                            <option value="Modul Komunikasi">Modul Komunikasi</option>
                            <option value="Microcontroller & Development Board">Microcontroller & Development Board</option>
                            <option value="Power Supply & Energi">Power Supply & Energi</option>
                            <option value="Jaringan & Cloud">Jaringan & Cloud</option>
                            <option value="Kabel & Breadboard">Kabel & Breadboard</option>
                            <option value="Tools & Instrumentasi">Tools & Instrumentasi</option>
                        </select>
                        <div class="form-error text-red-500 text-sm mt-1" id="category-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="form-label">Jumlah</label>
                        <input type="number" name="quantity" id="quantity" class="form-control text-gray-900 bg-white border-gray-300" required min="0">
                        <div class="form-error text-red-500 text-sm mt-1" id="quantity-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="image" class="form-label">Foto Barang</label>
                        <input type="file" name="image" id="image" class="form-control text-gray-900 bg-white border-gray-300" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <p class="mt-1 text-sm text-gray-500">Format: JPG, PNG, JPEG, WebP. Maksimal 2MB</p>
                        <div class="form-error text-red-500 text-sm mt-1" id="image-error"></div>
                    </div>
                </div>
                <div class="modal-footer mt-6">
                    <button type="button" onclick="closeModal('addItemModal')" class="btn btn-secondary">Batal</button>
                    <x-button class="btn btn-primary" loadingText="Menyimpan...">Simpan</x-button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Barang -->
<div id="editItemModal" class="modal">
    <div class="modal-content max-h-[90vh] flex flex-col">
        <div class="modal-header">
            <h2 class="modal-title">Edit Barang</h2>
            <button type="button" onclick="closeModal('editItemModal')" class="text-white hover:text-gray-200 focus:outline-none">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <div class="modal-body overflow-y-auto no-scrollbar flex-1">
            <form id="editItemForm" method="POST" enctype="multipart/form-data" data-base-url="{{ route('admin.manajemen-barang') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Nama Barang</label>
                        <input type="text" name="name" id="edit_name" class="form-control text-gray-900 bg-white border-gray-300" required>
                        <div class="form-error text-red-500 text-sm mt-1" id="edit_name-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_category" class="form-label">Kategori</label>
                        <select name="category" id="edit_category" class="form-control text-gray-900 bg-white border-gray-300" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Sensor & Aktuator">Sensor & Aktuator</option>
                            <option value="Modul Komunikasi">Modul Komunikasi</option>
                            <option value="Microcontroller & Development Board">Microcontroller & Development Board</option>
                            <option value="Power Supply & Energi">Power Supply & Energi</option>
                            <option value="Jaringan & Cloud">Jaringan & Cloud</option>
                            <option value="Kabel & Breadboard">Kabel & Breadboard</option>
                            <option value="Tools & Instrumentasi">Tools & Instrumentasi</option>
                        </select>
                        <div class="form-error text-red-500 text-sm mt-1" id="edit_category-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_quantity" class="form-label">Jumlah</label>
                        <input type="number" name="quantity" id="edit_quantity" class="form-control text-gray-900 bg-white border-gray-300" required min="0">
                        <div class="form-error text-red-500 text-sm mt-1" id="edit_quantity-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_image" class="form-label">Foto Barang</label>
                        <input type="file" name="image" id="edit_image" class="form-control text-gray-900 bg-white border-gray-300" accept="image/jpeg,image/png,image/jpg,image/webp">
                        <p class="mt-1 text-sm text-gray-500">Format: JPG, PNG, JPEG, WebP. Maksimal 2MB</p>
                        <div class="form-error text-red-500 text-sm mt-1" id="edit_image-error"></div>
                        <div id="current_image" class="mt-3">
                            <p class="text-sm font-medium text-gray-700 mb-2">Gambar Saat Ini:</p>
                            <img src="" alt="Foto barang saat ini" class="max-w-xs rounded-lg shadow-md">
                        </div>
                        <input type="hidden" name="old_image" id="old_image">
                    </div>
                </div>
                <div class="modal-footer mt-6">
                    <button type="button" onclick="closeModal('editItemModal')" class="btn btn-secondary">Batal</button>
                    <x-button class="btn btn-primary" loadingText="Menyimpan...">Simpan</x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toast notification if success/error from session
        @if(session('success'))
            showToast('success', "{{ session('success') }}");
        @endif

        @if(session('error'))
            showToast('error', "{{ session('error') }}");
        @endif

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

        // Auto-submit saat filter kategori berubah
        const categoryFilter = document.getElementById('category_filter');
        categoryFilter.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>
@endpush

