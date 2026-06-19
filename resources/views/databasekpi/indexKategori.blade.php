@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .page-header-modern {
            margin-bottom: 2rem;
            padding: 1.5rem 0;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .page-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .breadcrumb-modern {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 0.875rem;
        }

        .breadcrumb-modern .breadcrumb-item {
            color: #64748b;
        }

        .breadcrumb-modern .breadcrumb-item.active {
            color: #6366f1;
            font-weight: 600;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 0;
            transition: all 0.2s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-action.primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-action.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            color: #fff;
        }

        .filter-card {
            border-radius: 16px;
            padding: 1.5rem;
        }

        .filter-card .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .filter-card .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.7rem 1rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .filter-card .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .content-card {
            background: #fff;
            border-radius: 16px;
            border: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .content-card .card-body {
            padding: 1.5rem;
        }

        .modern-table {
            border: 0 !important;
            border-radius: 12px !important;
            overflow: hidden;
            width: 100% !important;
        }

        .modern-table thead th {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
            border-bottom: 2px solid #e2e8f0 !important;
            font-weight: 700;
            color: #475569;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem !important;
            white-space: nowrap;
        }

        .modern-table tbody td {
            padding: 1rem !important;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .modern-table tbody tr {
            transition: all 0.15s ease;
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.005);
        }

        .modern-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .badge-modern {
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge-jangka {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .badge-semester {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .badge-kode {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            font-family: monospace;
        }

        .btn-table-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-table-action:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .btn-table-action.edit:hover {
            border-color: #6366f1;
            color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }

        .btn-table-action.delete:hover {
            border-color: #ef4444;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }

        .btn-table-action.share:hover {
            border-color: #0ea5e9;
            color: #0ea5e9;
            background: rgba(14, 165, 233, 0.05);
        }

        .btn-table-action.clean:hover {
            border-color: #f59e0b;
            color: #f59e0b;
            background: rgba(245, 158, 11, 0.05);
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

        .dataTables_wrapper {
            overflow: visible !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 1rem 0;
            font-size: 0.9rem;
            color: #475569;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.5rem 0.8rem;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            border: 1px solid transparent !important;
            padding: 0.4rem 0.8rem !important;
            margin: 0 3px;
            color: #475569 !important;
            background: transparent !important;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #6366f1 !important;
            transform: translateY(-1px);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .evaluator-card {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            border: 1px solid #e2e8f0;
            background: #fff;
            transition: all 0.2s ease;
        }

        .evaluator-card:hover {
            background: #f8fafc;
            transform: translateX(4px);
        }

        .evaluator-card.success {
            background: rgba(16, 185, 129, 0.05);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .evaluator-card.danger {
            background: rgba(239, 68, 68, 0.05);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .select2-container--default .select2-selection--multiple {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px;
            min-height: 42px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: none !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #6366f1;
            border: none;
            border-radius: 6px;
            color: #fff;
            padding: 3px 10px;
            margin-top: 4px;
            font-size: 0.85rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-right: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .select2-dropdown {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #6366f1;
            color: white;
        }

        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .modern-table {
            border: 0 !important;
            border-radius: 12px !important;
            overflow: hidden;
            width: 100% !important;
            min-width: 1200px !important;
            /* Paksa lebar minimum agar bisa scroll */
        }

        .dataTables_wrapper {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .modern-table {
                font-size: 0.85rem;
            }

            .modern-table thead th,
            .modern-table tbody td {
                padding: 0.75rem !important;
            }
        }
    </style>

    <div class="container content-wrapper mt-4">
        <div class="content-card">
            <div class="filter-card">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fa-solid fa-building text-primary me-2"></i>Pilih Divisi
                        </label>
                        <select name="divisiSelectUtama" id="divisiSelectUtama" class="form-select">
                            <option value="" selected>Semua Divisi</option>
                            @foreach ($divisi as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fa-solid fa-calendar-alt text-primary me-2"></i>Pilih Semester
                        </label>
                        <select name="quartalSelectUtama" id="quartalSelectUtama" class="form-select">
                            <option value="S1">Semester 1 (Jan - Jun)</option>
                            <option value="S2">Semester 2 (Jul - Des)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fa-solid fa-calendar text-primary me-2"></i>Pilih Tahun
                        </label>
                        <select name="tahunSelectUtama" id="tahunSelectUtama" class="form-select"></select>
                    </div>
                    <input type="hidden" name="jenis_form" id="jenis_form" value="{{ $tipe }}">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_karyawan" class="table modern-table align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Evaluator</th>
                                <th>Yang Dinilai</th>
                                <th>Divisi</th>
                                <th>Tanggal</th>
                                <th>Kode Form</th>
                                <th>Semester</th>
                                <th>Tahun</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shareEvaluatorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('penilaian.shareForm') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="title-icon"
                                style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; margin-right: 0.5rem;">
                                <i class="fa-solid fa-share-nodes"></i>
                            </span>
                            Bagikan Formulir Penilaian
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modal-body-content"></div>
                        <div id="content_select_input"></div>
                        <input type="hidden" name="jenis_form" value="{{ $tipe }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-action primary">
                            <i class="fa-solid fa-paper-plane"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="evaluatorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="title-icon"
                            style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; margin-right: 0.5rem;">
                            <i class="fa-solid fa-users"></i>
                        </span>
                        Daftar Evaluator
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="evaluatorModalContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const month = new Date().getMonth() + 1;
            let selectedQuarter = month <= 6 ? 'S1' : 'S2';
            $('#quartalSelectUtama').val(selectedQuarter);

            const tahunSelect = document.getElementById('tahunSelectUtama');
            const tahunSekarang = new Date().getFullYear();
            for (let tahun = 2020; tahun <= tahunSekarang; tahun++) {
                const option = document.createElement('option');
                option.value = tahun;
                option.text = tahun;
                if (tahun === tahunSekarang) option.selected = true;
                tahunSelect.appendChild(option);
            }

            $('#quartalSelectUtama, #tahunSelectUtama, #divisiSelectUtama, #jenis_form').on('change', function() {
                loadData();
            });

            loadData();
        });

        function loadData() {
            const selectedQuartal = $('#quartalSelectUtama').val();
            const selectedTahun = $('#tahunSelectUtama').val();
            const selectedDivisi = $('#divisiSelectUtama').val();
            const jenis_form = $('#jenis_form').val();

            $.ajax({
                url: "{{ route('penilaian.get.data') }}",
                type: 'get',
                data: {
                    quartal: selectedQuartal,
                    tahun: selectedTahun,
                    divisi: selectedDivisi,
                    jenis_form: jenis_form
                },
                beforeSend: function() {
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    let data = response.data;

                    if ($.fn.DataTable.isDataTable('#table_karyawan')) {
                        $('#table_karyawan').DataTable().destroy();
                    }

                    $('#table_karyawan').DataTable({
                        data: data.map((item, index) => {
                            let jenis = '';
                            if (item.jenis_penilaian === 'General Manager') jenis = 'J01P';
                            else if (item.jenis_penilaian ===
                                'Manager/SPV/Team Leader (Atasan Langsung)') jenis = 'J02P';
                            else if (item.jenis_penilaian === 'Rekan Kerja (Satu Divisi)')
                                jenis = 'J03P';
                            else if (item.jenis_penilaian === 'Pekerja (Beda Divisi)') jenis =
                                'J04P';
                            else if (item.jenis_penilaian === 'Self Apprisial') jenis = 'J05P';
                            else jenis = 'not_found';

                            let button_evaluatorShow = (item.evaluator_by_jenis && Object.keys(
                                    item.evaluator_by_jenis).length > 0) ?
                                `<button type="button" class="btn-table-action edit btn-show-evaluator" 
                                data-evaluator='${encodeURIComponent(JSON.stringify(item.evaluator_by_jenis))}'
                                data-kode-form="${item.kode_form}" title="Lihat Evaluator">
                                <i class="fa-solid fa-users"></i>
                            </button>` :
                                `<span class="text-muted">-</span>`;

                            return [
                                index + 1,
                                button_evaluatorShow,
                                `<strong>${item.evaluated}</strong>`,
                                `<span class="badge badge-modern badge-jangka">${item.evaluatedDivisi || '-'}</span>`,
                                item.tanggal,
                                `<span class="badge badge-modern badge-kode">${item.kode_form_label}</span>`,
                                `<span class="badge badge-modern badge-semester">${item.quartal}</span>`,
                                item.tahun,
                                `<div class="d-flex gap-2">
                                    <button type="button" class="btn-table-action share" onclick="shareForm(this)" data-kode="${item.kode_form}" data-id="${item.id_karyawan}" data-bs-toggle="modal" data-bs-target="#shareEvaluatorModal" title="Share">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                    <a href="/penilaian/detail/data-penilaian/${item.kode_form}/${item.id_karyawan}/{{ $tipe }}" class="btn-table-action edit" title="Detail">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </a>
                                    <button type="button" class="btn-table-action clean btn-clean" data-kode_form="${item.kode_form}" data-id_karyawan="${item.id_karyawan}" data-jenis_penilaian="${jenis}" data-quartal="${item.quartal}" data-tahun="${item.tahun}" data-jenis_form="{{ $tipe }}" title="Bersihkan">
                                        <i class="fa-solid fa-brush"></i>
                                    </button>
                                    <button type="button" class="btn-table-action delete btn-hapus" data-kode_form="${item.kode_form}" data-id_karyawan="${item.id_karyawan}" data-jenis_penilaian="${item.jenis_penilaian}" data-quartal="${item.quartal}" data-tahun="${item.tahun}" data-jenis_form="{{ $tipe }}" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>`
                            ];
                        }),
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50, 100],
                        responsive: true,
                        ordering: false,
                        dom: "<'row mb-2'<'col-md-6 custom-dt-length'l><'col-md-6 text-end custom-dt-search'f>>" +
                            "<'row'<'col-sm-12'tr>>" +
                            "<'row mt-2'<'col-md-5 custom-dt-info'i><'col-md-7 custom-dt-pagination'p>>",
                        language: {
                            search: "",
                            searchPlaceholder: "Cari data...",
                            lengthMenu: "_MENU_ per halaman",
                            info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
                            infoEmpty: "Data Tidak Ditemukan",
                            paginate: {
                                first: "Awal",
                                last: "Akhir",
                                previous: "<",
                                next: ">"
                            },
                        },
                    });
                },
                complete: function() {
                    $('#loadingModal').modal('hide');
                }
            });
        }

        $(document).on('click', '.btn-hapus', function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('kode_form', $(this).data('kode_form'));
            formData.append('id_karyawan', $(this).data('id_karyawan'));
            formData.append('jenis_penilaian', $(this).data('jenis_penilaian'));
            formData.append('quartal', $(this).data('quartal'));
            formData.append('tahun', $(this).data('tahun'));
            formData.append('jenis_form', $(this).data('jenis_form'));

            Swal.fire({
                title: 'Yakin ingin menghapus data?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/penilaian/hapus`,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Data berhasil dihapus.',
                                confirmButtonColor: '#6366f1'
                            });
                            loadData();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.btn-show-evaluator', function() {
            const rawEvaluator = $(this).attr('data-evaluator');
            const kodeFormGlobal = $(this).attr('data-kode-form');
            const evaluatorByJenis = JSON.parse(decodeURIComponent(rawEvaluator));

            const jenisPenilaianToKode = {
                'General Manager': 'JP01',
                'Manager/SPV/Team Leader (Atasan Langsung)': 'JP02',
                'Rekan Kerja (Satu Divisi)': 'JP03',
                'Pekerja (Beda Divisi)': 'JP04',
                'Self Appraisal': 'JP05',
                'Self Apprisial': 'JP05'
            };

            let html = '';
            Object.keys(evaluatorByJenis).forEach(function(jenis) {
                const kodeJenis = jenisPenilaianToKode[jenis] || jenis;
                html += `
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-users me-2 text-primary"></i> ${jenis}</h6>
                    <div class="row">`;

                evaluatorByJenis[jenis].forEach(function(ev, index) {
                    const cardClass = ev.is_red ? 'danger' : 'success';
                    html += `
                    <div class="col-12 mb-2">
                        <div class="evaluator-card ${cardClass}">
                            <div>
                                <span class="badge badge-modern badge-jangka me-2">${index + 1}</span> 
                                <strong>${ev.name}</strong>
                            </div>
                            <button class="btn-table-action delete btn-action-hapus-evaluator"
                                data-jenis-penilaian="${kodeJenis}"
                                data-id-evaluator="${ev.id}"
                                data-kode-form="${kodeFormGlobal}" title="Hapus Evaluator">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>`;
                });
                html += `</div></div>`;
            });

            $('#evaluatorModalContent').html(html);
            $('#evaluatorModal').modal('show');
        });

        $(document).on('click', '.btn-action-hapus-evaluator', function() {
            const $button = $(this);
            const jenisPenilaian = $button.data('jenis-penilaian');
            const idEvaluator = $button.data('id-evaluator');
            const kodeForm = $button.data('kode-form');

            Swal.fire({
                title: 'Yakin hapus evaluator ini?',
                text: "Tindakan ini tidak bisa dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/penilaian/hapus-evaluator/${jenisPenilaian}/${idEvaluator}/${kodeForm}`,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Terhapus!',
                                text: response.message || 'Evaluator berhasil dihapus.',
                                icon: 'success',
                                confirmButtonColor: '#6366f1'
                            });
                            loadData();
                            $('#evaluatorModal').modal('hide');
                            if (window.activeEvaluatorButton) window.activeEvaluatorButton
                                .click();
                        },
                        error: function(xhr) {
                            let msg = 'Gagal menghapus evaluator.';
                            if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                            Swal.fire({
                                title: 'Gagal!',
                                text: msg,
                                icon: 'error',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.btn-clean', function(e) {
            e.preventDefault();
            let formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('kode_form', $(this).data('kode_form'));
            formData.append('id_karyawan', $(this).data('id_karyawan'));
            formData.append('jenis_penilaian', $(this).data('jenis_penilaian'));
            formData.append('quartal', $(this).data('quartal'));
            formData.append('tahun', $(this).data('tahun'));

            Swal.fire({
                title: 'Yakin ingin membersihkan data?',
                text: "Data yang dibersihkan tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, bersihkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/penilaian/clean`,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Data berhasil dibersihkan.',
                                confirmButtonColor: '#6366f1'
                            });
                            loadData();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    });
                }
            });
        });

        let allKaryawan = [];

        function shareForm(el) {
            const kodeForm = el.dataset.kode;
            const idKaryawan = el.dataset.id;
            const modalBody = $('#modal-body-content');
            const contentSelect = $('#content_select_input');

            modalBody.html(`
            <input type="hidden" value="${kodeForm}" name="kode_form">
            <input type="hidden" value="${idKaryawan}" name="id_evaluated">
        `);
            contentSelect.empty();

            $.ajax({
                url: "{{ route('penilaian.get.data') }}",
                type: 'GET',
                success: function(response) {
                    const karyawan = response.karyawan;
                    const divisiSet = new Set(karyawan.map(item => item.divisi).filter(Boolean));
                    const gmList = karyawan.filter(item => item.jabatan === 'GM' && item.divisi ===
                        'Office');

                    const html = `
                    <div class="mb-3">
                        <label class="form-label">Jenis Penilaian</label>
                        <select name="jenis_penilaian" class="form-select" required>
                            <option disabled selected>Pilih Jenis Penilaian</option>
                            <option value="General Manager">General Manager</option>
                            <option value="Manager/SPV/Team Leader (Atasan Langsung)">Manager/SPV/Team Leader (Atasan Langsung)</option>
                            <option value="Rekan Kerja (Satu Divisi)">Rekan Kerja (Satu Divisi)</option>
                            <option value="Pekerja (Beda Divisi)">Pekerja (Beda Divisi)</option>
                            <option value="Self Apprisial">Self Apprisial</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Divisi</label>
                        <select id="multiple-select-field-divisi" name="divisi[]" multiple class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Evaluator</label>
                        <select id="multiple-select-field-karyawan" name="id_karyawan[]" multiple class="form-select"></select>
                    </div>
                `;
                    contentSelect.append(html);

                    const $jenisPenilaianSelect = $('select[name="jenis_penilaian"]');
                    const $divisiSelect = $('#multiple-select-field-divisi');
                    const $evaluatorSelect = $('#multiple-select-field-karyawan');

                    $divisiSelect.select2({
                        dropdownParent: $('#shareEvaluatorModal'),
                        width: '100%',
                        placeholder: 'Pilih Divisi',
                        closeOnSelect: false
                    });
                    $evaluatorSelect.select2({
                        dropdownParent: $('#shareEvaluatorModal'),
                        width: '100%',
                        placeholder: 'Pilih Evaluator',
                        closeOnSelect: false
                    });

                    function renderDivisiSelect(allowGm) {
                        $divisiSelect.empty();
                        const defaultDivisi = 'Office';
                        if (allowGm) {
                            const option = new Option(defaultDivisi, defaultDivisi, true, true);
                            $(option).attr('disabled', true);
                            $divisiSelect.append(option);
                            if (!$('#defaultDivisiInput').length) contentSelect.append(
                                `<input type="hidden" name="divisi[]" value="${defaultDivisi}" id="defaultDivisiInput">`
                            );
                        } else {
                            $('#defaultDivisiInput').remove();
                        }

                        divisiSet.forEach(divisi => {
                            if (!allowGm || divisi !== defaultDivisi) $divisiSelect.append(new Option(
                                divisi, divisi));
                        });
                        $divisiSelect.trigger('change');
                    }

                    function updateEvaluatorOptions(selectedDivisi, allowGm = false) {
                        let filtered = karyawan.filter(item => selectedDivisi.includes(item.divisi));
                        modalBody.find('input[data-gm="true"]').remove();
                        if (allowGm) {
                            gmList.forEach(gm => {
                                if (!filtered.find(k => k.id === gm.id)) filtered.push(gm);
                                modalBody.append(
                                    `<input type="hidden" name="id_karyawan[]" value="${gm.id}" data-gm="true">`
                                );
                            });
                        }
                        let options = '';
                        filtered.forEach(item => {
                            const isGM = gmList.find(gm => gm.id === item.id);
                            const isSelected = isGM && allowGm;
                            const isDisabled = isGM && allowGm;
                            options +=
                                `<option value="${item.id}" ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${item.nama_lengkap} - ${item.divisi}</option>`;
                        });
                        $evaluatorSelect.html(options).trigger('change');
                    }

                    $jenisPenilaianSelect.on('change', function() {
                        const jenis = $(this).val();
                        const allowGm = jenis === 'General Manager';
                        renderDivisiSelect(allowGm);
                        const selectedDivisi = $divisiSelect.val() || [];
                        const finalDivisi = allowGm ? [...new Set([...selectedDivisi,
                            'Office'
                        ])] : selectedDivisi;
                        updateEvaluatorOptions(finalDivisi, allowGm);
                    });

                    $divisiSelect.on('change', function() {
                        const selectedDivisi = $(this).val() || [];
                        const jenis = $jenisPenilaianSelect.val();
                        const allowGm = jenis === 'General Manager';
                        const finalDivisi = allowGm ? [...new Set([...selectedDivisi,
                            'Office'
                        ])] : selectedDivisi;
                        updateEvaluatorOptions(finalDivisi, allowGm);
                    });

                    renderDivisiSelect(false);
                    updateEvaluatorOptions([], false);
                }
            });
            $('#shareEvaluatorModal').modal('show');
        }
    </script>
@endsection
