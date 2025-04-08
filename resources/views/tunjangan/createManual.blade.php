@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Tunjangan') }}</h5>
                    <form method="POST" action="{{ route('tunjangan.storekelompok') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="karyawan_id" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <select id="karyawan_id" class="form-select @error('karyawan_id') is-invalid @enderror" name="karyawan_id[]" required autocomplete="karyawan_id" autofocus multiple>
                                    @foreach ($karyawan as $item)
                                        <option value="{{$item->id}}">{{$item->nama_lengkap}}</option>
                                    @endforeach
                                </select>
                                @error('karyawan_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="id_tunjangan" class="col-md-4 col-form-label text-md-start">{{ __('Tunjangan') }}</label>
                            <div class="col-md-6">
                                <select id="id_tunjangan" class="form-select @error('id_tunjangan') is-invalid @enderror" name="id_tunjangan" required autocomplete="id_tunjangan" autofocus>
                                    <option value="" selected>Pilih Jenis</option>
                                    @foreach ($tunjangan as $item)
                                        <option value="{{$item->id}}" data-nilai="{{$item->nilai}}">{{$item->nama_tunjangan}}</option>
                                    @endforeach
                                </select>
                                @error('id_tunjangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="nilai" class="col-md-4 col-form-label text-md-start">{{ __('Nilai') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('min_harga_pelatihan') is-invalid @enderror" name="nilai" id="nilai" placeholder="Nilai Tunjangan" required>
                                </div>
                                @error('nilai')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>  
                        <div class="row mb-3">
                            <label for="kelipatan" class="col-md-4 col-form-label text-md-start">{{ __('Kelipatan') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="kelipatan" id="kelipatan" class="form-control">
                                @error('kelipatan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>                             
                        <div class="row mb-3">
                            <label for="hitung" class="col-md-4 col-form-label text-md-start">{{ __('Penghitungan') }}</label>
                            <div class="col-md-6">
                                <select id="hitung" class="form-select @error('hitung') is-invalid @enderror" name="hitung" required autocomplete="hitung" autofocus>
                                    <option value="" selected>Pilih Hitungan</option>
                                    <option value="Perhari">Perhari</option>
                                    <option value="Perbulan">Perbulan</option>
                                </select>
                                @error('hitung')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function () {
        $('#karyawan_id').select2();
        $('#kelipatan').closest('.row').css('display', 'none');
        $('#nilai').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        $('#id_tunjangan').change(function() {
            console.log("Selected value: " + $(this).val()); // Debugging line
            if ($(this).val() == 'BPJS Keluarga') {
                // Sembunyikan input kelipatan dengan display: none
                $('#kelipatan').closest('.row').css('display', 'none');  // Menyembunyikan seluruh row
                $('#kelipatanInput').prop('disabled', true);  // Menonaktifkan input
            } else {
                // Tampilkan input kelipatan dengan display: block
                $('#kelipatan').closest('.row').css('display', 'flex');  // Menampilkan seluruh row
                $('#kelipatanInput').prop('disabled', false);  // Mengaktifkan input
            }
            // Ambil nilai dari data-nilai atribut yang sesuai
            var nilaiTunjangan = $(this).find('option:selected').data('nilai');
            
            // Set nilai ke input nilai
            $('#nilai').val(formatRupiah(nilaiTunjangan));
        });
        // Intercept form submission
        $('form').on('submit', function (e) {
            // Get the nilai input element
            let nilaiInput = $('#nilai');
            
            // Remove Rupiah format and set the cleaned value back to the input
            let cleanedValue = removeRupiahFormat(nilaiInput.val());
            nilaiInput.val(cleanedValue);
        });
    });

    // Function to format value to Rupiah format
    // function formatRupiah(angka, prefix) {
    //     var split = angka.split(','); // Pisahkan bagian desimal
    //     var sisa = split[0].length % 3; // Hitung sisa untuk pemisah ribuan
    //     var rupiah = split[0].substr(0, sisa); // Bagian awal (sebelum koma)
    //     var ribuan = split[0].substr(sisa).match(/\d{3}/gi); // Ambil bagian ribuan setelah koma

    //     if (ribuan) {
    //         separator = sisa ? '.' : ''; // Tambahkan pemisah ribuan
    //         rupiah += separator + ribuan.join('.'); // Gabungkan angka dengan pemisah ribuan
    //     }

    //     // Gabungkan bagian desimal (jika ada)
    //     if (split[1] != undefined) {
    //         rupiah += ',' + split[1]; // Menambahkan koma dan bagian desimal
    //     }

    //     return prefix === undefined ? rupiah : (rupiah ? rupiah : '');
    // }
    function formatRupiah(angka, prefix) {
        // Mengecek apakah angka negatif
        var isNegative = angka < 0;

        // Mengubah angka menjadi string dan menghilangkan karakter selain angka dan koma
        var number_string = Math.abs(angka).toString(); // Menggunakan Math.abs untuk menghindari angka negatif pada proses formatting

        // Memisahkan bagian bulat dan desimal
        var split = number_string.split('.');
        var bulat = split[0];
        var desimal = split[1] || ''; // Jika tidak ada bagian desimal, isi dengan string kosong

        // Memformat bagian bulat
        var sisa = bulat.length % 3;
        var rupiah = bulat.substr(0, sisa);
        var ribuan = bulat.substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        // Memformat bagian desimal (maksimal 2 angka di belakang koma)
        desimal = desimal.substr(0, 2);
        rupiah = rupiah + (desimal ? ',' + desimal : '');

        // Tambahkan prefix jika diperlukan
        rupiah = prefix ? prefix + ' ' + rupiah : rupiah;

        // Menambahkan tanda minus (-) jika angka negatif
        if (isNegative) {
            rupiah = '-' + rupiah;
        }

        return rupiah;
    }

    // Function to remove Rupiah formatting before submitting
    function removeRupiahFormat(angka) {
        return parseFloat(angka.replace(/[^\d,]/g, '').replace(',', '.'));
    }
</script>

@endsection
