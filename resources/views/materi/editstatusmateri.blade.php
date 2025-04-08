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
                                <input id="nama_materi" disabled type="text" placeholder="Masukan Nama Materi" class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi', $materis->nama_materi) }}" autocomplete="nama_materi" autofocus>
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
                                <select disabled class="form-select @error('kategori_materi') is-invalid @enderror" name="kategori_materi" value="{{ old('kategori_materi', ) }}" required autocomplete="kategori_materi">
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
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Status') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('status') is-invalid @enderror" name="status" required autocomplete="status">
                                    <option selected>Pilih Status</option>
                                    <option value="Aktif" @if ($materis->status == "Aktif") selected @endif>Aktif</option>
                                    <option value="Retired" @if ($materis->status == "Retired") selected @endif>Retired</option>
                                    <option value="Nonaktif" @if ($materis->status == "Nonaktif") selected @endif>Nonaktif</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                            <div class="col-md-6">
                                <textarea name="keterangan" id="keterangan" cols="30" rows="5" >{{$materis->keterangan}}</textarea>
                                @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe_materi" class="col-md-4 col-form-label text-md-start">{{ __('Tipe Materi') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('tipe_materi') is-invalid @enderror" name="tipe_materi" required autocomplete="tipe_materi">
                                    <option selected>Pilih Tipe Materi</option>
                                    <option value="Pergantian Nama" @if ($materis->tipe_materi == "Pergantian Nama") selected @endif>Pergantian Nama</option>
                                    <option value="Custom Request" @if ($materis->tipe_materi == "Custom Request") selected @endif>Custom Request</option>
                                    <option value="Normal" @if ($materis->tipe_materi == "Normal") selected @endif>Normal</option>
                                    <option value="Webinar/Workshop" @if ($materis->tipe_materi == "Webinar/Workshop") selected @endif>Webinar/Workshop</option>
                                </select>
                                @error('tipe_materi')
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
