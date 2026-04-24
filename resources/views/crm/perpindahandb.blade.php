@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container-fluid">

        <div class="modal fade" id="loadingModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="cube">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="transferModal" tabindex="-1">
            <div class="modal-dialog">
                <form id="formTransfer" action="{{ route('perpindahan-db.transfer') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Transfer Database</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="perusahaan_id" id="transferPerusahaanId">

                            <div class="mb-3">
                                <label>Perusahaan</label>
                                <input type="text" id="transferNamaPerusahaan" class="form-control" readonly>
                            </div>

                            <div class="mb-3">
                                <label>Sales Saat Ini</label>
                                <input type="text" id="transferSalesLama" class="form-control" readonly>
                            </div>

                            <div class="mb-3">
                                <label>Sales Tujuan</label>
                                <select name="sales_baru" id="selectSalesBaru" class="form-select" required></select>
                            </div>

                            <div class="mb-3">
                                <label>Alasan</label>
                                <textarea name="alasan" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Transfer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="historyModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Riwayat Transfer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6 id="historyNamaPerusahaan"></h6>
                        <div id="historyContent"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center m-1">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-primary">Daftar Perusahaan</h5>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari..."
                        style="width:250px;">
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="perusahaanTable" class="table table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Sales</th>
                                    <th>Status</th>
                                    <th>History</th>
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

    <style>
        .table-primary th {
            background-color: #e3e9ff !important;
            color: #000 !important;
        }

        .modal-content {
            border-radius: 0px;
            box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        let table;
        let salesList = [];

        $(document).ready(function () {

            table = $('#perusahaanTable').DataTable({
                processing: true,
                ajax: {
                    url: "{{ route('perpindahan-db.data') }}",
                    type: "GET",
                    dataSrc: "data"
                },
                searching: false,
                columns: [
                    { data: null, render: (d, t, r, m) => m.row + 1 },
                    { data: 'nama_perusahaan', defaultContent: '-' },
                    { data: 'kategori_perusahaan', render: d => d ? `<span class="badge bg-info">${d}</span>` : '-' },
                    { data: 'lokasi', defaultContent: '-' },
                    { data: 'sales_key', render: d => d ? `<span class="badge bg-primary">${d}</span>` : `<span class="badge bg-secondary">-</span>` },
                    { data: 'status', render: d => `<span class="badge bg-success">${d ?? '-'}</span>` },
                    {
                        data: 'history_sales',
                        render: (d, t, r) => {
                            try {
                                let h = d ? JSON.parse(d) : [];
                                return h.length ? `<button class="btn btn-sm btn-outline-primary" onclick="showHistory(${r.id})">${h.length}x</button>` : '-';
                            } catch { return '-' }
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: (d, t, r) => `
                            <button class="btn btn-sm btn-warning"
                                onclick="openTransferModal(${r.id},'${r.nama_perusahaan}','${r.sales_key ?? ''}')">
                                Transfer
                            </button>
                        `
                    }
                ],
                order: [[0, 'asc']],
                pageLength: 10
            });

            $('#searchInput').keyup(function () {
                table.search(this.value).draw();
            });

            loadSales();
        });

        function loadSales() {
            $.get("{{ route('perpindahan-db.sales') }}", function (res) {

                let data = res.sales || [];

                let opt = '<option value="">-- Pilih Sales --</option>';

                data.forEach(s => {
                    if (s.kode_karyawan) {
                        opt += `<option value="${s.kode_karyawan}">${s.nama_lengkap}</option>`;
                    }
                });
                $('#selectSalesBaru').html(opt);
            });
        }

        function openTransferModal(id, nama, sales) {
            $('#transferPerusahaanId').val(id);
            $('#transferNamaPerusahaan').val(nama);
            $('#transferSalesLama').val(sales);
            new bootstrap.Modal('#transferModal').show();
        }

        function showHistory(id) {
            $.get("/crm/perpindahandb/history/" + id, res => {
                $('#historyNamaPerusahaan').text(res.perusahaan);
                let html = '';
                (res.history || []).forEach(h => {
                    html += `<div>${h.tanggal} : ${h.dari} → ${h.ke}</div>`;
                });
                $('#historyContent').html(html);
                new bootstrap.Modal('#historyModal').show();
            });
        }

        $('#formTransfer').submit(function (e) {
            e.preventDefault();
            $.post($(this).attr('action'), $(this).serialize(), () => {
                $('#transferModal').modal('hide');
                table.ajax.reload();
            });
        });
    </script>
@endsection