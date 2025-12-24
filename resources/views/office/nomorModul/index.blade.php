@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded shadow-sm" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Header + Tombol Tambah --}}
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h4 class="mb-0 fw-bold text-dark">Data Nomor Modul</h4>
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                Tambah Nomor Modul
            </button>
        </div>

        {{-- Card Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-dark fw-semibold text-uppercase small">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>No Modul</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th class="text-center pe-4" style="width: 22%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted">
                            @forelse ($nomor as $item)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $item->no_modul }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $item->type == 'Authorize' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                            {{ $item->type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('office.modul.detail', $item->id) }}"
                                                class="btn btn-outline-info btn-sm">Detail</a>

                                            <button type="button" class="btn btn-outline-warning btn-sm editBtn"
                                                data-id="{{ $item->id }}" data-no="{{ $item->no_modul }}"
                                                data-type="{{ $item->type }}" data-bs-toggle="modal"
                                                data-bs-target="#editModal">
                                                Edit
                                            </button>

                                            <button type="button" class="btn btn-outline-secondary btn-sm pdfBtn"
                                                data-id="{{ $item->id }}" data-note="{{ $item->note_modul }}"
                                                data-bs-toggle="modal" data-bs-target="#noteModal">
                                                PDF
                                            </button>

                                            <form action="{{ route('office.modul.delete.nomor', $item->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        Belum ada data nomor modul.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('office.modul.store.nomor') }}" method="POST"
                class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="createModalLabel">Tambah Nomor Modul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No Modul</label>
                        <input type="text" name="no_modul" class="form-control form-control-lg"
                            value="{{ old('no_modul', $noModul ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type</label>
                        <select name="type" class="form-select form-select-lg">
                            <option value="Regular" {{ old('type') == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Authorize" {{ old('type') == 'Authorize' ? 'selected' : '' }}>Authorize</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan>Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editModalLabel">Edit Nomor Modul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No Modul</label>
                        <input type="text" id="edit_no_modul" name="no_modul" class="form-control form-control-lg"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type</label>
                        <select id="edit_type" name="type" class="form-select form-select-lg">
                            <option value="Regular">Regular</option>
                            <option value="Authorize">Authorize</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="noteForm" action="" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="noteModalLabel">Catatan untuk PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label for="note" class="form-label fw-semibold">Note / Catatan (opsional)</label>
                        <textarea name="note" id="note" class="form-control" rows="5"
                            placeholder="Tuliskan catatan tambahan yang ingin dicantumkan di PDF..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            // Edit Modal
            $('.editBtn').on('click', function() {
                const id = $(this).data('id');
                const no = $(this).data('no');
                const type = $(this).data('type');

                $('#edit_no_modul').val(no);
                $('#edit_type').val(type);
                $('#editForm').attr('action', `/office/modul/update/nomor/${id}`);
            });

            $('.pdfBtn').on('click', function() {
                const id = $(this).data('id');
                const noteContent = $(this).data('note'); 

                $('#note').val(noteContent);

                const route = '{{ route('office.modul.download.pdf', ':id') }}';
                $('#noteForm').attr('action', route.replace(':id', id));
            });
        });
    </script>
@endsection
