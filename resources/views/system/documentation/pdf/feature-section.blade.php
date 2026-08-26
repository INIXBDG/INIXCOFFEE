<div class="section" style="page-break-before: always;">
    <div class="section-title">SUB FITUR: {{ $feature->name }}</div>
    <div class="section-content">
        <table style="width: 100%; margin-bottom: 15px;">
            <tr>
                <td style="width: 30%; font-weight: bold; color: #0F4C81;">Kategori</td>
                <td>: {{ $feature->category }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #0F4C81;">Status</td>
                <td>: {{ ucfirst($feature->status ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #0F4C81;">Versi Dokumen</td>
                <td>: {{ $feature->document_version }}</td>
            </tr>
            @if ($feature->parentFeature)
                <tr>
                    <td style="font-weight: bold; color: #0F4C81;">Induk Fitur</td>
                    <td>: {{ $feature->parentFeature->name }}</td>
                </tr>
            @endif
        </table>
    </div>
</div>

@if ($feature->short_description)
    <div class="section">
        <div class="section-title">RINGKASAN</div>
        <div class="section-content">{!! $feature->short_description !!}</div>
    </div>
@endif

@if ($feature->purpose)
    <div class="section">
        <div class="section-title">TUJUAN</div>
        <div class="section-content">{!! $feature->purpose !!}</div>
    </div>
@endif

@if ($feature->problem_solved)
    <div class="section">
        <div class="section-title">MASALAH YANG DISELESAIKAN</div>
        <div class="section-content">{!! $feature->problem_solved !!}</div>
    </div>
@endif

@if ($feature->how_it_works)
    <div class="section">
        <div class="section-title">CARA PENGGUNAAN</div>
        <div class="section-content">{!! $feature->how_it_works !!}</div>
    </div>
@endif

@if ($feature->user_access)
    <div class="section">
        <div class="section-title">HAK AKSES PENGGUNA</div>
        <div class="section-content">{!! $feature->user_access !!}</div>
    </div>
@endif

@if ($feature->codeDocumentations && $feature->codeDocumentations->count() > 0)
    <div class="section">
        <div class="section-title">DOKUMENTASI KODE / API</div>
        <div class="section-content">
            <ul>
                @foreach ($feature->codeDocumentations as $code)
                    <li>
                        <strong>{{ $code->name ?? ($code->endpoint ?? 'Endpoint') }}</strong>
                        @if ($code->description ?? false)
                            : {{ $code->description }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif