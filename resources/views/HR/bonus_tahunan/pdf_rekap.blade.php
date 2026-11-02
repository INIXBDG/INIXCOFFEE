@extends('HR.bonus_tahunan.pdf_layout')

@section('title', 'Surat Rekap Bonus Tahunan ' . $tahun)

@section('content')
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
            <td colspan="2">1 (satu) berkas</td>
        </tr>
        <tr>
            <td class="lbl">Perihal</td>
            <td class="sep">:</td>
            <td colspan="2" class="bold">Persetujuan Pencairan Bonus Tahunan Karyawan Periode {{ $tahunBonus }}</td>
        </tr>
    </table>

    <div class="tujuan">
        Yth. Pimpinan Perusahaan<br>
        {{ $profil['nama'] }}<br>
        di Tempat
    </div>

    <p class="para">Dengan hormat,</p>
    <p class="para">
        Sehubungan dengan berakhirnya periode kerja Januari sampai dengan Desember {{ $tahunBonus }},
        bersama ini kami sampaikan daftar karyawan penerima bonus tahunan yang akan dicairkan pada bulan
        Desember {{ $tahun }}. Perhitungan bonus menggunakan rumus
        <strong>(Basic Salary &divide; 12) &times; masa kerja pada tahun {{ $tahunBonus }}</strong>,
        sesuai skema yang ditetapkan untuk masing-masing karyawan. Karyawan yang mulai bekerja pada tahun
        {{ $tahun }}
        belum termasuk dalam daftar penerima.
    </p>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Nama / Jabatan</th>
                <th style="width: 14%;">Rekening Maybank</th>
                <th style="width: 14%;">Basic Salary (Rp)</th>
                <th style="width: 8%;">Masa Kerja</th>
                <th style="width: 10%;">Skema</th>
                <th style="width: 15%;">Nominal Bonus (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $i => $row)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>
                        <span class="bold">{{ $row['nama_lengkap'] }}</span><br>
                        <span class="sub">{{ $row['jabatan'] }} &bull; {{ $row['divisi'] }}</span>
                    </td>
                    <td class="center">{{ $row['rekening_maybank'] }}</td>
                    <td class="right">{{ number_format($row['basic_salary'], 0, ',', '.') }}</td>
                    <td class="center">{{ $row['bulan_kerja'] }} Bln</td>
                    <td class="center">{{ strtoupper($row['skema_bonus']) }}</td>
                    <td class="right">{{ number_format($row['nominal_bonus'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data.</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="3" class="right">TOTAL ({{ $summary['total_karyawan'] }} Karyawan)</td>
                <td class="right">{{ number_format($summary['total_basic_salary'], 0, ',', '.') }}</td>
                <td colspan="2"></td>
                <td class="right">{{ number_format($summary['total_nominal_bonus'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="terbilang">Terbilang: {{ $summary['total_terbilang'] }}</p>

    <p class="para">
        Demikian surat ini kami sampaikan. Atas perhatian dan persetujuan Bapak/Ibu, kami ucapkan terima kasih.
    </p>

    <div class="ttd-wrap">
        <table class="ttd">
            <tr>
                @foreach ($ttd as $t)
                    <td>
                        <div class="ttd-role">{{ $t['peran'] }}</div>
                        <div class="ttd-space"></div>
                        <div style="font-weight: bold; font-size: 9.5px; text-decoration: underline; margin: 0 6px;">
                            {{ $t['nama'] !== '' ? $t['nama'] : '(                                        )' }}
                        </div>
                        <div style="font-size: 8.5px; color: #4b5563; margin-top: 2px;">{{ $t['jabatan'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>
@endsection
