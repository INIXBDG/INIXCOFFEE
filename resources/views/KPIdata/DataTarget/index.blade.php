@extends('databasekpi.berandaKPI')

@section('contentKPI')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-file-document"></i>
                </span> KPI / Konfigurasi Data Target
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <span></span> Buat Target
                        <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Halaman ini digunakan untuk membuat target perdivisi dan dilakukan oleh koordinator/manager dari divisi tersebut.">
                        </i>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="container-fluid py-4">
                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class=" py-3 d-flex justify-content-between align-items-center">
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalImportData">
                                            <i class="fas fa-file-import me-1"></i> Import Data
                                        </button>
                                    </div>
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Petunjuk Import:</strong> Download template <a
                                            href="{{ route('kpi.dataTarget.template') }}" class="alert-link">disini</a>.
                                        Format: asistant_route, jangka_target, tipe_target, nilai_target
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="tableDataTarget">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Assistant Route</th>
                                                    <th>Jangka Target</th>
                                                    <th>Tipe Target</th>
                                                    <th>Nilai Target</th>
                                                    <th width="15%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($dataTargets as $index => $item)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td class="fw-bold">{{ $item->asistant_route }}</td>
                                                        <td>
                                                            <span
                                                                class="badge bg-info text-dark">{{ $item->jangka_target }}</span>
                                                        </td>
                                                        <td>
                                                            @if ($item->tipe_target === 'angka')
                                                                <span class="badge bg-secondary">Angka</span>
                                                            @elseif($item->tipe_target === 'rupiah')
                                                                <span class="badge bg-success">Rupiah</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark">Persen</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->tipe_target === 'rupiah')
                                                                {{ number_format($item->nilai_target, 0, ',', '.') }}
                                                            @elseif($item->tipe_target === 'persen')
                                                                {{ $item->nilai_target }}%
                                                            @else
                                                                {{ number_format($item->nilai_target, 0, ',', '.') }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary"
                                                                    onclick="openModalEdit({{ json_encode($item) }})">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger"
                                                                    onclick="confirmDelete({{ $item->id }}, '{{ $item->asistant_route }}')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4 text-muted">
                                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                            Belum ada data target yang dikonfigurasi
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImportData" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formImportData" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import Data Target</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih File Excel/CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file_import" id="file_import" class="form-control"
                                accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Format: .xlsx, .xls, atau .csv</small>
                        </div>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Catatan:</strong> Data dengan asistant_route yang sudah ada akan diupdate, data baru
                            akan ditambahkan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload me-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditDataTarget" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formDataTargetEdit">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editDataTargetId" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data Target</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Assistant Route</label>
                            <input type="text" readonly id="edit_asistant_route" class="form-control" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jangka Target <span class="text-danger">*</span></label>
                                <select name="jangka_target" id="edit_jangka_target" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Tahunan">Tahunan</option>
                                    <option value="Bulanan">Bulanan</option>
                                    <option value="Kuartalan">Kuartalan</option>
                                    <option value="Mingguan">Mingguan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipe Target <span class="text-danger">*</span></label>
                                <select name="tipe_target" id="edit_tipe_target" class="form-select" required
                                    onchange="updateEditNilaiPlaceholder()">
                                    <option value="">-- Pilih --</option>
                                    <option value="angka">Angka</option>
                                    <option value="rupiah">Rupiah</option>
                                    <option value="persen">Persen (%)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nilai Target <span class="text-danger">*</span></label>
                            <input type="text" name="nilai_target" id="edit_nilai_target" class="form-control"
                                required placeholder="Masukkan nilai target">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $('#tableDataTarget').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            ordering: true,
            searching: true,
            responsive: true,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                    next: "Next",
                    previous: "Prev"
                },
                zeroRecords: "Data tidak ditemukan"
            }
        });
        $(document).ready(function() {

            $('#formImportData').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                Swal.fire({
                    title: 'Memproses Import',
                    text: 'Sedang mengimport data, silakan tunggu...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '{{ route('kpi.dataTarget.import') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success').then(() => location
                            .reload());
                    },
                    error: function(xhr) {
                        let msg = 'Gagal mengimport data';
                        if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            $('#formDataTargetEdit').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editDataTargetId').val();
                const formData = $(this).serialize();

                $.ajax({
                    url: `/kpi-data/data-target/${id}`,
                    type: 'PUT',
                    data: formData,
                    success: function(res) {
                        $('#modalEditDataTarget').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success').then(() => location
                            .reload());
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan';
                        if (xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        });

        function openModalEdit(item) {
            $('#editDataTargetId').val(item.id);
            $('#edit_asistant_route').val(item.asistant_route);
            $('#edit_jangka_target').val(item.jangka_target);
            $('#edit_tipe_target').val(item.tipe_target);
            $('#edit_nilai_target').val(item.nilai_target);
            updateEditNilaiPlaceholder();
            $('#modalEditDataTarget').modal('show');
        }

        function updateEditNilaiPlaceholder() {
            const tipe = $('#edit_tipe_target').val();
            const input = $('#edit_nilai_target');
            if (tipe === 'rupiah') {
                input.attr('placeholder', 'Contoh: 10000000');
            } else if (tipe === 'persen') {
                input.attr('placeholder', 'Contoh: 85');
            } else {
                input.attr('placeholder', 'Contoh: 100');
            }
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Data Target?',
                text: `Anda akan menghapus konfigurasi "${name}". Tindakan ini tidak dapat diurungkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/kpi-data/data-target/${id}`,
                        type: 'DELETE',
                        success: function(res) {
                            Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Gagal menghapus data';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        }

        $('#edit_nilai_target').on('input', function() {
            const tipe = $('#edit_tipe_target').val();
            let val = $(this).val().replace(/\D/g, '');
            if (!val) {
                $(this).val('');
                return;
            }
            $(this).val(new Intl.NumberFormat('id-ID').format(parseInt(val)));
        });
    </script>
@endsection
