@extends('layouts_crm.app')
@section('crm_contents')
    <div class="container py-4">
        <!-- Section: Perusahaan & Peluang (Side by Side) -->
        <div class="row g-4 mb-4">
            <!-- Card: Data Perusahaan -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="card-title h4 fw-bold mb-3">Data Perusahaan</h2>
                        <dl class="row mb-3">
                            <dt class="col-sm-4">Nama</dt>
                            <dd class="col-sm-8">{{ $data->nama_perusahaan }}</dd>

                            <dt class="col-sm-4">Kategori</dt>
                            <dd class="col-sm-8">{{ $data->kategori_perusahaan ?? '-' }}</dd>

                            <dt class="col-sm-4">Lokasi</dt>
                            <dd class="col-sm-8">{{ $data->lokasi ?? '-' }}</dd>

                            <dt class="col-sm-4">Sales</dt>
                            <dd class="col-sm-8">{{ $data->sales_key ?? '-' }}</dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">{{ $data->status ?? '-' }}</dd>

                            <dt class="col-sm-4">NPWP</dt>
                            <dd class="col-sm-8">{{ $data->npwp ?? '-' }}</dd>

                            <dt class="col-sm-4">Alamat</dt>
                            <dd class="col-sm-8">{{ $data->alamat ?? '-' }}</dd>

                            <dt class="col-sm-4">Contact Person</dt>
                            <dd class="col-sm-8">{{ $data->cp ?? '-' }}</dd>

                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $data->email ?? '-' }}</dd>

                            <dt class="col-sm-4">No. Telp</dt>
                            <dd class="col-sm-8">{{ $data->no_telp ?? '-' }}</dd>

                            <dt class="col-sm-4">Foto NPWP</dt>
                            <dd class="col-sm-8">
                                @if (!empty($data->foto_npwp))
                                    <a href="{{ asset('storage/' . $data->foto_npwp) }}" target="_blank">Lihat Foto NPWP</a>
                                @else
                                    -
                                @endif
                            </dd>

                            <dt class="col-sm-4">Ditambah</dt>
                            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') }}
                            </dd>
                        </dl>
                        <div class="text-end">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editContactModal">
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Data Peluang -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="card-title h4 fw-bold mb-0">Data Prospect</h2>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#tambahLeadModal">
                                Tambah Lead
                            </button>
                        </div>
                        <p class="mb-3"><strong>Total Final:</strong> Rp
                            {{ number_format($peluang->sum('final'), 2, ',', '.') }}</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col" class="px-3 py-2">Materi</th>
                                        <th scope="col" class="px-3 py-2">Periode</th>
                                        <th scope="col" class="px-3 py-2 text-center">Pax</th>
                                        <th scope="col" class="px-3 py-2 text-center">Prospect Terbuat</th>
                                        <th scope="col" class="px-3 py-2 text-center">Tahap</th>
                                        <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($peluang as $item)
                                        <tr>
                                            <td class="px-3 py-2">{{ $item->materiRelation->nama_materi }}</td>
                                            <td class="px-3 py-2">
                                                {{ \Carbon\Carbon::parse($item->periode_mulai)->translatedFormat('d F Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($item->periode_selesai)->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-3 py-2 text-center">{{ $item->pax }}</td>
                                            <td class="px-3 py-2 text-center">
                                                {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-3 py-2 text-center {{ match (strtolower($item->tahap)) {
                                                    'merah' => 'bg-danger text-white',
                                                    'biru', 'lead' => 'bg-info text-white',
                                                    'hitam' => 'bg-dark text-white',
                                                    'lost' => 'bg-primary text-white',
                                                    default => 'bg-secondary text-white'
                                                } }}">
                                                    {{ strtoupper($item->tahap) }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="{{ route('detail.peluang', $item->id) }}"
                                                        class="btn btn-sm btn-info">Detail</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal: Edit Data Perusahaan -->
                <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editContactModalLabel">Edit Perusahaan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editContactForm" method="POST" enctype="multipart/form-data"
                                    action="{{ route('update.contact', $data->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" id="edit_contact_id">

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_nama_perusahaan">Nama Perusahaan</label>
                                        <input type="text" class="form-control" id="edit_nama_perusahaan"
                                            name="nama_perusahaan" value="{{ $data->nama_perusahaan }}" required
                                            maxlength="255">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_kategori_perusahaan">Kategori
                                            Perusahaan</label>
                                        <select class="form-select @error('kategori_perusahaan') is-invalid @enderror"
                                            name="kategori_perusahaan" id="edit_kategori_perusahaan" required>
                                            <option value="" selected>Pilih Kategori Perusahaan</option>
                                            <option value="Pemerintahan Daerah"
                                                {{ $data->kategori_perusahaan == 'Pemerintahan Daerah' ? 'selected' : '' }}>
                                                Pemerintahan Daerah</option>
                                            <option value="Kementerian"
                                                {{ $data->kategori_perusahaan == 'Kementerian' ? 'selected' : '' }}>
                                                Kementerian</option>
                                            <option value="Lembaga Pemerintahan"
                                                {{ $data->kategori_perusahaan == 'Lembaga Pemerintahan' ? 'selected' : '' }}>
                                                Lembaga Pemerintahan</option>
                                            <option value="BUMN"
                                                {{ $data->kategori_perusahaan == 'BUMN' ? 'selected' : '' }}>BUMN</option>
                                            <option value="BUMD"
                                                {{ $data->kategori_perusahaan == 'BUMD' ? 'selected' : '' }}>BUMD</option>
                                            <option value="Swasta"
                                                {{ $data->kategori_perusahaan == 'Swasta' ? 'selected' : '' }}>Swasta
                                            </option>
                                            <option value="Akademik"
                                                {{ $data->kategori_perusahaan == 'Akademik' ? 'selected' : '' }}>Akademik
                                            </option>
                                            <option value="Bank Daerah"
                                                {{ $data->kategori_perusahaan == 'Bank Daerah' ? 'selected' : '' }}>Bank
                                                Daerah</option>
                                            <option value="Bank Umum"
                                                {{ $data->kategori_perusahaan == 'Bank Umum' ? 'selected' : '' }}>Bank Umum
                                            </option>
                                            <option value="Bank BUMN"
                                                {{ $data->kategori_perusahaan == 'Bank BUMN' ? 'selected' : '' }}>Bank BUMN
                                            </option>
                                            <option value="Rumah Sakit"
                                                {{ $data->kategori_perusahaan == 'Rumah Sakit' ? 'selected' : '' }}>Rumah
                                                Sakit</option>
                                            <option value="Personal"
                                                {{ $data->kategori_perusahaan == 'Personal' ? 'selected' : '' }}>Personal
                                            </option>
                                        </select>
                                        @error('kategori_perusahaan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_cp">PIC</label>
                                        <input type="text" class="form-control" id="edit_cp" name="cp"
                                            value="{{ $data->cp }}" maxlength="100">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_email">Email</label>
                                        <input type="email" class="form-control" id="edit_email" name="email"
                                            value="{{ $data->email }}" required maxlength="255">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_lokasi">Lokasi</label>
                                        <select class="form-select" id="edit_lokasi" name="lokasi" required>
                                            <option value="">Pilih Lokasi</option>
                                            <option value="Aceh" {{ $data->lokasi == 'Aceh' ? 'selected' : '' }}>Aceh
                                            </option>
                                            <option value="Sumatera Utara"
                                                {{ $data->lokasi == 'Sumatera Utara' ? 'selected' : '' }}>Sumatera Utara
                                            </option>
                                            <option value="Sumatera Barat"
                                                {{ $data->lokasi == 'Sumatera Barat' ? 'selected' : '' }}>Sumatera Barat
                                            </option>
                                            <option value="Riau" {{ $data->lokasi == 'Riau' ? 'selected' : '' }}>Riau
                                            </option>
                                            <option value="Kepulauan Riau"
                                                {{ $data->lokasi == 'Kepulauan Riau' ? 'selected' : '' }}>Kepulauan Riau
                                            </option>
                                            <option value="Jambi" {{ $data->lokasi == 'Jambi' ? 'selected' : '' }}>Jambi
                                            </option>
                                            <option value="Bengkulu" {{ $data->lokasi == 'Bengkulu' ? 'selected' : '' }}>
                                                Bengkulu</option>
                                            <option value="Sumatera Selatan"
                                                {{ $data->lokasi == 'Sumatera Selatan' ? 'selected' : '' }}>Sumatera
                                                Selatan</option>
                                            <option value="Bangka Belitung"
                                                {{ $data->lokasi == 'Bangka Belitung' ? 'selected' : '' }}>Bangka Belitung
                                            </option>
                                            <option value="Lampung" {{ $data->lokasi == 'Lampung' ? 'selected' : '' }}>
                                                Lampung</option>
                                            <option value="DKI Jakarta"
                                                {{ $data->lokasi == 'DKI Jakarta' ? 'selected' : '' }}>DKI Jakarta</option>
                                            <option value="Banten" {{ $data->lokasi == 'Banten' ? 'selected' : '' }}>
                                                Banten</option>
                                            <option value="Jawa Barat"
                                                {{ $data->lokasi == 'Jawa Barat' ? 'selected' : '' }}>Jawa Barat</option>
                                            <option value="Jawa Tengah"
                                                {{ $data->lokasi == 'Jawa Tengah' ? 'selected' : '' }}>Jawa Tengah</option>
                                            <option value="DI Yogyakarta"
                                                {{ $data->lokasi == 'DI Yogyakarta' ? 'selected' : '' }}>DI Yogyakarta
                                            </option>
                                            <option value="Jawa Timur"
                                                {{ $data->lokasi == 'Jawa Timur' ? 'selected' : '' }}>Jawa Timur</option>
                                            <option value="Bali" {{ $data->lokasi == 'Bali' ? 'selected' : '' }}>Bali
                                            </option>
                                            <option value="Nusa Tenggara Barat"
                                                {{ $data->lokasi == 'Nusa Tenggara Barat' ? 'selected' : '' }}>Nusa
                                                Tenggara Barat</option>
                                            <option value="Nusa Tenggara Timur"
                                                {{ $data->lokasi == 'Nusa Tenggara Timur' ? 'selected' : '' }}>Nusa
                                                Tenggara Timur</option>
                                            <option value="Kalimantan Barat"
                                                {{ $data->lokasi == 'Kalimantan Barat' ? 'selected' : '' }}>Kalimantan
                                                Barat</option>
                                            <option value="Kalimantan Tengah"
                                                {{ $data->lokasi == 'Kalimantan Tengah' ? 'selected' : '' }}>Kalimantan
                                                Tengah</option>
                                            <option value="Kalimantan Selatan"
                                                {{ $data->lokasi == 'Kalimantan Selatan' ? 'selected' : '' }}>Kalimantan
                                                Selatan</option>
                                            <option value="Kalimantan Timur"
                                                {{ $data->lokasi == 'Kalimantan Timur' ? 'selected' : '' }}>Kalimantan
                                                Timur</option>
                                            <option value="Kalimantan Utara"
                                                {{ $data->lokasi == 'Kalimantan Utara' ? 'selected' : '' }}>Kalimantan
                                                Utara</option>
                                            <option value="Sulawesi Utara"
                                                {{ $data->lokasi == 'Sulawesi Utara' ? 'selected' : '' }}>Sulawesi Utara
                                            </option>
                                            <option value="Gorontalo"
                                                {{ $data->lokasi == 'Gorontalo' ? 'selected' : '' }}>Gorontalo</option>
                                            <option value="Sulawesi Tengah"
                                                {{ $data->lokasi == 'Sulawesi Tengah' ? 'selected' : '' }}>Sulawesi Tengah
                                            </option>
                                            <option value="Sulawesi Barat"
                                                {{ $data->lokasi == 'Sulawesi Barat' ? 'selected' : '' }}>Sulawesi Barat
                                            </option>
                                            <option value="Sulawesi Selatan"
                                                {{ $data->lokasi == 'Sulawesi Selatan' ? 'selected' : '' }}>Sulawesi
                                                Selatan</option>
                                            <option value="Sulawesi Tenggara"
                                                {{ $data->lokasi == 'Sulawesi Tenggara' ? 'selected' : '' }}>Sulawesi
                                                Tenggara</option>
                                            <option value="Maluku" {{ $data->lokasi == 'Maluku' ? 'selected' : '' }}>
                                                Maluku</option>
                                            <option value="Maluku Utara"
                                                {{ $data->lokasi == 'Maluku Utara' ? 'selected' : '' }}>Maluku Utara
                                            </option>
                                            <option value="Papua" {{ $data->lokasi == 'Papua' ? 'selected' : '' }}>Papua
                                            </option>
                                            <option value="Papua Barat"
                                                {{ $data->lokasi == 'Papua Barat' ? 'selected' : '' }}>Papua Barat</option>
                                            <option value="Papua Selatan"
                                                {{ $data->lokasi == 'Papua Selatan' ? 'selected' : '' }}>Papua Selatan
                                            </option>
                                            <option value="Papua Tengah"
                                                {{ $data->lokasi == 'Papua Tengah' ? 'selected' : '' }}>Papua Tengah
                                            </option>
                                            <option value="Papua Pegunungan"
                                                {{ $data->lokasi == 'Papua Pegunungan' ? 'selected' : '' }}>Papua
                                                Pegunungan</option>
                                            <option value="Papua Barat Daya"
                                                {{ $data->lokasi == 'Papua Barat Daya' ? 'selected' : '' }}>Papua Barat
                                                Daya</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_status">Status</label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="edit_status"
                                            name="status" required>
                                            <option value="" selected>Pilih Status</option>
                                            <option value="Q1" {{ $data->status == 'Q1' ? 'selected' : '' }}>Q1
                                            </option>
                                            <option value="Q2" {{ $data->status == 'Q2' ? 'selected' : '' }}>Q2
                                            </option>
                                            <option value="Q3" {{ $data->status == 'Q3' ? 'selected' : '' }}>Q3
                                            </option>
                                            <option value="Q4" {{ $data->status == 'Q4' ? 'selected' : '' }}>Q4
                                            </option>
                                            <option value="Database Baru"
                                                {{ $data->status == 'Database Baru' ? 'selected' : '' }}>Database Baru
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_npwp">NPWP</label>
                                        <input type="text" class="form-control" id="edit_npwp" name="npwp"
                                            value="{{ $data->npwp }}" maxlength="50">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_alamat">Alamat</label>
                                        <textarea class="form-control" id="edit_alamat" name="alamat" maxlength="500">{{ $data->alamat }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_no_telp">No Telepon</label>
                                        <input type="text" class="form-control" id="edit_no_telp" name="no_telp"
                                            value="{{ $data->no_telp }}" maxlength="20">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="edit_foto_npwp">Foto NPWP (jpg, jpeg, png, pdf max
                                            2MB)</label>
                                        <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="form-control"
                                            id="edit_foto_npwp" name="foto_npwp">
                                    </div>

                                    <button type="submit" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal: Tambah Lead -->
                <div class="modal fade" id="tambahLeadModal" tabindex="-1" aria-labelledby="tambahLeadModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tambahLeadModalLabel">Tambah Lead</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('store.peluang') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_contact" value="{{ $data->id }}">
                                    <div class="mb-3">
                                        <label for="materi" class="form-label">Materi</label>
                                        <select class="form-control" id="materi" name="materi" required>
                                            <option value="" disabled selected>-- Pilih Materi --</option>
                                            @foreach ($materi as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_materi }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="catatan" class="form-label">Catatan</label>
                                        <textarea class="form-control" id="catatan" name="catatan"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="harga" class="form-label">Harga</label>
                                        <input type="number" class="form-control" id="harga" name="harga"
                                            step="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="pax" class="form-label">Jumlah Peserta (Pax)</label>
                                        <input type="number" class="form-control" id="pax" name="pax"
                                            min="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="netsales" class="form-label">Net Sales</label>
                                        <input type="number" class="form-control" id="netsales" name="netsales"
                                            step="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="periode_mulai" class="form-label">Periode Mulai</label>
                                        <input type="date" class="form-control" id="periode_mulai"
                                            name="periode_mulai" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="periode_selesai" class="form-label">Periode Selesai</label>
                                        <input type="date" class="form-control" id="periode_selesai"
                                            name="periode_selesai" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="metode_kelas">Metode Kelas</label>
                                        <select class="form-select" id="metode_kelas" name="metode_kelas" required>
                                            <option value="" disabled selected>Pilih Metode Kelas</option>
                                            <option value="Inhouse Bandung">Inhouse Bandung</option>
                                            <option value="Inhouse Luar Bandung">Inhouse Luar Bandung</option>
                                            <option value="Offline">Offline</option>
                                            <option value="Virtual">Virtual</option>
                                        </select>
                                        <div class="invalid-feedback">Pilih metode kelas.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="event">Event</label>
                                        <select class="form-select" id="event" name="event" required>
                                            <option value="" disabled selected>Pilih Event</option>
                                            <option value="Kelas">Kelas</option>
                                            <option value="Workshop">Workshop</option>
                                            <option value="Webinar">Webinar</option>
                                            <option value="Narasumber">Narasumber</option>
                                            <option value="Pinjam Instruktur">Pinjam Instruktur</option>
                                        </select>
                                        <div class="invalid-feedback">Pilih event.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Exam</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="examToggle" role="switch"
                                                onchange="document.getElementById('exam').value = this.checked ? '1' : '0';">
                                            <label class="form-check-label" for="examToggle">Aktifkan Exam</label>
                                        </div>
                                        <input type="hidden" id="exam" name="exam" value="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Authorize</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="authorizeToggle" role="switch"
                                                onchange="document.getElementById('authorize').value = this.checked ? '1' : '0';">
                                            <label class="form-check-label" for="authorizeToggle">Aktif</label>
                                        </div>
                                        <input type="hidden" id="authorize" name="authorize" value="0">
                                        <div class="invalid-feedback">Pilih status authorize.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="id_aktivitas" class="form-label">Aktivitas</label>
                                        <select class="form-select" id="id_aktivitas" name="id_aktivitas[]" multiple>
                                            <option value="" disabled>-- Pilih Aktivitas (Opsional) --</option>
                                            @foreach ($aktivitas as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->contact->nama }}
                                                    ({{ \Carbon\Carbon::parse($item->waktu_aktivitas)->translatedFormat('d F Y') }})
                                                    {{ $item->aktivitas  }}
                                                    {{ $item->subject }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="form-text text-muted">*Ctrl + click untuk multi select</span>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Aktivitas -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="card-title h4 fw-bold mb-0">Data Aktivitas</h2>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#tambahAktivitasModal">
                        Tambah Aktivitas
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col" class="px-3 py-2 text-center">ID Sales</th>
                                <th scope="col" class="px-3 py-2">Contact (PIC)</th>
                                <th scope="col" class="px-3 py-2">Aktivitas</th>
                                <th scope="col" class="px-3 py-2">Subject</th>
                                <th scope="col" class="px-3 py-2">Deskripsi</th>
                                <th scope="col" class="px-3 py-2">Waktu Aktivitas</th>
                                <th scope="col" class="px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aktivitass as $item)
                                <tr>
                                    <td class="px-3 py-2 text-center">{{ $item->id_sales }}</td>
                                    <td class="px-3 py-2">{{ $item->contact->nama }}</td>
                                    <td class="px-3 py-2">{{ $item->aktivitas }}</td>
                                    <td class="px-3 py-2">{{ $item->subject }}</td>
                                    <td class="px-3 py-2">{{ $item->deskripsi ?? '-' }}</td>
                                    <td class="px-3 py-2">
                                        {{ \Carbon\Carbon::parse($item->waktu_aktivitas)->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#editAktivitasModal"
                                                onclick='editAktivitas(@json($item))'>
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
                                <script>
                                    console.log($item)
                                </script>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Tambah Aktivitas -->
        <div class="modal fade" id="tambahAktivitasModal" tabindex="-1" aria-labelledby="tambahAktivitasModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahAktivitasModalLabel">Tambah Aktivitas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('store.aktivitas.new') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_perusahaan" value="{{ $data->id }}">
                            <div class="mb-3">
                                <label for="id_contact" class="form-label">Pilih Contact</label>
                                <select name="id_contact" class="form-control" id="id_contact" required>
                                    <option value=""> -- Pilih Contact -- </option>
                                    @foreach($data->contacts as $contact)
                                        <option value="{{ $contact->id }}">{{ $contact->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="aktivitas" class="form-label">Aktivitas</label>
                                <select name="aktivitas" class="form-control" id="">
                                    <option value=""> -- Pilih Aktivitas Anda -- </option>
                                    <option value="Call">Call</option>
                                    <option value="Email">Email</option>
                                    <option value="Visit">Visit</option>
                                    <option value="Meet">Meeting</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="waktu_aktivitas" class="form-label">Waktu Aktivitas</label>
                                <input type="date" class="form-control" id="waktu_aktivitas" name="waktu_aktivitas"
                                    required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Edit Aktivitas -->
        <div class="modal fade" id="editAktivitasModal" tabindex="-1" aria-labelledby="editAktivitasModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAktivitasModalLabel">Edit Aktivitas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editAktivitasForm" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="id_contact" id="edit_id_contact">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="mb-3">
                                <label for="edit_aktivitas" class="form-label">Aktivitas</label>
                                <select name="aktivitas" class="form-control" id="edit_aktivitas">
                                    <option value=""> -- Pilih Aktivitas Anda -- </option>
                                    <option value="Call">Call</option>
                                    <option value="Email">Email</option>
                                    <option value="Visit">Visit</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="edit_subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_waktu_aktivitas" class="form-label">Waktu Aktivitas</label>
                                <input type="date" class="form-control" id="edit_waktu_aktivitas"
                                    name="waktu_aktivitas" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Submit form via AJAX
            document.getElementById('editAktivitasForm').addEventListener('submit', function(e) {
                e.preventDefault(); // Stop default form submit

                const form = this;
                const url = form.action;
                const formData = new FormData(form);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: formData
                    })
                    .then(response => {
                        if (response.ok) {
                            alert('Data berhasil diperbarui');
                            window.location.reload(); // Reload page after success
                        } else {
                            alert('Gagal menyimpan data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan');
                    });
            });

            // Fungsi ini harus berada DI LUAR event listener
            function editAktivitas(data) {
                document.getElementById('edit_id').value = data.id;
                document.getElementById('edit_id_contact').value = data.id_contact;
                document.getElementById('edit_aktivitas').value = data.aktivitas;
                document.getElementById('edit_subject').value = data.subject;
                document.getElementById('edit_deskripsi').value = data.deskripsi || '';
                document.getElementById('edit_waktu_aktivitas').value = data.waktu_aktivitas.split(' ')[0];

                // Set action form ke route update
                document.getElementById('editAktivitasForm').action = `/crm/aktivitas/update/${data.id}`;
            }
        </script>

    </div>
@endsection
