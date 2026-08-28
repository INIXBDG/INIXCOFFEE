@extends('layout_HR.app')

@section('content_HR')
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('HR.reports.index') }}">Report Generator</a></li>
                <li class="breadcrumb-item active">{{ $template->name }}</li>
            </ol>
        </nav>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($placeholders->filter(fn($p) => !$p->is_manual && empty($p->source_column))->isNotEmpty())
            <div class="alert alert-warning">
                <strong>Peringatan:</strong> Beberapa field otomatis tidak memiliki mapping ke kolom database.
                Silakan <a href="{{ route('HR.reports.edit', $template) }}" class="alert-link">edit template</a> untuk
                memperbaikinya.
            </div>
        @endif

        <div class="row g-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Generate Laporan: {{ $template->name }}</h5>

                        <form action="{{ route('HR.reports.generate', $template) }}" method="POST" id="generateForm">
                            @csrf
                            <input type="hidden" name="template_id" value="{{ $template->id }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Judul Laporan <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="report_title" class="form-control" required
                                    value="{{ old('report_title', $template->name . ' - ' . date('d/m/Y')) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Data Sumber <span
                                        class="text-danger">*</span></label>
                                <select name="source_id" class="form-select select2-ajax" required>
                                    <option value="">-- Pilih Data --</option>
                                    @foreach ($sourceData as $item)
                                        @php
                                            $itemId = $item->id ?? ($item->nip ?? ($item->kode_karyawan ?? null));
                                            $itemLabel =
                                                $item->nama_lengkap ??
                                                ($item->nama ??
                                                    ($item->nama_lengkap ?? ($item->nama_lengkap ?? 'Item #' . $itemId)));
                                        @endphp
                                        <option value="{{ $itemId }}"
                                            {{ old('source_id') == $itemId ? 'selected' : '' }}>
                                            {{ $itemLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @php
                                $manualFields = $placeholders->filter(fn($p) => $p->is_manual && !in_array($p->field_type, ['loop_manual', 'manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select', 'manual_checkbox']));
                                $autoFields = $placeholders->filter(fn($p) => !$p->is_manual);
                                $manualInputFields = $placeholders->filter(fn($p) => in_array($p->field_type, ['manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select', 'manual_checkbox']));
                            @endphp

                            @if ($autoFields->isNotEmpty())
                                <div class="alert alert-info small mt-3">
                                    <strong>Info:</strong> Field otomatis berikut akan diisi dari database:
                                    <ul class="mb-0 mt-2">
                                        @foreach ($autoFields->take(5) as $field)
                                            <li><code>{{ $field->placeholder_key }}</code> → {{ $field->source_column }}
                                            </li>
                                        @endforeach
                                        @if ($autoFields->count() > 5)
                                            <li>... dan {{ $autoFields->count() - 5 }} field lainnya</li>
                                        @endif
                                    </ul>
                                </div>
                            @endif

                            @if ($manualFields->isNotEmpty())
                                <div class="form-section mt-4 p-3 bg-light rounded border">
                                    <h6 class="fw-semibold mb-3 text-warning">
                                        <span class="iconify me-2" data-icon="mdi:pencil-box"></span>Field Manual
                                    </h6>
                                    @foreach ($manualFields as $field)
                                        <div class="mb-3">
                                            <label
                                                class="form-label small fw-medium">{{ $field->placeholder_label }}</label>
                                            @if ($field->field_type === 'textarea')
                                                <textarea name="manual_inputs[{{ $field->placeholder_key }}]" class="form-control form-control-sm" rows="2">{{ old("manual_inputs.{$field->placeholder_key}", $field->default_value) }}</textarea>
                                            @elseif($field->field_type === 'date')
                                                <input type="date" name="manual_inputs[{{ $field->placeholder_key }}]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old("manual_inputs.{$field->placeholder_key}", $field->default_value) }}">
                                            @elseif($field->field_type === 'select' && $field->options)
                                                <select name="manual_inputs[{{ $field->placeholder_key }}]"
                                                    class="form-select form-select-sm">
                                                    <option value="">-- Pilih --</option>
                                                    @foreach ($field->options as $opt)
                                                        <option value="{{ $opt }}"
                                                            {{ old("manual_inputs.{$field->placeholder_key}") == $opt ? 'selected' : '' }}>
                                                            {{ $opt }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif($field->field_type === 'checkbox')
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                        name="manual_inputs[{{ $field->placeholder_key }}]"
                                                        class="form-check-input" value="1"
                                                        {{ old("manual_inputs.{$field->placeholder_key}", $field->default_value) ? 'checked' : '' }}>
                                                    <label
                                                        class="form-check-label small">{{ $field->placeholder_label }}</label>
                                                </div>
                                            @else
                                                <input type="text" name="manual_inputs[{{ $field->placeholder_key }}]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old("manual_inputs.{$field->placeholder_key}", $field->default_value) }}">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($manualInputFields->isNotEmpty())
                                <div class="card mb-3 mt-4">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <h6 class="mb-0 text-primary">
                                            <span class="iconify me-2" data-icon="mdi:form-textbox"></span>Input Manual
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info small">
                                            <strong>Wajib diisi</strong> sebelum generate laporan.
                                        </div>

                                        @foreach ($manualInputFields as $field)
                                            @php
                                                $config = $field->config ?? [];
                                                $label = $config['label'] ?? $field->placeholder_label;
                                                $defaultValue = old("manual_inputs.{$field->placeholder_key}", $config['default'] ?? $field->default_value ?? '');
                                            @endphp
                                            <div class="mb-3">
                                                <label class="form-label small fw-medium">{{ $label }}</label>
                                                
                                                @if ($field->field_type === 'manual_text')
                                                    <input type="text" 
                                                           name="manual_inputs[{{ $field->placeholder_key }}]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $defaultValue }}"
                                                           placeholder="{{ $config['placeholder'] ?? '' }}"
                                                           {{ ($config['required'] ?? false) ? 'required' : '' }}>
                                                   
                                                @elseif($field->field_type === 'manual_textarea')
                                                    <textarea name="manual_inputs[{{ $field->placeholder_key }}]"
                                                              class="form-control form-control-sm"
                                                              rows="{{ $config['rows'] ?? 3 }}"
                                                              {{ ($config['required'] ?? false) ? 'required' : '' }}>{{ $defaultValue }}</textarea>
                                                   
                                                @elseif($field->field_type === 'manual_date')
                                                    @php
                                                        $dayFormat = $config['day_format'] ?? 'number';
                                                        $monthFormat = $config['month_format'] ?? 'number';
                                                        $yearFormat = $config['year_format'] ?? 'number';
                                                        $separator = $config['separator'] ?? ' ';
                                                        
                                                        $inputType = 'date';
                                                        $helpText = 'Format output: ';
                                                        $formatParts = [];
                                                        
                                                        if ($dayFormat !== 'none') {
                                                            $dayLabels = [
                                                                'number' => 'Angka (25)',
                                                                'word' => 'Kata (Dua Puluh Lima)',
                                                                'word_upper' => 'KATA (DUA PULUH LIMA)',
                                                                'day_name' => 'Nama Hari (Kamis)',
                                                                'day_name_upper' => 'NAMA HARI (KAMIS)',
                                                            ];
                                                            $formatParts[] = $dayLabels[$dayFormat] ?? 'Angka';
                                                        }
                                                        
                                                        if ($monthFormat !== 'none') {
                                                            $monthLabels = [
                                                                'number' => 'Angka (06)',
                                                                'month_name' => 'Nama Bulan (Juni)',
                                                                'month_name_upper' => 'NAMA BULAN (JUNI)',
                                                            ];
                                                            $formatParts[] = $monthLabels[$monthFormat] ?? 'Angka';
                                                        }
                                                        
                                                        if ($yearFormat !== 'none') {
                                                            $yearLabels = [
                                                                'number' => 'Angka (2026)',
                                                                'word' => 'Kata (Dua Ribu Dua Puluh Enam)',
                                                                'word_upper' => 'KATA (DUA RIBU DUA PULUH ENAM)',
                                                            ];
                                                            $formatParts[] = $yearLabels[$yearFormat] ?? 'Angka';
                                                        }
                                                        
                                                        $helpText .= implode($separator, $formatParts);
                                                        
                                                        // Jika hanya bulan+tahun atau tahun saja, bisa pakai input month/year
                                                        if ($dayFormat === 'none' && $monthFormat !== 'none' && $yearFormat !== 'none') {
                                                            $inputType = 'month';
                                                        } elseif ($dayFormat === 'none' && $monthFormat === 'none' && $yearFormat !== 'none') {
                                                            $inputType = 'number';
                                                        }
                                                    @endphp
                                                    
                                                    @if($inputType === 'date')
                                                        <input type="date" 
                                                            name="manual_inputs[{{ $field->placeholder_key }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $defaultValue }}"
                                                            {{ ($config['required'] ?? false) ? 'required' : '' }}>
                                                    @elseif($inputType === 'month')
                                                        <input type="month" 
                                                            name="manual_inputs[{{ $field->placeholder_key }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $defaultValue }}"
                                                            {{ ($config['required'] ?? false) ? 'required' : '' }}>
                                                    @elseif($inputType === 'number')
                                                        <input type="number" 
                                                            name="manual_inputs[{{ $field->placeholder_key }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $defaultValue }}"
                                                            min="1900" max="2100"
                                                            placeholder="Contoh: 2026"
                                                            {{ ($config['required'] ?? false) ? 'required' : '' }}>
                                                    @endif
                                                    
                                                    <small class="text-muted d-block mt-1">
                                                        <span class="iconify me-1" data-icon="mdi:information-outline"></span>
                                                        {{ $helpText }}
                                                    </small>
                                                @elseif($field->field_type === 'manual_number')
                                                    @php
                                                        $numberType = $config['number_type'] ?? 'number';
                                                        $step = $numberType === 'integer' ? '1' : '0.01';
                                                    @endphp
                                                    <input type="number" 
                                                           name="manual_inputs[{{ $field->placeholder_key }}]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $defaultValue }}"
                                                           step="{{ $step }}"
                                                           {{ ($config['required'] ?? false) ? 'required' : '' }}>
                                                    <small class="text-muted">
                                                        @if ($numberType === 'currency')
                                                            Format: Mata Uang (Rp)
                                                        @elseif($numberType === 'integer')
                                                            Format: Angka Bulat
                                                        @else
                                                            Format: Angka Desimal
                                                        @endif
                                                    </small>
                                                   
                                                @elseif($field->field_type === 'manual_select')
                                                    @php
                                                        $options = $config['options'] ?? [];
                                                    @endphp
                                                    @if (!empty($options))
                                                        <select name="manual_inputs[{{ $field->placeholder_key }}]"
                                                                class="form-select form-select-sm"
                                                                {{ ($config['required'] ?? false) ? 'required' : '' }}>
                                                            <option value="">-- Pilih --</option>
                                                            @foreach ($options as $opt)
                                                                <option value="{{ $opt }}"
                                                                    {{ $defaultValue == $opt ? 'selected' : '' }}>
                                                                    {{ $opt }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                   
                                                @elseif($field->field_type === 'manual_checkbox')
                                                    <div class="form-check">
                                                        <input type="hidden" 
                                                               name="manual_inputs[{{ $field->placeholder_key }}]"
                                                               value="0">
                                                        <input type="checkbox" 
                                                               name="manual_inputs[{{ $field->placeholder_key }}]"
                                                               class="form-check-input" 
                                                               value="1"
                                                               {{ $defaultValue ? 'checked' : '' }}>
                                                        <label class="form-check-label small">{{ $label }}</label>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @php
                                $loopManualFields = $placeholders->filter(fn($p) => $p->field_type === 'loop_manual');
                                $loopRelationFields = $placeholders->filter(fn($p) => $p->field_type === 'loop_relation');
                            @endphp

                            @if ($loopManualFields->isNotEmpty())
                                <div class="card mb-3">
                                    <div class="card-header bg-warning bg-opacity-10">
                                        <h6 class="mb-0 text-dark">
                                            <span class="iconify me-2" data-icon="mdi:table"></span>Data Loop Manual
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info small">
                                            <strong>Wajib diisi</strong> sebelum generate. Klik "Tambah Baris" untuk menambah data.
                                        </div>

                                        @foreach ($loopManualFields as $field)
                                            @php
                                                $columns = $field->config['columns'] ?? [];
                                                $loopKey = $field->placeholder_key;
                                            @endphp
                                            <div class="mb-4 p-3 bg-light rounded border">
                                                <label class="form-label fw-semibold">{{ $field->placeholder_label }}</label>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle" data-loop-key="{{ $loopKey }}">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th width="40">#</th>
                                                                @foreach ($columns as $col)
                                                                    <th>{{ $col['label'] ?? $col['key'] }}</th>
                                                                @endforeach
                                                                <th width="60" class="text-center">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="loop-body">
                                                            <tr data-row-index="0">
                                                                <td class="text-center text-muted">1</td>
                                                                @foreach ($columns as $colIdx => $col)
                                                                    <td>
                                                                        <input type="{{ $col['type'] ?? 'text' }}"
                                                                            name="manual_inputs[{{ $loopKey }}][0][{{ $col['key'] }}]"
                                                                            class="form-control form-control-sm"
                                                                            {{ ($col['required'] ?? false) ? 'required' : '' }}
                                                                            placeholder="{{ $col['placeholder'] ?? '' }}">
                                                                    </td>
                                                                @endforeach
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLoopRow(this)">
                                                                        <span class="iconify" data-icon="mdi:delete"></span>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="addLoopRow(this, '{{ $loopKey }}')">
                                                    <span class="iconify me-1" data-icon="mdi:plus"></span>Tambah Baris
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($loopRelationFields->isNotEmpty())
                                <div class="card mb-3">
                                    <div class="card-header bg-info bg-opacity-10">
                                        <h6 class="mb-0">
                                            <span class="iconify me-2" data-icon="mdi:database"></span>Data Loop dari Relasi
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info small">
                                            Data berikut akan otomatis diambil dari relasi database dan di-loop di dokumen.
                                        </div>
                                        @foreach ($loopRelationFields as $field)
                                            @php
                                                $relation = $field->config['relation'] ?? '';
                                                $fields = $field->config['fields'] ?? [];
                                            @endphp
                                            <div class="mb-2 p-2 bg-light rounded">
                                                <small class="text-muted">Field:</small>
                                                <code>{{ $field->placeholder_key }}</code>
                                                <br>
                                                <small class="text-muted">Relasi:</small>
                                                <span class="badge bg-info">{{ $relation }}</span>
                                                @if (!empty($fields))
                                                    <br><small class="text-muted">Kolom:</small>
                                                    @foreach ($fields as $f)
                                                        <span class="badge bg-secondary">{{ $f }}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 pt-3 border-top d-flex gap-2">
                                <a href="{{ route('HR.reports.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary px-4" id="btnGenerate">
                                    <span class="iconify me-2" data-icon="mdi:file-document-check-outline"></span>
                                    <span class="btn-text">Proses & Download Laporan</span>
                                    <span class="spinner-border spinner-border-sm d-none ms-2" id="loadingSpinner"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Informasi Template</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Fields:</span>
                            <span class="fw-bold">{{ $placeholders->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Auto Fields:</span>
                            <span class="fw-bold text-success">{{ $autoFields->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Manual Fields:</span>
                            <span class="fw-bold text-warning">{{ $manualFields->count() + $manualInputFields->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Input Manual:</span>
                            <span class="fw-bold text-primary">{{ $manualInputFields->count() }}</span>
                        </div>
                    </div>
                </div>

                @if ($autoFields->isNotEmpty())
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3 text-primary">Field Otomatis</h6>
                            @foreach ($autoFields as $field)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                    <div>
                                        <small class="text-muted d-block">{{ $field->placeholder_label }}</small>
                                        <span class="fw-medium small text-primary">{{ $field->source_column }}</span>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success">Auto</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($manualInputFields->isNotEmpty())
                    <div class="card">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3 text-info">Field Input Manual</h6>
                            @foreach ($manualInputFields as $field)
                                @php
                                    $config = $field->config ?? [];
                                    $label = $config['label'] ?? $field->placeholder_label;
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                    <div>
                                        <small class="text-muted d-block">{{ $label }}</small>
                                        <span class="fw-medium small text-info">{{ $field->placeholder_key }}</span>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">Manual</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        $(function() {
            if ($.fn.select2) {
                $('.select2-ajax').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Cari data...',
                    dropdownParent: $(document.body)
                });
            }

            $('#generateForm').on('submit', function(e) {
                const sourceId = $('[name="source_id"]').val();
                if (!sourceId) {
                    e.preventDefault();
                    alert('Silakan pilih data sumber terlebih dahulu!');
                    return false;
                }

                const btn = $('#btnGenerate');
                btn.prop('disabled', true);
                btn.find('.btn-text').addClass('d-none');
                btn.find('#loadingSpinner').removeClass('d-none');
            });
        });

        window.addLoopRow = function(btn, loopKey) {
            const table = btn.closest('.mb-4').querySelector('table[data-loop-key="' + loopKey + '"]');
            const tbody = table.querySelector('.loop-body');
            const rows = tbody.querySelectorAll('tr');
            const newIndex = rows.length;

            // Clone baris pertama
            const firstRow = rows[0];
            const newRow = firstRow.cloneNode(true);

            // Update row index & nomor
            newRow.dataset.rowIndex = newIndex;
            newRow.querySelector('td:first-child').textContent = newIndex + 1;

            // Update name input
            newRow.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.name;
                if (name) {
                    input.name = name.replace(/\[\d+\]/, '[' + newIndex + ']');
                    input.value = '';
                }
            });

            tbody.appendChild(newRow);
        };

        window.removeLoopRow = function(btn) {
            const row = btn.closest('tr');
            const tbody = row.closest('tbody');
            const rows = tbody.querySelectorAll('tr');

            if (rows.length <= 1) {
                alert('Minimal harus ada 1 baris!');
                return;
            }

            row.remove();

            // Re-number rows
            tbody.querySelectorAll('tr').forEach((r, idx) => {
                r.dataset.rowIndex = idx;
                r.querySelector('td:first-child').textContent = idx + 1;
                r.querySelectorAll('input, select, textarea').forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/\[\d+\]/, '[' + idx + ']');
                    }
                });
            });
        };
    </script>
@endsection