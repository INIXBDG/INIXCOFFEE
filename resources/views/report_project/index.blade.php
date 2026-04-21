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
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Pendapatan (Keseluruhan)</h5>
                    <h3 class="card-text fw-bold" id="lbl_total_revenue">Rp 0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Pendapatan Terealisasi (Proyek Selesai)</h5>
                    <h3 class="card-text fw-bold" id="lbl_realized_revenue">Rp 0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark bg-warning mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Potensi Pendapatan (Proyek Berjalan)</h5>
                    <h3 class="card-text fw-bold" id="lbl_pipeline_revenue">Rp 0</h3>
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
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<script>
    $(document).ready(function(){
        // Format Mata Uang Rupiah
        const formatRupiah = (angka) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(angka);
        };

        // Inisialisasi DataTables
        $('#salesRecapTable').DataTable({
            "ajax": {
                "url": "{{ route('reports.sales.data') }}",
                "type": "GET",
                "dataSrc": function (json) {
                    // Pembaruan elemen DOM untuk ringkasan kartu
                    if(json.summary) {
                        $('#lbl_total_revenue').text(formatRupiah(json.summary.total_revenue));
                        $('#lbl_realized_revenue').text(formatRupiah(json.summary.realized_revenue));
                        $('#lbl_pipeline_revenue').text(formatRupiah(json.summary.pipeline_revenue));
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