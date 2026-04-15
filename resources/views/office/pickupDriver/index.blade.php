@extends($extends)

@section($section)
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        .table> :not(caption)>*>* {
            border-bottom-width: 0 !important;
        }

        .item-row td {
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
        }

        .item-start td {
            box-shadow: inset 0 1px 0 #e5e7eb;
        }

        .item-end td {
            box-shadow: inset 0 -1.5px 0 #e5e7eb;
        }

        .table th,
        .table td {
            white-space: nowrap;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }

        @media (max-width: 768px) {

            .table th,
            .table td {
                white-space: normal;
                word-wrap: break-word;
                font-size: 0.75rem;
                padding: 0.4rem 0.5rem;
            }
        }

        .table-responsive {
            overflow-x: auto;
            position: relative;
        }

        @media (max-width: 768px) {
            .table-responsive {
                padding: 0;
            }
        }

        #content-person {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        #content-person::-webkit-scrollbar {
            height: 6px;
        }

        #content-person::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        #content-person::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>

    {{-- Modal Detail --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-semibold"><i class="fa fa-truck me-2"></i> Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-3" id="detailContent"></div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Kepulangan --}}
    <div class="modal fade" id="kepulanganModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="kepulanganForm">
                @csrf
                <div class="modal-content shadow-lg border-0 rounded-4">
                    <div class="modal-header bg-light border-0">
                        <h5 class="modal-title fw-semibold">Input Waktu Kepulangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Waktu Kepulangan</label>
                            <input type="time" name="waktu_kepulangan" class="form-control form-control-lg" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">KM Awal</label>
                            <input type="number" name="KM_awal" id="KM_awal" class="form-control form-control-lg"
                                placeholder="Contoh: 12000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">KM Akhir</label>
                            <input type="number" name="KM_akhir" id="KM_akhir" class="form-control form-control-lg"
                                placeholder="Contoh: 12050" required>
                            <small id="km_warning" class="text-danger d-none">KM Akhir tidak boleh lebih kecil dari KM
                                Awal</small>
                        </div>
                        <div class="mb-3" id="budget_calculation" style="display:none;">
                            <label class="form-label fw-semibold">Estimasi Pemakaian Budget</label>
                            <div class="alert alert-info mb-0">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="d-block text-muted">Kapasitas Tangki</small>
                                        <strong id="tank_capacity_display">0</strong> Liter
                                    </div>
                                    <div class="col-6">
                                        <small class="d-block text-muted">Harga Bensin</small>
                                        <strong>Rp <span id="fuel_price_display">0</span></strong>/Liter
                                    </div>
                                </div>
                                <hr class="my-2">
                                Jarak: <span id="jarak_tempuh">0</span> KM × Rp <span id="rate_per_km">0</span>/KM =
                                <strong>Rp <span id="total_pemakaian">0</span></strong>
                            </div>
                        </div>
                        <input type="hidden" name="pickup_driver_id" id="kepulangan_id">
                        <input type="hidden" name="total_pemakaian" id="total_pemakaian_hidden">
                        <input type="hidden" name="vehicle_type" id="vehicle_type_hidden">
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Koordinasi --}}
    <div class="modal fade" id="editKoordinasiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="editKoordinasiForm">
                @csrf
                <input type="hidden" name="pickup_driver_id" id="edit_id">
                <div class="modal-content shadow-lg border-0 rounded-4">
                    <div class="modal-header bg-light border-0">
                        <h5 class="modal-title fw-semibold"><i class="fa fa-edit me-2"></i> Edit Koordinasi Driver</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <div class="row mb-4">
                            <label class="col-md-3 col-form-label fw-semibold">Driver</label>
                            <div class="col-md-9">
                                @if (Auth()->user()->jabatan === 'Driver')
                                    <select name="id_driver" class="form-select" disabled required>
                                        <option value="">Pilih Driver</option>
                                        @foreach ($dataDriver as $driver)
                                            <option value="{{ $driver->id }}">{{ $driver->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select name="id_driver" class="form-select" required>
                                        <option value="">Pilih Driver</option>
                                        @foreach ($dataDriver as $driver)
                                            <option value="{{ $driver->id }}">{{ $driver->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-4" id="budgetSection" style="display:none;">
                            <label class="col-md-3 col-form-label fw-semibold">Budget</label>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="budget" id="edit_budget_input" class="form-control"
                                        placeholder="0" autocomplete="off">
                                    <input type="hidden" name="budget_value" id="edit_budget_hidden">
                                </div>
                                <small class="text-muted">Opsional. Kosongkan jika tidak ada budget khusus.</small>
                            </div>
                        </div>

                        <div class="row mb-4" id="vehicleSection" style="display:none;">
                            <label class="col-md-3 col-form-label fw-semibold">Kendaraan</label>
                            <div class="col-md-9">
                                <select name="budget" class="form-select">
                                    <option value="">Belum Dipilih</option>
                                    @foreach ($kendaraan as $data)
                                        <option value="{{ $data }}">{{ $data }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4 mb-3">Detail Rute</h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0" id="editDetailTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Lokasi</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="editDetailBody"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-primary mt-2" id="addEditDetailRow">
                            <i class="fa fa-plus me-1"></i> Tambah Rute
                        </button>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save me-1"></i> Simpan
                            Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($section === 'crm_contents')
        <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">
            @else
                <div class="container-fluid py-4">
    @endif
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h4 class="fw-bold text-dark">Koordinasi Driver</h4>
        <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
    </div>

    <div class="card shadow-lg border-0 rounded-4 mb-3 glass-force">
        <div class="card-body px-3 py-3">
            <div id="content-person" class="d-flex gap-3 overflow-x-auto p-3">
            </div>
        </div>
    </div>

    <div class="card shadow-lg border-0 rounded-4 glass-force">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @if ($section === 'crm_contents')
                        <a class="btn btn-primary" href="{{ route('CRM.create.koordinasi') }}">
                            <i class="fa fa-plus me-1"></i> Buat Koordinasi
                        </a>
                    @else
                        <a class="btn btn-primary" href="{{ route('office.pickupDriver.create') }}">
                            <i class="fa fa-plus me-1"></i> Buat Koordinasi
                        </a>
                    @endif


                    @if (Auth()->user()->jabatan === 'HRD' || Auth()->user()->jabatan === 'GM' || Auth()->user()->jabatan === 'Office Boy')
                        <div class="btn-group">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-file-export me-1"></i> Export Laporan
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#modalExport">
                                        <i class="fas fa-cog me-2"></i> Export dengan Filter
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('office.pickupDriver.export.excel') }}">
                                        <i class="fas fa-file-excel text-success me-2"></i> Excel (Semua Data)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('office.pickupDriver.export.pdf') }}">
                                        <i class="fas fa-file-pdf text-danger me-2"></i> PDF (Semua Data)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif

                </div>

                <div class="modal fade" id="modalExport" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form id="formExport" method="GET">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-filter me-2"></i>Filter Export Laporan
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Mulai</label>
                                            <input type="date" name="start_date" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Akhir</label>
                                            <input type="date" name="end_date" class="form-control">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Kendaraan</label>
                                            <select name="kendaraan" class="form-select">
                                                <option value="">Semua Kendaraan</option>
                                                <option value="H1">H1</option>
                                                <option value="Inova">Inova</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success"
                                        formaction="{{ route('office.pickupDriver.export.excel') }}">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                    <button type="submit" class="btn btn-danger"
                                        formaction="{{ route('office.pickupDriver.export.pdf') }}">
                                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <span id="dataCountBadge" class="badge bg-primary-subtle text-primary">Loading...</span>
            </div>
        </div>

        <div class="card-body m-4">
            <div class="row g-3 mb-3 align-items-center">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text border-end-0">
                            <i class="fa fa-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0"
                            placeholder="Cari driver, lokasi, atau pembuat..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch"
                            title="Hapus pencarian">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 col-lg-8">
                    <div class="d-flex justify-content-md-end align-items-center gap-2">
                        <small class="text-muted me-2" id="paginationInfo">Menampilkan 0 data</small>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" id="prevPage" disabled>
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="nextPage" disabled>
                                <i class="fa fa-chevron-right"></i>
                            </button>
                        </div>
                        <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;">
                            <option value="10">10/halaman</option>
                            <option value="25">25/halaman</option>
                            <option value="50">50/halaman</option>
                            <option value="100">100/halaman</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="pickupTable" class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Driver</th>
                            <th>Tipe</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Pembuat</th>
                            <th>Apply Driver</th>
                            <th>Status</th>
                            <th>KM Awal</th>
                            <th>KM Akhir</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody id="content_body"></tbody>
                </table>
            </div>
        </div>
    </div>
    @if ($section === 'crm_contents')
        </div>
        </div>
    @else
        </div>
    @endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>

    <script>
        const AuthId = "{{ Auth()->user()->id }}";
        const baseUrl = "{{ url('/') }}";

        let currentPage = 1;
        let perPage = 10;
        let searchQuery = '';
        let totalData = 0;
        let allData = [];

        const VEHICLE_CONFIG = {
            'Innova': {
                fuelPrice: 14500,
                tankCapacity: 55,
                kmPerLiter: 8
            },
            'H1': {
                fuelPrice: 12500,
                tankCapacity: 75,
                kmPerLiter: 5
            }
        };

        $(document).ready(function() {
            loadData();
            loadOnlineStatus();
            initSearchAndPagination();
        });

        const budgetInput = document.getElementById('edit_budget_input');
        const budgetHidden = document.getElementById('edit_budget_hidden');

        if (budgetInput) {
            budgetInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                budgetHidden.value = value;
                this.value = value ? 'Rp ' + parseInt(value).toLocaleString('id-ID') : '';
            });
        }

        $(document).on('change', 'select[name="id_driver"]', function() {
            const driverId = $(this).val();
            const statusApply = $('#edit_id').val() ?
                allData.find(d => d.id == $('#edit_id').val())?.status_apply : 0;

            if (driverId && (statusApply == 1 || statusApply == 2)) {
                $('#budgetSection').slideDown();
            } else {
                $('#budgetSection').slideUp();
                $('#edit_budget_input').val('');
                $('#edit_budget_hidden').val('');
            }
        });

        function initSearchAndPagination() {
            // Debounce search
            let searchTimeout;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchQuery = $(this).val().toLowerCase();
                    currentPage = 1;
                    renderTable();
                }, 300);
            });

            // Clear search
            $('#clearSearch').on('click', function() {
                $('#searchInput').val('');
                searchQuery = '';
                currentPage = 1;
                renderTable();
            });

            // Pagination buttons
            $('#prevPage').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });

            $('#nextPage').on('click', function() {
                const totalPages = Math.ceil(totalData / perPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });

            // Per page selector
            $('#perPageSelect').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                renderTable();
            });
        }

        function loadData() {
            $.get("{{ route('office.pickupDriver.get') }}", function(response) {
                // Simpan semua data ke cache
                allData = Array.isArray(response) ? response : [];
                totalData = allData.length;
                renderTable();
            }).fail(() => {
                Swal.fire('Error!', 'Gagal memuat data.', 'error');
                $('#content_body').html(
                    '<tr><td colspan="10" class="text-center text-muted">Gagal memuat data</td></tr>');
            });
        }

        function renderTable() {
            const tbody = $('#content_body');
            tbody.empty();

            // Filter data berdasarkan search
            let filteredData = allData.filter(item => {
                const driverName = (item.karyawan?.nama_lengkap || '').toLowerCase();
                const lokasi = (item.detail_pickup_driver?.[0]?.lokasi || '').toLowerCase();
                const pembuat = (item.pembuat?.nama_lengkap || '').toLowerCase();
                const kendaraan = (item.kendaraan || '').toLowerCase();

                return driverName.includes(searchQuery) ||
                    lokasi.includes(searchQuery) ||
                    pembuat.includes(searchQuery) ||
                    kendaraan.includes(searchQuery);
            });

            // Update total setelah filter
            const totalFiltered = filteredData.length;
            const totalPages = Math.ceil(totalFiltered / perPage);

            // Update info pagination
            const start = totalFiltered === 0 ? 0 : (currentPage - 1) * perPage + 1;
            const end = Math.min(currentPage * perPage, totalFiltered);
            $('#paginationInfo').text(`Menampilkan ${start}-${end} dari ${totalFiltered} data`);

            // Update tombol pagination
            $('#prevPage').prop('disabled', currentPage === 1);
            $('#nextPage').prop('disabled', currentPage >= totalPages || totalPages === 0);

            // Update badge count
            $('#dataCountBadge').text(totalFiltered + ' Data');

            // Slice data untuk halaman saat ini
            const paginatedData = filteredData.slice((currentPage - 1) * perPage, currentPage * perPage);

            if (paginatedData.length === 0) {
                tbody.append(`<tr><td colspan="8" class="text-center text-muted py-4">
                ${searchQuery ? 'Data tidak ditemukan' : 'Tidak ada data'}
            </td></tr>`);
                return;
            }

            let html = '';
            paginatedData.forEach(item => {
                const details = item.detail_pickup_driver || [];
                if (details.length === 0) {
                    html += buildRow(item, null, true, true);
                } else {
                    details.forEach((detail, index) => {
                        const isFirst = index === 0;
                        const isLast = index === details.length - 1;
                        html += buildRow(item, detail, isFirst, isLast);
                    });
                }
            });
            tbody.html(html);
        }

        function loadOnlineStatus() {
            $.get("{{ route('office.pickupDriver.getDriverStatus') }}", function(response) {
                const contentPerson = $('#content-person');
                contentPerson.empty();
                let data = response.data || [];
                data.forEach(function(item) {
                    const color = item.status === 'online' ? '#22c55e' : '#ef4444';
                    const foto_profil = item.foto ?
                        `{{ asset('storage') }}/${item.foto}` :
                        `{{ asset('assets/images/download.png') }}`;
                    contentPerson.append(`
                    <div class="flex-shrink-0 text-center" style="width:80px">
                        <div class="position-relative d-inline-block">
                            <img src="${foto_profil}" class="rounded-circle border" width="48" height="48">
                            <span class="position-absolute bottom-4 end-2 translate-middle rounded-circle border border-white"
                                style="width:12px;height:12px;background:${color};"></span>
                        </div>
                        <div class="mt-1 small fw-semibold text-truncate">${item.nama}</div>
                    </div>
                `);
                });
            });
        }

        function buildRow(item, detail, isFirst, isLast) {
            let rowClass = 'item-row';
            if (isFirst) rowClass += ' item-start';
            if (isLast) rowClass += ' item-end';

            const driverName = isFirst ? (item.karyawan?.nama_lengkap || '-') : '';
            const tanggal = isFirst ? getWaktuMulai(item.detail_pickup_driver) : '';
            const pembuat = isFirst ? (item.pembuat?.nama_lengkap || '-') : '';
            const applyBtn = isFirst ? getDriverButton(item, AuthId) : '';
            const status = isFirst ? getStatusBadge(item.status_driver) : '';
            const actions = isFirst ? getActionButtons(item) : '';

            return `
        <tr class="${rowClass}" data-item='${JSON.stringify(item)}'>
            <td>${driverName}</td>
            <td>${detail?.tipe || '-'}</td>
            <td class="text-truncate" style="max-width: 150px;" title="${detail?.lokasi || '-'}">${detail?.lokasi || '-'}</td>
            <td>${tanggal}</td>
            <td>${pembuat}</td>
            <td>${applyBtn}</td>
            <td>${status}</td>
            <td>${item.KM_awal ?? '-'}</td>
            <td>${item.KM_akhir ?? '-'}</td>
            <td class="text-center pe-4">${actions}</td>
        </tr>`;
        }

        function getWaktuMulai(details) {
            if (!details || details.length === 0) return '-';
            const sorted = [...details].sort((a, b) =>
                moment(`${a.tanggal_keberangkatan} ${a.waktu_keberangkatan}`) -
                moment(`${b.tanggal_keberangkatan} ${b.waktu_keberangkatan}`)
            );
            const first = sorted[0];
            return `${moment(first.tanggal_keberangkatan).format('DD-MM-YYYY')} ${moment(first.waktu_keberangkatan, 'HH:mm:ss').format('HH:mm')}`;
        }

        function getStatusBadge(status) {
            const map = {
                'Sedang Menjemput': '<span class="badge bg-success-subtle text-success">Sedang Menjemput</span>',
                'Sedang Mengantar': '<span class="badge bg-warning-subtle text-warning">Sedang Mengantar</span>',
                'Ready': '<span class="badge bg-warning-subtle text-warning">Ready</span>',
                'Selesai, Driver Ready': '<span class="badge bg-warning-subtle text-warning">Selesai, Driver Ready</span>'
            };
            return map[status] || '<span class="badge bg-secondary-subtle text-secondary">Ready</span>';
        }

        function getActionButtons(item) {
            return `
            <div class="dropdown">
                <button type="button" class="btn btn-primary dropdown-toggle" 
                        id="actionDropdown" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        data-bs-popper="static">
                    Action
                </button>
                <ul class="dropdown-menu">
                    <li><button class="dropdown-item btn-edit-koordinasi" data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'>Edit Koordinasi</button></li>
                    <li><button class="dropdown-item btn-detail" data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'>Detail</button></li>
                    <li><button class="dropdown-item btn-kepulangan" data-id="${item.id}">Kepulangan</button></li>
                    <li><button class="dropdown-item btn-delete" data-id="${item.id}">Hapus</button></li>
                </ul>
            </div>
        `;
        }

        // --- Event Handlers (Edit, Detail, Kepulangan, Delete) ---

        $(document).on('click', '.btn-edit-koordinasi', function() {
            const item = JSON.parse($(this).attr('data-item').replace(/&apos;/g, "'"));
            $('#edit_id').val(item.id);
            $('select[name="id_driver"]').val(item.karyawan?.id || '');
            if (item.status_apply === 1 || item.kendaraan) {
                $('#vehicleSection').show();
                $('select[name="kendaraan"]').val(item.kendaraan || '');
            } else {
                $('#vehicleSection').hide();
                $('select[name="kendaraan"]').val('');
            }
            if (item.budget) {
                $('#budgetSection').show();
                const budgetVal = Number(item.budget);
                $('#edit_budget_hidden').val(budgetVal);
                $('#edit_budget_input').val('Rp ' + budgetVal.toLocaleString('id-ID'));
            } else {
                $('#budgetSection').hide();
                $('#edit_budget_input').val('');
                $('#edit_budget_hidden').val('');
            }
            const body = $('#editDetailBody');
            body.empty();
            if (item.detail_pickup_driver?.length > 0) {
                item.detail_pickup_driver.forEach(d => body.append(createEditDetailRow(d)));
            } else {
                body.append(createEditDetailRow());
            }
            $('#editKoordinasiModal').modal('show');
        });

        $(document).on('click', '#addEditDetailRow', () => $('#editDetailBody').append(createEditDetailRow()));
        $(document).on('click', '.remove-edit-row', function() {
            $(this).closest('tr').remove();
        });

        function createEditDetailRow(detail = null) {
            const idx = Date.now() + Math.random();
            const waktu = detail?.waktu_keberangkatan ? moment(detail.waktu_keberangkatan, 'HH:mm:ss').format('HH:mm') : '';
            return `
        <tr data-index="${idx}">
            <td><select name="details[${idx}][tipe]" class="form-select form-select-sm" required>
                <option value="">Pilih</option>
                <option value="Penjemputan" ${detail?.tipe === 'Penjemputan' ? 'selected' : ''}>Penjemputan</option>
                <option value="Pengantaran" ${detail?.tipe === 'Pengantaran' ? 'selected' : ''}>Pengantaran</option>
            </select></td>
            <td><input type="text" name="details[${idx}][lokasi]" class="form-control form-control-sm" value="${detail?.lokasi || ''}" required></td>
            <td><input type="date" name="details[${idx}][tanggal]" class="form-control form-control-sm" value="${detail?.tanggal_keberangkatan || ''}" required></td>
            <td><input type="time" name="details[${idx}][waktu]" class="form-control form-control-sm" value="${waktu}" required></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-edit-row"><i class="fa fa-trash"></i></button></td>
        </tr>`;
        }

        $('#editKoordinasiForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: "{{ route('office.pickupDriver.updateKoordinasi') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: () => {
                    loadData();
                    Swal.fire('Berhasil!', 'Koordinasi berhasil diperbarui.', 'success');
                    $('#editKoordinasiModal').modal('hide');
                },
                error: (xhr) => Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan.',
                    'error')
            });
        });

        function getDriverButton(item, AuthId) {
            const isDriver = item.karyawan?.jabatan === "Driver";
            const isOwner = Number(item.karyawan?.id) == AuthId;
            if (item.status_apply === 1)
                return `<span class="badge bg-warning-subtle text-warning">Dalam Perjalanan</span>`;
            if (item.status_apply === 2) return `<span class="badge bg-success-subtle text-success">Selesai</span>`;
            if (item.status_apply === 0 && isDriver && isOwner) {
                return `<button class="btn btn-sm btn-warning btn-driver" data-id="${item.id}"><i class="fa fa-check me-1"></i> Terima</button>`;
            }
            return `<span class="badge bg-secondary-subtle text-secondary">Menunggu</span>`;
        }

        $(document).on('click', '.btn-driver', function() {
            const id = $(this).data('id');
            Swal.fire({
                    title: 'Terima Koordinasi?',
                    text: 'Anda akan memulai perjalanan ini.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Terima',
                    cancelButtonText: 'Batal'
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.post(`{{ url('/office/pickup-driver/update-status') }}/${id}`, {
                                _token: '{{ csrf_token() }}'
                            })
                            .done(function(response) {
                                if (response.success) {
                                    loadData();
                                    Swal.fire('Berhasil!', response.message, 'success');
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            })
                            .fail((xhr) => Swal.fire('Error!', xhr.responseJSON?.message ||
                                'Gagal mengupdate status.', 'error'));
                    }
                });
        });

        $(document).on('click', '.btn-detail', function() {
            const item = JSON.parse($(this).attr('data-item').replace(/&apos;/g, "'"));
            let html = `
            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Driver</small><h6 class="mb-0 fw-semibold">${item.karyawan?.nama_lengkap || '-'}</h6></div></div>
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Pembuat</small><h6 class="mb-0 fw-semibold">${item.pembuat?.nama_lengkap || '-'}</h6></div></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Mobil</small><h6 class="mb-0 fw-semibold">${item.kendaraan || 'Belum dipilih'}</h6></div></div>
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Budget</small><h6 class="mb-0 fw-semibold">${item.budget ? 'Rp ' + Number(item.budget).toLocaleString('id-ID') : '-'}</h6></div></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Budget Terpakai</small><h6 class="mb-0 fw-semibold">${item.uang_kepakai ? 'Rp ' + Number(item.uang_kepakai).toLocaleString('id-ID') : '-'}</h6></div></div>
                <div class="col-md-6"><div class="border rounded-3 p-3"><small class="text-muted">Budget Tersisa</small><h6 class="mb-0 fw-semibold ${item.sisa_budget < 0 ? 'text-danger' : ''}">${item.sisa_budget ? 'Rp ' + Number(item.sisa_budget).toLocaleString('id-ID') : '-'}${item.sisa_budget < 0 ? '<small class="text-danger d-block">Penggunaan dana melebihi budget</small>' : ''}</h6></div></div>
            </div>
            <p class="mt-3">Detail Rute</p><ul class="list-group">`;
            (item.detail_pickup_driver || []).forEach(d => {
                html +=
                    `<li class="list-group-item"><strong>${d.tipe}</strong> - ${d.lokasi}<br><small class="text-muted">${moment(d.tanggal_keberangkatan).format('DD-MM-YYYY')} ${moment(d.waktu_keberangkatan, 'HH:mm:ss').format('HH:mm')}</small><hr class="my-2"><div style="white-space: pre-line;">${d.detail || '-'}</div></li>`;
            });
            html += `</ul><p class="mt-3">Tracking</p><ul class="list-group">`;
            (item.tracking || []).forEach(t => {
                html +=
                    `<li class="list-group-item"><strong>${moment(t.created_at).format('DD-MM-YYYY HH:mm')}</strong><br><small class="text-muted">${t.status}</small></li>`;
            });
            html += '</ul>';
            $('#detailContent').html(html);
            $('#detailModal').modal('show');
        });

        $(document).on('click', '.btn-kepulangan', function() {
            const id = $(this).data('id');
            $('#kepulangan_id').val(id);
            $('#kepulanganForm')[0].reset();
            $('#km_warning').addClass('d-none');
            $('#budget_calculation').hide();
            $('#total_pemakaian_hidden').val('');
            const item = $(this).closest('tr').data('item');
            let vehicleType = item?.kendaraan || 'Innova';
            if (!VEHICLE_CONFIG[vehicleType]) vehicleType = 'Innova';
            const config = VEHICLE_CONFIG[vehicleType];
            const ratePerKm = config.fuelPrice / config.kmPerLiter;
            $('#vehicle_type_hidden').val(vehicleType);
            $('#tank_capacity_display').text(config.tankCapacity);
            $('#fuel_price_display').text(config.fuelPrice.toLocaleString('id-ID'));
            $('#rate_per_km').text(ratePerKm.toLocaleString('id-ID'));
            $('#kepulanganModal').modal('show');
        });

        $('#KM_akhir').on('input', function() {
            const kmAwal = parseInt($('#KM_awal').val()) || 0;
            const kmAkhir = parseInt($(this).val()) || 0;
            const vehicleType = $('#vehicle_type_hidden').val() || 'Innova';
            const config = VEHICLE_CONFIG[vehicleType] || VEHICLE_CONFIG['Innova'];
            const ratePerKm = config.fuelPrice / config.kmPerLiter;
            if (kmAkhir < kmAwal) {
                $('#km_warning').removeClass('d-none');
                $('#budget_calculation').hide();
                $('#total_pemakaian_hidden').val('');
            } else {
                $('#km_warning').addClass('d-none');
                const jarak = kmAkhir - kmAwal;
                const total = jarak * ratePerKm;
                $('#jarak_tempuh').text(jarak);
                $('#rate_per_km').text(ratePerKm.toLocaleString('id-ID'));
                $('#total_pemakaian').text(total.toLocaleString('id-ID'));
                $('#total_pemakaian_hidden').val(total);
                $('#budget_calculation').show();
            }
        });
        $('#KM_awal').on('input', function() {
            $('#KM_akhir').trigger('input');
        });

        $('#kepulanganForm').on('submit', function(e) {
            e.preventDefault();
            const kmAwal = parseInt($('#KM_awal').val()) || 0;
            const kmAkhir = parseInt($('#KM_akhir').val()) || 0;
            if (kmAkhir < kmAwal) {
                Swal.fire('Validasi Gagal', 'KM Akhir tidak boleh lebih kecil dari KM Awal', 'error');
                return;
            }
            const formData = $(this).serialize();
            $.post("{{ route('office.pickupDriver.updateKepulangan') }}", formData)
                .done((res) => {
                    if (res.success) {
                        loadData();
                        Swal.fire('Berhasil!', res.message, 'success');
                        $('#kepulanganModal').modal('hide');
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                })
                .fail((xhr) => Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal menyimpan kepulangan.',
                    'error'));
        });

        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                    title: 'Yakin hapus?',
                    text: 'Data tidak bisa dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/office/pickup-driver/delete/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: () => {
                                loadData();
                                Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                            },
                            error: () => Swal.fire('Error!', 'Gagal menghapus data.', 'error')
                        });
                    }
                });
        });

        // Dropdown fix for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                dropdown.addEventListener('show.bs.dropdown', function(e) {
                    const menu = this.querySelector('.dropdown-menu');
                    const button = this.querySelector('.dropdown-toggle');
                    if (window.innerWidth <= 768) {
                        const rect = button.getBoundingClientRect();
                        menu.style.width = rect.width + 'px';
                        menu.style.left = rect.left + 'px';
                        menu.style.right = 'auto';
                        menu.style.top = rect.bottom + window.scrollY + 'px';
                        menu.style.position = 'fixed';
                        menu.style.margin = '0';
                    }
                });
                dropdown.addEventListener('hide.bs.dropdown', function(e) {
                    const menu = this.querySelector('.dropdown-menu');
                    if (window.innerWidth <= 768) {
                        menu.style.position = '';
                        menu.style.left = '';
                        menu.style.right = '';
                        menu.style.top = '';
                        menu.style.width = '';
                        menu.style.margin = '';
                    }
                });
            });
        });
    </script>
@endsection
