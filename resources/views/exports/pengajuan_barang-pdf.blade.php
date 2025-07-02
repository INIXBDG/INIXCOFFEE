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
                                <a href="javascript:void(0);" class="btn btn-success me-1" id="printInvoiceBTN"><i class="fa fa-print"></i> Print Form Permintaan</a>

                            </div>
                            
                        </div>
                    </div>

                </div>

                <div class="container">
                    <h3 class="text-center">Form Permintaan Barang</h3>
                    {{-- {{$data}} --}}
                    <table class="table table-bordered my-24">
                        <tbody>
                            <tr>
                                <td>Hari / Tanggal</td>
                                <td>{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') }}</td>
                                {{-- <td>{{$data->created_at}}</td> --}}
                            </tr>
                            <tr>
                                <td>Divisi</td>
                                <td>{{$data->karyawan->divisi}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered my-4">
                        <thead>
                            <tr>
                                <th>Qty</th>
                                <th>Nama Barang</th>
                                <th>Besarnya (Rp.)</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                    $totalHarga = 0; // Inisialisasi total harga  
                            @endphp
                            @foreach ($data->detail as $item)
                                <tr>
                                    <td>{{$item->qty}}</td>
                                    <td>{{$item->nama_barang}}</td>
                                    <td>{{formatRupiah($item->harga)}}</td>
                                    <td>{{$item->keterangan}}</td>
                                </tr>
                                @php  
                                    $totalbarang = $item->harga * $item->qty;
                                    $totalHarga += $totalbarang; // Tambahkan harga ke total  
                                @endphp  
                            @endforeach
                        </tbody>
                        <tfoot>
                            <th scope="col" colspan="2">Total</th>  
                            <th scope="col" colspan="2">{{ formatRupiah($totalHarga) }}</th> <!-- Tampilkan total harga -->  
                        </tfoot>
                    </table>
                </div>
                <div class="row text-center">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-4"><p style="margin-bottom: 4px">Yang Mengajukan</p></div>
                            <div class="col-sm-4"><p style="margin-bottom: 4px">Menyetujui</p></div>
                            <div class="col-sm-4"><p style="margin-bottom: 4px">Mengetahui</p></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                @if ($data->karyawan->ttd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $data->karyawan->ttd) }}" alt="{{ $data->karyawan->nama_lengkap }}" style="width: 155px;height:auto">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                @if ($finance->ttd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $finance->ttd) }}" alt="{{ $finance->nama_lengkap }}" style="width: 155px;height:auto">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                @if ($gm->ttd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $gm->ttd) }}" alt="{{ $gm->nama_lengkap }}" style="width: 155px;height:auto">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                {{ $data->karyawan->nama_lengkap }}
                            </div>
                            <div class="col-sm-4">
                                {{ $finance->nama_lengkap }}
                            </div>
                            <div class="col-sm-4">
                                {{ $gm->nama_lengkap }}
                            </div>
                            
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
            $('#printInvoiceBTN ').on('click', function() {
                window.print();
            });
        });
    </script>
</body>
</html>
