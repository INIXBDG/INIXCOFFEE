@extends('layouts_crm.app')

@section('crm_contents')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h4 class="fw-bold mb-0 text-dark">Laporan Penjualan</h4>

            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTargetPenjualan">
                <i class="bx bx-plus me-1"></i> Set Target Penjualan
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden glass-force">
            <div class="card-header bg-white pt-4 pb-0 border-0">
                <ul class="nav nav-tabs" id="tabLaporanTriwulan" role="tablist">
                    @foreach($laporan as $namaTriwulan => $dataTriwulan)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold {{ $loop->first ? 'active' : '' }}"
                                    id="nav-{{ $loop->index }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#pane-{{ $loop->index }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="pane-{{ $loop->index }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                {{ $namaTriwulan }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="tabLaporanTriwulanContent">
                    @foreach($laporan as $namaTriwulan => $dataTriwulan)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }} p-3"
                             id="pane-{{ $loop->index }}"
                             role="tabpanel"
                             aria-labelledby="nav-{{ $loop->index }}-tab"
                             tabindex="0">

                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle w-100 table-bordered">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th class="align-middle">Nama Sales</th>
                                            @foreach($dataTriwulan['nama_bulan'] as $bulan)
                                                <th class="align-middle">{{ $bulan }}</th>
                                            @endforeach
                                            <th class="align-middle">Total</th>
                                            <th class="align-middle">Target</th>
                                            <th class="align-middle">Kekurangan/Kelebihan Closing</th>
                                            <th class="align-middle">Presentase Closing</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($dataTriwulan['data_sales'] as $sales)
                                            <tr>
                                                <td class="fw-medium">{{ $sales['nama_sales'] }}</td>
                                                <td class="text-end">Rp {{ number_format($sales['bulan_1'], 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($sales['bulan_2'], 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($sales['bulan_3'], 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-primary">Rp {{ number_format($sales['total'], 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($sales['target'], 0, ',', '.') }}</td>
                                                <td class="text-end fw-semibold {{ $sales['selisih'] < 0 ? 'text-danger' : 'text-success' }}">
                                                    Rp {{ number_format($sales['selisih'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $sales['persentase'] >= 100 ? 'bg-success' : ($sales['persentase'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                        {{ $sales['persentase'] }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                    <tfoot class="table-warning fw-bold">
                                        <tr>
                                            <td class="text-center">TOTAL</td>
                                            <td class="text-end">Rp {{ number_format($dataTriwulan['total_keseluruhan']['bulan_1'], 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($dataTriwulan['total_keseluruhan']['bulan_2'], 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($dataTriwulan['total_keseluruhan']['bulan_3'], 0, ',', '.') }}</td>
                                            <td class="text-end text-primary">Rp {{ number_format($dataTriwulan['total_keseluruhan']['total'], 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($dataTriwulan['total_keseluruhan']['target'], 0, ',', '.') }}</td>
                                            <td class="text-end {{ $dataTriwulan['total_keseluruhan']['selisih'] < 0 ? 'text-danger' : 'text-success' }}">
                                                Rp {{ number_format($dataTriwulan['total_keseluruhan']['selisih'], 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">{{ $dataTriwulan['total_keseluruhan']['persentase'] }}%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalTargetPenjualan" tabindex="-1" aria-labelledby="modalTargetPenjualanLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('target_penjualan.store') }}" method="POST" class="modal-content shadow-lg border-0 rounded-4">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="modalTargetPenjualanLabel">Set Target Penjualan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">

                        @if(session('success'))
                            <div class="alert alert-success mb-3">{{ session('success') }}</div>
                        @endif

                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Nama Sales</label>
                            <select name="id_sales" class="form-select" required>
                                <option value="">-- Pilih Sales --</option>
                                @foreach($daftarSales as $sales)
                                    <option value="{{ $sales->id_sales }}">{{ $sales->username }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group mb-3">
                            <label class="form-label fw-semibold">Nilai Target (Rp)</label>
                            <input type="number" name="nilai_target" class="form-control" required min="0" placeholder="Contoh: 450000000">
                            <small class="text-muted">Masukkan angka tanpa titik atau koma.</small>
                        </div>

                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">Simpan Target</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('modalTargetPenjualan'));
        myModal.show();
    });
</script>
@endif

@endsection
