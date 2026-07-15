@extends('layouts_office.app')

@section('office_contents')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">PO Exam Sertifa</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#poExamModal">Tambah Data</button>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" id="poExamTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Materi</th>
                                    <th>RKM</th>
                                    <th>Tanggal Exam</th>
                                    <th>Perusahaan</th>
                                    <th>Pax</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->materi->nama_materi ?? '-' }}</td>
                                        <td>{{ $item->rkm->id ?? '-' }}</td>
                                        <td>{{ $item->tanggal_exam ? \Carbon\Carbon::parse($item->tanggal_exam)->format('d M Y') : '-' }}</td>
                                        <td>{{ $item->perusahaan->nama_perusahaan ?? '-' }}</td>
                                        <td>{{ $item->pax ?? '-' }}</td>
                                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPoExamModal{{ $item->id }}">Edit</button>
                                            <form action="{{ route('office.exam.po-exam-sertifa.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editPoExamModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('office.exam.po-exam-sertifa.update', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit PO Exam Sertifa</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Materi</label>
                                                            <select name="id_materi" class="form-select">
                                                                <option value="">- Pilih Materi -</option>
                                                                @foreach($materis as $materi)
                                                                    <option value="{{ $materi->id }}" {{ $item->id_materi == $materi->id ? 'selected' : '' }}>{{ $materi->nama_materi }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">RKM</label>
                                                            <select name="id_rkm" class="form-select">
                                                                <option value="">- Pilih RKM -</option>
                                                                @foreach($rkms as $rkm)
                                                                    <option value="{{ $rkm->id }}" {{ $item->id_rkm == $rkm->id ? 'selected' : '' }}>{{ $rkm->id }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Tanggal Exam</label>
                                                            <input type="date" name="tanggal_exam" class="form-control" value="{{ $item->tanggal_exam }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Perusahaan</label>
                                                            <select name="id_perusahaan" class="form-select">
                                                                <option value="">- Pilih Perusahaan -</option>
                                                                @foreach($perusahaans as $perusahaan)
                                                                    <option value="{{ $perusahaan->id }}" {{ $item->id_perusahaan == $perusahaan->id ? 'selected' : '' }}>{{ $perusahaan->nama_perusahaan }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Pax</label>
                                                            <input type="number" name="pax" class="form-control" value="{{ $item->pax }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Harga</label>
                                                            <input type="number" name="harga" class="form-control" value="{{ $item->harga }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="poExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('office.exam.po-exam-sertifa.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah PO Exam Sertifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Materi</label>
                        <select name="id_materi" class="form-select">
                            <option value="">- Pilih Materi -</option>
                            @foreach($materis as $materi)
                                <option value="{{ $materi->id }}">{{ $materi->nama_materi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RKM</label>
                        <select name="id_rkm" class="form-select">
                            <option value="">- Pilih RKM -</option>
                            @foreach($rkms as $rkm)
                                <option value="{{ $rkm->id }}">{{ $rkm->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Exam</label>
                        <input type="date" name="tanggal_exam" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perusahaan</label>
                        <select name="id_perusahaan" class="form-select">
                            <option value="">- Pilih Perusahaan -</option>
                            @foreach($perusahaans as $perusahaan)
                                <option value="{{ $perusahaan->id }}">{{ $perusahaan->nama_perusahaan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pax</label>
                        <input type="number" name="pax" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="harga" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#poExamTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            }
        });
    });
</script>
@endsection
