@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <div class="container content-wrapper mt-4">
        <div class="content-card position-relative">
            
            <div class="skeleton-overlay" id="skeletonDataTarget" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 10; background: #ffffff; border-radius: 16px; overflow: hidden;">
                <div class="card-body">
                    <div class="d-flex gap-3 mb-4">
                        <div class="skeleton" style="width: 150px; height: 42px; border-radius: 10px;"></div>
                    </div>
                    <div class="skeleton mb-4" style="width: 100%; height: 60px; border-radius: 12px;"></div>
                    <div class="table-responsive">
                        <table class="table modern-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="5%"><div class="skeleton" style="width: 30px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 120px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 80px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 60px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 80px; height: 14px;"></div></th>
                                    <th width="10%"><div class="skeleton" style="width: 50px; height: 14px;"></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 10; $i++)
                                <tr class="skeleton-row-table">
                                    <td><div class="skeleton" style="width: 20px; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 80%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 60%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 50%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 70%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 60px; height: 32px; border-radius: 6px;"></div></td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="action-bar">
                    <button class="btn-action success" data-bs-toggle="modal" data-bs-target="#modalImportData">
                        <i class="fas fa-file-import"></i> Import Data
                    </button>
                </div>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong class="text-dark">Petunjuk Import:</strong> Download template
                        <a href="{{ route('kpi.dataTarget.template') }}">disini</a>.
                        Format: <code>asistant_route</code>, <code>jangka_target</code>, <code>tipe_target</code>,
                        <code>nilai_target</code>
                    </div>
                </div>

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
                                            <button class="btn-table-action edit btn-edit-target" 
                                                    data-item="{{ json_encode($item) }}" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-table-action delete btn-delete-target" 
                                                    data-id="{{ $item->id }}" 
                                                    data-name="{{ $item->asistant_route }}" 
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
                                <select name="tipe_target" id="edit_tipe_target" class="form-select" required>
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

    <script>
        window.KPI_CONFIG = {
            importRoute: "{{ route('kpi.dataTarget.import') }}",
            updateRoute: "{{ route('kpi.dataTarget.update', ['id' => 'REPLACE_ID']) }}",
            deleteRoute: "{{ route('kpi.dataTarget.destroy', ['id' => 'REPLACE_ID']) }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>

    <script defer src="{{ asset('assets/js/kpi/dataTarget.js') }}"></script>
@endsection