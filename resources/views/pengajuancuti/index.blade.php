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
        
                            <div class="mt-3" id="alasanManagerInput" style="display: none;">
                                <label for="alasan_manager" class="form-label">Alasan Penolakan</label>
                                <textarea class="form-control" id="alasan_manager" name="alasan" rows="3"></textarea>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                {{-- @can('Rekap Cuti') --}}
                    <a href="{{route('pengajuancuti.rekap')}}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Cuti"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Rekap</a>
                {{-- @endcan --}}
                <a href="pengajuancuti/create" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Cuti"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Ajukan Cuti</a>
            </div>  
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Cuti') }}</h3>
                    <table class="table table-striped" id="jabatantable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">KODE</th>
                                <th scope="col">Kontak</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Alasan</th>
                                <th scope="col">Durasi</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Status</th>
                                <th scope="col">Alasan Manager</th>
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
        var user = '{{ auth()->user()->karyawan_id }}';
        console.log(user);
        $('#jabatantable').DataTable({
            "ajax": {
                "url": "{{ route('getPengajuanCuti') }}", // URL API untuk mengambil data
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
                {"data": "tanggal_awal", "visible": false},
                {"data": "karyawan.nama_lengkap"},
                {"data": "karyawan.divisi"},
                {"data": "karyawan.jabatan", "visible": false},
                {"data": "kontak"},
                {"data": "tipe"},
                {"data": "alasan"},
                {
                    "data": null,
                    "render": function(data) {
                        return data.durasi + ' Hari' ;
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        moment.locale('id');
                        return moment(data.tanggal_awal).format('DD MMMM YYYY')+ ' s/d ' + moment(data.tanggal_akhir).format('DD MMMM YYYY');
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        if (data.approval_manager == '0') {
                            return '<span class="badge bg-warning" style="color:black;"> Menunggu Persetujuan Manager Divisi </span>';
                        } else if (data.approval_manager == '1') {
                            return '<span class="badge bg-success"> Disetujui </span>';
                        } else if (data.approval_manager == '2') {
                            return '<span class="badge bg-danger"> Ditolak </span>';
                        }
                    },
                },
                {   "data": null,
                    "render": function(data) {
                        if (data.alasan_manager == null) {
                            return '-';
                        } else 
                            return data.alasan_manager;
                        }
                },

                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                        const allowedDeleteRoles = ['GM', 'Education Manager', 'Office Manager', 'Koordinator Office', 'SPV Sales', 'Koordinator ITSM'];
                        var allowedRoles = ['Office Manager', 'Koordinator Office', 'Education Manager', 'SPV Sales', 'GM', 'Koordinator ITSM'];
                        var userRole = '{{ auth()->user()->jabatan}}';
                        var requesterRole = data.karyawan.jabatan; // Assuming this is passed in the row data
                        console.log(userRole);
                        // Base URL for file viewing
                        var fileBaseUrl = '{{ url('storage/') }}/';
                        var suratSakitUrl = fileBaseUrl + (data.surat_sakit || '');

                        if (allowedRoles.includes(userRole)) {
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                            // View Surat Sakit
                            if (data.surat_sakit) {
                                actions += '<a class="dropdown-item" href="' + suratSakitUrl + '" target="_blank"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> View Surat Sakit</a>';
                            }

                            if (userRole == 'GM') {
                                // GM can only approve if the requester is Office Manager, Education Manager, or SPV Sales
                                if (['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator Office', 'Koordinator ITSM'].includes(requesterRole)) {
                                    if (data.approval_manager === '1') {
                                        actions += '<a class="dropdown-item" href="{{route('pengajuancuti.show', ':id')}}"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';
                                        actions = actions.replace(':id', data.id);
                                        actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    } else if (data.approval_manager === '2') {
                                        actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    } else {
                                        actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    }
                                } else {
                                    actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                }
                            } else if (userRole !== requesterRole) {
                                // Other roles can approve subordinate's requests, but not their own
                                if (data.approval_manager === '1') {
                                    actions += '<a class="dropdown-item" href="{{route('pengajuancuti.show', ':id')}}"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';
                                    actions = actions.replace(':id', data.id);
                                    actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                } else if (data.approval_manager === '2') {
                                    actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                } else {
                                    actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                }
                            } else {
                                actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                            }

                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/pengajuancuti') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method('DELETE')';

                            // Hapus hanya jika data berasal dari Education Manager, Office Manager, atau SPV Sales dan userRole adalah GM
                            if (userRole === 'HRD') {
                                    actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            } else {
                                    actions += '<button type="submit" class="dropdown-item disabled"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            }

                            actions += '</form>';
                            actions += '</div>';
                            actions += '</div>';
                        } else {
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '<a class="dropdown-item" href="{{route('pengajuancuti.show', ':id')}}"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class="">  Form PDF</a>';
                            actions = actions.replace(':id', data.id);
                            
                            // View Surat Sakit
                            if (data.surat_sakit) {
                                actions += '<a class="dropdown-item" href="' + suratSakitUrl + '" target="_blank"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> View Surat Sakit</a>';
                            }

                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/pengajuancuti') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method('DELETE')';
                            if (userRole === 'HRD') {
                                    actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            } else {
                                    actions += '<button type="submit" class="dropdown-item disabled"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            }
                            actions += '</form>';
                            actions += '</div>';
                            actions += '</div>';
                        }

                        return actions;
                    }
                }

            ],
            "order": [[0, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
                "columnDefs" : [{"targets":[0], "type":"date"}],
        });
    });
        function openApproveModal(id, jabatan) {
            // Set the action URL for the approval form
            var approveUrl = "{{ url('/pengajuancuti') }}/" + id;
            $('#approveForm').attr('action', approveUrl);
            $('#approveModal').modal('show');
        }

        function toggleAlasanManager(show) {
            if (show) {
                document.getElementById('alasanManagerInput').style.display = 'block';
            } else {
                document.getElementById('alasanManagerInput').style.display = 'none';
                document.getElementById('alasan_manager').value = ''; // Clear the input if hidden
            }
        }
        
</script>
@endpush
@endsection
