/**
 * Page Script: Home / Menus (resources/views/layouts/menus.blade.php)
 */

// 1. Card Ordering & Masonry Layout
document.addEventListener('DOMContentLoaded', function () {
    const savedCardOrders = window.savedCardOrders || {};
    if (savedCardOrders && Object.keys(savedCardOrders).length > 0) {
        document.querySelectorAll('.dashboard-section-row').forEach(function (row) {
            const orderList = savedCardOrders[row.getAttribute('data-section')];
            if (Array.isArray(orderList) && orderList.length > 0) {
                const cardMap = new Map();
                Array.from(row.querySelectorAll(':scope > [data-card-id]')).forEach(function (el) {
                    cardMap.set(el.getAttribute('data-card-id'), el);
                });
                orderList.forEach(function (cardId) {
                    if (cardMap.has(cardId)) {
                        row.appendChild(cardMap.get(cardId));
                        cardMap.delete(cardId);
                    }
                });
                cardMap.forEach(function (el) {
                    row.appendChild(el);
                });
            }
        });
    }

    const container = document.querySelector('#pills-home .masonry-container');
    if (!container) return;
    const items = Array.from(container.querySelectorAll('.masonry-item'));
    const mobileLayout = window.matchMedia('(max-width: 768px)');

    function arrangeSections() {
        container.replaceChildren();
        if (mobileLayout.matches) {
            items.forEach(function (item) {
                container.appendChild(item);
            });
            return;
        }
        const columns = [document.createElement('div'), document.createElement('div')];
        columns.forEach(function (column) {
            column.classList.add('masonry-column');
        });
        items.forEach(function (item, index) {
            columns[index % 2].appendChild(item);
        });
        columns.forEach(function (column) {
            container.appendChild(column);
        });
    }

    arrangeSections();
    mobileLayout.addEventListener('change', arrangeSections);
});

// 2. WebPush Toggle Subscription
const publicVapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const subscribeRoute = '/webpush/subscribe';
const unsubscribeRoute = '/webpush/unsubscribe';
const statusRoute = '/webpush/status';
const swPath = '/service-worker.js';
let currentSubscription = null;

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        throw new Error('Browser tidak mendukung Service Worker');
    }
    const existingRegistration = await navigator.serviceWorker.getRegistration(swPath);
    if (existingRegistration) {
        return existingRegistration;
    }
    return await navigator.serviceWorker.register(swPath);
}

async function toggleSubscription() {
    const btn = document.getElementById('toggle-subscribe-btn');
    const statusEl = document.getElementById('status');

    if (!publicVapidKey) {
        alert('Error: VAPID Public Key tidak ditemukan di meta tag!');
        return;
    }
    if (!window.isSecureContext) {
        alert('Error: Web Push memerlukan HTTPS atau localhost!');
        return;
    }

    try {
        if (statusEl) statusEl.textContent = 'Memproses...';
        if (btn) btn.disabled = true;

        const registration = await registerServiceWorker();
        const statusRes = await fetch(statusRoute, {
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (!statusRes.ok) throw new Error('Status HTTP: ' + statusRes.status);
        const statusData = await statusRes.json();
        if (!statusData.success) throw new Error('Respon status tidak valid');

        const isSubscribedOnServer = statusData.subscribed;
        currentSubscription = await registration.pushManager.getSubscription();

        if (isSubscribedOnServer) {
            if (statusEl) statusEl.textContent = 'Menonaktifkan...';
            const res = await fetch(unsubscribeRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    endpoint: currentSubscription?.endpoint || '',
                }),
            });
            const result = await res.json();
            if (res.ok && result.success) {
                if (currentSubscription) {
                    await currentSubscription.unsubscribe();
                }
                currentSubscription = null;
                if (btn) {
                    btn.textContent = 'Aktifkan Notifikasi';
                    btn.style.background = '#0d6efd';
                }
                if (statusEl) statusEl.textContent = 'Notifikasi dinonaktifkan';
            } else {
                throw new Error(result.message || 'Gagal unsubscribe di server');
            }
        } else {
            if (statusEl) statusEl.textContent = 'Meminta izin...';
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                if (statusEl) statusEl.textContent = 'Izin ditolak user';
                if (btn) btn.disabled = false;
                return;
            }
            if (statusEl) statusEl.textContent = 'Membuat subscription...';
            currentSubscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicVapidKey),
            });
            if (statusEl) statusEl.textContent = 'Menyimpan ke server...';
            const res = await fetch(subscribeRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify(currentSubscription.toJSON()),
            });
            const result = await res.json();
            if (res.ok && result.success) {
                if (btn) {
                    btn.textContent = 'Nonaktifkan Notifikasi';
                    btn.style.background = '#dc3545';
                }
                if (statusEl) statusEl.textContent = 'Notifikasi aktif';
            } else {
                await currentSubscription.unsubscribe();
                currentSubscription = null;
                throw new Error(result.message || 'Gagal menyimpan subscription');
            }
        }
    } catch (err) {
        console.error('Toggle Error:', err);
        if (statusEl) statusEl.textContent = 'Error: ' + err.message;
        alert('Error: ' + err.message);
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function updateButtonStatus() {
    const btn = document.getElementById('toggle-subscribe-btn');
    const statusEl = document.getElementById('status');
    if (!btn) return;
    try {
        const res = await fetch(statusRoute, {
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (!res.ok) throw new Error('Gagal mengambil status');
        const data = await res.json();
        if (data.success && data.subscribed) {
            btn.textContent = 'Nonaktifkan Notifikasi';
            btn.style.background = '#dc3545';
            if (statusEl) statusEl.textContent = 'Notifikasi aktif';
        } else {
            btn.textContent = 'Aktifkan Notifikasi';
            btn.style.background = '#0d6efd';
            if (statusEl) statusEl.textContent = 'Notifikasi belum aktif';
        }
    } catch (e) {
        if (statusEl) statusEl.textContent = 'Status tidak diketahui';
    }
}

window.addEventListener('load', updateButtonStatus);

// 3. Dashboard Tab Loader, Absensi Camera & Uptime Monitoring
let chartDataCache = null;
let isLoaded = false;
let isLoading = false;
let uptimeLoadStarted = false;

const services = {
    coffee: 'https://inixcoffee.inixindobdg.co.id/',
    latte: 'https://latte.inixindobdg.co.id/',
};

function getMenusConfig() {
    return window.menusConfig || {};
}

function showPane(selector, callback) {
    $('#pills-tabContent > .tab-pane.show')
        .fadeOut(100)
        .promise()
        .done(function () {
            $('#pills-tabContent > .tab-pane').removeClass('show active');
            $(selector).stop(true, true).fadeIn(100).addClass('show active');
            if (callback) callback();
        });
}

function finishLoading() {
    isLoading = false;
    $('#loadingModal').modal('hide');
    $('#pills-dashboard-tab').prop('disabled', false);
}

function loadDashboard() {
    if (isLoading) return Promise.resolve({ status: 'busy' });
    if (isLoaded) {
        showPane('#pills-dashboard');
        return Promise.resolve({ status: 'cached' });
    }
    isLoading = true;
    $('#pills-dashboard-tab').prop('disabled', true);
    $('#loadingModal').modal('show');

    return new Promise(function (resolve, reject) {
        const $contentContainer = $('#dashboard-content');
        $.ajax({
            url: '/partials/dashboard',
            type: 'GET',
            dataType: 'html',
        })
            .done(function (html) {
                $contentContainer.html(html);
                import('./dashboard.js')
                    .then(function () {
                        if (typeof window.initializeYearlySales === 'function') {
                            window.initializeYearlySales();
                        }
                        isLoaded = true;
                        finishLoading();
                        showPane('#pills-dashboard');
                        resolve({ status: 'loaded' });
                    })
                    .catch(function (err) {
                        console.error('Failed loading dashboard chunk:', err);
                        $contentContainer.append('<p>Terjadi kesalahan saat memuat dashboard.</p>');
                        finishLoading();
                        reject(new Error('getScript_failed'));
                    });
            })
            .fail(function () {
                $contentContainer.html('<p>Terjadi kesalahan saat memuat dashboard.</p>');
                finishLoading();
                reject(new Error('ajax_failed'));
            });
    });
}

function cekjabatan() {
    const jabatan = getMenusConfig().jabatan;
    if (jabatan === 'Direktur' || jabatan === 'Direktur Utama') {
        $('#pills-dashboard-tab').prop('checked', true);
        loadDashboard().catch(function (err) {
            console.error(err);
        });
    } else {
        showPane('#pills-home');
        setTimeout(function () {
            $('#loadingModal').modal('hide');
            $('#modalPemberitahuan').modal('show');
        }, 1000);
    }
}

function cekip() {
    const cfg = getMenusConfig();
    $.ajax({
        url: cfg.urls?.cekip || '/cek-ip',
        type: 'GET',
        success: function (response) {
            const data = response.success;
            if (data === 'Absen Normal') {
                $('#inhouse').prop('disabled', true);
                $('#spj').prop('disabled', true);
                $('#normal').prop('checked', true).prop('disabled', false);
            } else if (data === 'Absen Luar') {
                $('#normal').prop('disabled', true);
                $('#inhouse').prop('disabled', false);
                $('#spj').prop('disabled', false).prop('checked', true);
            } else {
                $('#normal').prop('disabled', false);
                $('#inhouse').prop('disabled', false);
                $('#spj').prop('disabled', false);
            }
            $('#absen').show();
        },
        error: function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.error) || 'Gagal memeriksa lokasi absen');
        },
    });
}

function hitungShift(jabatan, now, tipe) {
    const jam = now.toTimeString().split(' ')[0];
    const hari = now.getDay();
    if (jabatan !== 'Office Boy') return 1;
    if (tipe === 'masuk') {
        if (hari === 6 || hari === 0) {
            if (jam >= '03:00:00' && jam < '08:00:00') return 1;
            if (jam >= '08:00:00' && jam < '23:00:00') return 2;
            return 'Tidak Sesuai Shift';
        }
        if (jam >= '03:00:00' && jam < '10:00:00') return 1;
        if (jam >= '14:00:00' && jam < '23:00:00') return 2;
        return 'Tidak Sesuai Shift';
    }
    if (jam >= '14:00:00' && jam <= '23:59:59') return 1;
    if (jam >= '00:00:00' && jam < '09:00:00') return 2;
    return 'Tidak Sesuai Shift';
}

function updateSummary(prefix, period, data) {
    const uptimePerc = parseFloat(data.overall) || 0;
    const downtimePerc = (100 - uptimePerc).toFixed(2);
    const hasData = uptimePerc > 0 || data.downtime > 0;
    $(`#${prefix}-${period}-uptime`).text(uptimePerc.toFixed(2) + '%');
    $(`#${prefix}-${period}-downtime-mins`).text(data.downtime);
    $(`#${prefix}-${period}-ms`).text(data.averageMs || 0);
    $(`#${prefix}-${period}-uptime-bar`).css('width', (hasData ? uptimePerc : 0) + '%');
    $(`#${prefix}-${period}-downtime-bar`).css('width', (hasData ? downtimePerc : 0) + '%');
}

function calculateWeeklyUptime(data) {
    const weekAgo = Date.now() - 7 * 24 * 60 * 60 * 1000;
    const responseTimes = data.response_times || [];
    const recentIndexes = data.statuses
        .map(function (_, i) {
            return i;
        })
        .filter(function (i) {
            const timestamp = data.timestamps && data.timestamps[i] ? Date.parse(data.timestamps[i]) : NaN;
            return isNaN(timestamp) || timestamp >= weekAgo;
        });
    const recentStatuses = recentIndexes.map(function (i) {
        return data.statuses[i];
    });
    const recentTimes = recentIndexes
        .map(function (i) {
            return Number(responseTimes[i] || 0);
        })
        .filter(function (v) {
            return v > 0;
        });
    const upCount = recentStatuses.filter(function (s) {
        return s === true;
    }).length;
    const percentage = recentStatuses.length > 0 ? (upCount / recentStatuses.length) * 100 : 0;
    const averageMs =
        typeof data.avg_response_ms === 'number' && data.avg_response_ms > 0
            ? data.avg_response_ms
            : recentTimes.length
              ? Math.round(
                    recentTimes.reduce(function (sum, v) {
                        return sum + v;
                    }, 0) / recentTimes.length
                )
              : 0;
    return {
        overall: percentage.toFixed(2),
        downtime: (recentStatuses.length - upCount) * 5,
        averageMs: averageMs,
    };
}

function calculateMonthlyUptime(data) {
    const now = new Date();
    const responseTimes = data.response_times || [];
    const monthlyIndexes = data.statuses
        .map(function (_, i) {
            return i;
        })
        .filter(function (i) {
            const timestamp = data.timestamps && data.timestamps[i] ? new Date(data.timestamps[i]) : null;
            return (
                !timestamp ||
                (timestamp.getFullYear() === now.getFullYear() && timestamp.getMonth() === now.getMonth())
            );
        });
    const monthlyStatuses = monthlyIndexes.map(function (i) {
        return data.statuses[i];
    });
    const monthlyTimes = monthlyIndexes
        .map(function (i) {
            return Number(responseTimes[i] || 0);
        })
        .filter(function (v) {
            return v > 0;
        });
    const upCount = monthlyStatuses.filter(function (s) {
        return s === true;
    }).length;
    const percentage = monthlyStatuses.length > 0 ? (upCount / monthlyStatuses.length) * 100 : 0;
    const averageMs = monthlyTimes.length
        ? Math.round(
              monthlyTimes.reduce(function (sum, v) {
                  return sum + v;
              }, 0) / monthlyTimes.length
          )
        : typeof data.avg_response_ms === 'number' && data.avg_response_ms > 0
          ? data.avg_response_ms
          : 0;
    return {
        overall: percentage.toFixed(2),
        downtime: (monthlyStatuses.length - upCount) * 5,
        averageMs: averageMs,
    };
}

function processService(type, data) {
    if (data && data.is_dummy) {
        $(`#${type}-data-source`).text('Data dummy');
    } else if (data) {
        $(`#${type}-data-source`).text('Activity log');
    }
    if (!data || !Array.isArray(data.labels) || !Array.isArray(data.statuses) || data.labels.length === 0) {
        updateSummary(type, 'weekly', { overall: 0, downtime: 0, averageMs: 0 });
        updateSummary(type, 'monthly', { overall: 0, downtime: 0, averageMs: 0 });
        return;
    }
    updateSummary(type, 'weekly', calculateWeeklyUptime(data));
    updateSummary(type, 'monthly', calculateMonthlyUptime(data));
}

function showUptimeError(message) {
    $('#uptime-loading')
        .removeClass('d-none')
        .html(`
    <div class="text-center py-5">
      <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
      <h5>Gagal Memuat Data</h5>
      <p class="text-muted">${message}</p>
    </div>`);
    $('#uptime-content').addClass('d-none');
}

function renderChartIfNeeded(canvasId, url) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !chartDataCache || !chartDataCache[url]) return;
    if (ctx.chartInstance) return;

    const data = chartDataCache[url];
    if (!data || !Array.isArray(data.labels) || !Array.isArray(data.response_times) || !Array.isArray(data.statuses))
        return;

    const upData = data.statuses.map(function (s) {
        return s === true ? 1 : null;
    });
    const downData = data.statuses.map(function (s) {
        return s === false ? 1 : null;
    });

    ctx.chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Response Time (ms)',
                    data: data.response_times,
                    backgroundColor: 'rgba(54,162,235,0.5)',
                    borderColor: 'rgba(54,162,235,1)',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: 'UP',
                    type: 'line',
                    data: upData,
                    borderColor: 'rgba(40,167,69,1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'y1',
                },
                {
                    label: 'DOWN',
                    type: 'line',
                    data: downData,
                    borderColor: 'rgba(220,53,69,1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Response Time (ms)' },
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Status' },
                    ticks: {
                        stepSize: 1,
                        callback: function (v) {
                            return v === 1 ? 'UP' : '';
                        },
                    },
                },
                x: {
                    title: { display: true, text: 'Waktu' },
                },
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        generateLabels: function (chart) {
                            return chart.data.datasets.map(function (dataset, i) {
                                const label = dataset.label || '';
                                return {
                                    text: label,
                                    fillStyle: dataset.backgroundColor || dataset.borderColor,
                                    strokeStyle: dataset.borderColor,
                                    lineWidth: 2,
                                    hidden: !chart.isDatasetVisible(i),
                                    index: i,
                                    pointStyle: label === 'UP' || label === 'DOWN' ? 'circle' : 'rect',
                                    fontColor: '#000',
                                };
                            });
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (c) {
                            if (c.dataset.label === 'Response Time (ms)')
                                return 'Response Time: ' + c.parsed.y + ' ms';
                            if (c.dataset.label === 'UP') return 'Status: UP';
                            if (c.dataset.label === 'DOWN') return 'Status: DOWN';
                        },
                    },
                },
            },
        },
    });
}

function loadUptimePercentage() {
    if (uptimeLoadStarted) return;
    uptimeLoadStarted = true;
    const $content = $('#uptime-content');
    const $loading = $('#uptime-loading');
    $loading.removeClass('d-none');
    $content.addClass('d-none');

    $.ajax({
        url: '/activity-log/data',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (!response || typeof response !== 'object' || response.error) {
                showUptimeError('Respons dari server tidak valid.');
                uptimeLoadStarted = false;
                return;
            }
            processService('coffee', response[services.coffee] || null);
            processService('latte', response[services.latte] || null);
            chartDataCache = response;
            renderChartIfNeeded('uptimeChartInixcoffee', services.coffee);
            renderChartIfNeeded('uptimeChartInixlatte', services.latte);
            $loading.addClass('d-none');
            $content.removeClass('d-none');
        },
        error: function (xhr) {
            uptimeLoadStarted = false;
            $('#coffee-data-source').text('Gagal memuat data: HTTP ' + xhr.status);
            $('#latte-data-source').text('Gagal memuat data: HTTP ' + xhr.status);
            $loading.addClass('d-none');
            $content.removeClass('d-none');
        },
    });
}

function ajaxUptime() {
    const cfg = getMenusConfig();
    $.ajax({
        url: cfg.urls?.activityLogChart || '/activity-log/data',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (!response || typeof response !== 'object' || response.error) return;
            chartDataCache = response;
            const activeTab = $('.nav-link.active').attr('data-bs-target');
            if (activeTab === '#nav-inixcoffee') {
                renderChartIfNeeded('uptimeChartInixcoffee', services.coffee);
            } else if (activeTab === '#nav-inixlatte') {
                renderChartIfNeeded('uptimeChartInixlatte', services.latte);
            }
        },
    });
}

function handleNotificationDismissal() {
    $(this).closest('.card-body').hide();
    if ($('#modalPemberitahuan .card-body:visible').length === 0) {
        $('#modalPemberitahuan hr').hide();
    }
}

$(document).ready(function () {
    const cfg = getMenusConfig();

    $('#pills-home-tab').on('click', function () {
        $('#loadingModal').modal('show');
        showPane('#pills-home');
        cekip();
        setTimeout(function () {
            $('#loadingModal').modal('hide');
        }, 1000);
    });

    $('#pills-admin-tab').on('click', function () {
        $('#loadingModal').modal('show');
        showPane('#pills-admin');
        setTimeout(function () {
            $('#loadingModal').modal('hide');
        }, 1000);
    });

    $('#pills-dashboard-tab').on('click', function () {
        loadDashboard().catch(function (err) {
            console.error(err);
        });
    });

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('data-bs-target');
        if (target === '#nav-inixcoffee' || target === '#nav-inixlatte') {
            ajaxUptime();
        }
    });

    $(document).on('shown.bs.tab', '#pills-uptime-presentase-tab', function () {
        loadUptimePercentage();
        let activeBtn = $('#uptimeServiceTabs button.active');
        if (!activeBtn.length) {
            $('#uptime-coffee-tab').addClass('active').attr('aria-selected', 'true');
            $('#uptime-coffee').addClass('show active');
            activeBtn = $('#uptime-coffee-tab');
        }
        const activeTarget = activeBtn.attr('data-bs-target') || '#uptime-coffee';
        $(activeTarget).addClass('show active').css('display', '');
        const isCoffee = activeTarget === '#uptime-coffee';
        const canvasId = isCoffee ? 'uptimeChartInixcoffee' : 'uptimeChartInixlatte';
        const canvas = document.getElementById(canvasId);
        if (canvas && canvas.chartInstance) {
            canvas.chartInstance.resize();
        } else if (chartDataCache) {
            renderChartIfNeeded(canvasId, isCoffee ? services.coffee : services.latte);
        }
    });

    $(document).on('shown.bs.tab', '#uptimeServiceTabs button[data-bs-toggle="tab"]', function (e) {
        const isCoffee = e.target.id === 'uptime-coffee-tab';
        const canvasId = isCoffee ? 'uptimeChartInixcoffee' : 'uptimeChartInixlatte';
        const targetSelector = $(e.target).attr('data-bs-target') || (isCoffee ? '#uptime-coffee' : '#uptime-latte');
        $(targetSelector).addClass('show active').css('display', '');
        const canvas = document.getElementById(canvasId);
        if (canvas && canvas.chartInstance) {
            canvas.chartInstance.resize();
        } else if (chartDataCache) {
            renderChartIfNeeded(canvasId, isCoffee ? services.coffee : services.latte);
        }
    });

    if ($('#pills-uptime-presentase').hasClass('show') && $('#pills-uptime-presentase').hasClass('active')) {
        loadUptimePercentage();
    }
    setTimeout(loadUptimePercentage, 500);

    $(document).on('click', '.btn-absen', function () {
        if (typeof Webcam === 'undefined') return;
        Webcam.set({
            width: 320,
            height: 320,
            image_format: 'jpeg',
            jpeg_quality: 50,
            force_flash: false,
            flip_horiz: true,
            constraints: {
                facingMode: 'user',
                width: { ideal: 320 },
                height: { ideal: 320 },
            },
        });
        Webcam.attach('#camera');

        $('#takeSnapshot')
            .off('click')
            .on('click', function () {
                Webcam.snap(function (data_uri) {
                    $('#result').html('<img src="' + data_uri + '"/>');
                    const now = new Date();
                    const jabatan = cfg.jabatan;
                    $.ajax({
                        url: cfg.urls?.absensiMasuk,
                        type: 'POST',
                        data: {
                            _token: cfg.csrfToken || csrfToken,
                            id_karyawan: cfg.idKaryawan,
                            tanggal: now.toISOString().split('T')[0],
                            jabatan: jabatan,
                            jam_masuk: now.toTimeString().split(' ')[0],
                            keterangan: $('input[name="keterangan"]:checked').val(),
                            shift: hitungShift(jabatan, now, 'masuk'),
                            foto: data_uri,
                        },
                        success: function () {
                            $('#modalAbsen').modal('hide');
                            window.location.href = cfg.urls?.absensiKaryawan || '/absensi/karyawan';
                        },
                        error: function (xhr) {
                            alert(
                                (xhr.responseJSON && xhr.responseJSON.error) ||
                                    'Terjadi kesalahan. Silakan coba lagi.'
                            );
                            location.reload();
                        },
                    });
                });
            });
    });

    $('#pulang')
        .off('click')
        .on('click', function () {
            const now = new Date();
            const jabatan = cfg.jabatan;
            const keteranganPulang = $('input[name="keterangan"]:checked').val();
            if (!keteranganPulang) {
                alert('Silakan pilih keterangan pulang.');
                return;
            }
            $.ajax({
                url: cfg.urls?.absensiKeluar,
                type: 'POST',
                data: {
                    _token: cfg.csrfToken || csrfToken,
                    id_karyawan: cfg.idKaryawan,
                    tanggal: now.toISOString().split('T')[0],
                    jam_keluar: now.toTimeString().split(' ')[0],
                    shift: hitungShift(jabatan, now, 'pulang'),
                    keterangan_pulang: keteranganPulang,
                    jabatan: jabatan,
                    client_time: now.toISOString(),
                },
                success: function (response) {
                    if (response.success) {
                        window.location.href = cfg.urls?.absensiKaryawan || '/absensi/karyawan';
                    } else {
                        alert('Respons tidak valid dari server. Silakan coba lagi.');
                    }
                },
                error: function (xhr) {
                    let errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    alert(errorMsg);
                    window.location.href = cfg.urls?.absensiKaryawan || '/absensi/karyawan';
                },
            });
        });

    $('#modalAbsen').on('hidden.bs.modal', function () {
        if (typeof Webcam !== 'undefined') {
            Webcam.reset();
        }
    });

    $('#modalPemberitahuan').on('click', '.btn-danger', handleNotificationDismissal);

    cekip();
    cekjabatan();
});

Object.assign(window, {
    toggleSubscription,
    updateButtonStatus,
    loadDashboard,
    loadUptimePercentage,
    cekjabatan,
    cekip,
    hitungShift,
});
