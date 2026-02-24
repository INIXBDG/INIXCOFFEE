@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Edit Data Teknis Lab') }}</h5>

                    <form id="labForm" method="POST" action="{{ route('pengajuanlabsdansubs.updatelabsubs', $data->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- 1. INFORMASI UMUM --}}
                        <div class="row mb-3">
                            <label for="nama_labs" class="col-md-4 col-form-label text-md-start">{{ __('Nama Lab / Software') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('nama_labs') is-invalid @enderror"
                                       name="nama_labs" id="nama_labs" value="{{ old('nama_labs', $data->lab->nama_labs) }}" required>
                                @error('nama_labs')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="merk" class="col-md-4 col-form-label text-md-start">{{ __('Vendor / Merk') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('merk') is-invalid @enderror"
                                       name="merk" id="merk" value="{{ old('merk', $data->lab->merk) }}" placeholder="Contoh: Adobe, AWS">
                                @error('merk')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Aset') }}</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                    <option value="subscription" {{ $data->lab->tipe == 'subscription' ? 'selected' : '' }}>Subscription (Berlangganan)</option>
                                    <option value="one-time" {{ $data->lab->tipe == 'one-time' ? 'selected' : '' }}>One-Time (Sekali Beli)</option>
                                </select>
                                @error('tipe')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Status') }}</label>
                            <div class="col-md-6">
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="Pending" {{ $data->lab->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Active" {{ $data->lab->status == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Expired" {{ $data->lab->status == 'Expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="desc" class="col-md-4 col-form-label text-md-start">{{ __('Deskripsi') }}</label>
                            <div class="col-md-6">
                                <textarea name="desc" id="desc" class="form-control" rows="2">{{ old('desc', $data->lab->desc) }}</textarea>
                            </div>
                        </div>

                        {{-- 2. DETAIL AKSES --}}
                        <div class="row mb-3">
                            <label for="lab_url" class="col-md-4 col-form-label text-md-start">{{ __('URL Lab') }}</label>
                            <div class="col-md-6">
                                <input type="url" class="form-control" name="lab_url" id="lab_url" value="{{ old('lab_url', $data->lab->lab_url) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="access_code" class="col-md-4 col-form-label text-md-start">{{ __('Kode Akses / Key') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="access_code" id="access_code" value="{{ old('access_code', $data->lab->access_code) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="start_date" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                       value="{{ $data->lab->start_date ? \Carbon\Carbon::parse($data->lab->start_date)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="end_date" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Berakhir') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="end_date" id="end_date"
                                       value="{{ $data->lab->end_date ? \Carbon\Carbon::parse($data->lab->end_date)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        {{-- 3. KEUANGAN --}}
                        <div class="row mb-3">
                            <label for="mata_uang" class="col-md-4 col-form-label text-md-start">{{ __('Mata Uang') }}</label>
                            <div class="col-md-6">
                                <select name="mata_uang" id="mata_uang" class="form-select" required>
                                    @foreach (['Rupiah', 'Dollar', 'Euro', 'Poundsterling'] as $currency)
                                        <option value="{{ $currency }}" {{ $data->lab->mata_uang == $currency ? 'selected' : '' }}>
                                            {{ $currency }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harga" class="col-md-4 col-form-label text-md-start">{{ __('Nominal Harga Asli') }}</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" class="form-control" name="harga" id="harga"
                                       value="{{ old('harga', $data->lab->harga) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3" id="row_kurs">
                            <label for="kurs" class="col-md-4 col-form-label text-md-start">{{ __('Kurs (Rate)') }}</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" class="form-control" name="kurs" id="kurs"
                                       value="{{ old('kurs', $data->lab->kurs ?? 1) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harga_rupiah" class="col-md-4 col-form-label text-md-start">{{ __('Estimasi Rupiah') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control" name="harga_rupiah" id="harga_rupiah"
                                           value="{{ old('harga_rupiah', $data->lab->harga_rupiah ? number_format($data->lab->harga_rupiah, 0, ',', '.') : '') }}" readonly>
                                </div>
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
    // Fungsi Format Rupiah (Pemisah Titik)
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

    $(document).ready(function () {

        // Fungsi Kalkulasi Otomatis
        function calculateTotal() {
            var currency = $('#mata_uang').val();

            // Ambil nilai harga dan kurs
            var harga = parseFloat($('#harga').val()) || 0;
            var kurs = parseFloat($('#kurs').val()) || 1;

            // Logic Mata Uang
            if (currency === 'Rupiah') {
                $('#kurs').val(1).prop('readonly', true); // Kurs otomatis 1
                kurs = 1;
            } else {
                $('#kurs').prop('readonly', false);
            }

            // Hitung Total
            var total = harga * kurs;

            // Tampilkan dengan format Rupiah (tanpa desimal untuk tampilan)
            $('#harga_rupiah').val(formatRupiah(Math.floor(total).toString()));
        }

        // Event Listener untuk inputan
        $('#mata_uang').on('change', calculateTotal);
        $('#harga, #kurs').on('input', calculateTotal);

        // Jalankan kalkulasi saat halaman dimuat (untuk data existing)
        calculateTotal();

        // Prepare data before form submission (Hapus titik format rupiah)
        $('#labForm').on('submit', function () {
            $('#harga_rupiah').val($('#harga_rupiah').val().replace(/\./g, ''));
        });
    });
</script>
@endsection
