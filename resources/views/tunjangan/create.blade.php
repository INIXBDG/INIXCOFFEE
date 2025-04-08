@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Tunjangan') }}</h5>
                    <form method="POST" action="{{ route('tunjangan.store') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_tunjangan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Tunjangan') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nama_tunjangan" placeholder="Masukan Nama Tunjangan" required>
                                @error('nama_tunjangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> 
                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Tunjangan') }}</label>
                            <div class="col-md-6">
                                <select id="tipe" class="form-select @error('tipe') is-invalid @enderror" name="tipe" required autocomplete="tipe" autofocus>
                                    <option value="" selected>Pilih Jenis</option>
                                    <option value="potongan" >Potongan</option>
                                    <option value="tunjangan" >Tunjangan</option>
                                </select>
                                @error('tipe')
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
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <select id="divisi" class="form-select @error('divisi') is-invalid @enderror" name="divisi" required autocomplete="divisi" autofocus>
                                    <option value="" selected>Pilih Divisi</option>
                                    <option value="All">All</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Office">Office</option>
                                    <option value="Education">Education</option>
                                </select>
                                @error('divisi')
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
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                        <h5 class="card-title text-center mb-4">{{ __('Education') }}</h5>
                            {{-- <a href="{{ route('createManualEducation') }}" class="btn btn-md click-primary my-4" id=""><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Manual Tunjangan Education</a> --}}
                            <table class="table table-border" id="table_tunjangan_education">
                                <thead>
                                    <th>Tunjangan</th>
                                    <th>Jumlah</th>
                                    {{-- <th>Keterangan</th> --}}
                                    <th>Aksi</th>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            {{-- <a href="{{ route('createManualOffice') }}" class="btn btn-md click-primary my-4" id=""><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Manual Tunjangan Office</a> --}}
                        <h5 class="card-title text-center mb-4">{{ __('Office') }}</h5>
                            <table class="table table-border" id="table_tunjangan_office">
                                <thead>
                                    <th>Tunjangan</th>
                                    <th>Jumlah</th>
                                    {{-- <th>Keterangan</th> --}}
                                    <th>Aksi</th>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            {{-- <a href="{{ route('createManualSales') }}" class="btn btn-md click-primary my-4" id=""><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Manual Tunjangan Sales</a> --}}
                        <h5 class="card-title text-center mb-4">{{ __('Sales') }}</h5>
                            <table class="table table-border" id="table_tunjangan_sales">
                                <thead>
                                    <th>Tunjangan</th>
                                    <th>Jumlah</th>
                                    <th>Aksi</th>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function () {
        fetchTunjanganEdu();
        fetchTunjanganSales();
        fetchTunjanganOffice();
        // Format the target input as the user types
        $('#nilai').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
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
    function fetchTunjanganEdu() {
        $('#table_tunjangan_education').DataTable({
            "ajax": {
                "url": "{{ route('getTunjanganEducation') }}", // URL to fetch Education data
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                }
            },
            "columns": [
                { "data": "nama_tunjangan" },
                {
                    "data": "nilai",
                    "render": function (data, type, row) {
                        return 'Rp. ' + formatRupiah(data.toString()); // Format data as Rupiah
                    }
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return `
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" style="color:white;" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="{{ url('tunjangan/${row.id}/edit') }}" data-toggle="tooltip" data-placement="top" title="Edit">
                                        <img src="{{ asset('icon/edit-warning.svg') }}" alt="Edit"> Edit
                                    </a>
                                </div>
                            </div>
                        `;
                    },
                },
            ],
            "createdRow": function (row, data, dataIndex) {
                var tipe = data.tipe;
                if (tipe == 'Potongan') {
                    $(row).css('background-color', '#FF2929');
                    $(row).css('color', 'white'); // Optional: Ubah warna teks menjadi putih
                } else {
                    $(row).css('background-color', '#006A67');
                    $(row).css('color', 'white'); // Optional: Ubah warna teks menjadi putih
                }
            }
        });
    }

    // Fetch and initialize DataTable for Office
    function fetchTunjanganOffice() {
        $('#table_tunjangan_office').DataTable({
            "ajax": {
                "url": "{{ route('getTunjanganOffice') }}", // URL to fetch Office data
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                }
            },
            "columns": [
                { "data": "nama_tunjangan" },
                {
                    "data": "nilai",
                    "render": function (data, type, row) {
                        return 'Rp. ' + formatRupiah(data.toString());
                    }
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return `
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" style="color:white;" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="{{ url('tunjangan/${row.id}/edit') }}" data-toggle="tooltip" data-placement="top" title="Edit">
                                        <img src="{{ asset('icon/edit-warning.svg') }}" alt="Edit"> Edit
                                    </a>
                                </div>
                            </div>
                        `;
                    }
                }
            ],
            "createdRow": function (row, data, dataIndex) {
                // Logika warna latar belakang berdasarkan status_pembayaran dan tanggal_bayar
                var tipe = data.tipe;
                if (tipe == 'Potongan') {
                    $(row).css('background-color', '#FF2929');
                    $(row).css('color', 'white'); // Optional: Ubah warna teks menjadi putih
                }else{
                    $(row).css('background-color', '#006A67');
                    $(row).css('color', 'white'); // Optional: Ubah warna teks menjadi putih
                }
            }
        });
    }
    // Fetch and initialize DataTable for Sales
    function fetchTunjanganSales() {
        $('#table_tunjangan_sales').DataTable({
            "ajax": {
                "url": "{{ route('getTunjanganSales') }}", // URL to fetch Sales data
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                    }, 1000);
                }
            },
            "columns": [
                { "data": "nama_tunjangan" },
                {
                    "data": "nilai",
                    "render": function (data, type, row) {
                        return 'Rp. ' + formatRupiah(data.toString());
                    }
                },
                {
                    "data": null,
                    "render": function (data, type, row) {
                        return `
                            <div class="dropdown">
                                <button class="btn dropdown-toggle" style="color:white;" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="{{ url('tunjangan/${row.id}/edit') }}" data-toggle="tooltip" data-placement="top" title="Edit">
                                        <img src="{{ asset('icon/edit-warning.svg') }}" alt="Edit"> Edit
                                    </a>
                                </div>
                            </div>
                        `;
                    }
                }
            ],
            "createdRow": function (row, data, dataIndex) {
                // Logika warna latar belakang berdasarkan status_pembayaran dan tanggal_bayar
                var tipe = data.tipe;
                if (tipe == 'Potongan') {
                    $(row).css('background-color', '#FF2929');
                    $(row).css('color', 'white'); // Optional: Ubah warna teks menjadi putih
                }else{
                    $(row).css('background-color', '#006A67');
                    $(row).css('color', 'white'); // Optional: Ubah warna teks menjadi putih
                }
            }
        });
    }
    // Function to format value to Rupiah format
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
