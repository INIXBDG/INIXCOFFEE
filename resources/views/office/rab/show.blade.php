@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            {{-- Header Kartu --}}
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-calendar-check me-2"></i>
                    {{ $kegiatan->nama_kegiatan }}
                </h5>
                <span class="badge bg-success bg-opacity-10 text-dark px-3 py-2 rounded-pill">
                    <i class="fas fa-circle fa-xs me-1"></i> {{ ucfirst($kegiatan->status) }}
                </span>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">

                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center mb-2">
                            <small class="text-muted fw-bold text-uppercase"
                                style="font-size: 0.75rem; letter-spacing: 0.5px;">Waktu</small>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">
                                {{ \Carbon\Carbon::parse($kegiatan->waktu_kegiatan)->translatedFormat('d F Y') }}
                            </h6>
                            <small class="text-primary fw-semibold">
                                {{ \Carbon\Carbon::parse($kegiatan->waktu_kegiatan)->format('H:i') }} WIB
                            </small>
                        </div>
                    </div>

                    <div class="col-6 col-md-3 border-start-md">
                        <div class="d-flex align-items-center mb-2">
                            <small class="text-muted fw-bold text-uppercase"
                                style="font-size: 0.75rem; letter-spacing: 0.5px;">Durasi</small>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark mt-1">{{ $kegiatan->lama_kegiatan }}</h6>
                    </div>

                    <div class="col-6 col-md-3 border-start-md">
                        <div class="d-flex align-items-center mb-2">
                            <small class="text-muted fw-bold text-uppercase"
                                style="font-size: 0.75rem; letter-spacing: 0.5px;">PIC</small>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark mt-1">{{ $kegiatan->pic }}</h6>
                    </div>

                    <div class="col-6 col-md-3 border-start-md">
                        <div class="card h-100 shadow-sm cursor-pointer" role="button" data-bs-toggle="modal"
                            data-bs-target="#pesertaModal">

                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <small class="text-muted fw-bold text-uppercase"
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        Peserta Kegiatan
                                    </small>
                                </div>
                                <h6 class="mb-0 fw-bold text-dark mt-1">
                                    {{ $karyawan->count() }}
                                </h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-body p-4 border-top">
                <div class="d-flex align-items-center mb-3">
                    <h6 class="mb-0 fw-bold text-dark">Tracking Status Kegiatan</h6>
                </div>

                <div class="row g-4">

                    {{-- Diajukan --}}
                    <div class="col-6 col-md">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem;">Diajukan</small>
                        <span class="fw-semibold text-dark">
                            {{ $kegiatan->created_at ? \Carbon\Carbon::parse($kegiatan->created_at)->translatedFormat('d M Y H:i') : '-' }}
                        </span>
                    </div>

                    {{-- Menunggu --}}
                    <div class="col-6 col-md border-start-md">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem;">Menunggu</small>
                        <span class="fw-semibold {{ $kegiatan->menunggu ? 'text-warning' : 'text-muted' }}">
                            {{ $kegiatan->menunggu ? \Carbon\Carbon::parse($kegiatan->menunggu)->translatedFormat('d M Y H:i') : '-' }}
                        </span>
                    </div>

                    {{-- Approved --}}
                    <div class="col-6 col-md border-start-md">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem;">Approved</small>
                        <span class="fw-semibold {{ $kegiatan->approved ? 'text-success' : 'text-muted' }}">
                            {{ $kegiatan->approved ? \Carbon\Carbon::parse($kegiatan->approved)->translatedFormat('d M Y H:i') : '-' }}
                        </span>
                    </div>

                    {{-- Pencairan --}}
                    <div class="col-6 col-md border-start-md">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem;">Pencairan</small>
                        <span class="fw-semibold {{ $kegiatan->pencairan ? 'text-info' : 'text-muted' }}">
                            {{ $kegiatan->pencairan ? \Carbon\Carbon::parse($kegiatan->pencairan)->translatedFormat('d M Y H:i') : '-' }}
                        </span>
                    </div>

                    {{-- Selesai --}}
                    <div class="col-6 col-md border-start-md">
                        <small class="text-muted fw-bold text-uppercase d-block mb-1"
                            style="font-size: 0.7rem;">Selesai</small>
                        <span class="fw-semibold {{ $kegiatan->selesai ? 'text-primary' : 'text-muted' }}">
                            {{ $kegiatan->selesai ? \Carbon\Carbon::parse($kegiatan->selesai)->translatedFormat('d M Y H:i') : '-' }}
                        </span>
                    </div>

                </div>
            </div>

        </div>

        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Detail Kebutuhan Kegiatan</h4>
            </div>
            @if (!$kegiatan->selesai)
                {{-- HRD --}}
                @if (Auth::user()->jabatan === 'HRD')
                    <button class="btn btn-primary px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        Tambah Kebutuhan
                    </button>

                    @if ($kegiatan->status === 'Pencairan')
                        <button class="btn btn-success px-4 shadow-sm d-flex align-items-center gap-2"
                            data-bs-toggle="modal" data-bs-target="#hrdUpdateModal">
                            Selesaikan Kegiatan
                        </button>
                    @endif
                @endif

                {{-- GM --}}
                @if (Auth::user()->jabatan === 'GM' && $kegiatan->status !== 'Approved')
                    <button class="btn btn-primary px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                        data-bs-target="#gmUpdateModal">
                        Update Status
                    </button>
                @endif

                {{-- Finance & Accounting --}}
                @if (Auth::user()->jabatan === 'Finance & Accounting' && $kegiatan->status === 'Approved')
                    <button class="btn btn-info px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                        data-bs-target="#financeUpdateModal">
                        Selesaikan Pencairan
                    </button>
                @endif
            @endif
        </div>

        {{-- Card Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">Daftar Kebutuhan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="text-dark fw-semibold small bg-light">
                            <tr>
                                <th class="ps-4 border-0" style="width: 5%;">No</th>
                                <th class="border-0" style="width: 25%;">Hal</th>
                                <th class="border-0" style="width: 25%;">Rincian</th>
                                <th class="border-0" style="width: 10%;">Qty</th>
                                <th class="border-0" style="width: 15%;">Harga Satuan</th>
                                <th class="border-0" style="width: 12%;">Total</th>
                                <th class="border-0 text-center" style="width: 13%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kegiatan->rincian as $item)
                                <tr>
                                    <td class="ps-4 fw-medium text-muted">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold text-dark">{{ $item->hal }}</td>
                                    <td>{{ $item->rincian }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="{{ $item->id }}" data-hal="{{ $item->hal }}"
                                                data-rincian="{{ $item->rincian }}" data-qty="{{ $item->qty }}"
                                                data-harga="{{ $item->harga_satuan }}">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </button>

                                            <form action="{{ route('office.deleteRincian', $item->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus kebutuhan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                                    <i class="fas fa-trash"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <h5>Belum ada kebutuhan tercatat</h5>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($kegiatan->rincian->count() > 0)
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Menampilkan {{ $kegiatan->rincian->count() }} kebutuhan
                    </small>

                    <strong>
                        Rp {{ number_format($totalRincian, 0, ',', '.') }}
                    </strong>
                </div>
            @endif

        </div>
    </div>

    {{-- Modal Tambah Kebutuhan --}}
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('office.storeRincian', $kegiatan->id) }}" method="POST"
                class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="createModalLabel">Tambah Kebutuhan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Hal / Nama Kebutuhan</label>
                            <input type="text" name="hal" class="form-control form-control-lg"
                                placeholder="Contoh: Konsumsi" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Rincian</label>
                            <textarea name="rincian" class="form-control" rows="3"
                                placeholder="Detail kebutuhan, misal: Snack box 100 pcs"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Qty</label>
                            <input type="number" name="qty" class="form-control form-control-lg" min="1"
                                value="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Harga Satuan (Rp)</label>
                            <input type="number" name="harga_satuan" class="form-control form-control-lg"
                                min="0" placeholder="50000" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Kebutuhan --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="" method="POST" id="editForm" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editModalLabel">Edit Kebutuhan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Hal / Nama Kebutuhan</label>
                            <input type="text" name="hal" id="edit_hal" class="form-control form-control-lg"
                                required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Rincian</label>
                            <textarea name="rincian" id="edit_rincian" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Qty</label>
                            <input type="number" name="qty" id="edit_qty" class="form-control form-control-lg"
                                min="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Harga Satuan (Rp)</label>
                            <input type="number" name="harga_satuan" id="edit_harga"
                                class="form-control form-control-lg" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal HRD --}}
    <div class="modal fade" id="hrdUpdateModal" tabindex="-1" aria-labelledby="hrdUpdateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('office.UpdateStatusSelesai', $kegiatan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Selesai">
                <div class="modal-content">
                    <div class="modal-header text-dark">
                        <h5 class="modal-title" id="hrdUpdateModalLabel">
                            <i class="fas fa-check-circle me-2"></i>Selesaikan Kegiatan
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Apakah Anda yakin ingin menandai kegiatan ini sebagai <strong>Selesai</strong>?
                        </p>
                        <div class="alert alert-info py-2" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Setelah diselesaikan, status kegiatan akan berubah menjadi <strong>Selesai</strong> dan tidak
                            dapat diubah kembali.
                        </div>
                        <div class="mt-3">
                            <strong>Nama Kegiatan:</strong> {{ $kegiatan->nama_kegiatan ?? '-' }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Ya, Selesaikan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal GM --}}
    <div class="modal fade" id="gmUpdateModal" tabindex="-1" aria-labelledby="gmUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('office.UpdateStatusGM', $kegiatan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header text-dark">
                        <h5 class="modal-title" id="gmUpdateModalLabel">
                            <i class="fas fa-sync-alt me-2"></i>Update Status Kegiatan
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Pilih status baru untuk kegiatan ini:</p>
                        <div class="d-flex gap-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="statusMenunggu"
                                    value="Menunggu" {{ $kegiatan->status === 'Menunggu' ? 'checked' : '' }} required>
                                <label class="form-check-label fw-bold text-warning" for="statusMenunggu">
                                    <i class="bi bi-hourglass-split me-1"></i>Menunggu
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="statusApproved"
                                    {{ $kegiatan->status === 'Approved' ? 'checked' : '' }} value="Approved">
                                <label class="form-check-label fw-bold text-success" for="statusApproved">
                                    <i class="bi bi-check-circle me-1"></i>Approved
                                </label>
                            </div>
                        </div>
                        <div class="alert alert-info py-2" style="font-size: 0.85rem;">
                            Status akan diperbarui sesuai pilihan Anda. Notifikasi akan dikirim ke pihak terkait.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Status
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Finance --}}
    <div class="modal fade" id="financeUpdateModal" tabindex="-1" aria-labelledby="financeUpdateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('office.UpdateStatusFinance', $kegiatan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Pencairan">
                <div class="modal-content">
                    <div class="modal-header text-dark">
                        <h5 class="modal-title" id="financeUpdateModalLabel">
                            <i class="fas fa-cash-coin me-2"></i>Pencairan Dana Selesai
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Apakah Anda yakin dana untuk kegiatan ini telah <strong>dicairkan</strong>?</p>
                        <div class="alert alert-info py-2" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Status akan berubah menjadi <strong>Pencairan Selesai</strong>. Notifikasi akan dikirim ke HRD.
                        </div>
                        <div class="mt-3">
                            <strong>Nama Kegiatan:</strong> {{ $kegiatan->nama_kegiatan ?? '-' }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-check me-1"></i>Ya, Cairkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Karyawan --}}
    <div class="modal fade" id="pesertaModal" tabindex="-1" aria-labelledby="pesertaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="pesertaModalLabel">
                        <i class="bi bi-people-fill me-2"></i>Peserta Kegiatan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if ($karyawan->count())
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nama</th>
                                        <th>Jabatan</th>
                                        <th>Kedatangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($karyawan as $index => $absen)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="fw-semibold">
                                                {{ $absen->karyawan->nama_lengkap ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $absen->karyawan->jabatan ?? '-' }}
                                            </td>
                                            <td>{{ $absen->jam_masuk }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                            Tidak ada peserta kegiatan.
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>


    <style>
        @media (min-width: 768px) {
            .border-start-md {
                border-left: 1px solid #dee2e6 !important;
            }
        }
    </style>

    <script>
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const id = button.getAttribute('data-id');
            const hal = button.getAttribute('data-hal');
            const rincian = button.getAttribute('data-rincian');
            const qty = button.getAttribute('data-qty');
            const harga = button.getAttribute('data-harga');

            // Isi field
            document.getElementById('edit_hal').value = hal;
            document.getElementById('edit_rincian').value = rincian;
            document.getElementById('edit_qty').value = qty;
            document.getElementById('edit_harga').value = harga;

            const form = document.getElementById('editForm');
            form.action = `/office/kegiatan/update/rincian/${id}`;
        });
    </script>
@endsection
