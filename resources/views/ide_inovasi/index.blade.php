@extends('layouts.app')

@section('content')
<div class="container-fluid">

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show m-4" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Ide Inovasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('ide-inovasi.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nama_karyawan" class="form-label">Nama Karyawan</label>
                            <input type="text" class="form-control" id="nama_karyawan" value="{{ auth()->user()->username ?? auth()->user()->name }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="jabatan" class="form-label">Jabatan</label>
                            <input type="text" class="form-control" id="jabatan" value="{{ auth()->user()->jabatan }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Inovasi</label>
                            <input type="text" class="form-control" id="judul" name="judul" placeholder="Contoh. Pengembangan sistem Inixcoffe" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Ubah Ide Inovasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEdit" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="edit_judul" class="form-label">Judul Inovasi</label>
                            <input type="text" class="form-control" id="edit_judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Perbarui Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-md btn-primary mx-4" data-bs-toggle="modal" data-bs-target="#createModal">
                    Tambah Ide Inovasi
                </button>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Ide Inovasi') }}</h3>
                    <table class="table table-striped" id="ideInovasiTable">
                        <thead>
                            <tr>
                                <th scope="col" width="5%">No</th>
                                <th scope="col" width="15%">Nama Lengkap</th>
                                <th scope="col" width="15%">Jabatan</th>
                                <th scope="col" width="20%">Judul</th>
                                <th scope="col" width="30%">Deskripsi</th>
                                <th scope="col" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ideInovasis as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->karyawan->nama_lengkap ?? '-' }}</td>
                                <td>{{ $item->karyawan->jabatan ?? '-' }}</td>
                                <td>{{ $item->judul }}</td>
                                <td>{{ $item->deskripsi }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton{{ $item->id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $item->id }}">
                                            <button type="button" class="dropdown-item" onclick="openEditModal({{ $item }})">
                                                Ubah Data
                                            </button>

                                            <form onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" action="{{ route('ide-inovasi.destroy', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    Hapus Data
                                                </button>
                                            </form>
                                        </div>
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
</div>

<style>
    .modal-content {
        border-radius: 0px;
        box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }
    .modal-backdrop.show {
        opacity: 0.75;
    }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<script>
    $(document).ready(function() {
        $('#ideInovasiTable').DataTable({
            "order": [[0, 'asc']]
        });
    });

    function openEditModal(data) {
        var updateUrl = "{{ url('/ide-inovasi') }}/" + data.id;
        $('#formEdit').attr('action', updateUrl);
        $('#edit_judul').val(data.judul);
        $('#edit_deskripsi').val(data.deskripsi);
        $('#editModal').modal('show');
    }
</script>
@endpush
@endsection
