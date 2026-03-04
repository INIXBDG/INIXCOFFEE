@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Kembali</a>
                <h5 class="card-title text-center mb-4">{{ __('Pengajuan Souvenir') }}</h5>

                {{-- Menampilkan error validasi (terutama untuk array) --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('pengajuansouvenir.store') }}">
                    @csrf
                    <div class="row mb-3">
                        <label for="id_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                        <div class="col-md-6">
                            <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">
                            <input disabled id="nama_karyawan" type="text" class="form-control" value="{{ $karyawan->nama_lengkap }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                        <div class="col-md-6">
                            <input disabled id="divisi" type="text" class="form-control" value="{{ $karyawan->divisi }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="id_vendor" class="col-md-4 col-form-label text-md-start">{{ __('Vendor') }}</label>
                        <div class="col-md-6">
                            <select name="id_vendor" id="id_vendor" class="form-select @error('id_vendor') is-invalid @enderror" required>
                                <option value="" disabled selected>Pilih Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('id_vendor') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_vendor')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-center">Detail Item Souvenir</h6>

                    <div id="itemContainer">
                        <div class="item mb-3 p-3 border rounded">
                            <div class="row">
                                <label class="col-md-4 col-form-label text-md-start">Souvenir</label>
                                <div class="col-md-6">
                                    <select name="souvenir[id][]" class="form-select souvenir-select" required>
                                        <option value="" disabled selected>Pilih Souvenir</option>
                                        @foreach($souvenirs as $souvenir)
                                            <option value="{{ $souvenir->id }}">
                                                {{ $souvenir->nama_souvenir }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <label class="col-md-4 col-form-label text-md-start">Jumlah (Pax)</label>
                                <div class="col-md-6">
                                    <input type="number" class="form-control item-pax" name="souvenir[pax][]" required min="1">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <label class="col-md-4 col-form-label text-md-start">Harga Satuan</label>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control item-harga-satuan" name="souvenir[harga_satuan][]" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4"></div>
                        <div class="col-md-6">
                            <button type="button" id="addItem" class="btn btn-primary">Tambah Item</button>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn click-primary">
                                {{ __('Simpan Pengajuan') }}
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
    $(document).ready(function () {

        // --- Fungsi Helper ---
        function formatRupiah(angka) {
            if(typeof angka === 'undefined' || angka === null) return '0';
            var number_string = angka.toString().replace(/[^,\d]/g, ''),
                sisa = number_string.length % 3,
                rupiah = number_string.substr(0, sisa),
                ribuan = number_string.substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah === '' ? '0' : rupiah;
        }

        function getNumericValue(value) {
            if(typeof value === 'undefined' || value === null) return 0;
            return parseInt(value.replace(/\./g, '')) || 0;
        }

        // --- Fungsi Utama ---
        function addItem() {
            const newItem = `
                <div class="item mb-3 p-3 border rounded">
                    <div class="row">
                        <label class="col-md-4 col-form-label text-md-start">Souvenir</label>
                        <div class="col-md-6">
                            <select name="souvenir[id][]" class="form-select souvenir-select" required>
                                <option value="" disabled selected>Pilih Souvenir</option>
                                @foreach($souvenirs as $souvenir)
                                    {{-- PERBAIKAN: atribut data-harga dihapus --}}
                                    <option value="{{ $souvenir->id }}">
                                        {{ $souvenir->nama_souvenir }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger removeItemButton">Hapus</button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <label class="col-md-4 col-form-label text-md-start">Jumlah (Pax)</label>
                        <div class="col-md-6">
                            <input type="number" class="form-control item-pax" name="souvenir[pax][]" required min="1">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <label class="col-md-4 col-form-label text-md-start">Harga Satuan</label>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" class="form-control item-harga-satuan" name="souvenir[harga_satuan][]" required>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#itemContainer').append(newItem);
        }

        // --- Event Listeners (Menggunakan Event Delegation) ---

        // Tombol Tambah Item
        $('#addItem').click(function() {
            addItem();
        });

        // Tombol Hapus Item
        $(document).on('click', '.removeItemButton', function() {
            $(this).closest('.item').remove();
        });

        // Format Rupiah saat diketik di Harga Satuan (Tetap digunakan)
        $(document).on('input', '.item-harga-satuan', function() {
            var selection = this.selectionStart;
            var originalLength = this.value.length;

            this.value = formatRupiah(this.value);

            var newLength = this.value.length;
            selection = selection + (newLength - originalLength);
            this.setSelectionRange(selection, selection);
        });

        // Membersihkan format Rupiah sebelum form disubmit
        $('form').on('submit', function (e) {
            e.preventDefault();

            // Loop setiap item dan bersihkan format harganya
            $('.item-harga-satuan').each(function() {
                $(this).val(getNumericValue($(this).val()));
            });

            this.submit(); // Lanjutkan submit
        });
    });
</script>

@endsection
