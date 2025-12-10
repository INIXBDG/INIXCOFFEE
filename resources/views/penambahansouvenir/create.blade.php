@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('penambahansouvenir.index') }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Kembali
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Input Distribusi Souvenir') }}</h5>

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

                    <form method="POST" action="{{ route('penambahansouvenir.store') }}">
                        @csrf

                        {{-- === BAGIAN 1: IDENTITAS PENGINPUT (READ ONLY) === --}}
                        <div class="row mb-3 bg-light p-2 rounded mx-1">
                            <div class="col-12 text-center mb-2"><strong>Identitas Penginput (Karyawan)</strong></div>

                            <label class="col-md-4 col-form-label text-md-start">Nama Karyawan</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control" value="{{ $karyawan->nama ?? $karyawan->nama_lengkap }}" readonly disabled style="background-color: #e9ecef; font-weight:bold;">
                            </div>

                            <label class="col-md-4 col-form-label text-md-start mt-2">Divisi</label>
                            <div class="col-md-8 mt-2">
                                <input type="text" class="form-control" value="{{ $karyawan->divisi ?? '-' }}" readonly disabled style="background-color: #e9ecef;">
                            </div>
                        </div>

                        <hr>

                        {{-- === BAGIAN 2: DATA DISTRIBUSI === --}}
                        <div class="row mb-3">
                            <label for="tanggal" class="col-md-4 col-form-label text-md-start">Tanggal Distribusi</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">Nama RKM</label>
                            <div class="col-md-6">
                                <select name="id_rkm" class="form-select" required>
                                    <option value="">-- Pilih RKM --</option>
                                    @foreach($rkms as $rkm)
                                        <option value="{{ $rkm->id }}" {{ old('id_rkm') == $rkm->id ? 'selected' : '' }}>
                                            {{-- Menampilkan Tanggal Awal s/d Akhir dan Nama Materi --}}
                                            [{{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d M') }} - {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d M Y') }}] : {{ $rkm->materi->nama_materi ?? 'Tanpa Materi' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 0.75rem">Filter: 1 minggu lalu s/d 3 minggu ke depan.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nama" class="col-md-4 col-form-label text-md-start">Nama Penerima</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Tamu Undangan / Peserta">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="jabatan" class="col-md-4 col-form-label text-md-start">Jabatan Penerima</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="jabatan" value="{{ old('jabatan') }}" required placeholder="Contoh: Manager / Staff">
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-center mb-3">Item Souvenir</h6>

                        {{-- Container Item Dinamis --}}
                        <div id="itemContainer">
                            <div class="item mb-3 p-3 border rounded shadow-sm">
                                <div class="row">
                                    <label class="col-md-4 col-form-label text-md-start">Souvenir</label>
                                    <div class="col-md-6">
                                        <select name="souvenir_id[]" class="form-select souvenir-select" required>
                                            <option value="">-- Pilih Souvenir --</option>
                                            @foreach($souvenirs as $s)
                                                <option value="{{ $s->id }}" data-stok="{{ $s->stok }}">
                                                    {{ $s->nama_souvenir }} (Stok: {{ $s->stok }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-success stok-info" style="display:none; font-weight:bold">Sisa stok: <span class="stok-val">-</span></small>
                                    </div>
                                    <div class="col-md-2"></div>
                                </div>
                                <div class="row mt-2">
                                    <label class="col-md-4 col-form-label text-md-start">Qty</label>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control item-qty" name="qty[]" required min="1" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4"></div>
                            <div class="col-md-6">
                                <button type="button" id="addItem" class="btn btn-outline-primary btn-sm">
                                    + Tambah Item Lain
                                </button>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    Simpan Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Template Item Baru (Hidden) --}}
<div id="itemTemplate" style="display: none;">
    <div class="item mb-3 p-3 border rounded shadow-sm">
        <div class="row">
            <label class="col-md-4 col-form-label text-md-start">Souvenir</label>
            <div class="col-md-6">
                <select name="souvenir_id[]" class="form-select souvenir-select" required>
                    <option value="">-- Pilih Souvenir --</option>
                    @foreach($souvenirs as $s)
                        <option value="{{ $s->id }}" data-stok="{{ $s->stok }}">
                            {{ $s->nama_souvenir }} (Stok: {{ $s->stok }})
                        </option>
                    @endforeach
                </select>
                <small class="text-success stok-info" style="display:none; font-weight:bold">Sisa stok: <span class="stok-val">-</span></small>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm removeItemButton">Hapus</button>
            </div>
        </div>
        <div class="row mt-2">
            <label class="col-md-4 col-form-label text-md-start">Qty</label>
            <div class="col-md-6">
                <input type="number" class="form-control item-qty" name="qty[]" required min="1" placeholder="0">
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logika JS tetap sama
        $('#addItem').click(function() {
            var newItem = $('#itemTemplate').html();
            $('#itemContainer').append(newItem);
        });

        $(document).on('click', '.removeItemButton', function() {
            $(this).closest('.item').remove();
        });

        $(document).on('change', '.souvenir-select', function() {
            var $row = $(this).closest('.item');
            var selectedOption = $(this).find('option:selected');
            var maxStock = parseInt(selectedOption.data('stok')) || 0;
            var $qtyInput = $row.find('.item-qty');
            var $stokInfo = $row.find('.stok-info');
            var $stokVal = $row.find('.stok-val');

            if (this.value) {
                $stokVal.text(maxStock);
                $stokInfo.show();
                $qtyInput.attr('max', maxStock);
                if (parseInt($qtyInput.val()) > maxStock) {
                    alert('Stok maksimal hanya ' + maxStock);
                    $qtyInput.val(maxStock);
                }
            } else {
                $stokInfo.hide();
                $qtyInput.removeAttr('max');
            }
        });

        $(document).on('input', '.item-qty', function() {
            var $row = $(this).closest('.item');
            var maxStock = parseInt($row.find('.souvenir-select option:selected').data('stok')) || 0;
            var currentQty = parseInt($(this).val()) || 0;

            if (maxStock > 0 && currentQty > maxStock) {
                alert('Jumlah melebihi stok yang tersedia (' + maxStock + ')');
                $(this).val(maxStock);
            }
        });
    });
</script>
@endsection
