@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Manajemen Gaji Karyawan</h1>

        <div class="card shadow-sm" style="margin-bottom: 10%; border-radius: 12px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        @foreach ($karyawan as $divisi => $users)
                            {{-- Section Header Divisi --}}
                            <thead>
                                <tr>
                                    <th colspan="5" class="bg-primary bg-opacity-10 text-primary border-top border-primary border-opacity-25 py-2 px-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold" style="color: #ffffff;">{{ $divisi }}</span>
                                            <span class="badge bg-opacity-75 fw-normal">{{ count($users) }} karyawan</span>
                                        </div>
                                    </th>
                                </tr>
                                <tr class="table-light text-muted small">
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>Gaji Saat Ini</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $index => $item)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td>{{ $item->karyawan->nama_lengkap }}</td>
                                        <td class="text-muted">{{ $item->karyawan->jabatan ?? '-' }}</td>
                                        <td class="fw-semibold">Rp {{ number_format($item->karyawan->gaji, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary btn-detail"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailGajiModal"
                                                data-id="{{ $item->id }}"
                                                data-karyawan-id="{{ $item->karyawan->id }}"
                                                data-nama="{{ $item->karyawan->nama_lengkap }}"
                                                data-jabatan="{{ $item->karyawan->jabatan ?? '-' }}"
                                                data-divisi="{{ $item->karyawan->divisi ?? '-' }}"
                                                data-gaji="{{ $item->karyawan->gaji }}"
                                                data-log="{{ json_encode($item->karyawan->logGaji) }}"
                                            >
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted fst-italic py-3">Tidak ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Gaji --}}
    <div class="modal fade" id="detailGajiModal" tabindex="-1" aria-labelledby="detailGajiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailGajiModalLabel">Detail Gaji Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="fw-semibold text-muted" style="width: 110px;">Nama</td>
                                    <td>: <span id="detail-nama">-</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Jabatan</td>
                                    <td>: <span id="detail-jabatan">-</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-muted">Divisi</td>
                                    <td>: <span id="detail-divisi">-</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="p-3 bg-light rounded w-100 text-center">
                                <div class="text-muted small mb-1">Gaji Saat Ini</div>
                                <div class="fs-5 fw-bold text-primary" id="detail-gaji">-</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6 class="fw-semibold mb-2">Ubah Gaji</h6>
                        <form id="formEditGaji" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input
                                    type="number"
                                    id="input-gaji-baru"
                                    name="jumlah_gaji"
                                    class="form-control"
                                    min="0"
                                    step="1"
                                    placeholder="Masukkan nominal gaji baru"
                                    required
                                >
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                            <div class="form-text">Nominal dalam Rupiah, tanpa titik atau koma.</div>
                        </form>
                    </div>

                    <hr>

                    <h6 class="fw-semibold mb-2">Riwayat Gaji</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Bulan</th>
                                    <th class="text-center">Tahun</th>
                                    <th>Gaji</th>
                                    <th class="text-center">Dicatat</th>
                                </tr>
                            </thead>
                            <tbody id="log-gaji-body">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada riwayat gaji.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('detailGajiModal');

            modal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;

                const id      = btn.getAttribute('data-id');
                const nama    = btn.getAttribute('data-nama');
                const jabatan = btn.getAttribute('data-jabatan');
                const divisi  = btn.getAttribute('data-divisi');
                const gaji    = parseInt(btn.getAttribute('data-gaji')) || 0;
                const log     = JSON.parse(btn.getAttribute('data-log') || '[]');

                document.getElementById('detail-nama').textContent    = nama;
                document.getElementById('detail-jabatan').textContent = jabatan;
                document.getElementById('detail-divisi').textContent  = divisi;
                document.getElementById('detail-gaji').textContent    = 'Rp ' + gaji.toLocaleString('id-ID');

                const form = document.getElementById('formEditGaji');
                form.action = '{{ url("gaji") }}/' + id;
                document.getElementById('input-gaji-baru').value = gaji;

                const tbody = document.getElementById('log-gaji-body');

                if (!log.length) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Tidak ada riwayat gaji.</td></tr>`;
                    return;
                }

                log.sort((a, b) => b.id - a.id);
                
                tbody.innerHTML = log.map((row, i) => {
                    const gajiFormatted = 'Rp ' + parseInt(row.gaji).toLocaleString('id-ID');
                    const tanggal = row.created_at
                        ? new Date(row.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                        : '-';

                    return `
                        <tr>
                            <td class="text-center">${i + 1}</td>
                            <td class="text-center">${bulanNames[row.bulan] ?? row.bulan}</td>
                            <td class="text-center">${row.tahun}</td>
                            <td>${gajiFormatted}</td>
                            <td class="text-center text-muted small">${tanggal}</td>
                        </tr>
                    `;
                }).join('');
            });
        });
    </script>
@endsection