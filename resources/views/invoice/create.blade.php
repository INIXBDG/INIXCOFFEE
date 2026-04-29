<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            vertical-align: top;
        }

        .invoice-table th {
            background-color: #f8f9fa;
        }

        .invoice-table {
            border-collapse: collapse;
            width: 100%;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Buat Invoice untuk RKM #{{ $rkm->id }}</h2>
        @if ($errors->any())
        <div class="alert alert-danger no-print">
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
            <input type="hidden" name="terbilang" id="terbilang_hidden">
            
            <!-- Input Hidden untuk isPeserta dan isTtd -->
            <input type="hidden" name="is_peserta" id="is_peserta_input" value="false">
            <input type="hidden" name="is_ttd" id="is_ttd_input" value="false">

            <div class="table-responsive">
                <table class="invoice-table">
                    <tbody>
                        <tr>
                            <td colspan="3" class="fw-bold">Nomor Invoice:</td>
                            <td colspan="2">
                                @php
                                $idRkm = $rkm->id;
                                $kodeInvoice = "INXBDG-INV";
                                $bulanRomawi = [
                                1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
                                7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
                                ];
                                $bulan = (int) date('m');
                                $tahun = date('Y');
                                $bulanRomawiNow = $bulanRomawi[$bulan];
                                $invoiceNumber = $idRkm . '/' . $kodeInvoice . '/' . $bulanRomawiNow . '/' . $tahun;
                                @endphp
                                <input type="text" class="form-control" name="invoice_number"
                                    value="{{ old('invoice_number', $invoiceNumber) }}">
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3" class="fw-bold">Tanggal Invoice:</td>
                            <td colspan="2">
                                <input type="date" class="form-control" name="tanggal_invoice" id="tanggal_invoice"
                                    value="{{ old('tanggal_invoice', date('Y-m-d')) }}" required>
                            </td>
                        </tr>

                        <!-- ✅ Purchase Order Number -->
                        <tr>
                            <td colspan="3" class="fw-bold">Purchase Order No:</td>
                            <td colspan="2">
                                <input type="text" class="form-control" name="purchase_order" 
                                    placeholder="Masukkan nomor PO (opsional)"
                                    value="{{ old('purchase_order') }}">
                            </td>
                        </tr>

                        <!-- ✅ Due Date -->
                        <tr>
                            <td colspan="3" class="fw-bold">Due Date:</td>
                            <td colspan="2">
                                {{-- due date ke db --}}
                                <input type="date" hidden name="due_date" id="due_date"
                                    value="{{ old('due_date') }}">
                                {{-- due date manual --}}
                                <input type="date" class="form-control" name="due_date_manual" id="due_date_manual"
                                    value="{{ old('due_date') }}">
                                <small class="text-muted">Otomatis 6 bulan dari tanggal invoice</small>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5" class="bg-light fw-bold text-center">Detail RKM</td>
                        </tr>
                        <tr>
                            <td colspan="3">Perusahaan:</td>
                            <td colspan="2">
                                <input type="text" class="form-control" name="perusahaan" value="{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}">
                                {{-- <b>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</b> --}}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">Materi:</td>
                            <td colspan="2">
                                <b>{{ $rkm->materi->nama_materi ?? '-' }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">Tanggal:</td>
                            <td colspan="2">
                                <b>{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }} s/d
                                    {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">Peserta:</td>
                            <td colspan="2">
                                <b>{{ $rkm->pax ?? '-' }}</b>
                            </td>
                        </tr>

                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 45%;">Deskripsi</th>
                            <th style="width: 10%;">Pax</th>
                            <th style="width: 20%;">Harga Unit</th>
                            <th style="width: 20%;">Jumlah</th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>
                                Materi: <input type="text" class="form-control" name="materi"
                                    value="{{ $rkm->materi->nama_materi ?? '-' }}"><br>
                                Tanggal: <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal"
                                    value="{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('Y-m-d') }}" required><br>
                                Sampai Dengan: <input type="date" class="form-control" id="tanggal_akhir"
                                    name="tanggal_akhir"
                                    value="{{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d') }}" required><br>
                                Peserta:
                                    <br>
                                    @foreach ($rkm->registrasi as $item)
                                          <input 
                                            type="text" 
                                            name="peserta[]" 
                                            class="form-control"
                                            value="{{ $item->peserta->nama ?? '-' }}"
                                        >
                                    @endforeach
                            </td>
                            <td>
                                <input type="number" class="form-control" id="pax" name="pax"
                                    value="{{ $rkm->pax ?? 0 }}">
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control currency-input" id="unit_price"
                                        name="unit_price"
                                        value="{{ number_format($rkm->harga_jual ?? 0, 0, ',', '.') }}" required>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control currency-input" id="total_amount"
                                        name="amount"
                                        value="{{ number_format(($rkm->harga_jual ?? 0) * ($rkm->pax ?? 0), 0, ',', '.') }}"
                                        readonly>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3" rowspan="3" class="align-top">
                                <label for="bank_name" class="fw-bold">Nama Bank:</label>
                                <select name="bank_name" id="bank_name" class="form-control mb-2">
                                    <option value="">Pilih Nama Bank</option>
                                    <option value="BANK MANDIRI KK BANDUNG CIHAMPELAS">BANK MANDIRI KK BANDUNG CIHAMPELAS</option>
                                    <option value="BANK BCA KK BANDUNG ABDUL RIVAI">BANK BCA KK BANDUNG ABDUL RIVAI</option>
                                    <option value="BANK BJB KCP CIHAMPELAS BANDUNG">BANK BJB KCP CIHAMPELAS BANDUNG</option>
                                </select>

                                <label for="account_number" class="fw-bold">Nomor Rekening:</label>
                                <select name="account_number" id="account_number" class="form-control">
                                    <option value="">Pilih Nomor Rekening</option>
                                </select>
                            </td>
                            <td class="text-end">SubTotal</td>
                            <td class="text-end">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control currency-input text-end"
                                        id="sub_total_display"
                                        value="{{ number_format(($rkm->harga_jual ?? 0) * ($rkm->pax ?? 0), 0, ',', '.') }}"
                                        readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-end">PPN 11%</td>
                            <td class="text-end">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control currency-input text-end" id="ppn"
                                        value="{{ number_format((($rkm->harga_jual ?? 0) * ($rkm->pax ?? 0)) * 0.11, 0, ',', '.') }}"
                                        readonly>
                                </div>
                            </td>
                        </tr>
                        <tr class="no-print">
                            <td colspan="2" class="text-end">
                                <div class="form-check d-inline-block">
                                    <input class="form-check-input" type="checkbox" id="pph23_check">
                                    <label class="form-check-label fw-bold" for="pph23_check">
                                        Gunakan PPh 23 (2%)
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr id="pph23_row" style="display:none;">
                            <td colspan="3"></td>
                            <td class="text-end">PPh 23 (2%)</td>
                            <td class="text-end">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control currency-input text-end" id="pph23" value="0" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td class="text-end fw-bold">TOTAL</td>
                            <td class="text-end fw-bold">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control currency-input text-end"
                                        id="grand_total"
                                        value="{{ number_format((($rkm->harga_jual ?? 0) * ($rkm->pax ?? 0)) * 1.11, 0, ',', '.') }}"
                                        readonly>
                                    <input type="hidden" name="amount" id="final_amount">
                                </div>
                            </td>
                        </tr>

                        <tr class="bg-secondary text-white fw-bold text-center">
                            <td colspan="5">
                                <i>
                                    <p class="mb-0 fs-5" id="terbilang_total"></p>
                                </i>
                            </td>
                        </tr>

                        <tr class="no-print">
                            <td colspan="5" class="border-0 pt-3">
                                <!-- Toggle Checklist untuk isPeserta dan isTtd -->
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="toggle_is_peserta">
                                    <label class="form-check-label" for="toggle_is_peserta">Sertakan Daftar Peserta?</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="toggle_is_ttd">
                                    <label class="form-check-label" for="toggle_is_ttd">Sertakan Tanda Tangan?</label>
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan Invoice</button>
                                <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Batal</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            const belasan = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas',
                'tujuh belas', 'delapan belas', 'sembilan belas'
            ];
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

                if (ratusan === 1) {
                    tempTerbilang += 'seratus ';
                } else if (ratusan > 1) {
                    tempTerbilang += bil[ratusan] + ' ratus ';
                }

                if (sisaRatusan < 10) {
                    tempTerbilang += bil[sisaRatusan];
                } else if (sisaRatusan < 20) {
                    tempTerbilang += belasan[sisaRatusan - 10];
                } else {
                    let puluhan = Math.floor(sisaRatusan / 10);
                    let satuan = sisaRatusan % 10;
                    tempTerbilang += bil[puluhan] + ' puluh ' + bil[satuan];
                }

                if (tempTerbilang.trim() !== '') {
                    tempTerbilang += ' ' + ribuan[i];
                }

                hasil = tempTerbilang.trim() + ' ' + hasil.trim();
                i++;
            }

            hasil = hasil.replace('satu ribu', 'seribu');
            hasil = hasil.trim().charAt(0).toUpperCase() + hasil.trim().slice(1);
            return hasil + ' Rupiah';
        }

        function unformatNumber(input) {
            return input.replace(/\./g, '');
        }

        function formatNumber(input) {
            let number = parseFloat(input);
            if (isNaN(number)) {
                return "0";
            }
            return number.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function recalculateTotals() {
            const unitPriceInput = document.getElementById('unit_price');
            const paxInput = document.getElementById('pax');
            const totalAmountInput = document.getElementById('total_amount');
            const subTotalDisplayInput = document.getElementById('sub_total_display');
            const ppnInput = document.getElementById('ppn');
            const grandTotalInput = document.getElementById('grand_total');
            const terbilangDisplay = document.getElementById('terbilang_total');
            const finalAmountInput = document.getElementById('final_amount');
            const terbilangHidden = document.getElementById('terbilang_hidden');
            const pph23Check = document.getElementById('pph23_check');
            const pph23Row = document.getElementById('pph23_row');
            const pph23Input = document.getElementById('pph23');

            let unitPrice = parseFloat(unformatNumber(unitPriceInput.value)) || 0;
            let pax = parseInt(paxInput.value, 10) || 0;

            const totalAmount = unitPrice * pax;
            const ppn = totalAmount * 0.11;
            let grandTotal = totalAmount + ppn;

            let pph23 = 0;
            if (pph23Check.checked) {
                pph23Row.style.display = '';
                pph23 = totalAmount * 0.02;
                grandTotal -= pph23;
            } else {
                pph23Row.style.display = 'none';
            }

            totalAmountInput.value = formatNumber(totalAmount);
            subTotalDisplayInput.value = formatNumber(totalAmount);
            ppnInput.value = formatNumber(ppn);
            pph23Input.value = formatNumber(pph23);
            grandTotalInput.value = formatNumber(grandTotal);

            finalAmountInput.value = grandTotal;
            const terbilangValue = terbilang(grandTotal);
            terbilangDisplay.innerText = terbilangValue;
            terbilangHidden.value = terbilangValue;
        }

        document.getElementById('pph23_check').addEventListener('change', recalculateTotals);

        document.addEventListener('DOMContentLoaded', (event) => {
            recalculateTotals();

            const togglePeserta = document.getElementById('toggle_is_peserta');
            const toggleTtd = document.getElementById('toggle_is_ttd');
            const inputPeserta = document.getElementById('is_peserta_input');
            const inputTtd = document.getElementById('is_ttd_input');

            togglePeserta.checked = false; // Default false
            toggleTtd.checked = false;     // Default false
            inputPeserta.value = togglePeserta.checked ? 'true' : 'false';
            inputTtd.value = toggleTtd.checked ? 'true' : 'false';
        });

        document.getElementById('unit_price').addEventListener('input', function(e) {
            let cleanValue = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = formatNumber(cleanValue);
            recalculateTotals();
        });

        document.getElementById('pax').addEventListener('input', function(e) {
            recalculateTotals();
        });

        document.getElementById('toggle_is_peserta').addEventListener('change', function() {
            const input = document.getElementById('is_peserta_input');
            input.value = this.checked ? 'true' : 'false';
        });

        document.getElementById('toggle_is_ttd').addEventListener('change', function() {
            const input = document.getElementById('is_ttd_input');
            input.value = this.checked ? 'true' : 'false';
        });

        document.querySelector('form').addEventListener('submit', function() {
            const unitPriceInput = document.getElementById('unit_price');
            unitPriceInput.value = unformatNumber(unitPriceInput.value);
        });

        document.getElementById('tanggal_invoice').addEventListener('change', function() {
            const invoiceDate = new Date(this.value);

            if (!isNaN(invoiceDate)) {
                invoiceDate.setMonth(invoiceDate.getMonth() + 6);

                const year = invoiceDate.getFullYear();
                const month = String(invoiceDate.getMonth() + 1).padStart(2, '0');
                const day = String(invoiceDate.getDate()).padStart(2, '0');

                document.getElementById('due_date').value = `${year}-${month}-${day}`;
                document.getElementById('due_date_manual').value = `${year}-${month}-${day}`;
            }
        });

        document.getElementById('tanggal_invoice').dispatchEvent(new Event('change'));

        const bankAccounts = {
            "BANK MANDIRI KK BANDUNG CIHAMPELAS": "131-00-0734797-6",
            "BANK BCA KK BANDUNG ABDUL RIVAI": "5170583738",
            "BANK BJB KCP CIHAMPELAS BANDUNG": "0142016095100"
        };

        const bankSelect = document.getElementById("bank_name");
        const accountSelect = document.getElementById("account_number");

        bankSelect.addEventListener("change", function () {
            const selectedBank = this.value;
            accountSelect.innerHTML = '<option value="">Pilih Nomor Rekening</option>';

            if (selectedBank && bankAccounts[selectedBank]) {
                const option = document.createElement("option");
                option.value = bankAccounts[selectedBank];
                option.textContent = bankAccounts[selectedBank];
                option.selected = true;
                accountSelect.appendChild(option);
            }
        });
    </script>
</body>

</html>