@extends('layouts_office.app')
@section('office_contents')

<div class="container-fluid py-3">
  <h5 class="mb-3">Rekap Laporan Exam</h5>

  {{-- Filter --}}
  <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
    <select id="sel-tahun" class="form-select form-select-sm" style="width:110px;">
      <option value="">Semua tahun</option>
      @for($y = date('Y'); $y >= 2022; $y--)
        <option value="{{ $y }}">{{ $y }}</option>
      @endfor
    </select>
    <select id="sel-filter-type" class="form-select form-select-sm" style="width:130px;">
      <option value="">Semua periode</option>
      <option value="triwulan">Triwulan</option>
      <option value="bulan">Bulan</option>
    </select>
    <select id="sel-sub" class="form-select form-select-sm" style="width:130px; display:none;"></select>
    <button class="btn btn-sm btn-primary" onclick="loadData()">Tampilkan</button>
  </div>

  {{-- Cards --}}
  <div class="row g-2 mb-4">
      <div class="col">
          <div class="card border-0 p-3 h-100">
              <div class="text-muted small">Total exam</div>
              <div class="fs-4 fw-medium" id="v-exam">—</div>
          </div>
      </div>

      <div class="col">
          <div class="card border-0 p-3 h-100">
              <div class="text-muted small">Total peserta</div>
              <div class="fs-4 fw-medium" id="v-peserta">—</div>
          </div>
      </div>

      <div class="col">
          <div class="card border-0 p-3 h-100">
              <div class="text-muted small">Peserta lulus</div>
              <div class="fs-4 fw-medium text-success" id="v-lulus">—</div>
          </div>
      </div>

      <div class="col">
          <div class="card border-0 p-3 h-100">
              <div class="text-muted small">Tidak lulus</div>
              <div class="fs-4 fw-medium text-danger" id="v-tidak">—</div>
          </div>
      </div>

      <div class="col">
          <div class="card border-0 p-3 h-100">
              <div class="text-muted small">Tidak Exam</div>
              <div class="fs-4 fw-medium text-danger" id="v-tidak-exam">—</div>
          </div>
      </div>
  </div>

  {{-- Tabs + Chart --}}
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" onclick="switchTab(this,'materi_exam')" href="#">Per materi</a></li>
    <li class="nav-item"><a class="nav-link" onclick="switchTab(this,'instansi')" href="#">Per instansi</a></li>
    <li class="nav-item"><a class="nav-link" onclick="switchTab(this,'instruktur')" href="#">Per instruktur</a></li>
    <li class="nav-item"><a class="nav-link" onclick="switchTab(this,'kategori')" href="#">Per kategori</a></li>
  </ul>

  {{-- Legend + info jumlah item --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div id="legend-wrap" class="d-flex flex-wrap gap-2"></div>
    <div id="chart-info" class="text-muted" style="font-size:12px; display:none;"></div>
  </div>

  {{-- Chart outer: fixed height + scroll --}}
  <div id="chart-outer" style="
    display:none;
    position:relative;
    width:100%;
    max-height:520px;
    overflow-y:auto;
    overflow-x:hidden;
    border:1px solid #e9e9e7;
    border-radius:8px;
    background:#fff;
  ">
    {{-- Sumbu X tetap di atas (sticky) --}}
    <div id="x-axis-sticky" style="
      position:sticky;
      top:0;
      z-index:10;
      background:#fff;
      border-bottom:1px solid #e9e9e7;
      padding:0;
      display:none;
    ">
      <canvas id="xAxisChart" style="display:block;"></canvas>
    </div>

    {{-- Canvas chart utama --}}
    <div id="chart-wrap" style="position:relative; width:100%;">
      <canvas id="mainChart"></canvas>
    </div>
  </div>

  {{-- Hint scroll --}}
  <div id="scroll-hint" class="text-muted text-center mt-1" style="font-size:11px; display:none;">
    ↕ Scroll untuk melihat semua data
  </div>

  {{-- Empty state --}}
  <div id="empty" style="display:none;">
    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="mb-3 opacity-50">
        <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
      </svg>
      <div style="font-size:14px; font-weight:500;">Tidak ada data tersedia</div>
      <div style="font-size:13px; margin-top:4px;">Untuk periode ini tidak ada data tersedia.</div>
    </div>
  </div>

  {{-- Tooltip custom --}}
  <div id="chart-tooltip" style="
    position:fixed; display:none; pointer-events:none;
    background:#fff; border:1px solid #e0e0e0; border-radius:8px;
    padding:10px 14px; box-shadow:0 4px 16px rgba(0,0,0,0.10);
    font-size:13px; color:#333; min-width:180px; z-index:9999;
  "></div>

  {{-- Modal detail kategori --}}
  <div class="modal fade" id="modalKategoriDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title mb-0" id="modalKategoriTitle">Detail Kategori</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-3">
              <thead>
                <tr>
                  <th style="width:130px;">Pengajuan</th>
                  <th style="width:130px;">Periode</th>
                  <th>Materi</th>
                  <th>Instansi</th>
                  <th>Instruktur</th>
                </tr>
              </thead>
              <tbody id="modalKategoriBody"></tbody>
            </table>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted" style="font-size:12px;" id="modalKategoriInfo"></div>
            <nav>
              <ul class="pagination pagination-sm mb-0" id="modalKategoriPagination"></ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const URL_REKAP  = "{{ route('office.exam.rekap.json') }}";
const ROW_H      = 44;    // tinggi per baris chart
const MAX_VISIBLE = 10;   // batas sebelum scroll aktif
const MAX_H      = MAX_VISIBLE * ROW_H + 60; // ~520px

let chartInst        = null;
let currentTab        = 'materi_exam';
let lastData          = null;
let mouseX            = 0;
let mouseY            = 0;
let currentActiveSeries = null; // series yg sedang dipakai chart aktif (SERIES atau SIMPLE_SERIES)

// Series lengkap: dipakai untuk tab yang datanya berupa objek
// {total_exam, total_peserta, total_lulus, total_tidak_lulus, total_tidak_exam}
const SERIES = [
  { label: 'Jumlah exam',  key: 'total_exam',        color: '#2a78d6' },
  { label: 'Peserta',      key: 'total_peserta',      color: '#1baf7a' },
  { label: 'Lulus',        key: 'total_lulus',        color: '#008300' },
  { label: 'Tidak lulus',  key: 'total_tidak_lulus',  color: '#e34948' },
  { label: 'Tidak Exam',   key: 'total_tidak_exam',   color: '#ffc107' },
];

// Series simpel: dipakai untuk tab yang datanya cuma angka polos (mis. kategori)
const SIMPLE_SERIES = [
  { label: 'Jumlah exam', key: null, color: '#2a78d6' },
];

// ── Data detail per kategori (utk modal) ───────────────────────
let kategoriDetailData = {};
let modalRows          = [];
let modalCurrentPage   = 1;
const MODAL_PAGE_SIZE  = 10;
let bsModalKategori     = null;

// ── Track mouse ───────────────────────────────────────────────
document.addEventListener('mousemove', function (e) {
  mouseX = e.clientX;
  mouseY = e.clientY;

  const tooltip = document.getElementById('chart-tooltip');
  const canvas  = document.getElementById('mainChart');
  if (!canvas || !tooltip) return;
  const rect = canvas.getBoundingClientRect();
  if (e.clientX < rect.left || e.clientX > rect.right ||
      e.clientY < rect.top  || e.clientY > rect.bottom) {
    tooltip.style.display = 'none';
  }
});

// ── Filter type change ────────────────────────────────────────
document.getElementById('sel-filter-type').addEventListener('change', function () {
  const sub = document.getElementById('sel-sub');
  sub.innerHTML = '<option value="">Pilih...</option>';
  if (this.value === 'triwulan') {
    ['Triwulan 1','Triwulan 2','Triwulan 3','Triwulan 4']
      .forEach((l, i) => { sub.innerHTML += `<option value="${i+1}">${l}</option>`; });
    sub.style.display = '';
  } else if (this.value === 'bulan') {
    ['Januari','Februari','Maret','April','Mei','Juni',
     'Juli','Agustus','September','Oktober','November','Desember']
      .forEach((l, i) => { sub.innerHTML += `<option value="${i+1}">${l}</option>`; });
    sub.style.display = '';
  } else {
    sub.style.display = 'none';
  }
});

// ── Load data ─────────────────────────────────────────────────
function loadData() {
  const tahun  = document.getElementById('sel-tahun').value;
  const type   = document.getElementById('sel-filter-type').value;
  const subVal = document.getElementById('sel-sub').value;
  const params = new URLSearchParams();

  if (tahun)                          params.append('tahun',    tahun);
  if (type === 'triwulan' && subVal)  params.append('triwulan', subVal);
  if (type === 'bulan'    && subVal)  params.append('bulan',    subVal);

  fetch(`${URL_REKAP}?${params}`)
    .then(r => r.json())
    .then(data => {
      lastData            = data;
      kategoriDetailData  = data.kategori_data || {};
      document.getElementById('v-exam').textContent       = data.total_exam;
      document.getElementById('v-peserta').textContent    = data.total_peserta;
      document.getElementById('v-lulus').textContent      = data.total_lulus;
      document.getElementById('v-tidak').textContent      = data.total_tidak_lulus;
      document.getElementById('v-tidak-exam').textContent = data.total_tidak_exam;
      renderChart(currentTab, data);
    });
}

// ── Switch tab ────────────────────────────────────────────────
function switchTab(el, tab) {
  document.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  currentTab = tab;
  if (lastData) renderChart(tab, lastData);
}

// ── Posisi tooltip ─────────────────────────────────────────────
function positionTooltip(tooltipEl) {
  const TW = tooltipEl.offsetWidth  || 200;
  const TH = tooltipEl.offsetHeight || 120;
  const VW = window.innerWidth;
  const VH = window.innerHeight;
  const OFFSET = 14;
  let x = mouseX + OFFSET;
  let y = mouseY + OFFSET;
  if (x + TW > VW - 8) x = mouseX - TW - OFFSET;
  if (y + TH > VH - 8) y = mouseY - TH - OFFSET;
  tooltipEl.style.left = x + 'px';
  tooltipEl.style.top  = y + 'px';
}

// ── Render chart ──────────────────────────────────────────────
function renderChart(tab, data) {
  const src    = data[tab] ?? {};
  const labels = Object.keys(src);

  const chartOuter = document.getElementById('chart-outer');
  const chartWrap  = document.getElementById('chart-wrap');
  const emptyEl    = document.getElementById('empty');
  const legendEl   = document.getElementById('legend-wrap');
  const infoEl     = document.getElementById('chart-info');
  const hintEl     = document.getElementById('scroll-hint');
  const tooltip    = document.getElementById('chart-tooltip');

  if (!labels.length) {
    chartOuter.style.display = 'none';
    hintEl.style.display     = 'none';
    infoEl.style.display     = 'none';
    legendEl.innerHTML       = '';
    emptyEl.style.display    = '';
    tooltip.style.display    = 'none';
    if (chartInst) { chartInst.destroy(); chartInst = null; }
    return;
  }

  emptyEl.style.display = 'none';

  // Deteksi bentuk data: kalau value-nya bukan objek (mis. tab "kategori"
  // yang cuma berisi angka per label), pakai SIMPLE_SERIES (1 series saja).
  const isSimple = typeof src[labels[0]] !== 'object' || src[labels[0]] === null;
  const activeSeries = isSimple ? SIMPLE_SERIES : SERIES;
  currentActiveSeries = activeSeries; // simpan referensi utk dipakai toggleSeries()

  const needsScroll = labels.length > MAX_VISIBLE;
  const fullH       = labels.length * ROW_H + 60;

  // Outer container: fixed max-height kalau data banyak
  chartOuter.style.display   = '';
  chartOuter.style.maxHeight = needsScroll ? MAX_H + 'px' : 'none';
  chartOuter.scrollTop       = 0;

  // Inner canvas height = full selalu (supaya tidak terpotong)
  chartWrap.style.height = fullH + 'px';

  // Hint scroll
  hintEl.style.display = needsScroll ? '' : 'none';

  // Info jumlah item
  infoEl.style.display   = '';
  infoEl.textContent     = `Menampilkan ${labels.length} item`;

  if (chartInst) { chartInst.destroy(); }

  chartInst = new Chart(document.getElementById('mainChart'), {
    type: 'bar',
    data: {
      labels,
      datasets: activeSeries.map(s => ({
        label:           s.label,
        data:            labels.map(k => isSimple ? src[k] : src[k][s.key]),
        backgroundColor: s.color,
        borderRadius:    4,
      })),
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      onClick: (evt, elements) => {
        if (currentTab !== 'kategori' || !elements.length) return;
        const idx   = elements[0].index;
        const label = chartInst.data.labels[idx];
        openKategoriModal(label);
      },
      onHover: (evt, elements) => {
        if (evt.native?.target) {
          evt.native.target.style.cursor = (currentTab === 'kategori' && elements.length) ? 'pointer' : 'default';
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: false,
          external: function (ctx) {
            const { tooltip: t } = ctx;
            if (t.opacity === 0) { tooltip.style.display = 'none'; return; }

            const label   = t.title?.[0] ?? '';
            const rowData = src[label];

            tooltip.innerHTML =
              `<div style="font-weight:500;font-size:13px;margin-bottom:8px;color:#111;border-bottom:1px solid #eee;padding-bottom:6px;">${label}</div>` +
              activeSeries.map(s => {
                const val = isSimple ? (rowData ?? 0) : (rowData?.[s.key] ?? 0);
                return `<div style="display:flex;justify-content:space-between;gap:24px;padding:2px 0;">
                  <span style="display:flex;align-items:center;gap:6px;">
                    <span style="width:10px;height:10px;border-radius:2px;background:${s.color};flex-shrink:0;display:inline-block;"></span>
                    <span style="color:#555;">${s.label}</span>
                  </span>
                  <span style="font-weight:500;color:#111;">${val}</span>
                </div>`;
              }).join('') +
              (currentTab === 'kategori'
                ? `<div style="margin-top:8px;padding-top:6px;border-top:1px dashed #eee;font-size:11px;color:#999;">Klik bar untuk lihat detail</div>`
                : '');

            tooltip.style.display = 'block';
            requestAnimationFrame(() => positionTooltip(tooltip));
          }
        },
      },
      scales: {
        x: {
          position: 'top',  // sumbu X di atas supaya selalu kelihatan saat scroll
          grid:   { color: 'rgba(0,0,0,0.06)' },
          ticks:  { color: '#898781' },
          border: { display: false },
        },
        y: {
          grid:   { display: false },
          ticks:  { color: '#52514e' },
          border: { display: false },
        },
      },
    },
  });

  // ── Legend clickable ──────────────────────────────────────
  legendEl.innerHTML = activeSeries.map((s, i) =>
    `<span class="legend-item" data-idx="${i}" onclick="toggleSeries(this,${i})"
      style="display:flex;align-items:center;gap:6px;cursor:pointer;
             background:#f5f5f5;border-radius:6px;padding:4px 10px;
             font-size:12px;color:#444;user-select:none;">
      <span class="legend-dot" style="width:10px;height:10px;border-radius:2px;background:${s.color};flex-shrink:0;display:inline-block;"></span>
      ${s.label}
    </span>`
  ).join('');
}

// ── Toggle series ─────────────────────────────────────────────
function toggleSeries(el, idx) {
  if (!chartInst) return;
  const meta = chartInst.getDatasetMeta(idx);
  meta.hidden = !meta.hidden;
  chartInst.update();
  const dot = el.querySelector('.legend-dot');
  const series = currentActiveSeries || SERIES;
  if (meta.hidden) {
    el.style.opacity     = '0.4';
    dot.style.background = '#ccc';
  } else {
    el.style.opacity     = '1';
    dot.style.background = series[idx].color;
  }
}

// ── Modal detail kategori ───────────────────────────────────────
function openKategoriModal(label) {
  if (!bsModalKategori) {
    bsModalKategori = new bootstrap.Modal(document.getElementById('modalKategoriDetail'));
  }
  modalRows        = kategoriDetailData[label] || [];
  modalCurrentPage = 1;
  document.getElementById('modalKategoriTitle').textContent = `Detail Kategori - ${label}`;
  renderModalPage();
  bsModalKategori.show();
}

function renderModalPage() {
  const tbody   = document.getElementById('modalKategoriBody');
  const infoEl  = document.getElementById('modalKategoriInfo');
  const start   = (modalCurrentPage - 1) * MODAL_PAGE_SIZE;
  const pageRows = modalRows.slice(start, start + MODAL_PAGE_SIZE);

  tbody.innerHTML = pageRows.length
    ? pageRows.map(r => `
        <tr>
          <td>${r.tanggal_pengajuan ?? '-'}</td>
          <td>${r.tanggal_mulai && r.tanggal_selesai ? `${r.tanggal_mulai}/${r.tanggal_selesai}` : '-' }</td>
          <td>${r.materi ?? '-'}</td>
          <td>${r.perusahaan ?? '-'}</td>
          <td>${r.instruktur ?? '-'}</td>
        </tr>`).join('')
    : `<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data</td></tr>`;

  infoEl.textContent = modalRows.length
    ? `Menampilkan ${start + 1}-${Math.min(start + MODAL_PAGE_SIZE, modalRows.length)} dari ${modalRows.length} data`
    : '';

  renderModalPagination();
}

function renderModalPagination() {
  const pagEl = document.getElementById('modalKategoriPagination');
  const totalPages = Math.ceil(modalRows.length / MODAL_PAGE_SIZE);
  if (totalPages <= 1) { pagEl.innerHTML = ''; return; }

  let html = `<li class="page-item ${modalCurrentPage === 1 ? 'disabled' : ''}">
    <a class="page-link" href="#" onclick="gotoModalPage(${modalCurrentPage - 1});return false;">&laquo;</a></li>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<li class="page-item ${i === modalCurrentPage ? 'active' : ''}">
      <a class="page-link" href="#" onclick="gotoModalPage(${i});return false;">${i}</a></li>`;
  }
  html += `<li class="page-item ${modalCurrentPage === totalPages ? 'disabled' : ''}">
    <a class="page-link" href="#" onclick="gotoModalPage(${modalCurrentPage + 1});return false;">&raquo;</a></li>`;
  pagEl.innerHTML = html;
}

function gotoModalPage(page) {
  const totalPages = Math.ceil(modalRows.length / MODAL_PAGE_SIZE);
  if (page < 1 || page > totalPages) return;
  modalCurrentPage = page;
  renderModalPage();
}

loadData();
</script>
@endsection