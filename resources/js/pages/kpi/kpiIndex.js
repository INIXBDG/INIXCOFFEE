const fmt1 = (v) => (Number(v) || 0).toFixed(1);

let allTargetData = [];
let currentFilteredData = [];
let currentPage = 1;
let totalPages = 1;
let totalRecords = 0;
const itemsPerPage = 10;

const allowedAssistantRoutes = [
    'dorong inovasi pelayanan',
    'inisiatif efisiensi keuangan',
    'mengurangi manual work dan error',
];

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
    "timeline webinar": "/timeline",
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

$(document).ready(function() {
    $('#jabatan').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', dropdownParent: $('#modalBuatTarget') });
    $('#karyawan').select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', dropdownParent: $('#modalBuatTarget') });

    $(document).on('click', '.buttonHapusTarget, .buttonForm', function(e) { e.stopPropagation(); });

    $('#updateKaryawan').select2({
        theme: 'bootstrap-5', allowClear: true, width: '100%',
        dropdownParent: $('#modalUpdateTarget')
    });

    $(document).on('click', '.buttonPanduan', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const route = ($(this).data('route') || '').toString().toLowerCase().trim();
        const judul = $(this).data('judul') || 'Target KPI';

        const panduanMap = {
            'pelaksanaan kegiatan karyawan': 'pelaksanaan_kegiatan_karyawan',
            'pengeluaran biaya karyawan': 'pengeluaran_biaya_karyawan',
            'administrasi karyawan': 'administrasi_karyawan',
            'mengukur kualitas aplikasi agar minim bug': 'mengukur_kualitas_aplikasi_agar_minim_bug',
            'ketepatan waktu penyelesaian fitur': 'ketepatan_waktu_penyelesaian_fitur',
            'keberhasilan support memenuhi sla': 'tingkat_keberhasilan_support_memenuhi_sla',
            'kualitas layanan exam': 'kualitas_layanan_exam',
            'meningkatkan kepuasan dan loyalitas peserta/client': 'meningkatkan_kepuasan_dan_loyalitas_peserta',
            'availability sistem internal kritis': 'availability_sistem_internal_kritis',
            'persentase gap kompetensi tim terhadap standar skill': 'persentase_gap_kompetensi',
            'konsistensi campaign digital': 'konsistensi_campaign_digital',
            'ketepatan waktu po': 'ketepatan_waktu_po',
            'kualitas dokumentasi support dan proctor': 'kualitas_dokumentasi_support_dan_proctor',
            'laporan mom': 'laporan_mom',
            'akurasi kelengkapan data penjualan': 'akurasi_kelengkapan_data_penjualan',
            'todo administrasi': 'todo_administrasi',
            'peserta puas dengan pelayanan dan fasilitas training': 'peserta_puas_dengan_pelayanan_dan_fasilitas_training',
            'dorong inovasi pelayanan': 'dorong_inovasi_pelayanan',
            'penanganan komplain perseta': 'penanganan_komplain_peserta',
            'report persiapan kelas': 'report_persiapan_kelas',
            'kepuasan client itsm': 'kepuasan_client_itsm',
            'inovation adaption rate': 'inovation_adaption_rate',
            'perbaikan kendaraan': 'perbaikan_kendaraan',
            'kontrol pengeluaran transportasi': 'kontrol_pengeluaran_transportasi',
            'report kondisi kendaraan': 'report_kondisi_kendaraan',
            'feedback kenyamanan berkendaran': 'feedback_kenyamanan_berkendara',
            'pengembangan kurikulum pelatihan': 'pengembangan_kurikulum_pelatihan',
            'peningkatan knowledge sharing': 'peningkatan_knowledge_sharing',
            'peningkatan kontribusi pelatihan': 'peningkatan_kontribusi_pelatihan',
            'evaluasi kinerja instruktur': 'evaluasi_kinerja_instruktur',
            'pembuatan artikel': 'pembuatan_artikel',
            'outstanding': 'outstanding',
            'inisiatif efisiensi keuangan': 'inisiatif_efisiensi_keuangan',
            'mengurangi manual work dan error': 'mengurangi_manual_work_dan_error',
            'laporan analisis keuangan': 'laporan_analisis_keuangan',
            'pencairan biaya operasional': 'pencairan_biaya_operasional',
            'penyelesaian tagihan perusahaan': 'penyelesaian_tagihan_perusahaan',
            'akurasi pencatatan masuk': 'akurasi_pencatatan_masuk',
            'pemasukan kotor': 'pemasukan_kotor',
            'pemasukan bersih': 'pemasukan_bersih',
            'target penjualan project tahunan': 'target_penjualan_project_tahunan',
            'kepuasan pelanggan': 'kepuasan_pelanggan',
            'rasio biaya operasional terhadap revenue': 'rasio_biaya_operasional_terhadap_revenue',
            'performa kpi departemen': 'performa_kpi_departemen',
            'feedback kebersihan dan kenyamanan': 'feedback_kebersihan_dan_kenyamanan',
            'penyelesaian tugas harian': 'penyelesaian_tugas_harian',
            'efektifitas digital marketing': 'efektifitas_digital_marketing',
            'timeline webinar': 'timeline_webinar',
            'presentase kinerja instruktur': 'presentase_kinerja_instruktur',
            'kepuasan peserta pelatihan': 'kepuasan_peserta_pelatihan',
            'upseling lanjutan materi': 'upseling_lanjutan_materi',
            'sertifikasi kompetensi internal': 'sertifikasi_kompetensi_internal',
            'pelatihan kompetensi eksternal': 'pelatihan_kompetensi_eksternal',
            'target penjualan tahunan': 'target_penjualan_tahunan',
            'biaya akuisisi perclient': 'biaya_akuisisi_perclient',
            'peningkatan kemampuan kompetensi sales': 'peningkatan_kemampuan_kompetensi_sales',
            'meningkatkan revenue perusahaan': 'meningkatkan_revenue_perusahaan',
            'customer acquisition cost': 'customer_acquisition_cost',
            'evaluasi kinerja sales': 'evaluasi_kinerja_sales',
            'pendapatan penjualan project': 'pendapatan_penjualan_project',
            'leads project': 'leads_project',
        };

        const key = panduanMap[route] || null;

        const $modal = $('#modalPanduan');
        const $body  = $('#bodyContentPanduan');
        const $title = $('#panduanTitle');

        $title.text('Panduan: ' + judul);
        $body.html(`
            <div class="text-center py-5">
                <i class="fa-solid fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                <p class="text-muted mb-0">Memuat panduan...</p>
            </div>
        `);

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPanduan')).show();
        } else {
            $modal.modal('show');
        }

        if (!key) {
            $body.html(`
                <div class="alert alert-secondary text-center py-5 mb-0">
                    <i class="fa-solid fa-book-open fa-2x mb-3 text-muted"></i>
                    <p class="mb-0">Panduan untuk target ini belum tersedia.</p>
                    <small class="text-muted d-block mt-2">Route: <code>${route || '-'}</code></small>
                </div>
            `);
            return;
        }

        $.ajax({
            url: `/kpi-data/panduan/${key}`,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                $body.html(res.html || '<div class="alert alert-warning">Konten panduan kosong.</div>');
                if (res.title) {
                    $title.text('Panduan: ' + res.title);
                }
            },
            error: function () {
                $body.html(`
                    <div class="alert alert-danger text-center py-4 mb-0">
                        <i class="fa-solid fa-circle-exclamation fa-2x mb-3"></i>
                        <p class="mb-0">Gagal memuat panduan. Silakan coba lagi.</p>
                    </div>
                `);
            }
        });
    });

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
                routeSelect.trigger('change');
            }

            const jabatanSelect = $('#jabatan');
            if (jabatanSelect.length && response.jabatan_list) {
                const currentSelectedJabatan = jabatanSelect.val() || [];

                jabatanSelect.empty();
                response.jabatan_list.forEach(function(jab) {
                    jabatanSelect.append(`<option value="${jab}">${jab}</option>`);
                });

                jabatanSelect.val(currentSelectedJabatan);

                jabatanSelect.trigger('change');
            }
        }
    });

    let xhrKaryawan = null, xhrRoutes = null;
    $('#jabatan').off('change').on('change', function() {
        const selectedJabatans = $(this).val() || [];
        const karyawanSelect = $('#karyawan');
        const assistantRouteSelect = $('#assistant_route');

        karyawanSelect.empty().trigger('change');
        assistantRouteSelect.empty().prop('disabled', true).html('<option selected disabled>-- Memuat routes... --</option>').trigger('change');
        $('#detail_jangka_container').empty();

        if (selectedJabatans.length === 0) {
            assistantRouteSelect.html('<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>').trigger('change');
            return;
        }

        if (xhrKaryawan) xhrKaryawan.abort();
        xhrKaryawan = $.ajax({
            url: window.KPI_CONFIG.getKaryawanByJabatan,
            type: 'GET',
            data: { jabatan: selectedJabatans },
            success: function(response) {
                karyawanSelect.empty();
                if (response && response.length > 0) {
                    response.forEach(function(emp) {
                        karyawanSelect.append(`<option value="${emp.id}">${emp.text}</option>`);
                    });
                }
                karyawanSelect.trigger('change');
            }
        });

        if (xhrRoutes) xhrRoutes.abort();
        xhrRoutes = $.ajax({
            url: window.KPI_CONFIG.getRoutesByJabatan,
            type: 'GET',
            data: { jabatan: selectedJabatans },
            success: function(response) {
                assistantRouteSelect.empty();
                assistantRouteSelect.append('<option value="" selected disabled>-- Pilih Assistant Route --</option>');

                if (response && response.length > 0) {
                    response.forEach(route => {
                        assistantRouteSelect.append(`<option value="${route.asistant_route}">${route.asistant_route}</option>`);
                    });
                    assistantRouteSelect.prop('disabled', false);
                } else {
                    assistantRouteSelect.append('<option disabled>-- Tidak ada route tersedia untuk jabatan ini --</option>');
                    assistantRouteSelect.prop('disabled', true);
                }

                assistantRouteSelect.trigger('change');
            },
            error: function(xhr, status) {
                if (status === 'abort') return;
                assistantRouteSelect.html('<option disabled>-- Gagal memuat routes --</option>').trigger('change');
                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Gagal memuat daftar assistant route', 'error');
            }
        });
    });

    loadContentForm();

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

    const $assistantRoute = $('#assistant_route');
    $assistantRoute.prop('disabled', true).html('<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>');

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
        body.html(KpiDetail.skeleton('index'));

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

                const dateNow = new Date(window.KPI_CONFIG.dateNow);
                const startOfYear = new Date(window.KPI_CONFIG.startOfYear);
                const tenggatWaktu = data.tenggat_waktu || '';
                let Tercapai;

                const currentDate = new Date(dateNow);
                const startDate = new Date(startOfYear);
                const deadlineDate = new Date(tenggatWaktu);

                const isInverse = dataDetail.is_inverse === true
                    || data.condition === 'persentase gap kompetensi tim terhadap standar skill';

                const progressVal = dataDetail.progress ?? 0;
                const targetVal   = data.nilai_target ?? 0;

                const capai = isInverse
                    ? progressVal <= targetVal
                    : progressVal >= targetVal;

                if (isNaN(currentDate) || isNaN(startDate) || currentDate < startDate) {
                    Tercapai = "Belum Dimulai";
                } else if (!isNaN(deadlineDate) && (currentDate > deadlineDate || currentDate.getTime() === deadlineDate.getTime())) {
                    Tercapai = capai ? "Mencapai Target" : "Target Gagal";
                } else {
                    Tercapai = capai ? "Mencapai Target" : "Sedang Berjalan";
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
                    progressValue = fmt1(progressValueRaw) + ' %';
                    gapValue = fmt1(gapValueRaw) + ' %';
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

                let bgCard = Tercapai === "Mencapai Target" ? "success" : Tercapai === "Target Gagal" ? "danger" : Tercapai === "Sedang Berjalan" ? "warning" : "secondary";
                let gapText = Tercapai === "Mencapai Target" ? "success" : "danger";

                let widthProgress;
                if ((data.tipe_target === "rupiah" || data.tipe_target === "angka") && actualTarget > 0) {
                    widthProgress = Math.min(100, Number(((progressValueRaw / actualTarget) * 100).toFixed(1)));
                } else {
                    widthProgress = Math.min(100, progressValueRaw || 0);
                }

                const contents = KpiDetail.buildContents(data, 'index');

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
                                        ${contents.karyawanHtml}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 p-3 border rounded-4">
                            ${contents.pieChart}
                        </div>

                        ${contents.trafikSales ? `
                            <div class="mt-3">
                                ${contents.trafikSales}
                            </div>
                        ` : ''}

                        ${contents.kontribusiSales ? `
                            <div class="mt-3">
                                ${contents.kontribusiSales}
                            </div>
                        ` : ''}

                        ${contents.kalenderInstruktur ? contents.kalenderInstruktur : ''}

                        ${contents.pesertaPuas}
                    </div>

                    <div class="modal-footer border-0">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                `);

                setTimeout(function() {
                    KpiDetail.afterRender(data, 'index', contents);
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

    $(document).on('click', '.buttonUpdateTarget', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        const item = allTargetData.find(t => t.id == id);
        if (!item) return;

        $('#updateTargetId').val(item.id);
        $('#updateJudul').val(item.judul);
        $('#updateDeskripsi').val(item.deskripsi);
        $('#updateRoute').val(item.asistant_route);
        $('#updateJabatan').val(item.jabatanRaw.join(', '));
        $('#updateJangka').val(item.jangkaDisplay);
        $('#updateTipe').val(item.tipeLabel);
        $('#updateNilai').val(item.nilaiPlain);

        const tahun = parseInt(item.detailJangka, 10);
        const tahunan = String(item.jangkaRaw || '').toLowerCase() === 'tahunan' && !isNaN(tahun);
        $('#updateTahunWrap').toggleClass('d-none', !tahunan);
        $('#updateTahun').val(tahunan ? tahun : '').prop('disabled', !tahunan);

        const $k = $('#updateKaryawan');
        const selectedIds = item.karyawanTerpilih.map(k => String(k.id));
        $k.empty().prop('disabled', true).trigger('change');

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUpdateTarget')).show();

        $.get(window.KPI_CONFIG.getKaryawanByJabatan, { jabatan: item.jabatanRaw })
            .done(function(list) {
                // gabungkan kandidat + yang sudah terpilih (kalau ada yang sudah nonaktif, tetap muncul)
                const map = new Map();
                (list || []).forEach(k => map.set(String(k.id), k.text));
                item.karyawanTerpilih.forEach(k => map.set(String(k.id), k.text));

                map.forEach((text, kid) => {
                    $k.append(new Option(text, kid, false, selectedIds.includes(kid)));
                });
                $k.prop('disabled', false).trigger('change');
            })
            .fail(function() {
                item.karyawanTerpilih.forEach(k => {
                    $k.append(new Option(k.text, k.id, true, true));
                });
                $k.prop('disabled', false).trigger('change');
            });
    });

    $('#formUpdateTarget').on('submit', function(e) {
        e.preventDefault();
        const id = $('#updateTargetId').val();
        const $btn = $('#btnUpdateTarget');
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> <span>Menyimpan...</span>');

        const payload = {
            _token: window.KPI_CONFIG.csrfToken,
            judul_kpi: $('#updateJudul').val().trim(),
            deskripsi_kpi: $('#updateDeskripsi').val().trim(),
            karyawan: $('#updateKaryawan').val() || []
        };
        
        if (!$('#updateTahun').prop('disabled') && $('#updateTahun').val()) {
            payload.detail_jangka = $('#updateTahun').val();
        }

        $.ajax({
            url: window.KPI_CONFIG.updateTarget.replace('REPLACE_ID', id),
            type: 'POST',
            data: payload,
            success: function(res) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUpdateTarget')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message || 'Data berhasil diperbarui.', timer: 1500, showConfirmButton: false });
                loadContentForm(currentPage, $('#searchTarget').val().trim());
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat memperbarui data.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                Swal.fire({ icon: 'error', title: 'Gagal!', html: msg });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
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
                form.find('.select2').val(null).trigger('change');
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

    const manualDoc = document.getElementById('manual_document');
    if (manualDoc) {
        manualDoc.addEventListener('change', function(e) {
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
    }

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

function loadContentForm(page = 1, search = '') {
    $('#kpi-skeleton-view').removeClass('d-none');
    $('#kpi-real-view').addClass('d-none');

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
                const percentProgress = fmt1(actualTarget > 0 ? Math.min((progress / actualTarget) * 100, 100) : 0);
                progressNumeric = parseFloat(percentProgress);
                progressValueDisplay = `${percentProgress}%`;
            } else if (item.tipe_target === 'angka') {
                const percentProgress = fmt1(actualTarget > 0 ? Math.min((progress / actualTarget) * 100, 100) : 0);
                progressNumeric = parseFloat(percentProgress);
                progressValueDisplay = `${percentProgress}%`;
            } else {
                progressValueDisplay = fmt1(progressNumeric) + '%';
            }

            let lengthProgress = actualTarget > 0
                ? Math.min(Math.round((progress / actualTarget) * 100), 100)
                : 0;

            let isTargetReached = false;

            const isGapRoute = item.asistant_route === 'persentase gap kompetensi tim terhadap standar skill';

            if (isGapRoute) {
                const belumDinilai = item.progress === null || item.progress === undefined;
                const gapValue = parseFloat(item.progress);
                const targetGap = parseFloat(item.nilai_target) || 10;

                if (belumDinilai || isNaN(gapValue)) {
                    isTargetReached = false;
                    lengthProgress = 0;
                    progressValueDisplay = 'Belum dinilai';
                } else {
                    isTargetReached = gapValue <= targetGap;
                    lengthProgress = Math.max(0, Math.min(100, Math.round(gapValue)));
                    progressValueDisplay = `Gap ${fmt1(gapValue)}%`;
                }
            } else {
                if (item.tipe_target === 'angka') {
                    isTargetReached = (item.manual_value ?? 0) >= actualTarget;
                } else if (item.tipe_target === 'rupiah') {
                    isTargetReached = progress >= actualTarget;
                } else {
                    isTargetReached = progressNumeric >= item.nilai_target;
                }
            }

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
                tenggat_waktu: item.tenggat_waktu, buttonIsiForm, routeUrl, asistant_route: item.asistant_route,
                deskripsi: item.deskripsi === '-' ? '' : (item.deskripsi || ''),
                detailJangka: item.detail_jangka,
                jabatanRaw: Array.isArray(item.jabatan) ? item.jabatan : (item.jabatan ? [item.jabatan] : []),
                karyawanTerpilih: item.karyawan_terpilih || [],
                tipeLabel: item.tipe_target === 'persen' ? 'Persen (%)'
                        : item.tipe_target === 'rupiah' ? 'Rupiah (Nilai Keuangan)'
                        : 'Angka (Unit, Jumlah, dll)',
                nilaiPlain: item.tipe_target === 'rupiah'
                        ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.nilai_target)
                        : item.tipe_target === 'persen' ? `${item.nilai_target} %`
                        : new Intl.NumberFormat('id-ID').format(item.nilai_target),
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
                            <li>
                                <a href="${item.routeUrl}" class="dropdown-item text-dark" target="_blank">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Lihat Detail KPI
                                </a>
                            </li>
                            <li>
                                <button type="button"
                                        class="dropdown-item text-info buttonPanduan"
                                        data-route="${item.asistant_route || ''}"
                                        data-judul="${item.judul || ''}">
                                    <i class="fa-solid fa-book-open me-2"></i> Panduan
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item text-primary buttonUpdateTarget" data-id="${item.id}">
                                    <i class="fa-solid fa-pen-to-square me-2"></i> Update Data
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item text-danger buttonHapusTarget" data-id="${item.id}">
                                    <i class="fa-solid fa-trash-can me-2"></i> Hapus
                                </button>
                            </li>
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

function escHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function syncPanduanPdfState() {
    const $jabatan = $('#panduanPdfOpsi .cek-jabatan');
    const total = $jabatan.length;
    const checked = $jabatan.filter(':checked').length;

    $('#cekSemuaPanduan')
        .prop('checked', total > 0 && checked === total)
        .prop('indeterminate', checked > 0 && checked < total);

    $('#panduanPdfOpsi .cek-divisi').each(function () {
        const grup = $(this).data('grup');
        const $items = $('#panduanPdfOpsi .cek-jabatan[data-grup="' + grup + '"]');
        const dipilih = $items.filter(':checked').length;
        $(this)
            .prop('checked', dipilih > 0 && dipilih === $items.length)
            .prop('indeterminate', dipilih > 0 && dipilih < $items.length);
    });

    $('#btnDownloadPanduanPdf').prop('disabled', checked === 0);
    $('#infoPanduanPdf').text(checked + ' jabatan dipilih');
}

function renderPanduanPdfOpsi(groups) {
    if (!groups || groups.length === 0) {
        $('#panduanPdfOpsi').html('<div class="alert alert-secondary mb-0">Belum ada panduan yang tersedia.</div>');
        syncPanduanPdfState();
        return;
    }

    let html = '';
    groups.forEach(function (g, i) {
        html += `<div class="border rounded-3 p-3 mb-3">
            <div class="form-check mb-2">
                <input class="form-check-input cek-divisi" type="checkbox" id="cekDivisi${i}" data-grup="${i}">
                <label class="form-check-label fw-semibold" for="cekDivisi${i}">${escHtml(g.divisi)}</label>
            </div>
            <div class="row g-2 ps-3">`;

        (g.items || []).forEach(function (it, j) {
            const id = `cekJabatan${i}_${j}`;
            html += `<div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input cek-jabatan" type="checkbox" name="jabatan[]" value="${escHtml(it.value)}" id="${id}" data-grup="${i}">
                    <label class="form-check-label" for="${id}">${escHtml(it.label)} <span class="badge bg-light text-muted border">${it.total} panduan</span></label>
                </div>
            </div>`;
        });

        html += '</div></div>';
    });

    $('#panduanPdfOpsi').html(html);
    syncPanduanPdfState();
}

function loadPanduanPdfOpsi() {
    $('#panduanPdfOpsi').html('<div class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat pilihan...</div>');
    $('#cekSemuaPanduan').prop('checked', false).prop('indeterminate', false);
    $('#btnDownloadPanduanPdf').prop('disabled', true);
    $('#infoPanduanPdf').text('0 jabatan dipilih');

    const url = (window.KPI_CONFIG && window.KPI_CONFIG.panduanOpsi) ? window.KPI_CONFIG.panduanOpsi : null;

    if (!url) {
        $('#panduanPdfOpsi').html('<div class="alert alert-danger mb-0">URL panduanOpsi tidak ditemukan di KPI_CONFIG.</div>');
        return;
    }

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            renderPanduanPdfOpsi(res.groups || []);
        },
        error: function (xhr) {
            console.error('Gagal load panduan opsi', xhr.status, xhr.responseText);
            $('#panduanPdfOpsi').html(
                '<div class="alert alert-danger mb-0">Gagal memuat pilihan (' + xhr.status + '). Silakan coba lagi.</div>'
            );
        }
    });
}

$(document).on('click', '[data-bs-target="#modalPanduanPdf"]', function () {
    setTimeout(loadPanduanPdfOpsi, 50);
});

$(document).on('show.bs.modal', '#modalPanduanPdf', function () {
    loadPanduanPdfOpsi();
});

$(document).on('change', '#cekSemuaPanduan', function () {
    $('#panduanPdfOpsi .cek-jabatan').prop('checked', $(this).is(':checked'));
    syncPanduanPdfState();
});

$(document).on('change', '#panduanPdfOpsi .cek-divisi', function () {
    const grup = $(this).data('grup');
    $('#panduanPdfOpsi .cek-jabatan[data-grup="' + grup + '"]').prop('checked', $(this).is(':checked'));
    syncPanduanPdfState();
});

$(document).on('change', '#panduanPdfOpsi .cek-jabatan', function () {
    syncPanduanPdfState();
});

$(document).on('submit', '#formPanduanPdf', function (e) {
    if ($('#panduanPdfOpsi .cek-jabatan:checked').length === 0) {
        e.preventDefault();
        if (typeof Swal !== 'undefined') {
            Swal.fire('Peringatan', 'Pilih minimal satu jabatan.', 'warning');
        } else {
            alert('Pilih minimal satu jabatan.');
        }
        return;
    }

    const $btn = $('#btnDownloadPanduanPdf');
    const originalHtml = '<i class="fa-solid fa-download"></i> <span>Download PDF</span>';

    $btn.prop('disabled', true)
        .html('<i class="fa-solid fa-spinner fa-spin me-1"></i><span>Menyiapkan PDF...</span>');

    setTimeout(function () {
        $btn.html(originalHtml);
        syncPanduanPdfState();
    }, 2500);
});

$(document).on('hidden.bs.modal', '#modalPanduanPdf', function () {
    const $btn = $('#btnDownloadPanduanPdf');
    $btn.html('<i class="fa-solid fa-download"></i> <span>Download PDF</span>');
    syncPanduanPdfState();
});

$(function () {
    ['modalPanduan', 'modalPanduanPdf'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el && el.parentElement !== document.body) document.body.appendChild(el);
    });
});

$(document).on('click', '#modalPanduan [data-bs-dismiss="modal"], #modalPanduan [data-dismiss="modal"], #modalPanduanPdf [data-bs-dismiss="modal"], #modalPanduanPdf [data-dismiss="modal"]', function (e) {
    e.preventDefault();
    const el = $(this).closest('.modal')[0];
    if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(el).hide();
    } else {
        $(el).modal('hide');
    }
});

$(document).on('hidden.bs.modal', '#modalPanduan, #modalPanduanPdf', function () {
    if ($('.modal.show').length === 0) {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ overflow: '', 'padding-right': '' });
    }
});