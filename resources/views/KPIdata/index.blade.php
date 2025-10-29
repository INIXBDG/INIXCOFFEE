@extends('databasekpi.berandaKPI')

@section('contentKPI')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .card-icon {
        font-size: 1.5rem;
        margin-right: 10px;
    }

    .stat-card {
        transition: transform 0.2s;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }

    .gradient-bg-pink {
        background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
    }

    .gradient-bg-blue {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }

    .gradient-bg-green {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .gradient-bg-yellow {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card h5 {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .stat-card p {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .stat-card ul {
        padding-left: 1.2rem;
        font-size: 0.85rem;
    }

    .stat-card li {
        margin-bottom: 0.3rem;
    }

    .card-title i {
        margin-right: 8px;
    }

    .form-control,
    .select2-selection {
        border-radius: 8px;
    }

    .btn-gradient-primary {
        border-radius: 8px;
        padding: 0.5rem 1rem;
    }

    .target-card {
        width: 220px;
        height: auto;
        border: 2px solid #ddd;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: 0.2s;
        background-color: #fff;
        cursor: pointer;
    }

    .target-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .bg-purple-light {
        background-color: #d4c4fb !important;
        color: #5e35b1 !important;
    }

    .bg-yellow-light {
        background-color: #fff3cd !important;
        color: #856404 !important;
    }

    .bg-red-light {
        background-color: #f8d7da !important;
        color: #721c24 !important;
    }

    .add-card {
        background-color: #f9f9f9;
        border: 2px dashed #28a745;
    }

    .add-card:hover {
        background-color: #eaffea;
    }

    #targetContainer {
        justify-content: center;
    }
</style>

<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-file-document"></i>
            </span> KPI
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span> Buat Target
                    <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Halaman ini digunakan untuk membuat target perdivisi dan dilakukan oleh koordinator/manager dari divisi tersebut.">
                    </i>
                </li>
            </ul>
        </nav>
    </div>
    <div class="stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"><i class="fas fa-bullseye card-icon me-2"></i> Buat Target Baru</h4>
                <div class="d-flex flex-wrap gap-3 mt-3" id="targetContainer">
                    <button type="button"
                        class="target-card add-card d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal"
                        data-bs-target="#modalBuatTarget"
                        style="width: 280px; flex: 0 0 auto;">
                        <i class="fas fa-plus fa-2x text-success"></i>
                    </button>

                    <div id="content_target" class="d-flex flex-wrap gap-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBuatTarget" tabindex="-1" role="dialog" aria-labelledby="modalBuatTargetLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('kpi.createTarget') }}" method="post" id="targetForm">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalBuatTargetLabel">
                        <i class="fas fa-bullseye me-2"></i> Buat Target Divisi Anda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="modal-content-form">
                    <div class="row">
                        <input type="hidden" name="id_pembuat" value="{{ auth()->user()->id }}">

                        <div class="col-md-12 mb-3">
                            <label for="judul_kpi" class="form-label">Judul KPI <span class="text-danger">*</span></label>
                            <input type="text" name="judul_kpi" id="judul_kpi" class="form-control"
                                placeholder="Contoh: Peningkatan Penjualan Produk A" required>
                        </div>

                        <!-- Deskripsi KPI -->
                        <div class="col-md-12 mb-3">
                            <label for="deskripsi_kpi" class="form-label">Deskripsi KPI</label>
                            <textarea name="deskripsi_kpi" id="deskripsi_kpi" class="form-control" rows="2"
                                placeholder="Jelaskan tujuan atau konteks dari target ini..."></textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="assistant_route" class="form-label">Pilih Assistant Route <span class="text-danger">*</span></label>
                            <select name="assistant_route" id="assistant_route" class="form-select" required>
                                <option selected disabled>-- Pilih Assistant Route --</option>
                                <option value="Pemasukan Kotor">Pemasukan Kotor (PK * Pengeluaran)</option>
                                <option value="Pemasukan Bersih">Pemasukan Bersih (PK * 9%)</option>
                                <option value="Kepuasan Pelangan">Kepuasan Pelangan (> 95%)</option>
                                <option value="Rasio Biaya Operasional">Rasio Biaya Operasional (40% =< PK)</option>
                                <option value="Rata Rata Pencapaian Departement">Rata Rata Pencapaian Per Departement</option>
                            </select>
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-6 mb-3">
                            <label for="jabatan" class="form-label">Pilih Jabatan <span class="text-danger">*</span></label>
                            <select name="jabatan" id="jabatan" class="form-select" required>
                                <option selected disabled>-- Pilih Jabatan --</option>
                            </select>
                        </div>

                        <!-- Tipe Target -->
                        <div class="col-md-6 mb-3">
                            <label for="tipeTarget" class="form-label">Tipe Target <span class="text-danger">*</span></label>
                            <select name="tipe_target" id="tipeTarget" class="form-select" required>
                                <option selected disabled>-- Pilih Tipe --</option>
                                <option value="angka">Angka (Unit, Jumlah, dll)</option>
                                <option value="rupiah">Rupiah (Nilai Keuangan)</option>
                                <option value="persen">Persen (%)</option>
                            </select>
                        </div>

                        <!-- Nilai Target -->
                        <div class="col-md-6 mb-3">
                            <label for="nilaiTarget" class="form-label">Nilai Target <span class="text-danger">*</span></label>
                            <input type="text" name="nilai_target" id="nilaiTarget" class="form-control"
                                placeholder="Contoh: 1200" required>
                        </div>

                        <!-- Jangka Target -->
                        <div class="col-md-6 mb-3">
                            <label for="jangkaTarget" class="form-label">Jangka Target <span class="text-danger">*</span></label>
                            <select name="jangka_target" id="jangkaTarget" class="form-select" required>
                                <option selected disabled>-- Pilih Jangka --</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3" id="detailJangkaGroup" style="display: none;">
                            <label for="detailJangka" class="form-label" id="detailJangkaLabel">
                                Detail Jangka <span class="text-danger">*</span>
                            </label>
                            <div id="detailJangkaField"></div>
                        </div>

                        <div class="col-md-12 mb-3" id="konversiGroup"
                            style="display: none; background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                            <h6 class="mb-2">
                                <i class="fas fa-calculator me-2"></i> Estimasi Distribusi Target Tahunan:
                            </h6>
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <small class="text-muted">Per Bulan</small>
                                    <p class="mb-0 fw-bold" id="hasilBulanan">-</p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Per Kuartal</small>
                                    <p class="mb-0 fw-bold" id="hasilKuartal">-</p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">Per Minggu</small>
                                    <p class="mb-0 fw-bold" id="hasilMingguan">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Target
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        loadContentForm();
    });

    $('#targetForm').on('submit', function(e) {
        e.preventDefault();

        const judul = $('#judul_kpi').val().trim();
        if (!judul) {
            Swal.fire('Peringatan', 'Judul KPI wajib diisi.', 'warning');
            return;
        }

        const form = $(this);
        const url = form.attr('action');
        const formData = new FormData(this);

        const rawNilai = $('#nilaiTarget').val().replace(/\D/g, '');
        formData.set('nilai_target', rawNilai);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Target berhasil dibuat.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#modalBuatTarget').modal('hide');
                });

                loadContentForm();
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: msg
                });
            }
        });
    });

    function setupFormListeners(formId, isEdit = false) {
        const prefix = isEdit ? 'edit_' : '';
        const $form = $(`#${formId}`);
        const $tipeTarget = $form.find(`#${prefix}tipe_target`);
        const $nilaiTarget = $form.find(`#${prefix}nilai_target`);
        const $jangkaTarget = $form.find(`#${prefix}jangka_target`);
        const $detailJangkaGroup = $form.find(`#${prefix}detailJangkaGroup`);
        const $detailJangkaField = $form.find(`#${prefix}detailJangkaField`);
        const $konversiGroup = $form.find(`#${prefix}konversiGroup`);
        const $hasilBulanan = $form.find(`#${prefix}hasilBulanan`);
        const $hasilKuartal = $form.find(`#${prefix}hasilKuartal`);
        const $hasilMingguan = $form.find(`#${prefix}hasilMingguan`);

        function parseRawNilai() {
            const raw = $nilaiTarget.val() ? $nilaiTarget.val().toString().replace(/\D/g, '') : '';
            return raw ? parseFloat(raw) : 0;
        }

        function updateKonversiIfNeeded() {
            const nilai = parseRawNilai();
            if (nilai > 0 && $jangkaTarget.val() === 'Tahunan') {
                $hasilBulanan.text(formatNumber(nilai / 12));
                $hasilKuartal.text(formatNumber(nilai / 4));
                $hasilMingguan.text(formatNumber(nilai / 52));
                $konversiGroup.show();
            } else {
                $konversiGroup.hide();
            }
        }

        $nilaiTarget.off('input').on('input', function() {
            const tipe = $tipeTarget.val();
            let value = $(this).val().replace(/\D/g, '');
            if (!value) {
                $(this).val('');
                updateKonversiIfNeeded();
                return;
            }

            let formatted;
            if (tipe === 'rupiah') {
                formatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(parseInt(value));
            } else if (tipe === 'persen') {
                formatted = new Intl.NumberFormat('id-ID').format(parseInt(value)) + ' %';
            } else {
                formatted = new Intl.NumberFormat('id-ID').format(parseInt(value));
            }

            $(this).val(formatted);
            updateKonversiIfNeeded();
        });

        $jangkaTarget.off('change').on('change', function() {
            const jangka = $(this).val();
            $detailJangkaGroup.hide();
            $detailJangkaField.empty();
            $konversiGroup.hide();

            if (!jangka) return;

            const tahunIni = new Date().getFullYear();

            if (jangka === 'Tahunan') {
                const startYear = tahunIni;
                const endYear = tahunIni + 1;
                let html = `<select class="form-select" name="detail_jangka" required>`;

                for (let y = startYear; y <= endYear; y++) {
                    const selected = (y === tahunIni) ? 'selected' : '';
                    html += `<option value="${y}" ${selected}>${y}</option>`;
                }

                html += `</select>`;
                $detailJangkaField.html(html);
                $detailJangkaGroup.show();
                updateKonversiIfNeeded();
                return;
            }

            if (jangka === 'Quartal') {
                const now = new Date();
                const bulanSekarang = now.getMonth() + 1;
                const quartalSekarang = Math.ceil(bulanSekarang / 3);
                const tahunIni = now.getFullYear();
                const tahunDepan = tahunIni + 1;

                let html = `<select class="form-select" name="detail_jangka" required>`;

                // Label Tahun Ini
                html += `<optgroup label="Tahun ${tahunIni}">`;
                for (let q = quartalSekarang; q <= 4; q++) {
                    const selected = (q === quartalSekarang) ? 'selected' : '';
                    html += `<option value="${tahunIni}-Q${q}" ${selected}>Kuartal ${q} (${tahunIni})</option>`;
                }
                html += `</optgroup>`;

                // Label Tahun Depan (hanya Kuartal 1)
                html += `<optgroup label="Tahun ${tahunDepan}">`;
                html += `<option value="${tahunDepan}-Q1">Kuartal 1 (${tahunDepan})</option>`;
                html += `</optgroup>`;

                html += `</select>`;
                $detailJangkaField.html(html);
                $detailJangkaGroup.show();
                return;
            }

            if (jangka === 'Bulanan') {
                const now = new Date();
                const bulanSekarangIndex = now.getMonth();
                const tahunSekarang = now.getFullYear();
                const tahunDepan = tahunSekarang + 1;
                const namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
                ];

                let html = `<select class="form-select" name="detail_jangka" required>`;

                // Label Tahun Ini
                html += `<optgroup label="Tahun ${tahunSekarang}">`;
                for (let i = bulanSekarangIndex; i < 12; i++) {
                    const selected = (i === bulanSekarangIndex) ? 'selected' : '';
                    html += `<option value="${tahunSekarang}-${i + 1}" ${selected}>${namaBulan[i]} ${tahunSekarang}</option>`;
                }
                html += `</optgroup>`;

                // Label Tahun Depan (hanya Januari)
                html += `<optgroup label="Tahun ${tahunDepan}">`;
                html += `<option value="${tahunDepan}-1">Januari ${tahunDepan}</option>`;
                html += `</optgroup>`;

                html += `</select>`;
                $detailJangkaField.html(html);
                $detailJangkaGroup.show();
                return;
            }

            if (jangka === 'Mingguan') {
                const today = new Date();
                const currentYear = today.getFullYear();
                const currentMonth = today.getMonth();
                const nextYear = currentYear + 1;

                const formatDateNumeric = (date) => {
                    const d = date.getDate().toString().padStart(2, '0');
                    const m = (date.getMonth() + 1).toString().padStart(2, '0');
                    const y = date.getFullYear();
                    return `${d}-${m}-${y}`;
                };

                const getWeeksInMonth = (year, month) => {
                    const weeks = [];
                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    let current = new Date(firstDay);

                    while (current <= lastDay) {
                        const start = new Date(current);
                        const end = new Date(current);
                        end.setDate(end.getDate() + 6);
                        if (end > lastDay) end.setDate(lastDay.getDate());
                        weeks.push([start.getTime(), end.getTime()]);
                        current.setDate(current.getDate() + 7);
                    }
                    return weeks;
                };

                let html = `<select class="form-select" name="detail_jangka" required>`;

                // Label Tahun Ini
                html += `<optgroup label="Tahun ${currentYear}">`;
                const weeksThisMonth = getWeeksInMonth(currentYear, currentMonth);
                weeksThisMonth.forEach((week, idx) => {
                    const [startMs, endMs] = week;
                    const startDate = new Date(startMs);
                    const endDate = new Date(endMs);
                    if (endDate < today) return;

                    const startStr = formatDateNumeric(startDate);
                    const endStr = formatDateNumeric(endDate);
                    const value = `${startStr} - ${endStr} - ${currentYear}`;
                    const label = `Minggu ${idx + 1} (${startStr} - ${endStr})`;
                    html += `<option value="${value}">${label}</option>`;
                });
                html += `</optgroup>`;

                // Label Tahun Depan (semua minggu di bulan Januari)
                html += `<optgroup label="Tahun ${nextYear}">`;
                const janWeeksNextYear = getWeeksInMonth(nextYear, 0);
                janWeeksNextYear.forEach((week, idx) => {
                    const [startMs, endMs] = week;
                    const startDate = new Date(startMs);
                    const endDate = new Date(endMs);
                    const startStr = formatDateNumeric(startDate);
                    const endStr = formatDateNumeric(endDate);
                    const value = `${startStr} - ${endStr} - ${nextYear}`;
                    const label = `Minggu ${idx + 1} (${startStr} - ${endStr})`;
                    html += `<option value="${value}">${label}</option>`;
                });
                html += `</optgroup>`;

                html += `</select>`;
                $detailJangkaField.html(html);
                $detailJangkaGroup.show();
                return;
            }
        });

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(Math.round(num));
        }

        function formatDate(date) {
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short'
            }).replace(/\./g, '');
        }

        function getWeeksInMonth(year, month) {
            const weeks = [];
            const firstDate = new Date(year, month, 1);
            const lastDate = new Date(year, month + 1, 0);
            let cursor = new Date(firstDate);

            function clone(d) {
                return new Date(d.getTime());
            }
            let start = clone(firstDate);
            let end = clone(start);
            while (end.getDay() !== 0 && end < lastDate) {
                end.setDate(end.getDate() + 1);
            }
            if (end > lastDate) end = clone(lastDate);
            weeks.push([start.getTime(), end.getTime()]);
            let nextStart = clone(end);
            nextStart.setDate(nextStart.getDate() + 1);
            while (nextStart <= lastDate) {
                let nextEnd = clone(nextStart);
                nextEnd.setDate(nextEnd.getDate() + 6);
                if (nextEnd > lastDate) nextEnd = clone(lastDate);
                weeks.push([nextStart.getTime(), nextEnd.getTime()]);
                nextStart.setDate(nextEnd.getDate() + 1);
            }
            return weeks;
        }
    }

    function loadContentForm() {
        $.ajax({
            url: '{{ route("kpi.getDataTarget") }}',
            type: 'GET',
            success: function(response) {
                const data = response;

                const content_target = $('#content_target');
                const jabatanSelect = $('#jabatan');
                const jangkaSelect = $('#jangkaTarget');
                const pembuatGroup = $('#pembuatGroup');
                const pembuatContainer = $('#radioPembuatContainer');
                const tahunIni = new Date().getFullYear();

                content_target.empty();

                if (data.detail.length === 0) {} else {
                    const now = new Date();

                    data.detail.forEach(function(item) {
                        // === Format Nilai Target ===
                        let formattedTarget = item.nilai_target;
                        if (item.tipe_target === 'persen') {
                            formattedTarget = `${item.nilai_target}%`;
                        } else if (item.tipe_target === 'rupiah') {
                            formattedTarget = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(item.nilai_target);
                        }

                        // === Hitung Deadline Berdasarkan jangka_target ===
                        let deadlineText = '';
                        let deadlineDate = null;

                        const detail = item.detail_jangka ? item.detail_jangka.toString().trim() : '';

                        if (item.jangka_target === 'tahunan') {
                            const year = parseInt(detail);
                            if (!isNaN(year)) {
                                deadlineText = `31 Des ${year}`;
                                deadlineDate = new Date(`${year}-12-31`);
                            }
                        } else if (item.jangka_target === 'bulanan') {
                            // format bisa "2025-05" atau "2025-5"
                            const parts = detail.split('-');
                            if (parts.length === 2) {
                                const [year, month] = parts;
                                const lastDay = new Date(year, month, 0).getDate();
                                const monthName = new Date(year, month - 1).toLocaleString('id-ID', {
                                    month: 'short'
                                });
                                deadlineText = `${lastDay} ${monthName} ${year}`;
                                deadlineDate = new Date(year, month - 1, lastDay);
                            }
                        } else if (item.jangka_target === 'kuartalan') {
                            // format bisa "2025Q1" atau "2025-Q1"
                            const match = detail.match(/(\d{4})\D?Q?(\d)/i);
                            if (match) {
                                const year = match[1];
                                const quarter = parseInt(match[2]);
                                const monthEnd = quarter * 3;
                                const lastDay = new Date(year, monthEnd, 0).getDate();
                                const monthName = new Date(year, monthEnd - 1).toLocaleString('id-ID', {
                                    month: 'short'
                                });
                                deadlineText = `${lastDay} ${monthName} ${year}`;
                                deadlineDate = new Date(year, monthEnd - 1, lastDay);
                            }
                        } else if (item.jangka_target === 'mingguan') {
                            const match = detail.match(/(\d{4})\D?W?(\d{1,2})/i);
                            if (match) {
                                const year = parseInt(match[1]);
                                const week = parseInt(match[2]);
                                const firstDay = new Date(year, 0, 1);
                                const deadlineMillis = firstDay.getTime() + (week * 7 * 24 * 60 * 60 * 1000);
                                deadlineDate = new Date(deadlineMillis);
                                deadlineText = `Minggu ke-${week}, ${year}`;
                            }
                        }

                        let statusText = '';
                        let badgeClass = 'bg-secondary';
                        const nowTime = now.getTime();
                        const deadlineTime = deadlineDate ? deadlineDate.getTime() : null;

                        if (item.status == 1) {
                            statusText = 'Selesai';
                            badgeClass = 'bg-success';
                        } else {
                            if (deadlineTime && nowTime > deadlineTime) {
                                statusText = 'Gagal';
                                badgeClass = 'bg-danger';
                            } else {
                                statusText = 'Dalam Proses';
                                badgeClass = 'bg-warning text-dark';
                            }
                        }

                        let progressValue = item.progress ?? 45;

                        content_target.append(`
                            <div class="target-card rounded-4 border-0 shadow-sm position-relative overflow-hidden"
                                style="width: 290px; background: white; flex: 0 0 auto; transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1); cursor: pointer; border: 2px solid #f0f0f0;"
                                onmouseenter="this.style.transform='scale(1.03)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.12)'; this.querySelector('.action-buttons').style.opacity='1'; this.querySelector('.action-buttons').style.transform='translateY(0)';"
                                onmouseleave="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.querySelector('.action-buttons').style.opacity='0'; this.querySelector('.action-buttons').style.transform='translateY(-8px)';">

                                <!-- Status strip (top accent) -->
                                <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: ${badgeClass === 'bg-success' ? '#28a745' : badgeClass === 'bg-danger' ? '#dc3545' : '#ffc107'};"></div>

                                <!-- Action buttons -->
                                <div class="action-buttons d-flex gap-1 position-absolute top-0 end-0 p-2" 
                                    style="opacity: 0; transform: translateY(-8px); transition: all 0.3s ease; z-index: 10;">
                                    <button class="btn btn-sm btn-info rounded-circle p-2" style="width: 36px; height: 36px;">
                                        <i class="fa-solid fa-eye" style="font-size: 1rem;"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning rounded-circle d-flex align-items-center justify-content-center btnEditTarget"
                                            title="Edit"
                                            style="width: 36px; height: 36px; font-size: 0.9rem;"
                                            data-id="${item.id}"
                                            data-judul_kpi="${item.judul_kpi}"
                                            data-deskripsi_kpi="${item.deskripsi_kpi}"
                                            data-tipe_target="${item.tipe_target}"
                                            data-nilai_target="${item.nilai_target}"
                                            data-jangka_target="${item.jangka_target}"
                                            data-detail_jangka="${item.detail_jangka}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger rounded-circle d-flex align-items-center justify-content-center buttonHapusTarget"
                                            data-id="${item.id}" title="Hapus"
                                            style="width: 36px; height: 36px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>

                                <!-- Content -->
                                <div class="p-3 pt-4">
                                    <!-- Judul KPI -->
                                    <h5 class="fw-bold mb-2 fs-6 text-dark" style="min-height: 2.2em; line-height: 1.2;">
                                        ${item.judul}
                                    </h5>

                                    <!-- Jenis Target Badge -->
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-light text-primary border border-primary fw-medium me-2" style="font-size: 0.75rem;">
                                            ${item.jangka_target.charAt(0).toUpperCase() + item.jangka_target.slice(1)}
                                        </span>
                                        <span class="badge ${badgeClass} fw-medium" style="font-size: 0.75rem;">${statusText}</span>
                                    </div>

                                    <!-- Target Value -->
                                    <div class="mb-2">
                                        <p class="mb-1 text-muted small">
                                            <i class="fa-solid fa-bullseye me-1" style="color: #6c757d;"></i>
                                            <strong>Target:</strong> ${formattedTarget}
                                        </p>
                                    </div>

                                    <!-- Info Tambahan -->
                                    <div class="small text-muted mb-2" style="font-size: 0.82rem;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Jabatan</span>
                                            <span class="fw-medium">${item.jabatan || '-'}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Divisi</span>
                                            <span class="fw-medium">${item.divisi || '-'}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Dibuat oleh</span>
                                            <span class="fw-medium">${item.pembuat || '-'}</span>
                                        </div>
                                    </div>

                                    <div class="mt-2 mb-1">
                                        <div class="progress rounded-pill" style="height: 8px; background-color: #e9ecef; overflow: visible;">
                                            <div class="progress-bar bg-success rounded-pill position-relative" 
                                                role="progressbar" 
                                                style="width: ${Math.min(progressValue, 100)}%;"
                                                aria-valuenow="${progressValue}" 
                                                aria-valuemin="0" aria-valuemax="100">
                                                <span class="position-absolute top-0 start-0 translate-middle-y ms-1" 
                                                    style="font-size: 0.65rem; color: white; text-shadow: 0 0 2px rgba(0,0,0,0.5);">
                                                    ${progressValue}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deadline -->
                                    <div class="d-flex align-items-center justify-content-between mt-1">
                                        <small class="text-muted">
                                            <i class="fa-solid fa-calendar-days me-1" style="font-size: 0.8rem;"></i>
                                            ${item.tenggat_waktu}
                                        </small>
                                        <small class="fw-medium" style="color: ${badgeClass === 'bg-success' ? '#28a745' : badgeClass === 'bg-danger' ? '#dc3545' : '#856404'};">
                                            ${statusText}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        `);
                    });

                    setupFormListeners('targetForm', false);

                    const $editJangka = $('#edit_jangka_target');
                    $editJangka.empty().append('<option selected disabled>-- Pilih Jangka --</option>');
                    $editJangka.append(`<option value="Tahunan">Tahunan (${tahunIni})</option>`);
                    $editJangka.append(`<option value="Quartal">Kuartal</option>`);
                    $editJangka.append(`<option value="Bulanan">Bulanan</option>`);
                    $editJangka.append(`<option value="Mingguan">Mingguan</option>`);
                }

                jabatanSelect.empty().append('<option selected disabled>-- Pilih Jabatan --</option>');
                jangkaSelect.empty().append('<option selected disabled>-- Pilih Jangka Target --</option>');
                pembuatContainer.empty();

                const jabatanTersedia = data.jabatan_list || [];
                const detailTargets = data.detail || [];

                const jabatanCount = {};
                detailTargets.forEach(d => {
                    jabatanCount[d.jabatan] = (jabatanCount[d.jabatan] || 0) + 1;
                });

                jabatanTersedia.forEach(jab => {
                    const count = jabatanCount[jab] || 0;
                    const isDisabled = count >= 5;
                    let label = jab;
                    if (count > 0) {
                        label += ` (${count}/5)`;
                    }
                    if (isDisabled) {
                        label += ' — Maksimal tercapai';
                    }

                    jabatanSelect.append(`
                        <option value="${jab}" ${isDisabled ? 'disabled' : '' }>
                            ${label}
                        </option>
                    `);
                });

                // === 2. JANGKA TARGET: SEMUA SELALU AKTIF (tanpa batas) ===
                jangkaSelect.append(`<option value="Tahunan">Tahunan (${tahunIni})</option>`);
                jangkaSelect.append(`<option value="Quartal">Kuartal</option>`);
                jangkaSelect.append(`<option value="Bulanan">Bulanan</option>`);
                jangkaSelect.append(`<option value="Mingguan">Mingguan</option>`);

                const hasPembuat = data.pembuat && (
                    (Array.isArray(data.pembuat) && data.pembuat.length > 0) ||
                    (typeof data.pembuat === 'string' && data.pembuat.trim() !== '')
                );

                if (hasPembuat) {
                    pembuatGroup.show();
                    const pembuatList = Array.isArray(data.pembuat) ? data.pembuat : [{
                        nama: data.pembuat
                    }];

                    pembuatList.forEach((p, idx) => {
                        const nama = p.nama ?? p;
                        const checked = (pembuatList.length === 1) ? 'checked' : '';

                        pembuatContainer.append(`
                            <div class="form-check form-check-inline">
                                <input class="form-check-input pembuat-radio" type="radio" name="pembuat" id="pembuat_${idValue}" value="${idValue}" ${checked}>
                                <label class="form-check-label" for="pembuat_${idValue}">${nama}</label>
                            </div>
                        `);
                    });
                } else {
                    pembuatGroup.hide();
                }

                setupFormListeners();
                $('#detailJangkaGroup').hide();
                $('#konversiGroup').hide();
            },
            error: function(xhr) {
                Swal.fire('Error', 'Gagal memuat data form: ' + (xhr.responseJSON?.message || 'Silakan coba lagi.'), 'error');
            }
        });
    }

    $(document).on('click', '.buttonHapusTarget', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data target ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            customClass: {
                confirmButton: 'btn btn-gradient-info me-3',
                cancelButton: 'btn btn-gradient-danger'
            },
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/kpi-data/hapus-data-target/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data target berhasil dihapus.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        loadContentForm();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    });

    function setupFormListeners() {
        const $tipeTarget = $('#tipeTarget');
        const $nilaiTarget = $('#nilaiTarget');
        const $jangkaTarget = $('#jangkaTarget');
        const $detailJangkaGroup = $('#detailJangkaGroup');
        const $detailJangkaField = $('#detailJangkaField');
        const $konversiGroup = $('#konversiGroup');
        const $hasilBulanan = $('#hasilBulanan');
        const $hasilKuartal = $('#hasilKuartal');
        const $hasilMingguan = $('#hasilMingguan');

        function parseRawNilai() {
            const raw = $nilaiTarget.val() ? $nilaiTarget.val().toString().replace(/\D/g, '') : '';
            return raw ? parseFloat(raw) : 0;
        }

        function updateKonversiIfNeeded() {
            const nilai = parseRawNilai();
            const jangka = $('#jangkaTarget').val();
            const tipe = $('#tipeTarget').val();

            if (nilai > 0 && jangka === 'Tahunan' && tipe !== 'persen') {
                $hasilBulanan.text(formatNumber(nilai / 12));
                $hasilKuartal.text(formatNumber(nilai / 4));
                $hasilMingguan.text(formatNumber(nilai / 52));
                $konversiGroup.show();
            } else {
                $konversiGroup.hide();
            }
        }

        $nilaiTarget.off('input').on('input', function() {
            const tipe = $tipeTarget.val();
            let value = $(this).val().replace(/\D/g, '');
            if (!value) {
                $(this).val('');
                updateKonversiIfNeeded();
                return;
            }

            let formatted;
            if (tipe === 'rupiah') {
                formatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(parseInt(value));
            } else if (tipe === 'persen') {
                formatted = new Intl.NumberFormat('id-ID').format(parseInt(value)) + ' %';
            } else {
                formatted = new Intl.NumberFormat('id-ID').format(parseInt(value));
            }

            $(this).val(formatted);
            updateKonversiIfNeeded();
        });

        $jangkaTarget.off('change').on('change', function() {
            const jangka = $(this).val();
            $detailJangkaGroup.hide();
            $detailJangkaField.empty();
            $konversiGroup.hide();

            if (!jangka) return;

            const tahunIni = new Date().getFullYear();

            if (jangka === 'Tahunan') {
                const tahunIni = new Date().getFullYear();
                const tahunDepan = tahunIni + 1;

                const html = `
                    <select class="form-select" name="detail_jangka" required>
                        <option value="${tahunIni}">${tahunIni}</option>
                        <option value="${tahunDepan}">${tahunDepan}</option>
                    </select>
                `;

                $detailJangkaField.html(html);
                $detailJangkaGroup.show();
                updateKonversiIfNeeded();
                return;
            }


            if (jangka === 'Quartal') {
                const bulanSekarang = new Date().getMonth() + 1;
                const quartalSekarang = Math.ceil(bulanSekarang / 3);
                let html = `<select class="form-select" name="detail_jangka" required>`;

                for (let q = 1; q <= 4; q++) {
                    const disabled = q < quartalSekarang ? 'disabled' : '';
                    const selected = q === quartalSekarang ? 'selected' : '';
                    html += `<option value="Q${q} - ${tahunIni}" ${disabled} ${selected}>Kuartal ${q} - (${tahunIni})</option>`;
                }

                html += `<option disabled>──────── Tahun Depan ────────</option>`;
                html += `<option value="Q1 - ${tahunIni + 1}">Kuartal 1 - (${tahunIni + 1})</option>`;

                html += `</select>`;
                $detailJangkaField.html(html);
                $detailJangkaGroup.show();
                return;
            }

            if (jangka === 'Bulanan') {
                const bulanSekarangIndex = new Date().getMonth();
                const namaBulan = [
                    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
                ];

                let html = `<select class="form-select" name="detail_jangka" required>`;

                for (let i = 0; i < 12; i++) {
                    const disabled = i < bulanSekarangIndex ? 'disabled' : '';
                    const selected = i === bulanSekarangIndex ? 'selected' : '';
                    html += `<option value="${i + 1} - ${tahunIni}" ${disabled} ${selected}>${namaBulan[i]} ${tahunIni}</option>`;
                }

                html += `<option disabled>──────── Tahun Depan ────────</option>`;
                html += `<option value="1 - ${tahunIni + 1}">Januari ${tahunIni + 1}</option>`;

                html += `</select>`;
                $detailJangkaField.html(html);
                $detailJangkaGroup.show();
                return;
            }

            if (jangka === 'Mingguan') {
                const today = new Date();
                const currentYear = today.getFullYear();
                const currentMonth = today.getMonth();
                const weeksThisMonth = getWeeksInMonth(currentYear, currentMonth);

                const formatDateNumeric = (date) => {
                    const d = date.getDate().toString().padStart(2, '0');
                    const m = (date.getMonth() + 1).toString().padStart(2, '0');
                    return `${d}-${m}`;
                };

                let html = `<select class="form-select" name="detail_jangka" required>`;

                weeksThisMonth.forEach((week, idx) => {
                    const [startMs, endMs] = week;
                    const startDate = new Date(startMs);
                    const endDate = new Date(endMs);
                    const disabled = endDate < today ? 'disabled' : '';
                    const label = `Minggu ${idx + 1} (${formatDateNumeric(startDate)} - ${formatDateNumeric(endDate)} - ${currentYear})`;
                    html += `<option value="${formatDateNumeric(startDate)} - ${formatDateNumeric(endDate)} - ${currentYear}" ${disabled}>${label}</option>`;
                });

                html += `<option disabled>──────── Tahun Depan ────────</option>`;

                const weeksNextYearJanuary = getWeeksInMonth(currentYear + 1, 0);
                weeksNextYearJanuary.forEach((week, idx) => {
                    const [startMs, endMs] = week;
                    const startDate = new Date(startMs);
                    const endDate = new Date(endMs);
                    const label = `Minggu ${idx + 1} (${formatDateNumeric(startDate)} - ${formatDateNumeric(endDate)} - ${currentYear + 1})`;
                    html += `<option value="${formatDateNumeric(startDate)} - ${formatDateNumeric(endDate)} - ${currentYear + 1}">${label}</option>`;
                });

                html += `</select>`;
                $detailJangkaField.html(html);
                $detailJangkaGroup.show();
                return;
            }
        });

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(Math.round(num));
        }

        function formatDate(date) {
            const options = {
                day: '2-digit',
                month: 'short'
            };
            return date.toLocaleDateString('id-ID', options).replace(/\./g, '');
        }

        function getWeeksInMonth(year, month) {
            const weeks = [];
            const firstDate = new Date(year, month, 1);
            const lastDate = new Date(year, month + 1, 0);
            let cursor = new Date(firstDate);

            function clone(d) {
                return new Date(d.getTime());
            }
            let start = clone(firstDate);
            let end = clone(start);
            while (end.getDay() !== 0 && end < lastDate) {
                end.setDate(end.getDate() + 1);
            }
            if (end > lastDate) end = clone(lastDate);
            weeks.push([start.getTime(), end.getTime()]);
            let nextStart = clone(end);
            nextStart.setDate(nextStart.getDate() + 1);
            while (nextStart <= lastDate) {
                let nextEnd = clone(nextStart);
                nextEnd.setDate(nextEnd.getDate() + 6);
                if (nextEnd > lastDate) nextEnd = clone(lastDate);
                weeks.push([nextStart.getTime(), nextEnd.getTime()]);
                nextStart.setDate(nextEnd.getDate() + 1);
            }
            return weeks;
        }
    }
</script>
@endsection