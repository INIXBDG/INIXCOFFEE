<!DOCTYPE html>
<html>
<head>
    <title>Export Jurnal Akuntansi</title>
    <style>
        table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #78ffcb; text-align: center; font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        h2, h4 { text-align: center; font-family: Arial, sans-serif; margin: 5px 0; font-weight: 700; }
    </style>
</head>
<body>
    <h2>PT INIXINDO AMIETE MANDIRI</h2>
    <h4>KAS KECIL ( PETTY CASH )</h4>
    <h4>{{ strtoupper($periode) }}</h4>
    <br>
    @php 
        $totalDebit = 0; 
        $totalKredit = 0; 
    @endphp
    <table>
        <thead>
            <tr><th colspan="7" class="text-center fw-bold" style="font-size: 16px;">Laporan Jurnal Akuntansi</th></tr>
            <tr><th colspan="7" class="text-center fw-bold" style="font-size: 14px;">Periode: {{ strtoupper($periode) }}</th></tr>
            <tr><th colspan="7" style="height: 15px;"></th></tr>
            <tr>
                <th class="border-all bg-gray text-center">No</th>
                <th class="border-all bg-gray text-center">Nomor KK</th>
                <th class="border-all bg-gray text-center">Tanggal Transaksi</th>
                <th class="border-all bg-gray text-center">Keterangan</th>
                <th class="border-all bg-gray text-center">No Akun</th>
                <th class="border-all bg-gray text-center">Debit (Rp)</th>
                <th class="border-all bg-gray text-center">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
                @php 
                    $totalDebit += $item->debit; 
                    $totalKredit += $item->kredit; 
                @endphp
                <tr>
                    <td class="border-all text-center">{{ $index + 1 }}</td>
                    <td class="border-all text-center">{{ $item->nomor_kk ?? '-' }}</td>
                    <td class="border-all text-center">{{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d-m-Y') }}</td>
                    <td class="border-all">{{ $item->keterangan }}</td>
                    <td class="border-all text-center">{{ $item->no_akun ?? '-'}} - {{ $item->nama_akun ?? '-' }}</td>
                    <td class="border-all text-right" style="mso-number-format:'\@';">{{ number_format($item->debit, 2, ',', '.') }}</td>
                    <td class="border-all text-right" style="mso-number-format:'\@';">{{ number_format($item->kredit, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="border-all text-right fw-bold">TOTAL</th>
                <th class="border-all text-right fw-bold" style="mso-number-format:'\@';">{{ number_format($totalDebit, 2, ',', '.') }}</th>
                <th class="border-all text-right fw-bold" style="mso-number-format:'\@';">{{ number_format($totalKredit, 2, ',', '.') }}</th>
            </tr>
            <tr><td colspan="7" style="height: 20px;"></td></tr>
            
            <tr>
                <td colspan="5"></td>
                <td class="border-all fw-bold text-right">Saldo Awal</td>
                <td class="border-all text-right" style="mso-number-format:'\@';">{{ number_format($saldo_awal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5"></td>
                <td class="border-all fw-bold text-right">Kas Masuk</td>
                <td class="border-all text-right" style="mso-number-format:'\@';">{{ number_format($kas_masuk, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5"></td>
                <td class="border-all fw-bold text-right">Kas Keluar</td>
                <td class="border-all text-right" style="mso-number-format:'\@';">{{ number_format($kas_keluar, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5"></td>
                <td class="border-all fw-bold text-right">Saldo Akhir</td>
                <td class="border-all text-right" style="mso-number-format:'\@';">{{ number_format($saldo_akhir, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>