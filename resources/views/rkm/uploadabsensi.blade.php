@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body" id="card">
                        <a href="{{ route('rkm.index') }}" class="btn click-primary my-2">
                            <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                        </a>
                        <h5 class="card-title text-center mb-4">{{ __('Upload Absensi') }}</h5>

                        <form method="POST" action="{{ route('storeAbsensi') }}" enctype="multipart/form-data">
                            @csrf
                            @method('POST')

                            <input type="hidden" name="id_rkm" value="{{ $rkm->id }}">

                            {{-- Nama Materi --}}
                            <div class="row mb-3">
                                <label for="nama_materi" class="col-md-4 col-form-label text-md-start">Nama Materi</label>
                                <div class="col-md-6">
                                    <input readonly id="nama_materi" type="text" class="form-control" name="nama_materi"
                                        value="{{ $rkm->materi->nama_materi }}">
                                </div>
                            </div>

                            {{-- Perusahaan --}}
                            <div class="row mb-3">
                                <label for="perusahaan" class="col-md-4 col-form-label text-md-start">Perusahaan</label>
                                <div class="col-md-6">
                                    <input readonly id="perusahaan" type="text" class="form-control" name="perusahaan"
                                        value="{{ $rkm->perusahaan->nama_perusahaan }}">
                                </div>
                            </div>

                            {{-- Tanggal Awal --}}
                            <div class="row mb-3">
                                <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">Tanggal Awal</label>
                                <div class="col-md-6">
                                    <input readonly id="tanggal_awal" type="text" class="form-control"
                                        name="tanggal_awal" value="{{ $rkm->tanggal_awal }}">
                                </div>
                            </div>

                            {{-- Tanggal Akhir --}}
                            <div class="row mb-3">
                                <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">Tanggal
                                    Akhir</label>
                                <div class="col-md-6">
                                    <input readonly id="tanggal_akhir" type="text" class="form-control"
                                        name="tanggal_akhir" value="{{ $rkm->tanggal_akhir }}">
                                </div>
                            </div>

                            {{-- Upload Absensi --}}
                            <div class="row mb-3">
                                <label for="absensi" class="col-md-4 col-form-label text-md-start">Upload Absensi
                                    (PDF)</label>
                                <div class="col-md-6">
                                    <input id="absensi" type="file" class="form-control" name="absensi"
                                        accept="application/pdf" required>
                                    <small class="text-muted">*File harus dalam format PDF. Maksimal ukuran file:
                                        10MB</small>
                                </div>
                            </div>


                            {{-- Tombol Simpan --}}
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn click-primary">
                                        {{ __('Simpan') }}
                                    </button>
                                </div>
                            </div>
                        </form>


                        {{-- Tampilkan PDF Jika Sudah Ada --}}
                        @if ($pdf)
                            <div class="mt-4 text-center">
                                <p class="fw-bold">PDF Absensi (Tersimpan):</p>
                                <embed src="{{ Storage::url($pdf->pdf_path) }}" type="application/pdf" width="100%"
                                    height="400px" style="max-width: 500px;" />
                            </div>

                            <form method="POST" action="{{ route('deleteAbsensi') }}"
                                onsubmit="return confirm('Yakin ingin menghapus file ini?')">
                                @csrf
                                <input type="hidden" name="pdf_id" value="{{ $pdf->id }}">
                                <button type="submit" class="btn btn-danger btn-sm mt-2">Hapus PDF</button>
                            </form>
                        @endif


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
