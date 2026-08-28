@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
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

        .content-card .card-title-modern {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .content-card .card-title-modern i {
            color: #6366f1;
        }

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

        .modern-tab-group {
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
        }

        .modern-tab-btn:hover {
            border-color: #cbd5e1;
            color: #1e293b;
            transform: translateY(-1px);
        }

        .modern-tab-btn.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(99, 102, 241, .25);
        }

        .evaluator-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 1.5rem;
        }

        .evaluator-pill {
            padding: .5rem 1rem;
            border: 2px solid #e2e8f0;
            background: #fff;
            color: #475569;
            font-weight: 600;
            font-size: .8rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .evaluator-pill:hover {
            border-color: #6366f1;
            color: #6366f1;
            background: rgba(99, 102, 241, .03);
        }

        .evaluator-pill.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 10px rgba(99, 102, 241, .25);
        }

        .evaluator-pill .pill-number {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(99, 102, 241, .1);
            color: #6366f1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 700;
        }

        .evaluator-pill.active .pill-number {
            background: rgba(255, 255, 255, .25);
            color: #fff;
        }

        .kriteria-section {
            margin-bottom: 1.5rem;
        }

        .kriteria-header {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: .5rem;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .kriteria-header::before {
            content: '\f0ae';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #6366f1;
            font-size: .9rem;
        }

        .sub-kriteria-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #6366f1;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: .75rem;
            transition: all .2s ease;
        }

        .sub-kriteria-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
        }

        .sub-kriteria-item .sub-label {
            font-weight: 600;
            color: #334155;
            font-size: .875rem;
            margin-bottom: .5rem;
        }

        .sub-kriteria-item .nilai-display {
            background: linear-gradient(135deg, rgba(99, 102, 241, .05), rgba(139, 92, 246, .05));
            border: 1px solid rgba(99, 102, 241, .15);
            border-radius: 8px;
            padding: .6rem 1rem;
            font-weight: 700;
            color: #6366f1;
            font-size: 1rem;
            margin-bottom: .5rem;
        }

        .sub-kriteria-item .deskripsi-display {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: #475569;
            line-height: 1.5;
        }

        .absensi-card {
            background: linear-gradient(135deg, rgba(245, 158, 11, .05), rgba(245, 158, 11, .02));
            border: 1px solid rgba(245, 158, 11, .15);
            border-radius: 14px;
            padding: 1.25rem;
        }

        .absensi-card .card-title-modern {
            color: #d97706;
        }

        .absensi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1rem;
        }

        .absensi-table thead th {
            background: rgba(245, 158, 11, .1);
            color: #92400e;
            font-weight: 700;
            font-size: .8rem;
            text-transform: uppercase;
            padding: .75rem;
            border-radius: 8px;
            text-align: center;
        }

        .absensi-table tbody td {
            padding: .9rem;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .absensi-table tbody td:first-child {
            color: #ef4444;
        }

        .absensi-table tbody td:nth-child(2) {
            color: #f59e0b;
        }

        .absensi-table tbody td:nth-child(3) {
            color: #10b981;
        }

        .catatan-box {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem;
        }

        .catatan-box .catatan-label {
            font-weight: 700;
            color: #334155;
            font-size: .85rem;
            margin-bottom: .5rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .catatan-box .catatan-label i {
            color: #6366f1;
        }

        .catatan-box .catatan-content {
            font-size: .875rem;
            color: #475569;
            line-height: 1.6;
        }

        .form-header-card {
            background: linear-gradient(135deg, rgba(99, 102, 241, .05), rgba(139, 92, 246, .05));
            border: 1px solid rgba(99, 102, 241, .1);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-header-card h4 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: .25rem;
        }

        .form-header-card .evaluated-name {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            padding: .3rem .9rem;
            border-radius: 50px;
            font-size: .8rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(99, 102, 241, .25);
        }

        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .loading-state i {
            font-size: 2rem;
            margin-bottom: .75rem;
        }

        .loading-state p {
            margin: 0;
            font-size: .9rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: .75rem;
            opacity: .5;
        }

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

        @media (max-width: 992px) {
            .modern-tab-group {
                flex-wrap: nowrap;
                overflow-x: auto;
                scrollbar-width: thin;
            }

            .modern-tab-group::-webkit-scrollbar {
                height: 4px;
            }

            .modern-tab-group::-webkit-scrollbar-thumb {
                background: rgba(99, 102, 241, .3);
                border-radius: 2px;
            }
        }

        @media (max-width: 768px) {
            .content-card .card-body {
                padding: 1.25rem;
            }

            .form-header-card h4 {
                font-size: 1rem;
            }
        }
    </style>

    <div class="container content-wrapper mt-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="content-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3"
                            style="border-bottom: 2px solid #f1f5f9;">
                            <h5 class="card-title-modern mb-0">
                                <i class="fa-solid fa-chart-simple"></i> Penilaian 360 Derajat
                            </h5>
                            <div class="d-flex align-items-center gap-2">
                                <label class="fw-semibold text-secondary mb-0" style="font-size: .9rem;">Periode:</label>
                                <select id="selectPeriode" class="form-select form-select-sm"
                                    style="width: 220px; border-radius: 8px; border: 1px solid #cbd5e1; font-weight: 600; color: #334155; background-color: #f8fafc;">
                                </select>
                            </div>
                        </div>

                        <h5 class="card-title-modern">
                            <i class="fa-solid fa-layer-group"></i> Jenis Penilaian
                        </h5>
                        <div class="modern-tab-group" id="groupButtonJenisPenilaian">
                            <div class="loading-state w-100">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Memuat jenis penilaian...</p>
                            </div>
                        </div>

                        <h5 class="card-title-modern">
                            <i class="fa-solid fa-user-check"></i> Pilih Evaluator
                        </h5>
                        <div class="evaluator-pills" id="groupButtonEvaluator">
                            <div class="empty-state w-100">
                                <p>Pilih jenis penilaian terlebih dahulu</p>
                            </div>
                        </div>

                        <div id="formContainer">
                            <div class="loading-state">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                <p>Memuat form penilaian...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="content-card">
                    <div class="card-body">
                        <div class="absensi-card">
                            <h5 class="card-title-modern">
                                <i class="fa-solid fa-calendar-check" style="color: #d97706;"></i> Data Absensi
                            </h5>
                            <table class="absensi-table">
                                <thead>
                                    <tr>
                                        <th><i class="fa-solid fa-clock me-1"></i> Telat</th>
                                        <th><i class="fa-solid fa-bed me-1"></i> Sakit</th>
                                        <th><i class="fa-solid fa-envelope me-1"></i> Izin</th>
                                    </tr>
                                </thead>
                                <tbody id="content_body_absen">
                                    <tr>
                                        <td colspan="3" style="color: #94a3b8; font-size: .9rem; font-weight: 500;">
                                            Memuat...</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div id="content_footer_absen">
                                <div class="catatan-box">
                                    <div class="catatan-label">
                                        <i class="fa-solid fa-note-sticky"></i> Informasi Periode
                                    </div>
                                    <div class="catatan-content">Memuat data...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        let globalData = [];
        let selectedJenis = null;
        let selectedEvaluator = null;
        let evaluatedName = '';
        let currentQuartal = null;

        $(function() {
            loadData();

            $('#selectPeriode').on('change', function() {
                currentQuartal = $(this).val();
                loadData(currentQuartal);
            });
        });

        function loadData(quartal = null) {
            let url = `/penilaian360/get/{{ $id_karyawan }}`;
            if (quartal) {
                url += `?quartal=${encodeURIComponent(quartal)}`;
            }

            $('#groupButtonJenisPenilaian').html(
                `<div class="loading-state w-100"><i class="fa-solid fa-spinner fa-spin"></i><p>Memuat jenis penilaian...</p></div>`
                );
            $('#groupButtonEvaluator').html(`<div class="empty-state w-100"><p>Memuat evaluator...</p></div>`);
            $('#formContainer').html(
                `<div class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Memuat form penilaian...</p></div>`
                );

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (!response || response.message) {
                        $('#groupButtonJenisPenilaian').html(
                            `<div class="empty-state w-100"><i class="fa-solid fa-inbox"></i><p>${response.message ?? 'Data kosong'}</p></div>`
                            );
                        $('#groupButtonEvaluator').html(`<div class="empty-state w-100"><p>-</p></div>`);
                        $('#formContainer').html(
                            `<div class="empty-state"><i class="fa-solid fa-inbox"></i><p>Data kosong</p></div>`
                            );
                        return;
                    }

                    if (response.listPeriode && $('#selectPeriode option').length === 0) {
                        let options = '';
                        response.listPeriode.forEach(p => {
                            let selected = (p.quartal == response.quartal) ? 'selected' : '';
                            options += `<option value="${p.quartal}" ${selected}>${p.label}</option>`;
                        });
                        $('#selectPeriode').html(options);
                    }

                    renderAbsen(response);
                    globalData = Array.isArray(response.data) ? response.data : [];
                    evaluatedName = response.nama_evaluated?.[0] ?? '-';

                    if (globalData.length > 0) {
                        renderJenisPenilaian(globalData);
                    } else {
                        $('#groupButtonJenisPenilaian').html(
                            `<div class="empty-state w-100"><i class="fa-solid fa-inbox"></i><p>Tidak ada data penilaian</p></div>`
                            );
                        $('#groupButtonEvaluator').html(`<div class="empty-state w-100"><p>-</p></div>`);
                        $('#formContainer').html(
                            `<div class="empty-state w-100"><i class="fa-solid fa-inbox"></i><p>Tidak ada form penilaian</p></div>`
                            );
                    }
                },
                error: function(xhr) {
                    $('#groupButtonJenisPenilaian').html(
                        `<div class="empty-state w-100"><i class="fa-solid fa-triangle-exclamation"></i><p>Gagal memuat data</p></div>`
                        );
                    $('#groupButtonEvaluator').html(`<div class="empty-state w-100"><p>-</p></div>`);
                    $('#formContainer').html(
                        `<div class="empty-state"><i class="fa-solid fa-triangle-exclamation"></i><p>Gagal memuat data</p></div>`
                        );
                    console.error(xhr.responseText);
                }
            });
        }

        function renderAbsen(response) {
            const dataAbsen = response.dataAbsen ?? {};
            $('#content_body_absen').html(`
                <tr>
                    <td>${dataAbsen.telat ?? 0}</td>
                    <td>${dataAbsen.sakit ?? 0}</td>
                    <td>${dataAbsen.izin ?? 0}</td>
                </tr>
            `);

            let catatan = 'Belum ada catatan mengenai Anda.';
            if (Array.isArray(response.catatan)) {
                catatan = response.catatan.join('<br>');
            } else if (response.catatan && response.catatan !== 'null') {
                catatan = response.catatan;
            }

            $('#content_footer_absen').html(`
                <div class="catatan-box mb-3">
                    <div class="catatan-label">
                        <i class="fa-solid fa-calendar-day"></i> Periode Absensi
                    </div>
                    <div class="catatan-content">
                        <strong>${response.quartal ?? '-'}</strong> Tahun <strong>${response.tahun ?? '-'}</strong>
                    </div>
                </div>
                <div class="catatan-box">
                    <div class="catatan-label">
                        <i class="fa-solid fa-note-sticky"></i> Catatan
                    </div>
                    <div class="catatan-content">${catatan}</div>
                </div>
            `);
        }

        function renderJenisPenilaian(data) {
            const container = $('#groupButtonJenisPenilaian');
            container.empty();

            const iconMap = {
                'General Manager': 'fa-crown',
                'Manager/SPV/Team Leader (Atasan Langsung)': 'fa-user-tie',
                'Rekan Kerja (Satu Divisi)': 'fa-users',
                'Pekerja (Beda Divisi)': 'fa-people-arrows',
                'Self Apprisial': 'fa-user-check'
            };

            data.forEach(item => {
                const icon = iconMap[item.jenis_penilaian] || 'fa-file-lines';
                container.append(`
                    <button class="modern-tab-btn jenis-btn" data-jenis="${item.jenis_penilaian}">
                        <i class="fa-solid ${icon} me-1"></i> ${item.jenis_penilaian}
                    </button>
                `);
            });

            $('.jenis-btn').on('click', function() {
                $('.jenis-btn').removeClass('active');
                $(this).addClass('active');
                selectedJenis = $(this).data('jenis');
                renderEvaluators(selectedJenis);
            });

            $('.jenis-btn').first().click();
        }

        function renderEvaluators(jenis) {
            const jenisData = globalData.find(j => j.jenis_penilaian === jenis);
            if (!jenisData) return;

            const container = $('#groupButtonEvaluator');
            container.empty();

            if (!jenisData.evaluator || !Array.isArray(jenisData.evaluator) || jenisData.evaluator.length === 0) {
                container.html('<div class="empty-state w-100"><p>Tidak ada evaluator</p></div>');
                $('#formContainer').html(
                    '<div class="empty-state w-100"><i class="fa-solid fa-inbox"></i><p>Tidak ada form penilaian</p></div>'
                    );
                return;
            }

            jenisData.evaluator.forEach((ev, i) => {
                container.append(`
                    <button class="evaluator-pill evaluator-btn" data-nama="${ev.nama_evaluator}">
                        <span>Evaluator ${i + 1}</span>
                    </button>
                `);
            });

            $('.evaluator-btn').on('click', function() {
                $('.evaluator-btn').removeClass('active');
                $(this).addClass('active');
                selectedEvaluator = $(this).data('nama');
                renderForm(jenis, selectedEvaluator);
            });

            $('.evaluator-btn').first().click();
        }

        function renderForm(jenis, evaluatorName) {
            const jenisData = globalData.find(j => j.jenis_penilaian === jenis);
            const evaluatorData = jenisData?.evaluator.find(e => e.nama_evaluator === evaluatorName);
            if (!evaluatorData) {
                $('#formContainer').html(
                    '<div class="empty-state w-100"><i class="fa-solid fa-inbox"></i><p>Data evaluator tidak ditemukan</p></div>'
                    );
                return;
            }

            let html = `
                <div class="form-header-card">
                    <h4><i class="fa-solid fa-clipboard-check text-primary me-2"></i>Penilaian ${jenis}</h4>
                    <span class="evaluated-name">
                        <i class="fa-solid fa-user me-1"></i> ${evaluatedName}
                    </span>
                </div>
            `;

            if (evaluatorData.kriteria && Array.isArray(evaluatorData.kriteria) && evaluatorData.kriteria.length > 0) {
                evaluatorData.kriteria.forEach(k => {
                    html += `<div class="kriteria-section">`;
                    html += `<div class="kriteria-header">${k.kriteria ?? '-'}</div>`;

                    if (k.subKriteria && Array.isArray(k.subKriteria) && k.subKriteria.length > 0) {
                        k.subKriteria.forEach(sk => {
                            const deskripsi = sk.deskripsi ? sk.deskripsi.toString().trim() : '';
                            html += `
                                <div class="sub-kriteria-item">
                                    <div class="sub-label">${sk.subKriteria ?? '-'}</div>
                                    <div class="nilai-display">
                                        <i class="fa-solid fa-star me-1" style="font-size: .85rem;"></i>
                                        ${sk.nilai ?? '-'}
                                    </div>
                                    ${deskripsi && deskripsi !== 'null' && deskripsi !== ''
                                        ? `<div class="deskripsi-display"><i class="fa-solid fa-comment-dots me-1 text-primary"></i> ${sk.deskripsi}</div>`
                                        : ''}
                                </div>
                            `;
                        });
                    } else {
                        html += `<div class="empty-state"><p>Tidak ada sub kriteria</p></div>`;
                    }

                    html += `</div>`;
                });
            } else {
                html += `<div class="empty-state"><i class="fa-solid fa-inbox"></i><p>Tidak ada kriteria</p></div>`;
            }

            $('#formContainer').html(html);
        }
    </script>
@endsection
