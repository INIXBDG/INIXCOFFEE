<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; margin: 0; padding: 0; }
        .container { max-width: 190mm; margin: 0 auto; padding: 5mm; }

        .header { width: 100%; margin-bottom: 5mm; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .logo img { width: 200px; height: auto; }
        .office-info { text-align: right; font-size: 10pt; line-height: 12pt; }

        .headertext { text-decoration: underline; font-weight: bold; font-size: 14pt; margin: 3mm 0; text-align: center; }

        .employee-table { border-collapse: collapse; width: 100%; margin: 3mm 0; }
        .employee-table th, .employee-table td { border: 1px solid #ccc; padding: 4pt 6pt; text-align: left; font-size: 10pt; }
        .employee-table th { background-color: #f2f2f2; width: 25%; }

        .combined-table { width: 100%; margin: 3mm 0; border-collapse: collapse; font-size: 10pt; }
        .combined-table th, .combined-table td { padding: 6pt 8pt; text-align: left; border: 1px solid #eee; }
        .pendapatan-header { background-color: #006A67; color: #fff; font-weight: bold; }
        .potongan-header { background-color: #FF2929; color: #fff; font-weight: bold; }
        .combined-table thead tr.sub-header th { background-color: #f7f7f7; font-weight: bold; }
        .combined-table tbody tr { background-color: #fafafa; }

        .take-home-pay { margin-top: 5mm; padding: 8pt; background-color: #e6f3fa; text-align: right; font-weight: bold; font-size: 11pt; }

        .statement { font-size: 10pt; margin: 4mm 0 0 0; }
        .statement .tanggal { text-align: right; }

        /* Tanda tangan pakai TABLE, bukan flexbox, biar konsisten render di DomPDF */
        .signature-section { width: 100%; border-collapse: collapse; margin-top: 8mm; }
        .signature-section td { width: 50%; text-align: center; vertical-align: top; padding-top: 6mm; }
        .signature-section img { width: 70pt; height: auto; }
        .signature-section .name { margin-top: 8mm; padding-top: 1mm; border-top: 1px solid #000; display: inline-block; min-width: 60mm; font-size: 10pt; }
        .signature-section .position { font-size: 9pt; color: #555; }

        @page { size: A4; margin: 5mm; }
    </style>
</head>
<body>
    <div class="container">
        <table class="header">
            <tr>
                <td class="logo">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo">
                    @endif
                </td>
                <td class="office-info">
                    <p style="font-weight:bold;">INIXINDO BANDUNG</p>
                    <p>Jl Cipaganti No 95 Bandung</p>
                    <p>Telepon : 022 2032 831</p>
                    <p>www.inixindobdg.co.id</p>
                </td>
            </tr>
        </table>

        <div class="headertext">SLIP GAJI</div>

        <table class="employee-table">
            <thead><tr><th colspan="2">DETAIL KARYAWAN</th></tr></thead>
            <tbody>
                <tr><th>Nama Karyawan</th><td>{{ $user->karyawan->nama_lengkap }}</td></tr>
                <tr><th>Jabatan</th><td>{{ $user->karyawan->jabatan }}</td></tr>
                <tr><th>MayBank</th><td>{{ $user->karyawan->rekening_maybank }}</td></tr>
                <tr><th>BCA</th><td>{{ $user->karyawan->rekening_bca }}</td></tr>
                <tr><th>Periode Gaji</th><td>{{ $namaBulanText }} {{ $tahun }}</td></tr>
            </tbody>
        </table>

        <table class="combined-table">
            <thead>
                <tr>
                    <th colspan="3" class="pendapatan-header">Pendapatan</th>
                    <th colspan="3" class="potongan-header">Potongan</th>
                </tr>
                <tr class="sub-header">
                    <th style="width:5%">No</th>
                    <th style="width:30%">Pendapatan</th>
                    <th style="width:20%">Jumlah</th>
                    <th style="width:5%">No</th>
                    <th style="width:30%">Potongan</th>
                    <th style="width:20%">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr>
                    <td>{{ $row['p_no'] }}</td>
                    <td>{{ $row['p_nama'] }}</td>
                    <td>{{ $row['p_jumlah'] }}</td>
                    <td>{{ $row['pot_no'] }}</td>
                    <td>{{ $row['pot_nama'] }}</td>
                    <td>{{ $row['pot_jumlah'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="take-home-pay">
            TAKE HOME PAY: {{ $totalBersihFormatted ?? '' }}
        </div>

        <div class="statement">
            <p>Slip gaji ini bersifat rahasia dan hanya untuk keperluan karyawan.</p>
            <p class="tanggal">Bandung, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        </div>

        <table class="signature-section">
            <tr>
                <td>
                    @if($signUserBase64)
                        <img src="{{ $signUserBase64 }}" alt="Tanda Tangan Karyawan"><br>
                    @endif
                    <span class="name">{{ $user->karyawan->nama_lengkap }}</span><br>
                    <span class="position">{{ $user->karyawan->divisi }}</span>
                </td>
                <td>
                    @if($signHrdBase64)
                        <img src="{{ $signHrdBase64 }}" alt="Tanda Tangan HRD"><br>
                    @endif
                    <span class="name">{{ $HRD->karyawan->nama_lengkap }}</span><br>
                    <span class="position">{{ $HRD->karyawan->divisi }}</span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>