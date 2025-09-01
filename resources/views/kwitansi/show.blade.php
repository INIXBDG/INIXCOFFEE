<!DOCTYPE html>
<html lang="id">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<head>
    <title>Kwitansi {{ $kwitansi->invoice->invoice_number }}</title>
    <style>
        .meta {
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 10px;
            /* kasih jarak antar cell */
        }

        .meta td {
            padding: 4px 10px;
            /* kiri-kanan lebih lega */
        }

        .kwitansi-header {
            text-align: center;
            width: 100%;
        }

        .kwitansi-header h1 {
            margin: 0 auto;
            font-weight: bold;
            font-size: 28px;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            width: 60%;
            /* panjang garis */
            text-align: center;
            /* teks tetap center */
            display: block;
            /* biar konsisten di print */
        }


        .nomor-kwitansi {
            font-size: 16px;
            margin-top: 6px;
        }

        /* khusus print */
        @media print {
            .no-print {
                display: none !important;
            }

            .kwitansi-header {
                width: 100%;
                text-align: center;
            }

            @media print {
                .kwitansi-header h1 {
                    font-size: 28px;
                    width: 60%;
                }
            }

            .nomor-kwitansi {
                font-size: 16px;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

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
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .container {
            width: 95%;
            margin: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            text-decoration: underline;
        }

        .meta {
            width: 100%;
            margin-bottom: 20px;
        }

        .meta td {
            padding: 4px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .signature {
            margin-top: 50px;
            width: 100%;
        }

        .signature td {
            text-align: center;
            vertical-align: bottom;
            height: 80px;
        }
    </style>
</head>

<body>
    <table class="meta">
        <tr>
            <td style="width:60%; vertical-align:top;">
                <img src="{{ asset('icon/logoo.png') }}" alt="Logo" style="width:150px;"><br>
                <p style="margin:0; font-weight:bold;">PT. INIXINDO AMIETE MANDIRI</p>
                <p style="margin:0;">Jl. Cipaganti No.95, Bandung 40161</p>
                <p style="margin:0;">Ph. (022) 2032831 / Fax. (022) 2032831</p>
            </td>
            <td style="width:40%; vertical-align:bottom; text-align:center;">
                <div class="kwitansi-header">
                    <h1 class="judul-kwitansi">
                        KUITANSI
                    </h1>
                    <div class="nomor-kwitansi">
                        Nomor : {{ $kwitansi->invoice->invoice_number ?? '-' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="no-print" style="position: absolute; top: 20px; right: 20px;">
        <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <table style="margin-bottom:20px; white-space:nowrap;">
        <tr style="font-weight: bold;">
            <td style="width:150px;">Sudah terima dari</td>
            <td>: {{ $kwitansi->invoice->rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
        </tr>
        <tr style="font-weight: bold;">
            <td>Jumlah Uang</td>
            <td>: Rp {{ number_format($kwitansi->invoice->amount ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr style="font-weight: bold;">
            <td>Terbilang</td>
            <td> : <i style="border-bottom: 0.5px solid #000; width: max-content;">{{ $terbilang }}</i></td>
        </tr>

        <tr>
            <td>Untuk Pembayaran Pelatihan</td>
            <td> : {{ $kwitansi->invoice->rkm->materi->nama_materi }}</td>
        </tr>
        <tr>
            <td>tanggal</td>
            <td> : {{ optional($kwitansi->invoice->rkm)->tanggal_awal ? \Carbon\Carbon::parse($kwitansi->invoice->rkm->tanggal_awal)->format('d F') : '-' }}
                -
                {{ optional($kwitansi->invoice->rkm)->tanggal_akhir ? \Carbon\Carbon::parse($kwitansi->invoice->rkm->tanggal_akhir)->format('d F Y') : '-' }}
            </td>
        </tr>
    </table>



    <table style="width:100%; margin-top:40px; border-collapse:collapse; border: 1px solid #000;">
        <tr>
            <td style="width:100%; text-align:center; vertical-align:top; border: 1px solid #000;">
                <p style="margin:0;">Pembayaran dapat dilakukan melalui transfer ke :</p>
                <p style="margin:0; font-weight:bold;">{{ $invoice->bank_name ?? 'BANK MANDIRI KK BANDUNG CIHAMPELAS' }}</p>
                <p style="margin:0; font-weight:bold;">No. Rek : {{ $invoice->account_number ?? '131-00-0734797-6' }}</p>
                <p style="margin:0; font-weight:bold;">a/n PT. INIXINDO AMIETE MANDIRI</p>
                @if($kwitansi->invoice->catatan_pembayaran)
                <p style="margin:0;">{{ $invoice->catatan_pembayaran }}</p>
                @else
                <p style="margin:0;">Note : Mohon nomor invoice dan nama perusahaan dicantumkan</p>
                @endif
            </td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: center; vertical-align: top;">
                <p style="margin: 0;">{{ $kota ?? 'Bandung, ' }} {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <div style="height: 60px;"></div>
                <p style="margin: 0;"><u>{{ $karyawan->nama_lengkap ?? 'Nama Penanggung Jawab Kanan' }}</u></p>
                <small>{{ $karyawan->jabatan ?? 'Accounting Finance' }}</small>
            </td>
        </tr>
    </table>
    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Ambil isi halaman (sederhana, bisa pakai html2canvas untuk layout lengkap)
        doc.html(document.body, {
            callback: function (doc) {
                // Nama file: Kwitansi + nomor invoice
                let nomorInvoice = "{{ $kwitansi->invoice->invoice_number ?? 'NoInvoice' }}";
                doc.save("Kwitansi-" + nomorInvoice + ".pdf");
            },
            x: 10,
            y: 10
        });
    }
</script>
</html>