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
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                @can('Create Tunjangan')
                    <a href="{{ route('penghitunganTunjangan') }}" class="btn btn-md click-primary mx-4"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Penghitungan Tunjangan Umum Otomatis</a>
                    <a href="{{ route('tunjangan.create') }}" class="btn btn-md click-primary mx-4"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Buat Jenis Tunjangan</a>
                    <a href="{{ route('createManual') }}" class="btn btn-md click-primary mx-4" id=""><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Manual Tunjangan</a>
                @endcan
            </div>
            <div class="card m-4">
                <div class="card-body">
                    <h3 class="card-title text-center my-1">{{ __('Data Tunjangan') }}</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card m-4">
                                    <div class="card-body">
                                        <h3 class="card-title text-center my-1">{{ __('Data Tunjangan Karyawan') }}</h3>
                                        <div class="card-body d-flex justify-content-center">
                                            <div class="col-md-4 mx-1">
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
                                            <div class="col-md-4 mx-1">
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
                                            <div class="col-md-4 mx-1">
                                                <a href="#" class="btn btn-danger" style="margin: 30px 8px 4px 8px;" id="exportpdf" onclick="exportPDF()">Export to PDF</a>
                                                <a href="#" class="btn btn-success" style="margin: 30px 8px 4px 8px;" id="exportexcel" onclick="exportExcel()">Export to Excel</a>
                                            </div>
                                        </div>
                                        <table class="table table-bordered" id="tablekaryawan">
                                            <thead>
                                                <th scope="col">No</th>
                                                <th scope="col">Nama Karyawan</th>
                                                <th scope="col">Divisi</th>
                                                <th scope="col">Detail Absensi</th>
                                            </thead>
                                            <tbody>
                                                <!-- Data Karyawan akan diisi di sini -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card m-4">
                                    <div class="card-body">
                                            <h3 class="card-title text-center my-1" id="detailTunjangan">{{ __('Detail Tunjangan Karyawan') }}</h3>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="card m-4">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <form method="POST" action="{{ route('tunjangan.storeManual') }}" id="form-tunjangan">
                                                                        @csrf
                                                                        <div class="row mb-3">
                                                                            <div class="col-md-12 d-flex justify-content-end">
                                                                                <a href="#" class="btn click-primary mx-4" id="generateTunjangan" onclick="generateTunjanganUmum()">Generate Tunjangan Umum Karyawan</a>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row mb-3">
                                                                            <label for="karyawan_id" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                                                                            <div class="col-md-6">
                                                                                <input type="text" readonly class="form-control" name="nama_karyawan" id="nama_karyawan">
                                                                                <input type="hidden" class="form-control" name="karyawan_id" id="karyawan_id">
                                                                                <input type="hidden" class="form-control" name="jumlah_absen" id="jumlah_absen">
                                                                                <input type="hidden" class="form-control" name="keterlambatan" id="keterlambatan">
                                                                                <input type="hidden" class="form-control" name="bulan_tunjangan" id="bulan_tunjangan">
                                                                                <input type="hidden" class="form-control" name="tahun_tunjangan" id="tahun_tunjangan">
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div id="tunjanganContainer">
                                                                            <!-- Data tunjangan akan diisi di sini -->
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <input type="hidden" class="form-control" name="nama_tunjangan" id="nama_tunjangan">
                                                                                <input type="hidden" name="tipe_tunjangan" id="tipe_tunjangan">
                                                                                <div class="row mb-3">
                                                                                    <label for="nilai" class="col-md-4 col-form-label text-md-start">{{ __('Nilai') }}</label>
                                                                                    <div class="col-md-6">
                                                                                        <div class="input-group mb-3">
                                                                                            <span class="input-group-text">Rp.</span>
                                                                                            <input type="text" class="form-control @error('min_harga_pelatihan') is-invalid @enderror" name="nilai" id="nilai" placeholder="Nilai Tunjangan">
                                                                                        </div>
                                                                                        @error('nilai')
                                                                                            <span class="invalid-feedback" role="alert">
                                                                                                <strong>{{ $message }}</strong>
                                                                                            </span>
                                                                                        @enderror
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row mb-3">
                                                                                    <label for="kelipatan" class="col-md-4 col-form-label text-md-start">{{ __('Satuan') }}</label>
                                                                                    <div class="col-md-6">
                                                                                        <input type="text" name="kelipatan" id="kelipatan" class="form-control">
                                                                                        @error('kelipatan')
                                                                                            <span class="invalid-feedback" role="alert">
                                                                                                <strong>{{ $message }}</strong>
                                                                                            </span>
                                                                                        @enderror
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row mb-3">
                                                                                    <div class="col-md-12 d-flex justify-content-center">
                                                                                        <button type="button" class="btn btn-success btn-md" id="btn_generate" onclick="generateData()">Generate</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="row" id="keterangan"></div>
                                                                        <div class="row justify-content-center">
                                                                            <div class="col-md-12">
                                                                                <div class="card mt-4">
                                                                                    {{-- <h5 class="card-title text-center">Total Tunjangan</h5> --}}
                                                                                    <div class="card-body">
                                                                                        <table class="table table-bordered">
                                                                                            <thead>
                                                                                                <tr>
                                                                                                    <th>Nama Tunjangan</th>
                                                                                                    <th>Detail</th>
                                                                                                    <th>Nilai</th>
                                                                                                    <th>Total</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody id="dataPreview">
                                                                                                <!-- Hasil dari generateData() akan ditambahkan di sini -->
                                                                                            </tbody>
                                                                                        </table>
                                                                                        <div>
                                                                                            <strong id="total">Total: 0</strong>
                                                                                        </div>
                                                                                        <div class="row mb-0">
                                                                                            <div class="col-md-12 offset-md-8">
                                                                                                <button type="submit" class="btn click-primary" id="submitData" style="padding:8px" onclick="event.preventDefault(); if(confirm('Apakah Anda Yakin?')) { document.getElementById('form-tunjangan').submit(); }">
                                                                                                    {{ __('Simpan') }}
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .nav-tabs {
        display: flex;
        flex-wrap: nowrap; /* Mencegah tab terbungkus ke bawah */
        overflow-x: auto; /* Menambahkan scroll horizontal jika diperlukan */
    }
    
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function () {
        var userRole = '{{ auth()->user()->jabatan}}';
        var divisi = '{{ auth()->user()->karyawan->divisi }}';
        $('#generateTunjangan').attr('disabled', true);
        $('#generateTunjangan').css('pointer-events', 'none'); // Mencegah klik
        $('#generateTunjangan').css('opacity', '0.5'); // Memberi efek tombol tidak aktif
        $('#submitData').attr('disabled', true);
        $('#submitData').css('pointer-events', 'none'); // Mencegah klik
        $('#submitData').css('opacity', '0.5'); // Memberi efek tombol tidak aktif
        var today = new Date();
        var day = today.getDate();
        console.log(day);
        if (day < 10 && day > 1) {
            $('#generateTunjanganBtn').attr('disabled', false);
        }else{
            $('#generateTunjanganBtn').attr('disabled', true);
            $('#generateTunjanganBtn').css('pointer-events', 'none'); // Mencegah klik
            $('#generateTunjanganBtn').css('opacity', '0.5'); // Memberi efek tombol tidak aktif
        }
        getUserall();


    });

    function getTunjangan(divisi, karyawanId) {
        let url = '';
        url = '/getJenisTunjanganIndex';
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var container = $('#tunjanganContainer');
                // Kosongkan kontainer sebelum menambahkan data baru
                container.empty();
                var data = response.data;
                let html = '';
                html += '<div class="row mb-3">' +
                            '<label for="id_tunjangan" class="col-md-4 col-form-label text-md-start">{{ __('Tunjangan') }}</label>' +
                            '<div class="col-md-6">' +
                            '<select id="id_tunjangan" class="form-select" name="id_tunjangan" autocomplete="id_tunjangan" autofocus>' +
                            '<option value="" selected>Pilih Jenis</option>';
                $.each(data, function(index, item) {
                    let namaTunjanganId = item.nama_tunjangan.replace(/ /g, '_'); // Ganti spasi dengan underscore
                    html += '<option value="'+namaTunjanganId+'" data-nilai="'+item.nilai+'" data-nama="'+item.nama_tunjangan+'" data-tipe="'+item.tipe+'">'+item.nama_tunjangan+'</option>';
                });
                if(divisi === 'Education'){
                    html += '<option value="Education" data-nama="Education">Education</option>'
                };
                html += '</select>' +
                        '</div>' +
                        '</div>';

                // Menambahkan elemen error jika ada
                if (response.error) {
                    html += '<span class="invalid-feedback" role="alert">' +
                                '<strong>' + response.error + '</strong>' +
                            '</span>';
                }

                // Append HTML ke kontainer
                container.append(html);

                // Tambahkan event listener untuk dropdown
                $('#id_tunjangan').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    var nilai = selectedOption.data('nilai'); // Ambil data-nilai
                    var tipe = selectedOption.data('tipe'); // Ambil data-tipe
                    var nama = selectedOption.data('nama'); // Ambil data-tipe
                    console.log(nama);
                    if(nama === 'Education'){
                        tunjanganEdu(karyawanId);
                    }else{
                        $('#nilai').val(nilai); // Mengisi input nilai
                        $('#tipe_tunjangan').val(tipe); // Mengisi input tipe_tunjangan
                        $('#nama_tunjangan').val(nama); // Mengisi input tipe_tunjangan
                    }
                    
                });
            },
            error: function(xhr, status, error) {
                console.error("Terjadi kesalahan: " + error);
            }
        });
    }
    function tunjanganEdu(karyawanId){
        var bulan = $('#bulan_tunjangan').val();
        var tahun = $('#tahun_tunjangan').val();
        console.log(bulan, tahun, karyawanId);
        $.ajax({
            type: "GET",
            url: "{{ route('getTunjanganEdu', ['id' => ':id', 'month' => ':month', 'year' => ':year']) }}".replace(':id', karyawanId).replace(':month', bulan).replace(':year', tahun),
            dataType: "json",
            success: function(data) {
                console.log(data.total_tunjangan)
                $('#tipe_tunjangan').val('Tunjangan'); // Mengisi input tipe_tunjangan
                $('#nilai').val(data.total_tunjangan);
                $('#nama_tunjangan').val('Education'); // Mengisi input tipe_tunjangan
                $('#kelipatan').val('1'); // Mengisi input tipe_tunjangan

            }
        });

    }
    function getDataTunjangan(karyawanId) {
        moment.locale('id');
        var tahun = $("#tahun").val();
        var bulan = $("#bulan").val();
        var bulans;

        // Logika untuk menentukan bulan dan tahun yang benar
        if (bulan == 1) { // Pastikan menggunakan '==' untuk perbandingan
            bulans = 12; // Bulan Desember
            tahun -= 1; // Tahun sebelumnya
        } else {
            bulans = bulan - 1; // Bulan sebelumnya
        }
        console.log(bulans, tahun)
        var idBulan = moment(bulans, 'M').format('MMMM'); // Format bulan untuk ditampilkan
        $('#keterangan').empty();
        $('#detailTunjangan').empty();
        $('#detailTunjangan').text('Detail Tunjangan Karyawan Bulan ' + idBulan + ' ' + tahun); // Menampilkan bulan dan tahun
        $('#bulan_tunjangan').val(bulans);
        $('#tahun_tunjangan').val(tahun);

        $.ajax({
            type: "GET",
            url: "{{ route('jumlahAbsensi', ['id_karyawan' => ':karyawan_id', 'bulan' => ':bulan', 'tahun' => ':tahun']) }}".replace(':karyawan_id', karyawanId).replace(':bulan', bulans).replace(':tahun', tahun),
            dataType: "json",
            success: function(response) {
                var data = response.data;
                var divisi = data.karyawan.divisi;
                console.log(divisi); 

                getTunjangan(divisi, karyawanId);
                if(response.success == false){
                    alert(response.message);
                    $('#jumlah_absen').val('0');
                    $('#keterlambatan').val('Tidak Pernah Terlambat');
                    $('#nama_karyawan').val('Error');
                    $('#karyawan_id').val(karyawanId);
                    $('#keterangan').empty();
                    $('#generateTunjangan').attr('disabled', true); // Disable button if there's an error
                } else {
                    $('#jumlah_absen').val(data.jumlah_absensi !== null && data.jumlah_absensi !== undefined ? data.jumlah_absensi : 0);
                    $('#nama_karyawan').val(data.karyawan.nama_lengkap);
                    $('#karyawan_id').val(data.karyawan.id);
                    $('#keterlambatan').val(data.keterangan);

                    var listItem = '<div class="col-md-12">' + 
                    '<h6>Keterangan</h6>' +
                    '<ul>' + 
                        '<li>Jumlah absen Pada Bulan Ini (Sudah Termasuk Pengurangan Cuti lebih dari 3 hari): ' + data.jumlah_absensi + '</li>' + 
                        '<li>Keterlambatan Pada Bulan Ini: ' + data.keterangan + '</li>' + 
                        '<li>Cuti Pada Bulan Ini:</li>' +
                        '<ul>'; // Membuka <ul> untuk list cuti

                        // Iterasi melalui data.cutikaryawan
                        data.cutikaryawan.forEach(function(cuti) {
                            listItem += '<li>' +
                                        'Tipe: ' + cuti.tipe + ', ' +
                                        'Tanggal Awal: ' + cuti.tanggal_awal + ', ' +
                                        'Tanggal Akhir: ' + cuti.tanggal_akhir + ', ' +
                                        'Durasi: ' + cuti.durasi + ' hari, ' +
                                        'Alasan: ' + (cuti.alasan || 'Tidak ada alasan') + 
                                        '</li>';
                        });

                        listItem += '</ul>' + // Menutup <ul> cuti
                                    '</ul>' +
                                    '</div>';
                        // Menambahkan listItem ke dalam elemen dengan id "keterangan"
                        $('#keterangan').append(listItem);

                        // Aktifkan tombol setelah data berhasil diambil
                        $('#generateTunjangan').attr('disabled', false);
                        $('#generateTunjangan').css('opacity', '1.5'); 
                        $('#generateTunjangan').css('pointer-events', 'auto');


                }
            },
            error: function(xhr, status, error) {
                console.log("Terjadi kesalahan: " + error);
                $('#generateTunjangan').attr('disabled', true); // Disable button on error
            }
        });
        cekdata(karyawanId, bulans, tahun);
        $('#dataPreview').empty(); // Menambahkan ke elemen yang sama
        updateTotal();

    }
    function cekdata(karyawanId, bulan, tahun) {
        var url = "{{ route('getTunjanganSayaGenerate', ['id' => ':karyawan_id', 'month' => ':bulan', 'year' => ':tahun']) }}";
        url = url.replace(':karyawan_id', karyawanId).replace(':bulan', bulan).replace(':tahun', tahun);
        console.log(bulan, tahun);
        
        $.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            success: function(response) {
                // Cek apakah data ada
                if (!response.data || response.data.length === 0) {
                    $('#generateTunjangan').attr('disabled', false);
                    $('#generateTunjangan').css('opacity', '1.5'); 
                    $('#generateTunjangan').css('pointer-events', 'auto');
                    return; // Hentikan eksekusi lebih lanjut
                }

                var data = response.data;
                console.log(data);
                data.forEach(function(item) {
                    // console.log(item.jenistunjangan.nama_tunjangan);
                    var nama_tunjangan = item.jenistunjangan.nama_tunjangan;
                    var hitung = item.total/item.jenistunjangan.nilai;
                    var kelipatan = (item.jenistunjangan.hitung === 'Perhari') ? $('#jumlah_absen').val() : hitung;
                    if(nama_tunjangan == 'Education'){
                        var listItem =  '<tr>' +
                        '<td>' + nama_tunjangan + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>1</td>' +
                        '<td> = ' + formatRupiah(item.total) + 
                        '<input type="hidden" name="dataTunjangan[' + item.jenistunjangan.nama_tunjangan + ']" value="' + item.total + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
                    }else if(item.jenistunjangan.hitung === 'Perbulan'){
                        var listItem =  '<tr>' +
                        '<td>' + nama_tunjangan + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>1 x '+item.total+'</td>' +
                        '<td> = ' + formatRupiah(item.total) + 
                        '<input type="hidden" name="dataTunjangan[' + item.jenistunjangan.nama_tunjangan + ']" value="' + item.total + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
                    }else{
                        var listItem =  '<tr>' +
                        '<td>' + item.jenistunjangan.nama_tunjangan + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>' + kelipatan + ' x ' + formatRupiah(item.jenistunjangan.nilai) + '</td>' +
                        '<td> = ' + formatRupiah(item.total) + 
                        '<input type="hidden" name="dataTunjangan[' + item.jenistunjangan.nama_tunjangan + ']" value="' + item.total + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
                    }

                    $('#dataPreview').append(listItem); // Menambahkan ke elemen yang sama
                });

                $('#generateTunjangan').attr('disabled', true);
                $('#generateTunjangan').css('pointer-events', 'none'); // Mencegah klik
                $('#generateTunjangan').css('opacity', '0.5'); // Memberi efek tombol tidak aktif
                updateTotal();
                var nama_karyawan = $('#nama_karyawan').val();
                alert('Tunjangan untuk karyawan '+ nama_karyawan +' sudah digenerate sebelumya. Hati-hati pada saat mengubah atau menghapus data');
            },
            error: function(xhr, status, error) {
                console.log("Terjadi kesalahan: " + error);
            }
        });
    }
    function exportPDF(){
      var tahun = $('#tahun').val();  
      var bulan = $('#bulan').val(); 
      if (bulan && tahun) {
            // Membangun URL untuk route
            var url = "{{ url('tunjanganExportPDF') }}/" + bulan + "/" + tahun;
            window.location.href = url; // Mengarahkan ke URL
        } else {
            alert('Silakan pilih bulan dan tahun terlebih dahulu.');
        }
    }
    function exportExcel(){
      var tahun = $('#tahun').val();  
      var bulan = $('#bulan').val(); 
      if (bulan && tahun) {
            // Membangun URL untuk route
            var url = "{{ url('tunjanganExportExcel') }}/" + bulan + "/" + tahun;
            window.location.href = url; // Mengarahkan ke URL
        } else {
            alert('Silakan pilih bulan dan tahun terlebih dahulu.');
        }
    }

    function formatRupiah(angka, prefix) {
        // Mengecek apakah angka negatif
        var isNegative = angka < 0;

        // Mengubah angka menjadi string dan menghilangkan karakter selain angka dan koma
        var number_string = Math.abs(angka).toString(); // Menggunakan Math.abs untuk menghindari angka negatif pada proses formatting

        // Memisahkan bagian bulat dan desimal
        var split = number_string.split('.');
        var bulat = split[0];
        var desimal = split[1] || ''; // Jika tidak ada bagian desimal, isi dengan string kosong

        // Memformat bagian bulat
        var sisa = bulat.length % 3;
        var rupiah = bulat.substr(0, sisa);
        var ribuan = bulat.substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        // Memformat bagian desimal (maksimal 2 angka di belakang koma)
        desimal = desimal.substr(0, 2);
        rupiah = rupiah + (desimal ? ',' + desimal : '');

        // Tambahkan prefix jika diperlukan
        rupiah = prefix ? prefix + ' ' + rupiah : rupiah;

        // Menambahkan tanda minus (-) jika angka negatif
        if (isNegative) {
            rupiah = '-' + rupiah;
        }

        return rupiah;
    }
    function getUserall() { 
        if ($.fn.DataTable.isDataTable('#tablekaryawan')) {
            $('#tablekaryawan').DataTable().destroy();
        }
        var tableKaryawan = $('#tablekaryawan').DataTable({
            "ajax": {
                "url": "{{ route('getUserall') }}",
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                },
                "complete": function () {
                    // console.log(divisi);
                    $('#loadingModal').modal('hide');
                }
            },
            "columns": [
                {"data": "id"},
                {"data": "karyawan.nama_lengkap"},
                {"data": "karyawan.divisi"}, // Menambahkan kolom divisi
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button type="button" onclick="getDataTunjangan(' + row.id + ')" class="btn click-primary">Detail Tunjangan</button>';
                    },
                }
            ],
        });
    }
    var totalTunjangan = 0.0; // Inisialisasi total tunjangan sebagai float
    var totalPotongan = 0.0; // Inisialisasi total potongan sebagai float

    function generateData() {
        var tunjanganName = $('#id_tunjangan').val();
        var tunjanganValue = parseFloat($('#nilai').val()); // Mengubah nilai menjadi float
        var kelipatan = parseFloat($('#kelipatan').val()); // Mengubah kelipatan menjadi float
        var tipeTunjangan = $('#tipe_tunjangan').val();
        var nama = $('#nama_tunjangan').val(); 
        console.log(tunjanganName, tunjanganValue, kelipatan, tipeTunjangan, nama);

        var totalTunjanganPerItem = kelipatan * tunjanganValue;

        if (tipeTunjangan === 'Potongan') {
            totalPotongan += totalTunjanganPerItem; // Tambah potongan
        } else {
            totalTunjangan += totalTunjanganPerItem; // Tambah tunjangan
        }

        if(nama == 'Education'){
            var listItem =  '<tr>' +
                        '<td>' + nama + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>1</td>' +
                        '<td> = ' + formatRupiah(totalTunjanganPerItem) + 
                        '<input type="hidden" name="dataTunjangan[' + tunjanganName + ']" value="' + totalTunjanganPerItem + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
        }else{
            var listItem =  '<tr>' +
                        '<td>' + nama + '</td>' +
                        '<td>Jumlah Absensi / Satuan: </td>' +
                        '<td>' + kelipatan + ' x ' + formatRupiah(tunjanganValue) + '</td>' +
                        '<td> = ' + formatRupiah(totalTunjanganPerItem) + 
                        '<input type="hidden" name="dataTunjangan[' + tunjanganName + ']" value="' + totalTunjanganPerItem + '">' +
                        '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>' +
                        '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                        '</td>' +
                        '</tr>';
        }
        

        $('#dataPreview').append(listItem); // Menambahkan ke elemen yang sama

        updateTotal(); // Memperbarui total setelah menambahkan data
    }

    // Function to calculate total hours between two times LEMBUR
    function calculateTotalHours(jamMulai, jamSelesai) {
        if (jamMulai && jamSelesai) {
            var start = moment(jamMulai, "HH:mm");
            var end = moment(jamSelesai, "HH:mm");
            var duration = moment.duration(end.diff(start));
            return duration.asHours().toFixed(2); // Return total hours in decimal format
        }
        return '0.00'; // Return 0 if no start or end time
    }
    function cekLembur(id) {
        var bulan = $('#bulan').val() - 1;
        var tahun = $('#tahun').val();
        var url = '/getOvertimeLemburByKaryawan/' + id + '/' + bulan + '/' + tahun;

        return new Promise(function(resolve, reject) {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var data = response.data;
                    var totalLembur = 0;

                    if (data.length > 0) {
                        data.forEach(function(item) {
                            if(item.hitunglembur.approval_gm == '1'){
                                var jamLembur = calculateTotalHours(item.jam_mulai, item.jam_selesai);
                                var nilaiPerJam = item.hitunglembur.nilai_lembur;
                                totalLembur += jamLembur * nilaiPerJam;
                            }
                            
                        });
                    }

                    resolve(totalLembur); // Selesaikan Promise dengan totalLembur
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching data:", error);
                    reject(0); // Jika error, kembalikan 0
                }
            });
        });
    }


    function generateTunjanganUmum() {
        var url = '/getJenisTunjanganUmum';
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var data = response.data;
                console.log(data);
                $.each(data, function(index, item) {
                    var tunjanganName = item.nama_tunjangan;
                    var tunjanganValue = parseFloat(item.nilai); // Mengubah nilai menjadi float
                    var keterlambatan = $('#keterlambatan').val();
                    var id_karyawan = $('#karyawan_id').val();
                    if (tunjanganName == "Lembur") {
                    cekLembur(id_karyawan).then(function(lembur) {
                        console.log(lembur);
                        item.nilai = lembur; // Memperbarui nilai lembur
                        
                        // Panggil fungsi update UI setelah mendapatkan nilai lembur
                        updateTunjanganRow(tunjanganName, lembur);
                    }).catch(function(error) {
                        console.error("Gagal mendapatkan data lembur:", error);
                    });

                    return; // Hindari proses lebih lanjut sebelum nilai lembur diperbarui
                }

                    // Cek kondisi untuk mengecualikan 'Absensi' jika keterlambatan > 15 menit
                    if (keterlambatan === 'Keterlambatan > 15 Menit' && tunjanganName === 'Absensi') {
                        return; // Lewati iterasi ini
                    }

                    var kelipatan = (tunjanganName === 'Absensi' || tunjanganName === 'Lembur') ? 1 : $('#jumlah_absen').val(); 
                    var tipeTunjangan = item.tipe;
                    // console.log(tunjanganName, tunjanganValue, kelipatan, tipeTunjangan);

                    // Hitung total tunjangan per item
                    var totalTunjanganPerItem = kelipatan * tunjanganValue; // Mengalikan dengan kelipatan

                    // Tambahkan ke total potongan atau tunjangan
                    if (tipeTunjangan === 'Potongan') {
                        totalPotongan += totalTunjanganPerItem; // Tambah potongan
                    } else {
                        totalTunjangan += totalTunjanganPerItem; // Tambah tunjangan
                    }

                    var listItem =  '<tr>' +
                                    '<td>' + tunjanganName + '</td>' +
                                    '<td>Jumlah Absensi / Satuan: </td>' +
                                    '<td>' + kelipatan + ' x ' + formatRupiah(tunjanganValue) + '</td>' +
                                    '<td> = ' + formatRupiah(totalTunjanganPerItem) + 
                                    '<input type="hidden" name="dataTunjangan[' + tunjanganName + ']" value="' + totalTunjanganPerItem + '">' +
                                    '</td>' +
                                    '<td>' +
                                    '<button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>' +
                                    '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                                    '</td>' +
                                    '</tr>';

                    $('#dataPreview').append(listItem); // Menambahkan ke elemen yang sama
                });

                updateTotal(); // Memperbarui total setelah menambahkan data

                // Nonaktifkan tombol generateTunjangan
                $('#generateTunjangan').attr('disabled', true);
                $('#generateTunjangan').css('pointer-events', 'none'); // Mencegah klik
                $('#generateTunjangan').css('opacity', '0.5'); // Memberi efek tombol tidak aktif
            },
            error: function(xhr, status, error) {
                console.error("Terjadi kesalahan: " + error);
            }
        });
    }
    function updateTunjanganRow(nama, nilai) {
        var kelipatan = (nama === 'Absensi' || nama === 'Lembur') ? 1 : $('#jumlah_absen').val();
        var totalTunjanganPerItem = kelipatan * nilai;

        var listItem = '<tr>' +
                    '<td>' + nama + '</td>' +
                    '<td>Jumlah Absensi / Satuan: </td>' +
                    '<td>' + kelipatan + ' x ' + formatRupiah(nilai) + '</td>' +
                    '<td> = ' + formatRupiah(totalTunjanganPerItem) + 
                    '<input type="hidden" name="dataTunjangan[' + nama + ']" value="' + totalTunjanganPerItem + '">' +
                    '</td>' +
                    '<td>' +
                    '<button type="button" class="btn btn-warning btn-sm" onclick="editRow(this)">Edit</button>' +
                    '<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Hapus</button>' +
                    '</td>' +
                    '</tr>';

        $('#dataPreview').append(listItem);
        updateTotal();
    }
    function updateTotal() {
        totalTunjangan = 0.0; // Reset total tunjangan
        totalPotongan = 0.0; // Reset total potongan

        // Iterasi melalui setiap baris di tabel untuk menghitung total
        $('#dataPreview tr').each(function() {
            var totalPerItem = parseFloat($(this).find('input[type="hidden"]').val()); // Ambil nilai dari input tersembunyi
            var tipeTunjangan = $(this).find('td:eq(0)').text(); // Ambil nama tunjangan

            if (totalPerItem < 0) {
                totalPotongan += totalPerItem; // Tambah potongan
            } else {
                totalTunjangan += totalPerItem; // Tambah tunjangan
            }
        });

        var totalAkhir = (totalTunjangan + totalPotongan);
        console.log(totalAkhir);
        $('#submitData').attr('disabled', false);
        $('#submitData').css('pointer-events', 'auto'); // Mencegah klik
        $('#submitData').css('opacity', '1');
        $('#total').text('Total Tunjangan: ' + formatRupiah(totalTunjangan) + ' | Total Potongan: ' + formatRupiah(totalPotongan) + ' | Total Bersih: ' + formatRupiah(totalAkhir));
    }

    function editRow(button) {
        var row = $(button).closest('tr');
        var tunjanganName = row.find('td:eq(0)').text();
        var kelipatan = row.find('td:eq(2)').text().split(' x ')[0];
        var nilai = row.find('td:eq(2)').text().split(' x ')[1].replace('Rp. ', '').replace('.', '').trim(); // Menghapus format Rp. dan titik

        // Ganti teks dengan input form
        row.find('td:eq(0)').html('<input type="text" class="form-control nilai" value="' + tunjanganName + '" />');
        row.find('td:eq(2)').html('<input type="number" class="form-control kelipatan" value="' + kelipatan + '" /> x <input type="text" class="form-control" value="' + nilai + '" /> <button type="button" class="btn btn-sm btn-primary" onclick="ambilNilaiAbsensi(this)">Ambil nilai Absensi</button>');
        
        // Ganti tombol Edit dengan tombol Simpan
        $(button).text('Simpan').attr('onclick', 'saveRow(this)');
    }

    function ambilNilaiAbsensi(button) {
        var row = $(button).closest('tr');
        var tunjanganName = row.find('.nilai').val(); // Ambil nama tunjangan

        // Cek apakah tunjangan yang diedit adalah 'Absensi'
        if (tunjanganName === 'Absensi') {
            // var keterlambatan = $('#keterlambatan').val();
            var keterlambatan = 'Terlambat > 15 Menit';
            if(keterlambatan === 'Terlambat > 15 Menit'){
                row.find('.kelipatan').val(0);
            }else{
                row.find('.kelipatan').val(1);
            }
        } else {
            var jumlahAbsensi = $('#jumlah_absen').val(); // Ambil nilai dari input jumlah_absen
            row.find('.kelipatan').val(jumlahAbsensi); // Set nilai kelipatan dengan nilai dari jumlah_absen
        }

        console.log(tunjanganName);
    }


    function saveRow(button) {
        var row = $(button).closest('tr');
        var tunjanganName = row.find('input[type="text"]').eq(0).val();
        var kelipatan = row.find('input[type="number"]').val();
        var nilai = row.find('input[type="text"]').eq(1).val();

        // Hitung total baru
        var totalTunjanganPerItem = kelipatan * parseFloat(nilai.replace(/\./g, '').replace(',', '.')); // Menghitung total

        // Update baris dengan nilai baru
        row.find('td:eq(0)').text(tunjanganName);
        row.find('td:eq(2)').html(kelipatan + ' x ' + formatRupiah(nilai));
        row.find('td:eq(3)').html(' = ' + formatRupiah(totalTunjanganPerItem) + 
            '<input type="hidden" name="dataTunjangan[' + tunjanganName + ']" value="' + totalTunjanganPerItem + '">');

        // Ganti tombol Simpan kembali menjadi tombol Edit
        $(button).text('Edit').attr('onclick', 'editRow(this)');

        // Perbarui total
        updateTotal();
    }

    function deleteRow(button) {
        var row = $(button).closest('tr');
        var tunjanganName = row.find('td:eq(0)').text();
        // Buat input tersembunyi untuk menyimpan data yang dihapus
        var deletedDataInput = '<input type="hidden" name="deletedata[]" value="' + tunjanganName + '">';

        // Tambahkan input tersembunyi ke form
        $('#form-tunjangan').append(deletedDataInput); // Ganti '#form-tunjangan' dengan ID form yang sesuai

        // Hapus baris dari tabel
        row.remove();

        // Perbarui total setelah menghapus baris
        updateTotal();

        // Cek apakah ada nilai tertentu di deletedata[]
        checkDeletedData();
    }

    function checkDeletedData() {
        // Ambil semua nilai dari input deletedata[]
        var deletedData = $('input[name="deletedata[]"]').map(function() {
            return $(this).val();
        }).get();

        // Cek apakah ada nilai "makan", "transport", atau "absensi"
        var hasRequiredValues = deletedData.includes('Makan') || deletedData.includes('Transport') || deletedData.includes('Absensi');

        // Enable atau disable tombol generateTunjangan
        if (hasRequiredValues) {
            $('#generateTunjangan').attr('disabled', false);
            $('#generateTunjangan').css('pointer-events', 'auto'); // Mencegah klik
            $('#generateTunjangan').css('opacity', '1'); // Memberi efek tombol tidak aktif
        } else {
            $('#generateTunjangan').attr('disabled', true);
            $('#generateTunjangan').css('pointer-events', 'none'); // Mencegah klik
            $('#generateTunjangan').css('opacity', '0.5'); // Memberi efek tombol tidak aktif
        }
    }

</script>
@endpush
@endsection
