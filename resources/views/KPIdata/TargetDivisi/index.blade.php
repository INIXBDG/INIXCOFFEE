@extends('databasekpi.berandaKPI')

@section('contentKPI')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
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

        .gradient-bg-pink {
            ground: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
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

        .stat-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .progress-bar-modern {
            height: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
        }

        .progress-fill {
            height: 100%;
            border-radius: 5px;
            background: linear-gradient(90deg, #4361ee, #3a0ca3);
        }

        .metric-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2b2d42;
        }

        .metric-label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .custom-modal {
            max-width: 800px;
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
            flex-grow: 1;
        }

        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .participant-list::-webkit-scrollbar {
            width: 4px;
        }

        .participant-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .participant-list::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 2px;
        }

        .participant-list::-webkit-scrollbar-thumb:hover {
            background-color: rgba(0, 0, 0, 0.3);
        }

        .target-card {
            width: 450px;
        }

        @media (max-width: 768px) {
            .target-card {
                width: 100%;
            }
        }
    </style>
    <div class="modal fade" id="detailTargetModal" tabindex="-1" aria-labelledby="detailTargetModalLable"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div id="bodyContentDetailTarget" class="p-3"></div>
            </div>
        </div>
    </div>

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
                        <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle" data-bs-toggle="tooltip"
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
                    {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalImport">
                        Import Target
                    </button> --}}

                    <div class="d-flex flex-wrap gap-3 mt-3" id="targetContainer">
                        <button type="button" class="target-card add-card d-flex align-items-center justify-content-center"
                            data-bs-toggle="modal" data-bs-target="#modalBuatTarget" style="width: 280px; flex: 0 0 auto;">
                            <i class="fas fa-plus fa-2x text-success"></i>
                        </button>

                        <div id="content_target" class="d-flex flex-wrap gap-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ModalImport" tabindex="-1" role="dialog" aria-labelledby="ModalImportLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('kpi.importTarget') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalImportLabel">Modal title</h5>
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file">File Import</label>
                            <input type="file" class="form-control" id="file" placeholder="pilih file xlsx,xls,csv">
                        </div>
                        <div class="form-group text-center">     
                            <small id="emailHelp" class="form-text text-muted">jika belum memiliki file template, silahkan di download.</small>
                            <a href="{{ asset('template_KPI/template_import_kpi.xlsx') }}" class="btn btn-success" download>Download Template</a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keluar</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBuatTarget" tabindex="-1" role="dialog" aria-labelledby="modalBuatTargetLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
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
                                <label for="judul_kpi" class="form-label">Judul KPI <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="judul_kpi" id="judul_kpi" class="form-control"
                                    placeholder="Contoh: Peningkatan Penjualan Produk A" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="deskripsi_kpi" class="form-label">Deskripsi KPI</label>
                                <textarea name="deskripsi_kpi" id="deskripsi_kpi" class="form-control" rows="2"
                                    placeholder="Jelaskan tujuan atau konteks dari target ini..."></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="jabatan" class="form-label">Pilih Jabatan <span
                                        class="text-danger">*</span></label>
                                <select name="jabatan[]" id="jabatan" class="form-select select2" multiple></select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="karyawan" class="form-label">Pilih Karyawan <span
                                        class="text-danger">*</span></label>
                                <select name="karyawan[]" id="karyawan" class="form-select select2" multiple></select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="assistant_route" class="form-label">Pilih Assistant Route <span
                                        class="text-danger">*</span></label>
                                <select name="asistant_route" id="assistant_route" class="form-select" required>
                                    <option selected disabled>-- Pilih Assistant Route --</option>
                                </select>
                            </div>


                            <div class="col-md-6 mb-3">
                                <label for="tipeTarget" class="form-label">Tipe Target <span
                                        class="text-danger">*</span></label>
                                <select name="tipe_target" id="tipeTarget" class="form-select" required>
                                    <option selected disabled>-- Pilih Tipe --</option>
                                    <option value="angka">Angka (Unit, Jumlah, dll)</option>
                                    <option value="rupiah">Rupiah (Nilai Keuangan)</option>
                                    <option value="persen">Persen (%)</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nilaiTarget" class="form-label">Nilai Target <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nilai_target" id="nilaiTarget" class="form-control"
                                    placeholder="Contoh: 1200" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="jangkaTarget" class="form-label">Jangka Target <span
                                        class="text-danger">*</span></label>
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

    <div class="modal fade" id="modalFormManual" tabindex="-1" role="dialog" aria-labelledby="modalFormManualLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form id="formManualValue" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalFormManualLabel">Isi Manual Target</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="manualValueId">
                        <div class="form-group">
                            <label>Format Nilai</label>
                            <select class="form-control" id="manual_format">
                                <option value="angka">Angka</option>
                                <option value="persen">Persen (%)</option>
                                <option value="rupiah">Rupiah (Rp)</option>
                            </select>
                        </div>
                        <div id="doubleInputArea" style="display:none;">

                            <div class="form-group">
                                <label>Biaya Gaji Tahunan</label>
                                <input type="text" class="form-control" id="biaya_gaji_display">
                                <input type="hidden" name="biaya_gaji_tahunan" id="biaya_gaji_tahunan" required>
                            </div>

                            <div class="form-group">
                                <label>Biaya BPJS Tahunan</label>
                                <input type="text" class="form-control" id="biaya_bpjs_display">
                                <input type="hidden" name="biaya_bpjs_tahunan" id="biaya_bpjs_tahunan" required>
                            </div>

                            <div class="form-group">
                                <label>Biaya Rekrutmen Tahunan</label>
                                <input type="text" class="form-control" id="biaya_rekrutmen_display">
                                <input type="hidden" name="biaya_rekrutmen_tahunan" id="biaya_rekrutmen_tahunan"
                                    required>
                            </div>
                        </div>

                        <div class="form-group" id="singleInputArea">
                            <label>Masukan Nilai</label>
                            <input type="text" class="form-control" id="manual_value_display">
                            <input type="hidden" name="manual_value" id="manual_value">
                        </div>

                        <div class="form-group">
                            <label>Masukan Document</label>
                            <input type="file" class="form-control" name="manual_document" id="manual_document"
                                accept="image/*,.pdf">
                        </div>

                        <div class="form-group">
                            <div id="documentPreview" class="mt-3"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $('#jabatan').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalBuatTarget')
        });

        $('#karyawan').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalBuatTarget')
        });

        $(document).on('click', '.buttonHapusTarget, .buttonForm', function(e) {
            e.stopPropagation();
        });

        const allowedAssistantRoutes = [
            'dorong inovasi pelayanan',
            'inisiatif efisiensi keuangan',
            'rasio biaya operasional terhadap revenue',
            'mengurangi manual work dan error',
            'pengeluaran biaya karyawan'
        ];

        const allowedDoubleManualRoutes = [
            'pengeluaran biaya karyawan'
        ];


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
            // Single Input
            $('#manual_value_display').off('input.formatting');
            $('#manual_value_display').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $('#manual_value').val(raw);

                const format = $('#manual_format').val();
                let formatted = formatNumber(raw);

                if (format === 'rupiah' && raw) {
                    formatted = 'Rp ' + formatted;
                } else if (format === 'persen' && raw) {
                    formatted = formatted + '%';
                }

                $(this).val(formatted);
            });

            // Double Input - Biaya Gaji
            $('#biaya_gaji_display').off('input.formatting');
            $('#biaya_gaji_display').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $('#biaya_gaji_tahunan').val(raw);
                $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
            });

            // Double Input - Biaya BPJS
            $('#biaya_bpjs_display').off('input.formatting');
            $('#biaya_bpjs_display').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $('#biaya_bpjs_tahunan').val(raw);
                $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
            });

            // ✅ Double Input - Biaya Rekrutmen (BARU)
            $('#biaya_rekrutmen_display').off('input.formatting');
            $('#biaya_rekrutmen_display').on('input.formatting', function() {
                const raw = getRawNumber($(this).val());
                $('#biaya_rekrutmen_tahunan').val(raw);
                $(this).val(raw ? 'Rp ' + formatNumber(raw) : '');
            });
        }

        $(document).ready(function() {
            loadContentForm();

            initInputFormatting();

            $('#modalFormManual').on('show.bs.modal', function() {
                resetFormManual();
            });

            $('#modalFormManual').on('hidden.bs.modal', function() {
                resetFormManual();
            });
        });

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

        $(document).on('click', '.buttonForm', function() {
            const route = $(this).data('route');
            const value = $(this).data('value') || '';
            const id = $(this).data('id');

            $('#manualValueId').val(id);

            if (allowedDoubleManualRoutes.includes(route)) {
                $('#singleInputArea').hide();
                $('#doubleInputArea').show();

                let gaji = '';
                let bpjs = '';
                let rekrutmen = '';

                if (value && value.includes(',')) {
                    const parts = value.split(',');
                    gaji = parts[0] || '';
                    bpjs = parts[1] || '';
                    rekrutmen = parts[2] || '';
                } else {
                    gaji = value;
                    bpjs = '';
                    rekrutmen = '';
                }

                const gajiRaw = getRawNumber(gaji);
                const bpjsRaw = getRawNumber(bpjs);
                const rekrutmenRaw = getRawNumber(rekrutmen);

                $('#biaya_gaji_display').val(gajiRaw ? 'Rp ' + formatNumber(gajiRaw) : '');
                $('#biaya_gaji_tahunan').val(gajiRaw);
                $('#biaya_bpjs_display').val(bpjsRaw ? 'Rp ' + formatNumber(bpjsRaw) : '');
                $('#biaya_bpjs_tahunan').val(bpjsRaw);

                $('#biaya_rekrutmen_display').val(rekrutmenRaw ? 'Rp ' + formatNumber(rekrutmenRaw) : '');
                $('#biaya_rekrutmen_tahunan').val(rekrutmenRaw);

            } else {
                $('#singleInputArea').show();
                $('#doubleInputArea').hide();

                const format = $('#manual_format').val();
                const rawValue = getRawNumber(value);
                let displayValue = formatNumber(rawValue);

                if (format === 'rupiah' && rawValue) {
                    displayValue = 'Rp ' + displayValue;
                } else if (format === 'persen' && rawValue) {
                    displayValue = displayValue + '%';
                }

                $('#manual_value_display').val(displayValue);
                $('#manual_value').val(rawValue);
            }
        });

        $(document).on('change', '#manual_format', function() {
            if ($('#doubleInputArea').is(':visible')) {
                return;
            }

            const format = $(this).val();
            const rawValue = getRawNumber($('#manual_value').val());
            let displayValue = formatNumber(rawValue);

            if (format === 'rupiah' && rawValue) {
                displayValue = 'Rp ' + displayValue;
            } else if (format === 'persen' && rawValue) {
                displayValue = displayValue + '%';
            }

            $('#manual_value_display').val(displayValue);

            console.log('Format changed:', {
                format,
                rawValue,
                displayValue
            });
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
                url: "{{ route('kpi.manualValue') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    $('#modalFormManual').modal('hide');
                    resetFormManual();
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (typeof loadContentForm === 'function') {
                        loadContentForm();
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        const msg = Object.values(errors).map(e => e[0]).join('\n');
                        alert(msg);
                    } else {
                        alert('Terjadi kesalahan sistem: ' + (xhr.statusText || 'Unknown error'));
                    }
                }
            });
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
            const $assistantRoute = $form.find(`#${prefix}assistant_route`);
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

            $assistantRoute.off('change').on('change', function() {
                const value = $(this).val();
                const itsmRoutes = [
                    'kepuasan client ITSM',
                    'kualitas layanan support',
                    'keberhasilan support',
                    'ketepatan waktu penyelesaian fitur'
                ];
                if (value === 'Kepuasan Pelanggan' || itsmRoutes.includes(value)) {
                    $tipeTarget.html(`<option value="persen" selected>Persen (%)</option>`);
                    $tipeTarget.attr('disabled', true);
                    $jangkaTarget.empty().append(`<option value="Tahunan" selected>Tahunan</option>`);
                    $jangkaTarget.attr('disabled', true);
                } else {
                    $tipeTarget.html(`
                    <option selected disabled>-- Pilih Tipe --</option>
                    <option value="angka">Angka (Unit, Jumlah, dll)</option>
                    <option value="rupiah">Rupiah (Nilai Keuangan)</option>
                    <option value="persen">Persen (%)</option>
                `);
                    $tipeTarget.removeAttr('disabled');
                    $jangkaTarget.empty().append(`
                    <option selected disabled>-- Pilih Jangka --</option>
                    <option value="Tahunan">Tahunan</option>
                    <option value="Quartal">Quartal</option>
                    <option value="Bulanan">Bulanan</option>
                    <option value="Mingguan">Mingguan</option>
                `);
                    $jangkaTarget.removeAttr('disabled');
                }
                $detailJangkaGroup.hide();
                $konversiGroup.hide();
                $nilaiTarget.val('');
            });

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
                    let html = `<select class="form-select" name="detail_jangka" required>`;
                    html += `<option value="${tahunIni}" selected>${tahunIni}</option>`;
                    html += `<option value="${tahunIni + 1}">${tahunIni + 1}</option>`;
                    html += `</select>`;
                    $detailJangkaField.html(html);
                    $detailJangkaGroup.show();
                    updateKonversiIfNeeded();
                    return;
                }
            });

            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(Math.round(num));
            }
        }

        function loadContentForm() {
            $.ajax({
                url: '{{ route('kpi.getDataTarget') }}',
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
                        const groupedByPembuat = {};
                        data.detail.forEach(item => {
                            const idPembuat = item.id_pembuat;
                            if (!groupedByPembuat[idPembuat]) {
                                groupedByPembuat[idPembuat] = {
                                    nama_pembuat: item.pembuat,
                                    targets: []
                                };
                            }
                            groupedByPembuat[idPembuat].targets.push(item);
                        });

                        const getColor = (str) => {
                            let hash = 0;
                            for (let i = 0; i < str.length; i++) {
                                hash = str.charCodeAt(i) + ((hash << 5) - hash);
                            }
                            let color = '#';
                            for (let i = 0; i < 3; i++) {
                                const value = (hash >> (i * 8)) & 0xFF;
                                color += Math.floor(value * 0.7).toString(16).padStart(2, '0');
                            }
                            return color;
                        };

                        Object.entries(groupedByPembuat).forEach(([idPembuat, group]) => {
                            const bgColor = getColor(idPembuat);
                            const cardWrapper = $(`
                                    <div class="rounded-3 p-3" style="background-color: white; border: 4px solid ${bgColor}40;">
                                        <h6 class="mb-3 fw-bold" style="color: ${bgColor};">
                                            <i class="fa-solid fa-user me-1"></i> Target oleh: ${group.nama_pembuat || '–'}
                                        </h6>
                                        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center">
                                        </div>
                                    </div>
                                `);
                            const targetContainer = cardWrapper.find('div.d-flex');

                            group.targets.forEach(function(item) {
                                let formattedTarget = item.nilai_target;
                                if (item.tipe_target === 'persen' || item.tipe_target ===
                                    'angka') {
                                    formattedTarget = `${item.nilai_target}%`;
                                } else if (item.tipe_target === 'rupiah') {
                                    formattedTarget = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        minimumFractionDigits: 0
                                    }).format(item.nilai_target);
                                }

                                let jabatanDisplay = '-';
                                if (item.jabatan && item.jabatan.length > 0) {
                                    const jabatanList = item.jabatan;
                                    if (jabatanList.length === 1) {
                                        jabatanDisplay = jabatanList[0];
                                    } else {
                                        jabatanDisplay = jabatanList.map(j => j.substring(0,
                                            4) + '...').join(', ');
                                    }
                                }

                                let deadlineText = '';
                                let deadlineDate = null;
                                const detail = item.detail_jangka ? item.detail_jangka
                                    .toString().trim() : '';

                                if (item.jangka_target === 'tahunan') {
                                    const year = parseInt(detail);
                                    if (!isNaN(year)) {
                                        deadlineText = `31 Des ${year}`;
                                        deadlineDate = new Date(`${year}-12-31`);
                                    }
                                } else if (item.jangka_target === 'bulanan') {
                                    const parts = detail.split('-');
                                    if (parts.length === 2) {
                                        const [year, month] = parts;
                                        const lastDay = new Date(year, month, 0).getDate();
                                        const monthName = new Date(year, month - 1)
                                            .toLocaleString('id-ID', {
                                                month: 'short'
                                            });
                                        deadlineText = `${lastDay} ${monthName} ${year}`;
                                        deadlineDate = new Date(year, month - 1, lastDay);
                                    }
                                } else if (item.jangka_target === 'kuartalan') {
                                    const match = detail.match(/(\d{4})\D?Q?(\d)/i);
                                    if (match) {
                                        const year = match[1];
                                        const quarter = parseInt(match[2]);
                                        const monthEnd = quarter * 3;
                                        const lastDay = new Date(year, monthEnd, 0).getDate();
                                        const monthName = new Date(year, monthEnd - 1)
                                            .toLocaleString('id-ID', {
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
                                        const deadlineMillis = firstDay.getTime() + (week * 7 *
                                            24 * 60 * 60 * 1000);
                                        deadlineDate = new Date(deadlineMillis);
                                        deadlineText = `Minggu ke-${week}, ${year}`;
                                    }
                                }

                                let statusText = '';
                                let badgeClass = 'bg-secondary';
                                const now = new Date();
                                const year = now.getFullYear();
                                const month = String(now.getMonth() + 1).padStart(2, '0');
                                const day = String(now.getDate()).padStart(2, '0');
                                const nowTime = `${year}-${month}-${day}`;
                                let lengthProgress;

                                if (item.tipe_target === 'persen' || item.tipe_target ===
                                    'angka') {
                                    progressNumeric = parseFloat(item.progress) || 0;
                                    progressValueDisplay = `${progressNumeric}%`;
                                } else if (item.tipe_target === 'rupiah') {
                                    const target = parseFloat(item.nilai_target) || 0;
                                    const progressRupiah = parseFloat(item.progress) || 0;
                                    progressNumeric = target > 0 ? Math.min((progressRupiah /
                                        target) * 100, 100) : 0;
                                    progressValueDisplay = new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        minimumFractionDigits: 0
                                    }).format(progressRupiah);
                                }

                                lengthProgress = Math.max(0, Math.min(progressNumeric, 100));
                                let isTargetReached = false;

                                if (item.tipe_target === 'angka') {
                                    isTargetReached = item.manual_value >= item.nilai_target;
                                } else {
                                    isTargetReached = progressNumeric >= item.nilai_target;
                                }

                                if (progressNumeric === 0) {
                                    statusText = 'Belum Dimulai';
                                    badgeClass = 'bg-warning text-dark';

                                } else if (nowTime > item.tenggat_waktu && !isTargetReached) {
                                    statusText = 'Gagal';
                                    badgeClass = 'bg-danger';

                                } else if (nowTime > item.tenggat_waktu && isTargetReached) {
                                    statusText = 'Selesai';
                                    badgeClass = 'bg-success';

                                } else {
                                    statusText = 'Dalam Progress';
                                    badgeClass = 'bg-warning text-dark';
                                }

                                let buttonIsiForm = '';

                                if (allowedAssistantRoutes.includes(item.asistant_route)) {
                                    buttonIsiForm = `
                                            <div class="position-absolute top-0 p-3 start-0">
                                                <button type="button"
                                                    class="btn btn-sm btn-info rounded-circle d-flex align-items-center justify-content-center buttonForm"
                                                    data-id="${item.id}"
                                                    data-value="${item.manual_value}"
                                                    data-route="${item.asistant_route}"
                                                    title="isi data"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalFormManual"
                                                    style="width: 36px; height: 36px; font-size: 0.9rem;">
                                                    <i class="fa-solid fa-file-pen"></i>
                                                </button>
                                            </div>
                                        `;
                                }


                                targetContainer.append(`
                                        <div class="target-card rounded-4 border-1 shadow-md position-relative overflow-hidden" style="background: white; flex: 0 0 auto; border: 2px solid #f0f0f0; cursor: pointer;">
                                            <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: ${badgeClass === 'bg-success' ? '#28a745' :
                                        badgeClass === 'bg-danger' ? '#dc3545' : '#ffc107'
                                    };"></div>

                                            <div class="action-buttons position-absolute top-0 start-0 end-0 p-3 d-flex justify-content-between align-items-center"
                                                style="z-index: 10;">

                                                ${buttonIsiForm}

                                                <div class="d-flex gap-2 position-absolute top-0 end-0 p-3">
                                                    <button type="button" class="btn btn-sm btn-danger rounded-circle d-flex align-items-center justify-content-center buttonHapusTarget" data-id="${item.id}" title="Hapus" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>

                                            </div>

                                            <div data-id="${item.id}" id="buttonDetailTarget"  data-bs-toggle="modal" data-bs-target="#detailTargetModal">

                                            <div class="p-3 pt-4 mt-4">
                                                <h5 class="fw-bold mb-2 fs-6 text-dark" style="min-height: 2.2em; line-height: 1.2;">
                                                    ${item.judul}
                                                </h5>

                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge bg-light text-primary border border-primary me-2" style="font-size: 0.75rem;">
                                                        ${item.jangka_target.charAt(0).toUpperCase() + item.jangka_target.slice(1)}
                                                    </span>
                                                    <span class="badge ${badgeClass}" style="font-size: 0.75rem;">${statusText}</span>
                                                </div>

                                                <div class="mb-2">
                                                    <p class="mb-1 text-muted small">
                                                        <i class="fa-solid fa-bullseye me-1"></i>
                                                        <strong>Target:</strong> ${formattedTarget}
                                                    </p>
                                                </div>

                                                <div class="small text-muted mb-2" style="font-size: 0.82rem;">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Jabatan</span>
                                                        <span class="fw-medium">${jabatanDisplay}</span>
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
                                                    <div class="progress rounded-pill" style="height: 12px; background-color: #e9ecef; position: relative;">
                                                        <div class="progress-bar rounded-pill"
                                                            style="width: ${lengthProgress}%; background: ${badgeClass === 'bg-success' ? '#28a745' :
                                                                    badgeClass === 'bg-danger' ? '#dc3545' : '#ffc107'
                                                                }"></div>
                                                        <span class="position-absolute top-50 start-50 translate-middle" style="font-size: 0.7rem; color: black;">
                                                            ${progressValueDisplay}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center justify-content-between mt-1">
                                                    <small class="text-muted">
                                                        <i class="fa-solid fa-calendar-days me-1"></i>
                                                        ${item.tenggat_waktu}
                                                    </small>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                    `);
                            });
                            content_target.append(cardWrapper);
                        });
                    }

                    const $editJangka = $('#edit_jangka_target');
                    $editJangka.empty().append('<option selected disabled>-- Pilih Jangka --</option>');
                    $editJangka.append(`<option value="Tahunan">Tahunan</option>`);

                    jangkaSelect.empty().append('<option selected disabled>-- Pilih Jangka Target --</option>');
                    pembuatContainer.empty();

                    const jabatanTersedia = data.jabatan_list || [];
                    const detailTargets = data.detail || [];
                    const jabatanCount = {};
                    detailTargets.forEach(d => {
                        jabatanCount[d.jabatan] = (jabatanCount[d.jabatan] || 0) + 1;
                    });

                    jabatanTersedia.forEach(jab => {
                        if (jabatanSelect.find(`option[value="${jab}"]`).length > 0) {
                            return;
                        }

                        const count = jabatanCount[jab] || 0;
                        const isDisabled = false;
                        let label = jab;

                        jabatanSelect.append(`
                                <option value="${jab}">
                                    ${label}
                                </option>
                            `);
                    });

                    jangkaSelect.append(`<option value="Tahunan">Tahunan</option>`);

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
                            const idValue = p.id ?? idx;
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
                    Swal.fire('Error', 'Gagal memuat data form: ' + (xhr.responseJSON?.message ||
                        'Silakan coba lagi.'), 'error');
                }
            });
        }

        $('#jabatan').on('change', function() {
            const selectedJabatan = $(this).val();
            const karyawanSelect = $('#karyawan');

            if (!selectedJabatan || selectedJabatan.length === 0) {
                karyawanSelect.empty().trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('kpi.getKaryawanByJabatan') }}",
                type: 'GET',
                data: {
                    jabatan: selectedJabatan
                },
                success: function(response) {
                    karyawanSelect.empty();

                    response.forEach(item => {
                        const option = new Option(item.text, item.id, false, false);
                        karyawanSelect.append(option);
                    });

                    karyawanSelect.trigger('change');
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memuat daftar karyawan.', 'error');
                    karyawanSelect.empty().trigger('change');
                }
            });
        });


        $(document).on('click', '#buttonDetailTarget', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route('kpi.detail') }}",
                method: 'GET',
                data: {
                    id
                },
                dataType: 'json',
                success: function(response) {
                    const body = $('#bodyContentDetailTarget');
                    if (body.length === 0) {
                        console.error("Elemen #bodyContentDetailTarget tidak ditemukan!");
                        return;
                    }

                    body.empty();

                    let detailArray = response.detail;
                    let data = detailArray && detailArray.length > 0 ? detailArray[0].data : null;

                    if (!data) {
                        body.append(`
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Target</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            Belum ada data
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    `);
                    } else {
                        const monthlyData = data.data_detail.monthly_data || {};
                        const dailyData = data.data_detail.daily_breakdown_per_month || {};

                        const dateNow = "{{ now()->format('Y-m-d') }}";
                        const startOfYear = "{{ now()->startOfYear()->format('Y-m-d') }}";
                        const tenggatWaktu = data.tenggat_waktu;

                        function isDateBefore(date1, date2) {
                            return date1 < date2;
                        }

                        function isDateAfter(date1, date2) {
                            return date1 > date2;
                        }

                        let Tercapai;
                        if (isDateBefore(dateNow, startOfYear)) {
                            Tercapai = "Belum Dimulai";
                        } else if (isDateAfter(dateNow, tenggatWaktu) || dateNow === tenggatWaktu) {
                            Tercapai = data.data_detail.progress >= data.nilai_target ?
                                "Mencapai Target" : "Target Gagal";
                        } else {
                            Tercapai = data.data_detail.progress >= data.nilai_target ?
                                "Mencapai Target" : "Sedang Berjalan";
                        }

                        let targetValue, progressValue, gapValue;
                        if (data.tipe_target === "persen") {
                            targetValue = data.nilai_target + ' %';
                            progressValue = data.data_detail.progress + ' %';
                            gapValue = data.data_detail.gap + ' %';
                        } else if (data.tipe_target === "rupiah") {
                            const formatter = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            });
                            targetValue = formatter.format(data.nilai_target);
                            progressValue = formatter.format(data.data_detail.progress);
                            gapValue = formatter.format(Math.abs(data.data_detail.gap));
                        } else {
                            targetValue = data.nilai_target;
                            progressValue = data.data_detail.progress;
                            gapValue = data.data_detail.gap;
                        }

                        let bgCard;

                        if (Tercapai === "Mencapai Target") {
                            bgCard = "success";
                        } else if (Tercapai === "Target Gagal") {
                            bgCard = "danger";
                        } else if (Tercapai === "Sedang Berjalan") {
                            bgCard = "warning";
                        } else if (Tercapai === "Belum Berjalan") {
                            bgCard = "secondary";
                        } else {
                            bgCard = "secondary";
                        }

                        let textTitle;

                        const pieChart = data.data_detail.pie_chart || {
                            above: 0,
                            below: 0
                        };
                        const dataPieChart = {
                            labels: ['Above', 'Below'],
                            datasets: [{
                                label: 'Jumlah',
                                data: [pieChart.above ?? 0, pieChart.below ?? 0],
                                backgroundColor: ['#B66DFF', '#FE7C96'],
                                hoverOffset: 4
                            }]
                        };

                        let StripedProgress;

                        if (data.tipe_target === "persen" || data.tipe_target === "rupiah") {
                            StripedProgress = data.nilai_target;
                        } else if (data.tipe_target === "angka") {
                            StripedProgress = "100";
                        } else if (data.tipe_target === "rupiah") {
                            StripedProgress = data.data_detail.progress;
                        }

                        setTimeout(() => {
                            const ctx = document.getElementById('MyChartDoughtnut');
                            if (ctx) {
                                new Chart(ctx, {
                                    type: 'doughnut',
                                    data: dataPieChart,
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    boxWidth: 12,
                                                    padding: 15
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        }, 0);


                        const formatRupiah = (nilai = 0) =>
                            'Rp ' + Number(nilai).toLocaleString('id-ID');

                        const formatTanggalSingkat = (tanggalString) => {
                            const tanggal = new Date(tanggalString);
                            return tanggal.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short'
                            });
                        };

                        const dataBulananRupiah = data.data_detail.monthly_data;
                        const dataHarianRupiah = data.data_detail.daily_breakdown_per_month;
                        const karyawanTerkaitRupiah = data.karyawan?.[0];
                        const keyBulanTerakhirRupiah = Object.keys(dataBulananRupiah).sort().pop();
                        const nilaiBulanTerakhirRupiah = dataBulananRupiah[keyBulanTerakhirRupiah];

                        const labelBulanTerakhir = new Date(`${keyBulanTerakhirRupiah}-01`)
                            .toLocaleDateString('id-ID', {
                                month: 'long',
                                year: 'numeric'
                            });

                        const seluruhDataHarian = Object.values(dataHarianRupiah)
                            .flatMap(bulan => Object.entries(bulan));

                        const top3HariTertinggi = seluruhDataHarian
                            .filter(([_, nilai]) => nilai > 0)
                            .sort((a, b) => b[1] - a[1])
                            .slice(0, 3);

                        const karyawanList = Array.isArray(data.karyawan) ? data.karyawan : [data
                            .karyawan
                        ];

                        let no = 1;
                        const karyawanHtml = karyawanList.map(item => `
                                <div class="d-flex align-items-center py-2 participant-item">
                                    <div class="avatar me-3">${no++}</div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark small">${item.nama_lengkap}</div>
                                        <div class="text-muted small">${item.jabatan}</div>
                                    </div>
                                </div>
                            `).join('');

                        const allowedAssistantRoutes = [
                            'dorong inovasi pelayanan',
                            'rasio biaya operasional terhadap revenue',
                            'inisiatif efisiensi keuangan',
                            'mengurangi manual work dan error',
                            'pengeluaran biaya karyawan'
                        ];

                        const allowedAssistantRoutesForRupiah = [
                            'Pemasukan Kotor',
                            'meningkatkan revenue perusahaan',
                            'target penjualan tahunan'
                        ];

                        const allowedAssistantRoutesForPresentaseGapKompetensi = [
                            'persentase gap kompetensi tim terhadap standar skill'
                        ]

                        const allowedAssistantRoutesForTargetPenjualanTahunan = [
                            'target penjualan tahunan',
                            'Pemasukan Kotor'
                        ]

                        const allowedAssistantRoutesForPeningkatanKontribusiPelatihan = [
                            'peningkatan kontribusi pelatihan',
                        ]

                        const allowedAssistantRoutesForPemasukanBersih = [
                            'pemasukan bersih'
                        ];

                        const allowedAssistantRoutesForLaporanAnalisisKeuangan = [
                            'laporan analisis keuangan'
                        ];
                        
                        let ContentTrafikSales = '';

                        if (allowedAssistantRoutesForTargetPenjualanTahunan.includes(data.condition)) {
                            const salesPerf = data.data_detail?.sales_performance;
                            
                            if (salesPerf && salesPerf.data) {
                                const formatRupiah = (num) => {
                                    return 'Rp ' + Number(num).toLocaleString('id-ID');
                                };

                                if (salesPerf.type === 'individual') {
                                    const s = salesPerf.data;
                                    const statusClass = s.status === 'achieved' ? 'badge-success' : 'badge-warning';
                                    const progressColor = s.status === 'achieved' ? '#28a745' : '#ffc107';
                                    const progressWidth = Math.min(s.percentage, 100);

                                    ContentTrafikSales = `
                                        <div class="card shadow-sm mb-4 mt-2    ">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Sales:</strong> ${s.nama}</p>
                                                        <p class="mb-2"><strong>Revenue:</strong> ${formatRupiah(s.revenue)}</p>
                                                        <p class="mb-3"><strong>Target:</strong> ${formatRupiah(s.presentase_kemampuan)}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-2">
                                                            <div class="d-flex justify-content-between">
                                                                <span>Progress</span>
                                                                <span>${s.percentage}%</span>
                                                            </div>
                                                            <div class="progress" style="height: 10px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                    style="width: ${progressWidth}%; background-color: ${progressColor};"
                                                                    aria-valuenow="${s.percentage}" aria-valuemin="0" aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="badge ${statusClass} p-2">${s.status.toUpperCase()}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }
                                
                                else if (salesPerf.type === 'all') {
                                    let rows = '';
                                    
                                    salesPerf.data.forEach((sales, index) => {
                                        const statusClass = sales.status === 'achieved' ? 'badge-success' : 'badge-warning';
                                        const textClass = sales.status === 'achieved' ? 'text-success' : 'text-warning';
                                        
                                        const targetValue = Number(sales.presentase_kemampuan).toLocaleString('id-ID', { useGrouping: false });
                                        
                                        rows += `
                                            <tr id="row-${sales.kode_karyawan}">
                                                <td class="text-center">${index + 1}</td>
                                                <td><strong>${sales.nama}</strong></td>
                                                <td class="text-right">${formatRupiah(sales.revenue)}</td>
                                                <td class="text-center">
                                                    <div class="input-group input-group-sm" style="max-width: 150px; float: right;">
                                                        <input type="text" 
                                                            class="form-control text-right target-input ${sales.id_detailPerson ? '' : 'is-invalid'}" 
                                                            value="${targetValue}" 
                                                            data-id-detail="${sales.id_detailPerson || ''}"
                                                            data-kode-karyawan="${sales.kode_karyawan}"
                                                            placeholder="Target"
                                                            ${!sales.id_detailPerson ? 'disabled' : ''}
                                                        >
                                                    </div>
                                                    <div class="loading-spinner" style="display: none; float: right; margin-right: 10px;">
                                                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                                    </div>
                                                    <div class="update-feedback" style="display: none; float: right; margin-right: 10px; margin-top: 5px;">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </td>
                                                <td class="text-center ${textClass}"><strong>${sales.percentage}%</strong></td>
                                                <td class="text-center">
                                                    <span class="badge ${statusClass}">${sales.status.toUpperCase()}</span>
                                                </td>
                                            </tr>
                                        `;
                                    });

                                    let htmlTargetTahunanSales = '';

                                    Object.entries(data.data_detail.triwulan_data).forEach(([label, value]) => {
                                        htmlTargetTahunanSales += `
                                            <div class="col-md-6">
                                                <div class="card h-100 shadow-sm border-0">
                                                    <div class="card-body">
                                                        <h5 class="card-title">${label.replace('_', ' ')}</h5>
                                                        <p class="card-text">Rp ${value.toLocaleString('id-ID')}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    });

                                    ContentTrafikSales = `
                                        <div class="row">
                                            <div class="col">
                                                <div class="card shadow-sm mt-3">
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover mb-0">
                                                                <thead class="thead-light">
                                                                    <tr>
                                                                        <th class="text-center" width="5%">No</th>
                                                                        <th>Sales</th>
                                                                        <th class="text-right" width="20%">Revenue</th>
                                                                        <th class="text-right" width="20%">Target (Editable)</th>
                                                                        <th class="text-center" width="15%">Persentase</th>
                                                                        <th class="text-center" width="15%">Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    ${rows}
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="col">
                                                    <div class="card shadow-sm border-0 mt-3">
                                                        <div class="card-body">
                                                            <div class="row g-3">
                                                                ${htmlTargetTahunanSales}
                                                            </div>
                                                        </div>

                                                        <div class="mb-2 text-center">
                                                            <hr>
                                                            <p>Data Triwulan diambil dari tahun ${data.detail_jangka}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    `;

                                    setTimeout(() => {
                                        document.querySelectorAll('.target-input').forEach(input => {
                                            let timeout = null;
                                            
                                            input.addEventListener('input', function() {
                                                const el = this;
                                                const row = el.closest('tr');
                                                const spinner = row.querySelector('.loading-spinner');
                                                const feedback = row.querySelector('.update-feedback');
                                                
                                                clearTimeout(timeout);
                                                
                                                feedback.style.display = 'none';
                                                
                                                timeout = setTimeout(() => {
                                                    const idDetailPerson = el.dataset.idDetail;
                                                    const kodeKaryawan = el.dataset.kodeKaryawan;
                                                    const newTarget = el.value.replace(/\./g, ''); 
                                                    
                                                    if (!idDetailPerson) {
                                                        el.classList.add('is-invalid');
                                                        return;
                                                    }
                                                    
                                                    spinner.style.display = 'inline-block';
                                                    el.disabled = true;
                                                    
                                                    $.ajax({
                                                        url: "{{ route('kpi.overview.updateTargetPerSales') }}", 
                                                        method: 'POST',
                                                        data: {
                                                            _token: '{{ csrf_token() }}',
                                                            id_detailPerson: idDetailPerson,
                                                            kode_karyawan: kodeKaryawan,
                                                            presentase_kemampuan: newTarget
                                                        },
                                                        success: function(response) {
                                                            spinner.style.display = 'none';
                                                            el.disabled = false;
                                                            
                                                            feedback.style.display = 'inline-block';
                                                            el.classList.remove('is-invalid');
                                                            el.classList.add('is-valid');
                                                            
                                                            if (response.data) {
                                                                const percentageCell = row.querySelector('td:nth-child(5)');
                                                                const statusCell = row.querySelector('td:nth-child(6)');
                                                                
                                                                if (response.data.percentage) {
                                                                    percentageCell.innerHTML = `<strong class="${response.data.status === 'achieved' ? 'text-success' : 'text-warning'}">${response.data.percentage}%</strong>`;
                                                                }
                                                                
                                                                if (response.data.status) {
                                                                    const statusClass = response.data.status === 'achieved' ? 'badge-success' : 'badge-warning';
                                                                    statusCell.innerHTML = `<span class="badge ${statusClass}">${response.data.status.toUpperCase()}</span>`;
                                                                }
                                                            }
                                                            
                                                            setTimeout(() => {
                                                                feedback.style.display = 'none';
                                                                el.classList.remove('is-valid');
                                                            }, 2000);
                                                        },
                                                        error: function(xhr) {
                                                            spinner.style.display = 'none';
                                                            el.disabled = false;
                                                            
                                                            el.classList.add('is-invalid');
                                                            
                                                            console.error('Update failed:', xhr.responseText);
                                                        }
                                                    });
                                                }, 1000); 
                                            });
                                            
                                            input.addEventListener('blur', function() {
                                                const value = this.value.replace(/\./g, '');
                                                if (value) {
                                                    this.value = Number(value).toLocaleString('id-ID');
                                                }
                                            });
                                            
                                            input.addEventListener('focus', function() {
                                                const value = this.value.replace(/\./g, '');
                                                if (value) {
                                                    this.value = value;
                                                }
                                            });
                                        });
                                    }, 100);
                                }
                            }
                        } else if (allowedAssistantRoutesForPeningkatanKontribusiPelatihan.includes(data.condition)) {
                            ContentTrafikSales = `
                            <div class="card mt-4 border-0 rounded-4" style="background:#f8fafc;">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-end mb-4">
                                        <div>
                                            <div class="text-muted small">Total Kelas</div>
                                            <div class="fs-2 fw-semibold text-dark">
                                                ${data.data_detail.class_breakdown.total}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-white border h-100">
                                                <div class="text-muted small mb-2">Kelas Inixindo</div>
                                                <div class="fs-4 fw-semibold text-dark">
                                                    ${data.data_detail.class_breakdown.kelas_od}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-white border h-100">
                                                <div class="text-muted small mb-2">Kelas Orang Luar</div>
                                                <div class="fs-4 fw-semibold text-dark">
                                                    ${data.data_detail.class_breakdown.kelas_ol}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-white border h-100">
                                                <div class="text-muted small mb-2">Kelas Offline</div>
                                                <div class="fs-4 fw-semibold text-dark">
                                                    ${data.data_detail.class_breakdown.kelas_offline}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="p-3 rounded-3 bg-white border h-100">
                                                <div class="text-muted small mb-2">Kelas Online</div>
                                                <div class="fs-4 fw-semibold text-dark">
                                                    ${data.data_detail.class_breakdown.kelas_online}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 rounded-3 bg-white border">
                                        <div class="fw-semibold mb-3">Kelas Inhouse</div>
                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                            <span class="text-muted small">Bandung</span>
                                            <span class="fw-semibold text-dark">
                                                ${data.data_detail.class_breakdown.Inhouse.kelas_inhouse}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between py-2">
                                            <span class="text-muted small">Luar Bandung</span>
                                            <span class="fw-semibold text-dark">
                                                ${data.data_detail.class_breakdown.Inhouse.kelas_inhouse_luar}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                        } else {
                            ContentTrafikSales = '';
                        }

                        let contentPieChart = '';

                        if (allowedAssistantRoutes.includes(data.condition)) {
                            const fileUrl = data.data_detail.dataManual.manual_document;
                            const fileName = fileUrl ? fileUrl.split('/').pop() : '';
                            const fileExtension = fileName ? fileName.split('.').pop().toLowerCase() :
                                '';

                            const imageExtensions = ['jpg', 'jpeg', 'png'];
                            const pdfExtensions = ['pdf'];

                            let fileContent = '';

                            const getFileUrl = (path) => {
                                return path ? `/storage/${path}` : '';
                            };

                            const fullFileUrl = getFileUrl(fileUrl);

                            if (imageExtensions.includes(fileExtension)) {
                                fileContent = `
                                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                            <div class="mb-3" style="max-width: 100%; max-height: 300px;">
                                                <img src="${fullFileUrl}" alt="${fileName}" class="img-fluid rounded shadow-sm" style="max-width: 100%; max-height: 300px; object-fit: contain;">
                                            </div>
                                            <div class="mt-2 text-center">
                                                <a href="${fullFileUrl}" download="${fileName}" class="btn btn-primary btn-sm">
                                                    <i class="fa-solid fa-download me-1"></i>Download Gambar
                                                </a>
                                            </div>
                                            <div class="mt-2 small text-muted">
                                                <i class="fa-solid fa-file-image me-1"></i>${fileName}
                                            </div>
                                        </div>
                                    `;
                            } else if (pdfExtensions.includes(fileExtension)) {
                                fileContent = `
                                        <div class="w-100 h-100 d-flex flex-column">
                                            <div class="flex-grow-1 mb-3" style="min-height: 250px;">
                                                <iframe src="${fullFileUrl}" class="w-100 h-100" style="border: 1px solid #dee2e6; border-radius: 8px;"></iframe>
                                            </div>
                                            <div class="text-center">
                                                <a href="${fullFileUrl}" download="${fileName}" class="btn btn-primary btn-sm">
                                                    <i class="fa-solid fa-download me-1"></i>Download PDF
                                                </a>
                                            </div>
                                            <div class="mt-2 small text-muted text-center">
                                                <i class="fa-solid fa-file-pdf me-1"></i>${fileName}
                                            </div>
                                        </div>
                                    `;
                            } else {
                                fileContent = `
                                        <div class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fa-solid fa-file text-secondary" style="font-size: 4rem;"></i>
                                            </div>
                                            <p class="text-muted mb-3">File tidak dapat ditampilkan</p>
                                            <p class="text-muted small">Hanya gambar dan PDF yang dapat ditampilkan</p>
                                        </div>
                                    `;
                            }

                            contentPieChart = `
                                    <h6 class="fw-semibold mb-3 text-secondary">
                                        <i class="fa-solid fa-file me-2"></i>Dokumen Manual
                                    </h6>

                                    <div class="manual-document-container flex-grow-1 d-flex flex-column align-items-center justify-content-center p-3" style="background-color: #f8f9fa; border-radius: 8px;">
                                        ${fileContent}
                                    </div>

                                    <div class="mt-3 small text-muted text-center">
                                        <i class="fa-solid fa-info-circle me-1"></i>
                                        Klik tombol download untuk menyimpan file
                                    </div>
                                `;
                        } else if (allowedAssistantRoutesForRupiah.includes(data.condition)) {
                            contentPieChart = `
                                <div class="p-2">

                                    <div class="mb-4">
                                        <small class="text-muted">Ringkasan performa</small>
                                    </div>

                                    <div class="mb-4 p-3 rounded-3 border bg-white shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-muted small">Bulan Terakhir</div>
                                                <div class="fw-semibold">${labelBulanTerakhir}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold fs-5 text-dark">
                                                    ${formatRupiah(nilaiBulanTerakhirRupiah)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="text-muted small mb-2 fw-semibold">
                                            Top Hari Tertinggi
                                        </div>

                                        ${top3HariTertinggi.map(([tanggal, nilai], index) => `
                                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded 
                                                ${index === 0 ? 'bg-success-subtle' : 'bg-light'}">

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge ${index === 0 ? 'bg-success' : 'bg-secondary'}">
                                                        #${index + 1}
                                                    </span>
                                                    <span class="${index === 0 ? 'fw-semibold text-dark' : 'text-muted'}">
                                                        ${formatTanggalSingkat(tanggal)}
                                                    </span>
                                                </div>

                                                <span class="fw-semibold ${index === 0 ? 'text-success' : 'text-dark'}">
                                                    ${formatRupiah(nilai)}
                                                </span>
                                            </div>
                                        `).join('')}
                                    </div>

                                    <hr class="my-3">

                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-person-circle fs-4 text-secondary"></i>
                                            <div>
                                                <div class="fw-semibold text-dark">
                                                    ${karyawanTerkaitRupiah?.nama_lengkap ?? '-'}
                                                </div>
                                                <small class="text-muted">
                                                    ${karyawanTerkaitRupiah?.jabatan ?? '-'}
                                                </small>
                                            </div>
                                        </div>

                                        <span class="badge bg-light text-dark border">
                                            Karyawan
                                        </span>
                                    </div>

                                </div>
                            `;

                        } else {
                            contentPieChart = `
                                    <h6 class="fw-semibold mb-3 text-secondary">
                                        <i class="fa-solid fa-chart-pie me-2"></i>Chart ${data.condition}
                                    </h6>

                                    <div class="chart-container flex-grow-1">
                                        <canvas id="MyChartDoughtnut"></canvas>
                                    </div>
                                `;
                        }

                        let contentStatisticChart = '';

                        if (allowedAssistantRoutes.includes(data.condition)) {
                            contentStatisticChart = ``;
                        } else if (allowedAssistantRoutesForLaporanAnalisisKeuangan.includes(data.condition)) {
                            const bulanIndo = [
                                '',
                                'Januari',
                                'Februari',
                                'Maret',
                                'April',
                                'Mei',
                                'Juni',
                                'Juli',
                                'Agustus',
                                'September',
                                'Oktober',
                                'November',
                                'Desember'
                            ];
                            contentStatisticChart = `
                            <div class="row g-4">

                                ${(data.data_detail.analisa_data).map(item => `
                                <div class="col-md-4 mt-5">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="mb-2 p-3">
                                            <h5 class="fw-bold text-primary mb-1">${bulanIndo[item.month]}</h5>
                                            <small class="text-muted">Laporan Analisis Bulanan</small>
                                        </div>
                                        <div class="card-body d-flex flex-column" style="overflow-y: scroll; max-height: 280px;">

                                            <div class="mb-3">
                                                <p class="mb-0" style="text-align: justify;">
                                                    ${item.description}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-auto p-3">
                                            <a href="{{ asset('${item.file_paths}') }}" class="btn btn-sm btn-outline-primary w-100">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                `).join('')}

                            </div>
                            `;
                        } else if (allowedAssistantRoutesForPemasukanBersih.includes(data.condition)) {
                            const bulanIndo = [
                                "Januari", "Februari", "Maret", "April",
                                "Mei", "Juni", "Juli", "Agustus",
                                "September", "Oktober", "November", "Desember"
                            ];

                            contentStatisticChart = `
                                <div class="mt-4">
                                    <div class="row g-4">

                                        ${(data.data_detail.previous_quarter.data || []).map((item, index) => `
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <div class="card border-0 shadow-sm h-100 quarter-card">
                                                    <div class="card-body d-flex flex-column p-4">

                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div>
                                                                <h6 class="fw-semibold text-muted mb-1">Periode</h6>
                                                                <h5 class="fw-bold mb-0">
                                                                    ${bulanIndo[item.month - 1] ?? '-'}
                                                                </h5>
                                                            </div>
                                                            <span class="badge rounded-pill bg-${item.color} bg-opacity-10 text-${item.color} px-3 py-2">
                                                                Laporan
                                                            </span>
                                                        </div>

                                                        <div class="mb-3">
                                                            <h3 class="fw-bold text-dark mb-0">
                                                                Rp ${item.nilai ? Number(item.nilai).toLocaleString('id-ID') : '-'}
                                                            </h3>
                                                            <small class="text-muted">Total Pemasukan</small>
                                                        </div>

                                                        <div class="flex-grow-1">
                                                            <p class="text-muted small mb-2 description-text" id="desc-${index}">
                                                                ${item.description ?? '-'}
                                                            </p>
                                                            ${(item.description && item.description.length > 100) ? `
                                                                <button class="btn btn-sm btn-link p-0 text-primary btn-toggle-desc" data-target="desc-${index}">
                                                                    Lihat Selengkapnya
                                                                </button>
                                                            ` : ''}
                                                        </div>

                                                        <div class="d-flex justify-content-end align-items-center mt-4">
                                                            <a href="{{ asset('${item.file_paths}') }}" class="btn btn-sm btn-dark d-flex align-items-center gap-2" download>
                                                                <i class="fas fa-download"></i>
                                                                Download
                                                            </a>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}

                                    </div>
                                </div>

                                <style>
                                    .quarter-card {
                                        border-radius: 16px;
                                        transition: all 0.25s ease;
                                        background: #ffffff;
                                    }

                                    .quarter-card:hover {
                                        transform: translateY(-6px) scale(1.01);
                                        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
                                    }

                                    .quarter-card h3 {
                                        letter-spacing: 0.5px;
                                    }

                                    .quarter-card .badge {
                                        font-size: 12px;
                                        font-weight: 500;
                                    }

                                    .description-text {
                                        display: -webkit-box;
                                        -webkit-line-clamp: 3;
                                        -webkit-box-orient: vertical;
                                        overflow: hidden;
                                    }

                                    .description-text.expanded {
                                        -webkit-line-clamp: unset;
                                        overflow: visible;
                                    }

                                    .quarter-card .btn {
                                        border-radius: 8px;
                                        font-size: 13px;
                                    }
                                </style>
                            `;

                                        setTimeout(() => {
                                        document.querySelectorAll('.btn-toggle-desc').forEach(btn => {
                                            btn.addEventListener('click', function () {
                                                const targetId = this.getAttribute('data-target');
                                                const textEl = document.getElementById(targetId);

                                                if (textEl.classList.contains('expanded')) {
                                                    textEl.classList.remove('expanded');
                                                    this.innerText = 'Lihat Selengkapnya';
                                                } else {
                                                    textEl.classList.add('expanded');
                                                    this.innerText = 'Sembunyikan';
                                                }
                                            });
                                        });
                                    }, 0);
                        } else if (allowedAssistantRoutesForPresentaseGapKompetensi.includes(data.condition)) {
                            contentStatisticChart = `
                                <div class="mt-4">
                                    <div class="card shadow-sm border-0 rounded-4">
                                        <div class="card-body">
                                            <h6 class="fw-semibold mb-3">Input Presentase Kemampuan Programmer</h6>

                                            <form id="formGapKompetensi">
                                                @php
                                                    $allowed = auth()->user()->jabatan === 'Koordinator ITSM';
                                                @endphp

                                                <div class="row mb-2 fw-semibold text-muted border-bottom pb-2">
                                                    <div class="col-md-4">Nama Karyawan</div>
                                                    <div class="col-md-4">Kemampuan (%)</div>
                                                    <div class="col-md-4">Standar (%)</div>
                                                </div>

                                                <!-- DATA -->
                                                ${(data.karyawan || []).map((item, index) => {

                                                    const kemampuan = parseFloat(item.presentase_kemampuan ?? 0);
                                                    const standar = parseFloat(item.presentase_standar ?? 100);

                                                    let badge = '';
                                                    let rowClass = '';

                                                    if (kemampuan === 0) {
                                                        badge = `<span class="badge bg-danger">0%</span>`;
                                                    } else if (kemampuan < standar) {
                                                        badge = `<span class="badge bg-warning text-dark">Not Achieved</span>`;
                                                    } else {
                                                        badge = `<span class="badge bg-success">Achieved</span>`;
                                                    }

                                                    return `
                                                        <div class="row mb-2 align-items-center p-2 rounded">
                                                            
                                                            <div class="col-md-4 d-flex justify-content-between align-items-center">
                                                                <span>${item.nama_lengkap ?? '-'}</span>
                                                                ${badge}
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="number" step="0.1" class="form-control kemampuan-input"
                                                                    name="data[${index}][kemampuan]"
                                                                    value="${kemampuan}" {{ $allowed ? '' : 'disabled' }}>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="number" step="0.1" class="form-control standar-input"
                                                                    name="data[${index}][standar]"
                                                                    value="${standar}" {{ $allowed ? '' : 'disabled' }}>
                                                            </div>

                                                            <input type="hidden" name="data[${index}][id]" value="${item.id}">
                                                        </div>
                                                    `;
                                                }).join('')}

                                                <!-- BUTTON -->
                                                @if (auth()->user()->jabatan === 'Koordinator ITSM')
                                                    <div class="mt-3">
                                                        <button type="submit" class="btn btn-primary">
                                                            Simpan
                                                        </button>
                                                    </div>
                                                @endif

                                            </form>
                                        </div>
                                    </div>
                                </div>
                                `;

                            $(document).on('submit', '#formGapKompetensi', function(e) {
                                e.preventDefault();

                                let formData = $(this).serialize();

                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });

                                $.ajax({
                                    url: "{{ route('kpi.updateGapKompetensi') }}",
                                    method: 'POST',
                                    data: formData,
                                    success: function(res) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil!',
                                            text: 'berhasil diupdate.',
                                            timer: 2000,
                                            showConfirmButton: false
                                        }).then(() => {
                                            $('#detailTargetModal').modal('hide');
                                        });
                                        loadContentForm();
                                    },
                                    error: function(err) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal!',
                                            html: msg
                                        });
                                        console.log(err);
                                    }
                                });
                            });

                        } else {
                            contentStatisticChart = `
                                    <div class="mt-4">
                                        <div class="card shadow-sm border-0 rounded-4">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-3">
                                                    <h6 class="fw-semibold mb-0">Statistik ${data.condition}</h6>
                                                    <div class="d-flex gap-2">
                                                        <select class="form-select form-select-sm" id="filterType">
                                                            <option value="year">Per Tahun</option>
                                                            <option value="month">Per Bulan</option>
                                                        </select>
                                                        <select class="form-select form-select-sm d-none" id="filterMonth">
                                                            <option value="">Pilih Bulan</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div style="height:300px">
                                                    <canvas id="StatisticChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                        }

                        let widthProgress;

                        if (data.tipe_target === "rupiah") {
                            widthProgress = Number(
                                ((data.data_detail.progress / data.nilai_target) * 100).toFixed(1)
                            );
                        } else if (data.tipe_target === "angka" || data.tipe_target === "persen") {
                            widthProgress = data.data_detail.progress;
                        }


                        body.append(`
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">${data.judul}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body pt-3">
                                <div class="container-fluid p-3">
                                    <div class="row g-4">
                                        <div class="col-lg-8">
                                            <div class="card shadow h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span class="badge bg-primary">${data.jangka_target}</span>
                                                        <span class="badge bg-${bgCard}">${Tercapai}</span>
                                                    </div>

                                                    <div class="row text-center mb-3">
                                                        <div class="col">
                                                            <small class="text-muted d-block">Target</small>
                                                            <h4 class="fw-bold mb-0" style="font-size: 25px">${targetValue}</h4>
                                                        </div>
                                                        <div class="col">
                                                            <small class="text-muted d-block">Progress</small>
                                                            <h1 class="fw-bold text-${bgCard} mb-0" style="font-size: 55px;">${progressValue}</h1>
                                                        </div>
                                                        <div class="col">
                                                            <small class="text-muted d-block">Gap</small>
                                                            <h4 class="fw-bold text-danger mb-0" style="font-size: 25px">-${gapValue}</h4>
                                                        </div>
                                                    </div>

                                                    <div class="position-relative mb-3">
                                                        <div class="progress" style="height:18px;">
                                                            <div class="progress-bar bg-${bgCard} progress-bar-striped progress-bar-animated"
                                                                style="width: ${widthProgress}%"></div>
                                                        </div>
                                                        <div class="position-absolute bg-light top-0" style="left: ${StripedProgress}%; height:18px; width:2px;"></div>
                                                    </div>

                                                    <div class="text-muted mb-4">
                                                        <i class="fa-solid fa-calendar-days me-1"></i>
                                                        Deadline: <strong>${data.tenggat_waktu}</strong>
                                                    </div>

                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 kpi-card">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        <div class="me-2 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                                            <i class="fa-solid fa-chart-line text-primary"></i>
                                                                        </div>
                                                                        <h6 class="mb-0 fw-semibold text-secondary">INFORMASI KPI</h6>
                                                                    </div>
                                                                    <div class="row mb-3">
                                                                        <div class="col-4 label">KPI Divisi</div>
                                                                        <div class="col-8 value">${data.divisi_kpi}</div>
                                                                    </div>
                                                                    <div class="row mb-3">
                                                                        <div class="col-4 label">KPI Jabatan</div>
                                                                        <div class="col-8 value">${data.jabatan_kpi}</div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-4 label">Pembuat</div>
                                                                        <div class="col-8 value">${data.pembuat}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="card border-0 shadow-sm rounded-4 participant-card h-100">
                                                                <div class="card-body px-4 py-3">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        <div class="me-2 rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                                                            <i class="fa-solid fa-users text-success"></i>
                                                                        </div>
                                                                        <h6 class="mb-0 fw-semibold text-secondary">KARYAWAN</h6>
                                                                    </div>
                                                                    <div class="participant-list" style="overflow-y: scroll; max-height: 140px;">
                                                                        ${karyawanHtml}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <div class="col-lg-4">
                                            <div class="card shadow h-100">
                                                <div class="card border-0 shadow-sm rounded-4 h-100">
                                                    <div class="card-body d-flex flex-column">
                                                        ${contentPieChart}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    ${ContentTrafikSales}

                                    ${contentStatisticChart}
                                </div>
                            </div>

                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        `);

                        const NAMA_BULAN = [
                            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];

                        function getNamaBulan(tahunBulan) {
                            const parts = tahunBulan.split('-');
                            if (parts.length < 2) return tahunBulan;
                            const bulanIndex = parseInt(parts[1], 10) - 1;
                            return NAMA_BULAN[bulanIndex] || tahunBulan;
                        }

                        let statisticChart = null;
                        const statisticCtx = document.getElementById('StatisticChart');

                        function renderStatistic(labels, values, label) {
                            if (statisticChart) statisticChart.destroy();

                            const maxValue = values.length > 0 ? Math.max(...values) : 0;
                            const suggestedMax = maxValue + 3;

                            statisticChart = new Chart(statisticCtx, {
                                type: 'line',
                                data: {
                                    labels,
                                    datasets: [{
                                        label,
                                        data: values,
                                        borderColor: '#4e73df',
                                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                                        tension: 0.4,
                                        fill: true
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            suggestedMax: suggestedMax,
                                            ticks: {
                                                count: 6,
                                                precision: 0,
                                                callback: function(value) {
                                                    return Math.round(value);
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }

                        const monthLabels = Object.keys(monthlyData).map(key => getNamaBulan(key));
                        const monthValues = Object.values(monthlyData);
                        renderStatistic(monthLabels, monthValues, 'Rata-rata');

                        $('#filterType').off('change').on('change', function() {
                            if (this.value === 'month') {
                                $('#filterMonth').removeClass('d-none').empty().append(
                                    '<option value="">Pilih Bulan</option>');
                                Object.keys(dailyData).forEach(monthKey => {
                                    $('#filterMonth').append(
                                        `<option value="${monthKey}">${getNamaBulan(monthKey)}</option>`
                                    );
                                });
                                if (statisticChart) statisticChart.destroy();
                            } else {
                                $('#filterMonth').addClass('d-none');
                                renderStatistic(monthLabels, monthValues, 'Rata-rata');
                            }
                        });

                        $('#filterMonth').off('change').on('change', function() {
                            const selectedMonth = this.value;
                            if (!selectedMonth || !dailyData[selectedMonth]) return;

                            const dayLabels = Object.keys(dailyData[selectedMonth]).map(d => d
                                .substring(8));
                            const dayValues = Object.values(dailyData[selectedMonth]);
                            renderStatistic(dayLabels, dayValues,
                                `Tanggal ${getNamaBulan(selectedMonth)}`);
                        });
                    }

                    const modalEl = document.getElementById('detailTargetModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                },
                error: function() {
                    Swal.fire('Error', 'Gagal memuat detail target', 'error');
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
            const $assistantRoute = $(`#assistant_route`);
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
                        html +=
                            `<option value="Q${q} - ${tahunIni}" ${disabled} ${selected}>Kuartal ${q} - (${tahunIni})</option>`;
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
                        html +=
                            `<option value="${i + 1} - ${tahunIni}" ${disabled} ${selected}>${namaBulan[i]} ${tahunIni}</option>`;
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
                        const label =
                            `Minggu ${idx + 1} (${formatDateNumeric(startDate)} - ${formatDateNumeric(endDate)} - ${currentYear})`;
                        html +=
                            `<option value="${formatDateNumeric(startDate)} - ${formatDateNumeric(endDate)} - ${currentYear}" ${disabled}>${label}</option>`;
                    });
                    html += `<option disabled>──────── Tahun Depan ────────</option>`;
                    const weeksNextYearJanuary = getWeeksInMonth(currentYear + 1, 0);
                    weeksNextYearJanuary.forEach((week, idx) => {
                        const [startMs, endMs] = week;
                        const startDate = new Date(startMs);
                        const endDate = new Date(endMs);
                        const label =
                            `Minggu ${idx + 1} (${formatDateNumeric(startDate)} - ${formatDateNumeric(endDate)} - ${currentYear + 1})`;
                        html +=
                            `<option value="${formatDateNumeric(startDate)} - ${formatDateNumeric(endDate)} - ${currentYear + 1}">${label}</option>`;
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

        $('#assistant_route').prop('disabled', true).html(
            '<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>');
        $('#tipeTarget').prop('disabled', true).html(
            '<option selected disabled>-- Pilih Assistant Route Terlebih Dahulu --</option>');

        $('#jabatan').on('change', function() {
            const selectedJabatan = $(this).val();
            const assistantRouteSelect = $('#assistant_route');
            const tipeTargetSelect = $('#tipeTarget');

            assistantRouteSelect.empty();
            tipeTargetSelect.empty();

            if (!selectedJabatan || selectedJabatan.length === 0) {
                assistantRouteSelect.prop('disabled', true).html(
                    '<option selected disabled>-- Pilih Jabatan Terlebih Dahulu --</option>');
                tipeTargetSelect.prop('disabled', true).html(
                    '<option selected disabled>-- Pilih Assistant Route Terlebih Dahulu --</option>');
                return;
            }

            const hasGM = selectedJabatan.includes('GM');
            const hasKoorITSM = selectedJabatan.includes('Koordinator ITSM');
            const hasProgrammer = selectedJabatan.includes('Programmer');
            const hasDigital = selectedJabatan.includes('Tim Digital');
            const hasTS = selectedJabatan.includes('Technical Support');
            const hasCC = selectedJabatan.includes('Customer Care');
            const hasFinance = selectedJabatan.includes('Finance & Accounting');
            const hasHRD = selectedJabatan.includes('HRD');
            const hasDriver = selectedJabatan.includes('Driver');
            const hasOB = selectedJabatan.includes('Office Boy');
            const hasInstruktur = selectedJabatan.includes('Instruktur');
            const hasManagerEdu = selectedJabatan.includes('Education Manager');
            const hasSales = selectedJabatan.includes('Sales');
            const hasSPVSales = selectedJabatan.includes('SPV Sales');
            const hasAdmSales = selectedJabatan.includes('Adm Sales');
            const hasAdmHolding = selectedJabatan.includes('Admin Holding');

            let options = '<option selected disabled>-- Pilih Assistant Route --</option>';

            const jabatanCount = selectedJabatan.length;

            if (jabatanCount >= 2) {
                // Programmer + Tim Digital + TS
                if (hasProgrammer && hasDigital && hasTS) {
                    options += `
                        <option value="kepuasan client ITSM">Kepuasan Client ITSM</option>
                        <option value="inovation adaption rate">Inovation Adaption Rate</option>
                        <option value="persentase gap kompetensi tim terhadap standar skill">Persentase Gap Kompetensi Tim terhadap Standar Skill</option>
                    `;
                } else if (hasSales && hasSPVSales && hasAdmSales) {
                    options += `
                        <option value="peningkatan kemampuan kompetensi sales">Peningkatan Kemampuan Kompetensi Sales</option>
                    `;
                } else {
                    options +=
                        '<option disabled>-- Kombinasi jabatan ini belum memiliki Assistant Route --</option>';
                }
            } else {
                //Office
                //GM
                if (hasGM) {
                    options += `
                            <option value="Pemasukan Kotor">Pemasukan Kotor (PK * Pengeluaran)</option>
                            <option value="pemasukan bersih">Laba Bersih</option>
                            <option value="Kepuasan Pelanggan">Feedback Peserta</option>
                            <option value="rasio biaya operasional terhadap revenue">Rasio Biaya Operasional Terhadap Revenue</option>
                            <option value="performa KPI departemen">Performa KPI Departemen</option>
                        `;
                }
                //CS
                else if (hasCC) {
                    options += `
                            <option value="peserta puas dengan pelayanan dan fasilitas training">Peserta Puas Dengan Pelayanan & Fasilitas Training</option>
                            <option value="dorong inovasi pelayanan">Dorong Inovasi Pelayanan</option>
                            <option value="penanganan komplain perseta">Penanganan Komplain Peserta</option>
                            <option value="report persiapan kelas">Report Persiapan Kelas</option>
                        `;
                }
                //Finanace
                else if (hasFinance) {
                    options += `
                        <option value="outstanding">Banyak Tagihan Client Yang Belum Lunas</option>
                        <option value="inisiatif efisiensi keuangan">Inisiatif Efisiensi keuangan</option>
                        <option value="mengurangi manual work dan error">Mengurangi Manual Work Dan Error</option>
                        <option value="laporan analisis keuangan">Laporan Analisis Keuangan</option>
                        <option value="pencairan biaya operasional">Pencairan Biaya Operasional Kantor</option>
                        <option value="penyelesaian tagihan perusahaan">Penyelesaian Tagihan Perusahaan</option>
                        <option value="akurasi pencatatan masuk">Akurasi Pencatatan Masuk</option>
                    `;
                }

                //HRD
                else if (hasHRD) {
                    options += `
                            <option value="pelaksanaan kegiatan karyawan">Pelaksanaan Kegiatan Karyawan</option>
                            <option value="pengeluaran biaya karyawan">Pengeluaran Biaya Karyawan</option>
                            <option value="administrasi karyawan">Administrasi Karyawan</option>
                        `;
                }

                //Driver
                else if (hasDriver) {
                    options += `
                        <option value="perbaikan kendaraan">Perbaikan kendaraan</option>
                        <option value="report kondisi kendaraan">Report Kondisi Kendaraan</option>
                        <option value="kontrol pengeluaran transportasi">Kontrol Pengeluaran Transportasi</option>
                        <option value="feedback kenyamanan berkendaran">Feedback Kenyamanan Berkendara</option>
                    `;
                }

                //OB
                else if (hasOB) {
                    options += `
                        <option value="feedback kebersihan dan kenyamanan">Feedback Kebersihan Dan Kenyamanan Peserta</option>
                        <option value="penyelesaian tugas harian">Peyelesaian Tugas Harian</option>
                    `;
                }

                //ITSM
                //Koordinator ITSM
                else if (hasKoorITSM) {
                    options += `
                            <option value="meningkatkan kepuasan dan loyalitas peserta/client">Meningkatkan Kepuasan Dan Loyalitas Peserta/Client</option>
                            <option value="availability sistem internal kritis">Availability Sistem Internal Kritis (Uptime%)</option>
                        `;
                }
                //Programmer
                else if (hasProgrammer) {
                    options += `
                            <option value="ketepatan waktu penyelesaian fitur">Ketepatan Waktu Penyelesaian Fitur/Modul</option>
                            <option value="mengukur kualitas aplikasi agar minim bug">Mengukur Kualitas Aplikasi Agar Minim Bug</option>
                        `;
                }
                //Digital
                else if (hasDigital) {
                    options += `
                            <option value="konsistensi campaign digital">Konsistensi Campaign Digital</option>
                            <option value="efektifitas diital marketing">Database Client Baru</option>
                        `;
                }
                //TS
                else if (hasTS) {
                    options += `
                            <option value="keberhasilan support memenuhi sla">Tingkat Keberhasilan Support Memenuhi SLA</option>
                            <option value="kualitas layanan exam">Kualitas Layanan Exam</option>
                        `;
                }

                //Education
                //Instruktur
                else if (hasInstruktur) {
                    options += `
                            <option value="presentase kinerja instruktur">Presentase Kinerja Instruktur</option>
                            <option value="kepuasan peserta pelatihan">Kepuasan Peserta Pelatihan</option>
                            <option value="upseling lanjutan materi">Upseling Lanjutan Materi</option>
                            <option value="sertifikasi kompetensi internal">Peningkatan Kompetensi Instruktur - Sertifikasi Internal</option>
                            <option value="pelatihan kompetensi eksternal">Peningkatan Kompetensi Instruktur - Pelatihan Eksternal</option>
                        `;
                }

                //Education Manager
                else if (hasManagerEdu) {
                    options += `
                            <option value="pengembangan kurikulum pelatihan">Pengembangan Kurikulum & Modul Pelatihan</option>
                            <option value="peningkatan knowledge sharing">Peningkatan Knowledge Sharing</option>
                            <option value="peningkatan kontribusi pelatihan">Peningkatan Kontribusi Pelatihan</option>
                            <option value="evaluasi kinerja instruktur">Evaluasi Kinerja Instruktur</option>
                        `;
                }

                //Sales & Materketing
                //Sales
                else if (hasSales) {
                    options += `
                        <option value="target penjualan tahunan">Target Penjualan Tahunan</option>
                        <option value="biaya akuisisi perclient">Biaya Akuisisi Perclient</option>
                    `;
                }

                //SPV Sales
                else if (hasSPVSales) {
                    options += `
                        <option value="meningkatkan revenue perusahaan">Meningkatkan Revenue Perusahaan</option>
                        <option value="customer acquisition cost">Customer Acquisition Cost</option>
                        <option value="evaluasi kinerja sales">Evaluasi Kinerja Sales</option>
                    `;
                }

                // ADM Sales
                else if (hasAdmSales) {
                    options += `
                        <option value="laporan mom">Laporan MOM</option>
                        <option value="akurasi kelengkapan data penjualan">Akurasi Kelengkapan Data Penjualan</option>
                        <option value="todo administrasi">Todo Administrasi</option>
                    `;
                }

                // ADM Holding
                else if (hasAdmHolding) {
                    options += `
                        <option value="ketepatan waktu po">Ketepatan Waktu PO</option>
                        <option value="kualitas dokumentasi support dan proctor">Kualitas Dokumentasi Support Dan Proctor</option>
                    `;
                }

                //end/selesai
                else {
                    options +=
                        '<option disabled>-- Tidak ada Assistant Route tersedia untuk jabatan ini --</option>';
                }

            }

            assistantRouteSelect.html(options);

            const hasValidOptions = options.includes('<option value=');
            assistantRouteSelect.prop('disabled', !hasValidOptions);

            tipeTargetSelect.prop('disabled', true).html(
                '<option selected disabled>-- Pilih Assistant Route Terlebih Dahulu --</option>');
        });

        $(document).on('change', '#assistant_route', function() {
            const selectedRoute = $(this).val();
            const $tipeTarget = $('#tipeTarget');
            const $nilaiTarget = $('#nilaiTarget');

            if (!selectedRoute) {
                $tipeTarget
                    .removeClass('select-readonly')
                    .html(`
                        <option selected disabled>-- Silahkan Memilih Assistant Route --</option>
                    `)
                    .prop('disabled', false);
                $nilaiTarget.prop('disabled', false).val('').trigger('input');
                return;
            }


            const routeLower = selectedRoute.toLowerCase();

            const persenRoutes = [
                // ================= GM =================
                'pemasukan bersih',
                'kepuasan pelanggan',
                'rasio biaya operasional terhadap revenue',
                'performa kpi departemen',

                // ================= CS =================
                'peserta puas dengan pelayanan dan fasilitas training',
                'penanganan komplain peserta',
                'report persiapan kelas',

                // ================= Finance =================
                'outstanding',
                'penyelesaian tagihan perusahaan',
                'akurasi pencatatan masuk',
                'pencairan biaya operasional',

                // ================= HRD =================
                'pelaksanaan kegiatan karyawan',
                'pengeluaran biaya karyawan',
                'administrasi karyawan',

                // ================= Driver =================
                'perbaikan kendaraan',
                'report kondisi kendaraan',
                'kontrol pengeluaran transportasi',
                'feedback kenyamanan berkendaran',

                // ================= OB =================
                'feedback kebersihan dan kenyamanan',
                'penyelesaian tugas harian',

                // ================= ITSM =================
                'kepuasan client itsm',
                'meningkatkan kepuasan dan loyalitas peserta/client',
                'availability sistem internal kritis',

                // ================= Programmer =================
                'ketepatan waktu penyelesaian fitur',
                'mengukur kualitas aplikasi agar minim bug',

                // ================= Digital =================
                'konsistensi campaign digital',

                // ================= TS =================
                'keberhasilan support memenuhi sla',
                'kualitas layanan exam',

                // ================= Instruktur =================
                'presentase kinerja instruktur',
                'kepuasan peserta pelatihan',
                'upseling lanjutan materi',

                // ================= Manager Edu =================
                'peningkatan kontribusi pelatihan',
                'evaluasi kinerja instruktur',

                // ================= Sales =================
                'target penjualan tahunan',
                'biaya akuisisi perclient',

                // ================= SPV Sales =================
                'customer acquisition cost',
                'evaluasi kinerja sales',

                // ================= Adm Sales =================
                'laporan mom',
                'akurasi kelengkapan data penjualan',
                'todo administrasi',

                // ================= Adm Holding =================
                'ketepatan waktu po',
                'kualitas dokumentasi support dan proctor',

                // ================= Kombinasi =================
                'persentase gap kompetensi tim terhadap standar skill',
                'peningkatan kemampuan kompetensi sales'
            ].map(route => route.toLowerCase());

            const rupiahRoutes = [
                // GM
                'pemasukan kotor',

                // SPV Sales
                'meningkatkan revenue perusahaan',
            ].map(r => r.toLowerCase());

            const angkaRoutes = [
                // CS
                'dorong inovasi pelayanan',

                // Finance
                'inisiatif efisiensi keuangan',
                'mengurangi manual work dan error',
                'laporan analisis keuangan',

                // Digital
                'efektifitas digital marketing',

                // Instruktur
                'sertifikasi kompetensi internal',
                'pelatihan kompetensi eksternal',

                // Manager Edu
                'pengembangan kurikulum pelatihan',
                'peningkatan knowledge sharing',

                // Kombinasi IT
                'inovation adaption rate'
            ].map(route => route.toLowerCase());

            if (persenRoutes.includes(routeLower)) {
                $tipeTarget
                    .html(`
                        <option disabled>Angka (Unit, Jumlah, dll)</option>
                        <option disabled>Rupiah (Nilai Keuangan)</option>
                        <option value="persen" selected>Persen (%)</option>
                    `)
                    .addClass('select-readonly')
                    .prop('disabled', false); 
            } else if (rupiahRoutes.includes(routeLower)) {
                $tipeTarget
                    .html(`
                        <option disabled>Angka (Unit, Jumlah, dll)</option>
                        <option value="rupiah" selected>Rupiah (Nilai Keuangan)</option>
                        <option disabled>Persen (%)</option>
                    `)
                    .addClass('select-readonly')
                    .prop('disabled', false);
            } else if (angkaRoutes.includes(routeLower)) {
                $tipeTarget
                    .html(`
                        <option value="angka" selected>Angka (Unit, Jumlah, dll)</option>
                        <option disabled>Rupiah (Nilai Keuangan)</option>
                        <option disabled>Persen (%)</option>
                    `)
                    .addClass('select-readonly')
                    .prop('disabled', false);
            } else {
                $tipeTarget
                    .html(`
                        <option selected disabled>-- Route Tidak Dikenali --</option>
                    `)
                    .removeClass('select-readonly') 
                    .prop('disabled', false);
            }

            const autoFillValues = {
                'sertifikasi kompetensi internal': $('#jabatan').val()?.length || 1,
                'pelatihan kompetensi eksternal': $('#jabatan').val()?.length || 1,
                'efektifitas digital marketing': 4,
                'laporan analisis keuangan': 12,
                'dorong inovasi pelayanan': 3,
                'mengurangi manual work dan error': 2,
                'inisiatif efisiensi keuangan': 2,
                'pengembangan kurikulum pelatihan': 12,
            };

            if (autoFillValues.hasOwnProperty(routeLower)) {
                $nilaiTarget.val(autoFillValues[routeLower]).trigger('input');
            }

            if ($nilaiTarget.val()) {
                $nilaiTarget.trigger('input');
            }
        });

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
                pdfInfo.innerHTML = `
                    <p class="mb-2"><strong>PDF:</strong> ${file.name}</p>
                    <embed src="${URL.createObjectURL(file)}" type="application/pdf" width="100%" height="300px">
                `;
                preview.appendChild(pdfInfo);
            } else {
                preview.innerHTML = `<p><strong>File:</strong> ${file.name}</p>`;
            }
        });
    </script>
@endsection
