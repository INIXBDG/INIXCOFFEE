@extends('layouts_office.app')

@section('office_contents')
    <div class="container mt-4">
        <h4 class="mb-4">Manajemen Pemesanan Modul</h4>

        {{-- Pesan sukses --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Error validasi --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan!</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Tambah Modul --}}
        <div class="card mb-4">
            <div class="card-header">Tambah Pemesanan Modul</div>
            <div class="card-body">
                <form action="{{ route('modul.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor Pemesanan</label>
                            <input type="text" name="nomor" class="form-control" value="{{ $nomorPrefix }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe</label>
                            <select name="tipe" class="form-control tipeSelect" id="tipeSelect0" required>
                                <option value="">-- Pilih --</option>
                                <option value="Regular">Regular</option>
                                <option value="Authorize">Authorize</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Simpan Pemesanan</button>
                </form>
            </div>
        </div>

        {{-- Daftar Modul --}}
        <div class="card">
            <div class="card-header">Daftar Pemesanan Modul</div>
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nomor</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($moduls as $index => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nomor }}</td>
                                <td>{{ $item->tipe }}</td>
                                <td>{{ $item->status ?? 'Menunggu' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#detailModul{{ $item->id }}">
                                        Show
                                    </button>
                                    <button class="btn btn-sm btn-info">
                                        Detail
                                    </button>
                                    <form action="{{ route('modul.delete.nomor', $item->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin ingin menghapus pemesanan ini?')">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data modul.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    @foreach ($moduls as $item)
        <div class="modal fade" id="detailModul{{ $item->id }}" tabindex="-1"
            aria-labelledby="detailModulLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailModulLabel{{ $item->id }}">Detail Pemesanan
                            {{ $item->nomor }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Informasi Pemesanan:</h6>
                        <p><strong>ID:</strong> {{ $item->id }}</p>
                        <p><strong>Nomor:</strong> {{ $item->nomor }}</p>
                        <p><strong>Tipe:</strong> {{ $item->tipe }}</p>
                        <p><strong>Status:</strong> <span
                                class="badge {{ $item->status == 'Disetujui' ? 'bg-success' : 'bg-warning' }}">{{ $item->status ?? 'Menunggu' }}</span>
                        </p>
                        <hr>
                        <h6>Daftar Item:</h6>
                        @if ($item->moduls->count() > 0)
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipe</th>
                                        <th>Nama Materi</th>
                                        <th>Tanggal Training</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Subtotal</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($item->moduls as $modulItem)
                                        <tr>
                                            <td>{{ $modulItem->id }}</td>
                                            <td>{{ $modulItem->tipe }}</td>
                                            <td>{{ $modulItem->nama_materi }}</td>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($modulItem->awal_training)->format('d/m/Y') }} •
                                                {{ \Carbon\Carbon::parse($modulItem->akhir_training)->format('d/m/Y') }}
                                            </td>
                                            <td>{{ $modulItem->jumlah }}</td>
                                            <td>Rp {{ number_format($modulItem->harga_satuan, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($modulItem->subtotal, 0, ',', '.') }}</td>
                                            <td>{{ $modulItem->note ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-semibold">
                                    Total:
                                    <span class="text-success">
                                        Rp {{ number_format($item->moduls->sum('subtotal'), 0, ',', '.') }}
                                    </span>
                                </span>
                            </div>
                        @else
                            <p class="text-muted">Tidak ada item untuk pemesanan ini.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
