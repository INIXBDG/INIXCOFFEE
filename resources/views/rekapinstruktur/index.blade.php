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
    <div class="modal fade" id="modalRekap" tabindex="-1" aria-labelledby="exampleModalRekap" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5 class="modal-title" id="exampleModalRekap">Rekap</h5>
                    </div>
                </div>
                <div class="modal-body" style="overflow-y: scroll;">
                    <form method="POST" action="{{ route('rekapmengajarinstruktur.store') }}">
                        @csrf
                        <div class="mb-3 border-bottom" id="instruktur1">
                            <div class="row mb-3">
                                <label for="instruktur" class="col-md-4 col-form-label text-md-start">{{ __('Instruktur') }}</label>
                                <div class="col-md-6">
                                    <input id="instruktur" readonly type="text" placeholder="Instruktur" class="form-control" name="instruktur1" autocomplete="instruktur" autofocus>
                                    <input type="hidden" name="instruktur[0][instruktur]" id="instruktur_key">
                                    <input type="hidden" name="materi_key" id="materi_key">
                                    @error('instruktur.0.instruktur')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                                <div class="col-md-6">
                                    <input id="nama_materi" disabled type="text" placeholder="Nama Materi" class="form-control nama_materi @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi') }}" autocomplete="nama_materi" autofocus>
                                    @error('nama_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="rkm" class="col-md-4 col-form-label text-md-start">{{ __('RKM') }}</label>
                                <div class="col-md-6">
                                    <a href="#" target="_blank" id="linkRKM" class="btn btn-sm btn-primary">Link RKM</a>
                                    <input type="hidden" name="id_rkm" id="id_rkm">
                                    @error('rkm')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                                <div class="col-md-6">
                                    <input id="pax" type="text" placeholder="Pax" class="form-control pax @error('pax') is-invalid @enderror" name="instruktur[0][pax]" value="{{ old('pax') }}" autocomplete="pax" autofocus>
                                    @error('pax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="feedback_inst" class="col-md-4 col-form-label text-md-start">{{ __('Feedback') }}</label>
                                <div class="col-md-6">
                                    <input id="feedback_inst" readonly type="text" placeholder="feedback_inst" class="form-control feedback_inst @error('feedback_inst') is-invalid @enderror" name="instruktur[0][feedback]" value="{{ old('feedback_inst') }}" autocomplete="feedback_inst" autofocus>
                                    @error('feedback_inst')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_rkm" class="col-md-4 col-form-label text-md-start">{{ __('Durasi RKM') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_rkm" disabled type="text" placeholder="durasi_rkm" class="form-control durasi_rkm @error('durasi_rkm') is-invalid @enderror" name="durasi_rkm" value="{{ old('durasi_rkm') }}" autocomplete="durasi_rkm" autofocus>
                                    @error('durasi_rkm')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_materi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi Materi') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_materi" disabled type="text" placeholder="durasi_materi" class="form-control durasi_materi @error('durasi_materi') is-invalid @enderror" name="durasi_materi" value="{{ old('durasi_materi') }}" autocomplete="durasi_materi" autofocus>
                                    @error('durasi_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_inst" class="col-md-4 col-form-label text-md-start">{{ __('Durasi') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_inst" type="text" placeholder="Durasi" class="form-control @error('instruktur.0.durasi') is-invalid @enderror" name="instruktur[0][durasi]" value="{{ old('instruktur.0.durasi') }}" autocomplete="durasi" autofocus>
                                    @error('instruktur.0.durasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tanggal_awal_inst" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai') }}</label>
                                <div class="col-md-6">
                                    <input id="tanggal_awal_inst" type="date" placeholder="tanggal_awal_inst" class="form-control tanggal_awal @error('tanggal_awal_inst') tanggal_awal_inst is-invalid @enderror" name="instruktur[0][tanggal_awal]" value="{{ old('tanggal_awal_inst') }}" autocomplete="tanggal_awal_inst" autofocus>
                                    @error('tanggal_awal_inst')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tanggal_akhir_inst" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Selesai') }}</label>
                                <div class="col-md-6">
                                    <input id="tanggal_akhir_inst" type="date" placeholder="tanggal_akhir_inst" class="form-control tanggal_akhir @error('tanggal_akhir_inst') tanggal_akhir_inst is-invalid @enderror" name="instruktur[0][tanggal_akhir]" value="{{ old('tanggal_akhir_inst') }}" autocomplete="tanggal_akhir_inst" autofocus>
                                    @error('tanggal_akhir_inst')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="metode_kelas" class="col-md-4 col-form-label text-md-start">{{ __('Metode Kelas') }}</label>
                                <div class="col-md-6">
                                    <input id="metode_kelas" disabled type="text" placeholder="metode_kelas" class="form-control metode_kelas @error('metode_kelas')  is-invalid @enderror" name="metode_kelas" value="{{ old('metode_kelas') }}" autocomplete="metode_kelas" autofocus>
                                    @error('metode_kelas')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="event" class="col-md-4 col-form-label text-md-start">{{ __('Event') }}</label>
                                <div class="col-md-6">
                                    <input id="event" disabled type="text" placeholder="event" class="form-control event @error('event')  is-invalid @enderror" name="event" value="{{ old('event') }}" autocomplete="event" autofocus>
                                    @error('event')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="level_inst" class="col-md-4 col-form-label text-md-start">{{ __('Level') }}</label>
                                <div class="col-md-3">
                                    <select class="form-select @error('instruktur.0.level') is-invalid @enderror" name="instruktur[0][level]">
                                        <option selected>Pilih Level</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                    @error('instruktur.0.level')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <a href="#" id="linkLevel" target="_blank" class="btn btn-sm btn-primary">Lihat Level Sebelumnya</a>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" placeholder="Keterangan" id="keterangan" name="instruktur[0][keterangan]" style="height: 100px"></textarea>
                                    @error('keterangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 border-bottom" id="instruktur2">
                            <div class="row mb-3">
                                <label for="instruktur" class="col-md-4 col-form-label text-md-start">{{ __('Instruktur') }}</label>
                                <div class="col-md-6">
                                    <input id="instrukturke2" readonly type="text" placeholder="Instruktur" class="form-control" name="instrukturke2" autocomplete="instruktur" autofocus>
                                    <input type="hidden" name="instruktur[1][instruktur]" id="instruktur_key2">
                                    @error('instruktur.1.instruktur')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                                <div class="col-md-6">
                                    <input id="nama_materi" disabled type="text" placeholder="Nama Materi" class="form-control nama_materi @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi') }}" autocomplete="nama_materi" autofocus>
                                    @error('nama_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                                <div class="col-md-6">
                                    <input id="pax" type="text" placeholder="Pax" class="form-control pax @error('pax') is-invalid @enderror" name="instruktur[1][pax]" value="{{ old('pax') }}" autocomplete="pax" autofocus>
                                    @error('pax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="feedback_inst_2" class="col-md-4 col-form-label text-md-start">{{ __('Feedback') }}</label>
                                <div class="col-md-6">
                                    <input id="feedback_inst_2" readonly type="text" placeholder="feedback_inst" class="form-control @error('feedback_inst') is-invalid @enderror" name="instruktur[1][feedback]" value="{{ old('feedback_inst_2') }}" autocomplete="feedback_inst_2" autofocus>
                                    @error('feedback_inst_2')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_rkm" class="col-md-4 col-form-label text-md-start">{{ __('Durasi RKM') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_rkm" disabled type="text" placeholder="durasi_rkm" class="form-control durasi_rkm @error('durasi_rkm') is-invalid @enderror" name="durasi_rkm" value="{{ old('durasi_rkm') }}" autocomplete="durasi_rkm" autofocus>
                                    @error('durasi_rkm')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_materi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi Materi') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_materi" disabled type="text" placeholder="durasi_materi" class="form-control durasi_materi @error('durasi_materi') is-invalid @enderror" name="durasi_materi" value="{{ old('durasi_materi') }}" autocomplete="durasi_materi" autofocus>
                                    @error('durasi_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_inst2" class="col-md-4 col-form-label text-md-start">{{ __('Durasi') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_inst2" type="text" placeholder="Durasi" class="form-control @error('instruktur.1.durasi') is-invalid @enderror" name="instruktur[1][durasi]" value="{{ old('instruktur.1.durasi') }}" autocomplete="durasi" autofocus>
                                    @error('instruktur.1.durasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tanggal_awal_inst" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai') }}</label>
                                <div class="col-md-6">
                                    <input id="tanggal_awal_inst" type="date" placeholder="tanggal_awal_inst" class="form-control tanggal_awal @error('tanggal_awal_inst') tanggal_awal_inst is-invalid @enderror" name="instruktur[1][tanggal_awal]" value="{{ old('tanggal_awal_inst') }}" autocomplete="tanggal_awal_inst" autofocus>
                                    @error('tanggal_awal_inst')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tanggal_akhir_inst" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Selesai') }}</label>
                                <div class="col-md-6">
                                    <input id="tanggal_akhir_inst" type="date" placeholder="tanggal_akhir_inst" class="form-control tanggal_akhir @error('tanggal_akhir_inst') tanggal_akhir_inst is-invalid @enderror" name="instruktur[1][tanggal_akhir]" value="{{ old('tanggal_akhir_inst') }}" autocomplete="tanggal_akhir_inst" autofocus>
                                    @error('tanggal_akhir_inst')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="metode_kelas" class="col-md-4 col-form-label text-md-start">{{ __('Metode Kelas') }}</label>
                                <div class="col-md-6">
                                    <input id="metode_kelas" disabled type="text" placeholder="metode_kelas" class="form-control metode_kelas @error('metode_kelas') is-invalid @enderror" name="metode_kelas" value="{{ old('metode_kelas') }}" autocomplete="metode_kelas" autofocus>
                                    @error('metode_kelas')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="event" class="col-md-4 col-form-label text-md-start">{{ __('Event') }}</label>
                                <div class="col-md-6">
                                    <input id="event" disabled type="text" placeholder="event" class="form-control event @error('event')  is-invalid @enderror" name="event" value="{{ old('event') }}" autocomplete="event" autofocus>
                                    @error('event')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="level_inst_2" class="col-md-4 col-form-label text-md-start">{{ __('Level') }}</label>
                                <div class="col-md-6">
                                    <select class="form-select @error('instruktur.1.level') is-invalid @enderror" name="instruktur[1][level]">
                                        <option selected>Pilih Level</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                    @error('instruktur.1.level')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" placeholder="Keterangan" id="keterangan" name="instruktur[1][keterangan]" style="height: 100px"></textarea>
                                    @error('keterangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 border-bottom" id="asisten">
                            <div class="row mb-3">
                                <label for="instruktur" class="col-md-4 col-form-label text-md-start">{{ __('Instruktur') }}</label>
                                <div class="col-md-6">
                                    <input id="asisten_nama" readonly type="text" placeholder="Instruktur" class="form-control" name="asisten_nama" autocomplete="instruktur" autofocus>
                                    <input type="hidden" name="instruktur[2][instruktur]" id="asisten_key">
                                    @error('instruktur.2.instruktur')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                                <div class="col-md-6">
                                    <input id="nama_materi" disabled type="text" placeholder="Nama Materi" class="form-control nama_materi @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi') }}" autocomplete="nama_materi" autofocus>
                                    @error('nama_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                                <div class="col-md-6">
                                    <input id="pax" type="text" placeholder="Pax" class="form-control pax @error('pax') is-invalid @enderror" name="instruktur[2][pax]" value="{{ old('pax') }}" autocomplete="pax" autofocus>
                                    @error('pax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="feedback_ass" class="col-md-4 col-form-label text-md-start">{{ __('Feedback') }}</label>
                                <div class="col-md-6">
                                    <input id="feedback_ass" readonly type="text" placeholder="feedback_ass" class="form-control feedback_ass @error('feedback_ass') is-invalid @enderror" name="instruktur[2][feedback]" value="{{ old('feedback_ass') }}" autocomplete="feedback_ass" autofocus>
                                    @error('feedback_ass')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_rkm" class="col-md-4 col-form-label text-md-start">{{ __('Durasi RKM') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_rkm" disabled type="text" placeholder="durasi_rkm" class="form-control durasi_rkm @error('durasi_rkm') is-invalid @enderror" name="durasi_rkm" value="{{ old('durasi_rkm') }}" autocomplete="durasi_rkm" autofocus>
                                    @error('durasi_rkm')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_materi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi Materi') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_materi" disabled type="text" placeholder="durasi_materi" class="form-control durasi_materi @error('durasi_materi') is-invalid @enderror" name="durasi_materi" value="{{ old('durasi_materi') }}" autocomplete="durasi_materi" autofocus>
                                    @error('durasi_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi_inst3" class="col-md-4 col-form-label text-md-start">{{ __('Durasi') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi_inst3" type="text" placeholder="Durasi" class="form-control @error('instruktur.2.durasi') is-invalid @enderror" name="instruktur[2][durasi]" value="{{ old('instruktur.2.durasi') }}" autocomplete="durasi" autofocus>
                                    @error('instruktur.2.durasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tanggal_awal_inst" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai') }}</label>
                                <div class="col-md-6">
                                    <input id="tanggal_awal_inst" type="date" placeholder="tanggal_awal_inst" class="form-control tanggal_awal @error('tanggal_awal_inst') tanggal_awal_inst is-invalid @enderror" name="instruktur[2][tanggal_awal]" value="{{ old('tanggal_awal_inst') }}" autocomplete="tanggal_awal_inst" autofocus>
                                    @error('tanggal_awal_inst')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tanggal_akhir_inst" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Selesai') }}</label>
                                <div class="col-md-6">
                                    <input id="tanggal_akhir_inst" type="date" placeholder="tanggal_akhir_inst" class="form-control tanggal_akhir @error('tanggal_akhir_inst') tanggal_akhir_inst is-invalid @enderror" name="instruktur[2][tanggal_akhir]" value="{{ old('tanggal_akhir_inst') }}" autocomplete="tanggal_akhir_inst" autofocus>
                                    @error('tanggal_akhir_inst')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="metode_kelas" class="col-md-4 col-form-label text-md-start">{{ __('Metode Kelas') }}</label>
                                <div class="col-md-6">
                                    <input id="metode_kelas" disabled type="text" placeholder="metode_kelas" class="form-control metode_kelas @error('metode_kelas')  is-invalid @enderror" name="metode_kelas" value="{{ old('metode_kelas') }}" autocomplete="metode_kelas" autofocus>
                                    @error('metode_kelas')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="event" class="col-md-4 col-form-label text-md-start">{{ __('Event') }}</label>
                                <div class="col-md-6">
                                    <input id="event" disabled type="text" placeholder="event" class="form-control event @error('event')  is-invalid @enderror" name="event" value="{{ old('event') }}" autocomplete="event" autofocus>
                                    @error('event')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="level_inst_3" class="col-md-4 col-form-label text-md-start">{{ __('Level') }}</label>
                                <div class="col-md-6">
                                    <select class="form-select @error('instruktur.1.level') is-invalid @enderror" name="instruktur[2][level]">
                                        <option selected>Pilih Level</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                    @error('instruktur.2.level')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" placeholder="Keterangan" id="keterangan" name="instruktur[2][keterangan]" style="height: 100px"></textarea>
                                    @error('keterangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Simpan') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editmodalRekap" tabindex="-1" aria-labelledby="exampleeditModalRekap" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5 class="modal-title" id="exampleeditModalRekap">Edit Rekap</h5>
                    </div>
                </div>
                <div class="modal-body" style="overflow-y: scroll;">
                    <form id="editRekapForm" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="instruktur" class="col-md-4 col-form-label text-md-start">{{ __('Instruktur') }}</label>
                            <div class="col-md-6">
                                <input id="instruktur_edit" readonly type="text" placeholder="Instruktur" class="form-control" name="instruktur" autocomplete="instruktur" autofocus>
                                <input type="hidden" name="instruktur" id="instruktur_key_edit">
                                <input type="hidden" name="id_rekap" id="id_rekap">
                                <input type="hidden" name="materi_key" id="materi_key_edit">
                                @error('instruktur')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                            <div class="col-md-6">
                                <input id="nama_materi_edit" disabled type="text" placeholder="Nama Materi" class="form-control nama_materi @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi') }}" autocomplete="nama_materi" autofocus>
                                @error('nama_materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="rkm" class="col-md-4 col-form-label text-md-start">{{ __('RKM') }}</label>
                            <div class="col-md-6">
                                <a href="#" target="_blank" id="linkRKM_edit" class="btn btn-sm btn-primary">Link RKM</a>
                                <input type="hidden" name="id_rkm" id="id_rkm_edit">
                                @error('rkm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="feedback_inst" class="col-md-4 col-form-label text-md-start">{{ __('Feedback') }}</label>
                            <div class="col-md-6">
                                <input id="feedback_inst_edit" readonly type="text" placeholder="Feedback" class="form-control feedback_inst @error('feedback_inst') is-invalid @enderror" name="feedback" value="{{ old('feedback_inst') }}" autocomplete="feedback_inst" autofocus>
                                @error('feedback_inst')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                            <div class="col-md-6">
                                <input id="pax_edit" type="text" placeholder="Pax" class="form-control pax @error('pax') is-invalid @enderror" name="pax" value="{{ old('pax') }}" autocomplete="pax" autofocus>
                                @error('pax')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="durasi_rkm" class="col-md-4 col-form-label text-md-start">{{ __('Durasi RKM') }}</label>
                            <div class="col-md-6">
                                <input id="durasi_rkm_edit" disabled type="text" placeholder="Durasi RKM" class="form-control durasi_rkm @error('durasi_rkm') is-invalid @enderror" name="durasi_rkm" value="{{ old('durasi_rkm') }}" autocomplete="durasi_rkm" autofocus>
                                @error('durasi_rkm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="durasi_materi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi Materi') }}</label>
                            <div class="col-md-6">
                                <input id="durasi_materi_edit" disabled type="text" placeholder="Durasi Materi" class="form-control durasi_materi @error('durasi_materi') is-invalid @enderror" name="durasi_materi" value="{{ old('durasi_materi') }}" autocomplete="durasi_materi" autofocus>
                                @error('durasi_materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="durasi_inst" class="col-md-4 col-form-label text-md-start">{{ __('Durasi') }}</label>
                            <div class="col-md-6">
                                <input id="durasi_inst_edit" type="text" placeholder="Durasi" class="form-control @error('durasi') is-invalid @enderror" name="durasi" value="{{ old('instruktur.0.durasi') }}" autocomplete="durasi" autofocus>
                                @error('durasi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="tanggal_awal_inst" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_awal_edit" type="date" class="form-control tanggal_awal @error('tanggal_awal_inst') is-invalid @enderror" name="tanggal_awal" value="{{ old('tanggal_awal_inst') }}" autocomplete="tanggal_awal_inst" autofocus>
                                @error('tanggal_awal_inst')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="tanggal_akhir_inst" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Selesai') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_akhir_edit" type="date" class="form-control tanggal_akhir @error('tanggal_akhir_inst') is-invalid @enderror" name="tanggal_akhir" value="{{ old('tanggal_akhir_inst') }}" autocomplete="tanggal_akhir_inst" autofocus>
                                @error('tanggal_akhir_inst')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="metode_kelas" class="col-md-4 col-form-label text-md-start">{{ __('Metode Kelas') }}</label>
                            <div class="col-md-6">
                                <input id="metode_kelas_edit" disabled type="text" placeholder="Metode Kelas" class="form-control metode_kelas @error('metode_kelas') is-invalid @enderror" name="metode_kelas" value="{{ old('metode_kelas') }}" autocomplete="metode_kelas" autofocus>
                                @error('metode_kelas')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="event" class="col-md-4 col-form-label text-md-start">{{ __('Event') }}</label>
                            <div class="col-md-6">
                                <input id="event_edit" disabled type="text" placeholder="Event" class="form-control event @error('event') is-invalid @enderror" name="event" value="{{ old('event') }}" autocomplete="event" autofocus>
                                @error('event')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="level_inst" class="col-md-4 col-form-label text-md-start">{{ __('Level') }}</label>
                            <div class="col-md-3">
                                <select class="form-select @error('instruktur.0.level') is-invalid @enderror" name="level" id="level_edit">
                                    <option selected>Pilih Level</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                                @error('level')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <a href="#" id="linkLevel_edit" target="_blank" class="btn btn-sm btn-primary">Lihat Level Sebelumnya</a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                            <div class="col-md-6">
                                <textarea class="form-control" placeholder="Keterangan" id="keterangan_edit" name="keterangan" style="height: 100px">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Simpan') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Rekap Mengajar Instruktur') }}</h3>
                    <div class="d-flex justify-content-center mb-3">
                        <div class="col-md-3 mx-1">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select id="tahun" class="form-select" aria-label="tahun">
                                <option disabled>Pilih Tahun</option>
                                @php
                                $tahun_sekarang = now()->year;
                                for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                    $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                    echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-3 mx-1">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select id="bulan" class="form-select" aria-label="bulan">
                                <option disabled>Pilih Bulan</option>
                                @php
                                $bulan_sekarang = now()->month;
                                $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                for ($bulan = 1; $bulan <= 12; $bulan++) {
                                    $bulan_awal = $nama_bulan[$bulan - 1];
                                    $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                    echo "<option value=\"$bulan\" $selected>$bulan_awal</option>";
                                }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-3 mx-1">
                            <button type="button" id="cekdatas" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-list-tab" data-bs-toggle="pill" data-bs-target="#pills-list" type="button" role="tab" aria-controls="pills-list" aria-selected="true">List</button>
                                </li>
                                @php
                                    $id_instruktur = auth()->user()->karyawan->kode_karyawan; 
                                @endphp
                                @foreach ($karyawan as $item)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link 
                                            @if($id_instruktur !== $item->kode_karyawan && $id_instruktur !== 'AD') disabled @endif" 
                                            id="pills-{{ $item->kode_karyawan }}-tab" 
                                            data-bs-toggle="pill" 
                                            data-bs-target="#pills-{{ $item->kode_karyawan }}" 
                                            type="button" 
                                            role="tab" 
                                            aria-controls="pills-{{ $item->kode_karyawan }}" 
                                            aria-selected="false">
                                            {{ $item->nama_lengkap }}
                                        </button>
                                    </li>                        
                                @endforeach
                                <li class="nav-item" role="presentation">
                                  <button class="nav-link" id="pills-OL-tab" data-bs-toggle="pill" data-bs-target="#pills-OL" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">OL</button>
                                </li>
                            </ul>
                              <div class="tab-content" id="pills-tabContent">
                                <div class="d-flex justify-content-end">
                                    @if ( auth()->user()->jabatan == 'Education Manager')
                                        <button type="button" class="btn btn-sm btn-primary my-2" onclick="sinkronData()">Sinkronisasi Data</button>
                                    @endif
                                    </div>
                                <div class="tab-pane fade show active" id="pills-list" role="tabpanel" aria-labelledby="pills-list-tab" tabindex="0">
                                    <table class="table table-striped" id="mengajartable">
                                        <thead>
                                            <tr>
                                                <th scope="col">Nama Materi</th>
                                                <th scope="col">Instruktur</th>
                                                <th scope="col">tanggal</th>
                                                <th scope="col">Tanggal Mulai</th>
                                                <th scope="col">Tanggal Selesai</th>
                                                <th scope="col">Durasi RKM</th>
                                                <th scope="col">Durasi Materi</th>
                                                <th scope="col">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                
                                        </tbody>
                                    </table>
                                </div>
                                @foreach ($karyawan as $item)
                                    <div class="tab-pane fade" id="pills-{{ $item->kode_karyawan }}" role="tabpanel" aria-labelledby="pills-{{ $item->kode_karyawan }}-tab" tabindex="0">
                                        <table class="table" id="tableinstruktur-{{ $item->kode_karyawan }}">
                                            <thead>
                                                <tr>
                                                    <th>Nama Materi</th>
                                                    <th>Instruktur</th>
                                                    <th>Feedback</th>
                                                    <th>Pax</th>
                                                    <th>Durasi</th>
                                                    <th>Level</th>
                                                    <th>Metode Kelas</th>
                                                    <th>Tanggal Mulai</th>
                                                    <th>Tanggal Selesai</th>
                                                    <th>Keterangan</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data akan diisi oleh DataTable -->
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                                <div class="tab-pane fade" id="pills-OL" role="tabpanel" aria-labelledby="pills-OL-tab" tabindex="0">
                                    <table class="table" id="tableinstruktur-OL">
                                        <thead>
                                            <tr>
                                                <th>Nama Materi</th>
                                                <th>Instruktur</th>
                                                <th>Feedback</th>
                                                <th>Pax</th>
                                                <th>Durasi</th>
                                                <th>Level</th>
                                                <th>Metode Kelas</th>
                                                <th>Tanggal Mulai</th>
                                                <th>Tanggal Selesai</th>
                                                <th>Keterangan</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data akan diisi oleh DataTable -->
                                        </tbody>
                                    </table>
                                </div>
                              </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
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
    .modal-content {
    border-radius: 0px;
    box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }

    .modal-backdrop.show {
    opacity: 0.75;
    }

    .loader-txt {
    p {
        font-size: 13px;
        color: #666;
        small {
        font-size: 11.5px;
        color: #999;
        }
    }
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function(){
        console.log('{{$id_instruktur}}');
        // $('#modalRekap').modal('show');
        var userRole = '{{ auth()->user()->jabatan}}';
        var kode_karyawan = '{{ auth()->user()->id_instruktur }}';
        if (userRole == 'Education Manager') {
            // Jika Education Manager, aktifkan tab List
            $('#pills-list-tab').removeClass('disabled').addClass('active');
            $('#pills-list').addClass('show active'); // Aktifkan konten List
        } else {
            // Jika bukan Education Manager, nonaktifkan tab List dan tab OL
            $('#pills-list-tab').addClass('disabled').removeClass('active');
            $('#pills-OL-tab').addClass('disabled');

            // Aktifkan tab sesuai kode_karyawan
            $(`#pills-${kode_karyawan}-tab`).removeClass('disabled').addClass('active');
            $(`#pills-${kode_karyawan}`).addClass('show active'); // Aktifkan konten sesuai kode_karyawan
            
            // Nonaktifkan semua tab lainnya
            $('button[data-bs-toggle="pill"]').not(`#pills-${kode_karyawan}-tab`).removeClass('active').addClass('disabled');
            $('.tab-pane').not(`#pills-${kode_karyawan}`).removeClass('show active'); // Nonaktifkan semua konten tab lain
            tableInstruktur(kode_karyawan); // Memanggil fungsi untuk mengisi DataTable

        }
        $("#asisten").hide();
        $("#instruktur2").hide();
        $('button[data-bs-toggle="pill"]').on('click', function() {
            var kode_karyawan = $(this).attr('id').split('-')[1]; // Mengambil kode_karyawan dari ID tombol
            tableInstruktur(kode_karyawan); // Memanggil fungsi untuk mengisi DataTable
        });
        $('#cekdatas').click(function() {
            var activeTab = $('.nav-pills .nav-link.active'); // Mendapatkan tab yang aktif
            var kode_karyawan = activeTab.attr('id').split('-')[1]; // Mengambil kode_karyawan dari ID tab
            if(kode_karyawan == 'list'){
                tableMengajar();
            }else{
                tableInstruktur(kode_karyawan);
            }
        });

        tableMengajar();
    });
    function sinkronData() {
        // console.log('oke')
        $.ajax({
            url: "{{ route('sinkronData') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                alert(response.message);
            },
            error: function(xhr) {
                console.error("Terjadi kesalahan saat mengambil data:", xhr);
            }
        });
    }
    function modalRekap(id){
        $('#modalRekap').modal('show');
        // console.log(id);
            $.ajax({
                    url: '{{ route("getRKMDetailGroup") }}',
                    type: "GET",
                    data: {
                        id_rkm: id
                    },
                    dataType: "json",
                    success:function(data) {
                        // console.log(data);
                        // var data = data.rkm;
                        var tanggalAwal = moment(data.tanggal_awal);
                        var tanggalAkhir = moment(data.tanggal_akhir);
                        var durasiRKM = moment.duration(tanggalAkhir.diff(tanggalAwal));
                        var durasi_rkm = durasiRKM.asDays() + 1;
                        var tanggal = moment(data.tanggal_awal).format('D')
                        var lanbu = moment(data.tanggal_awal).format('M')
                        var hunta = moment(data.tanggal_awal).format('Y')
                        if (data.metode_kelas == 'Offline') {
                            var kelas = "off"
                        }else if(data.metode_kelas == 'Inhouse Bandung'){
                            var kelas = "inhb"
                        }else if(data.metode_kelas == 'Inhouse Luar Bandung'){
                            var kelas = "inhlb"
                        }else{
                            var kelas = "vir"
                        }

                        if(data.asisten_key === "-" || data.asisten_key === null){
                            $("#asisten").hide();
                        }else{
                            $("#asisten").show();
                            $('#asisten_nama').val(data.asisten.nama_lengkap);
                            $('#asisten_key').val(data.asisten_key);
                        }
                        if(data.instruktur_key2 === "-" || data.instruktur_key2 === null){
                            $("#instruktur2").hide();
                        }else{
                            $("#instruktur2").show();
                            $('#instrukturke2').val(data.instruktur2.nama_lengkap);
                            $('#instruktur_key2').val(data.instruktur_key2);
                        }

                        $('#id_rkm').val(data.id_rkm);
                        $('#instruktur_key').val(data.instruktur_key);
                        $('#instruktur').val(data.instruktur.nama_lengkap);
                        $('.nama_materi').val(data.materi.nama_materi);
                        $('.durasi_materi').val(data.materi.durasi);
                        $('.tanggal_awal').val(data.tanggal_awal);
                        $('.tanggal_akhir').val(data.tanggal_akhir);
                        $('.metode_kelas').val(data.metode_kelas);
                        $('.event').val(data.event);
                        $('.pax').val(data.pax);
                        $('.durasi_rkm').val(durasi_rkm);
                        $('#linkRKM').prop("href", '/rkm/' + data.materi_key + 'ixb' + tanggal + 'ie' + hunta +'ie' + lanbu + 'ixb' + kelas);
                        $('#linkLevel').prop("href", '/cekLevel/' + data.materi_key);
                        generatefeedback(data.id_rkm);
                    }
            });
    }
    function editmodalRekap(id) {
        $('#editmodalRekap').modal('show');
        // console.log(id);
        $.ajax({
            url: "{{ route('editMengajarInstruktur', ['id' => ':id']) }}".replace(':id', id),
            type: "GET",
            dataType: "json",
            success: function(data) {
                // console.log(data); // Log data untuk memeriksa struktur
                $('#tanggal_awal_edit').val(data.data.tanggal_awal);
                $('#tanggal_akhir_edit').val(data.data.tanggal_akhir);
                $('#durasi_inst_edit').val(data.data.durasi);
                $('#pax_edit').val(data.data.pax);
                $('#level_edit').val(data.data.level);
                $('#keterangan_edit').val(data.data.keterangan);

                if (data.success && data.data) {
                    var rkmData = data.data.rkm; // Akses objek rkm dari data
                    var instruktur = data.data.instruktur; // Akses objek rkm dari data

                    // Cek apakah tanggal_awal dan tanggal_akhir ada
                    if (rkmData.tanggal_awal && rkmData.tanggal_akhir) {
                        var tanggalAwal = moment(rkmData.tanggal_awal);
                        var tanggalAkhir = moment(rkmData.tanggal_akhir);
                        var durasiRKM = moment.duration(tanggalAkhir.diff(tanggalAwal));
                        var durasi_rkm = durasiRKM.asDays() + 1;
                        var tanggal = moment(rkmData.tanggal_awal).format('D')
                        var lanbu = moment(rkmData.tanggal_awal).format('M')
                        var hunta = moment(rkmData.tanggal_awal).format('Y')
                        if (rkmData.metode_kelas == 'Offline') {
                            var kelas = "off"
                        }else if(rkmData.metode_kelas == 'Inhouse Bandung'){
                            var kelas = "inhb"
                        }else if(rkmData.metode_kelas == 'Inhouse Luar Bandung'){
                            var kelas = "inhlb"
                        }else{
                            var kelas = "vir"
                        }
                        $('#editRekapForm').attr('action', "{{ route('rekapmengajarinstruktur.update', ':id') }}".replace(':id', id));
                        // console.log(tanggalAwal, tanggalAkhir);
                        $('#id_rkm_edit').val(data.data.id_rkm); // Menggunakan id_rkm dari data
                        $('#id_rekap').val(id); // Menggunakan id_rkm dari data
                        $('#instruktur_key_edit').val(instruktur.kode_karyawan);
                        $('#instruktur_edit').val(instruktur.nama_lengkap || ""); // Cek jika instruktur ada
                        $('#nama_materi_edit').val(rkmData.materi.nama_materi || ""); // Cek jika materi ada
                        $('#durasi_materi_edit').val(rkmData.materi.durasi || ""); // Cek jika durasi ada
                        $('#metode_kelas_edit').val(rkmData.metode_kelas);
                        $('#event_edit').val(rkmData.event);
                        $('#durasi_rkm_edit').val(durasi_rkm);
                        $('#linkRKM_edit').prop("href", '/rkm/' + rkmData.materi_key + 'ixb' + tanggal + 'ie' + hunta +'ie' + lanbu + 'ixb' + kelas);
                        $('#linkLevel_edit').prop("href", '/cekLevel/' + rkmData.materi_key);
                        $('#materi_key_edit').val(rkmData.materi_key);
                        // Menentukan cek berdasarkan instruktur_key
                        let cek = null;
                        if (rkmData.instruktur_key === instruktur.kode_karyawan) {
                            cek = 'Instruktur1';
                        } else if (rkmData.instruktur_key2 === instruktur.kode_karyawan) {
                            cek = 'Instruktur2';
                        } else if (rkmData.asisten_key === instruktur.kode_karyawan) {
                            cek = 'Asisten';
                        }
                        // console.log(data.data.id_rkm)
                        // Panggil fungsi generatefeedbackedit dengan cek yang ditentukan
                        generatefeedbackedit(data.data.id_rkm, cek);
                    } else {
                        console.error("Tanggal awal atau tanggal akhir tidak ditemukan");
                    }
                } else {
                    console.error("Data tidak valid atau tidak ditemukan");
                }
            },
            error: function(xhr) {
                console.error("Terjadi kesalahan saat mengambil data:", xhr);
            }
        });
    }
    function generatefeedbackedit(id, cek) {
        var temp = id.split(','); // Memisahkan ID menjadi array
        // console.log(temp);
        // 
        // Variabel untuk menyimpan total nilai
        var totalFeedback = 0;
        var count = 0; // Untuk menghitung jumlah permintaan yang berhasil

        // Menggunakan forEach untuk melakukan AJAX untuk setiap ID
        temp.forEach(function(item) {
            $.ajax({
                url: "{{ route('getNilaiFeedbackInstRKM', ['id' => ':id']) }}".replace(':id', item), // Mengganti :id dengan item
                type: "GET",
                dataType: "json",
                success: function(data) {
                    // Menambahkan nilai feedback ke totalFeedback
                    if (cek === 'Instruktur1') {
                        totalFeedback += data.average.instruktur || 0; // Menambahkan nilai instruktur
                    } else if (cek === 'Instruktur2') {
                        totalFeedback += data.average.instruktur2 || 0; // Menambahkan nilai instruktur2
                    } else if (cek === 'Asisten') {
                        totalFeedback += data.average.asisten || 0; // Menambahkan nilai asisten
                    }
                    count++; // Meningkatkan jumlah permintaan yang berhasil

                    // Jika semua permintaan selesai, hitung rata-rata
                    if (count === temp.length) {
                        var averageFeedback = totalFeedback / count; // Menghitung rata-rata
                        $('#feedback_inst_edit').val(averageFeedback); // Mengatur nilai rata-rata ke elemen
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data for ID " + item + ": " + error);
                    count++; // Meningkatkan jumlah permintaan yang berhasil meskipun ada error

                    // Jika semua permintaan selesai, hitung rata-rata
                    if (count === temp.length) {
                        var averageFeedback = totalFeedback / count; // Menghitung rata-rata
                        $('#feedback_inst_edit').val(averageFeedback); // Mengatur nilai rata-rata ke elemen
                    }
                }
            });
        });
    }
    function generatefeedback(id) {
        var temp = id.split(', '); // Memisahkan ID menjadi array
        // console.log(temp);
        
        // Variabel untuk menyimpan total nilai
        var instruktur1 = 0;
        var instruktur2 = 0;
        var asisten = 0;
        var count = 0; // Untuk menghitung jumlah permintaan yang berhasil

        // Menggunakan forEach untuk melakukan AJAX untuk setiap ID
        temp.forEach(function(item) {
            $.ajax({
                url: "{{ route('getNilaiFeedbackInstRKM', ['id' => ':id']) }}".replace(':id', item),
                type: "GET",
                dataType: "json",
                success: function(data) {
                    // console.log(item, data);
                    instruktur1 += data.average.instruktur || 0;
                    instruktur2 += data.average.instruktur2 || 0;
                    asisten += data.average.asisten || 0;
                    count++; // Meningkatkan jumlah permintaan yang berhasil

                    // Jika semua permintaan selesai, hitung rata-rata
                    if (count === temp.length) {
                        var averageFeedback1 = instruktur1 / count; // Rata-rata instruktur1
                        var averageFeedback2 = instruktur2 / count; // Rata-rata instruktur2
                        var averageFeedback3 = asisten / count; // Rata-rata asisten

                        // Mengatur nilai rata-rata ke elemen input
                        $('#feedback_inst').val(averageFeedback1); // Mengatur nilai dengan 2 desimal
                        $('#feedback_inst_2').val(averageFeedback2); // Mengatur nilai dengan 2 desimal
                        $('#feedback_ass').val(averageFeedback3); // Mengatur nilai dengan 2 desimal
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data for ID " + item + ": " + error);
                    count++; // Meningkatkan jumlah permintaan yang berhasil meskipun ada error

                    // Jika semua permintaan selesai, hitung rata-rata
                    if (count === temp.length) {
                        var averageFeedback1 = instruktur1 / count; // Rata-rata instruktur1
                        var averageFeedback2 = instruktur2 / count; // Rata-rata instruktur2
                        var averageFeedback3 = asisten / count; // Rata-rata asisten

                        // Mengatur nilai rata-rata ke elemen input
                        $('#feedback_inst').val(averageFeedback1); // Mengatur nilai dengan 2 desimal
                        $('#feedback_inst_2').val(averageFeedback2); // Mengatur nilai dengan 2 desimal
                        $('#feedback_ass').val(averageFeedback3); // Mengatur nilai dengan 2 desimal
                    }
                }
            });
        });
    }

    function tableMengajar() {
        if ($.fn.DataTable.isDataTable('#mengajartable')) {
            $('#mengajartable').DataTable().clear().destroy(); // Hancurkan DataTable yang ada
        }
        var tahun = $("#tahun").val();
        var bulan = $("#bulan").val();
        $('#mengajartable').DataTable({
            "ajax": {
                "url": "{{ route('getListMengajar', ['bulan' => ':bulan', 'tahun' => ':tahun']) }}".replace(':bulan', bulan).replace(':tahun', tahun),
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').removeAttr('inert');
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').attr('inert', true);
                    }, 1000);
                }
            },
            "columns": [
                {"data": "nama_materi"},
                {
                    "data": "instruktur.nama_lengkap",
                    render: function(data, type, row) {
                        return (data == null || data == '-') ? '-' : data;
                    }
                },
                {
                        "data": "tanggal_awal",
                        "visible": false,
                },
                {
                        "data": null,
                        "render": function(data, type, row) {
                            moment.locale('id');
                            var tanggalAwal = moment(data.tanggal_awal).format('DD MMMM YYYY');
                            return tanggalAwal;
                        }
                },
                {
                        "data": null,
                        "render": function(data, type, row) {
                            moment.locale('id');
                            var tanggalAkhir = moment(data.tanggal_akhir).format('DD MMMM YYYY');
                            return tanggalAkhir;
                        }
                },
                {"data": "durasi_rkm"},
                {"data": "durasi_materi"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                            // console.log(row.id);
                            var actions = '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '<button type="button" class="dropdown-item" onclick="modalRekap('+row.id+')" > Rekap </button>'
                            actions += '</div></div>';
                            return actions;
                    }
                }
            ],
            "columnDefs" : [{"targets":[2], "type":"date"}],
            "order": [[2, 'asc']],
        });
    }
    function tableInstruktur(kode_karyawan) {
        var tahun = $("#tahun").val();
        var bulan = $("#bulan").val();
        
        // Hancurkan DataTable yang ada jika sudah ada
        if ($.fn.DataTable.isDataTable('#tableinstruktur-' + kode_karyawan)) {
            $('#tableinstruktur-' + kode_karyawan).DataTable().clear().destroy();
        }
        $('#tableinstruktur-' + kode_karyawan).DataTable({
            "ajax": {
                "url": "{{ route('getMengajarInstruktur', ['id' => ':id', 'month' => ':month', 'year' => ':year']) }}".replace(':id', kode_karyawan).replace(':month', bulan).replace(':year', tahun),
                "type": "GET",
                "dataSrc": function (json) {
                    // console.log(json); // Cek data yang diterima
                    return json.data; // Pastikan ini mengembalikan array data
                },
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').removeAttr('inert');
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').attr('inert', true);
                    }, 1000);
                }
            },
            "columns": [
                {"data": "nama_materi"},
                // {"data": "nama_lengkap"},
                {
                    "data": "nama_lengkap",
                    render: function(data, type, row) {
                        return (data == null || data == '-') ? '-' : data;
                    }
                },
                {"data": "feedback"},
                {"data": "pax"},
                {"data": "durasi"},
                {"data": "level"},
                {"data": "metode_kelas"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        moment.locale('id');
                        return moment(data.tanggal_awal).format('DD MMMM YYYY');
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        moment.locale('id');
                        return moment(data.tanggal_akhir).format('DD MMMM YYYY');
                    }
                },
                {"data": "keterangan"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                        var userRole = '{{ auth()->user()->jabatan}}';
                        if (userRole === 'Education Manager') {
                            actions = '<div class="dropdown">';
                        actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                        actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                        actions += '<button type="button" class="dropdown-item" onclick="editmodalRekap('+data.id+')" ><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit </button>';
                        actions += '</div></div>';
                        } else {
                            actions = '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle disabled" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                            actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                            actions += '<button type="button" class="dropdown-item disabled" onclick="editmodalRekap('+data.id+')" ><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit </button>';
                            actions += '</div></div>';
                        }
                        
                        return actions;
                    }
                }
            ],
            "columnDefs": [{"targets": [6], "type": "date"}],
            "order": [[6, 'desc']],
        });
    }

</script>
@endpush
@endsection

