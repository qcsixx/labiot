<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Berhasil - Lab IoT Vokasi UB</title>
    <link rel="icon" href="{{ asset('images/logo-vokasi-ub.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
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
            background-color: #0a0a0a;
            color: white;
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
            margin: 0;
            padding: 0;
            overflow: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 0;
        }
        
        .success-container {
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
            text-align: center;
        }
        
        .success-container:before {
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
        
        .success-icon {
            font-size: 3rem;
            color: #48bb78;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .close-button {
            display: inline-block;
            background: rgba(15, 76, 129, 0.3);
            color: white;
            border: 1px solid rgba(15, 76, 129, 0.5);
            border-radius: 4px;
            padding: 8px 20px;
            margin-top: 20px;
            font-size: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .close-button:hover {
            background: rgba(15, 76, 129, 0.5);
        }
        
        /* Spinner loading animation */
        .spinner-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            border-top-color: var(--accent);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Circular progress */
        .circular-progress {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 20px auto;
        }
        
        .circular-progress svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }
        
        .circular-progress circle {
            fill: transparent;
            stroke-width: 5;
            stroke-dasharray: 170;
            stroke-dashoffset: 170;
            stroke-linecap: round;
        }
        
        .circular-progress .bg {
            stroke: rgba(255, 255, 255, 0.1);
            stroke-dashoffset: 0;
        }
        
        .circular-progress .progress {
            stroke: var(--accent);
            transition: stroke-dashoffset 0.5s ease;
        }
        
        .countdown {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--accent);
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="circuit-bg"></div>
    
    <div class="container mx-auto px-4 flex items-center justify-center min-h-screen">
        <div class="success-container">
            <i class="fas fa-check-circle success-icon"></i>
            <h1 class="text-2xl font-bold mb-2">Verifikasi Berhasil!</h1>
            <p class="mb-4">Email Anda telah berhasil diverifikasi.</p>
            <p class="text-sm text-gray-300">Tab ini akan tertutup secara otomatis dalam</p>
            
            <!-- Circular Progress dengan countdown di tengah -->
            <div class="circular-progress" id="circular-progress">
                <svg viewBox="0 0 60 60">
                    <circle class="bg" cx="30" cy="30" r="27"></circle>
                    <circle class="progress" id="progress-circle" cx="30" cy="30" r="27"></circle>
                </svg>
                <div class="countdown" id="countdown">5</div>
            </div>
            
            <button class="close-button" onclick="window.close()">
                Tutup Tab Ini
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Countdown untuk menutup tab
            let count = 5;
            const countdownEl = document.getElementById('countdown');
            const progressCircle = document.getElementById('progress-circle');
            
            // Nilai awal untuk circle progress
            const circumference = 2 * Math.PI * 27;
            progressCircle.style.strokeDasharray = circumference;
            progressCircle.style.strokeDashoffset = circumference;
            
            const interval = setInterval(() => {
                count--;
                countdownEl.textContent = count;
                
                // Update circular progress
                const progress = (5 - count) / 5;
                const offset = circumference - (progress * circumference);
                progressCircle.style.strokeDashoffset = offset;
                
                if (count <= 0) {
                    clearInterval(interval);
                    window.close();
                    
                    // Jika gagal menutup tab (karena kebijakan browser)
                    setTimeout(() => {
                        countdownEl.textContent = "!";
                        document.querySelector('.text-gray-300').textContent = "Tab tidak dapat ditutup otomatis";
                    }, 1000);
                }
            }, 1000);
        });
    </script>
</body>
</html> 