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
            <div class="d-flex justify-content-end">
                @can('Create Souvenir')
                    <a href="{{ route('souvenir.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah User"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Data Souvenir</a>
                @endcan
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Souvenir') }}</h3>
                    <table class="table table-striped" id="souvenirtable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Souvenir</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Range Harga Pelatihan</th>
                                <th scope="col">Stok</th>
                                {{-- <th scope="col">Stok</th> --}}
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

    @keyframes spin {
    to {
        -webkit-transform: rotate(360deg);
    }
    }

    @-webkit-keyframes spin {
    to {
        -webkit-transform: rotate(360deg);
    }
    }
    .modal-content {
    border-radius: 0px;
    box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }

    .modal-backdrop.show {
    opacity: 0.75;
    }

    .loader-txt {
    p {
        font-size: 13px;
        color: #666;
        small {
        font-size: 11.5px;
        color: #999;
        }
    }
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    function formatRupiah(angka, prefix) {
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix === undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    $(document).ready(function() {
        var userRole = '{{ auth()->user()->jabatan}}';
        var tableIndex = 1;
        var tableIndex2 = 1;
        $('#souvenirtable').DataTable({
            "ajax": {
                "url": "{{ route('getSouvenir') }}", // URL API untuk mengambil data
                "type": "GET",
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
                {   "data": null,
                    "render": function (data){
                        return tableIndex++
                    }
                },
                {"data": "nama_souvenir"},
                {
                    "data": "harga",
                    "render": function(data) {
                        return formatRupiah(data, 'Rp. ');
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        var minHarga = formatRupiah(data.min_harga_pelatihan, 'Rp. ');
                        var maxHarga = formatRupiah(data.max_harga_pelatihan, 'Rp. ');
                        return minHarga + ' - ' + maxHarga;
                    }
                },
                {"data": "stok"},
                
                {
                    "data": null,
                    "render": function(data, type, row) {
                        if (userRole === 'Direktur' || userRole === 'Direktur Utama') {
                            return "";
                        } else {
                            var actions = "";
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '@can('Edit Souvenir')';
                            actions += '<a class="dropdown-item" href="{{ url('/souvenir') }}/' + row.id + '/edit" data-toggle="tooltip" data-placement="top" title="Edit Souvenir"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                            actions += '<a class="dropdown-item" href="{{ url('/souvenir') }}/' + row.id + '/editstok" data-toggle="tooltip" data-placement="top" title="Update Stok Souvenir"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Update Stok</a>';
                            actions += '@endcan';
                            actions += '<a class="dropdown-item" href="{{ url('/souvenir') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                            actions += '</div>';
                            actions += '</div>';
                            return actions;
                        }
                    }
                }
            ]
        });
    });
</script>

@endpush
@endsection
