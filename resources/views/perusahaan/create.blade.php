@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="/perusahaan" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Perusahaan Baru') }}</h5>
                    <form method="POST" action="{{ route('perusahaan.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_perusahaan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Perusahaan') }}</label>
                            <div class="col-md-6">
                                <input id="nama_perusahaan" type="text" placeholder="Masukan Nama Perusahaan" class="form-control @error('nama_perusahaan') is-invalid @enderror" name="nama_perusahaan" value="{{ old('nama_perusahaan') }}" autocomplete="nama_perusahaan" autofocus>
                                @error('nama_perusahaan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kategori_perusahaan" class="col-md-4 col-form-label text-md-start">{{ __('Kategori Perusahaan') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('kategori_perusahaan') is-invalid @enderror" name="kategori_perusahaan" autocomplete="kategori_perusahaan  ">
                                    <option value="" selected>Pilih Kategori Perusahaan</option>
                                    <option value="Pemerintahan Daerah" >Pemerintahan Daerah</option>
                                    <option value="Kementerian" >Kementerian</option>
                                    <option value="Lembaga Pemerintahan" >Lembaga Pemerintahan</option>
                                    <option value="BUMN" >BUMN</option>
                                    <option value="BUMD" >BUMD</option>
                                    <option value="Swasta" >Swasta</option>
                                    <option value="Akademik" >Akademik</option>
                                    <option value="Bank Daerah" >Bank Daerah</option>
                                    <option value="Bank Umum" >Bank Umum</option>
                                    <option value="Bank BUMN" >Bank BUMN</option>
                                    <option value="Rumah Sakit" >Rumah Sakit</option>
                                    <option value="Personal" >Personal</option>
                                </select>
                                @error('kategori_perusahaan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="lokasi" class="col-md-4 col-form-label text-md-start">{{ __('Wilayah') }}</label>
                            <div class="col-md-6">
                                <select id="lokasi" class="form-select @error('lokasi') is-invalid @enderror" name="lokasi"  autocomplete="lokasi">
                                    <option value="" selected>Pilih Wilayah</option>
                                    <option value="Nanggroe Aceh Darussalam" >Nanggroe Aceh Darussalam</option>
                                    <option value="Sumatera Utara" >Sumatera Utara</option>
                                    <option value="Sumatera Selatan" >Sumatera Selatan</option>
                                    <option value="Sumatera Barat" >Sumatera Barat</option>
                                    <option value="Bengkulu" >Bengkulu</option>
                                    <option value="Riau" >Riau</option>
                                    <option value="Kepulauan Riau" >Kepulauan Riau</option>
                                    <option value="Jambi" >Jambi</option>
                                    <option value="Lampung" >Lampung</option>
                                    <option value="Bangka Belitung" >Bangka Belitung</option>
                                    <option value="Kalimantan Barat" >Kalimantan Barat</option>
                                    <option value="Kalimantan Timur" >Kalimantan Timur</option>
                                    <option value="Kalimantan Selatan" >Kalimantan Selatan</option>
                                    <option value="Kalimantan Tengah" >Kalimantan Tengah</option>
                                    <option value="Kalimantan Utara" >Kalimantan Utara</option>
                                    <option value="Banten" >Banten</option>
                                    <option value="DKI Jakarta" >DKI Jakarta</option>
                                    <option value="Jawa Barat" >Jawa Barat</option>
                                    <option value="Jawa Tengah" >Jawa Tengah</option>
                                    <option value="Daerah Istimewa Yogyakarta" >Daerah Istimewa Yogyakarta</option>
                                    <option value="Jawa Timur" >Jawa Timur</option>
                                    <option value="Bali" >Bali</option>
                                    <option value="Nusa Tenggara Timur" >Nusa Tenggara Timur</option>
                                    <option value="Nusa Tenggara Barat" >Nusa Tenggara Barat</option>
                                    <option value="Gorontalo" >Gorontalo</option>
                                    <option value="Sulawesi Barat" >Sulawesi Barat</option>
                                    <option value="Sulawesi Tengah" >Sulawesi Tengah</option>
                                    <option value="Sulawesi Utara" >Sulawesi Utara</option>
                                    <option value="Sulawesi Tenggara" >Sulawesi Tenggara</option>
                                    <option value="Sulawesi Selatan" >Sulawesi Selatan</option>
                                    <option value="Maluku Utara" >Maluku Utara</option>
                                    <option value="Maluku" >Maluku</option>
                                    <option value="Papua Barat" >Papua Barat</option>
                                    <option value="Papua" >Papua</option>
                                    <option value="Papua Tengah" >Papua Tengah</option>
                                    <option value="Papua Pegunungan" >Papua Pegunungan</option>
                                    <option value="Papua Selatan" >Papua Selatan</option>
                                    <option value="Papua Barat Daya" >Papua Barat Daya</option>
                                </select>
                                @error('lokasi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="sales_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama Sales') }}</label>
                            <div class="col-md-6">
                                @if (auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'Adm Sales' )
                                <select class="form-select @error('sales_key') is-invalid @enderror" name="sales_key"  autocomplete="sales_key  ">
                                    <option value="">Pilih Sales</option>
                                    @foreach ($sales as $salesis)
                                       <option value="{{ $salesis->kode_karyawan }}">{{ $salesis->kode_karyawan }} - {{ $salesis->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                                @else
                                <select disabled class="form-select @error('sales_key') is-invalid @enderror" name="sales_key"  autocomplete="sales_key">
                                    <option value="{{ auth()->user()->id_sales }}" >{{ auth()->user()->id_sales }}</option>
                                </select>
                                <input type="hidden" name="sales_key" value="{{auth()->user()->id_sales}}"/>
                                @endif
                                @error('sales_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Status') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('status') is-invalid @enderror" name="status"  autocomplete="status">
                                    <option value="" selected>Pilih Status</option>
                                    <option value="Q1" >Q1</option>
                                    <option value="Q2" >Q2</option>
                                    <option value="Q3" >Q3</option>
                                    <option value="Q4" >Q4</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="npwp" class="col-md-4 col-form-label text-md-start">{{ __('NPWP') }}</label>
                            <div class="col-md-6">
                                <input id="npwp" type="text" placeholder="Masukan NPWP" class="form-control @error('npwp') is-invalid @enderror" name="npwp" value="{{ old('npwp') }}" autocomplete="npwp" autofocus>
                                @error('npwp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="alamat" class="col-md-4 col-form-label text-md-start">{{ __('Alamat') }}</label>
                            <div class="col-md-6">
                                <input id="alamat" type="text" placeholder="Masukan Alamat Perusahaan" class="form-control @error('alamat') is-invalid @enderror" name="alamat" value="{{ old('alamat') }}" autocomplete="alamat" autofocus>
                                @error('alamat')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="cp" class="col-md-4 col-form-label text-md-start">{{ __('CP') }}</label>
                            <div class="col-md-6">
                                <input id="cp" type="text" placeholder="Masukan CP Perusahaan" class="form-control @error('cp') is-invalid @enderror" name="cp" value="{{ old('cp') }}" autocomplete="cp" autofocus>
                                @error('cp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="no_telp" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Telepon') }}</label>
                            <div class="col-md-6">
                                <input id="no_telp" type="text" placeholder="Masukan Nomor Telepon Perusahaan" class="form-control @error('no_telp') is-invalid @enderror" name="no_telp" value="{{ old('no_telp') }}" autocomplete="no_telp" autofocus>
                                @error('no_telp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="foto_npwp" class="col-md-4 col-form-label text-md-start">{{ __('Foto NPWP') }}</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control @error('foto_npwp') is-invalid @enderror" name="foto_npwp">
                                @error('foto_npwp')
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
<style>

</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#lokasi').select2();
</script>
@endsection
