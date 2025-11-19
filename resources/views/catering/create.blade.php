@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-single {
        padding: 14px;
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ route('catering.index') }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Pengajuan Catering') }}</h5>
                    <form id="cateringForm" method="POST" action="{{ route('catering.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-2">
                            <label for="id_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">
                                <input disabled id="nama_karyawan" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('nama_karyawan') is-invalid @enderror" name="nama_karyawan" value="{{ $karyawan->nama_lengkap }}" autocomplete="nama_karyawan" autofocus>
                                @error('id_karyawan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input disabled id="divisi" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                                @error('divisi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Tipe') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="tipe" value="Makanan" class="form-control" readonly>
                                @error('tipe')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div id="itemContainer">
                            <div class="item mb-4 p-3 border rounded">

                                <div class="item mb-2">
                                    <div class="row mb-2">
                                        <label for="barang[nama_makanan][]" class="col-md-4 col-form-label text-md-start">Nama Pengajuan</label>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="barang[nama_makanan][]" required>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label for="barang[qty][]" class="col-md-4 col-form-label text-md-start">Jumlah</label>
                                        <div class="col-md-6">
                                            <input type="number" class="form-control" name="barang[qty][]" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label for="barang[harga][]" class="col-md-4 col-form-label text-md-start">Harga Pengajuan (Rp.)</label>
                                        <div class="col-md-6">
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">Rp.</span>
                                                <input type="text" class="form-control" name="barang[harga][]" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <label for="barang[tipe_detail][]" class="col-md-4 col-form-label text-md-start">Tipe Catering</label>
                                        <div class="col-md-6">
                                            <select name="barang[tipe_detail][]" class="form-control tipe-detail-select" required>
                                                <option selected disabled> Pilih Tipe</option>
                                                <option value="Coffee Break">Coffee Break</option>
                                                <option value="Makan Siang">Makan Siang</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <label for="barang[vendor][]" class="col-md-4 col-form-label text-md-start">Vendor</label>
                                        <div class="col-md-6">
                                            <select name="barang[vendor][]" class="form-control vendor-select" required>
                                                <option selected disabled> Pilih vendor</option>
                                                @foreach ($vendorCB as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label for="barang[keterangan][]" class="col-md-4 col-form-label text-md-start">Keterangan (Optional)</label>
                                        <div class="col-md-6">
                                            <textarea class="form-control" name="barang[keterangan][]"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <button type="button" id="addItem" class="btn btn-primary">Tambah Item</button>
                            </div>
                        </div>

                        <div class="row mb-0 text-end">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Ajukan') }}
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        const vendorCB = @json($vendorCB);
        const vendorMS = @json($vendorMS);

        function applyRupiahFormatter() {
            $('input[name="barang[harga][]"]').each(function() {
                if (!$(this).data('formatter-applied')) {
                    $(this).data('formatter-applied', true);

                    $(this).on('input', function() {
                        let value = $(this).val().replace(/[^0-9]/g, '');
                        if (value) {
                            let formatted = 'Rp. ' + parseInt(value).toLocaleString('id-ID');
                            $(this).val(formatted);
                        } else {
                            $(this).val('');
                        }
                    });
                }
            });
        }

        applyRupiahFormatter();

        function cleanRupiahInputs() {
            $('input[name="barang[harga][]"]').each(function() {
                let value = $(this).val();
                let clean = value.replace(/Rp\.?\s?/g, '').replace(/\./g, '');
                $(this).val(clean || '0');
            });
        }

        $('#cateringForm').on('submit', function(e) {
            e.preventDefault();

            cleanRupiahInputs();

            this.submit();
        });

        // Fungsi untuk mengisi opsi vendor ke dalam select
        function populateVendorOptions($selectElement, vendorList) {
            $selectElement.empty();
            $selectElement.append('<option selected disabled> Pilih vendor</option>');
            vendorList.forEach(function(vendor) {
                $selectElement.append('<option value="' + vendor.id + '">' + vendor.nama + '</option>');
            });
        }

        // Event listener untuk perubahan pada select tipe_detail
        $(document).on('change', '.tipe-detail-select', function() {
            const $this = $(this);
            const selectedTipe = $this.val();
            const $vendorSelect = $this.closest('.item').find('.vendor-select');

            if (selectedTipe === 'Coffee Break') {
                populateVendorOptions($vendorSelect, vendorCB);
            } else if (selectedTipe === 'Makan Siang') {
                populateVendorOptions($vendorSelect, vendorMS);
            }
        });

        $('#addItem').click(function() {
            const tipe = $('#tipe').val() || 'Pengajuan';

            const newItem = `
            <div class="item mb-4 p-3 border rounded">

                <div class="row mb-2">
                    <label class="col-md-4 col-form-label">Nama ${tipe}</label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="barang[nama_makanan][]" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm removeItemButton">Hapus</button>
                    </div>
                </div>

                <div class="row mb-2">
                    <label class="col-md-4 col-form-label">Jumlah</label>
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="barang[qty][]" min="1" required>
                    </div>
                </div>

                <div class="row mb-2">
                    <label class="col-md-4 col-form-label">Harga ${tipe} (Rp.)</label>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">Rp.</span>
                            <input type="text" class="form-control" name="barang[harga][]" placeholder="0" required>
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="barang[tipe_detail][]" class="col-md-4 col-form-label text-md-start">Tipe Catering</label>
                    <div class="col-md-6">
                        <select name="barang[tipe_detail][]" class="form-control tipe-detail-select" required>
                            <option selected disabled> Pilih Tipe</option>
                            <option value="Coffee Break">Coffee Break</option>
                            <option value="Makan Siang">Makan Siang</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <label for="barang[vendor][]" class="col-md-4 col-form-label text-md-start">Vendor</label>
                    <div class="col-md-6">
                        <select name="barang[vendor][]" class="form-control vendor-select" required>
                            <option selected disabled> Pilih vendor</option>
                            @foreach ($vendorCB as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <label class="col-md-4 col-form-label">Keterangan (Opsional)</label>
                    <div class="col-md-6">
                        <textarea class="form-control" name="barang[keterangan][]" rows="2"></textarea>
                    </div>
                </div>
                <hr>
            </div>`;

            $('#itemContainer').append(newItem);

            applyRupiahFormatter();
            // $('.select2-single').select2({
            //     placeholder: "Pilih vendor",
            //     allowClear: true,
            //     width: '100%'
            // });
        });

        $(document).on('click', '.removeItemButton', function() {
            $(this).closest('.item').remove();
        });

        $('#tipe').on('change', function() {
            const tipe = $(this).val();
            $('.item').each(function() {
                $(this).find('label').each(function() {
                    let text = $(this).text();
                    if (text.includes('Nama ') || text.includes('Harga ')) {
                        if (text.includes('Nama ')) {
                            $(this).text('Nama ' + tipe);
                        }
                        if (text.includes('Harga ')) {
                            $(this).text('Harga ' + tipe + ' (Rp.)');
                        }
                    }
                });
            });
        });
    });
</script>
@endsection