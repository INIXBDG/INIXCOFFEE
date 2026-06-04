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
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detailModalBody" style="max-height: 75vh; overflow-y: auto;">
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
                        <h5 class="modal-title">Apakah Materi Ini Sudah Sesuai?</h5>
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
            <div class="col-md-12 d-flex my-2 justify-content-end"></div>
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
            background-color: #118b35;
            color: #fff;
            padding: 4px 10px;
            border-radius: 4px;
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
            border-radius: 4px;
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
                                examBadge = '<span class="badge-exam">Sudah Rekomendasi</span>';
                            } else if (rkm.exam_status == 'belum_rekomendasi') {
                                examBadge =
                                    '<span class="badge-exam-warning">Belum Rekomendasi</span>';
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

                var pdfUrl = rkm.pdf_peserta;
                if (pdfUrl && !pdfUrl.startsWith('http')) {
                    pdfUrl = '{{ asset('storage') }}/' + pdfUrl;
                }

                html += '<div class="card mb-3 border-0 shadow-sm">';
                html += '<div class="card-body">';

                html +=
                    '<h6 class="text-primary border-bottom pb-2 mb-3"><i class="bi bi-file-earmark-text me-2"></i>Data RKM</h6>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Materi</div><div class="col-md-8">: ' +
                    rkm.materi + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Perusahaan</div><div class="col-md-8">: ' +
                    rkm.perusahaan + '</div></div>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Sales</div><div class="col-md-8">: ' +
                    rkm.sales + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Harga Jual</div><div class="col-md-8">: ' +
                    rkm.harga_jual + '</div></div>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Pax</div><div class="col-md-8">: ' +
                    rkm.pax + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Isi Pax</div><div class="col-md-8">: ' +
                    rkm.isi_pax + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Tanggal Training</div><div class="col-md-8">: ' +
                    rkm.tanggal + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Metode Kelas</div><div class="col-md-8">: ' +
                    rkm.metode_kelas + '</div></div>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Event</div><div class="col-md-8">: ' +
                    rkm.event + '</div></div>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Ruang</div><div class="col-md-8">: ' +
                    rkm.ruang + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Instruktur 1</div><div class="col-md-8">: ' +
                    rkm.instruktur + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Instruktur 2</div><div class="col-md-8">: ' +
                    rkm.instruktur2 + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Asisten</div><div class="col-md-8">: ' +
                    rkm.asisten + '</div></div>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Status</div><div class="col-md-8">: ' +
                    rkm.status + '</div></div>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Exam</div><div class="col-md-8">: ' +
                    rkm.exam + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Authorize</div><div class="col-md-8">: ' +
                    rkm.authorize + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Registrasi Form</div><div class="col-md-8">: ' +
                    rkm.registrasi_form + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Quartal</div><div class="col-md-8">: ' +
                    rkm.quartal + '</div></div>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Bulan</div><div class="col-md-8">: ' +
                    rkm.bulan + '</div></div>';
                html += '<div class="row mb-2"><div class="col-md-4 fw-bold">Tahun</div><div class="col-md-8">: ' +
                    rkm.tahun + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">Makanan</div><div class="col-md-8">: ' +
                    rkm.makanan + '</div></div>';
                html +=
                    '<div class="row mb-2"><div class="col-md-4 fw-bold">PDF Peserta</div><div class="col-md-8">: ' +
                    (rkm.pdf_peserta ? '<a href="' + pdfUrl +
                        '" target="_blank" class="text-primary text-decoration-underline">Lihat / Download</a>' :
                        '-') + '</div></div>';

                html +=
                    '<h6 class="text-success border-bottom pb-2 mb-3 mt-4"><i class="bi bi-clipboard-check me-2"></i>Data Eksam</h6>';
                if (eksam) {
                    var invoiceUrl = eksam.file_invoice;
                    if (invoiceUrl && !invoiceUrl.startsWith('http')) {
                        invoiceUrl = '{{ asset('storage') }}/' + invoiceUrl;
                    }

                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Invoice</div><div class="col-md-8">: ' +
                        eksam.invoice + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">File Invoice</div><div class="col-md-8">: ' +
                        (eksam.file_invoice ? '<a href="' + invoiceUrl +
                            '" target="_blank" class="text-primary text-decoration-underline">Lihat / Download</a>' :
                            '-') + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Tanggal Pengajuan</div><div class="col-md-8">: ' +
                        eksam.tanggal_pengajuan + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Tanggal Mulai</div><div class="col-md-8">: ' +
                        eksam.tanggal_mulai + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Tanggal Selesai</div><div class="col-md-8">: ' +
                        eksam.tanggal_selesai + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Materi</div><div class="col-md-8">: ' +
                        eksam.materi + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Perusahaan</div><div class="col-md-8">: ' +
                        eksam.perusahaan + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Mata Uang</div><div class="col-md-8">: ' +
                        eksam.mata_uang + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Harga</div><div class="col-md-8">: ' +
                        eksam.harga + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Biaya Admin</div><div class="col-md-8">: ' +
                        eksam.biaya_admin + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Harga Rupiah</div><div class="col-md-8">: ' +
                        eksam.harga_rupiah + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Kurs</div><div class="col-md-8">: ' +
                        eksam.kurs + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Kurs Dollar</div><div class="col-md-8">: ' +
                        eksam.kurs_dollar + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Pax</div><div class="col-md-8">: ' +
                        eksam.pax + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Total</div><div class="col-md-8">: ' +
                        eksam.total + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Kode Exam</div><div class="col-md-8">: ' +
                        eksam.kode_exam + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Total Pax</div><div class="col-md-8">: ' +
                        eksam.total_pax + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Keterangan</div><div class="col-md-8">: ' +
                        eksam.keterangan + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Status</div><div class="col-md-8">: ' +
                        eksam.status + '</div></div>';
                    html +=
                        '<div class="row mb-2"><div class="col-md-4 fw-bold">Kode Karyawan</div><div class="col-md-8">: ' +
                        eksam.kode_karyawan + '</div></div>';
                } else {
                    html += '<div class="alert alert-secondary text-center">Data Eksam kosong untuk RKM ini.</div>';
                }

                html += '</div>';
                html += '</div>';
            });

            $('#detailModalBody').html(html);
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
