@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/penilaian360.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container content-wrapper mt-4">
        <div class="content-card position-relative">
            
            <div class="skeleton-overlay" id="skeletonPenilaian">
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><div class="skeleton" style="width: 100%; height: 42px; border-radius: 10px;"></div></div>
                        <div class="col-md-6"><div class="skeleton" style="width: 100%; height: 42px; border-radius: 10px;"></div></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table modern-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="5%"><div class="skeleton" style="width: 30px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 80px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 100px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 80px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 80px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 80px; height: 14px;"></div></th>
                                    <th><div class="skeleton" style="width: 50px; height: 14px;"></div></th>
                                    <th width="20%"><div class="skeleton" style="width: 60px; height: 14px;"></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 8; $i++)
                                <tr class="skeleton-row-table">
                                    <td><div class="skeleton" style="width: 20px; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 36px; height: 36px; border-radius: 8px;"></div></td>
                                    <td><div class="skeleton" style="width: 80%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 60%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 70%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 50%; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 40px; height: 14px;"></div></td>
                                    <td><div class="skeleton" style="width: 120px; height: 36px;"></div></td>
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- AKHIR SKELETON OVERLAY --}}

            <div class="filter-card">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fa-solid fa-building text-primary me-2"></i>Pilih Divisi</label>
                        <select name="divisiSelectUtama" id="divisiSelectUtama" class="form-select">
                            <option value="" selected>Semua Divisi</option>
                            @foreach ($divisi as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="fa-solid fa-calendar text-primary me-2"></i>Pilih Tahun</label>
                        <select name="tahunSelectUtama" id="tahunSelectUtama" class="form-select"></select>
                    </div>
                    <input type="hidden" name="jenis_form" id="jenis_form" value="{{ $tipe }}">
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_karyawan" class="table modern-table align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Evaluator</th>
                                <th>Yang Dinilai</th>
                                <th>Divisi</th>
                                <th>Tanggal</th>
                                <th>Kode Form</th>
                                <th>Tahun</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Share Evaluator --}}
    <div class="modal fade" id="shareEvaluatorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('penilaian.shareForm') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="page-title-icon" style="width: 36px; height: 36px; font-size: 1rem; margin-right: 0.5rem;"><i class="fa-solid fa-share-nodes"></i></span>
                            Bagikan Formulir Penilaian
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modal-body-content"></div>
                        <div id="content_select_input"></div>
                        <input type="hidden" name="jenis_form" value="{{ $tipe }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-action primary"><i class="fa-solid fa-paper-plane"></i> Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Daftar Evaluator --}}
    <div class="modal fade" id="evaluatorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="page-title-icon" style="width: 36px; height: 36px; font-size: 1rem; margin-right: 0.5rem;"><i class="fa-solid fa-users"></i></span>
                        Daftar Evaluator
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="evaluatorModalContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <script>Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", confirmButtonColor: '#6366f1' });</script>
    @endif
    @if (session('error'))
        <script>Swal.fire({ icon: 'error', title: 'Gagal', text: "{{ session('error') }}", confirmButtonColor: '#ef4444' });</script>
    @endif
    @if ($errors->any())
        <script>Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: `{!! implode('<br>', $errors->all()) !!}`, confirmButtonColor: '#ef4444' });</script>
    @endif

    <script>
        window.PENILAIAN_CONFIG = {
            tipe: "{{ $tipe }}",
            getDataRoute: "{{ route('penilaian.get.data') }}",
            shareFormRoute: "{{ route('penilaian.shareForm') }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    <script src="{{ asset('assets/js/penilaian360/index.js') }}"></script>
@endsection