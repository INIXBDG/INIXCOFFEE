@extends('layouts_crm.app')
@section('crm_contents')
    <div class="container my-5">
        <h1 class="mb-4 text-primary fw-bold">Manage Ketentuan</h1>

        <!-- Toast Notification -->
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
            <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body"></div>
            </div>
        </div>

        <!-- Form to Create Ketentuan -->
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white">Add New Ketentuan</div>
            <div class="card-body">
                <form action="{{ route('crm.store.ketentuan') }}" method="POST" id="createKetentuanForm">
                    @csrf
                    <div class="mb-3">
                        <label for="ketentuan" class="form-label fw-semibold">Ketentuan</label>
                        <textarea class="form-control @error('ketentuan') is-invalid @enderror" id="ketentuan" name="ketentuan" rows="4"
                            required></textarea>
                        @error('ketentuan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>

        <!-- List of Ketentuan -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">Ketentuan List </div>
            <div class="card-body">
                <table id="ketentuanTable" class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Ketentuan</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $ketentuan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ Str::limit($ketentuan->ketentuan, 50) }}</td>
                                <td>
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-sm btn-warning me-2" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $ketentuan->id }}">Edit</button>

                                    <!-- Delete Form -->
                                    <form action="{{ route('crm.delete.ketentuan', $ketentuan->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal{{ $ketentuan->id }}" tabindex="-1"
                                aria-labelledby="editModalLabel{{ $ketentuan->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="editModalLabel{{ $ketentuan->id }}">Edit Ketentuan
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('crm.update.ketentuan', $ketentuan->id) }}"
                                                method="POST" class="edit-ketentuan-form">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-3">
                                                    <label for="ketentuan{{ $ketentuan->id }}"
                                                        class="form-label fw-semibold">Ketentuan</label>
                                                    <textarea class="form-control @error('ketentuan') is-invalid @enderror" id="ketentuan{{ $ketentuan->id }}"
                                                        name="ketentuan" rows="4" required>{{ old('ketentuan', $ketentuan->ketentuan) }}</textarea>
                                                    @error('ketentuan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- Include DataTables CSS and JS -->
        <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTables
                $('#ketentuanTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [
                        [0, 'desc']
                    ],
                });

                // Handle form submissions with AJAX for better UX
                $('#createKetentuanForm, .edit-ketentuan-form').on('submit', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    $.ajax({
                        url: form.attr('action'),
                        method: form.find('input[name="_method"]').val() || form.attr('method'),
                        data: form.serialize(),
                        success: function(response) {
                            showToast('Success', response.message || 'Operation successful!',
                                'success');
                            setTimeout(() => location.reload(), 1500);
                        },
                        error: function(xhr) {
                            showToast('Error', xhr.responseJSON?.message || 'An error occurred.',
                                'danger');
                        }
                    });
                });

                // Confirm delete with a better UI
                $('.delete-form').on('submit', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to delete this ketentuan?')) {
                        const form = $(this);
                        $.ajax({
                            url: form.attr('action'),
                            method: 'DELETE',
                            data: form.serialize(),
                            success: function(response) {
                                showToast('Success', 'Ketentuan deleted successfully!', 'success');
                                setTimeout(() => location.reload(), 1500);
                            },
                            error: function(xhr) {
                                showToast('Error', xhr.responseJSON?.message ||
                                    'An error occurred.', 'danger');
                            }
                        });
                    }
                });

                // Toast notification function
                function showToast(title, message, type) {
                    const toast = $('#toastNotification');
                    toast.find('.toast-header strong').text(title);
                    toast.find('.toast-body').text(message);
                    toast.removeClass('text-bg-success text-bg-danger').addClass(`text-bg-${type}`);
                    toast.toast({
                        delay: 3000
                    });
                    toast.toast('show');
                }
            });
        </script>
    @endpush
@endsection
