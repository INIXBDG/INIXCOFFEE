// ==========================================================================
// KPI INDEX MANAGEMENT - EXTERNAL JS
// ==========================================================================

let allTargetData = [];
let currentFilteredData = [];
let currentPage = 1;
let totalPages = 1;
let totalRecords = 0;
const itemsPerPage = 10;

// Konstanta Route & Mapping
const allowedAssistantRoutes = [
    'dorong inovasi pelayanan',
    'inisiatif efisiensi keuangan',
    'rasio biaya operasional terhadap revenue',
    'mengurangi manual work dan error',
];

// Definisikan variabel ini untuk mencegah ReferenceError
const allowedDoubleManualRoutes = []; 

const assistantRouteUrlMap = {
    "pemasukan kotor": "/rkm",
    "target penjualan project tahunan": "/projects/leads",
    "pemasukan bersih": "/office/analysis",
    "kepuasan pelanggan": "/feedback",
    "rasio biaya operasional terhadap revenue": "/kpi-data/table-data",
    "performa kpi departemen": "/kpi-data/table-data",
    "peserta puas dengan pelayanan dan fasilitas training": "/feedback",
    "dorong inovasi pelayanan": "/kpi-data/table-data",
    "penanganan komplain perseta": "/komplain-peserta",
    "report persiapan kelas": "/office/dashboard",
    "outstanding": "/outstanding",
    "inisiatif efisiensi keuangan": "/kpi-data/table-data",
    "mengurangi manual work dan error": "/kpi-data/table-data",
    "laporan analisis keuangan": "/office/analysis",
    "pencairan biaya operasional": "/pengajuanbarang",
    "penyelesaian tagihan perusahaan": "/office/tagihan-perusahaan",
    "akurasi pencatatan masuk": "/outstanding",
    "pelaksanaan kegiatan karyawan": "office/kegiatan/index",
    "pengeluaran biaya karyawan": "/office/administrasi-karyawan",
    "administrasi karyawan": "/office/administrasi-karyawan",
    "perbaikan kendaraan": "/office/kendaraan/index/perbaikan",
    "report kondisi kendaraan": "/office/kendaraan/index/kondisi",
    "kontrol pengeluaran transportasi": "/office/pickup-driver/index",
    "feedback kenyamanan berkendaran": "/feedback",
    "feedback kebersihan dan kenyamanan": "/feedback",
    "penyelesaian tugas harian": "/office/daftar-tugas/Index",
    "meningkatkan kepuasan dan loyalitas peserta/client": "/survey/kepuasan/table",
    "availability sistem internal kritis": "/home",
    "persentase gap kompetensi tim terhadap standar skill": "/kpi-data/table-data",
    "inovation adaption rate": "/ide-inovasi",
    "ketepatan waktu penyelesaian fitur": "/tickets",
    "mengukur kualitas aplikasi agar minim bug": "/tickets",
    "konsistensi campaign digital": "/content-schedules",
    "efektifitas digital marketing": "/colaborator",
    "keberhasilan support memenuhi sla": "/tickets",
    "kualitas layanan exam": "/registrasi",
    "presentase kinerja instruktur": "/activityinstruktur",
    "kepuasan peserta pelatihan": "/feedback",
    "upseling lanjutan materi": "/office/rekomendasi-lanjutan/index",
    "sertifikasi kompetensi internal": "/development",
    "pelatihan kompetensi eksternal": "/development",
    "pengembangan kurikulum pelatihan": "/materi",
    "peningkatan knowledge sharing": "/activityinstruktur",
    "peningkatan kontribusi pelatihan": "/rkm",
    "evaluasi kinerja instruktur": "/activityinstruktur",
    "target penjualan tahunan": "/rkm",
    "biaya akuisisi perclient": "/crm/peluang/index",
    "peningkatan kemampuan kompetensi sales": "https://elearning.inixindobdg.co.id/login/index.php?loginredirect=1",
    "meningkatkan revenue perusahaan": "/rkm",
    "customer acquisition cost": "/crm/peluang/index",
    "evaluasi kinerja sales": "/crm/aktivitas",
    "laporan mom": "/crm/laporan-harian",
    "akurasi kelengkapan data penjualan": "/crm/laporanPenjualan",
    "todo administrasi": "/crm/todo-administrasi",
    "ketepatan waktu po": "/office/modul/index",
    "kualitas dokumentasi support dan proctor": "/daftar-peserta-exam",
    "pendapatan penjualan project": "/projects/leads",
    "leads project": "/projects/leads"
};

// Utility Functions
function formatNumber(value) {
    if (!value && value !== '0') return '';
    const raw = String(value).replace(/[^0-9]/g, '');
    if (!raw) return '';
    return new Intl.NumberFormat('id-ID').format(raw);
}

function getRawNumber(value) {
    if (!value) return '';
    return String(value).replace(/[^0-9]/g, '');
}

function initInputFormatting() {
    $('#manual_value_display').off('input.formatting').on('input.formatting', function() {
        const raw = getRawNumber($(this).val());
        $('#manual_value').val(raw);
        const format = $('#manual_format').val();
        let formatted = formatNumber(raw);
        if (format === 'rupiah' && raw) formatted = 'Rp ' + formatted;
        else if (format === 'persen' && raw) formatted = formatted + '%';
        $(this).val(formatted);
    });

    $('#biaya_gaji_display').off('input.formatting').on('input.formatting', function() {
        const raw = getRawNumber($(this).val());
        $('#biaya_gaji_tahunan').val(raw);
        $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
    });

    $('#biaya_bpjs_display').off('input.formatting').on('input.formatting', function() {
        const raw = getRawNumber($(this).val());
        $('#biaya_bpjs_tahunan').val(raw);
        $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
    });

    $('#biaya_rekrutmen_display').off('input.formatting').on('input.formatting', function() {
        const raw = getRawNumber($(this).val());
        $('#biaya_rekrutmen_tahunan').val(raw);
        $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
    });
}

function resetFormManual() {
    $('#formManualValue')[0].reset();
    $('#documentPreview').html('');
    $('#singleInputArea').show();
    $('#doubleInputArea').hide();
    $('#manual_value_display').val('').trigger('input');
    $('#manual_value').val('');
    $('#biaya_gaji_display').val('').trigger('input');
    $('#biaya_gaji_tahunan').val('');
    $('#biaya_bpjs_display').val('').trigger('input');
    $('#biaya_bpjs_tahunan').val('');
    $('#biaya_rekrutmen_display').val('').trigger('input');
    $('#biaya_rekrutmen_tahunan').val('');
    $('#manualValueId').val('');
}

// ==========================================================================
// MAIN INITIALIZATION
// ==========================================================================
$(document).ready(function() {
    // Inisialisasi Select2
    $('#jabatan').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', dropdownParent: $('#modalBuatTarget') });
    $('#karyawan').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', dropdownParent: $('#modalBuatTarget') });

    // Mencegah event bubbling
    $(document).on('click', '.buttonHapusTarget, .buttonForm', function(e) { e.stopPropagation(); });

    // Handle Session Import Errors
    if (window.KPI_CONFIG && window.KPI_CONFIG.importErrors && window.KPI_CONFIG.importErrors.length > 0) {
        const errors = window.KPI_CONFIG.importErrors;
        const $errorSummary = $('#errorSummary');
        const $errorList = $errorSummary.find('ul');
        $errorList.empty();
        errors.slice(0, 10).forEach(err => $errorList.append(`<li class="text-danger">${err}</li>`));
        if (errors.length > 10) $errorList.append(`<li class="text-muted">...dan ${errors.length - 10} error lainnya</li>`);
        $errorSummary.removeClass('d-none');
        $('#ModalImport').modal('show');
    }

    $('#formImport').on('submit', function(e) {
        const $btn = $('#btnSubmitImport');
        const $preview = $('#importPreview');
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Memproses...');
        $preview.removeClass('d-none');
    });

    // Fetch Initial Data for Modal Dropdowns
    $.ajax({
        url: window.KPI_CONFIG.getDataTarget,
        type: 'GET',
        success: function(response) {
            const routeSelect = $('#assistant_route');
            if (routeSelect.length && response.routes) {
                routeSelect.empty().append('<option selected disabled>-- Pilih Assistant Route --</option>');
                response.routes.forEach(function(route) {
                    routeSelect.append(`<option value="${route.asistant_route}">${route.asistant_route}</option>`);
                });
            }
            const jabatanSelect = $('#jabatan');
            if (jabatanSelect.length && response.jabatan_list) {
                jabatanSelect.empty();
                response.jabatan_list.forEach(function(jab) {
                    jabatanSelect.append(`<option value="${jab}">${jab}</option>`);
                });
                jabatanSelect.trigger('change');
            }
        }
    });

    $('#jabatan').off('change').on('change', function() {
        const selectedJabatans = $(this).val() || [];
        const karyawanSelect = $('#karyawan');
        karyawanSelect.empty().trigger('change');
        if (selectedJabatans.length === 0) return;

        $.ajax({
            url: window.KPI_CONFIG.getKaryawanByJabatan,
            type: 'GET',
            data: { jabatan: selectedJabatans },
            success: function(response) {
                karyawanSelect.empty();
                response.forEach(function(emp) {
                    karyawanSelect.append(`<option value="${emp.id}">${emp.text}</option>`);
                });
                karyawanSelect.trigger('change');
            }
        });
    });

    // Load Content Form (Initial Load)
    loadContentForm();

    // Search dengan Debounce (HANYA SATU EVENT LISTENER)
    let searchTimer;
    $('#searchTarget').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const searchVal = $(this).val().trim();
            currentPage = 1;
            loadContentForm(1, searchVal);
        }, 300);
    });

    initInputFormatting();
    $('#modalFormManual').on('show.bs.modal', function() { resetFormManual(); });
    $('#modalFormManual').on('hidden.bs.modal', function() { resetFormManual(); });

    // Setup Form Listeners for Create Target Modal
    const $assistantRoute = $('#assistant_route');
    $assistantRoute.prop('disabled', true).html('<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>');

    $('#jabatan').on('change', function() {
        const selectedJabatan = $(this).val();
        const assistantRouteSelect = $('#assistant_route');
        
        assistantRouteSelect.empty().prop('disabled', true).html('<option selected disabled>-- Memuat routes... --</option>');
        $('#detail_jangka_container').empty();

        if (!selectedJabatan || selectedJabatan.length === 0) {
            assistantRouteSelect.html('<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>');
            return;
        }

        $.ajax({
            url: window.KPI_CONFIG.getRoutesByJabatan,
            type: 'GET',
            data: { jabatan: selectedJabatan },
            success: function(response) {
                assistantRouteSelect.empty();
                assistantRouteSelect.append('<option selected disabled>-- Pilih Assistant Route --</option>');
                if (response.length === 0) {
                    assistantRouteSelect.append('<option disabled>-- Tidak ada Assistant Route tersedia untuk jabatan ini --</option>');
                    assistantRouteSelect.prop('disabled', true);
                    return;
                }
                response.forEach(route => {
                    assistantRouteSelect.append(`<option value="${route.asistant_route}">${route.asistant_route}</option>`);
                });
                assistantRouteSelect.prop('disabled', false);
            },
            error: function() {
                assistantRouteSelect.html('<option disabled>-- Gagal memuat routes --</option>');
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Gagal memuat daftar assistant route', 'error');
            }
        });
    });

    $(document).on('change', '#assistant_route', function() {
        const selectedRoute = $(this).val();
        $('#jangka_target_display, #tipe_target_display, #nilai_target_display').val('').addClass('bg-light');
        $('#jangka_target_hidden, #tipe_target_hidden, #nilai_target_hidden').val('');
        $('#detail_jangka_container').empty();

        if (!selectedRoute) return;

        $.ajax({
            url: window.KPI_CONFIG.getTargetByRoute,
            type: 'GET',
            data: { route: selectedRoute },
            success: function(config) {
                const jangka = config.jangka_target || 'Tidak ditentukan';
                $('#jangka_target_display').val(jangka);
                $('#jangka_target_hidden').val(jangka);

                let tipeLabel = '';
                if (config.tipe_target === 'persen') tipeLabel = 'Persen (%)';
                else if (config.tipe_target === 'rupiah') tipeLabel = 'Rupiah (Nilai Keuangan)';
                else tipeLabel = 'Angka (Unit, Jumlah, dll)';
                $('#tipe_target_display').val(tipeLabel);
                $('#tipe_target_hidden').val(config.tipe_target);

                let nilaiFormatted = '';
                if (config.tipe_target === 'rupiah') nilaiFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(config.nilai_target);
                else if (config.tipe_target === 'persen') nilaiFormatted = `${config.nilai_target} %`;
                else nilaiFormatted = new Intl.NumberFormat('id-ID').format(config.nilai_target);
                $('#nilai_target_display').val(nilaiFormatted);
                $('#nilai_target_hidden').val(config.nilai_target);

                let jangkaLower = jangka.toLowerCase();
                let htmlDetailJangka = '';
                if (jangkaLower === 'tahunan') {
                    const tahunIni = new Date().getFullYear();
                    htmlDetailJangka = `<div class="col-md-12 mb-3"><label for="detail_jangka" class="form-label">Tahun Pelaksanaan <span class="text-danger">*</span></label><select name="detail_jangka" id="detail_jangka" class="form-select" required><option value="${tahunIni}" selected>${tahunIni}</option></select></div>`;
                } else if (jangkaLower === 'bulanan') {
                    htmlDetailJangka = `<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Bulan & Tahun <span class="text-danger">*</span></label><input type="month" name="detail_jangka" id="detail_jangka" class="form-control" required></div>`;
                } else if (jangkaLower === 'kuartalan') {
                    htmlDetailJangka = `<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Tahun & Kuartal <span class="text-danger">*</span></label><input type="text" name="detail_jangka" id="detail_jangka" class="form-control" placeholder="Contoh: 2026-Q1" required></div>`;
                } else {
                    htmlDetailJangka = `<div class="col-md-6 mb-3"><label for="detail_jangka" class="form-label">Detail Jangka <span class="text-danger">*</span></label><input type="text" name="detail_jangka" id="detail_jangka" class="form-control" placeholder="Masukkan periode spesifik" required></div>`;
                }
                $('#detail_jangka_container').html(htmlDetailJangka);
            },
            error: function() {
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Gagal memuat konfigurasi target', 'error');
            }
        });
    });

    $(document).on('click', '.buttonDetailTarget', function() {
        let id = $(this).data('id');
        const body = $('#bodyContentDetailTarget');
    
        body.empty();
        body.html(generateDetailSkeleton());

        const modalEl = document.getElementById('detailTargetModal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        $.ajax({
            url: window.KPI_CONFIG.detail,
            method: 'GET',
            data: { id },
            dataType: 'json',
            success: function(response) {
                body.empty();

                const detailArray = Array.isArray(response.detail) ? response.detail : [];
                const data = detailArray.length > 0 ? (detailArray[0].data || null) : null;

                if (!data) {
                    body.append(`
                        <div class="modal-header"><h5 class="modal-title">Detail Target</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body text-center">Belum ada data</div>
                        <div class="modal-footer"><button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button></div>`);
                    return;
                }

                const dataDetail = data.data_detail || {};
                const monthlyData = dataDetail.monthly_data || {};
                const dailyData = dataDetail.daily_breakdown_per_month || {};
                
                const dateNow = new Date(window.KPI_CONFIG.dateNow);
                const startOfYear = new Date(window.KPI_CONFIG.startOfYear);
                const tenggatWaktu = data.tenggat_waktu || '';
                let Tercapai;

                const currentDate = new Date(dateNow);
                const startDate = new Date(startOfYear);
                const deadlineDate = new Date(tenggatWaktu);

                if (isNaN(currentDate) || isNaN(startDate)) Tercapai = "Belum Dimulai";
                else if (currentDate < startDate) Tercapai = "Belum Dimulai";
                else if (!isNaN(deadlineDate) && (currentDate > deadlineDate || currentDate.getTime() === deadlineDate.getTime())) {
                    Tercapai = (dataDetail.progress ?? 0) >= (data.nilai_target ?? 0) ? "Mencapai Target" : "Target Gagal";
                } else {
                    Tercapai = (dataDetail.progress ?? 0) >= (data.nilai_target ?? 0) ? "Mencapai Target" : "Sedang Berjalan";
                }

                const targetValueRaw = data.nilai_target ?? 0;
                const progressValueRaw = dataDetail.progress ?? 0;
                const gapValueRaw = dataDetail.gap ?? 0;

                const totalPeserta = Array.isArray(data.karyawan) ? data.karyawan.length : 0;
                const routesTargetPerPeserta = ['sertifikasi kompetensi internal', 'pelatihan kompetensi eksternal'];
                const isPerPesertaRoute = routesTargetPerPeserta.includes(data.condition);

                const actualTarget = (isPerPesertaRoute && totalPeserta > 0) 
                    ? totalPeserta * targetValueRaw 
                    : targetValueRaw;

                let targetValue, progressValue, gapValue;
                if (data.tipe_target === "persen") {
                    targetValue = targetValueRaw + ' %';
                    progressValue = progressValueRaw + ' %';
                    gapValue = gapValueRaw + ' %';
                } else if (data.tipe_target === "rupiah") {
                    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 });
                    targetValue = formatter.format(actualTarget);
                    progressValue = formatter.format(progressValueRaw);
                    gapValue = formatter.format(Math.abs(gapValueRaw));
                } else {
                    targetValue = isPerPesertaRoute 
                        ? `${actualTarget} <small class="text-muted">(${totalPeserta} peserta × ${targetValueRaw})</small>` 
                        : targetValueRaw;
                        
                    progressValue = isPerPesertaRoute 
                        ? `${progressValueRaw} / ${actualTarget}` 
                        : progressValueRaw;
                        
                    gapValue = gapValueRaw;
                }

                let contentKalenderInstruktur = '';
                const instrukturDetails = Array.isArray(dataDetail.instruktur_details) ? dataDetail.instruktur_details : [];
                const hariLiburNasional = dataDetail.hari_libur_nasional || { jumlah: 0, daftar: {} };

                if (instrukturDetails.length > 0) {
                    const NAMA_BULAN_KAL = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    const NAMA_HARI = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

                    window.__instrukturData = instrukturDetails;
                    window.__currentMonthIndex = {};

                    function buildKalenderBulanHtml(instruktur, bulanKey) {
                        const kalender = instruktur.kalender || {};
                        if (!kalender[bulanKey]) return '<div class="text-muted text-center py-4">Tidak ada data untuk bulan ini.</div>';

                        const [thn, bln] = bulanKey.split('-');
                        const namaBulan = NAMA_BULAN_KAL[parseInt(bln, 10) - 1];
                        const daysInMonth = new Date(thn, bln, 0).getDate();
                        const firstDayOfWeek = new Date(thn, parseInt(bln,10)-1, 1).getDay();

                        let cellsHtml = '';
                        NAMA_HARI.forEach(h => { cellsHtml += `<div class="kal-header">${h}</div>`; });
                        for (let i = 0; i < firstDayOfWeek; i++) {
                            cellsHtml += `<div class="kal-cell kal-empty"></div>`;
                        }
                        for (let d = 1; d <= daysInMonth; d++) {
                            const cellData = kalender[bulanKey][d] || { status: 'empty', keterangan: '' };
                            const statusClass = 'kal-' + cellData.status;
                            cellsHtml += `
                                <div class="kal-cell ${statusClass}" title="${cellData.keterangan || ''}" data-bs-toggle="tooltip">
                                    <div class="kal-day">${d}</div>
                                </div>
                            `;
                        }

                        const monthStats = Object.values(kalender[bulanKey]).reduce((acc, cur) => {
                            acc[cur.status] = (acc[cur.status] || 0) + 1;
                            return acc;
                        }, {});

                        return `
                            <div class="kalender-bulan-slide" data-bulan="${bulanKey}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-semibold mb-0">${namaBulan} ${thn}</h6>
                                    <div class="d-flex gap-2 small">
                                        <span class="badge bg-success">Aktif: ${monthStats.working || 0}</span>
                                        <span class="badge bg-warning text-dark">Cuti: ${monthStats.cuti || 0}</span>
                                        <span class="badge bg-danger">Libur: ${monthStats.libur || 0}</span>
                                    </div>
                                </div>
                                <div class="kalender-grid">${cellsHtml}</div>
                            </div>
                        `;
                    }

                    window.showKalenderBulan = function(instrukturId, bulanIndex) {
                        const instruktur = window.__instrukturData.find(i => i.id == instrukturId);
                        if (!instruktur) return;

                        const kalender = instruktur.kalender || {};
                        const bulanKeys = Object.keys(kalender).sort();
                        const totalBulan = bulanKeys.length;

                        if (totalBulan === 0) return;

                        if (bulanIndex < 0) bulanIndex = totalBulan - 1;
                        if (bulanIndex >= totalBulan) bulanIndex = 0;

                        window.__currentMonthIndex[instrukturId] = bulanIndex;

                        const bulanKey = bulanKeys[bulanIndex];
                        const slideContainer = document.getElementById(`kalenderSlide_${instrukturId}`);
                        if (slideContainer) {
                            slideContainer.innerHTML = buildKalenderBulanHtml(instruktur, bulanKey);
                        }

                        const counterEl = document.getElementById(`bulanCounter_${instrukturId}`);
                        if (counterEl) {
                            counterEl.textContent = `${bulanIndex + 1} dari ${totalBulan} bulan`;
                        }

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
                                                    <td class="small">${new Date(tgl).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'})}</td>
                                                    <td class="small">${dt.tipe || '-'}</td>
                                                    <td class="small">${dt.alasan || '-'}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;
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
                                                <strong>${new Date(tgl).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'})}</strong>
                                                <div class="text-muted">${ket}</div>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }

                    let accordionItems = '';
                    instrukturDetails.forEach((instruktur, idx) => {
                        const persentaseColor = instruktur.persentase >= 100 ? 'success' : (instruktur.persentase >= 75 ? 'warning' : 'danger');
                        const kalender = instruktur.kalender || {};
                        const bulanKeys = Object.keys(kalender).sort();
                        const totalBulan = bulanKeys.length;

                        window.__currentMonthIndex[instruktur.id] = 0;

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
                                                <div class="fw-bold text-${persentaseColor}">${instruktur.persentase}%</div>
                                                <div class="small text-muted">${instruktur.jam_aktif} / ${instruktur.target_jam} jam</div>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapseInstruktur${idx}" class="accordion-collapse collapse ${idx === 0 ? 'show' : ''}" data-bs-parent="#accordionInstruktur">
                                    <div class="accordion-body">
                                        <div class="row g-2 mb-3">
                                            <div class="col-6 col-md-3">
                                                <div class="p-2 border rounded text-center bg-success-subtle">
                                                    <div class="small text-muted">Hari Aktif</div>
                                                    <div class="fw-bold fs-5">${instruktur.total_hari_kerja}</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-2 border rounded text-center bg-warning-subtle">
                                                    <div class="small text-muted">Hari Cuti</div>
                                                    <div class="fw-bold fs-5">${instruktur.total_hari_cuti}</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-2 border rounded text-center bg-danger-subtle">
                                                    <div class="small text-muted">Hari Libur</div>
                                                    <div class="fw-bold fs-5">${instruktur.total_hari_libur}</div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="p-2 border rounded text-center bg-primary-subtle">
                                                    <div class="small text-muted">Jam Aktif</div>
                                                    <div class="fw-bold fs-5">${instruktur.jam_aktif}</div>
                                                </div>
                                            </div>
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
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrevBulan_${instruktur.id}" onclick="showKalenderBulan(${instruktur.id}, window.__currentMonthIndex[${instruktur.id}] - 1)">
                                                    <i class="fa-solid fa-chevron-left me-1"></i> Bulan Sebelumnya
                                                </button>

                                                <div class="flex-grow-1 text-center">
                                                    <small class="text-muted">Gunakan tombol atau swipe untuk navigasi</small>
                                                </div>

                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnNextBulan_${instruktur.id}" onclick="showKalenderBulan(${instruktur.id}, window.__currentMonthIndex[${instruktur.id}] + 1)">
                                                    Bulan Selanjutnya <i class="fa-solid fa-chevron-right ms-1"></i>
                                                </button>
                                            </div>

                                            <div id="kalenderSlide_${instruktur.id}">
                                                ${totalBulan > 0 ? buildKalenderBulanHtml(instruktur, bulanKeys[0]) : '<div class="text-muted text-center py-4">Tidak ada data kalender.</div>'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    contentKalenderInstruktur = `
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
                            .kalender-grid {
                                display: grid;
                                grid-template-columns: repeat(7, 1fr);
                                gap: 3px;
                                background: #f8f9fa;
                                padding: 6px;
                                border-radius: 8px;
                            }
                            .kal-header {
                                text-align: center;
                                font-size: 11px;
                                font-weight: 600;
                                color: #6c757d;
                                padding: 4px 0;
                            }
                            .kal-cell {
                                aspect-ratio: 1;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                border-radius: 4px;
                                font-size: 12px;
                                cursor: pointer;
                                transition: transform 0.15s;
                                position: relative;
                            }
                            .kal-cell:hover { transform: scale(1.1); z-index: 2; }
                            .kal-day { font-weight: 500; }
                            .kal-empty { background: transparent; }
                            .kal-working { background: #d1e7dd; color: #0f5132; }
                            .kal-cuti { background: #fff3cd; color: #664d03; }
                            .kal-libur { background: #f8d7da; color: #842029; }
                            .kal-weekend { background: #e2e3e5; color: #41464b; }
                            .kal-legend-box {
                                display: inline-block;
                                width: 14px;
                                height: 14px;
                                border-radius: 3px;
                                vertical-align: middle;
                                margin-right: 4px;
                            }
                            .kal-legend-box.kal-working { background: #d1e7dd; }
                            .kal-legend-box.kal-cuti { background: #fff3cd; }
                            .kal-legend-box.kal-libur { background: #f8d7da; }
                            .kal-legend-box.kal-weekend { background: #e2e3e5; }
                            .kal-legend-box.kal-empty { background: #f8f9fa; border: 1px dashed #adb5bd; }

                            .kalender-bulan-slide {
                                animation: slideInBulan 0.3s ease-out;
                            }
                            @keyframes slideInBulan {
                                from { opacity: 0; transform: translateX(20px); }
                                to { opacity: 1; transform: translateX(0); }
                            }
                        </style>
                    `;
                }

                let bgCard = Tercapai === "Mencapai Target" ? "success" : Tercapai === "Target Gagal" ? "danger" : Tercapai === "Sedang Berjalan" ? "warning" : "secondary";
                let gapText = Tercapai === "Mencapai Target" ? "success" : "danger";

                const pieChart = dataDetail.pie_chart || { above: 0, below: 0 };
                const dataPieChart = {
                    labels: ['Above', 'Below'],
                    datasets: [{ label: 'Jumlah', data: [pieChart.above ?? 0, pieChart.below ?? 0], backgroundColor: ['#B66DFF', '#FE7C96'], hoverOffset: 4 }]
                };

                const formatRupiah = (nilai = 0) => 'Rp ' + Number(nilai || 0).toLocaleString('id-ID');
                const formatTanggalSingkat = (tanggalString) => {
                    if (!tanggalString) return '-';
                    const tanggal = new Date(tanggalString);
                    return isNaN(tanggal) ? '-' : tanggal.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
                };

                const dataBulananRupiah = monthlyData || {};
                const dataHarianRupiah = dailyData || {};
                const karyawanList = Array.isArray(data.karyawan) ? data.karyawan.filter(k => k) : (data.karyawan ? [data.karyawan] : []);
                const monthKeys = Object.keys(dataBulananRupiah || {}).filter(k => k).sort();
                const keyBulanTerakhirRupiah = monthKeys.length > 0 ? monthKeys[monthKeys.length - 1] : null;
                const nilaiBulanTerakhirRupiah = keyBulanTerakhirRupiah ? (dataBulananRupiah[keyBulanTerakhirRupiah] ?? 0) : 0;
                const labelBulanTerakhir = keyBulanTerakhirRupiah ? new Date(`${keyBulanTerakhirRupiah}-01`).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' }) : '-';

                const seluruhDataHarian = Object.entries(dataHarianRupiah || {}).flatMap(([_, bulan]) => Object.entries(bulan || {}).map(([tgl, nil]) => [tgl, nil])).filter(([_, nilai]) => typeof nilai === 'number' && nilai > 0);
                const top3HariTertinggi = seluruhDataHarian.sort((a, b) => (b[1] || 0) - (a[1] || 0)).slice(0, 3);

                let no = 1;
                const karyawanHtml = karyawanList.map(item => {
                    if (!item) return '';
                    return `<div class="d-flex align-items-center py-2 participant-item"><div class="avatar me-3">${no++}</div><div class="flex-grow-1"><div class="fw-semibold text-dark small">${item.nama_lengkap || '-'}</div><div class="text-muted small">${item.jabatan || '-'}</div></div></div>`;
                }).join('') || '<div class="text-muted small">Tidak ada karyawan</div>';

                const allowedAssistantRoutesForRupiah = ['pemasukan kotor', 'meningkatkan revenue perusahaan', 'target penjualan tahunan', 'target penjualan project tahunan', 'pendapatan penjualan project'];
                const allowedAssistantRoutesForPresentaseGapKompetensi = ['persentase gap kompetensi tim terhadap standar skill'];
                const allowedAssistantRoutesForTargetPenjualanTahunan = ['target penjualan tahunan', 'pemasukan kotor'];
                const allowedAssistantRoutesForPeningkatanKontribusiPelatihan = ['peningkatan kontribusi pelatihan'];
                const allowedAssistantRoutesForPemasukanBersih = ['pemasukan bersih'];
                const allowedAssistantRoutesForPerformaKPIDepartemen = ['performa kpi departemen'];
                const allowedAssistantRoutesForKepuasanPelanggan = ['Kepuasan Pelanggan', 'kepuasan pelanggan'];
                const allowedAssistantRoutesForLaporanAnalisisKeuangan = ['laporan analisis keuangan'];

                let ContentTrafikSales = '';
                const condition = data.condition || '';

                if (allowedAssistantRoutesForTargetPenjualanTahunan.includes(condition)) {
                    const salesPerf = dataDetail.sales_performance || {};
                    if (salesPerf.data) {
                        if (salesPerf.type === 'individual') {
                            const s = salesPerf.data;
                            const statusClass = s.status === 'achieved' ? 'badge-success' : 'badge-warning';
                            const progressColor = s.status === 'achieved' ? '#28a745' : '#ffc107';
                            const progressWidth = Math.min(s.percentage ?? 0, 100);
                            ContentTrafikSales = `<div class="card shadow-sm mb-4 mt-2"><div class="card-body"><div class="row"><div class="col-md-6"><p class="mb-2"><strong>Sales:</strong> ${s.nama || '-'}</p><p class="mb-2"><strong>Revenue:</strong> ${formatRupiah(s.revenue)}</p><p class="mb-3"><strong>Target:</strong> ${formatRupiah(s.presentase_kemampuan)}</p></div><div class="col-md-6"><div class="mb-2"><div class="d-flex justify-content-between"><span>Progress</span><span>${s.percentage ?? 0}%</span></div><div class="progress" style="height: 10px;"><div class="progress-bar" role="progressbar" style="width: ${progressWidth}%; background-color: ${progressColor};"></div></div></div><div class="text-end"><span class="badge ${statusClass} p-2">${(s.status || '').toUpperCase()}</span></div></div></div></div></div>`;
                        } else if (salesPerf.type === 'all' && Array.isArray(salesPerf.data)) {
                            let rows = '';
                            salesPerf.data.forEach((sales, index) => {
                                if (!sales) return;
                                const statusClass = sales.status === 'achieved' ? 'badge-success' : 'badge-warning';
                                const textClass = sales.status === 'achieved' ? 'text-success' : 'text-warning';
                                const targetValue = Number(sales.presentase_kemampuan || 0).toLocaleString('id-ID', { useGrouping: false });
                                rows += `<tr id="row-${sales.kode_karyawan || index}"><td class="text-center">${index + 1}</td><td><strong>${sales.nama || '-'}</strong></td><td class="text-end">${formatRupiah(sales.revenue)}</td><td class="text-center"><div class="input-group input-group-sm" style="max-width: 150px; float: right;"><input type="text" class="form-control text-end target-input ${sales.id_detailPerson ? '' : 'is-invalid'}" value="${targetValue}" data-id-detail="${sales.id_detailPerson || ''}" data-kode-karyawan="${sales.kode_karyawan || ''}" placeholder="Target" ${!sales.id_detailPerson ? 'disabled' : ''}></div><div class="loading-spinner" style="display: none; float: right; margin-right: 10px;"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div><div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;"><i class="menu-icon fa-solid fa-check-circle text-success"></i></div><div class="clearfix"></div></td><td class="text-center ${textClass}"><strong>${sales.percentage ?? 0}%</strong></td><td class="text-center"><span class="badge ${statusClass}">${(sales.status || '').toUpperCase()}</span></td></tr>`;
                            });
                            let htmlTargetTahunanSales = '';
                            const triwulanData = dataDetail.triwulan_data || {};
                            Object.entries(triwulanData).forEach(([label, value]) => {
                                const safeLabel = (label || '').toString().replace(/_/g, ' ');
                                htmlTargetTahunanSales += `<div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h5 class="card-title">${safeLabel}</h5><p class="card-text">Rp ${Number(value || 0).toLocaleString('id-ID')}</p></div></div></div>`;
                            });
                            ContentTrafikSales = `<div class="row"><div class="col"><div class="card shadow-sm mt-3"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th class="text-center" width="5%">No</th><th>Sales</th><th class="text-end" width="20%">Revenue</th><th class="text-end" width="20%">Target (Editable)</th><th class="text-center" width="15%">Persentase</th><th class="text-center" width="15%">Status</th></tr></thead><tbody>${rows || '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>'}</tbody></table></div></div></div></div><div class="col"><div class="card shadow-sm border-0 mt-3"><div class="card-body"><div class="row g-3">${htmlTargetTahunanSales || '<p class="text-muted">Tidak ada data triwulan</p>'}</div></div><div class="mb-2 text-center"><hr><p>Data Triwulan diambil dari tahun ${data.detail_jangka || '-'}</p></div></div></div></div>`;
                        }
                    }
                } else if (allowedAssistantRoutesForPemasukanBersih.includes(condition)) {
                    const bulanIndo = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                    const previousData = dataDetail.previous_quarter?.data || [];
                    ContentTrafikSales = `<div class="mt-4"><div class="row g-4">${previousData.map((item, index) => `<div class="col-12 col-md-6 col-lg-4"><div class="card border-0 shadow-sm h-100 quarter-card"><div class="card-body d-flex flex-column p-4"><div class="d-flex justify-content-between align-items-start mb-3"><div><h6 class="fw-semibold text-muted mb-1">Periode</h6><h5 class="fw-bold mb-0">${bulanIndo[(item.month || 1) - 1] ?? '-'}</h5></div><span class="badge rounded-pill bg-${item.color || 'secondary'} bg-opacity-10 text-${item.color || 'secondary'} px-3 py-2">Laporan</span></div><div class="mb-3"><h3 class="fw-bold text-dark mb-0">Rp ${item.nilai ? Number(item.nilai).toLocaleString('id-ID') : '-'}</h3><small class="text-muted">Total Pemasukan</small></div><div class="flex-grow-1"><p class="text-muted small mb-2 description-text" id="desc-${index}">${item.description ?? '-'}</p>${(item.description && item.description.length > 100) ? `<button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${index}">Lihat Selengkapnya</button>` : ''}</div><div class="d-flex justify-content-end align-items-center mt-4"><a href="${window.KPI_CONFIG.storageBase}/${item.file_paths ? item.file_paths : ''}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" download="${item.file_paths ? 'true' : 'false'}"><i class="menu-icon fa-solid fa-download"></i> Download</a></div></div></div></div>`).join('')}</div></div><style>.quarter-card { border-radius: 16px; transition: all 0.25s ease; background: #ffffff; }.quarter-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }.quarter-card h3 { letter-spacing: 0.5px; }.quarter-card .badge { font-size: 12px; font-weight: 500; }.description-text { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }.description-text.expanded { -webkit-line-clamp: unset; overflow: visible; }.quarter-card .btn { border-radius: 8px; font-size: 13px; }</style>`;
                } else if (allowedAssistantRoutesForKepuasanPelanggan.includes(condition)) {
                    const item = dataDetail || {};
                    ContentTrafikSales = `<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Feedback</span><span class="fw-bold">${item.total_feedback ?? 0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Sesi</span><span class="fw-bold">${item.total_sessions ?? 0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Prediksi</span><span class="fw-bold text-primary">${item.prediction ?? 0}</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA</h6><div class="mb-3"><small class="text-muted d-block">Top Performer</small><span class="fw-bold text-success">${item.top_performer?.label ?? '-'} (${item.top_performer?.value ?? 0})</span></div><div><small class="text-muted d-block">Lowest Performer</small><span class="fw-bold text-danger">${item.lowest_performer?.label ?? '-'} (${item.lowest_performer?.value ?? 0})</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${item.trend === 'up' ? 'success' : (item.trend === 'down' ? 'danger' : 'secondary')}">Trend: ${item.trend || '-'} (${item.trend_value || 0})</span></div><div class="mb-2"><span class="badge bg-${item.consistency === 'stable' ? 'success' : 'warning'}">Konsistensi: ${item.consistency || '-'}</span></div><div><span class="badge bg-${item.target_status === 'on_track' ? 'success' : (item.target_status === 'at_risk' ? 'warning' : 'danger')}">Target: ${item.target_status || '-'}</span></div></div></div></div><div class="col-12"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">KATEGORI PENILAIAN</h6><div class="row text-center">${Object.entries(item.category_scores || {}).map(([key, val]) => `<div class="col"><div class="p-2 border rounded-3"><small class="text-muted d-block">${key}</small><h5 class="fw-bold mb-0">${val ?? 0}</h5></div></div>`).join('')}</div></div></div></div><div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${item.insight ?? '-'}</div></div></div>`;
                } else if (allowedAssistantRoutesForPeningkatanKontribusiPelatihan.includes(condition)) {
                    const classBreakdown = dataDetail.class_breakdown || {};
                    const inhouseData = classBreakdown.Inhouse || {};
                    ContentTrafikSales = `<div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-end mb-4"><div><div class="text-muted small">Total Kelas</div><div class="fs-2 fw-semibold text-dark">${classBreakdown.total ?? 0}</div></div></div><div class="row g-3 mb-4"><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Inixindo</div><div class="fs-4 fw-semibold text-dark">${classBreakdown.kelas_od ?? 0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Orang Luar</div><div class="fs-4 fw-semibold text-dark">${classBreakdown.kelas_ol ?? 0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Offline</div><div class="fs-4 fw-semibold text-dark">${classBreakdown.kelas_offline ?? 0}</div></div></div><div class="col-md-6"><div class="p-3 rounded-3 bg-white border h-100"><div class="text-muted small mb-2">Kelas Online</div><div class="fs-4 fw-semibold text-dark">${classBreakdown.kelas_online ?? 0}</div></div></div></div><div class="p-3 rounded-3 bg-white border"><div class="fw-semibold mb-3">Kelas Inhouse</div><div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Bandung</span><span class="fw-semibold text-dark">${inhouseData.kelas_inhouse ?? 0}</span></div><div class="d-flex justify-content-between py-2"><span class="text-muted small">Luar Bandung</span><span class="fw-semibold text-dark">${inhouseData.kelas_inhouse_luar ?? 0}</span></div></div></div></div>`;
                }

                let contentPieChart = '';
                if (allowedAssistantRoutes.includes(condition)) {
                    const fileUrl = dataDetail.dataManual?.manual_document || '';
                    const fileName = fileUrl ? fileUrl.split('/').pop() : '';
                    const fileExtension = fileName ? fileName.split('.').pop().toLowerCase() : '';
                    const imageExtensions = ['jpg', 'jpeg', 'png'];
                    const pdfExtensions = ['pdf'];
                    let fileContent = '';
                    const fullFileUrl = fileUrl ? `/storage/${fileUrl}` : '';
                    if (imageExtensions.includes(fileExtension)) {
                        fileContent = `<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3"><div class="mb-3" style="max-width: 100%; max-height: 300px;"><img src="${fullFileUrl}" alt="${fileName}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;"></div><div class="mt-2 text-center"><a href="${fullFileUrl}" download="${fileName}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download Gambar</a></div><div class="mt-2 small text-muted"><i class="fa-solid fa-file-image me-1"></i>${fileName}</div></div>`;
                    } else if (pdfExtensions.includes(fileExtension)) {
                        fileContent = `<div class="w-100 h-100 d-flex flex-column"><div class="flex-grow-1 mb-3" style="min-height: 250px;"><iframe src="${fullFileUrl}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe></div><div class="text-center"><a href="${fullFileUrl}" download="${fileName}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i>Download PDF</a></div><div class="mt-2 small text-muted text-center"><i class="fa-solid fa-file-pdf me-1"></i>${fileName}</div></div>`;
                    } else {
                        fileContent = `<div class="text-center py-5"><div class="mb-3"><i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i></div><p class="text-muted mb-3">File tidak dapat ditampilkan</p><p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p></div>`;
                    }
                    contentPieChart = `<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-file me-2"></i>Dokumen Manual</h6><div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">${fileContent}</div><div class="mt-3 small text-muted text-center"><i class="fa-solid fa-info-circle me-1"></i>Klik tombol download untuk menyimpan file</div>`;
                } else if (allowedAssistantRoutesForRupiah.includes(condition)) {
                    const karyawanTerkaitRupiah = karyawanList[0] || {};
                    contentPieChart = `<div class="p-2"><div class="mb-4"><small class="text-muted">Ringkasan performa</small></div><div class="mb-4 p-3 rounded-3 border bg-white shadow-sm"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Bulan Terakhir</div><div class="fw-semibold">${labelBulanTerakhir}</div></div><div class="text-end"><div class="fw-bold fs-5 text-dark">${formatRupiah(nilaiBulanTerakhirRupiah)}</div></div></div></div><div class="mb-3"><div class="text-muted small mb-2 fw-semibold">Top Hari Tertinggi</div>${top3HariTertinggi.map(([tanggal, nilai], index) => `<div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded ${index === 0 ? 'bg-success-subtle' : 'bg-light'}"><div class="d-flex align-items-center gap-2"><span class="badge ${index === 0 ? 'bg-success' : 'bg-secondary'}">#${index + 1}</span><span class="${index === 0 ? 'fw-semibold text-dark' : 'text-muted'}">${formatTanggalSingkat(tanggal)}</span></div><span class="fw-semibold ${index === 0 ? 'text-success' : 'text-dark'}">${formatRupiah(nilai)}</span></div>`).join('')}</div><hr class="my-3"><div class="d-flex align-items-center justify-content-between"><div class="d-flex align-items-center gap-2"><i class="bi bi-person-circle fs-4 text-secondary"></i><div><div class="fw-semibold text-dark">${karyawanTerkaitRupiah?.nama_lengkap ?? '-'}</div><small class="text-muted">${karyawanTerkaitRupiah?.jabatan ?? '-'}</small></div></div><span class="badge bg-light text-dark border">Karyawan</span></div></div>`;
                } else {
                    contentPieChart = `<h6 class="fw-semibold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2"></i>Chart ${condition}</h6><div class="chart-container flex-grow-1"><canvas id="MyChartDoughtnut"></canvas></div>`;
                }

                let contentStatisticChart = '';
                if (allowedAssistantRoutes.includes(condition)) {
                    contentStatisticChart = '';
                } else if (allowedAssistantRoutesForPerformaKPIDepartemen.includes(condition)) {
                    const item = dataDetail || {};
                    const trend = item.trend ?? 'stable';
                    const trendValue = item.trend_value ?? 0;
                    const consistency = item.consistency ?? 'stable';
                    const targetStatus = item.target_status ?? 'behind';
                    const trendColor = trend === 'up' ? 'success' : (trend === 'down' ? 'danger' : 'secondary');
                    const consistencyColor = consistency === 'stable' ? 'success' : 'warning';
                    const targetColor = targetStatus === 'on_track' ? 'success' : (targetStatus === 'at_risk' ? 'warning' : 'danger');
                    const divisionHtml = Object.entries(item.division_breakdown || {}).map(([div, val]) => `<div class="col mb-3"><div class="p-2 border rounded-3"><small class="text-muted d-block">${div}</small><h5 class="fw-bold mb-0">${val ?? 0}%</h5></div></div>`).join('');
                    const riskHtml = (item.risk_divisions && item.risk_divisions.length > 0) ? `<div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-danger mb-3">DIVISI BERISIKO</h6><div class="row">${item.risk_divisions.map(risk => `<div class="col mb-2"><div class="p-2 border rounded-3 text-center"><small class="text-muted d-block">${risk.name || '-'}</small><span class="fw-bold text-danger">${risk.value ?? 0}%</span></div></div>`).join('')}</div></div></div></div>` : '';
                    contentStatisticChart = `<div class="row g-4 mt-1"><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">RINGKASAN</h6><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total KPI</span><span class="fw-bold">${item.total_kpi ?? 0}</span></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Total Divisi</span><span class="fw-bold">${item.total_division ?? 0}</span></div><div class="d-flex justify-content-between"><span class="text-muted">Progress</span><span class="fw-bold text-primary">${item.progress ?? 0}%</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">PERFORMA DIVISI</h6><div class="mb-3"><small class="text-muted d-block">Top Division</small><span class="fw-bold text-success">${item.top_division?.name ?? '-'} (${item.top_division?.value ?? 0}%)</span></div><div><small class="text-muted d-block">Lowest Division</small><span class="fw-bold text-danger">${item.lowest_division?.name ?? '-'} (${item.lowest_division?.value ?? 0}%)</span></div></div></div></div><div class="col-lg-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">STATUS</h6><div class="mb-2"><span class="badge bg-${trendColor}">Trend: ${trend} (${trendValue})</span></div><div class="mb-2"><span class="badge bg-${consistencyColor}">Konsistensi: ${consistency}</span></div><div><span class="badge bg-${targetColor}">Target: ${targetStatus}</span></div></div></div></div><div class="col-6"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold text-secondary mb-3">BREAKDOWN DIVISI</h6><div class="row text-center">${divisionHtml}</div></div></div></div>${riskHtml}<div class="col-12"><div class="alert alert-info rounded-4 shadow-sm mb-0"><i class="fa-solid fa-lightbulb me-2"></i>${item.insight ?? '-'}</div></div></div>`;
                } else if (allowedAssistantRoutesForLaporanAnalisisKeuangan.includes(condition)) {
                    const bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    const analisaData = dataDetail.analisa_data || [];
                    contentStatisticChart = `<div class="row g-4">${analisaData.map(item => {
                        const fileUrl = item.file_paths ? window.KPI_CONFIG.storageBase + '/' + item.file_paths : '#';
                        return `<div class="col-md-4 mt-5"><div class="card border-0 shadow-sm h-100"><div class="mb-2 p-3"><h5 class="fw-bold text-primary mb-1">${bulanIndo[item.month] || '-'}</h5><small class="text-muted">Laporan Analisis Bulanan</small></div><div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;"><div class="mb-3"><p class="mb-0" style="text-align: justify;">${item.description || ''}</p></div></div><div class="mt-auto p-3"><a href="${fileUrl}" class="btn btn-sm btn-outline-primary w-100" ${item.file_paths ? 'download' : ''}><i class="menu-icon fa-solid fa-file-alt"></i></a></div></div></div>`;
                    }).join('')}</div>`;
                } else if (allowedAssistantRoutesForPresentaseGapKompetensi.includes(condition)) {
                    const karyawanGap = Array.isArray(data.karyawan) ? data.karyawan : [];
                    const isKoordinatorItsm = window.KPI_CONFIG.isKoordinatorItsm;
                    const disabledAttr = isKoordinatorItsm ? '' : 'disabled';
                    const submitBtnHtml = isKoordinatorItsm ? '<div class="mt-3"><button type="submit" class="btn btn-primary">Simpan</button></div>' : '';
                    
                    contentStatisticChart = `<div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><h6 class="fw-semibold mb-3">Input Presentase Kemampuan Programmer</h6><form id="formGapKompetensi"><input type="hidden" name="_token" value="${window.KPI_CONFIG.csrfToken}"><div class="row mb-2 fw-semibold text-muted border-bottom pb-2"><div class="col-md-4">Nama Karyawan</div><div class="col-md-4">Kemampuan (%)</div><div class="col-md-4">Standar (%)</div></div>${karyawanGap.map((item, index) => {
                        const kemampuan = parseFloat(item.presentase_kemampuan ?? 0);
                        const standar = parseFloat(item.presentase_standar ?? 100);
                        let badge = '';
                        if (kemampuan === 0) badge = `<span class="badge bg-danger">0%</span>`;
                        else if (kemampuan < standar) badge = `<span class="badge bg-warning text-dark">Not Achieved</span>`;
                        else badge = `<span class="badge bg-success">Achieved</span>`;
                        return `<div class="row mb-2 align-items-center p-2 rounded"><div class="col-md-4 d-flex justify-content-between align-items-center"><span>${item.nama_lengkap ?? '-'}</span>${badge}</div><div class="col-md-4"><input type="number" step="0.1" class="form-control kemampuan-input" name="data[${index}][kemampuan]" value="${kemampuan}" ${disabledAttr}></div><div class="col-md-4"><input type="number" step="0.1" class="form-control standar-input" name="data[${index}][standar]" value="${standar}" ${disabledAttr}></div><input type="hidden" name="data[${index}][id]" value="${item.id || ''}"></div>`;
                    }).join('')}${submitBtnHtml}</form></div></div></div>`;
                } else {
                    contentStatisticChart = `<div class="mt-4"><div class="card shadow-sm border-0 rounded-4"><div class="card-body"><div class="d-flex justify-content-between mb-3"><h6 class="fw-semibold mb-0">Statistik ${condition}</h6><div class="d-flex gap-2"><select class="form-select form-select-sm" id="filterType"><option value="year">Per Tahun</option><option value="month">Per Bulan</option></select><select class="form-select form-select-sm d-none" id="filterMonth"><option value="">Pilih Bulan</option></select></div></div><div style="height:300px"><canvas id="StatisticChart"></canvas></div></div></div></div>`;
                }

                let widthProgress;
                if ((data.tipe_target === "rupiah" || data.tipe_target === "angka") && actualTarget > 0) {
                    widthProgress = Math.min(100, Number(((progressValueRaw / actualTarget) * 100).toFixed(1)));
                } else {
                    widthProgress = Math.min(100, progressValueRaw || 0);
                }

                body.append(`
                    <div class="modal-header border-0 pb-2">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">${data.judul || 'Detail Target'}</h5>
                            <small class="text-muted">${data.jangka_target || '-'}</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-2">
                        <div class="p-4 rounded-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-${bgCard} px-3 py-2">${Tercapai}</span>
                                <span class="text-muted small">
                                    Deadline: <strong>${data.tenggat_waktu || '-'}</strong>
                                </span>
                            </div>

                            <div class="row text-center">
                                <div class="col">
                                    <div class="text-muted small">Target</div>
                                    <div class="fw-bold fs-4">${targetValue}</div>
                                </div>
                                <div class="col">
                                    <div class="text-muted small">Progress</div>
                                    <div class="fw-bold text-${bgCard}" style="font-size:40px">
                                        ${progressValue}
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="text-muted small">Gap</div>
                                    <div class="fw-bold text-${gapText} fs-4">
                                        ${gapValue}
                                    </div>
                                </div>
                            </div>

                            <div class="progress mt-3" style="height:10px;">
                                <div class="progress-bar bg-${bgCard}"
                                    style="width:${widthProgress}%"></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-4 h-100">
                                    <h6 class="fw-semibold mb-3">Informasi KPI</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Divisi</span>
                                        <span class="fw-semibold">${data.divisi_kpi || '-'}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Jabatan</span>
                                        <span class="fw-semibold">${data.jabatan_kpi || '-'}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Pembuat</span>
                                        <span class="fw-semibold">${data.pembuat || '-'}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-4 h-100">
                                    <h6 class="fw-semibold mb-3">Karyawan</h6>
                                    <div style="max-height:140px; overflow:auto;">
                                        ${karyawanHtml}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 p-3 border rounded-4">
                            ${contentPieChart}
                        </div>

                        ${ContentTrafikSales ? `
                            <div class="mt-3">
                                ${ContentTrafikSales}
                            </div>
                        ` : ''}

                        ${contentStatisticChart ? `
                            <div class="mt-3">
                                ${contentStatisticChart}
                            </div>
                        ` : ''}

                        ${contentKalenderInstruktur ? contentKalenderInstruktur : ''}
                    </div>

                    <div class="modal-footer border-0">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                `);

                const NAMA_BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const getNamaBulan = (tahunBulan) => {
                    if (!tahunBulan) return '-';
                    const parts = tahunBulan.split('-');
                    return parts.length >= 2 ? NAMA_BULAN[parseInt(parts[1], 10) - 1] || tahunBulan : tahunBulan;
                };

                setTimeout(function() {
                    const doughnutCtx = document.getElementById('MyChartDoughtnut');
                    if (doughnutCtx && typeof Chart !== 'undefined') {
                        const existingDoughnut = Chart.getChart(doughnutCtx);
                        if (existingDoughnut) existingDoughnut.destroy();
                        new Chart(doughnutCtx, {
                            type: 'doughnut',
                            data: dataPieChart,
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } } } }
                        });
                    }

                    document.querySelectorAll('.target-input').forEach(input => {
                        if (!input) return;
                        let timeout = null;
                        input.addEventListener('input', function() {
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
                                    url: window.KPI_CONFIG.updateTargetPerSales,
                                    method: 'POST',
                                    data: { _token: window.KPI_CONFIG.csrfToken, id_detailPerson: idDetailPerson, kode_karyawan: kodeKaryawan, presentase_kemampuan: newTarget },
                                    success: function(response) {
                                        if (spinner) spinner.style.display = 'none';
                                        el.disabled = false;
                                        if (feedback) { feedback.style.display = 'inline-block'; setTimeout(() => { feedback.style.display = 'none'; }, 2000); }
                                        el.classList.remove('is-invalid'); el.classList.add('is-valid');
                                        setTimeout(() => el.classList.remove('is-valid'), 2000);
                                        if (response?.data) {
                                            const percentageCell = row.querySelector('td:nth-child(5)');
                                            const statusCell = row.querySelector('td:nth-child(6)');
                                            if (percentageCell && response.data.percentage) percentageCell.innerHTML = `<strong class="${response.data.status === 'achieved' ? 'text-success' : 'text-warning'}">${response.data.percentage}%</strong>`;
                                            if (statusCell && response.data.status) { const statusClass = response.data.status === 'achieved' ? 'badge-success' : 'badge-warning'; statusCell.innerHTML = `<span class="badge ${statusClass}">${response.data.status.toUpperCase()}</span>`; }
                                        }
                                    },
                                    error: function() { if (spinner) spinner.style.display = 'none'; el.disabled = false; el.classList.add('is-invalid'); }
                                });
                            }, 1000);
                        });
                        input.addEventListener('blur', function() { const value = (this.value || '').toString().replace(/\./g, ''); if (value) this.value = Number(value).toLocaleString('id-ID'); });
                        input.addEventListener('focus', function() { const value = (this.value || '').toString().replace(/\./g, ''); if (value) this.value = value; });
                    });

                    $(document).off('submit', '#formGapKompetensi').on('submit', '#formGapKompetensi', function(e) {
                        e.preventDefault();
                        $.ajax({
                            url: window.KPI_CONFIG.updateGapKompetensi,
                            method: 'POST',
                            data: $(this).serialize(),
                            success: function() {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Berhasil diupdate.', timer: 2000, showConfirmButton: false }).then(() => {
                                        $('#detailTargetModal').modal('hide');
                                        if (typeof loadContentForm === 'function') loadContentForm();
                                    });
                                }
                            },
                            error: function(err) { if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Gagal!', html: err.responseJSON?.message || 'Terjadi kesalahan' }); }
                        });
                    });

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

                    const monthLabels = Object.keys(monthlyData || {}).map(getNamaBulan);
                    const monthValues = Object.values(monthlyData || {}).map(v => Number(v) || 0);
                    renderStatistic(monthLabels, monthValues, 'Rata-rata');

                    $('#filterType').off('change').on('change', function() {
                        if (this.value === 'month') {
                            $('#filterMonth').removeClass('d-none').empty().append('<option value="">Pilih Bulan</option>');
                            Object.keys(dailyData || {}).forEach(monthKey => { if (monthKey) $('#filterMonth').append(`<option value="${monthKey}">${getNamaBulan(monthKey)}</option>`); });
                            if (statisticChart) statisticChart.destroy();
                        } else {
                            $('#filterMonth').addClass('d-none');
                            renderStatistic(monthLabels, monthValues, 'Rata-rata');
                        }
                    });

                    $('#filterMonth').off('change').on('change', function() {
                        const selectedMonth = this.value;
                        if (!selectedMonth || !dailyData?.[selectedMonth]) return;
                        const dayLabels = Object.keys(dailyData[selectedMonth] || {}).map(d => (d || '').substring(8));
                        const dayValues = Object.values(dailyData[selectedMonth] || {}).map(v => Number(v) || 0);
                        renderStatistic(dayLabels, dayValues, `Tanggal ${getNamaBulan(selectedMonth)}`);
                    });

                    document.querySelectorAll('.btn-toggle-desc').forEach(btn => {
                        if (!btn) return;
                        btn.addEventListener('click', function() {
                            const targetId = this.getAttribute('data-target');
                            const textEl = targetId ? document.getElementById(targetId) : null;
                            if (!textEl) return;
                            if (textEl.classList.contains('expanded')) { textEl.classList.remove('expanded'); this.innerText = 'Lihat Selengkapnya'; }
                            else { textEl.classList.add('expanded'); this.innerText = 'Sembunyikan'; }
                        });
                    });
                }, 150);

                const modalEl = document.getElementById('detailTargetModal');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }

                setTimeout(() => {
                    body.addClass('modal-content-loaded');
                }, 50);
            },
            error: function(err) {
                body.empty();
                body.html(`
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold mb-1 text-danger">Gagal Memuat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <i class="fa-solid fa-circle-exclamation text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h6 class="fw-semibold">Terjadi Kesalahan Jaringan</h6>
                        <p class="text-muted small">${err.responseJSON?.message || 'Silakan coba lagi nanti.'}</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                `);
                body.addClass('modal-content-loaded');
            }
        });
    });

    function generateDetailSkeleton() {
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
            </div>
        `;
    }

    $(document).on('click', '.buttonHapusTarget', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data target ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            customClass: { confirmButton: 'btn btn-gradient-info me-3', cancelButton: 'btn btn-gradient-danger' },
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: window.KPI_CONFIG.hapusTarget.replace('REPLACE_ID', id), 
                    type: 'DELETE',
                    data: { _token: window.KPI_CONFIG.csrfToken },
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data target berhasil dihapus.', showConfirmButton: false, timer: 1500 });
                        loadContentForm(currentPage, $('#searchTarget').val().trim());
                    },
                    error: function(xhr) { 
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.' }); 
                    }
                });
            }
        });
    });

    $(document).on('click', '.buttonForm', function() {
        const route = $(this).data('route');
        const value = $(this).data('value') || '';
        const id = $(this).data('id');
        $('#manualValueId').val(id);
        
        if (typeof allowedDoubleManualRoutes !== 'undefined' && allowedDoubleManualRoutes.includes(route)) {
            $('#singleInputArea').hide();
            $('#doubleInputArea').show();
            let gaji = '', bpjs = '', rekrutmen = '';
            if (value && value.includes(',')) {
                const parts = value.split(',');
                gaji = parts[0] || ''; bpjs = parts[1] || ''; rekrutmen = parts[2] || '';
            } else { gaji = value; bpjs = ''; rekrutmen = ''; }
            
            $('#biaya_gaji_display').val(gaji ? 'Rp ' + formatNumber(getRawNumber(gaji)) : '').trigger('input');
            $('#biaya_bpjs_display').val(bpjs ? 'Rp ' + formatNumber(getRawNumber(bpjs)) : '').trigger('input');
            $('#biaya_rekrutmen_display').val(rekrutmen ? 'Rp ' + formatNumber(getRawNumber(rekrutmen)) : '').trigger('input');
        } else {
            $('#singleInputArea').show();
            $('#doubleInputArea').hide();
            const format = $('#manual_format').val();
            const rawValue = getRawNumber(value);
            let displayValue = formatNumber(rawValue);
            if (format === 'rupiah' && rawValue) displayValue = 'Rp ' + displayValue;
            else if (format === 'persen' && rawValue) displayValue = displayValue + '%';
            $('#manual_value_display').val(displayValue);
            $('#manual_value').val(rawValue);
        }
    });

    $(document).on('change', '#manual_format', function() {
        if ($('#doubleInputArea').is(':visible')) return;
        const format = $(this).val();
        const rawValue = getRawNumber($('#manual_value').val());
        let displayValue = formatNumber(rawValue);
        if (format === 'rupiah' && rawValue) displayValue = 'Rp ' + displayValue;
        else if (format === 'persen' && rawValue) displayValue = displayValue + '%';
        $('#manual_value_display').val(displayValue);
    });

    $('#formManualValue').on('submit', function(e) {
        e.preventDefault();
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');
        const formData = new FormData(this);
        
        if ($('#doubleInputArea').is(':visible')) {
            formData.set('biaya_gaji_tahunan', $('#biaya_gaji_tahunan').val());
            formData.set('biaya_bpjs_tahunan', $('#biaya_bpjs_tahunan').val());
            formData.set('biaya_rekrutmen_tahunan', $('#biaya_rekrutmen_tahunan').val());
        } else {
            formData.set('manual_value', $('#manual_value').val());
            formData.set('manual_format', $('#manual_format').val());
        }
        
        $.ajax({
            url: window.KPI_CONFIG.manualValue,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                $('#modalFormManual').modal('hide');
                resetFormManual();
                $submitBtn.prop('disabled', false).html(originalText);
                loadContentForm(currentPage, $('#searchTarget').val().trim());
            },
            error: function(xhr) {
                $submitBtn.prop('disabled', false).html(originalText);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};
                    const msg = Object.values(errors).map(e => e[0]).join('<br>');
                    alert(msg);
                } else {
                    alert('Terjadi kesalahan sistem: ' + (xhr.statusText || 'Unknown error'));
                }
            }
        });
    });

    $('#targetForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        const judul = $('#judul_kpi').val().trim();
        if (!judul) {
            Swal.fire('Peringatan', 'Judul KPI wajib diisi.', 'warning');
            return;
        }
        const form = $(this);
        const formData = new FormData(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#modalBuatTarget').modal('hide');
                form[0].reset();
                $('.select2').val(null).trigger('change');
                $('#detail_jangka_container').empty();
                $('#jangka_target_display, #tipe_target_display, #nilai_target_display').val('');
                $('#jangka_target_hidden, #tipe_target_hidden, #nilai_target_hidden').val('');
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: response.message || 'Target berhasil dibuat.', timer: 2000, showConfirmButton: false
                }).then(() => {
                    loadContentForm();
                });
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Gagal!', html: msg });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Document Preview Logic
    document.getElementById('manual_document').addEventListener('change', function(e) {
        const preview = document.getElementById('documentPreview');
        preview.innerHTML = '';
        const file = e.target.files[0];
        if (!file) return;
        const fileType = file.type;
        if (fileType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.createElement('img');
                img.src = event.target.result;
                img.classList.add('img-fluid', 'rounded');
                img.style.maxHeight = '300px';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        } else if (fileType === 'application/pdf') {
            const pdfInfo = document.createElement('div');
            pdfInfo.innerHTML = `<p class="mb-2"><strong>PDF:</strong> ${file.name}</p><embed src="${URL.createObjectURL(file)}" type="application/pdf" width="100%" height="300px">`;
            preview.appendChild(pdfInfo);
        } else {
            preview.innerHTML = `<p><strong>File:</strong> ${file.name}</p>`;
        }
    });

    // Import Form Logic (Fetch API)
    const formImport = document.getElementById('formImport');
    if (formImport) {
        formImport.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(formImport);
            const isDryRun = document.getElementById('dryRun')?.checked;
            const btnSubmit = document.getElementById('btnSubmitImport');
            const importPreview = document.getElementById('importPreview');
            const errorSummary = document.getElementById('errorSummary');

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Memproses...';
            importPreview.classList.remove('d-none');
            errorSummary.classList.add('d-none');

            fetch(formImport.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.success, 'success');
                    if (!isDryRun) {
                        setTimeout(() => { window.location.reload(); }, 2000);
                    } else {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fa-solid fa-upload me-1"></i>Import Sekarang';
                        importPreview.classList.add('d-none');
                    }
                } else {
                    showError(data.errors || { file: ['Terjadi kesalahan tidak diketahui'] });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError({ file: ['Gagal terhubung ke server. Periksa koneksi internet.'] });
            })
            .finally(() => {
                if (!isDryRun) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fa-solid fa-upload me-1"></i>Import Sekarang';
                    importPreview.classList.add('d-none');
                }
            });
        });
    }

    function showAlert(message, type) {
        if (type === undefined) type = 'success';
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(alertDiv);
        setTimeout(() => { if (alertDiv.parentNode) alertDiv.parentNode.removeChild(alertDiv); }, 5000);
    }

    function showError(errors) {
        document.getElementById('importPreview').classList.add('d-none');
        const errorSummary = document.getElementById('errorSummary');
        errorSummary.classList.remove('d-none');
        const errorList = errorSummary.querySelector('ul');
        errorList.innerHTML = '';
        const allErrors = errors.file || errors.preview || [];
        allErrors.forEach(err => {
            const li = document.createElement('li');
            li.textContent = err;
            errorList.appendChild(li);
        });
        document.getElementById('btnSubmitImport').disabled = false;
        document.getElementById('btnSubmitImport').innerHTML = '<i class="fa-solid fa-upload me-1"></i>Import Sekarang';
    }
});

// ==========================================================================
// DATA LOADING & RENDERING FUNCTIONS
// ==========================================================================
function loadContentForm(page = 1, search = '') {
    // Tampilkan skeleton, sembunyikan konten asli
    $('#kpi-skeleton-view').removeClass('d-none');
    $('#kpi-real-view').addClass('d-none');
    
    // Hapus min-height yang menyebabkan space kosong
    $('#dt-space-reserver').css('min-height', 'auto');
    
    $('#content_target').empty();
    $('#paginationContainer').empty();

    $.ajax({
        url: window.KPI_CONFIG.getDataTarget,
        type: 'GET',
        data: { page: page, per_page: itemsPerPage, search: search },
        cache: false,
        success: function(response) {
            $('#kpi-skeleton-view').addClass('d-none');
            $('#kpi-real-view').removeClass('d-none');
            
            if (!response.detail || response.detail.length === 0) {
                $('#content_target').html('<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data target</td></tr>');
                allTargetData = [];
                currentFilteredData = [];
                renderPagination();
                return;
            }
            
            allTargetData = processRawData(response.detail);
            currentFilteredData = [...allTargetData];
            currentPage = response.pagination.current_page;
            totalPages = response.pagination.last_page;
            totalRecords = response.pagination.total;
            
            renderTable();
            renderPagination();
        },
        error: function(xhr) {
            $('#kpi-skeleton-view').addClass('d-none');
            $('#kpi-real-view').removeClass('d-none');
            $('#content_target').html('<tr><td colspan="10" class="text-center py-4 text-danger">Gagal memuat data</td></tr>');
        }
    });
}

function processRawData(details) {
    const groupedByPembuat = {};
    details.forEach(item => {
        const idPembuat = item.id_pembuat;
        if (!groupedByPembuat[idPembuat]) groupedByPembuat[idPembuat] = { targets: [] };
        groupedByPembuat[idPembuat].targets.push(item);
    });

    const processed = [];
    const nowDate = new Date();
    const routesTargetPerPeserta = ['sertifikasi kompetensi internal', 'pelatihan kompetensi eksternal'];

    Object.entries(groupedByPembuat).forEach(([idPembuat, group]) => {
        group.targets.forEach(function(item) {
            const isPerPesertaRoute = routesTargetPerPeserta.includes(item.asistant_route);
            const totalPeserta = item.total_peserta || (Array.isArray(item.karyawan) ? item.karyawan.length : 0) || 0;
            const actualTarget = (isPerPesertaRoute && totalPeserta > 0) ? totalPeserta * (parseFloat(item.nilai_target) || 0) : (parseFloat(item.nilai_target) || 0);

            let formattedTarget = item.nilai_target;
            if (item.tipe_target === 'persen') formattedTarget = `${item.nilai_target}%`;
            else if (item.tipe_target === 'rupiah') formattedTarget = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.nilai_target);
            else if (item.tipe_target === 'angka') formattedTarget = isPerPesertaRoute ? `${actualTarget} <small class="text-muted">(${totalPeserta} peserta × ${item.nilai_target})</small>` : `${item.nilai_target}`;

            let jabatanDisplay = '-';
            if (item.jabatan) {
                const jabatanList = Array.isArray(item.jabatan) ? item.jabatan : [item.jabatan];
                if (jabatanList.length > 0) {
                    jabatanDisplay = jabatanList.length === 1 ? jabatanList[0] : jabatanList.map(j => String(j).substring(0, 4) + '...').join(', ');
                }
            }

            let statusText = '';
            let badgeClass = 'bg-secondary';
            let progressNumeric = parseFloat(item.progress) || 0;
            let progressValueDisplay = progressNumeric;
            const progress = parseFloat(item.progress) || 0;

            if (item.tipe_target === 'rupiah') {
                const percentProgress = actualTarget > 0 ? Math.min(((progress / actualTarget) * 100).toFixed(1), 100) : 0;
                progressNumeric = parseFloat(percentProgress);
                progressValueDisplay = `${percentProgress}%`;
            } else if (item.tipe_target === 'angka') {
                const percentProgress = actualTarget > 0 ? Math.min(((progress / actualTarget) * 100).toFixed(1), 100) : 0;
                progressNumeric = percentProgress;
                progressValueDisplay = `${percentProgress}%`;
            } else {
                progressValueDisplay = Math.round(progressNumeric) + '%';
            }

            const lengthProgress = actualTarget > 0 ? Math.min(Math.round((progress / actualTarget) * 100), 100) : 0;
            let isTargetReached = false;
            if (item.tipe_target === 'angka') isTargetReached = (item.manual_value ?? 0) >= actualTarget;
            else if (item.tipe_target === 'rupiah') isTargetReached = progress >= actualTarget;
            else isTargetReached = progressNumeric >= item.nilai_target;

            let deadline;
            if (item.tenggat_waktu.includes('-')) {
                const parts = item.tenggat_waktu.split('-');
                if (parts[0].length === 4) deadline = new Date(parts[0], parts[1] - 1, parts[2]);
                else deadline = new Date(parts[2], parts[1] - 1, parts[0]);
            } else {
                deadline = new Date(item.tenggat_waktu);
            }

            const isOverdue = nowDate > deadline;
            const isSameYear = nowDate.getFullYear() === deadline.getFullYear();

            if (!isOverdue && isSameYear) { statusText = 'Sedang Berjalan'; badgeClass = 'bg-warning text-dark'; }
            else if (isOverdue) { statusText = isTargetReached ? 'Selesai' : 'Gagal'; badgeClass = isTargetReached ? 'bg-success' : 'bg-danger'; }
            else { statusText = 'Dalam Progress'; badgeClass = 'bg-warning text-dark'; }

            let buttonIsiForm = '';
            if (typeof allowedAssistantRoutes !== 'undefined' && allowedAssistantRoutes.includes(item.asistant_route)) {
                buttonIsiForm = `<li><button type="button" class="dropdown-item text-dark buttonForm" data-id="${item.id}" data-value="${item.manual_value ?? 0}" data-route="${item.asistant_route}" data-bs-toggle="modal" data-bs-target="#modalFormManual"><i class="fa-solid fa-file-pen me-2"></i> Isi Data</button></li>`;
            }

            const routeUrl = (typeof assistantRouteUrlMap !== 'undefined' && assistantRouteUrlMap[item.asistant_route]) || '#';
            const progressBg = badgeClass === 'bg-success' ? '#28a745' : badgeClass === 'bg-danger' ? '#dc3545' : '#ffc107';
            const jangkaDisplay = item.jangka_target.charAt(0).toUpperCase() + item.jangka_target.slice(1);

            processed.push({
                id: item.id, judul: item.judul, jangkaDisplay, statusText, badgeClass, formattedTarget, jabatanDisplay,
                divisi: item.divisi || '-', pembuat: item.pembuat || '-', lengthProgress, progressValueDisplay, progressBg,
                tenggat_waktu: item.tenggat_waktu, buttonIsiForm, routeUrl
            });
        });
    });
    return processed;
}

function renderTable() {
    const $contentTarget = $('#content_target');
    
    if (currentFilteredData.length === 0) {
        $contentTarget.html('<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data yang ditemukan</td></tr>');
        return;
    }

    let allRowsHtml = '';
    
    currentFilteredData.forEach(item => {
        allRowsHtml += `
            <tr>
                <td class="fw-bold buttonDetailTarget" data-id="${item.id}" style="cursor: pointer;">${item.judul}</td>
                <td class="buttonDetailTarget" data-id="${item.id}" style="cursor: pointer;"><span class="badge bg-light text-primary border border-primary">${item.jangkaDisplay}</span></td>
                <td class="buttonDetailTarget" data-id="${item.id}" style="cursor: pointer;"><span class="badge ${item.badgeClass}">${item.statusText}</span></td>
                <td class="buttonDetailTarget" data-id="${item.id}" style="cursor: pointer;">${item.formattedTarget}</td>
                <td class="buttonDetailTarget" data-id="${item.id}" style="cursor: pointer;">${item.jabatanDisplay}</td>
                <td class="buttonDetailTarget" data-id="${item.id}" style="cursor: pointer;">${item.divisi}</td>
                <td class="buttonDetailTarget" data-id="${item.id}" style="cursor: pointer;">${item.pembuat}</td>
                <td class="buttonDetailTarget" data-id="${item.id}" style="cursor: pointer; min-width:150px;">
                    <div class="progress" style="height: 12px;"><div class="progress-bar" style="width: ${item.lengthProgress}%; background: ${item.progressBg}"></div></div>
                    <small>${item.progressValueDisplay}</small>
                </td>
                <td class="buttonDetailTarget" data-id="${item.id}" style="cursor: pointer;"><small><i class="fa-solid fa-calendar-days me-1"></i> ${item.tenggat_waktu}</small></td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Aksi</button>
                        <ul class="dropdown-menu">
                            ${item.buttonIsiForm}
                            <li><a href="${item.routeUrl}" class="dropdown-item text-dark" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Lihat Detail KPI</a></li>
                            <li><button type="button" class="dropdown-item text-danger buttonHapusTarget" data-id="${item.id}"><i class="fa-solid fa-trash-can me-2"></i> Hapus</button></li>
                        </ul>
                    </div>
                </td>
            </tr>`;
    });
    
    $contentTarget.html(allRowsHtml);
}

function renderPagination() {
    const $container = $('#paginationContainer');
    $container.empty();
    if (totalPages <= 1) return;

    let paginationHtml = '<ul class="pagination justify-content-center mb-0">';
    paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">Previous</a></li>`;
    for (let i = 1; i <= totalPages; i++) {
        paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a></li>`;
    }
    paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">Next</a></li>`;
    paginationHtml += '</ul>';
    
    const from = (currentPage - 1) * itemsPerPage + 1;
    const to = Math.min(currentPage * itemsPerPage, totalRecords);
    paginationHtml += `<div class="text-muted small mt-2 text-center">Menampilkan ${from}-${to} dari ${totalRecords} data</div>`;
    
    $container.html(paginationHtml);
}

window.changePage = function(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    const searchVal = $('#searchTarget').val().trim();
    loadContentForm(page, searchVal);
    $('html, body').animate({ scrollTop: $('#content_target').offset().top - 100 }, 300);
};