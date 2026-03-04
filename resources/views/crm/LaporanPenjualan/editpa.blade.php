@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $canEdit = in_array(Auth::user()->jabatan, ['Adm Sales', 'SPV Sales', 'Sales']);
    @endphp
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            {{-- Header Page --}}
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Detail Payment Advance (PA)</h4>
                <p class="text-muted mb-0">Kelola dan pantau detail pembayaran advance</p>
            </div>

            <div class="d-flex flex-wrap gap-3 mb-4 align-items-center">
                <a href="{{ route('crm.laporanPenjualan') }}" class="btn btn-secondary d-flex align-items-center gap-2">
                    Kembali
                </a>

                <a href="{{ route('netsales.detail', $netsales->id_rkm) }}"
                    class="btn btn-primary d-flex align-items-center gap-2" target="_blank"> {{-- optional: buka di tab baru --}}
                    Lihat Detail (INIXCOFFEE)
                </a>
            </div>

            {{-- Card 1: Table Payment Advance --}}

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Data Payment Advance</h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th>Transportasi</th>
                                    <th>Jenis Transportasi</th>
                                    <th>Akomodasi Peserta</th>
                                    <th>Akomodasi Tim</th>
                                    <th>Keterangan Akomodasi Tim</th>
                                    <th>Fresh Money</th>
                                    <th>Entertainment</th>
                                    <th>Keterangan Entertainment</th>
                                    <th>Souvenir</th>
                                    <th>Cashback</th>
                                    <th>Sewa Laptop</th>
                                    <th>Tgl. PA</th>
                                    <th>Pembayaran</th>
                                    <th>Deskripsi Tambahan</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pa as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->transportasi ? 'Rp ' . number_format($item->transportasi, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $item->jenis_transportasi ?? '-' }}</td>
                                        <td>{{ $item->akomodasi_peserta ? 'Rp ' . number_format($item->akomodasi_peserta, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $item->akomodasi_tim ? 'Rp ' . number_format($item->akomodasi_tim, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $item->keterangan_akomodasi_tim ?? '-' }}</td>
                                        <td>{{ $item->fresh_money ? 'Rp ' . number_format($item->fresh_money, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $item->entertaint ? 'Rp ' . number_format($item->entertaint, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $item->keterangan_entertaint ?? '-' }}</td>
                                        <td>{{ $item->souvenir ? 'Rp ' . number_format($item->souvenir, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $item->cashback ? 'Rp ' . number_format($item->cashback, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $item->sewa_laptop ? 'Rp ' . number_format($item->sewa_laptop, 0, ',', '.') : '-' }}
                                        </td>
                                        <td>{{ $item->tgl_pa ? \Carbon\Carbon::parse($item->tgl_pa)->translatedFormat('d/m/Y') : '-' }}
                                        </td>
                                        <td>
                                            @if ($item->tipe_pembayaran)
                                                <span
                                                    class="badge bg-{{ $item->tipe_pembayaran === 'cash' ? 'success' : ($item->tipe_pembayaran === 'transfer' ? 'info' : 'warning') }}">
                                                    {{ ucfirst($item->tipe_pembayaran) }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->deskripsi_tambahan ?? '-' }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary btn-edit"
                                                data-id="{{ $item->id }}"
                                                data-transportasi="{{ $item->transportasi }}"
                                                data-jenis_transportasi="{{ $item->jenis_transportasi }}"
                                                data-akomodasi_peserta="{{ $item->akomodasi_peserta }}"
                                                data-akomodasi_tim="{{ $item->akomodasi_tim }}"
                                                data-keterangan_akomodasi_tim="{{ $item->keterangan_akomodasi_tim }}"
                                                data-cashback="{{ $item->cashback }}"
                                                data-fresh_money="{{ $item->fresh_money }}"
                                                data-entertaint="{{ $item->entertaint }}"
                                                data-keterangan_entertaint ="{{ $item->keterangan_entertaint }}"
                                                data-souvenir="{{ $item->souvenir }}" data-tgl_pa="{{ $item->tgl_pa }}"
                                                data-tipe_pembayaran="{{ $item->tipe_pembayaran }}"
                                                data-sewa_laptop="{{ $item->sewa_laptop }}"
                                                data-deskripsi="{{ $item->deskripsi_tambahan }}"
                                                {{ $canEdit ? '' : 'disabled' }}>
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="19" class="text-center text-muted py-5">
                                            Belum ada data Payment Advance
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($pa->isNotEmpty())
                    <div class="card-footer">
                        <div class="row" style="margin-top: 20px">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <small class="text-muted d-block mb-1">Total Biaya</small>
                                <h5 class="mb-0 text-success">
                                    Rp
                                    {{ number_format(
                                        $pa->sum('transportasi') +
                                            $pa->sum('akomodasi_peserta') +
                                            $pa->sum('akomodasi_tim') +
                                            $pa->sum('sewa_laptop') +
                                            $pa->sum('cashback') +
                                            $pa->sum('fresh_money') +
                                            $pa->sum('entertaint') +
                                            $pa->sum('souvenir'),
                                        0,
                                        ',',
                                        '.',
                                    ) }}
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Netsales</small>
                                <h5 class="mb-0 text-info">
                                    Rp
                                    {{ number_format($totalPA, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                    </div>
                @endif

            </div>


            {{-- Card 2: Tracking Information --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 text-white">Tracking Information</h5>
                </div>
                <div class="card-body" style="margin-top: 20px">
                    @if ($netsales->first() && $netsales->first()->trackingNetSales)
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <small class="text-muted d-block mb-1">Tracking Number</small>
                                <p class="mb-0 fw-medium">{{ $netsales->first()->trackingNetSales->tracking ?? '-' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">Tanggal Dibuat</small>
                                <p class="mb-0 fw-medium">
                                    {{ $netsales->first()->created_at ? \Carbon\Carbon::parse($netsales->first()->created_at)->translatedFormat('d F Y H:i') : '-' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0 text-center py-3">Belum ada data tracking untuk Payment Advance ini
                        </p>
                    @endif
                </div>
            </div>

            {{-- Card 3: Approval Information --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0 text-white">Approval Information</h5>
                </div>
                <div class="card-body" style="margin-top: 20px">
                    @if ($netsales->first() && $netsales->first()->approvedNetSales->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th width="15%" class="text-center">Status</th>
                                        <th width="25%">Approver</th>
                                        <th>Keterangan</th>
                                        <th width="20%">Tanggal</th>
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
                                                default => $approval->level_status ?? '-',
                                            };
                                            $badgeColor = match (true) {
                                                $status === 'Diproses' => 'warning',
                                                $status === 'Disetujui' => 'success',
                                                $status === 'Ditolak' => 'danger',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $badgeColor }}">{{ $status }}</span>
                                            </td>
                                            <td class="fw-medium">{{ $approver }}</td>
                                            <td>{{ $approval->keterangan ?? '-' }}</td>
                                            <td>
                                                {{ $approval->created_at ? \Carbon\Carbon::parse($approval->created_at)->translatedFormat('d F Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0 text-center py-3">Belum ada data approval untuk Payment Advance ini
                        </p>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="table-light">
                            <th>User</th>
                            <th>Field</th>
                            <th>Sebelum</th>
                            <th>Sesudah</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    @foreach ($historyNet as $history)
                        <tbody>
                            {{-- Di sini kuncinya: kita looping isi dari kolom 'data' --}}
                            @foreach ($history->data as $field => $value)
                                <tr>
                                    <td><strong>{{ $history->user->karyawan->nama_lengkap ?? '-' }}</strong></td>
                                    <td><strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong></td>
                                    <td class="text-danger">
                                        {{ is_numeric($value['before']) ? 'Rp ' . number_format((float) $value['before'], 0, ',', '.') : $value['before'] ?? '-' }}
                                    </td>
                                    <td class="text-success">
                                        {{ is_numeric($value['after']) ? 'Rp ' . number_format((float) $value['after'], 0, ',', '.') : $value['after'] ?? '-' }}
                                    </td>
                                    <td>{{ $history->created_at->translatedFormat('d F Y, H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @endforeach
                    </table>
                </div>

        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Payment Advance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Transportasi</label>
                                <input type="text" class="form-control rupiah" id="editTransportasi"
                                    name="transportasi" placeholder="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jenis Transportasi</label>
                                <input type="text" class="form-control" id="jenisTransportasi"
                                    name="jenis_transportasi" placeholder="Kendaraan">
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Akomodasi Peserta</label>
                                <input type="text" class="form-control rupiah" id="editAkoPeserta"
                                    name="akomodasi_peserta" placeholder="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Akomodasi Tim</label>
                                <input type="text" class="form-control rupiah" id="editAkoTim" name="akomodasi_tim"
                                    placeholder="0">
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Cashback</label>
                                <input type="text" class="form-control rupiah" id="editCashback" name="cashback"
                                    placeholder="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fresh Money</label>
                                <input type="text" class="form-control rupiah" id="editFreshMoney" name="fresh_money"
                                    placeholder="0">
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Entertainment</label>
                                <input type="text" class="form-control rupiah" id="editEntertaint" name="entertaint"
                                    placeholder="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Souvenir</label>
                                <input type="text" class="form-control rupiah" id="editSouvenir" name="souvenir"
                                    placeholder="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Sewa Laptop</label>
                                <input type="text" class="form-control rupiah" id="editSewaLaptop" name="sewa_laptop"
                                    placeholder="Optional">
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal PA</label>
                                <input type="date" class="form-control" id="editTglPa" name="tgl_pa">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tipe Pembayaran</label>
                                <select class="form-select" id="editTipePembayaran" name="tipe_pembayaran">
                                    <option value="">Pilih tipe...</option>
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Keterangan Entertainment</label>
                            <textarea class="form-control" id="keteranganEntertaint" name="keterangan_entertaint" rows="2"></textarea>
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Keterangan Akomodasi Tim</label>
                            <textarea class="form-control" id="keteranganAkoTim" name="keterangan_akomodasi_tim" rows="2"></textarea>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="editDeskripsi" name="deskripsi_tambahan" rows="3"></textarea>
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


    {{-- Modal Deskripsi --}}
    @foreach ($pa as $item)
        @if ($item->desc)
            <div class="modal fade" id="deskripsiModal{{ $item->id }}" tabindex="-1"
                aria-labelledby="deskripsiModalLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deskripsiModalLabel{{ $item->id }}">Deskripsi Lengkap
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="white-space: pre-wrap;">
                            {{ $item->desc }}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            function formatRupiah(angka) {
                if (angka === null || angka === undefined) return '';
                let numberString = angka.toString().replace(/[^\d.,]/g, '');

                const split = numberString.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    const separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah ? 'Rp ' + rupiah : '';
            }

            function parseRupiah(rupiahString) {
                if (!rupiahString) return '';
                return rupiahString.replace(/[^0-9]/g, '');
            }

            $(document).on('input', '.rupiah', function() {
                let value = $(this).val();
                let rawValue = parseRupiah(value);
                $(this).val(formatRupiah(rawValue));
            });

            $(document).on('click', '.btn-edit', function() {
                const data = $(this).data();

                $('#editId').val(data.id);
                $('#editTransportasi').val(formatRupiah(data.transportasi || 0));
                $('#jenisTransportasi').val(data.jenis_transportasi || 0);
                $('#editAkoPeserta').val(formatRupiah(data.akomodasi_peserta || 0));
                $('#editAkoTim').val(formatRupiah(data.akomodasi_tim || 0));
                $('#editCashback').val(formatRupiah(data.cashback || 0));
                $('#editFreshMoney').val(formatRupiah(data.fresh_money || 0));
                $('#editEntertaint').val(formatRupiah(data.entertaint || 0));
                $('#editSouvenir').val(formatRupiah(data.souvenir || 0));
                $('#editSewaLaptop').val(formatRupiah(data.sewa_laptop || 0));

                $('#editTglPa').val(data.tgl_pa || '');
                $('#editTipePembayaran').val(data.tipe_pembayaran || '');
                $('#editDeskripsi').val(data.deskripsi || '');

                $('#keteranganEntertaint').val(data.keterangan_entertaint || '');
                $('#keteranganAkoTim').val(data.keterangan_akomodasi_tim || '');

                new bootstrap.Modal(document.getElementById('editModal')).show();
            });


            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#editId').val();
                const form = this;
                const submitButton = $(form).find('button[type="submit"]');

                // UI Loading State
                submitButton.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...'
                );

                const formData = new FormData(form);
                const serializedData = {};

                const currencyFields = [
                    'transportasi',
                    'akomodasi_peserta',
                    'akomodasi_tim',
                    'cashback',
                    'fresh_money',
                    'entertaint',
                    'souvenir',
                    'sewa_laptop',
                ];

                for (let [key, value] of formData.entries()) {
                    if (currencyFields.includes(key)) {
                        serializedData[key] = parseRupiah(value) || 0;
                    } else {
                        serializedData[key] = value;
                    }
                }

                const queryString = $.param(serializedData);

                $.ajax({
                    url: `{{ url('/crm/update/pa') }}/${id}`,
                    type: 'PUT',
                    data: queryString,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(response) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            'editModal'));
                        modal.hide();
                        Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success').then(
                            () => {
                                location.reload();
                            });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', errorMessage, 'error');
                    },
                    complete: function() {
                        submitButton.prop('disabled', false).html('Simpan Perubahan');
                    }
                });
            });
        });
    </script>
@endsection
