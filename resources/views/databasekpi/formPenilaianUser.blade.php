<style>
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

<form method="POST" action="{{ route('penilaianEvaluator') }}" class="mb-2 styled-form" novalidate>
    @csrf
    <input type="hidden" name="kode_form" value="{{ $data['kode_form_global'] }}">
    <input type="hidden" name="id_evaluated" value="{{ $data['id_karyawan'] }}">
    <input type="hidden" name="jenis_penilaian" value="{{ $data['jenis_penilaian'] }}">
    <input type="hidden" name="quartal" value="{{ $data['quartal'] }}">
    <input type="hidden" name="tahun" value="{{ $data['tahun'] }}">
    <h5 class="text-center mb-4">
        PENILAIAN KINERJA {{ strtoupper($data['evaluated']) }}<br>
        <small>Penilaian {{ $data['jenis_penilaian'] }}</small>
    </h5>
    @foreach ($data['detail_kategori'] as $index => $kategori)
    <div class="card-kriteria">
        <h5 class="mb-3">{{ $kategori['kriteria_utama'] }}</h5>
        @foreach ($kategori['isi_kriteria'] as $i => $sub)
        @php
        $fieldKey = "field_{$index}_{$i}";
        $fieldName = "{$fieldKey}[{$sub['sub_kriteria_judul']}]";
        $fieldId = Str::slug($sub['sub_kriteria_judul']) . "_{$index}_{$i}";
        @endphp
        <div class="row mb-3 ms-3">
            <label id="label_{{ $fieldId }}" class="col-md-4 col-form-label text-md-start">
                @if ($sub['level'] === 'required')<span style="color:red;">*</span>@endif
                {{ $sub['sub_kriteria_judul'] }} :
            </label>
            <div class="col-md-6">
                @if ($sub['tipe_kategori'] === 'radio')
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($sub['keterangan_tipe'] as $j => $option)
                    @php $id = "radio_{$fieldId}_{$j}"; @endphp
                    <div>
                        <input type="radio" class="btn-check"
                            name="{{ $fieldName }}"
                            id="{{ $id }}"
                            value="{{ $option['nilai'] ?? $option['ket'] }}"
                            data-group="{{ $fieldId }}"
                            @if($sub['level']==='required' ) data-required="true" @endif>
                        <label class="btn btn-outline-secondary rounded-pill" for="{{ $id }}">
                            {{ $option['ket'] }}
                        </label>
                    </div>
                    @endforeach
                </div>
                @elseif ($sub['tipe_kategori'] === 'checkbox')
                <div class="d-flex flex-wrap gap-3">
                    @foreach ($sub['keterangan_tipe'] as $j => $option)
                    @php $id = "checkbox_{$fieldId}_{$j}"; @endphp
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input checkbox-group"
                            name="{{ $fieldName }}[]"
                            id="{{ $id }}"
                            value="{{ $option['nilai'] ?? $option['ket'] }}"
                            data-group="{{ $fieldId }}"
                            @if($sub['level']==='required' ) data-required="true" @endif>
                        <label class="form-check-label" for="{{ $id }}">{{ $option['ket'] }}</label>
                    </div>
                    @endforeach
                </div>
                @elseif ($sub['tipe_kategori'] === 'select')
                <select class="form-select"
                    name="{{ $fieldName }}"
                    id="select_{{ $fieldId }}"
                    @if($sub['level']==='required' ) data-required="true" @endif>
                    <option selected disabled>Pilih {{ $sub['sub_kriteria_judul'] }}</option>
                    @foreach ($sub['keterangan_tipe'] as $option)
                    <option value="{{ $option['nilai'] ?? $option['ket'] }}">{{ $option['ket'] }}</option>
                    @endforeach
                </select>
                @elseif ($sub['tipe_kategori'] === 'textarea')
                <textarea name="pesan_field_{{ $fieldKey }}[{{ $sub['sub_kriteria_judul'] }}]"
                    class="form-control"
                    rows="8"
                    id="textarea_{{ $fieldId }}"
                    @if($sub['level']==='required' ) data-required="true" @endif></textarea>
                @elseif ($sub['tipe_kategori'] === 'range')
                <div class="d-flex align-items-center gap-3">
                    <input type="range"
                        class="form-range"
                        name="{{ $fieldName }}"
                        id="range_{{ $fieldId }}"
                        min="0" max="100" value="0"
                        @if($sub['level']==='required' ) data-required="true" @endif
                        oninput="updateRangeValue(this)">
                    <span class="range-value fw-bold" id="val_range_{{ $fieldId }}">0</span>
                </div>
                @elseif ($sub['tipe_kategori'] === 'text')
                <div class="row g-2">
                    <div class="col-md-8">
                        <input type="text"
                            name="teks_field_{{ $fieldKey }}[{{ $sub['sub_kriteria_judul'] }}]"
                            id="input_{{ $fieldId }}"
                            class="form-control"
                            placeholder="Masukkan {{ $sub['sub_kriteria_judul'] }}"
                            @if($sub['level']==='required' ) data-required="true" @endif>
                    </div>
                    <div class="col-md-4">
                        <input type="number"
                            name="nilai_field_{{ $fieldKey }}[{{ $sub['sub_kriteria_judul'] }}]"
                            id="nilai_{{ $fieldId }}"
                            class="form-control"
                            placeholder="Nilai"
                            min="0"
                            max="100"
                            step="1"
                            @if($sub['level']==='required' ) data-required="true" @endif>
                    </div>
                </div>
                @else
                <input type="{{ $sub['tipe_kategori'] }}"
                    name="{{ $fieldName }}"
                    id="input_{{ $fieldId }}"
                    class="form-control"
                    placeholder="Masukkan {{ $sub['sub_kriteria_judul'] }}"
                    autocomplete="off"
                    @if($sub['level']==='required' ) data-required="true" @endif>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
    <div class="text-end me-5 mt-4">
        <button type="submit" class="btn btn-primary">Kirim</button>
    </div>
</form>
{{-- Letakkan ini DI LUAR loop, hanya sekali --}}
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

    function validateForm(form) {
        let isValid = true;
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Radio
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
                const firstLabel = document.querySelector(`label[for="${firstRadio.id}"]`);
                (firstLabel || firstRadio)?.scrollIntoView({
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

        // Checkbox
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
                const firstCb = checkboxes[0];
                const firstLabel = document.querySelector(`label[for="${firstCb.id}"]`);
                (firstLabel || firstCb)?.scrollIntoView({
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

        // Input biasa
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

    // Hanya bind sekali
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.styled-form');
        if (!form) return;

        // Hapus listener lama jika ada (opsional, tapi aman)
        const submitHandler = function(e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn) return;

            const isValid = validateForm(form);
            if (!isValid) {
                alert('Harap lengkapi semua field yang wajib diisi.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Kirim';
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim...';
            form.submit();
        };

        // Pastikan hanya ada satu listener
        form.removeEventListener('submit', submitHandler);
        form.addEventListener('submit', submitHandler);
    });
</script>