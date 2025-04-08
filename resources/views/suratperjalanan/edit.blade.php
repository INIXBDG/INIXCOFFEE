@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Rate SPJ') }}</h5>
                    <form method="POST" action="{{ route('suratperjalanan.update', $suratperjalanan->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="nama_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">
                                <input type="hidden" name="approval_hrd" value="1">
                                <input disabled id="nama_karyawan" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('nama_karyawan') is-invalid @enderror" name="nama_karyawan" value="{{ $karyawan->nama_lengkap }}">
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
                                <input disabled id="divisi" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $karyawan->divisi }}" >
                                @error('divisi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Jenis Travel') }}</label>
                            <div class="col-md-6">
                                <select disabled name="tipe" id="tipe" class="form-select">
                                    <option value="-">Pilih Jenis Travel</option>
                                    <option value="Domestik" {{ $suratperjalanan->tipe == 'Domestik' ? 'selected' : '' }}>Travel Domestik</option>
                                    <option value="Internasional" {{ $suratperjalanan->tipe == 'Internasional' ? 'selected' : '' }}>Travel Internasional</option>
                                </select>
                                @error('tipe')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="kontak-row">
                            <label for="tujuan" class="col-md-4 col-form-label text-md-start">{{ __('Tujuan') }}</label>
                            <div class="col-md-6">
                                <input disabled id="tujuan" type="text" placeholder="Kota yang dituju" class="form-control @error('tujuan') is-invalid @enderror" name="tujuan" value="{{ $suratperjalanan->tujuan }}" >
                                @error('tujuan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_berangkat-row">
                            <label for="tanggal_berangkat" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Berangkat') }}</label>
                            <div class="col-md-6">
                                <input disabled type="datetime-local" class="form-control" name="tanggal_berangkat" id="tanggal_berangkat" value="{{ $suratperjalanan->tanggal_berangkat }}">
                                @error('tanggal_berangkat')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_pulang-row">
                            <label for="tanggal_pulang" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Kedatangan') }}</label>
                            <div class="col-md-6">
                                <input disabled type="datetime-local" class="form-control" name="tanggal_pulang" id="tanggal_pulang" value="{{ $suratperjalanan->tanggal_pulang }}">
                                @error('tanggal_pulang')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="durasi-row">
                            <label for="durasi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi Hari') }}</label>
                            <div class="col-md-6">
                                <input readonly id="durasi" type="text" placeholder="Durasi" class="form-control @error('durasi') is-invalid @enderror" name="durasi" value="{{ $suratperjalanan->durasi }}">
                                @error('durasi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="alasan" class="col-md-4 col-form-label text-md-start">{{ __('Alasan Perjalanan') }}</label>
                            <div class="col-md-6">
                                <input disabled id="alasan" type="text" placeholder="Alasan" class="form-control @error('alasan') is-invalid @enderror" name="alasan" value="{{ $suratperjalanan->alasan }}">
                                @error('alasan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="harga_rupiah_div">
                            <label for="ratemakan" class="col-md-4 col-form-label text-md-start">{{ __('Uang Makan (dalam Rp.)') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" step="0.01" class="form-control @error('ratemakan') is-invalid @enderror" name="ratemakan" id="ratemakan" >
                                </div>
                                @error('ratemakan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3" id="harga_rupiah_div">
                            <label for="ratespj" class="col-md-4 col-form-label text-md-start">{{ __('SPJ (dalam Rp.)') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" step="0.01" class="form-control @error('ratespj') is-invalid @enderror" name="ratespj" id="ratespj" >
                                </div>
                                @error('ratespj')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3" id="harga_rupiah_div">
                            <label for="ratetaksi" class="col-md-4 col-form-label text-md-start">{{ __('Taksi (dalam Rp.)') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" step="0.01" class="form-control @error('ratetaksi') is-invalid @enderror" name="ratetaksi" id="ratetaksi" >
                                </div>
                                @error('ratetaksi')
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

    $(document).ready(function () {
        function calculateTotal() {
            var durasi = parseFloat($('#durasi').val()) || 0;

            // Unmask and convert to float
            var ratemakan = parseFloat($('#ratemakan').val().replace(/\./g, '')) || 0;
            var ratespj = parseFloat($('#ratespj').val().replace(/\./g, '')) || 0;
            var ratetaksi = parseFloat($('#ratetaksi').val().replace(/\./g, '')) || 0;

            var totalmakan = ratemakan * durasi;
            var totalspj = ratespj * durasi;
            var totaltaksi = ratetaksi;

            var total = totalmakan + totalspj + totaltaksi;

            // Display total with Rupiah format
            $('#total').val(formatRupiah(total.toString()));
        }

        $('#durasi, #ratemakan, #ratespj, #ratetaksi').on('input', calculateTotal);

        // Unmask and prepare data before form submission
        $('form').on('submit', function () {
            $('#ratemakan').val($('#ratemakan').val().replace(/\./g, ''));
            $('#ratespj').val($('#ratespj').val().replace(/\./g, ''));
            $('#ratetaksi').val($('#ratetaksi').val().replace(/\./g, ''));
            $('#total').val($('#total').val().replace(/\./g, ''));
        });
    });

</script>

@endsection
