@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Modal Loading Spinner --}}
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
            <div class="d-flex justify-content-end mb-3">
                {{-- @can('Create Administrasi') --}}
                    <button type="button" class="btn btn-md click-primary mx-4" data-bs-toggle="modal" data-bs-target="#createModal">
                        <img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Tambah Administrasi
                    </button>
                {{-- @endcan --}}
            </div>

            {{-- Modal Kelola Dokumen (Update Stage) --}}
            <div class="modal fade" id="updateStageModal" tabindex="-1" aria-labelledby="updateStageModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateStageModalLabel">{{ __('Kelola Dokumen Proyek') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="formUpdateStage" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="update_project_id" name="project_id">
                            
                            <div class="modal-body">
                                <h6 class="fw-bold mb-3 text-primary" id="update_project_name">Nama Proyek</h6>
                                
                                {{-- Antarmuka Unggah Dokumen --}}
                                <div id="uploadSection">
                                    <div class="mb-3">
                                        <label for="current_stage" class="form-label fw-bold">{{ __('Pilih Kategori Dokumen') }}</label>
                                        <select class="form-select" id="current_stage" name="current_stage">
                                            <option value="">-- Pilih Dokumen yang Ingin Diunggah --</option>
                                            <optgroup label="Fase Administrasi Awal">
                                                <option value="kak">Kerangka Acuan Kerja (KAK)</option>
                                                <option value="proposal">Proposal</option>
                                                <option value="penganggaran">RAB / Penganggaran</option>
                                                <option value="surat_pekerjaan_dimulai">Surat Pekerjaan Dimulai / Kontrak</option>
                                                <option value="dokumen_klien">Dokumen Klien</option>
                                            </optgroup>
                                            <optgroup label="Fase Penutupan (Handover)">
                                                <option value="bast">Berita Acara Serah Terima (BAST)</option>
                                                <option value="final_report">Laporan Akhir</option>
                                                <option value="pembayaran">Dokumen Pembayaran / Invoice</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="file" class="form-label fw-bold">{{ __('Unggah Dokumen') }} <small class="text-muted">(Dapat memilih lebih dari 1 berkas sekaligus)</small></label>
                                        <input class="form-control" type="file" id="file" name="file[]" multiple required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                        <div class="form-text mt-1 text-black"><i class="fas fa-info-circle me-1"></i> Jika dokumen sudah ada, berkas baru akan ditambahkan (tidak menimpa yang lama).</div>
                                    </div>
                                </div>

                                {{-- Antarmuka Keputusan Akhir (Final Decision) --}}
                                <div id="decisionSection" style="display: none;">
                                    <div class="alert alert-success" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>Seluruh prasyarat dokumen telah lengkap. Tentukan arah proyek ini selanjutnya.
                                    </div>
                                    <div class="mb-3">
                                        <label for="final_decision" class="form-label fw-bold">{{ __('Keputusan Proyek') }}</label>
                                        <select class="form-select" id="final_decision" name="final_decision">
                                            <option value="">-- Pilih Keputusan --</option>
                                            <option value="lanjut">Lanjut ke Eksekusi (Masuk ke Kanban Board)</option>
                                            <option value="gagal">Gagal (Proyek/Negosiasi Batal)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                                <button type="submit" class="btn btn-primary" id="btnUpdateSave">
                                    <i class="fas fa-save me-1"></i> {{ __('Simpan') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Create Administrasi --}}
            <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createModalLabel">{{ __('Tambah Data Administrasi Proyek') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="formCreateAdministrasi">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nama_projek" class="form-label">{{ __('Nama Projek') }}</label>
                                    <input type="text" class="form-control" id="nama_projek" name="nama_projek" required>
                                </div>
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">{{ __('Deskripsi Projek') }}</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="perusahaan_key" class="form-label">{{ __('Klien Dari :') }}</label>
                                    <select style="height: 30px; width: 100%;" class="form-control @error('perusahaan_key') is-invalid @enderror" name="perusahaan_key" id="perusahaan_key">
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                                <button type="submit" class="btn btn-primary" id="btnSave">{{ __('Simpan') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tabel Utama Data Administrasi --}}
            <div class="card m-4 shadow-sm border-0">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-3">{{ __('Data Administrasi Proyek') }}</h3>
                    <table class="table table-striped " id="administrasiProjekTable" style="width:100%">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Projek</th>
                                <th scope="col">Perusahaan/Klien</th>
                                <th scope="col">KAK</th>
                                <th scope="col">Proposal</th>
                                <th scope="col">RAB</th>
                                <th scope="col">SPK/Kontrak</th>
                                <th scope="col">Dokumen Klien</th>
                                <th scope="col">BAST</th>
                                <th scope="col">Laporan Akhir</th>
                                <th scope="col">Pembayaran</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-nowrap">
                            {{-- Diisi oleh DataTables AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Loading Cube (Mempertahankan desain bawaan) */
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

    @keyframes spin { to { -webkit-transform: rotate(360deg); } }
    @-webkit-keyframes spin { to { -webkit-transform: rotate(360deg); } }

    .modal-content {
        border-radius: 8px; /* Diperhalus sedikit agar lebih modern */
        box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.2);
    }
    .modal-backdrop.show {
        opacity: 0.75;
    }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function(){
        
        // --- Fungsi Pembantu untuk Merender Kolom Dokumen (Multi-file Support) ---
        // --- Fungsi Pembantu untuk Merender Kolom Dokumen (Desain Sesuai Gambar) ---
        function renderDocumentColumn(data) {
            // Gaya visual jika dokumen kosong (Silang Merah)
            const emptyBadge = '<span class="text-danger fw-bold fs-5">&#10008;</span>';
            
            if (!data || data === 'null' || data === '[]') {
                return emptyBadge;
            }
            
            try {
                let parsed = JSON.parse(data);
                if (Array.isArray(parsed)) {
                    if (parsed.length === 0) return emptyBadge;
                    // Gaya visual jika dokumen terisi lebih dari satu (Blok Hijau Solid)
                    return `<span class="badge bg-success shadow-sm" style="padding: 6px 12px; font-size: 0.85rem; border-radius: 4px;">${parsed.length} File</span>`;
                }
            } catch (e) {
                // Tangkapan aman untuk format data lama (string tunggal)
                if(typeof data === 'string' && data.trim() !== '') {
                    // Gaya visual jika dokumen hanya satu (Ceklis Ungu Sesuai Gambar)
                    // Anda dapat mengganti warna style jika ungu kurang pas, misal: color: #6f42c1;
                    return '<span class="fw-bold fs-5" style="color: #6f42c1;">&#10004;</span>';
                }
            }
            return emptyBadge;
        }

        // --- Inisialisasi DataTables ---
        $('#administrasiProjekTable').DataTable({
            "ajax": {
                "url": "{{ route('getAdministrasi') }}",
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                },
                "complete": function () {
                    setTimeout(() => { $('#loadingModal').modal('hide'); }, 800);
                }
            },
            "scrollX": true, 
            "columns": [
                {
                    "data": null,
                    "searchable": false,
                    "orderable": false,
                    "render": function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { "data": 'dataproject.name', "className": "text-start" },
                { 
                    "data": 'dataproject.client.nama_perusahaan',
                    "className": "text-start",
                    "render": function(data) { return data ? data : '-'; }
                },
                { "data": "kak_file", "render": renderDocumentColumn },
                { "data": "proposal_file", "render": renderDocumentColumn },
                { "data": "budget_file", "render": renderDocumentColumn },
                { "data": "surat_pekerjaan_dimulai_file", "render": renderDocumentColumn },
                { "data": "client_doc_file", "render": renderDocumentColumn },
                { "data": "project_handover.bast_file", "render": renderDocumentColumn },
                { "data": "project_handover.final_report_file", "render": renderDocumentColumn },
                { "data": "payment_doc_file", "render": renderDocumentColumn },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        if (row.dataproject.phase === 'teknis') return '<span class="badge bg-primary px-3 py-2" style="border-radius: 4px;">Eksekusi Aktif (Kanban)</span>';
                        if (row.dataproject.phase === 'gagal') return '<span class="badge bg-danger px-3 py-2" style="border-radius: 4px;">Proyek Gagal</span>';
                        if (row.dataproject.phase === 'selesai') return '<span class="badge bg-success px-3 py-2" style="border-radius: 4px;">Proyek Selesai</span>';
                        return '<span class="badge bg-warning text-dark px-3 py-2" style="border-radius: 4px;">Administrasi / Persiapan</span>';
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        // Logika Penentuan Kelengkapan Dokumen Dasar
                        // Logika Penentuan Kelengkapan Dokumen Dasar
                        let isComplete = row.kak_file && row.proposal_file && row.budget_file && row.client_doc_file && row.surat_pekerjaan_dimulai_file;
                        
                        let actions = "";
                        
                        if (row.dataproject.phase === 'administrasi') {
                            if (isComplete) {
                                actions += '<button class="btn btn-sm btn-success btn-update-stage" style="border-radius: 4px;">Keputusan Akhir</button>';
                            } else {
                                actions += '<button class="btn btn-sm btn-primary btn-update-stage" style="border-radius: 4px;">Kelola Dokumen</button>';
                            }
                        } else {
                             // Jika proyek masuk tahap teknis/selesai, biarkan pengguna tetap bisa mengelola dokumen (misal Laporan Akhir)
                             actions += '<button class="btn btn-sm btn-primary btn-update-stage" style="border-radius: 4px;">Kelola Dokumen</button>';
                        }
                        
                        return actions;
                    }
                },
            ]
        });

        // --- Inisialisasi Select2 ---
        $('#perusahaan_key').select2({
            placeholder: "Cari & Pilih Perusahaan...",
            allowClear: true,
            dropdownParent: $('#createModal'),
            ajax: {
                url: '{{route('getPerusahaan')}}',
                processResults: function({data}){
                    return{
                        results: $.map(data, function(item){
                            return { id: item.id, text: item.nama_perusahaan }
                        })
                    }
                }
            }
        });

        // --- Submit Form Pembuatan Administrasi ---
        $('#formCreateAdministrasi').on('submit', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route('administrasi.store') }}",
                type: "POST",
                data: formData,
                beforeSend: function() {
                    $('#btnSave').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                },
                success: function(response) {
                    $('#createModal').modal('hide');
                    $('#formCreateAdministrasi')[0].reset();
                    $('#perusahaan_key').val(null).trigger('change');
                    $('#administrasiProjekTable').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                    alert(errorMessage);
                },
                complete: function() {
                    $('#btnSave').prop('disabled', false).text('Simpan');
                }
            });
        });

        // --- Buka Modal Pengelolaan Dokumen (Update Stage) ---
        $('#administrasiProjekTable tbody').on('click', '.btn-update-stage', function () {
            var data = $('#administrasiProjekTable').DataTable().row($(this).parents('tr')).data();
            
            $('#formUpdateStage')[0].reset();
            $('#update_project_id').val(data.dataproject.id);
            $('#update_project_name').text(data.dataproject.name);

            // Cek kondisi kelengkapan dokumen minimum
            var isComplete = data.kak_file && data.proposal_file && data.budget_file && data.client_doc_file && data.surat_pekerjaan_dimulai_file;

            // Jika status masih administrasi dan dokumen lengkap, tawarkan Keputusan Akhir
            if (data.dataproject.phase === 'administrasi' && isComplete) {
                $('#uploadSection').hide();
                $('#file').removeAttr('required');
                $('#current_stage').removeAttr('required');
                
                $('#decisionSection').show();
                $('#final_decision').attr('required', true);
            } else {
                // Tampilan reguler: Upload Dokumen Secara Acak
                $('#decisionSection').hide();
                $('#final_decision').removeAttr('required');
                
                $('#uploadSection').show();
                $('#file').attr('required', true);
                $('#current_stage').attr('required', true);
            }

            $('#updateStageModal').modal('show');
        });

        // --- Submit Form Pengelolaan Dokumen (AJAX with FormData) ---
        $('#formUpdateStage').on('submit', function(e) {
            e.preventDefault();
            var projectId = $('#update_project_id').val();
            var formData = new FormData(this); // Menangani transmisi file input[]
            var actionUrl = "{{ url('/projects/administrasi') }}/" + projectId + "/update-stage";

            $.ajax({
                url: actionUrl,
                type: "POST",
                data: formData,
                contentType: false, 
                processData: false, 
                beforeSend: function() {
                    $('#btnUpdateSave').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                    $('#loadingModal').modal('show');
                },
                success: function(response) {
                    $('#updateStageModal').modal('hide');
                    $('#formUpdateStage')[0].reset();
                    $('#administrasiProjekTable').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                    alert(errorMessage);
                },
                complete: function() {
                    $('#btnUpdateSave').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan');
                    setTimeout(() => { $('#loadingModal').modal('hide'); }, 500);
                }
            });
        });
    });
</script>
@endpush
@endsection