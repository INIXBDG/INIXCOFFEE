@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 d-flex my-2 justify-content-end">
            @can('Create RKM')
            <a class="btn click-primary mx-1" href="{{ route('rkm.create') }}">Tambah RKM</a>
            @endcan
        </div>
        <div class="col-md-12">
            <div class="card" style="width: 100%">
                <div class="card-body d-flex justify-content-center">
                    <div class="col-md-4 mx-1">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select id="tahun" class="form-select" aria-label="tahun">
                            <option disabled>Pilih Tahun</option>
                            @php
                            $tahun_sekarang = now()->year;
                            for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                $selected=$tahun==$tahun_sekarang ? 'selected' : '' ;
                                echo "<option value=\" $tahun\" $selected>$tahun</option>";
                                }
                                @endphp
                        </select>
                    </div>
                    <div class="col-md-4 mx-1">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select id="bulan" class="form-select" aria-label="bulan">
                            <option disabled>Pilih Bulan</option>
                            @php
                            $bulan_sekarang = now()->month;
                            $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            for ($bulan = 1; $bulan <= 12; $bulan++) {
                                $bulan_awal=$nama_bulan[$bulan - 1];
                                $bulan_akhir=$nama_bulan[$bulan % 12];
                                $selected=$bulan==$bulan_sekarang ? 'selected' : '' ;
                                echo "<option value=\" $bulan\" $selected>$bulan_awal - $bulan_akhir</option>";
                                }
                                @endphp
                        </select>
                    </div>
                    <div class="col-md-4 mx-1">
                        <button type="button" onclick="getDataRKM()" class="btn click-primary" style="margin-top: 30px; height: 37px;">Cari Data</button>
                        <button type="button" onclick="excelDownload()" class="btn btn-success" style="margin-top: 30px">Download excel</button>
                    </div>
                </div>
            </div>
            <div class="row my-2">
                <div class="col-md-12" id="content">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalRekomendasiLanjutan" tabindex="-1" role="dialog" aria-labelledby="modalRekomendasiLanjutanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formStoreRekomendasi">
                @csrf
                <input type="hidden" name="id_rekomendasi" id="id_rekomendasi">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRekomendasiLanjutanLabel">Ajukan Rekomendasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row mt-3">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label for="Materi">Materi Sebelumnya</label>
                                    <input type="text" class="form-control" id="Materi" readonly>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="form-group">
                                    <label for="tanggal_training">Tanggal Training</label>
                                    <input type="text" class="form-control" id="tanggal_training" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label for="perusahaan">Perusahaan</label>
                                    <input type="text" class="form-control" id="perusahaan" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label for="rekomendasi">Ajukan Rekomendasi Materi Selanjutnya</label>
                                    <select name="rekomendasi[]" id="rekomendasi" class="form-control rekomendasi" multiple="multiple" required>
                                        {{-- <option value="" disabled>Pilih Materi</option> --}}
                                        @foreach ($dataMateri as $data)
                                        <option value="{{ $data->id }}">{{ $data->nama_materi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label for="keterangan_rekomendasi">Keterangan</label>
                                    <textarea name="keterangan" id="keterangan_rekomendasi" class="form-control" rows="3" placeholder="Masukkan keterangan tambahan..."></textarea>
                                </div>
                            </div>
                        </div>
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
<style>
    #content {
        overflow-y: hidden;
    }

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

    .dropdown-submenu {
        position: relative;
    }

    .dropdown-submenu .dropdown-menu {
        top: 100%;
        left: 0;
        margin-top: 1px;
        display: none;
    }

    .dropdown-submenu:hover>.dropdown-menu {
        display: block;
    }

    .dropdown-submenu.left .dropdown-menu {
        top: 100%;
        right: 0;
        left: auto;
    }

    .select2-container .select2-search--dropdown .select2-search__field {
        width: 100% !important;
    }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function() {
        getDataRKM();

        $(document).on('click', '.js-ajukan-rekomendasi', function(e) {
            e.preventDefault();

            var id_rkm = $(this).data('id');
            var materi_sebelumnya = $(this).data('materi');
            var tanggal_training = $(this).data('tanggal');
            var perusahaan = $(this).data('perusahaan');
            var id_rekomendasi = $(this).data('rekomendasi-id');
            var keterangan = $(this).data('keterangan');
            
            // AMBIL DATA MATERI (Pastikan ini dikirim sebagai array atau string dipisah koma)
            var id_rekomendasi_materi = $(this).data('rekomendasi-materi-id'); 

            $('#Materi').val(materi_sebelumnya);
            $('#tanggal_training').val(tanggal_training);
            $('#perusahaan').val(perusahaan);
            $('#id_rekomendasi').val(id_rekomendasi || '');
            $('#keterangan_rekomendasi').val(keterangan || '');

            // Logika penanganan Hidden Input id_rkm
            var $hidden = $('input[name="id_rkm"]');
            if ($hidden.length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'id_rkm',
                    value: id_rkm
                }).appendTo('#formStoreRekomendasi');
            } else {
                $hidden.val(id_rkm);
            }

            // INISIALISASI SELECT2
            $('.rekomendasi').select2({
                placeholder: "Pilih Materi",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalRekomendasiLanjutan')
            });

            // SET VALUE (Jika data tersimpan adalah string "1,2,3", ubah ke array [1,2,3])
            if (id_rekomendasi_materi) {
                var selectedValues = id_rekomendasi_materi.toString().split(',');
                $('#rekomendasi').val(selectedValues).trigger('change');
            } else {
                $('#rekomendasi').val(null).trigger('change');
            }
        });

        $('#formStoreRekomendasi').on('submit', function(e) {
            e.preventDefault();

            // serialize() akan otomatis mengambil 'rekomendasi[]' dan 'id_rkm' 
            // sehingga terbaca sebagai array oleh Laravel
            var formData = $(this).serialize(); 

            $.ajax({
                url: '/rekomendasi-lanjutan/store',
                method: 'POST',
                data: formData, 
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Berhasil!', res.message, 'success');
                        $('#modalRekomendasiLanjutan').modal('hide');
                        getDataRKM();
                    }
                },
                error: function(xhr) {
                    // Jika validasi gagal (422), tampilkan pesan errornya
                    var errors = xhr.responseJSON.errors;
                    if (errors && errors.rekomendasi) {
                        Swal.fire('Error!', 'Mohon pilih minimal satu materi.', 'error');
                    } else {
                        Swal.fire('Error!', 'Gagal menyimpan data.', 'error');
                    }
                }
            });
        });

        $('#modalRekomendasiLanjutan').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('input[name="id_rkm"]').remove();
            if ($('#rekomendasi').data('select2')) {
                $('#rekomendasi').select2('destroy');
                $('#keterangan_rekomendasi').val('');
            }
        });
    });

    function getDataRKM() {
        var tahun = $('#tahun').val();
        var bulan = $('#bulan').val();

        $.ajax({
            url: "/rekomendasi-lanjutan/get/" + tahun + "/" + bulan,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                var html = '';
                var jabatan = `{!! auth()->user()->jabatan !!}`.replace(/&amp;/g, "&").trim();
                var listMateriSemua = {!! json_encode($dataMateri) !!};
                response.data.forEach(function(monthData) {
                    monthData.weeksData.forEach(function(weekData) {
                        html += '<div class="card my-1">';
                        html += '<div class="card-body table-responsive">';
                        html += '<h3 class="card-title my-1">Rencana Kelas Mingguan</h3>';
                        moment.locale('id');
                        var startOfWeek = moment(weekData.start);
                        var endOfWeek = startOfWeek.clone().add(4, 'days');
                        html += '<p class="card-title my-1">Periode : ' + moment(startOfWeek).format('DD MMMM YYYY') + ' - ' + moment(endOfWeek).format('DD MMMM YYYY') + '</p>';
                        html += '<table class="table table-responsive table-striped">';
                        html += '<thead><tr>';
                        html += '<th scope="col">No</th>';
                        html += '<th scope="col">Materi</th>';
                        html += '<th scope="col">Tanggal Training</th>';
                        html += '<th scope="col">Perusahaan</th>';
                        html += '<th scope="col">Rekomendasi Pelatihan Selanjutnya</th>';
                        html += '<th scope="col">Keterangan</th>';
                        if (jabatan == 'Education Manager' || jabatan == 'Instruktur') {
                            html += '<th scope="col">Aksi</th>';
                        }
                        html += '</tr></thead><tbody>';

                        if (weekData.data.length === 0) {
                            html += '<tr><td colspan="6" class="text-center">Tidak Ada Kelas Mingguan</td></tr>';
                        } else {
                            weekData.data.forEach(function(rkm, index) {
                            var tanggalText = rkm.tanggal_awal == rkm.tanggal_akhir ?
                                moment(rkm.tanggal_awal).format('DD MMMM YYYY') :
                                moment(rkm.tanggal_awal).format('DD MMMM YYYY') + ' s/d ' + moment(rkm.tanggal_akhir).format('DD MMMM YYYY');

                            var perusahaanText = rkm.perusahaan.map(p => p.nama_perusahaan).join(', ');

                            // --- LOGIKA BARU UNTUAL FORMAT STRING KOMA ---
                            var rekomendasiNamaMateri = '-';
                            var rawIds = '';
                            var ketText = '-';
                            console.log();
                            if (rkm.rekomendasilanjutan) {
                                rawIds = rkm.rekomendasilanjutan.id_materi || ''; // Contoh: "1,2"
                                ketText = rkm.rekomendasilanjutan.keterangan || '-';

                                if (rawIds) {
                                    var arrayIds = rawIds.split(',');
                                    var namaArray = [];
                                    
                                    arrayIds.forEach(function(id) {
                                        // Cari nama materi di listMateriSemua (variable dari PHP)
                                        var materiKetemu = listMateriSemua.find(m => m.id == id.trim());
                                        if (materiKetemu) {
                                            namaArray.push(materiKetemu.nama_materi);
                                        }
                                    });
                                    
                                    rekomendasiNamaMateri = namaArray.length > 0 ? namaArray.join(', ') : '-';
                                }
                            }
                            // --------------------------------------------

                            html += '<tr>';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td>' + rkm.materi.nama_materi + '</td>';
                            html += '<td>' + tanggalText + '</td>';
                            html += '<td>' + perusahaanText + '</td>';
                            // TAMPILKAN NAMA MATERI DAN KETERANGAN
                            html += '<td>' + 
                                        '<strong>' + rekomendasiNamaMateri + '</strong>' + 
                                    '</td>';
                            html += '<td>' + ketText + '</td>';
                            
                            if (jabatan == 'Education Manager' || jabatan == 'Instruktur') {
                                html += '<td>';
                                html += '<div class="btn-group dropup">';
                                html += '<button type="button" class="btn dropdown-toggle text-black" data-bs-toggle="dropdown">Actions</button>';
                                html += '<div class="dropdown-menu">';
                                html += '<a class="dropdown-item js-ajukan-rekomendasi" href="#" ' +
                                    'data-id="' + rkm.id + '" ' +
                                    'data-materi="' + rkm.materi.nama_materi + '" ' +
                                    'data-tanggal="' + tanggalText + '" ' +
                                    'data-perusahaan="' + perusahaanText + '" ' +
                                    'data-keterangan="' + (ketText === '-' ? '' : ketText) + '" ' +
                                    'data-rekomendasi-id="' + (rkm.rekomendasilanjutan ? rkm.rekomendasilanjutan.id : '') + '" ' +
                                    'data-rekomendasi-materi-id="' + rawIds + '" ' + 
                                    'data-bs-toggle="modal" data-bs-target="#modalRekomendasiLanjutan">' +
                                    '<img src="{{ asset("icon/clipboard-primary.svg") }}" class="me-1">Ajukan Rekomendasi</a>';
                                html += '</div></div></td>';
                            }
                            html += '</tr>';
                        });
                        }
                        html += '</tbody></table></div></div>';
                    });
                });

                $('#content').html(html);
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Gagal memuat data. Silakan coba lagi.', 'error');
            }
        });
    }
</script>
@endpush
@endsection