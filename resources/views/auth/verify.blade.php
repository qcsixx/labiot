@extends('layouts.app')

@section('content')
<div class="circuit-bg">
    <!-- Floating Chips -->
    <div class="floating-chip chip-1"></div>
    <div class="floating-chip chip-2"></div>
    <div class="floating-chip chip-3"></div>
    
    <div class="container-center">
        <div class="card-container">
            <h2 class="text-2xl font-bold text-center form-title mb-1">Verifikasi Email</h2>
            <p class="text-sm text-center form-subtitle mb-6">Silakan verifikasi alamat email Anda</p>

            @if (session('resent'))
                <div class="success-message p-4 text-sm text-center mb-6">
                    Link verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            @endif
            
            @if (session('error'))
                <div class="error-message p-4 text-sm text-center mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div id="verification-status" class="hidden success-message p-4 text-sm text-center mb-6">
                Email berhasil diverifikasi! Anda akan diarahkan ke halaman login dalam beberapa detik...
            </div>

            <div class="mt-6">
                <p class="text-sm verify-info">
                    Sebelum melanjutkan, silakan periksa email Anda untuk link verifikasi.
                    <br><br>
                    Jika Anda tidak menerima email,
                    <form class="inline-form" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="resend-button">
                            klik di sini untuk meminta link baru
                        </button>
                    </form>
                </p>
                
                <!-- Tombol Logout -->
                <div class="mt-6 text-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Reset dan variabel dasar */
    :root {
        --primary-color: #0F4C81;
        --secondary-color: #FDB813;
    }
    
    /* Full-screen circuit background */
    .circuit-bg {
        background-color: #0a0a0a;
        background-image: 
            radial-gradient(#0F4C81 1px, transparent 1px),
            linear-gradient(to right, rgba(15, 76, 129, 0.1) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(15, 76, 129, 0.1) 1px, transparent 1px);
        background-size: 20px 20px, 20px 20px, 20px 20px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        margin: 0;
        padding: 0;
        overflow: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 0;
    }
    
    /* Container untuk centering */
    .container-center {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 100vh;
        padding: 20px;
        box-sizing: border-box;
    }
    
    /* Floating chips */
    .floating-chip {
        position: fixed;
        width: 120px;
        height: 120px;
        background-color: rgba(15, 76, 129, 0.05);
        border: 1px solid rgba(253, 184, 19, 0.2);
        border-radius: 10px;
        z-index: 1;
        animation: float 15s infinite ease-in-out;
    }
    
    .floating-chip:before {
        content: '';
        position: absolute;
        width: 80%;
        height: 80%;
        top: 10%;
        left: 10%;
        border: 1px dotted rgba(253, 184, 19, 0.3);
        border-radius: 5px;
    }
    
    .chip-1 {
        top: 10%;
        left: 5%;
        animation-delay: 0s;
    }
    
    .chip-2 {
        top: 60%;
        right: 5%;
        transform: rotate(45deg);
        animation-delay: -5s;
    }
    
    .chip-3 {
        bottom: 15%;
        left: 15%;
        transform: rotate(-30deg);
        animation-delay: -10s;
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0);
        }
        25% {
            transform: translateY(-15px) rotate(5deg);
        }
        50% {
            transform: translateY(5px) rotate(-5deg);
        }
        75% {
            transform: translateY(-5px) rotate(2deg);
        }
    }
    
    /* Card container */
    .card-container {
        backdrop-filter: blur(5px);
        background: rgba(20, 20, 20, 0.8);
        border-radius: 15px;
        box-shadow: 0 0 30px rgba(15, 76, 129, 0.3);
        border: 1px solid rgba(15, 76, 129, 0.4);
        position: relative;
        z-index: 10;
        overflow: hidden;
        animation: appear 0.7s ease forwards;
        color: white;
        width: 100%;
        max-width: 420px;
        padding: 30px;
        margin: 0 auto;
    }
    
    .card-container:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #0F4C81, #FDB813, #0F4C81);
        background-size: 200% 100%;
        animation: gradient 4s linear infinite;
        z-index: 1;
    }
    
    @keyframes appear {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes gradient {
        0% {
            background-position: 0% 50%;
        }
        100% {
            background-position: 200% 50%;
        }
    }
    
    /* Typography styles */
    .form-title {
        color: white;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-shadow: 0 0 10px rgba(253, 184, 19, 0.3);
        margin-bottom: 8px;
    }
    
    .form-subtitle {
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 24px;
    }
    
    .success-message {
        background: rgba(72, 187, 120, 0.2);
        border: 1px solid rgba(72, 187, 120, 0.5);
        color: #48bb78;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 24px;
        text-align: center;
    }
    
    .verify-info {
        color: rgba(255, 255, 255, 0.7);
        line-height: 1.6;
    }
    
    /* Form styles */
    .inline-form {
        display: inline;
    }
    
    .resend-button {
        background: none;
        border: none;
        color: rgba(253, 184, 19, 0.8);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        padding: 0;
        font-weight: 500;
        text-decoration: underline;
    }
    
    .resend-button:hover {
        color: #FDB813;
        text-shadow: 0 0 8px rgba(253, 184, 19, 0.5);
    }
    
    /* Media queries for responsiveness */
    @media screen and (max-width: 640px) {
        .card-container {
            padding: 20px;
            margin: 10px;
            width: calc(100% - 20px);
        }
        
        .floating-chip {
            width: 80px;
            height: 80px;
        }
    }
    
    @media screen and (max-height: 600px) {
        .container-center {
            padding-top: 60px;
            padding-bottom: 60px;
            align-items: flex-start;
        }
    }
    
    /* Make sure all utility classes are defined */
    .text-2xl {
        font-size: 1.5rem;
        line-height: 2rem;
    }
    
    .font-bold {
        font-weight: 700;
    }
    
    .text-center {
        text-align: center;
    }
    
    .mb-1 {
        margin-bottom: 0.25rem;
    }
    
    .text-sm {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }
    
    .mb-6 {
        margin-bottom: 1.5rem;
    }
    
    .p-4 {
        padding: 1rem;
    }
    
    .mt-6 {
        margin-top: 1.5rem;
    }
    
    /* Menambahkan style untuk tombol logout */
    .logout-button {
        display: inline-block;
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        padding: 6px 16px;
        margin-top: 20px;
        font-size: 14px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .logout-button:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    
    /* Style tambahan untuk error message */
    .error-message {
        background: rgba(245, 101, 101, 0.2);
        border: 1px solid rgba(245, 101, 101, 0.5);
        color: #f56565;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 24px;
        text-align: center;
    }

    /* Utility class */
    .hidden {
        display: none;
    }
</style>

<script>
    // Polling untuk memeriksa status verifikasi email
    document.addEventListener('DOMContentLoaded', function() {
        // Periksa status verifikasi setiap 3 detik
        const checkInterval = setInterval(checkVerificationStatus, 3000);
        
        function checkVerificationStatus() {
            fetch('{{ route("verification.check") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.verified) {
                    // Jika email sudah diverifikasi
                    clearInterval(checkInterval);
                    document.getElementById('verification-status').classList.remove('hidden');
                    
                    // Redirect ke halaman login setelah 3 detik
                    setTimeout(function() {
                        window.location.href = '{{ route("login") }}';
                    }, 3000);
                }
            })
            .catch(error => console.error('Error checking verification status:', error));
        }
    });
</script>
@endsection 