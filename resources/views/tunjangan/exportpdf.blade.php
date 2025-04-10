<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tunjangan Karyawan - {{ ($bulan) }} - {{ $tahun }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
    {{-- <link rel="stylesheet" href="css/app.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        @page {
            size: A4 Portrait;
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
                    <a href="javascript:void(0);" class="btn btn-success m-4 d-print-none" id="printInvoice"><i class="fa fa-print"></i> Print Invoice</a>
                    @php
                    $grandTotalTunjangan = 0;
                    $grandTotalPotongan = 0;
                    $grandTotalPremiHadir = 0;
                    $grandTotalTransaksi = 0;
                @endphp
                
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="">Nama Karyawan</th>
                            <th class="">Tunjangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($post as $index => $item)
                            <tr>
                                <td style="width: 20%">{{ $index }}</td>
                                <td style="width: 80%">
                                    <div class="row">
                                        @php
                                            $totalPerKaryawan = 0;
                                        @endphp
                
                                        @foreach ($item as $items)
                                            <div class="col d-block">
                                                <p>{{ $items->jenistunjangan->nama_tunjangan }}</p>
                                                {{-- <p>{{ $items->jenistunjangan->tipe }}</p> --}}
                                                <p>{{ formatRupiah(floatval($items->total)) }}</p>
                                            </div>
                
                                            @php
                                                $totalPerKaryawan += floatval($items->total);
                                                $grandTotalTransaksi += floatval($items->total);
                
                                                if ($items->jenistunjangan->tipe == 'Potongan') {
                                                    $grandTotalPotongan += floatval($items->total);
                                                }
                                                elseif ($items->jenistunjangan->nama_tunjangan == 'Absensi') {
                                                    $grandTotalPremiHadir += floatval($items->total);
                                                } else {
                                                    $grandTotalTunjangan += floatval($items->total);
                                                }


                                            @endphp
                                        @endforeach
                
                                        <hr>
                                        <p>Total Karyawan Ini: {{ formatRupiah($totalPerKaryawan) }}</p>
                                        
                                    </div>
                                </td>                                
                            </tr>
                        @endforeach
                        <tr>
                            <th>Total Tunjangan</th>
                            <th>{{ formatRupiah($grandTotalTunjangan) }}</th>
                        </tr>
                        <tr>
                            <th>Total Potongan</th>
                            <th>{{ formatRupiah($grandTotalPotongan) }}</th>
                        </tr>
                        <tr>
                            <th>Total Premi Hadir</th>
                            <th>{{ formatRupiah($grandTotalPremiHadir) }}</th>
                        </tr>
                        <tr>
                            <th>Total Transaksi</th>
                            <th>{{ formatRupiah($grandTotalTransaksi) }}</th>
                        </tr>
                    </tbody>
                    {{-- <tfoot> --}}
                        
                    {{-- </tfoot> --}}
                </table>
                
                {{-- {{$post}} --}}
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
