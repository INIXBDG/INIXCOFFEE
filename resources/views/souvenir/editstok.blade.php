@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Edit Souvenir') }}</h5>
                    <form method="POST" action="{{ route('souvenir.update', $souvenir->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama_souvenir" class="col-md-4 col-form-label text-md-start">{{ __('Nama Souvenir') }}</label>
                            <div class="col-md-6">
                                <input id="nama_souvenir" type="text" placeholder="Masukan Nama Souvenir" class="form-control @error('nama_souvenir') is-invalid @enderror" name="nama_souvenir" value="{{ old('nama_souvenir', $souvenir->nama_souvenir) }}" autocomplete="nama_souvenir" disabled autofocus>
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
                                <input id="harga" type="text" placeholder="Masukan Harga Souvenir" class="form-control @error('harga') is-invalid @enderror" name="harga" value="{{ old('harga', $souvenir->harga) }}" autocomplete="harga" disabled autofocus>
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
                                    <input type="text" class="form-control @error('min_harga_pelatihan') is-invalid @enderror" name="min_harga_pelatihan" id="min_harga_pelatihan" placeholder="Min" value="{{ old('min_harga_pelatihan', $souvenir->min_harga_pelatihan) }}" disabled required>
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
                                    <input type="text" class="form-control @error('max_harga_pelatihan') is-invalid @enderror" name="max_harga_pelatihan" id="max_harga_pelatihan" placeholder="Max" value="{{ old('max_harga_pelatihan', $souvenir->max_harga_pelatihan) }}" disabled required>
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
                                <input id="stok" type="text" placeholder="Masukan Stok Souvenir" class="form-control @error('stok') is-invalid @enderror" name="stok" value="{{ old('stok', $souvenir->stok) }}" autocomplete="stok" readonly autofocus>
                                @error('stok')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="new_stok" class="col-md-4 col-form-label text-md-start">{{ __('Stok Tambahan') }}</label>
                            <div class="col-md-6">
                                <input id="new_stok" type="text" placeholder="Masukan Tambahan Stok" class="form-control @error('new_stok') is-invalid @enderror" name="new_stok" value="{{ old('new_stok', $souvenir->new_stok) }}" autocomplete="new_stok"  autofocus>
                                @error('new_stok')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="catatan" class="col-md-4 col-form-label text-md-start">{{ __('Catatan') }}</label>
                            <div class="col-md-6">
                                <input id="catatan" type="text" placeholder="Masukan Catatan Update" class="form-control @error('catatan') is-invalid @enderror" name="catatan" value="{{ old('catatan') }}" autocomplete="catatan"  autofocus>
                                @error('catatan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Update') }}
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

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix === undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        }

        function setupInputFormatter(selector) {
            var $input = $(selector);
            $input.on('input', function() {
                $input.val(formatRupiah(this.value));
            });

            // Apply formatRupiah on initial load if value is present
            if ($input.val()) {
                $input.val(formatRupiah($input.val()));
            }
        }

        setupInputFormatter('#harga');
        setupInputFormatter('#min_harga_pelatihan');
        setupInputFormatter('#max_harga_pelatihan');
    });
</script>

@endsection
