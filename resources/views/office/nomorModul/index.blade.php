@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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

        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h4 class="mb-0 fw-bold text-dark">Data Nomor Modul</h4>
            <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                Tambah Nomor Modul
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden glass-force">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="nomorModulTable" class="table table-hover mb-0 align-middle" style="width:100%">
                        <thead class="bg-light text-dark fw-semibold text-uppercase small">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>No Modul</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th>Subscode</th>
                                <th>SLA</th>
                                <th class="text-center pe-4 no-sort" style="width: 18%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted">
                            @forelse ($nomor as $item)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $item->no_modul }}</td>
                                    <td>
                                        <span class="badge {{ $item->type == 'Authorize' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                            {{ $item->type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $item->uploaded ? \Carbon\Carbon::parse($item->uploaded)->format('d M Y') : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                    @if ( $item->type === "Authorize" )
                                        @if ($item->tanggal_subscode_masuk)
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge bg-success w-fit-content">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Uploaded
                                                </span>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    {{ \Carbon\Carbon::parse($item->tanggal_subscode_masuk)->format('d M Y, H:i') }}
                                                </small>
                                                @if ($item->status_subscode == 1)
                                                    <span class="badge bg-info text-dark w-fit-content" style="font-size: 0.7rem;">Ada</span>
                                                @elseif ($item->status_subscode == 0)
                                                    <span class="badge bg-danger w-fit-content" style="font-size: 0.7rem;">Tidak Ada</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-dash-circle me-1"></i> Belum
                                            </span>
                                        @endif
                                    @else
                                        
                                    @endif
                                    </td>
                                    <td>
                                        @if ($item->tanggal_subscode_masuk && $item->tanggal_tenggat)
                                            @php
                                                $tanggalDiterima = \Carbon\Carbon::parse($item->tanggal_subscode_masuk);
                                                $tenggat = \Carbon\Carbon::parse($item->tanggal_tenggat);
                                            @endphp
                                            @if ($tanggalDiterima->lte($tenggat))
                                                <span class="badge bg-success">Berhasil</span>
                                                <small class="text-muted d-block" style="font-size: .75rem;">100%</small>
                                            @else
                                                @php
                                                    $minutesLate = $tanggalDiterima->diffInMinutes($tenggat);
                                                    // Hitung hari keterlambatan (setiap bagian hari dianggap 1 hari)
                                                    $daysLate = (int) ceil($minutesLate / 1440);
                                                    // Turun 7% per hari terlambat, batasi minimal 0%
                                                    $percent = max(0, 100 - ($daysLate * 7));
                                                @endphp
                                                <span class="badge bg-danger">Gagal</span>
                                                <small class="text-muted d-block" style="font-size: .75rem;">
                                                    {{ $percent }}%
                                                </small>
                                            @endif
                                        @elseif ($item->tanggal_subscode_masuk)
                                            <span class="badge bg-warning text-dark">Tenggat belum diisi</span>
                                        @elseif ($item->tanggal_tenggat)
                                            <span class="badge bg-secondary">Belum diterima</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary dropdown-toggle px-3" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i> Aksi
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <a href="{{ route('office.modul.detail', $item->id) }}" class="dropdown-item">
                                                        <i class="bi bi-eye text-info me-2"></i> Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item editBtn"
                                                        data-id="{{ $item->id }}" data-no="{{ $item->no_modul }}"
                                                        data-type="{{ $item->type }}"
                                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                                        <i class="bi bi-pencil-square text-warning me-2"></i> Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item pdfBtn"
                                                        data-id="{{ $item->id }}" data-note="{{ $item->note_modul }}"
                                                        data-bs-toggle="modal" data-bs-target="#noteModal">
                                                        <i class="bi bi-file-earmark-pdf text-secondary me-2"></i> PDF
                                                    </button>
                                                </li>
                                                @if ( $item->type === "Authorize" )
                                                    <li>
                                                        {{-- PERBAIKAN: format data-tanggal dan data-tenggat langsung ke format datetime-local (Y-m-d\TH:i) --}}
                                                        <button type="button" class="dropdown-item subscode"
                                                            data-id="{{ $item->id }}"
                                                            data-no="{{ $item->no_modul }}"
                                                            data-status="{{ $item->status_subscode ?? '' }}"
                                                            data-tanggal="{{ $item->tanggal_subscode_masuk ? \Carbon\Carbon::parse($item->tanggal_subscode_masuk)->format('Y-m-d\TH:i') : '' }}"
                                                            data-tenggat="{{ $item->tanggal_tenggat ? \Carbon\Carbon::parse($item->tanggal_tenggat)->format('Y-m-d\TH:i') : '' }}"
                                                            data-catatan="{{ $item->catatan ?? '' }}"
                                                            data-bs-toggle="modal" data-bs-target="#subscodeModal">
                                                            <i class="bi bi-tag text-info me-2"></i> Subscode
                                                        </button>
                                                    </li>
                                                @endif
                                                @if ($item->status !== 'Uploaded')
                                                    <li>
                                                        <button type="button" class="dropdown-item uploadedBtn"
                                                            data-id="{{ $item->id }}"
                                                            data-bs-toggle="modal" data-bs-target="#uploadedModal">
                                                            <i class="bi bi-cloud-upload text-success me-2"></i> Uploaded
                                                        </button>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('office.modul.delete.nomor', $item->id) }}"
                                                        method="POST" id="delete-form-{{ $item->id }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="dropdown-item text-danger"
                                                            onclick="if(confirm('Yakin ingin menghapus data ini?')) { this.closest('form').submit(); }">
                                                            <i class="bi bi-trash me-2"></i> Hapus
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                {{-- PENTING: Biarkan KOSONG di sini. Jangan isi <tr> atau <td> apapun agar tidak bentrok dengan DataTables. --}}
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
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
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
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Uploaded</label>
                        <input type="date" class="form-control form-control-lg" name="uploaded" id="edit_uploaded">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="subscodeModal" tabindex="-1" aria-labelledby="subscodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="subscodeForm" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="subscodeModalLabel">Isi Data Subscode</h5>
                        <small class="text-muted" id="subscode_no_modul_display"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-block">Status</label>
                        <div class="btn-group w-100" role="group" aria-label="Status">
                            <input type="radio" class="btn-check" name="status" id="status_subscode_aktif"
                                value="1" autocomplete="off" checked>
                            <label class="btn btn-outline-success" for="status_subscode_aktif">
                                <i class="bi bi-check-circle me-1"></i> Sudah
                            </label>

                            <input type="radio" class="btn-check" name="status" id="status_subscode_nonaktif"
                                value="0" autocomplete="off">
                            <label class="btn btn-outline-danger" for="status_subscode_nonaktif">
                                <i class="bi bi-x-circle me-1"></i> Belum
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Diterima</label>
                        <input type="datetime-local" name="tanggal_subscode_masuk" id="tanggal_subscode"
                            class="form-control form-control-lg">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tenggat</label>
                        <input type="datetime-local" name="tanggal_tenggat" id="tanggal_tenggat"
                            class="form-control form-control-lg">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="catatan" id="catatan_subscode"
                            class="form-control" rows="5"
                            placeholder="Tuliskan catatan subscode..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
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

    {{-- Uploaded Modal --}}
    <div class="modal fade" id="uploadedModal" tabindex="-1" aria-labelledby="uploadedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="uploadedForm" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="uploadedModalLabel">Konfirmasi Upload Modul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Upload</label>
                        <input type="date" name="uploaded" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Delayed <span class="text-muted fw-normal">(opsional)</span></label>
                        <select name="delay" class="form-select form-select-lg">
                            <option value="" disabled selected>Pilih...</option>
                            <option value="Client">Client</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="keterangan" class="form-control" rows="4"
                            placeholder="Tuliskan keterangan tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">Konfirmasi Upload</button>
                </div>
            </form>
        </div>
    </div>

    {{-- DataTables CSS (Bootstrap 5 styling) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {

            const table = $('#nomorModulTable').DataTable({
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                pageLength: 10,

                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    search: "Cari:",
                    paginate: {
                        first: "First",
                        last: "End",
                        next: ">",
                        previous: "<"
                    }
                },

                columnDefs: [
                    { orderable: true, targets: [0, 1, 2, 3, 4, 5, 6] },
                    { orderable: false, targets: 7 } 
                ],

                order: [[0, 'asc']],

                responsive: true,
                autoWidth: false
            });

            table.on('order.dt search.dt draw.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            });

            // Edit Modal
            $('.editBtn').on('click', function() {
                const id = $(this).data('id');
                const no = $(this).data('no');
                const type = $(this).data('type');

                $('#edit_no_modul').val(no);
                $('#edit_type').val(type);
                $('#editForm').attr('action', `/office/modul/update/nomor/${id}`);
            });

            // SUBSCODE MODAL - PERBAIKAN: gunakan event delegation + attr() agar data-tanggal/tenggat terbaca fresh
            $(document).on('click', '.subscode', function () {
                const id      = $(this).data('id');
                const noModul = $(this).data('no');
                const status  = $(this).data('status');
                // Pakai attr() untuk baca value sebagai string utuh (hindari auto-conversion jQuery .data() pada format datetime)
                const tanggal = $(this).attr('data-tanggal') || '';
                const tenggat = $(this).attr('data-tenggat') || '';
                const catatan = $(this).data('catatan');

                $('#subscode_no_modul_display').text('No Modul: ' + noModul);

                if (status == '1') {
                    $('#status_subscode_aktif').prop('checked', true);
                } else {
                    $('#status_subscode_nonaktif').prop('checked', true);
                }

                // Value dari Blade sudah dalam format Y-m-d\TH:i, tinggal pasang langsung
                $('#tanggal_subscode').val(tanggal);
                $('#tanggal_tenggat').val(tenggat);
                $('#catatan_subscode').val(catatan || '');

                const route = '{{ route('office.modul.update.subscode', ':id') }}';
                $('#subscodeForm').attr('action', route.replace(':id', id));
            });

            $('.pdfBtn').on('click', function() {
                const id = $(this).data('id');
                const noteContent = $(this).data('note');

                $('#note').val(noteContent);

                const route = '{{ route('office.modul.download.pdf', ':id') }}';
                $('#noteForm').attr('action', route.replace(':id', id));
            });

            // Uploaded Modal
            $('.uploadedBtn').on('click', function () {
                const id = $(this).data('id');

                const today = new Date().toISOString().split('T')[0];
                $('#uploadedForm [name="uploaded"]').val(today);

                $('#uploadedForm [name="delay"]').val('');
                $('#uploadedForm [name="keterangan"]').val('');

                const route = '{{ route('office.modul.update.status.nomor', ':id') }}';
                $('#uploadedForm').attr('action', route.replace(':id', id));
            });
        });
    </script>
@endsection