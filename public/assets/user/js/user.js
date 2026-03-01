/**
 * USER MAIN JS
 * File JS utama untuk semua halaman user
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('JS user terinisialisasi');
    
    // Inisialisasi semua fungsi
    initSweetAlert();
    initModals();
    initDropdowns();
    initMobileMenu();
    initFormValidation();
    
    // Tunggu Alpine.js terinisialisasi
    if (window.Alpine) {
        console.log('Alpine.js terdeteksi');
    } else {
        document.addEventListener('alpine:init', function() {
            console.log('Alpine.js terinisialisasi');
        });
    }
});

// =====================================================
// NOTIFIKASI SweetAlert2
// =====================================================
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
    
    // Buat fungsi global untuk SweetAlert standar
    window.showAlert = function(options) {
        return Swal.fire(options);
    }
    
    // Fungsi untuk konfirmasi dengan SweetAlert
    window.confirmAction = function(options) {
        const defaultOptions = {
            title: 'Apakah Anda yakin?',
            text: 'Tindakan ini tidak dapat dibatalkan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0F4C81',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, lanjutkan!',
            cancelButtonText: 'Batal'
        };
        
        return Swal.fire({...defaultOptions, ...options});
    }
}

// =====================================================
// MODAL DIALOGS
// =====================================================
function initModals() {
    // Tombol buka modal
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    if (modalTriggers.length === 0) return;
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                const backdrop = modal.closest('.modal-backdrop');
                backdrop.classList.add('show');
                modal.classList.add('show');
            }
        });
    });
    
    // Tombol tutup modal
    const closeButtons = document.querySelectorAll('[data-modal-close]');
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            const backdrop = modal.closest('.modal-backdrop');
            modal.classList.remove('show');
            backdrop.classList.remove('show');
        });
    });
    
    // Close modal ketika klik backdrop
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                const modal = backdrop.querySelector('.modal');
                modal.classList.remove('show');
                backdrop.classList.remove('show');
            }
        });
    });
}

// =====================================================
// DROPDOWN MENUS
// =====================================================
function initDropdowns() {
    const dropdownTriggers = document.querySelectorAll('[data-dropdown-toggle]');
    if (dropdownTriggers.length === 0) return;
    
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const dropdownId = trigger.getAttribute('data-dropdown-toggle');
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        });
    });
    
    // Close dropdown ketika klik di luar
    document.addEventListener('click', (e) => {
        if (!e.target.matches('[data-dropdown-toggle]') && !e.target.closest('[data-dropdown-toggle]')) {
            const dropdowns = document.querySelectorAll('.dropdown-menu:not(.hidden)');
            dropdowns.forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
    });
}

// =====================================================
// MOBILE MENU
// =====================================================
function initMobileMenu() {
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (!mobileMenuButton || !mobileMenu) return;
    
    mobileMenuButton.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
}

// =====================================================
// FORM VALIDATION
// =====================================================
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    if (forms.length === 0) return;
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Cek semua input yang required
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Cari error message container
                    const errorContainer = field.nextElementSibling;
                    if (errorContainer && errorContainer.classList.contains('invalid-feedback')) {
                        errorContainer.textContent = 'Field ini wajib diisi.';
                    }
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Validasi email
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value.trim() && !validateEmail(field.value)) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Cari error message container
                    const errorContainer = field.nextElementSibling;
                    if (errorContainer && errorContainer.classList.contains('invalid-feedback')) {
                        errorContainer.textContent = 'Format email tidak valid.';
                    }
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                showToast('Mohon periksa kembali form Anda', 'error');
            }
        });
        
        // Reset validasi saat input berubah
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });
    });
}

// Helper function untuk validasi email
function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// =====================================================
// AJAX FUNCTIONS
// =====================================================
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Create request options
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    // Add body if method is POST, PUT, PATCH
    if (['POST', 'PUT', 'PATCH'].includes(method.toUpperCase()) && data) {
        options.body = JSON.stringify(data);
    }
    
    // Fetch API
    fetch(url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (typeof successCallback === 'function') {
                successCallback(data);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            if (typeof errorCallback === 'function') {
                errorCallback(error);
            }
        });
}

// Fungsi global untuk redirect
function redirect(url) {
    window.location.href = url;
}

// Expose global utility functions
window.ajaxRequest = ajaxRequest;
window.redirect = redirect; 