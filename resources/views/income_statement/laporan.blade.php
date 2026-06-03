<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba/Rugi - PT XYZ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            background-color: #fff;
            color: #000;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-table td {
            padding: 4px 2px;
            vertical-align: bottom;
        }
        /* Utilitas Teks */
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        /* Utilitas Indentasi */
        .indent-1 { padding-left: 20px !important; }
        .indent-2 { padding-left: 40px !important; }
        
        /* Utilitas Garis Batas */
        .border-top { border-top: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }
        .border-bottom-double { border-bottom: 3px double #000; }
        
        /* Pengaturan Lebar Kolom */
        .col-keterangan { width: 35%; }
        .col-currency { width: 3%; text-align: left; }
        .col-value { width: 12%; text-align: right; }
        .col-percent { width: 7%; text-align: center; }
    </style>
</head>
<body>

    <div class="header-title">
        PT XYZ<br>
        Laporan Laba/Rugi<br>
        Januari - Desember 2026
    </div>

    <table class="report-table">
        <tbody>
            <tr class="font-bold">
                <td class="col-keterangan" colspan="10">Pendapatan:</td>
            </tr>
            <tr>
                <td class="indent-1">Penjualan Training:</td>
                <td class="col-currency">Rp</td>
                <td class="col-value"></td>
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
                <td class="col-value">41,926,500.00</td>
                <td class="col-percent" style="background-color: #fce4d6;">7.0%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-2">Exam</td>
                <td class="col-currency">-Rp</td>
                <td class="col-value">17,088,000.00</td>
                <td class="col-percent" style="background-color: #fce4d6;">2.8%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-2">Discount</td>
                <td class="col-currency border-bottom">Rp</td>
                <td class="col-value border-bottom">-</td>
                <td class="col-percent" style="background-color: #fce4d6;">0.0%</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-2">Nett Sales Training</td>
                <td colspan="2"></td>
                <td class="col-percent" style="background-color: #fce4d6;">90.2%</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">540,985,500</td>
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
                <td class="col-value">540,985,505</td>
                <td></td>
            </tr>

            <tr class="font-bold">
                <td colspan="10"><br>Beban Biaya Penjualan:</td>
            </tr>
            <tr>
                <td class="indent-1">Biaya-Biaya Training</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">-</td>
                <td class="col-percent text-left">#DIV/0! HPP</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Tunjangan Komisi Sales</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">-</td>
                <td class="col-percent text-left">#DIV/0!</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Tunjangan Komisi Instruktur</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">-</td>
                <td class="col-percent text-left">#DIV/0!</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Bonus Tahunan u/Sales</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">-</td>
                <td class="col-percent text-left">#DIV/0!</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya FEE Proyek</td>
                <td class="col-currency border-bottom">Rp</td>
                <td class="col-value border-bottom">-</td>
                <td class="col-percent text-left border-bottom">#DIV/0!</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Total Beban Biaya Penjualan</td>
                <td colspan="3"></td>
                <td class="col-currency">Rp</td>
                <td class="col-value">-</td>
                <td class="col-percent">0%</td>
                <td colspan="3"></td>
            </tr>

            <tr class="font-bold">
                <td colspan="10"><br>Beban Biaya Operasional:</td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Inventaris</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">-</td>
                <td class="col-percent text-left">#DIV/0!</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Gaji & Tunjangan Karyawan</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">-</td>
                <td class="col-percent text-left">#DIV/0!</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Tugas Luar Kota (SPJ)</td>
                <td class="col-currency">Rp</td>
                <td class="col-value">-</td>
                <td class="col-percent text-left">#DIV/0!</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Biaya Operasional</td>
                <td class="col-currency border-bottom">Rp</td>
                <td class="col-value border-bottom">-</td>
                <td class="col-percent text-left border-bottom">#DIV/0!</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="indent-1">Total Beban Biaya Operasional</td>
                <td colspan="3"></td>
                <td class="col-currency">Rp</td>
                <td class="col-value border-bottom">-</td>
                <td class="col-percent">0%</td>
                <td colspan="3"></td>
            </tr>

            <tr class="font-bold">
                <td>Total Beban Biaya</td>
                <td class="col-currency"></td>
                <td class="col-value text-left">HPP</td>
                <td class="col-percent text-left">#DIV/0!</td>
                <td colspan="3"></td>
                <td class="col-currency border-bottom">Rp</td>
                <td class="col-value border-bottom">-</td>
                <td></td>
            </tr>
            <tr class="font-bold border-bottom-double">
                <td>Laba Bersih 2026</td>
                <td colspan="6"></td>
                <td class="col-currency">Rp</td>
                <td class="col-value"></td>
                <td></td>
            </tr>
        </tbody>
    </table>

</body>
</html>