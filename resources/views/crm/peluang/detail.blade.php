@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $isLost = strtolower($peluang->tahap) === 'lost';
        $allowedUser = ['Adm Sales', 'HRD', 'Finance & Accounting', 'GM', 'Sales', 'Direktur Utama', 'Direktur'];
    @endphp
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Lead</h4>
                <div class="d-flex gap-2">
                    <button type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#paymentAdvanceModal"
                            @if ($peluang->tahap !== 'merah' || !empty($peluang->netsales)) disabled @endif>
                        <i class="menu-icon bx bx-plus"></i> Payment Advance
                    </button>
                    @if ($isLost)
                        <span class="btn btn-sm btn-info" style="pointer-events: none; opacity: 0.5;">Lihat di RKM</span>
                    @else
                        <a class="btn btn-sm btn-info" target="blank_"
                            href="/rkm/{{ $peluang->rkm->materi_key }}ixb{{ $peluang->rkm->tanggal_awal_day }}ie{{ $peluang->rkm->tanggal_awal_year }}ie{{ $peluang->rkm->tanggal_awal_month }}ixb{{ $peluang->rkm->metode_kelas }}">Lihat
                            di RKM</a>
                    @endif
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                        data-bs-target="#editPeluangModal">
                        Edit Lead
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#updateProbabilitasModal" @disabled($peluang->tahap === 'merah' || $peluang->tahap === 'lost' || ($peluang->tahap === 'biru' && !$regis))>
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
                                    data-bs-target="#tambahAktivitasModal" {{ $peluang->merah ? 'disabled' : '' }}
                                    @if (in_array(Auth::user()->jabatan, $allowedUser)) disabled @endif>
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

                                @if ($peluang->tentatif == true)
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8"><strong>Tentatif</strong></dd>
                                @endif

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

                                {{-- Tahap Biru --}}
                                @if ($peluang->biru)
                                    <li class="mb-3">
                                        <strong class="text-info">Update Biru:</strong><br>
                                        {{ \Carbon\Carbon::parse($peluang->biru)->translatedFormat('d F Y') }}
                                    </li>

                                    <div class="d-flex gap-2 flex-wrap mb-3">
                                        {{-- Hanya tampil kalau biru sudah ada & belum merah --}}
                                        @if (!$peluang->merah)
                                            <a href="{{ route('crm.index.regis', ['id' => $peluang->id]) }}"
                                                target="_blank" class="btn btn-sm btn-info">
                                                <i class="bi bi-file-earmark-plus"></i> Generate Regis Form
                                            </a>

                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#uploadPdfModal">
                                                <i class="bi bi-upload"></i> Upload PDF
                                            </button>
                                        @endif

                                        @if ($regis)
                                            <a href="{{ asset('storage/' . $regis->path) }}" target="_blank"
                                                class="btn btn-sm btn-success">
                                                <i class="bi bi-file-earmark-pdf"></i> Lihat Regis Form
                                            </a>
                                        @endif
                                    </div>
                                @endif


                                {{-- Tahap Lost --}}
                                @if ($peluang->lost)
                                    <li class="mb-3">
                                        <strong class="text-primary">Lost:</strong><br>
                                        {{ \Carbon\Carbon::parse($peluang->lost)->translatedFormat('d F Y') }}
                                    </li>
                                @endif

                                {{-- Tahap Merah --}}
                                @if ($peluang->merah)
                                    <li class="mb-3">
                                        <strong class="text-danger">Update Merah:</strong><br>
                                        {{ \Carbon\Carbon::parse($peluang->merah)->translatedFormat('d F Y') }}
                                    </li>
                                @endif

                                {{-- Final Harga --}}
                                @if ($peluang->final)
                                    <li class="mb-3">
                                        <strong class="text-success">Final Harga:</strong><br>
                                        Rp {{ number_format($peluang->final, 2, ',', '.') }}
                                    </li>
                                @endif
                            </ul>

                            {{-- Deskripsi Lost --}}
                            @if ($peluang->tahap === 'lost' && $peluang->desc_lost)
                                <div class="card mt-4 shadow-sm border-0">
                                    <div class="card-header bg-primary text-white fw-bold small">
                                        Deskripsi Lost
                                    </div>
                                    <div class="card-body bg-light text-muted">
                                        {{ $peluang->desc_lost }}
                                    </div>
                                </div>
                            @endif

                            {{-- Detail Payment Advance --}}
                            @if ($netsales->isNotEmpty())
                                <div class="mt-4">
                                    <p class="fw-bold mb-2">Lihat detail Payment Advance :</p>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#detailPAModal">
                                        <i class="bi bi-credit-card"></i> Detail PA
                                    </button>
                                </div>
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
                                                <td>
                                                    @if ($item->aktivitas === 'Incharge')
                                                        Incharge Inhouse
                                                    @else
                                                        {{ $item->aktivitas }}
                                                    @endif
                                                </td>
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
                                    <option value="Incharge">Incharge Inhouse</option>
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
                <form action="{{ route('edit.peluang', $peluang->id) }}" method="POST" id="editLeads">
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
                                <input type="text" class="form-control editLead" id="harga" name="harga"
                                    value="{{ 'Rp ' . number_format($peluang->harga, 0, ',', '.') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="netsales" class="form-label">Net Sales</label>
                                <input type="text" class="form-control editLead" id="netsales" name="netsales"
                                    value="{{ 'Rp ' . number_format($peluang->netsales, 0, ',', '.') }}" required>
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

                            <input type="hidden" name="tentatif" value="0">

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="tentatifSwitch"
                                    name="tentatif" value="1"
                                    {{ old('tentatif', $peluang->tentatif) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tentatifSwitch">Tentatif</label>
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
                <form method="POST" action="{{ route('update.tahap', $peluang->id) }}" id="updates">
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
                                <label for="close_win_display" class="form-label">Harga Final (Menang)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <!-- Input tampilan -->
                                    <input type="text" class="form-control" id="close_win_display"
                                        placeholder="Masukkan harga final">
                                    <!-- Input hidden untuk dikirim -->
                                    <input type="hidden" name="final" id="close_win">
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
                <form action="{{ route('store.payment.advance') }}" method="POST" id="StorePA">
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
                                <label for="id_peserta" class="form-label">Pilih Peserta</label>
                                <select class="form-select" id="id_peserta" name="id_peserta">
                                    <option selected disabled>Pilih Peserta</option>
                                    @foreach ($regisuser as $registrasi)
                                        @if (isset($registrasi->peserta->nama))
                                            <option value="{{ $registrasi->peserta->id }}">
                                                {{ $registrasi->peserta->nama }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="hargaPenawaran" class="form-label">Harga Penawaran</label>
                                <input type="text" class="form-control rupiah" id="hargaPenawaran"
                                    name="hargaPenawaran">
                            </div>
                            <div class="mb-3">
                                <label for="transportasi" class="form-label">Transportasi</label>
                                <input type="text" class="form-control rupiah" id="transportasi" name="transportasi">
                            </div>
                            <div class="mb-3">
                                <label for="penginapan" class="form-label">Penginapan</label>
                                <input type="text" class="form-control rupiah" id="penginapan" name="penginapan">
                            </div>
                            <div class="mb-3">
                                <label for="freshMoney" class="form-label">Fresh Money</label>
                                <input type="text" class="form-control rupiah" id="freshMoney" name="freshMoney">
                            </div>
                            <div class="mb-3">
                                <label for="cashback" class="form-label">Cashback</label>
                                <input type="text" class="form-control rupiah" id="cashback" name="cashback">
                            </div>
                            <div class="mb-3">
                                <label for="diskon" class="form-label">Diskon</label>
                                <input type="text" class="form-control rupiah" id="diskon" name="diskon">
                            </div>
                            <div class="mb-3">
                                <label for="entertaint" class="form-label">Entertaint</label>
                                <input type="text" class="form-control rupiah" id="entertaint" name="entertaint">
                            </div>
                            <div class="mb-3">
                                <label for="souvenir" class="form-label">Souvenir</label>
                                <input type="text" class="form-control rupiah" id="souvenir" name="souvenir">
                            </div>
                            <div class="mb-3">
                                <label for="desc" class="form-label">Description</label>
                                <textarea name="desc" id="desc" rows="4" class="form-control"></textarea>
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
        <div class="modal fade" id="detailPAModal" tabindex="-1" aria-labelledby="detailPAModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailPAModalLabel">Detail Payment Advance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($netsales->isNotEmpty())
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Harga Penawaran</th>
                                            <th>Transportasi</th>
                                            <th>Penginapan</th>
                                            <th>Fresh Money</th>
                                            <th>Entertaint</th>
                                            <th>Souvenir</th>
                                            <th>Diskon</th>
                                            <th>Cashback</th>
                                            <th>Tanggal PA</th>
                                            <th>Tipe Pembayaran</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($netsales as $item)
                                            <tr>
                                                <td>{{ $item->peserta->nama }}</td>
                                                <td>Rp
                                                    {{ $item->harga_penawaran ? number_format($item->harga_penawaran, 2, ',', '.') : '-' }}
                                                </td>
                                                <td>Rp
                                                    {{ $item->transportasi ? number_format($item->transportasi, 2, ',', '.') : '-' }}
                                                </td>
                                                <td>Rp
                                                    {{ $item->penginapan ? number_format($item->penginapan, 2, ',', '.') : '-' }}
                                                </td>
                                                <td>Rp
                                                    {{ $item->fresh_money ? number_format($item->fresh_money, 2, ',', '.') : '-' }}
                                                </td>
                                                <td>Rp
                                                    {{ $item->entertaint ? number_format($item->entertaint, 2, ',', '.') : '-' }}
                                                </td>
                                                <td>Rp
                                                    {{ $item->souvenir ? number_format($item->souvenir, 2, ',', '.') : '-' }}
                                                </td>
                                                <td>Rp
                                                    {{ $item->diskon ? number_format($item->diskon, 2, ',', '.') : '-' }}
                                                </td>
                                                <td>Rp
                                                    {{ $item->cashback ? number_format($item->cashback, 2, ',', '.') : '-' }}
                                                </td>
                                                <td>{{ $item->tgl_pa ? \Carbon\Carbon::parse($item->tgl_pa)->translatedFormat('d F Y') : '-' }}
                                                </td>
                                                <td>{{ $item->tipe_pembayaran ? ucfirst($item->tipe_pembayaran) : '-' }}
                                                </td>
                                                <td>{{ $item->desc ?? 'Tidak ada deskripsi.' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Tracking Information --}}
                            <h6 class="mt-4 mb-3">Tracking Information</h6>
                            @if ($netsales->first()->trackingNetSales)
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
                                                <td>{{ $netsales->first()->trackingNetSales->tracking ?? '-' }}</td>
                                                <td>{{ $netsales->first()->created_at ? \Carbon\Carbon::parse($netsales->first()->created_at)->translatedFormat('d F Y') : '-' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">Belum ada data tracking untuk Payment Advance ini.</p>
                            @endif

                            {{-- Approval Information --}}
                            <h6 class="mt-4 mb-3">Approval Information</h6>
                            @if ($netsales->first()->approvedNetSales->isNotEmpty())
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
                                            @foreach ($netsales->first()->approvedNetSales as $approval)
                                                @php
                                                    $status = match (true) {
                                                        $approval->status === 1 &&
                                                            $approval->level_status === 'III' &&
                                                            $approval->keterangan !== 'Selesai'
                                                            => 'Diproses',
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
                                                    <td>{{ $approval->created_at ? \Carbon\Carbon::parse($approval->created_at)->translatedFormat('d F Y H:i') : '-' }}
                                                    </td>
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

        <!-- Modal Upload RegisForm-->
        <div class="modal fade" id="uploadPdfModal" tabindex="-1" aria-labelledby="uploadPdfModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-3 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="upladPdfModalLabel">Upload PDF</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <form action="{{ route('crm.upload.regis') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id_peluang" value="{{ $peluang->id }}">

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="pdfFile" class="form-label">Pilih File PDF</label>
                                <input type="file" name="pdf" id="pdfFile" class="form-control"
                                    accept="application/pdf" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>




        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let peluang = @json($peluang);
                console.log(peluang)
                console.log(peluang.materi.nama_materi);

                const editLead = document.querySelectorAll(".editLead");
                const rupiahInputs = document.querySelectorAll(".rupiah");
                const tahapSelect = document.getElementById('tahap');
                const closeWinInput = document.getElementById('input-close-win');
                const paInput = document.getElementById('HargaPA');
                const displayInput = document.getElementById('close_win_display');
                const hiddenInput = document.getElementById('close_win');
                const descLostInput = document.getElementById('input-desc-lost');
                const descLostField = document.getElementById('desc_lost');

                // toggle input sesuai tahap
                function toggleInputs() {
                    if (tahapSelect.value.toLowerCase() === 'merah') {
                        closeWinInput.classList.remove('d-none');
                        displayInput.setAttribute('required', 'required');
                        hiddenInput.setAttribute('required', 'required');

                        descLostInput.classList.add('d-none');
                        descLostField.removeAttribute('required');
                    } else if (tahapSelect.value.toLowerCase() === 'lost') {
                        descLostInput.classList.remove('d-none');
                        descLostField.setAttribute('required', 'required');

                        closeWinInput.classList.add('d-none');
                        displayInput.removeAttribute('required');
                        hiddenInput.removeAttribute('required');
                    } else {
                        closeWinInput.classList.add('d-none');
                        displayInput.removeAttribute('required');
                        hiddenInput.removeAttribute('required');

                        descLostInput.classList.add('d-none');
                        descLostField.removeAttribute('required');
                    }
                }

                if (tahapSelect) {
                    tahapSelect.addEventListener('change', toggleInputs);
                    toggleInputs(); // trigger awal
                }

                // format rupiah saat user ketik
                displayInput.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, ''); // angka murni
                    if (!value) {
                        this.value = "";
                        hiddenInput.value = "";
                        return;
                    }

                    // update tampilan
                    this.value = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(value);

                    // update hidden untuk backend
                    hiddenInput.value = value;
                });

                // sebelum submit pastikan hiddenInput aman
                document.getElementById('updates').addEventListener('submit', function() {
                    hiddenInput.value = displayInput.value.replace(/\D/g, '');
                });

                rupiahInputs.forEach(input => {
                    input.addEventListener("input", function() {
                        let value = this.value.replace(/\D/g, ""); // angka murni
                        if (!value) {
                            this.value = "";
                            return;
                        }

                        // format tampilan Rp
                        this.value = new Intl.NumberFormat("id-ID", {
                            style: "currency",
                            currency: "IDR",
                            minimumFractionDigits: 0
                        }).format(value);
                    });
                });

                // sebelum submit, reset ke angka murni
                document.getElementById("StorePA").addEventListener("submit", function() {
                    rupiahInputs.forEach(input => {
                        input.value = input.value.replace(/\D/g, ""); // kirim angka murni
                    });
                });

                editLead.forEach(input => {
                    input.addEventListener("input", function() {
                        let value = this.value.replace(/\D/g, ""); // angka murni
                        if (!value) {
                            this.value = "";
                            return;
                        }

                        // format tampilan Rp
                        this.value = new Intl.NumberFormat("id-ID", {
                            style: "currency",
                            currency: "IDR",
                            minimumFractionDigits: 0
                        }).format(value);
                    });
                });

                // sebelum submit, reset ke angka murni
                document.getElementById("editLeads").addEventListener("submit", function() {
                    editLead.forEach(input => {
                        input.value = input.value.replace(/\D/g, ""); // kirim angka murni
                    });
                });

            });
        </script>
    @endsection
