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
        <form method="POST" action="{{ route('kelasanalisis.store') }}" id="post">
            @csrf
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body" id="card">
                        <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                        <h5 class="card-title text-center mb-4">{{ __('Analisis RKM') }}</h5>
                        <div class="row">
                            <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                <div class="row mb-3">
                                    <label for="harga_modul_regular" class="col-md-4 col-form-label text-md-start">{{ __('Harga Modul Regular') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="harga_modul_regular" type="text" class="form-control @error('harga_modul_regular') is-invalid @enderror" name="harga_modul_regular" autocomplete="harga_modul_regular" autofocus>
                                        </div>
                                        @error('harga_modul_regular')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                    <div class="row mb-3">
                                        <label for="harga_modul_regular_dollar" class="col-md-4 col-form-label text-md-start">{{ __('Harga Modul Dalam Dollar') }}</label>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text" id="currency-symbol">$</span>
                                                <input id="harga_modul_regular_dollar" type="text" class="form-control @error('harga_modul_regular_dollar') is-invalid @enderror" name="harga_modul_regular_dollar" autocomplete="harga_modul_regular_dollar" autofocus>
                                            </div>
                                            @error('harga_modul_regular_dollar')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                        <div class="row mb-3">
                                            <label for="kurs_dollar" class="col-md-4 col-form-label text-md-start">{{ __('Kurs Dollar') }}</label>
                                            <div class="col-md-6">
                                                <div class="input-group">
                                                    <span class="input-group-text" id="currency-symbol">$</span>
                                                    <input id="kurs_dollar" type="text" class="form-control @error('kurs_dollar') is-invalid @enderror" name="kurs_dollar" autocomplete="kurs_dollar" autofocus>
                                                </div>
                                                @error('kurs_dollar')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="biaya_modul_regular" class="col-md-4 col-form-label text-md-start">{{ __('Biaya Modul Regular') }}</label>
                                            <div class="col-md-6">
                                                <div class="input-group">
                                                    <span class="input-group-text" id="currency-symbol">Rp.</span>
                                                    <input readonly id="biaya_modul_regular" type="text"  class="form-control @error('biaya_modul_regular') is-invalid @enderror" name="biaya_modul_regular" autocomplete="biaya_modul_regular" autofocus>
                                                </div>
                                                @error('biaya_modul_regular')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="biaya_modul_regular_dollar" class="col-md-4 col-form-label text-md-start">{{ __('Biaya Modul Regular Dollar') }}</label>
                                            <div class="col-md-6">
                                                <div class="input-group">
                                                    <span class="input-group-text" id="currency-symbol">Rp.</span>
                                                    <input readonly id="biaya_modul_regular_dollar" type="text"  class="form-control @error('biaya_modul_regular_dollar') is-invalid @enderror" name="biaya_modul_regular_dollar" autocomplete="biaya_modul_regular_dollar" autofocus>
                                                </div>
                                                @error('biaya_modul_regular_dollar')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                <div class="row mb-3">
                                    <label for="exam" class="col-md-4 col-form-label text-md-start">{{ __('Exam') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="exam" type="text" class="form-control @error('exam') is-invalid @enderror" name="exam">
                                        </div>
                                        @error('exam')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>                                

                                <div class="row mb-3">
                                    <label for="makan_siang" class="col-md-4 col-form-label text-md-start">{{ __('Makan Siang') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="makan_siang" type="text" class="form-control @error('makan_siang') is-invalid @enderror" name="makan_siang" autocomplete="makan_siang" autofocus>
                                        </div>
                                        @error('makan_siang')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="coffee_break" class="col-md-4 col-form-label text-md-start">{{ __('Coffee Break') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="coffee_break" type="text" class="form-control @error('coffee_break') is-invalid @enderror" name="coffee_break" autocomplete="coffee_break" autofocus>
                                        </div>
                                        @error('coffee_break')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="konsumsi" class="col-md-4 col-form-label text-md-start">{{ __('Konsumsi') }}</label>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input readonly id="konsumsi" type="text"  class="form-control @error('konsumsi') is-invalid @enderror" name="konsumsi" autocomplete="konsumsi" autofocus>
                                        </div>
                                        @error('konsumsi')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="konsumsi_instruktur" id="konsumsi_instruktur" value="1">
                                            <label class="form-check-label" for="konsumsiinstruktur">
                                                + Instruktur
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="souvenir_satu" class="col-md-4 col-form-label text-md-start">{{ __('Souvenir per Pax') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="souvenir_satu" type="text" class="form-control @error('souvenir_satu') is-invalid @enderror" name="souvenir_satu" autocomplete="souvenir_satu" autofocus>
                                        </div>
                                        @error('souvenir_satu')
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
                                            <input readonly id="souvenir" type="text"  class="form-control @error('souvenir') is-invalid @enderror" name="souvenir" autocomplete="souvenir" autofocus>
                                        </div>
                                        @error('souvenir')
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
                                            <input id="transportasi" type="text" class="form-control @error('transportasi') is-invalid @enderror" name="transportasi" autocomplete="transportasi" autofocus>
                                        </div>
                                        @error('transportasi')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="pc_pax" class="col-md-4 col-form-label text-md-start">{{ __('PC per Pax') }}</label>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="pc_pax" type="text" class="form-control @error('pc_pax') is-invalid @enderror" name="pc_pax" autocomplete="pc_pax" autofocus>
                                        </div>
                                        @error('pc_pax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="pc_instruktur" id="pc_instruktur" value="1">
                                            <label class="form-check-label" for="pcinstruktur">
                                                + Instruktur
                                            </label>
                                        </div>
                                        <div class="form-check" id="pcpeserta">
                                            <input class="form-check-input" checked type="checkbox" name="pc_peserta" id="pc_peserta" value="1">
                                            <label class="form-check-label" for="pc_peserta">
                                                + Peserta
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="pc" class="col-md-4 col-form-label text-md-start">{{ __('PC') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input readonly id="pc" type="text" class="form-control @error('pc') is-invalid @enderror" name="pc" autocomplete="pc" autofocus>
                                        </div>
                                        @error('pc')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>                                        

                                <div class="row mb-3">
                                    <label for="alat" class="col-md-4 col-form-label text-md-start">{{ __('Alat') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="alat" type="text" class="form-control @error('alat') is-invalid @enderror" name="alat" autocomplete="alat" autofocus>
                                        </div>
                                        @error('alat')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="pa_hotel" class="col-md-4 col-form-label text-md-start">{{ __('Akomodasi PA / Hotel') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="pa_hotel" type="text" class="form-control @error('pa_hotel') is-invalid @enderror" name="pa_hotel" autocomplete="pa_hotel" autofocus>
                                        </div>
                                        @error('pa_hotel')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="fee_instruktur" class="col-md-4 col-form-label text-md-start">{{ __('Fee Instruktur') }}</label>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="fee_instruktur" type="text" class="form-control @error('fee_instruktur') is-invalid @enderror" name="fee_instruktur" autocomplete="fee_instruktur" autofocus>
                                        </div>
                                        
                                        @error('fee_instruktur')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="ol" id="ol" value="1">
                                            <label class="form-check-label" for="ol">
                                                + OL
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="total_fee_instruktur" class="col-md-4 col-form-label text-md-start">{{ __('Total Fee Instruktur') }}</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text" id="currency-symbol">Rp.</span>
                                            <input id="total_fee_instruktur" type="text"  class="form-control @error('total_fee_instruktur') is-invalid @enderror" name="total_fee_instruktur" autocomplete="total_fee_instruktur" autofocus>
                                        </div>
                                        @error('total_fee_instruktur')
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
                                        <div class="row mb-3">
                                            <label for="komentar" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                                            <div class="col-md-6">
                                                <input id="komentar" type="text" placeholder="Keterangan" class="form-control @error('komentar') is-invalid @enderror" name="komentar" autocomplete="komentar" autofocus>
                                                @error('komentar')
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
    $(document).ready(function () {
        let hargaJual = $('#harga_jual').val();
        var kelas = $('#kelas').val();
        if (hargaJual) {
            $('#harga_jual').val(formatRupiah(hargaJual.toString()));
        }
        if (kelas == "Inhouse Bandung" || kelas == "Inhouse Luar Bandung"){
            $('#pcpeserta').prop('hidden', false);
        }else{
            $('#pcpeserta').prop('hidden', true);
            // $('#pcpeserta').prop('checked', true);
        }
        toggleFeeInstruktur();
        $('#ol').change(function () {
            toggleFeeInstruktur();
        });
        // exam();
        calculateDuration();
        updateHargaJual();
        $('#harga_modul_regular').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateHargaModul();
        });
        $('#harga_modul_regular_dollar').on('input', function () {
            updateHargaModulDollar();
        });
        $('#kurs_dollar').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateHargaModulDollar();
        });
        $('#makan_siang, #coffee_break, #konsumsi_instruktur').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateKonsumsi();
        });
        $('#souvenir_satu').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateSouvenir();
        });
        $('#transportasi').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        $('#pc_pax, #pc_instruktur, #pc_peserta' ).on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updatePC();
        });
        $('#fee_instruktur').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
            updateInstruktur();
        });
        $('#alat').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        $('#total').on('click', function () {
            $('#loadingModal').modal('show');
            setTimeout(function() { 
                totalSemua();
                $('#btnsubmit').prop('disabled', false);
                $('#loadingModal').modal('hide');
            }, 2000);
        });   
        $('#pa_hotel').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        $('#exam').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        $('#btnsubmit').on('click', function () {
            submitreaction(); // Panggil fungsi yang kamu butuhkan sebelum submit
            $('#btnsubmit').prop('disabled', true); // Disable tombol submit setelah diklik

            // Kirim form secara manual
            $('#post').submit();
        });
        $('#total_fee_instruktur').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
    });
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
        console.log(harga_modul_regular_dollar, Math.round(biaya_dollar), Math.round(biaya_modul_regular_dollar));

        // Menampilkan hasil dengan format Rupiah
        $('#biaya_modul_regular_dollar').val((biaya_modul_regular_dollar));
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
        if(kelas == "Offline"){
            var konsumsi = ((durasi * pax)*makan_siang)+((durasi * pax)*coffee_break);
        }else if(kelas == "Virtual"){
            var konsumsi = ((durasi * inst)*makan_siang)+((durasi * inst)*coffee_break);
        }else{
            var konsumsi = ((durasi * pax)*makan_siang)+((durasi * pax)*coffee_break);
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
        if(kelas == 'Virtual'){
            var pc = pc_pax * durasi * inst;
        }else{
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
        const transportasi = parseFloat(removeRupiahFormat($('#transportasi').val())) || 0;
        const konsumsi = parseFloat(removeRupiahFormat($('#konsumsi').val())) || 0;
        const biaya_modul_regular = parseFloat(removeRupiahFormat($('#biaya_modul_regular').val())) || 0;
        const biaya_modul_regular_dollar = parseFloat(removeRupiahFormat($('#biaya_modul_regular_dollar').val())) || 0;
        const alat = parseFloat(removeRupiahFormat($('#alat').val())) || 0;
        const pa_hotel = parseFloat(removeRupiahFormat($('#pa_hotel').val())) || 0;
        const total_harga_jual = parseFloat(removeRupiahFormat($('#total_harga_jual').val())) || 0;
        var nett_penjualan = total_harga_jual - (total_fee_instruktur + pc + souvenir + transportasi + konsumsi + biaya_modul_regular + biaya_modul_regular_dollar + alat + pa_hotel + exam);

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
        const transportasi = parseFloat(removeRupiahFormat($('#transportasi').val())) || 0;
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
        $('#transportasi').val(transportasi);
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
        const isNegative = value.startsWith('-'); // Check if the value is negative
        value = value.replace(/[^,\d]/g, '').toString(); // Remove non-numeric characters except comma
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
