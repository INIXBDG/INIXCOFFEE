@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="d-flex justify-content-end mb-2">
                <a href="{{ route('registry.create') }}" class="btn btn-md btn-primary mx-4" data-toggle="tooltip" title="Tambah Tugas Baru">
                    <i class="fas fa-plus me-1"></i> Tambah Tugas Baru
                </a>
            </div>

            <div class="modal fade" id="startTaskModal" tabindex="-1" aria-labelledby="startTaskModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="startTaskForm" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="startTaskModalLabel">Mulai Tugas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="input_tanggal_mulai" class="form-label">Tanggal & Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="tanggal_mulai" id="input_tanggal_mulai" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="finishTaskModal" tabindex="-1" aria-labelledby="finishTaskModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="finishTaskForm" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="finishTaskModalLabel">Tandai Selesai</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="input_tanggal_akhir" class="form-label">Tanggal & Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="tanggal_akhir" id="input_tanggal_akhir" required>
                                </div>
                                <div class="mb-3">
                                    <label for="input_kesulitan" class="form-label">Tingkat Kesulitan (Untuk Tiket)</label>
                                    <select name="kesulitan" id="input_kesulitan" class="form-select">
                                        <option value="" selected disabled>Pilih Tingkat Kesulitan</option>
                                        <option value="Major">Major</option>
                                        <option value="Moderate">Moderate</option>
                                        <option value="Minor">Minor</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="input_keterangan" class="form-label">Keterangan Selesai (Untuk Tiket)</label>
                                    <textarea class="form-control" id="input_keterangan" name="keterangan" rows="2" placeholder="Masukkan keterangan selesai..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="input_penanganan" class="form-label">Update Penanganan (Untuk Tiket)</label>
                                    <textarea class="form-control" id="input_penanganan" name="penanganan" rows="2" placeholder="Masukkan update penanganan..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success">Simpan & Selesaikan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold" id="detailModalLabel">
                                <i class="fas fa-clipboard-list me-2"></i> Detail Registry Feature
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.8rem;">Nama Tugas</h6>
                                <h4 class="text-dark fw-bold" id="detailTugas"></h4>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light h-100 border-start border-4 border-danger">
                                        <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-exclamation-circle me-1"></i> Fakta Saat Ini</h6>
                                        <p class="mb-0 text-dark" id="detailFakta" style="white-space: pre-wrap; font-size: 0.95rem;"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light h-100 border-start border-4 border-success">
                                        <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-bullseye me-1"></i> Harapan Sistem</h6>
                                        <p class="mb-0 text-dark" id="detailHarapan" style="white-space: pre-wrap; font-size: 0.95rem;"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="p-3 border rounded bg-white">
                                    <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-sticky-note me-1"></i> Catatan</h6>
                                    <p class="mb-0 text-dark" id="detailCatatan" style="white-space: pre-wrap; font-size: 0.95rem;"></p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-white h-100 text-center shadow-sm">
                                        <h6 class="fw-bold text-muted mb-2">Durasi Pengerjaan Aktual</h6>
                                        <h5 class="mb-0 text-primary fw-bold" id="detailDurasi"></h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-white h-100 text-center shadow-sm">
                                        <h6 class="fw-bold text-muted mb-2">Analisis Gap Waktu</h6>
                                        <h5 class="mb-0" id="detailGap"></h5>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup Detail</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Registry Feature') }}</h3>
                    <table class="table table-striped text-nowrap" id="registryTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Tugas</th>
                                <th>Fitur</th>
                                <th>Tipe</th>
                                <th>Pemilik</th>
                                <th>Pengerjaan</th>
                                <th>Waktu Perkiraan</th>
                                <th>Status</th>
                                <th>Mulai</th>
                                <th>Akhir</th>
                                <th>Aksi</th>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    $(document).ready(function() {
        var csrfToken = '{{ csrf_token() }}';

        $('#registryTable').DataTable({
            "ajax": {
                "url": "{{ route('registry.data') }}",
                "type": "GET"
            },
            "columns": [
                {
                    "data": "ticket_id",
                    "render": function(data) {
                        return data ? data : '-';
                    }
                },
                { "data": "tugas" },
                { "data": "fitur" },
                { "data": "tipe" },
                { "data": "pemilik" },
                {
                    "data": "pengerja",
                    "render": function(data) {
                        return data && data.karyawan ? data.karyawan.kode_karyawan : '-';
                    }
                },
                {
                    "data": "waktu_perkiraan",
                    "render": function(data) {
                        return data ? data + ' Menit' : '-';
                    }
                },
                {
                    "data": "status",
                    "render": function(data) {
                        if (data === 'Selesai') {
                            return '<span class="badge bg-success">' + data + '</span>';
                        } else if (data === 'Dalam proses') {
                            return '<span class="badge bg-warning text-dark">' + data + '</span>';
                        } else if (data === 'Belum dimulai') {
                            return '<span class="badge bg-secondary">' + data + '</span>';
                        } else {
                            return '<span class="badge bg-primary">' + data + '</span>';
                        }
                    }
                },
                {
                    "data": "tanggal_mulai",
                    "render": function(data) {
                        return data ? moment(data).format('DD/MM/YYYY HH:mm') : '-';
                    }
                },
                {
                    "data": "tanggal_akhir",
                    "render": function(data) {
                        return data ? moment(data).format('DD/MM/YYYY HH:mm') : '-';
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = '<div class="dropdown">';
                        actions += '<button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>';
                        actions += '<ul class="dropdown-menu shadow">';

                        actions += '<li><button type="button" class="dropdown-item btn-detail"><i class="fas fa-eye me-2"></i> Detail</button></li>';
                        actions += '<li><hr class="dropdown-divider"></li>';

                        if (row.tanggal_mulai === null) {
                            actions += '<li>';
                            actions += '<button type="button" class="dropdown-item text-primary" onclick="openStartModal(' + row.id + ')"><i class="fas fa-play me-2"></i> Mulai Tugas</button>';
                            actions += '</li>';
                        }

                        if (row.tanggal_mulai !== null && row.tanggal_akhir === null) {
                            actions += '<li>';
                            actions += '<button type="button" class="dropdown-item text-success" onclick="openFinishModal(' + row.id + ')"><i class="fas fa-check-circle me-2"></i> Tandai Selesai</button>';
                            actions += '</li>';
                        }

                        if (row.tanggal_mulai === null || (row.tanggal_mulai !== null && row.tanggal_akhir === null)) {
                            actions += '<li><hr class="dropdown-divider"></li>';
                        }

                        actions += '<li><a class="dropdown-item" href="/registry/' + row.id + '/edit"><i class="fas fa-pencil-alt me-2"></i> Edit</a></li>';

                        actions += '<li><hr class="dropdown-divider"></li>';
                        actions += '<li>';
                        actions += '<form action="/registry/' + row.id + '" method="POST" onsubmit="return confirm(\'Yakin hapus data ini?\');">';
                        actions += '<input type="hidden" name="_token" value="' + csrfToken + '">';
                        actions += '<input type="hidden" name="_method" value="DELETE">';
                        actions += '<button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash-alt me-2"></i> Hapus</button>';
                        actions += '</form>';
                        actions += '</li>';

                        actions += '</ul></div>';
                        return actions;
                    }
                }
            ],
            "order": [],
            "language": {
                "emptyTable": "Belum ada data tugas."
            }
        });

        $('#registryTable tbody').on('click', '.btn-detail', function () {
            var table = $('#registryTable').DataTable();
            var data = table.row($(this).parents('tr')).data();

            $('#detailTugas').text(data.tugas || '-');
            $('#detailFakta').text(data.fakta || '-');
            $('#detailHarapan').text(data.harapan || '-');
            $('#detailCatatan').text(data.catatan || '-');

            $('#detailDurasi').text(data.durasi_human || '-');

            if (data.tanggal_mulai && data.tanggal_akhir && data.waktu_perkiraan) {
                var waktuMulai = moment(data.tanggal_mulai);
                var waktuAkhir = moment(data.tanggal_akhir);

                var durasiAktual = waktuAkhir.diff(waktuMulai, 'minutes');
                var waktuPerkiraan = parseInt(data.waktu_perkiraan);

                var gap = durasiAktual - waktuPerkiraan;

                if (gap > 0) {
                    $('#detailGap').html('<span class="text-danger fw-bold">+' + gap + ' Menit (Over)</span>');
                } else if (gap < 0) {
                    $('#detailGap').html('<span class="text-success fw-bold">' + gap + ' Menit (Under)</span>');
                } else {
                    $('#detailGap').html('<span class="text-secondary fw-bold">0 Menit (Tepat)</span>');
                }
            } else {
                $('#detailGap').text('-');
            }

            $('#detailModal').modal('show');
        });
    });

    function openStartModal(id) {
        $('#startTaskForm').attr('action', '/registry/' + id + '/start');
        var now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#input_tanggal_mulai').val(now.toISOString().slice(0,16));
        $('#startTaskModal').modal('show');
    }

    function openFinishModal(id) {
        $('#finishTaskForm').attr('action', '/registry/' + id + '/finish');
        var now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#input_tanggal_akhir').val(now.toISOString().slice(0,16));
        $('#finishTaskModal').modal('show');
    }

</script>
@endsection
