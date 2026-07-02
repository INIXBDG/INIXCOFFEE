@php
    $isPrivate = $isPrivate ?? false;
@endphp

@if ($isPrivate)
    {{-- ========================================= --}}
    {{-- JOB PROFILE PRIBADI (Qualification, Description, Compensation) --}}
    {{-- ========================================= --}}

    @if (!empty($data->qualifications) && is_array($data->qualifications) && count($data->qualifications) > 0)
        <div class="profile-section">
            <div class="profile-section-title"><i class="fa-solid fa-graduation-cap" style="color:var(--pri)"></i>Qualification</div>
            <ul class="profile-list">
                @foreach ($data->qualifications as $qual)
                    <li>{{ $qual }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="profile-section">
            <div class="profile-section-title"><i class="fa-solid fa-graduation-cap" style="color:var(--pri)"></i>Qualification</div>
            <div class="empty-text">Belum diisi</div>
        </div>
    @endif

    @if (!empty($data->descriptions) && is_array($data->descriptions) && count($data->descriptions) > 0)
        <div class="profile-section">
            <div class="profile-section-title"><i class="fa-solid fa-list-ul" style="color:var(--success)"></i>Job Description</div>
            <ul class="profile-list">
                @foreach ($data->descriptions as $desc)
                    <li>{{ $desc }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="profile-section">
            <div class="profile-section-title"><i class="fa-solid fa-list-ul" style="color:var(--success)"></i>Job Description</div>
            <div class="empty-text">Belum diisi</div>
        </div>
    @endif

    @if (!empty($data->compensation_benefit) && is_array($data->compensation_benefit) && count($data->compensation_benefit) > 0)
        <div class="profile-section">
            <div class="profile-section-title"><i class="fa-solid fa-coins" style="color:var(--warning)"></i>Compensation & Benefit</div>
            <ul class="profile-list">
                @foreach ($data->compensation_benefit as $benefit)
                    <li>{{ $benefit }}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="profile-section">
            <div class="profile-section-title"><i class="fa-solid fa-coins" style="color:var(--warning)"></i>Compensation & Benefit</div>
            <div class="empty-text">Belum diisi</div>
        </div>
    @endif
@else
    {{-- ========================================= --}}
    {{-- JOB DESK (Struktur Lama - Tidak Diubah)   --}}
    {{-- ========================================= --}}

    <div class="profile-section">
        <div class="profile-section-title"><i class="fa-solid fa-bullseye" style="color:var(--pri)"></i>Fungsi Utama</div>
        <p style="color:var(--gray-700);line-height:1.7;font-size:.875rem;margin-bottom:0">
            {{ $data->fungsi_utama ?: 'Belum diisi' }}
        </p>
    </div>

    <div class="profile-section">
        <div class="profile-section-title"><i class="fa-solid fa-user-tie" style="color:var(--info)"></i>Spesifikasi</div>
        <div class="row g-2">
            @php
                $specs = [
                    ['label' => 'Tujuan', 'value' => $data->tujuan_jabatan],
                    ['label' => 'Pendidikan', 'value' => $data->kualifikasi_pendidikan],
                    ['label' => 'Pengalaman', 'value' => $data->pengalaman_kerja],
                    ['label' => 'Karakteristik', 'value' => $data->karakteristik_pribadi],
                ];
            @endphp
            @foreach ($specs as $spec)
                <div class="col-md-6">
                    <div class="info-card-mini">
                        <div class="label">{{ $spec['label'] }}</div>
                        <div class="value" style="font-size:.85rem">{{ $spec['value'] ?: '-' }}</div>
                    </div>
                </div>
            @endforeach
            <div class="col-12">
                <div class="info-card-mini">
                    <div class="label">Kompetensi</div>
                    <div class="value mt-1">
                        @if (!empty($data->kompetensi) && is_array($data->kompetensi) && count($data->kompetensi) > 0)
                            @foreach ($data->kompetensi as $k)
                                <span class="kompetensi-tag">{{ $k }}</span>
                            @endforeach
                        @else
                            <span class="empty-text">-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $hierarchicalSections = [
            ['title' => 'Tugas dan Tanggung Jawab', 'data' => $data->tugas_tanggung_jawab ?? [], 'icon' => 'fa-list-check', 'color' => 'var(--pri)'],
            ['title' => 'Wewenang', 'data' => $data->wewenang ?? [], 'icon' => 'fa-gavel', 'color' => 'var(--success)'],
            ['title' => 'Standard Operating Procedure (SOP)', 'data' => $data->sop ?? [], 'icon' => 'fa-file-contract', 'color' => 'var(--info)'],
        ];
    @endphp

    @foreach ($hierarchicalSections as $section)
        <div class="profile-section">
            <div class="profile-section-title"><i class="fa-solid {{ $section['icon'] }}" style="color:{{ $section['color'] }}"></i>{{ $section['title'] }}</div>

            @if (!empty($section['data']) && is_array($section['data']))
                @foreach ($section['data'] as $index => $item)
                    <div class="detail-block">
                        <div class="detail-block-title">{{ $index + 1 }}. {{ $item['name'] ?? 'Tidak bernama' }}</div>
                        @if (!empty($item['details']) && is_array($item['details']))
                            <ul>
                                @foreach ($item['details'] as $detail)
                                    <li>{{ $detail }}</li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-text" style="margin-left:18px">Tidak ada detail</div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="empty-text">Belum ada data</div>
            @endif
        </div>
    @endforeach
@endif