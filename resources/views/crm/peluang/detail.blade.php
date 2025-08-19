@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $isLost = strtolower($peluang->tahap) === 'lost';
    @endphp
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Lead</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentAdvanceModal"
                        {{ $peluang->tahap === 'merah' ? '' : 'disabled' }}>
                        <i class="bx bx-plus"></i>  Payment Advance
                    </button>
                    @if ($isLost)
                        <span class="btn btn-sm btn-info" style="pointer-events: none; opacity: 0.5;">Lihat di RKM</span>
                    @else
                        <a class="btn btn-sm btn-info" target="blank_"
                            href="/rkm/{{ $peluang->rkm->materi_key }}ixb{{ $peluang->rkm->tanggal_awal_day }}ie{{ $peluang->rkm->tanggal_awal_year }}ie{{ $peluang->rkm->tanggal_awal_month }}ixb{{ $peluang->rkm->metode_kelas }}">Lihat
                            di RKM</a>
                    @endif
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                        data-bs-target="#editPeluangModal">Edit Lead</button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#updateProbabilitasModal" @disabled($peluang->tahap === 'merah' || $peluang->tahap === 'lost')>
                        Update Lead
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Card Utama -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Informasi Lead</h5>

                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#tambahAktivitasModal">
                                    Tambah Aktivitas
                                </button>

                                <form action="{{ route('delete.peluang', $peluang->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus peluang ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Materi</dt>
                                <dd class="col-sm-8">{{ $peluang->materiRelation->nama_materi ?? '-' }}</dd>

                                <dt class="col-sm-4">Catatan</dt>
                                <dd class="col-sm-8">{{ $peluang->catatan ?? '-' }}</dd>

                                <dt class="col-sm-4">Harga</dt>
                                <dd class="col-sm-8">Rp {{ number_format($peluang->harga, 2, ',', '.') }}</dd>

                                <dt class="col-sm-4">Net Sales</dt>
                                <dd class="col-sm-8">Rp {{ number_format($peluang->netsales, 2, ',', '.') }}</dd>

                                <dt class="col-sm-4">Jumlah Peserta (Pax)</dt>
                                <dd class="col-sm-8">{{ $peluang->pax }}</dd>

                                <dt class="col-sm-4">Periode Mulai</dt>
                                <dd class="col-sm-8">
                                    {{ \Carbon\Carbon::parse($peluang->periode_mulai)->translatedFormat('d F Y') }}</dd>

                                <dt class="col-sm-4">Periode Selesai</dt>
                                <dd class="col-sm-8">
                                    {{ \Carbon\Carbon::parse($peluang->periode_selesai)->translatedFormat('d F Y') }}</dd>

                                <dt class="col-sm-4">Client</dt>
                                <dd class="col-sm-8">
                                    {{ $peluang->perusahaan->nama_perusahaan ?? '-' }}
                                </dd>

                                <dt class="col-sm-4">Sales</dt>
                                <dd class="col-sm-8">{{ $peluang->id_sales }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Card Samping -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Status Tahapan</h6>
                            @php
                                $badgeColor = match ($peluang->tahap) {
                                    'hitam' => 'secondary',
                                    'biru' => 'info',
                                    'merah' => 'danger',
                                    'lost' => 'primary',
                                    default => 'dark',
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeColor }}">{{ strtoupper($peluang->tahap) }}</span>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @if ($peluang->biru)
                                    <li>
                                        <strong class="text-info">Update Biru:</strong><br>
                                        {{ \Carbon\Carbon::parse($peluang->biru)->translatedFormat('d F Y') }}
                                    </li>
                                @endif

                                @if ($peluang->lost)
                                    <li>
                                        <strong class="text-primary">Lost:</strong><br>
                                        {{ \Carbon\Carbon::parse($peluang->lost)->translatedFormat('d F Y') }}
                                    </li>
                                @endif

                                @if ($peluang->merah)
                                    <li class="mt-2">
                                        <strong class="text-danger">Update Merah:</strong><br>
                                        {{ \Carbon\Carbon::parse($peluang->merah)->translatedFormat('d F Y') }}
                                    </li>
                                @endif

                                @if ($peluang->final)
                                    <li class="mt-2">
                                        <strong class="text-success">Final Harga:</strong><br>
                                        Rp {{ number_format($peluang->final, 2, ',', '.') }}
                                    </li>
                                @endif
                            </ul>

                            @if ($peluang->tahap === 'lost' && $peluang->desc_lost)
                                <div class="card mt-5 shadow-sm rounded" style="border: none; max-width: 500px;">
                                    <div class="card-header bg-primary text-white fw-bold"
                                        style="font-size: 0.85rem; border-radius: 0.375rem 0.375rem 0 0; padding: 0.5rem 1rem;">
                                        Deskripsi Lost :
                                    </div>
                                    <div class="card-body bg-light"
                                        style="border-radius: 0 0 0.375rem 0.375rem; color: #444; font-size: 0.95rem; line-height: 1.5; padding: 1rem;">
                                        {{ $peluang->desc_lost }}
                                    </div>
                                </div>
                            @endif
                            
                            @if ($netsales)
                                <p class="mt-4"><strong>Lihat detail Payment Advance :</strong></p>

                                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detailPAModal">
                                    Detail PA
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Daftar Aktivitas -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aktivitas Terkait</h5>
                </div>
                <div class="card-body">
                    @if ($peluang->aktivitas->isEmpty())
                        <p class="text-muted">Belum ada aktivitas yang tercatat.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Jenis</th>
                                        <th>Subjek</th>
                                        <th>Deskripsi</th>
                                        <th>Waktu</th>
                                        <th>Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach ($peluang->aktivitas as $item)
                                        @if ($item->id_sales == $peluang->id_sales)
                                            <tr>
                                                <td>{{ $no++ }}</td>
                                                <td>{{ $item->aktivitas }}</td>
                                                <td>{{ $item->subject }}</td>
                                                <td>{{ $item->deskripsi }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->waktu_aktivitas)->translatedFormat('d F Y') }}
                                                </td>
                                                <td>{{ $item->id_sales ?? '-' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Modal Tambah Aktivitas -->
        <div class="modal fade" id="tambahAktivitasModal" tabindex="-1" aria-labelledby="tambahAktivitasModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('store.aktivitas') }}" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="id_sales" value="{{ auth()->user()->id_sales }}">
                    <input type="hidden" name="id_contact" value="{{ $peluang->id_contact }}">
                    <input type="hidden" name="id_peluang" value="{{ $peluang->id }}">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="tambahAktivitasModalLabel">Tambah Aktivitas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="aktivitas" class="form-label">Jenis Aktivitas</label>
                                <select class="form-select" name="aktivitas" id="aktivitas" required>
                                    <option value="">-- Pilih Aktivitas --</option>
                                    <option value="Call">Call</option>
                                    <option value="Email">Email</option>
                                    <option value="Visit">Visit</option>
                                    <option value="Meet">Meeting</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Subjek</label>
                                <input type="text" name="subject" id="subject" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="waktu_aktivitas" class="form-label">Waktu Aktivitas</label>
                                <input type="date" name="waktu_aktivitas" id="waktu_aktivitas" class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Simpan Aktivitas</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Peluang -->
        <div class="modal fade" id="editPeluangModal" tabindex="-1" aria-labelledby="editPeluangModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('edit.peluang', $peluang->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editPeluangModalLabel">Edit Lead</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="materi" class="form-label">Materi</label>
                                <select class="form-select" id="materi" name="materi" required>
                                    <option value="">-- Pilih Materi --</option>
                                    @foreach ($materi as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $item->id == $peluang->materi ? 'selected' : '' }}>
                                            {{ $item->nama_materi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="2">{{ $peluang->catatan }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga</label>
                                <input type="number" class="form-control" id="harga" name="harga" step="0.01"
                                    value="{{ intval($peluang->harga) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="netsales" class="form-label">Net Sales</label>
                                <input type="number" class="form-control" id="netsales" name="netsales"
                                    step="0.01" value="{{ intval($peluang->netsales) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="pax" class="form-label">Jumlah Peserta (Pax)</label>
                                <input type="number" class="form-control" id="pax" name="pax" min="1"
                                    value="{{ $peluang->pax }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="periode_mulai" class="form-label">Periode Mulai</label>
                                <input type="date" class="form-control" id="periode_mulai" name="periode_mulai"
                                    value="{{ $peluang->periode_mulai }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="periode_selesai" class="form-label">Periode Selesai</label>
                                <input type="date" class="form-control" id="periode_selesai" name="periode_selesai"
                                    value="{{ $peluang->periode_selesai }}" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Update Tahap -->
        <div class="modal fade" id="updateProbabilitasModal" tabindex="-1" aria-labelledby="updateProbabilitasLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('update.tahap', $peluang->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Tahap Peluang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            @php
                                $tahapSaatIni = $peluang->tahap;
                                $opsiTahap = [];

                                if ($tahapSaatIni === 'hitam') {
                                    $opsiTahap = ['biru', 'lost'];
                                } elseif ($tahapSaatIni === 'biru') {
                                    $opsiTahap = ['merah', 'lost'];
                                }
                            @endphp

                            @if (empty($opsiTahap))
                                <p class="text-muted">Tahap sudah berada di posisi akhir.</p>
                            @else
                                <div class="mb-3">
                                    <label for="tahap" class="form-label">Pilih Tahap Baru</label>
                                    <select class="form-select" name="tahap" id="tahap" required>
                                        <option value="">-- PILIH TAHAP --</option>
                                        @foreach ($opsiTahap as $tahap)
                                            <option value="{{ $tahap }}">{{ strtoupper($tahap) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- Input Harga Final hanya muncul jika tahap = Merah -->
                            <div class="mb-3 d-none" id="input-close-win">
                                <label for="close_win" class="form-label">Harga Final (Menang)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        name="final" id="close_win" placeholder="Masukkan harga final">
                                </div>
                            </div>

                            <!-- Input Deskripsi Lost hanya muncul jika tahap = Lost -->
                            <div class="mb-3 d-none" id="input-desc-lost">
                                <label for="desc_lost" class="form-label">Deskripsi Lost</label>
                                <textarea class="form-control" name="desc_lost" id="desc_lost" rows="3"
                                    placeholder="Masukkan alasan kehilangan peluang"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="paymentAdvanceModal" tabindex="-1" aria-labelledby="paymentAdvanceModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('store.payment.advance') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentAdvanceModalLabel">Payment Advance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_rkm" value='{{ $peluang->id_rkm }}'>
                            <div class="mb-3">
                                <label for="hargaPenawaran" class="form-label">Harga Penawaran</label>
                                <input type="number" step="any" class="form-control" id="hargaPenawaran"
                                    name="hargaPenawaran">
                            </div>
                            <div class="mb-3">
                                <label for="transportasi" class="form-label">Transportasi</label>
                                <input type="number" step="any" class="form-control" id="transportasi"
                                    name="transportasi">
                            </div>
                            <div class="mb-3">
                                <label for="penginapan" class="form-label">Penginapan</label>
                                <input type="number" step="any" class="form-control" id="penginapan"
                                    name="penginapan">
                            </div>
                            <div class="mb-3">
                                <label for="freshMoney" class="form-label">Fresh Money</label>
                                <input type="number" step="any" class="form-control" id="freshMoney"
                                    name="freshMoney">
                            </div>
                            <div class="mb-3">
                                <label for="entertaint" class="form-label">Entertaint</label>
                                <input type="number" step="any" class="form-control" id="entertaint"
                                    name="entertaint">
                            </div>
                            <div class="mb-3">
                                <label for="souvenir" class="form-label">Souvenir</label>
                                <input type="number" step="any" class="form-control" id="souvenir"
                                    name="souvenir">
                            </div>
                            <div class="mb-3">
                                <label for="tanggalPayment" class="form-label">Tanggal Payment Advance</label>
                                <input type="date" class="form-control" id="tanggalPayment" name="tanggalPayment"
                                    placeholder="dd/mm/yyyy">
                            </div>
                            <div class="mb-3">
                                <label for="tipePembayaran" class="form-label">Tipe Pembayaran</label>
                                <select class="form-select" id="tipePembayaran" name="tipePembayaran">
                                    <option selected disabled>Pilih Tipe Pembayaran</option>
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


<!-- Modal Detail PA -->
    <div class="modal fade" id="detailPAModal" tabindex="-1" aria-labelledby="detailPAModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailPAModalLabel">Detail Payment Advance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($netsales)
                        <h6 class="mb-3">Informasi Payment Advance</h6>
                        <dl class="row">
                            <dt class="col-sm-4">Harga Penawaran</dt>
                            <dd class="col-sm-8">Rp {{ $netsales->harga_penawaran ? number_format($netsales->harga_penawaran, 2, ',', '.') : '-' }}</dd>
                            <dt class="col-sm-4">Transportasi</dt>
                            <dd class="col-sm-8">Rp {{ $netsales->transportasi ? number_format($netsales->transportasi, 2, ',', '.') : '-' }}</dd>
                            <dt class="col-sm-4">Penginapan</dt>
                            <dd class="col-sm-8">Rp {{ $netsales->penginapan ? number_format($netsales->penginapan, 2, ',', '.') : '-' }}</dd>
                            <dt class="col-sm-4">Fresh Money</dt>
                            <dd class="col-sm-8">Rp {{ $netsales->fresh_money ? number_format($netsales->fresh_money, 2, ',', '.') : '-' }}</dd>
                            <dt class="col-sm-4">Entertaint</dt>
                            <dd class="col-sm-8">Rp {{ $netsales->entertaint ? number_format($netsales->entertaint, 2, ',', '.') : '-' }}</dd>
                            <dt class="col-sm-4">Souvenir</dt>
                            <dd class="col-sm-8">Rp {{ $netsales->souvenir ? number_format($netsales->souvenir, 2, ',', '.') : '-' }}</dd>
                            <dt class="col-sm-4">Tanggal Payment Advance</dt>
                            <dd class="col-sm-8">{{ $netsales->tgl_pa ? \Carbon\Carbon::parse($netsales->tgl_pa)->translatedFormat('d F Y') : '-' }}</dd>
                            <dt class="col-sm-4">Tipe Pembayaran</dt>
                            <dd class="col-sm-8">{{ $netsales->tipe_pembayaran ? ucfirst($netsales->tipe_pembayaran) : '-' }}</dd>
                            <dt class="col-sm-4">Pajak</dt>
                            <dd class="col-sm-8">{{ $netsales->pajak ? number_format($netsales->pajak, 2, ',', '.') . '%' : '-' }}</dd>
                        </dl>

                        <h6 class="mt-4 mb-3">Tracking Information</h6>
                        @if ($netsales->trackingNetSales)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tracking</th>
                                            <th>Tanggal Dibuat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $netsales->trackingNetSales->tracking ?? '-' }}</td>
                                            <td>{{ $netsales->created_at ? \Carbon\Carbon::parse($netsales->created_at)->translatedFormat('d F Y') : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Belum ada data tracking untuk Payment Advance ini.</p>
                        @endif

                        <h6 class="mt-4 mb-3">Approval Information</h6>
                        @if ($netsales->approvedNetSales->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Approver</th>
                                            <th>Keterangan</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($netsales->approvedNetSales as $approval)
                                            @php
                                                $status = match (true) {
                                                    $approval->status === 1 && $approval->level_status === 'III' && $approval->keterangan !== 'Selesai' => 'Diproses',
                                                    $approval->status === 1 => 'Disetujui',
                                                    $approval->status === 0 => 'Ditolak',
                                                    default => 'Belum diketahui',
                                                };
                                                $approver = match ($approval->level_status) {
                                                    'I' => 'SPV Sales',
                                                    'II' => 'GM',
                                                    'III' => 'Finance & Accounting',
                                                    default => $approval->level_status ?? '-',
                                                };
                                            @endphp
                                            <tr>
                                                <td>{{ $status }}</td>
                                                <td>{{ $approver }}</td>
                                                <td>{{ $approval->keterangan ?? '-' }}</td>
                                                <td>{{ $approval->created_at ? \Carbon\Carbon::parse($approval->created_at)->translatedFormat('d F Y H:i') : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Belum ada data approval untuk Payment Advance ini.</p>
                        @endif
                    @else
                        <p class="text-muted">Belum ada data Payment Advance untuk peluang ini.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>



        <script>
            document.addEventListener('DOMContentLoaded', function() {

                let peluang = @json($peluang);
                console.log(peluang)
                console.log(peluang.materi.nama_materi);

                const tahapSelect = document.getElementById('tahap');
                const closeWinInput = document.getElementById('input-close-win');
                const closeWinField = document.getElementById('close_win');
                const descLostInput = document.getElementById('input-desc-lost');
                const descLostField = document.getElementById('desc_lost');

                function toggleInputs() {
                    if (tahapSelect.value.toLowerCase() === 'merah') {
                        closeWinInput.classList.remove('d-none');
                        closeWinField.setAttribute('required', 'required');
                        descLostInput.classList.add('d-none');
                        descLostField.removeAttribute('required');
                    } else if (tahapSelect.value.toLowerCase() === 'lost') {
                        descLostInput.classList.remove('d-none');
                        descLostField.setAttribute('required', 'required');
                        closeWinInput.classList.add('d-none');
                        closeWinField.removeAttribute('required');
                    } else {
                        closeWinInput.classList.add('d-none');
                        closeWinField.removeAttribute('required');
                        descLostInput.classList.add('d-none');
                        descLostField.removeAttribute('required');
                    }
                }

                if (tahapSelect) {
                    tahapSelect.addEventListener('change', toggleInputs);
                    toggleInputs(); // Trigger saat pertama kali modal muncul
                }
            });
        </script>
    @endsection
