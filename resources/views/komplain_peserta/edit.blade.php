@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-5">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Edit Komplain Peserta') }}</h5>

                    <form method="POST" action="{{ route('updateKomplain', $feedback->nilaifeedback_id) }}">
                        @csrf 

                        @foreach ($komplains as $komplain)
                            <div class="row mb-3">
                                <label for="komplain[{{ $komplain->id }}]" class="col-md-4 col-form-label text-md-start">{{ __('Komplain') }}</label>
                                <div class="col-md-6">
                                    <textarea type="text" required class="form-control" name="komplain[{{ $komplain->id }}]" id="komplain[{{ $komplain->id }}]" rows="2" cols="20" wrap="off" style="overflow: hidden; resize: horizontal">{{ $komplain->komplain }}</textarea>
                                    @error('komplain[{{ $komplain->id }}]')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="kategori[{{ $komplain->id }}]" class="col-md-4 col-form-label text-md-start">{{ __('Kategori') }}</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="kategori[{{ $komplain->id }}]" id="kategori[{{ $komplain->id }}]" disabled value="{{ $komplain->kategori_feedback }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="created_at" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Dibuat') }}</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="created_at" id="created_at" disabled value="{{ $komplain->created_at }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="feedback" class="col-md-4 col-form-label text-md-start">{{ __('Feedback') }}</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="feedback" id="feedback" disabled value="{{ $feedback->nama_perusahaan ?? '-' }} - {{ $feedback->nama_materi ?? '-' }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_selesai[{{ $komplain->id }}]" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal selesai') }}</label>
                                <div class="col-md-6">
                                    <input type="date" class="form-control" name="tanggal_selesai[{{ $komplain->id }}]" id="tanggal_selesai[{{ $komplain->id }}]" value="{{ $komplain->tanggal_selesai }}">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <label for="status[{{ $komplain->id }}]" class="col-md-4 col-form-label text-md-start">{{ __('Status') }}</label>
                                <div class="col-md-6">
                                    <select class="form-select" name="status[{{ $komplain->id }}]" id="status[{{ $komplain->id }}]">
                                    <option value="on progress" {{ old('status', $komplain->status) == 'on progress' ? 'selected' : '' }}>On Progress</option>
                                    <option value="completed" {{ old('status', $komplain->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="delayed" {{ old('status', $komplain->status) == 'delayed' ? 'selected' : '' }}>Delayed</option>
                                </select>
                                </div>
                            </div>

                            <hr>

                            @endforeach
                        <div class="row mb-4">
                            <div class="col-md-8 offset-md-4">
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
@endsection
