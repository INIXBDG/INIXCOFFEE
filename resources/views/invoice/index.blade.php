<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Invoice & Kwitansi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        @@keyframes slideNext {
            from {
                opacity: 0;
                transform: translateX(40px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @@keyframes slidePrev {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .anim-next {
            animation: slideNext .25s ease;
        }

        .anim-prev {
            animation: slidePrev .25s ease;
        }

        .rows-scroll {
            max-height: 40vh;
            overflow-y: auto;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #dee2e6;
            display: inline-block;
            margin: 0 3px;
        }

        .dot.active {
            background: #0d6efd;
        }

        #pesertaModal .modal-dialog {
            max-width: 95vw;
            width: 95vw;
        }
        @media (min-width: 1400px) {
            #pesertaModal .modal-dialog {
                max-width: 1300px;
            }
        }

        .num-stat {
            background: #fff;
            border: 1px solid #dee2e6;
            border-left: 4px solid #0d6efd;
            border-radius: 8px;
            padding: 14px 16px;
            height: 100%;
        }

        .num-stat-ok {
            border-left-color: #198754;
        }

        .num-stat-danger {
            background: #fff5f5;
            border-left-color: #dc3545;
        }

        .num-stat-label {
            color: #6c757d;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .num-stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .num-stat-small {
            font-size: 0.95rem;
            padding: 0.35rem 0;
            word-break: break-all;
        }

        .num-stat-sub {
            color: #6c757d;
            font-size: 0.78rem;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="no-print" style="position: absolute; top: 20px; right: 20px;">
            <a href="{{ route('home') }}" class="btn btn-secondary">Kembali</a>
        </div>

        <ul class="nav nav-tabs" id="invoiceKwitansiTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="invoice-tab" data-bs-toggle="tab"
                    data-bs-target="#invoiceTab" type="button" role="tab">
                    Invoice
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="kwitansi-tab" data-bs-toggle="tab" data-bs-target="#kwitansiTab"
                    type="button" role="tab">
                    Kwitansi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="duplikat-tab" data-bs-toggle="tab" data-bs-target="#duplikatTab"
                    type="button" role="tab">
                    Duplikat
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="nomor-tab" data-bs-toggle="tab" data-bs-target="#nomorTab"
                    type="button" role="tab">
                    Nomor Invoice
                    @if ($dupGroups->count())
                        <span class="badge bg-danger ms-1">{{ $dupGroups->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="invoiceKwitansiTabContent">

            @php
                $romawi = [
                    1 => 'I',
                    2 => 'II',
                    3 => 'III',
                    4 => 'IV',
                    5 => 'V',
                    6 => 'VI',
                    7 => 'VII',
                    8 => 'VIII',
                    9 => 'IX',
                    10 => 'X',
                    11 => 'XI',
                    12 => 'XII',
                ];
                $noBase = '/INXBDG-INV/' . $romawi[(int) date('m')] . '/' . date('Y');
            @endphp

            <div class="tab-pane fade show active" id="invoiceTab" role="tabpanel">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header text-center bg-light">
                        <h5 class="mb-0 fw-bold">Buat Invoice</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="notInvoicedTable" class="table table-striped table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th>No.</th>
                                        <th>Nama Materi</th>
                                        <th>Tanggal Periode</th>
                                        <th>Nama Perusahaan</th>
                                        <th>Pax</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($notInvoicedRkms as $rkm)
                                        <tr data-id="{{ $rkm->id }}"
                                            data-materi="{{ $rkm->materi->nama_materi ?? '-' }}"
                                            data-perusahaan="{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}"
                                            data-number="{{ $rkm->id . $noBase }}">
                                            <td><input type="checkbox" class="form-check-input row-check" disabled></td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $rkm->materi->nama_materi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d
                                                {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                            <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td>{{ $rkm->pax ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('invoice.create', $rkm->id) }}"
                                                    class="btn btn-primary btn-sm">Buat Invoice</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header text-center bg-light">
                        <h5 class="mb-0 fw-bold">Lihat Invoice</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="invoicedTable" class="table table-striped table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nomor Invoice</th>
                                        <th>Nama Materi</th>
                                        <th>Tanggal Periode</th>
                                        <th>Nama Perusahaan</th>
                                        <th>Peserta</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoicedRkms as $rkm)
                                        @php
                                            $peserta = $rkm->registrasi->pluck('peserta.nama')->filter()->values()->toArray();
                                            $invoiceId = $rkm->invoice->id ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $rkm->invoice->invoice_number ?? '-' }}</td>
                                            <td>{{ $rkm->materi->nama_materi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d
                                                {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                            <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td data-order="{{ count($peserta) }}">
                                                @if (count($peserta))
                                                    <span class="badge bg-success" data-bs-toggle="tooltip"
                                                        title="{{ implode(', ', $peserta) }}">
                                                        {{ count($peserta) }} peserta
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary" data-bs-toggle="tooltip"
                                                        title="Belum ada peserta terdaftar, invoice per peserta tidak tersedia">
                                                        Belum ada
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $peserta = $rkm->registrasi->pluck('peserta.nama')->filter()->values()->toArray();
                                                    $invoiceId = $rkm->invoice->id ?? null;
                                                @endphp
                                                <div class="dropdown">
                                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        Aksi
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('download.pdf', ['id' => $invoiceId, 'peserta' => $peserta]) }}">
                                                                PDF
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('download.pdf', ['id' => $invoiceId, 'peserta' => $peserta, 'preview' => 1]) }}"
                                                                target="_blank" rel="noopener">
                                                                Preview
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item btn-invoice-peserta"
                                                                data-invoice-id="{{ $invoiceId }}"
                                                                data-number="{{ $rkm->invoice->invoice_number ?? '' }}"
                                                                data-unit-price="{{ $rkm->invoice->unit_price ?: ($rkm->harga_jual ?? 0) }}"
                                                                data-pph="{{ ($rkm->invoice->pph ?? 0) > 0 ? 1 : 0 }}"
                                                                data-po="{{ $rkm->invoice->purchase_order ?? '' }}"
                                                                data-bank="{{ $rkm->invoice->bank_name ?? '' }}"
                                                                data-account="{{ $rkm->invoice->account_number ?? '' }}"
                                                                data-peserta="{{ json_encode($peserta) }}"
                                                                {{ count($peserta) ? '' : 'disabled' }}>
                                                                Invoice per Peserta
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('invoice.create', $rkm->id) }}">
                                                                Edit
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('invoice.destroy', $invoiceId) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Yakin ingin menghapus invoice {{ $rkm->invoice->invoice_number ?? '' }}? Data terkait (kwitansi/approval) juga perlu dicek.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="kwitansiTab" role="tabpanel">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header text-center bg-light">
                        <h5 class="mb-0 fw-bold">Buat Kwitansi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="notReceiptedTable" class="table table-striped table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Materi</th>
                                        <th>Tanggal Periode</th>
                                        <th>Nama Perusahaan</th>
                                        <th>Pax</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($notReceiptedRkms as $rkm)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $rkm->materi->nama_materi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d
                                                {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                            <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td>{{ $rkm->pax ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('kwitansi.create', ['invoiceId' => $rkm->invoice->id ?? '-']) }}"
                                                    class="btn btn-primary btn-sm">Buat Kwitansi</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header text-center bg-light">
                        <h5 class="mb-0 fw-bold">Lihat Kwitansi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="receiptedTable" class="table table-striped table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nomor Kwitansi</th>
                                        <th>Nama Materi</th>
                                        <th>Tanggal Periode</th>
                                        <th>Nama Perusahaan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($receiptedRkms as $rkm)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $rkm->invoice->invoice_number ?? '-' }}</td>
                                            <td>{{ $rkm->materi->nama_materi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d
                                                {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                            <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('kwitansi.pdf', $rkm->kwitansi->first()->id) }}"
                                                        class="btn btn-success btn-sm">Lihat Kwitansi</a>
                                                    <a href="{{ route('kwitansi.create', ['invoiceId' => $rkm->invoice->id ?? '-']) }}"
                                                        class="btn btn-primary btn-sm">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="duplikatTab" role="tabpanel">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header text-center bg-light">
                        <h5 class="mb-0 fw-bold">Data Duplikat</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Data RKM di bawah ini terdeteksi identik (materi, perusahaan, harga, tanggal, sales)
                            dengan RKM lain yang sudah terpakai, sehingga tidak muncul di tab "Belum di-Invoice".
                        </p>
                        <div class="table-responsive">
                            <table id="duplicateTable" class="table table-striped table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Materi</th>
                                        <th>Tanggal Periode</th>
                                        <th>Nama Perusahaan</th>
                                        <th>Pax</th>
                                        <th>Sales</th>
                                        <th>ID RKM</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($duplicateRkms as $rkm)
                                        @php
                                            $tglAwal = \Carbon\Carbon::parse($rkm->tanggal_awal);
                                            $tanggal = $tglAwal->format('j');
                                            $lanbu = $tglAwal->format('n');
                                            $hunta = $tglAwal->format('Y');

                                            $kelas =
                                                $rkm->metode_kelas == 'Offline'
                                                    ? 'off'
                                                    : ($rkm->metode_kelas == 'Inhouse Bandung'
                                                        ? 'inhb'
                                                        : ($rkm->metode_kelas == 'Inhouse Luar Bandung'
                                                            ? 'inhlb'
                                                            : ($rkm->metode_kelas == 'Exam Only'
                                                                ? 'exam'
                                                                : 'vir')));

                                            $rkmDetailUrl =
                                                '/rkm/' .
                                                $rkm->materi_key .
                                                'ixb' .
                                                $tanggal .
                                                'ie' .
                                                $hunta .
                                                'ie' .
                                                $lanbu .
                                                'ixb' .
                                                $kelas;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $rkm->materi->nama_materi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d
                                                {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                            <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td>{{ $rkm->pax ?? '-' }}</td>
                                            <td>{{ $rkm->sales->kode_karyawan ?? '-' }}</td>
                                            <td>{{ $rkm->id }}</td>
                                            <td>
                                                <a href="{{ $rkmDetailUrl }}" class="btn btn-info btn-sm"
                                                    data-toggle="tooltip" data-placement="top" title="Detail RKM">
                                                    Detail RKM
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="tab-pane fade" id="nomorTab" role="tabpanel">
            @php
                $last = $invoiceStats['last'];
                $fmtRp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
                $fmtTgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-';
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="num-stat">
                        <div class="num-stat-label">Total Entry Invoice</div>
                        <div class="num-stat-value">{{ number_format($invoiceStats['total']) }}</div>
                        <div class="num-stat-sub">{{ $invoiceStats['this_month'] }} dibuat bulan ini</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="num-stat">
                        <div class="num-stat-label">Nomor Terakhir</div>
                        <div class="num-stat-value num-stat-small">{{ $last->invoice_number ?? '-' }}</div>
                        <div class="num-stat-sub">
                            {{ $last ? $fmtTgl($last->tanggal_invoice) . ' · ' . ($last->rkm?->perusahaan?->nama_perusahaan ?? '-') : 'Belum ada invoice' }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="num-stat {{ $invoiceStats['dup_groups'] ? 'num-stat-danger' : 'num-stat-ok' }}">
                        <div class="num-stat-label">Nomor Ganda</div>
                        <div class="num-stat-value">{{ $invoiceStats['dup_groups'] }}</div>
                        <div class="num-stat-sub">
                            {{ $invoiceStats['dup_groups'] ? $invoiceStats['dup_entries'] . ' entry terdampak' : 'Semua nomor unik' }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="num-stat">
                        <div class="num-stat-label">ID Invoice Terakhir</div>
                        <div class="num-stat-value">{{ $last->id ?? '-' }}</div>
                        <div class="num-stat-sub">Dibuat {{ $last?->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Panel duplikat --}}
            @if ($dupGroups->count())
                <div class="card mb-4 shadow-sm border-danger">
                    <div class="card-header bg-danger-subtle text-danger-emphasis fw-bold">
                        ⚠ {{ $dupGroups->count() }} nomor invoice ganda ditemukan
                    </div>
                    <div class="card-body">
                        @foreach ($dupGroups as $grp)
                            <div class="border rounded mb-3">
                                <div class="p-2 px-3 bg-light d-flex justify-content-between flex-wrap gap-2">
                                    <div>
                                        <span class="fw-bold">{{ $grp['number'] }}</span>
                                        <span class="badge bg-danger ms-2">{{ $grp['items']->count() }} entry</span>
                                    </div>
                                    <div class="small text-muted">{{ $grp['hint'] }}</div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 align-middle" style="font-size:.82rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID Inv</th>
                                                <th>ID RKM</th>
                                                <th>Kelas</th>
                                                <th>Materi</th>
                                                <th>Perusahaan</th>
                                                <th>Periode</th>
                                                <th>Harga/Pax</th>
                                                <th>Sales</th>
                                                <th>Tgl Invoice</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($grp['items'] as $d)
                                                @php $r = $d->rkm; @endphp
                                                <tr>
                                                    <td>{{ $d->id }}</td>
                                                    <td>{{ $d->id_rkm }}</td>
                                                    <td><span class="badge bg-info text-dark">{{ $r->metode_kelas ?? '-' }}</span></td>
                                                    <td>{{ $r?->materi?->nama_materi ?? '-' }}</td>
                                                    <td>{{ $r?->perusahaan?->nama_perusahaan ?? '-' }}</td>
                                                    <td class="text-nowrap">{{ $fmtTgl($r?->tanggal_awal) }} s/d {{ $fmtTgl($r?->tanggal_akhir) }}</td>
                                                    <td class="text-nowrap">{{ $fmtRp($d->unit_price) }}</td>
                                                    <td>{{ $r?->sales?->kode_karyawan ?? '-' }}</td>
                                                    <td class="text-nowrap">{{ $fmtTgl($d->tanggal_invoice) }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-warning btn-sm btn-edit-number"
                                                            data-id="{{ $d->id }}" data-number="{{ $d->invoice_number }}">Edit</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="alert alert-success">✔ Tidak ada nomor invoice ganda. Semua nomor unik.</div>
            @endif

            {{-- Semua invoice --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header text-center bg-light">
                    <h5 class="mb-0 fw-bold">Semua Nomor Invoice</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3" style="max-width:220px;">
                        <label class="form-label mb-1 fw-semibold" style="font-size:.85rem;">Tampilkan</label>
                        <select class="form-select form-select-sm" id="numFilter">
                            <option value="">Semua invoice</option>
                            <option value="dup">Hanya yang duplikat</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table id="invoiceNumberTable" class="table table-striped table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nomor Invoice</th>
                                    <th>Status</th>
                                    <th>Tgl Invoice</th>
                                    <th>Materi / Periode</th>
                                    <th>Kelas</th>
                                    <th>Perusahaan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allInvoices as $inv)
                                    @php
                                        $r = $inv->rkm;
                                        $isDup = in_array($inv->id, $dupIds);
                                    @endphp
                                    <tr class="{{ $isDup ? 'table-danger' : '' }}">
                                        <td>{{ $inv->id }}</td>
                                        <td class="fw-semibold text-nowrap">{{ $inv->invoice_number }}</td>
                                        <td>
                                            @if ($isDup)
                                                <span class="badge bg-danger">Duplikat</span>
                                            @else
                                                <span class="badge bg-success">OK</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">{{ $fmtTgl($inv->tanggal_invoice) }}</td>
                                        <td>
                                            {{ $r?->materi?->nama_materi ?? '-' }}
                                            <div class="small text-muted">{{ $fmtTgl($r?->tanggal_awal) }} s/d {{ $fmtTgl($r?->tanggal_akhir) }}</div>
                                        </td>
                                        <td>{{ $r->metode_kelas ?? '-' }}</td>
                                        <td>{{ $r?->perusahaan?->nama_perusahaan ?? '-' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm btn-edit-number"
                                                data-id="{{ $inv->id }}" data-number="{{ $inv->invoice_number }}">Edit</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalTitle"></h5>
                        <div class="small text-muted" id="modalStepInfo"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="btnCloseX"></button>
                </div>
                <div class="modal-body" style="overflow:hidden;" id="modalBody"></div>
                <div class="modal-footer justify-content-between">
                    <button class="btn btn-outline-secondary" id="btnPrev">Kembali</button>
                    <div id="dots"></div>
                    <button class="btn btn-primary" id="btnNext">Lanjut</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editNumberModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Nomor Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="small text-muted mb-1">Nomor saat ini</div>
                    <div class="fw-bold mb-3" id="enOld"></div>
                    <label class="form-label fw-semibold">Nomor baru</label>
                    <input type="text" class="form-control" id="enInput" maxlength="255">
                    <div class="text-danger small mt-2" id="enError"></div>
                    <div class="alert alert-warning small mt-3 mb-0">
                        Nomor di Approval Pendapatan dan Outstanding ikut diperbarui. PDF invoice lama dihapus dan
                        dibuat ulang saat diunduh. PDF kwitansi yang sudah tersimpan tidak berubah.
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="enSave">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pesertaModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="pmTitle"></h5>
                        <div class="small text-muted" id="pmStepInfo"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="pmBody" style="max-height:70vh;overflow-y:auto;"></div>
                <div class="modal-footer justify-content-between">
                    <button class="btn btn-outline-secondary" id="pmPrev">Kembali</button>
                    <div id="pmDots"></div>
                    <button class="btn btn-primary" id="pmNext">Lanjut</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {

            function extractMonthYear(periodeStr) {
                const months = {
                    'Januari': '01',
                    'Februari': '02',
                    'Maret': '03',
                    'April': '04',
                    'Mei': '05',
                    'Juni': '06',
                    'Juli': '07',
                    'Agustus': '08',
                    'September': '09',
                    'Oktober': '10',
                    'November': '11',
                    'Desember': '12',
                    'January': '01',
                    'February': '02',
                    'March': '03',
                    'May': '05',
                    'June': '06',
                    'July': '07',
                    'August': '08',
                    'October': '10'
                };

                const regex = /(\d{1,2})\s+(\w+)\s+(\d{4})/g;
                const results = [];
                let match;

                while ((match = regex.exec(periodeStr)) !== null) {
                    const monthNum = months[match[2]];
                    if (monthNum) {
                        results.push({
                            month: monthNum,
                            year: match[3]
                        });
                    }
                }

                return results;
            }

            function initTableWithFilter(tableId, periodeColIndex) {
                const $table = $('#' + tableId);
                const wrapperId = tableId + '_wrapper';
                const years = new Set();

                $table.find('tbody tr').each(function() {
                    const periodeText = $(this).find('td').eq(periodeColIndex).text().trim();
                    extractMonthYear(periodeText).forEach(({
                        year
                    }) => {
                        if (year) years.add(year);
                    });
                });

                const dt = $table.DataTable();

                const currentYear = new Date().getFullYear();

                let yearOptions = '<option value="">-- Semua Tahun --</option>';

                for (let y = currentYear - 5; y <= currentYear + 1; y++) {
                    yearOptions += `<option value="${y}">${y}</option>`;
                }

                const monthList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                    'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];
                let monthOptions = '<option value="">-- Semua Bulan --</option>';
                monthList.forEach((label, i) => {
                    monthOptions += `<option value="${String(i + 1).padStart(2, '0')}">${label}</option>`;
                });

                $('#' + wrapperId).before(`
                    <div class="d-flex gap-2 mb-3 flex-wrap" id="filter_${tableId}">
                        <div>
                            <label class="form-label mb-1 fw-semibold" style="font-size:0.85rem;">Filter Tahun</label>
                            <select class="form-select form-select-sm" id="yearFilter_${tableId}" style="min-width:130px;">
                                ${yearOptions}
                            </select>
                        </div>
                        <div>
                            <label class="form-label mb-1 fw-semibold" style="font-size:0.85rem;">Filter Bulan</label>
                            <select class="form-select form-select-sm" id="monthFilter_${tableId}" style="min-width:130px;">
                                ${monthOptions}
                            </select>
                        </div>
                    </div>
                `);

                $.fn.dataTable.ext.search.push(function(settings, data) {
                    if (settings.nTable.id !== tableId) return true;

                    const selectedYear = $('#yearFilter_' + tableId).val();
                    const selectedMonth = $('#monthFilter_' + tableId).val();

                    if (!selectedYear && !selectedMonth) return true;

                    return extractMonthYear(data[periodeColIndex]).some(({
                        month,
                        year
                    }) => {
                        if (selectedYear && year !== selectedYear) return false;
                        if (selectedMonth && month !== selectedMonth) return false;
                        return true;
                    });
                });

                $('#yearFilter_' + tableId + ', #monthFilter_' + tableId).on('change', function() {
                    dt.draw();
                });
            }

            $('#notInvoicedTable').DataTable({
                columnDefs: [{
                    orderable: false,
                    targets: 0
                }],
                order: []
            });

            initTableWithFilter('notInvoicedTable', 3);
            initTableWithFilter('invoicedTable', 3);
            initTableWithFilter('notReceiptedTable', 2);
            initTableWithFilter('receiptedTable', 3);
            initTableWithFilter('duplicateTable', 2);

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

            $('#invoicedTable').on('draw.dt', function () {
                this.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => bootstrap.Tooltip.getOrCreateInstance(el));
            });

            const STORE_URL = @json(route('invoice.bulk.store'));
            const CSRF = $('meta[name=csrf-token]').attr('content');
            const dtB = $('#notInvoicedTable').DataTable();
            const yearSelB = $('#yearFilter_notInvoicedTable');
            const monthSelB = $('#monthFilter_notInvoicedTable');
            const selected = new Map();

            $('#notInvoicedTable_wrapper').before(`
                <div class="d-flex align-items-center gap-3 mb-3 flex-wrap" id="bulkToolbar">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="bulkCheckAll" disabled>
                        <label class="form-check-label fw-semibold" for="bulkCheckAll">Pilih semua</label>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="btnBulkCreate" disabled>Buat Invoice (0)</button>
                    <small class="text-muted" id="bulkHint">Pilih tahun terlebih dahulu untuk mengaktifkan pilihan.</small>
                </div>`);

            const rowInfo = tr => ({
                id_rkm: tr.dataset.id,
                materi: tr.dataset.materi,
                perusahaan: tr.dataset.perusahaan,
                invoice_number: tr.dataset.number,
                pph23: false,
                purchase_order: '',
                bank_sel: '',
                bank_name: '',
                account_number: '',
                is_peserta: false,
                is_ttd: false,
            });
            const appliedNodes = () => dtB.rows({
                search: 'applied'
            }).nodes().toArray();

            function syncUI() {
                const yearOn = !!yearSelB.val();
                monthSelB.prop('disabled', !yearOn);
                if (!yearOn) selected.clear();

                const applied = appliedNodes();
                const ids = new Set(applied.map(tr => tr.dataset.id));
                [...selected.keys()].forEach(id => {
                    if (!ids.has(id)) selected.delete(id);
                });

                dtB.rows().nodes().to$().find('.row-check').each(function() {
                    this.disabled = !yearOn;
                    this.checked = selected.has(this.closest('tr').dataset.id);
                });

                const n = selected.size;
                $('#bulkCheckAll').prop('disabled', !yearOn)
                    .prop('checked', applied.length > 0 && n === applied.length)
                    .prop('indeterminate', n > 0 && n < applied.length);
                $('#btnBulkCreate').prop('disabled', n === 0).text(`Buat Invoice (${n})`);
                $('#bulkHint').toggle(!yearOn);
            }

            yearSelB.on('change', function() {
                if (!this.value && monthSelB.val()) {
                    monthSelB.val('');
                    dtB.draw();
                }
            });
            dtB.on('draw', syncUI);
            $('#notInvoicedTable').on('change', '.row-check', function() {
                const tr = this.closest('tr');
                if (this.checked) selected.set(tr.dataset.id, rowInfo(tr));
                else selected.delete(tr.dataset.id);
                syncUI();
            });
            $('#bulkCheckAll').on('change', function() {
                selected.clear();
                if (this.checked) appliedNodes().forEach(tr => selected.set(tr.dataset.id, rowInfo(tr)));
                syncUI();
            });
            syncUI();

            let items = [];
            let cur = 0;
            let fixMode = {};
            let dir = 'next';
            let finished = false;
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            const body = document.getElementById('modalBody');
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));

            const BANK_KEY = 'bulk_invoice_banks';

            function loadBanks() {
                try {
                    return JSON.parse(localStorage.getItem(BANK_KEY) || '[]');
                } catch (e) {
                    return [];
                }
            }

            function persistBanks() {
                try {
                    localStorage.setItem(BANK_KEY, JSON.stringify(savedBanks));
                } catch (e) {}
            }
            let savedBanks = loadBanks();
            let gBank = {
                bank_sel: '',
                bank_name: '',
                account_number: ''
            };

            function addSavedBank(name, acc) {
                name = (name || '').trim();
                acc = (acc || '').trim();
                if (!name || !acc) return false;
                const exists = savedBanks.some(b => b.bank_name.toLowerCase() === name.toLowerCase() && b
                    .account_number === acc);
                if (!exists) {
                    savedBanks.push({
                        bank_name: name,
                        account_number: acc
                    });
                    persistBanks();
                }
                return !exists;
            }

            const DEFAULT_BANKS = [
                'BANK MANDIRI KK BANDUNG CIHAMPELAS',
                'BANK BCA KK BANDUNG ABDUL RIVAI',
                'BANK BJB KCP CIHAMPELAS BANDUNG'
            ];

            const bankOptionsHtml = sel =>
                '<option value="">-- Pilih bank yang sudah ada --</option>' +
                savedBanks.map((b, i) =>
                    `<option value="saved:${i}" ${String(`saved:${i}`) === String(sel) ? 'selected' : ''}>${esc(b.bank_name)} — ${esc(b.account_number)}</option>`
                ).join('') +
                DEFAULT_BANKS.map((name, i) =>
                    `<option value="default:${i}" ${String(`default:${i}`) === String(sel) ? 'selected' : ''}>${esc(name)}</option>`
                ).join('') +
                `<option value="new" ${sel === 'new' ? 'selected' : ''}>➕ Isi bank baru...</option>`;

            function bankControl(o, scope) {
                const isDefault = String(o.bank_sel || '').startsWith('default:');

                return `<div data-scope="${scope}">
                    <select class="form-select form-select-sm" data-bank="sel">
                        ${bankOptionsHtml(o.bank_sel)}
                    </select>

                    ${o.bank_sel === 'new'
                        ? `<input class="form-control form-control-sm mt-2" data-bank="name" placeholder="Nama bank" value="${esc(o.bank_name)}">
                        <input class="form-control form-control-sm mt-2" data-bank="acc" placeholder="Nomor rekening" value="${esc(o.account_number)}">`
                        : isDefault
                            ? `<input class="form-control form-control-sm mt-2" data-bank="acc" placeholder="Nomor rekening" value="${esc(o.account_number)}">
                            <div class="small text-muted mt-1">Bank default dipilih. Silakan isi nomor rekening secara manual.</div>`
                            : o.bank_sel !== ''
                                ? `<div class="small text-muted mt-1">Rek: ${esc(o.account_number)}</div>`
                                : ''
                    }
                </div>`;
            }

            const bankTarget = el => {
                const sc = el.closest('[data-scope]').dataset.scope;
                return sc === 'global' ? gBank : items.find(r => r.id_rkm == sc);
            };
            const applyGlobalBank = () => items.forEach(r => {
                r.bank_sel = gBank.bank_sel;
                r.bank_name = gBank.bank_name;
                r.account_number = gBank.account_number;
            });

            function refreshBankSelects() {
                body.querySelectorAll('select[data-bank=sel]').forEach(sel => {
                    sel.innerHTML = bankOptionsHtml(bankTarget(sel).bank_sel);
                });
            }

            const STEPS = [{
                    title: 'Nomor Invoice',
                    hint: 'Periksa nomor invoice. Format otomatis: {id RKM}/INXBDG-INV/{bulan}/{tahun}.',
                    okText: 'Semua nomor sudah benar',
                    global: () => '',
                    row: r =>
                        `<input class="form-control form-control-sm" data-k="invoice_number" value="${esc(r.invoice_number)}">`,
                    validate: () => {
                        if (items.some(r => !r.invoice_number.trim())) return 'Ada nomor invoice yang kosong.';
                        const nums = items.map(r => r.invoice_number.trim());
                        if (new Set(nums).size !== nums.length) return 'Ada nomor invoice yang kembar.';
                    },
                },
                {
                    title: 'PPh 23 & Purchase Order',
                    hint: 'Default: tidak memakai PPh 23 dan tanpa nomor PO. Pilih "Ada yang perlu diperbaiki" bila ada yang berbeda.',
                    okText: 'Semua tanpa PPh 23 & PO sudah benar',
                    global: () => '',
                    row: r => `<div class="form-check mb-1"><input type="checkbox" class="form-check-input" data-k="pph23" ${r.pph23 ? 'checked' : ''}>
                               <label class="form-check-label small">Pakai PPh 23 (2%)</label></div>
                               <input class="form-control form-control-sm" data-k="purchase_order" placeholder="Nomor PO (opsional)" value="${esc(r.purchase_order)}">`,
                },
                {
                    title: 'Bank & Nomor Rekening',
                    hint: 'Isi nama bank dan nomor rekening secara manual, atau pilih yang sudah pernah diisi. Yang baru diisi otomatis menjadi pilihan untuk invoice lain.',
                    okText: 'Bank & rekening sudah benar untuk semua',
                    global: () => {
                        const f = items[0];
                        const same = items.every(r => r.bank_sel === f.bank_sel && r.bank_name === f
                            .bank_name && r.account_number === f.account_number);
                        gBank = same ? {
                            bank_sel: f.bank_sel,
                            bank_name: f.bank_name,
                            account_number: f.account_number
                        } : {
                            bank_sel: '',
                            bank_name: '',
                            account_number: ''
                        };
                        return `<div class="mb-3"><label class="form-label fw-semibold">Bank untuk semua invoice</label>${bankControl(gBank, 'global')}</div>`;
                    },
                    row: r => bankControl(r, r.id_rkm),
                    validate: () => {
                        if (items.some(r => !r.bank_name.trim() || !r.account_number.trim()))
                            return 'Semua invoice harus punya nama bank dan nomor rekening.';
                        items.forEach(r => addSavedBank(r.bank_name, r.account_number));
                    },
                },
                {
                    title: 'Daftar Peserta & Tanda Tangan (PDF)',
                    hint: 'Default: keduanya tidak disertakan.',
                    okText: 'Pengaturan PDF sudah benar',
                    global: () => `<div class="form-check form-switch mb-1"><input class="form-check-input" type="checkbox" data-global="is_peserta" ${items.every(r => r.is_peserta) ? 'checked' : ''}><label class="form-check-label">Sertakan Daftar Peserta (semua)</label></div>
                                   <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" data-global="is_ttd" ${items.every(r => r.is_ttd) ? 'checked' : ''}><label class="form-check-label">Sertakan Tanda Tangan (semua)</label></div>`,
                    row: r => `<div class="form-check form-switch"><input class="form-check-input" type="checkbox" data-k="is_peserta" ${r.is_peserta ? 'checked' : ''}><label class="form-check-label small">Peserta</label></div>
                               <div class="form-check form-switch"><input class="form-check-input" type="checkbox" data-k="is_ttd" ${r.is_ttd ? 'checked' : ''}><label class="form-check-label small">Tanda Tangan</label></div>`,
                },
            ];

            function renderStep() {
                const s = STEPS[cur],
                    fix = !!fixMode[cur];
                document.getElementById('modalTitle').textContent = s.title;
                document.getElementById('modalStepInfo').textContent =
                    `Langkah ${cur + 1} dari ${STEPS.length} • ${items.length} invoice`;
                document.getElementById('dots').innerHTML = STEPS.map((_, i) =>
                    `<span class="dot ${i === cur ? 'active' : ''}"></span>`).join('');
                document.getElementById('btnPrev').style.visibility = cur === 0 ? 'hidden' : 'visible';
                const last = cur === STEPS.length - 1;
                const btnNext = document.getElementById('btnNext');
                btnNext.textContent = last ? `Simpan & Buat ${items.length} Invoice` : 'Lanjut';
                btnNext.className = 'btn ' + (last ? 'btn-success' : 'btn-primary');

                body.innerHTML = `<div class="${dir === 'next' ? 'anim-next' : 'anim-prev'}">
                    <p class="text-muted">${s.hint}</p>
                    ${s.global()}
                    <div class="btn-group w-100 mb-3">
                        <button type="button" class="btn ${fix ? 'btn-outline-success' : 'btn-success'}" data-mode="ok">✔ ${s.okText}</button>
                        <button type="button" class="btn ${fix ? 'btn-warning' : 'btn-outline-warning'}" data-mode="fix">✎ Ada yang perlu diperbaiki</button>
                    </div>
                    ${fix ? `<div class="rows-scroll border rounded"><table class="table table-sm mb-0 align-middle">
                        <thead class="table-light"><tr><th>Materi / Perusahaan</th><th style="width:48%">Ubah</th></tr></thead><tbody>
                        ${items.map(r => `<tr data-rid="${r.id_rkm}"><td><b>${esc(r.materi)}</b><div class="small text-muted">${esc(r.perusahaan)}</div></td><td>${s.row(r)}</td></tr>`).join('')}
                        </tbody></table></div>` : ''}
                    <div class="text-danger small mt-2" id="stepError"></div>
                </div>`;
            }

            body.addEventListener('click', e => {
                const b = e.target.closest('[data-mode]');
                if (!b) return;
                fixMode[cur] = b.dataset.mode === 'fix';
                dir = 'next';
                renderStep();
            });

            body.addEventListener('input', e => {
                const k = e.target.dataset.k,
                    tr = e.target.closest('tr[data-rid]');
                if (!k || !tr) return;
                const r = items.find(x => x.id_rkm == tr.dataset.rid);
                r[k] = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
            });

            body.addEventListener('change', e => {
                const k = e.target.dataset.global;
                if (!k) return;
                const v = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
                items.forEach(r => r[k] = v);
                if (fixMode[cur]) renderStep();
            });

            body.addEventListener('change', e => {
                const el = e.target.closest('[data-bank]');
                if (!el) return;
                const o = bankTarget(el),
                    isG = o === gBank,
                    type = el.dataset.bank;
                    if (type === 'sel') {
                        o.bank_sel = el.value;

                        if (el.value === '') {
                            o.bank_name = '';
                            o.account_number = '';
                        } else if (el.value === 'new') {
                            o.bank_name = '';
                            o.account_number = '';
                        } else if (el.value.startsWith('saved:')) {
                            const index = Number(el.value.replace('saved:', ''));
                            const b = savedBanks[index];

                            if (b) {
                                o.bank_name = b.bank_name;
                                o.account_number = b.account_number;
                            }
                        } else if (el.value.startsWith('default:')) {
                            const index = Number(el.value.replace('default:', ''));
                            o.bank_name = DEFAULT_BANKS[index] || '';
                            o.account_number = '';
                        }

                        if (isG) {
                            applyGlobalBank();
                        }

                        const y = body.querySelector('.rows-scroll')?.scrollTop;

                        renderStep();

                        if (y) {
                            body.querySelector('.rows-scroll').scrollTop = y;
                        }
                    } else if (addSavedBank(o.bank_name, o.account_number)) {
                        refreshBankSelects();
                    }
            });

            body.addEventListener('input', e => {
                const el = e.target.closest('[data-bank]');
                if (!el || el.dataset.bank === 'sel') return;
                const o = bankTarget(el);
                if (el.dataset.bank === 'name') o.bank_name = el.value;
                else o.account_number = el.value;
                if (o === gBank) applyGlobalBank();
            });

            function go(delta) {
                dir = delta > 0 ? 'next' : 'prev';
                cur += delta;
                renderStep();
            }

            document.getElementById('btnPrev').addEventListener('click', () => go(-1));
            document.getElementById('btnNext').addEventListener('click', async () => {
                if (finished) {
                    location.reload();
                    return;
                }
                const msg = STEPS[cur].validate?.();
                if (msg) {
                    document.getElementById('stepError').textContent = msg;
                    return;
                }
                if (cur < STEPS.length - 1) return go(1);
                await save();
            });

            $('#btnBulkCreate').on('click', () => {
                items = [...selected.values()].map(r => ({
                    ...r
                }));
                cur = 0;
                fixMode = {};
                dir = 'next';
                finished = false;
                gBank = {
                    bank_sel: '',
                    bank_name: '',
                    account_number: ''
                };
                document.getElementById('btnPrev').style.display = '';
                document.getElementById('dots').style.display = '';
                renderStep();
                modal.show();
            });

            async function save() {
                const btn = document.getElementById('btnNext');
                btn.disabled = true;
                btn.textContent = 'Menyimpan...';
                try {
                    const res = await fetch(STORE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: JSON.stringify({
                            items: items.map(({
                                materi,
                                perusahaan,
                                bank_sel,
                                ...r
                            }) => r)
                        }),
                    });
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message || 'Gagal menyimpan.');
                    showResults(json.results);
                } catch (err) {
                    document.getElementById('stepError').textContent = err.message;
                    btn.textContent = `Simpan & Buat ${items.length} Invoice`;
                } finally {
                    btn.disabled = false;
                }
            }

            function showResults(results) {
                finished = true;
                const ok = results.filter(r => r.ok).length;
                document.getElementById('modalTitle').textContent = 'Hasil';
                document.getElementById('modalStepInfo').textContent =
                    `${ok} berhasil, ${results.length - ok} gagal`;
                document.getElementById('btnPrev').style.display = 'none';
                document.getElementById('dots').style.display = 'none';
                const btnNext = document.getElementById('btnNext');
                btnNext.textContent = 'Selesai';
                btnNext.className = 'btn btn-primary';
                body.innerHTML = `<div class="rows-scroll"><ul class="list-group">` + results.map(r => `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${esc(r.invoice_number)}${r.ok ? '' : `<div class="small text-danger">${esc(r.message)}</div>`}</span>
                        ${r.ok ? `<a class="btn btn-sm btn-primary" target="_blank" href="${esc(r.pdf_url)}">PDF</a>` : '<span class="badge bg-danger">Gagal</span>'}
                    </li>`).join('') + `</ul></div>`;
            }

            document.getElementById('confirmModal').addEventListener('hidden.bs.modal', () => {
                if (finished) location.reload();
            });

            (function() {
                const PM_URL_TPL = @json(route('invoice.peserta.pdf', ['invoice' => '__ID__']));
                const PM_KW_URL_TPL = @json(route('invoice.peserta.kwitansi', ['invoice' => '__ID__']));
                const pmModalEl = document.getElementById('pesertaModal');
                const pmModal = new bootstrap.Modal(pmModalEl);
                const pmBody = document.getElementById('pmBody');
                const PM_STEPS = ['Pengelompokan Peserta', 'Detail Invoice', 'Preview & Download'];
                const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
                const safe = s => String(s).replace(/[\/\\:*?"<>|\s]+/g, '-');

                let pm = {};

                function pmReset(btn) {
                    pm = {
                        inv: {
                            id: btn.dataset.invoiceId,
                            number: btn.dataset.number,
                            price: parseFloat(btn.dataset.unitPrice) || 0,
                            pph: btn.dataset.pph === '1',
                            po: btn.dataset.po || '',
                            bank: btn.dataset.bank || '',
                            account: btn.dataset.account || '',
                        },
                        peserta: JSON.parse(btn.dataset.peserta || '[]'),
                        assign: [],
                        groups: [],
                        sig: '',
                        step: 0,
                        dir: 'next',
                        loading: false,
                        error: '',
                        urls: [],
                        tab: 0,
                        kw: true,     
                        extra: [],
                        cache: new Map(),
                        runId: 0,
                        status: [],
                        errors: [],
                    };
                    pm.assign = pm.peserta.map((_, i) => i + 1); // default: terpisah semua
                }

                function pmRevoke() {
                    (pm.cache || new Map()).forEach(u => URL.revokeObjectURL(u));
                    pm.cache = new Map();
                    pm.urls = [];
                }

                const pmKey = g => JSON.stringify([g.invoice_number, g.names, g.pph23, g.purchase_order,
                    g.bank_name, g.account_number, g.is_peserta, g.is_ttd]);

                function pmUpdateNext(next, last) {
                    const done = pm.status.filter(s => s === 'ready').length;
                    const all = pm.status.length > 0 && done === pm.status.length;
                    if (pm.step === 2) {
                        next.disabled = pm.loading;
                        next.textContent = all ? `Setuju & Download (${pm.groups.length} file)`
                            : `Menyiapkan ${done}/${pm.groups.length}...`;
                    } else {
                        next.disabled = false;
                        next.textContent = 'Lanjut';
                    }
                }

                function pmPaintPane() {
                    const pane = document.getElementById('pmPane');
                    if (!pane) return;
                    const g = pm.groups[pm.tab], st = pm.status[pm.tab];
                    const info = `<div class="small text-muted mb-2">${esc(g.invoice_number)} • ${g.names.length} pax • ${rupiah(pmCalc(g))}</div>`;
                    if (st === 'ready') {
                        pane.innerHTML = info + `<iframe src="${pm.urls[pm.tab]}" style="width:100%;height:55vh;border:1px solid #dee2e6;border-radius:6px;"></iframe>`;
                    } else if (st === 'error') {
                        pane.innerHTML = info + `<div class="alert alert-danger">${esc(pm.errors[pm.tab] || 'Gagal membuat preview.')} Klik "Kembali" lalu "Lanjut" untuk mencoba lagi.</div>`;
                    } else {
                        pane.innerHTML = info + `<div class="text-center py-5"><div class="spinner-border"></div><div class="mt-2 text-muted">Membuat preview...</div></div>`;
                    }
                }

                function pmAfterUpdate(i) {
                    const spin = document.querySelector(`[data-pm-tab="${i}"] .pm-spin`);
                    if (spin && pm.status[i] !== 'loading' && pm.status[i] !== 'pending') spin.hidden = true;
                    if (i === pm.tab) pmPaintPane();
                    pmUpdateNext(document.getElementById('pmNext'));
                }

                function pmBuildGroups() {
                    const map = new Map();
                    pm.assign.forEach((g, i) => {
                        if (!map.has(g)) map.set(g, []);
                        map.get(g).push(i);
                    });
                    const keys = [...map.keys()].sort((a, b) => a - b);
                    const sig = JSON.stringify(keys.map(k => map.get(k)));
                    if (sig === pm.sig) return;
                    pm.sig = sig;
                    const multi = keys.length > 1;
                    pm.groups = keys.map((k, n) => ({
                        names: map.get(k).map(i => pm.peserta[i]),
                        invoice_number: multi ? `${pm.inv.number}-${n + 1}` : pm.inv.number,
                        pph23: pm.inv.pph,
                        purchase_order: pm.inv.po,
                        bank_name: pm.inv.bank,
                        account_number: pm.inv.account,
                        is_peserta: true,
                        is_ttd: false,
                    }));
                }

                const pmCalc = g => {
                    const sub = pm.inv.price * g.names.length;
                    const ppn = Math.round(sub * 0.11);
                    const pph = g.pph23 ? Math.round(sub * 0.02) : 0;
                    return sub + ppn - pph;
                };

                function pmGroupCount() {
                    return new Set(pm.assign).size;
                }

                function pmRender() {
                    document.getElementById('pmTitle').textContent = PM_STEPS[pm.step];
                    document.getElementById('pmStepInfo').textContent =
                        `Langkah ${pm.step + 1} dari 3 • Invoice ${pm.inv.number}`;
                    document.getElementById('pmDots').innerHTML = PM_STEPS.map((_, i) =>
                        `<span class="dot ${i === pm.step ? 'active' : ''}"></span>`).join('');
                    document.getElementById('pmPrev').style.visibility = pm.step === 0 ? 'hidden' : 'visible';

                    const next = document.getElementById('pmNext');
                    const last = pm.step === 2;
                    next.className = 'btn ' + (last ? 'btn-success' : 'btn-primary');
                    pmUpdateNext(next, last);

                    const anim = pm.dir === 'next' ? 'anim-next' : 'anim-prev';
                    let html = '';

                    if (pm.step === 0) {
                        const n = pm.peserta.length;
                        const opts = sel => Array.from({
                            length: n
                        }, (_, k) =>
                            `<option value="${k + 1}" ${sel === k + 1 ? 'selected' : ''}>Invoice ${k + 1}</option>`).join('');
                        html = `<p class="text-muted">Peserta dengan <b>nomor invoice yang sama</b> akan digabung dalam satu invoice. Default-nya setiap peserta mendapat invoice sendiri.</p>
                            <div class="btn-group mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-pm-quick="split">Pisahkan semua</button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-pm-quick="merge">Gabungkan semua</button>
                            </div>
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light"><tr><th style="width:50px">No.</th><th>Nama Peserta</th><th style="width:180px">Masuk ke</th></tr></thead>
                                <tbody>${pm.peserta.map((p, i) => `<tr>
                                    <td>${i + 1}</td><td>${esc(p)}</td>
                                    <td><select class="form-select form-select-sm" data-pm-assign="${i}">${opts(pm.assign[i])}</select></td></tr>`).join('')}
                                </tbody>
                            </table>
                            <div class="fw-semibold" id="pmSummary">Akan dibuat ${pmGroupCount()} invoice.</div>`;
                    } else if (pm.step === 1) {
                        html = pm.groups.map((g, gi) => `
                            <div class="border rounded p-3 mb-3" data-gi="${gi}">
                                <div class="fw-bold">Invoice ${gi + 1}: ${esc(g.names.join(' & '))}</div>
                                <div class="small text-muted mb-2">${g.names.length} pax × ${rupiah(pm.inv.price)} → Total
                                    <b class="pm-total" data-gi="${gi}">${rupiah(pmCalc(g))}</b> (termasuk PPN 11%)</div>
                                <div class="row g-2">
                                    <div class="col-md-6"><label class="form-label small mb-0">Nomor Invoice</label>
                                        <input class="form-control form-control-sm" data-k="invoice_number" value="${esc(g.invoice_number)}"></div>
                                    <div class="col-md-6"><label class="form-label small mb-0">Nomor PO (opsional)</label>
                                        <input class="form-control form-control-sm" data-k="purchase_order" value="${esc(g.purchase_order)}"></div>
                                    <div class="col-md-6"><label class="form-label small mb-0">Bank</label>
                                        <input class="form-control form-control-sm" data-k="bank_name" value="${esc(g.bank_name)}"></div>
                                    <div class="col-md-6"><label class="form-label small mb-0">Nomor Rekening</label>
                                        <input class="form-control form-control-sm" data-k="account_number" value="${esc(g.account_number)}"></div>
                                    <div class="col-12 d-flex gap-4 flex-wrap mt-2">
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" data-k="pph23" ${g.pph23 ? 'checked' : ''}><label class="form-check-label small">PPh 23 (2%)</label></div>
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" data-k="is_peserta" ${g.is_peserta ? 'checked' : ''}><label class="form-check-label small">Sertakan Daftar Peserta</label></div>
                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" data-k="is_ttd" ${g.is_ttd ? 'checked' : ''}><label class="form-check-label small">Sertakan Tanda Tangan</label></div>
                                    </div>
                                </div>
                            </div>`).join('');
                    } else {
                        if (pm.loading) {
                            html = `<div class="text-center py-5"><div class="spinner-border"></div><div class="mt-2 text-muted">Membuat preview...</div></div>`;
                        } else {
                            html = `<ul class="nav nav-pills mb-3 flex-nowrap" style="overflow-x:auto;">
                                ${pm.groups.map((g, i) => `<li class="nav-item"><button type="button" class="nav-link ${i === pm.tab ? 'active' : ''} text-nowrap"
                                    data-pm-tab="${i}" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;">${esc(g.names.join(' & '))}
                                    <span class="spinner-border spinner-border-sm ms-1 pm-spin" ${pm.status[i] === 'ready' || pm.status[i] === 'error' ? 'hidden' : ''}></span></button></li>`).join('')}
                                </ul><div id="pmPane"></div>
                                <div class="form-check mt-3 p-3 border rounded bg-light">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" id="pmKwitansi" ${pm.kw ? 'checked' : ''}>
                                    <label class="form-check-label fw-semibold" for="pmKwitansi">
                                        Sekalian buat kwitansi untuk setiap invoice di atas (${pm.groups.length} file)
                                    </label>
                                </div>`;
                        }
                    }
                    pmBody.innerHTML = `<div class="${pm.step === 2 ? '' : anim}">${html}<div class="text-danger small mt-2" id="pmError">${esc(pm.error)}</div></div>`;
                    if (pm.step === 2) pmPaintPane();
                }

                function pmLoadPreviews() {
                    const run = ++pm.runId;
                    const n = pm.groups.length;
                    const url = PM_URL_TPL.replace('__ID__', pm.inv.id);
                    pm.status = Array(n).fill('pending');
                    pm.urls = Array(n).fill(null);
                    pm.errors = [];
                    pm.tab = 0;

                    // pakai ulang hasil sebelumnya jika data kelompok tidak berubah
                    pm.groups.forEach((g, i) => {
                        const key = pmKey(g);
                        if (pm.cache.has(key)) {
                            pm.urls[i] = pm.cache.get(key);
                            pm.status[i] = 'ready';
                        }
                    });
                    pmRender();

                    const pick = () => pm.status[pm.tab] === 'pending' ? pm.tab : pm.status.indexOf('pending');

                    const worker = async () => {
                        let i;
                        while (run === pm.runId && (i = pick()) !== -1) {
                            pm.status[i] = 'loading';
                            const g = pm.groups[i];
                            try {
                                const res = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json, application/pdf',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': CSRF
                                    },
                                    body: JSON.stringify({
                                        invoice_number: g.invoice_number,
                                        peserta: g.names,
                                        pph23: g.pph23,
                                        purchase_order: g.purchase_order,
                                        bank_name: g.bank_name,
                                        account_number: g.account_number,
                                        is_peserta: g.is_peserta,
                                        is_ttd: g.is_ttd,
                                    }),
                                });
                                if (!res.ok) {
                                    let m = 'Gagal membuat preview.';
                                    try { m = (await res.json()).message || m; } catch (e) {}
                                    throw new Error(m);
                                }
                                const u = URL.createObjectURL(await res.blob());
                                pm.cache.set(pmKey(g), u);
                                if (run !== pm.runId) return;
                                pm.urls[i] = u;
                                pm.status[i] = 'ready';
                            } catch (err) {
                                if (run !== pm.runId) return;
                                pm.status[i] = 'error';
                                pm.errors[i] = err.message;
                            }
                            pmAfterUpdate(i);
                        }
                    };

                    // 2 permintaan paralel: cukup cepat tanpa membebani server
                    Array.from({ length: Math.min(2, n) }, worker);
                }

                function pmValidateStep2() {
                    const nums = pm.groups.map(g => g.invoice_number.trim());
                    if (nums.some(n => !n)) return 'Ada nomor invoice yang kosong.';
                    if (new Set(nums).size !== nums.length) return 'Ada nomor invoice yang kembar.';
                    return '';
                }

                async function pmDownloadAll() {
                    const next = document.getElementById('pmNext');
                    const files = pm.groups.map((g, i) => {
                        const nm = g.names.length === 1 ? '_' + safe(g.names[0]) : '_gabungan' + (i + 1);
                        return { url: pm.urls[i], name: (safe(g.invoice_number) + nm).slice(0, 120) + '.pdf' };
                    });

                    if (pm.kw) {
                        next.disabled = true;
                        next.textContent = 'Membuat kwitansi...';
                        try {
                            const url = PM_KW_URL_TPL.replace('__ID__', pm.inv.id);
                            for (const g of pm.groups) {
                                const res = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json, application/pdf',
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': CSRF
                                    },
                                    body: JSON.stringify({
                                        invoice_number: g.invoice_number,
                                        peserta: g.names,
                                        pph23: g.pph23,
                                        purchase_order: g.purchase_order,
                                        bank_name: g.bank_name,
                                        account_number: g.account_number,
                                        is_peserta: g.is_peserta,
                                        is_ttd: g.is_ttd,
                                    }),
                                });
                                if (!res.ok) {
                                    let m = 'Gagal membuat kwitansi.';
                                    try { m = (await res.json()).message || m; } catch (e) {}
                                    throw new Error(m);
                                }
                                const u = URL.createObjectURL(await res.blob());
                                pm.extra.push(u);
                                files.push({ url: u, name: 'KW-' + safe(g.invoice_number).slice(0, 110) + '.pdf' });
                            }
                        } catch (err) {
                            pm.error = err.message;
                            pmRender();
                            return;
                        }
                    }

                    files.forEach((f, i) => {
                        setTimeout(() => {
                            const a = document.createElement('a');
                            a.href = f.url;
                            a.download = f.name;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                        }, i * 400);
                    });
                    setTimeout(() => pmModal.hide(), files.length * 400 + 300);
                }

                // --- events ---
                $('#invoicedTable').on('click', '.btn-invoice-peserta', function() {
                    pmReset(this);
                    pmRender();
                    pmModal.show();
                });

                pmBody.addEventListener('click', e => {
                    const q = e.target.closest('[data-pm-quick]');
                    if (q) {
                        pm.assign = pm.peserta.map((_, i) => q.dataset.pmQuick === 'split' ? i + 1 : 1);
                        pm.dir = 'next';
                        return pmRender();
                    }
                    const t = e.target.closest('[data-pm-tab]');
                    if (t) {
                        pm.tab = Number(t.dataset.pmTab);
                        pm.dir = 'tab';
                        pmRender();
                    }
                });

                pmBody.addEventListener('change', e => {
                    if (e.target.id === 'pmKwitansi') { pm.kw = e.target.checked; return; }
                    const s = e.target.closest('[data-pm-assign]');
                    if (s) {
                        pm.assign[Number(s.dataset.pmAssign)] = Number(s.value);
                        document.getElementById('pmSummary').textContent = `Akan dibuat ${pmGroupCount()} invoice.`;
                    }
                });

                pmBody.addEventListener('input', e => {
                    const k = e.target.dataset.k;
                    const card = e.target.closest('[data-gi]');
                    if (!k || !card) return;
                    const g = pm.groups[Number(card.dataset.gi)];
                    g[k] = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
                    pmBody.querySelectorAll('.pm-total').forEach(el => {
                        el.textContent = rupiah(pmCalc(pm.groups[Number(el.dataset.gi)]));
                    });
                });

                document.getElementById('pmPrev').addEventListener('click', () => {
                    if (pm.step === 2) pmRevoke();
                    pm.step--;
                    pm.dir = 'prev';
                    pm.error = '';
                    pmRender();
                });

                document.getElementById('pmNext').addEventListener('click', () => {
                    pm.error = '';
                    if (pm.step === 0) {
                        pmBuildGroups();
                        pm.step = 1;
                        pm.dir = 'next';
                        return pmRender();
                    }
                    if (pm.step === 1) {
                        const msg = pmValidateStep2();
                        if (msg) {
                            document.getElementById('pmError').textContent = msg;
                            return;
                        }
                        pm.step = 2;
                        pm.dir = 'next';
                        return pmLoadPreviews();
                    }
                    if (pm.step === 2 && !pm.loading && pm.urls.length) pmDownloadAll();
                });

                pmModalEl.addEventListener('hidden.bs.modal', () => {
                    pm.runId++;
                    const old = pm.cache;
                    pm.cache = new Map();
                    setTimeout(() => old.forEach(u => URL.revokeObjectURL(u)), 1500);

                    const ex = pm.extra || [];
                    pm.extra = [];
                    setTimeout(() => ex.forEach(u => URL.revokeObjectURL(u)), 1500);
                });
            })();

            // ================= TAB NOMOR INVOICE =================
            (function() {
                const UPDATE_TPL = @json(route('invoice.number.update', ['invoice' => '__ID__']));
                const editModal = new bootstrap.Modal(document.getElementById('editNumberModal'));
                const input = document.getElementById('enInput');
                const errEl = document.getElementById('enError');
                const saveBtn = document.getElementById('enSave');
                let curId = null;

                const invTable = $('#invoiceNumberTable').DataTable({
                    order: [[0, 'desc']],
                    pageLength: 10,
                    columnDefs: [{ orderable: false, targets: -1 }]
                });

                // filter "hanya duplikat" (kolom Status = indeks 2)
                $.fn.dataTable.ext.search.push(function(settings, data) {
                    if (settings.nTable.id !== 'invoiceNumberTable') return true;
                    return $('#numFilter').val() === 'dup' ? data[2].indexOf('Duplikat') !== -1 : true;
                });
                $('#numFilter').on('change', () => invTable.draw());

                // lebar kolom DataTables rusak jika tabel dibuat saat tab tersembunyi
                document.querySelectorAll('#invoiceKwitansiTab [data-bs-toggle="tab"]').forEach(b =>
                    b.addEventListener('shown.bs.tab', () =>
                        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust()));

                // buka lagi tab ini setelah reload
                try {
                    const t = sessionStorage.getItem('invoiceActiveTab');
                    if (t) {
                        sessionStorage.removeItem('invoiceActiveTab');
                        const b = document.querySelector(`[data-bs-target="${t}"]`);
                        if (b) bootstrap.Tab.getOrCreateInstance(b).show();
                    }
                } catch (e) {}

                $('#nomorTab').on('click', '.btn-edit-number', function() {
                    curId = this.dataset.id;
                    document.getElementById('enOld').textContent = this.dataset.number;
                    input.value = this.dataset.number;
                    errEl.textContent = '';
                    editModal.show();
                    setTimeout(() => { input.focus(); input.select(); }, 300);
                });

                input.addEventListener('keydown', e => { if (e.key === 'Enter') saveBtn.click(); });

                saveBtn.addEventListener('click', async () => {
                    const val = input.value.trim();
                    if (!val) { errEl.textContent = 'Nomor invoice tidak boleh kosong.'; return; }

                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Menyimpan...';
                    errEl.textContent = '';
                    try {
                        const res = await fetch(UPDATE_TPL.replace('__ID__', curId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': CSRF
                            },
                            body: JSON.stringify({ invoice_number: val })
                        });
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(json.errors?.invoice_number?.[0] || json.message || 'Gagal menyimpan.');
                        try { sessionStorage.setItem('invoiceActiveTab', '#nomorTab'); } catch (e) {}
                        location.reload();
                    } catch (err) {
                        errEl.textContent = err.message;
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Simpan';
                    }
                });
            })();
        });
    </script>

</body>

</html>