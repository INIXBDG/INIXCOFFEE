@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Kembali</a>
                <h5 class="card-title text-center mb-4">{{ __('Pengajuan Souvenir') }}</h5>
                    <form method="POST" action="{{ route('pengajuansouvenir.store') }}" enctype="multipart/form-data">
                        @csrf
                        <!-- ID Karyawan (Info Pengaju) -->
                        <div class="row mb-3">
                            <label for="id_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">
                                <input disabled id="nama_karyawan" type="text" class="form-control" name="nama_karyawan" value="{{ $karyawan->nama_lengkap }}" autocomplete="nama_karyawan" autofocus>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input disabled id="divisi" type="text" class="form-control" name="divisi" value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-center">Detail Kebutuhan</h6>

                        <!-- ID Vendor -->
                        <div class="row mb-3">
                            <label for="id_vendor" class="col-md-4 col-form-label text-md-start">{{ __('Vendor') }}</label>
                            <div class="col-md-6">
                                {{-- TODO: Anda harus mengirimkan $vendors dari Controller --}}
                                <select name="id_vendor" id="id_vendor" class="form-select @error('id_vendor') is-invalid @enderror" required>
                                    <option value="" disabled selected>Pilih Vendor</option>
                                    {{-- Contoh loop (pastikan $vendors ada):
                                    @if(isset($vendors))
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ old('id_vendor') == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->nama_vendor }}
                                            </option>
                                        @endforeach
                                    @endif
                                    --}}
                                    <option value="1">Vendor Dummy 1</option> {{-- Hapus ini jika $vendors sudah ada --}}
                                </select>
                                @error('id_vendor')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- ID Souvenir -->
                        <div class="row mb-3">
                            <label for="id_souvenir" class="col-md-4 col-form-label text-md-start">{{ __('Souvenir') }}</label>
                            <div class="col-md-6">
                                {{-- TODO: Anda harus mengirimkan $souvenirs dari Controller --}}
                                <select name="id_souvenir" id="id_souvenir" class="form-select @error('id_souvenir') is-invalid @enderror" required>
                                    <option value="" disabled selected>Pilih Souvenir</option>
                                    {{-- Contoh loop (pastikan $souvenirs ada):
                                    @if(isset($souvenirs))
                                        @foreach($souvenirs as $souvenir)
                                            <option value="{{ $souvenir->id }}" {{ old('id_souvenir') == $souvenir->id ? 'selected' : '' }}>
                                                {{ $souvenir->nama_souvenir }}
                                            </option>
                                        @endforeach
                                    @endif
                                    --}}
                                    <option value="1">Souvenir Dummy 1</option> {{-- Hapus ini jika $souvenirs sudah ada --}}
                                </select>
                                @error('id_souvenir')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- Pax -->
                        <div class="row mb-3">
                            <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax (Jumlah)') }}</label>
                            <div class="col-md-6">
                                <input id="pax" type="number" class="form-control @error('pax') is-invalid @enderror" name="pax" value="{{ old('pax') }}" required min="1">
                                @error('pax')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- Harga Satuan -->
                        <div class="row mb-3">
                            <label for="harga_satuan" class="col-md-4 col-form-label text-md-start">{{ __('Harga Satuan') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input id="harga_satuan" type="text" class="form-control @error('harga_satuan') is-invalid @enderror" name="harga_satuan" value="{{ old('harga_satuan') }}" required>
                                </div>
                                @error('harga_satuan')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <!-- Harga Total -->
                        <div class="row mb-3">
                            <label for="harga_total" class="col-md-4 col-form-label text-md-start">{{ __('Harga Total') }}</slabel>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input id="harga_total" type="text" class="form-control @error('harga_total') is-invalid @enderror" name="harga_total" value="{{ old('harga_total') }}" required readonly>
                                </div>
                                <div class="form-text">Harga total dihitung otomatis (Pax x Harga Satuan)</div>
                                @error('harga_total')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        {{-- Tombol Add/Remove Item dari contoh barang telah dihapus --}}

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
        // 1. Setup formatter untuk harga_satuan
        setupInputFormatter('#harga_satuan');

        // 2. Setup kalkulasi otomatis
        // Dengar perubahan di input 'pax' dan 'harga_satuan'
        $('#pax, #harga_satuan').on('input', function() {
            calculateTotal();
        });

        // 3. Setup form submit untuk membersihkan format Rupiah
        $('form').on('submit', function (e) {
            e.preventDefault(); // Cegah submit standar

            // Hapus titik dari input harga sebelum submit
            $('#harga_satuan').val(getNumericValue('#harga_satuan'));
            $('#harga_total').val(getNumericValue('#harga_total'));

            this.submit(); // Lanjutkan submit form
        });
    });

    /**
     * Membersihkan input dan mengembalikan nilai angka
     */
    function getNumericValue(selector) {
        var value = $(selector).val().replace(/\./g, ''); // Hapus titik
        return parseInt(value) || 0;
    }

    /**
     * Menghitung total dan menampilkannya
     */
    function calculateTotal() {
        var pax = getNumericValue('#pax');
        var hargaSatuan = getNumericValue('#harga_satuan');

        var total = pax * hargaSatuan;

        // Tampilkan total yang sudah diformat ke input harga_total
        $('#harga_total').val(formatRupiah(total.toString()));
    }

    /**
     * Memformat angka menjadi format Rupiah (IDR)
     */
    function formatRupiah(angka, prefix) {
        if(typeof angka === 'undefined' || angka === null) return '';
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
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    /**
     * Menerapkan event listener ke input untuk memformat Rupiah saat diketik
     */
    function setupInputFormatter(selector) {
        var $input = $(selector);
        $input.on('input', function(e) {
            // Dapatkan posisi kursor
            var selection = this.selectionStart;
            var originalLength = this.value.length;

            // Format nilai
            var formattedValue = formatRupiah(this.value);
            this.value = formattedValue;
            
            // Setel ulang posisi kursor
            var newLength = this.value.length;
            selection = selection + (newLength - originalLength);
            this.setSelectionRange(selection, selection);
        });
    }
</script>

@endsection
