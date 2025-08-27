@extends('layouts_crm.app')
@section('crm_contents')
    <div class="container mt-5">
        <h1 class="mb-4">Manage Ketentuan</h1>

        <!-- Form to Create Ketentuan -->
        <div class="card mb-4">
            <div class="card-header">Add New Ketentuan</div>
            <div class="card-body">
                <form action="{{ route('crm.store.ketentuan') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="ketentuan" class="form-label">Ketentuan</label>
                        <textarea class="form-control" id="ketentuan" name="ketentuan" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>

        <!-- List of Ketentuan -->
        <div class="card">
            <div class="card-header">Ketentuan List</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ketentuan</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $ketentuan)
                            <tr>
                                <td>{{ $ketentuan->id }}</td>
                                <td>{{ $ketentuan->ketentuan }}</td>
                                <td>
                                    <!-- Edit Form -->
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $ketentuan->id }}">Edit</button>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $ketentuan->id }}" tabindex="-1"
                                        aria-labelledby="editModalLabel{{ $ketentuan->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel{{ $ketentuan->id }}">Edit
                                                        Ketentuan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('crm.update.ketentuan', $ketentuan->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="mb-3">
                                                            <label for="ketentuan{{ $ketentuan->id }}"
                                                                class="form-label">Ketentuan</label>
                                                            <textarea class="form-control" id="ketentuan{{ $ketentuan->id }}" name="ketentuan" required>{{ $ketentuan->ketentuan }}</textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Form -->
                                    <form action="{{ route('crm.delete.ketentuan', $ketentuan->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this ketentuan?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
