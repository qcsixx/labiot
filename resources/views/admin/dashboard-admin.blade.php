@extends('layouts.admin.admin-layout')


@section('content')
<!-- Header Section -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-[var(--primary)]">Dashboard Kepala Laboratorium</h1>
    <p class="text-sm text-[var(--secondary)] mt-1">Pantau aktivitas peminjaman dan pengembalian barang lab secara real-time.</p>
</div>

<!-- Data Barang -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="bg-blue-50 bg-opacity-50 rounded-lg shadow p-5 transition-all duration-200 hover:shadow-md hover:bg-blue-100 hover:bg-opacity-30">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-medium text-gray-700">Barang Sedang Dipinjam</h2>
                <p class="text-xl font-bold text-[var(--primary)] mt-2">{{ $borrowedItems }} Barang</p>
            </div>
            <div class="p-3 bg-white rounded-lg shadow">
                <i data-lucide="package" class="h-7 w-7 text-[var(--primary)]"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-emerald-50 bg-opacity-50 rounded-lg shadow p-5 transition-all duration-200 hover:shadow-md hover:bg-emerald-100 hover:bg-opacity-30">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-medium text-gray-700">Stok Tersedia</h2>
                <p class="text-xl font-bold text-green-500 mt-2">{{ $availableItems }} Barang</p>
            </div>
            <div class="p-3 bg-white rounded-lg shadow">
                <i data-lucide="box" class="h-7 w-7 text-green-500"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-purple-50 bg-opacity-50 rounded-lg shadow p-5 transition-all duration-200 hover:shadow-md hover:bg-purple-100 hover:bg-opacity-30"
         onclick="location.href='{{ route('admin.manajemen-barang') }}'">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-medium text-gray-700">Kelola Stok</h2>
                <p class="text-xl font-bold text-purple-500 mt-2">Atur Inventori</p>
            </div>
            <div class="p-3 bg-white rounded-lg shadow">
                <i data-lucide="warehouse" class="h-7 w-7 text-purple-500"></i>
            </div>
        </div>
    </div>
</div>

<!-- Notifikasi Terbaru -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    <!-- Notifikasi Peminjaman -->
    <div class="bg-blue-50 bg-opacity-40 rounded-lg shadow p-5 flex flex-col h-[320px]">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-[var(--primary)] flex items-center">
                <i data-lucide="clipboard-list" class="h-5 w-5 mr-2"></i>
                Pengajuan Peminjaman Terbaru
            </h3>
            <a href="{{ route('admin.persetujuan-peminjaman') }}" class="text-sm text-[var(--primary)] hover:underline">Lihat Semua</a>
        </div>
        <div class="space-y-2 flex-1 overflow-y-auto pr-1 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;">
            <style>
                .scrollbar-hide::-webkit-scrollbar {
                    display: none;
                }
            </style>
            @forelse($latestBorrowRequests->where('status', 'pending') as $request)
                <div class="bg-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer hover:shadow border-l-2 border-[var(--primary)]"
                     onclick="location.href='{{ route('admin.persetujuan-peminjaman') }}'">
                    <div class="p-3">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i data-lucide="user" class="h-4 w-4 text-[var(--primary)]"></i>
                                </div>
                                <span class="font-medium text-gray-800">{{ $request->user->name }}</span>
                            </div>
                            <span class="text-xs bg-yellow-50 border border-yellow-200 text-yellow-800 px-2 py-0.5 rounded">Menunggu</span>
                        </div>
                        <div class="ml-10 bg-gray-50 p-2 rounded-lg text-sm">
                            <p class="text-gray-700">
                                Mengajukan peminjaman <span class="font-medium">{{ $request->quantity ?? 1 }} {{ $request->item->name }}</span>
                            </p>
                            <div class="text-xs text-gray-500 mt-1 flex items-center">
                                <i data-lucide="clock" class="h-3 w-3 mr-1"></i>
                                {{ $request->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg text-center shadow-sm flex flex-col items-center justify-center h-full">
                    <i data-lucide="inbox" class="h-12 w-12 mx-auto mb-3 opacity-70 text-gray-400"></i>
                    <p class="text-gray-500 text-sm">Tidak ada pengajuan peminjaman baru</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Notifikasi Pengembalian -->
    <div class="bg-amber-50 bg-opacity-40 rounded-lg shadow p-5 flex flex-col h-[320px]">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-[var(--warning)] flex items-center">
                <i data-lucide="rotate-ccw" class="h-5 w-5 mr-2"></i>
                Pengajuan Pengembalian Terbaru
            </h3>
            <a href="{{ route('admin.pengembalian-barang') }}" class="text-sm text-[var(--warning)] hover:underline">Lihat Semua</a>
        </div>
        <div class="space-y-2 flex-1 overflow-y-auto pr-1 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;">
            @forelse($latestReturns as $return)
                <div class="bg-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer hover:shadow border-l-2 border-[var(--warning)]"
                     onclick="location.href='{{ route('admin.pengembalian-barang') }}'">
                    <div class="p-3">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 bg-amber-100 rounded-full flex items-center justify-center">
                                    <i data-lucide="user" class="h-4 w-4 text-amber-700"></i>
                                </div>
                                <span class="font-medium text-gray-800">
                                    {{ $return->user->name ?? 'User tidak ditemukan' }}
                                </span>
                            </div>
                            <span class="text-xs bg-amber-50 border-amber-200 text-amber-800 border px-2 py-0.5 rounded">
                                Pengajuan Pengembalian
                            </span>
                        </div>
                        <div class="ml-10 bg-gray-50 p-2 rounded-lg text-sm">
                            <div class="flex justify-between items-start">
                                <p class="text-gray-700">
                                    Mengajukan pengembalian {{ $return->quantity ?? 1 }} {{ $return->item->name ?? 'barang' }}
                                </p>
                                <span class="text-xs text-gray-500">{{ $return->updated_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="text-xs text-gray-500 italic mt-1">
                                <i data-lucide="info" class="h-3 w-3 mr-1 inline"></i>
                                Periksa riwayat pelacakan sebelum menyelesaikan
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg text-center shadow-sm flex flex-col items-center justify-center h-full">
                    <i data-lucide="package" class="h-12 w-12 mx-auto mb-3 opacity-70 text-gray-400"></i>
                    <p class="text-gray-500 text-sm">Tidak ada pengajuan pengembalian baru</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Tombol Aksi Cepat -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <a href="{{ route('admin.laporan-peminjaman') }}" 
       class="bg-indigo-50 bg-opacity-50 rounded-lg shadow p-4 transition-all duration-200 hover:shadow-md hover:bg-indigo-100 hover:bg-opacity-30">
        <div class="flex items-center">
            <div class="p-2 bg-white rounded-lg shadow">
                <i data-lucide="file-text" class="h-6 w-6 text-blue-500"></i>
            </div>
            <div class="ml-3">
                <p class="font-medium text-gray-800">Laporan</p>
                <p class="text-sm text-gray-500">Lihat laporan peminjaman</p>
            </div>
        </div>
    </a>
    
    <a href="{{ route('admin.pengelolaan-user') }}" 
       class="bg-teal-50 bg-opacity-50 rounded-lg shadow p-4 transition-all duration-200 hover:shadow-md hover:bg-teal-100 hover:bg-opacity-30">
        <div class="flex items-center">
            <div class="p-2 bg-white rounded-lg shadow">
                <i data-lucide="users" class="h-6 w-6 text-green-500"></i>
            </div>
            <div class="ml-3">
                <p class="font-medium text-gray-800">Kelola User</p>
                <p class="text-sm text-gray-500">Atur pengguna sistem</p>
            </div>
        </div>
    </a>
</div>
@endsection

@push('scripts')
<script>
    // Inisialisasi Lucide Icons
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
@endpush