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
    <div class="modal fade" id="modalAnalisis" tabindex="-1" aria-labelledby="modalAnalisisLabel" aria-hidden="true" aria-modal="true">
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
    <div class="modal fade" id="modalAnalisaMargin" tabindex="-1" aria-labelledby="modalAnalisaMarginLabel" aria-hidden="true" aria-modal="true">
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

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">Net Sales <h3> 
                    <div class="row">
                        <div class="col-12">
                            <div class="card" style="width: 100%">
                                <div class="card-body d-flex justify-content-start">
                                    <div class="col-md-4 mx-1">
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
                                        <button type="submit" onclick="getData()" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                                        @if (auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Koordinator Office')
                                            <button type="button" onclick="sinkronData()" class="btn click-primary" style="margin-top: 37px">Sinkron Data</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-12" id="content">
                                </div>
                            </div>
                        </div>
                    </div>                    
            </div>
        </div>
    </div>
</div>
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
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function() {
        getData()
    });
    function analisaMargin(tahun, bulan) {
        // console.log(tahun, bulan);
        // Kirim request ke server melalui AJAX
        $.ajax({
            url: '/netsales/' + tahun + '/' + bulan,
            type: 'GET',
            success: function(response) {
                var html = '';
                var totalMinus = 0;
                var totalProfit = 0;
                var dataRKM = response.data.weeklyProfit;
                
                // Iterate over each week's profit
                $.each(dataRKM, function(week, value) {
                        var minusValue = '';
                        var profitValue = '';
                        if (value < 0) {
                            minusValue = formatRupiah(value, true); // Format as rupiah and make it red
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
                    // Append the rows to the table body
                    $('#tableBody').html(html);

                    // Update the totals
                    $('#totalMinus').html(`<span class="text-danger">${formatRupiah(totalMinus, true)}</span>`);
                    $('#totalProfit').html(formatRupiah(totalProfit));
                    $('#overallSubtotal').html(formatRupiah(overallSubtotal, overallSubtotal < 0));

                    // Show the modal
                    $('#modalAnalisaMargin').modal('show');
                },
            });
    }

    function analisisData(formId, tahun, bulan, minggu) {
        console.log(`Form ID: ${formId}`);
        console.log(`Year: ${tahun}, Month: ${bulan}, Week: ${minggu}`);

        // Kirim request ke server melalui AJAX
        $.ajax({
            url: '/analisisrkm/' + tahun + '/' + bulan + '/' + minggu,
            type: 'GET',
            success: function(response) {
                var html = '';
                var totalNettPenjualan = 0;
                var dataRKM = response.data.data;
                console.log(dataRKM[0].analisisrkmmingguan.data);
                if (!dataRKM[0].analisisrkmmingguan.data || dataRKM[0].analisisrkmmingguan.data.length === 0 || dataRKM[0].analisisrkmmingguan.data == null) {
                    html += '<form id="kirimData" method="POST" action="/analisisrkm/'+ tahun +'/'+ bulan +'/'+ minggu +'/post">'; // Lengkapi form dengan method POST dan action
                    html += '@csrf'; // Tambahkan token CSRF untuk keamanan Laravel
                    html += '<table class="table table-bordered">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">No</th>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">Kelas</th>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">Nett Penjualan</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    // Array untuk menyimpan nett_penjualan
                    var nettPenjualanArray = [];
                                    
                    $.each(response.data.data, function(index, value) {
                        var nett_penjualan = value.analisisrkm.nett_penjualan;
                        var kelasId = value.analisisrkm.id;
                        nettPenjualanArray.push(Math.floor(nett_penjualan)); // Simpan nett_penjualan ke array
                        // console.log(response.data.data);
                        html += '<tr>';
                        html += '<input type="hidden" class="form-control" name="id_kelasanalisis[]" value="'+ (kelasId) +'">'; // Tambahkan array untuk multiple data
                        html += '<input type="hidden" class="form-control" name="tahun[]" value="'+ (tahun) +'">';
                        html += '<input type="hidden" class="form-control" name="bulan[]" value="'+ (bulan) +'">';
                        html += '<input type="hidden" class="form-control" name="minggu[]" value="'+ (minggu) +'">';
                        html += '<input type="hidden" class="form-control" name="nama_materi[]" value="'+ (value.nama_materi) +'">';
                        html += '<input type="hidden" class="form-control" name="nett_penjualan[]" value="'+(nett_penjualan) +'">';
                        html += '<td style="font-size: 14px;">' + (index + 1) + '</td>';
                        html += '<td style="font-size: 14px;">' + (value.nama_materi) + '</td>';
                        html += '<td style="font-size: 14px;">' + formatWithoutDecimals(nett_penjualan) + '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody>';
                    html += '</table>';
                    
                    // Tambahkan input fixcost dan profit di bawah tabel
                    html += '<div class="row">';
                    html += '<div class="col-md-6">';
                    html += '<label>Fix Cost:</label>';
                    html += '<input type="text" class="form-control fixcost" name="fixcost">';
                    html += '</div>';
                    html += '<div class="col-md-6">';
                    html += '<label>Profit:</label>';
                    html += '<input type="text" class="form-control profit" id="profit" name="profit" readonly>';
                    html += '</div>';
                    html += '</div>';
                    html += '</form>';
                }
                else {
                    var dataRKM = response.data.data;

                    // Check if all fixcost and profit values are the same across all data
                    var firstFixcost = dataRKM[0]?.analisisrkmmingguan?.data?.[0]?.fixcost ?? 'N/A';
                    var firstProfit = dataRKM[0]?.analisisrkmmingguan?.data?.[0]?.profit ?? 'N/A';
                    var allSameFixcost = dataRKM.every(item => item.analisisrkmmingguan?.data?.[0]?.fixcost === firstFixcost);
                    var allSameProfit = dataRKM.every(item => item.analisisrkmmingguan?.data?.[0]?.profit === firstProfit);

                    // Group data by 'nama_materi' and sum 'nett_penjualan' for each group
                    var groupedData = {};
                    var totalNettPenjualanAll = 0; // Initialize total for all nett_penjualan
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
                        totalNettPenjualanAll += nettPenjualan; // Add to overall total
                    });

                    // Start table creation
                    var html = '<table class="table table-bordered">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">No</th>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">Kelas</th>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">Total Nett Penjualan</th>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">Fix Cost</th>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">Profit</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';

                    // Initialize row counter
                    var rowIndex = 1;
                    var commentsArray = [];
                    // Loop through each unique 'nama_materi' group
                    $.each(groupedData, function(materi, group) {
                        // Extract the first item in the group to access fixcost and profit values
                        $.each(group.items, function(itemIndex, item) {
                            var komentar = item.analisisrkm.komentar ?? 'Tidak ada komentar';
                            // Collect komentar for each item
                            commentsArray.push(komentar);
                        });
                        var firstItem = group.items[0];
                        var fixcost = firstItem.analisisrkmmingguan?.data?.[0]?.fixcost ?? 'N/A';
                        var profit = firstItem.analisisrkmmingguan?.data?.[0]?.profit ?? 'N/A';
                        var komentar = firstItem.komentar ?? 'Tidak ada komentar'; // Get komentar value
                        html += '<tr>';
                        html += '<td style="font-size: 14px;">' + rowIndex + '</td>';
                        html += '<td style="font-size: 14px;">' + materi + '</td>';
                        html += '<td style="font-size: 14px;">' + formatWithoutDecimals(group.totalNettPenjualan) + '</td>';

                        // Only show 'fixcost' and 'profit' once, with rowspan if they are the same across all items
                        if (rowIndex === 1 && allSameFixcost) {
                            html += '<td style="font-size: 14px;text-align:center;padding: 70px 0;" rowspan="' + Object.keys(groupedData).length + '">' + formatWithoutDecimals(firstFixcost) + '</td>';
                        } else if (!allSameFixcost) {
                            html += '<td style="font-size: 14px;text-align:center;padding: 70px 0;">' + fixcost + '</td>';
                        }
                        
                        if (rowIndex === 1 && allSameProfit) {
                            html += '<td style="font-size: 14px;text-align:center;padding: 70px 0;" rowspan="' + Object.keys(groupedData).length + '">' + formatWithoutDecimals(firstProfit) + '</td>';
                        } else if (!allSameProfit) {
                            html += '<td style="font-size: 14px;text-align:center;padding: 70px 0;">' + profit + '</td>';
                        }
                        
                        html += '</tr>';
                        rowIndex++;
                    });

                    html += '</tbody>';

                    // Add a footer with the total nett penjualan
                    html += '<tfoot>';
                    html += '<tr>';
                    html += '<td colspan="2" style="font-size: 14px; text-align: right; font-weight: bold;">Total</td>';
                    html += '<td style="font-size: 14px; font-weight: bold;">' + formatWithoutDecimals(totalNettPenjualanAll) + '</td>';
                    html += '<td colspan="2"></td>'; // Empty cells for Fix Cost and Profit columns
                    html += '</tr>';
                    html += '<tr>';
                    html += '<td colspan="2" style="font-size: 14px; text-align: right; font-weight: bold;">Keterangan</td>';
                    html += '<td colspan="3"></td>'; // Empty cells for Fix Cost and Profit columns
                    html += '</tr>';
                    $.each(commentsArray, function(index, komentar) {
                        html += '<tr>';
                        html += '<td colspan="2"></td>';
                        html += '<td colspan="3" style="font-size: 14px;">' + komentar + '</td>';
                        html += '</tr>';
                    });
                    html += '<tr>';
                    html += '<td colspan="5" style="text-align: center;">';
                    html += '<button type="button" class="btn btn-primary" id="updateButton">Update Data</button>';
                    html += '</td>';
                    html += '</tr>';
                    html += '</tfoot>';

                    html += '</table>';

                    html += '</tbody>';
                    html += '</table>';
                    $('#submitForm').prop('hidden', true);


                }
                $('#dataRKM').html(html); // Sesuaikan dengan respons data yang kamu kirim
                $('#modalAnalisis').modal('show'); // Tampilkan modal
                $('#updateButton').on('click', function() {
                    $("dataRKM").empty();
                    analisisDataUpdate(formId, tahun, bulan, minggu);
                });
                // Event listener untuk menghitung profit saat fixcost berubah
                $('.fixcost').on('input', function() {
                    var fixcostFormatted = $(this).val();
                    var fixcost = parseFloat(fixcostFormatted.replace(/[^0-9,-]+/g, "")); 
                    var totalNettPenjualan = nettPenjualanArray.reduce((a, b) => a + b, 0);
                    var profit = totalNettPenjualan - fixcost;
                    $(this).val(formatRupiah(fixcost)); 
                    $('#profit').val(formatRupiah(profit)); 
                    $('#submitForm').prop('hidden', false);

                });

                // Event listener untuk submit form
                $('#submitForm').on('click', function() {
                    $('#kirimData').submit();
                });
            },

            error: function(xhr) {
                console.error(xhr.responseText); // Menampilkan error jika request gagal
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
                var nettPenjualanArray = []; // Initialize nettPenjualanArray to store nett_penjualan values
                var totalNettPenjualan = 0;
                var dataRKM = response.data.data;
                
                if (dataRKM[0].analisisrkmmingguan?.data && dataRKM[0].analisisrkmmingguan.data.length > 0) {
                    // Load existing data for editing
                    html += '<form id="updateData" method="POST" action="/analisisrkm/'+ tahun +'/'+ bulan +'/'+ minggu +'/update">';
                    html += '@csrf';
                    html += '@method("PUT")';
                    html += '<table class="table table-bordered">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">No</th>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">Kelas</th>';
                    html += '<th scope="col" style="font-size: 14px;text-align: center;">Nett Penjualan</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';

                    // Load data into form fields for each entry
                    $.each(dataRKM, function(index, value) {
                        var nett_penjualan = parseFloat(value.analisisrkm.nett_penjualan) || 0;
                        var kelasId = value.analisisrkm.id;
                        
                        // Push nett_penjualan into nettPenjualanArray for profit calculation later
                        nettPenjualanArray.push(nett_penjualan);

                        html += '<tr>';
                        html += '<input type="hidden" class="form-control" name="id_kelasanalisis[]" value="'+ (kelasId) +'">';
                        html += '<input type="hidden" class="form-control" name="tahun[]" value="'+ (tahun) +'">';
                        html += '<input type="hidden" class="form-control" name="bulan[]" value="'+ (bulan) +'">';
                        html += '<input type="hidden" class="form-control" name="minggu[]" value="'+ (minggu) +'">';
                        html += '<input type="text" class="form-control" name="nama_materi[]" value="'+ (value.nama_materi) +'" readonly>';
                        html += '<input type="number" class="form-control" name="nett_penjualan[]" value="'+ (nett_penjualan) +'">';
                        html += '<td style="font-size: 14px;">' + (index + 1) + '</td>';
                        html += '<td style="font-size: 14px;">' + (value.nama_materi) + '</td>';
                        html += '<td style="font-size: 14px;">' + formatWithoutDecimals(nett_penjualan) + '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody>';
                    html += '</table>';

                    // Add input fields for fixcost and profit for update
                    html += '<div class="row">';
                    html += '<div class="col-md-6">';
                    html += '<label>Fix Cost:</label>';
                    html += '<input type="text" class="form-control fixcost" name="fixcost" value="'+ (dataRKM[0].analisisrkmmingguan?.data[0]?.fixcost || '') +'">';
                    html += '</div>';
                    html += '<div class="col-md-6">';
                    html += '<label>Profit:</label>';
                    html += '<input type="text" class="form-control profit" id="profit" name="profit" readonly value="'+ (dataRKM[0].analisisrkmmingguan?.data[0]?.profit || '') +'">';
                    html += '</div>';
                    html += '</div>';
                    
                    html += '<button type="button" class="btn btn-primary" id="submitUpdateForm">Update Data</button>';
                    html += '</form>';
                } else {
                    // Data does not exist, render form for new data creation
                    html += '<form id="kirimData" method="POST" action="/analisisrkm/'+ tahun +'/'+ bulan +'/'+ minggu +'/post">';
                    html += '@csrf';
                    html += '<table class="table table-bordered">';
                    // Table structure and data entry as shown before for new entry
                    // Add logic here as per your original code if no data exists
                    html += '</form>';
                }

                $('#dataRKM').html(html); // Populate modal with HTML content
                $('#modalAnalisis').modal('show'); // Show the modal
                
                // Event listener to handle update form submission
                $('#submitUpdateForm').on('click', function() {
                    $('#updateData').submit();
                });

                // Calculate profit when fixcost changes
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
        $.ajax({
            url: "sinkronDataKelasAnalisis/",
            // url: "sinkronDataKelasAnalisis/" + tahun,
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#loadingModal').modal('show');
                $('#loadingModal').on('show.bs.modal', function () {
                    $('#loadingModal').removeAttr('inert');
                });
            },
            complete: function () {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').on('hidden.bs.modal', function () {
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

        // Show loading modal
        $('#loadingModal').modal('show');

        $.ajax({
            url: "netsales/" + tahun,
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#loadingModal').modal('show');
                $('#loadingModal').on('show.bs.modal', function () {
                    $('#loadingModal').removeAttr('inert');
                });
            },
            complete: function () {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').on('hidden.bs.modal', function () {
                        $('#loadingModal').attr('inert', true);
                    });
                }, 1000);
            },
            success: function(response) {
                var html = '';
                var jabatan = "{{ auth()->user()->jabatan}}";

                // Loop through each month
                response.data.forEach(function(monthData) {
                    var jabatan = '{{auth()->user()->jabatan}}';
                    var monthName = monthData.month;
                    html += '<h4>' + monthName + '</h4>';
                    if(jabatan == 'HRD'){
                        html += `<button type="button" class="btn click-primary p-2" onclick="analisaMargin('${tahun}', '${monthName}')">Analisa Margin</button>`;
                    }
                    // Loop through weeks in the month
                    monthData.weeksData.forEach(function(weekData) {
                        html += '<div class="card my-1">';
                        html += '<div class="card-body table-responsive">';
                        html += `<h3 class="card-title my-1">Rencana Kelas Mingguan ${monthName} (Minggu ke - ${weekData.minggu}) ${weekData.tanggal_awal_minggu} - ${weekData.tanggal_akhir_minggu}</h3>`;
                            // console.log(weekData.bulan);
                        // Check if the rkmfull status for this specific week is "ok"
                        if (weekData.rkmfull === "ok") {
                            if(jabatan == 'HRD'){
                            var formId = 'analisisForm_' + weekData.tahun + '_' + weekData.bulan + '_' + weekData.minggu;
                            html += `<form id="${formId}">`;
                            html += `<button type="button" class="btn click-primary p-2" data-bs-toggle="modal" data-bs-target="#modalAnalisis" onclick="analisisData('${formId}', '${weekData.tahun}', '${weekData.bulan}', '${weekData.minggu}')">Analisis</button>`;
                            html += `<input type="hidden" name="tahunRKM" value="${weekData.tahun}">`;
                            html += `<input type="hidden" name="bulanRKM" value="${weekData.bulan}">`;
                            html += `<input type="hidden" name="mingguRKM" value="${weekData.minggu}">`;
                            html += '</form>';
                            }
                        }

                        if (weekData.data === null) {
                            html += '<p class="text-center">Tidak Ada Kelas Mingguan</p>';
                        } else {
                            // Process and render table
                            html += renderTable(weekData.data, jabatan);
                        }

                        html += '</div>';
                        html += '</div>';
                    });
                });

                // Display the generated HTML
                $('#content').html(html);
                $('#loadingModal').modal('hide');
            },
            error: function() {
                $('#loadingModal').modal('hide');
                alert("Error fetching data. Please try again.");
            }
        });
    }
    function renderTable(data, jabatan) {
    var html = '<table class="table table-bordered">';
    html += '<thead>';
    html += '<tr>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">No</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Kelas</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Pax</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Durasi</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Harga Nett Jual (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Sebelum Net Sales (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Pajak (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Pa/Cashback (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Biaya Akomodasi (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Entertaint (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Total (Rp.)</th>';
    if (jabatan === 'HRD' || jabatan === 'Koordinator Office') {
        html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Aksi</th>';
    }
    html += '</tr><tr></tr></thead><tbody>';

    var rowIndex = 1;
    var groupedData = {};
    data.forEach(function (item) {
        if (!groupedData[item.nama_materi]) {
            groupedData[item.nama_materi] = [];
        }
        groupedData[item.nama_materi].push(item);
    });

    Object.keys(groupedData).forEach(function (materi) {
        var group = groupedData[materi];
        group.forEach(function (item, index) {
            var rowColor = (item.status === 'Merah') ? 'rgba(255, 0, 0, 0.5); color: #fff' : 'rgba(0, 99, 71, 0.5); color: #fff';
            html += `<tr style="background-color: ${rowColor}">`;

            if (index === 0) {
                html += `<td style="font-size: 14px;" rowspan="${group.length}">${rowIndex}</td>`;
                html += `<td style="font-size: 14px;" rowspan="${group.length}">${materi}</td>`;
                rowIndex++;
            }

            html += `<td style="font-size: 14px;">${item.pax}</td>`;
            html += `<td style="font-size: 14px;">${item.durasi}</td>`;
            html += `<td style="font-size: 14px;">${formatRupiah(item.harga_jual)}</td>`;

            const sales = item.netsales || item.analisisrkm || item;

            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.sebelumNetSales || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.pajak || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.cashback || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.biaya_akomodasi || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.entertaint || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.total || 0)}</td>`;

            if (jabatan === 'HRD' || jabatan === 'Koordinator Office') {
                html += '<td style="font-size: 14px;">';
                html += '<div class="btn-group dropup">';
                html += '<button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                html += '<div class="dropdown-menu">';
                if (item.status == 'Merah') {
                    html += `<a class="dropdown-item" href="/netsales/${item.id}/create" data-toggle="tooltip" title="Input Data"><img src="{{ asset('icon/clipboard-primary.svg') }}"> Input Data</a>`;
                } else {
                    html += `<a class="dropdown-item disabled" href="/netSales/${item.id}/create" data-toggle="tooltip" title="Input Data"><img src="{{ asset('icon/clipboard-primary.svg') }}"> Input Data</a>`;
                    html += `<a class="dropdown-item" href="/kelasanalisis/${item.id}/edit" data-toggle="tooltip" title="Edit Data"><img src="{{ asset('icon/edit-warning.svg') }}"> Edit Data</a>`;
                }
                html += '</div></div></td>';
            }

            html += '</tr>';
        });

        var totalNettPenjualan = group.reduce((acc, item) => {
            const sales = item.netsales || item.analisisrkm || item;
            return acc + (parseFloat(sales.total) || 0);
        }, 0);

        html += '<tr>';
        html += '<td colspan="10" style="font-size: 14px; text-align: right; font-weight: bold;">Subtotal:</td>';
        html += `<td style="font-size: 14px; font-weight: bold;">${formatWithoutDecimals(totalNettPenjualan)}</td>`;
        if (jabatan === 'HRD' || jabatan === 'Koordinator Office') {
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
    function formatRupiah(angka) {
            if (angka === null || angka === undefined || isNaN(angka)) {
                return '0';
            }
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        function formatWithoutDecimals(value) {
            // Check if the value is an integer
            if (Math.floor(value) === value) {
                return new Intl.NumberFormat('id-ID').format(value); // Format as Indonesian Rupiah
            } else {
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
            }
        }
</script>
@endpush
@endsection
