@extends('layouts.app')

@section('content')
<style>
    /* Cegah chart melebihi card (terutama saat lebar layout berubah, mis. sidebar dibuka/tutup) */
    #rekap-tiket { min-width: 0; max-width: 100%; }
    #rekap-tiket [class*="col-"] { min-width: 0; }
    #rekap-body .card { min-width: 0; overflow: hidden; }
    #rekap-tiket .chart-box { position: relative; width: 100%; min-width: 0; overflow: hidden; }
    #rekap-tiket .chart-box canvas { display: block; max-width: 100%; }
</style>
<div class="container-fluid" id="rekap-tiket">

    {{-- Filter global --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                {{-- Pilih jenis periode --}}
                <div class="col-12 col-xl-auto">
                    <div class="form-label mb-1">Lihat data</div>
                    <div class="btn-group" role="group" aria-label="Jenis periode">
                        <input type="radio" class="btn-check" name="periode" id="p-harian" value="harian" autocomplete="off">
                        <label class="btn btn-outline-primary" for="p-harian">Harian</label>

                        <input type="radio" class="btn-check" name="periode" id="p-mingguan" value="mingguan" autocomplete="off">
                        <label class="btn btn-outline-primary" for="p-mingguan">Mingguan</label>

                        <input type="radio" class="btn-check" name="periode" id="p-bulanan" value="bulanan" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="p-bulanan">Bulanan</label>

                        <input type="radio" class="btn-check" name="periode" id="p-tahunan" value="tahunan" autocomplete="off">
                        <label class="btn btn-outline-primary" for="p-tahunan">Tahunan</label>

                        <input type="radio" class="btn-check" name="periode" id="p-custom" value="custom" autocomplete="off">
                        <label class="btn btn-outline-primary" for="p-custom">Rentang tanggal</label>
                    </div>
                </div>

                {{-- Harian: pilih tanggal --}}
                <div class="col-12 col-md-auto picker d-none" data-mode="harian">
                    <label class="form-label mb-1" for="f-hari">Tanggal</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" data-step="-1" aria-label="Hari sebelumnya">&lsaquo;</button>
                        <input type="date" class="form-control" id="f-hari">
                        <button type="button" class="btn btn-outline-secondary" data-step="1" aria-label="Hari berikutnya">&rsaquo;</button>
                    </div>
                </div>

                {{-- Mingguan: pilih minggu (Senin - Minggu) --}}
                <div class="col-12 col-md-auto picker d-none" data-mode="mingguan">
                    <label class="form-label mb-1" for="f-minggu">Minggu (Senin - Minggu)</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" data-step="-1" aria-label="Minggu sebelumnya">&lsaquo;</button>
                        <select class="form-select" id="f-minggu"></select>
                        <button type="button" class="btn btn-outline-secondary" data-step="1" aria-label="Minggu berikutnya">&rsaquo;</button>
                    </div>
                </div>

                {{-- Bulanan: pilih bulan dan tahun --}}
                <div class="col-12 col-md-auto picker" data-mode="bulanan">
                    <label class="form-label mb-1" for="f-bulan">Bulan</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" data-step="-1" aria-label="Bulan sebelumnya">&lsaquo;</button>
                        <select class="form-select" id="f-bulan"></select>
                        <select class="form-select" id="f-bln-tahun" aria-label="Tahun"></select>
                        <button type="button" class="btn btn-outline-secondary" data-step="1" aria-label="Bulan berikutnya">&rsaquo;</button>
                    </div>
                </div>

                {{-- Tahunan: pilih tahun --}}
                <div class="col-12 col-md-auto picker d-none" data-mode="tahunan">
                    <label class="form-label mb-1" for="f-thn">Tahun</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" data-step="-1" aria-label="Tahun sebelumnya">&lsaquo;</button>
                        <select class="form-select" id="f-thn"></select>
                        <button type="button" class="btn btn-outline-secondary" data-step="1" aria-label="Tahun berikutnya">&rsaquo;</button>
                    </div>
                </div>

                {{-- Rentang tanggal --}}
                <div class="col-6 col-md-auto picker d-none" data-mode="custom">
                    <label class="form-label mb-1" for="f-dari">Dari</label>
                    <input type="date" class="form-control" id="f-dari">
                </div>
                <div class="col-6 col-md-auto picker d-none" data-mode="custom">
                    <label class="form-label mb-1" for="f-sampai">Sampai</label>
                    <input type="date" class="form-control" id="f-sampai">
                </div>

                {{-- Filter divisi, kategori, PIC --}}
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="f-divisi">Divisi</label>
                    <select class="form-select" id="f-divisi"><option value="">Semua</option></select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="f-kategori">Kategori</label>
                    <select class="form-select" id="f-kategori"><option value="">Semua</option></select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="f-pic">PIC</label>
                    <select class="form-select" id="f-pic"><option value="">Semua</option></select>
                </div>
                <div class="col-6 col-md-auto">
                    <button type="button" class="btn btn-outline-secondary" id="btn-reset">Reset</button>
                </div>
            </div>
            <div class="text-muted small mt-2" id="range-label"></div>
        </div>
    </div>

    <div id="rekap-body">

        {{-- Status & tiket belum selesai --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-4">
                <div class="card h-100"><div class="card-body">
                    <h6 class="card-title">Komposisi status</h6>
                    <div class="chart-box" style="height:260px"><canvas id="c-status"></canvas></div>
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
            <div class="chart-box" style="height:280px"><canvas id="c-daily"></canvas></div>
        </div></div>

        {{-- Volume bulanan & kategori --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-body">
                    <h6 class="card-title" id="trend-title">Volume 6 bulan terakhir</h6>
                    <div class="chart-box" style="height:280px"><canvas id="c-trend"></canvas></div>
                </div></div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100"><div class="card-body">
                    <h6 class="card-title">Tiket per kategori</h6>
                    <div class="chart-box" style="height:280px"><canvas id="c-kategori"></canvas></div>
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
    const DATA_URL = "{{ route('tickets.rekap.data') }}";
    const FIRST_YEAR = 2025; // tahun pertama data tiket

    const $ = (id) => document.getElementById(id);
    const charts = {};
    let filtersFilled = false;

    const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'];
    const now = new Date();
    const thisYear = now.getFullYear();
    const pad = (n) => String(n).padStart(2, '0');
    const ymd = (d) => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    const periode = () => document.querySelector('input[name="periode"]:checked').value;

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

    // ===== Pilihan periode =====
    function buildPickers() {
        [$('f-bln-tahun'), $('f-thn')].forEach(function (sel) {
            for (let y = thisYear; y >= FIRST_YEAR; y--) sel.add(new Option(y, y));
        });
        MONTHS.forEach(function (m, i) { $('f-bulan').add(new Option(m, i + 1)); });

        // 52 minggu terakhir, minggu ini paling atas
        const monday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        monday.setDate(monday.getDate() - ((monday.getDay() + 6) % 7));
        const short = (d) => d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        for (let i = 0; i < 52; i++) {
            const s = new Date(monday); s.setDate(s.getDate() - 7 * i);
            const e = new Date(s); e.setDate(e.getDate() + 6);
            $('f-minggu').add(new Option(short(s) + ' - ' + short(e) + ' ' + e.getFullYear() + (i === 0 ? ' (minggu ini)' : ''), ymd(s)));
        }
    }

    function showPicker() {
        const p = periode();
        document.querySelectorAll('.picker').forEach(function (el) {
            el.classList.toggle('d-none', el.dataset.mode !== p);
        });
    }

    function setDefaults() {
        $('p-bulanan').checked = true;
        $('f-hari').value = ymd(now);
        $('f-minggu').selectedIndex = 0;
        $('f-bulan').value = now.getMonth() + 1;
        $('f-bln-tahun').value = thisYear;
        $('f-thn').value = thisYear;
        $('f-dari').value = ymd(new Date(now.getFullYear(), now.getMonth(), 1));
        $('f-sampai').value = ymd(now);
        ['f-divisi', 'f-kategori', 'f-pic'].forEach(function (id) { $(id).value = ''; });
        showPicker();
    }

    // Tombol panah: geser periode satu langkah
    function step(dir) {
        const p = periode();
        if (p === 'harian') {
            if (!$('f-hari').value) return;
            const d = new Date($('f-hari').value + 'T00:00:00');
            d.setDate(d.getDate() + dir);
            $('f-hari').value = ymd(d);
        } else if (p === 'mingguan') {
            const s = $('f-minggu'), i = s.selectedIndex - dir; // daftar: terbaru di atas
            if (i < 0 || i >= s.options.length) return;
            s.selectedIndex = i;
        } else if (p === 'bulanan') {
            let m = parseInt($('f-bulan').value, 10) + dir, y = parseInt($('f-bln-tahun').value, 10);
            if (m < 1) { m = 12; y--; }
            if (m > 12) { m = 1; y++; }
            if (!Array.prototype.some.call($('f-bln-tahun').options, (o) => parseInt(o.value, 10) === y)) return;
            $('f-bulan').value = m;
            $('f-bln-tahun').value = y;
        } else if (p === 'tahunan') {
            const s = $('f-thn'), i = s.selectedIndex - dir; // daftar: terbaru di atas
            if (i < 0 || i >= s.options.length) return;
            s.selectedIndex = i;
        } else {
            return;
        }
        load();
    }

    function buildParams() {
        const p = periode();
        const q = new URLSearchParams({ periode: p });
        if (p === 'harian' && $('f-hari').value) q.set('tanggal', $('f-hari').value);
        if (p === 'mingguan') q.set('tanggal', $('f-minggu').value);
        if (p === 'bulanan') q.set('tanggal', $('f-bln-tahun').value + '-' + pad($('f-bulan').value) + '-01');
        if (p === 'tahunan') q.set('tahun', $('f-thn').value);
        if (p === 'custom') {
            if ($('f-dari').value) q.set('dari', $('f-dari').value);
            if ($('f-sampai').value) q.set('sampai', $('f-sampai').value);
        }
        ['divisi', 'kategori', 'pic'].forEach(function (k) {
            if ($('f-' + k).value) q.set(k, $('f-' + k).value);
        });
        return q;
    }

    function fillFilters(f) {
        if (filtersFilled) return;
        [['divisi', f.divisi], ['kategori', f.kategori], ['pic', f.pic]].forEach(function (pair) {
            const sel = $('f-' + pair[0]);
            pair[1].forEach(function (v) { sel.add(new Option(v, v)); });
        });
        filtersFilled = true;
    }

    // ===== Render =====
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
        if (periode() === 'custom' && $('f-dari').value && $('f-sampai').value && $('f-dari').value > $('f-sampai').value) {
            alert('Tanggal "Dari" tidak boleh lebih besar dari "Sampai".');
            return;
        }

        $('rekap-body').style.opacity = '0.5';
        fetch(DATA_URL + '?' + buildParams().toString(), {
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

    // ===== Event: semua perubahan langsung memuat ulang data =====
    document.querySelectorAll('input[name="periode"]').forEach(function (r) {
        r.addEventListener('change', function () { showPicker(); load(); });
    });
    ['f-hari', 'f-minggu', 'f-bulan', 'f-bln-tahun', 'f-thn', 'f-dari', 'f-sampai', 'f-divisi', 'f-kategori', 'f-pic']
        .forEach(function (id) { $(id).addEventListener('change', load); });
    document.querySelectorAll('[data-step]').forEach(function (b) {
        b.addEventListener('click', function () { step(parseInt(b.dataset.step, 10)); });
    });
    $('btn-reset').addEventListener('click', function () { setDefaults(); load(); });

    buildPickers();
    setDefaults();
    load();
})();
</script>
@endsection