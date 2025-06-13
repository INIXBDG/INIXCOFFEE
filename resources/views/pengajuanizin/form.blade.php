<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>INIXCOFFEE</title>
    <link rel="apple-touch-icon" sizes="180x180" href="https://inixindobdg.co.id/images/logoinix.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://inixindobdg.co.id/images/logoinix.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://inixindobdg.co.id/images/logoinix.png">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            margin-top: 20px;
        }
        .panel {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: #fff;
            padding: 20px;
        }
        .logo-img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .header-info h5 {
            font-weight: 700;
            margin-bottom: 5px;
        }
        .header-info span {
            color: #6c757d;
        }
        .print-btn {
            background-color: #28a745;
            border-color: #28a745;
            font-weight: 600;
        }
        .print-btn:hover {
            background-color: #218838;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        .table-bordered td {
            vertical-align: middle;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-row p {
            margin: 0;
        }
        .signature-img {
            max-width: 80px;
            margin: 0 auto;
        }
        @media print {
            .d-print-none {
                display: none !important;
            }
            .panel {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="panel">
            <div class="row align-items-center">
                <div class="col-md-6 col-sm-6">
                    <div class="header-info">
                        <img src="{{ asset('css/logo.png') }}" class="logo-img" alt="INIXINDO Logo">
                        <h5>INIXINDO BANDUNG</h5>
                        <span>Jl. Cipaganti no.95 Bandung</span>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6 text-end">
                    <a href="javascript:void(0);" class="btn btn-success print-btn d-print-none" id="printInvoiceBTN">
                        <i class="fa fa-print"></i> Print Invoice
                    </a>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    @if ($suratperjalanan->tipe == 'Izin')
                        <h5 class="text-center section-title">Leave Form</h5>
                    @elseif ($suratperjalanan->tipe == 'Cuti')
                        <h5 class="text-center section-title">Annual Leave Form</h5>
                    @elseif ($suratperjalanan->tipe == 'Sakit')
                        <h5 class="text-center section-title">Sick Leave Form</h5>
                    @elseif ($suratperjalanan->tipe == 'Berduka')
                        <h5 class="text-center section-title">Bereavement Leave Form</h5>
                    @elseif ($suratperjalanan->tipe == 'Menikah')
                        <h5 class="text-center section-title">Marriage Leave Form</h5>
                    @endif
                </div>
            </div>

            <div class="row info-row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> {{$suratperjalanan->karyawan->nama_lengkap}}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Division:</strong> {{$suratperjalanan->karyawan->divisi}}</p>
                </div>
            </div>
            <div class="row info-row">
                <div class="col-md-4">
                    <p><strong>Duration:</strong> {{$suratperjalanan->durasi}} Hours</p>
                </div>
                <div class="col-md-4">
                    <p><strong>From:</strong> {{ \Carbon\Carbon::parse($suratperjalanan->jam_mulai)->translatedFormat('H:i') }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>To:</strong> {{ \Carbon\Carbon::parse($suratperjalanan->jam_selesai)->translatedFormat('H:i') }}</p>
                </div>
            </div>
            <div class="row info-row">
                <div class="col-md-6"><p><strong>Starting on date:</strong></p></div>
                <div class="col-md-6"><p>{{ \Carbon\Carbon::parse($suratperjalanan->created_at)->translatedFormat('d M Y') }}</p></div>
            </div>
            <div class="row info-row">
                <div class="col-md-6"><p><strong>With reason:</strong></p></div>
                <div class="col-md-6"><p>{{$suratperjalanan->alasan}}</p></div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h5 class="section-title">Approval</h5>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td>Requested By:</td>
                                <td class="text-center">
                                    <img class="signature-img" src="{{ asset('storage/ttd/' . $suratperjalanan->karyawan->ttd) }}" alt="ttd">
                                    <br>
                                    {{ $suratperjalanan->karyawan->nama_lengkap }}
                                </td>
                            </tr>
                            <tr>
                                <td>HRD:</td>
                                <td class="text-center">
                                    <img class="signature-img" src="{{ asset('storage/ttd/' . $suratperjalanan->karyawan->ttd) }}" alt="ttd">
                                    <br>
                                    {{ $hrd->nama_lengkap }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/85b3409c34.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('#printInvoiceBTN').on('click', function() {
                window.print();
            });
        });
    </script>
</body>
</html>