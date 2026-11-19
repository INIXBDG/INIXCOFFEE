const DashboardKPI = (function($) {
    'use strict';

    const DOM = {
        contentJK: $('#content_JK'), contentKS: $('#content_KS'), contentKC: $('#content_KC'), contentKI: $('#content_KI'),
        titleChart: $('#title_chartPenilaian'), loadingPenilaian: $('#loadingPenilaian'), emptyPenilaian: $('#emptyPenilaian'),
        contentPenilaian: $('#contentPenilaian'), loadingFormulir: $('#loadingFormulir'), selectPeringkat: $('#select_peringkatPenilaian'),
        rowRanking: $('.row-ranking'), bodyContentPeringkat: $('#bodyContentPeringkat'), contentKPIPersonal: $('#contentKPIPersonal'),
        contentKPITim: $('#contentKPITim'), contentKPIDivisi: $('#contentKPIDivisi'), companyProgressRow: $('#companyProgressRow'),
        companyProgressSkeleton: $('#companyProgressSkeleton'), companyProgressChartWrap: $('#companyProgressChartWrap'),
        companyTrendBadge: $('#companyTrendBadge'), companyDivisiOverview: $('#companyDivisiOverview'), divisiCount: $('#divisiCount'),
        kpiAlertsContainer: $('#kpiAlertsContainer'), alertsCount: $('#alertsCount'), upcomingDeadlinesContainer: $('#upcomingDeadlinesContainer'),
        heatmapGrid: $('#heatmapGrid'), heatmapMetricSelect: $('#heatmapMetricSelect'), activityTimelineContainer: $('#activityTimelineContainer'),
        achievementsContainer: $('#achievementsContainer'), achievementsCount: $('#achievementsCount'), newsContainer: $('#newsContainer'),
        newsCount: $('#newsCount'), statProjects: $('#stat_projects'), statProjectsTrend: $('#stat_projects_trend'),
        statAchieved: $('#stat_achieved'), statTotalTargets: $('#stat_total_targets'), statDeadlines: $('#stat_deadlines'),
        statEngagement: $('#stat_engagement'), statEngagementLabel: $('#stat_engagement_label'), heatmapDivisiSelect: $('#heatmapDivisiSelect'),
        heatmapMetricDescText: $('#heatmapMetricDescText'),heatmapDivisiSelect: $('#heatmapDivisiSelect'), heatmapMetricDescText: $('#heatmapMetricDescText'),
    };

    const charts = { penilaian: null, formulir: null, healthDonut: null, assessmentRadar: null, companyProgress: null, drilldown: null };
    let dashboardData = null;
    let heatmapData = null;
    let currentDivisiList = [];
    let currentHeatmapData = {};

    function destroyChart(chartName) {
        if (charts[chartName]) { charts[chartName].destroy(); charts[chartName] = null; }
    }

    function createGradient(ctx, color1, color2, height = 200) {
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, color1); gradient.addColorStop(1, color2);
        return gradient;
    }

    function getHeatmapClass(value) {
        if (value <= 0) return 'heatmap-empty';
        if (value < 20) return 'heatmap-1';
        if (value < 40) return 'heatmap-2';
        if (value < 60) return 'heatmap-3';
        if (value < 80) return 'heatmap-4';
        return 'heatmap-5';
    }

    function animateCounter(el, target) {
        el.css('min-height', '24px');
        const duration = 1000; const step = target / (duration / 16); let current = 0;
        const interval = setInterval(() => {
            current += step;
            if (current >= target) { current = target; clearInterval(interval); }
            el.text(Math.floor(current));
        }, 16);
    }

    function init() {
        loadData();

        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                loadProgressData();
                fetchChartStatistics();
            });
        } else {
            setTimeout(() => {
                loadProgressData();
                fetchChartStatistics();
            }, 200);
        }

        loadProgressData();
        fetchChartStatistics();

        $('#modalSakit').on('shown.bs.modal', () => renderModalData('Sakit'));
        $('#modalIzin').on('shown.bs.modal', () => renderModalData('Izin'));
        $('#modalCuti').on('shown.bs.modal', () => renderModalData('Cuti'));
        
        $('#heatmapMetricSelect').off('change');
        
        // Event Metric berubah
        $('#heatmapMetricSelect').off('change').on('change', function () {
            const metric = $(this).val();
            const selectedDivisi = DOM.heatmapDivisiSelect.val();
            if (currentHeatmapData && currentDivisiList.length > 0) {
                renderHeatmap(currentHeatmapData, metric, currentDivisiList, selectedDivisi);
            }
        });

        // Event Divisi berubah (baru)
        $('#heatmapDivisiSelect').off('change').on('change', function () {
            const selectedDivisi = $(this).val();
            const metric = DOM.heatmapMetricSelect.val() || 'progress';
            if (currentHeatmapData && currentDivisiList.length > 0 && selectedDivisi) {
                renderHeatmap(currentHeatmapData, metric, currentDivisiList, selectedDivisi);
            }
        });
    }

    function loadData() {
        $.ajax({
            url: window.dashboardContentUrl, 
            type: "GET", 
            dataType: "json",
            success: function(response) {
                renderSummaryCards(response.dataCard_first);
                renderPenilaianChart(response.dataChartPenilaian);
                renderFormulirChart(response.dataFormulir);
                renderRanking(response.dataDivisi, response.dataRangking);
                
                renderQuickStats(response.quick_stats);
                
                if (window.isExecutive) {
                    renderKPIAlerts(response.kpi_alerts);
                    renderUpcomingDeadlines(response.upcoming_deadlines);
                    
                    if (response.heatmap_data) {
                        currentHeatmapData = response.heatmap_data;

                        const rawDivisiList = response.divisi_list || response.dataDivisi.map(d => d.divisi);
                        currentDivisiList = rawDivisiList.filter(divisi => {
                            return divisi && 
                                divisi.trim() !== '' && 
                                divisi !== 'Pilih Divisi' && 
                                !divisi.startsWith('Pilih');
                        });

                        if (currentDivisiList.length > 0) {
                            // Default divisi = divisi user yang login
                            const defaultDivisi = window.userDivisi && currentDivisiList.includes(window.userDivisi)
                                ? window.userDivisi
                                : currentDivisiList[0];

                            renderHeatmap(currentHeatmapData, 'progress', currentDivisiList, defaultDivisi);
                        } else {
                            DOM.heatmapGrid.html('<div class="text-center text-muted py-4">Tidak ada data divisi yang valid</div>');
                        }
                    }
                    
                    renderActivityTimeline(response.activity_timeline);
                    renderAchievements(response.achievements);
                    renderNews(response.news);
                }
            },
            error: function(xhr, status, error) {
                [DOM.contentJK, DOM.contentKS, DOM.contentKC, DOM.contentKI].forEach(el => el.text("-"));
            }
        });
    }

    function renderSummaryCards(data) {
        if (!data) return; dashboardData = data;
        const sakit = data.dataSakit?.totalAbsenSakit ?? 0;
        const cuti = data.dataCuti?.totalAbsenCuti ?? 0;
        const izin = data.dataIzin?.totalAbsenIzin ?? 0;
        const aktif = data.karyawan_aktif ?? 0;
        const role = window.userJabatan || "";
        const labelType = (role === "HRD" || role === "GM" || role === "Direktur Utama") ? "Karyawan" : "Data";
        
        DOM.contentJK.html(`<span class="fw-bold">${aktif}</span>`);
        DOM.contentKS.html(`<span class="fw-bold">${sakit}</span> <small class="text-muted">${labelType}</small>`);
        DOM.contentKC.html(`<span class="fw-bold">${cuti}</span> <small class="text-muted">${labelType}</small>`);
        DOM.contentKI.html(`<span class="fw-bold">${izin}</span> <small class="text-muted">${labelType}</small>`);
    }

    function renderQuickStats(data) {
        if (!data) return;
        animateCounter(DOM.statProjects, data.projects || 0);
        DOM.statProjectsTrend.text((data.projects_trend || 0) + '%');
        animateCounter(DOM.statAchieved, data.achieved || 0);
        DOM.statTotalTargets.text(data.total_targets || 0);
        animateCounter(DOM.statDeadlines, data.deadlines || 0);
        animateCounter(DOM.statEngagement, data.engagement || 0);
        
        let label = 'Cukup';
        if (data.engagement >= 85) label = 'Sangat Baik';
        else if (data.engagement >= 70) label = 'Baik';
        DOM.statEngagementLabel.text(label);
    }

    function renderPenilaianChart(dataChart) {
        const totalSemua = dataChart?.totalSemua ?? 0;
        DOM.titleChart.empty().append(totalSemua ? `Penilaian Yang Diadakan : ${totalSemua} Penilaian` : "");
        DOM.loadingPenilaian.addClass("d-none");

        if (totalSemua > 0) {
            DOM.emptyPenilaian.addClass("d-none").removeClass("d-flex");
            DOM.contentPenilaian.show();
            const canvas = document.getElementById("myChart");
            if (!canvas) return;
            destroyChart('penilaian');
            const ctx = canvas.getContext("2d");
            charts.penilaian = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Dilaksanakan", "Belum Dilaksanakan"],
                    datasets: [{
                        data: [Number(dataChart.totalDilaksanakan ?? 0), Number(dataChart.totalBelumDilaksanakan ?? 0)],
                        backgroundColor: [createGradient(ctx, "#8F87F1", "#FED2E2", 300), createGradient(ctx, "#fbbf24", "#f59e0b", 300)],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        } else {
            DOM.contentPenilaian.hide();
            DOM.emptyPenilaian.removeClass("d-none").addClass("d-flex");
            destroyChart('penilaian');
        }
    }

    function renderFormulirChart(dataFormulir) {
        const TotalFormulir = dataFormulir?.totalFormulir ?? 0;
        DOM.loadingFormulir.addClass("d-none");
        if (TotalFormulir > 0) {
            const canvas = document.getElementById("doughnutCharthr");
            if (!canvas) return;
            destroyChart('formulir');
            const ctx = canvas.getContext("2d");
            charts.formulir = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Total", "Rutin", "Probation", "Kontrak"],
                    datasets: [{
                        data: [Number(dataFormulir.totalFormulir), Number(dataFormulir.totalRutin), Number(dataFormulir.totalProbation), Number(dataFormulir.totalKontrak)],
                        backgroundColor: [createGradient(ctx, "#a78bfa", "#7c3aed"), createGradient(ctx, "#38bdf8", "#0284c7"), createGradient(ctx, "#fbbf24", "#f59e0b"), createGradient(ctx, "#34d399", "#059669")],
                        borderWidth: 0
                    }]
                },
                options: { plugins: { legend: { display: false } }, responsive: true, maintainAspectRatio: false }
            });
            const createLegend = (val, label, c1, c2) => `<p class="fw-bold fs-5 mb-1">${val}</p><p class="mb-0 small d-flex align-items-center text-muted"><span class="me-2 legend-box" style="background:linear-gradient(135deg,${c1},${c2});"></span> ${label}</p>`;
            $("#totalFormulir").html(createLegend(dataFormulir.totalFormulir, "Total", "#a78bfa", "#7c3aed"));
            $("#totalRutin").html(createLegend(dataFormulir.totalRutin, "Rutin", "#38bdf8", "#0284c7"));
            $("#totalProbation").html(createLegend(dataFormulir.totalProbation, "Probation", "#fbbf24", "#f59e0b"));
            $("#totalKontrak").html(createLegend(dataFormulir.totalKontrak, "Kontrak", "#34d399", "#059669"));
        } else {
            destroyChart('formulir');
            $("#totalFormulir, #totalRutin, #totalProbation, #totalKontrak").html('<span class="text-muted small">-</span>');
        }
    }

    function renderRanking(divisiList, rankingData) {
        DOM.selectPeringkat.off("change").empty();
        if (!divisiList || !divisiList.length) {
            DOM.rowRanking.html('<div class="col-12 text-center text-muted py-5">Belum ada data divisi</div>');
            return;
        }
        divisiList.forEach(d => DOM.selectPeringkat.append(`<option value="${d.divisi}">${d.divisi}</option>`));
        const defaultDivisi = divisiList[0].divisi;
        DOM.selectPeringkat.val(defaultDivisi);
        processRankingData(defaultDivisi, rankingData);
        DOM.selectPeringkat.on("change", function() { processRankingData($(this).val(), rankingData); });
    }

    function processRankingData(divisi, allData) {
        const dataFiltered = (allData || []).filter(i => i.divisi === divisi && Number(i.total_nilai) > 0).sort((a, b) => b.total_nilai - a.total_nilai);
        DOM.rowRanking.empty(); DOM.bodyContentPeringkat.empty();
        $("#title_peringkat").text(`Terbaik Divisi ${divisi}`);

        if (!dataFiltered.length) {
            DOM.rowRanking.html('<div class="col-12"><div class="p-4 text-center rounded-4 bg-light"><h6 class="text-muted mt-3">Belum ada karyawan yang memiliki peringkat di divisi ini</h6></div></div>');
            return;
        }

        const baseUrl = window.assetStorageUrl; const defaultFoto = window.defaultProfileUrl;
        dataFiltered.slice(0, 3).forEach((item, index) => {
            const posisi = index + 1;
            const podiumClass = posisi === 1 ? 'podium-1' : posisi === 2 ? 'podium-2' : 'podium-3';
            const foto = item.foto ? `${baseUrl}/${item.foto}` : defaultFoto;
            DOM.rowRanking.append(`
                <div class="col-12 col-sm-6 col-lg-4 text-center mb-4">
                    <div class="ranking-card p-4 rounded-4 text-white ${podiumClass}" style="box-shadow: 0 6px 20px rgba(0,0,0,.1);">
                        <img src="${foto}" width="110" height="110" loading="lazy" decoding="async"
                            class="rounded-circle border border-4 border-white my-3" 
                            style="width:110px;height:110px;object-fit:cover;">
                        <h6 class="fw-bold mb-1">${item.nama_karyawan}</h6><small class="opacity-75">${item.divisi}</small>
                    </div>
                    <h5 class="mt-3 fw-bold text-dark">${posisi}</h5>
                </div>`);
        });

        let lastScore = null, rank = 0, shown = 0;
        DOM.bodyContentPeringkat.html(dataFiltered.map(item => {
            if (item.total_nilai !== lastScore) { rank = shown + 1; }
            lastScore = item.total_nilai; shown++;
            return `<div class="d-flex align-items-center mb-3 p-3 rounded-3 plain-card">
                <span class="me-3 fw-bold text-primary" style="min-width:28px;">${rank}.</span>
                <div class="flex-grow-1"><div class="fw-bold text-dark">${item.nama_karyawan}</div><small class="text-muted">${item.divisi}</small></div>
                <div class="fw-bold me-3 text-dark">${item.total_nilai}</div>
                <div class="progress flex-grow-1" style="max-width:250px;height:10px;background:#f1f5f9;"><div class="progress-bar" style="width:${item.total_nilai}%;background:linear-gradient(90deg,#6366f1,#a78bfa);"></div></div>
            </div>`;
        }).join(''));
    }

    function renderModalData(type) {
        const loadingId = `#loadingModal${type}`; const contentId = `#contentModal${type}`; const dataKey = `data${type}`;
        $(loadingId).show(); $(contentId).addClass('d-none').empty();
        setTimeout(() => {
            $(loadingId).hide(); $(contentId).removeClass('d-none');
            if (!dashboardData || !dashboardData[dataKey]) { $(contentId).html('<p class="text-center text-muted py-4">Data tidak tersedia.</p>'); return; }
            const items = dashboardData[dataKey][dataKey] || []; const total = dashboardData[dataKey][`totalAbsen${type}`] || 0;
            if (items.length === 0) {
                $(contentId).html(`<div class="text-center py-4"><i class="fas fa-inbox fa-2x text-muted mb-2 opacity-50"></i><p class="text-muted mb-0">Tidak ada data ${type.toLowerCase()} untuk periode ini.</p></div>`);
            } else {
                const rows = items.map(item => {
                    const tanggal = item.tanggalAwal === item.tanggalAkhir ? item.tanggalAwal : `${item.tanggalAwal} s/d ${item.tanggalAkhir}`;
                    return `<tr><td class="fw-semibold text-dark">${item.namaKaryawan || '-'}</td><td><span class="badge bg-light text-dark border">${item.divisi || '-'}</span></td><td>${item.alasan || '-'}</td><td><small class="text-muted"><i class="far fa-calendar-alt me-1"></i>${tanggal}</small></td></tr>`;
                }).join('');
                $(contentId).html(`<div class="d-flex justify-content-between align-items-center mb-3"><h6 class="fw-bold mb-0">Total: ${total} Catatan</h6></div><div class="table-responsive"><table class="table table-hover align-middle table-sm"><thead class="table-light"><tr><th>Nama Karyawan</th><th>Divisi</th><th>Alasan</th><th>Tanggal</th></tr></thead><tbody>${rows}</tbody></table></div>`);
            }
        }, 300);
    }

    function loadProgressData() {
        $.ajax({
            url: window.progressDashboardUrl, type: 'GET', dataType: 'json',
            success: function(response) {
                if (DOM.contentKPIPersonal.length) renderOutput1(response.output_1);
                if (DOM.contentKPITim.length) renderOutput2(response.output_2);
                if (DOM.contentKPIDivisi.length) renderOutput3(response.output_3);
                renderOutput4(response.output_4);
            },
            error: function() {
                if (DOM.contentKPIPersonal.length) DOM.contentKPIPersonal.html('<div class="text-center py-5 text-warning">Gagal memuat data personal</div>');
                if (DOM.contentKPITim.length) DOM.contentKPITim.html('<div class="text-center py-5 text-warning">Gagal memuat data tim</div>');
                if (DOM.contentKPIDivisi.length) DOM.contentKPIDivisi.html('<div class="text-center py-4 text-warning">Gagal memuat data assessment</div>');
                DOM.companyProgressRow.hide();
            }
        });
    }

    function renderOutput1(data) {
        if (!DOM.contentKPIPersonal.length) return; DOM.contentKPIPersonal.empty();
        if (!data || data.titleGet_data === "Tidak ada data") {
            DOM.contentKPIPersonal.html(`<div class="d-flex flex-column justify-content-center align-items-center text-center h-100 py-5"><div style="font-size:60px;color:#a78bfa;">≈</div><h5 class="fw-semibold mt-3 mb-2 text-dark">Belum Ada Data KPI</h5><p class="text-muted small mb-4" style="max-width:320px;">Data performa personal belum tersedia.</p><span class="badge bg-light text-muted px-3 py-2">Menunggu Data</span></div>`);
            return;
        }
        let performanceColor = "warning", performanceIcon = "";
        if (data.performance_title === "Naik") { performanceColor = "success"; performanceIcon = "↑"; }
        else if (data.performance_title === "Turun") { performanceColor = "warning"; performanceIcon = "↓"; }
        
        let monthlyHTML = "";
        data.progress_kpi_perbulan.forEach((item, index) => {
            monthlyHTML += `<div class="col"><div class="fw-semibold ${index === data.progress_kpi_perbulan.length - 1 ? 'text-primary fw-bold' : 'text-dark'}">${item.nilai}%</div><div class="small text-muted">${item.bulan.split(" ")[0].substring(0, 3)}</div></div>`;
        });

        DOM.contentKPIPersonal.html(`
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div><h1 class="fw-bold mb-1 text-dark">${data.nilai_kpi_anda}%</h1><div class="small fw-semibold text-${performanceColor}"><i class="fas fa-arrow-${performanceIcon === '↑' ? 'up' : performanceIcon === '↓' ? 'down' : 'right'} me-1"></i>${performanceIcon} ${data.performance}% dari bulan lalu</div></div>
                <div class="text-end"><div class="small text-muted mt-2">Performa ${data.performance_title}</div></div>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between small text-muted mb-2"><span>Progress KPI</span><span class="fw-semibold text-dark">${data.nilai_kpi_anda}%</span></div>
                <div class="progress" style="height:8px;background:#f1f5f9;"><div class="progress-bar progress-animated" data-value="${data.nilai_kpi_anda}" style="width:0%;background:linear-gradient(90deg,#6366f1,#a78bfa);"></div></div>
            </div>
            <div class="border-top pt-3 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="small text-muted">Deadline</div><div class="fw-semibold text-dark">${new Date(data.deadline).toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' })}</div></div>
                    <span class="badge bg-light text-dark">${data.countdown}</span>
                </div>
            </div>
            <div class="mt-auto"><div class="small text-muted mb-3">Riwayat Bulanan</div><div class="row text-center g-3">${monthlyHTML}</div></div>`);
        setTimeout(() => { $('.progress-animated').each(function() { $(this).css('width', $(this).data('value') + '%'); }); }, 200);
    }

    function renderOutput2(data) {
        const container = DOM.contentKPITim; if (!container.length) return; container.empty();
        if (!data || (data.total_kpi_tracked ?? 0) === 0) { container.append('<div class="text-center text-muted py-4">Belum ada data KPI</div>'); return; }
        
        const summary = data.summary_cards || { total_kpi_tracked: 0, avg_progress: 0, on_track_percentage: 0 };
        const health = data.health_donut || { on_track: 0, at_risk: 0, behind: 0 };
        const kpiCards = data.kpi_cards || [];

        container.append(`<div class="row g-3">
            <div class="col-4"><div class="kpi-summary-box text-center"><div class="fw-bold fs-4 text-dark">${summary.total_kpi_tracked}</div><div class="small text-muted">Total KPI</div></div></div>
            <div class="col-4"><div class="kpi-summary-box text-center"><div class="fw-bold fs-4 text-dark">${summary.avg_progress}%</div><div class="small text-muted">Rata-rata Progress</div></div></div>
            <div class="col-4"><div class="kpi-summary-box text-center"><div class="fw-bold fs-4 text-success">${summary.on_track_percentage}%</div><div class="small text-muted">On Track</div></div></div>
        </div>`);

        container.append(`<div class="row g-3 align-items-center">
            <div class="col-5"><canvas id="kpiHealthDonutChart" height="140"></canvas></div>
            <div class="col-7 d-flex flex-column gap-2">
                <div class="health-legend-item"><span class="legend-box" style="background:#34d399;"></span> On Track: ${health.on_track}</div>
                <div class="health-legend-item"><span class="legend-box" style="background:#fbbf24;"></span> At Risk: ${health.at_risk}</div>
                <div class="health-legend-item"><span class="legend-box" style="background:#ef4444;"></span> Behind: ${health.behind}</div>
            </div>
        </div>`);

        let kpiCardsHtml = '<div class="row g-3">';
        kpiCards.forEach(card => {
            const values = Object.values(card.sparkline || {}); const max = Math.max(1, ...values.map(v => Number(v) || 0));
            let bars = ''; values.forEach(v => { bars += `<div class="sparkline-bar" style="height:${Math.max(8, Math.round((Number(v) / max) * 100))}%;"></div>`; });
            let statusColor = card.status === 'on_track' ? 'success' : card.status === 'behind' ? 'danger' : 'warning';
            let trendIcon = card.trend === 'up' ? '↑' : card.trend === 'down' ? '↓' : '∿';
            kpiCardsHtml += `<div class="col-md-6"><div class="kpi-mini-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0 fw-semibold text-dark text-truncate" style="max-width:70%;" title="${card.kpi_title}">${card.kpi_title}</h6>
                    <span class="badge bg-${statusColor} bg-opacity-10 text-${statusColor} fw-bold">${card.progress}%</span>
                </div><div class="sparkline mb-2">${bars}</div><small class="text-muted">${trendIcon} ${card.trend_value}% tren</small>
            </div></div>`;
        });
        container.append(kpiCardsHtml + '</div>');
        
        const donutCanvas = document.getElementById('kpiHealthDonutChart');
        if (donutCanvas) {
            destroyChart('healthDonut');
            charts.healthDonut = new Chart(donutCanvas.getContext('2d'), {
                type: 'doughnut', data: { labels: ['On Track', 'At Risk', 'Behind'], datasets: [{ data: [health.on_track, health.at_risk, health.behind], backgroundColor: ['#34d399', '#fbbf24', '#ef4444'], borderWidth: 0 }] },
                options: { plugins: { legend: { display: false } }, responsive: true, maintainAspectRatio: false }
            });
        }
    }

    function renderOutput3(data) {
        const container = DOM.contentKPIDivisi; if (!container.length) return; container.empty();
        if (!data || (data.total_feedback_received ?? 0) === 0) { container.html('<div class="d-flex justify-content-center align-items-center w-100 text-muted py-4"><span class="small">Belum ada data penilaian.</span></div>'); return; }
        
        container.append(`<div class="text-center mb-3"><h2 class="fw-bold text-dark mb-0">${data.total_score}%</h2><small class="text-muted">Total Skor Penilaian 360°</small></div><div style="height:260px;"><canvas id="assessment360RadarChart"></canvas></div>`);
        
        let barsHtml = '<div class="mt-3">';
        (data.breakdown_bars || []).forEach(item => {
            let barColor = item.rata_rata_nilai >= 80 ? 'linear-gradient(90deg,#34d399,#059669)' : item.rata_rata_nilai >= 50 ? 'linear-gradient(90deg,#6366f1,#a78bfa)' : 'linear-gradient(90deg,#fbbf24,#f59e0b)';
            barsHtml += `<div class="mb-2"><div class="d-flex justify-content-between small text-muted mb-1"><span>${item.jenis}</span><span class="fw-semibold text-dark">${item.rata_rata_nilai}</span></div><div class="progress" style="height:6px;background:#f1f5f9;"><div class="progress-bar" style="width:${item.rata_rata_nilai}%;background:${barColor};"></div></div></div>`;
        });
        container.append(barsHtml + '</div>');

        const radarCanvas = document.getElementById('assessment360RadarChart');
        if (radarCanvas) {
            const radarData = data.radar_chart || [];
            destroyChart('assessmentRadar');
            charts.assessmentRadar = new Chart(radarCanvas.getContext('2d'), {
                type: 'radar', data: { labels: radarData.map(item => item.axis), datasets: [{ label: 'Nilai', data: radarData.map(item => item.value), backgroundColor: 'rgba(99,102,241,0.15)', borderColor: '#6366f1', pointBackgroundColor: '#6366f1' }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { r: { beginAtZero: true, max: 5 } }, plugins: { legend: { display: false } } }
            });
        }
    }

    function renderOutput4(data) {
        const row = DOM.companyProgressRow; if (!data) { row.hide(); return; }
        row.show(); DOM.companyProgressSkeleton.hide(); DOM.companyProgressChartWrap.show();

        const trend = data.company_trend || { historical: {}, forecast: {} };
        const historical = trend.historical || {}; const forecast = trend.forecast || {}; const overview = data.overview || [];
        const historicalLabels = Object.keys(historical); const forecastLabels = Object.keys(forecast);
        const allLabels = [...historicalLabels, ...forecastLabels];
        const historicalValues = historicalLabels.map(k => historical[k]);
        const lastHistoricalValue = historicalValues.length ? historicalValues[historicalValues.length - 1] : null;
        
        const solidData = [...historicalValues, ...forecastLabels.map(() => null)];
        const dashedData = [...historicalLabels.map(() => null)];
        if (historicalLabels.length > 0) dashedData[historicalLabels.length - 1] = lastHistoricalValue;
        forecastLabels.forEach(k => dashedData.push(forecast[k]));

        const canvas = document.getElementById('companyProgressChart');
        if (canvas) {
            destroyChart('companyProgress');
            charts.companyProgress = new Chart(canvas.getContext('2d'), {
                type: 'line', data: {
                    labels: allLabels, datasets: [
                        { label: 'Historis', data: solidData, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', borderWidth: 3, tension: 0.4, fill: true, spanGaps: false, pointBackgroundColor: '#fff', pointBorderColor: '#6366f1', pointBorderWidth: 2, pointRadius: 4 },
                        { label: 'Proyeksi', data: dashedData, borderColor: '#f59e0b', backgroundColor: 'transparent', borderWidth: 3, borderDash: [6, 6], tension: 0.4, fill: false, spanGaps: true, pointBackgroundColor: '#f59e0b', pointBorderColor: '#f59e0b', pointRadius: 4 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'top' } }, scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } } }
            });
        }

        const totalHistorical = historicalValues.reduce((a, b) => a + b, 0);
        const avgHistorical = historicalValues.length ? totalHistorical / historicalValues.length : 0;
        const forecastValues = Object.values(forecast);
        const avgForecast = forecastValues.length ? forecastValues.reduce((a, b) => a + b, 0) / forecastValues.length : 0;
        const trendDiff = avgForecast - avgHistorical;
        const trendColor = trendDiff > 0 ? 'success' : trendDiff < 0 ? 'warning' : 'muted';
        const trendIcon = trendDiff > 0 ? '↑' : trendDiff < 0 ? '↓' : '∿';
        DOM.companyTrendBadge.html(`<span class="badge bg-${trendColor} bg-opacity-10 text-${trendColor} fw-semibold">${trendIcon} ${Math.abs(trendDiff).toFixed(1)}% tren</span>`);

        DOM.divisiCount.text(`${overview.length} Divisi`);
        DOM.companyDivisiOverview.empty();
        if (overview.length === 0) { DOM.companyDivisiOverview.html('<div class="text-center text-muted py-4 small">Belum ada data divisi.</div>'); return; }
        
        overview.forEach(item => {
            let colorClass = item.avg_progress >= 80 ? 'success' : item.avg_progress >= 50 ? 'primary' : 'warning';
            let barColor = item.avg_progress >= 80 ? 'linear-gradient(90deg,#34d399,#059669)' : item.avg_progress >= 50 ? 'linear-gradient(90deg,#6366f1,#a78bfa)' : 'linear-gradient(90deg,#fbbf24,#f59e0b)';
            const predictionDiff = item.avg_prediction - item.avg_progress;
            const predictionIcon = predictionDiff > 0 ? '↑' : predictionDiff < 0 ? '↓' : '∿';
            const predictionColor = predictionDiff > 0 ? 'success' : predictionDiff < 0 ? 'warning' : 'muted';
            DOM.companyDivisiOverview.append(`
                <div class="kpi-mini-card divisi-overview-item" style="cursor:pointer;" data-divisi="${item.divisi}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div><h6 class="mb-0 fw-semibold text-dark">${item.divisi}</h6><small class="text-muted">${item.total_kpi} KPI · ${item.total_karyawan} Karyawan</small></div>
                        <span class="badge bg-${colorClass} bg-opacity-10 text-${colorClass} fw-bold">${item.avg_progress}%</span>
                    </div>
                    <div class="progress mb-2" style="height:6px;background:#e2e8f0;"><div class="progress-bar" style="width:${item.avg_progress}%;background:${barColor};"></div></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-${predictionColor}">${predictionIcon} Proyeksi: ${item.avg_prediction}%</small>
                        <small class="text-muted"><span class="text-success">${item.health.on_track}</span>/<span class="text-warning">${item.health.at_risk}</span>/<span class="text-danger">${item.health.behind}</span></small>
                    </div>
                </div>`);
        });
        $('.divisi-overview-item').off('click').on('click', function() { openDivisiDrilldown($(this).data('divisi')); });
    }

    function openDivisiDrilldown(divisi) {
        $('#drilldownDivisiTitle').text('Detail Divisi: ' + divisi);
        $('#drilldownLoading').show(); $('#drilldownContent').addClass('d-none'); $('#drilldownEmpty').addClass('d-none');
        $('#modalDivisiDrilldown').modal('show');
        $.ajax({
            url: window.divisiDrilldownUrl, type: 'GET', data: { divisi: divisi }, dataType: 'json',
            success: function(response) { $('#drilldownLoading').hide(); renderDrilldown(response); },
            error: function() { $('#drilldownLoading').hide(); $('#drilldownEmpty').removeClass('d-none').text('Gagal memuat detail divisi.'); }
        });
    }

    function renderDrilldown(data) {
        const team = data.team || []; const monthlyProgress = data.monthly_progress || {}; const insights = data.insights || [];
        if (team.length === 0 && Object.keys(monthlyProgress).length === 0) { $('#drilldownEmpty').removeClass('d-none'); return; }
        $('#drilldownContent').removeClass('d-none');
        
        const chartCanvas = document.getElementById('drilldownChart');
        if (chartCanvas) {
            destroyChart('drilldown');
            charts.drilldown = new Chart(chartCanvas.getContext('2d'), {
                type: 'line', data: { labels: Object.keys(monthlyProgress), datasets: [{ label: 'Progress Divisi (%)', data: Object.values(monthlyProgress), borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: '#fff', pointBorderColor: '#6366f1', pointRadius: 4 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } } }
            });
        }

        const insightsContainer = $('#drilldownInsights'); insightsContainer.empty();
        if (insights.length === 0) insightsContainer.html('<div class="text-muted small">Belum ada insight.</div>');
        else insights.forEach(item => { insightsContainer.append(`<div class="p-2 rounded-3" style="background:#f8fafc;"><div class="fw-semibold text-dark small">${item.kpi_title}</div><div class="small text-muted">${item.insight}</div></div>`); });

        const teamContainer = $('#drilldownTeamList'); teamContainer.empty();
        if (team.length === 0) { teamContainer.html('<div class="text-center text-muted py-4 small">Belum ada anggota tim.</div>'); return; }
        team.forEach((member, index) => {
            let barColor = member.progress >= 80 ? 'linear-gradient(90deg,#34d399,#059669)' : member.progress >= 50 ? 'linear-gradient(90deg,#6366f1,#a78bfa)' : 'linear-gradient(90deg,#fbbf24,#f59e0b)';
            let trendIcon = member.trend === 'up' ? '↑' : member.trend === 'down' ? '↓' : '∿';
            let trendColor = member.trend === 'up' ? 'success' : member.trend === 'down' ? 'warning' : 'muted';
            teamContainer.append(`
                <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f8fafc;">
                    <span class="fw-bold text-primary" style="min-width:24px;">${index + 1}.</span>
                    <div class="flex-grow-1"><div class="fw-semibold text-dark small">${member.nama_karyawan}</div><small class="text-muted">${member.jabatan}</small></div>
                    <div class="text-end" style="min-width:70px;"><div class="fw-bold text-dark small">${member.progress}%</div><small class="text-${trendColor}">${trendIcon}</small></div>
                    <div class="progress flex-grow-1" style="max-width:150px;height:6px;background:#e2e8f0;"><div class="progress-bar" style="width:${member.progress}%;background:${barColor};"></div></div>
                </div>`);
        });
    }

    function renderKPIAlerts(alerts) {
        if (!alerts || alerts.length === 0) {
            DOM.alertsCount.text('0 Alerts');
            DOM.kpiAlertsContainer.html('<div class="text-center text-muted py-4 small">Tidak ada alert saat ini.</div>');
            return;
        }
        DOM.alertsCount.text(`${alerts.length} Alerts`); DOM.kpiAlertsContainer.empty();
        alerts.forEach(alert => {
            const iconClass = alert.type === 'behind' ? 'fa-exclamation-triangle text-danger' : alert.type === 'at-risk' ? 'fa-exclamation-circle text-warning' : 'fa-check-circle text-success';
            DOM.kpiAlertsContainer.append(`
                <div class="alert-kpi-item ${alert.type}">
                    <div class="d-flex align-items-start gap-3">
                        <i class="fas ${iconClass} mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold text-dark mb-1">${alert.title}</h6>
                                <span class="badge bg-${alert.type === 'behind' ? 'danger' : alert.type === 'at-risk' ? 'warning' : 'success'} bg-opacity-10 text-${alert.type === 'behind' ? 'danger' : alert.type === 'at-risk' ? 'warning' : 'success'} small">${alert.priority}</span>
                            </div>
                            <p class="text-muted small mb-1">${alert.desc}</p>
                            <small class="text-muted"><i class="far fa-clock me-1"></i>${alert.time}</small>
                        </div>
                    </div>
                </div>`);
        });
    }

    function renderUpcomingDeadlines(deadlines) {
        if (!deadlines || deadlines.length === 0) {
            DOM.upcomingDeadlinesContainer.html('<div class="text-center text-muted py-4 small">Tidak ada deadline terdekat.</div>');
            return;
        }
        DOM.upcomingDeadlinesContainer.empty();
        const priorityColor = { 'Overdue': 'danger', 'High': 'danger', 'Medium': 'warning', 'Low': 'success' };

        deadlines.forEach(dl => {
            const color = priorityColor[dl.priority] || 'secondary';
            const dateBoxStyle = dl.priority === 'Overdue' ? 'background:linear-gradient(135deg,#ef4444,#b91c1c);' : 'background:linear-gradient(135deg,#6366f1,#a78bfa);';
            const $item = $(`
                <div class="deadline-item" style="cursor:pointer;"
                    data-kode-form="${dl.kode_form}" data-id-evaluator="${dl.id_evaluator}"
                    data-id-evaluated="${dl.id_evaluated}" data-jenis="${dl.jenis_penilaian}">
                    <div class="deadline-date" style="${dateBoxStyle}"><div class="day">${dl.day}</div><div class="month">${dl.month}</div></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark small mb-1">${dl.title}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="fas fa-user-tie me-1"></i>${dl.assignee}</small>
                            <span class="badge bg-${color} bg-opacity-10 text-${color} small">${dl.priority}</span>
                        </div>
                    </div>
                </div>`);
            DOM.upcomingDeadlinesContainer.append($item);
        });

        DOM.upcomingDeadlinesContainer.find('.deadline-item').on('click', function() {
            openDeadlineDetail($(this).data('kode-form'), $(this).data('id-evaluator'), $(this).data('id-evaluated'), $(this).data('jenis'));
        });
    }

    const METRIC_DESCRIPTIONS = {
        progress: 'Progress menunjukkan rata-rata pencapaian target KPI divisi setiap bulan (0–100%).',
        completion: 'Completion menunjukkan persentase target yang sudah diselesaikan di divisi tersebut setiap bulan.',
        engagement: 'Engagement mengukur tingkat keterlibatan karyawan dalam pengisian / update KPI di divisi tersebut.'
    };

    function renderHeatmap(heatmapData, metric, divisiList, selectedDivisi = null) {
        if (!heatmapData || !divisiList || divisiList.length === 0) {
            DOM.heatmapGrid.html('<div class="text-center text-muted py-4 w-100">Tidak ada data untuk ditampilkan</div>');
            return;
        }

        // Update deskripsi
        DOM.heatmapMetricDescText.text(METRIC_DESCRIPTIONS[metric] || 'Pilih metric untuk melihat penjelasan.');

        // Tentukan divisi yang akan ditampilkan
        let targetDivisi = selectedDivisi;

        // Jika user HRD → boleh pilih
        if (window.isHRD) {
            const $divisiSelect = DOM.heatmapDivisiSelect;

            // Isi select hanya sekali
            if ($divisiSelect.length && $divisiSelect.find('option').length <= 1) {
                $divisiSelect.empty().append('<option value="">Pilih Divisi</option>');
                divisiList.forEach(d => {
                    if (d && d.trim() && d !== 'Pilih Divisi' && !d.startsWith('Pilih')) {
                        $divisiSelect.append(`<option value="${d}">${d}</option>`);
                    }
                });
            }

            // Ambil dari select, fallback ke divisi user atau pertama
            targetDivisi = $divisiSelect.val() || window.userDivisi || divisiList[0];

            if (targetDivisi && $divisiSelect.find(`option[value="${targetDivisi}"]`).length) {
                $divisiSelect.val(targetDivisi);
            }
        } else {
            // Non-HRD → paksa pakai divisi user yang login
            targetDivisi = window.userDivisi || divisiList[0];
        }

        if (!targetDivisi) {
            DOM.heatmapGrid.html('<div class="text-center text-muted py-4 w-100">Divisi tidak ditemukan</div>');
            return;
        }

        const divisiData = heatmapData[targetDivisi];
        if (!divisiData || !divisiData[metric]) {
            DOM.heatmapGrid.html(`
                <div class="text-center text-muted py-4 w-100">
                    Tidak ada data <strong>${metric}</strong> untuk divisi <strong>${targetDivisi}</strong>
                </div>
            `);
            return;
        }

        const values = divisiData[metric] || [];
        const monthLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        let html = '';
        values.forEach((val, idx) => {
            const adjustedVal = Math.max(0, Math.min(100, parseFloat(val) || 0));
            const displayVal = adjustedVal > 0 ? Math.round(adjustedVal) : '';
            const cellClass = getHeatmapClass(adjustedVal);

            html += `
                <div class="heatmap-month-col text-center" style="flex:1; min-width:48px;">
                    <div class="heatmap-cell ${cellClass}"
                        data-divisi="${targetDivisi}"
                        title="${targetDivisi} - ${monthLabels[idx]}: ${adjustedVal.toFixed(1)}%"
                        style="height:48px; display:flex; align-items:center; justify-content:center; 
                                font-size:13px; font-weight:600; border-radius:8px; cursor:pointer;">
                        ${displayVal}
                    </div>
                    <div class="small text-muted mt-1" style="font-size:11px; font-weight:500;">
                        ${monthLabels[idx]}
                    </div>
                </div>
            `;
        });

        DOM.heatmapGrid.html(html);

        // Click → drilldown
        DOM.heatmapGrid.find('.heatmap-cell').off('click').on('click', function () {
            const divisi = $(this).data('divisi');
            if (divisi) openDivisiDrilldown(divisi);
        });
    }

    function renderActivityTimeline(activities) {
        if (!activities || activities.length === 0) {
            DOM.activityTimelineContainer.html('<div class="text-center text-muted py-4 small">Belum ada aktivitas.</div>');
            return;
        }
        DOM.activityTimelineContainer.empty();
        activities.forEach(act => {
            const $item = $(`
                <div class="timeline-item" style="cursor:pointer;" data-id="${act.id}">
                    <div class="timeline-dot ${act.type}"></div>
                    <div>
                        <div class="fw-semibold text-dark small mb-1">${act.title}</div>
                        <div class="text-muted small mb-1">${act.desc}</div>
                        <small class="text-muted"><i class="far fa-clock me-1"></i>${act.time}</small>
                    </div>
                </div>`);
            DOM.activityTimelineContainer.append($item);
        });
        DOM.activityTimelineContainer.find('.timeline-item').on('click', function() {
            openActivityDetail($(this).data('id'));
        });
    }

    function renderAchievements(achievements) {
        if (!achievements || achievements.length === 0) {
            DOM.achievementsCount.text('0');
            DOM.achievementsContainer.html('<div class="text-center text-muted py-4 small">Belum ada pencapaian.</div>');
            return;
        }
        DOM.achievementsCount.text(achievements.length); DOM.achievementsContainer.empty();
        achievements.forEach(ach => {
            const $item = $(`
                <div class="achievement-item" style="cursor:pointer;" data-type="${ach.achievement_type}" data-ref-id="${ach.ref_id}">
                    <div class="achievement-icon"><i class="fas ${ach.icon}"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark small mb-1">${ach.title}</div>
                        <div class="text-muted small mb-1">${ach.desc}</div>
                        <small class="text-muted"><i class="far fa-clock me-1"></i>${ach.time}</small>
                    </div>
                </div>`);
            DOM.achievementsContainer.append($item);
        });
        DOM.achievementsContainer.find('.achievement-item').on('click', function() {
            openAchievementDetail($(this).data('type'), $(this).data('ref-id'));
        });
    }

    function renderNews(news) {
        if (!news || news.length === 0) {
            DOM.newsCount.text('0');
            DOM.newsContainer.html('<div class="text-center text-muted py-4 small">Belum ada pengumuman.</div>');
            return;
        }
        DOM.newsCount.text(news.length); DOM.newsContainer.empty();
        news.forEach(item => {
            const tagColor = item.tag === 'Administrasi' ? 'success' : item.tag === 'Target' ? 'primary' : 'info';
            const $item = $(`
                <div class="news-item" style="cursor:pointer;" data-type="${item.news_type}" data-ref-id="${item.ref_id}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-semibold text-dark mb-0 small">${item.title}</h6>
                        <span class="badge bg-${tagColor} bg-opacity-10 text-${tagColor} small">${item.tag}</span>
                    </div>
                    <p class="text-muted small mb-1">${item.desc}</p>
                    <small class="text-muted"><i class="far fa-clock me-1"></i>${item.time}</small>
                </div>`);
            DOM.newsContainer.append($item);
        });
        DOM.newsContainer.find('.news-item').on('click', function() {
            openNewsDetail($(this).data('type'), $(this).data('ref-id'));
        });
    }

    function fetchChartStatistics() {
        if ($('#chartStatisticsContainer').length === 0) return;
        $.ajax({
            url: window.chartStatsUrl, method: 'GET', dataType: 'json',
            success: function(response) {
                if (response.summary.total_targets === 0) return;
                $('#overallAverage').text(response.summary.overall_average);
                $('#totalTargets').text(response.summary.total_targets);
                $('#achievedTargets').text(response.summary.achieved_targets);
                $('#completionRate').text(response.summary.completion_rate + '%');
            }
        });
    }

    function openDeadlineDetail(kodeForm, idEvaluator, idEvaluated, jenis) {
        $('#deadlineDetailLoading').removeClass('d-none'); $('#deadlineDetailContent').addClass('d-none').empty();
        $('#modalDeadlineDetail').modal('show');
        $.ajax({
            url: window.deadlineDetailUrl, type: 'GET',
            data: { kode_form: kodeForm, id_evaluator: idEvaluator, id_evaluated: idEvaluated, jenis_penilaian: jenis },
            dataType: 'json',
            success: function(data) {
                $('#deadlineDetailLoading').addClass('d-none');
                const kategoriRows = (data.kategori_list || []).map(k => `
                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 mb-2" style="background:#f8fafc;">
                        <span class="small text-dark">${k.nama}</span>
                        <span class="badge ${k.status === 'Selesai' ? 'bg-success' : 'bg-warning'} bg-opacity-10 ${k.status === 'Selesai' ? 'text-success' : 'text-warning'} small">${k.status}</span>
                    </div>`).join('');
                $('#deadlineDetailContent').removeClass('d-none').html(`
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1">${data.evaluated} <small class="text-muted">(${data.divisi})</small></h6>
                        <small class="text-muted">Dinilai oleh ${data.evaluator} · Jenis: ${data.jenis_penilaian}</small>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1"><span>Progress Penilaian</span><span class="fw-semibold text-dark">${data.progress_percent}%</span></div>
                    <div class="progress mb-3" style="height:8px;background:#f1f5f9;"><div class="progress-bar" style="width:${data.progress_percent}%;background:linear-gradient(90deg,#6366f1,#a78bfa);"></div></div>
                    <small class="text-muted d-block mb-3"><i class="far fa-calendar me-1"></i>Ditugaskan sejak: ${data.assigned_at}</small>
                    <h6 class="fw-bold text-dark mb-2">Kategori Penilaian (${data.total_selesai}/${data.total_kategori})</h6>
                    ${kategoriRows || '<div class="text-muted small">Tidak ada kategori.</div>'}
                `);
            },
            error: function() {
                $('#deadlineDetailLoading').addClass('d-none');
                $('#deadlineDetailContent').removeClass('d-none').html('<div class="text-center text-muted py-4">Gagal memuat detail.</div>');
            }
        });
    }

    function openActivityDetail(id) {
        $('#activityDetailLoading').removeClass('d-none'); $('#activityDetailContent').addClass('d-none').empty();
        $('#modalActivityDetail').modal('show');
        $.ajax({
            url: window.activityDetailUrl, type: 'GET', data: { id: id }, dataType: 'json',
            success: function(data) {
                $('#activityDetailLoading').addClass('d-none');
                const riwayat = (data.riwayat_hari_ini || []).map(r => `
                    <div class="d-flex justify-content-between p-2 rounded-3 mb-2" style="background:#f8fafc;">
                        <span class="small text-dark">${r.status}</span><small class="text-muted">${r.time}</small>
                    </div>`).join('');
                $('#activityDetailContent').removeClass('d-none').html(`
                    <h6 class="fw-bold text-dark mb-1">${data.title}</h6>
                    <p class="text-muted small mb-1">${data.karyawan} · ${data.jabatan} · ${data.divisi}</p>
                    <small class="text-muted d-block mb-3"><i class="far fa-clock me-1"></i>${data.waktu}</small>
                    <h6 class="fw-bold text-dark mb-2">Riwayat Aktivitas Hari Ini</h6>
                    ${riwayat || '<div class="text-muted small">Tidak ada riwayat lain.</div>'}
                `);
            },
            error: function() {
                $('#activityDetailLoading').addClass('d-none');
                $('#activityDetailContent').removeClass('d-none').html('<div class="text-center text-muted py-4">Gagal memuat detail.</div>');
            }
        });
    }

    function openAchievementDetail(type, refId) {
        $('#achievementDetailLoading').removeClass('d-none'); $('#achievementDetailContent').addClass('d-none').empty();
        $('#modalAchievementDetail').modal('show');
        $.ajax({
            url: window.achievementDetailUrl, type: 'GET', data: { type: type, ref_id: refId }, dataType: 'json',
            success: function(data) {
                $('#achievementDetailLoading').addClass('d-none');
                if (data.type === 'target') {
                    const detailRows = (data.detail || []).map(d => `<div class="small text-muted mb-1">• ${d.jabatan}: ${d.keterangan}</div>`).join('');
                    $('#achievementDetailContent').removeClass('d-none').html(`
                        <h6 class="fw-bold text-dark mb-1">${data.judul}</h6>
                        <span class="badge bg-success bg-opacity-10 text-success mb-2">${data.status}</span>
                        <p class="small text-muted mb-1">Dibuat: ${data.dibuat}</p>
                        <p class="small text-muted mb-3">Selesai: ${data.selesai}</p>
                        ${detailRows}
                    `);
                } else {
                    const breakdownRows = (data.breakdown || []).map(b => `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-dark">${b.kategori}</span>
                            <span class="fw-semibold text-dark small">${b.nilai}</span>
                        </div>`).join('');
                    $('#achievementDetailContent').removeClass('d-none').html(`
                        <h6 class="fw-bold text-dark mb-1">${data.nama}</h6>
                        <p class="text-muted small mb-3">${data.jabatan} · ${data.divisi}</p>
                        <div class="text-center mb-3"><h2 class="fw-bold text-dark mb-0">${data.rata_rata_keseluruhan}</h2><small class="text-muted">Rata-rata Nilai Bulan Ini</small></div>
                        <h6 class="fw-bold text-dark mb-2">Rincian per Kategori</h6>
                        ${breakdownRows || '<div class="text-muted small">Tidak ada rincian.</div>'}
                    `);
                }
            },
            error: function() {
                $('#achievementDetailLoading').addClass('d-none');
                $('#achievementDetailContent').removeClass('d-none').html('<div class="text-center text-muted py-4">Gagal memuat detail.</div>');
            }
        });
    }

    function openNewsDetail(type, refId) {
        $('#newsDetailLoading').removeClass('d-none'); $('#newsDetailContent').addClass('d-none').empty();
        $('#modalNewsDetail').modal('show');
        $.ajax({
            url: window.newsDetailUrl, type: 'GET', data: { type: type, ref_id: refId }, dataType: 'json',
            success: function(data) {
                $('#newsDetailLoading').addClass('d-none');
                let extra = '';
                if (data.detail) {
                    extra = (data.detail || []).map(d => `<div class="small text-muted mb-1">• ${d.jabatan}: ${d.keterangan}</div>`).join('');
                }
                $('#newsDetailContent').removeClass('d-none').html(`
                    <h6 class="fw-bold text-dark mb-2">${data.title}</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-2">${data.status}</span>
                    <p class="small text-muted mb-1">Dibuat: ${data.dibuat}</p>
                    ${data.selesai ? `<p class="small text-muted mb-3">Selesai: ${data.selesai}</p>` : ''}
                    ${extra}
                `);
            },
            error: function() {
                $('#newsDetailLoading').addClass('d-none');
                $('#newsDetailContent').removeClass('d-none').html('<div class="text-center text-muted py-4">Gagal memuat detail.</div>');
            }
        });
    }

    return { init: init };
})(jQuery);

$(document).ready(function() { DashboardKPI.init(); });