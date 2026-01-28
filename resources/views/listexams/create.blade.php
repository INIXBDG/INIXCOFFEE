@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-5">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Tambah List Exam') }}</h5>
                    <form method="POST" action="{{ route('listexams.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="provider" class="col-md-4 col-form-label text-md-start">{{ __('Provider') }}</label>
                            <div class="col-md-6">
                                <select class="form-select" name="provider" id="provider">
                                    <option value="">Pilih Provider</option>
                                    <option value="Pearson Vue">Pearson Vue</option>
                                    <option value="On Vue">On Vue</option>
                                    <option value="PSI">PSI</option>
                                    <option value="Exam Shield">Exam Shield</option>
                                    <option value="Examity">Examity</option>
                                    @foreach ($provider as $p)
                                        <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                                @error('provider')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-2 ">
                                <a href="#" class="btn click-primary d-flex" id="addProvider" data-bs-toggle="modal" data-bs-target="#providerModal">
                                    <img src="{{ asset('icon/plus.svg') }}" class="img-responsive" width="20px"> <p style="margin-bottom: 0; margin-top:3px">Provider</p>
                                </a>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nama_exam" class="col-md-4 col-form-label text-md-start">{{ __('Nama Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama_exam" id="nama_exam">
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
                                <input type="text" class="form-control" name="kode_exam" id="kode_exam">
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
                                    <option value="AWS">AWS</option>
                                    <option value="Cisco">Cisco</option>
                                    <option value="EC-Council">EC-Council</option>
                                    <option value="EPI">EPI</option>
                                    <option value="Google">Google</option>
                                    <option value="ISACA">ISACA</option>
                                    <option value="LSP">LSP</option>
                                    <option value="Microsoft">Microsoft</option>
                                    <option value="Mikrotik">Mikrotik</option>
                                    <option value="CompTIA">CompTIA</option>
                                    <option value="BNSP">BNSP</option>
                                    <option value="Inixindo Certificate">Inixindo Certificate</option>
                                    @foreach ($vendor as $v)
                                        <option value="{{ $v->nama }}">{{ $v->nama }}</option>
                                    @endforeach
                                </select>
                                @error('vendor')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-2 ">
                                <a href="#" class="btn click-primary d-flex" id="addVendor" data-bs-toggle="modal" data-bs-target="#vendorModal">
                                    <img src="{{ asset('icon/plus.svg') }}" class="img-responsive" width="20px"> <p style="margin-bottom: 0; margin-top:3px">Vendor</p>
                                </a>
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

                        {{-- <div class="row mb-3" id="kurs_harga_div">
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
                        </div> --}}
                         
                        <div class="row mb-3">
                            <label for="estimasi_durasi_booking" class="col-md-4 col-form-label text-md-start">{{ __('Estimasi Durasi Booking') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="estimasi_durasi_booking" id="estimasi_durasi_booking">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="valid_until" class="col-md-4 col-form-label text-md-start">{{ __('Valid Until') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="valid_until" id="valid_until">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="valid_until" class="col-md-4 col-form-label text-md-start">{{ __('Valid Until') }}</label>
                            <div class="col-md-6">
                                <input id="valid_until" type="date" class="form-control" name="valid_until">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="note" class="col-md-4 col-form-label text-md-start">{{ __('Note/Syarat Exam') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="note" id="note">
                            </div>
                        </div>

                        <input type="hidden" name="total_final" id="total_final">

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

<!-- Modal for Adding Provider -->
<div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="providerModalLabel">Tambah Provider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="providerForm">
          <div class="mb-3">
            <label for="providerName" class="form-label">Nama Provider</label>
            <input type="text" class="form-control" id="providerName" name="providerName">
          </div>
          <button type="button" class="btn btn-primary" id="saveProvider">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Adding Vendor -->
<div class="modal fade" id="vendorModal" tabindex="-1" aria-labelledby="vendorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vendorModalLabel">Tambah Vendor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="vendorForm">
          <div class="mb-3">
            <label for="vendorName" class="form-label">Nama Vendor</label>
            <input type="text" class="form-control" id="vendorName" name="vendorName">
          </div>
          <button type="button" class="btn btn-primary" id="saveVendor">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#saveProvider').on('click', function() {
        var providerName = $('#providerName').val();
        if (providerName) {
            $.ajax({
                url: "{{ route('providers.store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nama: providerName
                },
                success: function(response) {
                    setTimeout(function(){// wait for 5 secs(2)
                        // console.log(response);
                        // $('#provider').append(new Option(response.name, response.name));
                        $('#providerModal').modal('hide');
                        $('#providerName').val('');
                        location.reload(); // then reload the page.(3)
                    }, 2000);
                },
                error: function(response) {
                    alert('Error adding provider');
                }
            });
        }
    });

    $('#saveVendor').on('click', function() {
        var vendorName = $('#vendorName').val();
        if (vendorName) {
            $.ajax({
                url: "{{ route('vendors.store') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nama: vendorName
                },
                success: function(response) {
                    setTimeout(function(){// wait for 5 secs(2)
                        $('#vendorModal').modal('hide');
                        $('#vendorName').val('');
                        location.reload(); // then reload the page.(3)
                        // console.log(response);
                    }, 2000);
                },
                error: function(response) {
                    alert('Error adding vendor');
                }
            });
        }
    });

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
        $('#total_final').val(totalNet);
    }
});
</script>
@endsection
