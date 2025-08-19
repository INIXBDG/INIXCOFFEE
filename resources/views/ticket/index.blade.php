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
                    <a href="{{ route('tickets.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah User"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Buat Target</a>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Target') }}</h3>
                    <table class="table table-striped" id="jabatantable">
                        <thead>
                            <tr>
                                {{-- <th scope="col">No</th> --}}
                                <th scope="col">Timestamp</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Keperluan</th>
                                <th scope="col">Detail Kendala</th>
                                {{-- <th scope="col">Tanggal Response</th>
                                <th scope="col">PIC</th>
                                <th scope="col">Penanganan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Tanggal Selesai</th>
                                <th scope="col">Keterangan</th> 
                                <th scope="col">Tingkat Kesulitan</th> --}}
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
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>

<script>
    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan}}';

        $('#jabatantable').DataTable({
            "ajax": {
                "url": "{{ route('getTickets') }}", // URL API untuk mengambil data
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
                // {"data": "id"},
                {
                    "data": "timestamp",
                    "render": function(data){
                        moment.locale('id');
                        return moment(data).format('DD MMMM YYYY H:mm:ss');
                    }
                },
                {"data": "nama_karyawan"},
                {"data": "divisi"},
                {"data": "kategori"},
                {"data": "keperluan"},
                {"data": "detail_kendala"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var pic = "{{ auth()->user()->username }}";
                        switch(pic.toLowerCase()) {
                            case 'ardhan':
                                pic = 'Ardhan';
                                break;
                            case 'naufal':
                                pic = 'Naufal';
                                break;
                            case 'ferdi':
                                pic = 'Ferdi';
                                break;
                            case 'donna':
                                pic = 'Donna';
                                break;
                            case 'juliet':
                                pic = 'Juli';
                                break;
                            case 'stepanusberkatsinaga':
                                pic = 'Stefan';
                                break;
                            case 'sergiomosesriyanto':
                                pic = 'Sergio';
                                break;
                            case 'vickyryandysaputra':
                                pic = 'Vicky';
                                break;
                            // Tambahkan case lain jika diperlukan
                            default:
                                pic = '';
                                break;
                        }
                        var actions = "";
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            
                            console.log(data);
                            if(data.status == 'Di Proses'){
                                actions += '<form onsubmit="return confirm(\'Anda akan menerima tiket ini ?\');" action="{{ url('/tickets') }}/' + row.id + '/block" method="POST">';
                                actions += '@csrf';
                                actions += '<input type="hidden" name="pic" value="'+pic+'">';
                                actions += '<input type="hidden" name="row" value="'+data.row+'">';
                                actions += '@method('POST')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/x-circle.svg') }}" class=""> Tolak</button>';
                                actions += '</form>';
                                actions += '<form onsubmit="return confirm(\'Anda akan menerima tiket ini ?\');" action="{{ url('/tickets') }}/' + row.id + '/finish" method="POST">';
                                actions += '@csrf';
                                actions += '<input type="hidden" name="pic" value="'+pic+'">';
                                actions += '<input type="hidden" name="row" value="'+data.row+'">';
                                
                                actions += '@method('POST')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/thumbs-up.svg') }}" class=""> Selesai</button>';
                                actions += '</form>';
                            }else{
                                actions += '<form onsubmit="return confirm(\'Anda akan menerima tiket ini ?\');" action="{{ url('/tickets') }}/' + row.id + '/accept" method="POST">';
                                actions += '@csrf';
                                actions += '<input type="hidden" name="pic" value="'+pic+'">';
                                actions += '<input type="hidden" name="row" value="'+data.row+'">';
                                actions += '@method('POST')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Terima</button>';
                                actions += '</form>';
                            }
                            
                            // actions += '<a class="dropdown-item" href="{{ url('/tickets') }}/' + row.id + '/accept" data-toggle="tooltip" data-placement="top" title="Update Tiket"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Terima Tiket</a>';
                            // actions += '<a class="dropdown-item" href="{{ url('/materi') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                            // actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/tickets') }}/' + row.id + '" method="POST">';
                            // actions += '@csrf';
                            // actions += '@method('DELETE')';
                            // actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            // actions += '</form>';
                            actions += '</div>';
                            actions += '</div>';
                            return actions;

                    }
                    
                }
            ]
        });
    });
    function formatRupiah(angka, prefix) {
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }
</script>
@endpush
@endsection




{{-- <!DOCTYPE html>
<html>
<head>
    <title>Daftar Tiket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Daftar Tiket</h1>
        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif
        <table class="w-full bg-white shadow-md rounded">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2">ID</th>
                    <th class="p-2">Nomor Pengirim</th>
                    <th class="p-2">Deskripsi</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td class="p-2">{{ $ticket->id }}</td>
                        <td class="p-2">{{ $ticket->no_user }}</td>
                        <td class="p-2">{{ $ticket->deskripsi }}</td>
                        <td class="p-2">{{ $ticket->status }}</td>
                        <td class="p-2">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-500">Detail</a>
                            @if ($ticket->status == 'Menunggu')
                                <form action="{{ route('tickets.accept', $ticket) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-500">Terima</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html> --}}