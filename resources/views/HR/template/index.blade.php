@extends('layout_HR.app')

@section('content_HR')
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Dashboard Template</li>
            </ol>
        </nav>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Daftar Template Laporan</h5>
                    <a href="{{ route('HR.reports.create') }}" class="btn btn-primary">
                        <span class="iconify me-2" data-icon="mdi:plus"></span>Buat Template Baru
                    </a>
                </div>

                @if ($templates->isNotEmpty())
                    @foreach ($templates as $category => $items)
                        <h6 class="fw-bold text-uppercase text-muted mb-3 mt-4">{{ $category }}</h6>
                        <div class="row g-3">
                            @foreach ($items as $tpl)
                                <div class="col-md-4 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <span class="iconify text-primary" data-icon="mdi:file-document-outline"
                                                    style="font-size: 2rem;"></span>
                                            </div>
                                            <h6 class="card-title fw-bold mb-1">{{ $tpl->name }}</h6>
                                            <p class="text-muted small mb-3">
                                                {{ Str::limit($tpl->description ?? 'Tidak ada deskripsi', 50) }}</p>

                                            <div class="d-flex gap-2 mt-auto">
                                                <!-- Link langsung ke halaman generate -->
                                                <a href="{{ route('HR.reports.generate.form', $tpl) }}"
                                                    class="btn btn-sm btn-primary flex-grow-1">
                                                    <span class="iconify me-1" data-icon="mdi:file-pdf-box"></span> Generate
                                                </a>
                                                <a href="{{ route('HR.reports.edit', $tpl) }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    <span class="iconify" data-icon="mdi:pencil"></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <img src="{{ asset('svgundraw/emptydata.svg') }}" alt="" width="12%">
                        <h5 class="mt-3"></h5>
                        <a href="{{ route('HR.reports.create') }}" class="btn btn-primary mt-3">Buat Template Baru</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
