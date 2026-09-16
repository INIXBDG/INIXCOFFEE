let globalEvaluators = [];
let globalKriteria = [];
let globalEvaluated = {};
let globalTahun = '';
let chartTahunIni = null;
let chartAllYears = null;

$(document).ready(function() {
    loadData();
    loadChartData();
});

function pengubahFormat(angka) {
    return angka.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

$('#jenis-penilaian-tab').on('click', '.modern-tab-btn', function() {
    $('#jenis-penilaian-tab .modern-tab-btn').removeClass('active-tab');
    $(this).addClass('active-tab');
    const jenis = $(this).data('jenis');
    renderTabel(jenis);
});

function loadData() {
    let formData = new FormData();
    formData.append('_token', window.PENILAIAN_DETAIL_CONFIG.csrfToken);
    formData.append('kodeForm', $('#kodeForm').val());
    formData.append('id_karyawan', $('#id_karyawan').val());
    formData.append('tahun', $('#selectTahun').val() || new Date().getFullYear());
    formData.append('jenis_form', $('#jenis_form').val());

    $.ajax({
        url: window.PENILAIAN_DETAIL_CONFIG.detailGetRoute,
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: function(response) {
            $('#skeletonInfo').addClass('d-none');
            $('#skeletonTable').addClass('d-none');
            $('#skeletonAbsensi').addClass('d-none');

            const data = response.data[0];
            globalEvaluators = data.data.evaluator;
            globalKriteria = data.data.dataKriteria;
            globalEvaluated = data.evaluated;
            globalChart = data.chart;
            globalAbsensi = data.dataAbsen;
            globalTahun = data.evaluated.tahun;

            let content_utama = $('#content_utama');
            content_utama.empty();

            let content_absensi = $('#body_content_absensi');
            content_absensi.empty();

            if (!globalAbsensi.isEmpty) {
                content_absensi.append(`<tr><td>${globalAbsensi.telat}</td><td>${globalAbsensi.sakit}</td><td>${globalAbsensi.izin}</td></tr>`);
            } else {
                content_absensi.append(`<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data absensi</td></tr>`);
            }

            const jenisList = [...new Set(globalEvaluators.map(ev => ev.jenis_penilaian))];
            let kodeForm = globalKriteria.length > 0 ? globalKriteria[0].kodeForm : '';
            let id_karyawan = globalEvaluated.id_karyawan ?? '';

            let jenisHTML = `<a class="modern-tab-btn active-tab" data-jenis="all"><i class="fa-solid fa-layer-group me-1"></i> Semua</a>`;
            const routePreview = window.PENILAIAN_DETAIL_CONFIG.pdfPreviewRoute; 

            let emailSend = `
                <div class="d-flex flex-wrap gap-2">
                    <button id="kirimEmail" class="btn-action success" data-kodeform="${kodeForm}" data-id="${id_karyawan}"><i class="fa-solid fa-paper-plane"></i> Kirim Email</button>
                    <a href="${routePreview}?kodeForm=${kodeForm}&id_karyawan=${id_karyawan}&tipe=office" target="_blank" class="btn-action danger"><i class="fa-solid fa-file-pdf"></i> Preview & Print Office</a>
                    <a href="${routePreview}?kodeForm=${kodeForm}&id_karyawan=${id_karyawan}&tipe=non_office" target="_blank" class="btn-action danger"><i class="fa-solid fa-file-pdf"></i> Preview & Print Non-Office</a>
                </div>`;

            const iconMap = {
                'General Manager': 'fa-crown',
                'Manager/SPV/Team Leader (Atasan Langsung)': 'fa-user-tie',
                'Rekan Kerja (Satu Divisi)': 'fa-users',
                'Pekerja (Beda Divisi)': 'fa-people-arrows',
                'Self Apprisial': 'fa-user-check'
            };

            jenisList.forEach(jenis => {
                let label = jenis === 'Manager/SPV/Team Leader (Atasan Langsung)' ? 'Koordinator' : jenis === 'Rekan Kerja (Satu Divisi)' ? 'Satu Divisi' : jenis === 'Pekerja (Beda Divisi)' ? 'Beda Divisi' : jenis;
                let icon = iconMap[jenis] || 'fa-user';
                jenisHTML += `<a class="modern-tab-btn" data-jenis="${jenis}"><i class="fa-solid ${icon} me-1"></i> ${label}</a>`;
            });

            $('#shareEmail').html(emailSend);
            $('#jenis-penilaian-tab').html(jenisHTML);

            let listEvaluatorHTML = globalEvaluators.map(ev => `<li data-target="${ev.nama}-${ev.jenis_penilaian}" class="list-group-item"><i class="fa-solid fa-user me-2 text-primary"></i>${ev.nama}</li>`).join('');

            content_utama.append(`
                <div class="info-item">
                    <div class="info-label"><i class="fa-solid fa-file-lines me-1"></i> Jenis Form</div>
                    <div class="info-value">${window.PENILAIAN_DETAIL_CONFIG.tipeForm}</div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fa-solid fa-users me-1"></i> Evaluator</div>
                    <ul class="list-group evaluator-list ms-0">${listEvaluatorHTML}</ul>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fa-solid fa-user-check me-1"></i> Yang Dinilai</div>
                    <div class="info-value">${globalEvaluated.nama}</div>
                </div>
                <div class="row g-2">
                    <div class="col-12">
                        <div class="info-item mb-0">
                            <div class="info-label"><i class="fa-solid fa-calendar me-1"></i> Tahun</div>
                            <div class="info-value text-center">${globalEvaluated.tahun}</div>
                        </div>
                    </div>
                </div>
                <hr class="my-3">
                <form method="post" action="${window.PENILAIAN_DETAIL_CONFIG.sendCatatanRoute}">
                    <input type="hidden" name="_token" value="${window.PENILAIAN_DETAIL_CONFIG.csrfToken}">
                    <input type="hidden" name="id_karyawan" value="${globalEvaluated.id_karyawan}">
                    <input type="hidden" name="tahun" value="${globalEvaluated.tahun}">
                    <input type="hidden" name="kode_form" value="${globalEvaluated.kode_form}">
                    <div class="info-item">
                        <label class="form-label-modern"><i class="fa-solid fa-note-sticky"></i> Catatan</label>
                        <textarea class="modern-textarea" placeholder="Berikan catatan..." rows="4" name="catatan">${globalEvaluated.catatan === 'null' || globalEvaluated.catatan === null ? '' : globalEvaluated.catatan}</textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn-action primary"><i class="fa-solid fa-save"></i> Simpan Catatan</button>
                    </div>
                </form>
            `);

            renderTabel('all');
        },
        error: function() {
            $('#skeletonInfo, #skeletonTable, #skeletonAbsensi').addClass('d-none');
        }
    });
}

function renderTabel(filterJenis) {
    let content = $('#body_content');
    content.empty();

    const persentaseJenis = {
        'General Manager': 35, 'Manager/SPV/Team Leader (Atasan Langsung)': 30,
        'Rekan Kerja (Satu Divisi)': 20, 'Pekerja (Beda Divisi)': 10, 'Self Apprisial': 5
    };

    let groupRata2 = {};
    let filteredEvaluators = filterJenis === 'all' ? globalEvaluators : globalEvaluators.filter(ev => ev.jenis_penilaian === filterJenis);

    if (filteredEvaluators.length === 0) {
        content.append(`<tr><td colspan="6" class="text-center py-5 text-muted"><i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>Tidak Ada Data</td></tr>`);
        return;
    }

    filteredEvaluators.forEach(evaluator => {
        let nilaiList = evaluator.nilai;
        let nilaiIndex = 0;
        globalKriteria.forEach(kriteria => {
            kriteria.detailKriteria.forEach(sub => {
                const nilaiItem = nilaiList[nilaiIndex++] || { nilai: '-', pesan: '-' };
                const nilai = parseFloat(nilaiItem.nilai);
                if (sub.tipe_input !== 'textarea' && !isNaN(nilai)) {
                    groupRata2[evaluator.jenis_penilaian] = groupRata2[evaluator.jenis_penilaian] || {};
                    groupRata2[evaluator.jenis_penilaian][kriteria.kriteria] = groupRata2[evaluator.jenis_penilaian][kriteria.kriteria] || {};
                    groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria] = groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria] || [];
                    groupRata2[evaluator.jenis_penilaian][kriteria.kriteria][sub.sub_kriteria].push(nilai);
                }
            });
        });
    });

    let rata2Hasil = {};
    for (const jenis in groupRata2) {
        rata2Hasil[jenis] = {};
        for (const kriteria in groupRata2[jenis]) {
            rata2Hasil[jenis][kriteria] = {};
            for (const sub in groupRata2[jenis][kriteria]) {
                let arr = groupRata2[jenis][kriteria][sub];
                rata2Hasil[jenis][kriteria][sub] = arr.reduce((a, b) => a + b, 0) / arr.length;
            }
        }
    }

    let jenisTotalRaw = {};

    filteredEvaluators.forEach(evaluator => {
        content.append(`<tr id="${evaluator.nama}-${evaluator.jenis_penilaian}" class="evaluator-header"><td colspan="6"><i class="fa-solid fa-user me-2"></i>${evaluator.nama} <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size: .75rem;">${evaluator.jenis_penilaian}</span></td></tr>`);

        let nilaiList = evaluator.nilai;
        let nilaiIndex = 0;
        let totalSkorEvaluator = 0;

        globalKriteria.forEach(kriteria => {
            const subKriteriaList = kriteria.detailKriteria;
            const jumlahSub = subKriteriaList.length;

            subKriteriaList.forEach((sub, idxSub) => {
                const nilaiItem = nilaiList[nilaiIndex++] || { nilai: '-', pesan: '-' };
                const nilai = nilaiItem.nilai;
                const pesan = nilaiItem.pesan;
                const tipe = sub.tipe_input;
                const bobot = parseFloat(sub.bobot);

                let kriteriaCell = idxSub === 0 ? `<td rowspan="${jumlahSub}" class="text-left fw-semibold align-middle">${kriteria.kriteria}</td>` : '';
                let subKriteriaCell = `<td style="text-align: left;">${sub.sub_kriteria}</td>`;
                let dataNilai = '';

                if (tipe === 'textarea') {
                    dataNilai = `<td colspan="4" style="font-style: italic; color: #64748b;">${pesan && pesan.trim() !== '' ? pesan : '-'}</td>`;
                } else {
                    const nilaiAngka = parseFloat(nilai);
                    const rataData = rata2Hasil[evaluator.jenis_penilaian]?.[kriteria.kriteria]?.[sub.sub_kriteria];
                    let rata = '-';
                    let skor = 0;
                    if (!isNaN(nilaiAngka)) {
                        rata = rataData !== undefined ? rataData : nilaiAngka;
                        skor = (rata * bobot) / 100;
                        totalSkorEvaluator += skor;
                    }
                    dataNilai = `<td><span class="badge bg-light text-dark border">${bobot}%</span></td><td class="fw-semibold">${nilai}</td><td>${rata === '-' ? '-' : pengubahFormat(rata)}</td><td class="fw-bold text-primary">${rata === '-' ? '-' : pengubahFormat(skor)}</td>`;
                }

                content.append(`<tr>${kriteriaCell}${subKriteriaCell}${dataNilai}</tr>`);
            });
        });

        content.append(`<tr class="total-row"><td colspan="5" class="text-end">Total (${evaluator.nama})</td><td class="text-center">${pengubahFormat(totalSkorEvaluator)}</td></tr>`);
        
        const jenis = evaluator.jenis_penilaian;
        if (!jenisTotalRaw.hasOwnProperty(jenis)) jenisTotalRaw[jenis] = totalSkorEvaluator;
    });

    let jenisTotalPost = {};
    for (const jenis in jenisTotalRaw) {
        const persen = persentaseJenis[jenis] || 0;
        jenisTotalPost[jenis] = (jenisTotalRaw[jenis] * persen) / 100;
    }

    let totalSemuaSkor = filterJenis === 'all' ? Object.values(jenisTotalPost).reduce((a, b) => a + b, 0) : (jenisTotalPost[filterJenis] || 0);
    let grade = totalSemuaSkor >= 90 ? 'A' : totalSemuaSkor >= 80 ? 'B' : totalSemuaSkor >= 70 ? 'C' : totalSemuaSkor >= 60 ? 'D' : 'E';

    content.append(`<tr class="grand-total-row"><td colspan="5" class="text-end">Grade</td><td class="text-center fs-4 fw-bold">${grade}</td></tr>`);
}

$(document).on('click', '.evaluator-list .list-group-item', function() {
    const targetId = $(this).data('target');
    const evaluatorRow = document.getElementById(targetId);
    if (evaluatorRow) {
        $('html, body').animate({ scrollTop: $(evaluatorRow).offset().top - 100 }, 500);
    }
});

$(document).on('click', '#kirimEmail', function(e) {
    e.preventDefault();
    $.ajax({
        url: window.PENILAIAN_DETAIL_CONFIG.emailRoute,
        type: 'POST',
        data: {
            _token: window.PENILAIAN_DETAIL_CONFIG.csrfToken,
            kodeForm: $(this).data('kodeform'),
            id_karyawan: $(this).data('id')
        },
        dataType: 'json',
        success: function(response) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Email berhasil dikirim!', confirmButtonColor: '#6366f1' });
        },
        error: function(xhr) {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: `Gagal mengirim email: ${xhr.responseText}`, confirmButtonColor: '#ef4444' });
        }
    });
});

function loadChartData() {
    let formData = new FormData();
    formData.append('_token', window.PENILAIAN_DETAIL_CONFIG.csrfToken);
    formData.append('kodeForm', $('#kodeForm').val());
    formData.append('id_karyawan', $('#id_karyawan').val());
    formData.append('tahun', $('#selectTahun').val() || new Date().getFullYear());

    $.ajax({
        url: window.PENILAIAN_DETAIL_CONFIG.detailChartGetRoute,
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: function(res) {
            $('#skeletonChart1').addClass('d-none');
            $('#skeletonChart2').addClass('d-none');
            if (res.chartTahunan) tampilkanChartSkorTahunan(res.chartTahunan);
        },
        error: function() {
            $('#skeletonChart1, #skeletonChart2').addClass('d-none');
        }
    });
}

const selectTahun = document.getElementById("selectTahun");
if (selectTahun) {
    const tahunSekarang = new Date().getFullYear();
    for (let tahun = tahunSekarang; tahun <= tahunSekarang + 10; tahun++) {
        const option = document.createElement("option");
        option.value = tahun;
        option.textContent = tahun;
        if (tahun === tahunSekarang) option.selected = true;
        selectTahun.appendChild(option);
    }
    $('#selectTahun').on('change', function() {
        loadChartData();
    });
}

function tampilkanChartSkorTahunan(chartTahunan) {
    const tahunList = Object.keys(chartTahunan).sort();
    const dataValues = tahunList.map(t => parseFloat(chartTahunan[t]).toFixed(2));

    const ctxBar = document.getElementById("barChart").getContext("2d");
    if (chartTahunIni) chartTahunIni.destroy();
    chartTahunIni = new Chart(ctxBar, {
        type: "bar",
        data: {
            labels: tahunList,
            datasets: [{
                label: "Skor per Tahun", data: dataValues,
                backgroundColor: 'rgba(99, 102, 241, 0.7)', borderColor: '#6366f1', borderWidth: 2, borderRadius: 8
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8 } },
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => v.toFixed(2) } }, x: { grid: { display: false } } }
        }
    });

    const ctxLine = document.getElementById("lineChart").getContext("2d");
    if (chartAllYears) chartAllYears.destroy();
    chartAllYears = new Chart(ctxLine, {
        type: "line",
        data: {
            labels: tahunList,
            datasets: [{
                label: "Trend Skor Tahunan", data: dataValues,
                borderColor: '#6366f1', backgroundColor: 'rgba(99, 102, 241, 0.15)', borderWidth: 3, fill: true, tension: 0.4,
                pointRadius: 5, pointHoverRadius: 7, pointBackgroundColor: '#fff', pointBorderColor: '#6366f1', pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } }, tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', padding: 12, cornerRadius: 8 } },
            scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => v.toFixed(2) } }, x: { grid: { display: false } } }
        }
    });
}