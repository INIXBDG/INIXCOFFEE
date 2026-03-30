@extends('layouts_crm.app')

@section('crm_contents')
<div class="container mt-3">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
                <div>
                    <h3 class="mb-2 fw-bold text-dark">Data Laporan MoM</h3>
                    <p class="text-muted fs-6 mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <a class="btn btn-primary mb-3" href="{{ route('laporan.harian.create') }}">
        <i class="bx bx-plus"></i> Tambah Laporan
    </a>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="mb-3 fw-bold">Daftar Laporan MoM</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>No</th>
                            <th>Jenis</th>
                            <th>Waktu Pelaksanaan</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th>Topik</th>
                            <th>Jenis Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($laporans as $laporan)
                            
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="row ps-3">
                                        {{ $laporan->jenis_meeting }}
                                        <ul>
                                            @foreach ($laporan->catatanClient as $client)
                                                <li>{{ $client->nama_perusahaan }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($laporan->waktu_pelaksanaan)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($laporan->tanggal_pelaksanaan)->format('l, d F Y') }}</td>
                                <td>{{ $laporan->topic }}</td>
                                <td>
                                    @if (count($laporan->catatanClient) > 0)
                                        <span class="badge bg-info">Catatan Client</span>
                                    @else
                                        <span class="badge bg-secondary">Catatan Sales</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('laporan.harian.edit', $laporan->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('laporan.harian.delete', $laporan->id) }}" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bx bx-trash"></i> Hapus
                                        </button>
                                    </form>
                                    @if (count($laporan->catatanClient) > 0)
                                        <a href="{{ route('laporan.harian.pdf', ['id'=>$laporan->id,'type'=>'client']) }}" class="btn btn-sm btn-success">
                                            <i class="bx bx-file"></i> PDF
                                        </a>
                                    @else
                                        <a href="{{ route('laporan.harian.pdf', ['id'=>$laporan->id,'type'=>'sales']) }}" class="btn btn-sm btn-success">
                                            <i class="bx bx-file"></i> PDF
                                        </a>
                                    @endif
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bx bx-info-circle text-muted" style="font-size:4rem;"></i>
                                    <p class="text-muted mt-3">Tidak ada data laporan</p>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection