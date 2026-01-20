@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Pengajuan Kegiatan</h4>
            </div>
            <button class="btn btn-primary px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                data-bs-target="#createModal">
                Buat Pengajuan
            </button>
        </div>

        {{-- Card Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0 fw-semibold">Daftar Kegiatan</h5>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="text-dark fw-semibold small bg-light">
                            <tr>
                                <th class="ps-4 border-0" style="width: 5%;">No</th>
                                <th class="border-0" style="width: 25%;">Kegiatan</th>
                                <th class="border-0" style="width: 15%;">Waktu</th>
                                <th class="border-0" style="width: 10%;">Durasi</th>
                                <th class="border-0" style="width: 15%;">PIC</th>
                                <th class="border-0" style="width: 12%;">Status</th>
                                <th class="border-0 text-center" style="width: 18%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kegiatan as $item)
                                <tr>
                                    <td class="ps-4 fw-medium text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->nama_kegiatan }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-medium">
                                            {{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('d M Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('H:i') }} WIB
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-clock me-1"></i>{{ $item->lama_kegiatan }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $item->pic }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $item->status }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{route('office.showRincian', $item->id)}}">
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center gap-1">
                                                    Detail
                                                </button>
                                            </a>

                                            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="{{ $item->id }}" data-nama="{{ $item->nama_kegiatan }}"
                                                data-waktu="{{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('Y-m-d\TH:i') }}"
                                                data-durasi="{{ $item->lama_kegiatan }}" data-pic="{{ $item->pic }}">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>

                                            <form action="{{ route('office.deleteKegiatan', $item->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center gap-3">
                                            <div>
                                                <h5 class="text-muted mb-1">Belum ada kegiatan</h5>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($kegiatan->count() > 0)
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Menampilkan {{ $kegiatan->count() }} kegiatan</small>
                        {{-- Pagination bisa ditambahkan di sini jika diperlukan --}}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('office.storeKegiatan') }}" method="POST"
                class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="createModalLabel">Pengajuan Kegiatan Baru</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Nama Kegiatan
                            </label>
                            <input type="text" name="nama_kegiatan" class="form-control form-control-lg"
                                placeholder="Masukkan nama kegiatan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-clock text-primary me-2"></i>Waktu Kegiatan
                            </label>
                            <input type="datetime-local" name="waktu_kegiatan" class="form-control form-control-lg"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-hourglass-half text-primary me-2"></i>Durasi
                            </label>
                            <input type="text" name="lama_kegiatan" class="form-control form-control-lg"
                                placeholder="Contoh: 2 jam" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user text-primary me-2"></i>PIC
                            </label>
                            <input type="text" name="pic" class="form-control form-control-lg"
                                placeholder="Masukkan nama PIC" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        Ajukan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="" method="POST" id="editForm" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="editModalLabel">Edit Pengajuan Kegiatan</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>Nama Kegiatan
                            </label>
                            <input type="text" name="nama_kegiatan" id="edit_nama_kegiatan"
                                class="form-control form-control-lg" placeholder="Masukkan nama kegiatan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-clock text-primary me-2"></i>Waktu Kegiatan
                            </label>
                            <input type="datetime-local" name="waktu_kegiatan" id="edit_waktu_kegiatan"
                                class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-hourglass-half text-primary me-2"></i>Durasi
                            </label>
                            <input type="text" name="lama_kegiatan" id="edit_lama_kegiatan"
                                class="form-control form-control-lg" placeholder="Contoh: 2 jam" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user text-primary me-2"></i>PIC
                            </label>
                            <input type="text" name="pic" id="edit_pic" class="form-control form-control-lg"
                                placeholder="Masukkan nama PIC" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', event => {
            // Button yang memicu modal
            const button = event.relatedTarget;

            // Ambil data dari attribute
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const waktu = button.getAttribute('data-waktu');
            const durasi = button.getAttribute('data-durasi');
            const pic = button.getAttribute('data-pic');

            // Isi form
            editModal.querySelector('#edit_nama_kegiatan').value = nama;
            editModal.querySelector('#edit_waktu_kegiatan').value = waktu;
            editModal.querySelector('#edit_lama_kegiatan').value = durasi;
            editModal.querySelector('#edit_pic').value = pic;

            // Set action form ke route update
            const form = editModal.querySelector('#editForm');
            form.action =
                `/office/kegiatan/update/${id}`;
        });
    </script>
@endsection
