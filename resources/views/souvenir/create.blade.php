@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Souvenir Baru') }}</h5>
                    <form method="POST" action="{{ route('souvenir.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_souvenir" class="col-md-4 col-form-label text-md-start">{{ __('Nama Souvenir') }}</label>
                            <div class="col-md-6">
                                <input id="nama_souvenir" type="text" placeholder="Masukan Nama Souvenir" class="form-control @error('nama_souvenir') is-invalid @enderror" name="nama_souvenir" value="{{ old('nama_souvenir') }}" autocomplete="nama_souvenir" autofocus>
                                @error('nama_souvenir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harga" class="col-md-4 col-form-label text-md-start">{{ __('Harga Souvenir') }}</label>
                            <div class="col-md-6">
                                <input id="harga" type="text" placeholder="Masukan Harga Souvenir" class="form-control @error('harga') is-invalid @enderror" name="harga" value="{{ old('harga') }}" autocomplete="harga" autofocus>
                                @error('harga')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harga_pelatihan" class="col-md-4 col-form-label text-md-start">{{ __('Range Harga Pelatihan') }}</label>
                            <div class="col-md-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('min_harga_pelatihan') is-invalid @enderror" name="min_harga_pelatihan" id="min_harga_pelatihan" placeholder="Min" required>
                                </div>
                                @error('min_harga_pelatihan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('max_harga_pelatihan') is-invalid @enderror" name="max_harga_pelatihan" id="max_harga_pelatihan" placeholder="Max" required>
                                </div>
                                @error('max_harga_pelatihan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="stok" class="col-md-4 col-form-label text-md-start">{{ __('Stok Saat ini') }}</label>
                            <div class="col-md-6">
                                <input id="stok" type="text" placeholder="Masukan Stok Souvenir" class="form-control @error('stok') is-invalid @enderror" name="stok" value="{{ old('stok') }}" autocomplete="stok" autofocus>
                                @error('stok')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="foto" class="col-md-4 col-form-label text-md-start">{{ __('Foto Souvenir') }}</label>
                            <div class="col-md-6">
                                <input id="foto" type="file" placeholder="Masukan Foto Souvenir" class="form-control @error('foto') is-invalid @enderror" name="foto" value="{{ old('foto') }}" autocomplete="foto" autofocus accept="image/png, image/jpeg">
                                @error('foto')
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        function formatRupiah(angka, prefix) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        }

        function setupInputFormatter(id) {
            var input = document.getElementById(id);
            input.addEventListener('input', function() {
                input.value = formatRupiah(this.value);
            });
        }

        setupInputFormatter('harga');
        setupInputFormatter('min_harga_pelatihan');
        setupInputFormatter('max_harga_pelatihan');
    });
</script>
@endsection
