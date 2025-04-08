<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tunjangan Karyawan - {{ $month }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    {{-- <link rel="stylesheet" href="css/app.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
        }
        h1,h2,h3,h4,h5 {
            text-align: center;
            margin-bottom: 10px;
            text-wrap: nowrap;
        }
        .tbody {
            /* width: 100%; */
            border-bottom: 0px solid
        }
        .footer {
            margin-top: 20px;
            text-align: center;
        }
        .dbl-border {
            border-bottom: 8px double;
        }
        .one-border {
            border-bottom: 2px solid;
        }
        .judul {
            width: 25%;
        }
    </style>
</head>
<body>
    <div class="container-fluid bootstrap snippets bootdey">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-3">
                        <img src="{{ asset('css/logo.png') }}" class="img-responsive" width="50%">
                    </div>
                    <div class="col-6">
                        <div class="mt-4">
                            <h3>PT. INIXINDO AMIETE MANDIRI</h3>
                            <h3>JALAN CIPAGANTI NO. 95 BANDUNG</h3>
                            <h3 class="mt-3">SLIP TUNJANGAN</h3>
                        </div>
                        
                    </div>
                    <div class="col-3">
                        <a href="javascript:void(0);" class="btn btn-success m-4 d-print-none" id="printInvoice"><i class="fa fa-print"></i> Print Invoice</a>
                    </div>
                </div>
                <div class="row">
                    {{-- <div class="col-12"> --}}
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td>Nama Karyawan</td>
                                    <td>:</td>
                                    <td>{{ $me->nama_lengkap }}</td>
                                    <td>Divisi</td>
                                    <td>:</td>
                                    <td>{{ $me->divisi }}</td>
                                </tr>
                                <tr>
                                    <td>Period</td>
                                    <td>:</td>
                                    <td>{{ $month }}</td>
                                    <td>Jabatan</td>
                                    <td>:</td>
                                    <td>{{ $me->jabatan }}</td>
                                    {{-- <td>{{ $tunjangan->keterlambatan}}</td> --}}
                                </tr>
                            </tbody>
                        </table>
                    {{-- </div> --}}
                </div>
                <div class="row">
                    <h5>INCOME</h5>
                </div>
                <div class="row">
                    <table class="table">
                        <tbody>
                            {{-- {{$absensi}} --}}
                            @foreach($tunjangan as $item)
                            {{-- {{$item}} --}}
                                @if($item->jenistunjangan->nama_tunjangan == 'Education') 
                                    <tr>
                                        <td class="judul">Tunjangan {{ $item->jenistunjangan->nama_tunjangan }}</td>
                                        <td>:</td>
                                        <td style="width: 30%"></td>
                                        <td style="width: 10%"></td>
                                        <td style="width: 30%">Rp. {{ number_format($item->total, 2, ',', '.') }}</td>
                                    </tr>
                                    @elseif ($item->jenistunjangan->nama_tunjangan == 'Makan' || $item->jenistunjangan->nama_tunjangan == 'Transport')
                                    <tr>
                                        <td class="judul">Tunjangan {{ $item->jenistunjangan->nama_tunjangan }}</td>
                                        <td>:</td>
                                        <td style="width: 30%">Rp. {{ number_format($item->jenistunjangan->nilai, 2, ',', '.') }}</td>
                                            <td style="width: 10%">x {{ $absensi }}</td>
                            
                                        <td style="width: 30%">Rp. {{ number_format($item->total, 2, ',', '.') }}</td>
                                    </tr>
                                    @elseif ($item->keterangan == 'Tunjangan')
                                    <tr>
                                        <td class="judul">Tunjangan {{ $item->jenistunjangan->nama_tunjangan }}</td>
                                        <td>:</td>
                                        <td style="width: 30%">Rp. {{ number_format($item->jenistunjangan->nilai, 2, ',', '.') }}</td>
                                        @if (isset($item->jumlah_absensi) && $item->jumlah_absensi)
                                            <td style="width: 10%">x {{ $item->jumlah_absensi }}</td>
                                        @else
                                            <td style="width: 10%">x 1</td>
                                        @endif
                                        <td style="width: 30%">Rp. {{ number_format($item->total, 2, ',', '.') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="judul">Total</td>
                                <td>:</td>
                                <td></td>
                                <td style="width: 10%"></td>
                                <td style="width: 30%">Rp. {{ number_format($totalTunjangan, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row">
                    <h5>PEMOTONGAN</h5>
                </div>
                <div class="row">
                    <table class="table">
                        <tbody>
                            @foreach($tunjangan as $item)
                                @if ($item->keterangan == 'Potongan')
                                <tr>
                                    <td class="judul">Potongan {{ $item->jenistunjangan->nama_tunjangan }}</td>
                                    <td>:</td>
                                    <td style="width: 30%">Rp. {{ number_format($item->total, 2, ',', '.') }}</td>
                                    @if (isset($item->jumlah_absensi) && $item->jumlah_absensi)
                                        <td style="width: 10%">x {{ $item->jumlah_absensi }}</td>
                                    @else
                                        <td style="width: 10%">x 1</td>
                                    @endif
                                    <td style="width: 30%">Rp. {{ number_format($item->total, 2, ',', '.') }}</td>
                                </tr> 
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="one-border">
                                <td class="judul">Total</td>
                                <td>:</td>
                                <td></td>
                                <td style="width: 10%"></td>
                                <td style="width: 30%">Rp. {{ number_format($totalPotongan, 2, ',', '.') }}</td>
                            </tr>
                            <tr class="dbl-border">
                                <td class="judul">Take Home Pay</td>
                                <td>:</td>
                                <td></td>
                                <td style="width: 10%"></td>
                                <td style="width: 30%">Rp. {{ number_format($totalBersih, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row text-center justify-content-center">
                    <div class="col-12">
                        <div class="row justify-content-center">
                            <div class="col-3"><p style="margin-bottom: 4px">Membuat</p></div>
                            <div class="col-3"><p style="margin-bottom: 4px">Mengetahui</p></div>
                            <div class="col-3"><p style="margin-bottom: 4px">Menerima</p></div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-3">
                                @if ($hrd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $hrd->ttd) }}" alt="{{ $hrd->nama_lengkap }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-3">
                                @if ($direktur)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $direktur->ttd) }}" alt="{{ $direktur->nama_lengkap }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-3">
                                @if ($me)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $me->ttd) }}" alt="{{ $me->nama_lengkap }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-3">{{ $hrd->nama_lengkap ?? '-' }}</div>
                            <div class="col-3">{{ $direktur->nama_lengkap ?? '-' }}</div>
                            <div class="col-3">{{ $me->nama_lengkap ?? '-' }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/85b3409c34.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('#printInvoice').on('click', function() {
                window.print();
            });
        });
    </script>
</body>
</html>
