<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Level Materi</title>
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
                    <div class="card">
                        <div class="card-body">
                            {{-- {{$data}} --}}
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Instruktur</th>
                                        <th>Nama Materi</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Metode Kelas</th>
                                        <th>Event</th>
                                        <th>Durasi</th>
                                        <th>Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>{{$item->instruktur->nama_lengkap}}</td>
                                            <td>
                                                @if($item->rkm && $item->rkm->materi)
                                                    {{$item->rkm->materi->nama_materi}}
                                                @else
                                                    N/A <!-- Atau bisa ditampilkan pesan lain sesuai kebutuhan -->
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_awal)->translatedFormat('l, j F Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_akhir)->translatedFormat('l, j F Y') }}</td>
                                            <td>{{$item->rkm->metode_kelas ?? 'N/A'}}</td>
                                            <td>{{$item->rkm->event ?? 'N/A'}}</td>
                                            <td>{{$item->durasi}} Hari</td>
                                            <td>{{$item->level}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>                            
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
        });
    </script>
</body>
</html>
