@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1 mb-5">Payment Advance <h3> 
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
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="approveForm" method="POST">
                        @csrf
                        <p>Apakah Disetujui?</p>
                        <div id="manager-row">
                            <div class="btn-group" role="group" aria-label="Approval Options">
                                <input type="hidden" value="" id="id_net_sales" name="id_net_sales">
                                <button class="btn btn-outline-primary" type="submit">Ya</button>

                                <input type="radio" class="btn-check" name="approval" id="approveNo" value="2" autocomplete="off">
                                <label class="btn btn-outline-danger" for="approveNo" onclick="toggleAlasanManager(true)">Tidak</label>

                            </div>

                            <div class="mt-3" id="alasanManagerInput" style="display: none;">
                                <label for="alasan_manager" class="form-label">Alasan Penolakan</label>
                                <textarea class="form-control" id="alasan_manager" name="keterangan" rows="3"></textarea>
                                <input type="hidden" value="{{ auth()->user()->jabatan }}" name="jabatan">
                                <button class="btn btn-outline-success mt-3" type="submit">Kirim</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

    $(document).on('submit', '#approveForm', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('netsales.approved') }}",
            method: 'POST',
            data: formData,
            processData: false, 
            contentType: false, 
            dataType: 'json',
            success: function(response) {
                let modal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
                modal.hide();
                getData(); 
            },
            error: function(xhr) {
                console.log(xhr.responseText); 
            }
        });
    });

    

    function getData() {
        var tahun = document.getElementById('tahun').value;

        // Show loading modal
        $('#loadingModal').modal('show');

        $.ajax({
            url: "paymantAdvance/" + tahun,
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
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Harga Penawaran (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Transportasi (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Fresh Money (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Entertaint (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Souvenir (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Penginapan (Rp.)</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Tanggal Payment Advance</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Tipe Pembayaran</th>';
    html += '<th scope="col" style="font-size: 14px;text-align: center;" rowspan="2">Total (Rp.)</th>';
    if (jabatan === 'SPV Sales' || jabatan === 'Sales' || jabatan === 'GM' || jabatan === 'Finance &amp; Accounting') {
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

            const sales = item.netsales || item.analisisrkm || item;

            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.harga_penawaran || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.transportasi || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.fresh_money || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.entertaint || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.souvenir || 0)}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.penginapan || 0)}</td>`;
            html += `<td style="font-size: 14px;">${sales.tgl_pa || '0000-00-00'}</td>`;
            html += `<td style="font-size: 14px;">${sales.tipe_pembayaran || ''}</td>`;
            html += `<td style="font-size: 14px;">${formatWithoutDecimals(sales.total || 0)},00</td>`;

            if (jabatan === 'SPV Sales' || jabatan === 'Sales' || jabatan === 'GM' || jabatan === 'Finance &amp; Accounting') {
                html += '<td style="font-size: 14px;">';
                html += '<div class="btn-group dropup">';
                html += '<button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                html += '<div class="dropdown-menu">';
                if (item.status == 'Merah') {
                    if (jabatan === "Sales") {
                        html += `<a class="dropdown-item" href="/paymantAdvance/${item.id}/create" data-toggle="tooltip" title="Input Data"><img src="{{ asset('icon/clipboard-primary.svg') }}"> Input Data</a>`;  
                    } else {
                        html += `<a class="dropdown-item disabled" href="/paymantAdvance/${item.id}/create" data-toggle="tooltip" title="Input Data" disabled><img src="{{ asset('icon/clipboard-primary.svg') }}"> Input Data</a>`;  
                    }
                } else {
                    html += `<a class="dropdown-item" href="/paymantAdvance/detail/${item.id}" data-toggle="tooltip" title="Detail"><img src="{{ asset('icon/clipboard-primary.svg') }}"> Detail</a>`;
                    
                    if (sales.level_status === null || sales.level_status === 'Belum disetujui') {
                        if (jabatan === "SPV Sales") {
                            html += `
                                <button class="dropdown-item" type="button" onclick="openApproveModal('${sales.id_NetSales}');" title="Detail">
                                    <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                </button>
                            `;
                        } else {
                            html += `
                                <button class="dropdown-item" disabled type="button" title="Sudah Ditangani oleh SPV Sales">
                                    <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                </button>
                            `;
                        }
                    } else if (sales.level_status === "I") {
                        if (jabatan === "GM") {
                            html += `
                                <button class="dropdown-item" type="button" onclick="openApproveModal('${sales.id_NetSales}');" title="Detail">
                                    <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                </button>
                            `;
                        } else {
                            html += `
                                <button class="dropdown-item" disabled type="button" title="Sudah Ditangani oleh GM">
                                    <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                </button>
                            `;
                        }
                    } else if (sales.level_status === "II") {
                        if (jabatan === 'Finance &amp; Accounting') {
                            html += `
                                <button class="dropdown-item" type="button" onclick="openApproveModal('${sales.id_NetSales}');" title="Detail">
                                    <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                </button>
                            `;
                        } else {
                            html += `
                                <button class="dropdown-item" disabled type="button" title="Sudah Ditangani oleh Finance & Accounting">
                                    <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                </button>
                            `;
                        }
                    } else if (sales.level_status === "III") {
                        if (jabatan === 'Finance &amp; Accounting' || jabatan === 'GM' || jabatan === 'SPV Sales') {
                            html += `
                                <button class="dropdown-item" disabled type="button" title="Sudah Ditangani oleh Finance & Accounting">
                                    <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                </button>
                            `;
                        }
                    }
                    const editUrlBase = "{{ url('/paymantAdvance/edit') }}/";
                    html += `<a class="dropdown-item" href="${editUrlBase}${sales.id}" data-toggle="tooltip" title="Edit Data"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit Data</a>`;
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

        let approvalSelected = false;

        function openApproveModal(id) {
            console.log('ID yang dikirim ke modal:', id);
            $('#id_net_sales').val(id);
            $('#approveModal').modal('show');
            approvalSelected = false;

            $('#approveYes').prop('checked', true);
            $('#approveNo').prop('checked', false);
            toggleAlasanManager(false); 

            setTimeout(() => {
                if (!approvalSelected) {
                    toggleAlasanManager(true); 
                }
            }, 3000);
        }


        function toggleAlasanManager(show) {
            approvalSelected = true;
            if (show) {
                $('#alasanManagerInput').show();
            } else {
                $('#alasanManagerInput').hide();
                $('#alasan_manager').val('');
            }
        }
</script>


@endpush
@endsection