@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
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
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                @can('Create Feedback')
                <a href="{{ route('nilaifeedback.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah Perusahaan"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Isi Feedback</a>
                @endcan
                @can('Detail Feedback Per Bulan')
                <a href="{{ route('detailfeedbacks') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Detail Feedback Per Bulan"><img src="{{ asset('icon/clipboard.svg') }}" class="" width="30px"> Detail Feedback Per Bulan</a>
                @endcan
            </div>
            <div class="card">
                <div class="card-body table-responsive">
                    <h4 class="card-title mt-3 text-center">&nbsp;Data Feedback</h4>
                    <table id="datafeedback" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col">RKM</th>
                                <th>Instruktur</th>
                                <th>Sales</th>
                                <th>Tanggal Awal</th>
                                <th>Tanggal Akhir</th>
                                <th>Materi</th>
                                <th>Pelayanan</th>
                                <th>Fasilitas</th>
                                <th>Instuktur</th>
                                <th>Instruktur 2</th>
                                <th>Asisten</th>
                                <th>created_at</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_feedback_pelayanan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content rounded">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Feedback Pelayanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalContentFeedbackPelayanan">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/ashl1/datatables-rowsgroup@fbd569b8768155c7a9a62568e66a64115887d7d0/dataTables.rowsGroup.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function() {
        var idInstruktur = "{{ auth()->user()->id_instruktur }}";
        var idSales = "{{ auth()->user()->id_sales }}";
        if (idInstruktur == 'AD') {
            var idInstruktur = "";
        }
        if (idSales == 'AM') {
            var idSales = "";
        }
        $('#datafeedback').DataTable({
            // 'rowsGroup': [0,1],
            "dom": 'Bfrtip',
            "buttons": [{
                    extend: 'excel',
                    text: 'Export to Excel',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] // Kolom yang akan diekspor ke Excel
                    },
                },
                {
                    extend: 'pdf',
                    text: 'Export to PDF',
                    exportOptions: {
                        columns: [0, 2, 3, 4, 5, 6, 7, 8, 9] // Kolom yang akan diekspor ke PDF
                    },
                    customize: function(doc) {
                        doc.content[1].table.widths = ['*', '*', '*', '*', '*', '*', '*', '*', '*']; // Menyesuaikan lebar kolom
                        doc.content.splice(0, 1, {
                            text: 'Inixindo E-Office Data Feedback',
                            fontSize: 12,
                            alignment: 'center',
                            margin: [0, 0, 0, 12] // Margin dari header
                        });
                        doc['footer'] = function(currentPage, pageCount) {
                            return {
                                text: 'Data Feedback ' + currentPage.toString() + ' of ' + pageCount,
                                alignment: 'center',
                                margin: [0, 0, 0, 12] // Margin dari footer
                            };
                        };
                    }
                }
            ],
            "ajax": {
                "url": "{{ route('getFeedbacks') }}", // URL API untuk mengambil data
                "type": "GET",
                "beforeSend": function() {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function() {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function() {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function() {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                }
            },
            "columns": [{
                    "data": "nama_materi"
                },
                {
                    "data": "instruktur_key"
                },
                {
                    "data": "sales_key",
                    "visible": true
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        moment.locale('id');
                        var tanggalAwal = moment(data.tanggal_awal).format('DD MMMM YYYY');
                        return tanggalAwal;
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        moment.locale('id');
                        var tanggalAkhir = moment(data.tanggal_akhir).format('DD MMMM YYYY');
                        return tanggalAkhir;
                    }
                },
                {
                    "data": "averageM"
                },
                {
                    "data": "averageP"
                },
                {
                    "data": "averageF"
                },
                {
                    "data": "averageI"
                },
                {
                    "data": "averageIb"
                },
                {
                    "data": "averageIas"
                },
                {
                    "data": "tanggal_awal",
                    "visible": false
                },

                {
                    "data": null,
                    "render": function(data, type, row) {
                        moment.locale('en');
                        var tanggalAwal = moment(data.tanggal_awal).format('YYYY-MM-DD');
                        var parts = tanggalAwal.split('-');
                        var tahun = parts[0];
                        var bulan = parts[1];
                        var hari = parts[2];
                        const userRole = "{{ auth()->user()->karyawan->divisi }}";
                        var actions = "";
                        actions += '<div class="dropdown">';
                        actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                        actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                        actions += '<a class="dropdown-item" href="{{ url('/feedback') }}/' + row.materi_key + 'ixb' + bulan + 'ixb' + tahun + 'ixb' + hari + '" data-toggle="tooltip" data-placement="top" title="Detail Feedback"><img src="{{ asset('icon/clipboard-primary.svg ') }}" class=""> Detail</a>';
                        if (userRole === "Office") {
                            actions += '<a class="dropdown-item" id="feebackPelayanan" ' +
                                'data-materi_key="' + row.materi_key + '" ' +
                                'data-bulan="' + bulan + '" ' +
                                'data-tahun="' + tahun + '" ' +
                                'data-hari="' + hari + '" ' +
                                'title="Detail Feedback Pelayanan">' +
                                '<img src="{{ asset('icon/clipboard-primary.svg') }}"> Feedback Pelayanan</a>';
                        }
                        actions += '</div>';
                        actions += '</div>';
                        return actions;
                    }
                }
            ],
            "columnDefs": [{
                "targets": [11],
                "type": "date"
            }],
            "order": [
                [11, 'desc']
            ], // Ubah urutan menjadi descending untuk kolom ke-6

            "initComplete": function() {
                this.api().columns(1).search(idInstruktur).draw();
                this.api().columns(2).search(idSales).draw();
            }
        });
    });

    $(document).on('click', '#feebackPelayanan', function () {
        var materi_key = $(this).data('materi_key');
        var bulan = $(this).data('bulan');
        var tahun = $(this).data('tahun');
        var hari = $(this).data('hari');

        $.ajax({
            url: "{{ route('feedbackPelayanan') }}",
            type: "GET",
            data: {
                materi_key: materi_key,
                bulan: bulan,
                tahun: tahun,
                hari: hari
            },
            success: function (response) {
                const content = $('#modalContentFeedbackPelayanan');
                const data = Array.isArray(response) ? response : (response?.post || []);

                if (!Array.isArray(data) || data.length === 0 || !data[0].data || data[0].data.length === 0) {
                    content.html(`
                        <div class="p-4">
                            <h5 class="text-center">Tidak ada data feedback pelayanan!</h5>
                        </div>
                    `);
                    showModal();
                    return;
                }

                const group = data[0];
                const pesertaList = group.data;
                const totalPeserta = pesertaList.length;

                const fields = {
                    M: ['M1', 'M2', 'M3', 'M4'],
                    P: ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7'],
                    F: ['F1', 'F2', 'F3', 'F4', 'F5'],
                    I: ['I1', 'I2', 'I3', 'I4', 'I5', 'I6', 'I7', 'I8']
                };

                let avg = {};
                for (const category in fields) {
                    fields[category].forEach(f => avg[f] = 0);
                }

                pesertaList.forEach(p => {
                    const fb = p.datafeedbacks || {};
                    for (const category in fields) {
                        fields[category].forEach(f => {
                            const val = parseFloat(fb[f]) || 0;
                            avg[f] += val;
                        });
                    }
                });

                for (const category in fields) {
                    fields[category].forEach(f => {
                        let val = avg[f] / totalPeserta;
                        val = parseFloat(val.toFixed(2));
                        avg[f] = val;
                    });
                }

                const hasInstruktur2 = pesertaList.some(p => p.datafeedbacks?.I1b != null);
                const hasAsisten = pesertaList.some(p => p.datafeedbacks?.I1as != null);

                const sampleFeedback = pesertaList[0].datafeedbacks || {};

                const formatTanggal = (dateStr) => {
                    const options = { day: 'numeric', month: 'long', year: 'numeric' };
                    return new Date(dateStr).toLocaleDateString('id-ID', options);
                };
                const firstFeedback = pesertaList[0];

                let html = `
                    <div class="container-fluid">
                        <h5 class="mb-4">Detail Feedbacks</h5>
                        <div class="row" style="height: 500px;">
                            <div class="col-lg-5 col-md-12 col-sm-12">
                                <div class="row">
                                    <div class="col-4"><p>Nama Materi</p></div>
                                    <div class="col-1"><p>:</p></div>
                                    <div class="col-7"><p>${firstFeedback.nama_materi || '-'}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-4"><p>Tanggal Pelaksanaan</p></div>
                                    <div class="col-1"><p>:</p></div>
                                    <div class="col-7">
                                        <p>
                                            ${firstFeedback.tanggal_awal ? formatTanggal(firstFeedback.tanggal_awal) : '-'} 
                                            s/d 
                                            ${firstFeedback.tanggal_akhir ? formatTanggal(firstFeedback.tanggal_akhir) : '-'}
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4"><p>Instruktur</p></div>
                                    <div class="col-1"><p>:</p></div>
                                    <div class="col-7"><p>${firstFeedback.instruktur_key || '-'}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-4"><p>Instruktur 2</p></div>
                                    <div class="col-1"><p>:</p></div>
                                    <div class="col-7"><p>${firstFeedback.instruktur_key2 || '-'}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-4"><p>Asisten</p></div>
                                    <div class="col-1"><p>:</p></div>
                                    <div class="col-7"><p>${firstFeedback.asisten_key || '-'}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-4"><p>Perusahaan</p></div>
                                    <div class="col-1"><p>:</p></div>
                                    <div class="col-7"><p>${group.nama_perusahaan || '-'}</p></div>
                                </div>
                                <div class="row">
                                    <div class="col-4"><p>Sales</p></div>
                                    <div class="col-1"><p>:</p></div>
                                    <div class="col-7"><p>${firstFeedback.sales_key || '-'}</p></div>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-12 col-sm-12">
                                <div class="card" style="height: 500px;">
                                    <div class="card-body" style="overflow-y: auto;">
                                        <nav>
                                            <div class="nav nav-tabs" id="nav-tab-modal" role="tablist">
                                                <button class="nav-link active" id="nav-nilai-tab" data-bs-toggle="tab" data-bs-target="#nav-nilai" type="button" role="tab">Nilai Keseluruhan</button>
                                                ${pesertaList.map((_, idx) => `
                                                    <button class="nav-link" id="nav-detail-tab-${idx+1}" data-bs-toggle="tab" data-bs-target="#nav-detail-${idx+1}" type="button" role="tab">Peserta ${idx+1}</button>
                                                `).join('')}
                                            </div>
                                        </nav>
                                        <div class="tab-content mt-3" id="nav-tabContent-modal">
                                            <div class="tab-pane fade show active" id="nav-nilai" role="tabpanel">
                                                <h5>Rata-rata dari ${totalPeserta} peserta</h5>
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <tr><td colspan="2"><h5>Pelayanan</h5></td></tr>
                                                        <tr><td>Informasi mudah dan tepat</td><td>${avg.P1}</td></tr>
                                                        <tr><td>Penyambutan dan pembukaan</td><td>${avg.P2}</td></tr>
                                                        <tr><td>Kenyamanan ruang kelas</td><td>${avg.P3}</td></tr>
                                                        <tr><td>Keramahan staf</td><td>${avg.P4}</td></tr>
                                                        <tr><td>Kesigapan staf dalam menangani masalah</td><td>${avg.P5}</td></tr>
                                                        <tr><td>Registrasi dan administrasi training</td><td>${avg.P6}</td></tr>
                                                        <tr><td>Kualitas makanan dan minuman</td><td>${avg.P7}</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            ${pesertaList.map((fb, idx) => {
                                                const df = fb.datafeedbacks || {};
                                                return `
                                                    <div class="tab-pane fade" id="nav-detail-${idx+1}" role="tabpanel">
                                                        <table class="table table-bordered">
                                                            <tbody>
                                                                <tr><td colspan="2"><h5>Pelayanan</h5></td></tr>
                                                                <tr><td>Informasi mudah dan tepat</td><td>${df.P1 || '-'}</td></tr>
                                                                <tr><td>Penyambutan dan pembukaan</td><td>${df.P2 || '-'}</td></tr>
                                                                <tr><td>Kenyamanan ruang kelas</td><td>${df.P3 || '-'}</td></tr>
                                                                <tr><td>Keramahan staf</td><td>${df.P4 || '-'}</td></tr>
                                                                <tr><td>Kesigapan staf dalam menangani masalah</td><td>${df.P5 || '-'}</td></tr>
                                                                <tr><td>Registrasi dan administrasi training</td><td>${df.P6 || '-'}</td></tr>
                                                                <tr><td>Kualitas makanan dan minuman</td><td>${df.P7 || '-'}</td></tr>
                                                                <tr><td colspan="2"><h5>Pengalaman yang anda anggap berkesan sewaktu mengikuti training di sini?</h5></tr>
                                                                <tr><td colspan="2">${df.U1 || '-'}</td></tr>
                                                                <tr><td colspan="2"><h5>Saran dan Usulan perbaikan</h5></tr>
                                                                <tr><td colspan="2">${df.U2 || '-'}</td></tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                content.html(html);
                showModal();
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                $('#modalContentFeedbackPelayanan').html('<div class="p-4 text-center text-danger">Gagal memuat data.</div>');
                showModal();
            }
        });
    });

    function showModal() {
        const modalEl = document.getElementById('modal_feedback_pelayanan');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
</script>
@endsection