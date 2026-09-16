<div id="generate-form-wrapper" class="generate-modal-content">

    {{-- Alert Error --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Warning Mapping --}}
    @if ($placeholders->filter(fn($p) => !$p->is_manual && empty($p->source_column))->isNotEmpty())
        <div class="alert alert-warning mx-3 mt-3 mb-0">
            <strong>Peringatan:</strong> Beberapa field otomatis tidak memiliki mapping ke kolom database.
        </div>
    @endif

    <div class="row g-0">
        {{-- ==================== LEFT: FORM ==================== --}}
        <div class="col-lg-8 border-end">
            <div class="p-4">
                <form action="{{ route('HR.reports.generate', $template) }}"
                    method="POST"
                    id="generateForm"
                    data-preview-url="{{ route('HR.reports.preview.generate', $template) }}">
                    @csrf
                    <input type="hidden" name="template_id" value="{{ $template->id }}">

                    {{-- Judul + Sumber Data --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Judul Laporan <span class="text-danger">*</span></label>
                            <input type="text" name="report_title" class="form-control" required
                                value="{{ old('report_title', $template->name) }}"
                                placeholder="Masukkan judul laporan">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Data Sumber <span class="text-danger">*</span></label>
                            <select name="source_id" class="form-select" required>
                                <option value="">-- Pilih Data --</option>
                                @foreach ($sourceData as $item)
                                    @php
                                        $itemId = $item->id ?? ($item->nip ?? ($item->kode_karyawan ?? null));
                                        $itemLabel = $item->nama_lengkap ?? ($item->nama ?? 'Item #' . $itemId);
                                    @endphp
                                    <option value="{{ $itemId }}" {{ old('source_id') == $itemId ? 'selected' : '' }}>
                                        {{ $itemLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @php
                        $manualFields = $placeholders->filter(fn($p) => $p->is_manual && !in_array($p->field_type, [
                            'loop_manual', 'manual_text', 'manual_textarea', 'manual_date',
                            'manual_number', 'manual_select', 'manual_checkbox',
                        ]));
                        $autoFields = $placeholders->filter(fn($p) => !$p->is_manual);
                        $manualInputFields = $placeholders->filter(fn($p) => in_array($p->field_type, [
                            'manual_text', 'manual_textarea', 'manual_date',
                            'manual_number', 'manual_select', 'manual_checkbox',
                        ]));
                        $loopManualFields = $placeholders->filter(fn($p) => $p->field_type === 'loop_manual');
                        $loopRelationFields = $placeholders->filter(fn($p) => $p->field_type === 'loop_relation');
                    @endphp

                    {{-- Auto Fields Info --}}
                    @if ($autoFields->isNotEmpty())
                        <div class="alert alert-soft-info small mb-4">
                            <div class="d-flex align-items-start gap-2">
                                <span class="iconify mt-1" data-icon="mdi:database-check" style="font-size:1.1rem;"></span>
                                <div>
                                    <strong>Field otomatis</strong> akan diisi dari database:
                                    <div class="mt-1">
                                        @foreach ($autoFields->take(6) as $field)
                                            <span class="badge bg-white text-primary border me-1 mb-1">
                                                {{ $field->placeholder_key }}
                                            </span>
                                        @endforeach
                                        @if ($autoFields->count() > 6)
                                            <span class="badge bg-secondary">+{{ $autoFields->count() - 6 }} lainnya</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Manual Input Fields --}}
                    @if ($manualInputFields->isNotEmpty())
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <span class="iconify me-2" data-icon="mdi:form-textbox"></span>
                                Input Manual
                            </div>
                            <div class="section-body">
                                <div class="row g-3">
                                    @foreach ($manualInputFields as $field)
                                        @php
                                            $config = $field->config ?? [];
                                            $label = $config['label'] ?? $field->placeholder_label;
                                            $defaultValue = old("manual_inputs.{$field->placeholder_key}", $config['default'] ?? ($field->default_value ?? ''));
                                        @endphp
                                        <div class="col-md-6">
                                            <label class="form-label small fw-medium">{{ $label }}</label>

                                            @if ($field->field_type === 'manual_text')
                                                <input type="text" name="manual_inputs[{{ $field->placeholder_key }}]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $defaultValue }}"
                                                    placeholder="{{ $config['placeholder'] ?? '' }}"
                                                    {{ ($config['required'] ?? false) ? 'required' : '' }}>

                                            @elseif ($field->field_type === 'manual_textarea')
                                                <textarea name="manual_inputs[{{ $field->placeholder_key }}]"
                                                    class="form-control form-control-sm"
                                                    rows="{{ $config['rows'] ?? 3 }}"
                                                    {{ ($config['required'] ?? false) ? 'required' : '' }}>{{ $defaultValue }}</textarea>

                                            @elseif ($field->field_type === 'manual_date')
                                                <input type="date" name="manual_inputs[{{ $field->placeholder_key }}]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $defaultValue }}"
                                                    {{ ($config['required'] ?? false) ? 'required' : '' }}>

                                            @elseif ($field->field_type === 'manual_number')
                                                @php $step = ($config['number_type'] ?? 'number') === 'integer' ? '1' : '0.01'; @endphp
                                                <input type="number" name="manual_inputs[{{ $field->placeholder_key }}]"
                                                    class="form-control form-control-sm"
                                                    value="{{ $defaultValue }}" step="{{ $step }}"
                                                    {{ ($config['required'] ?? false) ? 'required' : '' }}>

                                            @elseif ($field->field_type === 'manual_select')
                                                @php $options = $config['options'] ?? []; @endphp
                                                <select name="manual_inputs[{{ $field->placeholder_key }}]"
                                                    class="form-select form-select-sm"
                                                    {{ ($config['required'] ?? false) ? 'required' : '' }}>
                                                    <option value="">-- Pilih --</option>
                                                    @foreach ($options as $opt)
                                                        <option value="{{ $opt }}" {{ $defaultValue == $opt ? 'selected' : '' }}>
                                                            {{ $opt }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            @elseif ($field->field_type === 'manual_checkbox')
                                                <div class="form-check mt-1">
                                                    <input type="hidden" name="manual_inputs[{{ $field->placeholder_key }}]" value="0">
                                                    <input type="checkbox" name="manual_inputs[{{ $field->placeholder_key }}]"
                                                        class="form-check-input" value="1"
                                                        {{ $defaultValue ? 'checked' : '' }} id="chk_{{ $field->placeholder_key }}">
                                                    <label class="form-check-label small" for="chk_{{ $field->placeholder_key }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Loop Manual --}}
                    @if ($loopManualFields->isNotEmpty())
                        @foreach ($loopManualFields as $field)
                            @php
                                $columns = $field->config['columns'] ?? [];
                                $loopKey = $field->placeholder_key;
                            @endphp
                            <div class="section-card mb-4">
                                <div class="section-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="iconify me-2" data-icon="mdi:table"></span>
                                        {{ $field->placeholder_label }}
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addLoopRow(this, '{{ $loopKey }}')">
                                        <span class="iconify me-1" data-icon="mdi:plus"></span> Tambah Baris
                                    </button>
                                </div>
                                <div class="section-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle" data-loop-key="{{ $loopKey }}">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40" class="text-center">#</th>
                                                    @foreach ($columns as $col)
                                                        <th>{{ $col['label'] ?? $col['key'] }}</th>
                                                    @endforeach
                                                    <th width="50" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="loop-body">
                                                <tr data-row-index="0">
                                                    <td class="text-center text-muted small">1</td>
                                                    @foreach ($columns as $col)
                                                        <td>
                                                            <input type="{{ $col['type'] ?? 'text' }}"
                                                                name="manual_inputs[{{ $loopKey }}][0][{{ $col['key'] }}]"
                                                                class="form-control form-control-sm"
                                                                placeholder="{{ $col['placeholder'] ?? '' }}">
                                                        </td>
                                                    @endforeach
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                                            onclick="removeLoopRow(this)">
                                                            <span class="iconify" data-icon="mdi:delete"></span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Loop Relation Info --}}
                    @if ($loopRelationFields->isNotEmpty())
                        <div class="section-card mb-4">
                            <div class="section-header">
                                <span class="iconify me-2" data-icon="mdi:database"></span>
                                Data Loop dari Relasi
                            </div>
                            <div class="section-body">
                                <p class="small text-muted mb-3">Data berikut akan otomatis diambil dari database.</p>
                                @foreach ($loopRelationFields as $field)
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <code class="small">{{ $field->placeholder_key }}</code>
                                        <span class="badge bg-info-subtle text-info">{{ $field->config['relation'] ?? '-' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2 pt-3 border-top mt-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="btnPreview" onclick="doPreview()">
                            <span class="iconify me-1" data-icon="mdi:eye-outline"></span>
                            <span id="previewBtnText">Preview</span>
                            <span class="spinner-border spinner-border-sm d-none ms-1" id="previewSpinner"></span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-auto" id="btnGenerate">
                            <span class="iconify me-1" data-icon="mdi:file-document-check-outline"></span>
                            <span class="btn-text">Proses & Download</span>
                            <span class="spinner-border spinner-border-sm d-none ms-1" id="loadingSpinner"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==================== RIGHT: INFO ==================== --}}
        <div class="col-lg-4 bg-light">
            <div class="p-4">
                <h6 class="fw-semibold mb-3 text-muted text-uppercase small letter-spacing">Informasi Template</h6>

                <div class="info-stat mb-3">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Total Fields</span>
                        <span class="fw-bold">{{ $placeholders->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Auto Fields</span>
                        <span class="fw-bold text-success">{{ $autoFields->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted small">Input Manual</span>
                        <span class="fw-bold text-primary">{{ $manualInputFields->count() }}</span>
                    </div>
                </div>

                @if ($autoFields->isNotEmpty())
                    <h6 class="fw-semibold mb-2 mt-4 text-muted text-uppercase small">Field Otomatis</h6>
                    <div class="d-flex flex-column gap-2">
                        @foreach ($autoFields->take(8) as $field)
                            <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                                <div class="text-truncate me-2">
                                    <div class="small fw-medium text-truncate">{{ $field->placeholder_label }}</div>
                                    <code class="small text-muted">{{ $field->source_column }}</code>
                                </div>
                                <span class="badge bg-success-subtle text-success">Auto</span>
                            </div>
                        @endforeach
                        @if ($autoFields->count() > 8)
                            <small class="text-muted">+{{ $autoFields->count() - 8 }} field lainnya</small>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .generate-modal-content {
        font-size: 0.925rem;
    }

    .section-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .section-header {
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .section-body {
        padding: 1rem;
    }

    .alert-soft-info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }

    .letter-spacing {
        letter-spacing: 0.04em;
    }

    .info-stat {
        background: #fff;
        border-radius: 8px;
        padding: 0.25rem 0.75rem;
        border: 1px solid #e5e7eb;
    }

    .docx-container-preview {
        background: #e9ecef !important;
        padding: 12px !important;
        min-height: 100%;
        overflow: auto;
    }

    .docx-container-preview .docx-wrapper > section.docx {
        box-shadow: 0 1px 4px rgba(0,0,0,0.12);
        margin-bottom: 12px !important;
        background: white;
        padding: 24px 32px !important;
        transform: scale(0.88);
        transform-origin: top center;
    }
</style>