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
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Tunjangan') }}</h3>
                      <div class="card my-2">
                        <div class="card-body">
                            <div class="col-md-12">
                                    <div class="card" style="width: 100%">
                                        <div class="card-body d-flex justify-content-center">
                                            <div class="col-md-4 mx-1">
                                                <label for="tahun" class="form-label">Tahun</label>
                                                <select id="tahun" class="form-select" aria-label="tahun">
                                                    <option disabled>Pilih Tahun</option>
                                                    @php
                                                    $tahun_sekarang = now()->year;
                                                    for ($tahun = 2020; $tahun <= $tahun_sekarang   + 2; $tahun++) {
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
                                                        $bulan_awal = $nama_bulan[$bulan - 1]; // Accessing the array with $bulan - 1
                                                        $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                                        echo "<option value=\"$bulan\" $selected>$bulan_awal</option>";
                                                    }
                                                    @endphp
                                                </select>
                                            </div>                                            
                
                                            <div class="col-md-4 mx-1">
                                                <button type="submit" onclick="fetchTunjanganSaya()" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            <h4 id="tunjangan-title" class="card-title text-center my-1">
                                Tunjangan Anda pada Bulan {{ \Carbon\Carbon::createFromFormat('m', $month - 1)->locale('id_ID')->format('F Y') }}
                            </h4>
                            <div class="d-flex justify-content-end">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('generateTunjanganPDF', [
                                        'id' => auth()->user()->karyawan_id, 
                                        'month' => $month, 
                                        'year' => $year
                                    ]) }}">
                                        Generate PDF
                                    </a>
                                </div>
                                
                            </div>                            
                            <table id="table_tunjangan_saya" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama Tunjangan</th>
                                        <th>Total</th>
                                        {{-- <th>Keterangan</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated here -->
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #006A67; color:white;">
                                        <td>Total Tunjangan:</td>
                                        <td id="total_tunjangan">Rp. 0.00</td>
                                    </tr>
                                    <tr style="background-color: #FF2929; color:white;">
                                        <td>Total Potongan:</td>
                                        <td id="total_potongan">Rp. 0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Total Bersih:</td>
                                        <td id="total_bersih">Rp. 0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                            
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
<script>
    $(document).ready(function () {
        var userRole = '{{ auth()->user()->jabatan}}';
        fetchTunjanganSaya();
    });

    // Format numbers as Rupiah currency
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

    function fetchTunjanganSaya() { 
        if ($.fn.DataTable.isDataTable('#table_tunjangan_saya')) {
            $('#table_tunjangan_saya').DataTable().destroy();
        }
        var tahun = $('#tahun').val()
        var bulan = $('#bulan').val()
        var karyawanId = {{ auth()->user()->karyawan_id }}; // Ensure this is correctly outputted
        console.log(tahun, bulan)
        var bulans = bulan - 1;
        var date = new Date(tahun, bulans - 1);
        var options = { month: 'long', year: 'numeric', locale: 'id-ID' };
        var formattedDate = date.toLocaleDateString('id-ID', options);

        $('#tunjangan-title').text('Tunjangan Anda pada Bulan ' + formattedDate)
        $('#table_tunjangan_saya').DataTable({
            "ajax": {
                "url": "/getTunjanganSaya/" + karyawanId + "/" + bulan + "/" + tahun,
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                },
                "error": function (xhr, error, thrown) {
                    console.error("DataTables error: ", error, thrown);
                    // Optionally, send this error to your server for logging
                    $.post('/logError', { error: error, thrown: thrown });
                }
            },
            "columns": [
                { "data": "jenistunjangan.nama_tunjangan" },
                {
                    "data": "total",
                    "render": function (data, type, row) {
                        return 'Rp. ' + formatRupiah(data.toString()); // Format data as Rupiah
                    }
                },
            ],
            "footerCallback": function (row, data, start, end, display) {
                var totalTunjangan = 0;
                var totalPotongan = 0;

                data.forEach(function(item) {
                    var total = parseFloat(item.total);
                    var keterangan = item.keterangan;

                    if (keterangan === 'Tunjangan') {
                        totalTunjangan += total;
                    } else if (keterangan === 'Potongan') {
                        totalPotongan += total;
                    }
                });

                var totalBersih = totalTunjangan + totalPotongan;

                $('#total_tunjangan').text('Rp. ' + formatRupiah(totalTunjangan.toString()));
                $('#total_potongan').text('Rp. ' + formatRupiah(totalPotongan.toString()));
                $('#total_bersih').text('Rp. ' + formatRupiah(totalBersih.toString()));
            },
        });
    }



</script>
@endpush
@endsection
