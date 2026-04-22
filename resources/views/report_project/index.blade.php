@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center m-3">
        <div class="col-md-4 d-flex justify-content-center">
            <div class="card-blog w-100 mb-4">
                <div class="date-time-container">
                    <time class="date-time">
                        <span>Keuangan</span>
                        <span class="separator"></span>
                    </time>
                </div>
                <div class="content">
                    <div class="infos">
                        <span class="title">Total Pendapatan</span>
                        <p class="description fw-bold fs-4 text-dark mt-2" id="lbl_total_revenue">Rp 0</p>
                        <small class="text-muted">Keseluruhan</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 d-flex justify-content-center">
            <div class="card-blog w-100 mb-4">
                <div class="date-time-container">
                    <time class="date-time">
                        <span>Keuangan</span>
                        <span class="separator"></span>
                    </time>
                </div>
                <div class="content">
                    <div class="infos">
                        <span class="title">Terealisasi</span>
                        <p class="description fw-bold fs-4 text-success mt-2" id="lbl_realized_revenue">Rp 0</p>
                        <small class="text-muted">Proyek Selesai</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 d-flex justify-content-center">
            <div class="card-blog w-100 mb-4">
                <div class="date-time-container">
                    <time class="date-time">
                        <span>Keuangan</span>
                        <span class="separator"></span>
                    </time>
                </div>
                <div class="content">
                    <div class="infos">
                        <span class="title">Potensi</span>
                        <p class="description fw-bold fs-4 text-warning mt-2" id="lbl_pipeline_revenue">Rp 0</p>
                        <small class="text-muted">Proyek Berjalan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center m-3">
        <div class="col-md-3 d-flex justify-content-center">
            <div class="card-blog w-100 mb-4">
                <div class="date-time-container">
                    <time class="date-time">
                        <span>Penjualan</span>
                        <span class="separator"></span>
                    </time>
                </div>
                <div class="content">
                    <div class="infos">
                        <span class="title">Leads Awal</span>
                        <p class="description fw-bold fs-4 text-info mt-2" id="lbl_leads_awal">0</p>
                        <small class="text-muted">Penawaran s/d Meeting</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 d-flex justify-content-center">
            <div class="card-blog w-100 mb-4">
                <div class="date-time-container">
                    <time class="date-time">
                        <span>Penjualan</span>
                        <span class="separator"></span>
                    </time>
                </div>
                <div class="content">
                    <div class="infos">
                        <span class="title">Prospek Aktif</span>
                        <p class="description fw-bold fs-4 text-dark mt-2" id="lbl_prospek_aktif">0</p>
                        <small class="text-muted">Dokumen s/d Proposal</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 d-flex justify-content-center">
            <div class="card-blog w-100 mb-4">
                <div class="date-time-container">
                    <time class="date-time">
                        <span>Penjualan</span>
                        <span class="separator"></span>
                    </time>
                </div>
                <div class="content">
                    <div class="infos">
                        <span class="title">Closing Won</span>
                        <p class="description fw-bold fs-4 text-success mt-2" id="lbl_closing_won">0</p>
                        <small class="text-muted">Berhasil Terkonversi</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 d-flex justify-content-center">
            <div class="card-blog w-100 mb-4">
                <div class="date-time-container">
                    <time class="date-time">
                        <span>Penjualan</span>
                        <span class="separator"></span>
                    </time>
                </div>
                <div class="content">
                    <div class="infos">
                        <span class="title">Closing Lost</span>
                        <p class="description fw-bold fs-4 text-danger mt-2" id="lbl_closing_lost">0</p>
                        <small class="text-muted">Gagal / Dibatalkan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Rekapitulasi Penjualan Proyek') }}</h3>
                    <table class="table table-striped text-center" id="salesRecapTable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Proyek</th>
                                <th scope="col">Perusahaan Klien</th>
                                <th scope="col">Project Manager</th>
                                <th scope="col">Fase Saat Ini</th>
                                <th scope="col">Nilai Proyek</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .loader { position: relative; text-align: center; margin: 15px auto 35px auto; z-index: 9999; display: block; width: 80px; height: 80px; border: 10px solid rgba(0, 0, 0, .3); border-radius: 50%; border-top-color: #000; animation: spin 1s ease-in-out infinite; -webkit-animation: spin 1s ease-in-out infinite; }
    @keyframes spin { to { -webkit-transform: rotate(360deg); } }
    @-webkit-keyframes spin { to { -webkit-transform: rotate(360deg); } }
    .modal-content { border-radius: 0px; box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7); }
    .modal-backdrop.show { opacity: 0.75; }
    .loader-txt p { font-size: 13px; color: #666; }
    .loader-txt p small { font-size: 11.5px; color: #999; }
    /* CSS Kustom Uiverse.io */
    .card-blog {
        box-sizing: border-box;
        display: flex;
        max-width: 100%; /* Disesuaikan untuk Grid Bootstrap */
        background-color: rgba(255, 255, 255, 1);
        border: 1px solid rgba(17, 24, 39, 0.1); /* Penambahan border agar sejajar secara visual */
        border-radius: 8px; /* Opsi visual (dapat dihapus jika ingin sudut tajam) */
        transition: all .15s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-blog:hover {
        box-shadow: 10px 10px 30px rgba(0, 0, 0, 0.081);
    }

    .date-time-container {
        writing-mode: vertical-lr;
        transform: rotate(180deg);
        padding: 0.5rem;
        background-color: rgba(243, 244, 246, 0.5); /* Penambahan latar visual untuk margin kiri */
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .date-time {
        display: flex;
        align-items: center;
        justify-content: space-between;
        grid-gap: 1rem;
        gap: 1rem;
        font-size: 0.75rem;
        line-height: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        color: rgba(17, 24, 39, 1);
    }

    .separator {
        width: 1px;
        flex: 1 1 0%;
        background-color: rgba(17, 24, 39, 0.1);
    }

    .content {
        display: flex;
        flex: 1 1 0%;
        flex-direction: column;
        justify-content: center; /* Memusatkan isi teks secara vertikal */
    }

    .infos {
        border-left: 1px solid rgba(17, 24, 39, 0.1);
        padding: 1rem;
    }

    .title {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 14px; /* Penyesuaian ukuran teks untuk memuat ruang grid */
        color: rgba(17, 24, 39, 1);
    }

    .description {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 5;
        line-clamp: 5;
        margin-top: 0.5rem;
        line-height: 1.25rem;
    }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<script>
    $(document).ready(function(){
        // Konversi Angka ke Format Mata Uang Rupiah
        const formatRupiah = (angka) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(angka);
        };

        // Inisialisasi DataTables AJAX
        $('#salesRecapTable').DataTable({
            "ajax": {
                "url": "{{ route('reports.sales.data') }}",
                "type": "GET",
                "dataSrc": function (json) {
                    // Penugasan nilai ke elemen DOM Kartu Keuangan
                    if(json.summary) {
                        $('#lbl_total_revenue').text(formatRupiah(json.summary.total_revenue));
                        $('#lbl_realized_revenue').text(formatRupiah(json.summary.realized_revenue));
                        $('#lbl_pipeline_revenue').text(formatRupiah(json.summary.pipeline_revenue));
                        
                        // Penugasan nilai ke elemen DOM Kartu Kuantitas Leads
                        $('#lbl_leads_awal').text(json.summary.leads_awal);
                        $('#lbl_prospek_aktif').text(json.summary.prospek_aktif);
                        $('#lbl_closing_won').text(json.summary.closing_won);
                        $('#lbl_closing_lost').text(json.summary.closing_lost);
                    }
                    return json.data;
                },
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function () {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function () {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                }
            },
            "columns": [
                {
                    "data": null,
                    "searchable": false,
                    "orderable": false,
                    "render": function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {"data": "name"},
                {
                    "data": "client.nama_perusahaan",
                    "render": function(data) { return data ? data : '-'; }
                },
                {
                    "data": "administration.project_manager.nama_lengkap",
                    "render": function(data) { return data ? data : '-'; }
                },
                {
                    "data": "phase",
                    "render": function(data) {
                        let phaseClass = 'bg-secondary';
                        if(data === 'administrasi') phaseClass = 'bg-info text-dark';
                        if(data === 'teknis') phaseClass = 'bg-primary';
                        if(data === 'selesai') phaseClass = 'bg-success';
                        return '<span class="badge ' + phaseClass + '">' + data.toUpperCase() + '</span>';
                    }
                },
                {
                    "data": "nilai_proyek",
                    "render": function(data) { return formatRupiah(data); }
                }
            ]
        });
    });
</script>
@endpush
@endsection