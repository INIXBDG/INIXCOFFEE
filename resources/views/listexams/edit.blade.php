@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-5 px-4">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Edit List Exam') }}</h5>
                    <form method="POST" action="{{ route('listexams.update', $exam->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="provider" class="col-md-4 col-form-label text-md-start">{{ __('Provider') }}</label>
                            <div class="col-md-6">
                                <select class="form-select" name="provider" id="provider">
                                    <option value="">Pilih Provider</option>
                                    <option value="Pearson Vue" {{ $exam->provider == 'Pearson Vue' ? 'selected' : '' }}>Pearson Vue</option>
                                    <option value="On Vue" {{ $exam->provider == 'On Vue' ? 'selected' : '' }}>On Vue</option>
                                    <option value="PSI" {{ $exam->provider == 'PSI' ? 'selected' : '' }}>PSI</option>
                                    <option value="Exam Shield" {{ $exam->provider == 'Exam Shield' ? 'selected' : '' }}>Exam Shield</option>
                                    <option value="Examity" {{ $exam->provider == 'Examity' ? 'selected' : '' }}>Examity</option>
                                    @foreach ($provider as $p)
                                        <option value="{{ $p->nama }}" {{ $exam->provider == $p->nama ? 'selected' : '' }}>{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                                @error('provider')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nama_exam" class="col-md-4 col-form-label text-md-start">{{ __('Nama Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama_exam" id="nama_exam" value="{{ $exam->nama_exam }}">
                                @error('nama_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kode_exam" class="col-md-4 col-form-label text-md-start">{{ __('Kode Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="kode_exam" id="kode_exam" value="{{ $exam->kode_exam }}">
                                @error('kode_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="vendor" class="col-md-4 col-form-label text-md-start">{{ __('Vendor') }}</label>
                            <div class="col-md-6">
                                <select class="form-select" name="vendor" id="vendor">
                                    <option value="">Pilih Vendor</option>
                                    <option value="AWS" {{ $exam->vendor == 'AWS' ? 'selected' : '' }}>AWS</option>
                                    <option value="Cisco" {{ $exam->vendor == 'Cisco' ? 'selected' : '' }}>Cisco</option>
                                    <option value="EC-Council" {{ $exam->vendor == 'EC-Council' ? 'selected' : '' }}>EC-Council</option>
                                    <option value="EPI" {{ $exam->vendor == 'EPI' ? 'selected' : '' }}>EPI</option>
                                    <option value="Google" {{ $exam->vendor == 'Google' ? 'selected' : '' }}>Google</option>
                                    <option value="ISACA" {{ $exam->vendor == 'ISACA' ? 'selected' : '' }}>ISACA</option>
                                    <option value="LSP" {{ $exam->vendor == 'LSP' ? 'selected' : '' }}>LSP</option>
                                    <option value="Microsoft" {{ $exam->vendor == 'Microsoft' ? 'selected' : '' }}>Microsoft</option>
                                    <option value="Mikrotik" {{ $exam->vendor == 'Mikrotik' ? 'selected' : '' }}>Mikrotik</option>
                                    <option value="CompTIA" {{ $exam->vendor == 'CompTIA' ? 'selected' : '' }}>CompTIA</option>
                                    <option value="BNSP" {{ $exam->vendor == 'BNSP' ? 'selected' : '' }}>BNSP</option>
                                    <option value="Inixindo Certificate" {{ $exam->vendor == 'Inixindo Certificate' ? 'selected' : '' }}>Inixindo Certificate</option>
                                    @foreach ($vendor as $v)
                                        <option value="{{ $v->nama }}" {{ $exam->vendor == $v->nama ? 'selected' : '' }}>{{ $v->nama }}</option>
                                    @endforeach
                                </select>
                                @error('vendor')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="mata_uang" class="col-md-4 col-form-label text-md-start">{{ __('Mata Uang') }}</label>
                            <div class="col-md-6">
                                <select name="mata_uang" id="mata_uang" class="form-select" required>
                                    <option value="" selected>Pilih Mata Uang</option>
                                    <option value="Rupiah" {{ $exam->mata_uang == 'Rupiah' ? 'selected' : '' }}>Rupiah</option>
                                    <option value="Dollar" {{ $exam->mata_uang == 'Dollar' ? 'selected' : '' }}>Dollar</option>
                                    <option value="Poundsterling" {{ $exam->mata_uang == 'Poundsterling' ? 'selected' : '' }}>Poundsterling</option>
                                    <option value="Euro" {{ $exam->mata_uang == 'Euro' ? 'selected' : '' }}>Euro</option>
                                    <option value="Franc Swiss" {{ $exam->mata_uang == 'Franc Swiss' ? 'selected' : '' }}>Franc Swiss</option>
                                </select>
                                @error('mata_uang')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3" id="harga_div">
                            <label for="harga" class="col-md-4 col-form-label text-md-start">{{ __('Harga') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="currency-symbol">$</span>
                                    <input type="text" class="form-control @error('harga') is-invalid @enderror" 
                                           name="harga" id="harga" required value="{{ $exam->mata_uang === 'Rupiah' ? number_format($exam->harga_exam ?? 0, 0, ',', '.') : number_format($exam->harga_exam ?? 0, 2, '.', ',') }}">
                                </div>
                                @error('harga')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- <div class="row mb-3" id="kurs_harga_div">
                            <label for="kurs" class="col-md-4 col-form-label text-md-start"><div class="d-flex">Kurs<p id="symbol" class="mx-2"></p></div></label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('kurs') is-invalid @enderror" 
                                           name="kurs" id="kurs" value="{{ number_format((int) $exam->kurs, 0, ',', '.') }}" required>
                                </div>
                                @error('kurs')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="biaya_admin_div">
                            <label for="biaya_admin" class="col-md-4 col-form-label text-md-start">{{ __('Biaya Admin') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">$</span>
                                    <input type="text" step="0.01" class="form-control @error('biaya_admin') is-invalid @enderror" 
                                           name="biaya_admin" id="biaya_admin" value="{{ number_format((int) $exam->biaya_admin, 0, ',', '.') }}" required>
                                </div>
                                @error('biaya_admin')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="kurs_dollar_div">
                            <label for="kurs_dollar" class="col-md-4 col-form-label text-md-start">{{ __('Kurs $') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('kurs_dollar') is-invalid @enderror" 
                                           name="kurs_dollar" id="kurs_dollar" value="{{ number_format((int) $exam->kurs_dollar, 0, ',', '.') }}" required>
                                </div>
                                @error('kurs_dollar')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="harga_rupiah_div">
                            <label for="harga_rupiah" class="col-md-4 col-form-label text-md-start">{{ __('Harga (dalam Rp.)') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" step="0.01" class="form-control @error('harga_rupiah') is-invalid @enderror" 
                                           name="harga_rupiah" id="harga_rupiah" readonly>
                                </div>
                                @error('harga_rupiah')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> --}}
                         
                        <div class="row mb-3">
                            <label for="estimasi_durasi_booking" class="col-md-4 col-form-label text-md-start">{{ __('Estimasi Durasi Booking') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="estimasi_durasi_booking" id="estimasi_durasi_booking" value={{ $exam->estimasi_durasi_booking }}>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="valid_until" class="col-md-4 col-form-label text-md-start">{{ __('Valid Until') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="valid_until" id="valid_until" value="{{ $exam->valid_until }}">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="note" class="col-md-4 col-form-label text-md-start">{{ __('Note/Syarat Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="note" id="note" value={{ $exam->note }}>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
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
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

    // Currency symbol mapping
    var currencySymbols = {
        'Rupiah': 'Rp.',
        'Dollar': '$',
        'Poundsterling': 'Â£',
        'Euro': 'â‚¬',
        'Franc Swiss': 'CHF'
    };

    // Currency symbol mapping for kurs field
    var kursSymbols = {
        'Rupiah': 'Rp.',
        'Dollar': '$',
        'Poundsterling': 'Â£',
        'Euro': 'â‚¬',
        'Franc Swiss': 'CHF'
    };

    // Format number as Rupiah
    function formatRupiah(angka, prefix) {
        var numberString = angka.toString().replace(/[^,\d]/g, ''),
            split = numberString.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return (prefix === undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : ''));
    }

    // Set currency format saat pertama load
    function setCurrencyUI(mataUang) {
        if (!mataUang) return;

        $('#currency-symbol').text(currencySymbols[mataUang] || '$');
        $('#symbol').text(kursSymbols[mataUang] || '');

        if (mataUang === 'Rupiah' || mataUang === 'Dollar') {
            $('#kurs_harga_div').hide();
            $('#kurs').prop('disabled', true);
        } else {
            $('#kurs_harga_div').show();
            $('#kurs').prop('disabled', false);
        }

        $('#kurs_dollar_div').show();
        $('#kurs_dollar').prop('disabled', false);
        $('#biaya_admin').prop('disabled', false);
    }


    // Remove Rupiah format for calculations
    function removeRupiahFormat(value) {
        if (!value) return 0;

        // jika ada titik desimal → USD
        if (value.includes('.')) {
            return parseFloat(value);
        }

        // Rupiah
        return parseFloat(value.replace(/[^\d]/g, '')) || 0;
    }

    // Validate numeric input
    function validateInput(input, fieldName) {
        let value = parseFloat(input.val()) || 0;
        if (value < 0) {
            alert(`${fieldName} tidak boleh negatif!`);
            input.val('');
            return false;
        }
        return true;
    }

    // Currency change event
    $('#mata_uang').on('change', function() {
        let val = $(this).val();
        if (!val) return;

        setCurrencyUI(val);
        hitungTotal();
    });


    $('#harga').on('input', function () {
        let mataUang = $('#mata_uang').val();
        let value = $(this).val();

        if (mataUang === 'Rupiah') {
            $(this).val(formatRupiah(value));
        } else {
            // Dollar, Euro, Pound, CHF
            $(this).val(formatUSD(value));
        }

        hitungTotal();
    });

    // Pax event
    $('#pax').on('input', function() {
        if (validateInput($(this), 'Pax')) {
            hitungTotal();
        }
    });

    // Calculate total
    function hitungTotal() {
        var harga = removeRupiahFormat($('#harga').val()) || 0;
        var pax = parseInt($('#pax').val()) || 0;
        var biayaAdmin = removeRupiahFormat($('#biaya_admin').val()) || 0;
        var kurs = removeRupiahFormat($('#kurs').val()) || 1; // Default 1 kalau kosong
        var kursDollar = removeRupiahFormat($('#kurs_dollar').val()) || 1; // Default 1 kalau kosong
        var mataUang = $('#mata_uang').val();

        if (!mataUang) {
            $('#harga_rupiah, #total, #harga_total_rupiah, #total_final').val('');
            return;
        }

        var totalHarga = 0;
        switch (mataUang) {
            case 'Rupiah':
            case 'Dollar':
                totalHarga = (harga + biayaAdmin) * kursDollar;
                break;
            case 'Poundsterling':
            case 'Euro':
            case 'Franc Swiss':
                totalHarga = (harga * kurs) + (biayaAdmin * kursDollar);
                break;
            default:
                totalHarga = 0;
                break;
        }

        var totalNet = totalHarga * pax;

        // Update display
        $('#harga_rupiah').val(formatRupiah(totalHarga.toString()));
        $('#total').val(formatRupiah(totalNet.toString()));

        // Update hidden fields
        $('#total_final').val(totalNet);
    }

    let initialCurrency = $('#mata_uang').val();
    setCurrencyUI(initialCurrency);
});
</script>
@endpush
