@extends('layouts_crm.app')

@section('crm_contents')
    <div class="container mt-4">
        <h2>Manajemen Target Aktivitas</h2>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Create Button (Aligned Right) -->
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                Tambah Target Aktivitas
            </button>
        </div>

        <!-- Table -->
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Sales</th>
                    <th>DB</th>
                    <th>Contact</th>
                    <th>Call</th>
                    <th>Visit</th>
                    <th>Email</th>
                    <th>Meet</th>
                    <th>Incharge</th>
                    <th>PA</th>
                    <th>PI</th>
                    <th>Telemarketing</th>
                    <th>Form Masuk</th>
                    <th>Form Keluar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($target as $activity)
                    <tr>
                        <td>{{ $activity->id_sales }}</td>
                        <td>{{ $activity->DB }}</td>
                        <td>{{ $activity->Contact }}</td>
                        <td>{{ $activity->Call }}</td>
                        <td>{{ $activity->Visit }}</td>
                        <td>{{ $activity->Email }}</td>
                        <td>{{ $activity->Meet }}</td>
                        <td>{{ $activity->Incharge }}</td>
                        <td>{{ $activity->PA }}</td>
                        <td>{{ $activity->PI }}</td>
                        <td>{{ $activity->Telemarketing }}</td>
                        <td>{{ $activity->FormM }}</td>
                        <td>{{ $activity->FormK }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                data-bs-target="#editModal{{ $activity->id }}">Edit</button>
                            <form action="{{ route('index.target.delete', $activity->id) }}" method="POST"
                                style="display: inline-block;"
                                onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $activity->id }}" tabindex="-1"
                        aria-labelledby="editModalLabel{{ $activity->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel{{ $activity->id }}">Edit Target Aktivitas
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{ route('index.target.update', $activity->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="Sales{{ $activity->id }}" class="form-label">Sales</label>
                                            <select name="id_sales" id="Sales{{ $activity->id }}" class="form-control"
                                                required>
                                                <option value="" disabled>-- Pilih Sales --</option>
                                                @foreach ($user as $item)
                                                    <option value="{{ $item->id_sales }}"
                                                        {{ $activity->id_sales == $item->id_sales ? 'selected' : '' }}>
                                                        {{ $item->id_sales }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Contact{{ $activity->id }}" class="form-label">DB</label>
                                            <input type="number" class="form-control" id="Contact{{ $activity->id }}"
                                                name="DB" value="{{ $activity->DB }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Contact{{ $activity->id }}" class="form-label">Contact</label>
                                            <input type="number" class="form-control" id="Contact{{ $activity->id }}"
                                                name="Contact" value="{{ $activity->Contact }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Call{{ $activity->id }}" class="form-label">Call</label>
                                            <input type="number" class="form-control" id="Call{{ $activity->id }}"
                                                name="Call" value="{{ $activity->Call }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Visit{{ $activity->id }}" class="form-label">Visit</label>
                                            <input type="number" class="form-control" id="Visit{{ $activity->id }}"
                                                name="Visit" value="{{ $activity->Visit }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Email{{ $activity->id }}" class="form-label">Email</label>
                                            <input type="number" class="form-control" id="Email{{ $activity->id }}"
                                                name="Email" value="{{ $activity->Email }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Meet{{ $activity->id }}" class="form-label">Meet</label>
                                            <input type="number" class="form-control" id="Meet{{ $activity->id }}"
                                                name="Meet" value="{{ $activity->Meet }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Incharge{{ $activity->id }}" class="form-label">Incharge</label>
                                            <input type="number" class="form-control" id="Incharge{{ $activity->id }}"
                                                name="Incharge" value="{{ $activity->Incharge }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Incharge{{ $activity->id }}" class="form-label">Incharge</label>
                                            <input type="number" class="form-control" id="PA{{ $activity->id }}"
                                                name="PA" value="{{ $activity->PA }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Incharge{{ $activity->id }}" class="form-label">Incharge</label>
                                            <input type="number" class="form-control" id="PI{{ $activity->id }}"
                                                name="PI" value="{{ $activity->PI }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Incharge{{ $activity->id }}" class="form-label">Incharge</label>
                                            <input type="number" class="form-control"
                                                id="Telemarketing{{ $activity->id }}" name="Telemarketing"
                                                value="{{ $activity->Telemarketing }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Incharge{{ $activity->id }}" class="form-label">Incharge</label>
                                            <input type="number" class="form-control" id="FormM{{ $activity->id }}"
                                                name="FormM" value="{{ $activity->FormM }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Incharge{{ $activity->id }}" class="form-label">Incharge</label>
                                            <input type="number" class="form-control" id="FormK{{ $activity->id }}"
                                                name="FormK" value="{{ $activity->FormK }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>

        <!-- Create Modal -->
        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Tambah Target Aktivitas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('index.target.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="id_sales" class="form-label">Sales</label>
                                <select name="id_sales" id="id_sales" class="form-control">
                                    <option value="" disabled selected>-- Pilih Sales --</option>
                                    @foreach ($user as $item)
                                        <option value="{{ $item->id_sales }}">{{ $item->id_sales }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="Contact" class="form-label">DB</label>
                                <input type="number" class="form-control" id="DB" name="DB" required>
                            </div>
                            <div class="mb-3">
                                <label for="Contact" class="form-label">Contact</label>
                                <input type="number" class="form-control" id="Contact" name="Contact" required>
                            </div>
                            <div class="mb-3">
                                <label for="Call" class="form-label">Call</label>
                                <input type="number" class="form-control" id="Call" name="Call" required>
                            </div>
                            <div class="mb-3">
                                <label for="Visit" class="form-label">Visit</label>
                                <input type="number" class="form-control" id="Visit" name="Visit" required>
                            </div>
                            <div class="mb-3">
                                <label for="Email" class="form-label">Email</label>
                                <input type="number" class="form-control" id="Email" name="Email" required>
                            </div>
                            <div class="mb-3">
                                <label for="Meet" class="form-label">Meet</label>
                                <input type="number" class="form-control" id="Meet" name="Meet" required>
                            </div>
                            <div class="mb-3">
                                <label for="Incharge" class="form-label">Incharge</label>
                                <input type="number" class="form-control" id="Incharge" name="Incharge" required>
                            </div>
                            <div class="mb-3">
                                <label for="Incharge" class="form-label">Penawaran Awal</label>
                                <input type="number" class="form-control" id="PA" name="PA" required>
                            </div>
                            <div class="mb-3">
                                <label for="Incharge" class="form-label">Penawaran Internal</label>
                                <input type="number" class="form-control" id="PI" name="PI" required>
                            </div>
                            <div class="mb-3">
                                <label for="Incharge" class="form-label">Telemarketing</label>
                                <input type="number" class="form-control" id="Telemarketing" name="Telemarketing"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="Incharge" class="form-label">Form Masuk</label>
                                <input type="number" class="form-control" id="FormM" name="FormM" required>
                            </div>
                            <div class="mb-3">
                                <label for="Incharge" class="form-label">Form Keluar</label>
                                <input type="number" class="form-control" id="FormK" name="FormK" required>
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
    </div>
@endsection
