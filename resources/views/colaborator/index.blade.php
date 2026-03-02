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

    <div class="row justify-content-center">
        <div class="col-md-12">
            
            <div class="d-flex justify-content-end mb-3">
                {{-- @can('Create Kolaborasi') --}}
                    <a href="{{ route('colaborator.create') }}" class="btn btn-md click-primary mx-4">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px"> Data Kolaborasi
                    </a>
                {{-- @endcan --}}
            </div>

            <div class="card m-4">
                {{-- <div class="card-header" style="background-color: #C2E2FA; border-bottom: none;">
                    <h3 class="card-title text-center my-1">{{ __('Data Kolaborasi Eksternal') }}</h3>
                </div> --}}
                
                <div class="card-body table-responsive">
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="filter_quarter">Kuartal / Triwulan</label>
                            <select id="filter_quarter" class="form-control">
                                <option value="">Semua Kuartal</option>
                                <option value="1">Kuartal 1 (Jan - Mar)</option>
                                <option value="2">Kuartal 2 (Apr - Jun)</option>
                                <option value="3">Kuartal 3 (Jul - Sep)</option>
                                <option value="4">Kuartal 4 (Okt - Des)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_year">Tahun</label>
                            <select id="filter_year" class="form-control">
                                @php $currentYear = date('Y'); @endphp
                                @for($i = $currentYear; $i >= $currentYear - 5; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button id="btn-filter" class="btn btn-primary w-100">Filter Data</button>
                        </div>
                    </div>

                    <table class="table table-striped" id="colaboratortable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Partner</th>
                                <th scope="col">Judul</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Periode</th>
                                <th scope="col">Status</th>
                                <th scope="col">MoU</th>
                                <th scope="col">Aksi</th>
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
    /* Styling Loader dari Template Anda */
    .loader {
        position: relative;
        text-align: center;
        margin: 15px auto 35px auto;
        z-index: 9999;
        display: block;
        width: 80px;
        height: 80px;
        border: 10px solid rgba(0, 0, 0, .3);
        border-radius: 50%;
        border-top-color: #000;
        animation: spin 1s ease-in-out infinite;
        -webkit-animation: spin 1s ease-in-out infinite;
    }
    @keyframes spin { to { -webkit-transform: rotate(360deg); } }
    @-webkit-keyframes spin { to { -webkit-transform: rotate(360deg); } }
    .modal-content { border-radius: 0px; box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7); }
    .modal-backdrop.show { opacity: 0.75; }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function(){
        // Inisialisasi DataTables
        var table = $('#colaboratortable').DataTable({
            "processing": true,
            "serverSide": false, // Menggunakan manipulasi data lokal dari respon JSON AJAX
            "ajax": {
                "url": "{{ route('colaborator.data') }}",
                "type": "GET",
                "data": function (d) {
                    d.quarter = $('#filter_quarter').val();
                    d.year = $('#filter_year').val();
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
                { "data": "id", render: function (data, type, row, meta) { return meta.row + 1; } },
                { "data": "nama_partner" },
                { "data": "title" },
                { "data": "type" },
                { 
                    "data": null,
                    "render": function(data, type, row) {
                        return row.start_date + ' s/d ' + row.end_date;
                    }
                },
                { "data": "status" },
                { 
                    "data": "document_mou",
                    "render": function(data, type, row) {
                        if(data) {
                            return '<a href="/storage/' + data + '" target="_blank" class="btn btn-sm btn-info">Lihat</a>';
                        }
                        return '-';
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = '<div class="dropdown">';
                        actions += '<button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>';
                        actions += '<div class="dropdown-menu">';
                        
                        // Tombol Edit
                        actions += '<a href="{{ url('/colaborator') }}/' + row.id + '/edit" class="dropdown-item">Edit</a>';
                        
                        // Tombol Delete
                        actions += '@can("Delete Kolaborasi")';
                        actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin?\');" action="{{ url('/colaborator') }}/' + row.id + '" method="POST">';
                        actions += '@csrf @method("DELETE")';
                        actions += '<button type="submit" class="dropdown-item text-danger">Hapus</button>';
                        actions += '</form>';
                        actions += '@endcan';
                        
                        actions += '</div></div>';
                        return actions;
                    }
                }
            ]
        });

        // Trigger Filter Reload
        $('#btn-filter').click(function(){
            table.ajax.reload();
        });
    });
</script>
@endpush
@endsection