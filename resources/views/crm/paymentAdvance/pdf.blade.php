<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Payment Advanced - {{ $nama }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 15mm; }

        * { box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #000; margin: 0; }

        table { width: 100%; border-collapse: collapse; }
        .text-end    { text-align: right; }
        .text-center { text-align: center; }
        .coret       { text-decoration: line-through; }
        .mt          { margin-top: 12px; }

        /* Kop */
        .kop td { vertical-align: top; }
        .kop .logo img { height: 90px; width: auto; }
        .kop .tgl { text-align: right; padding-top: 4px; }

        .judul { text-align: center; font-size: 15px; font-weight: bold; letter-spacing: .5px; margin: 2px 0 12px; }

        /* Identitas */
        .ident td { padding: 2px 0; vertical-align: top; }
        .ident .lbl { width: 62px; }
        .ident .sep { width: 10px; }

        /* Project + Materi / Tanggal Pelatihan / Instansi: 1 tabel, 60%, kiri */
        .info { width: 60%; margin-top: 8px; table-layout: fixed; }
        .info td { border: 1px solid #000; padding: 5px 6px; vertical-align: middle; text-align: center; }
        .info .project-label { width: 100px; font-weight: bold; white-space: nowrap; }
        .info .info-label { font-weight: bold; }

        /* Description / Amount */
        .desc th, .desc td { border: 1px solid #000; padding: 6px 8px; }
        .desc th { text-align: center; font-weight: normal; background: #fafafa; }
        .desc td { height: 26px; vertical-align: middle; }
        .desc tr.total td { font-weight: bold; }
        .desc tr.grand td { font-weight: bold; background: #eeeeee; }

        .note    { margin-top: 6px; font-size: 10px; }
        .catatan { margin-top: 8px; font-size: 10px; }

        /* Approval & Finance */
        .pa-bawah { margin-top: 26px; page-break-inside: avoid; }
        .pa-bawah > tbody > tr > td { vertical-align: top; }
        .pa-judul-kecil { font-size: 11px; margin-bottom: 3px; }
        .pa-apv td, .pa-fin td { border: 1px solid #000; height: 50px; padding: 4px 6px; font-size: 11px; }
        .pa-apv td.lbl { width: 62%; }
        .pa-apv td.lbl span { margin-left: 6px; }
        .pa-apv td.ttd { width: 38%; text-align: center; }
        .ttd-img { max-height: 42px; max-width: 100%; }
        .stamp { font-weight: bold; font-size: 10px; }
    </style>
</head>
<body>

    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.') . ',-';

        // Persen tanpa nol di belakang koma: 10,00 -> 10
        $pct = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');

        // Baris ringkasan di bawah item: [label, nominal, class]
        $summary = [
            ['Total',                         $total, 'total'],
            ['PPN ' . $pct($ppnPersen) . '%', $ppn,   'total'],
        ];

        // PPH hanya dicetak kalau diisi
        if ($pphPersen > 0) {
            $summary[] = ['PPH ' . $pct($pphPersen) . '%', $pph, 'total'];
        }

        $summary[] = ['Total Keseluruhan', $totalKeseluruhan, 'grand'];

        // Level tertinggi yang sudah disetujui: 1 = SPV, 2 = GM, 3 = Selesai
        $ok    = $approved->where('status', 1);
        $level = (int) ($ok->max(fn ($a) => (int) $a->level_status) ?? 0);

        // Cari file ttd di disk public (storage/app/public). Kolom ttd bisa berisi nama file atau path.
        // Gambar dinormalkan lewat GD (diperkecil maksimal 400px, jadi PNG) lalu dikirim sebagai data URI,
        // supaya aman untuk DomPDF apa pun format asli filenya. Null kalau file tidak ada atau tidak terbaca.
        $ttdFile = function ($f) {
            if (!$f) return null;

            foreach ([$f, 'ttd/' . $f, 'ttd/' . basename($f)] as $p) {
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($p)) continue;

                $src = @imagecreatefromstring(\Illuminate\Support\Facades\Storage::disk('public')->get($p));
                if (!$src) return null;   // format tidak terbaca GD

                $w = imagesx($src);
                $h = imagesy($src);
                $scale = min(1, 400 / max($w, $h));
                $nw = max(1, (int) round($w * $scale));
                $nh = max(1, (int) round($h * $scale));

                // Latar transparan dipertahankan
                $dst = imagecreatetruecolor($nw, $nh);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 255, 255, 255, 127));
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

                ob_start();
                imagepng($dst);
                $png = ob_get_clean();

                return 'data:image/png;base64,' . base64_encode($png);
            }

            return null;
        };

        // Penandatangan. 'stamp' = teks cadangan kalau ttd tidak ada.
        $signers = [
            ['label' => 'Requested by',    'name' => $salesName,                'ttd' => $ttdFile($requester?->ttd), 'show' => true,        'stamp' => 'Requested'],
            ['label' => 'Manager',         'name' => $manager?->nama_lengkap,   'ttd' => $ttdFile($manager?->ttd),   'show' => $level >= 1, 'stamp' => 'Approved'],
            ['label' => 'General Manager', 'name' => $gm?->nama_lengkap,        'ttd' => $ttdFile($gm?->ttd),        'show' => $level >= 2, 'stamp' => 'Approved'],
            ['label' => 'Director',        'name' => $director?->nama_lengkap,  'ttd' => $ttdFile($director?->ttd),  'show' => $level >= 2, 'stamp' => 'Approved'],
        ];

        // Finance & Accounting: terisi setelah Selesai (level 3)
        $finTtd = $level >= 3 ? $ttdFile($finance?->ttd) : null;
    @endphp

    {{-- Kop --}}
    <table class="kop">
        <tr>
            <td class="logo"><img src="{{ $logo }}" alt="Inixindo Bandung"></td>
            <td class="tgl">DATE : {{ $tglPa }}</td>
        </tr>
    </table>

    <div class="judul">PAYMENT ADVANCED</div>

    {{-- Identitas --}}
    <table class="ident">
        <tr><td class="lbl">NAME</td><td class="sep">:</td><td>{{ $nama }}</td></tr>
        <tr><td class="lbl">DIVISION</td><td class="sep">:</td><td>{{ $divisi }}</td></tr>
    </table>

    {{-- Project + Materi / Tanggal Pelatihan / Instansi --}}
    <table class="info">
        <tr>
            <td class="project-label">Project</td>
            <td colspan="2">{{ $project }}</td>
        </tr>
        <tr>
            <td class="info-label">Materi</td>
            <td class="info-label">Tanggal Pelatihan</td>
            <td class="info-label">Instansi</td>
        </tr>
        <tr>
            <td>{{ $materi }}</td>
            <td>{{ $tglKelas }}</td>
            <td>{{ $instansi }}</td>
        </tr>
    </table>

    {{-- Description / Amount --}}
    <table class="desc mt">
        <thead>
            <tr>
                <th style="width:36%">DESCRIPTION (Jenis Pengajuan/PPN/PPH)</th>
                <th style="width:7%">QTY</th>
                <th style="width:19%">AMOUNT</th>
                <th style="width:19%">TYPE</th>
                <th style="width:19%">DATE REQUIRED</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $it)
                @php $type = $it['type'] ?? 'Non'; @endphp
                <tr>
                    <td>{{ $it['description'] }}</td>
                    <td class="text-center">{{ $it['qty'] }}</td>
                    <td class="text-end">{{ $it['amount'] > 0 ? $rp($it['amount']) : '' }}</td>
                    <td class="text-center">
                        {{-- Coret yang tidak dipakai. Non = tidak ada coretan sama sekali --}}
                        <span class="{{ $type === 'Cash' ? 'coret' : '' }}">Transfer</span> /
                        <span class="{{ $type === 'Transfer' ? 'coret' : '' }}">Cash</span>
                    </td>
                    <td class="text-center">{{ $it['date_required'] }}</td>
                </tr>
            @endforeach

            @foreach ($summary as [$label, $nilai, $cls])
                <tr class="{{ $cls }}">
                    <td>{{ $label }}</td>
                    <td></td>
                    <td class="text-end">{{ $rp($nilai) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="note">Note: Please cross out the payment type that is not applicable.</div>

    @if (trim($catatan) !== '')
        <div class="catatan"><strong>Catatan:</strong> {!! nl2br(e($catatan)) !!}</div>
    @endif

    {{-- Approvals & Finance --}}
    <table class="pa-bawah">
        <tr>
            <td style="width:54%">
                <div class="pa-judul-kecil">APPROVALS</div>
                <table class="pa-apv">
                    @foreach ($signers as $s)
                        <tr>
                            <td class="lbl">{{ $s['label'] }} : <span>{{ $s['show'] ? mb_convert_case($s['name'] ?? '', MB_CASE_TITLE, 'UTF-8') : '' }}</span></td>
                            <td class="ttd">
                                @if ($s['show'])
                                    @if ($s['ttd'])
                                        <img class="ttd-img" src="{{ $s['ttd'] }}" alt="TTD">
                                    @elseif ($s['stamp'])
                                        <div class="stamp">{{ $s['stamp'] }}</div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>

            <td style="width:6%"></td>

            <td style="width:40%">
                <div class="pa-judul-kecil">For Finance &amp; Administration only</div>
                <table class="pa-fin">
                    <tr>
                        <td>Status :
                            @if ($level >= 3)
                                Lengkap
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Verified by :
                            @if ($level >= 3)@endif
                            @if ($finTtd)
                                <div><img class="ttd-img" src="{{ $finTtd }}" alt="TTD"></div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>