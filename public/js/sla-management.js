$(document).ready(function () {

    // =======================================================================
    // 0. PAGINATION STATE — Detail Tiket Programmer
    // =======================================================================
    let progTicketAllData = [];
    let progTicketCurrentPage = 1;
    const PROG_PAGE_SIZE = 10;
    let progTicketSearchQuery = '';   // kata kunci pencarian aktif

    // Kembalikan data yang sudah difilter berdasarkan search query
    function getFilteredTickets() {
        const q = progTicketSearchQuery.trim().toLowerCase();
        if (!q) return progTicketAllData;
        return progTicketAllData.filter(t =>
            (t.ticket_id  && t.ticket_id.toLowerCase().includes(q)) ||
            (t.pic        && t.pic.toLowerCase().includes(q))        ||
            (t.kategori   && t.kategori.toLowerCase().includes(q))   ||
            (t.priority   && t.priority.toLowerCase().includes(q))
        );
    }

    function renderProgTicketPage(page) {
        const tickets   = getFilteredTickets();   // ← hasil filter / semua data
        const total     = tickets.length;
        const totalPages = Math.max(1, Math.ceil(total / PROG_PAGE_SIZE));
        progTicketCurrentPage = Math.max(1, Math.min(page, totalPages));

        const start = (progTicketCurrentPage - 1) * PROG_PAGE_SIZE;
        const end   = Math.min(start + PROG_PAGE_SIZE, total);
        const pageTickets = tickets.slice(start, end);

        const detailBody = document.getElementById('prog-ticket-detail-body');
        const pageInfo   = document.getElementById('prog-ticket-page-info');
        const prevBtn    = document.getElementById('prog-ticket-prev');
        const nextBtn    = document.getElementById('prog-ticket-next');
        if (!detailBody) return;

        const maxHours       = tickets.length > 0 ? tickets[0].resolution_hours : 0;
        const priorityBadge  = { High: 'danger', Medium: 'warning', Low: 'success', Other: 'secondary' };
        const monthNames     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        const formatDate     = (str) => {
            if (!str) return '-';
            const d = new Date(str);
            return `${d.getDate()} ${monthNames[d.getMonth()]} ${d.getFullYear()}`;
        };

        detailBody.innerHTML = '';
        if (total === 0) {
            detailBody.innerHTML = '<tr><td colspan="9" class="text-center py-3 text-muted">Tidak ada data tiket.</td></tr>';
        } else {
            pageTickets.forEach((t, i) => {
                const globalIdx  = start + i;
                const isLongest  = t.resolution_hours === maxHours;
                const slaBadge   = t.sla_met
                    ? '<span class="badge bg-success">✓ Met</span>'
                    : '<span class="badge bg-danger">✗ Breached</span>';
                const rowClass   = isLongest ? 'table-danger fw-bold' : '';
                const resDisplay = isLongest
                    ? `<span class="text-danger fw-bold">${t.resolution_hours} jam</span> <i class="bi bi-trophy-fill text-danger" title="Resolusi Terlama"></i>`
                    : `${t.resolution_hours} jam`;

                detailBody.innerHTML += `<tr class="${rowClass}">
                    <td class="text-muted small">${globalIdx + 1}</td>
                    <td><code>${t.ticket_id}</code></td>
                    <td>${t.pic}</td>
                    <td><small>${t.kategori}</small></td>
                    <td><span class="badge bg-${priorityBadge[t.priority] || 'secondary'}">${t.priority}</span></td>
                    <td><small>${formatDate(t.created_at)}</small></td>
                    <td><small>${formatDate(t.tanggal_selesai)}</small></td>
                    <td class="text-center">${resDisplay}</td>
                    <td class="text-center">${slaBadge}</td>
                </tr>`;
            });
        }

        if (pageInfo) {
            const allTotal = progTicketAllData.length;
            if (progTicketSearchQuery.trim()) {
                pageInfo.textContent = `Halaman ${progTicketCurrentPage} dari ${totalPages} — ditemukan ${total} dari ${allTotal} tiket`;
            } else {
                pageInfo.textContent = `Halaman ${progTicketCurrentPage} dari ${totalPages} (${total} tiket)`;
            }
        }
        if (prevBtn)  prevBtn.disabled  = progTicketCurrentPage <= 1;
        if (nextBtn)  nextBtn.disabled  = progTicketCurrentPage >= totalPages;

        // Sync badge count dengan hasil filter
        const detailCount = document.getElementById('prog-ticket-detail-count');
        if (detailCount) {
            detailCount.textContent = progTicketSearchQuery.trim()
                ? `${total} / ${progTicketAllData.length} Tiket`
                : `${progTicketAllData.length} Tiket`;
        }
    }

    // Bind tombol Prev / Next — cukup sekali di luar loadSlaUser
    $(document).on('click', '#prog-ticket-prev', function () {
        renderProgTicketPage(progTicketCurrentPage - 1);
    });
    $(document).on('click', '#prog-ticket-next', function () {
        renderProgTicketPage(progTicketCurrentPage + 1);
    });

    // Bind input pencarian live search
    $(document).on('input', '#prog-ticket-search', function () {
        progTicketSearchQuery = $(this).val();
        progTicketCurrentPage = 1;   // reset ke halaman 1 setiap ketik
        renderProgTicketPage(1);
    });

    // Bind tombol clear (×)
    $(document).on('click', '#prog-ticket-search-clear', function () {
        $('#prog-ticket-search').val('').trigger('input');
    });

    // =======================================================================
    // 1. HELPER FUNCTIONS
    // =======================================================================
    const formatPercent = (val) => `${parseFloat(val).toFixed(1)}%`;
    const formatHours = (val) => `${parseFloat(val).toFixed(1)} jam`;
    const formatValue = (val) => parseFloat(val).toFixed(0);
    const getSlaClass = (val) => (val >= 90 ? 'text-success' : (val >= 80 ? 'text-warning' : 'text-danger'));

    function updateFilterDisplay(filters, elementId) {
        try {
            if (!filters || !filters.start || !filters.end) return;
            const startDate = new Date(filters.start.replace(' ', 'T'));
            const endDate   = new Date(filters.end.replace(' ', 'T'));
            const year      = startDate.getFullYear();
            const el        = document.getElementById(elementId);
            if (!el) return;

            // Cek apakah range mencakup seluruh tahun (1 Jan – 31 Des)
            const isFullYear =
                startDate.getMonth() === 0 && startDate.getDate() === 1 &&
                endDate.getMonth()   === 11 && endDate.getDate()   === 31;

            if (isFullYear) {
                el.innerHTML = `<strong>Tahun: ${year} - Semua Bulan</strong><br><small class="text-muted">(Data: ${filters.start.split(' ')[0]} s/d ${filters.end.split(' ')[0]})</small>`;
            } else {
                const namaBulan = startDate.toLocaleString('id-ID', { month: 'long' });
                el.innerHTML = `<strong>Bulan: ${namaBulan} ${year}</strong><br><small class="text-muted">(Data: ${filters.start.split(' ')[0]} s/d ${filters.end.split(' ')[0]})</small>`;
            }
        } catch (e) { console.error("Gagal update filter display", e); }
    }

    // Bangun parameter start_date & end_date dari pilihan filter global
    function buildQueryString() {
        const year          = parseInt($('#globalTahunFilter').val()) || new Date().getFullYear();
        const selectedMonth = $('#globalBulanFilter').val();

        let startDate, endDate;

        const pad = n => String(n).padStart(2, '0');
        if (selectedMonth && selectedMonth !== 'all') {
            const monthIndex = parseInt(selectedMonth) - 1;
            const lastDay = new Date(year, monthIndex + 1, 0).getDate();
            startDate = `${year}-${pad(monthIndex + 1)}-01`;
            endDate   = `${year}-${pad(monthIndex + 1)}-${pad(lastDay)}`;
        } else {
            startDate = `${year}-01-01`;
            endDate   = `${year}-12-31`;
        }

        return `?start_date=${startDate}&end_date=${endDate}`;
    }

    // =======================================================================
    // 2. SLA PROGRAMMER
    // =======================================================================
    const slaProgTimBaseUrl = "/dashboard-sla/programmer/tim";
    const slaProgUserBaseUrl = "/dashboard-sla/programmer/user";
    const slaProgKritisBaseUrl = "/dashboard-sla/programmer/kritis";
    let slaProgrammerChart;

    async function loadSlaTim() {
        try {
            const query = buildQueryString();
            const response = await fetch(slaProgTimBaseUrl + query);
            const kpi = await response.json();
            updateFilterDisplay(kpi.filters, 'sla_current_period');

            const resComp = kpi.sla_resolution_compliance || 0;
            const resEl = document.getElementById('tim-sla-resolution');
            resEl.textContent = formatPercent(resComp);
            resEl.className = `fs-2 fw-bold ${getSlaClass(resComp)}`;

            const topProgEl = document.getElementById('top-prog-sla');
            if (topProgEl) {
                topProgEl.textContent = formatPercent(resComp);
                topProgEl.className = `fs-2 fw-bold ${getSlaClass(resComp)}`;
            }

            const respEl = document.getElementById('tim-sla-response');
            respEl.textContent = formatPercent(kpi.sla_response_compliance);
            respEl.className = `fs-2 fw-bold ${getSlaClass(kpi.sla_response_compliance)}`;

            document.getElementById('tim-avg-resolution').textContent = formatHours(kpi.avg_resolution_time);
            document.getElementById('tim-total-tickets').textContent = formatValue(kpi.total_tickets);

            const chartCtx = document.getElementById('slaTimPriorityChart').getContext('2d');
            const pData = kpi.tickets_by_priority || {};
            if (slaProgrammerChart) slaProgrammerChart.destroy();

            if (kpi.total_tickets > 0) {
                $('#programmer-chart-row').show();
                slaProgrammerChart = new Chart(chartCtx, {
                    type: 'bar',
                    data: {
                        // Tambahkan 'High (Ext)' ke labels
                        labels: ['High', 'High (Ext)', 'Medium', 'Low', 'Other'],
                        datasets: [{
                            label: 'Jumlah Tiket',
                            // Tarik data 'High-Extended'
                            data: [
                                pData.High || 0,
                                pData['High-Extended'] || 0,
                                pData.Medium || 0,
                                pData.Low || 0,
                                pData.Other || 0
                            ],
                            // Tambahkan warna maroon (#842029) untuk High-Extended
                            backgroundColor: ['#dc3545', '#842029', '#ffc107', '#198754', '#6c757d']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
                });
            } else { $('#programmer-chart-row').hide(); }
        } catch (error) { console.error('Gagal SLA Prog Tim:', error); }
    }

    async function loadSlaUser() {
        try {
            const query = buildQueryString();
            const response = await fetch(slaProgUserBaseUrl + query);
            const data = await response.json();

            // --- Tabel Kinerja Per Programmer ---
            const tableBody = document.getElementById('sla-user-table-body');
            tableBody.innerHTML = '';
            if (!data.kpi || data.kpi.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data.</td></tr>';
            } else {
                data.kpi.sort((a, b) => b.total_tickets - a.total_tickets).forEach(item => {
                    const priority = item.tickets_by_priority || { High: 0, Medium: 0, Low: 0 };
                    const maxResTime = (item.total_tickets > 0 && item.max_resolution_time !== undefined)
                        ? formatHours(item.max_resolution_time)
                        : '-';
                    const ticketInfo = (item.longest_ticket && item.longest_ticket.ticket_id)
                        ? `<br><small class="text-muted font-monospace">${item.longest_ticket.ticket_id}</small>`
                        : '';
                    tableBody.innerHTML += `<tr>
                        <td><strong>${item.nama_programmer}</strong></td>
                        <td class="${getSlaClass(item.sla_resolution_compliance)}">${formatPercent(item.sla_resolution_compliance)}</td>
                        <td class="${getSlaClass(item.sla_response_compliance)}">${formatPercent(item.sla_response_compliance)}</td>
                        <td>${formatHours(item.avg_resolution_time)}</td>
                        <td><span class="fw-bold text-danger">${maxResTime}</span>${ticketInfo}</td>
                        <td><strong>${formatValue(item.total_tickets)}</strong></td>
                        <td><span class="badge bg-danger">H:${priority.High}</span> <span class="badge bg-warning">M:${priority.Medium}</span> <span class="badge bg-success">L:${priority.Low}</span></td>
                    </tr>`;
                });
            }
            data.kpi.sort((a, b) => b.total_tickets - a.total_tickets).forEach(item => {
                            const priority = item.tickets_by_priority || {};

                            // Tambahkan span badge untuk HE (High-Extended)
                            const badgeHtml = `
                                <span class="badge bg-danger">H:${priority.High || 0}</span>
                                <span class="badge bg-dark">HE:${priority['High-Extended'] || 0}</span>
                                <span class="badge bg-warning text-dark">M:${priority.Medium || 0}</span>
                                <span class="badge bg-success">L:${priority.Low || 0}</span>
                            `;

                            tableBody.innerHTML += `<tr>
                                <td><strong>${item.nama_programmer}</strong></td>
                                <td class="${getSlaClass(item.sla_resolution_compliance)}">${formatPercent(item.sla_resolution_compliance)}</td>
                                <td class="${getSlaClass(item.sla_response_compliance)}">${formatPercent(item.sla_response_compliance)}</td>
                                <td>${formatHours(item.avg_resolution_time)}</td>
                                <td><strong>${formatValue(item.total_tickets)}</strong></td>
                                <td>${badgeHtml}</td>
                            </tr>`;
                        });
        } catch (error) { console.error('Gagal SLA Prog User:', error); }
    }

    async function loadSlaKritis() {
        try {
            const query = buildQueryString();
            const response = await fetch(slaProgKritisBaseUrl + query);
            const data = await response.json();
            const kpi = data.kpi || {};
            document.getElementById('kritis-sla-resolution').textContent = formatPercent(kpi.sla_resolution_compliance || 0);
            document.getElementById('kritis-sla-response').textContent = formatPercent(kpi.sla_response_compliance || 0);
            document.getElementById('kritis-avg-resolution').textContent = formatHours(kpi.avg_resolution_time || 0);
            document.getElementById('kritis-total-insiden').textContent = formatValue(kpi.total_insiden || 0);

            const tableBody = document.getElementById('sla-kritis-table-body');
            tableBody.innerHTML = '';
            if (!data.details || data.details.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada insiden kritis.</td></tr>'; return;
            }
            data.details.forEach(item => {
                const badge = item.sla_resolution_met ? '<span class="badge bg-success">Met</span>' : '<span class="badge bg-danger">Breached</span>';
                tableBody.innerHTML += `<tr><td>${item.id}</td><td>${item.laporan ? item.laporan.substring(0, 50) : '-'}...</td><td>${badge}</td><td>${formatHours(item.actual_resolution_hours)}</td><td>${item.actual_response_hours ? formatHours(item.actual_response_hours) : '-'}</td><td>${item.responder}</td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA Prog Kritis:', error); }
    }

    // =======================================================================
    // 3. SLA TECHNICAL SUPPORT
    // =======================================================================
    const slaTsTimBaseUrl = "/dashboard-sla/tech-support/tim";
    const slaTsUserBaseUrl = "/dashboard-sla/tech-support/user";
    const slaTsKritisBaseUrl = "/dashboard-sla/tech-support/kritis";
    let slaTsTimChart;

    async function loadSlaTsTim() {
        try {
            const query = buildQueryString();
            const response = await fetch(slaTsTimBaseUrl + query);
            const kpi = await response.json();
            updateFilterDisplay(kpi.filters, 'ts_sla_current_period');

            const resComp = kpi.sla_resolution_compliance || 0;
            document.getElementById('ts-tim-sla-resolution').textContent = formatPercent(resComp);
            document.getElementById('ts-tim-sla-resolution').className = `fs-2 fw-bold ${getSlaClass(resComp)}`;

            const topTsEl = document.getElementById('top-ts-sla');
            if (topTsEl) {
                topTsEl.textContent = formatPercent(resComp);
                topTsEl.className = `fs-2 fw-bold ${getSlaClass(resComp)}`;
            }

            document.getElementById('ts-tim-sla-response').textContent = formatPercent(kpi.sla_response_compliance);
            document.getElementById('ts-tim-sla-response').className = `fs-2 fw-bold ${getSlaClass(kpi.sla_response_compliance)}`;

            document.getElementById('ts-tim-avg-resolution').textContent = formatHours(kpi.avg_resolution_time);
            document.getElementById('ts-tim-total-tickets').textContent = formatValue(kpi.total_tickets);

            const chartCtx = document.getElementById('tsSlaTimPriorityChart').getContext('2d');
            const pData = kpi.tickets_by_priority || {};
            if (slaTsTimChart) slaTsTimChart.destroy();

            if (kpi.total_tickets > 0) {
                $('#ts-chart-row').show();
                slaTsTimChart = new Chart(chartCtx, {
                    type: 'bar',
                    data: {
                        labels: ['High', 'High (Ext)', 'Medium', 'Low', 'Other'],
                        datasets: [{
                            label: 'Jumlah Tiket',
                            data: [
                                pData.High || 0,
                                pData['High-Extended'] || 0,
                                pData.Medium || 0,
                                pData.Low || 0,
                                pData.Other || 0
                            ],
                            backgroundColor: ['#dc3545', '#842029', '#ffc107', '#198754', '#6c757d']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
                });
            } else { $('#ts-chart-row').hide(); }
        } catch (error) { console.error('Gagal SLA TS Tim:', error); }
    }

    async function loadSlaTsUser() {
        try {
            const query = buildQueryString();
            const response = await fetch(slaTsUserBaseUrl + query);
            const data = await response.json();
            const tableBody = document.getElementById('ts-sla-user-table-body');
            tableBody.innerHTML = '';
            if (!data.kpi || data.kpi.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>'; return;
            }
            data.kpi.sort((a, b) => b.total_tickets - a.total_tickets).forEach(item => {
                const priority = item.tickets_by_priority || {};

                // Tambahkan span badge untuk HE
                const badgeHtml = `
                    <span class="badge bg-danger">H:${priority.High || 0}</span>
                    <span class="badge bg-dark">HE:${priority['High-Extended'] || 0}</span>
                    <span class="badge bg-warning text-dark">M:${priority.Medium || 0}</span>
                    <span class="badge bg-success">L:${priority.Low || 0}</span>
                `;

                tableBody.innerHTML += `<tr>
                    <td><strong>${item.nama_programmer}</strong></td>
                    <td class="${getSlaClass(item.sla_resolution_compliance)}">${formatPercent(item.sla_resolution_compliance)}</td>
                    <td class="${getSlaClass(item.sla_response_compliance)}">${formatPercent(item.sla_response_compliance)}</td>
                    <td>${formatHours(item.avg_resolution_time)}</td>
                    <td><strong>${formatValue(item.total_tickets)}</strong></td>
                    <td>${badgeHtml}</td>
                </tr>`;
            });
        } catch (error) { console.error('Gagal SLA TS User:', error); }
    }

    async function loadSlaTsKritis() {
        try {
            const query = buildQueryString();
            const response = await fetch(slaTsKritisBaseUrl + query);
            const data = await response.json();
            const kpi = data.kpi || {};
            document.getElementById('ts-kritis-sla-resolution').textContent = formatPercent(kpi.sla_resolution_compliance || 0);
            document.getElementById('ts-kritis-sla-response').textContent = formatPercent(kpi.sla_response_compliance || 0);
            document.getElementById('ts-kritis-avg-resolution').textContent = formatHours(kpi.avg_resolution_time || 0);
            document.getElementById('ts-kritis-total-insiden').textContent = formatValue(kpi.total_insiden || 0);

            const tableBody = document.getElementById('ts-sla-kritis-table-body');
            tableBody.innerHTML = '';
            if (!data.details || data.details.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada insiden kritis.</td></tr>'; return;
            }
            data.details.forEach(item => {
                const badge = item.sla_resolution_met ? '<span class="badge bg-success">Met</span>' : '<span class="badge bg-danger">Breached</span>';
                tableBody.innerHTML += `<tr><td>${item.id}</td><td>${item.laporan ? item.laporan.substring(0, 50) : '-'}...</td><td>${badge}</td><td>${formatHours(item.actual_resolution_hours)}</td><td>${item.actual_response_hours ? formatHours(item.actual_response_hours) : '-'}</td><td>${item.responder}</td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA TS Kritis:', error); }
    }

    // =======================================================================
    // 4. SLA EVENT (WEBINAR)
    // =======================================================================
    async function loadSlaEvent() {
        $('#event-title').text('Memuat data...');
        $('#event-date').text('...');
        $('#event-kpi-completion, #event-kpi-compliance').text('...');
        $('#event-kpi-late, #event-kpi-overdue').text('...');
        $('#event-sla-table-body').html('<tr><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</td></tr>');

        try {
            const query = buildQueryString();
            const response = await fetch('/dashboard-sla/event/overall' + query);
            if (!response.ok) throw new Error('Network error');

            const data = await response.json();
            const kpi = data.kpi || {};
            const events = data.events || [];

            const getKpiClass = (val) => val >= 100 ? 'text-success' : 'text-primary';

            $('#event-title').text(kpi.event_title || 'Data Keseluruhan');
            $('#event-date').text(`Tahun Evaluasi: ${kpi.event_date || '-'}`);
            $('#event-kpi-completion').text(formatPercent(kpi.completion_rate || 0)).attr('class', `fs-2 fw-bold ${getKpiClass(kpi.completion_rate || 0)}`);

            const eventSlaComp = kpi.sla_compliance || 0;
            $('#event-kpi-compliance').text(formatPercent(eventSlaComp)).attr('class', `fs-2 fw-bold ${getSlaClass(eventSlaComp)}`);

            const topEventEl = document.getElementById('top-event-sla');
            if (topEventEl) {
                topEventEl.textContent = formatPercent(eventSlaComp);
                topEventEl.className = `fs-2 fw-bold ${getSlaClass(eventSlaComp)}`;
            }

            $('#event-kpi-late').text(kpi.total_late || 0);
            $('#event-kpi-overdue').text(kpi.total_overdue || 0);

            const tableBody = $('#event-sla-table-body');
            tableBody.empty();

            if (events.length === 0) {
                tableBody.html('<tr><td colspan="6" class="text-center p-4">Tidak ada data event webinar ditemukan pada periode ini.</td></tr>');
            } else {
                events.forEach(event => {
                    tableBody.append(`
                        <tr>
                            <td>
                                <strong>Bulan ${event.month_name || '-'}</strong><br>
                                <small class="text-secondary">${event.theme || '-'}</small>
                            </td>
                            <td>
                                <strong>${event.event_title || '-'}</strong><br>
                                <small class="text-muted font-monospace">${event.planned_date || '-'}</small>
                            </td>
                            <td class="text-center fw-bold text-primary">${formatPercent(event.completion_rate || 0)}</td>
                            <td class="text-center fw-bold ${getSlaClass(event.sla_compliance || 0)}">${formatPercent(event.sla_compliance || 0)}</td>
                            <td class="text-center text-warning fw-bold">${event.total_late || 0}</td>
                            <td class="text-center text-danger fw-bold">${event.total_overdue || 0}</td>
                        </tr>
                    `);
                });
            }

        } catch (error) {
            console.error('Gagal SLA Event:', error);
            $('#event-sla-table-body').html(`<tr><td colspan="6" class="text-center py-3 text-danger">Error: ${error.message}</td></tr>`);
        }
    }

    // =======================================================================
    // 5. SLA DIGITAL
    // =======================================================================
    async function loadSlaDigital() {
        console.log('🚀 [SLA Digital] Memuat data');

        const $container = $('#sla-digital-container');
        const baseUrl = $container.data('url') || '/dashboard-sla/digital';

        $('#digital_sla_period').text('Memuat periode data...');
        $('#digital-ticket-res-sla, #digital-ticket-resp-sla, #digital-ticket-avg').text('...');
        $('#digital-content-sla, #digital-content-total, #digital-weeks-met, #digital-weeks-total').text('...');
        $('#digital-weekly-table-body').html('<tr><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</td></tr>');

        try {
            const query    = buildQueryString();
            const response = await $.ajax({
                url:         baseUrl + query,
                method:      'GET',
                dataType:    'json',
                headers:     { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                timeout:     15000
            });

            if (!response || !response.kpi) {
                throw new Error('Response tidak valid.');
            }

            const { kpi, content_details } = response;

            if (kpi.filters) {
                const start = kpi.filters.start?.split(' ')[0] || '-';
                const end = kpi.filters.end?.split(' ')[0] || '-';
                $('#digital_sla_period').text(`Periode: ${start} s/d ${end}`);
            }

            const resComp = parseFloat(kpi.ticket_resolution_compliance) || 0;
            const respComp = parseFloat(kpi.ticket_response_compliance) || 0;
            const avgTime = parseFloat(kpi.avg_resolution_time) || 0;

            $('#digital-ticket-res-sla').text(`${resComp.toFixed(1)}%`).attr('class', `fs-2 fw-bold ${getSlaClass(resComp)}`);
            $('#digital-ticket-resp-sla').text(`${respComp.toFixed(1)}%`).attr('class', `fs-2 fw-bold ${getSlaClass(respComp)}`);
            $('#digital-ticket-avg').text(`${avgTime.toFixed(1)} Jam`);

            const contentComp = parseFloat(kpi.content_sla_compliance) || 0;
            const totalContent = parseInt(kpi.total_content_uploaded) || 0;
            const weeksMet = parseInt(kpi.weeks_met) || 0;
            const totalWeeks = parseInt(kpi.total_weeks_evaluated) || 0;

            $('#digital-content-sla').text(`${contentComp.toFixed(1)}%`).attr('class', `fs-1 fw-bold ${getSlaClass(contentComp)}`);

            const topDigitalEl = document.getElementById('top-digital-sla');
            if (topDigitalEl) {
                topDigitalEl.textContent = formatPercent(contentComp);
                topDigitalEl.className = `fs-2 fw-bold ${getSlaClass(contentComp)}`;
            }

            $('#digital-content-total').text(totalContent);
            $('#digital-weeks-met').text(weeksMet);
            $('#digital-weeks-total').text(totalWeeks);

            renderDigitalWeeklyTable(content_details || []);

        } catch (err) {
            console.error('❌ Error SLA Digital:', err);
            $('#digital_sla_period').html(`<span class="text-danger">⚠️ Gagal: ${err.message}</span>`);
            $('#digital-ticket-res-sla, #digital-ticket-resp-sla, #digital-ticket-avg').text('-').addClass('text-muted');
            $('#digital-content-sla, #digital-content-total, #digital-weeks-met, #digital-weeks-total').text('-').addClass('text-muted');
            $('#digital-weekly-table-body').html(`<tr><td colspan="4" class="text-center py-3 text-danger">Error: ${err.message}</td></tr>`);
        }
    }

    function renderDigitalWeeklyTable(data) {
        const $tbody =$('#digital-weekly-table-body');
        if (!data || !Array.isArray(data) || data.length === 0) {
            $tbody.html('<tr><td colspan="4" class="text-center py-3 text-muted">Belum ada data mingguan</td></tr>');
            return;
        }

        const rows = data.map(row => {
            const period = row.week_range || row.period || '-';
            const uploads = row.count ?? row.uploads ?? 0;
            const target = row.target ?? 3;
            const status = row.status || '-';

            // Render label hari aktif jika ada dari controller
            const activeDaysLabel = row.active_days !== undefined
                ? `<br><small class="text-muted">(${row.active_days} Hari Aktif)</small>`
                : '';

            const badgeClass = status === 'Met' ? 'bg-success' : (status === 'Missed' ? 'bg-warning text-dark' : 'bg-secondary');

            return `
                <tr>
                    <td>${period}</td>
                    <td class="text-center fw-bold align-middle">${uploads}</td>
                    <td class="text-center align-middle">
                        <strong>${target}</strong>
                        ${activeDaysLabel}
                    </td>
                    <td class="text-center align-middle"><span class="badge ${badgeClass}">${status}</span></td>
                </tr>
            `;
        }).join('');

        $tbody.html(rows);
    }

    // =======================================================================
    // 6. SURVEY KEPUASAN (TAHUNAN)
    // =======================================================================
    async function loadSlaSurveyKepuasan() {
        const year = $('#globalTahunFilter').val() || new Date().getFullYear();
        $('#top-survey-year').text(year);
        $('#top-survey-sla').text('...');
        $('#top-survey-subtext').text('Memuat data...');

        try {
            const response = await fetch(`/dashboard-sla/survey-yearly?tahun=${year}`);
            if (!response.ok) throw new Error('Network error');
            const data = await response.json();

            const avgStars = parseFloat(data.avg_stars || 0);
            const total = parseInt(data.total_respondents || 0);
            const satisfaction = parseFloat(data.satisfaction_percentage || 0);

            if (total === 0) {
                $('#top-survey-sla').text('-').removeClass('text-success text-warning text-danger').css('color', '#6c757d');
                $('#top-survey-subtext').text('0 Responden');
            } else {
                $('#top-survey-sla').html(`<i class="fa-solid fa-star text-warning me-1"></i>${avgStars.toFixed(1)} <span class="fs-5 text-muted">/ 4</span>`).css('color', '#6f42c1');
                $('#top-survey-subtext').html(`<span class="fw-bold text-success">${satisfaction.toFixed(1)}% Puas</span> &bull; ${total} Survey`);
            }
        } catch (error) {
            console.error('Gagal SLA Survey Kepuasan:', error);
            $('#top-survey-sla').text('-').css('color', '#dc3545');
            $('#top-survey-subtext').text('Gagal memuat');
        }
    }

    // =======================================================================
    // 7. INITIAL LOAD & EVENT BINDING
    // =======================================================================

    // Bind Filter Events
    $('#globalTahunFilter, #globalBulanFilter').on('change', function () {
        loadAllData();
    });

    function loadAllData() {
        // A. SLA Programmer
        loadSlaTim();
        loadSlaUser();
        loadSlaKritis();

        // B. SLA Technical Support
        loadSlaTsTim();
        loadSlaTsUser();
        loadSlaTsKritis();

        // C. SLA Digital
        loadSlaDigital();

        // D. SLA Webinar (Event)
        loadSlaEvent();

        // E. SLA Survey Kepuasan Tahunan
        loadSlaSurveyKepuasan();
    }

    // Initial Execute
    loadAllData();
});
