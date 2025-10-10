@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Manajemen Gaji Karyawan</h1>

        <!-- Table for Listing -->
        <div class="card shadow-sm">
            <div class="card-header">Daftar Gaji</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center">No</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Jumlah Gaji</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($karyawan as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->karyawan->nama_lengkap }}</td>
                                    <td>Rp {{ number_format($item->karyawan->gaji, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editGajiModal"
                                                data-id="{{ $item->id }}" data-gaji="{{ $item->gaji }}">Edit</button>
                                        <form style="display:inline;" action="{{ route('gaji.destroy', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Yakin ingin menghapus gaji ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada data gaji tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editGajiModal" tabindex="-1" aria-labelledby="editGajiModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editGajiModalLabel">Edit Gaji</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editGajiForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="jumlah_gaji" class="form-label fw-bold">Jumlah Gaji (Rp)</label>
                                <input type="number" class="form-control @error('jumlah_gaji') is-invalid @enderror" 
                                       id="jumlah_gaji" name="jumlah_gaji" 
                                       min="0" step="1" required aria-describedby="jumlah_gaji_help">
                                <div id="jumlah_gaji_help" class="form-text">Masukkan jumlah gaji dalam Rupiah tanpa tanda titik atau koma.</div>
                                @error('jumlah_gaji')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editModal = document.getElementById('editGajiModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const gaji = button.getAttribute('data-gaji');
                
                const form = document.getElementById('editGajiForm');
                const inputGaji = form.querySelector('#jumlah_gaji');
                
                form.action = '{{ url("gaji") }}/' + id;
                inputGaji.value = gaji || '';
            });
        });
    </script>
@endsection