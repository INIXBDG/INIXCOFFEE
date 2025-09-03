<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        <h2 class="mt-5">RKM Siap Dibuatkan Invoice</h2>
        <table id="notInvoicedTable" class="table table-striped table-bordered">
            <thead>
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

        <h2 class="mt-5">RKM yang Sudah Dibuatkan Invoice</h2>
        <table id="invoicedTable" class="table table-striped table-bordered">
            <thead>
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
                            <a href="{{ route('invoice.show', $rkm->invoice->id) }}" class="btn btn-success btn-sm">Lihat Invoice</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#notInvoicedTable').DataTable();
            $('#invoicedTable').DataTable();
        });
    </script>
</body>
</html>