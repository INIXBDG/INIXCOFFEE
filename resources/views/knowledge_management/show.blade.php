@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card my-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <a href="{{ route('knowledge-management.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <a href="{{ url('/home') }}" class="btn btn-primary"><i class="fas fa-home"></i> Kembali ke Home</a>
                    </div>
                    <h4 class="card-title text-center mb-4">Detail Knowledge Management</h4>

                    <div class="row mb-3">
                        <div class="col-md-3 font-weight-bold">Judul</div>
                        <div class="col-md-9">: {{ $knowledge->title }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 font-weight-bold">Kategori</div>
                        <div class="col-md-9">: 
                            @if ($knowledge->category == 'SOP')
                                <span class="badge bg-primary">SOP</span>
                            @elseif($knowledge->category == 'FAQ')
                                <span class="badge bg-success">FAQ</span>
                            @elseif($knowledge->category == 'TUTORIAL')
                                <span class="badge bg-warning text-dark">Tutorial</span>
                            @else
                                <span class="badge bg-info text-dark">Panduan Instalasi</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 font-weight-bold">Penulis</div>
                        <div class="col-md-9">: {{ $knowledge->author_name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 font-weight-bold">Tanggal Dibuat</div>
                        <div class="col-md-9">: {{ $knowledge->created_at ? $knowledge->created_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>

                    @if ($knowledge->file_path)
                        <div class="row mb-3">
                            <div class="col-md-3 font-weight-bold">Lampiran File</div>
                            <div class="col-md-9">: 
                                <a href="{{ route('knowledge-management.download', $knowledge->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> Unduh {{ $knowledge->file_name }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <hr>

                    <div class="mb-3">
                        <h5 class="font-weight-bold mb-3">Isi / Deskripsi Detail</h5>
                        <div class="p-3 bg-light border rounded" style="white-space: pre-line;">
                            {{ $knowledge->content }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('knowledge-management.edit', $knowledge->id) }}" class="btn btn-warning text-white me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('knowledge-management.destroy', $knowledge->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
