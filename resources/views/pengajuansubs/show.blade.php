@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('pengajuansubs.index') }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>

                    <h5 class="card-title fw-bold mb-3">
                        Detail Permintaan & Akses Subscription
                    </h5>

                    @php
                        $userJabatan = auth()->user()->karyawan->jabatan ?? '';
                        $canSeeFinance = in_array($userJabatan, ['Koordinator ITSM', 'Technical Support', 'Finance & Accounting', 'GM', 'Education Manager', 'SPV Sales']);
                        $displayData = $data->subs_snapshot ?? ($data->subs ? $data->subs->toArray() : null);

                        $qty = (int) ($displayData['qty'] ?? $displayData['requested_qty'] ?? 1);

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

                                <div class="col-md-4"><p>Jumlah Akun Diminta</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    <p class="fw-bold text-dark mb-1">
                                        {{ $displayData['requested_qty'] ?? $qty }} Akun / Lisensi
                                    </p>
                                    @if(!empty($displayData['partial_fulfillment']))
                                        <small class="text-muted d-block">
                                            • Dipenuhi dari Stok Existing: <strong>{{ $displayData['fulfilled_existing_qty'] ?? 0 }} Akun</strong>
                                            @if(!empty($displayData['fulfilled_existing_users']))
                                                ({{ implode(', ', (array)$displayData['fulfilled_existing_users']) }})
                                            @endif
                                            <br>
                                            • Pengadaan Baru ke Finance: <strong>{{ $qty }} Akun</strong>
                                        </small>
                                    @endif
                                </div>

                                <div class="col-md-4"><p>Kategori Eksekusi</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    <p class="fw-bold text-dark">
                                        @if($data->jenis_transaksi === 'existing')
                                            Permintaan Akses Subscription
                                        @elseif($data->jenis_transaksi === 'baru')
                                            Pengadaan Subscription Baru
                                        @else
                                            Perpanjangan Masa Aktif Subscription
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
                                        <a href="{{ asset('storage/pengajuansubs/'.$data->invoice) }}" class="btn btn-sm btn-primary" target="_blank">Lihat Invoice</a>
                                    @else
                                        <p>-</p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="col-md-12 d-flex justify-content-between mb-2">
                                        <h5 class="card-title fw-bold">Informasi Akses & Teknis Subscription</h5>
                                        @if($canSeeFinance && $isAccessGranted)
                                        <div>
                                            <a href="{{ route('pengajuansubs.exportpdf', $data->id) }}" target="_blank" class="btn btn-sm btn-danger">
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
                                                        <td width="35%">Nama Subscription / Software</td>
                                                        <td class="fw-bold">{{ $displayData['nama_subs'] ?? $data->subs->nama_subs ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Vendor / Merk</td>
                                                        <td>{{ $displayData['merk'] ?? $data->subs->merk ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>URL Akses Subscription</td>
                                                        <td>
                                                            @php $url = $displayData['subs_url'] ?? $data->subs->subs_url ?? null; @endphp
                                                            @if (!empty($url))
                                                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-success">Buka URL Subscription</a>
                                                            @else
                                                                <span class="text-muted">Belum tersedia</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Kode Akses / Key</td>
                                                        <td>
                                                            <code class="fs-6 text-dark fw-bold">{{ $displayData['access_code'] ?? $data->subs->access_code ?? '-' }}</code>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Catatan / Deskripsi</td>
                                                        <td>{{ $displayData['desc'] ?? $data->subs->desc ?? ($displayData['requested_catatan'] ?? '-') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Masa Berlaku Subscription</td>
                                                        <td>
                                                            {{ !empty($displayData['start_date']) ? \Carbon\Carbon::parse($displayData['start_date'])->format('d M Y') : '-' }}
                                                            s/d
                                                            {{ !empty($displayData['end_date']) ? \Carbon\Carbon::parse($displayData['end_date'])->format('d M Y') : '-' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Status Subscription</td>
                                                        <td class="fw-bold text-dark">{{ ucfirst($displayData['status'] ?? $data->subs->status ?? '-') }}</td>
                                                    </tr>

                                                    @if($canSeeFinance && ($data->jenis_transaksi === 'baru' || $data->jenis_transaksi === 'pembaharuan'))
                                                        @php
                                                            $hargaAsli = (float) ($displayData['harga'] ?? $data->subs->harga ?? 0);
                                                            $kursVal = (float) ($displayData['kurs'] ?? $data->subs->kurs ?? 1);
                                                            $hargaSatuanRp = (float) ($displayData['harga_satuan_rupiah'] ?? ($hargaAsli * $kursVal));
                                                            $totalRp = (float) ($displayData['harga_rupiah'] ?? ($hargaSatuanRp * $qty));
                                                        @endphp
                                                        <tr><td>Mata Uang</td><td>{{ $displayData['mata_uang'] ?? '-' }}</td></tr>
                                                        <tr><td>Harga Satuan Asli</td><td>{{ number_format($hargaAsli, 2, ',', '.') }}</td></tr>
                                                        <tr><td>Kurs</td><td>{{ number_format($kursVal, 2, ',', '.') }}</td></tr>
                                                        <tr><td>Harga Satuan (Rupiah)</td><td>Rp {{ number_format($hargaSatuanRp, 0, ',', '.') }}</td></tr>
                                                        <tr><td>Jumlah Akun (Qty)</td><td class="fw-bold">{{ $qty }} Akun</td></tr>
                                                        <tr><td>Total Estimasi (Rupiah)</td><td class="fw-bold">Rp {{ number_format($totalRp, 0, ',', '.') }}</td></tr>
                                                    @endif
                                                @else
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted py-4">
                                                            <em>Permintaan <strong>{{ $qty }} Akun {{ $displayData['requested_nama_subs'] ?? $data->subs->nama_subs ?? '' }}</strong> sedang diproses. Detail URL & Kode Akses akan muncul setelah diberikan oleh Tim ITSM.</em>
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
