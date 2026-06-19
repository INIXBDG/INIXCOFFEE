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
                                                    ($item->nama_kegiatan ?? ($item->judul ?? 'Item #' . $itemId)));
                                        @endphp
                                        <option value="{{ $itemId }}"
                                            {{ old('source_id') == $itemId ? 'selected' : '' }}>
                                            {{ $itemLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @php
                                $manualFields = $placeholders->filter(fn($p) => $p->is_manual);
                                $autoFields = $placeholders->filter(fn($p) => !$p->is_manual);
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
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Manual Fields:</span>
                            <span class="fw-bold text-warning">{{ $manualFields->count() }}</span>
                        </div>
                    </div>
                </div>

                @if ($autoFields->isNotEmpty())
                    <div class="card">
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
    </script>
@endsection
