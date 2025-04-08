@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Edit Peusahaan') }}</h5>
                    <form method="POST" action="{{ route('perusahaan.update', $perusahaans->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama_perusahaan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Perusahaan') }}</label>
                            <div class="col-md-6">
                                <input id="nama_perusahaan" type="text" placeholder="Masukan Nama Perusaaan" class="form-control @error('nama_perusahaan') is-invalid @enderror" name="nama_perusahaan" value="{{ old('nama_perusahaan', $perusahaans->nama_perusahaan) }}" autocomplete="nama_perusahaan" autofocus>
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
                                <select class="form-select @error('kategori_perusahaan') is-invalid @enderror" name="kategori_perusahaan" required autocomplete="kategori_perusahaan  ">
                                    <option value="">Pilih Kategori Perusahaan</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Pemerintahan Daerah") selected @endif value="Pemerintahan Daerah" >Pemerintahan Daerah</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Kementerian") selected @endif value="Kementerian" >Kementerian</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Lembaga Pemerintahan") selected @endif value="Lembaga Pemerintahan" >Lembaga Pemerintahan</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "BUMN") selected @endif value="BUMN" >BUMN</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "BUMD") selected @endif value="BUMD" >BUMD</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Swasta") selected @endif value="Swasta" >Swasta</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Akademik") selected @endif value="Akademik" >Akademik</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Bank Daerah") selected @endif value="Bank Daerah" >Bank Daerah</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Bank Umum") selected @endif value="Bank Umum" >Bank Umum</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Bank BUMN") selected @endif value="Bank BUMN" >Bank BUMN</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Rumah Sakit") selected @endif value="Rumah Sakit" >Rumah Sakit</option>
                                    <option @if ($perusahaans->kategori_perusahaan == "Personal") selected @endif value="Personal" >Personal</option>
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
                                <select id="lokasi" class="form-select @error('lokasi') is-invalid @enderror" name="lokasi" required autocomplete="lokasi">
                                    <option value="" selected>Pilih Wilayah</option>
                                    <option @if ($perusahaans->lokasi == "Nanggroe Aceh Darussalam") selected @endif value="Nanggroe Aceh Darussalam" >Nanggroe Aceh Darussalam</option>
                                    <option @if ($perusahaans->lokasi == "Sumatera Utara") selected @endif value="Sumatera Utara" >Sumatera Utara</option>
                                    <option @if ($perusahaans->lokasi == "Sumatera Selatan") selected @endif value="Sumatera Selatan" >Sumatera Selatan</option>
                                    <option @if ($perusahaans->lokasi == "Sumatera Barat") selected @endif value="Sumatera Barat" >Sumatera Barat</option>
                                    <option @if ($perusahaans->lokasi == "Bengkulu") selected @endif value="Bengkulu" >Bengkulu</option>
                                    <option @if ($perusahaans->lokasi == "Riau") selected @endif value="Riau" >Riau</option>
                                    <option @if ($perusahaans->lokasi == "Kepulauan Riau") selected @endif value="Kepulauan Riau" >Kepulauan Riau</option>
                                    <option @if ($perusahaans->lokasi == "Jambi") selected @endif value="Jambi" >Jambi</option>
                                    <option @if ($perusahaans->lokasi == "Lampung") selected @endif value="Lampung" >Lampung</option>
                                    <option @if ($perusahaans->lokasi == "Bangka Belitung") selected @endif value="Bangka Belitung" >Bangka Belitung</option>
                                    <option @if ($perusahaans->lokasi == "Kalimantan Barat") selected @endif value="Kalimantan Barat" >Kalimantan Barat</option>
                                    <option @if ($perusahaans->lokasi == "Kalimantan Timur") selected @endif value="Kalimantan Timur" >Kalimantan Timur</option>
                                    <option @if ($perusahaans->lokasi == "Kalimantan Selatan") selected @endif value="Kalimantan Selatan" >Kalimantan Selatan</option>
                                    <option @if ($perusahaans->lokasi == "Kalimantan Tengah") selected @endif value="Kalimantan Tengah" >Kalimantan Tengah</option>
                                    <option @if ($perusahaans->lokasi == "Kalimantan Utara") selected @endif value="Kalimantan Utara" >Kalimantan Utara</option>
                                    <option @if ($perusahaans->lokasi == "Banten") selected @endif value="Banten" >Banten</option>
                                    <option @if ($perusahaans->lokasi == "DKI Jakarta") selected @endif value="DKI Jakarta" >DKI Jakarta</option>
                                    <option @if ($perusahaans->lokasi == "Jawa Barat") selected @endif value="Jawa Barat" >Jawa Barat</option>
                                    <option @if ($perusahaans->lokasi == "Jawa Tengah") selected @endif value="Jawa Tengah" >Jawa Tengah</option>
                                    <option @if ($perusahaans->lokasi == "Daerah Istimewa Yogyakarta") selected @endif value="Daerah Istimewa Yogyakarta" >Daerah Istimewa Yogyakarta</option>
                                    <option @if ($perusahaans->lokasi == "Jawa Timur") selected @endif value="Jawa Timur" >Jawa Timur</option>
                                    <option @if ($perusahaans->lokasi == "Bali") selected @endif value="Bali" >Bali</option>
                                    <option @if ($perusahaans->lokasi == "Nusa Tenggara Timur") selected @endif value="Nusa Tenggara Timur" >Nusa Tenggara Timur</option>
                                    <option @if ($perusahaans->lokasi == "Nusa Tenggara Barat") selected @endif value="Nusa Tenggara Barat" >Nusa Tenggara Barat</option>
                                    <option @if ($perusahaans->lokasi == "Gorontalo") selected @endif value="Gorontalo" >Gorontalo</option>
                                    <option @if ($perusahaans->lokasi == "Sulawesi Barat") selected @endif value="Sulawesi Barat" >Sulawesi Barat</option>
                                    <option @if ($perusahaans->lokasi == "Sulawesi Tengah") selected @endif value="Sulawesi Tengah" >Sulawesi Tengah</option>
                                    <option @if ($perusahaans->lokasi == "Sulawesi Utara") selected @endif value="Sulawesi Utara" >Sulawesi Utara</option>
                                    <option @if ($perusahaans->lokasi == "Sulawesi Tenggara") selected @endif value="Sulawesi Tenggara" >Sulawesi Tenggara</option>
                                    <option @if ($perusahaans->lokasi == "Sulawesi Selatan") selected @endif value="Sulawesi Selatan" >Sulawesi Selatan</option>
                                    <option @if ($perusahaans->lokasi == "Maluku Utara") selected @endif value="Maluku Utara" >Maluku Utara</option>
                                    <option @if ($perusahaans->lokasi == "Maluku") selected @endif value="Maluku" >Maluku</option>
                                    <option @if ($perusahaans->lokasi == "Papua Barat") selected @endif value="Papua Barat" >Papua Barat</option>
                                    <option @if ($perusahaans->lokasi == "Papua") selected @endif value="Papua" >Papua</option>
                                    <option @if ($perusahaans->lokasi == "Papua Tengah") selected @endif value="Papua Tengah" >Papua Tengah</option>
                                    <option @if ($perusahaans->lokasi == "Papua Pegunungan") selected @endif value="Papua Pegunungan" >Papua Pegunungan</option>
                                    <option @if ($perusahaans->lokasi == "Papua Selatan") selected @endif value="Papua Selatan" >Papua Selatan</option>
                                    <option @if ($perusahaans->lokasi == "Papua Barat Daya") selected @endif value="Papua Barat Daya" >Papua Barat Daya</option>
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
                                <select class="form-select @error('sales_key') is-invalid @enderror" name="sales_key" required autocomplete="sales_key  ">
                                    <option value="">Pilih Sales</option>
                                    @foreach ($sales as $salesis)
                                       <option value="{{ $salesis->kode_karyawan }}" @if ($perusahaans->sales_key == $salesis->kode_karyawan) selected @endif>{{ $salesis->kode_karyawan }} - {{ $salesis->nama_lengkap }}</option>
                                    @endforeach
                                </select>
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
                                <select class="form-select @error('status') is-invalid @enderror" name="status" required autocomplete="status">
                                    <option value="" selected>Pilih Status</option>
                                    <option @if ($perusahaans->status == "Q1") selected @endif value="Q1" >Q1</option>
                                    <option @if ($perusahaans->status == "Q2") selected @endif value="Q2" >Q2</option>
                                    <option @if ($perusahaans->status == "Q3") selected @endif value="Q3" >Q3</option>
                                    <option @if ($perusahaans->status == "Q4") selected @endif value="Q4" >Q4</option>
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
                                <input id="npwp" type="text" placeholder="Masukan NPWP Perusahaan" class="form-control @error('npwp') is-invalid @enderror" name="npwp" value="{{ old('npwp', $perusahaans->npwp) }}" autocomplete="npwp" autofocus>
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
                                <input id="alamat" type="text" placeholder="Masukan Alamat Perusahaan" class="form-control @error('alamat') is-invalid @enderror" name="alamat" value="{{ old('alamat', $perusahaans->alamat) }}" autocomplete="alamat" autofocus>
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
                                <input id="cp" type="text" placeholder="Masukan CP Perusahaan" class="form-control @error('cp') is-invalid @enderror" name="cp" value="{{ old('cp', $perusahaans->cp) }}" autocomplete="cp" autofocus>
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
                                <input id="no_telp" type="text" placeholder="Masukan Nomor Telepon Perusahaan" class="form-control @error('no_telp') is-invalid @enderror" name="no_telp" value="{{ old('no_telp', $perusahaans->no_telp) }}" autocomplete="no_telp" autofocus>
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
@endsection
