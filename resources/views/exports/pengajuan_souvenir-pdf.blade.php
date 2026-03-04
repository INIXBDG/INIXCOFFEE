<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Permintaan Souvenir</title>

    {{-- Ambil asset untuk PDF --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Arial', sans-serif; }
        .container { width: 90%; margin: 0 auto; }
        .table-bordered { border-color: #000 !important; }
        .table-bordered th, .table-bordered td { border-color: #000 !important; }
        .panel-body { padding: 15px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .m-0 { margin: 0; }
        .my-24 { margin-top: 24px; margin-bottom: 24px; }
        .my-4 { margin-top: 4px; margin-bottom: 4px; }
        .small { font-size: 0.85rem; }
        .img-responsive { max-width: 100%; height: auto; }

        /* Gaya Khusus Print */
        @media print {
            .d-print-none { display: none !important; }
            .col-sm-4 { width: 33.33333%; float: left; }
            .col-sm-6 { width: 50%; float: left; }
            .text-center { text-align: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 text-left" style="width: 50%; float: left;">
                        <img src="{{ asset('css/logo.png') }}" class="img-responsive" width="100px">
                        <h5 class="m-0">INIXINDO BANDUNG<br></h5>
                        <span class="small">Jl. Cipaganti no.95 Bandung</span>
                    </div>

                    <div class="col-sm-6 text-right" style="width: 50%; float: left; text-align: right;">
                        <div class="panel panel-default d-print-none" style="margin-top: 15px;">
                            <a href="javascript:void(0);" class="btn btn-success me-1" id="printInvoiceBTN"><i class="fa fa-print"></i> Print Form Permintaan</a>
                        </div>
                    </div>
                </div>

                <div class="container my-5">
                    <h3 class="text-center">Form Permintaan Souvenir</h3>

                    <table class="table table-bordered my-4">
                        <tbody>
                            <tr>
                                <td style="width: 25%">Hari / Tanggal</td>
                                <td>{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td>Divisi / Jabatan</td>
                                <td>{{$data->karyawan->divisi}} / {{$data->karyawan->jabatan}}</td>
                            </tr>
                            <tr>
                                <td>Vendor</td>
                                <td>{{$data->vendor->nama ?? 'N/A'}}</td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table table-bordered my-4">
                        <thead>
                            <tr>
                                <th style="width: 10%">No</th>
                                <th>Nama Souvenir</th>
                                <th style="width: 15%">Pax (Qty)</th>
                                <th style="width: 20%">Harga Satuan</th>
                                <th style="width: 20%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalHarga = 0;
                            @endphp
                            @foreach ($data->detail as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$item->souvenir->nama_souvenir ?? 'N/A'}}</td>
                                    <td>{{$item->pax}}</td>
                                    <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->harga_total, 0, ',', '.') }}</td>
                                </tr>
                                @php
                                    $totalHarga += $item->harga_total;
                                @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="col" colspan="4" class="text-end">Total Keseluruhan</th>
                                <th scope="col" colspan="1">Rp {{ number_format($totalHarga, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row text-center my-5" style="display: flex; justify-content: space-between;">
                    <div class="col-sm-4" style="width: 33%; float: left;">
                        <p style="margin-bottom: 4px">Yang Mengajukan</p>
                        <br>
                        @if ($data->karyawan->ttd)
                            <img src="{{ asset('storage/ttd/' . $data->karyawan->ttd) }}" alt="{{ $data->karyawan->nama_lengkap }}" style="width: 155px;height:auto">
                        @else
                            <br><br><br>
                        @endif
                        <p style="text-decoration: underline; margin-top: 5px;">{{ $data->karyawan->nama_lengkap }}</p>
                    </div>

                    <div class="col-sm-4" style="width: 33%; float: left;">
                        <p style="margin-bottom: 4px">Menyetujui</p>
                        <br>
                        @if ($penyetuju->ttd ?? null)
                            <img src="{{ asset('storage/ttd/' . $penyetuju->ttd) }}" alt="{{ $penyetuju->nama_lengkap }}" style="width: 155px;height:auto">
                        @else
                            <br><br><br>
                        @endif
                        <p style="text-decoration: underline; margin-top: 5px;">{{ $penyetuju->nama_lengkap ?? 'N/A' }}</p>
                    </div>

                    <div class="col-sm-4" style="width: 33%; float: left;">
                        <p style="margin-bottom: 4px">Mengetahui</p>
                        <br>
                        @if ($penyetuju->ttd ?? null)
                            <img src="{{ asset('storage/ttd/' . $penyetuju->ttd) }}" alt="{{ $penyetuju->nama_lengkap }}" style="width: 155px;height:auto">
                        @else
                            <br><br><br>
                        @endif
                        <p style="text-decoration: underline; margin-top: 5px;">{{ $penyetuju->nama_lengkap ?? 'N/A' }}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#printInvoiceBTN').on('click', function() {
                window.print();
            });
        });
    </script>
</body>
</html>
