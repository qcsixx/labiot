/**
 * ADMIN MAIN JS
 * File JS utama untuk semua halaman admin
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('JS admin terinisialisasi');

    // Inisialisasi semua fungsi
    initSweetAlert();
    // initSidebar(); // Removed because we have a new sidebar implementation
    initTooltips();
    initDropdowns();
    initFormValidation();
    initDataTables();
    initModals();
    initSearchFunctionality();
    initNotificationPolling();

    // Inisialisasi Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    } else {
        console.error("Lucide tidak tersedia, memuat ulang library...");

        // Tambahkan script lucide secara dinamis jika belum dimuat
        var script = document.createElement('script');
        script.src = "https://unpkg.com/lucide@latest/dist/umd/lucide.min.js";
        script.onload = function() {
            lucide.createIcons();
        };
        document.head.appendChild(script);
    }

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
            icon: 'swal2-icon-custom'
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

    // Tambahkan style untuk toast
    const style = document.createElement('style');
    style.textContent = `
        .colored-toast {
            background-color: white !important;
            border-radius: 1rem !important;
            padding: 1rem !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }
        .colored-toast .swal2-title {
            color: #1a202c !important;
            font-size: 0.875rem !important;
            margin-left: 0.5rem !important;
            display: flex !important;
            align-items: center !important;
            font-weight: 500 !important;
        }
        .colored-toast .swal2-icon {
            margin: 0 0.75rem 0 0 !important;
            padding: 0 !important;
            width: 1.75rem !important;
            height: 1.75rem !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .colored-toast .swal2-icon-content {
            font-size: 1.25rem !important;
            font-weight: bold !important;
        }
        .colored-toast .swal2-success {
            background-color: rgba(34, 197, 94, 0.15) !important;
            color: #22c55e !important;
            border-radius: 50% !important;
            width: 1.75rem !important;
            height: 1.75rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .colored-toast .swal2-success-ring {
            border: none !important;
            background-color: transparent !important;
        }
        .colored-toast .swal2-success-line-tip,
        .colored-toast .swal2-success-line-long {
            background-color: #22c55e !important;
            height: 0.15rem !important;
        }
        .colored-toast .swal2-error {
            background-color: rgba(239, 68, 68, 0.15) !important;
            color: #ef4444 !important;
            border-radius: 50% !important;
            width: 1.75rem !important;
            height: 1.75rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .colored-toast .swal2-error-x {
            height: 0.15rem !important;
        }
        .colored-toast .swal2-info {
            background-color: rgba(59, 130, 246, 0.15) !important;
            color: #3b82f6 !important;
            border-radius: 50% !important;
            width: 1.75rem !important;
            height: 1.75rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .colored-toast .swal2-warning {
            background-color: rgba(245, 158, 11, 0.15) !important;
            color: #f59e0b !important;
            border-radius: 50% !important;
            width: 1.75rem !important;
            height: 1.75rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .colored-toast .swal2-question {
            background-color: rgba(15, 76, 129, 0.15) !important;
            color: #0F4C81 !important;
            border-radius: 50% !important;
            width: 1.75rem !important;
            height: 1.75rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .swal2-icon-custom {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
        }
        .swal2-icon-custom .swal2-icon-content {
            font-size: 1.25rem !important;
            font-weight: bold !important;
        }
        .swal2-container.swal2-center > .swal2-popup {
            grid-column: 2;
            grid-row: 2;
            align-self: center;
            justify-self: center;
        }
    `;
    document.head.appendChild(style);

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
            confirmButtonColor: '#0F4C81', // Biru UB
            cancelButtonColor: '#ef4444', // Merah
            confirmButtonText: 'Ya, lanjutkan!',
            cancelButtonText: 'Batal',
            buttonsStyling: true, // Menggunakan styling default SweetAlert2
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
        };

        return Swal.fire({...defaultOptions, ...options});
    }

    // Fungsi khusus untuk konfirmasi penghapusan
    window.confirmDelete = function(form, itemName, options = {}) {
        const deleteOptions = {
            title: 'Konfirmasi Hapus',
            text: `Anda yakin ingin menghapus "${itemName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0F4C81', // Biru UB
            cancelButtonColor: '#ef4444', // Merah
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            buttonsStyling: true, // Menggunakan styling default SweetAlert2
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
        };

        Swal.fire({...deleteOptions, ...options}).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form dengan fetch
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading state
                    Swal.close();

                    // Tampilkan toast notification
                    showToast(data.status, data.message);

                    // Refresh halaman setelah delay
                    setTimeout(() => {
                        window.location.reload();
                        }, 1500);
                })
                .catch(error => {
                    // Hide loading state
                        Swal.close();

                    // Tampilkan error toast
                    showToast('error', 'Terjadi kesalahan saat menghapus data');
                });
            }
        });
    }
}

// =====================================================
// MODAL FUNCTIONS
// =====================================================
function initModals() {
    // Fungsi untuk membuka modal
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show'); // Hanya tambahkan class show

            // Reset form errors when opening modal
            if (modalId === 'addItemModal' && document.getElementById('addItemForm')) {
                document.querySelectorAll('#addItemForm .form-error').forEach(el => el.innerHTML = '');
                document.getElementById('addItemForm').reset();

                // Reset image preview jika ada
                const imagePreview = document.getElementById('image_preview');
                if (imagePreview) {
                    imagePreview.style.display = 'none';
                    imagePreview.querySelector('img').src = '';
                }
            } else if (modalId === 'editItemModal' && document.getElementById('editItemForm')) {
                document.querySelectorAll('#editItemForm .form-error').forEach(el => el.innerHTML = '');
            }

            // Prevent body scrolling when modal is open
            document.body.style.overflow = 'hidden';

            // Make sure all Lucide icons are rendered
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }

    // Fungsi untuk menutup modal
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show'); // Hapus class show

            // Re-enable body scrolling
            document.body.style.overflow = 'auto';
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('show'); // Hapus class show
            document.body.style.overflow = 'auto';
        }
    }

    // Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => {
                modal.classList.remove('show'); // Hapus class show
                    document.body.style.overflow = 'auto';
            });
        }
    });

    // Function to edit item in manajemen-barang page
    window.editItem = function(id, name, category, quantity, image) {
        // Reset error messages
        document.querySelectorAll('#editItemForm .form-error').forEach(el => el.innerHTML = '');

        // Populate form fields
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_quantity').value = quantity;
        document.getElementById('old_image').value = image;

        // Update form action URL for the specific route
        const baseUrl = document.getElementById('editItemForm').dataset.baseUrl || '/admin/manajemen-barang';
        document.getElementById('editItemForm').action = `${baseUrl}/${id}`;

        // Update current image preview
        const currentImageContainer = document.getElementById('current_image');
        if (currentImageContainer) {
            if (image) {
                const imgElement = currentImageContainer.querySelector('img');
                if (imgElement) {
                    // Update image source with proper path
                    if (image.includes('/')) {
                        imgElement.src = image;
                    } else {
                    imgElement.src = `/storage/items/${image}`;
                    }
                }
                currentImageContainer.style.display = 'block';
            } else {
                currentImageContainer.style.display = 'none';
            }
        }

        openModal('editItemModal');
    }

    // Initialize AJAX form submission for manajemen-barang
    $(document).ready(function() {
        if ($('#addItemForm').length) {
            initManajemenBarangAjax();
            initImagePreview();
        }
    });
}

function initManajemenBarangAjax() {
    // Form submission dengan AJAX untuk form tambah barang
    $('#addItemForm').submit(function(e) {
        e.preventDefault();

        // Validasi form sebelum mengirim
        let isValid = true;
        const name = $('#name').val();
        const category = $('#category').val();
        const quantity = $('#quantity').val();

        // Reset error messages
        $('.form-error').text('');

        // Validasi nama
        if (!name || name.trim() === '') {
            $('#name-error').text('Nama barang tidak boleh kosong');
            isValid = false;
        }

        // Validasi kategori
        if (!category || category === '') {
            $('#category-error').text('Kategori barang harus dipilih');
            isValid = false;
        }

        // Validasi jumlah
        if (!quantity) {
            $('#quantity-error').text('Jumlah barang harus diisi');
            isValid = false;
        } else if (isNaN(quantity) || parseInt(quantity) < 0) {
            $('#quantity-error').text('Jumlah barang harus berupa angka positif');
            isValid = false;
        }

        if (!isValid) {
            return false;
        }

        // Show loading state
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Sedang menambahkan barang',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Hide loading state
                Swal.close();

                // Tampilkan notifikasi sukses
                showToast('success', 'Barang berhasil ditambahkan');
                closeModal('addItemModal');

                // Refresh halaman setelah delay
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                // Hide loading state
                Swal.close();

                var errors = xhr.responseJSON?.errors;

                // Reset error messages
                $('.form-error').text('');

                // Display validation errors
                if (errors) {
                    $.each(errors, function(key, value) {
                        if (key === 'name') {
                            $('#' + key + '-error').text('Nama barang: ' + value[0]);
                        } else if (key === 'category') {
                            $('#' + key + '-error').text('Kategori: ' + value[0]);
                        } else if (key === 'quantity') {
                            $('#' + key + '-error').text('Jumlah: ' + value[0]);
                        } else if (key === 'image') {
                            $('#' + key + '-error').text('Foto: ' + value[0]);
                        } else {
                        $('#' + key + '-error').text(value[0]);
                        }
                    });
                } else {
                    showToast('error', 'Terjadi kesalahan saat menambahkan barang');
                }
            }
        });
    });

    // Form submission dengan AJAX untuk form edit barang
    $('#editItemForm').submit(function(e) {
        e.preventDefault();

        // Validasi form sebelum mengirim
        let isValid = true;
        const name = $('#edit_name').val();
        const category = $('#edit_category').val();
        const quantity = $('#edit_quantity').val();

        // Reset error messages
        $('.form-error').text('');

        // Validasi nama
        if (!name || name.trim() === '') {
            $('#edit_name-error').text('Nama barang tidak boleh kosong');
            isValid = false;
        }

        // Validasi kategori
        if (!category || category === '') {
            $('#edit_category-error').text('Kategori barang harus dipilih');
            isValid = false;
        }

        // Validasi jumlah
        if (!quantity) {
            $('#edit_quantity-error').text('Jumlah barang harus diisi');
            isValid = false;
        } else if (isNaN(quantity) || parseInt(quantity) < 0) {
            $('#edit_quantity-error').text('Jumlah barang harus berupa angka positif');
            isValid = false;
        }

        if (!isValid) {
            return false;
        }

        // Show loading state
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Sedang mengubah data barang',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Hide loading state
                Swal.close();

                // Tampilkan notifikasi sukses
                showToast('success', 'Barang berhasil diperbarui');
                closeModal('editItemModal');

                // Refresh halaman setelah delay
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                // Hide loading state
                Swal.close();

                var errors = xhr.responseJSON?.errors;

                // Reset error messages
                $('.form-error').text('');

                // Display validation errors
                if (errors) {
                    $.each(errors, function(key, value) {
                        if (key === 'name') {
                            $('#edit_' + key + '-error').text('Nama barang: ' + value[0]);
                        } else if (key === 'category') {
                            $('#edit_' + key + '-error').text('Kategori: ' + value[0]);
                        } else if (key === 'quantity') {
                            $('#edit_' + key + '-error').text('Jumlah: ' + value[0]);
                        } else if (key === 'image') {
                            $('#edit_' + key + '-error').text('Foto: ' + value[0]);
                        } else {
                        $('#edit_' + key + '-error').text(value[0]);
                        }
                    });
                } else {
                    showToast('error', 'Terjadi kesalahan saat memperbarui barang');
                }
            }
        });
    });
}

// Fungsi untuk preview gambar
function initImagePreview() {
    // Tambahkan preview untuk form tambah barang
    const imageInput = document.getElementById('image');
    if (imageInput) {
        // Buat container preview jika belum ada
        let previewContainer = document.getElementById('image_preview');
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.id = 'image_preview';
            previewContainer.classList.add('mt-3');
            previewContainer.innerHTML = `
                <p class="text-sm font-medium text-gray-700 mb-2">Preview Gambar:</p>
                <img src="" alt="Preview" class="max-w-xs rounded-lg shadow-md">
            `;
            imageInput.parentNode.appendChild(previewContainer);
        }

        // Tambahkan event listener untuk input file
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewContainer.querySelector('img').src = e.target.result;
                    previewContainer.style.display = 'block';
                }

                reader.readAsDataURL(this.files[0]);
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }

    // Tambahkan preview untuk form edit barang
    const editImageInput = document.getElementById('edit_image');
    if (editImageInput) {
        // Buat container preview jika belum ada
        let editPreviewContainer = document.getElementById('edit_image_preview');
        if (!editPreviewContainer) {
            editPreviewContainer = document.createElement('div');
            editPreviewContainer.id = 'edit_image_preview';
            editPreviewContainer.classList.add('mt-3');
            editPreviewContainer.innerHTML = `
                <p class="text-sm font-medium text-gray-700 mb-2">Preview Gambar Baru:</p>
                <img src="" alt="Preview" class="max-w-xs rounded-lg shadow-md">
            `;
            editImageInput.parentNode.insertBefore(editPreviewContainer, document.getElementById('current_image'));
        }

        // Tambahkan event listener untuk input file
        editImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    editPreviewContainer.querySelector('img').src = e.target.result;
                    editPreviewContainer.style.display = 'block';
                }

                reader.readAsDataURL(this.files[0]);
            } else {
                editPreviewContainer.style.display = 'none';
            }
        });
    }
}

// =====================================================
// SIDEBAR MENU
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Tidak perlu lagi fungsi toggle sidebar
});

// =====================================================
// SIDEBAR MENU - MOBILE
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Tambahkan event listener untuk hamburger menu
    const hamburgerMenu = document.getElementById('hamburgerMenu');
    const sidebar = document.getElementById('sidebar');

    if (hamburgerMenu && sidebar) {
        hamburgerMenu.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });

        // Tutup sidebar saat klik di luar
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 768 &&
                !sidebar.contains(event.target) &&
                !hamburgerMenu.contains(event.target) &&
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    }

    console.log('Admin sidebar initialized');
});

// =====================================================
// TOOLTIPS
// =====================================================
function initTooltips() {
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    if (tooltipTriggers.length === 0) return;

    tooltipTriggers.forEach(trigger => {
        const tooltipText = trigger.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = tooltipText;

        trigger.appendChild(tooltip);

        trigger.addEventListener('mouseenter', () => {
            tooltip.classList.add('show');
        });

        trigger.addEventListener('mouseleave', () => {
            tooltip.classList.remove('show');
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
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const dropdownId = trigger.getAttribute('data-dropdown-toggle');
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.classList.toggle('hidden');

                // Posisi dropdown
                positionDropdown(trigger, dropdown);
            }
        });
    });

    // Helper untuk memposisikan dropdown
    function positionDropdown(trigger, dropdown) {
        const triggerRect = trigger.getBoundingClientRect();
        const dropdownRect = dropdown.getBoundingClientRect();

        // Periksa apakah dropdown akan keluar dari viewpoint ke bawah
        const bottomSpace = window.innerHeight - triggerRect.bottom;
        if (bottomSpace < dropdownRect.height && triggerRect.top > dropdownRect.height) {
            dropdown.style.top = 'auto';
            dropdown.style.bottom = '100%';
            dropdown.style.marginBottom = '0.5rem';
            dropdown.style.marginTop = '0';
        } else {
            dropdown.style.top = '100%';
            dropdown.style.bottom = 'auto';
            dropdown.style.marginTop = '0.5rem';
            dropdown.style.marginBottom = '0';
        }
    }

    // Close dropdown ketika klik di luar
    document.addEventListener('click', (e) => {
        const dropdowns = document.querySelectorAll('.dropdown-menu:not(.hidden)');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
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
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

// =====================================================
// DataTables Initialization
// =====================================================
function initDataTables() {
    // Periksa apakah jQuery dan DataTables ada
    if (typeof $ === 'undefined' || typeof $.fn.DataTable !== 'function') return;

    // Inisialisasi semua tabel dengan kelas .datatable
    const tables = document.querySelectorAll('table.datatable');
    if (tables.length === 0) return;

    tables.forEach(table => {
        $(table).DataTable({
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
            }
        });
    });
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

// =====================================================
// BULK ACTIONS
// =====================================================
function initBulkActions() {
    const bulkActionContainers = document.querySelectorAll('.bulk-actions');
    if (bulkActionContainers.length === 0) return;

    bulkActionContainers.forEach(container => {
        const selectAllCheckbox = container.querySelector('.select-all');
        const itemCheckboxes = document.querySelectorAll('.select-item');
        const bulkActionButton = container.querySelector('.bulk-action-btn');

        if (!selectAllCheckbox || !bulkActionButton) return;

        // Select all checkbox
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateBulkActionButton();
        });

        // Individual checkboxes
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkActionButton();
                // Update select all checkbox
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });

        // Update bulk action button status
        function updateBulkActionButton() {
            const checkedCount = document.querySelectorAll('.select-item:checked').length;
            bulkActionButton.disabled = checkedCount === 0;
            bulkActionButton.querySelector('.count').textContent = checkedCount;
        }

        // Bulk action form submission
        const bulkActionForm = container.closest('form');
        if (bulkActionForm) {
            bulkActionForm.addEventListener('submit', function(e) {
                const checkedCount = document.querySelectorAll('.select-item:checked').length;
                if (checkedCount === 0) {
                    e.preventDefault();
                    showToast('Pilih minimal satu item', 'warning');
                    return;
                }

                const action = bulkActionForm.querySelector('.bulk-action-select').value;
                if (action === 'delete') {
                    e.preventDefault();
                    confirmDelete({
                        title: 'Hapus ' + checkedCount + ' item?',
                        text: 'Item yang dihapus tidak dapat dikembalikan'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            bulkActionForm.submit();
                        }
                    });
                }
            });
        }
    });
}

// =====================================================
// SEARCH FUNCTIONALITY
// =====================================================
function initSearchFunctionality() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('search');
    const searchSpinner = document.getElementById('searchSpinner');

    // Jika elemen pencarian ada
    if (searchForm && searchInput) {
        // Pastikan tidak ada submit otomatis dari form
        searchForm.onsubmit = function(e) {
            e.preventDefault();
            handleSearch();
        };

        // Hanya kirim form saat menekan Enter
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearch();
            }
        });

        // Fungsi untuk handle pencarian
        function handleSearch() {
            // Tampilkan spinner dengan efek smooth
            if (searchSpinner) {
                searchSpinner.classList.add('show');

                // Gunakan timeout untuk animasi lebih smooth saat submit
                setTimeout(function() {
                    searchForm.submit();
                }, 300);
            } else {
                searchForm.submit();
            }
        }
    }
}

// Expose global utility functions
window.ajaxRequest = ajaxRequest;
// window.confirmDelete sudah didefinisikan di bawah

// Fungsi untuk filter otomatis di halaman laporan peminjaman
$(document).ready(function() {
    console.log('Initializing filter functionality');

    // Auto-submit filter form when date changes
    $('#filter-date').on('change', function() {
        console.log('Date filter changed');
        submitFilterForm();
    });

    // Auto-submit filter form when status changes
    $('#filter-status').on('change', function() {
        console.log('Status filter changed');
        submitFilterForm();
    });

    // Auto-submit filter form when search input changes (with debounce)
    let searchTimer;
    $('#filter-search').on('input', function() {
        console.log('Search input changed');
        // Show spinner
        $('#searchSpinner').addClass('active');

        // Clear previous timer
        clearTimeout(searchTimer);

        // Set new timer
        searchTimer = setTimeout(function() {
            submitFilterForm();
        }, 500); // 500ms delay for better UX
    });

    // Handle Enter key on search
    $('#filter-search').on('keydown', function(e) {
        if (e.key === 'Enter') {
            console.log('Enter key pressed in search');
            e.preventDefault();
            clearTimeout(searchTimer);
            submitFilterForm();
        }
    });

    // Function to submit filter form
    function submitFilterForm() {
        console.log('Submitting filter form');
        if ($('#filterForm').length) {
            console.log('Filter form found, adding active class to spinner');
            $('#searchSpinner').addClass('active');
            $('#filterForm').submit();
        } else {
            console.log('Filter form not found');
        }
    }
});

// Fungsi untuk menangani persetujuan peminjaman
function handleApproval(requestId, itemName) {
    confirmAction({
        title: 'Konfirmasi Persetujuan',
        text: `Apakah Anda yakin ingin menyetujui peminjaman "${itemName}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0F4C81',
        cancelButtonColor: '#ef4444',
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
            // Show loading state
            Swal.fire({
                title: 'Memproses Persetujuan',
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

                    // Tampilkan toast dulu
                    showToast('success', 'Peminjaman berhasil disetujui');
                }
            });

            // Submit form setelah delay
            setTimeout(() => {
                // Menggunakan form submission biasa
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/borrow-requests/${requestId}/approve`;
                form.style.display = 'none';

                // Tambahkan CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrfInput);

                // Tambahkan method PUT
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }, 1500); // Delay 1.5 detik agar toast terlihat
        }
    });
}

// Fungsi untuk menangani penolakan peminjaman
function handleRejection(requestId, itemName) {
    Swal.fire({
        title: 'Masukkan Alasan Penolakan',
        input: 'textarea',
        inputPlaceholder: 'Alasan penolakan peminjaman barang...',
        inputAttributes: {
            'aria-label': 'Alasan penolakan',
            'maxlength': 255
        },
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Kirim',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0F4C81',
        cancelButtonColor: '#ef4444',
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
        },
        preConfirm: (reason) => {
            if (!reason.trim()) {
                Swal.showValidationMessage('Alasan penolakan tidak boleh kosong');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Memproses Penolakan',
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

                    // Tampilkan toast dulu
                    showToast('success', 'Peminjaman berhasil ditolak');
                }
            });

            // Submit form setelah delay
            setTimeout(() => {
                // Menggunakan form submission biasa
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/borrow-requests/${requestId}/reject`;
                form.style.display = 'none';

                // Tambahkan CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                form.appendChild(csrfInput);

                // Tambahkan method PUT
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);

                // Tambahkan notes
                const notesInput = document.createElement('input');
                notesInput.type = 'hidden';
                notesInput.name = 'notes';
                notesInput.value = result.value;
                form.appendChild(notesInput);

                document.body.appendChild(form);
                form.submit();
            }, 1500); // Delay 1.5 detik agar toast terlihat
        }
    });
}

// Fungsi konfirmasi untuk pengelolaan user
window.confirmDelete = function(form, userName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus user ${userName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--danger)',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading state
            Swal.fire({
                title: 'Memproses...',
                text: 'Menghapus user',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();

                    // Tampilkan toast dulu
                    showToast('success', 'User berhasil dihapus');

                    // Submit form setelah delay
                    setTimeout(() => {
                        form.submit();
                    }, 300);
                }
            });
        }
    });
};

window.confirmSuspend = function(form, userName) {
    Swal.fire({
        title: 'Konfirmasi Suspend',
        text: `Apakah Anda yakin ingin menangguhkan user ${userName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--warning)',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Suspend',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading state
            Swal.fire({
                title: 'Memproses...',
                text: 'Menangguhkan user',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();

                    // Tampilkan toast dulu
                    showToast('success', 'User berhasil ditangguhkan');

                    // Submit form setelah delay
                    setTimeout(() => {
                        form.submit();
                    }, 300);
                }
            });
        }
    });
};

window.confirmActivate = function(form, userName) {
    Swal.fire({
        title: 'Konfirmasi Aktivasi',
        text: `Apakah Anda yakin ingin mengaktifkan kembali user ${userName}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--success)',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Aktifkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading state
            Swal.fire({
                title: 'Memproses...',
                text: 'Mengaktifkan user',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();

                    // Tampilkan toast dulu
                    showToast('success', 'User berhasil diaktifkan');

                    // Submit form setelah delay
                    setTimeout(() => {
                        form.submit();
                    }, 300);
            }
        });
        }
    });
};

/**
 * Fungsi untuk auto-refresh notifikasi pada sidebar
 * Melakukan polling ke server untuk mendapatkan jumlah notifikasi terbaru
 */
function initNotificationPolling() {
    // Polling interval dalam milidetik (15 detik)
    const POLLING_INTERVAL = 15000;

    // Variabel untuk menyimpan notifikasi sebelumnya
    let prevPendingBorrow = 0;
    let prevPendingReturn = 0;

    // Fungsi untuk mengambil jumlah notifikasi terbaru
    function fetchNotifications() {
        fetch('/admin/get-notifications', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Update badge peminjaman barang
            const peminjamanBadge = document.querySelector('a[href*="persetujuan-peminjaman"] .sidebar-badge');
            if (data.pending_borrow > 0) {
                if (peminjamanBadge) {
                    // Tambahkan animasi flash jika jumlah bertambah
                    if (data.pending_borrow > prevPendingBorrow && prevPendingBorrow > 0) {
                        flashBadge(peminjamanBadge);
                    }

                    peminjamanBadge.textContent = data.pending_borrow;
                    peminjamanBadge.classList.remove('hidden');
    } else {
                    const peminjamanLink = document.querySelector('a[href*="persetujuan-peminjaman"]');
                    if (peminjamanLink) {
                        const badge = document.createElement('div');
                        badge.className = 'sidebar-badge';
                        badge.textContent = data.pending_borrow;
                        peminjamanLink.appendChild(badge);

                        // Flash badge baru
                        flashBadge(badge);
                    }
                }
            } else if (peminjamanBadge) {
                peminjamanBadge.classList.add('hidden');
            }

            // Update badge pengembalian barang
            const pengembalianBadge = document.querySelector('a[href*="pengembalian-barang"] .sidebar-badge');
            if (data.pending_return > 0) {
                if (pengembalianBadge) {
                    // Tambahkan animasi flash jika jumlah bertambah
                    if (data.pending_return > prevPendingReturn && prevPendingReturn > 0) {
                        flashBadge(pengembalianBadge);
                    }

                    pengembalianBadge.textContent = data.pending_return;
                    pengembalianBadge.classList.remove('hidden');
                } else {
                    const pengembalianLink = document.querySelector('a[href*="pengembalian-barang"]');
                    if (pengembalianLink) {
                        const badge = document.createElement('div');
                        badge.className = 'sidebar-badge';
                        badge.textContent = data.pending_return;
                        pengembalianLink.appendChild(badge);

                        // Flash badge baru
                        flashBadge(badge);
    }
                }
            } else if (pengembalianBadge) {
                pengembalianBadge.classList.add('hidden');
            }

            // Simpan nilai notifikasi sebelumnya
            prevPendingBorrow = data.pending_borrow;
            prevPendingReturn = data.pending_return;
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
        });
    }

    // Fungsi untuk animasi flash pada badge
    function flashBadge(badge) {
        badge.style.animation = 'none';
        setTimeout(() => {
            badge.style.animation = 'flash-badge 1s 2';
        }, 10);
    }

    // Tambahkan style untuk animasi flash
    const style = document.createElement('style');
    style.textContent = `
        @keyframes flash-badge {
            0%, 50%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            25%, 75% {
                opacity: 0.6;
                transform: scale(1.2);
            }
        }
    `;
    document.head.appendChild(style);

    // Panggil sekali pada awal load halaman
    fetchNotifications();

    // Set interval untuk polling
    setInterval(fetchNotifications, POLLING_INTERVAL);
}
