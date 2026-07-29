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
                    <h5 class="card-title text-center mb-4">Edit Knowledge Management</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('knowledge-management.update', $knowledge->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul Knowledge <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $knowledge->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="SOP" {{ old('category', $knowledge->category) == 'SOP' ? 'selected' : '' }}>SOP</option>
                                <option value="FAQ" {{ old('category', $knowledge->category) == 'FAQ' ? 'selected' : '' }}>FAQ</option>
                                <option value="TUTORIAL" {{ old('category', $knowledge->category) == 'TUTORIAL' ? 'selected' : '' }}>TUTORIAL</option>
                                <option value="Panduan Instalasi" {{ old('category', $knowledge->category) == 'Panduan Instalasi' ? 'selected' : '' }}>Panduan Instalasi</option>
                            </select>
                            @error('category')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Isi / Deskripsi Detail <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8" required>{{ old('content', $knowledge->content) }}</textarea>
                            @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Lampiran File (Opsional)</label>
                            @if ($knowledge->file_path)
                                <div class="mb-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <span><i class="fas fa-paperclip me-1"></i> {{ $knowledge->file_name }}</span>
                                    <a href="{{ route('knowledge-management.download', $knowledge->id) }}" class="btn btn-sm btn-outline-primary">Unduh File</a>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            <small class="form-text text-muted">Format: PDF, DOC, DOCX, ZIP, PNG, JPG (Maks. 20MB)</small>
                            @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
