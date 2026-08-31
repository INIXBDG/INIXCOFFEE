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
                    <h5 class="card-title text-center mb-4">{{ __('Edit Data Teknis Subscription') }}</h5>

                    <form id="subsForm" method="POST" action="{{ route('pengajuansubs.updatesubssubs', $data->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- 1. INFORMASI UMUM --}}
                        <div class="row mb-3">
                            <label for="nama_subs" class="col-md-4 col-form-label text-md-start">{{ __('Nama Subscription / Software') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('nama_subs') is-invalid @enderror"
                                       name="nama_subs" id="nama_subs" value="{{ old('nama_subs', $data->subs->nama_subs) }}" required>
                                @error('nama_subs')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="merk" class="col-md-4 col-form-label text-md-start">{{ __('Vendor / Merk') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('merk') is-invalid @enderror"
                                       name="merk" id="merk" value="{{ old('merk', $data->subs->merk) }}" placeholder="Contoh: Adobe, AWS">
                                @error('merk')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Aset') }}</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                    <option value="subscription" {{ $data->subs->tipe == 'subscription' ? 'selected' : '' }}>Subscription (Berlangganan)</option>
                                    <option value="one-time" {{ $data->subs->tipe == 'one-time' ? 'selected' : '' }}>One-Time (Sekali Beli)</option>
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
                                    <option value="pending" {{ $data->subs->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="active" {{ $data->subs->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ $data->subs->status == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="desc" class="col-md-4 col-form-label text-md-start">{{ __('Deskripsi') }}</label>
                            <div class="col-md-6">
                                <textarea name="desc" id="desc" class="form-control" rows="2">{{ old('desc', $data->subs->desc) }}</textarea>
                            </div>
                        </div>

                        {{-- 2. DETAIL AKSES --}}
                        <div class="row mb-3">
                            <label for="subs_url" class="col-md-4 col-form-label text-md-start">{{ __('URL Subs') }}</label>
                            <div class="col-md-6">
                                <input type="url" class="form-control" name="subs_url" id="subs_url" value="{{ old('subs_url', $data->subs->subs_url) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="access_code" class="col-md-4 col-form-label text-md-start">{{ __('Kode Akses / Key') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="access_code" id="access_code" value="{{ old('access_code', $data->subs->access_code) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="start_date" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                       value="{{ $data->subs->start_date ? \Carbon\Carbon::parse($data->subs->start_date)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="end_date" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Berakhir') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="end_date" id="end_date"
                                       value="{{ $data->subs->end_date ? \Carbon\Carbon::parse($data->subs->end_date)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        {{-- 3. KEUANGAN --}}
                        <div class="row mb-3">
                            <label for="mata_uang" class="col-md-4 col-form-label text-md-start">{{ __('Mata Uang') }}</label>
                            <div class="col-md-6">
                                <select name="mata_uang" id="mata_uang" class="form-select" required>
                                    @foreach (['Rupiah', 'Dollar', 'Euro', 'Poundsterling'] as $currency)
                                        <option value="{{ $currency }}" {{ $data->subs->mata_uang == $currency ? 'selected' : '' }}>
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
                                       value="{{ old('harga', $data->subs->harga) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3" id="row_kurs">
                            <label for="kurs" class="col-md-4 col-form-label text-md-start">{{ __('Kurs (Rate)') }}</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" class="form-control" name="kurs" id="kurs"
                                       value="{{ old('kurs', $data->subs->kurs ?? 1) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harga_rupiah" class="col-md-4 col-form-label text-md-start">{{ __('Estimasi Rupiah') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control" name="harga_rupiah" id="harga_rupiah"
                                           value="{{ old('harga_rupiah', $data->subs->harga_rupiah ? number_format($data->subs->harga_rupiah, 0, ',', '.') : '') }}" readonly>
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

        function calculateTotal() {
            var currency = $('#mata_uang').val();
            var harga = parseFloat($('#harga').val()) || 0;
            var kurs = parseFloat($('#kurs').val()) || 1;

            if (currency === 'Rupiah') {
                $('#kurs').val(1).prop('readonly', true);
                kurs = 1;
            } else {
                $('#kurs').prop('readonly', false);
            }
            var total = harga * kurs;
            $('#harga_rupiah').val(formatRupiah(Math.floor(total).toString()));
        }

        // Listener
        $('#mata_uang').on('change', calculateTotal);
        $('#harga, #kurs').on('input', calculateTotal);

        // Initialization on Load
        calculateTotal();

        $('#subsForm').on('submit', function () {
            $('#harga_rupiah').val($('#harga_rupiah').val().replace(/\./g, ''));
        });
    });
</script>
@endsection
