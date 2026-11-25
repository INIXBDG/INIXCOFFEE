<div id="generate-form-wrapper" class="gf">

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if ($placeholders->filter(fn($p) => !$p->is_manual && empty($p->source_column))->isNotEmpty())
        <div class="alert alert-warning m-3 mb-0 small">
            Beberapa field otomatis belum terhubung ke kolom database.
        </div>
    @endif

    @php
        $manualTypes = [
            'manual_text',
            'manual_textarea',
            'manual_date',
            'manual_number',
            'manual_select',
            'manual_checkbox',
        ];
        $autoFields = $placeholders->filter(fn($p) => !$p->is_manual);
        $manualInputFields = $placeholders->filter(fn($p) => in_array($p->field_type, $manualTypes));
        $loopManualFields = $placeholders->filter(fn($p) => $p->field_type === 'loop_manual');
        $loopRelationFields = $placeholders->filter(fn($p) => $p->field_type === 'loop_relation');
    @endphp

    <div class="row g-0">
        <div class="col-lg-8">
            <div class="p-4">
                <form action="{{ route('HR.reports.generate', $template) }}" method="POST" id="generateForm"
                    data-preview-url="{{ route('HR.reports.preview.generate', $template) }}">
                    @csrf
                    <input type="hidden" name="template_id" value="{{ $template->id }}">

                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Judul laporan <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="report_title" class="form-control" required
                                value="{{ old('report_title', $template->name) }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Data sumber <span
                                    class="text-danger">*</span></label>
                            <select name="source_id" class="form-select" required>
                                <option value="">Pilih data</option>
                                @foreach ($sourceData as $item)
                                    @php
                                        $itemId = $item->id ?? ($item->nip ?? ($item->kode_karyawan ?? null));
                                        $itemLabel = $item->nama_lengkap ?? ($item->nama ?? 'Item #' . $itemId);
                                    @endphp
                                    <option value="{{ $itemId }}"
                                        {{ old('source_id') == $itemId ? 'selected' : '' }}>
                                        {{ $itemLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($manualInputFields->isNotEmpty())
                        <div class="gf-card mb-4">
                            <div class="gf-card-head">Isian manual</div>
                            <div class="gf-card-body">
                                <div class="row g-3">
                                    @foreach ($manualInputFields as $field)
                                        @php
                                            $config = $field->config ?? [];
                                            $label = $config['label'] ?? $field->placeholder_label;
                                            $defaultValue = old(
                                                "manual_inputs.{$field->placeholder_key}",
                                                $config['default'] ?? ($field->default_value ?? ''),
                                            );
                                            $isRequired = $config['required'] ?? false;
                                            $inputName = "manual_inputs[{$field->placeholder_key}]";
                                        @endphp
                                        <div
                                            class="{{ $field->field_type === 'manual_textarea' ? 'col-12' : 'col-md-6' }}">
                                            @if ($field->field_type !== 'manual_checkbox')
                                                <label class="form-label small fw-medium">
                                                    {{ $label }}
                                                    @if ($isRequired)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                            @endif

                                            @if ($field->field_type === 'manual_text')
                                                <input type="text" name="{{ $inputName }}"
                                                    class="form-control form-control-sm" value="{{ $defaultValue }}"
                                                    placeholder="{{ $config['placeholder'] ?? '' }}"
                                                    {{ $isRequired ? 'required' : '' }}>
                                            @elseif ($field->field_type === 'manual_textarea')
                                                <textarea name="{{ $inputName }}" class="form-control form-control-sm" rows="{{ $config['rows'] ?? 3 }}"
                                                    {{ $isRequired ? 'required' : '' }}>{{ $defaultValue }}</textarea>
                                            @elseif ($field->field_type === 'manual_date')
                                                <input type="date" name="{{ $inputName }}"
                                                    class="form-control form-control-sm" value="{{ $defaultValue }}"
                                                    {{ $isRequired ? 'required' : '' }}>
                                            @elseif ($field->field_type === 'manual_number')
                                                @php $step = ($config['number_type'] ?? 'number') === 'integer' ? '1' : '0.01'; @endphp
                                                <input type="number" name="{{ $inputName }}"
                                                    class="form-control form-control-sm" value="{{ $defaultValue }}"
                                                    step="{{ $step }}" {{ $isRequired ? 'required' : '' }}>
                                            @elseif ($field->field_type === 'manual_select')
                                                <select name="{{ $inputName }}" class="form-select form-select-sm"
                                                    {{ $isRequired ? 'required' : '' }}>
                                                    <option value="">Pilih</option>
                                                    @foreach ($config['options'] ?? [] as $opt)
                                                        <option value="{{ $opt }}"
                                                            {{ $defaultValue == $opt ? 'selected' : '' }}>
                                                            {{ $opt }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif ($field->field_type === 'manual_checkbox')
                                                <div class="form-check mt-4">
                                                    <input type="hidden" name="{{ $inputName }}" value="0">
                                                    <input type="checkbox" name="{{ $inputName }}"
                                                        class="form-check-input" value="1"
                                                        {{ $defaultValue ? 'checked' : '' }}
                                                        id="chk_{{ $field->placeholder_key }}">
                                                    <label class="form-check-label small"
                                                        for="chk_{{ $field->placeholder_key }}">
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

                    @foreach ($loopManualFields as $field)
                        @php
                            $columns = $field->config['columns'] ?? [];
                            $loopKey = $field->placeholder_key;
                        @endphp
                        <div class="gf-card section-card mb-4">
                            <div class="gf-card-head d-flex justify-content-between align-items-center">
                                <span>{{ $field->placeholder_label }}</span>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="addLoopRow(this)">
                                    <span class="iconify me-1" data-icon="mdi:plus"></span>Tambah baris
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0 align-middle"
                                    data-loop-key="{{ $loopKey }}">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40" class="text-center">#</th>
                                            @foreach ($columns as $col)
                                                <th>{{ $col['label'] ?? $col['key'] }}</th>
                                            @endforeach
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
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
                                                    onclick="removeLoopRow(this)" title="Hapus baris">
                                                    <span class="iconify" data-icon="mdi:delete-outline"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    @if ($loopRelationFields->isNotEmpty())
                        <div class="gf-card mb-4">
                            <div class="gf-card-head">Data berulang dari relasi</div>
                            <div class="gf-card-body">
                                <p class="small text-muted mb-2">Data berikut diambil otomatis dari database.</p>
                                @foreach ($loopRelationFields as $field)
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <code class="small">{{ $field->placeholder_key }}</code>
                                        <span
                                            class="badge bg-info-subtle text-info">{{ $field->config['relation'] ?? '-' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-outline-primary" id="btnPreview"
                            onclick="doPreview()">
                            <span class="iconify me-1" data-icon="mdi:eye-outline"></span>
                            <span id="previewBtnText">Pratinjau</span>
                            <span class="spinner-border spinner-border-sm d-none ms-1" id="previewSpinner"></span>
                        </button>
                        <button type="submit" class="btn btn-primary ms-auto" id="btnGenerate">
                            <span class="btn-text">
                                <span class="iconify me-1" data-icon="mdi:download-outline"></span>Proses dan unduh
                            </span>
                            <span class="spinner-border spinner-border-sm d-none" id="loadingSpinner"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4 gf-side">
            <div class="p-4">
                <h6 class="gf-side-title">Ringkasan template</h6>

                <div class="gf-stats">
                    <div><span>Total field</span><strong>{{ $placeholders->count() }}</strong></div>
                    <div><span>Otomatis</span><strong class="text-success">{{ $autoFields->count() }}</strong></div>
                    <div><span>Isian manual</span><strong
                            class="text-primary">{{ $manualInputFields->count() }}</strong></div>
                </div>

                @if ($autoFields->isNotEmpty())
                    <h6 class="gf-side-title mt-4">Field otomatis</h6>
                    <div class="d-flex flex-column gap-2">
                        @foreach ($autoFields->take(8) as $field)
                            <div class="gf-auto">
                                <div class="text-truncate">
                                    <div class="small fw-medium text-truncate">{{ $field->placeholder_label }}</div>
                                    <code class="small text-muted">{{ $field->source_column }}</code>
                                </div>
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
    .gf {
        font-size: .9rem;
    }

    .gf-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .gf-card-head {
        padding: .65rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 600;
        font-size: .875rem;
        color: #0f172a;
    }

    .gf-card-body {
        padding: 1rem;
    }

    .gf-side {
        background: #f8fafc;
        border-left: 1px solid #e2e8f0;
    }

    .gf-side-title {
        margin-bottom: .75rem;
        font-size: .8rem;
        font-weight: 600;
        color: #64748b;
    }

    .gf-stats {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: .25rem .85rem;
    }

    .gf-stats div {
        display: flex;
        justify-content: space-between;
        padding: .55rem 0;
        font-size: .85rem;
    }

    .gf-stats div+div {
        border-top: 1px solid #f1f5f9;
    }

    .gf-stats span {
        color: #64748b;
    }

    .gf-auto {
        padding: .5rem .75rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    @media (max-width: 991.98px) {
        .gf-side {
            border-left: 0;
            border-top: 1px solid #e2e8f0;
        }
    }
</style>
