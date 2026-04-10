@extends('layouts_crm.app')

@section('crm_contents')
<div class="container mt-4">

    <div class="card shadow-sm">
        
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tambah Laporan MoM</h5>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mt-3 mx-3">
                <div class="fw-bold mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Terjadi kesalahan:
                </div>

                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-body">

            <form class="auto-save" method="POST" action="{{ route('laporan.harian.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3"> 

                    <div class="col-md-6">
                        <label class="form-label">Topik <span class="text-danger">*</span></label>
                        <input type="text" name="topic" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pelaksanaan" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Waktu Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="time" name="waktu_pelaksanaan" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tempat / Media Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="text" name="tempat_or_media" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Pimpinan Meeting <span class="text-danger">*</span></label>
                        <select name="pic" class="form-select">
                            <option value="" hidden disable>Pilih Pimpinan</option>
                            @foreach ($sales as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Notulis <span class="text-danger">*</span></label>
                        <select name="notulis" class="form-select">
                            <option value="" hidden disable>Pilih Notulis</option>
                            @foreach ($sales as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jenis Meeting <span class="text-danger">*</span></label>

                        <select name="jenis_meeting" id="jenis_meeting" class="form-control select2">
                            <option value="" hidden disable>Pilih Jenis</option>
                            <option value="Briefing">Briefing</option>
                            <option value="Evaluasi">Evaluasi</option>
                            <option value="Prospek">Prospek</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Client">Client</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah Peserta Hadir <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_peserta_hadir" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah Peserta Tidak Hadir</label>
                        <input type="number" name="jumlah_peserta_tidak_hadir" class="form-control">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Alasan Peserta Tidak Hadir</label>
                        <input type="text" name="alasan_peserta_tidak_hadir" class="form-control">
                    </div>

                    <div class="col-6">
                        <label class="form-label">Catatan Tambahan Meeting (Opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>

                </div>

                <div class="row mt-4 justify-content-between col-md-12">

                    <div class="col-md-4">
                        <label class="form-label">Jenis Catatan Lainnya</label>

                        <select id="jenis_catatan" class="form-select">
                            <option value="" selected hidden>Pilih Jenis Catatan</option>
                            <option value="sales">Catatan Untuk Sales</option>
                            <option value="client">Catatan Untuk Client</option>
                        </select>
                    </div>

                    <div class="col-md-3 mt-4 text-end">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-info" id="autoSaveBtn" disabled>
                                <i class="bi bi-cloud-arrow-up me-1"></i>Simpan Draf
                            </button>
                        </div>
                        <small class="form-text text-muted">Harap isi topik untuk menyimpan draf</small>
                    </div>

                </div>

                <div id="section-sales" style="display:none">
                    {{-- Catatan Untuk sales --}}
                    <div class="p-0 my-5">

                        <div class="card-header d-flex justify-content-between align-items-center p-0">
                            <h5 class="mb-0">Catatan Untuk Sales</h5>
                        </div>

                        <div id="sales-wrapper" style="display: none;">
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary" id="add-sales">
                                + Tambah
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="section-client" style="display:none">
                    {{-- Catatan untuk Client --}}
                    <div class="p-0 my-5">

                        <div class="card-header d-flex justify-content-between align-items-center p-0">
                            <h5 class="mb-0">Catatan Untuk Client</h5>
                        </div>

                        <div id="client-wrapper" style="display: none;">
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary" id="add-client">
                                + Tambah
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>
                </div>

                <input type="hidden" id="is_draft_input" name="is_draft" value="false">

            </form>

        </div>
    </div>

</div>

<style>
    /* samakan dengan form-control bootstrap */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding-left: 12px;
        display: flex;
        align-items: center;
    }

    /* text dalam select */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        line-height: normal;
        color: #6c757d;
    }

    /* arrow dropdown */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        right: 10px;
    }

    /* focus effect seperti bootstrap */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.25);
    }

    /* dropdown menu */
    .select2-dropdown {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
    }

    /* hover option */
    .select2-results__option--highlighted {
        background-color: #0d6efd !important;
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {

        // Select2 untuk jenis meeting
        $('.select2').select2({
            placeholder: "Pilih atau ketik jenis meeting",
            tags: true,
            width: '100%'
        });


        // Toggle jenis catatan
        $('#jenis_catatan').change(function(){

            let jenis = $(this).val();

            if(jenis == 'sales'){

                $('#section-sales').show();
                $('#section-client').hide();

            } 
            else if(jenis == 'client'){

                $('#section-client').show();
                $('#section-sales').hide();

            }

        });


        // Tambah catatan untuk sales
        $('#add-sales').click(function () {

            // Tampilkan sales wrapper jika tersembunyi
            $('#sales-wrapper').show();

            let salesHtml = `
            <div class="row g-3 sales-item mb-2">

                <div class="col-md-4">
                    <select name="sales[]" class="form-select">
                        <option value="" hidden disabled selected>Pilih Sales</option>
                        @foreach ($sales as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <textarea name="catatan_sales[]" class="form-control" rows="3" placeholder="Catatan"></textarea>
                </div>

                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-sales w-100">
                        Hapus
                    </button>
                </div>

                <hr>

            </div>
            `;

            $('#sales-wrapper').append(salesHtml);

        });

        $(document).on('click', '.remove-sales', function () {

            if($('.sales-item').length > 1){
                $(this).closest('.sales-item').remove();
            } else {
                // Jika ini adalah item terakhir, sembunyikan wrapper
                $(this).closest('.sales-item').remove();
                $('#sales-wrapper').hide();
            }

        });
        
        
        // Tambah catatan untuk client
        $('#add-client').click(function () {

            // Tampilkan client wrapper jika tersembunyi
            $('#client-wrapper').show();

            let clientHtml = `
            <div class="row g-3 client-item mb-2">

                <div class="col-md-10 g-3 row">
                    <div class="col-md-6">
                        <label class="form-label">Nama Perusahaan</label>
                        <input type="text" name="nama_perusahaan[]" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kebutuhan Klien</label>
                        <input type="text" name="kebutuhan[]" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Rekomendasi Silabus</label>
                        <input type="text" name="rekomendasi_silabus[]" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <textarea name="catatan_client[]" class="form-control" rows="3" placeholder="Catatan"></textarea>
                    </div>
                </div>

                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-client w-100">
                        Hapus
                    </button>
                </div>

                <hr>

            </div>
            `;

            $('#client-wrapper').append(clientHtml);

        });

        $(document).on('click', '.remove-client', function () {

            if($('.client-item').length > 1){
                $(this).closest('.client-item').remove();
            } else {
                // Jika ini adalah item terakhir, sembunyikan wrapper
                $(this).closest('.client-item').remove();
                $('#client-wrapper').hide();
            }

        });

        let momId = null;
        let timeout = null;
        let hasChanged = false;
        let isAutoSaving = false; // Flag untuk prevent double request

        // Fungsi untuk validasi autosave (hanya topic)
        function isValidAutoSave() {
            let topic = $('input[name="topic"]').val();
            
            // Topic HARUS terisi sebagai persyaratan utama
            if (!topic || topic.trim() === '') {
                return false;
            }
            
            return true;
        }

        // Fungsi untuk validasi form submit - cek semua field required
        function isFormValid() {
            const topic = $('input[name="topic"]').val();
            const tanggalPelaksanaan = $('input[name="tanggal_pelaksanaan"]').val();
            const waktuPelaksanaan = $('input[name="waktu_pelaksanaan"]').val();
            const tempatMedia = $('input[name="tempat_or_media"]').val();
            const jumlahHadir = $('input[name="jumlah_peserta_hadir"]').val();
            const jenisMeeting = $('select[name="jenis_meeting"]').val();
            const pic = $('select[name="pic"]').val();

            // Validasi semua field required
            if (!topic || topic.trim() === '') return false;
            if (!tanggalPelaksanaan || tanggalPelaksanaan.trim() === '') return false;
            if (!waktuPelaksanaan || waktuPelaksanaan.trim() === '') return false;
            if (!tempatMedia || tempatMedia.trim() === '') return false;
            if (!jumlahHadir || jumlahHadir.trim() === '' || parseInt(jumlahHadir) < 1) return false;
            if (!jenisMeeting || jenisMeeting.trim() === '') return false;
            if (!pic || pic.trim() === '') return false;

            return true;
        }

        // Update status button submit
        function updateSubmitButtonState() {
            let isValid = isFormValid();
            $('#submitBtn').prop('disabled', !isValid);
        }

        function updateAutoSaveButtonState() {
            let isValid = isValidAutoSave();
            $('#autoSaveBtn').prop('disabled', !isValid);
        }

        // Monitor perubahan di semua field required
        $('input[name="topic"], input[name="tanggal_pelaksanaan"], input[name="waktu_pelaksanaan"], input[name="tempat_or_media"], input[name="jumlah_peserta_hadir"], select[name="jenis_meeting"], select[name="pic"]').on('change input', function() {
            updateSubmitButtonState();
            updateAutoSaveButtonState();
        });

        // Initial state
        updateSubmitButtonState();
        updateAutoSaveButtonState();

        $('.auto-save').on('input', function () {
            hasChanged = true;

            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (!isAutoSaving && isValidAutoSave()) {
                    console.log('Auto-saving...');
                    autoSave();
                    hasChanged = false;
                } else {
                    console.log('Belum memenuhi syarat autosave atau sedang autosave');
                }
            }, 5000);
        });


        function autoSave() {
            // Cegah double request
            if (isAutoSaving) {
                console.log('Auto-save sedang berjalan, skip request...');
                return;
            }

            isAutoSaving = true;
            let formData = $('.auto-save').serializeArray();

            // Hanya kirim id jika sudah ada (untuk update), jangan kirim id jika null
            if (momId) {
                formData.push({ name: 'id', value: momId });
            }

            console.log('Data untuk auto-save:', formData);

            $.ajax({
                url: "{{ route('laporan.harian.autosave') }}",
                method: 'POST',
                data: $.param(formData),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        
                        if (response.id && !momId) {
                            momId = response.id;
                            console.log('Laporan draft dibuat dengan ID:', momId);
                           
                            showNotification('Draf berhasil disimpan', 'success');
                        } else if (response.id) {
                            console.log('Laporan draft diperbarui, ID:', response.id);
                            showNotification('Draf berhasil diperbarui', 'success');
                        }
                    }
                    isAutoSaving = false;
                },
                error: function (error) {
                    console.error('Auto-save failed:', error);
                    showNotification('Gagal menyimpan draf', 'error');
                    isAutoSaving = false;
                }
            });
        }

        // Manual trigger auto save button
        $('#autoSaveBtn').click(function() {
            if (isValidAutoSave()) {
                console.log('Manual auto-save triggered');
                autoSave();
            }
        });

        function showNotification(message, type) {
            let alertClass = type === 'success' ? 'success' : 'danger';
            
            let notification = `
                <div class="alert alert-${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" 
                     role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            $('body').append(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                $('.position-fixed.alert').fadeOut(() => {
                    $('.position-fixed.alert').remove();
                });
            }, 3000);
        }

        // Handle form submit - ubah is_draft menjadi false dan tambahkan momId jika ada
        $('.auto-save').on('submit', function(e) {
            // Set is_draft menjadi false untuk submit manual
            $('#is_draft_input').val('false');
            
            // Jika ada draft ID, tambahkan sebagai hidden input
            if (momId) {
                // Hapus hidden input id jika sudah ada
                $('input[name="id"]').remove();
                // Tambah hidden input dengan momId
                $(this).append('<input type="hidden" name="id" value="' + momId + '">');
                console.log('Form submit dengan draft ID:', momId);
            }
        });
    });
</script>

@endsection