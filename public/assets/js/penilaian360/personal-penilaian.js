let personalGlobalData = [];
let personalSelectedJenis = null;
let personalSelectedEvaluator = null;
let personalEvaluatedName = '';
let personalCurrentTahun = null;

document.addEventListener('DOMContentLoaded', () => {
    loadData();

    const selectPeriode = document.getElementById('selectPeriode');
    if (selectPeriode) {
        selectPeriode.addEventListener('change', function() {
            personalCurrentTahun = this.value;
            loadData(personalCurrentTahun);
        });
    }
});

function loadData(tahun = null) {
    document.getElementById('personal-skeleton-container').style.display = 'block';
    document.getElementById('personal-real-container').style.display = 'none';

    let url = window.personalConfig.routes.getData;
    if (tahun) {
        url += `?tahun=${encodeURIComponent(tahun)}`;
    }
    
    document.getElementById('groupButtonJenisPenilaian').innerHTML = `
        <div class="personal-loading-state w-100">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Memuat jenis penilaian...</p>
        </div>
    `;
    document.getElementById('groupButtonEvaluator').innerHTML = `
        <div class="personal-empty-state w-100">
            <i class="fa-solid fa-hourglass-start"></i>
            <p>Memuat evaluator...</p>
        </div>
    `;
    document.getElementById('formContainer').innerHTML = `
        <div class="personal-loading-state">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <p>Memuat form penilaian...</p>
        </div>
    `;

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (!response || response.message) {
                document.getElementById('groupButtonJenisPenilaian').innerHTML = `
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-inbox"></i>
                        <p>${response.message ?? 'Data kosong'}</p>
                    </div>
                `;
                document.getElementById('groupButtonEvaluator').innerHTML = `
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-minus"></i>
                        <p>-</p>
                    </div>
                `;
                document.getElementById('formContainer').innerHTML = `
                    <div class="personal-empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Data kosong</p>
                    </div>
                `;
                toggleContainers();
                return;
            }

            if (response.listPeriode && document.getElementById('selectPeriode').options.length === 0) {
                let options = '';
                response.listPeriode.forEach(p => {
                    let selected = (p.tahun == response.tahun) ? 'selected' : '';
                    options += `<option value="${p.tahun}" ${selected}>${p.label}</option>`;
                });
                document.getElementById('selectPeriode').innerHTML = options;
            }

            renderAbsen(response);
            personalGlobalData = Array.isArray(response.data) ? response.data : [];
            personalEvaluatedName = response.nama_evaluated?.[0] ?? '-';

            if (personalGlobalData.length > 0) {
                renderJenisPenilaian(personalGlobalData);
            } else {
                document.getElementById('groupButtonJenisPenilaian').innerHTML = `
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Tidak ada data penilaian</p>
                    </div>
                `;
                document.getElementById('groupButtonEvaluator').innerHTML = `
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-minus"></i>
                        <p>-</p>
                    </div>
                `;
                document.getElementById('formContainer').innerHTML = `
                    <div class="personal-empty-state w-100">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Tidak ada form penilaian</p>
                    </div>
                `;
            }
            toggleContainers();
        },
        error: function(xhr) {
            document.getElementById('groupButtonJenisPenilaian').innerHTML = `
                <div class="personal-empty-state w-100">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>Gagal memuat data</p>
                </div>
            `;
            document.getElementById('groupButtonEvaluator').innerHTML = `
                <div class="personal-empty-state w-100">
                    <i class="fa-solid fa-minus"></i>
                    <p>-</p>
                </div>
            `;
            document.getElementById('formContainer').innerHTML = `
                <div class="personal-empty-state">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>Gagal memuat data</p>
                </div>
            `;
            console.error(xhr.responseText);
            toggleContainers();
        }
    });
}

function toggleContainers() {
    document.getElementById('personal-skeleton-container').style.display = 'none';
    document.getElementById('personal-real-container').style.display = 'block';
}

function renderAbsen(response) {
    const dataAbsen = response.dataAbsen ?? {};
    document.getElementById('content_body_absen').innerHTML = `
        <tr>
            <td>${dataAbsen.telat ?? 0}</td>
            <td>${dataAbsen.sakit ?? 0}</td>
            <td>${dataAbsen.izin ?? 0}</td>
        </tr>
    `;

    let catatan = 'Belum ada catatan mengenai Anda.';
    if (Array.isArray(response.catatan)) {
        catatan = response.catatan.join('<br>');
    } else if (response.catatan && response.catatan !== 'null') {
        catatan = response.catatan;
    }

    document.getElementById('content_footer_absen').innerHTML = `
        <div class="personal-catatan-box">
            <div class="catatan-label">
                <i class="fa-solid fa-calendar-day"></i> Periode Absensi
            </div>
            <div class="catatan-content">
                Tahun <strong>${response.tahun ?? '-'}</strong>
            </div>
        </div>
        <div class="personal-catatan-box">
            <div class="catatan-label">
                <i class="fa-solid fa-note-sticky"></i> Catatan
            </div>
            <div class="catatan-content">${catatan}</div>
        </div>
    `;
}

function renderJenisPenilaian(data) {
    const container = document.getElementById('groupButtonJenisPenilaian');
    container.innerHTML = '';

    const iconMap = {
        'General Manager': 'fa-crown',
        'Manager/SPV/Team Leader (Atasan Langsung)': 'fa-user-tie',
        'Rekan Kerja (Satu Divisi)': 'fa-users',
        'Pekerja (Beda Divisi)': 'fa-people-arrows',
        'Self Apprisial': 'fa-user-check'
    };

    data.forEach(item => {
        const icon = iconMap[item.jenis_penilaian] || 'fa-file-lines';
        const btn = document.createElement('button');
        btn.className = 'personal-tab-btn personal-jenis-btn';
        btn.setAttribute('data-jenis', item.jenis_penilaian);
        btn.innerHTML = `<i class="fa-solid ${icon} me-1"></i> ${item.jenis_penilaian}`;
        container.appendChild(btn);
    });

    document.querySelectorAll('.personal-jenis-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.personal-jenis-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            personalSelectedJenis = this.getAttribute('data-jenis');
            renderEvaluators(personalSelectedJenis);
        });
    });

    const firstBtn = document.querySelector('.personal-jenis-btn');
    if (firstBtn) firstBtn.click();
}

function renderEvaluators(jenis) {
    const jenisData = personalGlobalData.find(j => j.jenis_penilaian === jenis);
    if (!jenisData) return;

    const container = document.getElementById('groupButtonEvaluator');
    container.innerHTML = '';

    if (!jenisData.evaluator || !Array.isArray(jenisData.evaluator) || jenisData.evaluator.length === 0) {
        container.innerHTML = `
            <div class="personal-empty-state w-100">
                <i class="fa-solid fa-inbox"></i>
                <p>Tidak ada evaluator</p>
            </div>
        `;
        document.getElementById('formContainer').innerHTML = `
            <div class="personal-empty-state w-100">
                <i class="fa-solid fa-inbox"></i>
                <p>Tidak ada form penilaian</p>
            </div>
        `;
        return;
    }

    jenisData.evaluator.forEach((ev, i) => {
        const btn = document.createElement('button');
        btn.className = 'personal-evaluator-pill personal-evaluator-btn';
        btn.setAttribute('data-nama', ev.nama_evaluator);
        btn.innerHTML = `
            <span class="pill-number">${i + 1}</span>
            <span>Evaluator ${i + 1}</span>
        `;
        container.appendChild(btn);
    });

    document.querySelectorAll('.personal-evaluator-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.personal-evaluator-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            personalSelectedEvaluator = this.getAttribute('data-nama');
            renderForm(jenis, personalSelectedEvaluator);
        });
    });

    const firstBtn = document.querySelector('.personal-evaluator-btn');
    if (firstBtn) firstBtn.click();
}

function renderForm(jenis, evaluatorName) {
    const jenisData = personalGlobalData.find(j => j.jenis_penilaian === jenis);
    const evaluatorData = jenisData?.evaluator.find(e => e.nama_evaluator === evaluatorName);
    
    if (!evaluatorData) {
        document.getElementById('formContainer').innerHTML = `
            <div class="personal-empty-state w-100">
                <i class="fa-solid fa-inbox"></i>
                <p>Data evaluator tidak ditemukan</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="personal-form-header-card">
            <h4><i class="fa-solid fa-clipboard-check me-2" style="color: #4f46e5;"></i>Penilaian ${jenis}</h4>
            <span class="evaluated-name">
                <i class="fa-solid fa-user"></i> ${personalEvaluatedName}
            </span>
        </div>
    `;

    if (evaluatorData.kriteria && Array.isArray(evaluatorData.kriteria) && evaluatorData.kriteria.length > 0) {
        evaluatorData.kriteria.forEach(k => {
            html += `<div class="personal-kriteria-section">`;
            html += `<div class="personal-kriteria-header">${k.kriteria ?? '-'}</div>`;

            if (k.subKriteria && Array.isArray(k.subKriteria) && k.subKriteria.length > 0) {
                k.subKriteria.forEach(sk => {
                    const deskripsi = sk.deskripsi ? sk.deskripsi.toString().trim() : '';
                    html += `
                        <div class="personal-sub-kriteria-item">
                            <div class="sub-label">${sk.subKriteria ?? '-'}</div>
                            <div class="personal-nilai-display">
                                <i class="fa-solid fa-star me-1" style="font-size: 0.85rem;"></i>
                                ${sk.nilai ?? '-'}
                            </div>
                            ${deskripsi && deskripsi !== 'null' && deskripsi !== ''
                                ? `<div class="personal-deskripsi-display"><i class="fa-solid fa-comment-dots me-1" style="color: #4f46e5;"></i> ${sk.deskripsi}</div>`
                                : ''}
                        </div>
                    `;
                });
            } else {
                html += `
                    <div class="personal-empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>Tidak ada sub kriteria</p>
                    </div>
                `;
            }

            html += `</div>`;
        });
    } else {
        html += `
            <div class="personal-empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p>Tidak ada kriteria</p>
            </div>
        `;
    }

    document.getElementById('formContainer').innerHTML = html;
}