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

                   {{-- Judul tetap dinamis berdasarkan ID --}}
                   <h5 class="card-title">
                        Detail Pengajuan {{ $data->id_labs ? 'Lab' : ($data->id_subs ? 'Subscription' : '-') }}
                    </h5>

                    <div class="row">
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
                                    <p>{{ $data->id_labs ? 'Lab' : 'Subscription' }}</p>
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

                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    @php
                                        $displayData = null;
                                        $type = '';

                                        if ($data->id_labs) {
                                            $type = 'Lab';
                                            $displayData = $data->lab_snapshot;
                                        } elseif ($data->id_subs) {
                                            $type = 'Subscription';
                                            $displayData = $data->subs_snapshot;
                                        }

                                        $isComplete = false;

                                        if (!empty($displayData) && is_array($displayData)) {
                                            $nameKey = ($type == 'Lab') ? 'nama_labs' : 'nama_subs';

                                            // Pastikan key wajib ada dalam array JSON
                                            $isComplete = !empty($displayData[$nameKey])
                                                && !empty($displayData['harga'])
                                                && !empty($displayData['mata_uang'])
                                                && !empty($displayData['start_date'])
                                                && !empty($displayData['end_date']);
                                        }
                                    @endphp

                                    <div class="col-md-12 d-flex justify-content-between">
                                        <h5 class="card-title">Detail {{ $type }}</h5>
                                        <div>
                                            {{-- Tombol PDF hanya aktif jika snapshot JSON sudah lengkap --}}
                                            <a href="{{ $isComplete ? route('pengajuanlabsdansubs.exportpdf', $data->id) : '#' }}"
                                               target="{{ $isComplete ? '_blank' : '' }}"
                                               class="btn btn-danger {{ !$isComplete ? 'disabled' : '' }}"
                                               title="{{ $isComplete ? 'Export PDF' : 'Menunggu Approval Koordinator ITSM' }}">
                                                Export PDF
                                            </a>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <tbody>
                                                {{-- Cek apakah data snapshot ada --}}
                                                @if (!empty($displayData) && is_array($displayData))
                                                    @if ($type == 'Lab')
                                                        <tr><td>Nama Lab</td><td>{{ $displayData['nama_labs'] ?? '-' }}</td></tr>
                                                        <tr><td>Deskripsi</td><td>{{ $displayData['desc'] ?? '-' }}</td></tr>
                                                        <tr><td>URL Lab</td>
                                                            <td>
                                                                @if (!empty($displayData['lab_url']))
                                                                    <a href="{{ $displayData['lab_url'] }}" target="_blank">Lihat URL Lab</a>
                                                                @else - @endif
                                                            </td>
                                                        </tr>
                                                        <tr><td>Durasi (menit)</td><td>{{ $displayData['duration_minutes'] ?? '-' }}</td></tr>
                                                    @else
                                                        {{-- Tampilan Subscription --}}
                                                        <tr><td>Nama Subscription</td><td>{{ $displayData['nama_subs'] ?? '-' }}</td></tr>
                                                        <tr><td>Merk</td><td>{{ $displayData['merk'] ?? '-' }}</td></tr>
                                                        <tr><td>Deskripsi</td><td>{{ $displayData['desc'] ?? '-' }}</td></tr>
                                                        <tr><td>URL</td>
                                                            <td>
                                                                @if (!empty($displayData['subs_url']))
                                                                    <a href="{{ $displayData['subs_url'] }}" target="_blank">Lihat URL</a>
                                                                @else - @endif
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    {{-- Field Umum (Lab & Subs) --}}
                                                    <tr><td>Kode Akses</td><td>{{ $displayData['access_code'] ?? '-' }}</td></tr>
                                                    <tr><td>Mata Uang</td><td>{{ $displayData['mata_uang'] ?? '-' }}</td></tr>
                                                    <tr><td>Harga</td><td>{{ number_format((float)($displayData['harga'] ?? 0), 2, ',', '.') }}</td></tr>
                                                    <tr><td>Kurs</td><td>{{ number_format((float)($displayData['kurs'] ?? 1), 2, ',', '.') }}</td></tr>
                                                    <tr><td>Harga (Rupiah)</td><td>{{ isset($displayData['harga_rupiah']) ? 'Rp '.number_format((float)$displayData['harga_rupiah'], 0, ',', '.') : '-' }}</td></tr>
                                                    <tr><td>Mulai</td><td>{{ !empty($displayData['start_date']) ? \Carbon\Carbon::parse($displayData['start_date'])->format('d M Y') : '-' }}</td></tr>
                                                    <tr><td>Berakhir</td><td>{{ !empty($displayData['end_date']) ? \Carbon\Carbon::parse($displayData['end_date'])->format('d M Y') : '-' }}</td></tr>
                                                    <tr><td>Status Alat</td><td>{{ ucfirst($displayData['status'] ?? '-') }}</td></tr>
                                                @else
                                                    {{-- Tampilan jika JSON Snapshot masih NULL --}}
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">
                                                            <em>Belum ada detail data tersimpan (Menunggu Approval Koordinator ITSM).</em>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
