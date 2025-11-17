@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Edit Surat Perjalanan') }}</h5>
                    <form method="POST" action="{{ route('expensehub.updateInvoice', $expenseHub->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_karyawan" value="{{ $expenseHub->karyawan->id }}">
                                <input disabled id="nama_karyawan" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('nama_karyawan') is-invalid @enderror" name="nama_karyawan" value="{{ $expenseHub->karyawan->nama_lengkap }}">
                                @error('nama_karyawan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input disabled id="divisi" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $expenseHub->karyawan->divisi }}">
                                @error('divisi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Bukti Transaksi / Invoice') }}</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control" name="invoice" id="invoice">
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
                                    {{ __('Simpan Perubahan') }}
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

    // Apply Rupiah format to the specified inputs
    setupInputFormatter('ratemakan');
    setupInputFormatter('ratespj');
    setupInputFormatter('ratetaksi');

    $(document).ready(function() {
        function calculateTotal() {
            var durasi = parseFloat($('#durasi').val()) || 0;

            // Unmask and convert to float
            var ratemakan = parseFloat($('#ratemakan').val().replace(/\./g, '')) || 0;
            var ratespj = parseFloat($('#ratespj').val().replace(/\./g, '')) || 0;
            var ratetaksi = parseFloat($('#ratetaksi').val().replace(/\./g, '')) || 0;

            var totalmakan = ratemakan * durasi;
            var totalspj = ratespj * durasi;
            var totaltaksi = ratetaksi * durasi;

            var total = totalmakan + totalspj + totaltaksi;

            // Display total with Rupiah format
            $('#total').val(formatRupiah(total.toString()));
        }

        $('#durasi, #ratemakan, #ratespj, #ratetaksi').on('input', calculateTotal);

        // Unmask and prepare data before form submission
        $('form').on('submit', function() {
            $('#ratemakan').val($('#ratemakan').val().replace(/\./g, ''));
            $('#ratespj').val($('#ratespj').val().replace(/\./g, ''));
            $('#ratetaksi').val($('#ratetaksi').val().replace(/\./g, ''));
            $('#total').val($('#total').val().replace(/\./g, ''));
        });
    });
</script>

@endsection