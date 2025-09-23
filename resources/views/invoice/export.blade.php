<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .invoice-table {
            border-collapse: collapse;
            width: 100%;
        }
        .invoice-table th {
            background: #f1f1f1;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <h2 class="text-center">Detail Invoice #{{ $invoice->invoice_number }}</h2>

    <table class="invoice-table">
        <tbody>
            <tr>
                <td colspan="3" class="fw-bold">Nomor Invoice:</td>
                <td colspan="2">{{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td colspan="3" class="fw-bold">Tanggal Invoice:</td>
                <td colspan="2">{{ \Carbon\Carbon::parse($invoice->tanggal_invoice)->format('d F Y') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="fw-bold text-center">Detail RKM</td>
            </tr>
            <tr>
                <td colspan="3">Perusahaan:</td>
                <td colspan="2"><b>{{ $invoice->rkm->perusahaan->nama_perusahaan ?? '-' }}</b></td>
            </tr>
            <tr>
                <td colspan="3">Materi:</td>
                <td colspan="2"><b>{{ $invoice->rkm->materi->nama_materi ?? '-' }}</b></td>
            </tr>
            <tr>
                <td colspan="3">Tanggal:</td>
                <td colspan="2"><b>{{ \Carbon\Carbon::parse($invoice->rkm->tanggal_awal)->format('d F Y') }} 
                s/d {{ \Carbon\Carbon::parse($invoice->rkm->tanggal_akhir)->format('d F Y') }}</b></td>
            </tr>
            <tr>
                <td colspan="3">Peserta:</td>
                <td colspan="2"><b>{{ $invoice->rkm->pax ?? '-' }}</b></td>
            </tr>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Deskripsi</th>
                <th style="width: 10%;">Pax</th>
                <th style="width: 20%;">Harga Unit</th>
                <th style="width: 20%;">Jumlah</th>
            </tr>
            <tr>
                <td>1</td>
                <td>
                    Materi: {{ $invoice->rkm->materi->nama_materi ?? '-' }} <br>
                    Tanggal: {{ \Carbon\Carbon::parse($invoice->rkm->tanggal_awal)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($invoice->rkm->tanggal_akhir)->format('d F Y') }} <br>
                    Peserta: {{ $invoice->rkm->pax ?? '-' }} orang
                </td>
                <td>{{ $invoice->rkm->pax ?? '-' }}</td>
                <td>Rp. {{ number_format($invoice->rkm->harga_jual ?? 0, 0, ',', '.') }}</td>
                <td>Rp. {{ number_format(($invoice->rkm->harga_jual ?? 0) * ($invoice->rkm->pax ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" rowspan="3"></td>
                <td class="text-end">SubTotal</td>
                <td class="text-end">Rp. {{ number_format(($invoice->rkm->harga_jual ?? 0) * ($invoice->rkm->pax ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-end">PPN 11%</td>
                <td class="text-end">Rp. {{ number_format((($invoice->rkm->harga_jual ?? 0) * ($invoice->rkm->pax ?? 0)) * 0.11, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-end fw-bold">TOTAL</td>
                <td class="text-end fw-bold">Rp. {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-center fw-bold"><i>{{ $terbilang }}</i></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
