<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Lab IoT Vokasi UB</title>
    <link rel="icon" href="{{ asset('images/logo-vokasi-ub.png') }}" type="image/png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#0F4C81',
                        'ub-yellow': '#FDB813',
                        'primary-dark': '#0D3A5E',
                        'accent': '#FDB813',
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        /* Custom Variables mapped to Tailwind config/CSS vars for consistency */
        :root {
            --primary: #0F4C81;
            --primary-dark: #0D3A5E;
            --accent: #FDB813;
            --secondary: #707070;
            --bg-color: #F9FAFB;
        }

        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .bg-tech {
            position: fixed;
            inset: 0;
            background-color: var(--primary-dark);
            background-image:
                radial-gradient(circle at 15% 50%, rgba(253, 184, 19, 0.1) 0%, transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(15, 76, 129, 0.15) 0%, transparent 30%);
            z-index: -2;
        }

        .circuit-lines {
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25'%3E%3Cdefs%3E%3Cpattern id='circuit' patternUnits='userSpaceOnUse' width='80' height='80' patternTransform='scale(1.5) rotate(0)'%3E%3Cpath d='M0,40 L20,40 L20,60 L40,60 L40,20 L60,20 L60,40 L80,40 M0,20 L20,20 M60,60 L60,80 M40,0 L40,20' stroke='%231E68A5' stroke-width='1' fill='none' stroke-linecap='round' stroke-opacity='0.15'/%3E%3Ccircle cx='20' cy='20' r='2' fill='%23FDB813' fill-opacity='0.15'/%3E%3Ccircle cx='60' cy='60' r='2' fill='%23FDB813' fill-opacity='0.15'/%3E%3Ccircle cx='20' cy='60' r='2' fill='%23FDB813' fill-opacity='0.15'/%3E%3Ccircle cx='60' cy='20' r='2' fill='%23FDB813' fill-opacity='0.15'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100%25' height='100%25' fill='url(%23circuit)'/%3E%3C/svg%3E");
            opacity: 0.7;
            z-index: -1;
        }

        .floating-chips {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .chip {
            position: absolute;
            opacity: 0.6;
            filter: blur(1px);
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(100%) translateX(0) rotate(0deg); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.6; }
            100% { transform: translateY(-100%) translateX(30px) rotate(360deg); opacity: 0; }
        }

        .card-shadow {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25), 0 5px 15px rgba(0, 0, 0, 0.12);
        }

        .input-field {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .input-field:focus {
            border-left: 4px solid var(--accent);
            box-shadow: 0 2px 12px rgba(253, 184, 19, 0.4);
            outline: none;
            animation: inputPulse 2s infinite;
        }

        @keyframes inputPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(253, 184, 19, 0.4);
            }
            70% {
                box-shadow: 0 0 0 8px rgba(253, 184, 19, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(253, 184, 19, 0);
            }
        }

        /* Input focus label animation */
        .input-wrapper {
            position: relative;
            border-radius: 0.5rem;
        }

        .input-field {
            position: relative;
            z-index: 1;
        }

        .input-wrapper .focus-effect {
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            z-index: -1;
            border-radius: 0.5rem;
            pointer-events: none;
        }

        .input-wrapper .focus-effect::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--primary), var(--accent));
            transition: width 0.5s linear;
            border-radius: inherit;
        }

        .input-field:focus ~ .focus-effect::before {
            width: 100%;
            box-shadow: 0 0 8px rgba(253, 184, 19, 0.6);
            animation: borderFlow 2s linear infinite;
        }

        @keyframes borderFlow {
            0% {
                background-position: 0% 0%;
            }
            100% {
                background-position: 200% 0%;
            }
        }

        .input-wrapper .absolute {
            z-index: 2;
        }

        .btn-primary {
            background: var(--primary);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(15, 76, 129, 0.3);
        }

        .btn-primary:hover {
            background: var(--accent);
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(253, 184, 19, 0.35);
        }

        .link-primary {
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .link-primary:hover {
            color: var(--accent);
        }

        .pulse-dot {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
        }

        .pulse-dot::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 2s infinite;
            opacity: 0.8;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            70% { transform: scale(3); opacity: 0; }
            100% { transform: scale(1); opacity: 0; }
        }

        /* Glassmorphism effect */
        .glass-card {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Connector animation */
        .connector {
            position: absolute;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            height: 2px;
            width: 400px;
            opacity: 0.8;
            animation: connect 3s infinite;
        }

        @keyframes connect {
            0% { width: 0; opacity: 0.6; }
            100% { width: 400px; opacity: 0.8; }
        }

        .connector-section {
            position: relative;
            height: 2px;
            display: flex;
            align-items: center;
            width: 100%;
            margin: 0;
        }

        /* Animasi IoT untuk submit button */
        .iot-circuit {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: 0;
            transition: opacity 0.8s ease;
            mix-blend-mode: screen;
        }

        .iot-circuit.active {
            opacity: 0.8;
            z-index: 5;
        }

        .circuit-path {
            stroke: var(--accent);
            stroke-dasharray: 15;
            stroke-dashoffset: 1000;
            stroke-width: 2.5;
            filter: drop-shadow(0 0 8px var(--accent));
            fill: none;
            animation: circuit-dash 3s linear forwards;
        }

        @keyframes circuit-dash {
            to {
                stroke-dashoffset: 0;
            }
        }

        .data-node {
            fill: var(--primary);
            r: 5;
            opacity: 0;
        }

        .data-pulse {
            fill: var(--accent);
            r: 0;
        }

        .data-node.active {
            opacity: 1;
            animation: node-pulse 2s 0.5s infinite;
        }

        .data-pulse.active {
            animation: pulse-travel 2s 0.8s infinite;
        }

        @keyframes node-pulse {
            0%, 100% { fill: var(--primary); r: 5; filter: drop-shadow(0 0 5px rgba(15, 76, 129, 0.8)); }
            50% { fill: var(--accent); r: 8; filter: drop-shadow(0 0 12px rgba(253, 184, 19, 0.9)); }
        }

        @keyframes pulse-travel {
            0% { r: 0; opacity: 1; }
            50% { r: 10; opacity: 0.8; filter: drop-shadow(0 0 15px rgba(253, 184, 19, 0.9)); }
            100% { r: 0; opacity: 0; }
        }

        /* Animasi binary data */
        .binary-stream {
            position: fixed;
            color: var(--accent);
            font-family: monospace;
            font-size: 14px;
            line-height: 1;
            opacity: 0;
            pointer-events: none;
            text-shadow: 0 0 8px var(--accent);
            z-index: 5;
        }

        .binary-stream.active {
            animation: binary-flow 4s linear forwards;
        }

        @keyframes binary-flow {
            0% { opacity: 0; transform: translateY(0); }
            10% { opacity: 0.7; }
            90% { opacity: 0.7; }
            100% { opacity: 0; transform: translateY(-100vh); }
        }

        /* Overlay untuk efek login */
        .login-overlay {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at center, rgba(15, 76, 129, 0.2) 0%, rgba(0, 0, 0, 0.8) 100%);
            z-index: 4;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s ease;
        }

        .login-overlay.active {
            opacity: 1;
        }

        /* Animasi data packet */
        .data-packet {
            position: fixed;
            width: 10px;
            height: 10px;
            background: var(--accent);
            border-radius: 50%;
            z-index: 5;
            filter: drop-shadow(0 0 10px var(--accent));
            opacity: 0;
        }

        .data-packet.active {
            animation: packet-travel 3s linear forwards;
        }

        @keyframes packet-travel {
            0% {
                opacity: 1;
                transform: scale(0.2) translate(0, 0);
            }
            20% {
                opacity: 1;
                transform: scale(1) translate(20vw, -15vh);
            }
            40% {
                transform: scale(1.5) translate(40vw, -5vh);
            }
            60% {
                transform: scale(1) translate(60vw, -20vh);
            }
            80% {
                opacity: 1;
                transform: scale(2) translate(80vw, -10vh);
            }
            100% {
                opacity: 0;
                transform: scale(0.5) translate(100vw, -25vh);
            }
        }

        /* Animasi tombol */
        .btn-primary.processing {
            position: relative;
            overflow: hidden;
        }

        .btn-primary.processing::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: btn-processing 1.5s infinite;
        }

        @keyframes btn-processing {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Media queries untuk responsivitas */
        @media (max-width: 1024px) {
            .input-field {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 768px) {
            #register-card {
                max-width: 90%;
                padding: 1.5rem;
                margin: 0 auto;
            }

            #register-card h2 {
                font-size: 1.25rem;
                margin-bottom: 0.25rem;
            }

            #register-card p {
                font-size: 0.875rem;
                margin-bottom: 1rem;
            }

            .input-field {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
                font-size: 0.9rem;
            }

            .mt-4 {
                margin-top: 0.75rem;
            }

            .mt-6 {
                margin-top: 1rem;
            }

            label {
                font-size: 0.8rem;
                margin-bottom: 0.25rem;
            }

            .btn-primary {
                padding: 0.5rem;
            }

            .text-xs {
                font-size: 0.65rem;
            }
        }

        @media (max-width: 480px) {
            #register-card {
                max-width: 95%;
                padding: 1.25rem;
            }

            #register-card h2 {
                font-size: 1.125rem;
            }

            .input-field {
                font-size: 0.875rem;
            }

            .mt-4 {
                margin-top: 0.5rem;
            }

            .mt-6 {
                margin-top: 0.75rem;
            }

            .text-sm {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="bg-tech"></div>
    <div class="circuit-lines"></div>

    <div class="floating-chips" id="floating-chips"></div>

    <!-- Pulse Dots -->
    <div class="pulse-dot top-[15%] left-[20%]"></div>
    <div class="pulse-dot bottom-[25%] right-[15%]"></div>
    <div class="pulse-dot top-[60%] right-[30%]"></div>

    <!-- Animasi background yang akan aktif saat register -->
    <div class="login-overlay" id="login-overlay"></div>

    <!-- IoT Circuit di background -->
    <div class="iot-circuit" id="iot-circuit">
        <svg width="100%" height="100%" viewBox="0 0 1920 1080" xmlns="http://www.w3.org/2000/svg">
            <!-- Circuit paths -->
            <path class="circuit-path" d="M0,540 L300,540 L300,200 L600,200 L600,700 L900,700 L900,300 L1200,300 L1200,800 L1500,800 L1500,100 L1920,100" />
            <path class="circuit-path" d="M0,300 L200,300 L200,600 L500,600 L500,900 L800,900 L800,400 L1100,400 L1100,200 L1400,200 L1400,600 L1700,600 L1700,300 L1920,300" style="animation-delay: 0.3s" />
            <path class="circuit-path" d="M0,800 L400,800 L400,500 L700,500 L700,200 L1000,200 L1000,700 L1300,700 L1300,500 L1600,500 L1600,900 L1920,900" style="animation-delay: 0.6s" />
            <path class="circuit-path" d="M960,0 L960,300 L1300,300 L1300,600 L960,600 L960,900 L1600,900 L1600,400 L1920,400" style="animation-delay: 0.9s" />
            <path class="circuit-path" d="M300,0 L300,800 L600,800 L600,400 L900,400 L900,1080" style="animation-delay: 1.2s" />
            <path class="circuit-path" d="M1600,0 L1600,300 L1300,300 L1300,900 L1000,900 L1000,500 L700,500 L700,1080" style="animation-delay: 1.5s" />

            <!-- Data nodes -->
            <circle class="data-node" cx="300" cy="540" r="5" />
            <circle class="data-node" cx="600" cy="200" r="5" />
            <circle class="data-node" cx="900" cy="700" r="5" />
            <circle class="data-node" cx="1200" cy="300" r="5" />
            <circle class="data-node" cx="1500" cy="800" r="5" />
            <circle class="data-node" cx="200" cy="300" r="5" />
            <circle class="data-node" cx="500" cy="600" r="5" />
            <circle class="data-node" cx="800" cy="900" r="5" />
            <circle class="data-node" cx="1100" cy="400" r="5" />
            <circle class="data-node" cx="1400" cy="200" r="5" />
            <circle class="data-node" cx="1700" cy="600" r="5" />
            <circle class="data-node" cx="400" cy="800" r="5" />
            <circle class="data-node" cx="700" cy="500" r="5" />
            <circle class="data-node" cx="1000" cy="200" r="5" />
            <circle class="data-node" cx="1300" cy="700" r="5" />
            <circle class="data-node" cx="1600" cy="500" r="5" />
            <circle class="data-node" cx="960" cy="300" r="5" />
            <circle class="data-node" cx="1300" cy="600" r="5" />
            <circle class="data-node" cx="960" cy="900" r="5" />
            <circle class="data-node" cx="300" cy="800" r="5" />
            <circle class="data-node" cx="1600" cy="300" r="5" />

            <!-- Pulse effects -->
            <circle class="data-pulse" cx="600" cy="700" r="0" />
            <circle class="data-pulse" cx="1200" cy="800" r="0" />
            <circle class="data-pulse" cx="500" cy="900" r="0" />
            <circle class="data-pulse" cx="1100" cy="200" r="0" />
            <circle class="data-pulse" cx="400" cy="500" r="0" />
            <circle class="data-pulse" cx="1000" cy="700" r="0" />
            <circle class="data-pulse" cx="1300" cy="500" r="0" />
            <circle class="data-pulse" cx="960" cy="600" r="0" />
            <circle class="data-pulse" cx="600" cy="400" r="0" />
        </svg>
    </div>

    <!-- Register Container -->
    <div class="container mx-auto px-4 relative z-10 flex flex-col lg:flex-row items-center justify-center gap-12">

        <!-- Left Side: Hero/Info -->
        <div class="w-full lg:w-2/5 text-white text-center lg:text-left mb-8 lg:mb-0">
            <div class="relative">
                <h1 class="text-4xl lg:text-5xl font-bold mb-3">Lab IoT Vokasi UB</h1>
                <p class="text-lg text-gray-300 mb-3">Sistem Peminjaman Peralatan IoT</p>

                <!-- Connector sebagai garis pemisah -->
                <div class="hidden lg:block w-full mb-3 relative">
                    <div class="connector-section w-full relative overflow-hidden">
                        <div class="connector"></div>
                    </div>
                </div>

                <!-- Features -->
                <div class="hidden lg:flex space-x-6 mb-8 justify-center lg:justify-start">
                    <div class="flex items-center">
                        <div class="bg-opacity-30 p-2 rounded-lg mr-3" style="background-color: rgba(253, 184, 19, 0.2);">
                            <i class="fas fa-shield-alt" style="color: #FDB813;"></i>
                        </div>
                        <span>Aman & Terpercaya</span>
                    </div>
                    <div class="flex items-center">
                        <div class="bg-opacity-30 p-2 rounded-lg mr-3" style="background-color: rgba(253, 184, 19, 0.2);">
                            <i class="fas fa-bolt" style="color: #FDB813;"></i>
                        </div>
                        <span>Cepat & Efisien</span>
                    </div>
                </div>

                <!-- Technology indicators -->
                <div class="hidden lg:block absolute -bottom-10 left-0 w-full">
                    <div class="flex justify-start space-x-2">
                        <div class="h-1 w-12 rounded-full" style="background-color: #0F4C81;"></div>
                        <div class="h-1 w-8 rounded-full" style="background-color: #FDB813;"></div>
                        <div class="h-1 w-4 rounded-full bg-gray-400"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="w-full lg:w-2/5 relative">
            <div id="register-card" class="glass-card rounded-2xl p-6 card-shadow relative z-10 opacity-0 transform translate-y-10 transition-all duration-700 mx-auto max-w-md">
                <h2 class="text-xl font-bold text-center text-gray-800 mb-1">Buat Akun Baru</h2>
                <p class="text-center text-gray-600 mb-4 text-sm">Daftar untuk mengakses peminjaman barang IoT</p>

                @if(session('success'))
                    <div class="mb-4 p-2 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-md">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3"></i>
                            <span>{{ session('success') }}</span>
                        </div>
            </div>
        @endif

                <form method="POST" action="{{ route('register.submit') }}" id="register-form" class="mt-4">
            @csrf

            <!-- Name Field -->
                    <div class="mb-3">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <div class="relative input-wrapper">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                                class="input-field pl-10 w-full px-4 py-2 bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-75"
                                style="--tw-ring-color: #FDB813; border: none; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);"
                                placeholder="Masukkan nama lengkap">
                            <div class="focus-effect"></div>
                        </div>
                @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- NIM Field -->
                    <div class="mb-3">
                        <label for="nim" class="block text-sm font-medium text-gray-700 mb-1">NIM</label>
                        <div class="relative input-wrapper">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-gray-400"></i>
                            </div>
                <input id="nim" type="text" name="nim" value="{{ old('nim') }}" required
                                class="input-field pl-10 w-full px-4 py-2 bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-75"
                                style="--tw-ring-color: #FDB813; border: none; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);"
                                placeholder="Masukkan NIM">
                            <div class="focus-effect"></div>
                        </div>
                @error('nim')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="relative input-wrapper">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                class="input-field pl-10 w-full px-4 py-2 bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-75"
                                style="--tw-ring-color: #FDB813; border: none; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);"
                                placeholder="Masukkan email">
                            <div class="focus-effect"></div>
                        </div>
                @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative input-wrapper">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                    <input id="password" type="password" name="password" required autocomplete="off"
                                class="input-field pl-10 w-full px-4 py-2 bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-75"
                                style="--tw-ring-color: #FDB813; border: none; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
                    <button type="button" onclick="togglePassword('password')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                                <i id="password-eye-icon" class="fas fa-eye"></i>
                    </button>
                            <div class="focus-effect"></div>
                </div>
                        <p class="text-xs text-gray-500 mt-1">Min 8 karakter, 1 huruf besar, 1 huruf kecil, 1 angka</p>
                @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password Field -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <div class="relative input-wrapper">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="off"
                                class="input-field pl-10 w-full px-4 py-2 bg-gray-50 rounded-lg focus:outline-none focus:ring-2 focus:ring-opacity-75"
                                style="--tw-ring-color: #FDB813; border: none; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
                    <button type="button" onclick="togglePassword('password_confirmation')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                                <i id="confirm-eye-icon" class="fas fa-eye"></i>
                    </button>
                            <div class="focus-effect"></div>
                </div>
                @error('password_confirmation')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Register Button -->
                    <button type="submit" id="register-btn" class="btn-primary w-full py-2 px-4 text-white rounded-lg font-medium flex items-center justify-center mt-4">
                        <span>Daftar</span>
                </button>

            <!-- Already Registered? -->
                    <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                            <a href="{{ route('login') }}" class="font-medium link-primary hover:underline transition duration-300">
                        Login disini
                    </a>
                </p>
            </div>
        </form>

                <!-- Decoration element -->
                <div class="absolute -bottom-1 left-0 right-0 h-1 rounded-b-2xl overflow-hidden">
                    <div class="h-full w-full bg-gradient-to-r from-[#0F4C81] to-[#FDB813]"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                document.getElementById("register-card").classList.remove("opacity-0", "translate-y-10");
            }, 300);

            // Create floating IoT chips
            createIoTElements();
        });

        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = inputId === 'password' ? document.getElementById('password-eye-icon') : document.getElementById('confirm-eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        function createIoTElements() {
            const container = document.getElementById('floating-chips');
            const chipCount = 12;

            // IoT chip SVGs
            const chipSVGs = [
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FDB813" stroke-width="1.5"><rect x="4" y="4" width="16" height="16" rx="2" /><rect x="9" y="9" width="6" height="6" /><line x1="9" y1="2" x2="9" y2="4" /><line x1="15" y1="2" x2="15" y2="4" /><line x1="9" y1="20" x2="9" y2="22" /><line x1="15" y1="20" x2="15" y2="22" /><line x1="20" y1="9" x2="22" y2="9" /><line x1="20" y1="14" x2="22" y2="14" /><line x1="2" y1="9" x2="4" y2="9" /><line x1="2" y1="14" x2="4" y2="14" /></svg>',
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0F4C81" stroke-width="1.5"><path d="M10 2L8 6H16L14 2M8 6L4 10L8 14M16 6L20 10L16 14M8 14L10 18L14 18L16 14M10 18L8 22M14 18L16 22"/></svg>',
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0F4C81" stroke-width="1.5"><circle cx="12" cy="12" r="8" /><path d="M12 8L12 12L15 15" /></svg>',
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FDB813" stroke-width="1.5"><path d="M4 9H20M4 15H20M10 3L8 21M16 3L14 21"/></svg>'
            ];

            for (let i = 0; i < chipCount; i++) {
                const chip = document.createElement('div');
                chip.classList.add('chip');

                // Random SVG
                const randomSVG = chipSVGs[Math.floor(Math.random() * chipSVGs.length)];
                chip.innerHTML = randomSVG;

                // Random position
                chip.style.left = `${Math.random() * 100}%`;

                // Random duration
                const duration = Math.random() * 20 + 10; // 10-30s
                chip.style.animation = `float ${duration}s infinite linear`;

                // Random delay
                chip.style.animationDelay = `${Math.random() * 20}s`;

                container.appendChild(chip);
            }
        }
    </script>
</body>
</html>
