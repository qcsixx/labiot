@extends('layouts.user.user-layout')

@section('title', 'Profil Pengguna - Lab IoT Vokasi UB')

@push('styles')
<style>
    /* Page background */
    body {
        position: relative;
        background: linear-gradient(135deg, #f0f5ff 0%, #ffffff 100%);
        overflow-x: hidden;
    }

    /* Master IoT Background Illustration */
    .iot-master-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        opacity: 0.4;
        pointer-events: none;
        background-color: #f0f5ff;
        overflow: hidden;
    }

    .iot-elements {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .profile-content {
        position: relative;
        z-index: 1;
    }

    /* Animated Elements */
    .iot-animate-pulse {
        animation: iotPulse 4s infinite ease-in-out;
    }

    .iot-animate-float {
        animation: iotFloat 8s infinite ease-in-out;
    }

    .iot-animate-spin {
        transform-origin: center;
        animation: iotSpin 20s infinite linear;
    }

    .iot-animate-dash {
        stroke-dasharray: 10;
        animation: iotDash 20s infinite linear;
    }

    .iot-animate-transmission {
        stroke-dasharray: 10;
        stroke-dashoffset: 1000;
        animation: iotTransmission 10s infinite linear;
    }

    .data-flow {
        animation: dataFlow 15s infinite linear;
    }

    @keyframes iotPulse {
        0%, 100% { opacity: 0.2; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(1.05); }
    }

    @keyframes iotFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes iotSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes iotDash {
        to { stroke-dashoffset: 1000; }
    }

    @keyframes iotTransmission {
        to { stroke-dashoffset: 0; }
    }

    @keyframes dataFlow {
        0% { stroke-dashoffset: 1000; }
        100% { stroke-dashoffset: 0; }
    }

    /* Animasi dan Efek */
    @keyframes floatUp {
        0% { transform: translateY(10px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes gradientFlow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .animate-float-up {
        animation: floatUp 0.6s ease-out forwards;
    }

    .animate-pulse-slow {
        animation: pulse 3s infinite ease-in-out;
    }

    /* Efek Parallax sederhana */
    .parallax {
        transform: translateY(var(--parallax-y, 0));
        transition: transform 0.1s ease-out;
    }

    /* Desain Profil */
    .profile-header {
        background: rgba(15, 76, 129, 0.7);
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(15, 76, 129, 0.15);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.05;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    /* Penampilan Card Profile */
    .profile-card {
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 10px 30px rgba(15, 76, 129, 0.1);
        position: relative;
    }

    /* Card tanpa efek hover */
    .profile-card-static {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 76, 129, 0.1);
        position: relative;
    }

    /* Card dengan efek frosted glass */
    .frosted-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(15, 76, 129, 0.08);
    }

    /* Background pattern untuk card data profile */
    .iot-pattern-bg {
        position: relative;
        overflow: hidden;
    }

    .connect-line {
        position: absolute;
        width: 3px;
        background: linear-gradient(to bottom, #0F4C81, #FDB813);
        top: 50px;
        bottom: 50px;
        left: 40px;
        z-index: 0;
    }

    .data-point {
        position: relative;
        z-index: 1;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 16px;
        transition: all 0.3s ease;
    }

    .data-point-icon {
        background: rgba(15, 76, 129, 0.8);
        color: white;
        border-radius: 12px;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        transition: all 0.3s ease;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    /* Hover icon saat edit mode */
    .data-point.editing {
        background: rgba(15, 76, 129, 0.15);
    }

    /* Hanya efek hover untuk foto profil */
    .photo-upload-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        transition: all 0.4s ease;
        border: 5px solid rgba(255, 255, 255, 0.2);
    }

    .photo-upload-container:hover {
        transform: scale(1.05);
        border-color: #FDB813;
    }

    .upload-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(15, 76, 129, 0.7);
        padding: 8px 0;
        text-align: center;
        color: white;
        font-size: 12px;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .photo-upload-container:hover .upload-overlay {
        opacity: 1;
    }

    .iot-background {
        position: absolute;
        width: 100%;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180' viewBox='0 0 180 180'%3E%3Cg transform='scale(0.8)'%3E%3Cpath d='M80,30 L100,30 L100,50 L80,50 Z' fill='%23ffffff' fill-opacity='0.03'/%3E%3Cpath d='M130,80 L150,80 L150,100 L130,100 Z' fill='%23ffffff' fill-opacity='0.03'/%3E%3Cpath d='M80,130 L100,130 L100,150 L80,150 Z' fill='%23ffffff' fill-opacity='0.03'/%3E%3Cpath d='M30,80 L50,80 L50,100 L30,100 Z' fill='%23ffffff' fill-opacity='0.03'/%3E%3Cpath d='M115,45 L135,45 L135,65 L115,65 Z' fill='%23ffffff' fill-opacity='0.03'/%3E%3Cpath d='M115,115 L135,115 L135,135 L115,135 Z' fill='%23ffffff' fill-opacity='0.03'/%3E%3Cpath d='M45,115 L65,115 L65,135 L45,135 Z' fill='%23ffffff' fill-opacity='0.03'/%3E%3Cpath d='M45,45 L65,45 L65,65 L45,65 Z' fill='%23ffffff' fill-opacity='0.03'/%3E%3Cpath d='M30,30 L150,30 L150,150 L30,150 Z' stroke='%23ffffff' stroke-opacity='0.03' stroke-width='2' fill='none'/%3E%3Cpath d='M60,60 L120,60 L120,120 L60,120 Z' stroke='%23ffffff' stroke-opacity='0.03' stroke-width='2' fill='none'/%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
        z-index: 0;
    }

    .profile-card-gradient {
        background: linear-gradient(135deg, rgba(15, 76, 129, 0.85) 0%, rgba(13, 67, 114, 0.85) 100%);
        background-size: 200% 200%;
        animation: gradientFlow 8s ease infinite;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .input-focused {
        border-color: #FDB813 !important;
        box-shadow: 0 0 0 3px rgba(253, 184, 19, 0.2) !important;
        background-color: white !important;
    }

    /* Animasi input saat edit mode */
    .input-animated {
        transition: all 0.3s ease;
    }

    .input-animated.active {
        transform: translateX(10px);
        border-color: #FDB813 !important;
        background-color: white !important;
        box-shadow: 0 0 0 4px rgba(253, 184, 19, 0.1) !important;
    }

    .btn-save {
        background-color: #FDCB4D;
        color: #0F4C81;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-save:hover {
        background-color: #FFD970;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(253, 184, 19, 0.2);
    }

    .btn-edit {
        background-color: #0F4C81;
        color: white;
        font-weight: 500;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-edit:hover {
        background-color: #0a3c68;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(15, 76, 129, 0.3);
    }

    .btn-cancel {
        background-color: #f1f5f9;
        color: #475569;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background-color: #e2e8f0;
        color: #334155;
    }

    .btn-ripple {
        position: relative;
        overflow: hidden;
    }

    .btn-ripple::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.5);
        opacity: 0;
        border-radius: 100%;
        transform: scale(1, 1) translate(-50%);
        transform-origin: 50% 50%;
    }

    .btn-ripple:focus:not(:active)::after {
        animation: ripple 1s ease-out;
    }

    @keyframes ripple {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }
        100% {
            transform: scale(20, 20);
            opacity: 0;
        }
    }

    .status-badge {
        position: relative;
        display: inline-flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .status-badge::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #10b981;
        margin-right: 6px;
        animation: pulse 2s infinite;
    }

    .fade-out {
        opacity: 0;
        transition: opacity 0.5s ease;
    }

    /* Smooth Animations for Photo */
    .photo-smooth-animation {
        transition: all 0.6s ease-in-out;
    }

    .photo-smooth-animation:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 25px rgba(15, 76, 129, 0.25);
    }

    /* Modern Toast Notification Styling */
    .swal2-container.swal2-top-end {
        top: 60px !important; /* Mengurangi jarak dari atas */
        right: 15px !important;
    }

    /* Custom Toast Animation - Slide from right */
    .swal2-popup.swal2-toast.modern-toast {
        padding: 10px 15px; /* Mengurangi padding */
        border-radius: 8px; /* Mengurangi radius */
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: none;
        max-width: 280px; /* Membatasi lebar maksimum */
        animation: slideInRight 0.5s ease forwards;
        background: white;
    }

    .swal2-popup.swal2-toast.modern-toast.swal2-icon-success {
        background: #ffffff;
        border-left: 3px solid #4CAF50;
    }

    .swal2-popup.swal2-toast.modern-toast.swal2-icon-error {
        background: #ffffff;
        border-left: 3px solid #F44336;
    }

    /* Customize icons */
    .modern-toast .swal2-icon {
        margin: 0 8px 0 0 !important;
        transform: scale(0.8); /* Mengecilkan ukuran ikon */
    }

    /* Customize title */
    .modern-toast .swal2-title {
        font-size: 13px !important;
        color: #333 !important;
        font-weight: 400 !important;
        margin: 0 !important;
    }

    /* Progress bar styling */
    .modern-toast .swal2-timer-progress-bar {
        background: rgba(0, 0, 0, 0.15);
        height: 2px !important;
        bottom: 0;
    }

    /* Slide in from right animation */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Slide out to right animation */
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .swal2-popup.swal2-toast.swal2-hide {
        animation: slideOutRight 0.5s ease forwards !important;
    }
</style>
@endpush

@section('content')
<!-- Master IoT Background Illustration -->
<div class="iot-master-bg">
    <svg class="iot-elements" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 800" preserveAspectRatio="xMidYMid slice">
        <!-- Grid Pattern -->
        <defs>
            <pattern id="smallGrid" width="20" height="20" patternUnits="userSpaceOnUse">
                <path d="M 20 0 L 0 0 0 20" fill="none" stroke="#0F4C81" stroke-width="0.5" opacity="0.2"/>
            </pattern>
            <pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse">
                <rect width="100" height="100" fill="url(#smallGrid)"/>
                <path d="M 100 0 L 0 0 0 100" fill="none" stroke="#0F4C81" stroke-width="1" opacity="0.3"/>
            </pattern>
        </defs>

        <!-- Background Grid -->
        <rect width="100%" height="100%" fill="url(#grid)" />

        <!-- IoT Network Lines -->
        <g opacity="0.7">
            <!-- Main Network Hub -->
            <circle cx="700" cy="400" r="15" fill="#0F4C81" class="iot-animate-pulse" />

            <!-- Transmission Lines -->
            <path d="M 700 400 L 300 200 L 200 350 L 450 600 L 700 400 L 950 600 L 1200 350 L 1100 200 L 700 400"
                  stroke="#0F4C81" stroke-width="2" fill="none" opacity="0.5" class="iot-animate-dash" />

            <!-- Additional Data Flow Lines -->
            <path d="M 450 600 L 300 700" stroke="#0F4C81" stroke-width="1.5" fill="none" opacity="0.5" class="iot-animate-dash" />
            <path d="M 950 600 L 1100 700" stroke="#0F4C81" stroke-width="1.5" fill="none" opacity="0.5" class="iot-animate-dash" />
            <path d="M 200 350 L 50 300" stroke="#0F4C81" stroke-width="1.5" fill="none" opacity="0.5" class="iot-animate-dash" />
            <path d="M 1200 350 L 1350 300" stroke="#0F4C81" stroke-width="1.5" fill="none" opacity="0.5" class="iot-animate-dash" />

            <!-- Additional Edge Nodes -->
            <circle cx="300" cy="700" r="6" fill="#FDB813" class="iot-animate-pulse" />
            <circle cx="1100" cy="700" r="6" fill="#FDB813" class="iot-animate-pulse" />
            <circle cx="50" cy="300" r="6" fill="#FDB813" class="iot-animate-pulse" />
            <circle cx="1350" cy="300" r="6" fill="#FDB813" class="iot-animate-pulse" />

            <!-- Secondary Nodes -->
            <circle cx="300" cy="200" r="10" fill="#FDB813" class="iot-animate-pulse" />
            <circle cx="200" cy="350" r="8" fill="#0F4C81" />
            <circle cx="450" cy="600" r="12" fill="#0F4C81" class="iot-animate-pulse" />
            <circle cx="950" cy="600" r="12" fill="#FDB813" />
            <circle cx="1200" cy="350" r="8" fill="#0F4C81" class="iot-animate-pulse" />
            <circle cx="1100" cy="200" r="10" fill="#FDB813" />

            <!-- Data Paths -->
            <path d="M 700 400 L 500 300" stroke="#FDB813" stroke-width="2" stroke-dasharray="5,5" class="data-flow" />
            <path d="M 700 400 L 900 300" stroke="#FDB813" stroke-width="2" stroke-dasharray="5,5" class="data-flow" />
            <path d="M 500 300 L 400 400" stroke="#FDB813" stroke-width="2" stroke-dasharray="5,5" class="data-flow" />
            <path d="M 900 300 L 1000 400" stroke="#FDB813" stroke-width="2" stroke-dasharray="5,5" class="data-flow" />

            <!-- Additional Data Flows -->
            <path d="M 300 200 L 200 150" stroke="#FDB813" stroke-width="1.5" stroke-dasharray="4,4" class="data-flow" />
            <path d="M 1100 200 L 1200 150" stroke="#FDB813" stroke-width="1.5" stroke-dasharray="4,4" class="data-flow" />
            <path d="M 450 600 L 400 650" stroke="#FDB813" stroke-width="1.5" stroke-dasharray="4,4" class="data-flow" />
            <path d="M 950 600 L 1000 650" stroke="#FDB813" stroke-width="1.5" stroke-dasharray="4,4" class="data-flow" />

            <!-- IoT Device Clusters -->
            <g transform="translate(150, 130)" class="iot-animate-float">
                <circle cx="0" cy="0" r="15" fill="#0F4C81" opacity="0.4" />
                <circle cx="30" cy="10" r="10" fill="#0F4C81" opacity="0.3" />
                <circle cx="15" cy="25" r="8" fill="#0F4C81" opacity="0.3" />
                <path d="M 0 0 L 30 10 L 15 25 Z" stroke="#FDB813" stroke-width="1" fill="none" />
            </g>

            <g transform="translate(1200, 130)" class="iot-animate-float">
                <circle cx="0" cy="0" r="15" fill="#0F4C81" opacity="0.4" />
                <circle cx="-30" cy="10" r="10" fill="#0F4C81" opacity="0.3" />
                <circle cx="-15" cy="25" r="8" fill="#0F4C81" opacity="0.3" />
                <path d="M 0 0 L -30 10 L -15 25 Z" stroke="#FDB813" stroke-width="1" fill="none" />
            </g>

            <!-- Sensor Arrays -->
            <g transform="translate(400, 650)" class="iot-animate-pulse">
                <rect x="-25" y="-25" width="50" height="50" rx="5" fill="#0F4C81" opacity="0.3" />
                <circle cx="0" cy="0" r="5" fill="#FDB813" />
                <circle cx="-15" cy="-15" r="3" fill="#FDB813" />
                <circle cx="15" cy="-15" r="3" fill="#FDB813" />
                <circle cx="-15" cy="15" r="3" fill="#FDB813" />
                <circle cx="15" cy="15" r="3" fill="#FDB813" />
            </g>

            <g transform="translate(1000, 650)" class="iot-animate-pulse">
                <rect x="-25" y="-25" width="50" height="50" rx="5" fill="#0F4C81" opacity="0.3" />
                <circle cx="0" cy="0" r="5" fill="#FDB813" />
                <circle cx="-15" cy="-15" r="3" fill="#FDB813" />
                <circle cx="15" cy="-15" r="3" fill="#FDB813" />
                <circle cx="-15" cy="15" r="3" fill="#FDB813" />
                <circle cx="15" cy="15" r="3" fill="#FDB813" />
            </g>

            <!-- Sensors -->
            <circle cx="400" cy="400" r="6" fill="#FDB813" />
            <circle cx="1000" cy="400" r="6" fill="#FDB813" />
            <circle cx="500" cy="300" r="8" fill="#0F4C81" />
            <circle cx="900" cy="300" r="8" fill="#0F4C81" />

            <!-- IoT Devices -->
            <g transform="translate(350, 380)" class="iot-animate-pulse">
                <rect x="0" y="0" width="20" height="30" rx="2" fill="#0F4C81" />
                <rect x="5" y="5" width="10" height="10" rx="1" fill="#FDB813" />
                <rect x="5" y="20" width="10" height="5" rx="1" fill="#ffffff" opacity="0.6" />
            </g>

            <g transform="translate(980, 380)" class="iot-animate-pulse">
                <rect x="0" y="0" width="20" height="30" rx="2" fill="#0F4C81" />
                <rect x="5" y="5" width="10" height="10" rx="1" fill="#FDB813" />
                <rect x="5" y="20" width="10" height="5" rx="1" fill="#ffffff" opacity="0.6" />
            </g>

            <!-- Cloud System -->
            <g transform="translate(650, 150)" class="iot-animate-float">
                <path d="M 25 50 Q 0 50 0 25 Q 0 0 25 0 L 75 0 Q 100 0 100 25 Q 100 50 75 50 L 25 50" fill="#0F4C81" opacity="0.7" />
                <path d="M 30 40 L 70 40 M 30 30 L 70 30 M 30 20 L 70 20" stroke="#ffffff" stroke-width="2" opacity="0.6" />
            </g>

            <!-- Server Racks -->
            <g transform="translate(800, 150)" class="iot-animate-pulse">
                <rect x="0" y="0" width="40" height="60" rx="2" fill="#0F4C81" />
                <rect x="5" y="5" width="30" height="5" rx="1" fill="#FDB813" />
                <rect x="5" y="15" width="30" height="5" rx="1" fill="#FDB813" />
                <rect x="5" y="25" width="30" height="5" rx="1" fill="#FDB813" />
                <rect x="5" y="35" width="30" height="5" rx="1" fill="#FDB813" />
                <rect x="5" y="45" width="30" height="5" rx="1" fill="#FDB813" />
            </g>
        </g>

        <!-- Lab IoT System -->
        <g transform="translate(550, 500)">
            <rect x="0" y="0" width="300" height="150" rx="10" fill="#0F4C81" opacity="0.2" stroke="#0F4C81" stroke-width="2" />
            <text x="150" y="30" font-family="Arial" font-size="14" fill="#0F4C81" text-anchor="middle" font-weight="bold">LAB IoT SYSTEM</text>

            <!-- Lab Devices -->
            <g transform="translate(30, 60)" class="iot-animate-pulse">
                <rect x="0" y="0" width="50" height="70" rx="5" fill="#0F4C81" opacity="0.6" />
                <circle cx="25" cy="20" r="10" fill="#FDB813" />
                <rect x="10" y="40" width="30" height="20" rx="2" fill="#ffffff" opacity="0.6" />
            </g>

            <g transform="translate(120, 60)" class="iot-animate-pulse">
                <rect x="0" y="0" width="70" height="60" rx="5" fill="#0F4C81" opacity="0.6" />
                <circle cx="35" cy="15" r="8" fill="#FDB813" />
                <rect x="10" y="30" width="50" height="15" rx="2" fill="#ffffff" opacity="0.6" />
            </g>

            <g transform="translate(230, 60)" class="iot-animate-pulse">
                <rect x="0" y="0" width="40" height="60" rx="5" fill="#0F4C81" opacity="0.6" />
                <rect x="5" y="10" width="30" height="5" rx="1" fill="#FDB813" />
                <rect x="5" y="20" width="30" height="5" rx="1" fill="#FDB813" />
                <rect x="5" y="30" width="30" height="5" rx="1" fill="#FDB813" />
            </g>

            <!-- Connection Lines -->
            <line x1="55" y1="90" x2="120" y2="90" stroke="#FDB813" stroke-width="2" stroke-dasharray="4,4" class="iot-animate-transmission" />
            <line x1="190" y1="90" x2="230" y2="90" stroke="#FDB813" stroke-width="2" stroke-dasharray="4,4" class="iot-animate-transmission" />
        </g>
    </svg>
</div>

<div class="profile-content mx-auto max-w-5xl py-8">
<div class="profile-header rounded-xl p-3 mb-6 relative">
    <div class="grid grid-cols-1 lg:grid-cols-6 gap-4 relative">
        <!-- Kartu Profil (Lebar 2 kolom) -->
        <div class="lg:col-span-2 animate-float-up" style="animation-delay: 0.1s">
            <div class="profile-card profile-card-gradient h-full">
                <div class="text-center p-4 relative z-10">
                    <form action="{{ route('user.update-photo') }}" method="POST" enctype="multipart/form-data" id="photo-form">
                        @csrf
                        <div class="photo-upload-container mb-4 photo-smooth-animation" style="width: 130px; height: 130px;">
                            <img id="profile-image" src="{{ auth()->user()->profile_photo ? asset('storage/profiles/' . auth()->user()->profile_photo) : asset('images/default-avatar.png') }}"
                                alt="Profile Photo" class="w-full h-full object-cover">
                            <div class="upload-overlay" id="upload-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-auto" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                                Ganti Foto
                            </div>
                        </div>
                        <input type="file" id="photo-upload" name="profile_photo" accept="image/*" class="hidden" onchange="previewImage(event)">
                        <button type="submit" id="upload-photo-btn" class="hidden mt-3 bg-[#FDB813] text-[#0F4C81] px-4 py-2 rounded-full font-semibold hover:bg-[#e5a70f] transition-all">
                            <span class="button-text">Simpan Foto</span>
                            <span class="loading-indicator hidden ml-2 inline-block">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                    </form>
                </div>

                <div class="text-center px-4 pb-4 pt-0 relative z-10">
                    <h2 class="text-xl font-bold text-white">{{ auth()->user()->name }}</h2>
                    <p class="text-gray-300 mb-2 text-sm">{{ auth()->user()->nim }}</p>

                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-green-500 bg-opacity-20 text-green-300 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-green-400 mr-2 animate-pulse"></span>
                        {{ auth()->user()->status }}
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div class="bg-white bg-opacity-10 rounded-xl p-2 flex items-center space-x-2 hover:bg-opacity-15 transition-all">
                            <div class="bg-[#FDB813] bg-opacity-20 p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FDB813]" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-gray-400">Email</p>
                                <p class="text-xs text-white truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <div class="bg-white bg-opacity-10 rounded-xl p-2 flex items-center space-x-2 hover:bg-opacity-15 transition-all">
                            <div class="bg-[#FDB813] bg-opacity-20 p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FDB813]" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-gray-400">Telepon</p>
                                <p class="text-xs text-white">{{ auth()->user()->phone ?? 'Belum diatur' }}</p>
                            </div>
                        </div>

                        <div class="bg-white bg-opacity-10 rounded-xl p-2 flex items-center space-x-2 hover:bg-opacity-15 transition-all">
                            <div class="bg-[#FDB813] bg-opacity-20 p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FDB813]" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-gray-400">Bergabung</p>
                                <p class="text-xs text-white">{{ auth()->user()->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Profil (Lebar 4 kolom) -->
        <div class="lg:col-span-4 animate-float-up" style="animation-delay: 0.2s">
            <div class="profile-card-static frosted-card h-full">
                <div class="p-4" x-data="{
                    editMode: false,
                    name: '{{ auth()->user()->name }}',
                    email: '{{ auth()->user()->email }}',
                    phone: '{{ auth()->user()->phone ?? '' }}',

                    enableEditMode() {
                        this.editMode = true;
                        // Focus pada input nama setelah DOM update
                        this.$nextTick(() => {
                            document.getElementById('name').focus();
                        });
                    },

                    cancelEdit() {
                        this.editMode = false;
                        // Reset nilai input ke data asli
                        this.name = '{{ auth()->user()->name }}';
                        this.email = '{{ auth()->user()->email }}';
                        this.phone = '{{ auth()->user()->phone ?? '' }}';
                    }
                }">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[#0F4C81]">
                            Data Profile
                        </h3>
                </div>

                    <div class="relative">
                        <form action="{{ route('user.update-profile') }}" method="POST" id="profile-form">
                            @csrf
                            @method('PUT')

                            <div class="space-y-4">
                                <!-- Nama -->
                                <div class="data-point p-3 flex items-start" :class="{ 'bg-[#0F4C81]/5 editing': editMode }">
                                    <div class="data-point-icon mr-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                    <div class="flex-grow">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                        <input type="text" name="name" id="name" x-model="name"
                                               class="input-animated w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:input-focused text-sm text-gray-900 bg-white"
                                               :class="{ 'active': editMode }"
                                               :readonly="!editMode"
                                       placeholder="Nama Lengkap">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                    </div>
                            </div>

                                <!-- Email -->
                                <div class="data-point p-3 flex items-start" :class="{ 'bg-[#0F4C81]/5 editing': editMode }">
                                    <div class="data-point-icon mr-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                </div>
                                    <div class="flex-grow">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="email" id="email" x-model="email"
                                               class="input-animated w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:input-focused text-sm text-gray-900 bg-white"
                                               :class="{ 'active': editMode }"
                                               :readonly="!editMode"
                                       placeholder="Email">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                    </div>
                            </div>

                                <!-- Telepon -->
                                <div class="data-point p-3 flex items-start" :class="{ 'bg-[#0F4C81]/5 editing': editMode }">
                                    <div class="data-point-icon mr-3 flex-shrink-0" style="width: 40px; height: 40px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                    </svg>
                                </div>
                                    <div class="flex-grow">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                                        <input type="text" name="phone" id="phone" x-model="phone"
                                               class="input-animated w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:input-focused text-sm text-gray-900 bg-white"
                                               :class="{ 'active': editMode }"
                                               :readonly="!editMode"
                                       placeholder="Nomor Telepon">
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <div x-show="!phone" class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded-md">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-4 w-4 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-2">
                                            <p class="text-xs text-amber-700">
                                                <strong>Penting:</strong> Nomor telepon wajib diisi untuk dapat mengajukan peminjaman barang.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>

                                <div class="mt-6 flex justify-end space-x-3">
                                    <button x-show="!editMode" @click.prevent="enableEditMode" type="button" class="btn-edit btn-ripple rounded-lg px-3 py-1.5 flex items-center text-xs shadow-sm hover:shadow">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                        Edit Profile
                                    </button>
                                    <div x-show="editMode" class="flex space-x-3">
                                        <button type="button" @click.prevent="cancelEdit" class="btn-cancel rounded-lg px-3 py-1.5 text-xs shadow-sm hover:shadow">
                                            Batal
                                        </button>
                                        <button type="submit" class="btn-save btn-ripple rounded-lg px-4 py-1.5 text-xs shadow-sm hover:shadow">
                                            Simpan
                                        </button>
                            </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Script untuk profile page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cek URL parameter dan session flash messages
        function checkMessagesOnLoad() {
            // Cek session flash message
            @if(session('success'))
                window.showToast('success', "{{ session('success') }}");
            @endif

            @if(session('error'))
                window.showToast('error', "{{ session('error') }}");
            @endif

            // Cek URL params
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'success') {
                const message = urlParams.get('message') || 'Data berhasil disimpan';
                window.showToast('success', message);

                // Bersihkan URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }

        // Panggil fungsi cek messages saat halaman dimuat
        checkMessagesOnLoad();

        // Menangani klik pada overlay upload foto
        const uploadTrigger = document.getElementById('upload-trigger');
        const photoUpload = document.getElementById('photo-upload');
        const uploadButton = document.getElementById('upload-photo-btn');
        const photoForm = document.getElementById('photo-form');
        const profileForm = document.querySelector('form[action="{{ route('user.update-profile') }}"]');

        // Menangani submit form profil
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.textContent;
                    submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...';
                    submitBtn.disabled = true;

                    // Store a flag in sessionStorage
                    sessionStorage.setItem('profile_updated', 'true');
                }
            });
        }

        // Check if we need to show a toast for profile update
        if (sessionStorage.getItem('profile_updated') === 'true') {
            window.showToast('success', 'Data profil berhasil diperbarui');
            sessionStorage.removeItem('profile_updated'); // Clear the flag
        }

        if (uploadTrigger && photoUpload) {
            uploadTrigger.addEventListener('click', function() {
                photoUpload.click();
            });
        }

        // Fungsi untuk preview gambar sebelum upload
        window.previewImage = function(event) {
            const profileImage = document.getElementById('profile-image');
            const file = event.target.files[0];

            if (file) {
                // Validasi ukuran file (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    window.showToast('error', 'Ukuran file terlalu besar. Maksimal 2MB.');
                    photoUpload.value = '';
                    return false;
                }

                // Validasi tipe file
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    window.showToast('error', 'Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
                    photoUpload.value = '';
                    return false;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;

                    // Tampilkan overlay loading
                    const loadingOverlay = document.createElement('div');
                    loadingOverlay.className = 'absolute inset-0 bg-[#0F4C81] bg-opacity-40 flex items-center justify-center rounded-full';
                    loadingOverlay.innerHTML = `
                        <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    `;

                    const photoContainer = document.querySelector('.photo-upload-container');
                    photoContainer.style.position = 'relative';
                    photoContainer.appendChild(loadingOverlay);

                    // Store a flag for photo update
                    sessionStorage.setItem('photo_updated', 'true');

                    // Otomatis submit form setelah preview
                    setTimeout(() => {
                        photoForm.submit();
                    }, 800);
                }
                reader.readAsDataURL(file);
            }
        };

        // Check if we need to reload page for profile photo display
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('photo_updated') === '1') {
            // Force reload the page to ensure new photo is displayed from server
            window.history.replaceState({}, document.title, window.location.pathname);

            // Show success toast for photo update if the flag is set
            if (sessionStorage.getItem('photo_updated') === 'true') {
                window.showToast('success', 'Foto profil berhasil diperbarui');
                sessionStorage.removeItem('photo_updated'); // Clear the flag
            }
        }

        // Efek parallax sederhana untuk background IoT
        const iotElements = document.querySelector('.iot-elements');

        if (iotElements) {
            document.addEventListener('mousemove', function(e) {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;

                // Gerakan parallax terbatas
                const moveX = (x - 0.5) * 20; // max 10px gerakan
                const moveY = (y - 0.5) * 20; // max 10px gerakan

                iotElements.style.transform = `translate(${moveX}px, ${moveY}px)`;
            });
        }
    });
</script>
@endpush
