<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Terkirim - Lab IoT Vokasi UB</title>
    <link rel="icon" href="{{ asset('images/logo-vokasi-ub.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0F4C81;
            --secondary-color: #FDB813;
        }
        
        body {
            background-color: #0a0a0a;
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
        }
        
        .circuit-bg {
            background-color: #0a0a0a;
            background-image: 
                radial-gradient(#0F4C81 1px, transparent 1px),
                linear-gradient(to right, rgba(15, 76, 129, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 76, 129, 0.1) 1px, transparent 1px);
            background-size: 20px 20px, 20px 20px, 20px 20px;
            position: relative;
        }
        
        .floating-chip {
            position: absolute;
            width: 120px;
            height: 120px;
            background-color: rgba(15, 76, 129, 0.05);
            border: 1px solid rgba(253, 184, 19, 0.2);
            border-radius: 10px;
            z-index: 0;
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
        
        .card-container {
            backdrop-filter: blur(5px);
            background: rgba(20, 20, 20, 0.8);
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(15, 76, 129, 0.3);
            border: 1px solid rgba(15, 76, 129, 0.4);
            position: relative;
            z-index: 10;
            overflow: hidden;
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
        
        .form-title {
            color: white;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 0 0 10px rgba(253, 184, 19, 0.3);
        }
        
        .form-subtitle {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .back-link {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .back-link:hover {
            color: #FDB813;
            text-shadow: 0 0 8px rgba(253, 184, 19, 0.5);
        }
        
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            100% {
                background-position: 200% 50%;
            }
        }
        
        .email-notification {
            background: linear-gradient(135deg, rgba(15, 76, 129, 0.2), rgba(253, 184, 19, 0.1));
            border: 1px solid rgba(15, 76, 129, 0.5);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.6s ease-out forwards;
            transform-origin: top;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .notification-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: rgba(15, 76, 129, 0.8);
            border-radius: 50%;
            margin: 0 auto 15px;
            box-shadow: 0 0 15px rgba(15, 76, 129, 0.5);
            animation: glowPulse 2s infinite;
        }
        
        @keyframes glowPulse {
            0% {
                box-shadow: 0 0 5px rgba(15, 76, 129, 0.5);
            }
            50% {
                box-shadow: 0 0 20px rgba(15, 76, 129, 0.8);
            }
            100% {
                box-shadow: 0 0 5px rgba(15, 76, 129, 0.5);
            }
        }
        
        .notification-icon i {
            color: var(--secondary-color);
            font-size: 28px;
        }
        
        .notification-title {
            color: #48bb78;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .notification-text {
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            line-height: 1.6;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #0F4C81, #1a6bab);
            border: none;
            position: relative;
            z-index: 1;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 4px 12px rgba(15, 76, 129, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #FDB813, #fdca4d);
            color: #0a0a0a;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(253, 184, 19, 0.35);
        }
        
        .btn-primary:active {
            transform: scale(0.98);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="circuit-bg flex items-center justify-center min-h-screen py-8">
    <!-- Floating Chips -->
    <div class="floating-chip chip-1"></div>
    <div class="floating-chip chip-2"></div>
    <div class="floating-chip chip-3"></div>
    
    <div id="success-card" class="w-full max-w-md p-8 card-container opacity-0 transform translate-y-10 transition-all duration-700">
        <h2 class="text-2xl font-bold text-center form-title mb-1">Reset Password</h2>
        <p class="text-sm text-center form-subtitle mb-6">Email reset password berhasil dikirim</p>

        <!-- Notifikasi Sukses -->
        <div class="email-notification">
            <div class="notification-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h3 class="notification-title">Email Terkirim!</h3>
            <p class="notification-text">
                Link reset password telah dikirim ke alamat email Anda. <br>
                Silakan periksa inbox dan folder spam Anda, lalu klik tautan
                yang diberikan untuk melanjutkan proses reset password.
            </p>
        </div>

        <!-- Tombol Login -->
        <div class="mt-8">
            <a href="{{ route('login') }}" class="btn-primary w-full px-4 py-3 text-white rounded-lg font-medium block text-center">
                Kembali ke Halaman Login
            </a>
        </div>
        
        <!-- Kembali ke Lupa Password -->
        <div class="mt-6 text-center">
            <a href="{{ route('password.request') }}" class="back-link text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Form Lupa Password
            </a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Tampilkan Card dengan animasi
            setTimeout(() => {
                document.getElementById("success-card").classList.remove("opacity-0", "translate-y-10");
            }, 200);
        });
    </script>
</body>
</html> 