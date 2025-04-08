@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                {{-- {{$karyawan}} --}}

                <a href="/lembur" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Perintah Lembur') }}</h5>
                    <form method="POST" action="{{ route('lembur.update', $data->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="backup_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <select name="id_karyawan" id="id_karyawan" class="form-select" disabled>
                                    <option value="-">Pilih Karyawan</option>
                                    @foreach ($karyawanall as $item)
                                        <option value="{{$item->id}}" @if($item->id == $data->id_karyawan) selected @endif>{{$item->nama_lengkap}}</option>
                                    @endforeach
                                </select>                           
                                @error('tipe')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input disabled id="divisi" type="text" placeholder="Masukan Divisi" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                                @error('divisi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_spl">
                            <label for="tanggal_spl" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Perintah Lembur') }}</label>
                            <div class="col-md-6">
                                <input type="date" readonly class="form-control" name="tanggal_spl" id="tanggal_spl" value="{{ $data->tanggal_spl }}">
                                @error('tanggal_spl')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="uraian_tugas">
                            <label for="uraian_tugas" class="col-md-4 col-form-label text-md-start">{{ __('Uraian Tugas') }}</label>
                            <div class="col-md-6">
                                <textarea name="uraian_tugas" class="form-control" id="uraian_tugas" cols="51" rows="5">{{$data->uraian_tugas}}</textarea>
                                @error('uraian_tugas')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="waktu_lembur" class="col-md-4 col-form-label text-md-start">{{ __('Waktu Lembur') }}</label>
                            <div class="col-md-6">
                                <select name="waktu_lembur" id="waktu_lembur" class="form-select">
                                    <option value="-">Pilih Waktu Lembur</option>
                                    <option value="Kerja" @if($data->waktu_lembur == 'Kerja') selected @endif>Hari Kerja</option>
                                    <option value="Libur" @if($data->waktu_lembur == 'Libur') selected @endif>Hari Libur</option>
                                </select>
                                @error('waktu_lembur')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_lembur">
                            <label for="tanggal_lembur" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Lembur') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="tanggal_lembur" id="tanggal_lembur" value="{{$data->tanggal_lembur}}">
                                @error('tanggal_lembur')
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
<style>

</style>
@endsection
