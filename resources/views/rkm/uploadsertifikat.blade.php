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

                        <form action="{{ route('storeSertifikat') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="id_rkm" value="{{ $rkm->id }}">

                            <div class="mb-3">
                                <label for="sertifikat" class="form-label">Upload Sertifikat (PDF)</label>
                                <input type="file" name="sertifikat[]" class="form-control" multiple
                                    accept="application/pdf" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>

                        @foreach ($rkm->sertifikatPDF as $sertifikat)
                            <div class="card mb-4 shadow-sm">
                                <div class="card-body">
                                    <embed src="{{ Storage::url($sertifikat->pdf_path) }}" type="application/pdf"
                                        width="100%" height="500px" style="border: 1px solid #ccc; border-radius: 8px;" />

                                    <form method="POST" action="{{ route('deleteSertifikat') }}"
                                        onsubmit="return confirm('Yakin ingin menghapus file ini?')">
                                        @csrf
                                        <input type="hidden" name="pdf_id" value="{{ $sertifikat->id }}">
                                        <button type="submit" class="btn btn-danger btn-sm mt-2">Hapus PDF</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
