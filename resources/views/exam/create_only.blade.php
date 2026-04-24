@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <h5 class="card-title text-center mb-4">{{ __('Tambah Exam Only') }}</h5>
                    <form method="POST" action="{{ route('exam.storeOnly') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="tanggal" class="col-md-4 col-form-label text-md-start">Tanggal Pengajuan</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('tanggal') is-invalid @enderror"
                                    name="tanggal" id="tanggal" readonly>
                                @error('tanggal')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_exam" class="col-md-4 col-form-label text-md-start">Tanggal Exam</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control @error('tanggal_exam') is-invalid @enderror"
                                    name="tanggal_exam" id="tanggal_exam" value="{{ old('tanggal_exam') }}" required>
                                @error('tanggal_exam')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <!-- Bagian select sales yang perlu diperbaiki -->
                        @if($isSPVSales)
                        <div class="row mb-3">
                            <label for="selected_sales" class="col-md-4 col-form-label text-md-start">Pilih Sales</label>
                            <div class="col-md-6">
                                <select name="selected_sales" id="selected_sales" class="form-control @error('selected_sales') is-invalid @enderror" required>
                                    <option value="">-- Pilih Sales --</option>
                                    @foreach($salesEmployees as $sales)
                                        <!-- GANTI: Gunakan id sebagai value, bukan nama_lengkap -->
                                        <option value="{{ $sales->id }}" {{ old('selected_sales') == $sales->id ? 'selected' : '' }}>
                                            {{ $sales->nama_lengkap }} ({{ $sales->kode_karyawan }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('selected_sales')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                                <small class="form-text text-muted">
                                    Sebagai SPV Sales, Anda dapat memilih karyawan sales untuk exam ini.
                                </small>
                            </div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <label for="materi" class="col-md-4 col-form-label text-md-start">Materi</label>
                            <div class="col-md-6">
                                <select name="materi" id="materi" class="form-control @error('materi') is-invalid @enderror" required>
                                    <option value="">-- Pilih Materi --</option>
                                    @foreach($materi as $m)
                                    <option value="{{ $m->id }}" {{ old('materi') == $m->id ? 'selected' : '' }}>
                                        {{ $m->nama_materi }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('materi')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kode_exam" class="col-md-4 col-form-label text-md-start">{{ __('Kode Exam') }}</label>
                            <div class="col-md-6">
                                <select name="kode_exam" id="kode_exam" class="form-select @error('kode_exam') is-invalid @enderror" required>
                                    <option value="" selected>Pilih Kode Exam</option>
                                    @foreach ($kode_exam as $list)
                                    <option value="{{ $list->kode_exam }}" {{ old('kode_exam') == $list->kode_exam ? 'selected' : '' }}>
                                        {{ $list->kode_exam }} - {{ $list->nama_exam }} - {{ $list->provider }} - {{ $list->vendor }}
                                    </option>
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
                            <label for="perusahaan_autocomplete" class="col-md-4 col-form-label text-md-start">Perusahaan</label>
                            <div class="col-md-6">
                                <input type="text"
                                    id="perusahaan_autocomplete"
                                    class="form-control @error('perusahaan') is-invalid @enderror"
                                    placeholder="Ketik nama perusahaan..."
                                    value="{{ old('perusahaan_autocomplete') }}" required>
                                <input type="hidden" name="perusahaan" id="perusahaan" value="{{ old('perusahaan') }}">
                                @error('perusahaan')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="mata_uang" class="col-md-4 col-form-label text-md-start">{{ __('Mata Uang') }}</label>
                            <div class="col-md-6">
                                <select name="mata_uang" id="mata_uang" class="form-select" required>
                                    <option value="" selected>Pilih Mata Uang</option>
                                    <option value="Rupiah" {{ old('mata_uang') == 'Rupiah' ? 'selected' : '' }}>Rupiah</option>
                                    <option value="Dollar" {{ old('mata_uang') == 'Dollar' ? 'selected' : '' }}>Dollar</option>
                                    <option value="Poundsterling" {{ old('mata_uang') == 'Poundsterling' ? 'selected' : '' }}>Poundsterling</option>
                                    <option value="Euro" {{ old('mata_uang') == 'Euro' ? 'selected' : '' }}>Euro</option>
                                    <option value="Franc Swiss" {{ old('mata_uang') == 'Franc Swiss' ? 'selected' : '' }}>Franc Swiss</option>
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
                                    <input type="text" step="0.01" class="form-control @error('harga') is-invalid @enderror"
                                           name="harga" id="harga" value="{{ old('harga') }}" required>
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
                                    <input type="text" class="form-control @error('kurs') is-invalid @enderror"
                                           name="kurs" id="kurs" value="{{ old('kurs') }}" required>
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
                                           name="biaya_admin" id="biaya_admin" value="{{ old('biaya_admin') }}" required>
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
                                           name="kurs_dollar" id="kurs_dollar" value="{{ old('kurs_dollar') }}" required>
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
                        </div>

                        <div class="row mb-3">
                            <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('pax') is-invalid @enderror"
                                       name="pax" id="pax" value="{{ old('pax') }}" required>
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
                                    <input type="text" class="form-control @error('total') is-invalid @enderror"
                                           name="total" id="total" readonly>
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
                                <input type="hidden" name="tipe" value="exam_only">
                                <input type="hidden" name="harga_total_rupiah" id="harga_total_rupiah">
                                <input type="hidden" name="total_final" id="total_final">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ui-autocomplete {
        z-index: 2000 !important;
        background: #fff;
        border: 1px solid #ddd;
        max-height: 200px;
        overflow-y: auto;
    }
</style>

@push('js')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

<script>
$(document).ready(function() {
    // Initialize submission date
    var today = new Date().toISOString().split('T')[0];
    $('#tanggal').val(today);

    // Currency symbol mapping
    var currencySymbols = {
        'Rupiah': '$',
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

    // Remove Rupiah format for calculations
    function removeRupiahFormat(angka) {
        return parseFloat(angka.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
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

    // Initialize autocomplete for company
    const perusahaanList = @json($perusahaan);
    $("#perusahaan_autocomplete").autocomplete({
        source: perusahaanList.map(p => ({
            label: p.nama_perusahaan,
            value: p.id
        })),
        minLength: 2,
        select: function(event, ui) {
            event.preventDefault();
            $("#perusahaan_autocomplete").val(ui.item.label);
            $("#perusahaan").val(ui.item.value);
        },
        change: function(event, ui) {
            if (!ui.item) {
                $("#perusahaan_autocomplete").val('');
                $("#perusahaan").val('');
                alert('Pilih perusahaan dari daftar!');
            }
        }
    });

    // Currency change event
    $('#mata_uang').on('change', function() {
        var val = $(this).val();
        if (!val) {
            alert('Pilih mata uang terlebih dahulu!');
            $('#harga, #kurs, #biaya_admin, #kurs_dollar, #harga_rupiah, #total').val('');
            $('#kurs_harga_div, #kurs_dollar_div').hide();
            return;
        }

        $('#currency-symbol').text(currencySymbols[val] || '$');
        $('#symbol').text(kursSymbols[val] || '');

        // Show/hide kurs fields based on currency
        if (val === 'Rupiah' || val === 'Dollar') {
            $('#kurs_harga_div').hide();
            $('#kurs').val('').prop('disabled', true);
        } else {
            $('#kurs_harga_div').show();
            $('#kurs').prop('disabled', false);
        }
        $('#kurs_dollar_div').show();
        $('#kurs_dollar').prop('disabled', false);
        $('#biaya_admin').prop('disabled', false);

        hitungTotal();
    });

    // Format and validate numeric inputs
    $('#harga, #kurs, #biaya_admin, #kurs_dollar').on('input', function() {
        if (validateInput($(this), $(this).attr('name'))) {
            $(this).val(formatRupiah($(this).val()));
            hitungTotal();
        }
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
        $('#harga_total_rupiah').val(totalHarga * pax);
        $('#total_final').val(totalNet);
    }

    // Validate before submit
    $('form').on('submit', function(e) {
        @if($isSPVSales)
        if (!$('#selected_sales').val()) {
            alert('Pilih sales karyawan terlebih dahulu!');
            e.preventDefault();
            return false;
        }
        @endif

        if (!$('#perusahaan').val()) {
            alert('Pilih perusahaan terlebih dahulu!');
            e.preventDefault();
            return false;
        }
        if (!$('#mata_uang').val()) {
            alert('Pilih mata uang terlebih dahulu!');
            e.preventDefault();
            return false;
        }
        if (removeRupiahFormat($('#total').val()) <= 0) {
            alert('Total tidak boleh nol atau negatif!');
            e.preventDefault();
            return false;
        }

        // Remove format only for editable fields
        $('#harga').val(removeRupiahFormat($('#harga').val()));
        $('#kurs').val(removeRupiahFormat($('#kurs').val()));
        $('#biaya_admin').val(removeRupiahFormat($('#biaya_admin').val()));
        $('#kurs_dollar').val(removeRupiahFormat($('#kurs_dollar').val()));
        $('#total').val(removeRupiahFormat($('#total').val()));
    });

    // Initialize values from old input if available
    @if(old('mata_uang'))
    $('#mata_uang').trigger('change');
    @endif

    // Set company autocomplete if old value exists
    @if(old('perusahaan'))
    var selectedCompany = perusahaanList.find(p => p.value == '{{ old('perusahaan') }}');
    if (selectedCompany) {
        $('#perusahaan_autocomplete').val(selectedCompany.label);
        $('#perusahaan').val(selectedCompany.value);
    }
    @endif
});
</script>
@endpush
@endsection
