<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Monitoring KPI - {{ $karyawan->nama_lengkap ?? '-' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8pt;
            color: #1F2937;
            background: #fff;
        }

        /* ── Header ─────────────────────────────────── */
        .doc-header {
            background: #2F5496;
            color: #fff;
            padding: 10px 14px;
            border-radius: 4px 4px 0 0;
            margin-bottom: 2px;
        }

        .doc-header h1 {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .doc-header p {
            font-size: 7.5pt;
            opacity: .9;
        }

        .info-bar {
            background: #EBF3FB;
            border: 1px solid #4472C4;
            padding: 5px 14px;
            margin-bottom: 8px;
            font-size: 7.5pt;
        }

        .info-bar span {
            margin-right: 16px;
        }

        .info-bar strong {
            color: #2F5496;
        }

        /* ── Section ─────────────────────────────────── */
        .section-title {
            background: #8EA9DB;
            color: #fff;
            font-size: 8.5pt;
            font-weight: bold;
            padding: 4px 10px;
            margin-top: 8px;
            margin-bottom: 0;
            border-radius: 3px 3px 0 0;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ── Tabel ────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        thead tr th {
            background: #2F5496;
            color: #fff;
            padding: 4px 5px;
            text-align: center;
            border: 1px solid #1B3A7A;
            font-weight: bold;
        }

        tbody tr:nth-child(odd) td {
            background: #DCE6F1;
        }

        tbody tr:nth-child(even) td {
            background: #fff;
        }

        tbody tr td {
            padding: 3.5px 5px;
            border: 1px solid #CCC;
            vertical-align: middle;
        }

        tfoot tr td {
            background: #D9E1F2;
            font-weight: bold;
            padding: 4px 5px;
            border: 1.5px solid #4472C4;
            text-align: center;
        }

        /* ── Progress bar ───────────────────────────── */
        .pb-wrap {
            background: #e9ecef;
            border-radius: 4px;
            height: 9px;
            width: 100%;
        }

        .pb-fill {
            height: 9px;
            border-radius: 4px;
        }

        .pb-selesai {
            background: #28a745;
        }

        .pb-progress {
            background: #ffc107;
        }

        .pb-gagal {
            background: #dc3545;
        }

        .pb-belum {
            background: #6c757d;
        }

        /* ── Status text ────────────────────────────── */
        .s-selesai {
            color: #155724;
            font-weight: bold;
        }

        .s-progress {
            color: #856404;
            font-weight: bold;
        }

        .s-gagal {
            color: #721c24;
            font-weight: bold;
        }

        .s-belum {
            color: #383d41;
            font-weight: bold;
        }

        /* ── Alignment ──────────────────────────────── */
        .tc {
            text-align: center;
        }

        .tr {
            text-align: right;
        }

        .tl {
            text-align: left;
        }

        /* ── Layout 2 kolom ─────────────────────────── */
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }

        .two-col td {
            vertical-align: top;
            padding: 0;
        }

        .col-l {
            width: 49%;
            padding-right: 5px;
        }

        .col-r {
            width: 49%;
            padding-left: 5px;
        }

        /* ── Page break ─────────────────────────────── */
        .page-break {
            page-break-after: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        /* ── Footer ─────────────────────────────────── */
        .footer {
            margin-top: 14px;
            border-top: 1px solid #4472C4;
            padding-top: 5px;
            font-size: 6.5pt;
            color: #6B7280;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- HEADER                                              --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="doc-header">
        <h1>Monitoring KPI — {{ $karyawan->nama_lengkap ?? '-' }}</h1>
        <p>Jabatan: {{ $karyawan->jabatan ?? '-' }} &nbsp;|&nbsp; Divisi: {{ $karyawan->divisi ?? '-' }} &nbsp;|&nbsp;
            Periode: Tahun {{ $tahun }}</p>
    </div>
    <div class="info-bar">
        <span><strong>Nama:</strong> {{ $karyawan->nama_lengkap ?? '-' }}</span>
        <span><strong>Jabatan:</strong> {{ $karyawan->jabatan ?? '-' }}</span>
        <span><strong>Divisi:</strong> {{ $karyawan->divisi ?? '-' }}</span>
        <span><strong>Tahun:</strong> {{ $tahun }}</span>
        <span><strong>Dicetak:</strong> {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- TABEL DAFTAR TARGET KPI                             --}}
    {{-- (Kolom identik dengan index blade)                  --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="section-title">Daftar Target KPI — Tahun {{ $tahun }}</div>
    <div class="avoid-break">
        <table>
            <thead>
                <tr>
                    <th style="width:2%">No</th>
                    <th style="width:26%" class="tl">Judul KPI</th>
                    <th style="width:8%">Jangka</th>
                    <th style="width:10%">Status</th>
                    <th style="width:12%">Target</th>
                    <th style="width:8%">Jabatan</th>
                    <th style="width:8%">Divisi</th>
                    <th style="width:8%">Pembuat</th>
                    <th style="width:12%">Progress</th>
                    <th style="width:6%">Tenggat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tabel_target as $idx => $t)
                    @php
                        $statusClass = match ($t['status']) {
                            'Selesai' => 's-selesai',
                            'Gagal' => 's-gagal',
                            'Belum Dimulai' => 's-belum',
                            default => 's-progress',
                        };
                        $pbClass = match ($t['status']) {
                            'Selesai' => 'pb-selesai',
                            'Gagal' => 'pb-gagal',
                            'Belum Dimulai' => 'pb-belum',
                            default => 'pb-progress',
                        };
                    @endphp
                    <tr>
                        <td class="tc">{{ $idx + 1 }}</td>
                        <td class="tl" style="font-weight:600;">{{ $t['judul'] }}</td>
                        <td class="tc">{{ $t['jangka_target'] }}</td>
                        <td class="tc">
                            <span class="{{ $statusClass }}">{{ $t['status'] }}</span>
                        </td>
                        <td class="tc">{{ $t['nilai_target_fmt'] }}</td>
                        <td class="tc">{{ $t['jabatan'] }}</td>
                        <td class="tc">{{ $t['divisi'] }}</td>
                        <td class="tl">{{ $t['pembuat'] }}</td>
                        <td>
                            {{-- Progress bar + display value (sama dengan index blade) --}}
                            <div class="pb-wrap">
                                <div class="pb-fill {{ $pbClass }}" style="width:{{ $t['length_progress'] }}%">
                                </div>
                            </div>
                            <small class="tc" style="display:block;font-size:6.5pt;margin-top:1px;">
                                {{ $t['progress_display'] }}
                            </small>
                        </td>
                        <td class="tc" style="font-size:6.5pt;">{{ $t['tenggat_waktu'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="tc" style="color:#888;font-style:italic;padding:8px;">
                            Tidak ada data KPI.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- REKAP BULANAN + INDIKATOR KEBERHASILAN              --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="doc-header">
        <h1>Rekap & Analisa KPI — {{ $karyawan->nama_lengkap ?? '-' }} ({{ $tahun }})</h1>
    </div>
    <div style="height:6px;"></div>

    <table class="two-col" style="border:none;">
        <tr>
            {{-- Kiri: Rekap Bulanan --}}
            <td class="col-l">
                <div class="section-title">Rekap Bulanan</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:8%">No</th>
                            <th style="width:44%" class="tl">Bulan</th>
                            <th style="width:28%">% Capaian</th>
                            <th style="width:20%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rekap_bulanan as $idx => $rekap)
                            @php
                                $persen = $rekap['persen_capaian'];
                                $cls =
                                    $persen >= 80
                                        ? 's-selesai'
                                        : ($persen >= 40
                                            ? 's-progress'
                                            : ($persen > 0
                                                ? 's-gagal'
                                                : 's-belum'));
                            @endphp
                            <tr>
                                <td class="tc">{{ $idx + 1 }}</td>
                                <td class="tl">{{ $rekap['nama_bulan'] }}</td>
                                <td class="tc">
                                    @if ($persen > 0)
                                        <span class="{{ $cls }}">{{ $persen }}%</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="tc">{{ $rekap['status'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="tl" style="padding-left:10px;">TOTAL</td>
                            <td>{{ $total_kumulatif }}%</td>
                            <td>-</td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            {{-- Kanan: Indikator + Penilaian --}}
            <td class="col-r">
                <div class="section-title">Indikator Keberhasilan</div>
                <table style="margin-bottom:8px;">
                    <thead>
                        <tr>
                            <th style="width:30%">Kategori</th>
                            <th class="tl">Keterangan</th>
                            <th style="width:20%">Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tc">Softskill/360</td>
                            <td class="tl">Penilaian 360 (softskill)</td>
                            <td class="tc">40%</td>
                        </tr>
                        <tr>
                            <td class="tc">KPI</td>
                            <td class="tl">Total pencapaian KPI</td>
                            <td class="tc">60%</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>TOTAL</td>
                            <td></td>
                            <td>100%</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="section-title">Tabel Penilaian</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:32%">Kategori</th>
                            <th>Total Capaian (Actual)</th>
                            <th>Capaian × Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tc">Softskill/360</td>
                            <td class="tc">
                                {{ $penilaian['nilai_softskill'] > 0 ? $penilaian['nilai_softskill'] . '%' : '-' }}
                            </td>
                            <td class="tc">{{ $penilaian['softskill_x_bobot'] }}</td>
                        </tr>
                        <tr>
                            <td class="tc">KPI</td>
                            <td class="tc">{{ $penilaian['total_capaian_kpi'] }}%</td>
                            <td class="tc">{{ $penilaian['kpi_x_bobot'] }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>TOTAL</td>
                            <td>100%</td>
                            <td>{{ $penilaian['total_akhir'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- ANALISA PENGAMBILAN TARGET                          --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="section-title">Analisa Pengambilan Target (Tahun {{ $tahun }})</div>
    <table>
        <thead>
            <tr>
                <th style="width:18%">Target 1 Tahun</th>
                <th style="width:20%">Actual Per Bulan</th>
                <th style="width:14%">Bulan</th>
                <th style="width:14%">% Bulan</th>
                <th style="width:14%">% Kumulatif</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($analisa_data as $idx => $analisa)
                @php
                    $targetDisp =
                        $analisa['target_tahunan'] > 0
                            ? 'Rp ' . number_format($analisa['target_tahunan'], 0, ',', '.')
                            : '-';
                    $actualDisp =
                        $analisa['actual_rupiah'] > 0
                            ? 'Rp ' . number_format($analisa['actual_rupiah'], 0, ',', '.')
                            : '-';
                @endphp
                <tr>
                    <td class="tr">{{ $targetDisp }}</td>
                    <td class="tr">{{ $actualDisp }}</td>
                    <td class="tl">{{ $analisa['nama_bulan'] }}</td>
                    <td class="tc">{{ $analisa['persen_bulan'] > 0 ? $analisa['persen_bulan'] . '%' : '-' }}</td>
                    <td class="tc">{{ $analisa['kumulatif'] > 0 ? $analisa['kumulatif'] . '%' : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="tr">
                    {{ $nilai_target_tahunan > 0 ? 'Rp ' . number_format($nilai_target_tahunan, 0, ',', '.') : '-' }}
                </td>
                <td class="tr">
                    {{ $total_actual_rupiah > 0 ? 'Rp ' . number_format($total_actual_rupiah, 0, ',', '.') : '-' }}
                </td>
                <td>-</td>
                <td>-</td>
                <td>{{ $total_kumulatif }}%</td>
            </tr>
        </tfoot>
    </table>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- FOOTER                                              --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="footer">
        Dokumen ini dicetak otomatis oleh Sistem Monitoring KPI &nbsp;|&nbsp;
        {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} WIB &nbsp;|&nbsp;
        {{ $karyawan->nama_lengkap ?? '-' }} — {{ $tahun }}
    </div>

</body>

</html>
