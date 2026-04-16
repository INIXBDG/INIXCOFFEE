@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div> --}}
    <div class="modal fade" id="editJurnalModal" tabindex="-1" aria-labelledby="editJurnalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editJurnalModalLabel">Edit Jurnal Akuntansi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editJurnalForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id" name="id">
                        
                        <div class="mb-3">
                            <label for="edit_tanggal_transaksi" class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control" id="edit_tanggal_transaksi" name="tanggal_transaksi" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="no_akun" class="form-label">No Akun</label>
                            <input type="text" class="form-control" id="no_akun" name="no_akun">
                        </div>
                        
                        <div id="form-pengajuan-group" style="display: none;">
                            <div class="mb-3">
                                <label for="edit_kredit_pengajuan" class="form-label">Kredit (Rp)</label>
                                <input type="number" class="form-control input-pengajuan" id="edit_kredit_pengajuan" name="kredit" min="0" step="0.01">
                            </div>
                        </div>

                        <div id="form-pettycash-group" style="display: none;">
                            <div class="mb-3">
                                <label for="edit_tipe_transaksi" class="form-label">Tipe Transaksi</label>
                                <select class="form-control input-pettycash" id="edit_tipe_transaksi" name="tipe_transaksi">
                                    <option value="" disabled selected>-- Pilih Tipe --</option>
                                    <option value="debit">Pemasukan (Debit)</option>
                                    <option value="kredit">Pengeluaran (Kredit)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_nominal_pettycash" class="form-label">Nominal (Rp)</label>
                                <input type="number" class="form-control input-pettycash" id="edit_nominal_pettycash" name="nominal" min="0" step="0.01">
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-update-jurnal">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="tambahPettyCashModal" tabindex="-1" aria-labelledby="tambahPettyCashModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahPettyCashModalLabel">Tambah Data Kas Kecil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="tambahPettyCashForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="pettycash_tanggal" class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control" id="pettycash_tanggal" name="tanggal_transaksi" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="pettycash_keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="pettycash_keterangan" name="keterangan" rows="3" placeholder="Contoh: Pembelian galon air minum" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="no_akun" class="form-label">No Akun</label>
                            <input type="text" class="form-control" id="no_akun" name="no_akun">
                        </div>
                        
                        <div class="mb-3">
                            <label for="pettycash_tipe" class="form-label">Tipe Transaksi</label>
                            <select class="form-control" id="pettycash_tipe" name="tipe_transaksi" required>
                                <option value="" disabled selected>-- Pilih Tipe --</option>
                                <option value="debit">Pemasukan (Debit)</option>
                                <option value="kredit">Pengeluaran (Kredit)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pettycash_nominal" class="form-label">Nominal (Rp)</label>
                            <input type="number" class="form-control" id="pettycash_nominal" name="nominal" min="1" step="0.01" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btn-simpan-pettycash">Simpan Kas Kecil</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel">Import Data Jurnal (Excel)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importExcelForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file_excel" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                            <input class="form-control" type="file" id="file_excel" name="file" accept=".xlsx, .xls, .csv" required>
                            <div class="form-text text-muted mt-2">
                                <b>Format Kolom Wajib (Kiri ke Kanan):</b><br>
                                1. No (Kosongkan untuk Auto-Generate)<br>
                                2. Tanggal Transaksi<br>
                                3. Keterangan<br>
                                4. Cat. (No Akun)<br>
                                5. Debit (Rp)<br>
                                6. Kredit (Rp)<br>
                                <i>*Baris pertama pada file akan diabaikan (sebagai Header).</i>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btn-submit-import">Mulai Import</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end mb-3 ">
                <button type="button" class="btn click-primary" id="btn-tambah-pettycash">
                    + Tambah Kas Kecil
                </button>
                <button type="button" class="btn btn-success ms-2" id="btn-import-excel">
                    Import Excel
                </button>
            </div>
            <div class="card m-4">
                <div class="card-body">
                    <h3 class="card-title text-center my-1">{{ __('Jurnal Akuntansi') }}</h3>
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" id="btn-filter">Filter Data</button>
                            <button type="button" class="btn btn-secondary ms-2" id="btn-reset">Reset</button>
                        </div>
                    </div>
                    <hr>

                    <div class="table-responsive">
                        <table class="table table-striped" id="jurnaltable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Keterangan</th>
                                        <th scope="col">Cat.</th>
                                        <th scope="col">Debit (Rp)</th>
                                        <th scope="col">Kredit (Rp)</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" style="text-align:right">Total:</th>
                                        <th id="total-debit">0</th>
                                        <th id="total-kredit">0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                    </div>
                </div>
            </div>
            <div class="card m-4">
                <div class="card-body">
                    <h3 class="card-title text-center my-1 text-warning">{{ __('Pengajuan Barang Belum Dijurnal') }}</h3>
                    <p class="text-center text-muted">Daftar pengajuan dengan status Selesai yang belum tercatat pada Jurnal Akuntansi.</p>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-striped" id="belumjurnaltable" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- <th scope="col">ID Pengajuan</th> --}}
                                    <th scope="col">Tanggal Pengajuan</th>
                                    <th scope="col">Nama Karyawan</th>
                                    <th scope="col">Tipe</th>
                                    <th scope="col">Total (Rp)</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
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
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function(){
        // Inisialisasi DataTables
        var table = $('#jurnaltable').DataTable({
            "ajax": {
                "url": "{{ route('getJurnalAkuntansi') }}",
                "type": "GET",
                "data": function (d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
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
                    "data": "nomor_kk",
                    "render": function (data, type, row, meta) {
                        return data ? data : '-'; // Menampilkan '-' jika data lama belum ada nomor_kk nya
                    }
                },
                {
                    "data": "tanggal_transaksi",
                    "render": function(data, type, row) {
                        var date = new Date(data);
                        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                    }
                },
                {"data": "keterangan"},
                {"data": "no_akun"},
                {
                    "data": "debit",
                    "render": function(data, type, row) {
                        return formatRupiah(data);
                    }
                },
                {
                    "data": "kredit",
                    "render": function(data, type, row) {
                        return formatRupiah(data);
                    }
                },

                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-sm btn-primary btn-edit-jurnal" data-id="' + row.id + '">Edit</button>';
                    }
                }
            ],
            order: [[1, 'desc']],
            columnDefs: [{ targets: [1], type: "date" }],
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api();

                // Fungsi untuk memastikan data numerik valid dan mengabaikan nilai desimal (.00)
                var intVal = function (i) {
                    var val = typeof i === 'string' ? parseFloat(i) : typeof i === 'number' ? i : 0;
                    return Math.round(val);
                };

                // Kalkulasi total Debit dari seluruh data yang difilter
                var debitTotal = api
                    .column(4, { search: 'applied' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Kalkulasi total Kredit dari seluruh data yang difilter
                var kreditTotal = api
                    .column(5, { search: 'applied' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Menampilkan hasil kalkulasi pada elemen footer dengan fungsi formatRupiah
                $(api.column(4).footer()).html(formatRupiah(debitTotal));
                $(api.column(5).footer()).html(formatRupiah(kreditTotal));
            }
        });

        // Inisialisasi DataTables untuk Pengajuan Belum Dijurnal
        var tableBelumJurnal = $('#belumjurnaltable').DataTable({
            "ajax": {
                "url": "{{ route('jurnalakuntansi.belumJurnal') }}",
                "type": "GET"
            },
            "columns": [
                // { "data": "id" },
                {
                    "data": "tanggal",
                    "render": function(data, type, row) {
                        var date = new Date(data);
                        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                    }
                },
                { "data": "nama_karyawan" },
                { "data": "tipe" },
                {
                    "data": "total",
                    "render": function(data, type, row) {
                        return new Intl.NumberFormat('id-ID').format(data);
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-sm btn-success btn-create-jurnal" data-id="' + row.id + '">Tambahkan ke Jurnal</button>';
                    }
                }
            ],
            order: [[0, 'desc']],
        columnDefs: [{ targets: [0], type: "date" }]
        });

        // Event listener untuk tombol Create Jurnal
        $('#belumjurnaltable tbody').on('click', '.btn-create-jurnal', function() {
            var idPengajuan = $(this).data('id');
            var url = "{{ url('/jurnalakuntansi/store-manual') }}/" + idPengajuan;

            if (confirm('Apakah Anda yakin ingin membuat jurnal akuntansi untuk ID Pengajuan: ' + idPengajuan + '?')) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('#loadingModal').modal('show');
                        $('#loadingModal').removeAttr('inert');
                    },
                    success: function(response) {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').attr('inert', true);
                        if (response.success) {
                            alert(response.message);
                            // Refresh kedua tabel
                            tableBelumJurnal.ajax.reload(null, false);
                            table.ajax.reload(null, false); // Asumsi 'table' adalah variabel DataTables jurnal utama
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').attr('inert', true);
                        alert('Terjadi kesalahan sistem saat membuat jurnal.');
                    }
                });
            }
        });

        // Event listener untuk tombol Edit Jurnal
        $('#jurnaltable tbody').on('click', '.btn-edit-jurnal', function() {
            var idJurnal = $(this).data('id');
            var urlEdit = "{{ url('/jurnalakuntansi') }}/" + idJurnal + "/edit";

            $.ajax({
                url: urlEdit,
                type: 'GET',
                beforeSend: function() {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').removeAttr('inert');
                },
                success: function(response) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    
                    if (response.success) {
                        $('#edit_id').val(response.data.id);
                        
                        var dateOnly = response.data.tanggal_transaksi.split(' ')[0];
                        $('#edit_tanggal_transaksi').val(dateOnly);
                        $('#edit_keterangan').val(response.data.keterangan);
                        
                        // Logika pemisahan form berdasarkan parameter is_petty_cash
                        if (response.is_petty_cash) {
                            $('#form-pengajuan-group').hide();
                            $('.input-pengajuan').removeAttr('required').attr('disabled', true);
                            
                            $('#form-pettycash-group').show();
                            $('.input-pettycash').attr('required', true).removeAttr('disabled');
                            
                            var tipe = response.data.debit > 0 ? 'debit' : 'kredit';
                            var nominal = response.data.debit > 0 ? response.data.debit : response.data.kredit;
                            
                            $('#edit_tipe_transaksi').val(tipe);
                            $('#edit_nominal_pettycash').val(nominal);
                            $('#editJurnalModalLabel').text('Edit Kas Kecil');
                        } else {
                            $('#form-pettycash-group').hide();
                            $('.input-pettycash').removeAttr('required').attr('disabled', true);
                            
                            $('#form-pengajuan-group').show();
                            $('.input-pengajuan').attr('required', true).removeAttr('disabled');
                            
                            $('#edit_kredit_pengajuan').val(response.data.kredit);
                            $('#editJurnalModalLabel').text('Edit Jurnal Pengajuan Barang');
                        }
                        
                        $('#editJurnalModal').modal('show');
                    } else {
                        alert('Gagal mengambil data jurnal.');
                    }
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    alert('Terjadi kesalahan sistem saat mengambil data.');
                }
            });
        });

        // Event listener untuk tombol Simpan Perubahan
        $('#btn-update-jurnal').click(function() {
            var idJurnal = $('#edit_id').val();
            var urlUpdate = "{{ url('/jurnalakuntansi') }}/" + idJurnal;
            var formData = $('#editJurnalForm').serialize();

            $.ajax({
                url: urlUpdate,
                type: 'POST', // Menggunakan POST dengan @method('PUT') dalam serialize
                data: formData,
                beforeSend: function() {
                    $('#editJurnalModal').modal('hide');
                    $('#loadingModal').modal('show');
                    $('#loadingModal').removeAttr('inert');
                },
                success: function(response) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    
                    if (response.success) {
                        alert(response.message);
                        table.ajax.reload(null, false);
                    } else {
                        alert('Gagal memperbarui data.');
                    }
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    alert('Terjadi kesalahan validasi atau sistem saat menyimpan perubahan.');
                }
            });
        });

        // Event listener untuk tombol filter
        $('#btn-filter').click(function() {
            table.ajax.reload();
        });

        // Event listener untuk tombol reset
        $('#btn-reset').click(function() {
            $('#start_date').val('');
            $('#end_date').val('');
            table.ajax.reload();
        });

        // Event listener untuk membuka Modal Tambah Kas Kecil
        $('#btn-tambah-pettycash').click(function() {
            $('#tambahPettyCashForm')[0].reset(); // Reset form
            $('#tambahPettyCashModal').modal('show');
        });

        // Event listener untuk menyimpan data Kas Kecil
        $('#btn-simpan-pettycash').click(function() {
            var urlPettyCash = "{{ route('jurnalakuntansi.storePettyCash') }}";
            var formData = $('#tambahPettyCashForm').serialize();

            $.ajax({
                url: urlPettyCash,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#tambahPettyCashModal').modal('hide');
                    $('#loadingModal').modal('show');
                    $('#loadingModal').removeAttr('inert');
                },
                success: function(response) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    
                    if (response.success) {
                        alert(response.message);
                        table.ajax.reload(null, false); // Memperbarui tabel jurnal akuntansi
                    } else {
                        alert('Gagal menyimpan data.');
                    }
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    alert('Terjadi kesalahan validasi atau sistem saat menyimpan kas kecil. Pastikan semua form terisi dengan benar.');
                }
            });
        });

        function formatRupiah(angka) {
            // Memastikan angka dikonversi menjadi integer untuk menghindari bug pada nilai desimal
            let parsedAngka = Math.round(parseFloat(angka));
            if (isNaN(parsedAngka)) return '0';

            let number_string = parsedAngka.toString(),
                sisa = number_string.length % 3,
                rupiah = number_string.substr(0, sisa),
                ribuan = number_string.substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return rupiah;
        }

        // Event listener untuk membuka Modal Import Excel
        $('#btn-import-excel').click(function() {
            $('#importExcelForm')[0].reset();
            $('#importExcelModal').modal('show');
        });

        // Event listener untuk eksekusi proses Import via AJAX
        $('#btn-submit-import').click(function() {
            var formElement = document.getElementById('importExcelForm');
            var formData = new FormData(formElement);
            var urlImport = "{{ route('jurnalakuntansi.importExcel') }}";

            // Validasi file kosong
            if ($('#file_excel').val() === '') {
                alert('Pilih file Excel terlebih dahulu.');
                return;
            }

            $.ajax({
                url: urlImport,
                type: 'POST',
                data: formData,
                contentType: false, // Wajib false untuk upload file
                processData: false, // Wajib false untuk upload file
                beforeSend: function() {
                    $('#importExcelModal').modal('hide');
                    $('#loadingModal').modal('show');
                    $('#loadingModal').removeAttr('inert');
                },
                success: function(response) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    
                    if (response.success) {
                        alert(response.message);
                        table.ajax.reload(null, false); // Reload tabel tanpa reset pagination
                    } else {
                        alert('Kegagalan sistem saat impor data.');
                    }
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    
                    var errorMsg = 'Terjadi kesalahan sistem saat mengimpor file.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        });

    });
</script>
@endpush
@endsection