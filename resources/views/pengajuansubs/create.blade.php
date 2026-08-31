@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">
                        <img src="{{ asset('icon/arrow-left.svg') }}" width="20px"> Kembali
                    </a>

                    <h5 class="card-title text-center mb-4 fw-bold">Pengajuan Subscription (Divisi Education)</h5>

                    <form method="POST" action="{{ route('pengajuansubs.store') }}">
                        @csrf
                        <input type="hidden" name="kode_karyawan" value="{{ $karyawan->kode_karyawan }}">

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Nama Karyawan</label>
                            <div class="col-md-6">
                                <input disabled type="text" class="form-control" value="{{ $karyawan->nama_lengkap }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Divisi</label>
                            <div class="col-md-6">
                                <input disabled type="text" class="form-control" value="{{ $karyawan->divisi }}">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Pilih RKM</label>
                            <div class="col-md-6">
                                <select id="id_rkm" name="id_rkm" class="form-select" required>
                                    <option value="">-- Pilih Jadwal Kelas --</option>
                                    @foreach($rkms as $rkm)
                                        <option value="{{ $rkm->id }}">
                                            {{ $rkm->perusahaan->nama_perusahaan ?? '-' }}
                                            ({{ \Carbon\Carbon::parse($rkm->tanggal_awal)->translatedFormat('d M Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Materi</label>
                            <div class="col-md-6">
                                <input type="text" id="view_materi" class="form-control bg-light" readonly
                                       placeholder="Otomatis terisi...">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Sumber Subscription</label>
                            <div class="col-md-8 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sumber_subs" id="opt_existing" value="existing" checked disabled>
                                    <label class="form-check-label" for="opt_existing">
                                        Gunakan Subscription Terdaftar
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sumber_subs" id="opt_new" value="new" disabled>
                                    <label class="form-check-label" for="opt_new">
                                        Request Subscription Baru
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="section_existing">
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">Pilih Subscription</label>
                                <div class="col-md-6">
                                    <select id="id_existing_subs" name="id_existing_subs" class="form-select" disabled>
                                        <option value="">-- Menunggu RKM --</option>
                                    </select>
                                    <small class="text-muted d-block mt-1" id="hint_existing"></small>
                                </div>
                            </div>
                        </div>

                        <div id="section_new" class="d-none">
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">Nama Subscription / Software</label>
                                <div class="col-md-6">
                                    <input type="text" name="new_nama_subs" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">Vendor / Merk</label>
                                <div class="col-md-6">
                                    <input type="text" name="new_merk" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0 mt-4">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary px-4" id="btn_submit" disabled>
                                    <i class="bi bi-save me-1"></i> Simpan Pengajuan
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
<script>
$(document).ready(function() {
    const rkmSelect = $('#id_rkm');
    const existingSelect = $('#id_existing_subs');
    const materiInput = $('#view_materi');
    const radios = $('input[name="sumber_subs"]');
    const submitBtn = $('#btn_submit');
    const sectionExisting = $('#section_existing');
    const sectionNew = $('#section_new');

    rkmSelect.change(function() {
        let rkmId = $(this).val();

        existingSelect.empty().append('<option value="">Loading data...</option>').prop('disabled', true);
        materiInput.val('');
        radios.prop('disabled', true);
        submitBtn.prop('disabled', true);

        if (rkmId) {
            $.ajax({
                url: '/api/get-subs-by-rkm/' + rkmId,
                type: 'GET',
                success: function(response) {
                    materiInput.val(response.materi_nama);
                    radios.prop('disabled', false);
                    submitBtn.prop('disabled', false);
                    existingSelect.empty().append('<option value="">-- Pilih Subscription Terdaftar --</option>');

                    if (response.subs.length > 0) {
                        $.each(response.subs, function(k, v) {
                            let typeLabel = v.tipe === 'subscription' ? '[Subs]' : '[One-Time]';
                            let merkLabel = v.merk ? `(${v.merk})` : '';
                            existingSelect.append(`<option value="${v.id}">${typeLabel} ${v.nama_subs} ${merkLabel}</option>`);
                        });
                        $('#opt_existing').prop('checked', true).trigger('change');
                        existingSelect.prop('disabled', false);
                    } else {
                        existingSelect.append('<option value="" disabled>Belum ada subscription terdaftar untuk materi ini</option>');
                        $('#opt_new').prop('checked', true).trigger('change');
                    }
                },
                error: function() {
                    alert('Gagal mengambil data Subscription.');
                }
            });
        }
    });

    radios.change(function() {
        let mode = $(this).val();

        if (mode === 'existing') {
            sectionExisting.removeClass('d-none');
            sectionNew.addClass('d-none');

            $('#id_existing_subs').prop('required', true);
            $('input[name="new_nama_subs"]').prop('required', false);
            $('input[name="new_merk"]').prop('required', false);
        } else {
            sectionExisting.addClass('d-none');
            sectionNew.removeClass('d-none');

            $('#id_existing_subs').prop('required', false);
            $('input[name="new_nama_subs"]').prop('required', true);
            $('input[name="new_merk"]').prop('required', true);
        }
    });
});
</script>
@endsection
