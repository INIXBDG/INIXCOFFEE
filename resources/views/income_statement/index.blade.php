@extends('layouts.app')

@section('content')
    {{-- <div class="container-fluid income-statement-page"> --}}
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

    <div class="income-statement-scroll">
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
                            <td><input type="text" class="input-calc vc-item month-{{ $m }}" data-item="{{ $cost->item_code }}" data-month="{{ $m }}" value="{{ number_format($transactionData['vc_' . $cost->id][$m] ?? 0, 2, ',', '.') }}"></td>
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
            </tbody>s

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
                            <td><input type="text" class="input-calc fc-item month-{{ $m }}" data-item="{{ $cost->item_code }}" data-month="{{ $m }}" value="{{ number_format($transactionData['fc_' . $cost->id][$m] ?? 0, 2, ',', '.') }}"></td>
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
        :root {
            --pastel-border: #d8dee9;
            --pastel-header: #e8eef7;
            --pastel-yellow: #fff3c4;
            --pastel-blue: #d9eef7;
            --pastel-green: #dcefdc;
            --pastel-red: #f8d7da;
            --pastel-orange: #ffe4c7;
            --pastel-primary: #8fb8d8;
            --pastel-primary-dark: #6f9fbe;
            --pastel-danger: #d9959b;
        }

        body { font-family: sans-serif; font-size: 12px; color: #354052; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid var(--pastel-border); padding: 4px; text-align: right; vertical-align: middle; }
        th { background-color: var(--pastel-header); text-align: center; font-weight: bold; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        
        .bg-yellow { background-color: var(--pastel-yellow) !important; font-weight: bold; }
        .bg-blue { background-color: var(--pastel-blue) !important; font-weight: bold; }
        .bg-green { background-color: var(--pastel-green) !important; font-weight: bold; }
        .bg-red { background-color: var(--pastel-red) !important; color: #6f3f43; font-weight: bold; }
        .bg-orange { background-color: var(--pastel-orange) !important; font-weight: bold; }
        .fw-bold { font-weight: bold; }
        
        input { width: 130px; text-align: right; border: 1px solid var(--pastel-border); padding: 2px; color: #354052; background-color: #fbfcfe; }
        input:focus { border: 1px solid var(--pastel-primary-dark); outline: 2px solid #dcebf5; }
        .btn-save, .btn-laporan { margin-bottom: 15px; padding: 10px; cursor: pointer; color: #29445a; border: none; font-weight: bold; border-radius: 4px; }
        .btn-save { background-color: var(--pastel-primary); }
        .btn-save:hover { background-color: var(--pastel-primary-dark); color: #fff; }
        .btn-laporan { background-color: var(--pastel-danger); color: #60383c; text-decoration: none; display: inline-block; }
        .btn-laporan:hover { background-color: #c77f86; color: #fff; }
        .income-statement-page { max-width: 100%; }
        .income-statement-scroll {
            width: 100%;
            max-width: 100%;
            max-height: calc(100vh - 170px);
            overflow: auto;
            padding-bottom: 20px;
            -webkit-overflow-scrolling: touch;
            scrollbar-color: var(--pastel-primary) var(--pastel-header);
            scrollbar-width: auto;
        }
        .income-statement-scroll::-webkit-scrollbar { width: 12px; height: 12px; }
        .income-statement-scroll::-webkit-scrollbar-track { background: var(--pastel-header); border-radius: 6px; }
        .income-statement-scroll::-webkit-scrollbar-thumb { background: var(--pastel-primary); border-radius: 6px; }
        #incomeStatementTable { min-width: 1900px; margin-bottom: 16px; }
    </style>
    @push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            // --- FUNGSI FORMATTING ---
            function formatCurrency(value) {
                if (isNaN(value) || !isFinite(value)) return 'Rp 0,00';
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(value);
            }

            function parseInputValue(value) {
                let normalizedValue = String(value ?? '').trim()
                    .replace(/^-?Rp\s*/i, match => match.startsWith('-') ? '-' : '')
                    .replace(/\s/g, '');

                if (normalizedValue.includes(',')) {
                    normalizedValue = normalizedValue.replace(/\./g, '').replace(',', '.');
                } else if (/^-?\d{1,3}(?:\.\d{3})+$/.test(normalizedValue)) {
                    normalizedValue = normalizedValue.replace(/\./g, '');
                }

                return parseFloat(normalizedValue) || 0;
            }

            function formatInputNumber(value) {
                let rawValue = String(value ?? '').replace(/\s/g, '');
                let isNegative = rawValue.startsWith('-');
                rawValue = rawValue.replace(/[^0-9,]/g, '');

                let commaIndex = rawValue.indexOf(',');
                let integerPart = commaIndex >= 0 ? rawValue.slice(0, commaIndex) : rawValue;
                let decimalPart = commaIndex >= 0 ? rawValue.slice(commaIndex + 1).replace(/,/g, '') : '';

                integerPart = integerPart.replace(/^0+(?=\d)/, '') || '0';
                integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                return (isNegative ? '-' : '') + integerPart + (commaIndex >= 0 ? ',' + decimalPart : '');
            }

            function formatInputFromValue(value) {
                return formatInputNumber(Number(value).toFixed(2).replace('.', ','));
            }

            function formatPercent(value) {
                if (isNaN(value) || !isFinite(value) || value === 0) return '0.00%';
                return value.toFixed(2) + '%';
            }

            $('.input-calc').each(function() {
                let value = $(this).val();
                $(this).val(value ? formatInputNumber(value) : '');
                $(this).data('raw', value ? parseInputValue(value) : '');
            });

            function calculateIncomeStatement() {
                let yearlyGrandTotalSales = 0;
                let yearlyGrandTotalVc = 0;
                let yearlyGrandTotalFc = 0;
                let yearlyNetSales = 0;

                for (let m = 1; m <= 12; m++) {
                    let training = parseInputValue($(`.sales-training.month-${m}`).val());
                    let discount = parseInputValue($(`.discount.month-${m}`).val());
                    let advance = parseInputValue($(`.advance.month-${m}`).val());
                    let exam = parseInputValue($(`.exam.month-${m}`).val());
                    let project = parseInputValue($(`.project.month-${m}`).val());
                    let webinar = parseInputValue($(`.webinar.month-${m}`).val());

                    let netSales = training - (discount + advance + exam);
                    let totalSales = netSales + project + webinar;

                    yearlyNetSales += netSales;
                    yearlyGrandTotalSales += totalSales;

                    $(`td.net-sales.month-${m}`).text(formatCurrency(netSales));
                    $(`td.grand-total-sales.month-${m}`).text(formatCurrency(totalSales));

                    let totalVc = 0;
                    $(`input.vc-item.month-${m}`).each(function() {
                        totalVc += parseInputValue($(this).val());
                    });
                    yearlyGrandTotalVc += totalVc;
                    $(`td.total-vc.month-${m}`).text(formatCurrency(totalVc));

                    let totalFc = 0;
                    $(`input.fc-item.month-${m}`).each(function() {
                        totalFc += parseInputValue($(this).val());
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
                            rowTotal += parseInputValue($(this).val());
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

            // Format angka saat mengetik dan simpan nilai numeriknya untuk autosave.
            $(document).on('input', '.input-calc', function() {
                let input = this;
                let currentValue = input.value;

                if (!/[+\-*/]/.test(currentValue.replace(/^-/, ''))) {
                    let caretPosition = input.selectionStart;
                    let formattedValue = formatInputNumber(currentValue);
                    let formattedBeforeCaret = formatInputNumber(currentValue.slice(0, caretPosition));

                    input.value = formattedValue;
                    input.setSelectionRange(formattedBeforeCaret.length, formattedBeforeCaret.length);
                }

                $(this).data('raw', input.value.trim() === '' ? '' : parseInputValue(input.value));
                calculateIncomeStatement();
            });

            function saveInput(input) {
                let $input = $(input);
                let itemCode = $input.data('item');
                let month = $input.data('month');
                let rawValue = $input.data('raw');

                let amount = (rawValue === '' || rawValue === undefined) ? null : rawValue;

                if (itemCode && month) {
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
                            $input.css('background-color', '#d4edda');
                            $input.attr('title', 'Tersimpan');
                            setTimeout(() => {
                                $input.css('background-color', '');
                                $input.removeAttr('title');
                            }, 1000);
                        },
                        error: function(xhr) {
                            $input.css('background-color', '#f8d7da');
                            $input.attr('title', 'Gagal Menyimpan');
                            console.error('Gagal menyimpan otomatis pada sel:', itemCode, 'Bulan:', month);
                        }
                    });
                }
            }

            $(document).on('change', '.input-calc', function() {
                saveInput(this);
            });

            $(document).on('keydown', '.input-calc', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    saveInput(this);
                }
            });

            // Jalankan kalkulasi pertama kali
            calculateIncomeStatement();
        });
    </script>
    @endpush
@endsection
