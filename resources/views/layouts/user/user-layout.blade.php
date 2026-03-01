<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Lab IoT Vokasi UB'))</title>

    <!-- Critical CSS -->
    <style>
        /* Reset dasar */
        * { box-sizing: border-box; }
        body, html { margin: 0; padding: 0; height: 100%; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8fafc;
        }
        [x-cloak] { display: none !important; }
    </style>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('images/logo-vokasi-ub.png') }}" type="image/x-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome untuk ikon sosial media -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Main CSS untuk User -->
    <link rel="stylesheet" href="{{ asset('assets/user/css/user.css') }}?v={{ time() }}">

    <!-- Dashboard CSS - harus dimuat SETELAH user.css untuk override -->
    <link rel="stylesheet" href="{{ asset('assets/user/css/dashboard.css') }}?v={{ time() }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Animate.css untuk animasi toast -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css">

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
                        'ub-yellow': '#FDB813'
                    }
                }
            }
        }
    </script>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    <!-- Navbar Component -->
    <nav class="navbar fixed top-0 left-0 right-0 w-full z-50" x-data="{ mobileOpen: false, profileOpen: false }" x-cloak>
        <div class="container max-w-[1280px] mx-auto px-4 sm:px-8 md:px-16 lg:px-16">
            @include('layouts.user.navbar')
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex-shrink-0 relative z-10">
        <div class="main-wrapper w-full relative">
            <div class="container max-w-[1280px] mx-auto pt-20 pb-16 px-4 sm:px-8 md:px-16 lg:px-16">
            @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer Component -->
    <footer class="relative z-10 mt-auto bg-[#0F4C81]">
        <div class="container max-w-[1280px] mx-auto px-4 sm:px-8 md:px-16 lg:px-16">
            @include('layouts.user.footer')
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Dark Mode Script -->
    @include('partials.dark-mode-script')

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>

    <!-- Main JavaScript untuk User -->
    <script src="{{ asset('assets/user/js/user.js') }}"></script>

    <!-- Dashboard JavaScript -->
    <script src="{{ asset('assets/user/js/dashboard.js') }}?v={{ time() }}"></script>

    <!-- Scheduler JavaScript -->


    <!-- Lucide Icons Init -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Page-specific JavaScript -->
    @stack('scripts')

    <!-- Fungsi confirmLogout -->
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
</body>
</html>
