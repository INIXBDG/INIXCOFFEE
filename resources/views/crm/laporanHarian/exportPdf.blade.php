<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan MoM</title>

<style>

body{
    font-family: 'DejaVu Sans','Arial',sans-serif;
    font-size:12px;
    line-height:1.4;
    color:#333;
}

.container{
    width:100%;
    max-width:1000px;
    margin:0 auto;
    padding:15px;
}

.header{
    text-align:center;
    margin-bottom:20px;
    border-bottom:2px solid #2F80ED;
    padding-bottom:15px;
}

.header h1{
    margin:0;
    color:#2F80ED;
    font-size:20px;
}

.section{
    margin-top:20px;
}

.section-title{
    font-weight:bold;
    font-size:14px;
    margin-bottom:8px;
}

.info-table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.info-table td{
    padding:6px;
}

.summary{
    background:#f8f9fa;
    padding:15px;
    border-radius:6px;
    margin-bottom:20px;
    border-left:4px solid #2F80ED;
}

.summary table td{
    padding:6px;
    vertical-align:top;
}

.summary-item strong{
    display:block;
    font-size:11px;
    color:#666;
}

.summary-value{
    font-weight:bold;
    font-size:13px;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.table th{
    background-color:#2F80ED;
    color:white;
    padding:8px;
    text-align:center;
    border:1px solid #ddd;

    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.table tr:nth-child(even){
    background:#f8f9fa;
}

.table td{
    padding:7px;
    border:1px solid #ddd;
}

.text-center{
    text-align:center;
}

.text-start{
    text-align:left;
}

.footer{
    margin-top:30px;
    text-align:center;
    font-size:10px;
    color:#777;
}

</style>

</head>

<body>

<div class="container">

    <!-- Header -->
    <div class="header">
        <h1>Laporan Meeting (MoM)</h1>
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }}</p>
    </div>


    <!-- Ringkasan Meeting -->
    <div class="summary">

    <table width="100%">
    <tr>

    <td width="50%">
    <strong>Topik</strong><br>
    {{ $laporan->topic }}
    </td>

    <td width="50%">
    <strong>Jenis Meeting</strong><br>
    {{ $laporan->jenis_meeting }}
    </td>

    </tr>

    <tr>

    <td>
    <strong>Tanggal</strong><br>
    {{ \Carbon\Carbon::parse($laporan->tanggal_pelaksanaan)->format('d F Y') }}
    </td>

    <td>
    <strong>Waktu</strong><br>
    {{ substr($laporan->waktu_pelaksanaan,0,5) }}
    </td>

    </tr>

    <tr>

    <td>
    <strong>Pimpinan Meeting</strong><br>
    {{ $laporan->picMeeting->nama_lengkap ?? '-' }}
    </td>

    <td>
    <strong>Notulis</strong><br>
    {{ $laporan->notulisMeeting->nama_lengkap ?? '-' }}
    </td>

    </tr>

    <tr>

    <td>
    <strong>Peserta Hadir</strong><br>
    {{ $laporan->jumlah_peserta_hadir }}
    </td>

    <td>
    <strong>Tempat / Media</strong><br>
    {{ $laporan->tempat_or_media }}
    </td>

    </tr>

    <tr>

    <td>
    <strong>Peserta Tidak Hadir</strong><br>
    {{ $laporan->jumlah_peserta_tidak_hadir ?? 0 }}
    </td>

    @if ($laporan->jumlah_peserta_tidak_hadir > 0)

    <td colspan="2">
    <strong>Keterangan Peserta Tidak Hadir</strong><br>
    {{ $laporan->alasan_peserta_tidak_hadir ?? '-' }}
    </td>
    
    @endif
    
    </tr>

    </table>

    </div>


    <!-- Catatan Meeting -->
    @if($laporan->catatan)
    <div class="section">
        <div class="section-title">Catatan Meeting</div>

        <table class="info-table">
            <tr>
                <td>{{ $laporan->catatan }}</td>
            </tr>
        </table>
    </div>
    @endif


    {{-- Catatan untuk sales --}}
    @if($type == 'sales')

        <div class="section">

            <div class="section-title">
                Catatan Untuk Sales
            </div>

            <table class="table">

                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th width="200">Sales</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($laporan->catatanSales as $item)

                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $item->sales->nama_lengkap ?? '-' }}</td>
                            <td class="text-start">{{ $item->catatan }}</td>
                        </tr>

                        @empty

                        <tr>
                            <td colspan="3" class="text-center">
                                Tidak ada catatan sales
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    @endif



    <!-- Catatan Untuk Client -->
    @if($type == 'client')

    <div class="section">

        <div class="section-title">
            Catatan Untuk Client
        </div>

        <table class="table">

            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Perusahaan</th>
                    <th>Kebutuhan</th>
                    <th>Rekomendasi Silabus</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>

                @forelse($laporan->catatanClient as $item)

                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->nama_perusahaan }}</td>
                        <td>{{ $item->kebutuhan }}</td>
                        <td>{{ $item->rekomendasi_silabus }}</td>
                        <td>{{ $item->catatan }}</td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="5" class="text-center">
                            Tidak ada catatan client
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @endif



    <div class="footer">
        Laporan ini dihasilkan secara otomatis dari sistem CRM.
    </div>

</div>

</body>
</html>