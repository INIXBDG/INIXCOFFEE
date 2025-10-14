@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>

                   <h5 class="card-title">
                        Detail Pengajuan {{ $data->lab ? 'Lab' : ($data->subs ? 'Subscription' : '-') }}
                    </h5>

                    <div class="row">
                        <!-- ===================== INFORMASI KARYAWAN ===================== -->
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-4"><p>Nama Karyawan</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>{{ $data->karyawan->nama_lengkap ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Divisi</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>{{ $data->karyawan->divisi ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Jabatan</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>{{ $data->karyawan->jabatan ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Tipe Pengajuan</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    <p>{{ $data->lab ? 'Lab' : 'Subscription' }}</p>
                                </div>

                                <div class="col-md-4"><p>Invoice</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    @if ($data->invoice)
                                        <a href="{{ asset('storage/pengajuanlabsubs/'.$data->invoice) }}" class="btn btn-sm btn-primary" target="_blank">Lihat Invoice</a>
                                    @else
                                        <p>-</p>
                                    @endif
                                </div>

                                <div class="col-md-4"><p>RKM</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    @if($data->rkm)
                                        {{ $data->rkm->perusahaan->nama_perusahaan ?? '-' }}
                                        ({{ $data->rkm->materi->nama_materi ?? '-' }})
                                        <br>
                                        <small class="text-muted">
                                            {{ $data->rkm->tanggal_awal ? \Carbon\Carbon::parse($data->rkm->tanggal_awal)->format('d M Y') : '-' }}
                                            –
                                            {{ $data->rkm->tanggal_akhir ? \Carbon\Carbon::parse($data->rkm->tanggal_akhir)->format('d M Y') : '-' }}
                                        </small>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- ===================== DETAIL LAB / SUBS ===================== -->
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="col-md-12" style="display: flex; justify-content: space-between;">
                                        <h5 class="card-title">Detail {{ $data->lab ? 'Lab' : 'Subscription' }}</h5>

                                        @php
                                            $jabatan = auth()->user()->jabatan;
                                            $id_karyawan = auth()->user()->karyawan_id;

                                            // Cek kelengkapan data
                                            if ($data->lab) {
                                                $isComplete = !empty($data->lab->nama_labs)
                                                    && !empty($data->lab->harga)
                                                    && !empty($data->lab->mata_uang)
                                                    && (!empty($data->lab->start_date) && !empty($data->lab->end_date));
                                            } elseif ($data->subs) {
                                                $isComplete = !empty($data->subs->nama_subs)
                                                    && !empty($data->subs->harga)
                                                    && !empty($data->subs->mata_uang)
                                                    && (!empty($data->subs->start_date) && !empty($data->subs->end_date));
                                            } else {
                                                $isComplete = false;
                                            }
                                        @endphp

                                        <div>
                                            <a href="{{ $isComplete ? route('pengajuanlabsdansubs.exportpdf', $data->id) : '#' }}"
                                            target="{{ $isComplete ? '_blank' : '' }}"
                                            class="btn btn-danger {{ !$isComplete ? 'disabled' : '' }}"
                                            title="{{ $isComplete ? 'Export PDF' : 'Lengkapi data terlebih dahulu' }}">
                                                Export PDF
                                            </a>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <tbody>
                                                {{-- ===================== LAB ===================== --}}
                                                @if ($data->lab)
                                                    <tr><td>Nama Lab</td><td>{{ $data->lab->nama_labs ?? '-' }}</td></tr>
                                                    <tr><td>Deskripsi</td><td>{{ $data->lab->desc ?? '-' }}</td></tr>
                                                    <tr>
                                                        <td>URL Lab</td>
                                                        <td>
                                                            @if (!empty($data->lab->lab_url))
                                                                <a href="{{ $data->lab->lab_url }}" target="_blank">
                                                                    Lihat URL Lab
                                                                </a>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr><td>Kode Akses</td><td>{{ $data->lab->access_code ?? '-' }}</td></tr>
                                                    <tr><td>Durasi (menit)</td><td>{{ $data->lab->duration_minutes ?? '-' }}</td></tr>
                                                    <tr><td>Mata Uang</td><td>{{ $data->lab->mata_uang ?? '-' }}</td></tr>
                                                    <tr><td>Harga</td><td>{{ number_format($data->lab->harga, 2, ',', '.') ?? '-' }}</td></tr>
                                                    <tr><td>Kurs</td><td>{{ $data->lab->kurs ? number_format($data->lab->kurs, 2, ',', '.') : '-' }}</td></tr>
                                                    <tr><td>Harga (Rupiah)</td><td>{{ $data->lab->harga_rupiah ? 'Rp '.number_format($data->lab->harga_rupiah, 0, ',', '.') : '-' }}</td></tr>
                                                    <tr><td>Mulai</td><td>{{ $data->lab->start_date ? \Carbon\Carbon::parse($data->lab->start_date)->format('d M Y') : '-' }}</td></tr>
                                                    <tr><td>Berakhir</td><td>{{ $data->lab->end_date ? \Carbon\Carbon::parse($data->lab->end_date)->format('d M Y') : '-' }}</td></tr>
                                                    <tr><td>Status</td><td>{{ ucfirst($data->lab->status ?? '-') }}</td></tr>

                                                {{-- ===================== SUBS ===================== --}}
                                                @elseif ($data->subs)
                                                    <tr><td>Nama Subscription</td><td>{{ $data->subs->nama_subs ?? '-' }}</td></tr>
                                                    <tr><td>Merk</td><td>{{ $data->subs->merk ?? '-' }}</td></tr>
                                                    <tr><td>Deskripsi</td><td>{{ $data->subs->desc ?? '-' }}</td></tr>
                                                    <tr>
                                                        <td>URL</td>
                                                        <td>
                                                            @if (!empty($data->subs->subs_url))
                                                                <a href="{{ $data->subs->subs_url }}" target="_blank">
                                                                    Lihat URL Subscription
                                                                </a>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr><td>Kode Akses</td><td>{{ $data->subs->access_code ?? '-' }}</td></tr>
                                                    <tr><td>Mata Uang</td><td>{{ $data->subs->mata_uang ?? '-' }}</td></tr>
                                                    <tr><td>Harga</td><td>{{ number_format($data->subs->harga, 2, ',', '.') ?? '-' }}</td></tr>
                                                    <tr><td>Kurs</td><td>{{ $data->subs->kurs ? number_format($data->subs->kurs, 2, ',', '.') : '-' }}</td></tr>
                                                    <tr><td>Harga (Rupiah)</td><td>{{ $data->subs->harga_rupiah ? 'Rp '.number_format($data->subs->harga_rupiah, 0, ',', '.') : '-' }}</td></tr>
                                                    <tr><td>Mulai</td><td>{{ $data->subs->start_date ? \Carbon\Carbon::parse($data->subs->start_date)->format('d M Y') : '-' }}</td></tr>
                                                    <tr><td>Berakhir</td><td>{{ $data->subs->end_date ? \Carbon\Carbon::parse($data->subs->end_date)->format('d M Y') : '-' }}</td></tr>
                                                    <tr><td>Status</td><td>{{ ucfirst($data->subs->status ?? '-') }}</td></tr>

                                                @else
                                                    <tr>
                                                        <td colspan="2" class="text-center">Belum ada detail Lab / Subscription</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- ===================== TRACKING APPROVAL ===================== -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h5 class="card-title">Tracking Pengajuan</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($data->tracking as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                                                        <td>{{ $item->tracking }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center">Belum ada data tracking</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- end col-md-7 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
