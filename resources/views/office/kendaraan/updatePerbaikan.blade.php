@extends('layouts_office.app')

@section('office_contents')
    @php
        $isReadOnly = Auth::user()->jabatan !== 'Driver';
    @endphp

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0">Detail & Edit Perbaikan Kendaraan</h3>
            <a href="{{ route('office.indexPerbaikanKendaraan') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card shadow-sm glass-force">
            <div class="card-body">
                <form action="{{ route('office.updatePerbaikanKendaraan', $perbaikan->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">

                        {{-- Nama Kendaraan --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Kendaraan *</label>
                            <select name="kendaraan" class="form-select" required @disabled($isReadOnly)>
                                <option value="H1"
                                    {{ old('kendaraan', $perbaikan->kendaraan) == 'H1' ? 'selected' : '' }}>
                                    H1
                                </option>
                                <option value="Innova"
                                    {{ old('kendaraan', $perbaikan->kendaraan) == 'Innova' ? 'selected' : '' }}>
                                    Innova
                                </option>
                            </select>
                        </div>

                        {{-- Tipe Kondisi --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipe Kondisi *</label>
                            <select name="type_condition" id="type_condition" class="form-select" required
                                @disabled($isReadOnly)>
                                <option value="Perawatan"
                                    {{ old('type_condition', $perbaikan->type_condition) == 'Perawatan' ? 'selected' : '' }}>
                                    Perawatan
                                </option>
                                <option value="Kecelakaan"
                                    {{ old('type_condition', $perbaikan->type_condition) == 'Kecelakaan' ? 'selected' : '' }}>
                                    Kecelakaan
                                </option>
                            </select>
                        </div>

                        {{-- Tingkat Kerusakan --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tingkat Kerusakan *</label>
                            <select name="type_vehicle_condition" class="form-select" required @disabled($isReadOnly)>
                                <option value="Kerusakan Ringan"
                                    {{ old('type_vehicle_condition', $perbaikan->type_vehicle_condition) == 'Kerusakan Ringan' ? 'selected' : '' }}>
                                    Kerusakan Ringan
                                </option>
                                <option value="Kerusakan Sedang"
                                    {{ old('type_vehicle_condition', $perbaikan->type_vehicle_condition) == 'Kerusakan Sedang' ? 'selected' : '' }}>
                                    Kerusakan Sedang
                                </option>
                                <option value="Kerusakan Berat"
                                    {{ old('type_vehicle_condition', $perbaikan->type_vehicle_condition) == 'Kerusakan Berat' ? 'selected' : '' }}>
                                    Kerusakan Berat
                                </option>
                                <option value="Kerusakan Total"
                                    {{ old('type_vehicle_condition', $perbaikan->type_vehicle_condition) == 'Kerusakan Total' ? 'selected' : '' }}>
                                    Kerusakan Total
                                </option>
                            </select>
                        </div>

                        {{-- Jenis Perbaikan --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Perbaikan *</label>
                            <select name="type_repair" class="form-select" required @disabled($isReadOnly)>
                                <option value="Penggantian"
                                    {{ old('type_repair', $perbaikan->type_repair) == 'Penggantian' ? 'selected' : '' }}>
                                    Penggantian
                                </option>
                                <option value="Peningkatan"
                                    {{ old('type_repair', $perbaikan->type_repair) == 'Peningkatan' ? 'selected' : '' }}>
                                    Peningkatan
                                </option>
                                <option value="Perbaikan"
                                    {{ old('type_repair', $perbaikan->type_repair) == 'Perbaikan' ? 'selected' : '' }}>
                                    Perbaikan
                                </option>
                                <option value="Perbaikan Total"
                                    {{ old('type_repair', $perbaikan->type_repair) == 'Perbaikan Total' ? 'selected' : '' }}>
                                    Perbaikan Total
                                </option>
                            </select>
                        </div>

                        {{-- Section Kecelakaan --}}
                        <div id="sectionKecelakaan"
                            class="row g-4 {{ old('type_condition', $perbaikan->type_condition) == 'Kecelakaan' ? '' : 'd-none' }}">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Kejadian *</label>
                                <input type="date" name="tanggal_kejadian" class="form-control"
                                    value="{{ old('tanggal_kejadian', $perbaikan->tanggal_kejadian) }}"
                                    @disabled($isReadOnly)>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Waktu Kejadian *</label>
                                <input type="time" name="waktu_kejadian" class="form-control"
                                    value="{{ old('waktu_kejadian', $perbaikan->waktu_kejadian) }}"
                                    @disabled($isReadOnly)>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Lokasi Kejadian *</label>
                                <input type="text" name="lokasi" class="form-control"
                                    value="{{ old('lokasi', $perbaikan->lokasi) }}" @disabled($isReadOnly)>
                            </div>
                        </div>

                        {{-- Estimasi --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Estimasi Biaya</label>

                            <input type="text" id="estimasi_display" class="form-control"
                                value="{{ old('estimasi', $perbaikan->estimasi) }}" @disabled($isReadOnly)>

                            <input type="hidden" name="estimasi" id="estimasi"
                                value="{{ old('estimasi', $perbaikan->estimasi) }}">
                        </div>

                        {{-- Deskripsi --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi Kondisi</label>
                            <textarea name="deskripsi_kondisi" class="form-control" rows="3" @disabled($isReadOnly)>{{ old('deskripsi_kondisi', $perbaikan->deskripsi_kondisi) }}</textarea>
                        </div>

                        {{-- Bukti --}}
                        <div class="col-12 mb-5">
                            <label class="form-label fw-semibold">Bukti (Foto / Video)</label>

                            @if ($perbaikan->bukti)
                                @php
                                    $extension = strtolower(pathinfo($perbaikan->bukti, PATHINFO_EXTENSION));
                                    $fileUrl = asset('storage/' . $perbaikan->bukti);
                                @endphp

                                <div class="mb-3">

                                    @if (in_array($extension, ['jpg', 'jpeg', 'png']))
                                        <img src="{{ $fileUrl }}" class="img-fluid rounded shadow-sm border"
                                            style="max-height:250px;">
                                    @elseif (in_array($extension, ['mp4', 'mov', 'avi']))
                                        <video class="rounded shadow-sm border" style="max-height:250px;" controls>
                                            <source src="{{ $fileUrl }}">
                                            Browser tidak mendukung video.
                                        </video>
                                    @else
                                        <div class="alert alert-warning">
                                            File tidak dapat ditampilkan.
                                        </div>
                                    @endif

                                    <div class="mt-2">
                                        <a href="{{ $fileUrl }}" class="btn btn-sm btn-outline-primary"
                                            target="_blank">
                                            <i class="fas fa-download"></i> Download Bukti
                                        </a>
                                    </div>

                                </div>
                            @else
                                <div class="alert alert-light border">
                                    Belum ada bukti yang diupload.
                                </div>
                            @endif

                            <input type="file" name="bukti" class="form-control" accept="image/*,video/*"
                                @disabled($isReadOnly)>
                        </div>

                        
                        <h5 class="mt-5">Detail Perbaikan dan Invoice</h5>

                        <div class="row g-4 mt-0">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Tanggal Perbaikan</label>
                                <input type="date" name="tanggal_perbaikan" class="form-control" @disabled($isReadOnly) value="{{ $perbaikan->tanggal_perbaikan }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi Perbaikan</label>
                                <textarea name="deskripsi_perbaikan" class="form-control" rows="4" @disabled($isReadOnly)>{{ $perbaikan->deskripsi_perbaikan }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Invoice<span
                                        style="text-danger">*</span></label>
                                @php
                                    $extension = strtolower(pathinfo($perbaikan->invoice, PATHINFO_EXTENSION));
                                    $fileUrl = asset('storage/' . $perbaikan->invoice);
                                @endphp

                                <div class="mb-3">

                                    @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']))
                                        <img src="{{ $fileUrl }}" class="img-fluid rounded shadow-sm border"
                                            style="max-height:250px;">

                                    @elseif (in_array($extension, ['mp4', 'mov', 'avi', 'webm']))
                                        <video class="rounded shadow-sm border" style="max-height:250px;" controls>
                                            <source src="{{ $fileUrl }}">
                                            Browser tidak mendukung video.
                                        </video>

                                    @elseif ($extension === 'pdf')
                                        <iframe src="{{ $fileUrl }}" class="w-100 border rounded"
                                            style="height:400px;"></iframe>

                                    @elseif (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
                                        <div class="alert alert-info">
                                            File dokumen tidak bisa ditampilkan langsung. Harap download file<br>
                                        </div>

                                    @elseif (!$perbaikan->invoice)
                                    @else
                                        <div class="alert alert-warning">
                                            File tidak dapat ditampilkan.<br>
                                        </div>
                                    @endif

                                    @if ($perbaikan->invoice)
                                        <div class="my-2">
                                            <a href="{{ $fileUrl }}" class="btn btn-sm btn-outline-primary"
                                                target="_blank">
                                                <i class="fas fa-download"></i> Download Invoice
                                            </a>
                                        </div>
                                    @endif

                                    <input type="file" name="invoice" class="form-control"
                                        @disabled($isReadOnly)>

                                </div>
                            </div>

                        </div>

                    </div>

                    @if (!$isReadOnly)
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('office.indexPerbaikanKendaraan') }}" class="btn btn-light border">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    @else
                        <div class="alert alert-secondary mt-4 mb-0 text-center">
                            <i class="fas fa-lock"></i> Mode Lihat Saja
                        </div>
                    @endif

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const displayInput = document.getElementById('estimasi_display');
            const hiddenInput = document.getElementById('estimasi');

            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }

            function parseRupiah(rupiah) {
                return rupiah.replace(/[^0-9]/g, '');
            }

            if (displayInput.value) {
                displayInput.value = formatRupiah(parseRupiah(displayInput.value));
            }

            displayInput.addEventListener('input', function() {
                let angka = parseRupiah(this.value);

                hiddenInput.value = angka;

                if (angka) {
                    this.value = formatRupiah(angka);
                } else {
                    this.value = '';
                }
            });

        });
        document.addEventListener('DOMContentLoaded', function() {
            const typeCondition = document.getElementById('type_condition');
            const sectionKecelakaan = document.getElementById('sectionKecelakaan');

            typeCondition.addEventListener('change', function() {
                if (this.value === 'Kecelakaan') {
                    sectionKecelakaan.classList.remove('d-none');
                } else {
                    sectionKecelakaan.classList.add('d-none');
                }
            });
        });
    </script>

@endsection
