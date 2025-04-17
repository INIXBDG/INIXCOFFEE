
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="col-md-12 d-flex">
                        {{-- {{ $rkm }} --}}
                        <a href="/exam" class="btn click-primary m-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                        @if ($approvalexam->technical_support == '1')
                        <form action="{{ route('exam.invoice', $rkm->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn click-primary m-2">
                                <img src="{{ asset('icon/printer.svg') }}" class="img-responsive" width="20px"> Print Invoice
                            </button>
                        </form>
                        @endif
                    </div>
                    <h5 class="card-title">Detail Exam</h5>
                    <div class="row">
                        <div class="col-md-5">
                            {{-- {{ $rkm }} --}}
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>ID Exam</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->id }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Invoice</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->invoice }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Tanggal Pengajuan Exam</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ \Carbon\Carbon::parse($rkm->tanggal_pengajuan)->translatedFormat('l, j F Y') }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Nama Materi</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->materi }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Nama Perusahaan</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->perusahaan }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Sales</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->rkm->sales_key }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <h5>Rincian Harga</h5>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Mata Uang</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ $rkm->mata_uang }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Harga</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ $rkm->harga }} {{ $rkm->mata_uang }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Biaya Admin</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>$ {{ $rkm->biaya_admin }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Harga dalam Rupiah</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>
                                            {{ formatRupiah(floatval($harga)) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Biaya Admin dalam Rupiah</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                    <p>{{ formatRupiah(floatval($biaya_admin)) }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Harga Total dalam Rupiah</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ formatRupiah($rkm->harga_rupiah) }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>PAX</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ $rkm->pax }} Peserta</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Total</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ formatRupiah($rkm->total) }}</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-7">
                                <div class="row">
                                    <div class="col-md-12 my-1">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4>Approval Exam</h4>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <th>SPV Sales</th>
                                                        <th>Accounting</th>
                                                        <th>Technical Support</th>
                                                        <th>Status</th>
                                                    </thead>
                                                    <tbody>
                                                        @if($approvalexam)
                                                            @if (auth()->user()->jabatan == 'SPV Sales')
                                                                @if ($approvalexam->spv_sales == '0')
                                                                    <td><a href="{{ route('approvalexam', $approvalexam->id_exam) }}" class="btn btn-primary">Approval</a></td>
                                                                @elseif ($approvalexam->spv_sales == '1')
                                                                    <td>Approve</td>
                                                                @else
                                                                    <td>{{ $approvalexam->spv_sales ? 'Approve' : 'Belum' }}</td>
                                                                @endif
                                                            @else
                                                                <td>{{ $approvalexam->spv_sales ? 'Approve' : 'Belum' }}</td>
                                                            @endif

                                                            @if (auth()->user()->jabatan == 'Finance & Accounting')
                                                                @if ($approvalexam->spv_sales == '1' && $approvalexam->office_manager == '0')
                                                                    <td><a href="{{ route('approvalexam', $approvalexam->id_exam) }}" class="btn btn-primary">Konfirmasi</a></td>
                                                                @elseif ($approvalexam->office_manager == '1')
                                                                    <td>Dikonfirmasi</td>
                                                                @else
                                                                    <td>Belum</td>
                                                                @endif
                                                            @else
                                                                <td>{{ $approvalexam->office_manager ? 'Dikonfirmasi' : 'Belum' }}</td>
                                                            @endif

                                                            @if (auth()->user()->jabatan == 'Technical Support')
                                                                @if ($approvalexam->spv_sales == '1' && $approvalexam->office_manager == '1' && $approvalexam->technical_support == '0')
                                                                    <td><a href="{{ route('approvalexam', $approvalexam->id_exam) }}" class="btn btn-primary">Konfirmasi</a></td>
                                                                @elseif ($approvalexam->technical_support == '1')
                                                                    <td>Dikonfirmasi</td>
                                                                @else
                                                                    <td>Belum</td>
                                                                @endif
                                                            @else
                                                                <td>{{ $approvalexam->technical_support ? 'Dikonfirmasi' : 'Belum' }}</td>
                                                            @endif

                                                            <td>{{ $approvalexam->status }}</td>
                                                        @else
                                                            <td colspan="4">Data tidak tersedia</td>
                                                        @endif

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 my-1">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4>Histori Pengubahan Exam</h4>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Kode Karyawan</th>
                                                            <th>Keterangan</th>
                                                            <th>Status Terakhir</th>
                                                            <th>Tanggal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($exam as $e)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $e->kode_karyawan }}</td>
                                                            <td>{{ $e->keterangan }}</td>
                                                            <td>{{ $e->status }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($e->created_at)->translatedFormat('d F Y H:i:s') }}</td>
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
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    @media screen and (max-width: 768px) {
        .card {
            padding: 15px;
            max-width: 100%;
        }

        .card-body  .row {
            margin-bottom: 10px;
        }

        /* .col-xs-4, */
        .col-xs-1 {
            display: none;
        }

        .col-xs-7 {
            width: 100%;
            text-align: left;
        }
    }

        .cardname {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .click-secondary-icon {
            background:    #355C7C;
            border-radius: 1000px;
            width:         45px;
            height:        45px;
            color:         #ffffff;
            display:       flex;
            justify-content: center;
            align-items:   center;
            text-align:    center;
            text-decoration: none;
        }
        .click-secondary-icon i {
            line-height: 45px;
        }

        .click-secondary {
            background:    #355C7C;
            border-radius: 1000px;
            padding:       10px 25px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }

        .click-secondary:hover {
            color:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .click-warning {
            background:    #f8be00;
            border-radius: 1000px;
            padding:       10px 20px;
            color:         #000000;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear; /
        }

        .click-warning:hover {
            background:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: auto;
            height: auto;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.45);
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(2px);
            }
            .checkmark {
        display: block;
        width: 25px;
        height: 25px;
        border: 1px solid #ccc;
        border-radius: 50%;
        position: relative;
        margin: 0 auto;
    }

    .checkmark:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22bb33;
        display: none;
    }

    tr.selected .checkmark:after {
        display: block;
    }

</style>
@endsection
