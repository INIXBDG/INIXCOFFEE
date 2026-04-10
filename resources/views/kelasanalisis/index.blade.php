@extends('layouts.app')

@section('content')
    <div class="container-fluid">
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
        <div class="modal fade" id="modalAnalisis" tabindex="-1" aria-labelledby="modalAnalisisLabel" aria-hidden="true"
            aria-modal="true">
            <div class="modal-dialog" style="max-width: 90% !important">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalAnalisisLabel">Analisis</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="dataRKM"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="submitForm" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalAnalisaMargin" tabindex="-1" aria-labelledby="modalAnalisaMarginLabel"
            aria-hidden="true" aria-modal="true">
            <div class="modal-dialog" style="max-width: 90% !important">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalAnalisaMarginLabel">Analisa Margin</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="analisaMargin">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th>MINGGU</th>
                                        <th>MINUS</th>
                                        <th>PROFIT</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <th>TOTAL</th>
                                        <th id="overallSubtotal" colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{-- <button type="button" id="submitForm" class="btn btn-primary">Submit</button> --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body d-flex justify-content-start">
            <div class="col-md-3 mx-1">
                <label for="tahun" class="form-label">Tahun</label>
                <select id="tahun" class="form-select" aria-label="tahun">
                    <option disabled>Pilih Tahun</option>
                    @php
                        $tahun_sekarang = now()->year;
                        for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                            $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                            echo "<option value=\"$tahun\" $selected>$tahun</option>";
                        }
                    @endphp
                </select>
            </div>
            <div class="col-md-4 mx-1">
                <label for="bulanRange" class="form-label">Bulan</label>
                <select id="bulanRange" class="form-select" aria-label="bulanRange">
                    <option disabled>Pilih Bulan</option>
                    @php
                        $bulan_sekarang = now()->month;
                        $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        for ($bulan = 1; $bulan <= 12; $bulan++) {
                            $bulan_awal = $nama_bulan[$bulan - 1];
                            $bulan_akhir = $nama_bulan[$bulan % 12];
                            $value_bulan = $bulan . '-' . (($bulan % 12) + 1);
                            $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                            echo "<option value=\"$value_bulan\" $selected>$bulan_awal - $bulan_akhir</option>";
                        }
                    @endphp
                </select>
            </div>
            <div class="col-md-4 mx-1">
                <button type="submit" onclick="getData()" class="btn click-primary" style="margin-top: 37px">Cari
                    Data</button>
                @if (auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Koordinator Office')
                    <button type="button" onclick="sinkronData()" class="btn click-primary"
                        style="margin-top: 37px">Sinkron Data</button>
                @endif
            </div>
        </div>
        <div id="content"></div> <!-- Pastikan elemen ini ada -->


        <style>
            .loader {
                position: relative;
                text-align: center;
                margin: 15px auto 35px auto;
                z-index: 9999;
                display: block;
                width: 80px;
                height: 80px;
                border: 10px solid rgba(0, 0, 0, .3);
                border-radius: 50%;
                border-top-color: #000;
                animation: spin 1s ease-in-out infinite;
                -webkit-animation: spin 1s ease-in-out infinite;
            }

            @keyframes spin {
                to {
                    -webkit-transform: rotate(360deg);
                }
            }

            @-webkit-keyframes spin {
                to {
                    -webkit-transform: rotate(360deg);
                }
            }

            .modal-content {
                border-radius: 0px;
                box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
            }

            .modal-backdrop.show {
                opacity: 0.75;
            }

            .loader-txt {
                p {
                    font-size: 13px;
                    color: #666;

                    small {
                        font-size: 11.5px;
                        color: #999;
                    }
                }
            }

            #content table {
                display: table !important;
                visibility: visible !important;
                width: 100%;
                border-collapse: collapse;
            }

            #content table th,
            #content table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
            }
        </style>
        @push('js')
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
            <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
            <script>
                $(document).ready(function() {
                    const savedYear = localStorage.getItem('selectedYear');
                    if (savedYear) {
                        $('#tahun').val(savedYear);
                    }

                    // Cek dan set value bulanRange
                    const savedMonth = localStorage.getItem('selectedMonth');
                    if (savedMonth) {
                        $('#bulanRange').val(savedMonth);
                    }

                    // Simpan pilihan saat dropdown berubah
                    $('#tahun').on('change', function() {
                        localStorage.setItem('selectedYear', this.value);
                        getData();
                    });

                    $('#bulanRange').on('change', function() {
                        localStorage.setItem('selectedMonth', this.value);
                        getData();
                    });
                });

                function analisaMargin(tahun, bulan) {
                    $.ajax({
                        url: '/analisisrkm/' + tahun + '/' + bulan,
                        type: 'GET',
                        success: function(response) {
                            var html = '';
                            var totalMinus = 0;
                            var totalProfit = 0;
                            var dataRKM = response.data.weeklyProfit;

                            $.each(dataRKM, function(week, value) {
                                var minusValue = '';
                                var profitValue = '';
                                if (value < 0) {
                                    minusValue = formatRupiah(value, true);
                                    totalMinus += value;
                                } else {
                                    profitValue = formatRupiah(value);
                                    totalProfit += value;
                                }

                                html += `<tr>
                                    <td>${week}</td>
                                    <td class="text-danger">${minusValue}</td>
                                    <td>${profitValue}</td>
                                </tr>`;
                            });
                            var overallSubtotal = totalMinus + totalProfit;
                            $('#tableBody').html(html);
                            $('#totalMinus').html(`<span class="text-danger">${formatRupiah(totalMinus, true)}</span>`);
                            $('#totalProfit').html(formatRupiah(totalProfit));
                            $('#overallSubtotal').html(formatRupiah(overallSubtotal, overallSubtotal < 0));
                            $('#modalAnalisaMargin').modal('show');
                        },
                    });
                }

                function analisisData(formId, tahun, bulan, minggu) {
                    console.log(`Form ID: ${formId}`);
                    console.log(`Year: ${tahun}, Month: ${bulan}, Week: ${minggu}`);

                    $.ajax({
                        url: '/getAnalisisRKM/' + tahun + '/' + bulan + '/' + minggu,
                        type: 'GET',
                        success: function(response) {
                            var html = '';
                            var totalNettPenjualan = 0;
                            var dataRKM = response.data.data;
                            console.log(response);
                            // console.log(dataRKM[0].analisisrkmmingguan.data);
                            if (!dataRKM[0].analisisrkmmingguan.data || dataRKM[0].analisisrkmmingguan.data.length ===
                                0 || dataRKM[0].analisisrkmmingguan.data == null) {
                                html += '<form id="kirimData" method="POST" action="/analisisrkm/' + tahun + '/' +
                                    bulan + '/' + minggu + '/post">';
                                html += '@csrf';
                                html += '<table class="table table-bordered">';
                                html += '<thead>';
                                html += '<tr>';
                                html += '<th scope="col" style="font-size: 14px;text-align: center;">No</th>';
                                html += '<th scope="col" style="font-size: 14px;text-align: center;">Kelas</th>';
                                html +=
                                    '<th scope="col" style="font-size: 14px;text-align: center;">Nett Penjualan</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';
                                var nettPenjualanArray = [];

                                $.each(response.data.data, function(index, value) {
                                    var nett_penjualan = value.analisisrkm.nett_penjualan;
                                    var kelasId = value.analisisrkm.id;
                                    nettPenjualanArray.push(Math.floor(nett_penjualan));
                                    html += '<tr>';
                                    html +=
                                        '<input type="hidden" class="form-control" name="id_kelasanalisis[]" value="' +
                                        (kelasId) + '">';
                                    html += '<input type="hidden" class="form-control" name="tahun[]" value="' +
                                        (tahun) + '">';
                                    html += '<input type="hidden" class="form-control" name="bulan[]" value="' +
                                        (bulan) + '">';
                                    html +=
                                        '<input type="hidden" class="form-control" name="minggu[]" value="' + (
                                            minggu) + '">';
                                    html +=
                                        '<input type="hidden" class="form-control" name="nama_materi[]" value="' +
                                        (value.nama_materi) + '">';
                                    html +=
                                        '<input type="hidden" class="form-control" name="nett_penjualan[]" value="' +
                                        (nett_penjualan) + '">';
                                    html += '<td style="font-size: 14px;">' + (index + 1) + '</td>';
                                    html += '<td style="font-size: 14px;">' + (value.nama_materi) + '</td>';
                                    html += '<td style="font-size: 14px;">' + formatWithoutDecimals(
                                        nett_penjualan) + '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody>';
                                html += '</table>';
                                html += '<div class="row">';
                                html += '<div class="col-md-6">';
                                html += '<label>Fix Cost:</label>';
                                html += '<input type="text" class="form-control fixcost" name="fixcost">';
                                html += '</div>';
                                html += '<div class="col-md-6">';
                                html += '<label>Profit:</label>';
                                html +=
                                    '<input type="text" class="form-control profit" id="profit" name="profit" readonly>';
                                html += '</div>';
                                html += '</div>';
                                html += '</form>';
                            } else {
                                var dataRKM = response.data.data;
                                var firstFixcost = dataRKM[0]?.analisisrkmmingguan?.data?.[0]?.fixcost ?? 'N/A';
                                var firstProfit = dataRKM[0]?.analisisrkmmingguan?.data?.[0]?.profit ?? 'N/A';
                                var allSameFixcost = dataRKM.every(item => item.analisisrkmmingguan?.data?.[0]
                                    ?.fixcost === firstFixcost);
                                var allSameProfit = dataRKM.every(item => item.analisisrkmmingguan?.data?.[0]
                                    ?.profit === firstProfit);

                                var groupedData = {};
                                var totalNettPenjualanAll = 0;
                                console.log(dataRKM);
                                $.each(dataRKM, function(index, item) {
                                    var materi = item.nama_materi;
                                    var nettPenjualan = parseFloat(item.analisisrkm?.nett_penjualan ?? 0);
                                    var komentar = item.komentar;
                                    if (!groupedData[materi]) {
                                        groupedData[materi] = {
                                            items: [],
                                            totalNettPenjualan: 0
                                        };
                                    }

                                    groupedData[materi].items.push(item);
                                    groupedData[materi].totalNettPenjualan += nettPenjualan;
                                    totalNettPenjualanAll += nettPenjualan;
                                });

                                var html = '<table class="table table-bordered">';
                                html += '<thead>';
                                html += '<tr>';
                                html += '<th scope="col" style="font-size: 14px;text-align: center;">No</th>';
                                html += '<th scope="col" style="font-size: 14px;text-align: center;">Kelas</th>';
                                html +=
                                    '<th scope="col" style="font-size: 14px;text-align: center;">Total Nett Penjualan</th>';
                                html += '<th scope="col" style="font-size: 14px;text-align: center;">Fix Cost</th>';
                                html += '<th scope="col" style="font-size: 14px;text-align: center;">Profit</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';

                                var rowIndex = 1;
                                var commentsArray = [];
                                $.each(groupedData, function(materi, group) {
                                    $.each(group.items, function(itemIndex, item) {
                                        var komentar = item.analisisrkm.komentar ??
                                            'Tidak ada komentar';
                                        commentsArray.push(komentar);
                                    });
                                    var firstItem = group.items[0];
                                    var fixcost = firstItem.analisisrkmmingguan?.data?.[0]?.fixcost ?? 'N/A';
                                    var profit = firstItem.analisisrkmmingguan?.data?.[0]?.profit ?? 'N/A';
                                    var komentar = firstItem.komentar ??
                                        'Tidak ada komentar';
                                    html += '<tr>';
                                    html += '<td style="font-size: 14px;">' + rowIndex + '</td>';
                                    html += '<td style="font-size: 14px;">' + materi + '</td>';
                                    html += '<td style="font-size: 14px;">' + formatWithoutDecimals(group
                                        .totalNettPenjualan) + '</td>';

                                    if (rowIndex === 1 && allSameFixcost) {
                                        html +=
                                            '<td style="font-size: 14px;text-align:center;padding: 70px 0;" rowspan="' +
                                            Object.keys(groupedData).length + '">' + formatWithoutDecimals(
                                                firstFixcost) + '</td>';
                                    } else if (!allSameFixcost) {
                                        html +=
                                            '<td style="font-size: 14px;text-align:center;padding: 70px 0;">' +
                                            fixcost + '</td>';
                                    }

                                    if (rowIndex === 1 && allSameProfit) {
                                        html +=
                                            '<td style="font-size: 14px;text-align:center;padding: 70px 0;" rowspan="' +
                                            Object.keys(groupedData).length + '">' + formatWithoutDecimals(
                                                firstProfit) + '</td>';
                                    } else if (!allSameProfit) {
                                        html +=
                                            '<td style="font-size: 14px;text-align:center;padding: 70px 0;">' +
                                            profit + '</td>';
                                    }

                                    html += '</tr>';
                                    rowIndex++;
                                });

                                html += '</tbody>';
                                html += '<tfoot>';
                                html += '<tr>';
                                html +=
                                    '<td colspan="2" style="font-size: 14px; text-align: right; font-weight: bold;">Total</td>';
                                html += '<td style="font-size: 14px; font-weight: bold;">' + formatWithoutDecimals(
                                    totalNettPenjualanAll) + '</td>';
                                html += '<td colspan="2"></td>';
                                html += '</tr>';
                                html += '<tr>';
                                html +=
                                    '<td colspan="2" style="font-size: 14px; text-align: right; font-weight: bold;">Keterangan</td>';
                                html += '<td colspan="3"></td>';
                                html += '</tr>';
                                $.each(commentsArray, function(index, komentar) {
                                    html += '<tr>';
                                    html += '<td colspan="2"></td>';
                                    html += '<td colspan="3" style="font-size: 14px;">' + komentar + '</td>';
                                    html += '</tr>';
                                });
                                html += '<tr>';
                                html += '<td colspan="5" style="text-align: center;">';
                                html +=
                                    '<button type="button" class="btn btn-primary" id="updateButton">Update Data</button>';
                                html += '</td>';
                                html += '</tr>';
                                html += '</tfoot>';
                                html += '</table>';
                                $('#submitForm').prop('hidden', true);
                            }
                            $('#dataRKM').html(html);
                            $('#modalAnalisis').modal('show');
                            $('#updateButton').on('click', function() {
                                $("#dataRKM").empty();
                                analisisDataUpdate(formId, tahun, bulan, minggu);
                            });
                            $('.fixcost').on('input', function() {
                                var fixcostFormatted = $(this).val();
                                var fixcost = parseFloat(fixcostFormatted.replace(/[^0-9,-]+/g, ""));
                                var totalNettPenjualan = nettPenjualanArray.reduce((a, b) => a + b, 0);
                                var profit = totalNettPenjualan - fixcost;
                                $(this).val(formatRupiah(fixcost));
                                $('#profit').val(formatRupiah(profit));
                                $('#submitForm').prop('hidden', false);
                            });
                            $('#submitForm').on('click', function() {
                                $('#kirimData').submit();
                            });
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                        }
                    });
                }

                function analisisDataUpdate(formId, tahun, bulan, minggu) {
                    console.log(`Form ID: ${formId}`);
                    console.log(`Year: ${tahun}, Month: ${bulan}, Week: ${minggu}`);

                    $.ajax({
                        url: '/analisisrkm/' + tahun + '/' + bulan + '/' + minggu,
                        type: 'GET',
                        success: function(response) {
                            var html = '';
                            var nettPenjualanArray = [];
                            var totalNettPenjualan = 0;
                            var dataRKM = response.data.data;

                            if (dataRKM[0].analisisrkmmingguan?.data && dataRKM[0].analisisrkmmingguan.data.length >
                                0) {
                                html += '<form id="updateData" method="POST" action="/analisisrkm/' + tahun + '/' +
                                    bulan + '/' + minggu + '/update">';
                                html += '@csrf';
                                html += '@method('PUT')';
                                html += '<table class="table table-bordered">';
                                html += '<thead>';
                                html += '<tr>';
                                html += '<th scope="col" style="font-size: 14px;text-align: center;">No</th>';
                                html += '<th scope="col" style="font-size: 14px;text-align: center;">Kelas</th>';
                                html +=
                                    '<th scope="col" style="font-size: 14px;text-align: center;">Nett Penjualan</th>';
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';

                                $.each(dataRKM, function(index, value) {
                                    var nett_penjualan = parseFloat(value.analisisrkm.nett_penjualan) || 0;
                                    var kelasId = value.analisisrkm.id;
                                    nettPenjualanArray.push(nett_penjualan);
                                    html += '<tr>';
                                    html +=
                                        '<input type="hidden" class="form-control" name="id_kelasanalisis[]" value="' +
                                        (kelasId) + '">';
                                    html += '<input type="hidden" class="form-control" name="tahun[]" value="' +
                                        (tahun) + '">';
                                    html += '<input type="hidden" class="form-control" name="bulan[]" value="' +
                                        (bulan) + '">';
                                    html +=
                                        '<input type="hidden" class="form-control" name="minggu[]" value="' + (
                                            minggu) + '">';
                                    html +=
                                        '<input type="text" class="form-control" name="nama_materi[]" value="' +
                                        (value.nama_materi) + '" readonly>';
                                    html +=
                                        '<input type="number" class="form-control" name="nett_penjualan[]" value="' +
                                        (nett_penjualan) + '">';
                                    html += '<td style="font-size: 14px;">' + (index + 1) + '</td>';
                                    html += '<td style="font-size: 14px;">' + (value.nama_materi) + '</td>';
                                    html += '<td style="font-size: 14px;">' + formatWithoutDecimals(
                                        nett_penjualan) + '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody>';
                                html += '</table>';
                                html += '<div class="row">';
                                html += '<div class="col-md-6">';
                                html += '<label>Fix Cost:</label>';
                                html += '<input type="text" class="form-control fixcost" name="fixcost" value="' + (
                                    dataRKM[0].analisisrkmmingguan?.data[0]?.fixcost || '') + '">';
                                html += '</div>';
                                html += '<div class="col-md-6">';
                                html += '<label>Profit:</label>';
                                html +=
                                    '<input type="text" class="form-control profit" id="profit" name="profit" readonly value="' +
                                    (dataRKM[0].analisisrkmmingguan?.data[0]?.profit || '') + '">';
                                html += '</div>';
                                html += '</div>';
                                html +=
                                    '<button type="button" class="btn btn-primary" id="submitUpdateForm">Update Data</button>';
                                html += '</form>';
                            } else {
                                html += '<form id="kirimData" method="POST" action="/analisisrkm/' + tahun + '/' +
                                    bulan + '/' + minggu + '/post">';
                                html += '@csrf';
                                html += '<table class="table table-bordered">';
                                html += '</form>';
                            }

                            $('#dataRKM').html(html);
                            $('#modalAnalisis').modal('show');
                            $('#submitUpdateForm').on('click', function() {
                                $('#updateData').submit();
                            });
                            $('.fixcost').on('input', function() {
                                var fixcostFormatted = $(this).val();
                                var fixcost = parseFloat(fixcostFormatted.replace(/[^0-9,-]+/g, ""));
                                var totalNettPenjualan = nettPenjualanArray.reduce((a, b) => a + b, 0);
                                var profit = totalNettPenjualan - fixcost;
                                $(this).val(formatRupiah(fixcost));
                                $('#profit').val(formatRupiah(profit));
                            });
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                        }
                    });
                }

                function sinkronData() {
                    var tahun = document.getElementById('tahun').value;
                    var bulanRange = document.getElementById('bulanRange').value;

                    if (!tahun || !bulanRange) {
                        alert("Mohon pilih tahun dan rentang bulan terlebih dahulu.");
                        return;
                    }

                    var [bulanAwal, bulanAkhir] = bulanRange.split('-');

                    if (isNaN(bulanAwal) || isNaN(bulanAkhir)) {
                        alert("Rentang bulan tidak valid.");
                        return;
                    }
                    $.ajax({
                        url: `sinkronDataKelasAnalisis/${tahun}/${bulanAwal}/${bulanAkhir}`,
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function() {
                            $('#loadingModal').modal('show');
                            $('#loadingModal').on('show.bs.modal', function() {
                                $('#loadingModal').removeAttr('inert');
                            });
                        },
                        complete: function() {
                            setTimeout(() => {
                                $('#loadingModal').modal('hide');
                                $('#loadingModal').on('hidden.bs.modal', function() {
                                    $('#loadingModal').attr('inert', true);
                                });
                            }, 1000);
                        },
                        success: function(response) {
                            $('#loadingModal').modal('hide');
                            alert(response.message);
                            getData();
                        },
                        error: function() {
                            $('#loadingModal').modal('hide');
                            alert("Error fetching data. Please try again.");
                        }
                    });
                }

                function getData() {
                    var tahun = document.getElementById('tahun').value;
                    var bulanRange = document.getElementById('bulanRange').value;
                    console.log(bulanRange);
                    if (!tahun || !bulanRange) {
                        alert("Mohon pilih tahun dan rentang bulan terlebih dahulu.");
                        return;
                    }

                    var [bulanAwal, bulanAkhir] = bulanRange.split('-');

                    if (isNaN(bulanAwal) || isNaN(bulanAkhir)) {
                        alert("Rentang bulan tidak valid.");
                        return;
                    }

                    $('#loadingModal').modal('show');

                    $.ajax({
                        url: `analisisrkm/${tahun}/${bulanAwal}/${bulanAkhir}`,
                        method: 'GET',
                        dataType: 'json',
                        beforeSend: function() {
                            $('#loadingModal').modal('show');
                            $('#loadingModal').on('show.bs.modal', function() {
                                $('#loadingModal').removeAttr('inert');
                            });
                        },
                        complete: function() {
                            setTimeout(() => {
                                $('#loadingModal').modal('hide');
                                $('#loadingModal').on('hidden.bs.modal', function() {
                                    $('#loadingModal').attr('inert', true);
                                    console.log("Modal loading closed, table should be rendered");
                                });
                            }, 1000);
                        },
                        success: function(response) {
                            console.log("Response from server: ", response);
                            if (!response || !response.success || !response.data || !Array.isArray(response.data)) {
                                console.error("Invalid or unsuccessful response: ", response);
                                alert("Data tidak valid atau tidak ditemukan. Silakan cek server.");
                                $('#content').html(
                                    '<div class="alert alert-danger">Tidak ada data untuk ditampilkan.</div>');
                                $('#loadingModal').modal('hide');
                                return;
                            }

                            var html = '';
                            var jabatan = "{{ auth()->user()->jabatan }}";

                            response.data.forEach(function(monthData) {
                                var monthName = monthData.month;
                                console.log("Processing month: ", monthName);
                                html += '<h4>' + monthName + '</h4>';
                                if (jabatan == 'HRD') {
                                    html +=
                                        `<button type="button" class="btn click-primary p-2" onclick="analisaMargin('${tahun}', '${monthName}')">Analisa Margin</button>`;
                                }

                                monthData.weeksData.forEach(function(weekData) {
                                    console.log(`Processing week ${weekData.minggu} in ${monthName}: `,
                                        weekData);
                                    html += '<div class="card my-1">';
                                    html += '<div class="card-body table-responsive">';
                                    html +=
                                        `<h3 class="card-title my-1">Rencana Kelas Mingguan ${monthName} (Minggu ke - ${weekData.minggu}) ${weekData.tanggal_awal_minggu} - ${weekData.tanggal_akhir_minggu}</h3>`;

                                    if (weekData.rkmfull === "ok" && jabatan == 'HRD') {
                                        var formId =
                                            `analisisForm_${weekData.tahun}_${weekData.bulan}_${weekData.minggu}`;
                                        html += `<form id="${formId}">`;
                                        html +=
                                            `<button type="button" class="btn click-primary p-2" data-bs-toggle="modal" data-bs-target="#modalAnalisis" onclick="analisisData('${formId}', '${weekData.tahun}', '${weekData.bulan}', '${weekData.minggu}')">Analisis</button>`;
                                        html +=
                                            `<input type="hidden" name="tahunRKM" value="${weekData.tahun}">`;
                                        html +=
                                            `<input type="hidden" name="bulanRKM" value="${weekData.bulan}">`;
                                        html +=
                                            `<input type="hidden" name="mingguRKM" value="${weekData.minggu}">`;
                                        html += `</form>`;
                                    }

                                    if (!weekData.data || !Array.isArray(weekData.data) || weekData.data
                                        .length === 0) {
                                        console.log(
                                            `No data for week ${weekData.minggu} in ${monthName}`);
                                        html += '<p class="text-center">Tidak Ada Kelas Mingguan</p>';
                                    } else {
                                        console.log(
                                            `Rendering table for week ${weekData.minggu} with data: `,
                                            weekData.data);
                                        html += renderTable(weekData.data, jabatan);
                                    }

                                    html += '</div></div>';
                                });
                            });

                            if ($('#content').length === 0) {
                                console.error("Element #content not found in DOM");
                                alert("Kesalahan: Kontainer tabel tidak ditemukan.");
                                $('body').append('<div id="content"></div>');
                            }
                            // console.log("Generated HTML: ", html);
                            $('#content').html(html);
                            // console.log("Content after insertion: ", $('#content').html());
                            $('#loadingModal').modal('hide');
                        },
                        error: function(xhr) {
                            console.error("AJAX error: ", xhr.responseJSON || xhr.responseText);
                            alert("Gagal mengambil data: " + (xhr.responseJSON?.message ||
                                "Kesalahan server. Silakan coba lagi."));
                            $('#content').html('<div class="alert alert-danger">Gagal memuat data: ' + (xhr.responseJSON
                                ?.message || "Kesalahan server") + '</div>');
                            $('#loadingModal').modal('hide');
                        }
                    });
                }

                function renderTable(data, jabatan) {
                    console.log("Render table data: ", data);
                    if (!data || !Array.isArray(data) || data.length === 0) {
                        console.warn("No valid data to render table");
                        return '<div class="alert alert-info">Tidak ada data untuk ditampilkan.</div>';
                    }

                    var html = '<table class="table table-bordered">';
                    html += '<thead><tr>';
                    html += '<th rowspan="2" style="text-align:center;">No</th>';
                    html += '<th rowspan="2" style="text-align:center;">Kelas</th>';
                    html += '<th rowspan="2" style="text-align:center;">Pax</th>';
                    html += '<th rowspan="2" style="text-align:center;">Durasi</th>';
                    html += '<th rowspan="2" style="text-align:center;">Harga Nett Jual (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Total Harga Jual (Rp.)</th>';
                    html += '<th colspan="4" style="text-align:center;">Harga Modul</th>';
                    html += '<th rowspan="2" style="text-align:center;">Konsumsi (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Souvenir (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Transportasi</th>';
                    html += '<th rowspan="2" style="text-align:center;">PA/Hotel (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Exam (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">PC (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Total Fee Instruktur (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Alat (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Fee Instruktur / Hours (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Total (Rp.)</th>';
                    html += '<th rowspan="2" style="text-align:center;">Persentase (%)</th>';
                    if (jabatan === 'HRD' || jabatan === 'Koordinator Office' || jabatan === 'SPV Sales') {
                        html += '<th rowspan="2" style="text-align:center;">Aksi</th>';
                    }
                    html += '</tr><tr>';
                    html += '<th style="text-align:center;">Harga Modul Regular (Rp.)</th>';
                    html += '<th style="text-align:center;">Harga Dollar ($)</th>';
                    html += '<th style="text-align:center;">Biaya Modul Dollar (Rp.)</th>';
                    html += '<th style="text-align:center;">Biaya Modul (Rp.)</th>';
                    html += '</tr></thead><tbody>';

                    var rowIndex = 1;
                    var groupedData = {};
                    data.forEach(item => {
                        if (!item || !item.nama_materi) {
                            console.warn("Invalid item: ", item);
                            return;
                        }
                        if (!groupedData[item.nama_materi]) {
                            groupedData[item.nama_materi] = [];
                        }
                        groupedData[item.nama_materi].push(item);
                    });

                    console.log("Grouped data: ", groupedData);

                    Object.keys(groupedData).forEach(materi => {
                        var group = groupedData[materi];
                        group.forEach((item, index) => {
                            var rowColor = (item.status === 'Merah') ? 'rgba(255, 0, 0, 0.5); color: #fff' :
                                'rgba(0, 99, 71, 0.5); color: #fff';
                            html += `<tr style="background-color:${rowColor}">`;
                            if (index === 0) {
                                html += `<td rowspan="${group.length}">${rowIndex}</td>`;
                                html += `<td rowspan="${group.length}">${materi}</td>`;
                                rowIndex++;
                            }
                            html += `<td>${item.pax || 'N/A'}</td>`;
                            html += `<td>${item.durasi || 'N/A'}</td>`;
                            html += `<td>${formatRupiah(item.harga_jual || 0)}</td>`;
                            html += `<td>${formatWithoutDecimals(item.total_harga_jual || 0)}</td>`;

                            if (!item.analisisrkm) {
                                let colspan = (jabatan === 'HRD' || jabatan === 'Koordinator Office' || jabatan === 'SPV Sales') ? 14 : 13;
                                html += `<td colspan="${colspan}" class="text-center">Belum Diinput data</td>`;
                            } else {
                                let a = item.analisisrkm;
                                html += `<td>${formatWithoutDecimals(a.harga_modul_regular || 0)}</td>`;
                                html += `<td>${a.harga_modul_regular_dollar || 0}</td>`;
                                html += `<td>${formatWithoutDecimals(a.biaya_modul_regular_dollar || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.biaya_modul_regular || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.konsumsi || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.souvenir || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.transportasi * a.pax * a.durasi || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.pa_hotel || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.exam || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.pc || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.total_fee_instruktur || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.alat || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.fee_instruktur || 0)}</td>`;
                                html += `<td>${formatWithoutDecimals(a.nett_penjualan || 0)}</td>`;

                                // Hitung persentase
                                let persentase = 0;
                                if (item.total_harga_jual && item.total_harga_jual > 0) {
                                    persentase = (a.nett_penjualan / item.total_harga_jual) * 100;
                                }
                                let persentaseColor = persentase < 30 ? 'red' : 'green';
                                html += `<td style="color:${persentaseColor}; font-weight:bold">${persentase.toFixed(2)}%</td>`;
                            }

                            if (jabatan === 'HRD' || jabatan === 'Koordinator Office' || jabatan === 'SPV Sales') {
                                html += `<td>
                                    <div class="btn-group dropup">
                                        <button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu">`;
                                if (item.status === 'Merah') {
                                    html += `<a class="dropdown-item" href="/analisisrkm/${item.id}/create">Input Data</a>`;
                                    html += `<a class="dropdown-item" href="/kalkulator/analisis/${item.id}/kelas">Kalkulator Analisis</a>`;
                                } else {
                                    html += `<a class="dropdown-item disabled" href="#">Input Data</a>`;
                                    html += `<a class="dropdown-item" href="/kelasanalisis/${item.id}/edit">Edit Data</a>`;
                                    html += `<a class="dropdown-item" href="/kalkulator/analisis/${item.id}/kelas">Kalkulator Analisis</a>`;
                                }
                                html += `</div></div></td>`;
                            }

                            html += '</tr>';
                        });

                        var totalNettPenjualan = group.reduce((acc, item) => acc + (parseFloat(item.analisisrkm?.nett_penjualan || 0)), 0);
                        html += '<tr style="font-weight:bold; background:#f2f2f2">';
                        let colspan = (jabatan === 'HRD' || jabatan === 'Koordinator Office' || jabatan === 'SPV Sales') ? 19 : 18;
                        html += `<td colspan="${colspan}" class="text-end">Subtotal:</td>`;
                        html += `<td>${formatWithoutDecimals(totalNettPenjualan)}</td>`;
                        html += `<td></td>`; // Kosong untuk kolom persentase subtotal
                        if (jabatan === 'HRD' || jabatan === 'Koordinator Office' || jabatan === 'SPV Sales') {
                            html += '<td></td>';
                        }
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    return html;
                }

                function removeRupiahFormat(angka) {
                    return parseFloat(angka.replace(/[^\d,]/g, '').replace(',', '.'));
                }

                function formatRupiah(angka, isNegative = false) {
                    if (angka === null || angka === undefined || isNaN(angka)) {
                        return '0';
                    }
                    let formatted = angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    return isNegative ? `<span class="text-danger">${formatted}</span>` : formatted;
                }

                function formatWithoutDecimals(value) {
                    if (value === null || value === undefined || isNaN(value)) {
                        return '0';
                    }
                    if (Math.floor(value) === value) {
                        return new Intl.NumberFormat('id-ID').format(value);
                    } else {
                        return new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(value);
                    }
                }
            </script>
        @endpush
    @endsection
