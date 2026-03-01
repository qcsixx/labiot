@extends('layouts.admin.admin-layout')

@section('title', 'Manajemen Pengembalian Barang')

@section('content')
<!-- Header Utama -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-[var(--primary)] mb-1">Manajemen Pengembalian Barang</h1>
    <p class="text-sm text-[var(--secondary)]">Admin dapat menyelesaikan pengembalian setelah memverifikasi pelacakan barang dari pengguna.</p>
</div>

<!-- Filter dan Pencarian -->
<div class="bg-[var(--bg-color)] rounded-lg p-4 mb-5 flex flex-wrap items-center justify-between">
    <!-- Pencarian -->
    <div class="w-full md:w-1/3">
        <form id="filterForm" action="{{ route('admin.pengembalian-barang') }}" method="GET">
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

<!-- Tabel Daftar Pengembalian -->
<div class="bg-[var(--card-bg)] rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="divide-x divide-gray-200">
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10 w-16">No</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Peminjam</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Nama Barang</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Jumlah</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Tanggal Pinjam</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Tanggal Kembali</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Tujuan Peminjaman</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Status</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider bg-[var(--primary)] text-white sticky top-0 z-10">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($borrowRequests as $index => $request)
                <tr class="divide-x divide-gray-200 hover:bg-gray-50">
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 text-center whitespace-nowrap w-16">
                        {{ ($borrowRequests->currentPage() - 1) * $borrowRequests->perPage() + $index + 1 }}
                    </td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 text-center whitespace-nowrap">
                        {{ $request->user->name }}
                    </td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 text-center whitespace-nowrap">
                        {{ $request->item->name }}
                    </td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 text-center">
                        {{ $request->quantity ?? '1' }}
                    </td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 text-center whitespace-nowrap">
                        {{ $request->borrow_date->format('d M Y') }}
                    </td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 text-center whitespace-nowrap">
                        {{ $request->return_deadline->format('d M Y') }}
                    </td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 text-center whitespace-nowrap">
                        {{ $request->purpose ?? '-' }}
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-medium rounded-full 
                            @if($request->status == \App\Enums\BorrowStatus::PENDING_RETURN)
                                @if($request->return_deadline->startOfDay() < now()->startOfDay())
                                    bg-[#F44336] text-white
                                @else
                                bg-[#FDB813] text-white
                                @endif
                            @elseif($request->status == \App\Enums\BorrowStatus::COMPLETED)
                                @if($request->return_status == 'late')
                                    bg-orange-500 text-white
                                @else
                                bg-[#4CAF50] text-white
                                @endif
                            @else
                                bg-gray-100 text-gray-800
                            @endif">
                            {{ $request->getStatusLabel() }}
                            @if($request->status == \App\Enums\BorrowStatus::PENDING_RETURN && $request->return_deadline->startOfDay() < now()->startOfDay())
                                (T)
                            @elseif($request->status == \App\Enums\BorrowStatus::COMPLETED && $request->return_status == 'late')
                                (T)
                            @endif
                        </span>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="showTrackingModal({{ $request->request_id }})" 
                                    class="btn btn-primary btn-sm">
                                <i data-lucide="search" class="h-4 w-4 mr-1"></i>
                                Lihat
                            </button>
                            
                            @if($request->status == \App\Enums\BorrowStatus::PENDING_RETURN)
                            <button onclick="confirmComplete({{ $request->request_id }}, '{{ $request->user->name }}', '{{ $request->item->name }}')" 
                                    class="btn btn-success btn-sm">
                                <i data-lucide="check" class="h-4 w-4 mr-1"></i>
                                Selesai
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i data-lucide="package-x" class="h-12 w-12 text-gray-300 mb-2"></i>
                            <p>Tidak ada data pengembalian yang ditemukan</p>
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

<!-- Modal: Timeline Pelacakan Barang -->
<div id="trackingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-y-auto">
    <div class="bg-white rounded-xl shadow-md w-full max-w-[700px] max-h-[90vh] overflow-hidden flex flex-col m-4">
        <div class="flex justify-between items-center p-6 border-b border-gray-200 sticky top-0 bg-[#0F4C81] z-20 shadow-sm">
            <h3 id="trackingModalTitle" class="text-xl font-bold text-white">Pelacakan Barang</h3>
            <button onclick="closeTrackingModal()" class="text-white/80 hover:text-white focus:outline-none transition-colors">
                <i data-lucide="x" class="h-6 w-6"></i>
            </button>
        </div>
        
        <div id="trackingModalContent" class="p-6 overflow-y-auto">
            <!-- Loading spinner, content will be replaced by AJAX -->
            <div class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#0F4C81]"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk menampilkan gambar dalam ukuran penuh -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="relative max-w-4xl max-h-[90vh] w-full mx-4">
        <button id="closeImageModal" class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-lg text-gray-800 hover:text-gray-600 transition-colors z-30">
            <i data-lucide="x" class="h-6 w-6"></i>
            </button>
        <img id="fullSizeImage" src="" alt="Gambar Pelacakan" class="max-w-full max-h-[85vh] mx-auto object-contain bg-white p-2 rounded-lg shadow-xl">
    </div>
</div>

@endsection

@push('scripts')
<style>
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

    /* CSS untuk membuat tabel dapat di-scroll horizontal jika perlu */
    @media (max-width: 1200px) {
        .overflow-x-auto {
            overflow-x: auto;
        }
    }
    
    /* Menambahkan hover effect pada baris tabel */
    .table-hover tr:hover td {
        background-color: #f9fafb;
    }
    
    /* Style untuk truncate text */
    .truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script>
    // Menampilkan modal timeline pelacakan dengan handling yang lebih baik
    function showTrackingModal(requestId) {
        const modal = document.getElementById('trackingModal');
        
        // Tampilkan modal dengan animasi fade
        modal.classList.remove('hidden');
        setTimeout(() => modal.querySelector('.bg-white').classList.add('scale-100', 'opacity-100'), 10);
        
        // Handle click outside modal
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeTrackingModal();
            }
        });
        
        // Handle Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTrackingModal();
            }
        });
        
        // Ambil data pelacakan
        fetch(`/admin/tracking-history/${requestId}`)
            .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
            .then(data => {
                // Update judul modal
                document.getElementById('trackingModalTitle').textContent = `Pelacakan Barang - ${data.item.name}`;
                
                // Buat content HTML untuk modal
                let modalContent = `
                    <!-- Detail Informasi Barang -->
                    <div class="bg-white rounded-lg p-6 mb-6 shadow-md border border-gray-100">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="mb-3">
                                    <span class="text-[var(--primary)] font-medium">Peminjam:</span>
                                    <span class="text-gray-700 ml-2">${data.user.name}</span>
                                </div>
                                <div>
                                    <span class="text-[var(--primary)] font-medium">Nama Barang:</span>
                                    <span class="text-gray-700 ml-2">${data.item.name}</span>
                                </div>
                            </div>
                            
                            <div>
                                <div class="mb-3">
                                    <span class="text-[var(--primary)] font-medium">Tanggal Peminjaman:</span>
                                    <span class="text-gray-700 ml-2">${data.borrow_date}</span>
                                </div>
                                <div>
                                    <span class="text-[var(--primary)] font-medium">Tenggat Pengembalian:</span>
                                    <span class="text-gray-700 ml-2">${data.return_deadline}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Tambahkan timeline jika ada data tracking
                if (data.trackings && data.trackings.length > 0) {
                    // PERBAIKAN: Hapus pengurutan dari lama ke baru
                    // Gunakan data tracking apa adanya - biasanya sudah diurutkan di controller dari terbaru ke terlama
                    const trackingData = data.trackings;
                    
                    // Container timeline dengan header
                    modalContent += `
                    <div class="bg-gray-50 rounded-lg p-6 shadow-md border border-gray-100">
                        <h4 class="font-bold text-[var(--primary)] mb-6 text-lg flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-[#F59E0B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            PROGRESS PELACAKAN BARANG
                        </h4>

                        <!-- Timeline Container dengan Gap Tertentu -->
                        <div class="flex flex-col">
                    `;
                    
                    // Hitung total entries
                    const totalEntries = trackingData.length;
                    
                    // Tampilkan entries dari atas ke bawah (terbaru di atas)
                    trackingData.forEach((tracking, index) => {
                        const isFirst = index === 0; // Ini adalah yang terbaru (paling atas)
                        const isLast = index === totalEntries - 1; // Ini adalah yang terlama (paling bawah)
                        const stepNumber = totalEntries - index; // Angka step dibalik
                        
                        // Tentukan status dan warna untuk step ini
                        let stepStatus = isFirst ? "current" : "completed";
                        // Warna utama adalah biru UB untuk buletan
                        let statusColor = "#0F4C81";
                        let stepTitle = isLast ? "Mulai Pelacakan" : (isFirst ? "Pelacakan Terakhir" : `Pelacakan ke-${stepNumber}`);
                        
                        modalContent += `
                            <!-- Step ${stepNumber} -->
                            <div class="flex">
                                <!-- Left - Timeline Indicator -->
                                <div class="flex flex-col items-center mr-4">
                                    <!-- Step Circle with Number -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full 
                                        bg-[#0F4C81] 
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
                                            ${tracking.formatted_date}
                                        </span>
                                    </div>
                                    
                                    <!-- Step Content Card -->
                                    <div class="bg-white rounded-lg shadow p-4 border-l-4" style="border-left-color: ${statusColor}">
                                        <div class="flex flex-col md:flex-row gap-4">
                                            <!-- Tracking Information -->
                                            <div class="flex-1">
                                                ${tracking.location ? `
                                                <div class="mb-2 flex items-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-[${statusColor}] flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                        <circle cx="12" cy="10" r="3"></circle>
                                                    </svg>
                                                    <div>
                                                        <span class="font-semibold text-gray-700">Lokasi:</span>
                                                        <span class="text-gray-600 ml-1">${tracking.location}</span>
                                                    </div>
                                                </div>
                                                ` : ''}
                                                
                                                ${tracking.notes ? `
                                                <div class="flex items-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-[${statusColor}] flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                        <polyline points="14 2 14 8 20 8"></polyline>
                                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                                        <polyline points="10 9 9 9 8 9"></polyline>
                                                    </svg>
                                                    <div class="text-gray-600">${tracking.notes}</div>
                                                </div>
                                                ` : ''}
                                            </div>
                                            
                                            <!-- Image if exists - DIUBAH: gunakan click untuk zoom -->
                                            ${tracking.image_path ? `
                                            <div class="md:w-1/3 flex-shrink-0">
                                                <div class="image-thumbnail-container" onclick="showImageModal('/storage/${tracking.image_path.replace(/^storage\//, '')}')">
                                                    <img 
                                                        src="/storage/${tracking.image_path.replace(/^storage\//, '')}" 
                                                        alt="Foto pelacakan" 
                                                        onerror="this.onerror=null; this.src='/images/image-placeholder.png';"
                                                        class="w-full h-auto shadow-sm object-cover max-h-32 transition-all duration-300"
                                                    >
                                                    <div class="image-zoom-indicator">
                                                        <i data-lucide="zoom-in" class="h-3 w-3 inline-block mr-0.5"></i> Klik untuk lihat
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
                    modalContent += `
                        </div>
                    </div>`;
                } else {
                    modalContent += `
                        <div class="bg-white rounded-lg p-6 text-center shadow-md border border-gray-100">
                            <i data-lucide="info" class="h-10 w-10 text-gray-300 mx-auto mb-2"></i>
                            <p class="text-gray-500 text-sm">Belum ada data pelacakan untuk peminjaman ini</p>
                        </div>
                    `;
                }
                
                // Tampilkan konten di modal
                document.getElementById('trackingModalContent').innerHTML = modalContent;
                
                // Reinitialize lucide icons
                lucide.createIcons();
            })
            .catch(error => {
                console.error('Error fetching tracking data:', error);
                document.getElementById('trackingModalContent').innerHTML = `
                    <div class="text-center py-8">
                        <i data-lucide="alert-triangle" class="h-12 w-12 text-red-500 mx-auto mb-3"></i>
                        <p class="text-gray-700">Terjadi kesalahan saat memuat data pelacakan.</p>
                        <p class="text-gray-500 mt-1">${error.message || error}</p>
                        <button onclick="closeTrackingModal()" class="mt-4 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                            Tutup
                        </button>
                    </div>
                `;
                lucide.createIcons();
                showToast('error', 'Terjadi kesalahan saat memuat data pelacakan');
            });
    }
    
    function closeTrackingModal() {
        const modal = document.getElementById('trackingModal');
        const modalContent = modal.querySelector('.bg-white');
        
        // Animate out
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        // Hide after animation
        setTimeout(() => {
            modal.classList.add('hidden');
            modalContent.classList.remove('scale-95', 'opacity-0');
        }, 200);
    }
    
    // Fungsi baru untuk menampilkan modal gambar saat gambar diklik
    function showImageModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const fullSizeImage = document.getElementById('fullSizeImage');
        
        // Set gambar sumber
        fullSizeImage.src = imageSrc;
        
        // Tampilkan modal dengan animasi fade in
        modal.classList.remove('hidden');
        
        // Event listener untuk menutup modal saat klik di luar gambar atau tombol close
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeImageModal();
            }
        });
        
        document.getElementById('closeImageModal').addEventListener('click', closeImageModal);
        
        // Event listener untuk tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    }
    
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
    }
    
    // Konfirmasi penyelesaian peminjaman dengan UX yang lebih baik
    function confirmComplete(requestId, userName, itemName) {
        Swal.fire({
            title: 'Konfirmasi Penyelesaian',
            text: `Konfirmasi pengembalian barang ${itemName} dari ${userName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0F4C81',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Selesaikan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-primary mr-3 px-4',
                cancelButton: 'btn btn-danger px-4',
                popup: 'rounded-lg shadow-xl',
                actions: 'flex gap-3 justify-center'
            },
            buttonsStyling: true,
            showClass: {
                popup: 'animate__animated animate__zoomIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__zoomOut animate__faster'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading dengan animasi yang lebih baik
        Swal.fire({
            title: 'Memproses...',
            text: 'Mohon tunggu sebentar',
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
                    }
                });
                
                // Kirim request untuk menyelesaikan pengembalian
                fetch(`/admin/complete-borrowing/${requestId}`, {
            method: 'POST',
            headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                // Jika bukan JSON, mungkin HTML redirect
                Swal.close();
                showToast('success', 'Pengembalian berhasil diselesaikan');
                setTimeout(() => location.reload(), 1500);
                return;
            }
            
            Swal.close();
            showToast('success', data.message || 'Pengembalian berhasil diselesaikan');
            setTimeout(() => location.reload(), 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.close();
            showToast('error', 'Terjadi kesalahan saat menyelesaikan pengembalian');
                });
            }
        });
    }
    
    // Event listeners saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        
        // Auto-submit form saat search diisi
        let searchTimer;
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                document.getElementById('searchSpinner').classList.add('active');
                
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    document.getElementById('filterForm').submit();
                }, 500); // Delay 500ms untuk menghindari terlalu banyak request
            });
            
            // Enter key untuk submit search
            searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimer);
                    document.getElementById('searchSpinner').classList.add('active');
                    document.getElementById('filterForm').submit();
            }
        });
        }
        
        // Inisialisasi lucide icons
        lucide.createIcons();
        
        // Toast notification if success/error from session
        @if(session('success'))
            showToast('success', "{{ session('success') }}");
        @endif
        
        @if(session('error'))
            showToast('error', "{{ session('error') }}");
        @endif

        // Auto Refresh Setup
        let currentReturnRequestCount = {{ count($borrowRequests ?? []) }};
        let refreshInterval;
        let isPageActive = true;
        
        // Mulai polling saat halaman dimuat
        startAutoRefresh();
        
        // Fungsi untuk melakukan polling data baru
        function startAutoRefresh() {
            refreshInterval = setInterval(checkForNewReturnRequests, 10000); // Cek setiap 10 detik
        }
        
        // Fungsi untuk memeriksa permintaan pengembalian baru
        function checkForNewReturnRequests() {
            if (!isPageActive) return; // Jangan refresh jika tab tidak aktif
            
            fetch('/admin/check-return-requests?_=' + new Date().getTime(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.count !== currentReturnRequestCount) {
                    // Ada perubahan jumlah request, refresh halaman
                    document.location.reload();
                }
            })
            .catch(error => console.error('Error checking for new return requests:', error));
        }
        
        // Deteksi tab aktif/tidak aktif untuk menghemat resource
        document.addEventListener('visibilitychange', function() {
            isPageActive = document.visibilityState === 'visible';
            
            if (isPageActive) {
                // Jika tab menjadi aktif, cek segera dan mulai polling lagi
                checkForNewReturnRequests();
                if (!refreshInterval) startAutoRefresh();
            } else {
                // Jika tab tidak aktif, hentikan polling
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        });
    });
</script>
@endpush