@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/penilaian360.css') }}">
    <style>
        .content-card, .card-body {
            overflow: visible !important;
        }

        .scrollable-table-wrapper {
            overflow-x: auto !important;
            overflow-y: auto !important;
            display: block !important;
            width: 100% !important;
            -webkit-overflow-scrolling: touch !important;
        }

        table#table-fixed.modern-table {
            width: 1000px !important;     
            min-width: 1000px !important;
            table-layout: auto !important;
            border-collapse: separate !important;
        }

        table#table-fixed.modern-table th,
        table#table-fixed.modern-table td {
            white-space: nowrap !important;
            word-break: keep-all !important;
            overflow: visible !important;   
            text-overflow: clip !important;    
        }

        table#table-fixed.modern-table th:nth-child(2),
        table#table-fixed.modern-table td:nth-child(2) {
            white-space: normal !important;
            word-break: break-word !important;
            min-width: 250px !important;
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

        <a href="{{ route('ketegoriKPI.get') }}" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Penilaian
        </a>

        @if ($kodeForm)
            <input type="hidden" id="kodeForm" name="kodeForm" value="{{ $kodeForm }}">
        @endif
        @if ($id_karyawan)
            <input type="hidden" id="id_karyawan" name="id_karyawan" value="{{ $id_karyawan }}">
        @endif
        <input type="hidden" name="jenis_form" id="jenis_form" value="{{ $tipe }}">

        <div class="row">
            <div id="shareEmail" class="mb-3"></div>

            <div class="col-lg-4 mb-4">

                <div class="content-card">
                    <div class="skeleton-overlay" id="skeletonInfo">
                        <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>
                        <div class="skeleton mb-4" style="width: 100%; height: 120px;"></div>
                        <div class="skeleton mb-3" style="width: 30%; height: 16px;"></div>
                        <div class="skeleton mb-4" style="width: 100%; height: 100px;"></div>
                        <div class="skeleton" style="width: 100%; height: 42px; border-radius: 10px;"></div>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa-solid fa-user-tie"></i>
                            Informasi Penilaian
                        </h5>

                        <div class="info-panel" id="content_utama"></div>
                    </div>
                </div>
                
                <div class="content-card chart-card">
                    <div class="skeleton-overlay" id="skeletonChart1">
                        <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>

                        <div class="skeleton"
                            style="width: 100%; height: 250px; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa-solid fa-chart-column"></i>
                            Skor per Tahun
                        </h5>

                        <div class="chart-wrapper">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="content-card chart-card">
                    <div class="skeleton-overlay" id="skeletonChart2">
                        <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>

                        <div class="skeleton"
                            style="width: 100%; height: 250px; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa-solid fa-chart-line"></i>
                            Trend Skor Tahunan
                        </h5>

                        <div class="chart-wrapper">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="content-card mb-4">
                    <div class="skeleton-overlay" id="skeletonTable">
                        <div class="skeleton mb-3" style="width: 30%; height: 20px;"></div>
                        <div class="d-flex gap-2 mb-3">
                            <div class="skeleton" style="width: 80px; height: 36px; border-radius: 8px;"></div>
                            <div class="skeleton" style="width: 80px; height: 36px; border-radius: 8px;"></div>
                            <div class="skeleton" style="width: 80px; height: 36px; border-radius: 8px;"></div>
                        </div>
                        
                        <!-- SAMA PERSIS seperti tabel asli -->
                        <div class="scrollable-table-wrapper" style="overflow-x: auto !important; overflow-y: auto !important; max-width: 100% !important; max-height: 400px; border: 1px solid #e2e8f0; border-radius: 12px;">
                            <div style="min-width: 1000px; width: max-content;">
                                <table class="table modern-table mb-0" style="width: 100% !important;">
                                    <thead>
                                        <tr>
                                            <th><div class="skeleton" style="width: 60%; height: 14px;"></div></th>
                                            <th><div class="skeleton" style="width: 80%; height: 14px;"></div></th>
                                            <th><div class="skeleton" style="width: 40%; height: 14px;"></div></th>
                                            <th><div class="skeleton" style="width: 40%; height: 14px;"></div></th>
                                            <th><div class="skeleton" style="width: 50%; height: 14px;"></div></th>
                                            <th><div class="skeleton" style="width: 40%; height: 14px;"></div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($i = 0; $i < 6; $i++)
                                            <tr class="skeleton-row-table">
                                                <td><div class="skeleton" style="width: 70%; height: 14px;"></div></td>
                                                <td><div class="skeleton" style="width: 90%; height: 14px;"></div></td>
                                                <td><div class="skeleton" style="width: 50%; height: 14px;"></div></td>
                                                <td><div class="skeleton" style="width: 50%; height: 14px;"></div></td>
                                                <td><div class="skeleton" style="width: 60%; height: 14px;"></div></td>
                                                <td><div class="skeleton" style="width: 60%; height: 14px;"></div></td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa-solid fa-table-list"></i> Detail Penilaian</h5>
                        <div class="modern-tab-nav" id="jenis-penilaian-tab" role="tablist"></div>
                        
                        <div class="scrollable-table-wrapper" style="overflow-x: auto !important; overflow-y: auto !important; max-width: 100% !important; max-height: 550px; border: 1px solid #e2e8f0; border-radius: 12px;">
                            
                            <div style="min-width: 1000px; width: max-content;">
                                <table class="table modern-table" id="table-fixed" style="width: 100% !important;">
                                    <thead>
                                        <tr>
                                            <th>Kriteria</th>
                                            <th>Sub Kriteria</th>
                                            <th>Bobot</th>
                                            <th>Nilai</th>
                                            <th>Rata-Rata</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body_content"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-card mb-4">
                    <div class="skeleton-overlay" id="skeletonAbsensi" style="padding: 1.25rem;">
                        <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>
                        <div class="skeleton" style="width: 100%; height: 60px; border-radius: 8px;"></div>
                    </div>
                    <div class="card-body">
                        <div class="absensi-card">
                            <h5 class="card-title mb-3"><i class="fa-solid fa-calendar-check"
                                    style="color: #d97706;"></i> Data Jumlah Absensi</h5>
                            <table class="absensi-table">
                                <thead>
                                    <tr>
                                        <th><i class="fa-solid fa-clock me-1"></i> Telat</th>
                                        <th><i class="fa-solid fa-bed me-1"></i> Sakit</th>
                                        <th><i class="fa-solid fa-envelope me-1"></i> Izin</th>
                                    </tr>
                                </thead>
                                <tbody id="body_content_absensi"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.PENILAIAN_DETAIL_CONFIG = {
            emailRoute: "{{ route('penilaian.email') }}",
            pdfPreviewRoute: "{{ route('penilaian.download.pdf') }}",
            detailGetRoute: "{{ route('penilaian.detail.get') }}",
            detailChartGetRoute: "{{ route('penilaian.detailChart.get') }}",
            sendCatatanRoute: "{{ route('penilaian.sendCatatan') }}",
            csrfToken: "{{ csrf_token() }}",
            tipeForm: "{{ $tipe }}"
        };
    </script>

    <script src="{{ asset('assets/js/penilaian360/detail.js') }}"></script>
@endsection
