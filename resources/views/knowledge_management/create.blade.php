@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card my-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <a href="{{ route('knowledge-management.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <a href="{{ url('/home') }}" class="btn btn-primary"><i class="fas fa-home"></i> Kembali ke Home</a>
                    </div>
                    <h5 class="card-title text-center mb-4">Tambah Knowledge Management</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('knowledge-management.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Knowledge <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Masukkan Judul..." required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Pilih Kategori</option>
                                <option value="SOP" {{ old('category') == 'SOP' ? 'selected' : '' }}>SOP</option>
                                <option value="FAQ" {{ old('category') == 'FAQ' ? 'selected' : '' }}>FAQ</option>
                                <option value="TUTORIAL" {{ old('category') == 'TUTORIAL' ? 'selected' : '' }}>TUTORIAL</option>
                                <option value="Panduan Instalasi" {{ old('category') == 'Panduan Instalasi' ? 'selected' : '' }}>Panduan Instalasi</option>
                            </select>
                            @error('category')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Isi / Deskripsi Detail <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8" placeholder="Masukkan Isi / Deskripsi Detail..." required>{{ old('content') }}</textarea>
                            @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Lampiran File (Opsional)</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            <small class="form-text text-muted">Format: PDF, DOC, DOCX, ZIP, PNG, JPG (Maks. 20MB)</small>
                            @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
