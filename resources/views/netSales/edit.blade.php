@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Loading Modal --}} 
        <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0 shadow-none">
                    <div class="d-flex justify-content-center">
                        <div class="loader"></div>
                    </div>
                    <h5 class="text-white text-center mt-2 font-weight-bold">Memproses Data...</h5>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            {{-- UPDATE FORM --}}
            <form method="POST" action="{{ route('netSales.update') }}" id="post">
                @csrf
                @method('PUT') {{-- Method PUT wajib untuk Update --}}

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body" id="card">
                            <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                                <img src="{{ asset('icon/arrow-left.svg') }}" width="20px"> Back
                            </a>
                            <h5 class="card-title text-center mb-4">{{ __('Edit Analisis Payment Advance') }}</h5>

                            {{-- HIDDEN ID & ID_RKM --}}
                            <input type="hidden" name="id_netsales" value="{{ $dataNetSales->id }}">
                            <input type="hidden" name="id_rkm" value="{{ $rkm->id }}">

                            {{-- INFORMASI RKM (READONLY) --}}
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h6 class="text-primary mb-3">Informasi Kegiatan (RKM)</h6>
                                </div>

                                {{-- Kolom Kiri RKM --}}
                                <div class="col-md-6">
                                    <div class="row mb-2">
                                        <label class="col-md-4 col-form-label">{{ __('Nama Materi') }}</label>
                                        <div class="col-md-8">
                                            <input readonly type="text" class="form-control bg-light"
                                                value="{{ $rkm->materi->nama_materi }}">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label class="col-md-4 col-form-label">{{ __('Nama Perusahaan') }}</label>
                                        <div class="col-md-8">
                                            <input readonly type="text" class="form-control bg-light"
                                                value="{{ $rkm->perusahaan->nama_perusahaan }}">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label class="col-md-4 col-form-label">{{ __('Kelas') }}</label>
                                        <div class="col-md-8">
                                            <input readonly type="text" class="form-control bg-light"
                                                value="{{ $rkm->metode_kelas }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan RKM --}}
                                <div class="col-md-6">
                                    <div class="row mb-2">
                                        <label class="col-md-4 col-form-label">{{ __('Tanggal') }}</label>
                                        <div class="col-md-8">
                                            <input readonly type="text" class="form-control bg-light"
                                                value="{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d M Y') }}">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label class="col-md-4 col-form-label">{{ __('Pax') }}</label>
                                        <div class="col-md-8">
                                            <input readonly type="text" class="form-control bg-light"
                                                value="{{ $rkm->pax }} Orang">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- FORM INPUT DETAIL BIAYA --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="text-primary mb-3">Detail Pengajuan Biaya</h6>
                                </div>

                                {{-- KOLOM KIRI --}}
                                <div class="col-lg-6 col-md-12">
                                    {{-- Transportasi --}}
                                    <div class="mb-3 row">
                                        <label for="transportasi"
                                            class="col-md-4 col-form-label">{{ __('Transportasi') }}</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="transportasi" type="text" class="form-control rupiah"
                                                    name="transportasi"
                                                    value="{{ old('transportasi', $dataNetSales->transportasi) }}"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="jenis_transportasi"
                                            class="col-md-4 col-form-label">{{ __('Jenis Transportasi') }}</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="jenis_transportasi"
                                                value="{{ old('jenis_transportasi', $dataNetSales->jenis_transportasi) }}"
                                                placeholder="Contoh: Pesawat">
                                        </div>
                                    </div>

                                    {{-- Akomodasi Peserta --}}
                                    <div class="mb-3 row">
                                        <label for="akomodasi_peserta"
                                            class="col-md-4 col-form-label">{{ __('Akomodasi Peserta') }}</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="akomodasi_peserta" type="text" class="form-control rupiah"
                                                    name="akomodasi_peserta"
                                                    value="{{ old('akomodasi_peserta', $dataNetSales->akomodasi_peserta) }}"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Akomodasi Tim --}}
                                    <div class="mb-3 row">
                                        <label for="akomodasi_tim"
                                            class="col-md-4 col-form-label">{{ __('Akomodasi Tim') }}</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="akomodasi_tim" type="text" class="form-control rupiah"
                                                    name="akomodasi_tim"
                                                    value="{{ old('akomodasi_tim', $dataNetSales->akomodasi_tim) }}"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="keterangan_akomodasi_tim"
                                            class="col-md-4 col-form-label">{{ __('Ket. Akomodasi Tim') }}</label>
                                        <div class="col-md-8">
                                            <textarea name="keterangan_akomodasi_tim" class="form-control" rows="1" placeholder="Opsional">{{ old('keterangan_akomodasi_tim', $dataNetSales->keterangan_akomodasi_tim) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOLOM KANAN --}}
                                <div class="col-lg-6 col-md-12">
                                    {{-- Fresh Money --}}
                                    <div class="mb-3 row">
                                        <label for="fresh_money"
                                            class="col-md-4 col-form-label">{{ __('Fresh Money') }}</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="fresh_money" type="text" class="form-control rupiah"
                                                    name="fresh_money"
                                                    value="{{ old('fresh_money', $dataNetSales->fresh_money) }}"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Entertaint --}}
                                    <div class="mb-3 row">
                                        <label for="entertaint"
                                            class="col-md-4 col-form-label">{{ __('Entertaint') }}</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="entertaint" type="text" class="form-control rupiah"
                                                    name="entertaint"
                                                    value="{{ old('entertaint', $dataNetSales->entertaint) }}"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="keterangan_entertaint"
                                            class="col-md-4 col-form-label">{{ __('Ket. Entertaint') }}</label>
                                        <div class="col-md-8">
                                            <textarea name="keterangan_entertaint" class="form-control" rows="1" placeholder="Opsional">{{ old('keterangan_entertaint', $dataNetSales->keterangan_entertaint) }}</textarea>
                                        </div>
                                    </div>

                                    {{-- Lain-lain --}}
                                    <div class="mb-3 row">
                                        <label for="souvenir"
                                            class="col-md-4 col-form-label">{{ __('Souvenir') }}</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="souvenir" type="text" class="form-control rupiah"
                                                    name="souvenir"
                                                    value="{{ old('souvenir', $dataNetSales->souvenir) }}"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="cashback"
                                            class="col-md-4 col-form-label">{{ __('Cashback') }}</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="cashback" type="text" class="form-control rupiah"
                                                    name="cashback"
                                                    value="{{ old('cashback', $dataNetSales->cashback) }}"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="sewa_laptop"
                                            class="col-md-4 col-form-label">{{ __('Sewa Laptop') }}</label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input id="sewa_laptop" type="text" class="form-control rupiah"
                                                    name="sewa_laptop"
                                                    value="{{ old('sewa_laptop', $dataNetSales->sewa_laptop) }}"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- ADMINISTRASI --}}
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h6 class="text-primary mb-3">Administrasi</h6>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3 row">
                                        <label for="tgl_pa"
                                            class="col-md-4 col-form-label">{{ __('Tgl Pengajuan') }}</label>
                                        <div class="col-md-8">
                                            <input id="tgl_pa" type="date" class="form-control" name="tgl_pa"
                                                required
                                                value="{{ old('tgl_pa', \Carbon\Carbon::parse($dataNetSales->tgl_pa)->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3 row">
                                        <label for="tipe_pembayaran"
                                            class="col-md-4 col-form-label">{{ __('Tipe Bayar') }}</label>
                                        <div class="col-md-8">
                                            <select name="tipe_pembayaran" id="tipe_pembayaran" class="form-control"
                                                required>
                                                <option value="" disabled>Pilih...</option>
                                                <option value="Cash" @selected(old('tipe_pembayaran', $dataNetSales->tipe_pembayaran) == 'Cash')>Cash</option>
                                                <option value="Transfer" @selected(old('tipe_pembayaran', $dataNetSales->tipe_pembayaran) == 'Transfer')>Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <label for="deskripsi_tambahan"
                                            class="col-md-2 col-form-label">{{ __('Deskripsi') }}</label>
                                        <div class="col-md-10">
                                            <textarea name="deskripsi_tambahan" class="form-control" rows="2">{{ old('deskripsi_tambahan', $dataNetSales->deskripsi_tambahan) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SUBMIT --}}
                            <div class="row mt-4">
                                <div class="col-md-12 text-end">
                                    <button type="submit" class="btn click-primary btn-lg px-5 shadow">
                                        {{ __('Simpan Perubahan') }}
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- CSS UNTUK LOADER --}}
    <style>
        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            // Daftar field biaya
            const costFields = [
                'transportasi',
                'akomodasi_peserta',
                'akomodasi_tim',
                'fresh_money',
                'entertaint',
                'souvenir',
                'cashback',
                'sewa_laptop'
            ];

            const $fields = costFields.map(id => $(`#${id}`));
            const $totalDisplay = $('#total_display');

            // Fungsi format rupiah TANPA desimal (tanpa ,00)
            function formatRupiah(angka) {
                // Hapus semua karakter selain angka
                let number_string = angka.toString().replace(/[^0-9]/g, '');

                // Format ribuan dengan titik
                let sisa = number_string.length % 3;
                let rupiah = number_string.substr(0, sisa);
                let ribuan = number_string.substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                return rupiah || '0';
            }

            // Parse string format rupiah ke angka (untuk kalkulasi)
            function parseRupiah(str) {
                if (!str) return 0;
                // Hapus titik dan prefix Rp jika ada
                return parseInt(str.replace(/[^0-9]/g, '')) || 0;
            }

            // Hitung total dan tampilkan
            function calculateAndDisplayTotal() {
                let total = 0;

                $fields.forEach($input => {
                    let nilai = parseRupiah($input.val());
                    let id = $input.attr('id');

                    if (id === 'cashback') {
                        total -= nilai;
                    } else {
                        total += nilai;
                    }
                });

                // Tampilkan total tanpa ,00
                const formattedTotal = formatRupiah(total);
                if ($totalDisplay.is('input, textarea')) {
                    $totalDisplay.val(formattedTotal);
                } else {
                    $totalDisplay.text(formattedTotal);
                }
            }

            // Event saat user mengetik
            $fields.forEach($input => {
                $input.on('input', function() {
                    let clean = $(this).val().replace(/[^0-9]/g, ''); // hanya angka
                    $(this).val(formatRupiah(clean));
                    calculateAndDisplayTotal();
                });

                // Saat fokus: hapus format supaya mudah diedit
                $input.on('focus', function() {
                    let val = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(val);
                });

                // Saat keluar fokus: format ulang
                $input.on('blur', function() {
                    let val = $(this).val().replace(/[^0-9]/g, '');
                    $(this).val(formatRupiah(val));
                });
            });

            // Inisialisasi nilai dari database
            function initializeForm() {
                $fields.forEach($input => {
                    let val = $input.val();
                    if (val && val !== '0') {
                        // Pastikan nilai dari DB dibersihkan dulu
                        let clean = val.toString().replace(/[^0-9]/g, '');
                        $input.val(formatRupiah(clean));
                    }
                });
                calculateAndDisplayTotal();
            }

            initializeForm();

            // Loading saat submit
            $('#post').on('submit', function() {
                $('#loadingModal').modal('show');
                $(this).find('button[type="submit"]').prop('disabled', true);
            });
        });
    </script>
@endsection
