@extends('layouts.app')

@section('content')
<style>
    #webcam-preview {
        transform: scaleX(1);
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-1">{{ __('Pengajuan Klaim') }}</h5>
                    <p class="card-title text-center mb-5">{{ __('Ajukan Pembatalan Cuti Anda Yang sudah Disetujui') }}</p>
                    <form method="POST" action="{{ route('pengajuanklaim.aproveCancelLeave') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">
                                <input disabled id="nama_karyawan" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('nama_karyawan') is-invalid @enderror" name="nama_karyawan" value="{{ $karyawan->nama_lengkap }}" autocomplete="nama_karyawan" autofocus>
                                @error('nama_karyawan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input disabled id="divisi" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                                @error('divisi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_cuti-row">
                            <label for="tanggal_cuti" class="col-md-4 col-form-label text-md-start">{{ __('Pilih Tanggal Cuti') }}</label>
                            <div class="col-md-6">
                                <select name="tanggal_cuti" class="form-select @error('tanggal_cuti') is-invalid @enderror" required id="tanggal_cuti">
                                    <option selected disabled>Pilih Tanggal Cuti</option>
                                    @foreach ($data_cuti as $data)
                                    <option value="{{ $data->id }}">
                                        {{ \Carbon\Carbon::parse($data->tanggal_awal)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($data->tanggal_akhir)->translatedFormat('d F Y') }}
                                    </option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="row mb-3" id="bukti_gambar-row">
                            <label for="bukti_gambar" class="col-md-4 col-form-label text-md-start">{{ __('Upload Bukti Gambar') }}</label>
                            <div class="col-md-6">
                                <input id="bukti_gambar" type="file" class="form-control @error('bukti_gambar') is-invalid @enderror" name="bukti_gambar" accept="image/*">
                            </div>
                        </div>


                        <div class="row mb-3" id="kronologi-row">
                            <label for="kronologi" class="col-md-4 col-form-label text-md-start">{{ __('Kronologi') }}</label>
                            <div class="col-md-6">
                                <textarea name="kronologi" placeholder="kronologi..." required class="form-control" id="kronologi" cols="51" rows="5" required></textarea>
                                @error('kronologi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Simpan') }}
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tanggal_cuti').select2({
            placeholder: "Pilih Tanggal Absen",
            allowClear: true
        });
    });
</script>

@endsection