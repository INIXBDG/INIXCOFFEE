<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Payroll</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 9px; 
            line-height: 1.2;
            padding: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px;
        }
        th, td { 
            border: 1px solid #000; 
            padding: 3px 4px;
            vertical-align: middle;
        }
        .header { 
            text-align: center; 
            font-weight: bold; 
            font-size: 12px;
            padding: 5px;
        }
        .th-header { 
            background: #f0f0f0; 
            font-weight: bold; 
            text-align: center;
            font-size: 8px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .summary-table { 
            width: 60%; 
            margin-top: 10px;
        }
        .summary-header {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th class="header" colspan="22">LAPORAN PERHITUNGAN PAYROLL DAN BPJS</th>
        </tr>
        <tr>
            <td colspan="22" style="text-align:center;background:#f5f5f5;">
                Periode: {{ $data['periode_label'] }}
            </td>
        </tr>
        <tr>
            <td colspan="22">Dicetak: {{ now()->format('d M Y H:i') }}</td>
        </tr>
        
        <tr>
            <th class="th-header">No</th>
            <th class="th-header">Nama</th>
            <th class="th-header">Status</th>
            <th class="th-header">Salary<br>Bulan</th>
            <th class="th-header">Salary<br>BPJSTK</th>
            <th class="th-header">Tunjangan</th>
            <th class="th-header">THP</th>
            <th class="th-header">UMK<br>Bandung</th>
            <th class="th-header">JHT Per<br>3.70%</th>
            <th class="th-header">JKM Per<br>0.30%</th>
            <th class="th-header">JKK Per<br>0.24%</th>
            <th class="th-header">JP Per<br>2.00%</th>
            <th class="th-header">Total<br>BPJS Per</th>
            <th class="th-header">JHT Kar<br>2.00%</th>
            <th class="th-header">JP Kar<br>1.00%</th>
            <th class="th-header">Total<br>BPJS Kar</th>
            <th class="th-header">Total<br>Per+Kar</th>
            <th class="th-header">BPJS Kes<br>Per 4%</th>
            <th class="th-header">BPJS Kes<br>Kar 1%</th>
            <th class="th-header">Ditanggung<br>Per</th>
            <th class="th-header">Ditanggung<br>Kar</th>
            <th class="th-header">Salary<br>THP Kar</th>
        </tr>

        @php
            $no = 1;
            $totalGaji = 0;
            $totalDitanggungPer = 0;
            $totalDitanggungKar = 0;
        @endphp

        @foreach($data['rows'] as $row)
        <tr>
            <td class="text-center">{{ $no++ }}</td>
            <td>{{ $row['nama'] }}</td>
            <td class="text-center">{{ $row['status'] }}</td>
            <td class="text-right">{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['salary_bpjstk'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['total_tunjangan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['thp_bersih'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['umk_bandung'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['jht_perusahaan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['jkm_perusahaan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['jkk_perusahaan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['jp_perusahaan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['total_bpjs_perusahaan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['jht_karyawan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['jp_karyawan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['total_bpjs_karyawan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['total_bpjs_perusahaan'] + $row['total_bpjs_karyawan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['bpjs_kes_perusahaan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['bpjs_kes_karyawan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['total_bpjs_perusahaan'] + $row['bpjs_kes_perusahaan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['total_bpjs_karyawan'] + $row['bpjs_kes_karyawan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['thp_bersih'], 0, ',', '.') }}</td>
        </tr>
        @php
            $totalGaji += $row['gaji_pokok'];
            $totalDitanggungPer += $row['total_bpjs_perusahaan'] + $row['bpjs_kes_perusahaan'];
            $totalDitanggungKar += $row['total_bpjs_karyawan'] + $row['bpjs_kes_karyawan'];
        @endphp
        @endforeach

        <tr style="background:#f5f5f5;font-weight:bold;">
            <td colspan="3" class="text-right">TOTAL:</td>
            <td class="text-right">{{ number_format($totalGaji, 0, ',', '.') }}</td>
            <td colspan="19"></td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <th class="summary-header" colspan="2">RINGKASAN TOTAL BIAYA BPJS</th>
        </tr>
        <tr>
            <td style="width:70%;">Total Gaji Karyawan dalam 1 Tahun</td>
            <td class="text-right">{{ number_format($totalGaji * 12, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Batas Maksimal (maks 40%)</td>
            <td class="text-right">{{ number_format($totalGaji * 12 * 0.40, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total BPJS TK & Kes ditanggung pers. Dalam 1 tahun</td>
            <td class="text-right">{{ number_format($totalDitanggungPer * 12, 0, ',', '.') }}</td>
        </tr>
        @php
            $percentage = $totalGaji > 0 ? ($totalDitanggungPer / $totalGaji) * 100 : 0;
        @endphp

        <tr style="font-weight:bold;">
            <td>Persentase</td>
            <td class="text-right" style="background-color: {{ $percentage > 40 ? '#dc3545' : '#28a745' }}; color: #fff;">
                {{ number_format($percentage, 2, ',', '.') }}%
            </td>
        </tr>
    </table>
</body>
</html>