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
                                    $nama_bulan = [
                                        'Januari',
                                        'Februari',
                                        'Maret',
                                        'April',
                                        'Mei',
                                        'Juni',
                                        'Juli',
                                        'Agustus',
                                        'September',
                                        'Oktober',
                                        'November',
                                        'Desember',
                                    ];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        $bulan_awal = $nama_bulan[$bulan - 1];
                                        $bulan_akhir = $nama_bulan[$bulan % 12];
                                        $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                        echo "<option value=\"$bulan\" $selected>$bulan_awal - $bulan_akhir</option>";
                                    }
                                @endphp
                            </select>
                        </div>

                        <div class="col-md-4 mx-1">
                            <button type="submit" onclick="getDataRKM()" class="btn click-primary"
                                style="margin-top: 37px">Cari Data</button>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>

        <script>
            $(document).ready(function() {
                getDataRKM();
            });

            function getDataRKM() {
                var tahun = document.getElementById('tahun').value;
                var bulan = document.getElementById('bulan').value;
                const baseRkmApiUrl = @json(route('rkmAPI', ['year' => 'YEAR', 'month' => 'MONTH']));

                var apiUrl = baseRkmApiUrl
                    .replace('YEAR', tahun)
                    .replace('MONTH', bulan);

                // console.log(tahun);
                // console.log(bulan);

                // Show loading modal
                $('#loadingModal').modal('show');

                $.ajax({
                    url: apiUrl,
                    method: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        $('#loadingModal').modal('show');
                        $('#loadingModal').on('show.bs.modal', function() {
                            $('#loadingModal').removeAttr('inert');
                        });
                    },
                    complete: function() {
                        setTimeout(() => {
                            $('#loadingModal').modal('hide');
                            $('#loadingModal').on('hidden.bs.modal', function() {
                                $('#loadingModal').attr('inert', true);
                            });
                        }, 1000);
                    },
                    success: function(response) {
                        // console.log(response);
                        var html = ''; // Define html as an empty string here
                        var count = 1;
                        var jabatan = "{{ auth()->user()->jabatan }}";
                        // console.log(jabatan);
                        response.data.forEach(function(monthData) {
                            monthData.weeksData.forEach(function(weekData) {
                                // console.log(weekData);
                                var bulanKosong = moment(weekData.start).format('M')
                                // console.log(bulanKosong);
                                html += '<div class="card my-1">';
                                html += '<div class="card-body table-responsive">';
                                html += '<h3 class="card-title my-1">Rencana Kelas Mingguan</h3>';
                                moment.locale('id');
                                var startOfWeek = moment(weekData.start) // Mulai dari Senin
                                var endOfWeek = startOfWeek.clone().add(4,
                                    'days'); // Akhiri di Jumat
                                html += '<p class="card-title my-1">Periode : ' + moment(
                                    startOfWeek).format('DD MMMM YYYY') + ' - ' + moment(
                                    endOfWeek).format('DD MMMM YYYY') + '</p>';
                                html += '<table class="table table-responsive table-striped">';
                                html += '<thead>';
                                html += '<tr>';
                                html += '<th scope="col">No</th>';
                                html += '<th scope="col">Materi</th>';
                                html += '<th scope="col">Tanggal Training</th>';
                                html += '<th scope="col">Perusahaan</th>';
                                html += '<th scope="col">Kode Sales</th>';
                                html += '<th scope="col">Instruktur</th>';
                                html += '<th scope="col">Metode Kelas</th>';
                                html += '<th scope="col">Event</th>';
                                html += '<th scope="col">Ruang</th>';
                                html += '<th scope="col">Pax</th>';
                                if (jabatan == 'SPV Sales' || jabatan == 'GM' || jabatan ==
                                    'Sales' || jabatan == 'Adm Sales' || jabatan ==
                                    'Education Manager' || jabatan == 'Instruktur' || jabatan ==
                                    'Direktur' || jabatan == 'Office Manager' || jabatan ==
                                    'Customer Care' || jabatan == 'Customer Service' || jabatan ==
                                    'Admin Holding' || jabatan == 'Technical Support' || jabatan ===
                                    'Direktur Utama' || jabatan === 'Direktur' || jabatan ===
                                    'HRD' || jabatan === 'Koordinator Office' || jabatan ===
                                    'Finance &amp; Accounting') {
                                    html += '<th scope="col">Aksi</th>';
                                }
                                html += '</tr>';
                                html += '</thead>';
                                html += '<tbody>';
                                if (weekData.data.length === 0) {
                                    html += '<tr>';
                                    html +=
                                        '<td colspan="10" class="text-center">Tidak Ada Kelas Mingguan</td>';
                                    html += '</tr>';
                                } else {
                                    weekData.data.forEach(function(rkm, index) {
                                        if (rkm.status_all == 0) {
                                            var tanggal = moment(rkm.tanggal_awal).format(
                                                'D')
                                            var lanbu = moment(rkm.tanggal_awal).format('M')
                                            var hunta = moment(rkm.tanggal_awal).format('Y')
                                            // console.log(tanggal,lanbu,hunta);
                                            //console.log(rkm.metode_kelas);
                                            if (rkm.metode_kelas == 'Offline') {
                                                var kelas = "off"
                                            } else if (rkm.metode_kelas ==
                                                'Inhouse Bandung') {
                                                var kelas = "inhb"
                                            } else if (rkm.metode_kelas ==
                                                'Inhouse Luar Bandung') {
                                                var kelas = "inhlb"
                                            } else {
                                                var kelas = "vir"
                                            }
                                            if (rkm.status_all == '0') {
                                                html +=
                                                    '<tr style="background-color: rgba(255, 0, 0, 0.5); color: #fff">';
                                            } else if (rkm.status_all == '1') {
                                                html +=
                                                    '<tr style="background-color: rgba(0, 0, 255, 0.5); color: #fff">';
                                            } else {
                                                html +=
                                                    '<tr style="background-color: rgba(0, 0, 0, 0.5); color: #fff">';
                                            }
                                            html += '<td>' + (index + 1) + '</td>';
                                            html += '<td>' + rkm.materi.nama_materi +
                                                '</td>';
                                            if (rkm.tanggal_awal == rkm.tanggal_akhir) {
                                                html += '<td>' + moment(rkm.tanggal_awal)
                                                    .format('DD MMMM YYYY') + '</td>'
                                            } else {
                                                html += '<td>' + moment(rkm.tanggal_awal)
                                                    .format('DD MMMM YYYY') + ' s/d ' +
                                                    moment(rkm.tanggal_akhir).format(
                                                        'DD MMMM YYYY') + '</td>';
                                            }
                                            html += '<td>';
                                            rkm.perusahaan.forEach(function(perusahaan) {
                                                html += perusahaan.nama_perusahaan +
                                                    ', ';
                                            });
                                            html += '</td>';
                                            html += '<td>';
                                            rkm.sales.forEach(function(sales) {
                                                html += sales.kode_karyawan + ', ';
                                            });
                                            html += '</td>';
                                            html += '<td>';
                                            if (rkm.instruktur_all && rkm.instruktur_all
                                                .trim() !== '') {
                                                var instruktur_array = rkm.instruktur_all
                                                    .split(', ');
                                                html += instruktur_array[0];
                                            } else {
                                                html += 'Belum Ditentukan';
                                            }
                                            html += '</td>';
                                            html += '<td>' + rkm.metode_kelas + '</td>';
                                            html += '<td>' + rkm.event + '</td>';
                                            if (rkm.ruang == null || rkm.ruang == "-") {
                                                html += '<td>Belum Ditentukan</td>';
                                            } else {
                                                html += '<td>' + rkm.ruang + '</td>';
                                            }
                                            html += '<td>' + rkm.total_pax + '</td>';
                                            if (jabatan == 'SPV Sales' || jabatan == 'GM' ||
                                                jabatan == 'Sales' || jabatan ==
                                                'Adm Sales' || jabatan ==
                                                'Education Manager' || jabatan ==
                                                'Instruktur' || jabatan ==
                                                'Office Manager' || jabatan ==
                                                'Customer Care' || jabatan ==
                                                'Customer Service' || jabatan ==
                                                'Admin Holding' || jabatan ==
                                                'Technical Support' || jabatan ===
                                                'Direktur Utama' || jabatan ===
                                                'Direktur' || jabatan === 'HRD' ||
                                                jabatan === 'Koordinator Office' ||
                                                jabatan === 'Finance &amp; Accounting') {
                                                html += '<td>';
                                                html += '<div class="btn-group dropup">';
                                                html +=
                                                    '<button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                                html += 'Actions';
                                                html += '</button>';
                                                html += '<div class="dropdown-menu">';
                                                html +=
                                                    '<a class="dropdown-item"" href="/rkm/' +
                                                    rkm.materi_key + 'ixb' + tanggal +
                                                    'ie' + hunta + 'ie' + lanbu + 'ixb' +
                                                    kelas +
                                                    '" data-toggle="tooltip" data-placement="top" title="Detail RKM"><img src="{{ asset('icon/clipboard-primary.svg') }}" class=""> Detail RKM</a>';
                                                html +=
                                                    '<a class="dropdown-item" href="/rkm/uploadAbsensi/' +
                                                    rkm.materi_key + 'ixb' + tanggal +
                                                    'ie' + hunta + 'ie' + lanbu + 'ixb' +
                                                    kelas +
                                                    '" data-toggle="tooltip" data-placement="top" title="Detail RKM"><img src="{{ asset('icon/upload.svg') }}" class=""> Upload Absensi</a>';
                                                html +=
                                                    '<a class="dropdown-item" href="/rkm/uploadSertifikat/' +
                                                    rkm.id +
                                                    '" data-toggle="tooltip" data-placement="top" title="Upload Sertifikat"><img src="{{ asset('icon/upload.svg') }}" class=""> Upload Sertifikat</a>';
                                                html += '</div>';
                                                html += '</div>';
                                                html += '</td>';
                                            }
                                            html += '</tr>';
                                        }
                                    });
                                }
                                html += '</tbody>';
                                html += '</table>';
                                html += '</div>';
                                html += '</div>';
                            });
                        });
                        setTimeout(() => {
                            $('#loadingModal').modal('hide');
                            $('#content').html(html);
                        }, 1000);
                    }
                });
            }
        </script>
    @endpush
@endsection
