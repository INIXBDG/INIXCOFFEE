@extends('layouts_crm.app')

@section('crm_contents')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="container-fluid mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <h3 class="m-0 fw-bold">Validasi Penjualan Sales</h3>
                        <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#petunjukModal">
                            <i class="bi bi-question-circle me-1"></i>Petunjuk
                        </button>
                    </div>
                    <span class="badge bg-primary-subtle text-primary-emphasis fs-6 px-3 py-2" id="current-period"></span>
                </div>
                <div class="card shadow-sm mb-4 border-0 bg-gradient">
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
                            <div class="col-md-3 mx-1 d-flex gap-2">
                                <button class="btn btn-primary" onclick="loadTable();">Refresh</button>
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
    </div>

    <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius:16px;">

                <div class="modal-header px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="modal-icon-wrap d-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-20"
                            style="width:40px;height:40px;">
                            <i class="bi bi-pencil-square fs-5" id="modal-icon"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="modal-title-text">Update Validasi Penjualan Sales</h5>
                            <small class="text-white text-opacity-75" id="modal-subtitle">—</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="px-4 py-2 border-bottom d-flex align-items-center gap-0" style="background:#f8fafc;">
                    <div class="step-pill active" data-step="1">
                        <span class="step-num">1</span>
                        <span class="step-label">Informasi</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-pill" data-step="2">
                        <span class="step-num">2</span>
                        <span class="step-label">Perhitungan</span>
                    </div>
                </div>

                <form id="formUpdate">
                    @csrf
                    <input type="hidden" id="update_id">
                    <input type="hidden" id="is_create_mode" value="0">

                    <div class="modal-body p-0" style="background:#f8fafc;max-height:70vh;overflow-y:auto;">

                        <div class="step-content p-4" id="step-1">
                            <div class="row g-3">

                                <div class="col-12 d-none" id="rkm-picker-wrapper">
                                    <div class="section-label-bar">
                                        <i class="bi bi-list-check me-2 text-primary"></i>
                                        <span>Pilih Training (RKM)</span>
                                    </div>
                                    <select class="form-select" id="rkm_select">
                                        <option value="">-- Pilih RKM --</option>
                                    </select>
                                    <small class="text-muted">Hanya menampilkan RKM yang belum punya data penjualan di minggu ini. Begitu dipilih, data langsung tersimpan otomatis.</small>
                                    <div id="rkm_saved_info" class="alert alert-success py-2 px-3 mt-2 d-none">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <span id="rkm_saved_text"></span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="section-label-bar">
                                        <i class="bi bi-file-text me-2 text-primary"></i>
                                        <span>Informasi Training</span>
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
                                <button type="button" class="btn btn-primary px-4" id="btnGoStep2" onclick="goStep(2)">
                                    Selanjutnya <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="step-content p-4 d-none" id="step-2">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="section-label-bar">
                                        <i class="bi bi-calculator me-2 text-success"></i>
                                        <span>Perhitungan Penjualan</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Harga Net</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
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
                                    <div class="input-group">
                                        <span class="input-group-text bg-success bg-opacity-10 text-success">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control bg-success bg-opacity-10 fw-bold text-success currency-input"
                                            id="total" name="total">
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="section-label-bar">
                                        <i class="bi bi-dash-circle me-2 text-danger"></i>
                                        <span>Pengurang</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Diskon / PA</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="diskon" name="diskon">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Total Diskon</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_diskon"
                                            name="total_diskon">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Total PA</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_pa"
                                            name="total_pa">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Cashback</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_cashback"
                                            name="total_cashback">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Uang Saku</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_uang_saku"
                                            name="total_uang_saku">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Akomodasi</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="total_akomodasi"
                                            name="total_akomodasi">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Oleh-Oleh Peserta</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="oleh_oleh"
                                            name="oleh_oleh">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Biaya Lain-Lain</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="biaya_lain_lain"
                                            name="biaya_lain_lain">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Entertainment</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="entertainment"
                                            name="entertainment">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Pengurangan PHH</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="pengurangan_phh" name="pengurangan_phh">
                                    </div>
                                    <small class="text-muted">Kosongkan jika tidak ingin dikurangi</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Exam</label>
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="exam_value"
                                            name="exam">
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="section-label-bar">
                                        <i class="bi bi-truck me-2 text-warning"></i>
                                        <span>Transport</span>
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
                                    <div class="input-group"><span
                                            class="input-group-text bg-white text-muted small">Rp</span>
                                        <input type="text" inputmode="numeric"
                                            class="form-control input-calc currency-input" id="biaya_transport"
                                            name="biaya_transport">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 p-3 rounded-3 d-flex align-items-center justify-content-between"
                                style="background:#16a34a;color:#fff;border:1px solid #15803d;">
                                <div>
                                    <div class="small opacity-75 mb-1">Total Penjualan Sales (Bersih)</div>
                                    <input type="text" inputmode="numeric" class="form-control fw-bold currency-input"
                                        style="background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.35);"
                                        id="total_penjualan_sales" name="total_penjualan_sales">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-light px-4" onclick="goStep(1)">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success px-5 fw-semibold" id="btnSimpan">
                                        <i class="bi bi-save me-2"></i>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addMoreModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:2.5rem;"></i>
                    <h5 class="fw-bold mt-3 mb-1">Data Berhasil Disimpan</h5>
                    <p class="text-muted mb-4">Ingin langsung menambahkan data training lain?</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" id="btnSelesaiTambah">Selesai</button>
                        <button type="button" class="btn btn-success px-4" id="btnTambahLagi">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Lagi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="petunjukModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                <div class="modal-header bg-info bg-opacity-10">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-info-circle me-2 text-info"></i>Petunjuk Penggunaan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">

                    <h6 class="fw-bold mb-3">Apa itu halaman ini?</h6>
                    <p class="text-muted">
                        Halaman ini digunakan untuk <strong>memvalidasi / mengisi rincian penjualan sales</strong> 
                        dari setiap kelas yang dijual dalam periode tertentu. Data ditampilkan per minggu agar lebih rapi.
                    </p>

                    <hr>

                    <h6 class="fw-bold mb-3">Arti Warna Baris</h6>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded" style="width:28px;height:28px;background:#fff;border:1px solid #dee2e6;"></div>
                            <div>
                                <strong>Putih / Biasa</strong><br>
                                <small class="text-muted">Data sudah tervalidasi (status valid)</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded" style="width:28px;height:28px;background:#fff3cd;border:1px solid #ffecb5;"></div>
                            <div>
                                <strong>Kuning</strong><br>
                                <small class="text-muted">Data belum tervalidasi (masih kosong / belum diisi)</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded" style="width:28px;height:28px;background:#d1e7dd;border:1px solid #badbcc;"></div>
                            <div>
                                <strong>Hijau</strong><br>
                                <small class="text-muted">Data yang ditambahkan secara manual (bukan dari payment advance (prospect) otomatis)</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3">Cara Menambah Data Baru</h6>
                    <ol class="text-muted">
                        <li class="mb-2">Pilih <strong>Tahun</strong> dan <strong>Bulan</strong> yang diinginkan, lalu klik <strong>Refresh</strong>.</li>
                        <li class="mb-2">Pada kartu minggu yang sesuai, klik tombol hijau <strong>+ Tambah Data</strong>.</li>
                        <li class="mb-2">Pilih Training (RKM) dari daftar yang muncul. Hanya RKM yang belum punya data penjualan yang ditampilkan.</li>
                        <li class="mb-2">Setelah dipilih, data langsung tersimpan dan form terisi otomatis dari data RKM (harga, pax, dll).</li>
                        <li class="mb-2">Lanjutkan ke langkah <strong>Perhitungan</strong> untuk melengkapi diskon, biaya, transport, dll.</li>
                        <li>Klik <strong>Simpan</strong> bila sudah selesai.</li>
                    </ol>

                    <hr>

                    <h6 class="fw-bold mb-3">Cara Mengubah Data yang Sudah Ada</h6>
                    <p class="text-muted mb-2">
                        Cukup <strong>klik baris</strong> data yang ingin diubah. Modal akan terbuka dan Anda bisa mengedit semua isian, lalu klik <strong>Simpan</strong>.
                    </p>

                    <hr>

                    <h6 class="fw-bold mb-3">Dari mana data berasal?</h6>
                    <ul class="text-muted">
                        <li class="mb-2">
                            <strong>Data biasa (putih/kuning)</strong> — diambil dari payment advance (prospect) yang sudah ada, 
                            lalu bisa dilengkapi / divalidasi di sini.
                        </li>
                        <li>
                            <strong>Data hijau (manual)</strong> — data yang Anda tambahkan sendiri melalui tombol 
                            <strong>+ Tambah Data</strong>. Biasanya dipakai bila training belum punya payment advance (prospect).
                        </li>
                    </ul>

                    <div class="alert alert-info mt-4 mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Tips:</strong> Setelah menambah data, form sudah terisi otomatis dari data RKM 
                        (harga jual, jumlah peserta, dll). Anda tinggal cek dan lengkapi bagian yang masih kosong.
                    </div>

                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    #weekly-container {
        overflow-y: hidden;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .table th {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .table td {
        font-size: .8rem;
    }

    @media (max-width:1400px) {

        .table th,
        .table td {
            padding: .4rem .5rem;
        }
    }

    .table-info td {
        background-color: #cff4fc !important;
        color: #055160;
        border-top: 2px solid #9eeaf9;
    }

    .table-dark td {
        background-color: #212529 !important;
        color: #fff;
        border-top: 3px double #495057;
        font-size: .85rem;
    }

    .table-success td {
        background-color: #d1e7dd !important;
    }

    .sync-scroll-wrapper {
        overflow-x: auto;
    }

    .step-pill {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: .8rem;
        font-weight: 500;
        color: #9ca3af;
        background: transparent;
        transition: all .2s;
    }

    .step-pill.active {
        background: #e0ecff;
        color: #1e3a5f;
    }

    .step-pill.done {
        color: #16a34a;
    }

    .step-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        font-size: .75rem;
        font-weight: 700;
    }

    .step-pill.active .step-num {
        background: #1e3a5f;
        color: #fff;
    }

    .step-pill.done .step-num {
        background: #25c6607a;
        color: #fff;
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #e5e7eb;
        min-width: 24px;
        margin: 0 4px;
    }

    .section-label-bar {
        display: flex;
        align-items: center;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #374151;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 4px;
    }

    .bg-gradient {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .badge {
        font-weight: 500;
    }

    .form-label {
        margin-bottom: .35rem;
    }

    #rkm_suggestions .list-group-item {
        cursor: pointer;
        font-size: .85rem;
    }

    #rkm_suggestions .list-group-item:hover {
        background: #f1f5f9;
    }
</style>

@section('scripts')
    <script>
        let manualTotalKotor = false;
        let manualTotalBersih = false;
        let rkmSearchTimer = null;
        let filterWeekStart = null;
        let filterWeekEnd = null;
        let selectedRkmId = null;

        $(document).ready(function() {
            updateCurrentPeriod();
            loadTable();
        });

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
            return 'Rp ' + parseFloat(value).toLocaleString('id-ID');
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
                parseNumber($('#pengurangan_phh').val()) +
                parseNumber($('#exam_value').val());

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

        function resetForm() {
            $('#formUpdate')[0].reset();
            $('#update_id').val('');
            $('#materi').val('');
            $('#perusahaan').val('');
            $('#nama_sales').val('');
            $('#instruktur').val('');
            $('.currency-input').val('');
            $('#pax').val('');
            $('#transportasi_select').val('');
            $('#transportasi_manual').addClass('d-none').val('');
            manualTotalKotor = false;
            manualTotalBersih = false;
            selectedRkmId = null;
            filterWeekStart = null;
            filterWeekEnd = null;

            // Reset dropdown RKM
            $('#rkm_select').html('<option value="">-- Pilih RKM --</option>').prop('disabled', false);
            $('#rkm_saved_info').addClass('d-none');
        }

        $('#btnTambahLagi').on('click', function() {
            bootstrap.Modal.getInstance(document.getElementById('addMoreModal'))?.hide();
            openCreateModal(filterWeekStart, filterWeekEnd);
        });

        function openCreateModal(start = null, end = null) {
            resetForm();
            filterWeekStart = start;
            filterWeekEnd = end;

            $('#is_create_mode').val('1');
            $('#modal-title-text').text('Tambah Data Penjualan Sales');
            $('#modal-icon').removeClass('bi-pencil-square').addClass('bi-plus-circle');

            if (start && end) {
                $('#modal-subtitle').text('Pilih RKM untuk periode ' + moment(start).format('DD MMM') + ' - ' + moment(end).format('DD MMM YYYY'));
            } else {
                $('#modal-subtitle').text('Pilih RKM untuk memulai');
            }

            $('#rkm-picker-wrapper').removeClass('d-none');
            $('#btnGoStep2').prop('disabled', true);
            $('#materi, #perusahaan').prop('disabled', true);

            loadRkmByWeek(start, end);

            goStep(1);
            new bootstrap.Modal(document.getElementById('updateModal')).show();
        }

        function loadRkmByWeek(start, end) {
            let $select = $('#rkm_select');
            $select.html('<option value="">Memuat data RKM...</option>').prop('disabled', true);

            $.ajax({
                url: `/crm/approval-pendapatan-sales/rkm-search`,
                type: 'GET',
                data: { start, end },
                success: function(res) {
                    $select.empty().append('<option value="">-- Pilih RKM --</option>');

                    if (!res.length) {
                        $select.append('<option value="" disabled>Tidak ada RKM tersedia di minggu ini</option>');
                    } else {
                        res.forEach(r => {
                            $select.append(`<option value="${r.id}">${escapeHtml(r.label)}</option>`);
                        });
                    }
                    $select.prop('disabled', false);
                },
                error: function() {
                    $select.html('<option value="">Gagal memuat data</option>');
                    showAlert('danger', 'Gagal memuat data RKM.');
                }
            });
        }

        $('#rkm_select').on('change', function() {
            let id = $(this).val();
            if (!id) return;

            $(this).prop('disabled', true);
            createFromRkm(id);
        });

        $('#rkm_search').on('input', function() {
            let q = $(this).val();
            clearTimeout(rkmSearchTimer);
            rkmSearchTimer = setTimeout(() => searchRkm(q), 350);
        });

        function searchRkm(q) {
            let data = { q };
            if (filterWeekStart && filterWeekEnd) {
                data.start = filterWeekStart;
                data.end = filterWeekEnd;
            }

            $.ajax({
                url: `/crm/approval-pendapatan-sales/rkm-search`,
                type: 'GET',
                data: data,
                success: function(res) {
                    let $box = $('#rkm_suggestions');
                    $box.empty();

                    if (!res.length) {
                        $box.append(
                            `<div class="list-group-item text-muted small">Tidak ada RKM ditemukan / semua sudah punya data.</div>`
                        );
                    } else {
                        res.forEach(r => {
                            $box.append(
                                `<button type="button" class="list-group-item list-group-item-action" data-id="${r.id}">${escapeHtml(r.label)}</button>`
                            );
                        });
                    }
                    $box.removeClass('d-none');
                },
                error: function() {
                    showAlert('danger', 'Gagal mencari data RKM.');
                }
            });
        }

        $(document).on('click', '#rkm_suggestions button', function() {
            let id = $(this).data('id');
            let label = $(this).text();
            $('#rkm_search').val(label);
            $('#rkm_suggestions').addClass('d-none').empty();
            createFromRkm(id);
        });

        function createFromRkm(idRkm) {
            $.ajax({
                url: `/crm/approval-pendapatan-sales/store`,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id_rkm: idRkm
                },
                success: function(response) {
                    if (!response.success) {
                        showAlert('danger', response.message || 'Gagal membuat data.');
                        $('#rkm_select').prop('disabled', false);
                        return;
                    }

                    let item = response.data;
                    selectedRkmId = item.id_rkm;

                    // Set ID & info
                    $('#update_id').val(item.id_rkm);
                    $('#modal-subtitle').text(item.materi + ' — tersimpan otomatis');
                    $('#materi').val(item.materi_id ?? '');
                    $('#perusahaan').val(item.perusahaan_id ?? '');
                    $('#nama_sales').val(item.nama_sales ?? '');
                    $('#instruktur').val(item.instruktur ?? '');

                    // === PREFILL DARI RKM / NET SALES ===
                    setCurrencyValue('#harga', item.harga);
                    $('#pax').val(item.pax ?? '');
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
                    setCurrencyValue('#pengurangan_phh', item.pengurangan_phh);
                    setCurrencyValue('#exam_value', item.exam_value);
                    setCurrencyValue('#total_penjualan_sales', item.total_penjualan_sales);

                    // Hitung ulang total bersih
                    manualTotalKotor = false;
                    manualTotalBersih = false;
                    calculateTotal();
                    calculateTotalPenjualanSales();

                    // Info sukses
                    $('#rkm_saved_text').text('Data untuk "' + item.materi + '" sudah tersimpan & diisi otomatis. Silakan cek / lengkapi rincian penjualannya.');
                    $('#rkm_saved_info').removeClass('d-none');

                    $('#btnGoStep2').prop('disabled', false);
                    $('#materi, #perusahaan').prop('disabled', false);

                    loadTable();
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal membuat data baru.';
                    showAlert('danger', msg);
                    $('#rkm_select').prop('disabled', false);
                }
            });
        }

        $('#btnTambahLagi').on('click', function() {
            bootstrap.Modal.getInstance(document.getElementById('addMoreModal'))?.hide();
            openCreateModal();
        });

        $('#btnSelesaiTambah').on('click', function() {
            bootstrap.Modal.getInstance(document.getElementById('addMoreModal'))?.hide();
            loadTable();
        });

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
                url: `/crm/approval-pendapatan-sales/get/${tahun}/${bulan}`,
                type: "GET",
                success: function(response) {
                    $('#loading-spinner').addClass('d-none');

                    let container = $('#weekly-container');
                    let monthDataList = response.data || [];
                    let footerBulanan = response.footer_bulanan || {};
                    let footerTahunan = response.footer_tahunan || {};
                    let isFinancePeriod = response.is_finance_period === true;

                    if (monthDataList.length === 0) {
                        container.html(`
                            <div class="card shadow-sm border-0">
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

                            let rows = '';

                            if (weekData.data.length === 0) {
                                rows =
                                rows = `<tr><td colspan="23" class="text-center">Tidak Ada Data pada Periode Ini</td></tr>`;
                            } else {
                                weekData.data.forEach((item, i) => {
                                    let totalVal = Number(item.total_penjualan_kotor ??
                                        (item.harga * item.pax) ?? 0);
                                    let rowClass = item.valid === 'valid' ? '' :
                                        'table-warning';
                                    if (item.manual) {
                                        rowClass = 'table-success';
                                    }
                                    let encodedItem = encodeURIComponent(JSON.stringify(
                                        item));
                                    let manualBadge = '';
                                    rows += `
                                        <tr class="${rowClass} cursor-pointer btn-edit" data-item="${encodedItem}">
                                            <td class="text-center fw-bold">${i + 1}</td>
                                            <td>${escapeHtml(item.materi)}${manualBadge}</td>
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
                                            <td class="text-end">${formatRupiah(item.pengurangan_phh || 0)}</td>
                                            <td>${item.exam}</td>
                                            <td class="text-end">${formatRupiah(item.total_penjualan_sales || 0)}</td>
                                        </tr>
                                    `;
                                });
                            }

                            let footerHtml = '';
                            if (isLastWeek) {
                                footerHtml = `
                                    <tfoot>
                                        <tr class="table-info fw-bold">
                                            <td colspan="8" class="text-end">TOTAL BULANAN</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_penjualan || 0)}</td>
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
                                            <td class="text-end">${formatRupiah(footerBulanan.pengurangan_phh || 0)}</td> 
                                            <td class="text-end">${formatRupiah(footerBulanan.total_exam || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerBulanan.total_penjualan_sales || 0)}</td>
                                        </tr>
                                        <tr class="table-dark fw-bold">
                                            <td colspan="8" class="text-end">TOTAL TAHUNAN</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_penjualan || 0)}</td>
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
                                            <td class="text-end">${formatRupiah(footerTahunan.pengurangan_phh || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_exam || 0)}</td>
                                            <td class="text-end">${formatRupiah(footerTahunan.total_penjualan_sales || 0)}</td>
                                        </tr>
                                    </tfoot>
                                `;
                            }

                            let btnTambahHtml = '';
                            if (!isFinancePeriod) {
                                btnTambahHtml = `
                                    <button class="btn btn-sm btn-success" onclick="openCreateModal('${weekData.start}', '${weekData.end}')">
                                        <i class="bi bi-plus-lg me-1"></i>Tambah Data
                                    </button>
                                `;
                            } else {
                                btnTambahHtml = `
                                    <button class="btn btn-sm btn-secondary" onclick="alertFinancePeriod()">
                                        <i class="bi bi-lock me-1"></i>Tambah Data
                                    </button>
                                `;
                            }

                            container.append(`
                                <div class="card my-1">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div>
                                                <h3 class="card-title my-1 fs-6 fw-bold">Validasi Penjualan Sales</h3>
                                                <p class="card-title my-1 text-muted small mb-0">Periode : ${moment(startOfWeek).format('DD MMMM YYYY')} - ${moment(endOfWeek).format('DD MMMM YYYY')}</p>
                                            </div>
                                            ${btnTambahHtml}
                                        </div>
                                        <div class="sync-scroll-wrapper table-scroll-sync">
                                            <table class="table table-striped table-hover mb-0" style="min-width:1800px;">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
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
                                                        <th>Oleh-Oleh peserta</th>
                                                        <th>Biaya Lain-Lain</th>
                                                        <th>Entertainment</th>
                                                        <th>Jenis Transport</th>
                                                        <th>Biaya Transport</th>
                                                        <th>Pengurangan PHH</th>
                                                        <th>Exam</th>
                                                        <th>Total Penjualan Sales (Bersih)</th>
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

        function alertFinancePeriod() {
            $('#financeAlertModal').remove();

            let modalHtml = `
                <div class="modal fade" id="financeAlertModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
                            <div class="modal-body text-center p-4">
                                <i class="bi bi-lock-fill text-warning" style="font-size:2.5rem;"></i>
                                <h5 class="fw-bold mt-3 mb-2">Tidak Dapat Menambah Data</h5>
                                <p class="text-muted mb-0">
                                    Periode <strong>Januari – September 2026</strong> merupakan data approval pendapatan accounting yang digunakan untuk validasi.<br>
                                    Penambahan data manual tidak diperbolehkan pada periode ini.
                                </p>
                            </div>
                            <div class="modal-footer border-0 justify-content-center pb-4">
                                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Mengerti</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
            new bootstrap.Modal(document.getElementById('financeAlertModal')).show();

            $('#financeAlertModal').on('hidden.bs.modal', function () {
                $(this).remove();
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

            resetForm();
            $('#is_create_mode').val('0');
            $('#modal-title-text').text('Update Validasi Penjualan Sales');
            $('#modal-icon').removeClass('bi-plus-circle').addClass('bi-pencil-square');
            $('#rkm-picker-wrapper').addClass('d-none');
            $('#btnGoStep2').prop('disabled', false);
            $('#materi, #perusahaan').prop('disabled', false);

            $('#update_id').val(item.id_rkm);
            $('#modal-subtitle').text(item.materi || '—');

            $('#materi').val(item.materi_id ?? '');
            $('#perusahaan').val(item.perusahaan_id ?? '');
            $('#nama_sales').val(item.nama_sales ?? '');
            $('#instruktur').val(item.instruktur ?? '');

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
            setCurrencyValue('#exam_value', item.exam_value);
            setCurrencyValue('#total_penjualan_sales', item.total_penjualan_sales);

            $('#pax').val(item.pax ?? '');

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

            if (!id) {
                showAlert('danger', 'Silakan pilih RKM terlebih dahulu.');
                return;
            }

            let isCreateMode = $('#is_create_mode').val() === '1';
            let jenisTransport = $('#transportasi_select').val() === 'Lainnya' ?
                $('#transportasi_manual').val() :
                $('#transportasi_select').val();
            let $btn = $('#btnSimpan');

            $.ajax({
                url: `/crm/approval-pendapatan-sales/update/${id}`,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
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
                    pengurangan_phh: getCurrencyValue('#pengurangan_phh'),
                    exam: getCurrencyValue('#exam_value'),
                    total_penjualan_sales: getCurrencyValue('#total_penjualan_sales'),
                    materi: $('#materi').val(),
                    perusahaan: $('#perusahaan').val(),
                },
                beforeSend: function() {
                    $btn.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
                },
                success: function(response) {
                    if (response.success) {
                        $('#updateModal').modal('hide');
                        setTimeout(function() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('body').css('overflow', '');
                            $('body').css('padding-right', '');

                            if (isCreateMode) {
                                new bootstrap.Modal(document.getElementById('addMoreModal')).show();
                            } else {
                                loadTable();
                            }
                        }, 300);

                        if (!isCreateMode) {
                            showAlert('success', 'Data berhasil diupdate!');
                        }
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