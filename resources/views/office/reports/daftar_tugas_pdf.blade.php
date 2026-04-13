<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Tugas Office Boy</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            margin: 15px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14px;
        }

        .header p {
            margin: 2px 0;
            font-size: 8px;
            font-style: italic;
        }

        .meta {
            margin-bottom: 10px;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background: #f5f5f5;
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #ccc;
            font-weight: bold;
            font-size: 8px;
        }

        td {
            padding: 5px 4px;
            border: 1px solid #ccc;
            vertical-align: top;
            font-size: 8px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status-selesai {
            background: #d4edda;
            color: #155724;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .badge-harian {
            background: #cce5ff;
            color: #004085;
        }

        .badge-mingguan {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-bulanan {
            background: #d4edda;
            color: #155724;
        }

        .badge-quartal {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-semester {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge-tahunan {
            background: #fff3cd;
            color: #856404;
        }

        .summary-box {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
        }

        .summary-box h4 {
            margin: 0 0 8px 0;
            font-size: 10px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 8px;
        }

        .page-break {
            page-break-before: always;
        }

        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-box .line {
            border-top: 1px solid #333;
            margin: 30px 0 3px;
            padding-top: 2px;
        }

        .signature-box p {
            margin: 2px 0;
            font-size: 8px;
        }

        .no-bukti {
            color: #999;
            font-style: italic;
        }

        .has-bukti {
            color: #198754;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN {{ $reportType === 'kategori' ? 'KATEGORI TUGAS' : 'PELAKSANAAN TUGAS' }} OFFICE BOY</h2>
        <p>Office Management System</p>
        <p>{{ $reportType === 'kategori' ? '' : 'Periode: ' . ($startDate ? \Carbon\Carbon::parse($startDate)->format('d F Y') : 'Awal') . ' s/d ' . ($endDate ? \Carbon\Carbon::parse($endDate)->format('d F Y') : 'Sekarang') }}
        </p>
        <p>Export: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }} | User:
            {{ auth()->user()->karyawan->nama_lengkap ?? auth()->user()->username }}</p>
    </div>

    @if ($reportType !== 'kategori')
        <div class="summary-box">
            <h4>📊 RINGKASAN EKSEKUSI</h4>
            <div class="summary-item"><span>Total Tugas:</span><strong>{{ $totalTugas }}</strong></div>
            <div class="summary-item"><span>✅ Selesai:</span><strong class="text-success">{{ $totalSelesai }}</strong>
            </div>
            <div class="summary-item"><span>⏳ Pending:</span><strong class="text-warning">{{ $totalPending }}</strong>
            </div>
            <div class="summary-item"><span>📈 Completion
                    Rate:</span><strong>{{ $totalTugas > 0 ? round(($totalSelesai / $totalTugas) * 100, 1) : 0 }}%</strong>
            </div>
        </div>
    @endif

    <table>
        @if ($reportType === 'kategori')
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Nama Tugas</th>
                    <th width="12%">Tipe</th>
                    <th width="12%">Dibuat</th>
                    <th width="11%">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($data as $item)
                    @php
                        $totalInstance = $item->kontrolTugas
                            ? $item->kontrolTugas->count()
                            : \App\Models\KontrolTugas::where('id_DaftarTugas', $item->id)->count();
                        $badgeClass = match ($item->Tipe) {
                            'Harian' => 'badge-harian',
                            'Mingguan' => 'badge-mingguan',
                            'Bulanan' => 'badge-bulanan',
                            'Quartal' => 'badge-quartal',
                            'Semester' => 'badge-semester',
                            'Tahunan' => 'badge-tahunan',
                            default => '',
                        };
                    @endphp
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td>{{ $item->judul_kategori }}</td>
                        <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $item->Tipe }}</span>
                        </td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $totalInstance }}</td>
                    </tr>
                @endforeach
            </tbody>
        @else
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Assign</th>
                    <th width="20%">Tugas</th>
                    <th width="10%">Tipe</th>
                    <th width="15%">Office Boy</th>
                    <th width="12%">Deadline</th>
                    <th width="10%">Status</th>
                    <th width="11%">Bukti</th>
                    <th width="15%">Selesai</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($data as $item)
                    @php
                        $statusClass = $item->status == 1 ? 'status-selesai' : 'status-pending';
                        $statusText = $item->status == 1 ? '✓ Selesai' : '⏳ Pending';
                        $badgeClass = match ($item->kategoriDaftarTugas?->Tipe) {
                            'Harian' => 'badge-harian',
                            'Mingguan' => 'badge-mingguan',
                            'Bulanan' => 'badge-bulanan',
                            'Quartal' => 'badge-quartal',
                            'Semester' => 'badge-semester',
                            'Tahunan' => 'badge-tahunan',
                            default => '',
                        };
                    @endphp
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m') }}</td>
                        <td>{{ $item->kategoriDaftarTugas?->judul_kategori ?? '-' }}</td>
                        <td class="text-center"><span
                                class="badge {{ $badgeClass }}">{{ $item->kategoriDaftarTugas?->Tipe ?? '-' }}</span>
                        </td>
                        <td>{{ $item->karyawan?->nama_lengkap ?? '-' }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->Deadline_Date)->format('d/m/Y') }}</td>
                        <td class="text-center"><span class="{{ $statusClass }}">{{ $statusText }}</span></td>
                        <td class="text-center">
                            @if ($item->bukti)
                                <span class="has-bukti">✓ Ada</span>
                            @else
                                <span class="no-bukti">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($item->status == 1 && $item->updated_at)
                                {{ \Carbon\Carbon::parse($item->updated_at)->format('d/m H:i') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        @endif
    </table>
</body>

</html>
