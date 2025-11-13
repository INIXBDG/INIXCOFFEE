@extends('layouts.app')

@section('content')
{{-- CSS di-load di sini --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">


<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Registry</h2>
        <a href="{{ route('registry.create') }}" class="btn btn-primary mb-3">Tambah Tugas Baru</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tugas-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tugas</th>
                            <th>Fitur</th>
                            <th>Tipe</th>
                            <th>Pemilik</th>
                            <th>Pengerjaan</th>
                            <th>Status</th>
                            <th>Mulai</th>
                            <th>Akhir</th>
                            <th>Durasi</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar_tugas as $tugas)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $tugas->tugas }}</td>
                                <td>{{ $tugas->fitur }}</td>
                                <td>{{ $tugas->tipe }}</td>
                                <td>{{ $tugas->pemilik }}</td>
                                <td>{{ $tugas->pengerja->karyawan->nama_lengkap }}</td>
                                <td>
                                    <span class="badge
                                        @if($tugas->status == 'Selesai') bg-success
                                        @elseif($tugas->status == 'Antrian') bg-secondary
                                        @else bg-primary @endif">
                                        {{ $tugas->status }}
                                    </span>
                                </td>
                                <td>{{ $tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('d M Y H:i') : '-' }}</td>
                                <td>{{ $tugas->tanggal_akhir ? $tugas->tanggal_akhir->format('d M Y H:i') : '-' }}</td>
                                <td>{{ $tugas->durasi_human }}</td>
                                <td>{{ Str::limit($tugas->catatan, 30) }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton-{{ $tugas->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $tugas->id }}">
                                            @if(is_null($tugas->tanggal_mulai))
                                            <li>
                                                <form action="{{ route('registry.start', $tugas->id) }}" method="POST" style="display: block;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item text-primary">
                                                        <i class="fas fa-play me-2"></i>Mulai Tugas
                                                    </button>
                                                </form>
                                            </li>
                                            @endif

                                            @if(!is_null($tugas->tanggal_mulai) && is_null($tugas->tanggal_akhir))
                                            <li>
                                                <form action="{{ route('registry.finish', $tugas->id) }}" method="POST" style="display: block;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fas fa-check-circle me-2"></i>Tandai Selesai
                                                    </button>
                                                </form>
                                            </li>
                                            @endif

                                            @if(is_null($tugas->tanggal_mulai) || (!is_null($tugas->tanggal_mulai) && is_null($tugas->tanggal_akhir)))
                                            <li><hr class="dropdown-divider"></li>
                                            @endif

                                            {{-- Item Edit --}}
                                            <li>
                                                <a class="dropdown-item" href="{{ route('registry.edit', $tugas->id) }}">
                                                    <i class="fas fa-pencil-alt me-2"></i>Edit
                                                </a>
                                            </li>

                                            {{-- Item Hapus --}}
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('registry.destroy', $tugas->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?');" style="display: block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash-alt me-2"></i>Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">Belum ada data tugas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#tugas-table').DataTable({
        "order": [[ 0, "asc" ]],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        "pageLength": 10,
    });
});
</script>
@endsection
