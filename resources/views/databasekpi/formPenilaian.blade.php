@extends('layouts_kpi.app')

@section('kpi_contents')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    {{-- ===== STYLE KHUSUS HALAMAN INI ===== --}}
    <style>
        /* Page Header */
        .page-header-modern {
            margin-bottom: 1.5rem;
        }

        .page-header-modern .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .page-title-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(99, 102, 241, .1), rgba(139, 92, 246, .1));
            color: #6366f1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* Content Card */
        .content-card {
            background: #fff;
            border-radius: 16px;
            border: 0;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .content-card .card-body {
            padding: 2rem;
        }

        /* Tab Navigation Modern */
        .modern-tab-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .modern-tab-btn {
            flex: 1 1 auto;
            min-width: 180px;
            padding: 12px 18px;
            border: 1px solid transparent;
            background: #fff;
            color: #475569;
            font-weight: 600;
            font-size: .875rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 10px;
            transition: all .25s ease;
            cursor: pointer;
            white-space: nowrap;
            text-align: left;
        }

        .modern-tab-btn i {
            font-size: 1.1rem;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, .08);
            color: #6366f1;
            flex-shrink: 0;
        }

        .modern-tab-btn:hover {
            border-color: #cbd5e1;
            background: #fff;
            color: #1e293b;
            transform: translateY(-1px);
        }

        .modern-tab-btn.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(99, 102, 241, .25);
        }

        .modern-tab-btn.active i {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .modern-tab-btn .tab-text {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }

        .modern-tab-btn .tab-text .name {
            font-weight: 700;
            font-size: .9rem;
        }

        .modern-tab-btn .tab-text .type {
            font-size: .75rem;
            opacity: .8;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .modern-tab-group {
                flex-wrap: nowrap;
                overflow-x: auto;
                scrollbar-width: thin;
                -webkit-overflow-scrolling: touch;
                padding: 10px;
            }

            .modern-tab-group::-webkit-scrollbar {
                height: 6px;
            }

            .modern-tab-group::-webkit-scrollbar-thumb {
                background: rgba(99, 102, 241, .3);
                border-radius: 3px;
            }

            .modern-tab-btn {
                min-width: 220px;
            }
        }

        /* Select Dropdown (when > 3 tabs) */
        .modern-select-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .modern-select-wrapper .form-select {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
            transition: all .2s ease;
            background-color: #fff;
        }

        .modern-select-wrapper .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
        }

        /* Empty & Thank You States */
        .state-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .06);
            padding: 3rem 2rem;
            text-align: center;
            border: 1px solid #f1f5f9;
        }

        .state-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .state-icon.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, .15), rgba(5, 150, 105, .15));
            color: #059669;
        }

        .state-icon.info {
            background: linear-gradient(135deg, rgba(99, 102, 241, .15), rgba(139, 92, 246, .15));
            color: #6366f1;
        }

        .state-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: .5rem;
        }

        .state-card p {
            color: #64748b;
            font-size: .95rem;
            margin-bottom: 1.5rem;
        }

        .state-card .highlight-text {
            display: inline-block;
            background: linear-gradient(135deg, rgba(99, 102, 241, .1), rgba(139, 92, 246, .1));
            color: #6366f1;
            padding: .5rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: .9rem;
            margin-top: .5rem;
        }

        /* Back Button */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: .875rem;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            transition: all .2s ease;
            margin-bottom: 1.5rem;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateX(-2px);
        }

        /* Loading Spinner */
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid transparent;
            border-top: 6px solid #a78bfa;
            border-right: 6px solid #38bdf8;
            border-bottom: 6px solid #34d399;
            border-left: 6px solid #facc15;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Form Container */
        .form-container {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .form-container .card-body {
            padding: 0;
        }
    </style>

    <div class="container content-wrapper mt-4">
        {{-- Loading Modal --}}
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="background: transparent; box-shadow: none; border: none;">
                    <div class="d-flex justify-content-center">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $outputData = collect($outputData ?? [])
                ->unique(function ($item) {
                    return $item['kode_form_global'] .
                        '_' .
                        $item['id_karyawan'] .
                        '_' .
                        $item['jenis_penilaian'] .
                        '_' .
                        $item['quartal'] .
                        '_' .
                        $item['tahun'];
                })
                ->values()
                ->toArray();
            $isEvaluator = $isEvaluator ?? false;
            $activeTab = min(max((int) request('active_tab', 0), 0), max(count($outputData) - 1, 0));
            $showThankYou = empty($outputData) && $isEvaluator;
        @endphp

        @if ($showThankYou)
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="state-card">
                        <div class="state-icon success">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <h3>Terima Kasih!</h3>
                        <p>Penilaian Anda telah berhasil dikirim dan akan menjadi kontribusi berharga bagi pengembangan tim
                            kami.</p>
                        @if (session('evaluated_name'))
                            <div class="highlight-text">
                                <i class="fa-solid fa-user-tie me-1"></i>
                                PENILAIAN KINERJA {{ strtoupper(session('evaluated_name')) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- NOT EVALUATOR STATE --}}
        @elseif (empty($outputData) && !$isEvaluator)
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="state-card">
                        <div class="state-icon info">
                            <i class="fa-solid fa-user-slash"></i>
                        </div>
                        <h3>Belum Ditunjuk Sebagai Evaluator</h3>
                        <p>Anda saat ini belum ditunjuk sebagai evaluator untuk penilaian kinerja manapun. Silakan hubungi
                            atasan jika ini adalah kesalahan.</p>
                        <a href="javascript:history.back()" class="btn-back">
                            <i class="fa-solid fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            {{-- MAIN FORM --}}
        @else
            <a href="javascript:history.back()" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>

            <div class="row justify-content-center">
                <div class="col-md-11 col-lg-10">
                    @if (count($outputData) > 1)
                        {{-- Tab Navigation (<=3 items) --}}
                        @if (count($outputData) <= 3)
                            <div class="modern-tab-group" role="tablist">
                                @foreach ($outputData as $i => $data)
                                    @php
                                        $shortNamePenilaian = match ($data['jenis_penilaian']) {
                                            'Manager/SPV/Team Leader (Atasan Langsung)' => 'Atasan Langsung',
                                            'General Manager' => 'General Manager',
                                            'Rekan Kerja (Satu Divisi)', 'Pekerja (Beda Divisi)' => 'Rekan Kerja',
                                            'Self Apprisial' => 'Self Appraisal',
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
                                        $evaluatedName = strtoupper(
                                            implode(' ', array_slice(explode(' ', $data['evaluated']), 0, 2)),
                                        );
                                    @endphp
                                    <button class="modern-tab-btn nav-link @if ($i == $activeTab) active @endif"
                                        id="tab-{{ $i }}" data-bs-toggle="tab"
                                        data-bs-target="#form-{{ $i }}" type="button" role="tab">
                                        <i class="bi {{ $iconNamePenilaian }}"></i>
                                        <span class="tab-text">
                                            <span class="name">{{ $evaluatedName }}</span>
                                            <span class="type">{{ $shortNamePenilaian }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>

                            {{-- Select Dropdown (>3 items) --}}
                        @else
                            <div class="modern-select-wrapper">
                                <label class="form-label fw-semibold text-dark mb-2">
                                    <i class="fa-solid fa-list-check text-primary me-1"></i> Pilih Form Penilaian
                                </label>
                                <select id="formTabSelect" class="form-select form-select-lg">
                                    @foreach ($outputData as $i => $data)
                                        @php
                                            $shortNamePenilaian = match ($data['jenis_penilaian']) {
                                                'Manager/SPV/Team Leader (Atasan Langsung)' => 'Atasan Langsung',
                                                'General Manager' => 'General Manager',
                                                'Rekan Kerja (Satu Divisi)', 'Pekerja (Beda Divisi)' => 'Rekan Kerja',
                                                'Self Apprisial' => 'Self Appraisal',
                                                default => 'Lainnya',
                                            };
                                            $evaluatedName = strtoupper(
                                                implode(' ', array_slice(explode(' ', $data['evaluated']), 0, 2)),
                                            );
                                        @endphp
                                        <option value="form-{{ $i }}"
                                            @if ($i == $activeTab) selected @endif>
                                            {{ $evaluatedName }} - {{ $shortNamePenilaian }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Tab Content --}}
                        <div class="tab-content">
                            @foreach ($outputData as $i => $data)
                                <div class="tab-pane fade @if ($i == $activeTab) show active @endif"
                                    id="form-{{ $i }}" role="tabpanel">
                                    <div class="form-container">
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

                        {{-- Single Form (no tabs) --}}
                    @else
                        <div class="form-container">
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

        {{-- SweetAlert Notifications --}}
        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {!! json_encode(session('success')) !!},
                    confirmButtonColor: '#6366f1',
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
                    text: {!! json_encode(session('error')) !!},
                    confirmButtonColor: '#ef4444'
                });
            </script>
        @endif

        @if ($errors->any())
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: '{!! implode('<br>', array_map('e', $errors->all())) !!}',
                    confirmButtonColor: '#ef4444'
                });
            </script>
        @endif

        @if (session('completed_all'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Terima Kasih!',
                    html: 'Terima kasih telah menyelesaikan semua penilaian.<br><strong class="text-primary">PENILAIAN KINERJA {{ strtoupper(session('evaluated_name') ?? '') }}</strong>',
                    confirmButtonText: 'Kembali',
                    confirmButtonColor: '#6366f1',
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
                valueDisplay.style.color = val < 30 ? "#ef4444" : val < 70 ? "#f59e0b" : "#10b981";
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
            form.querySelectorAll('input[data-required], select[data-required], textarea[data-required]').forEach(el => {
                if (!el.value.trim()) {
                    isValid = false;
                    el.classList.add('is-invalid');
                }
            });
            return isValid;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.tab-pane.active').forEach(p => bindRangeEvents(p));

            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', e => {
                    const pane = document.querySelector(e.target.getAttribute('data-bs-target'));
                    if (pane) bindRangeEvents(pane);
                });
            });

            // Handle select dropdown for > 3 tabs
            const selectEl = document.getElementById('formTabSelect');
            if (selectEl) {
                selectEl.addEventListener('change', function() {
                    const targetId = this.value;
                    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show',
                        'active'));
                    const target = document.getElementById(targetId);
                    if (target) {
                        target.classList.add('show', 'active');
                        bindRangeEvents(target);
                    }
                });
            }

            document.querySelectorAll('.styled-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!validateForm(this)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal',
                            text: 'Harap lengkapi semua field yang wajib diisi.',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                });
            });
        });
    </script>
@endsection
