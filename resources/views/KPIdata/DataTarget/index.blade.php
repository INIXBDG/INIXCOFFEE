@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    {{-- ===== STYLE KHUSUS HALAMAN INI ===== --}}
    <style>
        /* Page Header */
        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .page-title-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(99, 102, 241, .1), rgba(139, 92, 246, .1));
            color: #6366f1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Content Card */
        .content-card {
            background: #fff;
            border-radius: 16px;
            border: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
        }

        .content-card .card-body {
            padding: 1.5rem;
        }

        /* Action Bar */
        .action-bar {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            align-items: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem 1.1rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            border: 0;
            transition: all .2s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-action i {
            font-size: .9rem;
        }

        .btn-action.success {
            background: linear-gradient(135deg, #34d399, #059669);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, .25);
        }

        .btn-action.success:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, .35);
            color: #fff;
        }

        .btn-action.primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, .25);
        }

        .btn-action.primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, .35);
            color: #fff;
        }

        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, rgba(99, 102, 241, .05), rgba(139, 92, 246, .05));
            border: 1px solid rgba(99, 102, 241, .15);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: .75rem;
        }

        .info-box i {
            color: #6366f1;
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .info-box a {
            color: #6366f1;
            font-weight: 600;
            text-decoration: none;
        }

        .info-box a:hover {
            text-decoration: underline;
        }

        .info-box.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, .05), rgba(245, 158, 11, .02));
            border-color: rgba(245, 158, 11, .2);
        }

        .info-box.warning i {
            color: #f59e0b;
        }

        /* Modern Table */
        .modern-table {
            border: 0 !important;
            border-radius: 12px !important;
            overflow: hidden;
            width: 100% !important;
        }

        .modern-table thead th {
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            font-weight: 600;
            color: #475569;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 1rem !important;
        }

        .modern-table tbody td {
            padding: 1rem !important;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            font-size: .9rem;
        }

        .modern-table tbody tr {
            transition: background .15s ease;
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
        }

        .modern-table tbody tr:last-child td {
            border-bottom: 0;
        }

        /* Badges */
        .badge-modern {
            font-size: .75rem;
            font-weight: 600;
            padding: .35rem .65rem;
            border-radius: 6px;
        }

        .badge-angka {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-rupiah {
            background: rgba(16, 185, 129, .1);
            color: #059669;
        }

        .badge-persen {
            background: rgba(245, 158, 11, .1);
            color: #d97706;
        }

        .badge-jangka {
            background: rgba(99, 102, 241, .1);
            color: #6366f1;
        }

        /* Action Buttons in Table */
        .btn-table-action {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            transition: all .2s ease;
            cursor: pointer;
        }

        .btn-table-action:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-table-action.edit:hover {
            color: #6366f1;
            border-color: #6366f1;
            background: rgba(99, 102, 241, .05);
        }

        .btn-table-action.delete:hover {
            color: #ef4444;
            border-color: #ef4444;
            background: rgba(239, 68, 68, .05);
        }

        /* DataTables Customization */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 1rem 0;
            font-size: .9rem;
            color: #475569;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: .4rem .8rem;
            transition: all .2s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
            outline: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
            border: 1px solid transparent !important;
            padding: .3rem .7rem !important;
            margin: 0 2px;
            color: #475569 !important;
            background: transparent !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #6366f1 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 10px rgba(99, 102, 241, .25);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Form Controls */
        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: .6rem 1rem;
            transition: all .2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: .875rem;
            margin-bottom: .5rem;
        }

        .auto-field {
            background: #f8fafc !important;
            border: 1px dashed #cbd5e1 !important;
            color: #475569;
        }
    </style>

    <div class="container content-wrapper mt-4">

        <div class="content-card">
            <div class="card-body">
                {{-- Action Bar --}}
                <div class="action-bar">
                    <button class="btn-action success" data-bs-toggle="modal" data-bs-target="#modalImportData">
                        <i class="fas fa-file-import"></i> Import Data
                    </button>
                </div>

                {{-- Info Box --}}
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong class="text-dark">Petunjuk Import:</strong> Download template
                        <a href="{{ route('kpi.dataTarget.template') }}">disini</a>.
                        Format: <code>asistant_route</code>, <code>jangka_target</code>, <code>tipe_target</code>,
                        <code>nilai_target</code>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table modern-table align-middle" id="tableDataTarget">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Assistant Route</th>
                                <th>Jangka Target</th>
                                <th>Tipe Target</th>
                                <th>Nilai Target</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataTargets as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold text-dark">{{ $item->asistant_route }}</td>
                                    <td><span class="badge-modern badge-jangka">{{ $item->jangka_target }}</span></td>
                                    <td>
                                        @if ($item->tipe_target === 'angka')
                                            <span class="badge-modern badge-angka">Angka</span>
                                        @elseif($item->tipe_target === 'rupiah')
                                            <span class="badge-modern badge-rupiah">Rupiah</span>
                                        @else
                                            <span class="badge-modern badge-persen">Persen</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">
                                        @if ($item->tipe_target === 'rupiah')
                                            Rp. {{ number_format($item->nilai_target, 0, ',', '.') }}
                                        @elseif($item->tipe_target === 'persen')
                                            {{ $item->nilai_target }}%
                                        @elseif ($item->tipe_target === 'angka')
                                            {{ $item->nilai_target }}
                                        @else
                                            {{ number_format($item->nilai_target, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn-table-action edit"
                                                onclick="openModalEdit({{ json_encode($item) }})" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-table-action delete"
                                                onclick="confirmDelete({{ $item->id }}, '{{ $item->asistant_route }}')"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL IMPORT DATA ===== --}}
    <div class="modal fade" id="modalImportData" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formImportData" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="title-icon"><i class="fa-solid fa-file-import"></i></span>
                            Import Data Target
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih File Excel/CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file_import" id="file_import" class="form-control"
                                accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Format: .xlsx, .xls, atau .csv</small>
                        </div>
                        <div class="info-box warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong class="text-dark">Catatan:</strong> Data dengan <code>asistant_route</code> yang
                                sudah ada akan diupdate, data baru akan ditambahkan.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-action success">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== MODAL EDIT DATA TARGET ===== --}}
    <div class="modal fade" id="modalEditDataTarget" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formDataTargetEdit">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editDataTargetId" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="title-icon"><i class="fa-solid fa-pen-to-square"></i></span>
                            Edit Data Target
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Assistant Route</label>
                            <input type="text" readonly id="edit_asistant_route" class="form-control auto-field">
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
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-action primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== SCRIPTS ===== --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#tableDataTarget').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                        orderable: false,
                        targets: [5]
                    } // Disable sorting on Action column
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data yang tersedia",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Berikutnya"
                    },
                    emptyTable: "Belum ada data target yang dikonfigurasi",
                    zeroRecords: "Tidak ditemukan data yang cocok"
                }
            });

            // Handle Import Form Submission
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

            // Handle Edit Form Submission
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

        // Open Edit Modal
        function openModalEdit(item) {
            $('#editDataTargetId').val(item.id);
            $('#edit_asistant_route').val(item.asistant_route);
            $('#edit_jangka_target').val(item.jangka_target);
            $('#edit_tipe_target').val(item.tipe_target);
            $('#edit_nilai_target').val(item.nilai_target);
            updateEditNilaiPlaceholder();
            $('#modalEditDataTarget').modal('show');
        }

        // Update Placeholder based on Target Type
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

        // Confirm Delete
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

        // Format Number Input for Rupiah
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
