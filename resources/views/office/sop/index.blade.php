@extends('layouts_office.app')

@section('office_contents')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Persyaratan Dokumen</h2>
            <p class="text-muted mb-0">Daftar dokumen berdasarkan perusahaan</p>
        </div>
    </div>

    {{-- Filter Row --}}
    <form method="GET" action="{{ route('sop.perusahaan.index') }}" class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-4 align-items-end">
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Kategori</label>
                    <select id="filterKategori" name="kategori" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        @foreach($kategoriOptions as $kategori)
                            <option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Status</label>
                    <select id="filterStatus" name="status" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Sales Key</label>
                    <select id="filterSalesKey" name="sales_key" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        @foreach($salesKeyOptions as $salesKey)
                            <option value="{{ $salesKey }}" @selected(request('sales_key') === $salesKey)>{{ $salesKey }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Terapkan Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('sop.perusahaan.index') }}" id="btnReset" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <table id="tableSop" class="table table-bordered table-hover align-middle w-100">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">#</th>
                        <th>Nama Perusahaan</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Sales Key</th>
                        <th width="8%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perusahaan as $i => $p)
                    <tr
                        data-kategori="{{ $p->kategori_perusahaan }}"
                        data-status="{{ $p->status }}"
                        data-saleskey="{{ $p->sales_key }}"
                    >
                        <td>{{ $perusahaan->firstItem() + $i }}</td>
                        <td>{{ $p->nama_perusahaan }}</td>
                        <td><span class="badge bg-secondary">{{ $p->kategori_perusahaan ?? '-' }}</span></td>
                        <td><span class="badge">{{ $p->status ?? '-' }}</span></td>
                        <td>{{ $p->sales_key ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('sop.perusahaan.detail', $p->id) }}"
                               class="btn btn-sm btn-primary"
                               title="Lihat Detail">Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{ $perusahaan->links() }}
            </div>
        </div>
    </div>
</div>



<script>
$(document).ready(function () {
});
</script>
@endsection