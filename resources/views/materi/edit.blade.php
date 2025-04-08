@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Edit Materi') }}</h5>
                    <form method="POST" action="{{ route('materi.update', $materis->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                            <div class="col-md-6">
                                <input id="nama_materi" type="text" placeholder="Masukan Nama Materi" class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi', $materis->nama_materi) }}" autocomplete="nama_materi" autofocus>
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
                                <select class="form-select @error('kategori_materi') is-invalid @enderror" name="kategori_materi" value="{{ old('kategori_materi', ) }}" required autocomplete="kategori_materi">
                                    <option selected>Pilih Kategori Materi</option>
                                    <option @if ($materis->kategori_materi == "Management") selected @endif value="Management">Management</option>
                                    <option @if ($materis->kategori_materi == "Security") selected @endif value="Security">Security</option>
                                    <option @if ($materis->kategori_materi == "Data Analist") selected @endif value="Data Analist">Data Analist</option>
                                    <option @if ($materis->kategori_materi == "Data Engineer") selected @endif value="Data Engineer">Data Engineer</option>
                                    <option @if ($materis->kategori_materi == "Cloud") selected @endif value="Cloud">Cloud</option>
                                    <option @if ($materis->kategori_materi == "Data Center") selected @endif value="Data Center">Data Center</option>
                                    <option @if ($materis->kategori_materi == "Networking") selected @endif value="Networking">Networking</option>
                                    <option @if ($materis->kategori_materi == "Server") selected @endif value="Server">Server</option>
                                    <option @if ($materis->kategori_materi == "Virtualization") selected @endif value="Virtualization">Virtualization</option>
                                    <option @if ($materis->kategori_materi == "Hardware") selected @endif value="Hardware">Hardware</option>
                                    <option @if ($materis->kategori_materi == "GIS") selected @endif value="GIS">GIS</option>
                                    <option @if ($materis->kategori_materi == "Multimedia") selected @endif value="Multimedia">Multimedia</option>
                                    <option @if ($materis->kategori_materi == "Programming") selected @endif value="Programming">Programming</option>
                                    <option @if ($materis->kategori_materi == "Software Engineer") selected @endif value="Software Engineer">Software Engineer</option>
                                    <option @if ($materis->kategori_materi == "Office") selected @endif value="Office">Office</option>
                                    <option @if ($materis->kategori_materi == "Non-IT") selected @endif value="Non-IT">Non-IT</option>
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
                                <input id="durasi" type="text" placeholder="Masukan Durasi Materi" class="form-control @error('durasi') is-invalid @enderror" name="durasi" value="{{ old('durasi', $materis->durasi) }}" autocomplete="durasi" autofocus>
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
                                <input id="kode_materi" type="text" placeholder="Masukan Kode Materi" class="form-control @error('kode_materi') is-invalid @enderror" name="kode_materi" value="{{ old('kode_materi', $materis->kode_materi) }}" autocomplete="kode_materi" autofocus>
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
                                <select class="form-select @error('vendor') is-invalid @enderror" name="vendor" value="{{ old('vendor', ) }}" required autocomplete="vendor">
                                    <option selected>Pilih Vendor</option>
                                    <option value="AWS" @if ($materis->vendor == "AWS") selected @endif>AWS</option>
                                    <option value="Cisco" @if ($materis->vendor == "Cisco") selected @endif>Cisco</option>
                                    <option value="EC-Council" @if ($materis->vendor == "EC-Council") selected @endif>EC-Council</option>
                                    <option value="EPI" @if ($materis->vendor == "EPI") selected @endif>EPI</option>
                                    <option value="Google" @if ($materis->vendor == "Google") selected @endif>Google</option>
                                    <option value="ISACA" @if ($materis->vendor == "ISACA") selected @endif>ISACA</option>
                                    <option value="LSP" @if ($materis->vendor == "LSP") selected @endif>LSP</option>
                                    <option value="Microsoft" @if ($materis->vendor == "Microsoft") selected @endif>Microsoft</option>
                                    <option value="Mikrotik" @if ($materis->vendor == "Mikrotik") selected @endif>Mikrotik</option>
                                    <option value="CompTIA" @if ($materis->vendor == "CompTIA") selected @endif>CompTIA</option>
                                    <option value="Regular" @if ($materis->vendor == "Regular") selected @endif>Regular</option>
                                    <option value="RedHat" @if ($materis->vendor == "RedHat") selected @endif>RedHat</option>
                                    
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
                                <input type="file" name="silabus" class="form-control-file" accept="application/pdf" value="{{ old('silabus', $materis->silabus) }}">
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
