@extends('layouts_office.app')
@section('office_contents')
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

        <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Detail Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detailModalBody" style="max-height: 80vh;">
                        Memuat detail...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="bundlingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Apakah Exam Ini Bandling Atau Tidak?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <select class="form-select" id="bundlingStatusSelect">
                                <option value="0">Kosong</option>
                                <option value="1">Ya</option>
                                <option value="2">Tidak</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" onclick="updateBundling()">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="width: 100%">
                    <div class="card-body d-flex justify-content-center">
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
                            <label for="bulan" class="form-label">Bulan</label>
                            <select id="bulan" class="form-select" aria-label="bulan">
                                <option disabled>Pilih Bulan</option>
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
                                        echo "<option value=\"$bulan\" $selected>$bulan_awal - $bulan_akhir</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-4 mx-1">
                            <button type="submit" onclick="getDataExam()" class="btn btn-primary"
                                style="margin-top: 30px; height: 37px;">Cari Data</button>
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

    <style>
        #content {
            overflow-y: hidden;
        }

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

        .badge-exam {
            color: #118b35;
            padding: 4px 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-exam-warning {
            background-color: #ffc107;
            color: #000;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-exam-secondary {
            background-color: #6c757d;
            color: #fff;
            padding: 4px 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .row-bundling-no {
            background-color: #e8f5e9 !important;
            color: #1b5e20 !important;
        }

        .row-bundling-no td {
            color: #1b5e20 !important;
            border-color: #c8e6c9 !important;
        }

        .row-bundling-no .text-muted,
        .row-bundling-no .text-light {
            color: #2e7d32 !important;
        }

        .row-bundling-yes {
            background-color: #4caf50 !important;
            color: #ffffff !important;
        }

        .row-bundling-yes td {
            color: #ffffff !important;
            border-color: #4caf50 !important;
        }

        .row-bundling-yes .text-muted,
        .row-bundling-yes .text-light {
            color: #e8f5e9 !important;
        }

        .notification-success .modal-header {
            background-color: #d4edda;
            border-bottom: 2px solid #28a745;
        }

        .notification-error .modal-header {
            background-color: #f8d7da;
            border-bottom: 2px solid #dc3545;
        }

        .notification-info .modal-header {
            background-color: #d1ecf1;
            border-bottom: 2px solid #17a2b8;
        }

        .detail-row {
            display: flex;
            padding: 8px 0;
            align-items: flex-start;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            flex: 0 0 45%;
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
        }

        .detail-separator {
            flex: 0 0 5%;
            color: #888;
            font-weight: 600;
        }

        .detail-value {
            flex: 0 0 50%;
            color: #222;
            font-size: 0.9rem;
            word-break: break-word;
        }

        .detail-value.text-currency-dollar {
            font-weight: 600;
            color: #c77d00;
            font-family: 'Courier New', monospace;
        }

        .section-title i {
            margin-right: 8px;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-acc {
            background: #d4edda;
            color: #155724;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-reject {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-eksam {
            text-align: center;
            padding: 30px 15px;
            color: #888;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .empty-eksam i {
            font-size: 2.5rem;
            color: #bbb;
            margin-bottom: 10px;
        }

        @media screen and (max-width: 768px) {
            .detail-label {
                flex: 0 0 40%;
            }

            .detail-separator {
                flex: 0 0 5%;
            }

            .detail-value {
                flex: 0 0 55%;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script>
        $(document).ready(function() {
            getDataExam();
        });

        function showNotification(title, message, type = 'info') {
            $('#notificationTitle').text(title);
            $('#notificationBody').text(message);
            $('#notificationModal').removeClass('notification-success notification-error notification-info');
            $('#notificationModal').addClass('notification-' + type);
            $('#notificationModal').modal('show');
        }

        function getDataExam() {
            var tahun = document.getElementById('tahun').value;
            var bulan = document.getElementById('bulan').value;

            $('#loadingModal').modal('show');

            $.ajax({
                url: "/office/exam/" + tahun + "/" + bulan,
                method: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#loadingModal').modal('show');
                },
                complete: function() {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 800);
                },
                success: function(response) {
                    renderExamData(response);
                },
                error: function(xhr) {
                    console.error("Error fetching data:", xhr);
                    showNotification('Error!', 'Gagal memuat data. Silakan coba lagi.', 'error');
                    $('#loadingModal').modal('hide');
                }
            });
        }

        function renderExamData(response) {
            var html = '';
            moment.locale('id');

            if (!response.data || response.data.length === 0) {
                html += '<div class="alert alert-warning text-center">';
                html += '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
                html += 'Tidak ada data kelas dengan Exam pada periode ini.';
                html += '</div>';
                $('#content').html(html);
                return;
            }

            response.data.forEach(function(monthData) {
                monthData.weeksData.forEach(function(weekData) {
                    var startOfWeek = moment(weekData.start);
                    var endOfWeek = startOfWeek.clone().add(4, 'days');

                    html += '<div class="card my-1">';
                    html += '<div class="card-body table-responsive">';
                    html += '<h3 class="card-title my-1">Rencana Kelas Mingguan</h3>';
                    html += '<p class="card-title my-1">Periode : ' + moment(startOfWeek).format(
                        'DD MMMM YYYY') + ' - ' + moment(endOfWeek).format('DD MMMM YYYY') + '</p>';

                    html += '<table class="table table-responsive table-striped">';
                    html += '<thead>';
                    html += '<tr>';
                    html += '<th scope="col">No</th>';
                    html += '<th scope="col">Materi</th>';
                    html += '<th scope="col">Tanggal Training</th>';
                    html += '<th scope="col">Perusahaan</th>';
                    html += '<th scope="col">Kode Sales</th>';
                    html += '<th scope="col">Instruktur</th>';
                    html += '<th scope="col">Exam</th>';
                    html += '<th scope="col">Bundling</th>';
                    html += '<th scope="col">Metode Kelas</th>';
                    html += '<th scope="col">Event</th>';
                    html += '<th scope="col">Ruang</th>';
                    html += '<th scope="col">Pax</th>';
                    html += '<th scope="col">Aksi</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';

                    if (weekData.data.length === 0) {
                        html += '<tr>';
                        html += '<td colspan="13" class="text-center">Tidak Ada Kelas Mingguan</td>';
                        html += '</tr>';
                    } else {
                        weekData.data.forEach(function(rkm, index) {
                            var rowClass = '';
                            if (rkm.bundling_status == 1) {
                                rowClass = 'row-bundling-yes';
                            } else if (rkm.bundling_status == 2) {
                                rowClass = 'row-bundling-no';
                            }

                            var tanggalTraining = '';
                            if (rkm.tanggal_awal == rkm.tanggal_akhir) {
                                tanggalTraining = moment(rkm.tanggal_awal).format('DD MMMM YYYY');
                            } else {
                                tanggalTraining = moment(rkm.tanggal_awal).format('DD MMMM YYYY') +
                                    ' s/d ' + moment(rkm.tanggal_akhir).format('DD MMMM YYYY');
                            }

                            var perusahaanText = '-';
                            if (rkm.perusahaan && rkm.perusahaan.length > 0) {
                                perusahaanText = rkm.perusahaan.map(p => p.nama_perusahaan).join(
                                    ', ');
                            }

                            var salesText = '-';
                            if (rkm.sales && rkm.sales.length > 0) {
                                salesText = rkm.sales.map(s => s.kode_karyawan).join(', ');
                            }

                            var instrukturText = 'Belum Ditentukan';
                            if (rkm.instruktur_all && rkm.instruktur_all.trim() !== '') {
                                instrukturText = rkm.instruktur_all.split(', ')[0];
                            }

                            var eventText = (rkm.event && rkm.event !== '-') ? rkm.event :
                                'Belum Ditentukan';
                            var ruangText = (rkm.ruang && rkm.ruang !== '-') ? rkm.ruang :
                                'Belum Ditentukan';

                            var rkmId = rkm.id || 0;

                            var bundlingText = 'Kosong';
                            if (rkm.bundling_status == 1) bundlingText = 'Ya';
                            else if (rkm.bundling_status == 2) bundlingText = 'Tidak';

                            var examBadge = '';
                            if (rkm.exam_status == 'sudah_rekomendasi') {
                                examBadge = '<p class="badge-exam">Sudah Pengajuan</p>';
                            } else {
                                examBadge =
                                    'Belum Pengajuan';
                            }

                            html += '<tr class="' + rowClass + '">';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td>' + (rkm.materi?.nama_materi || '-') + '</td>';
                            html += '<td>' + tanggalTraining + '</td>';
                            html += '<td>' + perusahaanText + '</td>';
                            html += '<td>' + salesText + '</td>';
                            html += '<td>' + instrukturText + '</td>';
                            html += '<td>' + examBadge + '</td>';
                            html += '<td><strong>' + bundlingText + '</strong></td>';
                            html += '<td>' + (rkm.metode_kelas || '-') + '</td>';
                            html += '<td>' + eventText + '</td>';
                            html += '<td>' + ruangText + '</td>';
                            html += '<td>' + (rkm.total_pax || 0) + '</td>';

                            html += '<td>';
                            html += '<div class="btn-group dropup">';
                            html +=
                                '<button type="button" class="btn dropdown-toggle text-black" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                            html += 'Actions';
                            html += '</button>';
                            html +=
                                '<div class="dropdown-menu" style="max-height: 250px; overflow-y: auto;">';
                            html +=
                                '<a class="dropdown-item" href="javascript:void(0)" onclick="showDetail(\'' +
                                rkmId +
                                '\')"><i class="bi bi-eye text-primary me-2"></i>Detail</a>';
                            html +=
                                '<a class="dropdown-item" href="javascript:void(0)" onclick="showBundlingModal(\'' +
                                rkmId + '\', \'' + (rkm.bundling_status || '0') +
                                '\')"><i class="bi bi-box-seam text-success me-2"></i>Bundling</a>';
                            html += '</div>';
                            html += '</div>';
                            html += '</td>';

                            html += '</tr>';
                        });
                    }

                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                });
            });

            setTimeout(() => {
                $('#content').html(html);
            }, 500);
        }

        function showDetail(id) {
            $('#detailModalBody').html(
                '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Memuat detail...</p></div>'
            );
            $('#detailModal').modal('show');

            $.ajax({
                url: "/office/exam/detail/" + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderDetailData(response.data);
                    } else {
                        $('#detailModalBody').html(
                            '<p class="text-danger text-center">Gagal memuat detail.</p>');
                    }
                },
                error: function() {
                    $('#detailModalBody').html(
                        '<p class="text-danger text-center">Terjadi kesalahan saat memuat detail.</p>');
                }
            });
        }

        function renderDetailData(dataArray) {
            var html = '';

            dataArray.forEach(function(data, index) {
                var rkm = data.rkm;
                var eksam = data.eksam;

                // URL PDF Peserta
                var pdfUrl = rkm.pdf_peserta;
                if (pdfUrl && !pdfUrl.startsWith('http')) {
                    pdfUrl = "{{ asset('storage') }}/" + pdfUrl;
                }

                // Badge Status RKM
                var statusBadge = '';
                if (rkm.status === 'Ya') statusBadge = '<span class="badge-status badge-acc">Ya</span>';
                else if (rkm.status === 'Tidak') statusBadge =
                    '<span class="badge-status badge-reject">Tidak</span>';
                else statusBadge = '<span class="badge-status badge-pending">Tidak</span>';

                html += '<div class="detail-card">';
                html += '<div class="detail-card-body">';

                // === KOLOM KIRI: INFO DASAR RKM ===
                html += '<div class="row">';
                html += '<div class="col-md-6">';

                html += '<div class="section-title"><i class="bi bi-info-circle"></i>Informasi Dasar</div>';
                html += buildRow('ID RKM', rkm.id || '-');
                html += buildRow('Materi', rkm.materi);
                html += buildRow('Perusahaan', rkm.perusahaan);
                html += buildRow('Sales', rkm.sales);
                html += buildRow('Tanggal Training', rkm.tanggal);
                html += buildRow('Metode Kelas', rkm.metode_kelas);
                html += buildRow('Event', rkm.event);
                html += buildRow('Ruang', rkm.ruang);
                html += buildRow('Pax', rkm.pax + ' Peserta');
                html += buildRow('Isi Pax', rkm.isi_pax);
                html += buildRow('Status', statusBadge, true);
                html += buildRow('Exam', rkm.exam);

                html += '</div>'; // end col-md-6 kiri

                // === KOLOM KANAN: INSTRUKTUR & LAINNYA ===
                html += '<div class="col-md-6">';

                html += '<div class="section-title"><i class="bi bi-people"></i>Tim Pengajar</div>';
                html += buildRow('Instruktur 1', rkm.instruktur);
                html += buildRow('Instruktur 2', rkm.instruktur2);
                html += buildRow('Asisten', rkm.asisten);

                html += '<div class="section-title"><i class="bi bi-calendar3"></i>Informasi Periode</div>';
                html += buildRow('Quartal', rkm.quartal);
                html += buildRow('Bulan', rkm.bulan);
                html += buildRow('Tahun', rkm.tahun);
                html += buildRow('Authorize', rkm.authorize);

                // Harga Jual RKM (selalu Rupiah)
                html += buildRowCurrency('Harga Jual', rkm.harga_jual, 'Rupiah');

                html += '</div>'; // end col-md-6 kanan
                html += '</div>'; // end row

                // === BAGIAN EKSAM (Full Width) ===
                html += '<hr class="my-3">';
                if (eksam) {
                    // URL File Invoice
                    var invoiceUrl = eksam.file_invoice;
                    if (invoiceUrl && !invoiceUrl.startsWith('http')) {
                        invoiceUrl = "{{ asset('storage') }}/" + invoiceUrl;
                    }

                    html += '<div class="detail-card" style="box-shadow:none; border:1px solid #ddd;">';
                    html += '<div class="detail-card-body">';

                    html += '<div class="row">';

                    html += '<div class="col-md-6">';
                    html += buildRow('Invoice', eksam.invoice);
                    html += buildRow('Kode Exam', eksam.kode_exam);
                    html += buildRow('Materi', eksam.materi);
                    html += buildRow('Perusahaan', eksam.perusahaan);
                    html += buildRow('Status', eksam.status);
                    html += buildRow('Kode Karyawan', eksam.kode_karyawan);
                    html += buildRow('Keterangan', eksam.keterangan);
                    html += buildRow('Tanggal Pengajuan', eksam.tanggal_pengajuan);
                    html += buildRow('Tanggal Mulai', eksam.tanggal_mulai);
                    html += buildRow('Tanggal Selesai', eksam.tanggal_selesai);

                    // Link File Invoice
                    if (eksam.file_invoice) {
                        html += '<div class="detail-row"><div class="detail-value"><a href="' + invoiceUrl +
                            '" target="_blank" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-invoice me-1"></i>Lihat Invoice</a></div></div>';
                    }
                    html += '</div>'; // end col kiri eksam

                    html += '<div class="col-md-6">';
                    html +=
                        '<div class="section-title" style="color:#118b35; border-color:#118b35;"><i class="bi bi-cash-coin"></i>Rincian Harga</div>';
                    html += buildRow('Mata Uang', eksam.mata_uang);

                    // Harga sesuai mata uang
                    html += buildRowCurrency('Harga', eksam.harga, eksam.mata_uang);
                    html += buildRowCurrency('Biaya Admin', eksam.biaya_admin, eksam.mata_uang);

                    html += buildRow('Pax', eksam.pax + ' Peserta');
                    html += buildRow('Total Pax', eksam.total_pax);

                    // Kurs
                    if (eksam.kurs && eksam.kurs != 0) {
                        html += buildRowCurrency('Kurs', eksam.kurs, 'Rupiah');
                    }
                    if (eksam.kurs_dollar && eksam.kurs_dollar != 0) {
                        html += buildRowCurrency('Kurs Dollar', eksam.kurs_dollar, 'Dollar');
                    }

                    // Harga dalam Rupiah (selalu Rupiah)
                    html += buildRowCurrency('Harga dalam Rupiah', eksam.harga_rupiah, 'Rupiah');
                    html += buildRowCurrency('Total', eksam.total, 'Rupiah', true);

                    html += '</div>'; // end col kanan eksam
                    html += '</div>'; // end row

                    html += '</div>'; // end detail-card-body eksam
                    html += '</div>'; // end detail-card eksam
                } else {
                    html += '<div class="empty-eksam">';
                    html += '<i class="bi bi-inbox d-block"></i>';
                    html += '<strong>Data Eksam Kosong</strong><br>';
                    html += '<small>Belum ada data exam yang diajukan untuk RKM ini.</small>';
                    html += '</div>';
                }

                html += '</div>'; // end detail-card-body
                html += '</div>'; // end detail-card
            });

            $('#detailModalBody').html(html);
        }

        // === HELPER FUNCTIONS ===

        // Build row detail standar
        function buildRow(label, value, isHtml = false) {
            var displayValue = (value === null || value === undefined || value === '') ? '-' : value;
            if (isHtml) {
                return '<div class="detail-row"><div class="detail-label">' + label +
                    '</div><div class="detail-separator">:</div><div class="detail-value">' + displayValue + '</div></div>';
            }
            return '<div class="detail-row"><div class="detail-label">' + label +
                '</div><div class="detail-separator">:</div><div class="detail-value">' + escapeHtml(String(displayValue)) +
                '</div></div>';
        }

        // Build row dengan format mata uang
        function buildRowCurrency(label, value, mataUang, isTotal = false) {
            var numValue = parseFloat(value) || 0;
            var formatted = formatCurrency(numValue, mataUang);
            var cssClass = 'text-currency';
            if (mataUang && mataUang.toLowerCase() !== 'rupiah') {
                cssClass = 'text-currency-dollar';
            }
            if (isTotal) {
                cssClass += ' fw-bold';
            }
            return '<div class="detail-row"><div class="detail-label">' + label +
                '</div><div class="detail-separator">:</div><div class="detail-value ' + cssClass + '">' + formatted +
                '</div></div>';
        }

        // Format currency (Rupiah / Dollar / lainnya)
        function formatCurrency(angka, mataUang) {
            if (!angka && angka !== 0) return '-';
            var num = parseFloat(angka);
            if (isNaN(num)) return '-';

            // Format angka dengan separator ribuan
            var formatted = num.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            });

            if (!mataUang) return formatted;

            var mu = mataUang.toString().toLowerCase();
            if (mu === 'rupiah' || mu === 'idr' || mu === 'rp') {
                return 'Rp ' + formatted;
            } else if (mu === 'dollar' || mu === 'usd' || mu === '$') {
                return '$ ' + formatted;
            } else {
                return formatted + ' ' + mataUang;
            }
        }

        // Escape HTML untuk mencegah XSS
        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }

        function showBundlingModal(id, currentStatus) {
            $('#bundlingModal').data('id', id);
            $('#bundlingStatusSelect').val(currentStatus);
            $('#bundlingModal').modal('show');
        }

        function updateBundling() {
            var id = $('#bundlingModal').data('id');
            var status = $('#bundlingStatusSelect').val();

            $.ajax({
                url: "/office/exam/update-bundling",
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        $('#bundlingModal').modal('hide');
                        getDataExam();
                        showNotification('Berhasil!', 'Status bundling telah diperbarui.', 'success');
                    }
                },
                error: function() {
                    showNotification('Error!', 'Gagal memperbarui status bundling.', 'error');
                }
            });
        }
    </script>
@endsection
