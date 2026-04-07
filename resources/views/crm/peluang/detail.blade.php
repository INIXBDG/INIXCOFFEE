@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $isLost = strtolower($peluang->tahap) === 'lost';
        $allowedUser = [
            'Adm Sales',
            'SPV Sales',
            'HRD',
            'Finance & Accounting',
            'GM',
            'Sales',
            'Direktur Utama',
            'Direktur',
        ];
    @endphp
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Lead</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#paymentAdvanceModal" @if ($netsales) disabled @endif>
                        <i class="menu-icon bx bx-plus"></i> Payment Advance
                    </button>
                    @if ($isLost)
                        <span class="btn btn-sm btn-info" style="pointer-events: none; opacity: 0.5;">Lihat di RKM</span>
                    @else
                        <a class="btn btn-sm btn-info" target="blank_"
                            href="/rkm/{{ $peluang->rkm->materi_key }}ixb{{ $peluang->rkm->tanggal_awal_day }}ie{{ $peluang->rkm->tanggal_awal_year }}ie{{ $peluang->rkm->tanggal_awal_month }}ixb{{ $peluang->rkm->metode_kelas }}">Lihat
                            di RKM</a>
                    @endif
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editPeluangModal">
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
                                <input type="text" hidden disabled value="{{ $peluang->id_contact }}" name="id_contact">
                                <dt class="col-sm-4">Materi</dt>
                                <dd class="col-sm-8">{{ $peluang->materiRelation->nama_materi ?? '-' }}</dd>

                                <dt class="col-sm-4">Catatan</dt>
                                <dd class="col-sm-8">{{ $peluang->catatan ?? '-' }}</dd>

                                <dt class="col-sm-4">Harga Penawaran</dt>
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
                                    {{ \Carbon\Carbon::parse($peluang->periode_mulai)->translatedFormat('d F Y') }}
                                </dd>

                                <dt class="col-sm-4">Periode Selesai</dt>
                                <dd class="col-sm-8">
                                    {{ \Carbon\Carbon::parse($peluang->periode_selesai)->translatedFormat('d F Y') }}
                                </dd>

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
                                        @if ($regis)
                                            <a href="{{ asset('storage/' . $regis->path) }}" target="_blank"
                                                class="btn btn-sm btn-success">
                                                <i class="bi bi-file-earmark-pdf"></i> Lihat Regis Form
                                            </a>
                                            @endif
                                            <a href="{{ route('crm.index.regis', ['id' => $peluang->id]) }}" target="_blank"
                                                class="btn btn-sm btn-info">
                                                <i class="bi bi-file-earmark-plus"></i> Generate Regis Form
                                            </a>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#uploadPdfModal">
                                                <i class="bi bi-upload"></i>
                                                @if ($regis)
                                                    Edit PDF
                                                @else
                                                    Upload PDF                                                
                                                @endif
                                            </button>
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
                            @if ($netsales)
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
                            <table class="table table-bordered table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col" class="px-3 py-2 text-center">ID Sales</th>
                                        <th scope="col" class="px-3 py-2">Contact (PIC)</th>
                                        <th scope="col" class="px-3 py-2">Aktivitas</th>
                                        <th scope="col" class="px-3 py-2">Deskripsi</th>
                                        <th scope="col" class="px-3 py-2">Waktu Aktivitas</th>
                                        <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($peluang->aktivitas as $item)
                                        <tr>
                                            <td class="px-3 py-2 text-center">{{ $item->id_sales }}</td>
                                            <td class="px-3 py-2">
                                                {{ $item->contact->nama ?? ($item->peserta->nama ?? '-') }}
                                            </td>
                                            <td class="px-3 py-2">
                                                @if ($item->aktivitas === 'Incharge')
                                                    Incharge Inhouse
                                                @elseif ($item->aktivitas === 'Form_Keluar')
                                                    Regis Form Keluar
                                                @elseif ($item->aktivitas === 'Form_Masuk')
                                                    Regis Form Masuk
                                                @else
                                                    {{ $item->aktivitas }}
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ $item->deskripsi ?? '-' }}</td>
                                            <td class="px-3 py-2">
                                                {{ \Carbon\Carbon::parse($item->waktu_aktivitas)->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                        data-bs-target="#editAktivitasModal" onclick='editAktivitas(@json($item))'>
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('delete.aktivitas', $item->id) }}" method="POST"
                                                        onsubmit="return confirm('Yakin ingin menghapus?')"
                                                        style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
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
                <form action="{{ route('store.aktivitas.new') }}" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="id_sales" value="{{ auth()->user()->id_sales }}">
                    <input type="hidden" name="id_perusahaan" id="id_perusahaan" value="{{ $peluang->id_contact }}">
                    <input type="hidden" name="id_peluang" value="{{ $peluang->id }}">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="tambahAktivitasModalLabel">Tambah Aktivitas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="id_contact" class="form-label">Pilih Contact</label>
                                <select name="id_contact" class="form-control" id="id_contact">
                                    <option value="">-- Pilih Contact </option>
                                    @foreach ($items as $contact)
                                        <option value="{{ $contact['id'] }}" data-type="{{ $contact['type'] }}">
                                            {{ $contact['label'] }}
                                        </option>
                                    @endforeach
                                    </option>
                                </select>
                                <input type="hidden" name="contact_type" id="contact_type" value="">
                            </div>

                            <div class="mb-3">
                                <label for="aktivitas" class="form-label">Jenis Aktivitas</label>
                                <select class="form-select" name="aktivitas" id="aktivitas" required>
                                    <option value="">-- Pilih Aktivitas --</option>
                                    <option value="Call">Call</option>
                                    <option value="Email">Email</option>
                                    <option value="Visit">Visit</option>
                                    <option value="Meet">Meeting</option>
                                    <option value="Incharge">Incharge Inhouse</option>
                                    <option value="PA">Penawaran Awal</option>
                                    <option value="PI">Penawaran Internal</option>
                                    <option value="Telemarketing">Telemarketing</option>
                                    <option value="Form_Masuk">Regis Form Masuk</option>
                                    <option value="Form_Keluar">Regis Form Keluar</option>
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
            <div class="modal-dialog modal-lg">
                <form action="{{ route('edit.peluang', $peluang->id) }}" method="POST" id="editLeads">
                    @method('PUT')
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editPeluangModalLabel">Edit Lead</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Existing Fields -->
                            <div class="mb-3">
                                <label for="materi" class="form-label">Materi</label>
                                <select class="form-select" id="materi" name="materi" required>
                                    <option value="">-- Pilih Materi --</option>
                                    @foreach ($materi as $item)
                                        <option value="{{ $item->id }}" {{ $item->id == $peluang->materi ? 'selected' : '' }}>
                                            {{ $item->nama_materi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('materi')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan</label>
                                <textarea class="form-control" id="catatan" name="catatan"
                                    rows="2">{{ old('catatan', $peluang->catatan) }}</textarea>
                                @error('catatan')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga Penawaran</label>
                                <input type="text" class="form-control editLead" id="harga" name="harga"
                                    value="{{ old('harga', 'Rp ' . number_format($peluang->harga, 0, ',', '.')) }}"
                                    required>
                                @error('harga')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="final" class="form-label">Harga Final</label>
                                <input type="text" class="form-control editLead" id="final" name="final"
                                    value="{{ old('final', 'Rp ' . number_format($peluang->final, 0, ',', '.')) }}"
                                    required>
                                @error('final')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="pax" class="form-label">Jumlah Peserta (Pax)</label>
                                <input type="number" class="form-control" id="pax" name="pax" min="1"
                                    value="{{ old('pax', $peluang->pax) }}" required>
                                @error('pax')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="periode_mulai" class="form-label">Periode Mulai</label>
                                <input type="date" class="form-control" id="periode_mulai" name="periode_mulai"
                                    value="{{ old('periode_mulai', $peluang->periode_mulai) }}" required>
                                @error('periode_mulai')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="periode_selesai" class="form-label">Periode Selesai</label>
                                <input type="date" class="form-control" id="periode_selesai" name="periode_selesai"
                                    value="{{ old('periode_selesai', $peluang->periode_selesai) }}" required>
                                @error('periode_selesai')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <input type="hidden" name="tentatif" value="0">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="tentatifSwitch"
                                    name="tentatif" value="1" {{ old('tentatif', $peluang->tentatif) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tentatifSwitch">Tentatif</label>
                            </div>

                            <!-- Related Activities -->
                            <div class="mb-3">
                                <h6 class="fw-bold">Aktivitas Terkait</h6>
                                <div id="editAktivitasTableWrapper">
                                    <p class="text-muted">Memuat aktivitas...</p>
                                </div>
                                @error('id_aktivitas.*')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Confirm Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="approveForm" method="POST">
                            @csrf
                            <p>Apakah Disetujui?</p>
                            <div id="manager-row">
                                @php
                                    $jabatan = auth()->user()->jabatan;
                                @endphp
                                @if ($jabatan == 'Finance & Accounting')
                                    <div class="row my-2">
                                        <select name="status_tracking" id="status_tracking" class="form-select">
                                            <option disabled selected>Pilih Status Tracking</option>
                                            <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">
                                                Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                            <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang
                                                Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                            <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi
                                            </option>
                                            <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur
                                                Utama</option>
                                            <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam
                                                proses Pencairan</option>
                                            <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                            <option value="Selesai">Selesai</option>
                                        </select>
                                        <div class="invalid-feedback">Silakan pilih status tracking terlebih dahulu.</div>
                                    </div>
                                @endif

                                <div class="btn-group" role="group">
                                    <input type="hidden" value="" id="id_rkm" name="id_rkm">
                                    <button class="btn btn-outline-primary" type="submit" id="btnApproveYes">Ya</button>

                                    <input type="radio" class="btn-check" name="approval" id="approveNo" value="2"
                                        autocomplete="off">
                                    <label class="btn btn-outline-danger" for="approveNo"
                                        onclick="toggleAlasanManager(true)">Tidak</label>
                                </div>

                                <div class="mt-3" id="alasanManagerInput" style="display: none;">
                                    <label for="alasan_manager" class="form-label">Alasan Penolakan</label>
                                    <textarea class="form-control" id="alasan_manager" name="keterangan"
                                        rows="3"></textarea>
                                    <input type="hidden" value="{{ auth()->user()->jabatan }}" name="jabatan">
                                    <button class="btn btn-outline-success mt-3" type="submit">Kirim</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
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
                                <label for="close_win_display" class="form-label">Harga Final (PAX)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="close_win_display"
                                        placeholder="Masukkan harga final">
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">

                            <input type="hidden" name="id_rkm" value='{{ $peluang->id_rkm }}'>
                            <input type="hidden" name="id_peluang" value='{{ $peluang->id }}'>

                            <!-- TRANSPORTASI -->
                            <div class="mb-3">
                                <label class="form-label">Transportasi</label>
                                <input type="text" class="form-control rupiah" name="transportasi">
                            </div>

                            <!-- JENIS TRANSPORTASI -->
                            <div class="mb-3">
                                <label class="form-label">Jenis Transportasi (Sewa Mobil / Tiket Pesawat)</label>
                                <textarea class="form-control" name="jenis_transportasi" rows="2"></textarea>
                            </div>

                            <!-- AKOMODASI PESERTA -->
                            <div class="mb-3">
                                <label class="form-label">Akomodasi Peserta</label>
                                <input type="text" class="form-control rupiah" name="akomodasi_peserta">
                            </div>

                            <!-- AKOMODASI TIM -->
                            <div class="mb-3">
                                <label class="form-label">Akomodasi Tim</label>
                                <input type="text" class="form-control rupiah" name="akomodasi_tim">
                            </div>

                            <!-- KETERANGAN AKOMODASI TIM -->
                            <div class="mb-3">
                                <label class="form-label">Keterangan Akomodasi Tim</label>
                                <textarea class="form-control" name="keterangan_akomodasi_tim" rows="3"></textarea>
                            </div>

                            <!-- FRESH MONEY -->
                            <div class="mb-3">
                                <label class="form-label">Fresh Money (Uang Saku Peserta)</label>
                                <input type="text" class="form-control rupiah" name="fresh_money">
                            </div>

                            <!-- ENTERTAINT -->
                            <div class="mb-3">
                                <label class="form-label">Entertaint</label>
                                <input type="text" class="form-control rupiah" name="entertaint">
                            </div>

                            <!-- KETERANGAN ENTERTAINT -->
                            <div class="mb-3">
                                <label class="form-label">Keterangan Entertain</label>
                                <textarea class="form-control" name="keterangan_entertaint" rows="3"></textarea>
                            </div>

                            <!-- SOUVENIR -->
                            <div class="mb-3">
                                <label class="form-label">Souvenir</label>
                                <input type="text" class="form-control rupiah" name="Souvenir">
                            </div>

                            <!-- CASHBACK -->
                            <div class="mb-3">
                                <label class="form-label">Cashback</label>
                                <input type="text" class="form-control rupiah" name="cashback">
                            </div>

                            <!-- SEWA LAPTOP -->
                            <div class="mb-3">
                                <label class="form-label">Sewa Laptop (Opsional)</label>
                                <input type="text" class="form-control rupiah" name="sewa_laptop" placeholder="Opsional">
                            </div>

                            {{-- Tanggal Payment --}}
                            <div class="mb-3">
                                <label class="form-label">Tanggal Payment</label>
                                <input type="date" class="form-control" name="tgl_pa">
                            </div>

                            <!-- DESKRIPSI TAMBAHAN -->
                            <div class="mb-3">
                                <label class="form-label">Deskripsi Tambahan</label>
                                <textarea class="form-control" name="deskripsi_tambahan" rows="3"></textarea>
                            </div>

                            <!-- PEMBAYARAN -->
                            <div class="mb-3">
                                <label class="form-label">Pembayaran</label>
                                <select class="form-select" name="tipe_pembayaran">
                                    <option selected disabled>Pilih Tipe Pembayaran</option>
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
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
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailPAModalLabel">Detail Payment Advance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        @if ($netsales)

                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Transportasi</th>
                                            <th>Jenis Transportasi</th>
                                            <th>Akomodasi Peserta</th>
                                            <th>Akomodasi Tim</th>
                                            <th>Keterangan Akomodasi Tim</th>
                                            <th>Fresh Money</th>
                                            <th>Entertaint</th>
                                            <th>Keterangan Entertaint</th>
                                            <th>Souvenir</th>
                                            <th>Cashback</th>
                                            <th>Sewa Laptop</th>
                                            <th>Deskripsi Tambahan</th>
                                            <th>Tanggal PA</th>
                                            <th>Tipe Pembayaran</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>Rp {{ number_format($netsales->transportasi, 0, ',', '.') }}</td>
                                            <td>{{ $netsales->jenis_transportasi ?? '-' }}</td>

                                            <td>Rp {{ number_format($netsales->akomodasi_peserta, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($netsales->akomodasi_tim, 0, ',', '.') }}</td>
                                            <td>{{ $netsales->keterangan_akomodasi_tim ?? '-' }}</td>

                                            <td>{{ $netsales->fresh_money ? 'Rp ' . number_format($netsales->fresh_money, 0, ',', '.') : '-' }}
                                            </td>

                                            <td>{{ $netsales->entertaint ? 'Rp ' . number_format($netsales->entertaint, 0, ',', '.') : '-' }}
                                            </td>
                                            <td>{{ $netsales->keterangan_entertaint ?? '-' }}</td>

                                            <td>{{ $netsales->souvenir ? 'Rp ' . number_format($netsales->souvenir, 0, ',', '.') : '-' }}
                                            </td>

                                            <td>{{ $netsales->cashback ? 'Rp ' . number_format($netsales->cashback, 0, ',', '.') : '-' }}
                                            </td>

                                            <td>{{ $netsales->sewa_laptop ? 'Rp ' . number_format($netsales->sewa_laptop, 0, ',', '.') : '-' }}
                                            </td>

                                            <td>{{ $netsales->deskripsi_tambahan ?? '-' }}</td>

                                            <td>{{ \Carbon\Carbon::parse($netsales->tgl_pa)->translatedFormat('d F Y') }}
                                            </td>

                                            <td>{{ ucfirst($netsales->tipe_pembayaran) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Tracking --}}
                            <h6 class="mt-4 mb-3">Tracking Information</h6>
                            @if ($netsales->first()->trackingNetSales)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tracking</th>
                                                <th>Tanggal Dibuat</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td>{{ $netsales->first()->trackingNetSales->tracking ?? '-' }}</td>
                                                <td>{{ $netsales->first()->trackingNetSales->created_at->translatedFormat('d F Y') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">Belum ada tracking</p>
                            @endif

                            {{-- Approval --}}
                            <h6 class="mt-4 mb-3">Approval Information</h6>
                            @if ($netsales->first()->approvedNetSales->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-striped">
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
                                                        $approval->level_status === '3' &&
                                                        $approval->keterangan !== 'Selesai'
                                                        => 'Diproses',
                                                        $approval->status === 1 => 'Disetujui',
                                                        $approval->status === 0 => 'Ditolak',
                                                        default => 'Belum diketahui',
                                                    };

                                                    $approver = match ($approval->level_status) {
                                                        '1' => 'SPV Sales',
                                                        '2' => 'GM',
                                                        '3' => 'Finance & Accounting',
                                                        default => '-',
                                                    };
                                                @endphp

                                                <tr>
                                                    <td>{{ $status }}</td>
                                                    <td>{{ $approver }}</td>
                                                    <td>{{ $approval->keterangan ?? '-' }}</td>
                                                    <td>{{ $approval->created_at->translatedFormat('d F Y H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">Belum ada data approval.</p>
                            @endif
                        @else
                            <p class="text-muted">Belum ada data Payment Advance untuk peluang ini.</p>
                        @endif

                    </div>

                    <div class="modal-footer">
                        @if ($netsales)
                            @php
                                $pa = $netsales->first();
                                $approvals = $pa->approvedNetSales ?? collect();
                                $lastApproval = $approvals->last();

                                $jabatanUser = auth()->user()->jabatan;
                                $level = $lastApproval ? $lastApproval->level_status : null;

                                // Tambahkan pengecekan status Selesai di sini
                                $isSelesai = ($lastApproval && $lastApproval->keterangan === 'Selesai');

                                $canApprove = false;

                                // Jika sudah selesai, button tidak akan muncul (canApprove tetap false)
                                if (!$isSelesai) {
                                    if (is_null($level) || $level === '' || $level === 'Belum Disetujui') {
                                        $canApprove = ($jabatanUser === 'SPV Sales');
                                    } elseif ($level === '1') {
                                        $canApprove = in_array($jabatanUser, ['GM', 'Koordinator Office']);
                                    } elseif ($level === '2') {
                                        $canApprove = ($jabatanUser === 'Finance & Accounting');
                                    } elseif ($level === '3') {
                                        $canApprove = in_array($jabatanUser, ['Finance & Accounting', 'GM', 'SPV Sales']);
                                    }
                                }
                            @endphp

                            @if ($isSelesai)
                                <div class="text-end">
                                    <span class="badge bg-label-success p-2">
                                        <i class="bi bi-check-all"></i> Seluruh Proses Selesai
                                    </span>
                                </div>
                            @elseif ($canApprove)
                                <div class="text-end">
                                    <button type="button" class="btn btn-success"
                                        onclick="openApproveModal('{{ $pa->id_rkm ?? $peluang->id_rkm }}')">
                                        <i class="bi bi-check-circle"></i> Approve Sekarang
                                    </button>
                                </div>
                            @else

                            @endif
                        @endif

                        <a type="button" class="btn btn-primary" target="_blank"
                            href="{{route('netsales.detail', ['id' => $peluang->id_rkm])}}">View PA</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal Upload RegisForm-->
        <div class="modal fade" id="uploadPdfModal" tabindex="-1" aria-labelledby="uploadPdfModalLabel" aria-hidden="true">
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
                                <input type="file" name="pdf" id="pdfFile" class="form-control" accept="application/pdf"
                                    required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
        <script>
            // 1. Inisialisasi variabel global
            let approveType = '';

            // 2. Fungsi untuk membuka modal Approve (Global Scope)
            window.openApproveModal = function (id_rkm) {
                console.log('ID RKM yang dikirim ke modal:', id_rkm);

                // --- TAMBAHAN: Tutup modal detail PA terlebih dahulu ---
                const detailPAModalEl = document.getElementById('detailPAModal');
                const detailPAModalInstance = bootstrap.Modal.getInstance(detailPAModalEl);
                if (detailPAModalInstance) {
                    detailPAModalInstance.hide();
                } else {
                    // Jika instance belum ada (fallback menggunakan jQuery)
                    $('#detailPAModal').modal('hide');
                }
                // -------------------------------------------------------

                // Reset form dan state modal approve
                $('#approveForm')[0].reset();
                $('#id_rkm').val(id_rkm);
                $('#alasanManagerInput').hide();
                $('#status_tracking').removeClass('is-invalid');
                approveType = '';

                // Tampilkan modal approve menggunakan instance Bootstrap 5
                const modalElement = document.getElementById('approveModal');
                const modalInstance = new bootstrap.Modal(modalElement);
                modalInstance.show();
            };

            // 3. Fungsi toggle alasan penolakan (Global Scope)
            window.toggleAlasanManager = function (show) {
                if (show) {
                    $('#alasanManagerInput').slideDown();
                    $('#btnApproveYes').hide(); // Sembunyikan tombol "Ya" jika memilih "Tidak"
                } else {
                    $('#alasanManagerInput').slideUp();
                    $('#alasan_manager').val('');
                    $('#btnApproveYes').show();
                }
            };

            // 4. Event Listeners (Jquery Ready)
            $(document).ready(function () {

                // Menentukan tipe approve berdasarkan tombol yang diklik
                $(document).on('click', '#btnApproveYes', function () {
                    approveType = 'ya';
                    window.toggleAlasanManager(false);
                });

                $(document).on('click', '#approveNo', function () {
                    approveType = 'tidak';
                });

                // Handle Submit Form via AJAX
                $(document).on('submit', '#approveForm', function (e) {
                    e.preventDefault();

                    const jabatan = "{{ auth()->user()->jabatan }}";
                    const selectedTracking = $('#status_tracking').val();

                    // Validasi tracking khusus Finance jika memilih "Ya"
                    if (approveType === 'ya' && jabatan === 'Finance & Accounting') {
                        if (!selectedTracking || selectedTracking === "null" || selectedTracking === "") {
                            alert('Silakan pilih status tracking terlebih dahulu.');
                            $('#status_tracking').addClass('is-invalid');
                            return;
                        }
                    }

                    let formData = new FormData(this);
                    // Tambahkan flag ke FormData agar controller tahu ini Approve atau Reject
                    formData.append('approval_status', approveType);

                    $.ajax({
                        url: "{{ route('netsales.approved') }}",
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        beforeSend: function () {
                            $('button[type="submit"]').prop('disabled', true).text('Processing...');
                        },
                        success: function (response) {
                            const modalEl = document.getElementById('approveModal');
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) modalInstance.hide();

                            location.reload();
                        },
                        error: function (xhr) {
                            $('button[type="submit"]').prop('disabled', false).text('Kirim');
                            console.error(xhr.responseText);
                            alert('Terjadi kesalahan saat memproses data.');
                        }
                    });
                });
            });
        </script>
        <script>
            $(document).ready(function () {
                const perusahaanId = $('#id_perusahaan').val();

                // Function to fetch and display activities
                function loadActivities(targetElementId) {
                    $.ajax({
                        url: `/crm/ambil/aktivitas/${perusahaanId}`,
                        method: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            const activities = data.data ||
                                data; // Fallback if response is directly an array

                            if (!Array.isArray(activities) || activities.length === 0) {
                                $(`#${targetElementId}`).html(
                                    `<p class="text-muted">Tidak ada aktivitas yang tersedia untuk contact ini.</p>`
                                );
                                return;
                            }

                            let table = `
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pilih</th>
                                        <th>Kontak</th>
                                        <th>Jenis Aktivitas</th>
                                        <th>Subjek</th>
                                        <th>Deskripsi</th>
                                        <th>Waktu Aktivitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                            activities.forEach(a => {
                                const waktu = new Date(a.waktu).toLocaleDateString(
                                    'id-ID', {
                                    day: '2-digit',
                                    month: 'long',
                                    year: 'numeric'
                                });
                                table += `
                            <tr>
                                <td><input type="checkbox" name="id_aktivitas[]" value="${a.id}"></td>
                                <td>${a.kontak || '-'}</td>
                                <td>${a.aktivitas || '-'}</td>
                                <td>${a.subject || '-'}</td>
                                <td>${a.deskripsi ?? '-'}</td>
                                <td>${waktu}</td>
                            </tr>
                        `;
                            });

                            table += `</tbody></table></div>`;
                            $(`#${targetElementId}`).html(table);
                        },
                        error: function (err) {
                            console.error('Gagal memuat aktivitas:', err);
                            $(`#${targetElementId}`).html(
                                `<p class="text-danger">Terjadi kesalahan saat memuat aktivitas. Periksa console untuk detail.</p>`
                            );
                        }
                    });
                }

                // Load activities for the main "Aktivitas Terkait" table
                loadActivities('aktivitasTableWrapper');

                // Load activities when Edit Lead Modal is opened
                $('#editPeluangModal').on('show.bs.modal', function () {
                    loadActivities('editAktivitasTableWrapper');
                });

                // Existing JavaScript for Select2 and input formatting
                initContactSelect2();

                let peluang = @json($peluang);
                console.log(peluang);

                const editLead = document.querySelectorAll(".editLead");
                const rupiahInputs = document.querySelectorAll(".rupiah");
                const tahapSelect = document.getElementById('tahap');
                const closeWinInput = document.getElementById('input-close-win');
                const displayInput = document.getElementById('close_win_display');
                const hiddenInput = document.getElementById('close_win');
                const descLostInput = document.getElementById('input-desc-lost');
                const descLostField = document.getElementById('desc_lost');

                // Toggle inputs based on stage
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
                    toggleInputs(); // Initial trigger
                }

                // Format rupiah for close_win_display
                displayInput.addEventListener('input', function () {
                    let value = this.value.replace(/\D/g, ''); // Pure numbers
                    if (!value) {
                        this.value = "";
                        hiddenInput.value = "";
                        return;
                    }

                    this.value = new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(value);
                    hiddenInput.value = value;
                });

                // Ensure hiddenInput is clean before submission
                document.getElementById('updates').addEventListener('submit', function () {
                    hiddenInput.value = displayInput.value.replace(/\D/g, '');
                });

                // Format rupiah inputs
                rupiahInputs.forEach(input => {
                    input.addEventListener("input", function () {
                        let value = this.value.replace(/\D/g, "");
                        if (!value) {
                            this.value = "";
                            return;
                        }
                        this.value = new Intl.NumberFormat("id-ID", {
                            style: "currency",
                            currency: "IDR",
                            minimumFractionDigits: 0
                        }).format(value);
                    });
                });

                // Clean rupiah inputs before submission
                document.getElementById("StorePA").addEventListener("submit", function () {
                    rupiahInputs.forEach(input => {
                        input.value = input.value.replace(/\D/g, "");
                    });
                });

                editLead.forEach(input => {
                    input.addEventListener("input", function () {
                        let value = this.value.replace(/\D/g, "");
                        if (!value) {
                            this.value = "";
                            return;
                        }
                        this.value = new Intl.NumberFormat("id-ID", {
                            style: "currency",
                            currency: "IDR",
                            minimumFractionDigits: 0
                        }).format(value);
                    });
                });

                document.getElementById("editLeads").addEventListener("submit", function () {
                    editLead.forEach(input => {
                        input.value = input.value.replace(/\D/g, "");
                    });
                });

                function initContactSelect2() {
                    var $select = $('#id_contact');
                    if (typeof $.fn.select2 !== 'function') {
                        console.error('Select2 belum ter-load!');
                        return;
                    }
                    var $closestModal = $select.closest('.modal');
                    $select.select2({
                        width: '100%',
                        theme: 'bootstrap-5',
                        dropdownParent: $closestModal.length ? $closestModal : $(document.body)
                    });
                }
            });
        </script>
@endsection