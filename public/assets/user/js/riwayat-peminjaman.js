/**
 * RIWAYAT PEMINJAMAN JS
 * File khusus untuk halaman riwayat peminjaman
 */

// Debug mode
const DEBUG = true;

// Logger
function log(...args) {
    if (DEBUG) {
        console.log(...args);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    log('Riwayat peminjaman JS loaded');
    
    // Inisialisasi event handlers
    initFilterForms();
    initDetailModals();
});

/**
 * Inisialisasi formulir filter
 */
function initFilterForms() {
    // Form filter submit otomatis hanya untuk select
    const filterInputs = document.querySelectorAll('#filterForm select');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // Validasi rentang tanggal tidak diperlukan lagi karena sudah dihandle oleh flatpickr
}

/**
 * Inisialisasi modal detail
 */
function initDetailModals() {
    log('Initializing detail modals');
    
    // Tombol close untuk modal - bind ulang
    document.querySelectorAll('.modal-close').forEach(button => {
        // Hapus event listener lama jika ada
        button.removeEventListener('click', closeModal);
        // Tambahkan event listener baru
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            log('Close button clicked');
            closeModal();
        });
    });
    
    // Tutup modal saat klik di luar modal (overlay)
    const modal = document.getElementById('detailModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                log('Modal overlay clicked');
                closeModal();
            }
        });
    }
    
    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            log('Escape key pressed, closing modal');
            closeModal();
        }
    });
    
    // Tambahkan event listener untuk semua tombol detail
    document.querySelectorAll('.detail-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const requestId = this.closest('.borrow-card').dataset.requestId;
            log('Detail button clicked for request ID:', requestId);
            openDetailModal(requestId);
        });
    });
}

/**
 * Fungsi untuk menutup modal - dibuat lebih robust
 */
function closeModal() {
    log('Closing modal');
    
    // Force stop any ongoing fetch
    if (window.currentFetchController) {
        try {
            window.currentFetchController.abort();
            log('Aborted ongoing fetch request');
        } catch (error) {
            log('Error aborting fetch:', error);
        }
    }
    
    try {
        // Sembunyikan modal langsung tanpa animasi
        const modal = document.getElementById('detailModal');
        if (!modal) {
            log('Error: Modal element not found on close');
            return;
        }
        
        // Sembunyikan modal langsung
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.classList.add('hidden');
        
        // Kembalikan body ke kondisi normal
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        
        log('Modal closed successfully');
    } catch (error) {
        log('Error closing modal:', error);
        
        // Final fallback with direct DOM manipulation
        try {
            document.getElementById('detailModal').style.display = 'none';
            document.body.style.overflow = '';
            document.body.classList.remove('modal-open');
        } catch (finalError) {
            log('Final error closing modal:', finalError);
        }
    }
}

/**
 * Format tanggal
 */
function formatDate(dateString, includeTime = false) {
    if (!dateString) return '-';
    
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
        return dateString;
    }
}

/**
 * Fungsi untuk membuka dan mengisi modal detail
 */
function openDetailModal(requestId) {
    log('Opening modal for request ID:', requestId);
    
    if (!requestId) {
        log('Error: No request ID provided');
        alert('ID permintaan tidak valid');
        return;
    }
    
    // Menampilkan modal
    const modal = document.getElementById('detailModal');
    if (!modal) {
        log('Error: Modal element not found');
        return;
    }
    
    // Tampilkan modal langsung tanpa animasi
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    modal.classList.add('show');
    
    // Lock scroll pada body dengan teknik yang lebih baik
    document.body.classList.add('modal-open');
    
    // Kalkuasi lebar scrollbar agar tidak terjadi lompatan layout
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    if (scrollbarWidth > 0) {
        document.body.style.paddingRight = `${scrollbarWidth}px`;
    }
    
    // URL untuk API request
    const apiUrl = `/user/detail-peminjaman/${requestId}`;
    log('Fetching data from:', apiUrl);
    
    // Buat controller untuk abort fetch
    if (window.currentFetchController) {
        window.currentFetchController.abort();
    }
    window.currentFetchController = new AbortController();
    const signal = window.currentFetchController.signal;
    
    // Tambahkan timeout untuk mencegah loading stuck
    const timeoutId = setTimeout(() => {
        log('Request timeout after 10 seconds');
        if (!window.currentFetchController.signal.aborted) {
            window.currentFetchController.abort();
        }
        
        const content = document.getElementById('modal-content');
        if (content) {
            content.innerHTML = `
                <div class="p-6 text-center">
                    <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Timeout</h3>
                    <p class="text-gray-600 mb-4">Permintaan memakan waktu terlalu lama. Silakan coba lagi.</p>
                    <div class="flex justify-center space-x-4">
                        <button type="button" onclick="openDetailModal('${requestId}')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[var(--primary)] hover:bg-opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary)]">
                            Coba Lagi
                        </button>
                        <button type="button" onclick="closeModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Tutup
                        </button>
                    </div>
                </div>
            `;
        }
    }, 10000); // 10 detik timeout
    
    // Ambil data detail peminjaman
    fetch(apiUrl, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        signal: signal
    })
    .then(response => {
        // Batalkan timeout karena response sudah diterima
        clearTimeout(timeoutId);
        log('Response received, status:', response.status);
        
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('Data peminjaman tidak ditemukan');
            }
            throw new Error(`Server error: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        log('Data received:', data);
        
        // Dump full raw data untuk debugging
        console.log('FULL DATA STRUCTURE:', JSON.stringify(data));
        
        const content = document.getElementById('modal-content');
        
        try {
            // Coba handle berbagai kemungkinan format data
            if (data === null || data === undefined) {
                throw new Error('Data kosong dari server');
            }
            
            console.log('Analyzing data structure for itemTrackings:');
            // Analisis struktur untuk menemukan data pelacakan
            if (data.borrowRequest && data.borrowRequest.item_trackings) {
                console.log('Found itemTrackings as item_trackings in borrowRequest');
            } else if (data.borrowRequest && data.borrowRequest.itemTrackings) {
                console.log('Found itemTrackings in borrowRequest');
            } else if (data.item_trackings) {
                console.log('Found item_trackings directly in data');
            } else if (data.itemTrackings) {
                console.log('Found itemTrackings directly in data');
            } else if (data.trackings) {
                console.log('Found trackings in data');
            } else {
                console.log('No tracking data structure found');
            }
            
            // Menentukan struktur data yang benar
            let borrowData = null;
            let trackingData = null;
            
            // Prioritaskan data pelacakan dari property trackings yang dikirim API
            if (data.trackings && Array.isArray(data.trackings)) {
                console.log('Using trackings property from API response');
                trackingData = data.trackings;
            }
            
            // Selanjutnya tentukan borrowData dari berbagai kemungkinan struktur
            if (data.borrowRequest) {
                log('Data format: borrowRequest property');
                borrowData = data.borrowRequest;
                // Cek jika trackingData belum didapatkan
                if (!trackingData) {
                    trackingData = data.borrowRequest.itemTrackings || data.borrowRequest.item_trackings;
                }
            } else if (data.success && data.borrow) {
                log('Data format: success + borrow property');
                borrowData = data.borrow;
                if (!trackingData) {
                    trackingData = data.borrow.itemTrackings || data.borrow.item_trackings;
                }
            } else if (data.item) {
                log('Data format: direct object');
                borrowData = data;
                if (!trackingData) {
                    trackingData = data.itemTrackings || data.item_trackings;
                }
            } else if (data.success && data.data) {
                // Format API umum
                log('Data format: success + data property');
                borrowData = data.data;
                if (!trackingData) {
                    trackingData = data.data.itemTrackings || data.data.item_trackings;
                }
            } else {
                // Fallback: coba gunakan data langsung
                log('Data format: fallback to direct data');
                borrowData = data;
                // Cek jika tracking ada dalam format lain dan belum didapatkan
                if (!trackingData) {
                    trackingData = data.itemTrackings || data.item_trackings || data.tracking || [];
                }
            }
            
            // Fill modal content
            if (borrowData) {
                fillModalContent(borrowData);
                
                // Render tracking data if available
                if (trackingData && trackingData.length > 0) {
                    console.log('Tracking data found:', JSON.stringify(trackingData, null, 2));
                    renderTrackingTimeline(trackingData);
                } else {
                    console.log('No tracking data found in response:', {
                        hasTrackingProperty: !!data.itemTrackings || !!data.trackings || !!data.tracking,
                        trackingLength: (data.itemTrackings && data.itemTrackings.length) || 
                                      (data.trackings && data.trackings.length) || 
                                      (data.tracking && data.tracking.length) || 0,
                        response: JSON.stringify(data, null, 2)
                    });
                    showEmptyTrackingMessage();
                }
            } else {
                throw new Error('Data peminjaman tidak ditemukan dalam respons');
            }
        } catch (error) {
            log('Error processing data:', error);
            showErrorInModal(error.message, requestId);
        }
    })
    .catch(error => {
        // Batalkan timeout karena error sudah tertangkap
        clearTimeout(timeoutId);
        log('Error fetching detail data:', error);
        
        // Jangan tampilkan error jika request dibatalkan secara sengaja
        if (error.name === 'AbortError') {
            log('Request was aborted');
            return;
        }
        
        showErrorInModal(error.message, requestId);
    });
}

/**
 * Menampilkan pesan error dalam modal
 */
function showErrorInModal(errorMessage, requestId) {
    const loader = document.getElementById('modal-loader');
    const content = document.getElementById('modal-content');
    
    // Sembunyikan loader, tampilkan pesan error
    if (loader) {
        loader.classList.add('hidden');
    }
    
    if (content) {
        content.classList.remove('hidden');
        content.innerHTML = `
            <div class="p-6 text-center">
                <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Gagal Memuat Data</h3>
                <p class="text-gray-600 mb-4">${errorMessage}</p>
                <div class="flex justify-center space-x-4">
                    <button type="button" onclick="openDetailModal('${requestId}')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[var(--primary)] hover:bg-opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary)]">
                        Coba Lagi
                    </button>
                    <button type="button" onclick="closeModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Tutup
                    </button>
                </div>
            </div>
        `;
    }
}

/**
 * Mengisi konten modal dengan data peminjaman
 */
function fillModalContent(data) {
    log('Filling modal content with data:', data);
    
    try {
        // Validasi data utama
        if (!data) {
            throw new Error('Data tidak tersedia');
        }
        
        if (!data.item && !data.item_id) {
            throw new Error('Data item tidak tersedia');
        }
        
        // Handle jika item adalah ID saja
        if (!data.item && data.item_id) {
            data.item = {
                id: data.item_id,
                name: data.item_name || 'Nama barang tidak tersedia',
                image: data.item_image || null
            };
        }
        
        // Isi data item
        const itemName = document.getElementById('modal-item-name');
        const itemId = document.getElementById('modal-item-id');
        const itemCategory = document.getElementById('modal-item-category');
        const itemQuantity = document.getElementById('modal-item-quantity');
        
        if (itemName) itemName.textContent = data.item.name || 'Tidak ada nama';
        if (itemId) itemId.textContent = data.item.id || data.item_id || 'N/A';
        if (itemCategory) itemCategory.textContent = data.item.category || data.category || 'Umum';
        if (itemQuantity) itemQuantity.textContent = (data.quantity || 0) + ' unit';
        
        // Status peminjaman
        const itemStatus = document.getElementById('modal-item-status');
        let statusLabel = '';
        let statusClass = '';
        
        if (data.status_label) {
            statusLabel = data.status_label;
        } else if (data.status && typeof data.status === 'object') {
            statusLabel = data.status.value || data.status.label || '';
        } else if (data.status) {
            statusLabel = data.status;
        } else {
            statusLabel = 'Unknown';
        }
        
        // Format status untuk tampilan dan tentukan warnanya
        if (statusLabel === 'completed' || statusLabel === 'Selesai') {
            statusLabel = 'Selesai';
            statusClass = 'bg-green-100 text-green-800 border border-green-300';
        } else if (statusLabel === 'rejected' || statusLabel === 'Ditolak') {
            statusLabel = 'Ditolak';
            statusClass = 'bg-red-100 text-red-800 border border-red-300';
        } else if (statusLabel === 'pending' || statusLabel === 'Menunggu Persetujuan') {
            statusLabel = 'Menunggu Persetujuan';
            statusClass = 'bg-yellow-100 text-yellow-800 border border-yellow-300';
        } else if (statusLabel === 'borrowed' || statusLabel === 'Sedang Dipinjam') {
            statusLabel = 'Sedang Dipinjam';
            statusClass = 'bg-blue-100 text-blue-800 border border-blue-300';
        } else {
            // Default style
            statusClass = 'bg-gray-100 text-gray-800 border border-gray-300';
        }
        
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
        
        // Tanggal pengembalian (jika ada)
        const returnDateContainer = document.getElementById('modal-return-date-container');
        const returnDate = document.getElementById('modal-return-date');
        
        if (returnDateContainer && returnDate) {
            if (data.return_date) {
                returnDate.textContent = formatDate(data.return_date);
                returnDateContainer.style.display = 'flex';
            } else {
                returnDateContainer.style.display = 'none';
            }
        }
        
        // Tujuan peminjaman
        const purpose = document.getElementById('modal-purpose');
        if (purpose) purpose.textContent = data.purpose || '-';
        
        // Catatan admin - diambil dari kolom notes pada tabel borrow_request
        const notesContainer = document.getElementById('modal-admin-notes-container');
        const notesElement = document.getElementById('modal-admin-notes');
        
        // Cek semua kemungkinan field untuk catatan admin
        const notes = data.notes || '';
        
        if (notesContainer && notesElement) {
            if (notes && notes.trim() !== '') {
                notesElement.textContent = notes;
                notesContainer.style.display = 'flex'; // Tampilkan container
                log('Displaying admin notes');
            } else {
                notesContainer.style.display = 'none'; // Sembunyikan jika tidak ada catatan
                log('No admin notes to display');
            }
        }
        
        // Gambar item
        updateItemImage(data);
        
    } catch (error) {
        log('Error filling modal content:', error);
        throw new Error('Gagal menampilkan data: ' + error.message);
    }
}

/**
 * Update gambar item dengan penanganan error yang lebih baik
 */
function updateItemImage(data) {
    const imageContainer = document.getElementById('modal-item-image-container');
    if (!imageContainer) return;
    
    // Coba semua kemungkinan path gambar
    const item = data.item || {};
    let imageUrl = null;
    
    // Cek semua kemungkinan lokasi URL gambar
    if (item.image_url) {
        imageUrl = item.image_url;
    } else if (item.imageUrl) {
        imageUrl = item.imageUrl;
    } else if (item.image) {
        // Periksa apakah sudah berisi path lengkap atau hanya nama file
        if (item.image.startsWith('http')) {
            imageUrl = item.image;
        } else {
            // Jika relatif, tambahkan prefix storage
            imageUrl = '/storage/items/' + item.image;
        }
    } else if (data.item_image) {
        if (data.item_image.startsWith('http')) {
            imageUrl = data.item_image;
        } else {
            imageUrl = '/storage/items/' + data.item_image;
        }
    }
    
    log('Image data:', { 
        image: item.image,
        image_url: item.image_url,
        imageUrl: item.imageUrl,
        item_image: data.item_image,
        finalImageUrl: imageUrl
    });
    
    // Default placeholder
    imageContainer.innerHTML = `
        <div class="flex items-center justify-center w-full h-full bg-gray-100 rounded-lg">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
    `;
    
    // Jika tidak ada URL gambar, hentikan
    if (!imageUrl) {
        log('No image URL found');
        return;
    }
    
    log('Attempting to load image from:', imageUrl);
    
    // Daftar kemungkinan path gambar
    const possiblePaths = [
        imageUrl,
        '/storage/items/' + item.image,
        '/storage/' + item.image,
        '/public/storage/items/' + item.image,
        item.image
    ].filter(Boolean);
    
    // Coba muat gambar dari semua kemungkinan path
    tryLoadImage(possiblePaths, 0, imageContainer, item.name || 'Gambar Barang');
}

/**
 * Coba memuat gambar dari berbagai path yang mungkin
 */
function tryLoadImage(paths, index, container, altText) {
    // Jika sudah mencoba semua path, berhenti
    if (index >= paths.length) {
        log('All image paths failed');
        return;
    }
    
    const currentPath = paths[index];
    log(`Trying image path ${index + 1}/${paths.length}: ${currentPath}`);
    
    const img = new Image();
    
    img.onload = function() {
        log('Image loaded successfully from:', currentPath);
        container.innerHTML = `<img src="${currentPath}" alt="${altText}" class="w-full h-full object-contain rounded-lg">`;
    };
    
    img.onerror = function() {
        log(`Image path ${index + 1} failed, trying next...`);
        // Coba path berikutnya
        tryLoadImage(paths, index + 1, container, altText);
    };
    
    // Tambahkan timestamp untuk mencegah cache
    img.src = currentPath + (currentPath.includes('?') ? '&' : '?') + 'nocache=' + new Date().getTime();
}

// Fungsi untuk menentukan URL foto yang benar
function getPhotoUrl(tracking) {
    // Cek terlebih dahulu jika ada photo_url yang sudah disiapkan oleh server
    if (tracking.photo_url) {
        return tracking.photo_url;
    }
    
    // Jika tidak ada photo_url, coba ambil dari photo
    if (!tracking.photo) {
        return null;
    }
    
    const photo = tracking.photo;
    
    // Kasus 1: Jika path sudah diawali dengan http/https (URL lengkap)
    if (photo.startsWith('http://') || photo.startsWith('https://')) {
        return photo;
    }
    
    // Kasus 2: Jika path sudah diawali dengan 'storage/'
    if (photo.startsWith('storage/')) {
        return '/' + photo;
    }
    
    // Kasus 3: Jika path berada di direktori item_tracking
    if (photo.startsWith('item_tracking/')) {
        return '/storage/' + photo;
    }
    
    // Kasus 4: Jika path berada di direktori tracking_photos (kompatibilitas ke belakang)
    if (photo.startsWith('tracking_photos/')) {
        return '/storage/' + photo;
    }
    
    // Kasus 5: Jika path hanya berupa nama file tanpa direktori
    if (photo.indexOf('/') === -1) {
        return '/storage/item_tracking/' + photo;
    }
    
    // Default: Anggap sebagai path relatif terhadap storage/app/public
    return '/storage/' + photo;
}

/**
 * Render timeline pelacakan
 */
function renderTrackingTimeline(trackings) {
    console.log('renderTrackingTimeline called with data:', trackings);
    const container = document.getElementById('modal-tracking-timeline');
    
    if (!container) {
        console.error('Modal tracking timeline container not found!');
        return;
    }
    
    if (!trackings || trackings.length === 0) {
        showEmptyTrackingMessage();
        return;
    }
    
    // Urutkan tracking berdasarkan tanggal (terbaru di atas)
    const sortedTrackings = [...trackings].sort((a, b) => {
        return new Date(b.tracking_date) - new Date(a.tracking_date);
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
        
        // Tentukan URL foto dengan mempertimbangkan semua kemungkinan format
        const photoUrl = getPhotoUrl(tracking);
        
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
                            ${formatDate(tracking.tracking_date, true)}
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
}

/**
 * Menampilkan pesan timeline kosong
 */
function showEmptyTrackingMessage() {
    const container = document.getElementById('modal-tracking-timeline');
    if (!container) {
        console.error('Modal tracking timeline container not found!');
        return;
    }
    
    container.innerHTML = `
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            <p class="text-gray-600 mb-1 font-medium">Belum Ada Data Pelacakan</p>
            <p class="text-gray-500 text-sm">Peminjaman ini belum memiliki catatan pelacakan barang.</p>
        </div>
    `;
    console.log('Empty tracking message displayed');
}

/**
 * Fungsi untuk menampilkan modal gambar
 */
function showImageModal(imageSrc) {
    log('Showing image modal for:', imageSrc);
    const modal = document.getElementById('imageModal');
    const fullSizeImage = document.getElementById('fullSizeImage');
    
    if (!modal || !fullSizeImage) {
        log('Error: Image modal elements not found');
        return;
    }
    
    // Set gambar sumber
    fullSizeImage.src = imageSrc;
    
    // Tampilkan modal
    modal.classList.remove('hidden');
    
    // Setup event listener
    const closeHandler = function(e) {
        if (e.target === modal) {
            closeImageModal();
        }
    };
    
    // Hapus handler lama jika ada
    modal.removeEventListener('click', closeHandler);
    // Tambahkan handler baru
    modal.addEventListener('click', closeHandler);
    
    const closeButton = document.getElementById('closeImageModal');
    if (closeButton) {
        // Hapus handler lama jika ada
        closeButton.removeEventListener('click', closeImageModal);
        // Tambahkan handler baru
        closeButton.addEventListener('click', closeImageModal);
    }
    
    // Handler untuk tombol Escape
    const escapeHandler = function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    };
    
    // Pastikan hanya ada satu listener
    document.removeEventListener('keydown', escapeHandler);
    document.addEventListener('keydown', escapeHandler);
}

/**
 * Fungsi untuk menutup modal gambar
 */
function closeImageModal() {
    log('Closing image modal');
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Definisi fungsi global agar tersedia untuk inline event handlers
window.showImageModal = showImageModal;
window.closeImageModal = closeImageModal; 