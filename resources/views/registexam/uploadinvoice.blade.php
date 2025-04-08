@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h6 class="card-title text-center">Upload Invoice</h6>
                    <form action="{{ route('exam.uploadInvoicePost', $post->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id_peserta" value="{{$post->id_peserta}}">
                        <input type="hidden" name="id_registexam" value="{{$post->id}}">
                        <div class="form-group">
                            <label class="font-weight-bold">Invoice</label>
                            <input type="file" class="form-control @error('invoice') is-invalid @enderror" name="invoice" accept="application/pdf">
                            <!-- error message untuk title -->
                            @error('invoice')
                                <div class="alert alert-danger mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- {{$post}} --}}

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
