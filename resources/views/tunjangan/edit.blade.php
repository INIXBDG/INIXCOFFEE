@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Tunjangan') }}</h5>
                    <form method="POST" action="{{ route('tunjangan.update', $tunjangan->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama_tunjangan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Tunjangan') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama_tunjangan" placeholder="Masukan Nama Tunjangan" value="{{$tunjangan->nama_tunjangan}}" required>
                                @error('nama_tunjangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> 
                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Tunjangan') }}</label>
                            <div class="col-md-6">
                                <select id="tipe" class="form-select @error('tipe') is-invalid @enderror" name="tipe" required autocomplete="tipe" autofocus>
                                    <option value="">Pilih Jenis</option>
                                    <option value="Potongan" {{ $tunjangan->tipe == 'Potongan' ? 'selected' : '' }}>Potongan</option>
                                    <option value="Tunjangan" {{ $tunjangan->tipe == 'Tunjangan' ? 'selected' : '' }}>Tunjangan</option>
                                </select>
                                @error('tipe')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="nilai" class="col-md-4 col-form-label text-md-start">{{ __('Nilai') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('min_harga_pelatihan') is-invalid @enderror" name="nilai" id="nilai" value="{{$tunjangan->nilai}}" placeholder="Nilai Tunjangan" required>
                                </div>
                                @error('nilai')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="hitung" class="col-md-4 col-form-label text-md-start">{{ __('Penghitungan') }}</label>
                            <div class="col-md-6">
                                <select id="hitung" class="form-select @error('hitung') is-invalid @enderror" name="hitung" required autocomplete="hitung" autofocus>
                                    <option value="">Pilih hitung</option>
                                    <option value="Perhari"{{ $tunjangan->hitung == 'Perhari' ? 'selected' : '' }}>Perhari</option>
                                    <option value="Perbulan"{{ $tunjangan->hitung == 'Perbulan' ? 'selected' : '' }}>Perbulan</option>
                                </select>
                                @error('hitung')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>          
                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <select id="divisi" class="form-select @error('divisi') is-invalid @enderror" name="divisi" required autocomplete="divisi" autofocus>
                                    <option value="">Pilih Divisi</option>
                                    <option value="All"{{ $tunjangan->divisi == 'All' ? 'selected' : '' }}>All</option>
                                    <option value="Sales"{{ $tunjangan->divisi == 'Sales' ? 'selected' : '' }}>Sales</option>
                                    <option value="Office"{{ $tunjangan->divisi == 'Office' ? 'selected' : '' }}>Office</option>
                                    <option value="Education"{{ $tunjangan->divisi == 'Education' ? 'selected' : '' }}>Education</option>
                                </select>
                                @error('divisi')
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
        $('#nilai').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });

        // Intercept form submission
        $('form').on('submit', function (e) {
            // Get the nilai input element
            let nilaiInput = $('#nilai');
            
            // Remove Rupiah format and set the cleaned value back to the input
            let cleanedValue = removeRupiahFormat(nilaiInput.val());
            nilaiInput.val(cleanedValue);
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
