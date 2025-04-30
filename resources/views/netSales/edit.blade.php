@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <form method="POST" action="{{ route('netSales.update') }}" id="post">
            @csrf
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body" id="card">
                        <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                        <h5 class="card-title text-center mb-4">{{ __('Analisis RKM') }}</h5>
                        <div class="row">
                            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                <div class="row mb-3">
                                    <label for="harga_penawaran" class="col-md-4 col-form-label text-md-start">{{ __('Harga Penawaran') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="harga_penawaran" type="text" class="form-control @error('harga_penawaran') is-invalid @enderror" name="harga_penawaran" value="{{ old('harga_penawaran', number_format($dataNetSales->harga_penawaran, 0, ',', '.')) }}">      
                                            <input id="id_netSales" type="hidden" name="id_netsales" value="{{ old('id', number_format($dataNetSales->id)) }}">      
                                        </div>                                  
                                            @error('harga_penawaran')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="transportasi" class="col-md-4 col-form-label text-md-start">{{ __('Transportasi') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="transportasi" type="text" class="form-control @error('transportasi') is-invalid @enderror" name="transportasi" value="{{ old('transportasi', number_format($dataNetSales->transportasi, 0, ',', '.')) }}">
                                        </div>
                                        @error('transportasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="penginapan" class="col-md-4 col-form-label text-md-start">{{ __('Penginapan') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="penginapan" type="text" class="form-control @error('penginapan') is-invalid @enderror" name="penginapan" value="{{ old('penginapan', number_format($dataNetSales->penginapan, 0, ',', '.')) }}">
                                        </div>
                                        @error('penginapan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="fresh_money" class="col-md-4 col-form-label text-md-start">{{ __('Fresh Money') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="fresh_money" type="text" class="form-control @error('fresh_money') is-invalid @enderror" name="fresh_money" value="{{ old('fresh_money', number_format($dataNetSales->fresh_money, 0, ',', '.')) }}">
                                        </div>
                                        @error('fresh_money')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="entertaint" class="col-md-4 col-form-label text-md-start">{{ __('Entertaint') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="entertaint" type="text" class="form-control @error('entertaint') is-invalid @enderror" name="entertaint" value="{{ old('entertaint', number_format($dataNetSales->entertaint, 0, ',', '.')) }}">
                                        </div>
                                        @error('entertaint')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="souvenir" class="col-md-4 col-form-label text-md-start">{{ __('Souvenir') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="souvenir" type="text" class="form-control @error('souvenir') is-invalid @enderror" name="souvenir" value="{{ old('souvenir', number_format($dataNetSales->souvenir, 0, ',', '.')) }}">
                                        </div>
                                        @error('souvenir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="tgl_pa" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Payment Advance') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input id="tgl_pa" type="date"  class="form-control @error('tgl_pa') is-invalid @enderror"  name="tgl_pa"  value="{{ old('tgl_pa', \Carbon\Carbon::parse($dataNetSales->tgl_pa)->format('Y-m-d')) }}">
                                        </div>
                                        @error('tgl_pa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="tipe_pembayaran" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Pembayaran') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <select name="tipe_pembayaran" id="tipe_pembayaran" class="form-control @error('tipe_pembayaran') is-invalid @enderror">
                                                <option value="" disabled {{ old('tipe_pembayaran', $dataNetSales->tipe_pembayaran) == null ? 'selected' : '' }}>Pilih Tipe Pembayaran</option>
                                                <option value="Cash" @selected(old('tipe_pembayaran', $dataNetSales->tipe_pembayaran) == 'Cash')>Cash</option>
                                                <option value="Transfer" @selected(old('tipe_pembayaran', $dataNetSales->tipe_pembayaran) == 'Transfer')>Transfer</option>
                                            </select>
                                        </div>
                                        @error('tipe_pembayaran')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                <div class="row mb-3">
                                    <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                                    <div class="col-md-6">
                                        <input readonly id="nama_materi" type="text" placeholder="Masukan Nama Jabatan" class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{$rkm->materi->nama_materi}}" autocomplete="nama_materi" autofocus>
                                        <input type="hidden" name="id_rkm" value="{{$rkm->id}}">
                                        @error('nama_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="nama_perusahaan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Perusahaan') }}</label>
                                    <div class="col-md-6">
                                        <input readonly id="nama_perusahaan" type="text" placeholder="Masukan Nama Jabatan" class="form-control @error('nama_perusahaan') is-invalid @enderror" name="nama_perusahaan" value="{{$rkm->perusahaan->nama_perusahaan}}" autocomplete="nama_perusahaan" autofocus>
                                        @error('nama_perusahaan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3" id="tanggal_awal-row">
                                    <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Awal') }}</label>
                                    <div class="col-md-6">
                                        <input readonly type="date" class="form-control" value="{{$rkm->tanggal_awal}}" name="tanggal_awal" id="tanggal_awal">
                                        @error('tanggal_awal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3" id="tanggal_akhir-row">
                                    <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Akhir') }}</label>
                                    <div class="col-md-6">
                                        <input readonly type="date" class="form-control" value="{{$rkm->tanggal_akhir}}" name="tanggal_akhir" id="tanggal_akhir">
                                        @error('tanggal_akhir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3" id="durasi-row">
                                    <label for="kelas" class="col-md-4 col-form-label text-md-start">{{ __('Kelas') }}</label>
                                    <div class="col-md-6">
                                        <input readonly id="kelas" type="text" placeholder="kelas" class="form-control @error('kelas') is-invalid @enderror" name="kelas" autocomplete="kelas" autofocus value="{{$rkm->metode_kelas}}">
                                        @error('kelas')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3" id="durasi-row">
                                    <label for="durasi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi Hari') }}</label>
                                    <div class="col-md-6">
                                        <input readonly id="durasi" type="text" placeholder="Durasi" class="form-control @error('durasi') is-invalid @enderror" name="durasi" autocomplete="durasi" autofocus>
                                        @error('durasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                                    <div class="col-md-6">
                                        <input readonly id="pax" type="text" placeholder="Masukan Nama Jabatan" class="form-control @error('pax') is-invalid @enderror" name="pax" value="{{$rkm->pax}}" autocomplete="pax" autofocus>
                                        @error('pax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="harga_jual" class="col-md-4 col-form-label text-md-start">{{ __('Harga Jual') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input disabled type="text" step="0.01" class="form-control @error('harga_jual') is-invalid @enderror" name="harga_jual" value="{{$rkm->harga_jual}}" id="harga_jual" required>
                                        </div>
                                        @error('harga_jual')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="total_harga_jual" class="col-md-4 col-form-label text-md-start">{{ __('Total Harga Jual') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input readonly type="text" step="0.01" class="form-control @error('total_harga_jual') is-invalid @enderror" name="total_harga_jual" value="" id="total_harga_jual" required>
                                        </div>
                                        @error('total_harga_jual')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="nett_penjualan" class="col-md-4 col-form-label text-md-start">{{ __('Total') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input readonly type="text" step="0.01" class="form-control @error('nett_penjualan') is-invalid @enderror" name="nett_penjualan" value="" id="nett_penjualan" required>
                                            <button class="btn btn-success" type="button" id="total">Total</button>
                                        </div>
                                        @error('nett_penjualan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-10">
                                        <button type="submit" class="btn click-primary" id="btnsubmit">
                                            {{ __('Simpan') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
    .loader {
        position: relative;
        text-align: center;
        margin: 15px auto 35px auto;
        z-index: 9999;
        display: block;
        width: 80px;
        height: 80px;
        border: 10px solid rgba(0, 0, 0, .3);
        border-radius: 50%;
        border-top-color: #000;
        animation: spin 1s ease-in-out infinite;
        -webkit-animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    @-webkit-keyframes spin {
        to {
            -webkit-transform: rotate(360deg);
        }
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function formatRupiah(angka, prefix) {
        let number_string = angka.replace(/[^,\d]/g, '').toString(),
            split   	 = number_string.split(','),
            sisa     	 = split[0].length % 3,
            rupiah     	 = split[0].substr(0, sisa),
            ribuan     	 = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    document.querySelectorAll('input[type="text"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let formatted = formatRupiah(this.value, '');
            this.value = formatted;
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formatRupiah = (angka, prefix) => {
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix === undefined ? rupiah : (rupiah ? prefix + ' ' + rupiah : '');
        };

        const fields = ['harga_penawaran', 'transportasi', 'penginapan', 'fresh_money', 'entertaint', 'souvenir'];

        fields.forEach(id => {
            const input = document.getElementById(id);
            input.addEventListener('input', function() {
                const cursorPos = this.selectionStart;
                const originalLength = this.value.length;
                const unformatted = this.value.replace(/[^0-9]/g, '');
                this.dataset.value = unformatted;
                this.value = formatRupiah(unformatted, '');
                const newLength = this.value.length;
                this.setSelectionRange(cursorPos + (newLength - originalLength), cursorPos + (newLength - originalLength));
            });
        });

        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            fields.forEach(id => {
                const input = document.getElementById(id);
                input.value = input.value.replace(/[^0-9]/g, '');
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
        let hargaJual = $('#harga_jual').val();
        var kelas = $('#kelas').val();
        if (hargaJual) {
            $('#harga_jual').val(formatRupiah(hargaJual.toString()));
        }
        if (kelas == "Inhouse Bandung" || kelas == "Inhouse Luar Bandung") {
            $('#pcpeserta').prop('hidden', false);
        } else {
            $('#pcpeserta').prop('hidden', true);
            // $('#pcpeserta').prop('checked', true);
        }
        toggleFeeInstruktur();
        $('#ol').change(function() {
            toggleFeeInstruktur();
        });
        // exam();
        calculateDuration();
        updateHargaJual();
        $('#harga_modul_regular').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateHargaModul();
        });
        $('#harga_modul_regular_dollar').on('input', function() {
            updateHargaModulDollar();
        });
        $('#kurs_dollar').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateHargaModulDollar();
        });
        $('#makan_siang, #coffee_break, #konsumsi_instruktur').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateKonsumsi();
        });
        $('#souvenir_satu').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateSouvenir();
        });
        $('#pc_pax, #pc_instruktur, #pc_peserta').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updatePC();
        });
        $('#fee_instruktur').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateInstruktur();
        });
        $('#alat').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        $('#total').on('click', function() {
            $('#loadingModal').modal('show');
            setTimeout(function() {
                totalSemua();
                $('#btnsubmit').prop('disabled', false);
                $('#loadingModal').modal('hide');
            }, 2000);
        });
        $('#pa_hotel').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        $('#exam').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        $('#btnsubmit').on('click', function() {
            submitreaction(); // Panggil fungsi yang kamu butuhkan sebelum submit
            $('#btnsubmit').prop('disabled', true); // Disable tombol submit setelah diklik

            // Kirim form secara manual
            $('#post').submit();
        });
        $('#total_fee_instruktur').on('input', function() {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });

        loadNetSales();
    });

    function loadNetSales() {
        
    }

    function toggleFeeInstruktur() {
        if ($('#ol').is(':checked')) {
            $('#fee_instruktur').attr('readonly', true);
            $('#total_fee_instruktur').attr('readonly', false);
        } else {
            $('#fee_instruktur').attr('readonly', false);
            $('#total_fee_instruktur').attr('readonly', true);
        }
    }

    function updateHargaJual() {
        var pax = parseInt($('#pax').val(), 10); // Mengonversi string ke integer
        const harga_jual = parseFloat(removeRupiahFormat($('#harga_jual').val())) || 0;
        var total_harga_jual = harga_jual * pax;
        $('#total_harga_jual').val(formatRupiah(total_harga_jual.toString()));
    }

    function updateHargaModul() {
        var pax = parseInt($('#pax').val(), 10); // Mengonversi string ke integer
        const harga_modul_regular = parseFloat(removeRupiahFormat($('#harga_modul_regular').val())) || 0;
        var biaya_modul_regular = harga_modul_regular * pax;
        $('#biaya_modul_regular').val(formatRupiah(biaya_modul_regular.toString()));
    }

    function updateHargaModulDollar() {
        var pax = parseInt($('#pax').val(), 10) || 1; // Mengonversi string ke integer, default ke 1 jika kosong
        var kurs = parseFloat(removeRupiahFormat($('#kurs_dollar').val())) || 0; // Mengonversi string ke float
        const harga_modul_regular_dollar = parseFloat($('#harga_modul_regular_dollar').val()) || 0;
        // Lakukan perhitungan
        var biaya_dollar = harga_modul_regular_dollar * pax;
        var biaya_modul_regular_dollar = biaya_dollar * kurs;
        // console.log(harga_modul_regular_dollar, Math.round(biaya_dollar), Math.round(biaya_modul_regular_dollar));

        // Menampilkan hasil dengan format Rupiah
        $('#biaya_modul_regular_dollar').val(formatRupiah(Math.round(biaya_modul_regular_dollar)));
    }

    function updateKonsumsi() {
        var paxs = parseInt($('#pax').val(), 10); // Mengonversi string ke integer
        var inst = $('#konsumsi_instruktur').is(':checked') ? 1 : 0;
        var pax = (paxs + inst);
        var kelas = $('#kelas').val();
        // console.log(pax);
        const durasi = $('#durasi').val();
        const makan_siang = parseFloat(removeRupiahFormat($('#makan_siang').val())) || 0;
        const coffee_break = parseFloat(removeRupiahFormat($('#coffee_break').val())) || 0;
        if (kelas == "Offline") {
            var konsumsi = ((durasi * pax) * makan_siang) + ((durasi * pax) * coffee_break);
        } else if (kelas == "Virtual") {
            var konsumsi = ((durasi * inst) * makan_siang) + ((durasi * inst) * coffee_break);
        } else {
            var konsumsi = ((durasi * pax) * makan_siang) + ((durasi * pax) * coffee_break);
        }
        $('#konsumsi').val(formatRupiah(konsumsi.toString()));
    }

    function updateSouvenir() {
        var pax = parseInt($('#pax').val(), 10); // Mengonversi string ke integer
        const souvenir_satu = parseFloat(removeRupiahFormat($('#souvenir_satu').val())) || 0;
        var souvenir = souvenir_satu * pax;
        $('#souvenir').val(formatRupiah(souvenir.toString()));
    }

    function updatePC() {
        var peserta = parseInt($('#pax').val(), 10)
        var durasi = parseInt($('#durasi').val(), 10) || 0;
        var inst = $('#pc_instruktur').is(':checked') ? 1 : 0;
        var paxs = $('#pc_peserta').is(':checked') ? peserta : 0;
        var kelas = $('#kelas').val();
        var pax = paxs + inst;
        const pc_pax = parseFloat(removeRupiahFormat($('#pc_pax').val())) || 0;
        if (kelas == 'Virtual') {
            var pc = pc_pax * durasi * inst;
        } else {
            var pc = pc_pax * durasi * pax;
        }
        $('#pc').val(formatRupiah(pc.toString()));
    }

    function updateInstruktur() {
        var durasi = parseInt($('#durasi').val(), 10); // Mengonversi string ke integer
        const fee_instruktur = parseFloat(removeRupiahFormat($('#fee_instruktur').val())) || 0;
        var jam = 5;
        var total_fee_instruktur = fee_instruktur * jam * durasi;
        $('#total_fee_instruktur').val(formatRupiah(total_fee_instruktur.toString()));
    }
    // function updateAkomodasi() {
    //     var durasi = parseInt($('#durasi').val(), 10); // Mengonversi string ke integer
    //     const pa_hotel_akomodasi = parseFloat(removeRupiahFormat($('#pa_hotel_akomodasi').val())) || 0;
    //     var pa_hotel = pa_hotel_akomodasi  * durasi;
    //     $('#pa_hotel').val(formatRupiah(pa_hotel.toString()));
    // }
    function totalSemua() {
        const total_fee_instruktur = parseFloat(removeRupiahFormat($('#total_fee_instruktur').val())) || 0;
        const exam = parseFloat(removeRupiahFormat($('#exam').val())) || 0;
        const pc = parseFloat(removeRupiahFormat($('#pc').val())) || 0;
        const souvenir = parseFloat(removeRupiahFormat($('#souvenir').val())) || 0;
        const konsumsi = parseFloat(removeRupiahFormat($('#konsumsi').val())) || 0;
        const biaya_modul_regular = parseFloat(removeRupiahFormat($('#biaya_modul_regular').val())) || 0;
        const biaya_modul_regular_dollar = parseFloat(removeRupiahFormat($('#biaya_modul_regular_dollar').val())) || 0;
        const alat = parseFloat(removeRupiahFormat($('#alat').val())) || 0;
        const pa_hotel = parseFloat(removeRupiahFormat($('#pa_hotel').val())) || 0;
        const total_harga_jual = parseFloat(removeRupiahFormat($('#total_harga_jual').val())) || 0;
        var nett_penjualan = total_harga_jual - (total_fee_instruktur + pc + souvenir + konsumsi + biaya_modul_regular + biaya_modul_regular_dollar + alat + pa_hotel + exam);

        // Debugging check to see the raw value of nett_penjualan
        console.log('Nett Penjualan before formatting:', nett_penjualan);

        $('#nett_penjualan').val(formatRupiah(nett_penjualan.toString()));
    }


    function calculateDuration() {
        // Ambil nilai tanggal dari input dengan id 'tanggal_awal' dan 'tanggal_akhir'
        var startDate = new Date($('#tanggal_awal').val());
        var endDate = new Date($('#tanggal_akhir').val());

        // Periksa apakah input tanggal valid
        if (!isNaN(startDate) && !isNaN(endDate)) {
            // Hitung selisih tanggal dalam milidetik
            var timeDifference = endDate - startDate;

            // Konversi milidetik ke hari (1 hari = 1000 ms * 60 s * 60 m * 24 h)
            var daysDifference = Math.floor(timeDifference / (1000 * 60 * 60 * 24));

            // Jika hasil perhitungan lebih dari 0, tampilkan, jika tidak, tampilkan 0
            $('#durasi').val(daysDifference >= 0 ? daysDifference + 1 : 0); // Tambahkan 1 untuk menyertakan hari pertama
        } else {
            // Jika tanggal tidak valid, kosongkan input durasi
            $('#durasi').val('');
        }
    }

    function submitreaction() {
        const total_fee_instruktur = parseFloat(removeRupiahFormat($('#total_fee_instruktur').val())) || 0;
        const fee_instruktur = parseFloat(removeRupiahFormat($('#fee_instruktur').val())) || 0;
        const pc = parseFloat(removeRupiahFormat($('#pc').val())) || 0;
        const souvenir = parseFloat(removeRupiahFormat($('#souvenir').val())) || 0;
        const konsumsi = parseFloat(removeRupiahFormat($('#konsumsi').val())) || 0;
        const biaya_modul_regular = parseFloat(removeRupiahFormat($('#biaya_modul_regular').val())) || 0;
        const biaya_modul_regular_dollar = parseFloat(removeRupiahFormat($('#biaya_modul_regular_dollar').val())) || 0;
        const alat = parseFloat(removeRupiahFormat($('#alat').val())) || 0;
        const pa_hotel = parseFloat(removeRupiahFormat($('#pa_hotel').val())) || 0;
        const total_harga_jual = parseFloat(removeRupiahFormat($('#total_harga_jual').val())) || 0;
        const nett_penjualan = parseFloat(removeRupiahFormat($('#nett_penjualan').val())) || 0;
        const harga_modul_regular = parseFloat(removeRupiahFormat($('#harga_modul_regular').val())) || 0;
        const exam = parseFloat(removeRupiahFormat($('#exam').val())) || 0;
        const makan_siang = parseFloat(removeRupiahFormat($('#makan_siang').val())) || 0;
        const coffee_break = parseFloat(removeRupiahFormat($('#coffee_break').val())) || 0;
        const souvenir_satu = parseFloat(removeRupiahFormat($('#souvenir_satu').val())) || 0;
        const pc_pax = parseFloat(removeRupiahFormat($('#pc_pax').val())) || 0;
        const kurs_dollar = parseFloat(removeRupiahFormat($('#kurs_dollar').val())) || 0;

        $('#fee_instruktur').val(fee_instruktur);
        $('#total_fee_instruktur').val(total_fee_instruktur);
        $('#pc').val(pc);
        $('#souvenir').val(souvenir);
        $('#konsumsi').val(konsumsi);
        $('#biaya_modul_regular').val(biaya_modul_regular);
        $('#biaya_modul_regular_dollar').val(biaya_modul_regular_dollar);
        $('#harga_modul_regular').val(harga_modul_regular);
        $('#alat').val(alat);
        $('#pa_hotel').val(pa_hotel);
        $('#total_harga_jual').val(total_harga_jual);
        $('#total_fee_instruktur').val(total_fee_instruktur);
        $('#nett_penjualan').val(nett_penjualan);
        $('#total_harga_jual').val(total_harga_jual);
        $('#exam').val(exam);
        $('#makan_siang').val(makan_siang);
        $('#coffee_break').val(coffee_break);
        $('#souvenir_satu').val(souvenir_satu);
        $('#pc_pax').val(pc_pax);
        $('#kurs_dollar').val(kurs_dollar);
    }

    function formatRupiah(value) {
        const isNegative = value.startsWith('-'); 
        value = value.replace(/[^,\d]/g, '').toString(); 
        const split = value.split(',');
        let rupiah = split[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        rupiah = (split[1] !== undefined) ? rupiah + ',' + split[1] : rupiah;
        return (isNegative ? '-' : '') + rupiah;
    }


    function removeRupiahFormat(angka) {
        return angka.replace(/[Rp.\s]/g, '').replace(/,/g, '.');
    }
</script>
@endsection