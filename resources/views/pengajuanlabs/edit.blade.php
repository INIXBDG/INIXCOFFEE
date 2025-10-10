@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn btn-primary mb-3">
                        <img src="{{ asset('icon/arrow-left.svg') }}" width="20"> Back
                    </a>

                    <h5 class="mb-4">Edit {{ $data->id_subs ? 'Subscription' : 'Lab' }}</h5>

                    {{-- ✅ Form dengan ID dinamis agar JS bisa mendeteksi --}}
                    <form id="{{ $data->subs ? 'form_subs' : 'form_lab' }}"
                          action="{{ route('pengajuanlabsdansubs.updatelabsubs', $data->id) }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        {{-- ======================== FORM SUBS ======================== --}}
                        @if ($data->subs)
                            <div class="mb-3">
                                <label class="form-label">Nama Subscription</label>
                                <input type="text" name="nama_subs" class="form-control" value="{{ old('nama_subs', $data->subs->nama_subs) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Merk</label>
                                <input type="text" name="merk" class="form-control" value="{{ old('merk', $data->subs->merk) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="desc" class="form-control" rows="2">{{ old('desc', $data->subs->desc) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">URL</label>
                                <input type="text" name="subs_url" class="form-control" value="{{ old('subs_url', $data->subs->subs_url) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Access Code</label>
                                <input type="text" name="access_code" class="form-control" value="{{ old('access_code', $data->subs->access_code) }}">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Mata Uang</label>
                                    <select name="mata_uang" id="mata_uang_subs" class="form-select">
                                        <option value="">Pilih Mata Uang</option>
                                        @foreach (['Rupiah', 'Dollar', 'Poundsterling', 'Euro', 'Franc Swiss'] as $currency)
                                            <option value="{{ $currency }}" {{ $data->subs->mata_uang == $currency ? 'selected' : '' }}>
                                                {{ $currency }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" id="harga_label_subs">
                                        Harga {{ $data->subs->mata_uang ?: '' }}
                                    </label>
                                    <input type="number" id="harga_subs" name="harga" class="form-control"
                                           value="{{ old('harga', $data->subs->harga) }}">
                                </div>
                            </div>

                            <div class="row mb-3" id="kurs_group_subs">
                                <div class="col-md-6">
                                    <label class="form-label">Kurs</label>
                                    <input type="number" id="kurs_subs" step="0.01" name="kurs" class="form-control"
                                           value="{{ old('kurs', $data->subs->kurs ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Harga Rupiah</label>
                                    <input type="text" id="harga_rupiah_subs" name="harga_rupiah" class="form-control"
                                           value="{{ old('harga_rupiah', $data->subs->harga_rupiah ?? '') }}">
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_date">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="{{ old('start_date', $data->subs->start_date ? \Carbon\Carbon::parse($data->subs->start_date)->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date">Tanggal Berakhir</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="{{ old('end_date', $data->subs->end_date ? \Carbon\Carbon::parse($data->subs->end_date)->format('Y-m-d') : '') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="{{ $data->subs->status }}" selected>{{ ucfirst($data->subs->status) }}</option>
                                    @foreach (['Active', 'Expired', 'Pending'] as $status)
                                        @if ($status !== $data->subs->status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                        {{-- ======================== FORM LAB ======================== --}}
                        @elseif ($data->lab)
                            <div class="mb-3">
                                <label class="form-label">Nama Lab</label>
                                <input type="text" name="nama_labs" class="form-control" value="{{ old('nama_labs', $data->lab->nama_labs) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="desc" class="form-control" rows="2">{{ old('desc', $data->lab->desc) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">URL</label>
                                <input type="text" name="lab_url" class="form-control" value="{{ old('lab_url', $data->lab->lab_url) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Access Code</label>
                                <input type="text" name="access_code" class="form-control" value="{{ old('access_code', $data->lab->access_code) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Durasi (menit)</label>
                                <input type="number" name="duration_minutes" class="form-control"
                                       value="{{ old('duration_minutes', $data->lab->duration_minutes) }}">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Mata Uang</label>
                                    <select name="mata_uang" id="mata_uang_lab" class="form-select">
                                        <option value="">Pilih Mata Uang</option>
                                        @foreach (['Rupiah', 'Dollar', 'Poundsterling', 'Euro', 'Franc Swiss'] as $currency)
                                            <option value="{{ $currency }}" {{ $data->lab->mata_uang == $currency ? 'selected' : '' }}>
                                                {{ $currency }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" id="harga_label_lab">
                                        Harga {{ $data->lab->mata_uang ?: '' }}
                                    </label>
                                    <input type="number" id="harga_lab" name="harga" class="form-control"
                                           value="{{ old('harga', $data->lab->harga) }}">
                                </div>
                            </div>

                            <div class="row mb-3" id="kurs_group_lab">
                                <div class="col-md-6">
                                    <label class="form-label">Kurs</label>
                                    <input type="number" id="kurs_lab" step="0.01" name="kurs" class="form-control"
                                           value="{{ old('kurs', $data->lab->kurs ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Harga Rupiah</label>
                                    <input type="text" id="harga_rupiah_lab" name="harga_rupiah" class="form-control"
                                           value="{{ old('harga_rupiah', $data->lab->harga_rupiah ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_date">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="{{ old('start_date', $data->lab->start_date ? \Carbon\Carbon::parse($data->lab->start_date)->format('Y-m-d') : '') }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="end_date">Tanggal Berakhir</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="{{ old('end_date', $data->lab->end_date ? \Carbon\Carbon::parse($data->lab->end_date)->format('Y-m-d') : '') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="{{ $data->lab->status }}" selected>{{ ucfirst($data->lab->status) }}</option>
                                    @foreach (['Active', 'Expired', 'Pending'] as $status)
                                        @if ($status !== $data->lab->status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ====================== SCRIPT ====================== --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // 🔹 Fungsi untuk format angka ke Rupiah
        function formatRupiah(angka) {
            if (!angka && angka !== 0) return '';
            angka = Math.floor(angka); // buang desimal, hanya tampil integer dalam rupiah
            const number_string = angka.toString().replace(/[^,\d]/g, '');
            const split = number_string.split(',');
            const sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            const ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) rupiah += (sisa ? '.' : '') + ribuan.join('.');
            return 'Rp ' + rupiah;
        }

        // 🔹 Fungsi utama untuk meng-handle kurs & harga
        function initCurrencyHandler(selectId, labelId, kursInputId, hargaInputId, hargaRupiahId) {
            const mataUang = document.getElementById(selectId);
            const hargaLabel = document.getElementById(labelId);
            const kursInput = document.getElementById(kursInputId);
            const hargaInput = document.getElementById(hargaInputId);
            const hargaRupiah = document.getElementById(hargaRupiahId);

            if (!mataUang) return;

            // ✅ Perhitungan otomatis harga rupiah
            function updateHargaRupiah() {
                const val = mataUang.value;

                // pastikan angka koma tetap dihitung
                const harga = parseFloat(hargaInput.value.replace(',', '.')) || 0;
                const kurs = parseFloat(kursInput.value.replace(',', '.')) || 0;

                let total = 0;
                if (val === 'Rupiah') {
                    total = harga;
                } else {
                    total = harga * kurs;
                }

                // format ke rupiah tanpa desimal
                hargaRupiah.value = total ? formatRupiah(total) : '';
            }

            // ✅ Menyembunyikan atau menampilkan kolom kurs sesuai mata uang
            function toggleKurs() {
                const val = mataUang.value;
                const kursCol = kursInput.closest('.col-md-6');
                const hargaRupiahCol = hargaRupiah.closest('.col-md-6');
                hargaLabel.textContent = val ? `Harga ${val}` : 'Harga';

                if (val === 'Rupiah') {
                    kursCol.style.display = 'none';
                    hargaRupiahCol.style.display = 'block';
                    hargaRupiah.readOnly = true;
                    hargaRupiah.value = formatRupiah(parseFloat(hargaInput.value.replace(',', '.')) || 0);
                } else {
                    kursCol.style.display = 'block';
                    hargaRupiahCol.style.display = 'block';
                    hargaRupiah.readOnly = true;
                    updateHargaRupiah();
                }
            }

            // event listener
            mataUang.addEventListener('change', toggleKurs);
            hargaInput.addEventListener('input', updateHargaRupiah);
            kursInput.addEventListener('input', updateHargaRupiah);

            toggleKurs();
        }

        // Jalankan handler untuk SUBS & LAB
        initCurrencyHandler('mata_uang_subs', 'harga_label_subs', 'kurs_subs', 'harga_subs', 'harga_rupiah_subs');
        initCurrencyHandler('mata_uang_lab', 'harga_label_lab', 'kurs_lab', 'harga_lab', 'harga_rupiah_lab');
    });
</script>
@endsection
