@extends('layouts_crm.app')

@section('crm_contents')
<style>
    .pa-paper {
        max-width: 960px;
        margin: 0 auto 30px;
        background: #fff;
        border: 1px solid #ccc;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
        padding: 28px 34px;
        font-family: Helvetica, Arial, sans-serif;
        font-size: 13px;
        color: #000;
    }
    .pa-paper table { border-collapse: collapse; }

    /* Input terlihat seperti isian di kertas */
    .pa-input {
        width: 100%;
        border: 0;
        border-bottom: 1px dashed #b5b5b5;
        background: transparent;
        padding: 3px 4px;
        font: inherit;
        color: inherit;
        border-radius: 0;
    }
    .pa-input:hover { background: #fafafa; }
    .pa-input:focus { outline: none; background: #f4f9ff; border-bottom-color: #0d6efd; }
    select.pa-input { padding-right: 0; }
    textarea.pa-input { resize: vertical; }

    /* Kop */
    .pa-kop { width: 100%; }
    .pa-kop img { height: 100px; width: auto; }
    .pa-kop .tgl { text-align: right; vertical-align: top; white-space: nowrap; }
    .pa-kop .tgl .pa-input { width: 150px; }

    .pa-judul { text-align: center; font-size: 18px; font-weight: bold; letter-spacing: .5px; margin: 6px 0 14px; }

    /* Identitas */
    .pa-ident td { padding: 2px 0; }
    .pa-ident td.lbl { width: 78px; }
    .pa-ident td.sep { width: 12px; }
    .pa-ident .pa-input { max-width: 280px; }

    /* Project + Materi / Tanggal Pelatihan / Instansi: 1 tabel, 60%, kiri */
    .pa-info { width: 60%; margin-top: 8px; table-layout: fixed; }
    .pa-info td { border: 1px solid #000; padding: 5px 6px; vertical-align: middle; text-align: center; }
    .pa-info .project-label { width: 100px; font-weight: bold; white-space: nowrap; }
    .pa-info .info-label { font-weight: bold; }

    /* Tabel Description / Amount */
    .pa-desc { width: 100%; margin-top: 14px; }
    .pa-desc th, .pa-desc td { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; }
    .pa-desc th { text-align: center; font-weight: normal; background: #fafafa; }
    .pa-desc tr.sum td { font-weight: bold; }
    .pa-desc tr.grand td { font-weight: bold; background: #eee; }

    .pa-note { font-size: 11px; margin-top: 6px; }

    /* Approval & Finance (tidak bisa diedit) */
    .pa-bawah { width: 100%; margin-top: 26px; }
    .pa-bawah > tbody > tr > td { vertical-align: top; }
    .pa-judul-kecil { margin-bottom: 3px; }
    .pa-apv, .pa-fin { width: 100%; }
    .pa-apv td, .pa-fin td { border: 1px solid #000; height: 50px; padding: 4px 8px; }
    .pa-apv td.lbl { width: 62%; }
    .pa-apv td.lbl span { margin-left: 6px; }
    .pa-apv td.ttd { width: 38%; text-align: center; }
    .ttd-img { max-height: 42px; max-width: 100%; }
    .stamp { font-weight: bold; color: #198754; font-size: 11px; }

    .pa-actions { max-width: 960px; margin: 0 auto 40px; display: flex; justify-content: space-between; }
</style>

@php
    // Level tertinggi yang sudah disetujui: 1 = SPV, 2 = GM, 3 = Selesai
    $ok    = $approved->where('status', 1);
    $level = (int) ($ok->max(fn ($a) => (int) $a->level_status) ?? 0);

    $tglApv = function ($lv) use ($ok) {
        $a = $ok->firstWhere('level_status', $lv);
        return $a && $a->tanggal ? \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') : '';
    };

    // Cari file ttd di disk public (storage/app/public). Kolom ttd bisa berisi nama file atau path.
    // Mengembalikan path relatif yang ketemu, selain itu null.
    $ttdFile = function ($f) {
        if (!$f) return null;
        foreach ([$f, 'ttd/' . $f, 'ttd/' . basename($f)] as $p) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($p)) return $p;
        }
        return null;
    };

    // Penandatangan. 'stamp' = teks cadangan kalau ttd tidak ada.
    $signers = [
        ['label' => 'Requested by',    'name' => $salesName,                'ttd' => $ttdFile($requester?->ttd), 'show' => true,        'stamp' => 'Requested'],
        ['label' => 'Manager',         'name' => $manager?->nama_lengkap,   'ttd' => $ttdFile($manager?->ttd),   'show' => $level >= 1, 'stamp' => 'Approved'],
        ['label' => 'General Manager', 'name' => $gm?->nama_lengkap,        'ttd' => $ttdFile($gm?->ttd),        'show' => $level >= 2, 'stamp' => 'Approved'],
        ['label' => 'Director',        'name' => $director?->nama_lengkap,  'ttd' => $ttdFile($director?->ttd),  'show' => $level >= 2, 'stamp' => 'Approved'],
    ];

    // Finance & Accounting: terisi setelah Selesai (level 3)
    $finTtd = $level >= 3 ? $ttdFile($finance?->ttd) : null;
@endphp

<div class="container-fluid mt-10">
    <form id="formPrint" method="POST" action="{{ route('crm.paymentAdvance.printPdf') }}" target="_blank">
        @csrf
        <input type="hidden" name="id_rkm" value="{{ $rkm->id }}">

        <div class="pa-paper">

            {{-- Kop --}}
            <table class="pa-kop">
                <tr>
                    <td><img src="{{ asset('assets/img/inix.png') }}" alt="Inixindo Bandung"></td>
                    <td class="tgl">DATE : <input type="date" name="tgl_pa" class="pa-input" value="{{ $tglPa }}"></td>
                </tr>
            </table>

            <div class="pa-judul">PAYMENT ADVANCED</div>

            {{-- Identitas --}}
            <table class="pa-ident">
                <tr>
                    <td class="lbl">NAME</td><td class="sep">:</td>
                    <td><input type="text" name="nama" class="pa-input" value="{{ $nama }}"></td>
                </tr>
                <tr>
                    <td class="lbl">DIVISION</td><td class="sep">:</td>
                    <td><input type="text" name="divisi" class="pa-input" value="{{ $divisi }}"></td>
                </tr>
            </table>

            {{-- Project + Materi / Tanggal Pelatihan / Instansi --}}
            <table class="pa-info">
                <tr>
                    <td class="project-label">Project</td>
                    <td colspan="2"><input type="text" name="project" class="pa-input" value="{{ $project }}"></td>
                </tr>
                <tr>
                    <td class="info-label">Materi</td>
                    <td class="info-label">Tanggal Pelatihan</td>
                    <td class="info-label">Instansi</td>
                </tr>
                <tr>
                    <td><input type="text" name="materi" class="pa-input" value="{{ $materi }}"></td>
                    <td><input type="text" name="tgl_pelatihan" class="pa-input" value="{{ $tglKelas }}"></td>
                    <td><input type="text" name="instansi" class="pa-input" value="{{ $instansi }}"></td>
                </tr>
            </table>

            {{-- Description / Amount --}}
            <div class="table-responsive">
                <table class="pa-desc">
                    <thead>
                        <tr>
                            <th style="width:36%">DESCRIPTION (Jenis Pengajuan/PPN/PPH)</th>
                            <th style="width:7%">QTY</th>
                            <th style="width:19%">AMOUNT</th>
                            <th style="width:14%">TYPE</th>
                            <th style="width:19%">DATE REQUIRED</th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemRows"></tbody>
                    <tfoot>
                        <tr class="sum">
                            <td>Total</td>
                            <td></td>
                            <td class="text-end" id="totalText">Rp 0,-</td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="sum">
                            <td>
                                PPN
                                <input type="number" name="ppn_persen" id="ppnPersen" class="pa-input d-inline-block text-center"
                                       style="width:60px" min="0" max="100" step="any" value="0"> %
                            </td>
                            <td></td>
                            <td class="text-end" id="ppnText">Rp 0,-</td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="sum">
                            <td>
                                PPH
                                <input type="number" name="pph_persen" id="pphPersen" class="pa-input d-inline-block text-center"
                                       style="width:60px" min="0" max="100" step="any" placeholder="0"
                                       title="Kosongkan kalau tidak ada PPH, baris ini tidak dicetak di PDF"> %
                            </td>
                            <td></td>
                            <td class="text-end" id="pphText">Rp 0,-</td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="grand">
                            <td>Total Keseluruhan</td>
                            <td></td>
                            <td class="text-end" id="grandText">Rp 0,-</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-2">
                <button type="button" id="btnAddRow" class="btn btn-sm btn-outline-primary">+ Tambah Baris</button>
            </div>

            <div class="pa-note">Note: Please cross out the payment type that is not applicable.</div>

            <div class="mt-3">
                <label class="form-label mb-1 small">Catatan (opsional)</label>
                <textarea name="catatan" class="pa-input" rows="2">{{ $catatan }}</textarea>
            </div>

            {{-- Approvals & Finance: tidak bisa diedit --}}
            <table class="pa-bawah">
                <tr>
                    <td style="width:54%">
                        <div class="pa-judul-kecil">APPROVALS</div>
                        <table class="pa-apv">
                            @foreach ($signers as $s)
                                <tr>
                                    <td class="lbl">{{ $s['label'] }} : <span>{{ $s['show'] ? mb_convert_case($s['name'] ?? '', MB_CASE_TITLE, 'UTF-8') : '' }}</span></td>
                                    <td class="ttd">
                                        @if ($s['show'])
                                            @if ($s['ttd'])
                                                <img class="ttd-img" src="{{ asset('storage/' . $s['ttd']) }}" alt="TTD">
                                            @elseif ($s['stamp'])
                                                <div class="stamp">{{ $s['stamp'] }}</div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>

                    <td style="width:6%"></td>

                    <td style="width:40%">
                        <div class="pa-judul-kecil">For Finance &amp; Administration only</div>
                        <table class="pa-fin">
                            <tr>
                                <td>Status :
                                    @if ($level >= 3)
                                        Lengkap @if ($tglApv(3)) ({{ $tglApv(3) }}) @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Verified by :
                                    @if ($finTtd)
                                        <div><img class="ttd-img" src="{{ asset('storage/' . $finTtd) }}" alt="TTD"></div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="pa-actions">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary px-5">Print</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    const fmtRp = (n) => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n) + ',-';
    const attr  = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    const initialItems = @json($items);
    let idx = 0;

    function rowHtml(i, d) {
        // Non = tidak ada coretan di PDF
        const type = ['Non', 'Transfer', 'Cash'].includes(d.type) ? d.type : 'Transfer';
        return `
            <tr>
                <td><input type="text" name="items[${i}][description]" class="pa-input" value="${attr(d.description)}"></td>
                <td><input type="number" name="items[${i}][qty]" class="pa-input text-center" min="0" step="any" value="${attr(d.qty)}"></td>
                <td><input type="number" name="items[${i}][amount]" class="pa-input text-end pa-amount" min="0" step="any" value="${attr(d.amount)}"></td>
                <td>
                    <select name="items[${i}][type]" class="pa-input">
                        <option value="Non" ${type === 'Non' ? 'selected' : ''}> - </option>
                        <option value="Transfer" ${type === 'Transfer' ? 'selected' : ''}>Transfer</option>
                        <option value="Cash" ${type === 'Cash' ? 'selected' : ''}>Cash</option>
                    </select>
                </td>
                <td><input type="date" name="items[${i}][date_required]" class="pa-input" value="${attr(d.date_required)}"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-del" title="Hapus baris">&times;</button></td>
            </tr>`;
    }

    function hitung() {
        let total = 0;
        $('.pa-amount').each(function () { total += parseFloat(this.value) || 0; });

        const ppn = total * (parseFloat($('#ppnPersen').val()) || 0) / 100;
        const pph = total * (parseFloat($('#pphPersen').val()) || 0) / 100;

        $('#totalText').text(fmtRp(total));
        $('#ppnText').text(fmtRp(ppn));
        $('#pphText').text(fmtRp(pph));
        $('#grandText').text(fmtRp(total + ppn + pph));
    }

    function addRow(d) {
        $('#itemRows').append(rowHtml(idx++, d || { description: '', qty: 1, amount: '', type: 'Transfer', date_required: '' }));
        hitung();
    }

    initialItems.forEach(addRow);

    $('#btnAddRow').on('click', function () { addRow(); });

    $('#itemRows').on('click', '.btn-del', function () {
        $(this).closest('tr').remove();
        if (!$('#itemRows tr').length) addRow();   // minimal 1 baris
        hitung();
    });

    $('#itemRows').on('input', '.pa-amount', hitung);
    $('#ppnPersen, #pphPersen').on('input', hitung);

    // Setelah PDF terbuka di tab baru, muat ulang halaman ini
    $('#formPrint').on('submit', function () {
        setTimeout(function () { window.location.reload(); }, 500);
    });

    hitung();
});
</script>
@endsection