@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- ===== STYLE KHUSUS HALAMAN INI ===== --}}
    <style>
        /* Page Header */
        .page-header-modern {
            margin-bottom: 1.5rem;
        }

        .page-header-modern .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .25rem;
        }

        .page-title-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(99, 102, 241, .1), rgba(139, 92, 246, .1));
            color: #6366f1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .breadcrumb-modern {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-modern .breadcrumb-item {
            font-size: .85rem;
            color: #64748b;
        }

        .breadcrumb-modern .breadcrumb-item.active {
            color: #6366f1;
            font-weight: 500;
        }

        /* Back Button */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: .875rem;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            transition: all .2s ease;
            margin-bottom: 1.5rem;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateX(-2px);
        }

        /* Content Card */
        .content-card {
            background: #fff;
            border-radius: 16px;
            border: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .content-card .card-body {
            padding: 1.5rem;
        }

        .content-card .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .content-card .card-title i {
            color: #6366f1;
        }

        /* Info Panel (Left Side) */
        .info-panel {
            background: linear-gradient(135deg, rgba(99, 102, 241, .03), rgba(139, 92, 246, .03));
            border: 1px solid rgba(99, 102, 241, .1);
            border-radius: 14px;
            padding: 1.5rem;
        }

        .info-panel .info-item {
            margin-bottom: 1rem;
        }

        .info-panel .info-label {
            font-size: .75rem;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: .35rem;
        }

        .info-panel .info-value {
            font-size: .9rem;
            font-weight: 600;
            color: #1e293b;
            padding: .6rem .9rem;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        /* Evaluator List */
        .evaluator-list {
            max-height: 180px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .evaluator-list::-webkit-scrollbar {
            width: 6px;
        }

        .evaluator-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .evaluator-list .list-group-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: .4rem;
            padding: .5rem .75rem;
            font-size: .85rem;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: all .2s ease;
        }

        .evaluator-list .list-group-item:hover {
            background: rgba(99, 102, 241, .05);
            border-color: #6366f1;
            color: #6366f1;
            transform: translateX(4px);
        }

        /* Modern Tab Navigation */
        .modern-tab-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 1.25rem;
        }

        .modern-tab-btn {
            padding: .55rem 1.1rem;
            border: 1px solid transparent;
            background: #fff;
            color: #475569;
            font-weight: 600;
            font-size: .8rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all .2s ease;
            white-space: nowrap;
            text-decoration: none;
        }

        .modern-tab-btn:hover {
            border-color: #cbd5e1;
            background: #fff;
            color: #1e293b;
        }

        .modern-tab-btn.active-tab {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(99, 102, 241, .25);
        }

        @media (max-width: 768px) {
            .modern-tab-nav {
                flex-wrap: nowrap;
                overflow-x: auto;
                scrollbar-width: thin;
            }

            .modern-tab-nav::-webkit-scrollbar {
                height: 4px;
            }

            .modern-tab-nav::-webkit-scrollbar-thumb {
                background: rgba(99, 102, 241, .3);
                border-radius: 2px;
            }
        }

        /* Modern Table */
        .modern-table {
            border: 0 !important;
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table thead th {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
            border-bottom: 2px solid #e2e8f0 !important;
            font-weight: 700;
            color: #475569;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: .9rem .75rem !important;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .modern-table tbody td {
            padding: .75rem !important;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            font-size: .85rem;
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
        }

        .modern-table .evaluator-header {
            background: linear-gradient(135deg, rgba(99, 102, 241, .08), rgba(139, 92, 246, .08)) !important;
            font-weight: 700;
            color: #1e293b;
            font-size: .9rem;
            padding: .9rem !important;
        }

        .modern-table .total-row {
            background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
            color: #fff !important;
            font-weight: 700;
        }

        .modern-table .total-row td {
            color: #fff !important;
            border: none !important;
        }

        .modern-table .grand-total-row {
            background: linear-gradient(135deg, #1e293b, #334155) !important;
            color: #fff !important;
            font-weight: 700;
        }

        .modern-table .grand-total-row td {
            color: #fff !important;
            border: none !important;
        }

        /* Scrollable Table Container */
        .scrollable-table-wrapper {
            max-height: 650px;
            overflow-y: auto;
            overflow-x: auto;
            scrollbar-width: thin;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .scrollable-table-wrapper::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .scrollable-table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .scrollable-table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .scrollable-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Chart Container */
        .chart-wrapper {
            position: relative;
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
        }

        .chart-wrapper canvas {
            max-height: 300px !important;
            width: 100% !important;
        }

        /* Action Buttons */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem 1.1rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            border: 0;
            transition: all .2s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-action.primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, .25);
        }

        .btn-action.primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, .35);
            color: #fff;
        }

        .btn-action.success {
            background: linear-gradient(135deg, #34d399, #059669);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, .25);
        }

        .btn-action.success:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, .35);
            color: #fff;
        }

        .btn-action.danger {
            background: linear-gradient(135deg, #f87171, #dc2626);
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, .25);
        }

        .btn-action.danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, .35);
            color: #fff;
        }

        .btn-action.light {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-action.light:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* Textarea Modern */
        .modern-textarea {
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: .9rem 1.1rem;
            font-size: .9rem;
            background: #fff;
            transition: all .3s ease;
            resize: vertical;
            width: 100%;
        }

        .modern-textarea:focus {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
            outline: none;
        }

        /* Form Label */
        .form-label-modern {
            font-weight: 700;
            color: #334155;
            font-size: .85rem;
            margin-bottom: .5rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .form-label-modern i {
            color: #6366f1;
        }

        /* Loading Spinner */
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid transparent;
            border-top: 6px solid #a78bfa;
            border-right: 6px solid #38bdf8;
            border-bottom: 6px solid #34d399;
            border-left: 6px solid #facc15;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Absensi Card */
        .absensi-card {
            background: linear-gradient(135deg, rgba(245, 158, 11, .05), rgba(245, 158, 11, .02));
            border: 1px solid rgba(245, 158, 11, .15);
            border-radius: 14px;
            padding: 1.25rem;
        }

        .absensi-card .card-title {
            color: #d97706;
        }

        .absensi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .absensi-table thead th {
            background: rgba(245, 158, 11, .1);
            color: #92400e;
            font-weight: 700;
            font-size: .8rem;
            text-transform: uppercase;
            padding: .75rem;
            border-radius: 8px;
        }

        .absensi-table tbody td {
            padding: .9rem;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .info-panel {
                margin-bottom: 1.5rem;
            }
        }
    </style>

    <div class="container content-wrapper mt-4">
        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#6366f1'
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#ef4444'
                });
            </script>
        @endif

        @if ($errors->any())
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    confirmButtonColor: '#ef4444'
                });
            </script>
        @endif

        {{-- Back Button --}}
        <a href="{{ route('ketegoriKPI.get') }}" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Penilaian
        </a>

        {{-- Hidden Inputs --}}
        @if ($kodeForm)
            <input type="hidden" id="kodeForm" name="kodeForm" value="{{ $kodeForm }}">
        @endif
        @if ($id_karyawan)
            <input type="hidden" id="id_karyawan" name="id_karyawan" value="{{ $id_karyawan }}">
        @endif
        <input type="hidden" name="jenis_form" id="jenis_form" value="{{ $tipe }}">

        {{-- Main Content --}}
        <div class="row g-4">
        <div id="shareEmail" class="mb-3"></div>
            <div class="col-lg-4">
                <div class="content-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa-solid fa-user-tie"></i> Informasi Penilaian
                        </h5>
                        <div class="info-panel" id="content_utama">
                            {{-- Content will be loaded via AJAX --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Table & Charts --}}
            <div class="col-lg-8">
                {{-- Main Assessment Table --}}
                <div class="content-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa-solid fa-table-list"></i> Detail Penilaian
                        </h5>

                        {{-- Tab Navigation --}}
                        <div class="modern-tab-nav" id="jenis-penilaian-tab" role="tablist">
                            {{-- Tabs will be loaded via AJAX --}}
                        </div>

                        {{-- Table --}}
                        <div class="scrollable-table-wrapper">
                            <table class="table modern-table" id="table-fixed">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Kriteria</th>
                                        <th style="width: 20%;">Sub Kriteria</th>
                                        <th style="width: 10%;">Bobot</th>
                                        <th style="width: 12%;">Nilai</th>
                                        <th style="width: 15%;">Rata-Rata</th>
                                        <th style="width: 13%;">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="body_content">
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                                            Memuat data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Absensi Card --}}
                <div class="content-card">
                    <div class="card-body">
                        <div class="absensi-card">
                            <h5 class="card-title mb-3">
                                <i class="fa-solid fa-calendar-check" style="color: #d97706;"></i> Data Jumlah Absensi
                            </h5>
                            <table class="absensi-table">
                                <thead>
                                    <tr>
                                        <th><i class="fa-solid fa-clock me-1"></i> Telat</th>
                                        <th><i class="fa-solid fa-bed me-1"></i> Sakit</th>
                                        <th><i class="fa-solid fa-envelope me-1"></i> Izin</th>
                                    </tr>
                                </thead>
                                <tbody id="body_content_absensi">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Memuat...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Charts --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="content-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa-solid fa-chart-column"></i> Trendline Tahun Ini
                                </h5>
                                <div class="chart-wrapper">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="content-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa-solid fa-chart-line"></i> Trendline Progress
                                </h5>
                                <div class="chart-wrapper">
                                    <canvas id="lineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SCRIPTS ===== --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let globalEvaluators = [];
        let globalKriteria = [];
        let globalEvaluated = {};
        let globalTahun = '';

        $(document).ready(function() {
            loadData();
            loadChartData();
        });

        function pengubahFormat(angka) {
            return angka.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        $('#jenis-penilaian-tab').on('click', '.modern-tab-btn', function() {
            $('#jenis-penilaian-tab .modern-tab-btn').removeClass('active-tab');
            $(this).addClass('active-tab');
            const jenis = $(this).data('jenis');
            renderTabel(jenis);
        });

        function loadData() {
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('kodeForm', $('#kodeForm').val());
            formData.append('id_karyawan', $('#id_karyawan').val());
            formData.append('tahun', $('#selectTahun').val());
            formData.append('jenis_form', $('#jenis_form').val());

            $.ajax({
                url: "{{ route('penilaian.detail.get') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    const data = response.data[0];
                    globalEvaluators = data.data.evaluator;
                    globalKriteria = data.data.dataKriteria;
                    globalEvaluated = data.evaluated;
                    globalChart = data.chart;
                    globalAbsensi = data.dataAbsen;
                    globalTahun = data.evaluated.tahun;

                    let content_utama = $('#content_utama');
                    content_utama.empty();

                    let content_absensi = $('#body_content_absensi');
                    content_absensi.empty();

                    if (!globalAbsensi.isEmpty) {
                        content_absensi.append(`
                        <tr>
                            <td>${globalAbsensi.telat}</td>
                            <td>${globalAbsensi.sakit}</td>
                            <td>${globalAbsensi.izin}</td>
                        </tr>
                    `);
                    } else {
                        content_absensi.append(`
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Tidak ada data absensi</td>
                        </tr>
                    `);
                    }

                    const jenisList = [...new Set(globalEvaluators.map(ev => ev.jenis_penilaian))];
                    let kodeForm = globalKriteria.length > 0 ? globalKriteria[0].kodeForm : '';
                    let id_karyawan = globalEvaluated.id_karyawan ?? '';

                    let jenisHTML = `
                    <a class="modern-tab-btn active-tab" data-jenis="all">
                        <i class="fa-solid fa-layer-group me-1"></i> Semua
                    </a>
                `;

                    let emailSend = `
                    <div class="d-flex flex-wrap gap-2">
                        <button id="kirimEmail" class="btn-action success" data-kodeform="${kodeForm}" data-id="${id_karyawan}">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Email
                        </button>
                        <form method="POST" action="{{ route('penilaian.download.pdf') }}" class="d-flex gap-2">
                            @csrf
                            <input type="hidden" name="kodeForm" value="${kodeForm}">
                            <input type="hidden" name="id_karyawan" value="${id_karyawan}">
                            <button type="submit" name="tipe" value="office" class="btn-action danger">
                                <i class="fa-solid fa-file-pdf"></i> PDF Office
                            </button>
                            <button type="submit" name="tipe" value="non_office" class="btn-action danger">
                                <i class="fa-solid fa-file-pdf"></i> PDF Non-Office
                            </button>
                        </form>
                    </div>
                `;

                    jenisList.forEach(jenis => {
                        let label;
                        let icon;
                        if (jenis === 'General Manager') {
                            label = 'General Manager';
                            icon = 'fa-crown';
                        } else if (jenis === 'Manager/SPV/Team Leader (Atasan Langsung)') {
                            label = 'Koordinator';
                            icon = 'fa-user-tie';
                        } else if (jenis === 'Rekan Kerja (Satu Divisi)') {
                            label = 'Satu Divisi';
                            icon = 'fa-users';
                        } else if (jenis === 'Pekerja (Beda Divisi)') {
                            label = 'Beda Divisi';
                            icon = 'fa-people-arrows';
                        } else if (jenis === 'Self Apprisial') {
                            label = 'Self Appraisal';
                            icon = 'fa-user-check';
                        } else {
                            label = jenis;
                            icon = 'fa-user';
                        }

                        jenisHTML += `
                        <a class="modern-tab-btn" data-jenis="${jenis}">
                            <i class="fa-solid ${icon} me-1"></i> ${label}
                        </a>
                    `;
                    });

                    $('#shareEmail').html(emailSend);
                    $('#jenis-penilaian-tab').html(jenisHTML);

                    let listEvaluatorHTML = globalEvaluators.map(ev =>
                        `<li data-target="${ev.nama}-${ev.jenis_penilaian}" class="list-group-item">
                        <i class="fa-solid fa-user me-2 text-primary"></i>${ev.nama}
                    </li>`
                    ).join('');

                    content_utama.append(`
                    <div class="info-item">
                        <div class="info-label"><i class="fa-solid fa-file-lines me-1"></i> Jenis Form</div>
                        <div class="info-value">{{ $tipe }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa-solid fa-users me-1"></i> Evaluator</div>
                        <ul class="list-group evaluator-list ms-0">${listEvaluatorHTML}</ul>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa-solid fa-user-check me-1"></i> Yang Dinilai</div>
                        <div class="info-value">${globalEvaluated.nama}</div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="info-item mb-0">
                                <div class="info-label"><i class="fa-solid fa-calendar-day me-1"></i> Semester</div>
                                <div class="info-value text-center">${globalEvaluated.quartal}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-item mb-0">
                                <div class="info-label"><i class="fa-solid fa-calendar me-1"></i> Tahun</div>
                                <div class="info-value text-center">${globalEvaluated.tahun}</div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <form method="post" action="{{ route('penilaian.sendCatatan') }}">
                        @csrf
                        <input type="hidden" name="id_karyawan" value="${globalEvaluated.id_karyawan}">
                        <input type="hidden" name="quartal" value="${globalEvaluated.quartal}">
                        <input type="hidden" name="tahun" value="${globalEvaluated.tahun}">
                        <input type="hidden" name="kode_form" value="${globalEvaluated.kode_form}">
                        <div class="info-item">
                            <label class="form-label-modern">
                                <i class="fa-solid fa-note-sticky"></i> Catatan
                            </label>
                            <textarea class="modern-textarea" placeholder="Berikan catatan..." rows="4" name="catatan">${globalEvaluated.catatan === 'null' || globalEvaluated.catatan === null ? '' : globalEvaluated.catatan}</textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn-action primary">
                                <i class="fa-solid fa-save"></i> Simpan Catatan
                            </button>
                        </div>
                    </form>
                `);

                    renderTabel('all');
                }
            });
        }

        function renderTabel(filterJenis) {
            let content = $('#body_content');
            content.empty();

            const persentaseJenis = {
                'General Manager': 35,
                'Manager/SPV/Team Leader (Atasan Langsung)': 30,
                'Rekan Kerja (Satu Divisi)': 20,
                'Pekerja (Beda Divisi)': 10,
                'Self Apprisial': 5
            };

            let groupRata2 = {};
            let filteredEvaluators = filterJenis === 'all' ? globalEvaluators : globalEvaluators.filter(ev => ev
                .jenis_penilaian === filterJenis);

            if (filteredEvaluators.length === 0) {
                content.append(
                    `<tr><td colspan="6" class="text-center py-5 text-muted"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>Tidak Ada Data</td></tr>`
                    );
                return;
            }

            filteredEvaluators.forEach(evaluator => {
                let nilaiList = evaluator.nilai;
                let nilaiIndex = 0;
                globalKriteria.forEach(kriteria => {
                    kriteria.detailKriteria.forEach(sub => {
                        const nilaiItem = nilaiList[nilaiIndex++] || {
                            nilai: '-',
                            pesan: '-'
                        };
                        const nilai = parseFloat(nilaiItem.nilai);
                        if (sub.tipe_input !== 'textarea' && !isNaN(nilai)) {
                            groupRata2[evaluator.jenis_penilaian] = groupRata2[evaluator
                                .jenis_penilaian] || {};
                            groupRata2[evaluator.jenis_penilaian][kriteria.kriteria] = groupRata2[
                                evaluator.jenis_penilaian][kriteria.kriteria] || {};
                            groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub
                                .sub_kriteria
                            ] = groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub
                                .sub_kriteria
                            ] || [];
                            groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub
                                .sub_kriteria
                            ].push(nilai);
                        }
                    });
                });
            });

            let rata2Hasil = {};
            for (const jenis in groupRata2) {
                rata2Hasil[jenis] = {};
                for (const kriteria in groupRata2[jenis]) {
                    rata2Hasil[jenis][kriteria] = {};
                    for (const sub in groupRata2[jenis][kriteria]) {
                        let arr = groupRata2[jenis][kriteria][sub];
                        let avg = arr.reduce((a, b) => a + b, 0) / arr.length;
                        rata2Hasil[jenis][kriteria][sub] = avg;
                    }
                }
            }

            let jenisTotalRaw = {};

            filteredEvaluators.forEach(evaluator => {
                content.append(`
                <tr id="${evaluator.nama}-${evaluator.jenis_penilaian}" class="evaluator-header">
                    <td colspan="6">
                        <i class="fa-solid fa-user me-2"></i>${evaluator.nama} 
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size: .75rem;">${evaluator.jenis_penilaian}</span>
                    </td>
                </tr>
            `);

                let nilaiList = evaluator.nilai;
                let nilaiIndex = 0;
                let totalSkorEvaluator = 0;

                globalKriteria.forEach(kriteria => {
                    const subKriteriaList = kriteria.detailKriteria;
                    const rowspan = subKriteriaList.length;
                    subKriteriaList.forEach((sub, idxSub) => {
                        const nilaiItem = nilaiList[nilaiIndex++] || {
                            nilai: '-',
                            pesan: '-'
                        };
                        const nilai = nilaiItem.nilai;
                        const pesan = nilaiItem.pesan;
                        const tipe = sub.tipe_input;
                        const bobot = parseFloat(sub.bobot);
                        let dataNilai = '';
                        if (tipe === 'textarea') {
                            dataNilai =
                                `<td colspan="4" style="font-style: italic; color: #64748b;">${pesan && pesan.trim() !== '' ? pesan : '-'}</td>`;
                        } else {
                            const nilaiAngka = parseFloat(nilai);
                            const rataData = rata2Hasil[evaluator.jenis_penilaian]?.[kriteria
                                .kriteria
                            ]?.[sub.sub_kriteria];
                            let rata = '-';
                            let skor = 0;
                            if (!isNaN(nilaiAngka)) {
                                rata = rataData !== undefined ? rataData : nilaiAngka;
                                skor = (rata * bobot) / 100;
                                totalSkorEvaluator += skor;
                            }
                            dataNilai = `
                            <td><span class="badge bg-light text-dark border">${bobot}%</span></td>
                            <td class="fw-semibold">${nilai}</td>
                            <td>${rata === '-' ? '-' : pengubahFormat(rata)}</td>
                            <td class="fw-bold text-primary">${rata === '-' ? '-' : pengubahFormat(skor)}</td>
                        `;
                        }

                        const kriteriaText = kriteria.kriteria;
                        const subKriteriaText = sub.sub_kriteria;
                        const isKriteriaLong = kriteriaText.length > 30;
                        const isSubKriteriaLong = subKriteriaText.length > 30;
                        const displayKriteria = isKriteriaLong ? kriteriaText.substring(0, 30) +
                            '...' : kriteriaText;
                        const displaySubKriteria = isSubKriteriaLong ? subKriteriaText.substring(0,
                            30) + '...' : subKriteriaText;
                        const fullKriteriaContent = isKriteriaLong ?
                            `<span class="full-text" style="display:none;">${kriteriaText}</span><span class="short-text">${displayKriteria}</span><button class="btn btn-sm btn-link text-primary p-0 ms-1 read-more-btn" type="button" onclick="toggleText(this)">...</button>` :
                            kriteriaText;
                        const fullSubKriteriaContent = isSubKriteriaLong ?
                            `<span class="full-text" style="display:none;">${subKriteriaText}</span><span class="short-text">${displaySubKriteria}</span><button class="btn btn-sm btn-link text-primary p-0 ms-1 read-more-btn" type="button" onclick="toggleText(this)">...</button>` :
                            subKriteriaText;

                        content.append(`
                        <tr>
                            ${idxSub === 0 ? `<td class="text-left fw-semibold">${fullKriteriaContent}</td>` : ''}
                            <td style="text-align: left;">${fullSubKriteriaContent}</td>
                            ${dataNilai}
                        </tr>
                    `);
                    });
                });

                content.append(`
                <tr class="total-row">
                    <td colspan="5" class="text-end">Total (${evaluator.nama})</td>
                    <td class="text-center">${pengubahFormat(totalSkorEvaluator)}</td>
                </tr>
            `);

                const jenis = evaluator.jenis_penilaian;
                if (!jenisTotalRaw.hasOwnProperty(jenis)) {
                    jenisTotalRaw[jenis] = totalSkorEvaluator;
                }
            });

            let jenisTotalPost = {};
            for (const jenis in jenisTotalRaw) {
                const persen = persentaseJenis[jenis] || 0;
                jenisTotalPost[jenis] = (jenisTotalRaw[jenis] * persen) / 100;
            }

            let totalSemuaSkor = 0;
            if (filterJenis === 'all') {
                totalSemuaSkor = Object.values(jenisTotalPost).reduce((a, b) => a + b, 0);
            } else {
                totalSemuaSkor = jenisTotalPost[filterJenis] || 0;
            }

            let grade = '';
            let keterangan = '';
            if (totalSemuaSkor >= 90) {
                grade = 'A';
                keterangan = 'Sangat Baik';
            } else if (totalSemuaSkor >= 80) {
                grade = 'B';
                keterangan = 'Baik';
            } else if (totalSemuaSkor >= 70) {
                grade = 'C';
                keterangan = 'Cukup';
            } else if (totalSemuaSkor >= 60) {
                grade = 'D';
                keterangan = 'Kurang';
            } else {
                grade = 'E';
                keterangan = 'Sangat Kurang';
            }

            content.append(`
            <tr class="grand-total-row">
                <td colspan="5" class="text-end">Total Semua Nilai</td>
                <td class="text-center fs-5">${pengubahFormat(totalSemuaSkor)}</td>
            </tr>
            <tr class="grand-total-row">
                <td colspan="5" class="text-end">Kriteria</td>
                <td class="text-center">${keterangan}</td>
            </tr>
            <tr class="grand-total-row">
                <td colspan="5" class="text-end">Grade</td>
                <td class="text-center fs-4 fw-bold">${grade}</td>
            </tr>
        `);

            tampilkanChartTahunIni(globalChart.quartal);
            tampilkanChartSemuaTahun(globalChart.all);
        }

        function toggleText(button) {
            const container = $(button).closest('td');
            const fullText = container.find('.full-text');
            const shortText = container.find('.short-text');
            const dots = button;
            if (fullText.is(':visible')) {
                fullText.hide();
                shortText.show();
                dots.text('...');
            } else {
                fullText.show();
                shortText.hide();
                dots.text('Sembunyikan');
            }
        }

        $(document).on('click', '.evaluator-list .list-group-item', function() {
            const targetId = $(this).data('target');
            const evaluatorRow = document.getElementById(targetId);
            if (evaluatorRow) {
                $('html, body').animate({
                    scrollTop: $(evaluatorRow).offset().top - 100
                }, 500);
            }
        });

        $(document).on('click', '#kirimEmail', function(e) {
            e.preventDefault();
            let kodeForm = $(this).data('kodeform');
            let id_karyawan = $(this).data('id');
            $.ajax({
                url: "{{ route('penilaian.email') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    kodeForm: kodeForm,
                    id_karyawan: id_karyawan
                },
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Email berhasil dikirim!',
                        confirmButtonColor: '#6366f1'
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: `Gagal mengirim email: ${xhr.responseText}`,
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        });
    </script>

    <script>
        let chartTahunIni = null;
        let chartAllYears = null;

        function loadChartData() {
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('kodeForm', $('#kodeForm').val());
            formData.append('id_karyawan', $('#id_karyawan').val());
            formData.append('tahun', $('#selectTahun').val());

            $.ajax({
                url: "{{ route('penilaian.detailChart.get') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.chartQuartal) tampilkanChartTahunIni(res.chartQuartal);
                    if (res.chartAllYears) tampilkanChartSemuaTahun(res.chartAllYears);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }

        const selectTahun = document.getElementById("selectTahun");
        if (selectTahun) {
            const tahunSekarang = new Date().getFullYear();
            for (let tahun = tahunSekarang; tahun <= tahunSekarang + 10; tahun++) {
                const option = document.createElement("option");
                option.value = tahun;
                option.textContent = tahun;
                if (tahun === tahunSekarang) option.selected = true;
                selectTahun.appendChild(option);
            }
            $('#selectTahun').on('change', function() {
                loadChartData();
            });
        }

        function tampilkanChartTahunIni(quartalData) {
            const ctx = document.getElementById("barChart").getContext("2d");
            if (chartTahunIni) chartTahunIni.destroy();

            const labels = [];
            const dataValues = [];

            Object.entries(quartalData).forEach(([quartal, forms]) => {
                if (typeof forms === "object") {
                    Object.entries(forms).forEach(([kodeForm, skor]) => {
                        labels.push(`${quartal}`);
                        dataValues.push(parseFloat(skor).toFixed(2));
                    });
                } else {
                    labels.push(quartal);
                    dataValues.push(parseFloat(forms).toFixed(2));
                }
            });

            chartTahunIni = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Total Skor per Quartal",
                        data: dataValues,
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderColor: '#6366f1',
                        borderWidth: 2,
                        borderRadius: 8
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
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            padding: 12,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(2);
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function tampilkanChartSemuaTahun(allData) {
            const ctx = document.getElementById("lineChart").getContext("2d");
            if (chartAllYears) chartAllYears.destroy();

            const quartals = [...new Set(Object.values(allData).flatMap(yearData => Object.keys(yearData)))];
            const colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'];

            const datasets = Object.keys(allData).map((year, idx) => {
                const data = quartals.map(q => {
                    const forms = allData[year]?.[q];
                    if (!forms) return null;
                    if (typeof forms === "object") return Object.values(forms).reduce((a, b) => a +
                        parseFloat(b), 0);
                    return parseFloat(forms);
                });
                return {
                    label: `Tahun ${year}`,
                    data: data,
                    borderColor: colors[idx % colors.length],
                    backgroundColor: colors[idx % colors.length] + '20',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: colors[idx % colors.length],
                    pointBorderWidth: 2
                };
            });

            chartAllYears = new Chart(ctx, {
                type: "line",
                data: {
                    labels: quartals,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            padding: 12,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(2);
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection
