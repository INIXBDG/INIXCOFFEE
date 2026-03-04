@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('penambahansouvenir.index') }}" class="btn click-primary mb-3">
                        <img src="{{ asset('icon/arrow-left.svg') }}" width="20px"> Kembali
                    </a>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('penambahansouvenir.update', $penambahan->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- Tanggal --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">Tanggal Distribusi</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="tanggal" value="{{ old('tanggal', $penambahan->tanggal) }}" required>
                            </div>
                        </div>

                        {{-- RKM --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">Nama RKM</label>
                            <div class="col-md-6">
                                <select name="id_rkm" class="form-select" required>
                                    <option value="">-- Pilih RKM --</option>
                                    @foreach($rkms as $rkm)
                                        <option value="{{ $rkm->id }}"
                                            {{ old('id_rkm', $penambahan->id_rkm) == $rkm->id ? 'selected' : '' }}>
                                            [{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M') }}] -
                                            {{ $rkm->materi->nama_materi ?? $rkm->nama_program ?? 'RKM #'.$rkm->id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Identitas Penerima --}}
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">Nama Penerima</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama" value="{{ old('nama', $penambahan->nama) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">Jabatan Penerima</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="jabatan" value="{{ old('jabatan', $penambahan->jabatan) }}" required>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-center mb-3 text-muted">Detail Barang</h6>

                        {{-- Item Souvenir (Single Item untuk Edit) --}}
                        <div class="p-3 border rounded bg-light">
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label">Souvenir</label>
                                <div class="col-md-8">
                                    <select name="id_souvenir" class="form-select souvenir-select" required>
                                        @foreach($souvenirs as $s)
                                            {{-- Hitung Stok Display: Stok Database + Stok qty saat ini (karena milik dia sendiri) --}}
                                            @php
                                                $isCurrentItem = $s->id == $penambahan->id_souvenir;
                                                $displayStok = $isCurrentItem ? ($s->stok + $penambahan->qty) : $s->stok;
                                            @endphp

                                            <option value="{{ $s->id }}"
                                                data-stok="{{ $displayStok }}"
                                                {{ old('id_souvenir', $penambahan->id_souvenir) == $s->id ? 'selected' : '' }}>
                                                {{ $s->nama_souvenir }} (Stok Tersedia: {{ $displayStok }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-success stok-info fw-bold">
                                        Maksimal Input: <span class="stok-val">-</span>
                                    </small>
                                </div>
                            </div>

                            <div class="row">
                                <label class="col-md-4 col-form-label">Jumlah (Qty)</label>
                                <div class="col-md-6">
                                    <input type="number" class="form-control item-qty" name="qty"
                                           value="{{ old('qty', $penambahan->qty) }}" required min="1">
                                    <small class="text-muted">Jika qty diubah, stok akan otomatis disesuaikan.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Simpan Perubahan
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
    $(document).ready(function() {
        // Trigger saat halaman dimuat untuk set max value awal
        updateStockInfo($('.souvenir-select'));

        // Trigger saat dropdown berubah
        $('.souvenir-select').change(function() {
            updateStockInfo($(this));
        });

        // Validasi input manual
        $('.item-qty').on('input', function() {
            var max = parseInt($(this).attr('max'));
            var val = parseInt($(this).val());
            if (val > max) {
                alert('Jumlah melebihi stok tersedia (' + max + ')');
                $(this).val(max);
            }
        });

        function updateStockInfo(element) {
            var selected = element.find('option:selected');
            var stok = selected.data('stok');

            $('.stok-val').text(stok);
            $('.item-qty').attr('max', stok);
        }
    });
</script>
@endsection
