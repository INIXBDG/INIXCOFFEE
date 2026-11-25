(function (global) {
    'use strict';

    const f1 = (v) => (Number(v) || 0).toFixed(1);
    const f2 = (v) => (Number(v) || 0).toFixed(2);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    const namaBulan = (tahunBulan) => {
        if (!tahunBulan) return '-';
        const parts = String(tahunBulan).split('-');
        if (parts.length < 2) return tahunBulan;
        return BULAN[parseInt(parts[1], 10) - 1] || tahunBulan;
    };

    const CFG = {
        personal: {
            manualRoutes: ['dorong inovasi pelayanan', 'inisiatif efisiensi keuangan', 'mengurangi manual work dan error', 'pengeluaran biaya karyawan'],
            rupiahRoutes: ['Pemasukan Kotor', 'meningkatkan revenue perusahaan'],
            trafikRoutes: ['target penjualan tahunan', 'Pemasukan Kotor'],
            updateTargetUrl: () => global.KPI_CONFIG.updateTargetPerSalesRoute
        },
        index: {
            manualRoutes: ['dorong inovasi pelayanan', 'inisiatif efisiensi keuangan', 'mengurangi manual work dan error'],
            rupiahRoutes: ['pemasukan kotor', 'meningkatkan revenue perusahaan', 'target penjualan tahunan', 'target penjualan project tahunan', 'pendapatan penjualan project'],
            trafikRoutes: ['target penjualan tahunan', 'pemasukan kotor'],
            updateTargetUrl: () => global.KPI_CONFIG.updateTargetPerSales
        }
    };

    const rpPersonal = (a) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(a || 0);
    const rpIndex = (n = 0) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    const rp = (mode) => (mode === 'index' ? rpIndex : rpPersonal);

    const storage = (mode, p) => (mode === 'index' ? `${global.KPI_CONFIG.storageBase}/${p || ''}` : `/storage/${p || ''}`);

    const tanggalSingkat = (t) => {
        if (!t) return '-';
        const d = new Date(t);
        return isNaN(d) ? '-' : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
    };

    const PP_ROUTE = 'peserta puas dengan pelayanan dan fasilitas training';
    const isPesertaPuasRoute = (condition) => String(condition || '').toLowerCase().trim() === PP_ROUTE;

    function buildKaryawan(data) {
        const list = Array.isArray(data.karyawan) ? data.karyawan.filter(k => k) : (data.karyawan ? [data.karyawan] : []);
        let no = 1;
        const html = list.map(item => `<div class="d-flex align-items-center py-2 participant-item"><div class="avatar me-3">${no++}</div><div class="flex-grow-1"><div class="fw-semibold text-dark small">${item.nama_lengkap || '-'}</div><div class="text-muted small">${item.jabatan || '-'}</div></div></div>`).join('') || '<div class="text-muted small">Tidak ada karyawan</div>';
        return { list, html };
    }

    function pemasukanBersihHtml(dd, mode) {
        const bulanIndo = BULAN;
        const previousData = dd.previous_quarter?.data || [];
        const dl = (p) => (mode === 'index' ? `download="${p ? 'true' : 'false'}"` : (p ? 'download' : ''));
        const IC = mode === 'index' ? 'menu-icon ' : '';
        return `<div class="mt-4"><div class="row g-4">${previousData.map((item, index) => `<div class="col-12 col-md-6 col-lg-4"><div class="card border-0 shadow-sm h-100 quarter-card"><div class="card-body d-flex flex-column p-4"><div class="d-flex justify-content-between align-items-start mb-3"><div><h6 class="fw-semibold text-muted mb-1">Periode</h6><h5 class="fw-bold mb-0">${bulanIndo[(item.month || 1) - 1] ?? '-'}</h5></div><span class="badge rounded-pill bg-${item.color || 'secondary'} bg-opacity-10 text-${item.color || 'secondary'} px-3 py-2">Laporan</span></div><div class="mb-3"><h3 class="fw-bold text-dark mb-0">Rp ${item.nilai ? Number(item.nilai).toLocaleString('id-ID') : '-'}</h3><small class="text-muted">Total Pemasukan</small></div><div class="flex-grow-1"><p class="text-muted small mb-2 description-text" id="desc-${index}">${item.description ?? '-'}</p>${(item.description && item.description.length > 100) ? `<button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${index}">Lihat Selengkapnya</button>` : ''}</div><div class="d-flex justify-content-end align-items-center mt-4"><a href="${storage(mode, item.file_paths)}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" ${dl(item.file_paths)}><i class="${IC}fa-solid fa-download"></i> Download</a></div></div></div></div>`).join('')}</div></div><style>.quarter-card { border-radius: 16px; transition: all 0.25s ease; background: #ffffff; }.quarter-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }.quarter-card h3 { letter-spacing: 0.5px; }.quarter-card .badge { font-size: 12px; font-weight: 500; }.description-text { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }.description-text.expanded { -webkit-line-clamp: unset; overflow: visible; }.quarter-card .btn { border-radius: 8px; font-size: 13px; }</style>`;
    }

    function kepuasanHtml(dd) {
        const item = dd || {};
        return `<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Feedback</span><span class="fw-bold">${item.total_feedback ?? 0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Sesi</span><span class="fw-bold">${item.total_sessions ?? 0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Prediksi</span><span class="fw-bold text-primary">${item.prediction ?? 0}</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA</h6><div class="mb-3"><small class="text-muted d-block">Top Performer</small><span class="fw-bold text-success">${item.top_performer?.label ?? '-'} (${item.top_performer?.value ?? 0})</span></div><div><small class="text-muted d-block">Lowest Performer</small><span class="fw-bold text-danger">${item.lowest_performer?.label ?? '-'} (${item.lowest_performer?.value ?? 0})</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${item.trend === 'up' ? 'success' : (item.trend === 'down' ? 'danger' : 'secondary')}">Trend: ${item.trend || '-'} (${f1(item.trend_value)})</span></div><div class="mb-2"><span class="badge bg-${item.consistency === 'stable' ? 'success' : 'warning'}">Konsistensi: ${item.consistency || '-'}</span></div><div><span class="badge bg-${item.target_status === 'on_track' ? 'success' : (item.target_status === 'at_risk' ? 'warning' : 'danger')}">Target: ${item.target_status || '-'}</span></div></div></div></div><div class="col-12"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">KATEGORI PENILAIAN</h6><div class="row text-center">${Object.entries(item.category_scores || {}).map(([key, val]) => `<div class="col"><div class="p-2 border rounded-3"><small class="text-muted d-block">${key}</small><h5 class="fw-bold mb-0">${val ?? 0}</h5></div></div>`).join('')}</div></div></div></div><div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${item.insight ?? '-'}</div></div></div>`;
    }

    function kontribusiPelatihanHtml(dd) {
        const cb = dd.class_breakdown || {};
        const internal = Number(cb.internal) || 0;
        const freelance = Number(cb.freelance) || 0;
        const total = internal + freelance;
        const target = Number(cb.target) || 0;
        const sisa = Math.max(0, target - internal);
        const persen = Math.min(100, Number(dd.progress) || 0);
        const bulan = Array.isArray(cb.per_bulan) ? cb.per_bulan : [];
        const instruktur = Array.isArray(cb.per_instruktur) ? cb.per_instruktur : [];

        const now = new Date();
        const keyIni = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
        const bulanBerjalan = bulan.filter(b => b.bulan <= keyIni);
        const rataBulan = bulanBerjalan.length ? internal / bulanBerjalan.length : 0;
        const urut = [...bulanBerjalan].sort((a, b) => b.internal - a.internal);
        const tertinggi = urut[0];
        const terendah = urut[urut.length - 1];
        const maxBulan = Math.max(1, ...bulan.map(b => b.internal + b.freelance));
        const maxInstruktur = Math.max(1, ...instruktur.map(i => i.jumlah));
        const targetBulanan = target / 12;

        const stat = (label, nilai, sub, warna) => `
            <div class="col-6 col-lg-3">
                <div class="p-3 rounded-3 bg-white border h-100">
                    <div class="text-muted small mb-1">${label}</div>
                    <div class="fs-3 fw-bold text-${warna}">${nilai}</div>
                    <div class="text-muted small">${sub}</div>
                </div>
            </div>`;

        const barisBulan = bulan.map(b => {
            const jml = b.internal + b.freelance;
            const wIn = (b.internal / maxBulan) * 100;
            const wOl = (b.freelance / maxBulan) * 100;
            const capai = targetBulanan > 0 ? (b.internal / targetBulanan) * 100 : 0;
            const warna = capai >= 100 ? 'text-success' : 'text-danger';
            return `
                <tr>
                    <td class="fw-semibold">${esc(namaBulan(b.bulan))}</td>
                    <td class="text-center">${b.internal}</td>
                    <td class="text-center">${b.freelance}</td>
                    <td class="text-center fw-semibold">${jml}</td>
                    <td style="min-width:180px;">
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-primary" style="width:${wIn}%"></div>
                            <div class="progress-bar bg-secondary" style="width:${wOl}%"></div>
                        </div>
                    </td>
                    <td class="text-center fw-semibold ${warna}">${f1(capai)}%</td>
                </tr>`;
        }).join('');

        const barisInstruktur = instruktur.map((i, idx) => `
            <tr>
                <td class="text-center">${idx + 1}</td>
                <td class="fw-semibold">${esc(i.nama)}</td>
                <td class="text-center">${i.jumlah}</td>
                <td style="min-width:160px;">
                    <div class="progress" style="height:10px;">
                        <div class="progress-bar bg-success" style="width:${(i.jumlah / maxInstruktur) * 100}%"></div>
                    </div>
                </td>
                <td class="text-center">${f1(i.persen)}%</td>
            </tr>`).join('');

        return `
        <div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-1"><i class="fa-solid fa-chalkboard-user me-2 text-primary"></i>Kontribusi Kelas yang Dibawakan Instruktur Inixindo</h6>
                <small class="text-muted d-block mb-3">Kelas yang dibawakan orang luar tidak dihitung dalam pencapaian target.</small>

                <div class="row g-3 mb-4">
                    ${stat('Seluruh Kelas', total, 'Inixindo + orang luar', 'dark')}
                    ${stat('Kelas Instruktur Inixindo', internal, 'Dihitung untuk target', 'primary')}
                    ${stat('Kelas Orang Luar', freelance, 'Tidak dihitung untuk target', 'secondary')}
                    ${stat('Sisa Menuju Target', sisa, `Target ${target} kelas`, sisa === 0 ? 'success' : 'danger')}
                </div>

                <div class="p-3 rounded-3 bg-white border mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small fw-semibold">Pencapaian target tahun ini</span>
                        <span class="small fw-bold text-primary">${internal} dari ${target} kelas (${f1(persen)}%)</span>
                    </div>
                    <div class="progress" style="height:14px;">
                        <div class="progress-bar bg-primary" style="width:${persen}%"></div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    ${stat('Rata-rata per Bulan', f1(rataBulan), `Kelas Inixindo, patokan target ${f1(targetBulanan)} per bulan`, 'info')}
                    ${stat('Bulan Tertinggi', tertinggi ? tertinggi.internal : '-', tertinggi ? esc(namaBulan(tertinggi.bulan)) : '-', 'success')}
                    ${stat('Bulan Terendah', terendah ? terendah.internal : '-', terendah ? esc(namaBulan(terendah.bulan)) : '-', 'danger')}
                    ${stat('Instruktur Terlibat', instruktur.filter(i => i.jumlah > 0).length, `Dari ${instruktur.length} peserta target`, 'warning')}
                </div>

                <div class="p-3 rounded-3 bg-white border mb-4">
                    <div class="fw-semibold mb-1">Jumlah Kelas Tiap Bulan</div>
                    <div class="small text-muted mb-3">
                        <span class="badge bg-primary me-1">&nbsp;</span>Instruktur Inixindo
                        <span class="badge bg-secondary ms-3 me-1">&nbsp;</span>Orang luar
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">Kelas Inixindo</th>
                                    <th class="text-center">Kelas Orang Luar</th>
                                    <th class="text-center">Seluruhnya</th>
                                    <th>Perbandingan</th>
                                    <th class="text-center">Pencapaian Bulan Ini</th>
                                </tr>
                            </thead>
                            <tbody>${barisBulan || '<tr><td colspan="6" class="text-center text-muted">Belum ada data kelas</td></tr>'}</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .kp-scroll { max-height: 360px; overflow-y: scroll; overflow-x: auto; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: #adb5bd #f1f3f5; }
            .kp-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
            .kp-scroll::-webkit-scrollbar-track { background: #f1f3f5; border-radius: 8px; }
            .kp-scroll::-webkit-scrollbar-thumb { background: #adb5bd; border-radius: 8px; }
            .kp-scroll thead th { position: sticky; top: 0; z-index: 1; background: #f8f9fa; }
        </style>`;
    }

    function buildTrafikSales(data, dd, cond, mode) {
        const cfg = CFG[mode];
        const rupiah = rp(mode);
        const IC = mode === 'index' ? 'menu-icon ' : '';

        if (cfg.trafikRoutes.includes(cond)) {
            const salesPerf = dd.sales_performance || {};
            if (!salesPerf.data) return '';

            if (salesPerf.type === 'individual') {
                const s = salesPerf.data;
                const statusClass = s.status === 'achieved' ? 'badge-success' : 'badge-warning';
                const progressColor = s.status === 'achieved' ? '#28a745' : '#ffc107';
                const progressWidth = Math.min(s.percentage ?? 0, 100);
                return `<div class="card shadow-sm mb-4 mt-2"><div class="card-body"><div class="row"><div class="col-md-6"><p class="mb-2"><strong>Sales:</strong> ${s.nama || '-'}</p><p class="mb-2"><strong>Revenue:</strong> ${rupiah(s.revenue)}</p><p class="mb-3"><strong>Target:</strong> ${rupiah(s.presentase_kemampuan)}</p></div><div class="col-md-6"><div class="mb-2"><div class="d-flex justify-content-between"><span>Progress</span><span>${f1(s.percentage)}%</span></div><div class="progress" style="height: 10px;"><div class="progress-bar" role="progressbar" style="width: ${progressWidth}%; background-color: ${progressColor};"></div></div></div><div class="text-end"><span class="badge ${statusClass} p-2">${(s.status || '').toUpperCase()}</span></div></div></div></div></div>`;
            }

            if (salesPerf.type === 'all' && Array.isArray(salesPerf.data)) {
                let rows = '';
                salesPerf.data.forEach((sales, index) => {
                    if (!sales) return;
                    const statusClass = sales.status === 'achieved' ? 'badge-success' : 'badge-warning';
                    const textClass = sales.status === 'achieved' ? 'text-success' : 'text-warning';
                    const targetValue = Number(sales.presentase_kemampuan || 0).toLocaleString('id-ID', { useGrouping: false });
                    rows += `<tr id="row-${sales.kode_karyawan || index}"><td class="text-center">${index + 1}</td><td><strong>${sales.nama || '-'}</strong></td><td class="text-end">${rupiah(sales.revenue)}</td><td class="text-center"><div class="input-group input-group-sm" style="max-width: 150px; float: right;"><input type="text" class="form-control text-end target-input ${sales.id_detailPerson ? '' : 'is-invalid'}" value="${targetValue}" data-id-detail="${sales.id_detailPerson || ''}" data-kode-karyawan="${sales.kode_karyawan || ''}" placeholder="Target" ${!sales.id_detailPerson ? 'disabled' : ''}></div><div class="loading-spinner" style="display: none; float: right; margin-right: 10px;"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div><div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;"><i class="${IC}fa-solid fa-check-circle text-success"></i></div><div class="clearfix"></div></td><td class="text-center ${textClass}"><strong>${f1(sales.percentage)}%</strong></td><td class="text-center"><span class="badge ${statusClass}">${(sales.status || '').toUpperCase()}</span></td></tr>`;
                });
                let htmlTargetTahunanSales = '';
                Object.entries(dd.triwulan_data || {}).forEach(([label, value]) => {
                    const safeLabel = (label || '').toString().replace(/_/g, ' ');
                    htmlTargetTahunanSales += `<div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h5 class="card-title">${safeLabel}</h5><p class="card-text">Rp ${Number(value || 0).toLocaleString('id-ID')}</p></div></div></div>`;
                });
                return `<div class="row"><div class="col"><div class="card shadow-sm mt-3"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th class="text-center" width="5%">No</th><th>Sales</th><th class="text-end" width="20%">Revenue</th><th class="text-end" width="20%">Target (Editable)</th><th class="text-center" width="15%">Persentase</th><th class="text-center" width="15%">Status</th></tr></thead><tbody>${rows || '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>'}</tbody></table></div></div></div></div><div class="col"><div class="card shadow-sm border-0 mt-3"><div class="card-body"><div class="row g-3">${htmlTargetTahunanSales || '<p class="text-muted">Tidak ada data triwulan</p>'}</div></div><div class="mb-2 text-center"><hr><p>Data Triwulan diambil dari tahun ${data.detail_jangka || '-'}</p></div></div></div></div>`;
            }
            return '';
        }

        if (mode === 'index' && cond === 'pemasukan bersih') return pemasukanBersihHtml(dd, mode);
        if (mode === 'index' && ['Kepuasan Pelanggan', 'kepuasan pelanggan'].includes(cond)) return kepuasanHtml(dd);
        if (cond === 'peningkatan kontribusi pelatihan') return kontribusiPelatihanHtml(dd);
        return '';
    }

    function buildKontribusiSales(dd, cond, mode) {
        const rupiah = rp(mode);
        const kontribusi = dd.kontribusi_sales;
        if (!kontribusi || String(cond).toLowerCase().trim() !== 'pemasukan kotor') return '';

        const statusMap = {
            on_track: ['bg-primary', 'On Track'],
            selesai: ['bg-success', 'Selesai'],
            gagal: ['bg-danger', 'Gagal']
        };
        const badge = (s) => {
            const [cls, label] = statusMap[s] || ['bg-secondary', '-'];
            return `<span class="badge ${cls}">${label}</span>`;
        };
        const selisihCls = (n) => (n < 0 ? 'text-danger' : 'text-success');

        const rows = (kontribusi.rows || []).map((r, i) => `
            <tr>
                <td class="text-center">${i + 1}</td>
                <td class="fw-semibold">${r.nama || '-'}</td>
                <td class="text-end">${rupiah(r.triwulan[1])}</td>
                <td class="text-end">${rupiah(r.triwulan[2])}</td>
                <td class="text-end">${rupiah(r.triwulan[3])}</td>
                <td class="text-end">${rupiah(r.triwulan[4])}</td>
                <td class="text-end fw-bold text-primary">${rupiah(r.total)}</td>
                <td class="text-end">${rupiah(r.target)}</td>
                <td class="text-end fw-semibold ${selisihCls(r.selisih)}">${rupiah(r.selisih)}</td>
                <td class="text-center">${f1(r.persentase)}%</td>
                <td class="text-center">${badge(r.status)}</td>
            </tr>`).join('');

        const t = kontribusi.total;
        const footer = t ? `
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-center">TOTAL</td>
                    <td class="text-end">${rupiah(t.triwulan[1])}</td>
                    <td class="text-end">${rupiah(t.triwulan[2])}</td>
                    <td class="text-end">${rupiah(t.triwulan[3])}</td>
                    <td class="text-end">${rupiah(t.triwulan[4])}</td>
                    <td class="text-end text-primary">${rupiah(t.total)}</td>
                    <td class="text-end">${rupiah(t.target)}</td>
                    <td class="text-end ${selisihCls(t.selisih)}">${rupiah(t.selisih)}</td>
                    <td class="text-center">${f1(t.persentase)}%</td>
                    <td class="text-center">${badge(t.status)}</td>
                </tr>
            </tfoot>` : '';

        return `
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="fa-solid fa-users me-2"></i>Kontribusi Sales ${kontribusi.tahun ?? ''}</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="4%">No</th><th>Sales</th>
                                    <th>Triwulan I</th><th>Triwulan II</th><th>Triwulan III</th><th>Triwulan IV</th>
                                    <th>Total</th><th>Target</th><th>Selisih</th><th>Persentase</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody>${rows || '<tr><td colspan="11" class="text-center text-muted">Belum ada target penjualan sales untuk tahun ini</td></tr>'}</tbody>
                            ${footer}
                        </table>
                    </div>
                </div>
            </div>`;
    }

    function manualDocumentHtml(dd) {
        const fileUrl = dd.dataManual?.manual_document || '';
        const fileName = fileUrl ? fileUrl.split('/').pop() : '';
        const fileExtension = fileName ? fileName.split('.').pop().toLowerCase() : '';
        const fullFileUrl = fileUrl ? `/storage/${fileUrl}` : '';
        let fileContent = '';
        if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
            fileContent = `<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3"><div class="mb-3" style="max-width: 100%; max-height: 300px;"><img src="${fullFileUrl}" alt="${fileName}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;"></div><div class="mt-2 text-center"><a href="${fullFileUrl}" download="${fileName}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download Gambar</a></div><div class="mt-2 small text-muted"><i class="fa-solid fa-file-image me-1"></i>${fileName}</div></div>`;
        } else if (fileExtension === 'pdf') {
            fileContent = `<div class="w-100 h-100 d-flex flex-column"><div class="flex-grow-1 mb-3" style="min-height: 250px;"><iframe src="${fullFileUrl}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe></div><div class="text-center"><a href="${fullFileUrl}" download="${fileName}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download PDF</a></div><div class="mt-2 small text-muted text-center"><i class="fa-solid fa-file-pdf me-1"></i>${fileName}</div></div>`;
        } else {
            fileContent = `<div class="text-center py-5"><div class="mb-3"><i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i></div><p class="text-muted mb-3">File tidak dapat ditampilkan</p><p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p></div>`;
        }
        return `<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-file me-2"></i>Dokumen Manual</h6><div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">${fileContent}</div><div class="mt-3 small text-muted text-center"><i class="fa-solid fa-info-circle me-1"></i>Klik tombol download untuk menyimpan file</div>`;
    }

    function rupiahSummaryHtml(data, dd, mode, karyawanList) {
        const rupiah = rp(mode);
        const monthlyData = dd.monthly_data || {};
        const dailyData = dd.daily_breakdown_per_month || {};

        if (mode === 'personal') {
            const monthlyEntries = Object.entries(monthlyData);
            const lastMonthEntry = monthlyEntries[monthlyEntries.length - 1] || [];
            const labelBulanTerakhir = lastMonthEntry[0] ? namaBulan(lastMonthEntry[0]) : '-';
            const nilaiBulanTerakhir = lastMonthEntry[1] || 0;
            const allDays = [];
            Object.values(dailyData).forEach(month => {
                Object.entries(month).forEach(([tanggal, nilai]) => { allDays.push([tanggal, nilai]); });
            });
            const top3 = allDays.sort((a, b) => b[1] - a[1]).slice(0, 3);
            const karyawanTerkait = karyawanList[0] || null;
            return `<div class="mb-4"><h6 class="fw-semibold text-primary mb-1"><i class="fa-solid fa-wallet me-2"></i>${data.judul}</h6><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded bg-light"><div class="text-muted small mb-1">Bulan Terakhir</div><div class="fw-semibold">${labelBulanTerakhir}</div><div class="fw-bold fs-6 text-dark">${rupiah(nilaiBulanTerakhir)}</div></div><div class="mb-3"><div class="text-muted small mb-2">Top Hari Tertinggi</div>${top3.map(([tanggal, nilai], index) => `<div class="d-flex justify-content-between mb-1 ${index > 0 ? 'text-muted' : ''}"><span>${tanggalSingkat(tanggal)}</span><span class="fw-semibold">${rupiah(nilai)}</span></div>`).join('')}</div><hr class="my-3"><div class="d-flex align-items-center gap-2 text-muted small"><i class="fa-solid fa-user-circle fs-5"></i><span class="fw-semibold text-dark">${karyawanTerkait?.nama_lengkap ?? '-'}</span><span>• ${karyawanTerkait?.jabatan ?? '-'}</span></div>`;
        }

        const monthKeys = Object.keys(monthlyData).filter(k => k).sort();
        const keyTerakhir = monthKeys.length > 0 ? monthKeys[monthKeys.length - 1] : null;
        const nilaiTerakhir = keyTerakhir ? (monthlyData[keyTerakhir] ?? 0) : 0;
        const labelTerakhir = keyTerakhir ? new Date(`${keyTerakhir}-01`).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '-';
        const seluruhHarian = Object.entries(dailyData).flatMap(([_, bulan]) => Object.entries(bulan || {}).map(([tgl, nil]) => [tgl, nil])).filter(([_, nilai]) => typeof nilai === 'number' && nilai > 0);
        const top3 = seluruhHarian.sort((a, b) => (b[1] || 0) - (a[1] || 0)).slice(0, 3);
        const karyawanTerkait = karyawanList[0] || {};
        return `<div class="p-2"><div class="mb-4"><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded-3 border bg-white shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Bulan Terakhir</div><div class="fw-semibold">${labelTerakhir}</div></div><div class="text-end"><div class="fw-bold fs-5 text-dark">${rupiah(nilaiTerakhir)}</div></div></div></div><div class="mb-3"><div class="text-muted small mb-2 fw-semibold">Top Hari Tertinggi</div>${top3.map(([tanggal, nilai], index) => `<div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded ${index === 0 ? 'bg-success-subtle' : 'bg-light'}"><div class="d-flex align-items-center gap-2"><span class="badge ${index === 0 ? 'bg-success' : 'bg-secondary'}">#${index + 1}</span><span class="${index === 0 ? 'fw-semibold text-dark' : 'text-muted'}">${tanggalSingkat(tanggal)}</span></div><span class="fw-semibold ${index === 0 ? 'text-success' : 'text-dark'}">${rupiah(nilai)}</span></div>`).join('')}</div><hr class="my-3"><div class="d-flex align-items-center justify-content-between"><div class="d-flex align-items-center gap-2"><i class="bi bi-person-circle fs-4 text-secondary"></i><div><div class="fw-semibold text-dark">${karyawanTerkait?.nama_lengkap ?? '-'}</div><small class="text-muted">${karyawanTerkait?.jabatan ?? '-'}</small></div></div><span class="badge bg-light text-dark border">Karyawan</span></div></div>`;
    }

    function buildPie(data, dd, cond, mode, karyawanList) {
        const cfg = CFG[mode];
        if (cfg.manualRoutes.includes(cond)) return manualDocumentHtml(dd);
        if (cfg.rupiahRoutes.includes(cond)) return rupiahSummaryHtml(data, dd, mode, karyawanList);
        return `<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>Chart ${cond}</h6><div class="chart-container flex-grow-1"><canvas id="MyChartDoughtnut"></canvas></div>`;
    }

    function inovasiHtml(dd) {
        const dataInovasi = dd.dataInovasi || [];
        return `
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Judul</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${dataInovasi.length
                            ? dataInovasi.map(inovasi => `
                                <tr>
                                    <td class="fw-semibold">${inovasi.judul ?? '-'}</td>
                                    <td>${inovasi.deskripsi ?? '-'}</td>
                                </tr>`).join('')
                            : `<tr><td colspan="2" class="text-center text-muted">Tidak ada data inovasi</td></tr>`}
                    </tbody>
                </table>
            </div>`;
    }

    function laporanHtml(dd, mode) {
        const bulanIndo = ['', ...BULAN];
        const IC = mode === 'index' ? 'menu-icon ' : '';
        const analisaData = dd.analisa_data || [];
        return `<div class="row g-4">${analisaData.map(item => {
            const fileUrl = mode === 'index'
                ? (item.file_paths ? global.KPI_CONFIG.storageBase + '/' + item.file_paths : '#')
                : storage(mode, item.file_paths);
            return `<div class="col-md-4 mt-5"><div class="card border-0 shadow-sm h-100"><div class="mb-2 p-3"><h5 class="fw-bold text-primary mb-1">${bulanIndo[item.month] || '-'}</h5><small class="text-muted">Laporan Analisis Bulanan</small></div><div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;"><div class="mb-3"><p class="mb-0" style="text-align: justify;">${item.description || ''}</p></div></div><div class="mt-auto p-3"><a href="${fileUrl}" class="btn btn-sm btn-outline-primary w-100" ${item.file_paths ? 'download' : ''}><i class="${IC}fa-solid fa-file-alt"></i></a></div></div></div>`;
        }).join('')}</div>`;
    }

    function performaHtml(dd) {
        const item = dd || {};
        const trend = item.trend ?? 'stable';
        const trendValue = item.trend_value ?? 0;
        const consistency = item.consistency ?? 'stable';
        const targetStatus = item.target_status ?? 'behind';
        const trendColor = trend === 'up' ? 'success' : (trend === 'down' ? 'danger' : 'secondary');
        const consistencyColor = consistency === 'stable' ? 'success' : 'warning';
        const targetColor = targetStatus === 'on_track' ? 'success' : (targetStatus === 'at_risk' ? 'warning' : 'danger');
        const divisionHtml = Object.entries(item.division_breakdown || {}).map(([div, val]) => `<div class="col mb-3"><div class="p-2 border rounded-3"><small class="text-muted d-block">${div}</small><h5 class="fw-bold mb-0">${f1(val)}%</h5></div></div>`).join('');
        const riskHtml = (item.risk_divisions && item.risk_divisions.length > 0) ? `<div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-danger mb-3">DIVISI BERISIKO</h6><div class="row">${item.risk_divisions.map(risk => `<div class="col mb-2"><div class="p-2 border rounded-3 text-center"><small class="text-muted d-block">${risk.name || '-'}</small><span class="fw-bold text-danger">${f1(risk.value)}%</span></div></div>`).join('')}</div></div></div></div>` : '';
        return `<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total KPI</span><span class="fw-bold">${item.total_kpi ?? 0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Divisi</span><span class="fw-bold">${item.total_division ?? 0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Progress</span><span class="fw-bold text-primary">${f1(item.progress)}%</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA DIVISI</h6><div class="mb-3"><small class="text-muted d-block">Top Division</small><span class="fw-bold text-success">${item.top_division?.name ?? '-'} (${f1(item.top_division?.value)}%)</span></div><div><small class="text-muted d-block">Lowest Division</small><span class="fw-bold text-danger">${item.lowest_division?.name ?? '-'} (${f1(item.lowest_division?.value)}%)</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${trendColor}">Trend: ${trend} (${f1(trendValue)})</span></div><div class="mb-2"><span class="badge bg-${consistencyColor}">Konsistensi: ${consistency}</span></div><div><span class="badge bg-${targetColor}">Target: ${targetStatus}</span></div></div></div></div><div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">BREAKDOWN DIVISI</h6><div class="row text-center">${divisionHtml}</div></div></div></div>${riskHtml}<div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${item.insight ?? '-'}</div></div></div>`;
    }

    function buildGapKompetensiForm(data, canEdit, csrfToken) {
        const list = Array.isArray(data.karyawan) ? data.karyawan : [];
        const disabledAttr = canEdit ? '' : 'disabled';
        const targetGap = Number(data.nilai_target ?? 0);

        const rows = list.map((item, i) => {
            const skala = item.skala_kemampuan ?? null;

            let badge;
            if (skala === null) {
                badge = '<span class="badge bg-secondary">Belum dinilai</span>';
            } else {
                // [INVERSE] Gap individu = 100 - kemampuan. Bagus bila gap <= target.
                const gapIndividu = Math.max(0, 100 - (Number(skala) * 10));
                const ok = gapIndividu <= targetGap;
                badge = `<span class="badge bg-${ok ? 'success' : 'danger'}">Gap ${f1(gapIndividu)}%</span>`;
            }

            return `
                <div class="row mb-2 align-items-center p-2 rounded">
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <span>${esc(item.nama_lengkap ?? '-')}</span>${badge}
                    </div>
                    <div class="col-md-6">
                        <input type="number" min="1" max="10" step="0.5" class="form-control"
                            name="data[${i}][kemampuan]" value="${skala ?? ''}"
                            placeholder="1 - 10" ${disabledAttr}>
                    </div>
                    <input type="hidden" name="data[${i}][id_karyawan]" value="${item.id_karyawan}">
                    <input type="hidden" name="data[${i}][detailTargetKey]" value="${item.detailTargetKey}">
                </div>`;
        }).join('');

        return `
            <div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body">
                <h6 class="fw-semibold mb-1">Penilaian Kemampuan Tim</h6>
                <small class="text-muted d-block mb-3">Isi skala 1-10 (8 = 80%). Gap dihitung dari rata-rata seluruh anggota tim. Hijau berarti gap individu masih dalam batas target (${f1(targetGap)}%), merah berarti melebihi batas.</small>
                <form id="formGapKompetensi">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                        <div class="col-md-6">Nama Karyawan</div><div class="col-md-6">Nilai (1-10)</div>
                    </div>
                    ${rows || '<div class="text-muted small">Tidak ada anggota tim.</div>'}
                    ${canEdit ? '<div class="mt-3"><button type="submit" class="btn btn-primary">Simpan</button></div>' : ''}
                </form>
            </div></div></div>`;
    }

    function genericStatisticHtml(cond) {
        return `<div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="d-flex justify-content-between mb-3"><h6 class="fw-semibold mb-0">Statistik ${cond}</h6><div class="d-flex gap-2"><select class="form-select form-select-sm" id="filterType"><option value="year">Per Tahun</option><option value="month">Per Bulan</option></select><select class="form-select form-select-sm d-none" id="filterMonth"><option value="">Pilih Bulan</option></select></div></div><div style="height:300px"><canvas id="StatisticChart"></canvas></div></div></div></div>`;
    }

    function buildStatistic(data, dd, cond, mode, pesertaPuas, isPesertaPuas) {
        const cfg = CFG[mode];
        if (cfg.manualRoutes.includes(cond)) return '';
        if (mode === 'index' && isPesertaPuas) return '';
        if (mode === 'index' && cond === 'performa kpi departemen') return performaHtml(dd);
        if (cond === 'inovation adaption rate') return inovasiHtml(dd);
        if (cond === 'laporan analisis keuangan') return laporanHtml(dd, mode);
        if (mode === 'personal' && cond === 'pemasukan bersih') return pemasukanBersihHtml(dd, mode);
        if (cond === 'persentase gap kompetensi tim terhadap standar skill') {
            const canEdit = mode === 'index'
                ? data.can_edit === true
                : (data.can_edit ?? ((global.KPI_CONFIG?.userJabatan || '').toLowerCase().trim() === 'koordinator itsm'));
            return buildGapKompetensiForm(data, canEdit, global.KPI_CONFIG.csrfToken);
        }
        if (mode === 'personal' && isPesertaPuas) return pesertaPuas;
        return genericStatisticHtml(cond);
    }

    function buildKalender(dd) {
        const instrukturDetails = Array.isArray(dd.instruktur_details) ? dd.instruktur_details : [];
        const hariLiburNasional = dd.hari_libur_nasional || { jumlah: 0, daftar: {} };
        if (instrukturDetails.length === 0) return '';

        const NAMA_BULAN_KAL = BULAN;
        const NAMA_HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        global.__instrukturData = instrukturDetails;
        global.__currentMonthIndex = {};

        function buildKalenderBulanHtml(instruktur, bulanKey) {
            const kalender = instruktur.kalender || {};
            if (!kalender[bulanKey]) return '<div class="text-muted text-center py-4">Tidak ada data untuk bulan ini.</div>';
            const [thn, bln] = bulanKey.split('-');
            const namaBulanKal = NAMA_BULAN_KAL[parseInt(bln, 10) - 1];
            const daysInMonth = new Date(thn, bln, 0).getDate();
            const firstDayOfWeek = new Date(thn, parseInt(bln, 10) - 1, 1).getDay();
            let cellsHtml = '';
            NAMA_HARI.forEach(h => { cellsHtml += `<div class="kal-header">${h}</div>`; });
            for (let i = 0; i < firstDayOfWeek; i++) { cellsHtml += `<div class="kal-cell kal-empty"></div>`; }
            for (let d = 1; d <= daysInMonth; d++) {
                const cellData = kalender[bulanKey][d] || { status: 'empty', keterangan: '' };
                cellsHtml += `<div class="kal-cell kal-${cellData.status}" title="${cellData.keterangan || ''}" data-bs-toggle="tooltip"><div class="kal-day">${d}</div></div>`;
            }
            const monthStats = Object.values(kalender[bulanKey]).reduce((acc, cur) => {
                acc[cur.status] = (acc[cur.status] || 0) + 1;
                return acc;
            }, {});
            return `
                <div class="kalender-bulan-slide" data-bulan="${bulanKey}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">${namaBulanKal} ${thn}</h6>
                        <div class="d-flex gap-2 small">
                            <span class="badge bg-success">Aktif: ${monthStats.working || 0}</span>
                            <span class="badge bg-warning text-dark">Cuti: ${monthStats.cuti || 0}</span>
                            <span class="badge bg-danger">Libur: ${monthStats.libur || 0}</span>
                        </div>
                    </div>
                    <div class="kalender-grid">${cellsHtml}</div>
                </div>`;
        }

        global.showKalenderBulan = function (instrukturId, bulanIndex) {
            const instruktur = global.__instrukturData.find(i => i.id == instrukturId);
            if (!instruktur) return;
            const kalender = instruktur.kalender || {};
            const bulanKeys = Object.keys(kalender).sort();
            const totalBulan = bulanKeys.length;
            if (totalBulan === 0) return;
            if (bulanIndex < 0) bulanIndex = totalBulan - 1;
            if (bulanIndex >= totalBulan) bulanIndex = 0;
            global.__currentMonthIndex[instrukturId] = bulanIndex;
            const bulanKey = bulanKeys[bulanIndex];
            const slideContainer = document.getElementById(`kalenderSlide_${instrukturId}`);
            if (slideContainer) slideContainer.innerHTML = buildKalenderBulanHtml(instruktur, bulanKey);
            const counterEl = document.getElementById(`bulanCounter_${instrukturId}`);
            if (counterEl) counterEl.textContent = `${bulanIndex + 1} dari ${totalBulan} bulan`;
            const prevBtn = document.getElementById(`btnPrevBulan_${instrukturId}`);
            const nextBtn = document.getElementById(`btnNextBulan_${instrukturId}`);
            if (prevBtn) prevBtn.disabled = (totalBulan <= 1);
            if (nextBtn) nextBtn.disabled = (totalBulan <= 1);
        };

        function buildCutiHtml(instruktur) {
            const cutiEntries = Object.entries(instruktur.daftar_cuti || {});
            if (cutiEntries.length === 0) return '';
            return `
                <div class="mt-3">
                    <h6 class="fw-semibold small text-muted mb-2"><i class="fa-solid fa-suitcase-rolling me-1"></i>Detail Cuti (${cutiEntries.length} hari)</h6>
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-warning"><tr><th>Tanggal</th><th>Tipe</th><th>Alasan</th></tr></thead>
                            <tbody>
                                ${cutiEntries.map(([tgl, dt]) => `
                                    <tr>
                                        <td class="small">${new Date(tgl).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                                        <td class="small">${dt.tipe || '-'}</td>
                                        <td class="small">${dt.alasan || '-'}</td>
                                    </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>`;
        }

        let liburNasionalHtml = '';
        const liburEntries = Object.entries(hariLiburNasional.daftar || {});
        if (liburEntries.length > 0) {
            liburNasionalHtml = `
                <div class="alert alert-light border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-flag me-2 text-danger"></i>Hari Libur Nasional</h6>
                        <span class="badge bg-danger">${hariLiburNasional.jumlah} Hari</span>
                    </div>
                    <div class="row g-2" style="max-height: 150px; overflow-y: auto;">
                        ${liburEntries.map(([tgl, ket]) => `
                            <div class="col-md-6">
                                <div class="small p-2 bg-danger bg-opacity-10 rounded">
                                    <strong>${new Date(tgl).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</strong>
                                    <div class="text-muted">${ket}</div>
                                </div>
                            </div>`).join('')}
                    </div>
                </div>`;
        }

        let accordionItems = '';
        instrukturDetails.forEach((instruktur, idx) => {
            const persentaseColor = instruktur.persentase >= 100 ? 'success' : (instruktur.persentase >= 75 ? 'warning' : 'danger');
            const kalender = instruktur.kalender || {};
            const bulanKeys = Object.keys(kalender).sort();
            const totalBulan = bulanKeys.length;
            global.__currentMonthIndex[instruktur.id] = 0;
            const cutiHtml = buildCutiHtml(instruktur);
            accordionItems += `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingInstruktur${idx}">
                        <button class="accordion-button ${idx > 0 ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstruktur${idx}" aria-expanded="${idx === 0 ? 'true' : 'false'}">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <div>
                                    <strong>${instruktur.nama}</strong>
                                    <div class="small text-muted">${instruktur.kode_karyawan} • ${instruktur.jabatan}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-${persentaseColor}">${f1(instruktur.persentase)}%</div>
                                    <div class="small text-muted">${instruktur.jam_aktif} / ${instruktur.target_jam} jam</div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapseInstruktur${idx}" class="accordion-collapse collapse ${idx === 0 ? 'show' : ''}" data-bs-parent="#accordionInstruktur">
                        <div class="accordion-body">
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-success-subtle"><div class="small text-muted">Hari Aktif</div><div class="fw-bold fs-5">${instruktur.total_hari_kerja}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-warning-subtle"><div class="small text-muted">Hari Cuti</div><div class="fw-bold fs-5">${instruktur.total_hari_cuti}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-danger-subtle"><div class="small text-muted">Hari Libur</div><div class="fw-bold fs-5">${instruktur.total_hari_libur}</div></div></div>
                                <div class="col-6 col-md-3"><div class="p-2 border rounded text-center bg-primary-subtle"><div class="small text-muted">Jam Aktif</div><div class="fw-bold fs-5">${instruktur.jam_aktif}</div></div></div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3 small">
                                <span><span class="kal-legend-box kal-working"></span> Hari Aktif</span>
                                <span><span class="kal-legend-box kal-cuti"></span> Cuti</span>
                                <span><span class="kal-legend-box kal-libur"></span> Libur Nasional</span>
                                <span><span class="kal-legend-box kal-weekend"></span> Weekend</span>
                                <span><span class="kal-legend-box kal-empty"></span> Tidak Ada Aktivitas</span>
                            </div>
                            ${cutiHtml}
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-semibold mb-0"><i class="fa-solid fa-calendar me-2"></i>Kalender Bulanan</h6>
                                    <span class="badge bg-info" id="bulanCounter_${instruktur.id}">1 dari ${totalBulan} bulan</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-3 p-2 bg-light rounded-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrevBulan_${instruktur.id}" onclick="showKalenderBulan(${instruktur.id}, window.__currentMonthIndex[${instruktur.id}] - 1)"><i class="fa-solid fa-chevron-left me-1"></i> Bulan Sebelumnya</button>
                                    <div class="flex-grow-1 text-center"><small class="text-muted">Gunakan tombol atau swipe untuk navigasi</small></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnNextBulan_${instruktur.id}" onclick="showKalenderBulan(${instruktur.id}, window.__currentMonthIndex[${instruktur.id}] + 1)">Bulan Selanjutnya <i class="fa-solid fa-chevron-right ms-1"></i></button>
                                </div>
                                <div id="kalenderSlide_${instruktur.id}">
                                    ${totalBulan > 0 ? buildKalenderBulanHtml(instruktur, bulanKeys[0]) : '<div class="text-muted text-center py-4">Tidak ada data kalender.</div>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
        });

        return `
            <div class="mt-3">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calendar-days me-2"></i>Kalender Kinerja Instruktur</h6>
                        ${liburNasionalHtml}
                        <div class="accordion" id="accordionInstruktur">
                            ${accordionItems}
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .kalender-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; background: #f8f9fa; padding: 6px; border-radius: 8px; }
                .kal-header { text-align: center; font-size: 11px; font-weight: 600; color: #6c757d; padding: 4px 0; }
                .kal-cell { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 12px; cursor: pointer; transition: transform 0.15s; position: relative; }
                .kal-cell:hover { transform: scale(1.1); z-index: 2; }
                .kal-day { font-weight: 500; }
                .kal-empty { background: transparent; }
                .kal-working { background: #d1e7dd; color: #0f5132; }
                .kal-cuti { background: #fff3cd; color: #664d03; }
                .kal-libur { background: #f8d7da; color: #842029; }
                .kal-weekend { background: #e2e3e5; color: #41464b; }
                .kal-legend-box { display: inline-block; width: 14px; height: 14px; border-radius: 3px; vertical-align: middle; margin-right: 4px; }
                .kal-legend-box.kal-working { background: #d1e7dd; }
                .kal-legend-box.kal-cuti { background: #fff3cd; }
                .kal-legend-box.kal-libur { background: #f8d7da; }
                .kal-legend-box.kal-weekend { background: #e2e3e5; }
                .kal-legend-box.kal-empty { background: #f8f9fa; border: 1px dashed #adb5bd; }
                .kalender-bulan-slide { animation: slideInBulan 0.3s ease-out; }
                @keyframes slideInBulan { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
            </style>`;
    }

    function buildPesertaPuasContent(data) {
        const d = data.data_detail || {};
        const s = d.feedback_summary;

        if (!s) {
            return `<div class="alert alert-secondary mt-4 mb-0 small">Belum ada penilaian dari peserta untuk tahun ini.</div>`;
        }

        const thr = s.threshold ?? 3.5;
        const target = Number(s.target ?? data.nilai_target ?? 0);
        const total = s.total || 0;
        const puas = s.puas || 0;
        const tidak = s.tidak_puas || 0;
        const prog = Number(d.progress || 0);
        const qa = d.question_averages || [];
        const ms = d.monthly_summary || [];
        const kategori = d.kategori_summary || [];

        const skala = Number(s.skala_maks || 4);
        const daftarPertanyaan = kategori.map(k => {
            const items = qa.filter(q => q.kategori === k.kategori);
            return `<div class="mb-2"><div class="fw-semibold small">${esc(k.kategori)}</div><ul class="small mb-0 ps-3">${items.map(q => `<li>${esc(q.label)}</li>`).join('')}</ul></div>`;
        }).join('');

        let kurang = 0;
        if (target < 100 && prog < target) {
            kurang = Math.max(0, Math.ceil((target / 100 * total - puas) / (1 - target / 100)));
        }

        const ranked = qa.filter(q => (q.n || 0) > 0).sort((a, b) => b.avg - a.avg);
        const topList = ranked.slice(0, 5);
        const bottomList = [...ranked].reverse().slice(0, 5);

        const mSorted = [...ms].sort((a, b) => b.pct - a.pct);
        const mBest = mSorted[0], mWorst = mSorted[mSorted.length - 1];

        const summaryCard = (view, icon, color, label, value, sub) => `
            <div class="col-6 col-lg">
                <div class="card pp-card border-0 shadow-sm h-100" data-view="${view}" role="button" tabindex="0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold">${label}</span>
                            <span class="rounded-circle bg-${color} bg-opacity-10 d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                <i class="fa-solid ${icon} text-${color}"></i>
                            </span>
                        </div>
                        <div class="fs-3 fw-bold text-${color}">${value}</div>
                        <div class="text-muted small">${sub}</div>
                        <div class="pp-hint small mt-2 text-primary"><i class="fa-solid fa-table me-1"></i>Klik untuk lihat tabel</div>
                    </div>
                </div>
            </div>`;

        const insight = (icon, color, title, text) => `
            <div class="col-md-6 col-xl-3">
                <div class="p-3 rounded-3 border bg-white h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid ${icon} text-${color} me-2"></i>
                        <span class="small fw-semibold text-muted">${title}</span>
                    </div>
                    <div class="small">${text}</div>
                </div>
            </div>`;

        const rankItem = (q, i, warna, tampilKurang) => {
            const lebar = Math.min(100, Math.max(0, (Number(q.avg) / skala) * 100));
            const jumlahKurang = (Number(q.distribusi?.[1]) || 0) + (Number(q.distribusi?.[2]) || 0);
            return `
                <div class="d-flex align-items-start py-2 ${i > 0 ? 'border-top' : ''}">
                    <span class="badge rounded-pill bg-${warna} me-3 mt-1">${i + 1}</span>
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">${esc(q.label)}</div>
                        <div class="text-muted small mb-1">${esc(q.kategori)}</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px"><div class="progress-bar bg-${warna}" style="width:${lebar}%"></div></div>
                            <small class="fw-bold text-${warna}">${f2(q.avg)}</small>
                        </div>
                        <div class="small text-muted mt-1">${f1(q.pct_tinggi)}% peserta menilai bagus${tampilKurang ? ` &middot; ${jumlahKurang} peserta menilai kurang (nilai 1 atau 2)` : ''}</div>
                    </div>
                </div>`;
        };

        const rankCard = (judul, sub, icon, warna, list, tampilKurang) => `
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-1"><i class="fa-solid ${icon} text-${warna} me-2"></i>${judul}</h6>
                        <small class="text-muted d-block mb-2">${sub}</small>
                        ${list.length ? list.map((q, i) => rankItem(q, i, warna, tampilKurang)).join('') : '<div class="text-muted small py-3">Belum ada data.</div>'}
                    </div>
                </div>
            </div>`;

        const statusBox = prog >= target
            ? `<div class="alert alert-success mb-0 small"><i class="fa-solid fa-circle-check me-1"></i>Target <strong>${target}%</strong> tercapai (lebih tinggi <strong>${f1(prog - target)}%</strong> dari target).</div>`
            : `<div class="alert alert-warning mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Target <strong>${target}%</strong> belum tercapai (kurang <strong>${f1(target - prog)}%</strong>).
               Perlu sekitar <strong>${kurang}</strong> peserta puas tambahan agar target tercapai.</div>`;

        const kategoriText = kategori.length
            ? kategori.map(k => `${esc(k.kategori)}: <strong>${f2(k.avg)}</strong>`).join('<br>')
            : '-';

        return `
        <div class="mt-4" id="ppWrapper">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-semibold mb-0"><i class="fa-solid fa-face-smile me-2 text-primary"></i>Ringkasan Penilaian Peserta Tahun ${esc(s.tahun ?? '')}</h6>
                    <small class="text-muted">Peserta dianggap puas bila nilai rata-ratanya ${thr} ke atas (nilai terbaik adalah ${skala})</small>
                </div>
            </div>

            <div class="row g-3 mb-4">
                ${summaryCard('total', 'fa-comments', 'primary', 'Jumlah Peserta Menilai', total, 'Seluruh peserta tahun ini')}
                ${summaryCard('puas', 'fa-thumbs-up', 'success', `Peserta Puas`, puas, `${total ? f1(puas / total * 100) : '0.0'}% dari seluruh peserta`)}
                ${summaryCard('tidak', 'fa-thumbs-down', 'danger', `Peserta Belum Puas`, tidak, `${total ? f1(tidak / total * 100) : '0.0'}% dari seluruh peserta`)}
                ${summaryCard('question', 'fa-list-check', 'info', 'Nilai Tiap Pertanyaan', f2(s.avg_overall), `Nilai rata-rata dari ${skala} &middot; ${qa.length} pertanyaan`)}
                ${summaryCard('monthly', 'fa-calendar-check', 'warning', 'Ringkasan Tiap Bulan', ms.length + ' bulan', 'Peserta puas dan belum puas')}
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h6 class="fw-semibold mb-0" id="ppTableTitle"></h6>
                        <span class="badge bg-secondary" id="ppTableCount"></span>
                    </div>
                    <div class="table-responsive" id="ppTableWrap"></div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                ${rankCard('Nilai Tertinggi', '5 pertanyaan yang paling disukai peserta', 'fa-arrow-trend-up', 'success', topList, false)}
                ${rankCard('Perlu Diperbaiki', '5 pertanyaan dengan nilai paling rendah', 'fa-arrow-trend-down', 'danger', bottomList, true)}
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="fa-solid fa-calculator me-2 text-primary"></i>Cara Menghitung</h6>
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <ol class="small mb-0 ps-3">
                                <li class="mb-2">Kami ambil semua penilaian peserta pada tahun <strong>${esc(s.tahun ?? '-')}</strong> (1 Januari sampai 31 Desember).</li>
                                <li class="mb-2">Nilai setiap peserta didapat dengan menjumlahkan nilai dari <strong>${qa.length} pertanyaan</strong> (daftar ada di bawah), lalu dibagi ${qa.length}. Pertanyaan yang tidak diisi dihitung 0.</li>
                                <li class="mb-2">Peserta dianggap <span class="badge bg-success">Puas</span> bila nilainya <strong>${thr} ke atas</strong>, dan <span class="badge bg-danger">Belum Puas</span> bila di bawah ${thr}.</li>
                                <li class="mb-2"><strong>Persentase peserta puas</strong> = jumlah peserta puas &divide; jumlah seluruh peserta &times; 100%.</li>
                                <li class="mb-0"><strong>Selisih dengan target</strong> = persentase peserta puas &minus; target. Bila target sudah tercapai, selisih dianggap 0.</li>
                            </ol>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="small text-muted mb-2">Contoh dengan data tahun ini</div>
                                <div class="small">
                                    Persentase puas = ${puas} &divide; ${total} &times; 100 = <strong>${f1(prog)}%</strong><br>
                                    Target = <strong>${target}%</strong><br>
                                    Selisih = ${f1(prog)} &minus; ${target} = <strong>${f1(prog - target)}%</strong>
                                </div>
                                <div class="mt-3">${statusBox}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3 border">
                                <div class="small fw-semibold mb-2">Pertanyaan yang dinilai</div>
                                ${daftarPertanyaan || '<div class="small text-muted">-</div>'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                ${insight('fa-building', 'primary', 'Nilai Rata-rata per Kelompok', kategoriText)}
                ${insight('fa-gauge-high', 'warning', 'Rentang Nilai Peserta', `Paling rendah <strong>${f2(s.min_avg)}</strong> &middot; Paling tinggi <strong>${f2(s.max_avg)}</strong><br>Rata-rata <strong>${f2(s.avg_overall)}</strong>`)}
                ${insight('fa-bullseye', 'info', 'Posisi terhadap Target', `Peserta puas <strong>${f1(prog)}%</strong> dari target <strong>${target}%</strong>`)}
                ${insight('fa-users', 'secondary', 'Rata-rata Peserta per Bulan', ms.length ? `<strong>${f1(total / ms.length)}</strong> peserta mengisi penilaian tiap bulan` : '-')}
                ${insight('fa-calendar-plus', 'success', 'Bulan Terbaik', mBest ? `<strong>${namaBulan(mBest.bulan)}</strong> &ndash; ${f1(mBest.pct)}% puas (${mBest.puas} dari ${mBest.total} peserta)` : '-')}
                ${insight('fa-calendar-minus', 'danger', 'Bulan Terendah', mWorst ? `<strong>${namaBulan(mWorst.bulan)}</strong> &ndash; ${f1(mWorst.pct)}% puas (${mWorst.puas} dari ${mWorst.total} peserta)` : '-')}
            </div>

            <div class="row g-4 mb-2">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <h6 class="fw-semibold mb-3">Nilai Rata-rata Tiap Pertanyaan</h6>
                        <div style="height:340px"><canvas id="ppChartQuestion"></canvas></div>
                    </div></div>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body">
                        <h6 class="fw-semibold mb-3">Sebaran Nilai Peserta</h6>
                        <div style="height:340px"><canvas id="ppChartDist"></canvas></div>
                    </div></div>
                </div>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
                        <h6 class="fw-semibold mb-3">Perkembangan Tiap Bulan: Peserta Puas dan Belum Puas</h6>
                        <div style="height:300px"><canvas id="ppChartMonthly"></canvas></div>
                    </div></div>
                </div>
            </div>
        </div>
        <style>
            .pp-card { border-radius: 14px; cursor: pointer; transition: all .2s ease; border: 2px solid transparent !important; }
            .pp-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,.1) !important; }
            .pp-card.active { border-color: #4e73df !important; background: #f4f7ff; }
            .pp-card:not(.active) .pp-hint { opacity: .6; }
            .pp-card.active .pp-hint { display: none; }
        </style>`;
    }

    function initPesertaPuas(data) {
        const d = data.data_detail || {};
        const s = d.feedback_summary;
        if (!s || !document.getElementById('ppTableWrap')) return;

        const thr = s.threshold ?? 3.5;
        const codes = d.question_codes || [];
        const rows = d.feedback_rows || [];
        const qa = d.question_averages || [];
        const ms = d.monthly_summary || [];

        const potong = (t, n) => {
            const x = String(t ?? '');
            return x.length > n ? x.slice(0, n - 1) + '…' : x;
        };

        const badgeStatus = (puas) => puas
            ? '<span class="badge bg-success">Puas</span>'
            : '<span class="badge bg-danger">Belum Puas</span>';
        const avgCell = (v) => `<strong class="${v >= thr ? 'text-success' : 'text-danger'}">${f2(v)}</strong>`;

        const questionColumns = codes.map(c => {
            const q = qa.find(x => x.kode === c);
            const full = q ? q.label : c;
            return { title: `<span title="${esc(full)}">${esc(potong(full, 30))}</span>`, className: 'text-center' };
        });

        const feedbackCols = [
            { title: 'No' },
            { title: 'Tanggal' },
            ...questionColumns,
            { title: 'Nilai Rata-rata', className: 'text-center' },
            { title: 'Status', className: 'text-center' },
            { title: 'Komentar' }
        ];
        const toFeedbackData = (list) => list.map((r, i) => [
            i + 1,
            r.tanggal,
            ...r.scores.map(v => v === null ? '<span class="text-muted">-</span>' : String(Number(v))),
            avgCell(r.avg),
            badgeStatus(r.puas),
            r.komentar ? `<small>${esc(r.komentar)}</small>` : '<span class="text-muted">-</span>'
        ]);

        const views = {
            total: {
                title: '<i class="fa-solid fa-comments text-primary me-2"></i>Semua Penilaian Peserta',
                columns: feedbackCols,
                data: () => toFeedbackData(rows)
            },
            puas: {
                title: `<i class="fa-solid fa-thumbs-up text-success me-2"></i>Peserta Puas (nilai ${thr} ke atas)`,
                columns: feedbackCols,
                data: () => toFeedbackData(rows.filter(r => r.puas))
            },
            tidak: {
                title: `<i class="fa-solid fa-thumbs-down text-danger me-2"></i>Peserta Belum Puas (nilai di bawah ${thr})`,
                columns: feedbackCols,
                data: () => toFeedbackData(rows.filter(r => !r.puas))
            },
            question: {
                title: '<i class="fa-solid fa-list-check text-info me-2"></i>Nilai Rata-rata Tiap Pertanyaan',
                columns: [
                    { title: 'Kelompok' },
                    { title: 'Pertanyaan' },
                    { title: 'Nilai Rata-rata', className: 'text-center' },
                    { title: 'Nilai Terendah', className: 'text-center' },
                    { title: 'Nilai Tertinggi', className: 'text-center' },
                    { title: 'Jumlah Peserta yang Menjawab', className: 'text-center' },
                    { title: `% Peserta Menilai Bagus (${thr} ke atas)`, className: 'text-center' },
                    { title: 'Status', className: 'text-center' }
                ],
                data: () => qa.map(q => [
                    esc(q.kategori), esc(q.label),
                    avgCell(q.avg), q.min, q.max, q.n ?? '-', f1(q.pct_tinggi) + '%', badgeStatus(q.avg >= thr)
                ])
            },
            monthly: {
                title: '<i class="fa-solid fa-calendar-check text-warning me-2"></i>Ringkasan Tiap Bulan',
                columns: [
                    { title: 'Bulan' },
                    { title: 'Jumlah Peserta', className: 'text-center' },
                    { title: 'Peserta Puas', className: 'text-center' },
                    { title: 'Peserta Belum Puas', className: 'text-center' },
                    { title: 'Nilai Rata-rata', className: 'text-center' },
                    { title: '% Peserta Puas', className: 'text-center' }
                ],
                data: () => ms.map(m => [
                    `<strong>${namaBulan(m.bulan)}</strong>`,
                    m.total,
                    `<span class="text-success fw-semibold">${m.puas}</span>`,
                    `<span class="text-danger fw-semibold">${m.tidak}</span>`,
                    avgCell(m.avg),
                    `<div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:8px"><div class="progress-bar ${m.pct >= (s.target || 0) ? 'bg-success' : 'bg-warning'}" style="width:${m.pct}%"></div></div><small class="fw-semibold">${f1(m.pct)}%</small></div>`
                ])
            }
        };

        function renderView(key) {
            const v = views[key];
            if (!v) return;

            if ($.fn.DataTable.isDataTable('#ppTable')) $('#ppTable').DataTable().destroy();
            $('#ppTableWrap').html('<table id="ppTable" class="table table-sm table-hover table-bordered align-middle w-100"></table>');

            const tableData = v.data();
            $('#ppTableTitle').html(v.title);
            $('#ppTableCount').text(tableData.length + ' baris');

            const dt = $('#ppTable').DataTable({
                data: tableData,
                columns: v.columns,
                pageLength: 10,
                lengthChange: false,
                deferRender: true,
                scrollX: true,
                language: {
                    search: 'Cari:',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Tidak ada data yang tersedia',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: { next: 'Selanjutnya', previous: 'Sebelumnya' }
                }
            });
            setTimeout(() => dt.columns.adjust(), 50);

            $('.pp-card').removeClass('active');
            $(`.pp-card[data-view="${key}"]`).addClass('active');
        }

        $('#bodyContentDetailTarget').off('click.pp keydown.pp')
            .on('click.pp', '.pp-card', function () { renderView($(this).data('view')); })
            .on('keydown.pp', '.pp-card', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); renderView($(this).data('view')); }
            });

        renderView('total');

        if (typeof Chart === 'undefined') return;
        const GREEN = 'rgba(40,207,180,.75)', RED = 'rgba(254,124,150,.75)';

        const cq = document.getElementById('ppChartQuestion');
        if (cq) {
            new Chart(cq, {
                type: 'bar',
                data: {
                    labels: qa.map(q => potong(q.label, 32)),
                    datasets: [{ label: 'Nilai rata-rata', data: qa.map(q => q.avg), backgroundColor: qa.map(q => q.avg >= thr ? GREEN : RED), borderRadius: 6 }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { title: (i) => qa[i[0].dataIndex].label } }
                    },
                    scales: { x: { min: 0, max: 4 } }
                }
            });
        }

        const cd = document.getElementById('ppChartDist');
        if (cd) {
            const dist = d.rating_distribution || {};
            const labels = Object.keys(dist);
            new Chart(cd, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label: 'Jumlah Peserta', data: Object.values(dist), backgroundColor: labels.map(l => (l.startsWith('3.5') || l === '4.0') ? GREEN : RED), borderRadius: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        const cm = document.getElementById('ppChartMonthly');
        if (cm) {
            new Chart(cm, {
                type: 'bar',
                data: {
                    labels: ms.map(m => namaBulan(m.bulan)),
                    datasets: [
                        { label: 'Puas', data: ms.map(m => m.puas), backgroundColor: GREEN, borderRadius: 4, stack: 's' },
                        { label: 'Belum Puas', data: ms.map(m => m.tidak), backgroundColor: RED, borderRadius: 4, stack: 's' },
                        { label: '% Peserta Puas', data: ms.map(m => m.pct), type: 'line', borderColor: '#4e73df', backgroundColor: 'transparent', tension: .35, yAxisID: 'y1', pointRadius: 4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Jumlah peserta' } },
                        y1: { position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false }, ticks: { callback: v => v + '%' } },
                        x: { stacked: true }
                    }
                }
            });
        }
    }

    function buildContents(data, mode) {
        const dd = data.data_detail || {};
        const cond = data.condition || '';
        const isPesertaPuas = isPesertaPuasRoute(cond);
        const karyawan = buildKaryawan(data);
        const pesertaPuas = isPesertaPuas ? buildPesertaPuasContent(data) : '';

        return {
            isPesertaPuas,
            karyawanList: karyawan.list,
            karyawanHtml: karyawan.html,
            trafikSales: buildTrafikSales(data, dd, cond, mode),
            kontribusiSales: buildKontribusiSales(dd, cond, mode),
            pieChart: buildPie(data, dd, cond, mode, karyawan.list),
            statisticChart: buildStatistic(data, dd, cond, mode, pesertaPuas, isPesertaPuas),
            kalenderInstruktur: buildKalender(dd),
            pesertaPuas
        };
    }

    function bindTargetInputs(mode) {
        document.querySelectorAll('.target-input').forEach(input => {
            if (!input) return;
            let timeout = null;
            input.addEventListener('input', function () {
                const el = this;
                const row = el.closest('tr');
                if (!row) return;
                const spinner = row.querySelector('.loading-spinner');
                const feedback = row.querySelector('.update-feedback');
                clearTimeout(timeout);
                if (feedback) feedback.style.display = 'none';
                timeout = setTimeout(() => {
                    const idDetailPerson = el.dataset.idDetail;
                    const kodeKaryawan = el.dataset.kodeKaryawan;
                    const newTarget = (el.value || '').toString().replace(/\./g, '');
                    if (!idDetailPerson) { el.classList.add('is-invalid'); return; }
                    if (spinner) spinner.style.display = 'inline-block';
                    el.disabled = true;
                    $.ajax({
                        url: CFG[mode].updateTargetUrl(),
                        method: 'POST',
                        data: { _token: global.KPI_CONFIG.csrfToken, id_detailPerson: idDetailPerson, kode_karyawan: kodeKaryawan, presentase_kemampuan: newTarget },
                        success: function (response) {
                            if (spinner) spinner.style.display = 'none';
                            el.disabled = false;
                            if (feedback) { feedback.style.display = 'inline-block'; setTimeout(() => { feedback.style.display = 'none'; }, 2000); }
                            el.classList.remove('is-invalid');
                            el.classList.add('is-valid');
                            setTimeout(() => el.classList.remove('is-valid'), 2000);
                            if (response?.data) {
                                const percentageCell = row.querySelector('td:nth-child(5)');
                                const statusCell = row.querySelector('td:nth-child(6)');
                                if (percentageCell && response.data.percentage) percentageCell.innerHTML = `<strong class="${response.data.status === 'achieved' ? 'text-success' : 'text-warning'}">${f1(response.data.percentage)}%</strong>`;
                                if (statusCell && response.data.status) {
                                    const statusClass = response.data.status === 'achieved' ? 'badge-success' : 'badge-warning';
                                    statusCell.innerHTML = `<span class="badge ${statusClass}">${response.data.status.toUpperCase()}</span>`;
                                }
                            }
                        },
                        error: function () {
                            if (spinner) spinner.style.display = 'none';
                            el.disabled = false;
                            el.classList.add('is-invalid');
                        }
                    });
                }, 1000);
            });
            input.addEventListener('blur', function () {
                const value = (this.value || '').toString().replace(/\./g, '');
                if (value) this.value = Number(value).toLocaleString('id-ID');
            });
            input.addEventListener('focus', function () {
                const value = (this.value || '').toString().replace(/\./g, '');
                if (value) this.value = value;
            });
        });
    }

    function bindGapForm() {
        $(document).off('submit', '#formGapKompetensi').on('submit', '#formGapKompetensi', function (e) {
            e.preventDefault();
            $.ajax({
                url: global.KPI_CONFIG.updateGapKompetensi,
                method: 'POST',
                data: $(this).serialize(),
                success: function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Berhasil diupdate.', timer: 2000, showConfirmButton: false }).then(() => {
                            $('#detailTargetModal').modal('hide');
                            if (typeof global.loadContentForm === 'function') global.loadContentForm();
                        });
                    }
                },
                error: function (err) {
                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Gagal!', html: err.responseJSON?.message || 'Terjadi kesalahan' });
                }
            });
        });
    }

    function bindStatistic(dd) {
        const monthlyData = dd.monthly_data || {};
        const dailyData = dd.daily_breakdown_per_month || {};
        let statisticChart = null;
        const statisticCtx = document.getElementById('StatisticChart');

        const renderStatistic = (labels, values, label) => {
            if (!statisticCtx || typeof Chart === 'undefined') return;
            if (statisticChart) statisticChart.destroy();
            const safeValues = (values || []).map(v => Number(v) || 0);
            const maxValue = safeValues.length > 0 ? Math.max(...safeValues) : 100;
            statisticChart = new Chart(statisticCtx, {
                type: 'line',
                data: { labels: labels || [], datasets: [{ label: label || 'Data', data: safeValues, borderColor: '#4e73df', backgroundColor: 'rgba(78, 115, 223, 0.1)', tension: 0.4, fill: true }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, suggestedMax: maxValue + 3, ticks: { count: 6, precision: 0, callback: v => Math.round(v) } } } }
            });
        };

        const monthLabels = Object.keys(monthlyData).map(namaBulan);
        const monthValues = Object.values(monthlyData).map(v => Number(v) || 0);
        renderStatistic(monthLabels, monthValues, 'Rata-rata');

        $('#filterType').off('change').on('change', function () {
            if (this.value === 'month') {
                $('#filterMonth').removeClass('d-none').empty().append('<option value="">Pilih Bulan</option>');
                Object.keys(dailyData).forEach(monthKey => { if (monthKey) $('#filterMonth').append(`<option value="${monthKey}">${namaBulan(monthKey)}</option>`); });
                if (statisticChart) statisticChart.destroy();
            } else {
                $('#filterMonth').addClass('d-none');
                renderStatistic(monthLabels, monthValues, 'Rata-rata');
            }
        });

        $('#filterMonth').off('change').on('change', function () {
            const selectedMonth = this.value;
            if (!selectedMonth || !dailyData[selectedMonth]) return;
            const dayLabels = Object.keys(dailyData[selectedMonth]).map(d => (d || '').substring(8));
            const dayValues = Object.values(dailyData[selectedMonth]).map(v => Number(v) || 0);
            renderStatistic(dayLabels, dayValues, `Tanggal ${namaBulan(selectedMonth)}`);
        });
    }

    function afterRender(data, mode, contents) {
        const dd = data.data_detail || {};

        if (contents && contents.isPesertaPuas) initPesertaPuas(data);

        const doughnutCtx = document.getElementById('MyChartDoughtnut');
        if (doughnutCtx && typeof Chart !== 'undefined') {
            const existing = Chart.getChart ? Chart.getChart(doughnutCtx) : null;
            if (existing) existing.destroy();
            const pie = dd.pie_chart || { above: 0, below: 0 };
            const labels = dd.is_inverse === true
                ? ['Dalam batas target', 'Melebihi target']
                : ['Above', 'Below'];
            new Chart(doughnutCtx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{ label: 'Jumlah', data: [pie.above ?? 0, pie.below ?? 0], backgroundColor: ['#B66DFF', '#FE7C96'], hoverOffset: 4 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } } } }
            });
        }

        bindTargetInputs(mode);
        bindGapForm();
        bindStatistic(dd);

        document.querySelectorAll('.btn-toggle-desc').forEach(btn => {
            if (!btn) return;
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const textEl = targetId ? document.getElementById(targetId) : null;
                if (!textEl) return;
                if (textEl.classList.contains('expanded')) {
                    textEl.classList.remove('expanded');
                    this.innerText = 'Lihat Selengkapnya';
                } else {
                    textEl.classList.add('expanded');
                    this.innerText = 'Sembunyikan';
                }
            });
        });
    }

    function skeleton(mode) {
        if (mode === 'index') {
            return `
            <div class="skeleton-modal-content">
                <div class="skeleton-modal-header">
                    <div>
                        <div class="skeleton" style="width: 200px; height: 24px; margin-bottom: 8px;"></div>
                        <div class="skeleton" style="width: 120px; height: 14px;"></div>
                    </div>
                    <div class="skeleton" style="width: 32px; height: 32px; border-radius: 8px;"></div>
                </div>
                <div class="modal-body pt-2">
                    <div class="skeleton-progress-section">
                        <div class="skeleton-progress-header">
                            <div class="skeleton-badge skeleton"></div>
                            <div class="skeleton" style="width: 150px; height: 14px;"></div>
                        </div>
                        <div class="skeleton-progress-stats">
                            <div class="skeleton-stat"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            <div class="skeleton-stat"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            <div class="skeleton-stat"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                        </div>
                        <div class="skeleton-progress-bar skeleton"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="skeleton-info-card">
                                <div class="skeleton-title skeleton"></div>
                                <div class="skeleton-info-row"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                                <div class="skeleton-info-row"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                                <div class="skeleton-info-row"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="skeleton-info-card">
                                <div class="skeleton-title skeleton"></div>
                                <div class="skeleton-employee-list">
                                    <div class="skeleton-employee-item"><div class="skeleton-avatar skeleton"></div><div class="skeleton-employee-info"><div class="skeleton-employee-name skeleton"></div><div class="skeleton-employee-position skeleton"></div></div></div>
                                    <div class="skeleton-employee-item"><div class="skeleton-avatar skeleton"></div><div class="skeleton-employee-info"><div class="skeleton-employee-name skeleton"></div><div class="skeleton-employee-position skeleton"></div></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-3 border rounded-4">
                        <div class="skeleton-chart-container">
                            <div class="skeleton-chart-title skeleton"></div>
                            <div class="skeleton-chart-area skeleton"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <div class="skeleton" style="width: 100px; height: 38px; border-radius: 8px;"></div>
                </div>
            </div>`;
        }
        return `
            <div class="skeleton-modal-content p-4">
                <div class="skeleton-modal-header">
                    <div>
                        <div class="skeleton" style="width: 250px; height: 28px; margin-bottom: 8px;"></div>
                        <div class="skeleton" style="width: 120px; height: 14px;"></div>
                    </div>
                    <div class="skeleton" style="width: 32px; height: 32px; border-radius: 50%;"></div>
                </div>
                <div class="card shadow h-100 border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="skeleton" style="width: 20%; height: 24px;"></div>
                            <div class="skeleton" style="width: 20%; height: 24px;"></div>
                        </div>
                        <div class="skeleton-modal-stats">
                            <div class="skeleton-modal-stat-item"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            <div class="skeleton-modal-stat-item"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                            <div class="skeleton-modal-stat-item"><div class="skeleton-label skeleton"></div><div class="skeleton-value skeleton"></div></div>
                        </div>
                        <div class="skeleton mb-4" style="width: 100%; height: 18px; border-radius: 9px;"></div>
                        <div class="skeleton-modal-cards">
                            <div class="skeleton-modal-card skeleton"></div>
                            <div class="skeleton-modal-card skeleton"></div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card shadow border-0">
                            <div class="card-body">
                                <div class="skeleton mb-3" style="width: 40%; height: 20px;"></div>
                                <div class="skeleton" style="width: 100%; height: 150px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card shadow border-0">
                            <div class="card-body d-flex flex-column">
                                <div class="skeleton mb-3" style="width: 50%; height: 20px;"></div>
                                <div class="skeleton flex-grow-1" style="width: 100%; height: 200px; border-radius: 50%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    global.isPesertaPuasRoute = isPesertaPuasRoute;
    global.buildPesertaPuasContent = buildPesertaPuasContent;
    global.initPesertaPuas = initPesertaPuas;

    global.KpiDetail = {
        skeleton,
        buildContents,
        afterRender,
        namaBulan,
        buildGapKompetensiForm,
        isPesertaPuasRoute,
        buildPesertaPuasContent,
        initPesertaPuas
    };
})(window);