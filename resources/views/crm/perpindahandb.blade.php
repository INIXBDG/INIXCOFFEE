@extends('layouts_crm.app')

@section('crm_contents')
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

        <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form id="formTransfer" action="{{ route('perpindahan-db.transfer') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">🔄 Transfer Database</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="perusahaan_id" id="transferPerusahaanId">
                            <div class="mb-3">
                                <label class="form-label">Perusahaan</label>
                                <input type="text" class="form-control" id="transferNamaPerusahaan" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sales Saat Ini</label>
                                <input type="text" class="form-control" id="transferSalesLama" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">🎯 Sales Tujuan <span class="text-danger">*</span></label>
                                <select name="sales_baru" id="selectSalesBaru" class="form-select" required>
                                    <option value="">-- Pilih Sales --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">📝 Alasan Transfer (Opsional)</label>
                                <textarea name="alasan" class="form-control" rows="3"
                                    placeholder="Contoh: Reorganisasi tim, sales resign, dll."></textarea>
                            </div>
                            <div class="alert alert-warning mb-0">
                                <small>⚠️ Setelah transfer, data perusahaan ini <strong>tidak akan muncul</strong> di
                                    dashboard sales lama, dan hanya dapat diakses oleh sales baru.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">✅ Konfirmasi Transfer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">📜 Riwayat Transfer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6 id="historyNamaPerusahaan" class="fw-bold mb-3"></h6>
                        <div id="historyContent" class="list-group"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center m-1">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-primary">📋 Daftar Perusahaan</h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="searchInput" class="form-control form-control-sm"
                            placeholder="Cari perusahaan / sales / lokasi..." style="width: 250px;">
                        <button class="btn btn-sm btn-primary" onclick="fetchData()">
                            <i class="bx bx-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="perusahaanTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Lokasi</th>
                                    <th scope="col">Sales Saat Ini</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">History Transfer</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
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

        .loader-txt p {
            font-size: 13px;
            color: #666;
        }

        .loader-txt p small {
            font-size: 11.5px;
            color: #999;
        }

        .container-dash {
            overflow: auto;
            display: flex;
            scroll-snap-type: x mandatory;
            width: 90%;
            margin: 0 auto;
            padding: 0 15px;
        }

        .container-dash::-webkit-scrollbar {
            height: 6px;
        }

        .container-dash::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        .card-dash {
            flex: 0 0 220px;
            scroll-snap-align: start;
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(7px);
            -webkit-backdrop-filter: blur(7px);
            border-radius: 10px;
            padding: 2rem;
            margin: 1rem;
            width: 100%;
        }

        .card-dash:hover {
            transform: translateY(-5px);
        }

        .title-dash {
            width: 100%;
            display: inline-block;
            word-break: break-all;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
            margin: auto;
        }

        .badge-sales {
            font-weight: 500;
        }

        .history-item {
            border-left: 3px solid #0d6efd;
        }
    </style>

    @push('js')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script>
            let table;
            let salesList = [];
            let tableIndex = 1;

            $(document).ready(function () {
                initDataTable();
                loadSalesList();
                $('#searchInput').on('keyup', function () {
                    table.search($(this).val()).draw();
                });
            });

            function fetchData() {
                $('#loadingModal').modal('show');
                $('#loadingModal').on('show.bs.modal', function () {
                    $('#loadingModal').removeAttr('inert');
                });
                if ($.fn.DataTable.isDataTable('#perusahaanTable')) {
                    table.ajax.reload(function () {
                        setTimeout(() => {
                            $('#loadingModal').modal('hide');
                            $('#loadingModal').on('hidden.bs.modal', function () {
                                $('#loadingModal').attr('inert', true);
                            });
                        }, 500);
                    }, false);
                }
            }

            function initDataTable() {
                table = $('#perusahaanTable').DataTable({
                    ajax: {
                        url: "{{ route('perpindahan-db.data') }}",
                        type: 'GET',
                        dataSrc: 'data',
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
                        }
                    },
                    columns: [
                        { data: null, render: function (data) { return tableIndex++; } },
                        { data: 'nama_perusahaan' },
                        { data: 'kategori_perusahaan', render: function (data) { return data ? '<span class="badge bg-info">' + data + '</span>' : '-'; } },
                        { data: 'lokasi' },
                        { data: 'sales_key', render: function (data) { return data ? '<span class="badge bg-primary badge-sales">' + data + '</span>' : '<span class="badge bg-secondary">Unassigned</span>'; } },
                        { data: 'status', render: function (data) { var colors = { 'aktif': 'success', 'prospek': 'warning', 'nonaktif': 'secondary' }; return data ? '<span class="badge bg-' + (colors[data] || 'secondary') + '">' + data + '</span>' : '-'; } },
                        { data: 'history_sales', render: function (data) { var history = JSON.parse(data || '[]'); return history.length > 0 ? '<button class="btn btn-sm btn-outline-primary" onclick="showHistory(\'' + history[0].id + '\')">' + history.length + 'x Transfer</button>' : '-'; } },
                        { data: null, orderable: false, render: function (data, type, row) { return '<button class="btn btn-sm btn-warning" onclick="openTransferModal(' + row.id + ', \'' + row.nama_perusahaan + '\', \'' + (row.sales_key || '-') + '\')" ' + (!row.sales_key ? 'disabled title="Tidak ada sales saat ini"' : '') + '>🔄 Transfer</button>'; } }
                    ],
                    order: [[0, 'desc']],
                    pageLength: 10,
                    language: { search: "Cari:", zeroRecords: "Tidak ada data perusahaan", paginate: { next: "›", previous: "‹" } }
                });
            }

            function loadSalesList() {
                $.get("{{ route('perpindahan-db.sales') }}", function (res) {
                    if (res.error) return;
                    salesList = res.sales;
                    populateSalesDropdown();
                });
            }

            function populateSalesDropdown() {
                let options = '<option value="">-- Pilih Sales --</option>';
                salesList.forEach(function (sales) {
                    options += '<option value="' + (sales.kode_karyawan || sales.id) + '">' + sales.name + ' (' + sales.kode_karyawan + ')</option>';
                });
                $('#selectSalesBaru').html(options);
            }

            function openTransferModal(id, nama, salesLama) {
                $('#transferPerusahaanId').val(id);
                $('#transferNamaPerusahaan').val(nama);
                $('#transferSalesLama').val(salesLama);
                $('#selectSalesBaru').val('');
                new bootstrap.Modal('#transferModal').show();
            }

            function showHistory(id) {
                $.get("{{ route('perpindahan-db.history', ':id') }}".replace(':id', id), function (res) {
                    if (res.error) return alert('Akses ditolak');
                    $('#historyNamaPerusahaan').text(res.perusahaan);
                    if (!res.history || res.history.length === 0) {
                        $('#historyContent').html('<p class="text-muted">Belum ada riwayat transfer.</p>');
                    } else {
                        let html = '';
                        res.history.reverse().forEach(function (h) {
                            html += '<div class="list-group-item history-item"><div class="d-flex justify-content-between"><strong>' + h.tanggal + '</strong><small class="text-muted">Oleh: ' + h.oleh + '</small></div><div class="mt-1"><span class="text-danger">📤 ' + h.dari + '</span> → <span class="text-success">📥 ' + h.ke + '</span></div>' + (h.alasan ? '<small class="d-block mt-1 text-muted">📝 ' + h.alasan + '</small>' : '') + '</div>';
                        });
                        $('#historyContent').html(html);
                    }
                    new bootstrap.Modal('#historyModal').show();
                });
            }

            $('#formTransfer').on('submit', function (e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const originalText = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');
                $.post($(this).attr('action'), $(this).serialize(), function (res) {
                    window.location.reload();
                }).fail(function (xhr) {
                    alert('❌ ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                    btn.prop('disabled', false).html(originalText);
                });
            });
        </script>
    @endpush
@endsection