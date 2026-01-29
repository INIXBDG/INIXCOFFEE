@extends('layouts.app')

@section('content')
    <div class="container">
        
        {{-- BUTTON KEMBALI (DIPERBAIKI) --}}
        <div class="mb-3">
            <a href="{{ url('/outstanding') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        {{-- ROW INFORMASI UTAMA (3 KOLOM) --}}
        <div class="row">
            {{-- KOLOM 1: DATA UTAMA RKM --}}
            <div class="col-md-4">
                <div class="card mb-3 h-100">
                    <div class="card-header fw-bold">RKM</div>
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tr>
                                <th>ID RKM</th>
                                <td>{{ $rkm->id }}</td>
                            </tr>
                            <tr>
                                <th>Event</th>
                                <td>{{ $rkm->event }}</td>
                            </tr>
                            <tr>
                                <th>Metode Kelas</th>
                                <td>{{ $rkm->metode_kelas }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($rkm->tanggal_awal)->format('d F Y') }}
                                    <br> s/d <br>
                                    {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->format('d F Y') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Harga Jual</th>
                                <td>Rp {{ number_format($rkm->harga_jual, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Pax</th>
                                <td>{{ $rkm->pax }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- KOLOM 2: PERUSAHAAN --}}
            <div class="col-md-4">
                <div class="card mb-3 h-100">
                    <div class="card-header fw-bold">Perusahaan</div>
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tr>
                                <th>Nama Perusahaan</th>
                                <td>{{ $rkm->perusahaan->nama_perusahaan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>{{ $rkm->perusahaan->kategori_perusahaan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Lokasi</th>
                                <td>{{ $rkm->perusahaan->lokasi ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- KOLOM 3: MATERI --}}
            <div class="col-md-4">
                <div class="card mb-3 h-100">
                    <div class="card-header fw-bold">Materi</div>
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tr>
                                <th>Nama Materi</th>
                                <td>{{ $rkm->materi->nama_materi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kode Materi</th>
                                <td>{{ $rkm->materi->kode_materi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>{{ $rkm->materi->kategori_materi ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- END ROW --}}

        {{-- OUTSTANDING --}}
        <div class="card mb-3 mt-3">
            <div class="card-header fw-bold">Outstanding</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%;">Status Pembayaran</th>
                        <td>
                            @if ($rkm->outstanding?->status_pembayaran == 1)
                                <span class="badge bg-success">Lunas</span>
                            @else
                                <span class="badge bg-danger">Belum Lunas</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Net Sales</th>
                        <td>Rp {{ number_format($rkm->outstanding->net_sales ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Due Date</th>
                        <td>
                            {{ !empty($rkm->outstanding->due_date) ? \Carbon\Carbon::parse($rkm->outstanding->due_date)->format('d F Y') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>No Invoice</th>
                        <td>{{ $rkm->outstanding->no_invoice ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Faktur Pajak</th>
                        <td>
                            @if (!empty($rkm->outstanding->path_faktur_pajak))
                                <a href="{{ asset('storage/' . $rkm->outstanding->path_faktur_pajak) }}" target="_blank"
                                    class="btn btn-sm btn-primary">
                                    Lihat Faktur
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Dokumen Tambahan</th>
                        <td>
                            @if (!empty($rkm->outstanding->path_dokumen_tambahan))
                                @php
                                    $docPath = str_replace('public/', '', $rkm->outstanding->path_dokumen_tambahan);
                                @endphp
                                <a href="{{ asset('storage/' . $docPath) }}" target="_blank"
                                    class="btn btn-sm btn-secondary">
                                    Lihat Dokumen
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

{{-- PERHITUNGAN NET SALES --}}
        <div class="mt-4 mb-3">
            <h5 class="fw-bold">Perhitungan Net Sales</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-nowrap align-middle table-sm mb-0"
                    style="font-size: 0.85rem; background-color: #fff;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Peserta</th>
                            <th>Tanggal PA</th>
                            <th>Harga Penawaran</th>
                            <th>Transportasi</th>
                            <th>Akomodasi Peserta</th>
                            <th>Penginapan</th>
                            <th>Meeting Room</th>
                            <th>Akom. Sales/Instruktur</th>
                            <th>Reimburse Trans.</th>
                            <th>Sewa Laptop</th>
                            <th>Fresh Money</th>
                            <th>Diskon</th>
                            <th>Entertaint</th>
                            <th>Souvenir</th>
                            <th>Pajak</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rkm->perhitunganNetSales as $i => $pns)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>{{ $pns->peserta->nama ?? '-' }}</td>
                                <td>
                                    {{ !empty($pns->tgl_pa) ? \Carbon\Carbon::parse($pns->tgl_pa)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-end fw-bold">
                                    {{ number_format($pns->harga_penawaran ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ number_format($pns->transportasi ?? 0, 0, ',', '.') }}</span>
                                        <small class="text-muted fst-italic" style="font-size: 0.75rem;">
                                            {{ $pns->jenis_transportasi ?? '-' }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->akomodasi_peserta ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->penginapan ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->penginapan_meeting_room ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->akomodasi_sales_instruktur ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->reimburse_transport_sales_instruktur ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->sewa_laptop ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->fresh_money ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->diskon ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ number_format($pns->entertaint ?? 0, 0, ',', '.') }}</span>
                                        @if (!empty($pns->deskripsi_entertaint))
                                            <small class="text-muted fst-italic text-wrap"
                                                style="font-size: 0.75rem; min-width: 150px; max-width: 200px;">
                                                {{ Str::limit($pns->deskripsi_entertaint, 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->souvenir ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    {{ number_format($pns->pajak ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if (!empty($pns->desc))
                                        <span class="d-inline-block text-truncate" style="max-width: 120px;"
                                            title="{{ $pns->desc }}">
                                            {{ $pns->desc }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="17" class="text-center py-3">Data tidak tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- FOOTER TOTAL SUM --}}
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td colspan="3" class="text-center">TOTAL</td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('harga_penawaran'), 0, ',', '.') }}
                            </td>
                            <td>
                                {{ number_format($rkm->perhitunganNetSales->sum('transportasi'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('akomodasi_peserta'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('penginapan'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('penginapan_meeting_room'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('akomodasi_sales_instruktur'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('reimburse_transport_sales_instruktur'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('sewa_laptop'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('fresh_money'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('diskon'), 0, ',', '.') }}
                            </td>
                            <td>
                                {{ number_format($rkm->perhitunganNetSales->sum('entertaint'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('souvenir'), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                {{ number_format($rkm->perhitunganNetSales->sum('pajak'), 0, ',', '.') }}
                            </td>
                            <td></td> {{-- Kolom Keterangan Kosong --}}
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection