@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .corporate-btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .corporate-btn {
        flex: 0 1 auto;
        padding: 8px 14px;
        border: none;
        background: white;
        color: #495057;
        font-weight: 500;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        transition: all 0.25s ease;
        white-space: nowrap;
        /* Biar teks tidak pecah ke bawah */
    }

    .corporate-btn i {
        font-size: 1rem;
        flex-shrink: 0;
    }

    .corporate-btn:hover {
        background: linear-gradient(to right, #f1f3f5, #e9ecef);
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .corporate-btn:active {
        background: #dee2e6;
    }

    /* Scroll horizontal untuk layar kecil */
    @media (max-width: 768px) {
        .corporate-btn-group {
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .corporate-btn-group::-webkit-scrollbar {
            height: 6px;
        }

        .corporate-btn-group::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }
    }
</style>
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

                    @php
                        $tabsCount = count($outputData);
                    @endphp

                    @if ($tabsCount <= 3)
                        <div class="corporate-btn-group nav nav-tabs rounded-0 rounded-top" id="formTab" role="tablist">
                            @foreach ($outputData as $i => $data)

                            @php
                            $shortNamePenilaian = 'default';
                            switch ($data['jenis_penilaian']) {
                                case "Manager/SPV/Team Leader (Atasan Langsung)":
                                    $shortNamePenilaian = "Atasan Langsung"; break;
                                case "General Manager":
                                    $shortNamePenilaian = "General Manager"; break;
                                case "Rekan Kerja (Satu Divisi)":
                                case "Pekerja (Beda Divisi)":
                                    $shortNamePenilaian = "Rekan Kerja"; break;
                                case "Self Apprisial":
                                    $shortNamePenilaian = "Self Apprisial"; break;
                            }

                            $iconNamePenilaian = match ($data['jenis_penilaian']) {
                                "Manager/SPV/Team Leader (Atasan Langsung)" => 'bi-person-badge',
                                "General Manager" => 'bi-award',
                                "Rekan Kerja (Satu Divisi)" => 'bi-people',
                                "Pekerja (Beda Divisi)" => 'bi-people-arrows',
                                "Self Apprisial" => 'bi-person-check',
                                default => 'default',
                            };
                            @endphp

                            <button class="corporate-btn nav-link @if($i === 0) active @endif"
                                id="tab-{{ $i }}"
                                data-bs-toggle="tab"
                                data-bs-target="#form-{{ $i }}"
                                type="button"
                                role="tab"
                                aria-controls="form-{{ $i }}"
                                aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                <i class="bi {{ $iconNamePenilaian }}"></i>
                                {{ strtoupper(implode(' ', array_slice(explode(' ', $data['evaluated']), 0, 2))) }} - {{ $shortNamePenilaian }}
                            </button>
                            @endforeach
                        </div>
                    @else
                        <div class="mb-3">
                            <select id="formTabSelect" class="form-select" aria-label="Pilih Penilaian">
                                @foreach ($outputData as $i => $data)
                                @php
                                    $shortNamePenilaian = 'default';
                                    switch ($data['jenis_penilaian']) {
                                        case "Manager/SPV/Team Leader (Atasan Langsung)":
                                            $shortNamePenilaian = "Atasan Langsung"; break;
                                        case "General Manager":
                                            $shortNamePenilaian = "General Manager"; break;
                                        case "Rekan Kerja (Satu Divisi)":
                                        case "Pekerja (Beda Divisi)":
                                            $shortNamePenilaian = "Rekan Kerja"; break;
                                        case "Self Apprisial":
                                            $shortNamePenilaian = "Self Apprisial"; break;
                                    }
                                @endphp
                                <option value="form-{{ $i }}" @if($i === 0) selected @endif>
                                    {{ strtoupper(implode(' ', array_slice(explode(' ', $data['evaluated']), 0, 2))) }} - {{ $shortNamePenilaian }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="tab-content" id="formTabContent">
                        @foreach ($outputData as $i => $data)
                        <div class="tab-pane fade @if($i === 0) show active @endif" id="form-{{ $i }}"
                            role="tabpanel"
                            aria-labelledby="tab-{{ $i }}">
                            <div class="card rounded-0 rounded-bottom shadow-sm">
                                <div class="card-body">
                                    @include('databasekpi.formPenilaianUser', ['data' => $data, 'index' => $i])
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!-- @elseif (!empty($outputData))
                    <div class="card rounded-0 rounded-bottom shadow-sm">
                        <div class="card-body">
                            @include('databasekpi.formPenilaianUser', ['data' => $outputData[0], 'index' => 0])
                        </div>
                    </div>
                    @endif -->
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
            updateRangeValue(input);
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

                const select = document.getElementById('formTabSelect');
                if(select) {
                    select.value = paneId;
                }
            });
        });

        const select = document.getElementById('formTabSelect');
        if (select) {
            select.addEventListener('change', function() {
                const targetId = this.value;

                document.querySelectorAll('.tab-pane').forEach(function(pane) {
                    pane.classList.remove('show', 'active');
                });
                const targetPane = document.getElementById(targetId);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                    bindRangeEvents(targetPane);
                }

                document.querySelectorAll('.corporate-btn-group button').forEach(function(btn) {
                    btn.classList.remove('active');
                });
            });
        }
    });
</script>

@endsection