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

    {{-- ===== MODAL EDIT USER (Edit Tiket oleh Pengaju) ===== --}}
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Tiket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditUser" action="" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keperluan</label>
                            <select name="keperluan" id="editKeperluan" class="form-select" required>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Tim Digital">Tim Digital</option>
                                <option value="Programming">Programming</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="kategori" id="editKategori" class="form-select" required>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Detail Kendala</label>
                            <textarea name="detail_kendala" id="editDetailKendala" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL EDIT ITSM (Edit Waktu Terima & Selesai oleh ITSM) ===== --}}
    <div class="modal fade" id="editItsmModal" tabindex="-1" aria-labelledby="editItsmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editItsmModalLabel"><i class="bi bi-clock-history me-2"></i>Edit Waktu Tiket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditItsm" action="" method="POST">
                        @csrf
                        @method('PUT')
                        <p class="text-muted small mb-3">ID Tiket: <strong id="itsmTicketIdLabel"></strong></p>
                        <hr>
                        <label class="form-label fw-semibold">Waktu Terima</label>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label small text-muted">Tanggal Terima</label>
                                <input type="date" class="form-control" name="tanggal_response" id="editTanggalResponse">
                            </div>
                            <div class="col">
                                <label class="form-label small text-muted">Jam Terima</label>
                                <input type="time" class="form-control" name="jam_response" id="editJamResponse">
                            </div>
                        </div>
                        <label class="form-label fw-semibold">Waktu Selesai</label>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label small text-muted">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="tanggal_selesai" id="editTanggalSelesai">
                            </div>
                            <div class="col">
                                <label class="form-label small text-muted">Jam Selesai</label>
                                <input type="time" class="form-control" name="jam_selesai" id="editJamSelesai">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 text-white">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Waktu
                        </button>
                    </form>
                </div>
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
                            <input type="date" value="{{ now()->toDateString() }}" class="form-control" name="tanggal_selesai" id="tanggal_selesai">
                            <input type="time" value="{{ now()->toTimeString() }}" class="form-control" name="jam_selesai" id="jam_selesai">
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
                            <input type="date" value="{{ now()->toDateString() }}" class="form-control" name="tanggal_selesai" id="tanggal_selesai">
                            <input type="time" value="{{ now()->toTimeString() }}" class="form-control" name="jam_selesai" id="jam_selesai">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Selesaikan Tiket</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="terimatiketModal" tabindex="-1" aria-labelledby="terimatiketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="terimatiketModalLabel">Terima Tiket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form Tolak Tiket -->
                    <form id="formTerima" action="" method="POST">
                        @csrf
                        @method("POST")
                        <input type="hidden" name="pic" id="terimaPic" value="">
                        <input type="hidden" name="row" id="terimaRow" value="">
                        <div class="mb-3">
                            <input type="date" value="{{ now()->toDateString() }}" class="form-control" name="tanggal_response" id="tanggal_response">
                            <input type="time" value="{{ now()->toTimeString() }}" class="form-control" name="jam_response" id="jam_response">
                        </div>
                        <button type="submit" class="btn btn-success mb-2 w-100">Terima Tiket</button>
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
                    <a href="{{ route('tickets.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah User"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Buat Ticket</a>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Ticketing') }}</h3>
                    <table class="table table-striped" id="jabatantable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Timestamp</th>
                                <th scope="col">Ticket ID</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Divisi</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Keperluan</th>
                                <th scope="col">Detail Kendala</th>
                                <th scope="col">PIC</th>
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
    // Mapping kategori per keperluan (sama dengan halaman create)
    const kategoriOptions = {
        'Technical Support': [
            {value: 'Jaringan', text: 'Jaringan'},
            {value: 'Hardware', text: 'Hardware'},
            {value: 'Software', text: 'Software'},
            {value: 'Exam', text: 'Exam'}
        ],
        'Tim Digital': [
            {value: 'Flyer', text: 'Flyer'},
            {value: 'Banner Cetak', text: 'Banner Cetak'},
            {value: 'Konten (Video)', text: 'Konten (Video)'},
            {value: 'Kerja Sama Mitra', text: 'Kerja Sama Mitra'}
        ],
        'Programming': [
            {value: 'Request', text: 'Request'},
            {value: 'Error (Aplikasi)', text: 'Error (Aplikasi)'}
        ]
    };

    function updateEditKategori(selectedKeperluan, selectedKategori) {
        var $kategori = $('#editKategori');
        $kategori.html('<option value="" disabled>Pilih Kategori</option>');
        if (selectedKeperluan && kategoriOptions[selectedKeperluan]) {
            $.each(kategoriOptions[selectedKeperluan], function(i, opt) {
                var selected = (opt.value === selectedKategori) ? ' selected' : '';
                $kategori.append('<option value="' + opt.value + '"' + selected + '>' + opt.text + '</option>');
            });
        }
    }

    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan}}';
        var authNama = '{{ auth()->user()->karyawan->nama_lengkap ?? "" }}';
        var authDivisi = '{{ auth()->user()->karyawan->divisi ?? "" }}';

        $('#jabatantable').DataTable({
            "ajax": {
                "url": "{{ route('getTickets') }}",
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
                {
                    "data": "created_at",
                    "visible": false,
                },
                {
                    "data": "timestamp",
                    "render": function(data){
                        moment.locale('id');
                        return moment(data).format('DD MMMM YYYY H:mm:ss');
                    }
                },
                {"data": "ticket_id"},
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
                        var divisi = "{{ auth()->user()->karyawan->divisi }}";
                        var canDeleteTicket = @json(auth()->user()->jabatan === 'Koordinator ITSM' && optional(auth()->user()->karyawan)->divisi === 'IT Service Management');

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
                            case 'eggiherlambang':
                                pic = 'Eggi';
                                break;
                            default:
                                pic = '';
                                break;
                        }

                        // Simpan data di global map agar aman dari karakter khusus / quote
                        window.ticketDataMap = window.ticketDataMap || {};
                        window.ticketDataMap[data.id] = data;

                        var actions = "";
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            var divisi = "{{ auth()->user()->karyawan->divisi }}";
                            console.log(divisi);
                            if(data.status == 'Di Proses' && divisi == 'IT Service Management'){
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(\'' + data.id + '\', \'' + pic + '\', \'' + data.row + '\')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Kelola Tiket</button>';
                            }else if(data.status == 'Selesai' && divisi == 'IT Service Management' || data.status == 'Terkendala' && divisi == 'IT Service Management'){
                                actions += '<button type="submit" disabled class="dropdown-item"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Terima</button>';
                            }else if(divisi == 'IT Service Management'){
                                actions += '<button type="button" class="dropdown-item" onclick="terimatiketModal(\'' + data.id + '\', \'' + pic + '\', \'' + data.row + '\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Terima</button>';
                            }

                            // Status belum diproses (Menunggu, null, kosong, atau selain Di Proses/Selesai/Terkendala)
                            var statusBelumDiproses = (data.status !== 'Di Proses' && data.status !== 'Selesai' && data.status !== 'Terkendala');

                            // Tombol Edit untuk Pengaju (hanya jika tiket belum diproses)
                            // Jika user bukan ITSM, list tiket sudah otomatis milik sendiri
                            var isOwner = (divisi !== 'IT Service Management') || (data.nama_karyawan && authNama && data.nama_karyawan.trim().toLowerCase() === authNama.trim().toLowerCase());
                            if (statusBelumDiproses && (isOwner || divisi == 'IT Service Management')) {
                                actions += '<button type="button" class="dropdown-item" onclick="openEditUserModal(' + data.id + ')"><img src="{{ asset('icon/edit-dark.svg') }}" class=""> Edit</button>';
                            }

                            // Tombol Edit Waktu untuk ITSM (jika tiket sudah diproses / selesai / terkendala)
                            if (divisi == 'IT Service Management' && (data.status == 'Di Proses' || data.status == 'Selesai' || data.status == 'Terkendala')) {
                                actions += '<button type="button" class="dropdown-item" onclick="openEditItsmModal(' + data.id + ')"><img src="{{ asset('icon/edit-dark.svg') }}" class=""> Edit</button>';
                            }

                            actions += '<button type="button" class="dropdown-item" onclick="openDetailModal(' + data.id + ')"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</button>';
                            if (canDeleteTicket) {
                                actions += '<button type="button" class="dropdown-item text-danger" onclick="hapusTiket(' + data.id + ')"><i class="bi bi-trash me-2"></i>Hapus</button>';
                            }
                            actions += '</div>';
                            actions += '</div>';
                            return actions;

                    }

                }
            ],
            "order": [[0, 'desc']],
            "columnDefs" : [{"targets":[0], "type":"date"}],

        });
    });

    function hapusTiket(ticketId) {
        if (!confirm('Hapus tiket ini? Data yang dihapus tidak dapat dikembalikan.')) {
            return;
        }

        var form = $('<form>', {
            method: 'POST',
            action: "{{ url('/tickets') }}/" + ticketId
        });
        form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
        form.append('<input type="hidden" name="_method" value="DELETE">');
        $('body').append(form);
        form.submit();
    }
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
        var blockUrl = "{{ url('/tickets') }}/" + ticketId + "/block";
        $('#formBlock').attr('action', blockUrl);
        $('#blockPic').val(picValue);
        $('#blockRow').val(rowValue);

        var finishUrl = "{{ url('/tickets') }}/" + ticketId + "/finish";
        $('#formFinish').attr('action', finishUrl);
        $('#finishPic').val(picValue);
        $('#finishRow').val(rowValue);

        $('#approveModal').modal('show');
    }
    function terimatiketModal(ticketId, picValue, rowValue) {
        console.log(ticketId, picValue, rowValue);
        var terimaUrl = "{{ url('/tickets') }}/" + ticketId + "/accept";
        $('#formTerima').attr('action', terimaUrl);
        $('#terimaPic').val(picValue);
        $('#terimaRow').val(rowValue);
        $('#terimatiketModal').modal('show');
    }

    // ===== OPEN MODAL EDIT USER =====
    function openEditUserModal(item) {
        var data = (typeof item === 'object') ? item : (window.ticketDataMap ? window.ticketDataMap[item] : null);
        if (!data) return;

        var editUrl = "{{ url('/tickets') }}/" + data.id + "/edit-user";
        $('#formEditUser').attr('action', editUrl);

        // Set nilai keperluan
        $('#editKeperluan').val(data.keperluan);

        // Update kategori sesuai keperluan dan pilih kategori yang ada
        updateEditKategori(data.keperluan, data.kategori);

        // Set detail kendala
        $('#editDetailKendala').val(data.detail_kendala);

        $('#editUserModal').modal('show');
    }

    // Update kategori saat keperluan berubah di modal edit
    $('#editKeperluan').on('change', function() {
        updateEditKategori($(this).val(), '');
    });

    // ===== OPEN MODAL EDIT ITSM =====
    function openEditItsmModal(item) {
        var data = (typeof item === 'object') ? item : (window.ticketDataMap ? window.ticketDataMap[item] : null);
        if (!data) return;

        var editUrl = "{{ url('/tickets') }}/" + data.id + "/edit-itsm";
        $('#formEditItsm').attr('action', editUrl);
        $('#itsmTicketIdLabel').text(data.ticket_id || '-');

        // Isi nilai yang sudah ada
        $('#editTanggalResponse').val(data.tanggal_response || '');
        $('#editJamResponse').val(data.jam_response ? data.jam_response.substring(0, 5) : '');
        $('#editTanggalSelesai').val(data.tanggal_selesai || '');
        $('#editJamSelesai').val(data.jam_selesai ? data.jam_selesai.substring(0, 5) : '');

        $('#editItsmModal').modal('show');
    }

    function openDetailModal(item) {
        var data = (typeof item === 'object') ? item : (window.ticketDataMap ? window.ticketDataMap[item] : null);
        if (!data) return;
        console.log(data);
        var html = '';
            html += '<table class="table table-responsive table-borderless">';
            html += '<tbody>';
            html += '<tr>';
            html += '<td>Nama Karyawan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.nama_karyawan || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Divisi</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.divisi || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Kategori</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.kategori || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Detail Kendala</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.detail_kendala || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Keperluan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.keperluan || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Waktu Response</td>';
            html += '<td>:</td>';
            html += '<td>' + ((data.tanggal_response || '-') + ' ' + (data.jam_response || '-')) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Penanganan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.penanganan || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>PIC</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.pic || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Status</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.status || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Waktu Selesai</td>';
            html += '<td>:</td>';
            html += '<td>' + ((data.tanggal_selesai || '-') + ' ' + (data.jam_selesai || '-')) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Keterangan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.keterangan || '-') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Tingkat Kesulitan</td>';
            html += '<td>:</td>';
            html += '<td>' + (data.tingkat_kesulitan || '-') + '</td>';
            html += '</tr>';
            html += '</tbody>';
            html += '</table>';
            $('#content').html(html);


        $('#detailModal').modal('show');
    }
</script>
@endpush
@endsection
