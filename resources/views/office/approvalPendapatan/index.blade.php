@extends('layouts_office.app')
@section('office_contents')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <div id="lockScreenOverlay" class="lock-overlay">
        <div class="lock-card shadow-lg">
            <div class="text-center mb-4">
                <div class="vlk-icon-badge mx-auto mb-3"><i class="bi bi-shield-lock"></i></div>
                <h4 class="fw-bold mb-1">Approval Penjualan Terkunci</h4>
                <p class="text-muted small mb-0" id="lockScreenSubtitle">Memeriksa status keamanan...</p>
            </div>

            <div id="unlockLoadingState" class="text-center py-3">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="fw-semibold mb-1">Memuat data approval...</p>
                <p class="text-muted small mb-0">Mohon tunggu sebentar...</p>
            </div>

            <div id="unlockForm" class="d-none">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Password</label>
                    <input type="password" id="unlockPassword" class="form-control form-control-lg text-center"
                        placeholder="••••••" autofocus>
                    <div id="unlockError" class="text-danger small mt-1 d-none"></div>
                </div>
                <button class="btn btn-primary w-100 py-2 fw-semibold" id="btnUnlockApproval"
                    onclick="attemptUnlock('approval')">
                    <span class="btn-label"><i class="bi bi-unlock me-1"></i> Buka Kunci</span>
                    <span class="btn-spinner d-none"><span
                            class="spinner-border spinner-border-sm me-2"></span>Memeriksa...</span>
                </button>
                <div class="text-center mt-3">
                    <button class="btn btn-link text-muted small text-decoration-none" onclick="showFallbackLogin()">Gunakan
                        Password Login</button>
                </div>
            </div>

            <div id="fallbackForm" class="d-none">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Password Login</label>
                    <input type="password" id="fallbackPassword" class="form-control form-control-lg text-center"
                        placeholder="••••••">
                    <div id="fallbackError" class="text-danger small mt-1 d-none"></div>
                </div>
                <button class="btn btn-primary w-100 py-2 fw-semibold" id="btnUnlockLogin" onclick="attemptUnlock('login')">
                    <span class="btn-label"><i class="bi bi-unlock me-1"></i> Buka dengan Password Login</span>
                    <span class="btn-spinner d-none"><span
                            class="spinner-border spinner-border-sm me-2"></span>Memeriksa...</span>
                </button>
                <div class="text-center mt-3">
                    <button class="btn btn-link text-muted small text-decoration-none" onclick="showApprovalLogin()">←
                        Kembali ke Password Approval</button>
                </div>
            </div>

            <div id="failCounter" class="text-center mt-3 d-none">
                <span class="badge bg-danger-subtle text-danger-emphasis px-3 py-2 rounded-pill">
                    <i class="bi bi-exclamation-triangle me-1"></i> Percobaan gagal: <span id="failCount">0</span>/3
                </span>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setupModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-header px-4 py-3" style="background:var(--vlk-primary);color:#fff;border:none;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i>Pengaturan Awal Password Approval
                    </h5>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Untuk keamanan, hanya user dengan jabatan <b>Finance & Accounting</b>
                        yang dapat membuat password approval ini. Silakan masukkan password login sistem Anda untuk
                        konfirmasi identitas.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password Login Sistem</label>
                        <input type="password" id="setupLoginPass" class="form-control"
                            placeholder="Masukkan password login Anda">
                        <div id="setupLoginError" class="text-danger small mt-1 d-none"></div>
                    </div>
                    <hr class="my-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Buat Password Approval Baru</label>
                        <input type="password" id="setupNewPass" class="form-control" placeholder="Minimal 4 karakter">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Konfirmasi Password Approval Baru</label>
                        <input type="password" id="setupConfirmPass" class="form-control"
                            placeholder="Ulangi password baru">
                        <div id="setupNewError" class="text-danger small mt-1 d-none"></div>
                    </div>
                </div>
                <div class="modal-footer px-4 pb-4 border-0">
                    <button class="btn btn-primary fw-semibold w-100 py-2" onclick="submitSetup()"><i
                            class="bi bi-shield-check me-1"></i> Buat & Simpan Password Approval</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setupAccountingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-header px-4 py-3" style="background:var(--vlk-primary);color:#fff;border:none;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i>Setup Password Accounting</h5>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning small mb-3"><i class="bi bi-exclamation-triangle me-1"></i> Sistem
                        mendeteksi password fitur sudah ada, tetapi <b>Password Accounting</b> belum diatur. Anda wajib
                        mengisi password Accounting terlebih dahulu untuk melanjutkan.</div>
                    <p class="text-muted small mb-3">Masukkan password login sistem Anda (user Finance & Accounting) untuk
                        konfirmasi identitas, lalu buat Password Accounting yang akan digunakan ke depannya.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password Login Sistem</label>
                        <input type="password" id="accSetupLoginPass" class="form-control"
                            placeholder="Password login Anda">
                        <div id="accSetupLoginError" class="text-danger small mt-1 d-none"></div>
                    </div>
                    <hr class="my-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Buat Password Accounting Baru</label>
                        <input type="password" id="accSetupNewPass" class="form-control"
                            placeholder="Minimal 4 karakter">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Konfirmasi Password Accounting</label>
                        <input type="password" id="accSetupConfirmPass" class="form-control"
                            placeholder="Ulangi password">
                        <div id="accSetupNewError" class="text-danger small mt-1 d-none"></div>
                    </div>
                </div>
                <div class="modal-footer px-4 pb-4 border-0">
                    <button class="btn btn-primary fw-semibold w-100 py-2" onclick="submitAccountingSetup()"><i
                            class="bi bi-shield-check me-1"></i> Simpan Password Accounting</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changePassModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-header px-4 py-3" style="background:var(--vlk-mint);color:#fff;border:none;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-key me-2"></i>Ubah Password Approval</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password Approval Saat Ini</label>
                        <input type="password" id="chgCurrentPass" class="form-control">
                        <div id="chgCurrentError" class="text-danger small mt-1 d-none"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password Approval Baru</label>
                        <input type="password" id="chgNewPass" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Konfirmasi Password Approval Baru</label>
                        <input type="password" id="chgConfirmPass" class="form-control">
                        <div id="chgNewError" class="text-danger small mt-1 d-none"></div>
                    </div>
                </div>
                <div class="modal-footer px-4 pb-4 border-0">
                    <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary fw-semibold" onclick="submitChangePassword()"><i
                            class="bi bi-save me-1"></i> Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="vlk-theme">
        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 vlk-page-head">
                <div class="d-flex align-items-center gap-3">
                    <div class="vlk-icon-badge"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <h3 class="m-0">Approval Penjualan</h3>
                        <span class="vlk-subtitle">Rekonsiliasi pendapatan &amp; pelacakan dokumen per periode</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary-emphasis fs-6 px-3 py-2" id="current-period"></span>
                    <button type="button" class="btn btn-outline-primary btn-sm ks-icon-btn"
                        onclick="toggleFilterSidebar()" title="Filter & Urutkan">
                        <i class="bi bi-funnel-fill"></i>
                        <span class="ks-filter-badge" id="filterCountBadge" style="display:none;">0</span>
                    </button>
                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary btn-sm ks-icon-btn dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false" title="Export Laporan">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm vlk-dropdown-menu">
                            <li><button type="button" class="dropdown-item" id="btnExportExcel"><i
                                        class="bi bi-file-earmark-spreadsheet-fill me-2 text-success"></i>Export
                                    Excel</button></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#calculatorModal" title="Kalkulator Finansial">
                        <i class="bi bi-calculator me-1"></i>Kalkulator
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#changePassModal" title="Ubah Password Approval">
                        <i class="bi bi-key-fill me-1"></i>Ubah Password
                    </button>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-0 vlk-filter-card">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 mx-1">
                            <label class="form-label fw-semibold small text-muted">Tahun</label>
                            <select id="year" class="form-select" aria-label="tahun">
                                <option disabled>Pilih Tahun</option>
                                @php
                                    $tahun_sekarang = now()->year;
                                    for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                        $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                        echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-4 mx-1">
                            <label class="form-label fw-semibold small text-muted">Bulan</label>
                            <select id="month" class="form-select" aria-label="bulan">
                                <option disabled>Pilih Bulan</option>
                                @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = [
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
                                        'Desember',
                                    ];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        $bulan_nama = $nama_bulan[$bulan - 1];
                                        $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                        echo "<option value=\"$bulan\" $selected>$bulan_nama</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-3 mx-1">
                            <button class="btn btn-primary" onclick="loadTable();"><i
                                    class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="weekly-container"></div>
            <div id="loading-spinner" class="text-center py-5 d-none">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-3 text-muted">Memuat data...</p>
            </div>
        </div>
    </div>

    <div class="filter-sidebar-overlay" id="filterSidebarOverlay"></div>
    <div class="filter-sidebar" id="filterSidebar">
        <div class="filter-sidebar-header">
            <h5 class="m-0 fw-bold"><i class="bi bi-funnel me-2"></i>Filter & Urutkan</h5>
            <button class="btn-close" onclick="toggleFilterSidebar()"></button>
        </div>
        <div class="filter-sidebar-body">
            <div class="filter-live-note"><i class="bi bi-lightning-charge-fill"></i> Filter langsung diterapkan otomatis
            </div>
            <div class="filter-section">
                <button class="btn btn-outline-primary w-100" onclick="resetAllFilters()"><i
                        class="bi bi-arrow-counterclockwise me-2"></i>Reset Semua Filter</button>
            </div>
            <div class="filter-section">
                <h6 class="filter-section-title">Filter Sales & Instruktur</h6>
                <div class="mb-3">
                    <label class="form-label small">Sales</label>
                    <input type="text" class="form-control form-control-sm" id="filterSales"
                        placeholder="Cari nama sales (contoh: UDIN)...">
                    <small class="text-muted">Kosongkan = semua sales</small>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Instruktur</label>
                    <input type="text" class="form-control form-control-sm" id="filterInstruktur"
                        placeholder="Cari nama instruktur...">
                    <small class="text-muted">Kosongkan = semua instruktur</small>
                </div>
            </div>
            <div class="filter-section">
                <h6 class="filter-section-title">Filter Tanggal Training</h6>
                <div class="mb-3">
                    <label class="form-label small">Tanggal Mulai</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateStart">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Tanggal Selesai</label>
                    <input type="date" class="form-control form-control-sm" id="filterDateEnd">
                </div>
            </div>
            <div class="filter-section">
                <h6 class="filter-section-title">Filter Status</h6>
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="">Semua Status</option>
                    <option value="valid">Tervalidasi</option>
                    <option value="belum">Belum Valid</option>
                </select>
            </div>
            <div class="filter-section">
                <h6 class="filter-section-title">Filter Nilai (Range)</h6>
                <div class="mb-3">
                    <label class="form-label small">Total Penjualan Kotor</label>
                    <div class="row g-2">
                        <div class="col-6"><input type="number" class="form-control form-control-sm"
                                id="rangeTotalMin" placeholder="Min"></div>
                        <div class="col-6"><input type="number" class="form-control form-control-sm"
                                id="rangeTotalMax" placeholder="Max"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Total Penjualan Bersih</label>
                    <div class="row g-2">
                        <div class="col-6"><input type="number" class="form-control form-control-sm" id="rangeNettMin"
                                placeholder="Min"></div>
                        <div class="col-6"><input type="number" class="form-control form-control-sm" id="rangeNettMax"
                                placeholder="Max"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Pax</label>
                    <div class="row g-2">
                        <div class="col-6"><input type="number" class="form-control form-control-sm" id="rangePaxMin"
                                placeholder="Min"></div>
                        <div class="col-6"><input type="number" class="form-control form-control-sm" id="rangePaxMax"
                                placeholder="Max"></div>
                    </div>
                </div>
            </div>
            <div class="filter-section">
                <h6 class="filter-section-title">Tampilkan Kolom</h6>
                <div class="column-toggles">
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="no_faktur"
                            id="colNoFaktur" checked><label class="form-check-label" for="colNoFaktur">No Faktur</label>
                    </div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="no_invoice"
                            id="colNoInvoice" checked><label class="form-check-label" for="colNoInvoice">No
                            Invoice</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="materi"
                            id="colMateri" checked><label class="form-check-label" for="colMateri">Materi</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="tanggal_training"
                            id="colTanggalTraining" checked><label class="form-check-label"
                            for="colTanggalTraining">Tanggal</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="perusahaan"
                            id="colPerusahaan" checked><label class="form-check-label"
                            for="colPerusahaan">Perusahaan</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="nama_sales"
                            id="colSales" checked><label class="form-check-label" for="colSales">Sales</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="instruktur"
                            id="colInstruktur" checked><label class="form-check-label"
                            for="colInstruktur">Instruktur</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="harga"
                            id="colHarga" checked><label class="form-check-label" for="colHarga">Harga</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="pax"
                            id="colPax" checked><label class="form-check-label" for="colPax">Pax</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox"
                            value="total_penjualan_kotor" id="colTotalKotor" checked><label class="form-check-label"
                            for="colTotalKotor">Total Penjualan Kotor</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="diskon"
                            id="colDiskon" checked><label class="form-check-label" for="colDiskon">Diskon/PA</label>
                    </div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="total_diskon"
                            id="colTotalDiskon" checked><label class="form-check-label" for="colTotalDiskon">Total
                            Diskon</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="total_pa"
                            id="colTotalPA" checked><label class="form-check-label" for="colTotalPA">Total PA</label>
                    </div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="total_cashback"
                            id="colCashback" checked><label class="form-check-label" for="colCashback">Cashback</label>
                    </div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="total_uang_saku"
                            id="colUangSaku" checked><label class="form-check-label" for="colUangSaku">Uang Saku</label>
                    </div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="total_akomodasi"
                            id="colAkomodasi" checked><label class="form-check-label"
                            for="colAkomodasi">Akomodasi</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="oleh_oleh"
                            id="colOlehOleh" checked><label class="form-check-label" for="colOlehOleh">Oleh-Oleh</label>
                    </div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="biaya_lain_lain"
                            id="colBiayaLain" checked><label class="form-check-label" for="colBiayaLain">Biaya
                            Lain-Lain</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="entertainment"
                            id="colEntertainment" checked><label class="form-check-label"
                            for="colEntertainment">Entertainment</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="jenis_transport"
                            id="colJenisTransport" checked><label class="form-check-label" for="colJenisTransport">Jenis
                            Transport</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="biaya_transport"
                            id="colBiayaTransport" checked><label class="form-check-label" for="colBiayaTransport">Biaya
                            Transport</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="pengurangan_pph"
                            id="colPenguranganPPH" checked><label class="form-check-label"
                            for="colPenguranganPPH">Pengurangan PPH</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="exam"
                            id="colExam" checked><label class="form-check-label" for="colExam">Exam</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox"
                            value="total_penjualan_sales" id="colTotalBersih" checked><label class="form-check-label"
                            for="colTotalBersih">Total Bersih</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="PPN"
                            id="colPPN" checked><label class="form-check-label" for="colPPN">PPN</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="PPH"
                            id="colPPH" checked><label class="form-check-label" for="colPPH">PPH</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="jumlah_pembayaran"
                            id="colPembayaran" checked><label class="form-check-label" for="colPembayaran">Jumlah
                            Pembayaran</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="tanggal_pembayaran"
                            id="colTanggalPembayaran" checked><label class="form-check-label"
                            for="colTanggalPembayaran">Tgl Pembayaran</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="biaya_admin"
                            id="colBiayaAdmin" checked><label class="form-check-label" for="colBiayaAdmin">Biaya
                            Admin</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="total_piutang"
                            id="colTotalPiutang" checked><label class="form-check-label" for="colTotalPiutang">Total
                            Piutang</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="tanggal_mulai"
                            id="colTanggalMulai" checked><label class="form-check-label" for="colTanggalMulai">Tgl
                            Mulai</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" value="tanggal_selesai"
                            id="colTanggalSelesai" checked><label class="form-check-label" for="colTanggalSelesai">Tgl
                            Selesai</label></div>
                </div>
            </div>
            <div class="filter-section">
                <h6 class="filter-section-title">Preview Filter Aktif</h6>
                <div id="activeFiltersPreview" class="active-filters-list">
                    <p class="text-muted small mb-0">Belum ada filter aktif</p>
                </div>
            </div>
        </div>
        <div class="filter-sidebar-footer">
            <button class="btn btn-primary w-100" onclick="applyAllFilters()"><i
                    class="bi bi-check-lg me-2"></i>Selesai</button>
        </div>
    </div>

    <div class="modal fade" id="calculatorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-header px-4 py-3" style="background:var(--vlk-mint);color:#fff;border:none;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calculator me-2"></i>Kalkulator Finansial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <ul class="nav nav-tabs px-3 pt-3" id="calcTabs" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" id="basic-tab"
                                data-bs-toggle="tab" data-bs-target="#basic" type="button"><i
                                    class="bi bi-calculator me-1"></i>Basic</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="pajak-tab"
                                data-bs-toggle="tab" data-bs-target="#pajak" type="button"><i
                                    class="bi bi-receipt me-1"></i>Pajak</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="diskon-tab"
                                data-bs-toggle="tab" data-bs-target="#diskon" type="button"><i
                                    class="bi bi-percent me-1"></i>Diskon</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="komisi-tab"
                                data-bs-toggle="tab" data-bs-target="#komisi" type="button"><i
                                    class="bi bi-award me-1"></i>Komisi</button></li>
                    </ul>
                    <div class="tab-content p-4" id="calcTabContent">
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12"><label class="form-label small">Angka 1</label><input type="number"
                                        class="form-control" id="calcNum1"></div>
                                <div class="col-12"><label class="form-label small">Angka 2</label><input type="number"
                                        class="form-control" id="calcNum2"></div>
                                <div class="col-12">
                                    <div class="btn-group w-100">
                                        <button class="btn btn-outline-primary" onclick="calcBasic('+')">+</button>
                                        <button class="btn btn-outline-primary" onclick="calcBasic('-')">-</button>
                                        <button class="btn btn-outline-primary" onclick="calcBasic('*')">×</button>
                                        <button class="btn btn-outline-primary" onclick="calcBasic('/')">÷</button>
                                    </div>
                                </div>
                                <div class="col-12"><label class="form-label small">Hasil</label><input type="text"
                                        class="form-control fw-bold" id="calcResult" readonly></div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pajak" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12"><label class="form-label small">Dasar Pengenaan Pajak
                                        (DPP)</label><input type="number" class="form-control" id="pajakDPP"
                                        placeholder="Masukkan DPP"></div>
                                <div class="col-6"><label class="form-label small">PPN (%)</label><select
                                        class="form-select" id="pajakPPNRate">
                                        <option value="11">11%</option>
                                        <option value="12">12%</option>
                                        <option value="0">0%</option>
                                    </select></div>
                                <div class="col-6"><label class="form-label small">PPH (%)</label><select
                                        class="form-select" id="pajakPPHRate">
                                        <option value="2">2%</option>
                                        <option value="5">5%</option>
                                        <option value="10">10%</option>
                                        <option value="0">0%</option>
                                    </select></div>
                                <div class="col-12"><button class="btn btn-primary w-100" onclick="calcPajak()">Hitung
                                        Pajak</button></div>
                                <div class="col-12 mt-3">
                                    <div class="p-3 rounded" style="background:var(--vlk-primary-soft);">
                                        <div class="d-flex justify-content-between mb-2"><span>PPN:</span><strong
                                                id="pajakPPNResult">Rp 0</strong></div>
                                        <div class="d-flex justify-content-between mb-2"><span>PPH:</span><strong
                                                id="pajakPPHResult">Rp 0</strong></div>
                                        <hr>
                                        <div class="d-flex justify-content-between"><span>Total Setelah
                                                Pajak:</span><strong id="pajakTotalResult"
                                                style="color:var(--vlk-primary);">Rp 0</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="diskon" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12"><label class="form-label small">Harga Awal</label><input
                                        type="number" class="form-control" id="diskonHarga"
                                        placeholder="Harga sebelum diskon"></div>
                                <div class="col-12"><label class="form-label small">Diskon Bertingkat (pisahkan dengan
                                        koma)</label><input type="text" class="form-control" id="diskonTingkat"
                                        placeholder="Contoh: 10,5,2"><small class="text-muted">Diskon 10% + 5% +
                                        2%</small></div>
                                <div class="col-12"><button class="btn btn-primary w-100" onclick="calcDiskon()">Hitung
                                        Diskon</button></div>
                                <div class="col-12 mt-3">
                                    <div class="p-3 rounded" style="background:var(--vlk-coral-soft);">
                                        <div class="d-flex justify-content-between mb-2"><span>Total Diskon:</span><strong
                                                id="diskonTotal">Rp 0</strong></div>
                                        <div class="d-flex justify-content-between"><span>Harga Akhir:</span><strong
                                                id="diskonAkhir" style="color:var(--vlk-coral);">Rp 0</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="komisi" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12"><label class="form-label small">Total Penjualan Bersih</label><input
                                        type="number" class="form-control" id="komisiNett"
                                        placeholder="Masukkan total nett"></div>
                                <div class="col-12"><label class="form-label small">Persentase Komisi (%)</label><input
                                        type="number" class="form-control" id="komisiPersen" value="2"
                                        placeholder="Default 2%"></div>
                                <div class="col-12"><button class="btn btn-primary w-100" onclick="calcKomisi()">Hitung
                                        Komisi</button></div>
                                <div class="col-12 mt-3">
                                    <div class="p-3 rounded" style="background:var(--vlk-amber-soft);">
                                        <div class="d-flex justify-content-between"><span>Estimasi Komisi:</span><strong
                                                id="komisiHasil" style="color:var(--vlk-amber);font-size:1.2rem;">Rp
                                                0</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius:16px;">
                <div class="modal-header px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="modal-icon-wrap d-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-20"
                            style="width:40px;height:40px;"><i class="bi bi-pencil-square fs-5"></i></div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Update Penjualan Kotor</h5>
                            <small class="text-white text-opacity-75" id="modal-subtitle">—</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="px-4 py-2 border-bottom d-flex align-items-center gap-0 vlk-steps-bar">
                    <div class="step-pill active" data-step="1"><span class="step-num">1</span><span
                            class="step-label">Informasi</span></div>
                    <div class="step-line"></div>
                    <div class="step-pill" data-step="2"><span class="step-num">2</span><span
                            class="step-label">Perhitungan</span></div>
                    <div class="step-line"></div>
                    <div class="step-pill" data-step="3"><span class="step-num">3</span><span
                            class="step-label">Tracking</span></div>
                </div>
                <form id="formUpdate">
                    @csrf
                    <input type="hidden" id="update_id">
                    <div class="modal-body p-0 vlk-modal-body" style="max-height:70vh;overflow-y:auto;">
                        <div class="step-content p-4" id="step-1">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="section-label-bar section-blue"><i
                                            class="bi bi-file-text me-2"></i><span>Informasi Training & Invoice</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">No Faktur</label>
                                    <div class="input-group"><span class="input-group-text bg-white"><i
                                                class="bi bi-receipt text-muted"></i></span><input type="text"
                                            class="form-control" id="no_faktur" name="no_faktur"
                                            placeholder="Nomor faktur"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">No Invoice</label>
                                    <div class="input-group"><span class="input-group-text bg-white"><i
                                                class="bi bi-card-list text-muted"></i></span><input type="text"
                                            class="form-control" id="no_invoice" name="no_invoice"
                                            placeholder="Nomor invoice"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Materi</label>
                                    <select class="form-select" id="materi" name="materi">
                                        <option value="">Pilih Materi</option>
                                        @foreach ($dataMateri as $m)
                                            <option value="{{ $m->id }}">{{ $m->nama_materi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Perusahaan</label>
                                    <select class="form-select" id="perusahaan" name="perusahaan">
                                        <option value="">Pilih Perusahaan</option>
                                        @foreach ($dataPerusahaan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Tanggal Mulai</label>
                                    <div class="input-group"><span class="input-group-text bg-white"><i
                                                class="bi bi-calendar-event text-muted"></i></span><input type="date"
                                            class="form-control" id="tanggal_mulai" name="tanggal_mulai"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Tanggal Selesai</label>
                                    <div class="input-group"><span class="input-group-text bg-white"><i
                                                class="bi bi-calendar-check text-muted"></i></span><input type="date"
                                            class="form-control" id="tanggal_selesai" name="tanggal_selesai"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Sales</label>
                                    <div class="input-group"><span class="input-group-text bg-white"><i
                                                class="bi bi-person text-muted"></i></span><input type="text"
                                            class="form-control bg-light" id="nama_sales" readonly placeholder="—"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Instruktur</label>
                                    <div class="input-group"><span class="input-group-text bg-white"><i
                                                class="bi bi-person-badge text-muted"></i></span><input type="text"
                                            class="form-control bg-light" id="instruktur" readonly placeholder="—"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4"><button type="button"
                                    class="btn btn-primary px-4" onclick="goStep(2)">Selanjutnya <i
                                        class="bi bi-arrow-right ms-1"></i></button></div>
                        </div>
                        <div class="step-content p-4 d-none" id="step-2">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="section-label-bar section-mint"><i
                                            class="bi bi-calculator me-2"></i><span>Perhitungan Penjualan</span></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Harga Net</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="harga" name="harga">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold small">Pax</label>
                                    <input type="number" class="form-control input-calc" id="pax" name="pax"
                                        min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Total Penjualan Kotor</label>
                                    <div class="input-group"><span
                                            class="input-group-text vlk-input-prefix-mint">Rp</span><input type="text"
                                            inputmode="numeric"
                                            class="form-control vlk-input-tint-mint fw-bold currency-input" id="total"
                                            name="total"></div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="section-label-bar section-coral"><i
                                            class="bi bi-dash-circle me-2"></i><span>Pengurang</span></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Diskon / PA</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="diskon" name="diskon">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Total Diskon</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_diskon"
                                            name="total_diskon"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Total PA</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_pa"
                                            name="total_pa"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Cashback</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_cashback"
                                            name="total_cashback"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Uang Saku</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_uang_saku"
                                            name="total_uang_saku"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Akomodasi</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_akomodasi"
                                            name="total_akomodasi"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Oleh-Oleh Peserta</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="oleh_oleh"
                                            name="oleh_oleh"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Biaya Lain-Lain</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="biaya_lain_lain"
                                            name="biaya_lain_lain"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Entertainment</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="entertainment"
                                            name="entertainment"></div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="section-label-bar section-amber"><i
                                            class="bi bi-truck me-2"></i><span>Transport & Pajak</span></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Jenis Transportasi</label>
                                    <select class="form-select" id="transportasi_select">
                                        <option value="">Pilih Transportasi</option>
                                        <option>Pesawat</option>
                                        <option>Kereta</option>
                                        <option>Bus</option>
                                        <option>Mobil</option>
                                        <option>Travel</option>
                                        <option>Lainnya</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 d-none" id="transportasi_manual"
                                        placeholder="Transportasi lainnya">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Biaya Transport</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="biaya_transport"
                                            name="biaya_transport"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Pengurangan PPH</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="pengurangan_pph"
                                            name="pengurangan_pph"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Exam</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="exam_value"
                                            name="exam"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">PPN</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric" class="form-control currency-input"
                                            id="PPN" name="PPN"></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">PPH</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric" class="form-control currency-input"
                                            id="PPH" name="PPH"></div>
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="section-label-bar section-lavender"><i
                                            class="bi bi-wallet2 me-2"></i><span>Pembayaran</span></div>
                                </div>
                                <div class="col-md-4"><label class="form-label fw-semibold small">PIC</label><input
                                        type="text" class="form-control" id="pic" name="pic"></div>
                                <div class="col-md-4"><label class="form-label fw-semibold small">No.
                                        Regist</label><input type="text" class="form-control" id="regist"
                                        name="regist"></div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Jumlah Pembayaran</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric" class="form-control currency-input"
                                            id="jumlah_pembayaran" name="jumlah_pembayaran"></div>
                                </div>
                                <div class="col-md-4"><label class="form-label fw-semibold small">Tanggal
                                        Pembayaran</label><input type="date" class="form-control"
                                        id="tanggal_pembayaran" name="tanggal_pembayaran"></div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Biaya Admin</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span><input
                                            type="text" inputmode="numeric" class="form-control currency-input"
                                            id="biaya_admin" name="biaya_admin"></div>
                                </div>
                            </div>
                            <div
                                class="mt-4 p-3 rounded-3 d-flex align-items-center justify-content-between vlk-summary-panel">
                                <div>
                                    <div class="small opacity-75 mb-1">Total Penjualan Sales (Bersih)</div>
                                    <input type="text" inputmode="numeric"
                                        class="form-control fw-bold text-dark currency-input" id="total_penjualan_sales"
                                        name="total_penjualan_sales">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-light px-4" onclick="goStep(1)"><i
                                        class="bi bi-arrow-left me-1"></i> Kembali</button>
                                <button type="button" class="btn btn-primary px-4" onclick="goStep(3)">Selanjutnya <i
                                        class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </div>
                        <div class="step-content p-4 d-none" id="step-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="section-label-bar section-lavender"><i
                                            class="bi bi-clipboard2-check me-2"></i><span>Tracking Outstanding</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="tracking-grid">
                                        <div class="tracking-item disabled">
                                            <div class="tracking-check-wrap"><input class="form-check-input"
                                                    type="checkbox" id="trk_invoice" disabled checked></div>
                                            <div class="tracking-info"><span class="tracking-title">Invoice</span><span
                                                    class="tracking-desc text-muted small">Otomatis terisi</span></div>
                                            <span class="badge vlk-badge-auto">Auto</span>
                                        </div>
                                        <div class="tracking-item">
                                            <div class="tracking-check-wrap"><input
                                                    class="form-check-input tracking-check" type="checkbox"
                                                    id="trk_faktur_pajak" name="faktur_pajak" value="1"></div>
                                            <div class="tracking-info"><label class="tracking-title"
                                                    for="trk_faktur_pajak">Faktur Pajak</label></div>
                                        </div>
                                        <div class="tracking-item">
                                            <div class="tracking-check-wrap"><input
                                                    class="form-check-input tracking-check" type="checkbox"
                                                    id="trk_dokumen_tambahan" name="dokumen_tambahan" value="1">
                                            </div>
                                            <div class="tracking-info"><label class="tracking-title"
                                                    for="trk_dokumen_tambahan">Dokumen Tambahan</label></div>
                                        </div>
                                        <div class="tracking-item">
                                            <div class="tracking-check-wrap"><input
                                                    class="form-check-input tracking-check" type="checkbox"
                                                    id="trk_konfir_cs" name="konfir_cs" value="1"></div>
                                            <div class="tracking-info"><label class="tracking-title"
                                                    for="trk_konfir_cs">Konfirmasi Pengiriman RPX</label></div>
                                        </div>
                                        <div class="tracking-item">
                                            <div class="tracking-check-wrap"><input
                                                    class="form-check-input tracking-check" type="checkbox"
                                                    id="trk_tracking_dokumen" name="tracking_dokumen" value="1">
                                            </div>
                                            <div class="tracking-info"><label class="tracking-title"
                                                    for="trk_tracking_dokumen">Tracking Dokumen</label></div>
                                        </div>
                                        <div class="tracking-item">
                                            <div class="tracking-check-wrap"><input
                                                    class="form-check-input tracking-check" type="checkbox"
                                                    id="trk_no_resi" name="no_resi" value="1"></div>
                                            <div class="tracking-info"><label class="tracking-title"
                                                    for="trk_no_resi">Status Resi</label></div>
                                        </div>
                                        <div class="tracking-item">
                                            <div class="tracking-check-wrap"><input
                                                    class="form-check-input tracking-check" type="checkbox"
                                                    id="trk_konfir_pic" name="konfir_pic" value="1"></div>
                                            <div class="tracking-info"><label class="tracking-title"
                                                    for="trk_konfir_pic">Konfirmasi PIC</label></div>
                                        </div>
                                        <div class="tracking-item">
                                            <div class="tracking-check-wrap"><input
                                                    class="form-check-input tracking-check" type="checkbox"
                                                    id="trk_pembayaran" name="pembayaran" value="1"></div>
                                            <div class="tracking-info"><label class="tracking-title"
                                                    for="trk_pembayaran">Pembayaran</label></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Nomor Resi</label>
                                    <div class="input-group"><span class="input-group-text bg-white"><i
                                                class="bi bi-box-seam text-muted"></i></span><input type="text"
                                            class="form-control" id="trk_status_resi"
                                            placeholder="Masukkan nomor resi"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Status PIC</label>
                                    <input type="text" class="form-control tracking-check" id="trk_status_pic"
                                        name="status_pic" placeholder="Status PIC">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-light px-4" onclick="goStep(2)"><i
                                        class="bi bi-arrow-left me-1"></i> Kembali</button>
                                <button type="submit" class="btn btn-mint px-5 fw-semibold" id="btnSimpan"><i
                                        class="bi bi-save me-2"></i>Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        :root {
            --vlk-primary: #7AA4D4;
            --vlk-primary-dark: #5E8BC2;
            --vlk-primary-light: #A9C4E3;
            --vlk-primary-soft: #E8F0F9;
            --vlk-coral: #E89B7C;
            --vlk-coral-soft: #FBEEE9;
            --vlk-mint: #8CC5A7;
            --vlk-mint-soft: #E5F3EB;
            --vlk-lavender: #A79CD1;
            --vlk-lavender-soft: #EFECF8;
            --vlk-amber: #E5C17A;
            --vlk-amber-soft: #FAF1DE;
            --vlk-rose: #E08CA8;
            --vlk-ink: #2A3A4D;
            --vlk-muted: #6B7C93;
            --vlk-bg: #F7F9FC;
            --vlk-surface: #FFFFFF;
            --vlk-border: #E4E9F1;
        }

        .vlk-theme {
            --vlk-primary: #7AA4D4;
            --vlk-primary-dark: #10315a;
            --vlk-primary-light: #A9C4E3;
            --vlk-primary-soft: #E8F0F9;
            --vlk-coral: #E89B7C;
            --vlk-coral-soft: #FBEEE9;
            --vlk-mint: #8CC5A7;
            --vlk-mint-soft: #E5F3EB;
            --vlk-lavender: #A79CD1;
            --vlk-lavender-soft: #EFECF8;
            --vlk-amber: #E5C17A;
            --vlk-amber-soft: #FAF1DE;
            --vlk-rose: #E08CA8;
            --vlk-ink: #2A3A4D;
            --vlk-muted: #6B7C93;
            --vlk-bg: #F7F9FC;
            --vlk-surface: #FFFFFF;
            --vlk-border: #E4E9F1;
            --bs-primary: var(--vlk-primary);
            --bs-primary-rgb: 122, 164, 212;
            --bs-success: #8CC5A7;
            --bs-success-rgb: 140, 197, 167;
            --bs-warning: var(--vlk-amber);
            --bs-warning-rgb: 229, 193, 122;
            --bs-danger: #E89B7C;
            --bs-danger-rgb: 232, 155, 124;
            --bs-info: var(--vlk-lavender);
            --bs-info-rgb: 167, 156, 209;
            --bs-body-font-family: 'Inter', sans-serif;
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--vlk-ink);
            background: none;
            display: block;
            padding-bottom: 1px;
        }

        .vlk-theme h3,
        .vlk-theme h5,
        .vlk-theme .modal-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .vlk-theme h3 {
            font-weight: 800;
            letter-spacing: -.02em;
            color: var(--vlk-ink);
        }

        .vlk-subtitle {
            font-size: .8rem;
            color: var(--vlk-muted);
        }

        .vlk-icon-badge {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--vlk-primary);
            color: #fff;
            font-size: 1.15rem;
            box-shadow: 0 6px 16px -6px rgba(122, 164, 212, .55);
        }

        .vlk-theme .badge.bg-primary-subtle {
            background: var(--vlk-primary-soft) !important;
            color: var(--vlk-primary-dark) !important;
            border: 1px solid var(--vlk-primary-light);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .vlk-filter-card {
            border-radius: 16px !important;
            background: var(--vlk-surface);
            border: 1px solid var(--vlk-border) !important;
        }

        .vlk-theme .form-select,
        .vlk-theme .form-control {
            border-radius: 10px;
            border-color: var(--vlk-border);
        }

        .vlk-theme .form-select:focus,
        .vlk-theme .form-control:focus {
            border-color: var(--vlk-primary);
            box-shadow: 0 0 0 .22rem rgba(122, 164, 212, .22);
        }

        .vlk-theme .btn-primary {
            background: var(--vlk-primary);
            border-color: var(--vlk-primary);
            border-radius: 10px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 4px 12px -4px rgba(122, 164, 212, .55);
        }

        .vlk-theme .btn-primary:hover,
        .vlk-theme .btn-primary:focus {
            background: var(--vlk-primary-dark);
            border-color: var(--vlk-primary-dark);
            transform: translateY(-1px);
            color: #fff;
        }

        .vlk-theme .btn-mint {
            background: var(--vlk-mint);
            border-color: var(--vlk-mint);
            border-radius: 10px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 4px 12px -4px rgba(140, 197, 167, .55);
        }

        .vlk-theme .btn-mint:hover {
            background: #79B796;
            border-color: #79B796;
            color: #fff;
        }

        .vlk-theme .btn-success {
            background: var(--vlk-mint);
            border-color: var(--vlk-mint);
            border-radius: 10px;
            font-weight: 600;
            color: #fff;
        }

        .vlk-theme .btn-success:hover {
            background: #79B796;
            border-color: #79B796;
            color: #fff;
        }

        .vlk-theme .btn-light {
            border-radius: 10px;
            font-weight: 600;
            background: #F1F4F9;
            border-color: var(--vlk-border);
        }

        .vlk-theme .btn {
            transition: transform .15s ease, background-color .15s ease;
        }

        .vlk-card {
            border-radius: 16px !important;
            border: 1px solid var(--vlk-border) !important;
            background: var(--vlk-surface);
            box-shadow: 0 1px 3px rgba(42, 58, 77, .05);
            animation: vlkFadeIn .35s ease both;
            position: relative;
        }

        .vlk-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 16px;
            bottom: 16px;
            width: 3px;
            background: var(--vlk-primary);
            border-radius: 0 3px 3px 0;
        }

        .vlk-card:nth-child(even)::before {
            background: var(--vlk-coral);
        }

        .vlk-card:nth-child(3n)::before {
            background: var(--vlk-mint);
        }

        .vlk-card:nth-child(4n)::before {
            background: var(--vlk-lavender);
        }

        .vlk-card .card-body.text-center .bi-inbox {
            color: var(--vlk-lavender) !important;
        }

        @keyframes vlkFadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .vlk-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .vlk-week-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--vlk-amber);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: .75rem;
            margin-right: 8px;
        }

        .vlk-status-pill {
            font-family: 'Inter', sans-serif;
            font-size: .72rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: var(--vlk-mint-soft);
            color: #4E8E6F;
            border: 1px solid var(--vlk-mint);
        }

        #weekly-container {
            overflow-y: hidden;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .vlk-theme .table th {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            color: var(--vlk-primary-dark);
            background: var(--vlk-primary-soft);
            border-bottom: 2px solid var(--vlk-primary-light);
            white-space: nowrap;
        }

        .vlk-theme .table td {
            font-size: .8rem;
            vertical-align: middle;
            color: #000;
        }

        .vlk-theme .table td.text-end {
            font-family: 'Inter', sans-serif;
            font-variant-numeric: tabular-nums;
            font-size: .78rem;
            color: #000;
        }

        .vlk-theme .table .num-zero {
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            color: #000;
        }

        .vlk-theme .table-striped>tbody>tr:nth-of-type(odd)>td {
            background-color: #FAFBFD;
        }

        .vlk-theme .table-hover>tbody>tr:hover>td {
            background-color: var(--vlk-amber-soft);
        }

        @media (max-width:1400px) {

            .vlk-theme .table th,
            .vlk-theme .table td {
                padding: .4rem .5rem;
            }
        }

        .vlk-theme tr.table-warning>td {
            background-color: var(--vlk-coral-soft) !important;
        }

        .vlk-theme tr.table-warning>td:first-child {
            box-shadow: inset 3px 0 0 var(--vlk-coral);
        }

        .vlk-theme .table-info td {
            background-color: var(--vlk-primary-soft) !important;
            color: var(--vlk-primary-dark) !important;
            border-top: 2px solid var(--vlk-primary-light);
            font-family: 'Inter', sans-serif;
        }

        .vlk-theme .table-dark td {
            background-color: var(--vlk-ink) !important;
            color: #F3F6F5 !important;
            border-top: 3px double var(--vlk-amber);
            font-size: .85rem;
            font-family: 'Inter', sans-serif;
        }

        .sync-scroll-wrapper {
            overflow-x: auto;
        }

        .sync-scroll-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .sync-scroll-wrapper::-webkit-scrollbar-thumb {
            background: var(--vlk-primary-light);
            border-radius: 4px;
        }

        .sync-scroll-wrapper::-webkit-scrollbar-track {
            background: var(--vlk-bg);
        }

        .vlk-theme .modal-header {
            background: var(--vlk-primary);
            color: #fff;
            border-bottom: none;
        }

        .vlk-modal-body {
            background: var(--vlk-bg);
        }

        .vlk-steps-bar {
            background: var(--vlk-surface);
        }

        .step-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 500;
            color: #9aa5b4;
            background: transparent;
            transition: all .2s;
        }

        .step-pill.active {
            background: var(--vlk-primary-soft);
            color: var(--vlk-primary-dark);
        }

        .step-pill.done {
            color: #4E8E6F;
        }

        .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #e8ecf2;
            color: #6b7c93;
            font-size: .75rem;
            font-weight: 700;
        }

        .step-pill.active .step-num {
            background: var(--vlk-primary);
            color: #fff;
        }

        .step-pill.done .step-num {
            background: var(--vlk-mint);
            color: #fff;
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: #e8ecf2;
            min-width: 24px;
            margin: 0 4px;
        }

        .section-label-bar {
            display: flex;
            align-items: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #374151;
            padding-bottom: 8px;
            border-bottom: 2px solid #e8ecf2;
            margin-bottom: 4px;
        }

        .section-label-bar.section-blue i {
            color: var(--vlk-primary);
        }

        .section-label-bar.section-blue {
            border-bottom-color: var(--vlk-primary-light);
        }

        .section-label-bar.section-mint i {
            color: var(--vlk-mint);
        }

        .section-label-bar.section-mint {
            border-bottom-color: var(--vlk-mint);
        }

        .section-label-bar.section-coral i {
            color: var(--vlk-coral);
        }

        .section-label-bar.section-coral {
            border-bottom-color: var(--vlk-coral);
        }

        .section-label-bar.section-amber i {
            color: var(--vlk-amber);
        }

        .section-label-bar.section-amber {
            border-bottom-color: var(--vlk-amber);
        }

        .section-label-bar.section-lavender i {
            color: var(--vlk-lavender);
        }

        .section-label-bar.section-lavender {
            border-bottom-color: var(--vlk-lavender);
        }

        .vlk-summary-panel {
            background: var(--vlk-primary);
            color: #fff;
            border: 1px solid var(--vlk-primary-dark);
        }

        .vlk-summary-panel input {
            background: rgba(255, 255, 255, .18);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .35);
        }

        .vlk-summary-panel input::placeholder {
            color: rgba(255, 255, 255, .6);
        }

        .vlk-summary-panel .small {
            color: rgba(255, 255, 255, .85);
        }

        .vlk-input-prefix-mint {
            background: var(--vlk-mint-soft) !important;
            color: #4E8E6F !important;
            border-color: var(--vlk-border);
        }

        .vlk-input-tint-mint {
            background: var(--vlk-mint-soft) !important;
            color: #4E8E6F !important;
        }

        .tracking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 10px;
        }

        .tracking-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid var(--vlk-border);
            border-radius: 10px;
            padding: 12px 14px;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .tracking-item:hover:not(.disabled) {
            border-color: var(--vlk-primary);
            box-shadow: 0 0 0 3px var(--vlk-primary-soft);
        }

        .tracking-item.disabled {
            opacity: .55;
        }

        .tracking-check-wrap .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            margin: 0;
        }

        .tracking-check-wrap .form-check-input:checked {
            background-color: var(--vlk-mint);
            border-color: var(--vlk-mint);
        }

        .tracking-info {
            flex: 1;
        }

        .tracking-title {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            color: var(--vlk-ink);
            margin: 0;
            cursor: pointer;
        }

        .tracking-desc {
            font-size: .75rem;
        }

        .tracking-item:has(.form-check-input:checked) {
            border-color: var(--vlk-mint);
            background: var(--vlk-mint-soft);
        }

        .vlk-badge-auto {
            background: var(--vlk-lavender-soft) !important;
            color: #7A6FB5 !important;
            border: 1px solid var(--vlk-lavender);
            font-weight: 600;
            font-size: .7rem;
        }

        .vlk-theme .badge.bg-secondary-subtle {
            background: var(--vlk-lavender-soft) !important;
            color: #7A6FB5 !important;
            border: 1px solid var(--vlk-lavender);
        }

        .vlk-theme .badge {
            font-weight: 500;
        }

        .vlk-theme .form-label {
            margin-bottom: .35rem;
        }

        .vlk-theme .spinner-border.text-primary {
            color: var(--vlk-primary) !important;
        }

        .vlk-filter-card .form-label {
            color: var(--vlk-muted);
            font-size: .78rem;
        }

        .vlk-modal-body .form-control:focus,
        .vlk-modal-body .form-select:focus {
            border-color: var(--vlk-primary);
            box-shadow: 0 0 0 .22rem rgba(122, 164, 212, .22);
        }

        .vlk-theme .btn-close {
            filter: none;
        }

        .vlk-page-head {
            flex-wrap: wrap;
            gap: 12px;
        }

        .lock-overlay {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(42, 58, 77, 0.6);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lock-card {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            background: #fff;
            border-radius: 20px;
            border: 1px solid var(--vlk-border);
            box-shadow: 0 25px 60px -12px rgba(42, 58, 77, 0.35);
            animation: vlkFadeIn .4s ease both;
        }

        .lock-card .vlk-icon-badge {
            margin-bottom: 1rem;
        }

        .lock-card .form-control-lg {
            border-radius: 12px;
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        .lock-card .btn .btn-spinner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .filter-sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(42, 58, 77, 0.5);
            backdrop-filter: blur(4px);
            z-index: 100000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .filter-sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .filter-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            max-width: 100%;
            height: 100vh;
            background: var(--vlk-surface);
            z-index: 100001;
            box-shadow: -4px 0 20px rgba(42, 58, 77, 0.15);
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .filter-sidebar.show {
            right: 0;
        }

        .filter-sidebar-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--vlk-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, var(--vlk-primary-soft) 0%, var(--vlk-mint-soft) 100%);
        }

        .filter-sidebar-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .filter-sidebar-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid var(--vlk-border);
            background: var(--vlk-bg);
        }

        .filter-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--vlk-border);
        }

        .filter-section:last-child {
            border-bottom: none;
        }

        .filter-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--vlk-ink);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .column-toggles {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            max-height: 300px;
            overflow-y: auto;
            padding: 0.5rem;
            background: var(--vlk-bg);
            border-radius: 8px;
            border: 1px solid var(--vlk-border);
        }

        .column-toggles .form-check {
            margin-bottom: 0;
        }

        .column-toggles .form-check-label {
            font-size: 0.8rem;
            color: var(--vlk-muted);
            cursor: pointer;
        }

        .active-filters-list {
            background: var(--vlk-primary-soft);
            border: 1px solid var(--vlk-primary-light);
            border-radius: 8px;
            padding: 1rem;
            max-height: 200px;
            overflow-y: auto;
        }

        .filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--vlk-primary);
            color: #fff;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            margin-right: 0.5rem;
        }

        .filter-badge .remove-filter {
            cursor: pointer;
            font-weight: bold;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .filter-badge .remove-filter:hover {
            opacity: 1;
        }

        .filter-live-note {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #4E8E6F;
            background: var(--vlk-mint-soft);
            border: 1px solid var(--vlk-mint);
            border-radius: 20px;
            padding: 4px 10px;
            margin-bottom: 1rem;
        }

        .ks-icon-btn {
            position: relative;
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: .95rem;
        }

        .ks-icon-btn::after {
            display: none;
        }

        .ks-icon-btn.dropdown-toggle {
            width: auto;
            padding: 0 8px;
        }

        .ks-filter-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 999px;
            background: var(--vlk-coral);
            color: #fff;
            font-size: .62rem;
            font-weight: 700;
            line-height: 16px;
            text-align: center;
        }

        .vlk-dropdown-menu {
            border-radius: 12px;
            border: 1px solid var(--vlk-border);
            padding: 6px;
            min-width: 190px;
        }

        .vlk-dropdown-menu .dropdown-item {
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 500;
            padding: 8px 10px;
            display: flex;
            align-items: center;
        }

        .vlk-dropdown-menu .dropdown-item:hover,
        .vlk-dropdown-menu .dropdown-item:focus {
            background: var(--vlk-primary-soft);
            color: var(--vlk-primary-dark);
        }

        #calculatorModal .nav-tabs {
            border-bottom: 2px solid var(--vlk-border);
        }

        #calculatorModal .nav-link {
            border: none;
            color: var(--vlk-muted);
            font-weight: 600;
            padding: 0.75rem 1rem;
        }

        #calculatorModal .nav-link.active {
            color: var(--vlk-primary);
            border-bottom: 3px solid var(--vlk-primary);
            background: transparent;
        }

        @media (max-width: 576px) {
            .filter-sidebar {
                width: 100%;
                right: -100%;
            }

            .column-toggles {
                grid-template-columns: 1fr;
            }
        }

        .filter-sidebar-body::-webkit-scrollbar,
        .column-toggles::-webkit-scrollbar,
        .active-filters-list::-webkit-scrollbar {
            width: 6px;
        }

        .filter-sidebar-body::-webkit-scrollbar-thumb,
        .column-toggles::-webkit-scrollbar-thumb,
        .active-filters-list::-webkit-scrollbar-thumb {
            background: var(--vlk-primary-light);
            border-radius: 3px;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script>
        let manualTotalKotor = false;
        let manualTotalBersih = false;
        let failAttempts = 0;
        const MAX_FAILS = 3;
        let rawDataStore = [];
        let rawWeeksStore = [];
        let footerDataStore = {
            bulanan: {},
            tahunan: {}
        };
        let filterState = {
            sales: '',
            instruktur: '', 
            dateStart: '',
            dateEnd: '',
            status: '',
            rangeTotal: {
                min: null,
                max: null
            },
            rangeNett: {
                min: null,
                max: null
            },
            rangePax: {
                min: null,
                max: null
            },
            visibleColumns: ['no_faktur', 'no_invoice', 'materi', 'tanggal_training', 'perusahaan', 'nama_sales',
                'instruktur', 'harga', 'pax', 'total_penjualan_kotor', 'diskon', 'total_diskon', 'total_pa',
                'total_cashback', 'total_uang_saku', 'total_akomodasi', 'oleh_oleh', 'biaya_lain_lain',
                'entertainment', 'jenis_transport', 'biaya_transport', 'pengurangan_pph', 'exam',
                'total_penjualan_sales', 'PPN', 'PPH', 'jumlah_pembayaran', 'tanggal_pembayaran', 'biaya_admin',
                'total_piutang', 'tanggal_mulai', 'tanggal_selesai'
            ]
        };

        const COLUMN_DEFS = {
            'no_faktur': {
                label: 'No Faktur',
                type: 'text',
                dataProp: 'no_faktur'
            },
            'no_invoice': {
                label: 'No Invoice',
                type: 'text',
                dataProp: 'no_invoice'
            },
            'materi': {
                label: 'Materi',
                type: 'text',
                dataProp: 'materi'
            },
            'tanggal_training': {
                label: 'Tanggal',
                type: 'text',
                dataProp: 'tanggal_training'
            },
            'perusahaan': {
                label: 'Perusahaan',
                type: 'text',
                dataProp: 'perusahaan'
            },
            'nama_sales': {
                label: 'Sales',
                type: 'text',
                dataProp: 'nama_sales'
            },
            'instruktur': {
                label: 'Instruktur',
                type: 'text',
                dataProp: 'instruktur'
            },
            'harga': {
                label: 'Harga',
                type: 'currency',
                dataProp: 'harga'
            },
            'pax': {
                label: 'Pax',
                type: 'number',
                dataProp: 'pax'
            },
            'total_penjualan_kotor': {
                label: 'Total Penjualan Kotor',
                type: 'currency',
                dataProp: 'total_penjualan_kotor'
            },
            'diskon': {
                label: 'Diskon/PA',
                type: 'currency',
                dataProp: 'diskon'
            },
            'total_diskon': {
                label: 'Total Diskon',
                type: 'currency',
                dataProp: 'total_diskon'
            },
            'total_pa': {
                label: 'Total PA',
                type: 'currency',
                dataProp: 'total_pa'
            },
            'total_cashback': {
                label: 'Cashback',
                type: 'currency',
                dataProp: 'total_cashback'
            },
            'total_uang_saku': {
                label: 'Uang Saku',
                type: 'currency',
                dataProp: 'total_uang_saku'
            },
            'total_akomodasi': {
                label: 'Akomodasi',
                type: 'currency',
                dataProp: 'total_akomodasi'
            },
            'oleh_oleh': {
                label: 'Oleh-Oleh Peserta',
                type: 'currency',
                dataProp: 'oleh_oleh'
            },
            'biaya_lain_lain': {
                label: 'Biaya Lain-Lain',
                type: 'currency',
                dataProp: 'biaya_lain_lain'
            },
            'entertainment': {
                label: 'Entertainment',
                type: 'currency',
                dataProp: 'entertainment'
            },
            'jenis_transport': {
                label: 'Jenis Transport',
                type: 'text',
                dataProp: 'jenis_transport'
            },
            'biaya_transport': {
                label: 'Biaya Transport',
                type: 'currency',
                dataProp: 'biaya_transport'
            },
            'pengurangan_pph': {
                label: 'Pengurangan PPH',
                type: 'currency',
                dataProp: 'pengurangan_pph'
            },
            'exam': {
                label: 'Exam',
                type: 'text',
                dataProp: 'exam'
            },
            'total_penjualan_sales': {
                label: 'Total Penjualan Sales (Bersih)',
                type: 'currency',
                dataProp: 'total_penjualan_sales'
            },
            'PPN': {
                label: 'PPN',
                type: 'currency',
                dataProp: 'PPN'
            },
            'PPH': {
                label: 'PPH',
                type: 'currency',
                dataProp: 'PPH'
            },
            'jumlah_pembayaran': {
                label: 'Jumlah Pembayaran',
                type: 'currency',
                dataProp: 'jumlah_pembayaran'
            },
            'tanggal_pembayaran': {
                label: 'Tanggal Pembayaran',
                type: 'text',
                dataProp: 'tanggal_pembayaran'
            },
            'biaya_admin': {
                label: 'Biaya Admin',
                type: 'currency',
                dataProp: 'biaya_admin'
            },
            'total_piutang': {
                label: 'Total Piutang',
                type: 'currency',
                dataProp: 'total_piutang'
            },
            'tanggal_mulai': {
                label: 'Tanggal Mulai',
                type: 'text',
                dataProp: 'tanggal_mulai'
            },
            'tanggal_selesai': {
                label: 'Tanggal Selesai',
                type: 'text',
                dataProp: 'tanggal_selesai'
            }
        };

        $(document).ready(function() {
            checkAndInitLock();
        });

        function checkAndInitLock() {
            $('#unlockLoadingState').removeClass('d-none');
            $('#unlockForm, #fallbackForm, #failCounter').addClass('d-none');
            $('#lockScreenSubtitle').text('Memeriksa status keamanan...');
            $.ajax({
                url: '/office/approval-pendapatan/lock-status',
                type: 'GET',
                success: function(res) {
                    $('#unlockLoadingState').addClass('d-none');
                    if (!res.has_password) {
                        $('#lockScreenOverlay').addClass('d-none');
                        new bootstrap.Modal(document.getElementById('setupModal')).show();
                        return;
                    }
                    if (res.needs_accounting_setup) {
                        $('#lockScreenOverlay').addClass('d-none');
                        new bootstrap.Modal(document.getElementById('setupAccountingModal')).show();
                        return;
                    }
                    if (res.is_locked) {
                        $('#lockScreenOverlay').removeClass('d-none');
                        $('#unlockForm').removeClass('d-none');
                        $('#lockScreenSubtitle').text('Masukkan password untuk melanjutkan');
                        setTimeout(function() {
                            $('#unlockPassword').focus();
                        }, 300);
                    } else {
                        $('#lockScreenOverlay').addClass('d-none');
                        updateCurrentPeriod();
                        loadTable();
                    }
                },
                error: function() {
                    $('#unlockLoadingState').addClass('d-none');
                    $('#lockScreenOverlay').removeClass('d-none');
                    $('#unlockForm').removeClass('d-none');
                    $('#lockScreenSubtitle').text('Masukkan password untuk melanjutkan');
                    setTimeout(function() {
                        $('#unlockPassword').focus();
                    }, 300);
                }
            });
        }

        function showFallbackLogin() {
            $('#unlockForm').addClass('d-none');
            $('#fallbackForm').removeClass('d-none');
            $('#fallbackPassword').focus();
        }

        function showApprovalLogin() {
            $('#fallbackForm').addClass('d-none');
            $('#unlockForm').removeClass('d-none');
            $('#unlockPassword').focus();
        }

        function setUnlockLoading(type, isLoading) {
            let btnId = type === 'approval' ? '#btnUnlockApproval' : '#btnUnlockLogin';
            let inputId = type === 'approval' ? '#unlockPassword' : '#fallbackPassword';
            let $btn = $(btnId);
            $btn.prop('disabled', isLoading);
            $btn.find('.btn-label').toggleClass('d-none', isLoading);
            $btn.find('.btn-spinner').toggleClass('d-none', !isLoading);
            $(inputId).prop('disabled', isLoading);
        }

        function attemptUnlock(type) {
            let password = type === 'approval' ? $('#unlockPassword').val() : $('#fallbackPassword').val();
            let errorEl = type === 'approval' ? '#unlockError' : '#fallbackError';
            if (!password) {
                $(errorEl).text('Password wajib diisi.').removeClass('d-none');
                return;
            }
            $(errorEl).addClass('d-none');
            setUnlockLoading(type, true);
            $.ajax({
                url: '/office/approval-pendapatan/unlock',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    password: password,
                    type: type
                },
                success: function(res) {
                    if (res.success) {
                        failAttempts = 0;
                        $('#failCount').text(0);
                        $('#failCounter').addClass('d-none');
                        $('#unlockError').addClass('d-none');
                        $('#fallbackError').addClass('d-none');
                        $('#lockScreenOverlay').addClass('d-none');
                        setUnlockLoading(type, false);
                        updateCurrentPeriod();
                        loadTable();
                    } else {
                        setUnlockLoading(type, false);
                    }
                },
                error: function(xhr) {
                    setUnlockLoading(type, false);
                    failAttempts++;
                    let msg = xhr.responseJSON?.message || 'Password salah.';
                    $(errorEl).text(msg).removeClass('d-none');
                    $('#failCounter').removeClass('d-none');
                    $('#failCount').text(failAttempts);
                    if (failAttempts >= MAX_FAILS) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Percobaan Gagal Berulang',
                            text: 'Anda telah gagal 3 kali. Silakan gunakan password login atau hubungi administrator.',
                            confirmButtonColor: '#7AA4D4',
                            showCancelButton: true,
                            cancelButtonText: 'Coba Lagi',
                            confirmButtonText: 'Gunakan Password Login'
                        }).then((result) => {
                            if (result.isConfirmed) showFallbackLogin();
                            failAttempts = 0;
                            $('#failCount').text(0);
                        });
                    }
                }
            });
        }

        function submitSetup() {
            let loginPass = $('#setupLoginPass').val();
            let newPass = $('#setupNewPass').val();
            let confirmPass = $('#setupConfirmPass').val();
            $('#setupLoginError, #setupNewError').addClass('d-none');
            if (!loginPass) {
                $('#setupLoginError').text('Password login wajib diisi.').removeClass('d-none');
                return;
            }
            if (!newPass || newPass.length < 4) {
                $('#setupNewError').text('Password baru minimal 4 karakter.').removeClass('d-none');
                return;
            }
            if (newPass !== confirmPass) {
                $('#setupNewError').text('Konfirmasi password tidak cocok.').removeClass('d-none');
                return;
            }
            $.ajax({
                url: '/office/approval-pendapatan/setup-lock',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    login_password: loginPass,
                    new_password: newPass,
                    new_password_confirmation: confirmPass
                },
                success: function(res) {
                    bootstrap.Modal.getInstance(document.getElementById('setupModal')).hide();
                    $('#lockScreenOverlay').addClass('d-none');
                    updateCurrentPeriod();
                    loadTable();
                    showAlert('success', 'Password approval berhasil dibuat!');
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || {};
                    if (errors.login_password) $('#setupLoginError').text(errors.login_password[0]).removeClass(
                        'd-none');
                    if (errors.new_password) $('#setupNewError').text(errors.new_password[0]).removeClass(
                        'd-none');
                }
            });
        }

        function submitAccountingSetup() {
            let loginPass = $('#accSetupLoginPass').val();
            let newPass = $('#accSetupNewPass').val();
            let confirmPass = $('#accSetupConfirmPass').val();
            $('#accSetupLoginError, #accSetupNewError').addClass('d-none');
            if (!loginPass) {
                $('#accSetupLoginError').text('Password login wajib diisi.').removeClass('d-none');
                return;
            }
            if (!newPass || newPass.length < 4) {
                $('#accSetupNewError').text('Password Accounting minimal 4 karakter.').removeClass('d-none');
                return;
            }
            if (newPass !== confirmPass) {
                $('#accSetupNewError').text('Konfirmasi password tidak cocok.').removeClass('d-none');
                return;
            }
            $.ajax({
                url: '/office/approval-pendapatan/setup-accounting-password',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    login_password: loginPass,
                    accounting_password: newPass,
                    accounting_password_confirmation: confirmPass
                },
                success: function(res) {
                    bootstrap.Modal.getInstance(document.getElementById('setupAccountingModal')).hide();
                    Swal.fire('Berhasil!', res.message, 'success').then(() => {
                        checkAndInitLock();
                    });
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || {};
                    if (errors.login_password) $('#accSetupLoginError').text(errors.login_password[0])
                        .removeClass('d-none');
                    if (errors.accounting_password) $('#accSetupNewError').text(errors.accounting_password[0])
                        .removeClass('d-none');
                    if (xhr.responseJSON?.message) Swal.fire('Gagal', xhr.responseJSON.message, 'error');
                }
            });
        }

        function submitChangePassword() {
            let current = $('#chgCurrentPass').val();
            let newP = $('#chgNewPass').val();
            let confirm = $('#chgConfirmPass').val();
            $('#chgCurrentError, #chgNewError').addClass('d-none');
            if (!current) {
                $('#chgCurrentError').text('Wajib diisi.').removeClass('d-none');
                return;
            }
            if (!newP || newP.length < 4) {
                $('#chgNewError').text('Minimal 4 karakter.').removeClass('d-none');
                return;
            }
            if (newP !== confirm) {
                $('#chgNewError').text('Konfirmasi tidak cocok.').removeClass('d-none');
                return;
            }
            $.ajax({
                url: '/office/approval-pendapatan/change-lock-password',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    current_password: current,
                    new_password: newP,
                    new_password_confirmation: confirm
                },
                success: function(res) {
                    $('#changePassModal').modal('hide');
                    showAlert('success', 'Password approval berhasil diubah!');
                    $('#chgCurrentPass, #chgNewPass, #chgConfirmPass').val('');
                    setTimeout(function() {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        $('body').css('overflow', '');
                        $('body').css('padding-right', '');
                    }, 300);
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || {};
                    if (errors.current_password) $('#chgCurrentError').text(errors.current_password[0])
                        .removeClass('d-none');
                }
            });
        }

        $('#month, #year').on('change', function() {
            updateCurrentPeriod();
            loadTable();
        });

        function updateCurrentPeriod() {
            let bulan = $('#month option:selected').text();
            let tahun = $('#year').val();
            if (bulan && tahun && bulan !== 'Pilih Bulan') {
                $('#current-period').text('Periode: ' + bulan + ' ' + tahun);
            }
        }

        function formatRupiah(value) {
            if (!value || value === 'belum tervalidasi' || value === 'kosong' || isNaN(value)) return value;
            let num = parseFloat(value);
            if (num === 0) return '<span class="num-zero">0</span>';
            return 'Rp ' + num.toLocaleString('id-ID');
        }

        function parseNumber(value) {
            if (!value || value === 'belum tervalidasi' || value === 'kosong') return 0;
            let cleaned = String(value).replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.\-]/g, '');
            return parseFloat(cleaned) || 0;
        }

        function formatCurrency(value) {
            let num = parseFloat(value) || 0;
            if (num <= 0) return '';
            return Math.round(num).toLocaleString('id-ID');
        }

        function setCurrencyValue(selector, value) {
            let num = parseFloat(value) || 0;
            if (num > 0) {
                $(selector).val(Math.round(num).toLocaleString('id-ID'));
            } else {
                $(selector).val('');
            }
        }

        function getCurrencyValue(selector) {
            let val = $(selector).val();
            if (!val) return 0;
            return parseInt(String(val).replace(/\./g, '').replace(/[^0-9]/g, '')) || 0;
        }

        $(document).on('input', '.currency-input', function(e) {
            let el = this;
            let raw = el.value.replace(/\./g, '').replace(/[^0-9]/g, '');
            if (raw === '') {
                el.value = '';
                return;
            }
            let num = parseInt(raw);
            let formatted = num.toLocaleString('id-ID');
            if (el.value !== formatted) {
                let cursorPos = el.selectionStart;
                let oldLength = el.value.length;
                el.value = formatted;
                let newLength = el.value.length;
                let newPos = Math.max(0, cursorPos + (newLength - oldLength));
                el.setSelectionRange(newPos, newPos);
            }
        });
        $(document).on('blur', '.currency-input', function() {
            let num = parseNumber($(this).val());
            if (num > 0) {
                $(this).val(Math.round(num).toLocaleString('id-ID'));
            }
        });

        $('#transportasi_select').on('change', function() {
            if ($(this).val() === 'Lainnya') {
                $('#transportasi_manual').removeClass('d-none').focus();
            } else {
                $('#transportasi_manual').addClass('d-none').val('');
            }
        });
        $('#total').on('input', function() {
            manualTotalKotor = true;
            calculateTotalPenjualanSales();
        });
        $('#total_penjualan_sales').on('input', function() {
            manualTotalBersih = true;
        });
        $('.input-calc').on('input change', function() {
            calculateTotal();
            calculateTotalPenjualanSales();
        });
        $(document).on('input change', '.input-calc', function() {
            calculateTotal();
            calculateTotalPenjualanSales();
        });

        function calculateTotal() {
            let harga = parseNumber($('#harga').val());
            let pax = parseNumber($('#pax').val());
            let total = harga * pax;
            if (!manualTotalKotor && total > 0) {
                $('#total').val(formatCurrency(total));
            }
        }

        function calculateTotalPenjualanSales() {
            let total = parseNumber($('#total').val());
            let deductions = parseNumber($('#diskon').val()) + parseNumber($('#total_diskon').val()) + parseNumber($(
                    '#total_pa').val()) + parseNumber($('#total_cashback').val()) + parseNumber($('#total_uang_saku')
                    .val()) + parseNumber($('#total_akomodasi').val()) + parseNumber($('#biaya_transport').val()) +
                parseNumber($(
                    '#oleh_oleh').val()) + parseNumber($('#biaya_lain_lain').val()) + parseNumber($('#entertainment')
                    .val()) + parseNumber($('#exam_value').val()) + parseNumber($('#pengurangan_pph').val());
            let totalPenjualanSales = Math.max(0, total - deductions);
            if (!manualTotalBersih) {
                $('#total_penjualan_sales').val(formatCurrency(totalPenjualanSales));
            }
        }

        function goStep(n) {
            $('.step-content').addClass('d-none');
            $('#step-' + n).removeClass('d-none');
            $('.step-pill').each(function() {
                let s = parseInt($(this).data('step'));
                $(this).removeClass('active done');
                if (s === n) $(this).addClass('active');
                if (s < n) $(this).addClass('done').find('.step-num').html(
                    '<i class="bi bi-check-lg" style="font-size:.7rem"></i>');
                if (s >= n) $(this).find('.step-num').text(s);
            });
        }

        function loadTable() {
            let bulan = $('#month').val();
            let tahun = $('#year').val();
            if (!bulan || !tahun) {
                $('#weekly-container').html(
                    `<div class="alert alert-warning">Silakan pilih periode bulan dan tahun terlebih dahulu.</div>`);
                return;
            }
            $('#loading-spinner').removeClass('d-none');
            $('#weekly-container').html('');
            rawDataStore = [];
            rawWeeksStore = [];
            footerDataStore = {
                bulanan: {},
                tahunan: {}
            };
            $.ajax({
                url: `/office/approval-pendapatan/get/${tahun}/${bulan}`,
                type: "GET",
                success: function(response) {
                    $('#loading-spinner').addClass('d-none');
                    let monthDataList = response.data || [];
                    footerDataStore.bulanan = response.footer_bulanan || {};
                    footerDataStore.tahunan = response.footer_tahunan || {};
                    if (monthDataList.length === 0) {
                        $('#weekly-container').html(
                            `<div class="card shadow-sm border-0 vlk-card"><div class="card-body text-center py-5"><i class="bi bi-inbox fs-1 text-muted mb-3"></i><p class="text-muted fs-5 mb-0">Tidak ada data pada periode ini</p></div></div>`
                        );
                        return;
                    }
                    moment.locale('id');
                    monthDataList.forEach(md => {
                        md.weeksData.forEach(wd => {
                            rawWeeksStore.push({
                                week_key: 'week_' + wd.week_number,
                                week_number: wd.week_number,
                                start: wd.start,
                                end: wd.end
                            });
                            wd.data.forEach(item => {
                                item.week_key = 'week_' + wd.week_number;
                                item.week_start = wd.start;
                                item.week_end = wd.end;
                                rawDataStore.push(item);
                            });
                        });
                    });
                    applyFiltersToTable();
                },
                error: function(xhr) {
                    $('#loading-spinner').addClass('d-none');
                    let msg = xhr.responseJSON?.error || 'Gagal memuat data';
                    $('#weekly-container').html(`<div class="alert alert-danger">${escapeHtml(msg)}</div>`);
                }
            });
        }

        function bindSyncScroll() {
            let $wrappers = $('.table-scroll-sync');
            let isSyncing = false;
            $wrappers.off('scroll.sync').on('scroll.sync', function() {
                if (isSyncing) return;
                isSyncing = true;
                let scrollLeft = this.scrollLeft;
                $wrappers.not(this).each(function() {
                    this.scrollLeft = scrollLeft;
                });
                isSyncing = false;
            });
        }

        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let item = JSON.parse(decodeURIComponent($(this).data('item')));
            manualTotalKotor = false;
            manualTotalBersih = false;
            $('#update_id').val(item.id_rkm);
            $('#modal-subtitle').text((item.no_faktur ?? '') + (item.no_invoice ? ' · ' + item.no_invoice : ''));
            $('#no_faktur').val(item.no_faktur ?? '');
            $('#no_invoice').val(item.no_invoice ?? '');
            $('#materi').val(item.materi_id ?? '');
            $('#perusahaan').val(item.perusahaan_id ?? '');
            $('#tanggal_mulai').val(item.tanggal_mulai ?? '');
            $('#tanggal_selesai').val(item.tanggal_selesai ?? '');
            $('#nama_sales').val(item.nama_sales ?? '');
            $('#instruktur').val(item.instruktur ?? '');
            $('#pic').val(item.pic ?? '');
            $('#regist').val(item.regist ?? '');
            $('#tanggal_pembayaran').val(item.tanggal_pembayaran ?? '');
            setCurrencyValue('#harga', item.harga);
            setCurrencyValue('#total', item.total_penjualan_kotor);
            setCurrencyValue('#diskon', item.diskon);
            setCurrencyValue('#total_diskon', item.total_diskon);
            setCurrencyValue('#total_pa', item.total_pa);
            setCurrencyValue('#total_cashback', item.total_cashback);
            setCurrencyValue('#total_uang_saku', item.total_uang_saku);
            setCurrencyValue('#total_akomodasi', item.total_akomodasi);
            setCurrencyValue('#oleh_oleh', item.oleh_oleh);
            setCurrencyValue('#biaya_lain_lain', item.biaya_lain_lain);
            setCurrencyValue('#entertainment', item.entertainment);
            setCurrencyValue('#biaya_transport', item.biaya_transport);
            setCurrencyValue('#pengurangan_pph', item.pengurangan_pph);
            setCurrencyValue('#exam_value', item.exam_value);
            setCurrencyValue('#PPN', item.PPN);
            setCurrencyValue('#PPH', item.PPH);
            setCurrencyValue('#jumlah_pembayaran', item.jumlah_pembayaran);
            setCurrencyValue('#biaya_admin', item.biaya_admin);
            setCurrencyValue('#total_penjualan_sales', item.total_penjualan_sales);
            $('#pax').val(item.pax ?? '');
            $('.tracking-check').prop('checked', false);
            if (item.tracking) {
                if (item.tracking.faktur_pajak) $('#trk_faktur_pajak').prop('checked', true);
                if (item.tracking.dokumen_tambahan) $('#trk_dokumen_tambahan').prop('checked', true);
                if (item.tracking.konfir_cs) $('#trk_konfir_cs').prop('checked', true);
                if (item.tracking.tracking_dokumen) $('#trk_tracking_dokumen').prop('checked', true);
                if (item.tracking.no_resi) $('#trk_no_resi').prop('checked', true);
                if (item.tracking.pembayaran) $('#trk_pembayaran').prop('checked', true);
                $('#trk_status_resi').val(item.tracking.status_resi ?? '');
                $('#trk_status_pic').val(item.tracking.status_pic ?? '');
            }
            if (['Pesawat', 'Kereta', 'Bus', 'Mobil', 'Travel', 'Lainnya'].includes(item.jenis_transport)) {
                $('#transportasi_select').val(item.jenis_transport);
                if (item.jenis_transport === 'Lainnya') {
                    $('#transportasi_manual').removeClass('d-none').val(item.jenis_transport);
                } else {
                    $('#transportasi_manual').addClass('d-none').val('');
                }
            } else {
                $('#transportasi_select').val('');
                $('#transportasi_manual').removeClass('d-none').val(item.jenis_transport ?? '');
            }
            calculateTotal();
            calculateTotalPenjualanSales();
            goStep(1);
            new bootstrap.Modal(document.getElementById('updateModal')).show();
        });

        $('#formUpdate').submit(function(e) {
            e.preventDefault();
            let id = $('#update_id').val();
            let jenisTransport = $('#transportasi_select').val() === 'Lainnya' ? $('#transportasi_manual').val() :
                $('#transportasi_select').val();
            let $btn = $('#btnSimpan');
            $.ajax({
                url: `/office/approval-pendapatan/update/${id}`,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    no_faktur: $('#no_faktur').val(),
                    no_invoice: $('#no_invoice').val(),
                    harga: getCurrencyValue('#harga'),
                    pax: $('#pax').val() || 0,
                    diskon: getCurrencyValue('#diskon'),
                    total_diskon: getCurrencyValue('#total_diskon'),
                    total_pa: getCurrencyValue('#total_pa'),
                    total_cashback: getCurrencyValue('#total_cashback'),
                    total_uang_saku: getCurrencyValue('#total_uang_saku'),
                    total_akomodasi: getCurrencyValue('#total_akomodasi'),
                    jenis_transport: jenisTransport,
                    biaya_transport: getCurrencyValue('#biaya_transport'),
                    oleh_oleh: getCurrencyValue('#oleh_oleh'),
                    biaya_lain_lain: getCurrencyValue('#biaya_lain_lain'),
                    entertainment: getCurrencyValue('#entertainment'),
                    exam: getCurrencyValue('#exam_value'),
                    total_penjualan_sales: getCurrencyValue('#total_penjualan_sales'),
                    PPN: getCurrencyValue('#PPN'),
                    PPH: getCurrencyValue('#PPH'),
                    pengurangan_pph: getCurrencyValue('#pengurangan_pph'),
                    jumlah_pembayaran: getCurrencyValue('#jumlah_pembayaran'),
                    tanggal_pembayaran: $('#tanggal_pembayaran').val(),
                    biaya_admin: getCurrencyValue('#biaya_admin'),
                    materi: $('#materi').val(),
                    perusahaan: $('#perusahaan').val(),
                    tanggal_mulai: $('#tanggal_mulai').val(),
                    tanggal_selesai: $('#tanggal_selesai').val(),
                    pic: $('#pic').val(),
                    regist: $('#regist').val(),
                    faktur_pajak: $('#trk_faktur_pajak').is(':checked') ? 1 : 0,
                    dokumen_tambahan: $('#trk_dokumen_tambahan').is(':checked') ? 1 : 0,
                    konfir_cs: $('#trk_konfir_cs').is(':checked') ? 1 : 0,
                    tracking_dokumen: $('#trk_tracking_dokumen').is(':checked') ? 1 : 0,
                    no_resi: $('#trk_no_resi').is(':checked') ? 1 : 0,
                    konfir_pic: $('#trk_konfir_pic').is(':checked') ? 1 : 0,
                    pembayaran: $('#trk_pembayaran').is(':checked') ? 1 : 0,
                    status_resi: $('#trk_status_resi').val(),
                    status_pic: $('#trk_status_pic').val(),
                    total: getCurrencyValue('#total'),
                    total_penjualan_kotor: getCurrencyValue('#total')
                },
                beforeSend: function() {
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
                },
                success: function(response) {
                    if (response.success) {
                        $('#updateModal').modal('hide');
                        loadTable();
                        showAlert('success', 'Data berhasil diupdate!');
                    } else {
                        showAlert('danger', response.message || 'Gagal menyimpan data.');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal menyimpan data. Silakan coba lagi.';
                    showAlert('danger', msg);
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-2"></i>Simpan');
                }
            });
        });

        function showAlert(type, message) {
            let alertHtml =
                `<div class="position-fixed top-0 end-0 p-3" style="z-index:9999"><div class="alert alert-${type} alert-dismissible fade show" role="alert">${escapeHtml(message)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>`;
            $('body').append(alertHtml);
            setTimeout(() => $('.alert').alert('close'), 3000);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function toggleFilterSidebar() {
            const sidebar = document.getElementById('filterSidebar');
            const overlay = document.getElementById('filterSidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            if (sidebar.classList.contains('show')) {
                loadFilterValues();
            }
        }
        document.getElementById('filterSidebarOverlay').addEventListener('click', toggleFilterSidebar);

        function loadFilterValues() {
            $('#filterSales').val(filterState.sales);
            $('#filterInstruktur').val(filterState.instruktur);
            $('#filterDateStart').val(filterState.dateStart);
            $('#filterDateEnd').val(filterState.dateEnd);
            $('#filterStatus').val(filterState.status);
            $('#rangeTotalMin').val(filterState.rangeTotal.min || '');
            $('#rangeTotalMax').val(filterState.rangeTotal.max || '');
            $('#rangeNettMin').val(filterState.rangeNett.min || '');
            $('#rangeNettMax').val(filterState.rangeNett.max || '');
            $('#rangePaxMin').val(filterState.rangePax.min || '');
            $('#rangePaxMax').val(filterState.rangePax.max || '');
            $('.column-toggles input[type="checkbox"]').each(function() {
                const colName = $(this).val();
                $(this).prop('checked', filterState.visibleColumns.includes(colName));
            });
            updateActiveFiltersPreview();
        }

        function readFilterFormIntoState() {
            filterState.sales = $('#filterSales').val().toLowerCase();
            filterState.instruktur = ($('#filterInstruktur').val() || '').toLowerCase().trim(); 
            filterState.dateStart = $('#filterDateStart').val();
            filterState.dateEnd = $('#filterDateEnd').val();
            filterState.status = $('#filterStatus').val();
            filterState.rangeTotal.min = $('#rangeTotalMin').val() ? parseFloat($('#rangeTotalMin').val()) : null;
            filterState.rangeTotal.max = $('#rangeTotalMax').val() ? parseFloat($('#rangeTotalMax').val()) : null;
            filterState.rangeNett.min = $('#rangeNettMin').val() ? parseFloat($('#rangeNettMin').val()) : null;
            filterState.rangeNett.max = $('#rangeNettMax').val() ? parseFloat($('#rangeNettMax').val()) : null;
            filterState.rangePax.min = $('#rangePaxMin').val() ? parseFloat($('#rangePaxMin').val()) : null;
            filterState.rangePax.max = $('#rangePaxMax').val() ? parseFloat($('#rangePaxMax').val()) : null;
            filterState.visibleColumns = [];
            $('.column-toggles input[type="checkbox"]:checked').each(function() {
                filterState.visibleColumns.push($(this).val());
            });
        }

        function applyFiltersLive() {
            readFilterFormIntoState();
            applyFiltersToTable();
            updateActiveFiltersPreview();
            updateFilterBadge();
        }

        function applyAllFilters() {
            applyFiltersLive();
            toggleFilterSidebar();
        }

        function resetAllFilters() {
            filterState = {
                sales: '',
                instruktur: '',
                dateStart: '',
                dateEnd: '',
                status: '',
                rangeTotal: {
                    min: null,
                    max: null
                },
                rangeNett: {
                    min: null,
                    max: null
                },
                rangePax: {
                    min: null,
                    max: null
                },
                visibleColumns: ['no_faktur', 'no_invoice', 'materi', 'tanggal_training', 'perusahaan', 'nama_sales',
                    'instruktur', 'harga', 'pax', 'total_penjualan_kotor', 'diskon', 'total_diskon', 'total_pa',
                    'total_cashback', 'total_uang_saku', 'total_akomodasi', 'oleh_oleh', 'biaya_lain_lain',
                    'entertainment', 'jenis_transport', 'biaya_transport', 'pengurangan_pph', 'exam',
                    'total_penjualan_sales', 'PPN', 'PPH', 'jumlah_pembayaran', 'tanggal_pembayaran', 'biaya_admin',
                    'total_piutang', 'tanggal_mulai', 'tanggal_selesai'
                ]
            };
            loadFilterValues();
            applyFiltersToTable();
            updateFilterBadge();
        }

        function updateActiveFiltersPreview() {
            let html = '';
            let count = 0;
            if (filterState.sales) {
                html +=
                    `<span class="filter-badge">Sales: "${filterState.sales}" <span class="remove-filter" onclick="removeFilter('sales')">×</span></span>`;
                count++;
            }
            if (filterState.instruktur) {
                html +=
                    `<span class="filter-badge">Instruktur: "${filterState.instruktur}" <span class="remove-filter" onclick="removeFilter('instruktur')">×</span></span>`;
                count++;
            }
            if (filterState.dateStart || filterState.dateEnd) {
                html +=
                    `<span class="filter-badge">Tanggal: ${filterState.dateStart || '...'} - ${filterState.dateEnd || '...'} <span class="remove-filter" onclick="removeFilter('date')">×</span></span>`;
                count++;
            }
            if (filterState.status) {
                html +=
                    `<span class="filter-badge">Status: ${filterState.status} <span class="remove-filter" onclick="removeFilter('status')">×</span></span>`;
                count++;
            }
            if (filterState.rangeTotal.min !== null || filterState.rangeTotal.max !== null) {
                html +=
                    `<span class="filter-badge">Total Kotor Range <span class="remove-filter" onclick="removeFilter('rangeTotal')">×</span></span>`;
                count++;
            }
            if (filterState.rangeNett.min !== null || filterState.rangeNett.max !== null) {
                html +=
                    `<span class="filter-badge">Total Bersih Range <span class="remove-filter" onclick="removeFilter('rangeNett')">×</span></span>`;
                count++;
            }
            if (filterState.rangePax.min !== null || filterState.rangePax.max !== null) {
                html +=
                    `<span class="filter-badge">Pax Range <span class="remove-filter" onclick="removeFilter('rangePax')">×</span></span>`;
                count++;
            }
            if (html === '') {
                html = '<p class="text-muted small mb-0">Belum ada filter aktif</p>';
            }
            $('#activeFiltersPreview').html(html);
        }

        function removeFilter(type) {
            switch (type) {
                case 'sales':
                    filterState.sales = '';
                    $('#filterSales').val('');
                    break;
                case 'instruktur':
                    filterState.instruktur = '';
                    $('#filterInstruktur').val('');
                    break;
                case 'date':
                    filterState.dateStart = '';
                    filterState.dateEnd = '';
                    $('#filterDateStart').val('');
                    $('#filterDateEnd').val('');
                    break;
                case 'status':
                    filterState.status = '';
                    $('#filterStatus').val('');
                    break;
                case 'rangeTotal':
                    filterState.rangeTotal = {
                        min: null,
                        max: null
                    };
                    $('#rangeTotalMin').val('');
                    $('#rangeTotalMax').val('');
                    break;
                case 'rangeNett':
                    filterState.rangeNett = {
                        min: null,
                        max: null
                    };
                    $('#rangeNettMin').val('');
                    $('#rangeNettMax').val('');
                    break;
                case 'rangePax':
                    filterState.rangePax = {
                        min: null,
                        max: null
                    };
                    $('#rangePaxMin').val('');
                    $('#rangePaxMax').val('');
                    break;
            }
            updateActiveFiltersPreview();
            applyFiltersToTable();
            updateFilterBadge();
        }

        function updateFilterBadge() {
            let count = 0;
            if (filterState.sales) count++;
            if (filterState.instruktur) count++;
            if (filterState.dateStart || filterState.dateEnd) count++;
            if (filterState.status) count++;
            if (filterState.rangeTotal.min !== null || filterState.rangeTotal.max !== null) count++;
            if (filterState.rangeNett.min !== null || filterState.rangeNett.max !== null) count++;
            if (filterState.rangePax.min !== null || filterState.rangePax.max !== null) count++;
            $('#filterCountBadge').text(count).toggle(count > 0);
        }

        $(document).on('input', '#filterSales, #filterInstruktur', debounce(applyFiltersLive, 350));
        $(document).on('change', '#filterDateStart, #filterDateEnd, #filterStatus', applyFiltersLive);
        $(document).on('input', '#rangeTotalMin, #rangeTotalMax, #rangeNettMin, #rangeNettMax, #rangePaxMin, #rangePaxMax',
            debounce(applyFiltersLive, 350));
        $(document).on('change', '.column-toggles input[type="checkbox"]', applyFiltersLive);

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function applyFiltersToTable() {
            let filteredData = rawDataStore.filter(item => {
                if (filterState.sales && !(item.nama_sales || '').toLowerCase().includes(filterState.sales)) {
                    return false;
                }
                if (filterState.instruktur && !(item.instruktur || '').toLowerCase().includes(filterState.instruktur)) {
                    return false;
                }
                if (filterState.dateStart && item.tanggal_mulai && item.tanggal_mulai < filterState.dateStart)
                    return false;
                if (filterState.dateEnd && item.tanggal_selesai && item.tanggal_selesai > filterState.dateEnd)
                    return false;
                if (filterState.status && item.valid !== filterState.status) return false;
                let totalKotor = parseFloat(item.total_penjualan_kotor) || 0;
                if (filterState.rangeTotal.min !== null && totalKotor < filterState.rangeTotal.min) return false;
                if (filterState.rangeTotal.max !== null && totalKotor > filterState.rangeTotal.max) return false;
                let totalBersih = parseFloat(item.total_penjualan_sales) || 0;
                if (filterState.rangeNett.min !== null && totalBersih < filterState.rangeNett.min) return false;
                if (filterState.rangeNett.max !== null && totalBersih > filterState.rangeNett.max) return false;
                let pax = parseInt(item.pax) || 0;
                if (filterState.rangePax.min !== null && pax < filterState.rangePax.min) return false;
                if (filterState.rangePax.max !== null && pax > filterState.rangePax.max) return false;
                return true;
            });
            renderFilteredTable(filteredData);
        }

        function renderFilteredTable(filteredData) {
            let container = $('#weekly-container');
            container.empty();
            if (rawWeeksStore.length === 0) {
                container.html(
                    `<div class="card shadow-sm border-0 vlk-card"><div class="card-body text-center py-5"><i class="bi bi-inbox fs-1 text-muted mb-3"></i><p class="text-muted fs-5 mb-0">Tidak ada data dalam periode ini</p></div></div>`
                );
                return;
            }
            moment.locale('id');
            let visibleCols = filterState.visibleColumns;
            let totalWeeks = rawWeeksStore.length;
            rawWeeksStore.forEach((weekInfo, idx) => {
                let weekKey = weekInfo.week_key;
                let weekItems = filteredData.filter(item => item.week_key === weekKey);
                let isLastWeek = (idx + 1) === totalWeeks;
                let startOfWeek = moment(weekInfo.start);
                let endOfWeek = startOfWeek.clone().add(4, 'days');
                let rowsHtml = '';
                if (weekItems.length === 0) {
                    let colSpan = visibleCols.length + 1;
                    rowsHtml =
                        `<tr><td colspan="${colSpan}" class="text-center py-4 text-muted"><i class="bi bi-inbox me-2"></i>Tidak ada data dalam periode ini</td></tr>`;
                } else {
                    weekItems.forEach((item, i) => {
                        let rowClass = item.valid === 'valid' ? '' : 'table-warning';
                        let encodedItem = encodeURIComponent(JSON.stringify(item));
                        let cellsHtml = `<td class="text-center fw-bold">${i + 1}</td>`;
                        visibleCols.forEach(colKey => {
                            let colDef = COLUMN_DEFS[colKey];
                            if (!colDef) return;
                            let val = item[colDef.dataProp];
                            if (colDef.type === 'currency') {
                                cellsHtml += `<td class="text-end">${formatRupiah(val)}</td>`;
                            } else if (colDef.type === 'number') {
                                cellsHtml += `<td class="text-center">${val ?? '-'}</td>`;
                            } else {
                                cellsHtml += `<td>${escapeHtml(val || '-')}</td>`;
                            }
                        });
                        rowsHtml +=
                            `<tr class="${rowClass} cursor-pointer btn-edit" data-item="${encodedItem}">${cellsHtml}</tr>`;
                    });
                }
                let headerHtml = '<th>No</th>';
                visibleCols.forEach(colKey => {
                    let colDef = COLUMN_DEFS[colKey];
                    if (colDef) headerHtml += `<th>${colDef.label}</th>`;
                });
                let validCount = weekItems.filter(i => i.valid === 'valid').length;
                let totalRows = weekItems.length;
                let footerHtml = '';
                if (isLastWeek && (filteredData.length > 0 || rawDataStore.length > 0)) {
                    let fb = footerDataStore.bulanan || {};
                    let ft = footerDataStore.tahunan || {};
                    let bulananCells = '<td colspan="10" class="text-end">TOTAL BULANAN</td>';
                    let tahunanCells = '<td colspan="10" class="text-end">TOTAL TAHUNAN</td>';
                    visibleCols.forEach(colKey => {
                        let colDef = COLUMN_DEFS[colKey];
                        if (!colDef) {
                            bulananCells += '<td></td>';
                            tahunanCells += '<td></td>';
                            return;
                        }
                        if (colDef.type === 'currency') {
                            let dataProp = colDef.dataProp;
                            let mapKey = dataProp === 'total_penjualan_kotor' ? 'total_penjualan' :
                                dataProp === 'total_penjualan_sales' ? 'total_penjualan_sales' :
                                dataProp === 'pengurangan_pph' ? 'pengurangan_pph' : dataProp ===
                                'biaya_lain_lain' ? 'biaya_lain_lain' : dataProp === 'total_diskon' ?
                                'total_diskon' : dataProp === 'total_pa' ? 'total_pa' : dataProp ===
                                'total_cashback' ? 'total_cashback' : dataProp === 'total_uang_saku' ?
                                'total_uang_saku' : dataProp === 'total_akomodasi' ? 'total_akomodasi' :
                                dataProp === 'oleh_oleh' ? 'oleh_oleh' : dataProp === 'entertainment' ?
                                'entertainment' : dataProp === 'biaya_transport' ? 'biaya_transport' :
                                dataProp === 'PPN' ? 'total_ppn' : dataProp === 'PPH' ? 'total_pph' :
                                dataProp === 'jumlah_pembayaran' ? 'jumlah_pembayaran' : dataProp ===
                                'biaya_admin' ? 'biaya_admin' : dataProp === 'total_piutang' ?
                                'total_piutang' : dataProp === 'exam' ? 'total_exam' : dataProp;
                            let bulananVal = fb[mapKey] ?? 0;
                            let tahunanVal = ft[mapKey] ?? 0;
                            bulananCells += `<td class="text-end">${formatRupiah(bulananVal)}</td>`;
                            tahunanCells += `<td class="text-end">${formatRupiah(tahunanVal)}</td>`;
                        } else {
                            bulananCells += '<td></td>';
                            tahunanCells += '<td></td>';
                        }
                    });
                    footerHtml =
                        `<tfoot><tr class="table-info fw-bold">${bulananCells}</tr><tr class="table-dark fw-bold">${tahunanCells}</tr></tfoot>`;
                }
                container.append(
                    `<div class="card my-1 vlk-card"><div class="card-body p-2"><div class="vlk-card-header px-1 pt-1"><div><h3 class="card-title my-1 fs-6"><span class="vlk-week-badge">${weekInfo.week_number}</span> Approval Penjualan</h3><p class="card-title my-1 text-muted small">Periode : ${startOfWeek.format('DD MMMM YYYY')} - ${endOfWeek.format('DD MMMM YYYY')}</p></div>${totalRows > 0 ? `<span class="vlk-status-pill"><i class="bi bi-check2-circle me-1"></i>${validCount}/${totalRows} tervalidasi</span>` : ''}</div><div class="sync-scroll-wrapper table-scroll-sync"><table class="table table-striped table-hover mb-0" style="min-width:2600px;"><thead><tr>${headerHtml}</tr></thead><tbody>${rowsHtml}</tbody>${footerHtml}</table></div></div></div>`
                );
            });
            bindSyncScroll();
        }

        function calcBasic(op) {
            let n1 = parseFloat($('#calcNum1').val()) || 0;
            let n2 = parseFloat($('#calcNum2').val()) || 0;
            let result = 0;
            switch (op) {
                case '+':
                    result = n1 + n2;
                    break;
                case '-':
                    result = n1 - n2;
                    break;
                case '*':
                    result = n1 * n2;
                    break;
                case '/':
                    result = n2 !== 0 ? n1 / n2 : 0;
                    break;
            }
            $('#calcResult').val(result.toLocaleString('id-ID', {
                maximumFractionDigits: 2
            }));
        }

        function calcPajak() {
            let dpp = parseFloat($('#pajakDPP').val()) || 0;
            let ppnRate = parseFloat($('#pajakPPNRate').val()) || 0;
            let pphRate = parseFloat($('#pajakPPHRate').val()) || 0;
            let ppn = dpp * (ppnRate / 100);
            let pph = dpp * (pphRate / 100);
            let total = dpp + ppn - pph;
            $('#pajakPPNResult').text('Rp ' + ppn.toLocaleString('id-ID'));
            $('#pajakPPHResult').text('Rp ' + pph.toLocaleString('id-ID'));
            $('#pajakTotalResult').text('Rp ' + total.toLocaleString('id-ID'));
        }

        function calcDiskon() {
            let harga = parseFloat($('#diskonHarga').val()) || 0;
            let tingkatStr = $('#diskonTingkat').val();
            let tingkat = tingkatStr.split(',').map(x => parseFloat(x.trim()) || 0);
            let hargaAkhir = harga;
            tingkat.forEach(pct => {
                hargaAkhir = hargaAkhir * (1 - pct / 100);
            });
            let totalDiskon = harga - hargaAkhir;
            $('#diskonTotal').text('Rp ' + totalDiskon.toLocaleString('id-ID'));
            $('#diskonAkhir').text('Rp ' + hargaAkhir.toLocaleString('id-ID'));
        }

        function calcKomisi() {
            let nett = parseFloat($('#komisiNett').val()) || 0;
            let persen = parseFloat($('#komisiPersen').val()) || 2;
            let komisi = nett * (persen / 100);
            $('#komisiHasil').text('Rp ' + komisi.toLocaleString('id-ID'));
        }

        $(document).on('click', '#btnExportExcel', function() {
            let bulan = $('#month').val();
            let tahun = $('#year').val();
            if (!bulan || !tahun) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih periode terlebih dahulu.'
                });
                return;
            }
            let payload = {
                tahun: tahun,
                bulan: bulan,
                filter: filterState,
                _token: '{{ csrf_token() }}'
            };
            let form = $('<form>', {
                'method': 'POST',
                'action': '/office/approval-pendapatan/export-excel',
                'style': 'display:none;'
            });
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': '{{ csrf_token() }}'
            }));
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'payload',
                'value': JSON.stringify(payload)
            }));
            $('body').append(form);
            form.submit();
            form.remove();
        });
    </script>
@endsection
