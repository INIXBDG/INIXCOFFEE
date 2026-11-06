@extends('HR.bonus_tahunan.pdf_layout')

@section('title', 'Surat THR ' . $period->nama_periode)

@section('content')
    @foreach ($items as $row)
        <div @if (!$loop->last) style="page-break-after: always;" @endif>
            <table class="meta">
                <tr>
                    <td class="lbl">Nomor</td>
                    <td class="sep">:</td>
                    <td>{{ $nomorSurat }}</td>
                    <td class="tgl">{{ $profil['kota'] }}, ........ {{ $bulanSurat }} {{ $tahun }}</td>
                </tr>
                <tr>
                    <td class="lbl">Lampiran</td>
                    <td class="sep">:</td>
                    <td colspan="2">-</td>
                </tr>
                <tr>
                    <td class="lbl">Perihal</td>
                    <td class="sep">:</td>
                    <td colspan="2" class="bold">Pemberitahuan Pencairan Tunjangan Hari Raya (THR)</td>
                </tr>
            </table>

            <div class="tujuan">
                Kepada Yth.<br>
                <span class="bold">{{ $row['nama_lengkap'] }}</span><br>
                {{ $row['jabatan'] }}{{ $row['divisi'] !== '-' ? ' - ' . $row['divisi'] : '' }}<br>
                di Tempat
            </div>

            <p class="para">Dengan hormat,</p>
            <p class="para">
                Sehubungan dengan hari raya <strong>{{ $period->nama_periode }}</strong> yang jatuh pada tanggal
                <strong>{{ $tglJatuhTempo }}</strong>, dengan ini perusahaan memberikan Tunjangan Hari Raya (THR)
                kepada Bapak/Ibu dengan rincian sebagai berikut:
            </p>

            <table class="detail">
                <tr>
                    <td class="k">Nama Lengkap</td>
                    <td>{{ $row['nama_lengkap'] }}</td>
                </tr>
                <tr>
                    <td class="k">NIP</td>
                    <td>{{ $row['nip'] }}</td>
                </tr>
                <tr>
                    <td class="k">Jabatan / Divisi</td>
                    <td>{{ $row['jabatan'] }} / {{ $row['divisi'] }}</td>
                </tr>
                <tr>
                    <td class="k">Status Karyawan</td>
                    <td>{{ $row['status_karyawan'] }}</td>
                </tr>
                <tr>
                    <td class="k">Tanggal Masuk</td>
                    <td>{{ $row['tanggal_join_teks'] }}</td>
                </tr>
                <tr>
                    <td class="k">Masa Kerja Dihitung</td>
                    <td>{{ $row['bulan_kerja'] }} bulan ({{ $row['masa_kerja_teks'] }})</td>
                </tr>
                <tr>
                    <td class="k">Basic Salary</td>
                    <td>Rp {{ $row['basic_fmt'] }}</td>
                </tr>
                <tr>
                    <td class="k">Kategori</td>
                    <td>{{ $row['kategori'] }}</td>
                </tr>
                <tr>
                    <td class="k">Rumus Perhitungan</td>
                    <td>{{ $row['rumus'] }}</td>
                </tr>
                <tr>
                    <td class="k">Rekening Maybank</td>
                    <td>{{ $row['rekening_maybank'] }}</td>
                </tr>
                <tr class="nominal">
                    <td class="k" style="background: #eef2ff;">Nilai THR</td>
                    <td>Rp {{ $row['nilai_fmt'] }}</td>
                </tr>
            </table>

            <p class="terbilang">Terbilang: {{ $row['terbilang'] }}</p>

            @if (!empty($row['catatan']))
                <p class="para"><strong>Catatan:</strong> {{ $row['catatan'] }}</p>
            @endif

            <p class="para">
                THR akan ditransfer ke rekening Maybank sebagaimana tercantum di atas. Kami mengucapkan selamat
                merayakan hari raya bagi Bapak/Ibu beserta keluarga.
            </p>
            <p class="para">Demikian surat ini kami sampaikan, atas perhatian Bapak/Ibu kami ucapkan terima kasih.</p>

            <div class="ttd-wrap">
                <table class="ttd">
                    <tr>
                        @foreach ($ttd as $t)
                            <td>
                                <div class="ttd-role">{{ $t['peran'] }}</div>
                                <div class="ttd-space"></div>
                                <div
                                    style="font-weight: bold; font-size: 9.5px; text-decoration: underline; margin: 0 6px;">
                                    {{ $t['nama'] !== '' ? $t['nama'] : '(                                        )' }}
                                </div>
                                <div style="font-size: 8.5px; color: #4b5563; margin-top: 2px;">{{ $t['jabatan'] }}</div>
                            </td>
                        @endforeach
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
@endsection