/**
 * Scheduler Runner - Script untuk menjalankan Laravel Scheduler secara otomatis
 * Ini akan dijalankan di semua halaman aplikasi untuk memastikan scheduler selalu berjalan
 */

class SchedulerRunner {
    constructor() {
        // Ubah interval menjadi 30 menit (1.800.000 ms)
        this.interval = 30 * 60 * 1000;
        this.isRunning = false;
        this.lastRun = null;
        
        // Cek apakah sudah pernah dijalankan sebelumnya di sesi ini
        this.sessionStorageKey = 'scheduler_last_run_time';
        this.minInterval = 15 * 60 * 1000; // 15 menit dalam milidetik
    }

    /**
     * Mulai scheduler runner
     */
    start() {
        if (this.isRunning) return;
        
        this.isRunning = true;
        
        // Cek apakah scheduler sudah dijalankan di sesi browser ini
        const lastRunTime = sessionStorage.getItem(this.sessionStorageKey);
        const now = new Date().getTime();
        
        if (lastRunTime && (now - parseInt(lastRunTime)) < this.minInterval) {
            // Jika sudah dijalankan dalam 15 menit terakhir di sesi ini, jangan jalankan lagi
            console.log(`Scheduler tidak perlu dijalankan, sudah berjalan ${Math.round((now - parseInt(lastRunTime))/1000/60)} menit yang lalu`);
            
            // Tetapkan interval untuk menjalankan kembali nanti
            setTimeout(() => this.runScheduler(), this.minInterval);
        } else {
            // Jalankan scheduler segera jika belum dijalankan dalam 15 menit terakhir
            this.runScheduler();
        }
        
        // Set interval untuk menjalankan scheduler secara berkala (30 menit)
        setInterval(() => this.runScheduler(), this.interval);
        
        console.log('✓ Scheduler runner started - aktif setiap 30 menit');
    }

    /**
     * Jalankan scheduler dengan memanggil endpoint yang sudah dibuat
     */
    runScheduler() {
        console.log('Menjalankan scheduler...', new Date().toLocaleTimeString());
        
        // Tambahkan timestamp untuk menghindari cache
        const timestamp = new Date().getTime();
        sessionStorage.setItem(this.sessionStorageKey, timestamp.toString());
        
        fetch(`/run-scheduler?_=${timestamp}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(response => response.json())
        .then(data => {
            this.lastRun = new Date();
            console.log('Scheduler executed:', data);
        })
        .catch(error => {
            console.error('Failed to run scheduler:', error);
        });
    }
}

// Buat instance dan jalankan saat dokumen sudah siap
document.addEventListener('DOMContentLoaded', () => {
    // Skip jika ini landing page (sudah ditangani oleh landing page controller)
    if (window.location.pathname === '/') {
        console.log('Skip scheduler runner on landing page - handled by LandingPageController');
        return;
    }
    
    // Tambahkan delay kecil untuk memastikan semua resource dimuat
    setTimeout(() => {
        const scheduler = new SchedulerRunner();
        scheduler.start();
    }, 1000);
}); 