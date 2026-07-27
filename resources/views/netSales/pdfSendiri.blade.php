<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan RKM</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9pt;
            color: #2c3e50;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        .page {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .header {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 6px;
            margin-bottom: 8px;
            text-align: center;
        }

        .title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .section-title {
            background: #f1f5f9;
            padding: 4px 8px;
            font-weight: bold;
            margin-top: 8px;
            border-left: 3px solid #2c3e50;
            font-size: 9.5pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        table td,
        table th {
            padding: 3px 6px;
            border: 1px solid #dee2e6;
            vertical-align: top;
            font-size: 9pt;
        }

        .label {
            background: #f8f9fa;
            font-weight: 600;
            width: 22%;
        }

        .amount {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
        }

        .status-badge {
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
        }

        .status-hijau {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-merah {
            background: #ffebee;
            color: #c62828;
        }

        .footer {
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #eee;
        }

        .table-compact th,
        .table-compact td {
            padding: 2px 5px;
            font-size: 8pt;
        }

        .table-compact th {
            background: #f8f9fa;
            text-align: center;
        }

        .no-border {
            border: none !important;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <div class="title">Laporan Payment Advance {{ $rkm->id }}</div>
        </div>

        {{-- Informasi Utama + Detail Perusahaan (2 kolom sejajar) --}}
        <table class="no-border" style="margin-top: 4px;">
            <tr>
                <td class="no-border" style="width: 50%; padding: 0 6px 0 0; vertical-align: top;">
                    <div class="section-title">Informasi Utama</div>
                    <table>
                        <tr>
                            <td class="label">Materi</td>
                            <td><strong>{{ $rkm->materi->nama_materi ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Jadwal</td>
                            <td>
                                {{ \Carbon\Carbon::parse($rkm->tanggal_awal)->translatedFormat('d M Y') }}
                                s/d
                                {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->translatedFormat('d M Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Pax</td>
                            <td>{{ $rkm->pax }}</td>
                        </tr>
                        <tr>
                            <td class="label">Harga Jual</td>
                            <td>{{ number_format($rkm->harga_jual ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Total Jual</td>
                            <td>{{ number_format($rkm->pax * $rkm->harga_jual ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Sales</td>
                            <td>{{ $rkm->sales_key }}</td>
                        </tr>
                    </table>
                </td>
                <td class="no-border" style="width: 50%; padding: 0 0 0 6px; vertical-align: top;">
                    <div class="section-title">Detail Perusahaan</div>
                    <table>
                        <tr>
                            <td class="label">Nama</td>
                            <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Kategori/Lokasi</td>
                            <td>
                                {{ $rkm->perusahaan->kategori_perusahaan ?? '-' }} / {{ $rkm->perusahaan->lokasi ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Email</td>
                            <td>{{ $rkm->perusahaan->email ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    @php
        $pa = collect($rkm->perhitunganNetSales)->first();

        $totalAdvance = 0;
        if ($pa) {
            $totalAdvance =
                ($pa['transportasi'] ?? 0) +
                ($pa['akomodasi_peserta'] ?? 0) +
                ($pa['akomodasi_tim'] ?? 0) +
                ($pa['cashback'] ?? 0) +
                ($pa['fresh_money'] ?? 0) +
                ($pa['sewa_laptop'] ?? 0) +
                ($pa['souvenir'] ?? 0) +
                ($pa['entertaint'] ?? 0);
        }

        // Normalisasi level_status: romawi atau angka biasa -> selalu jadi '1' / '2' / '3'
        $normalizeLevel = function ($raw) {
            $map = [
                '1' => '1', 'I'   => '1',
                '2' => '2', 'II'  => '2',
                '3' => '3', 'III' => '3',
            ];
            return $map[strtoupper(trim($raw))] ?? $raw;
        };

        $allApprovals = collect($pa->approvedNetSales ?? [])
            ->sortBy('created_at')
            ->map(function ($item) use ($normalizeLevel) {
                $item->level_norm = $normalizeLevel($item->level_status);
                return $item;
            })
            ->values();

        // Level 1 = SPV Sales, Level 2 = GM -> ambil entry terakhir per level
        $approvalSpv = $allApprovals->where('level_norm', '1')->last();
        $approvalGm  = $allApprovals->where('level_norm', '2')->last();

        // Director otomatis dianggap approve begitu GM sudah approve
        $approvalDirut = null;
        if ($approvalGm) {
            $approvalDirut = new \stdClass();
            $approvalDirut->level_norm = '3';
            $approvalDirut->keterangan = 'Telah disetujui oleh Director';
            $approvalDirut->tanggal    = $approvalGm->tanggal;
            $approvalDirut->status     = 1;
        }

        $approvals = collect([$approvalSpv, $approvalGm, $approvalDirut])->filter()->values();
        
        $approvalUsers = [
            '1' => $spv ?? null,
            '2' => $gm ?? null,
            '3' => $dirut ?? null,
        ];

        $approvalLabels = [
            '1' => 'Telah disetujui oleh SPV Sales',
            '2' => 'Telah disetujui oleh General Manager',
            '3' => 'Telah disetujui oleh Director',
        ];
    @endphp

    <div class="section-title">Biaya Operasional (Advance)</div>
        @if ($pa)
            <table>
                <tr>
                    <td class="label">Transportasi</td>
                    <td class="amount">Rp {{ number_format($pa['transportasi'] ?? 0, 0, ',', '.') }}</td>
                    <td class="label">Akomodasi Peserta</td>
                    <td class="amount">Rp {{ number_format($pa['akomodasi_peserta'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Akomodasi Tim</td>
                    <td class="amount">Rp {{ number_format($pa['akomodasi_tim'] ?? 0, 0, ',', '.') }}</td>
                    <td class="label">Cashback</td>
                    <td class="amount">Rp {{ number_format($pa['cashback'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Fresh Money</td>
                    <td class="amount">Rp {{ number_format($pa['fresh_money'] ?? 0, 0, ',', '.') }}</td>
                    <td class="label">Sewa Laptop</td>
                    <td class="amount">Rp {{ number_format($pa['sewa_laptop'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Souvenir</td>
                    <td class="amount">Rp {{ number_format($pa['souvenir'] ?? 0, 0, ',', '.') }}</td>
                    <td class="label">Entertaint</td>
                    <td class="amount">Rp {{ number_format($pa['entertaint'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="background: #f1f5f9; font-weight: bold;">
                    <td class="label" colspan="3">Total Advance</td>
                    <td class="amount">Rp {{ number_format($totalAdvance, 0, ',', '.') }}</td>
                </tr>
            </table>

            <div style="margin-top: 6px;">
                <strong style="font-size: 9pt;">Keterangan / Catatan:</strong>
                <p style="border: 1px solid #dee2e6; padding: 5px; background: #fff; font-size: 8.5pt; margin-top: 3px; margin-bottom: 0;">
                    {{ $pa['deskripsi_tambahan'] ?? '-' }}
                </p>
            </div>

            {{-- Riwayat Persetujuan --}}
            <div class="section-title">Riwayat Persetujuan</div>
            <table class="table-compact">
                <thead>
                    <tr>
                        <th style="width:10%;">Level</th>
                        <th style="width:50%; text-align:left;">Keterangan</th>
                        <th style="width:20%;">Tanggal</th>
                        <th style="width:20%;">Approval</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center;">-</td>
                        <td>Requested By : {{ $rkm->sales->nama_lengkap ?? '-' }}</td>
                        <td style="text-align:center;">
                            {{ \Carbon\Carbon::parse($pa->tgl_pa)->translatedFormat('d M Y') }}
                        </td>
                        <td style="text-align:center;">
                            @if($rkm->sales->ttd ?? false)
                            <img src="{{ public_path('storage/ttd/' . $rkm->sales->ttd) }}"
                            alt="Tanda Tangan"
                            style="max-height:60px; max-width:120px;">
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @forelse ($approvals as $approval)
                        @php $ttdUser = $approvalUsers[$approval->level_norm] ?? null; @endphp
                        <tr>
                            <td style="text-align:center;">{{ $approval->level_norm }}</td>
                            <td>{{ $approvalLabels[$approval->level_norm] ?? $approval->keterangan }}</td>
                            <td style="text-align:center;">
                                {{ \Carbon\Carbon::parse($approval->tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td style="text-align:center;">
                                @if($ttdUser && $ttdUser->ttd)
                                    <img src="{{ public_path('storage/ttd/' . $ttdUser->ttd) }}"
                                        alt="Tanda Tangan"
                                        style="max-height:60px; max-width:120px; margin-top:2px;">
                                @else
                                    <span class="status-badge {{ $approval->status == 1 ? 'status-hijau' : 'status-merah' }}">
                                        {{ $approval->status == 1 ? 'Approved' : 'Ditolak' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; font-style:italic; color:#999;">
                                Belum ada riwayat persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Box kecil Finance & Administration, rata kanan --}}
            <table class="no-border table-compact" style="margin-top: 10px; margin-bottom: 15px;">
                <tr>
                    <td class="no-border" style="width: 60%;"></td>
                    <td class="no-border" style="width: 40%; padding: 0;">
                        <table style="border: 1px solid #2c3e50;">
                            <tr>
                                <td class="no-border" style="text-align:center; font-weight:bold; padding: 4px 6px; border-bottom: 1px solid #2c3e50;">
                                    For Finance &amp; Administration only
                                </td>
                            </tr>
                            <tr>
                                <td class="no-border" style="padding: 4px 6px;">
                                    Status :
                                </td>
                            </tr>
                            <tr>
                                <td class="no-border" style="padding: 4px 6px;">
                                    Verified by : 
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        @else
            <p style="text-align: center; font-style: italic; color: #999;">Data Payment Advance tidak tersedia.</p>
        @endif

        <div class="footer">
            RKM ID {{ $rkm->id }} | Dicetak: {{ date('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>