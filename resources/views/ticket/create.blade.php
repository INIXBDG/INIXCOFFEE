@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Ticketing') }}</h5>
                    <form method="POST" action="{{ route('tickets.store') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                {{-- {{auth()->user()->karyawan->divisi}} --}}
                                @if (auth()->user()->karyawan->divisi == 'IT Service Management')
                                    <select name="nama_karyawan" id="nama_karyawan" class="form-select">
                                        <option value="" selected>Pilih Karyawan</option>
                                        @foreach ($karyawan as $item)
                                            <option value="{{ $item->nama_lengkap }}" data-divisi="{{ $item->divisi }}">{{ $item->nama_lengkap }}</option>
                                        @endforeach
                                    <option value="Tim Digital">Tim Digital</option>
                                    <option value="Programming">Programming</option>
                                    </select>
                                @else
                                    <input type="text" class="form-control" name="nama_karyawan" value="{{auth()->user()->karyawan->nama_lengkap}}" readonly required>
                                @endif
                                @error('nama_karyawan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> 
                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input type="text" id="divisi" class="form-control" name="divisi" value="{{auth()->user()->karyawan->divisi}}" readonly required>
                                @error('divisi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>    
                        <div class="row mb-3">
                            <label for="keperluan" class="col-md-4 col-form-label text-md-start">{{ __('Keperluan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="datetime" value="{{ now() }}">
                                <select id="keperluan" class="form-select @error('keperluan') is-invalid @enderror" name="keperluan" required autocomplete="keperluan" autofocus>
                                    <option value="" selected>Pilih Keperluan</option>
                                    <option value="Technical Support">Technical Support</option>
                                    <option value="Tim Digital">Tim Digital</option>
                                    <option value="Programming">Programming</option>
                                </select>
                                @error('keperluan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="kategori" class="col-md-4 col-form-label text-md-start">{{ __('Kategori') }}</label>
                            <div class="col-md-6">
                                <select id="kategori" class="form-select @error('kategori') is-invalid @enderror" name="kategori" required autocomplete="kategori" autofocus>
                                    <option value="" selected>Pilih Kategori</option>
                                    <!-- Opsi akan diisi oleh jQuery -->
                                </select>
                                @error('kategori')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="detail_kendala" class="col-md-4 col-form-label text-md-start">{{ __('Detail Kendala') }}</label>
                            <div class="col-md-6">
                                <textarea name="detail_kendala" class="form-control" id="detail_kendala" cols="50" rows="5"></textarea>
                                @error('detail_kendala')
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
<script>
$(document).ready(function() {
     // Simpan divisi default saat halaman dimuat
    const defaultDivisi = "{{ auth()->user()->karyawan->divisi }}";
    
    // Jika user adalah IT Service Management, tambahkan event listener
    @if (auth()->user()->karyawan->divisi == 'IT Service Management')
        $('#nama_karyawan').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const divisiValue = selectedOption.data('divisi') || defaultDivisi;
            
            $('#divisi').val(divisiValue);
        });
        
        // Trigger change event saat halaman dimuat jika ada nilai yang sudah dipilih
        if ($('#nama_karyawan').val()) {
            $('#nama_karyawan').trigger('change');
        }
    @endif
    // Definisikan opsi untuk setiap keperluan
    const kategoriOptions = {
        'Technical Support': [
            {value: 'Jaringan', text: 'Jaringan'},
            {value: 'Hardware', text: 'Hardware'},
            {value: 'Software', text: 'Software'},
            {value: 'Exam', text: 'Exam'}
        ],
        'Tim Digital': [
            {value: 'Flyer', text: 'Flyer'},
            {value: 'Banner Cetak', text: 'Banner Cetak'},
            {value: 'Konten (Video)', text: 'Konten (Video)'},
            {value: 'Kerja Sama Mitra', text: 'Kerja Sama Mitra'}            
        ],
        'Programming': [
            {value: 'Request', text: 'Request'},
            {value: 'Error (Aplikasi)', text: 'Error (Aplikasi)'}
        ]
    };
    
    // Fungsi untuk mengupdate opsi kategori
    function updateKategoriOptions() {
        const selectedKeperluan = $('#keperluan').val();
        
        // Kosongkan dropdown kategori dan tambahkan opsi default
        $('#kategori').html('<option value="" selected>Pilih Kategori</option>');
        
        // Jika keperluan dipilih, tambahkan opsi yang sesuai
        if (selectedKeperluan && kategoriOptions[selectedKeperluan]) {
            $.each(kategoriOptions[selectedKeperluan], function(index, option) {
                $('#kategori').append($('<option>', {
                    value: option.value,
                    text: option.text
                }));
            });
        }
    }
    
    // Jalankan pertama kali saat halaman dimuat
    updateKategoriOptions();
    
    // Tambahkan event listener untuk perubahan keperluan
    $('#keperluan').on('change', updateKategoriOptions);
});
</script>

@endsection
