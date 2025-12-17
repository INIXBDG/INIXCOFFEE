<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        /* FONT GLOBAL 10px */
        body,
        table {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .valign-top {
            vertical-align: top;
        }

        .valign-mid {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <table width="100%">
        {{-- ================= META HEADER ================= --}}
        <tr>
            <td></td>
            <td style="border: 1px solid #000000; font-weight: bold;">To</td>
            <td style="border: 1px solid #000000;">: Mba Yana</td>
        </tr>
        <tr>
            <td></td>
            <td style="border: 1px solid #000000; font-weight: bold;">Subject</td>
            <td style="border: 1px solid #000000;">: Data Peserta</td>
        </tr>
        <tr>
            <td></td>
            <td style="border: 1px solid #000000; font-weight: bold;">From</td>
            <td style="border: 1px solid #000000;">: {{ explode(' ', $ttd->nama_lengkap)[0] }} Inixindo Bandung</td>
        </tr>

        <tr>
            <td colspan="6" height="20"></td>
        </tr>

        {{-- ================= LOGO & JUDUL ================= --}}
        <tr>
            <td colspan="6"
                style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 10px; text-decoration: underline; height: 15;">
                FORMULIR DATA PESERTA
            </td>
        </tr>

        <tr>
            <td colspan="6" height="15"></td>
        </tr>
        <tr>
            <td colspan="6" height="15"></td>
        </tr>
        <tr>
            <td colspan="6" height="15"></td>
        </tr>

        {{-- ================= INFO SECTION ================= --}}

        <tr>
            <td colspan="2" class="valign-top text-left">
                <span class="bold">Kepada/UP &nbsp;&nbsp;&nbsp;</span> : Holding
            </td>

            <td colspan="2" class="valign-top text-left">
                <span class="bold">Telp/email</span> : 021-57940868
            </td>

            <td colspan="2" class="valign-top text-left">
                <span class="bold">Tanggal Pesan</span> :
                {{ \Carbon\Carbon::parse($no->created_at)->translatedFormat('d F Y') }}
            </td>
        </tr>

        <tr>
            <td colspan="2" class="valign-top text-left">
                <span class="bold">Dari/UP &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> : Inixindo Bandung
            </td>

            <td colspan="2" class="valign-top text-left">
                <span class="bold">Telp/email</span> : 022-2032831
            </td>

            <td colspan="2" class="valign-top text-left">
                <span class="bold">Nomor PO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> :
                {{ $no->no_modul ?? '-' }}
            </td>
        </tr>

        <tr>
            <td colspan="6" height="20"></td>
        </tr>

        {{-- ================= TABEL DATA ================= --}}
        <thead>
            <tr>
                <th
                    style="border: 1px solid #000000; background-color: #e0e0e0; font-weight: bold; text-align: center; vertical-align: middle;">
                    NO</th>
                <th
                    style="border: 1px solid #000000; background-color: #e0e0e0; font-weight: bold; text-align: center; vertical-align: middle;">
                    NAMA PESERTA</th>
                <th
                    style="border: 1px solid #000000; background-color: #e0e0e0; font-weight: bold; text-align: center; vertical-align: middle;">
                    MATERI TRAINING</th>
                <th
                    style="border: 1px solid #000000; background-color: #e0e0e0; font-weight: bold; text-align: center; vertical-align: middle;">
                    PERIODE TRAINING</th>
                <th
                    style="border: 1px solid #000000; background-color: #e0e0e0; font-weight: bold; text-align: center; vertical-align: middle;">
                    PERUSAHAAN</th>
                <th
                    style="border: 1px solid #000000; background-color: #e0e0e0; font-weight: bold; text-align: center; vertical-align: middle;">
                    TELP/EMAIL PESERTA</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($peserta as $p)
                <tr height="40">
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">
                        {{ $loop->iteration }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">
                        {{ $p->nama_peserta }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">
                        {{ $p->dataModul->nama_materi ?? '-' }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">
                        @if ($p->awal_training && $p->akhir_training)
                            {{ \Carbon\Carbon::parse($p->awal_training)->format('d') }} -
                            {{ \Carbon\Carbon::parse($p->akhir_training)->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">
                        {{ $p->perusahaan->nama_perusahaan ?? '-' }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">
                        {{ $p->email ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="border: 1px solid #000000; text-align: center; vertical-align: middle;"
                        height="30">
                        Tidak ada data peserta
                    </td>
                </tr>
            @endforelse
        </tbody>

        <tr>
            <td height="15"></td>
        </tr>
        <tr>
            <td height="15"></td>
        </tr>
        <tr>
            <td height="15"></td>
        </tr>

        {{-- ================= FOOTER BOX (NOTE & TANDA TANGAN) ================= --}}
        <tr>
            <td rowspan="7"
                style="
                border: 1px solid #000;
                border-right: none;
                vertical-align: top;
                text-align: left;
                padding: 5px;
                width: 80px;">
                <span style="font-weight: bold; text-decoration: underline; display: block;">
                    NOTE: <br><br><br><br><br><br>
                </span>
            </td>

            <td rowspan="7"
                style="
                border: 1px solid #000;
                border-left: none;
                vertical-align: top;
                text-align: left;
                padding: 5px;">
                {!! nl2br(e($no->note_peserta ?? '-')) !!}<br><br><br><br><br><br>
            </td>

            <td rowspan="7" style="border:1px solid #000; text-align:center; vertical-align:top; padding:10px;">
                <br><br><br><br>
                <span style="font-weight:bold; text-decoration:underline;">
                    {{ $ttd->nama_lengkap ?? 'Admin Inixindo' }}
                </span>
            </td>

            {{-- KOLOM KANAN: TANDA TANGAN (colspan 2)
            <td colspan="1"
                style="border: 1px solid #000000; vertical-align: middle; text-align: center; position: relative; padding: 10px;">

                <!-- Background Image (watermark) -->
                <div
                    style="
                    position: absolute;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background-image: url('{{ public_path('assets/img/bg-sign.png') }}');
                    background-position: center center;
                    background-repeat: no-repeat;
                    background-size: 70% auto;
                    opacity: 0.4;
                    pointer-events: none;
                ">
                </div>

                <!-- Konten Tanda Tangan & Nama (di atas background) -->
                <div
                    style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                    @if (!empty($ttd->ttd) && \Storage::exists('public/ttd/' . $ttd->ttd))
                        <img src="{{ public_path('storage/ttd/' . $ttd->ttd) }}" height="80" alt="Tanda Tangan"
                            style="margin-bottom: 15px;" />
                    @else
                        <div style="height: 80px; margin-bottom: 15px;"></div>
                    @endif

                    <span style="font-weight: bold; text-decoration: underline;">
                        {{ $ttd->nama_lengkap ?? 'Admin Inixindo' }}
                    </span>
                </div>
            </td> --}}
        </tr>
    </table>
</body>

</html>
