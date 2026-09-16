@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Loading Modal -->
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="cube">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
            </div>
        </div>

    {{-- <button id="btnSaveData" class="btn-save">Simpan Data</button> --}}
    <a href="{{ route('income-statement.laporan') }}" class="btn-laporan">Laporan</a>

    <div style="width: 100%; overflow-x: auto; overflow-y: visible; padding-bottom: 20px;">
        <table id="incomeStatementTable">
            <thead>
                <tr>
                    <th class="text-center">Keterangan</th>
                    @php
                        $nama_bulan = [
                            'Januari', 'Februari', 'Maret', 'April',
                            'Mei', 'Juni', 'Juli', 'Agustus',
                            'September', 'Oktober', 'November', 'Desember'
                        ];
                    @endphp
                    @foreach ($nama_bulan as $bulan)
                        <th class="text-center">{{ $bulan }}</th>
                    @endforeach
                    <th class="text-center">TOTAL PER TAHUN</th>
                    <th class="text-center">Rata2<br>%</th>
                    <th class="text-center">PERSENTASE<br>BIAYA</th>
                </tr>
            </thead>

            <tbody id="salesContainer">
                <tr>
                    <td class="text-left">Penjualan Training</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td><input type="text" class="input-calc sales-training month-{{ $m }}" data-item="sales_training" data-month="{{ $m }}" value="{{ isset($transactionData['sales_training'][$m]) && (float)$transactionData['sales_training'][$m] != 0 ? (float)$transactionData['sales_training'][$m] : '' }}" placeholder="0.00"></td>
                    @endfor
                    <td class="row-total display-currency">0.00</td>
                    <td class="row-avg display-currency">0.00</td>
                    <td class="row-percent">0.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Discount Penjualan</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td><input type="text" class="input-calc discount month-{{ $m }}" data-item="discount" data-month="{{ $m }}" value="{{ isset($transactionData['discount'][$m]) && (float)$transactionData['discount'][$m] != 0 ? (float)$transactionData['discount'][$m] : '' }}" placeholder="0.00"></td>
                    @endfor
                    <td class="row-total display-currency">0.00</td>
                    <td class="row-avg display-currency">0.00</td>
                    <td class="row-percent">0.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Payment Advanced</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td><input type="text" class="input-calc advance month-{{ $m }}" data-item="payment_advance" data-month="{{ $m }}" value="{{ isset($transactionData['payment_advance'][$m]) && (float)$transactionData['payment_advance'][$m] != 0 ? (float)$transactionData['payment_advance'][$m] : '' }}" placeholder="0.00"></td>
                    @endfor
                    <td class="row-total display-currency">0.00</td>
                    <td class="row-avg display-currency">0.00</td>
                    <td class="row-percent">0.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Exam</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td><input type="text" class="input-calc exam month-{{ $m }}" data-item="exam" data-month="{{ $m }}" value="{{ isset($transactionData['exam'][$m]) && (float)$transactionData['exam'][$m] != 0 ? (float)$transactionData['exam'][$m] : '' }}" placeholder="0.00"></td>
                    @endfor
                    <td class="row-total display-currency">0.00</td>
                    <td class="row-avg display-currency">0.00</td>
                    <td class="row-percent">0.00%</td>
                </tr>
                <tr class="fw-bold">
                    <td class="text-left">Nett Sales Training</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="display-currency net-sales month-{{ $m }}">0.00</td>
                    @endfor
                    <td class="display-currency total-net-sales">0.00</td>
                    <td class="display-currency avg-net-sales">0.00</td>
                    <td class="percent-net-sales">0.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Penjualan Proyek</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td><input type="text" class="input-calc project month-{{ $m }}" data-item="project" data-month="{{ $m }}" value="{{ isset($transactionData['project'][$m]) && (float)$transactionData['project'][$m] != 0 ? (float)$transactionData['project'][$m] : '' }}" placeholder="0.00"></td>
                    @endfor
                    <td class="row-total display-currency">0.00</td>
                    <td class="row-avg display-currency">0.00</td>
                    <td class="row-percent">0.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Penjualan Webinar & Sertifikat</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td><input type="text" class="input-calc webinar month-{{ $m }}" data-item="webinar" data-month="{{ $m }}" value="{{ isset($transactionData['webinar'][$m]) && (float)$transactionData['webinar'][$m] != 0 ? (float)$transactionData['webinar'][$m] : '' }}" placeholder="0.00"></td>
                    @endfor
                    <td class="row-total display-currency">0.00</td>
                    <td class="row-avg display-currency">0.00</td>
                    <td class="row-percent">0.00%</td>
                </tr>
                <tr class="bg-yellow">
                    <td class="text-left">TOTAL PENJUALAN TAHUNAN</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="display-currency grand-total-sales month-{{ $m }}">0.00</td>
                    @endfor
                    <td class="display-currency grand-total-sales-year">0.00</td>
                    <td class="display-currency grand-total-sales-avg">0.00</td>
                    <td>100.00%</td>
                </tr>
            </tbody>

            <tbody id="variableCostContainer">
                <tr>
                    <td class="bg-blue text-left" colspan="16">VARIABLE COST</td>
                </tr>
                @foreach ($variableCosts as $type => $costs)
                <tr class="fw-bold">
                    <td class="text-left" colspan="16">{{ $type }} :</td>
                </tr>
                    @foreach ($costs as $cost)
                    <tr>
                        <td class="text-left">{{ $cost->name }}</td>
                        @for ($m = 1; $m <= 12; $m++)
                            <td><input type="text" class="input-calc vc-item month-{{ $m }}" data-item="vc_{{ $cost->id }}" data-month="{{ $m }}" value="{{ isset($transactionData['vc_' . $cost->id][$m]) && (float)$transactionData['vc_' . $cost->id][$m] != 0 ? (float)$transactionData['vc_' . $cost->id][$m] : '' }}" placeholder="0.00"></td>
                        @endfor
                        <td class="row-total display-currency">0.00</td>
                        <td class="row-avg display-currency">0.00</td>
                        <td class="row-percent">0.00%</td>
                    </tr>
                    @endforeach
                @endforeach
                <tr class="bg-blue">
                    <td class="text-left">TOTAL VARIABLE COST</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="display-currency total-vc month-{{ $m }}">0.00</td>
                    @endfor
                    <td class="display-currency grand-total-vc">0.00</td>
                    <td class="display-currency avg-total-vc">0.00</td>
                    <td class="percent-total-vc">0.00%</td>
                </tr>
            </tbody>

            <tbody id="fixedCostContainer">
                <tr>
                    <td class="bg-green text-left" colspan="16">FIXED COST</td>
                </tr>
                @foreach ($fixedCosts as $type => $costs)
                <tr class="fw-bold">
                    <td class="text-left" colspan="16">{{ $type }}</td>
                </tr>
                    @foreach ($costs as $cost)
                    <tr>
                        <td class="text-left">{{ $cost->name }}</td>
                        @for ($m = 1; $m <= 12; $m++)
                            <td><input type="text" class="input-calc fc-item month-{{ $m }}" data-item="fc_{{ $cost->id }}" data-month="{{ $m }}" value="{{ isset($transactionData['fc_' . $cost->id][$m]) && (float)$transactionData['fc_' . $cost->id][$m] != 0 ? (float)$transactionData['fc_' . $cost->id][$m] : '' }}" placeholder="0.00"></td>
                        @endfor
                        <td class="row-total display-currency">0.00</td>
                        <td class="row-avg display-currency">0.00</td>
                        <td class="row-percent">0.00%</td>
                    </tr>
                    @endforeach
                @endforeach
                <tr class="bg-green">
                    <td class="text-left">TOTAL FIXED COST</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="display-currency total-fc month-{{ $m }}">0.00</td>
                    @endfor
                    <td class="display-currency grand-total-fc">0.00</td>
                    <td class="display-currency avg-total-fc">0.00</td>
                    <td class="percent-total-fc">0.00%</td>
                </tr>
            </tbody>

            <tbody id="summaryContainer">
                <tr class="bg-red">
                    <td class="text-center">TOTAL PENGELUARAN</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="display-currency total-expense month-{{ $m }}">0.00</td>
                    @endfor
                    <td class="display-currency grand-total-expense">0.00</td>
                    <td class="display-currency avg-total-expense">0.00</td>
                    <td class="percent-total-expense">0.00%</td>
                </tr>
                <tr class="bg-orange">
                    <td class="text-center">Laba/Rugi</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="display-currency profit-loss month-{{ $m }}">0.00</td>
                    @endfor
                    <td class="display-currency grand-profit-loss">0.00</td>
                    <td class="display-currency avg-profit-loss">0.00</td>
                    <td class="percent-profit-loss">0.00%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: right; vertical-align: middle; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }

        .bg-yellow { background-color: #ffff00 !important; font-weight: bold; }
        .bg-blue { background-color: #00b0f0 !important; font-weight: bold; }
        .bg-green { background-color: #00b050 !important; font-weight: bold; }
        .bg-red { background-color: #ff0000 !important; color: #000; font-weight: bold; }
        .bg-orange { background-color: #ffc000 !important; font-weight: bold; }
        .fw-bold { font-weight: bold; }

        input { width: 100px; text-align: right; border: 1px solid #ccc; padding: 2px; }
        input:focus { border: 1px solid #000; outline: none; }
        .btn-save { margin-bottom: 15px; padding: 10px; cursor: pointer; background-color: #007bff; color: white; border: none; font-weight: bold; }
        .btn-laporan { margin-bottom: 15px; padding: 10px; cursor: pointer; background-color: #ff0000; color: white; border: none; font-weight: bold; text-decoration: none; display: inline-block; }

    </style>
    @push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            // --- FUNGSI FORMATTING ---
            function formatCurrency(value) {
                if (value === 0 || isNaN(value)) return '-';
                return 'Rp ' + value.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            function formatPercent(value) {
                if (isNaN(value) || !isFinite(value) || value === 0) return '0.00%';
                return value.toFixed(2) + '%';
            }

            function formatRupiahRealtime(angka) {
                let number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return rupiah ? 'Rp ' + rupiah : '';
            }

            $('.input-calc').each(function() {
                let val = $(this).val();
                if (val && val !== '0' && val !== '0.00') {
                    let cleanNumber = parseFloat(val);
                    $(this).val(formatRupiahRealtime(cleanNumber)); // Tampilkan sebagai Rupiah
                    $(this).data('raw', cleanNumber); // Simpan angka murni di attribute data-raw
                } else {
                    $(this).val('');
                    $(this).data('raw', '');
                }
            });

            function calculateIncomeStatement() {
                let yearlyGrandTotalSales = 0;
                let yearlyGrandTotalVc = 0;
                let yearlyGrandTotalFc = 0;
                let yearlyNetSales = 0;

                for (let m = 1; m <= 12; m++) {
                    // PERUBAHAN: Sekarang membaca nilai dari .data('raw') bukan dari .val()
                    let training = parseFloat($(`.sales-training.month-${m}`).data('raw')) || 0;
                    let discount = parseFloat($(`.discount.month-${m}`).data('raw')) || 0;
                    let advance = parseFloat($(`.advance.month-${m}`).data('raw')) || 0;
                    let exam = parseFloat($(`.exam.month-${m}`).data('raw')) || 0;
                    let project = parseFloat($(`.project.month-${m}`).data('raw')) || 0;
                    let webinar = parseFloat($(`.webinar.month-${m}`).data('raw')) || 0;

                    let netSales = training - (discount + advance + exam);
                    let totalSales = netSales + project + webinar;

                    yearlyNetSales += netSales;
                    yearlyGrandTotalSales += totalSales;

                    $(`td.net-sales.month-${m}`).text(formatCurrency(netSales));
                    $(`td.grand-total-sales.month-${m}`).text(formatCurrency(totalSales));

                    let totalVc = 0;
                    $(`input.vc-item.month-${m}`).each(function() {
                        totalVc += parseFloat($(this).data('raw')) || 0;
                    });
                    yearlyGrandTotalVc += totalVc;
                    $(`td.total-vc.month-${m}`).text(formatCurrency(totalVc));

                    let totalFc = 0;
                    $(`input.fc-item.month-${m}`).each(function() {
                        totalFc += parseFloat($(this).data('raw')) || 0;
                    });
                    yearlyGrandTotalFc += totalFc;
                    $(`td.total-fc.month-${m}`).text(formatCurrency(totalFc));

                    let totalExpense = totalVc + totalFc;
                    let profitLoss = totalSales - totalExpense;

                    $(`td.total-expense.month-${m}`).text(formatCurrency(totalExpense));
                    $(`td.profit-loss.month-${m}`).text(formatCurrency(profitLoss));
                }

                // --- Perhitungan Baris Total (Tetap Sama Konsepnya) ---
                $('table tbody tr').each(function() {
                    let isInputRow = $(this).find('input.input-calc').length > 0;
                    if (isInputRow) {
                        let rowTotal = 0;
                        $(this).find('input.input-calc').each(function() {
                            rowTotal += parseFloat($(this).data('raw')) || 0;
                        });

                        let rowAvg = rowTotal / 12;
                        let rowPercent = yearlyGrandTotalSales > 0 ? (rowTotal / yearlyGrandTotalSales) * 100 : 0;

                        $(this).find('td.row-total').text(formatCurrency(rowTotal));
                        $(this).find('td.row-avg').text(formatCurrency(rowAvg));
                        let percentCell = $(this).find('td.row-percent');
                        if(percentCell.length > 0) percentCell.text(formatPercent(rowPercent));
                    }
                });

                // Tetapkan Grand Total Tahunan...
                $('.total-net-sales').text(formatCurrency(yearlyNetSales));
                $('.avg-net-sales').text(formatCurrency(yearlyNetSales / 12));
                $('.percent-net-sales').text(formatPercent(yearlyGrandTotalSales > 0 ? (yearlyNetSales / yearlyGrandTotalSales) * 100 : 0));

                $('.grand-total-sales-year').text(formatCurrency(yearlyGrandTotalSales));
                $('.grand-total-sales-avg').text(formatCurrency(yearlyGrandTotalSales / 12));

                $('.grand-total-vc').text(formatCurrency(yearlyGrandTotalVc));
                $('.avg-total-vc').text(formatCurrency(yearlyGrandTotalVc / 12));
                $('.percent-total-vc').text(formatPercent(yearlyGrandTotalSales > 0 ? (yearlyGrandTotalVc / yearlyGrandTotalSales) * 100 : 0));

                $('.grand-total-fc').text(formatCurrency(yearlyGrandTotalFc));
                $('.avg-total-fc').text(formatCurrency(yearlyGrandTotalFc / 12));
                $('.percent-total-fc').text(formatPercent(yearlyGrandTotalSales > 0 ? (yearlyGrandTotalFc / yearlyGrandTotalSales) * 100 : 0));

                let yearlyGrandExpense = yearlyGrandTotalVc + yearlyGrandTotalFc;
                $('.grand-total-expense').text(formatCurrency(yearlyGrandExpense));
                $('.avg-total-expense').text(formatCurrency(yearlyGrandExpense / 12));
                $('.percent-total-expense').text(formatPercent(yearlyGrandTotalSales > 0 ? (yearlyGrandExpense / yearlyGrandTotalSales) * 100 : 0));

                let yearlyProfitLoss = yearlyGrandTotalSales - yearlyGrandExpense;
                $('.grand-profit-loss').text(formatCurrency(yearlyProfitLoss));
                $('.avg-profit-loss').text(formatCurrency(yearlyProfitLoss / 12));
                $('.percent-profit-loss').text(formatPercent(yearlyGrandTotalSales > 0 ? (yearlyProfitLoss / yearlyGrandTotalSales) * 100 : 0));
            }


            // --- 1. EVENT: SAAT USER MENGETIK (REALTIME FORMATTING) ---
            $(document).on('input', '.input-calc', function() {
                let val = $(this).val();

                if (val === '' || val === 'Rp ') {
                    $(this).val('');
                    $(this).data('raw', '');
                    calculateIncomeStatement();
                    return;
                }

                // Ambil angka murni
                let rawNumber = val.replace(/[^0-9]/g, '');

                if (rawNumber !== '') {
                    $(this).val(formatRupiahRealtime(rawNumber));
                    $(this).data('raw', rawNumber);
                } else {
                    $(this).val('');
                    $(this).data('raw', '');
                }

                // Kalkulasi tabel otomatis saat ngetik (opsional, tapi bagus untuk realtime)
                calculateIncomeStatement();
            });

            // --- 2. EVENT: SAAT KURSOR BERPINDAH (AUTOSAVE) ---
            $(document).on('change', '.input-calc', function() {
                let $input = $(this);
                let itemCode = $input.data('item');
                let month = $input.data('month');
                let rawValue = $input.data('raw');

                // Jika kosong, kirim null. Jika ada isi, kirim raw string numeric-nya.
                let amount = (rawValue === '' || rawValue === undefined) ? null : rawValue;

                if (itemCode && month) {
                    // Indikator Visual Menyimpan (Warna Kuning Muda)
                    $input.css('background-color', '#fff3cd');
                    $input.attr('title', 'Menyimpan...');

                    $.ajax({
                        url: "{{ route('income-statement.store') }}",
                        type: "POST",
                        contentType: "application/json",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({
                            transactions: [{
                                item_code: itemCode,
                                month: month,
                                amount: amount
                            }]
                        }),
                        success: function(response) {
                            // Indikator Visual Berhasil (Warna Hijau Muda, lalu kembali normal)
                            $input.css('background-color', '#d4edda');
                            $input.attr('title', 'Tersimpan');
                            setTimeout(() => {
                                $input.css('background-color', '');
                                $input.removeAttr('title');
                            }, 1000);
                        },
                        error: function(xhr) {
                            // Indikator Visual Error (Warna Merah Muda)
                            $input.css('background-color', '#f8d7da');
                            $input.attr('title', 'Gagal Menyimpan');
                            console.error('Gagal menyimpan otomatis pada sel:', itemCode, 'Bulan:', month);
                        }
                    });
                }
            });

            // Jalankan kalkulasi pertama kali
            calculateIncomeStatement();
        });
    </script>
    @endpush
@endsection
