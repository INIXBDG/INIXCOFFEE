@extends('databasekpi.berandaKPI')
@section('contentKPI')
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

        .corporate-btn.is-invalid {
            border-color: #e74c3c !important;
            color: #e74c3c !important;
            box-shadow: 0 0 0 0.15rem rgba(231, 76, 60, 0.25) !important;
        }

        @media (max-width: 768px) {
            .corporate-btn-group {
                flex-wrap: nowrap;
                overflow-x: auto;
                scrollbar-width: thin;
                -webkit-overflow-scrolling: touch;
            }

            .corporate-btn-group::-webkit-scrollbar {
                height: 6px;
            }

            .corporate-btn-group::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.2);
                border-radius: 3px;
            }
        }

        .styled-form {
            padding: 20px;
        }

        .styled-form .card-kriteria {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 25px 30px;
            margin-bottom: 30px;
            transition: all 0.3s ease-in-out;
            border-left: 5px solid #3498db;
            box-sizing: border-box;
        }

        .styled-form .card-kriteria h5 {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .styled-form label {
            font-weight: 500;
            color: #333;
        }

        .styled-form .form-control,
        .styled-form .form-select {
            border-radius: 10px;
            transition: border-color 0.3s ease-in-out;
        }

        .styled-form .form-control:focus,
        .styled-form .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.15rem rgba(52, 152, 219, 0.25);
        }

        .styled-form .btn-outline-secondary {
            border-radius: 20px;
        }

        .styled-form .btn-outline-secondary:hover,
        .styled-form .btn-outline-secondary:focus {
            background-color: #3498db;
            color: #fff;
            border-color: #3498db;
        }

        .styled-form .form-check-input:checked {
            background-color: #3498db;
            border-color: #3498db;
        }

        .styled-form .range-value {
            width: 40px;
            text-align: center;
            color: #3498db;
            font-weight: bold;
        }

        .styled-form .btn-primary {
            background-color: #3498db;
            border: none;
            border-radius: 12px;
            padding: 10px 30px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
            transition: all 0.3s ease-in-out;
        }

        .styled-form .btn-primary:hover {
            background-color: #2980b9;
            box-shadow: 0 6px 16px rgba(41, 128, 185, 0.5);
        }

        .styled-form .is-invalid {
            border-color: #e74c3c !important;
            box-shadow: 0 0 0 0.15rem rgba(231, 76, 60, 0.25) !important;
        }

        @media (max-width: 768px) {
            .styled-form {
                padding: 10px;
            }

            .styled-form .card-kriteria {
                padding: 15px;
                margin-bottom: 20px;
            }

            .styled-form .row {
                flex-direction: column;
                margin-left: 0 !important;
            }

            .styled-form label {
                margin-bottom: 8px;
                text-align: left !important;
                padding-left: 0;
            }

            .styled-form .col-md-4,
            .styled-form .col-md-6,
            .styled-form .col-md-8,
            .styled-form .col-md-4 {
                width: 100%;
                max-width: 100%;
            }

            .styled-form .text-end {
                text-align: center !important;
                margin-right: 0 !important;
            }

            .styled-form .btn-primary {
                width: 100%;
                padding: 12px;
            }

            .styled-form .d-flex.flex-wrap.gap-2,
            .styled-form .d-flex.flex-wrap.gap-3 {
                gap: 10px;
            }

            .styled-form .d-flex.align-items-center.gap-3 {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

        .styled-form .btn-outline-secondary.is-invalid {
            border-color: #e74c3c !important;
            color: #e74c3c !important;
            box-shadow: 0 0 0 0.15rem rgba(231, 76, 60, 0.25) !important;
        }

        .styled-form .form-check-label.is-invalid {
            color: #e74c3c;
            font-weight: 500;
        }

        .styled-form .form-control.is-invalid,
        .styled-form .form-select.is-invalid,
        .styled-form .btn-check.is-invalid,
        .styled-form .form-check-input.is-invalid {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            50% {
                transform: translateX(5px);
            }

            75% {
                transform: translateX(-5px);
            }

            100% {
                transform: translateX(0);
            }
        }
    </style>
    <div class="content-wrapper">
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="loading-spinner"></div>
            </div>
        </div>
        @php
            $outputData = $outputData ?? [];
            $isEvaluator = $isEvaluator ?? false;
            $activeTab = min(max((int) request('active_tab', 0), 0), count($outputData) - 1);
            $showThankYou = empty($outputData) && $isEvaluator;
        @endphp
        @if ($showThankYou)
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>Terima Kasih Telah Memberikan Penilaian Anda</h6>
                            @if (session('evaluated_name'))
                                <h5>"PENILAIAN KINERJA {{ strtoupper(session('evaluated_name')) }}"</h5>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @elseif (empty($outputData) && !$isEvaluator)
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
        @else
            <a href="javascript:history.back()" class="btn btn-primary mb-3">Kembali</a>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    @if (count($outputData) > 1)
                        @if (count($outputData) <= 3)
                            <div class="corporate-btn-group nav nav-tabs rounded-0 rounded-top" id="formTab"
                                role="tablist">
                                @foreach ($outputData as $i => $data)
                                    @php
                                        $shortNamePenilaian = match ($data['jenis_penilaian']) {
                                            'Manager/SPV/Team Leader (Atasan Langsung)' => 'Atasan Langsung',
                                            'General Manager' => 'General Manager',
                                            'Rekan Kerja (Satu Divisi)', 'Pekerja (Beda Divisi)' => 'Rekan Kerja',
                                            'Self Apprisial' => 'Self Apprisial',
                                            default => 'Lainnya',
                                        };
                                        $iconNamePenilaian = match ($data['jenis_penilaian']) {
                                            'Manager/SPV/Team Leader (Atasan Langsung)' => 'bi-person-badge',
                                            'General Manager' => 'bi-award',
                                            'Rekan Kerja (Satu Divisi)' => 'bi-people',
                                            'Pekerja (Beda Divisi)' => 'bi-people-arrows',
                                            'Self Apprisial' => 'bi-person-check',
                                            default => 'bi-file-text',
                                        };
                                    @endphp
                                    <button class="corporate-btn nav-link @if ($i == $activeTab) active @endif"
                                        id="tab-{{ $i }}" data-bs-toggle="tab"
                                        data-bs-target="#form-{{ $i }}" type="button" role="tab"
                                        aria-controls="form-{{ $i }}"
                                        aria-selected="{{ $i == $activeTab ? 'true' : 'false' }}">
                                        <i class="bi {{ $iconNamePenilaian }}"></i>
                                        {{ strtoupper(implode(' ', array_slice(explode(' ', $data['evaluated']), 0, 2))) }}
                                        -
                                        {{ $shortNamePenilaian }}
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="mb-3">
                                <select id="formTabSelect" class="form-select" aria-label="Pilih Penilaian">
                                    @foreach ($outputData as $i => $data)
                                        @php
                                            $shortNamePenilaian = match ($data['jenis_penilaian']) {
                                                'Manager/SPV/Team Leader (Atasan Langsung)' => 'Atasan Langsung',
                                                'General Manager' => 'General Manager',
                                                'Rekan Kerja (Satu Divisi)', 'Pekerja (Beda Divisi)' => 'Rekan Kerja',
                                                'Self Apprisial' => 'Self Apprisial',
                                                default => 'Lainnya',
                                            };
                                        @endphp
                                        <option value="form-{{ $i }}"
                                            @if ($i == $activeTab) selected @endif>
                                            {{ strtoupper(implode(' ', array_slice(explode(' ', $data['evaluated']), 0, 2))) }}
                                            - {{ $shortNamePenilaian }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="tab-content" id="formTabContent">
                            @foreach ($outputData as $i => $data)
                                <div class="tab-pane fade @if ($i == $activeTab) show active @endif"
                                    id="form-{{ $i }}" role="tabpanel"
                                    aria-labelledby="tab-{{ $i }}">
                                    <div class="card rounded-0 rounded-bottom shadow-sm">
                                        <div class="card-body">
                                            @include('databasekpi.formPenilaianUser', [
                                                'data' => $data,
                                                'index' => $i,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="card rounded-0 rounded-bottom shadow-sm">
                            <div class="card-body">
                                @include('databasekpi.formPenilaianUser', [
                                    'data' => $outputData[0],
                                    'index' => 0,
                                ])
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {
                        !!json_encode(session('success')) !!
                    },
                    confirmButtonColor: '#3085d6',
                    timer: 1500,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            </script>
        @endif
        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: {
                        !!json_encode(session('error')) !!
                    },
                    confirmButtonColor: '#d33'
                });
            </script>
        @endif
        @if ($errors->any())
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: '{!! implode('<br>', array_map('e', $errors->all())) !!}',
                    confirmButtonColor: '#d33'
                });
            </script>
        @endif
        @if (session('completed_all'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Terima Kasih!',
                    html: 'Terima kasih telah menyelesaikan semua penilaian.<br><strong>PENILAIAN KINERJA {{ strtoupper(session('evaluated_name ') ?? '') }}</strong>',
                    confirmButtonText: 'Kembali',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    window.history.back();
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
                valueDisplay.style.color = val < 30 ? "red" : val < 70 ? "orange" : "green";
            }
        }

        function bindRangeEvents(container) {
            container.querySelectorAll('input[type="range"]').forEach(input => {
                input.addEventListener('input', () => updateRangeValue(input));
                updateRangeValue(input);
            });
        }

        function validateForm(form) {
            let isValid = true;
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            const radioGroups = {};
            form.querySelectorAll('input[type="radio"][data-group]').forEach(radio => {
                const group = radio.dataset.group;
                if (!radioGroups[group]) radioGroups[group] = [];
                radioGroups[group].push(radio);
            });
            Object.entries(radioGroups).forEach(([group, radios]) => {
                const isRequired = radios.some(r => r.hasAttribute('data-required'));
                if (isRequired && !radios.some(r => r.checked)) {
                    isValid = false;
                    radios.forEach(r => {
                        r.classList.add('is-invalid');
                        const label = document.querySelector(`label[for="${r.id}"]`);
                        if (label) label.classList.add('is-invalid');
                    });
                    const first = radios[0];
                    const label = document.querySelector(`label[for="${first.id}"]`);
                    (label || first).scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                } else {
                    radios.forEach(r => {
                        r.classList.remove('is-invalid');
                        const label = document.querySelector(`label[for="${r.id}"]`);
                        if (label) label.classList.remove('is-invalid');
                    });
                }
            });
            const checkboxGroups = {};
            form.querySelectorAll('input[type="checkbox"].checkbox-group[data-group]').forEach(cb => {
                const group = cb.dataset.group;
                if (!checkboxGroups[group]) checkboxGroups[group] = [];
                checkboxGroups[group].push(cb);
            });
            Object.entries(checkboxGroups).forEach(([group, checkboxes]) => {
                const isRequired = checkboxes.some(cb => cb.hasAttribute('data-required'));
                if (isRequired && !checkboxes.some(cb => cb.checked)) {
                    isValid = false;
                    checkboxes.forEach(cb => {
                        cb.classList.add('is-invalid');
                        const label = document.querySelector(`label[for="${cb.id}"]`);
                        if (label) label.classList.add('is-invalid');
                    });
                    const first = checkboxes[0];
                    const label = document.querySelector(`label[for="${first.id}"]`);
                    (label || first).scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                } else {
                    checkboxes.forEach(cb => {
                        cb.classList.remove('is-invalid');
                        const label = document.querySelector(`label[for="${cb.id}"]`);
                        if (label) label.classList.remove('is-invalid');
                    });
                }
            });
            form.querySelectorAll(
                'input[data-required]:not([type="radio"]):not([type="checkbox"]), select[data-required], textarea[data-required]'
            ).forEach(el => {
                if (!el.value.trim()) {
                    isValid = false;
                    el.classList.add('is-invalid');
                    el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                } else {
                    el.classList.remove('is-invalid');
                }
            });
            form.querySelectorAll('input[type="range"][data-required]').forEach(range => {
                if (parseInt(range.value) === 0) {
                    isValid = false;
                    range.classList.add('is-invalid');
                    range.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                } else {
                    range.classList.remove('is-invalid');
                }
            });
            return isValid;
        }
        document.addEventListener('DOMContentLoaded', function() {
            const activePane = document.querySelector('.tab-pane.active');
            if (activePane) bindRangeEvents(activePane);
            const select = document.getElementById('formTabSelect');
            if (select && select.value) {
                const initialPane = document.getElementById(select.value);
                if (initialPane) initialPane.classList.add('show', 'active');
            }
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', event => {
                    const paneId = event.target.getAttribute('data-bs-target');
                    const targetPane = document.querySelector(paneId);
                    if (targetPane) bindRangeEvents(targetPane);
                    if (select) select.value = paneId.substring(1);
                });
            });
            if (select) {
                select.addEventListener('change', function() {
                    const targetId = this.value;
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });
                    const targetPane = document.getElementById(targetId);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                        bindRangeEvents(targetPane);
                    }
                    document.querySelectorAll('.corporate-btn-group button').forEach(btn => btn.classList
                        .remove('active'));
                });
            }
            document.querySelectorAll('.styled-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (!submitBtn) return;
                    if (!validateForm(this)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal',
                            text: 'Harap lengkapi semua field yang wajib diisi.',
                            confirmButtonColor: '#d33'
                        });
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Kirim';
                        return;
                    }
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Mengirim...';
                    this.submit();
                });
            });
        });
    </script>
@endsection
