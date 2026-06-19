@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">

        <div class="modal fade" id="modalEditPicPenagihan" tabindex="-1" aria-labelledby="modalEditPicPenagihanLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form id="formEditPicPenagihan" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="modalEditPicPenagihanLabel">Edit PIC Penagihan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Alamat Penagihan</label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Kategori</label>
                            <input type="text" name="category" id="edit_category" class="form-control" required>
                        </div>
                        <hr class="my-3">
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Nama PIC</label>
                            <input type="text" name="pic" id="edit_pic" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Telepon PIC</label>
                            <input type="text" name="telepon" id="edit_telepon" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">Update Data</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h4 class="mb-0 fw-bold text-dark">Data PIC Penagihan</h4>

            <div class="d-flex align-items-center gap-2">
                <label class="fw-semibold mb-0 text-nowrap text-secondary">Filter Dibuat:</label>

                <select id="filterTahun" class="form-select form-select-sm shadow-sm border-secondary">
                    <option value="">-- Semua Tahun --</option>
                    @for ($i = date('Y') + 1; $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>

                <select id="filterBulan" class="form-select form-select-sm shadow-sm border-secondary">
                    <option value="">-- Semua Bulan --</option>
                    <option value="01">Januari</option>
                    <option value="02">Februari</option>
                    <option value="03">Maret</option>
                    <option value="04">April</option>
                    <option value="05">Mei</option>
                    <option value="06">Juni</option>
                    <option value="07">Juli</option>
                    <option value="08">Agustus</option>
                    <option value="09">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>

                <button class="btn btn-outline-secondary btn-sm shadow-sm" id="btnResetFilter" title="Reset Filter">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden glass-force">
            <div class="card-header bg-white pt-4 pb-0 border-0">
                <ul class="nav nav-tabs" id="slaTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="tab-hijaumuda" data-bs-toggle="tab" data-bs-target="#pane-hijaumuda" type="button" role="tab" style="color: #9FCB98;">
                            <i class="fas fa-circle me-1" style="color: #9FCB98;"></i> Hijau Muda (< 30 Hari)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="tab-hijautua" data-bs-toggle="tab" data-bs-target="#pane-hijautua" type="button" role="tab" style="color: #6EC207;">
                            <i class="fas fa-circle me-1" style="color: #6EC207;"></i> Hijau Tua (30 - 59 Hari)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="tab-birumuda" data-bs-toggle="tab" data-bs-target="#pane-birumuda" type="button" role="tab" style="color: #36C2CE;">
                            <i class="fas fa-circle me-1" style="color: #36C2CE;"></i> Biru Muda (60 - 119 Hari)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="tab-merah" data-bs-toggle="tab" data-bs-target="#pane-merah" type="button" role="tab" style="color: #FF5555;">
                            <i class="fas fa-circle me-1" style="color: #FF5555;"></i> Merah (≥ 120 Hari)
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="slaTabContent">
                    <!-- Tabel Hijau Muda -->
                    <div class="tab-pane fade show active p-3" id="pane-hijaumuda" role="tabpanel" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle w-100 table-sla" id="tabelHijauMuda">
                                <thead class="bg-light text-dark fw-semibold text-uppercase small">
                                    <tr>
                                        <th class="ps-4" style="width: 15%;">Perusahaan</th>
                                        <th style="width: 25%; min-width: 250px;">RKM</th>
                                        <th style="width: 10%;">PIC</th>
                                        <th style="width: 20%;">Alamat</th>
                                        <th style="width: 5%;">Kategori</th>
                                        <th style="width: 10%;">Telepon</th>
                                        <th class="text-center pe-4" style="width: 15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabel Hijau Tua -->
                    <div class="tab-pane fade p-3" id="pane-hijautua" role="tabpanel" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle w-100 table-sla" id="tabelHijauTua">
                                <thead class="bg-light text-dark fw-semibold text-uppercase small">
                                    <tr>
                                        <th class="ps-4" style="width: 15%;">Perusahaan</th>
                                        <th style="width: 25%; min-width: 250px;">RKM</th>
                                        <th style="width: 10%;">PIC</th>
                                        <th style="width: 20%;">Alamat</th>
                                        <th style="width: 5%;">Kategori</th>
                                        <th style="width: 10%;">Telepon</th>
                                        <th class="text-center pe-4" style="width: 15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabel Biru Muda -->
                    <div class="tab-pane fade p-3" id="pane-birumuda" role="tabpanel" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle w-100 table-sla" id="tabelBiruMuda">
                                <thead class="bg-light text-dark fw-semibold text-uppercase small">
                                    <tr>
                                        <th class="ps-4" style="width: 15%;">Perusahaan</th>
                                        <th style="width: 25%; min-width: 250px;">RKM</th>
                                        <th style="width: 10%;">PIC</th>
                                        <th style="width: 20%;">Alamat</th>
                                        <th style="width: 5%;">Kategori</th>
                                        <th style="width: 10%;">Telepon</th>
                                        <th class="text-center pe-4" style="width: 15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabel Merah -->
                    <div class="tab-pane fade p-3" id="pane-merah" role="tabpanel" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle w-100 table-sla" id="tabelMerah">
                                <thead class="bg-light text-dark fw-semibold text-uppercase small">
                                    <tr>
                                        <th class="ps-4" style="width: 15%;">Perusahaan</th>
                                        <th style="width: 25%; min-width: 250px;">RKM</th>
                                        <th style="width: 10%;">PIC</th>
                                        <th style="width: 20%;">Alamat</th>
                                        <th style="width: 5%;">Kategori</th>
                                        <th style="width: 10%;">Telepon</th>
                                        <th class="text-center pe-4" style="width: 15%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden glass-force mt-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar text-primary me-2"></i> Grafik Pencairan per Kategori
                    </h5>
                </div>
                <div class="w-100">
                    <canvas id="grafikSlaPencairan" height="80"></canvas>
                </div>
            </div>
        </div>

    </div>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {

            // Konfigurasi Standar Kolom untuk Semua Tabel
            var commonColumns = [
                {
                    "data": null,
                    "className": "ps-4 fw-medium",
                    "render": function(data, type, row) {
                        return (data.perusahaan && data.perusahaan.nama_perusahaan) ? data.perusahaan.nama_perusahaan : '-';
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var materi = (data.rkm && data.rkm.materi && data.rkm.materi.nama_materi) ? data.rkm.materi.nama_materi : '-';
                        if (data.rkm && data.rkm.tanggal_awal && data.rkm.tanggal_akhir) {
                            moment.locale('id');
                            var tglAwal = moment(data.rkm.tanggal_awal);
                            var tglAkhir = moment(data.rkm.tanggal_akhir);

                            // Menghapus batasan max-width dan menggunakan min-width dengan white-space normal
                            var htmlOutput = '<div style="min-width: 250px; white-space: normal;">' +
                                             '<span class="fw-bold" style="font-size: 1.05rem;">' + materi + '</span><br>' +
                                             '<small style="opacity: 0.85;"><i class="far fa-calendar-alt me-1"></i>' + tglAwal.format('DD MMM YYYY') + ' s/d ' + tglAkhir.format('DD MMM YYYY') + '</small>';

                            if (data.rkm.outstanding && data.rkm.outstanding.tanggal_bayar) {
                                var tglBayar = moment(data.rkm.outstanding.tanggal_bayar);
                                var diffDays = tglBayar.diff(tglAkhir, 'days');
                                htmlOutput += '<div class="mt-2 pt-2" style="border-top: 1px solid rgba(0,0,0,0.1);">' +
                                              '<div style="font-size: 0.85rem;" class="fw-bold"><i class="fas fa-check-circle me-1"></i>Tgl Bayar: ' + tglBayar.format('DD MMM YYYY') + '</div>' +
                                              '<div style="font-size: 0.8rem;"><i class="fas fa-stopwatch me-1"></i>Durasi Pencairan: ' + diffDays + ' Hari</div>' +
                                              '</div>';
                            }
                            htmlOutput += '</div>';
                            return htmlOutput;
                        }
                        return materi;
                    }
                },
                {
                    "data": "pic",
                    "render": function(data, type, row) {
                        return data ? '<span class="badge border" style="background-color: rgba(0,0,0,0.1); color: inherit;">' + data + '</span>' : '-';
                    }
                },
                {
                    "data": "alamat",
                    "render": function(data, type, row) {
                        return data ? '<div class="text-wrap small" style="max-width: 200px;">' + data + '</div>' : '-';
                    }
                },
                {
                    "data": "category",
                    "render": function(data, type, row) {
                        if (!data) return '-';
                        return '<span class="badge bg-dark text-white bg-opacity-50">' + data + '</span>';
                    }
                },
                {
                    "data": "telepon",
                    "className": "pe-4",
                    "render": function(data, type, row) {
                        return data ? '<a href="tel:' + data + '" style="color: inherit; text-decoration: none;"><i class="fas fa-phone-alt me-1 small"></i>' + data + '</a>' : '-';
                    }
                },
                {
                    "data": null,
                    "className": "text-center pe-4 align-middle",
                    "render": function(data, type, row) {
                        var rowData = JSON.stringify(row).replace(/'/g, "&#39;");
                        var dropdown = '<div class="dropdown">';
                        dropdown += '<button class="btn btn-light btn-sm dropdown-toggle border shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="window"><i class="fas fa-cog me-1"></i> Aksi</button>';
                        dropdown += '<ul class="dropdown-menu dropdown-menu-end shadow">';
                        dropdown += '<li><a class="dropdown-item text-success" href="/office/pic-penagihan/pdf/' + row.id + '" target="_blank"><i class="fas fa-file-pdf me-2"></i>Cetak PDF</a></li>';
                        dropdown += '<li><hr class="dropdown-divider"></li>';
                        dropdown += '<li><a class="dropdown-item btn-edit-pic" href="#" data-row=\'' + rowData + '\'><i class="fas fa-edit text-warning me-2"></i>Edit</a></li>';
                        dropdown += '<li><form action="/office/pic-penagihan/delete/' + row.id + '" method="POST" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\');">@csrf @method("DELETE")<button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Hapus</button></form></li>';
                        dropdown += '</ul></div>';
                        return dropdown;
                    }
                }
            ];

            // Inisialisasi DataTables Kosong
            var dtHijauMuda = $('#tabelHijauMuda').DataTable({ data: [], columns: commonColumns, createdRow: applyRowStyle });
            var dtHijauTua  = $('#tabelHijauTua').DataTable({ data: [], columns: commonColumns, createdRow: applyRowStyle });
            var dtBiruMuda  = $('#tabelBiruMuda').DataTable({ data: [], columns: commonColumns, createdRow: applyRowStyle });
            var dtMerah     = $('#tabelMerah').DataTable({ data: [], columns: commonColumns, createdRow: applyRowStyle });

            // Fungsi Penerapan Warna Baris
            function applyRowStyle(row, data, dataIndex) {
                var bgColor = "", textColor = "";
                if (data.diffDays < 30) {
                    bgColor = "#9FCB98"; textColor = "#0f5132"; // Hijau Muda (< 30)
                } else if (data.diffDays >= 30 && data.diffDays < 60) {
                    bgColor = "#6EC207"; textColor = "#ffffff"; // Hijau Tua
                } else if (data.diffDays >= 60 && data.diffDays < 120) {
                    bgColor = "#36C2CE"; textColor = "#ffffff"; // Biru Muda
                } else {
                    bgColor = "#FF5555"; textColor = "#ffffff"; // Merah
                }
                $('td', row).attr('style', 'background-color: ' + bgColor + ' !important; color: ' + textColor + ' !important; border-bottom: 1px solid rgba(0,0,0,0.1);');
                $(row).removeClass('text-muted');
            }

            var grafikSlaInstance = null;

            // Fungsi Pengambilan dan Pemisahan Data (Single AJAX Call)
            function loadDataSLA() {
                var thn = $('#filterTahun').val();
                var bln = $('#filterBulan').val();

                $.ajax({
                    url: "{{ route('picpenagihan.data') }}",
                    type: "GET",
                    data: { filter_tahun: thn, filter_bulan: bln },
                    success: function(response) {
                        var dataRaw = response.data || [];
                        var dHijauMuda = [], dHijauTua = [], dBiruMuda = [], dMerah = [];

                        dataRaw.forEach(function(row) {
                            if (row.rkm && row.rkm.tanggal_akhir && row.rkm.outstanding && row.rkm.outstanding.tanggal_bayar) {
                                var tglAkhir = moment(row.rkm.tanggal_akhir);
                                var tglBayar = moment(row.rkm.outstanding.tanggal_bayar);
                                var diffDays = tglBayar.diff(tglAkhir, 'days');

                                row.diffDays = diffDays; // Simpan durasi ke dalam properti baris

                                if (diffDays < 30) {
                                    dHijauMuda.push(row);
                                } else if (diffDays >= 30 && diffDays < 60) {
                                    dHijauTua.push(row);
                                } else if (diffDays >= 60 && diffDays < 120) {
                                    dBiruMuda.push(row);
                                } else {
                                    dMerah.push(row);
                                }
                            } else {
                                // Default jika tanggal pembayaran belum ada (masuk Hijau Muda)
                                row.diffDays = 0;
                                dHijauMuda.push(row);
                            }
                        });

                        // Distribusikan data ke masing-masing DataTables
                        dtHijauMuda.clear().rows.add(dHijauMuda).draw();
                        dtHijauTua.clear().rows.add(dHijauTua).draw();
                        dtBiruMuda.clear().rows.add(dBiruMuda).draw();
                        dtMerah.clear().rows.add(dMerah).draw();

                        // Eksekusi pembaruan grafik
                        renderGrafikSLA(dataRaw);
                    },
                    error: function(xhr) {
                        console.error("Gagal mengambil data: ", xhr.status);
                    }
                });
            }

            // Inisiasi pemuatan data pertama kali
            loadDataSLA();

            // Fungsi Rendering Grafik (Telah diadaptasi dengan logika <30 Hari = Hijau Muda)
            function renderGrafikSLA(data) {
                var kategoriData = {};
                data.forEach(function(row) {
                    var kategori = row.category ? row.category : 'Tidak Terdefinisi';
                    if (!kategoriData[kategori]) {
                        kategoriData[kategori] = { 'HijauMuda': 0, 'HijauTua': 0, 'BiruMuda': 0, 'Merah': 0 };
                    }
                    if (row.diffDays !== undefined) {
                        if (row.diffDays < 30) kategoriData[kategori]['HijauMuda']++;
                        else if (row.diffDays >= 30 && row.diffDays < 60) kategoriData[kategori]['HijauTua']++;
                        else if (row.diffDays >= 60 && row.diffDays < 120) kategoriData[kategori]['BiruMuda']++;
                        else kategoriData[kategori]['Merah']++;
                    }
                });

                var labels = Object.keys(kategoriData);
                var datasetHijauMuda = [], datasetHijauTua = [], datasetBiruMuda = [], datasetMerah = [];

                labels.forEach(function(label) {
                    datasetHijauMuda.push(kategoriData[label]['HijauMuda']);
                    datasetHijauTua.push(kategoriData[label]['HijauTua']);
                    datasetBiruMuda.push(kategoriData[label]['BiruMuda']);
                    datasetMerah.push(kategoriData[label]['Merah']);
                });

                var ctx = document.getElementById('grafikSlaPencairan').getContext('2d');
                if (grafikSlaInstance) { grafikSlaInstance.destroy(); }

                grafikSlaInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: '< 30 Hari', backgroundColor: '#9FCB98', data: datasetHijauMuda },
                            { label: '30-59 Hari', backgroundColor: '#6EC207', data: datasetHijauTua },
                            { label: '60-119 Hari', backgroundColor: '#36C2CE', data: datasetBiruMuda },
                            { label: '≥ 120 Hari', backgroundColor: '#dc3545', data: datasetMerah }
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: { intersect: false, mode: 'index' },
                        scales: {
                            x: { stacked: true, title: { display: true, text: 'Kategori Perusahaan', font: { weight: 'bold' } } },
                            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Jumlah RKM', font: { weight: 'bold' } } }
                        },
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            // Event Listeners
            $(document).on('click', '.btn-edit-pic', function (e) {
                e.preventDefault();
                var rowData = $(this).data('row');
                $('#edit_alamat').val(rowData.alamat || '');
                $('#edit_category').val(rowData.category || '');
                $('#edit_pic').val(rowData.pic || '');
                $('#edit_telepon').val(rowData.telepon || '');
                $('#formEditPicPenagihan').attr('action', '/office/pic-penagihan/update/' + rowData.id);
                $('#modalEditPicPenagihan').modal('show');
            });

            $('#filterTahun, #filterBulan').change(function() {
                loadDataSLA();
            });

            $('#btnResetFilter').click(function() {
                $('#filterTahun').val('{{ date('Y') }}');
                $('#filterBulan').val('');
                loadDataSLA();
            });
        });
    </script>
@endsection
