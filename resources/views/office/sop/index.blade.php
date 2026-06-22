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
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-4 align-items-end">
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Kategori</label>
                    <select id="filterKategori" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        @foreach($perusahaan->pluck('kategori_perusahaan')->unique()->filter()->sort() as $kategori)
                            <option value="{{ $kategori }}">{{ $kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Status</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        @foreach($perusahaan->pluck('status')->unique()->filter()->sort() as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm mb-1">Sales Key</label>
                    <select id="filterSalesKey" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        @foreach($perusahaan->pluck('sales_key')->unique()->filter()->sort() as $salesKey)
                            <option value="{{ $salesKey }}">{{ $salesKey }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button id="btnReset" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                        <td>{{ $i + 1 }}</td>
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
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {

    var table = $('#tableSop').DataTable({
        pageLength: 20,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
        },
        columnDefs: [
            { orderable: false, targets: [0, 5] }
        ],
    });

    // ── Custom filter: dropdown (data-* attribute) ───────────────────────
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'tableSop') return true;

        var rowNode  = table.row(dataIndex).node();
        var kategori = $('#filterKategori').val();
        var status   = $('#filterStatus').val();
        var salesKey = $('#filterSalesKey').val();

        if (kategori && $(rowNode).data('kategori') != kategori) return false;
        if (status   && $(rowNode).data('status')   != status)   return false;
        if (salesKey && $(rowNode).data('saleskey') != salesKey) return false;

        return true;
    });

    // ── Search nama perusahaan — kolom index 1, real-time ────────────────
    $('#searchNama').on('input', function () {
        table.column(1).search($(this).val(), false, true).draw();
    });

    // ── Dropdown filter — real-time ──────────────────────────────────────
    $('#filterKategori, #filterStatus, #filterSalesKey').on('change', function () {
        table.draw();
    });

    // ── Reset ────────────────────────────────────────────────────────────
    $('#btnReset').on('click', function () {
        $('#searchNama').val('');
        $('#filterKategori, #filterStatus, #filterSalesKey').val('');
        table.column(1).search('').draw();
    });

});
</script>
@endsection