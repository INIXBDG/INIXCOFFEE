@extends('databasekpi.berandaKPI')

@section('contentKPI')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="modal fade" id="detailTargetModal" tabindex="-1" aria-labelledby="detailTargetModalLable" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div id="bodyContentDetailTarget" class="p-3"></div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-account"></i>
                </span>
                Dashboard Pribadi KPI
            </h3>
            @if (auth()->user()->jabatan === 'HRD' ||
                auth()->user()->jabatan === 'Direktur Utama' ||
                auth()->user()->jabatan === 'Direktur' ||
                auth()->user()->jabatan === 'GM' ||
                auth()->user()->jabatan === 'Koordinator ITSM' ||
                auth()->user()->jabatan === 'SPV Sales' ||
                auth()->user()->jabatan === 'Education Manager')
                <a href="{{ route('kpi.overview.index') }}" class="btn btn-primary">Ke Overview Departemen</a>             
            @endif
        </div>

        <div class="container-fluid bg-white p-4 rounded-4">
            <div class="mb-4 p-4 rounded-4 shadow-sm bg-gradient-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>
                    <div class="text-white">
                        <h3 class="fw-bold mb-1" id="userName"></h3>

                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                                <i class="fa-solid fa-briefcase me-1"></i>
                                <span id="userJabatan"></span>
                            </span>

                            <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                                <i class="fa-solid fa-building me-1"></i>
                                <span id="userDivisi"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Total Target</small>
                                <h4 class="fw-bold mb-0" id="totalTarget">0 Target</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-bullseye"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Rata-rata Progress</small>
                                <h4 class="fw-bold mb-0 text-primary" id="rataProgress">0%</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">KPI Aktif</small>
                                <h4 class="fw-bold mb-0 text-warning" id="kpiAktif">0</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">KPI Selesai</small>
                                <h4 class="fw-bold mb-0 text-success" id="kpiSelesai">0</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-3"
                                style="width:42px;height:42px;">
                                <i class="fa-solid fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-chart-bar me-2"></i>Performa KPI Saya</h6>
                            <div style="height: 350px;">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-chart-pie me-2"></i>Status Target</h6>
                            <div style="height: 250px;">
                                <canvas id="statusPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold mb-0"><i class="fa-solid fa-list-check me-2"></i>Semua Target Pribadi Saya
                        </h6>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary active"
                                onclick="filterTargets('all')">Semua</button>
                            <button class="btn btn-sm btn-outline-primary" onclick="filterTargets('active')">Aktif</button>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="filterTargets('completed')">Selesai</button>
                        </div>
                    </div>

                    <div class="row" id="targetCardContainer">
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"
                                    style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Loading data...</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFormManual" tabindex="-1" role="dialog" aria-labelledby="modalFormManualLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form id="formManualValue" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFormManualLabel">Isi Manual Target</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="manualValueId">
                        <div class="form-group">
                            <label>Format Nilai</label>
                            <select class="form-control" id="manual_format">
                                <option value="angka">Angka</option>
                                <option value="persen">Persen (%)</option>
                                <option value="rupiah">Rupiah (Rp)</option>
                            </select>
                        </div>
                        <div id="doubleInputArea" style="display:none;">

                            <div class="form-group">
                                <label>Biaya Gaji Tahunan</label>
                                <input type="text" class="form-control" id="biaya_gaji_display">
                                <input type="hidden" name="biaya_gaji_tahunan" id="biaya_gaji_tahunan" required>
                            </div>

                            <div class="form-group">
                                <label>Biaya BPJS Tahunan</label>
                                <input type="text" class="form-control" id="biaya_bpjs_display">
                                <input type="hidden" name="biaya_bpjs_tahunan" id="biaya_bpjs_tahunan" required>
                            </div>

                            <div class="form-group">
                                <label>Biaya Rekrutmen Tahunan</label>
                                <input type="text" class="form-control" id="biaya_rekrutmen_display">
                                <input type="hidden" name="biaya_rekrutmen_tahunan" id="biaya_rekrutmen_tahunan"
                                    required>
                            </div>
                        </div>

                        <div class="form-group" id="singleInputArea">
                            <label>Masukan Nilai</label>
                            <input type="text" class="form-control" id="manual_value_display">
                            <input type="hidden" name="manual_value" id="manual_value">
                        </div>

                        <div class="form-group">
                            <label>Masukan Document</label>
                            <input type="file" class="form-control" name="manual_document" id="manual_document"
                                accept="image/*,.pdf">
                        </div>

                        <div class="form-group">
                            <div id="documentPreview" class="mt-3"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <input type="hidden" id="currentKaryawanId" value="{{ $targetId ?? Auth::id() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.authUser = {
            nama: "{{ auth()->user()->karyawan->nama_lengkap }}",
            jabatan: "{{ auth()->user()->jabatan }}"
        };
    </script>
    <script>
        const TARGET_KARYAWAN_ID = {{ $targetId ?? auth()->user()->id }};
        let performanceChart = null;
        let statusPieChart = null;
        let allTargetsData = [];
        let currentFilter = 'all';

        const allowedAssistantRoutes = [
            'dorong inovasi pelayanan',
            'pemasukan bersih',
            'inisiatif efisiensi keuangan',
            'rasio biaya operasional terhadap revenue',
            'mengurangi manual work dan error',
            'laporan analisis keuangan',
            'pengeluaran biaya karyawan'
        ];

        const allowedDoubleManualRoutes = [
            'pengeluaran biaya karyawan'
        ];


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

        function initInputFormatting() {
            $('#manual_value_display').off('input.formatting');
            $('#manual_value_display').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $('#manual_value').val(raw);

                const format = $('#manual_format').val();
                let formatted = formatNumber(raw);

                if (format === 'rupiah' && raw) {
                    formatted = 'Rp ' + formatted;
                } else if (format === 'persen' && raw) {
                    formatted = formatted + '%';
                }

                $(this).val(formatted);
            });

            $('#biaya_gaji_display').off('input.formatting');
            $('#biaya_gaji_display').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $('#biaya_gaji_tahunan').val(raw);
                $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
            });

            $('#biaya_bpjs_display').off('input.formatting');
            $('#biaya_bpjs_display').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $('#biaya_bpjs_tahunan').val(raw);
                $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
            });

            $('#biaya_rekrutmen_display').off('input.formatting');
            $('#biaya_rekrutmen_display').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $('#biaya_rekrutmen_tahunan').val(raw);
                $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
            });
        }

        $(document).ready(function() {
            initInputFormatting();

            $('#modalFormManual').on('show.bs.modal', function() {
                resetFormManual();
            });

            $('#modalFormManual').on('hidden.bs.modal', function() {
                resetFormManual();
            });
        });

        function resetFormManual() {
            $('#formManualValue')[0].reset();
            $('#documentPreview').html('');
            $('#singleInputArea').show();
            $('#doubleInputArea').hide();

            $('#manual_value_display').val('').trigger('input');
            $('#manual_value').val('');
            $('#biaya_gaji_display').val('').trigger('input');
            $('#biaya_gaji_tahunan').val('');
            $('#biaya_bpjs_display').val('').trigger('input');
            $('#biaya_bpjs_tahunan').val('');

            $('#biaya_rekrutmen_display').val('').trigger('input');
            $('#biaya_rekrutmen_tahunan').val('');

            $('#manualValueId').val('');
        }

        $(document).on('click', '.buttonForm', function() {
            const route = $(this).data('route');
            const value = $(this).data('value') || '';
            const id = $(this).data('id');

            $('#manualValueId').val(id);

            if (allowedDoubleManualRoutes.includes(route)) {
                $('#singleInputArea').hide();
                $('#doubleInputArea').show();

                let gaji = '';
                let bpjs = '';
                let rekrutmen = '';

                if (value && value.includes(',')) {
                    const parts = value.split(',');
                    gaji = parts[0] || '';
                    bpjs = parts[1] || '';
                    rekrutmen = parts[2] || '';
                } else {
                    gaji = value;
                    bpjs = '';
                    rekrutmen = '';
                }

                const gajiRaw = getRawNumber(gaji);
                const bpjsRaw = getRawNumber(bpjs);
                const rekrutmenRaw = getRawNumber(rekrutmen);

                $('#biaya_gaji_display').val(gajiRaw ? 'Rp ' + formatNumber(gajiRaw) : '');
                $('#biaya_gaji_tahunan').val(gajiRaw);
                $('#biaya_bpjs_display').val(bpjsRaw ? 'Rp ' + formatNumber(bpjsRaw) : '');
                $('#biaya_bpjs_tahunan').val(bpjsRaw);

                $('#biaya_rekrutmen_display').val(rekrutmenRaw ? 'Rp ' + formatNumber(rekrutmenRaw) : '');
                $('#biaya_rekrutmen_tahunan').val(rekrutmenRaw);

            } else {
                $('#singleInputArea').show();
                $('#doubleInputArea').hide();

                const format = $('#manual_format').val();
                const rawValue = getRawNumber(value);
                let displayValue = formatNumber(rawValue);

                if (format === 'rupiah' && rawValue) {
                    displayValue = 'Rp ' + displayValue;
                } else if (format === 'persen' && rawValue) {
                    displayValue = displayValue + '%';
                }

                $('#manual_value_display').val(displayValue);
                $('#manual_value').val(rawValue);
            }
        });

        $(document).on('change', '#manual_format', function() {
            if ($('#doubleInputArea').is(':visible')) {
                return;
            }

            const format = $(this).val();
            const rawValue = getRawNumber($('#manual_value').val());
            let displayValue = formatNumber(rawValue);

            if (format === 'rupiah' && rawValue) {
                displayValue = 'Rp ' + displayValue;
            } else if (format === 'persen' && rawValue) {
                displayValue = displayValue + '%';
            }

            $('#manual_value_display').val(displayValue);

            console.log('Format changed:', {
                format,
                rawValue,
                displayValue
            });
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
                url: "{{ route('kpi.manualValue') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    $('#modalFormManual').modal('hide');
                    resetFormManual();
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (typeof loadContentForm === 'function') {
                        loadContentForm();
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        const msg = Object.values(errors).map(e => e[0]).join('\n');
                        alert(msg);
                    } else {
                        alert('Terjadi kesalahan sistem: ' + (xhr.statusText || 'Unknown error'));
                    }
                }
            });
        });

        $(document).ready(function() {
            loadDataPersonal();
        });

        function loadDataPersonal() {
            let tahun = {{ now()->year }};
            let karyawanId = $('#currentKaryawanId').val(); 

            $.ajax({
                url: "{{ route('kpi.overview.dataPersonal') }}",
                type: 'GET',
                data: {
                    tahun: tahun,
                    id_karyawan: karyawanId,
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#userName').text('Loading...');
                    $('#userJabatan').text('Loading...');
                    $('#userDivisi').text('Loading...');
                    $('#totalTarget').text('Loading...');
                    $('#rataProgress').text('Loading...');
                    $('#kpiAktif').text('Loading...');
                    $('#kpiSelesai').text('Loading...');

                    $('#targetCardContainer').html(`
                    <div class="col-12">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading data...</p>
                        </div>
                    </div>
                `);
                },
                success: function(response) {
                    if (response.success) {
                        updateHeader(response);
                        updateStats(response);
                        updateCharts(response.statistik_per_target, response.distribusi_status);
                        updateTargetCards(response.daftar_target_pribadi);
                        allTargetsData = response.daftar_target_pribadi;
                    } else {
                        showError(response.message || 'Terjadi kesalahan saat memuat data');
                    }
                },
                error: function(xhr, status, error) {
                    showError('Gagal memuat data. Silakan coba lagi.');
                    console.error('AJAX Error:', error);
                }
            });
        }

        function updateHeader(data) {
            $('#userName').text(data.user_info?.nama || 'User');
            $('#userJabatan').text(data.user_info?.jabatan || '-');
            $('#userDivisi').text(data.user_info?.divisi || '-');
        }

        function updateStats(data) {
            $('#totalTarget').text(data.total_target + ' Target');
            $('#rataProgress').text(Math.round(data.rata_rata_progress) + '%');
            $('#kpiAktif').text(data.kpi_aktif);
            $('#kpiSelesai').text(data.kpi_selesai);
        }

        function updateCharts(statistikTargets, distribusiStatus) {
            if (performanceChart) {
                performanceChart.destroy();
            }
            if (statusPieChart) {
                statusPieChart.destroy();
            }

            let targetLabels = statistikTargets.map(item => {
                return item.judul.length > 20 ? item.judul.substring(0, 20) + '...' : item.judul;
            });

            let targetProgress = statistikTargets.map(item => {
                if (item.tipe_target === "rupiah") {
                    if (!item.target || item.target === 0) return 0;
                    return Math.round((item.progress / item.target) * 100);
                } else {
                    return Math.round(item.progress || 0);
                }
            });

            let backgroundColors = statistikTargets.map(item => {
                let progress = item.progress || 0;

                if (progress === 0) {
                    return 'rgba(254, 215, 24, 0.7)';
                }

                if (item.tipe_target === "rupiah") {
                    let percent = item.target ? (progress / item.target) * 100 : 0;
                    return percent >= 100 ?
                        'rgba(40, 207, 180, 0.7)' :
                        'rgba(254, 124, 150, 0.7)';
                }

                return progress >= item.target ?
                    'rgba(40, 207, 180, 0.7)' :
                    'rgba(254, 124, 150, 0.7)';
            });

            let borderColors = statistikTargets.map(item => {
                let progress = item.progress || 0;

                if (progress === 0) {
                    return 'rgba(254, 215, 24, 1)';
                }

                if (item.tipe_target === "rupiah") {
                    let percent = item.target ? (progress / item.target) * 100 : 0;
                    return percent >= 100 ?
                        'rgba(40, 207, 180, 1)' :
                        'rgba(254, 124, 150, 1)';
                }

                return progress >= item.target ?
                    'rgba(40, 207, 180, 1)' :
                    'rgba(254, 124, 150, 1)';
            });

            const performanceCtx = document.getElementById('performanceChart').getContext('2d');

            performanceChart = new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: targetLabels,
                    datasets: [{
                        label: 'Progress (%)',
                        data: targetProgress,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Progress: ${context.parsed.y}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            let pieLabels = Object.keys(distribusiStatus);
            let pieData = Object.values(distribusiStatus);

            const statusCtx = document.getElementById('statusPieChart').getContext('2d');

            statusPieChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: [
                            '#28CFB4',
                            '#FE7C96',
                            '#FED718'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} target (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        function updateTargetCards(targets) {
            if (!targets || targets.length === 0) {
                $('#targetCardContainer').html(`
                <div class="col-12">
                    <div class="card border-0 rounded-4 bg-light h-100">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada target KPI</h5>
                            <p class="text-muted">Silakan tambahkan target KPI Anda</p>
                        </div>
                    </div>
                </div>
            `);
                return;
            }

            let html = '';
            targets.forEach((target, index) => {
                let progressBarColor = '';
                if (target.progress >= target.target) progressBarColor = 'bg-success';
                else if (target.progress < target.target) progressBarColor = 'bg-primary';
                else progressBarColor = 'bg-warning';

                let badgeClass = target.status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark';

                let cardBorderColor = '';
                if (target.progress >= 75) cardBorderColor = 'border-3 border-success';
                else if (target.progress >= 50) cardBorderColor = 'border-3 border-primary';
                else cardBorderColor = 'border-3 border-warning';

                let progressBarBg = 'bg-light';

                let statusBadgeColor = target.status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark';

                let targetTextColor = 'text-warning';
                let progressTextColor = 'text-primary';

                let yearBadgeColor = 'bg-primary';

                let perubahanProgressRupiah;

                if (target.tipe_target === 'rupiah') {
                    perubahanProgressRupiah = (target.progress / target.target) * 100;
                } else {
                    perubahanProgressRupiah = target.progress;
                }

                let targetChange;

                if (target.tipe_target === "rupiah") {
                    targetChange = "Rp " + Number(target.target).toLocaleString("id-ID");
                } else if (target.tipe_target === "persen") {
                    targetChange = target.target + "%";
                } else {
                    targetChange = target.target;
                }

                let realisasiChange;

                if (target.tipe_target === "rupiah") {
                    realisasiChange = "Rp " + Number(target.progress).toLocaleString("id-ID");
                } else {
                    realisasiChange = target.progress + "%";
                }

                const allowedAssistantRouteButtonsManual = [
                    'dorong inovasi pelayanan',
                    'pemasukan bersih',
                    'inisiatif efisiensi keuangan',
                    'rasio biaya operasional terhadap revenue',
                    'mengurangi manual work dan error',
                    'laporan analisis keuangan',
                    'pengeluaran biaya karyawan'
                ];

                const allowedDoubleManualRoutes = [
                    'pengeluaran biaya karyawan'
                ];


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

                function initInputFormatting() {
                    // Single Input
                    $('#manual_value_display').off('input.formatting');
                    $('#manual_value_display').on('input.formatting', function() {
                        const raw = getRawNumber($(this).val());
                        $('#manual_value').val(raw);

                        const format = $('#manual_format').val();
                        let formatted = formatNumber(raw);

                        if (format === 'rupiah' && raw) {
                            formatted = 'Rp ' + formatted;
                        } else if (format === 'persen' && raw) {
                            formatted = formatted + '%';
                        }

                        $(this).val(formatted);
                    });

                    // Double Input - Biaya Gaji
                    $('#biaya_gaji_display').off('input.formatting');
                    $('#biaya_gaji_display').on('input.formatting', function() {
                        const raw = getRawNumber($(this).val());
                        $('#biaya_gaji_tahunan').val(raw);
                        $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
                    });

                    // Double Input - Biaya BPJS
                    $('#biaya_bpjs_display').off('input.formatting');
                    $('#biaya_bpjs_display').on('input.formatting', function() {
                        const raw = getRawNumber($(this).val());
                        $('#biaya_bpjs_tahunan').val(raw);
                        $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
                    });

                    // ✅ Double Input - Biaya Rekrutmen (BARU)
                    $('#biaya_rekrutmen_display').off('input.formatting');
                    $('#biaya_rekrutmen_display').on('input.formatting', function() {
                        const raw = getRawNumber($(this).val());
                        $('#biaya_rekrutmen_tahunan').val(raw);
                        $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
                    });
                }

                $(document).ready(function() {
                    initInputFormatting();

                    $('#modalFormManual').on('show.bs.modal', function() {
                        resetFormManual();
                    });

                    $('#modalFormManual').on('hidden.bs.modal', function() {
                        resetFormManual();
                    });
                });

                function resetFormManual() {
                    $('#formManualValue')[0].reset();
                    $('#documentPreview').html('');
                    $('#singleInputArea').show();
                    $('#doubleInputArea').hide();

                    $('#manual_value_display').val('').trigger('input');
                    $('#manual_value').val('');
                    $('#biaya_gaji_display').val('').trigger('input');
                    $('#biaya_gaji_tahunan').val('');
                    $('#biaya_bpjs_display').val('').trigger('input');
                    $('#biaya_bpjs_tahunan').val('');

                    $('#biaya_rekrutmen_display').val('').trigger('input');
                    $('#biaya_rekrutmen_tahunan').val('');

                    $('#manualValueId').val('');
                }

                $(document).on('click', '.buttonForm', function() {
                    const route = $(this).data('route');
                    const value = $(this).data('value') || '';
                    const id = $(this).data('id');

                    $('#manualValueId').val(id);

                    if (allowedDoubleManualRoutes.includes(route)) {
                        $('#singleInputArea').hide();
                        $('#doubleInputArea').show();

                        let gaji = '';
                        let bpjs = '';
                        let rekrutmen = '';

                        if (value && value.includes(',')) {
                            const parts = value.split(',');
                            gaji = parts[0] || '';
                            bpjs = parts[1] || '';
                            rekrutmen = parts[2] || '';
                        } else {
                            gaji = value;
                            bpjs = '';
                            rekrutmen = '';
                        }

                        const gajiRaw = getRawNumber(gaji);
                        const bpjsRaw = getRawNumber(bpjs);
                        const rekrutmenRaw = getRawNumber(rekrutmen);

                        $('#biaya_gaji_display').val(gajiRaw ? 'Rp ' + formatNumber(gajiRaw) : '');
                        $('#biaya_gaji_tahunan').val(gajiRaw);
                        $('#biaya_bpjs_display').val(bpjsRaw ? 'Rp ' + formatNumber(bpjsRaw) : '');
                        $('#biaya_bpjs_tahunan').val(bpjsRaw);

                        $('#biaya_rekrutmen_display').val(rekrutmenRaw ? 'Rp ' + formatNumber(
                            rekrutmenRaw) : '');
                        $('#biaya_rekrutmen_tahunan').val(rekrutmenRaw);

                    } else {
                        $('#singleInputArea').show();
                        $('#doubleInputArea').hide();

                        const format = $('#manual_format').val();
                        const rawValue = getRawNumber(value);
                        let displayValue = formatNumber(rawValue);

                        if (format === 'rupiah' && rawValue) {
                            displayValue = 'Rp ' + displayValue;
                        } else if (format === 'persen' && rawValue) {
                            displayValue = displayValue + '%';
                        }

                        $('#manual_value_display').val(displayValue);
                        $('#manual_value').val(rawValue);
                    }
                });

                $(document).on('change', '#manual_format', function() {
                    if ($('#doubleInputArea').is(':visible')) {
                        return;
                    }

                    const format = $(this).val();
                    const rawValue = getRawNumber($('#manual_value').val());
                    let displayValue = formatNumber(rawValue);

                    if (format === 'rupiah' && rawValue) {
                        displayValue = 'Rp ' + displayValue;
                    } else if (format === 'persen' && rawValue) {
                        displayValue = displayValue + '%';
                    }

                    $('#manual_value_display').val(displayValue);

                    console.log('Format changed:', {
                        format,
                        rawValue,
                        displayValue
                    });
                });

                $('#formManualValue').on('submit', function(e) {
                    e.preventDefault();

                    const $submitBtn = $(this).find('button[type="submit"]');
                    const originalText = $submitBtn.html();

                    $submitBtn.prop('disabled', true).html(
                        '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');

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
                        url: "{{ route('kpi.manualValue') }}",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(res) {
                            $('#modalFormManual').modal('hide');
                            resetFormManual();
                            $submitBtn.prop('disabled', false).html(originalText);

                            if (typeof loadContentForm === 'function') {
                                loadContentForm();
                            }
                        },
                        error: function(xhr) {
                            $submitBtn.prop('disabled', false).html(originalText);

                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON?.errors || {};
                                const msg = Object.values(errors).map(e => e[0]).join('\n');
                                alert(msg);
                            } else {
                                alert('Terjadi kesalahan sistem: ' + (xhr.statusText ||
                                    'Unknown error'));
                            }
                        }
                    });
                });

                let buttonIsiForm = '';

                if (allowedAssistantRouteButtonsManual.includes(target.asistant_route)) {
                    buttonIsiForm = `
                        <button type="button"
                            class="btn btn-sm btn-info rounded-circle d-flex align-items-center justify-content-center buttonForm"
                            data-id="${target.id}"
                            data-value="${target.manual_value}"
                            data-route="${target.asistant_route}"
                            title="isi data"
                            data-bs-toggle="modal"
                            data-bs-target="#modalFormManual"
                            style="width: 36px; height: 36px; font-size: 0.9rem;">
                            <i class="fa-solid fa-file-pen"></i>
                        </button>
                    `;
                }

                html += `
                    <div class="col-12 col-sm-6 col-lg-4 mb-4 mt-4">
                                                ${buttonIsiForm}
                        <button type="button" 
                                class="btn btn-link p-0 text-start w-100 h-100 text-decoration-none" 
                                id="buttonDetailTarget" 
                                data-id="${target.id}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#detailTargetModal"
                                style="outline: none;">
                            
                            <div class="card border-0 rounded-4 h-100 ${cardBorderColor} shadow-sm hover-shadow transition-all overflow-hidden bg-white">
                                <div class="card-body p-3 p-md-4 d-flex flex-column">
                                    
                                    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                        <span class="${yearBadgeColor} badge fs-7 py-2 px-3 rounded-pill">${target.periode}</span>
                                        <span class="${statusBadgeColor} badge fs-7 py-2 px-3 rounded-pill">${target.status}</span>
                                    </div>

                                    <h5 class="card-title fw-bold mb-2 text-dark lh-base">
                                        <span class="d-inline-block bg-light text-dark px-2 py-1 rounded-2 ">
                                            ${target.judul}
                                        </span>
                                    </h5>
                                    
                                    <p class="text-muted small mb-3 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        ${target.deskripsi || 'Tidak ada deskripsi'}
                                    </p>

                                    <div class="mt-auto">
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted fw-semibold" style="font-size: 0.8rem;">Progress</small>
                                                <small class="fw-bold ${progressTextColor}" style="font-size: 0.8rem;">${target.progress_display}</small>
                                            </div>
                                            <div class="progress ${progressBarBg} rounded-pill" style="height: 8px;">
                                                <div class="progress-bar ${progressBarColor} rounded-pill" 
                                                    role="progressbar" 
                                                    style="width: ${perubahanProgressRupiah}%" 
                                                    aria-valuenow="${perubahanProgressRupiah}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100"></div>
                                            </div>
                                        </div>

                                        <div class="row g-2 pt-3">
                                            <div class="col-6 border-end">
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Target</small>
                                                <strong class="${targetTextColor}">${targetChange}</strong>
                                            </div>
                                            <div class="col-6 ps-3">
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Realisasi</small>
                                                <strong class="${progressTextColor}">${realisasiChange}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                `;
            });

            $('#targetCardContainer').html(html);
        }

        function filterTargets(filter) {
            currentFilter = filter;

            let filteredTargets = allTargetsData;

            if (filter === 'active') {
                filteredTargets = allTargetsData.filter(target => target.status === 'Aktif');
            } else if (filter === 'completed') {
                filteredTargets = allTargetsData.filter(target => target.status === 'Selesai');
            }

            updateTargetCards(filteredTargets);
        }

        function viewTargetDetail(targetId) {
            Swal.fire({
                title: 'Detail Target',
                text: 'Fitur detail target akan ditampilkan di sini',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }

        function updateProgress() {
            Swal.fire({
                title: 'Update Progress',
                text: 'Silakan update progress KPI Anda melalui halaman target',
                icon: 'info',
                confirmButtonText: 'Lihat Target'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('kpi.index') }}";
                }
            });
        }

        function viewAchievements() {
            Swal.fire({
                title: 'Pencapaian KPI',
                text: 'Halaman pencapaian KPI akan segera hadir',
                icon: 'info'
            });
        }

        function viewHistory() {
            Swal.fire({
                title: 'Riwayat KPI',
                text: 'Halaman riwayat KPI akan segera hadir',
                icon: 'info'
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonText: 'OK'
            });
        }

        $(document).on('click', '#buttonDetailTarget', function() {
            let id = $(this).data('id');
            let idUser = TARGET_KARYAWAN_ID;

            $.ajax({
                url: "{{ route('kpi.detail') }}",
                method: 'GET',
                data: {
                    id,
                    idUser
                },
                dataType: 'json',
                success: function(response) {
                    const body = $('#bodyContentDetailTarget');
                    if (body.length === 0) {
                        console.error("Elemen #bodyContentDetailTarget tidak ditemukan!");
                        return;
                    }

                    body.empty();

                    let detailArray = response.detail;
                    let data = detailArray && detailArray.length > 0 ? detailArray[0].data : null;

                    if (!data) {
                        body.append(`
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Target</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            Belum ada data
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    `);
                    } else {
                        const monthlyData = data.data_detail.monthly_data || {};
                        const dailyData = data.data_detail.daily_breakdown_per_month || {};

                        const dateNow = "{{ now()->format('Y-m-d') }}";
                        const startOfYear = "{{ now()->startOfYear()->format('Y-m-d') }}";
                        const tenggatWaktu = data.tenggat_waktu;

                        function isDateBefore(date1, date2) {
                            return date1 < date2;
                        }

                        function isDateAfter(date1, date2) {
                            return date1 > date2;
                        }

                        let Tercapai;
                        if (isDateBefore(dateNow, startOfYear)) {
                            Tercapai = "Belum Dimulai";
                        } else if (isDateAfter(dateNow, tenggatWaktu) || dateNow === tenggatWaktu) {
                            Tercapai = data.data_detail.progress >= data.nilai_target ?
                                "Mencapai Target" : "Target Gagal";
                        } else {
                            Tercapai = data.data_detail.progress >= data.nilai_target ?
                                "Mencapai Target" : "Sedang Berjalan";
                        }

                        let targetValue, progressValue, gapValue;
                        if (data.tipe_target === "persen") {
                            targetValue = data.nilai_target + ' %';
                            progressValue = data.data_detail.progress + ' %';
                            gapValue = data.data_detail.gap + ' %';
                        } else if (data.tipe_target === "rupiah") {
                            const formatter = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                            targetValue = formatter.format(data.nilai_target);
                            progressValue = formatter.format(data.data_detail.progress);
                            gapValue = formatter.format(Math.abs(data.data_detail.gap));
                        } else {
                            targetValue = data.nilai_target;
                            progressValue = data.data_detail.progress;
                            gapValue = data.data_detail.gap;
                        }

                        let bgCard;

                        if (Tercapai === "Mencapai Target") {
                            bgCard = "success";
                        } else if (Tercapai === "Target Gagal") {
                            bgCard = "danger";
                        } else if (Tercapai === "Sedang Berjalan") {
                            bgCard = "warning";
                        } else if (Tercapai === "Belum Berjalan") {
                            bgCard = "secondary";
                        } else {
                            bgCard = "secondary";
                        }

                        let textTitle;

                        const pieChart = data.data_detail.pie_chart || {
                            above: 0,
                            below: 0
                        };
                        const dataPieChart = {
                            labels: ['Above', 'Below'],
                            datasets: [{
                                label: 'Jumlah',
                                data: [pieChart.above ?? 0, pieChart.below ?? 0],
                                backgroundColor: ['#B66DFF', '#FE7C96'],
                                hoverOffset: 4
                            }]
                        };

                        setTimeout(() => {
                            const ctx = document.getElementById('MyChartDoughtnut');
                            if (ctx) {
                                new Chart(ctx, {
                                    type: 'doughnut',
                                    data: dataPieChart,
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    boxWidth: 12,
                                                    padding: 15
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        }, 0);

                        const NAMA_BULAN = [
                            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];

                        function getNamaBulan(tahunBulan) {
                            const parts = tahunBulan.split('-');
                            if (parts.length < 2) return tahunBulan;
                            const bulanIndex = parseInt(parts[1], 10) - 1;
                            return NAMA_BULAN[bulanIndex] || tahunBulan;
                        }

                        const monthlyEntries = Object.entries(monthlyData);

                        const lastMonthEntry = monthlyEntries[monthlyEntries.length - 1] || [];
                        const labelBulanTerakhir = lastMonthEntry[0] ? getNamaBulan(lastMonthEntry[0]) : '-';
                        const nilaiBulanTerakhirRupiah = lastMonthEntry[1] || 0;

                        let allDays = [];

                        Object.values(dailyData).forEach(month => {
                            Object.entries(month).forEach(([tanggal, nilai]) => {
                                allDays.push([tanggal, nilai]);
                            });
                        });

                        const top3HariTertinggi = allDays
                            .sort((a, b) => b[1] - a[1])
                            .slice(0, 3);

                        const karyawanTerkaitRupiah = data.karyawan?.[0] || null;

                        function formatRupiah(angka) {
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(angka || 0);
                        }

                        function formatTanggalSingkat(tanggal) {
                            const date = new Date(tanggal);
                            return date.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short'
                            });
                        }

                        const karyawanList = Array.isArray(data.karyawan) ? data.karyawan : [data
                            .karyawan
                        ];
                        let no = 1;
                        const karyawanHtml = karyawanList.map(item => `
                            <div class="d-flex align-items-center py-2 participant-item">
                                <div class="avatar me-3">${no++}</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark small">${item.nama_lengkap}</div>
                                    <div class="text-muted small">${item.jabatan}</div>
                                </div>
                            </div>
                        `).join('');

                        let FormatedProgress = 0;

                        if (data.nilai_target && data.nilai_target > 0) {
                            if (data.tipe_target === "rupiah") {
                                const rawProgress = (data.data_detail.progress / data.nilai_target) *
                                    100;

                                FormatedProgress = Math.min(rawProgress, 100).toFixed(2);
                            } else {
                                FormatedProgress = Math.min(data.data_detail.progress, 100).toFixed(2);
                            }
                        } else {
                            FormatedProgress = 0;
                        }

                        const allowedDetailAssistantRoutes = [
                            'dorong inovasi pelayanan',
                            'pemasukan bersih',
                            'rasio biaya operasional terhadap revenue',
                            'inisiatif efisiensi keuangan',
                            'mengurangi manual work dan error',
                            'laporan analisis keuangan',
                            'pengeluaran biaya karyawan'
                        ];

                        const allowedDetailAssistantRoutesForRupiah = [
                            'Pemasukan Kotor',
                            'meningkatkan revenue perusahaan'
                        ];

                        const allowedDetailAssistantRoutesForPresentaseGapKompetensi = [
                            'persentase gap kompetensi tim terhadap standar skill'
                        ]

                        const allowedAssistantRoutesForTargetPenjualanTahunan = [
                            'target penjualan tahunan',
                            'Pemasukan Kotor'
                        ]

                        const allowedAssistantRoutesForPeningkatanKontribusiPelatihan = [
                            'peningkatan kontribusi pelatihan',
                        ]

                        const allowedAssistantRoutesForPemasukanBersih = [
                            'pemasukan bersih'
                        ];

                        const allowedAssistantRoutesForLaporanAnalisisKeuangan = [
                            'laporan analisis keuangan'
                        ];

                        let ContentTrafikSales = '';

                        if (allowedAssistantRoutesForTargetPenjualanTahunan.includes(data.condition)) {
                            const salesPerf = data.data_detail?.sales_performance;
                            
                            if (salesPerf && salesPerf.data) {
                                const formatRupiah = (num) => {
                                    return 'Rp ' + Number(num).toLocaleString('id-ID');
                                };

                                if (salesPerf.type === 'individual') {
                                    const s = salesPerf.data;
                                    const statusClass = s.status === 'achieved' ? 'badge-success' : 'badge-warning';
                                    const progressColor = s.status === 'achieved' ? '#28a745' : '#ffc107';
                                    const progressWidth = Math.min(s.percentage, 100);

                                    ContentTrafikSales = `
                                        <div class="card shadow-sm mb-4 mt-2    ">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Sales:</strong> ${s.nama}</p>
                                                        <p class="mb-2"><strong>Revenue:</strong> ${formatRupiah(s.revenue)}</p>
                                                        <p class="mb-3"><strong>Target:</strong> ${formatRupiah(s.presentase_kemampuan)}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-2">
                                                            <div class="d-flex justify-content-between">
                                                                <span>Progress</span>
                                                                <span>${s.percentage}%</span>
                                                            </div>
                                                            <div class="progress" style="height: 10px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                    style="width: ${progressWidth}%; background-color: ${progressColor};"
                                                                    aria-valuenow="${s.percentage}" aria-valuemin="0" aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="badge ${statusClass} p-2">${s.status.toUpperCase()}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }
                                
                                else if (salesPerf.type === 'all') {
                                    let rows = '';
                                    
                                    salesPerf.data.forEach((sales, index) => {
                                        const statusClass = sales.status === 'achieved' ? 'badge-success' : 'badge-warning';
                                        const textClass = sales.status === 'achieved' ? 'text-success' : 'text-warning';
                                        
                                        const targetValue = Number(sales.presentase_kemampuan).toLocaleString('id-ID', { useGrouping: false });
                                        
                                        rows += `
                                            <tr id="row-${sales.kode_karyawan}">
                                                <td class="text-center">${index + 1}</td>
                                                <td><strong>${sales.nama}</strong></td>
                                                <td class="text-right">${formatRupiah(sales.revenue)}</td>
                                                <td class="text-center">
                                                    <div class="input-group input-group-sm" style="max-width: 150px; float: right;">
                                                        <input type="text" 
                                                            class="form-control text-right target-input ${sales.id_detailPerson ? '' : 'is-invalid'}" 
                                                            value="${targetValue}" 
                                                            data-id-detail="${sales.id_detailPerson || ''}"
                                                            data-kode-karyawan="${sales.kode_karyawan}"
                                                            placeholder="Target"
                                                            ${!sales.id_detailPerson ? 'disabled' : ''}
                                                        >
                                                    </div>
                                                    <div class="loading-spinner" style="display: none; float: right; margin-right: 10px;">
                                                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                                    </div>
                                                    <div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </td>
                                                <td class="text-center ${textClass}"><strong>${sales.percentage}%</strong></td>
                                                <td class="text-center">
                                                    <span class="badge ${statusClass}">${sales.status.toUpperCase()}</span>
                                                </td>
                                            </tr>
                                        `;
                                    });

                                    let htmlTargetTahunanSales = '';

                                    Object.entries(data.data_detail.triwulan_data).forEach(([label, value]) => {
                                        htmlTargetTahunanSales += `
                                            <div class="col-md-6">
                                                <div class="card h-100 shadow-sm border-0">
                                                    <div class="card-body">
                                                        <h5 class="card-title">${label.replace('_', ' ')}</h5>
                                                        <p class="card-text">Rp ${value.toLocaleString('id-ID')}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    });

                                    ContentTrafikSales = `
                                        <div class="row">
                                            <div class="col">
                                                <div class="card shadow-sm mt-3">
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover mb-0">
                                                                <thead class="thead-light">
                                                                    <tr>
                                                                        <th class="text-center" width="5%">No</th>
                                                                        <th>Sales</th>
                                                                        <th class="text-right" width="20%">Revenue</th>
                                                                        <th class="text-right" width="20%">Target (Editable)</th>
                                                                        <th class="text-center" width="15%">Persentase</th>
                                                                        <th class="text-center" width="15%">Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    ${rows}
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="col">
                                                    <div class="card shadow-sm border-0 mt-3">
                                                        <div class="card-body">
                                                            <div class="row g-3">
                                                                ${htmlTargetTahunanSales}
                                                            </div>
                                                        </div>

                                                        <div class="mb-2 text-center">
                                                            <hr>
                                                            <p>Data Triwulan diambil dari tahun ${data.detail_jangka}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    `;

                                    setTimeout(() => {
                                        document.querySelectorAll('.target-input').forEach(input => {
                                            let timeout = null;
                                            
                                            input.addEventListener('input', function() {
                                                const el = this;
                                                const row = el.closest('tr');
                                                const spinner = row.querySelector('.loading-spinner');
                                                const feedback = row.querySelector('.update-feedback');
                                                
                                                clearTimeout(timeout);
                                                
                                                feedback.style.display = 'none';
                                                
                                                timeout = setTimeout(() => {
                                                    const idDetailPerson = el.dataset.idDetail;
                                                    const kodeKaryawan = el.dataset.kodeKaryawan;
                                                    const newTarget = el.value.replace(/\./g, ''); 
                                                    
                                                    if (!idDetailPerson) {
                                                        el.classList.add('is-invalid');
                                                        return;
                                                    }
                                                    
                                                    spinner.style.display = 'inline-block';
                                                    el.disabled = true;
                                                    
                                                    $.ajax({
                                                        url: "{{ route('kpi.overview.updateTargetPerSales') }}", 
                                                        method: 'POST',
                                                        data: {
                                                            _token: '{{ csrf_token() }}',
                                                            id_detailPerson: idDetailPerson,
                                                            kode_karyawan: kodeKaryawan,
                                                            presentase_kemampuan: newTarget
                                                        },
                                                        success: function(response) {
                                                            spinner.style.display = 'none';
                                                            el.disabled = false;
                                                            
                                                            feedback.style.display = 'inline-block';
                                                            el.classList.remove('is-invalid');
                                                            el.classList.add('is-valid');
                                                            
                                                            if (response.data) {
                                                                const percentageCell = row.querySelector('td:nth-child(5)');
                                                                const statusCell = row.querySelector('td:nth-child(6)');
                                                                
                                                                if (response.data.percentage) {
                                                                    percentageCell.innerHTML = `<strong class="${response.data.status === 'achieved' ? 'text-success' : 'text-warning'}">${response.data.percentage}%</strong>`;
                                                                }
                                                                
                                                                if (response.data.status) {
                                                                    const statusClass = response.data.status === 'achieved' ? 'badge-success' : 'badge-warning';
                                                                    statusCell.innerHTML = `<span class="badge ${statusClass}">${response.data.status.toUpperCase()}</span>`;
                                                                }
                                                            }
                                                            
                                                            setTimeout(() => {
                                                                feedback.style.display = 'none';
                                                                el.classList.remove('is-valid');
                                                            }, 2000);
                                                        },
                                                        error: function(xhr) {
                                                            spinner.style.display = 'none';
                                                            el.disabled = false;
                                                            
                                                            el.classList.add('is-invalid');
                                                            
                                                            console.error('Update failed:', xhr.responseText);
                                                        }
                                                    });
                                                }, 1000); 
                                            });
                                            
                                            input.addEventListener('blur', function() {
                                                const value = this.value.replace(/\./g, '');
                                                if (value) {
                                                    this.value = Number(value).toLocaleString('id-ID');
                                                }
                                            });
                                            
                                            input.addEventListener('focus', function() {
                                                const value = this.value.replace(/\./g, '');
                                                if (value) {
                                                    this.value = value;
                                                }
                                            });
                                        });
                                    }, 100);
                                }
                            }
                        } else if (allowedAssistantRoutesForPeningkatanKontribusiPelatihan.includes(data.condition)) {
                            ContentTrafikSales = `
                            <div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-end mb-4">
                                        <div>
                                            <div class="text-muted small">Total Kelas</div>
                                            <div class="fs-2 fw-semibold text-dark">
                                                ${data.data_detail.class_breakdown.total}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-white border h-100">
                                                <div class="text-muted small mb-2">Kelas Inixindo</div>
                                                <div class="fs-4 fw-semibold text-dark">
                                                    ${data.data_detail.class_breakdown.kelas_od}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-white border h-100">
                                                <div class="text-muted small mb-2">Kelas Orang Luar</div>
                                                <div class="fs-4 fw-semibold text-dark">
                                                    ${data.data_detail.class_breakdown.kelas_ol}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-white border h-100">
                                                <div class="text-muted small mb-2">Kelas Offline</div>
                                                <div class="fs-4 fw-semibold text-dark">
                                                    ${data.data_detail.class_breakdown.kelas_offline}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-white border h-100">
                                                <div class="text-muted small mb-2">Kelas Online</div>
                                                <div class="fs-4 fw-semibold text-dark">
                                                    ${data.data_detail.class_breakdown.kelas_online}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 rounded-3 bg-white border">
                                        <div class="fw-semibold mb-3">Kelas Inhouse</div>
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted small">Bandung</span>
                                            <span class="fw-semibold text-dark">
                                                ${data.data_detail.class_breakdown.Inhouse.kelas_inhouse}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted small">Luar Bandung</span>
                                            <span class="fw-semibold text-dark">
                                                ${data.data_detail.class_breakdown.Inhouse.kelas_inhouse_luar}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        } else {
                            ContentTrafikSales = '';
                        }

                        let contentPieChart = '';
                        
                        if (allowedDetailAssistantRoutes.includes(data.condition)) {
                            const fileUrl = data.data_detail.dataManual.manual_document;
                            const fileName = fileUrl ? fileUrl.split('/').pop() : '';
                            const fileExtension = fileName ? fileName.split('.').pop().toLowerCase() :
                                '';

                            const imageExtensions = ['jpg', 'jpeg', 'png'];
                            const pdfExtensions = ['pdf'];

                            let fileContent = '';

                            const getFileUrl = (path) => {
                                return path ? `/storage/${path}` : '';
                            };

                            const fullFileUrl = getFileUrl(fileUrl);

                            if (imageExtensions.includes(fileExtension)) {
                                fileContent = `
                                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                            <div class="mb-3" style="max-width: 100%; max-height: 300px;">
                                                <img src="${fullFileUrl}" alt="${fileName}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;">
                                            </div>
                                            <div class="mt-2 text-center">
                                                <a href="${fullFileUrl}" download="${fileName}" class="btn btn-primary btn-sm">
                                                    <i class="fa-solid fa-download me-1"></i>Download Gambar
                                                </a>
                                            </div>
                                            <div class="mt-2 small text-muted">
                                                <i class="fa-solid fa-file-image me-1"></i>${fileName}
                                            </div>
                                        </div>
                                    `;
                            } else if (pdfExtensions.includes(fileExtension)) {
                                fileContent = `
                                        <div class="w-100 h-100 d-flex flex-column">
                                            <div class="flex-grow-1 mb-3" style="min-height: 250px;">
                                                <iframe src="${fullFileUrl}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe>
                                            </div>
                                            <div class="text-center">
                                                <a href="${fullFileUrl}" download="${fileName}" class="btn btn-primary btn-sm">
                                                    <i class="fa-solid fa-download me-1"></i>Download PDF
                                                </a>
                                            </div>
                                            <div class="mt-2 small text-muted text-center">
                                                <i class="fa-solid fa-file-pdf me-1"></i>${fileName}
                                            </div>
                                        </div>
                                    `;
                            } else {
                                fileContent = `
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i>
                                            </div>
                                            <p class="text-muted mb-3">File tidak dapat ditampilkan</p>
                                            <p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p>
                                        </div>
                                    `;
                            }

                            contentPieChart = `
                                    <h6 class="fw-semibold mb-3 text-secondary">
                                        <i class="fa-solid fa-file me-2"></i>Dokumen Manual
                                    </h6>

                                    <div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">
                                        ${fileContent}
                                    </div>

                                    <div class="mt-3 small text-muted text-center">
                                        <i class="fa-solid fa-info-circle me-1"></i>
                                        Klik tombol download untuk menyimpan file
                                    </div>
                                `;
                        } else if (allowedDetailAssistantRoutesForRupiah.includes(data.condition)) {
                            contentPieChart = `
                                    <div class="mb-4">
                                        <h6 class="fw-semibold text-primary mb-1">
                                            <i class="fa-solid fa-wallet me-2"></i>${data.judul}
                                        </h6>
                                        <small class="text-muted">Ringkasan performa</small>
                                    </div>

                                    <div class="mb-4 p-3 rounded bg-light">
                                        <div class="text-muted small mb-1">Bulan Terakhir</div>
                                        <div class="fw-semibold">${labelBulanTerakhir}</div>
                                        <div class="fw-bold fs-6 text-dark">
                                            ${formatRupiah(nilaiBulanTerakhirRupiah)}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="text-muted small mb-2">Top Hari Tertinggi</div>

                                        ${top3HariTertinggi.map(([tanggal, nilai], index) => `
                                                                <div class="d-flex justify-content-between mb-1 ${index > 0 ? 'text-muted' : ''}">
                                                                    <span>${formatTanggalSingkat(tanggal)}</span>
                                                                    <span class="fw-semibold">${formatRupiah(nilai)}</span>
                                                                </div>
                                                            `).join('')}
                                    </div>

                                    <hr class="my-3">

                                    <div class="d-flex align-items-center gap-2 text-muted small">
                                        <i class="bi bi-person-circle fs-5"></i>
                                        <span class="fw-semibold text-dark">${karyawanTerkaitRupiah?.nama_lengkap ?? '-'}</span>
                                        <span>• ${karyawanTerkaitRupiah?.jabatan ?? '-'}</span>
                                    </div>
                                    `;

                        } else {
                            contentPieChart = `
                                    <h6 class="fw-semibold mb-3 text-secondary">
                                        <i class="fa-solid fa-chart-pie me-2"></i>Chart ${data.condition}
                                    </h6>

                                    <div class="chart-container flex-grow-1">
                                        <canvas id="MyChartDoughtnut"></canvas>
                                    </div>
                                `;
                        }

                        let contentStatisticChart = '';

                        if (allowedDetailAssistantRoutes.includes(data.condition)) {
                            contentStatisticChart = ``;
                        } else if (allowedAssistantRoutesForLaporanAnalisisKeuangan.includes(data.condition)) {
                            const bulanIndo = [
                                '',
                                'Januari',
                                'Februari',
                                'Maret',
                                'April',
                                'Mei',
                                'Juni',
                                'Juli',
                                'Agustus',
                                'September',
                                'Oktober',
                                'November',
                                'Desember'
                            ];
                            contentStatisticChart = `
                            <div class="row g-4">

                                ${(data.data_detail.analisa_data).map(item => `
                                <div class="col-md-4 mt-5">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="mb-2 p-3">
                                            <h5 class="fw-bold text-primary mb-1">${bulanIndo[item.month]}</h5>
                                            <small class="text-muted">Laporan Analisis Bulanan</small>
                                        </div>
                                        <div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;">

                                            <div class="mb-3">
                                                <p class="mb-0" style="text-align: justify;">
                                                    ${item.description}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-auto p-3">
                                            <a href="{{ asset('${item.file_paths}') }}" class="btn btn-sm btn-outline-primary w-100">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                `).join('')}

                            </div>
                            `;
                        } else if (allowedAssistantRoutesForPemasukanBersih.includes(data.condition)) {
                            const bulanIndo = [
                                "Januari", "Februari", "Maret", "April",
                                "Mei", "Juni", "Juli", "Agustus",
                                "September", "Oktober", "November", "Desember"
                            ];

                            contentStatisticChart = `
                                <div class="mt-4">
                                    <div class="row g-4">

                                        ${(data.data_detail.previous_quarter.data || []).map((item, index) => `
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <div class="card border-0 shadow-sm h-100 quarter-card">
                                                    <div class="card-body d-flex flex-column p-4">

                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div>
                                                                <h6 class="fw-semibold text-muted mb-1">Periode</h6>
                                                                <h5 class="fw-bold mb-0">
                                                                    ${bulanIndo[item.month - 1] ?? '-'}
                                                                </h5>
                                                            </div>
                                                            <span class="badge rounded-pill bg-${item.color} bg-opacity-10 text-${item.color} px-3 py-2">
                                                                Laporan
                                                            </span>
                                                        </div>

                                                        <div class="mb-3">
                                                            <h3 class="fw-bold text-dark mb-0">
                                                                Rp ${item.nilai ? Number(item.nilai).toLocaleString('id-ID') : '-'}
                                                            </h3>
                                                            <small class="text-muted">Total Pemasukan</small>
                                                        </div>

                                                        <div class="flex-grow-1">
                                                            <p class="text-muted small mb-2 description-text" id="desc-${index}">
                                                                ${item.description ?? '-'}
                                                            </p>
                                                            ${(item.description && item.description.length > 100) ? `
                                                                <button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${index}">
                                                                    Lihat Selengkapnya
                                                                </button>
                                                            ` : ''}
                                                        </div>

                                                        <div class="d-flex justify-content-end align-items-center mt-4">
                                                            <a href="{{ asset('${item.file_paths}') }}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" download>
                                                                <i class="fas fa-download"></i>
                                                                Download
                                                            </a>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}

                                    </div>
                                </div>

                                <style>
                                    .quarter-card {
                                        border-radius: 16px;
                                        transition: all 0.25s ease;
                                        background: #ffffff;
                                    }

                                    .quarter-card:hover {
                                        transform: translateY(-6px) scale(1.01);
                                        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
                                    }

                                    .quarter-card h3 {
                                        letter-spacing: 0.5px;
                                    }

                                    .quarter-card .badge {
                                        font-size: 12px;
                                        font-weight: 500;
                                    }

                                    .description-text {
                                        display: -webkit-box;
                                        -webkit-line-clamp: 3;
                                        -webkit-box-orient: vertical;
                                        overflow: hidden;
                                    }

                                    .description-text.expanded {
                                        -webkit-line-clamp: unset;
                                        overflow: visible;
                                    }

                                    .quarter-card .btn {
                                        border-radius: 8px;
                                        font-size: 13px;
                                    }
                                </style>
                            `;

                                        setTimeout(() => {
                                        document.querySelectorAll('.btn-toggle-desc').forEach(btn => {
                                            btn.addEventListener('click', function () {
                                                const targetId = this.getAttribute('data-target');
                                                const textEl = document.getElementById(targetId);

                                                if (textEl.classList.contains('expanded')) {
                                                    textEl.classList.remove('expanded');
                                                    this.innerText = 'Lihat Selengkapnya';
                                                } else {
                                                    textEl.classList.add('expanded');
                                                    this.innerText = 'Sembunyikan';
                                                }
                                            });
                                        });
                                    }, 0);
                        } else if (allowedDetailAssistantRoutesForPresentaseGapKompetensi.includes(data.condition)) {
                            contentStatisticChart = `
                                <div class="mt-4">
                                    <div class="card shadow-sm border-0 rounded-4">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3">Input Presentase Kemampuan Programmer</h6>

                                            <form id="formGapKompetensi">
                                                @php
                                                    $allowed = auth()->user()->jabatan === 'Koordinator ITSM';
                                                @endphp

                                                <!-- HEADER -->
                                                <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                                                    <div class="col-md-4">Nama Karyawan</div>
                                                    <div class="col-md-4">Kemampuan (%)</div>
                                                    <div class="col-md-4">Standar (%)</div>
                                                </div>

                                                <!-- DATA -->
                                                ${(data.karyawan || []).map((item, index) => {

                                                    const kemampuan = parseFloat(item.presentase_kemampuan ?? 0);
                                                    const standar = parseFloat(item.presentase_standar ?? 100);

                                                    let badge = '';
                                                    let rowClass = '';

                                                    if (kemampuan === 0) {
                                                        badge = `<span class="badge bg-danger">0%</span>`;
                                                    } else if (kemampuan < standar) {
                                                        badge = `<span class="badge bg-warning text-dark">Not Achieved</span>`;
                                                    } else {
                                                        badge = `<span class="badge bg-success">Achieved</span>`;
                                                    }

                                                    return `
                                                        <div class="row mb-2 align-items-center p-2 rounded">
                                                            
                                                            <div class="col-md-4 d-flex justify-content-between align-items-center">
                                                                <span>${item.nama_lengkap ?? '-'}</span>
                                                                ${badge}
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="number" step="0.1" class="form-control kemampuan-input"
                                                                    name="data[${index}][kemampuan]"
                                                                    value="${kemampuan}" {{ $allowed ? '' : 'disabled' }}>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="number" step="0.1" class="form-control standar-input"
                                                                    name="data[${index}][standar]"
                                                                    value="${standar}" {{ $allowed ? '' : 'disabled' }}>
                                                            </div>

                                                            <input type="hidden" name="data[${index}][id]" value="${item.id}">
                                                        </div>
                                                    `;
                                                }).join('')}

                                                <!-- BUTTON -->
                                                 @if ($allowed)
                                                    <div class="mt-3">
                                                        <button type="submit" class="btn btn-primary">
                                                            Simpan
                                                        </button>
                                                    </div>
                                                @endif

                                            </form>
                                        </div>
                                    </div>
                                </div>
                                `;

                            $(document).on('submit', '#formGapKompetensi', function(e) {
                                e.preventDefault();

                                let formData = $(this).serialize();

                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });

                                $.ajax({
                                    url: "{{ route('kpi.updateGapKompetensi') }}",
                                    method: 'POST',
                                    data: formData,
                                    success: function(res) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil!',
                                            text: 'berhasil diupdate.',
                                            timer: 2000,
                                            showConfirmButton: false
                                        }).then(() => {
                                            $('#detailTargetModal').modal('hide');
                                        });
                                        loadContentForm();
                                    },
                                    error: function(err) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal!',
                                            html: msg
                                        });
                                        console.log(err);
                                    }
                                });
                            });

                        } else {
                            contentStatisticChart = `
                                    <div class="mt-4">
                                        <div class="card shadow-sm border-0 rounded-4">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-3">
                                                    <h6 class="fw-semibold mb-0">Statistik ${data.condition}</h6>
                                                    <div class="d-flex gap-2">
                                                        <select class="form-select form-select-sm" id="filterType">
                                                            <option value="year">Per Tahun</option>
                                                            <option value="month">Per Bulan</option>
                                                        </select>
                                                        <select class="form-select form-select-sm d-none" id="filterMonth">
                                                            <option value="">Pilih Bulan</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div style="height:300px">
                                                    <canvas id="StatisticChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                        }

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
                                                        <div class="col">
                                                            <small class="text-muted d-block">Target</small>
                                                            <h4 class="fw-bold mb-0" style="font-size: 25px">${targetValue}</h4>
                                                        </div>
                                                        <div class="col">
                                                            <small class="text-muted d-block">Progress</small>
                                                            <h1 class="fw-bold text-${bgCard} mb-0" style="font-size: 55px;">${progressValue}</h1>
                                                        </div>
                                                        <div class="col">
                                                            <small class="text-muted d-block">Gap</small>
                                                            <h4 class="fw-bold text-danger mb-0" style="font-size: 25px">-${gapValue}</h4>
                                                        </div>
                                                    </div>

                                                    <div class="position-relative mb-3">
                                                        <div class="progress" style="height:18px;">
                                                            <div class="progress-bar bg-${bgCard} progress-bar-striped progress-bar-animated"
                                                                style="width: ${FormatedProgress}%"></div>
                                                        </div>
                                                        <div class="position-absolute bg-light top-0" style="left:${data.nilai_target}%; height:18px; width:2px;"></div>
                                                    </div>

                                                    <div class="text-muted mb-4">
                                                        <i class="fa-solid fa-calendar-days me-1"></i>
                                                        Deadline: <strong>${data.tenggat_waktu}</strong>
                                                    </div>

                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 kpi-card">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        <div class="me-2 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                                            <i class="fa-solid fa-chart-line text-primary"></i>
                                                                        </div>
                                                                        <h6 class="mb-0 fw-semibold text-secondary">INFORMASI KPI</h6>
                                                                    </div>
                                                                    <div class="row mb-3">
                                                                        <div class="col-4 label">KPI Divisi</div>
                                                                        <div class="col-8 value">${data.divisi_kpi}</div>
                                                                    </div>
                                                                    <div class="row mb-3">
                                                                        <div class="col-4 label">KPI Jabatan</div>
                                                                        <div class="col-8 value">${data.jabatan_kpi}</div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-4 label">Pembuat</div>
                                                                        <div class="col-8 value">${data.pembuat}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 participant-card h-100">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        <div class="me-2 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                                            <i class="fa-solid fa-users text-success"></i>
                                                                        </div>
                                                                        <h6 class="mb-0 fw-semibold text-secondary">KARYAWAN</h6>
                                                                    </div>
                                                                    <div class="participant-list" style="overflow-y: scroll; max-height: 140px;">
                                                                        <div class="d-flex align-items-center py-2 participant-item">
                                                                            <div class="avatar me-3">1</div>
                                                                            <div class="flex-grow-1">
                                                                                <div class="fw-semibold text-dark small">${window.authUser.nama}</div>
                                                                                <div class="text-muted small">${window.authUser.jabatan}</div>
                                                                            </div>
                                                                        </div>
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
                                                <div class="card-body d-flex flex-column">
                                                    ${contentPieChart}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    ${ContentTrafikSales}
                                    ${contentStatisticChart}
                                </div>
                            </div>

                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        `);

                        let statisticChart = null;
                        const statisticCtx = document.getElementById('StatisticChart');

                        function renderStatistic(labels, values, label) {
                            if (statisticChart) statisticChart.destroy();

                            const maxValue = values.length > 0 ? Math.max(...values) : 0;
                            const suggestedMax = maxValue + 3;

                            statisticChart = new Chart(statisticCtx, {
                                type: 'line',
                                data: {
                                    labels,
                                    datasets: [{
                                        label,
                                        data: values,
                                        borderColor: '#4e73df',
                                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                                        tension: 0.4,
                                        fill: true
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            suggestedMax: suggestedMax,
                                            ticks: {
                                                count: 6,
                                                precision: 0,
                                                callback: function(value) {
                                                    return Math.round(value);
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }

                        const monthLabels = Object.keys(monthlyData).map(key => getNamaBulan(key));
                        const monthValues = Object.values(monthlyData);
                        renderStatistic(monthLabels, monthValues, 'Rata-rata');

                        $('#filterType').off('change').on('change', function() {
                            if (this.value === 'month') {
                                $('#filterMonth').removeClass('d-none').empty().append(
                                    '<option value="">Pilih Bulan</option>');
                                Object.keys(dailyData).forEach(monthKey => {
                                    $('#filterMonth').append(
                                        `<option value="${monthKey}">${getNamaBulan(monthKey)}</option>`
                                    );
                                });
                                if (statisticChart) statisticChart.destroy();
                            } else {
                                $('#filterMonth').addClass('d-none');
                                renderStatistic(monthLabels, monthValues, 'Rata-rata');
                            }
                        });

                        $('#filterMonth').off('change').on('change', function() {
                            const selectedMonth = this.value;
                            if (!selectedMonth || !dailyData[selectedMonth]) return;

                            const dayLabels = Object.keys(dailyData[selectedMonth]).map(d => d
                                .substring(8));
                            const dayValues = Object.values(dailyData[selectedMonth]);
                            renderStatistic(dayLabels, dayValues,
                                `Tanggal ${getNamaBulan(selectedMonth)}`);
                        });
                    }

                    const modalEl = document.getElementById('detailTargetModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memuat detail target', 'error');
                }
            });
        });
    </script>
@endsection
