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

    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="approveForm" method="POST">
                        @csrf
                        @method('PUT')
                        <p>Apakah Disetujui?</p>
                        <div id="manager-row">
                            <div class="btn-group" role="group" aria-label="Approval Options">
                                <input type="radio" class="btn-check" name="approval" id="approveYes" value="1" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="approveYes" onclick="toggleAlasanManager(false)">Disetujui</label>
        
                                <input type="radio" class="btn-check" name="approval" id="approveNo" value="2" autocomplete="off">
                                <label class="btn btn-outline-danger" for="approveNo" onclick="toggleAlasanManager(true)">Revisi</label>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ajukanModal" tabindex="-1" aria-labelledby="exampleajukanModal" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5 class="modal-title" id="exampleajukanModal">Rekap</h5>
                    </div>
                </div>
                <div class="modal-body" style="overflow-y: scroll;">
                    <form method="POST" action="#" id="ajukanForm">
                        @csrf
                        @method('PUT')
                        <div class="mb-3 border-bottom" id="">
                            <div class="row mb-3">
                                <label for="level" class="col-md-4 col-form-label text-md-start">{{ __('Level') }}</label>
                                <div class="col-md-6">
                                    <input id="level" readonly type="text" placeholder="Level" class="form-control" name="level" autocomplete="level" autofocus>
                                    <input type="hidden" name="approval" value="99">
                                    @error('level')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="durasi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi') }}</label>
                                <div class="col-md-6">
                                    <input id="durasi" readonly type="text" placeholder="Durasi" class="form-control" name="durasi" autocomplete="durasi" autofocus>
                                    @error('durasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                                <div class="col-md-6">
                                    <input id="pax" readonly type="text" placeholder="Pax" class="form-control" name="pax" autocomplete="pax" autofocus>
                                    @error('pax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="feedback" class="col-md-4 col-form-label text-md-start">{{ __('Feedback') }}</label>
                                <div class="col-md-6">
                                    <input id="feedback" readonly type="text" placeholder="feedback" class="form-control" name="feedback" autocomplete="feedback" autofocus>
                                    @error('feedback')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <label for="poin_durasi" class="col-md-4 col-form-label text-md-start">{{ __('Poin Durasi') }}</label>
                                <div class="col-md-6">
                                    <input id="poin_durasi" readonly type="text" placeholder="Poin Durasi" class="form-control" name="poin_durasi" autocomplete="poin_durasi" autofocus>
                                    @error('poin_durasi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="poin_pax" class="col-md-4 col-form-label text-md-start">{{ __('Poin Pax') }}</label>
                                <div class="col-md-6">
                                    <input id="poin_pax" readonly type="text" placeholder="Poin Pax" class="form-control" name="poin_pax" autocomplete="poin_pax" autofocus>
                                    @error('poin_pax')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <label for="tunjangan_feedback" class="col-md-4 col-form-label text-md-start">{{ __('Tunjangan Feedback') }}</label>
                                <div class="col-md-6">
                                    <input id="tunjangan_feedback" readonly type="text" placeholder="Tunjangan Feedback" class="form-control" name="tunjangan_feedback" autocomplete="tunjangan_feedback" autofocus>
                                    @error('tunjangan_feedback')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <label for="total_tunjangan" class="col-md-4 col-form-label text-md-start">{{ __('Total Tunjangan') }}</label>
                                <div class="col-md-6">
                                    <input id="total_tunjangan" readonly type="text" placeholder="Total Tunjangan" class="form-control" name="total_tunjangan" autocomplete="total_tunjangan" autofocus>
                                    @error('total_tunjangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary" id="btneditsubmit">
                                    {{ __('Simpan') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailmodalRekap" tabindex="-1" aria-labelledby="exampledetailModalRekap" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5 class="modal-title" id="exampleeditModalRekap">Detail</h5>
                    </div>
                </div>
                <div class="modal-body" style="overflow-y: scroll;">
                    {{-- <form id="editRekapForm" method="POST" action="">
                        @csrf
                        @method('PUT') --}}
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Instruktur</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="instruktur_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Nama Materi</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="nama_materi_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>RKM</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <a href="#" target="_blank" id="linkRKM_edit" class="btn btn-sm btn-primary">Link RKM</a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Feedback</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="feedback_inst_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Pax</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="pax_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Durasi</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="durasi_inst_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Tanggal Awal</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="tanggal_awal_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Tanggal Akhir</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="tanggal_akhir_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Metode Kelas</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="metode_kelas_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Event</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="event_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Level</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="level_edit"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <p>Keterangan</p><p id="titikdua"> :</p>
                            </div>
                            <div class="col-md-1 col-sm-1 col-xs-1">
                                <p>:</p>
                            </div>
                            <div class="col-md-7 col-sm-7 col-xs-7">
                                <p id="keterangan_edit"></p>
                            </div>
                        </div>
                        
                    {{-- </form> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Tunjangan Instruktur') }}</h3>
                    <div class="d-flex justify-content-center mb-3">
                        <div class="col-md-3 mx-1">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select id="tahun" class="form-select" aria-label="tahun">
                                <option disabled>Pilih Tahun</option>
                                @php
                                $tahun_sekarang = now()->year;
                                for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                    $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                    echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-3 mx-1">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select id="bulan" class="form-select" aria-label="bulan">
                                <option disabled>Pilih Bulan</option>
                                @php
                                $bulan_sekarang = now()->month;
                                $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                for ($bulan = 1; $bulan <= 12; $bulan++) {
                                    $bulan_awal = $nama_bulan[$bulan - 1];
                                    $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                    echo "<option value=\"$bulan\" $selected>$bulan_awal</option>";
                                }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-3 mx-1">
                            <button type="button" id="cekdatas" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                            <a href="{{ route('nilaifeedbackexport', [$tahun_sekarang, $bulan_sekarang]) }}" id="export-link" target="_blank" class="btn click-primary" style="margin-top: 37px">Export to Excel</a>
                        </div>
                    </div>
                        <div class="card">
                            <div class="card-body table-responsive">
                                        <table class="table table-striped" id="mengajartable">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Nama Materi</th>
                                                    <th scope="col">Instruktur</th>
                                                    <th scope="col">Kode Karyawan</th>
                                                    <th scope="col">Durasi</th>
                                                    <th scope="col">Pax</th>
                                                    <th scope="col">Feedback</th>
                                                    <th scope="col">Metode Kelas</th>
                                                    <th scope="col">Poin Durasi</th>
                                                    <th scope="col">Poin Pax</th>
                                                    <th scope="col">Tunjangan Feedback</th>
                                                    <th scope="col">Total Tunjangan</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Detail</th>
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

    @media screen and (min-width: 769px) {
                /* CSS untuk layar web */
                    #titikdua {
                        display: none; /* Menyembunyikan titikdua pada layar web */
                    }
                }
                @media screen and (max-width: 768px) {
                    #titikdua {
                        display: flex; /* Menampilkan titikdua */
                    }
                    .card {
                        padding: 15px;
                        max-width: 100%;
                    }

                    .card-body .row {
                        margin-bottom: 10px;
                    }

                    .col-xs-4, .col-sm-4{
                        margin :0 !important;
                        display: flex;
                    }

                    .col-xs-1 {
                        display: none;
                    }

                    .col-xs-7 {
                        width: 100%;
                        text-align: left;
                    }
                }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script>
    $(document).ready(function(){
        tableInstruktur()
        // $('#ajukanModal').modal('show');
        $('#cekdatas').click(function() {
            tableInstruktur()
        });
    });
    function removeRupiahFormat(angka) {
        return angka.replace(/[Rp.\s]/g, '').replace(/,/g, '.');
    }
    function formatRupiah(angka, prefix) {
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }
    function approvalModal(id) {
        $('#approveModal').modal('show');
        $('#approveForm').attr('action', "{{ route('tunjanganEducation.update', ':id') }}".replace(':id', id));
    }
    function detailTunjanganEdu (id){
        $('#detailmodalRekap').modal('show');
        $.ajax({
            url: "{{ route('editMengajarInstruktur', ['id' => ':id']) }}".replace(':id', id),
            type: "GET",
            dataType: "json",
            success: function(data) {
                // console.log(data); // Log data untuk memeriksa struktur
                moment.locale('id');
                var tanggalAwal = moment(data.data.tanggal_awal).format('dddd, DD MMMM YYYY');
                var tanggalAkhir = moment(data.data.tanggal_akhir).format('dddd, DD MMMM YYYY');
                $('#tanggal_awal_edit').text(tanggalAwal);
                $('#tanggal_akhir_edit').text(tanggalAkhir);
                $('#durasi_inst_edit').text(data.data.durasi);
                $('#pax_edit').text(data.data.pax);
                $('#level_edit').text(data.data.level);
                $('#keterangan_edit').text(data.data.keterangan);
                $('#feedback_inst_edit').text(data.data.feedback);

                if (data.success && data.data) {
                    var rkmData = data.data.rkm; // Akses objek rkm dari data
                    var instruktur = data.data.instruktur; // Akses objek rkm dari data

                    // Cek apakah tanggal_awal dan tanggal_akhir ada
                    if (rkmData.tanggal_awal && rkmData.tanggal_akhir) {
                        var tanggalAwal = moment(rkmData.tanggal_awal);
                        var tanggalAkhir = moment(rkmData.tanggal_akhir);
                        var durasiRKM = moment.duration(tanggalAkhir.diff(tanggalAwal));
                        var durasi_rkm = durasiRKM.asDays() + 1;
                        var tanggal = moment(rkmData.tanggal_awal).format('D')
                        var lanbu = moment(rkmData.tanggal_awal).format('M')
                        var hunta = moment(rkmData.tanggal_awal).format('Y')
                        if (rkmData.metode_kelas == 'Offline') {
                            var kelas = "off"
                        }else if(rkmData.metode_kelas == 'Inhouse Bandung'){
                            var kelas = "inhb"
                        }else if(rkmData.metode_kelas == 'Inhouse Luar Bandung'){
                            var kelas = "inhlb"
                        }else{
                            var kelas = "vir"
                        }
                        $('#editRekapForm').attr('action', "{{ route('rekapmengajarinstruktur.update', ':id') }}".replace(':id', id));
                        // console.log(tanggalAwal, tanggalAkhir);
                        $('#id_rkm_edit').text(data.data.id_rkm); // Menggunakan id_rkm dari data
                        $('#id_rekap').text(id); // Menggunakan id_rkm dari data
                        $('#instruktur_key_edit').text(instruktur.kode_karyawan);
                        $('#instruktur_edit').text(instruktur.nama_lengkap || ""); // Cek jika instruktur ada
                        $('#nama_materi_edit').text(rkmData.materi.nama_materi || ""); // Cek jika materi ada
                        $('#durasi_materi_edit').text(rkmData.materi.durasi || ""); // Cek jika durasi ada
                        $('#metode_kelas_edit').text(rkmData.metode_kelas);
                        $('#event_edit').text(rkmData.event);
                        $('#durasi_rkm_edit').text(durasi_rkm);
                        $('#linkRKM_edit').prop("href", '/rkm/' + rkmData.materi_key + 'ixb' + tanggal + 'ie' + hunta +'ie' + lanbu + 'ixb' + kelas);
                        $('#linkLevel_edit').prop("href", '/cekLevel/' + rkmData.materi_key);
                        $('#materi_key_edit').text(rkmData.materi_key);
                        // Menentukan cek berdasarkan instruktur_key
                        let cek = null;
                        if (rkmData.instruktur_key === instruktur.kode_karyawan) {
                            cek = 'Instruktur1';
                        } else if (rkmData.instruktur_key2 === instruktur.kode_karyawan) {
                            cek = 'Instruktur2';
                        } else if (rkmData.asisten_key === instruktur.kode_karyawan) {
                            cek = 'Asisten';
                        }
                        // console.log(data.data.id_rkm)
                        // Panggil fungsi generatefeedbackedit dengan cek yang ditentukan
                        generatefeedbackedit(data.data.id_rkm, cek);
                    } else {
                        console.error("Tanggal awal atau tanggal akhir tidak ditemukan");
                    }
                } else {
                    console.error("Data tidak valid atau tidak ditemukan");
                }
            },
            error: function(xhr) {
                console.error("Terjadi kesalahan saat mengambil data:", xhr);
            }
        });
    }
    function ajukanModal(id, level, durasi, pax, feedback, metode_kelas) {
        // $('#ajukanModal').modal('show');
        var route = "{{ route('tunjanganEducation.update', ':id') }}".replace(':id', id);
        $('#ajukanForm').attr('action', route);
        $('#level').val(level);
        $('#durasi').val(durasi);
        $('#pax').val(pax);
        $('#feedback').val(feedback);
        
        if(level === '1'){
            var level_inst = 1;
        }else if(level === '2'){
            var level_inst = 1.5;
        }else if(level === '3'){
            var level_inst = 2;
        }
        if(metode_kelas == 'Inhouse Luar Bandung'){
            var durasi_inst = durasi * 5 * 1.3;
        }else{
            var durasi_inst = durasi * 5;
        }
        var poin_durasi = durasi_inst * level_inst;
        var poin_pax = pax * level_inst;
        if(feedback >= '3.3'){
            if(level === '1'){
                var feedback_inst = 80000;
            }else if(level === '2'){
                var feedback_inst = 100000;
            }else if(level === '3'){
                var feedback_inst = 125000;
            }
        }else{
            var feedback_inst = 0;
        }
        var tunjangan_durasi = poin_durasi * 15000;
        var tunjangan_pax = poin_pax * 15000;
        var total_tunjangan = tunjangan_durasi + tunjangan_pax + feedback_inst;

        $('#poin_durasi').val(poin_durasi);
        $('#poin_pax').val(poin_pax);
        $('#tunjangan_feedback').val(feedback_inst);
        $('#total_tunjangan').val(total_tunjangan);
        $('#ajukanForm').submit();

    }
    function tableInstruktur() {
        var tahun = $("#tahun").val();
        var bulan = $("#bulan").val();
        var idInstruktur = "{{auth()->user()->id_instruktur}}";

        if(idInstruktur == 'AD'){
            var idInstruktur = "";
        }
        // Hancurkan DataTable yang ada jika sudah ada
        if ($.fn.DataTable.isDataTable('#mengajartable')) {
            $('#mengajartable').DataTable().clear().destroy();
        }

        // Inisialisasi DataTable
        $('#mengajartable').DataTable({
            "ajax": {
                "url": "{{ route('getListRekapInstruktur', ['bulan' => ':bulan', 'tahun' => ':tahun']) }}".replace(':bulan', bulan).replace(':tahun', tahun),
                "type": "GET",
                "dataSrc": function (json) {
                    return json.data; // Pastikan ini mengembalikan array data
                },
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').removeAttr('inert');
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').attr('inert', true);
                    }, 1000);
                }
            },
            "columns": [
                {"data": "rkm.materi.nama_materi"},
                // {"data": "id"},
                {"data": "instruktur.nama_lengkap"},
                {
                    "data": "instruktur.kode_karyawan",
                    "visible" : false,
                },
                {"data": "durasi"},
                {"data": "pax"},
                {"data": "feedback"},
                {"data": "rkm.metode_kelas"},
                {"data": "poin_durasi"},
                {"data": "poin_pax"},
                {
                    "data": "tunjangan_feedback",
                    "render": function(data, type, row) {
                        // Memeriksa apakah data adalah null
                        if (data === null) {
                            return '-'; // Tampilkan '-' jika data null
                        }
                        return 'Rp. ' + formatRupiah(data.toString()); // Format target as Rupiah
                    }
                },
                {
                    "data": "total_tunjangan",
                    "render": function(data, type, row) {
                        // Memeriksa apakah data adalah null
                        if (data === null) {
                            return '-'; // Tampilkan '-' jika data null
                        }
                        return 'Rp. ' + formatRupiah(data.toString()); // Format target as Rupiah
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        if (data.status == 'Belum Dihitung') {
                            return '<span class="badge bg-primary" style="color:black;"> Belum Dihitung </span>';
                        } else if (data.status == 'Diajukan') {
                            return '<span class="badge bg-warning"> Diajukan </span>';
                        } else if (data.status == 'Approve') {
                            return '<span class="badge bg-success"> Approved </span>';
                        } else{
                            return '<span class="badge bg-info"> Revisi </span>';
                        }
                    },
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var userRole = '{{ auth()->user()->jabatan}}';
                        var actions = '';
                            actions += '<button type="button" class="btn btn-md btn-success" onclick="detailTunjanganEdu('+ data.id +')" > Detail</button>';
                        return actions;
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var userRole = '{{ auth()->user()->jabatan}}';
                        var actions = '';
                            actions += '@can('Hitung TunjanganEducation')';
                            if(data.status == 'Belum Dihitung'){
                                actions += '<button type="button" class="btn btn-md btn-primary" onclick="ajukanModal('+data.id +', \''+ data.level +'\', \''+ data.durasi +'\', \''+ data.pax +'\', \''+ data.feedback +'\', \''+ data.rkm.metode_kelas +'\')" > Hitung Tunjangan</button>';
                            }else if(data.status == 'Revisi'){
                                actions += '<button type="button" class="btn btn-md btn-primary" onclick="ajukanModal('+data.id +', \''+ data.level +'\', \''+ data.durasi +'\', \''+ data.pax +'\', \''+ data.feedback +'\', \''+ data.rkm.metode_kelas +'\')" > Hitung Tunjangan</button>';
                            }else{
                                actions += '<button type="button" class="btn btn-md btn-primary disabled" onclick="ajukanModal('+data.id +', \''+ data.level +'\', \''+ data.durasi +'\', \''+ data.pax +'\', \''+ data.feedback +'\', \''+ data.rkm.metode_kelas +'\')" > Hitung Tunjangan</button>';
                            }
                            
                            actions += '@endcan';
                            actions += '@can('Approval TunjanganEducation')';
                            // if(data.status == 'Diajukan'){
                            //     actions += '<button type="button" class="btn btn-md btn-primary" onclick="approvalModal('+data.id+')" > Approve</button>';
                            // }else{
                            //     actions += '<button type="button" class="btn btn-md btn-primary disabled" onclick="approvalModal('+data.id+')" > Approve</button>';
                            // }
                            actions += '<button type="button" class="btn btn-md btn-primary" onclick="approvalModal('+data.id+')" > Approve</button>';
                            actions += '@endcan';

                        return actions;
                    }
                }
            ],
            // "columnDefs": [{"targets": [10],}],
            "order": [[10, 'desc']],
            "initComplete": function() {
                this.api().columns(2).search(idInstruktur).draw();
            }
        });
    }
    function updateExportLink() {
        var tahun = $('#tahun').val();
        var bulan = $('#bulan').val();
        var exportLink = $('#export-link');

        // Get current year and month
        var currentYear = new Date().getFullYear();
        var currentMonth = new Date().getMonth() + 1; // getMonth() returns month index (0-11), so we add 1

        // If year or month is not selected, use current year and month
        if (!tahun) {
            tahun = currentYear;
        }
        if (!bulan) {
            bulan = currentMonth;
        }

        exportLink.attr('href', '/tunjanganEduExportExcel/' + bulan + '/' + tahun);
    }

</script>
@endpush
@endsection
