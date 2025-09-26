@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

@section('content')
    <div class="modal fade" id="genericModal" tabindex="-1" role="dialog" aria-labelledby="genericModalLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="genericModalLabel"></h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Konten modal akan diisi secara dinamis di sini -->
                    <p id="modalQuestion"></p>
                    <div class="form-group" id="rejectReasonGroup" style="display:none;">
                        <label for="rejectReason">Keterangan Penolakan (opsional)</label>
                        <textarea class="form-control" id="rejectReason" rows="3" placeholder="Keterangan jika ditolak..."></textarea>
                    </div>
                    <div id="schemework">
                        <div class="mb-3">
                            <label for="jam_masuk" class="form-label">Jam Masuk</label>
                            <input type="time" class="form-control" id="jam_masuk">
                        </div>
                        <div class="mb-3">
                            <label for="jam_pulang" class="form-label">Jam Pulang</label>
                            <input type="time" class="form-control" id="jam_pulang">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <!-- Tombol Setujui dan Tolak -->
                    <button type="button" class="btn btn-danger" id="btnRejectGeneric">Tolak</button>
                    <button type="button" class="btn btn-success" id="btnApproveGeneric">Setujui</button>
                </div>
            </div>
        </div>
    </div>

    @foreach ($noRecord as $data)
        <div class="modal fade" id="modalNoRecord{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" data-backdrop="false" data-keyboard="false" style="background-color: transparent !important;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel{{ $data->id }}">Bukti Gambar</h5>
                    </div>
                    <div class="modal-body text-center">
                        @if($data->bukti_gambar)
                        <div class="frame-wrapper">
                            <img src="{{ asset('storage/'.$data->bukti_gambar) }}" alt="Bukti Gambar" class="img-fluid rounded" width="300px" height="300px">

                            <div class="corner tl"></div>
                            <div class="corner tr"></div>
                            <div class="corner bl"></div>
                            <div class="corner br"></div>

                            <div class="dot tl"></div>
                            <div class="dot tr"></div>
                            <div class="dot bl"></div>
                            <div class="dot br"></div>
                        </div> @else
                        <p class="text-muted">Tidak ada bukti gambar.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @foreach ($schemeWork as $data)
        <div class="modal fade" id="schemeWorkModal{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" data-backdrop="false" data-keyboard="false" style="background-color: transparent !important;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel{{ $data->id }}">Bukti Gambar</h5>
                    </div>
                    <div class="modal-body text-center">
                        @if($data->bukti_gambar)
                        <div class="frame-wrapper">
                            <img src="{{ asset('storage/'.$data->bukti_gambar) }}" alt="Bukti Gambar" class="img-fluid rounded" width="300px" height="300px">

                            <div class="corner tl"></div>
                            <div class="corner tr"></div>
                            <div class="corner bl"></div>
                            <div class="corner br"></div>

                            <div class="dot tl"></div>
                            <div class="dot tr"></div>
                            <div class="dot bl"></div>
                            <div class="dot br"></div>
                        </div> @else
                        <p class="text-muted">Tidak ada bukti gambar.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @foreach ($cancelLeave as $data)
        <div class="modal fade" id="cancelLeaveModal{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" data-backdrop="false" data-keyboard="false" style="background-color: transparent !important;">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel{{ $data->id }}">Bukti Gambar</h5>
                    </div>
                    <div class="modal-body text-center">
                        @if($data->bukti_gambar)
                        <div class="frame-wrapper">
                            <img src="{{ asset('storage/'.$data->bukti_gambar) }}" alt="Bukti Gambar" class="img-fluid rounded" width="300px" height="300px">

                            <div class="corner tl"></div>
                            <div class="corner tr"></div>
                            <div class="corner bl"></div>
                            <div class="corner br"></div>

                            <div class="dot tl"></div>
                            <div class="dot tr"></div>
                            <div class="dot bl"></div>
                            <div class="dot br"></div>
                        </div> @else
                        <p class="text-muted">Tidak ada bukti gambar.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    @foreach ($cancelLeave as $data)
        <div class="modal fade" id="DetailcancelLeaveModal{{ $data->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" style="backdrop-filter: blur(5px); background-color: rgba(0, 0, 0, 0.4);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-sm rounded-3" style="background-color: #ffffff;">
                    <div class="modal-header border-0 pb-2">
                        <h5 class="modal-title fw-bold text-dark" id="modalLabel{{ $data->id }}">Detail Pembatalan Cuti</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="fw-semibold text-muted me-3" style="min-width: 120px;">Tanggal Awal</span>
                                        <span class="text-dark">: {{ \Carbon\Carbon::parse($data->tanggal_awal)->format('d M Y') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="fw-semibold text-muted me-3" style="min-width: 120px;">Tanggal Akhir</span>
                                        <span class="text-dark">: {{ \Carbon\Carbon::parse($data->tanggal_akhir)->format('d M Y') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="fw-semibold text-muted me-3" style="min-width: 120px;">Durasi</span>
                                        <span class="text-dark">: {{ $data->durasi }} Hari</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="fw-semibold text-muted me-3" style="min-width: 120px;">Kontak</span>
                                        <span class="text-dark">: {{ $data->kontak ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="fw-semibold text-muted me-3" style="min-width: 150px;">Tipe Cuti</span>
                                        <span class="text-dark">: {{ $data->tipe ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="fw-semibold text-muted me-3" style="min-width: 150px;">Alasan</span>
                                        <span class="text-dark">: {{ $data->alasan ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex align-items-start mb-3">
                                        <span class="fw-semibold text-muted me-3" style="min-width: 150px;">Alasan Pembatalan</span>
                                        <span class="text-dark">: {{ $data->kronologi ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <span class="fw-semibold text-muted me-3" style="min-width: 120px;">Bukti Gambar</span>
                                        <div>
                                            @if($data->bukti_gambar)
                                            <div class="position-relative d-inline-block">
                                                <img src="{{ asset('storage/'.$data->bukti_gambar) }}" alt="Bukti Gambar" class="rounded-3 shadow-sm img-fluid" style="max-width: 250px; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            </div>
                                            @else
                                            <span class="text-muted fst-italic">Tidak ada bukti gambar.</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="container-fluid">
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="cube">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
            </div>
        </div>

        @php
        function canUserApprove($kendala, $pengajuDivisi, $userJabatan) {  
            switch ($kendala) {
                case 'Human Error':
                    return $userJabatan === 'GM';
                
                case 'Absen Pulang':
                    return $userJabatan === 'HRD';
                
                case 'System Error':
                    if ($pengajuDivisi === 'Office') {  // Sekarang pakai divisi pengaju
                        return $userJabatan === 'GM';
                    } else {
                        return strpos($userJabatan, 'Koordinator') !== false;
                    }
                
                default:
                    return in_array($userJabatan, ['HRD', 'GM']);
            }
        }

        $currentUserJabatan = auth()->user()->karyawan->jabatan ?? '';
        $currentUserDivisi = auth()->user()->karyawan->divisi ?? '';  // Tetap simpan, tapi tidak dipakai di canUserApprove untuk System Error
        @endphp

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    <!-- Navigation buttons (commented out) -->
                </div>
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1 mb-3">{{ __('Data Pengajuan Klaim') }}</h3>
                        <p class="line-text" id="judulTabel">{{ __('Absen Tidak Terekam') }}</p>
                        <div class="mb-3">
                            <div class="d-flex justify-content-end flex-warp gap-2">
                                <form action="{{ route('pengajuanklaim.excel') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="jenis_PK" value="No Record">
                                    <button type="submit" class="btn btn-outline-success d-flex align-items-center gap-2 px-3">
                                        <img src="{{ asset('icon/file-excel.svg') }}" alt="" width="25px"> Excel
                                    </button>
                                </form>

                                <form action="{{ route('pengajuanklaim.PDF') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="jenis_PK" value="No Record">
                                    <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2 px-3">
                                        <img src="{{ asset('icon/document-pdf.svg') }}" alt="" width="25px"> PDF
                                    </button>
                                </form>
                            </div>

                            <table class="table table-striped" id="no_record_table">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama Karyawan</th>
                                        <th scope="col">Divisi</th>
                                        <th scope="col">Kendala</th>
                                        <th scope="col">Tanggal Absen</th>
                                        <th scope="col">Kronologi</th>
                                        <th scope="col">Tanggal Diajukan</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Alasan Approval</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no = 1;
                                    @endphp
                                    @foreach ($noRecord as $data)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $data->karyawan->nama_lengkap }}</td>
                                        <td>{{ $data->karyawan->divisi }}</td>
                                        <td>
                                            <span class="badge 
                                                @switch($data->kendala)
                                                    @case('Human Error')
                                                        bg-warning text-dark
                                                        @break
                                                    @case('Absen Pulang')
                                                        bg-info text-white
                                                        @break
                                                    @case('System Error')
                                                        bg-danger text-white
                                                        @break
                                                    @default
                                                        bg-secondary text-white
                                                @endswitch
                                            ">{{ $data->kendala }}</span>
                                        </td>
                                        <td>
                                            @if($data->absensiKaryawan)
                                            {{ \Carbon\Carbon::parse($data->absensiKaryawan->tanggal)->translatedFormat('l, d F Y') }}
                                            @else
                                            {{ \Carbon\Carbon::parse($data->tanggal)->translatedFormat('l, d F Y') }}
                                            @endif
                                        </td>

                                        <td>{{ $data->kronologi }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d-M-Y H:i') }}</td>
                                        <td>
                                            @switch($data->approval)
                                            @case(0)
                                            <span class="badge rounded-pill bg-warning text-dark">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu
                                            </span>
                                            @break

                                            @case(1)
                                            <span class="badge rounded-pill bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Disetujui
                                            </span>
                                            @break

                                            @case(2)
                                            <span class="badge rounded-pill bg-danger">
                                                <i class="bi bi-x-circle me-1"></i> Ditolak
                                            </span>
                                            @break

                                            @default
                                            <span class="badge rounded-pill bg-secondary">
                                                <i class="bi bi-question-circle me-1"></i> Tidak Diketahui
                                            </span>
                                            @endswitch
                                        </td>

                                        <td>{{ $data->alasan_approval ?? '-' }}</td>
                                        <td style="font-size: 14px;">
                                            <div class="btn-group dropup">
                                                <button type="button" class="btn dropdown-toggle btn-secondary" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                                <div class="dropdown-menu">
                                                    <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Lihat Bukti" data-target="#modalNoRecord{{ $data->id }}">
                                                        <span><img src="{{ asset('icon/eye.svg') }}" alt="eye.png" width="20px" height="20px"></span> Bukti Gambar
                                                    </button>
                                                    <form action="{{ route('pengajuanklaim.deleteNoRecord') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" value="{{ $data->id }}" name="id_noRecord">
                                                        <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Hapus Data" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                            <span><img src="{{ asset('icon/trash-danger.svg') }}" alt="delete.png" width="20px" height="20px"></span> Hapus Data
                                                        </button>
                                                    </form>
                                                    
                                                    @if($data->approval === 0)
                                                        @if(canUserApprove($data->kendala, $data->karyawan->divisi, $currentUserJabatan))
                                                            <button type="button"
                                                                class="dropdown-item approve-btn"
                                                                data-id="{{ $data->id }}"
                                                                data-type="noRecord"
                                                                data-kendala="{{ $data->kendala }}"
                                                                title="Approve pengajuan">
                                                                <span><img src="{{ asset('icon/clipboard-primary.svg') }}" alt="approve.png" width="20px" height="20px"></span> Approve
                                                            </button>
                                                        @else
                                                            <button type="button"
                                                                class="dropdown-item disabled text-muted"
                                                                disabled
                                                                title="Anda tidak memiliki akses untuk approve {{ $data->kendala }}">
                                                                <span><img src="{{ asset('icon/clipboard-secondary.svg') }}" alt="no-access.png" width="20px" height="20px"></span> Approve
                                                            </button>
                                                        @endif
                                                    @elseif($data->approval === 1)
                                                        <span class="dropdown-item text-success">
                                                            <span><img src="{{ asset('icon/check-circle.svg') }}" alt="approved.png" width="20px" height="20px"></span> Sudah Disetujui
                                                        </span>
                                                    @elseif($data->approval === 2)
                                                        <span class="dropdown-item text-danger">
                                                            <span><img src="{{ asset('icon/x-circle.svg') }}" alt="rejected.png" width="20px" height="20px"></span> Sudah Ditolak
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1 mb-3">{{ __('Data Pengajuan Klaim') }}</h3>
                        <p class="line-text" id="judulTabel">{{ __('Skema Jam Kerja') }}</p>
                        <div class="mb-3">
                            <div class="d-flex justify-content-end flex-warp gap-2">
                                <form action="{{ route('pengajuanklaim.excel') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="jenis_PK" value="Scheme Work">
                                    <button type="submit" class="btn btn-outline-success d-flex align-items-center gap-2 px-3">
                                        <img src="{{ asset('icon/file-excel.svg') }}" alt="" width="25px"> Excel
                                    </button>
                                </form>

                                <form action="{{ route('pengajuanklaim.PDF') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="jenis_PK" value="Scheme Work">
                                    <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2 px-3">
                                        <img src="{{ asset('icon/document-pdf.svg') }}" alt="" width="25px"> PDF
                                    </button>
                                </form>
                            </div>
                            <table class="table table-striped" id="schema_work_table">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama Karyawan</th>
                                        <th scope="col">Divisi</th>
                                        <th scope="col">Tanggal Absen</th>
                                        <th scope="col">Kronologi</th>
                                        <th scope="col">Tanggal Diajukan</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Alasan Approval</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no = 1;
                                    @endphp
                                    @foreach ($schemeWork as $data)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $data->karyawan->nama_lengkap }}</td>
                                        <td>{{ $data->karyawan->divisi }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->absensiKaryawan->tanggal)->translatedFormat('l, d F Y') }}</td>
                                        <td>{{ $data->kronologi }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d-M-Y H:i') }}</td>
                                        <td>
                                            @switch($data->approval)
                                            @case(0)
                                            <span class="badge rounded-pill bg-warning text-dark">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu
                                            </span>
                                            @break

                                            @case(1)
                                            <span class="badge rounded-pill bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Disetujui
                                            </span>
                                            @break

                                            @case(2)
                                            <span class="badge rounded-pill bg-danger">
                                                <i class="bi bi-x-circle me-1"></i> Ditolak
                                            </span>
                                            @break

                                            @default
                                            <span class="badge rounded-pill bg-secondary">
                                                <i class="bi bi-question-circle me-1"></i> Tidak Diketahui
                                            </span>
                                            @endswitch
                                        </td>

                                        <td>{{ $data->alasan_approval ?? '-' }}</td>
                                        <td style="font-size: 14px;">
                                            <div class="btn-group dropup">
                                                <button type="button" class="btn dropdown-toggle btn-secondary" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                                <div class="dropdown-menu">
                                                    <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Lihat Bukti" data-target="#schemeWorkModal{{ $data->id }}">
                                                        <span><img src="{{ asset('icon/eye.svg') }}" alt="eye.png" width="20px" height="20px"></span> Bukti Gambar
                                                    </button>
                                                    <form action="{{ route('pengajuanklaim.deleteSchemeWork') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" value="{{ $data->id }}" name="id_scheme_work">
                                                        <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Hapus Data" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                            <span><img src="{{ asset('icon/trash-danger.svg') }}" alt="delete.png" width="20px" height="20px"></span> Hapus Data
                                                        </button>
                                                    </form>
                                                    
                                                    @if($data->approval === 0)
                                                        @if(in_array($currentUserJabatan, ['HRD', 'Koordinator ITSM']))
                                                            <button type="button"
                                                                class="dropdown-item approve-btn"
                                                                data-id="{{ $data->id }}"
                                                                data-type="schemeWork"
                                                                data-bukti="{{ $data->bukti_gambar ? 1 : 0 }}"
                                                                title="Approve pengajuan">
                                                                <span><img src="{{ asset('icon/clipboard-primary.svg') }}" alt="approve.png" width="20px" height="20px"></span> Approve
                                                            </button>
                                                        @else
                                                            <button type="button"
                                                                class="dropdown-item disabled text-muted"
                                                                disabled
                                                                title="Hanya HRD atau Koordinator ITSM yang bisa approve">
                                                                <span><img src="{{ asset('icon/clipboard-secondary.svg') }}" alt="no-access.png" width="20px" height="20px"></span> Approve
                                                            </button>
                                                        @endif
                                                    @elseif($data->approval === 1)
                                                        <span class="dropdown-item text-success">
                                                            <span><img src="{{ asset('icon/check-circle.svg') }}" alt="approved.png" width="20px" height="20px"></span> Sudah Disetujui
                                                        </span>
                                                    @elseif($data->approval === 2)
                                                        <span class="dropdown-item text-danger">
                                                            <span><img src="{{ asset('icon/x-circle.svg') }}" alt="rejected.png" width="20px" height="20px"></span> Sudah Ditolak
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card m-4">
                    <div class="card-body table-responsive">
                        <h3 class="card-title text-center my-1 mb-3">{{ __('Data Pengajuan Klaim') }}</h3>
                        <p class="line-text" id="judulTabel">{{ __('Pembatalan Cuti') }}</p>
                        <div class="mb-3">
                            <div class="d-flex justify-content-end flex-warp gap-2">
                                <form action="{{ route('pengajuanklaim.excel') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="jenis_PK" value="Cancel Leave">
                                    <button type="submit" class="btn btn-outline-success d-flex align-items-center gap-2 px-3">
                                        <img src="{{ asset('icon/file-excel.svg') }}" alt="" width="20"> Excel
                                    </button>
                                </form>

                                <form action="{{ route('pengajuanklaim.PDF') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="jenis_PK" value="Cancel Leave">
                                    <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2 px-3">
                                        <img src="{{ asset('icon/document-pdf.svg') }}" alt="PDF" width="20">
                                        <span>PDF</span>
                                    </button>
                                </form>
                            </div>
                            <table class="table table-striped" id="cancel_leave_table">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Nama Karyawan</th>
                                        <th scope="col">Divisi</th>
                                        <th scope="col">Tanggal Cuti</th>
                                        <th scope="col">Durasi</th>
                                        <th scope="col">Alasan Cuti</th>
                                        <th scope="col">Alasan Pembatalan</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Alasan Approval</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no = 1;
                                    @endphp
                                    @foreach ($cancelLeave as $data)
                                    <tr>
                                        <td>{{ $no++ }}</td>
                                        <td>{{ $data->karyawan->nama_lengkap }}</td>
                                        <td>{{ $data->karyawan->divisi }}</td>
                                        <td>{{ \Carbon\Carbon::parse($data->tanggal_awal)->translatedFormat('l, d F Y') }} - {{ \Carbon\Carbon::parse($data->tanggal_akhir)->translatedFormat('l, d F Y') }}</td>
                                        <td>{{ $data->durasi }}</td>
                                        <td>{{ $data->alasan }}</td>
                                        <td>{{ $data->kronologi }}</td>
                                        <td>
                                            @switch($data->approval)
                                            @case(0)
                                            <span class="badge rounded-pill bg-warning text-dark">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu Atasan
                                            </span>
                                            @break

                                            @case(1)
                                            <span class="badge rounded-pill bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Disetujui
                                            </span>
                                            @break

                                            @case(2)
                                            <span class="badge rounded-pill bg-danger">
                                                <i class="bi bi-x-circle me-1"></i> Ditolak
                                            </span>
                                            @break

                                            @default
                                            <span class="badge rounded-pill bg-secondary">
                                                <i class="bi bi-question-circle me-1"></i> Tidak Diketahui
                                            </span>
                                            @endswitch
                                        </td>

                                        <td>{{ $data->alasan_approval ?? '-' }}</td>
                                        <td style="font-size: 14px;">
                                            <div class="btn-group dropup">
                                                <button type="button" class="btn dropdown-toggle btn-secondary" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                                <div class="dropdown-menu">
                                                    <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Lihat Bukti" data-target="#cancelLeaveModal{{ $data->id }}">
                                                        <span><img src="{{ asset('icon/eye.svg') }}" alt="eye.png" width="20px" height="20px"></span> Bukti Gambar
                                                    </button>
                                                    <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Lihat Detail" data-target="#DetailcancelLeaveModal{{ $data->id }}">
                                                        <span><img src="{{ asset('icon/eye.svg') }}" alt="eye.png" width="20px" height="20px"></span> Detail
                                                    </button>
                                                    <form action="{{ route('pengajuanklaim.deleteCancelLeave') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" value="{{ $data->id }}" name="id_cancel_leave">
                                                        <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Hapus Data" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                            <span><img src="{{ asset('icon/trash-danger.svg') }}" alt="delete.png" width="20px" height="20px"></span> Hapus Data
                                                        </button>
                                                    </form>
                                                    
                                                    @if($data->approval === 0)
                                                        @if(in_array($currentUserJabatan, ['HRD', 'Koordinator ITSM']))
                                                            <button type="button"
                                                                class="dropdown-item approve-btn"
                                                                data-id="{{ $data->id }}"
                                                                data-type="cancelLeave"
                                                                data-bukti="{{ $data->bukti_gambar ? 1 : 0 }}"
                                                                title="Approve pengajuan">
                                                                <span><img src="{{ asset('icon/clipboard-primary.svg') }}" alt="approve.png" width="20px" height="20px"></span> Approve
                                                            </button>
                                                        @else
                                                            <button type="button"
                                                                class="dropdown-item disabled text-muted"
                                                                disabled
                                                                title="Hanya HRD atau Koordinator ITSM yang bisa approve">
                                                                <span><img src="{{ asset('icon/clipboard-secondary.svg') }}" alt="no-access.png" width="20px" height="20px"></span> Approve
                                                            </button>
                                                        @endif
                                                    @elseif($data->approval === 1)
                                                        <span class="dropdown-item text-success">
                                                            <span><img src="{{ asset('icon/check-circle.svg') }}" alt="approved.png" width="20px" height="20px"></span> Sudah Disetujui
                                                        </span>
                                                    @elseif($data->approval === 2)
                                                        <span class="dropdown-item text-danger">
                                                            <span><img src="{{ asset('icon/x-circle.svg') }}" alt="rejected.png" width="20px" height="20px"></span> Sudah Ditolak
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<style>
    .loader {
        position: relative;
        text-align: center;
        margin: 15px auto 35px auto;
        z-index: 9999;
        display: block;
        width: 80px;
        height: 80px;
        border: 10px solid rgba(0, 0, 0, .3);
        border-radius: 50%;
        border-top-color: #000;
        animation: spin 1s ease-in-out infinite;
        -webkit-animation: spin 1s ease-in-out infinite;
    }

    .line-text {
        display: flex;
        align-items: center;
        text-align: center;
        color: #000;
        font-size: 12px;
    }

    .line-text::before,
    .line-text::after {
        content: '';
        width: 10px;
        flex: 1;
        border-bottom: 3px solid #000;
        margin: 0 15px;
    }

    @keyframes spin {
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    @-webkit-keyframes spin {
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    .modal-content {
        border-radius: 0px;
        box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }

    .modal-backdrop.show {
        opacity: 0.75;
    }

    .loader-txt {
        p {
            font-size: 13px;
            color: #666;

            small {
                font-size: 11.5px;
                color: #999;
            }
        }
    }

    /* Style untuk tombol disabled */
    .dropdown-item.disabled {
        color: #6c757d !important;
        pointer-events: none !important;
        background-color: transparent !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
    }

    .dropdown-item.disabled:hover {
        background-color: transparent !important;
        color: #6c757d !important;
    }

    .dropdown-item.disabled:focus {
        background-color: transparent !important;
        color: #6c757d !important;
        outline: none !important;
    }

    .dropdown-item.disabled:active {
        background-color: transparent !important;
        color: #6c757d !important;
    }
</style>
@push('js')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script>
$(document).ready(function() {
    // Inisialisasi DataTables
    $('#no_record_table').DataTable();
    $('#cancel_leave_table').DataTable();
    $('#schema_work_table').DataTable();

    // Tooltip
    $('[title]').tooltip();

    // Event delegation untuk approve-btn (mendukung elemen dinamis)
    $(document).on('click', '.approve-btn', function(e) {
        e.preventDefault();
        
        const id = $(this).data('id');
        const type = $(this).data('type');
        const kendala = $(this).data('kendala');

        $('#rejectReason').val('');
        $('#rejectReasonGroup').hide();

        let modalTitle = '';
        let modalQuestion = '';

        switch(type) {
            case 'noRecord':
                modalTitle = 'Approve No Record';
                modalQuestion = `Setujui pengajuan No Record dengan kendala "${kendala}" (ID: ${id}) ini?`;
                $('#rejectReasonGroup').show();
                $('#schemework').hide();
                break;
                case 'cancelLeave':
                    modalTitle = 'Approve Cancel Leave';
                    modalQuestion = `Setujui pengajuan Cancel Leave dengan ID: ${id} ini?`;
                    $('#rejectReasonGroup').show();
                    $('#schemework').hide();
                    break;
                case 'schemeWork':
                    modalTitle = 'Approve Scheme Work';
                    modalQuestion = `Setujui pengajuan Scheme Work dengan ID: ${id} ini?`;
                    $('#rejectReasonGroup').show();
                    $('#schemework').show();
                    break;

                default:
                    modalTitle = 'Approval Confirmation';
                    modalQuestion = 'Apakah Anda yakin?';
                    $('#schemework').hide();
                    $('#rejectReasonGroup').hide();
                    break;
            }

$('#genericModalLabel').text(modalTitle);
        $('#modalQuestion').text(modalQuestion);

        $('#btnApproveGeneric').data('id', id).data('type', type);
        $('#btnRejectGeneric').data('id', id).data('type', type);
        
        $('#genericModal').modal('show');
        });

        // Handler saat klik tombol Setujui
        $('#btnApproveGeneric').on('click', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');

            // Data dasar untuk ajax
            let ajaxData = {
                id: id,
                type: type,
                action: 'approve',
                _token: '{{ csrf_token() }}'
            };

            // Kalau type schemeWork, tambahkan jam_masuk dan jam_pulang dari input modal
            if (type === 'schemeWork') {
                const jam_masuk = $('#jam_masuk').val();
                const jam_pulang = $('#jam_pulang').val();

                if (!jam_masuk || !jam_pulang) {
                    alert('Harap isi jam masuk dan jam pulang untuk Scheme Work!');
                    return;
                }

                ajaxData.jam_masuk = jam_masuk;
                ajaxData.jam_pulang = jam_pulang;
            }

            console.log(`Menyetujui ${type} dengan ID: ${id}`, ajaxData);

            $.ajax({
                url: '/pengajuan-klaim/approval',
                method: 'POST',
                data: ajaxData,
                beforeSend: function() {
                    // Show loading state
                    $('#btnApproveGeneric').prop('disabled', true).text('Processing...');
                },
                success: function(response) {
                    console.log(response);
                    $('#genericModal').modal('hide');
                    
                    if (response.success) {
                        alert('Pengajuan berhasil disetujui!');
                        location.reload(); // Reload untuk update tampilan
                    } else {
                        alert('Gagal menyetujui: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    let errorMsg = 'Terjadi kesalahan saat menyetujui.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    alert(errorMsg);
                },
                complete: function() {
                    // Reset button state
                    $('#btnApproveGeneric').prop('disabled', false).text('Setujui');
                }
            });
        });

        // Handler saat klik tombol Tolak
        $('#btnRejectGeneric').on('click', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const rejectReason = $('#rejectReason').val().trim();

            if (rejectReason === '') {
                alert('Harap isi keterangan penolakan untuk menolak pengajuan ini.');
                return; // Stop jika alasan kosong
            }

            console.log(`Menolak ${type} dengan ID: ${id}, Alasan: ${rejectReason}`);

            $.ajax({
                url: '/pengajuan-klaim/reject',
                method: 'POST',
                data: {
                    id: id,
                    type: type,
                    action: 'reject',
                    reject_reason: rejectReason,
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    // Show loading state
                    $('#btnRejectGeneric').prop('disabled', true).text('Processing...');
                },
                success: function(response) {
                    console.log(response);
                    $('#genericModal').modal('hide');
                    
                    if (response.success) {
                        alert('Pengajuan berhasil ditolak!');
                        location.reload(); // Reload untuk update tampilan
                    } else {
                        alert('Gagal menolak: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    let errorMsg = 'Terjadi kesalahan saat menolak.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    alert(errorMsg);
                },
                complete: function() {
                    // Reset button state
                    $('#btnRejectGeneric').prop('disabled', false).text('Tolak');
                }
            });
        });

        // Reset form saat modal ditutup
        $('#genericModal').on('hidden.bs.modal', function () {
            $('#rejectReason').val('');
            $('#jam_masuk').val('');
            $('#jam_pulang').val('');
            $('#rejectReasonGroup').hide();
            $('#schemework').hide();
        });
    });
</script>

@endpush
@endsection