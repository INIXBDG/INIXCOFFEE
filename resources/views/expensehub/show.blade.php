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
                    <form id="updateBarangForm" method="POST" action="{{ route('expensehub.update', $data['id']) }}">
                        @csrf
                        @method('PUT')
                        <div id="itemsContainer">
                            @foreach ($data['detail'] as $index => $detail)
                            <div class="item-section mb-4 p-3 border rounded">
                                <div class="row mb-3">
                                    <label for="nama_barang_{{ $detail['id'] }}" class="col-md-4 col-form-label">Nama Barang</label>
                                    <div class="col-md-6">
                                        <input type="hidden" name="id_detail_pengajuan[]" value="{{ $detail['id'] }}">
                                        <input id="nama_barang_{{ $detail['id'] }}" type="text" class="form-control" name="nama_barang[]" value="{{ old('nama_barang.'.$index, $detail['nama_pengajuan']) }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm delete-item" data-id="{{ $detail['id'] }}">Hapus</button>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="qty_{{ $detail['id'] }}" class="col-md-4 col-form-label">Qty</label>
                                    <div class="col-md-6">
                                        <input id="qty_{{ $detail['id'] }}" type="number" class="form-control" name="qty[]" value="{{ old('qty.'.$index, $detail['jumlah']) }}" min="1" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="harga_{{ $detail['id'] }}" class="col-md-4 col-form-label">Besarnya (Rp.)</label>
                                    <div class="col-md-6">
                                        <input id="harga_{{ $detail['id'] }}" type="number" class="form-control" name="harga[]" value="{{ old('harga.'.$index, $detail['harga_pengajuan']) }}" min="0" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="keterangan_{{ $detail['id'] }}" class="col-md-4 col-form-label">Keterangan</label>
                                    <div class="col-md-6">
                                        <textarea class="form-control" name="keterangan[]" id="keterangan_{{ $detail['id'] }}" rows="3">{{ old('keterangan.'.$index, $detail['keterangan']) }}</textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="addItemButton" class="btn btn-primary">Tambah Item</button>
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
                    <h5 class="card-title">Detail Pengajuan Entertaint, Reimburst, dan Oleh-Oleh</h5>
                    <div class="row">
                        <div class="col-md-5">
                            @if ($data)
                            <div class="row">
                                <div class="col-md-4 mb2">
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
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">ID RKM</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['id_rkm'] }}</p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Materi</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['materi'] }}</p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Perusahaan</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['perusahaan'] }}</p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Tanggal Mulai</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <p class="mb-1">{{ $data['tanggal_mulai'] }} s/d {{ $data['tanggal_selesai'] }}</p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <p class="mb-1">Tanggal Selesai</p>
                                </div>
                                <div class="col-md-8 mb-2">
                                    @if($data['invoice'])
                                    <a href="{{ asset('storage/' . $data['invoice']) }}" class="btn btn-primary" target="_blank">Lihat Invoice</a>
                                    @else
                                    <p class="mb-1">-</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0">Detail Pengajuan</h5>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-warning btn-sm" onclick="updateBarang()">Edit Barang</button>
                                            <form action="{{ route('expensehub.pdf') }}" method="post" class="m-0 d-inline">
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
                                                    <th scope="col">No</th>
                                                    <th scope="col">Qty</th>
                                                    <th scope="col">Nama Barang</th>
                                                    <th scope="col">Besarnya (Rp.)</th>
                                                    <th scope="col">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $no = 1;
                                                $totalSemua = 0;
                                                @endphp
                                                @foreach ($data['detail'] as $detail)
                                                @php
                                                $subtotal = $detail['jumlah'] * $detail['harga_pengajuan'];
                                                $totalSemua += $subtotal;
                                                @endphp
                                                <tr>
                                                    <td data-label="No">{{ $no++ }}</td>
                                                    <td data-label="Qty">{{ $detail['jumlah'] }}</td>
                                                    <td data-label="Nama Barang">{{ $detail['nama_pengajuan'] }}</td>
                                                    <td data-label="Besarnya (Rp.)">{{ number_format($detail['harga_pengajuan'], 0, ',', '.') }}</td>
                                                    <td data-label="Keterangan">{{ $detail['keterangan'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th scope="col" colspan="3">Total</th>
                                                    <th scope="col">Rp {{ number_format($totalSemua, 0, ',', '.') }}</th>
                                                    <th scope="col"></th>
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
                                                @php
                                                $no = 1;
                                                @endphp
                                                @foreach ($data['tracking'] as $tracking)
                                                <tr>
                                                    <td data-label="No">{{ $no++ }}</td>
                                                    <td data-label="Tanggal">{{ $tracking['tanggal'] }}</td>
                                                    <td data-label="Status">{{ $tracking['tracking'] }}</td>
                                                    <td data-label="keterangan">{{ $tracking['keterangan'] }}</td>
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
    $(document).ready(function() {
        let nextItemId = 9999;
        let deletedIds = [];

        function updateDeletedIdsInput() {
            $('#deletedIdsInput').val(deletedIds.join(','));
        }

        $(document).on('click', '.delete-item', function() {
            const $itemSection = $(this).closest('.item-section');
            const itemId = $(this).data('id');

            if (itemId) {
                deletedIds.push(itemId);
                updateDeletedIdsInput();
            }
            $itemSection.remove();
        });

        $(document).on('click', '.removeNewRowButton', function() {
            $(this).closest('.new-item-section').remove();
        });

        $('#addItemButton').on('click', function() {
            const newItemHtml = `
                <div class="new-item-section mb-4 p-3 border rounded">
                    <div class="row mb-3">
                        <label for="new_nama_barang_${nextItemId}" class="col-md-4 col-form-label">Nama Barang</label>
                        <div class="col-md-6">
                            <input type="hidden" name="id_detail_pengajuan[]" value="">
                            <input id="new_nama_barang_${nextItemId}" type="text" class="form-control" name="nama_barang[]" placeholder="Masukan Nama Barang" value="">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm removeNewRowButton">Hapus</button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="new_qty_${nextItemId}" class="col-md-4 col-form-label">Qty</label>
                        <div class="col-md-6">
                            <input id="new_qty_${nextItemId}" type="number" class="form-control" name="qty[]" placeholder="Masukan Qty" value="" min="1">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="new_harga_${nextItemId}" class="col-md-4 col-form-label">Besarnya (Rp.)</label>
                        <div class="col-md-6">
                            <input id="new_harga_${nextItemId}" type="number" class="form-control" name="harga[]" placeholder="Masukan Harga" value="" min="0">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="new_keterangan_${nextItemId}" class="col-md-4 col-form-label">Keterangan</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="keterangan[]" id="new_keterangan_${nextItemId}" placeholder="Keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <hr>
                </div>
            `;
            $('#itemsContainer').append(newItemHtml);
            nextItemId++;
        });

        window.updateBarang = function() {
            $('#updateExpenseHubData').modal('show');
        };
    });
</script>
@endsection