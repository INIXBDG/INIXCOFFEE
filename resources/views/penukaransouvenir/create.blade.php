@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('penukaransouvenir.index') }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Kembali
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Input Penukaran Souvenir') }}</h5>

                    {{-- Alert Messages --}}
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('penukaransouvenir.store') }}">
                        @csrf

                        {{-- === BAGIAN 1: IDENTITAS PENGINPUT (Sama seperti referensi) === --}}
                        {{-- Ini opsional, tapi saya pertahankan agar UI konsisten dengan halaman Penambahan --}}
                        <div class="row mb-3 bg-light p-2 rounded mx-1">
                            <div class="col-12 text-center mb-2"><strong>Petugas Penukaran</strong></div>

                            <label class="col-md-4 col-form-label text-md-start">Nama Karyawan</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ auth()->user()->karyawan->nama ?? auth()->user()->username }}" readonly disabled style="background-color: #e9ecef; font-weight:bold;">
                            </div>
                        </div>

                        <hr>

                        {{-- === BAGIAN 2: PILIH EVENT & PESERTA === --}}
                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">Pilih RKM</label>
                            <div class="col-md-6">
                                <select id="select_rkm" name="id_rkm" class="form-select" required>
                                    <option value="">-- Pilih RKM --</option>
                                    @foreach($rkms as $rkm)
                                        <option value="{{ $rkm->id }}">
                                            [{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M') }} - {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d M') }}] -
                                            {{ $rkm->materi->nama_materi ?? $rkm->nama_program }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 0.75rem">Pilih RKM untuk memuat daftar peserta.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="id_regist" class="col-md-4 col-form-label text-md-start">Nama Peserta</label>
                            <div class="col-md-6">
                                <select id="select_peserta" name="id_regist" class="form-select" required disabled>
                                    <option value="">-- Pilih RKM Terlebih Dahulu --</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-center mb-3">Detail Penukaran Barang</h6>

                        {{-- === BAGIAN 3: DETAIL BARANG (LAMA vs BARU) === --}}
                        <div id="itemContainer">
                            {{-- Container Info Barang --}}
                            <div class="item mb-3 p-3 border rounded shadow-sm">

                                {{-- Souvenir Lama (Read Only - Auto Fill) --}}
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label text-md-start text-danger">Souvenir Saat Ini</label>
                                    <div class="col-md-8">
                                        <input type="text" id="info_souvenir_lama" class="form-control bg-light" readonly value="-" style="font-weight: bold; color: #dc3545;">
                                        {{-- Hidden input untuk validasi --}}
                                        <input type="hidden" id="id_souvenir_lama_check" name="id_souvenir_lama_check">
                                    </div>
                                </div>

                                {{-- Icon Panah --}}
                                <div class="row mb-3 text-center">
                                    <div class="col-12">
                                        <i class="bi bi-arrow-down-circle-fill text-primary" style="font-size: 1.5rem;"></i>
                                        <p class="text-muted small mb-0">Akan ditukar menjadi</p>
                                    </div>
                                </div>

                                {{-- Souvenir Baru (Select) --}}
                                <div class="row">
                                    <label class="col-md-4 col-form-label text-md-start text-success">Souvenir Pengganti</label>
                                    <div class="col-md-8">
                                        <select name="id_souvenir_baru" class="form-select souvenir-select" required>
                                            <option value="">-- Pilih Souvenir Baru --</option>
                                            @foreach($souvenirs as $s)
                                                <option value="{{ $s->id }}" data-stok="{{ $s->stok }}">
                                                    {{ $s->nama_souvenir }} (Stok: {{ $s->stok }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-success stok-info" style="display:none; font-weight:bold">
                                            Sisa stok tersedia: <span class="stok-val">-</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Simpan --}}
                        <div class="row mb-0 mt-4">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary w-100">
                                    Proses Penukaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        // 1. Logika AJAX: Load Peserta saat RKM dipilih
        $('#select_rkm').change(function() {
            var rkmId = $(this).val();
            var $pesertaSelect = $('#select_peserta');
            var $infoLama = $('#info_souvenir_lama');

            // Reset field
            $pesertaSelect.empty().append('<option value="">-- Memuat Data... --</option>').prop('disabled', true);
            $infoLama.val('-');

            if (rkmId) {
                // Panggil API get-peserta
                $.ajax({
                    url: "{{ url('/get-peserta') }}/" + rkmId,
                    type: "GET",
                    success: function(data) {
                        $pesertaSelect.empty().append('<option value="">-- Pilih Peserta --</option>');

                        if (data.length > 0) {
                            $.each(data, function(key, val) {
                                // Simpan data souvenir lama di atribut data-souvenir
                                $pesertaSelect.append(`
                                    <option value="${val.id_regist}"
                                            data-souvenir-name="${val.nama_souvenir_lama}"
                                            data-souvenir-id="${val.id_souvenir_lama}">
                                        ${val.nama_peserta}
                                    </option>
                                `);
                            });
                            $pesertaSelect.prop('disabled', false);
                        } else {
                            $pesertaSelect.append('<option value="">Tidak ada peserta dengan souvenir di RKM ini</option>');
                        }
                    },
                    error: function() {
                        alert('Gagal mengambil data peserta. Pastikan koneksi aman.');
                        $pesertaSelect.empty().append('<option value="">Error</option>');
                    }
                });
            } else {
                $pesertaSelect.empty().append('<option value="">-- Pilih RKM Terlebih Dahulu --</option>');
            }
        });

        // 2. Logika Auto-Fill Souvenir Lama
        $('#select_peserta').change(function() {
            var selectedOption = $(this).find('option:selected');
            var namaSouvenir = selectedOption.data('souvenir-name');
            var idSouvenir = selectedOption.data('souvenir-id');

            if (namaSouvenir) {
                $('#info_souvenir_lama').val(namaSouvenir);
                $('#id_souvenir_lama_check').val(idSouvenir);
            } else {
                $('#info_souvenir_lama').val('-');
                $('#id_souvenir_lama_check').val('');
            }
        });

        // 3. Logika Tampilan Stok (Sama seperti referensi Anda)
        $(document).on('change', '.souvenir-select', function() {
            var selectedOption = $(this).find('option:selected');
            var maxStock = parseInt(selectedOption.data('stok')) || 0;
            var $stokInfo = $(this).closest('.item').find('.stok-info'); // Cari elemen stok info terdekat
            var $stokVal = $(this).closest('.item').find('.stok-val');

            if (this.value) {
                $stokVal.text(maxStock);
                $stokInfo.show();

                // Validasi sederhana jika stok habis
                if(maxStock <= 0) {
                    alert('Stok barang ini habis! Silakan pilih barang lain.');
                    $(this).val('');
                    $stokInfo.hide();
                }
            } else {
                $stokInfo.hide();
            }
        });
    });
</script>
@endpush
