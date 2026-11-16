@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('pengajuanlabsdansubs.index') }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>

                    <h5 class="card-title fw-bold mb-3">
                        Detail Permintaan & Akses Lab
                    </h5>

                    @php
                        $userJabatan = auth()->user()->karyawan->jabatan ?? '';
                        $canSeeFinance = in_array($userJabatan, ['Koordinator ITSM', 'Technical Support', 'Finance & Accounting', 'GM', 'Education Manager']);
                        $displayData = $data->lab_snapshot ?? ($data->lab ? $data->lab->toArray() : null);

                        $tracksText = $data->tracking->pluck('tracking')->implode(' ');
                        $isAccessGranted = str_contains(strtolower($tracksText), 'lihat akses nya di detail') || str_contains(strtolower($tracksText), 'selesai');
                    @endphp

                    <div class="row">
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-4"><p>Nama Peminta</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p class="fw-bold">{{ $data->karyawan->nama_lengkap ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Divisi / Jabatan</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>{{ $data->karyawan->divisi ?? '-' }} / {{ $data->karyawan->jabatan ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Kategori Eksekusi</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    <p>
                                        @if($data->jenis_transaksi === 'existing')
                                            <span class="badge bg-success">Permintaan Akses Lab</span>
                                        @elseif($data->jenis_transaksi === 'baru')
                                            <span class="badge bg-info text-dark">Pengadaan Lab Baru</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Perpanjangan Masa Aktif Lab</span>
                                        @endif
                                    </p>
                                </div>

                                @if(!empty($displayData['handled_by']))
                                <div class="col-md-4"><p>Ditangani Oleh (ITSM)</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    <p>{{ $displayData['handled_by'] }} ({{ $displayData['handled_by_jabatan'] ?? 'ITSM' }})</p>
                                </div>
                                @endif

                                @if(isset($pengajuanBarang) && $pengajuanBarang)
                                <div class="col-md-4"><p>Pengaju Barang</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    <p class="text-primary fw-bold">
                                        {{ $pengajuanBarang->karyawan->nama_lengkap ?? '-' }} ({{ $pengajuanBarang->karyawan->jabatan ?? '-' }})
                                    </p>
                                </div>
                                @endif

                                @if($canSeeFinance)
                                <div class="col-md-4"><p>Invoice</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    @if ($data->invoice)
                                        <a href="{{ asset('storage/pengajuanlabsubs/'.$data->invoice) }}" class="btn btn-sm btn-primary" target="_blank">Lihat Invoice</a>
                                    @else
                                        <p>-</p>
                                    @endif
                                </div>
                                @endif

                                <div class="col-md-4"><p>Jadwal Kelas (RKM)</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    @if($data->rkm)
                                        <strong>{{ $data->rkm->perusahaan->nama_perusahaan ?? '-' }}</strong><br>
                                        Materi: {{ $data->rkm->materi->nama_materi ?? '-' }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $data->rkm->tanggal_awal ? \Carbon\Carbon::parse($data->rkm->tanggal_awal)->format('d M Y') : '-' }}
                                            –
                                            {{ $data->rkm->tanggal_akhir ? \Carbon\Carbon::parse($data->rkm->tanggal_akhir)->format('d M Y') : '-' }}
                                        </small>
                                    @else
                                        <span class="text-muted">Perpanjangan Master Lab (Non-RKM)</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="col-md-12 d-flex justify-content-between mb-2">
                                        <h5 class="card-title fw-bold">Informasi Akses & Teknis Lab</h5>
                                        @if($canSeeFinance && $isAccessGranted)
                                        <div>
                                            <a href="{{ route('pengajuanlabsdansubs.exportpdf', $data->id) }}" target="_blank" class="btn btn-sm btn-danger">
                                                Export PDF
                                            </a>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <tbody>
                                                @if ($isAccessGranted || in_array($userJabatan, ['Koordinator ITSM', 'Technical Support', 'Finance & Accounting']))
                                                    <tr>
                                                        <td width="35%">Nama Lab / Software</td>
                                                        <td class="fw-bold">{{ $displayData['nama_labs'] ?? $data->lab->nama_labs ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Vendor / Merk</td>
                                                        <td>{{ $displayData['merk'] ?? $data->lab->merk ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>URL Akses Lab</td>
                                                        <td>
                                                            @php $url = $displayData['lab_url'] ?? $data->lab->lab_url ?? null; @endphp
                                                            @if (!empty($url))
                                                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-success">Buka URL Lab</a>
                                                            @else
                                                                <span class="text-muted">Belum tersedia</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Kode Akses / Key</td>
                                                        <td>
                                                            <code class="fs-6 text-dark fw-bold">{{ $displayData['access_code'] ?? $data->lab->access_code ?? '-' }}</code>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Durasi (Menit)</td>
                                                        <td>{{ $displayData['duration_minutes'] ?? $data->lab->duration_minutes ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Catatan / Deskripsi</td>
                                                        <td>{{ $displayData['desc'] ?? $data->lab->desc ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Masa Berlaku Lab</td>
                                                        <td>
                                                            {{ !empty($displayData['start_date']) ? \Carbon\Carbon::parse($displayData['start_date'])->format('d M Y') : '-' }}
                                                            s/d
                                                            {{ !empty($displayData['end_date']) ? \Carbon\Carbon::parse($displayData['end_date'])->format('d M Y') : '-' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Status Lab</td>
                                                        <td><span class="badge bg-primary">{{ ucfirst($displayData['status'] ?? $data->lab->status ?? '-') }}</span></td>
                                                    </tr>

                                                    @if($canSeeFinance && ($data->jenis_transaksi === 'baru' || $data->jenis_transaksi === 'pembaharuan'))
                                                        <tr><td>Mata Uang</td><td>{{ $displayData['mata_uang'] ?? '-' }}</td></tr>
                                                        <tr><td>Harga Asli</td><td>{{ number_format((float)($displayData['harga'] ?? 0), 2, ',', '.') }}</td></tr>
                                                        <tr><td>Kurs</td><td>{{ number_format((float)($displayData['kurs'] ?? 1), 2, ',', '.') }}</td></tr>
                                                        <tr><td>Total Estimasi (Rupiah)</td><td class="fw-bold">{{ isset($displayData['harga_rupiah']) ? 'Rp '.number_format((float)$displayData['harga_rupiah'], 0, ',', '.') : '-' }}</td></tr>
                                                    @endif
                                                @else
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted py-4">
                                                            <em>Permintaan Lab <strong>{{ $displayData['requested_nama_labs'] ?? $data->lab->nama_labs ?? '' }}</strong> sedang diproses. Detail URL & Kode Akses akan muncul setelah diberikan oleh Tim ITSM.</em>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">Tracking Status Permintaan</h5>
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
                                                @forelse ($data->tracking->unique('tracking')->values() as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y H:i') }}</td>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
