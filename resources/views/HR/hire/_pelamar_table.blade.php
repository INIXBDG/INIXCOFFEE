<table class="table table-applicant align-middle">
    <thead>
        <tr>
            <th style="width:40px;"><input type="checkbox" class="form-check-input" id="checkAll"></th>
            <th>Pelamar</th>
            <th>Posisi Dilamar</th>
            <th>Sumber</th>
            <th>Tanggal Melamar</th>
            <th>Penilaian</th>
            <th>Tahapan</th>
            <th class="text-center" style="width:140px;">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pelamars as $index => $p)
            @php
                $avClass = 'av-' . (($index % 6) + 1);
                $penilaians = \App\Models\PelamarFolder::where('pelamar_id', $p->id)->whereNotNull('rating')->get();
                $avgRating = $penilaians->avg('rating');
                $avgRating = $avgRating ? round($avgRating, 1) : null;
                $totalPenilai = $penilaians->count();
                $tahap = $p->tahap_rekrutmen;
            @endphp
            <tr draggable="true" data-id="{{ $p->id }}" data-nama="{{ $p->nama_lengkap }}"
                data-email="{{ $p->email }}" data-tahap="{{ $tahap }}" data-jabatan="{{ $p->jabatan }}"
                data-divisi="{{ $p->divisi }}" data-cv="{{ $p->cv_path ? asset('storage/' . $p->cv_path) : '' }}">

                <td><input type="checkbox" class="form-check-input check-row" value="{{ $p->id }}"></td>

                <td>
                    <div class="applicant-info">
                        <div class="applicant-avatar {{ $avClass }}">{{ $p->inisial }}</div>
                        <div>
                            <div class="applicant-name">{{ $p->nama_lengkap }}</div>
                            <div class="applicant-email">{{ $p->email }}</div>
                        </div>
                    </div>
                </td>

                <td>
                    <span class="position-tag">{{ $p->jabatan }}</span>
                    <div class="position-dept">{{ $p->divisi }}</div>
                </td>

                <td>
                    @php
                        $sumberIcon = match (strtolower($p->sumber_lamaran ?? '')) {
                            'linkedin' => ['icon' => 'fa-brands fa-linkedin-in', 'class' => 'linkedin'],
                            'jobstreet' => ['icon' => 'fa-solid fa-briefcase', 'class' => 'jobstreet'],
                            'website perusahaan' => ['icon' => 'fa-solid fa-globe', 'class' => 'website'],
                            'referral karyawan' => ['icon' => 'fa-solid fa-user-group', 'class' => 'referral'],
                            'glints' => ['icon' => 'fa-solid fa-graduation-cap', 'class' => 'glints'],
                            'kalibrr' => ['icon' => 'fa-solid fa-star', 'class' => 'kalibrr'],
                            default => ['icon' => 'fa-solid fa-circle-question', 'class' => 'other'],
                        };
                    @endphp
                    <span class="source-badge {{ $sumberIcon['class'] }}">
                        <i class="{{ $sumberIcon['icon'] }}"></i>
                        {{ $p->sumber_lamaran ?? 'Lainnya' }}
                    </span>
                </td>

                <td>{{ $p->tanggal_melamar?->format('d M Y') ?? '-' }}</td>

                <td>
                    @if ($avgRating)
                        @php
                            $fullStars = floor($avgRating);
                            $emptyStars = 4 - $fullStars;
                        @endphp
                        <div class="table-rating">
                            @for ($i = 0; $i < $fullStars; $i++)
                                <i class="fa-solid fa-star"></i>
                            @endfor
                            @for ($i = 0; $i < $emptyStars; $i++)
                                <i class="fa-solid fa-star star-empty"></i>
                            @endfor
                        </div>
                        <div class="table-rating-info">
                            {{ $avgRating }} · {{ $totalPenilai }} interviewer
                        </div>
                    @else
                        <span class="table-rating-empty">Belum dinilai</span>
                    @endif
                </td>

                <td>
                    <span class="stage-badge stage-{{ $tahap }}">
                        {{ $p->tahap_label }}
                    </span>
                </td>

                <td>
                    <div class="action-cell">
                        <button class="btn-quick-action btn-lihat-profil" title="Lihat Profil"
                            data-id="{{ $p->id }}">
                            <i class="fa-solid fa-eye text-primary"></i>
                        </button>

                        <div class="dropdown">
                            <button class="btn-action-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu">

                                @if ($p->cv_path)
                                    <li>
                                        <a class="dropdown-item btn-lihat-cv" data-id="{{ $p->id }}"
                                            data-nama="{{ $p->nama_lengkap }}"
                                            data-cv="{{ asset('storage/' . $p->cv_path) }}">
                                            <i class="fa-solid fa-file-pdf text-danger"></i>
                                            Lihat CV
                                        </a>
                                    </li>
                                @endif

                                {{-- <li>
                                    <a class="dropdown-item btn-nilai-pelamar" data-id="{{ $p->id }}"
                                        data-nama="{{ $p->nama_lengkap }}">
                                        <i class="fa-solid fa-star text-warning"></i>
                                        Beri Penilaian
                                    </a>
                                </li> --}}

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                @if (in_array($tahap, ['applied', 'screening']))
                                    <li>
                                        <a class="dropdown-item text-info btn-lanjut-tahap"
                                            data-id="{{ $p->id }}" data-nama="{{ $p->nama_lengkap }}"
                                            data-tahap-saat-ini="{{ $p->tahap_label }}"
                                            data-tahap-berikutnya="{{ $p->tahapBerikutnya() }}">
                                            <i class="fa-solid fa-arrow-right"></i>
                                            Lanjut Tahapan
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger btn-tolak" data-id="{{ $p->id }}"
                                            data-nama="{{ $p->nama_lengkap }}">
                                            <i class="fa-solid fa-xmark"></i>
                                            Tolak Lamaran
                                        </a>
                                    </li>
                                @elseif($tahap === 'interview')
                                    <li>
                                        <a class="dropdown-item text-warning btn-jadwal-interview"
                                            data-id="{{ $p->id }}" data-nama="{{ $p->nama_lengkap }}">
                                            <i class="fa-regular fa-calendar"></i>
                                            Jadwalkan Interview
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-success btn-lanjut-tahap"
                                            data-id="{{ $p->id }}" data-nama="{{ $p->nama_lengkap }}"
                                            data-tahap-saat-ini="{{ $p->tahap_label }}" data-tahap-berikutnya="interview">
                                            <i class="fa-solid fa-arrow-right"></i>
                                            Lanjutkan
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger btn-tolak" data-id="{{ $p->id }}"
                                            data-nama="{{ $p->nama_lengkap }}">
                                            <i class="fa-solid fa-xmark"></i>
                                            Tolak Lamaran
                                        </a>
                                    </li>
                                @elseif($tahap === 'offer')
                                    <li>
                                        <a class="dropdown-item text-info btn-kirim-offer"
                                            data-id="{{ $p->id }}" data-nama="{{ $p->nama_lengkap }}"
                                            data-jabatan="{{ $p->jabatan }}">
                                            <i class="fa-solid fa-file-invoice"></i>
                                            Kirim Offer
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-success btn-lanjut-tahap"
                                            data-id="{{ $p->id }}" data-nama="{{ $p->nama_lengkap }}"
                                            data-tahap-saat-ini="{{ $p->tahap_label }}" data-tahap-berikutnya="hired">
                                            <i class="fa-solid fa-check"></i>
                                            Diterima (Hired)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger btn-tolak" data-id="{{ $p->id }}"
                                            data-nama="{{ $p->nama_lengkap }}">
                                            <i class="fa-solid fa-xmark"></i>
                                            Tolak Lamaran
                                        </a>
                                    </li>
                                @elseif($tahap === 'hired')
                                    <li>
                                        <a class="dropdown-item text-success btn-onboarding"
                                            data-id="{{ $p->id }}" data-nama="{{ $p->nama_lengkap }}"
                                            data-divisi="{{ $p->divisi }}" data-jabatan="{{ $p->jabatan }}"
                                            data-mulai="{{ $p->tanggal_mulai_kerja?->format('Y-m-d') }}">
                                            <i class="fa-solid fa-user-plus"></i>
                                            Proses Onboarding
                                        </a>
                                    </li>
                                @elseif($tahap === 'rejected')
                                    <li>
                                        <a class="dropdown-item btn-kirim-email" data-id="{{ $p->id }}"
                                            data-nama="{{ $p->nama_lengkap }}" data-email="{{ $p->email }}">
                                            <i class="fa-regular fa-envelope"></i>
                                            Kirim Email
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger btn-hapus" data-id="{{ $p->id }}"
                                            data-nama="{{ $p->nama_lengkap }}">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus Data
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                    Belum ada pelamar
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
