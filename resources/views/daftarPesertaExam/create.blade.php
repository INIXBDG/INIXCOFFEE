@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card m-4 p-3">
                <div class="card-body">
                    <h3 class="card-title text-center my-3">{{ __('Daftarkan Peserta Exam') }}</h3>
                    
                    <form action="{{ route('daftar-peserta-exam.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_rkm" value="{{ $rkm->id }}">
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label fw-bold" for="selectAll">
                                        {{ __('Pilih Semua') }}
                                    </label>
                                </div>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            @forelse ($rkm->perusahaan->peserta as $peserta)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input peserta-checkbox" 
                                            type="checkbox" 
                                            name="peserta_id[]" 
                                            value="{{ $peserta->id }}"
                                            id="peserta_{{ $peserta->id }}"
                                        >
                                        <label class="form-check-label" for="peserta_{{ $peserta->id }}">
                                            {{ $peserta->nama }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        {{ __('Tidak ada peserta yang tersedia') }}
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> {{ __('Daftarkan') }}
                                </button>
                                <a href="{{ redirect()->back()->getTargetUrl() }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> {{ __('Batal') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const pesertaCheckboxes = document.querySelectorAll('.peserta-checkbox');

    // Handle "Select All" checkbox
    selectAllCheckbox.addEventListener('change', function() {
        pesertaCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update "Select All" checkbox based on individual checkboxes
    pesertaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(pesertaCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(pesertaCheckboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        });
    });
});
</script>
@endsection