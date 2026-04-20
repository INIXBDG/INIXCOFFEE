@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h4 class="mb-0 fw-bold text-dark">Data Laporan Analisis</h4>

            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('index.analysis') }}" method="GET" class="d-flex align-items-center mb-0 gap-2">
                    <select name="year_filter" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="" {{ empty($selectedYear) ? 'selected' : '' }}>Semua Tahun</option>
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                Tahun {{ $y }}
                            </option>
                        @endforeach
                    </select>

                    <select name="quarter_filter" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="">Semua Triwulan</option>
                        @for($q = 1; $q <= 4; $q++)
                            <option value="{{ $q }}" {{ $selectedQuarter == $q ? 'selected' : '' }}>
                                Triwulan {{ $q }}
                            </option>
                        @endfor
                    </select>

                    <select name="month_filter" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="">Semua Bulan</option>
                        @php
                            $allMonths = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        @foreach($allMonths as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <button class="btn btn-info px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                    Tambah Laporan Bulanan
                </button>
            </div>
        </div>

        @php
            $quarters = [
                1 => ['name' => 'Triwulan 1', 'months' => [1 => 'Januari', 2 => 'Februari', 3 => 'Maret']],
                2 => ['name' => 'Triwulan 2', 'months' => [4 => 'April', 5 => 'Mei', 6 => 'Juni']],
                3 => ['name' => 'Triwulan 3', 'months' => [7 => 'Juli', 8 => 'Agustus', 9 => 'September']],
                4 => ['name' => 'Triwulan 4', 'months' => [10 => 'Oktober', 11 => 'November', 12 => 'Desember']],
            ];
        @endphp

        @forelse ($years as $year)
            @php
                $currentYearData = $yearDescriptions[$year] ?? null;

                $yearReports = $reports->where('year', $year);
                $filledMonthsCount = $yearReports->pluck('month')->unique()->count();

                $quarterMap = [
                    1 => [1, 2, 3],
                    2 => [4, 5, 6],
                    3 => [7, 8, 9],
                    4 => [10, 11, 12]
                ];
            @endphp

            <div class="mb-5">
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom p-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <h5 class="fw-bold text-dark mb-2 mb-md-0 border-start border-4 border-info ps-3">
                            Laporan Per Bulan <span class="text-secondary">- Tahun {{ $year }}</span>
                        </h5>
                        <button class="btn btn-sm btn-outline-info editYearDescBtn"
                            data-year="{{ $year }}"
                            data-desc="{{ $currentYearData?->description ?? '' }}"
                            data-note="{{ $currentYearData?->note ?? '' }}"
                            data-bs-toggle="modal"
                            data-bs-target="#yearDescModal">
                            Kelola Catatan & Deskripsi Tahun
                        </button>
                    </div>

                    <div class="card-body p-4 bg-light rounded-bottom-4">

                        @if(!empty($currentYearData?->description))
                            <div class="alert border border-info rounded-3 mb-4 bg-white shadow-sm d-flex gap-3 align-items-start">
                                <i class="bi bi-info-circle-fill text-info fs-4"></i>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Deskripsi {{ $year }}</h6>
                                    <p class="mb-0 text-dark small">{!! nl2br(e($currentYearData->description)) !!}</p>
                                </div>
                            </div>
                        @endif

                        <div class="row g-4">
                            @foreach ($allMonths as $monthKey => $monthName)
                                @php
                                    $isMonthInFilter = empty($selectedMonth) || $selectedMonth == $monthKey;
                                    $isMonthInQuarterFilter = empty($selectedQuarter) || in_array($monthKey, $quarterMap[$selectedQuarter]);
                                @endphp

                                @if($isMonthInFilter && $isMonthInQuarterFilter)
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="card h-100 border shadow-sm rounded-4 overflow-hidden bg-white">
                                            <div class="card-header bg-light border-bottom py-3">
                                                <h6 class="mb-0 fw-bold text-dark">{{ $monthName }}</h6>
                                            </div>
                                            <div class="card-body p-3">
                                                @php
                                                    $monthReports = $reports->where('year', $year)->where('month', $monthKey);
                                                @endphp

                                                @forelse ($monthReports as $item)
                                                    <div class="p-3 mb-3 border rounded-3 bg-light">
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block mb-1">Oleh: {{ $item->user->karyawan->nama_lengkap ?? 'Unknown' }}</small>
                                                            <p class="mb-2 text-dark">{!! nl2br(e($item->description ?? 'Tidak ada deskripsi.')) !!}</p>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                                            @if(is_array($item->file_paths) && count($item->file_paths) > 0)
                                                                @foreach($item->file_paths as $index => $path)
                                                                    <a href="{{ route('download.analysis', ['id' => $item->id, 'index' => $index]) }}" target="_blank" class="btn btn-sm btn-info flex-fill">
                                                                        Lihat Dokumen
                                                                    </a>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted small">Tidak ada lampiran.</span>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex justify-content-end gap-2 border-top pt-2 mt-2">
                                                            <button type="button" class="btn btn-sm btn-warning editBtn"
                                                                data-id="{{ $item->id }}"
                                                                data-desc="{{ $item->description }}"
                                                                data-year="{{ $item->year }}"
                                                                data-month="{{ $item->month }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editModal">Edit</button>
                                                            <form action="{{ route('destroy.analysis', $item->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin menghapus data ini?')">Hapus</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center py-4">
                                                        <span class="text-muted small">Belum ada data bulanan.</span>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mt-4 mb-5 bg-white">
                    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Statistik Laporan Tahunan {{ $year }}</h6>

                        <button class="btn btn-sm btn-outline-info editYearDescBtn"
                            data-year="{{ $year }}"
                            data-desc="{{ $currentYearData?->description ?? '' }}"
                            data-note="{{ $currentYearData?->note ?? '' }}"
                            data-bs-toggle="modal"
                            data-bs-target="#yearDescModal">
                            Update note
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-start mb-0 p-4 border rounded-4 bg-light shadow-sm">

                            <div class="col-12 col-md-4 d-flex justify-content-center mb-4 mb-md-0 position-relative">
                                <div style="width: 200px; height: 200px;">
                                    <canvas id="doughnutChart_{{ $year }}"></canvas>
                                </div>
                                <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                                    <span class="fs-4 fw-bold text-dark">{{ round(($filledMonthsCount / 12) * 100) }}%</span>
                                    <span class="d-block text-muted" style="font-size: 0.75rem; margin-top: -5px;">Selesai</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-8 ps-md-4">
                                <h5 class="fw-bold text-dark mb-3">Rincian Status Laporan</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <div class="d-flex flex-column p-3 rounded-3 bg-white border border-start border-4 border-secondary shadow-sm">
                                            <span class="text-muted fw-semibold small mb-1 text-uppercase">Total Target</span>
                                            <span class="fw-bold text-dark fs-5">12 Laporan</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex flex-column p-3 rounded-3 bg-white border border-start border-4 border-primary shadow-sm">
                                            <span class="text-muted fw-semibold small mb-1 text-uppercase">Data Terisi</span>
                                            <span class="fw-bold text-primary fs-5">{{ $filledMonthsCount }} Laporan</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex flex-column p-3 rounded-3 bg-white border border-start border-4 border-danger shadow-sm">
                                            <span class="text-muted fw-semibold small mb-1 text-uppercase">Belum Terisi</span>
                                            <span class="fw-bold text-danger fs-5">{{ 12 - $filledMonthsCount }} Laporan</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 bg-white border border-info rounded-3 shadow-sm border-start border-4">
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle text-info me-2"></i>Catatan Statistik</h6>
                                    <p class="mb-0 text-dark small">
                                        @if(!empty($currentYearData?->note))
                                            {!! nl2br(e($currentYearData->note)) !!}
                                        @else
                                            <span class="text-muted fst-italic">Belum ada catatan untuk statistik tahun ini.</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="fw-bold text-dark mb-0 border-start border-4 border-info ps-3">Analisis Triwulan</h5>
                    </div>
                    <div class="card-body p-4 bg-light rounded-bottom-4">
                        <div class="row g-4">
                            @foreach ($quarters as $qKey => $quarter)
                                @if(empty($selectedQuarter) || $selectedQuarter == $qKey)
                                    @php
                                        $qData = $quarterData[$year][$qKey] ?? null;
                                    @endphp
                                    <div class="col-12 col-md-6">
                                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                                            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold text-dark mb-0">{{ $quarter['name'] }}</h6>
                                                <button class="btn btn-sm btn-outline-info editQuarterDescBtn"
                                                    data-year="{{ $year }}"
                                                    data-quarter="{{ $qKey }}"
                                                    data-desc="{{ $qData->description ?? '' }}"
                                                    data-format="{{ $qData->format_nilai ?? '' }}"
                                                    data-nilai="{{ $qData->nilai ?? '' }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#quarterDescModal">
                                                    Update Data
                                                </button>
                                            </div>
                                            <div class="card-body p-4 bg-white rounded-bottom-4">

                                                {{-- Menampilkan Nilai jika tersedia --}}
                                                @if(!empty($qData->nilai))
                                                    <div class="mb-3 p-3 bg-light rounded-3 border-start border-4 border-info shadow-sm d-flex justify-content-between align-items-center">
                                                        <span class="fw-semibold text-muted small text-uppercase">Capaian Nilai</span>
                                                        <span class="fs-5 fw-bold text-dark">
                                                            @if($qData->format_nilai == 'Rupiah')
                                                                Rp {{ number_format($qData->nilai, 2, ',', '.') }}
                                                            @elseif($qData->format_nilai == 'Persentase')
                                                                {{ floatval($qData->nilai) }} %
                                                            @else
                                                                {{ floatval($qData->nilai) }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif

                                                @if(!empty($qData->description))
                                                    <p class="text-dark mb-3">{!! nl2br(e($qData->description)) !!}</p>
                                                @else
                                                    <p class="text-muted small fst-italic mb-3">Deskripsi triwulan belum diatur.</p>
                                                @endif

                                                <div class="border-top pt-3">
                                                    <h6 class="small fw-bold text-secondary mb-2">Lampiran File Triwulan:</h6>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @if(!empty($qData->file_paths) && count($qData->file_paths) > 0)
                                                            @foreach($qData->file_paths as $index => $path)
                                                                <a href="{{ route('download.quarter.analysis', ['year' => $year, 'quarter' => $qKey, 'index' => $index]) }}" target="_blank" class="btn btn-sm btn-info text-white">
                                                                    <i class="bi bi-file-earmark me-1"></i> lihat File Q{{ $qKey }}
                                                                </a>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted small">Tidak ada file terlampir.</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="fw-bold text-dark mb-0 border-start border-4 border-info ps-3">Analisis Tahun {{ $year }}</h5>
                    </div>
                    <div class="card-body p-4 bg-light rounded-bottom-4">
                        @php
                            $aData = $annualData[$year] ?? null;
                        @endphp
                        <div class="card border-0 shadow-sm rounded-4 bg-white">
                            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-dark mb-0">Dokumen Laporan Akhir Tahun {{ $year }}</h6>
                                <button class="btn btn-sm btn-info text-white editAnnualBtn"
                                    data-year="{{ $year }}"
                                    data-desc="{{ $aData->description ?? '' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#annualModal">
                                    Kelola Laporan Tahunan
                                </button>
                            </div>
                            <div class="card-body p-4">
                                @if(!empty($aData->description))
                                    <p class="text-dark mb-3">{!! nl2br(e($aData->description)) !!}</p>
                                @else
                                    <p class="text-muted small fst-italic mb-3">Belum ada deskripsi laporan tahunan.</p>
                                @endif

                                <div class="border-top pt-3">
                                    <h6 class="small fw-bold text-secondary mb-2">Lampiran Dokumen Tahunan:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if(!empty($aData->file_paths) && count($aData->file_paths) > 0)
                                            @foreach($aData->file_paths as $index => $path)
                                                <a href="{{ route('download.annual.analysis', ['year' => $year, 'index' => $index]) }}" target="_blank" class="btn btn-sm btn-info text-white">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i> Lihat Dokumen
                                                </a>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">Tidak ada file terlampir.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> @empty
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
                    <h5 class="modal-title fw-bold" id="createModalLabel">Tambah Laporan Bulanan</h5>
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
                                @foreach($allMonths as $key => $name)
                                    <option value="{{ $key }}" {{ date('n') == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lampiran File</label>
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

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST" enctype="multipart/form-data" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Edit Laporan Bulanan</h5>
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
                                @foreach($allMonths as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lampiran File Baru</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                        <small class="text-danger mt-1 d-block">*Mengunggah file baru akan menimpa file lama.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="yearDescModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('update.year.description.analysis') }}" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Update Deskripsi & Catatan Tahun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" id="desc_year" name="year" class="form-control bg-light" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea id="desc_text" name="description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note</label>
                        <textarea id="note_text" name="note" class="form-control" rows="4" placeholder="Masukkan catatan atau keterangan persentase..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="quarterDescModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('update.quarter.description.analysis') }}" method="POST" enctype="multipart/form-data" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Update Data Triwulan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tahun</label>
                            <input type="number" id="desc_quarter_year" name="year" class="form-control bg-light" readonly required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Triwulan</label>
                            <input type="number" id="desc_quarter_val" name="quarter" class="form-control bg-light" readonly required>
                        </div>
                    </div>

                    {{-- Penambahan Input Format dan Nilai --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Format Nilai (Opsional)</label>
                            <select id="desc_quarter_format" name="format_nilai" class="form-select">
                                <option value="">Pilih Format</option>
                                <option value="Angka">Angka Biasa</option>
                                <option value="Persentase">Persentase (%)</option>
                                <option value="Rupiah">Rupiah (Rp)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nilai (Opsional)</label>
                            <input type="number" step="0.01" id="desc_quarter_nilai" name="nilai" class="form-control" placeholder="Contoh: 85.50">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi Triwulan</label>
                        <textarea id="desc_quarter_text" name="description" class="form-control" rows="8"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lampiran Laporan Triwulan</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                        <small class="text-danger mt-1 d-block">*Mengunggah file baru akan menimpa seluruh file triwulan sebelumnya.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="annualModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('update.annual.description.analysis') }}" method="POST" enctype="multipart/form-data" class="modal-content shadow-lg border-0 rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Update Laporan Tahunan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun</label>
                        <input type="number" id="annual_year" name="year" class="form-control bg-light" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi Laporan Tahunan</label>
                        <textarea id="annual_desc" name="description" class="form-control" rows="8"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unggah Dokumen (Bisa banyak file)</label>
                        <input type="file" name="files[]" class="form-control" multiple>
                        <small class="text-danger mt-1 d-block small">*Unggahan baru akan mengganti file sebelumnya.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-white px-4">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
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
                const note = $(this).data('note');

                $('#desc_year').val(year);
                $('#desc_text').val(desc);
                $('#note_text').val(note);
            });

            $('.editQuarterDescBtn').on('click', function() {
                const year = $(this).data('year');
                const quarter = $(this).data('quarter');
                const desc = $(this).data('desc');
                const format = $(this).data('format');
                const nilai = $(this).data('nilai');

                $('#desc_quarter_year').val(year);
                $('#desc_quarter_val').val(quarter);
                $('#desc_quarter_text').val(desc);
                $('#desc_quarter_format').val(format);
                $('#desc_quarter_nilai').val(nilai);
            });

            $('.editAnnualBtn').on('click', function() {
                const year = $(this).data('year');
                const desc = $(this).data('desc');

                $('#annual_year').val(year);
                $('#annual_desc').val(desc);
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            Chart.register(ChartDataLabels);

            @forelse ($years as $year)
                @php
                    $yearReports = $reports->where('year', $year);
                    $filledMonthsCount = $yearReports->pluck('month')->unique()->count();
                @endphp

                const ctx_{{ $year }} = document.getElementById('doughnutChart_{{ $year }}').getContext('2d');
                const filled_{{ $year }} = {{ $filledMonthsCount }};
                const empty_{{ $year }} = 12 - filled_{{ $year }};

                new Chart(ctx_{{ $year }}, {
                    type: 'doughnut',
                    data: {
                        labels: ['Data Terisi', 'Belum Terisi'],
                        datasets: [{
                            data: [filled_{{ $year }}, empty_{{ $year }}],
                            backgroundColor: [
                                '#0d6efd',
                                '#e2e8f0'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.raw + ' Bulan';
                                    }
                                }
                            },
                            datalabels: {
                                formatter: (value, ctx) => {
                                    if (value === 0) return null;

                                    let sum = 0;
                                    let dataArr = ctx.chart.data.datasets[0].data;
                                    dataArr.map(data => {
                                        sum += data;
                                    });
                                    let percentage = (value * 100 / sum).toFixed(0) + "%";
                                    return percentage;
                                },
                                color: function(context) {
                                    return context.dataIndex === 0 ? '#ffffff' : '#495057';
                                },
                                font: {
                                    weight: 'bold',
                                    size: 14
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            @empty
            @endforelse
        });
    </script>
@endsection
