@extends('layout_HR.app')

@section('content_HR')
    <style>
        #lockOverlay {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(15, 23, 42, .92);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        #lockOverlay.d-none {
            display: none !important;
        }

        #lockOverlay .lock-card {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            color: #1e293b;
            border: 0;
            border-radius: 1rem;
            padding: 2rem 1.75rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .45);
            text-align: center;
        }

        #lockOverlay .lock-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #lockOverlay .lock-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .25rem;
        }

        #lockOverlay .lock-desc {
            font-size: .875rem;
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        #lockOverlay .form-control {
            background: #ffffff;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            height: 44px;
        }

        #lockOverlay .form-control::placeholder {
            color: #94a3b8;
        }

        #lockOverlay .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 .2rem rgba(79, 70, 229, .15);
        }

        #lockOverlay .btn-toggle-pw {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            min-width: 96px;
        }

        #lockOverlay .btn-toggle-pw:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        #lockOverlay .btn-lock-back,
        #lockOverlay .btn-lock-open {
            height: 44px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #lockOverlay .btn-lock-back {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        #lockOverlay .btn-lock-back:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        #lockOverlay .btn-lock-open {
            background: #4f46e5;
            border: 1px solid #4f46e5;
            color: #ffffff;
        }

        #lockOverlay .btn-lock-open:hover {
            background: #4338ca;
            border-color: #4338ca;
        }

        #lockOverlay textarea.form-control {
            height: auto;
        }

        #lockOverlay .modal {
            z-index: 100000;
        }

        .dp-mask {
            letter-spacing: .2em;
            font-family: monospace;
        }

        .dp-secret {
            font-family: monospace;
            color: #dc3545;
            font-weight: 600;
            word-break: break-all;
        }

        .dp-note {
            max-width: 240px;
            white-space: normal;
        }

        .ol-req::after {
            content: ' *';
            color: #dc3545;
        }

        .dp-menu {
            position: fixed;
            z-index: 2000;
            min-width: 180px;
            background: #ffffff;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-radius: .5rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
            padding: .35rem 0;
            display: none;
        }

        .dp-menu.show {
            display: block;
        }

        .dp-menu .dp-item {
            display: block;
            width: 100%;
            text-align: left;
            background: transparent;
            border: 0;
            padding: .5rem 1rem;
            font-size: .875rem;
            color: inherit;
            cursor: pointer;
        }

        .dp-menu .dp-item:hover {
            background: #f1f5f9;
        }

        .dp-menu .dp-item.danger {
            color: #dc3545;
        }

        .dp-menu .dp-item.danger:hover {
            background: #fef2f2;
        }

        .dp-menu hr {
            margin: .35rem 0;
            border-color: #e2e8f0;
            opacity: 1;
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-0">Data Password</h4>
                <small class="text-muted">Simpanan informasi akses penting. Password tersamarkan dan terenkripsi.</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border" id="lockTimer">05:00</span>
                <button type="button" class="btn btn-outline-secondary" id="btnLock">Kunci Sekarang</button>
                <button type="button" class="btn btn-primary" id="btnTambah">+ Tambah Password</button>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <button type="button" class="nav-link active" id="tabData" data-bs-toggle="tab"
                    data-bs-target="#paneData">Data Password</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" id="tabLog" data-bs-toggle="tab" data-bs-target="#paneLog">Log
                    Aktivitas</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" id="tabRw" data-bs-toggle="tab" data-bs-target="#paneRw">Riwayat
                    Lupa Password</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="paneData">
                <form id="formCari" class="row g-2 mb-3">
                    <div class="col-md-5 col-lg-4">
                        <input type="text" id="searchQ" class="form-control"
                            placeholder="Cari judul atau identifier...">
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <select id="filterKategori" class="form-select">
                            <option value="">Semua kategori</option>
                            @foreach ($kategori as $k)
                                <option value="{{ $k }}">{{ $k }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-secondary" id="btnReset">Reset</button>
                    </div>
                </form>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">No.</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Identifier</th>
                                    <th>Password</th>
                                    <th>Catatan</th>
                                    <th>Diperbarui</th>
                                    <th style="width:100px" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody"></tbody>
                        </table>
                    </div>
                    <div class="card-footer"><small class="text-muted" id="tableInfo"></small></div>
                </div>
            </div>

            <div class="tab-pane fade" id="paneLog">
                <form id="formLogCari" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" id="logQ" class="form-control"
                            placeholder="Cari pengguna, IP, perangkat, data...">
                    </div>
                    <div class="col-md-2">
                        <select id="logAksi" class="form-select">
                            <option value="">Semua aksi</option>
                            @foreach ($aksiLog as $k => $l)
                                <option value="{{ $k }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><input type="date" id="logDari" class="form-control" title="Dari tanggal">
                    </div>
                    <div class="col-md-2"><input type="date" id="logSampai" class="form-control" title="Sampai tanggal">
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Aksi
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button type="submit" class="dropdown-item">Cari</button></li>
                                <li><button type="button" class="dropdown-item" id="btnLogReset">Reset</button></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><button type="button" class="dropdown-item" id="btnLogExport">Export</button></li>
                                <li><button type="button" class="dropdown-item" id="btnLogImport">Import</button></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><button type="button" class="dropdown-item text-danger"
                                        id="btnLogCleanup">Bersihkan</button></li>
                            </ul>
                        </div>
                    </div>
                </form>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">No.</th>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Aksi</th>
                                    <th>Data</th>
                                    <th>Alamat IP</th>
                                    <th>Perangkat</th>
                                    <th>Sumber</th>
                                </tr>
                            </thead>
                            <tbody id="logBody"></tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <small class="text-muted" id="logInfo"></small>
                        <div id="logPagination"></div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="paneRw">
                <form id="formRwCari" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" id="rwQ" class="form-control"
                            placeholder="Cari pengguna, IP, perangkat, alasan...">
                    </div>
                    <div class="col-md-2">
                        <select id="rwStatus" class="form-select">
                            <option value="">Semua status</option>
                            @foreach ($statusLupa as $k => $l)
                                <option value="{{ $k }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><input type="date" id="rwDari" class="form-control"
                            title="Dari tanggal"></div>
                    <div class="col-md-2"><input type="date" id="rwSampai" class="form-control"
                            title="Sampai tanggal"></div>
                    <div class="col-auto d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary">Cari</button>
                        <button type="button" class="btn btn-outline-secondary" id="btnRwReset">Reset</button>
                    </div>
                </form>

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">No.</th>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Status</th>
                                    <th>Hasil Pertanyaan</th>
                                    <th>Alasan</th>
                                    <th>Jawaban</th>
                                    <th>Alamat IP</th>
                                    <th>Perangkat</th>
                                </tr>
                            </thead>
                            <tbody id="rwBody"></tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <small class="text-muted" id="rwInfo"></small>
                        <div id="rwPagination"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalForm" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form class="modal-content" id="formPassword" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formTitle">Tambah Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="formError"></div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label ol-req">Judul</label>
                                <input type="text" name="judul" class="form-control" maxlength="255" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label ol-req">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    @foreach ($kategori as $k)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Identifier</label>
                                <input type="text" name="identifier" class="form-control" maxlength="255"
                                    placeholder="Username / email / ID" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" id="fPasswordLabel">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="fPassword" class="form-control"
                                        maxlength="50" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary btn-toggle-pw"
                                        data-target="#fPassword">Lihat</button>
                                    <button type="button" class="btn btn-outline-primary"
                                        id="btnGenerate">Generate</button>
                                </div>
                                <div class="form-text d-none" id="fPasswordHint">Kosongkan jika password tidak diubah.
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan</label>
                                <textarea name="catatan" rows="2" class="form-control" maxlength="2000"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLogExport" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Log Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="logExportError">Pilih minimal 1 kolom.</div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Format</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="logFmt" id="logFmtX"
                                    value="xlsx" checked>
                                <label class="form-check-label" for="logFmtX">Excel (.xlsx)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="logFmt" id="logFmtP"
                                    value="pdf">
                                <label class="form-check-label" for="logFmtP">PDF</label>
                            </div>
                        </div>
                    </div>
                    <label class="form-label fw-semibold">Kolom yang diexport</label>
                    <div class="row g-2" id="logExportCols"></div>
                    <div class="form-text mt-2">Filter yang sedang aktif ikut diterapkan. Maksimal 10.000 baris.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnDoLogExport">Export</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLogImport" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Log Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="logImportError"></div>
                    <div class="alert alert-success d-none" id="logImportOk"></div>
                    <p class="small text-muted">
                        Gunakan file hasil Export (.xlsx / .csv). Kolom <strong>Waktu</strong> dan <strong>Aksi</strong>
                        wajib ada.
                        Baris yang sudah ada dilewati, dan baris hasil import ditandai sumber <em>Import</em>.
                    </p>
                    <input type="file" id="logImportFile" class="form-control" accept=".xlsx,.xls,.csv">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-warning" id="btnDoLogImport">Import</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLogCleanup" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bersihkan Log Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="cleanupError"></div>
                    <div class="alert alert-success d-none" id="cleanupOk"></div>
                    <div class="alert alert-warning small">
                        Log yang dihapus tidak dapat dikembalikan, kecuali sudah di-export lebih dulu
                        (lalu di-import kembali). Log yang lebih baru dari 30 hari tidak dapat dihapus.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hapus log yang lebih lama dari</label>
                        <select id="cleanupPeriode" class="form-select">
                            <option value="30">1 bulan (30 hari)</option>
                            <option value="90" selected>3 bulan (90 hari)</option>
                            <option value="180">6 bulan (180 hari)</option>
                            <option value="365">1 tahun</option>
                            <option value="730">2 tahun</option>
                            <option value="custom">Pilih tanggal sendiri</option>
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="cleanupCustomWrap">
                        <label class="form-label">Hapus log sebelum tanggal</label>
                        <input type="date" id="cleanupSebelum" class="form-control">
                    </div>
                    <div class="border rounded p-2 mb-3 bg-light small" id="cleanupPreview">Menghitung...</div>
                    <div>
                        <label class="form-label fw-semibold">Password master <span class="text-danger">*</span></label>
                        <input type="password" id="cleanupPassword" class="form-control" autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger" id="btnDoCleanup" disabled>Hapus Log</button>
                </div>
            </div>
        </div>
    </div>

    <div id="lockOverlay" class="{{ $unlocked ? 'd-none' : '' }}">
        <div class="lock-card">
            <div class="lock-icon">
                <i class="iconify" data-icon="mdi:lock-outline" data-width="36" data-height="36"></i>
            </div>
            <div class="lock-title">Area Terproteksi</div>
            <div class="lock-desc">Masukkan password Data Password untuk melanjutkan.</div>
            <form id="formUnlock" autocomplete="off">
                <div class="alert alert-danger py-2 d-none text-start" id="unlockError"></div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" id="unlockPassword" placeholder="Password"
                        autocomplete="new-password" required>
                    <button type="button" class="btn btn-toggle-pw" data-target="#unlockPassword">Lihat</button>
                </div>
                <div class="d-flex gap-2">
                    <div class="mb-4">
                        <a href="{{ route('HR.index') }}" class="btn btn-lock-back flex-fill">Kembali</a>
                    </div>
                    <button type="submit" class="btn btn-lock-open flex-fill mb-4" id="btnUnlock">Buka</button>
                </div>

                <div class="text-end mb-3">
                    <a href="#" id="linkLupa" class="small">Lupa password?</a>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalLupa" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <div class="modal-header">
                    <h5 class="modal-title" id="lupaTitle">Lupa Password Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="alert alert-danger py-2 mx-3 mt-3 mb-0 d-none" id="lupaError"></div>

                {{-- Langkah 1: verifikasi --}}
                <form id="formLupaVerif" autocomplete="off">
                    <div class="modal-body">
                        <p class="small text-muted">Jawab pertanyaan berikut untuk memastikan ini benar-benar Anda.
                            Jawaban dan hasil verifikasi dicatat.</p>

                        <div class="mb-3">
                            <label class="form-label ol-req">1. Password akun login Anda</label>
                            <div class="input-group">
                                <input type="password" name="account_password" id="lupaAkunPw" class="form-control"
                                    autocomplete="new-password" required>
                                <button type="button" class="btn btn-toggle-pw" data-target="#lupaAkunPw">Lihat</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label ol-req">2. Username akun Anda</label>
                            <input type="text" name="username" class="form-control" maxlength="255"
                                autocomplete="off" required>
                        </div>
                        @if ($lupaAdaData)
                            <div class="mb-3">
                                <label class="form-label ol-req">3. Sebutkan judul salah satu data yang tersimpan di
                                    vault</label>
                                <input type="text" name="judul_data" class="form-control" maxlength="255" required>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label ol-req">{{ $lupaAdaData ? '4' : '3' }}. Alasan membuat password
                                baru</label>
                            <textarea name="alasan" rows="2" class="form-control" minlength="10" maxlength="1000" required></textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="konfirmasi" value="1"
                                id="lupaKonfirmasi" required>
                            <label class="form-check-label small" for="lupaKonfirmasi">
                                Saya menyatakan bahwa saya adalah pemilik akun ini dan bertanggung jawab atas pembuatan
                                ulang password.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Verifikasi</button>
                    </div>
                </form>

                {{-- Langkah 2: password baru --}}
                <form id="formLupaReset" autocomplete="off" class="d-none">
                    <div class="modal-body">
                        <p class="small text-muted">Verifikasi berhasil. Buat password master baru (8–50 karakter).</p>
                        <div class="mb-3">
                            <label class="form-label ol-req">Password baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="lupaPw1" class="form-control"
                                    minlength="8" maxlength="50" autocomplete="new-password" required>
                                <button type="button" class="btn btn-toggle-pw" data-target="#lupaPw1">Lihat</button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label ol-req">Konfirmasi password baru</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="lupaPw2" class="form-control"
                                    minlength="8" maxlength="50" autocomplete="new-password" required>
                                <button type="button" class="btn btn-toggle-pw" data-target="#lupaPw2">Lihat</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function() {
            const ROUTES = {
                data: "{{ route('HR.data-password.data') }}",
                store: "{{ route('HR.data-password.store') }}",
                update: "{{ route('HR.data-password.update', '__ID__') }}",
                destroy: "{{ route('HR.data-password.destroy', '__ID__') }}",
                secret: "{{ route('HR.data-password.secret', '__ID__') }}",
                unlock: "{{ route('HR.data-password.unlock') }}",
                lock: "{{ route('HR.data-password.lock') }}",
                lupaVerif: "{{ route('HR.data-password.lupa.verifikasi') }}",
                lupaReset: "{{ route('HR.data-password.lupa.reset') }}",
                lupaRiwayat: "{{ route('HR.data-password.lupa.riwayat') }}"
            };
            const KATEGORI_COLOR = {
                'Server': 'bg-dark',
                'Email': 'bg-primary',
                'Aplikasi': 'bg-success',
                'Perangkat': 'bg-info text-dark',
                'Lainnya': 'bg-secondary'
            };
            const REVEAL_MS = 10000;
            const IDLE_SEC = 300;
            const MASK = '&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;';

            const LOG_ROUTES = {
                data: "{{ route('HR.data-password.log.data') }}",
                export: "{{ route('HR.data-password.log.export') }}",
                import: "{{ route('HR.data-password.log.import') }}"
            };
            const KOLOM_LOG = @json($kolomLog);
            let logPage = 1;

            function logFilters() {
                return {
                    q: $.trim($('#logQ').val()),
                    aksi: $('#logAksi').val(),
                    dari: $('#logDari').val(),
                    sampai: $('#logSampai').val()
                };
            }

            function aksiBadge(key, label) {
                const cls = /gagal|hapus|bersihkan/.test(key) ? 'bg-danger' :
                    /reset|ganti/.test(key) ? 'bg-warning text-dark' :
                    /buka_berhasil|tambah|master_dibuat/.test(key) ? 'bg-success' : 'bg-secondary';
                return `<span class="badge ${cls}">${esc(label)}</span>`;
            }

            function loadLog(page) {
                logPage = page || logPage || 1;
                $('#logBody').html(
                    '<tr><td colspan="8" class="text-center text-muted py-4">Memuat data...</td></tr>');
                $.get(LOG_ROUTES.data, $.extend({
                    page: logPage
                }, logFilters())).done(renderLog).fail(xhr => {
                    if (xhr.status === 423) {
                        showLock('Sesi berakhir. Masukkan password kembali.');
                        return;
                    }
                    $('#logBody').html(
                        '<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat log.</td></tr>'
                    );
                });
            }

            function renderLog(res) {
                if (!res.data.length) {
                    $('#logBody').html(
                        '<tr><td colspan="8" class="text-center text-muted py-4">Belum ada log.</td></tr>');
                    $('#logInfo').text('');
                    $('#logPagination').empty();
                    return;
                }

                $('#logBody').html(res.data.map((l, i) => `<tr>
                    <td>${(res.from || 1) + i}</td>
                    <td class="text-nowrap">${esc(l.waktu)}</td>
                    <td class="fw-semibold">${esc(l.pengguna)}<div class="small text-muted fw-normal">${esc(l.jabatan)}</div></td>
                    <td>${aksiBadge(l.aksi_key, l.aksi)}${l.keterangan !== '-' ? `<div class="small text-muted">${esc(l.keterangan)}</div>` : ''}</td>
                    <td>${esc(l.data)}</td>
                    <td class="text-nowrap">${esc(l.ip)}</td>
                    <td>${esc(l.perangkat)}</td>
                    <td><span class="badge ${l.sumber === 'Import' ? 'bg-warning text-dark' : 'bg-light text-dark border'}">${esc(l.sumber)}</span></td>
                </tr>`).join(''));

                $('#logInfo').text(`Menampilkan ${res.from}–${res.to} dari ${res.total} log`);

                if (res.last_page <= 1) {
                    $('#logPagination').empty();
                    return;
                }
                const items = res.links.map(l => {
                    const label = String(l.label).replace('Previous', 'Sebelumnya').replace('Next',
                        'Berikutnya');
                    const page = l.url ? new URL(l.url).searchParams.get('page') : '';
                    return `<li class="page-item ${l.active ? 'active' : ''} ${l.url ? '' : 'disabled'}"><a class="page-link" href="#" data-page="${page}">${label}</a></li>`;
                }).join('');
                $('#logPagination').html(`<ul class="pagination pagination-sm mb-0">${items}</ul>`);
            }

            $('#tabLog').on('shown.bs.tab', function() {
                $('#btnTambah').addClass('d-none');
                loadLog(1);
            });
            $('#tabData').on('shown.bs.tab', () => $('#btnTambah').removeClass('d-none'));

            $('#formLogCari').on('submit', function(e) {
                e.preventDefault();
                loadLog(1);
            });

            $('#btnLogReset').on('click', function() {
                $('#logQ, #logAksi, #logDari, #logSampai').val('');
                loadLog(1);
            });

            $(document).on('click', '#logPagination a.page-link', function(e) {
                e.preventDefault();
                const p = $(this).attr('data-page');
                if (p) loadLog(parseInt(p, 10));
            });

            $('#btnLogExport').on('click', function() {
                $('#logExportError').addClass('d-none');
                $('#logFmtX').prop('checked', true);
                $('#logExportCols').html(Object.entries(KOLOM_LOG).map(([k, label]) =>
                    `<div class="col-6"><div class="form-check">
                        <input class="form-check-input log-col" type="checkbox" id="lc-${esc(k)}" value="${esc(k)}" checked>
                        <label class="form-check-label" for="lc-${esc(k)}">${esc(label)}</label>
                    </div></div>`).join(''));
                modal('modalLogExport').show();
            });

            $('#btnDoLogExport').on('click', function() {
                const cols = $('#logExportCols .log-col:checked').map((_, e) => e.value).get();
                if (!cols.length) {
                    $('#logExportError').removeClass('d-none');
                    return;
                }
                const params = $.extend({}, logFilters(), {
                    format: $('input[name=logFmt]:checked').val(),
                    columns: cols
                });
                window.location.href = LOG_ROUTES.export+'?' + $.param(params);
                modal('modalLogExport').hide();
            });

            $('#btnLogImport').on('click', function() {
                $('#logImportFile').val('');
                $('#logImportError, #logImportOk').addClass('d-none').empty();
                modal('modalLogImport').show();
            });

            $('#btnDoLogImport').on('click', function() {
                const f = $('#logImportFile')[0].files[0];
                $('#logImportError, #logImportOk').addClass('d-none').empty();
                if (!f) {
                    $('#logImportError').removeClass('d-none').text('Pilih file terlebih dahulu.');
                    return;
                }
                const fd = new FormData();
                fd.append('file', f);
                const $b = $(this).prop('disabled', true);
                $.ajax({
                        url: LOG_ROUTES.import,
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(res => {
                        $('#logImportOk').removeClass('d-none').text(res.message);
                        loadLog(1);
                    })
                    .fail(xhr => {
                        if (xhr.status === 423) {
                            showLock('Sesi berakhir. Masukkan password kembali.');
                            return;
                        }
                        const j = xhr.responseJSON;
                        let msg = (j && j.message) || 'Gagal mengimpor.';
                        if (j && j.errors) msg = Object.values(j.errors).flat().join(' ');
                        $('#logImportError').removeClass('d-none').text(msg);
                    })
                    .always(() => $b.prop('disabled', false));
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            let ITEMS = {};
            let editId = null;
            let idleLeft = IDLE_SEC;
            let idleTimer = null;
            let searchTimer = null;
            const revealTimers = {};

            const $overlay = $('#lockOverlay').appendTo('body');
            $('#modalLupa').appendTo($overlay);
            const $menu = $('<div class="dp-menu" id="dpMenu"></div>').appendTo('body');
            const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

            function esc(s) {
                return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            function url(tpl, id) {
                return tpl.replace('__ID__', encodeURIComponent(id));
            }

            function closeMenu() {
                $menu.removeClass('show').empty();
            }

            function menuHtml(id) {
                const item = (label, cls, danger) =>
                    `<button type="button" class="dp-item ${danger ? 'danger ' : ''}${cls}" data-id="${id}">${label}</button>`;
                const isMaster = ITEMS[id] && ITEMS[id].is_master;
                return item('Lihat Password', 'btn-reveal') +
                    item('Salin Password', 'btn-copy') +
                    '<hr>' +
                    item(isMaster ? 'Ganti Password' : 'Edit', 'btn-edit') +
                    (isMaster ? '' : item('Hapus', 'btn-hapus', true));
            }

            $(document).on('click', '.dp-dd-toggle', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                const wasOpen = $menu.hasClass('show') && $menu.attr('data-for') === String(id);
                closeMenu();
                if (wasOpen) return;

                $menu.html(menuHtml(id)).attr('data-for', id).addClass('show');

                const r = this.getBoundingClientRect();
                const mw = $menu.outerWidth();
                const mh = $menu.outerHeight();
                let left = r.right - mw;
                let top = r.bottom + 4;
                if (left < 8) left = 8;
                if (top + mh > window.innerHeight - 8) top = Math.max(8, r.top - mh - 4);
                $menu.css({
                    left: left + 'px',
                    top: top + 'px'
                });
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#dpMenu').length) closeMenu();
            });
            $menu.on('click', '.dp-item', closeMenu);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') closeMenu();
            });
            $(window).on('resize', closeMenu);
            window.addEventListener('scroll', closeMenu, true);

            function setInert(on) {
                const $others = $('body').children().not($overlay);
                if (on) $others.attr('inert', '');
                else $others.removeAttr('inert');
            }

            function clearReveals() {
                Object.keys(revealTimers).forEach(k => {
                    clearTimeout(revealTimers[k]);
                    delete revealTimers[k];
                });
            }

            function showLock(message) {
                clearInterval(idleTimer);
                clearReveals();
                closeMenu();
                $('.modal.show').each(function() {
                    bootstrap.Modal.getOrCreateInstance(this).hide();
                });
                $('#tableBody').empty();
                $('#logBody').empty();
                $('#logPagination').empty();
                $('#logInfo').text('');
                $('#rwBody').empty();
                $('#rwPagination').empty();
                $('#rwInfo').text('');
                ITEMS = {};
                $('#unlockPassword').val('').attr('type', 'password');
                $('.btn-toggle-pw[data-target="#unlockPassword"]').text('Lihat');
                $('#unlockError').toggleClass('d-none', !message).text(message || '');
                $overlay.removeClass('d-none');
                setInert(true);
                setTimeout(() => $('#unlockPassword').trigger('focus'), 50);
            }

            function hideLock() {
                $overlay.addClass('d-none');
                setInert(false);
                idleLeft = IDLE_SEC;
                clearInterval(idleTimer);
                idleTimer = setInterval(tickIdle, 1000);
                loadData();
            }

            function lockNow(message) {
                $.post(ROUTES.lock, {
                    auto: message ? 1 : 0
                }).always(() => showLock(message));
            }

            function tickIdle() {
                idleLeft--;
                const m = String(Math.floor(Math.max(idleLeft, 0) / 60)).padStart(2, '0');
                const s = String(Math.max(idleLeft, 0) % 60).padStart(2, '0');
                $('#lockTimer').text(`${m}:${s}`);
                if (idleLeft <= 0) lockNow('Terkunci otomatis karena tidak ada aktivitas.');
            }

            function handleFail(xhr, fallback) {
                if (xhr.status === 423) {
                    showLock('Sesi berakhir. Masukkan password kembali.');
                    return;
                }
                alert((xhr.responseJSON && xhr.responseJSON.message) || fallback);
            }

            function loadData() {
                $('#tableBody').html(
                    '<tr><td colspan="8" class="text-center text-muted py-4">Memuat data...</td></tr>');
                $.get(ROUTES.data, {
                    q: $.trim($('#searchQ').val()),
                    kategori: $('#filterKategori').val()
                }).done(res => renderTable(res.data)).fail(xhr => {
                    if (xhr.status === 423) {
                        showLock('Sesi berakhir. Masukkan password kembali.');
                        return;
                    }
                    $('#tableBody').html(
                        '<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>'
                    );
                });
            }

            function renderTable(rows) {
                clearReveals();
                closeMenu();
                ITEMS = {};
                if (!rows.length) {
                    $('#tableBody').html(
                        '<tr><td colspan="8" class="text-center text-muted py-4">Belum ada data.</td></tr>');
                    $('#tableInfo').text('');
                    return;
                }
                $('#tableBody').html(rows.map((d, i) => {
                    ITEMS[d.id] = d;
                    return `<tr>
                        <td>${i + 1}</td>
                        <td class="fw-semibold">${esc(d.judul)}${d.is_master ? ' <span class="badge bg-warning text-dark ms-1">Master</span>' : ''}</td>
                        <td><span class="badge ${KATEGORI_COLOR[d.kategori] || 'bg-secondary'}">${esc(d.kategori)}</span></td>
                        <td>${esc(d.identifier || '-')}</td>
                        <td><span class="dp-mask" data-pw="${d.id}">${MASK}</span></td>
                        <td class="dp-note">${esc(d.catatan || '-')}</td>
                        <td>${esc(d.updated_at || '-')}</td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-secondary dp-dd-toggle" data-id="${d.id}">Aksi</button></td>
                    </tr>`;
                }).join(''));
                $('#tableInfo').text(`Menampilkan ${rows.length} data`);
            }

            $('#formUnlock').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#btnUnlock').prop('disabled', true).text('Memeriksa...');
                $('#unlockError').addClass('d-none');
                $.post(ROUTES.unlock, {
                        password: $('#unlockPassword').val()
                    })
                    .done(() => hideLock())
                    .fail(xhr => {
                        const msg = xhr.status === 429 ?
                            'Terlalu banyak percobaan. Coba lagi sebentar lagi.' :
                            (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal membuka.';
                        $('#unlockError').removeClass('d-none').text(msg);
                        $('#unlockPassword').val('').trigger('focus');
                    })
                    .always(() => $btn.prop('disabled', false).text('Buka'));
            });

            $('#btnLock').on('click', () => lockNow());

            $(document).on('mousemove keydown click', function() {
                if ($overlay.hasClass('d-none')) idleLeft = IDLE_SEC;
            });

            $(document).on('click', '.btn-toggle-pw', function() {
                const $i = $($(this).data('target'));
                const show = $i.attr('type') === 'password';
                $i.attr('type', show ? 'text' : 'password');
                $(this).text(show ? 'Sembunyikan' : 'Lihat');
            });

            $('#searchQ').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadData, 300);
            });
            $('#filterKategori').on('change', loadData);
            $('#formCari').on('submit', e => e.preventDefault());
            $('#btnReset').on('click', function() {
                $('#searchQ').val('');
                $('#filterKategori').val('');
                loadData();
            });

            $(document).on('click', '.btn-reveal', function() {
                const id = $(this).attr('data-id');
                const $span = $(`[data-pw="${id}"]`);
                $.get(url(ROUTES.secret, id)).done(res => {
                    clearTimeout(revealTimers[id]);
                    $span.removeClass('dp-mask').addClass('dp-secret').text(res.secret);
                    revealTimers[id] = setTimeout(() => {
                        $span.removeClass('dp-secret').addClass('dp-mask').html(MASK);
                    }, REVEAL_MS);
                }).fail(xhr => handleFail(xhr, 'Gagal mengambil password.'));
            });

            $(document).on('click', '.btn-copy', function() {
                $.get(url(ROUTES.secret, $(this).attr('data-id'))).done(res => {
                    navigator.clipboard.writeText(res.secret).then(
                        () => alert('Password disalin ke clipboard.'),
                        () => alert('Gagal menyalin. Browser menolak akses clipboard.')
                    );
                }).fail(xhr => handleFail(xhr, 'Gagal mengambil password.'));
            });

            function randomPassword(len) {
                const set = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
                const arr = new Uint32Array(len);
                crypto.getRandomValues(arr);
                return Array.from(arr, n => set[n % set.length]).join('');
            }

            $('#btnGenerate').on('click', function() {
                $('#fPassword').val(randomPassword(16)).attr('type', 'text');
                $('.btn-toggle-pw[data-target="#fPassword"]').text('Sembunyikan');
            });

            function resetForm() {
                $('#formPassword')[0].reset();
                $('#formError').addClass('d-none').empty();
                $('#fPassword').attr('type', 'password');
                $('.btn-toggle-pw[data-target="#fPassword"]').text('Lihat');
                $('#formPassword').find('[name=judul],[name=kategori],[name=identifier],[name=catatan]').prop(
                    'disabled', false);
                $('#formPassword [name=kategori] option[data-temp]').remove();
            }

            $('#btnTambah').on('click', function() {
                editId = null;
                resetForm();
                $('#formTitle').text('Tambah Password');
                $('#fPassword').prop('required', true);
                $('#fPasswordLabel').addClass('ol-req');
                $('#fPasswordHint').addClass('d-none');
                modal('modalForm').show();
            });

            $(document).on('click', '.btn-edit', function() {
                const d = ITEMS[$(this).attr('data-id')];
                if (!d) return;
                editId = d.id;
                resetForm();
                const $f = $('#formPassword');
                $f.find('[name=judul]').val(d.judul);
                $f.find('[name=identifier]').val(d.identifier || '');
                $f.find('[name=catatan]').val(d.catatan || '');

                if (d.is_master) {
                    $f.find('[name=kategori]').append(
                        `<option data-temp value="${esc(d.kategori)}">${esc(d.kategori)}</option>`).val(
                        d.kategori);
                    $f.find('[name=judul],[name=kategori],[name=identifier],[name=catatan]').prop(
                        'disabled', true);
                    $('#formTitle').text('Ganti Password Master');
                    $('#fPassword').prop('required', true);
                    $('#fPasswordLabel').addClass('ol-req');
                    $('#fPasswordHint').addClass('d-none');
                } else {
                    $f.find('[name=kategori]').val(d.kategori);
                    $('#formTitle').text('Edit Password');
                    $('#fPassword').prop('required', false);
                    $('#fPasswordLabel').removeClass('ol-req');
                    $('#fPasswordHint').removeClass('d-none');
                }
                modal('modalForm').show();
            });

            $('#formPassword').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('[type=submit]').prop('disabled', true);
                const data = $(this).serializeArray();
                if (editId) data.push({
                    name: '_method',
                    value: 'PUT'
                });
                $('#formError').addClass('d-none').empty();
                $.post(editId ? url(ROUTES.update, editId) : ROUTES.store, data)
                    .done(() => {
                        modal('modalForm').hide();
                        loadData();
                    })
                    .fail(xhr => {
                        if (xhr.status === 423) {
                            showLock('Sesi berakhir. Masukkan password kembali.');
                            return;
                        }
                        const j = xhr.responseJSON;
                        let msgs = [];
                        if (j && j.errors) Object.values(j.errors).forEach(a => msgs = msgs.concat(a));
                        else msgs.push((j && j.message) || 'Terjadi kesalahan pada server.');
                        $('#formError').removeClass('d-none')
                            .html('<ul class="mb-0">' + msgs.map(m => `<li>${esc(m)}</li>`).join('') +
                                '</ul>');
                    })
                    .always(() => $btn.prop('disabled', false));
            });

            $(document).on('click', '.btn-hapus', function() {
                const d = ITEMS[$(this).attr('data-id')];
                if (!d || !confirm(`Hapus "${d.judul}"?`)) return;
                $.post(url(ROUTES.destroy, d.id), {
                        _method: 'DELETE'
                    })
                    .done(loadData)
                    .fail(xhr => handleFail(xhr, 'Gagal menghapus data.'));
            });

            const CLEANUP_URL = "{{ route('HR.data-password.log.cleanup') }}";

            function cleanupPayload() {
                return {
                    periode: $('#cleanupPeriode').val(),
                    sebelum: $('#cleanupSebelum').val()
                };
            }

            function cleanupPreview() {
                const p = cleanupPayload();
                $('#btnDoCleanup').prop('disabled', true);
                $('#cleanupError').addClass('d-none').empty();

                if (p.periode === 'custom' && !p.sebelum) {
                    $('#cleanupPreview').text('Pilih tanggal terlebih dahulu.');
                    return;
                }

                $('#cleanupPreview').text('Menghitung...');
                $.post(CLEANUP_URL, $.extend({
                        preview: 1
                    }, p))
                    .done(res => {
                        $('#cleanupPreview').html(
                            `Akan menghapus <strong>${res.jumlah}</strong> log sebelum <strong>${esc(res.sebelum)}</strong>.`
                        );
                        $('#btnDoCleanup').prop('disabled', res.jumlah === 0);
                    })
                    .fail(xhr => {
                        if (xhr.status === 423) {
                            showLock('Sesi berakhir. Masukkan password kembali.');
                            return;
                        }
                        const j = xhr.responseJSON;
                        let msg = (j && j.message) || 'Gagal menghitung.';
                        if (j && j.errors) msg = Object.values(j.errors).flat().join(' ');
                        $('#cleanupPreview').text('-');
                        $('#cleanupError').removeClass('d-none').text(msg);
                    });
            }

            $('#btnLogCleanup').on('click', function() {
                $('#cleanupPassword').val('');
                $('#cleanupSebelum').val('');
                $('#cleanupOk').addClass('d-none').empty();
                $('#cleanupPeriode').val('90');
                $('#cleanupCustomWrap').addClass('d-none');
                modal('modalLogCleanup').show();
                cleanupPreview();
            });

            $('#cleanupPeriode').on('change', function() {
                $('#cleanupCustomWrap').toggleClass('d-none', $(this).val() !== 'custom');
                cleanupPreview();
            });

            $('#cleanupSebelum').on('change', cleanupPreview);

            $('#btnDoCleanup').on('click', function() {
                $('#cleanupError, #cleanupOk').addClass('d-none').empty();

                if (!$('#cleanupPassword').val()) {
                    $('#cleanupError').removeClass('d-none').text('Password master wajib diisi.');
                    return;
                }
                if (!confirm('Hapus log secara permanen? Aksi ini tidak dapat dibatalkan.')) return;

                const $b = $(this).prop('disabled', true);
                $.post(CLEANUP_URL, $.extend({
                        password: $('#cleanupPassword').val()
                    }, cleanupPayload()))
                    .done(res => {
                        $('#cleanupOk').removeClass('d-none').text(res.message);
                        $('#cleanupPassword').val('');
                        loadLog(1);
                        cleanupPreview();
                    })
                    .fail(xhr => {
                        if (xhr.status === 423) {
                            showLock('Sesi berakhir. Masukkan password kembali.');
                            return;
                        }
                        const j = xhr.responseJSON;
                        $('#cleanupError').removeClass('d-none').text((j && j.message) ||
                            'Gagal menghapus log.');
                        $b.prop('disabled', false);
                    });
            });

            function lupaStep(n, msg) {
                $('#formLupaVerif').toggleClass('d-none', n !== 1);
                $('#formLupaReset').toggleClass('d-none', n !== 2);
                $('#lupaTitle').text(n === 1 ? 'Lupa Password Master' : 'Buat Password Baru');
                $('#lupaError').toggleClass('d-none', !msg).text(msg || '');
            }

            function lupaMsg(xhr, fallback) {
                const j = xhr.responseJSON;
                if (j && j.errors) return Object.values(j.errors).flat().join(' ');
                return (j && j.message) || fallback;
            }

            $('#linkLupa').on('click', function(e) {
                e.preventDefault();
                $('#formLupaVerif, #formLupaReset').each(function() {
                    this.reset();
                });
                $('#lupaAkunPw, #lupaPw1, #lupaPw2').attr('type', 'password');
                $('#modalLupa .btn-toggle-pw').text('Lihat');
                lupaStep(1);
                modal('modalLupa').show();
            });

            $('#modalLupa')
                .on('show.bs.modal', function() {
                    $('#lockOverlay .lock-card').addClass('d-none');
                })
                .on('hidden.bs.modal', function() {
                    $('#lockOverlay .lock-card').removeClass('d-none');
                    $('#unlockPassword').val('').trigger('focus');
                });

            $('#formLupaVerif').on('submit', function(e) {
                e.preventDefault();
                const $b = $(this).find('[type=submit]').prop('disabled', true).text('Memeriksa...');
                $.post(ROUTES.lupaVerif, $(this).serialize())
                    .done(() => {
                        lupaStep(2);
                        setTimeout(() => $('#lupaPw1').trigger('focus'), 50);
                    })
                    .fail(xhr => {
                        $('#lupaAkunPw').val('');
                        lupaStep(1, lupaMsg(xhr, 'Verifikasi gagal.'));
                    })
                    .always(() => $b.prop('disabled', false).text('Verifikasi'));
            });

            $('#formLupaReset').on('submit', function(e) {
                e.preventDefault();
                const $b = $(this).find('[type=submit]').prop('disabled', true);
                $.post(ROUTES.lupaReset, $(this).serialize())
                    .done(() => {
                        $('#modalLupa').one('hidden.bs.modal', hideLock);
                        modal('modalLupa').hide();
                    })
                    .fail(xhr => {
                        const j = xhr.responseJSON;
                        if (j && j.ulang) {
                            lupaStep(1, j.message);
                            return;
                        }
                        lupaStep(2, lupaMsg(xhr, 'Gagal menyimpan password baru.'));
                    })
                    .always(() => $b.prop('disabled', false));
            });

            let rwPage = 1;
            const RW_HASIL = {
                password_akun: 'Password akun',
                username: 'Username',
                nama_lengkap: 'Nama',
                judul_data: 'Judul data'
            };

            function rwFilters() {
                return {
                    q: $.trim($('#rwQ').val()),
                    status: $('#rwStatus').val(),
                    dari: $('#rwDari').val(),
                    sampai: $('#rwSampai').val()
                };
            }

            function rwStatusBadge(key, label) {
                const cls = {
                    gagal: 'bg-danger',
                    terverifikasi: 'bg-info text-dark',
                    selesai: 'bg-success',
                    kedaluwarsa: 'bg-secondary'
                } [key] || 'bg-secondary';
                return `<span class="badge ${cls}">${esc(label)}</span>`;
            }

            function loadRw(page) {
                rwPage = page || rwPage || 1;
                $('#rwBody').html('<tr><td colspan="9" class="text-center text-muted py-4">Memuat data...</td></tr>');
                $.get(ROUTES.lupaRiwayat, $.extend({
                    page: rwPage
                }, rwFilters())).done(renderRw).fail(xhr => {
                    if (xhr.status === 423) {
                        showLock('Sesi berakhir. Masukkan password kembali.');
                        return;
                    }
                    $('#rwBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Gagal memuat riwayat.</td></tr>');
                });
            }

            function renderRw(res) {
                if (!res.data.length) {
                    $('#rwBody').html('<tr><td colspan="9" class="text-center text-muted py-4">Belum ada riwayat lupa password.</td></tr>');
                    $('#rwInfo').text('');
                    $('#rwPagination').empty();
                    return;
                }

                $('#rwBody').html(res.data.map((r, i) => {
                    const hasil = Object.entries(r.hasil || {}).map(([k, ok]) =>
                        `<div><span class="badge ${ok ? 'bg-success' : 'bg-danger'}">${ok ? 'Benar' : 'Salah'}</span> <small>${esc(RW_HASIL[k] || k)}</small></div>`
                    ).join('') || '-';

                    return `<tr>
                        <td>${(res.from || 1) + i}</td>
                        <td class="text-nowrap">${esc(r.waktu)}</td>
                        <td class="fw-semibold">${esc(r.pengguna)}<div class="small text-muted fw-normal">${esc(r.jabatan)}</div></td>
                        <td>${rwStatusBadge(r.status_key, r.status)}
                            ${r.diverifikasi !== '-' ? `<div class="small text-muted">Verifikasi: ${esc(r.diverifikasi)}</div>` : ''}
                            ${r.selesai !== '-' ? `<div class="small text-muted">Selesai: ${esc(r.selesai)}</div>` : ''}</td>
                        <td>${hasil}</td>
                        <td class="dp-note">${esc(r.alasan)}</td>
                        <td class="small">${esc(r.jawaban_username)}<div class="text-muted">${esc(r.jawaban_judul)}</div></td>
                        <td class="text-nowrap">${esc(r.ip)}</td>
                        <td>${esc(r.perangkat)}</td>
                    </tr>`;
                }).join(''));

                $('#rwInfo').text(`Menampilkan ${res.from}–${res.to} dari ${res.total} riwayat`);

                if (res.last_page <= 1) {
                    $('#rwPagination').empty();
                    return;
                }
                const items = res.links.map(l => {
                    const label = String(l.label).replace('Previous', 'Sebelumnya').replace('Next', 'Berikutnya');
                    const page = l.url ? new URL(l.url).searchParams.get('page') : '';
                    return `<li class="page-item ${l.active ? 'active' : ''} ${l.url ? '' : 'disabled'}"><a class="page-link" href="#" data-page="${page}">${label}</a></li>`;
                }).join('');
                $('#rwPagination').html(`<ul class="pagination pagination-sm mb-0">${items}</ul>`);
            }

            $('#tabRw').on('shown.bs.tab', function() {
                $('#btnTambah').addClass('d-none');
                loadRw(1);
            });
            $('#formRwCari').on('submit', function(e) {
                e.preventDefault();
                loadRw(1);
            });
            $('#btnRwReset').on('click', function() {
                $('#rwQ, #rwStatus, #rwDari, #rwSampai').val('');
                loadRw(1);
            });
            $(document).on('click', '#rwPagination a.page-link', function(e) {
                e.preventDefault();
                const p = $(this).attr('data-page');
                if (p) loadRw(parseInt(p, 10));
            });

            @if ($unlocked)
                hideLock();
            @else
                showLock();
            @endif
        });
    </script>
@endpush
