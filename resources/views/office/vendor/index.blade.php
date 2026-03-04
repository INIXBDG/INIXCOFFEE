@extends('layouts_office.app')

@section('office_contents')
    @php use Illuminate\Support\Str; @endphp

    <div class="container-fluid py-4">

        <!-- Modal Detail -->
        <div class="modal fade" id="detailModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Detail Vendor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="text-center mb-3">
                            <img id="detailFoto" class="img-fluid rounded shadow-sm"
                                style="max-height:250px; object-fit:cover;">
                        </div>

                        <h4 id="detailNama" class="fw-bold text-center mb-3"></h4>

                        <div class="border rounded p-3"
                            style="white-space: pre-line; overflow-wrap: break-word; max-height:250px; overflow-y:auto;">
                            <p id="detailKeterangan"></p>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal Tambah -->
        <div class="modal fade" id="exampleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Tambah Data Vendor</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form action="{{ url('/office/vendor/' . $itemValue) }}" method="post"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" name="nama" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3"></textarea>
                            </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary">Save</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal Ajukan -->
        <div class="modal fade" id="exampleModalAjukan" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Ajukan Data Vendor</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body" id="itemContainer">
                        <form action="{{ route('pengajuanbarang.store') }}" method="post"
                            enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">

                            <div class="mb-3">
                                <label class="form-label">Tipe</label>      
                                <select name="tipe" id="tipe" class="form-select form-select-lg">
                                    <option value="Makan Siang" {{ $itemValue == 'makansiang' ? 'selected' : '' }}>Makan Siang</option>
                                    <option value="Coffee Break" {{ $itemValue == 'coffeebreak' ? 'selected' : '' }}>Coffee Break</option>
                                    <option value="Bengkel" {{ $itemValue == 'bengkel' ? 'selected' : '' }}>Bengkel</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="barang[nama_barang][]" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jumlah</label>
                                <input type="text" name="barang[qty][]" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Harga Barang (dalam Rp.)</label>
                                <input type="number" name="barang[harga_barang][]" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="barang[keterangan][]" class="form-control" rows="3"></textarea>
                            </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary">Save</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="fw-bold text-dark">Data Vendor {{ $itemValue }}</h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Card -->
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between">
                    <div class="d-flex gap-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            Tambah Vendor {{ $itemValue }}
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModalAjukan">
                            Ajukan {{ $itemValue }}
                        </button>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">{{ $data->total() }} Data</span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Vendor</th>
                                <th>Foto</th>
                                <th>Keterangan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-4">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($data as $index => $item)
                                <tr class="vendor-row" data-nama="{{ $item->nama }}"
                                    data-foto="{{ $item->foto ? asset('storage/' . $item->foto) : '' }}"
                                    data-keterangan="{{ $item->keterangan }}">

                                    <td>{{ $index + 1 }}</td>

                                    <td>
                                        <span class="truncate-text">{{ Str::limit($item->nama, 20) }}</span>
                                    </td>

                                    <td>
                                        @if ($item->foto)
                                            <img src="{{ asset('storage/' . $item->foto) }}"
                                                style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="truncate-text">{{ Str::limit($item->keterangan, 25, '...') }}</span>
                                    </td>

                                    <td class="text-center">
                                        @if ($item->is_active == '0')
                                            <span class="badge bg-warning-subtle text-warning">Tidak Aktif</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">Aktif</span>
                                        @endif
                                    </td>

                                    <td class="text-center pe-4">
                                        <form action="{{ url('/office/vendor/' . $itemValue . '/' . $item->id) }}"
                                            method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>

                                </tr>
                            @empty

                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bx bx-info-circle text-muted" style="font-size:4rem;"></i>
                                        <p class="text-muted mt-3">Tidak ada data Vendor</p>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

            @if ($data->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between">
                        <small>Menampilkan {{ $data->firstItem() }} - {{ $data->lastItem() }} dari {{ $data->total() }}
                            data</small>
                        {{ $data->links() }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- TRUNCATE CSS -->
    <style>
        .truncate-text {
            max-width: 160px;
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        tr.vendor-row {
            cursor: pointer;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.querySelectorAll('.vendor-row').forEach(row => {
            row.addEventListener('click', function(e) {

                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'FORM') return;

                let nama = this.dataset.nama;
                let foto = this.dataset.foto;
                let keterangan = this.dataset.keterangan;

                document.getElementById('detailNama').innerText = nama;

                document.getElementById('detailFoto').src = foto ? foto :
                    'https://via.placeholder.com/300x200?text=No+Image';

                document.getElementById('detailKeterangan').innerText = keterangan;

                var modal = new bootstrap.Modal(document.getElementById('detailModal'));
                modal.show();
            });
        });

        $(document).ready(function () {
            setupInputFormatter('#itemContainer input[name="barang[harga_barang][]"]'); 
            $('form').on('submit', function (e) {  
                e.preventDefault();  
                $('#itemContainer input[name="barang[harga_barang][]"]').each(function() {  
                    $(this).val($(this).val().replace(/\./g, ''));  
                });   
                this.submit();
            });
            function formatRupiah(angka, prefix) {
                var number_string = angka.replace(/[^,\d]/g, '').toString(),
                        split = number_string.split(','),
                        sisa = split[0].length % 3,
                        rupiah = split[0].substr(0, sisa),
                        ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    
                    if (ribuan) {
                        separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }
    
                    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                    return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
            }
    
            function setupInputFormatter(selector) {
                    var $input = $(selector);
                    $input.on('input', function() {
                        $input.val(formatRupiah(this.value));
                    });
                }
        });
    </script>
@endsection
