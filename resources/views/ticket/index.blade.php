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
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Kelola Tiket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form Tolak Tiket -->
                    <form id="formBlock" action="" method="POST"> {{-- action dikosongkan dulu, diisi via JS --}}
                        @csrf
                        @method("POST")
                        <input type="hidden" name="pic" id="blockPic" value=""> {{-- Tambahkan id untuk target JS --}}
                        <input type="hidden" name="row" id="blockRow" value=""> {{-- Tambahkan id untuk target JS --}}
                        <div class="mb-3">
                            <label for="keteranganBlock" class="form-label">Keterangan Tolak</label>
                            <select name="kesulitan" id="kesulitanBlock" class="form-select"> Ubah ID agar unik
                                <option value="" selected disabled>Pilih Tingkat Kesulitan</option>
                                <option value="Major">Major</option>
                                <option value="Moderate">Moderate</option>
                                <option value="Minor">Minor</option>
                            </select>
                            <textarea class="form-control" id="keteranganBlock" name="keterangan" rows="3" placeholder="Keterangan"></textarea>
                            <textarea class="form-control" id="penangananFinish" name="penanganan" rows="3" placeholder="Update Penanganan"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger mb-2 w-100">Tolak Tiket</button>
                    </form>

                    <!-- Form Selesaikan Tiket -->
                    <form id="formFinish" action="" method="POST"> {{-- action dikosongkan dulu, diisi via JS --}}
                        @csrf
                        @method("POST") {{-- Pastikan method POST juga ada di sini jika Anda akan menggunakannya --}}
                        <input type="hidden" name="pic" id="finishPic" value=""> {{-- Tambahkan id untuk target JS --}}
                        <input type="hidden" name="row" id="finishRow" value=""> {{-- Tambahkan id untuk target JS --}}
                        <div class="mb-3">
                            <label for="keteranganFinish" class="form-label">Keterangan Selesai</label>
                            {{-- <label for="kesulitanFinish" class="form-label">Tingkat Kesulitan</label> Ubah ID agar unik --}}
                            <select name="kesulitan" id="kesulitanFinish" class="form-select"> {{-- Ubah ID agar unik --}}
                                <option value="" selected disabled>Pilih Tingkat Kesulitan</option>
                                <option value="Major">Major</option>
                                <option value="Moderate">Moderate</option>
                                <option value="Minor">Minor</option>
                            </select>
                            <textarea class="form-control" id="keteranganFinish" name="keterangan" rows="3" placeholder="Keterangan"></textarea>
                            <textarea class="form-control" id="penangananFinish" name="penanganan" rows="3" placeholder="Update Penanganan"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Selesaikan Tiket</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Tiket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                   <div class="row" id="content"></div>
                </div>
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
                                <th scope="col">PIC</th>
                                <th scope="col">Status</th>
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
                    "data": "pic",
                    "render": function(data) {
                        return data ? data : '-';
                    }
                },
               {
                    "data": null,
                    "render": function(data) {
                        if (data.status == 'Di Proses') {
                            return `
                                <span class="badge rounded-pill bg-warning text-dark">
                                    <i class="bi bi-hourglass-split me-1"></i> Di Proses
                                </span>`;
                        } else if (data.status == 'Selesai') {
                            return `
                                <span class="badge rounded-pill bg-success">
                                    <i class="bi bi-check-circle me-1"></i> Selesai
                                </span>`;
                        } else if (data.status == 'Terkendala') {
                            return `
                                <span class="badge rounded-pill bg-danger">
                                    <i class="bi bi-x-circle me-1"></i> Terkendala
                                </span>`;
                        } else{
                            return '-';
                        }
                    }
                },
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
                            
                            // console.log(data);
                            if(data.status == 'Di Proses'){
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(\'' + data.id + '\', \'' + pic + '\', \'' + data.row + '\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Kelola Tiket</button>';
                            }else if(data.status == 'Selesai' || data.status == 'Terkendala'){
                                actions += '<button type="submit" disabled class="dropdown-item"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Terima</button>';
                            }else{
                                actions += '<form onsubmit="return confirm(\'Anda akan menerima tiket ini ?\');" action="{{ url('/tickets') }}/' + row.id + '/accept" method="POST">';
                                actions += '@csrf';
                                actions += '<input type="hidden" name="pic" value="'+pic+'">';
                                actions += '<input type="hidden" name="row" value="'+data.row+'">';
                                actions += '@method('POST')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Terima</button>';
                                actions += '</form>';
                            }
                            actions += '<button type="button" class="dropdown-item" onclick=\'openDetailModal(' + JSON.stringify(data) + ')\'><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</button>';
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
    function openApproveModal(ticketId, picValue, rowValue) {
        // Set action URL untuk form "Tolak Tiket"
        var blockUrl = "{{ url('/tickets') }}/" + ticketId + "/block";
        $('#formBlock').attr('action', blockUrl);

        // Set value untuk input hidden di form "Tolak Tiket"
        $('#blockPic').val(picValue);
        $('#blockRow').val(rowValue);

        // Set action URL untuk form "Selesaikan Tiket"
        var finishUrl = "{{ url('/tickets') }}/" + ticketId + "/finish";
        $('#formFinish').attr('action', finishUrl);

        // Set value untuk input hidden di form "Selesaikan Tiket"
        $('#finishPic').val(picValue);
        $('#finishRow').val(rowValue);


        // Tampilkan modal
        $('#approveModal').modal('show');
    }
    function openDetailModal(data) {
        console.log(data);
        // Tampilkan modal
        var html = '';
            html += '<table class="table table-responsive table-borderless">';
            html += '<tbody>';
            html += '<tr>';
            html += '<td>Nama Karyawan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.nama_karyawan || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>Divisi</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.divisi || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>Kategori</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.kategori || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>Detail Kendala</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.detail_kendala || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>Keperluan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.keperluan || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>Waktu Response</td>';
            // Pastikan kedua bagian ada sebelum digabung, jika tidak, tampilkan '-'
            html += '<td>:</td>';
            html += '<td>' + ((data.tanggal_response || '-') + ' ' + (data.jam_response || '-')) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Penanganan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.penanganan || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>PIC</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.pic || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>Status</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.status || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>Waktu Selesai</td>';
            // Pastikan kedua bagian ada sebelum digabung, jika tidak, tampilkan '-'
            html += '<td>:</td>';
            html += '<td>' + ((data.tanggal_selesai || '-') + ' ' + (data.jam_selesai || '-')) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Keterangan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.keterangan || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '<tr>';
            html += '<td>Tingkat Kesulitan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.tingkat_kesulitan || '-') + '</td>'; // Tambahkan || '-'
            html += '</tr>';
            html += '</tbody>';
            html += '</table>';
            $('#content').html(html);


        $('#detailModal').modal('show');
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