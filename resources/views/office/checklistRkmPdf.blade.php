<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Checklist Keperluan RKM</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            background-color: #667eea;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
        }

        .info-section {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 10px;
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 5px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            background: #667eea;
            color: white;
            padding: 6px;
            font-size: 10px;
            border: 1px solid #5568d3;
        }

        td {
            padding: 5px;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .kategori {
            background: #e9ecff;
            font-weight: bold;
        }

        .sub {
            padding-left: 15px;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: bold;
        }

        .yes {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .no {
            background: #ffebee;
            color: #c62828;
        }

        .progress {
            font-weight: bold;
            text-align: right;
        }

        .footer {
            margin-top: 15px;
            font-size: 9px;
            text-align: right;
            color: #666;
        }

        .container {
            padding: 0 25px; /* padding kiri kanan */
        }

        .day-card {
            margin-bottom: 20px; /* jarak antar hari */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #ffffff;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            padding: 6px 8px;
            background: #667eea;
            color: white;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="header">
            <h2>Checklist Keperluan RKM</h2>
        </div>

        <div class="info-section">
            <strong>Materi:</strong> {{ optional($rkm->materi)->nama_materi ?? '-' }} <br>
            <strong>Perusahaan:</strong> {{ optional($rkm->perusahaan)->nama_perusahaan ?? '-' }} <br>
            <strong>Tanggal Training:</strong>
            {{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M Y') }}
        </div>

        @php
            $checklists = \App\Models\ChecklistKeperluan::where('id_rkm', $rkm->id)
                ->with('subChecklistKeperluans')
                ->whereNotNull('tanggal_keperluan')
                ->orderBy('tanggal_keperluan', 'asc')
                ->get();
        @endphp

        @foreach ($checklists as $item)
            <div class="day-card">

                @php
                    $sub = $item->subChecklistKeperluans;

                    // ===== HITUNG PROGRESS =====
                    $progress = 0;

                    if ($rkm->metode_kelas === 'Offline') {
                        
                        $materi = ($sub?->materi_module ? 1 : 0) + ($sub?->materi_elearning ? 1 : 0);
                        $progress += ($materi / 2) * 20;
    
                        if ($item->kelas) $progress += 20;
    
                        $cb = ($sub?->cb_instruktur ? 1 : 0) + ($sub?->cb_peserta ? 1 : 0);
                        $progress += ($cb / 2) * 20;
    
                        $maksi = ($sub?->maksi_instruktur ? 1 : 0) + ($sub?->maksi_peserta ? 1 : 0);
                        $progress += ($maksi / 2) * 20;
    
                        $kelas = 
                            ($sub?->kelas_ac ? 1 : 0) +
                            ($sub?->kelas_jam ? 1 : 0) +
                            ($sub?->kelas_buku ? 1 : 0) +
                            ($sub?->kelas_pulpen ? 1 : 0) +
                            ($sub?->kelas_permen ? 1 : 0) +
                            ($sub?->kelas_camilan ? 1 : 0) +
                            ($sub?->kelas_minuman ? 1 : 0) +
                            ($sub?->kelas_lampu ? 1 : 0) +
                            ($sub?->kelas_kondisi_kebersihan ? 1 : 0);
    
                        $progress += ($kelas / 9) * 20;
    
                        $progress = round($progress);
                    } else {
                        $totalKategori = 3;
                        $kategoriSelesai = 0;

                        $totalMateri = 2;
                        $materiChecked =
                            ($sub?->materi_module ? 1 : 0) +
                            ($sub?->materi_elearning ? 1 : 0);
                        $kategoriSelesai += $materiChecked / $totalMateri;

                        $kategoriSelesai += ($sub?->cb_instruktur ? 1 : 0);
        
                        $kategoriSelesai += ($sub?->maksi_instruktur ? 1 : 0);

                        $progress = round(($kategoriSelesai / $totalKategori) * 100);
                    }
                @endphp

                <div class="section-title">
                    {{ \Carbon\Carbon::parse($item->tanggal_keperluan)->format('d M Y') }}
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Keperluan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- ===== MATERI ===== -->
                        <tr class="kategori">
                            <td colspan="2">Materi</td>
                        </tr>
                        <tr>
                            <td class="sub">Modul</td>
                            <td><span class="badge {{ $sub->materi_module ? 'yes' : 'no' }}">
                                {{ $sub->materi_module ? 'Tersedia' : 'Tidak Tersedia' }}
                            </span></td>
                        </tr>
                        <tr>
                            <td class="sub">E-Learning</td>
                            <td><span class="badge {{ $sub->materi_elearning ? 'yes' : 'no' }}">
                                {{ $sub->materi_elearning ? 'Tersedia' : 'Tidak Tersedia' }}
                            </span></td>
                        </tr>

                        <!-- ===== KELAS ===== -->
                        @if ($rkm->metode_kelas === 'Offline')
                            <tr class="kategori">
                                <td colspan="2">Kelas</td>
                            </tr>
                            <tr>
                                <td class="sub">Kelas</td>
                                <td><span class="badge {{ $item->kelas ? 'yes' : 'no' }}">
                                    {{ $item->kelas ? 'Tersedia' : 'Tidak Tersedia' }}
                                </span></td>
                            </tr>
                        @endif

                        <!-- ===== CB ===== -->
                        <tr class="kategori">
                            <td colspan="2">CB</td>
                        </tr>
                        <tr>
                            <td class="sub">Instruktur</td>
                            <td><span class="badge {{ $sub->cb_instruktur ? 'yes' : 'no' }}">
                                {{ $sub->cb_instruktur ? 'Tersedia' : 'Tidak Tersedia' }}
                            </span></td>
                        </tr>
                        @if ($rkm->metode_kelas === 'Offline')
                            <tr>
                                <td class="sub">Peserta</td>
                                <td><span class="badge {{ $sub->cb_peserta ? 'yes' : 'no' }}">
                                    {{ $sub->cb_peserta ? 'Tersedia' : 'Tidak Tersedia' }}
                                </span></td>
                            </tr>
                        @endif

                        <!-- ===== MAKSI ===== -->
                        <tr class="kategori">
                            <td colspan="2">Maksi</td>
                        </tr>
                        <tr>
                            <td class="sub">Instruktur</td>
                            <td><span class="badge {{ $sub->maksi_instruktur ? 'yes' : 'no' }}">
                                {{ $sub->maksi_instruktur ? 'Tersedia' : 'Tidak Tersedia' }}
                            </span></td>
                        </tr>
                        @if ($rkm->metode_kelas === 'Offline')
                            <tr>
                                <td class="sub">Peserta</td>
                                <td><span class="badge {{ $sub->maksi_peserta ? 'yes' : 'no' }}">
                                    {{ $sub->maksi_peserta ? 'Tersedia' : 'Tidak Tersedia' }}
                                </span></td>
                            </tr>
                        @endif

                        <!-- ===== KEPERLUAN KELAS ===== -->
                        @if ($rkm->metode_kelas === 'Offline')
                            <tr class="kategori">
                                <td colspan="2">Keperluan Kelas</td>
                            </tr>
                            <tr><td class="sub">AC</td><td><span class="badge {{ $sub->kelas_ac ? 'yes' : 'no' }}">{{ $sub->kelas_ac ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                            <tr><td class="sub">Jam</td><td><span class="badge {{ $sub->kelas_jam ? 'yes' : 'no' }}">{{ $sub->kelas_jam ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                            <tr><td class="sub">Buku</td><td><span class="badge {{ $sub->kelas_buku ? 'yes' : 'no' }}">{{ $sub->kelas_buku ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                            <tr><td class="sub">Pulpen</td><td><span class="badge {{ $sub->kelas_pulpen ? 'yes' : 'no' }}">{{ $sub->kelas_pulpen ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                            <tr><td class="sub">Permen</td><td><span class="badge {{ $sub->kelas_permen ? 'yes' : 'no' }}">{{ $sub->kelas_permen ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                            <tr><td class="sub">Camilan</td><td><span class="badge {{ $sub->kelas_camilan ? 'yes' : 'no' }}">{{ $sub->kelas_camilan ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                            <tr><td class="sub">Minuman</td><td><span class="badge {{ $sub->kelas_minuman ? 'yes' : 'no' }}">{{ $sub->kelas_minuman ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                            <tr><td class="sub">Lampu</td><td><span class="badge {{ $sub->kelas_lampu ? 'yes' : 'no' }}">{{ $sub->kelas_lampu ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                            <tr><td class="sub">Kondisi & Kebersihan Kelas</td><td><span class="badge {{ $sub->kelas_kondisi_kebersihan ? 'yes' : 'no' }}">{{ $sub->kelas_kondisi_kebersihan ? 'Tersedia' : 'Tidak Tersedia' }}</span></td></tr>
                        @endif

                        <!-- ===== PROGRESS ===== -->
                        <tr>
                            <td><strong>Progress</strong></td>
                            <td class="progress">{{ $progress }}%</td>
                        </tr>

                    </tbody>
                </table>

            </div>
        @endforeach

        <div class="footer">
            Dicetak:
            {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}
        </div>

    </div>
</body>
</html>