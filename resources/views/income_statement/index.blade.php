<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Laba Rugi {{ $year }}</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .btn-laporan { margin-bottom: 15px; padding: 10px; cursor: pointer; background-color: #ff0000; color: white; border: none; font-weight: bold; }
    </style>
</head>
<body>

    <button id="btnSaveData" class="btn-save">Simpan Data</button>
    <a href="{{ route('income-statement.laporan') }}" id="btnSaveData" class="btn-laporan">Laporan</a>

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
                    <td><input type="number" class="input-calc sales-training month-{{ $m }}" data-item="sales_training" data-month="{{ $m }}" value="{{ $transactionData['sales_training'][$m] ?? 0 }}"></td>
                @endfor
                <td class="row-total display-currency">0</td>
                <td class="row-avg display-currency">0</td>
                <td class="row-percent"></td>
            </tr>
            <tr>
                <td class="text-left">Discount Penjualan</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td><input type="number" class="input-calc discount month-{{ $m }}" data-item="discount" data-month="{{ $m }}" value="{{ $transactionData['discount'][$m] ?? 0 }}"></td>
                @endfor
                <td class="row-total display-currency">0</td>
                <td class="row-avg display-currency">0</td>
                <td class="row-percent"></td>
            </tr>
            <tr>
                <td class="text-left">Payment Advanced</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td><input type="number" class="input-calc advance month-{{ $m }}" data-item="payment_advance" data-month="{{ $m }}" value="{{ $transactionData['payment_advance'][$m] ?? 0 }}"></td>
                @endfor
                <td class="row-total display-currency">0</td>
                <td class="row-avg display-currency">0</td>
                <td class="row-percent"></td>
            </tr>
            <tr>
                <td class="text-left">Exam</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td><input type="number" class="input-calc exam month-{{ $m }}" data-item="exam" data-month="{{ $m }}" value="{{ $transactionData['exam'][$m] ?? 0 }}"></td>
                @endfor
                <td class="row-total display-currency">0</td>
                <td class="row-avg display-currency">0</td>
                <td class="row-percent"></td>
            </tr>
            <tr class="fw-bold">
                <td class="text-left">Nett Sales Training</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td class="display-currency net-sales month-{{ $m }}">0</td>
                @endfor
                <td class="display-currency total-net-sales">0</td>
                <td class="display-currency avg-net-sales">0</td>
                <td class="percent-net-sales">100.00%</td>
            </tr>
            <tr>
                <td class="text-left">Penjualan Proyek</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td><input type="number" class="input-calc project month-{{ $m }}" data-item="project" data-month="{{ $m }}" value="{{ $transactionData['project'][$m] ?? 0 }}"></td>
                @endfor
                <td class="row-total display-currency">0</td>
                <td class="row-avg display-currency">0</td>
                <td class="row-percent">0.00%</td>
            </tr>
            <tr>
                <td class="text-left">Penjualan Webinar & Sertifikat</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td><input type="number" class="input-calc webinar month-{{ $m }}" data-item="webinar" data-month="{{ $m }}" value="{{ $transactionData['webinar'][$m] ?? 0 }}"></td>
                @endfor
                <td class="row-total display-currency">0</td>
                <td class="row-avg display-currency">0</td>
                <td class="row-percent">0.00%</td>
            </tr>
            <tr class="bg-yellow">
                <td class="text-left">TOTAL PENJUALAN TAHUNAN</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td class="display-currency grand-total-sales month-{{ $m }}">0</td>
                @endfor
                <td class="display-currency grand-total-sales-year">0</td>
                <td class="display-currency grand-total-sales-avg">0</td>
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
                        <td><input type="number" class="input-calc vc-item month-{{ $m }}" data-item="vc_{{ $cost->id }}" data-month="{{ $m }}" value="{{ $transactionData['vc_' . $cost->id][$m] ?? 0 }}"></td>
                    @endfor
                    <td class="row-total display-currency">0</td>
                    <td class="row-avg display-currency">0</td>
                    <td class="row-percent">#DIV/0!</td>
                </tr>
                @endforeach
            @endforeach
            <tr class="bg-blue">
                <td class="text-left">TOTAL VARIABLE COST</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td class="display-currency total-vc month-{{ $m }}">0</td>
                @endfor
                <td class="display-currency grand-total-vc">0</td>
                <td class="display-currency avg-total-vc">0</td>
                <td class="percent-total-vc">#DIV/0!</td>
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
                        <td><input type="number" class="input-calc fc-item month-{{ $m }}" data-item="fc_{{ $cost->id }}" data-month="{{ $m }}" value="{{ $transactionData['fc_' . $cost->id][$m] ?? 0 }}"></td>
                    @endfor
                    <td class="row-total display-currency">0</td>
                    <td class="row-avg display-currency">0</td>
                    <td class="row-percent">#DIV/0!</td>
                </tr>
                @endforeach
            @endforeach
            <tr class="bg-green">
                <td class="text-left">TOTAL FIXED COST</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td class="display-currency total-fc month-{{ $m }}">0</td>
                @endfor
                <td class="display-currency grand-total-fc">0</td>
                <td class="display-currency avg-total-fc">0</td>
                <td class="percent-total-fc">#DIV/0!</td>
            </tr>
        </tbody>

        <tbody id="summaryContainer">
            <tr class="bg-red">
                <td class="text-center">TOTAL PENGELUARAN</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td class="display-currency total-expense month-{{ $m }}">0</td>
                @endfor
                <td class="display-currency grand-total-expense">0</td>
                <td class="display-currency avg-total-expense">0</td>
                <td class="percent-total-expense">0%</td>
            </tr>
            <tr class="bg-orange">
                <td class="text-center">Laba/Rugi</td>
                @for ($m = 1; $m <= 12; $m++)
                    <td class="display-currency profit-loss month-{{ $m }}">0</td>
                @endfor
                <td class="display-currency grand-profit-loss">0</td>
                <td class="display-currency avg-profit-loss">0</td>
                <td class="percent-profit-loss">0%</td>
            </tr>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {

            function formatCurrency(value) {
                if (value === 0 || isNaN(value)) return '-';
                return 'Rp ' + value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function formatPercent(value) {
                if (isNaN(value) || !isFinite(value)) return '0%';
                return value.toFixed(2) + '%';
            }

            function calculateIncomeStatement() {
                let yearlyGrandTotalSales = 0;
                let yearlyGrandTotalVc = 0;
                let yearlyGrandTotalFc = 0;
                let yearlyNetSales = 0;

                let monthlyTotalSalesArray = [];

                for (let m = 1; m <= 12; m++) {
                    let training = parseFloat($(`.sales-training.month-${m}`).val()) || 0;
                    let discount = parseFloat($(`.discount.month-${m}`).val()) || 0;
                    let advance = parseFloat($(`.advance.month-${m}`).val()) || 0;
                    let exam = parseFloat($(`.exam.month-${m}`).val()) || 0;
                    let project = parseFloat($(`.project.month-${m}`).val()) || 0;
                    let webinar = parseFloat($(`.webinar.month-${m}`).val()) || 0;

                    let netSales = training + (discount - advance - exam);
                    let totalSales = netSales + project + webinar;

                    monthlyTotalSalesArray[m] = totalSales;
                    yearlyNetSales += netSales;
                    yearlyGrandTotalSales += totalSales;

                    $(`td.net-sales.month-${m}`).text(formatCurrency(netSales)).data('value', netSales);
                    $(`td.grand-total-sales.month-${m}`).text(formatCurrency(totalSales)).data('value', totalSales);

                    let totalVc = 0;
                    $(`input.vc-item.month-${m}`).each(function() {
                        totalVc += parseFloat($(this).val()) || 0;
                    });
                    yearlyGrandTotalVc += totalVc;
                    $(`td.total-vc.month-${m}`).text(formatCurrency(totalVc)).data('value', totalVc);

                    let totalFc = 0;
                    $(`input.fc-item.month-${m}`).each(function() {
                        totalFc += parseFloat($(this).val()) || 0;
                    });
                    yearlyGrandTotalFc += totalFc;
                    $(`td.total-fc.month-${m}`).text(formatCurrency(totalFc)).data('value', totalFc);

                    let totalExpense = totalVc + totalFc;
                    let profitLoss = totalSales - totalExpense;

                    $(`td.total-expense.month-${m}`).text(formatCurrency(totalExpense)).data('value', totalExpense);
                    $(`td.profit-loss.month-${m}`).text(formatCurrency(profitLoss)).data('value', profitLoss);
                }

                $('table tbody tr').each(function() {
                    let isInputRow = $(this).find('input.input-calc').length > 0;
                    
                    if (isInputRow) {
                        let rowTotal = 0;
                        $(this).find('input.input-calc').each(function() {
                            rowTotal += parseFloat($(this).val()) || 0;
                        });

                        let rowAvg = rowTotal / 12;
                        let rowPercent = yearlyGrandTotalSales > 0 ? (rowTotal / yearlyGrandTotalSales) * 100 : NaN;

                        $(this).find('td.row-total').text(formatCurrency(rowTotal));
                        $(this).find('td.row-avg').text(formatCurrency(rowAvg));
                        
                        let percentCell = $(this).find('td.row-percent');
                        if(percentCell.length > 0) {
                            percentCell.text(formatPercent(rowPercent));
                        }
                    }
                });

                $('.total-net-sales').text(formatCurrency(yearlyNetSales));
                $('.avg-net-sales').text(formatCurrency(yearlyNetSales / 12));
                
                $('.grand-total-sales-year').text(formatCurrency(yearlyGrandTotalSales));
                $('.grand-total-sales-avg').text(formatCurrency(yearlyGrandTotalSales / 12));

                $('.grand-total-vc').text(formatCurrency(yearlyGrandTotalVc));
                $('.avg-total-vc').text(formatCurrency(yearlyGrandTotalVc / 12));
                $('.percent-total-vc').text(formatPercent((yearlyGrandTotalVc / yearlyGrandTotalSales) * 100));

                $('.grand-total-fc').text(formatCurrency(yearlyGrandTotalFc));
                $('.avg-total-fc').text(formatCurrency(yearlyGrandTotalFc / 12));
                $('.percent-total-fc').text(formatPercent((yearlyGrandTotalFc / yearlyGrandTotalSales) * 100));

                let yearlyGrandExpense = yearlyGrandTotalVc + yearlyGrandTotalFc;
                $('.grand-total-expense').text(formatCurrency(yearlyGrandExpense));
                $('.avg-total-expense').text(formatCurrency(yearlyGrandExpense / 12));
                $('.percent-total-expense').text(formatPercent((yearlyGrandExpense / yearlyGrandTotalSales) * 100));

                let yearlyProfitLoss = yearlyGrandTotalSales - yearlyGrandExpense;
                $('.grand-profit-loss').text(formatCurrency(yearlyProfitLoss));
                $('.avg-profit-loss').text(formatCurrency(yearlyProfitLoss / 12));
                $('.percent-profit-loss').text(formatPercent((yearlyProfitLoss / yearlyGrandTotalSales) * 100));
            }

            $(document).on('input', '.input-calc', function() {
                calculateIncomeStatement();
            });

            $('#btnSaveData').on('click', function() {
                let transactions = [];
                
                $('.input-calc').each(function() {
                    let itemCode = $(this).data('item');
                    let month = $(this).data('month');
                    let amount = parseFloat($(this).val()) || 0;
                    
                    if(itemCode && month) {
                        transactions.push({
                            item_code: itemCode,
                            month: month,
                            amount: amount
                        });
                    }
                });

                $.ajax({
                    url: "{{ route('income-statement.store') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        transactions: transactions
                    },
                    success: function(response) {
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan saat menyimpan data.');
                    }
                });
            });

            // Eksekusi kalkulasi berdasarkan nilai yang dimuat dari database
            calculateIncomeStatement();
        });
    </script>
</body>
</html>