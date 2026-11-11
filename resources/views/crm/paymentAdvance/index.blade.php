@extends('layouts_crm.app')

@section('crm_contents')
<div class="container-fluid mt-10">

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Payment Advance</h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label">Sales</label>
                    <select id="filterSales" class="form-select">
                        <option value="">Semua Sales</option>
                        @foreach ($sales as $s)
                            <option value="{{ $s->kode_karyawan }}">{{ $s->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select id="filterTahun" class="form-select">
                        @foreach ($tahunList as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <select id="filterBulan" class="form-select">
                        <option value="">Semua Bulan</option>
                        @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $b)
                            <option value="{{ $i + 1 }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Triwulan</label>
                    <select id="filterQuartal" class="form-select">
                        <option value="">Semua Triwulan</option>
                        <option value="1">Q1 (Jan - Mar)</option>
                        <option value="2">Q2 (Apr - Jun)</option>
                        <option value="3">Q3 (Jul - Sep)</option>
                        <option value="4">Q4 (Okt - Des)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="btnReset" class="btn btn-outline-secondary w-100">Reset Filter</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Card per minggu dirender di sini --}}
    <div id="weekContainer">
        <div class="text-center text-muted py-5">Memuat data...</div>
    </div>
</div>

{{-- Modal Input PA --}}
<div class="modal fade" id="modalInputPA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="formInputPA" class="modal-content" action="{{ route('store.payment.advance') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_rkm" id="paIdRkm">
            <input type="hidden" name="id_peluang" id="paIdPeluang">

            <div class="modal-header">
                <h5 class="modal-title">Input Payment Advance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div id="paErrors" class="alert alert-danger d-none"></div>

                {{-- Info RKM (read only) --}}
                <div class="alert alert-light border small mb-3">
                    <div><strong>Perusahaan:</strong> <span id="paInfoPerusahaan">-</span></div>
                    <div><strong>Materi:</strong> <span id="paInfoMateri">-</span></div>
                    <div><strong>Sales:</strong> <span id="paInfoSales">-</span></div>
                    <div><strong>Tgl Kelas:</strong> <span id="paInfoTanggal">-</span></div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal PA</label>
                        <input type="date" name="tgl_pa" id="paTglPa" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipe Pembayaran</label>
                        <select name="tipe_pembayaran" class="form-select" required>
                            <option value="">- Pilih -</option>
                            <option value="transfer">Transfer</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    {{-- Transportasi --}}
                    <div class="col-md-6">
                        <label class="form-label">Transportasi</label>
                        <input type="number" name="transportasi" class="form-control pa-biaya" min="0" step="any" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis Transportasi</label>
                        <input type="text" name="jenis_transportasi" class="form-control" placeholder="Contoh: Kereta, Grab, Bensin">
                    </div>

                    {{-- Akomodasi --}}
                    <div class="col-md-6">
                        <label class="form-label">Akomodasi Peserta</label>
                        <input type="number" name="akomodasi_peserta" class="form-control pa-biaya" min="0" step="any" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Akomodasi Tim</label>
                        <input type="number" name="akomodasi_tim" class="form-control pa-biaya" min="0" step="any" placeholder="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Keterangan Akomodasi Tim</label>
                        <textarea name="keterangan_akomodasi_tim" class="form-control" rows="2"></textarea>
                    </div>

                    {{-- Lain-lain --}}
                    <div class="col-md-6">
                        <label class="form-label">Fresh Money</label>
                        <input type="number" name="fresh_money" class="form-control pa-biaya" min="0" step="any" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Souvenir</label>
                        <input type="number" name="souvenir" class="form-control pa-biaya" min="0" step="any" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Entertaint</label>
                        <input type="number" name="entertaint" class="form-control pa-biaya" min="0" step="any" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cashback</label>
                        <input type="number" name="cashback" class="form-control pa-biaya" min="0" step="any" placeholder="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Keterangan Entertaint</label>
                        <textarea name="keterangan_entertaint" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sewa Laptop</label>
                        <input type="number" name="sewa_laptop" class="form-control pa-biaya" min="0" step="any" placeholder="0">
                    </div>

                    {{-- Bukti & deskripsi --}}
                    <div class="col-md-6">
                        <label class="form-label">Bukti</label>
                        <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Deskripsi Tambahan</label>
                        <textarea name="deskripsi_tambahan" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top mt-4 pt-3">
                    <strong>Total PA</strong>
                    <strong class="fs-5" id="paTotal">Rp 0</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    const urlData  = "{{ route('crm.paymentAdvance.data') }}";
    const urlStore = "{{ route('store.payment.advance') }}";
    const urlForm  = "{{ route('crm.paymentAdvance.form', ['idRkm' => '__ID__']) }}";
    const bulanOrder = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const now = new Date();
    const defaultBulan = String(now.getMonth() + 1);
    const defaultTahun = String(now.getFullYear());

    const fmtTgl = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const fmtRp  = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
    const biayaFields = ['transportasi','akomodasi_peserta','akomodasi_tim','fresh_money','entertaint','souvenir','cashback','sewa_laptop'];

    const esc = (s) => $('<div>').text(s ?? '').html();

    // Menyimpan baris berdasarkan id RKM, dipakai tombol Input untuk mengisi modal
    let rowMap = {};

    // Format dari string 'YYYY-MM-DD' (tanpa konversi zona waktu)
    function fmtYmd(ymd) {
        if (!ymd) return '-';
        const [y, m, d] = ymd.split('-').map(Number);
        return fmtTgl.format(new Date(y, m - 1, d));
    }

    // week_start (Senin) dari server. Backend Senin-Minggu (1-7), tampilan Senin-Jumat (1-5).
    function weekLabel(weekStart) {
        if (!weekStart) return 'Tanpa Tanggal';
        const [y, m, d] = weekStart.split('-').map(Number);
        const start = new Date(y, m - 1, d);
        const end   = new Date(y, m - 1, d + 4); // Jumat
        return `${fmtTgl.format(start)} - ${fmtTgl.format(end)}`;
    }

    function getStatus(approvals) {
        if (!approvals || !approvals.length) {
            return '<span class="badge bg-secondary">Belum ada approval</span>';
        }
        const last = approvals.reduce((a, b) => (parseInt(b.level_status) > parseInt(a.level_status) ? b : a));
        const done = parseInt(last.level_status) >= 3;
        return `<span class="badge ${done ? 'bg-success' : 'bg-warning text-dark'}">${esc(last.keterangan)}</span>`;
    }

    // 1 RKM = 1 baris
    function mapRkm(json) {
        return json.map(function (rkm) {
            const rel  = rkm.perhitungan_net_sales;
            const list = Array.isArray(rel) ? rel : (rel ? [rel] : []);
            const hasPA = list.length > 0;

            const total = list.reduce((sum, pa) =>
                sum + biayaFields.reduce((s, f) => s + (parseFloat(pa[f]) || 0), 0), 0);

            const approvals = list.flatMap(pa => pa.approved_net_sales || []);
            const tglPa = list.map(pa => pa.tgl_pa).filter(Boolean).sort().pop() || null;
            const tipe  = [...new Set(list.map(pa => pa.tipe_pembayaran).filter(Boolean))].join(', ');

            // peluang bisa berupa objek (hasOne/belongsTo) atau array (hasMany); ambil yang pertama
            const peluang = Array.isArray(rkm.peluang) ? rkm.peluang[0] : rkm.peluang;

            return {
                id: rkm.id,
                id_peluang: peluang?.id ?? null,
                week_start: rkm.week_start,
                tgl_awal: rkm.tgl_awal_ymd,
                tgl_akhir: rkm.tgl_akhir_ymd,
                tgl_pa: tglPa ? String(tglPa).substring(0, 10) : null,
                hasPA: hasPA,
                sales_nama: rkm.sales_key ?? '-',
                perusahaan: rkm.perusahaan?.nama_perusahaan ?? '-',
                materi: rkm.materi?.nama_materi ?? '-',
                bulan: rkm.bulan_no ? bulanOrder[rkm.bulan_no - 1] : '-',
                quartal: rkm.quartal_no ? 'Q' + rkm.quartal_no : '-',
                total: total,
                tipe_pembayaran: tipe || '-',
                status_html: hasPA
                    ? getStatus(approvals)
                    : '<span class="badge bg-danger">Belum ada PA</span>'
            };
        });
    }

    function render(rows) {
        const $wrap = $('#weekContainer').empty();
        rowMap = {};

        if (!rows.length) {
            $wrap.html('<div class="card"><div class="card-body text-center text-muted py-5">Data tidak ditemukan</div></div>');
            return;
        }

        rows.forEach(r => { rowMap[r.id] = r; });

        // group per minggu (data dari server sudah urut tanggal_awal)
        const groups = {};
        rows.forEach(r => {
            const key = r.week_start || '0000-00-00';
            (groups[key] ??= { label: weekLabel(r.week_start), items: [] }).items.push(r);
        });

        // minggu paling awal di atas
        Object.keys(groups).sort().forEach(function (key) {
            const g = groups[key];
            const subtotal = g.items.reduce((s, r) => s + r.total, 0);

            const body = g.items.map((r, i) => `
                <tr class="${r.hasPA ? 'table-success' : 'table-danger'}">
                    <td>${i + 1}</td>
                    <td>${fmtYmd(r.tgl_awal)}${r.tgl_akhir ? ' - ' + fmtYmd(r.tgl_akhir) : ''}</td>
                    <td>${fmtYmd(r.tgl_pa)}</td>
                    <td>${esc(r.sales_nama)}</td>
                    <td>${esc(r.perusahaan)}</td>
                    <td>${esc(r.materi)}</td>
                    <td>${esc(r.bulan)}</td>
                    <td>${esc(r.quartal)}</td>
                    <td class="text-end">${r.hasPA ? fmtRp.format(r.total) : '-'}</td>
                    <td>${esc(r.tipe_pembayaran).toUpperCase()}</td>
                    <td>${r.status_html}</td>
                    ${
                        r.hasPA
                            ? `<td class="text-nowrap">
                                <a href="${urlForm.replace('__ID__', r.id)}"
                                    class="btn btn-sm btn-outline-secondary">
                                    Form PA
                                </a>
                            </td>`
                            : `<td class="text-nowrap">
                                <button type="button"
                                        class="btn btn-sm btn-primary btn-input-pa"
                                        data-id="${r.id}">
                                    Input
                                </button>
                            </td>`
                    }
                </tr>
            `).join('');

            $wrap.append(`
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Minggu: ${esc(g.label)}</strong>
                            <span class="badge bg-dark ms-2">${g.items.length} RKM</span>
                        </div>
                        <div class="fw-bold">Total: ${fmtRp.format(subtotal)}</div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 tablePA">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px">No</th>
                                        <th>Tgl Kelas</th>
                                        <th>Tgl PA</th>
                                        <th>Sales</th>
                                        <th>Perusahaan</th>
                                        <th>Materi</th>
                                        <th>Bulan</th>
                                        <th>Quartal</th>
                                        <th class="text-end">Total PA</th>
                                        <th>Pembayaran</th>
                                        <th>Status Approval</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>${body}</tbody>
                            </table>
                        </div>
                    </div>
                </div>`);
        });
    }

    // Ambil data dari server sesuai filter
    let xhr = null;
    function loadData() {
        if (xhr) xhr.abort(); // batalkan request sebelumnya kalau filter berubah cepat

        $('#weekContainer').html('<div class="text-center text-muted py-5">Memuat data...</div>');

        xhr = $.getJSON(urlData, {
            sales:   $('#filterSales').val(),
            tahun:   $('#filterTahun').val(),
            bulan:   $('#filterBulan').val(),
            quartal: $('#filterQuartal').val()
        })
        .done(function (json) {
            render(mapRkm(json));
        })
        .fail(function (jqXHR, status) {
            if (status === 'abort') return;
            $('#weekContainer').html('<div class="alert alert-danger">Gagal memuat data.</div>');
        });
    }

    function setDefaultFilter() {
        $('#filterSales').val('');
        $('#filterQuartal').val('');
        $('#filterTahun').val(defaultTahun);
        $('#filterBulan').val(defaultBulan);
    }

    // ===== Modal Input PA =====
    const modalEl = document.getElementById('modalInputPA');
    const modalPA = new bootstrap.Modal(modalEl);

    function hitungTotalPA() {
        let total = 0;
        $('#formInputPA .pa-biaya').each(function () {
            total += parseFloat(this.value) || 0;
        });
        $('#paTotal').text(fmtRp.format(total));
    }

    // Buka modal dari tombol Input (event delegation karena baris dirender dinamis)
    $('#weekContainer').on('click', '.btn-input-pa', function () {
        const r = rowMap[$(this).data('id')];
        if (!r) return;

        $('#formInputPA')[0].reset();
        $('#paErrors').addClass('d-none').empty();
        $('#paIdRkm').val(r.id);
        $('#paIdPeluang').val(r.id_peluang ?? '');
        $('#paInfoPerusahaan').text(r.perusahaan);
        $('#paInfoMateri').text(r.materi);
        $('#paInfoSales').text(r.sales_nama);
        $('#paInfoTanggal').text(fmtYmd(r.tgl_awal) + (r.tgl_akhir ? ' - ' + fmtYmd(r.tgl_akhir) : ''));

        // default tanggal PA = hari ini (waktu lokal)
        const t = new Date();
        $('#paTglPa').val(`${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}-${String(t.getDate()).padStart(2, '0')}`);

        hitungTotalPA();
        modalPA.show();
    });

    $('#formInputPA').on('input', '.pa-biaya', hitungTotalPA);

    // Simpan lewat AJAX ke route store.payment.advance
    $('#formInputPA').on('submit', function (e) {
        e.preventDefault();

        const $btn = $(this).find('button[type=submit]');
        const $err = $('#paErrors').addClass('d-none').empty();

        // id_peluang wajib di backend, jadi RKM tanpa peluang tidak bisa disimpan
        if (!$('#paIdPeluang').val()) {
            $err.removeClass('d-none').text('RKM ini belum terhubung ke peluang, PA tidak bisa disimpan.');
            return;
        }

        $btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: urlStore,
            method: 'POST',
            data: new FormData(this),      // sudah termasuk _token dari @csrf dan file bukti
            processData: false,
            contentType: false,
            headers: { 'Accept': 'application/json' }
        })
        .done(function () {
            modalPA.hide();
            loadData();                    // muat ulang, baris berubah jadi hijau
        })
        .fail(function (xhr) {
            let html = 'Gagal menyimpan data.';
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                html = Object.values(xhr.responseJSON.errors).flat().map(esc).join('<br>');
            }
            $err.removeClass('d-none').html(html);
        })
        .always(function () {
            $btn.prop('disabled', false).text('Simpan');
        });
    });

    // ===== Filter =====
    $('#filterSales, #filterTahun').on('change', loadData);

    // Pilih triwulan -> bulan dikosongkan
    $('#filterQuartal').on('change', function () {
        if (this.value) $('#filterBulan').val('');
        loadData();
    });

    // Pilih bulan -> triwulan dikosongkan
    $('#filterBulan').on('change', function () {
        if (this.value) $('#filterQuartal').val('');
        loadData();
    });

    $('#btnReset').on('click', function () {
        setDefaultFilter();
        loadData();
    });

    // Load awal: bulan & tahun saat ini
    setDefaultFilter();
    loadData();
});
</script>
@endsection