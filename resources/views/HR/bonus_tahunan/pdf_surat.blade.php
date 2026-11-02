@extends('HR.bonus_tahunan.pdf_layout')

@section('title', 'Surat Bonus Tahunan ' . $tahun)

@section('content')
    @foreach ($items as $row)
        <div @if (!$loop->last) style="page-break-after: always;" @endif>
            <table class="meta">
                <tr>
                    <td class="lbl">Nomor</td>
                    <td class="sep">:</td>
                    <td>{{ $nomorSurat }}</td>
                    <td class="tgl">{{ $profil['kota'] }}, ........ Desember {{ $tahun }}</td>
                </tr>
                <tr>
                    <td class="lbl">Lampiran</td>
                    <td class="sep">:</td>
                    <td colspan="2">-</td>
                </tr>
                <tr>
                    <td class="lbl">Perihal</td>
                    <td class="sep">:</td>
                    <td colspan="2" class="bold">Pemberitahuan Pencairan Bonus Tahunan</td>
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
                Sehubungan dengan kinerja dan dedikasi Bapak/Ibu selama periode kerja
                <strong>{{ $row['periode_teks'] }}</strong>, dengan ini perusahaan memberikan bonus tahunan
                yang akan dicairkan pada bulan <strong>Desember {{ $tahun }}</strong> dengan rincian sebagai berikut:
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
                    <td class="k">Periode Perhitungan</td>
                    <td>{{ $row['periode_teks'] }}</td>
                </tr>
                <tr>
                    <td class="k">Masa Kerja Dihitung</td>
                    <td>
                        {{ $row['bulan_kerja'] }} bulan
                        @if ($row['is_direksi'])
                            (default Direksi)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="k">Basic Salary</td>
                    <td>Rp {{ number_format($row['basic_salary'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="k">Skema Bonus</td>
                    <td>{{ $row['skema_label'] }}</td>
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
                    <td class="k" style="background: #eef2ff;">Nominal Bonus</td>
                    <td>Rp {{ number_format($row['nominal_bonus'], 0, ',', '.') }}</td>
                </tr>
            </table>

            <p class="terbilang">Terbilang: {{ $row['terbilang'] }}</p>

            @if (!empty($row['catatan']))
                <p class="para"><strong>Catatan:</strong> {{ $row['catatan'] }}</p>
            @endif

            <p class="para">
                Bonus akan ditransfer ke rekening Maybank sebagaimana tercantum di atas. Kami berharap
                pemberian bonus ini dapat menjadi motivasi untuk terus berkontribusi bagi kemajuan perusahaan.
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
