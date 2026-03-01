/**
 * Script untuk menampilkan status pengiriman email di halaman landing
 * Hanya berjalan di landing page dan tidak mengganggu halaman lain
 */

// Fungsi untuk mengecek status pengiriman email hari ini
async function checkEmailStatus() {
    try {
        // Gunakan cache sederhana di localStorage untuk menghindari request berlebihan
        const lastCheckKey = 'email_status_last_check';
        const lastCheck = localStorage.getItem(lastCheckKey);
        const now = new Date().getTime();
        
        // Hanya cek status setiap 15 menit
        if (lastCheck && (now - parseInt(lastCheck)) < 15 * 60 * 1000) {
            console.log('Status email sudah dicek dalam 15 menit terakhir, melewati...');
            return;
        }
        
        // Catat waktu pengecekan
        localStorage.setItem(lastCheckKey, now.toString());
        
        // Buat request ke endpoint status
        const response = await fetch('/api/email-status', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        });
        
        if (!response.ok) {
            throw new Error('Gagal mendapatkan status email');
        }
        
        const data = await response.json();
        console.log('Status email:', data);
        
        // Jika email berhasil dikirim hari ini, tampilkan notifikasi kecil
        // Ini hanya untuk tujuan debugging/admin
        if (data.sent_today && document.getElementById('email-status-indicator')) {
            const indicator = document.getElementById('email-status-indicator');
            indicator.classList.remove('bg-yellow-500');
            indicator.classList.add('bg-green-500');
            
            if (document.getElementById('email-status-text')) {
                document.getElementById('email-status-text').textContent = 
                    `Email terkirim hari ini: ${new Date(data.sent_at).toLocaleTimeString()}`;
            }
        }
    } catch (error) {
        console.error('Error mengecek status email:', error);
    }
}

// Jalankan pengecekan status saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    // Pastikan kita berada di landing page
    if (window.location.pathname === '/') {
        // Tunggu semua elemen dimuat
        setTimeout(() => {
            checkEmailStatus();
        }, 1000);
    }
}); 