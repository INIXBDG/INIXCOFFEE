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
                                <a href="javascript:void(0);" class="btn btn-success me-1" id="printInvoiceBTN"><i class="fa fa-print"></i> Print Invoice</a>

                            </div>
                            
                        </div>
                    </div>

                </div>

                <div class="container">
                    <h3 class="text-center">SURAT PERINTAH LEMBUR</h3>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><p class="">Tanggal SPL</p></div>
                                <div class="col-md-1">:</div>
                                <div class="col-md-4">{{ \Carbon\Carbon::parse($data->tanggal_spl)->translatedFormat('d F Y') }}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><p class="">Divisi</p></div>
                                <div class="col-md-1">:</div>
                                <div class="col-md-4">{{$data->karyawan->divisi}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><p class="">Uraian Tugas</p></div>
                                <div class="col-md-1">:</div>
                                <div class="col-md-4">{{$data->uraian_tugas}}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4"><p class="">Lembur pada waktu</p></div>
                                <div class="col-md-1">:</div>
                                <div class="col-md-4">{{$data->waktu_lembur}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><p class="">Lembur pada hari/tanggal</p></div>
                                <div class="col-md-1">:</div>
                                <div class="col-md-4">{{ \Carbon\Carbon::parse($data->tanggal_lembur)->translatedFormat('d F Y') }}</div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h5 class="">Memberikan perintah lembur kepada :</h5>
                        <table class="table table-bordered">
                            <thead class="text-center">
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th rowspan="2">Nama Karyawan</th>
                                    <th rowspan="2">Jabatan</th>
                                    <th colspan="2">Jam Lembur</th>
                                    <th rowspan="2">Disetujui Karyawan</th>
                                </tr>
                                <tr>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                    <tr>
                                        <td>1</td>
                                        <td>{{$data->karyawan->nama_lengkap}}</td>
                                        <td>{{$data->karyawan->jabatan}}</td>
                                        <td>{{$data->jam_mulai}}</td>
                                        <td>{{$data->jam_selesai}}</td>
                                        <td>{{$data->approval_karyawan}}</td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                    
                </div>
                <div class="row text-center">
                    <div class="col-12 justify-content-center">
                        <div class="row">
                            <div class="col-sm-4"><p style="margin-bottom: 4px">Yang Mengajukan</p></div>
                            <div class="col-sm-4"><p style="margin-bottom: 4px">Menyetujui</p></div>
                            <div class="col-sm-4"><p style="margin-bottom: 4px">Mengetahui</p></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                @if ($atasan)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $atasan->ttd) }}" alt="{{ $atasan->name }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                @if ($gm)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $gm->ttd) }}" alt="{{ $gm->name }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                @if ($hrd)
                                    <div class="row justify-content-center">
                                        <img src="{{ asset('storage/ttd/' . $hrd->ttd) }}" alt="{{ $hrd->name }}" style="width: 110px">
                                    </div>
                                @else
                                    <br><br><br>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">SPV / Manajer Divisi</div>
                            <div class="col-sm-4">General Manager</div>
                            <div class="col-sm-4">HRD</div>
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
