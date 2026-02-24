@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0">Data Perbaikan Kendaraan</h3>
            @if (Auth::user()->jabatan === 'Driver')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPerbaikan">
                    Ajukan Perbaikan
                </button>
            @endif
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter Card --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small mb-1">Dari Tanggal</label>
                        <input type="date" id="minDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small mb-1">Sampai Tanggal</label>
                        <input type="date" id="maxDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <button id="resetFilter" class="btn btn-sm btn-secondary w-100">
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablePerbaikan" class="table table-bordered table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">No</th>
                                <th>Pengguna / Driver</th>
                                <th>Kendaraan</th>
                                <th>Kondisi</th>
                                <th>Tanggal Kejadian</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perbaikan as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->user->karyawan->nama_lengkap ?? ($item->user->name ?? '-') }}</td>
                                    <td>
                                        {{ $item->kendaraan }}
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $item->type_condition == 'Kecelakaan' ? 'Danger' : 'Info' }}">
                                            {{ $item->type_condition }}
                                        </span>
                                    </td>
                                    <td data-order="{{ $item->created_at }}">
                                        {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}
                                    </td>
                                    <td>
                                        {{ $item->status }}

                                        @if ($item->type_condition === 'Kecelakaan' && $item->estimasi > 1000000)
                                            <br>
                                            <small class="text-danger">
                                                Estimasi paling lambat pencairan 1 minggu dari tanggal pembuatan
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('office.detailPerbaikanKendaraan', $item->id) }}"
                                                class="btn btn-sm btn-info text-white" style="margin-right: 3px">
                                                Detail
                                            </a>

                                            @if (Auth::user()->jabatan === 'Driver')
                                                <form action="{{ route('office.deletePerbaikanKendaraan', $item->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($item->status === 'Diajukan')
                                                @if (Auth::user()->jabatan === 'GM')
                                                    <button type="button" class="btn btn-primary btnUpdateStatus"
                                                        data-bs-toggle="modal" data-bs-target="#ModalUpdateStatus"
                                                        data-id="{{ $item->id }}">
                                                        Approve
                                                    </button>
                                                @endif
                                            @elseif (Auth::user()->jabatan === 'Finance & Accounting' &&
                                                    !in_array($item->status, ['Diajukan', 'Selesai', 'Ditolak Oleh GM']))
                                                <button type="button" class="btn btn-primary btnUpdateStatus"
                                                    data-bs-toggle="modal" data-bs-target="#ModalUpdateStatus"
                                                    data-id="{{ $item->id }}">
                                                    Update
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada data pemeriksaan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahPerbaikan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Form Perbaikan Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('office.storePerbaikanKendaraan') }}" method="POST" id="formPerbaikan"
                        enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="id_user" value="{{ Auth::user()->id }}">

                        <div class="row g-4">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Kendaraan <span
                                        style="text-danger">*</span></label>
                                <select name="kendaraan" class="form-select">
                                    <option selected disabled>Pilih Kendaraan</option>
                                    <option value="H1">H1</option>
                                    <option value="Innova">Innova</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipe Kondisi <span
                                        style="text-danger">*</span></label>
                                <select name="type_condition" class="form-select">
                                    <option selected disabled>Pilih Kondisi</option>
                                    <option value="Perawatan">Perawatan</option>
                                    <option value="Kecelakaan">Kecelakaan</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tingkat Kerusakan <span
                                        style="text-danger">*</span></label>
                                <select name="type_vehicle_condition" class="form-select">
                                    <option selected disabled>Pilih Tingkat Kerusakan</option>
                                    <option value="Kerusakan Ringan">Kerusakan Ringan</option>
                                    <option value="Kerusakan Sedang">Kerusakan Sedang</option>
                                    <option value="Kerusakan Berat">Kerusakan Berat</option>
                                    <option value="Kerusakan Total">Kerusakan Total</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis Perbaikan <span
                                        style="text-danger">*</span></label>
                                <select name="type_repair" class="form-select">
                                    <option selected disabled>Pilih Jenis Perbaikan</option>
                                    <option value="Penggantian">Penggantian</option>
                                    <option value="Peningkatan">Peningkatan</option>
                                    <option value="Perbaikan">Perbaikan</option>
                                    <option value="Perbaikan Total">Perbaikan Total</option>
                                </select>
                            </div>

                            <div id="sectionKecelakaan" class="row g-4 d-none">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tanggal Kejadian <span
                                            style="text-danger">*</span></label>
                                    <input type="date" name="tanggal_kejadian" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Waktu Kejadian <span
                                            style="text-danger">*</span></label>
                                    <input type="time" name="waktu_kejadian" class="form-control">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Lokasi Kejadian <span
                                            style="text-danger">*</span></label>
                                    <input type="text" name="lokasi" class="form-control"
                                        placeholder="Masukkan lokasi kejadian">
                                </div>

                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Estimasi Biaya</label>
                                <input type="text" id="estimasi_display" class="form-control"
                                    placeholder="Masukkan estimasi biaya">
                                <input type="hidden" name="estimasi" id="estimasi">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi Kondisi</label>
                                <textarea name="deskripsi_kondisi" class="form-control" rows="4" placeholder="Jelaskan kondisi kendaraan..."></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Bukti (Foto / Video) <span
                                        style="text-danger">*</span></label>
                                <input type="file" name="bukti" class="form-control" accept="image/*,video/*">
                            </div>

                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formPerbaikan" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalUpdateStatus" tabindex="-1" role="dialog"
        aria-labelledby="ModalUpdateStatusLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('office.updateStatusPerbaikanKendaraan') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="modal_id">
                <input type="hidden" name="status_tracking" id="status_tracking_input">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalUpdateStatusLabel">Update Status</h5>
                    </div>

                    <div class="modal-body">

                        @if (auth()->user()->jabatan === 'GM')
                            <div class="text-center">
                                <p>Silakan pilih tindakan:</p>

                                <button type="button" class="btn btn-success m-2" onclick="setStatus('setujui')">
                                    Setujui
                                </button>

                                <button type="button" class="btn btn-danger m-2" onclick="setStatus('tolak')">
                                    Tolak
                                </button>
                            </div>
                        @else
                            <select name="status_tracking" class="form-select">
                                <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">
                                    Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi
                                </option>
                                <option value="Finance Menunggu Approve Direksi">
                                    Finance Menunggu Approve Direksi
                                </option>
                                <option value="Membuat Permintaan Ke Direktur Utama">
                                    Membuat Permintaan Ke Direktur Utama
                                </option>
                                <option value="Pengajuan sedang dalam proses Pencairan">
                                    Pengajuan sedang dalam proses Pencairan
                                </option>
                                <option value="Pencairan Sudah Selesai">
                                    Pencairan Sudah Selesai
                                </option>
                                <option value="Selesai">
                                    Selesai
                                </option>
                            </select>
                        @endif

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>

                        @if (auth()->user()->jabatan !== 'GM')
                            <button type="submit" class="btn btn-primary">
                                Update
                            </button>
                        @endif
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#tablePerbaikan').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                order: [
                    [3, 'desc']
                ], // Sort by date descending
                columnDefs: [{
                    orderable: false,
                    targets: 4
                }],
                pageLength: 25
            });

            // Custom date range filter
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const min = $('#minDate').val();
                const max = $('#maxDate').val();
                const dateStr = table.cell(dataIndex, 3).nodes().to$().attr('data-order');

                if (!dateStr) return true;
                if (!min && !max) return true;

                const date = new Date(dateStr);
                const minDate = min ? new Date(min) : null;
                const maxDate = max ? new Date(max) : null;

                if (minDate && date < minDate) return false;
                if (maxDate && date > maxDate) return false;

                return true;
            });

            // Date filter events
            $('#minDate, #maxDate').on('change', function() {
                table.draw();
            });

            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#minDate, #maxDate').val('');
                table.draw();
            });

            // Reset form when modal closes
            $('#modalTambahPerbaikan').on('hidden.bs.modal', function() {
                $('#formKondisi')[0].reset();
                $('#formKondisi select').each(function() {
                    $(this).val('1');
                });
                $('input[name="tanggal_pemeriksaan"]').val('{{ date('Y-m-d') }}');
            });
        });

        function setStatus(status) {
            document.getElementById('status_tracking_input').value = status;
            document.querySelector('#ModalUpdateStatus form').submit();
        }

        $(document).on('click', '.btnUpdateStatus', function() {
            let id = $(this).data('id');
            $('#modal_id').val(id);
        });

        document.addEventListener("DOMContentLoaded", function() {
            const displayInput = document.getElementById("estimasi_display");
            const hiddenInput = document.getElementById("estimasi");

            displayInput.addEventListener("input", function(e) {
                let value = this.value.replace(/\D/g, "");

                hiddenInput.value = value;

                if (value) {
                    this.value = formatRupiah(value);
                } else {
                    this.value = "";
                }
            });

            function formatRupiah(angka) {
                return "Rp " + angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });

        document.addEventListener('DOMContentLoaded', function() {


            const typeCondition = document.querySelector('select[name="type_condition"]');
            const sectionKecelakaan = document.getElementById('sectionKecelakaan');

            typeCondition.addEventListener('change', function() {

                if (this.value === 'Kecelakaan') {
                    sectionKecelakaan.classList.remove('d-none');
                } else {
                    sectionKecelakaan.classList.add('d-none');
                    sectionKecelakaan.querySelectorAll('input').forEach(input => input.value = '');
                }

            });

        });
    </script>
@endsection
