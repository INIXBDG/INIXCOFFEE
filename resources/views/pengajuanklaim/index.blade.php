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

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                    <!-- <a href="{{ route('pengajuanklaim.NoRecord') }}" class="btn btn-info color-white me-2">Absen Tidak Terekap</a>
                    <a href="{{ route('pengajuanklaim.SchemeWork') }}" class="btn btn-warning me-2">Perubahan Jam Kerja</a>
                    <a href="{{ route('pengajuanklaim.CancelLeave') }}" class="btn btn-danger me-2">Pembatalan Cuti</a> -->
                    {{-- <div>
                        <select class="form-select" id="tabelSelector">
                            <option value="no_record">Absen Tidak Terekam</option>
                            <option value="schema_work">Perubahan Jam Kerja</option>
                            <option value="cancel_leave">Pembatalan Cuti</option>
                        </select>
                    </div> --}}
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
                                        <td>{{ $data->kendala }}</td>
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
                                                        <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Lihat Bukti">
                                                            <span><img src="{{ asset('icon/trash-danger.svg') }}" alt="eye.png" width="20px" height="20px"></span> Hapus Data
                                                        </button>
                                                    </form>
                                                    @if($data->approval === 1)
                                                    @elseif($data->approval === 0)
                                                        <button type="button"
                                                            class="dropdown-item"
                                                            data-id="{{ $data->id }}"
                                                            data-type="noRecord"
                                                            data-toggle="modal"
                                                            data-target="#genericModal">
                                                            <span><img src="{{ asset('icon/clipboard-primary.svg') }}" alt="eye.png" width="20px" height="20px"></span> Approve
                                                        </button>
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
                                                        <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Hapus Data">
                                                            <span><img src="{{ asset('icon/trash-danger.svg') }}" alt="eye.png" width="20px" height="20px"></span> Hapus Data
                                                        </button>
                                                    </form>
                                                    {{--@if(auth()->user()->jabatan === 'HRD') --}}
                                                    @if($data->approval === 1)
                                                    @elseif($data->approval === 0)
                                                        <button type="button"
                                                            class="dropdown-item"
                                                            data-id="{{ $data->id }}"
                                                            data-type="schemeWork"
                                                            data-bukti="{{ $data->bukti_gambar ? 1 : 0 }}"
                                                            data-toggle="modal"
                                                            data-target="#genericModal">
                                                            <span><img src="{{ asset('icon/clipboard-primary.svg') }}" alt="eye.png" width="20px" height="20px"></span> Approve
                                                        </button>
                                                        
                                                    @endif
                                                    {{-- @endif --}}
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
                                                    <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Lihat Bukti" data-target="#DetailcancelLeaveModal{{ $data->id }}">
                                                        <span><img src="{{ asset('icon/eye.svg') }}" alt="eye.png" width="20px" height="20px"></span> Detail
                                                    </button>
                                                    <form action="{{ route('pengajuanklaim.deleteCancelLeave') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" value="{{ $data->id }}" name="id_cancel_leave">
                                                        <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Hapus Data">
                                                            <span><img src="{{ asset('icon/trash-danger.svg') }}" alt="eye.png" width="20px" height="20px"></span> Hapus Data
                                                        </button>
                                                    </form>
                                                    @if($data->approval === 1)
                                                    @elseif($data->approval === 0)
                                                        <button type="button"
                                                            class="dropdown-item"
                                                            data-id="{{ $data->id }}"
                                                            data-type="cancelLeave"
                                                            data-bukti="{{ $data->bukti_gambar ? 1 : 0 }}"
                                                            data-toggle="modal"
                                                            data-target="#genericModal">
                                                            <span><img src="{{ asset('icon/clipboard-primary.svg') }}" alt="eye.png" width="20px" height="20px"></span> Approve
                                                        </button>
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
        $('#no_record_table').dataTable();
        $('#cancel_leave_table').dataTable();
        $('#schema_work_table').dataTable();
        // Tooltip untuk semua elemen dengan attribute title
        $('[title]').tooltip();
        // Menangani klik tombol untuk membuka genericModal dengan data dinamis
        $('button[data-target="#genericModal"]').on('click', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');

            // Reset input alasan penolakan dan sembunyikan default
            $('#rejectReason').val('');
            $('#rejectReasonGroup').hide();

            let modalTitle = '';
            let modalQuestion = '';

            // Set judul dan pertanyaan modal sesuai type
            switch(type) {
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
                case 'noRecord':
                    modalTitle = 'Approve No Record';
                    modalQuestion = `Setujui pengajuan No Record dengan ID: ${id} ini?`;
                    $('#rejectReasonGroup').show();
                    $('#schemework').hide();
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

            // Simpan data id dan type ke tombol Approve dan Reject
            $('#btnApproveGeneric').data('id', id).data('type', type);
            $('#btnRejectGeneric').data('id', id).data('type', type);
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

                ajaxData.jam_masuk = jam_masuk;
                ajaxData.jam_pulang = jam_pulang;
            }

            console.log(`Menyetujui ${type} dengan ID: ${id}`, ajaxData);
            // alert(`Pengajuan ${type} dengan ID ${id} disetujui!`);

            $.ajax({
                url: '/pengajuan-klaim/approval',
                method: 'POST',
                data: ajaxData,
                success: function(response) {
                    console.log(response);
                    $('#genericModal').modal('hide');
                    // location.reload();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Terjadi kesalahan saat menyetujui.');
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
            alert(`Pengajuan ${type} dengan ID ${id} ditolak dengan alasan: ${rejectReason}`);

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
                success: function(response) {
                    console.log(response);
                    $('#genericModal').modal('hide');
                    // Bisa update UI setelah penolakan berhasil
                    // location.reload();

                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Terjadi kesalahan saat menolak.');
                }
            });
        });

        // Reset form saat modal ditutup
        $('#genericModal').on('hidden.bs.modal', function () {
            $('#rejectReason').val('');
            $('#rejectReasonGroup').hide();
        });

        // // Fungsi showTimeInputs dan showTextarea untuk menampilkan input sesuai kebutuhan
        // function showTimeInputs(id) {
        //     $('#timeInputs' + id).removeClass('d-none');
        //     $('#textareaDiv' + id).addClass('d-none');
        // }

        // function showTextarea(id) {
        //     $('#textareaDiv' + id).removeClass('d-none');
        //     $('#timeInputs' + id).addClass('d-none');
        // }

        // // Menampilkan tabel sesuai pilihan selector
        // const tables = {
        //     no_record: $('#no_record_table'),
        //     scheme_work: $('#schema_work_table'),
        //     cancel_leave: $('#cancel_leave_table')
        // };

        // const judulMap = {
        //     no_record: "Absen Tidak Terekam",
        //     scheme_work: "Terlambat Karena Perubahan Jam Kerja",
        //     cancel_leave: "Pembatalan Cuti"
        // };

        // function showTable(selected) {
        //     $.each(tables, function(key, element) {
        //         if (element.length) {
        //             element.css('display', (key === selected) ? 'block' : 'none');
        //         }
        //     });

        //     if (judulMap[selected]) {
        //         $('#judulTabel').text(judulMap[selected]);
        //     }

        //     // Update URL tanpa reload
        //     const url = new URL(window.location.href);
        //     url.searchParams.set('tabel', selected);
        //     window.history.replaceState({}, '', url);
        // }

        // // Inisialisasi default tabel dari URL atau no_record
        // const urlParams = new URLSearchParams(window.location.search);
        // const defaultTable = urlParams.get('tabel') || 'no_record';

        // $('#tabelSelector').val(defaultTable);
        // showTable(defaultTable);

        // // Event saat select berubah
        // $('#tabelSelector').on('change', function() {
        //     showTable(this.value);
        // });
    });
</script>


@endpush
@endsection