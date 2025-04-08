@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h6 class="card-title text-center">Ganti Foto Profile</h6>
                    <form action="{{ route('karyawan.updateFoto', $users->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="font-weight-bold">Foto Profil</label>
                            <input type="file" class="form-control @error('foto') is-invalid @enderror" name="foto">

                            <!-- error message untuk title -->
                            @error('foto')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Foto TTD</label>
                            <input type="file" class="form-control @error('ttd') is-invalid @enderror" name="ttd">

                            <!-- error message untuk title -->
                            @error('ttd')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end my-3">
                            <button type="submit" class="btn click-primary mx-4">Simpan</button>
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
