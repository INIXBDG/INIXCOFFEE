<style>
    /* Form Wrapper */
    .styled-form {
        padding: 2rem;
    }

    /* Form Header */
    .form-header {
        text-align: center;
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(99, 102, 241, .05), rgba(139, 92, 246, .05));
        border-radius: 12px;
        margin-bottom: 2rem;
        border: 1px solid rgba(99, 102, 241, .1);
    }

    .form-header h4 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: .5rem;
    }

    .form-header .type-badge {
        display: inline-block;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        padding: .35rem .9rem;
        border-radius: 50px;
        font-size: .8rem;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(99, 102, 241, .25);
    }

    /* Kriteria Card */
    .styled-form .card-kriteria {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        transition: all .3s ease;
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
    }

    .styled-form .card-kriteria::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #6366f1, #8b5cf6);
    }

    .styled-form .card-kriteria:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, .06);
        border-color: #cbd5e1;
    }

    .styled-form .card-kriteria h5 {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        padding-bottom: .75rem;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .styled-form .card-kriteria h5::before {
        content: '\f0ae';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #6366f1;
        font-size: 1rem;
    }

    /* Form Labels - PERBAIKAN */
    .styled-form label.col-form-label {
        font-weight: 600;
        color: #334155;
        font-size: .9rem;
        line-height: 1.5;
        margin-bottom: 0;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
    }

    .styled-form label .required-mark {
        color: #ef4444;
        font-weight: 700;
        margin-right: 2px;
    }

    /* Form Row - PERBAIKAN LAYOUT */
    .styled-form .form-row-item {
        margin-bottom: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .styled-form .form-label-col {
        flex: 0 0 50%;
        max-width: 50%;
        padding-right: 2rem;
    }

    .styled-form .form-input-col {
        flex: 1;
        min-width: 0;
    }

    /* Form Controls */
    .styled-form .form-control,
    .styled-form .form-select {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: .65rem 1rem;
        transition: all .2s ease;
        font-size: .9rem;
        width: 100%;
    }

    .styled-form .form-control:focus,
    .styled-form .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
    }

    .styled-form textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    /* Radio Button Group (Pill Style) - PERBAIKAN */
    .radio-pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .radio-pill {
        display: inline-flex;
    }

    .radio-pill input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .radio-pill label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 45px;
        height: 45px;
        padding: 0 1rem;
        border: 2px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-weight: 600;
        font-size: .95rem;
        border-radius: 50px;
        cursor: pointer;
        transition: all .2s ease;
        margin: 0;
        text-align: center;
    }

    .radio-pill label:hover {
        border-color: #6366f1;
        background: rgba(99, 102, 241, .05);
        color: #6366f1;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(99, 102, 241, .15);
    }

    .radio-pill input[type="radio"]:checked+label {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(99, 102, 241, .3);
        transform: translateY(-2px);
    }

    .radio-pill input[type="radio"].is-invalid+label {
        border-color: #ef4444;
        color: #ef4444;
        animation: shake .5s ease-in-out;
    }

    /* Checkbox Modern */
    .checkbox-modern {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        transition: all .2s ease;
        cursor: pointer;
        background: #fff;
        margin-bottom: 8px;
    }

    .checkbox-modern:hover {
        border-color: #6366f1;
        background: rgba(99, 102, 241, .02);
        transform: translateX(2px);
    }

    .checkbox-modern input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 2px;
        cursor: pointer;
        accent-color: #6366f1;
        flex-shrink: 0;
    }

    .checkbox-modern label {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
        color: #334155;
        font-size: .9rem;
        line-height: 1.4;
    }

    .checkbox-modern input[type="checkbox"].is-invalid {
        accent-color: #ef4444;
    }

    /* Range Slider */
    .range-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
        width: 100%;
    }

    .range-wrapper input[type="range"] {
        flex: 1;
        height: 8px;
        border-radius: 5px;
        background: linear-gradient(to right, #e2e8f0, #e2e8f0);
        outline: none;
        -webkit-appearance: none;
    }

    .range-wrapper input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(99, 102, 241, .4);
        border: 3px solid #fff;
    }

    .range-wrapper input[type="range"]::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(99, 102, 241, .4);
        border: 3px solid #fff;
    }

    .range-value {
        min-width: 60px;
        text-align: center;
        font-weight: 700;
        font-size: 1.1rem;
        padding: 6px 12px;
        border-radius: 8px;
        background: #f1f5f9;
        flex-shrink: 0;
    }

    /* Submit Button */
    .btn-submit-form {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 14px 45px;
        font-weight: 700;
        font-size: 1rem;
        box-shadow: 0 4px 14px rgba(99, 102, 241, .3);
        transition: all .25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit-form:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, .4);
        color: #fff;
    }

    .btn-submit-form:active {
        transform: translateY(0);
    }

    .btn-submit-form:disabled {
        opacity: .7;
        cursor: not-allowed;
    }

    /* Validation States */
    .styled-form .is-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .1) !important;
    }

    .styled-form .form-check-label.is-invalid {
        color: #ef4444;
        font-weight: 600;
    }

    @keyframes shake {

        0%,
        100% {
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
    }

    /* Responsive - PERBAIKAN */
    @media (max-width: 992px) {
        .styled-form .form-row-item {
            flex-direction: column;
            gap: 0.5rem;
        }

        .styled-form .form-label-col,
        .styled-form .form-input-col {
            flex: 0 0 100%;
            max-width: 100%;
            padding-right: 0;
        }

        .styled-form label.col-form-label {
            text-align: left !important;
            margin-bottom: 0.5rem;
            display: block;
        }
    }

    @media (max-width: 768px) {
        .styled-form {
            padding: 1.25rem;
        }

        .styled-form .card-kriteria {
            padding: 1.25rem;
        }

        .styled-form .card-kriteria h5 {
            font-size: 1.05rem;
        }

        .form-header h4 {
            font-size: 1.1rem;
        }

        .btn-submit-form {
            width: 100%;
            justify-content: center;
        }

        .radio-pill label {
            min-width: 40px;
            height: 40px;
            padding: 0 0.75rem;
            font-size: 0.875rem;
        }
    }

    @media (max-width: 576px) {
        .radio-pill-group {
            gap: 6px;
        }

        .radio-pill label {
            min-width: 36px;
            height: 36px;
            padding: 0 0.6rem;
            font-size: 0.85rem;
        }

        .range-wrapper {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .range-value {
            align-self: center;
        }
    }
</style>

<form method="POST" action="{{ route('penilaianEvaluator') }}" class="styled-form" novalidate>
    @csrf
    <input type="hidden" name="kode_form" value="{{ $data['kode_form_global'] }}">
    <input type="hidden" name="id_evaluated" value="{{ $data['id_karyawan'] }}">
    <input type="hidden" name="jenis_penilaian" value="{{ $data['jenis_penilaian'] }}">
    <input type="hidden" name="quartal" value="{{ $data['quartal'] }}">
    <input type="hidden" name="tahun" value="{{ $data['tahun'] }}">

    {{-- Form Header --}}
    <div class="form-header">
        <h4>
            <i class="fa-solid fa-user-tie text-primary me-2"></i>
            PENILAIAN KINERJA {{ strtoupper($data['evaluated']) }}
        </h4>
        <span class="type-badge">
            <i class="fa-solid fa-clipboard-list me-1"></i>
            {{ $data['jenis_penilaian'] }}
        </span>
    </div>

    @foreach ($data['detail_kategori'] as $index => $kategori)
        <div class="card-kriteria">
            <h5>{{ $kategori['kriteria_utama'] }}</h5>

            @foreach ($kategori['isi_kriteria'] as $i => $sub)
                @php
                    $fieldKey = "field_{$index}_{$i}";
                    $fieldName = "{$fieldKey}[{$sub['sub_kriteria_judul']}]";
                    $fieldId = Str::slug($sub['sub_kriteria_judul']) . "_{$index}_{$i}_{$data['id_karyawan']}_{$data['jenis_penilaian']}";
                @endphp

                <div class="form-row-item">
                    <label id="label_{{ $fieldId }}" class="form-label-col">
                        @if ($sub['level'] === 'required')
                            <span class="required-mark">*</span>
                        @endif
                        {{ $sub['sub_kriteria_judul'] }}
                    </label>
                    <div class="form-input-col">
                        {{-- RADIO --}}
                        @if ($sub['tipe_kategori'] === 'radio')
                            <div class="radio-pill-group">
                                @foreach ($sub['keterangan_tipe'] as $j => $option)
                                    @php $id = "radio_{$fieldId}_{$j}"; @endphp
                                    <div class="radio-pill">
                                        <input type="radio" name="{{ $fieldName }}" id="{{ $id }}"
                                            value="{{ $option['nilai'] ?? $option['ket'] }}"
                                            data-group="{{ $fieldId }}"
                                            @if ($sub['level'] === 'required') data-required="true" @endif>
                                        <label for="{{ $id }}">{{ $option['ket'] }}</label>
                                    </div>
                                @endforeach
                            </div>

                        {{-- CHECKBOX --}}
                        @elseif ($sub['tipe_kategori'] === 'checkbox')
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($sub['keterangan_tipe'] as $j => $option)
                                    @php $id = "checkbox_{$fieldId}_{$j}"; @endphp
                                    <div class="checkbox-modern">
                                        <input type="checkbox" name="{{ $fieldName }}[]" id="{{ $id }}"
                                            value="{{ $option['nilai'] ?? $option['ket'] }}"
                                            data-group="{{ $fieldId }}"
                                            @if ($sub['level'] === 'required') data-required="true" @endif>
                                        <label for="{{ $id }}">{{ $option['ket'] }}</label>
                                    </div>
                                @endforeach
                            </div>

                        {{-- SELECT --}}
                        @elseif ($sub['tipe_kategori'] === 'select')
                            <select class="form-select" name="{{ $fieldName }}" id="select_{{ $fieldId }}"
                                @if ($sub['level'] === 'required') data-required="true" @endif>
                                <option selected disabled>Pilih {{ $sub['sub_kriteria_judul'] }}</option>
                                @foreach ($sub['keterangan_tipe'] as $option)
                                    <option value="{{ $option['nilai'] ?? $option['ket'] }}">{{ $option['ket'] }}</option>
                                @endforeach
                            </select>

                        {{-- TEXTAREA --}}
                        @elseif ($sub['tipe_kategori'] === 'textarea')
                            <textarea name="pesan_field_{{ $fieldKey }}[{{ $sub['sub_kriteria_judul'] }}]" 
                                class="form-control" rows="4" placeholder="Tuliskan catatan Anda..." 
                                id="textarea_{{ $fieldId }}"
                                @if ($sub['level'] === 'required') data-required="true" @endif></textarea>

                        {{-- RANGE --}}
                        @elseif ($sub['tipe_kategori'] === 'range')
                            <div class="range-wrapper">
                                <input type="range" class="form-range" name="{{ $fieldName }}"
                                    id="range_{{ $fieldId }}" min="0" max="100" value="0"
                                    @if ($sub['level'] === 'required') data-required="true" @endif
                                    oninput="updateRangeValue(this)">
                                <span class="range-value fw-bold" id="val_range_{{ $fieldId }}">0</span>
                            </div>

                        {{-- TEXT + NUMBER --}}
                        @elseif ($sub['tipe_kategori'] === 'text')
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <input type="text" name="teks_field_{{ $fieldKey }}[{{ $sub['sub_kriteria_judul'] }}]"
                                        id="input_{{ $fieldId }}" class="form-control"
                                        placeholder="Masukkan {{ $sub['sub_kriteria_judul'] }}"
                                        @if ($sub['level'] === 'required') data-required="true" @endif>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="nilai_field_{{ $fieldKey }}[{{ $sub['sub_kriteria_judul'] }}]"
                                        id="nilai_{{ $fieldId }}" class="form-control" placeholder="Nilai"
                                        min="0" max="100" step="1"
                                        @if ($sub['level'] === 'required') data-required="true" @endif>
                                </div>
                            </div>

                        {{-- DEFAULT --}}
                        @else
                            <input type="{{ $sub['tipe_kategori'] }}" name="{{ $fieldName }}"
                                id="input_{{ $fieldId }}" class="form-control"
                                placeholder="Masukkan {{ $sub['sub_kriteria_judul'] }}" autocomplete="off"
                                @if ($sub['level'] === 'required') data-required="true" @endif>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    {{-- Submit Button --}}
    <div class="text-end mt-4 pt-3 border-top">
        <button type="submit" class="btn-submit-form">
            <i class="fa-solid fa-paper-plane"></i>
            Kirim Penilaian
        </button>
    </div>
</form>

{{-- ===== SCRIPTS (LOGIC UTUH) ===== --}}
<script>
    function updateRangeValue(rangeInput) {
        const valueDisplay = document.getElementById('val_' + rangeInput.id);
        if (valueDisplay) {
            valueDisplay.textContent = rangeInput.value;
            let val = parseInt(rangeInput.value);
            if (val < 30) {
                valueDisplay.style.color = "#ef4444";
                valueDisplay.style.background = "rgba(239, 68, 68, .1)";
            } else if (val < 70) {
                valueDisplay.style.color = "#f59e0b";
                valueDisplay.style.background = "rgba(245, 158, 11, .1)";
            } else {
                valueDisplay.style.color = "#10b981";
                valueDisplay.style.background = "rgba(16, 185, 129, .1)";
            }
        }
    }

    function validateForm(form) {
        let isValid = true;
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Radio Groups
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
                const firstRadio = radios[0];
                firstRadio?.scrollIntoView({
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

        // Checkbox Groups
        const checkboxGroups = {};
        form.querySelectorAll('input[type="checkbox"].checkbox-group[data-group], input[type="checkbox"][data-group]')
            .forEach(cb => {
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
                const firstCb = checkboxes[0];
                firstCb?.scrollIntoView({
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

        // Regular inputs
        form.querySelectorAll('input[data-required], select[data-required], textarea[data-required]').forEach(el => {
            if (el.type === 'radio' || el.type === 'checkbox') return;
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

        // Range
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
        const forms = document.querySelectorAll('.styled-form');
        forms.forEach(form => {
            const submitHandler = function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                if (!submitBtn) return;

                const isValid = validateForm(form);
                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Harap lengkapi semua field yang wajib diisi.',
                        confirmButtonColor: '#ef4444'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Penilaian';
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
                form.submit();
            };

            form.removeEventListener('submit', submitHandler);
            form.addEventListener('submit', submitHandler);
        });

        // Bind range events
        document.querySelectorAll('.tab-pane.active').forEach(p => {
            if (typeof bindRangeEvents === 'function') bindRangeEvents(p);
        });

        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', e => {
                const pane = document.querySelector(e.target.getAttribute('data-bs-target'));
                if (pane && typeof bindRangeEvents === 'function') bindRangeEvents(pane);
            });
        });
    });
</script>
