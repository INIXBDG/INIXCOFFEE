<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kwitansi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #dee2e6;
            padding: .5rem;
            vertical-align: middle;
        }

        .no-print {
            border: none !important;
            background: none !important;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Buat Kwitansi untuk Invoice #{{ $invoice->invoice_number }}</h2>

        @if ($errors->any())
        <div class="alert alert-danger no-print">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('kwitansi.store') }}" method="POST">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
            <input type="hidden" name="terbilang" id="terbilang_hidden">
            <input type="hidden" name="jumlah_uang" id="jumlah_uang_hidden" value="{{ $invoice->amount }}">
            <input type="hidden" name="jumlah_peserta" value="{{ $invoice->rkm->pax ?? 0 }}">

            <div class="table-responsive">
                <table class="invoice-table">
                    <tbody>

                        {{-- Nomor Kwitansi --}}
                        <tr>
                            <td colspan="3" class="fw-bold">Nomor Kwitansi:</td>
                            <td colspan="2">
                                @php
                                $kodeKwitansi = "INXBDG-KWIT";
                                $bulanRomawi = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
                                $bulanRomawiNow = $bulanRomawi[(int) date('m')];
                                $tahun = date('Y');
                                $idRkm = explode('/', $invoice->invoice_number)[0];
                                $kwitansiNumber = $idRkm . '/' . $kodeKwitansi . '/' . $bulanRomawiNow . '/' . $tahun;
                                @endphp
                                <input type="text" class="form-control" name="nomor_kwitansi"
                                    value="{{ old('nomor_kwitansi', $kwitansiNumber) }}" required>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="fw-bold">Tanggal Kwitansi:</td>
                            <td colspan="2">
                                <input type="date" class="form-control" name="tanggal"
                                    value="{{ old('tanggal', now()->toDateString()) }}" required>
                            </td>
                        </tr>

                        {{-- Detail Kwitansi --}}
                        <tr>
                            <td colspan="5" class="bg-light fw-bold text-center">Detail Kwitansi</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="fw-bold">Sudah terima dari:</td>
                            <td colspan="2">
                                <input type="text" class="form-control" name="nama_penerima"
                                    value="{{ old('nama_penerima', $invoice->rkm->perusahaan->nama_perusahaan ?? '') }}"
                                    required>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="fw-bold">Jumlah Uang:</td>
                            <td colspan="2">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control currency-input" id="amount_display"
                                        value="{{ number_format($invoice->amount, 0, ',', '.') }}" readonly>
                                </div>
                            </td>
                        </tr>

                        {{-- Terbilang --}}
                        <tr class="bg-secondary text-white fw-bold text-center">
                            <td colspan="5">
                                <i>
                                    <p class="mb-0 fs-5" id="terbilang_display"></p>
                                </i>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3" class="fw-bold">Untuk Pembayaran:</td>
                            <td colspan="2">
                                <textarea name="keterangan" class="form-control" rows="3" required>{{ old('keterangan', 'Pembayaran ' . ($invoice->rkm->materi->nama_materi ?? '')) }}</textarea>
                            </td>
                        </tr>
<tr>
    <td colspan="3">Tanggal Pelaksanaan:</td>
    <td colspan="2">
        <b>
            {{ \Carbon\Carbon::parse($invoice->rkm->tanggal_awal)->format('d F Y') }}
            s/d
            {{ \Carbon\Carbon::parse($invoice->rkm->tanggal_akhir)->format('d F Y') }}
        </b>
    </td>
</tr>

<tr>
    <td colspan="3">Peserta:</td>
    <td colspan="2">
        <b>{{ $invoice->rkm->pax ?? '-' }}</b>
    </td>
</tr>

                        <tr>
                            <td colspan="3" class="fw-bold">Nama Penandatangan:</td>
                            <td colspan="2">
                                <input type="text" class="form-control" name="nama_penandatangan"
                                    value="{{ old('nama_penandatangan', $karyawan->nama_lengkap ?? auth()->user()->name ?? '') }}">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="fw-bold">Jabatan Penandatangan:</td>
                            <td colspan="2">
                                <input type="text" class="form-control" name="jabatan_penandatangan"
                                    value="{{ old('jabatan_penandatangan', $karyawan->jabatan ?? '') }}" required>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="fw-bold">Tanggal TTD:</td>
                            <td colspan="2">
                                <input type="date" class="form-control" name="tanggal_ttd"
                                    value="{{ old('tanggal_ttd', now()->toDateString()) }}">
                            </td>
                        </tr>

                        {{-- Tombol --}}
                        <tr class="no-print">
                            <td colspan="5" class="border-0 pt-3">
                                <button type="submit" class="btn btn-primary">Simpan Kwitansi</button>
                                <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Batal</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    {{-- JS Terbilang + Export --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function terbilang(angka) {
            if (typeof angka !== 'number') {
                angka = Number(String(angka).replace(/[^0-9]/g, ''));
            }
            if (angka === 0) {
                return 'Nol Rupiah';
            }
            const bil = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
            const belasan = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
            const ribuan = ['', 'ribu', 'juta', 'miliar', 'triliun'];
            let hasil = '';
            let tempAngka = String(angka);
            let i = 0;

            while (tempAngka.length > 0) {
                let tigaDigit = parseInt(tempAngka.slice(-3), 10);
                tempAngka = tempAngka.slice(0, -3);
                if (tigaDigit === 0) {
                    i++;
                    continue;
                }

                let tempTerbilang = '';
                let ratusan = Math.floor(tigaDigit / 100);
                let sisaRatusan = tigaDigit % 100;

                if (ratusan === 1) tempTerbilang += 'seratus ';
                else if (ratusan > 1) tempTerbilang += bil[ratusan] + ' ratus ';

                if (sisaRatusan < 10) tempTerbilang += bil[sisaRatusan];
                else if (sisaRatusan < 20) tempTerbilang += belasan[sisaRatusan - 10];
                else {
                    let puluhan = Math.floor(sisaRatusan / 10);
                    let satuan = sisaRatusan % 10;
                    tempTerbilang += bil[puluhan] + ' puluh ' + bil[satuan];
                }

                if (tempTerbilang.trim() !== '') tempTerbilang += ' ' + ribuan[i];
                hasil = tempTerbilang.trim() + ' ' + hasil.trim();
                i++;
            }
            hasil = hasil.replace('satu ribu', 'seribu');
            hasil = hasil.trim().charAt(0).toUpperCase() + hasil.trim().slice(1);
            return hasil + ' Rupiah';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const amount = parseFloat("{{ $invoice->amount }}");
            const terbilangValue = terbilang(amount);
            document.getElementById('terbilang_display').innerText = terbilangValue;
            document.getElementById('terbilang_hidden').value = terbilangValue;
        });

        function exportToPdf() {
            const element = document.querySelector('.container');
            const options = {
                margin: [10, 10, 10, 10],
                filename: 'kwitansi_' + document.querySelector('input[name="nomor_kwitansi"]').value + '.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            html2pdf().set(options).from(element).save();
        }

        function exportToExcel() {
            alert('Export Excel kwitansi bisa ditambahkan pakai SheetJS / TableExport.js');
        }
    </script>
</body>

</html>