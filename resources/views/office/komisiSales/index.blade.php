@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- SweetAlert2 untuk konfirmasi export --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div id="lockScreenOverlay" class="lock-overlay">
        <div class="lock-card shadow-lg">
            <div class="text-center mb-4">
                <div class="vlk-icon-badge mx-auto mb-3"><i class="bi bi-shield-lock"></i></div>
                <h4 class="fw-bold mb-1">Komisi Sales Terkunci</h4>
                <p class="text-muted small mb-0">Masukkan password untuk melanjutkan</p>
            </div>

            <div id="unlockForm">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Password</label>
                    <input type="password" id="unlockPassword" class="form-control form-control-lg text-center"
                        placeholder="••••••" autofocus>
                    <div id="unlockError" class="text-danger small mt-1 d-none"></div>
                </div>
                <button class="btn btn-primary w-100 py-2 fw-semibold" onclick="attemptUnlock('approval')">
                    <i class="bi bi-unlock me-1"></i> Buka Kunci
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
                <button class="btn btn-primary w-100 py-2 fw-semibold" onclick="attemptUnlock('login')">
                    <i class="bi bi-unlock me-1"></i> Buka dengan Password Login
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
        </div>
    </div>

    <div class="modal fade" id="setupModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-header px-4 py-3" style="background:var(--vlk-primary);color:#fff;border:none;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i>Pengaturan Awal Password Approval</h5>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Untuk keamanan, hanya user dengan jabatan <b>Finance & Accounting</b> yang dapat membuat password approval ini. Silakan masukkan password login sistem Anda untuk konfirmasi identitas.</p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password Login Sistem</label>
                        <input type="password" id="setupLoginPass" class="form-control" placeholder="Masukkan password login Anda">
                        <div id="setupLoginError" class="text-danger small mt-1 d-none"></div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Buat Password Approval Baru</label>
                        <input type="password" id="setupNewPass" class="form-control" placeholder="Minimal 4 karakter">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Konfirmasi Password Approval Baru</label>
                        <input type="password" id="setupConfirmPass" class="form-control" placeholder="Ulangi password baru">
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
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-success btn-sm d-none" id="btnExportPdf">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
                    </button>
                    <span class="badge bg-primary-subtle text-primary-emphasis fs-6 px-3 py-2" id="current-period"></span>
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
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small text-muted">Tipe Periode</label>
                                <div class="ks-seg" id="ksSeg">
                                    <button type="button" class="active" data-mode="quartal">Per Quartal</button>
                                    {{-- <button type="button" data-mode="tahun">Per Tahun</button> --}}
                                </div>
                            </div>
                            <div class="col-md-3" id="wrapQuartal">
                                <label class="form-label fw-semibold small text-muted">Quartal</label>
                                <select id="quartal" class="form-select" aria-label="quartal">
                                    @php $qNow = (int) ceil(now()->month / 3); @endphp
                                    <option value="1" {{ $qNow == 1 ? 'selected' : '' }}>Quartal 1 (Jan – Mar)</option>
                                    <option value="2" {{ $qNow == 2 ? 'selected' : '' }}>Quartal 2 (Apr – Jun)</option>
                                    <option value="3" {{ $qNow == 3 ? 'selected' : '' }}>Quartal 3 (Jul – Sep)</option>
                                    <option value="4" {{ $qNow == 4 ? 'selected' : '' }}>Quartal 4 (Okt – Des)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-muted">Tahun</label>
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
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-muted d-block">&nbsp;</label>
                                <div class="ks-target-box">
                                    <div class="ks-target-stat">
                                        <div class="ks-target-label">Target {{ now()->year }}</div>
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
                                <thead>
                                    <tr>
                                        <th class="col-materi">Materi</th>
                                        <th class="text-center col-pax">Jumlah Peserta</th>
                                        <th class="text-end">Penjualan</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">PA</th>
                                        <th class="text-end">Total<br>Cashback</th>
                                        <th class="text-end">Total<br>Uang Saku</th>
                                        <th class="text-end">Total Akomodasi Hotel (Meeting Room)</th>
                                        <th class="text-end">Transport (Mobil/Pesawat/Kereta Api/Whoosh)</th>
                                        <th class="text-end">Oleh-oleh</th>
                                        <th class="text-end">Entertainment</th>
                                        <th class="text-end">Biaya Lainnya</th>
                                        <th class="text-end">Pengurangan PPH</th>
                                        <th class="text-end">Exam</th>
                                        <th class="text-end">NETT SALES</th>
                                        <th class="col-perusahaan">Perusahaan</th>
                                    </tr>
                                </thead>
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
        .lock-card .vlk-icon-badge { margin-bottom: 1rem; }
        .lock-card .form-control-lg { border-radius: 12px; font-size: 1.1rem; letter-spacing: 2px; }

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

        .vlk-page-head {
            flex-wrap: wrap;
            gap: 12px;
        }

        .vlk-filter-card {
            border-radius: 16px !important;
            background: var(--vlk-surface);
            border: 1px solid var(--vlk-border) !important;
        }

        .vlk-filter-card .form-label {
            color: var(--vlk-muted);
            font-size: .78rem;
        }

        .vlk-theme .form-select {
            border-radius: 10px;
            border-color: var(--vlk-border);
        }

        .vlk-theme .form-select:focus {
            border-color: var(--vlk-primary);
            box-shadow: 0 0 0 .22rem rgba(122, 164, 212, .22);
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

        .ks-seg {
            display: inline-flex;
            background: #F1F4F9;
            border: 1px solid var(--vlk-border);
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
        }

        .ks-seg button {
            border: none;
            background: transparent;
            border-radius: 9px;
            padding: 6px 18px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--vlk-muted);
            transition: all .2s;
        }

        .ks-seg button.active {
            background: var(--vlk-primary);
            color: #fff;
            box-shadow: 0 2px 8px -2px rgba(122, 164, 212, .6);
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

        /* Perbaikan Layout Kolom Materi dan Perusahaan */
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

        /* Pastikan td Materi dan Perusahaan tidak terpotong */
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

        .ks-target-box {
            display: flex;
            align-items: center;
            background: var(--vlk-primary-soft);
            border: 1px solid var(--vlk-primary-light);
            border-radius: 10px;
            padding: 8px 14px;
            height: 38px;
            gap: 14px;
        }
        .ks-target-stat {
            flex: 1;
            min-width: 0;
        }
        .ks-target-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--vlk-primary-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ks-target-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: .9rem;
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

        @media (max-width: 768px) {
            .ks-target-box {
                flex-direction: column;
                align-items: flex-start;
                height: auto;
                gap: 6px;
            }
            .ks-target-divider {
                width: 100%;
                height: 1px;
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

        $(document).ready(function() {
            checkAndInitLock();
        });

        function checkAndInitLock() {
            $.ajax({
                url: '/office/komisi-sales/lock-status',
                type: 'GET',
                success: function(res) {
                    if (!res.has_password) {
                        $('#lockScreenOverlay').addClass('d-none');
                        $('#unlockForm').addClass('d-none');
                        $('#fallbackForm').addClass('d-none');
                        $('#failCounter').addClass('d-none');
                        new bootstrap.Modal(document.getElementById('setupModal')).show();
                    } else if (res.is_locked) {
                        $('#lockScreenOverlay').removeClass('d-none');
                        $('#unlockForm').removeClass('d-none');
                        $('#fallbackForm').addClass('d-none');
                        $('#failCounter').addClass('d-none');
                        setTimeout(function() { $('#unlockPassword').focus(); }, 300);
                    } else {
                        $('#lockScreenOverlay').addClass('d-none');
                        loadKomisi();
                    }
                },
                error: function() {
                    $('#lockScreenOverlay').removeClass('d-none');
                    $('#unlockForm').removeClass('d-none');
                    $('#fallbackForm').addClass('d-none');
                    $('#failCounter').addClass('d-none');
                    setTimeout(function() { $('#unlockPassword').focus(); }, 300);
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

        function attemptUnlock(type) {
            let password = type === 'approval' ? $('#unlockPassword').val() : $('#fallbackPassword').val();
            let errorEl = type === 'approval' ? '#unlockError' : '#fallbackError';

            if (!password) {
                $(errorEl).text('Password wajib diisi.').removeClass('d-none');
                return;
            }

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
                        $('#lockScreenOverlay').addClass('d-none');
                        loadKomisi();
                    }
                },
                error: function(xhr) {
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
                    if (errors.login_password) $('#setupLoginError').text(errors.login_password[0]).removeClass('d-none');
                    if (errors.new_password) $('#setupNewError').text(errors.new_password[0]).removeClass('d-none');
                }
            });
        }

        function submitChangePassword() {
            let current = $('#chgCurrentPass').val();
            let newP = $('#chgNewPass').val();
            let confirm = $('#chgConfirmPass').val();

            $('#chgCurrentError, #chgNewError').addClass('d-none');

            if (!current) { $('#chgCurrentError').text('Wajib diisi.').removeClass('d-none'); return; }
            if (!newP || newP.length < 4) { $('#chgNewError').text('Minimal 4 karakter.').removeClass('d-none'); return; }
            if (newP !== confirm) { $('#chgNewError').text('Konfirmasi tidak cocok.').removeClass('d-none'); return; }

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
                    if (errors.current_password) $('#chgCurrentError').text(errors.current_password[0]).removeClass('d-none');
                }
            });
        }

        $(document).on('click', '#ksSeg button', function() {
            $('#ksSeg button').removeClass('active');
            $(this).addClass('active');
            if ($(this).data('mode') === 'tahun') $('#wrapQuartal').addClass('d-none');
            else $('#wrapQuartal').removeClass('d-none');
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

        function currentQuartal() {
            let mode = $('#ksSeg button.active').data('mode');
            return mode === 'tahun' ? 0 : $('#quartal').val();
        }

        function loadKomisi() {
            let tahun = $('#tahun').val();
            let quartal = currentQuartal();

            $.ajax({
                url: `/office/komisi-sales/get/${tahun}/${quartal}`,
                type: 'GET',
                success: function(res) {
                    komisiData = res.data || {};
                    $('#ttdDate').text(res.tanggal_ttd || '');
                    renderTabs(res.tabs || []);
                },
                error: function() {
                    komisiData = {};
                    activeSales = null;
                    $('#salesTabs').html('');
                    $('#ksContent').addClass('d-none');
                    $('#ksEmpty').removeClass('d-none');
                    $('#btnExportPdf').addClass('d-none');
                }
            });
        }

        function renderTabs(tabs) {
            if (tabs.length === 0) {
                activeSales = null;
                $('#salesTabs').html(`<div class="text-muted small py-2">Belum ada data sales pada periode ini.</div>`);
                $('#ksContent').addClass('d-none');
                $('#ksEmpty').removeClass('d-none');
                $('#btnExportPdf').addClass('d-none');
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
                $('#btnExportPdf').addClass('d-none');
                updatePeriodLabel(null);
            } else {
                renderContent();
            }
        }

        function renderContent() {
            let d = komisiData[activeSales];
            if (!d) return;

            $('#ksSalesName').text(d.nama);
            $('#sigSales').text(d.nama);
            updatePeriodLabel(d.nama);
            $('#btnExportPdf').removeClass('d-none');

            let rows = d.rows || [];
            let rowsHtml = '';

            if (rows.length === 0) {
                rowsHtml = `<tr><td colspan="15" class="text-center py-4">Tidak ada data pada periode ini</td></tr>`;
            } else {
                rows.forEach(function(r) {
                    rowsHtml += `
                        <tr>
                            <td class="td-materi">${escapeHtml(r.materi || '-')}</td>
                            <td class="text-center">${r.pax || 0}</td>
                            <td class="text-end">${rp(r.penjualan)}</td>
                            <td class="text-end">${rp(r.discount)}</td>
                            <td class="text-end">${rp(r.pa)}</td>
                            <td class="text-end">${rp(r.cashback)}</td>
                            <td class="text-end">${rp(r.uang_saku)}</td>
                            <td class="text-end">${rp(r.akomodasi)}</td>
                            <td class="text-end">${rp(r.transport)}</td>
                            <td class="text-end">${rp(r.oleh_oleh)}</td>
                            <td class="text-end">${rp(r.entertainment)}</td>
                            <td class="text-end">${rp(r.biaya_lainnya)}</td>
                            <td class="text-end">${rp(r.pengurangan_PPH)}</td>
                            <td class="text-end">${rp(r.exam)}</td>
                            <td class="text-end ks-nett">${rp(r.nett)}</td>
                            <td class="td-perusahaan">${escapeHtml(r.perusahaan || '-')}</td>
                        </tr>`;
                });
            }

            $('#ksTbody').html(rowsHtml);

            // Gunakan totals dari server (tidak ada manipulasi)
            let t = d.totals || {};
            let tfootHtml = `
                <tr class="table-info fw-bold">
                    <td class="td-materi">Total Penjualan</td>
                    <td class="text-center">${t.pax || 0}</td>
                    <td class="text-end">${rp(t.penjualan)}</td>
                    <td class="text-end">${rp(t.discount)}</td>
                    <td class="text-end">${rp(t.pa)}</td>
                    <td class="text-end">${rp(t.cashback)}</td>
                    <td class="text-end">${rp(t.uang_saku)}</td>
                    <td class="text-end">${rp(t.akomodasi)}</td>
                    <td class="text-end">${rp(t.transport)}</td>
                    <td class="text-end">${rp(t.oleh_oleh)}</td>
                    <td class="text-end">${rp(t.entertainment)}</td>
                    <td class="text-end">${rp(t.biaya_lainnya)}</td>
                    <td class="text-end">${rp(t.pengurangan_PPH)}</td>
                    <td class="text-end">${rp(t.exam)}</td>
                    <td class="text-end ks-nett">${rp(t.nett)}</td>
                    <td class="td-perusahaan"></td>
                </tr>
            `;
            $('#ksTfoot').html(tfootHtml);

            // Komisi dan terbilang dari server
            let komisi = d.komisi || 0;
            $('#ksKomisiValue').text('Rp ' + komisi.toLocaleString('id-ID'));
            $('#ksTerbilang').text(d.terbilang || '-');

            let target = d.target || 0;
            let pencapaian = d.pencapaian || 0;
            if (target > 0) {
                $('#ksTargetValue').text('Rp ' + target.toLocaleString('id-ID'));
                $('#ksPencapaianValue').text(pencapaian.toLocaleString('id-ID') + '%');
            } else {
                $('#ksTargetValue').text('Belum ditentukan');
                $('#ksPencapaianValue').text('-');
            }
        }

        function currentQuartalLabel() {
            let mode = $('#ksSeg button.active').data('mode');
            let tahun = $('#tahun').val();
            return mode === 'quartal' ? ('Quartal ' + $('#quartal').val() + ' · ' + tahun) : ('Tahun ' + tahun);
        }

        function updatePeriodLabel(sales) {
            let label = currentQuartalLabel();
            $('#ksPeriodLabel').text(label);
            $('#current-period').text(sales ? (label + ' · ' + sales) : label);
        }

        $(document).on('click', '#btnExportPdf', function() {
            Swal.fire({
                title: 'Konfirmasi Export PDF',
                text: "Apakah Anda yakin ingin mengexport laporan komisi ini dalam bentuk PDF?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#7AA4D4',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Export!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) processExportPdf();
            });
        });

        function processExportPdf() {
            let d = komisiData[activeSales];
            if (!d) return;

            let payload = {
                nama_sales: d.nama,
                tahun: $('#tahun').val(),
                quartal: currentQuartal(),
                tanggal_ttd: $('#ttdDate').text(),
                rows: JSON.stringify(d.rows || []),
                totals: JSON.stringify(d.totals || {}),
                komisi: d.komisi || 0,
                terbilang: d.terbilang || '',
                target: d.target || 0,
                pencapaian: d.pencapaian || 0
            };

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
                    link.download = `Komisi_${(d.nama || 'Sales').replace(/\s+/g, '_')}.pdf`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    Swal.fire('Berhasil!', 'PDF berhasil diexport.', 'success');
                },
                error: function() {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat mengexport PDF.', 'error');
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
