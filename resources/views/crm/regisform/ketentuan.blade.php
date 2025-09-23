@extends('layouts_crm.app')
@section('crm_contents')
    <div class="container my-5">
        <h1 class="mb-5 text-primary fw-bold display-5 text-center">Manage Ketentuan & Deskripsi</h1>

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

        <!-- Deskripsi and Ketentuan Forms Side by Side -->
        <div class="row mb-5">
            <!-- Deskripsi Form (Left) -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">{{ $deskripsiData ? 'Update Deskripsi' : 'Add New Deskripsi' }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ $deskripsiData ? route('crm.update.deskripsi', $deskripsiData->id) : route('crm.store.deskripsi') }}" method="POST" id="deskripsiForm">
                            @csrf
                            @if($deskripsiData)
                                @method('PUT')
                            @endif
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="5" required>{{ old('deskripsi', $deskripsiData ? $deskripsiData->deskripsi : '') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">{{ $deskripsiData ? 'Update' : 'Save' }}</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ketentuan Form (Right) -->
            <div class="col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">Add New Ketentuan</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('crm.store.ketentuan') }}" method="POST" id="createKetentuanForm">
                            @csrf
                            <div class="mb-3">
                                <label for="ketentuan" class="form-label fw-semibold">Ketentuan</label>
                                <textarea class="form-control @error('ketentuan') is-invalid @enderror" id="ketentuan" name="ketentuan" rows="5" required></textarea>
                                @error('ketentuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- List of Ketentuan -->
        <div class="row mb-5">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">Ketentuan List</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="ketentuanTable" class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-center">ID</th>
                                        <th scope="col">Ketentuan</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $ketentuan)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ Str::limit($ketentuan->ketentuan, 100) }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $ketentuan->id }}">Edit</button>
                                                <form action="{{ route('crm.delete.ketentuan', $ketentuan->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal{{ $ketentuan->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $ketentuan->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="editModalLabel{{ $ketentuan->id }}">Edit Ketentuan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('crm.update.ketentuan', $ketentuan->id) }}" method="POST" class="edit-ketentuan-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="mb-3">
                                                                <label for="ketentuan{{ $ketentuan->id }}" class="form-label fw-semibold">Ketentuan</label>
                                                                <textarea class="form-control @error('ketentuan') is-invalid @enderror" id="ketentuan{{ $ketentuan->id }}" name="ketentuan" rows="5" required>{{ old('ketentuan', $ketentuan->ketentuan) }}</textarea>
                                                                @error('ketentuan')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <button type="submit" class="btn btn-primary w-100 py-2">Update</button>
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
            </div>
        </div>

        <!-- Deskripsi Display -->
        @if($deskripsiData)
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0">Deskripsi</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="deskripsiTable" class="table table-hover table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="text-center">ID</th>
                                            <th scope="col">Deskripsi</th>
                                            <th scope="col" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center">1</td>
                                            <td>{{ Str::limit($deskripsiData->deskripsi, 100) }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('crm.delete.deskripsi', $deskripsiData->id) }}" method="POST" class="d-inline delete-deskripsi-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            .card {
                border-radius: 10px;
                transition: transform 0.3s ease;
            }
            .card:hover {
                transform: translateY(-5px);
            }
            .btn-primary {
                background-color: #007bff;
                border-color: #007bff;
                transition: background-color 0.3s ease;
            }
            .btn-primary:hover {
                background-color: #0056b3;
                border-color: #0056b3;
            }
            .table th, .table td {
                vertical-align: middle;
            }
            .form-control {
                border-radius: 8px;
            }
            .modal-content {
                border-radius: 10px;
            }
            @media (max-width: 991px) {
                .col-lg-6 {
                    margin-bottom: 1.5rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize DataTables for Ketentuan
                $('#ketentuanTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'desc']],
                    columnDefs: [
                        { targets: 0, width: '10%' },
                        { targets: 2, width: '20%', orderable: false }
                    ]
                });

                // Initialize DataTables for Deskripsi if exists
                if ($('#deskripsiTable').length) {
                    $('#deskripsiTable').DataTable({
                        responsive: true,
                        pageLength: 10,
                        order: [[0, 'desc']],
                        columnDefs: [
                            { targets: 0, width: '10%' },
                            { targets: 2, width: '20%', orderable: false }
                        ]
                    });
                }

                // Handle form submissions with AJAX for Ketentuan and Deskripsi
                $('#createKetentuanForm, .edit-ketentuan-form, #deskripsiForm').on('submit', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    $.ajax({
                        url: form.attr('action'),
                        method: form.find('input[name="_method"]').val() || form.attr('method'),
                        data: form.serialize(),
                        success: function(response) {
                            showToast('Success', response.message || 'Operation successful!', 'success');
                            setTimeout(() => location.reload(), 1500);
                        },
                        error: function(xhr) {
                            showToast('Error', xhr.responseJSON?.message || 'An error occurred.', 'danger');
                        }
                    });
                });

                // Confirm delete with a better UI for Ketentuan
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
                                showToast('Error', xhr.responseJSON?.message || 'An error occurred.', 'danger');
                            }
                        });
                    }
                });

                // Confirm delete with a better UI for Deskripsi
                $('.delete-deskripsi-form').on('submit', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to delete this deskripsi?')) {
                        const form = $(this);
                        $.ajax({
                            url: form.attr('action'),
                            method: 'DELETE',
                            data: form.serialize(),
                            success: function(response) {
                                showToast('Success', 'Deskripsi deleted successfully!', 'success');
                                setTimeout(() => location.reload(), 1500);
                            },
                            error: function(xhr) {
                                showToast('Error', xhr.responseJSON?.message || 'An error occurred.', 'danger');
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