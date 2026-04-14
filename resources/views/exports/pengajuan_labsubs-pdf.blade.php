<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Form Pengajuan Lab / Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 14px; }
        .table-borderless td, .table-borderless th { border: none !important; }
        .signature { height: 80px; }
        .small-text { font-size: 13px; }
        .border-black { border: 1px solid #000; }
    </style>
</head>
<body>
<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <img src="{{ asset('css/logo.png') }}" width="100px">
            <h5 class="mt-1 mb-0">INIXINDO BANDUNG</h5>
            <small>Jl. Cipaganti No. 95 Bandung</small>
        </div>
        <div class="text-end">
            <button class="btn btn-success d-print-none" id="printBtn">
                <i class="fa fa-print"></i> Print Form
            </button>
        </div>
    </div>

    <h4 class="text-center mb-3">
        Form Pengajuan {{ $labSnapshot ? 'Laboratorium' : 'Subscription' }}
    </h4>

    {{-- ================== DATA UMUM ================== --}}
    <table class="table table-bordered mb-3">
        <tbody>
            <tr>
                <td width="30%">Tanggal Pengajuan</td>
                <td>{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Divisi</td>
                <td>{{ $data->karyawan->divisi ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama Karyawan</td>
                <td>{{ $data->karyawan->nama_lengkap ?? '-' }}</td>
            </tr>
            <tr>
                <td>Jenis Pengajuan</td>
                <td>{{ $labSnapshot ? 'Lab' : 'Subscription' }}</td>
            </tr>
            <tr>
                <td>Nama Lab / Subscription</td>
                <td>{{ $labSnapshot->nama_labs ?? $subsSnapshot->nama_subs ?? '-' }}</td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td>{{ $labSnapshot->desc ?? $subsSnapshot->desc ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ================== DETAIL LAB ================== --}}
    @if ($labSnapshot)
        <table class="table table-bordered mb-3">
            <thead class="table-light">
                <tr>
                    <th>Nama Lab</th>
                    <th>Harga Asli ({{ $labSnapshot->mata_uang ?? '-' }})</th>
                    @if (($labSnapshot->mata_uang ?? '') !== 'Rupiah')
                        <th>Kurs</th>
                    @endif
                    <th>Harga (Rupiah)</th>
                    <th>Aktif</th>
                    <th>Kedaluwarsa</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $labSnapshot->nama_labs ?? '-' }}</td>
                    <td>
                        {{ isset($labSnapshot->harga) ? number_format($labSnapshot->harga, 2, ',', '.') : '-' }}
                        {{ $labSnapshot->mata_uang ?? '' }}
                    </td>
                    @if (($labSnapshot->mata_uang ?? '') !== 'Rupiah')
                        <td>{{ isset($labSnapshot->kurs) ? 'Rp ' . number_format($labSnapshot->kurs, 2, ',', '.') : '-' }}</td>
                    @endif
                    <td>
                        {{ isset($labSnapshot->harga_rupiah) ? 'Rp ' . number_format($labSnapshot->harga_rupiah, 0, ',', '.') : '-' }}
                    </td>
                    <td>
                        {{ !empty($labSnapshot->start_date) ? \Carbon\Carbon::parse($labSnapshot->start_date)->translatedFormat('d F Y') : '-' }}
                    </td>
                    <td>
                        {{ !empty($labSnapshot->end_date) ? \Carbon\Carbon::parse($labSnapshot->end_date)->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- ================== DETAIL SUBSCRIPTION ================== --}}
    @if ($subsSnapshot)
        <table class="table table-bordered mb-3">
            <thead class="table-light">
                <tr>
                    <th>Nama Subscription</th>
                    <th>Harga Asli ({{ $subsSnapshot->mata_uang ?? '-' }})</th>
                    @if (($subsSnapshot->mata_uang ?? '') !== 'Rupiah')
                        <th>Kurs</th>
                    @endif
                    <th>Harga (Rupiah)</th>
                    <th>Aktif</th>
                    <th>Kedaluwarsa</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $subsSnapshot->nama_subs ?? '-' }}</td>
                    <td>
                        {{ isset($subsSnapshot->harga) ? number_format($subsSnapshot->harga, 2, ',', '.') : '-' }}
                        {{ $subsSnapshot->mata_uang ?? '' }}
                    </td>
                    @if (($subsSnapshot->mata_uang ?? '') !== 'Rupiah')
                        <td>{{ isset($subsSnapshot->kurs) ? 'Rp ' . number_format($subsSnapshot->kurs, 2, ',', '.') : '-' }}</td>
                    @endif
                    <td>
                        {{ isset($subsSnapshot->harga_rupiah) ? 'Rp ' . number_format($subsSnapshot->harga_rupiah, 0, ',', '.') : '-' }}
                    </td>
                    <td>
                        {{ !empty($subsSnapshot->start_date) ? \Carbon\Carbon::parse($subsSnapshot->start_date)->translatedFormat('d F Y') : '-' }}
                    </td>
                    <td>
                        {{ !empty($subsSnapshot->end_date) ? \Carbon\Carbon::parse($subsSnapshot->end_date)->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- ================== TANDA TANGAN ================== --}}
    <div class="row text-center mt-5">
        <div class="col-4">
            <p>Yang Mengajukan</p>
            @if ($data->karyawan->ttd)
                <img src="{{ asset('storage/ttd/' . $data->karyawan->ttd) }}" class="signature">
            @else
                <div class="signature"></div>
            @endif
            <p>{{ $data->karyawan->nama_lengkap }}</p>
        </div>
        <div class="col-4">
            <p>Menyetujui</p>
            @if ($finance && $finance->ttd)
                <img src="{{ asset('storage/ttd/' . $finance->ttd) }}" class="signature">
            @else
                <div class="signature"></div>
            @endif
            <p>{{ $finance->nama_lengkap ?? '-' }}</p>
        </div>
        <div class="col-4">
            <p>Mengetahui</p>
            @if ($gm && $gm->ttd)
                <img src="{{ asset('storage/ttd/' . $gm->ttd) }}" class="signature">
            @else
                <div class="signature"></div>
            @endif
            <p>{{ $gm->nama_lengkap ?? '-' }}</p>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/85b3409c34.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#printBtn').on('click', () => window.print());
</script>
</body>
</html>
