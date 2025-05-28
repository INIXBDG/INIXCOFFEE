@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Update Barang Modal (Unchanged) -->
        <div class="modal fade" id="updateBarangModal" tabindex="-1" aria-labelledby="updateBarangModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateBarangModalLabel">Update Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="updateBarangForm" method="POST"
                            action="{{ route('pengajuanbarang.updateBarang', $data->id) }}">
                            @csrf
                            @method('PUT')
                            @foreach ($detail as $item)
                                <div id="item-{{ $item->id }}">
                                    <div class="row mb-3 item">
                                        <label for="nama_barang"
                                            class="col-md-4 col-form-label text-md-start">{{ __('Nama Barang') }}</label>
                                        <div class="col-md-6">
                                            <input type="hidden" name="id_detail_pengajuan[]" value="{{ $item->id }}">
                                            <input type="hidden" name="id_pengajuan_barang[]"
                                                value="{{ $item->id_pengajuan_barang }}">
                                            <input id="nama_barang" type="text" placeholder="Masukan Nama Barang"
                                                class="form-control" name="nama_barang[]" value="{{ $item->nama_barang }}">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm delete-item"
                                                data-id="{{ $item->id }}">Hapus</button>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="qty"
                                            class="col-md-4 col-form-label text-md-start">{{ __('Qty') }}</label>
                                        <div class="col-md-6">
                                            <input id="qty" type="text" placeholder="Masukan Qty"
                                                class="form-control" name="qty[]" value="{{ $item->qty }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="harga"
                                            class="col-md-4 col-form-label text-md-start">{{ __('Besarnya (Rp.)') }}</label>
                                        <div class="col-md-6">
                                            <input id="harga" type="text" placeholder="Masukan Harga"
                                                class="form-control" name="harga[]" value="{{ $item->harga }}">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="keterangan"
                                            class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                                        <div class="col-md-6">
                                            <textarea class="form-control" name="keterangan[]" id="keterangan" cols="30" rows="5">{{ $item->keterangan }}</textarea>
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            @endforeach
                            <div id="newItemsContainer"></div>
                            <button type="button" id="addItemButton" class="btn btn-primary">Add Item</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Detail Pengajuan Barang -->
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <a href="javascript:void(0);" onclick="window.history.back();" class="btn click-primary my-2">
                            <img src="/icon/arrow-left.svg" class="img-responsive" width="20px"> Back
                        </a>
                        <h5 class="card-title">Detail Pengajuan Barang</h5>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Nama Karyawan</p>
                                        <p id="titikdua"> :</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $data->karyawan->nama_lengkap }}</p>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Divisi</p>
                                        <p id="titikdua"> :</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $data->karyawan->divisi }}</p>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Jabatan</p>
                                        <p id="titikdua"> :</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $data->karyawan->jabatan }}</p>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Tipe Pengajuan</p>
                                        <p id="titikdua"> :</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $data->tipe }}</p>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Status Pengajuan</p>
                                        <p id="titikdua"> :</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <p>{{ $data->tracking->tracking }}</p>
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                        <p>Invoice/Bukti Pembayaran</p>
                                        <p id="titikdua"> :</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        @if (isset($data->invoice))
                                            <a href="{{ asset('storage/' . $data->invoice) }}" class="btn click-primary"
                                                target="_blank">Lihat Invoice</a>
                                        @else
                                            <p>-</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="col-md-12" style="display: flex; justify-content: space-between;">
                                            <h5 class="mx-2 card-title">Detail Barang</h5>
                                            @php
                                                $jabatan = auth()->user()->jabatan;
                                                $id_karyawan = auth()->user()->karyawan_id;
                                            @endphp
                                            <div>
                                                @if ($jabatan == 'Finance & Accounting' || $data->id_karyawan == $id_karyawan)
                                                    @if ($data->tracking->tracking == 'Selesai')
                                                        <button class="mx-2 btn btn-md btn-primary disabled"
                                                            onclick="updateBarang()">Edit Barang</button>
                                                    @else
                                                        <button class="mx-2 btn btn-md btn-primary"
                                                            onclick="updateBarang()">Edit Barang</button>
                                                    @endif
                                                @endif
                                                <a href="{{ route('pengajuanbarang.pdf', $data->id) }}"
                                                    class="mx-2 btn btn-md btn-danger">PDF</a>
                                            </div>
                                        </div>
                                        <!-- Modified Detail Barang Table -->
                                        <div class="table-responsive">
                                            <table class="table table-striped detail-barang-table">
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
                                                        $totalHarga = 0;
                                                    @endphp
                                                    @foreach ($detail as $item)
                                                        <tr>
                                                            <td data-label="No">{{ $loop->iteration }}</td>
                                                            <td data-label="Qty">{{ $item->qty }}</td>
                                                            <td data-label="Nama Barang">{{ $item->nama_barang }}</td>
                                                            <td data-label="Besarnya (Rp.)">
                                                                {{ formatRupiah($item->harga) }}</td>
                                                            <td data-label="Keterangan">{{ $item->keterangan }}</td>
                                                        </tr>
                                                        @php
                                                            $totalbarang = $item->harga * $item->qty;
                                                            $totalHarga += $totalbarang;
                                                        @endphp
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th scope="col">Total</th>
                                                        <th scope="col" colspan="2"></th>
                                                        <th scope="col">{{ formatRupiah($totalHarga) }}</th>
                                                        <th scope="col"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <!-- Modified Tracking Pengajuan Barang Table -->
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Tracking Pengajuan Barang</h5>
                                        <div class="table-responsive">
                                            <table class="table table-striped tracking-table">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($tracking as $item)
                                                        <tr>
                                                            <td data-label="No">{{ $loop->iteration }}</td>
                                                            <td data-label="Tanggal">
                                                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y H:i:s') }}
                                                            </td>
                                                            <td data-label="Status">{{ $item->tracking }}</td>
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

    <style>
        /* Existing styles (unchanged) */
        @media screen and (min-width: 769px) {
            #titikdua {
                display: none;
            }
        }

        @media screen and (max-width: 768px) {
            #titikdua {
                display: flex;
            }

            .card {
                padding: 15px;
                max-width: 100%;
            }

            .card-body .row {
                margin-bottom: 10px;
            }

            .col-xs-4,
            .col-sm-4 {
                margin: 0 !important;
                display: flex;
            }

            .col-xs-1 {
                display: none;
            }

            .col-xs-7 {
                width: 100%;
                text-align: left;
            }
        }

        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: auto;
            height: auto;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.45);
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(2px);
        }

        .click-primary {
            background: #007bff;
            border-radius: 5px;
            padding: 10px 20px;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            font: normal bold 16px/1 "Open Sans", sans-serif;
            text-align: center;
            transition: background-color 0.2s linear;
        }

        .click-primary:hover {
            background: #0056b3;
        }

        /* New styles for responsive tables */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .detail-barang-table,
        .tracking-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .detail-barang-table th,
        .detail-barang-table td,
        .tracking-table th,
        .tracking-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-barang-table th,
        .tracking-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        /* Mobile responsive table styling */
        @media screen and (max-width: 768px) {

            .detail-barang-table,
            .tracking-table {
                display: block;
            }

            .detail-barang-table thead,
            .tracking-table thead {
                display: none;
            }

            .detail-barang-table tbody,
            .detail-barang-table tr,
            .tracking-table tbody,
            .tracking-table tr {
                display: block;
            }

            .detail-barang-table td,
            .tracking-table td {
                display: block;
                text-align: left;
                position: relative;
                padding-left: 50%;
                border: none;
                border-bottom: 1px solid #dee2e6;
            }

            .detail-barang-table td:before,
            .tracking-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 12px;
                width: 45%;
                font-weight: bold;
            }

            .detail-barang-table tfoot,
            .detail-barang-table tfoot tr,
            .detail-barang-table tfoot th,
            .detail-barang-table tfoot td {
                display: block;
            }

            .detail-barang-table tfoot th {
                font-weight: bold;
                text-align: left;
                padding: 12px;
            }

            .detail-barang-table tfoot td {
                padding-left: 12px;
            }
        }

        /* Ensure no conflicts with other card styles */
        .detail-barang-table.card,
        .tracking-table.card {
            border: none;
            box-shadow: none;
            background: transparent;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.delete-item').on('click', function() {
                var itemId = $(this).data('id');
                $('#item-' + itemId).remove();

                var deletedItems = $('input[name="deletedatabarang[]"]').map(function() {
                    return $(this).val();
                }).get();

                if (!deletedItems.includes(itemId.toString())) {
                    deletedItems.push(itemId.toString());
                }

                $('input[name="deletedatabarang[]"]').remove();
                deletedItems.forEach(function(id) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'deletedatabarang[]',
                        value: id
                    }).appendTo('#updateBarangForm');
                });
                $(this).closest('.item').remove();
            });

            let itemCount = 0;
            var id_pengajuan_barang = "{{ $item->id_pengajuan_barang }}";
            $('#addItemButton').on('click', function() {
                let newItem = `
                <div class="row mb-3">
                    <label for="nama_barang_${itemCount}" class="col-md-4 col-form-label text-md-start">{{ __('Nama Barang') }}</label>
                    <div class="col-md-6">
                        <input type="hidden" name="id_detail_pengajuan[]" value="">
                        <input type="hidden" name="id_pengajuan_barang[]" value="${id_pengajuan_barang}">
                        <input id="nama_barang_${itemCount}" type="text" placeholder="Masukan Nama Barang" class="form-control" name="nama_barang[]" value="">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="qty_${itemCount}" class="col-md-4 col-form-label text-md-start">{{ __('Qty') }}</label>
                    <div class="col-md-6">
                        <input id="qty_${itemCount}" type="text" placeholder="Masukan Qty" class="form-control" name="qty[]" value="">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="harga_${itemCount}" class="col-md-4 col-form-label text-md-start">{{ __('Besarnya (Rp.)') }}</label>
                    <div class="col-md-6">
                        <input id="harga_${itemCount}" type="text" placeholder="Masukan Harga" class="form-control" name="harga[]" value="">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="keterangan_${itemCount}" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                    <div class="col-md-6">
                        <textarea class="form-control" name="keterangan[]" id="keterangan_${itemCount}" cols="30" rows="5"></textarea>
                    </div>
                </div>
                <hr>
            `;
                $('#newItemsContainer').append(newItem);
                itemCount++;
            });

            $(document).on('click', '.removeItemButton', function() {
                $(this).closest('.item').remove();
            });
        });

        function updateBarang() {
            $('#updateBarangModal').modal('show');
        }
    </script>
@endsection
