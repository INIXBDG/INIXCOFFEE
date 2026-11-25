/**
 * Global Notifications (Pusher), SweetAlert Cookie Toasts, Logout Confirmation & Service Worker
 */
(function () {
    function getCookie(name) {
        const matches = document.cookie.match(
            new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)')
        );
        return matches ? decodeURIComponent(matches[1].replace(/\+/g, ' ')) : undefined;
    }

    function initCookieAlerts() {
        if (typeof Swal === 'undefined') return;

        const successAlert = getCookie('swal_success');
        const errorAlert = getCookie('swal_error');

        if (successAlert) {
            document.cookie = 'swal_success=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            setTimeout(function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: successAlert,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    allowOutsideClick: false,
                });
            }, 300);
        } else if (errorAlert) {
            document.cookie = 'swal_error=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            setTimeout(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorAlert,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    allowOutsideClick: false,
                });
            }, 300);
        }
    }

    function initLogoutConfirm() {
        const logoutLink = document.getElementById('logout-link');
        if (!logoutLink || logoutLink.dataset.boundLogout === 'true') return;
        logoutLink.dataset.boundLogout = 'true';

        logoutLink.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof Swal === 'undefined') {
                document.getElementById('logout-form')?.submit();
                return;
            }
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Anda akan keluar dari aplikasi',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, keluar',
                cancelButtonText: 'Batal',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown animate__faster',
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp animate__faster',
                },
            }).then(function (result) {
                if (result.isConfirmed) {
                    document.getElementById('logout-form')?.submit();
                }
            });
        });
    }

    function initPusherNotifications() {
        if (window.__inixPusherInitialized) return;
        const pusherKey =
            window.PUSHER_APP_KEY ||
            document.querySelector('meta[name="pusher-app-key"]')?.content;
        const pusherCluster =
            window.PUSHER_APP_CLUSTER ||
            document.querySelector('meta[name="pusher-app-cluster"]')?.content ||
            'ap1';
        const userId =
            window.USER_ID ||
            document.querySelector('meta[name="user-id"]')?.content;

        if (!pusherKey || !userId || typeof window.Pusher === 'undefined') return;
        window.__inixPusherInitialized = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const pusher = new window.Pusher(pusherKey, {
            cluster: pusherCluster,
            forceTLS: true,
            authEndpoint: '/pusher/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            },
        });

        const channel = pusher.subscribe(`private-notifikasi.${userId}`);

        channel.bind('notifikasi-event', function (data) {
            const toast = document.createElement('div');
            toast.innerHTML = `
                <div class="rt-toast" style="position:fixed;top:90px;left:20px;z-index:99999;background:#10b981;color:white;padding:18px 32px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.3);font-family:system-ui;min-width:320px;animation:slideIn 0.5s ease;">
                    <div style="font-weight:700;font-size:16px;">Notifikasi Baru!</div>
                    <div class="rt-toast-text" style="margin-top:6px;font-size:14px;opacity:0.9;"></div>
                </div>`;
            toast.querySelector('.rt-toast-text').textContent =
                data.message?.message?.tipe || 'Ada pemberitahuan baru';
            document.body.appendChild(toast);

            setTimeout(function () {
                toast.style.transition = 'opacity 0.5s ease';
                toast.style.opacity = '0';
                setTimeout(function () {
                    toast.remove();
                }, 500);
            }, 3000);

            document.getElementById('notifSound')?.play().catch(function () {});

            const badge = document.getElementById('notifBadge');
            if (badge) {
                badge.textContent = (parseInt(badge.textContent) || 0) + 1;
                badge.classList.add('animate__animated', 'animate__bounceIn');
                setTimeout(function () {
                    badge.classList.remove('animate__bounceIn');
                }, 1000);
            }

            const modalEl = document.getElementById('notificationModal');
            const lazyContainer = modalEl?.querySelector('.lazy-notifications-container[data-lazy-url]');
            const modalBody = lazyContainer || modalEl?.querySelector('.modal-body');

            if (modalBody) {
                const isModalOpen = modalEl?.classList.contains('show');
                if (lazyContainer && !isModalOpen) {
                    // Tandai agar data terbaru otomatis diambil saat modal dibuka kembali
                    lazyContainer.dataset.loaded = 'false';
                } else {
                    const originalHtml = modalBody.innerHTML;
                    modalBody.innerHTML =
                        '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat notifikasi terbaru...</p></div>';

                    fetch('/notifications/modal-content')
                        .then(function (res) {
                            if (!res.ok) throw new Error('Network response was not ok');
                            return res.text();
                        })
                        .then(function (html) {
                            modalBody.innerHTML = html;
                            if (lazyContainer) {
                                lazyContainer.dataset.loaded = 'true';
                            }
                        })
                        .catch(function () {
                            modalBody.innerHTML = originalHtml;
                            const item = document.createElement('div');
                            item.className = 'notification p-3 border-bottom animate__animated animate__fadeIn';
                            item.innerHTML =
                                '<p class="mb-1 fw-bold text-danger">Baru!</p><p class="mb-1"><strong class="n-user"></strong></p><p class="mb-1 n-tipe"></p><small class="text-muted">Baru saja</small>';
                            item.querySelector('.n-user').textContent = data.message?.user || 'System';
                            item.querySelector('.n-tipe').textContent = data.message?.message?.tipe || 'Notifikasi';
                            modalBody.insertBefore(item, modalBody.firstChild);
                        });
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initCookieAlerts();
            initLogoutConfirm();
            initPusherNotifications();
        });
    } else {
        initCookieAlerts();
        initLogoutConfirm();
        initPusherNotifications();
    }
})();
