@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body" id="card">

                    {{-- Tombol Back/Kembali --}}
                    <a href="{{ route('registry.index') }}" class="btn btn-outline-secondary my-2">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>

                    {{-- Judul --}}
                    <h5 class="card-title text-center mb-4">{{ __('Edit Tugas') }}</h5>

                    {{-- Menampilkan Error Validasi --}}
                    @if ($errors->any())
                        <div class="alert alert-danger shadow-sm">
                            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validasi Gagal!</h5>
                            <p>Ada beberapa masalah dengan data yang Anda masukkan:</p>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- [PERUBAHAN] Action form ke route 'update' dan method 'PUT' --}}
                    <form method="POST" action="{{ route('registry.update', $tugas->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="tugas" class="col-md-4 col-form-label text-md-start">Nama Tugas</label>
                            <div class="col-md-6">
                                <input type="text" name="tugas" id="tugas"
                                       class="form-control @error('tugas') is-invalid @enderror"
                                       {{-- [PERUBAHAN] Mengisi value dari $tugas --}}
                                       value="{{ old('tugas', $tugas->tugas) }}" placeholder="cth: Error RKM" required>
                                @error('tugas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">Tipe</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                    <option value="" disabled>-- Pilih Tipe --</option>
                                    {{-- [PERUBAHAN] Memeriksa 'old' atau $tugas->tipe --}}
                                    <option value="Request" {{ old('tipe', $tugas->tipe) == 'Request' ? 'selected' : '' }}>Request</option>
                                    <option value="Error" {{ old('tipe', $tugas->tipe) == 'Error' ? 'selected' : '' }}>Error</option>
                                    <option value="Online" {{ old('tipe', $tugas->tipe) == 'Online' ? 'selected' : '' }}>Online</option>
                                    <option value="Lainnya" {{ old('tipe', $tugas->tipe) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('tipe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pemilik" class="col-md-4 col-form-label text-md-start">Pemilik</label>
                            <div class="col-md-6">
                                <!-- Tampilkan jabatan -->
                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $tugas->pemilik ?? 'Belum ada pemilik' }}"
                                    disabled
                                >

                                <!-- Hidden input untuk tetap mengirim nilai -->
                                <input
                                    type="hidden"
                                    name="pemilik"
                                    value="{{ $tugas->pemilik }}"
                                >

                                @error('pemilik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        {{-- [TAMBAHAN] Dropdown Pengerja (sesuai controller 'edit') --}}
                        <div class="row mb-3">
                            <label for="pengerja_id" class="col-md-4 col-form-label text-md-start">Pengerja</label>
                            <div class="col-md-6">
                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $tugas->pengerja ? $tugas->pengerja->karyawan->nama_lengkap : 'Belum ada pengerja' }}"
                                    disabled
                                >

                                <!-- Hidden input tetap untuk mengirim nilai ke controller -->
                                <input
                                    type="hidden"
                                    name="pengerja_id"
                                    value="{{ $tugas->pengerja_id }}"
                                >
                            </div>
                        </div>


                        {{-- [TAMBAHAN] Input Status (sesuai validator 'update' dan gambar) --}}
                         <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">Status</label>
                            <div class="col-md-6">
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="Belum dimulai" {{ old('status', $tugas->status) == 'Belum dimulai' ? 'selected' : '' }}>Belum dimulai</option>
                                    <option value="Dalam proses" {{ old('status', $tugas->status) == 'Dalam proses' ? 'selected' : '' }}>Dalam proses</option>
                                    <option value="Diblokir" {{ old('status', $tugas->status) == 'Diblokir' ? 'selected' : '' }}>Diblokir</col-md-4>
                                    <option value="Sudah Selesai namun Menunggu Hasil" {{ old('status', $tugas->status) == 'Sudah Selesai namun Menunggu Hasil' ? 'selected' : '' }}>Sudah Selesai namun Menunggu Hasil</option>
                                    <option value="Sudah Selesai namun menunggu review dari pemilik" {{ old('status', $tugas->status) == 'Sudah Selesai namun menunggu review dari pemilik' ? 'selected' : '' }}>Sudah Selesai namun menunggu review dari pemilik</section>
                                    <option value="Selesai" {{ old('status', $tugas->status) == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- [TAMBAHAN] Input Tanggal (sesuai validator 'update') --}}
                        <div class="row mb-3">
                            <label for="tanggal_mulai" class="col-md-4 col-form-label text-md-start">Tanggal Mulai</label>
                            <div class="col-md-6">
                                <!-- Input display (disabled) -->
                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('d M Y - H:i') : 'Belum ditentukan' }}"
                                    disabled
                                >

                                <!-- Hidden input untuk submit -->
                                <input
                                    type="hidden"
                                    name="tanggal_mulai"
                                    value="{{ $tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('Y-m-d\TH:i') : '' }}"
                                >

                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">Tanggal Akhir</label>
                            <div class="col-md-6">
                                <!-- Input display (disabled) -->
                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $tugas->tanggal_akhir ? $tugas->tanggal_akhir->format('d M Y - H:i') : '' }}"
                                    disabled
                                >

                                <!-- Hidden input untuk submit -->
                                <input
                                    type="hidden"
                                    name="tanggal_akhir"
                                    value="{{ $tugas->tanggal_akhir ? $tugas->tanggal_akhir->format('Y-m-d\TH:i') : '' }}"
                                >

                                @error('tanggal_akhir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>



                        <div class="row mb-3">
                            <label for="catatan" class="col-md-4 col-form-label text-md-start">Catatan (Opsional)</label>
                            <div class="col-md-6">
                                <textarea name="catatan" id="catatan"
                                          class="form-control @error('catatan') is-invalid @enderror"
                                          rows="3" placeholder="Tambahkan catatan jika perlu...">{{ old('catatan', $tugas->catatan) }}</textarea>
                                @error('catatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                {{-- [PERUBAHAN] Teks tombol submit --}}
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Tugas
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
