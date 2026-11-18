@extends('layouts.app')

@section('content')
<div class="container-fluid" id="rekap-tiket">

    {{-- Filter global --}}
    <div class="card mb-3">
        <div class="card-body">
            <form id="filter-form" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="f-periode">Periode</label>
                    <select class="form-select" name="periode" id="f-periode">
                        <option value="harian">Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan" selected>Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                        <option value="custom">Rentang tanggal</option>
                    </select>
                </div>
                <div class="col-6 col-md-2" id="wrap-tanggal">
                    <label class="form-label mb-1" for="f-tanggal">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" id="f-tanggal" value="{{ now()->toDateString() }}">
                </div>
                <div class="col-6 col-md-2 d-none" id="wrap-tahun">
                    <label class="form-label mb-1" for="f-tahun">Tahun</label>
                    <select class="form-select" name="tahun" id="f-tahun">
                        @for ($y = now()->year; $y >= 2025; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-6 col-md-2 d-none" id="wrap-dari">
                    <label class="form-label mb-1" for="f-dari">Dari</label>
                    <input type="date" class="form-control" name="dari" id="f-dari" value="{{ now()->startOfMonth()->toDateString() }}">
                </div>
                <div class="col-6 col-md-2 d-none" id="wrap-sampai">
                    <label class="form-label mb-1" for="f-sampai">Sampai</label>
                    <input type="date" class="form-control" name="sampai" id="f-sampai" value="{{ now()->toDateString() }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="f-divisi">Divisi</label>
                    <select class="form-select" name="divisi" id="f-divisi"><option value="">Semua</option></select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="f-kategori">Kategori</label>
                    <select class="form-select" name="kategori" id="f-kategori"><option value="">Semua</option></select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="f-pic">PIC</label>
                    <select class="form-select" name="pic" id="f-pic"><option value="">Semua</option></select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <button type="button" class="btn btn-outline-secondary" id="btn-reset">Reset</button>
                </div>
            </form>
            <div class="text-muted small mt-2" id="range-label"></div>
        </div>
    </div>

    <div id="rekap-body">

        {{-- Status & tiket belum selesai --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-4">
                <div class="card h-100"><div class="card-body">
                    <h6 class="card-title">Komposisi status</h6>
                    <div style="height:260px"><canvas id="c-status"></canvas></div>
                </div></div>
            </div>
            <div class="col-lg-8">
                <div class="card h-100"><div class="card-body">
                    <h6 class="card-title">Tiket belum selesai (terlama dulu)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Tiket</th><th>Kategori</th><th>PIC</th><th>Status</th><th>Umur</th><th>Alasan / keterangan</th></tr></thead>
                            <tbody id="t-open"></tbody>
                        </table>
                    </div>
                </div></div>
            </div>
        </div>

        {{-- Tren waktu --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="card-title" id="daily-title">Tren waktu response dan resolusi per hari (menit)</h6>
            <div style="height:280px"><canvas id="c-daily"></canvas></div>
        </div></div>

        {{-- Volume bulanan & kategori --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-body">
                    <h6 class="card-title" id="trend-title">Volume 6 bulan terakhir</h6>
                    <div style="height:280px"><canvas id="c-trend"></canvas></div>
                </div></div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-body">
                    <h6 class="card-title">Tiket per kategori</h6>
                    <div style="height:280px"><canvas id="c-kategori"></canvas></div>
                </div></div>
            </div>
        </div>

        {{-- PIC --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="card-title">Beban kerja per PIC</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>PIC</th><th class="text-end">Total</th><th class="text-end">Selesai</th><th class="text-end">Rata-rata resolusi</th></tr></thead>
                    <tbody id="t-pic"></tbody>
                </table>
            </div>
        </div></div>

        {{-- Log insiden (khusus mingguan) --}}
        <div class="card mb-3 d-none" id="card-incidents">
            <div class="card-body">
                <h6 class="card-title">Log masalah teknis minggu ini</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-top mb-0">
                        <thead><tr><th>Tiket</th><th>Tanggal</th><th>Tingkat</th><th>Masalah</th><th>Tindakan</th><th>Keterangan</th><th>Status</th></tr></thead>
                        <tbody id="t-incidents"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    // Sesuaikan nama route dengan yang kamu daftarkan untuk rekapData
    const DATA_URL = "{{ route('ticket.rekap.data') }}";

    const $ = (id) => document.getElementById(id);
    const charts = {};
    let filtersFilled = false;

    const esc = (s) => String(s === null || s === undefined ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    const fmtMin = (m) => {
        if (m === null || m === undefined) return '-';
        m = Math.round(m);
        if (m < 60) return m + ' mnt';
        const h = Math.floor(m / 60);
        if (h < 24) return h + ' j ' + (m % 60) + ' mnt';
        return Math.floor(h / 24) + ' hr ' + (h % 24) + ' j';
    };
    const emptyRow = (cols, text) => '<tr><td colspan="' + cols + '" class="text-muted text-center">' + text + '</td></tr>';

    function chart(id, config) {
        if (charts[id]) charts[id].destroy();
        config.options = Object.assign({ responsive: true, maintainAspectRatio: false }, config.options || {});
        charts[id] = new Chart($(id), config);
    }

    function togglePeriode() {
        const p = $('f-periode').value;
        $('wrap-tanggal').classList.toggle('d-none', p === 'custom' || p === 'tahunan');
        $('wrap-tahun').classList.toggle('d-none', p !== 'tahunan');
        $('wrap-dari').classList.toggle('d-none', p !== 'custom');
        $('wrap-sampai').classList.toggle('d-none', p !== 'custom');
    }

    function fillFilters(f) {
        if (filtersFilled) return;
        [['divisi', f.divisi], ['kategori', f.kategori], ['pic', f.pic]].forEach(function (pair) {
            const sel = $('f-' + pair[0]);
            pair[1].forEach(function (v) {
                const o = document.createElement('option');
                o.value = v; o.textContent = v;
                sel.appendChild(o);
            });
        });
        filtersFilled = true;
    }

    function render(d) {
        fillFilters(d.filters);
        const yearly = d.range.periode === 'tahunan';
        $('range-label').textContent = 'Menampilkan ' + d.range.label + ' - ' + d.range.total + ' tiket';
        $('daily-title').textContent = 'Tren waktu response dan resolusi per ' + (yearly ? 'bulan' : 'hari') + ' (menit)';
        $('trend-title').textContent = yearly ? 'Volume per bulan' : 'Volume 6 bulan terakhir';

        // Status
        chart('c-status', {
            type: 'doughnut',
            data: { labels: d.status.map((s) => s.label), datasets: [{ data: d.status.map((s) => s.total) }] },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        // Tiket belum selesai
        $('t-open').innerHTML = d.open_tickets.length ? d.open_tickets.map((t) =>
            '<tr><td>' + esc(t.ticket_id) + '</td><td>' + esc(t.kategori) + '</td><td>' + esc(t.pic || '-') +
            '</td><td>' + esc(t.status || '-') + '</td><td>' + fmtMin(t.age_min) +
            '</td><td>' + esc(t.alasan || '-') + '</td></tr>').join('')
            : emptyRow(6, 'Tidak ada tiket yang belum selesai');

        // Tren waktu (key "YYYY-MM-DD" per hari, atau "YYYY-MM" per bulan)
        chart('c-daily', {
            type: 'line',
            data: {
                labels: d.daily.map((x) => x.date.length === 7
                    ? x.date.slice(5, 7) + '/' + x.date.slice(0, 4)
                    : x.date.slice(8, 10) + '/' + x.date.slice(5, 7)),
                datasets: [
                    { label: 'Response', data: d.daily.map((x) => x.avg_response), tension: 0.25, spanGaps: true },
                    { label: 'Resolusi', data: d.daily.map((x) => x.avg_resolution), tension: 0.25, spanGaps: true }
                ]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

        // Volume bulanan
        chart('c-trend', {
            type: 'bar',
            data: {
                labels: d.trend.map((x) => x.label),
                datasets: [
                    { label: 'Total', data: d.trend.map((x) => x.total) },
                    { label: 'Selesai', data: d.trend.map((x) => x.selesai) }
                ]
            },
            options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        // Kategori
        chart('c-kategori', {
            type: 'bar',
            data: {
                labels: d.per_kategori.map((x) => x.label),
                datasets: [{ label: 'Tiket', data: d.per_kategori.map((x) => x.total) }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        // PIC
        $('t-pic').innerHTML = d.per_pic.length ? d.per_pic.map((x) =>
            '<tr><td>' + esc(x.pic) + '</td><td class="text-end">' + x.total + '</td><td class="text-end">' + x.selesai +
            '</td><td class="text-end">' + fmtMin(x.avg_resolution) + '</td></tr>').join('')
            : emptyRow(4, 'Belum ada data');

        // Insiden (mingguan)
        const showInc = d.incidents !== null;
        $('card-incidents').classList.toggle('d-none', !showInc);
        if (showInc) {
            $('t-incidents').innerHTML = d.incidents.length ? d.incidents.map((t) =>
                '<tr><td>' + esc(t.ticket_id) + '</td><td>' + esc(t.tanggal) + '</td><td>' + esc(t.tingkat) +
                '</td><td style="white-space:pre-line">' + esc(t.masalah) + '</td><td>' + esc(t.tindakan || '-') +
                '</td><td>' + esc(t.keterangan || '-') + '</td><td>' + esc(t.status || '-') + '</td></tr>').join('')
                : emptyRow(7, 'Tidak ada insiden menonjol pada minggu ini');
        }
    }

    function load() {
        const params = new URLSearchParams();
        new FormData($('filter-form')).forEach(function (v, key) { if (v !== '') params.append(key, v); });

        $('rekap-body').style.opacity = '0.5';
        fetch(DATA_URL + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('Gagal memuat data (' + res.status + ')');
                return res.json();
            })
            .then(render)
            .catch(function (err) { alert(err.message); })
            .finally(function () { $('rekap-body').style.opacity = '1'; });
    }

    $('f-periode').addEventListener('change', togglePeriode);
    $('filter-form').addEventListener('submit', function (e) { e.preventDefault(); load(); });
    $('btn-reset').addEventListener('click', function () {
        $('filter-form').reset();
        togglePeriode();
        load();
    });

    togglePeriode();
    load();
})();
</script>
@endsection