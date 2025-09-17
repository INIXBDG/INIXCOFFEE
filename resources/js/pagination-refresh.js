// Pagination refresh utility functions
function getCurrentPage(tableId) {
    var table = $('#' + tableId).DataTable();
    return table.page.info().page;
}

function refreshTableWithCurrentPage(tableId, refreshFunction) {
    var currentPage = getCurrentPage(tableId);
    refreshFunction(currentPage);
}

// Updated table functions with page parameter
function tableKaryawan(page = 0) {
    var tahun = $('#tahun').val();
    
    $('#barangTable').DataTable({
        autoWidth: false,
        "ajax": {
            url: "{{ route('getPengajuanBarang', ['month' => ':month', 'year' => ':year'] ) }}".replace(':month', 'All').replace(':year', tahun),
            "type": "GET",
            "beforeSend": function () {
                $('#loadingModal').modal('show');
                $('#loadingModal').on('show.bs.modal', function () {
                    $('#loadingModal').removeAttr('inert');
                });
            },
            "complete": function () {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').on('hidden.bs.modal', function () {
                        $('#loadingModal').attr('inert', true);
                    });
                }, 1000);
            }
        },
        "columns": [
            {
                "data": "created_at",
                "visible": false,
                "render": function(data, type, row) {
                    var tanggalAwal = moment(data).format('YYYY-MM-DD');
                    return tanggalAwal;
                }
            },
            {
                "data": "created_at",
                "render": function(data, type, row) {
                    moment.locale('id');
                    var tanggalAwal = moment(data).format('dddd, DD MMMM YYYY');
                    return tanggalAwal;
                }
            },
            {"data": "karyawan.nama_lengkap"},
            {"data": "karyawan.divisi"},
            {"data": "karyawan.jabatan", "visible": false},
            {"data": "tipe"},
            {"data": "tracking.tracking"},
            {
                "data": "detail",
                "render": function (data, type, row) {
                    if (data && Array.isArray(data)) {
                        return data.map(item => item.nama_barang).join('<hr style="margin: 4px 0; border: 1px solid black">');
                    }
                    return '-';
                }
            },
            {
                "data": "detail",
                "render": function (data, type, row) {
                    if (data && Array.isArray(data)) {
                        return data.map(item => {
                            let total = item.harga * item.qty;
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR'
                            }).format(total);
                        }).join('<hr style="margin: 4px 0; border: 1px solid black">');
                    }
                    return '-';
                }
            },
            {
                "data": "detail",
                "render": function (data) {
                    if (data && Array.isArray(data)) {
                        const total = data.reduce((sum, item) => sum + (item.harga * item.qty), 0);
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(total);
                    }
                    return '-';
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    var actions = "";
                    var allowedRoles = ['Office Manager', 'Education Manager', 'SPV Sales', 'GM', 'Koordinator Office', 'Finance & Accounting', 'Koordinator ITSM'];
                    var userRole = '{{ auth()->user()->jabatan}}';
                    var requesterRole = data.karyawan.jabatan;
                    var userKaryawanId = {{ auth()->user()->karyawan_id }};
                    var trackingStatus = data.tracking.tracking;
                    var karyawanId = data.karyawan.id;

                    function addButton(label, url, condition, icon) {
                        if (condition) {
                            return `<a href="${url}" class="dropdown-item"><img src="{{ asset('${icon}') }}" class=""> ${label}</a>`;
                        } else {
                            return `<button type="button" class="dropdown-item disabled"><img src="{{ asset('${icon}') }}" class=""> ${label}</button>`;
                        }
                    }

                    var actions = '<div class="dropdown">';
                    actions += '<button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>';
                    actions += '<div class="dropdown-menu">';

                    if (allowedRoles.includes(userRole)) {
                        if (userRole == 'GM' && ['Sudah Disetujui dan Sedang Ditinjau oleh General Manager', 'Telah Disetujui oleh SPV Sales dan Sedang Ditinjau oleh General Manager', 'Diajukan dan Sedang Ditinjau oleh General Manager'].includes(trackingStatus)) {
                            actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                        } else if (userRole == 'Education Manager' && trackingStatus == 'Diajukan dan Sedang Ditinjau oleh Education Manager') {
                            actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                        } else if (userRole == 'Koordinator ITSM' && trackingStatus == 'Diajukan dan Sedang Ditinjau oleh Koordinator IT Service Management') {
                            actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                        } else if (userRole == 'SPV Sales' && trackingStatus == 'Diajukan dan Sedang Ditinjau oleh SPV Sales') {
                            actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                        } else if (userRole == 'Finance & Accounting' && (trackingStatus.includes('Finance') || trackingStatus.includes('Permintaan') || trackingStatus.includes('proses'))) {
                            actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                        } else {
                            actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                        }
                    }

                    var uploadInvoiceUrl = "{{ url('/pengajuanbarang/uploadinvoice') }}/" + row.id;
                    var uploadInvoiceIcon = 'icon/clipboard-primary.svg';
                    var uploadInvoiceCondition = {{ auth()->user()->karyawan_id }} === karyawanId;

                    actions += addButton('Upload Invoice', uploadInvoiceUrl, uploadInvoiceCondition, uploadInvoiceIcon);
                    
                    var detailUrl = "{{ url('/pengajuanbarang') }}/" + row.id;
                    actions += addButton('Detail', detailUrl, true, 'icon/clipboard-primary.svg');
                    
                    if(!trackingStatus.includes('Finance')){
                        actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/pengajuanbarang') }}/' + row.id + '" method="POST">';
                        actions += '@csrf';
                        actions += '@method('DELETE')';
                        actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                        actions += '</form>';
                    }
                    
                    actions += '</div></div>';
                    return actions;
                }
            }
        ],
        "order": [[0, 'desc']],
        "columnDefs" : [{"targets":[0], "type":"date"}],
        "initComplete": function(settings, json) {
            if (page > 0) {
                this.api().page(page).draw(false);
            }
        }
    });
}

function tableFinance(page = 0) {
    if ($.fn.DataTable.isDataTable('#datasudah')) {
        $('#datasudah').DataTable().clear().destroy();
    }
    if ($.fn.DataTable.isDataTable('#databelum')) {
        $('#databelum').DataTable().clear().destroy();
    }
    
    $('#loadingModal').modal('show');
    var tahun = $('#tahun').val();
    var bulan = $('#bulan').val();
    
    $.ajax({
        url: "{{ route('getPengajuanBarang', ['month' => ':month', 'year' => ':year'] ) }}".replace(':month', bulan).replace(':year',tahun),
        type: "GET",
        success: function(data) {
            $('#loadingModal').modal('hide');
            var dataSelesai = data.data.filter(item => item.tracking.tracking === 'Selesai' || item.tracking.tracking.includes("tolak"));
            var dataBelum = data.data.filter(item => item.tracking.tracking !== 'Selesai' && !item.tracking.tracking.includes("tolak"));

            let totalItemsSelesai = dataSelesai.length;
            let totalHargaSelesai = 0;
            dataSelesai.forEach(item => {
                if (item.detail && Array.isArray(item.detail)) {
                    item.detail.forEach(detail => {
                        totalHargaSelesai += detail.qty * detail.harga;
                    });
                }
            });

            let totalItemsBelum = dataBelum.length;
            let totalHargaBelum = 0;
            dataBelum.forEach(item => {
                if (item.detail && Array.isArray(item.detail)) {
                    item.detail.forEach(detail => {
                        totalHargaBelum += detail.qty * detail.harga;
                    });
                }
            });

            $('#datasudah').DataTable({
                data: dataSelesai,
                columns: [
                    {
                        "data": "created_at",
                        "render": function(data, type, row) {
                            moment.locale('id');
                            var tanggalAwal = moment(data).format('dddd, DD MMMM YYYY');
                            return tanggalAwal;
                        }
                    },
                    {"data": "karyawan.nama_lengkap"},
                    {"data": "karyawan.divisi"},
                    {"data": "karyawan.jabatan", "visible": false},
                    {"data": "tipe"},
                    {"data": "tracking.tracking"},
                    {
                        "data": "detail",
                        "render": function (data, type, row) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => item.nama_barang).join('<hr style="margin: 4px 0; border: 1px solid black">');
                            }
                            return '-';
                        }
                    },
                    {
                        "data": "detail",
                        "render": function (data, type, row) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => {
                                    let total = item.harga * item.qty;
                                    return new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(total);
                                }).join('<hr style="margin: 4px 0; border: 1px solid black">');
                            }
                            return '-';
                        }
                    },
                    {
                        "data": "detail",
                        "render": function (data) {
                            if (data && Array.isArray(data)) {
                                const total = data.reduce((sum, item) => sum + (item.harga * item.qty), 0);
                                return new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(total);
                            }
                            return '-';
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            var actions = "";
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>';
                            actions += '<div class="dropdown-menu">';
                            actions += '<a class="dropdown-item" disabled href="{{ url('/pengajuanbarang') }}/' + row.id + '"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                            actions += '<a class="dropdown-item" disabled href="{{ url('/pengajuanbarang/uploadinvoice') }}/' + row.id + '"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Upload Invoice</a>';
                            actions += '</div></div>';
                            return actions;
                        },
                    },
                ],
                "order": [[0, 'desc']],
                "columnDefs" : [{"targets":[0], "type":"date"}],
                "initComplete": function(settings, json) {
                    if (page > 0) {
                        this.api().page(page).draw(false);
                    }
                },
                "drawCallback": function(settings) {
                    var api = this.api();
                    var formattedHarga = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(totalHargaSelesai);
                    var footerHtml = `
                        <tr>
                            <th colspan="6" style="text-align: left;">
                                Total Pengajuan: ${totalItemsSelesai}
                            </th>
                            <th colspan="4" style="text-align: left;">
                                Total Harga Pengajuan: ${formattedHarga}
                            </th>
                        </tr>
                    `;
                    $('#datasudah tfoot').html(footerHtml);
                }
            });

            $('#databelum').DataTable({
                data: dataBelum,
                columns: [
                    {
                        "data": "created_at",
                        "render": function(data, type, row) {
                            moment.locale('id');
                            var tanggalAwal = moment(data).format('dddd, DD MMMM YYYY');
                            return tanggalAwal;
                        }
                    },
                    {"data": "karyawan.nama_lengkap"},
                    {"data": "karyawan.divisi"},
                    {"data": "karyawan.jabatan", "visible": false},
                    {"data": "tipe"},
                    {"data": "tracking.tracking"},
                    {
                        "data": "detail",
                        "render": function (data, type, row) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => item.nama_barang).join('<hr style="margin: 4px 0; border: 1px solid black">');
                            }
                            return '-';
                        }
                    },
                    {
                        "data": "detail",
                        "render": function (data, type, row) {
                            if (data && Array.isArray(data)) {
                                return data.map(item => {
                                    let total = item.harga * item.qty;
                                    return new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(total);
                                }).join('<hr style="margin: 4px 0; border: 1px solid black">');
                            }
                            return '-';
                        }
                    },
                    {
                        "data": "detail",
                        "render": function (data) {
                            if (data && Array.isArray(data)) {
                                const total = data.reduce((sum, item) => sum + (item.harga * item.qty), 0);
                                return new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR'
                                }).format(total);
                            }
                            return '-';
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            var actions = "";
                            var allowedRoles = ['Office Manager', 'Education Manager', 'SPV Sales', 'GM', 'Koordinator Office', 'Finance & Accounting', 'Koordinator ITSM'];
                            var userRole = '{{ auth()->user()->jabatan}}';
                            var userKaryawanId = {{ auth()->user()->karyawan_id }};
                            var trackingStatus = data.tracking.tracking;
                            var karyawanId = data.karyawan.id;

                            function addButton(label, url, condition, icon) {
                                if (condition) {
                                    return `<a href="${url}" class="dropdown-item"><img src="{{ asset('${icon}') }}" class=""> ${label}</a>`;
                                } else {
                                    return `<button type="button" class="dropdown-item disabled"><img src="{{ asset('${icon}') }}" class=""> ${label}</button>`;
                                }
                            }

                            var actions = '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>';
                            actions += '<div class="dropdown-menu">';

                            if (userRole == 'Finance & Accounting' && (trackingStatus.includes('Finance') || trackingStatus.includes('Permintaan') || trackingStatus.includes('proses') || trackingStatus.includes('Selesai'))) {
                                actions += '<button type="button" class="dropdown-item" onclick="openApproveModal(' + row.id + ', \'Manager\')"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            } else {
                                actions += '<button type="button" class="dropdown-item disabled"><img src="{{ asset('icon/check-circle.svg') }}" class=""> Approve</button>';
                            }

                            var uploadInvoiceUrl = "{{ url('/pengajuanbarang/uploadinvoice') }}/" + row.id;
                            var uploadInvoiceCondition = {{ auth()->user()->karyawan_id }} === karyawanId || userRole == 'Finance & Accounting';
                            actions += addButton('Upload Invoice', uploadInvoiceUrl, uploadInvoiceCondition, 'icon/clipboard-primary.svg');
                            
                            var detailUrl = "{{ url('/pengajuanbarang') }}/" + row.id;
                            actions += addButton('Detail', detailUrl, true, 'icon/clipboard-primary.svg');
                            
                            if(!trackingStatus.includes('Finance')){
                                actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/pengajuanbarang') }}/' + row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                            }
                            
                            actions += '</div></div>';
                            return actions;
                        }
                    }
                ],
                "order": [[0, 'desc']],
                "columnDefs" : [{"targets":[0], "type":"date"}],
                "initComplete": function(settings, json) {
                    if (page > 0) {
                        this.api().page(page).draw(false);
                    }
                },
                "drawCallback": function(settings) {
                    var api = this.api();
                    var formattedHargaBelum = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(totalHargaBelum);
                    var footerHtml = `
                        <tr>
                            <th colspan="6" style="text-align: left;">
                                Total Pengajuan: ${totalItemsBelum}
                            </th>
                            <th colspan="4" style="text-align: left;">
                                Total Harga Pengajuan: ${formattedHargaBelum}
                            </th>
                        </tr>
                    `;
                    $('#databelum tfoot').html(footerHtml);
                }
            });
        }
    });
}

// Updated approval form handler
$('#approveForm').on('submit', function(e) {
    e.preventDefault();
    let form = $(this);
    let actionUrl = form.attr('action');
    
    // Get current page
    var currentPage = 0;
    var tableId = '';
    
    if ($('#barangTable').length) {
        tableId = 'barangTable';
        currentPage = getCurrentPage('barangTable');
    } else if ($('#datasudah').length) {
        tableId = 'datasudah';
        currentPage = getCurrentPage('datasudah');
    } else if ($('#databelum').length) {
        tableId = 'databelum';
        currentPage = getCurrentPage('databelum');
    }

    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: form.serialize(),
        success: function(res) {
            $('#approveModal').modal('hide');
            
            // Refresh with current page
            if (userRole == 'Finance & Accounting') {
                tableFinance(currentPage);
            } else {
                tableKaryawan(currentPage);
            }
            
            // Show success notification
            alert('Pengajuan berhasil disetujui!');
        },
        error: function(err) {
            alert('Gagal menyimpan data.');
        }
    });
});
