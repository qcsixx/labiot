<!DOCTYPE html>
<html lang="id" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Sistem Peminjaman Barang Laboratorium IoT Vokasi UB - Memudahkan proses peminjaman barang di Lab IoT Vokasi UB secara digital, cepat, dan terintegrasi">
    <meta name="keywords" content="lab IoT, vokasi UB, peminjaman barang, laboratorium, universitas brawijaya">
    <meta name="theme-color" content="#0F4C81">
    <meta property="og:title" content="Sistem Peminjaman Lab IoT - Vokasi UB">
    <meta property="og:description" content="Kelola peminjaman barang laboratorium dengan mudah, cepat, dan terstruktur secara terintegrasi.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/logo-vokasi-ub.png') }}">
    <title>Sistem Peminjaman Lab IoT - Vokasi UB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/logo-vokasi-ub.png') }}" type="image/png">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0F4C81',
                        'primary-dark': '#0D3A5E',
                        secondary: '#707070',
                        accent: '#FDB813',
                    },
                    animation: {
                        'pulse-short': 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 3s infinite',
                    },
                }
            }
        }
    </script>
    <style>
        :root {
            --primary: #0F4C81;
            --primary-dark: #0D3A5E;
            --secondary: #707070;
            --bg-color: #F9FAFB;
            --card-bg: #FFFFFF;
            --button-hover: #fdb813;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        /* Sembunyikan scrollbar tetapi tetap bisa scroll */
        html, body {
            /* Untuk Firefox */
            scrollbar-width: none;
            
            /* Untuk IE dan Edge */
            -ms-overflow-style: none;
        }
        
        /* Untuk Chrome, Safari, dan Opera */
        ::-webkit-scrollbar,
        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            display: none;
            width: 0;
            background: transparent;
        }
        
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        
        .mobile-menu.open {
            transform: translateX(0);
        }
        
        /* Step line for guide section */
        .step-line {
            height: 2px;
            background-color: #CBD5E1;
            position: relative;
            z-index: 1;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary);
            color: white;
            font-weight: bold;
            z-index: 2;
            position: relative;
        }
        
        @media (max-width: 768px) {
            .step-line {
                width: 2px;
                height: 40px;
            }
            h1 {
                font-size: 2.25rem; /* 36px */
                line-height: 1.2;
            }
            h2 {
                font-size: 1.75rem; /* 28px */
                line-height: 1.3;
            }
            section {
                padding-top: 3rem;
                padding-bottom: 3rem;
            }
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        
        @media (max-width: 640px) {
            h1 {
                font-size: 1.875rem; /* 30px */
            }
            h2 {
                font-size: 1.5rem; /* 24px */
            }
            .feature-card {
                margin-bottom: 1rem;
            }
        }
        
        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 40;
        }
        
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        /* Improved image handling for responsiveness */
        .responsive-img {
            max-width: 100%;
            height: auto;
            width: 100%;
            object-fit: contain;
        }
        
        /* Enhanced step cards styling */
        .step-card {
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.4s ease;
        }
        
        .step-card:hover {
            transform: translateY(-8px);
        }
        
        .step-card:hover .step-number {
            transform: scale(1.1);
            box-shadow: 0 0 0 5px rgba(15, 76, 129, 0.2);
        }
        
        .step-indicator {
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #0F4D92, #FDB813);
            position: absolute;
            bottom: 0;
            left: 0;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease-out;
        }
        
        .step-card:hover .step-indicator {
            transform: scaleX(1);
        }
        
        .step-number {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .progress-track {
            height: 3px;
            background: linear-gradient(90deg, rgba(15, 77, 146, 0.3), rgba(15, 77, 146, 0.1));
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-bar {
            position: absolute;
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #0F4D92, #FDB813);
            transition: width 0.6s ease-out;
        }
        
        .step-dot {
            transition: all 0.4s ease;
            position: relative;
            z-index: 20;
        }
        
        .step-dot::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 16px;
            background-color: white;
            border-radius: 50%;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .step-dot.active::after {
            opacity: 0.4;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.4;
            }
            70% {
                transform: translate(-50%, -50%) scale(2);
                opacity: 0;
            }
            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0;
            }
        }
        
        /* Fix touch actions on mobile */
        @media (hover: none) {
            .step-card:hover {
                transform: none;
            }
            .step-card:active {
                transform: translateY(-4px);
            }
        }
        
        /* Card content animation */
        .card-content {
            position: relative;
            z-index: 10;
        }
        
        /* Glassmorphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Mobile step dot styles */
        .mobile-step-dot {
            transition: all 0.4s ease;
            position: relative;
            z-index: 20;
        }
        
        .mobile-step-dot::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 12px;
            height: 12px;
            background-color: white;
            border-radius: 50%;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .mobile-step-dot.active::after {
            opacity: 0.4;
            animation: pulse 1.5s infinite;
        }
        
        /* Tambahkan styles untuk animasi timeline vertikal */
        .mobile-step-circle {
            transition: all 0.4s ease;
            position: relative;
            z-index: 5;
        }
        
        .mobile-step-circle.active {
            transform: scale(1.05);
            box-shadow: 0 0 0 5px rgba(15, 76, 129, 0.2);
        }
        
        .mobile-step-circle.active::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            background-color: rgba(15, 76, 129, 0.2);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }
        
        .mobile-step-line {
            transition: background-color 0.4s ease;
        }
        
        .step-item.active .mobile-step-line {
            background-image: linear-gradient(to bottom, #0F4D92, #FDB813);
        }
    </style>
</head>
<body class="bg-[var(--bg-color)] font-sans overflow-x-hidden">
    <!-- Header/Navigation - Optimized mobile experience -->
    <header class="w-full py-3 sm:py-4 bg-[var(--primary)] text-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto max-w-[1280px] px-4 sm:px-8 md:px-16 lg:px-24">
            <div class="flex justify-between items-center">
                <!-- Logo & Brand - Simplified for mobile -->
                <a href="#" class="flex items-center space-x-2">
                    <img src="{{ asset('images/logo-vokasi-ub.png') }}" alt="Logo Vokasi UB" class="h-8 sm:h-10 w-auto">
                    <span class="font-bold text-lg sm:text-xl">Lab IoT Vokasi UB</span>
                </a>
                
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="#tentang" class="hover:text-gray-200 transition">Tentang</a>
                    <a href="#fitur" class="hover:text-gray-200 transition">Fitur</a>
                    <a href="#panduan" class="hover:text-gray-200 transition">Panduan</a>
                    <a href="#kontak" class="hover:text-gray-200 transition">Kontak</a>
                    <a href="{{ url('/login') }}" class="bg-white text-[var(--primary)] px-4 py-2 rounded-full shadow hover:bg-gray-200 transition">
                        Masuk
                    </a>
                </nav>
                
                <!-- Mobile Menu Button - Improved touch target -->
                <button id="menuButton" class="md:hidden text-white focus:outline-none p-2 -mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu - Improved accessibility -->
        <div id="mobileMenu" class="mobile-menu fixed inset-0 bg-[var(--primary)] z-50 md:hidden">
            <div class="flex justify-end p-4">
                <button id="closeMenuButton" class="text-white focus:outline-none p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex flex-col items-center justify-center h-full space-y-8 text-white text-xl">
                <a href="#tentang" class="hover:text-gray-200 transition py-2 px-4">Tentang</a>
                <a href="#fitur" class="hover:text-gray-200 transition py-2 px-4">Fitur</a>
                <a href="#panduan" class="hover:text-gray-200 transition py-2 px-4">Panduan</a>
                <a href="#kontak" class="hover:text-gray-200 transition py-2 px-4">Kontak</a>
                <a href="{{ url('/login') }}" class="bg-white text-[var(--primary)] px-6 py-3 rounded-full shadow hover:bg-gray-200 transition mt-4">
                    Masuk
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section - Improved mobile layout -->
    <section class="relative min-h-[90vh] sm:min-h-screen bg-gradient-to-br from-[#0F4D92]/95 to-[#0A3B73]/90 flex items-center pt-16 sm:pt-20 pb-12 sm:pb-16 overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute w-64 sm:w-96 h-64 sm:h-96 bg-blue-500/10 rounded-full blur-3xl top-1/4 -left-32 sm:-left-48 animate-pulse"></div>
            <div class="absolute w-64 sm:w-96 h-64 sm:h-96 bg-[#FDB813]/10 rounded-full blur-3xl bottom-1/4 -right-32 sm:-right-48 animate-pulse"></div>
        </div>
        
        <div class="container mx-auto max-w-[1280px] px-4 sm:px-8 md:px-16 lg:px-24 relative z-10">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8 sm:gap-12">
                <!-- Left Content (Text) - Improved mobile typography -->
                <div class="lg:w-1/2" data-aos="fade-right">
                    <div class="text-xs sm:text-sm font-medium inline-flex items-center px-2 sm:px-3 py-1 bg-white/10 backdrop-blur-sm rounded-full text-white mb-4 sm:mb-6">
                        <span class="w-2 h-2 bg-[#FDB813] rounded-full mr-2 animate-pulse"></span>
                        Sistem Peminjaman Terintegrasi
                    </div>
                    
                    <h1 class="text-3xl sm:text-4xl md:text-5xl xl:text-6xl font-bold text-white leading-tight">
                        Sistem Peminjaman <span class="text-[#FDB813]">Lab IoT</span><span class="sm:hidden"><br></span> Vokasi UB
                    </h1>
                    
                    <p class="text-gray-200 text-base sm:text-lg md:text-xl mt-4 sm:mt-6 mb-6 sm:mb-8 leading-relaxed">
                        Kelola peminjaman barang laboratorium dengan mudah, cepat, dan terstruktur secara terintegrasi.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                        <a href="{{ url('/login') }}" 
                            class="bg-[#FDB813] text-[#0F4D92] px-6 sm:px-8 py-3 sm:py-4 rounded-full hover:scale-105 shadow-lg inline-block transition-all duration-300 font-semibold text-center">
                            <span class="flex items-center justify-center">
                                Mulai Pinjam Sekarang
                                <i data-lucide="arrow-right" class="w-4 h-4 sm:w-5 sm:h-5 ml-2 transition-transform group-hover:translate-x-1"></i>
                            </span>
                        </a>
                        
                        <a href="#panduan" class="px-6 sm:px-8 py-3 sm:py-4 border-2 border-white/30 text-white rounded-full hover:bg-white/10 transition-all duration-300 text-center backdrop-blur-sm">
                            Pelajari Panduan
                        </a>
                    </div>
                    
                    <!-- Mobile-visible illustration for smallest screens -->
                    <div class="mt-10 lg:hidden flex justify-center">
                        <img src="{{ asset('images/undraw_circuit_92r1.svg') }}" alt="Ilustrasi Sistem Peminjaman" class="w-4/5 max-w-xs mx-auto responsive-img" loading="lazy">
                    </div>
                    
                    <!-- Trust Badges - Improved spacing -->
                    <div class="mt-8 sm:mt-12 flex flex-wrap items-center gap-4 sm:gap-6 text-xs sm:text-sm text-white/80">
                        <div class="flex items-center">
                            <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5 mr-1 text-[#FDB813]"></i>
                            <span>Mudah</span>
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5 mr-1 text-[#FDB813]"></i>
                            <span>Cepat</span>
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5 mr-1 text-[#FDB813]"></i>
                            <span>Terintegrasi</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content (Illustration) - Hidden on very small screens -->
                <div class="lg:w-1/2 hidden lg:block relative" data-aos="fade-left" data-aos-delay="200">
                    <div class="relative">
                        <!-- Decorative Elements -->
                        <div class="absolute -top-10 -right-10 w-20 h-20 bg-[#FDB813]/20 rounded-full blur-md"></div>
                        <div class="absolute -bottom-6 -left-6 w-16 h-16 bg-blue-500/20 rounded-full blur-md"></div>
                        
                        <!-- Main Image with Floating Effect -->
                        <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl shadow-2xl relative overflow-hidden border border-white/20">
                            <img src="{{ asset('images/undraw_circuit_92r1.svg') }}" alt="Ilustrasi Sistem Peminjaman" class="w-full max-w-lg mx-auto relative z-10 transform hover:scale-105 transition-transform duration-700 responsive-img" loading="lazy">
                            
                            <!-- Decorative Dots -->
                            <div class="absolute top-5 right-5 grid grid-cols-2 gap-1">
                                <span class="block w-1 h-1 bg-[#FDB813] rounded-full"></span>
                                <span class="block w-1 h-1 bg-[#FDB813] rounded-full"></span>
                                <span class="block w-1 h-1 bg-[#FDB813] rounded-full"></span>
                                <span class="block w-1 h-1 bg-[#FDB813] rounded-full"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="tentang" class="py-24 bg-white relative">
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-full h-1 bg-gradient-to-r from-[#0F4D92]/0 via-[#0F4D92] to-[#0F4D92]/0"></div>
        <div class="absolute top-0 left-0 w-32 h-32 bg-[#FDB813]/5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-2xl"></div>
        <div class="absolute bottom-0 right-0 w-40 h-40 bg-[#0F4D92]/5 rounded-full translate-x-1/4 translate-y-1/4 blur-2xl"></div>
        
        <div class="container mx-auto max-w-[1280px] px-4 sm:px-8 md:px-16 lg:px-32 relative z-10">
            <div class="flex flex-col items-center mb-16" data-aos="fade-up">
                <div class="inline-flex items-center justify-center px-4 py-1.5 bg-[#0F4D92]/10 rounded-full text-[#0F4D92] text-sm font-medium mb-4">
                    <i data-lucide="info" class="w-4 h-4 mr-2"></i>
                    <span>Tentang Platform</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-4">
                    Tentang Sistem Peminjaman
            </h2>
                <div class="h-1 w-20 bg-[#FDB813] rounded-full mb-6"></div>
                <p class="text-gray-500 text-center max-w-2xl">
                    Platform digital untuk menyederhanakan proses peminjaman barang di laboratorium
                </p>
            </div>
            
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <!-- Text Content -->
                <div class="lg:w-1/2" data-aos="fade-right">
                    <p class="text-gray-700 text-lg leading-relaxed mb-8">
                        Sistem ini memudahkan proses peminjaman barang di Lab IoT Vokasi UB secara digital, cepat, dan terintegrasi. Mahasiswa dan admin dapat berinteraksi melalui platform ini untuk memastikan transparansi, efisiensi, dan akurasi dalam setiap transaksi peminjaman.
                    </p>
                    
                    <h3 class="text-2xl font-semibold text-gray-800 mb-6">Keunggulan Sistem:</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="feature-card bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-[#0F4D92]/20 transition-all">
                            <div class="w-12 h-12 bg-[#0F4D92]/10 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="zap" class="w-6 h-6 text-[#0F4D92]"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-2">Proses Cepat</h4>
                            <p class="text-gray-600 text-sm">Peminjaman barang dapat diproses dengan cepat dan efisien</p>
                        </div>
                        
                        <div class="feature-card bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-[#0F4D92]/20 transition-all">
                            <div class="w-12 h-12 bg-[#0F4D92]/10 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="eye" class="w-6 h-6 text-[#0F4D92]"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-2">Transparansi</h4>
                            <p class="text-gray-600 text-sm">Status peminjaman selalu transparan dan dapat dilacak</p>
                        </div>
                        
                        <div class="feature-card bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-[#0F4D92]/20 transition-all">
                            <div class="w-12 h-12 bg-[#0F4D92]/10 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="history" class="w-6 h-6 text-[#0F4D92]"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-2">Riwayat Lengkap</h4>
                            <p class="text-gray-600 text-sm">Akses ke riwayat peminjaman yang lengkap dan terperinci</p>
                        </div>
                        
                        <div class="feature-card bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-[#0F4D92]/20 transition-all">
                            <div class="w-12 h-12 bg-[#0F4D92]/10 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="users" class="w-6 h-6 text-[#0F4D92]"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-2">Terintegrasi</h4>
                            <p class="text-gray-600 text-sm">Admin dan user terintegrasi dalam satu platform terpadu</p>
                        </div>
                    </div>
                </div>
                
                <!-- Illustration -->
                <div class="lg:w-1/2 relative mt-10 lg:mt-0" data-aos="fade-left" data-aos-delay="100">
                    <div class="relative">
                        <div class="absolute -z-10 inset-0 blur-2xl bg-gradient-to-br from-[#0F4D92]/20 to-[#FDB813]/20 rounded-full transform -translate-x-10 -translate-y-10"></div>
                        <div class="absolute inset-0 -z-10 bg-white/40 backdrop-blur-3xl rounded-3xl rotate-3"></div>
                        <div class="absolute inset-0 -z-10 bg-white/40 backdrop-blur-3xl rounded-3xl -rotate-3"></div>
                        <div class="bg-white/70 backdrop-blur-sm border border-gray-100 p-6 rounded-2xl shadow-xl relative z-10">
                    <img src="{{ asset('images/undraw_circuit-board_.svg') }}" alt="Ilustrasi Sistem Informasi" class="w-full max-w-lg mx-auto" loading="lazy">
                        </div>
                        
                        <!-- Floating Decorative Elements -->
                        <div class="absolute -top-6 -right-6 w-12 h-12 bg-[#FDB813] rounded-full opacity-20 animate-pulse"></div>
                        <div class="absolute -bottom-6 -left-6 w-12 h-12 bg-[#0F4D92] rounded-full opacity-20 animate-pulse"></div>
                    </div>
                    
                    <!-- Stats (moved inside container) -->
                    <div class="mt-10 bg-white rounded-xl shadow-lg p-6 flex flex-wrap justify-center gap-8 border border-gray-100 relative z-20">
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Barang Tersedia</p>
                            <h4 class="text-2xl font-bold text-[#0F4D92]">100+</h4>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Peminjam</p>
                            <h4 class="text-2xl font-bold text-[#0F4D92]">500+</h4>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500">Transaksi</p>
                            <h4 class="text-2xl font-bold text-[#0F4D92]">1000+</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="fitur" class="py-24 bg-gradient-to-b from-gray-50 to-gray-100 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute -top-40 -left-40 w-80 h-80 bg-[#FDB813]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-80 h-80 bg-[#0F4D92]/5 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full h-full max-w-3xl max-h-3xl bg-white/30 rounded-full blur-3xl -z-10"></div>
        
        <div class="container mx-auto max-w-[1280px] px-4 sm:px-8 md:px-16 lg:px-32 relative z-10">
            <div class="flex flex-col items-center mb-16" data-aos="fade-up">
                <div class="inline-flex items-center justify-center px-4 py-1.5 bg-[#FDB813]/10 rounded-full text-[#FDB813] text-sm font-medium mb-4">
                    <i data-lucide="star" class="w-4 h-4 mr-2"></i>
                    <span>Fitur Unggulan</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-4">
                    Fitur Utama Sistem
            </h2>
                <div class="h-1 w-20 bg-[#0F4D92] rounded-full mb-6"></div>
                <p class="text-gray-500 text-center max-w-2xl">
                    Sistem peminjaman dengan fitur lengkap untuk memudahkan proses dari awal hingga akhir
                </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature Card 1 -->
                <div class="feature-card group" data-aos="fade-up">
                    <div class="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[#0F4D92]/20 h-full relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#0F4D92] to-[#FDB813] transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                        <div class="w-16 h-16 bg-[#0F4D92]/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#0F4D92]/20 transition-colors">
                            <i data-lucide="clipboard-list" class="w-8 h-8 text-[#0F4D92]"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Formulir Peminjaman Online</h3>
                        <p class="text-gray-600">Isi formulir digital untuk meminjam barang dengan cepat dan mudah tanpa perlu mengisi dokumen fisik. Proses pengajuan yang efisien dan paperless.</p>
                    </div>
                </div>
                
                <!-- Feature Card 2 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[#0F4D92]/20 h-full relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#0F4D92] to-[#FDB813] transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                        <div class="w-16 h-16 bg-[#0F4D92]/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#0F4D92]/20 transition-colors">
                            <i data-lucide="bell" class="w-8 h-8 text-[#0F4D92]"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Notifikasi Otomatis</h3>
                        <p class="text-gray-600">Dapatkan pengingat otomatis berupa Daily Reminder dan H-7 sebelum tenggat pengembalian. Hindari keterlambatan pengembalian barang.</p>
                    </div>
                </div>
                
                <!-- Feature Card 3 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[#0F4D92]/20 h-full relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#0F4D92] to-[#FDB813] transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                        <div class="w-16 h-16 bg-[#0F4D92]/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#0F4D92]/20 transition-colors">
                            <i data-lucide="map-pin" class="w-8 h-8 text-[#0F4D92]"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Pelacakan Lokasi Barang</h3>
                        <p class="text-gray-600">Unggah foto lokasi barang saat pengambilan untuk mempermudah pelacakan keberadaan barang. Pantau lokasi terakhir barang.</p>
                    </div>
                </div>
                
                <!-- Feature Card 4 -->
                <div class="feature-card group" data-aos="fade-up">
                    <div class="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[#0F4D92]/20 h-full relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#0F4D92] to-[#FDB813] transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                        <div class="w-16 h-16 bg-[#0F4D92]/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#0F4D92]/20 transition-colors">
                            <i data-lucide="bar-chart" class="w-8 h-8 text-[#0F4D92]"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Export Laporan</h3>
                        <p class="text-gray-600">Cetak dan unduh laporan peminjaman dalam format PDF dan Excel untuk keperluan dokumentasi dan analisis data peminjaman.</p>
                    </div>
                </div>
                
                <!-- Feature Card 5 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[#0F4D92]/20 h-full relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#0F4D92] to-[#FDB813] transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                        <div class="w-16 h-16 bg-[#0F4D92]/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#0F4D92]/20 transition-colors">
                            <i data-lucide="history" class="w-8 h-8 text-[#0F4D92]"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Histori Peminjaman Lengkap</h3>
                        <p class="text-gray-600">Akses riwayat peminjaman lengkap beserta detail status untuk memudahkan pengawasan dan evaluasi kegiatan peminjaman.</p>
                    </div>
                </div>
                
                <!-- Feature Card 6 -->
                <div class="feature-card group" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-[#0F4D92]/20 h-full relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#0F4D92] to-[#FDB813] transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                        <div class="w-16 h-16 bg-[#0F4D92]/10 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-[#0F4D92]/20 transition-colors">
                            <i data-lucide="layout-dashboard" class="w-8 h-8 text-[#0F4D92]"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Dashboard Terintegrasi</h3>
                        <p class="text-gray-600">Pantau semua aktivitas peminjaman secara real-time melalui dashboard yang informatif dan interaktif dengan visualisasi data.</p>
                    </div>
                </div>
            </div>
            
            <!-- CTA Banner -->
            <div class="mt-20 bg-gradient-to-r from-[#0F4D92] to-[#0A3B73] rounded-2xl p-8 md:p-12 shadow-xl" data-aos="fade-up">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div>
                        <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Siap Mencoba Sistem Kami?</h3>
                        <p class="text-gray-200 mb-0">Mulai proses peminjaman barang dengan mudah dan cepat sekarang juga.</p>
                    </div>
                    <a href="{{ url('/login') }}" class="whitespace-nowrap px-8 py-4 bg-white text-[#0F4D92] rounded-xl font-bold text-center hover:bg-[#FDB813] hover:text-white transition-all shadow-lg">
                        Akses Sistem
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Guide Section -->
    <section id="panduan" class="py-24 bg-white relative">
        <!-- Decorative Background with enhanced patterns -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTQ0MCIgaGVpZ2h0PSI3NjgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cmVjdCBmaWxsPSIjZmZmIiB3aWR0aD0iMTQ0MCIgaGVpZ2h0PSI3NjgiLz48Y2lyY2xlIHN0cm9rZT0iI0YxRjVGOSIgc3Ryb2tlLXdpZHRoPSIyIiBjeD0iNzIwIiBjeT0iMzg0IiByPSIyMzUiLz48Y2lyY2xlIHN0cm9rZT0iI0YxRjVGOSIgc3Ryb2tlLXdpZHRoPSIyIiBjeD0iNzIwIiBjeT0iMzg0IiByPSIyNzAiLz48Y2lyY2xlIHN0cm9rZT0iI0YxRjVGOSIgc3Ryb2tlLXdpZHRoPSIyIiBjeD0iNzIwIiBjeT0iMzg0IiByPSIzMDUiLz48Y2lyY2xlIHN0cm9rZT0iI0YxRjVGOSIgc3Ryb2tlLXdpZHRoPSIyIiBjeD0iNzIwIiBjeT0iMzg0IiByPSIzNDAiLz48Y2lyY2xlIHN0cm9rZT0iI0YxRjVGOSIgc3Ryb2tlLXdpZHRoPSIyIiBjeD0iNzIwIiBjeT0iMzg0IiByPSIzNzUiLz48L2c+PC9zdmc+')] opacity-40"></div>
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-white via-transparent to-white pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-white to-transparent"></div>
        
        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-24 h-24 rounded-full bg-[#0F4D92]/5 blur-xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-32 h-32 rounded-full bg-[#FDB813]/5 blur-xl animate-pulse-short"></div>
        
        <div class="container mx-auto max-w-[1280px] px-4 sm:px-8 md:px-16 lg:px-24 relative z-10">
            <div class="flex flex-col items-center mb-16" data-aos="fade-up">
                <div class="inline-flex items-center justify-center px-4 py-1.5 bg-[#0F4D92]/10 rounded-full text-[#0F4D92] text-sm font-medium mb-4 shadow-sm">
                    <i data-lucide="book-open" class="w-4 h-4 mr-2"></i>
                    <span>Panduan Pengguna</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-800 mb-4">
                    Cara Menggunakan Sistem
            </h2>
                <div class="h-1 w-20 bg-[#FDB813] rounded-full mb-6"></div>
                <p class="text-gray-500 text-center max-w-2xl">
                    Lima langkah mudah untuk menggunakan sistem peminjaman barang laboratorium
                </p>
            </div>
            
            <div class="max-w-5xl mx-auto">
                <!-- Interactive Progress Bar (Desktop) -->
                <div class="hidden md:block relative mb-16">
                    <div class="progress-track rounded-full mx-auto">
                        <div id="progressBar" class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="flex justify-between absolute top-0 w-full transform -translate-y-1/2">
                        <div class="step-dot active" data-step="1">
                            <div class="bg-[#0F4D92] text-white w-10 h-10 rounded-full flex items-center justify-center font-bold shadow-lg border-2 border-white">1</div>
                        </div>
                        <div class="step-dot" data-step="2">
                            <div class="bg-white text-[#0F4D92] w-10 h-10 rounded-full flex items-center justify-center font-bold shadow-md border-2 border-[#0F4D92]">2</div>
                        </div>
                        <div class="step-dot" data-step="3">
                            <div class="bg-white text-[#0F4D92] w-10 h-10 rounded-full flex items-center justify-center font-bold shadow-md border-2 border-[#0F4D92]">3</div>
                        </div>
                        <div class="step-dot" data-step="4">
                            <div class="bg-white text-[#0F4D92] w-10 h-10 rounded-full flex items-center justify-center font-bold shadow-md border-2 border-[#0F4D92]">4</div>
                        </div>
                        <div class="step-dot" data-step="5">
                            <div class="bg-white text-[#0F4D92] w-10 h-10 rounded-full flex items-center justify-center font-bold shadow-md border-2 border-[#0F4D92]">5</div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Timeline (enhanced horizontal cards) -->
                <div class="hidden md:grid grid-cols-5 gap-6" id="stepCards">
                <!-- Step 1 -->
                <div class="step-card group" data-aos="fade-up" data-step="1">
                    <div class="relative bg-white rounded-xl shadow-lg p-6 h-full flex-grow border border-gray-100 hover:border-[#0F4D92]/30 transition-all overflow-hidden">
                        <div class="step-indicator"></div>
                        <div class="card-content">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg text-center">Login</h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4">
                                Masuk ke sistem menggunakan akun mahasiswa yang telah terdaftar untuk mengakses fitur peminjaman barang.
                            </p>
                            <div class="mt-auto flex justify-center relative">
                                <div class="absolute -z-10 inset-0 bg-[#0F4D92]/5 rounded-full blur-lg transform scale-75 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <i data-lucide="log-in" class="w-10 h-10 text-[#0F4D92] transform transition-all group-hover:scale-110 flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step-card group" data-aos="fade-up" data-aos-delay="100" data-step="2">
                    <div class="relative bg-white rounded-xl shadow-lg p-6 h-full flex-grow border border-gray-100 hover:border-[#0F4D92]/30 transition-all overflow-hidden">
                        <div class="step-indicator"></div>
                        <div class="card-content">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg text-center">Isi Formulir</h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4">
                                Lengkapi formulir peminjaman dengan informasi barang yang dibutuhkan dan tujuan penggunaan barang tersebut.
                            </p>
                            <div class="mt-auto flex justify-center relative">
                                <div class="absolute -z-10 inset-0 bg-[#0F4D92]/5 rounded-full blur-lg transform scale-75 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <i data-lucide="clipboard-edit" class="w-10 h-10 text-[#0F4D92] transform transition-all group-hover:scale-110 flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="step-card group" data-aos="fade-up" data-aos-delay="200" data-step="3">
                    <div class="relative bg-white rounded-xl shadow-lg p-6 h-full flex-grow border border-gray-100 hover:border-[#0F4D92]/30 transition-all overflow-hidden">
                        <div class="step-indicator"></div>
                        <div class="card-content">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg text-center">Verifikasi</h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4">
                                Tunggu persetujuan dari admin lab melalui sistem dan notifikasi. Anda akan mendapat pemberitahuan saat disetujui.
                            </p>
                            <div class="mt-auto flex justify-center relative">
                                <div class="absolute -z-10 inset-0 bg-[#0F4D92]/5 rounded-full blur-lg transform scale-75 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <i data-lucide="check-circle-2" class="w-10 h-10 text-[#0F4D92] transform transition-all group-hover:scale-110 flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="step-card group" data-aos="fade-up" data-aos-delay="300" data-step="4">
                    <div class="relative bg-white rounded-xl shadow-lg p-6 h-full flex-grow border border-gray-100 hover:border-[#0F4D92]/30 transition-all overflow-hidden">
                        <div class="step-indicator"></div>
                        <div class="card-content">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg text-center">Ambil Barang</h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4">
                                Ambil barang di lab dan lakukan pelacakan dengan foto lokasi dan barang yang diambil. Pastikan kondisi barang sesuai.
                            </p>
                            <div class="mt-auto flex justify-center relative">
                                <div class="absolute -z-10 inset-0 bg-[#0F4D92]/5 rounded-full blur-lg transform scale-75 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <i data-lucide="package-check" class="w-10 h-10 text-[#0F4D92] transform transition-all group-hover:scale-110 flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="step-card group" data-aos="fade-up" data-aos-delay="400" data-step="5">
                    <div class="relative bg-white rounded-xl shadow-lg p-6 h-full flex-grow border border-gray-100 hover:border-[#0F4D92]/30 transition-all overflow-hidden">
                        <div class="step-indicator"></div>
                        <div class="card-content">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg text-center">Pengembalian</h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4">
                                Kembalikan barang tepat waktu sesuai dengan tanggal pengembalian dan dalam kondisi yang sama saat dipinjam.
                            </p>
                            <div class="mt-auto flex justify-center relative">
                                <div class="absolute -z-10 inset-0 bg-[#0F4D92]/5 rounded-full blur-lg transform scale-75 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <i data-lucide="rotate-ccw" class="w-10 h-10 text-[#0F4D92] transform transition-all group-hover:scale-110 flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                
                <!-- Mobile Timeline (improved vertical) -->
                <div class="md:hidden space-y-8">
                    <!-- Step 1 -->
                    <div class="flex gap-4 step-item" data-aos="fade-up" data-step="1">
                        <div class="flex flex-col items-center">
                            <div class="mobile-step-circle active bg-[#0F4D92] text-white w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-lg">1</div>
                            <div class="w-1 h-full bg-gradient-to-b from-[#0F4D92] to-[#0F4D92]/20 flex-grow mt-2 mobile-step-line"></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 flex-1 hover:border-[#0F4D92]/20 transition-all">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg">Login</h3>
                            <p class="text-gray-600">Masuk ke sistem menggunakan akun mahasiswa yang telah terdaftar untuk mengakses fitur peminjaman.</p>
                            <div class="mt-4 flex justify-end">
                                <i data-lucide="log-in" class="w-8 h-8 text-[#0F4D92] flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                
                <!-- Step 2 -->
                    <div class="flex gap-4 step-item" data-aos="fade-up" data-aos-delay="100" data-step="2">
                        <div class="flex flex-col items-center">
                            <div class="mobile-step-circle bg-white text-[#0F4D92] ring-2 ring-[#0F4D92] w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md">2</div>
                            <div class="w-1 h-full bg-gradient-to-b from-[#0F4D92]/20 to-[#0F4D92]/20 flex-grow mt-2 mobile-step-line"></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 flex-1 hover:border-[#0F4D92]/20 transition-all">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg">Isi Formulir</h3>
                            <p class="text-gray-600">Lengkapi formulir peminjaman dengan informasi barang yang dibutuhkan dan tujuan penggunaan.</p>
                            <div class="mt-4 flex justify-end">
                                <i data-lucide="clipboard-edit" class="w-8 h-8 text-[#0F4D92] flex-shrink-0"></i>
                            </div>
                        </div>
                </div>
                
                <!-- Step 3 -->
                    <div class="flex gap-4 step-item" data-aos="fade-up" data-aos-delay="200" data-step="3">
                        <div class="flex flex-col items-center">
                            <div class="mobile-step-circle bg-white text-[#0F4D92] ring-2 ring-[#0F4D92] w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md">3</div>
                            <div class="w-1 h-full bg-gradient-to-b from-[#0F4D92]/20 to-[#0F4D92]/20 flex-grow mt-2 mobile-step-line"></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 flex-1 hover:border-[#0F4D92]/20 transition-all">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg">Verifikasi</h3>
                            <p class="text-gray-600">Tunggu persetujuan dari admin lab melalui sistem dan notifikasi. Anda akan mendapat pemberitahuan saat disetujui.</p>
                            <div class="mt-4 flex justify-end">
                                <i data-lucide="check-circle-2" class="w-8 h-8 text-[#0F4D92] flex-shrink-0"></i>
                            </div>
                        </div>
                </div>
                
                    <!-- Step 4 -->
                    <div class="flex gap-4 step-item" data-aos="fade-up" data-aos-delay="300" data-step="4">
                        <div class="flex flex-col items-center">
                            <div class="mobile-step-circle bg-white text-[#0F4D92] ring-2 ring-[#0F4D92] w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md">4</div>
                            <div class="w-1 h-full bg-gradient-to-b from-[#0F4D92]/20 to-[#0F4D92]/20 flex-grow mt-2 mobile-step-line"></div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 flex-1 hover:border-[#0F4D92]/20 transition-all">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg">Ambil Barang</h3>
                            <p class="text-gray-600">Ambil barang di lab dan lakukan pelacakan dengan foto lokasi dan barang yang diambil. Pastikan kondisi barang sesuai.</p>
                            <div class="mt-4 flex justify-end">
                                <i data-lucide="package-check" class="w-8 h-8 text-[#0F4D92] flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 5 -->
                    <div class="flex gap-4 step-item" data-aos="fade-up" data-aos-delay="400" data-step="5">
                        <div class="flex flex-col items-center">
                            <div class="mobile-step-circle bg-white text-[#0F4D92] ring-2 ring-[#0F4D92] w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md">5</div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 flex-1 hover:border-[#0F4D92]/20 transition-all">
                            <h3 class="font-bold text-gray-800 mb-3 text-lg">Pengembalian</h3>
                            <p class="text-gray-600">Kembalikan barang tepat waktu sesuai dengan tanggal pengembalian dan dalam kondisi yang sama saat dipinjam.</p>
                            <div class="mt-4 flex justify-end">
                                <i data-lucide="rotate-ccw" class="w-8 h-8 text-[#0F4D92] flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Before Footer -->
    <section class="py-20 relative overflow-hidden bg-gradient-to-r from-[#0F4D92] to-[#0A3B73]">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute top-0 right-0 w-72 h-72 bg-[#FDB813]/10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="container mx-auto max-w-[1280px] px-4 sm:px-8 md:px-16 lg:px-32 relative z-10">
            <div class="flex flex-col items-center text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-6" data-aos="fade-up">
                    Siap Menggunakan Sistem Peminjaman?
                </h2>
                <p class="text-gray-200 mb-10 max-w-2xl" data-aos="fade-up" data-aos-delay="100">
                    Mulai peminjaman barang laboratorium dengan cepat dan efisien. Lengkapi kebutuhan praktikum dan penelitian Anda dengan mudah.
                </p>
                <div class="flex flex-col sm:flex-row gap-4" data-aos="fade-up" data-aos-delay="200">
                    <a href="{{ url('/login') }}" class="px-8 py-4 bg-white text-[#0F4D92] rounded-xl font-bold hover:bg-[#FDB813] hover:text-white transition-all shadow-lg">
                        Masuk Sekarang
                    </a>
                    <a href="#panduan" class="px-8 py-4 border-2 border-white/30 text-white rounded-xl hover:bg-white/10 transition-all">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer and Contact Section -->
    <footer id="kontak" class="bg-[var(--primary-dark)] text-white pt-20 pb-0 relative">
        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-[#FDB813]/0 via-[#FDB813] to-[#FDB813]/0"></div>
        
        <div class="container mx-auto max-w-[1280px] px-4 sm:px-8 md:px-16 lg:px-32">
            <!-- Main Footer Content -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 mb-16">
                <!-- About Column -->
                <div class="md:col-span-5" data-aos="fade-up">
                    <div class="flex items-center mb-6">
                        <img src="{{ asset('images/logo-vokasi-ub.png') }}" alt="Logo Vokasi UB" class="h-12 w-auto mr-3">
                        <h3 class="text-xl font-bold">Lab IoT Vokasi UB</h3>
                    </div>
                    <p class="text-gray-200 mb-6 leading-relaxed">
                        Lab IoT Vokasi UB merupakan pusat pengembangan dan riset Internet of Things untuk mendukung pembelajaran di Universitas Brawijaya. Kami berkomitmen pada inovasi teknologi dan pengembangan kompetensi mahasiswa.
                    </p>
                    <div class="flex space-x-4">
                        <a href="https://www.facebook.com/Pendidikan-Vokasi-UB-987357274986986/" target="_blank" class="bg-white/10 hover:bg-[#FDB813]/20 p-2 rounded-full transition-all">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </a>
                        <a href="https://twitter.com/VokasiUB" target="_blank" class="bg-white/10 hover:bg-[#FDB813]/20 p-2 rounded-full transition-all">
                            <i data-lucide="twitter" class="w-5 h-5"></i>
                        </a>
                        <a href="https://www.instagram.com/vokasiub" target="_blank" class="bg-white/10 hover:bg-[#FDB813]/20 p-2 rounded-full transition-all">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </a>
                        <a href="https://www.youtube.com/channel/UCbshdHnXN0kkhlXWxBH3VGA" target="_blank" class="bg-white/10 hover:bg-[#FDB813]/20 p-2 rounded-full transition-all">
                            <i data-lucide="youtube" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="md:col-span-3" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-lg font-semibold mb-6 flex items-center">
                        <i data-lucide="link" class="w-5 h-5 mr-2 text-[#FDB813]"></i> Link Cepat
                    </h3>
                    <ul class="space-y-3">
                        <li><a href="#tentang" class="hover:text-[#FDB813] transition-colors flex items-center">
                            <i data-lucide="chevron-right" class="w-4 h-4 mr-2"></i> Tentang Kami
                        </a></li>
                        <li><a href="#fitur" class="hover:text-[#FDB813] transition-colors flex items-center">
                            <i data-lucide="chevron-right" class="w-4 h-4 mr-2"></i> Fitur Sistem
                        </a></li>
                        <li><a href="#panduan" class="hover:text-[#FDB813] transition-colors flex items-center">
                            <i data-lucide="chevron-right" class="w-4 h-4 mr-2"></i> Panduan Penggunaan
                        </a></li>
                        <li><a href="{{ url('/login') }}" class="hover:text-[#FDB813] transition-colors flex items-center">
                            <i data-lucide="chevron-right" class="w-4 h-4 mr-2"></i> Masuk Sistem
                        </a></li>
                    </ul>
                </div>
                
                <!-- Contact Info Column -->
                <div class="md:col-span-4" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-lg font-semibold mb-6 flex items-center">
                        <i data-lucide="message-circle" class="w-5 h-5 mr-2 text-[#FDB813]"></i> Kontak Kami
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3 bg-white/5 p-3 rounded-lg hover:bg-white/10 transition-colors">
                            <i data-lucide="building" class="w-5 h-5 mt-1 flex-shrink-0 text-[#FDB813]"></i>
                            <p class="text-sm">
                                Jl. Puncak Dieng, Kunci, Kalisongo,
                                Kec. Dau, Kabupaten Malang,
                                Jawa Timur, 65151, Indonesia
                            </p>
                        </div>
                        <a href="mailto:lab.iot@vokasi.ub.ac.id" class="flex items-center space-x-3 bg-white/5 p-3 rounded-lg hover:bg-white/10 transition-colors">
                            <i data-lucide="mail-open" class="w-5 h-5 flex-shrink-0 text-[#FDB813]"></i>
                            <span class="text-sm">lab.iot@vokasi.ub.ac.id</span>
                        </a>
                        <a href="https://wa.me/089652944096" class="flex items-center space-x-3 bg-white/5 p-3 rounded-lg hover:bg-white/10 transition-colors">
                            <i data-lucide="phone-call" class="w-5 h-5 flex-shrink-0 text-[#FDB813]"></i>
                            <span class="text-sm">089652944096</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Divider -->
            <div class="border-t border-gray-700 mb-6"></div>
            
            <!-- Copyright Bar -->
            <div class="flex flex-col items-center text-center py-6">
                <p class="mb-4">© 2025 Lab IoT Vokasi UB. Seluruh hak cipta dilindungi.</p>
                <p class="text-xs text-gray-500">Sistem dikembangkan oleh Tim IT Lab IoT Vokasi UB</p>
            </div>
            
        </div>
    </footer>
    
    <!-- Modernized Back to Top Button -->
    <button id="backToTop" class="back-to-top bg-[#FDB813] text-white p-3 rounded-full shadow-lg focus:outline-none hover:bg-[#0F4D92] transition-all duration-300 flex items-center justify-center">
        <i data-lucide="chevron-up" class="h-5 w-5 animate-bounce-slow"></i>
    </button>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Initialize AOS animations dengan pengaturan yang dioptimalkan untuk mobile
        AOS.init({
            duration: 600,
            once: true,
            offset: 30,
            delay: 50,
            easing: 'ease-out',
            anchorPlacement: 'top-bottom',
            disable: false // Mengaktifkan di semua perangkat
        });
        
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Mobile Menu Functionality
        const menuButton = document.getElementById('menuButton');
        const closeMenuButton = document.getElementById('closeMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuLinks = mobileMenu.querySelectorAll('a');
        
        menuButton.addEventListener('click', () => {
            mobileMenu.classList.add('open');
            document.body.style.overflow = 'hidden';
            closeMenuButton.focus();
        });
        
        closeMenuButton.addEventListener('click', () => {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = '';
            menuButton.focus();
        });
        
        // Close mobile menu when a link is clicked
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
            });
        });
        
        // Close menu on escape key press
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
                menuButton.focus();
            }
        });
        
        // Optimize scroll performance
        let ticking = false;
        
        // Enhanced Back to Top Button Functionality with optimized performance
        const backToTopButton = document.getElementById('backToTop');
        
        // Smooth scroll function for all anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    const headerOffset = 80;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Show/hide back to top button dan progress tracking dengan performa yang lebih baik
        const handleScroll = () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const scrollPosition = window.pageYOffset;
                    
                    // Back to top button visibility
                    if (scrollPosition > 300) {
                        backToTopButton.classList.add('visible');
                    } else {
                        backToTopButton.classList.remove('visible');
                    }
                    
                    // Interactive Progress Tracking for Steps
                    const panduanSection = document.getElementById('panduan');
                    if (panduanSection) {
                        const panduanTop = panduanSection.offsetTop;
                        const panduanHeight = panduanSection.offsetHeight;
                        const viewportBottom = scrollPosition + window.innerHeight;
                        const progressBar = document.getElementById('progressBar');
                        const stepCards = document.querySelectorAll('.step-card');
                        const stepDots = document.querySelectorAll('.step-dot');
                        const mobileStepItems = document.querySelectorAll('.step-item');
                        const mobileStepCircles = document.querySelectorAll('.mobile-step-circle');
                        
                        // Check if we're in the panduan section
                        if (viewportBottom > panduanTop && scrollPosition < panduanTop + panduanHeight) {
                            const progress = Math.min(100, Math.max(0, (viewportBottom - panduanTop) / (panduanHeight * 0.8) * 100));
                            
                            if (progressBar) {
                                progressBar.style.width = `${progress}%`;
                            }
                            
                            // Highlight steps based on scroll position
                            const step = Math.min(5, Math.max(1, Math.floor(progress / 20) + 1));
                            
                            // Update desktop dot steps
                            stepDots.forEach((dot) => {
                                const dotStep = parseInt(dot.dataset.step);
                                if (dotStep <= step) {
                                    dot.classList.add('active');
                                    dot.querySelector('div').classList.remove('bg-white', 'text-[#0F4D92]');
                                    dot.querySelector('div').classList.add('bg-[#0F4D92]', 'text-white');
                                } else {
                                    dot.classList.remove('active');
                                    dot.querySelector('div').classList.remove('bg-[#0F4D92]', 'text-white');
                                    dot.querySelector('div').classList.add('bg-white', 'text-[#0F4D92]');
                                }
                            });
                            
                            // Update mobile step items
                            mobileStepItems.forEach((item) => {
                                const itemStep = parseInt(item.dataset.step);
                                if (itemStep <= step) {
                                    item.classList.add('active');
                                } else {
                                    item.classList.remove('active');
                                }
                            });
                            
                            // Update mobile step circles
                            mobileStepCircles.forEach((circle, index) => {
                                if (index < step) {
                                    circle.classList.add('active');
                                    circle.classList.remove('bg-white', 'text-[#0F4D92]');
                                    circle.classList.add('bg-[#0F4D92]', 'text-white');
                                } else {
                                    circle.classList.remove('active');
                                    circle.classList.remove('bg-[#0F4D92]', 'text-white');
                                    circle.classList.add('bg-white', 'text-[#0F4D92]');
                                }
                            });
                            
                            stepCards.forEach((card, index) => {
                                const cardStep = parseInt(card.dataset.step);
                                if (cardStep <= step) {
                                    card.classList.add('active');
                                    const stepNumber = card.querySelector('.step-number');
                                    if (stepNumber) {
                                        stepNumber.classList.remove('bg-white', 'text-[#0F4D92]', 'ring-2', 'ring-[#0F4D92]/20');
                                        stepNumber.classList.add('bg-[#0F4D92]', 'text-white');
                                    }
                                } else {
                                    card.classList.remove('active');
                                    const stepNumber = card.querySelector('.step-number');
                                    if (stepNumber) {
                                        stepNumber.classList.remove('bg-[#0F4D92]', 'text-white');
                                        stepNumber.classList.add('bg-white', 'text-[#0F4D92]', 'ring-2', 'ring-[#0F4D92]/20');
                                    }
                                }
                            });
                        }
                    }
                    
                    // Optional: Add active state to navbar based on scroll position
                    const sections = document.querySelectorAll('section[id]');
                    sections.forEach(section => {
                        const sectionTop = section.offsetTop - 100;
                        const sectionHeight = section.offsetHeight;
                        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                            const id = section.getAttribute('id');
                            document.querySelectorAll('nav a').forEach(link => {
                                link.classList.remove('text-[#FDB813]');
                                if (link.getAttribute('href') === '#' + id) {
                                    link.classList.add('text-[#FDB813]');
                                }
                            });
                        }
                    });
                    
                    ticking = false;
                });
                
                ticking = true;
            }
        };
        
        window.addEventListener('scroll', handleScroll, { passive: true });
        
        // Smooth scroll to top
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Initialize steps dan optimasi perangkat mobile
        document.addEventListener('DOMContentLoaded', function() {
            // Trigger the initial scroll check
            handleScroll();
            
            // Add hover/touch effect for step cards
            const stepCards = document.querySelectorAll('.step-card');
            
            stepCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    const step = parseInt(card.dataset.step);
                    document.querySelectorAll('.step-dot').forEach((dot) => {
                        const dotStep = parseInt(dot.dataset.step);
                        if (dotStep === step) {
                            dot.classList.add('hover');
                        }
                    });
                });
                
                card.addEventListener('mouseleave', () => {
                    document.querySelectorAll('.step-dot').forEach(dot => {
                        dot.classList.remove('hover');
                    });
                });
                
                // Add touch support for mobile devices
                card.addEventListener('touchstart', () => {
                    const step = parseInt(card.dataset.step);
                    document.querySelectorAll('.step-dot').forEach((dot) => {
                        const dotStep = parseInt(dot.dataset.step);
                        if (dotStep === step) {
                            dot.classList.add('hover');
                        }
                    });
                }, { passive: true });
                
                card.addEventListener('touchend', () => {
                    document.querySelectorAll('.step-dot').forEach(dot => {
                        dot.classList.remove('hover');
                    });
                }, { passive: true });
            });
            
            // Lazy load images for better performance
            if ('loading' in HTMLImageElement.prototype) {
                const images = document.querySelectorAll('img[loading="lazy"]');
                images.forEach(img => {
                    img.src = img.src;
                });
            } else {
                // Fallback for browsers that don't support lazy loading
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
                document.body.appendChild(script);
            }
            
            // Add resize handling for better responsiveness
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    // Refresh animasi saat ukuran layar berubah
                    AOS.refresh();
                }, 250);
            }, { passive: true });
        });
    </script>
</body>
</html>
