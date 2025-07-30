@extends('layouts.app')

@section('content')
<style>
    #webcam-preview {
        transform: scaleX(1);
    }
</style>
<div class="modal fade" id="webcamModal" tabindex="-1" aria-labelledby="webcamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ambil Gambar via Webcam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center">
                <video id="webcam-preview" autoplay playsinline width="100%" style="border:1px solid #ccc;"></video>
                <canvas id="webcam-canvas" style="display:none;"></canvas>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="capture-btn">Ambil Gambar</button>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-1">{{ __('Pengajuan Klaim') }}</h5>
                    <p class="card-title text-center mb-5">{{ __('Absen Tidak Terekap') }}</p>
                    <form method="POST" action="{{ route('pengajuanklaim.addNoRecord') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">
                                <input disabled id="nama_karyawan" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('nama_karyawan') is-invalid @enderror" name="nama_karyawan" value="{{ $karyawan->nama_lengkap }}" autocomplete="nama_karyawan" autofocus>
                                @error('nama_karyawan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input disabled id="divisi" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                                @error('divisi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="kendala-row">
                            <label for="kendala" class="col-md-4 col-form-label text-md-start">{{ __('Pilih Kendala') }}</label>
                            <div class="col-md-6">
                                <select name="kendala" id="kendala" required class="form-control @error('kendala') is-invalid @enderror" autocomplete="kendala" autofocus>
                                    <option selected disabled>Pilih Kendala</option>
                                    <option value="Human Error">Human Error</option>
                                    <option value="System Error">System Error</option>
                                </select>
                                @error('kendala')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_absen-row">
                            <label for="tanggal_absen" class="col-md-4 col-form-label text-md-start">
                                {{ __('Tanggal Absen') }}
                            </label>
                            <div class="col-md-6">
                                <input type="date" name="tanggal_absen" class="form-control @error('tanggal_absen') is-invalid @enderror" id="tanggal_absen" required>
                                @error('tanggal_absen')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>



                        <div class="row mb-3" id="bukti_gambar-row">
                            <label for="bukti_gambar" class="col-md-4 col-form-label text-md-start">{{ __('Upload Bukti Gambar') }}</label>
                            <div class="col-md-6">
                                <input id="bukti_gambar" type="file" class="form-control @error('bukti_gambar') is-invalid @enderror" name="bukti_gambar" accept="image/*">
                                <button type="button" id="openWebcamBtn" class="btn btn-outline-primary mt-2 d-none" data-bs-toggle="modal" data-bs-target="#webcamModal">
                                    Buka Kamera
                                </button>
                            </div>
                        </div>


                        <div class="row mb-3" id="kronologi-row">
                            <label for="kronologi" class="col-md-4 col-form-label text-md-start">{{ __('Kronologi') }}</label>
                            <div class="col-md-6">
                                <textarea name="kronologi" placeholder="kronologi..." required class="form-control" id="kronologi" cols="51" rows="5" required></textarea>
                                @error('kronologi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
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
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kendalaSelect = document.getElementById('kendala');
        const openWebcamBtn = document.getElementById('openWebcamBtn');
        const webcamModal = document.getElementById('webcamModal');
        let video = document.getElementById('webcam-preview');
        let canvas = document.getElementById('webcam-canvas');
        let captureBtn = document.getElementById('capture-btn');
        let stream = null;

        kendalaSelect.addEventListener('change', function() {
            const fileInput = document.getElementById('bukti_gambar');
            if (this.value === 'Human Error') {
                openWebcamBtn.classList.remove('d-none');
                fileInput.readOnly = true;
            } else {
                openWebcamBtn.classList.add('d-none');
            }
        });

        webcamModal.addEventListener('shown.bs.modal', async function() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: true
                });
                video.srcObject = stream;
            } catch (e) {
                alert('Gagal mengakses kamera: ' + e.message);
            }
        });

        webcamModal.addEventListener('hidden.bs.modal', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
        });

        captureBtn.addEventListener('click', function() {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);

            canvas.toBlob(function(blob) {
                const file = new File([blob], 'webcam.jpg', {
                    type: 'image/jpeg'
                });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('bukti_gambar').files = dataTransfer.files;
                alert('Gambar berhasil diambil');
                const modal = bootstrap.Modal.getInstance(webcamModal);
                modal.hide();
            }, 'image/jpeg');
        });
    });
</script>
{{-- Hapus atau komentar ini --}}
{{-- 
<script>
    $(document).ready(function() {
        $('#tanggal_absen').select2({
            placeholder: "Pilih Tanggal Absen",
            allowClear: true
        });
    });
</script> 
--}}


@endsection