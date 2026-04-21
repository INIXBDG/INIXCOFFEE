<!DOCTYPE html>
<html>
<head>
    <title>Export Jurnal Akuntansi</title>
    <style>
        table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        h2, h4 { text-align: center; font-family: Arial, sans-serif; margin: 5px 0; }
    </style>
</head>
<body>
    <h2>Laporan Jurnal Akuntansi</h2>
    <h4>Periode: {{ strtoupper($periode) }}</h4>
    <br>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor KK</th>
                <th>Tanggal Transaksi</th>
                <th>Keterangan</th>
                <th>No Akun</th>
                <th>Debit (Rp)</th>
                <th>Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalDebit = 0; 
                $totalKredit = 0; 
            @endphp
            @foreach($data as $index => $item)
                @php 
                    $totalDebit += $item->debit; 
                    $totalKredit += $item->kredit; 
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->nomor_kk ?? '-' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d-m-Y') }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="text-center">{{ $item->no_akun ?? '-' }}</td>
                    
                    <td class="text-right" style="mso-number-format:'\@';">{{ number_format($item->debit, 2, ',', '.') }}</td>
                    <td class="text-right" style="mso-number-format:'\@';">{{ number_format($item->kredit, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">TOTAL</th>
                
                <th class="text-right" style="mso-number-format:'\@';">{{ number_format($totalDebit, 2, ',', '.') }}</th>
                <th class="text-right" style="mso-number-format:'\@';">{{ number_format($totalKredit, 2, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>