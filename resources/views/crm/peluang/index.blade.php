@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Manajemen Peluang</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#opportunityModal"
                    onclick="resetForm()">
                    Tambah Peluang
                </button>
            </div>

            <!-- Tabel Peluang -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Daftar Peluang</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Judul</th>
                                    <th>Deskripsi</th>
                                    <th>Jumlah (Rp)</th>
                                    <th>Tahap</th>
                                    <th>Tanggal Tutup Diharapkan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $peluang)
                                    <tr>
                                        <td>{{ $peluang['judul'] }}</td>
                                        <td>{{ $peluang['deskripsi'] }}</td>
                                        <td>{{ number_format($peluang['jumlah'], 2, ',', '.') }}</td>
                                        <td>{{ $peluang['tahap'] }}</td>
                                        <td>{{ $peluang['tanggal_tutup_diharapkan'] }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('detail.peluang', ['id' => $peluang->id]) }}"
                                                    type="button" class="btn btn-sm btn-warning">
                                                    Detail
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="deletepeluang({{ $peluang['id'] }})">
                                                    Hapus
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

            <!-- Modal untuk Create Peluang -->
            <div class="modal fade" id="opportunityModal" tabindex="-1" aria-labelledby="opportunityModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="opportunityModalLabel">Tambah Peluang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="opportunityForm" action="{{ route('store.peluang') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" id="opportunity_id">

                                <div class="mb-3">
                                    <label class="form-label" for="id_contact">Contact Client</label>
                                    <select class="form-select" id="id_contact" name="id_contact" required>
                                        <option value="" disabled selected>Pilih Contact</option>
                                        @foreach ($contact as $c)
                                            <option value="{{ $c->id }}">{{ $c->nama_lengkap }}
                                                ({{ $c->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="judul">Judul</label>
                                    <select class="form-control" id="judul" name="judul" required>
                                        <option value="" disabled selected>Pilih Judul</option>
                                        @foreach ($materi as $item)
                                            <option value="{{ $item->nama_materi }}">{{ $item->nama_materi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="jumlah">Jumlah (Rp)</label>
                                    <input type="number" step="0.01" class="form-control" id="jumlah"
                                        name="jumlah" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="tahap">Tahap</label>
                                    <select class="form-select" id="tahap" name="tahap">
                                        <option value="">-- Pilih Tahap --</option>
                                        <option value="Prospek">Prospek</option>
                                        <option value="Kualifikasi">Kualifikasi</option>
                                        <option value="Proposal">Proposal</option>
                                        <option value="Negosiasi">Negosiasi</option>
                                        <option value="Ditutup Menang">Ditutup Menang</option>
                                        <option value="Ditutup Kalah">Ditutup Kalah</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="probabilitas">Probabilitas (%)</label>
                                    <input type="number" min="0" max="100" class="form-control"
                                        id="probabilitas" name="probabilitas" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="tanggal_tutup_diharapkan">Tanggal Tutup
                                        Diharapkan</label>
                                    <input type="date" class="form-control" id="tanggal_tutup_diharapkan"
                                        name="tanggal_tutup_diharapkan" />
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
