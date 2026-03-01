// Dashboard JavaScript untuk Lab IoT Vokasi UB

document.addEventListener('DOMContentLoaded', function() {
    // Periksa deadline peminjaman untuk mengubah status borrowed menjadi overdue jika melewati deadline
    document.querySelectorAll('.borrow-item').forEach(function(item) {
        const deadlineElement = item.querySelector('.deadline-normal');
        if (deadlineElement) {
            const deadlineText = deadlineElement.textContent.trim();
            const deadlineMatch = deadlineText.match(/Deadline:\s+(\d{1,2}\s+\w{3}\s+\d{4})/);
            
            if (deadlineMatch) {
                const deadlineDate = new Date(deadlineMatch[1]);
                const currentDate = new Date();
                
                // Jika melewati deadline, ubah tampilan ke overdue
                if (currentDate > deadlineDate) {
                    // Ubah status badge hanya jika bukan peminjaman aktif yang status-nya "borrowed"
                    const statusBadge = item.querySelector('.badge-status-borrowed, [data-status="borrowed"]');
                    
                    // Hanya lanjutkan jika badge tidak eksplisit menunjukkan status "borrowed"
                    // Ini mencegah JavaScript mengubah tampilan badge "Sedang Dipinjam" menjadi merah
                    if (statusBadge && !statusBadge.textContent.trim().includes("Sedang Dipinjam")) {
                        statusBadge.classList.remove('badge-status-borrowed');
                        statusBadge.classList.add('badge-status-overdue');
                        statusBadge.setAttribute('data-status', 'overdue');
                        
                        // Update ikon
                        const icon = statusBadge.querySelector('i');
                        if (icon) {
                            icon.setAttribute('data-lucide', 'alert-circle');
                            icon.classList.remove('icon-status-borrowed');
                            icon.classList.add('icon-status-overdue');
                        }
                        
                        // Update teks status
                        const statusText = statusBadge.textContent.trim();
                        if (!statusText.includes('MELEWATI DEADLINE')) {
                            statusBadge.innerHTML = statusBadge.innerHTML + ' (MELEWATI DEADLINE)';
                        }
                        
                        // Update tampilan deadline
                        deadlineElement.classList.remove('deadline-normal');
                        deadlineElement.classList.add('deadline-overdue');
                        
                        const deadlineIcon = deadlineElement.querySelector('i');
                        if (deadlineIcon) {
                            deadlineIcon.classList.remove('icon-status-borrowed');
                            deadlineIcon.classList.add('icon-status-overdue');
                        }
                        
                        // Refresh icon
                        lucide.createIcons();
                    }
                }
            }
        }
    });
    
    // Inisialisasi Lucide icons
    lucide.createIcons();
    
    // Counter animation dengan efek slot machine
    initCounterAnimation();
    
    // Inisialisasi carousel
    initCarousel();
    
    // FAB position adjustment
    initFABPosition();
});

// Fungsi untuk inisialisasi animasi counter
function initCounterAnimation() {
    const counterNumbers = document.querySelectorAll('.counter-number');
    
    counterNumbers.forEach(counter => {
        const targetValue = parseInt(counter.getAttribute('data-target'));
        const duration = 3000; // durasi total animasi
        
        // Jika nilai target adalah 0, langsung tampilkan 0
        if (targetValue === 0) {
            counter.textContent = '0';
            return;
        }
        
        // Mengubah nilai target menjadi string untuk memproses per digit
        const targetStr = targetValue.toString();
        const digitCount = targetStr.length;
        
        // Bersihkan konten counter dan buat wrapper untuk menjaga alignment
        counter.innerHTML = '';
        const digitsWrapper = document.createElement('div');
        digitsWrapper.className = 'digits-wrapper flex';
        counter.appendChild(digitsWrapper);
        
        // Buat wadah untuk setiap digit
        for (let i = 0; i < digitCount; i++) {
            const digitElement = document.createElement('div');
            digitElement.className = 'digit-container inline-block overflow-hidden text-center';
            digitElement.style.height = '1.2em';
            digitElement.style.position = 'relative';
            digitElement.setAttribute('data-target-digit', targetStr[i]);
            
            // Tambahkan elemen digit ke wrapper
            digitsWrapper.appendChild(digitElement);
            
            // Mulai animasi untuk digit ini
            animateDigit(digitElement, parseInt(targetStr[i]), i, duration);
        }
    });
}

// Fungsi untuk menganimasi digit
function animateDigit(element, finalDigit, position, duration) {
    // Buat strip angka
    const strip = document.createElement('div');
    strip.className = 'digit-strip';
    strip.style.position = 'relative';
    strip.style.transition = 'transform 0.1s linear';
    
    // Tunda animasi berdasarkan posisi (dari kiri ke kanan)
    const delay = 200 + (position * 200);
    
    // Jumlah iterasi sebelum mencapai angka final
    const iterationCount = 30;
    
    // Buat set angka untuk slot machine
    for (let j = 0; j <= iterationCount; j++) {
        const digitSpan = document.createElement('div');
        digitSpan.style.height = '1.2em';
        digitSpan.style.display = 'flex';
        digitSpan.style.justifyContent = 'center';
        digitSpan.style.alignItems = 'center';
        
        // Generate angka acak di awal, lalu angka yang sesuai di akhir
        let displayDigit;
        
        if (j < iterationCount - 10) {
            // Fase 1: Angka benar-benar acak
            displayDigit = Math.floor(Math.random() * 10);
        } else if (j < iterationCount) {
            // Fase 2: Mulai mendekati angka target
            const remaining = iterationCount - j;
            // Gunakan modulo untuk secara bertahap mendekati target
            displayDigit = (finalDigit + remaining) % 10;
        } else {
            // Fase 3: Angka final yang benar
            displayDigit = finalDigit;
        }
        
        digitSpan.textContent = displayDigit;
        strip.appendChild(digitSpan);
    }
    
    // Tambahkan strip ke container
    element.appendChild(strip);
    
    // Posisi awal: tersembunyi di atas
    strip.style.transform = 'translateY(0)';
    
    // Animasi dalam beberapa tahap untuk efek slot machine
    setTimeout(() => {
        // Fase spinning cepat
        strip.style.transition = `transform ${(duration - delay) * 0.7 / 1000}s cubic-bezier(0.1, 0.7, 0.1, 1)`;
        // Berputar ke posisi mendekati target
        strip.style.transform = `translateY(-${(iterationCount - 10) * 1.2}em)`;
        
        setTimeout(() => {
            // Fase perlambatan
            strip.style.transition = `transform ${(duration - delay) * 0.3 / 1000}s cubic-bezier(0.1, 0, 0.3, 1)`;
            // Berhenti tepat di angka target
            strip.style.transform = `translateY(-${iterationCount * 1.2}em)`;
        }, (duration - delay) * 0.7);
    }, delay);
}

// Fungsi untuk inisialisasi carousel
function initCarousel() {
    const track = document.getElementById('carousel-track');
    if (track) {
        const slides = Array.from(track.children);
        
        if (slides.length > 1) {
            // Penggandaan slide untuk simulasi carousel tak terbatas
            slides.forEach(slide => {
                const clone = slide.cloneNode(true);
                track.appendChild(clone);
            });
            
            // Update slides array setelah kloning
            const allSlides = Array.from(track.children);
            const slideCount = allSlides.length;
            const slideWidth = allSlides[0].getBoundingClientRect().width;
            const slideMargin = 16; // gap antar slide
            
            let currentPosition = 0;
            let animationId;
            let isPaused = false;
            
            // Fungsi untuk menggerakkan track secara terus menerus
            const animateTrack = () => {
                if (!isPaused) {
                    currentPosition += 0.65; // Kecepatan diatur ulang untuk keseimbangan yang lebih baik
                    
                    // Reset posisi jika sudah melewati setengah slide original
                    if (currentPosition >= (slides.length * (slideWidth + slideMargin))) {
                        currentPosition = 0;
                        track.style.transition = 'none';
                        track.style.transform = `translateX(0px)`;
                        
                        // Trigger reflow untuk menghindari visual skip
                        track.offsetHeight;
                        
                        // Hidupkan kembali transisi setelah reset
                        setTimeout(() => {
                            track.style.transition = 'transform 0.5s linear';
                        }, 50);
                    } else {
                        track.style.transform = `translateX(-${currentPosition}px)`;
                    }
                }
                
                animationId = requestAnimationFrame(animateTrack);
            };
            
            // Mulai animasi
            track.style.transition = 'transform 0.5s linear';
            animationId = requestAnimationFrame(animateTrack);
            
            // Pause pada hover
            allSlides.forEach(slide => {
                slide.addEventListener('mouseenter', () => {
                    isPaused = true;
                });
                
                slide.addEventListener('mouseleave', () => {
                    isPaused = false;
                });
            });
            
            // Cleanup jika diperlukan
            window.addEventListener('beforeunload', () => {
                cancelAnimationFrame(animationId);
            });
        }
    }
}

// Fungsi untuk mengatur posisi tombol FAB
function initFABPosition() {
    const fab = document.querySelector('.fab');
    if (fab) {
        // Adjust position on scroll to avoid footer
        window.addEventListener('scroll', () => {
            const scrollPosition = window.scrollY;
            const windowHeight = window.innerHeight;
            const documentHeight = document.body.scrollHeight;
            const footerHeight = 80; // Approximate footer height
            
            // If near bottom of page, adjust FAB position
            if (scrollPosition + windowHeight > documentHeight - footerHeight - 100) {
                fab.style.bottom = `${footerHeight + 20}px`;
            } else {
                fab.style.bottom = '2rem';
            }
        });
    }
} 