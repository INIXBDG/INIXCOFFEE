@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Pengajuan Lab / Subscription') }}</h5>

                    <form method="POST" action="{{ route('pengajuanlabsdansubs.store') }}">
                        @csrf

                        <!-- ID Karyawan -->
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Nama Karyawan</label>
                            <div class="col-md-6">
                                <input type="hidden" name="kode_karyawan" value="{{ $karyawan->kode_karyawan }}">
                                <input disabled type="text" class="form-control" value="{{ $karyawan->nama_lengkap }}">
                            </div>
                        </div>

                        <!-- Divisi -->
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start">Divisi</label>
                            <div class="col-md-6">
                                <input disabled type="text" class="form-control" value="{{ $karyawan->divisi }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">RKM</label>
                            <div class="col-md-6">
                                <select id="id_rkm" name="id_rkm" class="form-select">
                                    <option value="">-- Pilih RKM --</option>
                                    @foreach($rkms as $rkm)
                                        <option value="{{ $rkm->id }}">
                                            {{ $rkm->materi->nama_materi }} - {{ $rkm->perusahaan->nama_perusahaan }}
                                            ({{ \Carbon\Carbon::parse($rkm->tanggal_awal)->locale('id')->translatedFormat('d F Y') }}
                                            s/d {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->locale('id')->translatedFormat('d F Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Pilihan Jenis Pengajuan -->
                        <div class="row mb-3">
                            <label for="jenis_pengajuan" class="col-md-4 col-form-label text-md-start">Jenis Pengajuan</label>
                            <div class="col-md-6">
                                <select id="jenis_pengajuan" name="jenis_pengajuan" class="form-select" required>
                                    <option value="">-- Pilih Jenis Pengajuan --</option>
                                    <option value="lab">Lab</option>
                                    <option value="subs">Subscription</option>
                                </select>
                            </div>
                        </div>

                        <!-- Form Lab -->
                        <div id="formLab" class="d-none">
                            <h6 class="text-primary">Detail Lab</h6>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">Pilih Lab</label>
                                <div class="col-md-6">
                                    <select id="lab_id" name="lab_id" class="form-select">
                                        <option value="">-- Pilih Lab --</option>
                                        @foreach($labs as $lab)
                                            <option value="{{ $lab->id }}">{{ $lab->nama_labs }}</option>
                                        @endforeach
                                        <option value="new">+ Tambah Lab Baru</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 col-form-label text-md-start"></div>
                                <div class="col-md-6">
                                    <div id="newLabForm" class="d-none">
                                        <input type="text" name="new_nama_labs" class="form-control mb-2" placeholder="Nama Lab">
                                        <textarea name="new_desc_labs" class="form-control mb-2" placeholder="Deskripsi Lab"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Subscription -->
                        <div id="formSubs" class="d-none">
                            <h6 class="text-success">Detail Subscription</h6>
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">Pilih Subscription</label>
                                <div class="col-md-6">
                                    <select id="subs_id" name="subs_id" class="form-select">
                                        <option value="">-- Pilih Subscription --</option>
                                        @foreach($subs as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->nama_subs }}</option>
                                        @endforeach
                                        <option value="new">+ Tambah Subscription Baru</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Inline Tambah Subs -->
                            <div class="row mb-3">
                                <div class="col-md-4 col-form-label text-md-start"></div>
                                <div class="col-md-6">
                                    <div id="newSubsForm" class="d-none">
                                        <input type="text" name="new_nama_subs" class="form-control mb-2" placeholder="Nama Subscription">
                                        <input type="text" name="new_merk" class="form-control mb-2" placeholder="Merk">
                                        <textarea name="new_desc_subs" class="form-control mb-2" placeholder="Deskripsi Subscription"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // Toggle form berdasarkan jenis pengajuan
    $('#jenis_pengajuan').change(function () {
        let pilihan = $(this).val();
        if (pilihan === "lab") {
            $('#formLab').removeClass('d-none');
            $('#formSubs').addClass('d-none');
        } else if (pilihan === "subs") {
            $('#formSubs').removeClass('d-none');
            $('#formLab').addClass('d-none');
        } else {
            $('#formLab, #formSubs').addClass('d-none');
        }
    });

    // Toggle inline Lab
    $('#lab_id').change(function () {
        if ($(this).val() === "new") {
            $('#newLabForm').removeClass('d-none');
        } else {
            $('#newLabForm').addClass('d-none');
        }
    });

    // Toggle inline Subscription
    $('#subs_id').change(function () {
        if ($(this).val() === "new") {
            $('#newSubsForm').removeClass('d-none');
        } else {
            $('#newSubsForm').addClass('d-none');
        }
    });
});
</script>

@endsection
