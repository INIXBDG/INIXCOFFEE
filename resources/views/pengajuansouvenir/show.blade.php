@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('pengajuansouvenir.index') }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Kembali
                    </a>

                   <h5 class="card-title">
                        Detail Pengajuan Souvenir
                   </h5>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-4"><p>Nama Karyawan</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>{{ $data->karyawan->nama_lengkap ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Divisi</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>{{ $data->karyawan->divisi ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Jabatan</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>{{ $data->karyawan->jabatan ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Tipe Pengajuan</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>Souvenir</p></div>

                                <div class="col-md-4"><p>Vendor</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7"><p>{{ $data->vendor->nama ?? '-' }}</p></div>

                                <div class="col-md-4"><p>Total Pengajuan</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    <p class="fw-bold" id="infoTotalDisplay">Rp {{ number_format($data->total_keseluruhan, 0, ',', '.') ?? '-' }}</p>
                                </div>

                                <div class="col-md-4"><p>Bukti Invoice</p></div>
                                <div class="col-md-1"><p>:</p></div>
                                <div class="col-md-7">
                                    @if ($data->invoice)
                                        <div class="mb-2">
                                            <a href="{{ asset('storage/' . $data->invoice) }}" target="_blank" class="btn btn-sm btn-info text-white">
                                                <img src="{{ asset('icon/file-text.svg') }}" width="16px" style="filter: invert(1);"> Lihat Invoice
                                            </a>
                                        </div>
                                    @else
                                        <p class="text-muted fst-italic mb-2">Belum ada invoice</p>
                                    @endif

                                    @php
                                        $isOwnerOrFinance = (auth()->user()->karyawan_id == $data->id_karyawan) || (auth()->user()->karyawan->jabatan == 'Finance & Accounting');
                                        $isFinal = $data->tracking->tracking === 'Selesai' || str_contains($data->tracking->tracking, 'Ditolak');
                                    @endphp

                                    @if ($isOwnerOrFinance && !$isFinal)
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#uploadInvoiceModal"
                                            onclick="openUploadInvoiceModal()">
                                            {{ $data->invoice ? 'Ganti Invoice' : 'Upload Invoice' }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            @php
                                $canEdit = auth()->user()->karyawan_id == $data->id_karyawan &&
                                        $data->tracking->tracking !== 'Selesai' &&
                                        !str_contains($data->tracking->tracking, 'Ditolak');
                            @endphp
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">Detail Item Souvenir</h5>
                                        <div>
                                                @if ($canEdit)
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editItemsModal">
                                                    <img src="{{ asset('icon/edit.svg') }}" width="16px"> Edit Item
                                                </button>
                                                @endif

                                                <a href="{{ route('pengajuansouvenir.exportpdf', $data->id) }}" target="_blank" class="btn btn-danger btn-sm">
                                                    <img src="{{ asset('icon/file-pdf.svg') }}" width="16px" style="filter: invert(1);"> Export PDF
                                                </a>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Souvenir</th>
                                                    <th>Pax</th>
                                                    <th>Harga Satuan</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($data->detail as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $item->souvenir->nama_souvenir ?? 'N/A' }}</td>
                                                        <td>{{ $item->pax }}</td>
                                                        <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                                        <td>Rp {{ number_format($item->harga_total, 0, ',', '.') }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">Belum ada detail item</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" class="text-end">Total Keseluruhan:</th>
                                                    <th>Rp {{ number_format($data->total_keseluruhan, 0, ',', '.') ?? '0' }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body">
                                    <h5 class="card-title">Tracking Pengajuan</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($tracking as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                                                        <td>{{ $item->tracking }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center">Belum ada data tracking</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($canEdit)
<div class="modal fade" id="editItemsModal" tabindex="-1" aria-labelledby="editItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('pengajuansouvenir.updateItems', $data->id) }}" id="itemForm">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="editItemsModalLabel">Edit Detail Item Souvenir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="itemContainer">
                        @forelse ($data->detail as $item)
                            <div class="item mb-3 p-3 border rounded">
                                <input type="hidden" name="souvenir[detail_id][]" value="{{ $item->id }}">
                                <div class="row">
                                    <label class="col-md-4 col-form-label text-md-start">Souvenir</label>
                                    <div class="col-md-6">
                                        <select name="souvenir[id][]" class="form-select souvenir-select" required>
                                            @foreach($souvenirs as $souvenir)
                                                <option value="{{ $souvenir->id }}" {{ $item->id_souvenir == $souvenir->id ? 'selected' : '' }}>
                                                    {{ $souvenir->nama_souvenir }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm removeItemButton">Hapus</button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <label class="col-md-4 col-form-label text-md-start">Jumlah (Pax)</label>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control item-pax" name="souvenir[pax][]" value="{{ $item->pax }}" required min="1">
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <label class="col-md-4 col-form-label text-md-start">Harga Satuan</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp.</span>
                                            <input type="text" class="form-control item-harga-satuan" name="souvenir[harga_satuan][]" value="{{ number_format($item->harga_satuan, 0, ',', '.') }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <label class="col-md-4 col-form-label text-md-start">Subtotal</label>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">Rp.</span>
                                            <input type="text" class="form-control item-harga-total" value="{{ number_format($item->harga_total, 0, ',', '.') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p id="noItemMessage">Tidak ada item. Silakan tambahkan.</p>
                        @endforelse
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" id="addItem" class="btn btn-primary btn-sm">Tambah Item Baru</button>
                        </div>
                    </div>

                    <hr>
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <h5 class="text-end">Total Keseluruhan:</h5>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-end" id="grandTotalDisplay">Rp {{ number_format($data->total_keseluruhan, 0, ',', '.') ?? '0' }}</h5>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="itemTemplate" style="display: none;">
    <div class="item mb-3 p-3 border rounded">
        <input type="hidden" name="souvenir[detail_id][]" value="">
        <div class="row">
            <label class="col-md-4 col-form-label text-md-start">Souvenir</label>
            <div class="col-md-6">
                <select name="souvenir[id][]" class="form-select souvenir-select" required>
                    <option value="" disabled selected>Pilih Souvenir</option>
                    @foreach($souvenirs as $souvenir)
                        <option value="{{ $souvenir->id }}">{{ $souvenir->nama_souvenir }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm removeItemButton">Hapus</button>
            </div>
        </div>
        <div class="row mt-2">
            <label class="col-md-4 col-form-label text-md-start">Jumlah (Pax)</label>
            <div class="col-md-6">
                <input type="number" class="form-control item-pax" name="souvenir[pax][]" required min="1">
            </div>
        </div>
        <div class="row mt-2">
            <label class="col-md-4 col-form-label text-md-start">Harga Satuan</label>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">Rp.</span>
                    <input type="text" class="form-control item-harga-satuan" name="souvenir[harga_satuan][]" required>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <label class="col-md-4 col-form-label text-md-start">Subtotal</label>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">Rp.</span>
                    <input type="text" class="form-control item-harga-total" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    @if ($canEdit)
    $(document).ready(function () {
        function getNumericValue(value) {
            if (typeof value === 'undefined' || value === null || value === '') return 0;
            var cleanValue = value.toString().replace(/[^0-9]/g, '');
            return parseInt(cleanValue) || 0;
        }

        function formatRupiah(angka) {
            if (typeof angka === 'undefined' || angka === null || angka === '') return '';

            var number_string = angka.toString().replace(/[^0-9]/g, '');

            if (number_string === '') return '';

            var sisa = number_string.length % 3,
                rupiah = number_string.substr(0, sisa),
                ribuan = number_string.substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah || '0';
        }

        function calculateItemTotal(itemElement) {
            var $item = $(itemElement);
            var pax = getNumericValue($item.find('.item-pax').val());
            var hargaSatuan = getNumericValue($item.find('.item-harga-satuan').val());

            var total = pax * hargaSatuan;

            if (hargaSatuan === 0 && $item.find('.item-harga-satuan').val() === '') {
                 $item.find('.item-harga-total').val(formatRupiah(0)); // Subtotal tetap 0
            } else {
                 $item.find('.item-harga-total').val(formatRupiah(total));
            }

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            var grandTotal = 0;
            $('#itemForm .item-harga-total').each(function() {
                grandTotal += getNumericValue($(this).val());
            });
            var formattedTotal = 'Rp ' + formatRupiah(grandTotal);
            $('#grandTotalDisplay').text(formattedTotal);
            $('#infoTotalDisplay').text(formattedTotal);
        }

        $(document).on('input', '.item-harga-satuan', function() {
            var selection = this.selectionStart;
            var originalLength = this.value.length;
            var currentValue = this.value;

            if (currentValue.replace(/[^0-9]/g, '') === '') {
                 this.value = '';
                 calculateItemTotal($(this).closest('.item'));
                 return;
            }

            this.value = formatRupiah(currentValue);

            var newLength = this.value.length;
            selection = selection + (newLength - originalLength);
            this.setSelectionRange(selection, selection);

            calculateItemTotal($(this).closest('.item'));
        });

        $(document).on('click', '.removeItemButton', function() {
            var $item = $(this).closest('.item');
            var detailId = $item.find('input[name="souvenir[detail_id][]"]').val();
            if (detailId) {
                $('#itemForm').append('<input type="hidden" name="deleted_items[]" value="' + detailId + '">');
            }
            $item.remove();
            calculateGrandTotal();
        });

        $('#addItem').click(function() {
            var newItem = $('#itemTemplate').html();
            $('#itemContainer').append(newItem);
            $('#noItemMessage').hide();
        });

        $(document).on('input', '.item-pax', function() {
            calculateItemTotal($(this).closest('.item'));
        });

        $('#itemForm').on('submit', function (e) {
            e.preventDefault();
            $('#loadingModal').modal('show');

            $('.item-harga-satuan').each(function() {
                $(this).val(getNumericValue($(this).val()));
            });
            $('.item-harga-total').each(function() {
                $(this).val(getNumericValue($(this).val()));
            });

            this.submit();
        });

        $('#editItemsModal').on('shown.bs.modal', function () {
            $('.item-harga-satuan').each(function() {
                var rawValue = $(this).val();
                var numericValue = getNumericValue(rawValue);
                $(this).val(formatRupiah(numericValue));
            });

            $('.item').each(function() {
                if ($(this).is(':visible')) {
                    calculateItemTotal(this);
                }
            });

            calculateGrandTotal();
        });


    });
    @endif
</script>
@endpush
