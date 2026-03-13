@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="/pengajuanbarang" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Pengajuan Barang') }}</h5>
                    <form method="POST" action="{{ route('pengajuanbarang.store') }}" enctype="multipart/form-data">
                        @csrf
                        <!-- ID Karyawan -->
                        <div class="row mb-3">
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

                        <div class="row mb-3">
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

                        <!-- Tipe -->
                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Tipe') }}</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select">
                                    <option value="-">Pilih Jenis Barang</option>
                                    <option value="ATK">ATK</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Makanan">Makanan</option>
                                    <option value="Souvenir">Souvenir</option>
                                    <option value="Operasional">Operasional</option>
                                    <option value="Reimbursement">Reimbursement</option>
                                    <option value="Training & Sertifikasi">Training & Sertifikasi</option>
                                </select>
                                @error('tipe')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div id="itemContainer">  
                            <div class="item mb-3">   
                                <div class="row">  
                                    <label for="barang[nama_barang][]" class="col-md-4 col-form-label text-md-start">Nama Barang</label>  
                                    <div class="col-md-6">  
                                        <input type="text" class="form-control" name="barang[nama_barang][]" required>  
                                    </div>  
                                </div>  
                                <div class="row">  
                                    <label for="barang[qty][]" class="col-md-4 col-form-label text-md-start">Jumlah</label>  
                                    <div class="col-md-6">  
                                        <input type="number" class="form-control" name="barang[qty][]" required>  
                                    </div>  
                                </div>  
                                <div class="row">  
                                    <label for="barang[harga_barang][]" class="col-md-4 col-form-label text-md-start">Harga Barang (dalam Rp.)</label>  
                                    <div class="col-md-6">  
                                        <div class="input-group mb-3">  
                                            <span class="input-group-text">Rp.</span>  
                                            <input type="text" class="form-control" name="barang[harga_barang][]" required>  
                                        </div>  
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

                        <div class="row mb-3">
                            <div class="col-md-4"></div>
                            <div class="col-md-6">
                                <button type="button" id="addItem" class="btn btn-primary">Tambah Item</button>  
                            </div>     
                        </div>

                        

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Simpan') }}
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
        setupInputFormatter('#itemContainer input[name="barang[harga_barang][]"]');  
        $('form').on('submit', function (e) {  
            // Prevent the default form submission  
            e.preventDefault();  
    
            // Remove periods from harga_barang inputs  
            $('#itemContainer input[name="barang[harga_barang][]"]').each(function() {  
                $(this).val($(this).val().replace(/\./g, ''));  
            });  
    
            // Now submit the form  
            this.submit(); // Use 'this' to refer to the form element  
        });  
        $('#addItem').click(function() {  
            addItem();
        });
        $(document).on('click', '.removeItemButton', function() {
            $(this).closest('.item').remove(); // Remove the closest item div
        });
    });
    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    function setupInputFormatter(selector) {
            var $input = $(selector);
            $input.on('input', function() {
                $input.val(formatRupiah(this.value));
            });
        }

         let itemIndex = 1;  
  
    function addItem() {  
        const newItem = `  
            <div class="row">  
                    <label for="barang[nama_barang][]" class="col-md-4 col-form-label text-md-start">Nama Barang</label>  
                    <div class="col-md-6">  
                        <input type="text" class="form-control" name="barang[nama_barang][]" required>  
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger removeItemButton">Hapus</button>
                    </div>
                </div>
                <div class="row">  
                    <label for="barang[qty][]" class="col-md-4 col-form-label text-md-start">Qty</label>  
                    <div class="col-md-6">  
                        <input type="number" class="form-control" name="barang[qty][]" required>  
                    </div>
                </div>
                <div class="row">  
                    <label for="barang[harga][]" class="col-md-4 col-form-label text-md-start">Besarnya (Rp.)</label>  
                    <div class="col-md-6">  
                        <div class="input-group mb-3">  
                            <span class="input-group-text">Rp.</span>  
                            <input type="text" class="form-control" name="barang[harga_barang][]" required>  
                        </div>  
                    </div>  
                </div>
                <div class="row">  
                    <label for="barang[keterangan][]" class="col-md-4 col-form-label text-md-start">Keterangan</label>  
                    <div class="col-md-6">  
                        <textarea class="form-control" name="barang[keterangan][]" required></textarea>  
                    </div>
                </div>
                <br>
        `;  
        $('#itemContainer').append(newItem);  
        $('#itemContainer').find('input[name="barang[harga_barang][]"]').last().on('input', function() {    
            $(this).val(formatRupiah(this.value));    
        });

        itemIndex++;  
    }  


</script>

@endsection
