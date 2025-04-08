@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row" style="height: 90vh">
                        <div class="col-md-5">
                            <div class="col-md-12 d-flex">
                                {{-- {{ $rkm }} --}}
                                <a href="/registexam" class="btn click-primary m-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                                <a href="{{ route('registexam.invoice', $registexam->id) }}" class="btn click-primary m-2"><img src="{{ asset('icon/printer.svg') }}" class="img-responsive" width="20px"> Print Invoice</a>
                                {{-- <form action="{{ route('registexam.invoice', $registexam->exam->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn click-primary m-2">
                                        <img src="{{ asset('icon/printer.svg') }}" class="img-responsive" width="20px"> Print Invoice
                                    </button>
                                </form> --}}
                            </div>
                            <h5 class="card-title">Detail Hasil Exam</h5>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Nama Peserta</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $peserta->nama }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Email</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $registexam->email_exam }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Perusahaan</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $peserta->perusahaan->nama_perusahaan }}</p>
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
                                    <p>{{ $registexam->exam->materi }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Hasil Exam</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $post->hasil }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Keterangan</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $post->keterangan }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Kode Exam</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $registexam->kode_exam }}</p>
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
                                    <p>{{ $rkm->sales_key }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Status Pembayaran</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    @if ($registexam->status_pembayaran == '0') 
                                    <p>Sudah</p>
                                    @else
                                    <p>Belum</p>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>CC</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    @if ($registexam->creditcard) 
                                    <p>{{$registexam->creditcard->nama_pemilik}}</p>
                                    @else
                                    <p>-</p>
                                    @endif
                                </div>
                            </div>
                            {{-- <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Hasil Exam dalam bentuk PDF</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    @if (!$post->pdf)
                                        <p>Tidak Ada</p>
                                    @else
                                        <a href="{{ asset('storage/hasilexam/' . $post->pdf) }}" class="btn click-primary" target="_blank">Lihat Hasil</a>
                                    @endif
                                </div>
                            </div> --}}
                        </div>
                        <div class="col-md-7">
                            {{-- Menampilkan PDF di sini --}}
                            @if ($post->pdf)
                                <iframe src="{{ asset('storage/hasilexam/' . $post->pdf) }}" width="100%" height="800px"></iframe>
                            @else
                                <p>Hasil Exam tidak tersedia dalam bentuk PDF.</p>
                            @endif
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
        .card-body .row {
            margin-bottom: 10px;
        }
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
        background: #355C7C;
        border-radius: 1000px;
        width: 45px;
        height: 45px;
        color: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        text-decoration: none;
    }
    .click-secondary-icon i {
        line-height: 45px;
    }
    .click-secondary {
        background: #355C7C;
        border-radius: 1000px;
        padding: 10px 25px;
        color: #ffffff;
        display: inline-block;
        font: normal bold 18px/1 "Open Sans", sans-serif;
        text-align: center;
        transition: color 0.1s linear, background-color 0.2s linear;
    }
    .click-secondary:hover {
        color: #A5C7EF;
        transition: color 0.1s linear, background-color 0.2s linear;
    }
    .click-warning {
        background: #f8be00;
        border-radius: 1000px;
        padding: 10px 20px;
        color: #000000;
        display: inline-block;
        font: normal bold 18px/1 "Open Sans", sans-serif;
        text-align: center;
        transition: color 0.1s linear, background-color 0.2s linear;
    }
    .click-warning:hover {
        background: #A5C7EF;
        transition: color 0.1s linear, background-color 0.2s linear;
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
