@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Checklist RKM</h4>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light" id="periodLabel">Semua Periode</span>
                    <span class="badge bg-light" id="recordCount">0 Records</span>
                </div>
            </div>

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body bg-light-subtle p-4">
                    <form id="filterForm" class="mb-4 m-4">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">Cari Data</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <span class="iconify" data-icon="tabler:search"></span>
                                    </span>
                                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                                        class="form-control" placeholder="Materi, Perusahaan, Sales...">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Bulan</label>
                                <select name="bulan" id="bulanSelect" class="form-select">
                                    @foreach (range(1, 12) as $bulan)
                                        <option value="{{ $bulan }}"
                                            {{ (request('bulan') ?? date('n')) == $bulan ? 'selected' : '' }}>
                                            {{ Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Tahun</label>
                                <select name="tahun" id="tahunSelect" class="form-select">
                                    @foreach (range(date('Y') - 3, date('Y') + 1) as $tahun)
                                        <option value="{{ $tahun }}"
                                            {{ (request('tahun') ?? date('Y')) == $tahun ? 'selected' : '' }}>
                                            {{ $tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Minggu</label>
                                <select name="minggu" id="mingguSelect" class="form-select">
                                    @for ($i = 1; $i <= 4; $i++)
                                        <option value="{{ $i }}" {{ request('minggu') == $i ? 'selected' : '' }}>
                                            Minggu {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <span class="iconify me-1" data-icon="tabler:filter"></span>Filter
                                    </button>
                                    <button type="button" id="resetFilter" class="btn btn-outline-secondary">
                                        <span class="iconify" data-icon="tabler:x"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive m-4">
                        <table class="table table-bordered table-hover align-middle" id="rkmTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="20%">RKM / Materi</th>
                                    <th width="25%">Perusahaan</th>
                                    <th width="15%">Sales</th>
                                    <th width="10%" class="text-center">Reg. Form</th>
                                    <th width="10%" class="text-center">Kontrak</th>
                                    <th width="10%" class="text-center">PA</th>
                                    <th width="10%" class="text-center">PO</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-2 mb-0 text-muted">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="paginationContainer" class="mt-3 d-flex justify-content-center"></div>
                    <div id="emptyState" class="text-center py-5 d-none">
                        <span class="iconify fs-1 text-muted" data-icon="tabler:inbox"></span>
                        <p class="mt-3 text-muted">Tidak ada data yang ditemukan</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-gradient-primary text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-semibold">
                        <span class="iconify me-2" data-icon="tabler:eye"></span>Detail RKM
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card bg-light-subtle border-0">
                                <div class="card-body py-3">
                                    <small class="text-muted d-block mb-1">
                                        <span class="iconify me-1" data-icon="tabler:calendar-event"></span>Tanggal
                                        Training
                                    </small>
                                    <span id="detailTanggal" class="fw-semibold text-dark"></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card bg-light-subtle border-0">
                                <div class="card-body py-3">
                                    <small class="text-muted d-block mb-1">
                                        <span class="iconify me-1" data-icon="tabler:book"></span>Materi
                                    </small>
                                    <span id="detailMateri" class="fw-semibold text-dark"></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card bg-light-subtle border-0">
                                <div class="card-body py-3">
                                    <small class="text-muted d-block mb-1">
                                        <span class="iconify me-1" data-icon="tabler:user"></span>Instruktur
                                    </small>
                                    <span id="detailInstruktur" class="fw-semibold text-dark"></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light-subtle border-0 h-100">
                                <div class="card-body py-3">
                                    <small class="text-muted d-block mb-1">
                                        <span class="iconify me-1" data-icon="tabler:building"></span>Perusahaan
                                    </small>
                                    <div id="detailPerusahaan" class="fw-semibold text-dark"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light-subtle border-0 h-100">
                                <div class="card-body py-3">
                                    <small class="text-muted d-block mb-1">
                                        <span class="iconify me-1" data-icon="tabler:user-check"></span>Sales
                                    </small>
                                    <div id="detailSales" class="fw-semibold text-dark"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4">
                    <button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <span class="iconify me-1" data-icon="tabler:x-circle"></span>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090">
        <div id="liveToast" class="toast align-items-center text-bg-primary border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableBody = document.getElementById('tableBody');
            const filterForm = document.getElementById('filterForm');
            const paginationContainer = document.getElementById('paginationContainer');
            const emptyState = document.getElementById('emptyState');
            const recordCount = document.getElementById('recordCount');
            const periodLabel = document.getElementById('periodLabel');
            const rkmTable = document.getElementById('rkmTable');

            let currentPage = 1;
            let filters = {
                bulan: document.getElementById('bulanSelect')?.value || '',
                tahun: document.getElementById('tahunSelect')?.value || ''
            };
            let searchTimeout;

            loadData();

            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filters = {
                    search: document.getElementById('searchInput')?.value || '',
                    bulan: document.getElementById('bulanSelect')?.value || '',
                    tahun: document.getElementById('tahunSelect')?.value || '',
                    minggu: document.getElementById('mingguSelect')?.value || '',
                };
                currentPage = 1;
                updatePeriodLabel();
                loadData();
            });

            document.getElementById('resetFilter')?.addEventListener('click', function() {
                document.getElementById('searchInput').value = '';
                document.getElementById('bulanSelect').value = '';
                document.getElementById('tahunSelect').value = '';
                document.getElementById('mingguSelect').value = '';
                filters = {};
                currentPage = 1;
                updatePeriodLabel();
                loadData();
            });

            document.getElementById('searchInput')?.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filters.search = e.target.value;
                    currentPage = 1;
                    loadData();
                }, 300);
            });

            tableBody?.addEventListener('change', function(e) {
                if (e.target.classList.contains('checklist-checkbox')) {
                    const checkbox = e.target;
                    const rkmIds = checkbox.dataset.rkm.split(',');
                    const field = checkbox.dataset.field;
                    const checked = checkbox.checked;
                    const originalState = checked;

                    checkbox.disabled = true;
                    checkbox.classList.add('opacity-50');

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    const promises = rkmIds.map(id =>
                        fetch(`/crm/checklist-rkm/${id}/checklist`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                field,
                                checked
                            })
                        }).then(res => res.json())
                    );

                    Promise.all(promises)
                        .then(results => {
                            const allSuccess = results.every(r => r.success);
                            if (allSuccess) {
                                showToast('Checklist berhasil diupdate', 'success');
                            } else {
                                checkbox.checked = !originalState;
                                showToast(results.find(r => !r.success)?.message ||
                                    'Gagal update checklist', 'error');
                            }
                        })
                        .catch(err => {
                            checkbox.checked = !originalState;
                            showToast('Terjadi kesalahan koneksi', 'error');
                            console.error(err);
                        })
                        .finally(() => {
                            checkbox.disabled = false;
                            checkbox.classList.remove('opacity-50');
                        });
                }
            });

            tableBody?.addEventListener('click', function(e) {
                const detailBtn = e.target.closest('.show-detail');
                if (detailBtn && !e.target.closest('.form-check-input')) {
                    e.preventDefault();
                    document.getElementById('detailTanggal').textContent = detailBtn.dataset
                    .tanggaltraining;
                    document.getElementById('detailMateri').textContent = detailBtn.dataset.materi;
                    document.getElementById('detailInstruktur').textContent = detailBtn.dataset.instruktur;

                    const perusahaan = detailBtn.dataset.perusahaan || '-';
                    const sales = detailBtn.dataset.sales || '-';

                    document.getElementById('detailPerusahaan').textContent = perusahaan;
                    document.getElementById('detailSales').textContent = sales;

                    new bootstrap.Modal(document.getElementById('modalDetail')).show();
                }
            });

            document.addEventListener('click', function(e) {
                const pageLink = e.target.closest('.page-link');
                if (pageLink) {
                    e.preventDefault();
                    const page = pageLink.dataset.page;
                    if (page && page !== currentPage) {
                        currentPage = page;
                        loadData();
                        window.scrollTo({
                            top: rkmTable.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                }
            });

            function updatePeriodLabel() {
                const {
                    bulan,
                    tahun,
                    minggu
                } = filters;
                let label = 'Semua Periode';

                if (tahun && bulan && minggu) {
                    const monthName = new Date(tahun, bulan - 1, 1).toLocaleString('id-ID', {
                        month: 'long'
                    });
                    const startDay = (minggu - 1) * 7 + 1;
                    const endDay = Math.min(
                        minggu * 7,
                        new Date(tahun, bulan, 0).getDate()
                    );

                    let firstBusinessDay = null;
                    let lastBusinessDay = null;

                    for (let day = startDay; day <= endDay; day++) {
                        const date = new Date(tahun, bulan - 1, day);
                        const dayOfWeek = date.getDay();

                        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                            if (!firstBusinessDay) {
                                firstBusinessDay = day;
                            }
                            lastBusinessDay = day;
                        }
                    }

                    if (firstBusinessDay && lastBusinessDay) {
                        label = `${firstBusinessDay}-${lastBusinessDay} ${monthName} ${tahun}`;
                    }
                } else if (tahun && bulan) {
                    const monthName = new Date(tahun, bulan - 1, 1).toLocaleString('id-ID', {
                        month: 'long'
                    });
                    label = `${monthName} ${tahun}`;
                } else if (tahun) {
                    label = `Tahun ${tahun}`;
                } else if (bulan) {
                    const currentYear = new Date().getFullYear();
                    const monthName = new Date(currentYear, bulan - 1, 1).toLocaleString('id-ID', {
                        month: 'long'
                    });
                    label = `${monthName} ${currentYear}`;
                }

                periodLabel.textContent = label;
            }

            function loadData() {
                showLoading();
                updatePeriodLabel();

                const params = new URLSearchParams({
                    ...filters,
                    page: currentPage,
                    per_page: 20
                });

                fetch(`/crm/checklist-rkm/data?${params}`)
                    .then(res => res.json())
                    .then(response => {
                        if (response.data && response.data.length > 0) {
                            renderTable(response.data);
                            renderPagination(response.pagination);
                            recordCount.textContent = `${response.pagination.total} Records`;
                            emptyState.classList.add('d-none');
                            rkmTable.classList.remove('d-none');
                        } else {
                            showEmptyState();
                        }
                    })
                    .catch(err => {
                        showErrorState();
                        console.error(err);
                    });
            }

            function showLoading() {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                            <p class="mt-2 mb-0 text-muted small">Memuat data...</p>
                        </td>
                    </tr>
                `;
                emptyState.classList.add('d-none');
                rkmTable.classList.remove('d-none');
                paginationContainer.innerHTML = '';
            }

            function showEmptyState() {
                tableBody.innerHTML = '';
                rkmTable.classList.add('d-none');
                emptyState.classList.remove('d-none');
                paginationContainer.innerHTML = '';
                recordCount.textContent = '0 Records';
            }

            function showErrorState() {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-5 text-danger">
                            <span class="iconify fs-4" data-icon="tabler:alert-triangle"></span>
                            <p class="mt-2 mb-0">Gagal memuat data. Silakan coba lagi.</p>
                            <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadData()">
                                <span class="iconify me-1" data-icon="tabler:refresh"></span>Retry
                            </button>
                        </td>
                    </tr>
                `;
                emptyState.classList.add('d-none');
                rkmTable.classList.remove('d-none');
                paginationContainer.innerHTML = '';
            }

            function renderTable(data) {
                if (!data.length) {
                    showEmptyState();
                    return;
                }

                let globalNo = (currentPage - 1) * 20;

                tableBody.innerHTML = data.map(item => {

                    const checkboxes = Object.entries(item.checkboxes)
                        .map(([field, config]) => `
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input type="checkbox"
                                        class="form-check-input checklist-checkbox"
                                        data-rkm="${item.id}"
                                        data-field="${field}"
                                        ${config.checked ? 'checked' : ''}>
                                </div>
                            </td>
                        `)
                        .join('');

                    const showDetailBtn = `
                        <button class="btn btn-link text-decoration-none show-detail p-0"
                            data-materi="${escapeHtml(item.materi)}"
                            data-perusahaan="${escapeHtml(item.perusahaan)}"
                            data-sales="${escapeHtml(item.sales)}"
                            data-instruktur="${escapeHtml(item.instruktur)}"
                            data-tanggaltraining="${escapeHtml(item.tanggal_training)}">
                            <small class="text-muted">
                                ${escapeHtml(item.materi)}
                            </small>
                        </button>
                    `;

                    globalNo++;

                    return `
                        <tr class="table-row-hover">
                            <td class="text-center fw-medium text-muted">
                                ${globalNo}
                            </td>

                            <td>
                                ${showDetailBtn}
                            </td>

                            <td>
                                <small>${escapeHtml(item.perusahaan)}</small>
                            </td>

                            <td>
                                <small>${escapeHtml(item.sales)}</small>
                            </td>

                            ${checkboxes}
                        </tr>
                    `;
                }).join('');
            }

            function renderPagination(pagination) {
                if (!pagination || pagination.last_page <= 1) {
                    paginationContainer.innerHTML = '';
                    return;
                }

                let html = '<nav><ul class="pagination pagination-sm mb-0">';

                html += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}" aria-label="Previous">
                        <span class="iconify" data-icon="tabler:chevron-left"></span>
                    </a></li>`;

                const maxVisible = 5;
                let startPage = Math.max(1, pagination.current_page - Math.floor(maxVisible / 2));
                let endPage = Math.min(pagination.last_page, startPage + maxVisible - 1);

                if (endPage - startPage + 1 < maxVisible) {
                    startPage = Math.max(1, endPage - maxVisible + 1);
                }

                if (startPage > 1) {
                    html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                    if (startPage > 2) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }

                if (endPage < pagination.last_page) {
                    if (endPage < pagination.last_page - 1) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                    html +=
                        `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a></li>`;
                }

                html += `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}" aria-label="Next">
                        <span class="iconify" data-icon="tabler:chevron-right"></span>
                    </a></li>`;

                html += '</ul></nav>';
                paginationContainer.innerHTML = html;
            }

            function showToast(message, type = 'success') {
                const toast = document.getElementById('liveToast');
                const toastMessage = document.getElementById('toastMessage');

                toast.className =
                    `toast align-items-center border-0 ${type === 'success' ? 'text-bg-success' : 'text-bg-danger'}`;
                toastMessage.textContent = message;

                const bsToast = new bootstrap.Toast(toast, {
                    delay: 3000,
                    autohide: true
                });
                bsToast.show();
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
    </script>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .table-row-hover:hover {
            background-color: #f8f9fa !important;
            transition: background-color 0.15s ease-in-out;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        .page-link {
            color: #667eea;
            transition: all 0.2s ease;
        }

        .page-link:hover {
            color: #764ba2;
            background-color: #f8f9fa;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-outline-secondary:hover {
            transform: translateY(-1px);
        }

        .card {
            transition: box-shadow 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .modal-content {
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .spinner-border {
            animation-duration: 0.75s;
        }

        .toast {
            min-width: 300px;
        }

        .form-select,
        .form-control {
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        .input-group-text {
            background-color: #fff;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group .form-control:focus {
            box-shadow: none;
        }

        @media (max-width: 768px) {
            .card-header {
                padding: 1rem !important;
            }

            .card-body {
                padding: 1rem !important;
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .pagination {
                flex-wrap: wrap;
                gap: 2px;
            }
        }
    </style>
@endsection
