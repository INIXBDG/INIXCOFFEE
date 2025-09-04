@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>
    <div class="container">
        <a href="{{ url()->previous() }}" class="btn btn-primary mb-3">
            <img src="{{ asset('icon/arrow-left.svg') }}" class="me-2" width="18px"> Kembali
        </a>

        <div class="row">
            <div class="col-sm">
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1 mb-5">Detail Laporan Insiden</h3>
                        <div id="content">
                            <div class="card">
                                <div class="card-body p-3">
                                    @if ($dataLaporan)
                                    <form>
                                        <div class="form-group mb-3">
                                            <label for="nama">Nama Pelapor</label>
                                            <input type="text" class="form-control" value="{{ $dataLaporan->Pelapor->nama_lengkap }}" readonly>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="kejadian">Kejadian</label>
                                            <input type="text" class="form-control" value="{{ $dataLaporan->kejadian }}" readonly>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="deskripsi">Deskripsi</label>
                                            <textarea id="deskripsi" class="form-control" readonly>{{ $dataLaporan->deskripsi }}</textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="nama">Kategori</label>
                                            <input type="text" class="form-control" value="{{ $dataLaporan->kategori }}" readonly>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="tanggal">Tanggal Kejadian</label>
                                            <input type="date" class="form-control" value="{{ $dataLaporan->tanggal_kejadian }}" readonly>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="waktu">Waktu Kejadian</label>
                                            <input type="time" class="form-control" value="{{ $dataLaporan->waktu_kejadian }}" readonly>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="nama">Status</label>
                                            <input type="text" class="form-control" value="{{ $dataLaporan->status }}" readonly>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="nama">Catatan</label>
                                            <textarea id="catatan" class="form-control" readonly>{{ $dataLaporan->catatan }}</textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="lampiran">Lampiran</label>
                                            <img src="{{ asset('storage/' . $dataLaporan->lampiran) }}" alt="Lampiran" style="max-width:300px">
                                        </div>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm">
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1 mb-5">Data Tracking</h3>
                        <div id="content">
                            <div class="card">
                                <div class="card-body p-3">
                                    <table id="exampleTable" class="table table-bordered table-striped align-middle text-center" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Responder</th>
                                                <th>Solusi</th>
                                                <th>Tanggal Respon</th>
                                                <th>Waktu Respon</th>
                                                <th>Status</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="content_Tbody">
                                            @foreach ($dataTrackingLaporan as $item)
                                            @php
                                            $no = 1;
                                            @endphp
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td>{{ $item->karyawan->nama_lengkap ?? '_' }}</td>
                                                <td>{{ $item->solusi }}</td>
                                                <td>{{ $item->tanggal_response }}</td>
                                                <td>{{ $item->waktu_response }}</td>
                                                <td>{{ $item->status }}</td>
                                                <td>{{ $item->keterangan }}</td>
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
<style>
    .loader {
        position: relative;
        text-align: center;
        margin: 15px auto 35px auto;
        z-index: 9999;
        display: block;
        width: 80px;
        height: 80px;
        border: 10px solid rgba(0, 0, 0, .3);
        border-radius: 50%;
        border-top-color: #000;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    textarea {
        width: 300px;
        height: 150px;
    }

    .modal-content {
        border-radius: 6px;
        box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }

    .modal-backdrop.show {
        opacity: 0.75;
    }

    table.dataTable thead th,
    table.dataTable tbody td {
        font-size: 12px !important;
    }

    #content {
        font-size: 14px;
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
@endpush
@endsection