@extends('layouts.app')
@section('content')
    @php
        $isAdmSales = Auth::user()->jabatan === 'Adm Sales';
        $isFinance = Auth::user()->jabatan === 'Finance & Accounting';
    @endphp
    <div class="container-fluid kd">
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="cube">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                @if ($isAdmSales || $isFinance)
                    <div class="kd-shell">
                        @if ($isAdmSales)
                            <ul class="nav nav-tabs kd-tabs" id="kelengkapanTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-utama-btn" data-bs-toggle="tab"
                                        data-bs-target="#tab-utama" type="button" role="tab">Utama</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-pa-btn" data-bs-toggle="tab" data-bs-target="#tab-pa"
                                        type="button" role="tab">Payment Advance</button>
                                </li>
                            </ul>
                        @endif

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tab-utama" role="tabpanel">
                                <div class="kd-head d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <h5>Sinkronisasi Approval Pendapatan</h5>
                                        <div class="small kd-muted">Samakan data Approval Pendapatan Sales dan Finance per
                                            periode.
                                            Sinkron selesai jika tidak ada selisih dan kedua pihak sudah mengonfirmasi.
                                            Jika ada perubahan setelah konfirmasi, kedua pihak dapat mengonfirmasi ulang.
                                        </div>
                                    </div>
                                    <a class="btn btn-outline-primary"
                                        href="{{ $isAdmSales ? $urlApprovalSales : $urlApprovalFinance }}">
                                        {{ $isAdmSales ? 'Buka Approval Pendapatan Sales' : 'Buka Approval Pendapatan' }}
                                    </a>
                                </div>

                                <div class="kd-filterbar mb-3">
                                    <div>
                                        <label for="sinkMulai">Training mulai dari</label>
                                        <input type="date" class="form-control form-control-sm" id="sinkMulai">
                                    </div>
                                    <div>
                                        <label for="sinkSelesai">Sampai</label>
                                        <input type="date" class="form-control form-control-sm" id="sinkSelesai">
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="btnSinkTampil">Tampilkan</button>
                                    <div class="kd-seg" id="sinkPintasan">
                                        <button type="button" data-offset="0">Bulan ini</button>
                                        <button type="button" data-offset="-1">Bulan lalu</button>
                                    </div>
                                </div>

                                <div id="sinkPesan" class="alert d-none"></div>

                                <div id="sinkIsi" class="d-none">
                                    <div class="kd-strip mb-3" id="sinkRingkasan"></div>

                                    <div class="kd-card">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                            <div class="kd-seg" id="sinkFilter" role="group"></div>
                                            <input type="search" class="form-control form-control-sm" id="sinkCari"
                                                style="max-width:280px"
                                                placeholder="Cari perusahaan, materi, atau sales...">
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table kd-table w-100" id="tabelSinkron">
                                                <thead>
                                                    <tr>
                                                        <th>Perusahaan</th>
                                                        <th>Materi</th>
                                                        <th>Periode kelas</th>
                                                        <th>Sales</th>
                                                        <th>Status</th>
                                                        <th class="text-end">Inputan beda</th>
                                                        <th class="text-end">Bersih Sales</th>
                                                        <th class="text-end">Bersih Finance</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="kd-card mt-3" id="sinkKonfirmasi">
                                        <div class="kd-card-head">
                                            <span>Konfirmasi akhir</span>
                                            <span id="sinkBadge"></span>
                                        </div>
                                        <div class="small kd-muted mb-3">Lakukan setelah semua data di tabel sudah sama.
                                            Sinkron selesai jika Adm Sales dan Finance sudah sama-sama mengonfirmasi.
                                            Setiap ada perubahan data, konfirmasi lama gugur dan dapat dilakukan lagi.</div>
                                        <div class="row g-3" id="sinkStatus"></div>
                                    </div>
                                </div>
                            </div>

                            @if ($isAdmSales)
                                <div class="tab-pane fade" id="tab-pa" role="tabpanel">
                                    <div class="kd-head d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <h5>Payment Advance</h5>
                                            <div class="small kd-muted">Pantau Payment Advance terhadap penjualan bruto.
                                                Gunakan tab di bawah untuk berpindah antara ringkasan dan daftar prospect.</div>
                                        </div>
                                        <button type="button" class="btn btn-primary" id="btnLengkapiPA">Lengkapi data
                                            PA</button>
                                    </div>

                                    <ul class="nav kd-subtabs" id="paSubTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="sub-ringkasan-btn" data-bs-toggle="tab"
                                                data-bs-target="#sub-ringkasan" type="button" role="tab">Ringkasan &amp;
                                                Analisis</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="sub-daftar-btn" data-bs-toggle="tab"
                                                data-bs-target="#sub-daftar" type="button" role="tab">Daftar
                                                Prospect<span class="kd-count" id="cntDaftarPA"></span></button>
                                        </li>
                                    </ul>

                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="sub-ringkasan" role="tabpanel">
                                            <div class="row g-3 mb-4" id="ringkasanPA"></div>

                                            <div class="kd-toolbar">
                                                <div class="d-flex align-items-center flex-wrap gap-2">
                                                    <span class="small kd-muted">Periode</span>
                                                    <div class="kd-seg" id="granPA" role="group">
                                                        <button type="button" data-g="tahun" class="active">Tahunan</button>
                                                        <button type="button" data-g="kuartal">Kuartal</button>
                                                        <button type="button" data-g="bulan">Bulanan</button>
                                                        <button type="button" data-g="minggu">Mingguan</button>
                                                    </div>
                                                    <select id="tahunPA" class="form-select form-select-sm w-auto d-none"
                                                        aria-label="Tahun"></select>
                                                </div>
                                                <div class="small kd-muted" id="skopPA"></div>
                                            </div>

                                            <div class="row g-3 mb-3" id="kpiPA"></div>

                                            <div class="row g-3 mb-3">
                                                <div class="col-xl-8">
                                                    <div class="kd-card">
                                                        <div class="kd-card-head">
                                                            <span>Bruto vs PA per periode</span>
                                                            <span class="small kd-muted fw-normal">
                                                                <span class="kd-dot" style="background:#bfdbfe"></span>Bruto
                                                                <span class="kd-dot ms-2" style="background:#2563eb"></span>PA
                                                                <span class="kd-dot ms-2" style="background:#dc2626"></span>PA
                                                                melebihi bruto
                                                            </span>
                                                        </div>
                                                        <div id="grafikPA"></div>
                                                        <div class="small kd-muted mt-1 d-flex flex-wrap align-items-center gap-2">
                                                            <span>Klik batang untuk memfilter Daftar Prospect.</span>
                                                            <button type="button" class="btn btn-link btn-sm p-0 kd-detail"
                                                                id="btnKeDaftar">Buka Daftar Prospect</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-4">
                                                    <div class="kd-card" id="kpiMeter"></div>
                                                </div>
                                            </div>

                                            <div class="kd-card">
                                                <div class="kd-card-head"><span>Rincian per periode</span></div>
                                                <div class="table-responsive">
                                                    <table class="table kd-table w-100 mb-0" id="tabelPeriode"></table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="sub-daftar" role="tabpanel">
                                            <div class="kd-toolbar">
                                                <div class="kd-seg" id="filterListPA" role="group"></div>
                                                <div class="small kd-muted" id="skopListPA"></div>
                                            </div>

                                            <div class="kd-card">
                                                <div class="kd-card-head">
                                                    <span>Daftar prospect <span class="kd-chip d-none"
                                                            id="chipBucket"></span></span>
                                                </div>
                                                <div class="table-responsive">
                                                    <table id="tabelPA" class="table kd-table w-100">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th>Perusahaan</th>
                                                                <th>Materi</th>
                                                                <th>Periode kelas</th>
                                                                <th>Sales</th>
                                                                <th>Tahap</th>
                                                                <th>Status PA</th>
                                                                <th class="text-end">Jumlah PA</th>
                                                                <th class="text-end">Total PA</th>
                                                                <th class="text-end">Bruto</th>
                                                                <th class="text-end">PA / Bruto</th>
                                                                <th class="text-end">Sisa net sales</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($isAdmSales || $isFinance)
                    <div class="modal fade" id="detailSinkronModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title" id="detailSinkronJudul"></h5>
                                        <small class="text-muted" id="detailSinkronSub"></small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="detailSinkronPesan" class="alert d-none"></div>
                                    <div id="detailSinkronIsi"></div>
                                </div>
                                <div class="modal-footer">
                                    <small class="text-muted me-auto" id="detailSinkronInfo"></small>
                                    <a class="btn btn-outline-primary" id="detailSinkronLink" href="#">Buka approval</a>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                    <button type="button" class="btn btn-success" id="btnSinkSimpan">Simpan perubahan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($isAdmSales)
                    <div class="modal fade" id="perluLengkapiModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title">Prospect yang harus dilengkapi PA-nya</h5>
                                        <small class="text-muted" id="infoPerluLengkapi"></small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-light border small">
                                        Daftar ini berisi prospect yang sudah <strong>closed win</strong> tetapi belum
                                        memiliki
                                        Payment Advance, diurutkan dari yang paling lama menunggu.
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table kd-table w-100" id="tabelPerluLengkapi">
                                            <thead>
                                                <tr>
                                                    <th style="width:44px">No</th>
                                                    <th>Perusahaan</th>
                                                    <th>Materi</th>
                                                    <th>Periode kelas</th>
                                                    <th>Sales</th>
                                                    <th>Closed win</th>
                                                    <th class="text-end">Menunggu</th>
                                                    <th class="text-end">Harga</th>
                                                    <th class="text-end">Pax</th>
                                                    <th class="text-end">Net sales</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                    <button type="button" class="btn btn-primary" id="btnLengkapiDariDaftar">Lengkapi
                                        lewat import</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="importPaModal" data-bs-backdrop="static" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title">Lengkapi data Payment Advance</h5>
                                        <small class="text-muted" id="stepLabel">Langkah 1 dari 3: unggah file</small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Tutup"></button>
                                </div>

                                <div class="modal-body">
                                    <div id="pesanError" class="alert alert-danger d-none"></div>

                                    <div id="step1">
                                        <div class="row g-4">
                                            <div class="col-lg-6">
                                                <h6>Cara menggunakan</h6>
                                                <ol class="ps-3 mb-3">
                                                    <li class="mb-2"><strong>Unduh template</strong> di sebelah kanan.
                                                    </li>
                                                    <li class="mb-2">Buka sheet <em>Isi Data Di Sini</em>. Hapus baris
                                                        contoh (tulisan biru), lalu isi data mulai <strong>baris 4</strong>.
                                                        Satu baris untuk satu pengajuan PA.</li>
                                                    <li class="mb-2">Simpan file, pilih di kolom unggah, lalu klik
                                                        <strong>Periksa file</strong>. Belum ada data yang tersimpan pada
                                                        tahap ini.
                                                    </li>
                                                    <li class="mb-2">Cek hasil pemeriksaan. Data yang
                                                        <strong>sudah ada</strong> atau <strong>perlu ditinjau</strong>
                                                        menunggu keputusan Anda.
                                                    </li>
                                                    <li>Klik <strong>Proses import</strong> untuk menyimpan.</li>
                                                </ol>
                                                <div class="border rounded p-3 small bg-light">
                                                    <div class="mb-1"><strong>Perlu diketahui</strong></div>
                                                    <ul class="mb-0 ps-3">
                                                        <li>Prospect harus sudah ada di sistem. Import ini hanya menambahkan
                                                            Payment Advance ke prospect tersebut.</li>
                                                        <li>Prospect dicari dari nama perusahaan, nama materi, dan tanggal
                                                            kelas. Tulis sama seperti di sistem.</li>
                                                        <li>Jika ada prospect kembar, ID Sales atau Nama Lengkap Sales
                                                            dipakai
                                                            untuk membedakan.</li>
                                                        <li>Maksimal 500 baris per file. File bukti (foto/PDF) diunggah
                                                            manual
                                                            di halaman detail prospect.</li>
                                                        <li>Jika ada kesalahan saat menyimpan, semua data batal tersimpan.
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <h6>Template</h6>
                                                <a href="{{ route('kelengkapan-data.pa.template') }}"
                                                    class="btn btn-outline-primary mb-4">Unduh template</a>

                                                <h6>Unggah file</h6>
                                                <input type="file" class="form-control mb-3" id="fileImport"
                                                    accept=".xlsx">
                                                <button type="button" class="btn btn-primary" id="btnPeriksa">
                                                    <span class="spinner-border spinner-border-sm d-none me-1"></span>
                                                    Periksa file
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="step2" class="d-none">
                                        <div
                                            class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                            <div class="btn-group btn-group-sm" id="filterStatus" role="group"></div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    id="btnTolakSemuaTinjau">Jangan pakai semua yang perlu
                                                    ditinjau</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger d-none"
                                                    id="btnUnduhGagal">Unduh daftar yang gagal</button>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table kd-table w-100" id="tabelPreview">
                                                <thead>
                                                    <tr>
                                                        <th style="width:60px">Baris</th>
                                                        <th>Perusahaan / materi</th>
                                                        <th>Tgl PA</th>
                                                        <th class="text-end">Total PA</th>
                                                        <th style="width:140px">Status</th>
                                                        <th style="width:34%">Keterangan dan keputusan</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>

                                    <div id="step3" class="d-none text-center py-5">
                                        <h5 class="mb-2">Import selesai</h5>
                                        <p class="mb-1" id="hasilImport"></p>
                                    </div>
                                </div>

                                <div class="modal-footer justify-content-between">
                                    <div class="small text-muted" id="infoFooter"></div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-light d-none"
                                            id="btnKembali">Kembali</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                            id="btnTutup">Batal</button>
                                        <button type="button" class="btn btn-success d-none" id="btnProses" disabled>
                                            <span class="spinner-border spinner-border-sm d-none me-1"></span>
                                            Proses import
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($isAdmSales || $isFinance)
        <style>
            .kd {
                --kd-ink: #0f172a;
                --kd-muted: #64748b;
                --kd-line: #e2e8f0;
                --kd-soft: #f8fafc;
                --kd-blue: #2563eb;
            }

            .kd-muted {
                color: var(--kd-muted);
            }

            .kd-shell {
                background: #fff;
                border: 1px solid var(--kd-line);
                border-radius: 12px;
                padding: .5rem 1.5rem 1.5rem;
                color: var(--kd-ink);
                box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
            }

            .kd-tabs {
                border-bottom: 1px solid var(--kd-line);
                margin-bottom: 1.5rem;
            }

            .kd-tabs .nav-link {
                border: 0;
                border-bottom: 2px solid transparent;
                border-radius: 0;
                background: none;
                color: var(--kd-muted);
                padding: .75rem 1rem;
                margin-bottom: -1px;
            }

            .kd-tabs .nav-link:hover {
                color: var(--kd-ink);
            }

            .kd-tabs .nav-link.active {
                color: var(--kd-blue);
                border-bottom-color: var(--kd-blue);
                font-weight: 600;
                background: none;
            }

            .kd-subtabs {
                display: inline-flex;
                flex-wrap: nowrap;
                gap: 4px;
                padding: 4px;
                margin-bottom: 1.25rem;
                background: #f1f5f9;
                border-radius: 10px;
            }

            .kd-subtabs .nav-link {
                border: 0;
                border-radius: 7px;
                background: none;
                color: var(--kd-muted);
                padding: .45rem 1.1rem;
                font-size: .9rem;
                white-space: nowrap;
            }

            .kd-subtabs .nav-link:hover {
                color: var(--kd-ink);
            }

            .kd-subtabs .nav-link.active {
                background: #fff;
                color: var(--kd-blue);
                font-weight: 600;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .15);
            }

            .kd-subtabs .kd-count {
                margin-left: .45rem;
                padding: .05rem .5rem;
                border-radius: 999px;
                background: #e2e8f0;
                color: #475569;
                font-size: .72rem;
                font-weight: 600;
            }

            .kd-subtabs .kd-count:empty {
                display: none;
            }

            .kd-head h5 {
                font-weight: 600;
                margin: 0 0 .15rem;
            }

            .kd-stat {
                position: relative;
                height: 100%;
                overflow: hidden;
                background: var(--kd-soft);
                border: 1px solid var(--kd-line);
                border-radius: 10px;
                padding: .9rem 1rem .9rem 1.35rem;
            }

            .kd-stat::before {
                content: "";
                position: absolute;
                inset: 0 auto 0 0;
                width: 4px;
                background: var(--kd-accent, #64748b);
            }

            .kd-stat-label {
                font-size: .85rem;
                color: var(--kd-muted);
            }

            .kd-stat-num {
                font-size: 1.9rem;
                font-weight: 600;
                line-height: 1.25;
                color: var(--kd-ink);
            }

            .kd-stat-sub {
                font-size: .78rem;
                color: var(--kd-muted);
                min-height: 1.1rem;
            }

            .kd-legend {
                margin-top: .55rem;
                padding-top: .55rem;
                border-top: 1px dashed var(--kd-line);
                font-size: .76rem;
                color: var(--kd-muted);
            }

            .kd-legend>div {
                display: flex;
                align-items: flex-start;
                gap: .45rem;
                margin-bottom: .4rem;
            }

            .kd-legend>div:last-child {
                margin-bottom: 0;
            }

            .kd-legend .kd-badge {
                flex: none;
                font-size: .68rem;
                padding: .12rem .5rem;
            }

            .kd-seg {
                display: inline-flex;
                flex-wrap: wrap;
                gap: 2px;
                padding: 3px;
                background: #f1f5f9;
                border-radius: 9px;
            }

            .kd-seg button {
                border: 0;
                background: transparent;
                color: var(--kd-muted);
                padding: .3rem .85rem;
                border-radius: 7px;
                font-size: .85rem;
            }

            .kd-seg button:hover {
                color: var(--kd-ink);
            }

            .kd-seg button.active {
                background: #fff;
                color: var(--kd-ink);
                font-weight: 600;
                box-shadow: 0 1px 2px rgba(15, 23, 42, .15);
            }

            .kd-seg .kd-count {
                margin-left: .4rem;
                font-size: .78rem;
                color: var(--kd-muted);
                font-weight: 400;
            }

            .kd .kd-table {
                font-size: .875rem;
                border-collapse: collapse;
                color: var(--kd-ink);
            }

            table.dataTable.kd-table thead th,
            .kd .kd-table thead th {
                background: var(--kd-soft) !important;
                background-image: none !important;
                color: #475569;
                font-size: .8rem;
                font-weight: 600;
                white-space: nowrap;
                padding: .7rem .75rem;
                border-top: 1px solid var(--kd-line);
                border-bottom: 1px solid var(--kd-line) !important;
            }

            table.dataTable.kd-table thead th.sorting::after {
                content: "\2195";
                margin-left: .4rem;
                color: #cbd5e1;
            }

            table.dataTable.kd-table thead th.sorting_asc::after {
                content: "\25B2";
                margin-left: .4rem;
                font-size: .6rem;
                color: var(--kd-blue);
            }

            table.dataTable.kd-table thead th.sorting_desc::after {
                content: "\25BC";
                margin-left: .4rem;
                font-size: .6rem;
                color: var(--kd-blue);
            }

            table.dataTable.kd-table thead th.sorting,
            table.dataTable.kd-table thead th.sorting_asc,
            table.dataTable.kd-table thead th.sorting_desc {
                background-image: none !important;
            }

            table.dataTable.kd-table.no-footer {
                border-bottom: 1px solid var(--kd-line);
            }

            .kd .kd-table tbody td {
                padding: .7rem .75rem;
                border-bottom: 1px solid #eef2f6;
                vertical-align: middle;
                background: transparent;
            }

            .kd .kd-table tbody tr:hover>td {
                background: var(--kd-soft);
            }

            .kd .kd-table td.kontrol-detail {
                width: 36px;
                text-align: center;
                cursor: pointer;
                color: var(--kd-blue);
                font-weight: 600;
                user-select: none;
            }

            .kd-detail {
                color: var(--kd-blue);
                text-decoration: none;
                font-weight: 500;
            }

            .kd-detail:hover {
                text-decoration: underline;
            }

            .kd-badge {
                display: inline-block;
                padding: .2rem .65rem;
                border-radius: 999px;
                font-size: .75rem;
                font-weight: 600;
                white-space: nowrap;
            }

            .kd-badge--ok {
                background: #dcfce7;
                color: #166534;
            }

            .kd-badge--warn {
                background: #fee2e2;
                color: #991b1b;
            }

            .kd-badge--muted {
                background: #f1f5f9;
                color: #475569;
                text-transform: capitalize;
            }

            .kd-child {
                background: var(--kd-soft);
                border: 1px solid var(--kd-line);
                border-radius: 8px;
                padding: .5rem .75rem;
                margin: .25rem 0 .5rem 2.25rem;
            }

            .kd-child table {
                background: transparent;
                margin: 0;
            }

            .kd .dataTables_wrapper .dataTables_length,
            .kd .dataTables_wrapper .dataTables_filter,
            .modal .dataTables_wrapper .dataTables_length,
            .modal .dataTables_wrapper .dataTables_filter {
                margin-bottom: .9rem;
                color: var(--kd-muted);
                font-size: .875rem;
            }

            .kd .dataTables_wrapper .dataTables_filter input,
            .kd .dataTables_wrapper .dataTables_length select,
            .modal .dataTables_wrapper .dataTables_filter input,
            .modal .dataTables_wrapper .dataTables_length select {
                border: 1px solid var(--kd-line);
                border-radius: 8px;
                padding: .35rem .65rem;
                background: #fff;
                color: var(--kd-ink);
            }

            .kd .dataTables_wrapper .dataTables_info,
            .modal .dataTables_wrapper .dataTables_info {
                color: var(--kd-muted);
                font-size: .82rem;
                padding-top: 1rem;
            }

            .kd .dataTables_wrapper .dataTables_paginate,
            .modal .dataTables_wrapper .dataTables_paginate {
                padding-top: .75rem;
            }

            .kd .dataTables_wrapper .dataTables_paginate .paginate_button,
            .modal .dataTables_wrapper .dataTables_paginate .paginate_button {
                border-radius: 6px;
                border: 1px solid transparent !important;
            }

            .kd .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .kd .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
            .modal .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .modal .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: var(--kd-blue) !important;
                color: #fff !important;
                border-color: var(--kd-blue) !important;
            }

            #tabelPreview tr.baris-gagal>td {
                background: #fef2f2;
            }

            #tabelPreview tr.baris-tinjau>td {
                background: #fffbeb;
            }

            .kd-badge--info {
                background: #dbeafe;
                color: #1e40af;
            }

            .kd-sink-row {
                cursor: pointer;
            }

            .kd-beda {
                background: #fef2f2;
                color: #991b1b;
                font-weight: 600;
            }

            .kd-peran {
                height: 100%;
                background: #fff;
                border: 1px solid var(--kd-line);
                border-radius: 10px;
                padding: .9rem 1rem;
            }

            .kd-view {
                cursor: pointer;
                transition: border-color .15s, box-shadow .15s, background .15s;
            }

            .kd-view:hover {
                border-color: var(--kd-accent);
            }

            .kd-view.active {
                background: #fff;
                border-color: var(--kd-accent);
                box-shadow: 0 0 0 1px var(--kd-accent), 0 4px 12px rgba(15, 23, 42, .08);
            }

            .kd-view:focus-visible {
                outline: 2px solid var(--kd-blue);
                outline-offset: 2px;
            }

            .kd-num-md {
                font-size: 1.45rem;
            }

            .kd-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                margin-bottom: 1rem;
            }

            .kd-card {
                background: #fff;
                border: 1px solid var(--kd-line);
                border-radius: 10px;
                padding: 1rem 1.1rem;
                height: 100%;
            }

            .kd-card-head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: .5rem;
                margin-bottom: .75rem;
                font-weight: 600;
            }

            .kd-dot {
                display: inline-block;
                width: 10px;
                height: 10px;
                border-radius: 3px;
                margin-right: .3rem;
                vertical-align: baseline;
            }

            .kd-chart {
                width: 100%;
                height: auto;
                display: block;
            }

            .kd-bar {
                cursor: pointer;
            }

            .kd-bar:hover .kd-hit {
                fill: #f1f5f9;
            }

            .kd-meter {
                position: relative;
                height: 12px;
                background: #f1f5f9;
                border-radius: 999px;
            }

            .kd-meter-fill {
                height: 100%;
                border-radius: 999px;
            }

            .kd-meter-fill.ok {
                background: #16a34a;
            }

            .kd-meter-fill.bad {
                background: #dc2626;
            }

            .kd-meter-batas {
                position: absolute;
                top: -4px;
                bottom: -4px;
                width: 2px;
                background: var(--kd-ink);
            }

            .kd-mini {
                display: inline-block;
                width: 70px;
                height: 6px;
                background: #f1f5f9;
                border-radius: 999px;
                vertical-align: middle;
                margin-right: .5rem;
                overflow: hidden;
            }

            .kd-mini>span {
                display: block;
                height: 100%;
                background: var(--kd-blue);
            }

            .kd-mini.bad>span {
                background: #dc2626;
            }

            .kd-ins {
                display: flex;
                justify-content: space-between;
                gap: .75rem;
                padding: .45rem 0;
                border-bottom: 1px dashed var(--kd-line);
                font-size: .85rem;
            }

            .kd-ins:last-child {
                border-bottom: 0;
            }

            .kd-rank {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .kd-rank li {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: .5rem;
                padding: .4rem 0;
                border-bottom: 1px dashed var(--kd-line);
                font-size: .85rem;
            }

            .kd-rank li:last-child {
                border-bottom: 0;
            }

            .kd-chip {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                margin-left: .5rem;
                padding: .15rem .6rem;
                border-radius: 999px;
                background: #dbeafe;
                color: #1e40af;
                font-size: .75rem;
                font-weight: 600;
            }

            .kd-chip button {
                border: 0;
                background: none;
                color: inherit;
                padding: 0;
                line-height: 1;
                cursor: pointer;
            }

            #tabelPeriode tbody tr[data-key] {
                cursor: pointer;
            }

            #tabelPeriode tbody tr.aktif>td {
                background: #eff6ff;
            }

            #tabelPeriode tfoot td {
                font-weight: 600;
                background: var(--kd-soft);
                border-top: 1px solid var(--kd-line);
                padding: .7rem .75rem;
            }

            #tabelPA tr.baris-lebih>td {
                background: #fef2f2;
            }

            .kd-filterbar {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-end;
                gap: .75rem;
                padding: .75rem 1rem;
                background: var(--kd-soft);
                border: 1px solid var(--kd-line);
                border-radius: 10px;
            }

            .kd-filterbar label {
                display: block;
                font-size: .78rem;
                color: var(--kd-muted);
                margin-bottom: .2rem;
            }

            .kd-strip {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: .5rem 1.5rem;
                padding: .65rem 1rem;
                background: #fff;
                border: 1px solid var(--kd-line);
                border-radius: 10px;
            }

            .kd-strip-item {
                display: flex;
                align-items: baseline;
                gap: .45rem;
                padding-left: .75rem;
                border-left: 3px solid var(--kd-accent, #64748b);
            }

            .kd-strip-num {
                font-size: 1.25rem;
                font-weight: 600;
                line-height: 1.2;
            }

            .kd-strip-label {
                font-size: .82rem;
                color: var(--kd-muted);
            }

            .kd-strip-end {
                margin-left: auto;
                display: flex;
                align-items: center;
                gap: .75rem;
            }

            .kd-catatan {
                border-top: 1px solid var(--kd-line);
                padding-top: .75rem;
            }

            .kd-note {
                padding: .5rem .75rem;
                margin-bottom: .5rem;
                background: var(--kd-soft);
                border: 1px solid var(--kd-line);
                border-radius: 8px;
                font-size: .875rem;
            }

            .kd-note-teks {
                white-space: pre-wrap;
                word-break: break-word;
            }
        </style>
    @endif

    @push('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.3.1/css/rowGroup.dataTables.min.css">
        <script src="https://cdn.datatables.net/rowgroup/1.3.1/js/dataTables.rowGroup.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        @if ($isAdmSales)
            <script>
                $(function() {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const URL_APP = {
                        data: "{{ route('kelengkapan-data.pa.data') }}",
                        preview: "{{ route('kelengkapan-data.pa.preview') }}",
                        commit: "{{ route('kelengkapan-data.pa.commit') }}",
                        detailPeluang: "{{ url('/crm/peluang/detail') }}"
                    };

                    const BAHASA = {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Belum ada data',
                        infoFiltered: '(disaring dari _MAX_ data)',
                        zeroRecords: 'Data tidak ditemukan',
                        emptyTable: 'Belum ada data.',
                        paginate: {
                            previous: 'Sebelumnya',
                            next: 'Berikutnya'
                        }
                    };

                    const STATUS = {
                        siap: {
                            teks: 'Siap diimpor',
                            kelas: 'success'
                        },
                        duplikat: {
                            teks: 'Sudah ada',
                            kelas: 'secondary'
                        },
                        tinjau: {
                            teks: 'Perlu ditinjau',
                            kelas: 'warning'
                        },
                        gagal: {
                            teks: 'Tidak dapat diproses',
                            kelas: 'danger'
                        }
                    };

                    const rp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
                    const esc = s => $('<div>').text(s ?? '').html();

                    const fmtPeriode = v => {
                        const m = String(v ?? '').match(/^(\d{4})-(\d{2})-(\d{2}) s\/d (\d{4})-(\d{2})-(\d{2})$/);
                        if (!m) return esc(v);
                        const a = new Date(+m[1], +m[2] - 1, +m[3]),
                            z = new Date(+m[4], +m[5] - 1, +m[6]);
                        const f = (d, thn) => d.toLocaleDateString('id-ID', thn ? {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        } : {
                            day: 'numeric',
                            month: 'short'
                        });
                        if (a.getTime() === z.getTime()) return f(z, true);
                        return `${f(a, m[1] !== m[4])} \u2013 ${f(z, true)}`;
                    };

                    const UMUR_PERINGATAN_HARI = 30;
                    let tabelPA = null,
                        tabelPerlu = null,
                        tabelPreview = null,
                        dataProspect = [],
                        filterPA = 'semua',
                        stateSiap = false;

                    const STATE_KEY = 'kd_state';
                    const PANJANG_HALAMAN = [10, 25, 50, 100];
                    const FILTER_VALID = ['semua', 'sudah', 'belum', 'wajib'];

                    function bacaState() {
                        const sumber = location.search || sessionStorage.getItem(STATE_KEY) || '';
                        const p = new URLSearchParams(sumber);
                        const page = parseInt(p.get('page'), 10),
                            len = parseInt(p.get('len'), 10);
                        return {
                            tab: p.get('tab') === 'pa' ? 'pa' : 'utama',
                            sub: p.get('sub') === 'daftar' ? 'daftar' : 'ringkasan',
                            page: page > 0 ? page : 1,
                            len: PANJANG_HALAMAN.includes(len) ? len : 25,
                            filter: FILTER_VALID.includes(p.get('filter')) ? p.get('filter') : 'semua',
                            q: p.get('q') || ''
                        };
                    }

                    function simpanState() {
                        const tabPA = $('#tab-pa-btn').hasClass('active');
                        if (!tabPA) {
                            try {
                                sessionStorage.setItem(STATE_KEY, '');
                            } catch (e) {}
                            return;
                        }
                        if (!stateSiap) return;
                        const p = new URLSearchParams();
                        if (tabPA) {
                            p.set('tab', 'pa');
                            if ($('#sub-daftar-btn').hasClass('active')) p.set('sub', 'daftar');
                            if (tabelPA) {
                                const info = tabelPA.page.info();
                                if (info.page > 0) p.set('page', info.page + 1);
                                if (info.length !== 25) p.set('len', info.length);
                                if (tabelPA.search()) p.set('q', tabelPA.search());
                            }
                            if (filterPA !== 'semua') p.set('filter', filterPA);
                        }
                        const qs = p.toString() ? '?' + p.toString() : '';
                        history.replaceState(history.state, '', location.pathname + qs + location.hash);
                        try {
                            sessionStorage.setItem(STATE_KEY, qs);
                        } catch (e) {}
                    }

                    $('#kelengkapanTab button[data-bs-toggle="tab"]').on('shown.bs.tab', simpanState);

                    $('#tab-pa-btn').on('shown.bs.tab', function() {
                        if (!tabelPA) initTabelPA();
                        else tabelPA.columns.adjust();
                    });

                    $('#paSubTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
                        if (tabelPA) tabelPA.columns.adjust();
                        simpanState();
                    });

                    $(document).on('click', '#btnKeDaftar', function() {
                        bootstrap.Tab.getOrCreateInstance(document.getElementById('sub-daftar-btn')).show();
                    });

                    let granPA = 'tahun',
                        tahunPA = new Date().getFullYear(),
                        bucketPA = null;

                    const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const NAMA_VIEW = {
                        semua: 'Semua prospect',
                        sudah: 'Sudah ada PA',
                        belum: 'Belum ada PA',
                        wajib: 'Harus dilengkapi'
                    };
                    const WARNA = {
                        bruto: '#bfdbfe',
                        pa: '#2563eb',
                        lebih: '#dc2626'
                    };

                    const pad = n => String(n).padStart(2, '0');
                    const ptgl = s => {
                        const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
                        return m ? {
                            y: +m[1],
                            m: +m[2],
                            d: +m[3]
                        } : null;
                    };
                    const pct = (a, b) => b > 0 ? a / b * 100 : (a > 0 ? Infinity : 0);
                    const fmtPct = v => isFinite(v) ? v.toFixed(1).replace('.', ',') + '%' : 'Tanpa bruto';
                    const ringkas = n => {
                        const a = Math.abs(n);
                        if (a >= 1e9) return (n / 1e9).toFixed(1).replace('.', ',') + ' M';
                        if (a >= 1e6) return Math.round(n / 1e6) + ' jt';
                        if (a >= 1e3) return Math.round(n / 1e3) + ' rb';
                        return String(Math.round(n));
                    };

                    const cocokView = d =>
                        filterPA === 'semua' ||
                        (filterPA === 'sudah' && d.punya_pa) ||
                        (filterPA === 'belum' && !d.punya_pa) ||
                        (filterPA === 'wajib' && d.perlu_lengkapi);

                    function bucketDari(t, g) {
                        if (g === 'tahun') return {
                            key: String(t.y),
                            label: String(t.y),
                            sub: ''
                        };
                        if (g === 'kuartal') {
                            const q = Math.ceil(t.m / 3);
                            return {
                                key: `${t.y}-Q${q}`,
                                label: `Q${q}`,
                                sub: String(t.y)
                            };
                        }
                        if (g === 'bulan') return {
                            key: `${t.y}-${pad(t.m)}`,
                            label: BLN[t.m - 1],
                            sub: String(t.y)
                        };

                        const u = new Date(Date.UTC(t.y, t.m - 1, t.d));
                        u.setUTCDate(u.getUTCDate() - (u.getUTCDay() || 7) + 1);
                        const th = new Date(u);
                        th.setUTCDate(u.getUTCDate() + 3);
                        const mg = Math.ceil(((th - Date.UTC(th.getUTCFullYear(), 0, 1)) / 86400000 + 1) / 7);
                        return {
                            key: `${u.getUTCFullYear()}-${pad(u.getUTCMonth() + 1)}-${pad(u.getUTCDate())}`,
                            label: 'Mg ' + mg,
                            sub: `${u.getUTCDate()} ${BLN[u.getUTCMonth()]}`
                        };
                    }

                    const bucketProspect = d => {
                        const t = ptgl(d.tgl_kelas);
                        return t ? bucketDari(t, granPA) : null;
                    };

                    function kerangkaBucket() {
                        const m = new Map();
                        const tambah = t => {
                            const b = bucketDari(t, granPA);
                            if (!m.has(b.key)) m.set(b.key, {
                                ...b,
                                bruto: 0,
                                pa: 0,
                                n: 0,
                                lebih: 0
                            });
                        };
                        if (granPA === 'kuartal') [1, 4, 7, 10].forEach(b => tambah({
                            y: tahunPA,
                            m: b,
                            d: 1
                        }));
                        else if (granPA === 'bulan')
                            for (let b = 1; b <= 12; b++) tambah({
                                y: tahunPA,
                                m: b,
                                d: 1
                            });
                        else if (granPA === 'minggu') {
                            const u = new Date(Date.UTC(tahunPA, 0, 1));
                            while (u.getUTCFullYear() === tahunPA) {
                                tambah({
                                    y: tahunPA,
                                    m: u.getUTCMonth() + 1,
                                    d: u.getUTCDate()
                                });
                                u.setUTCDate(u.getUTCDate() + 7);
                            }
                        }
                        return m;
                    }

                    function dataSkop() {
                        return dataProspect.filter(d => {
                            const t = ptgl(d.tgl_kelas);
                            return t && (granPA === 'tahun' || t.y === tahunPA);
                        });
                    }

                    function susunBucket(daftar) {
                        const m = kerangkaBucket();
                        daftar.forEach(d => {
                            const b = bucketProspect(d);
                            if (!m.has(b.key)) m.set(b.key, {
                                ...b,
                                bruto: 0,
                                pa: 0,
                                n: 0,
                                lebih: 0
                            });
                            const x = m.get(b.key);
                            x.bruto += d.bruto;
                            x.pa += d.total_pa;
                            x.n++;
                            if (d.melebihi) x.lebih++;
                        });
                        return [...m.values()].sort((a, b) => a.key < b.key ? -1 : a.key > b.key ? 1 : 0);
                    }

                    function gambarGrafik(bs) {
                        if (!bs.some(b => b.bruto > 0 || b.pa > 0)) {
                            return '<div class="text-center kd-muted py-5">Belum ada data pada periode ini.</div>';
                        }
                        const W = 900,
                            H = 300,
                            L = 64,
                            R = 12,
                            T = 14,
                            B = 44,
                            pw = W - L - R,
                            ph = H - T - B;
                        const maks = Math.max(1, ...bs.map(b => Math.max(b.bruto, b.pa)));
                        const y = v => T + ph - v / maks * ph;
                        const gw = pw / bs.length,
                            bw = Math.min(gw * 0.36, 44),
                            lewati = Math.ceil(bs.length / 14);

                        let s = `<svg viewBox="0 0 ${W} ${H}" class="kd-chart" role="img" aria-label="Grafik bruto dibanding PA per periode">`;
                        for (let i = 0; i <= 4; i++) {
                            const yy = y(maks * i / 4);
                            s += `<line x1="${L}" x2="${W - R}" y1="${yy}" y2="${yy}" stroke="#e2e8f0"/>` +
                                `<text x="${L - 8}" y="${yy + 4}" text-anchor="end" font-size="11" fill="#64748b">${ringkas(maks * i / 4)}</text>`;
                        }
                        bs.forEach((b, i) => {
                            const x0 = L + i * gw,
                                cx = x0 + gw / 2,
                                aktif = b.key === bucketPA;
                            const judul = `${b.label} ${b.sub}\nProspect: ${b.n}\nBruto: ${rp(b.bruto)}\nPA: ${rp(b.pa)}\nPA/Bruto: ${fmtPct(pct(b.pa, b.bruto))}`;
                            s += `<g class="kd-bar" data-key="${esc(b.key)}"><title>${esc(judul)}</title>` +
                                `<rect class="kd-hit" x="${x0}" y="${T}" width="${gw}" height="${ph + B - 8}" fill="${aktif ? '#dbeafe' : 'transparent'}"/>` +
                                `<rect x="${cx - bw - 1}" y="${y(b.bruto)}" width="${bw}" height="${T + ph - y(b.bruto)}" rx="2" fill="${WARNA.bruto}"/>` +
                                `<rect x="${cx + 1}" y="${y(b.pa)}" width="${bw}" height="${T + ph - y(b.pa)}" rx="2" fill="${b.pa > b.bruto ? WARNA.lebih : WARNA.pa}"/>`;
                            if (i % lewati === 0) {
                                s += `<text x="${cx}" y="${H - 26}" text-anchor="middle" font-size="11" fill="#475569">${esc(b.label)}</text>`;
                                if (b.sub) s += `<text x="${cx}" y="${H - 12}" text-anchor="middle" font-size="10" fill="#94a3b8">${esc(b.sub)}</text>`;
                            }
                            s += '</g>';
                        });
                        return s + '</svg>';
                    }

                    function renderAnalitik() {
                        const tahunAda = [...new Set(dataProspect.map(d => (ptgl(d.tgl_kelas) || {}).y).filter(Boolean))];
                        if (!tahunAda.includes(tahunPA)) tahunAda.push(tahunPA);
                        tahunAda.sort((a, b) => b - a);
                        $('#tahunPA').html(tahunAda.map(t => `<option value="${t}">${t}</option>`).join('')).val(tahunPA)
                            .toggleClass('d-none', granPA === 'tahun');

                        const skop = dataSkop();
                        const labelSkop = granPA === 'tahun' ? 'Semua tahun' : 'Tahun ' + tahunPA;
                        $('#skopPA').text(`${labelSkop} \u00B7 ${NAMA_VIEW[filterPA]}`);
                        $('#skopListPA').text(`${labelSkop} \u00B7 periode diatur di tab Ringkasan & Analisis`);

                        const hv = {
                            semua: skop.length,
                            sudah: skop.filter(d => d.punya_pa).length,
                            belum: skop.filter(d => !d.punya_pa).length,
                            wajib: skop.filter(d => d.perlu_lengkapi).length
                        };

                        $('#cntDaftarPA').text(hv.semua);
                        $('#filterListPA').html(Object.keys(NAMA_VIEW).map(k =>
                            `<button type="button" data-v="${k}" class="${k === filterPA ? 'active' : ''}">${NAMA_VIEW[k]}<span class="kd-count">${hv[k]}</span></button>`
                        ).join(''));

                        const kartu = (k, judul, sub, warna, extra = '') => `
                            <div class="col-6 col-xl-3"><div class="kd-stat kd-view${k === filterPA ? ' active' : ''}" data-view="${k}"
                                role="button" tabindex="0" aria-pressed="${k === filterPA}" style="--kd-accent:${warna}">
                                <div class="kd-stat-label">${judul}</div>
                                <div class="kd-stat-num">${hv[k]}</div>
                                <div class="kd-stat-sub">${sub}</div>${extra}</div></div>`;

                        const legenda = `
                            <div class="kd-legend">
                                <div><span class="kd-badge kd-badge--warn">Belum ada PA</span>
                                    <span><strong>${hv.wajib}</strong> prospect. Merah: sudah closed win, PA wajib dilengkapi.</span></div>
                                <div><span class="kd-badge kd-badge--muted">Belum ada PA</span>
                                    <span><strong>${hv.belum - hv.wajib}</strong> prospect. Abu-abu: belum closed win, PA belum wajib.</span></div>
                            </div>`;

                        $('#ringkasanPA').html(
                            kartu('semua', 'Prospect berjalan', 'Di luar prospect lost', '#2563eb') +
                            kartu('sudah', 'Sudah ada PA', `${hv.semua ? Math.round(hv.sudah / hv.semua * 100) : 0}% dari seluruh prospect`, '#16a34a') +
                            kartu('belum', 'Belum ada PA', 'Belum memiliki Payment Advance', '#64748b', legenda) +
                            kartu('wajib', 'Harus dilengkapi', 'Closed win tanpa PA', hv.wajib > 0 ? '#dc2626' : '#16a34a',
                                `<button type="button" class="btn btn-sm btn-outline-danger mt-2" id="btnLihatPerlu" ${dataProspect.some(d => d.perlu_lengkapi) ? '' : 'disabled'}>Lihat daftar lengkap</button>`)
                        );

                        const data = skop.filter(cocokView);
                        const bs = susunBucket(data);
                        const agg = data.reduce((a, d) => {
                            a.bruto += d.bruto;
                            a.pa += d.total_pa;
                            if (d.total_pa > 0) a.nPA++;
                            if (d.melebihi) a.lebih++;
                            return a;
                        }, {
                            bruto: 0,
                            pa: 0,
                            nPA: 0,
                            lebih: 0
                        });
                        const r = pct(agg.pa, agg.bruto),
                            ok = agg.pa <= agg.bruto;

                        const kpi = (judul, nilai, sub, warna) => `
                            <div class="col-6 col-xl-3"><div class="kd-stat" style="--kd-accent:${warna}">
                                <div class="kd-stat-label">${judul}</div>
                                <div class="kd-stat-num kd-num-md">${nilai}</div>
                                <div class="kd-stat-sub">${sub}</div></div></div>`;

                        $('#kpiPA').html(
                            kpi('Total bruto', rp(agg.bruto), `${data.length} prospect (harga \u00D7 pax)`, '#2563eb') +
                            kpi('Total PA', rp(agg.pa), `${agg.nPA} prospect memiliki PA`, '#d97706') +
                            kpi('Rasio PA / Bruto', fmtPct(r), ok ? 'Di bawah batas 100%' : 'Melebihi batas 100%', ok ? '#16a34a' : '#dc2626') +
                            kpi(ok ? 'Sisa ruang PA' : 'Kelebihan PA', rp(Math.abs(agg.bruto - agg.pa)),
                                `${agg.lebih} prospect PA-nya melebihi bruto`, agg.lebih ? '#dc2626' : '#16a34a')
                        );

                        const skala = isFinite(r) ? Math.max(r, 100) : 200;
                        const aktifBs = bs.filter(b => b.n > 0);
                        const periodeOk = aktifBs.filter(b => b.pa <= b.bruto).length;
                        const tertinggi = aktifBs.reduce((m, b) => (!m || b.pa > m.pa) ? b : m, null);
                        const risiko = data.filter(d => d.total_pa > 0)
                            .map(d => ({
                                d,
                                r: pct(d.total_pa, d.bruto)
                            })).sort((a, b) => b.r - a.r).slice(0, 5);

                        $('#kpiMeter').html(`
                            <div class="kd-card-head"><span>KPI: PA tidak melebihi bruto</span>
                                <span class="kd-badge kd-badge--${ok ? 'ok' : 'warn'}">${ok ? 'Terpenuhi' : 'Melebihi bruto'}</span></div>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <span style="font-size:1.8rem;font-weight:600">${fmtPct(r)}</span>
                                <span class="small kd-muted">dari bruto terpakai PA</span></div>
                            <div class="kd-meter mb-1">
                                <div class="kd-meter-fill ${ok ? 'ok' : 'bad'}" style="width:${Math.min(r / skala * 100, 100)}%"></div>
                                <span class="kd-meter-batas" style="left:${100 / skala * 100}%" title="Batas 100%"></span></div>
                            <div class="small kd-muted mb-3">Garis hitam = batas 100% dari bruto.</div>
                            <div class="kd-ins"><span class="kd-muted">Periode memenuhi KPI</span><strong>${periodeOk} / ${aktifBs.length}</strong></div>
                            <div class="kd-ins"><span class="kd-muted">Rata-rata PA per prospect ber-PA</span><strong>${rp(agg.nPA ? agg.pa / agg.nPA : 0)}</strong></div>
                            <div class="kd-ins"><span class="kd-muted">PA tertinggi</span><strong>${tertinggi && tertinggi.pa > 0 ? esc(tertinggi.label + ' ' + tertinggi.sub).trim() : '-'}</strong></div>
                            <div class="small fw-semibold mt-3 mb-1">Rasio PA tertinggi</div>
                            ${risiko.length ? `<ul class="kd-rank">${risiko.map(x => `
                                <li><span class="text-truncate"><a class="kd-detail" href="${URL_APP.detailPeluang}/${x.d.id_peluang}">${esc(x.d.perusahaan)}</a>
                                    <span class="kd-muted small d-block">${esc(x.d.materi)}</span></span>
                                    <span class="kd-badge kd-badge--${x.r > 100 ? 'warn' : 'muted'}">${fmtPct(x.r)}</span></li>`).join('')}</ul>`
                                : '<div class="small kd-muted">Belum ada prospect dengan PA.</div>'}
                        `);

                        $('#grafikPA').html(gambarGrafik(bs));

                        const baris = aktifBs.map(b => {
                            const rr = pct(b.pa, b.bruto),
                                aman = b.pa <= b.bruto;
                            const badge = b.pa === 0 ? '<span class="kd-badge kd-badge--muted">Belum ada PA</span>' :
                                aman ? '<span class="kd-badge kd-badge--ok">Aman</span>' : '<span class="kd-badge kd-badge--warn">Melebihi</span>';
                            return `<tr data-key="${esc(b.key)}" class="${b.key === bucketPA ? 'aktif' : ''}">
                                <td class="fw-semibold">${esc(b.label)} <span class="kd-muted fw-normal">${esc(b.sub)}</span></td>
                                <td class="text-end">${b.n}</td>
                                <td class="text-end text-nowrap">${rp(b.bruto)}</td>
                                <td class="text-end text-nowrap">${rp(b.pa)}</td>
                                <td class="text-end text-nowrap"><span class="kd-mini${aman ? '' : ' bad'}"><span style="width:${Math.min(isFinite(rr) ? rr : 100, 100)}%"></span></span>${fmtPct(rr)}</td>
                                <td>${badge}</td></tr>`;
                        }).join('');
                        $('#tabelPeriode').html(`
                            <thead><tr><th>Periode</th><th class="text-end">Prospect</th><th class="text-end">Bruto</th>
                                <th class="text-end">PA</th><th class="text-end">PA / Bruto</th><th>Status</th></tr></thead>
                            <tbody>${baris || '<tr><td colspan="6" class="text-center kd-muted py-4">Belum ada data pada periode ini.</td></tr>'}</tbody>
                            ${baris ? `<tfoot><tr><td>Total</td><td class="text-end">${data.length}</td><td class="text-end text-nowrap">${rp(agg.bruto)}</td>
                                <td class="text-end text-nowrap">${rp(agg.pa)}</td><td class="text-end">${fmtPct(r)}</td><td></td></tr></tfoot>` : ''}`);

                        const bAktif = bs.find(b => b.key === bucketPA);
                        $('#chipBucket').toggleClass('d-none', !bAktif).html(bAktif ?
                            `${esc(bAktif.label)} ${esc(bAktif.sub)} <button type="button" id="resetBucket" aria-label="Hapus filter periode">&times;</button>` : '');
                    }

                    function segarkan() {
                        renderAnalitik();
                        if (tabelPA) tabelPA.draw();
                    }

                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        if (settings.nTable.id !== 'tabelPA') return true;
                        const d = settings.aoData[dataIndex]._aData;
                        if (!cocokView(d)) return false;
                        if (granPA !== 'tahun') {
                            const t = ptgl(d.tgl_kelas);
                            if (!t || t.y !== tahunPA) return false;
                        }
                        if (bucketPA) {
                            const b = bucketProspect(d);
                            if (!b || b.key !== bucketPA) return false;
                        }
                        return true;
                    });

                    $(document).on('click keydown', '.kd-view', function(e) {
                        if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
                        if ($(e.target).closest('button').length) return;
                        e.preventDefault();
                        filterPA = $(this).data('view');
                        bucketPA = null;
                        segarkan();
                    });

                    $(document).on('click', '#filterListPA button', function() {
                        filterPA = $(this).data('v');
                        bucketPA = null;
                        segarkan();
                    });

                    $(document).on('click', '#granPA button', function() {
                        granPA = $(this).data('g');
                        bucketPA = null;
                        $('#granPA button').removeClass('active').filter(this).addClass('active');
                        segarkan();
                    });

                    $(document).on('change', '#tahunPA', function() {
                        tahunPA = parseInt(this.value, 10);
                        bucketPA = null;
                        segarkan();
                    });

                    $(document).on('click', '#grafikPA .kd-bar, #tabelPeriode tbody tr[data-key]', function() {
                        const k = String($(this).attr('data-key'));
                        bucketPA = bucketPA === k ? null : k;
                        segarkan();
                    });

                    $(document).on('click', '#resetBucket', function() {
                        bucketPA = null;
                        segarkan();
                    });

                    function initTabelPA() {
                        const s = bacaState();
                        tabelPA = $('#tabelPA').DataTable({
                            ajax: {
                                url: URL_APP.data,
                                dataSrc: function(json) {
                                    dataProspect = json.data;
                                    renderAnalitik();
                                    stateSiap = true;
                                    return json.data;
                                }
                            },
                            order: [
                                [6, 'asc']
                            ],
                            pageLength: s.len,
                            displayStart: (s.page - 1) * s.len,
                            search: {
                                search: s.q
                            },
                            language: {
                                ...BAHASA,
                                emptyTable: 'Belum ada prospect.'
                            },
                            createdRow: function(row, d) {
                                if (d.melebihi) $(row).addClass('baris-lebih');
                            },
                            columns: [{
                                    data: null,
                                    orderable: false,
                                    className: 'kontrol-detail',
                                    defaultContent: '+'
                                },
                                {
                                    data: 'perusahaan',
                                    render: (v, t) => t === 'display' ? `<span class="fw-semibold">${esc(v)}</span>` : v
                                },
                                {
                                    data: 'materi',
                                    render: esc
                                },
                                {
                                    data: 'periode',
                                    className: 'text-nowrap',
                                    render: (v, t) => t === 'display' ? fmtPeriode(v) : v
                                },
                                {
                                    data: 'sales',
                                    render: esc
                                },
                                {
                                    data: 'tahap',
                                    render: (v, t) => t === 'display' ? `<span class="kd-badge kd-badge--muted">${esc(v)}</span>` : v
                                },
                                {
                                    data: 'punya_pa',
                                    render: function(v, type, r) {
                                        if (type === 'sort') return v ? 1 : 0;
                                        if (type !== 'display') return v ? 'Sudah ada PA' : 'Belum ada PA';
                                        if (v) return '<span class="kd-badge kd-badge--ok">Sudah ada PA</span>';
                                        return r.perlu_lengkapi ?
                                            '<span class="kd-badge kd-badge--warn" title="Sudah closed win, PA harus dilengkapi">Belum ada PA</span>' :
                                            '<span class="kd-badge kd-badge--muted" title="Belum closed win, PA belum wajib">Belum ada PA</span>';
                                    }
                                },
                                {
                                    data: 'jumlah_pa',
                                    className: 'text-end text-nowrap'
                                },
                                {
                                    data: 'total_pa',
                                    className: 'text-end text-nowrap',
                                    render: (v, t, r) => t === 'display' ? (r.punya_pa ? rp(v) : '-') : v
                                },
                                {
                                    data: 'bruto',
                                    className: 'text-end text-nowrap',
                                    render: (v, t) => t === 'display' ? rp(v) : v
                                },
                                {
                                    data: null,
                                    className: 'text-end text-nowrap',
                                    render: (v, t, r) => {
                                        const x = pct(r.total_pa, r.bruto);
                                        if (t === 'sort' || t === 'type') return isFinite(x) ? x : 1e9;
                                        if (t !== 'display') return fmtPct(x);
                                        if (!r.punya_pa) return '-';
                                        return `<span class="kd-badge kd-badge--${r.melebihi ? 'warn' : 'ok'}">${fmtPct(x)}</span>`;
                                    }
                                },
                                {
                                    data: 'netsales',
                                    className: 'text-end text-nowrap',
                                    render: (v, t) => t === 'display' ? rp(v) : v
                                },
                                {
                                    data: 'id_peluang',
                                    orderable: false,
                                    render: id => `<a class="kd-detail" href="${URL_APP.detailPeluang}/${id}">Detail</a>`
                                }
                            ]
                        });

                        tabelPA.on('draw.dt', simpanState);

                        $('#tabelPA tbody').on('click', 'td.kontrol-detail', function() {
                            const row = tabelPA.row($(this).closest('tr'));
                            if (row.child.isShown()) {
                                row.child.hide();
                                $(this).text('+');
                            } else {
                                row.child(daftarPaHtml(row.data())).show();
                                const tabelAnak = row.child().find('table');
                                if (tabelAnak.length) {
                                    tabelAnak.DataTable({
                                        paging: false,
                                        searching: false,
                                        info: false,
                                        ordering: false,
                                        autoWidth: false,
                                        dom: 't',
                                        language: BAHASA
                                    });
                                }
                                $(this).text('-');
                            }
                        });
                    }

                    function daftarPaHtml(d) {
                        if (!d.daftar_pa.length) {
                            return `<div class="kd-child small kd-muted">Belum ada Payment Advance untuk prospect ini.${d.perlu_lengkapi ? ' Prospect sudah closed win, PA perlu dilengkapi.' : ''}</div>`;
                        }
                        const item = pa => {
                            const biaya = Object.entries(pa.rincian).filter(([, v]) => v > 0)
                                .map(([k, v]) => `${esc(k)}: ${rp(v)}`).join(' &middot; ');
                            const ket = [
                                pa.jenis_transportasi && `Transportasi: ${esc(pa.jenis_transportasi)}`,
                                pa.keterangan_akomodasi_tim && `Akomodasi tim: ${esc(pa.keterangan_akomodasi_tim)}`,
                                pa.keterangan_entertaint && `Entertain: ${esc(pa.keterangan_entertaint)}`,
                                pa.keterangan_biaya_lain && `Biaya lain-lain: ${esc(pa.keterangan_biaya_lain)}`,
                                pa.deskripsi_tambahan && `Catatan: ${esc(pa.deskripsi_tambahan)}`
                            ].filter(Boolean).join(' &middot; ');
                            return `<tr>
                                <td class="text-nowrap">${esc(pa.tgl_pa) || '-'}</td>
                                <td>${esc(pa.tipe_pembayaran) || '-'}</td>
                                <td class="text-end text-nowrap">${rp(pa.total)}</td>
                                <td class="small">${biaya || '-'}${ket ? `<div class="text-muted">${ket}</div>` : ''}</td>
                                <td>${pa.bukti_url ? `<a href="${esc(pa.bukti_url)}" target="_blank" rel="noopener">Bukti</a>` : '-'}</td>
                            </tr>`;
                        };
                        return `<div class="kd-child"><table class="table kd-table w-100 mb-0">
                            <thead><tr><th>Tgl PA</th><th>Tipe</th><th class="text-end">Total</th><th>Rincian</th><th></th></tr></thead>
                            <tbody>${d.daftar_pa.map(item).join('')}</tbody></table></div>`;
                    }

                    function initTabelPerlu() {
                        return $('#tabelPerluLengkapi').DataTable({
                            data: [],
                            order: [],
                            pageLength: 10,
                            autoWidth: false,
                            language: {
                                ...BAHASA,
                                emptyTable: 'Tidak ada prospect yang menunggu PA.'
                            },
                            drawCallback: function() {
                                const api = this.api();
                                const awal = api.page.info().start;
                                api.column(0, {
                                    page: 'current'
                                }).nodes().each(function(c, i) {
                                    c.textContent = awal + i + 1;
                                });
                            },
                            columns: [{
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    defaultContent: ''
                                },
                                {
                                    data: 'perusahaan',
                                    render: esc
                                },
                                {
                                    data: 'materi',
                                    render: esc
                                },
                                {
                                    data: 'periode',
                                    className: 'text-nowrap',
                                    render: (v, t) => t === 'display' ? fmtPeriode(v) : v
                                },
                                {
                                    data: 'sales',
                                    render: esc
                                },
                                {
                                    data: 'tgl_closed',
                                    className: 'text-nowrap',
                                    searchable: false,
                                    render: (v, t) => t === 'display' ? (esc(v) || '-') : (v || '')
                                },
                                {
                                    data: 'umur_hari',
                                    className: 'text-end text-nowrap',
                                    searchable: false,
                                    render: (v, t) => {
                                        if (t !== 'display') return v === null ? -1 : v;
                                        if (v === null) return '-';
                                        return `<span class="${v > UMUR_PERINGATAN_HARI ? 'text-danger fw-semibold' : ''}">${v} hari</span>`;
                                    }
                                },
                                {
                                    data: 'harga',
                                    className: 'text-end',
                                    searchable: false,
                                    render: (v, t) => t === 'display' ? rp(v) : v
                                },
                                {
                                    data: 'pax',
                                    className: 'text-end',
                                    searchable: false
                                },
                                {
                                    data: 'netsales',
                                    className: 'text-end',
                                    searchable: false,
                                    render: (v, t) => t === 'display' ? rp(v) : v
                                },
                                {
                                    data: 'id_peluang',
                                    orderable: false,
                                    searchable: false,
                                    render: id =>
                                        `<a class="kd-detail" href="${URL_APP.detailPeluang}/${id}">Detail</a>`
                                }
                            ]
                        });
                    }

                    $(document).on('click', '#btnLihatPerlu', function() {
                        const daftar = dataProspect.filter(d => d.perlu_lengkapi)
                            .sort((a, b) => (b.umur_hari ?? -1) - (a.umur_hari ?? -1));

                        $('#infoPerluLengkapi').text(`${daftar.length} prospect menunggu PA`);
                        if (!tabelPerlu) tabelPerlu = initTabelPerlu();
                        tabelPerlu.search('').clear().rows.add(daftar).draw();
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('perluLengkapiModal')).show();
                    });

                    $('#perluLengkapiModal').on('shown.bs.modal', function() {
                        if (tabelPerlu) tabelPerlu.columns.adjust();
                    });

                    $('#btnLengkapiDariDaftar').on('click', function() {
                        const el = document.getElementById('perluLengkapiModal');
                        $(el).one('hidden.bs.modal', () => $('#btnLengkapiPA').trigger('click'));
                        bootstrap.Modal.getInstance(el).hide();
                    });

                    const modalEl = document.getElementById('importPaModal');
                    const modalImport = new bootstrap.Modal(modalEl);
                    let token = null,
                        baris = [],
                        filterAktif = 'semua';

                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        if (settings.nTable.id !== 'tabelPreview' || filterAktif === 'semua') return true;
                        return settings.aoData[dataIndex]._aData.status === filterAktif;
                    });

                    $('#btnLengkapiPA').on('click', function() {
                        resetWizard();
                        modalImport.show();
                    });

                    function resetWizard() {
                        token = null;
                        baris = [];
                        filterAktif = 'semua';
                        $('#fileImport').val('');
                        if (tabelPreview) tabelPreview.search('').clear().draw();
                        $('#filterStatus').empty();
                        tampilError(null);
                        keLangkah(1);
                    }

                    function keLangkah(n) {
                        $('#step1, #step2, #step3').addClass('d-none');
                        $('#step' + n).removeClass('d-none');
                        const label = {
                            1: 'Langkah 1 dari 3: unggah file',
                            2: 'Langkah 2 dari 3: periksa hasil dan beri keputusan',
                            3: 'Langkah 3 dari 3: selesai'
                        };
                        $('#stepLabel').text(label[n]);
                        $('#btnKembali').toggleClass('d-none', n !== 2);
                        $('#btnProses').toggleClass('d-none', n !== 2);
                        $('#btnTutup').text(n === 3 ? 'Tutup' : 'Batal');
                        $('#infoFooter').text('');
                    }

                    function tampilError(pesan) {
                        const el = $('#pesanError');
                        if (!pesan) return el.addClass('d-none').empty();
                        el.removeClass('d-none').html(pesan);
                    }

                    function loading(btn, aktif) {
                        $(btn).prop('disabled', aktif).find('.spinner-border').toggleClass('d-none', !aktif);
                    }

                    function pesanDariXhr(xhr) {
                        const j = xhr.responseJSON || {};
                        if (j.errors && j.errors.file) return esc(j.errors.file[0]);
                        if (Array.isArray(j.errors)) {
                            return esc(j.message || '') + '<ul class="mb-0">' + j.errors.map(e => `<li>${esc(e)}</li>`)
                                .join('') + '</ul>';
                        }
                        return esc(j.message || 'Terjadi kesalahan. Coba lagi.');
                    }

                    $('#btnPeriksa').on('click', function() {
                        tampilError(null);
                        const file = $('#fileImport')[0].files[0];
                        if (!file) return tampilError('Pilih file Excel (.xlsx) terlebih dahulu.');

                        const fd = new FormData();
                        fd.append('file', file);
                        loading(this, true);

                        $.ajax({
                                url: URL_APP.preview,
                                method: 'POST',
                                data: fd,
                                processData: false,
                                contentType: false
                            })
                            .done(res => {
                                token = res.token;
                                baris = res.baris;
                                filterAktif = res.ringkasan.tinjau > 0 ? 'tinjau' : 'semua';
                                keLangkah(2);
                                renderPreview(res.ringkasan);
                                hitungKesiapan();
                            })
                            .fail(xhr => tampilError(pesanDariXhr(xhr)))
                            .always(() => loading('#btnPeriksa', false));
                    });

                    $('#btnKembali').on('click', () => {
                        tampilError(null);
                        keLangkah(1);
                    });

                    function alasanHtml(b) {
                        return (b.alasan || []).length ?
                            `<ul class="mb-1 ps-3 small">${b.alasan.map(a => `<li>${esc(a)}</li>`).join('')}</ul>` : '';
                    }

                    function kontrolHtml(b) {
                        if (b.status === 'siap') {
                            return `<div class="form-check">
                                <input class="form-check-input k-impor" type="checkbox" id="imp${b.no}" checked>
                                <label class="form-check-label small" for="imp${b.no}">Impor data ini</label></div>`;
                        }
                        if (b.status === 'duplikat') {
                            return `<select class="form-select form-select-sm k-dup">
                                <option value="lewati" selected>Lewati (jangan simpan lagi)</option>
                                <option value="tambah">Tetap simpan sebagai data baru</option></select>`;
                        }
                        if (b.status === 'tinjau') {
                            return `<select class="form-select form-select-sm k-tinjau mb-1">
                                    <option value="">Pilih keputusan...</option>
                                    <option value="pakai">Pakai data ini</option>
                                    <option value="lewati">Jangan pakai</option></select>
                                <div class="opsi-pakai d-none">
                                    <select class="form-select form-select-sm k-perlakuan mb-1">
                                        <option value="potong">Simpan dan kurangi net sales</option>
                                        <option value="catat">Simpan sebagai catatan saja (net sales tidak dikurangi)</option></select>
                                    <input type="text" class="form-control form-control-sm k-alasan"
                                        maxlength="200" placeholder="Alasan memakai data ini (wajib)"></div>`;
                        }
                        return '';
                    }

                    function siapkanTabelPreview() {
                        if (tabelPreview) return;
                        tabelPreview = $('#tabelPreview').DataTable({
                            data: [],
                            order: [],
                            pageLength: 25,
                            autoWidth: false,
                            language: BAHASA,
                            createdRow: function(row, b) {
                                $(row).attr({
                                    'data-no': b.no,
                                    'data-status': b.status
                                }).addClass('baris-' + b.status);
                            },
                            columns: [{
                                    data: 'no'
                                },
                                {
                                    data: null,
                                    render: (v, t, b) => t === 'display' ?
                                        `<div>${esc(b.perusahaan) || '-'}</div><div class="small text-muted">${esc(b.materi)} &middot; ${esc(b.periode)}</div>` :
                                        [b.perusahaan, b.materi, b.periode].join(' ')
                                },
                                {
                                    data: 'tgl_pa',
                                    render: (v, t) => t === 'display' ? (esc(v) || '-') : (v || '')
                                },
                                {
                                    data: 'total',
                                    className: 'text-end',
                                    searchable: false,
                                    render: (v, t) => t === 'display' ? rp(v) : v
                                },
                                {
                                    data: 'status',
                                    render: (v, t) => t === 'display' ?
                                        `<span class="badge bg-${STATUS[v].kelas}${v === 'tinjau' ? ' text-dark' : ''}">${STATUS[v].teks}</span>` :
                                        STATUS[v].teks
                                },
                                {
                                    data: null,
                                    orderable: false,
                                    render: (v, t, b) => t === 'display' ? alasanHtml(b) + kontrolHtml(b) : (b
                                        .alasan || []).join(' ')
                                }
                            ]
                        });
                    }

                    function renderPreview(ring) {
                        const hitung = Object.assign({
                            semua: baris.length
                        }, ring);
                        $('#filterStatus').html(['semua', ...Object.keys(STATUS)].map(k => `
                            <button type="button" class="btn btn-outline-secondary" data-filter="${k}">
                                ${k === 'semua' ? 'Semua' : STATUS[k].teks}<span class="badge bg-light text-dark border ms-1">${hitung[k] || 0}</span>
                            </button>`).join(''));

                        $('#btnUnduhGagal').toggleClass('d-none', !(ring.gagal > 0));

                        siapkanTabelPreview();
                        tabelPreview.search('').clear().rows.add(baris);
                        terapkanFilter();
                        tabelPreview.columns.adjust();
                    }

                    $(document).on('click', '#filterStatus button', function() {
                        filterAktif = $(this).data('filter');
                        terapkanFilter();
                    });

                    function terapkanFilter() {
                        $('#filterStatus button').each(function() {
                            $(this).toggleClass('active', $(this).data('filter') === filterAktif);
                        });
                        if (tabelPreview) tabelPreview.draw();
                    }

                    $(document).on('change', '.k-tinjau', function() {
                        $(this).closest('td').find('.opsi-pakai').toggleClass('d-none', $(this).val() !== 'pakai');
                        hitungKesiapan();
                    });
                    $(document).on('change input', '.k-impor, .k-dup, .k-perlakuan, .k-alasan', hitungKesiapan);

                    $('#btnTolakSemuaTinjau').on('click', function() {
                        if (!tabelPreview) return;
                        $(tabelPreview.rows().nodes()).find('.k-tinjau').val('lewati')
                            .closest('td').find('.opsi-pakai').addClass('d-none');
                        hitungKesiapan();
                    });

                    function kumpulkanKeputusan() {
                        const k = {};
                        if (!tabelPreview) return k;
                        $(tabelPreview.rows().nodes()).each(function() {
                            const tr = $(this),
                                no = tr.data('no'),
                                st = tr.data('status');
                            if (st === 'siap') {
                                k[no] = {
                                    aksi: tr.find('.k-impor').is(':checked') ? 'impor' : 'lewati'
                                };
                            } else if (st === 'duplikat') {
                                k[no] = {
                                    aksi: tr.find('.k-dup').val()
                                };
                            } else if (st === 'tinjau') {
                                k[no] = {
                                    aksi: tr.find('.k-tinjau').val(),
                                    perlakuan: tr.find('.k-perlakuan').val(),
                                    alasan: tr.find('.k-alasan').val()
                                };
                            }
                        });
                        return k;
                    }

                    function hitungKesiapan() {
                        const k = kumpulkanKeputusan();
                        let belum = 0,
                            simpan = 0,
                            potongan = 0;

                        baris.forEach(b => {
                            const x = k[b.no];
                            if (!x) return;
                            if (b.status === 'siap' && x.aksi === 'impor') {
                                simpan++;
                                potongan += b.total;
                            }
                            if (b.status === 'duplikat' && x.aksi === 'tambah') {
                                simpan++;
                                potongan += b.total;
                            }
                            if (b.status === 'tinjau') {
                                if (!x.aksi) belum++;
                                else if (x.aksi === 'pakai') {
                                    if ((x.alasan || '').trim().length < 5) belum++;
                                    else {
                                        simpan++;
                                        if (x.perlakuan === 'potong') potongan += b.total;
                                    }
                                }
                            }
                        });

                        const info = belum > 0 ?
                            `${belum} data perlu ditinjau belum lengkap keputusannya.` :
                            `${simpan} data akan disimpan. Net sales berkurang ${rp(potongan)}.`;
                        $('#infoFooter').text(info);
                        $('#btnProses').prop('disabled', belum > 0 || simpan === 0).data({
                            simpan,
                            potongan
                        });
                    }

                    $('#btnProses').on('click', function() {
                        const simpan = $(this).data('simpan'),
                            potongan = $(this).data('potongan');
                        if (!confirm(
                                `Simpan ${simpan} data Payment Advance?\nNet sales akan berkurang ${rp(potongan)}.`
                            )) return;

                        tampilError(null);
                        loading(this, true);

                        $.ajax({
                                url: URL_APP.commit,
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    token: token,
                                    keputusan: kumpulkanKeputusan()
                                })
                            })
                            .done(res => {
                                $('#hasilImport').html(
                                    `<strong>${res.diimpor}</strong> data tersimpan, <strong>${res.dilewati}</strong> dilewati.<br>` +
                                    `Net sales berkurang ${rp(res.total_dipotong)}.`);
                                keLangkah(3);
                                if (tabelPA) tabelPA.ajax.reload(null, false);
                            })
                            .fail(xhr => tampilError(pesanDariXhr(xhr)))
                            .always(() => {
                                loading('#btnProses', false);
                            });
                    });

                    $('#btnUnduhGagal').on('click', function() {
                        const q = s => '"' + String(s ?? '').replace(/"/g, '""') + '"';
                        const isi = baris.filter(b => b.status === 'gagal')
                            .map(b => [b.no, b.perusahaan, b.materi, b.periode, (b.alasan || []).join(' | ')].map(q)
                                .join(','));
                        const csv = '\uFEFF' + ['Baris,Perusahaan,Materi,Periode,Alasan', ...isi].join('\r\n');
                        const a = document.createElement('a');
                        a.href = URL.createObjectURL(new Blob([csv], {
                            type: 'text/csv;charset=utf-8'
                        }));
                        a.download = 'daftar-import-gagal.csv';
                        a.click();
                        URL.revokeObjectURL(a.href);
                    });

                    modalEl.addEventListener('shown.bs.modal', function() {
                        if (tabelPreview) tabelPreview.columns.adjust();
                    });

                    (function pulihkanState() {
                        const s = bacaState();
                        filterPA = s.filter;
                        if (s.tab === 'pa') {
                            if (s.sub === 'daftar') {
                                bootstrap.Tab.getOrCreateInstance(document.getElementById('sub-daftar-btn')).show();
                            }
                            bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-pa-btn')).show();
                        }
                    })();
                });
            </script>
        @endif

        @if ($isAdmSales || $isFinance)
            <script>
                $(function() {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const URL_SINK = {
                        data: "{{ route('kelengkapan-data.sinkron.data') }}",
                        konfirmasi: "{{ route('kelengkapan-data.sinkron.konfirmasi') }}",
                        batal: "{{ route('kelengkapan-data.sinkron.batal') }}",
                        simpan: "{{ route('kelengkapan-data.sinkron.simpan') }}",
                        catatan: "{{ route('kelengkapan-data.sinkron.catatan') }}"
                    };
                    const PERAN = @json($isAdmSales ? 'sales' : 'finance');
                    const URL_APPROVAL = @json($isAdmSales ? $urlApprovalSales : $urlApprovalFinance);
                    const NAMA_PERAN = {
                        sales: 'Adm Sales',
                        finance: 'Finance'
                    };
                    const STATUS_SINK = {
                        sama: {
                            teks: 'Sama',
                            kelas: 'ok'
                        },
                        selisih: {
                            teks: 'Ada selisih',
                            kelas: 'warn'
                        },
                        hanya_finance: {
                            teks: 'Belum ada di Sales',
                            kelas: 'info'
                        },
                        hanya_sales: {
                            teks: 'Belum ada di Finance',
                            kelas: 'info'
                        },
                        belum_keduanya: {
                            teks: 'Belum divalidasi',
                            kelas: 'muted'
                        }
                    };

                    const BAHASA = {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Belum ada data',
                        infoFiltered: '(disaring dari _MAX_ data)',
                        zeroRecords: 'Data tidak ditemukan',
                        emptyTable: 'Tidak ada data.',
                        paginate: {
                            previous: 'Sebelumnya',
                            next: 'Berikutnya'
                        }
                    };

                    const rp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
                    const esc = s => $('<div>').text(s ?? '').html();
                    const tgl = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d
                        .getDate()).padStart(2, '0');

                    const DAFTAR = {
                        materi: @json($daftarMateri ?? []),
                        perusahaan: @json($daftarPerusahaan ?? [])
                    };
                    const PENGURANG = ['diskon', 'total_diskon', 'total_pa', 'total_cashback', 'total_uang_saku',
                        'total_akomodasi', 'biaya_transport', 'oleh_oleh', 'biaya_lain_lain', 'entertainment'
                    ];
                    const LAWAN = PERAN === 'sales' ? 'finance' : 'sales';

                    const KEY_SINK = 'kd_state_utama';
                    const FILTER_SINK_VALID = ['semua', 'perlu', 'sama'];
                    const PANJANG_SINK = [10, 25, 50, 100];

                    function bacaStateSink() {
                        const p = new URLSearchParams(location.search || sessionStorage.getItem(KEY_SINK) || '');
                        const tglOk = v => /^\d{4}-\d{2}-\d{2}$/.test(v || '');
                        const hal = parseInt(p.get('hal'), 10),
                            baris = parseInt(p.get('baris'), 10);
                        return {
                            mulai: tglOk(p.get('mulai')) ? p.get('mulai') : null,
                            selesai: tglOk(p.get('selesai')) ? p.get('selesai') : null,
                            status: FILTER_SINK_VALID.includes(p.get('status')) ? p.get('status') : null,
                            cari: p.get('cari') || '',
                            hal: hal > 0 ? hal : 1,
                            baris: PANJANG_SINK.includes(baris) ? baris : 25
                        };
                    }

                    const S = bacaStateSink();
                    let stateSinkSiap = false;

                    function simpanStateSink() {
                        if (!stateSinkSiap || !dataSink) return;
                        if ($('#tab-pa-btn').hasClass('active')) return;
                        const p = new URLSearchParams();
                        p.set('mulai', dataSink.periode.mulai);
                        p.set('selesai', dataSink.periode.selesai);
                        p.set('status', filterSink);
                        if (tabelSink) {
                            const info = tabelSink.page.info();
                            if (info.page > 0) p.set('hal', info.page + 1);
                            if (info.length !== 25) p.set('baris', info.length);
                        }
                        if (cariSink) p.set('cari', cariSink);
                        const qs = '?' + p.toString();
                        history.replaceState(history.state, '', location.pathname + qs + location.hash);
                        try {
                            sessionStorage.setItem(KEY_SINK, qs);
                        } catch (e) {}
                    }

                    let dataSink = null,
                        tabelSink = null,
                        filterSink = S.status || 'semua',
                        cariSink = S.cari,
                        filterAwalDiatur = S.status !== null,
                        barisAktif = null,
                        manualKotor = false,
                        manualBersih = false;

                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        if (settings.nTable.id !== 'tabelSinkron' || filterSink === 'semua') return true;
                        const status = settings.aoData[dataIndex]._aData.status;
                        return filterSink === 'sama' ? status === 'sama' : status !== 'sama';
                    });

                    const tabelRingan = el => $(el).DataTable({
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: false,
                        autoWidth: false,
                        dom: 't',
                        language: BAHASA
                    });

                    function fmtNilai(v, tipe) {
                        if (v === null || v === undefined) return '<span class="kd-muted">belum ada</span>';
                        if (tipe === 'uang') return rp(v);
                        if (tipe === 'angka') return new Intl.NumberFormat('id-ID').format(v);
                        return esc(v);
                    }

                    function pesan(tipe, teks) {
                        const el = $('#sinkPesan');
                        if (!tipe) return el.addClass('d-none').empty();
                        el.removeClass('d-none alert-danger alert-warning alert-success').addClass('alert-' + tipe).html(
                            teks);
                    }

                    function pesanXhr(xhr) {
                        const j = xhr.responseJSON || {};
                        if (j.errors) {
                            const p = Object.values(j.errors)[0];
                            return esc(Array.isArray(p) ? p[0] : p);
                        }
                        return esc(j.message || 'Terjadi kesalahan. Coba lagi.');
                    }

                    function setPeriode(offset) {
                        const n = new Date();
                        $('#sinkMulai').val(tgl(new Date(n.getFullYear(), n.getMonth() + offset, 1)));
                        $('#sinkSelesai').val(tgl(new Date(n.getFullYear(), n.getMonth() + offset + 1, 0)));
                    }

                    function muatSinkron(selesaiMuat) {
                        const mulai = $('#sinkMulai').val(),
                            selesai = $('#sinkSelesai').val();
                        if (!mulai || !selesai) return pesan('warning', 'Isi tanggal mulai dan selesai periode.');

                        pesan(null);
                        $('#btnSinkTampil').prop('disabled', true).text('Memuat...');

                        $.get(URL_SINK.data, {
                                mulai,
                                selesai
                            })
                            .done(res => {
                                dataSink = res;
                                if (!filterAwalDiatur) {
                                    filterSink = res.ringkasan.belum_sinkron > 0 ? 'perlu' : 'semua';
                                    filterAwalDiatur = true;
                                }
                                $('#sinkIsi').removeClass('d-none');
                                renderSink();
                                stateSinkSiap = true;
                                simpanStateSink();
                                if (typeof selesaiMuat === 'function') selesaiMuat();
                            })
                            .fail(xhr => {
                                $('#sinkIsi').addClass('d-none');
                                pesan('danger', pesanXhr(xhr));
                            })
                            .always(() => $('#btnSinkTampil').prop('disabled', false).text('Tampilkan'));
                    }

                    function itemStrip(angka, judul, warna, tip) {
                        return `<div class="kd-strip-item" style="--kd-accent:${warna}"${tip ? ` title="${esc(tip)}"` : ''}>
                            <span class="kd-strip-num">${angka}</span><span class="kd-strip-label">${judul}</span></div>`;
                    }

                    function renderSink() {
                        const r = dataSink.ringkasan;
                        const belumLengkap = r.hanya_sales + r.hanya_finance + r.belum_keduanya;

                        $('#sinkRingkasan').html(
                            itemStrip(r.total, 'RKM dibandingkan', '#2563eb', 'Training pada periode terpilih') +
                            itemStrip(r.sama, 'Sudah sama', '#16a34a', 'Sales dan Finance cocok') +
                            itemStrip(r.belum_sinkron, 'Perlu perbaikan', r.belum_sinkron > 0 ? '#dc2626' : '#16a34a',
                                `${r.selisih} selisih, ${belumLengkap} belum lengkap`) +
                            itemStrip(r.crm_beda, 'Total PA beda dengan CRM', '#d97706', 'Hanya informasi, tidak menahan sinkron') +
                            `<div class="kd-strip-end"><span id="sinkBadgeStrip"></span>
                                <button type="button" class="btn btn-link btn-sm p-0 kd-detail" id="btnKeKonfirmasi">Ke konfirmasi</button></div>`
                        );

                        renderStatusSesi();

                        const f = [
                            ['semua', 'Semua', r.total],
                            ['perlu', 'Perlu perbaikan', r.belum_sinkron],
                            ['sama', 'Sudah sama', r.sama]
                        ];
                        $('#sinkFilter').html(f.map(([k, t, n]) =>
                            `<button type="button" class="${k === filterSink ? 'active' : ''}" data-f="${k}">${t}<span class="kd-count">${n}</span></button>`
                        ).join(''));

                        isiTabelSink();
                    }

                    function renderStatusSesi() {
                        const s = dataSink.sesi,
                            r = dataSink.ringkasan;

                        const kartuPeran = p => {
                            const x = s[p];
                            let teks = '<span class="kd-muted">Belum dikonfirmasi</span>';
                            if (x.sudah) teks =
                                `Sudah dikonfirmasi oleh <strong>${esc(x.oleh)}</strong> pada ${esc(x.pada)}`;
                            else if (x.usang) teks =
                                `<span class="text-danger">Konfirmasi oleh ${esc(x.oleh)} pada ${esc(x.pada)} gugur karena data berubah. Perlu konfirmasi ulang.</span>`;

                            let aksi = '';
                            if (p === PERAN) {
                                const tahan = r.total === 0 || r.belum_sinkron > 0;
                                if (x.sudah) {
                                    aksi =
                                        '<button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnSinkBatal">Batalkan konfirmasi saya</button>';
                                } else {
                                    aksi =
                                        `<button type="button" class="btn btn-sm btn-success mt-2" id="btnSinkKonfirmasi" ${tahan ? 'disabled' : ''}>${x.usang ? 'Konfirmasi ulang' : 'Saya sudah memeriksa, konfirmasi'}</button>`;
                                    if (tahan && r.total > 0) aksi +=
                                        `<div class="small kd-muted mt-1">Selesaikan ${r.belum_sinkron} data yang belum sama lebih dulu.</div>`;
                                }
                            }

                            return `<div class="col-md-6"><div class="kd-peran">
                                <div class="fw-semibold mb-1">${NAMA_PERAN[p]}${p === PERAN ? ' (Anda)' : ''}</div>
                                <div class="small">${teks}</div>${aksi}</div></div>`;
                        };

                        const badge = s.selesai ?
                            '<span class="kd-badge kd-badge--ok">Sinkron selesai</span>' :
                            '<span class="kd-badge kd-badge--muted">Sinkron belum selesai</span>';

                        $('#sinkStatus').html(kartuPeran('sales') + kartuPeran('finance'));
                        $('#sinkBadge, #sinkBadgeStrip').html(badge);
                    }

                    function initTabelSink() {
                        tabelSink = $('#tabelSinkron').DataTable({
                            data: dataSink.baris,
                            dom: 'lrtip',
                            order: [],
                            pageLength: S.baris,
                            displayStart: (S.hal - 1) * S.baris,
                            search: {
                                search: cariSink
                            },
                            autoWidth: false,
                            language: BAHASA,
                            createdRow: function(row, b) {
                                $(row).addClass('kd-sink-row').attr('data-id', b.id_rkm);
                            },
                            columns: [{
                                    data: 'perusahaan',
                                    render: (v, t) => t === 'display' ?
                                        `<span class="fw-semibold">${esc(v)}</span>` : v
                                },
                                {
                                    data: 'materi',
                                    render: esc
                                },
                                {
                                    data: 'tanggal',
                                    className: 'text-nowrap',
                                    searchable: false,
                                    render: esc
                                },
                                {
                                    data: 'sales',
                                    render: esc
                                },
                                {
                                    data: 'status',
                                    searchable: false,
                                    render: (v, t, b) => {
                                        const st = STATUS_SINK[v];
                                        if (t !== 'display') return st.teks;
                                        const crm = b.crm_beda.length ?
                                            '<div class="small text-warning mt-1">PA beda dengan CRM</div>' : '';
                                        return `<span class="kd-badge kd-badge--${st.kelas}">${st.teks}</span>${crm}`;
                                    }
                                },
                                {
                                    data: 'jumlah_beda',
                                    className: 'text-end',
                                    searchable: false,
                                    render: (v, t) => t === 'display' ? (v || '-') : v
                                },
                                {
                                    data: 'bersih_sales',
                                    className: 'text-end text-nowrap',
                                    searchable: false,
                                    render: (v, t) => t === 'display' ? (v === null ? '-' : rp(v)) : (v === null ?
                                        -1 : v)
                                },
                                {
                                    data: 'bersih_finance',
                                    className: 'text-end text-nowrap',
                                    searchable: false,
                                    render: (v, t) => t === 'display' ? (v === null ? '-' : rp(v)) : (v === null ?
                                        -1 : v)
                                },
                                {
                                    data: null,
                                    orderable: false,
                                    searchable: false,
                                    className: 'text-nowrap',
                                    render: (v, t, b) =>
                                        `<button type="button" class="btn btn-sm btn-outline-primary me-1">Periksa</button>${b.jumlah_catatan ? `<span class="kd-badge kd-badge--info me-2" title="Jumlah catatan">${b.jumlah_catatan}</span>` : '<span class="me-2"></span>'}<a class="kd-detail" href="${esc(URL_APPROVAL)}">Buka approval</a>`
                                }
                            ]
                        });
                        tabelSink.on('draw.dt', simpanStateSink);
                    }

                    function isiTabelSink() {
                        if (!tabelSink) {
                            initTabelSink();
                        } else {
                            tabelSink.clear().rows.add(dataSink.baris).draw(false);
                        }
                        tabelSink.columns.adjust();
                    }

                    const angka = v => {
                        const n = parseInt(String(v ?? '').replace(/[^0-9]/g, ''), 10);
                        return isNaN(n) ? 0 : n;
                    };
                    const fmtAngka = n => new Intl.NumberFormat('id-ID').format(n || 0);
                    const escA = s => esc(s).replace(/"/g, '&quot;');

                    function normFe(v, jenis) {
                        if (jenis === 'uang' || jenis === 'angka') return typeof v === 'number' ? Math.round(v) : angka(v);
                        if (jenis === 'materi' || jenis === 'perusahaan') return parseInt(v, 10) || 0;
                        if (jenis === 'tanggal') return String(v ?? '').slice(0, 10);
                        const t = String(v ?? '').trim().replace(/\s+/g, ' ').toLowerCase();
                        return t === '-' ? '' : t;
                    }

                    function kotorSekarang() {
                        return angka($('#f_total_kotor').val());
                    }

                    function nilaiForm(k, jenis) {
                        return normFe($('#f_' + k).val(), jenis);
                    }

                    function pesanModal(tipe, teks) {
                        const el = $('#detailSinkronPesan');
                        if (!tipe) return el.addClass('d-none').empty();
                        el.removeClass('d-none alert-danger alert-warning alert-success').addClass('alert-' + tipe).html(esc(teks));
                    }

                    function inputBidang(x, nilai) {
                        const id = 'f_' + x.kunci;
                        const attr = `id="${id}" data-k="${x.kunci}" data-j="${x.jenis}"`;

                        if (x.jenis === 'uang') {
                            const tombol = (x.kunci === 'total_kotor' || x.kunci === 'bersih') ?
                                `<button type="button" class="btn btn-outline-secondary kd-hitung" data-t="${x.kunci}">Hitung</button>` : '';
                            return `<div class="input-group input-group-sm"><span class="input-group-text">Rp</span><input type="text" inputmode="numeric" class="form-control text-end kd-f" ${attr} value="${fmtAngka(nilai)}">${tombol}</div>`;
                        }
                        if (x.jenis === 'angka') {
                            return `<input type="number" min="0" class="form-control form-control-sm text-end kd-f" ${attr} value="${nilai ?? 0}">`;
                        }
                        if (x.jenis === 'tanggal') {
                            return `<input type="date" class="form-control form-control-sm kd-f" ${attr} value="${escA(nilai)}">`;
                        }
                        if (x.jenis === 'materi' || x.jenis === 'perusahaan') {
                            const daftar = DAFTAR[x.jenis];
                            const ada = daftar.some(d => d[0] == nilai);
                            const cadangan = (!ada && nilai) ? `<option value="${nilai}" selected>#${nilai}</option>` : '';
                            const opsi = daftar.map(d => `<option value="${d[0]}"${d[0] == nilai ? ' selected' : ''}>${esc(d[1])}</option>`).join('');
                            return `<select class="form-select form-select-sm kd-f" ${attr}><option value="">-</option>${cadangan}${opsi}</select>`;
                        }
                        return `<input type="text" maxlength="255" class="form-control form-control-sm kd-f" ${attr} value="${escA(nilai)}">`;
                    }

                    function htmlDaftarCatatan(b) {
                        const d = b.catatan || [];
                        if (!d.length) return '<div class="small kd-muted mb-2">Belum ada catatan.</div>';
                        return d.map(c => `<div class="kd-note">
                            <div><strong>${esc(c.nama)}</strong> : <span class="kd-note-teks">${esc(c.teks)}</span></div>
                            <div class="small kd-muted">${esc(NAMA_PERAN[c.peran] || c.peran)} &middot; ${esc(c.waktu)}</div></div>`).join('');
                    }

                    function htmlCatatan(b) {
                        const form = b.catatan_bisa ?
                            `<textarea class="form-control form-control-sm" id="inputCatatan" rows="2" maxlength="500" placeholder="Tulis catatan, misalnya: untuk kelas ..."></textarea>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnCatatan">Tambah catatan</button>` :
                            '<div class="small kd-muted">Catatan dapat ditambahkan setelah data Adm Sales untuk RKM ini dibuat.</div>';
                        return `<div class="kd-catatan mt-3">
                            <div class="small fw-semibold mb-2">Catatan</div>
                            <div id="listCatatan">${htmlDaftarCatatan(b)}</div>${form}</div>`;
                    }

                    function detailSink(b) {
                        const nilai = b.edit.nilai;
                        const st = STATUS_SINK[b.status];

                        const baris = b.rincian.map(x => {
                            const sendiri = `<td class="c-nilai text-end">${inputBidang(x, nilai[x.kunci])}</td>`;
                            const lawan = `<td class="c-nilai text-end">${fmtNilai(x[LAWAN], x.tipe)}</td>`;
                            return `<tr data-k="${x.kunci}"><td>${esc(x.label)}</td>${PERAN === 'sales' ? sendiri + lawan : lawan + sendiri}</tr>`;
                        }).join('');

                        const judulSendiri = `${NAMA_PERAN[PERAN]} (Anda, dapat diubah)`;
                        const kepala = PERAN === 'sales' ?
                            `<th class="text-end">${judulSendiri}</th><th class="text-end">${NAMA_PERAN.finance}</th>` :
                            `<th class="text-end">${NAMA_PERAN.sales}</th><th class="text-end">${judulSendiri}</th>`;

                        let banner = '';
                        if (!b.edit.ada) {
                            const asal = b.edit.sumber === 'lawan' ? 'data ' + NAMA_PERAN[LAWAN] : 'data RKM';
                            banner = `<div class="alert alert-info small py-2">Data ${NAMA_PERAN[PERAN]} untuk RKM ini belum ada. Isian awal diambil dari ${asal}. Periksa lalu simpan untuk membuatnya.</div>`;
                        }

                        const crmInfo = `<div class="small mb-2">Total PA di CRM (${b.jumlah_pa_crm} baris PA): <strong>${rp(b.total_pa_crm)}</strong>${
                            b.crm_beda.length ? ' <span class="text-warning">- berbeda dengan Total PA di ' + b.crm_beda.map(p => NAMA_PERAN[p]).join(' dan ') + '</span>' : ''}</div>`;

                        const khusus = (PERAN === 'finance' && (b.khusus_finance || []).length) ?
                            `<div class="small fw-semibold mt-3 mb-1">Khusus Finance</div><table class="table kd-table w-100 mb-0"><thead><tr><th>Bidang</th><th class="text-end">Nilai</th></tr></thead><tbody>${
                                b.khusus_finance.map(x => `<tr><td>${esc(x.label)}</td><td class="text-end">${fmtNilai(x.nilai, x.tipe)}</td></tr>`).join('')}</tbody></table>` : '';

                        return `${banner}
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="kd-badge kd-badge--${st.kelas}">${st.teks}</span>
                                <span class="small" id="detailSinkronBeda"></span>
                            </div>
                            ${crmInfo}
                            <table class="table kd-table w-100 mb-0">
                                <thead><tr><th>Bidang</th>${kepala}</tr></thead>
                                <tbody>${baris}</tbody>
                            </table>
                            <div class="small kd-muted mt-2">Total kotor dan total bersih dihitung otomatis dari isian lain. Tekan Hitung untuk menghitung ulang, atau ketik langsung untuk menimpa. Setelah menyimpan, konfirmasi sinkron perlu dilakukan ulang oleh kedua pihak.</div>
                            ${khusus}
                            ${htmlCatatan(b)}`;
                    }

                    function perbaruiBeda() {
                        const b = barisAktif;
                        if (!b) return;

                        const area = $('#detailSinkronIsi');
                        if (!b.lawan) {
                            area.find('td.c-nilai').removeClass('kd-beda');
                            $('#detailSinkronBeda').text('Data ' + NAMA_PERAN[LAWAN] + ' belum ada, tidak ada pembanding.');
                            return;
                        }

                        let jumlah = 0;
                        b.rincian.forEach(x => {
                            const beda = nilaiForm(x.kunci, x.jenis) !== normFe(b.lawan[x.kunci], x.jenis);
                            if (beda) jumlah++;
                            area.find(`tr[data-k="${x.kunci}"] td.c-nilai`).toggleClass('kd-beda', beda);
                        });
                        $('#detailSinkronBeda').text(jumlah ? `${jumlah} bidang masih berbeda` : 'Semua bidang sudah sama');
                    }

                    function hitungOtomatis() {
                        if (!manualKotor) {
                            $('#f_total_kotor').val(fmtAngka(angka($('#f_harga_net').val()) * angka($('#f_pax').val())));
                        }
                        if (!manualBersih) {
                            const pengurang = PENGURANG.reduce((a, k) => a + angka($('#f_' + k).val()), 0);
                            $('#f_bersih').val(fmtAngka(Math.max(0, kotorSekarang() - pengurang)));
                        }
                    }

                    function bukaDetail(id, tipe, teks) {
                        const el = document.getElementById('detailSinkronModal');
                        const b = dataSink ? dataSink.baris.find(x => x.id_rkm == id) : null;
                        if (!b) {
                            bootstrap.Modal.getInstance(el)?.hide();
                            return;
                        }

                        barisAktif = b;
                        const n = b.edit.nilai;
                        const pengurangAwal = PENGURANG.reduce((a, k) => a + (n[k] || 0), 0);
                        manualKotor = n.total_kotor !== n.harga_net * n.pax;
                        manualBersih = n.bersih !== Math.max(0, n.total_kotor - pengurangAwal);

                        $('#detailSinkronJudul').text(b.perusahaan);
                        $('#detailSinkronSub').text([b.materi, b.tanggal, b.sales].filter(Boolean).join(' \u00B7 '));
                        $('#detailSinkronIsi').html(detailSink(b));
                        $('#detailSinkronIsi table').each(function() {
                            tabelRingan(this);
                        });
                        $('#detailSinkronLink').attr('href', URL_APPROVAL);
                        $('#detailSinkronInfo').text('Hanya data ' + NAMA_PERAN[PERAN] + ' yang dapat diubah dari sini.');
                        $('#btnSinkSimpan').prop('disabled', false).text(b.edit.ada ? 'Simpan perubahan' : 'Buat data ' + NAMA_PERAN[PERAN]);
                        pesanModal(tipe, teks);
                        perbaruiBeda();

                        bootstrap.Modal.getOrCreateInstance(el).show();
                    }

                    $('#tabelSinkron').on('click', 'tbody tr.kd-sink-row', function(e) {
                        if ($(e.target).closest('a').length) return;
                        bukaDetail($(this).data('id'));
                    });

                    $('#detailSinkronModal').on('hidden.bs.modal', function() {
                        barisAktif = null;
                        $('#detailSinkronIsi').empty();
                        pesanModal(null);
                    });

                    $(document).on('input change', '#detailSinkronIsi .kd-f', function() {
                        const k = $(this).data('k');
                        if (k === 'total_kotor') {
                            manualKotor = true;
                            hitungOtomatis();
                        } else if (k === 'bersih') {
                            manualBersih = true;
                        } else if (k === 'harga_net' || k === 'pax' || PENGURANG.includes(k)) {
                            hitungOtomatis();
                        }
                        perbaruiBeda();
                    });

                    $(document).on('blur', '#detailSinkronIsi .kd-f[data-j="uang"]', function() {
                        this.value = fmtAngka(angka(this.value));
                    });

                    $(document).on('click', '#detailSinkronIsi .kd-hitung', function() {
                        if ($(this).data('t') === 'total_kotor') manualKotor = false;
                        else manualBersih = false;
                        hitungOtomatis();
                        perbaruiBeda();
                    });

                    $(document).on('click', '#btnSinkSimpan', function() {
                        const b = barisAktif;
                        if (!b) return;

                        const berubah = {};
                        b.rincian.forEach(x => {
                            const baru = nilaiForm(x.kunci, x.jenis);
                            if (baru === normFe(b.edit.nilai[x.kunci], x.jenis)) return;
                            berubah[x.kunci] = x.jenis === 'teks' ? String($('#f_' + x.kunci).val() ?? '').trim() : baru;
                        });

                        if (b.edit.ada && !Object.keys(berubah).length) {
                            return pesanModal('warning', 'Belum ada perubahan untuk disimpan.');
                        }
                        if (!confirm(`Simpan perubahan data ${NAMA_PERAN[PERAN]} untuk ${b.perusahaan}?`)) return;

                        pesanModal(null);
                        const tombol = $(this).prop('disabled', true);

                        $.post(URL_SINK.simpan, {
                                id_rkm: b.id_rkm,
                                versi: b.versi,
                                data: berubah
                            })
                            .done(res => {
                                muatSinkron(() => bukaDetail(b.id_rkm, 'success', res.pesan));
                            })
                            .fail(xhr => {
                                const teks = pesanXhr(xhr);
                                if (xhr.status === 409) {
                                    muatSinkron(() => bukaDetail(b.id_rkm, 'warning', $('<div>').html(teks).text()));
                                } else {
                                    pesanModal('danger', $('<div>').html(teks).text());
                                    tombol.prop('disabled', false);
                                }
                            });
                    });

                    $(document).on('click', '#btnCatatan', function() {
                        const b = barisAktif;
                        if (!b) return;

                        const teks = String($('#inputCatatan').val() || '').trim();
                        if (teks.length < 2) {
                            return pesanModal('warning', 'Catatan minimal 2 karakter.');
                        }

                        pesanModal(null);
                        const tombol = $(this).prop('disabled', true);

                        $.post(URL_SINK.catatan, {
                                id_rkm: b.id_rkm,
                                teks: teks
                            })
                            .done(res => {
                                b.catatan = res.catatan;
                                b.jumlah_catatan = res.catatan.length;
                                $('#listCatatan').html(htmlDaftarCatatan(b));
                                $('#inputCatatan').val('');
                                pesanModal('success', res.pesan);
                                isiTabelSink();
                            })
                            .fail(xhr => pesanModal('danger', $('<div>').html(pesanXhr(xhr)).text()))
                            .always(() => tombol.prop('disabled', false));
                    });

                    $('#btnSinkTampil').on('click', muatSinkron);
                    $('#sinkPintasan').on('click', 'button', function() {
                        setPeriode(parseInt($(this).data('offset'), 10));
                        muatSinkron();
                    });
                    $('#sinkCari').on('input', function() {
                        cariSink = $(this).val();
                        if (tabelSink) tabelSink.search(cariSink).draw();
                    });
                    $(document).on('click', '#sinkFilter button', function() {
                        filterSink = $(this).data('f');
                        $('#sinkFilter button').removeClass('active').filter(this).addClass('active');
                        if (tabelSink) tabelSink.draw();
                    });

                    $(document).on('click', '#btnSinkKonfirmasi', function() {
                        if (!confirm(
                                'Konfirmasi bahwa Anda sudah memeriksa data periode ini dan semuanya sudah sama?'))
                            return;
                        pesan(null);
                        $(this).prop('disabled', true);
                        $.post(URL_SINK.konfirmasi, {
                                mulai: dataSink.periode.mulai,
                                selesai: dataSink.periode.selesai,
                                sidik: dataSink.sidik
                            })
                            .done(() => {
                                pesan('success', 'Konfirmasi tersimpan.');
                                muatSinkron();
                            })
                            .fail(xhr => {
                                pesan('danger', pesanXhr(xhr));
                                if (xhr.status === 409) muatSinkron();
                                else renderStatusSesi();
                            });
                    });

                    $(document).on('click', '#btnSinkBatal', function() {
                        if (!confirm('Batalkan konfirmasi Anda untuk periode ini?')) return;
                        pesan(null);
                        $.post(URL_SINK.batal, {
                                mulai: dataSink.periode.mulai,
                                selesai: dataSink.periode.selesai
                            })
                            .done(() => muatSinkron())
                            .fail(xhr => pesan('danger', pesanXhr(xhr)));
                    });

                    $(document).on('click', '#btnKeKonfirmasi', function() {
                        const el = document.getElementById('sinkKonfirmasi');
                        if (el) el.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    });

                    $('#tab-utama-btn').on('shown.bs.tab', simpanStateSink);

                    if (S.mulai && S.selesai && S.mulai <= S.selesai) {
                        $('#sinkMulai').val(S.mulai);
                        $('#sinkSelesai').val(S.selesai);
                    } else {
                        setPeriode(0);
                    }
                    if (S.cari) $('#sinkCari').val(S.cari);
                    muatSinkron();
                });
            </script>
        @endif
    @endpush
@endsection