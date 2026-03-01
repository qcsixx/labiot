<!DOCTYPE html>
<html lang="id" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Lab IoT Vokasi UB</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo-vokasi-ub.png') }}" type="image/x-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Admin CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- Lucide icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Flatpickr - Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Page-specific CSS -->
    @stack('styles')

    <!-- Konfigurasi Tailwind -->
    <script>
        // Konfigurasi Tailwind
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0F4C81',
                    }
                }
            }
        }
    </script>

    <!-- Fix for horizontal scrollbar -->
    <style>
        html, body {
            width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            width: 100%;
            overflow-x: hidden;
            position: relative;
        }

        .admin-main {
            overflow-x: hidden;
        }

        /* Sembunyikan scrollbar di semua elemen tapi tetap bisa scroll */
        /* Untuk Chrome, Safari, dan Opera */
        ::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        /* Untuk Firefox */
        * {
            scrollbar-width: none;
        }

        /* Untuk IE dan Edge */
        * {
            -ms-overflow-style: none;
        }

        /* Pastikan semua elemen yang ingin discroll tetap memiliki overflow-y: scroll atau auto */
        .scrollable {
            overflow-y: auto;
        }
    </style>
</head>
<body class="overflow-x-hidden bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    <div class="admin-container">
        <!-- Tombol hamburger menu untuk mobile -->
        <button class="md:hidden hamburger-menu" id="hamburgerMenu">
            <i data-lucide="menu" class="h-5 w-5"></i>
        </button>

        <!-- Sidebar Admin -->
        <aside id="sidebar" class="sidebar bg-[#0F4C81] dark:bg-gray-800 transition-colors duration-300">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/logo-vokasi-ub.png') }}" alt="Logo Vokasi UB" class="w-9 h-9">
                    <span class="sidebar-logo-text">Lab IoT Vokasi UB</span>
                </div>
            </div>

            <!-- Menu Navigasi -->
            <nav class="sidebar-menu">
                <!-- Admin Info -->
                <div class="sidebar-profile">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white font-semibold mr-3 flex-shrink-0">
                                <i data-lucide="user" class="h-5 w-5"></i>
                            </div>
                            <div class="sidebar-text overflow-hidden">
                                <div class="font-semibold text-white truncate">{{ Auth::guard('web_admin')->user()->name ?? 'Admin' }}</div>
                                <div class="text-xs text-white/70">Selamat Datang</div>
                            </div>
                        </div>
                        <!-- Dark Mode Toggle -->
                        <button id="theme-toggle" type="button" class="text-white hover:text-gray-200 focus:outline-none p-2 rounded-lg" aria-label="Toggle Dark Mode">
                            <i id="theme-toggle-dark-icon" class="hidden fas fa-moon"></i>
                            <i id="theme-toggle-light-icon" class="hidden fas fa-sun"></i>
                        </button>
                    </div>
                </div>

                <!-- Daftar Menu Navigasi -->
                <div class="px-3 py-2">
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard-admin') }}"
                      class="sidebar-item {{ Request::is('admin/dashboard-admin') ? 'active' : '' }}">
                        <div class="sidebar-icon flex-shrink-0">
                            <i data-lucide="home"></i>
                        </div>
                        <span class="sidebar-text">Dashboard</span>
                    </a>

                    <!-- Manajemen Barang -->
                    <a href="{{ route('admin.manajemen-barang') }}"
                      class="sidebar-item {{ Request::is('admin/manajemen-barang') ? 'active' : '' }}">
                        <div class="sidebar-icon flex-shrink-0">
                            <i data-lucide="box"></i>
                        </div>
                        <span class="sidebar-text">Manajemen Barang</span>
                    </a>

                    <!-- Peminjaman Barang -->
                    <a href="{{ route('admin.persetujuan-peminjaman') }}"
                      class="sidebar-item {{ Request::is('admin/persetujuan-peminjaman') ? 'active' : '' }}">
                        <div class="sidebar-icon flex-shrink-0">
                            <i data-lucide="clipboard-list"></i>
                        </div>
                        <span class="sidebar-text">Peminjaman Barang</span>
                        <div class="sidebar-badge {{ App\Models\BorrowRequest::where('status', App\Enums\BorrowStatus::PENDING)->count() > 0 ? '' : 'hidden' }}">
                            {{ App\Models\BorrowRequest::where('status', App\Enums\BorrowStatus::PENDING)->count() }}
                        </div>
                    </a>

                    <!-- Pengembalian Barang -->
                    <a href="{{ route('admin.pengembalian-barang') }}"
                      class="sidebar-item {{ Request::is('admin/pengembalian-barang') ? 'active' : '' }}">
                        <div class="sidebar-icon flex-shrink-0">
                            <i data-lucide="rotate-ccw"></i>
                        </div>
                        <span class="sidebar-text">Pengembalian Barang</span>
                        <div class="sidebar-badge {{ App\Models\BorrowRequest::where('status', App\Enums\BorrowStatus::PENDING_RETURN)->count() > 0 ? '' : 'hidden' }}">
                            {{ App\Models\BorrowRequest::where('status', App\Enums\BorrowStatus::PENDING_RETURN)->count() }}
                        </div>
                    </a>

                    <!-- Laporan Peminjaman -->
                    <a href="{{ route('admin.laporan-peminjaman') }}"
                      class="sidebar-item {{ Request::is('admin/laporan-peminjaman') ? 'active' : '' }}">
                        <div class="sidebar-icon flex-shrink-0">
                            <i data-lucide="file-text"></i>
                        </div>
                        <span class="sidebar-text">Laporan Peminjaman</span>
                    </a>

                    <!-- Pengelolaan User -->
                    <a href="{{ route('admin.pengelolaan-user') }}"
                      class="sidebar-item {{ Request::is('admin/pengelolaan-user') ? 'active' : '' }}">
                        <div class="sidebar-icon flex-shrink-0">
                            <i data-lucide="users"></i>
                        </div>
                        <span class="sidebar-text">Pengelolaan User</span>
                    </a>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="mt-3">
                        @csrf
                        <button type="button" onclick="confirmLogout()" class="sidebar-item w-full logout-button">
                            <div class="flex items-center">
                                <i data-lucide="log-out" class="h-5 w-5 mr-2 flex-shrink-0"></i>
                                <span>Logout</span>
                            </div>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- Content Area -->
        <div id="adminContent" class="admin-content">
            <!-- Main Content -->
            <main class="admin-main">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Inisialisasi Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>

    <!-- Admin JavaScript -->
    <script src="{{ asset('assets/admin/js/admin.js') }}?v={{ time() }}"></script>

    <!-- Toast Notifications -->
    <x-toast />


    <!-- Definisi fungsi confirmLogout -->
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Anda yakin ingin keluar dari sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0F4C81',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Logout',
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
                    // Dapatkan CSRF token dari meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    // Gunakan fetch API untuk POST ke route logout
                    fetch('{{ route("logout") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (response.ok || response.redirected) {
                            window.location.href = "{{ route('login') }}";
                        } else {
                            throw new Error('Terjadi kesalahan saat logout');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Terjadi kesalahan saat logout. Coba refresh halaman.'
                        });
                    });
                }
            });
        }
    </script>

    <!-- Script untuk polling notifikasi langsung -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan script hanya berjalan di halaman admin
            if (document.getElementById('sidebar')) {
                // Variabel untuk menyimpan jumlah notifikasi sebelumnya
                let prevPendingBorrow = {{ App\Models\BorrowRequest::where('status', App\Enums\BorrowStatus::PENDING)->count() }};
                let prevPendingReturn = {{ App\Models\BorrowRequest::where('status', App\Enums\BorrowStatus::PENDING_RETURN)->count() }};

                // Fungsi untuk mengambil jumlah notifikasi
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
                                // Tambahkan animasi jika jumlah berubah
                                if (data.pending_borrow !== prevPendingBorrow) {
                                    flashBadge(peminjamanBadge);
                                }

                                peminjamanBadge.textContent = data.pending_borrow;
                                peminjamanBadge.classList.remove('hidden');
                            }
                        } else if (peminjamanBadge) {
                            peminjamanBadge.classList.add('hidden');
                        }

                        // Update badge pengembalian barang
                        const pengembalianBadge = document.querySelector('a[href*="pengembalian-barang"] .sidebar-badge');
                        if (data.pending_return > 0) {
                            if (pengembalianBadge) {
                                // Tambahkan animasi jika jumlah berubah
                                if (data.pending_return !== prevPendingReturn) {
                                    flashBadge(pengembalianBadge);
                                }

                                pengembalianBadge.textContent = data.pending_return;
                                pengembalianBadge.classList.remove('hidden');
                            }
                        } else if (pengembalianBadge) {
                            pengembalianBadge.classList.add('hidden');
                        }

                        // Simpan nilai terbaru
                        prevPendingBorrow = data.pending_borrow;
                        prevPendingReturn = data.pending_return;
                    })
                    .catch(error => {
                        console.error('Error fetching notifications:', error);
                    });
                }

                // Fungsi untuk animasi flash pada badge
                function flashBadge(badge) {
                    badge.animate([
                        { transform: 'scale(1)' },
                        { transform: 'scale(1.3)' },
                        { transform: 'scale(1)' }
                    ], {
                        duration: 600,
                        iterations: 2
                    });
                }

                // Jalankan pertama kali
                fetchNotifications();

                // Set interval polling lebih sering (5 detik)
                setInterval(fetchNotifications, 5000);
            }
        });
    </script>

    <!-- Page-specific JavaScript -->
    @stack('scripts')
    <!-- Dark Mode Script -->
    @include('partials.dark-mode-script')
</body>
</html>
