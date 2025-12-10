@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="updateExpenseHubData" tabindex="-1" aria-labelledby="updateExpenseHubDataLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateExpenseHubDataLabel">Update Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateBarangForm" method="POST" action="{{ route('catering.update', $data['id']) }}">
                        @csrf
                        @method('PUT')
                        <div id="itemsContainer">
                            @foreach ($data['detail'] as $index => $detail)
                            <div class="item-section mb-4 p-3 border rounded">
                                <input type="hidden" name="id_detail_catering[]" value="{{ $detail['id'] }}">
                                <input type="hidden" name="tipe_detail[]" class="tipe-detail-input" value="{{ $detail['tipe_detail'] }}">
                                <input type="hidden" name="current_vendor_id[]" class="current-vendor-id" value="{{ $detail['id_vendor'] }}">
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label">Nama Makanan</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="nama_makanan[]"
                                            value="{{ old('nama_makanan.'.$index, $detail['nama_makanan']) }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm delete-item" data-id="{{ $detail['id'] }}">Hapus</button>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label">Qty</label>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" name="qty[]"
                                            value="{{ old('qty.'.$index, $detail['jumlah']) }}" min="1" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label">Harga (Rp.)</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control rupiah-input" name="harga[]"
                                            value="{{ old('harga.'.$index, number_format($detail['harga'], 0, ',', '.')) }}" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label">Tipe Catering</label>
                                    <div class="col-md-6">
                                        <select name="tipe_detail_form[]" class="form-control tipe-detail-select" required>
                                            <option value="Coffee Break" {{ $detail['tipe_detail'] == 'Coffee Break' ? 'selected' : '' }}>Coffee Break</option>
                                            <option value="Makan Siang" {{ $detail['tipe_detail'] == 'Makan Siang' ? 'selected' : '' }}>Makan Siang</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label">Vendor</label>
                                    <div class="col-md-6">
                                        <select name="vendor[]" class="form-control vendor-select" required>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label">Keterangan</label>
                                    <div class="col-md-6">
                                        <textarea class="form-control" name="keterangan[]" rows="3">{{ old('keterangan.'.$index, $detail['keterangan']) }}</textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="addItemButton" class="btn btn-primary mb-3">Tambah Item</button>
                        <input type="hidden" name="deleted_ids" id="deletedIdsInput">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="updateBarangForm" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-primary my-2 mb-3">
                        <img src="/icon/arrow-left.svg" class="img-responsive me-1" width="16px" height="16px" alt="Back Icon"> Back
                    </a>
                    <h5 class="card-title">Detail Pengajuan Catering</h5>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Nama Karyawan</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['nama_karyawan'] }}</p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Divisi</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['divisi'] }}</p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Jabatan</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['jabatan'] }}</p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Tipe Pengajuan</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['tipe'] }}</p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Status Pengajuan</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['status'] }}</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    @if($data['invoice'])
                                    <a href="{{ asset('storage/' . $data['invoice']) }}" class="btn btn-primary" target="_blank">Lihat Invoice</a>
                                    @else
                                    <p class="mb-1">-</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0">Detail Pengajuan</h5>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-warning btn-sm" onclick="updateBarang()">Edit Barang</button>
                                            <form action="{{ route('catering.pdf') }}" method="post" class="m-0 d-inline">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $data['id'] }}">
                                                <button type="submit" class="btn btn-danger btn-sm">PDF</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Qty</th>
                                                    <th>Nama Barang</th>
                                                    <th>Harga (Rp.)</th>
                                                    <th>Tipe</th>
                                                    <th>Vendor</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $no = 1; $totalSemua = 0; @endphp
                                                @foreach ($data['detail'] as $detail)
                                                @php $subtotal = $detail['jumlah'] * $detail['harga']; $totalSemua += $subtotal; @endphp
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $detail['jumlah'] }}</td>
                                                    <td>{{ $detail['nama_makanan'] }}</td>
                                                    <td>{{ number_format($detail['harga'], 0, ',', '.') }}</td>
                                                    <td>{{ $detail['tipe_detail'] }}</td>
                                                    <td>{{ $detail['vendor'] }}</td>
                                                    <td>{{ $detail['keterangan'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3">Total</th>
                                                    <th>Rp {{ number_format($totalSemua, 0, ',', '.') }}</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Tracking Pengajuan</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $no = 1; @endphp
                                                @foreach ($data['tracking'] as $tracking)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $tracking['tanggal'] }}</td>
                                                    <td>{{ $tracking['tracking'] }}</td>
                                                    <td>{{ $tracking['keterangan'] }}</td>
                                                </tr>
                                                @endforeach
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const vendorCB = @json($vendorCB);
    const vendorMS = @json($vendorMS);

    let deletedIds = [];

    function applyRupiahFormatter() {
        $('.rupiah-input').each(function() {
            if ($(this).data('formatter-applied')) return;
            $(this).data('formatter-applied', true);
            $(this).on('input', function() {
                let val = $(this).val().replace(/[^0-9]/g, '');
                if (val === '') {
                    $(this).val('');
                    return;
                }
                $(this).val(parseInt(val).toLocaleString('id-ID'));
            });
            let current = $(this).val().replace(/[^0-9]/g, '');
            if (current !== '') $(this).val(parseInt(current).toLocaleString('id-ID'));
        });
    }

    function populateVendorOptions($selectElement, vendorList, selectedVendorId) {
        $selectElement.empty();
        $selectElement.append('<option selected disabled> Pilih vendor</option>');
        vendorList.forEach(function(vendor) {
            const isSelected = (vendor.id == selectedVendorId) ? 'selected' : '';
            $selectElement.append('<option value="' + vendor.id + '" ' + isSelected + '>' + vendor.nama + '</option>');
        });
    }

    function updateTipeDetailHidden($item, selectedTipe) {
        $item.find('.tipe-detail-input').val(selectedTipe);
    }

    $('.item-section').each(function() {
        const $item = $(this);
        const savedTipe = $item.find('.tipe-detail-input').val();
        const savedVendorId = $item.find('.current-vendor-id').val(); // Ambil dari hidden field
        const $vendorSelect = $item.find('.vendor-select');
        const $tipeSelect = $item.find('.tipe-detail-select');

        if (savedTipe === 'Coffee Break') {
            populateVendorOptions($vendorSelect, vendorCB, savedVendorId);
        } else if (savedTipe === 'Makan Siang') {
            populateVendorOptions($vendorSelect, vendorMS, savedVendorId);
        }
    });

    applyRupiahFormatter();

    $(document).on('change', '.tipe-detail-select', function() {
        const $this = $(this);
        const $item = $this.closest('.item-section, .new-item-section');
        const selectedTipe = $this.val();
        const $vendorSelect = $item.find('.vendor-select');

        $vendorSelect.empty();
        $vendorSelect.append('<option selected disabled> Pilih vendor</option>');

        if (selectedTipe === 'Coffee Break') {
            populateVendorOptions($vendorSelect, vendorCB, null);
        } else if (selectedTipe === 'Makan Siang') {
            populateVendorOptions($vendorSelect, vendorMS, null);
        } else {
            $vendorSelect.append('<option selected disabled> Pilih tipe dulu</option>');
        }

        updateTipeDetailHidden($item, selectedTipe);
    });

    $(document).on('click', '.delete-item', function() {
        let id = $(this).data('id');
        if (id) {
            deletedIds.push(id);
            $('#deletedIdsInput').val(deletedIds.join(','));
        }
        $(this).closest('.item-section').remove();
    });

    $(document).on('click', '.removeNewRowButton', function() {
        $(this).closest('.new-item-section').remove();
    });

    $('#addItemButton').on('click', function() {
        const html = `
        <div class="new-item-section mb-4 p-3 border rounded bg-light">
            <input type="hidden" name="id_detail_catering[]" value="">
            <input type="hidden" name="tipe_detail[]" class="tipe-detail-input" value="">
            <input type="hidden" name="current_vendor_id[]" class="current-vendor-id" value="">
            <div class="row mb-3">
                <label class="col-md-4 col-form-label">Nama Barang</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="nama_makanan[]" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm removeNewRowButton">Hapus</button>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-md-4 col-form-label">Tipe Catering</label>
                <div class="col-md-6">
                    <select name="tipe_detail_form[]" class="form-control tipe-detail-select" required>
                        <option selected disabled> Pilih Tipe</option>
                        <option value="Coffee Break">Coffee Break</option>
                        <option value="Makan Siang">Makan Siang</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-md-4 col-form-label">Vendor</label>
                <div class="col-md-6">
                    <select name="vendor[]" class="form-control vendor-select" required>
                        <option selected disabled> Pilih tipe dulu</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-md-4 col-form-label">Qty</label>
                <div class="col-md-6">
                    <input type="number" class="form-control" name="qty[]" min="1" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-md-4 col-form-label">Harga (Rp.)</label>
                <div class="col-md-6">
                    <input type="text" class="form-control rupiah-input" name="harga[]" placeholder="0" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-md-4 col-form-label">Keterangan</label>
                <div class="col-md-6">
                    <textarea class="form-control" name="keterangan[]" rows="2"></textarea>
                </div>
            </div>
            <hr>
        </div>`;
        $('#itemsContainer').append(html);
        applyRupiahFormatter();
    });

    $('#updateBarangForm').on('submit', function() {
        $('.rupiah-input').each(function() {
            let clean = $(this).val().replace(/\./g, '');
            $(this).val(clean || '0');
        });
    });

    window.updateBarang = function() {
        $('#updateExpenseHubData').modal('show');
    };
</script>
@endsection