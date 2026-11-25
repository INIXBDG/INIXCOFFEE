<div class="lazy-notifications-container" data-lazy-url="{{ route('notifications.modal-content') }}" data-loaded="false">
    <div class="text-center p-4 text-muted">
        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        <span class="ms-2">Memuat notifikasi...</span>
    </div>
</div>
<script>
    (function () {
        if (window.__inixNotifModalLazyBound) return;
        window.__inixNotifModalLazyBound = true;

        function loadModalNotifications(modalEl) {
            const container = modalEl ? modalEl.querySelector('.lazy-notifications-container[data-lazy-url]') : null;
            if (!container || container.dataset.loaded === 'true' || container.dataset.loading === 'true') return;

            container.dataset.loading = 'true';
            container.innerHTML =
                '<div class="text-center p-4 text-muted"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><span class="ms-2">Memuat notifikasi...</span></div>';

            fetch(container.dataset.lazyUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.text();
                })
                .then(function (html) {
                    container.innerHTML = html.trim()
                        ? html
                        : '<p class="text-muted text-center my-3 mb-0">Tidak ada notifikasi baru.</p>';
                    container.dataset.loaded = 'true';
                })
                .catch(function () {
                    container.innerHTML =
                        '<p class="text-danger text-center my-3 mb-0">Gagal memuat notifikasi. Silakan coba lagi.</p>';
                })
                .finally(function () {
                    container.dataset.loading = 'false';
                });
        }

        document.addEventListener('show.bs.modal', function (event) {
            if (event.target && event.target.id === 'notificationModal') {
                loadModalNotifications(event.target);
            }
        });
    })();
</script>
