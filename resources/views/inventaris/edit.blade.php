@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Pesan Sukses atau Error -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="{{ route('IndexInventaris') }}" class="btn click-primary my-2"><img
                                    src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>

                            <div>
                                <button class="btn btn-success me-2" data-bs-toggle="modal"
                                    data-bs-target="#addPeriodicCheckModal">Tambah Pemeriksaan</button>
                                <button class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#addServiceModal">Tambah Servis</button>
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editPenggunaRuanganModal">Edit Pengguna & Ruangan</button>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Bagian Detail Inventaris -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Informasi Barang</h6>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">ID Barang</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->idbarang }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Nama Barang</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->name }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Kode Barang</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->kodebarang }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Merk / Kode Seri / Kode Hardware</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->merk_kode_seri_hardware }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Qty</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->qty }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Satuan</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->satuan }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Tipe</div>
                                    <div class="col-md-8 col-sm-8">:
                                        {{ $data->type === 'E' ? 'Elektronik' : 'Non-Elektronik' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Harga Beli</div>
                                    <div class="col-md-8 col-sm-8">: Rp {{ number_format($data->harga_beli, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Total Harga</div>
                                    <div class="col-md-8 col-sm-8">: Rp
                                        {{ number_format($data->total_harga, 0, ',', '.') ?? '-' }}
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Tanggal Beli</div>
                                    <div class="col-md-8 col-sm-8">:
                                        {{ \Carbon\Carbon::parse($data->waktu_pembelian)->format('d M Y') }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Pengguna</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->pengguna ?? '-' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Ruangan</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->ruangan ?? '-' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Kondisi</div>
                                    <div class="col-md-8 col-sm-8">: {{ ucfirst(str_replace('/', ' / ', $data->kondisi)) }}
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 col-sm-4 fw-bold">Deskripsi</div>
                                    <div class="col-md-8 col-sm-8">: {{ $data->deskripsi ?? '-' }}</div>
                                </div>
                            </div>

                            <!-- Bagian Riwayat Pemeriksaan Berkala -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Riwayat Pemeriksaan Berkala</h6>
                                <div class="card">
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal Periksa</th>
                                                    <th>Interval</th>
                                                    <th>Kondisi</th>
                                                    <th>Catatan</th>
                                                    <th>Pemeriksa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($data->periodic_checks as $index => $check)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($check->tanggal_pemeriksaan)->format('d M Y') }}
                                                        </td>
                                                        <td>{{ $check->interval === '3bulan' ? '3 Bulan' : '6 Bulan' }}
                                                        </td>
                                                        <td>{{ ucfirst($check->kondisi) }}</td>
                                                        <td>{{ $check->catatan ?? '-' }}</td>
                                                        <td>{{ $check->inspector }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">Belum ada pemeriksaan berkala
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Bagian Riwayat Servis -->
                                <h6 class="mt-4 mb-3">Riwayat Servis</h6>
                                <div class="card">
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal Servis</th>
                                                    <th>Deskripsi</th>
                                                    <th>Biaya</th>
                                                    <th>Pencatat</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($data->services as $index => $service)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($service->tanggal_service)->format('d M Y') }}
                                                        </td>
                                                        <td>{{ $service->deskripsi }}</td>
                                                        <td>Rp {{ number_format($service->harga ?? 0, 0, ',', '.') }}</td>
                                                        <td>{{ $service->user }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">Belum ada riwayat servis</td>
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

                <!-- Modal Tambah Pemeriksaan Berkala -->
                <div class="modal fade" id="addPeriodicCheckModal" tabindex="-1"
                    aria-labelledby="addPeriodicCheckModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addPeriodicCheckModalLabel">Tambah Pemeriksaan Berkala</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ route('AddCheck', ['id' => $data->id]) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="tanggal_pemeriksaan" class="form-label">Tanggal Periksa</label>
                                        <input type="date" class="form-control" id="tanggal_pemeriksaan"
                                            name="tanggal_pemeriksaan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="interval" clas s="form-label">Interval</label>
                                        <select class="form-control" id="interval" name="interval" required>
                                            <option value="3bulan">3 Bulan</option>
                                            <option value="6bulan">6 Bulan</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="kondisi" class="form-label">Kondisi</label>
                                        <select class="form-control" id="kondisi" name="kondisi" required>
                                            <option value="baik">Baik</option>
                                            <option value="rusak/bermasalah">Rusak / Bermasalah</option>
                                            <option value="sedang diperbaiki">Sedang Diperbaiki</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="catatan" class="form-label">Catatan</label>
                                        <textarea class="form-control" id="catatan" name="catatan" rows="4"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Tambah Servis -->
                <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addServiceModalLabel">Tambah Riwayat Servis</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ route('AddService', ['id' => $data->id]) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="tanggal_service" class="form-label">Tanggal Servis</label>
                                        <input type="date" class="form-control" id="tanggal_service"
                                            name="tanggal_service" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="harga" class="form-label">Biaya (Rp)</label>
                                        <input type="number" class="form-control" id="harga" name="harga"
                                            min="0" step="1">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Pengguna & Ruangan -->
                <div class="modal fade" id="editPenggunaRuanganModal" tabindex="-1"
                    aria-labelledby="editPenggunaRuanganModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editPenggunaRuanganModalLabel">Edit Pengguna & Ruangan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ route('UpdatePengguna', ['id' => $data->id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <label for="pengguna" class="form-label">Pengguna</label>
                                            <select class="form-control" id="pengguna" name="pengguna">
                                                <option value="">-- Pilih Pengguna --</option>
                                                @foreach ($usernames as $username)
                                                    <option value="{{ $username }}"
                                                        {{ isset($data) && $data->pengguna == $username ? 'selected' : '' }}>
                                                        {{ $username }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ruangan" class="form-label">Ruangan</label>
                                            <input type="text" class="form-control" id="ruangan" name="ruangan"
                                                value="{{ $data->ruangan ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Validasi sederhana untuk form
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const inputs = form.querySelectorAll('[required]');
                let valid = true;
                inputs.forEach(input => {
                    if (!input.value) {
                        valid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                if (!valid) {
                    e.preventDefault();
                    alert('Harap isi semua kolom wajib.');
                }
            });
        });

        // Pastikan modal backdrop dihapus saat modal ditutup
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('modal-open');
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            });
        });
    </script>
@endsection
