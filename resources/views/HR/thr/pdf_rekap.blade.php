@extends('HR.bonus_tahunan.pdf_layout')

@section('title', 'Surat Rekap THR ' . $period->nama_periode)

@section('content')
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
            <td colspan="2">1 (satu) berkas</td>
        </tr>
        <tr>
            <td class="lbl">Perihal</td>
            <td class="sep">:</td>
            <td colspan="2" class="bold">Persetujuan Pencairan Tunjangan Hari Raya (THR) {{ $period->nama_periode }}</td>
        </tr>
    </table>

    <div class="tujuan">
        Yth. Pimpinan Perusahaan<br>
        {{ $profil['nama'] }}<br>
        di Tempat
    </div>

    <p class="para">Dengan hormat,</p>
    <p class="para">
        Sehubungan dengan akan tibanya hari raya yang jatuh pada tanggal <strong>{{ $tglJatuhTempo }}</strong>,
        bersama ini kami sampaikan daftar karyawan penerima Tunjangan Hari Raya (THR) {{ $period->nama_periode }}.
        Perhitungan THR menggunakan masa kerja sampai dengan tanggal tersebut: masa kerja 12 bulan atau lebih
        mendapat <strong>1x Basic Salary</strong>, sedangkan masa kerja kurang dari 12 bulan dihitung prorata
        dengan rumus <strong>(Basic Salary &divide; 12) &times; masa kerja (bulan)</strong>.
    </p>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 29%;">Nama / Jabatan</th>
                <th style="width: 15%;">Rekening Maybank</th>
                <th style="width: 15%;">Basic Salary (Rp)</th>
                <th style="width: 8%;">Masa Kerja</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 18%;">Nilai THR (Rp)</th>
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
                    <td class="right">{{ $row['basic_fmt'] }}</td>
                    <td class="center">{{ $row['bulan_kerja'] }} Bln</td>
                    <td class="center">{{ $row['kategori'] }}</td>
                    <td class="right">{{ $row['nilai_fmt'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data.</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="3" class="right">TOTAL ({{ $summary['total_karyawan'] }} Karyawan)</td>
                <td class="right">{{ $summary['total_basic_fmt'] }}</td>
                <td colspan="2"></td>
                <td class="right">{{ $summary['total_thr_fmt'] }}</td>
            </tr>
        </tbody>
    </table>

    <p class="terbilang">Terbilang: {{ $summary['total_terbilang'] }}</p>

    @if (!$tampilDesimal)
        <p class="sub" style="margin: 0 0 8px;">
            Catatan: nominal ditampilkan tanpa angka desimal (desimal dihapus, tidak dibulatkan).
            Total dihitung dari nilai asli, sehingga jumlah per baris dapat berbeda 1-2 rupiah dari total.
        </p>
    @endif

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