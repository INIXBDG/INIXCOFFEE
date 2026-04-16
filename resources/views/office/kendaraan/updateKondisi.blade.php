@extends('layouts_office.app')

@section('office_contents')
    @php
        $isReadOnly = Auth::user()->jabatan !== 'Driver';
    @endphp

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0">Detail & Edit Kondisi Kendaraan</h3>
            <a href="{{ route('office.indexKondisiKendaraan') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card shadow-sm glass-force">
            <div class="card-body">
                <form action="{{ route('office.updateKondisiKendaraan', $kondisi->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- 1. Informasi Umum --}}
                    <h5 class="text-primary mb-3"><i class="fas fa-info-circle"></i> Informasi Umum</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">User ID / Driver</label>
                            <input type="text" name="user_id" class="form-control"
                                value="{{ old('user_id', $kondisi->user->karyawan->nama_lengkap) }}" required @disabled($isReadOnly)>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jenis Kendaraan</label>
                            <select name="jenis_kendaraan" class="form-select" required @disabled($isReadOnly)>
                                <option value="Innova"
                                    {{ old('jenis_kendaraan', $kondisi->jenis_kendaraan) == 'Innova' ? 'selected' : '' }}>
                                    Innova</option>
                                <option value="H1"
                                    {{ old('jenis_kendaraan', $kondisi->jenis_kendaraan) == 'H1' ? 'selected' : '' }}>H1
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Pemeriksaan</label>
                            <input type="date" name="tanggal_pemeriksaan" class="form-control"
                                value="{{ old('tanggal_pemeriksaan', $kondisi->tanggal_pemeriksaan) }}" required
                                @disabled($isReadOnly)>
                        </div>
                    </div>

                    <hr>

                    {{-- 2. Kondisi Fisik --}}
                    <h5 class="text-primary mb-3"><i class="fas fa-car-side"></i> Kondisi Fisik</h5>
                    <div class="row g-3 mb-2">
                        @php
                            $fisikFields = [
                                'fisik_baik' => 'Body Fisik Baik',
                                'bersih' => 'Kebersihan',
                                'wiper_baik' => 'Kondisi Wiper',
                                'klakson_baik' => 'Fungsi Klakson',
                                'lampu_baik' => 'Fungsi Lampu',
                                'tekanan_ban_baik' => 'Tekanan Ban',
                                'ban_baik' => 'Kondisi Ban',
                                'ban_cadangan_lengkap' => 'Ban Cadangan',
                                'setir_pedal_baik' => 'Setir & Pedal',
                            ];
                        @endphp
                        @foreach ($fisikFields as $field => $label)
                            <div class="col-md-3">
                                <label class="form-label small">{{ $label }}</label>
                                <select name="{{ $field }}"
                                    class="form-select {{ $kondisi->$field == 0 ? 'border-danger text-danger' : 'border-success' }}"
                                    @disabled($isReadOnly)>
                                    <option value="1" {{ old($field, $kondisi->$field) == 1 ? 'selected' : '' }}>Baik
                                        / Ada</option>
                                    <option value="0" {{ old($field, $kondisi->$field) == 0 ? 'selected' : '' }}>Buruk
                                        / Tidak</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-muted">Catatan Kondisi Fisik</label>
                        <textarea name="catatan_kondisi" class="form-control" rows="2" @disabled($isReadOnly)>{{ old('catatan_kondisi', $kondisi->catatan_kondisi) }}</textarea>
                    </div>

                    <hr>

                    {{-- 3. Mesin --}}
                    <h5 class="text-primary mb-3"><i class="fas fa-cogs"></i> Mesin & Perawatan</h5>
                    <div class="row g-3 mb-2">
                        @php
                            $mesinFields = [
                                'oli_baik' => 'Oli Mesin',
                                'radiator_baik' => 'Air Radiator',
                                'air_wiper_baik' => 'Air Wiper',
                                'minyak_rem_baik' => 'Minyak Rem',
                                'aki_baik' => 'Kondisi Aki',
                            ];
                        @endphp
                        @foreach ($mesinFields as $field => $label)
                            <div class="col-md-3">
                                <label class="form-label small">{{ $label }}</label>
                                <select name="{{ $field }}"
                                    class="form-select {{ $kondisi->$field == 0 ? 'border-danger text-danger' : 'border-success' }}"
                                    @disabled($isReadOnly)>
                                    <option value="1" {{ old($field, $kondisi->$field) == 1 ? 'selected' : '' }}>Baik
                                        / Cukup</option>
                                    <option value="0" {{ old($field, $kondisi->$field) == 0 ? 'selected' : '' }}>Buruk
                                        / Kurang</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-muted">Catatan Mesin</label>
                        <textarea name="catatan_mesin" class="form-control" rows="2" @disabled($isReadOnly)>{{ old('catatan_mesin', $kondisi->catatan_mesin) }}</textarea>
                    </div>

                    <hr>

                    {{-- 4. Dokumen & Fasilitas --}}
                    <h5 class="text-primary mb-3"><i class="fas fa-clipboard-list"></i> Dokumen & Fasilitas</h5>
                    <div class="row g-3 mb-2">
                        @php
                            $fasilitasFields = [
                                'dokumen_lengkap' => 'STNK & Dokumen',
                                'jas_hujan_ada' => 'Jas Hujan',
                                'pengharum_ada' => 'Pengharum',
                                'ac_baik' => 'AC',
                                'audio_baik' => 'Audio',
                                'charger_ada' => 'Charger',
                                'air_minum_ada' => 'Air Minum',
                                'tisu_ada' => 'Tisu',
                                'hand_sanitizer_ada' => 'Hand Sanitizer',
                                'bbm_cukup' => 'BBM',
                                'etol_aktif' => 'E-Toll',
                            ];
                        @endphp
                        @foreach ($fasilitasFields as $field => $label)
                            <div class="col-md-3">
                                <label class="form-label small">{{ $label }}</label>
                                <select name="{{ $field }}"
                                    class="form-select {{ $kondisi->$field == 0 ? 'border-danger text-danger' : 'border-success' }}"
                                    @disabled($isReadOnly)>
                                    <option value="1" {{ old($field, $kondisi->$field) == 1 ? 'selected' : '' }}>
                                        Lengkap / Ada</option>
                                    <option value="0" {{ old($field, $kondisi->$field) == 0 ? 'selected' : '' }}>
                                        Tidak
                                        / Rusak</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Catatan Perlengkapan</label>
                            <textarea name="catatan_perlengkapan" class="form-control" rows="2" @disabled($isReadOnly)>{{ old('catatan_perlengkapan', $kondisi->catatan_perlengkapan) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Catatan Fasilitas</label>
                            <textarea name="catatan_fasilitas" class="form-control" rows="2" @disabled($isReadOnly)>{{ old('catatan_fasilitas', $kondisi->catatan_fasilitas) }}</textarea>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label small text-muted">Ajukan Keluhan/Permintaan Perbaikan (opsional)</label>
                            <textarea name="keluhan" class="form-control" rows="2" @disabled($isReadOnly)>{{ old('keluhan', $kondisi->keluhan) }}</textarea>
                        </div>
                    </div>

                    {{-- Tombol Aksi (Hanya muncul jika Driver / !isReadOnly) --}}
                    @if (!$isReadOnly)
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('office.indexKondisiKendaraan') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save"></i> Simpan
                                Perubahan</button>
                        </div>
                    @else
                        {{-- Opsional: Pesan informasi jika mode Read Only --}}
                        <div class="alert alert-secondary mt-4 mb-0 text-center">
                            <i class="fas fa-lock"></i> Anda sedang dalam mode <strong>Lihat Saja</strong>. Hanya Driver
                            yang dapat mengubah data ini.
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
