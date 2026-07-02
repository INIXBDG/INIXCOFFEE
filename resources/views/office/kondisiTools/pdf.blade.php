<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4472C4;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20px;
            color: #4472C4;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 14px;
            color: #666;
            font-weight: normal;
            margin-bottom: 10px;
        }
        
        .header .date {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
        }
        
        .filter-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 20px;
        }
        
        .filter-info h3 {
            font-size: 12px;
            color: #4472C4;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .filter-info .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
            font-size: 10px;
        }
        
        .filter-info .filter-item strong {
            color: #333;
        }
        
        .summary {
            background-color: #e7f3ff;
            border-left: 4px solid #4472C4;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        
        .summary p {
            font-size: 11px;
            margin: 3px 0;
        }
        
        .summary strong {
            color: #4472C4;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        
        thead {
            background-color: #4472C4;
            color: white;
        }
        
        thead th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #365f9e;
        }
        
        tbody tr {
            border-bottom: 1px solid #dee2e6;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tbody td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        
        .badge-baik {
            background-color: #28a745;
        }
        
        .badge-rusak-ringan {
            background-color: #ffc107;
            color: #333;
        }
        
        .badge-rusak-berat {
            background-color: #dc3545;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $title }}</h1>
        <h2>Sistem Manajemen Kondisi Tools</h2>
        <div class="date">
            Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB
        </div>
    </div>

    {{-- Filter Information --}}
    @if($hasFilter)
    <div class="filter-info">
        <h3>📋 Filter yang Diterapkan:</h3>
        @if(!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_selesai']))
            <div class="filter-item">
                <strong>Periode:</strong> 
                @if(!empty($filters['tanggal_mulai']))
                    {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d/m/Y') }}
                @endif
                @if(!empty($filters['tanggal_mulai']) && !empty($filters['tanggal_selesai']))
                    - 
                @endif
                @if(!empty($filters['tanggal_selesai']))
                    {{ \Carbon\Carbon::parse($filters['tanggal_selesai'])->format('d/m/Y') }}
                @endif
            </div>
        @endif
        
        @if(!empty($filters['kategori']))
            <div class="filter-item">
                <strong>Kategori:</strong> {{ $filters['kategori'] }}
            </div>
        @endif
        
        @if(!empty($filters['kondisi']))
            <div class="filter-item">
                <strong>Kondisi:</strong> {{ $filters['kondisi'] }}
            </div>
        @endif
        
        @if(!empty($filters['nama_alat']))
            <div class="filter-item">
                <strong>Alat:</strong> {{ $filters['nama_alat'] }}
            </div>
        @endif
    </div>
    @endif

    {{-- Summary --}}
    <div class="summary">
        <p><strong>Total Data:</strong> {{ $totalData }} record</p>
        @if($reportType === 'alat')
            <p><strong>Total Quantity:</strong> {{ $totalQty }} unit</p>
        @endif
    </div>

    {{-- Data Table --}}
    @if($reportType === 'alat')
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="30%">Nama Alat</th>
                    <th width="20%">Kategori</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="35%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->nama_alat }}</td>
                        <td>{{ $item->kategori }}</td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td>-</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-data">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($reportType === 'pemeriksaan')
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="20%">Nama Alat</th>
                    <th width="15%">Kategori</th>
                    <th width="15%">Kondisi</th>
                    <th width="30%">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_pemeriksaan)->format('d/m/Y') }}</td>
                        <td>{{ $item->alat->nama_alat ?? '-' }}</td>
                        <td>{{ $item->alat->kategori ?? '-' }}</td>
                        <td>
                            @php
                                $badgeClass = 'badge-baik';
                                if($item->kondisi === 'Rusak Ringan') $badgeClass = 'badge-rusak-ringan';
                                if($item->kondisi === 'Rusak Berat') $badgeClass = 'badge-rusak-berat';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $item->kondisi }}</span>
                        </td>
                        <td>{{ $item->catatan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">Tidak ada data yang tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>