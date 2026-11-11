@php
    $semua = \App\Support\KPI\PanduanData::all();
    $p = $semua[$key] ?? null;
@endphp

@if ($p)
    <div class="p-1">
        <div class="mb-4 pb-2 border-bottom">
            <h5 class="fw-bold mb-0">{{ $p['judul'] }}</h5>
            <small class="text-muted">Panduan lengkap cara kerja target ini</small>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold text-primary mb-3">
                <i class="fa-solid fa-database me-2"></i>
                1. Dari Mana Datanya & Bagaimana Dihitung
            </h6>
            {!! $p['dari_mana_data'] !!}
        </div>

        <hr class="my-4">

        <div class="mb-4">
            <h6 class="fw-bold text-warning mb-3">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                2. Jika Muncul Error atau Data Kosong
            </h6>
            {!! $p['error_kosong'] !!}
        </div>

        <hr class="my-4">

        <div class="mb-2">
            <h6 class="fw-bold text-success mb-3">
                <i class="fa-solid fa-diagram-project me-2"></i>
                3. Alur Kerja (Step by Step)
            </h6>
            {!! $p['alur_bisnis'] !!}
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fa-solid fa-book-open fa-2x text-muted mb-3"></i>
        <p class="mb-0 text-muted">Panduan untuk target ini belum tersedia.</p>
    </div>
@endif