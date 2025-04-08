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
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    {{-- <link rel="stylesheet" href="css/app.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .table-outer-border {
            border: 1px solid black; /* Border di luar tabel */
        }

        .table-outer-border tbody tr,
        .table-outer-border tbody td {
            border: none; /* Menghapus border dalam tabel */
        }
    </style>
</head>
<body>
    {{-- {{ $data }} --}}
    <div class="container bootstrap snippets bootdey">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6 col-sm-6 text-left">
                        <div class="row">
                            <div class="col-xs-12">
                                <img src="{{ asset('css/logo.png') }}" class="img-responsive" width="100px">
                                <h5 class="m-0">INIXINDO BANDUNG<br></h5>
                                    <span class="small">Jl. Cipaganti no.95 Bandung</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6 d-flex justify-content-end">
                        <div class="panel panel-default text-right">
                            <div class="panel-body d-print-none mt-4">
                                {{-- <a href="javascript:window.print()" class="btn btn-success me-1"><i class="fa fa-print"></i> Print Invoice</a> --}}
                                {{-- <button id="printInvoice" class="btn btn-success"><i class="fa fa-print"></i> PRINT INVOICE</button> --}}
                                <a href="javascript:void(0);" class="btn btn-success me-1" id="printInvoiceBTN"><i class="fa fa-print"></i> Print Invoice</a>

                            </div>
                            
                        </div>
                    </div>

                </div>

                <div class="container">
                    @if ($suratperjalanan->tipe == 'Izin')
                        <h5 class="text-center">Leave Form</h5>
                    @endif
                    @if ($suratperjalanan->tipe == 'Cuti')
                        <h5 class="text-center">Annual Leave Form</h5>
                    @endif
                    @if ($suratperjalanan->tipe == 'Sakit')
                        <h5 class="text-center">Sick leave Form</h5>
                    @endif
                    @if ($suratperjalanan->tipe == 'Berduka')
                        <h5 class="text-center">Bereavement leave Form</h5>
                    @endif
                    @if ($suratperjalanan->tipe == 'Menikah')
                        <h5 class="text-center">Marriage leave Form</h5>
                    @endif
                    
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p class="">Name : {{$suratperjalanan->karyawan->nama_lengkap}}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="">Division : {{$suratperjalanan->karyawan->divisi}}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><p>Duration : {{$suratperjalanan->durasi}} day(s)</p></div>
                        <div class="col-md-4"><p>From : {{ \Carbon\Carbon::parse($suratperjalanan->tanggal_awal)->translatedFormat('d F Y') }}</p></div>
                        <div class="col-md-4"><p>To : {{ \Carbon\Carbon::parse($suratperjalanan->tanggal_akhir)->translatedFormat('d F Y') }}</p></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><p>Address or phone number for contact during leave</p></div>
                        <div class="col-md-1 p-0"><p>:</p></div>
                        <div class="col-md-5"><p>{{$suratperjalanan->kontak}}</p></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><p>Will be available at the office again on</p></div>
                        <div class="col-md-1 p-0"><p>:</p></div>
                        <div class="col-md-5"><p>{{ \Carbon\Carbon::parse($suratperjalanan->tanggal_akhir)->addDay()->translatedFormat('d F Y') }}</p></div>
                    </div>                    
                    <div class="row">
                        <div class="col-md-6"><p>Person in charge to take over assignment(s) during leave</p></div>
                        <div class="col-md-1 p-0"><p>:</p></div>
                        <div class="col-md-5"><p>{{$orang1}}, {{$orang2}}</p></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><p>Purpose to leave</p></div>
                        <div class="col-md-1 p-0"><p>:</p></div>
                        <div class="col-md-5"><p>{{$suratperjalanan->alasan}}</p></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="">Approval</h5>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td>
                                        Requested By :
                                    </td>
                                    <td class="text-center p-0">
                                        <img style="width: 70px" src="{{ asset('storage/ttd/' . $suratperjalanan->karyawan->ttd) }}" alt="{{ $suratperjalanan->karyawan->nama_lengkap }}">
                                        {{-- {{$suratperjalanan->karyawan->ttd}} --}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Manager :
                                    </td>
                                    <td class="text-center p-0">
                                        @if ($suratperjalanan->approval_manager == '1')
                                            <img style="width: 70px" src="{{ asset('storage/ttd/' . $manager->ttd) }}" alt="{{ $manager->nama_lengkap }}">
                                        @else
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        @if ($suratperjalanan->tipe == 'Cuti')
                        <h5 class="">Leave Database</h5>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td>
                                        Taken :
                                    </td>
                                    <td class="text-center p-0">
                                        {{$suratperjalanan->durasi}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Remaining :
                                    </td>
                                    <td class="text-center p-0">
                                        {{$suratperjalanan->karyawan->cuti}}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @else
                        @endif
                    </div>
                </div>
                {{-- <div class="row text-center">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-3"><p style="margin-bottom: 4px">Yang Mengajukan</p><strong>Account Manager</strong></div>
                            <div class="col-sm-3"><p style="margin-bottom: 4px">Menyetujui</p><strong>Manager Marketing</strong></div>
                            <div class="col-sm-3"><p style="margin-bottom: 4px">Admin Exam</p><strong>Technical Support</strong></div>
                            <div class="col-sm-3"><p style="margin-bottom: 4px">Mengetahui</p><strong>Accounting</strong></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                @if ($data->approvalexam->sales && $sales->ttd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $sales->ttd) }}" alt="{{ $sales->name }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-sm-3">
                                @if ($data->approvalexam->spv_sales == '1' && $spv_sales->ttd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $spv_sales->ttd) }}" alt="{{ $spv_sales->name }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-sm-3">
                                @if ($data->approvalexam->technical_support == '1' && $technical_support->ttd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $technical_support->ttd) }}" alt="{{ $technical_support->name }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-sm-3">
                                @if ($data->approvalexam->office_manager == '1' && $office_manager->ttd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $office_manager->ttd) }}" alt="{{ $office_manager->name }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">{{ $sales->nama_lengkap }}</div>
                            <div class="col-sm-3">{{ $spv_sales->nama_lengkap }}</div>
                            <div class="col-sm-3">{{ $technical_support->nama_lengkap }}</div>
                            <div class="col-sm-3">{{ $office_manager->nama_lengkap }}</div>
                        </div>
                    </div>

                </div> --}}
            </div>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/85b3409c34.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $('#printInvoiceBTN ').on('click', function() {
                window.print();
            });
        });
    </script>
</body>
</html>
