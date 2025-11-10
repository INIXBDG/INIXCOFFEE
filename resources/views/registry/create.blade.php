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
                    <h5 class="card-title text-center mb-4">{{ __('Buat Tugas Baru') }}</h5>

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

                    <form method="POST" action="{{ route('registry.store') }}">
                        @csrf

                        {{-- Input tersembunyi untuk pengerja_id (user yang login) --}}
                        <input type="hidden" name="pengerja_id" value="{{ Auth::id() }}">

                        <div class="row mb-3">
                            <label for="tugas" class="col-md-4 col-form-label text-md-start">Nama Tugas</label>
                            <div class="col-md-6">
                                <input type="text" name="tugas" id="tugas"
                                       class="form-control @error('tugas') is-invalid @enderror"
                                       value="{{ old('tugas') }}" placeholder="cth: Error RKM" required>
                                @error('tugas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="fitur" class="col-md-4 col-form-label text-md-start">Fitur / Modul</label>
                            <div class="col-md-6">
                                <select class="form-select @error('fitur') is-invalid @enderror" id="fitur" name="fitur" required>
                                    <option value="" disabled {{ old('fitur') ? '' : 'selected' }}>Pilih satu fitur</option>
                                    @foreach($features as $featureName)
                                        <option value="{{ $featureName }}" {{ old('fitur') == $featureName ? 'selected' : '' }}>
                                            {{ $featureName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('fitur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">Tipe</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                    <option value="" disabled selected>-- Pilih Tipe --</option>
                                    <option value="Request" {{ old('tipe') == 'Request' ? 'selected' : '' }}>Request</option>
                                    <option value="Error" {{ old('tipe') == 'Error' ? 'selected' : '' }}>Error</option>
                                    <option value="Online" {{ old('tipe') == 'Online' ? 'selected' : '' }}>Online</option>
                                    <option value="Lainnya" {{ old('tipe') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('tipe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pemilik" class="col-md-4 col-form-label text-md-start">Pemilik</label>
                            <div class="col-md-6">
                                <select name="pemilik" id="pemilik" class="form-select @error('pemilik') is-invalid @enderror" required>
                                    <option value="" disabled selected>-- Pilih Jabatan --</option>
                                    <option value="Instruktur" {{ old('pemilik') == 'Instruktur' ? 'selected' : '' }}>Instruktur</option>
                                    <option value="Direktur Utama" {{ old('pemilik') == 'Direktur Utama' ? 'selected' : '' }}>Direktur Utama</option>
                                    <option value="Direktur" {{ old('pemilik') == 'Direktur' ? 'selected' : '' }}>Direktur</option>
                                    <option value="Education Manager" {{ old('pemilik') == 'Education Manager' ? 'selected' : '' }}>Education Manager</option>
                                    <option value="Technical Support" {{ old('pemilik') == 'Technical Support' ? 'selected' : '' }}>Technical Support</option>
                                    <option value="GM" {{ old('pemilik') == 'GM' ? 'selected' : '' }}>GM</option>
                                    <option value="SPV Sales" {{ old('pemilik') == 'SPV Sales' ? 'selected' : '' }}>SPV Sales</option>
                                    <option value="Tim Digital" {{ old('pemilik') == 'Tim Digital' ? 'selected' : '' }}>Tim Digital</option>
                                    <option value="Sales" {{ old('pemilik') == 'Sales' ? 'selected' : '' }}>Sales</option>
                                    <option value="Office Manager" {{ old('pemilik') == 'Office Manager' ? 'selected' : '' }}>Office Manager</option>
                                    <option value="Finance & Accounting" {{ old('pemilik') == 'Finance & Accounting' ? 'selected' : '' }}>Finance & Accounting</option>
                                    <option value="Koordinator Office" {{ old('pemilik') == 'Koordinator Office' ? 'selected' : '' }}>Koordinator Office</option>
                                    <option value="Admin Holding" {{ old('pemilik') == 'Admin Holding' ? 'selected' : '' }}>Admin Holding</option>
                                    <option value="Customer Care" {{ old('pemilik') == 'Customer Care' ? 'selected' : '' }}>Customer Care</option>
                                    <option value="Koordinator ITSM" {{ old('pemilik') == 'Koordinator ITSM' ? 'selected' : '' }}>Koordinator ITSM</option>
                                    <option value="Office Boy" {{ old('pemilik') == 'Office Boy' ? 'selected' : '' }}>Office Boy</option>
                                    <option value="Driver" {{ old('pemilik') == 'Driver' ? 'selected' : '' }}>Driver</option>
                                    <option value="Outsource" {{ old('pemilik') == 'Outsource' ? 'selected' : '' }}>Outsource</option>
                                    <option value="HRD" {{ old('pemilik') == 'HRD' ? 'selected' : '' }}>HRD</option>
                                    <option value="Adm Sales" {{ old('pemilik') == 'Adm Sales' ? 'selected' : '' }}>Adm Sales</option>
                                    <option value="Programmer" {{ old('pemilik') == 'Programmer' ? 'selected' : '' }}>Programmer</option>
                                </select>
                                @error('pemilik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">Status</label>
                            <div class="col-md-6">
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="Antrian" {{ old('status', 'Antrian') == 'Antrian' ? 'selected' : '' }}>Antrian</option>
                                    <option value="Sedang Dikerjakan" {{ old('status') == 'Sedang Dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                    <option value="Sudah Selesai namun Menunggu Hasil" {{ old('status') == 'Sudah Selesai namun Menunggu Hasil' ? 'selected' : '' }}>Sudah Selesai namun Menunggu Hasil</option>
                                    <option value="Selesai" {{ old('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div> --}}

                        <div class="row mb-3">
                            <label for="catatan" class="col-md-4 col-form-label text-md-start">Catatan (Opsional)</label>
                            <div class="col-md-6">
                                <textarea name="catatan" id="catatan"
                                          class="form-control @error('catatan') is-invalid @enderror"
                                          rows="3" placeholder="Tambahkan catatan jika perlu...">{{ old('catatan') }}</textarea>
                                @error('catatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan Tugas
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
