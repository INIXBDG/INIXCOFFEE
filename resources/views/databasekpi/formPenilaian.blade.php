@extends('layouts.app')

@section('content')
<div class="container mb-5">
    @if ($status === 'Belum Ditunjuk')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Anda Belum Ditunjuk Untuk Menjadi Evaluator</h6>
                    <h5>"PENILAIAN KINERJA"</h5>
                    <a href="javascript:history.back()" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
    @elseif ($status === true)
    <a href="javascript:history.back()" class="btn btn-primary mb-3">Kembali</a>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="">
                <div class="">
                    @if (!empty($outputData) && count($outputData) > 1)
                    <ul class="nav nav-tabs" id="formTab" role="tablist">
                        @foreach ($outputData as $i => $data)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($i === 0) active @endif"
                                id="tab-{{ $i }}" data-bs-toggle="tab" data-bs-target="#form-{{ $i }}"
                                type="button" role="tab">
                                {{ strtoupper(implode(' ', array_slice(explode(' ', $data['evaluated']), 0, 2))) }} - {{ $data['jenis_penilaian'] }}
                            </button>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content" id="formTabContent">
                        @foreach ($outputData as $i => $data)
                        <div class="tab-pane fade @if($i === 0) show active @endif" id="form-{{ $i }}"
                            role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    @include('databasekpi.formPenilaianUser', ['data' => $data, 'index' => $i])
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @elseif (!empty($outputData))
                    <!-- <h5 class="text-center mb-4">
                                PENILAIAN KINERJA {{ strtoupper($outputData[0]['evaluated']) }} <br>
                                <small>({{ $outputData[0]['jenis_penilaian'] }})</small>
                            </h5> -->
                    <div class="card">
                        <div class="card-body">
                            @include('databasekpi.formPenilaianUser', ['data' => $outputData[0], 'index' => 0])
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @elseif ($status === false)
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Terima Kasih Telah Melakukan</h6>
                    @if (!empty($outputData))
                    <h5>"PENILAIAN KINERJA {{ strtoupper($outputData[0]['evaluated']) }}"</h5>
                    @endif
                    <a href="javascript:history.back()" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- SweetAlert --}}
    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: "{{ session('success') }}",
            confirmButtonColor: '#3085d6'
        });
    </script>
    @endif

    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: "{{ session('error') }}",
            confirmButtonColor: '#d33'
        });
    </script>
    @endif

    @if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            confirmButtonColor: '#d33'
        });
    </script>
    @endif
</div>
<script>
    function updateRangeValue(rangeInput) {
        const valueDisplay = document.getElementById('val_' + rangeInput.id);
        if (valueDisplay) {
            valueDisplay.textContent = rangeInput.value;

            let val = parseInt(rangeInput.value);
            if (val < 30) valueDisplay.style.color = "red";
            else if (val < 70) valueDisplay.style.color = "orange";
            else valueDisplay.style.color = "green";
        }
    }

    function bindRangeEvents(container) {
        const rangeInputs = container.querySelectorAll('input[type="range"]');
        rangeInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                updateRangeValue(this);
            });
            updateRangeValue(input); // show initial value
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const firstTab = document.querySelector('.tab-pane.active');
        if (firstTab) bindRangeEvents(firstTab);

        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(event) {
                const paneId = event.target.getAttribute('data-bs-target');
                const targetPane = document.querySelector(paneId);
                if (targetPane) bindRangeEvents(targetPane);
            });
        });
    });
</script>
@endsection