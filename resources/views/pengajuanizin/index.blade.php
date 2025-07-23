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
                                <input type="radio" class="btn-check" name="approval" id="approveYes"
                                @if (auth()->user()->jabatan === 'HRD')
                                value="2"
                                @else
                                value="1"
                                @endif
                                autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="approveYes" onclick="toggleAlasanManager(false)">Ya</label>

                                <input type="radio" class="btn-check" name="approval" id="approveNo" value="4" autocomplete="off">
                                <label class="btn btn-outline-danger" for="approveNo" onclick="toggleAlasanManager(true)">Tidak</label>
                            </div>

                            <div class="mt-3" id="alasanManagerInput" style="display: none;">
                                <label for="alasan_approval" class="form-label">Alasan Penolakan</label>
                                <textarea class="form-control" id="alasan_approval" name="alasan" rows="3"></textarea>
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
                @if (auth()->user()->jabatan === 'HRD')
                <a href="{{ route('pengajuanIzin.excelDownload') }}" class="btn btn-success me-3" data-toggle="tooltip" data-placement="top" title="Rekap Excel"><img src="{{ asset('icon/file-excel.svg') }}" class="" width="30px"> Download Excel</a>
                <a href="{{ route('pengajuanIzin.PDFDownload') }}" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Rekap PDF">
                    <img src="{{ asset('icon/pdf-file.svg') }}" class="" width="30px"> Download PDF
                </a>
                @endif
                <a href="pengajuanizin/create" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Izin"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Ajukan Izin</a>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Izin') }}</h3>
                    <table class="table table-striped" id="jabatantable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">KODE</th>
                                <th scope="col">Jam</th>
                                <th scope="col">Alasan</th>
                                <th scope="col">Durasi</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Status</th>
                                <th scope="col">Alasan Approval</th>
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
    $(document).ready(function() {
        var userRole = '{{ auth()->user()->jabatan }}';
        var user = '{{ auth()->user()->karyawan_id }}';
        var userDivisi = '';
        var $no = 1;

        $('#jabatantable').DataTable({
            "ajax": {
                "url": "{{ route('getPengajuanIzin') }}",
                "type": "GET",
                "beforeSend": function() {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function() {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function(xhr) {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function() {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                },
                "dataSrc": function(json) {
                    json_user_divisi = json.data.divisi;
                    return json.data.pengajuanizin;
                }
            },
            "columns": [{
                    "data": null,
                    "render": function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    "data": "karyawan.nama_lengkap",
                    "defaultContent": "-"
                },
                {
                    "data": "karyawan.divisi",
                    "defaultContent": "-"
                },
                {
                    "data": "karyawan.jabatan",
                    "visible": false,
                    "defaultContent": "-"
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return row.jam_mulai.slice(0, 5) + ' s/d ' + row.jam_selesai.slice(0, 5);
                    }
                },
                {
                    "data": "alasan",
                    "defaultContent": "-"
                },
                {
                    "data": "durasi",
                    "render": function(data) {
                        return data + ' Jam';
                    }
                },
                {
                    "data": "tanggal",
                    "render": function(data, type, row) {
                        return moment(row.tanggal).isValid() ? moment(row.tanggal).format('DD MMMM YYYY') : '-';
                    }
                },
                {
                    "data": "approval",
                    "render": function(data, type, row) {
                        switch (parseInt(row.approval)) {
                            case 0:
                                return '<span class="badge bg-warning text-dark">Menunggu Koordinator</span>';
                            case 1:
                                return '<span class="badge bg-warning text-dark">Menunggu HRD</span>';
                            case 2:
                                return '<span class="badge bg-success">Disetujui</span>';
                            case 4:
                                return '<span class="badge bg-danger">Ditolak</span>';
                            default:
                                return '<span class="badge bg-secondary text-dark">Status Tidak Diketahui</span>';
                        }
                    }
                },
                {
                    "data": "alasan_approval",
                    "render": function(data) {
                        return data ?? '-';
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                        var userRole = '{{ auth()->user()->jabatan }}';
                        var requesterRole = data.karyawan.jabatan;
                        var requesterDivisi = data.karyawan.divisi;
                        var approval = data.approval;

                        actions += '<div class="dropdown">';
                        actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                        actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                        // Form PDF hanya jika approval === 2
                        if (approval === 2) {
                            actions += '<a class="dropdown-item" href="/pengajuanizin/' + row.id + '">';
                            actions += '<img src="{{ asset('icon/assept-document.svg') }}" style="width:24px" class=""> Form PDF</a>';
                        }

                        // Tombol Approve
                        let approveDisabled = true;
                        let approveColor = '';

                        if (approval === 0 && userRole.includes('Koordinator')) {
                            approveDisabled = false;
                        } else if (approval === 0 && userRole === 'Education Manager' && requesterDivisi === 'Education') {
                            approveDisabled = false;
                        } else if (approval === 0 && userRole === 'SPV Sales' && requesterDivisi === 'Sales & Marketing') {
                            approveDisabled = false;
                        } else if (approval === 0 && userRole === 'Koordinator ITSM' && requesterDivisi === 'IT Service Management') {
                            approveDisabled = false;
                        } else if (approval === 1 && userRole === 'HRD') {
                            approveDisabled = false;
                        } else if (approval === 4) {
                            approveDisabled = true;
                            approveColor = ' style="color: red;"';
                        }

                        if (approval <= 2) {
                            actions += '<button type="button" class="dropdown-item' + (approveDisabled ? ' disabled' : '') + '" ' +
                                (approveDisabled ? '' : 'onclick="openApproveModal(' + row.id + ', \'' + userRole + '\')"') + approveColor + '>';
                            actions += '<img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Approve</button>';
                        }

                        if (approval === 4) {
                            actions += '<button type="button" class="dropdown-item disabled text-danger">';
                            actions += '<img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Ditolak</button>';
                        }

                        // Tombol Hapus untuk HRD di approval 0, 2, atau 4
                        if ((userRole === 'HRD' && approval === 4) || (userRole === 'HRD' && approval === 0) || (userRole === 'HRD' && approval === 2)) {
                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/pengajuanizin') }}/' + row.id + '" method="POST">';
                            actions += '@csrf';
                            actions += '@method("DELETE")';
                            actions += '<button type="submit" class="dropdown-item">';
                            actions += '<img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                            actions += '</form>';
                        }

                        actions += '</div></div>';
                        return actions;
                    }
                }
           ],
            "order": [
                [0, 'desc']
            ]
        });
    });

    document.getElementById('approveForm').addEventListener('submit', function(e) {
        var approvalYes = document.getElementById('approveYes');
        var approvalNo = document.getElementById('approveNo');
        var alasanTextarea = document.getElementById('alasan_approval');
        var jabatan = @json(auth()->user()->jabatan);

        if (approvalNo.checked) {
            if (!alasanTextarea.value.trim()) {
                e.preventDefault();
                alert('Mohon isi alasan penolakan.');
                return;
            }

            approvalNo.value = 4;

            if (!alasanTextarea.value.includes('Ditolak oleh')) {
                alasanTextarea.value = 'Ditolak oleh ' + jabatan + ': ' + alasanTextarea.value;
            }
        }
    });

    function openApproveModal(id, jabatan) {
        var approveUrl = "{{ url('/pengajuanizin') }}/" + id;
        $('#approveForm').attr('action', approveUrl);
        $('#approveForm').data('jabatan', jabatan);
        $('#approveModal').modal('show');
    }

    function toggleAlasanManager(show) {
        const alasanInput = document.getElementById('alasanManagerInput');
        const approvalRadio = document.querySelector('input[name="approval"]');

        if (show) {
            alasanInput.style.display = 'block';
            approvalRadio.value = 4;
        } else {
            alasanInput.style.display = 'none';
            document.getElementById('alasan_approval').value = '';
            approvalRadio.value = 1;
        }
    }
</script>
@endpush
@endsection