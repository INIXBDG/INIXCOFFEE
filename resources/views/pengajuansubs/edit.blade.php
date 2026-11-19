@extends('layouts.app')

@section('content')
{{-- Load CSS Select2 & Bootstrap 5 Theme --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-body" id="card">
                    <a href="{{ route('pengajuansubs.index') }}" class="btn btn-secondary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Kembali
                    </a>
                    <h5 class="card-title text-center mb-4 fw-bold">Management Subscription - Eksekusi Permintaan (Tim ITSM)</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $reqQty = (int) ($data->subs_snapshot['requested_qty'] ?? $data->subs_snapshot['qty'] ?? 1);
                        $idleSubs = $masterSubs->where('is_idle', true);
                        $otherSubs = $masterSubs->where('is_idle', false);
                    @endphp

                    {{-- RINGKASAN PERMINTAAN DARI PENGAJU --}}
                    <div class="alert alert-info mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-info-circle"></i> Informasi Permintaan:</h6>
                        <div class="row small">
                            <div class="col-md-6">
                                <strong>Peminta:</strong> {{ $data->karyawan->nama_lengkap ?? '-' }}<br>
                                <strong>Divisi / Jabatan:</strong> {{ $data->karyawan->divisi ?? '-' }} / {{ $data->karyawan->jabatan ?? '-' }}<br>
                                <strong>Jumlah Akun Diminta:</strong> <span class="fw-bold text-dark">{{ $reqQty }} Akun / Lisensi</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Subscription Diminta:</strong> {{ $data->subs_snapshot['requested_nama_subs'] ?? $data->subs->nama_subs ?? '-' }} ({{ $data->subs_snapshot['requested_merk'] ?? $data->subs->merk ?? '-' }})<br>
                                <strong>Catatan:</strong> {{ $data->subs_snapshot['requested_catatan'] ?? $data->subs->desc ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <form id="subsForm" method="POST" action="{{ route('pengajuansubs.updatesubssubs', $data->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="initial_requested_qty" value="{{ $reqQty }}">

                        {{-- PILIH DARI MASTER SUBSCRIPTION ATAU TETAP GUNAKAN DRAFT --}}
                        <div class="row mb-3" id="row_tautkan_master">
                            <label class="col-md-4 col-form-label text-md-start fw-bold">Tautkan Master Subscription</label>
                            <div class="col-md-8">
                                <select name="id_subs_selected" id="id_subs_selected" class="form-select">
                                    @foreach($masterSubs as $mSub)
                                        @php
                                            $usageTag = $mSub->is_idle
                                                ? 'TERSEDIA / IDLE'
                                                : ($mSub->active_users_count > 0 ? "TERPAKAI: {$mSub->active_users_count} AKUN" : strtoupper($mSub->status));
                                            $identitasTag = $mSub->akun_identitas ? " - [{$mSub->akun_identitas}]" : '';
                                        @endphp
                                        <option value="{{ $mSub->id }}"
                                                data-sub='@json($mSub)'
                                                {{ $data->id_subs == $mSub->id ? 'selected' : '' }}>
                                            [{{ $usageTag }}] {{ $mSub->nama_subs }} ({{ $mSub->merk ?? '-' }}){{ $identitasTag }}
                                            @if($mSub->end_date) - Exp: {{ \Carbon\Carbon::parse($mSub->end_date)->format('d/m/Y') }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih subscription jika sudah ada di Master Subscription untuk memuat data otomatis.</small>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- KEPUTUSAN EKSEKUSI TIM ITSM --}}
                        <div class="row mb-4">
                            <label class="col-md-4 col-form-label text-md-start fw-bold text-primary">Keputusan Eksekusi ITSM <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <select name="keputusan_itsm" id="keputusan_itsm" class="form-select border-primary fw-bold" required>
                                    <option value="berikan_akses" {{ $data->jenis_transaksi == 'existing' && $data->subs?->status == 'active' && empty($data->subs_snapshot['partial_fulfillment']) ? 'selected' : '' }}>
                                        1. Semua Akun Tersedia -> Berikan Akses Sekarang (Permintaan Akses)
                                    </option>
                                    <option value="pengajuan_barang_baru" {{ $data->jenis_transaksi == 'baru' && empty($data->subs_snapshot['partial_fulfillment']) ? 'selected' : '' }}>
                                        2. Belum Ada Stok -> Beli Baru Seluruhnya atas nama {{ auth()->user()->karyawan->jabatan }}
                                    </option>
                                    <option value="split_existing_dan_baru" {{ !empty($data->subs_snapshot['partial_fulfillment']) ? 'selected' : '' }}>
                                        3. Sebagian Tersedia & Beli Sisanya ke Finance (Partial Fulfillment)
                                    </option>
                                    <option value="pengajuan_barang_perpanjangan" {{ $data->jenis_transaksi == 'pembaharuan' ? 'selected' : '' }}>
                                        4. Masa Aktif Habis / Expired -> Ajukan Perpanjangan Barang atas nama {{ auth()->user()->karyawan->jabatan }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- SECTION KHUSUS PARTIAL FULFILLMENT (SEBAGIAN DARI STOK EXISTING + SISANYA BELI BARU) --}}
                        <div id="section_partial_fulfillment" class="border border-primary rounded p-3 mb-4 bg-light d-none">
                            <h6 class="fw-bold text-primary mb-2">
                                Alokasi Pemenuhan Campuran (Partial Fulfillment)
                            </h6>
                            <p class="small text-muted mb-3">
                                Pilih akun yang <strong>sudah tersedia (Idle/Active)</strong> di Master Subscription untuk langsung diberikan aksesnya, dan sistem akan otomatis menghitung sisa kekurangan akun untuk diajukan ke Finance.
                            </p>

                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label fw-bold">1. Pilih Akun Tersedia (Stok) <span class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <select name="existing_subs_ids[]" id="existing_subs_ids" class="form-select" multiple="multiple" style="width: 100%;">
                                        <optgroup label="Stok Tersedia (Active & Idle - Siap Pakai)">
                                            @foreach($idleSubs as $iSub)
                                                <option value="{{ $iSub->id }}">
                                                    [IDLE] {{ $iSub->nama_subs }} ({{ $iSub->merk ?? '-' }}) {{ $iSub->akun_identitas ? '- ' . $iSub->akun_identitas : '' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Subscription Lainnya di Master">
                                            @foreach($otherSubs as $oSub)
                                                <option value="{{ $oSub->id }}">
                                                    [{{ strtoupper($oSub->status) }} - Terpakai: {{ $oSub->active_users_count }}] {{ $oSub->nama_subs }} ({{ $oSub->merk ?? '-' }}) {{ $oSub->akun_identitas ? '- ' . $oSub->akun_identitas : '' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <small class="text-muted">Pilih 1 atau lebih akun existing yang akan langsung diberikan aksesnya.</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label fw-bold">2. Berikan Akun Existing Kepada <span class="text-danger">*</span></label>
                                <div class="col-md-8">
                                    <select name="existing_assigned_users[]" id="existing_assigned_users" class="form-select" multiple="multiple" style="width: 100%;">
                                        <optgroup label="Peminta pada Pengajuan Ini">
                                            @foreach($karyawans->where('kode_karyawan', $data->kode_karyawan) as $tKar)
                                                <option value="{{ $tKar->kode_karyawan }}" selected>
                                                    ★ {{ $tKar->nama_lengkap }} ({{ $tKar->jabatan }} - {{ $tKar->divisi }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Karyawan Lainnya">
                                            @foreach($karyawans->where('kode_karyawan', '!=', $data->kode_karyawan) as $oKar)
                                                <option value="{{ $oKar->kode_karyawan }}">
                                                    {{ $oKar->nama_lengkap }} ({{ $oKar->jabatan }} - {{ $oKar->divisi }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <small class="text-muted">Karyawan yang dipilih di sini akan langsung menerima akses akun existing dan tercatat di Tracking Penggunaan.</small>
                                </div>
                            </div>

                            <div class="row align-items-center bg-white border rounded p-2 mx-0">
                                <div class="col-md-4 small">
                                    Total Diminta Awal: <strong class="text-dark">{{ $reqQty }} Akun</strong>
                                </div>
                                <div class="col-md-4 small">
                                    Dipenuhi dari Stok: <strong class="text-dark" id="summary_existing_count">0 Akun</strong>
                                </div>
                                <div class="col-md-4 small">
                                    Sisa Beli Baru (Finance): <strong class="text-primary" id="summary_new_count">{{ $reqQty }} Akun</strong>
                                    <input type="hidden" name="qty_pengadaan" id="qty_pengadaan" value="{{ $reqQty }}">
                                </div>
                            </div>
                        </div>

                        {{-- 1. INFORMASI UMUM SUBSCRIPTION --}}
                        <div class="row mb-3">
                            <label for="nama_subs" class="col-md-4 col-form-label text-md-start">Nama Subscription / Software <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('nama_subs') is-invalid @enderror"
                                       name="nama_subs" id="nama_subs" value="{{ old('nama_subs', $data->subs->nama_subs ?? '') }}" required>
                                @error('nama_subs')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="merk" class="col-md-4 col-form-label text-md-start">Vendor / Merk</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('merk') is-invalid @enderror"
                                       name="merk" id="merk" value="{{ old('merk', $data->subs->merk ?? '') }}" placeholder="Contoh: Anthropic, Adobe, AWS">
                                @error('merk')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="qty" class="col-md-4 col-form-label text-md-start" id="label_qty_field">Jumlah Akun / Lisensi (Qty) <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input type="number" min="1" class="form-control @error('qty') is-invalid @enderror"
                                       name="qty" id="qty" value="{{ old('qty', $reqQty) }}" required>
                                @error('qty')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror

                                {{-- CHECKBOX AUTO-SPLIT MASTER SUBSCRIPTION KHUSUS QTY > 1 --}}
                                <div class="form-check mt-2 p-2 ps-4 border rounded bg-light {{ $reqQty > 1 ? '' : 'd-none' }}" id="split_master_container">
                                    <input class="form-check-input" type="checkbox" name="split_master_subs" id="split_master_subs" value="1"
                                           {{ old('split_master_subs', $reqQty > 1 ? '1' : '') ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold text-dark" for="split_master_subs">
                                        Pecah otomatis menjadi <span id="label_split_qty">{{ $reqQty }}</span> data akun terpisah di Master Subscription (Contoh: Nama Subs #1, #2, dst.)
                                    </label>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                                        Memudahkan TS mengisi email/password berbeda dan meng-assign setiap akun ke karyawan yang berbeda di Master Subscription.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">Tipe Aset</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                    <option value="subscription" {{ ($data->subs->tipe ?? 'subscription') == 'subscription' ? 'selected' : '' }}>Subscription (Berlangganan)</option>
                                    <option value="one-time" {{ ($data->subs->tipe ?? '') == 'one-time' ? 'selected' : '' }}>One-Time (Sekali Beli)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">Status Subscription</label>
                            <div class="col-md-6">
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="pending" {{ ($data->subs->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="active" {{ ($data->subs->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ ($data->subs->status ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="desc" class="col-md-4 col-form-label text-md-start">Identitas Akun / Deskripsi</label>
                            <div class="col-md-6">
                                <textarea name="desc" id="desc" class="form-control" rows="2" placeholder="Contoh: [Akun: claude1@inixindo.id] Catatan tambahan...">{{ old('desc', $data->subs->desc ?? '') }}</textarea>
                            </div>
                        </div>

                        {{-- 2. DETAIL AKSES --}}
                        <hr class="my-3">
                        <h6 class="fw-bold mb-3">Detail Akses & Masa Aktif</h6>

                        <div class="row mb-3">
                            <label for="subs_url" class="col-md-4 col-form-label text-md-start">URL Subscription</label>
                            <div class="col-md-6">
                                <input type="url" class="form-control" name="subs_url" id="subs_url" value="{{ old('subs_url', $data->subs->subs_url ?? '') }}" placeholder="https://...">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="access_code" class="col-md-4 col-form-label text-md-start">Kode Akses / Credentials</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="access_code" id="access_code" value="{{ old('access_code', $data->subs->access_code ?? '') }}" placeholder="Email / Username / Password / Key">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="start_date" class="col-md-4 col-form-label text-md-start">Tanggal Mulai Aktif</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                       value="{{ !empty($data->subs->start_date) ? \Carbon\Carbon::parse($data->subs->start_date)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="end_date" class="col-md-4 col-form-label text-md-start">Tanggal Berakhir</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="end_date" id="end_date"
                                       value="{{ !empty($data->subs->end_date) ? \Carbon\Carbon::parse($data->subs->end_date)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        {{-- 3. KEUANGAN (Wajib jika Pengajuan Barang) --}}
                        <div id="section_keuangan">
                            <hr class="my-3">
                            <h6 class="fw-bold mb-3">Rincian Biaya Pengadaan (Untuk Pengajuan Barang ke Finance)</h6>

                            <div class="row mb-3">
                                <label for="mata_uang" class="col-md-4 col-form-label text-md-start">Mata Uang</label>
                                <div class="col-md-6">
                                    <select name="mata_uang" id="mata_uang" class="form-select">
                                        @foreach (['Rupiah', 'Dollar', 'Euro', 'Poundsterling'] as $currency)
                                            <option value="{{ $currency }}" {{ ($data->subs->mata_uang ?? 'Rupiah') == $currency ? 'selected' : '' }}>
                                                {{ $currency }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="harga" class="col-md-4 col-form-label text-md-start">Harga Satuan per Akun</label>
                                <div class="col-md-6">
                                    <input type="number" step="0.01" class="form-control" name="harga" id="harga"
                                           value="{{ old('harga', $data->subs->harga ?? 0) }}">
                                    <small class="text-muted">Harga asli untuk 1 akun/lisensi.</small>
                                </div>
                            </div>

                            <div class="row mb-3" id="row_kurs">
                                <label for="kurs" class="col-md-4 col-form-label text-md-start">Kurs (Rate)</label>
                                <div class="col-md-6">
                                    <input type="number" step="0.01" class="form-control" name="kurs" id="kurs"
                                           value="{{ old('kurs', $data->subs->kurs ?? 1) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="harga_rupiah" class="col-md-4 col-form-label text-md-start">Total Estimasi Rupiah</label>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control bg-light fw-bold" name="harga_rupiah" id="harga_rupiah"
                                               value="{{ old('harga_rupiah', !empty($data->subs->harga_rupiah) ? number_format($data->subs->harga_rupiah * $reqQty, 0, ',', '.') : '0') }}" readonly>
                                    </div>
                                    <small class="text-muted d-block mt-1" id="calc_formula_hint">
                                        Otomatis dihitung: Qty &times; Harga Satuan &times; Kurs
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0 mt-4">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    Simpan & Eksekusi Keputusan ITSM
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function formatRupiah(angka) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    $(document).ready(function () {
        const initialRequestedQty = parseInt($('#initial_requested_qty').val()) || 1;

        $('#existing_subs_ids').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih akun dari Master Subscription yang tersedia (Idle)...",
            allowClear: true,
            width: '100%'
        });

        $('#existing_assigned_users').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih karyawan penerima akun existing...",
            allowClear: true,
            width: '100%'
        });

        function updatePartialCalculation() {
            let keputusan = $('#keputusan_itsm').val();
            if (keputusan === 'split_existing_dan_baru') {
                let selectedExisting = $('#existing_subs_ids').val() || [];
                let existingCount = selectedExisting.length;
                let qtyPengadaan = Math.max(1, initialRequestedQty - existingCount);

                $('#summary_existing_count').text(existingCount + ' Akun');
                $('#summary_new_count').text(qtyPengadaan + ' Akun');
                $('#qty_pengadaan').val(qtyPengadaan);
                $('#qty').val(qtyPengadaan);
            }
            calculateTotal();
        }

        function calculateTotal() {
            var keputusan = $('#keputusan_itsm').val();
            var currency = $('#mata_uang').val();
            var qty = parseInt($('#qty').val()) || 1;

            if (keputusan === 'split_existing_dan_baru') {
                qty = parseInt($('#qty_pengadaan').val()) || qty;
            }

            var harga = parseFloat($('#harga').val()) || 0;
            var kurs = parseFloat($('#kurs').val()) || 1;

            if (currency === 'Rupiah') {
                $('#kurs').val(1).prop('readonly', true);
                kurs = 1;
            } else {
                $('#kurs').prop('readonly', false);
            }

            var satuanRupiah = Math.round(harga * kurs);
            var total = satuanRupiah * qty;

            $('#harga_rupiah').val(formatRupiah(Math.floor(total).toString()));
            $('#calc_formula_hint').text(
                `Rincian: ${qty} Akun × Rp ${formatRupiah(satuanRupiah.toString())} = Rp ${formatRupiah(total.toString())}`
            );

            // Toggle tampilan checkbox split akun jika qty > 1
            $('#label_split_qty').text(qty);
            if (qty > 1) {
                $('#split_master_container').removeClass('d-none');
            } else {
                $('#split_master_container').addClass('d-none');
                $('#split_master_subs').prop('checked', false);
            }
        }

        function handleKeputusanChange() {
            let val = $('#keputusan_itsm').val();
            if (val === 'berikan_akses') {
                $('#section_partial_fulfillment').addClass('d-none');
                $('#label_qty_field').html('Jumlah Akun / Lisensi (Qty) <span class="text-danger">*</span>');
                $('#qty').prop('readonly', false).removeClass('bg-light').val(initialRequestedQty);
                $('#status').val('active');
            } else if (val === 'split_existing_dan_baru') {
                $('#section_partial_fulfillment').removeClass('d-none');
                $('#label_qty_field').html('Qty Pengadaan Baru (Finance) <span class="text-danger">*</span>');
                $('#qty').prop('readonly', true).addClass('bg-light');
                $('#status').val('pending');
                updatePartialCalculation();
                return;
            } else {
                $('#section_partial_fulfillment').addClass('d-none');
                $('#label_qty_field').html('Jumlah Akun / Lisensi (Qty) <span class="text-danger">*</span>');
                $('#qty').prop('readonly', false).removeClass('bg-light').val(initialRequestedQty);
                $('#status').val('pending');
            }
            calculateTotal();
        }

        // Saat Master Subscription diganti dari dropdown, isi form dengan data subscription tersebut
        $('#id_subs_selected').on('change', function() {
            let sub = $(this).find(':selected').data('sub');
            if (sub) {
                $('#nama_subs').val(sub.nama_subs || '');
                $('#merk').val(sub.merk || '');
                $('#tipe').val(sub.tipe || 'subscription');
                $('#status').val(sub.status || 'pending');
                $('#desc').val(sub.desc || '');
                $('#subs_url').val(sub.subs_url || '');
                $('#access_code').val(sub.access_code || '');
                $('#start_date').val(sub.start_date ? sub.start_date.split(' ')[0] : '');
                $('#end_date').val(sub.end_date ? sub.end_date.split(' ')[0] : '');
                $('#mata_uang').val(sub.mata_uang || 'Rupiah');
                $('#harga').val(sub.harga || 0);
                $('#kurs').val(sub.kurs || 1);
                calculateTotal();
            }
        });

        $('#existing_subs_ids').on('change', updatePartialCalculation);
        $('#mata_uang').on('change', calculateTotal);
        $('#qty, #harga, #kurs').on('input', calculateTotal);
        $('#keputusan_itsm').on('change', handleKeputusanChange);

        handleKeputusanChange();

        $('#subsForm').on('submit', function () {
            $('#harga_rupiah').val($('#harga_rupiah').val().replace(/\./g, ''));
        });
    });
</script>
@endsection
