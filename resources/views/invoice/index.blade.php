
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
                                            <a href="{{ route('download.pdf', ['id' => $rkm->invoice->id, 'peserta[]' => $peserta]) }}" class="btn btn-primary">
                                                Pdf
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
                                            <a href="{{ route('kwitansi.create', ['invoiceId' => $rkm->invoice->id]) }}" class="btn btn-primary btn-sm">Buat Kwitansi</a>
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
                                            <a href="{{ route('kwitansi.pdf', $rkm->kwitansi->first()->id) }}" class="btn btn-success btn-sm">Lihat Kwitansi</a>
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
        $(document).ready(function() {
            $('#notInvoicedTable').DataTable();
            $('#invoicedTable').DataTable();
            $('#notReceiptedTable').DataTable();
            $('#receiptedTable').DataTable();
        });
    </script>

</body>

</html>