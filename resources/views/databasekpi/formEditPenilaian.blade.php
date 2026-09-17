@extends('layouts_kpi.app')

@section('kpi_contents')
    <script>
        window.editformPenilaianConfig = {
            routes: {
                update: "{{ route('penilaian.form.update') }}"
            },
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    <link rel="stylesheet" href="{{ asset('assets/vendor/vendorStyle/penilaian360.css') }}">

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: "{{ session('success') }}",
                        confirmButtonColor: '#6366f1'
                    });
                }
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: "{{ session('error') }}",
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: `{!! implode('<br>', $errors->all()) !!}`,
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        </script>
    @endif

    <div class="container editform-penilaian-content-wrapper mt-4">
        <div id="editform-penilaian-skeleton-container">
            <div class="editform-penilaian-skeleton-cell editform-penilaian-skeleton-back"></div>
            <div class="editform-penilaian-skeleton-cell editform-penilaian-skeleton-title"></div>
            <div class="editform-penilaian-skeleton-cell editform-penilaian-skeleton-block"></div>
            <div class="editform-penilaian-skeleton-cell editform-penilaian-skeleton-label" style="width: 200px; margin-bottom: 1rem;"></div>
            <div class="editform-penilaian-skeleton-cell editform-penilaian-skeleton-block" style="height: 200px;"></div>
            <div class="editform-penilaian-skeleton-cell editform-penilaian-skeleton-block" style="height: 200px;"></div>
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <div class="editform-penilaian-skeleton-cell editform-penilaian-skeleton-btn"></div>
                <div class="editform-penilaian-skeleton-cell editform-penilaian-skeleton-btn-save"></div>
            </div>
        </div>

        <div id="editform-penilaian-real-container" style="display: none;">
            <a href="{{ route('penilaian.form.data') }}" class="editform-penilaian-btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Form
            </a>

            <div class="editform-penilaian-content-card">
                <div class="card-body">
                    <h4 class="fw-bold text-dark mb-4 text-center">
                        <i class="fa-solid fa-file-pen text-primary me-2"></i>
                        {{ __('Edit Formulir') }}
                    </h4>

                    <form action="{{ route('penilaian.form.update') }}" method="POST">
                        @csrf
                        @if ($data['kode_form'])
                            <input type="hidden" name="kode_form" value="{{ $data['kode_form'] }}">
                        @endif

                        @if ($data['kode_form'])
                            <div class="mb-4">
                                <h5 class="editform-penilaian-section-title">
                                    <i class="fa-solid fa-layer-group"></i> Jenis Form
                                </h5>
                                <div class="editform-penilaian-karyawan-block">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Jenis Form <span class="text-danger">*</span></label>
                                            <select name="jenis_form" id="jenis_form" class="form-select" required>
                                                <option value="">Pilih Jenis Form</option>
                                                <option value="Kontrak" {{ $data['jenis_form'] === 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                                                <option value="Probation" {{ $data['jenis_form'] === 'Probation' ? 'selected' : '' }}>Probation</option>
                                                <option value="Rutin" {{ $data['jenis_form'] === 'Rutin' ? 'selected' : '' }}>Rutin</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div id="editform-penilaian-kriteria-container">
                            <h5 class="editform-penilaian-section-title">
                                <i class="fa-solid fa-list-check"></i> Kriteria Penilaian
                            </h5>

                            @foreach ($data['result'] as $kIndex => $item)
                                <div class="editform-penilaian-form-kriteria-block" data-kriteria-index="{{ $kIndex }}">
                                    <button type="button" class="editform-penilaian-btn-remove-block editform-penilaian-remove-kriteria-block" title="Hapus Kriteria">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>

                                    <div class="mb-3">
                                        <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                                        <input type="hidden" name="kriteria[{{ $kIndex }}][id_nama_penilaian]" value="{{ $item['id_formPenilaian'] }}">
                                        <input type="text" name="kriteria[{{ $kIndex }}][nama_penilaian]" class="form-control" placeholder="Masukan nama kriteria..." maxlength="250" value="{{ $item['nama_penilaian'] }}" required>
                                    </div>

                                    <div class="editform-penilaian-form-wrapper-sub-kriteria">
                                        @foreach ($item['kategori'] as $sIndex => $itemKategori)
                                            <div class="editform-penilaian-form-group-item" data-sub-kriteria-index="{{ $sIndex }}">
                                                <button type="button" class="editform-penilaian-btn-remove-block editform-penilaian-remove-sub-kriteria-block" title="Hapus Sub Kriteria">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Judul Sub Kriteria <span class="text-danger">*</span></label>
                                                        <input type="hidden" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][id_judul_kategori]" value="{{ $itemKategori['id_kategori'] }}">
                                                        <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][judul_kategori]" maxlength="250" class="form-control" placeholder="Masukan sub kriteria..." value="{{ $itemKategori['judul_kategori'] }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                                        <select name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][tipe_kategori]" class="form-select editform-penilaian-tipe-kategori" required>
                                                            <option disabled {{ empty($itemKategori['tipe_kategori']) ? 'selected' : '' }}>Pilih tipe</option>
                                                            <option value="text" {{ ($itemKategori['tipe_kategori'] ?? '') == 'text' ? 'selected' : '' }}>Teks</option>
                                                            <option value="radio" {{ ($itemKategori['tipe_kategori'] ?? '') == 'radio' ? 'selected' : '' }}>Pilihan (Radio)</option>
                                                            <option value="checkbox" {{ ($itemKategori['tipe_kategori'] ?? '') == 'checkbox' ? 'selected' : '' }}>Kotak Centang</option>
                                                            <option value="number" {{ ($itemKategori['tipe_kategori'] ?? '') == 'number' ? 'selected' : '' }}>Angka</option>
                                                            <option value="range" {{ ($itemKategori['tipe_kategori'] ?? '') == 'range' ? 'selected' : '' }}>Rentang</option>
                                                            <option value="textarea" {{ ($itemKategori['tipe_kategori'] ?? '') == 'textarea' ? 'selected' : '' }}>Teks Panjang</option>
                                                            <option value="select" {{ ($itemKategori['tipe_kategori'] ?? '') == 'select' ? 'selected' : '' }}>Pilihan Dropdown</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="editform-penilaian-ket-tipe-section {{ in_array($itemKategori['tipe_kategori'] ?? '', ['checkbox', 'radio', 'select']) ? '' : 'd-none' }} mb-3">
                                                    <label class="form-label fw-semibold">
                                                        <i class="fa-solid fa-tags text-primary me-1"></i> Keterangan Tipe
                                                    </label>
                                                    <div class="editform-penilaian-ket-tipe-wrapper text-end">
                                                        @if (!empty($itemKategori['dataTipeKeterangan']))
                                                            @foreach ($itemKategori['dataTipeKeterangan'] as $detail)
                                                                <div class="input-group mb-2">
                                                                    <input type="hidden" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][id_ket_tipe][]" value="{{ $detail['id'] }}">
                                                                    <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe" value="{{ $detail['keterangan_tipe'] }}">
                                                                    <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe..." value="{{ $detail['nilai_ket_tipe'] }}">
                                                                    <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="input-group mb-2">
                                                                <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][ket_tipe][]" class="form-control" placeholder="Masukkan keterangan tipe">
                                                                <input type="text" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][nilai_ket_tipe][]" class="form-control" placeholder="Nilai tipe...">
                                                                <button type="button" class="btn btn-danger btn-sm editform-penilaian-remove-ket-tipe"><i class="fa-solid fa-trash-can"></i></button>
                                                            </div>
                                                        @endif
                                                        <button type="button" class="editform-penilaian-btn-action success btn-sm editform-penilaian-add-ket-tipe mt-2">
                                                            <i class="fa-solid fa-plus"></i> Tambah Keterangan
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Bobot <span class="text-danger">*</span></label>
                                                        <input type="number" name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][bobot]" placeholder="Masukan bobot..." class="form-control" value="{{ $itemKategori['bobot'] }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Level <span class="text-danger">*</span></label>
                                                        <select name="kriteria[{{ $kIndex }}][sub_kriteria][{{ $sIndex }}][level]" class="form-select" required>
                                                            <option disabled {{ empty($itemKategori['level']) ? 'selected' : '' }}>Pilih</option>
                                                            <option value="required" {{ ($itemKategori['level'] ?? '') == 'required' ? 'selected' : '' }}>Harus Diisi</option>
                                                            <option value="null" {{ ($itemKategori['level'] ?? '') == 'null' ? 'selected' : '' }}>Tidak Harus</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="text-end mt-3">
                                        <button type="button" class="editform-penilaian-btn-action success btn-sm editform-penilaian-add-sub-kriteria-block">
                                            <i class="fa-solid fa-plus"></i> Tambah Sub Kriteria
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <button type="button" class="editform-penilaian-btn-action warning" id="editform-penilaian-add-kriteria-main-block">
                                <i class="fa-solid fa-plus"></i> Tambah Kriteria
                            </button>
                            <button type="submit" class="editform-penilaian-btn-action primary">
                                <i class="fa-solid fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/penilaian360/editform-penilaian.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('editform-penilaian-skeleton-container').style.display = 'none';
                document.getElementById('editform-penilaian-real-container').style.display = 'block';
            }, 800);
        });
    </script>
@endsection