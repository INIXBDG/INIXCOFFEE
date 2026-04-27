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
            font-size: 11px; 
            margin: 0;
            line-height: 1.3; 
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
        }

        .table-invoice th,
        .table-invoice td {
            border: 1px solid #000;
            padding: 4px 6px;
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

        table table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-customer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px; 
        }

        .customer-info-box {
            border: 1px solid #000;
            padding: 8px; 
            margin-top: 5px; 
        }

        .invoice-details-table {
            font-size: 12px; 
            margin-top: 5px;
            margin-bottom: 10px; 
        }

        .footer-bank-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .footer-bank-content {
            text-align: center;
            vertical-align: top;
            padding: 8px; 
        }

        .footer-signature-table {
            width: 100%;
            margin-top: 25px; 
            border-collapse: collapse;
        }

        .signature-cell {
            text-align: center;
            vertical-align: top;
            width: 50%; 
        }

        .signature-space {
            height: 60px; 
            margin-bottom: 5px; 
        }

        .table-invoice tr, .table-invoice td, .table-invoice tbody { page-break-inside: avoid; }
    </style>
</head>

<body>
    <table class="header-customer-table">
        <tr>
            <td style="width:60%; vertical-align:top;">
                <img src="{{ public_path('icon/logoo.png') }}" alt="Logo" style="width:150px;"><br>
                <p style="margin:0; font-weight:bold;">PT. INIXINDO AMIETE MANDIRI</p>
                <p style="margin:0;">Jl. Cipaganti No.95, Bandung 40161</p>
                <p style="margin:0;">Ph. (022) 2032831 / Fax. (022) 2032831</p>
            </td>
            <td style="width:40%; vertical-align:top; text-align:center;">
                <h2 style="margin:0; font-weight:bold; margin-top: 90px;">INVOICE</h2>
                <hr style="border:1px solid #000; margin:2px 0;">
            </td>
        </tr>
        <tr>
            <td colspan="2" style="vertical-align:top; padding:0;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:60%; vertical-align:top; padding-right:10px;">
                            <div class="customer-info-box">
                                <strong>Bill To</strong><br>
                                <b>{{$nama_perusahaan ?? '-'}}</b>
                            </div>
                        </td>
                        <td style="width:40%; vertical-align:top; text-align:center;">
                            <table class="invoice-details-table">
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
                                    <td style="border:1px solid #000; padding:6px;">
                                        {{ $invoice->purchase_order ?? '-' }}
                                    </td>
                                    <td style="border:1px solid #000; padding:6px;">
                                        {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('l, d F Y') : '-' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

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
                                {{ $materi ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 6px;"><strong>Tanggal</strong></td>
                            <td style="border:none; padding: 2px 6px;">:</td>
                            <td style="border:none; padding: 2px 6px;">
                                {{ $tanggal_awal ? \Carbon\Carbon::parse($tanggal_awal)->translatedFormat('d F Y') : '-' }}
                                {{-- {{ optional($invoice->rkm)->tanggal_awal ? \Carbon\Carbon::parse($invoice->rkm->tanggal_awal)->format('d F Y') : '-' }} --}}
                                s/d
                                {{ $tanggal_akhir ? \Carbon\Carbon::parse($tanggal_akhir)->translatedFormat('d F Y') : '-' }}
                                {{-- {{ optional($invoice->rkm)->tanggal_akhir ? \Carbon\Carbon::parse($invoice->rkm->tanggal_akhir)->format('d F Y') : '-' }} --}}
                            </td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 6px;"><strong>Peserta</strong></td>
                            <td style="border:none; padding: 2px 6px;">:</td>
                            <td style="border:none; padding: 2px 6px;">

                            @if ($isPeserta)
                                @if(!empty($pesertaList) && is_array($pesertaList))
                                    @foreach($pesertaList as $index => $namaPeserta)
                                        {{ $loop->iteration }}. {{ e($namaPeserta) }}<br>
                                    @endforeach
                                @else
                                    {{ $invoice->rkm->isi_pax ? $invoice->rkm->isi_pax . ' orang' : '-' }}
                                @endif
                            @else
                                {{ $invoice->rkm->isi_pax ? $invoice->rkm->isi_pax . ' orang' : '-' }}
                            @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="text-center">{{ $invoice->rkm->isi_pax ?? '0' }}</td>
                <td class="text-end">Rp {{ number_format($unit_price ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format(($unit_price ?? 0) * ($pax ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" rowspan="{{ $isPPh ? 4 : 3 }}"></td>
                <td class="text-end">Sub Total</td>
                <td class="text-end">
                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td class="text-end">PPN 11%</td>
                <td class="text-end">
                    Rp {{ number_format($ppn, 0, ',', '.') }}
                </td>
            </tr>

            @if ($isPPh)
            <tr>
                <td class="text-end">PPh 23 (2%)</td>
                <td class="text-end">
                    - Rp {{ number_format($pph, 0, ',', '.') }}
                </td>
            </tr>
            @endif

            <tr>
                <td class="text-end fw-bold">TOTAL</td>
                <td class="text-end fw-bold">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td colspan="5" class="text-center fw-bold" style="background-color: #ddd;"><i>{{ $terbilang }}</i></td>
            </tr>
        </tbody>
    </table>

    <table class="footer-bank-table">
        <tr>
            <td class="footer-bank-content">
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
{{-- 
                @if($invoice->catatan_pembayaran)
                    <p style="margin:0;">{{ $invoice->catatan_pembayaran }}</p>
                @else
                    <p style="margin:0;">Note : Mohon nomor invoice dan nama perusahaan dicantumkan</p>
                @endif --}}
            </td>
        </tr>
    </table>

    <table class="footer-signature-table">
        <tr>
            <td class="signature-cell">
                    @if ($isTtd)
                        <p><b>Recieved by,</b></p>
                        <div class="signature-space"></div> 
                        <p style="margin:0;">
                            <u>{{ $invoice->rkm->perusahaan->nama_perusahaan ?? 'Customer Stamp & Signature' }}</u>
                        </p>
                        <small>Customer Stamp & Signature</small>
                    @endif
                </td>
            <td class="signature-cell">
                <div class="signature-space" style="margin-top: 32px"></div> 
                <p style="margin:0;">
                    <u>{{ $karyawan->nama_lengkap ?? 'Nama Penanggung Jawab Kanan' }}</u>
                </p>
                <small>Accounting Finance</small>
            </td>
        </tr>
    </table>

</body>

</html>