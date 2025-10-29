(function () {
    console.log('📡 Memulai inisialisasi Laravel Echo...');

    function initEcho() {
        if (typeof Echo === 'undefined') {
            console.error('❌ Laravel Echo belum dimuat! Pastikan urutan script benar.');
            return;
        }

        // Inisialisasi Echo
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: window.PUSHER_KEY,
            cluster: window.PUSHER_CLUSTER,
            forceTLS: true,
        });

        console.log('✅ Laravel Echo berhasil diinisialisasi!');

        const userId = window.USER_ID;
        if (!userId) {
            console.warn('⚠️ USER_ID tidak ditemukan, channel private tidak akan diinisialisasi.');
            return;
        }

        console.log(`📡 Mendengarkan channel: notifikasi.${userId}`);

        const notifSound = document.getElementById('notifSound');
        const notifBadge = document.getElementById('notifBadge');

        // Dengarkan event broadcast
        window.Echo.private(`notifikasi.${userId}`)
            .listen('.notifikasi-event', (data) => {
                console.log('🔔 Notifikasi Baru:', data);

                if (notifSound) notifSound.play().catch(err => console.warn("⚠️ Tidak bisa memutar suara:", err));

                // Update badge
                updateBadgeCount();

                const list = document.getElementById('notif-list');
                if (list) {
                    const item = document.createElement('li');
                    item.classList.add('list-group-item');
                    item.textContent = `${data.user}: ${data.message.content}`;
                    list.prepend(item);
                }
            });

        function updateBadgeCount() {
            console.log('🟡 Memulai update badge count...');

            fetch(window.NOTIF_COUNT_URL)
                .then(res => {
                    console.log('🟢 Response diterima:', res);
                    if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    console.log('📦 Data JSON diterima:', data);

                    const notifBadge = document.getElementById('notifBadge');
                    if (!notifBadge) {
                        console.warn('⚠️ Elemen notifBadge tidak ditemukan di DOM.');
                        return;
                    }

                    notifBadge.innerText = data.count;
                    console.log(`🔢 Jumlah notifikasi baru: ${data.count}`);

                    if (data.count > 0) {
                        notifBadge.classList.add('bg-danger');
                        notifBadge.classList.remove('bg-secondary');
                    } else {
                        notifBadge.classList.remove('bg-danger');
                        notifBadge.classList.add('bg-secondary');
                    }

                    notifBadge.style.transform = 'scale(1.3)';
                    setTimeout(() => notifBadge.style.transform = 'scale(1)', 300);

                    console.log('✅ Badge berhasil diperbarui!');
                })
                .catch(err => {
                    console.error('❌ Gagal update badge:', err);
                });
        }

    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initEcho, 1000);
    } else {
        document.addEventListener('DOMContentLoaded', () => setTimeout(initEcho, 500));
    }
})();
