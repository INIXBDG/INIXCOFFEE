@php
    $isPrivate = $isPrivate ?? false;
@endphp

@if ($isPrivate)
    {{-- ========================================= --}}
    {{-- JOB PROFILE PRIBADI (Qualification, Description, Compensation) --}}
    {{-- ========================================= --}}

    @if (!empty($data->qualifications) && is_array($data->qualifications) && count($data->qualifications) > 0)
        <div class="mb-4">
            <h6 class="fw-bold mb-3" style="color: #333; font-size: 0.95rem;">Qualification</h6>
            <ul class="list-unstyled mb-0">
                @foreach ($data->qualifications as $index => $qual)
                    <li
                        style="padding: 6px 0 6px 20px; position: relative; color: #444; font-size: 0.9rem; line-height: 1.6;">
                        <span style="position: absolute; left: 0; color: #999;">{{ $index + 1 }}.</span>
                        {{ $qual }}
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="mb-4">
            <h6 class="fw-bold mb-2" style="color: #333; font-size: 0.95rem;">Qualification</h6>
            <p class="text-muted mb-0" style="font-size: 0.85rem; font-style: italic;">Belum diisi</p>
        </div>
    @endif

    @if (!empty($data->descriptions) && is_array($data->descriptions) && count($data->descriptions) > 0)
        <div class="mb-4">
            <h6 class="fw-bold mb-3" style="color: #333; font-size: 0.95rem;">Job Description</h6>
            <ul class="list-unstyled mb-0">
                @foreach ($data->descriptions as $index => $desc)
                    <li
                        style="padding: 6px 0 6px 20px; position: relative; color: #444; font-size: 0.9rem; line-height: 1.6;">
                        <span style="position: absolute; left: 0; color: #999;">{{ $index + 1 }}.</span>
                        {{ $desc }}
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="mb-4">
            <h6 class="fw-bold mb-2" style="color: #333; font-size: 0.95rem;">Job Description</h6>
            <p class="text-muted mb-0" style="font-size: 0.85rem; font-style: italic;">Belum diisi</p>
        </div>
    @endif

    @if (
        !empty($data->compensation_benefit) &&
            is_array($data->compensation_benefit) &&
            count($data->compensation_benefit) > 0)
        <div class="mb-0">
            <h6 class="fw-bold mb-3" style="color: #333; font-size: 0.95rem;">Compensation & Benefit</h6>
            <ul class="list-unstyled mb-0">
                @foreach ($data->compensation_benefit as $index => $benefit)
                    <li
                        style="padding: 6px 0 6px 20px; position: relative; color: #444; font-size: 0.9rem; line-height: 1.6;">
                        <span style="position: absolute; left: 0; color: #999;">{{ $index + 1 }}.</span>
                        {{ $benefit }}
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="mb-0">
            <h6 class="fw-bold mb-2" style="color: #333; font-size: 0.95rem;">Compensation & Benefit</h6>
            <p class="text-muted mb-0" style="font-size: 0.85rem; font-style: italic;">Belum diisi</p>
        </div>
    @endif
@else
    {{-- ========================================= --}}
    {{-- JOB DESK (Struktur Lama - Tidak Diubah)   --}}
    {{-- ========================================= --}}

    <div class="mb-4">
        <h6 class="fw-bold mb-2" style="color: #333; font-size: 0.95rem;">Fungsi Utama</h6>
        <p class="mb-0" style="color: #444; line-height: 1.7; font-size: 0.9rem;">
            {{ $data->fungsi_utama ?: 'Belum diisi' }}
        </p>
    </div>

    <div class="mb-4">
        <h6 class="fw-bold mb-3" style="color: #333; font-size: 0.95rem;">Spesifikasi</h6>

        @php
            $specs = [
                ['label' => 'Tujuan', 'value' => $data->tujuan_jabatan],
                ['label' => 'Pendidikan', 'value' => $data->kualifikasi_pendidikan],
                ['label' => 'Pengalaman', 'value' => $data->pengalaman_kerja],
                ['label' => 'Karakteristik', 'value' => $data->karakteristik_pribadi],
            ];
        @endphp

        <div class="row g-3">
            @foreach ($specs as $spec)
                <div class="col-md-6">
                    <div
                        style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                        {{ $spec['label'] }}
                    </div>
                    <div style="color: #333; font-size: 0.9rem; font-weight: 500;">
                        {{ $spec['value'] ?: '-' }}
                    </div>
                </div>
            @endforeach

            <div class="col-12">
                <div
                    style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    Kompetensi</div>
                @if (!empty($data->kompetensi) && is_array($data->kompetensi) && count($data->kompetensi) > 0)
                    <div>
                        @foreach ($data->kompetensi as $k)
                            <span
                                style="display: inline-block; background: #f5f5f5; color: #333; padding: 4px 12px; border-radius: 3px; font-size: 0.8rem; margin-right: 6px; margin-bottom: 6px; border: 1px solid #e0e0e0;">
                                {{ $k }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <span style="color: #999; font-size: 0.85rem;">-</span>
                @endif
            </div>
        </div>
    </div>

    @php
        $hierarchicalSections = [
            ['title' => 'Tugas dan Tanggung Jawab', 'data' => $data->tugas_tanggung_jawab ?? []],
            ['title' => 'Wewenang', 'data' => $data->wewenang ?? []],
            ['title' => 'Standard Operating Procedure (SOP)', 'data' => $data->sop ?? []],
        ];
    @endphp

    @foreach ($hierarchicalSections as $section)
        <div class="mb-4">
            <h6 class="fw-bold mb-3" style="color: #333; font-size: 0.95rem;">{{ $section['title'] }}</h6>

            @if (!empty($section['data']) && is_array($section['data']))
                @foreach ($section['data'] as $index => $item)
                    <div
                        style="background: #f9f9f9; border-left: 3px solid #333; padding: 12px 16px; margin-bottom: 8px; border-radius: 4px;">
                        <div style="font-weight: 600; color: #222; font-size: 0.9rem; margin-bottom: 8px;">
                            {{ $index + 1 }}. {{ $item['name'] ?? 'Tidak bernama' }}
                        </div>

                        @if (!empty($item['details']) && is_array($item['details']))
                            <ul
                                style="margin-left: 20px; padding-left: 12px; border-left: 1px dashed #ccc; margin-bottom: 0;">
                                @foreach ($item['details'] as $detail)
                                    <li style="color: #555; padding: 3px 0; line-height: 1.6; font-size: 0.85rem;">
                                        {{ $detail }}</li>
                                @endforeach
                            </ul>
                        @else
                            <div style="color: #999; font-size: 0.8rem; font-style: italic; margin-left: 20px;">Tidak
                                ada detail</div>
                        @endif
                    </div>
                @endforeach
            @else
                <p class="text-muted mb-0" style="font-size: 0.85rem; font-style: italic;">Belum ada data</p>
            @endif
        </div>
    @endforeach
@endif
