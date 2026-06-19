@extends('layouts.app')

@section('content')
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

    <div class="modal fade" id="mergeModal" tabindex="-1" aria-labelledby="mergeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mergeModalLabel">Penggabungan Data Perusahaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formMergeData">
                        <div class="mb-3">
                            <label for="primary_id" class="form-label">Perusahaan Utama (Dipertahankan)</label>
                            <select class="form-select" id="primary_id" name="primary_id" style="width: 100%;" required>
                                <option value="">Pilih Perusahaan Utama</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="duplicate_id" class="form-label">Perusahaan Duplikat (Digabungkan & Dihapus)</label>
                            <select class="form-select" id="duplicate_id" name="duplicate_id" style="width: 100%;" required>
                                <option value="">Pilih Perusahaan Duplikat</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnProsesMerge">Proses Penggabungan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end align-items-center">
                @can('Edit Perusahaan')
                    <button type="button" class="btn btn-md btn-warning mx-2" data-bs-toggle="modal" data-bs-target="#mergeModal">
                        Gabungkan Duplikat
                    </button>
                @endcan
                @can('Create Perusahaan')
                    <a href="{{ route('perusahaan.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah Perusahaan"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Data Perusahaan</a>
                @endcan
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Perusahaan') }}</h3>
                    <table class="table table-striped" id="perusahaantable">
                        <thead>
                          <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama Perusahaan</th>
                            <th scope="col">Kategori Perusahaan</th>
                            <th scope="col">Wilayah</th>
                            <th scope="col">Sales</th>
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
@push('js')
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function(){
        // Pengaturan CSRF Token untuk AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var idSales = "{{ auth()->user()->id_sales }}";
        var tableIndex = 1;
        if(idSales == 'VN'){
                var idSales = "";
            }
            var table = $('#perusahaantable').DataTable({
                "dom": 'Bfrtip',
                "buttons": [
                            {
                                extend: 'excel',
                                text: 'Export to Excel',
                                exportOptions: {
                                    columns: [ 1, 2, 3, 4 ]
                                },
                                filename: 'Inixindo E-office Data Perusahaan',
                            },
                            {
                                extend: 'pdf',
                                text: 'Export to PDF',
                                exportOptions: {
                                    columns: [ 1, 2, 3, 4 ]
                                },
                                filename: 'Inixindo E-office Data Perusahaan',
                                customize: function(doc) {
                                    doc.content[1].table.widths = ['*', '*', '*', '*'];
                                    doc.content.splice(0, 1, {
                                        text: 'Inixindo E-Office Data Perusahaan',
                                        fontSize: 12,
                                        alignment: 'center',
                                        margin: [0, 0, 0, 12]
                                    });
                                    doc['footer'] = function(currentPage, pageCount) {
                                        return {
                                            text: 'Data Perusahaan ' + currentPage.toString() + ' of ' + pageCount,
                                            alignment: 'center',
                                            margin: [0, 0, 0, 12]
                                        };
                                    };
                                }
                            }
                ],
                "ajax": {
                    "url": "{{ route('getPerusahaanall') }}",
                    "type": "GET",
                    "dataSrc": function (json) {
                        let perusahaanNames = {};
                        json.data.forEach(item => {
                            let normalizedName = item.nama_perusahaan.toLowerCase();
                            if (perusahaanNames[normalizedName]) {
                                perusahaanNames[normalizedName]++;
                            } else {
                                perusahaanNames[normalizedName] = 1;
                            }
                        });
                        
                        json.data.forEach(item => {
                            let normalizedName = item.nama_perusahaan.toLowerCase();
                            item.isDuplicate = perusahaanNames[normalizedName] > 1;
                        });

                        return json.data;
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
                    { "data": null },
                    { 
                        "data": "nama_perusahaan",
                        "render": function(data, type, row) {
                            // Ekstraksi nilai perhitungan dari properti objek
                            var countKaryawan = row.karyawan_count || 0;
                            var countRkms = row.rkms_count || 0;
                            var countPeserta = row.peserta_count || 0;
                            var countContacts = row.contacts_count || 0;
                            var countPeluang = row.peluang_count || 0;

                            // Format tampilan jumlah data
                            var countBadge = `<br><small class="text-muted">
                                [Karyawan: ${countKaryawan}] 
                                [RKM: ${countRkms}] 
                                [Peserta: ${countPeserta}] 
                                [Contact: ${countContacts}] 
                                [Peluang: ${countPeluang}]
                            </small>`;

                            // Evaluasi status duplikat
                            if (row.isDuplicate) {
                                return `<span class="text-danger fw-bold">${data} (Duplikat)</span>` + countBadge;
                            } else {
                                return `<span class="fw-bold">${data}</span>` + countBadge;
                            }
                        }
                    },
                    
                    { "data": "kategori_perusahaan" },
                    { "data": "lokasi" },
                    { "data": "sales_key" },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            var actions = "";
                                actions += '@if (auth()->user()->can('Edit Perusahaan') || auth()->user()->can('Delete Perusahaan'))'
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '<a class="dropdown-item" disabled href="{{ url('/perusahaan') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                                actions += '@can('Edit Perusahaan')';
                                actions += '<a class="dropdown-item" href="{{ url('/perusahaan') }}/' + row.id + '/edit"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                                actions += '@endcan';
                                actions += '@can('Delete Perusahaan')';
                                actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/perusahaan') }}/' + row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('DELETE')';
                                actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '@endcan';
                                actions += '</div>';
                                actions += '</div>';
                                actions += '@else';
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle disabled" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '<a class="dropdown-item" disabled href="{{ url('/perusahaan') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                                actions += '</div>';
                                actions += '</div>';
                                actions += '@endif';
                            return actions;
                        }
                    }
                ],
                "order": [[1, 'asc']],
                "drawCallback": function(settings) {
                    var api = this.api();
                    var start = api.page.info().start;
                    api.column(0, {page:'current'}).nodes().each(function(cell, i) {
                        cell.innerHTML = start + i + 1;
                    });
                },
            });

        // Event listener saat modal merge dibuka
        $('#mergeModal').on('show.bs.modal', function () {
            var selectPrimary = $('#primary_id');
            var selectDuplicate = $('#duplicate_id');
            
            // Pembersihan opsi sebelumnya
            selectPrimary.empty().append('<option value="">Pilih Perusahaan Utama</option>');
            selectDuplicate.empty().append('<option value="">Pilih Perusahaan Duplikat</option>');

            // Mengambil semua data terkini dari DataTables
            var tableData = table.rows().data().toArray();
            
            // Mengurutkan semua data berdasarkan nama perusahaan
            tableData.sort(function(a, b) {
                return a.nama_perusahaan.localeCompare(b.nama_perusahaan);
            });

            // Menambahkan SEMUA opsi ke dalam elemen select dengan atribut penanda duplikat
            tableData.forEach(function(item) {
                var countRkms = item.rkms_count || 0;
                var countPeserta = item.peserta_count || 0;
                var countContacts = item.contacts_count || 0;
                var countPeluang = item.peluang_count || 0;

                var textLokasi = item.lokasi ? ' - ' + item.lokasi : '';
                var textCounts = ` (RKM: ${countRkms}, Peserta: ${countPeserta}, Contact: ${countContacts}, Peluang: ${countPeluang})`;
                var sales = item.sales_key ? item.sales_key : '';
                var optionText = item.nama_perusahaan + ' - ' + sales + textLokasi + textCounts;

                // Inisialisasi objek Option baru
                var optionPrimary = new Option(optionText, item.id);
                var optionDuplicate = new Option(optionText, item.id);

                // Menambahkan atribut data HTML5 untuk mengidentifikasi status duplikat
                $(optionPrimary).attr('data-is-duplicate', item.isDuplicate ? 'true' : 'false');
                $(optionDuplicate).attr('data-is-duplicate', item.isDuplicate ? 'true' : 'false');

                selectPrimary.append(optionPrimary);
                selectDuplicate.append(optionDuplicate);
            });

            // Definisi fungsi penyaringan (matcher) kustom untuk Select2
            function customMatcher(params, data) {
                // Pengecualian selalu tampil untuk opsi placeholder (value kosong)
                if ($.trim(data.id) === '') {
                    return data;
                }

                // Kondisi 1: Jika kotak pencarian kosong (tidak di-search)
                if (typeof params.term === 'undefined' || $.trim(params.term) === '') {
                    // Hanya kembalikan data jika atribut data-is-duplicate bernilai 'true'
                    if ($(data.element).attr('data-is-duplicate') === 'true') {
                        return data;
                    }
                    return null; // Sembunyikan opsi non-duplikat
                }

                // Kondisi 2: Jika ada input di kotak pencarian (di-search)
                // Lakukan pencocokan string standar (case-insensitive) pada semua data
                if (typeof data.text === 'undefined') {
                    return null;
                }

                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                    return data;
                }

                // Sembunyikan opsi jika string tidak cocok dengan kata kunci
                return null; 
            }

            // Inisialisasi Select2 dengan matcher kustom
            $('#primary_id, #duplicate_id').select2({
                dropdownParent: $('#mergeModal'),
                placeholder: "Ketik untuk mencari...",
                allowClear: true,
                matcher: customMatcher
            });
        });

        // Eksekusi proses penggabungan via AJAX
        $('#btnProsesMerge').on('click', function() {
            var primaryId = $('#primary_id').val();
            var duplicateId = $('#duplicate_id').val();

            if (!primaryId || !duplicateId) {
                alert('Pilih data perusahaan utama dan duplikat terlebih dahulu.');
                return;
            }

            if (primaryId === duplicateId) {
                alert('Perusahaan utama dan duplikat tidak boleh sama.');
                return;
            }

            if(confirm('Aksi ini akan memindahkan semua data relasi dan menghapus perusahaan duplikat secara permanen. Proses ini tidak dapat dibatalkan. Lanjutkan?')) {
                $('#mergeModal').modal('hide');
                $('#loadingModal').modal('show');

                $.ajax({
                    url: "{{ route('perusahaan.merge') }}",
                    type: "POST",
                    data: {
                        primary_id: primaryId,
                        duplicate_id: duplicateId
                    },
                    success: function(response) {
                        $('#loadingModal').modal('hide');
                        if(response.status === 'success') {
                            alert(response.message);
                            $('#formMergeData')[0].reset();
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        $('#loadingModal').modal('hide');
                        var errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan internal server.';
                        alert('Kesalahan Sistem: ' + errorMsg);
                        table.ajax.reload();
                    }
                });
            }
        });
    });
</script>
@endpush
@endsection