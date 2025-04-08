@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <h5 class="card-title text-center mb-4">{{ __('Data Exam') }}</h5>
                    <form method="POST" action="{{ route('exam.approval', $exam->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="tanggal_pengajuan" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Pengajuan') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control @error('tanggal_pengajuan') is-invalid @enderror" name="tanggal_pengajuan" id="tanggal_pengajuan" readonly required>
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
                                <select name="kode_exam" id="kode_exam" class="form-select" disabled>
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
                                <select name="mata_uang" id="mata_uang" class="form-select" disabled>
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
                                    <input type="number" step="0.01" class="form-control @error('harga') is-invalid @enderror" value="{{ $exam->harga }}" name="harga" id="harga" readonly required>
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
                                    <input type="number" step="0.01" class="form-control @error('biaya_admin') is-invalid @enderror" value="{{ $exam->biaya_admin }}" name="biaya_admin" id="biaya_admin" readonly required>
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
                                    <input type="number" step="0.01" class="form-control @error('harga_rupiah') is-invalid @enderror" value="{{ $exam->harga_rupiah }}" name="harga_rupiah" id="harga_rupiah" readonly required>
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
                                <input type="number" class="form-control @error('pax') is-invalid @enderror" value="{{ $exam->pax }}" name="pax" id="pax" readonly>
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
                                    <input type="number" step="0,01" class="form-control @error('total') is-invalid @enderror" value="{{ $exam->total }}" name="total" id="total" readonly>
                                    {{-- <button class="btn btn-primary" id="updateHarga" type="button">Update</button> --}}
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
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="" id="approveCheck" required>
                                    <label class="form-check-label" for="approveCheck">
                                        {{ __('Saya telah memeriksa data di atas') }}
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    {{ __('Approve') }}
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

    $(document).ready(function() {
        var jabatan = '{{ auth()->user()->jabatan}}'
        var today = new Date().toISOString().split('T')[0];
        var paxInput = $('#pax');
        var totalInput = $('#total');
        // console.log(jabatan);
        $('#tanggal_pengajuan').val(today);

        $('#kurs, #kurs_dollar').on('input', calculateTotalRupiah);

        function formatRupiah(angka) {
            if (typeof angka !== 'number') {
                return 'Input harus berupa angka';
            }
            let rupiah = "";
            let angkaStr = angka.toString();
            let count = 0;

            for (let i = angkaStr.length - 1; i >= 0; i--) {
                rupiah = angkaStr[i] + rupiah;
                count++;
                if (count % 3 === 0 && i !== 0) {
                rupiah = "." + rupiah;
                }
            }

            return rupiah;
            };

            function calculateTotalRupiah() {
            // Mengambil nilai dari input pax
            var pax = $('#pax').val();
            var hargaRupiah = parseFloat($('#harga_rupiah').val());
            var totalRupiah = hargaRupiah * pax;
            $('#total_rupiah').val(totalRupiah);
            // console.log(totalRupiah);
        }

        function calculateTotalRupiah() {
            // Mengambil nilai dari input pax
            $('#total').val('')
            var pax = parseFloat(paxInput.val()); // Pastikan menggunakan parseFloat atau parseInt sesuai kebutuhan
            var hargaRupiah = parseFloat($('#harga_rupiah').val());
            var totalRupiah = hargaRupiah * pax;
            var totalnet = $('#total').val(totalRupiah);
            // console.log(totalnet)

        }

        function jabatans(){
            if(jabatan === 'SPV Sales'){
                $('#kurs_dollar').prop('readonly', true);
                $('#kurs').prop('readonly', true);
            }else if(jabatan === 'Office Manager' || jabatan === 'GM' || jabatan === 'Koordinator Office'){
                $('#kurs_dollar').prop('readonly', false);
                $('#kurs').prop('readonly', false);
                $('#total_rupiah').val('');
                calculateTotalRupiah();
            }else{
                $('#kurs_dollar').prop('readonly', true);
                $('#kurs').prop('readonly', true);
                $('#biaya_admin').prop('readonly', false);
                $('#harga').prop('readonly', false);
                $('#total_rupiah').val('')
                calculateTotalRupiah();
            }
        }
        function updateForm() {
            var totalInput = $('#total');
            var harga = parseFloat($('#harga').val()) || 0;
            var biayaAdmin = parseFloat($('#biaya_admin').val()) || 0;
            var kursdollar = parseFloat($('#kurs_dollar').val()) || 0;
            var kurs = parseFloat($('#kurs').val()) || 0;

            var totalHarga = harga * kurs;
            var totalBiaya = biayaAdmin * kursdollar;
            let format = $('#harga_rupiah').val(totalHarga + totalBiaya);

            calculateTotalRupiah();
        }

        $('#harga, #biaya_admin, #kurs, #kurs_dollar').on('input', updateForm);
        // $('#updateHarga').on('click', calculateTotalRupiah)
        jabatans();

    });
</script>
@endsection
