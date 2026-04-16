@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-bold text-dark">Generate Sertifikat</h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        <!-- Alert Success -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-check-circle me-2" style="font-size: 1.5rem;"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filters (Hanya Search + Tanggal) -->
        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="Cari materi, kode, atau perusahaan...">
                </div>
            </div>
            <div class="col-md-5">
                <div class="row g-2">
                    <div class="col-6">
                        <input type="date" id="startDate" class="form-control" title="Tanggal Mulai">
                    </div>
                    <div class="col-6">
                        <input type="date" id="endDate" class="form-control" title="Tanggal Selesai">
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Table -->
        <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden glass-force">
            <div class="card-header border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bx bx-list-ul text-primary me-2" style="font-size: 1.5rem;"></i>
                        Daftar RKM untuk Generate Sertifikat
                    </h5>
                    <span class="badge bg-primary-subtle text-primary" id="totalData">0 Data</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th class="border-0 ps-4" style="min-width: 60px;">No</th>
                                <th class="border-0" style="min-width: 250px;">Materi</th>
                                <th class="border-0" style="min-width: 200px;">Perusahaan</th>
                                <th class="border-0" style="min-width: 200px;">Tanggal Pelatihan</th>
                                <th class="border-0 text-center pe-4" style="min-width: 180px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Diisi oleh JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div class="card-footer border-top py-3" id="paginationContainer">
                <!-- Diisi oleh JavaScript -->
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-5 d-none">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat data...</p>
        </div>
    </div>

    <style>
        /* Style kamu tetap sama (tidak diubah) */
        .hover-bg:hover {
            background-color: rgba(91, 115, 232, 0.05) !important;
            transition: all 0.3s ease;
        }

        .hover-scale {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-scale:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .avatar-sm {
            width: 38px;
            height: 38px;
            font-size: 1rem;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 0.75rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .sticky-top {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
    </style>

    <script>
        let currentPage = 1;
        let debounceTimer = null;

        function loadRkm(page = 1) {
            currentPage = page;
            document.getElementById('loading').classList.remove('d-none');
            document.getElementById('tableBody').innerHTML = '';

            const params = new URLSearchParams({
                page: page,
                per_page: 15,
                search: document.getElementById('searchInput').value.trim(),
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value
            });

            fetch(`{{ route('office.certificate.getData') }}?${params}`)
                .then(res => res.json())
                .then(result => {
                    document.getElementById('loading').classList.add('d-none');
                    renderTable(result.data, result.pagination);
                    renderPagination(result.pagination);
                    document.getElementById('totalData').textContent = `${result.pagination.total} Data`;
                })
                .catch(() => {
                    document.getElementById('loading').classList.add('d-none');
                    document.getElementById('tableBody').innerHTML = `
                        <tr><td colspan="5" class="text-center py-5 text-danger">
                            <i class="bx bx-error-circle" style="font-size:3rem"></i><br>
                            Gagal memuat data
                        </td></tr>`;
                });
        }

        function renderTable(data, pagination) {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bx bx-info-circle text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                            <p class="text-muted mt-3 mb-0">Tidak ada data yang sesuai filter</p>
                        </td>
                    </tr>`;
                return;
            }

            data.forEach((item, idx) => {
                const no = pagination.from + idx;
                const row = `
                    <tr class="border-bottom hover-bg">
                        <td class="ps-4"><span class="fw-medium text-muted">${no}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-opacity-15 rounded-circle me-3">
                                    <i class="bx bx-book-open text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">${item.materi_nama}</div>
                                    <small class="text-muted">${item.materi_kode}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width:200px" data-bs-toggle="tooltip" title="${item.perusahaan_nama}">
                                <i class="bx bx-buildings text-muted me-1"></i>${item.perusahaan_nama}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column small text-center">
                                <span class="text-muted mb-1">
                                    ${item.tanggal_awal} <i class="bx bx-right-arrow-alt me-1"></i> ${item.tanggal_akhir} 
                                </span>
                            </div>
                        </td>
                        <td class="text-center pe-4">
                            <a href="{{ route('office.certificate.detail', '') }}/${item.id}" 
                               class="btn btn-sm btn-info shadow-sm hover-scale">
                                <i class="bx bx-detail me-1"></i>Lihat Detail
                            </a>
                        </td>
                    </tr>`;
                tbody.innerHTML += row;
            });
        }

        function renderPagination(p) {
            let html = `<div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">Menampilkan ${p.from} - ${p.to} dari ${p.total} data</div>
                <nav><ul class="pagination pagination-sm mb-0">`;

            html += `<li class="page-item ${p.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadRkm(${p.current_page-1});return false">‹ Prev</a>
            </li>`;

            for (let i = 1; i <= p.last_page; i++) {
                if (i === p.current_page) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else if (i === 1 || i === p.last_page || (i >= p.current_page - 2 && i <= p.current_page + 2)) {
                    html +=
                        `<li class="page-item"><a class="page-link" href="#" onclick="loadRkm(${i});return false">${i}</a></li>`;
                }
            }

            html += `<li class="page-item ${p.current_page === p.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadRkm(${p.current_page+1});return false">Next ›</a>
            </li>`;

            html += `</ul></nav></div>`;
            document.getElementById('paginationContainer').innerHTML = html;
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', () => {
            const debounced = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => loadRkm(1), 350);
            };

            document.getElementById('searchInput').addEventListener('input', debounced);
            document.getElementById('startDate').addEventListener('change', () => loadRkm(1));
            document.getElementById('endDate').addEventListener('change', () => loadRkm(1));

            // Load pertama kali
            loadRkm(1);

            new bootstrap.Tooltip(document.body, {
                selector: '[data-bs-toggle="tooltip"]'
            });
        });
    </script>
@endsection
