@extends('layouts_office.app')

@section('office_contents')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('sop.perusahaan.index') }}" class="text-muted text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Persyaratan Dokumen
            </a>
            <h2 class="mb-0 mt-1">{{ $perusahaan->nama_perusahaan }}</h2>
            <span class="badge bg-secondary">{{ $perusahaan->kategori_perusahaan ?? '-' }}</span>
            <span class="badge ms-1">{{ $perusahaan->status ?? '-' }}</span>
        </div>
    </div>

    <div class="row g-4">

        {{-- Kolom Kiri: Info Perusahaan --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    Info Perusahaan
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 40%">Nama</td>
                                <td>: <strong>{{ $perusahaan->nama_perusahaan ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kategori</td>
                                <td>: {{ $perusahaan->kategori_perusahaan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Sales Key</td>
                                <td>: {{ $perusahaan->sales_key ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>: <span class="badge">{{ $perusahaan->status ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Lokasi</td>
                                <td>: {{ $perusahaan->lokasi ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Alamat</td>
                                <td>: {{ $perusahaan->alamat ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">No. Telp</td>
                                <td>: {{ $perusahaan->no_telp ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td>: {{ $perusahaan->email ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">CP</td>
                                <td>: {{ $perusahaan->cp ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">NPWP</td>
                                <td>: {{ $perusahaan->npwp ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($perusahaan->foto_npwp)
                <div class="card-footer">
                    <p class="text-muted small mb-2">Foto NPWP</p>
                    <img src="{{ asset('storage/' . $perusahaan->foto_npwp) }}"
                        alt="Foto NPWP"
                        class="img-fluid rounded border"
                        style="max-height: 160px; object-fit: cover;">
                </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Daftar SOP (atas) + PIC Penagihan (bawah) --}}
        <div class="col-md-7 d-flex flex-column gap-4">

            {{-- Daftar SOP --}}
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>Daftar Persyaratan Dokumen</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark">{{ count($perusahaan->sop) }} Dokumen</span>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateSop">
                            Tambah Persyaratan
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($perusahaan->sop) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="ps-3">#</th>
                                        <th>Judul Persyaratan Dokumen</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($perusahaan->sop as $i => $sop)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                        <td>{{ $sop->judul }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-between align-items-center gap-2">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary btn-detail-sop"
                                                    title="Lihat Detail"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetailSop"
                                                    data-id="{{ $sop->id }}"
                                                    data-judul="{{ $sop->judul }}"
                                                    data-sop="{{ $sop->sop }}">
                                                    Detail
                                                </button>

                                                <form action="{{ route('sop.perusahaan.delete', $sop->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus SOP ini?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                            Belum ada Persyaratan untuk perusahaan ini.
                        </div>
                    @endif
                </div>
            </div>

            {{-- PIC Penagihan --}}
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>PIC Penagihan</span>
                    <span class="badge bg-light text-dark">{{ $perusahaan->picPenagihan->count() }} PIC</span>
                </div>
                <div class="card-body p-0">
                    @if(count($perusahaan->picPenagihan) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="ps-3">#</th>
                                        <th>PIC</th>
                                        <th>Kategori</th>
                                        <th>Telepon</th>
                                        <th>Alamat</th>
                                        <th width="10%" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($perusahaan->picPenagihan as $i => $pic)
                                    <tr>
                                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                        <td>{{ $pic['pic'] ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $pic['category'] ?? '-' }}</span>
                                        </td>
                                        <td>{{ $pic['telepon'] ?: '-' }}</td>
                                        <td>{{ $pic['alamat'] ?: '-' }}</td>
                                        <td class="text-center">
                                            @if(($pic['status'] ?? '0') == '1')
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            Belum ada PIC penagihan untuk perusahaan ini.
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>

{{-- Modal Create SOP --}}
<div class="modal fade" id="modalCreateSop" tabindex="-1" aria-labelledby="modalCreateSopLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalCreateSopLabel">
                    Tambah Persyaratan Dokumen — {{ $perusahaan->nama_perusahaan }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('sop.perusahaan.store') }}" method="POST" id="formCreateSop">
                @csrf
                <input type="hidden" name="id_perusahaan" value="{{ $perusahaan->id }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Judul Persyaratan Dokumen</label>
                        <input type="text" name="judul" id="judul" class="form-control" placeholder="Judul Persyaratan Dokumen" required>
                    </div>

                    <hr>

                    <p class="text-muted small mb-3">
                        Detail isi persyaratan dokumen. Klik <strong>+ Tambah Baris</strong> untuk menambah lebih dari 5.
                    </p>

                    <div id="sopInputList">
                        @for($n = 1; $n <= 5; $n++)
                        <div class="input-group mb-2 sop-row">
                            <span class="input-group-text text-muted" style="min-width: 42px;">{{ $n }}</span>
                            <input type="text"
                                   name="sop[]"
                                   class="form-control"
                                   placeholder="Dokumen {{ $n }}">
                            <button type="button" class="btn btn-outline-danger btn-remove-row" title="Hapus baris">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endfor
                    </div>

                    <button type="button" id="btnAddRow" class="btn btn-sm btn-outline-secondary mt-1">
                        <i class="fas fa-plus me-1"></i> Tambah Baris
                    </button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        Simpan SOP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Detail / Update SOP --}}
<div class="modal fade" id="modalDetailSop" tabindex="-1" aria-labelledby="modalDetailSopLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalDetailSopLabel">Detail Persyaratan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('sop.perusahaan.update', ['id' => ':id']) }}" method="POST" id="formDetailSop">
                @csrf
                @method('PUT')
                <input type="hidden" name="id_sop" id="detailIdSop">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Judul SOP</label>
                        <input type="text" name="judul" id="detailJudul" class="form-control" required>
                    </div>

                    <hr>

                    <p class="text-muted small mb-3">Detail isi persyaratan dokumen.</p>

                    <div id="detailSopList"></div>

                    <button type="button" id="btnAddRowDetail" class="btn btn-sm btn-outline-secondary mt-1">
                        <i class="fas fa-plus me-1"></i> Tambah Baris
                    </button>

                    <p id="detailEmpty" class="text-muted text-center py-3 mb-0 d-none">
                        Tidak ada detail persyaratan dokumen.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/85b3409c34.js" crossorigin="anonymous"></script>

<script>
$(document).ready(function () {

    // ── Tambah baris input ───────────────────────────────────────────────
    $('#btnAddRow').on('click', function () {
        var count = $('#sopInputList .sop-row').length + 1;
        var row = `
            <div class="input-group mb-2 sop-row">
                <span class="input-group-text text-muted" style="min-width: 42px;">${count}</span>
                <input type="text"
                       name="sop[]"
                       class="form-control"
                       placeholder="Dokumen ${count}">
                <button type="button" class="btn btn-outline-danger btn-remove-row" title="Hapus baris">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        $('#sopInputList').append(row);
        renumberRows();
    });

    // ── Hapus baris + renumber ───────────────────────────────────────────
    $(document).on('click', '.btn-remove-row', function () {
        if ($('#sopInputList .sop-row').length <= 1) return;
        $(this).closest('.sop-row').remove();
        renumberRows();
    });

    function renumberRows() {
        $('#sopInputList .sop-row').each(function (i) {
            $(this).find('.input-group-text').text(i + 1);
            $(this).find('input').attr('placeholder', 'Dokumen ' + (i + 1));
        });
    }

    // ── Reset modal create saat ditutup ──────────────────────────────────
    $('#modalCreateSop').on('hidden.bs.modal', function () {
        $('#judul').val('');
        var $list = $('#sopInputList');
        $list.empty();
        for (var n = 1; n <= 5; n++) {
            $list.append(`
                <div class="input-group mb-2 sop-row">
                    <span class="input-group-text text-muted" style="min-width: 42px;">${n}</span>
                    <input type="text"
                           name="sop[]"
                           class="form-control"
                           placeholder="Dokumen ${n}">
                    <button type="button" class="btn btn-outline-danger btn-remove-row" title="Hapus baris">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>`);
        }
    });

// ── Isi modal detail saat tombol "Detail" diklik ─────────────────────────
$(document).on('click', '.btn-detail-sop', function () {
    var id    = $(this).data('id');
    var judul = $(this).data('judul');
    var rawSop = $(this).data('sop'); // jQuery auto-parse kalau valid JSON

    $('#detailIdSop').val(id);
    $('#detailJudul').val(judul);
    $('#formDetailSop').attr('action', '{{ route("sop.perusahaan.update", ["id" => ":id"]) }}'.replace(':id', id));

    var list = [];
    if (Array.isArray(rawSop)) {
        list = rawSop;
    } else if (typeof rawSop === 'string') {
        try { list = JSON.parse(rawSop); } catch (e) { list = []; }
    }

    var $wrap  = $('#detailSopList').empty();
    var $empty = $('#detailEmpty');

    if (list.length > 0) {
        $empty.addClass('d-none');
        $wrap.removeClass('d-none');
        list.forEach(function (item, i) {
            $wrap.append(buildDetailRow(i + 1, item));
        });
    } else {
        $wrap.addClass('d-none');
        $empty.removeClass('d-none');
    }
});

function buildDetailRow(number, value) {
    var safeValue = $('<div>').text(value || '').html();
    return `
        <div class="input-group mb-2 sop-row-detail">
            <span class="input-group-text text-muted" style="min-width: 42px;">${number}</span>
            <input type="text" name="sop[]" class="form-control" value="${safeValue}" placeholder="Dokumen ${number}">
            <button type="button" class="btn btn-outline-danger btn-remove-row-detail" title="Hapus baris">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
}

// ── Tambah baris di modal detail ─────────────────────────────────────────
$(document).on('click', '#btnAddRowDetail', function () {
    var count = $('#detailSopList .sop-row-detail').length + 1;
    $('#detailEmpty').addClass('d-none');
    $('#detailSopList').removeClass('d-none').append(buildDetailRow(count, ''));
});

// ── Hapus baris + renumber di modal detail ───────────────────────────────
$(document).on('click', '.btn-remove-row-detail', function () {
    $(this).closest('.sop-row-detail').remove();
    $('#detailSopList .sop-row-detail').each(function (i) {
        $(this).find('.input-group-text').text(i + 1);
        $(this).find('input').attr('placeholder', 'Dokumen ' + (i + 1));
    });
    if ($('#detailSopList .sop-row-detail').length === 0) {
        $('#detailEmpty').removeClass('d-none');
        $('#detailSopList').addClass('d-none');
    }
});

});
</script>
@endsection