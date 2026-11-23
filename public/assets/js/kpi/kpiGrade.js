(function () {
    'use strict';

    const SCALE = [
        { min: 100, grade: 'A', label: 'Sangat Baik',   level: null,       range: '100%',      desc: 'Seluruh target tercapai penuh. Rata-rata capaian semua target mencapai 100%.' },
        { min: 95,  grade: 'B', label: 'Baik',          level: 'High',     range: '95% – 99%', desc: 'Capaian sangat mendekati target penuh. Sedikit lagi menuju grade A.' },
        { min: 90,  grade: 'B', label: 'Baik',          level: 'Mid',      range: '90% – 94%', desc: 'Capaian baik dan stabil, masih ada ruang kecil untuk dioptimalkan.' },
        { min: 85,  grade: 'B', label: 'Baik',          level: 'Standard', range: '85% – 89%', desc: 'Capaian berada di level baik yang standar.' },
        { min: 80,  grade: 'B', label: 'Baik',          level: 'Low',      range: '80% – 84%', desc: 'Capaian baik namun berada di batas bawah kategori Baik. Perlu dijaga agar tidak turun ke Cukup.' },
        { min: 70,  grade: 'C', label: 'Cukup',         level: null,       range: '70% – 79%', desc: 'Capaian cukup. Sebagian target belum terpenuhi dan perlu ditingkatkan.' },
        { min: 60,  grade: 'D', label: 'Kurang',        level: null,       range: '60% – 69%', desc: 'Capaian kurang. Banyak target belum terpenuhi sehingga perlu perhatian dan pendampingan.' },
        { min: 0,   grade: 'E', label: 'Sangat Kurang', level: null,       range: '< 60%',     desc: 'Capaian sangat kurang. Mayoritas target masih jauh dari terpenuhi dan perlu tindak lanjut segera.' }
    ];

    function clampScore(score) {
        const v = Math.max(0, Math.min(100, Number(score) || 0));
        return Math.round(v * 10) / 10;
    }

    function rowIndex(score) {
        const value = clampScore(score);
        const i = SCALE.findIndex(r => value >= r.min);
        return i === -1 ? SCALE.length - 1 : i;
    }

    function displayOf(r) {
        return r.grade + ' (' + r.label + (r.level ? ' - ' + r.level : '') + ')';
    }

    function calc(score) {
        const r = SCALE[rowIndex(score)];
        return { grade: r.grade, label: r.label, level: r.level, display: displayOf(r), score: clampScore(score) };
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function injectStyle() {
        if (document.getElementById('kpi-grade-style')) return;
        const style = document.createElement('style');
        style.id = 'kpi-grade-style';
        style.textContent = `
            .kg-A{--kg-c:#15803d;--kg-bg:#e8f7ee;--kg-bd:#b7e4c7}
            .kg-B{--kg-c:#1d4ed8;--kg-bg:#e8effe;--kg-bd:#bfd1fb}
            .kg-C{--kg-c:#b45309;--kg-bg:#fff4dc;--kg-bd:#f3d9a0}
            .kg-D{--kg-c:#c2410c;--kg-bg:#ffece0;--kg-bd:#f6c9aa}
            .kg-E{--kg-c:#b91c1c;--kg-bg:#fde8e8;--kg-bd:#f5bcbc}
            .kpi-grade-badge{display:inline-flex;align-items:center;justify-content:center;width:1.65rem;height:1.65rem;padding:0;margin-left:.5rem;border-radius:.5rem;border:1px solid var(--kg-bd);background:var(--kg-bg);color:var(--kg-c);font-size:.8rem;font-weight:700;line-height:1;cursor:pointer;vertical-align:middle;transition:transform .12s,box-shadow .12s}
            .kpi-grade-badge:hover{transform:translateY(-1px);box-shadow:0 2px 6px rgba(15,23,42,.15)}
            .kpi-grade-badge:focus-visible{outline:2px solid var(--kg-c);outline-offset:2px}
            .emp-badge{display:flex;align-items:center;flex-wrap:wrap;gap:.4rem}
            .emp-badge .kpi-grade-badge{margin-left:0}
            .kpi-gm .kg-hero{display:flex;align-items:center;gap:1rem;padding:1rem;border-radius:.9rem;background:var(--kg-bg);border:1px solid var(--kg-bd)}
            .kpi-gm .kg-letter{flex:0 0 auto;width:3.4rem;height:3.4rem;border-radius:.9rem;background:#fff;color:var(--kg-c);border:2px solid var(--kg-c);display:flex;align-items:center;justify-content:center;font-size:1.7rem;font-weight:800}
            .kpi-gm .kg-title{font-weight:700;color:#0f172a}
            .kpi-gm .kg-score{color:var(--kg-c);font-weight:600;font-size:.9rem}
            .kpi-gm .kg-meta{color:#64748b;font-size:.8rem;margin-top:.1rem}
            .kpi-gm .kg-desc{margin:1rem 0;color:#334155;font-size:.9rem;line-height:1.55}
            .kpi-gm .kg-scale-title{font-size:.72rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#94a3b8;margin-bottom:.4rem}
            .kpi-gm .kg-row{display:flex;align-items:center;gap:.7rem;padding:.4rem .6rem;border-radius:.5rem;font-size:.85rem;color:#475569}
            .kpi-gm .kg-row.is-active{background:#f1f5f9;color:#0f172a;font-weight:600}
            .kpi-gm .kg-row-label{flex:1}
            .kpi-gm .kg-row-label small{color:#94a3b8;font-weight:500}
            .kpi-gm .kg-row-range{font-variant-numeric:tabular-nums;color:#64748b}
            .kpi-gm .kg-chip{width:1.4rem;height:1.4rem;border-radius:.4rem;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:var(--kg-c);background:var(--kg-bg);border:1px solid var(--kg-bd)}
            .kpi-gm .kg-note{margin-top:.8rem;font-size:.75rem;color:#94a3b8}
        `;
        document.head.appendChild(style);
    }

    function ensureModal() {
        let el = document.getElementById('kpiGradeModal');
        if (el) return el;
        el = document.createElement('div');
        el.id = 'kpiGradeModal';
        el.className = 'modal fade';
        el.tabIndex = -1;
        el.setAttribute('aria-hidden', 'true');
        el.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content kpi-gm">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold">Detail Grade KPI</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body" id="kpiGradeModalBody"></div>
                </div>
            </div>`;
        document.body.appendChild(el);
        return el;
    }

    function renderBody(g, opt) {
        const idx = rowIndex(g.score);
        const r = SCALE[idx];
        const meta = [opt.nama, opt.total !== '' && opt.total != null ? opt.total + ' target' : null]
            .filter(Boolean).map(esc).join(' · ');

        const rows = SCALE.map((s, i) => `
            <div class="kg-row ${i === idx ? 'is-active' : ''}">
                <span class="kg-chip kg-${s.grade}">${s.grade}</span>
                <span class="kg-row-label">${s.label}${s.level ? ' <small>· ' + s.level + '</small>' : ''}</span>
                <span class="kg-row-range">${s.range}</span>
            </div>`).join('');

        return `
            <div class="kg-hero kg-${r.grade}">
                <div class="kg-letter">${r.grade}</div>
                <div>
                    <div class="kg-title">${esc(displayOf(r))}</div>
                    <div class="kg-score">Rata-rata capaian ${g.score.toFixed(1)}%</div>
                    ${meta ? `<div class="kg-meta">${meta}</div>` : ''}
                </div>
            </div>
            <p class="kg-desc">${esc(r.desc)}</p>
            <div class="kg-scale-title">Skala grade</div>
            <div class="kg-scale">${rows}</div>
            <div class="kg-note">Grade dihitung dari rata-rata capaian seluruh target karyawan.</div>`;
    }

    function openDetail(score, opt) {
        injectStyle();
        const el = ensureModal();
        el.querySelector('#kpiGradeModalBody').innerHTML = renderBody(calc(score), opt || {});
        (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)).show();
    }

    function badge(g, opt) {
        if (!g) return '';
        injectStyle();
        opt = opt || {};
        const r = SCALE[rowIndex(g.score)];
        return `<button type="button" class="kpi-grade-badge kg-${r.grade}" data-kpi-grade data-score="${esc(g.score)}" data-nama="${esc(opt.nama || '')}" data-total="${opt.total == null ? '' : esc(opt.total)}" title="Grade ${r.grade} — klik untuk detail" aria-label="Grade ${r.grade}, klik untuk detail">${r.grade}</button>`;
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-kpi-grade]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        openDetail(btn.dataset.score, { nama: btn.dataset.nama, total: btn.dataset.total });
    }, true);

    window.KpiGrade = { calc: calc, badge: badge, open: openDetail, scale: SCALE };
})();