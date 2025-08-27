@extends('layouts_crm.app')

@section('crm_contents')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Contact Client</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal"
                    onclick="resetForm()">
                    Tambah Client
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="picTable" class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>Nama</th>
                                    <th>Perusahaan</th>
                                    <th>Sales Key</th>
                                    <th>Status</th>
                                    <th>Email</th>
                                    <th>CP (no)</th>
                                    <th>Divisi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Tambah Client -->
            <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="clientModalLabel">Tambah Contact Client</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="clientForm" action="{{ route('store.pic') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="id_perusahaan" class="form-label">Perusahaan</label>
                                    <select name="id_perusahaan" id="id_perusahaan" class="form-select" required>
                                        <option value="" disabled selected>Pilih Perusahaan</option>
                                        @foreach ($perusahaans as $perusahaan)
                                            <option value="{{ $perusahaan->id }}">
                                                {{ $perusahaan->nama_perusahaan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Perusahaan wajib dipilih.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Contact</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                    <div class="form-text">Isi jika ada.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="cp" class="form-label">Phone CP</label>
                                    <input type="text" class="form-control" id="cp" name="cp">
                                    <div class="form-text">Nomor kontak.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="divisi" class="form-label">Divisi</label>
                                    <input type="text" class="form-control" id="divisi" name="divisi">
                                    <div class="form-text">Misal: Marketing, Finance, HR.</div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Contact</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- <div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editClientModalLabel">Edit Contact</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('pic.update', $contact->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="id_perusahaan" class="form-label">Perusahaan</label>
                                    <select name="id_perusahaan" id="id_perusahaan" class="form-select" required>
                                        <option value="">Pilih Perusahaan</option>
                                        @foreach($perusahaans as $perusahaan)
                                            <option value="{{ $perusahaan->id }}" {{ $contact->id_perusahaan == $perusahaan->id ? 'selected' : '' }}>
                                                {{ $perusahaan->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="{{ $contact->nama }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $contact->email }}">
                                </div>

                                <div class="mb-3">
                                    <label for="cp" class="form-label">CP (Contact Person)</label>
                                    <input type="text" class="form-control" id="cp" name="cp" value="{{ $contact->cp }}">
                                </div>

                                <div class="mb-3">
                                    <label for="divisi" class="form-label">Divisi</label>
                                    <input type="text" class="form-control" id="divisi" name="divisi" value="{{ $contact->divisi }}">
                                </div>

                                <!-- Status otomatis 1 -->
                                <input type="hidden" name="status" value="1">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> --}}

    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
    $('#picTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('index.json.pic') }}",
            type: 'GET'
        },
        columns: [
            { data: 'nama', name: 'nama' },
            { data: 'perusahaan', name: 'perusahaan' },
            { data: 'sales_key', name: 'sales_key' },
            { data: 'status', name: 'status' },
            { data: 'email', name: 'email' },
            { data: 'cp', name: 'cp' },
            { data: 'divisi', name: 'divisi' },
        ],
        order: [[0, 'asc']],
    });
});
</script>

@endsection
