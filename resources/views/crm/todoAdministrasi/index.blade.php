@extends('layouts_crm.app')

@section('crm_contents')
<div class="container mt-3">

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

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
                <div>
                    <h3 class="mb-2 fw-bold text-dark">Data Todo List</h3>
                    <p class="text-muted fs-6 mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createTodoModal">
        <i class="bx bx-plus"></i> Tambah ToDo
    </button>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                <h5 class="mb-0 fw-bold">Daftar Todo List</h5>
                <div class="d-flex gap-2">
                    Progres : <span class="badge bg-warning">{{ $todos->where('status', 'progres')->count() }}</span>
                    Selesai : <span class="badge bg-success">{{ $todos->where('status', 'selesai')->count() }}</span>
                    Gagal : <span class="badge bg-danger">{{ $todos->where('status', 'gagal')->count() }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>No</th>
                            <th>Case</th>
                            <th>Solution</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($todos as $index => $todo)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $todo->case }}</td>
                            <td>{{ $todo->solusi ?? '-' }}</td>
                            <td>
                                <span class="badge 
                                    @if($todo->status == 'selesai') bg-success
                                    @elseif($todo->status == 'progres') bg-warning
                                    @else bg-danger
                                    @endif">
                                    {{ ucfirst($todo->status) }}
                                </span>
                            </td>
                            <td>{{ $todo->catatan ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editTodo({{ $todo->id }}, '{{ $todo->case }}', '{{ $todo->solusi ?? '' }}', '{{ $todo->status }}', '{{ $todo->catatan ?? '' }}')">Edit</button>
                                <form id="deleteForm{{ $todo->id }}" action="{{ route('todo-administrasi.delete', $todo->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $todo->id }})">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= MODAL CREATE TODO ================= --}}
    <div class="modal fade" id="createTodoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Todo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('todo-administrasi.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Case</label>
                                <input type="text" name="case" class="form-control" required>                                
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="catatan" class="form-control" rows="3"></textarea>
                            </div>
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

    {{-- ================= MODAL EDIT TODO ================= --}}
    <div class="modal fade" id="editTodoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Todo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTodoForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Case</label>
                                <input type="text" name="case" id="editCase" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Solution</label>
                                <textarea name="solusi" id="editSolusi" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="editStatus" class="form-control" required>
                                    <option value="progres">Progres</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="gagal">Gagal</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="catatan" id="editCatatan" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus todo ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        function editTodo(id, caseText, solusi, status, catatan) {
            $('#editCase').val(caseText);
            $('#editSolusi').val(solusi);
            $('#editStatus').val(status);
            $('#editCatatan').val(catatan);
            $('#editTodoForm').attr('action', '{{ route("todo-administrasi.update", ":id") }}'.replace(':id', id));
            new bootstrap.Modal($('#editTodoModal')[0]).show();
        }

        let deleteTodoId = null;

        function confirmDelete(id) {
            deleteTodoId = id;
            $('#confirmDeleteModal').modal('show');
        }

        $(document).ready(function () {

            // pakai .on supaya aman (delegation)
            $(document).on('click', '#confirmDeleteBtn', function () {
                if (!deleteTodoId) return;

                $('#deleteForm' + deleteTodoId).submit();
            });

        });
    </script>

</div>

@endsection