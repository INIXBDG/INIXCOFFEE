@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <a href="/feedback" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    @if (auth()->user()->jabatan == 'Sales')
                        <a href="{{ route('feedback.exportExcels', $id) }}" class="btn btn-success">Export to Excel</a>
                        <a href="{{ route('feedback.exportPDFs', $id) }}" class="btn btn-danger">Export to PDF</a>
                    @endif
                <h5 class="card-title">Detail Feedbacks</h5>
                    {{-- {{ $post }} --}}
                        <div class="row" style="height: 500px">
                            <div class="col-lg-5 col-md-12 col-sm-12">
                                <!-- Data Materi dan Pelaksanaan -->
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Nama Materi</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $post['data'][0]['nama_materi'] }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Tanggal Pelaksanaan</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ \Carbon\Carbon::parse($post['data'][0]['tanggal_awal'])->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($post['data'][0]['tanggal_akhir'])->translatedFormat('d F Y') }}</p>
                                    </div>
                                </div>
                                <!-- Instruktur dan Asisten -->
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Instruktur</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $post['data'][0]['instruktur_key'] }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Instruktur 2</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $post['data'][0]['instruktur_key2'] }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Asisten</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $post['data'][0]['asisten_key'] }}</p>
                                    </div>
                                </div>
                                <!-- Perusahaan dan Sales -->
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Perusahaan</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $post['data'][0]['nama_perusahaan'] }}</p>
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
                                        <p>{{ $post['data'][0]['sales_key'] }}</p>
                                    </div>
                                </div>
                            </div>
                    
                            <div class="col-lg-7 col-md-12 col-sm-12">
                                <div class="card" style="height: 500px">
                                    <div class="card-body" style="overflow-y: auto">
                                        <nav>
                                            <div class="nav nav-tabs" id="nav-tab-main" role="tablist">
                                                <!-- Tab Nilai Keseluruhan -->
                                                <button class="nav-link active" 
                                                        id="nav-nilai-tab-main" 
                                                        data-bs-toggle="tab" 
                                                        data-bs-target="#nav-nilai-main" 
                                                        type="button" 
                                                        role="tab" 
                                                        aria-controls="nav-nilai-main" 
                                                        aria-selected="true">
                                                    Nilai Keseluruhan
                                                </button>
                                                <!-- Tab Detail Peserta -->
                                                @foreach($post['data'] as $key => $feedback)
                                                    <button class="nav-link" 
                                                            id="nav-detail-tab-main-{{ $loop->iteration }}" 
                                                            data-bs-toggle="tab" 
                                                            data-bs-target="#nav-detail-main-{{ $loop->iteration }}" 
                                                            type="button" 
                                                            role="tab" 
                                                            aria-controls="nav-detail-main-{{ $loop->iteration }}" 
                                                            aria-selected="false">
                                                        Peserta {{ $loop->iteration }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </nav>
                                        <div class="tab-content" id="nav-tabContent-main">
                                            <!-- Tab Nilai Keseluruhan Content -->
                                            <div class="tab-pane fade show active" 
                                                id="nav-nilai-main" 
                                                role="tabpanel" 
                                                aria-labelledby="nav-nilai-tab-main">
                                                <div class="card">
                                                    <div class="card-body" style="overflow-y: auto">
                                                        <div class="table-responsive">
                                                            <table class="table table-striped table-hover table-bordered table-sm">
                                                                <thead class="thead-dark">
                                                                    <tr>
                                                                        <th>Peserta</th>
                                                                        <th>Materi</th>
                                                                        <th>Pelayanan</th>
                                                                        <th>Fasilitas</th>
                                                                        <th>Instruktur</th>
                                                                        <th>Instruktur 2</th>
                                                                        <th>Asisten</th>
                                                                        {{-- <th>Pengalaman</th>
                                                                        <th>Saran</th> --}}
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($post['data'] as $feedback)
                                                                        <tr>
                                                                            <td>{{ $loop->iteration }}</td>
                                                                            <td>{{ $feedback['materi'] }}</td>
                                                                            <td>{{ $feedback['pelayanan'] }}</td>
                                                                            <td>{{ $feedback['fasilitas'] }}</td>
                                                                            <td>{{ $feedback['instruktur'] }}</td>
                                                                            <td>{{ $feedback['instruktur2'] }}</td>
                                                                            <td>{{ $feedback['asisten'] }}</td>
                                                                            {{-- <td>{{ $feedback['umum1'] }}</td>
                                                                            <td>{{ $feedback['umum2'] }}</td> --}}
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Tab Detail Peserta Content -->
                                            @foreach($post['data'] as $key => $feedback)
                                            
                                                <div class="tab-pane fade" 
                                                    id="nav-detail-main-{{ $loop->iteration }}" 
                                                    role="tabpanel" 
                                                    aria-labelledby="nav-detail-tab-main-{{ $loop->iteration }}">
                                                    <div class="card">
                                                        <div class="card-body">
                                                           <div class="d-flex justify-content-between">
                                                                <h5>Peserta {{ $loop->iteration }}</h5>
                                                                @if (auth()->user()->jabatan == "Programmer" || auth()->user()->jabatan == "Customer Care" || auth()->user()->jabatan == "Admin Holding" )
                                                                    <a href="/nilaifeedback/{{$feedback['datafeedbacks']['id']}}/edit" class="btn click-primary my-2"> Edit</a>  
                                                                @endif
                                                           </div>
                                                           {{-- @php
                                                               dump($feedbacks->first()->rkm->metode_kelas)
                                                           @endphp --}}
                                                            <table class="table table-bordered table-responsive">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="text-align: left;"><h5>Materi</h5></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Sesuai dengan harapan anda</td>
                                                                        <td>{{ $feedback['datafeedbacks']['M1'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Proporsi antara teori dengan praktek</td>
                                                                        <td>{{ $feedback['datafeedbacks']['M2'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Mutu Materi</td>
                                                                        <td>{{ $feedback['datafeedbacks']['M3'] }}</td>
                                                                    </tr>
                                                                    @if ($feedbacks->first()->rkm->metode_kelas == "Offline")
                                                                    <tr>
                                                                        <td style="text-align: left;">Hasil cetakan materi</td>
                                                                        <td>{{ $feedback['datafeedbacks']['M4'] }}</td>
                                                                    </tr>
                                                                    @endif
                                                                    <tr>
                                                                        <td style="text-align: left;"><h5>Pelayanan</h5></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Informasi mudah dan tepat</td>
                                                                        <td>{{ $feedback['datafeedbacks']['P1'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Penyambutan dan pembukaan</td>
                                                                        <td>{{ $feedback['datafeedbacks']['P2'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kenyamanan ruang kelas</td>
                                                                        <td>{{ $feedback['datafeedbacks']['P3'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Keramahan staf</td>
                                                                        <td>{{ $feedback['datafeedbacks']['P4'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kesigapan staf dalam menangani masalah</td>
                                                                        <td>{{ $feedback['datafeedbacks']['P5'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Registrasi dan administrasi training</td>
                                                                        <td>{{ $feedback['datafeedbacks']['P6'] }}</td>
                                                                    </tr>
                                                                    @if ($feedbacks->first()->rkm->metode_kelas == "Offline")                                                                        
                                                                    <tr>
                                                                        <td style="text-align: left;">Kualitas makanan dan minuman</td>
                                                                        <td>{{ $feedback['datafeedbacks']['P7'] }}</td>
                                                                    </tr>
                                                                    @endif
                                                                    <tr>
                                                                        <td style="text-align: left;"><h5>Fasilitas Laboratorium</h5></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Persiapan laboratorium sebelum training dimulai</td>
                                                                        <td>{{ $feedback['datafeedbacks']['F1'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kelengkapan sarana pendukung</td>
                                                                        <td>{{ $feedback['datafeedbacks']['F2'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kondisi peralatan laboratorium selama praktek</td>
                                                                        <td>{{ $feedback['datafeedbacks']['F3'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Penataan instalasi laboratorium</td>
                                                                        <td>{{ $feedback['datafeedbacks']['F4'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kecepatan mengatasi problem di laboratorium</td>
                                                                        <td>{{ $feedback['datafeedbacks']['F5'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;"><h5>Instruktur</h5></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Penguasaan Materi</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I1'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Penyampaian materi jelas dan baik</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I2'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Cara menjawab pertanyaan</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I3'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Cara menanggapi permasalahan dalam kelas</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I4'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kesigapan membantu siswa dalam belajar</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I5'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kepedulian Instruktur/Asisten diluar kelas</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I6'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Instruktur/Asisten mencerminkan profesional image</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I7'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Tepat waktu</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I8'] }}</td>
                                                                    </tr>

                                                                    @if ($feedback['datafeedbacks']['I1b'])
                                                                    <tr>
                                                                        <td style="text-align: left;"><h5>Instruktur 2</h5></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Penguasaan Materi</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I1b'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Penyampaian materi jelas dan baik</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I2b'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Cara menjawab pertanyaan</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I3b'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Cara menanggapi permasalahan dalam kelas</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I4b'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kesigapan membantu siswa dalam belajar</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I5b'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kepedulian Instruktur/Asisten diluar kelas</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I6b'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Instruktur/Asisten mencerminkan profesional image</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I7b'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Tepat waktu</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I8b'] }}</td>
                                                                    </tr>
                                                                    @endif
                                                                    @if ($feedback['datafeedbacks']['I1as'])
                                                                    <tr>
                                                                        <td style="text-align: left;"><h5>Instruktur 2</h5></td>
                                                                        <td></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Penguasaan Materi</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I1as'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Penyampaian materi jelas dan baik</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I2as'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Cara menjawab pertanyaan</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I3as'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Cara menanggapi permasalahan dalam kelas</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I4as'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kesigapan membantu siswa dalam belajar</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I5as'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Kepedulian Instruktur/Asisten diluar kelas</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I6as'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Instruktur/Asisten mencerminkan profesional image</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I7as'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;">Tepat waktu</td>
                                                                        <td>{{ $feedback['datafeedbacks']['I8as'] }}</td>
                                                                    </tr>
                                                                    @endif
                                                                    <tr>
                                                                        <td style="text-align: left;" colspan="2"><h5>Pengalaman yang anda angggap berkesan sewaktu mengikuti training di sini?</h5>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;" colspan="2">{{ $feedback['datafeedbacks']['U1'] }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;" colspan="2"><h5>Saran dan Usulan perbaikan</h5>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="text-align: left;" colspan="2">{{ $feedback['datafeedbacks']['U2'] }}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
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

    .table-responsive {
        margin-top: 20px;
    }

    .table thead th {
        text-align: center;
        vertical-align: middle;
        background-color: #757b81;
        color: white;
    }

    .table tbody td {
        text-align: center;
        vertical-align: middle;
    }

    .table-auto {
        table-layout: auto;
        /* width: auto; */
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
        padding: 7px 20px;
        color: #ffffff;
        text-align: center;
        text-decoration: none;
        margin-left: 10px;
        margin-right: 10px;
    }

    .click-secondary-icon:hover,
    .click-secondary:hover {
        background: #4e6680;
    }

    .click-primary-icon {
        background: #2B3A55;
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

    .click-primary-icon i {
        line-height: 45px;
    }

    .click-primary {
        background: #2B3A55;
        border-radius: 5px;
        padding: 7px 20px;
        color: #ffffff;
        text-align: center;
        text-decoration: none;
        margin-left: 10px;
        margin-right: 10px;
    }

    .click-primary-icon:hover,
    .click-primary:hover {
        background: #43587d;
    }
</style>

@endsection
