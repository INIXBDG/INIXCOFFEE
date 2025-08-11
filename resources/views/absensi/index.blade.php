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
    <div class="row">
        <div class="col-md-6">
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Absensi Karyawan') }}</h3>
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
                        <div class="col-md-4 mx-1 d-flex">
                            <button type="button" id="exportButton" class="btn click-primary mx-1" style="margin-top: 37px">Export per Karyawan</button>
                            <a href="{{ url('/RekapitulasiAbsenperBulanExport') }}/{{ $tahun_sekarang }}/{{ $bulan_sekarang }}" id="export-link" target="_blank" class="btn click-primary mx-1" style="margin-top: 37px">Export Per bulan</a>
                            <a href="{{ url('/RekapitulasiWaktuKeterlambatanExport') }}/{{ $tahun_sekarang }}" target="_blank" class="btn click-primary mx-1" style="margin-top: 37px">Export per Tahun</a>
                        </div>
                    </div>
                    <table class="table table-bordered" id="tablekaryawan">
                        <thead>
                            <th scope="col">No</th>
                            <th scope="col">Nama Karyawan</th>
                            <th scope="col">Detail Absensi</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
<div class="col-md-6">
    <div class="card m-4">
        <div class="card-body table-responsive">
            <h3 class="card-title text-center my-1">{{ __('Data Absensi Karyawan') }}</h3>
            @can('Create RekapAbsensi')
                <a href="/absensi/create" class="btn btn-primary my-2">Buat Absensi</a>
            @endcan
            <table class="table table-bordered" id="tableAbsen">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Keterangan Masuk</th>
                        <th>Keterangan Pulang</th>
                        <th>Waktu Keterlambatan</th>
                        <th>Izin</th>
                        <th>Foto</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6">Total Keterlambatan:</th>
                        <th colspan="3" id="totalKeterlambatan">-</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


    </div>
</div>
<style>
        /* Kolom ke-7 adalah kolom "Izin" */
    #tableAbsen th:nth-child(7),
    #tableAbsen td:nth-child(7) {
        min-width: 180px; /* Bisa diubah ke 200px atau lebih jika masih sempit */
        white-space: nowrap; /* Agar tidak turun ke baris baru */
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
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/auth()->user()->jabatan/2.17.1/moment-with-locales.min.js"></script> --}}

<script>
    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan}}';
        
        // Initialize tablekaryawan
        var tableKaryawan = $('#tablekaryawan').DataTable({
            "ajax": {
                "url": "{{ route('getUserall') }}", // URL API untuk mengambil data
                "type": "GET",
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
                {"data": "id"},
                {"data": "karyawan.nama_lengkap"},
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button type="button" onclick="getDataAbsensi(' + row.id + ')" class="btn click-primary">Detail Absensi</button>';
                    },
                }
            ]
        });

        // Function to fetch and display attendance data for selected employee, month, and year
        window.getDataAbsensi = function(karyawanId) {
            var tahun = $('#tahun').val();
            var bulan = $('#bulan').val();

            if(!tahun || !bulan) {
                alert('Pilih tahun dan bulan terlebih dahulu!');
                return;
            }

            var tableAbsen = $('#tableAbsen').DataTable({
                "destroy": true, // Destroy existing instance before reinitializing
                "ajax": {
                    "url": "{{ route('getAbsen') }}", // Adjust this route to your Laravel route
                    "type": "GET",
                    "data": {
                        "id_karyawan": karyawanId,
                        "tahun": tahun,
                        "bulan": bulan
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
                    },
                    "dataSrc": function (json) {
                        // Jika total_keterlambatan null atau "", tampilkan "0 menit"
                        var totalKeterlambatan = json.total_keterlambatan ? json.total_keterlambatan : "0 menit";
                        
                        // Update the footer with total keterlambatan
                        $('#totalKeterlambatan').html(totalKeterlambatan);
                        
                        // Return the data for the table
                        return json.data;
                    }
                },
                "columns": [
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            moment.locale('id');
                            var tanggalAwal = moment(data.tanggal).format('dddd, DD MMMM YYYY');
                            return tanggalAwal;
                        }
                    },
                    {"data": "jam_masuk"},
                    {"data": "jam_keluar"},
                    {"data": "keterangan"},
                    {"data": "keterangan_pulang"},
                    {"data": "waktu_keterlambatan"},
                    {
    "data": "izin",
    "render": function(data, type, row) {
        if (data && data.jam_mulai && data.jam_selesai) {
            return "Izin 3 Jam "+ data.jam_mulai + " - " + data.jam_selesai;
        }
        return "-";
    }
},

                    {
                        "data": null,
                        "render": function(data, type, row) {
                            // Assuming data.foto contains the path like 'absensi/asjdasd.jpeg'
                            var imagePath = "{{ Storage::url('') }}" + data.foto;
                            return '<img src="' + imagePath + '" style="max-width:100px">';
                        }
                    },
                    
                    {
                    "data": null,
                    "render": function(data, type, row) {
                            if (userRole === 'Direktur' || userRole === 'Direktur Utama') {
                                return "";
                            } else {
                                var actions = "";
                                    actions += '<div class="dropdown">';
                                    actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>';
                                    actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                    //if(userRole == 'Koordinator Office'){
                                        actions += '<a class="dropdown-item" href="{{ url('/absensi') }}/' + row.id + '/edit" data-toggle="tooltip" data-placement="top" title="Edit Peserta"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                                    //}
                                    // actions += '<a class="dropdown-item" href="{{ url('/creditcard') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Detail User"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail</a>';
                                    // actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/creditcard') }}/' + row.id + '" method="POST">';
                                    // actions += '@csrf';
                                    // actions += '@method('DELETE')';
                                    // actions += '<button type="submit" class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                    // actions += '</form>';
                                    actions += '</div>';
                                    actions += '</div>';
                                return actions;
                            }
                        }
                    }
                ],
                "order": [[0, 'desc']], // Ubah urutan menjadi descending untuk kolom ke-6
                "columnDefs" : [{"targets":[0], "type":"date"}],
            });
        };
        $('#tablekaryawan tbody').on('click', 'tr', function() {
            $('#tablekaryawan').DataTable().$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        });

        $('#exportButton').on('click', function(event) {
            event.preventDefault(); // Prevent the default anchor behavior
            var selectedRow = $('#tablekaryawan').DataTable().row('.selected');
            var karyawanData = selectedRow.data(); // Get the data of the selected row
            var karyawanId = karyawanData ? karyawanData.id : null; // Check if the data exists
            var tahun = $('#tahun').val(); // Get the selected year
            var bulan = $('#bulan').val(); // Get the selected month

            if(karyawanId && tahun && bulan) {
                // Construct the URL with the selected year and month
                var url = "{{ route('RekapitulasiAbsenperKaryawanExport', ['year' => ':year', 'month' => ':month']) }}"
                    .replace(':year', tahun)
                    .replace(':month', bulan);

                // Append the id_karyawan, tahun, and bulan as query parameters
                url += '?id_karyawan=' + karyawanId + '&tahun=' + tahun + '&bulan=' + bulan;

                // Navigate to the URL for exporting
                window.location.href = url;
            } else {
                alert('Please select the employee, year, and month.');
            }
        });

        $('#tahun, #bulan').on('change', function() {
            updateExportLink();
        });
    });
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

        exportLink.attr('href', '/RekapitulasiAbsenperBulanExport/' + tahun + '/' + bulan);
    }

    
</script>

@endpush
@endsection
