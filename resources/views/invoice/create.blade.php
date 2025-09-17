<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Buat Invoice untuk RKM #{{ $rkm->id }}</h2>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('invoice.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_rkm" value="{{ $rkm->id }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="invoice_number" class="form-label">Nomor Invoice</label>
                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_invoice" class="form-label">Tanggal Invoice</label>
                        <input type="date" class="form-control" id="tanggal_invoice" name="tanggal_invoice" value="{{ old('tanggal_invoice', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="perusahaan" class="form-label">Nama Perusahaan</label>
                        <input type="text" class="form-control" id="perusahaan" value="{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="materi" class="form-label">Nama Materi</label>
                        <input type="text" class="form-control" id="materi" value="{{ $rkm->materi->nama_materi ?? '-' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_periode" class="form-label">Tanggal Periode</label>
                        <input type="text" class="form-control" id="tanggal_periode" value="{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="peserta" class="form-label">Peserta</label>
                        <input type="text" class="form-control" id="peserta" value="{{ $rkm->pax ?? '-' }}" readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="unit_price" class="form-label">Harga Unit</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp.</span>
                            <input type="text" class="form-control currency-input" id="unit_price" name="unit_price" value="{{ number_format($rkm->harga_jual ?? 0, 0, ',', '.') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="pax" class="form-label">Pax</label>
                        <input type="number" class="form-control" id="pax" name="pax" value="{{ $rkm->pax ?? 0 }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="total_amount" class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp.</span>
                            <input type="text" class="form-control currency-input" id="total_amount" name="amount" value="{{ number_format(($rkm->harga_jual ?? 0) * ($rkm->pax ?? 0), 0, ',', '.') }}" required readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ppn" class="form-label">PPN 11%</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp.</span>
                            <input type="text" class="form-control currency-input" id="ppn" value="{{ number_format((($rkm->harga_jual ?? 0) * ($rkm->pax ?? 0)) * 0.11, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="grand_total" class="form-label">TOTAL</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp.</span>
                            <input type="text" class="form-control currency-input" id="grand_total" value="{{ number_format((($rkm->harga_jual ?? 0) * ($rkm->pax ?? 0)) * 1.11, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Invoice</button>
            <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Fungsi untuk menghilangkan format angka (titik/koma) sebelum dikirim ke server
        function unformatNumber(input) {
            return input.replace(/\./g, '');
        }

        // Fungsi untuk memformat angka dengan pemisah ribuan
        function formatNumber(input) {
            return input.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Fungsi untuk menghitung ulang total saat harga unit berubah
        function recalculateTotals() {
            const unitPriceInput = document.getElementById('unit_price');
            const paxInput = document.getElementById('pax');
            const totalAmountInput = document.getElementById('total_amount');
            const ppnInput = document.getElementById('ppn');
            const grandTotalInput = document.getElementById('grand_total');

            // Ambil nilai tanpa format
            let unitPrice = unformatNumber(unitPriceInput.value);
            let pax = parseInt(paxInput.value, 10);
            
            // Konversi ke float untuk perhitungan
            unitPrice = parseFloat(unitPrice) || 0;
            pax = isNaN(pax) ? 0 : pax;

            const totalAmount = unitPrice * pax;
            const ppn = totalAmount * 0.11;
            const grandTotal = totalAmount + ppn;

            // Tampilkan kembali nilai yang sudah diformat
            totalAmountInput.value = formatNumber(totalAmount.toFixed(0));
            ppnInput.value = formatNumber(ppn.toFixed(0));
            grandTotalInput.value = formatNumber(grandTotal.toFixed(0));
        }

        // Event listener untuk input harga unit
        document.getElementById('unit_price').addEventListener('input', recalculateTotals);
        
        // Atur kembali format angka saat submit form
        document.querySelector('form').addEventListener('submit', function() {
            const unitPriceInput = document.getElementById('unit_price');
            const totalAmountInput = document.getElementById('total_amount');
            
            // Ubah nilai input kembali ke format angka biasa sebelum dikirim
            unitPriceInput.value = unformatNumber(unitPriceInput.value);
            totalAmountInput.value = unformatNumber(totalAmountInput.value);
        });
    </script>
</body>
</html>