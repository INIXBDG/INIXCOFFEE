const fmt1 = (v) => (Number(v) || 0).toFixed(1);

(function() {
    'use strict';

    let performanceChart = null;
    let statusPieChart = null;
    let allTargetsData = [];
    let currentFilter = 'all';

    const allowedAssistantRoutes = ['dorong inovasi pelayanan', 'inisiatif efisiensi keuangan', 'mengurangi manual work dan error'];
    const allowedDoubleManualRoutes = window.allowedDoubleManualRoutes || [];

    function formatNumber(value) {
        if (!value && value !== '0') return '';
        const raw = String(value).replace(/[^0-9]/g, '');
        if (!raw) return '';
        return new Intl.NumberFormat('id-ID').format(raw);
    }

    function getRawNumber(value) {
        if (!value) return '';
        return String(value).replace(/[^0-9]/g, '');
    }

    function toPercent(t) {
        const p = parseFloat(t.progress) || 0;
        const tg = parseFloat(t.target) || 0;
        const v = (['rupiah', 'angka'].includes(t.tipe_target) && tg > 0) ? (p / tg) * 100 : p;
        return Math.max(0, Math.min(100, v));
    }

    const GRADE_SCALE = [
        { min: 100, grade: 'A', label: 'Sangat Baik',   level: null,       badge: 'success' },
        { min: 95,  grade: 'B', label: 'Baik',          level: 'High',     badge: 'primary' },
        { min: 90,  grade: 'B', label: 'Baik',          level: 'Mid',      badge: 'primary' },
        { min: 85,  grade: 'B', label: 'Baik',          level: 'Standard', badge: 'primary' },
        { min: 80,  grade: 'B', label: 'Baik',          level: 'Low',      badge: 'primary' },
        { min: 70,  grade: 'C', label: 'Cukup',         level: null,       badge: 'warning text-dark' },
        { min: 60,  grade: 'D', label: 'Kurang',        level: null,       badge: 'danger' },
        { min: 0,   grade: 'E', label: 'Sangat Kurang', level: null,       badge: 'dark' }
    ];

    function hitungGrade(score) {
        const value = Number(fmt1(Math.max(0, Math.min(100, Number(score) || 0))));
        const row = GRADE_SCALE.find(r => value >= r.min) || GRADE_SCALE[GRADE_SCALE.length - 1];
        return {
            grade: row.grade,
            label: row.label,
            level: row.level,
            badge: row.badge,
            display: row.grade + ' (' + row.label + (row.level ? ' - ' + row.level : '') + ')',
            score: value
        };
    }

    function renderGradeBadge(g) {
        return g ? ` <span class="badge bg-${g.badge} ms-2" title="Grade KPI">${g.display}</span>` : '';
    }

    function initInputFormatting() {
        $('#manual_value_display').off('input.formatting').on('input.formatting', function() {
            const raw = getRawNumber($(this).val());
            $('#manual_value').val(raw);
            const format = $('#manual_format').val();
            let formatted = formatNumber(raw);
            if (format === 'rupiah' && raw) formatted = 'Rp ' + formatted;
            else if (format === 'persen' && raw) formatted = formatted + '%';
            $(this).val(formatted);
        });

        ['biaya_gaji', 'biaya_bpjs', 'biaya_rekrutmen'].forEach(type => {
            $(`#${type}_display`).off('input.formatting').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $(`#${type}_tahunan`).val(raw);
                $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
            });
        });
    }

    function resetFormManual() {
        $('#formManualValue')[0].reset();
        $('#documentPreview').html('');
        $('#singleInputArea').show();
        $('#doubleInputArea').hide();
        $('#manual_value_display').val('').trigger('input');
        $('#manual_value').val('');
        ['biaya_gaji', 'biaya_bpjs', 'biaya_rekrutmen'].forEach(type => {
            $(`#${type}_display`).val('').trigger('input');
            $(`#${type}_tahunan`).val('');
        });
        $('#manualValueId').val('');
    }

    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error', text: message, confirmButtonText: 'OK' });
        } else {
            alert(message);
        }
    }

    function toggleSkeletons(show) {
        const display = show ? 'block' : 'none';
        document.querySelectorAll('.skeleton-overlay').forEach(el => {
            el.style.display = display;
        });
    }

    function loadDataPersonal() {
        toggleSkeletons(true);
        $('#targetCardContainer').css('opacity', '0.5');

        const config = window.KPI_CONFIG;
        $.ajax({
            url: config.dataPersonalRoute,
            type: 'GET',
            data: { tahun: config.currentYear, id_karyawan: config.targetId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toggleSkeletons(false);
                    $('#targetCardContainer').css('opacity', '1');

                    const jabatanUser = (
                        window.KPI_CONFIG?.userJabatan ||
                        window.authUser?.jabatan ||
                        window.authUser?.jabatan_user_table ||
                        ''
                    ).toLowerCase().trim();

                    const rolesLihatGap = ['gm', 'hrd', 'direktur utama', 'direktur'];
                    const isKoordinator =
                        jabatanUser.includes('koordinator itsm') ||
                        jabatanUser === 'koordinator itsm' ||
                        jabatanUser.includes('koordinator') ||
                        rolesLihatGap.includes(jabatanUser);

                    let daftarTarget = response.daftar_target_pribadi || [];
                    let statistik = response.statistik_per_target || [];

                    if (!isKoordinator) {
                        const isGapItem = (t) => {
                            const route = (
                                t.asistant_route ||
                                t.assistant_route ||
                                t.condition ||
                                ''
                            ).toLowerCase();
                            const judul = (t.judul || '').toLowerCase();
                            return (
                                route.includes('gap kompetensi') ||
                                route.includes('persentase gap') ||
                                judul.includes('gap performa') ||
                                judul.includes('gap kompetensi')
                            );
                        };

                        daftarTarget = daftarTarget.filter(t => !isGapItem(t));
                        statistik = statistik.filter(t => !isGapItem(t));

                        response.total_target = daftarTarget.length;
                        response.kpi_aktif = daftarTarget.filter(t =>
                            ['Aktif', 'Sedang Berjalan'].includes(t.status)
                        ).length;
                        response.kpi_selesai = daftarTarget.filter(t =>
                            ['Selesai', 'Mencapai Target'].includes(t.status)
                        ).length;
                        response.grade = daftarTarget.length ? KpiGrade.calc(response.rata_rata_progress) : null;
                    }

                    console.log('jabatanUser:', jabatanUser, 'isKoordinator:', isKoordinator);
                    console.log('targets after filter:', daftarTarget.map(t => t.judul));

                    updateHeader(response);
                    updateStats(response);
                    updateCharts(statistik, response.distribusi_status);
                    updateTargetCards(daftarTarget);
                    allTargetsData = daftarTarget;
                } else {
                    toggleSkeletons(false);
                    showError(response.message || 'Terjadi kesalahan saat memuat data');
                }
            },
            error: function() {
                toggleSkeletons(false);
                showError('Gagal memuat data. Silakan coba lagi.');
            }
        });
    }

    function updateHeader(data) {
        $('#userName').text(data.user_info?.nama || 'User');
        $('#userJabatan').text(data.user_info?.jabatan || '-');
        $('#userDivisi').text(data.user_info?.divisi || '-');
    }

    function updateStats(data) {
        $('#stat0').text(data.total_target + ' Target');
        $('#stat1').html(fmt1(data.rata_rata_progress) + '%' + KpiGrade.badge(data.grade, { nama: data.user_info?.nama, total: data.total_target }));
        $('#stat2').text(data.kpi_aktif);
        $('#stat3').text(data.kpi_selesai);
    }

    function updateCharts(statistikTargets, distribusiStatus) {
        if (performanceChart) performanceChart.destroy();
        if (statusPieChart) statusPieChart.destroy();

        const targetLabels = statistikTargets.map(item => item.judul.length > 20 ? item.judul.substring(0, 20) + '...' : item.judul);
        const targetProgress = statistikTargets.map(item => {
            if ((item.tipe_target === "rupiah" || item.tipe_target === "angka") && item.target > 0) {
                return Number(fmt1((item.progress / item.target) * 100));
            }
            return Number(fmt1(item.progress));
        });

        const backgroundColors = statistikTargets.map(item => {
            let progress = item.progress || 0;
            if (progress === 0) return 'rgba(254, 215, 24, 0.7)';
            if (item.tipe_target === "rupiah") {
                let percent = item.target ? (progress / item.target) * 100 : 0;
                return percent >= 100 ? 'rgba(40, 207, 180, 0.7)' : 'rgba(254, 124, 150, 0.7)';
            }
            return progress >= item.target ? 'rgba(40, 207, 180, 0.7)' : 'rgba(254, 124, 150, 0.7)';
        });

        const borderColors = backgroundColors.map(c => c.replace('0.7', '1'));

        const performanceCtx = document.getElementById('performanceChart');
        if (performanceCtx) {
            performanceChart = new Chart(performanceCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: targetLabels,
                    datasets: [{
                        label: 'Progress (%)',
                        data: targetProgress,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => 'Progress: ' + fmt1(c.parsed.y) + '%' } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => v + '%' } } }
                }
            });
        }

        const pieLabels = Object.keys(distribusiStatus);
        const pieData = Object.values(distribusiStatus);
        const statusCtx = document.getElementById('statusPieChart');
        if (statusCtx) {
            statusPieChart = new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: ['#28CFB4', '#FE7C96', '#FED718'],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '65%'
                }
            });
        }
    }

    function updateTargetCards(targets) {
        const container = $('#targetCardContainer');
        if (!targets || targets.length === 0) {
            container.html(`<div class="col-12"><div class="content-card"><div class="card-body text-center py-5"><i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i><h5 class="text-muted">Belum ada target KPI</h5></div></div></div>`);
            return;
        }

        let html = '';
        targets.forEach(target => {
            let progressBarColor = target.progress >= target.target ? 'bg-success' : 'bg-primary';
            let statusBadgeColor = target.status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark';
            let cardBorderColor = target.progress >= 75 ? 'border-success' : (target.progress >= 50 ? 'border-primary' : 'border-warning');

            let perubahanProgressRupiah = (target.tipe_target === 'rupiah' || target.tipe_target === 'angka')
                ? (target.progress / target.target) * 100
                : target.progress;

            let targetChange = target.tipe_target === "rupiah" ? "Rp " + Number(target.target).toLocaleString("id-ID") :
                               (target.tipe_target === "persen" ? target.target + "%" : target.target);

            let realisasiChange = target.tipe_target === "rupiah" ? "Rp " + Number(target.progress).toLocaleString("id-ID") :
                                  (target.tipe_target === "angka" ? target.progress : fmt1(target.progress) + "%");

            let buttonIsiForm = allowedAssistantRoutes.includes(target.asistant_route) ? 'true' : '';

            html += `
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                    <div class="target-card border-start border-4 ${cardBorderColor}">
                        ${buttonIsiForm ? `<button type="button" class="action-btn buttonForm" data-id="${target.id}" data-value="${target.manual_value}" data-route="${target.asistant_route}" data-bs-toggle="modal" data-bs-target="#modalFormManual" title="Isi Data"><i class="fa-solid fa-file-pen"></i></button>` : ''}
                        <button type="button" class="btn btn-link p-0 text-start w-100 h-100 text-decoration-none buttonDetailTarget" data-id="${target.id}" data-bs-toggle="modal" data-bs-target="#detailTargetModal" style="outline: none;">
                            <div class="card-body-inner">
                                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary status-badge">${target.periode}</span>
                                    <span class="badge ${statusBadgeColor} status-badge">${target.status}</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-2 lh-base title-badge">${target.judul}</h6>
                                <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px;">${target.deskripsi || 'Tidak ada deskripsi'}</p>
                                <div class="mt-auto">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted fw-semibold" style="font-size: 0.8rem;">Progress</small>
                                            <small class="fw-bold text-primary" style="font-size: 0.8rem;">${target.progress_display}</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar ${progressBarColor}" role="progressbar" style="width: ${perubahanProgressRupiah}%"></div>
                                        </div>
                                    </div>
                                    <div class="row g-2 pt-2 border-top">
                                        <div class="col-6">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Target</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;">${targetChange}</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Realisasi</small>
                                            <strong class="text-primary" style="font-size: 0.9rem;">${realisasiChange}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }

    $(document).ready(function() {
        initInputFormatting();
        loadDataPersonal();

        $('#modalFormManual').on('show.bs.modal', resetFormManual);
        $('#modalFormManual').on('hidden.bs.modal', resetFormManual);

        $(document).on('click', '.filter-btn-group .btn', function() {
            $('.filter-btn-group .btn').removeClass('active');
            $(this).addClass('active');

            let filter = $(this).data('filter');
            currentFilter = filter;
            let filteredTargets = allTargetsData;

            if (filter === 'active') filteredTargets = allTargetsData.filter(t => ['Aktif', 'Sedang Berjalan'].includes(t.status));
            else if (filter === 'completed') filteredTargets = allTargetsData.filter(t => t.status === 'Selesai');

            updateTargetCards(filteredTargets);
        });

        $(document).on('click', '.buttonForm', function() {
            const route = $(this).data('route');
            const value = $(this).data('value') || '';
            const id = $(this).data('id');
            $('#manualValueId').val(id);

            if (allowedDoubleManualRoutes.includes(route)) {
                $('#singleInputArea').hide();
                $('#doubleInputArea').show();
                let parts = value && value.includes(',') ? value.split(',') : [value, '', ''];
                ['biaya_gaji', 'biaya_bpjs', 'biaya_rekrutmen'].forEach((type, idx) => {
                    const raw = getRawNumber(parts[idx]);
                    $(`#${type}_display`).val(raw ? 'Rp ' + formatNumber(raw) : '').trigger('input');
                    $(`#${type}_tahunan`).val(raw);
                });
            } else {
                $('#singleInputArea').show();
                $('#doubleInputArea').hide();
                const format = $('#manual_format').val();
                const rawValue = getRawNumber(value);
                let displayValue = formatNumber(rawValue);
                if (format === 'rupiah' && rawValue) displayValue = 'Rp ' + displayValue;
                else if (format === 'persen' && rawValue) displayValue = displayValue + '%';
                $('#manual_value_display').val(displayValue);
                $('#manual_value').val(rawValue);
            }
        });

        $(document).on('change', '#manual_format', function() {
            if ($('#doubleInputArea').is(':visible')) return;
            const format = $(this).val();
            const rawValue = getRawNumber($('#manual_value').val());
            let displayValue = formatNumber(rawValue);
            if (format === 'rupiah' && rawValue) displayValue = 'Rp ' + displayValue;
            else if (format === 'persen' && rawValue) displayValue = displayValue + '%';
            $('#manual_value_display').val(displayValue);
        });

        $('#formManualValue').on('submit', function(e) {
            e.preventDefault();
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');

            const formData = new FormData(this);
            if ($('#doubleInputArea').is(':visible')) {
                formData.set('biaya_gaji_tahunan', $('#biaya_gaji_tahunan').val());
                formData.set('biaya_bpjs_tahunan', $('#biaya_bpjs_tahunan').val());
                formData.set('biaya_rekrutmen_tahunan', $('#biaya_rekrutmen_tahunan').val());
            } else {
                formData.set('manual_value', $('#manual_value').val());
                formData.set('manual_format', $('#manual_format').val());
            }

            $.ajax({
                url: window.KPI_CONFIG.manualValueRoute,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $('#modalFormManual').modal('hide');
                    resetFormManual();
                    $submitBtn.prop('disabled', false).html(originalText);
                    loadDataPersonal();
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).html(originalText);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        alert(Object.values(errors).map(e => e[0]).join('\n'));
                    } else {
                        alert('Terjadi kesalahan sistem.');
                    }
                }
            });
        });

        $(document).on('click', '.buttonDetailTarget', function() {
            let id = $(this).data('id');
            const body = $('#bodyContentDetailTarget');

            body.html(KpiDetail.skeleton('personal'));

            const modalEl = document.getElementById('detailTargetModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }

            $.ajax({
                url: window.KPI_CONFIG.detailRoute,
                method: 'GET',
                data: { id, idUser: window.KPI_CONFIG.targetId },
                dataType: 'json',
                success: function(response) {
                    body.empty();

                    let detailArray = response.detail;
                    let data = detailArray && detailArray.length > 0 ? detailArray[0].data : null;

                    if (!data) {
                        body.append(`
                            <div class="modal-header"><h5 class="modal-title">Detail Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body text-center py-5">Belum ada data</div>
                            <div class="modal-footer"><button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `);
                    } else {
                        const dateNow = new Date().toISOString().split('T')[0];
                        const startOfYear = new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0];
                        const tenggatWaktu = data.tenggat_waktu;

                        function isDateBefore(date1, date2) { return new Date(date1) < new Date(date2); }
                        function isDateAfter(date1, date2) { return new Date(date1) > new Date(date2); }

                        const dataDetail = data.data_detail || {};
                        const progressVal = dataDetail.progress ?? 0;
                        const targetVal   = data.nilai_target ?? 0;

                        const isInverse = dataDetail.is_inverse === true;
                        const capai = isInverse
                            ? progressVal <= targetVal
                            : progressVal >= targetVal;

                        let Tercapai;
                        if (isDateBefore(dateNow, startOfYear)) {
                            Tercapai = "Belum Dimulai";
                        } else if (isDateAfter(dateNow, tenggatWaktu) || dateNow === tenggatWaktu) {
                            Tercapai = capai ? "Mencapai Target" : "Target Gagal";
                        } else {
                            Tercapai = capai ? "Mencapai Target" : "Sedang Berjalan";
                        }

                        let targetValue, progressValue, gapValue;
                        if (data.tipe_target === "persen") {
                            targetValue = data.nilai_target + ' %';
                            progressValue = fmt1(data.data_detail.progress) + ' %';
                            gapValue = fmt1(data.data_detail.gap) + ' %';
                        } else if (data.tipe_target === "rupiah") {
                            const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 });
                            targetValue = formatter.format(data.nilai_target);
                            progressValue = formatter.format(data.data_detail.progress);
                            gapValue = formatter.format(Math.abs(data.data_detail.gap));
                        } else {
                            targetValue = data.nilai_target;
                            progressValue = data.data_detail.progress;
                            gapValue = data.data_detail.gap;
                        }

                        let bgCard = Tercapai === "Mencapai Target" ? "success" : Tercapai === "Target Gagal" ? "danger" : Tercapai === "Sedang Berjalan" ? "warning" : "secondary";

                        let FormatedProgress = 0;
                        if (data.nilai_target && data.nilai_target > 0) {
                            if (data.tipe_target === "rupiah") {
                                FormatedProgress = Math.min((data.data_detail.progress / data.nilai_target) * 100, 100).toFixed(1);
                            } else if (data.tipe_target === "angka") {
                                FormatedProgress = ((data.data_detail.progress / data.nilai_target) * 100).toFixed(1);
                            } else {
                                FormatedProgress = Math.min(data.data_detail.progress, 100).toFixed(1);
                            }
                        }

                        const contents = KpiDetail.buildContents(data, 'personal');

                        body.append(`
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">${data.judul}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body pt-3">
                                <div class="container-fluid p-3">
                                    <div class="row g-4">
                                        <div class="col-lg-8">
                                            <div class="card shadow h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span class="badge bg-primary">${data.jangka_target}</span>
                                                        <span class="badge bg-${bgCard}">${Tercapai}</span>
                                                    </div>
                                                    <div class="row text-center mb-3">
                                                        <div class="col"><small class="text-muted d-block">Target</small><h4 class="fw-bold mb-0" style="font-size: 25px">${targetValue}</h4></div>
                                                        <div class="col"><small class="text-muted d-block">Progress</small><h1 class="fw-bold text-${bgCard} mb-0" style="font-size: 55px;">${progressValue}</h1></div>
                                                        <div class="col"><small class="text-muted d-block">Gap</small><h4 class="fw-bold text-danger mb-0" style="font-size: 25px">${isInverse ? gapValue : `-${gapValue}`}</h4></div>
                                                    </div>
                                                    <div class="position-relative mb-3">
                                                        <div class="progress" style="height:18px;"><div class="progress-bar bg-${bgCard} progress-bar-striped progress-bar-animated" style="width: ${FormatedProgress}%"></div></div>
                                                        <div class="position-absolute bg-light top-0" style="left:${data.nilai_target}%; height:18px; width:2px;"></div>
                                                    </div>
                                                    <div class="text-muted mb-4"><i class="fa-solid fa-calendar-days me-1"></i> Deadline: <strong>${data.tenggat_waktu}</strong></div>
                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 kpi-card">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3"><div class="me-2 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-chart-line text-primary"></i></div><h6 class="mb-0 fw-semibold text-secondary">INFORMASI KPI</h6></div>
                                                                    <div class="row mb-3"><div class="col-4 label">KPI Divisi</div><div class="col-8 value">${data.divisi_kpi || '-'}</div></div>
                                                                    <div class="row mb-3"><div class="col-4 label">KPI Jabatan</div><div class="col-8 value">${data.jabatan_kpi || '-'}</div></div>
                                                                    <div class="row"><div class="col-4 label">Pembuat</div><div class="col-8 value">${data.pembuat || '-'}</div></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 participant-card h-100">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3"><div class="me-2 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fa-solid fa-users text-success"></i></div><h6 class="mb-0 fw-semibold text-secondary">KARYAWAN</h6></div>
                                                                    <div class="participant-list" style="overflow-y: scroll; max-height: 140px;">
                                                                        ${contents.karyawanHtml}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="card shadow h-100 border-0 rounded-4">
                                                <div class="card-body d-flex flex-column">${contents.pieChart}</div>
                                            </div>
                                        </div>
                                    </div>
                                    ${contents.trafikSales}
                                    ${contents.kontribusiSales}
                                    ${contents.statisticChart}
                                    ${contents.kalenderInstruktur}
                                </div>
                            </div>
                            <div class="modal-footer border-0"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>
                        `);

                        setTimeout(() => {
                            body.addClass('modal-content-loaded');
                            KpiDetail.afterRender(data, 'personal', contents);
                        }, 50);
                    }
                },
                error: function() {
                    body.empty();
                    body.html(`
                        <div class="modal-header border-0 pb-2">
                            <h5 class="modal-title fw-bold mb-1 text-danger">Gagal Memuat</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <i class="fa-solid fa-circle-exclamation text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <h6 class="fw-semibold">Terjadi Kesalahan Jaringan</h6>
                            <p class="text-muted small">Silakan coba lagi nanti.</p>
                        </div>
                        <div class="modal-footer border-0">
                            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    `);
                    body.addClass('modal-content-loaded');
                }
            });
        });
    });
})();