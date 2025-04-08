@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    @if (auth()->user()->jabatan == 'Education Manager')
                    <h5 class="card-title text-center mb-4">{{ __('Assign Instruktur & Kelas') }}</h5>
                    @endif
                    <h5 class="card-title text-center mb-4">{{ __('Assign Kelas') }}</h5>
                    <form method="POST" action="{{ route('updateInstruktur') }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            {{-- {{ $rkm }} --}}
                            <label for="rkm_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama RKM') }}</label>
                            <div class="col-md-6">
                                <input id="nama_materi" readonly type="text" class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ $rkm->materi->nama_materi }}" autocomplete="nama_materi" autofocus>
                                @error('rkm_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('ID RKM') }}</label>
                            <div class="col-md-6">
                                <input id="id_rkm" readonly type="text" class="form-control @error('id_rkm') is-invalid @enderror" name="id_rkm" value="{{ $rkm->id }}" autocomplete="id_rkm" autofocus>
                                @error('id_rkm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="materi_key" class="col-md-4 col-form-label text-md-start">{{ __('ID Materi') }}</label>
                            <div class="col-md-6">
                                <input id="materi_key" readonly type="text" class="form-control @error('materi_key') is-invalid @enderror" name="materi_key" value="{{ $rkm->materi_key }}" autocomplete="materi_key" autofocus>
                                @error('materi_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Awal') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_awal" type="date" placeholder="tanggal_awal" class="form-control @error('tanggal_awal') is-invalid @enderror" name="tanggal_awal" readonly value="{{ old('tanggal_awal', $rkm->tanggal_awal) }}" autocomplete="tanggal_awal" autofocus>
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
                                <input id="tanggal_akhir" type="date" placeholder="tanggal_akhir" class="form-control @error('tanggal_akhir') is-invalid @enderror" name="tanggal_akhir"  readonly value="{{ old('tanggal_akhir', $rkm->tanggal_akhir) }}" autocomplete="tanggal_akhir" autofocus>
                                @error('tanggal_akhir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        @if (auth()->user()->jabatan == 'Technical Support')

                        @else
                        <div class="row mb-3">
                            <label for="instruktur_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama Instruktur 1') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('instruktur_key') is-invalid @enderror" name="instruktur_key" required autocomplete="instruktur_key">
                                    <option value="-" selected>Pilih Instruktur 1</option>
                                    @foreach ( $karyawan as $instruktur_keys )
                                    <option value="{{ $instruktur_keys->kode_karyawan }}" @if ($rkm->instruktur_key == $instruktur_keys->kode_karyawan) selected @endif>{{ $instruktur_keys->kode_karyawan }} - {{ $instruktur_keys->nama_lengkap }}</option>
                                    @endforeach
                                    {{-- <option value="OL" @if ($rkm->instruktur_key == 'OL') selected @endif>OL - Orang Luar</option> --}}

                                </select>
                                @error('instruktur_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="instruktur_key2" class="col-md-4 col-form-label text-md-start">{{ __('Nama Instruktur 2') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('instruktur_key2') is-invalid @enderror" name="instruktur_key2" value="" required autocomplete="instruktur_key2">
                                    <option value="-" selected>Pilih Instruktur 2</option>
                                    @foreach ( $karyawan as $instruktur_key2 )
                                    <option value="{{ $instruktur_key2->kode_karyawan }}" @if ($rkm->instruktur_key2 == $instruktur_key2->kode_karyawan) selected @endif>{{ $instruktur_key2->kode_karyawan }} - {{ $instruktur_key2->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                                @error('instruktur_key2')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="asisten_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama Asisten Instruktur') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('asisten_key') is-invalid @enderror" name="asisten_key" value="" required autocomplete="asisten_key">
                                    <option value="-" selected>Pilih Asisten Instruktur</option>
                                    @foreach ( $karyawan as $asisten_key )
                                    <option value="{{ $asisten_key->kode_karyawan }}" @if ($rkm->asisten_key == $asisten_key->kode_karyawan) selected @endif>{{ $asisten_key->kode_karyawan }} - {{ $asisten_key->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                                @error('asisten_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <div class="row mb-3">
                            <label for="ruang" class="col-md-4 col-form-label text-md-start">{{ __('Ruang') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('ruang') is-invalid @enderror" name="ruang" value="{{ old('ruang', $rkm->ruang) }}" autocomplete="ruang">
                                    <option value="-">Pilih Ruang</option>
                                    <option value="1" @if ($rkm->ruang == "1") selected @endif>1</option>
                                    <option value="2" @if ($rkm->ruang == "2") selected @endif>2</option>
                                    <option value="3" @if ($rkm->ruang == "3") selected @endif>3</option>
                                    <option value="4" @if ($rkm->ruang == "4") selected @endif>4</option>
                                    <option value="5" @if ($rkm->ruang == "5") selected @endif>5</option>
                                    <option value="6" @if ($rkm->ruang == "6") selected @endif>6</option>
                                    <option value="7" @if ($rkm->ruang == "7") selected @endif>7</option>
                                    <option value="ADOC" @if ($rkm->ruang == "ADOC") selected @endif>ADOC</option>
                                    <option value="Inhouse" @if ($rkm->ruang == "Inhouse") selected @endif>Inhouse</option>
                                </select>
                                @error('ruang')
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
