@extends('layouts_crm.app')

@section('crm_contents')
<div class="container mt-4">

    <div class="card shadow-sm">
        
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Laporan MoM</h5>
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

            <form method="POST" action="{{ route('laporan.harian.update', $laporan->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3"> 

                    <div class="col-md-6">
                        <label class="form-label">Topik</label>
                        <input type="text" name="topic" class="form-control" value="{{ $laporan->topic }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Pelaksanaan</label>
                        <input type="date" name="tanggal_pelaksanaan" class="form-control" value="{{ $laporan->tanggal_pelaksanaan }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Waktu Pelaksanaan</label>
                        <input type="time" name="waktu_pelaksanaan" class="form-control" value="{{ substr($laporan->waktu_pelaksanaan, 0, 5) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tempat / Media Pelaksanaan</label>
                        <input type="text" name="tempat_or_media" class="form-control" value="{{ $laporan->tempat_or_media }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Pimpinan Meeting</label>
                        <select name="pic" class="form-select">
                            <option value="" hidden disable>Pilih PIC</option>
                            @foreach ($sales as $item)
                                <option value="{{ $item->id }}" {{ $laporan->pic == $item->id ? 'selected' : '' }}>{{ $item->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Notulis</label>
                        <select name="notulis" class="form-select">
                            <option value="" hidden disable>Pilih Notulis</option>
                            @foreach ($sales as $item)
                                <option value="{{ $item->id }}" {{ $laporan->notulis == $item->id ? 'selected' : '' }}>{{ $item->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>  

                    <div class="col-md-3">
                        <label class="form-label">Jenis Meeting</label>

                        <select name="jenis_meeting" id="jenis_meeting" class="form-control select2">
                            <option value="" hidden disable>Pilih Jenis</option>
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
                        <label class="form-label">Jumlah Peserta Hadir</label>
                        <input type="number" name="jumlah_peserta_hadir" class="form-control" value="{{ $laporan->jumlah_peserta_hadir }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jumlah Peserta Tidak Hadir</label>
                        <input type="number" name="jumlah_peserta_tidak_hadir" class="form-control" value="{{ $laporan->jumlah_peserta_tidak_hadir }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alasan Peserta Tidak Hadir</label>
                        <input type="text" name="alasan_peserta_tidak_hadir" class="form-control" value="{{ $laporan->alasan_peserta_tidak_hadir }}">
                    </div>

                    <div class="col-6">
                        <label class="form-label">Catatan Tambahan Meeting (Opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3">{{ $laporan->catatan }}</textarea>
                    </div>

                </div>

                <div class="row mt-4">

                    <div class="col-md-4">
                        <label class="form-label">Jenis Catatan Lainnya</label>

                        <select id="jenis_catatan" class="form-select">
                            <option value="" selected hidden>Pilih Jenis Catatan</option>
                            <option value="sales">Catatan Untuk Sales</option>
                            <option value="client">Catatan Untuk Client</option>
                        </select>
                    </div>

                </div>

                <div id="section-sales" style="display:none">
                    {{-- Catatan untuk Sales --}}
                    <div class="p-0 my-5">

                        <div class="card-header d-flex justify-content-between align-items-center p-0">
                            <h5 class="mb-0">Catatan Untuk Sales</h5>
                            <a href="{{ route('laporan.harian.pdf', ['id'=>$laporan->id,'type'=>'sales']) }}" class="btn btn-sm btn-success">
                                <i class="bx bx-file"></i> PDF
                            </a>
                        </div>

                        <div id="sales-wrapper" {{ $laporan->catatanSales->count() == 0 ? 'style=display:none' : '' }}>

                            @if($laporan->catatanSales->count())
                                @foreach ($laporan->catatanSales as $catatan)

                                <div class="row g-3 sales-item mb-2">

                                    <div class="col-md-4">
                                        <label class="form-label">Sales</label>
                                        <select name="sales[]" class="form-select">
                                            <option value="" hidden disabled>Pilih Sales</option>
                                            @foreach ($sales as $item)
                                                <option value="{{ $item->id }}" {{ $catatan->sales_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->nama_lengkap }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Catatan untuk Sales</label>
                                        <textarea name="catatan_sales[]" class="form-control" rows="3">{{ $catatan->catatan }}</textarea>
                                    </div>

                                    <div class="col-md-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-danger remove-sales w-100">
                                            Hapus
                                        </button>
                                    </div>

                                    <hr>

                                </div>

                                @endforeach
                            @endif

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
                            <a href="{{ route('laporan.harian.pdf', ['id'=>$laporan->id,'type'=>'client']) }}" class="btn btn-sm btn-success">
                                <i class="bx bx-file"></i> PDF
                            </a>
                        </div>

                        <div id="client-wrapper" {{ $laporan->catatanClient->count() == 0 ? 'style=display:none' : '' }}>

                            @if($laporan->catatanClient->count())
                                @foreach ($laporan->catatanClient as $catatan)

                                <div class="row g-3 client-item mb-2">

                                    <div class="col-md-10 g-3 row">
                                        <div class="col-md-6">
                                            <label class="form-label">Nama Perusahaan</label>
                                            <input type="text" name="nama_perusahaan[]" class="form-control" value="{{ $catatan->nama_perusahaan }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Kebutuhan Klien</label>
                                            <input type="text" name="kebutuhan[]" class="form-control" value="{{ $catatan->kebutuhan }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Rekomendasi Silabus</label>
                                            <input type="text" name="rekomendasi_silabus[]" class="form-control" value="{{ $catatan->rekomendasi_silabus }}">
                                        </div>

                                        <div class="col-md-6">
                                            <textarea name="catatan_client[]" class="form-control" rows="3" placeholder="Catatan">{{ $catatan->catatan }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-danger remove-client w-100">
                                            Hapus
                                        </button>
                                    </div>

                                    <hr>

                                </div>

                                @endforeach
                            @endif

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
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

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

    });
</script>

@endsection