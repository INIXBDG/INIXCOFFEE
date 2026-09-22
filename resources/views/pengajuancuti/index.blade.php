@extends('layouts.app')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


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

    <!-- Modal Edit Pengajuan -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Pengajuan Cuti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <!-- Hidden input penanda bahwa form ini melakukan update data, bukan approval -->
                        <input type="hidden" name="jenis_update" value="edit_data">

                        <div id="edit-warning-container"></div>

                        <div class="mb-3">
                            <label for="edit_tanggal_awal" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="edit_tanggal_awal" name="tanggal_awal" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tanggal_akhir" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="edit_tanggal_akhir" name="tanggal_akhir" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_durasi" class="form-label">Durasi (Hari)</label>
                            <!-- Tambahkan atribut readonly di sini -->
                            <input type="number" class="form-control" id="edit_durasi" name="durasi" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tipe" class="form-label">Jenis Cuti</label>
                            <select name="tipe" id="edit_tipe" class="form-select" required>
                                <option value="-">Pilih Jenis Cuti</option>
                                <option value="Cuti">Cuti</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Berduka">Berduka</option>
                                <option value="Menikah">Menikah</option>
                                <option value="Hamil & Melahirkan">Hamil & Melahirkan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_alasan" class="form-label">Alasan Cuti</label>
                            <textarea class="form-control" id="edit_alasan" name="alasan" rows="3" required></textarea>
                        </div>
                        <div class="mb-3" id="edit_surat_sakit_row" style="display: none;">
                            <label for="edit_surat_sakit" class="form-label">Surat Sakit</label>
                            <input type="file" class="form-control" id="edit_surat_sakit" name="surat_sakit" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">*Kosongkan jika tidak ingin mengubah surat sakit saat ini.</small>

                            <div id="current_surat_sakit_container" class="mt-2" style="display: none;">
                                <a href="#" target="_blank" id="link_current_surat_sakit" class="btn btn-sm btn-info text-white">
                                    <i class="bi bi-eye"></i> Lihat Surat Sakit Saat Ini
                                </a>
                            </div>
                        </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <!-- Tambahkan id="btnSimpanEdit" di sini -->
                            <button type="submit" class="btn btn-primary" id="btnSimpanEdit">Simpan Perubahan</button>
                        </div>
                    </form>
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
                @can('Rekap Cuti')
                    <a href="{{route('pengajuancuti.rekap')}}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Cuti">Rekap Pengajuan Cuti</a>
                @endcan
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
        var table = $('#jabatantable').DataTable({
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
                            return `
                                <span class="badge rounded-pill bg-warning text-dark">
                                    <i class="bi bi-hourglass-split me-1"></i> Menunggu Persetujuan Manager Divisi
                                </span>`;
                        } else if (data.approval_manager == '1') {
                            return `
                                <span class="badge rounded-pill bg-success">
                                    <i class="bi bi-check-circle me-1"></i> Disetujui
                                </span>`;
                        } else if (data.approval_manager == '2') {
                            return `
                                <span class="badge rounded-pill bg-danger">
                                    <i class="bi bi-x-circle me-1"></i> Ditolak
                                </span>`;
                        }
                    }
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

                            // Bersihkan enter pada alasan agar tidak merusak parameter function JS
                            var safeAlasan = row.alasan ? row.alasan.replace(/[\r\n]+/g, ' ').replace(/'/g, "\\'") : '';
                            var safeSuratSakit = row.surat_sakit ? row.surat_sakit : ''; // Tambahkan ini

                            actions += '<button type="button" class="dropdown-item" onclick="openEditModal(' + row.id + ', \'' + row.tanggal_awal + '\', \'' + row.tanggal_akhir + '\', \'' + row.durasi + '\', \'' + safeAlasan + '\', \'' + row.approval_manager + '\', \'' + row.tipe + '\', \'' + safeSuratSakit + '\')">';
                            actions += '<i class="bi bi-pencil-square" style="font-size: 1.1rem; margin-right:5px; color:#0d6efd;"></i> Edit Data';
                            actions += '</button>';

                            if (userRole == 'GM') {
                            const allowedIds = [4, 14, 29]; // ID spesial yang boleh dia approve
                            const isOffice = data.karyawan.divisi === 'Office';
                             const isSpesialId = allowedIds.includes(data.karyawan.id);

                                if (isOffice || isSpesialId) {
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
                                            }
                                    else if (userRole !== requesterRole) {
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

                            // --- TAMBAHKAN KODE EDIT INI DI DALAM ELSE ---
                            var safeAlasan = row.alasan ? row.alasan.replace(/[\r\n]+/g, ' ').replace(/'/g, "\\'") : '';
                            var safeSuratSakit = row.surat_sakit ? row.surat_sakit : ''; // Tambahkan ini

                            actions += '<button type="button" class="dropdown-item" onclick="openEditModal(' + row.id + ', \'' + row.tanggal_awal + '\', \'' + row.tanggal_akhir + '\', \'' + row.durasi + '\', \'' + safeAlasan + '\', \'' + row.approval_manager + '\', \'' + row.tipe + '\', \'' + safeSuratSakit + '\')">';
                            actions += '<i class="bi bi-pencil-square" style="font-size: 1.1rem; margin-right:5px; color:#0d6efd;"></i> Edit Data';
                            actions += '</button>';

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

        function reloadTableKeepPage() {
            var currentPage = table.page();
            table.ajax.reload(function () {
                table.page(currentPage).draw(false);
            }, false);
        }

        // ✅ Submit form approve pakai AJAX
        $('#approveForm').on('submit', function(e) {
            e.preventDefault(); // Jangan reload page

            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                success: function(response) {
                    $('#approveModal').modal('hide');

                    // 🚀 Reload DataTable setelah berhasil
                    $('#jabatantable').DataTable().ajax.reload(null, false); // false = tetap di page sekarang

                    // ✅ Tampilkan alert Bootstrap biasa
                    $('#alert-success').remove(); // hapus alert sebelumnya kalau ada
                    $('#content-wrapper').prepend(`
                        <div id="alert-success" class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            Data berhasil disetujui!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                },
                error: function(xhr) {
                    alert('Gagal menyimpan data!');
                }
            });
        });

        // ✅ Submit form Edit pakai AJAX dengan FormData (mendukung file upload)
        $('#editForm').on('submit', function(e) {
            e.preventDefault(); // Mencegah halaman ter-refresh

            var form = $(this)[0];
            var formData = new FormData(form);
            var url = $(this).attr('action');

            // Bersihkan pesan error sebelumnya jika ada
            $('#edit-warning-container .alert-danger').remove();

            $.ajax({
                type: "POST", // Method spoofing (PUT) sudah ada di dalam form
                url: url,
                data: formData,
                contentType: false, // Wajib diset false untuk upload file dengan FormData
                processData: false, // Wajib diset false untuk upload file dengan FormData
                success: function(response) {
                    // Tutup modal
                    $('#editModal').modal('hide');

                    // Reload Datatable tanpa me-refresh halaman
                    $('#jabatantable').DataTable().ajax.reload(null, false);

                    // Tampilkan pesan sukses di halaman
                    $('#alert-success').remove();
                    $('#content-wrapper').prepend(`
                        <div id="alert-success" class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            Data berhasil diubah!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // JIKA GAGAL VALIDASI DARI LARAVEL (Misal file > 2MB atau tipe file salah)
                        var errors = xhr.responseJSON.errors;
                        var errorMsg = '<ul class="mb-0" style="padding-left: 20px;">';
                        $.each(errors, function(key, value) {
                            errorMsg += '<li>' + value[0] + '</li>';
                        });
                        errorMsg += '</ul>';

                        // Tampilkan error merah langsung di dalam modal
                        $('#edit-warning-container').prepend(`
                            <div class="alert alert-danger py-2" role="alert">
                                <small><strong>Gagal menyimpan:</strong><br>${errorMsg}</small>
                            </div>
                        `);
                    } else {
                        alert('Gagal menyimpan data! Terjadi kesalahan sistem.');
                    }
                }
            });
        });

                // Trigger perhitungan saat tanggal diubah
        $('#edit_tanggal_awal, #edit_tanggal_akhir').on('change', function() {
            hitungDurasiCuti();
        });

    });

    function openApproveModal(id, jabatan) {
        var approveUrl = "{{ url('/pengajuancuti') }}/" + id;
        $('#approveForm').attr('action', '/pengajuancuti/' + id);
        $('#approveModal').modal('show');
    }

    function toggleAlasanManager(show) {
        if (show) {
            document.getElementById('alasanManagerInput').style.display = 'block';
        } else {
            document.getElementById('alasanManagerInput').style.display = 'none';
            document.getElementById('alasan_manager').value = '';
        }
    }

    // ✅ Fungsi Menghitung Durasi Sesuai Jabatan (Sama seperti Create)
    function hitungDurasiCutiEdit() {
        var userRole = '{{ auth()->user()->jabatan }}';
        var startDateStr = $('#edit_tanggal_awal').val();
        var endDateStr = $('#edit_tanggal_akhir').val();

        if (startDateStr && endDateStr) {
            var startDate = new Date(startDateStr);
            var endDate = new Date(endDateStr);

            if (!isNaN(startDate) && !isNaN(endDate)) {
                var daysDifference = 0;
                var currentDate = new Date(startDate); // Copy startDate

                while (currentDate <= endDate) {
                    var dayOfWeek = currentDate.getDay(); // 0 = Minggu, 6 = Sabtu

                    if (userRole === "Technical Support" || userRole === "Driver") {
                        if (dayOfWeek !== 0) daysDifference++; // Exclude Minggu
                    } else if (userRole === "Office Boy") {
                        daysDifference++; // Hitung semua hari
                    } else {
                        if (dayOfWeek !== 6 && dayOfWeek !== 0) daysDifference++; // Exclude Sabtu & Minggu
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                $('#edit_durasi').val(daysDifference > 0 ? daysDifference : 0);
            } else {
                $('#edit_durasi').val(0);
            }
        }
    }


    $('#edit_tipe').on('change', function (e) {
        var selectedType = $(this).val();
        var approval_manager = $('#editForm').data('approval_status');

        // Reset tampilan ke default
        $('#edit_surat_sakit_row').hide();
        $('#btnSimpanEdit').show();
        $('#edit-warning-container').html('');

        // Buka kuncian sementara untuk diproses logika
        $('#edit_tanggal_awal').prop('readonly', false);
        $('#edit_tanggal_akhir').prop('readonly', false);
        $('#edit_alasan').prop('readonly', false);

        if (approval_manager === '1' || approval_manager === '2') {
            if (selectedType === 'Sakit') {
                // JIKA SAKIT: Izinkan ubah tanggal & unggah surat
                $('#edit_surat_sakit_row').show();
                $('#edit_surat_sakit').prop('disabled', false);

                // Aktifkan kembali fungsi hitung durasi saat tanggal diubah
                $('#edit_tanggal_awal').off('change').on('change', hitungDurasiCutiEdit);
                $('#edit_tanggal_akhir').off('change').on('change', hitungDurasiCutiEdit);
                if (e.isTrigger !== true) { hitungDurasiCutiEdit(); }

                $('#edit-warning-container').html(`
                    <div class="alert alert-info py-2" role="alert">
                        <small><i class="bi bi-info-circle"></i> Status sudah diproses. Anda hanya diizinkan mengubah menjadi <strong>Sakit</strong> (Anda dapat menyesuaikan tanggal & melampirkan surat).</small>
                    </div>
                `);
            } else {
                // JIKA BUKAN SAKIT: Kunci total semuanya
                $('#edit_tanggal_awal').prop('readonly', true);
                $('#edit_tanggal_akhir').prop('readonly', true);
                $('#edit_tanggal_awal').off('change');
                $('#edit_alasan').prop('readonly', true);
                $('#btnSimpanEdit').hide();

                $('#edit-warning-container').html(`
                    <div class="alert alert-warning py-2" role="alert">
                        <small><i class="bi bi-info-circle"></i> Status sudah diproses. Anda hanya dapat mengubah data jika mengubah tipe menjadi <strong>Sakit</strong>.</small>
                    </div>
                `);
            }
            return; // Hentikan script di sini
        }

        switch (selectedType) {
            case 'Sakit':
                $('#edit_surat_sakit_row').show();
                $('#edit_tanggal_awal').off('change').on('change', hitungDurasiCutiEdit);
                $('#edit_tanggal_akhir').off('change').on('change', hitungDurasiCutiEdit);
                if (e.isTrigger !== true) { hitungDurasiCutiEdit(); }
                break;
            case 'Cuti':
                $('#edit_tanggal_awal').off('change').on('change', hitungDurasiCutiEdit);
                $('#edit_tanggal_akhir').off('change').on('change', hitungDurasiCutiEdit);
                if (e.isTrigger !== true) { hitungDurasiCutiEdit(); }
                break;
            case 'Izin':
                $('#edit_tanggal_awal').off('change').on('change', hitungDurasiCutiEdit);
                $('#edit_tanggal_akhir').off('change').on('change', hitungDurasiCutiEdit);
                if (e.isTrigger !== true) { $('#edit_durasi').val(''); }
                break;
            case 'Menikah':
                $('#edit_durasi').val(3);
                $('#edit_tanggal_akhir').prop('readonly', true);
                $('#edit_tanggal_awal').off('change').on('change', function () {
                    var startDate = new Date($(this).val());
                    if (isNaN(startDate)) return;
                    var daysCounted = 0, currentDate = new Date(startDate);
                    while (daysCounted < 3) {
                        var dayOfWeek = currentDate.getDay();
                        if (dayOfWeek !== 6 && dayOfWeek !== 0) { daysCounted++; }
                        if (daysCounted < 3) { currentDate.setDate(currentDate.getDate() + 1); }
                    }
                    $('#edit_durasi').val(3);
                    $('#edit_tanggal_akhir').val(currentDate.toISOString().split('T')[0]);
                });
                if (e.isTrigger !== true && $('#edit_tanggal_awal').val()) { $('#edit_tanggal_awal').trigger('change'); }
                break;
            case 'Hamil & Melahirkan':
                $('#edit_durasi').val(90);
                $('#edit_tanggal_akhir').prop('readonly', true);
                $('#edit_tanggal_awal').off('change').on('change', function (eventDate) {
                    var startDateStr = $(this).val();
                    if (!startDateStr) {
                        $('#edit_tanggal_akhir').val('');
                        return;
                    }
                    var startDate = new Date(startDateStr);
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (eventDate.isTrigger !== true && startDate < today) {
                        alert('Tanggal awal tidak boleh kurang dari hari ini!');
                        $('#edit_tanggal_akhir').val('');
                        return;
                    }
                    var endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + 89);
                    $('#edit_durasi').val(90);
                    $('#edit_tanggal_akhir').val(endDate.toISOString().split('T')[0]);
                });
                if (e.isTrigger !== true && $('#edit_tanggal_awal').val()) { $('#edit_tanggal_awal').trigger('change'); }
                break;
            default:
                $('#edit_durasi').val('');
                break;
        }
    });

    // ✅ Fungsi Buka Modal Edit
    function openEditModal(id, tanggal_awal, tanggal_akhir, durasi, alasan, approval_manager, tipe, surat_sakit) {
        $('#editForm').attr('action', '/pengajuancuti/' + id);
        $('#editForm').data('approval_status', approval_manager);

        // FITUR BARU: Menyembunyikan opsi selain tipe saat ini & 'Sakit' jika sudah diproses
        if (approval_manager === '1' || approval_manager === '2') {
            $('#edit_tipe option').each(function() {
                var optVal = $(this).val();
                if (optVal !== 'Sakit' && optVal !== tipe) {
                    $(this).prop('disabled', true).hide(); // Disable & Sembunyikan
                } else {
                    $(this).prop('disabled', false).show(); // Tampilkan tipe asli & Sakit
                }
            });
        } else {
            // Jika belum diproses (0), buka semua opsi
            $('#edit_tipe option').prop('disabled', false).show();
        }

        $('#edit_tipe').val(tipe);
        $('#edit_tanggal_awal').val(tanggal_awal.split(' ')[0]);
        $('#edit_tanggal_akhir').val(tanggal_akhir.split(' ')[0]);
        $('#edit_durasi').val(durasi);
        $('#edit_alasan').val(alasan);
        $('#edit_surat_sakit').val('');

        if (surat_sakit && surat_sakit !== 'null' && surat_sakit !== '') {
            $('#current_surat_sakit_container').show();
            $('#link_current_surat_sakit').attr('href', '{{ url('storage/') }}/' + surat_sakit);
        } else {
            $('#current_surat_sakit_container').hide();
        }

        // Buka kuncian dropdown agar user bisa mengubahnya menjadi Sakit
        $('#edit_tipe').css({'pointer-events': 'auto', 'background-color': ''});

        // Pancing event 'change'
        $('#edit_tipe').trigger('change');

        $('#editModal').modal('show');
    }

</script>
@endpush
@endsection
