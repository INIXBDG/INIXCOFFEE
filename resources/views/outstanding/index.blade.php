@extends('layouts.app')

@section('content')
    <div class="modal fade" id="modalExport" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formExport" method="GET">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-filter me-2"></i>Filter Export Laporan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Akhir</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tipe Outstandig</label>
                                <select name="tipe_outstanding" class="form-select">
                                    <option value="">Semua Outstanding</option>
                                    <option value="Outstanding">Outstanding</option>
                                    <option value="Outstanding PA">Outstanding PA</option>
                                    <option value="Lunas">Lunas</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"
                            formaction="{{ route('outstanding.export.excel') }}">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </button>
                        <button type="submit" class="btn btn-danger"
                            formaction="{{ route('outstanding.export.pdf') }}">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
                <div class="d-flex justify-content-end">
                    @can('Create Outstanding')
                        <a href="{{ route('outstanding.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip"
                            data-placement="top" title="Tambah User"><img src="{{ asset('icon/plus.svg') }}" class=""
                                width="30px"> List Outstanding</a>
                        <button type="button" class="btn click-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">Singkron Data</span>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('outstanding.singkronDataOutstanding') }}">Singkron Data
                                Minggu Ini</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                data-bs-target="#modalSinkron">Singkron Data</a>
                        </div>
                    @endcan
                </div>
                    <div class="btn-group">
                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-export me-1"></i> Export Laporan
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalExport">
                                    <i class="fas fa-cog me-2"></i> Export dengan Filter
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('outstanding.export.excel') }}">
                                    <i class="fas fa-file-excel text-success me-2"></i> Excel (Semua Data)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('outstanding.export.pdf') }}">
                                    <i class="fas fa-file-pdf text-danger me-2"></i> PDF (Semua Data)
                                </a>
                            </li>
                        </ul>
                    </div>
                <div class="modal fade" id="modalSinkron" tabindex="-1" role="dialog"
                    aria-labelledby="modalSinkronLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form action="{{ route('outstanding.singkronDataOutstanding') }}" method="GET">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalSinkronLabel">Pilih Tanggal Untuk Singkron
                                        Outstanding
                                    </h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label for="tanggal_awal">Tanggal Awal Minggu</label>
                                        <input type="date" class="form-control" id="tanggal_awal"
                                            name="tanggal_awal">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="tanggal_akhir">Tanggal Akhir Minggu</label>
                                        <input type="date" class="form-control" id="tanggal_akhir"
                                            name="tanggal_akhir" readonly>
                                    </div>

                                    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
                                    <script>
                                        const tanggalAwal = document.getElementById('tanggal_awal');
                                        const tanggalAkhir = document.getElementById('tanggal_akhir');

                                        tanggalAwal.addEventListener('change', function() {
                                            if (this.value) {
                                                let startDate = moment(this.value, 'YYYY-MM-DD');
                                                let endDate = startDate.clone().endOf('week');
                                                tanggalAkhir.value = endDate.format('YYYY-MM-DD');
                                            } else {
                                                tanggalAkhir.value = '';
                                            }
                                        });
                                    </script>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="btn btn-primary">Sinkron</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="card-title my-1">{{ __('Outstanding') }}</h3>
                            <div class="d-flex align-items-center">
                                <label for="filterTahunHutang" class="me-2 mb-0 text-nowrap fw-bold">Filter Tahun:</label>
                                <select id="filterTahunHutang" class="form-control form-control-sm w-auto">
                                    @for ($i = date('Y'); $i >= 2020; $i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <table class="table table-striped" id="outstandinghutangTable">
                            <thead>
                                <tr>
                                    <th scope="col" rowspan="2">Perusahaan</th>
                                    <th scope="col" rowspan="2">Materi</th>
                                    <th scope="col" rowspan="2">Periode Pelatihan</th>
                                    <th scope="col" rowspan="2">Net Sales</th>
                                    <th scope="col" rowspan="2">PIC</th>
                                    <th scope="col" rowspan="2">Sales</th>
                                    <th scope="col" rowspan="2">Tenggat Waktu</th>
                                    <th scope="col" rowspan="2">Status Pembayaran</th>
                                    <th scope="col" colspan="8" class="text-center">Tracking</th>
                                    <th scope="col" rowspan="2" style="width: 10%">No Resi</th>
                                    <th scope="col" rowspan="2" style="width: 10%">Keterangan PIC</th>
                                    <th scope="col" rowspan="2">Aksi</th>
                                </tr>
                                <tr>
                                    <th scope="col" style="width: 10%">Invoice</th>
                                    <th scope="col" style="width: 10%">Faktur Pajak</th>
                                    <th scope="col" style="width: 10%">Dokumen Tambahan</th>
                                    <th scope="col" style="width: 10%">Konfirmasi Pengiriman RPX</th>
                                    <th scope="col" style="width: 10%">Konfirmasi No Resi</th>
                                    <th scope="col" style="width: 10%">Status Pengiriman</th>
                                    <th scope="col" style="width: 10%">Konfirmasi PIC</th>
                                    <th scope="col" style="width: 10%">Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" style="text-align:right">Total:</th>
                                    <th colspan="2" id="totalNetSalesHutang"></th>
                                    <th colspan="14"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="card-title my-1">{{ __('Outstanding PA') }}</h3>
                            <div class="d-flex align-items-center">
                                <label for="filterBulanPA" class="me-2 mb-0 text-nowrap fw-bold">Filter Bulan:</label>
                                <input type="month" id="filterBulanPA" class="form-control form-control-sm w-auto"
                                    value="{{ date('Y-m') }}">
                            </div>
                        </div>
                        <table class="table table-striped" id="outstandingPaTable">
                            <thead>
                                <tr>
                                    <th scope="col">Perusahaan</th>
                                    <th scope="col">Materi</th>
                                    <th scope="col">Periode Pelatihan</th>
                                    <th scope="col">Net Sales</th>
                                    <th scope="col">PIC</th>
                                    <th scope="col">Sales</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" style="text-align:right">Total:</th>
                                    <th colspan="4" id="totalNetSalesPA"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="card-title my-1">{{ __('Lunas') }}</h3>
                            <div class="d-flex align-items-center">
                                <label for="filterTahunLunas" class="me-2 mb-0 text-nowrap fw-bold">Filter Tahun:</label>
                                <select id="filterTahunLunas" class="form-control form-control-sm w-auto">
                                    @for ($i = date('Y'); $i >= 2020; $i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <table class="table table-striped" id="outstandinglunasTable">
                            <thead>
                                <tr>
                                    <th scope="col">Perusahaan</th>
                                    <th scope="col">Materi</th>
                                    <th scope="col">Periode Pelatihan</th>
                                    <th scope="col">Net Sales</th>
                                    <th scope="col">PIC</th>
                                    <th scope="col">Sales</th>
                                    <th scope="col">Tenggat Waktu</th>
                                    <th scope="col">Status Pembayaran</th>
                                    <th scope="col">Jumlah Pembayaran</th>
                                    <th scope="col">Tanggal Bayar</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tracking</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" style="text-align:right">Total:</th>
                                    <th colspan="2" id="totalNetSalesLunas"></th>
                                    <th colspan="3" style="text-align:right">Total Minggu ini:</th>
                                    <th colspan="4" id="totalNetSalesThisWeek"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .container {
            --input-focus: #2d8cf0;
            --input-out-of-focus: #ccc;
            --bg-color: #fff;
            --bg-color-alt: #666;
            --main-color: #323232;
            position: relative;
            cursor: context-menu;
        }

        .container input {
            position: absolute;
            opacity: 0;
        }

        .checkmark {
            width: 30px;
            height: 30px;
            position: relative;
            top: 0;
            left: 0;
            border: 2px solid var(--main-color);
            border-radius: 5px;
            box-shadow: 4px 4px var(--main-color);
            background-color: var(--input-out-of-focus);
            transition: all 0.3s;
        }

        .container input:checked~.checkmark {
            background-color: var(--input-focus);
        }

        .checkmark:after {
            content: "";
            width: 7px;
            height: 15px;
            position: absolute;
            top: 2px;
            left: 8px;
            display: none;
            border: solid var(--bg-color);
            border-width: 0 2.5px 2.5px 0;
            transform: rotate(45deg);
        }

        .container input:checked~.checkmark:after {
            display: block;
        }
    </style>
    @push('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
        <script>
            function formatRupiah(angka, prefix) {
                var number_string = angka.toString().replace(/[^0-9.,]/g, ''),
                    split = number_string.split('.'),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix === undefined ? rupiah : (rupiah ? prefix + rupiah : '');
            }

            $(document).ready(function() {
                var userRole = '{{ auth()->user()->jabatan }}';
                console.log(userRole);

                var tableLunas = $('#outstandinglunasTable').DataTable({
                    "ajax": {
                        "url": "{{ route('getOutstandingLunas') }}",
                        "type": "GET",
                        "data": function(d) {
                            d.tahun = $('#filterTahunLunas').val();
                        },
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
                            "data": "rkm.perusahaan.nama_perusahaan",
                            "render": function(data) {
                                return data ? data : '-';
                            }
                        },
                        {
                            "data": "rkm.materi.nama_materi",
                            "render": function(data) {
                                return data ? data : '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                moment.locale('id');
                                if (data && data.rkm && data.rkm.tanggal_awal && data.rkm
                                    .tanggal_akhir) {
                                    var tanggalAwal = moment(data.rkm.tanggal_awal).format('LL');
                                    var tanggalAkhir = moment(data.rkm.tanggal_akhir).format('LL');
                                    return tanggalAwal + " s/d " + tanggalAkhir;
                                } else {
                                    return "-";
                                }
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                return formatRupiah(data.net_sales, 'Rp. ');
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                if (data.pic) {
                                    return data.pic;
                                } else {
                                    return "-";
                                }
                            }
                        },
                        {
                            "data": "sales_key"
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                moment.locale('id');
                                return moment(data.due_date).format('LL');
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                if (data.status_pembayaran == '1') {
                                    return "Sudah";
                                } else {
                                    return "Belum";
                                }
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                if (data.jumlah_pembayaran) {
                                    return formatRupiah(data.jumlah_pembayaran, 'Rp. ');
                                } else {
                                    return "-";
                                }
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                moment.locale('id');
                                return moment(data.tanggal_bayar).format('LL');
                            }
                        },
                        {
                            "data": null,
                            "visible": false
                        },
                        {
                            "data": null,
                            "visible": false
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                var actions = "";
                                actions += '@if (auth()->user()->can('Edit Outstanding') || auth()->user()->can('Delete Outstanding '))';
                                actions += '<div class="dropdown">';
                                actions +=
                                    '<button class="btn dropdown-toggle text-white" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions +=
                                    '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += `<a class="dropdown-item" href="/download/dokumen/${row.id}" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg> Download Dokumen </a>`;
                                actions += '@can('Edit Outstanding')';
                                actions +=
                                    '<a class="dropdown-item" href="{{ url('/outstanding') }}/' + row
                                    .id +
                                    '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                                actions += '@endcan';
                                actions += '@can('Delete Outstanding')';
                                actions +=
                                    '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/outstanding') }}/' +
                                    row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions +=
                                    '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '@endcan';
                                actions += '</div>';
                                actions += '</div>';
                                actions +=
                                    '@else';
                                actions += '<div class="dropdown">';
                                actions +=
                                    '<button class="btn dropdown-toggle disabled text-white" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '</div>';
                                actions += '@endif';
                                return actions;
                            }
                        }
                    ],
                    "order": [
                        [8, 'desc']
                    ],
                    "columnDefs": [{
                        "targets": [8],
                        "type": "date"
                    }],
                    "footerCallback": function(row, data, start, end, display) {
                        var api = this.api();
                        var total = 0;
                        var totalThisWeek = 0;

                        api.column(3, {
                            page: 'current'
                        }).data().each(function(value, index) {
                            var numericValue = parseFloat(value.net_sales) || 0;
                            total += numericValue;

                            if (value.status_pembayaran == '1' && value.tanggal_bayar) {
                                var bayarDate = moment(value.tanggal_bayar);
                                var oneWeekAgo = moment().subtract(7, 'days');
                                if (bayarDate.isAfter(oneWeekAgo)) {
                                    totalThisWeek += numericValue;
                                }
                            }
                        });

                        $('#totalNetSalesLunas').html(formatRupiah(total, 'Rp. '));
                        $('#totalNetSalesThisWeek').html(formatRupiah(totalThisWeek, 'Rp. '));
                    },
                    "createdRow": function(row, data, dataIndex) {
                        var status_pembayaran = data.status_pembayaran;
                        var tanggal_bayar = data.tanggal_bayar;

                        if (status_pembayaran == '1') {
                            if (tanggal_bayar) {
                                var oneWeekAgo = moment().subtract(7, 'days');
                                var bayarDate = moment(tanggal_bayar);

                                if (bayarDate.isAfter(oneWeekAgo)) {
                                    $(row).css('background-color', '#FAB12F');
                                } else {
                                    $(row).css('background-color', '#000B58');
                                    $(row).css('color', 'white');
                                }
                            } else {
                                $(row).css('background-color', '#000B58');
                                $(row).css('color', 'white');
                            }
                        }
                    }
                });

                var tableHutang = $('#outstandinghutangTable').DataTable({
                    "ajax": {
                        "url": "{{ route('getOutstandingHutang') }}",
                        "type": "GET",
                        "data": function(d) {
                            d.tahun = $('#filterTahunHutang').val();
                        },
                        "beforeSend": function() {
                            $('#loadingModal').modal('show');
                            $('#loadingModal').removeAttr('inert');
                        },
                        "complete": function() {
                            setTimeout(() => {
                                $('#loadingModal').modal('hide');
                                $('#loadingModal').attr('inert', true);
                            }, 1000);
                        }
                    },
                    "columns": [{
                            "data": "rkm.perusahaan.nama_perusahaan",
                            "render": function(data) {
                                return data ? data : '-';
                            }
                        },
                        {
                            "data": "rkm.materi.nama_materi",
                            "render": function(data) {
                                return data ? data : '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                moment.locale('id');
                                if (data && data.rkm && data.rkm.tanggal_awal && data.rkm
                                    .tanggal_akhir) {
                                    return moment(data.rkm.tanggal_awal).format('LL') + " s/d " +
                                        moment(data.rkm.tanggal_akhir).format('LL');
                                }
                                return "-";
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                if (data.net_sales) {
                                    return formatRupiah(data.net_sales, 'Rp. ');
                                } else {
                                    return "-";
                                }
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                if (data.pic) {
                                    return data.pic;
                                } else {
                                    return "-";
                                }
                            }
                        },
                        {
                            "data": "sales_key"
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                return moment(data.due_date).format('LL');
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                return data.status_pembayaran == '0' ? "Belum" : "Sudah";
                            }
                        },
                        ...Array.from({
                            length: 8
                        }, (_, i) => ({
                            "data": `tracking_outstanding.${['invoice', 'faktur_pajak', 'dokumen_tambahan', 'konfir_cs', 'tracking_dokumen', 'no_resi', 'konfir_pic', 'pembayaran'][i]}`,
                            "render": function(data) {
                                if (data === '1') {
                                    return '<label class="container"><input disabled checked="checked" type="checkbox"><div class="checkmark"></div></label>';
                                } else if (data === null || data === '-') {
                                    return '<label class="container"><input disabled type="checkbox"><div class="checkmark"></div></label>';
                                } else {
                                    return '<label class="container"><input disabled type="checkbox"><div class="checkmark"></div></label>';
                                }
                            }
                        })),
                        {
                            "data": null,
                            "render": function(data) {
                                if (data == null || data.tracking_outstanding == null || data
                                    .tracking_outstanding.status_resi == null) {
                                    return "-";
                                } else {
                                    return data.tracking_outstanding.status_resi;
                                }
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                if (data == null || data.tracking_outstanding == null || data
                                    .tracking_outstanding.status_pic == null) {
                                    return "-";
                                } else {
                                    return data.tracking_outstanding.status_pic;
                                }
                            }
                        },
                        {
                            "data": null,
                            "render": function(data, type, row) {
                                var actions = "";
                                actions += '@if (auth()->user()->can('Edit Outstanding') || auth()->user()->can('Delete Outstanding '))';
                                actions += '<div class="dropdown">';
                                actions +=
                                    '<button class="btn dropdown-toggle text-white" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions +=
                                    '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '@can('Edit Outstanding')';
                                actions +=
                                    '<a class="dropdown-item" href="{{ url('/outstanding') }}/' + row
                                    .id +
                                    '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                                actions += '@endcan';
                                actions += '@can('Delete Outstanding')';
                                actions +=
                                    '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/outstanding') }}/' +
                                    row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions +=
                                    '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '@endcan';
                                actions += '</div>';
                                actions += '</div>';
                                actions +=
                                    '@else';
                                actions += '<div class="dropdown">';
                                actions +=
                                    '<button class="btn dropdown-toggle disabled text-white" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '</div>';
                                actions += '@endif';
                                return actions;
                            }
                        }
                    ],
                    "order": [
                        [6, 'desc']
                    ],
                    "columnDefs": [{
                        "targets": [6],
                        "type": "date"
                    }],
                    "footerCallback": function(row, data, start, end, display) {
                        let total = 0;

                        data.forEach(item => {
                            total += parseFloat(item.net_sales) || 0;
                        });

                        $('#totalNetSalesHutang').html(formatRupiah(total, 'Rp. '));
                    },
                    "createdRow": function(row, data) {
                        if (!data.due_date) return;

                        let today = moment().startOf('day');
                        let dueDate = moment(data.due_date).startOf('day');

                        let overDueDate = today.diff(dueDate, 'days');

                        $(row).removeClass('row-white row-red row-ligth-blue');

                        if (overDueDate > 180) {
                            $(row).css('background-color', '#872819');
                            $(row).css('color', 'white');
                        } else if (overDueDate > 30) {
                            $(row).css('background-color', '#07b0ff');
                            $(row).css('color', 'white');
                        } else {
                            $(row).css('color', 'black');
                        }
                    }
                });

                var tablePA = $('#outstandingPaTable').DataTable({
                    "ajax": {
                        "url": "{{ route('getOutstandingPA') }}",
                        "type": "GET",
                        "data": function(d) {
                            d.bulan = $('#filterBulanPA').val();
                        },
                        "beforeSend": function() {
                            $('#loadingModal').modal('show');
                            $('#loadingModal').removeAttr('inert');
                        },
                        "complete": function() {
                            setTimeout(() => {
                                $('#loadingModal').modal('hide');
                                $('#loadingModal').attr('inert', true);
                            }, 1000);
                        }
                    },
                    "columns": [{
                            "data": "perusahaan.nama_perusahaan",
                            "render": function(data) {
                                return data ? data : '-';
                            }
                        },
                        {
                            "data": "materi.nama_materi",
                            "render": function(data) {
                                return data ? data : '-';
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                moment.locale('id');
                                if (data.tanggal_awal && data.tanggal_akhir) {
                                    var tglAwal = moment(data.tanggal_awal).format('LL');
                                    var tglAkhir = moment(data.tanggal_akhir).format('LL');
                                    return tglAwal + " s/d " + tglAkhir;
                                }
                                return "-";
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                if (data.outstanding && data.outstanding.net_sales) {
                                    return formatRupiah(data.outstanding.net_sales, 'Rp. ');
                                }
                                return "-";
                            }
                        },
                        {
                            "data": null,
                            "render": function(data) {
                                if (data.outstanding && data.outstanding.pic) {
                                    return data.outstanding.pic;
                                }
                                return "-";
                            }
                        },
                        {
                            "data": "sales_key"
                        },
                        {
                            "data": "id",
                            "render": function(data, type, row) {
                                return '<a href="/outstanding/' + data +
                                    '/detail" class="btn btn-sm btn-info text-white" onclick="console.log(' +
                                    data + ')">Detail</a>';
                            }
                        }
                    ],
                    "createdRow": function(row) {
                        $(row).css('background-color', '#FAB12F');
                        $(row).css('color', 'white');
                    },
                    "footerCallback": function(row, data, start, end, display) {
                        var total = 0;
                        data.forEach(function(item) {
                            if (item.outstanding && item.outstanding.net_sales) {
                                var numericValue = parseFloat(item.outstanding.net_sales) || 0;
                                total += numericValue;
                            }
                        });
                        $('#totalNetSalesPA').html(formatRupiah(total, 'Rp. '));
                    }
                });

                $('#filterTahunHutang').change(function() {
                    tableHutang.ajax.reload();
                });

                $('#filterTahunLunas').change(function() {
                    tableLunas.ajax.reload();
                });

                $('#filterBulanPA').change(function() {
                    tablePA.ajax.reload();
                });

            });
        </script>
    @endpush
@endsection
