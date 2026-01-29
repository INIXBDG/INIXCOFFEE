@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Materi Baru') }}</h5>
                    <form method="POST" action="{{ route('materi.store') }}"  enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                            <div class="col-md-6">
                                <input id="nama_materi" type="text" placeholder="Masukan Nama Materi" class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi') }}" autocomplete="nama_materi" autofocus>
                                @error('nama_materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kategori_materi" class="col-md-4 col-form-label text-md-start">{{ __('Kategori Materi') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('kategori_materi') is-invalid @enderror" name="kategori_materi" value="{{ old('kategori_materi', ) }}" autocomplete="kategori_materi">
                                    <option selected>Pilih Kategori Materi</option>
                                    <option value="Management">Management</option>
                                    <option value="Security">Security</option>
                                    <option value="Data Analist">Data Analist</option>
                                    <option value="Data Engineer">Data Engineer</option>
                                    <option value="Cloud">Cloud</option>
                                    <option value="Data Center">Data Center</option>
                                    <option value="Networking">Networking</option>
                                    <option value="Server">Server</option>
                                    <option value="Virtualization">Virtualization</option>
                                    <option value="Hardware">Hardware</option>
                                    <option value="GIS">GIS</option>
                                    <option value="Multimedia">Multimedia</option>
                                    <option value="Programming">Programming</option>
                                    <option value="Software Engineer">Software Engineer</option>
                                    <option value="Office">Office</option>
                                    <option value="Non-IT">Non-IT</option>
                                    <option value="Artificial Intelligence">Artificial Intelligence</option>
                                </select>
                                @error('kategori_materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="durasi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi') }}</label>
                            <div class="col-md-6">
                                <input id="durasi" type="text" placeholder="Masukan Durasi Materi" class="form-control @error('durasi') is-invalid @enderror" name="durasi" autocomplete="durasi" autofocus>
                                @error('kode_materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="kode_materi" class="col-md-4 col-form-label text-md-start">{{ __('Kode Materi') }}</label>
                            <div class="col-md-6">
                                <input id="kode_materi" type="text" placeholder="Masukan Kode Materi" class="form-control @error('kode_materi') is-invalid @enderror" name="kode_materi" value="{{ old('kode_materi') }}" autocomplete="kode_materi" autofocus>
                                @error('kode_materi')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="vendor" class="col-md-4 col-form-label text-md-start">{{ __('Vendor') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('vendor') is-invalid @enderror" name="vendor" value="{{ old('vendor', ) }}" autocomplete="vendor">
                                    <option selected>Pilih Vendor</option>
                                    <option value="AWS">AWS</option>
                                    <option value="Cisco">Cisco</option>
                                    <option value="EC-Council">EC-Council</option>
                                    <option value="EPI">EPI</option>
                                    <option value="Google">Google</option>
                                    <option value="ISACA">ISACA</option>
                                    <option value="LSP">LSP</option>
                                    <option value="Microsoft">Microsoft</option>
                                    <option value="Mikrotik">Mikrotik</option>
                                    <option value="CompTIA">CompTIA</option>
                                    <option value="RedHat">RedHat</option>
                                    <option value="Regular">Regular</option>
                                </select>
                                @error('vendor')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="silabus" class="col-md-4 col-form-label text-md-start">{{ __('Silabus (PDF)') }}</label>
                            <div class="col-md-6">
                                <input type="file" name="silabus" class="form-control-file" accept="application/pdf">
                                @error('silabus')
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
