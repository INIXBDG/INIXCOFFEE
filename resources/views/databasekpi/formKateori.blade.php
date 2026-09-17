@extends('layouts_kpi.app')

@section('kpi_contents')
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/penilaian360.css') }}">

    @if (session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", confirmButtonColor: '#6366f1' });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: "{{ session('error') }}", confirmButtonColor: '#f59e0b' });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({ icon: 'warning', title: 'Validasi Gagal', html: `{!! implode('<br>', $errors->all()) !!}`, confirmButtonColor: '#f59e0b' });
        </script>
    @endif

    <script>
        window.formkategoriConfig = {
            routes: {
                list: "{{ route('template.list') }}",
                load: "{{ route('template.load', ':kode') }}",
                store: "{{ route('ketegori.kpi.store') }}"
            }
        };
        window.formkategoriData = @json($data);
    </script>

    <div class="container content-wrapper mt-4">
        <a href="{{ route('ketegoriKPI.get') }}" class="btn btn-light mb-3">
            <i class="mdi mdi-arrow-left"></i> Penilaian
        </a>

        <div class="formkategori-content-card">
            <div class="card-body">
                <h4 class="fw-bold text-dark mb-4 text-center">
                    <i class="fa-solid fa-file-circle-plus text-primary me-2"></i>
                    {{ __('Kategori Baru') }}
                </h4>

                <form id="formkategori-main-form" action="{{ route('ketegori.kpi.store') }}" method="POST">
                    @csrf
                    @php
                        $divisiList = $data->pluck('divisi')->unique();
                    @endphp

                    <input type="hidden" name="template_quartal" id="template_quartal">
                    <input type="hidden" name="template_tahun" id="template_tahun">
                    <input type="hidden" name="template_nama_evaluator" id="template_nama_evaluator">
                    <input type="hidden" name="template_tanggal" id="template_tanggal">

                    <div class="mb-4">
                        <h5 class="formkategori-section-title">
                            <i class="fa-solid fa-layer-group"></i> Jenis Form
                        </h5>
                        <div class="formkategori-karyawan-block">
                            <button type="button" class="formkategori-btn-remove formkategori-btn-remove-karyawan" title="Hapus">
                                <i class="mdi mdi-trash-can"></i>
                            </button>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Form <span class="formkategori-text-required">*</span></label>
                                    <select name="jenis_form" id="formkategori-jenis-form" class="form-select formkategori-divisi-select" required>
                                        <option selected disabled>Pilih Jenis Form</option>
                                        <option value="Rutin">Penilaian Rutin</option>
                                        <option value="Kontrak">Penilaian Kontrak</option>
                                        <option value="Probation">Penilaian Probation</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="formkategori-section-title">
                            <i class="fa-solid fa-copy"></i> Pilih Template Penilaian
                            <span class="formkategori-badge-info">Opsional</span>
                        </h5>
                        <div class="formkategori-template-wrapper">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-9">
                                    <label class="form-label">Pilih Berdasarkan Nama</label>
                                    <div class="formkategori-template-dropdown" id="formkategori-template-dropdown">
                                        <button type="button" class="form-select text-start formkategori-template-toggle" id="formkategori-template-toggle">
                                            <span id="formkategori-template-toggle-text">Pilih Nama...</span>
                                            <i class="mdi mdi-chevron-down formkategori-template-toggle-caret"></i>
                                        </button>
                                        <div class="formkategori-template-panel d-none" id="formkategori-template-panel">
                                            <div class="formkategori-template-panel-search">
                                                <input type="text" class="form-control form-control-sm" id="formkategori-template-search" placeholder="Cari nama...">
                                            </div>
                                            <div class="formkategori-template-panel-list" id="formkategori-template-panel-list">
                                                <div class="formkategori-template-panel-empty text-muted small p-2">Memuat daftar nama...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="formkategori-load-template-btn" class="btn btn-primary w-100">
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="formkategori-form-karyawan" class="mb-4">
                        <h5 class="formkategori-section-title">
                            <i class="fa-solid fa-users"></i> Daftar Yang Dinilai
                        </h5>
                        <div class="text-end mb-3">
                            <button type="button" class="btn btn-success" id="formkategori-add-karyawan-block">
                                <i class="fa-solid fa-plus"></i> Tambah Yang Dinilai
                            </button>
                        </div>
                        <div class="formkategori-karyawan-block">
                            <button type="button" class="formkategori-btn-remove formkategori-btn-remove-karyawan" title="Hapus">
                                <i class="mdi mdi-trash-can"></i>
                            </button>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Divisi <span class="formkategori-text-required">*</span></label>
                                    <select name="divisi[]" class="form-select formkategori-divisi-select" required>
                                        <option selected disabled>Pilih Divisi</option>
                                        @foreach ($divisiList as $div)
                                            <option value="{{ $div }}">{{ $div }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Yang Dinilai <span class="formkategori-text-required">*</span></label>
                                    <select name="nama_karyawan[]" class="form-select formkategori-karyawan-select" required>
                                        <option selected disabled>Pilih Karyawan</option>
                                    </select>
                                    <input type="hidden" name="id_karyawan[]" class="formkategori-id-karyawan">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="formkategori-kriteria-container">
                        <h5 class="formkategori-section-title">
                            <i class="fa-solid fa-list-check"></i> Kriteria Penilaian
                        </h5>
                        <div class="formkategori-kriteria-block" data-kriteria-index="0">
                            <button type="button" class="formkategori-btn-remove formkategori-btn-remove-kriteria" title="Hapus Kriteria">
                                <i class="mdi mdi-trash-can"></i>
                            </button>

                            <div class="mb-3">
                                <label class="form-label">Nama Kriteria <span class="formkategori-text-required">*</span></label>
                                <input type="text" name="kriteria[0][nama_penilaian]" class="form-control formkategori-nama-penilaian" placeholder="Masukan nama kriteria..." maxlength="250" title="Hanya huruf dan spasi, maksimal 250 karakter">
                                <small class="form-text text-muted">Maksimal 250 karakter.</small>
                            </div>

                            <div class="formkategori-wrapper-sub">
                                <div class="formkategori-group-item" data-sub-kriteria-index="0">
                                    <button type="button" class="formkategori-btn-remove formkategori-btn-remove-sub" title="Hapus Sub Kriteria">
                                        <i class="mdi mdi-trash-can"></i>
                                    </button>

                                    <div class="formkategori-sub-header">
                                        <span class="badge">Sub Kriteria</span>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Judul Sub Kriteria <span class="formkategori-text-required">*</span></label>
                                            <input type="text" name="kriteria[0][sub_kriteria][0][judul_kategori]" maxlength="250" class="form-control" placeholder="Masukan sub kriteria..." required title="Maksimal 250 karakter">
                                            <small class="form-text text-muted">Maksimal 250 karakter.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tipe <span class="formkategori-text-required">*</span></label>
                                            <select name="kriteria[0][sub_kriteria][0][tipe_kategori]" class="form-select formkategori-tipe-kategori" required>
                                                <option selected disabled>Pilih tipe</option>
                                                <option value="text">Teks</option>
                                                <option value="radio">Pilihan (Radio)</option>
                                                <option value="checkbox">Kotak Centang</option>
                                                <option value="number">Angka</option>
                                                <option value="range">Rentang</option>
                                                <option value="textarea">Teks Panjang (catatan evaluator)</option>
                                                <option value="select">Pilihan Dropdown</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="formkategori-ket-tipe-section d-none mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="fa-solid fa-tags text-primary me-1"></i> Keterangan Tipe
                                        </label>
                                        <div class="formkategori-ket-tipe-wrapper text-end">
                                            <button type="button" class="btn btn-warning btn-sm formkategori-btn-add-ket mb-2">
                                                <i class="fa-solid fa-plus"></i> Tambah Keterangan
                                            </button>
                                            <div class="input-group mb-2">
                                                <input type="text" name="kriteria[0][sub_kriteria][0][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                                <input type="text" name="kriteria[0][sub_kriteria][0][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                                <button type="button" class="btn btn-warning btn-sm formkategori-btn-remove-ket"><i class="mdi mdi-trash-can"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Bobot <span class="formkategori-text-required">*</span></label>
                                            <input type="number" name="kriteria[0][sub_kriteria][0][bobot]" placeholder="Masukan bobot..." class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Level <span class="formkategori-text-required">*</span></label>
                                            <select name="kriteria[0][sub_kriteria][0][level]" class="form-select" required>
                                                <option selected disabled>Pilih</option>
                                                <option value="required">Harus Diisi</option>
                                                <option value="null">Tidak Harus</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-success btn-sm formkategori-btn-add-sub">
                                    <i class="fa-solid fa-plus"></i> Tambah Sub Kriteria
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-warning" id="formkategori-add-kriteria-main-block">
                            <i class="fa-solid fa-plus"></i> Tambah Kriteria
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Simpan Semua
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="formkategori-loading-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center" style="border-radius: 16px; border: none;">
                <div class="modal-body py-4">
                    <div class="formkategori-loading-spinner"></div>
                    <p class="mt-3 mb-0 fw-semibold text-secondary">Memuat template...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/penilaian360/formkategori.js') }}" defer></script>
@endsection