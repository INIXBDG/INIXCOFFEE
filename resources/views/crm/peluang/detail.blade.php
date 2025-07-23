@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Peluang</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('index.peluang') }}" class="btn btn-secondary">Kembali</a>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                        data-bs-target="#editPeluangModal">Edit Peluang</button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#updateProbabilitasModal">Update Peluang</button>
                </div>
            </div>

            <!-- Detail Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Informasi Peluang</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahAktivitasModal">
                        Tambah Aktivitas
                    </button>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Judul</dt>
                        <dd class="col-sm-8">{{ $peluang->judul }}</dd>

                        <dt class="col-sm-4">Deskripsi</dt>
                        <dd class="col-sm-8">{{ $peluang->deskripsi ?? '-' }}</dd>

                        <dt class="col-sm-4">Jumlah</dt>
                        <dd class="col-sm-8">Rp {{ number_format($peluang->jumlah, 2, ',', '.') }}</dd>

                        @if (!empty($peluang->close_win))
                            <dt class="col-sm-4">Catatan Kemenangan</dt>
                            <dd class="col-sm-8">Rp {{ number_format($peluang->close_win, 2, ',', '.') }}</dd>
                        @endif

                        @if (!empty($peluang->close_lost))
                            <dt class="col-sm-4">Alasan Kalah</dt>
                            <dd class="col-sm-8">Rp {{ number_format($peluang->close_lost, 2, ',', '.') }}</dd>
                        @endif


                        <dt class="col-sm-4">Tahap</dt>
                        <dd class="col-sm-8">{{ $peluang->tahap ?? '-' }}</dd>

                        <dt class="col-sm-4">Probabilitas</dt>
                        <dd class="col-sm-8">{{ $peluang->probabilitas ?? 0 }}%</dd>

                        <dt class="col-sm-4">Tanggal Tutup Diharapkan</dt>
                        <dd class="col-sm-8">
                            {{ \Carbon\Carbon::parse($peluang->tanggal_tutup_diharapkan)->translatedFormat('d F Y') }}
                        </dd>

                        <dt class="col-sm-4">Contact</dt>
                        <dd class="col-sm-8">
                            {{ $peluang->contact->nama_lengkap ?? '-' }}
                            ({{ $peluang->contact->email ?? '-' }})
                        </dd>
                    </dl>
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
                                    @foreach (['Panggilan', 'Email', 'Meeting', 'Catatan', 'Task'] as $item)
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
                                <input type="datetime-local" name="waktu_aktivitas" id="waktu_aktivitas"
                                    class="form-control" required>
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
                            <h5 class="modal-title" id="editPeluangModalLabel">Edit Peluang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_judul" class="form-label">Judul</label>
                                <input type="text" class="form-control" id="edit_judul" name="judul"
                                    value="{{ $peluang->judul }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3">{{ $peluang->deskripsi }}</textarea>
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
        <div class="modal fade" id="updateProbabilitasModal" tabindex="-1"
            aria-labelledby="updateProbabilitasModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="updateTahapForm" action="{{ route('update.tahap', $peluang->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateProbabilitasModalLabel">Update Tahap Peluang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="tahap" class="form-label">Tahap</label>
                                <select class="form-select" name="tahap" id="tahap" required>
                                    <option value="">-- Pilih Tahap --</option>
                                    @foreach (['Prospek', 'Kualifikasi', 'Proposal', 'Negosiasi', 'Ditutup Menang', 'Ditutup Kalah'] as $tahap)
                                        <option value="{{ $tahap }}"
                                            {{ $peluang->tahap == $tahap ? 'selected' : '' }}>
                                            {{ $tahap }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tahap')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 d-none" id="input-close-win">
                                <label for="close_win" class="form-label">Harga Akhir (Menang)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        name="close_win" id="close_win"
                                        placeholder="Masukkan harga akhir yang disepakati">
                                </div>
                                @error('close_win')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 d-none" id="input-close-lost">
                                <label for="close_lost" class="form-label">Total Kehilangan (Kalah)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        name="close_lost" id="close_lost"
                                        placeholder="Masukkan total kehilangan dari peluang">
                                </div>
                                @error('close_lost')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Update Tahap</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debugging: Pastikan script dimuat
            console.log('Script untuk modal update tahap dimuat.');

            // Ambil elemen
            const tahapSelect = document.getElementById('tahap');
            const closeWinInput = document.getElementById('input-close-win');
            const closeLostInput = document.getElementById('input-close-lost');
            const form = document.getElementById('updateTahapForm');

            // Debugging: Periksa apakah elemen ditemukan
            console.log('tahapSelect:', tahapSelect);
            console.log('closeWinInput:', closeWinInput);
            console.log('closeLostInput:', closeLostInput);

            // Fungsi untuk mengatur visibilitas input
            function toggleCloseInputs() {
                const selected = tahapSelect.value;
                console.log('Tahap dipilih:', selected); // Debugging

                // Sembunyikan kedua input
                closeWinInput.classList.add('d-none');
                closeLostInput.classList.add('d-none');
                closeWinInput.querySelector('input').removeAttribute('required');
                closeLostInput.querySelector('input').removeAttribute('required');

                // Tampilkan input sesuai tahap
                if (selected === 'Ditutup Menang') {
                    closeWinInput.classList.remove('d-none');
                    closeWinInput.querySelector('input').setAttribute('required', 'required');
                } else if (selected === 'Ditutup Kalah') {
                    closeLostInput.classList.remove('d-none');
                    closeLostInput.querySelector('input').setAttribute('required', 'required');
                }
            }

            // Bind event listener untuk perubahan tahap
            if (tahapSelect) {
                tahapSelect.addEventListener('change', toggleCloseInputs);
            } else {
                console.error('Elemen tahapSelect tidak ditemukan!');
            }

            // Validasi form sebelum submit
            if (form) {
                form.addEventListener('submit', function(event) {
                    const selectedTahap = tahapSelect.value;
                    const closeWinValue = document.getElementById('close_win').value;
                    const closeLostValue = document.getElementById('close_lost').value;

                    console.log('Validasi form:', {
                        selectedTahap,
                        closeWinValue,
                        closeLostValue
                    }); // Debugging

                    if (selectedTahap === 'Ditutup Menang' && (!closeWinValue || closeWinValue <= 0)) {
                        event.preventDefault();
                        alert('Harap masukkan harga akhir yang valid untuk peluang yang menang.');
                    } else if (selectedTahap === 'Ditutup Kalah' && (!closeLostValue || closeLostValue <
                            0)) {
                        event.preventDefault();
                        alert('Harap masukkan total kehilangan yang valid untuk peluang yang kalah.');
                    }
                });
            } else {
                console.error('Elemen form tidak ditemukan!');
            }

            // Inisialisasi saat modal dibuka
            toggleCloseInputs();

            // Event listener untuk modal ditampilkan
            document.getElementById('updateProbabilitasModal').addEventListener('shown.bs.modal', function() {
                console.log('Modal update tahap ditampilkan.');
                toggleCloseInputs();
            });
        });
    </script>
@endsection
