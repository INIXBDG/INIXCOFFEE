<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            td {
                white-space: nowrap;
            }
        }

        @page {
            margin: 40px;
            size: A4;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

        .table-invoice {
            border: 1px solid #000;
            border-collapse: collapse;
            width: 100%;
            page-break-inside: avoid;
        }

        .table-invoice th,
        .table-invoice td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        .table-invoice th {
            background: #f1f1f1;
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Pastikan tabel float tidak overflow */
        div[style*="overflow: hidden"] table {
            display: inline-table;
            width: 40% !important;
            page-break-inside: avoid;
        }

        /* Pastikan nested table tidak bermasalah */
        table table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <table style="width:100%; margin-bottom:10px; border-collapse:collapse;">
        <tr>
            <td style="width:60%; vertical-align:top;">
                <img src="{{ asset('icon/logoo.png') }}" alt="Logo" style="width:150px;"><br>
                <p style="margin:0; font-weight:bold;">PT. INIXINDO AMIETE MANDIRI</p>
                <p style="margin:0;">Jl. Cipaganti No.95, Bandung 40161</p>
                <p style="margin:0;">Ph. (022) 2032831 / Fax. (022) 2032831</p>
            </td>
        <tr class="no-print">
<div class="no-print" style="position: absolute; top: 20px; right: 20px;">
    <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Kembali</a>
</div>
        </tr>
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:60%; vertical-align:top; padding-right:10px;">
                    <table style="width:100%; border-collapse:collapse; border:1px solid #000; margin-top:40px;">
                        <tr>
                            <td style="background:#ddd; font-weight:bold; padding:6px; border-bottom:1px solid #000;">
                                Bill To
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:10px; height:100px; ">
                                {{ $invoice->rkm->perusahaan->nama_perusahaan ?? 'Bank Indonesia' }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:40%; vertical-align:top; text-align:center;">
                    <h2 style="margin:0; font-weight:bold;">INVOICE</h2>
                    <hr style="border:1px solid #000; margin:2px 0;">
                    <table style="width:100%; border-collapse:collapse; font-size:14px; margin-top:5px; margin-bottom:20px;">
                        <tr>
                            <td style="border:1px solid #000; padding:6px; font-weight:bold; width:50%;">Invoice Date</td>
                            <td style="border:1px solid #000; padding:6px; font-weight:bold; width:50%;">Invoice No</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:6px;">
                                {{ \Carbon\Carbon::parse($invoice->tanggal_invoice)->translatedFormat('l, d F Y') }}
                            </td>
                            <td style="border:1px solid #000; padding:6px;">
                                {{ $invoice->invoice_number }}
                            </td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:6px; font-weight:bold;">Purchase Order No.</td>
                            <td style="border:1px solid #000; padding:6px; font-weight:bold;">Due Date</td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:6px;"></td>
                            <td style="border:1px solid #000; padding:6px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </tr>
    </table>

    <!-- DETAIL BIAYA -->
    <table class="table-invoice">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:45%;">Description</th>
                <th style="width:10%;">Qty</th>
                <th style="width:20%;">Unit Price</th>
                <th style="width:20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <table style="border-collapse: collapse; width:100%;">
                        <tr>
                            <td style="border:none; padding: 2px 6px;"><strong>Materi</strong></td>
                            <td style="border:none; padding: 2px 6px;">:</td>
                            <td style="border:none; padding: 2px 6px;">
                                {{ optional($invoice->rkm->materi)->nama_materi ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 6px;"><strong>Tanggal</strong></td>
                            <td style="border:none; padding: 2px 6px;">:</td>
                            <td style="border:none; padding: 2px 6px;">
                                {{ optional($invoice->rkm)->tanggal_awal ? \Carbon\Carbon::parse($invoice->rkm->tanggal_awal)->format('d F Y') : '-' }}
                                s/d
                                {{ optional($invoice->rkm)->tanggal_akhir ? \Carbon\Carbon::parse($invoice->rkm->tanggal_akhir)->format('d F Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 6px;"><strong>Peserta</strong></td>
                            <td style="border:none; padding: 2px 6px;">:</td>
                            <td style="border:none; padding: 2px 6px;">
                                {{ optional($invoice->rkm)->pax ?? '-' }} orang
                            </td>
                        </tr>
                    </table>
                </td>


                <td class="text-center">{{ $invoice->rkm->pax ?? '-' }}</td>
                <td class="text-end">Rp {{ number_format($invoice->rkm->harga_jual ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format(($invoice->rkm->harga_jual ?? 0) * ($invoice->rkm->pax ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" rowspan="4"></td>
                <td class="text-end">Sub Total</td>
                <td class="text-end">
                    Rp {{ number_format(($invoice->rkm->harga_jual ?? 0) * ($invoice->rkm->pax ?? 0), 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-end">PPN 11%</td>
                <td class="text-end">
                    Rp {{ number_format((($invoice->rkm->harga_jual ?? 0) * ($invoice->rkm->pax ?? 0)) * 0.11, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-end">PPh 23 (2%)</td>
                <td class="text-end">
                    - Rp {{ number_format((($invoice->rkm->harga_jual ?? 0) * ($invoice->rkm->pax ?? 0)) * 0.02, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="text-end fw-bold">TOTAL</td>
                <td class="text-end fw-bold">
                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td colspan="5" class="text-center fw-bold" style="background-color: #ddd;"><i>{{ $terbilang }}</i></td>
            </tr>
        </tbody>
    </table>

    <!-- FOOTER -->
    <!-- FOOTER -->
    <table style="width:100%; margin-top:40px; border-collapse:collapse; border: 1px solid #000;">
        <tr>
            <td style="width:100%; text-align:center; vertical-align:top; border: 1px solid #000;">
                <p style="margin:0;">Pembayaran dapat dilakukan melalui transfer ke :</p>
                <p style="margin:0; font-weight:bold;">{{ $invoice->bank_name ?? 'BANK MANDIRI KK BANDUNG CIHAMPELAS' }}</p>
                <p style="margin:0; font-weight:bold;">No. Rek : {{ $invoice->account_number ?? '131-00-0734797-6' }}</p>

                @php
                $accountName = 'PT. INIXINDO AMIETE MANDIRI';
                if (($invoice->bank_name ?? '') === 'BANK BCA KK BANDUNG ABDUL RIVAI') {
                $accountName = 'RAY GUTAFSON MANURUNG';
                }
                @endphp

                <p style="margin:0; font-weight:bold;">a/n {{ $accountName }}</p>

                @if($invoice->catatan_pembayaran)
                <p style="margin:0;">{{ $invoice->catatan_pembayaran }}</p>
                @else
                <p style="margin:0;">Note : Mohon nomor invoice dan nama perusahaan dicantumkan</p>
                @endif
            </td>
        </tr>
    </table>
    </table>

    <div style="width: 100%; overflow: hidden;">
        <!-- Tabel Kiri -->
        <table style="width: 40%; float: left; margin-top: 35px; border-collapse: collapse;">
            <tr>
                <td style="width: 100%; text-align: center; vertical-align: top;">
                    <p><b>Recieved by,</b></p>
                    <div style="height: 60px;"></div>
                    <p style="margin: 0;"><u> {{ $invoice->rkm->perusahaan->nama_perusahaan ?? 'Customer Stamp & Signature' }}</u></p>
                    <small>Customer Stamp & Signature</small>
                </td>
            </tr>
        </table>

        <!-- Tabel Kanan -->
        <table style="width: 40%; float: right; margin-top: 70px; border-collapse: collapse;">
            <tr>
                <td style="width: 100%; text-align: center; vertical-align: top;">
                    <div style="height: 60px;"></div>
                    <p style="margin: 0;"><u>{{ $karyawan->nama_lengkap ?? 'Nama Penanggung Jawab Kanan' }}</u></p>
                    <small>{{ $karyawan->jabatan ?? 'Accounting & Finance' }}</small>
                </td>
            </tr>
        </table>
    </div>

    <!-- TOMBOL PRINT -->
    <div class="no-print text-center" style="margin-top:40px;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
</body>

</html>