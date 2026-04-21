@extends('layouts.app')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body" id="card">
                        <a href="{{ route('pengajuanklaimmodul.index') }}" class="btn click-primary my-2">
                            <img src="{{ asset('icon/arrow-left.svg') }}" width="20px"> Back
                        </a>
                        <h5 class="card-title text-center mb-4">{{ __('Ajukan Klaim Modul') }}</h5>
                        <form method="POST" action="{{ route('pengajuanklaimmodul.store') }}">
                            @csrf
                            <div class="row mb-3">
                                <label for="nama_karyawan"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                                <div class="col-md-6">
                                    <input type="hidden" name="kode_karyawan" value="{{ $karyawan->kode_karyawan }}">
                                    <input disabled id="nama_karyawan" type="text" class="form-control"
                                        value="{{ $karyawan->nama_lengkap }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                                <div class="col-md-6">
                                    <input disabled id="divisi" type="text" class="form-control"
                                        value="{{ $karyawan->divisi }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="title"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Judul Modul') }}</label>
                                <div class="col-md-6">
                                    <input id="title" type="text" class="form-control @error('title') is-invalid @enderror"
                                        name="title" value="{{ old('title') }}" required autofocus>
                                    @error('title') <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="category"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Kategori Materi') }}</label>
                                <div class="col-md-6">
                                    <select id="category" name="category"
                                        class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $cat)
                                            @if($cat)
                                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('category') <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="instructors"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Instruktur') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <select name="instructors[]" id="instructors"
                                        class="form-select select2 @error('instructors') is-invalid @enderror" multiple
                                        required>
                                        @foreach($instructors as $instructor)
                                            <option value="{{ $instructor->id }}" {{ in_array($instructor->id, old('instructors', $selectedInstructors ?? [])) ? 'selected' : '' }}>
                                                {{ $instructor->karyawan->nama_lengkap ?? $instructor->username ?? $instructor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('instructors') <span
                                    class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="description"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Deskripsi (Opsional)') }}</label>
                                <div class="col-md-6">
                                    <textarea id="description"
                                        class="form-control @error('description') is-invalid @enderror" name="description"
                                        rows="4">{{ old('description') }}</textarea>
                                    @error('description') <span
                                    class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="link" class="col-md-4 col-form-label text-md-start">{{ __('Link Modul') }} <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-6">
                                    <input id="link" type="url" class="form-control @error('link') is-invalid @enderror"
                                        name="link" value="{{ old('link') }}" placeholder="https://..." required>
                                    @error('link') <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn click-primary">{{ __('Simpan Pengajuan') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#instructors').select2({ theme: 'bootstrap-5', placeholder: 'Pilih Instruktur', allowClear: true, width: '100%' });
        });
    </script>
@endpush