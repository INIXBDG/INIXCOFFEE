@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    {{-- Pindahkan CDN SweetAlert2 dan jQuery ke PALING ATAS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .filter-bar .form-label {
            font-weight: 600;
            color: #334155;
            font-size: .85rem;
            margin-bottom: .5rem;
        }

        .filter-bar .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: .6rem 1rem;
            transition: all .2s ease;
        }

        .filter-bar .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
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

        .content-card {
            background: #fff;
            border-radius: 16px;
            border: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
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
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            font-weight: 600;
            color: #475569;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 1rem !important;
        }

        .modern-table tbody td {
            padding: 1rem !important;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            font-size: .9rem;
        }

        .modern-table tbody tr {
            transition: background .15s ease;
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
        }

        .modern-table tbody tr:last-child td {
            border-bottom: 0;
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
            transition: all .2s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-table-action:hover {
            background: rgba(245, 158, 11, .05);
            border-color: #f59e0b;
            color: #f59e0b;
            transform: translateY(-1px);
        }

        .evaluated-list {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
        }

        .evaluated-badge {
            background: rgba(99, 102, 241, .1);
            color: #6366f1;
            padding: .35rem .75rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
        }

        .show-more-link {
            color: #6366f1;
            font-size: .8rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all .2s ease;
        }

        .show-more-link:hover {
            color: #8b5cf6;
            text-decoration: underline;
        }

        .evaluated-modal-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .evaluated-modal-list li {
            padding: .75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: .75rem;
            transition: background .15s ease;
        }

        .evaluated-modal-list li:hover {
            background: #f8fafc;
        }

        .evaluated-modal-list li:last-child {
            border-bottom: 0;
        }

        .evaluated-modal-list .badge-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 700;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 1rem 0;
            font-size: .9rem;
            color: #475569;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: .4rem .8rem;
            transition: all .2s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
            outline: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
            border: 1px solid transparent !important;
            padding: .3rem .7rem !important;
            margin: 0 2px;
            color: #475569 !important;
            background: transparent !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #6366f1 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 10px rgba(99, 102, 241, .25);
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .modern-table th,
            .modern-table td {
                font-size: .8rem;
                padding: .75rem !important;
            }

            .filter-bar .row>div {
                margin-bottom: .75rem;
            }
        }
    </style>

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

    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: transparent; box-shadow: none; border: none;">
                <div class="d-flex justify-content-center">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container content-wrapper mt-4">
        <div class="content-card">
            <div class="card-body">
                <h5 class="fw-bold text-dark mb-4">
                    <i class="fa-solid fa-list-check text-primary me-2"></i>
                    Semua Form Penilaian
                </h5>

                <div class="filter-bar">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="quartal" class="form-label">
                                <i class="fa-solid fa-calendar-day text-primary me-1"></i> Quartal
                            </label>
                            <select class="form-select" name="quartal" id="quartal">
                                <option value="Q1">Q1 (Januari - Maret)</option>
                                <option value="Q2">Q2 (April - Juni)</option>
                                <option value="Q3">Q3 (Juli - September)</option>
                                <option value="Q4">Q4 (Oktober - Desember)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tahun" class="form-label">
                                <i class="fa-solid fa-calendar text-primary me-1"></i> Tahun
                            </label>
                            <select class="form-select" name="tahun" id="tahun">
                                <option value="">Pilih Tahun</option>
                                @for ($i = now()->year; $i >= 2020; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table modern-table align-middle" id="table_penilaian" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Kode Form</th>
                                <th>Evaluated</th>
                                <th>Quartal</th>
                                <th>Tahun</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="body_content">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="spinner-border text-primary mb-3" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <span class="text-muted">Memuat data...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEvaluated" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="title-icon"><i class="fa-solid fa-users"></i></span>
                        Daftar Evaluated
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="evaluatedContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- CDN DataTables di sini, setelah jQuery --}}
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            setDefaultQuartal();
            $("#tahun").val(new Date().getFullYear());
            loadData();

            $("#quartal, #tahun").on("change", function() {
                loadData();
            });
        });

        function setDefaultQuartal() {
            const month = new Date().getMonth() + 1;
            let quartal = "Q1";
            if (month >= 4 && month <= 6) quartal = "Q2";
            else if (month >= 7 && month <= 9) quartal = "Q3";
            else if (month >= 10 && month <= 12) quartal = "Q4";
            $("#quartal").val(quartal);
        }

        function loadData() {
            const quartal = $("#quartal").val();
            const tahun = $("#tahun").val();

            $.ajax({
                url: "{{ route('penilaian.form.get') }}",
                type: 'get',
                data: {
                    quartal: quartal,
                    tahun: tahun
                },
                beforeSend: function() {
                    $('#body_content').html(`
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="text-muted">Memuat data...</span>
                                </div>
                            </td>
                        </tr>
                    `);
                },
                success: function(response) {
                    const data = response.data ?? [];
                    const content = $('#body_content');
                    content.empty();

                    if ($.fn.DataTable.isDataTable('#table_penilaian')) {
                        $('#table_penilaian').DataTable().destroy();
                    }

                    if (data.length === 0) {
                        content.append(`
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                                        <span class="text-muted">Tidak ada Form!</span>
                                    </div>
                                </td>
                            </tr>
                        `);
                    } else {
                        data.forEach(function(item, index) {
                            let evaluatedArr = [];
                            if (Array.isArray(item.evaluated)) {
                                evaluatedArr = item.evaluated.map(e => e.nama);
                            } else if (item.evaluated) {
                                evaluatedArr = [item.evaluated.nama];
                            }

                            let badgesHtml = '';
                            const maxShow = 3;
                            const showList = evaluatedArr.slice(0, maxShow);

                            showList.forEach(nama => {
                                badgesHtml += `<span class="evaluated-badge">${nama}</span>`;
                            });

                            let moreLink = '';
                            if (evaluatedArr.length > maxShow) {
                                moreLink =
                                    `<a href="javascript:void(0)" class="show-more-link show-more" data-full='${JSON.stringify(evaluatedArr)}'>+${evaluatedArr.length - maxShow} lainnya</a>`;
                            }

                            content.append(`
                                <tr>
                                    <td class="fw-semibold text-muted">${index + 1}</td>
                                    <td><code class="bg-light px-2 py-1 rounded">${item.label_kode_form}</code></td>
                                    <td>
                                        <div class="evaluated-list">
                                            ${badgesHtml}
                                            ${moreLink}
                                        </div>
                                    </td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary">${item.quartal}</span></td>
                                    <td>${item.tahun}</td>
                                    <td class="text-center">
                                        <a class="btn-table-action" href="/penilaian/data-form/edit/${item.kode_form}" title="Edit Form">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        });

                        $('#table_penilaian').DataTable({
                            pageLength: 10,
                            lengthMenu: [5, 10, 25, 50, 100],
                            responsive: true,
                            scrollX: true,
                            scrollCollapse: true,
                            dom: "<'row mb-2'<'col-md-6 custom-dt-length'l><'col-md-6 text-end custom-dt-search'f>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row mt-2'<'col-md-5 custom-dt-info'i><'col-md-7 custom-dt-pagination'p>>",
                            language: {
                                search: "",
                                searchPlaceholder: "Cari data...",
                                lengthMenu: "_MENU_ per halaman",
                                info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
                                infoEmpty: "Tidak ada data",
                                zeroRecords: "Data tidak ditemukan",
                                paginate: {
                                    first: "Awal",
                                    last: "Akhir",
                                    next: "›",
                                    previous: "‹"
                                }
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading data:', error);
                    $('#body_content').html(`
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fa-solid fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                                    <span class="text-danger">Gagal memuat data</span>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        $(document).on("click", ".show-more", function() {
            const full = JSON.parse($(this).attr("data-full"));
            let listHtml = '<ul class="evaluated-modal-list">';
            full.forEach((nama, index) => {
                listHtml += `
                    <li>
                        <span class="badge-number">${index + 1}</span>
                        <span class="fw-semibold text-dark">${nama}</span>
                    </li>
                `;
            });
            listHtml += "</ul>";
            $("#evaluatedContent").html(listHtml);
            $("#modalEvaluated").modal("show");
        });
    </script>
@endsection
