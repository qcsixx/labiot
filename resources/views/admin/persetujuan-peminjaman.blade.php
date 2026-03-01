@extends('layouts.admin.admin-layout')

@section('title', 'Persetujuan Peminjaman Barang')

@section('content')
<!-- Header Utama -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-[var(--primary)] mb-1">Persetujuan Peminjaman Barang</h1>
    <p class="text-sm text-[var(--secondary)]">Kelola permintaan peminjaman barang dari pengguna Lab IoT Vokasi UB.</p>
</div>

<!-- Filter dan Pencarian -->
<div class="bg-[var(--bg-color)] rounded-lg p-4 mb-5 flex flex-wrap items-center justify-between">
    <div class="w-full md:w-1/3">
        <form id="filterForm" action="{{ route('admin.persetujuan-peminjaman') }}" method="GET">
            <input type="hidden" name="sort" value="oldest">
            <label class="block text-sm font-medium text-[var(--primary)] mb-1" for="search">Pencarian</label>
            <div class="relative search-container">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-5 w-5 text-[var(--primary)]"></i>
                </div>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                    class="pl-10 w-full rounded-full border border-[var(--primary)] py-2.5 px-4 text-[var(--primary)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] focus:border-transparent"
                    placeholder="Cari nama peminjam atau barang...">
                <div id="searchSpinner" class="search-spinner"></div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Daftar Permintaan Peminjaman -->
<div class="bg-[var(--card-bg)] rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 notDataTable">
            <thead>
                <tr class="divide-x divide-gray-200">
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">No</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Peminjam</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Nama Barang</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Jumlah</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Tanggal Pinjam</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Tanggal Kembali</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Tujuan Peminjaman</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    // Balik urutan array untuk menampilkan dari terlama ke terbaru
                    if(is_array($borrowRequests)) {
                        $borrowRequests = array_reverse($borrowRequests);
                    } else {
                        $borrowRequests = $borrowRequests->reverse();
                    }
                @endphp
                
                @forelse($borrowRequests as $index => $request)
                    <tr class="divide-x divide-gray-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                            {{ method_exists($borrowRequests, 'currentPage') ? 
                                ($borrowRequests->currentPage() - 1) * $borrowRequests->perPage() + $index + 1 : 
                                $index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->borrow_date ? \Carbon\Carbon::parse($request->borrow_date)->format('d M Y') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->return_deadline ? \Carbon\Carbon::parse($request->return_deadline)->format('d M Y') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">{{ $request->purpose ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                            <div class="flex justify-center gap-2">
                                <button type="button" class="btn btn-success btn-sm" 
                                        onclick="handleApproval('{{ $request->request_id }}', '{{ $request->item->name }}')">
                                    <i data-lucide="check" class="h-4 w-4 mr-1"></i>
                                    Setujui
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="handleRejection('{{ $request->request_id }}', '{{ $request->item->name }}')">
                                    <i data-lucide="x" class="h-4 w-4 mr-1"></i>
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i data-lucide="clipboard-x" class="h-12 w-12 text-gray-300 mb-2"></i>
                                <p>Tidak ada permintaan peminjaman yang menunggu persetujuan</p>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Lucide Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        
        // Toast notification if success/error from session
        @if(session('success'))
            showToast('success', "{{ session('success') }}");
        @endif
        
        @if(session('error'))
            showToast('error', "{{ session('error') }}");
        @endif

        // Auto Refresh Setup
        let currentRequestCount = {{ count($borrowRequests ?? []) }};
        let refreshInterval;
        let isPageActive = true;
        
        // Mulai polling saat halaman dimuat
        startAutoRefresh();
        
        // Fungsi untuk melakukan polling data baru
        function startAutoRefresh() {
            refreshInterval = setInterval(checkForNewRequests, 10000); // Cek setiap 10 detik
        }
        
        // Fungsi untuk memeriksa permintaan baru
        function checkForNewRequests() {
            if (!isPageActive) return; // Jangan refresh jika tab tidak aktif
            
            fetch('/admin/check-pending-requests?_=' + new Date().getTime(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.count !== currentRequestCount) {
                    // Ada perubahan jumlah request, refresh halaman
                    document.location.reload();
                }
            })
            .catch(error => console.error('Error checking for new requests:', error));
        }
        
        // Deteksi tab aktif/tidak aktif untuk menghemat resource
        document.addEventListener('visibilitychange', function() {
            isPageActive = document.visibilityState === 'visible';
            
            if (isPageActive) {
                // Jika tab menjadi aktif, cek segera dan mulai polling lagi
                checkForNewRequests();
                if (!refreshInterval) startAutoRefresh();
            } else {
                // Jika tab tidak aktif, hentikan polling
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
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
    });
</script>
@endpush
