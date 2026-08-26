@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Manajemen Gaji Karyawan</h1>

        <div class="card shadow-sm" style="margin-bottom: 10%; border-radius: 12px;">
            <div class="card-body p-3">
                <ul class="nav nav-tabs mb-3" id="divisiTab" role="tablist">
                    @foreach ($karyawan as $divisi => $users)
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link @if($loop->first) active @endif"
                                id="tab-btn-{{ $loop->index }}"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-{{ $loop->index }}"
                                type="button"
                                role="tab"
                                data-table-id="table-{{ $loop->index }}"
                            >
                                {{ $divisi }}
                                <span class="badge bg-secondary bg-opacity-50 ms-1">{{ count($users) }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="divisiTabContent">
                    @foreach ($karyawan as $divisi => $users)
                        <div
                            class="tab-pane fade @if($loop->first) show active @endif"
                            id="tab-{{ $loop->index }}"
                            role="tabpanel"
                        >
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 w-100" id="table-{{ $loop->index }}">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">No</th>
                                            <th>Nama</th>
                                            <th>Jabatan</th>
                                            <th>Gaji</th>
                                            <th>Tunjangan Jabatan</th>
                                            <th class="text-center" style="width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $item)
                                            <tr>
                                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                                <td>{{ $item->karyawan->nama_lengkap }}</td>
                                                <td class="text-muted">{{ $item->karyawan->jabatan ?? '-' }}</td>
                                                <td class="fw-semibold" data-order="{{ $item->karyawan->gaji }}">
                                                    Rp {{ number_format($item->karyawan->gaji, 0, ',', '.') }}
                                                </td>
                                                <td class="fw-semibold" data-order="{{ $item->karyawan->tunjangan_jabatan }}">
                                                    Rp {{ number_format($item->karyawan->tunjangan_jabatan, 0, ',', '.') }}
                                                </td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary btn-detail"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailGajiModal"
                                                        data-id="{{ $item->id }}"
                                                        data-karyawan-id="{{ $item->karyawan->id }}"
                                                        data-nama="{{ $item->karyawan->nama_lengkap }}"
                                                        data-jabatan="{{ $item->karyawan->jabatan ?? '-' }}"
                                                        data-divisi="{{ $item->karyawan->divisi ?? '-' }}"
                                                        data-gaji="{{ $item->karyawan->gaji }}"
                                                        data-log="{{ json_encode($item->karyawan->logGaji) }}"
                                                        data-tunjangan="{{ $item->karyawan->tunjangan_jabatan ?? 0 }}"
                                                    >
                                                        Detail
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted fst-italic py-3">Tidak ada data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Gaji --}}
    <div class="modal fade" id="detailGajiModal" tabindex="-1" aria-labelledby="detailGajiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailGajiModalLabel">Detail Gaji Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 110px;">Nama</td>
                                    <td>: <span id="detail-nama">-</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Jabatan</td>
                                    <td>: <span id="detail-jabatan">-</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Divisi</td>
                                    <td>: <span id="detail-divisi">-</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="p-3 bg-light rounded w-100 text-center">
                                <div class="text-muted small mb-1">Take Home Pay</div>
                                <div class="fs-5 fw-bold text-primary" id="detail-gaji">-</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <form id="formEditGaji" method="POST">
                            @csrf
                            @method('PUT')
                            <h6 class="fw-semibold mb-3">Ubah Data Gaji</h6>

                            <div class="row g-3">
                                {{-- Gaji Pokok --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Gaji Pokok</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input
                                            type="number"
                                            id="input-gaji-baru"
                                            name="jumlah_gaji"
                                            class="form-control"
                                            min="0"
                                            step="1"
                                            placeholder="Nominal gaji pokok"
                                            required
                                        >
                                    </div>
                                </div>

                                {{-- Tunjangan Jabatan --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Tunjangan Jabatan</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input
                                            type="number"
                                            id="input-tunjangan"
                                            name="tunjangan_jabatan"
                                            class="form-control"
                                            min="0"
                                            step="1"
                                            placeholder="Nominal tunjangan"
                                        >
                                    </div>
                                </div>

                                {{-- Bulan --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Bulan</label>
                                    <select id="input-bulan" name="bulan" class="form-select" required>
                                        <option value="" disabled selected>Pilih bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>

                                {{-- Tahun --}}
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Tahun</label>
                                    <input
                                        type="number"
                                        id="input-tahun"
                                        name="tahun"
                                        class="form-control"
                                        min="2000"
                                        max="2099"
                                        placeholder="Contoh: 2025"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-text mb-3">Nominal dalam Rupiah, tanpa titik atau koma.</div>

                            <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                        </form>
                    </div>

                    <hr>

                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <h6 class="fw-semibold mb-0">Riwayat Gaji</h6>
                        <div class="d-flex gap-2">
                            <select id="filter-log-tahun" class="form-select form-select-sm" style="width: 110px;">
                                <option value="">Semua Tahun</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Bulan</th>
                                    <th class="text-center">Tahun</th>
                                    <th>Gaji</th>
                                    <th>Tunjangan Jabatan</th>
                                    <th class="text-center">Dicatat</th>
                                </tr>
                            </thead>
                            <tbody id="log-gaji-body">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada riwayat gaji.</td>
                                </tr>
                            </tbody>
                            <tfoot id="log-gaji-foot" class="table-light" style="display: none;">
                                <tr>
                                    <th class="text-end" colspan="3">Total</th>
                                    <th id="log-total-gaji">-</th>
                                    <th id="log-total-tunjangan">-</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script>
        const bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        // Menyimpan log karyawan yang sedang dibuka di modal, dipakai ulang saat filter berubah
        let currentLog = [];

        function renderLogTable(log) {
            const tbody = document.getElementById('log-gaji-body');
            const tfoot = document.getElementById('log-gaji-foot');

            if (!log.length) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Tidak ada riwayat gaji.</td></tr>`;
                tfoot.style.display = 'none';
                return;
            }

            const sorted = [...log].sort((a, b) => {
                if (b.tahun !== a.tahun) return b.tahun - a.tahun;
                return b.bulan - a.bulan;
            });

            tbody.innerHTML = sorted.map((row, i) => {
                const gajiFormatted = 'Rp ' + parseInt(row.gaji).toLocaleString('id-ID');
                const tunjanganFormatted = 'Rp ' + parseInt(row.tunjangan_jabatan || 0).toLocaleString('id-ID');
                const tanggal = row.created_at
                    ? new Date(row.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                    : '-';

                return `
                    <tr>
                        <td class="text-center">${i + 1}</td>
                        <td class="text-center">${bulanNames[row.bulan] ?? row.bulan}</td>
                        <td class="text-center">${row.tahun}</td>
                        <td>${gajiFormatted}</td>
                        <td>${tunjanganFormatted}</td>
                        <td class="text-center text-muted small">${tanggal}</td>
                    </tr>
                `;
            }).join('');

            // Hitung total gaji & tunjangan sesuai data yang sedang ditampilkan (mengikuti filter)
            const totalGaji = sorted.reduce((sum, row) => sum + (parseInt(row.gaji) || 0), 0);
            const totalTunjangan = sorted.reduce((sum, row) => sum + (parseInt(row.tunjangan_jabatan) || 0), 0);

            document.getElementById('log-total-gaji').textContent = 'Rp ' + totalGaji.toLocaleString('id-ID');
            document.getElementById('log-total-tunjangan').textContent = 'Rp ' + totalTunjangan.toLocaleString('id-ID');
            tfoot.style.display = '';
        }

        function applyLogFilter() {
            const tahun = document.getElementById('filter-log-tahun').value;

            const filtered = currentLog.filter(row => {
                return tahun ? String(row.tahun) === String(tahun) : true;
            });

            renderLogTable(filtered);
        }

        function populateTahunFilter(log) {
            const select = document.getElementById('filter-log-tahun');
            select.innerHTML = '<option value="">Semua Tahun</option>';

            const tahunUnique = [...new Set(log.map(row => String(row.tahun)))]
                .sort((a, b) => b - a);

            tahunUnique.forEach(tahun => {
                const opt = document.createElement('option');
                opt.value = tahun;
                opt.textContent = tahun;
                select.appendChild(opt);
            });
        }

        // Kunci penyimpanan tab aktif, dipakai supaya tab tidak reset ke awal
        // setiap kali form submit menyebabkan full page reload
        const TAB_STORAGE_KEY = 'gajiActiveTabIndex';
        // Kunci penyimpanan ID karyawan yang modal-nya sedang dibuka saat form disubmit,
        // dipakai supaya modal otomatis kebuka lagi (dengan data terbaru) setelah reload
        const MODAL_STORAGE_KEY = 'gajiActiveModalId';

        document.addEventListener('DOMContentLoaded', function () {
            // Init DataTable untuk tab yang sedang aktif, dan tab lain saat pertama kali ditampilkan
            const initedTables = new Set();

            function initTable(id) {
                if (initedTables.has(id)) return;
                $('#' + id).DataTable({
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                        infoEmpty: 'Tidak ada data',
                        infoFiltered: '(disaring dari _MAX_ total data)',
                        paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                        zeroRecords: 'Data tidak ditemukan',
                    },
                    order: [[1, 'asc']],
                });
                initedTables.add(id);
            }

            // Simpan index tab tiap kali user pindah tab
            document.querySelectorAll('#divisiTab button[data-bs-toggle="tab"]').forEach((btn, idx) => {
                btn.addEventListener('shown.bs.tab', function (e) {
                    initTable(e.target.getAttribute('data-table-id'));
                    sessionStorage.setItem(TAB_STORAGE_KEY, idx);
                });
            });

            // Restore tab terakhir (kalau ada) sebelum init tabel yang sedang tampil
            const storedIndex = sessionStorage.getItem(TAB_STORAGE_KEY);
            const storedBtn = storedIndex !== null
                ? document.getElementById('tab-btn-' + storedIndex)
                : null;

            if (storedBtn && !storedBtn.classList.contains('active')) {
                bootstrap.Tab.getOrCreateInstance(storedBtn).show();
            } else {
                // Tab pertama (default) tetap perlu di-init manual karena shown.bs.tab
                // hanya terpicu saat *berpindah* tab, bukan saat load awal
                const activeTable = document.querySelector('.tab-pane.active table');
                if (activeTable) initTable(activeTable.id);
            }

            // Modal detail gaji
            const modal = document.getElementById('detailGajiModal');

            modal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;

                const id      = btn.getAttribute('data-id');
                const nama    = btn.getAttribute('data-nama');
                const jabatan = btn.getAttribute('data-jabatan');
                const divisi  = btn.getAttribute('data-divisi');
                const gaji    = parseInt(btn.getAttribute('data-gaji')) || 0;
                const tunjangan = parseInt(btn.getAttribute('data-tunjangan')) || 0;
                currentLog = JSON.parse(btn.getAttribute('data-log') || '[]');

                document.getElementById('input-tunjangan').value = btn.getAttribute('data-tunjangan') || 0;

                const now = new Date();
                document.getElementById('input-bulan').value = now.getMonth() + 1;
                document.getElementById('input-tahun').value = now.getFullYear();

                document.getElementById('detail-nama').textContent    = nama;
                document.getElementById('detail-jabatan').textContent = jabatan;
                document.getElementById('detail-divisi').textContent  = divisi;
                document.getElementById('detail-gaji').textContent    = 'Rp ' + (gaji + tunjangan).toLocaleString('id-ID');

                const form = document.getElementById('formEditGaji');
                form.action = '{{ url("gaji") }}/' + id;
                document.getElementById('input-gaji-baru').value = gaji;

                // Reset filter riwayat gaji tiap kali modal dibuka
                populateTahunFilter(currentLog);
                renderLogTable(currentLog);
            });

            document.getElementById('formEditGaji').addEventListener('submit', function () {
                const id = this.action.split('/').pop();
                sessionStorage.setItem(MODAL_STORAGE_KEY, id);

                const tabButtons = document.querySelectorAll('#divisiTab button[data-bs-toggle="tab"]');
                const activeIdx = Array.from(tabButtons).findIndex(btn => btn.classList.contains('active'));
                if (activeIdx !== -1) {
                    sessionStorage.setItem(TAB_STORAGE_KEY, activeIdx);
                }
            });

            document.getElementById('filter-log-tahun').addEventListener('change', applyLogFilter);

            const storedModalId = sessionStorage.getItem(MODAL_STORAGE_KEY);
            if (storedModalId !== null) {
                sessionStorage.removeItem(MODAL_STORAGE_KEY); // sekali pakai, biar refresh manual tidak ikut buka modal
                const detailBtn = document.querySelector('.btn-detail[data-id="' + storedModalId + '"]');
                if (detailBtn) {
                    detailBtn.click();
                }
            }
        });
    </script>
@endsection