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
            <form method="POST" action="{{ route('paymantAdvance.store') }}" id="post">
                @csrf
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body" id="card">
                            <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                                <img src="{{ asset('icon/arrow-left.svg') }}" width="20px"> Back
                            </a>
                            <h5 class="card-title text-center mb-4">{{ __('Analisis Payment Advance') }}</h5>

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

                            {{-- FORM INPUT PAYMENT ADVANCE --}}
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
                                                    name="transportasi" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="jenis_transportasi"
                                            class="col-md-4 col-form-label">{{ __('Jenis Transportasi') }}</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="jenis_transportasi"
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
                                                    name="akomodasi_peserta" placeholder="0">
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
                                                    name="akomodasi_tim" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="keterangan_akomodasi_tim"
                                            class="col-md-4 col-form-label">{{ __('Ket. Akomodasi Tim') }}</label>
                                        <div class="col-md-8">
                                            <textarea name="keterangan_akomodasi_tim" class="form-control" rows="1" placeholder="Opsional"></textarea>
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
                                                    name="fresh_money" placeholder="0">
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
                                                    name="entertaint" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="keterangan_entertaint"
                                            class="col-md-4 col-form-label">{{ __('Ket. Entertaint') }}</label>
                                        <div class="col-md-8">
                                            <textarea name="keterangan_entertaint" class="form-control" rows="1" placeholder="Opsional"></textarea>
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
                                                    name="souvenir" placeholder="0">
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
                                                    name="cashback" placeholder="0">
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
                                                    name="sewa_laptop" placeholder="Opsional">
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
                                                required value="{{ date('Y-m-d') }}">
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
                                                <option value="" disabled selected>Pilih...</option>
                                                <option value="Cash">Cash</option>
                                                <option value="Transfer">Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3 row">
                                        <label for="deskripsi_tambahan"
                                            class="col-md-2 col-form-label">{{ __('Deskripsi') }}</label>
                                        <div class="col-md-10">
                                            <textarea name="deskripsi_tambahan" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SUBMIT --}}
                            <div class="row mt-4">
                                <div class="col-md-12 text-end">
                                    <button type="submit" class="btn click-primary btn-lg px-5 shadow">
                                        {{ __('Simpan Pengajuan') }}
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

    {{-- SCRIPT CLEAN & TERINTEGRASI --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // 1. Array ID semua input biaya yang harus dihitung
            const costFields = [
                '#transportasi',
                '#akomodasi_peserta',
                '#akomodasi_tim',
                '#fresh_money',
                '#entertaint',
                '#souvenir',
                '#cashback',
                '#sewa_laptop'
            ];

            // 2. Event Listener untuk Input (Format Rupiah & Hitung Total)
            $(costFields.join(', ')).on('input', function() {
                let val = $(this).val();
                $(this).val(formatRupiah(val));
                calculateTotal();
            });

            // 3. Fungsi Format Rupiah (Visual dengan titik)
            function formatRupiah(angka, prefix) {
                let number_string = angka.replace(/[^,\d]/g, '').toString(),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
                return prefix === undefined ? rupiah : (rupiah ? prefix + rupiah : '');
            }

            // 4. Fungsi Parse Rupiah ke Float (untuk kalkulasi matematika)
            function parseRupiah(value) {
                if (!value) return 0;
                return parseFloat(value.replace(/\./g, '').replace(/,/g, '.')) || 0;
            }

            // 6. Handle Submit Form
            $('#post').on('submit', function(e) {
                $('#loadingModal').modal('show');

                $(this).find('button[type="submit"]').prop('disabled', true);
            });
        });
    </script>
@endsection
