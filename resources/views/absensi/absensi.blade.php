@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

@section('content')
@foreach ($cancelLeave as $data)
<div class="modal fade" id="modalApproveCancelLeave{{ $data->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="false" style="background-color: transparent !important;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formApproval{{ $data->id }}" action="{{ route('absensi.approveCancelLeave') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel{{ $data->id }}">Approve</h5>
                </div>
                <div class="modal-body text-start">
                    @if($data->bukti_gambar)
                    <input type="hidden" name="id_absen" id="id_absen" value="{{ $data->id }}">
                    <input type="hidden" name="id_karyawan" id="id_karyawan" value="{{ $data->id_karyawan }}">
                    <input type="hidden" name="approval" id="approvalInput{{ $data->id }}">

                    <div class="btn-group mb-3" role="group">
                        <input type="button" class="btn btn-primary" value="Ya" onclick="submitApproval('{{ $data->id }}', 1)">
                        <input type="button" class="btn btn-danger" value="Tidak" onclick="showTextarea('{{ $data->id }}')">
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
            <form id="formApproval{{ $data->id }}" action="{{ route('absensi.approveSchemeWork') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel{{ $data->id }}">Approve</h5>
                </div>
                <div class="modal-body text-start">
                    @if($data->bukti_gambar)
                    <input type="hidden" name="id_absen" id="id_absen" value="{{ $data->id_absen }}">
                    <input type="hidden" name="id_karyawan" id="id_karyawan" value="{{ $data->id_karyawan }}">
                    <input type="hidden" name="approval" id="approvalInput{{ $data->id }}">

                    <div class="btn-group mb-3" role="group">
                        <input type="button" class="btn btn-primary" value="Ya" onclick="submitApproval('{{ $data->id }}', 1)">
                        <input type="button" class="btn btn-danger" value="Tidak" onclick="showTextarea('{{ $data->id }}')">
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
@foreach ($noRecord as $data)
<div class="modal fade" id="modalApproveNoRecord{{ $data->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="false" style="background-color: transparent !important;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formApproval{{ $data->id }}" action="{{ route('absensi.approveNoRecord') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel{{ $data->id }}">Approve</h5>
                </div>
                <div class="modal-body text-start">
                    @if($data->bukti_gambar)
                    <input type="hidden" name="id_absen" id="id_absen" value="{{ $data->id_absen }}">
                    <input type="hidden" name="id_karyawan" id="id_karyawan" value="{{ $data->id_karyawan }}">
                    <input type="hidden" name="approval" id="approvalInput{{ $data->id }}">

                    <div class="btn-group mb-3" role="group">
                        <input type="button" class="btn btn-primary" value="Ya" onclick="submitApproval('{{ $data->id }}', 1)">
                        <input type="button" class="btn btn-danger" value="Tidak" onclick="showTextarea('{{ $data->id }}')">
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
<div class="modal fade" id="DetailcancelLeaveModal{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $data->id }}" aria-hidden="true" data-backdrop="false" data-keyboard="false" style="background-color: transparent !important;">
    <div class="modal-dialog" role="document">
        <div class="modal-content p-3">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel{{ $data->id }}">Detail Pembatalan Cuti</h5>
            </div>
            <div class="modal-body">
                <table style="width: 100%; font-size: 14px;">
                    <tr>
                        <td style="font-weight: bold; width: 35%;">Tanggal Awal</td>
                        <td> : {{ \Carbon\Carbon::parse($data->tanggal_awal)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Tanggal Akhir</td>
                        <td> : {{ \Carbon\Carbon::parse($data->tanggal_akhir)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Durasi</td>
                        <td> : {{ $data->durasi }} Hari</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Kontak</td>
                        <td> : Hp.{{ $data->kontak ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Tipe Cuti</td>
                        <td> : {{ $data->tipe ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Alasan</td>
                        <td> : {{ $data->alasan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Kronologi</td>
                        <td> : {{ $data->kronologi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; vertical-align: top;">Bukti Gambar</td>
                        <td>
                            @if($data->bukti_gambar)
                            <div class="frame-wrapper" style="position: relative; display: inline-block;">
                                <img src="{{ asset($data->bukti_gambar) }}" alt="Bukti Gambar" class="img-fluid rounded" width="200px" height="200px">

                                <!-- Frame corner lines -->
                                <div class="corner tl"></div>
                                <div class="corner tr"></div>
                                <div class="corner bl"></div>
                                <div class="corner br"></div>

                                <!-- Frame corner dots -->
                                <div class="dot tl"></div>
                                <div class="dot tr"></div>
                                <div class="dot bl"></div>
                                <div class="dot br"></div>
                            </div>
                            @else
                            <p class="text-muted">Tidak ada bukti gambar.</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<div class="container-fluid">
    <!-- Modal Spinner -->
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
    <div class="row">
        <div class="col-md-6">
            <div class="row my-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h2>Total Keterlambatan Bulan ini :
                                {{ $totalketerlambatan->total_keterlambatan ?? '0 menit' }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    use App\Models\izinTigaJam;
@endphp

<div class="row my-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-responsive">
                Data Kehadiran Anda bulan ini
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Keterangan Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Keterangan Pulang</th>
                            <th>Waktu Keterlambatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($absen as $item)
                            @php
                                $isToday = Carbon::parse($item->tanggal)->isToday();

                                // Ambil data izin 3 jam jika ada
                                $izin = izinTigaJam::where('id_karyawan', auth()->user()->karyawan_id)
                                        ->whereDate('tanggal_pengajuan', $item->tanggal)
                                        ->where('approval', 2)
                                        ->first();

                                // Keterangan Masuk: ambil dari field keterangan
                                $keteranganMasuk = $item->jam_masuk ? $item->keterangan : '-';

                                // Default keterangan pulang
                                $ket_pul = '-';

                                if ($item->jam_keluar) {
                                    if ($izin) {
                                        $jamMulaiIzin = Carbon::createFromFormat('H:i:s', $izin->jam_mulai);
                                        $jamSelesaiIzin = Carbon::createFromFormat('H:i:s', $izin->jam_selesai);

                                        if ($jamMulaiIzin->greaterThan(Carbon::createFromTime(12, 0))) {
                                            $ket_pul = 'Pulang - Izin 3 Jam (' . $jamMulaiIzin->format('H:i') . ' - ' . $jamSelesaiIzin->format('H:i') . ')';
                                        } else {
                                            $ket_pul = 'Pulang';
                                        }
                                    } else {
                                        $ket_pul = 'Pulang';
                                    }
                                }
                            @endphp
                            <tr class="{{ $isToday ? 'tabel-custom' : '' }}">
                                <td>{{ Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}</td>
                                <td>{{ $item->jam_masuk ?? '-' }}</td>
                                <td>{{ $keteranganMasuk }}</td>
                                <td>{{ $item->jam_keluar ?? '-' }}</td>
                                <td>{{ $ket_pul }}</td>
                                <td>{{ $item->waktu_keterlambatan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>



             <div class="row my-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="col-md-12">
                                <div class="card-body table-responsive">
                                    <h6>Ajukan Klaim Absen Anda:</h6>
                                    <a href="{{ route('pengajuanklaim.createNoRecord') }}" class="btn btn-info color-white">Absen Tidak Terekap</a>
                                    <a href="{{ route('absensi.schemeWork') }}" class="btn btn-warning">Perubahan Jam Kerja</a>
                                    <a href="{{ route('absensi.cancelLeave') }}" class="btn btn-danger">Pembatalan Cuti</a>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="card-body table-responsive text-end justify-content-end">
                                    <h6 class="justify-content-start">Pilih Tabel:</h6>
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        <select id="jenis_tabel" class="form-select w-auto">
                                            <option value="Tidak Terekam" selected>Tidak Terekam</option>
                                            <option value="Skema Kerja">Skema Kerja</option>
                                            <option value="Pembatalan Cuti">Pembatalan Cuti</option>
                                        </select>

                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive jenis-table" id="table-tidak-terekam">
                                <h6>Data Pengajuan Klaim Absen Tidak Terekam</h6>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Kendala</th>
                                            <th>Kronologi</th>
                                            <th>Approve</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($noRecord as $data)
                                        <tr>
                                            <td>
    {{ \Carbon\Carbon::parse($data->absensiKaryawan->tanggal ?? $data->tanggal)->translatedFormat('l, d F Y') }}
</td>

                                            <td>{{ $data->kendala }}</td>
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
                                            <td style="font-size: 14px;">
                                                <div class="btn-group dropup">
                                                    <button type="button" class="btn dropdown-toggle btn-secondary" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                                    <div class="dropdown-menu">
                                                        <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Lihat Bukti" data-target="#modalNoRecord{{ $data->id }}">
                                                            <span><img src="{{ asset('icon/eye.svg') }}" alt="eye.png" width="20px" height="20px"></span> Bukti Gambar
                                                        </button>
                                                        <form action="{{ route('absensi.deleteNoRecord') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" value="{{ $data->id }}" name="id_noRecord">
                                                            <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Lihat Bukti">
                                                                <span><img src="{{ asset('icon/trash-danger.svg') }}" alt="eye.png" width="20px" height="20px"></span> Hapus Data
                                                            </button>
                                                        </form>
                                                        @if(auth()->user()->jabatan === 'HRD')
                                                        @if($data->approval === 1)
                                                        @elseif($data->approval === 0)
                                                        <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Approve Pengajuan" data-target="#modalApproveNoRecord{{ $data->id }}">
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

                            <div class="table-responsive jenis-table d-none" id="table-skema-kerja">
                                <h6>Data Pengajuan Klaim Keterlambatan Karena Perubahan Skema Kerja</h6>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Kronologi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($schemeWork as $data)
                                        @if ($data->jenis_PK === 'Scheme Work')
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($data->absensiKaryawan->tanggal)->translatedFormat('l, d F Y') }}</td>
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
                                            <td style="font-size: 14px;">
                                                <div class="btn-group dropup">
                                                    <button type="button" class="btn dropdown-toggle btn-secondary" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
                                                    <div class="dropdown-menu">
                                                        <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Lihat Bukti" data-target="#schemeWorkModal{{ $data->id }}">
                                                            <span><img src="{{ asset('icon/eye.svg') }}" alt="eye.png" width="20px" height="20px"></span> Bukti Gambar
                                                        </button>
                                                        <form action="{{ route('absensi.deleteSchemeWork') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" value="{{ $data->id }}" name="id_scheme_work">
                                                            <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Lihat Bukti">
                                                                <span><img src="{{ asset('icon/trash-danger.svg') }}" alt="eye.png" width="20px" height="20px"></span> Hapus Data
                                                            </button>
                                                        </form>
                                                        @if(auth()->user()->jabatan === 'HRD')
                                                        @if($data->approval === 1)
                                                        @elseif($data->approval === 0)
                                                        <button class="dropdown-item" data-toggle="modal" data-toggle="tooltip" title="Approve Pengajuan" data-target="#modalApproveSchemeWork{{ $data->id }}">
                                                            <span><img src="{{ asset('icon/clipboard-primary.svg') }}" alt="eye.png" width="20px" height="20px"></span> Approve
                                                        </button>
                                                        @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-responsive jenis-table d-none" id="table-pembatalan-cuti">
                                <h6>Data Pembatalan Cuti</h6>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Tanggal Cuti</th>
                                            <th>Alasan Pembatalan</th>
                                            <th>Status</th>
                                            <th>aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cancelLeave as $data)
                                        <tr>
                                            <td>{{ $data->tanggal_awal }}</td>
                                            <td>{{ $data->alasan }}</td>
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

                                                        <form action="{{ route('absensi.deleteCancelLeave') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" value="{{ $data->id }}" name="id_cancel_leave">
                                                            <button type="submit" class="dropdown-item" data-toggle="tooltip" title="Lihat Bukti">
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
        <div class="col-md-6">
            <div class="card my-2">
                <div class="card-body table-responsive">
                    <h4>Leaderboard</h4>
                    <p>Top 3 Karyawan yang terlambat bulan ini :</p>
                    {{-- {{ $topKaryawan->karyawan->foto }} --}}
                    <div class="row justify-content-center">
                        <div class="container profile-container">
                            <div class="row justify-content-center">
                                <!-- Second position on the left -->
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center">
                                    <div class="circle second-position">
                                        <img src="{{asset('css/b2.png')}}" alt="" class="position-badge">
                                        <img src="{{ isset($topKaryawan[1]->foto) ? asset('storage/'.$topKaryawan[1]->foto) : asset('css/default-profile.jpg') }}" alt="Foto Karyawan" class="profile-photo">
                                    </div>
                                </div>

                                <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center">
                                    <div class="circle-satu first-position">
                                        <img src="{{asset('css/b1.png')}}" alt="" class="position-badge">
                                        <img src="{{ isset($topKaryawan[0]->foto) ? asset('storage/'.$topKaryawan[0]->foto) : asset('css/default-profile.jpg') }}" alt="Foto Karyawan" class="profile-photo-satu">
                                    </div>
                                </div>

                                <!-- Third position on the right -->
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center">
                                    <div class="circle third-position">
                                        <img src="{{asset('css/b3.png')}}" alt="" class="position-badge">
                                        <img src="{{ isset($topKaryawan[2]->foto) ? asset('storage/'.$topKaryawan[2]->foto) : asset('css/default-profile.jpg') }}" alt="Foto Karyawan" class="profile-photo">

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <p>Karyawan yang terlambat bulan ini:</p>
                    <h5>
                        <table>
                            <tbody>
                                @foreach ($topKaryawan as $item)
                                <tr>
                                    <td>{{$loop->iteration}}.</td>
                                    <td>{{ $item->karyawan->nama_lengkap }} dengan waktu keterlambatan {{$item->total_keterlambatan}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Karyawan</th>
                                <th>Waktu Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- {{$remainingLeaderboard}} --}}
                            @if($remainingLeaderboard->isNotEmpty())
                            @foreach ($remainingLeaderboard as $item)
                            <tr>
                                <td>{{ $item->karyawan->nama_lengkap }}</td>
                                <td>{{ $item->total_keterlambatan }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="2" class="text-center">Tidak ada data karyawan yang terlambat</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .frame-wrapper {
        position: relative;
        display: inline-block;
        padding: 20px;
        background-color: #f9f9f9;
        /* Optional */
    }

    .frame-image {
        display: block;
        max-width: 100%;
        border-radius: 5px;
    }

    /* Corner line styles */
    .corner {
        position: absolute;
        width: 40px;
        height: 40px;
    }

    .tabel-custom{
        /* border-collapse: collapse; */
        background-color: #1d1d1d;
        color: #f0f0f0;
    }
    .corner::before,
    .corner::after {
        content: '';
        position: absolute;
        background-color: black;
    }

    .corner.tl {
        top: 0;
        left: 0;
    }

    .corner.tr {
        top: 0;
        right: 0;
        transform: rotateY(180deg);
    }

    .corner.bl {
        bottom: 0;
        left: 0;
        transform: rotateX(180deg);
    }

    .corner.br {
        bottom: 0;
        right: 0;
        transform: rotate(180deg);
    }

    .corner::before {
        width: 30px;
        height: 3px;
        top: 0;
        left: 0;
    }

    .corner::after {
        width: 3px;
        height: 30px;
        top: 0;
        left: 0;
    }

    /* Dot styles */
    .dot {
        width: 6px;
        height: 6px;
        background-color: black;
        border-radius: 50%;
        position: absolute;
    }

    .dot.tl {
        top: -3px;
        left: -3px;
    }

    .dot.tr {
        top: -3px;
        right: -3px;
    }

    .dot.bl {
        bottom: -3px;
        left: -3px;
    }

    .dot.br {
        bottom: -3px;
        right: -3px;
    }

    .modal-content {
        background-color: white;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    }

    .modal-backdrop {
        display: none !important;
    }

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

    .container {
        padding: 0;
    }

    .profile-container {
        width: 100%;
        max-width: 700px;
        height: 500px;
        background-size: cover;
        background-position: center;
        background-image: url('/css/podiumkorea.png');
        background-color: #f0f0f0;
        /* Optional background for visual aid */
        margin: 0 auto;
        position: relative;
        overflow-x: auto;
        /* Allow horizontal scrolling when screen is too small */
    }

    /* Circle styles */
    .circle,
    .circle-satu {
        background-color: #6b52cc;
        border-radius: 50%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    /* Default sizes */
    .circle {
        width: 150px;
        height: 150px;
    }

    .circle-satu {
        width: 170px;
        height: 170px;
    }

    /* Profile photo adjustments for each circle */
    .profile-photo {
        width: 130px;
        height: 130px !important;
        border-radius: 50%;
        object-fit: cover;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .profile-photo-satu {
        width: 150px;
        height: 150px !important;
        border-radius: 50%;
        object-fit: cover;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Custom positioning for each podium place */
    .second-position {
        position: fixed;
        bottom: 38%;
        left: 10%;
    }

    .first-position {
        position: fixed;
        bottom: 45%;
        left: 51%;
        transform: translateX(-50%);
    }

    .third-position {
        position: fixed;
        bottom: 34%;
        right: 9.5%;
    }

    /* Position badge */
    .position-badge {
        position: absolute;
        top: -20px;
        right: -58px;
        padding: 5px;
        border-radius: 10px;
        font-size: 0.8rem;
    }
    @media (max-width: 576px) {
        /* Adjust positioning for mobile */
        .second-position {
            bottom: 45%;
            left: 18%;
        }

        .first-position {
            bottom: 50%;
            left: 51%;
            transform: translateX(-50%);
        }

        .third-position {
            bottom: 41%;
            right: 17%;
        }
    }
    /* Responsive adjustments for mobile screens */
    @media (max-width: 576px) {
        .profile-container {
            height: 260px;
            width: 360px;
        }

        /* Resize circles for mobile */
        .circle,
        .circle-satu {
            width: 100px;
            height: 100px;
        }

        /* Resize profile photos for mobile */
        .profile-photo {
            width: 80px;
            height: 80px !important;
        }

        .profile-photo-satu {
            width: 90px;
            height: 90px !important;
        }

        /* Adjust positioning for mobile */
        .second-position {
            bottom: 50%;
            left: -2%;
        }

        .first-position {
            bottom: 55%;
            left: 51%;
            transform: translateX(-50%);
        }

        .third-position {
            bottom: 47%;
            right: -2%;
        }

        /* Adjust position badge size for mobile */
        .position-badge {
            top: -10px;
            right: -40px;
            padding: 3px;
            font-size: 0.7rem;
        }
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>


<script>
    $(document).ready(function() {
        if (window.location.hash.startsWith('#modal')) {
            history.replaceState(null, null, ' ');
            $('.modal').modal('hide');
        }
    });

    function submitApproval(id, value) {
        document.getElementById('approvalInput' + id).value = value;

        if (value == 2) {
            const alasan = document.getElementById('alasan_approval' + id).value.trim();
            if (alasan === '') {
                alert('Silakan isi alasan terlebih dahulu.');
                return;
            }
        }

        document.getElementById('formApproval' + id).submit();
    }

    function showTextarea(id) {
        document.getElementById('textareaDiv' + id).classList.remove('d-none');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dropdown = document.getElementById('jenis_tabel');

        function showTable(jenis, updateURL = true) {
            document.querySelectorAll('.jenis-table').forEach(table => {
                table.classList.add('d-none');
            });

            if (jenis === 'Tidak Terekam') {
                document.getElementById('table-tidak-terekam').classList.remove('d-none');
            } else if (jenis === 'Skema Kerja') {
                document.getElementById('table-skema-kerja').classList.remove('d-none');
            } else if (jenis === 'Pembatalan Cuti') {
                document.getElementById('table-pembatalan-cuti').classList.remove('d-none');
            }

            if (updateURL) {
                const urlParams = new URLSearchParams(window.location.search);
                if (jenis === 'Tidak Terekam') {
                    urlParams.set('page', 'no_record');
                } else if (jenis === 'Skema Kerja') {
                    urlParams.set('page', 'scheme_work');
                } else if (jenis === 'Pembatalan Cuti') {
                    urlParams.set('page', 'cancel_leave');
                }
                const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                window.history.replaceState({}, '', newUrl);
            }
        }

        dropdown.addEventListener('change', function() {
            showTable(this.value);
        });

        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        let selectedValue = 'Tidak Terekam';

        if (page === 'scheme_work') {
            selectedValue = 'Skema Kerja';
        } else if (page === 'cancel_leave') {
            selectedValue = 'Pembatalan Cuti';
        }

        dropdown.value = selectedValue;
        showTable(selectedValue, false);
    });
</script>
<script>
    $(document).ready(function() {
        //    $('#loadingModal').modal('show')
    });
</script>
@endpush
@endsection