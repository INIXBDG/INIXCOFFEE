@extends('layouts.app')

@section('content')
{{-- Library CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-video me-2 text-primary"></i> Jadwal Konten Kreatif
        </h4>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus-circle me-1"></i> Tambah Jadwal
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table id="schedulesTable" class="table table-striped table-hover w-100 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 10%">Bentuk Konten</th>
                        <th style="width: 15%">Tanggal Upload</th>
                        <th style="width: 20%">Talent</th>
                        <th>Keterangan</th>
                        <th style="width: 10%" class="text-center">Bukti</th>
                        <th style="width: 10%" class="text-center">Aksi</th>
                        <th style="width: 5%" class="text-center">Tiktok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @php
                                    $badgeClass = match($item->content_form) {
                                        'Reels' => 'bg-warning text-dark',
                                        'Youtube' => 'bg-danger',
                                        'Story' => 'bg-info text-dark',
                                        'Feed' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeClass }}">
                                    {{ $item->content_form }}
                                </span>
                            </td>
                            <td class="text-center" data-sort="{{ $item->upload_date ? $item->upload_date->format('Y-m-d') : '9999-12-31' }}">
                                @if($item->upload_date)
                                    <span class="text-success fw-bold">
                                        {{ $item->upload_date->format('d M Y') }}
                                    </span>
                                @else
                                    <form action="{{ route('content-schedules.mark-uploaded', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-primary shadow-sm">
                                            <i class="fas fa-check me-1"></i> Tandai sebagai Upload
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                @foreach(explode(',', $item->talents) as $talent)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ trim($talent) }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                    {{ $item->description ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->proof_image_path || $item->proof_script)
                                    <button class="btn btn-sm btn-outline-info show-proof-btn"
                                            data-script="{{ $item->proof_script }}"
                                            data-image="{{ $item->proof_image_path ? Storage::url($item->proof_image_path) : '' }}">
                                        <i class="fas fa-file-invoice"></i>
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        {{-- Edit (Memicu Modal Edit) --}}
                                        <li>
                                            <button class="dropdown-item edit-btn"
                                                data-id="{{ $item->id }}"
                                                data-form="{{ $item->content_form }}"
                                                data-date="{{ $item->upload_date ? $item->upload_date->format('Y-m-d') : '' }}"
                                                data-talents="{{ $item->talents }}"
                                                data-desc="{{ $item->description }}"
                                                data-script="{{ $item->proof_script }}"
                                                data-tiktok="{{ $item->is_tiktok }}"
                                                data-route="{{ route('content-schedules.update', $item->id) }}">
                                                <i class="fas fa-pencil-alt me-2 text-warning"></i>Edit
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        {{-- Hapus --}}
                                        <li>
                                            <form action="{{ route('content-schedules.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i>Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($item->is_tiktok)
                                    <i class="fas fa-check-circle text-dark fa-lg"></i>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted p-4">
                                Belum ada jadwal konten.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('content-schedules.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Jadwal Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12"> {{-- Ubah col-md-6 jadi 12 karena tanggal dihapus --}}
                            <label class="form-label">Bentuk Konten</label>
                            <select name="content_form" class="form-select" required>
                                <option value="Story">Story</option>
                                <option value="Feed">Feed</option>
                                <option value="Reels">Reels</option>
                                <option value="Youtube">Youtube</option>
                                <option value="Tiktok">Tiktod</option>
                            </select>
                        </div>

                        {{-- INPUT TANGGAL DIHAPUS DARI SINI --}}

                        <div class="col-12">
                            <label class="form-label">Talent (Pilih Multiple)</label>
                            <select name="talents[]" class="form-select" multiple style="height: 100px;">
                                <option value="Hera">Hera</option>
                                <option value="Savanna">Savanna</option>
                                <option value="Reni">Reni</option>
                                <option value="Rara">Rara</option>
                                <option value="Alfi">Alfi</option>
                                <option value="Nabila">Nabila</option>
                                <option value="Fia">Fia</option>
                                <option value="Ani">Ani</option>
                                <option value="Yanuar">Yanuar</option>
                                <option value="Adit">Adit</option>
                                <option value="Luki">Luki</option>
                                <option value="Sabdhan">Sabdhan</option>
                                <option value="Rustan">Rustan</option>
                                <option value="Wahyu">Wahyu</option>
                                <option value="Sahrul">Sahrul</option>
                                <option value="Pani">Pani</option>
                                <option value="Yayat">Yayat</option>
                                <option value="Stepan">Stepan</option>
                                <option value="Vicky">Vicky</option>
                                <option value="Sergio">Sergio</option>
                                <option value="Donna">Donna</option>
                                <option value="Eggi">Eggi</option>
                                <option value="Ardhan">Ardhan</option>
                                <option value="Julie">Julie</option>
                                <option value="Ferdi">Ferdi</option>
                                <option value="Aulia">Aulia</option>
                                <option value="Alysia">Alysia</option>
                                <option value="Xepi">Xepi</option>
                                <option value="Rifa">Rifa</option>
                            </select>
                        </div>
                        {{-- Sisa input (Description, Script, Image, Tiktok) tetap sama --}}
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Script Konten</label>
                            <textarea name="proof_script" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Bukti Screenshot</label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_tiktok" value="1" id="isTiktokCreate">
                                <label class="form-check-label fw-bold" for="isTiktokCreate">Upload ke Tiktok?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Struktur sama dengan Create, ID ditambahkan untuk JS --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bentuk Konten</label>
                            <select name="content_form" id="edit_content_form" class="form-select" required>
                                <option value="Story">Story</option>
                                <option value="Feed">Feed</option>
                                <option value="Reels">Reels</option>
                                <option value="Youtube">Youtube</option>
                                <option value="Tiktok">Tiktod</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Upload</label>
                            <input type="date" name="upload_date" id="edit_upload_date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Talent</label>
                            <select name="talents[]" id="edit_talents" class="form-select" multiple style="height: 100px;">
                                <option value="Hera">Hera</option>
                                <option value="Savanna">Savanna</option>
                                <option value="Reni">Reni</option>
                                <option value="Rara">Rara</option>
                                <option value="Alfi">Alfi</option>
                                <option value="Nabila">Nabila</option>
                                <option value="Fia">Fia</option>
                                <option value="Ani">Ani</option>
                                <option value="Yanuar">Yanuar</option>
                                <option value="Adit">Adit</option>
                                <option value="Luki">Luki</option>
                                <option value="Sabdhan">Sabdhan</option>
                                <option value="Rustan">Rustan</option>
                                <option value="Wahyu">Wahyu</option>
                                <option value="Sahrul">Sahrul</option>
                                <option value="Pani">Pani</option>
                                <option value="Yayat">Yayat</option>
                                <option value="Stepan">Stepan</option>
                                <option value="Vicky">Vicky</option>
                                <option value="Sergio">Sergio</option>
                                <option value="Donna">Donna</option>
                                <option value="Eggi">Eggi</option>
                                <option value="Ardhan">Ardhan</option>
                                <option value="Julie">Julie</option>
                                <option value="Ferdi">Ferdi</option>
                                <option value="Aulia">Aulia</option>
                                <option value="Alysia">Alysia</option>
                                <option value="Xepi">Xepi</option>
                                <option value="Rifa">Rifa</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Script Konten</label>
                            <textarea name="proof_script" id="edit_proof_script" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Ganti Screenshot (Opsional)</label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_tiktok" value="1" id="edit_is_tiktok">
                                <label class="form-check-label fw-bold" for="edit_is_tiktok">Upload ke Tiktok?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL VIEW PROOF (BUKTI) --}}
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail Bukti & Script</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold mb-3">Script Konten</h6>
                        <div class="p-3 bg-light rounded" style="min-height: 200px; white-space: pre-wrap;" id="viewScriptContent"></div>
                    </div>
                    <div class="col-md-6 text-center">
                        <h6 class="fw-bold mb-3">Screenshot Bukti</h6>
                        <div id="viewImageContainer">
                            {{-- Gambar di sini --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script Javascript --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#schedulesTable').DataTable({
            order: [[ 2, "desc" ]],
            pageLength: 10,
            columnDefs: [ { orderable: false, targets: [6, 7] } ]
        });

        $('#schedulesTable tbody').on('click', '.edit-btn', function() {
            let id = $(this).data('id');
            let formUrl = $(this).data('route');

            $('#editForm').attr('action', formUrl);
            $('#edit_content_form').val($(this).data('form'));
            $('#edit_upload_date').val($(this).data('date'));
            $('#edit_description').val($(this).data('desc'));
            $('#edit_proof_script').val($(this).data('script'));
            let isTiktok = $(this).data('tiktok');
            $('#edit_is_tiktok').prop('checked', isTiktok == 1);
            let talents = $(this).data('talents').toString().split(',');
            $('#edit_talents').val(talents);

            $('#editModal').modal('show');
        });

        $('#schedulesTable tbody').on('click', '.show-proof-btn', function() {
            let script = $(this).data('script') || 'Tidak ada script.';
            let imageUrl = $(this).data('image');

            $('#viewScriptContent').text(script);

            let imgContainer = $('#viewImageContainer');
            imgContainer.empty();

            if (imageUrl) {
                imgContainer.append(`<img src="${imageUrl}" class="img-fluid rounded shadow-sm" alt="Bukti">`);
                imgContainer.append(`<br><a href="${imageUrl}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Buka Ukuran Penuh</a>`);
            } else {
                imgContainer.append('<p class="text-muted fst-italic mt-5">Tidak ada gambar bukti.</p>');
            }

            $('#proofModal').modal('show');
        });
    });
</script>
@endsection
