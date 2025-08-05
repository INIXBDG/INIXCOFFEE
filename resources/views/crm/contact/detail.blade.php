@extends('layouts_crm.app')
@section('crm_contents')
    <div class="container py-4">
        <!-- Section: Perusahaan & Peluang (Side by Side) -->
        <div class="row g-4 mb-4">
            <!-- Card: Data Perusahaan -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="card-title h4 fw-bold mb-3">Data Perusahaan</h2>
                        <dl class="row mb-3">
                            <dt class="col-sm-4">Nama Perusahaan</dt>
                            <dd class="col-sm-8">{{ $data->nama_perusahaan }}</dd>

                            <dt class="col-sm-4">Kategori</dt>
                            <dd class="col-sm-8">{{ $data->kategori_perusahaan ?? '-' }}</dd>

                            <dt class="col-sm-4">Lokasi</dt>
                            <dd class="col-sm-8">{{ $data->lokasi ?? '-' }}</dd>

                            <dt class="col-sm-4">Sales Key</dt>
                            <dd class="col-sm-8">{{ $data->sales_key ?? '-' }}</dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">{{ $data->status ?? '-' }}</dd>

                            <dt class="col-sm-4">NPWP</dt>
                            <dd class="col-sm-8">{{ $data->npwp ?? '-' }}</dd>

                            <dt class="col-sm-4">Alamat</dt>
                            <dd class="col-sm-8">{{ $data->alamat ?? '-' }}</dd>

                            <dt class="col-sm-4">Contact Person</dt>
                            <dd class="col-sm-8">{{ $data->cp ?? '-' }}</dd>

                            <dt class="col-sm-4">No. Telp</dt>
                            <dd class="col-sm-8">{{ $data->no_telp ?? '-' }}</dd>

                            <dt class="col-sm-4">Foto NPWP</dt>
                            <dd class="col-sm-8">{{ $data->foto_npwp ?? '-' }}</dd>

                            <dt class="col-sm-4">Created At</dt>
                            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') }}
                            </dd>

                            <dt class="col-sm-4">Updated At</dt>
                            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($data->updated_at)->translatedFormat('d F Y') }}
                            </dd>
                        </dl>
                        <div class="text-end">
                            <a href="#" class="btn btn-primary btn-sm">Edit</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Data Peluang -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="card-title h4 fw-bold mb-0">Data Peluang</h2>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#tambahLeadModal">
                                Tambah Lead
                            </button>
                        </div>
                        <p class="mb-3"><strong>Total Final:</strong> Rp
                            {{ number_format($peluang->sum('final'), 2, ',', '.') }}</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" class="px-3 py-2">Materi</th>
                                        <th scope="col" class="px-3 py-2">Periode</th>
                                        <th scope="col" class="px-3 py-2 text-center">Pax</th>
                                        <th scope="col" class="px-3 py-2 text-center">Tahap</th>
                                        <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($peluang as $item)
                                        <tr>
                                            <td class="px-3 py-2">{{ $item->materi }}</td>
                                            <td class="px-3 py-2">
                                                {{ \Carbon\Carbon::parse($item->periode_mulai)->translatedFormat('d F Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($item->periode_selesai)->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-3 py-2 text-center">{{ $item->pax }}</td>
                                            <td
                                                class="px-3 py-2 text-center {{ $item->tahap == 'merah' ? 'bg-danger text-white' : ($item->tahap == 'biru' ? 'bg-primary text-white' : ($item->tahap == 'hitam' ? 'bg-dark text-white' : 'bg-secondary text-white')) }}">
                                                {{ strtoupper($item->tahap) }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal" data-bs-target="#editAktivitasModal"
                                                        onclick='editAktivitas(@json($item))'>
                                                        Detail
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal: Tambah Lead -->
                <div class="modal fade" id="tambahLeadModal" tabindex="-1" aria-labelledby="tambahLeadModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tambahLeadModalLabel">Tambah Lead</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="#" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="materi" class="form-label">Materi</label>
                                        <input type="text" class="form-control" id="materi" name="materi" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="periode_mulai" class="form-label">Periode Mulai</label>
                                        <input type="date" class="form-control" id="periode_mulai" name="periode_mulai"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="periode_selesai" class="form-label">Periode Selesai</label>
                                        <input type="date" class="form-control" id="periode_selesai"
                                            name="periode_selesai" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="pax" class="form-label">Jumlah Peserta (Pax)</label>
                                        <input type="number" class="form-control" id="pax" name="pax"
                                            min="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tahap" class="form-label">Tahap</label>
                                        <select class="form-select" id="tahap" name="tahap" required>
                                            <option value="merah">Merah</option>
                                            <option value="biru">Biru</option>
                                            <option value="hitam">Hitam</option>
                                            <option value="lost">Lost</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Aktivitas -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title h4 fw-bold mb-3">Data Aktivitas</h2>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="px-3 py-2 text-center">ID Sales</th>
                                <th scope="col" class="px-3 py-2 text-center">ID Contact</th>
                                <th scope="col" class="px-3 py-2 text-center">ID Peluang</th>
                                <th scope="col" class="px-3 py-2">Aktivitas</th>
                                <th scope="col" class="px-3 py-2">Subject</th>
                                <th scope="col" class="px-3 py-2">Deskripsi</th>
                                <th scope="col" class="px-3 py-2">Waktu Aktivitas</th>
                                <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aktivitas as $item)
                                <tr>
                                    <td class="px-3 py-2 text-center">{{ $item->id_sales }}</td>
                                    <td class="px-3 py-2 text-center">{{ $item->id_contact }}</td>
                                    <td class="px-3 py-2 text-center">{{ $item->id_peluang ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $item->aktivitas }}</td>
                                    <td class="px-3 py-2">{{ $item->subject }}</td>
                                    <td class="px-3 py-2">{{ $item->deskripsi ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        {{ \Carbon\Carbon::parse($item->waktu_aktivitas)->translatedFormat('d F Y H:i') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#editAktivitasModal"
                                                onclick='editAktivitas(@json($item))'>
                                                Edit
                                            </button>
                                            <form action="" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus?')"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
