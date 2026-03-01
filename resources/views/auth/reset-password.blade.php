<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Lab IoT Vokasi UB</title>
    <link rel="icon" href="{{ asset('images/logo-vokasi-ub.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #0F4C81;
            --secondary-color: #FDB813;
        }
        
        body {
            background-color: #0a0a0a;
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 0;
        }
        
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
        
        .container-center {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
            z-index: 10;
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
            color: white;
            width: 100%;
            max-width: 450px;
            padding: 30px;
            margin: 0 auto;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.7s ease;
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
        
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            100% {
                background-position: 200% 50%;
            }
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
        
        .form-input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .form-label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        
        .input-wrapper {
            position: relative;
            width: 100%;
        }
        
        .form-input {
            background: rgba(30, 30, 30, 0.9);
            border: 1px solid rgba(15, 76, 129, 0.5);
            color: white;
            border-radius: 8px;
            transition: all 0.3s ease;
            padding: 12px 15px 12px 40px;
            width: 100%;
            box-sizing: border-box;
            height: 45px;
        }
        
        .form-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(15, 76, 129, 0.5), 0 0 15px rgba(15, 76, 129, 0.5);
            border-color: #0F4C81;
            animation: pulse 2s infinite;
        }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(253, 184, 19, 0.7);
            z-index: 2;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            z-index: 3;
            transition: all 0.3s ease;
            background: none;
            border: none;
            padding: 0;
            height: 24px;
            width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: var(--secondary-color);
            text-shadow: 0 0 8px rgba(253, 184, 19, 0.5);
        }
        
        .password-toggle:focus {
            outline: none;
        }
        
        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .submit-btn {
            background: linear-gradient(45deg, #0F4C81, #1a6bab);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }
        
        .submit-btn:hover {
            background: linear-gradient(45deg, #FDB813, #fdca4d);
            color: #0a0a0a;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(253, 184, 19, 0.4);
        }
        
        .submit-btn:active {
            transform: scale(0.98);
        }
        
        .back-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .back-link:hover {
            color: #FDB813;
            text-shadow: 0 0 8px rgba(253, 184, 19, 0.5);
        }
        
        .success-message {
            background: rgba(72, 187, 120, 0.2);
            border: 1px solid rgba(72, 187, 120, 0.5);
            color: #48bb78;
            border-radius: 8px;
            padding: 12px;
            margin: 15px 0;
            text-align: center;
        }
        
        .error-message {
            color: #f56565;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(15, 76, 129, 0.5);
            }
            70% {
                box-shadow: 0 0 0 5px rgba(15, 76, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(15, 76, 129, 0);
            }
        }
        
        @media screen and (max-width: 640px) {
            .card-container {
                padding: 20px;
            }
            
            .floating-chip {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="circuit-bg">
        <!-- Floating Chips -->
        <div class="floating-chip chip-1"></div>
        <div class="floating-chip chip-2"></div>
        <div class="floating-chip chip-3"></div>
        
        <div class="container-center">
            <div id="reset-password-card" class="card-container">
                <h2 class="form-title text-2xl font-bold text-center mb-1">Reset Password</h2>
                <p class="form-subtitle text-sm text-center mb-6">Masukkan password baru Anda</p>

                @if(session('status'))
                    <div class="success-message">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Form Reset Password -->
                <form method="POST" action="{{ route('password.update') }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div class="form-input-group">
                        <label for="email" class="form-label text-sm">Email</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autofocus
                                class="form-input" placeholder="Alamat email">
                        </div>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-input-group">
                        <label for="password" class="form-label text-sm">Password Baru</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input id="password" type="password" name="password" required
                                class="form-input" placeholder="Password baru">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-input-group">
                        <label for="password-confirm" class="form-label text-sm">Konfirmasi Password Baru</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input id="password-confirm" type="password" name="password_confirmation" required
                                class="form-input" placeholder="Konfirmasi password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password-confirm')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <button type="submit" class="submit-btn">
                            Reset Password
                        </button>
                    </div>
                </form>

                <!-- Kembali ke Login -->
                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                document.getElementById("reset-password-card").style.opacity = "1";
                document.getElementById("reset-password-card").style.transform = "translateY(0)";
            }, 200);
        });
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html> 