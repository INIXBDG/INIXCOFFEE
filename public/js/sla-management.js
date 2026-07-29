$(document).ready(function () {

    // =======================================================================
    // 1. HELPER FUNCTIONS
    // =======================================================================
    const formatPercent = (val) => `${parseFloat(val).toFixed(1)}%`;
    const formatHours = (val) => `${parseFloat(val).toFixed(1)} jam`;
    const formatValue = (val) => parseFloat(val).toFixed(0);
    const getSlaClass = (val) => (val >= 90 ? 'text-success' : (val >= 80 ? 'text-warning' : 'text-danger'));

    function updateFilterDisplay(filters, elementId) {
        try {
            const startDate = new Date(filters.start);
            const year = startDate.getFullYear();
            const month = startDate.getMonth();
            const semester = (month < 6) ? 1 : 2;
            const el = document.getElementById(elementId);
            if (el) {
                el.innerHTML = `<strong>Tahun: ${year} - Semester: ${semester}</strong><br><small class="text-muted">(Data: ${filters.start.split(' ')[0]} s/d ${filters.end.split(' ')[0]})</small>`;
            }
        } catch (e) { console.error("Gagal update filter display", e); }
    }

    // =======================================================================
    // 2. SLA PROGRAMMER
    // =======================================================================
    const slaProgTimUrl = "/dashboard-sla/programmer/tim";
    const slaProgUserUrl = "/dashboard-sla/programmer/user";
    const slaProgKritisUrl = "/dashboard-sla/programmer/kritis";
    let slaProgrammerChart;

    async function loadSlaTim() {
        try {
            const response = await fetch(slaProgTimUrl); 
            const kpi = await response.json();
            updateFilterDisplay(kpi.filters, 'sla_current_period');

            const resComp = kpi.sla_resolution_compliance || 0;

            const resEl = document.getElementById('tim-sla-resolution');
            resEl.textContent = formatPercent(resComp);
            resEl.className = `fs-2 fw-bold ${getSlaClass(resComp)}`;

            // Update Top Overview Card
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
            const pData = kpi.tickets_by_priority;
            if (slaProgrammerChart) slaProgrammerChart.destroy();
            if (kpi.total_tickets > 0) {
                $('#programmer-chart-row').show();
                slaProgrammerChart = new Chart(chartCtx, { 
                    type: 'bar', 
                    data: { 
                        labels: ['High', 'Medium', 'Low', 'Other'], 
                        datasets: [{ 
                            label: 'Jumlah Tiket', 
                            data: [pData.High, pData.Medium, pData.Low, pData.Other], 
                            backgroundColor: ['#dc3545', '#ffc107', '#198754', '#6c757d'] 
                        }] 
                    }, 
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        scales: { y: { beginAtZero: true } }, 
                        plugins: { legend: { display: false } } 
                    } 
                });
            } else { $('#programmer-chart-row').hide(); }
        } catch (error) { console.error('Gagal SLA Prog Tim:', error); }
    }

    async function loadSlaUser() {
        try {
            const response = await fetch(slaProgUserUrl); 
            const data = await response.json();
            const tableBody = document.getElementById('sla-user-table-body'); 
            tableBody.innerHTML = '';
            if (data.kpi.length === 0) { 
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>'; 
                return; 
            }
            data.kpi.sort((a, b) => b.total_tickets - a.total_tickets).forEach(item => {
                tableBody.innerHTML += `<tr><td><strong>${item.nama_programmer}</strong></td><td class="${getSlaClass(item.sla_resolution_compliance)}">${formatPercent(item.sla_resolution_compliance)}</td><td class="${getSlaClass(item.sla_response_compliance)}">${formatPercent(item.sla_response_compliance)}</td><td>${formatHours(item.avg_resolution_time)}</td><td><strong>${formatValue(item.total_tickets)}</strong></td><td><span class="badge bg-danger">H:${item.tickets_by_priority.High}</span> <span class="badge bg-warning">M:${item.tickets_by_priority.Medium}</span> <span class="badge bg-success">L:${item.tickets_by_priority.Low}</span></td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA Prog User:', error); }
    }

    async function loadSlaKritis() {
        try {
            const response = await fetch(slaProgKritisUrl); 
            const data = await response.json();
            const kpi = data.kpi;
            document.getElementById('kritis-sla-resolution').textContent = formatPercent(kpi.sla_resolution_compliance);
            document.getElementById('kritis-sla-response').textContent = formatPercent(kpi.sla_response_compliance);
            document.getElementById('kritis-avg-resolution').textContent = formatHours(kpi.avg_resolution_time);
            document.getElementById('kritis-total-insiden').textContent = formatValue(kpi.total_insiden);

            const tableBody = document.getElementById('sla-kritis-table-body'); 
            tableBody.innerHTML = '';
            if (data.details.length === 0) { 
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada insiden kritis.</td></tr>'; 
                return; 
            }
            data.details.forEach(item => {
                const badge = item.sla_resolution_met ? '<span class="badge bg-success">Met</span>' : '<span class="badge bg-danger">Breached</span>';
                tableBody.innerHTML += `<tr><td>${item.id}</td><td>${item.laporan.substring(0, 50)}...</td><td>${badge}</td><td>${formatHours(item.actual_resolution_hours)}</td><td>${item.actual_response_hours ? formatHours(item.actual_response_hours) : '-'}</td><td>${item.responder}</td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA Prog Kritis:', error); }
    }

    // =======================================================================
    // 3. SLA TECHNICAL SUPPORT
    // =======================================================================
    const slaTsTimUrl = "/dashboard-sla/tech-support/tim";
    const slaTsUserUrl = "/dashboard-sla/tech-support/user";
    const slaTsKritisUrl = "/dashboard-sla/tech-support/kritis";
    let slaTsTimChart;

    async function loadSlaTsTim() {
        try {
            const response = await fetch(slaTsTimUrl); 
            const kpi = await response.json();
            updateFilterDisplay(kpi.filters, 'ts_sla_current_period');

            const resComp = kpi.sla_resolution_compliance || 0;

            document.getElementById('ts-tim-sla-resolution').textContent = formatPercent(resComp);
            document.getElementById('ts-tim-sla-resolution').className = `fs-2 fw-bold ${getSlaClass(resComp)}`;

            // Update Top Overview Card
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
            const pData = kpi.tickets_by_priority;
            if (slaTsTimChart) slaTsTimChart.destroy();
            if (kpi.total_tickets > 0) {
                $('#ts-chart-row').show();
                slaTsTimChart = new Chart(chartCtx, { 
                    type: 'bar', 
                    data: { 
                        labels: ['High', 'Medium', 'Low', 'Other'], 
                        datasets: [{ 
                            label: 'Jumlah Tiket', 
                            data: [pData.High, pData.Medium, pData.Low, pData.Other], 
                            backgroundColor: ['#dc3545', '#ffc107', '#198754', '#6c757d'] 
                        }] 
                    }, 
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        scales: { y: { beginAtZero: true } }, 
                        plugins: { legend: { display: false } } 
                    } 
                });
            } else { $('#ts-chart-row').hide(); }
        } catch (error) { console.error('Gagal SLA TS Tim:', error); }
    }

    async function loadSlaTsUser() {
        try {
            const response = await fetch(slaTsUserUrl); 
            const data = await response.json();
            const tableBody = document.getElementById('ts-sla-user-table-body'); 
            tableBody.innerHTML = '';
            if (data.kpi.length === 0) { 
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>'; 
                return; 
            }
            data.kpi.sort((a, b) => b.total_tickets - a.total_tickets).forEach(item => {
                tableBody.innerHTML += `<tr><td><strong>${item.nama_programmer}</strong></td><td class="${getSlaClass(item.sla_resolution_compliance)}">${formatPercent(item.sla_resolution_compliance)}</td><td class="${getSlaClass(item.sla_response_compliance)}">${formatPercent(item.sla_response_compliance)}</td><td>${formatHours(item.avg_resolution_time)}</td><td><strong>${formatValue(item.total_tickets)}</strong></td><td><span class="badge bg-danger">H:${item.tickets_by_priority.High}</span> <span class="badge bg-warning">M:${item.tickets_by_priority.Medium}</span></td></tr>`;
            });
        } catch (error) { console.error('Gagal SLA TS User:', error); }
    }

    async function loadSlaTsKritis() {
        try {
            const response = await fetch(slaTsKritisUrl); 
            const data = await response.json();
            const kpi = data.kpi;
            document.getElementById('ts-kritis-sla-resolution').textContent = formatPercent(kpi.sla_resolution_compliance);
            document.getElementById('ts-kritis-sla-response').textContent = formatPercent(kpi.sla_response_compliance);
            document.getElementById('ts-kritis-avg-resolution').textContent = formatHours(kpi.avg_resolution_time);
            document.getElementById('ts-kritis-total-insiden').textContent = formatValue(kpi.total_insiden);

            const tableBody = document.getElementById('ts-sla-kritis-table-body'); 
            tableBody.innerHTML = '';
            if (data.details.length === 0) { 
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada insiden kritis.</td></tr>'; 
                return; 
            }
            data.details.forEach(item => {
                const badge = item.sla_resolution_met ? '<span class="badge bg-success">Met</span>' : '<span class="badge bg-danger">Breached</span>';
                tableBody.innerHTML += `<tr><td>${item.id}</td><td>${item.laporan.substring(0, 50)}...</td><td>${badge}</td><td>${formatHours(item.actual_resolution_hours)}</td><td>${item.actual_response_hours ? formatHours(item.actual_response_hours) : '-'}</td><td>${item.responder}</td></tr>`;
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
            const response = await fetch('/dashboard-sla/event/overall');
            if (!response.ok) throw new Error('Network error');

            const data = await response.json();
            const kpi = data.kpi;
            const events = data.events;

            const getKpiClass = (val) => val >= 100 ? 'text-success' : 'text-primary';
            const getSlaClass = (val) => val >= 90 ? 'text-success' : (val >= 80 ? 'text-warning' : 'text-danger');

            $('#event-title').text(kpi.event_title);
            $('#event-date').text(`Tahun Evaluasi: ${kpi.event_date}`);
            $('#event-kpi-completion').text(formatPercent(kpi.completion_rate)).attr('class', `fs-2 fw-bold ${getKpiClass(kpi.completion_rate)}`);
            
            const eventSlaComp = kpi.sla_compliance || 0;
            $('#event-kpi-compliance').text(formatPercent(eventSlaComp)).attr('class', `fs-2 fw-bold ${getSlaClass(eventSlaComp)}`);
            
            // Update Top Overview Card
            const topEventEl = document.getElementById('top-event-sla');
            if (topEventEl) {
                topEventEl.textContent = formatPercent(eventSlaComp);
                topEventEl.className = `fs-2 fw-bold ${getSlaClass(eventSlaComp)}`;
            }

            $('#event-kpi-late').text(kpi.total_late);
            $('#event-kpi-overdue').text(kpi.total_overdue);

            const tableBody = $('#event-sla-table-body');
            tableBody.empty();

            if (!events || events.length === 0) {
                tableBody.html('<tr><td colspan="6" class="text-center p-4">Tidak ada data event webinar ditemukan.</td></tr>');
            } else {
                events.forEach(event => {
                    tableBody.append(`
                        <tr>
                            <td>
                                <strong>Bulan ${event.month_name}</strong><br>
                                <small class="text-secondary">${event.theme}</small>
                            </td>
                            <td>
                                <strong>${event.event_title}</strong><br>
                                <small class="text-muted font-monospace">${event.planned_date}</small>
                            </td>
                            <td class="text-center fw-bold text-primary">${formatPercent(event.completion_rate)}</td>
                            <td class="text-center fw-bold ${getSlaClass(event.sla_compliance)}">${formatPercent(event.sla_compliance)}</td>
                            <td class="text-center text-warning fw-bold">${event.total_late}</td>
                            <td class="text-center text-danger fw-bold">${event.total_overdue}</td>
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
        const baseUrl = $container.data('url') || '/dashboard-digital';

        $('#digital_sla_period').text('Memuat periode data...');
        $('#digital-ticket-res-sla, #digital-ticket-resp-sla, #digital-ticket-avg').text('...');
        $('#digital-content-sla, #digital-content-total, #digital-weeks-met, #digital-weeks-total').text('...');
        $('#digital-weekly-table-body').html('<tr><td colspan="4" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</td></tr>');

        try {
            const now = new Date();
            const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
            const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];

            const response = await $.ajax({
                url: baseUrl,
                method: 'GET',
                data: { start_date: startOfMonth, end_date: endOfMonth },
                dataType: 'json',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                timeout: 15000
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
            
            // Update Top Overview Card
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
        const $tbody = $('#digital-weekly-table-body');
        if (!data || !Array.isArray(data) || data.length === 0) {
            $tbody.html('<tr><td colspan="4" class="text-center py-3 text-muted">Belum ada data mingguan</td></tr>');
            return;
        }

        const rows = data.map(row => {
            const period = row.week_range || row.period || '-';
            const uploads = row.count ?? row.uploads ?? 0;
            const target = row.target ?? 3;
            const status = row.status || '-';
            const badgeClass = status === 'Met' ? 'bg-success' : (status === 'Missed' ? 'bg-warning text-dark' : 'bg-secondary');

            return `
                <tr>
                    <td>${period}</td>
                    <td class="text-center fw-bold">${uploads}</td>
                    <td class="text-center">${target}</td>
                    <td class="text-center"><span class="badge ${badgeClass}">${status}</span></td>
                </tr>
            `;
        }).join('');

        $tbody.html(rows);
    }

    // =======================================================================
    // 6. INITIAL LOAD ON DOCUMENT READY
    // =======================================================================
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
});
