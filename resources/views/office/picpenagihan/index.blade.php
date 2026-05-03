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
            <div class="card-body p-0">
                <div class="table-responsive p-3">
                    <table class="table table-hover mb-0 align-middle w-100" id="tabelPicPenagihan">
                        <thead class="bg-light text-dark fw-semibold text-uppercase small">
                            <tr>
                                <th class="ps-4">Perusahaan</th>
                                <th>RKM</th>
                                <th>PIC</th>
                                <th>Alamat</th>
                                <th>Kategori</th>
                                <th>Telepon</th>
                                <th class="text-center pe-4" style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted">
                        </tbody>
                    </table>
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
            $('#tabelPicPenagihan').DataTable({
                "ajax": {
                    "url": "{{ route('picpenagihan.data') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.filter_tahun = $('#filterTahun').val();
                        d.filter_bulan = $('#filterBulan').val();
                    },
                    "dataSrc": "data",
                    "error": function(xhr, error, code) {
                        console.error("Status HTTP: ", xhr.status);
                    }
                },
                "createdRow": function(row, data, dataIndex) {
                    if (data.rkm && data.rkm.tanggal_akhir && data.rkm.outstanding && data.rkm.outstanding.tanggal_bayar) {
                        var tglAkhir = moment(data.rkm.tanggal_akhir);
                        var tglBayar = moment(data.rkm.outstanding.tanggal_bayar);
                        var diffDays = tglBayar.diff(tglAkhir, 'days');

                        var bgColor = "";
                        var textColor = "";

                        if (diffDays < 14) {
                            bgColor = "transparent"; textColor = "inherit"; // Default (Durasi < 14 Hari)
                        } else if (diffDays >= 14 && diffDays < 30) {
                            bgColor = "#d1e7dd"; textColor = "#0f5132"; // Hijau Muda (14 - 29 Hari)
                        } else if (diffDays >= 30 && diffDays < 60) {
                            bgColor = "#6EC207"; textColor = "#ffffff"; // Hijau Tua (30 - 59 Hari)
                        } else if (diffDays >= 60 && diffDays < 120) {
                            bgColor = "#CFECF3"; textColor = "#055160"; // Biru Muda (60 - 119 Hari)
                        } else {
                            bgColor = "#FF5555"; textColor = "#ffffff"; // Merah (>= 120 Hari)
                        }

                        $('td', row).attr('style', 'background-color: ' + bgColor + ' !important; color: ' + textColor + ' !important; border-bottom: 1px solid rgba(0,0,0,0.1);');
                        $(row).removeClass('text-muted');
                    }
                },
                "columns": [
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

                                var htmlOutput = '<div class="text-wrap" style="max-width: 280px;">' +
                                                 '<span class="fw-bold">' + materi + '</span><br>' +
                                                 '<small style="opacity: 0.85;"><i class="far fa-calendar-alt me-1"></i>' + tglAwal.format('DD MMM YYYY') + ' s/d ' + tglAkhir.format('DD MMM YYYY') + '</small>';

                                if (data.rkm.outstanding && data.rkm.outstanding.tanggal_bayar) {
                                    var tglBayar = moment(data.rkm.outstanding.tanggal_bayar);
                                    var diffDays = tglBayar.diff(tglAkhir, 'days');

                                    htmlOutput += '<div class="mt-2 pt-2" style="border-top: 1px solid rgba(0,0,0,0.1);">' +
                                                  '<div style="font-size: 0.8rem;" class="fw-bold"><i class="fas fa-check-circle me-1"></i>Tgl Bayar: ' + tglBayar.format('DD MMM YYYY') + '</div>' +
                                                  '<div style="font-size: 0.75rem;"><i class="fas fa-stopwatch me-1"></i>Durasi Pencairan: ' + diffDays + ' Hari</div>' +
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
                            dropdown += '<button class="btn btn-light btn-sm dropdown-toggle border shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="window">';
                            dropdown += '<i class="fas fa-cog me-1"></i> Aksi';
                            dropdown += '</button>';

                            dropdown += '<ul class="dropdown-menu dropdown-menu-end shadow">';

                            dropdown += '<li><a class="dropdown-item text-success" href="/office/pic-penagihan/pdf/' + row.id + '" target="_blank"><i class="fas fa-file-pdf me-2"></i>Cetak PDF</a></li>';

                            dropdown += '<li><hr class="dropdown-divider"></li>';

                            dropdown += '<li><a class="dropdown-item btn-edit-pic" href="#" data-row=\'' + rowData + '\'><i class="fas fa-edit text-warning me-2"></i>Edit</a></li>';

                            dropdown += '<li>';
                            dropdown += '<form action="/office/pic-penagihan/delete/' + row.id + '" method="POST" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\');">';
                            dropdown += '@csrf';
                            dropdown += '@method("DELETE")';
                            dropdown += '<button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Hapus</button>';
                            dropdown += '</form>';
                            dropdown += '</li>';

                            dropdown += '</ul>';
                            dropdown += '</div>';

                            return dropdown;
                        }
                    }
                ]
            });

            $('#tabelPicPenagihan tbody').on('click', '.btn-edit-pic', function (e) {
                e.preventDefault();

                var rowData = $(this).data('row');

                $('#edit_alamat').val(rowData.alamat || '');
                $('#edit_category').val(rowData.category || '');
                $('#edit_pic').val(rowData.pic || '');
                $('#edit_telepon').val(rowData.telepon || '');

                var actionUrl = '/office/pic-penagihan/update/' + rowData.id;
                $('#formEditPicPenagihan').attr('action', actionUrl);

                $('#modalEditPicPenagihan').modal('show');
            });

            var grafikSlaInstance = null;

            $('#tabelPicPenagihan').on('xhr.dt', function (e, settings, json, xhr) {
                if (json && json.data) {
                    renderGrafikSLA(json.data);
                }
            });

            function renderGrafikSLA(data) {
                var kategoriData = {};

                data.forEach(function(row) {
                    var kategori = row.category ? row.category : 'Tidak Terdefinisi';

                    if (!kategoriData[kategori]) {
                        kategoriData[kategori] = {
                            'Normal': 0,
                            'HijauMuda': 0,
                            'HijauTua': 0,
                            'BiruMuda': 0,
                            'Merah': 0
                        };
                    }

                    if (row.rkm && row.rkm.tanggal_akhir && row.rkm.outstanding && row.rkm.outstanding.tanggal_bayar) {
                        var tglAkhir = moment(row.rkm.tanggal_akhir);
                        var tglBayar = moment(row.rkm.outstanding.tanggal_bayar);
                        var diffDays = tglBayar.diff(tglAkhir, 'days');

                        if (diffDays < 14) {
                            kategoriData[kategori]['Normal']++;
                        } else if (diffDays >= 14 && diffDays < 30) {
                            kategoriData[kategori]['HijauMuda']++;
                        } else if (diffDays >= 30 && diffDays < 60) {
                            kategoriData[kategori]['HijauTua']++;
                        } else if (diffDays >= 60 && diffDays < 120) {
                            kategoriData[kategori]['BiruMuda']++;
                        } else {
                            kategoriData[kategori]['Merah']++;
                        }
                    }
                });

                var labels = Object.keys(kategoriData);
                var datasetNormal = [];
                var datasetHijauMuda = [];
                var datasetHijauTua = [];
                var datasetBiruMuda = [];
                var datasetMerah = [];

                labels.forEach(function(label) {
                    datasetNormal.push(kategoriData[label]['Normal']);
                    datasetHijauMuda.push(kategoriData[label]['HijauMuda']);
                    datasetHijauTua.push(kategoriData[label]['HijauTua']);
                    datasetBiruMuda.push(kategoriData[label]['BiruMuda']);
                    datasetMerah.push(kategoriData[label]['Merah']);
                });

                var ctx = document.getElementById('grafikSlaPencairan').getContext('2d');

                if (grafikSlaInstance) {
                    grafikSlaInstance.destroy();
                }

                grafikSlaInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: '< 14 Hari (Normal)', backgroundColor: '#e9ecef', data: datasetNormal },
                            { label: '(Hijau Muda)', backgroundColor: '#d1e7dd', data: datasetHijauMuda },
                            { label: '(Hijau Tua)', backgroundColor: '#198754', data: datasetHijauTua },
                            { label: '(Biru Muda)', backgroundColor: '#53CBF3', data: datasetBiruMuda },
                            { label: '(Merah)', backgroundColor: '#dc3545', data: datasetMerah }
                        ]
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        scales: {
                            x: {
                                stacked: true,
                                title: { display: true, text: 'Kategori Perusahaan', font: { weight: 'bold' } }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: { stepSize: 1 },
                                title: { display: true, text: 'Jumlah RKM', font: { weight: 'bold' } }
                            }
                        },
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            $('#filterTahun, #filterBulan').change(function() {
                $('#tabelPicPenagihan').DataTable().ajax.reload();
            });

            $('#btnResetFilter').click(function() {
                $('#filterTahun').val('{{ date('Y') }}');
                $('#filterBulan').val('');
                $('#tabelPicPenagihan').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
