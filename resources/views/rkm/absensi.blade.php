<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Absensi RKM</title>
    <link rel="apple-touch-icon" sizes="180x180" href="https://inixindobdg.co.id/images/logoinix.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://inixindobdg.co.id/images/logoinix.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://inixindobdg.co.id/images/logoinix.png">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    {{-- <link rel="stylesheet" href="css/app.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .table-outer-border {
            border: 1px solid black;
            /* Border di luar tabel */
        }

        .table-outer-border tbody tr,
        .table-outer-border tbody td {
            border: none;
            /* Menghapus border dalam tabel */
        }
    </style>
</head>

<body>
    <div class="container bootstrap snippets bootdey">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6 col-sm-6 text-left">
                        <div class="row">
                            <div class="col-xs-12">
                                <img src="{{ asset('css/logo.png') }}" class="img-responsive" width="200px">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6 d-flex justify-content-end">
                        <div class="panel panel-default text-right">
                            <div class="panel-body d-print-none mt-4">
                                <a href="javascript:void(0);" class="btn btn-success me-1" id="printInvoiceBTN"><i class="fa fa-print"></i> Print Absensi</a>
                                <div>
                                    <table class="table table-outer-border">
                                        <tbody>
                                            <tr>
                                                <td colspan="2">
                                                    <p>Custom RKM</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p>Materi :</p>
                                                </td>
                                                <td>
                                                    <input type="text" name="custom_materi" class="form-control" placeholder="Masukkan Materi" value="{{$rkm->materi->nama_materi}}" id="custom_materi">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p>Tanggal Awal:</p>
                                                </td>
                                                <td>
                                                    <input type="date" name="custom_tanggal_awal" class="form-control" value="{{$rkm->tanggal_awal}}" id="custom_tanggal_awal">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p>Tanggal Akhir:</p>
                                                </td>
                                                <td>
                                                    <input type="date" name="custom_tanggal_akhir" class="form-control" value="{{$rkm->tanggal_akhir}}" id="custom_tanggal_akhir">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h5>Daftar Hadir Peserta Training Inixindo Bandung</h5>
                        <p>Materi: <span class="materi_value"></span></p>
                        <p>Periode: <span class="tanggal_awal_value"></span> - <span class="tanggal_akhir_value"></span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        {{-- @php
                            $startDate = \Carbon\Carbon::parse($rkm->tanggal_awal);
                            $endDate = \Carbon\Carbon::parse($rkm->tanggal_akhir);
                            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
                            $totalDays = $period->count();
                        @endphp --}}
                        <table class="table table-bordered">
                            <thead style="text-align: center">
                                <tr>
                                    <th rowspan="2">No.</th>
                                    <th rowspan="2">Nama</th>
                                    <th rowspan="2">Instansi</th>
                                    <th colspan="1">Kehadiran</th>
                                </tr>
                                <tr>
                                    {{-- @foreach($period as $date)
                                        <th>{{ $date->translatedFormat('j F') }}</th>
                                    @endforeach --}}
                                </tr>
                            </thead>
                            <tbody style="text-align: center">
                                @foreach($rkm->registrasi as $r)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ ucwords(strtolower($r->peserta->nama)) }}</td>
                                    <td>{{$rkm->perusahaan->nama_perusahaan}}</td>
                                    {{-- @foreach($period as $date) --}}
                                    <td></td>
                                    {{-- @endforeach --}}
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="text-align: center">
                                <tr>
                                    <td colspan="3">Instruktur : {{$rkm->instruktur->nama_lengkap}}</td>
                                    {{-- sesuaikan dengan jumlah periode --}}
                                    {{-- @foreach($period as $date)
                                        <td></td>
                                    @endforeach --}}
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/85b3409c34.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#printInvoiceBTN ').on('click', function() {
                window.print();
            });

            moment.locale('id'); // Set locale to Indonesian

            // Function to format date to Indonesian format
            function formatTanggalIndo(date) {
                return moment(date).format('dddd, LL');
            }
            // Set initial values
            $('.materi_value').text($('#custom_materi').val());
            $('.tanggal_awal_value').text(formatTanggalIndo($('#custom_tanggal_awal').val()));
            $('.tanggal_akhir_value').text(formatTanggalIndo($('#custom_tanggal_akhir').val()));

            // Update values on input change
            $('#custom_materi').on('input', function() {
                $('.materi_value').text($(this).val());
            });

            $('#custom_tanggal_awal, #custom_tanggal_akhir').on('input', function() {
                var tanggalAwal = $('#custom_tanggal_awal').val();
                var tanggalAkhir = $('#custom_tanggal_akhir').val();

                $('.tanggal_awal_value').text(formatTanggalIndo(tanggalAwal));
                $('.tanggal_akhir_value').text(formatTanggalIndo(tanggalAkhir));

                generateTableHeaders(tanggalAwal, tanggalAkhir);
                generateTableBody(tanggalAwal, tanggalAkhir);
                generateTableFooter(tanggalAwal, tanggalAkhir);
            });

            function generateTableHeaders(startDate, endDate) {
                var start = moment(startDate);
                var end = moment(endDate);
                var period = end.diff(start, 'days') + 1; // Calculate total days
                var headerRow = '';

                for (var i = 0; i < period; i++) {
                    var currentDate = moment(start).add(i, 'days');
                    headerRow += '<th>' + currentDate.format('D MMMM') + '</th>';
                }

                // Insert headerRow into the table
                $('thead tr:nth-child(2)').html(headerRow);
                $('thead tr:first-child th[colspan]').attr('colspan', period);
            }

            // Generate table body rows based on date range
            function generateTableBody(startDate, endDate) {
                var start = moment(startDate);
                var end = moment(endDate);
                var period = end.diff(start, 'days') + 1;

                $('tbody tr').each(function() {
                    var bodyRow = '';

                    for (var i = 0; i < period; i++) {
                        bodyRow += '<td></td>';
                    }

                    $(this).find('td:gt(2)').remove(); // Remove existing dynamic cells
                    $(this).append(bodyRow); // Append new cells
                });
            }

            // Generate table footer based on date range
            function generateTableFooter(startDate, endDate) {
                var start = moment(startDate);
                var end = moment(endDate);
                var period = end.diff(start, 'days') + 1;
                var footerRow = '';

                for (var i = 0; i < period; i++) {
                    footerRow += '<td></td>';
                }

                $('tfoot tr').html('<td colspan="3">Instruktur : {{$rkm->instruktur->nama_lengkap}}</td>' + footerRow);
            }

            // Initialize table with default values
            generateTableHeaders($('#custom_tanggal_awal').val(), $('#custom_tanggal_akhir').val());
            generateTableBody($('#custom_tanggal_awal').val(), $('#custom_tanggal_akhir').val());
            generateTableFooter($('#custom_tanggal_awal').val(), $('#custom_tanggal_akhir').val());

        });
    </script>
</body>

</html>