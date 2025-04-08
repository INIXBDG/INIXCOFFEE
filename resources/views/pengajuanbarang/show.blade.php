
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="updateBarangModal" tabindex="-1" aria-labelledby="updateBarangModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateBarangModalLabel">Update Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateBarangForm" method="POST" action="{{ route('pengajuanbarang.updateBarang', $data->id) }}">
                        @csrf
                        @method('PUT')
                        @foreach ($detail as $item)
                        <div id="item-{{ $item->id }}">
                            <div class="row mb-3 item" >
                                <label for="nama_barang" class="col-md-4 col-form-label text-md-start">{{ __('Nama Barang') }}</label>
                                <div class="col-md-6">
                                    <input type="hidden" name="id_detail_pengajuan[]" value="{{ $item->id }}">
                                    <input type="hidden" name="id_pengajuan_barang[]" value="{{ $item->id_pengajuan_barang }}">
                                    {{-- <input type="hidden" name="deletedatabarang[]" value=""> --}}
                                    <input id="nama_barang" type="text" placeholder="Masukan Nama Barang" class="form-control" name="nama_barang[]" value="{{ $item->nama_barang }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm delete-item" data-id="{{ $item->id }}">Hapus</button>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="qty" class="col-md-4 col-form-label text-md-start">{{ __('Qty') }}</label>
                                <div class="col-md-6">
                                    <input id="qty" type="text" placeholder="Masukan Qty" class="form-control" name="qty[]" value="{{ $item->qty }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="harga" class="col-md-4 col-form-label text-md-start">{{ __('Besarnya (Rp.)') }}</label>
                                <div class="col-md-6">
                                    <input id="harga" type="text" placeholder="Masukan Harga" class="form-control" name="harga[]" value="{{ $item->harga }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" name="keterangan[]" id="keterangan" cols="30" rows="5">{{ $item->keterangan }}</textarea>
                                </div>
                            </div>
                            <hr>
                        </div>
                        
                        @endforeach
                
                        <!-- Container for new items -->
                        <div id="newItemsContainer"></div>
                
                        <!-- Button to add new items -->
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
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    {{-- <a href="#" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a> --}}
                    <h5 class="card-title">Detail Pengajuan Barang</h5>
                    <div class="row">
                        <div class="col-md-5">
                            {{-- {{$data}} --}}
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Nama Karyawan</p><p id="titikdua"> :</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $data->karyawan->nama_lengkap }}</p>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Divisi</p><p id="titikdua"> :</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $data->karyawan->divisi }}</p>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Jabatan</p><p id="titikdua"> :</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $data->karyawan->jabatan }}</p>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Tipe Pengajuan</p><p id="titikdua"> :</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $data->tipe }}</p>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Status Pengajuan</p><p id="titikdua"> :</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $data->tracking->tracking }}</p>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Invoice/Bukti Pembayaran</p><p id="titikdua"> :</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    @if (isset($data->invoice))
                                        <a href="{{ asset('storage/' . $data->invoice) }}" class="btn click-primary" target="_blank">Lihat Invoice</a>
                                    @else
                                        <p>-</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="col-md-12" style="display: flex;justify-content: space-between;">
                                        <h5 class="mx-2 card-title">Detail Barang</h5>
                                        @php
                                            $jabatan = auth()->user()->jabatan;
                                            $id_karyawan = auth()->user()->karyawan_id;
                                        @endphp
                                        <div>
                                            
                                            @if ($jabatan == 'Finance & Accounting' || $data->id_karyawan == $id_karyawan)
                                            @if ($data->tracking->tracking == 'Selesai')
                                                <button class="mx-2 btn btn-md btn-primary disabled" onclick="updateBarang()">Edit Barang</button>
                                            @else
                                                <button class="mx-2 btn btn-md btn-primary" onclick="updateBarang()">Edit Barang</button>
                                            @endif
                                            @endif
                                            {{-- {{$data}} --}}
                                            <a href="{{ route('pengajuanbarang.pdf', $data->id)}}" class="mx-2 btn btn-md btn-danger">PDF</a>
                                        </div>
                                        
                                    </div>
                                    <table class="table table-striped">  
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
                                                $totalHarga = 0; // Inisialisasi total harga  
                                            @endphp  
                                            @foreach($detail as $item)  
                                            <tr>  
                                                <td>{{ $loop->iteration }}</td>  
                                                <td>{{ $item->qty }}</td>  
                                                <td>{{ $item->nama_barang }}</td>  
                                                <td>{{ formatRupiah($item->harga) }}</td>  
                                                <td>{{ $item->keterangan }}</td>  
                                            </tr>  
                                            @php  
                                                $totalbarang = $item->harga * $item->qty;
                                                $totalHarga += $totalbarang; // Tambahkan harga ke total  
                                            @endphp  
                                            @endforeach  
                                        </tbody>  
                                        <tfoot>  
                                            <tr>  
                                                <th scope="col">Total</th>  
                                                <th scope="col" colspan="2"></th>  
                                                <th scope="col">{{ formatRupiah($totalHarga) }}</th> <!-- Tampilkan total harga -->  
                                                <th scope="col"></th>  
                                            </tr>  
                                        </tfoot>  
                                    </table>                                      
                                </div>
                            </div>
                            {{-- {{$detail}} --}}
                            <hr>
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Tracking Pengajuan Barang</h5>
                                    <table class="table table-striped">
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
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y H:i:s') }}</td>
                                                    <td>{{ $item->tracking }}</td>
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
<style>
    @media screen and (min-width: 769px) {
                /* CSS untuk layar web */
                    #titikdua {
                        display: none; /* Menyembunyikan titikdua pada layar web */
                    }
                }
                @media screen and (max-width: 768px) {
                    #titikdua {
                        display: flex; /* Menampilkan titikdua */
                    }
                    .card {
                        padding: 15px;
                        max-width: 100%;
                    }

                    .card-body .row {
                        margin-bottom: 10px;
                    }

                    .col-xs-4, .col-sm-4{
                        margin :0 !important;
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
    @media screen and (max-width: 768px) {
        .card {
            padding: 15px;
            max-width: 100%;
        }

        .card-body  .row {
            margin-bottom: 10px;
        }

        /* .col-xs-4, */
        .col-xs-1 {
            display: none;
        }

        .col-xs-7 {
            width: 100%;
            text-align: left;
        }
    }

        .cardname {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .click-secondary-icon {
            background:    #355C7C;
            border-radius: 1000px;
            width:         45px;
            height:        45px;
            color:         #ffffff;
            display:       flex;
            justify-content: center;
            align-items:   center;
            text-align:    center;
            text-decoration: none;
        }
        .click-secondary-icon i {
            line-height: 45px;
        }

        .click-secondary {
            background:    #355C7C;
            border-radius: 1000px;
            padding:       10px 25px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }

        .click-secondary:hover {
            color:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .click-warning {
            background:    #f8be00;
            border-radius: 1000px;
            padding:       10px 20px;
            color:         #000000;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear; /
        }

        .click-warning:hover {
            background:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
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
            .checkmark {
        display: block;
        width: 25px;
        height: 25px;
        border: 1px solid #ccc;
        border-radius: 50%;
        position: relative;
        margin: 0 auto;
    }

    .checkmark:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22bb33;
        display: none;
    }

    tr.selected .checkmark:after {
        display: block;
    }

</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Handle delete item button click
        $('.delete-item').on('click', function() {
            var itemId = $(this).data('id'); // Get the ID of the item to delete
            $('#item-' + itemId).remove(); // Remove the item from the DOM

            // Add the ID to the deletedatabarang array
            var deletedItems = $('input[name="deletedatabarang[]"]').map(function() {
                return $(this).val();
            }).get();

            // Check if the itemId is already in the deletedItems array
            if (!deletedItems.includes(itemId.toString())) {
                // Add the deleted item ID to the array without a comma
                deletedItems.push(itemId.toString());
            }

            // Update the hidden input field with the new deleted items
            $('input[name="deletedatabarang[]"]').remove(); // Clear existing inputs
            deletedItems.forEach(function(id) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'deletedatabarang[]',
                    value: id
                }).appendTo('#updateBarangForm'); // Append new hidden inputs
            });
            $(this).closest('.item').remove();
        });

        let itemCount = 0; // Counter for unique IDs
        var id_pengajuan_barang = "{{ $item->id_pengajuan_barang }}"
        $('#addItemButton').on('click', function() {
            // Create a new input row using template literals
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

            // Append the new item to the container
            $('#newItemsContainer').append(newItem);
            itemCount++; // Increment the counter for the next item
        });
        $(document).on('click', '.removeItemButton', function() {
            $(this).closest('.item').remove(); // Remove the closest item div
        });
    });
    function updateBarang(){
        $('#updateBarangModal').modal('show')
    }
</script>



@endsection
