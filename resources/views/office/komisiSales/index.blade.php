@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div id="lockScreenOverlay" class="lock-overlay">
        <div class="lock-card shadow-lg">
            <div class="text-center mb-4">
                <div class="vlk-icon-badge mx-auto mb-3"><i class="bi bi-shield-lock"></i></div>
                <h4 class="fw-bold mb-1">Komisi Sales Terkunci</h4>
                <p class="text-muted small mb-0" id="lockScreenSubtitle">Memeriksa status keamanan...</p>
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
                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Memeriksa...</span>
                </button>
                <div class="text-center mt-3">
                    <button class="btn btn-link text-muted small text-decoration-none" onclick="showFallbackLogin()">
                        Gunakan Password Login
                    </button>
                </div>
            </div>

            <div id="fallbackForm" class="d-none">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Password Login</label>
                    <input type="password" id="fallbackPassword" class="form-control form-control-lg text-center"
                        placeholder="••••••">
                    <div id="fallbackError" class="text-danger small mt-1 d-none"></div>
                </div>
                <button class="btn btn-primary w-100 py-2 fw-semibold" id="btnUnlockLogin"
                    onclick="attemptUnlock('login')">
                    <span class="btn-label"><i class="bi bi-unlock me-1"></i> Buka dengan Password Login</span>
                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2"></span>Memeriksa...</span>
                </button>
                <div class="text-center mt-3">
                    <button class="btn btn-link text-muted small text-decoration-none" onclick="showApprovalLogin()">
                        ← Kembali ke Password Approval
                    </button>
                </div>
            </div>

            <div id="failCounter" class="text-center mt-3 d-none">
                <span class="badge bg-danger-subtle text-danger-emphasis px-3 py-2 rounded-pill">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Percobaan gagal: <span id="failCount">0</span>/3
                </span>
            </div>

            <div id="unlockLoadingState" class="text-center py-3">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="fw-semibold mb-1">Memuat data password anda...</p>
                <p class="text-muted small mb-0">Mohon tunggu sebentar, jangan menutup atau memuat ulang halaman ini.</p>
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
                    <button class="btn btn-primary fw-semibold w-100 py-2" onclick="submitSetup()">
                        <i class="bi bi-shield-check me-1"></i> Buat & Simpan Password Approval
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setupAccountingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-header px-4 py-3" style="background:var(--vlk-primary);color:#fff;border:none;">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-lock me-2"></i>Setup Password Accounting
                    </h5>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Sistem mendeteksi password fitur sudah ada, tetapi <b>Password Accounting</b> belum diatur.
                        Anda wajib mengisi password Accounting terlebih dahulu untuk melanjutkan.
                    </div>

                    <p class="text-muted small mb-3">
                        Masukkan password login sistem Anda (user Finance & Accounting) untuk konfirmasi identitas,
                        lalu buat Password Accounting yang akan digunakan ke depannya.
                    </p>

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
                    <button class="btn btn-primary fw-semibold w-100 py-2" onclick="submitAccountingSetup()">
                        <i class="bi bi-shield-check me-1"></i> Simpan Password Accounting
                    </button>
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
                    <button class="btn btn-primary fw-semibold" onclick="submitChangePassword()">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="vlk-theme">
        <div class="container-fluid mt-4">

            <div class="d-flex justify-content-between align-items-center mb-4 vlk-page-head">
                <div class="d-flex align-items-center gap-3">
                    <div class="vlk-icon-badge"><i class="bi bi-cash-coin"></i></div>
                    <div>
                        <h3 class="m-0">Komisi Sales</h3>
                        <span class="vlk-subtitle">Rekap komisi penjualan per sales · periode quartal &amp; tahun</span>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap vlk-toolbar">
                    <span class="badge bg-primary-subtle text-primary-emphasis vlk-period-badge" id="current-period"></span>

                    <div class="ks-seg ks-seg-icon" id="editModeSeg">
                        <button type="button" class="active" data-mode="view" title="Mode Baca Saja">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" data-mode="edit" title="Mode Edit Langsung di Tabel">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm ks-icon-btn" onclick="toggleFilterSidebar()"
                        title="Filter & Urutkan">
                        <i class="bi bi-funnel-fill"></i>
                        <span class="ks-filter-badge" id="filterCountBadge" style="display:none;">0</span>
                    </button>

                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary btn-sm ks-icon-btn dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false" title="Export Laporan">
                            <i class="bi bi-download"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm vlk-dropdown-menu">
                            <li>
                                <button type="button" class="dropdown-item" id="btnExportExcel">
                                    <i class="bi bi-file-earmark-spreadsheet-fill me-2 text-success"></i>Export Excel
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item" id="btnExportPdf">
                                    <i class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i>Export PDF
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary btn-sm ks-icon-btn dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false" title="Pengaturan Lain">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm vlk-dropdown-menu">
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#changePassModal">
                                    <i class="bi bi-key-fill me-2"></i>Ubah Password
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="ks-tabs" id="salesTabs"></div>

            <div id="ksEmpty" class="ks-empty">
                <div class="ks-empty-card">
                    <div class="ks-empty-art">
                        <div class="ks-circle"><i class="bi bi-cash-stack"></i></div>
                        <div class="ks-float f1"><i class="bi bi-percent"></i></div>
                        <div class="ks-float f2"><i class="bi bi-graph-up-arrow"></i></div>
                        <div class="ks-float f3"><i class="bi bi-wallet2"></i></div>
                    </div>
                    <div class="ks-empty-title">KOMISI SALES</div>
                    <p class="text-muted mt-2 mb-0 small">Pilih nama sales pada tab di atas untuk melihat rekap komisi per
                        periode.</p>
                </div>
            </div>

            <div id="ksContent" class="d-none">
                <div class="card shadow-sm mb-4 border-0 vlk-filter-card">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-3 col-md-4">
                                <label class="form-label">Tipe Periode</label>
                                <div class="ks-seg" id="ksSeg">
                                    <button type="button" class="active" data-mode="quartal">Per Quartal</button>
                                    <button type="button" data-mode="bulan">Per Bulan</button>
                                    <button type="button" data-mode="tahun">Per Tahun</button>
                                </div>
                            </div>

                            <div class="col-lg-2 col-md-4" id="wrapQuartal">
                                <label class="form-label">Quartal</label>
                                <select id="quartal" class="form-select" aria-label="quartal">
                                    @php $qNow = (int) ceil(now()->month / 3); @endphp
                                    <option value="1" {{ $qNow == 1 ? 'selected' : '' }}>Quartal 1 (Jan – Mar)
                                    </option>
                                    <option value="2" {{ $qNow == 2 ? 'selected' : '' }}>Quartal 2 (Apr – Jun)
                                    </option>
                                    <option value="3" {{ $qNow == 3 ? 'selected' : '' }}>Quartal 3 (Jul – Sep)
                                    </option>
                                    <option value="4" {{ $qNow == 4 ? 'selected' : '' }}>Quartal 4 (Okt – Des)
                                    </option>
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-4 d-none" id="wrapBulan">
                                <label class="form-label">Bulan</label>
                                <select id="bulan" class="form-select" aria-label="bulan">
                                    @php
                                        $bulanSekarang = now()->month;
                                        $namaBulan = [
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
                                    @endphp
                                    @foreach ($namaBulan as $index => $nama)
                                        <option value="{{ $index + 1 }}"
                                            {{ $index + 1 == $bulanSekarang ? 'selected' : '' }}>
                                            {{ $nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-4">
                                <label class="form-label">Tahun</label>
                                <select id="tahun" class="form-select" aria-label="tahun">
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

                            <div class="col-lg-3 col-md-12">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="ks-target-box">
                                    <div class="ks-target-stat">
                                        <div class="ks-target-label">Target Periode</div>
                                        <div class="ks-target-value" id="ksTargetValue">Rp 0</div>
                                    </div>
                                    <div class="ks-target-divider"></div>
                                    <div class="ks-target-stat">
                                        <div class="ks-target-label">Pencapaian</div>
                                        <div class="ks-target-value" id="ksPencapaianValue">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card my-1 vlk-card">
                    <div class="card-body p-2">
                        <div class="vlk-card-header px-1 pt-1 mb-2">
                            <div>
                                <h3 class="card-title my-1 fs-6">
                                    <span class="ks-tab-badge"><i class="bi bi-person-badge me-1"></i><span
                                            id="ksSalesName">—</span></span>
                                </h3>
                                <p class="card-title my-1 text-muted small">Rekap penjualan &amp; komisi sales</p>
                            </div>
                            <span class="vlk-status-pill"><i class="bi bi-calendar-check me-1"></i><span
                                    id="ksPeriodLabel">—</span></span>
                        </div>

                        <div class="sync-scroll-wrapper">
                            <table class="table table-striped table-hover mb-0 ks-table"
                                style="min-width:1900px; table-layout: auto;">
                                <thead id="ksThead"></thead>
                                <tbody id="ksTbody"></tbody>
                                <tfoot id="ksTfoot"></tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="ks-commission mt-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="ks-com-icon"><i class="bi bi-award"></i></div>
                        <div>
                            <div class="ks-com-label">Komisi Sales · 2%</div>
                            <div class="ks-value" id="ksKomisiValue">Rp 0</div>
                        </div>
                    </div>
                    <div class="ks-terbilang">
                        <span class="ks-com-label">Terbilang</span>
                        <i id="ksTerbilang">-</i>
                    </div>
                </div>
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
            <div class="filter-live-note">
                <i class="bi bi-lightning-charge-fill"></i> Filter langsung diterapkan otomatis — tidak perlu klik apply
            </div>

            <div class="filter-section">
                <button class="btn btn-outline-primary w-100" onclick="resetAllFilters()">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Semua Filter
                </button>
            </div>

            <div class="filter-section">
                <h6 class="filter-section-title">Urutkan Berdasarkan</h6>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Materi</label>
                    <select class="form-select form-select-sm" id="sortMateri">
                        <option value="">Tidak Ada</option>
                        <option value="asc">A - Z</option>
                        <option value="desc">Z - A</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Perusahaan</label>
                    <select class="form-select form-select-sm" id="sortPerusahaan">
                        <option value="">Tidak Ada</option>
                        <option value="asc">A - Z</option>
                        <option value="desc">Z - A</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nilai Numerik</label>
                    <select class="form-select form-select-sm mb-2" id="sortNumericColumn">
                        <option value="">Tidak Ada</option>
                        <option value="penjualan">Penjualan Kotor</option>
                        <option value="nett">Nett Sales</option>
                        <option value="pax">Jumlah Peserta</option>
                        <option value="discount">Diskon</option>
                        <option value="cashback">Cashback</option>
                    </select>
                    <select class="form-select form-select-sm" id="sortNumericOrder">
                        <option value="asc">Terkecil → Terbesar</option>
                        <option value="desc">Terbesar → Terkecil</option>
                    </select>
                </div>
            </div>

            <div class="filter-section">
                <h6 class="filter-section-title">Tampilkan Kolom</h6>
                <div class="column-toggles">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="materi" id="colMateri" checked>
                        <label class="form-check-label" for="colMateri">Materi</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="pax" id="colPax" checked>
                        <label class="form-check-label" for="colPax">Jumlah Peserta</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="penjualan" id="colPenjualan" checked>
                        <label class="form-check-label" for="colPenjualan">Penjualan Kotor</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="discount" id="colDiscount" checked>
                        <label class="form-check-label" for="colDiscount">Diskon</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="pa" id="colPA" checked>
                        <label class="form-check-label" for="colPA">PA</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="cashback" id="colCashback" checked>
                        <label class="form-check-label" for="colCashback">Cashback</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="uang_saku" id="colUangSaku" checked>
                        <label class="form-check-label" for="colUangSaku">Uang Saku</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="akomodasi" id="colAkomodasi" checked>
                        <label class="form-check-label" for="colAkomodasi">Akomodasi</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="transport" id="colTransport" checked>
                        <label class="form-check-label" for="colTransport">Transport</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="oleh_oleh" id="colOlehOleh" checked>
                        <label class="form-check-label" for="colOlehOleh">Oleh-oleh</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="entertainment" id="colEntertainment" checked>
                        <label class="form-check-label" for="colEntertainment">Entertainment</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="biaya_lainnya" id="colBiayaLain" checked>
                        <label class="form-check-label" for="colBiayaLain">Biaya Lainnya</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="pengurangan_PPH" id="colPPH" checked>
                        <label class="form-check-label" for="colPPH">Pengurangan PPH</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="exam" id="colExam" checked>
                        <label class="form-check-label" for="colExam">Exam</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="nett" id="colNett" checked>
                        <label class="form-check-label" for="colNett">NETT SALES</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="perusahaan" id="colPerusahaan" checked>
                        <label class="form-check-label" for="colPerusahaan">Perusahaan</label>
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h6 class="form-label small fw-semibold">Filter Nilai (Range)</h6>

                <div class="mb-3">
                    <label class="form-label small">Penjualan Kotor</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" class="form-control form-control-sm" id="rangePenjualanMin"
                                placeholder="Min">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control form-control-sm" id="rangePenjualanMax"
                                placeholder="Max">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Nett Sales</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" class="form-control form-control-sm" id="rangeNettMin"
                                placeholder="Min">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control form-control-sm" id="rangeNettMax"
                                placeholder="Max">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Jumlah Peserta (Pax)</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" class="form-control form-control-sm" id="rangePaxMin"
                                placeholder="Min">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control form-control-sm" id="rangePaxMax"
                                placeholder="Max">
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h6 class="filter-section-title">Pencarian Teks</h6>
                <div class="mb-3">
                    <input type="text" class="form-control form-control-sm" id="searchMateri"
                        placeholder="Cari materi...">
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control form-control-sm" id="searchPerusahaan"
                        placeholder="Cari perusahaan...">
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
            <button class="btn btn-primary w-100" onclick="applyAllFilters()">
                <i class="bi bi-check-lg me-2"></i>Selesai
            </button>
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
            --vlk-ink: #2A3A4D;
            --vlk-muted: #6B7C93;
            --vlk-bg: #F7F9FC;
            --vlk-surface: #FFFFFF;
            --vlk-border: #E4E9F1;
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

        .vlk-theme {
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--vlk-ink);
        }

        .vlk-theme h3 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            letter-spacing: -.02em;
            color: var(--vlk-ink);
        }

        .vlk-subtitle {
            font-size: .8rem;
            color: var(--vlk-muted);
        }

        .vlk-filter-card {
            border-radius: 16px !important;
            background: var(--vlk-surface);
            border: 1px solid var(--vlk-border) !important;
            box-shadow: 0 2px 8px rgba(42, 58, 77, 0.08);
        }

        .vlk-filter-card .card-body {
            padding: 1.5rem;
        }

        .vlk-filter-card .form-label {
            color: var(--vlk-muted);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            display: block;
        }

        .ks-seg {
            display: inline-flex;
            background: #F1F4F9;
            border: 1px solid var(--vlk-border);
            border-radius: 10px;
            padding: 4px;
            gap: 4px;
        }

        .ks-seg button {
            border: none;
            background: transparent;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--vlk-muted);
            transition: all 0.2s;
            white-space: nowrap;
        }

        .ks-seg button.active {
            background: var(--vlk-primary);
            color: #fff;
            box-shadow: 0 2px 8px -2px rgba(122, 164, 212, 0.6);
        }

        .ks-seg button:hover:not(.active) {
            background: rgba(255, 255, 255, 0.6);
            color: var(--vlk-primary-dark);
        }

        .vlk-filter-card .form-select {
            border-radius: 10px;
            border-color: var(--vlk-border);
            padding: 0.65rem 2.5rem 0.65rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--vlk-ink);
            background-color: #fff;
            transition: all 0.2s;
            min-height: 42px;
        }

        .vlk-filter-card .form-select:hover {
            border-color: var(--vlk-primary-light);
        }

        .vlk-filter-card .form-select:focus {
            border-color: var(--vlk-primary);
            box-shadow: 0 0 0 3px var(--vlk-primary-soft);
            outline: none;
        }

        .ks-target-box {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--vlk-primary-soft) 0%, var(--vlk-mint-soft) 100%);
            border: 1px solid var(--vlk-primary-light);
            border-radius: 12px;
            padding: 12px 16px;   
            height: 100%;
            min-height: auto;                 
            gap: 12px;                     
            box-shadow: 0 2px 6px rgba(122, 164, 212, 0.1);
            flex-wrap: nowrap;               
        }

        .ks-target-stat {
            flex: 1 1 auto;                   
            min-width: auto;                  
            overflow: hidden;               
        }

        .ks-target-label {
            font-size: 0.65rem;               
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--vlk-primary-dark);
            margin-bottom: 2px;
            white-space: nowrap;              
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ks-target-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 0.95rem;            
            line-height: 1.1;                
            color: var(--vlk-ink);
            white-space: nowrap;              
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ks-target-divider {
            width: 1px;
            align-self: stretch;
            background: var(--vlk-primary-light);
            flex-shrink: 0;                   
        }

        @media (max-width: 1200px) {
            .ks-target-box {
                padding: 10px 14px;
                gap: 10px;
            }
            
            .ks-target-value {
                font-size: 0.85rem;         
            }
            
            .ks-target-label {
                font-size: 0.6rem;
            }
        }

        @media (max-width: 991px) {
            .ks-target-box {
                flex-direction: row;         
                flex-wrap: nowrap;
            }
            
            .ks-target-stat {
                flex: 1 1 0;
                min-width: 0;
            }
        }

        .form-group-wrapper {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-spacer {
            margin-top: 1.5rem;
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

        .inline-edit-input {
            transition: all 0.2s ease;
            border-radius: 4px;
            font-variant-numeric: tabular-nums;
            text-align: right;
            padding: 6px 8px !important;
        }

        .inline-edit-input:focus {
            background-color: #fff !important;
            border: 1.5px solid var(--vlk-primary) !important;
            box-shadow: 0 0 0 3px var(--vlk-primary-soft);
            outline: none;
        }

        .inline-edit-input:hover:not(:focus):not(:disabled) {
            background-color: var(--vlk-primary-soft);
            cursor: text;
        }

        .inline-edit-input:disabled {
            background-color: transparent !important;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .inline-edit-input[type=number]::-webkit-inner-spin-button,
        .inline-edit-input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .inline-edit-input[type=number] {
            -moz-appearance: textfield;
        }

        .td-materi,
        .td-perusahaan {
            position: relative;
        }

        #ksContent[data-mode="edit"] .td-materi,
        #ksContent[data-mode="edit"] .td-perusahaan {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .vlk-page-head {
            flex-wrap: wrap;
            gap: 12px;
        }

        .vlk-toolbar {
            row-gap: 8px;
        }

        .vlk-period-badge {
            font-size: .78rem;
            font-weight: 700;
            padding: 7px 14px;
            border-radius: 20px;
            white-space: nowrap;
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

        .ks-seg-icon button {
            padding: 7px 12px;
            font-size: .95rem;
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

        .filter-sidebar-footer .btn-outline-secondary {
            border-radius: 10px;
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

        .vlk-card {
            border-radius: 16px !important;
            border: 1px solid var(--vlk-border) !important;
            background: var(--vlk-surface);
            box-shadow: 0 1px 3px rgba(42, 58, 77, .05);
            animation: ksFadeIn .35s ease both;
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

        @keyframes ksFadeIn {
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

        .ks-tab-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 8px;
            background: var(--vlk-amber);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: .8rem;
        }

        .vlk-status-pill {
            font-size: .72rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: var(--vlk-mint-soft);
            color: #4E8E6F;
            border: 1px solid var(--vlk-mint);
        }

        .ks-tabs {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 4px 2px 14px;
        }

        .ks-tab {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--vlk-border);
            background: var(--vlk-surface);
            border-radius: 999px;
            padding: 7px 16px 7px 7px;
            cursor: pointer;
            transition: all .2s;
            white-space: nowrap;
            font-size: .82rem;
            font-weight: 600;
            color: var(--vlk-ink);
        }

        .ks-tab:hover {
            border-color: var(--vlk-primary);
            box-shadow: 0 0 0 3px var(--vlk-primary-soft);
        }

        .ks-tab .ks-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--vlk-primary-soft);
            color: var(--vlk-primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .72rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .ks-tab.active {
            background: var(--vlk-primary);
            border-color: var(--vlk-primary);
            color: #fff;
            box-shadow: 0 4px 12px -4px rgba(122, 164, 212, .55);
        }

        .ks-tab.active .ks-avatar {
            background: rgba(255, 255, 255, .25);
            color: #fff;
        }

        .ks-empty {
            min-height: 62vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ks-empty-card {
            text-align: center;
            padding: 2rem;
        }

        .ks-empty-art {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1.6rem;
        }

        .ks-empty-art .ks-circle {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--vlk-primary-soft), var(--vlk-mint-soft));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ks-empty-art .ks-circle i {
            font-size: 4rem;
            color: var(--vlk-primary);
        }

        .ks-float {
            position: absolute;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid var(--vlk-border);
            box-shadow: 0 8px 20px -8px rgba(42, 58, 77, .25);
            animation: ksFloat 3s ease-in-out infinite;
        }

        .ks-float i {
            font-size: 1.1rem;
        }

        .ks-float.f1 {
            top: -6px;
            left: -16px;
            color: var(--vlk-mint);
        }

        .ks-float.f2 {
            bottom: 2px;
            right: -20px;
            color: var(--vlk-amber);
            animation-delay: 1s;
        }

        .ks-float.f3 {
            top: 40%;
            right: -34px;
            color: var(--vlk-coral);
            animation-delay: 2s;
        }

        @keyframes ksFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .ks-empty-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            letter-spacing: .14em;
            font-size: 1.5rem;
            color: var(--vlk-ink);
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

        .ks-table thead th {
            white-space: normal;
            vertical-align: middle;
        }

        .ks-table thead th.col-materi {
            min-width: 250px;
            max-width: 300px;
        }

        .ks-table thead th.col-perusahaan {
            min-width: 250px;
            max-width: 300px;
        }

        .ks-table thead th.col-pax {
            min-width: 80px;
        }

        .vlk-theme .table td {
            font-size: .8rem;
            vertical-align: middle;
            color: #000;
            padding: 8px;
            white-space: normal;
            word-wrap: break-word;
        }

        .ks-table td.td-materi,
        .ks-table td.td-perusahaan {
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            min-width: 250px;
            max-width: 300px;
        }

        .vlk-theme .table td.text-end {
            font-family: 'Inter', sans-serif;
            font-variant-numeric: tabular-nums;
            font-size: .78rem;
            color: #000;
            white-space: nowrap;
        }

        .vlk-theme .table td.text-center {
            white-space: nowrap;
        }

        .vlk-theme .table-striped>tbody>tr:nth-of-type(odd)>td {
            background-color: #FAFBFD;
        }

        .vlk-theme .table-hover>tbody>tr:hover>td {
            background-color: var(--vlk-amber-soft);
        }

        .ks-table td.ks-nett {
            background: var(--vlk-amber) !important;
            font-weight: 700;
        }

        .vlk-theme .table-info td {
            background-color: var(--vlk-primary-soft) !important;
            color: var(--vlk-primary-dark) !important;
            border-top: 2px solid var(--vlk-primary-light);
        }

        .vlk-theme .table-info td.ks-nett {
            background: var(--vlk-amber) !important;
            color: var(--vlk-ink) !important;
        }

        .ks-commission {
            background: var(--vlk-primary);
            color: #fff;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--vlk-primary-dark);
        }

        .ks-com-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .ks-com-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: rgba(255, 255, 255, .8);
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .ks-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
        }

        .ks-terbilang {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: .9rem;
        }

        .ks-signature {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .ks-sig {
            text-align: center;
        }

        .ks-sig-space {
            height: 70px;
        }

        .ks-sig-name {
            font-weight: 700;
            text-decoration: underline;
            color: var(--vlk-ink);
        }

        .ks-sig-role {
            font-weight: 700;
            color: var(--vlk-ink);
        }

        @media (max-width: 768px) {
            .ks-target-box {
                flex-direction: column;
                align-items: stretch;
                height: auto;
                gap: 10px;
                padding: 12px;
            }
            
            .ks-target-divider {
                width: 100%;
                height: 1px;
                align-self: auto;
            }
            
            .ks-target-value {
                font-size: 0.9rem;
                word-break: break-all;           
            }
        }

        @media (max-width: 992px) {
            .ks-signature {
                grid-template-columns: repeat(2, 1fr);
                row-gap: 2rem;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        let komisiData = {};
        let activeSales = null;
        let failAttempts = 0;
        const MAX_FAILS = 3;

        const COLUMN_DEFS = [
            { key: 'materi', label: 'Materi', type: 'text', dataProp: 'materi', headClass: 'col-materi', cellClass: 'td-materi' },
            { key: 'pax', label: 'Jumlah Peserta', type: 'number', dataProp: 'pax', backendField: 'pax', headClass: 'text-center col-pax', cellClass: 'text-center' },
            { key: 'penjualan', label: 'Penjualan', type: 'currency', dataProp: 'penjualan', backendField: 'total_penjualan_kotor', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'discount', label: 'Discount', type: 'currency', dataProp: 'discount', backendField: 'total_diskon', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'pa', label: 'PA', type: 'currency', dataProp: 'pa', backendField: 'total_pa', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'cashback', label: 'Total<br>Cashback', type: 'currency', dataProp: 'cashback', backendField: 'total_cashback', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'uang_saku', label: 'Total<br>Uang Saku', type: 'currency', dataProp: 'uang_saku', backendField: 'total_uang_saku', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'akomodasi', label: 'Total Akomodasi Hotel (Meeting Room)', type: 'currency', dataProp: 'akomodasi', backendField: 'total_akomodasi', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'transport', label: 'Transport (Mobil/Pesawat/Kereta Api/Whoosh)', type: 'currency', dataProp: 'transport', backendField: 'biaya_transport', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'oleh_oleh', label: 'Oleh-oleh', type: 'currency', dataProp: 'oleh_oleh', backendField: 'oleh_oleh', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'entertainment', label: 'Entertainment', type: 'currency', dataProp: 'entertainment', backendField: 'entertainment', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'biaya_lainnya', label: 'Biaya Lainnya', type: 'currency', dataProp: 'biaya_lainnya', backendField: 'biaya_lain_lain', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'pengurangan_PPH', label: 'Pengurangan PPH', type: 'currency', dataProp: 'pengurangan_PPH', backendField: 'pengurangan_pph', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'exam', label: 'Exam', type: 'currency', dataProp: 'exam', backendField: 'exam', headClass: 'text-end', cellClass: 'text-end' },
            { key: 'nett', label: 'NETT SALES', type: 'currency', dataProp: 'nett', backendField: 'total_penjualan_bersih', headClass: 'text-end', cellClass: 'text-end ks-nett' },
            { key: 'perusahaan', label: 'Perusahaan', type: 'text', dataProp: 'perusahaan', headClass: 'col-perusahaan', cellClass: 'td-perusahaan' }
        ];

        function fmtNumber(v) {
            let num = parseFloat(v) || 0;
            if (num === 0) return '-';
            return num.toLocaleString('id-ID');
        }

        function getVisibleColumnDefs() {
            const visibleCols = filterState.visibleColumns || ['materi', 'pax', 'penjualan', 'nett', 'perusahaan'];
            return COLUMN_DEFS.filter(col => visibleCols.includes(col.key));
        }

        function renderTableHeader() {
            let html = '<tr>';
            getVisibleColumnDefs().forEach(col => {
                html += `<th class="${col.headClass || ''}">${col.label}</th>`;
            });
            html += '</tr>';
            $('#ksThead').html(html);
        }

        $(document).ready(function() {
            checkAndInitLock();
        });

        function checkAndInitLock() {
            $('#unlockLoadingState').removeClass('d-none');
            $('#unlockForm, #fallbackForm, #failCounter').addClass('d-none');
            $('#lockScreenSubtitle').text('Memeriksa status keamanan...');

            $.ajax({
                url: '/office/komisi-sales/lock-status',
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
                        loadKomisi();
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

        function showLoadingDataState() {
            $('#unlockForm').addClass('d-none');
            $('#fallbackForm').addClass('d-none');
            $('#failCounter').addClass('d-none');
            $('#unlockLoadingState').removeClass('d-none');
            $('#lockScreenSubtitle').text('Sedang memuat data...');
        }

        function hideLoadingDataState() {
            $('#unlockLoadingState').addClass('d-none');
        }

        function isUnlockRequestInProgress() {
            return $('#btnUnlockApproval').prop('disabled') || $('#btnUnlockLogin').prop('disabled');
        }

        function attemptUnlock(type) {
            if (isUnlockRequestInProgress()) {
                return;
            }

            let password = type === 'approval' ? $('#unlockPassword').val() : $('#fallbackPassword').val();
            let errorEl = type === 'approval' ? '#unlockError' : '#fallbackError';

            if (!password) {
                $(errorEl).text('Password wajib diisi.').removeClass('d-none');
                return;
            }

            $(errorEl).addClass('d-none');
            setUnlockLoading(type, true);

            $.ajax({
                url: '/office/komisi-sales/unlock',
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

                        showLoadingDataState();

                        loadKomisi(function() {
                            $('#lockScreenOverlay').addClass('d-none');
                            setUnlockLoading(type, false);
                        });
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
                url: '/office/komisi-sales/setup-lock',
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
                    loadKomisi();
                    Swal.fire('Berhasil!', 'Password approval berhasil dibuat!', 'success');
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
                url: '/office/komisi-sales/setup-accounting-password',
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
                    if (errors.login_password) {
                        $('#accSetupLoginError').text(errors.login_password[0]).removeClass('d-none');
                    }
                    if (errors.accounting_password) {
                        $('#accSetupNewError').text(errors.accounting_password[0]).removeClass('d-none');
                    }
                    if (xhr.responseJSON?.message) {
                        Swal.fire('Gagal', xhr.responseJSON.message, 'error');
                    }
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
                url: '/office/komisi-sales/change-lock-password',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    current_password: current,
                    new_password: newP,
                    new_password_confirmation: confirm
                },
                success: function(res) {
                    $('#changePassModal').modal('hide');
                    Swal.fire('Berhasil!', 'Password approval berhasil diubah!', 'success');
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

        $(document).on('click', '#ksSeg button', function() {
            $('#ksSeg button').removeClass('active');
            $(this).addClass('active');
            let mode = $(this).data('mode');

            if (mode === 'quartal') {
                $('#wrapQuartal').removeClass('d-none');
                $('#wrapBulan').addClass('d-none');
            } else if (mode === 'bulan') {
                $('#wrapQuartal').addClass('d-none');
                $('#wrapBulan').removeClass('d-none');
            } else {
                $('#wrapQuartal').addClass('d-none');
                $('#wrapBulan').addClass('d-none');
            }

            loadKomisi();
        });

        $('#bulan').on('change', function() {
            loadKomisi();
        });

        $('#quartal, #tahun').on('change', function() {
            loadKomisi();
        });

        $(document).on('click', '.ks-tab', function() {
            $('.ks-tab').removeClass('active');
            $(this).addClass('active');
            activeSales = String($(this).data('id'));
            $('#ksEmpty').addClass('d-none');
            $('#ksContent').removeClass('d-none');
            renderContent();
        });

        function currentPeriode() {
            let mode = $('#ksSeg button.active').data('mode');
            let tahun = $('#tahun').val();
            let quartal = $('#quartal').val();
            let bulan = $('#bulan').val();

            return {
                mode: mode,
                tahun: tahun,
                quartal: mode === 'quartal' ? quartal : 0,
                bulan: mode === 'bulan' ? bulan : 0
            };
        }

        function loadKomisi(onComplete) {
            let periode = currentPeriode();

            $.ajax({
                url: `/office/komisi-sales/get/${periode.tahun}/${periode.quartal}/${periode.bulan}`,
                type: 'GET',
                success: function(res) {
                    komisiData = res.data || {};
                    $('#ttdDate').text(res.tanggal_ttd || '');
                    renderTabs(res.tabs || []);
                    hideLoadingDataState();
                    if (typeof onComplete === 'function') onComplete();
                },
                error: function() {
                    komisiData = {};
                    activeSales = null;
                    $('#salesTabs').html('');
                    $('#ksContent').addClass('d-none');
                    $('#ksEmpty').removeClass('d-none');
                    hideLoadingDataState();
                    if (typeof onComplete === 'function') onComplete();
                }
            });
        }

        function renderTabs(tabs) {
            if (tabs.length === 0) {
                activeSales = null;
                $('#salesTabs').html(`<div class="text-muted small py-2">Belum ada data sales pada periode ini.</div>`);
                $('#ksContent').addClass('d-none');
                $('#ksEmpty').removeClass('d-none');
                updatePeriodLabel(null);
                return;
            }

            let html = '';
            let stillExists = false;

            tabs.forEach(function(t) {
                let isActive = activeSales === String(t.id);
                if (isActive) stillExists = true;
                html += `<button type="button" class="ks-tab${isActive ? ' active' : ''}" data-id="${t.id}">
                    <span class="ks-avatar">${escapeHtml(t.initials)}</span>
                    <span class="ks-name">${escapeHtml(t.nama)}</span>
                </button>`;
            });

            $('#salesTabs').html(html);

            if (!stillExists) {
                activeSales = null;
                $('#ksContent').addClass('d-none');
                $('#ksEmpty').removeClass('d-none');
                updatePeriodLabel(null);
            } else {
                renderContent();
            }
        }

        let editMode = 'view';

        $(document).on('click', '#editModeSeg button', function() {
            $('#editModeSeg button').removeClass('active');
            $(this).addClass('active');
            editMode = $(this).data('mode');
            renderContent();
        });

        let filterState = {
            sortMateri: '',
            sortPerusahaan: '',
            sortNumericColumn: '',
            sortNumericOrder: 'asc',
            visibleColumns: ['materi', 'pax', 'penjualan', 'discount', 'pa', 'cashback', 'uang_saku', 'akomodasi', 'transport', 'oleh_oleh', 'entertainment', 'biaya_lainnya', 'pengurangan_PPH', 'exam', 'nett', 'perusahaan'],
            rangeFilters: {
                penjualan: { min: null, max: null },
                nett: { min: null, max: null },
                pax: { min: null, max: null }
            },
            searchText: {
                materi: '',
                perusahaan: ''
            }
        };

        let originalRowsData = [];

        function toggleFilterSidebar() {
            const sidebar = document.getElementById('filterSidebar');
            const overlay = document.getElementById('filterSidebarOverlay');

            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');

            if (sidebar.classList.contains('show')) {
                loadFilterValues();
            }
        }

        function loadFilterValues() {
            $('#sortMateri').val(filterState.sortMateri);
            $('#sortPerusahaan').val(filterState.sortPerusahaan);
            $('#sortNumericColumn').val(filterState.sortNumericColumn);
            $('#sortNumericOrder').val(filterState.sortNumericOrder);

            $('.column-toggles input[type="checkbox"]').each(function() {
                const colName = $(this).val();
                $(this).prop('checked', filterState.visibleColumns.includes(colName));
            });

            $('#rangePenjualanMin').val(filterState.rangeFilters.penjualan.min || '');
            $('#rangePenjualanMax').val(filterState.rangeFilters.penjualan.max || '');
            $('#rangeNettMin').val(filterState.rangeFilters.nett.min || '');
            $('#rangeNettMax').val(filterState.rangeFilters.nett.max || '');
            $('#rangePaxMin').val(filterState.rangeFilters.pax.min || '');
            $('#rangePaxMax').val(filterState.rangeFilters.pax.max || '');

            $('#searchMateri').val(filterState.searchText.materi);
            $('#searchPerusahaan').val(filterState.searchText.perusahaan);

            updateActiveFiltersPreview();
        }

        function readFilterFormIntoState() {
            filterState.sortMateri = $('#sortMateri').val();
            filterState.sortPerusahaan = $('#sortPerusahaan').val();
            filterState.sortNumericColumn = $('#sortNumericColumn').val();
            filterState.sortNumericOrder = $('#sortNumericOrder').val();

            filterState.visibleColumns = [];
            $('.column-toggles input[type="checkbox"]:checked').each(function() {
                filterState.visibleColumns.push($(this).val());
            });

            filterState.rangeFilters.penjualan.min = $('#rangePenjualanMin').val() ? parseFloat($('#rangePenjualanMin')
                .val()) : null;
            filterState.rangeFilters.penjualan.max = $('#rangePenjualanMax').val() ? parseFloat($('#rangePenjualanMax')
                .val()) : null;
            filterState.rangeFilters.nett.min = $('#rangeNettMin').val() ? parseFloat($('#rangeNettMin').val()) : null;
            filterState.rangeFilters.nett.max = $('#rangeNettMax').val() ? parseFloat($('#rangeNettMax').val()) : null;
            filterState.rangeFilters.pax.min = $('#rangePaxMin').val() ? parseFloat($('#rangePaxMin').val()) : null;
            filterState.rangeFilters.pax.max = $('#rangePaxMax').val() ? parseFloat($('#rangePaxMax').val()) : null;

            filterState.searchText.materi = $('#searchMateri').val().toLowerCase();
            filterState.searchText.perusahaan = $('#searchPerusahaan').val().toLowerCase();
        }

        function applyFiltersLive() {
            readFilterFormIntoState();

            if (activeSales && komisiData[activeSales]) {
                filterAndRenderData();
            }

            updateActiveFiltersPreview();
            updateFilterBadge();
        }

        function applyAllFilters() {
            applyFiltersLive();
            toggleFilterSidebar();
        }

        function filterAndRenderData() {
            if (!activeSales || !komisiData[activeSales]) return;

            let d = komisiData[activeSales];
            let rows = d.rows || [];

            if (originalRowsData.length === 0) {
                originalRowsData = JSON.parse(JSON.stringify(rows));
            }

            let filteredRows = JSON.parse(JSON.stringify(originalRowsData));

            if (filterState.searchText.materi) {
                filteredRows = filteredRows.filter(r =>
                    (r.materi || '').toLowerCase().includes(filterState.searchText.materi)
                );
            }

            if (filterState.searchText.perusahaan) {
                filteredRows = filteredRows.filter(r =>
                    (r.perusahaan || '').toLowerCase().includes(filterState.searchText.perusahaan)
                );
            }

            if (filterState.rangeFilters.penjualan.min !== null) {
                filteredRows = filteredRows.filter(r => (r.penjualan || 0) >= filterState.rangeFilters.penjualan.min);
            }
            if (filterState.rangeFilters.penjualan.max !== null) {
                filteredRows = filteredRows.filter(r => (r.penjualan || 0) <= filterState.rangeFilters.penjualan.max);
            }

            if (filterState.rangeFilters.nett.min !== null) {
                filteredRows = filteredRows.filter(r => (r.nett || 0) >= filterState.rangeFilters.nett.min);
            }
            if (filterState.rangeFilters.nett.max !== null) {
                filteredRows = filteredRows.filter(r => (r.nett || 0) <= filterState.rangeFilters.nett.max);
            }

            if (filterState.rangeFilters.pax.min !== null) {
                filteredRows = filteredRows.filter(r => (r.pax || 0) >= filterState.rangeFilters.pax.min);
            }
            if (filterState.rangeFilters.pax.max !== null) {
                filteredRows = filteredRows.filter(r => (r.pax || 0) <= filterState.rangeFilters.pax.max);
            }

            if (filterState.sortMateri) {
                filteredRows.sort((a, b) => {
                    let aVal = (a.materi || '').toLowerCase();
                    let bVal = (b.materi || '').toLowerCase();
                    return filterState.sortMateri === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                });
            }

            if (filterState.sortPerusahaan) {
                filteredRows.sort((a, b) => {
                    let aVal = (a.perusahaan || '').toLowerCase();
                    let bVal = (b.perusahaan || '').toLowerCase();
                    return filterState.sortPerusahaan === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(
                        aVal);
                });
            }

            if (filterState.sortNumericColumn) {
                filteredRows.sort((a, b) => {
                    let aVal = parseFloat(a[filterState.sortNumericColumn]) || 0;
                    let bVal = parseFloat(b[filterState.sortNumericColumn]) || 0;
                    return filterState.sortNumericOrder === 'asc' ? aVal - bVal : bVal - aVal;
                });
            }

            d.rows = filteredRows;

            recalculateTotalsFromRows(filteredRows);

            renderContent();
        }

        function recalculateTotalsFromRows(rows) {
            let totals = {
                pax: 0,
                penjualan: 0,
                discount: 0,
                pa: 0,
                cashback: 0,
                uang_saku: 0,
                akomodasi: 0,
                transport: 0,
                oleh_oleh: 0,
                entertainment: 0,
                biaya_lainnya: 0,
                pengurangan_PPH: 0,
                exam: 0,
                nett: 0
            };

            rows.forEach(function(r) {
                totals.pax += parseInt(r.pax) || 0;
                totals.penjualan += parseFloat(r.penjualan) || 0;
                totals.discount += parseFloat(r.discount) || 0;
                totals.pa += parseFloat(r.pa) || 0;
                totals.cashback += parseFloat(r.cashback) || 0;
                totals.uang_saku += parseFloat(r.uang_saku) || 0;
                totals.akomodasi += parseFloat(r.akomodasi) || 0;
                totals.transport += parseFloat(r.transport) || 0;
                totals.oleh_oleh += parseFloat(r.oleh_oleh) || 0;
                totals.entertainment += parseFloat(r.entertainment) || 0;
                totals.biaya_lainnya += parseFloat(r.biaya_lainnya) || 0;
                totals.pengurangan_PPH += parseFloat(r.pengurangan_PPH) || 0;
                totals.exam += parseFloat(r.exam) || 0;
                totals.nett += parseFloat(r.nett) || 0;
            });

            if (activeSales && komisiData[activeSales]) {
                komisiData[activeSales].totals = totals;
                komisiData[activeSales].komisi = Math.round(totals.nett * 0.02);
            }
        }

        function updateActiveFiltersPreview() {
            let html = '';
            let count = 0;

            if (filterState.sortMateri) {
                html +=
                    `<span class="filter-badge">Materi: ${filterState.sortMateri === 'asc' ? 'A-Z' : 'Z-A'} <span class="remove-filter" onclick="removeFilter('sortMateri')">×</span></span>`;
                count++;
            }

            if (filterState.sortPerusahaan) {
                html +=
                    `<span class="filter-badge">Perusahaan: ${filterState.sortPerusahaan === 'asc' ? 'A-Z' : 'Z-A'} <span class="remove-filter" onclick="removeFilter('sortPerusahaan')">×</span></span>`;
                count++;
            }

            if (filterState.sortNumericColumn) {
                const colNames = {
                    'penjualan': 'Penjualan',
                    'nett': 'Nett',
                    'pax': 'Pax',
                    'discount': 'Diskon',
                    'cashback': 'Cashback'
                };
                const orderText = filterState.sortNumericOrder === 'asc' ? '↑' : '↓';
                html +=
                    `<span class="filter-badge">${colNames[filterState.sortNumericColumn]} ${orderText} <span class="remove-filter" onclick="removeFilter('sortNumeric')">×</span></span>`;
                count++;
            }

            if (filterState.rangeFilters.penjualan.min !== null || filterState.rangeFilters.penjualan.max !== null) {
                html +=
                    `<span class="filter-badge">Penjualan Range <span class="remove-filter" onclick="removeFilter('rangePenjualan')">×</span></span>`;
                count++;
            }

            if (filterState.rangeFilters.nett.min !== null || filterState.rangeFilters.nett.max !== null) {
                html +=
                    `<span class="filter-badge">Nett Range <span class="remove-filter" onclick="removeFilter('rangeNett')">×</span></span>`;
                count++;
            }

            if (filterState.rangeFilters.pax.min !== null || filterState.rangeFilters.pax.max !== null) {
                html +=
                    `<span class="filter-badge">Pax Range <span class="remove-filter" onclick="removeFilter('rangePax')">×</span></span>`;
                count++;
            }

            if (filterState.searchText.materi) {
                html +=
                    `<span class="filter-badge">Cari: "${filterState.searchText.materi}" <span class="remove-filter" onclick="removeFilter('searchMateri')">×</span></span>`;
                count++;
            }

            if (filterState.searchText.perusahaan) {
                html +=
                    `<span class="filter-badge">Perusahaan: "${filterState.searchText.perusahaan}" <span class="remove-filter" onclick="removeFilter('searchPerusahaan')">×</span></span>`;
                count++;
            }

            if (html === '') {
                html = '<p class="text-muted small mb-0">Belum ada filter aktif</p>';
            }

            $('#activeFiltersPreview').html(html);
            $('#filterCountBadge').text(count).toggle(count > 0);
        }

        function removeFilter(type) {
            switch (type) {
                case 'sortMateri':
                    filterState.sortMateri = '';
                    $('#sortMateri').val('');
                    break;
                case 'sortPerusahaan':
                    filterState.sortPerusahaan = '';
                    $('#sortPerusahaan').val('');
                    break;
                case 'sortNumeric':
                    filterState.sortNumericColumn = '';
                    filterState.sortNumericOrder = 'asc';
                    $('#sortNumericColumn').val('');
                    $('#sortNumericOrder').val('asc');
                    break;
                case 'rangePenjualan':
                    filterState.rangeFilters.penjualan = {
                        min: null,
                        max: null
                    };
                    $('#rangePenjualanMin').val('');
                    $('#rangePenjualanMax').val('');
                    break;
                case 'rangeNett':
                    filterState.rangeFilters.nett = {
                        min: null,
                        max: null
                    };
                    $('#rangeNettMin').val('');
                    $('#rangeNettMax').val('');
                    break;
                case 'rangePax':
                    filterState.rangeFilters.pax = {
                        min: null,
                        max: null
                    };
                    $('#rangePaxMin').val('');
                    $('#rangePaxMax').val('');
                    break;
                case 'searchMateri':
                    filterState.searchText.materi = '';
                    $('#searchMateri').val('');
                    break;
                case 'searchPerusahaan':
                    filterState.searchText.perusahaan = '';
                    $('#searchPerusahaan').val('');
                    break;
            }

            updateActiveFiltersPreview();
            applyFiltersLive();
        }

        function resetAllFilters() {
            filterState = {
                sortMateri: '',
                sortPerusahaan: '',
                sortNumericColumn: '',
                sortNumericOrder: 'asc',
                visibleColumns: ['materi', 'pax', 'penjualan', 'discount', 'pa', 'cashback', 'uang_saku', 'akomodasi', 'transport', 'oleh_oleh', 'entertainment', 'biaya_lainnya', 'pengurangan_PPH', 'exam', 'nett', 'perusahaan'],
                rangeFilters: {
                    penjualan: { min: null, max: null },
                    nett: { min: null, max: null },
                    pax: { min: null, max: null }
                },
                searchText: {
                    materi: '',
                    perusahaan: ''
                }
            };

            originalRowsData = [];
            loadFilterValues();
            updateFilterBadge();

            if (activeSales) {
                loadKomisi();
            }
        }

        function updateFilterBadge() {
            let count = 0;
            if (filterState.sortMateri) count++;
            if (filterState.sortPerusahaan) count++;
            if (filterState.sortNumericColumn) count++;
            if (filterState.rangeFilters.penjualan.min !== null || filterState.rangeFilters.penjualan.max !== null) count++;
            if (filterState.rangeFilters.nett.min !== null || filterState.rangeFilters.nett.max !== null) count++;
            if (filterState.rangeFilters.pax.min !== null || filterState.rangeFilters.pax.max !== null) count++;
            if (filterState.searchText.materi) count++;
            if (filterState.searchText.perusahaan) count++;

            $('#filterCountBadge').text(count).toggle(count > 0);
        }

        document.getElementById('filterSidebarOverlay').addEventListener('click', toggleFilterSidebar);

        (function setupLiveFilterListeners() {
            let debounceTimer = null;
            const DEBOUNCE_MS = 350;

            function debouncedApply() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(applyFiltersLive, DEBOUNCE_MS);
            }

            $(document).on('change', '#sortMateri, #sortPerusahaan, #sortNumericColumn, #sortNumericOrder', applyFiltersLive);
            $(document).on('change', '.column-toggles input[type="checkbox"]', applyFiltersLive);

            $(document).on('input', '#rangePenjualanMin, #rangePenjualanMax, #rangeNettMin, #rangeNettMax, #rangePaxMin, #rangePaxMax', debouncedApply);
            $(document).on('input', '#searchMateri, #searchPerusahaan', debouncedApply);
        })();

        function renderContent() {
            let d = komisiData[activeSales];
            if (!d) return;

            $('#ksSalesName').text(d.nama);
            updatePeriodLabel(d.nama);

            let rows = d.rows || [];
            let rowsHtml = '';
            let isEditable = editMode === 'edit';

            const visibleColDefs = getVisibleColumnDefs();
            const colCount = visibleColDefs.length || 1;

            renderTableHeader();

            if (rows.length === 0) {
                rowsHtml = `<tr><td colspan="${colCount}" class="text-center py-4 text-muted">Tidak ada data pada periode ini</td></tr>`;
            } else {
                rows.forEach(function(r) {
                    let cellsHtml = '';

                    visibleColDefs.forEach(col => {
                        if (col.type === 'text') {
                            let val = r[col.dataProp];
                            cellsHtml += `<td class="${col.cellClass || ''}">${escapeHtml(val || '-')}</td>`;
                            return;
                        }

                        let raw = r[col.dataProp];

                        if (!isEditable) {
                            let display = col.type === 'number' ? fmtNumber(raw) : rp(raw);
                            cellsHtml += `<td class="${col.cellClass || ''}">${escapeHtml(String(display))}</td>`;
                            return;
                        }

                        let numVal = parseFloat(raw) || 0;
                        let displayVal = '';
                        if (numVal > 0) {
                            displayVal = col.type === 'currency' ? 'Rp ' + numVal.toLocaleString('id-ID') : numVal
                                .toLocaleString('id-ID');
                        }

                        cellsHtml += `<td class="${col.cellClass || ''} p-0">
                            <input type="text" 
                                class="inline-edit-input form-control form-control-sm border-0 bg-transparent text-end" 
                                data-id="${r.id_rkm}" 
                                data-field="${col.backendField}" 
                                data-old-value="${numVal}"
                                value="${displayVal}" 
                                onfocus="this.value = this.value.replace(/[^0-9]/g, '')" 
                                onblur="saveInlineEdit(this)"
                                onkeypress="return handleEnterKey(event, this)">
                        </td>`;
                    });

                    rowsHtml += `<tr>${cellsHtml}</tr>`;
                });
            }

            $('#ksTbody').html(rowsHtml);

            let t = d.totals || {};
            let tfootCellsHtml = '';
            let firstColRendered = false;

            visibleColDefs.forEach(col => {
                if (!firstColRendered) {
                    tfootCellsHtml += `<td class="${col.cellClass || ''}">Total Penjualan</td>`;
                    firstColRendered = true;
                    return;
                }

                if (col.type === 'text') {
                    tfootCellsHtml += `<td class="${col.cellClass || ''}"></td>`;
                } else if (col.type === 'number') {
                    tfootCellsHtml += `<td class="${col.cellClass || ''}">${t[col.dataProp] || 0}</td>`;
                } else {
                    tfootCellsHtml += `<td class="${col.cellClass || ''}">${rp(t[col.dataProp])}</td>`;
                }
            });

            let tfootHtml = `<tr class="table-info fw-bold">${tfootCellsHtml}</tr>`;
            $('#ksTfoot').html(tfootHtml);

            let komisi = d.komisi || 0;
            $('#ksKomisiValue').text('Rp ' + komisi.toLocaleString('id-ID'));
            $('#ksTerbilang').text(d.terbilang || '-');

            let target = d.target || 0;
            let pencapaian = d.pencapaian || 0;

            if (target > 0) {
                $('#ksTargetValue').text('Rp\u00A0' + target.toLocaleString('id-ID'));
                $('#ksPencapaianValue').text(pencapaian.toLocaleString('id-ID') + '%');
            } else {
                $('#ksTargetValue').text('Belum ditentukan');
                $('#ksPencapaianValue').text('-');
            }

            $('#ksContent').attr('data-mode', editMode);
        }

        function handleEnterKey(event, inputElement) {
            if (event.key === 'Enter') {
                event.preventDefault();
                inputElement.blur();

                let $inputs = $('.inline-edit-input:visible');
                let currentIndex = $inputs.index(inputElement);
                if (currentIndex < $inputs.length - 1) {
                    $inputs.eq(currentIndex + 1).focus();
                }
                return false;
            }
            return true;
        }

        function saveInlineEdit(inputElement) {
            let $input = $(inputElement);
            let idRkm = $input.data('id');
            let field = $input.data('field');

            let rawValue = $input.val().toString().replace(/[^0-9]/g, '');
            let newValue = rawValue === '' ? '0' : rawValue;
            let oldValue = String($input.data('old-value') ?? '0');

            if (!idRkm || idRkm === 'undefined' || idRkm === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Valid',
                    text: 'ID RKM tidak ditemukan.',
                    background: '#FAF1DE',
                    color: '#2A3A4D',
                    confirmButtonColor: '#E5C17A'
                });
                return;
            }

            if (newValue === oldValue) {
                let numVal = parseFloat(newValue) || 0;
                $input.val(numVal > 0 ? 'Rp ' + numVal.toLocaleString('id-ID') : '');
                return;
            }

            $input.prop('disabled', true).css('background-color', 'var(--vlk-primary-soft)');

            $.ajax({
                url: `/office/komisi-sales/update-inline/${idRkm}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    field: field,
                    value: newValue
                },
                success: function(res) {
                    if (res.success) {
                        $input.prop('disabled', false).css('background-color', 'transparent');
                        $input.data('old-value', newValue);

                        loadKomisi();
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    $input.prop('disabled', false).css('background-color', 'transparent');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal Menyimpan',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan.',
                        background: '#FAF1DE',
                        color: '#2A3A4D',
                        confirmButtonColor: '#E5C17A'
                    });
                }
            });
        }

        $(document).on('click', '#btnExportExcel', function() {
            let payload = getExportPayload();
            if (!payload) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih sales terlebih dahulu.',
                    background: '#FAF1DE',
                    color: '#2A3A4D',
                    confirmButtonColor: '#E5C17A'
                });
                return;
            }

            let form = $('<form>', {
                'method': 'POST',
                'action': '/office/komisi-sales/export-excel',
                'style': 'display:none;'
            }).append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': '{{ csrf_token() }}'
            })).append($('<input>', {
                'type': 'hidden',
                'name': 'payload',
                'value': JSON.stringify(payload)
            }));

            $('body').append(form);
            form.submit();
            form.remove();
        });

        function currentPeriodeLabel() {
            let mode = $('#ksSeg button.active').data('mode');
            let tahun = $('#tahun').val();

            if (mode === 'quartal') {
                return 'Quartal ' + $('#quartal').val() + ' · ' + tahun;
            } else if (mode === 'bulan') {
                let bulanText = $('#bulan option:selected').text();
                return bulanText + ' · ' + tahun;
            } else {
                return 'Tahun ' + tahun;
            }
        }

        function updatePeriodLabel(sales) {
            let label = currentPeriodeLabel();
            $('#ksPeriodLabel').text(label);
            $('#current-period').text(sales ? (label + ' · ' + sales) : label);
        }

        function getExportPayload() {
            let d = komisiData[activeSales];
            if (!d) return null;

            let periode = currentPeriode();

            return {
                nama_sales: d.nama,
                tahun: periode.tahun,
                quartal: periode.quartal,
                bulan: periode.bulan,
                periode_mode: periode.mode,
                rows: d.rows || [],
                totals: d.totals || {},
                komisi: d.komisi || 0,
                terbilang: d.terbilang || '',
                target: d.target || 0,
                pencapaian: d.pencapaian || 0
            };
        }

        $(document).on('click', '#btnExportPdf', function() {
            let payload = getExportPayload();
            if (!payload) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih sales terlebih dahulu.',
                    background: '#FAF1DE',
                    color: '#2A3A4D',
                    confirmButtonColor: '#E5C17A'
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Export PDF',
                text: "Apakah Anda yakin ingin mengexport laporan komisi ini dalam bentuk PDF?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7AA4D4',
                cancelButtonColor: '#6B7C93',
                confirmButtonText: 'Ya, Export!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) processExportPdf(payload);
            });
        });

        function processExportPdf(payload) {
            payload.tanggal_ttd = $('#ttdDate').text();
            payload.rows = JSON.stringify(payload.rows);
            payload.totals = JSON.stringify(payload.totals);

            Swal.fire({
                title: 'Memproses PDF...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            let csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

            $.ajax({
                url: '/office/komisi-sales/export-pdf',
                type: 'POST',
                data: payload,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(blob) {
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = `Komisi_${(payload.nama_sales || 'Sales').replace(/\s+/g, '_')}.pdf`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'PDF berhasil diexport.',
                        background: '#E5F3EB',
                        color: '#2A3A4D',
                        confirmButtonColor: '#7AA4D4'
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengexport PDF.',
                        background: '#FAF1DE',
                        color: '#2A3A4D',
                        confirmButtonColor: '#E5C17A'
                    });
                }
            });
        }

        function rp(v) {
            let num = parseFloat(v) || 0;
            if (num === 0) return '-';
            return 'Rp ' + num.toLocaleString('id-ID');
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
    </script>
@endsection