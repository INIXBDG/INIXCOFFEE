@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="/peserta" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Edit Peserta') }}</h5>
                    <form method="POST" action="{{ route('peserta.update', $peserta->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama" class="col-md-4 col-form-label text-md-start">{{ __('Nama') }}</label>
                            <div class="col-md-6">
                                <input id="nama" type="text" placeholder="Masukan Nama Perusaaan" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama', $peserta->nama) }}" autocomplete="nama" autofocus>
                                @error('nama')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-start">{{ __('Email') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="text" placeholder="Masukan Kategori Perusahaan" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $peserta->email) }}" autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="alamat" class="col-md-4 col-form-label text-md-start">{{ __('Alamat') }}</label>
                            <div class="col-md-6">
                                <input id="alamat" type="text" placeholder="Masukan alamat Perusahaan" class="form-control @error('alamat') is-invalid @enderror" name="alamat" value="{{ old('alamat', $peserta->alamat) }}" autocomplete="alamat" autofocus>
                                @error('alamat')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="jenis_kelamin" class="col-md-4 col-form-label text-md-start">{{ __('Jenis Kelamin') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('jenis_kelamin') is-invalid @enderror" name="jenis_kelamin" required autocomplete="jenis_kelamin">
                                    <option disabled {{ old('jenis_kelamin', $peserta->jenis_kelamin) == null ? 'selected' : '' }}>Pilih Jenis Kelamin</option>
                                    <option value="L" {{ old('jenis_kelamin', $peserta->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $peserta->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_lahir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Lahir') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_lahir" type="date" placeholder="Masukan tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ old('tanggal_lahir', $peserta->tanggal_lahir) }}" autocomplete="tanggal_lahir" autofocus>
                                @error('tanggal_lahir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="no_hp" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Handphone') }}</label>
                            <div class="col-md-6">
                                <input id="no_hp" type="text" placeholder="Masukan no_hp Perusahaan" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp', $peserta->no_hp) }}" autocomplete="no_hp" autofocus>
                                @error('no_hp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- <div class="row mb-3">
                            <label for="perusahaan" class="col-md-4 col-form-label text-md-start">{{ __('Perusahaan') }}</label>
                            <div class="col-md-6">
                                <input id="perusahaan" type="text" placeholder="Masukan Perusahaan" class="form-control @error('perusahaan') is-invalid @enderror" name="perusahaan" value="{{ old('perusahaan', $peserta->perusahaan->nama_perusahaan) }}" autocomplete="perusahaan" readonly>
                                @error('perusahaan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> --}}

                        <div class="row mb-3">
                            <label for="perusahaan_key" class="col-md-4 col-form-label text-md-start">{{ __('Perusahaan / Instansi') }}</label>
                            <div class="col-md-6">
                                <select style="height: 30px" class="form-select @error('perusahaan_key') is-invalid @enderror" name="perusahaan_key" id="perusahaan_key">
                                    <!-- Set the pre-selected option if $peserta->perusahaan exists -->
                                    @if($peserta->perusahaan)
                                        <option value="{{ $peserta->perusahaan->id }}" selected>{{ $peserta->perusahaan->nama_perusahaan }}</option>
                                    @endif
                                </select>
                                @error('perusahaan_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- <div class="row mb-3">
                            <label for="alamat" class="col-md-4 col-form-label text-md-start">{{ __('Alamat') }}</label>
                            <div class="col-md-6">
                                <input id="alamat" type="text" placeholder="Masukan Alamat Perusahaan" class="form-control @error('alamat') is-invalid @enderror" name="alamat" value="{{ old('alamat', $peserta->alamat) }}" autocomplete="alamat" autofocus>
                                @error('alamat')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="cp" class="col-md-4 col-form-label text-md-start">{{ __('CP') }}</label>
                            <div class="col-md-6">
                                <input id="cp" type="text" placeholder="Masukan CP Perusahaan" class="form-control @error('cp') is-invalid @enderror" name="cp" value="{{ old('cp', $peserta->cp) }}" autocomplete="cp" autofocus>
                                @error('cp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="no_telp" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Telepon') }}</label>
                            <div class="col-md-6">
                                <input id="no_telp" type="text" placeholder="Masukan Nomor Telepon Perusahaan" class="form-control @error('no_telp') is-invalid @enderror" name="no_telp" value="{{ old('no_telp', $peserta->no_telp) }}" autocomplete="no_telp" autofocus>
                                @error('no_telp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="foto_npwp" class="col-md-4 col-form-label text-md-start">{{ __('foto_npwp') }}</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control @error('foto_npwp') is-invalid @enderror" name="foto_npwp">
                                @error('foto_npwp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> --}}

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
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#perusahaan_key').select2({
            placeholder: "Pilih Perusahaan",
            allowClear: true,
            ajax: {
                url: '{{route('getPerusahaanById')}}',
                dataType: 'json', // Ensure JSON format
                processResults: function({data}) {
                    console.log(data);
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.nama_perusahaan
                            };
                        })
                    };
                }
            }

        });
    });      
</script>
@endpush
@endsection
