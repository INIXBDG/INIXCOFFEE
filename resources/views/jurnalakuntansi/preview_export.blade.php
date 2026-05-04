@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="text-center fw-bold">PT INIXINDO AMIETE MANDIRI</h2>
            <h4 class="text-center fw-bold">KAS KECIL ( PETTY CASH )</h4>
            <h5 class="text-center text-muted">{{ strtoupper($labelPeriode) }}</h5>
            
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-hover">
                    <thead class="text-center">
                        <tr>
                            <th>No</th>
                            <th>Nomor KK</th>
                            <th>Tanggal Transaksi</th>
                            <th>Keterangan</th>
                            <th>No Akun</th>
                            <th>Debit (Rp)</th>
                            <th>Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $item->nomor_kk ?? '-' }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal_transaksi)->format('d-m-Y') }}</td>
                            <td>{{ $item->keterangan }}</td>
                            <td class="text-start">{{ $item->no_akun ?? '-'}} - {{ $item->no_accounting->nama_akun ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->debit, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->kredit, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="text-end">TOTAL</td>
                            <td class="text-end">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totalKredit, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <form action="{{ route('jurnalakuntansi.export') }}" method="GET" id="exportForm">
                <input type="hidden" name="tipe_periode" value="{{ $tipe_periode }}">
                <input type="hidden" name="tanggal_acuan" value="{{ $tanggal_acuan->format('Y-m-d') }}">
                <input type="hidden" name="format_export" id="format_export" value="">

                <div class="row mt-4 justify-content-end">
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3 text-center">Konfigurasi Saldo Manual</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <th class="align-middle">Saldo Awal</th>
                                        <td><input type="number" step="0.01" class="form-control text-end" name="saldo_awal" id="saldo_awal" value="0"></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">Kas Masuk</th>
                                        <td><input type="number" step="0.01" class="form-control text-end" name="kas_masuk" id="kas_masuk" value="0"></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">Kas Keluar</th>
                                        <td><input type="number" step="0.01" class="form-control text-end" name="kas_keluar" id="kas_keluar" value="0"></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">Saldo Akhir</th>
                                        <td><input type="number" step="0.01" class="form-control text-end fw-bold" name="saldo_akhir" id="saldo_akhir" value="0"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-success px-4" onclick="submitExport('excel')">
                        <img src="{{ asset('icon/file-text.svg') }}" width="20px" class="me-2"> Download Excel
                    </button>
                    <button type="button" class="btn btn-danger px-4 ms-2" onclick="submitExport('pdf')">
                        <img src="{{ asset('icon/file-text.svg') }}" width="20px" class="me-2"> Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    const totalKredit = {{ $totalKredit }};
    const elSaldoAwal = document.getElementById('saldo_awal');
    const elKasMasuk = document.getElementById('kas_masuk');
    const elKasKeluar = document.getElementById('kas_keluar');
    const elSaldoAkhir = document.getElementById('saldo_akhir');

    // Fungsi Kalkulasi Standar (Merespon input Saldo Awal & Kas Masuk)
    function calculateStandard() {
        let valSA = parseFloat(elSaldoAwal.value) || 0;
        let valKM = parseFloat(elKasMasuk.value) || 0;
        
        // Kalkulasi otomatis Kas Keluar: Total Kredit - Kas Masuk
        let calcKK = totalKredit - valKM;
        elKasKeluar.value = calcKK;

        // Kalkulasi otomatis Saldo Akhir: Saldo Awal + Kas Masuk - Kas Keluar
        let calcAkhir = valSA + valKM - calcKK;
        elSaldoAkhir.value = calcAkhir;
    }

    // Fungsi Kalkulasi Override (Merespon jika Kas Keluar diedit manual)
    function calculateOverride() {
        let valSA = parseFloat(elSaldoAwal.value) || 0;
        let valKM = parseFloat(elKasMasuk.value) || 0;
        let valKK = parseFloat(elKasKeluar.value) || 0;

        let calcAkhir = valSA + valKM - valKK;
        elSaldoAkhir.value = calcAkhir;
    }

    // Assign Event Listeners
    elSaldoAwal.addEventListener('input', calculateStandard);
    elKasMasuk.addEventListener('input', calculateStandard);
    elKasKeluar.addEventListener('input', calculateOverride);

    // Submit Logika
    function submitExport(formatType) {
        document.getElementById('format_export').value = formatType;
        document.getElementById('exportForm').submit();
    }

    // Inisialisasi awal saat load
    calculateStandard();
</script>
@endpush
@endsection