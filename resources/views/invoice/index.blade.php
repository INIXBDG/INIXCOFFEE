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
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoicedRkms as $rkm)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $rkm->invoice->invoice_number ?? '-' }}</td>
                                            <td>{{ $rkm->materi->nama_materi ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d
                                                {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                            <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $peserta = $rkm->registrasi->pluck('peserta.nama')->toArray();
                                                @endphp
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('download.pdf', ['id' => $rkm->invoice->id, 'peserta[]' => $peserta]) }}"
                                                        class="btn btn-primary">
                                                        Pdf
                                                    </a>
                                                    <a href="{{ route('invoice.create', $rkm->id) }}"
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
                    <label class="form-label small mb-1">Bank <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" data-bank="sel" required>
                        ${bankOptionsHtml(o.bank_sel)}
                    </select>

                    ${o.bank_sel === 'new'
                        ? `<label class="form-label small mt-2 mb-1">Nama Bank <span class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" data-bank="name" placeholder="Nama bank" value="${esc(o.bank_name)}" required>
                        <label class="form-label small mt-2 mb-1">Nomor Rekening <span class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" data-bank="acc" placeholder="Nomor rekening" value="${esc(o.account_number)}" required>`
                        : isDefault
                            ? `<label class="form-label small mt-2 mb-1">Nomor Rekening <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm" data-bank="acc" placeholder="Nomor rekening" value="${esc(o.account_number)}" required>
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
                        `<label class="form-label small">Nomor Invoice <span class="text-danger">*</span></label>
                         <input class="form-control form-control-sm" data-k="invoice_number" value="${esc(r.invoice_number)}" required>`,
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
                        return `<div class="mb-3"><label class="form-label fw-semibold">Bank untuk semua invoice <span class="text-danger">*</span></label>${bankControl(gBank, 'global')}</div>`;
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
        });
    </script>

</body>

</html>