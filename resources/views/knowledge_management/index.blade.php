@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-end mt-3 me-3">
        <a href="{{ url('/home') }}" class="btn btn-primary me-2"><i class="fas fa-home"></i> Kembali ke Home</a>
        <a href="{{ route('knowledge-management.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Buat Knowledge</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show my-3 mx-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show my-3 mx-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-3 mx-3">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1 mb-4">Knowledge Management</h3>

                    {{-- Category Filter Buttons Fit 100% Width --}}
                    <div class="row row-cols-1 row-cols-md-5 g-2 w-100 m-0 mb-3">
                        <div class="col p-1">
                            <a href="{{ route('knowledge-management.index', ['category' => 'all', 'search' => $search]) }}" 
                               class="btn w-100 text-nowrap {{ $category == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Semua ({{ $counts['total'] }})
                            </a>
                        </div>
                        <div class="col p-1">
                            <a href="{{ route('knowledge-management.index', ['category' => 'SOP', 'search' => $search]) }}" 
                               class="btn w-100 text-nowrap {{ $category == 'SOP' ? 'btn-primary' : 'btn-outline-primary' }}">
                                SOP ({{ $counts['SOP'] }})
                            </a>
                        </div>
                        <div class="col p-1">
                            <a href="{{ route('knowledge-management.index', ['category' => 'FAQ', 'search' => $search]) }}" 
                               class="btn w-100 text-nowrap {{ $category == 'FAQ' ? 'btn-primary' : 'btn-outline-primary' }}">
                                FAQ ({{ $counts['FAQ'] }})
                            </a>
                        </div>
                        <div class="col p-1">
                            <a href="{{ route('knowledge-management.index', ['category' => 'TUTORIAL', 'search' => $search]) }}" 
                               class="btn w-100 text-nowrap {{ $category == 'TUTORIAL' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Tutorial ({{ $counts['TUTORIAL'] }})
                            </a>
                        </div>
                        <div class="col p-1">
                            <a href="{{ route('knowledge-management.index', ['category' => 'Panduan Instalasi', 'search' => $search]) }}" 
                               class="btn w-100 text-nowrap {{ $category == 'Panduan Instalasi' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Panduan Instalasi ({{ $counts['Panduan Instalasi'] }})
                            </a>
                        </div>
                    </div>

                    {{-- Search Bar --}}
                    <div class="mb-4">
                        <form method="GET" action="{{ route('knowledge-management.index') }}">
                            <input type="hidden" name="category" value="{{ $category }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan judul atau isi..." value="{{ $search }}">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                                @if($search)
                                    <a href="{{ route('knowledge-management.index', ['category' => $category]) }}" class="btn btn-secondary"><i class="fas fa-times"></i> Reset</a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Table --}}
                    <table class="table table-striped align-middle w-100" id="kmTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Penulis</th>
                                <th>Tanggal</th>
                                <th>Lampiran</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($knowledgeList as $index => $item)
                                <tr>
                                    <td>{{ $knowledgeList->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('knowledge-management.show', $item->id) }}" class="fw-bold text-decoration-none text-dark">
                                            {{ $item->title }}
                                        </a>
                                        <div class="text-muted small">
                                            {{ Str::limit(strip_tags($item->content), 90) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($item->category == 'SOP')
                                            <span class="badge bg-primary">SOP</span>
                                        @elseif($item->category == 'FAQ')
                                            <span class="badge bg-success">FAQ</span>
                                        @elseif($item->category == 'TUTORIAL')
                                            <span class="badge bg-warning text-dark">Tutorial</span>
                                        @else
                                            <span class="badge bg-info text-dark">Panduan Instalasi</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->author_name }}</td>
                                    <td>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        @if ($item->file_path)
                                            <a href="{{ route('knowledge-management.download', $item->id) }}" class="btn btn-sm btn-outline-info" title="{{ $item->file_name }}">
                                                <i class="fas fa-paperclip"></i> Unduh
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton{{ $item->id }}" data-bs-toggle="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $item->id }}">
                                                <a class="dropdown-item" href="{{ route('knowledge-management.show', $item->id) }}">
                                                    <i class="fas fa-eye me-2 text-info"></i> Detail
                                                </a>
                                                <a class="dropdown-item" href="{{ route('knowledge-management.edit', $item->id) }}">
                                                    <i class="fas fa-edit me-2 text-warning"></i> Edit
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('knowledge-management.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data Knowledge Management.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $knowledgeList->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
