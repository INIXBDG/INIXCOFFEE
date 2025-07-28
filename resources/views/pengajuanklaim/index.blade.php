@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


@section('content')
@foreach ($cancelLeave as $data)
<div class="modal fade" id="modalApproveCancelLeave{{ $data->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="false" style="background-color: transparent !important;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formApproval{{ $data->id }}" action="{{ route('pengajuanklaim.aproveCancelLeave') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel{{ $data->id }}">Approve</h5>
                </div>
                <div class="modal-body text-start">
                    @if($data->bukti_gambar)
                    <input type="hidden" name="id_CL" id="id_CL" value="{{ $data->id }}">
                    <input type="hidden" name="id_karyawan" id="id_karyawan" value="{{ $data->id_karyawan }}">
                    <input type="hidden" name="approval" id="approvalInput{{ $data->id }}">

                    <div class="btn-group mb-3" role="group">
                        <input type="button" class="btn btn-outline-primary" value="Ya" onclick="submitApproval('{{ $data->id }}', 1)">
                        <input type="button" class="btn btn-outline-danger" value="Tidak" onclick="showTextarea('{{ $data->id }}')">
                    </div>

                    <div class="form-group d-none" id="textareaDiv{{ $data->id }}">
                        <label for="alasan_approval{{ $data->id }}">Keterangan</label>
                        <textarea class="form-control" name="alasan_approval" id="alasan_approval{{ $data->id }}" placeholder="Keterangan"></textarea>
                        <button type="button" class="btn btn-success mt-2 text-end" onclick="submitApproval('{{ $data->id }}', 2)">Kirim</button>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@foreach ($schemeWork as $data)
<div class="modal fade" id="modalApproveSchemeWork{{ $data->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="false" style="background-color: transparent !important;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formApproval{{ $data->id }}" action="{{ route('pengajuanklaim.approveSchemeWork') }}" method="POST">

                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel{{ $data->id }}">Approve</h5>
                </div>
                <div class="modal-body text-start">
                    @if($data->bukti_gambar)
                    <input type="hidden" name="id_absen" value="{{ $data->id_absen }}">
                    <input type="hidden" name="id_karyawan" value="{{ $data->id_karyawan }}">
                    <input type="hidden" name="approval" id="approvalInput{{ $data->id }}">

                    <div class="btn-group mb-3" role="group">
                        <input type="button" class="btn btn-outline-primary" value="Ya" onclick="showTimeInputs('{{ $data->id }}')">
                        <input type="button" class="btn btn-outline-danger" value="Tidak" onclick="showTextarea('{{ $data->id }}')">
                    </div>

                    {{-- Input Waktu Masuk & Pulang --}}
                    <div class="form-group d-none" id="timeInputs{{ $data->id }}">
                        <label for="waktu_masuk">Waktu Masuk</label>
                        <input type="time" class="form-control mb-2" id="waktu_masuk_{{ $data->id }}">

                        <label for="waktu_pulang">Waktu Pulang</label>
                        <input type="time" class="form-control mb-2" id="waktu_pulang_{{ $data->id }}">

                        <button type="button" class="btn btn-success mt-2" onclick="submitApproval('{{ $data->id }}', 1)">Kirim</button>
                    </div>

                    {{-- Input Alasan Penolakan --}}
                    <div class="form-group d-none" id="textareaDiv{{ $data->id }}">
                        <label for="alasan_approval{{ $data->id }}">Keterangan</label>
                        <textarea class="form-control" name="alasan_approval" id="alasan_approval{{ $data->id }}" placeholder="Keterangan"></textarea>
                        <button type="button" class="btn btn-success mt-2" onclick="submitApproval('{{ $data->id }}', 2)">Kirim</button>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endforeach
{{-- Modal Approve No Record --}}
@foreach ($noRecord as $data)
<div class="modal fade" id="modalApproveNoRecord{{ $data->id }}" tabindex="-1" aria-labelledby="modalLabelNoRecord{{ $data->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formApprovalNoRecord{{ $data->id }}" action="{{ route('pengajuanklaim.approveNoRecord') }}" method="POST">
        @csrf
        <input type="hidden" name="id_no_record" value="{{ $data->id }}">
        @if($data->id_absen)
    <input type="hidden" name="id_absen" value="{{ $data->id_absen }}">
@endif

        <input type="hidden" name="id_karyawan" value="{{ $data->id_karyawan }}">
        <input type="hidden" name="approval" id="approvalInput{{ $data->id }}">

        <div class="modal-header">
          <h5 class="modal-title" id="modalLabelNoRecord{{ $data->id }}">Approve Pengajuan</h5>
        </div>
        <div class="modal-body">
          <p>Setujui pengajuan No Record ini?</p>
          <div class="btn-group mb-3">
            <input type="button" class="btn btn-primary" value="Setujui" onclick="submitApproval('{{ $data->id }}', 1)">
            <input type="button" class="btn btn-danger" value="Tolak" onclick="submitApproval('{{ $data->id}}', 2)">
          </div>
          <div class="form-group mt-2">
            <label for="alasan_approval_{{ $data->id }}">Keterangan Penolakan (opsional)</label>
            <textarea class="form-control" name="alasan_approval" id="alasan_approval_{{ $data->id }}" placeholder="Keterangan jika ditolak..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach





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
                    <img src="{{ asset($data->bukti_gambar) }}" alt="Bukti Gambar" class="img-fluid rounded" width="300px" height="300px">

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
                    <img src="{{ asset($data->bukti_gambar) }}" alt="Bukti Gambar" class="img-fluid rounded" width="300px" height="300px">

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
                    <img src="{{ asset($data->bukti_gambar) }}" alt="Bukti Gambar" class="img-fluid rounded" width="300px" height="300px">

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
                                        <img src="{{ asset($data->bukti_gambar) }}" alt="Bukti Gambar" class="rounded-3 shadow-sm img-fluid" style="max-width: 250px; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
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
                <a href="{{ route('pengajuanklaim.createNoRecord') }}" class="btn btn-info color-white me-2">Absen Tidak Terekap</a>
                <a href="{{ route('pengajuanklaim.createSchemeWork') }}" class="btn btn-warning me-2">Perubahan Jam Kerja</a>
                <a href="{{ route('pengajuanklaim.createCancelLeave') }}" class="btn btn-danger me-2">Pembatalan Cuti</a>
                <div>
                    <select class="form-select" id="tabelSelector">
                        <option value="no_record">Absen Tidak Terekam</option>
                        <option value="schema_work">Perubahan Jam Kerja</option>
                        <option value="cancel_leave">Pembatalan Cuti</option>
                    </select>
                </div>
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1 mb-3">{{ __('Data Pengajuan Klaim') }}</h3>
                    <p class="line-text" id="judulTabel">{{ __('Absen Tidak Terekam') }}</p>
                    <div id="no_record_table" class="mb-3">
                        <div class="d-flex justify-content-end flex-warp gap-2">
                            <form action="{{ route('pengajuanklaim.excelNoRecord') }}" method="post">
                                @csrf
                                <input type="hidden" name="jenis_PK" value="No Record">
                                <button type="submit" class="btn btn-outline-success d-flex align-items-center gap-2 px-3">
                                    <img src="{{ asset('icon/file-excel.svg') }}" alt="" width="25px"> Excel
                                </button>
                            </form>

                            <form action="{{ route('pengajuanklaim.PDFNoRecord') }}" method="post">
                                @csrf
                                <input type="hidden" name="jenis_PK" value="No Record">
                                <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2 px-3">
                                    <img src="{{ asset('icon/document-pdf.svg') }}" alt="" width="25px"> PDF
                                </button>
                            </form>
                        </div>

                        <table class="table table-striped">
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
                                               {{-- @if(auth()->user()->jabatan === 'HRD')--}}
                                                @if($data->approval === 1)
                                                @elseif($data->approval === 0)
                                               <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalApproveNoRecord{{ $data->id }}" title="Approve Pengajuan">

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
                    <div id="schema_work_table" class="mb-3">
                        <div class="d-flex justify-content-end flex-warp gap-2">
                            <form action="{{ route('pengajuanklaim.excelNoRecord') }}" method="post">
                                @csrf
                                <input type="hidden" name="jenis_PK" value="Scheme Work">
                                <button type="submit" class="btn btn-outline-success d-flex align-items-center gap-2 px-3">
                                    <img src="{{ asset('icon/file-excel.svg') }}" alt="" width="25px"> Excel
                                </button>
                            </form>

                            <form action="{{ route('pengajuanklaim.PDFNoRecord') }}" method="post">
                                @csrf
                                <input type="hidden" name="jenis_PK" value="Scheme Work">
                                <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2 px-3">
                                    <img src="{{ asset('icon/document-pdf.svg') }}" alt="" width="25px"> PDF
                                </button>
                            </form>
                        </div>
                        <table class="table table-striped">
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
                                                <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Approve Pengajuan" data-target="#modalApproveSchemeWork{{ $data->id }}">
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
                    <div id="cancel_leave_table" class="mb-3">
                        <div class="d-flex justify-content-end flex-warp gap-2">
                            <form action="{{ route('pengajuanklaim.excelNoRecord') }}" method="post">
                                @csrf
                                <input type="hidden" name="jenis_PK" value="Cancel Leave">
                                <button type="submit" class="btn btn-outline-success d-flex align-items-center gap-2 px-3">
                                    <img src="{{ asset('icon/file-excel.svg') }}" alt="" width="20"> Excel
                                </button>
                            </form>

                            <form action="{{ route('pengajuanklaim.PDFNoRecord') }}" method="post">
                                @csrf
                                <input type="hidden" name="jenis_PK" value="Cancel Leave">
                                <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2 px-3">
                                    <img src="{{ asset('icon/document-pdf.svg') }}" alt="PDF" width="20">
                                    <span>PDF</span>
                                </button>
                            </form>
                        </div>
                        <table class="table table-striped">
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
                                                @if(auth()->user()->jabatan === 'HRD')
                                                @if($data->approval === 1)
                                                @elseif($data->approval === 0)
                                                <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Approve Pengajuan" data-target="#modalApproveCancelLeave{{ $data->id }}">
                                                    <span><img src="{{ asset('icon/clipboard-primary.svg') }}" alt="eye.png" width="20px" height="20px"></span> Approve
                                                </button>
                                                @endif
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
    function showTimeInputs(id) {
        document.getElementById('timeInputs' + id).classList.remove('d-none');
        document.getElementById('textareaDiv' + id).classList.add('d-none');
    }

    function showTextarea(id) {
        document.getElementById('textareaDiv' + id).classList.remove('d-none');
        document.getElementById('timeInputs' + id).classList.add('d-none');
    }


    function submitApproval(id, value) {
        document.getElementById('approvalInput' + id).value = value;

        // Tangani logic waktu_masuk & waktu_pulang
        let masuk = document.getElementById('waktu_masuk_' + id);
        let pulang = document.getElementById('waktu_pulang_' + id);

        if (value === 1) {
            // kalau tombol Ya, input waktu dikirim
            masuk.setAttribute('name', 'waktu_masuk');
            masuk.setAttribute('required', true);
            pulang.setAttribute('name', 'waktu_pulang');
            pulang.setAttribute('required', true);
        } else {
            // kalau Tidak, hapus agar tidak tervalidasi
            masuk.removeAttribute('name');
            masuk.removeAttribute('required');
            pulang.removeAttribute('name');
            pulang.removeAttribute('required');
        }

        document.getElementById('formApproval' + id).submit();
    }
    


    document.addEventListener('DOMContentLoaded', function() {
        const selector = document.getElementById('tabelSelector');
        const judul = document.getElementById('judulTabel');

        const tables = {
            no_record: document.getElementById('no_record_table'),
            schema_work: document.getElementById('schema_work_table'),
            cancel_leave: document.getElementById('cancel_leave_table')
        };

        const judulMap = {
            no_record: "Absen Tidak Terekam",
            schema_work: "Terlambat Karena Perubahan Jam Kerja",
            cancel_leave: "Pembatalan Cuti"
        };

        function showTable(selected) {
            for (let key in tables) {
                if (tables[key]) {
                    tables[key].style.display = (key === selected) ? 'block' : 'none';
                }
            }

            if (judulMap[selected]) {
                judul.textContent = judulMap[selected];
            }

            const url = new URL(window.location.href);
            url.searchParams.set('tabel', selected);
            window.history.replaceState({}, '', url);
        }

        const urlParams = new URLSearchParams(window.location.search);
        const defaultTable = urlParams.get('tabel') || 'no_record';

        selector.value = defaultTable;
        showTable(defaultTable);

        selector.addEventListener('change', function() {
            showTable(this.value);
        });
    });
</script>
<script>
function submitApproval(id, value) {
  const approvalInput = document.getElementById(`approvalInput${id}`);
  const form = document.getElementById(`formApprovalNoRecord${id}`);

  if (!approvalInput || !form) {
    console.error('Input atau form tidak ditemukan!', id);
    return;
  }

  approvalInput.setAttribute('value', value);
  form.submit();
}
</script>


<script>
    $(document).ready(function() {
        //    $('#loadingModal').modal('show')
    });
</script>
@endpush
@endsection