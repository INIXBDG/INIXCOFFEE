
<meta name="csrf-token" content="{{ csrf_token() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Invoice & Kwitansi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
</head>

<body>
    
    <div class="container mt-5">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="no-print" style="position: absolute; top: 20px; right: 20px;">
            <a href="{{ route('home') }}" class="btn btn-secondary">Kembali</a>
        </div>

        <!-- 🔹 Tab Navigation -->
        <ul class="nav nav-tabs" id="invoiceKwitansiTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="invoice-tab" data-bs-toggle="tab"
                    data-bs-target="#invoiceTab" type="button" role="tab">
                    Invoice
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="kwitansi-tab" data-bs-toggle="tab"
                    data-bs-target="#kwitansiTab" type="button" role="tab">
                    Kwitansi
                </button>
            </li>
        </ul>

        <!-- 🔹 Tab Content -->
        <div class="tab-content mt-3" id="invoiceKwitansiTabContent">

            <!-- =================== INVOICE =================== -->
            <div class="tab-pane fade show active" id="invoiceTab" role="tabpanel">
                <!-- Card 1 Buat Invoice -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header text-center bg-light">
                        <h5 class="mb-0 fw-bold">Buat Invoice</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="notInvoicedTable" class="table table-striped table-bordered mb-0">
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
                                    @foreach ($notInvoicedRkms as $rkm)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $rkm->materi->nama_materi ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                        <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                        <td>{{ $rkm->pax ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('invoice.create', $rkm->id) }}" class="btn btn-primary btn-sm">Buat Invoice</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 2 Lihat Invoice -->
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
                                        <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                        <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                        <td>
                                            @php
                                                $peserta = $rkm->registrasi
                                                    ->pluck('peserta.nama')
                                                    ->toArray();
                                            @endphp
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('download.pdf', ['id' => $rkm->invoice->id, 'peserta[]' => $peserta]) }}" class="btn btn-primary">
                                                    Pdf
                                                </a>
                                                 <a href="{{ route('invoice.create', $rkm->id) }}" class="btn btn-primary btn-sm">Edit</a>
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

            <!-- =================== KWITANSI =================== -->
            <div class="tab-pane fade" id="kwitansiTab" role="tabpanel">
                <!-- Card 3 Buat Kwitansi -->
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
                                        <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                        <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                        <td>{{ $rkm->pax ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('kwitansi.create', ['invoiceId' => $rkm->invoice->id ?? '-']) }}" class="btn btn-primary btn-sm">Buat Kwitansi</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 4 Lihat Kwitansi -->
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
                                        <td>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</td>
                                        <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a href="{{ route('kwitansi.pdf', $rkm->kwitansi->first()->id) }}" class="btn btn-success btn-sm">Lihat Kwitansi</a>
                                                <a href="{{ route('kwitansi.create', ['invoiceId' => $rkm->invoice->id ?? '-']) }}" class="btn btn-primary btn-sm">Edit</a>
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

        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function () {

        function extractMonthYear(periodeStr) {
            const months = {
                // Indonesia
                'Januari': '01', 'Februari': '02', 'Maret': '03', 'April': '04',
                'Mei': '05', 'Juni': '06', 'Juli': '07', 'Agustus': '08',
                'September': '09', 'Oktober': '10', 'November': '11', 'Desember': '12',
                // English
                'January': '01', 'February': '02', 'March': '03',
                'May': '05', 'June': '06', 'July': '07', 'August': '08',
                'October': '10'
                // April, September, November, December sama di dua bahasa
            };

            const regex = /(\d{1,2})\s+(\w+)\s+(\d{4})/g;
            const results = [];
            let match;

            while ((match = regex.exec(periodeStr)) !== null) {
                const monthNum = months[match[2]];
                if (monthNum) {
                    results.push({ month: monthNum, year: match[3] });
                }
            }

            return results; // array
        }

        function initTableWithFilter(tableId, periodeColIndex) {
            const $table = $('#' + tableId);
            const wrapperId = tableId + '_wrapper';

            const years = new Set();

            // ✅ Pakai array hasil fix
            $table.find('tbody tr').each(function () {
                const periodeText = $(this).find('td').eq(periodeColIndex).text().trim();
                const extracted = extractMonthYear(periodeText);
                extracted.forEach(({ year }) => { if (year) years.add(year); });
            });

            const dt = $table.DataTable();

            const sortedYears = [...years].sort();
            let yearOptions = '<option value="">-- Semua Tahun --</option>';
            sortedYears.forEach(y => yearOptions += `<option value="${y}">${y}</option>`);

            const monthList = [
                { val: '01', label: 'Januari' },
                { val: '02', label: 'Februari' },
                { val: '03', label: 'Maret' },
                { val: '04', label: 'April' },
                { val: '05', label: 'Mei' },
                { val: '06', label: 'Juni' },
                { val: '07', label: 'Juli' },
                { val: '08', label: 'Agustus' },
                { val: '09', label: 'September' },
                { val: '10', label: 'Oktober' },
                { val: '11', label: 'November' },
                { val: '12', label: 'Desember' },
            ];

            let monthOptions = '<option value="">-- Semua Bulan --</option>';
            monthList.forEach(({ val, label }) => {
                monthOptions += `<option value="${val}">${label}</option>`;
            });

            const filterHtml = `
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
            `;

            $('#' + wrapperId).before(filterHtml);

            // ✅ Pakai .some() bukan destructuring langsung
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== tableId) return true;

                const selectedYear  = $('#yearFilter_'  + tableId).val();
                const selectedMonth = $('#monthFilter_' + tableId).val();

                if (!selectedYear && !selectedMonth) return true;

                const periodeText = data[periodeColIndex];
                const extracted = extractMonthYear(periodeText);

                return extracted.some(({ month, year }) => {
                    if (selectedYear  && year  !== selectedYear)  return false;
                    if (selectedMonth && month !== selectedMonth) return false;
                    return true;
                });
            });

            $('#yearFilter_' + tableId + ', #monthFilter_' + tableId).on('change', function () {
                dt.draw();
            });
        }

        initTableWithFilter('notInvoicedTable',  2);
        initTableWithFilter('invoicedTable',     3);
        initTableWithFilter('notReceiptedTable', 2);
        initTableWithFilter('receiptedTable',    3);
    });
    </script>

</body>

</html>