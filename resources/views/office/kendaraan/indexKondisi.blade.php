@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0">Pemeriksaan Kondisi Kendaraan</h3>
            @if (Auth::user()->jabatan === 'Driver')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKondisi">
                    Tambah Pemeriksaan
                </button>
            @endif
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter Card --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small mb-1">Dari Tanggal</label>
                        <input type="date" id="minDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small mb-1">Sampai Tanggal</label>
                        <input type="date" id="maxDate" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <button id="resetFilter" class="btn btn-sm btn-secondary w-100">
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableKondisi" class="table table-bordered table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 5%">No</th>
                                <th>Pengguna / Driver</th>
                                <th>Jenis Kendaraan</th>
                                <th>Tanggal Pemeriksaan</th>
                                <th class="text-center" style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kondisi as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->user->karyawan->nama_lengkap ?? ($item->user->name ?? '-') }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $item->jenis_kendaraan == 'Innova' ? 'info' : 'warning' }}">
                                            {{ $item->jenis_kendaraan }}
                                        </span>
                                    </td>
                                    <td data-order="{{ $item->tanggal_pemeriksaan }}">
                                        {{ \Carbon\Carbon::parse($item->tanggal_pemeriksaan)->format('d M Y') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('office.detailKondisiKendaraan', $item->id) }}"
                                                class="btn btn-sm btn-info text-white" style="margin-right: 3px">
                                                Detail
                                            </a>

                                            @if (Auth::user()->jabatan === 'Driver')
                                                <form action="{{ route('office.deleteKondisiKendaraan', $item->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada data pemeriksaan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Kondisi (SUDAH DIPERBAIKI) --}}
    <div class="modal fade" id="modalTambahKondisi" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Form Cek Kondisi Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Bagian penting: modal-body dibatasi tinggi + bisa scroll -->
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; padding: 1.25rem;">
                    <form action="{{ route('office.storeKondisiKendaraan') }}" method="POST" id="formKondisi">
                        @csrf

                        {{-- Informasi Umum --}}
                        <h6 class="border-bottom pb-2 mb-3">Informasi Umum</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small">User ID / Driver</label>
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ Auth::user()->karyawan->nama_lengkap ?? Auth::user()->name }}" readonly>
                                <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Jenis Kendaraan <span class="text-danger">*</span></label>
                                <select name="jenis_kendaraan" class="form-select form-select-sm" required>
                                    <option value="">Pilih Jenis Kendaraan</option>
                                    <option value="Innova">Innova</option>
                                    <option value="H1">H1</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Tanggal Pemeriksaan <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="tanggal_pemeriksaan" class="form-control form-control-sm"
                                    required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        {{-- Kondisi Fisik --}}
                        <h6 class="border-bottom pb-2 mb-3">Kondisi Fisik</h6>
                        <div class="row g-3 mb-4">
                            @php
                                $fisikFields = [
                                    'fisik_baik' => 'Body Fisik',
                                    'bersih' => 'Kebersihan',
                                    'wiper_baik' => 'Wiper',
                                    'klakson_baik' => 'Klakson',
                                    'lampu_baik' => 'Lampu-lampu',
                                    'tekanan_ban_baik' => 'Tekanan Ban',
                                    'ban_baik' => 'Kondisi Ban',
                                    'ban_cadangan_lengkap' => 'Ban Cadangan',
                                    'setir_pedal_baik' => 'Setir & Pedal',
                                ];
                            @endphp
                            @foreach ($fisikFields as $name => $label)
                                <div class="col-md-4 col-sm-6">
                                    <label class="form-label small mb-1">{{ $label }}</label>
                                    <select name="{{ $name }}" class="form-select form-select-sm" required>
                                        <option value="1" selected>Baik/Lengkap</option>
                                        <option value="0">Buruk/Kurang</option>
                                    </select>
                                </div>
                            @endforeach
                            <div class="col-12">
                                <label class="form-label small mb-1">Catatan Kondisi Fisik</label>
                                <textarea name="catatan_kondisi" class="form-control form-control-sm" rows="2"
                                    placeholder="Tambahkan catatan jika ada kondisi yang perlu diperhatikan..."></textarea>
                            </div>
                        </div>

                        {{-- Mesin & Perawatan --}}
                        <h6 class="border-bottom pb-2 mb-3">Mesin & Perawatan</h6>
                        <div class="row g-3 mb-4">
                            @php
                                $mesinFields = [
                                    'oli_baik' => 'Oli Mesin',
                                    'radiator_baik' => 'Air Radiator',
                                    'air_wiper_baik' => 'Air Wiper',
                                    'minyak_rem_baik' => 'Minyak Rem',
                                    'aki_baik' => 'Kondisi Aki',
                                ];
                            @endphp
                            @foreach ($mesinFields as $name => $label)
                                <div class="col-md-4 col-sm-6">
                                    <label class="form-label small mb-1">{{ $label }}</label>
                                    <select name="{{ $name }}" class="form-select form-select-sm" required>
                                        <option value="1" selected>Baik/Cukup</option>
                                        <option value="0">Buruk/Kurang</option>
                                    </select>
                                </div>
                            @endforeach
                            <div class="col-12">
                                <label class="form-label small mb-1">Catatan Mesin</label>
                                <textarea name="catatan_mesin" class="form-control form-control-sm" rows="2"
                                    placeholder="Tambahkan catatan kondisi mesin jika diperlukan..."></textarea>
                            </div>
                        </div>

                        {{-- Fasilitas & Dokumen --}}
                        <h6 class="border-bottom pb-2 mb-3">Fasilitas & Dokumen</h6>
                        <div class="row g-3 mb-3">
                            @php
                                $fasilitasFields = [
                                    'dokumen_lengkap' => 'STNK & Dokumen',
                                    'ac_baik' => 'AC',
                                    'audio_baik' => 'Audio',
                                    'charger_ada' => 'Charger',
                                    'jas_hujan_ada' => 'Jas Hujan',
                                    'pengharum_ada' => 'Pengharum',
                                    'air_minum_ada' => 'Air Minum',
                                    'tisu_ada' => 'Tisu',
                                    'hand_sanitizer_ada' => 'Hand Sanitizer',
                                    'bbm_cukup' => 'BBM',
                                    'etol_aktif' => 'E-Toll',
                                ];
                            @endphp
                            @foreach ($fasilitasFields as $name => $label)
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">{{ $label }}</label>
                                    <select name="{{ $name }}" class="form-select form-select-sm" required>
                                        <option value="1" selected>Ada/Baik</option>
                                        <option value="0">Tidak/Rusak</option>
                                    </select>
                                </div>
                            @endforeach
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Catatan Perlengkapan</label>
                                <textarea name="catatan_perlengkapan" class="form-control form-control-sm" rows="2"
                                    placeholder="Catatan terkait dokumen dan perlengkapan..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Catatan Fasilitas</label>
                                <textarea name="catatan_fasilitas" class="form-control form-control-sm" rows="2"
                                    placeholder="Catatan terkait fasilitas kendaraan..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small mb-1">Ajukan Keluhan/Permintaan Perbaikan (opsional)</label>
                            <textarea name="keluhan" class="form-control form-control-sm" rows="2"
                                placeholder="Masukan keluhan/permintaan perbaikan..."></textarea>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formKondisi" class="btn btn-primary btn-sm">Simpan Data</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#tableKondisi').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                order: [
                    [3, 'desc']
                ], // Sort by date descending
                columnDefs: [{
                    orderable: false,
                    targets: 4
                }],
                pageLength: 25
            });

            // Custom date range filter
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const min = $('#minDate').val();
                const max = $('#maxDate').val();
                const dateStr = table.cell(dataIndex, 3).nodes().to$().attr('data-order');

                if (!dateStr) return true;
                if (!min && !max) return true;

                const date = new Date(dateStr);
                const minDate = min ? new Date(min) : null;
                const maxDate = max ? new Date(max) : null;

                if (minDate && date < minDate) return false;
                if (maxDate && date > maxDate) return false;

                return true;
            });

            // Date filter events
            $('#minDate, #maxDate').on('change', function() {
                table.draw();
            });

            // Reset filter
            $('#resetFilter').on('click', function() {
                $('#minDate, #maxDate').val('');
                table.draw();
            });

            // Reset form when modal closes
            $('#modalTambahKondisi').on('hidden.bs.modal', function() {
                $('#formKondisi')[0].reset();
                $('#formKondisi select').each(function() {
                    $(this).val('1');
                });
                $('input[name="tanggal_pemeriksaan"]').val('{{ date('Y-m-d') }}');
            });
        });
    </script>
@endsection
