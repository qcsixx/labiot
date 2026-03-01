@extends('layouts.admin.admin-layout')

@section('title', 'Pengelolaan User')

@section('content')
<!-- Header Utama -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-[var(--primary)] mb-1">Pengelolaan User</h1>
    <p class="text-sm text-[var(--secondary)]">Kelola semua pengguna yang terdaftar di sistem Lab IoT Vokasi UB.</p>
</div>

<!-- Filter dan Pencarian -->
<div class="bg-[var(--bg-color)] rounded-lg p-4 mb-5 flex flex-wrap items-center gap-4">
    <!-- Pencarian -->
    <div class="w-full md:w-1/3">
        <form id="filterForm" action="{{ route('admin.pengelolaan-user') }}" method="GET">
            <input type="hidden" name="status" value="{{ request('status') }}">
            
            <label class="block text-sm font-medium text-[var(--primary)] mb-1" for="search">Pencarian</label>
            <div class="relative search-container">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-5 w-5 text-[var(--primary)]"></i>
                </div>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                    class="pl-10 w-full rounded-full border border-[var(--primary)] py-2.5 px-4 text-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent"
                    placeholder="Cari nama, email atau no handphone...">
                <div id="searchSpinner" class="search-spinner"></div>
            </div>
        </form>
    </div>

    <!-- Filter Status -->
    <div class="flex-1 md:max-w-[250px]">
        <form action="{{ route('admin.pengelolaan-user') }}" method="GET" id="statusForm">
            <input type="hidden" name="search" value="{{ request('search') }}">
            
            <label class="block text-sm font-medium text-[var(--primary)] mb-1" for="statusFilter">Status</label>
            <select name="status" 
                id="statusFilter"
                class="w-full border border-[var(--primary)] rounded-md py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent text-[var(--primary)]">
                <option value="" {{ request('status') ? '' : 'selected' }}>Semua Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
            </select>
        </form>
    </div>
</div>

<!-- Tabel Daftar User -->
<div class="bg-[var(--card-bg)] rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="divide-x divide-gray-200">
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10 w-16">No</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Nama</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Email</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">No. Handphone</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Status</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $index => $user)
                    <tr class="divide-x divide-gray-200">
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center w-16">
                            {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $user->name }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $user->email }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $user->phone ?? '-' }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            @if($user->status === 'active')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditangguhkan</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-center">
                            <div class="flex justify-center gap-2">
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm inline-flex items-center" 
                                            onclick="confirmDelete(this.form, '{{ $user->name }}')">
                                        <i data-lucide="trash-2" class="h-4 w-4 mr-1"></i>
                                        Hapus
                                    </button>
                                </form>

                                @if($user->status === 'active')
                                    <form action="{{ route('admin.users.suspend', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="button" class="btn btn-warning btn-sm inline-flex items-center"
                                                onclick="confirmSuspend(this.form, '{{ $user->name }}')">
                                            <i data-lucide="pause" class="h-4 w-4 mr-1"></i>
                                            Suspend
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.activate', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="button" class="btn btn-success btn-sm inline-flex items-center"
                                                onclick="confirmActivate(this.form, '{{ $user->name }}')">
                                            <i data-lucide="play" class="h-4 w-4 mr-1"></i>
                                            Aktifkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 mb-2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <line x1="17" y1="8" x2="22" y2="13"></line>
                                    <line x1="22" y1="8" x2="17" y2="13"></line>
                                </svg>
                                <p>Tidak ada data user</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination dengan parameter pencarian -->
    @if(method_exists($users, 'hasPages') && $users->hasPages())
    <div class="px-6 py-4 bg-white border-t border-gray-200">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi ikon Lucide
        lucide.createIcons();
        
        // Tampilkan toast notifikasi saat halaman dimuat
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

        // Enter key untuk submit search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                document.getElementById('searchSpinner').classList.add('active');
                document.getElementById('filterForm').submit();
        }
        });
        
        // Status filter auto-submit
        document.getElementById('statusFilter').addEventListener('change', function() {
            document.getElementById('statusForm').submit();
        });
    });

    // Fungsi untuk tombol-tombol aksi
    function confirmDelete(form, userName) {
        Swal.fire({
            title: 'Hapus User?',
            text: `Apakah Anda yakin ingin menghapus user "${userName}"? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0F4C81', // Biru UB
            cancelButtonColor: '#ef4444', // Merah
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            buttonsStyling: true,
            showClass: {
                popup: 'animate__animated animate__zoomIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__zoomOut animate__faster'
            },
            didOpen: (popup) => {
                // Pastikan ikon Lucide terinisialisasi
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading state
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Menghapus user',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut animate__faster'
                    },
                    didOpen: () => {
                        Swal.showLoading();
                        
                        // Submit form tanpa menampilkan toast terlebih dahulu
                            form.submit();
                    }
                });
            }
        });
    }

    function confirmSuspend(form, userName) {
        Swal.fire({
            title: 'Tangguhkan User?',
            text: `Apakah Anda yakin ingin menangguhkan user "${userName}"? User tidak akan dapat login hingga diaktifkan kembali.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0F4C81', // Biru UB
            cancelButtonColor: '#ef4444', // Merah
            confirmButtonText: 'Ya, Tangguhkan!',
            cancelButtonText: 'Batal',
            buttonsStyling: true,
            showClass: {
                popup: 'animate__animated animate__zoomIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__zoomOut animate__faster'
            },
            didOpen: (popup) => {
                // Pastikan ikon Lucide terinisialisasi
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading state
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Menangguhkan user',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut animate__faster'
                    },
                    didOpen: () => {
                        Swal.showLoading();
                        
                        // Submit form tanpa menampilkan toast terlebih dahulu
                            form.submit();
                    }
                });
            }
        });
    }

    function confirmActivate(form, userName) {
        Swal.fire({
            title: 'Aktifkan User?',
            text: `Apakah Anda yakin ingin mengaktifkan kembali user "${userName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0F4C81', // Biru UB
            cancelButtonColor: '#ef4444', // Merah
            confirmButtonText: 'Ya, Aktifkan!',
            cancelButtonText: 'Batal',
            buttonsStyling: true,
            showClass: {
                popup: 'animate__animated animate__zoomIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__zoomOut animate__faster'
            },
            didOpen: (popup) => {
                // Pastikan ikon Lucide terinisialisasi
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading state
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mengaktifkan user',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut animate__faster'
                    },
                    didOpen: () => {
                        Swal.showLoading();
                        
                        // Submit form tanpa menampilkan toast terlebih dahulu
                            form.submit();
                    }
                });
            }
        });
    }
</script>
@endpush

<style>
    .custom-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
    }
    
    .custom-select option {
        background-color: white;
        color: var(--primary);
    }
    
    .custom-select option:checked {
        background-color: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
    }
</style>