@extends('layouts_kpi.app')
@section('kpi_contents')
    @php
        $allowedPositions = ['Direktur Utama', 'Direktur', 'Education Manager', 'GM', 'SPV Sales', 'Koordinator ITSM'];
    @endphp

    <div class="modal fade" id="detailTargetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div id="bodyContentDetailTarget" class="p-0"></div>
            </div>
        </div>
    </div>

    <div class="container flex-grow-1 mt-4" id="kpiPersonalContainer">

        @if(in_array(auth()->user()->jabatan, $allowedPositions))
            <div class="mb-2 mt-2 p-3 text-end">
                <a href="{{ route('kpi.overview.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        @endif

        <div class="profile-card position-relative">
            <div class="skeleton-overlay" id="skelProfile">
                <div class="d-flex align-items-center flex-wrap gap-4 p-3">
                    <div class="skeleton" style="width: 80px; height: 80px; border-radius: 50%;"></div>
                    <div style="flex: 1;">
                        <div class="skeleton mb-3" style="width: 40%; height: 32px;"></div>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="skeleton" style="width: 120px; height: 28px; border-radius: 50px;"></div>
                            <div class="skeleton" style="width: 150px; height: 28px; border-radius: 50px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-4">
                <div class="avatar-circle"><i class="fa-solid fa-user"></i></div>
                <div>
                    <h3 class="fw-bold mb-2 text-white" id="userName">Loading...</h3>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge"><i class="fa-solid fa-briefcase me-1"></i><span id="userJabatan">-</span></span>
                        <span class="badge"><i class="fa-solid fa-building me-1"></i><span id="userDivisi">-</span></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @php $stats = [['id'=>'skelStat1', 'label'=>'Total Target', 'icon'=>'fa-bullseye', 'color'=>'primary'], ['id'=>'skelStat2', 'label'=>'Rata-rata Progress', 'icon'=>'fa-chart-line', 'color'=>'success'], ['id'=>'skelStat3', 'label'=>'KPI Aktif', 'icon'=>'fa-clock', 'color'=>'warning'], ['id'=>'skelStat4', 'label'=>'KPI Selesai', 'icon'=>'fa-check-circle', 'color'=>'info']]; @endphp
            @foreach($stats as $stat)
            <div class="col-md-3">
                <div class="stat-card h-100 position-relative">
                    <div class="skeleton-overlay" id="{{ $stat['id'] }}">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div style="flex: 1;">
                                <div class="skeleton mb-2" style="width: 60%; height: 14px;"></div>
                                <div class="skeleton" style="width: 40%; height: 28px;"></div>
                            </div>
                            <div class="skeleton" style="width: 48px; height: 48px; border-radius: 12px;"></div>
                        </div>
                    </div>
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div><small>{{ $stat['label'] }}</small><h4 class="mb-0" id="stat{{ $loop->index }}">{{ $loop->index === 0 ? '0 Target' : '0' }}</h4></div>
                        <div class="stat-icon bg-{{ $stat['color'] }} bg-opacity-10 text-{{ $stat['color'] }}"><i class="fa-solid {{ $stat['icon'] }}"></i></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="content-card h-100 position-relative">
                    <div class="skeleton-overlay" id="skelChartBar">
                        <div class="card-body">
                            <div class="skeleton mb-3" style="width: 30%; height: 20px;"></div>
                            <div class="skeleton" style="width: 100%; height: 350px;"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fa-solid fa-chart-bar"></i> Performa KPI Saya</h6>
                        <div style="height: 350px;"><canvas id="performanceChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card h-100 position-relative">
                    <div class="skeleton-overlay" id="skelChartPie">
                        <div class="card-body">
                            <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>
                            <div class="skeleton" style="width: 100%; height: 350px;"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fa-solid fa-chart-pie"></i> Status Target</h6>
                        <div style="height: 350px;"><canvas id="statusPieChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <h6 class="mb-0"><i class="fa-solid fa-list-check"></i> Semua Target Pribadi Saya</h6>
                    <div class="btn-group filter-btn-group">
                        <button class="btn active" data-filter="all">Semua</button>
                        <button class="btn" data-filter="active">Aktif</button>
                        <button class="btn" data-filter="completed">Selesai</button>
                    </div>
                </div>

                <div class="row" id="targetCardContainer">
                    @for($i = 0; $i < 6; $i++)
                    <div class="col-12 col-sm-6 col-lg-4 mb-4">
                        <div class="target-card border-start border-4 border-secondary" style="pointer-events: none; min-height: 280px;">
                            <div class="card-body-inner">
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="skeleton" style="width: 30%; height: 20px; border-radius: 4px;"></div>
                                    <div class="skeleton" style="width: 25%; height: 20px; border-radius: 4px;"></div>
                                </div>
                                <div class="skeleton mb-3" style="width: 80%; height: 24px;"></div>
                                <div class="skeleton mb-3" style="width: 100%; height: 40px;"></div>
                                <div class="mt-auto">
                                    <div class="skeleton mb-2" style="width: 40%; height: 12px;"></div>
                                    <div class="skeleton mb-3" style="width: 100%; height: 8px; border-radius: 4px;"></div>
                                    <div class="d-flex justify-content-between mt-3 pt-2 border-top">
                                        <div class="skeleton" style="width: 30%; height: 16px;"></div>
                                        <div class="skeleton" style="width: 30%; height: 16px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFormManual" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-scrollable">
            <form id="formManualValue" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="title-icon"><i class="fa-solid fa-pen-to-square"></i></span> Isi Manual Target</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="manualValueId">
                        <div class="mb-3">
                            <label class="form-label">Format Nilai</label>
                            <select class="form-select" id="manual_format">
                                <option value="angka">Angka</option>
                                <option value="persen">Persen (%)</option>
                                <option value="rupiah">Rupiah (Rp)</option>
                            </select>
                        </div>
                        <div id="doubleInputArea" style="display:none;">
                            <div class="mb-3"><label class="form-label">Biaya Gaji Tahunan</label><input type="text" class="form-control" id="biaya_gaji_display"><input type="hidden" name="biaya_gaji_tahunan" id="biaya_gaji_tahunan" required></div>
                            <div class="mb-3"><label class="form-label">Biaya BPJS Tahunan</label><input type="text" class="form-control" id="biaya_bpjs_display"><input type="hidden" name="biaya_bpjs_tahunan" id="biaya_bpjs_tahunan" required></div>
                            <div class="mb-3"><label class="form-label">Biaya Rekrutmen Tahunan</label><input type="text" class="form-control" id="biaya_rekrutmen_display"><input type="hidden" name="biaya_rekrutmen_tahunan" id="biaya_rekrutmen_tahunan" required></div>
                        </div>
                        <div class="mb-3" id="singleInputArea">
                            <label class="form-label">Masukan Nilai</label>
                            <input type="text" class="form-control" id="manual_value_display">
                            <input type="hidden" name="manual_value" id="manual_value">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dokumen Pendukung</label>
                            <input type="file" class="form-control" name="manual_document" id="manual_document" accept="image/*,.pdf">
                        </div>
                        <div id="documentPreview" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <input type="hidden" id="currentKaryawanId" value="{{ $targetId ?? Auth::id() }}">
    
    <script src="{{ asset('assets/vendor/libs/chartjs/chart.js') }}"></script>
    <script>
        window.authUser = {
            nama: "{{ auth()->user()->karyawan->nama_lengkap ?? 'User' }}",
            jabatan: "{{ auth()->user()->jabatan ?? '-' }}"
        };

        window.KPI_CONFIG = {
            targetId: {{ $targetId ?? auth()->user()->id }},
            currentYear: {{ now()->year }},
            csrfToken: "{{ csrf_token() }}",
            dataPersonalRoute: "{{ route('kpi.overview.dataPersonal') }}",
            detailRoute: "{{ route('kpi.detail') }}",
            manualValueRoute: "{{ route('kpi.manualValue') }}",
            updateTargetPerSalesRoute: "{{ route('kpi.overview.updateTargetPerSales') }}",
            updateGapKompetensiRoute: "{{ route('kpi.updateGapKompetensi') }}"
        };

        window.allowedDoubleManualRoutes = []; 
    </script>

    <script defer src="{{ asset('assets/js/kpi/kpiPersonal.js') }}"></script>
@endsection