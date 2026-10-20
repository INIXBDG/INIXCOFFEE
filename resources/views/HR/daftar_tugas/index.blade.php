@extends('layout_HR.app')

@section('content_HR')
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, .02);
        }

        .task-text {
            transition: all .3s ease;
        }

        .bukti-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .bukti-badge.both {
            background: #d1e7dd;
            color: #0f5132;
        }

        .bukti-badge.partial {
            background: #fff3cd;
            color: #664d03;
        }

        .bukti-badge.none {
            background: #f8f9fa;
            color: #6c757d;
        }

        .bukti-preview {
            cursor: pointer;
            transition: transform .2s;
        }

        .bukti-preview:hover {
            transform: scale(1.05);
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }

        .chart-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }

        .photo-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 768px) {
            .photo-comparison {
                grid-template-columns: 1fr;
            }
        }

        .photo-comparison .photo-box {
            text-align: center;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .photo-comparison .photo-box img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }

        .photo-comparison .photo-box .label {
            font-weight: 600;
            margin-bottom: 4px;
            color: #495057;
        }

        .time-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 12px;
            background: #fff;
            border: 1px solid #dee2e6;
            color: #495057;
        }
    </style>

    @if (Auth::user()->jabatan !== 'HRD')
        <div class="container-fluid py-5">
            <div class="alert alert-danger border-0 shadow-sm rounded-4 text-center py-5">
                <i class="bx bx-lock-alt text-danger mb-2" style="font-size: 3rem;"></i>
                <h5 class="fw-bold">Akses Ditolak</h5>
                <p class="mb-0 text-muted small">Halaman ini bersifat View-Only dan hanya dapat diakses oleh HRD.</p>
            </div>
        </div>
    @else
        <div class="container-fluid py-4">
            <!-- Header Halaman (View-Only HRD) -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Daftar Tugas Office Boy</h4>
                    <p class="text-muted small mb-0">Mode pantau pelaksanaan tugas aktif dan grafik kinerja Office Boy.</p>
                </div>
                <div>
                    <span class="badge bg-secondary px-3 py-2 rounded-pill">
                        <i class="bx bx-show me-1"></i> Mode Pantau HRD (View Only)
                    </span>
                </div>
            </div>

            <!-- BAGIAN 1: DATA TUGAS AKTIF (VIEW ONLY) -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden glass-force">
                <div class="card-header border-0 py-3">
                    <div class="row align-items-center g-3">
                        <div class="col-md-5">
                            <h5 class="mb-0 fw-semibold" id="dynamicTitle">
                                Tugas Aktif - {{ now()->translatedFormat('l, d F Y') }}
                            </h5>
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                                <select id="filterTipe" class="form-select form-select-sm" style="width:auto">
                                    <option value="all" selected>Semua Tipe</option>
                                    <option value="Harian">Harian</option>
                                    <option value="Mingguan">Mingguan</option>
                                    <option value="Bulanan">Bulanan</option>
                                    <option value="Quartal">Quartal</option>
                                    <option value="Semester">Semester</option>
                                    <option value="Tahunan">Tahunan</option>
                                </select>
                                <select id="filterTipeTurunan" class="form-select form-select-sm" style="width:auto">
                                    <option value="all" selected>Semua Shift</option>
                                    <option value="Shift 1">Shift 1</option>
                                    <option value="Shift 2">Shift 2</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                                <input type="date" id="filterTanggal" class="form-control form-control-sm"
                                    style="width:auto" value="{{ now()->format('Y-m-d') }}">
                                <button class="btn btn-outline-secondary btn-sm" id="btnResetFilter" title="Reset Filter">
                                    <i class="bx bx-reset"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="text-dark fw-semibold small bg-light">
                                <tr>
                                    <th class="ps-4 border-0" style="width:12%">Status</th>
                                    <th class="border-0" style="width:30%">Tugas</th>
                                    <th class="border-0" style="width:12%">Tipe</th>
                                    <th class="border-0" style="width:12%">Shift</th>
                                    <th class="border-0" style="width:15%">Karyawan</th>
                                    <th class="border-0" style="width:10%">Deadline</th>
                                    <th class="border-0 text-center" style="width:9%">Bukti</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Memuat data tugas...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: GRAFIK KINERJA TUGAS -->
            <div class="card border-0 shadow-sm rounded-4 mt-4 glass-force">
                <div class="card-header border-0 py-3">
                    <h5 class="mb-0 fw-semibold">Grafik Kinerja Tugas</h5>
                </div>
                <div class="card-body">
                    <div class="chart-filters">
                        <select id="chartPeriod" class="form-select form-select-sm" style="width:auto">
                            <option value="weekly">Per Minggu</option>
                            <option value="monthly" selected>Per Bulan</option>
                            <option value="quarterly">Per 3 Bulan</option>
                            <option value="yearly">Per Tahun</option>
                        </select>
                        <select id="chartKaryawan" class="form-select form-select-sm" style="width:auto">
                            <option value="all">Semua Karyawan</option>
                            <?php foreach (($officeBoy ?? []) as $ob): ?>
                                <option value="{{ $ob->id }}">{{ $ob->nama_lengkap }}</option>
                            <?php endforeach; ?>
                        </select>
                        <input type="date" id="chartStartDate" class="form-control form-control-sm"
                            style="width:auto" value="{{ now()->subMonth()->format('Y-m-d') }}">
                        <input type="date" id="chartEndDate" class="form-control form-control-sm"
                            style="width:auto" value="{{ now()->format('Y-m-d') }}">
                        <button class="btn btn-primary btn-sm" id="btnLoadChart">
                            <i class="bx bx-refresh"></i> Load Grafik
                        </button>
                    </div>
                    <div class="chart-container">
                        <canvas id="taskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL PREVIEW BUKTI (MURNI VIEW-ONLY) -->
        <div class="modal fade" id="modalPreviewBukti" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="previewModalTitle">Detail Bukti</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="previewModalBody">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memastikan Chart.js tersedia -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const filterTipeEl = document.getElementById('filterTipe');
                const filterTipeTurunanEl = document.getElementById('filterTipeTurunan');
                const filterTanggalEl = document.getElementById('filterTanggal');
                const btnResetFilterEl = document.getElementById('btnResetFilter');
                const dynamicTitleEl = document.getElementById('dynamicTitle');
                const tbodyEl = document.getElementById('tbody');

                const chartPeriodEl = document.getElementById('chartPeriod');
                const chartKaryawanEl = document.getElementById('chartKaryawan');
                const chartStartDateEl = document.getElementById('chartStartDate');
                const chartEndDateEl = document.getElementById('chartEndDate');
                const btnLoadChartEl = document.getElementById('btnLoadChart');

                const today = "{{ now()->format('Y-m-d') }}";
                let taskChart = null;

                function updateTitle() {
                    const t = filterTipeEl.value;
                    const d = filterTanggalEl.value;
                    if (!d) return;
                    const dt = new Date(d + 'T00:00:00');
                    const tipeText = t === 'all' ? 'Semua Tipe' : t;
                    dynamicTitleEl.textContent =
                        `Tugas Aktif ${tipeText} - ${dt.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}`;
                }

                function parseBukti(bukti) {
                    const defaultData = {
                        before: null,
                        after: null,
                        waktu_mulai: null,
                        waktu_selesai: null
                    };
                    if (!bukti) return defaultData;
                    try {
                        if (typeof bukti === 'string' && bukti.trim().startsWith('{')) {
                            return Object.assign({}, defaultData, JSON.parse(bukti));
                        }
                        if (typeof bukti === 'object') {
                            return Object.assign({}, defaultData, bukti);
                        }
                        return Object.assign({}, defaultData, { before: bukti });
                    } catch (e) {
                        return Object.assign({}, defaultData, { before: bukti });
                    }
                }

                function formatWaktu(waktuStr) {
                    if (!waktuStr) return 'Belum tercatat';
                    // Jika input berupa jam saja (contoh: "08:30")
                    if (/^\d{2}:\d{2}(:\d{2})?$/.test(waktuStr.trim())) {
                        return waktuStr.trim() + ' WIB';
                    }
                    const dt = new Date(waktuStr.replace(' ', 'T'));
                    if (isNaN(dt.getTime())) return waktuStr;
                    return dt.toLocaleString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) + ' WIB';
                }

                function getBuktiStatus(bukti) {
                    const data = parseBukti(bukti);
                    const hasBefore = !!data.before;
                    const hasAfter = !!data.after;

                    if (hasBefore && hasAfter) {
                        return {
                            class: 'both',
                            text: 'Before + After',
                            rowClass: '',
                            canView: true
                        };
                    }
                    if (hasBefore || hasAfter) {
                        return {
                            class: 'partial',
                            text: '1 Foto',
                            rowClass: 'table-warning',
                            canView: true
                        };
                    }
                    return {
                        class: 'none',
                        text: 'Belum Ada',
                        rowClass: 'table-danger',
                        canView: false
                    };
                }

                function loadData() {
                    tbodyEl.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                <span class="text-muted small">Memuat data tugas...</span>
                            </td>
                        </tr>
                    `;

                    const params = new URLSearchParams({
                        tipe: filterTipeEl.value,
                        tipe_turunan: filterTipeTurunanEl.value,
                        tanggal: filterTanggalEl.value
                    });

                    fetch(`{{ route('hr.DaftarTugas.get') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('HTTP status ' + response.status);
                        return response.json();
                    })
                    .then(r => {
                        tbodyEl.innerHTML = '';

                        if (!r.data || !r.data.length) {
                            tbodyEl.innerHTML = `
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center gap-3">
                                            <div class="bg-light rounded-circle p-4">
                                                <i class="bx bx-clipboard text-muted" style="font-size:3rem"></i>
                                            </div>
                                            <h5 class="text-muted mb-1">Belum ada Tugas Aktif</h5>
                                            <p class="text-muted small mb-0">Tidak ada tugas yang ditemukan pada tanggal/filter ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        let rowsHtml = '';
                        r.data.forEach(function(it) {
                            const relKat = it.kategori_daftar_tugas || it.kategoriDaftarTugas || {};
                            const relKar = it.karyawan || {};

                            const kat = relKat.judul_kategori || 'Tanpa Kategori';
                            const tipe = relKat.Tipe || relKat.tipe || '-';
                            const turunan = relKat.tipe_turunan || '-';
                            const karyawan = relKar.nama_lengkap || '-';
                            const dl = it.Deadline_Date || it.deadline_date || '-';
                            const isDone = it.status == 1;
                            const done = isDone ? 'text-decoration-line-through text-muted opacity-50' : '';

                            const statusBadge = isDone
                                ? `<span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Selesai</span>`
                                : `<span class="badge bg-warning text-dark"><i class="bx bx-time-five me-1"></i>Pending</span>`;

                            const buktiData = parseBukti(it.bukti);
                            const buktiStatus = getBuktiStatus(it.bukti);
                            const buktiDataStr = encodeURIComponent(JSON.stringify(buktiData));
                            const safeJudul = kat.replace(/"/g, '&quot;');

                            const buktiView = buktiStatus.canView
                                ? `<button type="button" class="btn btn-sm btn-outline-primary btn-viewBukti" data-bukti="${buktiDataStr}" data-judul="${safeJudul}">
                                        <i class="bx bx-show"></i> Lihat (${buktiStatus.text})
                                   </button>`
                                : `<span class="bukti-badge ${buktiStatus.class}">${buktiStatus.text}</span>`;

                            rowsHtml += `
                                <tr class="${buktiStatus.rowClass} ${isDone ? 'bg-light' : ''}">
                                    <td class="ps-4">${statusBadge}</td>
                                    <td class="task-text ${done} fw-medium">${kat}</td>
                                    <td class="task-text ${done}"><span class="badge bg-secondary">${tipe}</span></td>
                                    <td class="task-text ${done}"><span class="badge bg-info text-dark">${turunan}</span></td>
                                    <td class="task-text ${done} small fw-semibold">${karyawan}</td>
                                    <td class="task-text ${done} small">${dl}</td>
                                    <td class="text-center">${buktiView}</td>
                                </tr>
                            `;
                        });

                        tbodyEl.innerHTML = rowsHtml;
                    })
                    .catch(err => {
                        console.error('Gagal memuat tugas:', err);
                        tbodyEl.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center py-4 text-danger">
                                    Gagal memuat data tugas. Silakan periksa Console (F12).
                                </td>
                            </tr>
                        `;
                    });
                }

                function loadChartData() {
                    const params = new URLSearchParams({
                        period: chartPeriodEl.value,
                        karyawan: chartKaryawanEl.value,
                        start_date: chartStartDateEl.value,
                        end_date: chartEndDateEl.value
                    });

                    fetch(`{{ route('hr.DaftarTugas.chartData') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('HTTP status ' + response.status);
                        return response.json();
                    })
                    .then(r => {
                        if (r && r.labels) {
                            renderChart(r.labels, r.dataSelesai || [], r.dataPending || []);
                        }
                    })
                    .catch(err => console.error('Gagal memuat grafik:', err));
                }

                function renderChart(labels, dataSelesai, dataPending) {
                    const ctx = document.getElementById('taskChart');
                    if (!ctx || typeof Chart === 'undefined') return;
                    const context = ctx.getContext('2d');
                    if (taskChart) taskChart.destroy();

                    taskChart = new Chart(context, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Selesai',
                                    data: dataSelesai,
                                    backgroundColor: 'rgba(13, 189, 115, 0.7)',
                                    borderColor: 'rgba(13, 189, 115, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Pending',
                                    data: dataPending,
                                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                                    borderColor: 'rgba(255, 193, 7, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    stacked: true,
                                    title: { display: true, text: 'Periode' }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    title: { display: true, text: 'Jumlah Tugas' }
                                }
                            },
                            plugins: {
                                legend: { position: 'top' },
                                tooltip: { mode: 'index', intersect: false }
                            }
                        }
                    });
                }

                // Tampilkan Foto Before & After beserta Waktu Mulai & Waktu Selesai di Modal
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.btn-viewBukti');
                    if (!btn) return;

                    const buktiRaw = btn.getAttribute('data-bukti');
                    const judul = btn.getAttribute('data-judul');
                    document.getElementById('previewModalTitle').textContent = judul;
                    const body = document.getElementById('previewModalBody');

                    const modalEl = document.getElementById('modalPreviewBukti');
                    if (typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }

                    try {
                        const data = JSON.parse(decodeURIComponent(buktiRaw));
                        const beforeUrl = data.before ? `/storage/${data.before}` : null;
                        const afterUrl = data.after ? `/storage/${data.after}` : null;
                        const waktuMulai = formatWaktu(data.waktu_mulai);
                        const waktuSelesai = formatWaktu(data.waktu_selesai);

                        let html = '<div class="photo-comparison">';

                        // Kolom Foto Before & Waktu Mulai
                        html += `
                            <div class="photo-box">
                                <div class="label text-primary">Before (Sebelum)</div>
                                <div class="time-badge">
                                    <i class="bx bx-time-five text-primary"></i>
                                    <span>Waktu Mulai: <strong>${waktuMulai}</strong></span>
                                </div>
                                <div>
                        `;
                        if (beforeUrl) {
                            const ext = beforeUrl.split('.').pop().toLowerCase();
                            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                                html += `<img src="${beforeUrl}" class="img-fluid rounded shadow-sm bukti-preview" alt="Before">`;
                            } else {
                                html += `<div class="alert alert-secondary small mb-0">File: ${beforeUrl.split('/').pop()}</div>`;
                            }
                        } else {
                            html += `<div class="alert alert-warning small mb-0">Foto Before tidak tersedia</div>`;
                        }
                        html += `</div></div>`;

                        // Kolom Foto After & Waktu Selesai
                        html += `
                            <div class="photo-box">
                                <div class="label text-success">After (Sesudah)</div>
                                <div class="time-badge">
                                    <i class="bx bx-check-double text-success"></i>
                                    <span>Waktu Selesai: <strong>${waktuSelesai}</strong></span>
                                </div>
                                <div>
                        `;
                        if (afterUrl) {
                            const ext = afterUrl.split('.').pop().toLowerCase();
                            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                                html += `<img src="${afterUrl}" class="img-fluid rounded shadow-sm bukti-preview" alt="After">`;
                            } else {
                                html += `<div class="alert alert-secondary small mb-0">File: ${afterUrl.split('/').pop()}</div>`;
                            }
                        } else {
                            html += `<div class="alert alert-warning small mb-0">Foto After tidak tersedia</div>`;
                        }
                        html += `</div></div>`;

                        html += `</div>`;
                        body.innerHTML = html;
                    } catch (err) {
                        body.innerHTML = '<div class="alert alert-danger">Gagal memuat bukti. Silakan coba lagi.</div>';
                    }
                });

                filterTipeEl.addEventListener('change', () => { updateTitle(); loadData(); });
                filterTipeTurunanEl.addEventListener('change', () => { updateTitle(); loadData(); });
                filterTanggalEl.addEventListener('change', () => { updateTitle(); loadData(); });

                btnResetFilterEl.addEventListener('click', function() {
                    filterTipeEl.value = 'all';
                    filterTipeTurunanEl.value = 'all';
                    filterTanggalEl.value = today;
                    updateTitle();
                    loadData();
                });

                btnLoadChartEl.addEventListener('click', loadChartData);
                chartPeriodEl.addEventListener('change', loadChartData);
                chartKaryawanEl.addEventListener('change', loadChartData);
                chartStartDateEl.addEventListener('change', loadChartData);
                chartEndDateEl.addEventListener('change', loadChartData);

                updateTitle();
                loadData();
                loadChartData();
            });
        </script>
    @endif
@endsection
