/**
 * STATUS PEMINJAMAN JS
 * File JS untuk mengelola status peminjaman
 */

let stream = null; // Global variable untuk stream kamera
let photoTaken = false; // Tracking jika foto sudah diambil
let currentFacingMode = 'user'; // Default untuk laptop/desktop (webcam depan)
let refreshInterval; // Interval untuk auto-refresh
let isPageActive = true; // Status halaman aktif atau tidak
let uploadFailCount = 0;
let useMirrorMode = true; // Mode mirror selalu aktif untuk kamera depan

// Variabel untuk maps dan lokasi
let map = null;
let marker = null;
let userLocation = null;

// Pastikan variabel global yang dideklarasikan di blade tersedia
if (typeof window.flashMessages === 'undefined') {
    window.flashMessages = {};
}

if (typeof window.borrowData === 'undefined') {
    window.borrowData = { count: 0, items: [] };
}

// Buat referensi lokal untuk kenyamanan
const flashMessages = window.flashMessages;
const borrowData = window.borrowData;

document.addEventListener('DOMContentLoaded', function() {
    console.log('JS status peminjaman terinisialisasi');
    
    // Periksa elemen modal saat inisialisasi
    const trackingModal = document.getElementById('tracking-modal');
    if (trackingModal) {
        console.log('Modal element ditemukan pada inisialisasi:', trackingModal);
        console.log('Modal classes:', trackingModal.className);
    } else {
        console.error('CRITICAL: Modal element tidak ditemukan pada inisialisasi!');
    }
    
    // Inisialisasi SweetAlert2 untuk toast
    initSweetAlert();
    
    // Jalankan animasi step progress
    initStepAnimation();
    
    // Tambahkan efek peringatan pada kartu dengan deadline hari ini
    enhanceTodayReturnCards();
    
    // Cek flash messages dari controller
    checkFlashMessages();

    // Setup camera dan file upload handlers
    setupFileUploadHandlers();
    
    // Setup modal interactions
    setupModalInteractions();
    
    // Setup auto refresh
    setupAutoRefresh();
    
    // Tambahkan event listener untuk tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const trackingModal = document.getElementById('tracking-modal');
            if (trackingModal && !trackingModal.classList.contains('hidden') && trackingModal.style.display !== 'none') {
                console.log('Menutup modal dengan tombol ESC');
                closeTrackingModal();
            }
        }
    });
    
    // Inisialisasi Lucide Icons jika belum
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

/**
 * Inisialisasi SweetAlert2 untuk toast notification
 * Menggunakan konfigurasi yang sama persis dengan admin.js
 */
function initSweetAlert() {
    // SweetAlert toast preset (untuk notifikasi kecil)
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        iconColor: '#0F4C81',
        customClass: {
            popup: 'colored-toast',
            title: 'text-gray-800',
            timerProgressBar: 'bg-[#0F4C81]/30',
            icon: 'swal2-icon-custom',
            container: 'swal2-toast-container'
        },
        showClass: {
            popup: 'animate__animated animate__fadeInRight animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutRight animate__faster'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    
    // Buat fungsi global untuk menampilkan toast
    window.showToast = function(icon, message) {
        // Tentukan warna dan HTML ikon untuk setiap jenis toast
        let iconHtml = '';
        let iconColor = '';
        
        switch(icon) {
            case 'success':
                iconHtml = '<i data-lucide="check-circle" class="h-6 w-6"></i>';
                iconColor = '#22c55e';
                break;
            case 'error':
                iconHtml = '<i data-lucide="x-circle" class="h-6 w-6"></i>';
                iconColor = '#ef4444';
                break;
            case 'warning':
                iconHtml = '<i data-lucide="alert-triangle" class="h-6 w-6"></i>';
                iconColor = '#f59e0b';
                break;
            case 'info':
                iconHtml = '<i data-lucide="info" class="h-6 w-6"></i>';
                iconColor = '#3b82f6';
                break;
            default:
                iconHtml = '<i data-lucide="bell" class="h-6 w-6"></i>';
                iconColor = '#0F4C81';
        }
        
        // Jalankan toast dengan custom icon
        const toast = Toast.fire({
            iconHtml: iconHtml,
            iconColor: iconColor,
            title: message,
            didOpen: (toast) => {
                // Inisialisasi ikon Lucide dalam toast
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons({
                        attrs: {
                            class: ['stroke-current']
                        }
                    });
                }
                
                // Event listener standar SweetAlert
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        return toast;
    }
}

/**
 * Fungsi untuk memeriksa flash messages dari controller
 */
function checkFlashMessages() {
    // Cek apakah ada variable flashMessages dari blade
    if (typeof flashMessages !== 'undefined') {
        if (flashMessages.success) {
            showToast('success', flashMessages.success);
        }
        
        if (flashMessages.error) {
            showToast('error', flashMessages.error);
        }
        
        if (flashMessages.warning) {
            showToast('warning', flashMessages.warning);
        }
        
        if (flashMessages.info) {
            showToast('info', flashMessages.info);
        }
    }
}

// Animasi sederhana untuk step progress
function initStepAnimation() {
    const cards = document.querySelectorAll('.borrow-card');
    cards.forEach(card => {
        const steps = card.querySelectorAll('.step-circle');
        
        // Animate steps sequentially on page load
        steps.forEach((step, index) => {
            setTimeout(() => {
                step.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    step.style.transform = 'scale(1)';
                }, 200);
            }, index * 100);
        });
    });
}

// Fungsi untuk menambahkan efek visual pada kartu untuk deadline hari ini
function enhanceTodayReturnCards() {
    console.log('Mencari kartu dengan deadline hari ini...');
    
    // Cari semua kartu yang memiliki class today-return
    const todayReturnCards = document.querySelectorAll('.borrow-card.today-return');
    console.log(`Total kartu dengan deadline hari ini ditemukan: ${todayReturnCards.length}`);
    
    todayReturnCards.forEach(card => {
        console.log('Menerapkan efek untuk card dengan deadline hari ini:', card);
        
        // Highlight deadline text (sudah diterapkan melalui CSS)
        const deadlineInfo = card.querySelector('.info-item:nth-child(3)');
        if (deadlineInfo) {
            const deadlineText = deadlineInfo.querySelector('span span');
            if (deadlineText && !deadlineText.classList.contains('deadline-text-highlight')) {
                deadlineText.classList.add('deadline-text-highlight');
            }
            
            // Update ikon jam dengan warna kuning
            const clockIcon = deadlineInfo.querySelector('svg');
            if (clockIcon) {
                clockIcon.style.color = '#FDB813';
                clockIcon.classList.add('animate-pulse');
            }
        }
        
        // Memastikan badge deadline hari ini tampil dengan benar
        const deadlineBadge = card.querySelector('.custom-deadline-badge');
        if (deadlineBadge) {
            // Pastikan badge terlihat dan animasi berjalan
            deadlineBadge.style.display = 'inline-flex';
        }
    });
}

/**
 * Konfirmasi peminjaman sudah diambil
 * @param {string} requestId - ID peminjaman
 */
function confirmBorrowed(requestId) {
    Swal.fire({
        title: 'Konfirmasi Peminjaman',
        text: 'Apakah kamu sudah mengambil barang ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Sudah Diambil',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading state
            Swal.fire({
                title: 'Sedang Memproses',
                text: 'Mohon tunggu sebentar...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Kirim form dengan jQuery AJAX
            const form = document.getElementById('borrow-form-' + requestId);
            const url = form.getAttribute('action');
            
            console.log('Mengirim konfirmasi ke URL:', url);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Success response:', response);
                    
                    // Update panggilan fungsi dengan parameter yang diubah
                    showToast('success', 'Berhasil! Ayo upload pelacakan pertama untuk barang ini');
                    
                    // Beri waktu sebelum refresh halaman
                    setTimeout(function() {
                        console.log('Refreshing halaman...');
                        window.location.reload();
                    }, 1600);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('XHR Status:', status);
                    console.error('XHR Response:', xhr.responseText);
                    
                    // Update panggilan fungsi dengan parameter yang diubah
                    showToast('success', 'Status peminjaman telah diperbarui menjadi "Sedang Dipinjam"');
                    
                    setTimeout(function() {
                        console.log('Refreshing halaman...');
                        window.location.reload();
                    }, 1600);
                }
            });
        }
    });
}

/**
 * Konfirmasi pengajuan pengembalian
 * @param {string} requestId - ID peminjaman
 */
function confirmReturn(requestId) {
    const form = document.getElementById('return-form-' + requestId);
    const url = form.getAttribute('action');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    
    Swal.fire({
        title: 'Konfirmasi Pengembalian',
        text: 'Yakin ingin mengajukan pengembalian barang ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Ajukan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#F59E0B',
        cancelButtonColor: '#6B7280',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Sedang Memproses',
                text: 'Mohon tunggu...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                    
                    // Kirim request dengan fetch yang lebih sederhana
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'text/html'
                        }
                    })
                    .then(response => {
                        // Update panggilan fungsi dengan parameter yang diubah
                        showToast('success', 'Pengajuan pengembalian telah dikirim');
                        
                            // Refresh halaman setelah pesan sukses
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Update panggilan fungsi dengan parameter yang diubah
                        showToast('error', 'Terjadi kesalahan, coba lagi');
                    });
                }
            });
        }
    });
}

/**
 * Memperbarui tampilan card saat pengajuan pengembalian
 * @param {string} requestId - ID peminjaman
 * @param {boolean} isLate - Apakah pengajuan terlambat
 */
function updateCardToReturnPending(requestId, isLate) {
    // Cari card
    const card = document.querySelector(`#return-form-${requestId}`).closest('.borrow-card');
    if (!card) return;
    
    // Hapus class yang tidak perlu
    card.classList.remove('overdue', 'today-return', 'warning');
    card.classList.add('pending-return');
    
    // Hapus tombol dan tampilkan pesan menunggu
    const cardFooter = card.querySelector('.card-footer');
    if (cardFooter) {
        cardFooter.innerHTML = `
            <div class="flex items-center text-yellow-600 bg-yellow-50 px-4 py-2 rounded-md w-full text-center">
                <div class="mx-auto flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm">Menunggu persetujuan pengembalian</span>
                </div>
            </div>
        `;
    }
} 

/**
 * Setup handlers untuk kamera
 */
function setupFileUploadHandlers() {
    // Simpan referensi elemen
    const photoInput = document.getElementById('photo');
    const imagePreview = document.getElementById('image-preview');
    const preview = document.getElementById('preview');
    const removeImageBtn = document.getElementById('remove-image');
    
    if (!photoInput) return;
    
    // Event ketika tombol hapus gambar di-klik
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            if (!photoInput || !imagePreview) return;
            
            // Reset input file
            photoInput.value = '';
            
            // Sembunyikan preview dan tampilkan kembali kamera
            imagePreview.classList.add('hidden');
            
            const cameraArea = document.getElementById('camera-area');
            if (cameraArea) {
                cameraArea.classList.remove('hidden');
                // Restart kamera
                startCamera();
            }
            
            // Reset flag
            photoTaken = false;
        });
    }
    
    // Setup event untuk file input
    photoInput.addEventListener('change', function() {
        if (this.files.length) {
            handleFileSelect(this.files[0]);
        }
    });
}

/**
 * Setup interaksi dengan modal tracking
 */
function setupModalInteractions() {
    console.log('Setting up modal interactions...');
    
    // Menangani tombol tutup modal
    const closeButton = document.getElementById('close-modal-btn');
    if (closeButton) {
        console.log('Close button ditemukan');
        closeButton.addEventListener('click', closeTrackingModal);
    } else {
        console.error('Close button tidak ditemukan');
    }
    
    // Menangani tombol cancel
    const cancelButton = document.getElementById('cancel-tracking');
    if (cancelButton) {
        console.log('Cancel button ditemukan');
        cancelButton.addEventListener('click', closeTrackingModal);
    }
    
    // Menangani submit tracking form
    const trackingForm = document.getElementById('tracking-form');
    if (trackingForm) {
        console.log('Tracking form ditemukan');
        trackingForm.addEventListener('submit', function(event) {
            event.preventDefault();
            submitTrackingForm(event);
        });
    }
    
    // Tombol fallback
    const fallbackBtn = document.getElementById('use-fallback-btn');
    if (fallbackBtn) {
        console.log('Fallback button ditemukan');
        fallbackBtn.addEventListener('click', switchToFallbackForm);
    }
    
    // Tambahkan listener untuk tombol refresh lokasi
    const refreshLocationBtn = document.getElementById('refresh-location');
    if (refreshLocationBtn) {
        console.log('Refresh location button ditemukan');
        refreshLocationBtn.addEventListener('click', getUserLocation);
    } else {
        console.error('Refresh location button tidak ditemukan');
    }
}

/**
 * Fungsi untuk menampilkan modal pelacakan
 * @param {string} requestId - ID permintaan peminjaman
 */
function showTrackingModal(requestId) {
    console.log('Membuka modal tracking dengan requestId:', requestId);
    
    try {
        // Debug modal element
        const modal = document.getElementById('tracking-modal');
        if (!modal) {
            console.error('CRITICAL: Modal element dengan ID tracking-modal tidak ditemukan!');
            // Fallback: redirect ke URL pelacakan langsung
            const fallbackUrl = window.location.origin + '/user/pelacakan/' + requestId + '/create';
            console.log('Mengalihkan ke URL fallback:', fallbackUrl);
            window.location.href = fallbackUrl;
            return;
        }
        console.log('Modal element ditemukan:', modal);
        
        // Atur request ID ke hidden input
        const requestIdInput = document.getElementById('request_id');
        if (requestIdInput) {
            requestIdInput.value = requestId;
            console.log('Request ID diatur ke:', requestId);
        } else {
            console.error('Element request_id tidak ditemukan');
        }
        
        // Reset form
        const trackingForm = document.getElementById('tracking-form');
        if (trackingForm) {
            trackingForm.reset();
            console.log('Form direset');
        } else {
            console.error('Tracking form tidak ditemukan');
        }
        
        // Reset status lokasi ke pencarian awal
        const locationStatus = document.getElementById('location-status');
        const locationInput = document.getElementById('location');
        
        if (locationStatus) {
            locationStatus.textContent = 'Mencari lokasi Anda...';
            locationStatus.className = 'text-sm mt-1 text-gray-500';
        }
        
        if (locationInput) {
            locationInput.value = 'Mendapatkan lokasi...';
        }
        
        // Tampilkan area kamera dan sembunyikan preview
        const imagePreview = document.getElementById('image-preview');
        if (imagePreview) {
            imagePreview.classList.add('hidden');
            console.log('Image preview disembunyikan');
        } else {
            console.error('Element image-preview tidak ditemukan');
        }
        
        // Update camera area jika belum
        const cameraArea = document.getElementById('camera-area');
        if (cameraArea) {
            cameraArea.classList.remove('hidden');
            // Tampilkan loading state
            cameraArea.innerHTML = `
                <div class="flex flex-col items-center justify-center h-48 bg-gray-100 rounded-lg">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[var(--primary)]"></div>
                    <p class="mt-3 text-sm text-gray-600">Menyiapkan kamera...</p>
                </div>
            `;
            console.log('Camera area diperbarui dengan loading state');
        } else {
            console.error('Camera area tidak ditemukan');
        }
        
        // Set URL untuk fallback form
        const fallbackForm = document.getElementById('fallback-tracking-form');
        if (fallbackForm) {
            fallbackForm.action = window.location.origin + '/user/pelacakan/' + requestId + '/store';
            
            const fallbackRequestIdInput = document.getElementById('fallback_request_id');
            if (fallbackRequestIdInput) fallbackRequestIdInput.value = requestId;
            console.log('Fallback form URL diatur');
        } else {
            console.warn('Fallback form tidak ditemukan');
        }
        
        // Nonaktifkan scroll pada body
        document.body.classList.add('modal-open');
        console.log('Body scroll dinonaktifkan');
        
        // PERBAIKAN: Tampilkan modal dengan benar
        // - Hapus class hidden
        // - Tambahkan class show
        modal.classList.remove('hidden');
        modal.classList.add('show');
        console.log('Modal ditampilkan dengan class show, hidden dihapus');
        
        // Efek fade in untuk modal content
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.classList.add('show');
            console.log('Modal content ditampilkan');
        } else {
            console.error('Modal content tidak ditemukan');
        }
        
        console.log('Status modal setelah diubah - hidden:', modal.classList.contains('hidden'), 'show:', modal.classList.contains('show'));
        
        // Set fokus ke field lokasi
        if (locationInput) {
            locationInput.focus();
            console.log('Fokus diset ke field lokasi');
        } else {
            console.error('Field lokasi tidak ditemukan');
        }
        
        // Mulai pencarian lokasi dan kamera secara langsung
        setTimeout(() => {
            console.log('Memulai pencarian lokasi otomatis...');
            // Inisialisasi peta kosong terlebih dahulu agar terlihat lebih responsif
            initEmptyMap();
            // Lalu mulai pencarian lokasi
            getUserLocation();
            
            console.log('Memulai inisialisasi kamera...');
            initializeCamera();
        }, 100);
    } catch (error) {
        console.error('Terjadi error saat membuka modal:', error);
        
        // Fallback jika terjadi error
        const fallbackUrl = window.location.origin + '/user/pelacakan/' + requestId + '/create';
        console.log('Mengalihkan ke URL fallback karena error:', fallbackUrl);
        window.location.href = fallbackUrl;
    }
}

// Fungsi untuk inisialisasi peta kosong
function initEmptyMap() {
    const mapContainer = document.getElementById('location-map');
    
    if (!mapContainer || map) return;
    
    // Inisialisasi peta dengan lokasi default (Indonesia)
    map = L.map(mapContainer, {
        zoomControl: false,
        attributionControl: false
    }).setView([-2.548926, 118.0148634], 5);
    
    // Tambahkan tile layer OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Tambahkan control zoom di pojok kiri atas
    L.control.zoom({
        position: 'topleft'
    }).addTo(map);
    
    // Tambahkan attribution di pojok kanan bawah
    L.control.attribution({
        position: 'bottomright',
        prefix: '© <a href="https://leafletjs.com">Leaflet</a>'
    }).addTo(map);
}

/**
 * Inisialisasi kamera saat modal dibuka
 */
function initializeCamera() {
    console.log('Inisialisasi kamera...');
    
    const cameraArea = document.getElementById('camera-area');
    
    if (!cameraArea) {
        console.error('Elemen camera area tidak ditemukan');
        return;
    }
    
    // Reset tampilan area kamera
    cameraArea.innerHTML = `
        <div class="flex flex-col items-center justify-center h-48 bg-gray-100 rounded-lg">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[var(--primary)]"></div>
            <p class="mt-3 text-sm text-gray-600">Meminta akses kamera...</p>
        </div>
    `;
    
    // Tambahkan timeout untuk mendeteksi jika kamera tidak muncul dalam waktu tertentu
    const cameraTimeout = setTimeout(() => {
        console.log('Timeout: Kamera tidak muncul dalam waktu yang ditentukan');
        
        // Jika stream belum ada, berarti kamera belum berhasil diinisialisasi
        if (!stream) {
            // Tambahkan tombol bantuan
            addCameraHelpButton();
            
            // Update tampilan area kamera
            cameraArea.innerHTML = `
                <div class="flex flex-col items-center justify-center h-48 bg-gray-100 rounded-lg p-4">
                    <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="text-sm text-gray-600 mb-4">Kamera tidak dapat diakses.</p>
                    <button type="button" id="diagnose-camera" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Bantu Perbaiki Kamera
                    </button>
                    <button type="button" id="use-file-upload" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Gunakan Upload File
                    </button>
                </div>
            `;
            
            // Tambahkan event listener ke tombol diagnosa
            const diagnoseButton = document.getElementById('diagnose-camera');
            if (diagnoseButton) {
                diagnoseButton.addEventListener('click', diagnoseCameraIssue);
            }
            
            // Tambahkan event listener ke tombol upload file
            const useFileUploadButton = document.getElementById('use-file-upload');
            if (useFileUploadButton) {
                useFileUploadButton.addEventListener('click', () => {
                    showAlternativeUploadOption();
                });
            }
        }
    }, 5000); // Tunggu 5 detik
    
    // Hentikan kamera sebelumnya jika ada
    if (stream) {
        stopCamera();
    }
    
    // Periksa dukungan getUserMedia
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.error('Browser tidak mendukung getUserMedia');
        clearTimeout(cameraTimeout);
        showAlternativeUploadOption();
        return;
    }
    
    // Request akses kamera
    console.log('Meminta akses kamera dengan mode:', currentFacingMode);
    
    const constraints = {
        video: { 
            facingMode: currentFacingMode,
            width: { ideal: 1280 },
            height: { ideal: 720 }
        },
        audio: false
    };
    
    navigator.mediaDevices.getUserMedia(constraints)
        .then(function(mediaStream) {
            // Clear timeout karena kamera berhasil diakses
            clearTimeout(cameraTimeout);
            
            console.log('Akses kamera berhasil diperoleh');
            stream = mediaStream;
            
            // Pulihkan tampilan kamera area dengan video dan tombol
            cameraArea.innerHTML = `
                <div class="border rounded-lg overflow-hidden">
                    <video id="camera-preview" class="w-full h-auto" autoplay playsinline></video>
                </div>
                <div class="flex justify-center mt-3 space-x-2">
                    <button type="button" id="capture-photo" class="px-4 py-2 bg-[var(--primary)] text-white rounded-lg hover:bg-[#0e447a] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        </svg>
                        Ambil Foto
                    </button>
                    <button type="button" id="switch-camera" class="px-3 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Ganti Kamera
                    </button>
                </div>
            `;
            
            // Perlu mendapatkan referensi baru ke elemen video
            const newCameraPreview = document.getElementById('camera-preview');
            if (!newCameraPreview) {
                console.error('Tidak dapat menemukan elemen video setelah me-render ulang');
                throw new Error('Elemen video tidak ditemukan');
            }
            
            // Pastikan elemen video dapat terlihat dan terapkan mode mirror sesuai pengaturan
            newCameraPreview.style.display = 'block';
            newCameraPreview.style.width = '100%';
            newCameraPreview.style.height = 'auto';
            
            // Terapkan efek mirror hanya jika mode mirror aktif dan kamera depan
            if (useMirrorMode && currentFacingMode === 'user') {
                newCameraPreview.style.transform = 'scaleX(-1)'; // Efek mirror seperti bercermin
                console.log('Menggunakan mode mirror (seperti bercermin)');
            } else {
                newCameraPreview.style.transform = 'scaleX(1)'; // Tidak ada efek mirror
                console.log('Menggunakan mode non-mirror (seperti orang lain melihat Anda)');
            }
            
            // Tambahkan track ke video
            newCameraPreview.srcObject = mediaStream;
            
            // Play video dengan explicit promise
            console.log('Mencoba memutar video');
            
            // Tambahkan event listener untuk debug
            newCameraPreview.addEventListener('playing', function() {
                console.log('Video berhasil diputar');
            });
            
            newCameraPreview.addEventListener('error', function(e) {
                console.error('Error saat memutar video:', e);
            });
            
            // Force resolusi
            if (mediaStream.getVideoTracks().length > 0) {
                const videoTrack = mediaStream.getVideoTracks()[0];
                console.log('Video track capabilities:', videoTrack.getCapabilities());
                console.log('Video track settings:', videoTrack.getSettings());
            }
            
            // Coba putar video
            try {
                const playPromise = newCameraPreview.play();
                
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            console.log('Video berhasil diputar');
                            // Setup event listeners untuk tombol-tombol kamera
                            setupCameraButtons();
                            
                            // Periksa dukungan multiple kamera
                            checkMultipleCameras().then(hasMultipleCameras => {
                                const switchBtn = document.getElementById('switch-camera');
                                if (switchBtn) {
                                    switchBtn.style.display = hasMultipleCameras ? 'block' : 'none';
                                }
                            });
                        })
                        .catch(err => {
                            console.error('Error saat memutar video:', err);
                            
                            // Coba fallback ke mode silent
                            console.log('Mencoba memutar dalam mode silent');
                            newCameraPreview.muted = true;
                            newCameraPreview.volume = 0;
                            newCameraPreview.play()
                                .then(() => {
                                    console.log('Video berhasil diputar dalam mode silent');
                                    setupCameraButtons();
                                })
                                .catch(silentErr => {
                                    console.error('Gagal memutar video bahkan dalam mode silent:', silentErr);
                                    // Tampilkan pesan error
                                    showAlternativeUploadOption();
                                });
                        });
                } else {
                    console.log('Play tidak mengembalikan promise, kemungkinan browser lama');
                    // Browser lama mungkin tidak mengembalikan promise
                    setupCameraButtons();
                }
            } catch (playError) {
                console.error('Terjadi exception saat memutar video:', playError);
                showAlternativeUploadOption();
            }
        })
        .catch(function(err) {
            // Clear timeout karena kita sudah mendapat hasil (meskipun error)
            clearTimeout(cameraTimeout);
            
            console.error('Error akses kamera:', err);
            
            // Tampilkan pesan error yang sesuai
            let errorMessage = 'Tidak dapat mengakses kamera. ';
            
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                errorMessage += 'Anda perlu memberikan izin untuk mengakses kamera.';
            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                errorMessage += 'Tidak menemukan perangkat kamera.';
            } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                errorMessage += 'Kamera sedang digunakan aplikasi lain. Tutup aplikasi lain yang mungkin menggunakan kamera.';
            } else {
                errorMessage += 'Pastikan izin kamera diaktifkan.';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Akses Kamera Gagal',
                text: errorMessage,
                confirmButtonColor: 'var(--primary)',
                footer: '<a href="#" id="camera-help-link">Butuh bantuan?</a>'
            }).then(() => {
                // Pasang event listener ke link bantuan
                const helpLink = document.getElementById('camera-help-link');
                if (helpLink) {
                    helpLink.addEventListener('click', showCameraHelp);
                }
            });
            
            // Tampilkan opsi upload alternatif
            showAlternativeUploadOption();
        });
}

/**
 * Tutup modal tracking
 */
function closeTrackingModal() {
    console.log('Menutup modal tracking');
    
    try {
        // Stop camera jika masih berjalan
        stopCamera();
        
        // Reset peta jika ada
        if (map) {
            console.log('Membersihkan peta');
            map.remove();
            map = null;
            marker = null;
            userLocation = null;
        }
        
        // Sembunyikan modal
        const modal = document.getElementById('tracking-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('show');
        } else {
            console.error('Modal element tidak ditemukan saat mencoba menutup');
        }
        
        // Hapus class untuk scrolling
        document.body.classList.remove('modal-open');
        
        // Reset photo taken flag
        photoTaken = false;
        
        // Reset form jika ada
        const trackingForm = document.getElementById('tracking-form');
        if (trackingForm) {
            trackingForm.reset();
        }
        
        // Reset fallback form jika ada
        const fallbackForm = document.getElementById('fallback-tracking-form');
        if (fallbackForm) {
            fallbackForm.reset();
            fallbackForm.style.display = 'none';
        }
        
        // Sembunyikan fallback container
        const fallbackContainer = document.getElementById('fallback-container');
        if (fallbackContainer) {
            fallbackContainer.classList.add('hidden');
        }
        
        console.log('Modal berhasil ditutup');
    } catch (error) {
        console.error('Terjadi error saat menutup modal:', error);
    }
}

/**
 * Setup auto refresh untuk status peminjaman
 */
function setupAutoRefresh() {
    // Mulai polling saat halaman dimuat
    if (typeof borrowData !== 'undefined') {
        startAutoRefresh();
    }
    
    // Deteksi tab aktif/tidak aktif untuk menghemat resource
    document.addEventListener('visibilitychange', function() {
        isPageActive = document.visibilityState === 'visible';
        
        if (isPageActive) {
            // Jika tab menjadi aktif, cek segera dan mulai polling lagi
            checkForStatusChanges();
            if (!refreshInterval) startAutoRefresh();
        } else {
            // Jika tab tidak aktif, hentikan polling
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    });
}

/**
 * Fungsi untuk melakukan polling data baru
 */
function startAutoRefresh() {
    refreshInterval = setInterval(checkForStatusChanges, 10000); // Cek setiap 10 detik
}

/**
 * Fungsi untuk memeriksa perubahan status
 */
function checkForStatusChanges() {
    if (!isPageActive || !borrowData) return; // Jangan refresh jika tab tidak aktif atau data belum ada
    
    fetch('/user/check-borrow-status?_=' + new Date().getTime(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Cek perubahan jumlah
        if (data.count !== borrowData.count) {
            document.location.reload();
            return;
        }
        
        // Cek perubahan status
        let hasChanges = false;
        
        if (data.data && data.data.length > 0) {
            data.data.forEach(item => {
                // Cari item yang cocok dari data yang ada
                let existingItem = borrowData.items.find(x => x.id === item.id);
                
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
    .catch(error => console.error('Error checking for status changes:', error));
}

/**
 * Fungsi untuk menampilkan panduan bantuan kamera
 */
function showCameraHelp() {
    Swal.fire({
        title: 'Panduan Mengatasi Masalah Kamera',
        icon: 'info',
        html: `
            <div class="text-left">
                <h3 class="font-bold mb-2">1. Periksa izin browser:</h3>
                <ul class="list-disc pl-5 mb-3">
                    <li>Klik ikon kunci/info di address bar</li>
                    <li>Pastikan izin kamera disetel ke "Allow"</li>
                    <li>Muat ulang halaman setelah mengubah izin</li>
                </ul>
                
                <h3 class="font-bold mb-2">2. Pastikan kamera tidak digunakan aplikasi lain:</h3>
                <ul class="list-disc pl-5 mb-3">
                    <li>Tutup aplikasi seperti Zoom, Meet, atau kamera lainnya</li>
                    <li>Restart browser Anda</li>
                </ul>
                
                <h3 class="font-bold mb-2">3. Periksa kamera perangkat:</h3>
                <ul class="list-disc pl-5 mb-3">
                    <li>Buka aplikasi kamera bawaan untuk memastikan kamera berfungsi</li>
                    <li>Periksa pengaturan perangkat untuk masalah driver</li>
                </ul>
                
                <h3 class="font-bold mb-2">4. Jika menggunakan laptop/PC:</h3>
                <ul class="list-disc pl-5 mb-3">
                    <li>Pastikan kamera tidak dimatikan secara fisik (beberapa laptop memiliki switch)</li>
                    <li>Periksa apakah driver kamera sudah terinstal dengan benar</li>
                </ul>
            </div>
        `,
        confirmButtonText: 'Mengerti',
        confirmButtonColor: 'var(--primary)',
        width: '600px'
    });
}

/**
 * Fungsi untuk menghentikan kamera
 */
function stopCamera() {
    console.log('Menghentikan kamera');
    
    if (stream) {
        try {
            // Hentikan semua track
            stream.getTracks().forEach(track => {
                console.log('Menghentikan track:', track.kind);
                track.stop();
            });
            
            // Hapus referensi ke stream
            stream = null;
            
            // Reset video element jika ada
            const cameraPreview = document.getElementById('camera-preview');
            if (cameraPreview) {
                cameraPreview.srcObject = null;
                cameraPreview.pause();
            }
            
            console.log('Kamera berhasil dihentikan');
        } catch (err) {
            console.error('Error saat menghentikan kamera:', err);
        }
    } else {
        console.log('Tidak ada stream kamera yang aktif');
    }
}

/**
 * Fungsi untuk setup tombol kamera
 */
function setupCameraButtons() {
    console.log('Setting up camera buttons');
    
    // Setup tombol ambil foto
    const captureButton = document.getElementById('capture-photo');
    if (captureButton) {
        captureButton.addEventListener('click', capturePhoto);
    }
    
    // Setup tombol switch kamera
    const switchCameraButton = document.getElementById('switch-camera');
    if (switchCameraButton) {
        switchCameraButton.addEventListener('click', function() {
            console.log('Switching camera mode');
            // Toggle facing mode
            currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
            console.log('Mengubah kamera ke mode:', currentFacingMode);
            // Restart kamera dengan mode baru
            initializeCamera();
        });
    }
}

/**
 * Fungsi untuk mengambil foto dari kamera
 */
function capturePhoto() {
    console.log('Mengambil foto dari kamera');
    
    // Dapatkan referensi ke elemen yang diperlukan
    const cameraPreview = document.getElementById('camera-preview');
    const preview = document.getElementById('preview');
    const photoInput = document.getElementById('photo');
    const imagePreview = document.getElementById('image-preview');
    const cameraArea = document.getElementById('camera-area');
    
    // Buat canvas untuk menangkap gambar jika belum ada
    let cameraCanvas = document.getElementById('camera-canvas');
    if (!cameraCanvas) {
        cameraCanvas = document.createElement('canvas');
        cameraCanvas.id = 'camera-canvas';
        cameraCanvas.style.display = 'none';
        document.body.appendChild(cameraCanvas);
    }
    
    // Validasi elemen
    if (!stream || !cameraPreview || !preview || !photoInput || !imagePreview || !cameraArea) {
        console.error('Elemen yang diperlukan tidak ditemukan');
        Swal.fire({
            icon: 'error',
            title: 'Tidak dapat mengambil foto',
            text: 'Kamera tidak aktif atau elemen tidak ditemukan',
            confirmButtonColor: 'var(--primary)'
        });
        return;
    }
    
    try {
        // Dapatkan ukuran video
        const width = cameraPreview.videoWidth;
        const height = cameraPreview.videoHeight;
        
        console.log('Ukuran video:', width, 'x', height);
        
        // Validasi ukuran video
        if (width === 0 || height === 0) {
            console.error('Video memiliki ukuran 0, kamera mungkin belum siap');
            Swal.fire({
                icon: 'error',
                title: 'Kamera belum siap',
                text: 'Tunggu beberapa saat dan coba lagi',
                confirmButtonColor: 'var(--primary)'
            });
            return;
        }
        
        // Atur ukuran canvas sesuai video
        cameraCanvas.width = width;
        cameraCanvas.height = height;
        
        // Ambil gambar dari video ke canvas
        const context = cameraCanvas.getContext('2d');
        
        // Jika menggunakan mode mirror, capture foto sesuai yang terlihat di layar
        if (useMirrorMode && currentFacingMode === 'user') {
            // Simpan state canvas
            context.save();
            // Terapkan transformasi mirror yang sama pada canvas
            context.scale(-1, 1);
            context.translate(-width, 0);
            // Gambar video dengan efek mirror
            context.drawImage(cameraPreview, 0, 0, width, height);
            // Kembalikan state canvas
            context.restore();
            console.log('Foto diambil dengan efek mirror (seperti yang terlihat di layar)');
        } else {
            // Gambar normal tanpa efek mirror
            context.drawImage(cameraPreview, 0, 0, width, height);
            console.log('Foto diambil tanpa efek mirror');
        }
        
        console.log('Gambar berhasil diambil ke canvas');
        
        // Konversi canvas ke blob (file)
        cameraCanvas.toBlob(function(blob) {
            console.log('Canvas berhasil dikonversi ke blob, ukuran:', blob.size);
            
            // Buat file dari blob
            const fileName = `camera_photo_${new Date().getTime()}.jpg`;
            const file = new File([blob], fileName, { type: 'image/jpeg' });
            
            console.log('File berhasil dibuat:', fileName);
            
            // Tambahkan file ke input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            photoInput.files = dataTransfer.files;
            
            console.log('File berhasil ditambahkan ke input');
            
            // Tampilkan preview
            const imageUrl = URL.createObjectURL(blob);
            preview.src = imageUrl;
            imagePreview.classList.remove('hidden');
            cameraArea.classList.add('hidden');
            
            // Tandai foto sudah diambil
            photoTaken = true;
            
            console.log('Preview foto ditampilkan');
        }, 'image/jpeg', 0.85);
    } catch (error) {
        console.error('Error saat mengambil foto:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal mengambil foto',
            text: 'Terjadi kesalahan saat mengambil foto. Silakan coba lagi.',
            confirmButtonColor: 'var(--primary)'
        });
    }
}

/**
 * Tampilkan opsi upload alternatif jika kamera tidak tersedia
 */
function showAlternativeUploadOption() {
    console.log('Menampilkan opsi upload alternatif');
    
    const cameraArea = document.getElementById('camera-area');
    const photoInput = document.getElementById('photo');
    
    if (!cameraArea || !photoInput) {
        console.error('Elemen tidak ditemukan');
        return;
    }
    
    // Ganti tampilan kamera dengan form upload manual
    cameraArea.innerHTML = `
        <div class="text-center p-5 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="mt-3 text-sm text-gray-600">Kamera tidak tersedia. Pilih file gambar sebagai gantinya.</p>
            <button id="manual-file-upload" class="mt-4 px-4 py-2 bg-[var(--primary)] text-white rounded-lg hover:bg-[#0e447a] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Pilih File Gambar
            </button>
        </div>
    `;
    
    // Setup event listener untuk tombol upload
    const manualUploadBtn = document.getElementById('manual-file-upload');
    if (manualUploadBtn) {
        manualUploadBtn.addEventListener('click', function() {
            photoInput.click();
        });
    }
    
    // Setup event listener untuk file input
    photoInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });
}

/**
 * Menangani file gambar yang dipilih
 * @param {File} file - File yang dipilih
 */
function handleFileSelect(file) {
    console.log('Menangani file yang dipilih:', file.name);
    
    const cameraArea = document.getElementById('camera-area');
    const imagePreview = document.getElementById('image-preview');
    const preview = document.getElementById('preview');
    
    if (!cameraArea || !imagePreview || !preview) {
        console.error('Elemen tidak ditemukan');
        return;
    }
    
    // Validasi jenis file
    if (!file.type.match('image.*')) {
        console.error('File bukan gambar:', file.type);
        Swal.fire({
            icon: 'error',
            title: 'File tidak valid',
            text: 'Harap pilih file gambar (JPG, PNG, GIF, dll)',
            confirmButtonColor: 'var(--primary)'
        });
        return;
    }
    
    // Validasi ukuran file (maksimum 5MB)
    if (file.size > 5 * 1024 * 1024) {
        console.error('File terlalu besar:', file.size);
        Swal.fire({
            icon: 'error',
            title: 'File terlalu besar',
            text: 'Ukuran file maksimum adalah 5MB',
            confirmButtonColor: 'var(--primary)'
        });
        return;
    }
    
    // Baca file sebagai Data URL
    const reader = new FileReader();
    
    // Setup event listeners
    reader.onload = function(e) {
        // Set gambar preview
        preview.src = e.target.result;
        
        // Tampilkan preview dan sembunyikan area kamera
        imagePreview.classList.remove('hidden');
        cameraArea.classList.add('hidden');
        
        // Set flag foto sudah diambil
        photoTaken = true;
        
        console.log('Preview file berhasil ditampilkan');
    };
    
    reader.onerror = function(error) {
        console.error('Error membaca file:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal membaca file',
            text: 'Terjadi kesalahan saat membaca file. Silakan coba lagi.',
            confirmButtonColor: 'var(--primary)'
        });
    };
    
    // Mulai membaca file
    console.log('Membaca file...');
    reader.readAsDataURL(file);
}

/**
 * Diagnosa dan perbaiki masalah kamera
 */
function diagnoseCameraIssue() {
    console.log('Mendiagnosis masalah kamera...');
    
    // Cek apakah browser mendukung getUserMedia
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.error('Browser tidak mendukung getUserMedia');
        Swal.fire({
            icon: 'error',
            title: 'Browser Tidak Mendukung',
            text: 'Browser Anda tidak mendukung akses kamera. Coba gunakan Chrome, Firefox, atau Safari terbaru.',
            confirmButtonColor: 'var(--primary)'
        });
        return;
    }
    
    // Cek apakah kamera terdeteksi
    navigator.mediaDevices.enumerateDevices()
        .then(devices => {
            const videoDevices = devices.filter(device => device.kind === 'videoinput');
            console.log('Perangkat video terdeteksi:', videoDevices.length);
            
            if (videoDevices.length === 0) {
                console.error('Tidak ada kamera yang terdeteksi');
                Swal.fire({
                    icon: 'error',
                    title: 'Kamera Tidak Terdeteksi',
                    text: 'Sistem tidak mendeteksi adanya kamera. Pastikan kamera terhubung dan berfungsi.',
                    confirmButtonColor: 'var(--primary)'
                });
                return;
            }
            
            // Coba akses kamera dengan mode berbeda
            currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
            console.log('Mencoba dengan mode kamera:', currentFacingMode);
            
            // Paksa penggunaan kamera pertama yang tersedia
            const constraints = {
                video: {
                    deviceId: { exact: videoDevices[0].deviceId },
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            };
            
            console.log('Mencoba constraints baru:', constraints);
            
            navigator.mediaDevices.getUserMedia(constraints)
                .then(stream => {
                    console.log('Berhasil mendapatkan akses kamera dengan deviceId spesifik');
                    // Matikan stream lama jika ada
                    if (window.stream) {
                        stopCamera();
                    }
                    
                    // Set stream baru
                    window.stream = stream;
                    
                    // Update UI
                    const cameraArea = document.getElementById('camera-area');
                    if (cameraArea) {
                        cameraArea.innerHTML = `
                            <div class="border rounded-lg overflow-hidden">
                                <video id="camera-preview" class="w-full h-auto" autoplay playsinline></video>
                            </div>
                            <div class="flex justify-center mt-3 space-x-2">
                                <button type="button" id="capture-photo" class="px-4 py-2 bg-[var(--primary)] text-white rounded-lg hover:bg-[#0e447a] focus:outline-none focus:ring-2 focus:ring-[var(--primary)]">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    </svg>
                                    Ambil Foto
                                </button>
                            </div>
                        `;
                        
                        // Set video source
                        const video = document.getElementById('camera-preview');
                        if (video) {
                            video.srcObject = stream;
                            video.play().catch(e => console.error('Error memutar video:', e));
                            
                            // Setup buttons
                            setupCameraButtons();
                        }
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Kamera Diperbaiki',
                        text: 'Kamera berhasil diaktifkan, silakan ambil foto.',
                        confirmButtonColor: 'var(--primary)'
                    });
                })
                .catch(err => {
                    console.error('Masih gagal mengakses kamera:', err);
                    
                    // Tampilkan opsi alternatif
                    showAlternativeUploadOption();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Masalah Kamera',
                        html: `
                            <p>Sistem tidak dapat mengakses kamera Anda. Silakan:</p>
                            <ul class="text-left mt-2 ml-4 list-disc">
                                <li>Pastikan Anda telah memberikan izin kamera</li>
                                <li>Periksa apakah kamera digunakan aplikasi lain</li>
                                <li>Coba refresh halaman</li>
                                <li>Gunakan opsi "Pilih File Gambar" sebagai alternatif</li>
                            </ul>
                        `,
                        confirmButtonColor: 'var(--primary)',
                        confirmButtonText: 'Gunakan Upload Manual'
                    });
                });
        })
        .catch(err => {
            console.error('Error saat enumerating devices:', err);
            showAlternativeUploadOption();
        });
}

/**
 * Tambahkan tombol bantuan saat kamera gagal
 */
function addCameraHelpButton() {
    const cameraArea = document.getElementById('camera-area');
    if (!cameraArea) return;
    
    // Tambahkan tombol diagnosa di camera area
    const helpButton = document.createElement('button');
    helpButton.className = 'mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400';
    helpButton.innerHTML = '<svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Kamera Tidak Muncul?';
    helpButton.addEventListener('click', diagnoseCameraIssue);
    
    // Tambahkan tombol ke camera area
    cameraArea.appendChild(helpButton);
}

/**
 * Tambahkan fungsi untuk menampilkan opsi fallback upload setelah beberapa kali gagal
 */
function showFallbackUploadOption() {
    // Increment counter setiap kali upload gagal
    uploadFailCount++;
    
    console.log('Upload gagal count:', uploadFailCount);
    
    // Tampilkan opsi fallback setelah 2 kali gagal
    if (uploadFailCount >= 2) {
        const fallbackContainer = document.getElementById('fallback-container');
        if (fallbackContainer) {
            fallbackContainer.classList.remove('hidden');
        }
        
        // Setup fallback button
        const useFallbackBtn = document.getElementById('use-fallback-btn');
        if (useFallbackBtn) {
            useFallbackBtn.addEventListener('click', switchToFallbackForm);
        }
    }
}

/**
 * Switch ke form fallback tradisional
 */
function switchToFallbackForm() {
    const trackingForm = document.getElementById('tracking-form');
    const fallbackForm = document.getElementById('fallback-tracking-form');
    
    if (!trackingForm || !fallbackForm) return;
    
    // Sembunyikan form utama
    trackingForm.style.display = 'none';
    
    // Tampilkan form fallback
    fallbackForm.style.display = 'block';
    
    // Copy nilai dari form utama ke fallback
    const requestId = document.getElementById('request_id').value;
    const location = document.getElementById('location').value;
    const notes = document.getElementById('notes').value;
    
    // Set URL secara langsung
    fallbackForm.action = window.location.origin + '/user/pelacakan/' + requestId + '/store';
    
    // Set nilai ke form fallback jika element ada
    if (document.getElementById('fallback_request_id')) {
        document.getElementById('fallback_request_id').value = requestId;
    }
    
    if (document.getElementById('fallback_location')) {
        document.getElementById('fallback_location').value = location || '';
    }
    
    if (document.getElementById('fallback_notes')) {
        document.getElementById('fallback_notes').value = notes || '';
    }
    
    // Tidak bisa mengcopy file dari input ke input lain,
    // pengguna perlu memilih file lagi
    Swal.fire({
        icon: 'info',
        title: 'Metode Upload Alternatif',
        text: 'Silakan pilih file foto lagi untuk menggunakan metode upload tradisional',
        confirmButtonColor: 'var(--primary)'
    });
}

/**
 * Memeriksa apakah perangkat memiliki beberapa kamera
 * @returns {Promise<boolean>} - Promise yang diselesaikan dengan true jika perangkat memiliki >1 kamera
 */
function checkMultipleCameras() {
    return new Promise((resolve) => {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            console.log('enumerateDevices() tidak didukung.');
            resolve(false);
            return;
        }

        navigator.mediaDevices.enumerateDevices()
            .then(devices => {
                // Filter untuk perangkat video saja
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                console.log('Perangkat video terdeteksi:', videoDevices.length);
                
                // Kembalikan true jika ada >1 kamera
                resolve(videoDevices.length > 1);
            })
            .catch(err => {
                console.error('Error memeriksa perangkat kamera:', err);
                resolve(false);
            });
    });
} 

// Fungsi untuk mendapatkan lokasi user
function getUserLocation() {
    const locationStatus = document.getElementById('location-status');
    const locationInput = document.getElementById('location');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    
    if (!navigator.geolocation) {
        locationStatus.textContent = 'Geolocation tidak didukung oleh browser Anda';
        locationStatus.className = 'text-sm mt-1 location-status-error';
        return;
    }
    
    // Tampilkan status "mencari" terlebih dahulu dengan warna kuning warning
    locationStatus.textContent = 'Mencari lokasi Anda...';
    locationStatus.className = 'text-sm mt-1 location-status-warning';
    locationInput.value = 'Mendapatkan lokasi...';
    
    // Flag untuk menandai apakah geolokasi sedang diproses
    let geolocationInProgress = true;
    
    // Timer untuk menampilkan pesan "mengizinkan lokasi" jika permintaan izin belum selesai dalam 1.5 detik
    const permissionTimer = setTimeout(() => {
        if (geolocationInProgress) {
            locationStatus.textContent = 'Mohon izinkan akses lokasi pada browser Anda...';
            locationStatus.className = 'text-sm mt-1 location-status-warning font-medium';
        }
    }, 1500);
    
    navigator.geolocation.getCurrentPosition(
        // Success callback
        async (position) => {
            // Tandai bahwa geolokasi sudah selesai
            geolocationInProgress = false;
            clearTimeout(permissionTimer);
            
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // Simpan koordinat di input tersembunyi
            latitudeInput.value = lat;
            longitudeInput.value = lng;
            
            // Update fallback form juga
            const fallbackLatInput = document.getElementById('fallback_latitude');
            const fallbackLngInput = document.getElementById('fallback_longitude');
            if (fallbackLatInput) fallbackLatInput.value = lat;
            if (fallbackLngInput) fallbackLngInput.value = lng;
            
            // Perbarui userLocation
            userLocation = { lat, lng };
            
            // Update status selama reverse geocoding dengan warna kuning
            locationStatus.textContent = 'Mendapatkan alamat lokasi...';
            locationStatus.className = 'text-sm mt-1 location-status-warning';
            
            // Reverse geocode untuk mendapatkan alamat
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
                const data = await response.json();
                
                if (data && data.display_name) {
                    // Tampilkan alamat user-friendly
                    locationInput.value = data.display_name;
                    // Update fallback juga
                    const fallbackLocationInput = document.getElementById('fallback_location');
                    if (fallbackLocationInput) fallbackLocationInput.value = data.display_name;
                    
                    locationStatus.textContent = 'Lokasi ditemukan!';
                    locationStatus.className = 'text-sm mt-1 location-status-success';
                } else {
                    locationInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    locationStatus.textContent = 'Koordinat ditemukan, alamat tidak tersedia';
                    locationStatus.className = 'text-sm mt-1 text-gray-600';
                }
            } catch (error) {
                console.error('Error saat reverse geocoding:', error);
                locationInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                locationStatus.textContent = 'Koordinat ditemukan, alamat tidak tersedia';
                locationStatus.className = 'text-sm mt-1 text-gray-600';
            }
            
            // Inisialisasi atau perbarui peta
            initOrUpdateMap(lat, lng);
        },
        // Error callback
        (error) => {
            // Tandai bahwa geolokasi sudah selesai
            geolocationInProgress = false;
            clearTimeout(permissionTimer);
            
            console.error('Geolocation error:', error);
            
            // Tampilkan pesan yang sesuai dengan jenis error
            if (error.code === 1) { // PERMISSION_DENIED
                locationStatus.textContent = `Akses lokasi ditolak. Mohon izinkan akses lokasi di pengaturan browser Anda.`;
            } else {
                locationStatus.textContent = `Gagal mendapatkan lokasi: ${getLocationErrorMessage(error)}`;
            }
            locationStatus.className = 'text-sm mt-1 location-status-error';
        },
        // Options
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}

// Fungsi untuk mendapatkan pesan error geolocation
function getLocationErrorMessage(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            return "Akses lokasi ditolak. Silakan berikan izin lokasi di pengaturan browser Anda.";
        case error.POSITION_UNAVAILABLE:
            return "Informasi lokasi tidak tersedia.";
        case error.TIMEOUT:
            return "Waktu permintaan lokasi habis.";
        default:
            return "Terjadi kesalahan saat mendapatkan lokasi.";
    }
} 

/**
 * Submit tracking form dengan data koordinat
 * @param {Event} e - Event object from form submission
 */
function submitTrackingForm(e) {
    e.preventDefault();
    
    // Validasi form
    const requestId = document.getElementById('request_id').value;
    const location = document.getElementById('location').value;
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;
    const photo = document.getElementById('photo').files[0];
    
    if (!location || !photo) {
        Swal.fire({
            icon: 'error',
            title: 'Data tidak lengkap',
            text: 'Harap isi semua field yang diperlukan',
            confirmButtonColor: 'var(--primary)'
        });
        return;
    }
    
    // Log data untuk debugging
    console.log("Form data:", {
        requestId: requestId,
        location: location,
        latitude: latitude,
        longitude: longitude,
        photo: photo ? photo.name + ' (' + (photo.size / 1024).toFixed(2) + ' KB)' : 'No photo'
    });
    
    // Cek ukuran file
    if (photo.size > 5 * 1024 * 1024) { // 5MB
        Swal.fire({
            icon: 'error',
            title: 'File terlalu besar',
            text: 'Ukuran file maksimum adalah 5MB. Mohon kompres atau pilih gambar yang lebih kecil.',
            confirmButtonColor: 'var(--primary)'
        });
        return;
    }
    
    // Tampilkan loading state
    const loadingSwal = Swal.fire({
        title: 'Sedang Memproses',
        text: 'Mohon tunggu sebentar...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Set timeout untuk menghindari stuck loading terlalu lama
    const timeoutId = setTimeout(() => {
        loadingSwal.close();
        Swal.fire({
            icon: 'error',
            title: 'Waktu habis',
            text: 'Proses upload terlalu lama, silakan coba lagi',
            confirmButtonColor: 'var(--primary)'
        });
    }, 60000); // 60 detik timeout
    
    // Kirim data ke server - GUNAKAN XMLHttpRequest SEBAGAI ALTERNATIF FETCH
    try {
        // Buat URL langsung dari requestId
        const url = window.location.origin + '/user/pelacakan/' + requestId + '/store';
        console.log("Mengirim data ke:", url);
        
        // Buat FormData baru
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('request_id', requestId);
        formData.append('location', location);
        formData.append('latitude', latitude); // Tambahkan koordinat latitude
        formData.append('longitude', longitude); // Tambahkan koordinat longitude
        formData.append('photo', photo);
        
        // Tambahkan notes jika ada
        const notes = document.getElementById('notes').value;
        if (notes) {
            formData.append('notes', notes);
        }
        
        // Gunakan XMLHttpRequest alih-alih fetch
        const xhr = new XMLHttpRequest();
        
        // Setup event handlers
        xhr.onload = function() {
            // Clear timeout
            clearTimeout(timeoutId);
            
            // Tutup loading state
            loadingSwal.close();
            
            console.log("Response status:", xhr.status);
            console.log("Response text:", xhr.responseText);
            
            // Handle respons
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        showToast('success', 'Pelacakan berhasil disimpan');
                        
                        // Sembunyikan modal
                        closeTrackingModal();
                        
                        // Refresh halaman setelah 1.5 detik
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        // Tampilkan opsi fallback jika server mengembalikan error
                        showFallbackUploadOption();
                        showToast('error', data.message || 'Terjadi kesalahan saat menyimpan pelacakan');
                    }
                } catch (parseError) {
                    console.error("Error parsing JSON:", parseError);
                    // Tampilkan opsi fallback jika gagal parse JSON
                    showFallbackUploadOption();
                    showToast('error', 'Terjadi kesalahan saat memproses respons server');
                }
            } else if (xhr.status === 404) {
                console.error("Error: URL endpoint tidak ditemukan:", url);
                // Tampilkan opsi fallback
                showFallbackUploadOption();
                Swal.fire({
                    icon: 'error',
                    title: 'Endpoint Tidak Ditemukan',
                    text: 'URL endpoint tidak ditemukan (404). Hubungi administrator sistem.',
                    confirmButtonColor: 'var(--primary)'
                });
            } else {
                console.error("Server error:", xhr.status, xhr.statusText);
                // Tampilkan opsi fallback
                showFallbackUploadOption();
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: `Server error: ${xhr.status}. Hubungi administrator.`,
                    confirmButtonColor: 'var(--primary)'
                });
            }
        };
        
        xhr.onerror = function() {
            // Clear timeout
            clearTimeout(timeoutId);
            
            // Tutup loading state
            loadingSwal.close();
            
            console.error("Network error occurred");
            
            // Tampilkan opsi fallback
            showFallbackUploadOption();
            
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Gagal',
                text: 'Terjadi kesalahan jaringan. Periksa koneksi internet Anda dan coba lagi.',
                confirmButtonColor: 'var(--primary)'
            });
        };
        
        xhr.onprogress = function(event) {
            if (event.lengthComputable) {
                const percentComplete = (event.loaded / event.total) * 100;
                console.log(`Upload progress: ${percentComplete.toFixed(2)}%`);
            }
        };
        
        xhr.ontimeout = function() {
            // Clear timeout
            clearTimeout(timeoutId);
            
            // Tutup loading state
            loadingSwal.close();
            
            console.error("Request timed out");
            
            // Tampilkan opsi fallback
            showFallbackUploadOption();
            
            Swal.fire({
                icon: 'error',
                title: 'Waktu Habis',
                text: 'Permintaan timeout. Server terlalu lama merespons. Silakan coba lagi nanti.',
                confirmButtonColor: 'var(--primary)'
            });
        };
        
        // Set timeout untuk request
        xhr.timeout = 50000; // 50 detik
        
        // Buka koneksi dan kirim data
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.send(formData);
        
    } catch (error) {
        // Clear timeout
        clearTimeout(timeoutId);
        
        // Tutup loading state
        loadingSwal.close();
        
        console.error('Error:', error);
        
        // Tampilkan opsi fallback
        showFallbackUploadOption();
        
        // Tampilkan error yang lebih jelas dan detail
        Swal.fire({
            icon: 'error',
            title: 'Gagal mengirim data',
            text: error.message || 'Terjadi kesalahan saat mengirim data ke server',
            confirmButtonColor: 'var(--primary)',
            footer: '<p class="text-sm text-gray-500">Coba lagi atau hubungi administrator sistem</p>'
        });
    }
} 

// Fungsi untuk inisialisasi atau update peta
function initOrUpdateMap(lat, lng) {
    const mapContainer = document.getElementById('location-map');
    
    if (!mapContainer) return;
    
    if (!map) {
        // Inisialisasi peta
        map = L.map(mapContainer, {
            zoomControl: false, // Menonaktifkan control zoom default
            attributionControl: false // Menonaktifkan attribution control default
        }).setView([lat, lng], 16);
        
        // Tambahkan tile layer OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Tambahkan control zoom di pojok kiri atas
        L.control.zoom({
            position: 'topleft'
        }).addTo(map);
        
        // Tambahkan attribution di pojok kanan bawah
        L.control.attribution({
            position: 'bottomright',
            prefix: '© <a href="https://leafletjs.com">Leaflet</a>'
        }).addTo(map);
        
        // Tambahkan circle untuk menunjukkan akurasi dengan warna kuning
        window.locationCircle = L.circle([lat, lng], {
            color: '#F59E0B',         // Warna border kuning
            fillColor: '#F59E0B33',   // Warna fill kuning dengan transparansi
            fillOpacity: 0.25,
            radius: 50,
            weight: 2
        }).addTo(map);
        
        // Gunakan marker standar Leaflet dengan custom icon dari CDN (marker kuning)
        const yellowIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],       // Ukuran standar icon marker Leaflet
            iconAnchor: [12, 41],     // Anchor pada ujung bawah marker
            popupAnchor: [1, -34],    // Posisi popup relatif terhadap marker
            shadowSize: [41, 41]      // Ukuran shadow
        });
        
        // Tambahkan marker dengan custom icon
        marker = L.marker([lat, lng], {
            icon: yellowIcon,  // Gunakan icon kuning
            title: 'Lokasi Anda',
            zIndexOffset: 1000  // Pastikan marker selalu di atas
        }).addTo(map);
    } else {
        // Update view dan marker jika map sudah ada
        map.setView([lat, lng], 16);
        if (marker) {
            marker.setLatLng([lat, lng]);
        }
        
        // Update circle juga jika sudah ada circle sebelumnya
        if (window.locationCircle) {
            window.locationCircle.setLatLng([lat, lng]);
        } else {
            // Buat circle baru jika belum ada
            window.locationCircle = L.circle([lat, lng], {
                color: '#F59E0B',
                fillColor: '#F59E0B33',
                fillOpacity: 0.25,
                radius: 50,
                weight: 2
            }).addTo(map);
        }
    }
} 