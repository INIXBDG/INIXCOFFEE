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
                                                            for (
                                                                $tahun = 2020;
                                                                $tahun <= $tahun_sekarang + 2;
                                                                $tahun++
                                                            ) {
                                                                $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                                                echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                                            }
                                                        @endphp
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mx-1">
                                                    <label for="bulanRange" class="form-label">Bulan</label>
                                                    <select id="bulanRange" class="form-select">
                                                        <option disabled>Pilih Rentang Bulan</option>
                                                        @php
                                                            $bulan_sekarang = now()->month;
                                                            $nama_bulan = [
                                                                'Januari',
                                                                'Februari',
                                                                'Maret',
                                                                'April',
                                                                'Mei',
                                                                'Juni',
                                                                'Juli',
                                                                'Agustus',
                                                                'September',
                                                                'Oktober',
                                                                'November',
                                                                'Desember',
                                                            ];
                                                            for ($bulan = 1; $bulan <= 12; $bulan++) {
                                                                $bulan_awal = $nama_bulan[$bulan - 1];
                                                                $bulan_akhir = $nama_bulan[$bulan % 12];
                                                                $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                                                echo "<option value=\"$bulan\" $selected>$bulan_awal</option>";
                                                            }
                                                        @endphp
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mx-1 mt-2">
                                                    <button type="submit" onclick="getData()" class="btn click-primary"
                                                        style="margin-top: 37px">Cari Data</button>
                                                    <button type="button" onclick="downloadPDF()" class="btn click-primary"
                                                        style="margin-left: 10px; margin-top: 37px">
                                                        PDF
                                                    </button>
                                                    @if (auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Koordinator Office')
                                                        <button type="button" onclick="sinkronData()"
                                                            class="btn click-primary" style="margin-top: 37px">Sinkron
                                                            Data</button>
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
                                    @php
                                        $jabatan = auth()->user()->jabatan;
                                    @endphp
                                    @if ($jabatan == 'Finance & Accounting')
                                        <div class="row my-2">
                                            <select name="status_tracking" id="status_tracking" class="form-select">
                                                <option disabled selected>Pilih Status Tracking</option>
                                                <option
                                                    value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">
                                                    Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                                <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">
                                                    Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                                <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve
                                                    Direksi</option>
                                                <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke
                                                    Direktur Utama</option>
                                                <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang
                                                    dalam proses Pencairan</option>
                                                <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                                <option value="Selesai">Selesai</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Silakan pilih status tracking terlebih dahulu.
                                            </div>
                                        </div>
                                    @endif
                                    <div class="btn-group" role="group" aria-label="Approval Options">
                                        <input type="hidden" value="" id="id_rkm" name="id_rkm">
                                        <button class="btn btn-outline-primary" type="submit"
                                            id="btnApproveYes">Ya</button>

                                        <input type="radio" class="btn-check" name="approval" id="approveNo"
                                            value="2" autocomplete="off">
                                        <label class="btn btn-outline-danger" for="approveNo"
                                            onclick="toggleAlasanManager(true)">Tidak</label>
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

            table.dataTable {
                font-size: 12px !important;
                /* Atur sesuai kebutuhan */
            }

            /* Mengatur font size tabel DataTable */
            table.dataTable {
                font-size: 12px !important;
            }

            /* Mengatur font size header dan body tabel */
            table.dataTable thead th,
            table.dataTable tbody td {
                font-size: 12px !important;
            }

            /* Jika ingin mengatur ukuran font pada seluruh container */
            #content {
                font-size: 14px;
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

                let approveType = '';

                $('#btnApproveYes').on('click', function() {
                    approveType = 'ya';
                });

                $('#approveNo').on('click', function() {
                    approveType = 'tidak';
                });

                $(document).on('submit', '#approveForm', function(e) {
                    e.preventDefault();

                    if (approveType === 'ya') {
                        let jabatan = "{{ auth()->user()->jabatan }}";
                        let selectedTracking = $('#status_tracking').val();

                        if (jabatan === 'Finance & Accounting' && (!selectedTracking || selectedTracking === "null")) {
                            alert('Silakan pilih status tracking terlebih dahulu.');
                            $('#status_tracking').addClass('is-invalid');
                            return;
                        } else {
                            $('#status_tracking').removeClass('is-invalid');
                        }
                    }

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

                // function buat dapat data
                function getSalesData(item) {
                    if (item.status?.toLowerCase() === 'hijau' || item.status === 'Hijau') {
                        const detail = item.netsales?.[0] || item.analisisRkm?.[0];
                        if (detail) {
                            return {
                                ...detail,
                                level_status: item.level_status,
                                keterangan: item.keterangan,
                                total: detail.total || item.total || 0,
                                tgl_pa: detail.tgl_pa || item.tgl_pa || '-'
                            };
                        }
                    }

                    return {
                        transportasi: item.transportasi || 0,
                        akomodasi_peserta: item.akomodasi_peserta || 0,
                        akomodasi_tim: item.akomodasi_tim || 0,
                        fresh_money: item.fresh_money || 0,
                        entertaint: item.entertaint || 0,
                        souvenir: item.souvenir || 0,
                        cashback: item.cashback || 0,
                        sewa_laptop: item.sewa_laptop || 0,
                        total: item.total,
                        tgl_pa: item.tgl_pa || '-',
                        level_status: item.level_status,
                        keterangan: item.keterangan
                    };
                }

                function getData() {
                    var tahun = document.getElementById('tahun').value;
                    var bulanRange = document.getElementById('bulanRange').value;
                    console.log('Tahun:', tahun);
                    console.log('Rentang Bulan:', bulanRange);
                    if (!tahun || !bulanRange) {
                        alert("Mohon pilih tahun dan rentang bulan terlebih dahulu.");
                        return;
                    }

                    $('#loadingModal').modal('show');
                    // Load datanya
                    $.ajax({
                        url: "paymantAdvance/" + tahun + "/" + bulanRange,
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
                            // 🔍 LOG: Tampilkan seluruh respons dari server
                            console.log('✅ Respons dari server (success):', response);

                            var html = '';
                            var jabatan = "{{ auth()->user()->jabatan }}";

                            // 🔍 LOG: Tampilkan struktur data yang akan diproses
                            console.log('📅 Data bulanan yang diterima:', response.data);

                            response.data.forEach(function(monthData) {
                                var monthName = monthData.month;
                                html += '<h4>' + monthName + '</h4>';

                                monthData.weeksData.forEach(function(weekData) {
                                    html += '<div class="card my-1">';
                                    html += '<div class="card-body table-responsive">';
                                    html +=
                                        `<h3 class="card-title my-1">Rencana Kelas Mingguan ${monthName} (Minggu ke - ${weekData.minggu}) ${weekData.tanggal_awal_minggu} - ${weekData.tanggal_akhir_minggu}</h3>`;

                                    if (weekData.data === null) {
                                        html += '<p class="text-center">Tidak Ada Kelas Mingguan</p>';
                                    } else {
                                        // ✅ Gunakan tableId yang unik dan konsisten
                                        var tableId =
                                            `table_${weekData.tahun}_${weekData.bulan}_${weekData.minggu}`;

                                        // 🔍 LOG: Data yang akan dimasukkan ke tabel
                                        console.log(`📋 Data minggu ${weekData.minggu} (${tableId}):`,
                                            weekData.data);

                                        html += renderTable(weekData.data, jabatan, tableId);
                                    }

                                    html += '</div>';
                                    html += '</div>';
                                });
                            });

                            // Masukkan HTML ke container
                            $('#content').html(html);

                            var idSales = "{{ auth()->user()->karyawan->kode_karyawan }}";
                            var jabatan = "{{ auth()->user()->jabatan }}";
                            console.log('🎯 ID Sales:', idSales);
                            console.log('📌 Jabatan:', jabatan);

                            // Jika jabatan memerlukan penghapusan ID Sales
                            if (jabatan == 'SPV Sales' || jabatan == 'HRD' || jabatan == 'GM' || jabatan ==
                                'Finance & Accounting' || jabatan == 'Koordinator Office') {
                                idSales = '';
                                console.log('🔄 ID Sales direset ke kosong karena jabatan:', jabatan);
                            }

                            // 🔍 LOG: Inisialisasi DataTables
                            console.log('🔧 Memulai inisialisasi DataTables...');

                            response.data.forEach(function(monthData) {
                                monthData.weeksData.forEach(function(weekData) {
                                    if (weekData.data !== null) {
                                        var tableId =
                                            `table_${weekData.tahun}_${weekData.bulan}_${weekData.minggu}`;

                                        // Cek dan destroy DataTable lama jika ada
                                        if ($.fn.DataTable.isDataTable('#' + tableId)) {
                                            console.log(
                                                `🗑️ Menghancurkan DataTable lama untuk: #${tableId}`
                                            );
                                            $('#' + tableId).DataTable().destroy();
                                        }

                                        // Inisialisasi DataTable baru
                                        console.log(`✅ Inisialisasi DataTable baru untuk: #${tableId}`);
                                        $('#' + tableId).DataTable({
                                            paging: true,
                                            searching: true,
                                            ordering: true,
                                            info: true,
                                            lengthChange: true,
                                            pageLength: 10,
                                            initComplete: function() {
                                                this.api().columns(2).search(idSales)
                                                    .draw();
                                                console.log(
                                                    '📊 Filter DataTable (kolom 2) dengan nilai:',
                                                    idSales);
                                            }
                                        });
                                    }
                                });
                            });

                            console.log('✅ Semua DataTable berhasil diinisialisasi.');
                        },
                        error: function(xhr, status, error) {
                            // 🔍 LOG: Tampilkan error jika terjadi
                            console.error('❌ ERROR saat mengambil data:', error);
                            console.error('📡 Detail respons AJAX:', xhr.responseText);
                            console.error('HTTP Status:', xhr.status);

                            $('#loadingModal').modal('hide');
                            alert("Error fetching data. Please try again.");
                        }
                    });
                }

                function downloadPDF() {
                    const tahun = document.getElementById('tahun').value;
                    const bulan = document.getElementById('bulanRange').value;

                    if (!tahun || !bulan) {
                        alert('Pilih tahun dan bulan terlebih dahulu!');
                        return;
                    }

                    const url = `/download/pdf/netsales/${tahun}/${bulan}`;
                    window.location.href = url;
                }

                function renderTable(data, jabatan, tableId) {
                    var html = `<table id="${tableId}" class="table table-bordered table-striped" style="width:100%">`;
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th style="font-size:14px">No</th>';
                    html += '<th style="font-size:14px">Kelas</th>';
                    html += '<th style="font-size:14px">Sales</th>';
                    html += '<th style="font-size:14px">Transportasi (Rp.)</th>';
                    html += '<th style="font-size:14px">Akomodasi Peserta (Rp.)</th>';
                    html += '<th style="font-size:14px">Akomodasi Tim (Rp.)</th>';
                    html += '<th style="font-size:14px">Fresh Money (Rp.)</th>';
                    html += '<th style="font-size:14px">Entertaint (Rp.)</th>';
                    html += '<th style="font-size:14px">Souvenir (Rp.)</th>';
                    html += '<th style="font-size:14px">Cashback (Rp.)</th>';
                    html += '<th style="font-size:14px">Sewa Laptop (Rp.)</th>';
                    html += '<th style="font-size:14px">Tanggal Payment Advance</th>';
                    html += '<th style="font-size:14px">NetSales (Rp.)</th>';
                    if (jabatan === 'SPV Sales' || jabatan === 'Sales' || jabatan === 'GM' || jabatan === 'Finance & Accounting' ||
                        jabatan == 'Koordinator Office') {
                        html += '<th style="font-size:14px">Aksi</th>';
                    }
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';

                    var rowIndex = 1;
                    var groupedData = {};
                    data.forEach(function(item) {
                        if (!groupedData[item.nama_materi]) {
                            groupedData[item.nama_materi] = [];
                        }
                        groupedData[item.nama_materi].push(item);
                    });

                    Object.keys(groupedData).forEach(function(materi) {
                        var group = groupedData[materi];
                        group.forEach(function(item, index) {
                            // Warna lebih soft & modern
                            const isMerah = item.status?.toLowerCase() === 'merah';
                            const rowColor = isMerah ?
                                'rgba(227, 39, 2)' // merah sangat soft
                                :
                                'rgba(47, 237, 111)'; // hijau sangat soft

                            const textColor = isMerah ? '#721c24' : '#155724';

                            html += `<tr style="background-color: ${rowColor}; color: ${textColor};">`;

                            if (index === 0) {
                                html += `<td rowspan="${group.length}">${rowIndex}</td>`;
                                html += `<td rowspan="${group.length}">${materi}</td>`;
                                html += `<td rowspan="${group.length}">${item.sales_key || '-'}</td>`;
                                rowIndex++;
                            }

                            // Ambil data sales yang benar
                            const sales = getSalesData(item);

                            html += `<td>${formatWithoutDecimals(sales.transportasi || 0)}</td>`;
                            html += `<td>${formatWithoutDecimals(sales.akomodasi_peserta || 0)}</td>`;
                            html += `<td>${formatWithoutDecimals(sales.akomodasi_tim || 0)}</td>`;
                            html += `<td>${formatWithoutDecimals(sales.fresh_money || 0)}</td>`;
                            html += `<td>${formatWithoutDecimals(sales.entertaint || 0)}</td>`;
                            html += `<td>${formatWithoutDecimals(sales.souvenir || 0)}</td>`;
                            html += `<td>${formatWithoutDecimals(sales.cashback || 0)}</td>`;
                            html += `<td>${formatWithoutDecimals(sales.sewa_laptop || 0)}</td>`;
                            html += `<td>${sales.tgl_pa || '-'}</td>`;
                            html += `<td>${formatWithoutDecimals(sales.total || 0)}</td>`;

                            if (jabatan === 'SPV Sales' || jabatan === 'Sales' || jabatan === 'GM' || jabatan ===
                                'Finance &amp; Accounting' || jabatan == 'Koordinator Office') {
                                html += '<td style="font-size: 14px;">';
                                html += '<div class="btn-group dropup">';
                                html +=
                                    '<button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                html += '<div class="dropdown-menu">';
                                if (item.status == 'Merah') {
                                    if (jabatan === "Sales") {
                                        html +=
                                            `<a class="dropdown-item" href="/paymantAdvance/${item.id}/create/form-view" data-toggle="tooltip" title="Input Data"><img src="{{ asset('icon/clipboard-primary.svg') }}"> Input Data</a>`;
                                    } else {
                                        html +=
                                            `<a class="dropdown-item disabled" href="/paymantAdvance/${item.id}/create/form-view" data-toggle="tooltip" title="Input Data" disabled><img src="{{ asset('icon/clipboard-primary.svg') }}"> Input Data</a>`;
                                    }
                                } else {
                                    html +=
                                        `<a class="dropdown-item" href="/paymantAdvance/detail/${item.id}/view" data-toggle="tooltip" title="Detail"><img src="{{ asset('icon/clipboard-primary.svg') }}"> Detail</a>`;

                                    if (sales.level_status === null || sales.level_status === 'Belum Disetujui') {
                                        if (jabatan === "SPV Sales") {
                                            html += `
                                    <button class="dropdown-item" type="button" onclick="openApproveModal('${sales.id_rkm}');" title="approved">
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
                                    } else if (sales.level_status === "1") {
                                        if (jabatan === "GM" || jabatan === 'Koordinator Office') {
                                            html += `
                                    <button class="dropdown-item" type="button" onclick="openApproveModal('${sales.id_rkm}');" title="approved">
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
                                    } else if (sales.level_status === "2") {
                                        if (jabatan === 'Finance &amp; Accounting') {
                                            html += `
                                    <button class="dropdown-item" type="button" onclick="openApproveModal('${sales.id_rkm}');" title="Detail">
                                        <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                    </button>
                                `;
                                        } else {
                                            html += `
                                    <button class="dropdown-item" type="button" disabled title="approved">
                                        <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                    </button>
                                `;
                                        }
                                    } else if (sales.level_status === "3") {
                                        if (jabatan === 'Finance &amp; Accounting' || jabatan === 'GM' ||
                                            jabatan === 'SPV Sales') {
                                            if (sales.keterangan === "Selesai") {
                                                html += `
                                        <button class="dropdown-item" type="button" disabled title="sudah ditangani oleh Finance & Accounting">
                                            <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                        </button>
                                    `;
                                            } else {
                                                html += `
                                        <button class="dropdown-item" type="button" onclick="openApproveModal('${sales.id_rkm}');" title="Detail">
                                            <i class="fa-regular fa-circle-check" style="font-size: 20px;"></i> Approved
                                        </button>
                                    `;
                                            }
                                        }
                                    }
                                    html +=
                                        `<a class="dropdown-item" href="{{ url('/paymantAdvance/edit') }}/${sales.id}" data-toggle="tooltip" title="Edit Data"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit Data</a>`;
                                html += `<a class="dropdown-item" href="{{ url('/download/pdf/netsales')}}/${sales.id_rkm}"><img src="{{ asset('icon/clipboard-primary.svg') }}"> PDF</a>`;
                                }
                                html += '</div></div></td>';
                            }
                            html += '</tr>';
                        });
                    });

                    html += '</tbody></table>';
                    return html;


                }

                function formatWithoutDecimals(number) {
                    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
                        return new Intl.NumberFormat('id-ID', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(value);
                    }
                }

                let approvalSelected = false;

                function openApproveModal(id_rkm) {
                    console.log('ID yang dikirim ke modal:', id_rkm);
                    $('#id_rkm').val(id_rkm);
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
