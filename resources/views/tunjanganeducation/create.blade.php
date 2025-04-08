@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Target') }}</h5>
                    <form method="POST" action="{{ route('target.store') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="objek" class="col-md-4 col-form-label text-md-start">{{ __('Objek') }}</label>
                            <div class="col-md-6">
                                <select id="objek" class="form-select @error('objek') is-invalid @enderror" name="objek" required autocomplete="objek" autofocus>
                                    <option value="" selected>Pilih Objek</option>
                                    <option value="Inixindo" >Inixindo</option>

                                    @foreach($user as $u)
                                    <option value="{{$u->id_sales}}">{{$u->karyawan->nama_lengkap}}</option>
                                    @endforeach
                                </select>
                                @error('objek')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="quartal" class="col-md-4 col-form-label text-md-start">{{ __('Quartal') }}</label>
                            <div class="col-md-6">
                                <select id="quartal" class="form-select @error('quartal') is-invalid @enderror" name="quartal" required autocomplete="quartal" autofocus>
                                    <option value="" selected>Pilih Quartal</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                    <option value="All">All</option>
                                </select>
                                @error('quartal')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tahun" class="col-md-4 col-form-label text-md-start">{{ __('Tahun') }}</label>
                            <div class="col-md-6">
                                <input type="number" class="form-control" name="tahun" value="{{ now()->year }}" required>
                                @error('tahun')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>                        

                        <div class="row mb-3">
                            <label for="target" class="col-md-4 col-form-label text-md-start">{{ __('Target') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('min_harga_pelatihan') is-invalid @enderror" name="target" id="target" placeholder="Capaian Target" required>
                                </div>
                                @error('target')
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
<script>
    $(document).ready(function () {
        // Format the target input as the user types
        $('#target').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });

        // Intercept form submission
        $('form').on('submit', function (e) {
            // Get the target input element
            let targetInput = $('#target');
            
            // Remove Rupiah format and set the cleaned value back to the input
            let cleanedValue = removeRupiahFormat(targetInput.val());
            targetInput.val(cleanedValue);
        });
    });

    // Function to format value to Rupiah format
    function formatRupiah(angka, prefix) {
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

    // Function to remove Rupiah formatting before submitting
    function removeRupiahFormat(angka) {
        return parseFloat(angka.replace(/[^\d,]/g, '').replace(',', '.'));
    }
</script>

@endsection
