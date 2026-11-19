@extends('layouts.app')

@section('content')
{{-- Load CSS Select2 & Bootstrap 5 Theme --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">
                        <img src="{{ asset('icon/arrow-left.svg') }}" width="20px"> Kembali
                    </a>

                    <h5 class="card-title text-center mb-4 fw-bold">Form Permintaan Lab</h5>

                    <form method="POST" action="{{ route('pengajuanlabsdansubs.store') }}">
                        @csrf
                        <input type="hidden" name="kode_karyawan" value="{{ $karyawan->kode_karyawan }}">
                        <input type="hidden" name="id_existing_lab" id="id_existing_lab" value="{{ old('id_existing_lab') }}">

                        @if ($errors->has('error'))
                            <div class="alert alert-danger">{{ $errors->first('error') }}</div>
                        @endif

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Nama Peminta</label>
                            <div class="col-md-6">
                                <input disabled type="text" class="form-control" value="{{ $karyawan->nama_lengkap }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Jabatan / Divisi</label>
                            <div class="col-md-6">
                                <input disabled type="text" class="form-control" value="{{ $karyawan->jabatan }} ({{ $karyawan->divisi }})">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Pilih Jadwal RKM <span class="text-muted small">(Opsional)</span></label>
                            <div class="col-md-6">
                                <select id="id_rkm" name="id_rkm" class="form-select @error('id_rkm') is-invalid @enderror">
                                    <option value="">-- Tanpa RKM / Pilih Jadwal Kelas --</option>
                                    @foreach($rkms as $rkm)
                                        <option value="{{ $rkm->id }}" {{ old('id_rkm') == $rkm->id ? 'selected' : '' }}>
                                            {{ $rkm->perusahaan->nama_perusahaan ?? '-' }}
                                            ({{ \Carbon\Carbon::parse($rkm->tanggal_awal)->translatedFormat('d M Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_rkm')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Materi</label>
                            <div class="col-md-6">
                                <input disabled type="text" id="view_materi" class="form-control bg-light" value="Non-RKM (Umum / Internal)">
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- INPUT LANGSUNG NAMA LAB (BISA CARI YANG SUDAH ADA ATAU KETIK BARU) --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Nama Lab / Software <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <select name="nama_labs" id="nama_labs" class="form-select @error('nama_labs') is-invalid @enderror" required>
                                    <option value="">-- Cari Lab atau Ketik Nama Baru --</option>
                                    <optgroup label="Rekomendasi Materi RKM" id="optgroup_rkm" disabled></optgroup>
                                    <optgroup label="Daftar Lab Terdaftar" id="optgroup_all">
                                        @foreach($masterLabs as $lab)
                                            <option value="{{ $lab->nama_labs }}"
                                                    data-id="{{ $lab->id }}"
                                                    data-merk="{{ $lab->merk ?? '-' }}"
                                                    {{ old('nama_labs') == $lab->nama_labs ? 'selected' : '' }}>
                                                {{ $lab->nama_labs }} ({{ $lab->merk ?? 'Tanpa Merk' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    Ketik untuk mencari lab yang sudah ada, atau langsung ketik nama lab baru lalu tekan <strong>Enter</strong>.
                                </small>
                                @error('nama_labs')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Vendor / Merk <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input type="text" name="merk" id="merk" class="form-control @error('merk') is-invalid @enderror"
                                       value="{{ old('merk') }}" required>
                                @error('merk')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Catatan Kebutuhan</label>
                            <div class="col-md-6">
                                <textarea name="catatan" class="form-control" rows="2" >{{ old('catatan') }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-0 mt-4">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary px-4" id="btn_submit">
                                    <i class="bi bi-send me-1"></i> Kirim Permintaan Lab
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const rkmSelect = $('#id_rkm');
    const materiInput = $('#view_materi');
    const namaLabSelect = $('#nama_labs');
    const optgroupRkm = $('#optgroup_rkm');
    const hiddenExistingId = $('#id_existing_lab');
    const merkInput = $('#merk');

    // Inisialisasi Select2 untuk RKM
    rkmSelect.select2({
        theme: 'bootstrap-5',
        placeholder: "-- Tanpa RKM / Pilih Jadwal Kelas --",
        allowClear: true,
        width: '100%'
    });

    // Inisialisasi Select2 dengan fitur Tags (bisa pilih atau ketik baru langsung)
    namaLabSelect.select2({
        theme: 'bootstrap-5',
        placeholder: "-- Cari Lab atau Ketik Nama Baru --",
        allowClear: true,
        tags: true,
        width: '100%',
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') {
                return null;
            }
            return {
                id: term,
                text: term + ' (Input Lab Baru)',
                newTag: true
            };
        }
    });

    // Saat Nama Lab dipilih atau diketik baru
    namaLabSelect.on('change', function() {
        let selectedOpt = $(this).find(':selected');
        let existingId = selectedOpt.attr('data-id');
        let merk = selectedOpt.attr('data-merk');

        if (existingId) {
            // Jika memilih dari Lab yang sudah terdaftar: isi Vendor otomatis & kunci
            hiddenExistingId.val(existingId);
            merkInput.val(merk || '-').prop('readonly', true).addClass('bg-light');
        } else if ($(this).val()) {
            // Jika mengetik Lab baru: kosongkan ID existing & buka kolom Vendor untuk diketik
            hiddenExistingId.val('');
            if (merkInput.prop('readonly')) {
                merkInput.val('');
            }
            merkInput.prop('readonly', false).removeClass('bg-light').attr('placeholder', 'Ketik Vendor / Merk untuk lab baru...');
            setTimeout(() => merkInput.focus(), 100);
        } else {
            // Jika input dikosongkan (clear)
            hiddenExistingId.val('');
            merkInput.val('').prop('readonly', false).removeClass('bg-light').attr('placeholder', 'Otomatis terisi / ketik manual jika lab baru...');
        }
    });

    // Saat RKM diubah, muat materi dan rekomendasi lab jika ada
    rkmSelect.on('change', function() {
        let rkmId = $(this).val();
        materiInput.val('');
        optgroupRkm.empty().prop('disabled', true);

        if (rkmId) {
            $.ajax({
                url: '/api/get-labs-by-rkm/' + rkmId,
                type: 'GET',
                success: function(response) {
                    materiInput.val(response.materi_nama || '-');

                    if (response.labs && response.labs.length > 0) {
                        optgroupRkm.prop('disabled', false);
                        $.each(response.labs, function(k, v) {
                            let merkText = v.merk ? v.merk : '-';
                            optgroupRkm.append(
                                `<option value="${v.nama_labs}" data-id="${v.id}" data-merk="${merkText}">★ ${v.nama_labs} (${merkText})</option>`
                            );
                        });

                        // Otomatis pilih rekomendasi pertama dari materi RKM tersebut
                        let firstLab = response.labs[0];
                        namaLabSelect.val(firstLab.nama_labs).trigger('change');
                    } else {
                        namaLabSelect.trigger('change.select2');
                    }
                },
                error: function() {
                    materiInput.val('Gagal memuat materi');
                }
            });
        } else {
            materiInput.val('Non-RKM (Umum / Internal)');
            namaLabSelect.trigger('change.select2');
        }
    });

    if (rkmSelect.val()) {
        rkmSelect.trigger('change');
    }
});
</script>
@endsection