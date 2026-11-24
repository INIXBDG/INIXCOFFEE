@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container-fluid py-3 px-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            {{-- Header Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h3 class="mb-0 fw-bold text-dark">Edit Laporan MoM</h3>
                                @if($laporan->is_draft)
                                    <span class="badge bg-warning text-dark">Draf</span>
                                @else
                                    <span class="badge bg-success">Final</span>
                                @endif
                            </div>
                            <p class="text-muted mb-0">Ubah notulensi atau lengkapi catatan sales dan client</p>
                        </div>
                        <div class="d-flex gap-2">
                            @if(!$laporan->is_draft)
                                @if($laporan->catatanClient->isNotEmpty())
                                    <a href="{{ route('mom.pdf', ['id' => $laporan->id, 'type' => 'client']) }}" class="btn btn-outline-success btn-sm px-3" target="_blank">
                                        <i class="bx bx-file me-1"></i> PDF Client
                                    </a>
                                @endif
                                @if($laporan->catatanSales->isNotEmpty())
                                    <a href="{{ route('mom.pdf', ['id' => $laporan->id, 'type' => 'sales']) }}" class="btn btn-outline-secondary btn-sm px-3" target="_blank">
                                        <i class="bx bx-file me-1"></i> PDF Sales
                                    </a>
                                @endif
                            @endif
                            <a href="{{ route('mom.index') }}" class="btn btn-secondary btn-sm px-3">
                                <i class="bx bx-arrow-back me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                @if ($errors->any())
                    <div class="alert alert-danger mt-3 mx-4">
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

                <div class="card-body p-4">
                    <form class="auto-save" method="POST" action="{{ route('mom.update', $laporan->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Topik <span class="text-danger">*</span></label>
                                <input type="text" name="topic" class="form-control" value="{{ $laporan->topic }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Pelaksanaan</label>
                                <input type="date" name="tanggal_pelaksanaan" class="form-control" value="{{ $laporan->tanggal_pelaksanaan }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Waktu Pelaksanaan</label>
                                <input type="time" name="waktu_pelaksanaan" class="form-control" value="{{ substr($laporan->waktu_pelaksanaan, 0, 5) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tempat / Media Pelaksanaan</label>
                                <input type="text" name="tempat_or_media" class="form-control" value="{{ $laporan->tempat_or_media }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Pimpinan Meeting</label>
                                <select name="pic" class="form-select">
                                    <option value="" hidden disabled>Pilih Pimpinan</option>
                                    @foreach ($sales as $item)
                                        <option value="{{ $item->id }}" {{ $laporan->pic == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Notulis</label>
                                <select name="notulis" class="form-select">
                                    <option value="" hidden disabled>Pilih Notulis</option>
                                    @foreach ($sales as $item)
                                        <option value="{{ $item->id }}" {{ $laporan->notulis == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Jenis Meeting</label>
                                <select name="jenis_meeting" id="jenis_meeting" class="form-control select2-tags">
                                    <option value="" hidden disabled>Pilih Jenis</option>
                                    <option value="Briefing" {{ $laporan->jenis_meeting == 'Briefing' ? 'selected' : '' }}>Briefing</option>
                                    <option value="Evaluasi" {{ $laporan->jenis_meeting == 'Evaluasi' ? 'selected' : '' }}>Evaluasi</option>
                                    <option value="Prospek" {{ $laporan->jenis_meeting == 'Prospek' ? 'selected' : '' }}>Prospek</option>
                                    <option value="Meeting" {{ $laporan->jenis_meeting == 'Meeting' ? 'selected' : '' }}>Meeting</option>
                                    <option value="Client" {{ $laporan->jenis_meeting == 'Client' ? 'selected' : '' }}>Client</option>
                                    @if (!in_array($laporan->jenis_meeting, ['Briefing', 'Evaluasi', 'Prospek', 'Meeting', 'Client']))
                                        <option value="{{ $laporan->jenis_meeting }}" selected>{{ $laporan->jenis_meeting }}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Jumlah Peserta Hadir</label>
                                <input type="number" name="jumlah_peserta_hadir" class="form-control" value="{{ $laporan->jumlah_peserta_hadir }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Jumlah Peserta Tidak Hadir</label>
                                <input type="number" name="jumlah_peserta_tidak_hadir" class="form-control" value="{{ $laporan->jumlah_peserta_tidak_hadir }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Alasan Peserta Tidak Hadir</label>
                                <input type="text" name="alasan_peserta_tidak_hadir" class="form-control" value="{{ $laporan->alasan_peserta_tidak_hadir }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Catatan Tambahan Meeting (Opsional)</label>
                                <textarea name="catatan" class="form-control" rows="3">{{ $laporan->catatan }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-1">Catatan Khusus Tambahan</label>
                                <select id="jenis_catatan" class="form-select">
                                    <option value="" selected>-- Pilih Jenis Catatan Khusus --</option>
                                    <option value="sales">Catatan Khusus Sales</option>
                                    <option value="client">Catatan Khusus Client</option>
                                </select>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <button type="button" class="btn btn-outline-info" id="autoSaveBtn" disabled>
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Simpan Draf
                                </button>
                                <div class="small text-muted mt-1">Draf otomatis tersimpan saat ada perubahan</div>
                            </div>
                        </div>

                        {{-- Section Catatan Untuk Sales --}}
                        <div id="section-sales" class="card border border-primary-subtle bg-light p-3 mb-3" {{ $laporan->catatanSales->count() == 0 ? 'style=display:none' : '' }}>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold text-primary"><i class="bx bx-user-pin me-1"></i>Catatan Untuk Sales</h6>
                                <button type="button" class="btn btn-sm btn-primary" id="add-sales">
                                    <i class="bx bx-plus me-1"></i> Tambah Sales
                                </button>
                            </div>

                            <div id="sales-wrapper" {{ $laporan->catatanSales->count() == 0 ? 'style=display:none' : '' }}>
                                @if($laporan->catatanSales->count())
                                    @foreach ($laporan->catatanSales as $catatan)
                                        <div class="row g-3 sales-item mb-3 align-items-center bg-white p-3 rounded border">
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">Pilih Sales</label>
                                                <select name="sales[]" class="form-select form-select-sm">
                                                    <option value="" hidden disabled>Pilih Sales</option>
                                                    @foreach ($sales as $item)
                                                        <option value="{{ $item->id }}" {{ $catatan->sales_id == $item->id ? 'selected' : '' }}>
                                                            {{ $item->nama_lengkap }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label small fw-bold">Catatan Sales</label>
                                                <textarea name="catatan_sales[]" class="form-control form-control-sm" rows="2">{{ $catatan->catatan }}</textarea>
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-sales mt-3" data-catatan-id="{{ $catatan->id }}" title="Hapus">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- Section Catatan Untuk Client --}}
                        <div id="section-client" class="card border border-info-subtle bg-light p-3 mb-3" {{ $laporan->catatanClient->count() == 0 ? 'style=display:none' : '' }}>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold text-info"><i class="bx bx-building me-1"></i>Catatan Untuk Client</h6>
                                <button type="button" class="btn btn-sm btn-info text-white" id="add-client">
                                    <i class="bx bx-plus me-1"></i> Tambah Client
                                </button>
                            </div>

                            <div id="client-wrapper" {{ $laporan->catatanClient->count() == 0 ? 'style=display:none' : '' }}>
                                @if($laporan->catatanClient->count())
                                    @foreach ($laporan->catatanClient as $client)
                                        <div class="client-item mb-3 bg-white p-3 rounded border">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">Nama Perusahaan</label>
                                                    <input type="text" name="nama_perusahaan[]" class="form-control form-control-sm" value="{{ $client->nama_perusahaan }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">Kebutuhan Klien</label>
                                                    <input type="text" name="kebutuhan[]" class="form-control form-control-sm" value="{{ $client->kebutuhan }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">Rekomendasi Silabus</label>
                                                    <input type="text" name="rekomendasi_silabus[]" class="form-control form-control-sm" value="{{ $client->rekomendasi_silabus }}">
                                                </div>
                                                <div class="col-md-1 text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-client mt-4" data-catatan-id="{{ $client->id }}" title="Hapus">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <label class="form-label small fw-bold">Catatan Pembicaraan</label>
                                                    <textarea name="catatan_client[]" class="form-control form-control-sm" rows="2">{{ $client->catatan }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('mom.index') }}" class="btn btn-secondary px-4">Kembali</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold" id="submitBtn">
                                <i class="bx bx-check me-1"></i> Perbarui Laporan MoM
                            </button>
                        </div>

                        <input type="hidden" id="is_draft_input" name="is_draft" value="false">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('.select2-tags').select2({
            placeholder: "Pilih atau ketik jenis meeting",
            tags: true,
            theme: "bootstrap-5",
            width: '100%'
        });

        // Toggle jenis catatan
        $('#jenis_catatan').change(function(){
            let jenis = $(this).val();
            if(jenis == 'sales'){
                $('#section-sales').show();
                if($('#sales-wrapper .sales-item').length === 0){
                    $('#add-sales').trigger('click');
                }
            } else if(jenis == 'client'){
                $('#section-client').show();
                if($('#client-wrapper .client-item').length === 0){
                    $('#add-client').trigger('click');
                }
            }
        });

        // Tambah catatan untuk sales
        $('#add-sales').click(function () {
            $('#sales-wrapper').show();
            let salesHtml = `
            <div class="row g-3 sales-item mb-3 align-items-center bg-white p-3 rounded border">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Pilih Sales</label>
                    <select name="sales[]" class="form-select form-select-sm">
                        <option value="" hidden disabled selected>Pilih Sales</option>
                        @foreach ($sales as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label small fw-bold">Catatan Sales</label>
                    <textarea name="catatan_sales[]" class="form-control form-control-sm" rows="2" placeholder="Catatan tugas / tindak lanjut"></textarea>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-sales mt-3" title="Hapus">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </div>`;
            $('#sales-wrapper').append(salesHtml);
        });

        $(document).on('click', '.remove-sales', function () {
            $(this).closest('.sales-item').remove();
            if($('.sales-item').length === 0){
                $('#sales-wrapper').hide();
            }
        });

        // Tambah catatan untuk client
        $('#add-client').click(function () {
            $('#client-wrapper').show();
            let clientHtml = `
            <div class="client-item mb-3 bg-white p-3 rounded border">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nama Perusahaan</label>
                        <input type="text" name="nama_perusahaan[]" class="form-control form-control-sm" placeholder="PT ...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Kebutuhan Klien</label>
                        <input type="text" name="kebutuhan[]" class="form-control form-control-sm" placeholder="Training / Konsultasi ...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Rekomendasi Silabus</label>
                        <input type="text" name="rekomendasi_silabus[]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-client mt-4" title="Hapus">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label small fw-bold">Catatan Pembicaraan</label>
                        <textarea name="catatan_client[]" class="form-control form-control-sm" rows="2" placeholder="Catatan penting terkait klien"></textarea>
                    </div>
                </div>
            </div>`;
            $('#client-wrapper').append(clientHtml);
        });

        $(document).on('click', '.remove-client', function () {
            $(this).closest('.client-item').remove();
            if($('.client-item').length === 0){
                $('#client-wrapper').hide();
            }
        });

        let momId = {{ $laporan->id }};
        let timeout = null;
        let isAutoSaving = false;

        function isValidAutoSave() {
            let topic = $('input[name="topic"]').val();
            return (topic && topic.trim() !== '');
        }

        function isFormValid() {
            const topic = $('input[name="topic"]').val();
            return (topic && topic.trim() !== '');
        }

        function updateSubmitButtonState() {
            $('#submitBtn').prop('disabled', !isFormValid());
        }

        function updateAutoSaveButtonState() {
            $('#autoSaveBtn').prop('disabled', !isValidAutoSave());
        }

        $('input, select, textarea').on('change input', function() {
            updateSubmitButtonState();
            updateAutoSaveButtonState();
        });

        updateSubmitButtonState();
        updateAutoSaveButtonState();

        $('.auto-save').on('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (!isAutoSaving && isValidAutoSave()) {
                    autoSave();
                }
            }, 5000);
        });

        function autoSave() {
            if (isAutoSaving) return;
            isAutoSaving = true;
            let formData = $('.auto-save').serializeArray();
            if (momId) {
                formData.push({ name: 'id', value: momId });
            }

            $.ajax({
                url: "{{ route('mom.autosave') }}",
                method: 'POST',
                data: $.param(formData),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        showNotification('Draf berhasil diperbarui', 'success');
                    }
                    isAutoSaving = false;
                },
                error: function () {
                    showNotification('Gagal menyimpan draf', 'danger');
                    isAutoSaving = false;
                }
            });
        }

        $('#autoSaveBtn').click(function() {
            if (isValidAutoSave()) {
                autoSave();
            }
        });

        function showNotification(message, type) {
            let notification = $(`
                <div class="alert alert-${type} alert-dismissible fade show position-fixed"
                     style="top: 80px; right: 20px; z-index: 99999; min-width: 250px;" role="alert">
                    <i class="bx bx-${type === 'success' ? 'check' : 'error'}-circle me-1"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            $('body').append(notification);
            setTimeout(() => { notification.fadeOut(() => notification.remove()); }, 3000);
        }

        $('.auto-save').on('submit', function() {
            $('#is_draft_input').val('false');
            if (momId) {
                $('input[name="id"]').remove();
                $(this).append('<input type="hidden" name="id" value="' + momId + '">');
            }
        });
    });
</script>
@endpush
@endsection
