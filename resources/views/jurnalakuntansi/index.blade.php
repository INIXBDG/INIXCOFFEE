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
    <div class="modal fade" id="detailNetSalesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Detail Net Sales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailNetSalesContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailPengajuanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Detail Pengajuan Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailPengajuanContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>
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
                            <select class="form-control select2" id="no_akun" name="no_akun" style="width: 75%">
                                <option value="" selected>Pilih No Akun</option>
                                @foreach ( $no_akun as $data )
                                <option value="{{ $data->no }}">{{ $data->no }} - {{ $data->nama_akun }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_debit" class="form-label">Debit (Rp)</label>
                                <input type="number" class="form-control" id="edit_debit" name="debit" min="0" step="0.01" value="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_kredit" class="form-label">Kredit (Rp)</label>
                                <input type="number" class="form-control" id="edit_kredit" name="kredit" min="0" step="0.01" value="0" required>
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
                            <select class="form-control input-pettycash" id="no_akun" name="no_akun">
                                <option value="" selected>Pilih No Akun</option>
                                @foreach ( $no_akun as $data )
                                <option value="{{ $data->no }}">{{ $data->no }} - {{ $data->nama_akun }}</option>
                                @endforeach
                            </select>
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
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Data Jurnal Akuntansi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('jurnalakuntansi.export') }}" method="GET" target="_blank">
                    <div class="modal-body">
                        <input type="hidden" name="format_export" value="preview">
                        
                        <div class="mb-3">
                            <label for="tipe_periode" class="form-label">Tipe Periode</label>
                            <select class="form-select" id="tipe_periode" name="tipe_periode" required>
                                <option value="harian">Per Hari</option>
                                <option value="mingguan">Per Minggu</option>
                                <option value="bulanan" selected>Per Bulan</option>
                                <option value="triwulan">Per Triwulan</option>
                                <option value="tahunan">Per Tahun</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_acuan" class="form-label">Tanggal Acuan</label>
                            <input type="date" class="form-control" id="tanggal_acuan" name="tanggal_acuan" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                            <small class="text-muted">Sistem akan otomatis menghitung rentang awal dan akhir periode berdasarkan tanggal acuan ini.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" onclick="$('#exportModal').modal('hide');">Tampilkan Preview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="masterNoAkunModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Manajemen Master No Akun</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="openFormNoAkun()">+ Tambah Akun</button>
                        <button class="btn btn-success btn-sm" onclick="$('#importNoAkunModal').modal('show')">Import Excel</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableNoAkun" style="width: 100%">
                            <thead class="table-secondary">
                                <tr>
                                    <th>No Akun</th>
                                    <th>Nama Akun</th>
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
    <div class="modal fade" id="formNoAkunModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titleFormNoAkun">Tambah No Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="noAkunForm">
                    @csrf
                    <input type="hidden" name="_method" id="methodNoAkun" value="POST">
                    <input type="hidden" id="id_no_akun">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nomor Akun</label>
                            <input type="text" class="form-control" id="form_no_akun" name="no" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Akun</label>
                            <input type="text" class="form-control" id="form_nama_akun" name="nama_akun" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="importNoAkunModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Master No Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formImportNoAkun" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">File Excel</label>
                            <input type="file" class="form-control" name="file_no_akun" id="file_no_akun" accept=".xlsx, .xls, .csv" required>
                            <small class="text-muted mt-2 d-block">Format: Kolom A (No Akun), Kolom B (Nama Akun). Baris 1 diabaikan sebagai header.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <div class="modal fade" id="otomatisasiJurnalModal" tabindex="-1" aria-labelledby="otomatisasiJurnalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otomatisasiJurnalModalLabel">Otomatisasi Jurnal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="otomatisasiJurnalForm">
                        @csrf
                        <div class="mb-3">
                            <label for="otomatisasi_waktu" class="form-label">Waktu</label>
                            <input type="month" class="form-control" id="otomatisasi_waktu" name="waktu" required>
                        </div>

                        <div class="mb-3">
                            <label for="tipe_otomatisasi" class="form-label">Jurnal</label>
                            <select class="form-control" id="tipe_otomatisasi" name="tipe_otomatisasi" required>
                                <option value="">Pilih Jurnal</option>
                                <option value="pengajuan_barang">Pengajuan Barang</option>
                                <option value="net_sales">Net Sales</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btn-otomatisasi">Otomatisasi</button>
                </div>
            </div>  
        </div>
    </div>
    <div class="modal fade" id="detailJurnalModal" tabindex="-1" aria-labelledby="detailJurnalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailJurnalModalLabel">{{ __('Detail Jurnal Akuntansi') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailJurnalBody">
                    {{-- Konten detail akan disuntikkan oleh JavaScript --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
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
                <button type="button" class="btn btn-secondary ms-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <img src="{{ asset('icon/file-text.svg') }}" width="20px"> Export Laporan
                </button>
                <button type="button" class="btn click-primary ms-2" id="btn-master-no-akun">
                   Master No Akun
                </button>
                <button type="button" class="btn click-primary ms-2" id="btn-otomatisasi-jurnal">
                   Otomatisasi Jurnal
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
                                        <th scope="col">No Pengajuan</th>
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
            <div class="card m-4">
                <div class="card-body">
                    <h3 class="card-title text-center my-1 text-warning">{{ __('Net Sales Belum Dijurnal') }}</h3>
                    <p class="text-center text-muted">Daftar Perhitungan Net Sales dengan status Selesai yang belum tercatat pada Jurnal Akuntansi.</p>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-striped" id="NSbelumjurnaltable" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">Tanggal Pengajuan</th>
                                    <th scope="col">Nama Materi</th>
                                    <th scope="col">Nama Perusahaan</th>
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

    #NSbelumjurnaltable tbody tr {
        cursor: pointer;
    }

    #NSbelumjurnaltable tbody tr:hover {
        background-color: #f5f5f5;
    }

    #belumjurnaltable tbody tr {
        cursor: pointer;
    }

    #belumjurnaltable tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Deklarasi variabel global untuk DataTable No Akun
    var dtNoAkun;

    // FUNGSI GLOBAL TETAP DI LUAR (karena dipanggil via onclick di dalam DataTables render)
    function openFormNoAkun() {
        $('#noAkunForm')[0].reset();
        $('#id_no_akun').val('');
        $('#methodNoAkun').val('POST');
        $('#titleFormNoAkun').text('Tambah No Akun');
        $('#formNoAkunModal').modal('show');
    }

    function editNoAkun(id) {
        $.get("{{ url('/no-akun') }}/" + id + "/edit", function(res) {
            if(res.success) {
                $('#noAkunForm')[0].reset();
                $('#id_no_akun').val(res.data.id);
                $('#form_no_akun').val(res.data.no);
                $('#form_nama_akun').val(res.data.nama_akun);

                $('#methodNoAkun').val('PUT');
                $('#titleFormNoAkun').text('Edit No Akun');
                $('#formNoAkunModal').modal('show');
            }
        });
    }

    function deleteNoAkun(id) {
        if(confirm('Yakin ingin menghapus data ini?')) {
            $.ajax({
                url: "{{ url('/no-akun') }}/" + id,
                type: "POST",
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if(res.success) {
                        if(dtNoAkun) dtNoAkun.ajax.reload(null, false);
                    }
                }
            });
        }
    }
    $(document).ready(function(){
        $('#no_akun').select2({
            placeholder: "Pilih No Akun",
            allowClear: true,
            dropdownParent: $('#editJurnalModal'),
            
            
        });
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
                    "data": "",
                    "render": function (data, type, row, meta) {
                        let info = "";
                        if (row.list_pengajuan && row.list_pengajuan.length > 0) {
                            let ids = row.list_pengajuan.map(p => p.no_kk).join(', ');
                            info += `<span class="badge bg-info text-dark" style="max-width:150px">${ids}</span>`;
                        }
                        return info;           
                    }
                },
                {
                    "data": "tanggal_transaksi",
                    "render": function(data, type, row) {
                        var date = new Date(data);
                        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                    }
                },
                {
                    "data": "keterangan",
                    "render": function(data, type, row) {
                        let info = data;
                        if (row.list_pengajuan && row.list_pengajuan.length > 0) {
                            let ids = row.list_pengajuan.map(p => p.id).join(', ');
                            info += `<br><small class="text-muted">ID Pengajuan: ${ids}</small>`;
                        }
                        return info;
                    }
                },
               {
                    "data": "no_accounting",
                    "render": function(data) {
                        return data
                            ? `${data.no ?? '-'} - ${data.nama_akun ?? '-'}`
                            : '-';
                    }
                },
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
                        var actions = "";
                        
                        actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                        actions += '<div class="dropdown-menu px-2" aria-labelledby="dropdownMenuButton">';
                            actions += '<button type="button" class="btn-edit-jurnal dropdown-item mb-2 rounded-2 bg-primary text-white" data-id="' + row.id + '">Edit</button>';
                            actions += '<a class="dropdown-item bg-danger text-white rounded-2" href="{{ url('/jurnalakuntansi/pdf') }}/' + row.id + '">PDF</a>';
                        actions += '</div>';
                        return actions;
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
        // --- Penanganan Event Klik pada Baris Tabel ---
        // --- Penanganan Event Klik pada Baris Tabel ---
        $('#jurnaltable tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('button, a, .dropdown-menu, .btn-group').length) {
                return;
            }

            var data = table.row(this).data();
            if (!data) return;

            var akunText = data.no_accounting ? (data.no_accounting.no + ' - ' + data.no_accounting.nama_akun) : (data.no_akun || '-');
            var tglTransaksi = new Date(data.tanggal_transaksi).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
            
            // 1. Informasi Utama Jurnal
            var html = '<h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Informasi Utama Transaksi</h6>';
            html += '<table class="table table-bordered table-striped table-sm mb-4">';
            html += `<tr><th width="35%" class="bg-light">Nomor KK</th><td>${data.nomor_kk || '-'}</td></tr>`;
            html += `<tr><th class="bg-light">Tanggal Transaksi</th><td>${tglTransaksi}</td></tr>`;
            html += `<tr><th class="bg-light">Keterangan</th><td>${data.keterangan || '-'}</td></tr>`;
            html += `<tr><th class="bg-light">No. Akun</th><td>${akunText}</td></tr>`;
            html += `<tr><th class="bg-light">Debit</th><td class="text-success fw-bold">${formatRupiah(data.debit)}</td></tr>`;
            html += `<tr><th class="bg-light">Kredit</th><td class="text-danger fw-bold">${formatRupiah(data.kredit)}</td></tr>`;
            html += '</table>';

            // 2. Render Pengajuan Barang (Jika ada array list_pengajuan)
            if (data.list_pengajuan && data.list_pengajuan.length > 0) {
                html += `<h6 class="fw-bold text-success border-bottom pb-2 mt-4 mb-3"><i class="fas fa-box-open me-2"></i>Detail Referensi: Pengajuan Barang (${data.list_pengajuan.length})</h6>`;
                
                data.list_pengajuan.forEach((pengajuan, index) => {
                    // Header pemisah jika pengajuan lebih dari satu
                    if(data.list_pengajuan.length > 1) {
                        html += `<div class="bg-light p-2 mb-2 border-start border-success border-4 fw-bold">Pengajuan #${index + 1} (ID: ${pengajuan.id})</div>`;
                    }
                    
                    html += '<table class="table table-bordered table-sm mb-3">';
                    html += `<tr><th width="35%" class="bg-light">Pemohon</th><td>${pengajuan.karyawan.nama_lengkap ?? '-'}</td></tr>`;
                    html += `<tr><th width="35%" class="bg-light">Tipe</th><td>${pengajuan.tipe || '-'}</td></tr>`;
                    html += `<tr><th class="bg-light">Tgl Pencairan</th><td>${pengajuan.tanggal_pencairan || '-'}</td></tr>`;
                    if(pengajuan.invoice) {
                        html += `<tr><th class="bg-light">Invoice</th><td><a href="/storage/${pengajuan.invoice}" target="_blank" class="btn btn-xs btn-primary py-0">Lihat File</a></td></tr>`;
                    }
                    if(pengajuan.bukti) {
                        html += `<tr><th class="bg-light">Bukti</th><td><a href="/storage/${pengajuan.bukti}" target="_blank" class="btn btn-xs btn-primary py-0">Lihat File</a></td></tr>`;
                    }
                    html += '</table>';

                    // Sub-tabel Detail Barang
                    if (pengajuan.detail && pengajuan.detail.length > 0) {
                        html += '<div class="ms-3 mb-4"><table class="table table-bordered table-sm align-middle text-center" style="font-size:0.85rem;">';
                        html += '<thead class="table-secondary"><tr><th>Nama Barang</th><th>Qty</th><th>Harga</th><th>Total</th></tr></thead><tbody>';
                        pengajuan.detail.forEach(det => {
                            let subtotal = parseFloat(det.qty) * parseFloat(det.harga);
                            html += `<tr>
                                <td class="text-start">${det.nama_barang}</td>
                                <td>${det.qty}</td>
                                <td class="text-end">${formatRupiah(det.harga)}</td>
                                <td class="text-end fw-bold">${formatRupiah(subtotal)}</td>
                            </tr>`;
                        });
                        html += '</tbody></table></div>';
                    }
                });
            }

            // 3. Render Net Sales (Jika ada objek net_sales)
            if (data.net_sales) {
                html += `<h6 class="fw-bold text-info border-bottom pb-2 mt-4 mb-3"><i class="fas fa-chart-line me-2"></i>Detail Referensi: Perhitungan Net Sales</h6>`;
                html += '<table class="table table-bordered table-sm mb-4"><tbody>';
                
                const currencyKeys = ['transportasi', 'akomodasi_peserta', 'akomodasi_tim', 'fresh_money', 'entertaint', 'souvenir', 'cashback', 'sewa_laptop'];
                
                // Loop properti net sales
                for (let key in data.net_sales) {
                    let val = data.net_sales[key];
                    if (!val || key === 'id' || key.includes('id_') || key === 'bukti') continue;

                    let label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    
                    if (currencyKeys.includes(key)) {
                        val = `<span class="fw-bold text-dark">${formatRupiah(val)}</span>`;
                    }
                    
                    html += `<tr><th width="35%" class="bg-light">${label}</th><td>${val}</td></tr>`;
                }
                if (data.net_sales.bukti) {
                    html += `<tr>
                        <th width="35%" class="bg-light">Bukti Transaksi</th>
                        <td><a href="/storage/${data.net_sales.bukti}" target="_blank" class="btn btn-sm btn-info">Lihat File</a></td>
                    </tr>`;
                }
                html += '</tbody></table>';
            }

            // Tampilkan Modal
            $('#detailJurnalBody').html(html);
            $('#detailJurnalModal').modal('show');
        });
        
        $('#jurnaltable tbody').css('cursor', 'pointer');

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

        // Inisialisasi DataTables untuk Net Sales Belum Dijurnal
        var tableBelumJurnalNS = $('#NSbelumjurnaltable').DataTable({
            "ajax": {
                "url": "{{ route('jurnalakuntansi.belumJurnalNetSales') }}",
                "type": "GET"
            },
            "columns": [
                {
                    "data": "tanggal",
                    "render": function(data) {
                        var date = new Date(data);
                        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                    }
                },
                { "data": "nama_materi" },
                { "data": "nama_perusahaan" },
                { "data": "tipe" },
                {
                    "data": "total",
                    "render": function(data) {
                        return formatRupiah(data);
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-sm btn-success btn-create-jurnal-ns" data-id="' + row.id + '">Tambahkan ke Jurnal</button>';
                    }
                }
            ],
            order: [[0, 'desc']],
            columnDefs: [{ targets: [0], type: "date" }]
        });

        // Event listener untuk tombol Create Jurnal (Net Sales)
        $('#NSbelumjurnaltable tbody').on('click', '.btn-create-jurnal-ns', function() {
            var idNetSales = $(this).data('id');
            var url = "{{ url('/jurnalakuntansi/store-manual-netsales') }}/" + idNetSales;

            if (confirm('Apakah Anda yakin ingin membuat jurnal akuntansi untuk ID Net Sales: ' + idNetSales + '?')) {
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
                            tableBelumJurnalNS.ajax.reload(null, false);
                            table.ajax.reload(null, false); // Reload tabel jurnal utama
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').attr('inert', true);
                        alert('Terjadi kesalahan sistem saat membuat jurnal Net Sales.');
                    }
                });
            }
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
                        // 1. RESET FORM KE KONDISI AWAL
                        $('#editJurnalForm')[0].reset();
                        $('#edit_id').val(response.data.id);
                        
                        // 2. SET VALUE DATA UMUM
                        var dateOnly = response.data.tanggal_transaksi.split(' ')[0];
                        $('#edit_tanggal_transaksi').val(dateOnly);
                        $('#edit_keterangan').val(response.data.keterangan);
                        
                        if(response.data.no_akun != null) {
                           $('#no_akun').val(response.data.no_akun); 
                        } else {
                           $('#no_akun').prop('selectedIndex', 0);
                        }

                        // 3. SET VALUE DEBIT & KREDIT SECARA LANGSUNG
                        $('#edit_debit').val(response.data.debit);
                        $('#edit_kredit').val(response.data.kredit);

                        // 4. PAKSA BUKA KUNCI SEMUA FIELD
                        $('#edit_tanggal_transaksi, #edit_keterangan, #no_akun, #edit_debit, #edit_kredit').prop('disabled', false).prop('readonly', false);
                        
                        // 5. SET JUDUL MODAL
                        if (response.is_petty_cash) {
                            $('#editJurnalModalLabel').text('Edit Kas Kecil');
                        } else {
                            $('#editJurnalModalLabel').text('Edit Jurnal Pengajuan');
                        }
                        
                        // 6. MENGHILANGKAN PEMBLOKIR FOKUS BOOTSTRAP
                        $('#editJurnalModal').removeAttr('tabindex'); 
                        $('#editJurnalModal').removeAttr('inert');    
                        
                        // 7. TAMPILKAN MODAL
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

        // Event listener untuk membuka Modal Otomatisasi Jurnal
        $('#btn-otomatisasi-jurnal').click(function() {
            $('#otomatisasiJurnalForm')[0].reset(); // Reset form
            $('#otomatisasiJurnalModal').modal('show');
        });


        $('#btn-otomatisasi').click(function() {
            var url = "{{ route('jurnalakuntansi.otomatisasiJurnal') }}";
            var formData = $('#otomatisasiJurnalForm').serialize();

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#otomatisasiJurnalModal').modal('hide');
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
                        alert('Gagal menyimpan data.');
                    }
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').attr('inert', true);
                    alert('Terjadi kesalahan validasi atau sistem saat menyimpan data.');
                }
            });
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
                        table.ajax.reload(null, false);
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

        // function formatRupiah(angka) {
        //     // Memastikan angka dikonversi menjadi integer untuk menghindari bug pada nilai desimal
        //     let parsedAngka = Math.round(parseFloat(angka));
        //     if (isNaN(parsedAngka)) return '0';

        //     let number_string = parsedAngka.toString(),
        //         sisa = number_string.length % 3,
        //         rupiah = number_string.substr(0, sisa),
        //         ribuan = number_string.substr(sisa).match(/\d{3}/g);

        //     if (ribuan) {
        //         let separator = sisa ? '.' : '';
        //         rupiah += separator + ribuan.join('.');
        //     }

        //     return rupiah;
        // }

        function formatRupiah(angka) {
            return Number(angka).toLocaleString('id-ID');
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

        $('#NSbelumjurnaltable tbody').on('click', 'tr', function (e) {

            // biar tombol tidak ikut trigger
            if ($(e.target).closest('button').length) return;

            var data = tableBelumJurnalNS.row(this).data();
            var id = data.id;

            $.ajax({
                url: "/netsales/" + id + "/detail",
                type: "GET",
                success: function (res) {
                    if (res.success) {
                        let d = res.data;

                        let html = `
                            <table class="table table-bordered">
                                <tr><th>Materi</th><td>${d.rkm?.materi?.nama_materi ?? '-'}</td></tr>
                                <tr><th>Perusahaan</th><td>${d.rkm?.perusahaan?.nama_perusahaan ?? '-'}</td></tr>
                                <tr><th>Transportasi</th><td>${formatRupiah(d.transportasi)}</td></tr>
                                <tr><th>Akomodasi Peserta</th><td>${formatRupiah(d.akomodasi_peserta)}</td></tr>
                                <tr><th>Akomodasi Tim</th><td>${formatRupiah(d.akomodasi_tim)}</td></tr>
                                <tr><th>Fresh Money</th><td>${formatRupiah(d.fresh_money)}</td></tr>
                                <tr><th>Entertain</th><td>${formatRupiah(d.entertaint)}</td></tr>
                                <tr><th>Souvenir</th><td>${formatRupiah(d.souvenir)}</td></tr>
                                <tr><th>Cashback</th><td>${formatRupiah(d.cashback)}</td></tr>
                                <tr><th>Sewa Laptop</th><td>${formatRupiah(d.sewa_laptop)}</td></tr>
                                <tr class="table-primary">
                                    <th>Total</th>
                                    <th>${formatRupiah(res.total)}</th>
                                </tr>
                            </table>
                        `;

                        $('#detailNetSalesContent').html(html);
                        $('#detailNetSalesModal').modal('show');
                    }
                }
            });
        });
    
        $('#belumjurnaltable tbody').on('click', 'tr', function (e) {

            // biar tombol "Tambahkan ke Jurnal" tidak ikut trigger
            if ($(e.target).closest('button').length) return;

            var data = tableBelumJurnal.row(this).data();
            var details = data.detail_pengajuan_barang;

            let html = `
                <table class="table table-bordered">
                    <tr>
                        <th>Nama Karyawan</th>
                        <td>${data.nama_karyawan}</td>
                    </tr>
                    <tr>
                        <th>Tipe</th>
                        <td>${data.tipe}</td>
                    </tr>
                </table>

                <h6 class="mt-3">Detail Barang</h6>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            let total = 0;

            details.forEach(function(item){
                let qty = parseInt(item.qty);
                let harga = parseFloat(item.harga.split('.')[0]);
                let subtotal = qty * harga;
                total += subtotal;

                html += `
                    <tr>
                        <td>${item.nama_barang ?? '-'}</td>
                        <td>${qty}</td>
                        <td>${formatRupiah(harga)}</td>
                        <td>${formatRupiah(subtotal)}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="3">Total</th>
                            <th>${formatRupiah(total)}</th>
                        </tr>
                    </tfoot>
                </table>
            `;

            $('#detailPengajuanContent').html(html);
            $('#detailPengajuanModal').modal('show');
        });  
        
        // EVENT LISTENER BUKA MODAL MASTER NO AKUN & INISIALISASI DATATABLES
        $('#btn-master-no-akun').click(function() {
            $('#masterNoAkunModal').modal('show');

            // Gunakan timeout kecil untuk memastikan modal selesai render sebelum DataTables dimuat
            // Ini mencegah isu lebar kolom yang tidak rata
            setTimeout(function() {
                if ($.fn.DataTable.isDataTable('#tableNoAkun')) {
                    dtNoAkun.ajax.reload();
                } else {
                    dtNoAkun = $('#tableNoAkun').DataTable({
                        ajax: { url: "{{ route('no_akun.data') }}", type: "GET" },
                        columns: [
                            { data: "no" },
                            { data: "nama_akun" },
                            {
                                data: null,
                                render: function(data, type, row) {
                                    return `
                                        <button class="btn btn-sm btn-info text-white" onclick="editNoAkun(${row.id})">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteNoAkun(${row.id})">Hapus</button>
                                    `;
                                }
                            }
                        ],
                        order: [[0, 'asc']],
                        // Penyesuaian agar DataTable muncul sempurna di dalam Modal
                        autoWidth: false,
                        responsive: true
                    });
                }
            }, 200); 
        });

        // Event listener Submit Form Create/Edit No Akun
        $('#noAkunForm').submit(function(e) {
            e.preventDefault();
            let id = $('#id_no_akun').val();
            let url = id ? "{{ url('/no-akun') }}/" + id : "{{ route('no_akun.store') }}";

            $.ajax({
                url: url,
                type: "POST", 
                data: $(this).serialize(),
                success: function(res) {
                    if(res.success) {
                        alert(res.message);
                        $('#formNoAkunModal').modal('hide');
                        if(dtNoAkun) dtNoAkun.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    alert('Gagal menyimpan data.');
                }
            });
        });

        // Event listener Import Master No Akun
        $('#formImportNoAkun').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('no_akun.import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if(res.success) {
                        alert(res.message);
                        $('#importNoAkunModal').modal('hide');
                        $('#formImportNoAkun')[0].reset();
                        if(dtNoAkun) dtNoAkun.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan saat import data.');
                }
            });
        });
    });
    
</script>
@endpush
@endsection