@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h4 class="mb-0 fw-bold text-dark">Data Laporan Analisis</h4>

            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('index.analysis') }}" method="GET" class="d-flex align-items-center mb-0">
                    <select name="year_filter" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="" {{ empty($selectedYear) ? 'selected' : '' }}>Semua Tahun</option>
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                Tahun {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                    Tambah Laporan
                </button>
            </div>
        </div>

        @php
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
        @endphp

        @forelse ($years as $year)
            <div class="mb-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold text-secondary mb-2 mb-md-0"><i class="bi bi-calendar-event me-2"></i>Tahun {{ $year }}</h5>
                    <button class="btn btn-sm btn-outline-primary editYearDescBtn"
                        data-year="{{ $year }}"
                        data-desc="{{ $yearDescriptions[$year] ?? '' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#yearDescModal">
                        Edit Deskripsi Tahun {{ $year }}
                    </button>
                </div>

                @if(!empty($yearDescriptions[$year]))
                    <div class="alert border-0 shadow-sm rounded-3 mb-4">
                        <strong>Deskripsi Tahun {{ $year }}:</strong><br>
                        {{ $yearDescriptions[$year] }}
                    </div>
                @endif

                <div class="row g-4">
                    @foreach ($months as $monthKey => $monthName)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden glass-force">
                                <div class="card-header bg-light border-0 py-3">
                                    <h6 class="mb-0 fw-bold text-dark">{{ $monthName }}</h6>
                                </div>
                                <div class="card-body p-3">
                                    @php
                                        $monthReports = $reports->where('year', $year)->where('month', $monthKey);
                                    @endphp

                                    @forelse ($monthReports as $item)
                                        <div class="p-3 mb-3 border rounded-3 bg-light">
                                            <div class="mb-2">
                                                <small class="text-muted d-block mb-1">Dibuat oleh: {{ $item->user->karyawan->nama_lengkap ?? 'Unknown' }}</small>
                                                <p class="mb-2 text-dark">{{ $item->description ?? 'Tidak ada deskripsi.' }}</p>
                                            </div>

                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                @if(is_array($item->file_paths) && count($item->file_paths) > 0)
                                                    @foreach($item->file_paths as $index => $path)
                                                        <a href="{{ route('download.analysis', ['id' => $item->id, 'index' => $index]) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                                                            Lihat File {{ $index + 1 }}
                                                        </a>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted small">Tidak ada lampiran file.</span>
                                                @endif
                                            </div>

                                            <div class="d-flex justify-content-end gap-2 border-top pt-2 mt-2">
                                                <button type="button" class="btn btn-sm btn-warning editBtn"
                                                    data-id="{{ $item->id }}"
                                                    data-desc="{{ $item->description }}"
                                                    data-year="{{ $item->year }}"
                                                    data-month="{{ $item->month }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal">
                                                    Edit
                                                </button>
                                                <form action="{{ route('destroy.analysis', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini beserta seluruh file di dalamnya?')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4">
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="alert alert-light text-center py-5 border rounded-4 shadow-sm">
                Belum ada data laporan analisis yang tersimpan di sistem.
            </div>
        @endforelse
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('store.analysis') }}" method="POST" enctype="multipart/form-data" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="createModalLabel">Tambah Laporan Analisis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tahun</label>
                            <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Bulan</label>
                            <select name="month" class="form-select" required>
                                @foreach($months as $key => $name)
                                    <option value="{{ $key }}" {{ date('n') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lampiran File (Dapat mengunggah lebih dari satu)</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST" enctype="multipart/form-data" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editModalLabel">Edit Laporan Analisis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tahun</label>
                            <input type="number" id="edit_year" name="year" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Bulan</label>
                            <select id="edit_month" name="month" class="form-select" required>
                                @foreach($months as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lampiran File Baru (Abaikan jika tidak mengubah file)</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                        <small class="text-danger mt-1 d-block">*Mengunggah file baru akan menimpa file lama secara keseluruhan.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="yearDescModal" tabindex="-1" aria-labelledby="yearDescModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('update.year.description.analysis') }}" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="yearDescModalLabel">Edit Deskripsi Tahun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" id="desc_year" name="year" class="form-control" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi Tahunan</label>
                        <textarea id="desc_text" name="description" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Deskripsi</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editBtn').on('click', function() {
                const id = $(this).data('id');
                const desc = $(this).data('desc');
                const year = $(this).data('year');
                const month = $(this).data('month');

                $('#edit_description').val(desc);
                $('#edit_year').val(year);
                $('#edit_month').val(month);

                let updateRoute = '{{ route("update.analysis", ":id") }}';
                $('#editForm').attr('action', updateRoute.replace(':id', id));
            });

            $('.editYearDescBtn').on('click', function() {
                const year = $(this).data('year');
                const desc = $(this).data('desc');

                $('#desc_year').val(year);
                $('#desc_text').val(desc);
            });
        });
    </script>
@endsection
