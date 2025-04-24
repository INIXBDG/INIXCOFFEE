@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                {{-- Card Informasi Umum --}}
                <div class="card mt-4">
                    <div class="card-body" id="card">
                        <a href="{{ route('rkm.index') }}" class="btn click-primary my-2">
                            <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                        </a>
                        <h5 class="card-title text-center mb-4">{{ __('Upload Sertifikat Peserta') }}</h5>

                        {{-- Nama Materi --}}
                        <div class="row mb-3">
                            <label for="nama_materi" class="col-md-4 col-form-label text-md-start">Nama Materi</label>
                            <div class="col-md-7">
                                <input readonly id="nama_materi" type="text" class="form-control" name="nama_materi"
                                    value="{{ $rkm->materi->nama_materi }}">
                            </div>
                        </div>

                        {{-- Instruktur --}}
                        <div class="row mb-3">
                            <label for="instruktur" class="col-md-4 col-form-label text-md-start">Instruktur</label>
                            <div class="col-md-7">
                                <input readonly id="instruktur" type="text" class="form-control" name="instruktur"
                                    value="{{ $rkm->instruktur->nama_lengkap }}">
                            </div>
                        </div>

                        {{-- Tanggal Awal --}}
                        <div class="row mb-3">
                            <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">Tanggal Awal</label>
                            <div class="col-md-7">
                                <input readonly id="tanggal_awal" type="text" class="form-control" name="tanggal_awal"
                                    value="{{ $rkm->tanggal_awal }}">
                            </div>
                        </div>

                        {{-- Tanggal Akhir --}}
                        <div class="row mb-3">
                            <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">Tanggal
                                Akhir</label>
                            <div class="col-md-7">
                                <input readonly id="tanggal_akhir" type="text" class="form-control" name="tanggal_akhir"
                                    value="{{ $rkm->tanggal_akhir }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Peserta --}}
                @foreach ($rkm->registrasi as $reg)
                    <div class="card mt-4 mb-8">
                        <div class="card-body" id="card">
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">Nama Peserta</label>
                                <div class="col-md-7">
                                    <input readonly type="text" class="form-control" value="{{ $reg->peserta->nama }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">Perusahaan</label>
                                <div class="col-md-7">
                                    <input readonly type="text" class="form-control"
                                        value="{{ $reg->peserta->perusahaan->nama_perusahaan ?? 'Tidak Terdaftar' }}">
                                </div>
                            </div>
                            <form action="{{ route('storeSertifikat') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('POST')
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label text-md-start">Upload Sertifikat (PDF)</label>
                                    <div class="col-md-7">
                                        <input type="file" class="form-control" name="sertifikat"
                                            accept="application/pdf" required>
                                        <input type="hidden" name="id_peserta" value="{{ $reg->peserta->id }}">
                                        <input type="hidden" name="id_rkm" value="{{ $rkm->id }}">
                                        <small class="text-muted">*File harus dalam format PDF. Maksimal ukuran file:
                                            10MB</small>
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
                            @php
                                $pdf = $rkm->sertifikatPdf->where('id_peserta', $reg->peserta->id)->first();
                            @endphp

                            {{-- Tampilkan PDF Jika Sudah Ada --}}
                            @if ($pdf)
                                <div class="mt-4 text-center">
                                    <p class="fw-bold">PDF Sertifikat (Tersimpan):</p>
                                    <embed src="{{ Storage::url($pdf->pdf_path) }}" type="application/pdf" width="100%"
                                        height="400px" style="max-width: 500px;" />
                                </div>

                                <form method="POST" action="{{route('deleteSertifikat')}}"
                                    onsubmit="return confirm('Yakin ingin menghapus file ini?')">
                                    @csrf
                                    <input type="hidden" name="pdf_id" value="{{ $pdf->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm mt-2">Hapus PDF</button>
                                </form>
                            @endif


                        </div>
                    </div>
                    <div class="mb-5"></div>
                @endforeach

            </div>
        </div>
    </div>
@endsection
