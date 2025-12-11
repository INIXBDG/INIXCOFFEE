@extends('layouts_office.app')

@section('office_contents')
    <div class="container">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between mb-3">
            <h4>Data Nomor Modul</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                Tambah Nomor Modul
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>No Modul</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th width="20%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($nomor as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->no_modul }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->status }}</td>
                                <td>
                                    <a href="{{ route('office.modul.detail', ['id' => $item->id]) }}"
                                        class="btn btn-info btn-sm">Detail</a>

                                    <button class="btn btn-warning btn-sm editBtn" data-id="{{ $item->id }}"
                                        data-no="{{ $item->no_modul }}" data-type="{{ $item->type }}"
                                        data-status="{{ $item->status }}" data-bs-toggle="modal"
                                        data-bs-target="#editModal">
                                        Edit
                                    </button>

                                    <form action="{{ route('office.modul.delete.nomor', ['id' => $item->id]) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus?')">
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('office.modul.store.nomor') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Nomor Modul</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>No Modul</label>
                        <input type="text" name="no_modul" class="form-control" value="{{ $noModul ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-select">
                            <option value="Regular">Regular</option>
                            <option value="Authorize">Authorize</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editForm" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Nomor Modul</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>No Modul</label>
                        <input type="text" id="edit_no_modul" name="no_modul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Type</label>
                        <select id="edit_type" name="type" class="form-select">
                            <option value="Regular">Regular</option>
                            <option value="Authorize">Authorize</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Nomor Modul</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>No Modul: </strong> <span id="detail_no_modul"></span></p>
                    <p><strong>Type: </strong> <span id="detail_type"></span></p>
                    <p><strong>Status: </strong> <span id="detail_status"></span></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.editBtn').on('click', function() {
                let id = $(this).data('id');
                let no = $(this).data('no');
                let type = $(this).data('type');

                $('#edit_no_modul').val(no);
                $('#edit_type').val(type);

                $('#editForm').attr('action', '/office/modul/update/nomor/' + id);
            });

            $('.detailBtn').on('click', function() {
                $('#detail_no_modul').text($(this).data('no'));
                $('#detail_type').text($(this).data('type'));
                $('#detail_status').text($(this).data('status'));
            });
        });
    </script>
@endsection
