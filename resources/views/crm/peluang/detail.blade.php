@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Lead</h4>
                <div class="d-flex gap-2">
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
                                <dd class="col-sm-8">{{ $peluang->materi ?? '-' }}</dd>

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
                                                <td>{{ \Carbon\Carbon::parse($item->waktu_aktivitas)->translatedFormat('d F Y H:i') }}
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="aktivitas" class="form-label">Jenis Aktivitas</label>
                                <select class="form-select" name="aktivitas" id="aktivitas" required>
                                    <option value="">-- Pilih Aktivitas --</option>
                                    @foreach (['Panggilan', 'Email', 'Meeting'] as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
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
                                        <option value="{{ $item->nama_materi }}"
                                            {{ $item->nama_materi === $peluang->materi ? 'selected' : '' }}>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
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
