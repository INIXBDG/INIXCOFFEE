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

        <div class="modal fade" id="setupAccountingModal" tabindex="-1" 
        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
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
                    <div class="vlk-icon-badge"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <h3 class="m-0"> Approval Penjualan</h3>
                        <span class="vlk-subtitle">Rekonsiliasi pendapatan &amp; pelacakan dokumen per periode</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary-emphasis fs-6 px-3 py-2" id="current-period"></span>
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
                                        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
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

        <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius:16px;">
                    <div class="modal-header px-4 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="modal-icon-wrap d-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-20"
                                style="width:40px;height:40px;">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Update Penjualan Kotor</h5>
                                <small class="text-white text-opacity-75" id="modal-subtitle">—</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="px-4 py-2 border-bottom d-flex align-items-center gap-0 vlk-steps-bar">
                        <div class="step-pill active" data-step="1">
                            <span class="step-num">1</span>
                            <span class="step-label">Informasi</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-pill" data-step="2">
                            <span class="step-num">2</span>
                            <span class="step-label">Perhitungan</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-pill" data-step="3">
                            <span class="step-num">3</span>
                            <span class="step-label">Tracking</span>
                        </div>
                    </div>

                    <form id="formUpdate">
                        @csrf
                        <input type="hidden" id="update_id">

                        <div class="modal-body p-0 vlk-modal-body" style="max-height:70vh;overflow-y:auto;">

                            <div class="step-content p-4" id="step-1">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="section-label-bar section-blue">
                                            <i class="bi bi-file-text me-2"></i>
                                            <span>Informasi Training & Invoice</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">No Faktur</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-receipt text-muted"></i></span>
                                            <input type="text" class="form-control" id="no_faktur" name="no_faktur"
                                                placeholder="Nomor faktur">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">No Invoice</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-card-list text-muted"></i></span>
                                            <input type="text" class="form-control" id="no_invoice" name="no_invoice"
                                                placeholder="Nomor invoice">
                                        </div>
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
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-calendar-event text-muted"></i></span>
                                            <input type="date" class="form-control" id="tanggal_mulai"
                                                name="tanggal_mulai">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Tanggal Selesai</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-calendar-check text-muted"></i></span>
                                            <input type="date" class="form-control" id="tanggal_selesai"
                                                name="tanggal_selesai">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Sales</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-person text-muted"></i></span>
                                            <input type="text" class="form-control bg-light" id="nama_sales" readonly
                                                placeholder="—">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Instruktur</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-person-badge text-muted"></i></span>
                                            <input type="text" class="form-control bg-light" id="instruktur" readonly
                                                placeholder="—">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary px-4" onclick="goStep(2)">
                                        Selanjutnya <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="step-content p-4 d-none" id="step-2">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="section-label-bar section-mint">
                                            <i class="bi bi-calculator me-2"></i>
                                            <span>Perhitungan Penjualan</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Harga Net</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="harga" name="harga">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold small">Pax</label>
                                        <input type="number" class="form-control input-calc" id="pax" name="pax" min="0">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Total Penjualan Kotor</label>
                                        <div class="input-group">
                                            <span class="input-group-text vlk-input-prefix-mint">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control vlk-input-tint-mint fw-bold currency-input" id="total" name="total">
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="section-label-bar section-coral">
                                            <i class="bi bi-dash-circle me-2"></i>
                                            <span>Pengurang</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Diskon / PA</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="diskon" name="diskon">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Total Diskon</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="total_diskon" name="total_diskon">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Total PA</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="total_pa" name="total_pa">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Cashback</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="total_cashback" name="total_cashback">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Uang Saku</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="total_uang_saku" name="total_uang_saku">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Akomodasi</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="total_akomodasi" name="total_akomodasi">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Oleh-Oleh Peserta</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="oleh_oleh" name="oleh_oleh">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Biaya Lain-Lain</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="biaya_lain_lain" name="biaya_lain_lain">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Entertainment</label>
                                        <div class="input-group"><span
                                                class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="entertainment" name="entertainment">
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="section-label-bar section-amber">
                                            <i class="bi bi-truck me-2"></i>
                                            <span>Transport & Pajak</span>
                                        </div>
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
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="biaya_transport" name="biaya_transport">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Pengurangan PPH</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="pengurangan_pph" name="pengurangan_pph">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Exam</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control input-calc currency-input" id="exam_value" name="exam">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">PPN</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control currency-input" id="PPN" name="PPN">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">PPH</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control currency-input" id="PPH" name="PPH">
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="section-label-bar section-lavender">
                                            <i class="bi bi-wallet2 me-2"></i>
                                            <span>Pembayaran</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">PIC</label>
                                        <input type="text" class="form-control" id="pic" name="pic">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">No. Regist</label>
                                        <input type="text" class="form-control" id="regist" name="regist">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Jumlah Pembayaran</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control currency-input" id="jumlah_pembayaran" name="jumlah_pembayaran">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Tanggal Pembayaran</label>
                                        <input type="date" class="form-control" id="tanggal_pembayaran" name="tanggal_pembayaran">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold small">Biaya Admin</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-muted small">Rp</span>
                                            <input type="text" inputmode="numeric" class="form-control currency-input" id="biaya_admin" name="biaya_admin">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 rounded-3 d-flex align-items-center justify-content-between vlk-summary-panel">
                                    <div>
                                        <div class="small opacity-75 mb-1">Total Penjualan Sales (Bersih)</div>
                                        <input type="text" inputmode="numeric" class="form-control fw-bold text-dark currency-input" id="total_penjualan_sales" name="total_penjualan_sales">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-light px-4" onclick="goStep(1)">
                                        <i class="bi bi-arrow-left me-1"></i> Kembali
                                    </button>
                                    <button type="button" class="btn btn-primary px-4" onclick="goStep(3)">
                                        Selanjutnya <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="step-content p-4 d-none" id="step-3">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="section-label-bar section-lavender">
                                            <i class="bi bi-clipboard2-check me-2"></i>
                                            <span>Tracking Outstanding</span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="tracking-grid">
                                            <div class="tracking-item disabled">
                                                <div class="tracking-check-wrap">
                                                    <input class="form-check-input" type="checkbox" id="trk_invoice"
                                                        disabled checked>
                                                </div>
                                                <div class="tracking-info">
                                                    <span class="tracking-title">Invoice</span>
                                                    <span class="tracking-desc text-muted small">Otomatis terisi</span>
                                                </div>
                                                <span class="badge vlk-badge-auto">Auto</span>
                                            </div>

                                            <div class="tracking-item">
                                                <div class="tracking-check-wrap">
                                                    <input class="form-check-input tracking-check" type="checkbox"
                                                        id="trk_faktur_pajak" name="faktur_pajak" value="1">
                                                </div>
                                                <div class="tracking-info">
                                                    <label class="tracking-title" for="trk_faktur_pajak">Faktur
                                                        Pajak</label>
                                                </div>
                                            </div>

                                            <div class="tracking-item">
                                                <div class="tracking-check-wrap">
                                                    <input class="form-check-input tracking-check" type="checkbox"
                                                        id="trk_dokumen_tambahan" name="dokumen_tambahan" value="1">
                                                </div>
                                                <div class="tracking-info">
                                                    <label class="tracking-title" for="trk_dokumen_tambahan">Dokumen
                                                        Tambahan</label>
                                                </div>
                                            </div>

                                            <div class="tracking-item">
                                                <div class="tracking-check-wrap">
                                                    <input class="form-check-input tracking-check" type="checkbox"
                                                        id="trk_konfir_cs" name="konfir_cs" value="1">
                                                </div>
                                                <div class="tracking-info">
                                                    <label class="tracking-title" for="trk_konfir_cs">Konfirmasi
                                                        Pengiriman RPX</label>
                                                </div>
                                            </div>

                                            <div class="tracking-item">
                                                <div class="tracking-check-wrap">
                                                    <input class="form-check-input tracking-check" type="checkbox"
                                                        id="trk_tracking_dokumen" name="tracking_dokumen" value="1">
                                                </div>
                                                <div class="tracking-info">
                                                    <label class="tracking-title" for="trk_tracking_dokumen">Tracking
                                                        Dokumen</label>
                                                </div>
                                            </div>

                                            <div class="tracking-item">
                                                <div class="tracking-check-wrap">
                                                    <input class="form-check-input tracking-check" type="checkbox"
                                                        id="trk_no_resi" name="no_resi" value="1">
                                                </div>
                                                <div class="tracking-info">
                                                    <label class="tracking-title" for="trk_no_resi">Status Resi</label>
                                                </div>
                                            </div>

                                            <div class="tracking-item">
                                                <div class="tracking-check-wrap">
                                                    <input class="form-check-input tracking-check" type="checkbox"
                                                        id="trk_konfir_pic" name="konfir_pic" value="1">
                                                </div>
                                                <div class="tracking-info">
                                                    <label class="tracking-title" for="trk_konfir_pic">Konfirmasi
                                                        PIC</label>
                                                </div>
                                            </div>

                                            <div class="tracking-item">
                                                <div class="tracking-check-wrap">
                                                    <input class="form-check-input tracking-check" type="checkbox"
                                                        id="trk_pembayaran" name="pembayaran" value="1">
                                                </div>
                                                <div class="tracking-info">
                                                    <label class="tracking-title" for="trk_pembayaran">Pembayaran</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Nomor Resi</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i
                                                    class="bi bi-box-seam text-muted"></i></span>
                                            <input type="text" class="form-control" id="trk_status_resi"
                                                placeholder="Masukkan nomor resi">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Status PIC</label>
                                        <input type="text" class="form-control tracking-check" id="trk_status_pic"
                                            name="status_pic" placeholder="Status PIC">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-light px-4" onclick="goStep(2)">
                                        <i class="bi bi-arrow-left me-1"></i> Kembali
                                    </button>
                                    <button type="submit" class="btn btn-mint px-5 fw-semibold" id="btnSimpan">
                                        <i class="bi bi-save me-2"></i>Simpan
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
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
        }

        @keyframes vlkFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
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

        .vlk-card {
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
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script>
        let manualTotalKotor = false;
        let manualTotalBersih = false;
        let failAttempts = 0;
        const MAX_FAILS = 3;

        $(document).ready(function() {
            checkAndInitLock();
        });

        function checkAndInitLock() {
            $.ajax({
                url: '/office/approval-pendapatan/lock-status',
                type: 'GET',
                success: function(res) {
                    // 1. Belum pernah ada password fitur sama sekali
                    if (!res.has_password) {
                        $('#lockScreenOverlay').addClass('d-none');
                        $('#unlockForm').addClass('d-none');
                        $('#fallbackForm').addClass('d-none');
                        $('#failCounter').addClass('d-none');
                        new bootstrap.Modal(document.getElementById('setupModal')).show();
                        return;
                    }

                    // 2. Password fitur sudah ada, tapi password_accounting masih null
                    //    → WAJIB setup Accounting dulu
                    if (res.needs_accounting_setup) {
                        $('#lockScreenOverlay').addClass('d-none');
                        $('#unlockForm').addClass('d-none');
                        $('#fallbackForm').addClass('d-none');
                        $('#failCounter').addClass('d-none');
                        new bootstrap.Modal(document.getElementById('setupAccountingModal')).show();
                        return;
                    }

                    // 3. Semua sudah lengkap
                    if (res.is_locked) {
                        $('#lockScreenOverlay').removeClass('d-none');
                        $('#unlockForm').removeClass('d-none');
                        $('#fallbackForm').addClass('d-none');
                        $('#failCounter').addClass('d-none');
                        updateCurrentPeriod();
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
                    $('#lockScreenOverlay').removeClass('d-none');
                    $('#unlockForm').removeClass('d-none');
                    $('#fallbackForm').addClass('d-none');
                    $('#failCounter').addClass('d-none');
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

        function attemptUnlock(type) {
            let password = type === 'approval' ? $('#unlockPassword').val() : $('#fallbackPassword').val();
            let errorEl = type === 'approval' ? '#unlockError' : '#fallbackError';

            if (!password) {
                $(errorEl).text('Password wajib diisi.').removeClass('d-none');
                return;
            }

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
                        updateCurrentPeriod();
                        loadTable();
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
                            if (result.isConfirmed) {
                                showFallbackLogin();
                            }
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
                    if (errors.login_password) {
                        $('#setupLoginError').text(errors.login_password[0]).removeClass('d-none');
                    }
                    if (errors.new_password) {
                        $('#setupNewError').text(errors.new_password[0]).removeClass('d-none');
                    }
                }
            });
        }

        
        function submitAccountingSetup() {
            let loginPass   = $('#accSetupLoginPass').val();
            let newPass     = $('#accSetupNewPass').val();
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
                        // Reload status lock setelah accounting password terisi
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
                    if (errors.current_password) {
                        $('#chgCurrentError').text(errors.current_password[0]).removeClass('d-none');
                    }
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

            let deductions =
                parseNumber($('#diskon').val()) +
                parseNumber($('#total_diskon').val()) +
                parseNumber($('#total_pa').val()) +
                parseNumber($('#total_cashback').val()) +
                parseNumber($('#total_uang_saku').val()) +
                parseNumber($('#total_akomodasi').val()) +
                parseNumber($('#biaya_transport').val()) +
                parseNumber($('#oleh_oleh').val()) +
                parseNumber($('#biaya_lain_lain').val()) +
                parseNumber($('#entertainment').val()) +
                parseNumber($('#exam_value').val()) +
                parseNumber($('#pengurangan_pph').val());

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

            $.ajax({
                url: `/office/approval-pendapatan/get/${tahun}/${bulan}`,
                type: "GET",
                success: function(response) {
                    $('#loading-spinner').addClass('d-none');

                    let container = $('#weekly-container');
                    let monthDataList = response.data || [];
                    let footerBulanan = response.footer_bulanan || {};
                    let footerTahunan = response.footer_tahunan || {};

                    if (monthDataList.length === 0) {
                        container.html(`
                            <div class="card shadow-sm border-0 vlk-card">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                    <p class="text-muted fs-5 mb-0">Tidak ada data pada periode ini</p>
                                </div>
                            </div>
                        `);
                        return;
                    }

                    moment.locale('id');

                    let totalWeeks = 0;
                    monthDataList.forEach(md => totalWeeks += md.weeksData.length);
                    let currentWeekIndex = 0;

                    monthDataList.forEach(function(monthData) {
                        monthData.weeksData.forEach(function(weekData) {
                            currentWeekIndex++;
                            let isLastWeek = currentWeekIndex === totalWeeks;

                            var startOfWeek = moment(weekData.start);
                            var endOfWeek = startOfWeek.clone().add(4, 'days');

                            let validCount = weekData.data.filter(i => i.valid === 'valid').length;
                            let totalRows = weekData.data.length;

                            let rows = '';

                            if (weekData.data.length === 0) {
                                rows = `<tr><td colspan="33" class="text-center">Tidak Ada Data pada Periode Ini</td></tr>`;
                            } else {
                                weekData.data.forEach((item, i) => {
                                    let totalVal = Number(item.total_penjualan_kotor ?? (item.harga * item.pax) ?? 0);
                                    let rowClass = item.valid === 'valid' ? '' : 'table-warning';
                                    let encodedItem = encodeURIComponent(JSON.stringify(item));

                                    rows += `
                                        <tr class="${rowClass} cursor-pointer btn-edit" data-item="${encodedItem}">
                                            <td class="text-center fw-bold">${i + 1}</td>
                                            <td>${escapeHtml(item.no_faktur)}</td>
                                            <td>${escapeHtml(item.no_invoice)}</td>
                                            <td>${escapeHtml(item.materi)}</td>
                                            <td>${escapeHtml(item.tanggal_training)}</td>
                                            <td>${escapeHtml(item.perusahaan)}</td>
                                            <td>${escapeHtml(item.nama_sales)}</td>
                                            <td>${escapeHtml(item.instruktur)}</td>
                                            <td class="text-end">${formatRupiah(item.harga)}</td>
                                            <td class="text-center">${item.pax ?? '-'}</td>
                                            <td class="text-end">${formatRupiah(totalVal)}</td>
                                            <td class="text-end">${formatRupiah(item.diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_pa || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_cashback || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_uang_saku || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.total_akomodasi || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.oleh_oleh || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.biaya_lain_lain || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.entertainment || 0)}</td>
                                            <td>${escapeHtml(item.jenis_transport || '-')}</td>
                                            <td class="text-end">${formatRupiah(item.biaya_transport || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.pengurangan_pph || 0)}</td>
                                            <td>${item.exam}</td>
                                            <td class="text-end">${formatRupiah(item.total_penjualan_sales || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.PPN || 0)}</td>
                                            <td class="text-end">${formatRupiah(item.PPH || 0)}</td>
                                            <td class="text-end">${escapeHtml(item.jumlah_pembayaran || '-')}</td>
                                            <td class="text-end">${escapeHtml(item.tanggal_pembayaran || '-')}</td>
                                            <td class="text-end">${escapeHtml(item.biaya_admin || '-')}</td>
                                            <td class="text-end">${escapeHtml(item.total_piutang || '-')}</td>
                                            <td>${escapeHtml(item.tanggal_mulai || '-')}</td>
                                            <td>${escapeHtml(item.tanggal_selesai || '-')}</td>
                                        </tr>
                                    `;
                                });
                            }

                            let footerHtml = '';
                            if (isLastWeek) {
                                footerHtml = `
                                    <tfoot>
                                        <tr class="table-info fw-bold">
                                            <td colspan="10" class="text-end">TOTAL BULANAN</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_penjualan || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_pa || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_cashback || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_uang_saku || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_akomodasi || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.oleh_oleh || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.biaya_lain_lain || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.entertainment || 0)}</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.biaya_transport || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.pengurangan_pph || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_exam || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_penjualan_sales || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_ppn || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_pph || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.jumlah_pembayaran || 0)}</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.biaya_admin || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_piutang || 0)}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr class="table-dark fw-bold">
                                            <td colspan="10" class="text-end">TOTAL TAHUNAN</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_penjualan || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_diskon || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_pa || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_cashback || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_uang_saku || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_akomodasi || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.oleh_oleh || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.biaya_lain_lain || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.entertainment || 0)}</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.biaya_transport || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.pengurangan_pph || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_exam || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_penjualan_sales || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_ppn || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_pph || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.jumlah_pembayaran || 0)}</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.biaya_admin || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_piutang || 0)}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                `;
                            }

                            container.append(`
                                <div class="card my-1 vlk-card">
                                    <div class="card-body p-2">
                                        <div class="vlk-card-header px-1 pt-1">
                                            <div>
                                                <h3 class="card-title my-1 fs-6"><span class="vlk-week-badge">${currentWeekIndex}</span> Approval Penjualan</h3>
                                                <p class="card-title my-1 text-muted small">Periode : ${moment(startOfWeek).format('DD MMMM YYYY')} - ${moment(endOfWeek).format('DD MMMM YYYY')}</p>
                                            </div>
                                            ${totalRows > 0 ? `<span class="vlk-status-pill"><i class="bi bi-check2-circle me-1"></i>${validCount}/${totalRows} tervalidasi</span>` : ''}
                                        </div>
                                        <div class="sync-scroll-wrapper table-scroll-sync">
                                            <table class="table table-striped table-hover mb-0" style="min-width:2600px;">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>No Faktur</th>
                                                        <th>No Invoice</th>
                                                        <th>Materi</th>
                                                        <th>Tanggal</th>
                                                        <th>Perusahaan</th>
                                                        <th>Sales</th>
                                                        <th>Instruktur</th>
                                                        <th>Harga</th>
                                                        <th>Pax</th>
                                                        <th>Total Penjualan Kotor</th>
                                                        <th>Diskon/PA</th>
                                                        <th>Total Diskon</th>
                                                        <th>Total PA</th>
                                                        <th>Cashback</th>
                                                        <th>Uang Saku</th>
                                                        <th>Akomodasi</th>
                                                        <th>Oleh-Oleh Peserta</th>
                                                        <th>Biaya Lain-Lain</th>
                                                        <th>Entertainment</th>
                                                        <th>Jenis Transport</th>
                                                        <th>Biaya Transport</th>
                                                        <th>Pengurangan PPH</th>
                                                        <th>Exam</th>
                                                        <th>Total Penjualan Sales (Bersih)</th>
                                                        <th>PPN</th>
                                                        <th>PPH</th>
                                                        <th>Jumlah Pembayaran</th>
                                                        <th>Tanggal Pembayaran</th>
                                                        <th>Biaya Admin</th>
                                                        <th>Total Piutang</th>
                                                        <th>Tanggal Mulai</th>
                                                        <th>Tanggal Selesai</th>
                                                    </tr>
                                                </thead>
                                                <tbody>${rows}</tbody>
                                                ${footerHtml}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });
                    });

                    bindSyncScroll();
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
            let jenisTransport = $('#transportasi_select').val() === 'Lainnya' ?
                $('#transportasi_manual').val() :
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
                    total_penjualan_kotor: getCurrencyValue('#total'),
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
            let alertHtml = `
                <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${escapeHtml(message)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>`;
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
    </script>
@endsection