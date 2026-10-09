@extends('layouts_office.app')
@section('office_contents')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Rekap Pembelian</h5>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="tableRekap">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
