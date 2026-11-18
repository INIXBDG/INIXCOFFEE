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
                    <a href="{{ route('pengajuansubs.index') }}" class="btn btn-secondary mb-3">
                        <img src="{{ asset('icon/arrow-left.svg') }}" width="20px"> Kembali
                    </a>

                    <h5 class="card-title text-center mb-4 fw-bold">Form Permintaan Subscription</h5>

                    <form method="POST" action="{{ route('pengajuansubs.store') }}">
                        @csrf
                        <input type="hidden" name="kode_karyawan" value="{{ $karyawan->kode_karyawan }}">
                        <input type="hidden" name="id_existing_subs" id="id_existing_subs" value="{{ old('id_existing_subs') }}">

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

                        {{-- INPUT LANGSUNG NAMA SUBSCRIPTION (BISA CARI YANG SUDAH ADA ATAU KETIK BARU) --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Nama Subscription / Software <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <select name="nama_subs" id="nama_subs" class="form-select @error('nama_subs') is-invalid @enderror" required>
                                    <option value="">-- Cari Subscription atau Ketik Nama Baru --</option>
                                    <optgroup label="Daftar Subscription Terdaftar">
                                        @foreach($masterSubs as $sub)
                                            <option value="{{ $sub->nama_subs }}"
                                                    data-id="{{ $sub->id }}"
                                                    data-merk="{{ $sub->merk ?? '-' }}"
                                                    {{ old('nama_subs') == $sub->nama_subs ? 'selected' : '' }}>
                                                {{ $sub->nama_subs }} ({{ $sub->merk ?? 'Tanpa Merk' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    Ketik untuk mencari subscription yang sudah ada, atau langsung ketik nama subscription baru lalu tekan <strong>Enter</strong>.
                                </small>
                                @error('nama_subs')
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
                            <label class="col-md-4 col-form-label text-md-start">Jumlah Akun / Lisensi <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input type="number" name="qty" id="qty" min="1" class="form-control @error('qty') is-invalid @enderror"
                                       value="{{ old('qty', 1) }}" required>
                                @error('qty')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Catatan Kebutuhan</label>
                            <div class="col-md-6">
                                <textarea name="catatan" class="form-control" rows="3">{{ old('catatan') }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-0 mt-4">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary px-4" id="btn_submit">
                                    <i class="bi bi-send me-1"></i> Kirim Permintaan Subscription
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const namaSubsSelect = $('#nama_subs');
    const hiddenExistingId = $('#id_existing_subs');
    const merkInput = $('#merk');

    // Inisialisasi Select2 dengan fitur Tags (bisa pilih atau ketik baru langsung)
    namaSubsSelect.select2({
        theme: 'bootstrap-5',
        placeholder: "Cari Subscription",
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
                text: term + ' (Input Subscription Baru)',
                newTag: true
            };
        }
    });

    // Saat Nama Subscription dipilih atau diketik baru
    namaSubsSelect.on('change', function() {
        let selectedOpt = $(this).find(':selected');
        let existingId = selectedOpt.attr('data-id');
        let merk = selectedOpt.attr('data-merk');

        if (existingId) {
            // Jika memilih dari Subscription yang sudah terdaftar: isi Vendor otomatis & kunci
            hiddenExistingId.val(existingId);
            merkInput.val(merk || '-').prop('readonly', true).addClass('bg-light');
        } else if ($(this).val()) {
            // Jika mengetik Subscription baru: kosongkan ID existing & buka kolom Vendor untuk diketik
            hiddenExistingId.val('');
            if (merkInput.prop('readonly')) {
                merkInput.val('');
            }
            merkInput.prop('readonly', false).removeClass('bg-light').attr('placeholder', '');
            setTimeout(() => merkInput.focus(), 100);
        } else {
            // Jika input dikosongkan (clear)
            hiddenExistingId.val('');
            merkInput.val('').prop('readonly', false).removeClass('bg-light').attr('placeholder', '');
        }
    });
});
</script>
@endsection
