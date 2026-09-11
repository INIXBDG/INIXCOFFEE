(function() {
    'use strict';

    function hideSkeleton(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
            el.classList.add('hidden');
        }
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = src;
            s.async = true; 
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    let chartJsLoaded = false;
    let sweetalertLoaded = false;
    let chartJsPromise = null;
    let sweetalertPromise = null;

    function ensureChartJs() {
        if (chartJsLoaded) return Promise.resolve();
        if (!chartJsPromise) {
            chartJsPromise = loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js')
                .then(() => { chartJsLoaded = true; });
        }
        return chartJsPromise;
    }

    function ensureSweetAlert() {
        if (sweetalertLoaded) return Promise.resolve();
        if (!sweetalertPromise) {
            sweetalertPromise = loadScript('https://cdn.jsdelivr.net/npm/sweetalert2@11')
                .then(() => { sweetalertLoaded = true; });
        }
        return sweetalertPromise;
    }

    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => ensureChartJs(), { timeout: 2000 });
    } else {
        setTimeout(() => ensureChartJs(), 1500);
    }

    let kpiChart = null;
    let kpiPieChart = null;
    let exportCtx = {};
    let exportModal = null;

    function getExportModal() {
        if (!exportModal) {
            exportModal = new bootstrap.Modal(document.getElementById('exportFilterModal'));
        }
        return exportModal;
    }

    function initYearOptions() {
        const sel = document.getElementById('filterTahun');
        if (!sel) return;
        const cur = new Date().getFullYear();
        sel.innerHTML = '<option value="">Semua Tahun</option>';
        for (let y = cur; y >= cur - 4; y--) {
            sel.insertAdjacentHTML('beforeend', `<option value="${y}">${y}</option>`);
        }
    }

    function getInitials(nama) {
        return (nama || '?').split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
    }

    function classifyEmployee(progress, currentMonth) {
        const isYearEnd = currentMonth === 12;
        if (isYearEnd) {
            if (progress >= 75) return { cls: 'status-top', av: 'av-green', val: 'col-green', badge: '<span class="badge bg-success">Top</span>' };
            if (progress >= 50) return { cls: 'status-process', av: 'av-amber', val: 'col-amber', badge: '<span class="badge bg-warning text-dark">Cukup</span>' };
            return { cls: 'status-low', av: 'av-red', val: 'col-red', badge: '<span class="badge bg-danger">Kurang</span>' };
        }
        if (progress === 0) {
            if (currentMonth <= 3) return { cls: 'status-new', av: 'av-indigo', val: 'col-indigo', badge: '<span class="badge bg-primary">Baru</span>' };
            return { cls: 'status-low', av: 'av-red', val: 'col-red', badge: '<span class="badge bg-danger">Perlu Bimbingan</span>' };
        }
        if (progress >= 75) return { cls: 'status-top', av: 'av-green', val: 'col-green', badge: '<span class="badge bg-success">On Track</span>' };
        return { cls: 'status-process', av: 'av-amber', val: 'col-amber', badge: '<span class="badge bg-warning text-dark">Dalam Proses</span>' };
    }

    function loadData() {
        // 1. PAKSA TAMPILAN SKELETON, SEMBUNYIKAN DATA ASLI
        const skeletonView = document.getElementById('overview-skeleton-view');
        const realView = document.getElementById('overview-real-view');
        
        if (skeletonView) skeletonView.classList.remove('d-none');
        if (realView) {
            realView.classList.add('d-none');
            realView.classList.remove('fade-in-content'); // Reset animasi
        }

        const divisi = document.getElementById('selectDivisi').value || '';
        const tahun = document.getElementById('selectTahun').value || window.KPI_CONFIG.currentYear;
        const idKaryawan = document.getElementById('inputIdKaryawan').value || window.KPI_CONFIG.userId;

        const namaUser = window.KPI_CONFIG.userName || 'Anda';
        if (divisi) {
            document.getElementById('overviewTitle').innerHTML = `Overview Personal: ${namaUser} <span class="text-muted fw-normal">| ${divisi}</span> ${tahun}`;
            document.getElementById('employeeTitle').innerHTML = `<i class="fa-solid fa-user" aria-hidden="true"></i> Data KPI Anda`;
        } else {
            document.getElementById('overviewTitle').textContent = `Overview Divisi ${tahun}`;
            document.getElementById('employeeTitle').innerHTML = `<i class="fa-solid fa-users" aria-hidden="true"></i> Karyawan di Departemen`;
        }

        const formData = new FormData(document.getElementById('FormFilter'));
        if (!formData.has('id_karyawan') && idKaryawan) {
            formData.append('id_karyawan', idKaryawan);
        }
        const params = new URLSearchParams(formData);

        fetch(`${window.KPI_CONFIG.getOverview}?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            })
            .then(data => {
                // 2. DATA SUDAH SIAP: TUKAR TAMPILAN
                if (skeletonView) skeletonView.classList.add('d-none');
                if (realView) {
                    realView.classList.remove('d-none');
                    // Trigger reflow agar animasi berjalan
                    void realView.offsetWidth; 
                    realView.classList.add('fade-in-content');
                }

                // 3. UPDATE DOM DENGAN DATA ASLI
                updateStats(data);
                updateEmployeeGrid(data.karyawan_departemen);
                updateLowPerf(data.karyawan_departemen);
                updateCharts(data.statistik_karyawan, data.distribusi_nilai);
                updateTargetTable(data.daftar_target_kpi);
            })
            .catch(err => {
                console.error('Load error:', err);
                // Tampilkan error di dalam real view jika gagal
                if (skeletonView) skeletonView.classList.add('d-none');
                if (realView) realView.classList.remove('d-none');
                
                ensureSweetAlert().then(() => {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat data. Coba lagi.' });
                });
            });
    }

    function updateStats(data) {
        const totalEl = document.getElementById('totalTarget');
        const rataEl = document.getElementById('rataProgress');
        const aktifEl = document.getElementById('kpiAktif');
        const selesaiEl = document.getElementById('kpiSelesai');

        if (totalEl) totalEl.textContent = (data.total_target || 0) + ' Target';
        if (rataEl) rataEl.textContent = (data.rata_rata_progress || 0).toFixed(1) + '%';
        if (aktifEl) aktifEl.textContent = data.kpi_aktif || 0;
        if (selesaiEl) selesaiEl.textContent = data.kpi_selesai || 0;
        
        // Hapus panggilan hideSkeleton lama karena sudah ditangani oleh swap container
    }

    function updateEmployeeGrid(employees) {
        const el = document.getElementById('employeeList');
        if (!employees || !employees.length) {
            el.innerHTML = `<div class="empty-state"><i class="fa-solid fa-users-slash" aria-hidden="true"></i><p>Tidak ada data karyawan</p></div>`;
            return;
        }
        const month = new Date().getMonth() + 1;
        const year = new Date().getFullYear();
        let html = '<div class="emp-grid">';

        employees.forEach(emp => {
            const progressNumber = parseFloat(emp.rata_rata_progress ?? 0);
            const progressDisplay = progressNumber.toFixed(1);
            const info = classifyEmployee(progressNumber, month);
            const initial = getInitials(emp.nama);

            html += `
                <div class="emp-card ${info.cls}" data-id="${emp.id_karyawan}">
                    <div class="emp-avatar ${info.av}">${initial}</div>
                    <div class="emp-info">
                        <div class="emp-name">${emp.nama}</div>
                        <div class="emp-jabatan">${emp.jabatan}</div>
                        <div class="emp-progress-wrapper">
                            <div class="emp-progress-val ${info.val}">${progressDisplay}%</div>
                            <div class="emp-badge">${info.badge}</div>
                        </div>
                    </div>
                    <div class="emp-actions">
                        <button class="btn btn-sm btn-outline-primary emp-export-btn" type="button" data-id="${emp.id_karyawan}" data-tahun="${year}">
                            <i class="fas fa-download" aria-hidden="true"></i>
                        </button>
                        <div class="emp-export-menu" style="display:none;">
                            <a class="dropdown-item btn-export-emp" href="#" data-type="excel" data-id="${emp.id_karyawan}" data-tahun="${year}">
                                <i class="fas fa-file-excel text-success me-1" aria-hidden="true"></i> Excel
                            </a>
                            <a class="dropdown-item btn-export-emp" href="#" data-type="pdf" data-id="${emp.id_karyawan}" data-tahun="${year}">
                                <i class="fas fa-file-pdf text-danger me-1" aria-hidden="true"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>`;
        });

        html += '</div>';
        el.innerHTML = html;
    }

    function updateLowPerf(employees) {
        const el = document.getElementById('lowPerformanceList');
        const low = (employees || []).filter(e => (e.rata_rata_progress || 0) < 50);
        
        if (!low.length) {
            el.innerHTML = `<div class="empty-state"><i class="fa-solid fa-circle-check text-success" aria-hidden="true"></i><p>Semua karyawan dalam performa baik</p></div>`;
            return;
        }
        
        el.innerHTML = low.map(emp => `
            <div class="low-perf-item d-flex justify-content-between align-items-center mb-2">
                <div>
                    <div class="lp-name">${emp.nama}</div>
                    <div class="lp-jabatan">${emp.jabatan}</div>
                </div>
                <div class="lp-val">${(emp.rata_rata_progress || 0).toFixed(1)}%</div>
            </div>`).join('');
    }

    function updateCharts(empStats, distribution) {
        ensureChartJs().then(() => {
            if (kpiChart) { kpiChart.destroy(); kpiChart = null; }
            if (kpiPieChart) { kpiPieChart.destroy(); kpiPieChart = null; }

            const names = (empStats || []).map(i => i.nama);
            const progress = (empStats || []).map(i => (i.rata_rata_progress || 0).toFixed(1));
            const targets = (empStats || []).map(i => i.total_target || 0);

            const barColors = progress.map(p => p >= 75 ? 'rgba(16,185,129,.65)' : p >= 50 ? 'rgba(245,158,11,.65)' : 'rgba(239,68,68,.65)');
            const borderColors = progress.map(p => p >= 75 ? '#10b981' : p >= 50 ? '#f59e0b' : '#ef4444');

            // Render Chart Bar
            kpiChart = new Chart(document.getElementById('kpiChart'), {
                type: 'bar',
                data: {
                    labels: names,
                    datasets: [{
                        label: 'Progress (%)',
                        data: progress,
                        backgroundColor: barColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        borderRadius: 8,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Total Target',
                        data: targets,
                        type: 'line',
                        borderColor: '#6366f1',
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        pointBackgroundColor: '#6366f1',
                        pointRadius: 5,
                        fill: false,
                        yAxisID: 'y1',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, padding: 15 } },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,.9)',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ctx.dataset.label === 'Progress (%)' ? `Progress: ${ctx.parsed.y}%` : `Total Target: ${ctx.parsed.y}`
                            }
                        }
                    },
                    scales: {
                        y: { type: 'linear', position: 'left', min: 0, max: 100, ticks: { callback: v => v + '%' }, grid: { drawOnChartArea: false }, title: { display: true, text: 'Progress (%)', font: { weight: '600' } } },
                        y1: { type: 'linear', position: 'right', min: 0, grid: { drawOnChartArea: false }, title: { display: true, text: 'Total Target', font: { weight: '600' } } },
                        x: { grid: { display: false }, title: { display: true, text: 'Karyawan', font: { weight: '600' } } }
                    }
                }
            });

            // Render Chart Pie
            const pieLabels = Object.keys(distribution || {});
            const pieData = Object.values(distribution || {});

            kpiPieChart = new Chart(document.getElementById('kpiPieChart'), {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,.9)',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total ? Math.round((ctx.parsed / total) * 100) : 0;
                                    return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Hapus panggilan hideSkeleton lama, karena container sudah ditukar
        });
    }
    let targetTableInstance = null;

    function updateTargetTable(targets) {
        const $table = $('#targetTable');
        const tbody = document.querySelector('#targetTableBody');

        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }

        tbody.innerHTML = '';

        if (!targets || !targets.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                        Tidak ada data target
                    </td>
                </tr>`;
            return;
        }

        const tableData = targets.map(t => {
            const pct = parseFloat((t.progress ?? 0).toFixed(2));
            let barColor, badgeCls;
            
            switch (t.status) {
                case 'Selesai': barColor = '#10b981'; badgeCls = 'bg-success'; break;
                case 'Gagal': barColor = '#343a40'; badgeCls = 'bg-dark'; break;
                case 'Sedang Berjalan': barColor = '#0d6efd'; badgeCls = 'bg-primary'; break;
                case 'Belum Mulai': 
                default: barColor = '#6c757d'; badgeCls = 'bg-secondary'; break;
            }

            return [
                `<strong>${t.judul}</strong>`,
                t.periode,
                t.target,
                `<div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height:8px;border-radius:4px;">
                        <div class="progress-bar" style="width:${pct}%;background:${barColor};"></div>
                    </div>
                    <small class="fw-semibold">${pct}%</small>
                </div>`,
                `<span class="badge ${badgeCls}">${t.status}</span>`
            ];
        });

        targetTableInstance = $table.DataTable({
            data: tableData,
            columns: [
                { title: 'Judul' },
                { title: 'Periode' },
                { title: 'Target' },
                { title: 'Progress' },
                { title: 'Status' }
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                emptyTable: "Tidak ada data yang tersedia"
            },
            pageLength: 10, 
            lengthChange: false, 
            ordering: true,
            responsive: true,
            deferRender: true, 
            scrollCollapse: false
        });
    }

    function handleDeptExport(event, type) {
        event.preventDefault();
        const divisi = document.getElementById('selectDivisi').value;
        const tahun = document.getElementById('selectTahun').value || window.KPI_CONFIG.currentYear;
        const idKaryawan = document.getElementById('inputIdKaryawan').value || window.KPI_CONFIG.userId;

        if (!divisi) {
            ensureSweetAlert().then(() => {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih Departemen terlebih dahulu.' });
            });
            return;
        }

        const baseUrl = type === 'excel' ? window.KPI_CONFIG.exportDeptExcel : window.KPI_CONFIG.exportDeptPdf;
        window.location.href = `${baseUrl}?divisi=${encodeURIComponent(divisi)}&tahun=${tahun}&id_karyawan=${idKaryawan}`;
    }

    function handleEmpExport(type, id, tahun) {
        exportCtx = { type, id, tahun };
        document.getElementById('filterTahun').value = tahun;
        document.getElementById('filterPeriode').value = 'all';
        document.getElementById('filterQuarterWrap').classList.add('d-none');
        getExportModal().show();
    }

    // =========================================================================
    // 4. EVENT DELEGATION (Performa Tinggi untuk Konten Dinamis)
    // =========================================================================
    // Menggunakan satu event listener di document, bukan menempelkan listener 
    // ke setiap tombol secara individual. Ini menghemat memori secara signifikan.
    
    document.getElementById('filterPeriode').addEventListener('change', function() {
        document.getElementById('filterQuarterWrap').classList.toggle('d-none', this.value !== 'kuartalan');
    });

    document.getElementById('btnApplyExport').addEventListener('click', function() {
        const params = new URLSearchParams({
            id_karyawan: exportCtx.id,
            tahun: document.getElementById('filterTahun').value || exportCtx.tahun,
            periode: document.getElementById('filterPeriode').value,
            quarter: document.getElementById('filterQuarterWrap').classList.contains('d-none') ? '' : document.getElementById('filterQuarter').value
        });
        const baseUrl = exportCtx.type === 'pdf' ? window.KPI_CONFIG.exportMonitoringPdf : window.KPI_CONFIG.exportMonitoringExcel;
        window.open(`${baseUrl}?${params}`);
        getExportModal().hide();
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.filter-bar .dropdown')) {
            document.querySelectorAll('.filter-bar .dropdown-menu').forEach(m => m.classList.remove('show'));
        }

        if (!e.target.closest('.emp-actions')) {
            document.querySelectorAll('.emp-export-menu').forEach(m => m.style.display = 'none');
        }

        const deptBtn = e.target.closest('.btn-export-dept');
        if (deptBtn) { e.preventDefault(); handleDeptExport(e, deptBtn.dataset.type); return; }

        const exportBtn = e.target.closest('.emp-export-btn');
        if (exportBtn) {
            e.stopPropagation();
            const menu = exportBtn.nextElementSibling;
            const isOpen = menu.style.display === 'block';
            document.querySelectorAll('.emp-export-menu').forEach(m => m.style.display = 'none');
            menu.style.display = isOpen ? 'none' : 'block';
            return;
        }

        const empExport = e.target.closest('.btn-export-emp');
        if (empExport) {
            e.preventDefault();
            e.stopPropagation();
            document.querySelectorAll('.emp-export-menu').forEach(m => m.style.display = 'none');
            handleEmpExport(empExport.dataset.type, empExport.dataset.id, empExport.dataset.tahun);
            return;
        }

        const empCard = e.target.closest('.emp-card');
        if (empCard && !e.target.closest('.emp-actions')) {
            const id = empCard.dataset.id;
            if (id) window.location.href = `/kpi-data/overview/index/personal/${id}`;
        }
    });

    const deptToggle = document.querySelector('.filter-bar .dropdown-toggle');
    if (deptToggle) {
        deptToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const menu = this.nextElementSibling;
            document.querySelectorAll('.filter-bar .dropdown-menu').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        });
    }

    document.getElementById('FormFilter').addEventListener('submit', function(e) {
        e.preventDefault();
        loadData();
    });

    initYearOptions();

    const divisiSelect = document.getElementById('selectDivisi');
    if (divisiSelect && window.KPI_CONFIG.userDivisi) {
        divisiSelect.value = window.KPI_CONFIG.userDivisi;
    }

    const deptExportDropdown = document.querySelector('.filter-bar .dropdown-toggle');
    if (deptExportDropdown) {
        deptExportDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const menu = this.nextElementSibling;
            const isOpen = menu.classList.contains('show');
            
            document.querySelectorAll('.filter-bar .dropdown-menu').forEach(m => {
                m.classList.remove('show');
            });
            
            if (!isOpen) {
                menu.classList.add('show');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.filter-bar .dropdown')) {
            document.querySelectorAll('.filter-bar .dropdown-menu.show').forEach(m => {
                m.classList.remove('show');
            });
        }
    });

    // Jalankan load data pertama kali
    loadData();
})();