@extends('layouts_office.app')
@section('office_contents')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Perbaikan Data Tugas</h5>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-3 g-2">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Office Boy</label>
                    <select class="form-select" id="filterOfficeBoy">
                        <option value="">-- Semua --</option>
                        @foreach($officeBoy as $ob)
                            <option value="{{ $ob->id }}">{{ $ob->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kategori Tugas</label>
                    <select class="form-select" id="filterKategori">
                        <option value="">-- Semua --</option>
                        @foreach($kategori as $k)
                            <option value="{{ $k->id }}">{{ $k->judul_kategori }} ({{ $k->tipe_turunan }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary w-100" id="btnCari">
                        <i class="fas fa-search me-1"></i> Cari
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="tablePerbaikan">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Office Boy</th>
                            <th>Kategori</th>
                            <th>Tipe</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Klik <strong>Cari</strong> untuk menampilkan data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
