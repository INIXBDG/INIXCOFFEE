<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Detail Invoice #{{ $invoice->invoice_number }}</h2>
        <div class="card mt-3">
            <div class="card-body">
                <p><strong>Nomor Invoice:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Tanggal Invoice:</strong> {{ \Carbon\Carbon::parse($invoice->tanggal_invoice)->format('d F Y') }}</p>
                <p><strong>Nama Perusahaan:</strong> {{ $invoice->rkm->perusahaan->nama_perusahaan ?? '-' }}</p>
                <p><strong>Nama Materi:</strong> {{ $invoice->rkm->materi->nama_materi ?? '-' }}</p>
                <p><strong>Total Harga:</strong> Rp. {{ number_format($invoice->amount, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</body>
</html>