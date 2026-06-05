<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba/Rugi - PT Inixindo Amiete Mandiri</title>
    <link rel="apple-touch-icon" sizes="180x180" href="https://inixindobdg.co.id/images/logoinix.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://inixindobdg.co.id/images/logoinix.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://inixindobdg.co.id/images/logoinix.png">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    {{-- <link rel="stylesheet" href="css/app.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; background-color: #fff; color: #000; }
        .header-title { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table td { padding: 4px 2px; vertical-align: bottom; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .indent-1 { padding-left: 20px !important; }
        .indent-2 { padding-left: 40px !important; }
        .border-top { border-top: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }
        .border-bottom-double { border-bottom: 3px double #000; }
        .col-keterangan { width: 35%; }
        .col-currency { width: 3%; text-align: left; }
        .col-value { width: 12%; text-align: right; }
        .col-percent { width: 7%; text-align: center; }
    </style>
</head>
<body>

    <div class="header-title">
        PT Inixindo Amiete Mandiri<br>
        Laporan Laba/Rugi<br>
        Januari - Desember {{ $year }}<br>
        <a href="javascript:void(0);" class="btn btn-success me-1 d-print-none" id="printInvoiceBTN"><i class="fa fa-print"></i> Print Invoice</a>
    </div>

    <table class="report-table">
        <tbody>
            <tr class="font-bold">
                <td class="col-keterangan" colspan="10">Pendapatan:</td>
            </tr>
            <tr>
                <td class="indent-1">Penjualan Training:</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($total_penjualan_training, 2, '.', ',') }}</td>
                <td class="col-percent"></td>
                <td class="col-currency"></td>
                <td class="col-value"></td>
                <td class="col-percent"></td>
                <td class="col-currency"></td>
                <td class="col-value"></td>
                <td class="col-percent"></td>
            </tr>
            <tr>
                <td class="indent-2">Beban Pokok Penjualan Training:</td>
                <td colspan="9"></td>
            </tr>
            <tr>
                <td class="indent-2">Payment Advance</td>
                <td class="col-currency">-Rp</td>
                <td class="col-value">{{ number_format($total_payment_advance, 2, '.', ',') }}</td>
                <td class="col-percent" style="background-color: #fce4d6;">{{ number_format($pct_payment_advance, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-2">Exam</td>
                <td class="col-currency">-Rp</td>
                <td class="col-value">{{ number_format($total_exam, 2, '.', ',') }}</td>
                <td class="col-percent" style="background-color: #fce4d6;">{{ number_format($pct_exam, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-2">Discount</td>
                <td class="col-currency border-bottom">Rp</td>
                <td class="col-value border-bottom">{{ number_format($total_discount, 2, '.', ',') }}</td>
                <td class="col-percent" style="background-color: #fce4d6;">{{ number_format($pct_discount, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-2">Nett Sales Training</td>
                <td colspan="2"></td>
                <td class="col-percent" style="background-color: #fce4d6;">{{ number_format($pct_netSales_training, 1, '.', '') }}%</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($netSales_training, 2, '.', ',') }}</td>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td class="col-percent" style="background-color: #fce4d6;">100.0%</td>
                <td colspan="6"></td>
            </tr>
            
            <tr class="font-bold border-top border-bottom">
                <td>Total Pendapatan Bruto</td>
                <td colspan="6"></td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($netSales_training, 2, '.', ',') }}</td>
                <td></td>
            </tr>

            <tr class="font-bold">
                <td colspan="10"><br>Beban Biaya Penjualan:</td>
            </tr>
            <tr>
                <td class="indent-1">Biaya-Biaya Training</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($biaya_biaya_training, 2, '.', ',') }}</td>
                <td class="col-percent text-left">{{ number_format($pct_biaya_training, 1, '.', '') }}%</td>
                <td colspan="6">HPP</td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Tunjangan Komisi Sales</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($tunjangan_sales, 2, '.', ',') }}</td>
                <td class="col-percent text-left">{{ number_format($pct_tunjangan_sales, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Tunjangan Komisi Instruktur</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($tunjangan_instruktur, 2, '.', ',') }}</td>
                <td class="col-percent text-left">{{ number_format($pct_tunjangan_instruktur, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Bonus Tahunan u/Sales</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($bonus_tahunan_sales, 2, '.', ',') }}</td>
                <td class="col-percent text-left">{{ number_format($pct_bonus_sales, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya FEE Proyek</td>
                <td class="col-currency border-bottom">Rp</td>
                <td class="col-value border-bottom">{{ number_format($fee_proyek, 2, '.', ',') }}</td>
                <td class="col-percent text-left border-bottom">{{ number_format($pct_fee_proyek, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Total Beban Biaya Penjualan</td>
                <td colspan="3"></td>
                <td class="col-currency">Rp</td>
                <td class="col-value border-bottom">{{ number_format($total_beban_biaya_penjualan, 2, '.', ',') }}</td>
                <td class="col-percent">{{ number_format($pct_total_beban_penjualan, 1, '.', '') }}%</td>
                <td colspan="3"></td>
            </tr>

            <tr class="font-bold">
                <td colspan="10"><br>Beban Biaya Operasional:</td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Inventaris</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($biaya_inventaris, 2, '.', ',') }}</td>
                <td class="col-percent text-left">{{ number_format($pct_biaya_inventaris, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Gaji & Tunjangan Karyawan</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($biaya_tunjangan_karyawan, 2, '.', ',') }}</td>
                <td class="col-percent text-left">{{ number_format($pct_tunjangan_karyawan, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Tugas Luar Kota (SPJ)</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($biaya_spj, 2, '.', ',') }}</td>
                <td class="col-percent text-left">{{ number_format($pct_spj, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Operasional</td>
                <td class="col-currency border-bottom">Rp</td>
                <td class="col-value border-bottom">{{ number_format($biaya_operasional, 2, '.', ',') }}</td>
                <td class="col-percent text-left border-bottom">{{ number_format($pct_operasional, 1, '.', '') }}%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Total Beban Biaya Operasional</td>
                <td colspan="3"></td>
                <td class="col-currency">Rp</td>
                <td class="col-value border-bottom">{{ number_format($total_biaya_operasional, 2, '.', ',') }}</td>
                <td class="col-percent">{{ number_format($pct_total_biaya_operasional, 1, '.', '') }}%</td>
                <td colspan="3"></td>
            </tr>

            <tr class="font-bold">
                <td>Total Beban Biaya</td>
                <td class="col-currency"></td>
                <td class="col-value text-left">HPP</td>
                <td class="col-percent text-left">{{ number_format($pct_total_beban_biaya, 1, '.', '') }}%</td>
                <td colspan="3"></td>
                <td class="col-currency border-bottom">Rp</td>
                <td class="col-value border-bottom">{{ number_format($total_beban_biaya, 2, '.', ',') }}</td>
                <td></td>
            </tr>
            <tr class="font-bold border-bottom-double">
                <td>Laba Bersih {{ $year }}</td>
                <td colspan="6"></td>
                <td class="col-currency">Rp</td>
                <td class="col-value">{{ number_format($laba_bersih, 2, '.', ',') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://kit.fontawesome.com/85b3409c34.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script>
            $(document).ready(function() {
                $('#printInvoiceBTN ').on('click', function() {
                    window.print();
                });
            });
    </script>
</body>
</html>