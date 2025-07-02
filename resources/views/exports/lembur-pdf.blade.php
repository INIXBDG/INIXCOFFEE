<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overtime Claim | INIXINDO Bandung</title>
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
    <div class="container">
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
            <div class="row mt-4">
                <h4 class="text-center mb-4">OVERTIME CLAIM</h4>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4">Nama</div>
                        <div class="col-md-1">:</div>
                        <div class="col-md-4">{{$data[0]->karyawan->nama_lengkap}}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">Divisi</div>
                        <div class="col-md-1">:</div>
                        <div class="col-md-4">{{$data[0]->karyawan->divisi}}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4">Tahun</div>
                        <div class="col-md-1">:</div>
                        <div class="col-md-4">{{ \Carbon\Carbon::parse($data[0]->tanggal_lembur)->translatedFormat('Y') }}</div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">Bulan</div>
                        <div class="col-md-1">:</div>
                        <div class="col-md-4">{{ \Carbon\Carbon::parse($data[0]->tanggal_lembur)->translatedFormat('F') }}</div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <table class="table table-bordered table-striped table-sm text-center align-center">
                        <thead>
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">Tanggal</th>
                                <th rowspan="2">Hari Biasa dan Libur</th>
                                <th rowspan="2">Keperluan</th>
                                <th colspan="2">Waktu Lembur</th>
                                <th colspan="1">Jumlah</th>
                                <th colspan="1">Nilai Lembur</th>
                                <th rowspan="2">Total Nilai Lembur</th>
                            </tr>
                            <tr>
                                <th>Jam Awal</th>
                                <th>Jam Akhir</th>
                                <th>Jam Lembur</th>
                                <th>Per Jam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $totaljam = 0;
                            $totalkeseluruhanjam = 0;
                            $totalkeseluruhanclaim = 0;
                            @endphp

                            @foreach ($data as $item)
                            @php
                            $start = \Carbon\Carbon::parse(str_replace('.', ':', $item->jam_mulai));
                            $end = \Carbon\Carbon::parse(str_replace('.', ':', $item->jam_selesai));

                            $diffInMinutes = $end->diffInMinutes($start);
                            $hours = floor($diffInMinutes / 60);
                            $minutes = $diffInMinutes % 60;

                            $totaljam = $hours . ' Jam ' . $minutes . ' Menit';

                            $totalJamNumerik = $hours + $minutes;

                            $nilaiLembur = (float) $item->hitunglembur->nilai_lembur;
                            $totalLembur = $nilaiLembur * number_format($totalJamNumerik, 2) ;

                            $totalkeseluruhanjam += number_format($totalJamNumerik, 2) ;
                            $totalkeseluruhanclaim += $totalLembur;
                            @endphp

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->tanggal_lembur }}</td>
                                <td>Hari {{ $item->waktu_lembur }}</td>
                                <td>{{ $item->uraian_tugas }}</td>
                                <td>{{ $item->jam_mulai }}</td>
                                <td>{{ $item->jam_selesai }}</td>
                                <td>{{ $totaljam }}</td>
                                <td>{{ $item->hitunglembur->nilai_lembur }}</td>
                                <td>Rp. {{ number_format($totalLembur, 2, '.', '') }}</td>
                            </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="6">Total Jam Lembur</th>
                                <th>{{ $totaljam }}</th>
                                <th>Total Claim</th>
                                <th>Rp. {{ number_format($totalkeseluruhanclaim, 2, '.', '') }}</th>
                            </tr>
                        </tfoot>

                    </table>
                </div>
            </div>

            <div class="row text-center">
                <div class="col-12">
                    <div class="row">
                        <div class="col-sm-3">
                            <p style="margin-bottom: 4px">Membuat :</p>
                        </div>
                        <div class="col-sm-3">
                            <p style="margin-bottom: 4px">Dibukukan :</p>
                        </div>
                        <div class="col-sm-3">
                            <p style="margin-bottom: 4px">Mengajukan :</p>
                        </div>
                        <div class="col-sm-3">
                            <p style="margin-bottom: 4px">Menyetujui :</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            @if ($hrd->ttd)
                            <div class="row justify-content-center">
                                <img src="{{ asset('storage/ttd/' . $hrd->ttd) }}" alt="{{ $hrd->name }}" style="width: 110px">
                            </div>
                            @else
                            <br><br><br>
                            @endif
                        </div>
                        <div class="col-sm-3">
                            @if ($finance->ttd)
                            <div class="row justify-content-center">
                                <img src="{{ asset('storage/ttd/' . $finance->ttd) }}" alt="{{ $finance->name }}" style="width: 110px">
                            </div>
                            @else
                            <br><br><br>
                            @endif
                        </div>
                        <div class="col-sm-3">
                            @if ($data[0]->karyawan->ttd)
                            <div class="row justify-content-center">
                                <img src="{{ asset('storage/ttd/' . $data[0]->karyawan->ttd) }}" alt="{{ $data[0]->karyawan->name }}" style="width: 110px">
                            </div>
                            @else
                            <br><br><br>
                            @endif
                        </div>
                        <div class="col-sm-3">
                            @if ($gm->ttd)
                            <div class="row justify-content-center">
                                <img src="{{ asset('storage/ttd/' . $gm->ttd) }}" alt="{{ $gm->name }}" style="width: 110px">
                            </div>
                            @else
                            <br><br><br>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">{{ $hrd->nama_lengkap }}</div>
                        <div class="col-sm-3">{{ $finance->nama_lengkap }}</div>
                        <div class="col-sm-3">{{ $data[0]->karyawan->nama_lengkap }}</div>
                        <div class="col-sm-3">{{ $gm->nama_lengkap }}</div>
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