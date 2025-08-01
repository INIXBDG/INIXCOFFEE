@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Manajemen Aktivitas</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal"
                    onclick="resetForm()">
                    Tambah Aktivitas
                </button>
            </div>

            <!-- Tabel Aktivitas -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Daftar Aktivitas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Kontak</th>
                                    <th>Jenis Aktivitas</th>
                                    <th>Subjek</th>
                                    <th>Deskripsi</th>
                                    <th>Waktu Aktivitas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $aktivitas)
                                    <tr>
                                        <td>{{ $aktivitas->perusahaan->nama_perusahaan ?? '-' }}
                                            ({{ $aktivitas->perusahaan->cp ?? '-' }})
                                        </td>
                                        <td>{{ ucfirst($aktivitas->aktivitas) }}</td>
                                        <td>{{ $aktivitas->subject }}</td>
                                        <td>{{ $aktivitas->deskripsi ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($aktivitas->waktu_aktivitas)->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('delete.aktivitas', $aktivitas->id) }}"
                                                    method="POST">
                                                    @method('DELETE')
                                                    @csrf
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

            <!-- Modal untuk Create Aktivitas -->
            <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="activityModalLabel">Tambah Aktivitas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="activityForm" action="{{ route('store.aktivitas.new') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label" for="id_contact">Kontak Klien</label>
                                    <select class="form-select" id="id_contact" name="id_contact" required>
                                        <option value="" disabled selected>Pilih Kontak</option>
                                        @foreach ($contact as $contact)
                                            <option value="{{ $contact->id }}">{{ $contact->nama_perusahaan }}
                                                ({{ $contact->cp ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="aktivitas">Jenis Aktivitas</label>
                                    <select class="form-select" id="aktivitas" name="aktivitas" required>
                                        <option value="" disabled selected>Pilih Jenis Aktivitas</option>
                                        <option value="Call">Call</option>
                                        <option value="Email">Email</option>
                                        <option value="Visit">Visit</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="subject">Subjek</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="waktu_aktivitas">Waktu Aktivitas</label>
                                    <input type="date" class="form-control" id="waktu_aktivitas" name="waktu_aktivitas"
                                        required>
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetForm() {
            document.getElementById('activityForm').reset();
        }
    </script>
@endsection
