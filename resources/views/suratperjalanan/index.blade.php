@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                                <input type="radio" class="btn-check" name="approval_manager" id="approveYes" value="1" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="approveYes">Ya</label>
        
                                <input type="radio" class="btn-check" name="approval_manager" id="approveNo" value="2" autocomplete="off">
                                <label class="btn btn-outline-danger" for="approveNo">Tidak</label>
                            </div>
                        </div>
                        <div id="direksi-row">
                            <div class="btn-group" role="group" aria-label="Approval Options">
                                <input type="radio" class="btn-check" name="approval_direksi" id="approveYes" value="1" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="approveYes">Ya</label>
        
                                <input type="radio" class="btn-check" name="approval_direksi" id="approveNo" value="2" autocomplete="off">
                                <label class="btn btn-outline-danger" for="approveNo">Tidak</label>
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
                {{-- @if ( auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Office ') --}}
                @can('Rekap SPJ')
                    <a href="{{ route('createPrint') }}" class="btn btn-success mx-4" data-toggle="tooltip" data-placement="top" title="Cetak Data"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Cetak</a>                    
                @endcan    
                    <a href="suratperjalanan/create" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Cuti"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Ajukan Surat Perjalanan</a>
                {{-- @endif --}}
            </div>  
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Surat Perjalanan') }}</h3>
                    <table class="table table-striped" id="jabatantable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">Jabatan</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Tujuan</th>
                                <th scope="col">Alasan</th>
                                <th scope="col">Durasi</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Status</th>
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
        //console.log(user);
         $('#tahun, #bulan').on('change', function() {
                updateExportLink();
        });
        $('#jabatantable').DataTable({
            "ajax": {
                "url": "{{ route('getSuratPerjalanan') }}", // URL API untuk mengambil data
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
                {"data": "tanggal_berangkat", "visible": false},
                {"data": "karyawan.nama_lengkap"},
                {"data": "karyawan.divisi"},
                {"data": "karyawan.jabatan", "visible": false},
                {"data": "tipe"},
                {"data": "tujuan"},
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
                        return moment(data.tanggal_berangkat).format('DD MMMM YYYY')+ ' s/d ' + moment(data.tanggal_pulang).format('DD MMMM YYYY');
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        if (data.approval_manager == '0' && data.approval_hrd == '0') {
                            return '<span class="badge bg-warning" style="color:black;"> Menunggu Persetujuan Manager Divisi </span>';
                        } 
                        
                        if (data.approval_manager == '1' && data.approval_hrd == '0') {
                            return '<span class="badge bg-warning"> Menunggu Rate dan Persetujuan HRD </span>';
                        } 
                        
                        if (data.approval_manager == '1' && data.approval_hrd == '1') {
                            if (data.tipe == "Internasional" && data.approval_direksi == '0') {
                                return '<span class="badge bg-warning"> Menunggu Persetujuan Direksi </span>';
                            }else if(data.tipe == "Internasional" && data.approval_direksi == '2') {
                                return '<span class="badge bg-danger"> Ditolak </span>';
                            }else{
                                return '<span class="badge bg-success"> Disetujui </span>';
                            }
                        }
                        if (data.approval_manager == '2') {
                            return '<span class="badge bg-danger"> Ditolak </span>';
                        }
                        
                        return '<span class="badge bg-secondary"> Status Tidak Diketahui </span>';
                    },
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                        var allowedRoles = ['Office Manager', 'Education Manager', 'SPV Sales', 'GM', 'Direktur Utama', 'Direktur', 'Koordinator ITSM'];
                        var userRole = '{{ auth()->user()->jabatan}}';
                        var requesterRole = data.karyawan.jabatan; // Assuming this is passed in the row data

                        if (allowedRoles.includes(userRole)) {
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                            if (userRole == 'GM') {
                                // GM can only approve if the requester is Office Manager, Education Manager, or SPV Sales
                                if (['Office Manager', 'Education Manager', 'SPV Sales', 'Koordinator ITSM'].includes(requesterRole)) {
                                    if (data.approval_manager === '1') {
                                        actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    } else {
                                        actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    }
                                } else {
                                    actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                }
                            } else if (userRole !== requesterRole) {
                                // Other allowed roles can approve subordinate's requests, but not their own
                                if (data.approval_manager === '1') {
                                    actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                } else {
                                    actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                }
                            }
                            else if (userRole == 'Direktur' || userRole == 'Direktur Utama') {
                                if (data.approval_manager === '1') {
                                        actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    } else {
                                        actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    }
                            }
                            else {
                                // Disable approval if the requester is the same as the user role
                                actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                            }
                            
                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/suratperjalanan') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method('DELETE')';
                            if (data.approval_manager == '0') {
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            } else {
                                actions += '<button type="submit" class="dropdown-item disabled"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';    
                            }
                            actions += '</form>';
                            actions += '</div>';
                            actions += '</div>';
                        } else if (userRole == 'Koordinator Office') {
                            actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                if (data.approval_manager == '0') {
                                    if (data.approval_manager === '1') {
                                        actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    } else {
                                        actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                    }
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';
                                } else if(data.approval_manager == '1' && data.approval_hrd == '1'){
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';    
                                    actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';    
                                    actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '/editspj"><img src="{{ asset('icon/edit-2.svg') }}" style="width:24px" class=""> Edit</a>';    
                                } else if(data.approval_manager == '1' && data.approval_hrd == '0'){
                                    actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';    
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';    
                                    actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/suratperjalanan') }}/' + row.id + '" method="POST">';
                                    actions += '@csrf';
                                    actions += '@method('DELETE')';
                                    actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                    actions += '</form>';
                                } else {
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';    
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';    
                                }
                                actions += '</div>';
                                actions += '</div>';
                        } else if (userRole == 'HRD') {
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                if (data.approval_manager == '0') {
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';
                                } else if(data.approval_manager == '1' && data.approval_hrd == '1'){
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';    
                                    actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';    
                                    actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '/editspj"><img src="{{ asset('icon/edit-2.svg') }}" style="width:24px" class=""> Edit</a>';    
                                } else if(data.approval_manager == '1' && data.approval_hrd == '0'){
                                    actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';    
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';    
                                    actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/suratperjalanan') }}/' + row.id + '" method="POST">';
                                    actions += '@csrf';
                                    actions += '@method('DELETE')';
                                    actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                    actions += '</form>';
                                } else {
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';    
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';    
                                }
                                actions += '</div>';
                                actions += '</div>';
                        } else if (userRole == 'Direktur' || userRole == 'Direktur Utama') {
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            
                            if (data.approval_manager == '0' && data.approval_direksi == '0' && data.approval_hrd == '0' && data.tipe == 'Internasional') {
                                actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                            } else if (data.approval_manager == '1' && data.approval_direksi == '1' && data.approval_hrd == '1' && data.tipe == 'Internasional') {
                                actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" alt="Form PDF"> Form PDF</a>';    
                            } else if (data.approval_manager == '1' && data.approval_direksi == '0' && data.approval_hrd == '1' && data.tipe == 'Internasional') {
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Direksi\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                                actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" alt="Form PDF"> Form PDF</a>';    
                            } else {
                                actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" alt="Rate SPJ"> Rate SPJ</a>';    
                                actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" alt="Form PDF"> Form PDF</a>';    
                            }
                            
                            actions += '</div>';
                            actions += '</div>';
                        } else {
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/suratperjalanan') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method('DELETE')';
                            if (data.approval_manager == '0') {
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                
                            } else if(data.approval_manager == '1' && data.approval_hrd == '1'){
                                    actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Rate SPJ</a>';    
                                    actions += '<a class="dropdown-item" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';    
                            } else {
                                actions += '<button type="submit"  class="dropdown-item disabled"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '<a class="dropdown-item disabled" href="{{ url('/suratperjalanan') }}/' + row.id + '"><img src="{{ asset('icon/assept-document.svg') }}"  style="width:24px"  class=""> Form PDF</a>';    
                                
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
            //console.log(jabatan)
            if (jabatan === 'Manager') {
                $('#manager-row').show();
                $('#direksi-row').hide();
            } else if (jabatan === 'Direksi') {
                $('#manager-row').hide();
                $('#direksi-row').show();
            }
            var approveUrl = "{{ url('/suratperjalanan') }}/" + id + "/approval";
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
