@extends('layouts_office.app')

@section('office_contents')
    @include('layouts_office.skeleton_table')

    <div id="real-dashboard" class="container-fluid py-4 d-none">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                    <i class="bx bx-task text-primary me-2" style="font-size: 1.5rem;"></i>
                    Tagihan Perusahaan
                </h4>
                <small class="text-muted">{{ now()->translatedFormat('l, d F Y') }}</small>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <select id="filter-tahun" class="form-select form-select-sm" style="width:auto;">
                    @foreach ($listTahun as $t)
                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}
                        </option>
                    @endforeach
                </select>

                <select id="filter-bulan" class="form-select form-select-sm" style="width:auto;">
                    <option value="">Semua Bulan</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>

                @can('Store Tagihan Perusahaan')
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#modalImport">
                        <i class="bx bx-upload me-1"></i>Import Data
                    </button>
                @endcan

                @can('Delete Tagihan Perusahaan')
                    <button type="button" class="btn btn-sm btn-danger" id="btn-hapus-semua">
                        <i class="bx bx-trash me-1"></i>Hapus Semua
                    </button>
                @endcan

                <div class="form-check form-switch ms-2">
                    <input class="form-check-input" type="checkbox" id="mode-edit">
                    <label class="form-check-label fw-medium" for="mode-edit">
                        <span id="mode-label">Mode Baca</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden glass-force">
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive" style="overflow-x: auto;">
                    <table id="table-tagihan" class="table table-hover align-middle mb-0" style="min-width: 1400px;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:42px;"></th>
                                <th style="min-width:130px;">Due Date</th>
                                <th style="min-width:180px;">Kegiatan</th>
                                <th style="min-width:90px;">Tipe</th>
                                <th style="min-width:130px;">Nominal</th>
                                <th style="min-width:220px;">Tracking</th>
                                <th style="min-width:160px;">Keterangan</th>
                                <th style="min-width:120px;">Tgl Selesai</th>
                                <th style="min-width:110px;">Status</th>
                                <th style="width:90px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trackingTagihanPerusahaans as $tagihan)
                                <tr data-id="{{ $tagihan->id }}" class="{{ $tagihan->is_urgent ? 'urgent-row' : '' }}"
                                    data-urgent="{{ $tagihan->is_urgent ? 1 : 0 }}">

                                    <td class="text-center pe-0">
                                        @if ($tagihan->status === 'selesai')
                                            <input class="custom-check" type="checkbox" checked disabled>
                                        @elseif($tagihan->status === 'telat')
                                            <input class="custom-fail" type="checkbox" checked disabled>
                                        @else
                                            <input class="check-blue" type="checkbox" disabled>
                                        @endif
                                    </td>

                                    <td class="editable due-date-cell" data-field="due_date"
                                        data-mulai="{{ $tagihan->tanggal_perkiraan_mulai }}"
                                        data-selesai="{{ $tagihan->tanggal_perkiraan_selesai }}">
                                        @if (!$tagihan->tanggal_perkiraan_selesai || $tagihan->tanggal_perkiraan_mulai == $tagihan->tanggal_perkiraan_selesai)
                                            {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_mulai)->format('d M Y') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_mulai)->format('d M') }} –
                                            {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_selesai)->format('d M Y') }}
                                        @endif
                                    </td>

                                    <td class="editable" data-field="kegiatan"
                                        data-value="{{ $tagihan->tagihanPerusahaan?->kegiatan ?? $tagihan->kegiatan }}">
                                        {{ $tagihan->tagihanPerusahaan?->kegiatan ?? $tagihan->kegiatan }}
                                    </td>

                                    <td class="editable" data-field="tipe"
                                        data-value="{{ $tagihan->tagihanPerusahaan?->tipe ?? $tagihan->tipe }}">
                                        <span class="badge bg-secondary-subtle text-secondary text-capitalize">
                                            {{ $tagihan->tagihanPerusahaan?->tipe ?? $tagihan->tipe }}
                                        </span>
                                    </td>

                                    <td class="editable" data-field="nominal" data-value="{{ $tagihan->nominal }}">
                                        @php
                                            $nominal = $tagihan->nominal;
                                            $isNumeric = is_numeric($nominal);
                                        @endphp

                                        @if ($isNumeric && $nominal !== null && $nominal !== '')
                                            Rp {{ number_format($nominal, 0, ',', '.') }}
                                        @else
                                            {{ $nominal ?: '—' }}
                                        @endif
                                    </td>

                                    <td class="editable" data-field="tracking" data-value="{{ $tagihan->tracking }}">
                                        <div class="text-truncate" style="max-width:210px;"
                                            title="{{ $tagihan->tracking }}">
                                            {{ $tagihan->tracking ?? '—' }}
                                        </div>
                                    </td>

                                    <td class="editable" data-field="keterangan" data-value="{{ $tagihan->keterangan }}">
                                        <div class="text-truncate" style="max-width:150px;"
                                            title="{{ $tagihan->keterangan }}">
                                            {{ $tagihan->keterangan ?? '—' }}
                                        </div>
                                    </td>

                                    <td class="editable" data-field="tanggal_selesai"
                                        data-value="{{ $tagihan->tanggal_selesai }}">
                                        {{ $tagihan->tanggal_selesai ? \Carbon\Carbon::parse($tagihan->tanggal_selesai)->format('d M Y') : '—' }}
                                    </td>

                                    <td class="editable text-center status-cell" data-field="status"
                                        data-value="{{ $tagihan->status }}">
                                        @php
                                            $cfg = [
                                                'pending' => ['c' => 'warning', 'i' => 'bx-time-five'],
                                                'proses' => ['c' => 'primary', 'i' => 'bx-loader-circle'],
                                                'selesai' => ['c' => 'success', 'i' => 'bx-check-circle'],
                                                'telat' => ['c' => 'danger', 'i' => 'bx-info-circle'],
                                            ][$tagihan->status] ?? ['c' => 'secondary', 'i' => 'bx-info-circle'];
                                        @endphp
                                        <span
                                            class="badge bg-{{ $cfg['c'] }}-subtle text-{{ $cfg['c'] }} px-2 py-1 text-capitalize">
                                            <i class="bx {{ $cfg['i'] }} me-1"></i>{{ $tagihan->status }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle py-1 px-2"
                                                type="button" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-horizontal-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li>
                                                    <button class="dropdown-item text-success btn-ajukan-tagihan"
                                                        data-id="{{ $tagihan->id }}">
                                                        <i class="bx bx-send me-2"></i>Ajukan Tagihan
                                                    </button>
                                                    <form id="form-ajukan-{{ $tagihan->id }}" style="display:none;">
                                                        @csrf
                                                        @php $k = auth()->user()->karyawan; @endphp
                                                        <input type="hidden" name="id_tagihan"
                                                            value="{{ $tagihan->id }}">
                                                        <input type="hidden" name="id_karyawan"
                                                            value="{{ $k->id ?? '' }}">
                                                        <input type="hidden" name="nama_karyawan"
                                                            value="{{ $k->nama_lengkap ?? '' }}">
                                                        <input type="hidden" name="divisi"
                                                            value="{{ $k->divisi ?? '' }}">
                                                        <input type="hidden" name="tipe" value="Tagihan Perusahaan">
                                                        <input type="hidden" name="barang[nama_barang][]"
                                                            value="{{ $tagihan->kegiatan ?? $tagihan->tagihanPerusahaan?->kegiatan }}">
                                                        <input type="hidden" name="barang[qty][]" value="1">
                                                        <input type="hidden" name="barang[harga_barang][]"
                                                            value="{{ $tagihan->nominal }}">
                                                        <input type="hidden" name="barang[keterangan][]"
                                                            value="{{ $tagihan->keterangan }}">
                                                    </form>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger btn-hapus-tagihan"
                                                        data-id="{{ $tagihan->id }}">
                                                        <i class="bx bx-trash me-2"></i>Hapus
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="form-import" class="modal-content" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bx bx-upload me-2"></i>Import Data Tagihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        Format kolom Excel: <b>No., Tagihan, Due Date, Realisasi, Keterangan, status</b>.
                        Baris header akan dideteksi otomatis.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">File Excel</label>
                        <input type="file" name="file" id="import-file" class="form-control"
                            accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-medium">Tipe Tagihan</label>
                        <select name="tipe" class="form-select">
                            <option value="bulanan">Bulanan</option>
                            <option value="tahunan">Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btn-submit-import">
                        <i class="bx bx-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalHapusSemua1" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title text-warning"><i class="bx bx-error me-2"></i>Konfirmasi Langkah 1 dari 2</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Anda akan menghapus <b>seluruh data tagihan perusahaan</b>.</p>
                    <p class="mb-0 text-muted small">Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin ingin melanjutkan?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="btn-hapus-lanjut">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHapusSemua2" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger-subtle">
                    <h5 class="modal-title text-danger"><i class="bx bx-trash me-2"></i>Konfirmasi Langkah 2 dari 2</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Ketik <b class="text-danger">HAPUS SEMUA</b> pada kolom di bawah untuk mengonfirmasi penghapusan permanen.</p>
                    <input type="text" id="input-konfirmasi-hapus" class="form-control" placeholder="HAPUS SEMUA"
                        autocomplete="off">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btn-hapus-final" disabled>
                        <i class="bx bx-trash me-1"></i>Hapus Semua Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-check,
        .custom-fail,
        .check-blue {
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 4px;
            position: relative;
        }

        .custom-check {
            border: 2px solid #71DD37;
        }

        .custom-check:checked {
            background: #71DD37;
        }

        .custom-check:checked::after {
            content: '✓';
            color: #fff;
            font-weight: bold;
            position: absolute;
            left: 1px;
            top: -3px;
            font-size: 12px;
        }

        .custom-fail {
            border: 2px solid #FF3E1D;
        }

        .custom-fail:checked {
            background: #FF3E1D;
        }

        .custom-fail:checked::after {
            content: '✖';
            color: #fff;
            font-weight: bold;
            position: absolute;
            left: 1px;
            top: -3px;
            font-size: 12px;
        }

        .check-blue {
            border: 2px solid #5B73E8;
        }

        tr.urgent-row {
            background-color: #fff8e6 !important;
            border-left: 4px solid #ffc107;
            cursor: grab;
        }

        tr.urgent-row:active {
            cursor: grabbing;
        }

        .sortable-ghost {
            opacity: 0.45;
            background: #ffe8a1 !important;
        }

        .sortable-chosen {
            background: #ffd666 !important;
        }

        body.edit-mode .editable {
            cursor: pointer;
            transition: background .15s;
        }

        body.edit-mode .editable:hover {
            background-color: #e8f0fe !important;
            outline: 1px dashed #5B73E8;
            outline-offset: -1px;
        }

        .inline-input {
            width: 100%;
            border: 1px solid #5B73E8;
            border-radius: 4px;
            padding: 3px 6px;
            font-size: 0.82rem;
            background: #fff;
        }

        @media (max-width: 1400px) {
            #table-tagihan {
                min-width: 1400px !important;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const csrf = $('meta[name="csrf-token"]').attr('content');

            const table = $('#table-tagihan').DataTable({
                order: [],
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                columnDefs: [
                    { orderable: false, targets: [0, 9] },
                    { searchable: false, targets: [0, 9] }
                ],
                stateSave: false
            });

            function applyFilter() {
                const tahun = $('#filter-tahun').val();
                const bulan = $('#filter-bulan').val();
                const url = new URL(window.location.href);
                url.searchParams.set('tahun', tahun);
                if (bulan) url.searchParams.set('bulan', bulan);
                else url.searchParams.delete('bulan');
                window.location = url.toString();
            }
            $('#filter-tahun, #filter-bulan').on('change', applyFilter);

            let isEditMode = false;
            $('#mode-edit').on('change', function () {
                isEditMode = this.checked;
                $('body').toggleClass('edit-mode', isEditMode);
                $('#mode-label').text(isEditMode ? 'Mode Edit' : 'Mode Baca');

                if (!isEditMode) {
                    $('.inline-input').each(function () {
                        const $td = $(this).closest('td');
                        $td.html($(this).data('original-html'));
                    });
                }
            });

            $(document).on('click', 'body.edit-mode td.editable', function (e) {
                e.stopPropagation();
                if ($(this).find('.inline-input, .flatpickr-input, .due-edit-wrapper').length) return;

                const $td = $(this);
                const field = $td.data('field');
                const originalHtml = $td.html();

                if (field === 'due_date') {
                    const mulai = $td.data('mulai') || '';
                    const selesai = $td.data('selesai') || '';

                    $td.html(`<input type="text" class="form-control form-control-sm flatpickr-due" placeholder="Pilih tanggal..." style="min-width:180px; cursor:pointer;">`);
                    const $input = $td.find('.flatpickr-due');

                    const fp = flatpickr($input[0], {
                        mode: "range",
                        dateFormat: "Y-m-d",
                        locale: "id",
                        defaultDate: selesai && selesai !== mulai ? [mulai, selesai] : mulai,
                        showMonths: 1,
                        static: false,
                        onClose: function (selectedDates) {
                            if (selectedDates.length === 0) {
                                $td.html(originalHtml);
                                return;
                            }

                            let newMulai, newSelesai;
                            if (selectedDates.length === 1) {
                                newMulai = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                                newSelesai = newMulai;
                            } else {
                                newMulai = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                                newSelesai = flatpickr.formatDate(selectedDates[1], "Y-m-d");
                            }

                            const id = $td.closest('tr').data('id');

                            $.ajax({
                                url: `/office/update-inline/${id}`,
                                method: 'POST',
                                data: {
                                    _token: csrf,
                                    field: 'due_date',
                                    tanggal_perkiraan_mulai: newMulai,
                                    tanggal_perkiraan_selesai: newSelesai
                                },
                                success: function () {
                                    $td.data('mulai', newMulai);
                                    $td.data('selesai', newSelesai);

                                    if (newMulai === newSelesai) {
                                        const d = new Date(newMulai);
                                        $td.html(d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }));
                                    } else {
                                        const d1 = new Date(newMulai);
                                        const d2 = new Date(newSelesai);
                                        $td.html(
                                            d1.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) +
                                            ' – ' +
                                            d2.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                                        );
                                    }
                                },
                                error: function () {
                                    alert('Gagal menyimpan tanggal');
                                    $td.html(originalHtml);
                                }
                            });
                        }
                    });

                    fp.open();
                    return;
                }

                if (field === 'tanggal_selesai') {
                    const value = $td.data('value') || '';
                    $td.html(`<input type="text" class="form-control form-control-sm flatpickr-single" style="min-width:140px;">`);
                    const $input = $td.find('.flatpickr-single');

                    const fp = flatpickr($input[0], {
                        dateFormat: "Y-m-d",
                        locale: "id",
                        defaultDate: value || null,
                        onClose: function (selectedDates) {
                            if (selectedDates.length === 0) {
                                $td.html(originalHtml);
                                return;
                            }

                            const newVal = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                            const id = $td.closest('tr').data('id');

                            $.ajax({
                                url: `/office/update-inline/${id}`,
                                method: 'POST',
                                data: {
                                    _token: csrf,
                                    field: 'tanggal_selesai',
                                    value: newVal
                                },
                                success: function () {
                                    $td.data('value', newVal);
                                    const d = new Date(newVal);
                                    $td.html(d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }));
                                },
                                error: function () {
                                    alert('Gagal menyimpan');
                                    $td.html(originalHtml);
                                }
                            });
                        }
                    });

                    fp.open();
                    return;
                }

                let value = $td.data('value') ?? '';
                let html = '';

                if (field === 'status') {
                    html = `<select class="inline-input form-select form-select-sm">
                                <option value="pending" ${value === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="proses"  ${value === 'proses' ? 'selected' : ''}>Proses</option>
                                <option value="selesai" ${value === 'selesai' ? 'selected' : ''}>Selesai</option>
                                <option value="telat"   ${value === 'telat' ? 'selected' : ''}>Telat</option>
                            </select>`;
                } else if (field === 'tipe') {
                    html = `<select class="inline-input form-select form-select-sm">
                                <option value="bulanan" ${value === 'bulanan' ? 'selected' : ''}>bulanan</option>
                                <option value="tahunan" ${value === 'tahunan' ? 'selected' : ''}>tahunan</option>
                            </select>`;
                } else if (field === 'nominal') {
                    html = `<input type="text" class="inline-input" value="${value || ''}" placeholder="Angka atau teks (fluktuatif, one year, dll)">`;
                } else if (field === 'tracking') {
                    const options = [
                        'Diajukan dan Sedang Ditinjau oleh Finance',
                        'Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager',
                        'Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi',
                        'Finance Menunggu Approve Direksi',
                        'Membuat Permintaan Ke Direktur Utama',
                        'Pengajuan sedang dalam proses Pencairan',
                        'Pencairan Sudah Selesai',
                        'Invoice belum keluar',
                        'Menunggu invoice dari PIC',
                        'Selesai'
                    ];

                    html = `<select class="inline-input form-select form-select-sm tracking-select">` +
                        options.map(o => `<option value="${o}" ${value === o ? 'selected' : ''}>${o}</option>`).join('') +
                        `</select>`;
                } else {
                    html = `<input type="text" class="inline-input" value="${value || ''}">`;
                }

                $td.html(html);
                const $input = $td.find('.inline-input');
                $input.data('original-html', originalHtml).focus();

                if (field === 'tracking') {
                    $input.on('change', function () {
                        let selected = $(this).val();

                        if (selected === 'Menunggu invoice dari PIC') {
                            const nama = prompt('Masukkan nama PIC:');

                            if (nama && nama.trim() !== '') {
                                selected = 'Menunggu invoice dari PIC ' + nama.trim();
                            } else {
                                $td.html(originalHtml);
                                return;
                            }
                        }

                        const id = $td.closest('tr').data('id');

                        $.ajax({
                            url: `/office/update-inline/${id}`,
                            method: 'POST',
                            data: {
                                _token: csrf,
                                field: 'tracking',
                                value: selected
                            },
                            success: function () {
                                $td.data('value', selected);
                                $td.html(`<div class="text-truncate" style="max-width:210px;" title="${selected}">${selected}</div>`);
                            },
                            error: function () {
                                alert('Gagal menyimpan');
                                $td.html(originalHtml);
                            }
                        });
                    });

                    return;
                }

                function save() {
                    const newVal = $input.val();
                    const id = $td.closest('tr').data('id');

                    $.ajax({
                        url: `/office/update-inline/${id}`,
                        method: 'POST',
                        data: {
                            _token: csrf,
                            field: field,
                            value: newVal
                        },
                        success: function (res) {
                            $td.data('value', newVal);

                            if (field === 'nominal') {
                                if (newVal !== '' && !isNaN(newVal) && !isNaN(parseFloat(newVal))) {
                                    $td.html('Rp ' + Number(newVal).toLocaleString('id-ID'));
                                } else {
                                    $td.html(newVal || '—');
                                }
                            } else if (field === 'status') {
                                const map = {
                                    pending: ['warning', 'bx-time-five'],
                                    proses: ['primary', 'bx-loader-circle'],
                                    selesai: ['success', 'bx-check-circle'],
                                    telat: ['danger', 'bx-info-circle']
                                };
                                const [c, i] = map[newVal] || ['secondary', 'bx-info-circle'];
                                $td.html(`<span class="badge bg-${c}-subtle text-${c} px-2 py-1 text-capitalize">
                                            <i class="bx ${i} me-1"></i>${newVal}</span>`);

                                const $checkTd = $td.closest('tr').find('td:first');
                                if (newVal === 'selesai') {
                                    $checkTd.html('<input class="custom-check" type="checkbox" checked disabled>');
                                } else if (newVal === 'telat') {
                                    $checkTd.html('<input class="custom-fail" type="checkbox" checked disabled>');
                                } else {
                                    $checkTd.html('<input class="check-blue" type="checkbox" disabled>');
                                }
                            } else if (field === 'tipe') {
                                $td.html(`<span class="badge bg-secondary-subtle text-secondary text-capitalize">${newVal}</span>`);
                            } else {
                                $td.html(`<div class="text-truncate" style="max-width:210px;" title="${newVal || ''}">${newVal || '—'}</div>`);
                            }
                        },
                        error: function () {
                            alert('Gagal menyimpan perubahan');
                            $td.html(originalHtml);
                        }
                    });
                }

                $input.on('blur', save);
                $input.on('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $(this).blur();
                    }
                    if (e.key === 'Escape') {
                        $td.html(originalHtml);
                    }
                });

                if ($input.is('select')) {
                    $input.on('change', save);
                }
            });

            function loadSortableJS() {
                return new Promise((resolve, reject) => {
                    if (typeof Sortable !== 'undefined') {
                        resolve();
                        return;
                    }
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
                    script.onload = () => resolve();
                    script.onerror = () => reject('Gagal load SortableJS');
                    document.head.appendChild(script);
                });
            }

            let urgentSortable = null;

            async function initUrgentSortable() {
                try {
                    await loadSortableJS();
                } catch (e) {
                    console.error(e);
                    return;
                }

                const tbody = document.querySelector('#table-tagihan tbody');
                if (!tbody) return;

                if (urgentSortable) {
                    urgentSortable.destroy();
                    urgentSortable = null;
                }

                if (document.querySelectorAll('#table-tagihan tbody tr.urgent-row').length === 0) return;

                urgentSortable = Sortable.create(tbody, {
                    animation: 150,
                    draggable: '.urgent-row',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    filter: 'input, select, button, a, .dropdown, .flatpickr-calendar',
                    onEnd: function () {
                        const orders = {};
                        document.querySelectorAll('#table-tagihan tbody tr.urgent-row').forEach((tr, index) => {
                            orders[tr.dataset.id] = index + 1;
                        });

                        $.ajax({
                            url: "{{ route('reorderUrgentTagihan') }}",
                            method: 'POST',
                            data: {
                                _token: csrf,
                                orders: orders
                            },
                            error: function () {
                                alert('Gagal menyimpan urutan');
                            }
                        });
                    }
                });
            }

            let lastClickTime = 0;
            let lastClickedId = null;
            let isProcessing = false;

            $(document).off('click.urgentToggle dblclick.urgentToggle');

            $(document).on('click.urgentToggle', '#table-tagihan tbody tr', function (e) {
                if ($(e.target).closest('input, select, button, a, .dropdown, .flatpickr-calendar, .inline-input').length) return;
                if (isEditMode && $(e.target).closest('.editable').length) return;

                const $tr = $(this);
                const id = $tr.data('id');
                const now = Date.now();

                if (lastClickedId === id && (now - lastClickTime) < 320) {
                    lastClickTime = 0;
                    lastClickedId = null;

                    if (isProcessing) return;
                    isProcessing = true;

                    $tr.css('opacity', '0.6');

                    $.ajax({
                        url: `/office/toggle-urgent/${id}`,
                        method: 'POST',
                        data: {
                            _token: csrf
                        },
                        success: function (res) {
                            if (res.is_urgent) {
                                $tr.addClass('urgent-row').attr('data-urgent', 1);
                                $tr.prependTo($('#table-tagihan tbody'));
                            } else {
                                $tr.removeClass('urgent-row').attr('data-urgent', 0);
                                const $lastUrgent = $('#table-tagihan tbody tr.urgent-row').last();
                                if ($lastUrgent.length) {
                                    $tr.insertAfter($lastUrgent);
                                } else {
                                    $tr.appendTo($('#table-tagihan tbody'));
                                }
                            }

                            table.row($tr).invalidate().draw(false);
                            setTimeout(initUrgentSortable, 80);
                        },
                        error: function () {
                            alert('Gagal mengubah status urgent');
                        },
                        complete: function () {
                            $tr.css('opacity', '1');
                            setTimeout(() => {
                                isProcessing = false;
                            }, 700);
                        }
                    });
                } else {
                    lastClickTime = now;
                    lastClickedId = id;
                }
            });

            initUrgentSortable();

            $(document).on('click', '.btn-ajukan-tagihan', function () {
                const id = $(this).data('id');
                const $form = $('#form-ajukan-' + id);

                $('#loadingModal').modal('show');
                $.ajax({
                    url: "{{ route('pengajuanbarang.store') }}",
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    success() {
                        $('#loadingModal').modal('hide');
                        alert('Berhasil membuat pengajuan barang');
                    },
                    error(xhr) {
                        $('#loadingModal').modal('hide');
                        alert('Gagal mengajukan');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.btn-hapus-tagihan', function () {
                if (!confirm('Yakin ingin menghapus tagihan ini?')) return;

                const id = $(this).data('id');
                const $tr = $(this).closest('tr');

                $.ajax({
                    url: `/office/delete-tagihan/${id}`,
                    method: 'POST',
                    data: {
                        _token: csrf
                    },
                    success() {
                        table.row($tr).remove().draw(false);
                    },
                    error() {
                        alert('Gagal menghapus');
                    }
                });
            });

            $('#form-import').on('submit', function (e) {
                e.preventDefault();

                const fileInput = document.getElementById('import-file');
                if (!fileInput.files.length) {
                    alert('Pilih file terlebih dahulu');
                    return;
                }

                const formData = new FormData(this);
                const $btn = $('#btn-submit-import');
                const btnHtml = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

                $.ajax({
                    url: "{{ route('importTagihanPerusahaan') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    success(res) {
                        alert(res.message);
                        window.location.reload();
                    },
                    error(xhr) {
                        let msg = 'Gagal import data';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            if (xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                            }
                        }
                        alert(msg);
                        $btn.prop('disabled', false).html(btnHtml);
                    }
                });
            });

            const modalHapus1 = new bootstrap.Modal(document.getElementById('modalHapusSemua1'));
            const modalHapus2 = new bootstrap.Modal(document.getElementById('modalHapusSemua2'));

            $('#btn-hapus-semua').on('click', function () {
                modalHapus1.show();
            });

            $('#btn-hapus-lanjut').on('click', function () {
                modalHapus1.hide();
                $('#input-konfirmasi-hapus').val('');
                $('#btn-hapus-final').prop('disabled', true);
                setTimeout(() => {
                    modalHapus2.show();
                    $('#input-konfirmasi-hapus').trigger('focus');
                }, 350);
            });

            $('#input-konfirmasi-hapus').on('input', function () {
                $('#btn-hapus-final').prop('disabled', $(this).val().trim() !== 'HAPUS SEMUA');
            });

            $('#btn-hapus-final').on('click', function () {
                const $btn = $(this);
                const btnHtml = $btn.html();

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...');

                $.ajax({
                    url: "{{ route('hapusSemuaTagihanPerusahaan') }}",
                    method: 'POST',
                    data: {
                        _token: csrf,
                        konfirmasi: $('#input-konfirmasi-hapus').val().trim()
                    },
                    success(res) {
                        alert(res.message);
                        window.location.reload();
                    },
                    error(xhr) {
                        let msg = 'Gagal menghapus data';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                            if (xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                            }
                        }
                        alert(msg);
                        $btn.prop('disabled', false).html(btnHtml);
                    }
                });
            });
        });
    </script>
@endsection