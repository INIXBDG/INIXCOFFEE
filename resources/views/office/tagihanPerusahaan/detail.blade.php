@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-bold text-dark">Detail Tagihan Perusahaan</h4>
            <small class="text-muted fw-medium">
                {{ now()->translatedFormat('l, d F Y') }}
            </small>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">

                <div class="card shadow-lg border-0 rounded-4 overflow-hidden glass-force">

                    <!-- Header -->
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-0 fw-semibold">
                            <i class="bx bx-info-circle text-primary me-2" style="font-size:1.5rem;"></i>
                            Informasi Tagihan
                        </h5>
                    </div>

                    @if (session('success_tagihan'))
                        <div class="alert alert-success">
                            {{ session('success_tagihan') }}
                        </div>
                    @endif


                    <!-- Body -->
                    <div class="card-body p-4">

                        <form id="updateTagihanForm" action="{{ route('updateTagihanPerusahaan', $tagihan->id) }}"
                            method="POST" enctype="multipart/form-data">

                            @csrf


                            <!-- ROW 1 -->
                            <div class="row g-4">

                                <!-- Kegiatan -->
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase">
                                        Kegiatan
                                    </label>

                                    <input type="text" name="kegiatan" class="form-control fw-semibold text-capitalize"
                                        value="{{ $tagihan->tagihanPerusahaan->kegiatan ?? $tagihan->kegiatan }}">
                                </div>


                                <!-- Nominal -->
                                <div class="col-md-3">
                                    <label class="form-label text-muted small text-uppercase">
                                        Nominal
                                    </label>

                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Rp.</span>

                                        <input type="text" name="nominal" class="form-control format-rupiah"
                                            value="{{ number_format($tagihan->nominal, 0, ',', '.') }}">
                                    </div>
                                </div>

                                {{-- Tipe --}}
                                <div class="col-md-3">

                                    <label class="form-label text-muted small text-uppercase">
                                        Tipe
                                    </label>

                                    <select name="tipe" id="tipe" class="form-select">

                                        <option value="tahunan" {{ $tagihan->tagihanPerusahaan->tipe ?? $tagihan->kegiatan === 'tahunan' ? 'selected' : '' }}>
                                            Tahunan
                                        </option>

                                        <option value="bulanan" {{ $tagihan->tagihanPerusahaan->tipe ?? $tagihan->kegiatan === 'bulanan' ? 'selected' : '' }}>
                                            Bulanan
                                        </option>

                                    </select>

                                </div>

                                {{-- Status --}}
                                <div class="col-md-3">

                                    <label class="form-label text-muted small text-uppercase">
                                        Status
                                    </label>

                                    <select name="status" id="status" class="form-select" {{ in_array($tagihan->status, ['selesai', 'telat']) ? 'disabled' : '' }}>

                                        <option value="pending" {{ $tagihan->status === 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>

                                        <option value="proses" {{ $tagihan->status === 'proses' ? 'selected' : '' }}>
                                            Proses
                                        </option>

                                        <option value="selesai" {{ $tagihan->status === 'selesai' ? 'selected' : '' }} {{ $tagihan->status === 'pending' && $tagihan->tanggal_selesai ? '' : 'disabled hidden' }}>
                                            Selesai
                                        </option>

                                        <option value="telat" {{ $tagihan->status === 'telat' ? 'selected' : '' }} disabled hidden>
                                            Terlambat
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <!-- ROW 2 -->
                            <div class="row g-4 mt-1">

                                <!-- Keterangan -->
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase">
                                        Keterangan
                                    </label>

                                    <textarea class="form-control" name="keterangan" rows="3">{{ $tagihan->keterangan ?? '-' }}</textarea>
                                </div>


                                <!-- Tanggal Perkiraan -->
                                <div class="col-md-6">

                                    <label class="form-label text-muted small text-uppercase">
                                        Tracking
                                    </label>

                                    <select name="tracking" id="tracking" class="form-select">

                                        <option {{ $tagihan->tracking === 'Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager' ? 'selected' : '' }} value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                        <option {{ $tagihan->tracking === 'Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi' ? 'selected' : '' }} value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                        <option {{ $tagihan->tracking === 'Finance Menunggu Approve Direksi' ? 'selected' : '' }} value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi</option>
                                        <option {{ $tagihan->tracking === 'Diajukan dan Sedang Ditinjau oleh Finance' ? 'selected' : '' }} value="Diajukan dan Sedang Ditinjau oleh Finance">Diajukan dan Sedang Ditinjau oleh Finance</option>
                                        <option {{ $tagihan->tracking === 'Membuat Permintaan Ke Direktur Utama' ? 'selected' : '' }} value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur Utama</option>
                                        <option {{ $tagihan->tracking === 'Pengajuan sedang dalam proses Pencairan' ? 'selected' : '' }} value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam proses Pencairan</option>
                                        <option {{ $tagihan->tracking === 'Pencairan Sudah Selesai' ? 'selected' : '' }} value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                        <option {{ $tagihan->tracking === 'Selesai' ? 'selected' : '' }} value="Selesai">Selesai</option>

                                    </select>

                                </div>
                                
                            </div>

                            <!-- ROW 3 -->
                            <div class="row g-4 mt-1">
                                
                                <!-- Tanggal Perkiraan Mulai -->
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Tanggal Perkiraan Mulai
                                    </label>
                                    
                                    <div>
                                        <input type="date" name="tanggal_perkiraan_mulai" class="form-control col-md-6"
                                            value="{{ $tagihan->tagihanPerusahaan->tanggal_perkiraan_mulai ?? $tagihan->tanggal_perkiraan_mulai }}">
                                    </div>
                                </div>
                                
                                <!-- Tanggal Perkiraan Selesai -->
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Tanggal Perkiraan Selesai
                                    </label>
                                    
                                    <div>
                                        <input type="date" name="tanggal_perkiraan_selesai" class="form-control col-md-6"
                                            value="{{ $tagihan->tagihanPerusahaan->tanggal_perkiraan_selesai ?? $tagihan->tanggal_perkiraan_selesai }}">
                                    </div>
                                </div>
                                
                                <!-- Tanggal Selesai -->
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Tanggal Realisasi
                                    </label>
                                    
                                    <div>
                                        <input type="date" name="tanggal_selesai" class="form-control col-md-6"
                                            value="{{ $tagihan->tanggal_selesai }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="mt-4 justify-content-between">
                                <a class="btn btn-secondary" href="{{ route('office.tagihanPerusahaan.index') }}">
                                    Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Simpan Perubahan
                                </button>
                            </div>

                        </form>

                    </div>

                </div>
            </div>
        </div>

    </div>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function formatRupiah(angka) {
                if (!angka) return '';

                angka = angka.toString();

                let number = angka.replace(/\D/g, '');
                return number.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function unformatRupiah(angka) {
                return angka.toString().replace(/\D/g, '');
            }

            // format saat mengetik
            $(document).on('input', '.format-rupiah', function () {
                this.value = formatRupiah(this.value);
            });

            // format saat pertama load
            $('.format-rupiah').each(function(){
                this.value = formatRupiah(this.value);
            });

            // sebelum submit
            $('#updateTagihanForm').on('submit', function () {
                $(this).find('.format-rupiah').each(function(){
                    this.value = unformatRupiah(this.value);
                });
            });

        });
    </script>
@endsection
