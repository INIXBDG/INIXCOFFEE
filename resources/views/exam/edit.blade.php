@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <h5 class="card-title text-center mb-4">{{ __('Data Exam') }}</h5>
                    <form method="POST" action="{{ route('exam.update', $exam->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="tanggal_pengajuan" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Pengajuan') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control @error('tanggal_pengajuan') is-invalid @enderror" name="tanggal_pengajuan" id="tanggal_pengajuan" value="{{ $exam->tanggal_pengajuan }}" readonly required>
                                @error('tanggal_pengajuan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <input type="hidden" name="id_rkm" value="{{ $exam->id_rkm }}">

                        <div class="row mb-3">
                            <label for="materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                            <div class="col-md-6">
                                <input type="text" readonly class="form-control @error('materi') is-invalid @enderror" name="materi" id="materi" value="{{ $exam->materi }}" required>
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
                                <input type="text" readonly class="form-control @error('perusahaan') is-invalid @enderror" name="perusahaan" id="perusahaan" value="{{ $exam->perusahaan }}" required>
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
                                        <option value="{{ $list->kode_exam }}" @selected($list->kode_exam == $exam->kode_exam)>
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
                            <label for="mata_uang" class="col-md-4 col-form-label text-md-start">{{ __('Mata Uang') }}</label>
                            <div class="col-md-6">
                                <select name="mata_uang" id="mata_uang" class="form-select">
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
                                    <input type="number" step="0.01" class="form-control @error('harga') is-invalid @enderror" value="{{ $exam->harga }}" name="harga" id="harga" required>
                                </div>
                                @error('harga')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="kurs_harga_div">
                            <label for="kurs" class="col-md-4 col-form-label text-md-start">
                                <div class="d-flex">Kurs<p id="symbol" class="mx-2"></p></div>
                            </label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control @error('kurs') is-invalid @enderror" value="{{ $exam->kurs }}" name="kurs" id="kurs" required>
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
                                    <input type="number" step="0.01" class="form-control @error('biaya_admin') is-invalid @enderror" value="{{ $exam->biaya_admin }}" name="biaya_admin" id="biaya_admin" required>
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
                                    <input type="number" class="form-control @error('kurs_dollar') is-invalid @enderror" value="{{ $exam->kurs_dollar }}" name="kurs_dollar" id="kurs_dollar" required>
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
                                    <input type="number" step="0.01" readonly class="form-control @error('harga_rupiah') is-invalid @enderror" value="{{ $exam->harga_rupiah }}" name="harga_rupiah" id="harga_rupiah">
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
                                <input type="number" class="form-control @error('pax') is-invalid @enderror" value="{{ $exam->pax }}" name="pax" id="pax" required>
                                @error('pax')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="total_div">
                            <label for="total" class="col-md-4 col-form-label text-md-start">{{ __('Total') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" readonly class="form-control @error('total') is-invalid @enderror" value="{{ $exam->total }}" name="total" id="total">
                                </div>
                                @error('total')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan Perubahan') }}</label>
                            <div class="col-md-6">
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" name="keterangan" id="keterangan">{{ $exam->keterangan }}</textarea>
                                @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0 justify-content-end">
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Submit') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const currencyElement = document.getElementById('mata_uang');
    const hargaElement = document.getElementById('harga');
    const kursElement = document.getElementById('kurs');
    const biayaAdminElement = document.getElementById('biaya_admin');
    const kursDollarElement = document.getElementById('kurs_dollar');
    const hargaRupiahElement = document.getElementById('harga_rupiah');
    const totalElement = document.getElementById('total');
    const currencySymbol = document.getElementById('currency-symbol');
    const kursHargaDiv = document.getElementById('kurs_harga_div');
    const kursDollarDiv = document.getElementById('kurs_dollar_div');
    const hargaRupiahDiv = document.getElementById('harga_rupiah_div');
    const symbol = document.getElementById('symbol');
    // const hargaRupiahElement = document.getElementById('harga_rupiah');
    const paxElement = document.getElementById('pax');
    // const totalElement = document.getElementById('total');
    function updateCurrencySymbol() {
        const selectedCurrency = currencyElement.value;
        let symbol = '';
        let display = '';

        switch (selectedCurrency) {
            case 'Rupiah':
                symbol = 'Rp.';
                display = 'none';
                break;
            case 'Dollar':
                symbol = '$';
                display = 'block';
                break;
            case 'Poundsterling':
                symbol = '£';
                display = 'block';
                break;
            case 'Euro':
                symbol = '€';
                display = 'block';
                break;
            case 'Franc Swiss':
                symbol = 'CHF';
                display = 'block';
                break;
        }

        currencySymbol.textContent = symbol;
        document.getElementById('symbol').textContent = symbol;
        kursHargaDiv.style.display = display === 'block' ? 'flex' : 'none';
        kursDollarDiv.style.display = display === 'block' ? 'flex' : 'none';
        hargaRupiahDiv.style.display = display === 'block' ? 'flex' : 'none';
        updateTotal();
    }

    currencyElement.addEventListener('change', updateCurrencySymbol);
    hargaElement.addEventListener('input', updateTotal);
    kursElement.addEventListener('input', updateTotal);
    biayaAdminElement.addEventListener('input', updateTotal);
    kursDollarElement.addEventListener('input', updateTotal);

    updateCurrencySymbol();  // Initial call to set the correct currency symbol and total

    function updateHargaRupiah() {
        const harga = parseFloat(hargaElement.value) || 0;
        const kurs = parseFloat(kursElement.value) || 0;
        const biayaAdmin = parseFloat(biayaAdminElement.value) || 0;
        const kursDollar = parseFloat(kursDollarElement.value) || 0;

        const totalHarga = (harga * kurs) + (biayaAdmin * kursDollar);
        hargaRupiahElement.value = totalHarga;
    }

    hargaElement.addEventListener('input', updateHargaRupiah);
    kursElement.addEventListener('input', updateHargaRupiah);
    biayaAdminElement.addEventListener('input', updateHargaRupiah);
    kursDollarElement.addEventListener('input', updateHargaRupiah);


    function updateTotal() {
        updateHargaRupiah();  // Initial call to set the correct harga_rupiah value

        const hargaRupiah = parseFloat(hargaRupiahElement.value) || 0;
        const pax = parseFloat(paxElement.value) || 0;
        const total = hargaRupiah * pax;
        totalElement.value = total;
    }

    hargaRupiahElement.addEventListener('input', updateTotal);
    paxElement.addEventListener('input', updateTotal);

    updateTotal();


});
</script>
@endsection
