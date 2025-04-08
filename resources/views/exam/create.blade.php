@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    {{-- <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a> --}}
                    <h5 class="card-title text-center mb-4">{{ __('Tambah Pengajuan Exam') }}</h5>
                    <form method="POST" action="{{ route('exam.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="tanggal_pengajuan" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Pengajuan') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control @error('tanggal_pengajuan') is-invalid @enderror" name="tanggal_pengajuan" id="tanggal_pengajuan" required>
                                @error('tanggal_pengajuan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <input type="hidden" name="id_rkm" value="{{ $rkm->id }}">

                        <div class="row mb-3">
                            <label for="materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                            <div class="col-md-6">
                                <input type="text" readonly class="form-control @error('materi') is-invalid @enderror" name="materi" id="materi" value="{{ $rkm->materi->nama_materi }}" required>
                                @error('materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="perusahaan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Perusahaan') }}</label>
                            <div class="col-md-6">
                                <input type="text" readonly class="form-control @error('perusahaan') is-invalid @enderror" name="perusahaan" id="perusahaan" value="{{ $rkm->perusahaan->nama_perusahaan }}" required>
                                @error('perusahaan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kode_exam" class="col-md-4 col-form-label text-md-start">{{ __('Kode Exam') }}</label>
                            <div class="col-md-6">
                                <select name="kode_exam" id="kode_exam" class="form-select">
                                    <option value="" selected>Pilih Kode Exam</option>
                                    @foreach ($kode_exam as $list)
                                    <option value="{{ $list->kode_exam }}">{{ $list->kode_exam }} - {{ $list->nama_exam }} - {{ $list->provider }} - {{ $list->vendor }}</option>
                                    @endforeach
                                </select>
                                @error('kode_exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="mata_uang" class="col-md-4 col-form-label text-md-start">{{ __('Mata Uang') }}</label>
                            <div class="col-md-6">
                                <select name="mata_uang" id="mata_uang" class="form-select">
                                    <option value="" selected>Pilih Mata Uang</option>
                                    <option value="Rupiah">Rupiah</option>
                                    <option value="Dollar">Dollar</option>
                                    <option value="Poundsterling">Poundsterling</option>
                                    <option value="Euro">Euro</option>
                                    <option value="Franc Swiss">Franc Swiss</option>
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
                                    <input type="text" step="0.01" class="form-control @error('harga') is-invalid @enderror" name="harga" id="harga" required>
                                </div>
                                @error('harga')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="kurs_harga_div">
                            <label for="kurs" class="col-md-4 col-form-label text-md-start"><div class="d-flex">Kurs<p id="symbol" class="mx-2"></p></div></label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('kurs') is-invalid @enderror" name="kurs" id="kurs" required>
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
                                    <input type="text" step="0.01" class="form-control @error('biaya_admin') is-invalid @enderror" name="biaya_admin" id="biaya_admin" required>
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
                                    <input type="text" class="form-control @error('kurs_dollar') is-invalid @enderror" name="kurs_dollar" id="kurs_dollar" required>
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
                                    <input type="text" step="0.01" class="form-control @error('harga_rupiah') is-invalid @enderror" name="harga_rupiah" id="harga_rupiah" readonly required>
                                </div>
                                @error('harga_rupiah')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('pax') is-invalid @enderror" name="pax" id="pax">
                                @error('pax')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="total" class="col-md-4 col-form-label text-md-start">{{ __('Total') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('total') is-invalid @enderror" name="total" id="total" readonly>
                                </div>
                                @error('total')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
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
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-price-format/2.2.0/jquery.priceformat.min.js" integrity="sha512-qHlEL6N+fxDGsJoPhq/jFcxJkRURgMerSFixe39WoYaB2oj91lvJXYDVyEO1+tOuWO+sBtUGHhl3v3hUp1tGMA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
<script>

$(document).ready(function() {
    var today = new Date().toISOString().split('T')[0];
    $('#tanggal_pengajuan').val(today);
    var paxInput = $('#pax');
    var totalInput = $('#total');
    $('#mata_uang, #harga, #kurs, #biaya_admin, #kurs_dollar').on('input change', function() {
        updateHargaRupiah();
    });

    // Apply Rupiah format to kurs, kurs_dollar, and harga_rupiah on input
    $('#kurs, #kurs_dollar, #harga_rupiah').on('input', function() {
        $(this).val(formatRupiah($(this).val()));
    });

    // Function to update Harga Rupiah
    function updateHargaRupiah() {
        const selectedCurrency = $('#mata_uang').val();
        const harga = parseFloat(($('#harga').val())) || 0;
        const kurs = parseFloat(removeRupiahFormat($('#kurs').val())) || 0;
        const biayaAdmin = parseFloat(removeRupiahFormat($('#biaya_admin').val())) || 0;
        const kursDollar = parseFloat(removeRupiahFormat($('#kurs_dollar').val())) || 0;
        let totalHarga = 0;

        // Calculate totalHarga based on selectedCurrency
        switch (selectedCurrency) {
            case 'Rupiah':
                totalHarga = (harga * kurs) + (biayaAdmin * kursDollar);
                break;
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

        // Update harga_rupiah with formatted Rupiah
        $('#harga_rupiah').val(formatRupiah(totalHarga.toString()));
    }

    // Function to format numbers as Rupiah
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

    // Function to remove Rupiah format before calculations
    function removeRupiahFormat(angka) {
        return parseFloat(angka.replace(/[^\d,]/g, '').replace(',', '.'));
    }

    paxInput.change(function() {
        // Mengambil nilai dari input pax
            var pax = parseInt(paxInput.val());
            var hargaRupiah = $('#harga_rupiah').val()
            var totalRupiah = removeRupiahFormat(hargaRupiah) * pax;
            // console.log(totalRupiah);
            totalInput.val(formatRupiah(totalRupiah));
    });

    $('form').on('submit', function() {
        $('#kurs').val(removeRupiahFormat($('#kurs').val()));
        $('#kurs_dollar').val(removeRupiahFormat($('#kurs_dollar').val()));
        $('#harga_rupiah').val(removeRupiahFormat($('#harga_rupiah').val()));
        $('#total').val(removeRupiahFormat($('#total').val()));
    });
});


</script>
@endpush

@endsection

