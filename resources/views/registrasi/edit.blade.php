@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    {{-- {{ $peserta }} --}}
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Edit Peserta') }}</h5>
                    <form method="POST" action="{{ route('registrasi.update', $peserta->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('Rencana Kelas Mingguan') }}</label>
                            <div class="col-md-6">
                                <select style="height: 35px" class="form-select @error('id_rkm') is-invalid @enderror" name="id_rkm" id="id_rkm">
                                    @foreach ($rkm as $rkms)
                                        <option value="{{ $rkms->id }}">{{ $rkms->materi->nama_materi }} - {{ $rkms->perusahaan->nama_perusahaan }} - {{ formatRupiah(floatval($rkms->harga_jual))}}</option>
                                    @endforeach
                                </select>
                                @error('id_rkm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div id="rkm-id">
                            <div class="row mb-3">
                                <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="nama_materi" type="text" class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ $peserta->rkm->materi->nama_materi }}" autocomplete="nama_materi" autofocus>
                                    @error('nama_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Awal') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="tanggal_awal" type="date" class="form-control @error('tanggal_awal') is-invalid @enderror" name="tanggal_awal" value="{{ $peserta->rkm->tanggal_awal }}" autocomplete="tanggal_awal" autofocus>
                                    @error('tanggal_awal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Akhir') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="tanggal_akhir" type="date" class="form-control @error('tanggal_akhir') is-invalid @enderror" name="tanggal_akhir" value="{{ $peserta->rkm->tanggal_akhir }}" autocomplete="tanggal_akhir" autofocus>
                                    @error('tanggal_akhir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-start">{{ __('Email') }}</label>
                            <div class="col-md-6">
                                <input readonly id="email" type="text" placeholder="Masukkan Email Peserta" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $peserta->peserta->email }}" autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            {{-- <div class="col-md-1">
                                <button type="button" id="cek" class="btn btn-primary">Cek</button>
                            </div> --}}
                        </div>

                        <div id="data-peserta">
                            <div class="row mb-3">
                                <label for="nama" class="col-md-4 col-form-label text-md-start">{{ __('Nama Peserta') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="nama" type="text" placeholder="Masukan Nama Peserta" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ $peserta->peserta->nama }}" autocomplete="nama" autofocus>
                                    @error('nama')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="id_peserta" class="col-md-4 col-form-label text-md-start">{{ __('ID Peserta') }}</label>
                                <div class="col-md-6">
                                    <input readonly type="text" readonly class="form-control" name="id_peserta" id="id_peserta" value="{{ $peserta->peserta->id }}">
                                    @error('id_peserta')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            {{-- <div class="row mb-3">
                                <label for="jenis_kelamin" class="col-md-4 col-form-label text-md-start">{{ __('Jenis Kelamin') }}</label>
                                <div class="col-md-6">
                                    <select class="form-select" aria-label="jenis_kelamin" name="jenis_kelamin" id="jenis_kelamin">
                                        <option value="L" {{ $peserta->peserta->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ $peserta->peserta->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div> --}}

                            {{-- <div class="row mb-3">
                                <label for="no_hp" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Handphone') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="no_hp" type="text" placeholder="Masukan Nomor Handphone" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ $peserta->peserta->no_hp }}" autocomplete="no_hp" autofocus>
                                    @error('no_hp')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div> --}}

                            {{-- <div class="row mb-3">
                                <label for="alamat" class="col-md-4 col-form-label text-md-start">{{ __('Alamat') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="alamat" type="text" placeholder="Masukan Alamat" class="form-control @error('alamat') is-invalid @enderror" name="alamat" value="{{ $peserta->peserta->alamat }}" autocomplete="alamat" autofocus>
                                    @error('alamat')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div> --}}



                            {{-- <div class="row mb-3">
                                <label for="tanggal_lahir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Lahir') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="tanggal_lahir" type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ $peserta->peserta->tanggal_lahir }}" autocomplete="tanggal_lahir" autofocus>
                                    @error('tanggal_lahir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                             --}}
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update') }}
                                </button>
                                <a href="{{ url()->previous() }}" class="btn click-primary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#perusahaan_key').select2();
        $('#id_rkm').select2();

        $('#cek').on('click', function() {
            var email = $('#email').val();
            $.ajax({
                url: '{{ route("getRKMRegist") }}',
                type: 'GET',
                data: { email: email },
                success: function(response) {
                    if (response.exists) {
                        $('#id_peserta').val(response.peserta.id);
                        $('#nama').val(response.peserta.nama);
                        $('#jenis_kelamin').val(response.peserta.jenis_kelamin);
                        $('#no_hp').val(response.peserta.no_hp);
                        $('#alamat').val(response.peserta.alamat);
                        $('#tanggal_lahir').val(response.peserta.tanggal_lahir);
                        $('#perusahaan_key').val(response.peserta.perusahaan_key).trigger('change');
                    } else {
                        $('#data-peserta input').val('');
                        $('#perusahaan_key').val('').trigger('change');
                    }
                }
            });
        });
    });
</script>
@endsection
