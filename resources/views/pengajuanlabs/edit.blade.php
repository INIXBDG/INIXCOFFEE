@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-body" id="card">
                    <a href="{{ route('pengajuanlabsdansubs.index') }}" class="btn btn-secondary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Kembali
                    </a>
                    <h5 class="card-title text-center mb-4 fw-bold">Management Lab - Eksekusi Permintaan (Tim ITSM)</h5>

                    {{-- RINGKASAN PERMINTAAN DARI PENGAJU --}}
                    <div class="alert alert-info mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-info-circle"></i> Informasi Permintaan:</h6>
                        <div class="row small">
                            <div class="col-md-6">
                                <strong>Peminta:</strong> {{ $data->karyawan->nama_lengkap ?? '-' }} ({{ $data->karyawan->jabatan ?? '-' }})<br>
                                <strong>RKM / Klien:</strong> {{ $data->rkm->perusahaan->nama_perusahaan ?? 'Perpanjangan Internal ITSM' }}<br>
                                <strong>Materi:</strong> {{ $data->rkm->materi->nama_materi ?? '-' }}
                            </div>
                            <div class="col-md-6">
                                <strong>Jadwal Kelas:</strong>
                                @if($data->rkm && $data->rkm->tanggal_awal)
                                    {{ \Carbon\Carbon::parse($data->rkm->tanggal_awal)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($data->rkm->tanggal_akhir)->format('d M Y') }}
                                @else
                                    -
                                @endif
                                <br>
                                <strong>Lab Diminta:</strong> {{ $data->lab_snapshot['requested_nama_labs'] ?? $data->lab->nama_labs ?? '-' }} ({{ $data->lab_snapshot['requested_merk'] ?? $data->lab->merk ?? '-' }})<br>
                                <strong>Catatan:</strong> {{ $data->lab_snapshot['requested_catatan'] ?? $data->lab->desc ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <form id="labForm" method="POST" action="{{ route('pengajuanlabsdansubs.updatelabsubs', $data->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- PILIH DARI MASTER LAB ATAU TETAP GUNAKAN DRAFT --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label text-md-start fw-bold">Tautkan Master Lab</label>
                            <div class="col-md-6">
                                <select name="id_labs_selected" id="id_labs_selected" class="form-select">
                                    @foreach($masterLabs as $mLab)
                                        <option value="{{ $mLab->id }}"
                                                data-lab='@json($mLab)'
                                                {{ $data->id_labs == $mLab->id ? 'selected' : '' }}>
                                            [{{ strtoupper($mLab->status) }}] {{ $mLab->nama_labs }} ({{ $mLab->merk ?? '-' }})
                                            @if($mLab->end_date) - Exp: {{ \Carbon\Carbon::parse($mLab->end_date)->format('d/m/Y') }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih lab jika sudah ada di Master Lab untuk memuat data otomatis.</small>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- KEPUTUSAN EKSEKUSI TIM ITSM --}}
                        <div class="row mb-4">
                            <label class="col-md-4 col-form-label text-md-start fw-bold text-primary">Keputusan Eksekusi ITSM <span class="text-danger">*</span></label>
                            <div class="col-md-8">
                                <select name="keputusan_itsm" id="keputusan_itsm" class="form-select border-primary fw-bold" required>
                                    <option value="berikan_akses" {{ $data->jenis_transaksi == 'existing' && $data->lab?->status == 'active' ? 'selected' : '' }}>
                                        1. Lab Masih Aktif -> Berikan Akses Sekarang (Permintaan Akses)
                                    </option>
                                    <option value="pengajuan_barang_baru" {{ $data->jenis_transaksi == 'baru' ? 'selected' : '' }}>
                                        2. Lab Baru -> Buat Pengajuan Barang atas nama {{ auth()->user()->karyawan->jabatan }}
                                    </option>
                                    <option value="pengajuan_barang_perpanjangan" {{ $data->jenis_transaksi == 'pembaharuan' ? 'selected' : '' }}>
                                        3. Masa Aktif Habis / Expired -> Ajukan Perpanjangan Barang atas nama {{ auth()->user()->karyawan->jabatan }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- 1. INFORMASI UMUM LAB --}}
                        <div class="row mb-3">
                            <label for="nama_labs" class="col-md-4 col-form-label text-md-start">Nama Lab / Software <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama_labs" id="nama_labs" value="{{ old('nama_labs', $data->lab->nama_labs ?? '') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="merk" class="col-md-4 col-form-label text-md-start">Vendor / Merk</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="merk" id="merk" value="{{ old('merk', $data->lab->merk ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">Tipe Aset</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select" required>
                                    <option value="subscription" {{ ($data->lab->tipe ?? '') == 'subscription' ? 'selected' : '' }}>Subscription (Berlangganan)</option>
                                    <option value="one-time" {{ ($data->lab->tipe ?? '') == 'one-time' ? 'selected' : '' }}>One-Time (Sekali Beli)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3" id="row_duration" style="display: none;">
                            <label for="duration_minutes" class="col-md-4 col-form-label text-md-start">Durasi (Menit)</label>
                            <div class="col-md-6">
                                <input type="number" class="form-control" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $data->lab->duration_minutes ?? '') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">Status Lab</label>
                            <div class="col-md-6">
                                <select name="status" id="status" class="form-select" required>
                                    <option value="pending" {{ ($data->lab->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="active" {{ ($data->lab->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ ($data->lab->status ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="desc" class="col-md-4 col-form-label text-md-start">Deskripsi / Catatan Akses</label>
                            <div class="col-md-6">
                                <textarea name="desc" id="desc" class="form-control" rows="2">{{ old('desc', $data->lab->desc ?? '') }}</textarea>
                            </div>
                        </div>

                        {{-- 2. DETAIL AKSES --}}
                        <hr class="my-3">
                        <h6 class="fw-bold mb-3">Detail Akses & Masa Aktif</h6>

                        <div class="row mb-3">
                            <label for="lab_url" class="col-md-4 col-form-label text-md-start">URL Lab</label>
                            <div class="col-md-6">
                                <input type="url" class="form-control" name="lab_url" id="lab_url" value="{{ old('lab_url', $data->lab->lab_url ?? '') }}" placeholder="https://...">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="access_code" class="col-md-4 col-form-label text-md-start">Kode Akses / Credentials</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="access_code" id="access_code" value="{{ old('access_code', $data->lab->access_code ?? '') }}" placeholder="Username / Password / Token">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="start_date" class="col-md-4 col-form-label text-md-start">Tanggal Mulai Aktif</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                       value="{{ !empty($data->lab->start_date) ? \Carbon\Carbon::parse($data->lab->start_date)->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="end_date" class="col-md-4 col-form-label text-md-start">Tanggal Berakhir</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="end_date" id="end_date"
                                       value="{{ !empty($data->lab->end_date) ? \Carbon\Carbon::parse($data->lab->end_date)->format('Y-m-d') : '' }}">
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
                                            <option value="{{ $currency }}" {{ ($data->lab->mata_uang ?? 'Rupiah') == $currency ? 'selected' : '' }}>
                                                {{ $currency }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="harga" class="col-md-4 col-form-label text-md-start">Nominal Harga Asli</label>
                                <div class="col-md-6">
                                    <input type="number" step="0.01" class="form-control" name="harga" id="harga"
                                           value="{{ old('harga', $data->lab->harga ?? 0) }}">
                                </div>
                            </div>

                            <div class="row mb-3" id="row_kurs">
                                <label for="kurs" class="col-md-4 col-form-label text-md-start">Kurs (Rate)</label>
                                <div class="col-md-6">
                                    <input type="number" step="0.01" class="form-control" name="kurs" id="kurs"
                                           value="{{ old('kurs', $data->lab->kurs ?? 1) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="harga_rupiah" class="col-md-4 col-form-label text-md-start">Estimasi Rupiah</label>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control bg-light" name="harga_rupiah" id="harga_rupiah"
                                               value="{{ old('harga_rupiah', !empty($data->lab->harga_rupiah) ? number_format($data->lab->harga_rupiah, 0, ',', '.') : '0') }}" readonly>
                                    </div>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        function calculateTotal() {
            var currency = $('#mata_uang').val();
            var harga = parseFloat($('#harga').val()) || 0;
            var kurs = parseFloat($('#kurs').val()) || 1;

            if (currency === 'Rupiah') {
                $('#kurs').val(1).prop('readonly', true);
                kurs = 1;
            } else {
                $('#kurs').prop('readonly', false);
            }
            var total = harga * kurs;
            $('#harga_rupiah').val(formatRupiah(Math.floor(total).toString()));
        }

        function toggleDuration() {
            if ($('#tipe').val() === 'one-time') {
                $('#row_duration').slideDown();
            } else {
                $('#row_duration').slideUp();
                $('#duration_minutes').val('');
            }
        }

        function handleKeputusanChange() {
            let val = $('#keputusan_itsm').val();
            if (val === 'berikan_akses') {
                $('#status').val('active');
            }
        }

        // Saat Master Lab diganti dari dropdown, isi form dengan data lab tersebut
        $('#id_labs_selected').on('change', function() {
            let lab = $(this).find(':selected').data('lab');
            if (lab) {
                $('#nama_labs').val(lab.nama_labs || '');
                $('#merk').val(lab.merk || '');
                $('#tipe').val(lab.tipe || 'one-time').trigger('change');
                $('#duration_minutes').val(lab.duration_minutes || '');
                $('#status').val(lab.status || 'pending');
                $('#desc').val(lab.desc || '');
                $('#lab_url').val(lab.lab_url || '');
                $('#access_code').val(lab.access_code || '');
                $('#start_date').val(lab.start_date ? lab.start_date.split(' ')[0] : '');
                $('#end_date').val(lab.end_date ? lab.end_date.split(' ')[0] : '');
                $('#mata_uang').val(lab.mata_uang || 'Rupiah');
                $('#harga').val(lab.harga || 0);
                $('#kurs').val(lab.kurs || 1);
                calculateTotal();
            }
        });

        $('#mata_uang').on('change', calculateTotal);
        $('#harga, #kurs').on('input', calculateTotal);
        $('#tipe').on('change', toggleDuration);
        $('#keputusan_itsm').on('change', handleKeputusanChange);

        calculateTotal();
        toggleDuration();

        $('#labForm').on('submit', function () {
            $('#harga_rupiah').val($('#harga_rupiah').val().replace(/\./g, ''));
        });
    });
</script>
@endsection
