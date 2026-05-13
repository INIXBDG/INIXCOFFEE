@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body" id="card">

                    <a href="{{ route('registry.index') }}" class="btn btn-outline-secondary my-2">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>

                    <h5 class="card-title text-center mb-4">{{ __('Edit Tugas') }}</h5>

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

                    <form method="POST" action="{{ route('registry.update', $tugas->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="ticket_id" class="col-md-4 col-form-label text-md-start">Nomor Ticket</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" value="{{ $tugas->ticket_id ?? '-' }}" disabled>
                                <input type="hidden" name="ticket_id" value="{{ $tugas->ticket_id }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tugas" class="col-md-4 col-form-label text-md-start">Nama Tugas</label>
                            <div class="col-md-6">
                                <input type="text" name="tugas" id="tugas"
                                       class="form-control @error('tugas') is-invalid @enderror"
                                       value="{{ old('tugas', $tugas->tugas) }}" placeholder="cth: Error RKM" required>
                                @error('tugas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="fitur" class="col-md-4 col-form-label text-md-start">Fitur / Modul</label>
                            <div class="col-md-6">
                                <select class="form-select @error('fitur') is-invalid @enderror" id="fitur" name="fitur" required>
                                    <option value="" disabled>Pilih satu fitur</option>
                                    @foreach($features as $featureName)
                                        <option value="{{ $featureName }}" {{ old('fitur', $tugas->fitur) == $featureName ? 'selected' : '' }}>
                                            {{ $featureName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fitur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">Tipe</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                    <option value="" disabled>-- Pilih Tipe --</option>
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
                                <input type="text" class="form-control" value="{{ $tugas->pemilik ?? 'Belum ada pemilik' }}" disabled>
                                <input type="hidden" name="pemilik" value="{{ $tugas->pemilik }}">
                                @error('pemilik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- PERBAIKAN: Mengaktifkan dropdown pemilihan Pengerja -->
                        <div class="row mb-3">
                            <label for="pengerja_id" class="col-md-4 col-form-label text-md-start">Pengerja</label>
                            <div class="col-md-6">
                                <select name="pengerja_id" id="pengerja_id" class="form-select @error('pengerja_id') is-invalid @enderror">
                                    <option value="">-- Belum ada pengerja --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('pengerja_id', $tugas->pengerja_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->karyawan ? $user->karyawan->nama_lengkap : $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pengerja_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="fakta" class="col-md-4 col-form-label text-md-start">Fakta</label>
                            <div class="col-md-6">
                                <textarea name="fakta" id="fakta" class="form-control @error('fakta') is-invalid @enderror" rows="3" required>{{ old('fakta', $tugas->fakta) }}</textarea>
                                @error('fakta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harapan" class="col-md-4 col-form-label text-md-start">Harapan</label>
                            <div class="col-md-6">
                                <textarea name="harapan" id="harapan" class="form-control @error('harapan') is-invalid @enderror" rows="3" required>{{ old('harapan', $tugas->harapan) }}</textarea>
                                @error('harapan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- PERBAIKAN: Kontrol Akses Berbasis Peran pada Waktu Perkiraan -->
                        <div class="row mb-3">
                            <label for="waktu_perkiraan" class="col-md-4 col-form-label text-md-start">Waktu Perkiraan (Menit)</label>
                            <div class="col-md-6">
                                @if(auth()->check() && optional(auth()->user()->karyawan)->jabatan == 'Koordinator ITSM')
                                    <input type="number" name="waktu_perkiraan" id="waktu_perkiraan" class="form-control @error('waktu_perkiraan') is-invalid @enderror" value="{{ old('waktu_perkiraan', $tugas->waktu_perkiraan) }}" min="1">
                                    @error('waktu_perkiraan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @else
                                    <input type="text" class="form-control" value="{{ $tugas->waktu_perkiraan ? $tugas->waktu_perkiraan . ' Menit' : 'Belum ditentukan' }}" disabled>
                                    <input type="hidden" name="waktu_perkiraan" value="{{ $tugas->waktu_perkiraan }}">
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_mulai" class="col-md-4 col-form-label text-md-start">Tanggal Mulai</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" value="{{ $tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('d M Y - H:i') : 'Belum ditentukan' }}" disabled>
                                <input type="hidden" name="tanggal_mulai" value="{{ $tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('Y-m-d\TH:i') : '' }}">
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">Tanggal Akhir</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" value="{{ $tugas->tanggal_akhir ? $tugas->tanggal_akhir->format('d M Y - H:i') : 'Belum ditentukan' }}" disabled>
                                <input type="hidden" name="tanggal_akhir" value="{{ $tugas->tanggal_akhir ? $tugas->tanggal_akhir->format('Y-m-d\TH:i') : '' }}">
                                @error('tanggal_akhir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- PERBAIKAN: Kontrol Akses Berbasis Peran pada Catatan -->
                        <div class="row mb-3">
                            <label for="catatan" class="col-md-4 col-form-label text-md-start">Catatan (Opsional)</label>
                            <div class="col-md-6">
                                @if(auth()->check() && optional(auth()->user()->karyawan)->jabatan == 'Koordinator ITSM')
                                    <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3" placeholder="Tambahkan catatan jika perlu...">{{ old('catatan', $tugas->catatan) }}</textarea>
                                    @error('catatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @else
                                    <textarea class="form-control" rows="3" disabled>{{ $tugas->catatan ?? 'Belum ada catatan' }}</textarea>
                                    <input type="hidden" name="catatan" value="{{ $tugas->catatan }}">
                                @endif
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
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
