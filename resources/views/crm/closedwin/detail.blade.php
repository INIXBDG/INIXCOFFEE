@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Card Daftar Peluang -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Lead (Win)</h5>
                </div>
                <div class="card-body">
                    @if ($data->isEmpty())
                        <p class="text-muted">Tidak ada peluang yang tercatat untuk sales ini.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Materi</th>
                                        <th>Harga</th>
                                        <th>Netsales</th>
                                        <th>Periode</th>
                                        <th>Pax</th>
                                        <th>Final</th>
                                        <th>Sales</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $peluang)
                                        <tr data-peluang='@json($peluang)'>
                                            <td>{{ $peluang->materi }}</td>
                                            <td>Rp {{ number_format($peluang->harga, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($peluang->netsales, 0, ',', '.') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($peluang->periode_mulai)->translatedFormat('d M Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($peluang->periode_selesai)->translatedFormat('d M Y') }}
                                            </td>
                                            <td>{{ $peluang->pax }}</td>
                                            <td>
                                                @if (!is_null($peluang->final))
                                                Rp {{ number_format($peluang->final, 0, ',', '.') }}
                                                @else
                                                -
                                                @endif
                                            </td>
                                            <td>{{ $peluang->id_sales }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#detailModal">Detail</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal Detail Peluang -->
            <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailModalLabel">Detail Peluang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Informasi Lead -->
                                <div class="col-md-8">
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">Informasi Lead</h5>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#tambahAktivitasModal">Tambah Aktivitas</button>
                                        </div>
                                        <div class="card-body">
                                            <dl class="row">
                                                <dt class="col-sm-4">Materi</dt>
                                                <dd class="col-sm-8" id="modal-materi">-</dd>

                                                <dt class="col-sm-4">Catatan</dt>
                                                <dd class="col-sm-8" id="modal-catatan">-</dd>

                                                <dt class="col-sm-4">Harga</dt>
                                                <dd class="col-sm-8" id="modal-harga">-</dd>

                                                <dt class="col-sm-4">Net Sales</dt>
                                                <dd class="col-sm-8" id="modal-netsales">-</dd>

                                                <dt class="col-sm-4">Jumlah Peserta (Pax)</dt>
                                                <dd class="col-sm-8" id="modal-pax">-</dd>

                                                <dt class="col-sm-4">Periode Mulai</dt>
                                                <dd class="col-sm-8" id="modal-periode-mulai">-</dd>

                                                <dt class="col-sm-4">Periode Selesai</dt>
                                                <dd class="col-sm-8" id="modal-periode-selesai">-</dd>

                                                <dt class="col-sm-4">Contact</dt>
                                                <dd class="col-sm-8" id="modal-contact">-</dd>

                                                <dt class="col-sm-4">Sales</dt>
                                                <dd class="col-sm-8" id="modal-sales">-</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Tahapan -->
                                <div class="col-md-4">
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">Status Tahapan</h6>
                                            <span class="badge" id="modal-tahap">-</span>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li id="modal-biru" class="d-none">
                                                    <strong class="text-primary">Update Biru:</strong><br>
                                                    <span id="modal-biru-date">-</span>
                                                </li>
                                                <li id="modal-merah" class="d-none">
                                                    <strong class="text-danger">Update Merah:</strong><br>
                                                    <span id="modal-merah-date">-</span>
                                                </li>
                                                <li id="modal-final" class="d-none">
                                                    <strong class="text-success">Final Harga:</strong><br>
                                                    <span id="modal-final-value">-</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aktivitas Terkait -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Aktivitas Terkait</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" id="modal-aktivitas">
                                        <p class="text-muted">Belum ada aktivitas yang tercatat.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const detailModal = document.getElementById('detailModal');
            detailModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const peluang = JSON.parse(button.closest('tr').dataset.peluang);

                // Isi Informasi Lead
                document.getElementById('modal-materi').textContent = peluang.materi || '-';
                document.getElementById('modal-catatan').textContent = peluang.catatan || '-';
                document.getElementById('modal-harga').textContent = peluang.harga ? 'Rp ' + Number(peluang
                    .harga).toLocaleString('id-ID') : '-';
                document.getElementById('modal-netsales').textContent = peluang.netsales ? 'Rp ' + Number(
                    peluang.netsales).toLocaleString('id-ID') : '-';
                document.getElementById('modal-pax').textContent = peluang.pax || '-';
                document.getElementById('modal-periode-mulai').textContent = peluang.periode_mulai ?
                    new Date(peluang.periode_mulai).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    }) : '-';
                document.getElementById('modal-periode-selesai').textContent = peluang.periode_selesai ?
                    new Date(peluang.periode_selesai).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    }) : '-';
                document.getElementById('modal-contact').textContent = peluang.perusahaan.nama_perusahaan || '-';
                document.getElementById('modal-sales').textContent = peluang.id_sales || '-';

                // Isi Status Tahapan
                const tahapBadge = document.getElementById('modal-tahap');
                const badgeColor = {
                    'hitam': 'bg-secondary',
                    'biru': 'bg-primary',
                    'merah': 'bg-danger'
                } [peluang.tahap] || 'bg-dark';
                tahapBadge.className = 'badge ' + badgeColor;
                tahapBadge.textContent = peluang.tahap ? peluang.tahap.toUpperCase() : '-';

                // Update Status Tahapan
                document.getElementById('modal-biru').classList.toggle('d-none', !peluang.biru);
                document.getElementById('modal-biru-date').textContent = peluang.biru ? new Date(peluang
                    .biru).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }) : '-';

                document.getElementById('modal-merah').classList.toggle('d-none', !peluang.merah);
                document.getElementById('modal-merah-date').textContent = peluang.merah ? new Date(peluang
                    .merah).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                }) : '-';

                document.getElementById('modal-final').classList.toggle('d-none', !peluang.final);
                document.getElementById('modal-final-value').textContent = peluang.final ? 'Rp ' + Number(
                    peluang.final).toLocaleString('id-ID') : '-';

                // Isi Aktivitas Terkait
                const aktivitasContainer = document.getElementById('modal-aktivitas');
                if (!peluang.aktivitas || peluang.aktivitas.length === 0) {
                    aktivitasContainer.innerHTML =
                        '<p class="text-muted">Belum ada aktivitas yang tercatat.</p>';
                } else {
                    let tableHTML = `
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Jenis</th>
                                        <th>Subjek</th>
                                        <th>Deskripsi</th>
                                        <th>Tanggal</th>
                                        <th>Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                    peluang.aktivitas.forEach((item, index) => {
                        if (item.id_sales === peluang.id_sales) {
                            tableHTML += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.aktivitas || '-'}</td>
                                        <td>${item.subject || '-'}</td>
                                        <td>${item.deskripsi || '-'}</td>
                                        <td>${item.waktu_aktivitas ? new Date(item.waktu_aktivitas).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-'}</td>
                                        <td>${item.id_sales || '-'}</td>
                                    </tr>
                                `;
                        }
                    });
                    tableHTML += '</tbody></table>';
                    aktivitasContainer.innerHTML = tableHTML;
                }
            });
        });
    </script>
@endsection
