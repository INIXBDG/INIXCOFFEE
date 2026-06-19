@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0">Data Perbaikan Kendaraan</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPerbaikan">
                Ajukan Perbaikan
            </button>
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Alert error --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        {{-- Filter & Export Card --}}
        <div class="card shadow-sm mb-3 glass-force">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Dari Tanggal</label>
                        <input type="date" id="minDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1">Sampai Tanggal</label>
                        <input type="date" id="maxDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <button id="resetFilter" class="btn btn-sm btn-secondary w-100">
                            Reset Filter
                        </button>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="dropup w-100">

                            <button class="btn btn-success btn-sm dropdown-toggle w-100" type="button" id="exportDropup"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Export
                            </button>

                            <ul class="dropdown-menu w-100" aria-labelledby="exportDropup">
                                <li>
                                    <a class="dropdown-item" href="#" id="exportPdfBtn">
                                        Export PDF
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" id="exportExcelBtn">
                                        Export Excel
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <form id="exportExcelForm" action="{{ route('office.excelExportPerbaikan') }}" method="GET"
                            target="_blank" style="display:none">

                            <input type="hidden" name="from" id="exportFrom">
                            <input type="hidden" name="to" id="exportTo">

                        </form>

                        <form id="exportPdfForm" action="{{ route('office.pdfExportPerbaikan') }}" method="GET"
                            target="_blank" style="display:none">

                            <input type="hidden" name="from" id="exportFrom">
                            <input type="hidden" name="to" id="exportTo">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm glass-force">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablePerbaikan" class="table table-bordered table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">No</th>
                                <th>Pengguna / Driver</th>
                                <th>Kendaraan</th>
                                <th>Kondisi</th>
                                <th>Tanggal Perbaikan</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        {{-- Table Body --}}
                        <tbody>
                            @forelse ($perbaikan as $item)
                                @php
                                    $rawStatus = $item->getOriginal('status');
                                    $displayStatus = $item->status; // sudah melalui accessor (sync dengan tracking)
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->user->karyawan->nama_lengkap ?? ($item->user->name ?? '-') }}</td>
                                    <td>{{ $item->kendaraan }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->type_condition == 'Kecelakaan' ? 'Danger' : 'Info' }}">
                                            {{ $item->type_condition }}
                                        </span>
                                    </td>
                                    <td
                                        data-order="{{ $item->tanggal_perbaikan ? \Carbon\Carbon::parse($item->tanggal_perbaikan)->format('Y-m-d') : '' }}">
                                        {{ $item->tanggal_perbaikan ? \Carbon\Carbon::parse($item->tanggal_perbaikan)->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        {{ $displayStatus }}
                                        @if ($item->type_condition === 'Kecelakaan' && $item->estimasi > 1000000)
                                            <br><small class="text-danger">
                                                Estimasi paling lambat pencairan 1 minggu dari tanggal pembuatan
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Aksi
                                            </button>
                                            <ul class="dropdown-menu">

                                                {{-- Detail --}}
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('office.detailPerbaikanKendaraan', $item->id) }}">
                                                        Detail
                                                    </a>
                                                </li>

                                                {{-- Hapus (Driver only) --}}
                                                <li>
                                                    <form action="{{ route('office.deletePerbaikanKendaraan', $item->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </li>

                                                {{-- Invoice (jika sudah selesai dan ada invoice) --}}
                                                @if ($item->status === 'Selesai' && $item->invoice)
                                                    <li>
                                                        <button type="button" class="dropdown-item btnViewInvoice"
                                                            data-bs-toggle="modal" data-bs-target="#ModalViewInvoice"
                                                            data-id="{{ $item->id }}">
                                                            Invoice
                                                        </button>
                                                    </li>
                                                @endif

                                                {{-- Selesaikan (Driver upload invoice) --}}
                                                <li>
                                                    <button type="button" class="dropdown-item btnSelesaiPerbaikan"
                                                        data-bs-toggle="modal" data-bs-target="#modalSelesaiPerbaikan"
                                                        data-id="{{ $item->id }}">
                                                        Selesaikan
                                                    </button>
                                                </li>


                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data pemeriksaan</td>
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
                <form action="{{ route('office.storePerbaikanKendaraan') }}" method="POST" id="formPerbaikan"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Form Perbaikan Kendaraan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

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

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estimasi Biaya</label>
                                <input type="text" id="estimasi_display" class="form-control"
                                    placeholder="Masukkan estimasi biaya">
                                <input type="hidden" name="estimasi" id="estimasi">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vendor</label>
                                <select name="vendor" class="form-select" id="vendor">
                                    <option selected disabled>Pilih Vendor</option>
                                    @foreach ($vendor as $bengkel)
                                        <option value="{{ $bengkel->id }}">{{ $bengkel->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi Kondisi</label>
                                <textarea name="deskripsi_kondisi" class="form-control" rows="4"
                                    placeholder="Jelaskan kondisi kendaraan..."></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Bukti (Foto / Video) <span
                                        style="text-danger">*</span></label>
                                <input type="file" name="bukti" class="form-control" accept="image/*,video/*">
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSelesaiPerbaikan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Selesai Perbaikan Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('office.selesaiPerbaikanKendaraan') }}" method="POST" id="formSelesaiPerbaikan"
                        enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="id" id="modal_selesai_id">
                        <input type="hidden" name="id_user" value="{{ Auth::user()->id }}">

                        <div class="row g-4">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Tanggal Perbaikan</label>
                                <input type="date" name="tanggal_perbaikan" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi Perbaikan</label>
                                <textarea name="deskripsi_perbaikan" class="form-control" rows="4"
                                    placeholder="Jelaskan perbaikan kendaraan..."></textarea required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Invoice<span
                                                style="text-danger">*</span></label>
                                        <input type="file" name="invoice" class="form-control" required>
                                    </div>

                                </div>
                            </form>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" form="formSelesaiPerbaikan" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($perbaikan) > 0)
                            <div class="modal fade" id="ModalViewInvoice" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content shadow">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Invoice Perbaikan Kendaraan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">

                                                <div class="row g-4">

                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">Tanggal Perbaikan</label>
                                                        <input type="date" name="tanggal_perbaikan" class="form-control" disabled value="{{ $item?->tanggal_perbaikan }}">
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold">Deskripsi Perbaikan</label>
                                                        <textarea name="deskripsi_perbaikan" class="form-control" rows="4" disabled>{{ $item?->deskripsi_perbaikan }}</textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Invoice<span style="text-danger">*</span></label>
                                            @php
                                                $extension = strtolower(pathinfo($item?->invoice, PATHINFO_EXTENSION));
                                                $fileUrl = asset('storage/' . $item?->invoice);
                                            @endphp

                                            <div class="mb-3">

                                                @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']))
                                                    <img src="{{ $fileUrl }}" class="img-fluid rounded shadow-sm border"
                                                        style="max-height:250px;">
                                                @elseif (in_array($extension, ['mp4', 'mov', 'avi', 'webm']))
                                                    <video class="rounded shadow-sm border" style="max-height:250px;" controls>
                                                        <source src="{{ $fileUrl }}">
                                                        Browser tidak mendukung video.
                                                    </video>
                                                @elseif ($extension === 'pdf')
                                                    <iframe src="{{ $fileUrl }}" class="w-100 border rounded"
                                                        style="height:400px;"></iframe>
                                                @elseif (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
                                                    <div class="alert alert-info">
                                                        File dokumen tidak bisa ditampilkan langsung.<br>
                                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                                            Download / Buka File
                                                        </a>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning">
                                                        File tidak dapat ditampilkan.<br>
                                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-secondary mt-2">
                                                            Download File
                                                        </a>
                                                    </div>
                                                @endif

                                                <div class="mt-2">
                                                    <a href="{{ $fileUrl }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-download"></i> Download Invoice
                                                    </a>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif


    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            const table = $('#tablePerbaikan').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                order: [
                    [4, 'desc']
                ], // Sort by date descending
                columnDefs: [{
                    orderable: false,
                    targets: 6
                }],
                pageLength: 25
            });

            // Custom date range filter
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                const min = $('#minDate').val();
                const max = $('#maxDate').val();
                const dateStr = table.cell(dataIndex, 4).nodes().to$().attr('data-order');

                // Jika data kosong, tetap tampil
                if (!dateStr) return true;
                if (!min && !max) return true;

                // Pastikan format tanggal valid (YYYY-MM-DD)
                const date = dateStr ? new Date(dateStr + 'T00:00:00') : null;
                const minDate = min ? new Date(min + 'T00:00:00') : null;
                const maxDate = max ? new Date(max + 'T23:59:59') : null;

                if (minDate && date < minDate) return false;
                if (maxDate && date > maxDate) return false;

                return true;
            });

            // Date filter events
            $('#minDate, #maxDate').on('change', function () {
                table.draw();
            });

            // Reset filter
            $('#resetFilter').on('click', function () {
                $('#minDate, #maxDate').val('');
                table.draw();
            });

            // Reset form when modal closes
            $('#modalTambahPerbaikan').on('hidden.bs.modal', function () {
                $('#formKondisi')[0].reset();
                $('#formKondisi select').each(function () {
                    $(this).val('1');
                });
                $('input[name="tanggal_pemeriksaan"]').val('{{ date('Y-m-d') }}');
            });
        });

        function setStatus(status) {
            document.getElementById('status_tracking_input').value = status;
            document.querySelector('#ModalUpdateStatus form').submit();
        }

        $(document).on('click', '.btnUpdateStatus', function () {
            let id = $(this).data('id');
            $('#modal_id').val(id);
        });

        $(document).on('click', '.btnSelesaiPerbaikan', function () {
            let id = $(this).data('id');
            $('#modal_selesai_id').val(id);
        });

        document.addEventListener("DOMContentLoaded", function () {
            const displayInput = document.getElementById("estimasi_display");
            const hiddenInput = document.getElementById("estimasi");

            displayInput.addEventListener("input", function (e) {
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

        document.addEventListener('DOMContentLoaded', function () {


            const typeCondition = document.querySelector('select[name="type_condition"]');
            const sectionKecelakaan = document.getElementById('sectionKecelakaan');

            typeCondition.addEventListener('change', function () {

                if (this.value === 'Kecelakaan') {
                    sectionKecelakaan.classList.remove('d-none');
                } else {
                    sectionKecelakaan.classList.add('d-none');
                    sectionKecelakaan.querySelectorAll('input').forEach(input => input.value = '');
                }

            });

        });
        // Export PDF button
        $('#exportPdfBtn').on('click', function (e) {
            e.preventDefault();
            // Ambil tanggal dari filter
            const from = $('#minDate').val();
            const to = $('#maxDate').val();
            $('#exportFrom').val(from);
            $('#exportTo').val(to);
            $('#exportPdfForm').submit();
        });

        // Export Excel button
        $('#exportExcelBtn').on('click', function (e) {
            e.preventDefault();
            const from = $('#minDate').val();
            const to = $('#maxDate').val();
            $('#exportFrom').val(from);
            $('#exportTo').val(to);
            $('#exportExcelForm').submit();
        });
    </script>
@endsection