@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/penilaian360.css') }}">

    <script>
        window.penilaianConfig = {
            routes: {
                getForm: "{{ route('penilaian.form.get') }}",
                getEvaluators: "{{ route('penilaian.form.evaluators') }}",
                updateDivisiEvaluator: "{{ route('penilaian.form.updateDivisiEvaluator') }}"
            },
            csrfToken: "{{ csrf_token() }}"
        };
    </script>

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}",
                confirmButtonColor: '#ef4444'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#ef4444'
            });
        </script>
    @endif

    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: transparent; box-shadow: none; border: none;">
                <div class="d-flex justify-content-center">
                    <div class="dataform-penilaian-loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container dataform-penilaian-content-wrapper">
        <div class="dataform-penilaian-content-card">
            <div class="card-body">
                
                <div id="dataform-penilaian-skeleton-container">
                    <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-title"></div>
                    <div class="dataform-penilaian-filter-bar">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-filter-label"></div>
                                <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-filter"></div>
                            </div>
                        </div>
                    </div>
                    <div id="dataform-penilaian-space-reserver" style="height: 50px; width: 100%;"></div>
                    <div class="table-responsive">
                        <table class="table dataform-penilaian-modern-table align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-th"></div></th>
                                    <th><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-th"></div></th>
                                    <th><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-th"></div></th>
                                    <th><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-th"></div></th>
                                    <th width="10%"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-th"></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                                <tr class="dataform-penilaian-skeleton-row"><td colspan="5"><div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-td"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="dataform-penilaian-skeleton-controls">
                        <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-controls-left"></div>
                        <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-controls-right"></div>
                    </div>
                    <div class="dataform-penilaian-skeleton-pagination">
                        <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-page-btn"></div>
                        <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-page-btn"></div>
                        <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-page-btn"></div>
                        <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-page-btn"></div>
                        <div class="dataform-penilaian-skeleton-cell dataform-penilaian-skeleton-page-btn"></div>
                    </div>
                </div>

                <div id="dataform-penilaian-real-container" style="display: none;">
                    <h5 class="fw-bold text-dark mb-4">
                        <i class="fa-solid fa-list-check text-primary me-2"></i>
                        Semua Form Penilaian
                    </h5>

                    <div class="dataform-penilaian-filter-bar">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="tahun" class="form-label">
                                    <i class="fa-solid fa-calendar text-primary me-1"></i> Tahun
                                </label>
                                <select class="form-select" name="tahun" id="tahun">
                                    <option value="">Pilih Tahun</option>
                                    @for ($i = now()->year; $i >= 2020; $i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table dataform-penilaian-modern-table align-middle" id="table_penilaian" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Kode Form</th>
                                    <th>Evaluated</th>
                                    <th>Tahun</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="dataform-penilaian-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEvaluated" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-users me-2"></i>
                        Daftar Evaluated
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="evaluatedContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEvaluator" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-user-check text-primary me-2"></i>
                        Daftar Evaluator
                        <small class="text-muted ms-2" id="modalKodeFormLabel"></small>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="evaluatorLoading" class="text-center py-5 d-none">
                        <div class="dataform-penilaian-loading-spinner"></div>
                        <p class="mt-2 text-muted">Memuat data evaluator...</p>
                    </div>

                    <div id="evaluatorContent" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Evaluator</th>
                                        <th>Jabatan</th>
                                        <th>Jenis Penilaian</th>
                                        <th>Divisi Saat Ini</th>
                                        <th width="220">Ubah Divisi</th>
                                        <th width="80">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyEvaluator"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="evaluatorEmpty" class="text-center py-5 d-none">
                        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada evaluator untuk form ini</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/penilaian360/dataFormPenilaian.js') }}"></script>
@endsection