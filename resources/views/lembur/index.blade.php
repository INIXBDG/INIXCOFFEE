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
                    <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="approveForm" method="POST">
                        @csrf
                        @method('PUT')
                        <p>Apakah Disetujui?</p>
                        <div id="manager-row">
                            <div class="btn-group" role="group" aria-label="Approval Options">
                                <input type="radio" class="btn-check" name="approval" id="approveYes" value="1" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="approveYes" onclick="toggleAlasanManager(false)">Ya</label>
                                <input type="radio" class="btn-check" name="approval" id="approveNo" value="2" autocomplete="off">
                                <label class="btn btn-outline-danger" for="approveNo" onclick="toggleAlasanManager(true)">Tidak</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                @can('Create Lembur')
                    <a href="{{ route('lembur.create') }}" class="btn btn-md click-primary mx-4"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Perintah Lembur</a>
                @endcan
            </div>
            @can('Create Lembur')
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Perintah Lembur Karyawan') }}</h3>
                        <table class="table table-striped" id="perintahlemburkaryawan">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Karyawan</th>
                                    <th scope="col">Divisi</th>
                                    <th scope="col">Tanggal SPL</th>
                                    <th scope="col">Uraian Tugas</th>
                                    <th scope="col">Waktu Lembur</th>
                                    <th scope="col">Tanggal Lembur</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endcan
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Lembur Karyawan') }}</h3>
                    <table class="table table-striped" id="lemburkaryawan">
                        <thead>
                            <tr>
                                <th scope="col" rowspan="2">No</th>
                                <th scope="col" rowspan="2">Nama Karyawan</th>
                                <th scope="col" rowspan="2">Jabatan</th>
                                <th scope="col" colspan="3" class="text-center">Jam Lembur</th>
                                <th scope="col" rowspan="2">Uraian Tugas</th>
                                <th scope="col" rowspan="2">Tanggal Lembur</th>
                                <th scope="col" rowspan="2">Keterangan</th>
                                <th scope="col" rowspan="2">Disetujui Karyawan</th>
                                <th scope="col" rowspan="2">Aksi</th>
                            </tr>
                            <tr>
                                <th scope="col" class="text-center">Mulai</th>
                                <th scope="col" class="text-center">Selesai</th>
                                <th scope="col" class="text-center">Total Jam</th>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan}}';
        var canUpdateLembur = {{ auth()->user()->can('Update Lembur') ? 'true' : 'false' }};
        var canCreateLembur = {{ auth()->user()->can('Create Lembur') ? 'true' : 'false' }};
        var canDeleteLembur = {{ auth()->user()->can('Delete Lembur') ? 'true' : 'false' }};
        
        $('#perintahlemburkaryawan').DataTable({
            "ajax": {
                "url": "{{ route('getSuratPerintahLembur') }}",
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
                {"data": "id"},
                {"data": "karyawan.nama_lengkap"},
                {"data": "karyawan.divisi"},
                {
                    "data": null,
                    "render": function (data, type, row) {
                        var created_at = moment(data.tanggal_spl).format('dddd, DD MMMM YYYY');
                        return created_at;
                    },
                },
                {"data": "uraian_tugas"},
                {"data": "waktu_lembur"},
                {
                    "data": null,
                    "render": function (data, type, row) {
                        var created_at = moment(data.tanggal_lembur).format('dddd, DD MMMM YYYY');
                        return created_at;
                    },
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        if (userRole === 'Direktur' || userRole === 'Direktur Utama') {
                            return "";
                        } else {
                            var actions = "";
                            actions += '@if (auth()->user()->can('Delete Lembur'))'
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '<a class="dropdown-item" href="{{ url('/lembur') }}/' + row.id + '/edit" data-toggle="tooltip" data-placement="top" title="Edit Souvenir"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                                actions += '@can('Delete Lembur')';
                                actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/lembur') }}/' + row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '@endcan';
                                actions += '</div>';
                                actions += '</div>';
                                actions += '@else';
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle disabled" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '</div>';
                                actions += '@endif';
                            return actions;
                        }
                    }
                }
            ]
        });

        $('#lemburkaryawan').DataTable({
            "ajax": {
                "url": "{{ route('getLemburKaryawan') }}",
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
                {"data": "id"},
                {"data": "karyawan.nama_lengkap"},
                {"data": "karyawan.jabatan"},
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return data.jam_mulai ? data.jam_mulai : '-';
                    },
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return data.jam_selesai ? data.jam_selesai : '-';
                    },
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        if (data.jam_mulai && data.jam_selesai) {
                            var start = moment(data.jam_mulai, "HH:mm");
                            var end = moment(data.jam_selesai, "HH:mm");
                            if (end.isBefore(start)) {
                                end.add(1, 'day');
                            }
                            var duration = moment.duration(end.diff(start));
                            return duration.asHours().toFixed(2) + ' Jam';
                        }
                        return '0.00';
                    },
                },
                {"data": "uraian_tugas"},
                {
                    "data": null,
                    "render": function (data, type, row) {
                        var created_at = moment(data.tanggal_lembur).format('dddd, DD MMMM YYYY');
                        return created_at;
                    },
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return data.keterangan ? data.keterangan : '-';
                    },
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        if (data.approval_karyawan == null) {
                            return '<span class="badge bg-primary"> Belum Disetujui </span>';
                        } else if (data.approval_karyawan == 'Disetujui') {
                            return '<span class="badge bg-success"> Disetujui </span>';
                        } else if (data.approval_karyawan == 'Ditolak') {
                            return '<span class="badge bg-danger"> Ditolak </span>';
                        } else {
                            return '<span class="badge bg-primary"> Belum Disetujui </span>';
                        }
                    },
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                        actions += '<div class="dropdown">';
                        actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                        actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                        actions += '<a class="dropdown-item" href="{{route('lembur.show', ':id')}}"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';
                        actions = actions.replace(':id', data.id);
                        if (data.approval_karyawan == null) {
                            actions += '<a class="dropdown-item" href="{{ url('/lembur') }}/' + row.id + '/editKaryawan" data-toggle="tooltip" data-placement="top" title="Edit Souvenir"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Detail Aktivitas</a>';
                        }
                        if (canCreateLembur && data.approval_karyawan == null) {
                            actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                        }
                        if (canDeleteLembur) {
                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/lembur') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method('DELETE')';
                            actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            actions += '</form>';
                        }
                        actions += '</div>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ]
        });

        function openApproveModal(id, jabatan) {
            var approveUrl = "{{ url('/lembur/approval/') }}/" + id;
            $('#approveForm').attr('action', approveUrl);
            $('#approveModal').modal('show');
        }
    });
</script>
@endpush
@endsection