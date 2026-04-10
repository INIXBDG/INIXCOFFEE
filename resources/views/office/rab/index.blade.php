@extends('layouts_office.app')

@section('office_contents')
    <style>
        #pickupDriverCondition {
            display: none;
        }

        #pickupDriverCondition .hidePickup {
            display: none;
        }
    </style>
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
                <h4 class="mb-1 fw-bold text-dark">Pengajuan RAB</h4>
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
                                <th class="border-0" style="width: 25%;">RAB</th>
                                <th class="border-0" style="width: 15%;">Tipe</th>
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
                                        <div class="fw-semibold text-dark text-uppercase">{{ $item->tipe }}</div>
                                    </td>
                                    <td>
                                        @if ($item->tipe === 'pembelian' || $item->tipe === 'rekrutmen')
                                            <div class="text-dark fw-medium">-</div>
                                        @else
                                            <div class="text-dark fw-medium">
                                                {{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('d M Y') }}
                                            </div>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('H:i') }} WIB
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($item->tipe === 'pembelian' || $item->tipe === 'rekrutmen')
                                            <span class="badge bg-light text-dark border">-</span>
                                        @else
                                            <span class="badge bg-light text-dark border">
                                                <i class="fas fa-clock me-1"></i>{{ $item->lama_kegiatan }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="fw-medium text-dark">{{ $item->pic }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $item->status }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('office.downloadPdfRab', $item->id) }}">
                                                <button
                                                    class="btn btn-sm btn-outline-success d-flex align-items-center gap-1">
                                                    PDF
                                                </button>
                                            </a>
                                            <a href="{{ route('office.showRincian', $item->id) }}">
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center gap-1">
                                                    Detail
                                                </button>
                                            </a>

                                            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="{{ $item->id }}" data-nama="{{ $item->nama_kegiatan }}"
                                                data-tipe="{{ $item->tipe }}"
                                                data-waktu="{{ \Carbon\Carbon::parse($item->waktu_kegiatan)->format('Y-m-d\TH:i') }}"
                                                data-durasi="{{ $item->lama_kegiatan }}" data-pic="{{ $item->pic }}">
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
                                                <h5 class="text-muted mb-1">Belum ada RAB</h5>
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
                        <small class="text-muted">Menampilkan {{ $kegiatan->count() }} RAB</small>
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
                        <h5 class="modal-title fw-bold" id="createModalLabel">Pengajuan RAB Baru</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-3">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-file-invoice text-primary me-2"></i>Nama RAB
                            </label>
                            <input type="text" name="nama_kegiatan" class="form-control form-control-lg"
                                placeholder="Masukkan nama" required>
                        </div>

                        <div class="col-12 mb-2">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-layer-group text-primary me-2"></i>Tipe RAB
                            </label>
                            <select name="tipe" id="tipeRAB" class="form-select form-select-lg" required>
                                <option value="" selected disabled>Pilih tipe RAB</option>
                                <option value="kegiatan">Kegiatan</option>
                                <option value="pembelian">Pembelian</option>
                                <option value="rekrutmen">Rekrutmen</option>
                            </select>
                        </div>

                        <div id="pickupDriverCondition">
                            <hr class="hidePickup">
                            <small class="hidePickup text-danger">* digunakan hanya jika kegiatan diperlukan pengantaran driver!</small>

                            <div class="col-12 mb-2 hidePickup" style="display: none;">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-car text-primary me-2"></i>Pilih Driver
                                </label>
                                <select name="id_driver" class="form-select text-dark form-select-lg">
                                    <option value="" selected disabled>Pilih Driver</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-2 hidePickup" style="display: none;">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-car text-primary me-2"></i>Budget Kendaraan
                                </label>
                                <input type="text" id="budgetInput" class="form-control form-control-lg"
                                    placeholder="Rp 0 (opsional)">

                                <input type="hidden" name="budget" id="budgetHidden">
                            </div>

                            <div class="col-12 mb-2 hidePickup" style="display: none;">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-car text-primary me-2"></i>Lokasi Pengantaran
                                </label>
                                <input type="text" class="form-control form-control-lg"
                                    placeholder="lokasi kegiatan (opsional untuk driver)" name="lokasi">
                            </div>

                            <hr class="hidePickup">
                        </div>

                        {{-- Wrapper untuk field kondisional (Create) --}}
                        <div id="kegiatanFields" style="display: contents;">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-clock text-primary me-2"></i>Waktu Pelaksanaan
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

                        {{-- Wrapper untuk field kondisional (Edit) --}}
                        <div id="edit_kegiatanFields" style="display: contents;">
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
        document.addEventListener("DOMContentLoaded", function() {
            const tipeSelect = document.getElementById('tipeRAB');
            const pickupDriverCondition = document.getElementById('pickupDriverCondition');
            const driverFields = pickupDriverCondition.querySelectorAll('.hidePickup');
            const driverSelect = pickupDriverCondition.querySelector('select');

            tipeSelect.addEventListener('change', function() {

                if (this.value === 'kegiatan') {

                    pickupDriverCondition.style.display = 'block';

                    driverFields.forEach(field => {
                        field.style.display = 'block';
                    });

                    driverSelect.setAttribute('required', true);

                } else {

                    pickupDriverCondition.style.display = 'none';

                    driverFields.forEach(field => {
                        field.style.display = 'none';
                    });

                    driverSelect.removeAttribute('required');
                }
            });

            const input = document.getElementById("budgetInput");
            const hidden = document.getElementById("budgetHidden");

            input.addEventListener("input", function(e) {

                let value = e.target.value.replace(/\D/g, "");

                if (!value) {
                    hidden.value = "";
                    input.value = "";
                    return;
                }

                hidden.value = value;
                input.value = "Rp " + parseInt(value).toLocaleString("id-ID");
            });
        });

        document.addEventListener('DOMContentLoaded', function() {

            // ==========================================
            // 1. LOGIKA MODAL CREATE (Dropdown Change)
            // ==========================================
            const tipeSelect = document.getElementById('tipeRAB');
            const createFields = document.getElementById('kegiatanFields');

            function toggleCreateFields() {
                if (!tipeSelect || !createFields) return;

                if (tipeSelect.value === 'pembelian' || tipeSelect.value === 'rekrutmen') {
                    // Sembunyikan field
                    createFields.style.display = 'none';
                    // Hapus required
                    createFields.querySelectorAll('input').forEach(input => {
                        input.removeAttribute('required');
                        input.value = ''; // Reset nilai
                    });
                } else {
                    // Tampilkan field (contents agar grid tetap rapi)
                    createFields.style.display = 'contents';
                    // Tambah required
                    createFields.querySelectorAll('input').forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
            }

            if (tipeSelect) {
                tipeSelect.addEventListener('change', toggleCreateFields);
                toggleCreateFields(); // Inisialisasi awal
            }

            // ==========================================
            // 2. LOGIKA MODAL EDIT (Button Click)
            // ==========================================
            const editModal = document.getElementById('editModal');
            const editFields = document.getElementById('edit_kegiatanFields');

            if (editModal) {
                editModal.addEventListener('show.bs.modal', event => {
                    // Button yang memicu modal
                    const button = event.relatedTarget;

                    // Ambil data dari attribute
                    const id = button.getAttribute('data-id');
                    const nama = button.getAttribute('data-nama');
                    const tipe = button.getAttribute('data-tipe'); // Atribut baru
                    const waktu = button.getAttribute('data-waktu');
                    const durasi = button.getAttribute('data-durasi');
                    const pic = button.getAttribute('data-pic');

                    // Isi form dasar
                    editModal.querySelector('#edit_nama_kegiatan').value = nama;
                    editModal.querySelector('#edit_pic').value = pic;

                    // Set action form ke route update
                    const form = editModal.querySelector('#editForm');
                    form.action = `/office/kegiatan/update/${id}`;

                    // Logika Kondisional berdasarkan Tipe
                    if (editFields) {
                        const inputs = editFields.querySelectorAll('input');

                        if (tipe === 'pembelian' || tipe === 'rekrutmen') {
                            // Jika tipe pembelian: sembunyikan kolom waktu & durasi
                            editFields.style.display = 'none';
                            inputs.forEach(input => {
                                input.removeAttribute('required');
                                input.value = '';
                            });
                        } else {
                            // Jika tipe kegiatan: tampilkan & isi nilai
                            editFields.style.display = 'contents';
                            inputs.forEach(input => input.setAttribute('required', 'required'));

                            editModal.querySelector('#edit_waktu_kegiatan').value = waktu;
                            editModal.querySelector('#edit_lama_kegiatan').value = durasi;
                        }
                    }
                });
            }
        });
    </script>
@endsection
