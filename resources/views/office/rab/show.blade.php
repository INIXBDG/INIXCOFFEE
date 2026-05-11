@extends('layouts_office.app')

@section('office_contents')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="container-fluid py-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">{{ $kegiatan->nama_kegiatan }}</h5>
                <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                    {{ ucfirst($kegiatan->status) }} | {{ ucfirst($kegiatan->tipe) }}
                </span>
            </div>

            <div class="card-body py-3">
                <div class="row g-3">

                    @if ($kegiatan->tipe != 'pembelian')
                        <div class="col-6 col-md">
                            <small class="text-muted d-block mb-1">Waktu</small>
                            <div class="fw-medium">
                                {{ \Carbon\Carbon::parse($kegiatan->waktu_kegiatan)->translatedFormat('d F Y') }}
                                <br>
                                <small
                                    class="text-muted">{{ \Carbon\Carbon::parse($kegiatan->waktu_kegiatan)->format('H:i') }}
                                    WIB</small>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <small class="text-muted d-block mb-1">Durasi</small>
                            <div class="fw-medium">{{ $kegiatan->lama_kegiatan }}</div>
                        </div>
                    @endif

                    <div class="col-6 col-md">
                        <small class="text-muted d-block mb-1">PIC</small>
                        <div class="fw-medium">{{ $kegiatan->pic }}</div>
                    </div>

                    @if ($kegiatan->tipe != 'pembelian')
                        <div class="col-6 col-md">
                            <small class="text-muted d-block mb-1">Peserta</small>
                            <div class="fw-medium" data-bs-toggle="modal" data-bs-target="#pesertaModal"
                                style="cursor:pointer">
                                @php $pakaiPeserta = isset($peserta) && $peserta->count(); @endphp
                                {{ $pakaiPeserta ? $peserta->count() : $absensi->count() }}
                            </div>
                        </div>
                    @endif

                    <div class="col-6 col-md">
                        <small class="text-muted d-block mb-1">Realisasi</small>
                        <div class="fw-medium" data-bs-toggle="modal" data-bs-target="#RealisasiKegiatan"
                            style="cursor:pointer">
                            Rp {{ number_format($kegiatan->realisasi ?? 0, 0, ',', '.') }}
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-body py-3 border-top">
                <small class="text-muted d-block mb-3">Tracking Status</small>
                <div class="row g-2 text-center">
                    <div class="col">
                        <small class="d-block text-muted">Diajukan</small>
                        <small>{{ $kegiatan->created_at ? \Carbon\Carbon::parse($kegiatan->created_at)->translatedFormat('d M H:i') : '-' }}</small>
                    </div>
                    <div class="col">
                        <small class="d-block text-muted">Menunggu</small>
                        <small
                            class="{{ $kegiatan->menunggu ? 'text-warning' : '' }}">{{ $kegiatan->menunggu ? \Carbon\Carbon::parse($kegiatan->menunggu)->translatedFormat('d M H:i') : '-' }}</small>
                    </div>
                    <div class="col">
                        <small class="d-block text-muted">Approved</small>
                        <small
                            class="{{ $kegiatan->approved ? 'text-success' : '' }}">{{ $kegiatan->approved ? \Carbon\Carbon::parse($kegiatan->approved)->translatedFormat('d M H:i') : '-' }}</small>
                    </div>
                    <div class="col">
                        <small class="d-block text-muted">Pencairan</small>
                        <small
                            class="{{ $kegiatan->pencairan ? 'text-info' : '' }}">{{ $kegiatan->pencairan ? \Carbon\Carbon::parse($kegiatan->pencairan)->translatedFormat('d M H:i') : '-' }}</small>
                    </div>
                    <div class="col">
                        <small class="d-block text-muted">Selesai</small>
                        <small
                            class="{{ $kegiatan->selesai ? 'text-primary' : '' }}">{{ $kegiatan->selesai ? \Carbon\Carbon::parse($kegiatan->selesai)->translatedFormat('d M H:i') : '-' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h5 class="mb-0">Detail Kebutuhan</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#linkPengajuanModal">
                    Link Pengajuan
                </button>
                @if (!$kegiatan->selesai)
                    @if (Auth::user()->jabatan === 'HRD')
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createModal">Tambah</button>
                        @if ($kegiatan->status === 'Pencairan')
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#hrdUpdateModal">Selesai</button>
                        @endif
                    @endif
                    @if (Auth::user()->jabatan === 'GM' && $kegiatan->status !== 'Approved')
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#gmUpdateModal">Update</button>
                    @endif
                    @if (Auth::user()->jabatan === 'Finance & Accounting' && $kegiatan->status === 'Approved')
                        <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal"
                            data-bs-target="#financeUpdateModal">Cairkan</button>
                    @endif
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
                <span class="fw-medium">Data Pengajuan Barang</span>
                <button onclick="loadPengajuanTable()" class="btn btn-sm btn-outline-secondary">Refresh</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="pengajuanTable" class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th></th>
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Divisi</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <h5 class="mb-3">Realisasi Budget</h5>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                @php
                    $linkedPengajuan = \App\Models\PengajuanBarang::with('detail')
                        ->where('id_kegiatan', $kegiatan->id)
                        ->get();
                    $totalBudget = $linkedPengajuan->sum(fn($pb) => $pb->detail->sum(fn($d) => $d->harga * $d->qty));
                    $totalRealisasi = $kegiatan->realisasi ?? 0;
                    $percentage = $totalBudget > 0 ? min(($totalRealisasi / $totalBudget) * 100, 100) : 0;
                    $isOverload = $totalRealisasi > $totalBudget;
                @endphp

                <div class="row g-4 align-items-center">
                    <div class="col-md-4 text-center">
                        <small class="text-muted d-block mb-1">Budget</small>
                        <div class="fw-bold">Rp {{ number_format($totalBudget, 0, ',', '.') }}</div>
                        <small class="text-muted">{{ $linkedPengajuan->count() }} pengajuan</small>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted d-block mb-1">Realisasi</small>
                        <div class="fw-bold {{ $isOverload ? 'text-danger' : 'text-success' }}">Rp
                            {{ number_format($totalRealisasi, 0, ',', '.') }}</div>
                        <small
                            class="{{ $isOverload ? 'text-danger' : 'text-success' }}">{{ $isOverload ? 'Over' : 'On Track' }}</small>
                    </div>
                    <div class="col-md-4">
                        <canvas id="realisasiChart" height="100"></canvas>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">Progress</small>
                        <small class="fw-medium">{{ number_format($percentage, 1) }}%</small>
                    </div>
                    <div class="progress" style="height:8px">
                        <div class="progress-bar {{ $isOverload ? 'bg-danger' : 'bg-success' }}"
                            style="width:{{ $percentage }}%"></div>
                    </div>
                    @if ($isOverload)
                        <small class="text-danger d-block mt-2">Melebihi budget Rp
                            {{ number_format($totalRealisasi - $totalBudget, 0, ',', '.') }}</small>
                    @else
                        <small class="text-success d-block mt-2">Sisa Rp
                            {{ number_format($totalBudget - $totalRealisasi, 0, ',', '.') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <form action="{{ route('pengajuanbarang.store') }}" method="POST" class="modal-content rounded-3">
                @csrf
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-medium">Tambah Kebutuhan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <input type="hidden" name="id_karyawan" value="{{ Auth::user()->id }}">
                <input type="hidden" name="id_kegiatan" value="{{ $kegiatan->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <select name="tipe" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="ATK">ATK</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Souvenir">Souvenir</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Reimbursement">Reimbursement</option>
                            <option value="Training & Sertifikasi">Training & Sertifikasi</option>
                        </select>
                    </div>
                    <div id="items-container">
                        <div class="row-item card border p-3 mb-3 rounded-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark item-number">Item #1</span>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove"
                                    style="display:none">Hapus</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <input type="text" name="barang[nama_barang][]"
                                        class="form-control form-control-sm" placeholder="Nama Barang" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="barang[keterangan][]"
                                        class="form-control form-control-sm" placeholder="Keterangan" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="barang[qty][]" class="form-control form-control-sm"
                                        min="1" value="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="barang[harga_barang][]"
                                        class="form-control form-control-sm" min="0" placeholder="Harga" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-row-btn" class="btn btn-outline-secondary btn-sm w-100">+ Tambah
                        Item</button>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="linkPengajuanModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-3">
                <form action="" method="POST" id="linkPengajuanForm">
                    @csrf
                    <div class="modal-header border-0">
                        <h6 class="modal-title fw-medium">Link Pengajuan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <small class="text-muted d-block mb-2">Periode: <span id="rangeStart"></span> - <span
                                id="rangeEnd"></span></small>
                        <div id="pengajuanList" class="list-group list-group-flush"></div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnLinkSubmit">Hubungkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" id="editForm" class="modal-content rounded-3">
                @csrf @method('PUT')
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-medium">Edit Kebutuhan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <input type="text" name="hal" id="edit_hal" class="form-control"
                            placeholder="Nama Kebutuhan" required>
                    </div>
                    <div class="mb-2">
                        <textarea name="rincian" id="edit_rincian" class="form-control" rows="2" placeholder="Rincian"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" name="qty" id="edit_qty" class="form-control" min="1"
                                placeholder="Qty" required>
                        </div>
                        <div class="col-6">
                            <input type="number" name="harga_satuan" id="edit_harga" class="form-control"
                                min="0" placeholder="Harga" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="hrdUpdateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('office.UpdateStatusSelesai', $kegiatan->id) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="Selesai">
                <div class="modal-content rounded-3">
                    <div class="modal-header border-0">
                        <h6 class="modal-title">Selesaikan Kegiatan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Tandai kegiatan ini sebagai <strong>Selesai</strong>?</p>
                        <small class="text-muted d-block">{{ $kegiatan->nama_kegiatan ?? '-' }}</small>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm">Ya, Selesai</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="gmUpdateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('office.UpdateStatusGM', $kegiatan->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-content rounded-3">
                    <div class="modal-header border-0">
                        <h6 class="modal-title">Update Status</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex gap-3 mb-3">
                            <label class="form-check">
                                <input class="form-check-input" type="radio" name="status" value="Menunggu"
                                    {{ $kegiatan->status === 'Menunggu' ? 'checked' : '' }} required>
                                <span class="form-check-label">Menunggu</span>
                            </label>
                            <label class="form-check">
                                <input class="form-check-input" type="radio" name="status" value="Approved"
                                    {{ $kegiatan->status === 'Approved' ? 'checked' : '' }}>
                                <span class="form-check-label">Approved</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="financeUpdateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('office.UpdateStatusFinance', $kegiatan->id) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="Pencairan">
                <div class="modal-content rounded-3">
                    <div class="modal-header border-0">
                        <h6 class="modal-title">Konfirmasi Pencairan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Dana telah dicairkan untuk kegiatan ini?</p>
                        <small class="text-muted d-block">{{ $kegiatan->nama_kegiatan ?? '-' }}</small>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-sm text-white">Ya, Cairkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="pesertaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-3">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-medium">Peserta Kegiatan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php $pakaiPeserta = isset($peserta) && $peserta->count(); @endphp
                    @if ($pakaiPeserta)
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($peserta as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $p->nama_lengkap }}</td>
                                        <td>{{ $p->jabatan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif ($absensi->count())
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Masuk</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($absensi as $index => $absen)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $absen->karyawan->nama_lengkap ?? '-' }}</td>
                                        <td>{{ $absen->karyawan->jabatan ?? '-' }}</td>
                                        <td>{{ $absen->jam_masuk }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <small class="text-muted d-block text-center py-3">Tidak ada peserta</small>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary btn-sm" id="openTambahPesertaBtn">Tambah
                        Peserta</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahPeserta" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-3">
                <form action="{{ route('office.StorePesertaKegiatan', $kegiatan->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header border-0">
                        <h6 class="modal-title fw-medium">Tambah Peserta</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <select name="peserta[]" class="form-select" multiple style="height:200px" required>
                            @foreach ($karyawan as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_lengkap }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Tahan Ctrl/Cmd untuk pilih banyak</small>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="RealisasiKegiatan" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-3">
                <form action="{{ route('office.kegiatan.updateRealisasi') }}" method="POST">
                    @csrf @method('post')
                    <div class="modal-header border-0">
                        <h6 class="modal-title">Realisasi</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($kegiatan)
                            <input type="hidden" name="id" value="{{ $kegiatan->id }}">
                            <input type="text" class="form-control" value="{{ $kegiatan->realisasi ?? '0' }}"
                                id="realisasi_display">
                            <input type="hidden" name="realisasi" value="{{ $kegiatan->realisasi ?? '0' }}"
                                id="realisasi">
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>
    <script>
        $(document).ready(function() {
            loadPengajuanTable();
            const display = document.getElementById('realisasi_display');
            const real = document.getElementById('realisasi');
            if (display && real) {
                display.addEventListener('input', function() {
                    let value = this.value.replace(/[^0-9]/g, '');
                    real.value = value;
                    this.value = value ? 'Rp ' + new Intl.NumberFormat('id-ID').format(value) : '';
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('realisasiChart');
            if (ctx) {
                const budget = {{ $totalBudget }};
                const realisasi = {{ $totalRealisasi }};
                const isOverload = {{ $isOverload ? 'true' : 'false' }};
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Terealisasi', 'Sisa'],
                        datasets: [{
                            data: isOverload ? [realisasi, 0] : [realisasi, budget - realisasi],
                            backgroundColor: [isOverload ? '#dc3545' : '#198754', '#e9ecef'],
                            borderWidth: 0,
                            cutout: '75%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(c) {
                                        return new Intl.NumberFormat('id-ID', {
                                            style: 'currency',
                                            currency: 'IDR',
                                            minimumFractionDigits: 0
                                        }).format(c.parsed);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const linkModal = document.getElementById('linkPengajuanModal');
            const pengajuanList = document.getElementById('pengajuanList');
            const linkForm = document.getElementById('linkPengajuanForm');
            if (linkModal) {
                linkModal.addEventListener('show.bs.modal', async function() {
                    const kegiatanId = '{{ $kegiatan->id }}';
                    linkForm.action = `/office/kegiatan/${kegiatanId}/link-pengajuan`;
                    pengajuanList.innerHTML =
                        `<div class="text-center py-3"><small class="text-muted">Memuat...</small></div>`;
                    try {
                        const res = await fetch(`/office/kegiatan/${kegiatanId}/available-pengajuan`);
                        const result = await res.json();
                        if (result.success && result.data.length) {
                            document.getElementById('rangeStart').textContent = result.range.start;
                            document.getElementById('rangeEnd').textContent = result.range.end;
                            let html = '';
                            result.data.forEach(item => {
                                const total = item.detail?.reduce((s, d) => s + (d.harga * d
                                    .qty), 0) || 0;
                                const detail = item.detail?.map(d =>
                                        `<div class="d-flex justify-content-between small py-1"><span>${d.nama_barang || d.rincian} ${d.keterangan ? '<small class="text-muted">('+d.keterangan+')</small>' : ''}</span><span class="text-end">${d.qty}×${parseInt(d.harga).toLocaleString('id-ID')}<br><small class="text-primary">Rp ${parseInt(d.harga*d.qty).toLocaleString('id-ID')}</small></span></div>`
                                        ).join('') ||
                                    '<small class="text-muted">No items</small>';
                                html +=
                                    `<label class="list-group-item border-0 py-2"><input class="form-check-input me-2" type="checkbox" name="pengajuan_ids[]" value="${item.id}"><div class="d-flex justify-content-between w-100"><div><small class="fw-medium">${item.tipe?.toUpperCase()}</small><br><small class="text-muted">#${item.id} • ${new Date(item.created_at).toLocaleDateString('id-ID')}</small></div><div class="text-end"><small class="fw-bold text-primary">Rp ${total.toLocaleString('id-ID')}</small><br><button type="button" class="btn btn-link btn-sm p-0 toggle-detail" data-target="detail-${item.id}">Detail</button></div></div><div id="detail-${item.id}" class="detail-content mt-2 d-none bg-light rounded p-2 small">${detail}</div></label>`;
                            });
                            pengajuanList.innerHTML = html;
                            document.querySelectorAll('.toggle-detail').forEach(btn => {
                                btn.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    const el = document.getElementById(this.dataset
                                        .target);
                                    el.classList.toggle('d-none');
                                    this.textContent = el.classList.contains('d-none') ?
                                        'Detail' : 'Sembunyikan';
                                });
                            });
                        } else {
                            pengajuanList.innerHTML =
                                `<small class="text-muted d-block text-center py-3">Tidak ada data</small>`;
                            document.getElementById('btnLinkSubmit').disabled = true;
                        }
                    } catch (e) {
                        pengajuanList.innerHTML =
                            `<small class="text-danger d-block text-center py-3">Gagal memuat</small>`;
                        document.getElementById('btnLinkSubmit').disabled = true;
                    }
                });
                linkModal.addEventListener('hidden.bs.modal', function() {
                    document.getElementById('btnLinkSubmit').disabled = false;
                    pengajuanList.innerHTML = '';
                });
            }
            if (linkForm) {
                linkForm.addEventListener('submit', function(e) {
                    if (!linkForm.querySelector('input[name="pengajuan_ids[]"]:checked')) {
                        e.preventDefault();
                        alert('Pilih minimal satu pengajuan');
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('items-container');
            const addBtn = document.getElementById('add-row-btn');

            function updateRows() {
                const rows = container.querySelectorAll('.row-item');
                rows.forEach((row, i) => {
                    row.querySelector('.item-number').textContent = `Item #${i+1}`;
                    const del = row.querySelector('.btn-remove');
                    del.style.display = rows.length > 1 ? 'block' : 'none';
                });
            }
            addBtn.addEventListener('click', function() {
                const first = container.querySelector('.row-item');
                const newRow = first.cloneNode(true);
                newRow.querySelectorAll('input').forEach(inp => {
                    inp.value = '';
                    if (inp.name === 'qty[]') inp.value = 1;
                });
                container.appendChild(newRow);
                updateRows();
            });
            container.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove')) {
                    e.target.closest('.row-item').remove();
                    updateRows();
                }
            });
            const pesertaModalEl = document.getElementById('pesertaModal');
            const tambahModalEl = document.getElementById('tambahPeserta');
            const pesertaModal = bootstrap.Modal.getOrCreateInstance(pesertaModalEl);
            const tambahModal = bootstrap.Modal.getOrCreateInstance(tambahModalEl);
            document.getElementById('openTambahPesertaBtn')?.addEventListener('click', function() {
                pesertaModal.hide();
                pesertaModalEl.addEventListener('hidden.bs.modal', function openSecond(e) {
                    tambahModal.show();
                    pesertaModalEl.removeEventListener('hidden.bs.modal', openSecond);
                }, {
                    once: true
                });
            });
            tambahModalEl.addEventListener('hidden.bs.modal', function() {
                if (!pesertaModalEl.classList.contains('show')) pesertaModal.show();
            });
        });

        function loadPengajuanTable() {
            if ($.fn.DataTable.isDataTable('#pengajuanTable')) $('#pengajuanTable').DataTable().destroy();
            $('#pengajuanTable').DataTable({
                processing: false,
                serverSide: false,
                responsive: true,
                language: {
                    processing: '<small class="text-muted">Loading...</small>',
                    emptyTable: "Belum ada data"
                },
                ajax: {
                    url: "{{ route('getPengajuanBarangKegiatan', $kegiatan->id) }}",
                    type: "GET",
                    dataSrc: "data"
                },
                columns: [{
                        data: "created_at",
                        visible: false,
                        render: d => moment(d).format('YYYY-MM-DD HH:mm:ss')
                    },
                    {
                        data: "created_at",
                        render: d => moment(d).locale('id').format('DD MMM YYYY')
                    },
                    {
                        data: "karyawan.nama_lengkap",
                        defaultContent: "-"
                    },
                    {
                        data: "karyawan.divisi",
                        defaultContent: "-"
                    },
                    {
                        data: "tipe",
                        render: d => `<small class="text-muted">${d}</small>`
                    },
                    {
                        data: "tracking.tracking",
                        defaultContent: "-",
                        render: d => `<small>${d}</small>`
                    },
                    {
                        data: "detail",
                        className: "text-end fw-medium",
                        render: d => d?.reduce((s, i) => s + (i.harga * i.qty), 0) ? new Intl.NumberFormat(
                            'id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(d.reduce((s, i) => s + (i.harga * i.qty), 0)) : '-'
                    },
                    {
                        data: null,
                        orderable: false,
                        render: (d, t, row) =>
                            `<a href="{{ url('/pengajuanbarang') }}/${row.id}" class="btn btn-sm btn-outline-primary">Detail</a>`
                    }
                ],
                order: [
                    [0, 'desc']
                ]
            });
        }
        document.getElementById('editModal')?.addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            document.getElementById('edit_hal').value = btn.dataset.hal;
            document.getElementById('edit_rincian').value = btn.dataset.rincian;
            document.getElementById('edit_qty').value = btn.dataset.qty;
            document.getElementById('edit_harga').value = btn.dataset.harga;
            document.getElementById('editForm').action = `/office/kegiatan/update/rincian/${btn.dataset.id}`;
        });
    </script>
@endsection
