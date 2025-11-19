<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>INIXCOFFEE</title>
    <link rel="apple-touch-icon" sizes="180x180" href="https://inixindobdg.co.id/images/logoinix.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://inixindobdg.co.id/images/logoinix.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://inixindobdg.co.id/images/logoinix.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .table-outer-border {
            border: 1px solid black;
        }

        .table-outer-border tbody tr,
        .table-outer-border tbody td {
            border: none;
        }

        .signature img {
            width: 155px;
            height: auto;
        }

        @media print {
            #printInvoiceBTN {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container bootstrap snippets bootdey mt-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6 col-sm-6 text-left">
                        <div class="row">
                            <div class="col-xs-12">
                                <img src="https://inixindobdg.co.id/images/logoinix.png" class="img-responsive"
                                    width="100px">
                                <h5 class="m-0">INIXINDO BANDUNG<br></h5>
                                <span class="small">Jl. Cipaganti no.95 Bandung</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6 d-flex justify-content-end">
                        <div class="panel panel-default text-right">
                            <div class="panel-body d-print-none mt-4">
                                <a href="javascript:void(0);" class="btn btn-success me-1" id="printInvoiceBTN"><i
                                        class="fa fa-print"></i> Print Form Pengajuan</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container mt-4">
                    <h3 class="text-center">Pengajuan Catering</h3>
                    @if ($data)
                    <table class="table table-bordered my-4">
                        <tbody>
                            <tr>
                                <td>Hari / Tanggal</td>
                                <td>{{ $data['tanggal_pengajuan'] }}</td>
                            </tr>
                            <tr>
                                <td>Divisi</td>
                                <td>{{ $data['jabatan'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif


                    <table class="table table-bordered my-4">
                        <thead>
                            <tr>
                                <th>Qty</th>
                                <th>Nama Makanan</th>
                                <th>Harga (Rp.)</th>
                                <th>Tipe Catering</th>
                                <th>Vendor</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['detail'] as $detail)
                            <tr>
                                <td>{{ $detail['jumlah'] }}</td>
                                <td>{{ $detail['nama_makanan'] }}</td>
                                <td>{{ $detail['harga'] }}</td>
                                <td>{{ $detail['tipe_detail'] }}</td>
                                <td>{{ $detail['vendor'] }}</td>
                                <td>{{ $detail['keterangan'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @if ($data)
                            <th scope="col" colspan="2">Total</th>
                            <th scope="col" colspan="2">{{ $data['total_harga'] }}</th>
                            @endif
                        </tfoot>
                    </table>
                </div>

                <div class="row text-center mt-5">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-4">
                                <p style="margin-bottom: 4px">Yang Mengajukan</p>
                            </div>
                            <div class="col-sm-4">
                                <p style="margin-bottom: 4px">Menyetujui</p>
                            </div>
                            <div class="col-sm-4">
                                <p style="margin-bottom: 4px">Mengetahui</p>
                            </div>
                        </div>

                        <div class="row signature">
                            <div class="col-sm-4">
                                @if ($data)
                                <img src="{{ asset('storage/ttd/' . $data['ttd']) }}" alt="TTD Karyawan">
                                @else
                                <br>
                                <br>
                                <br>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                @if ($finance)
                                <img src="{{ asset('storage/ttd/' . $finance->ttd) }}" alt="TTD Karyawan">
                                @else
                                <br>
                                <br>
                                <br>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                @if ($gm)
                                <img src="{{ asset('storage/ttd/' . $gm->ttd) }}" alt="TTD Karyawan">
                                @else
                                <br>
                                <br>
                                <br>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-sm-4">
                                @if ($data)
                                <strong>{{ $data['nama_pengaju'] }}</strong>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                @if ($finance)
                                <strong>{{ $finance->nama_lengkap }}</strong>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                @if ($gm)
                                <strong>{{ $gm->nama_lengkap }}</strong>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/85b3409c34.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $('#printInvoiceBTN').on('click', function() {
                window.print();
            });
        });
    </script>
</body>

</html>