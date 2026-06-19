@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="cube">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
            </div>
        </div>
        
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                <i class="bx bx-task text-primary me-2" style="font-size: 1.5rem;"></i>
                Tagihan Perusahaan 
            </h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditTagihan" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Tagihan Perusahaan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <small class="text-muted">* Status selesai dan terlambat otomatis terupdate dari sistem</small>
                        <form method="post" class="mt-5" id="formEditTagihan" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3 row">
                                {{-- Status --}}
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Status
                                    </label>

                                    <select name="status" id="status" class="form-select">
                                        <option value="pending">
                                            Pending
                                        </option>
                                        <option value="proses">
                                            Proses
                                        </option>
                                        <option value="selesai" disabled hidden>
                                            Selesai
                                        </option>
                                        <option value="telat" disabled hidden>
                                            Terlambat
                                        </option>
                                    </select>
                                </div>

                                <!-- Tanggal Perkiraan -->
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Tracking
                                    </label>

                                    <select name="tracking" id="tracking" class="form-select">
                                        <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">
                                            Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                        <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang
                                            Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                        <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve
                                            Direksi
                                        </option>
                                        <option value="Diajukan dan Sedang Ditinjau oleh Finance">Diajukan dan Sedang
                                            Ditinjau oleh Finance</option>
                                        <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke
                                            Direktur
                                            Utama</option>
                                        <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam
                                            proses Pencairan</option>
                                        <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                </div>

                                <!-- Tanggal Selesai -->
                                <div class="col-md-4">
                                    <label class="form-label text-muted small text-uppercase">
                                        Tanggal Realisasi
                                    </label>
                                    <div>
                                        <input type="date" name="tanggal_selesai" class="form-control col-md-6">
                                    </div>
                                </div>

                                {{-- Keterangan --}}
                                <div class="mb-3">
                                    <label for="keterangan" class="col-md-5 col-form-label">Keterangan
                                        (Optional)</label>
                                    <textarea class="form-control" name="keterangan"></textarea>
                                </div>

                            </div>

                            <div class="modal-footer mt-4">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-body p-4 mb-4 h-100 " style="height: 320px;">
                        {{-- Table Tagihan --}}
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="border-0 ps-4"></th>
                                        <th class="border-0" style="min-width: 160px;">Due Date</th>
                                        <th class="border-0" style="min-width: 150px;">Kegiatan</th>
                                        <th class="border-0" style="min-width: 100px;">Tipe</th>
                                        <th class="border-0" style="min-width: 200px;">Nominal</th>
                                        <th class="border-0" style="min-width: 120px;">Tracking</th>
                                        <th class="border-0" style="min-width: 120px;">Tanggal Selesai</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Status</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trackingTagihanPerusahaans as $tagihan)
                                        <tr class="border-bottom ">
                                            @if ($tagihan->status === 'selesai')
                                                <td class="text-center ps-4"><input class="custom-check" type="checkbox" checked disabled></td>
                                            @elseif ($tagihan->status === 'telat')
                                                <td class="text-center ps-4"><input class="custom-fail" type="checkbox" checked disabled></td>
                                            @else
                                                <td class="text-center ps-4"><input class="check-blue" data-id="{{ $tagihan->id }}" type="checkbox" id="edit-tagihan"></td>
                                            @endif
                                            <td>
                                                @if ($tagihan->tanggal_perkiraan_mulai === $tagihan->tanggal_perkiraan_selesai || $tagihan->tanggal_perkiraan_selesai === null )
                                                    <div class="small">
                                                        {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_mulai)->format('d F') }}
                                                    </div>
                                                @else
                                                    <div class="small">
                                                        {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($tagihan->tanggal_perkiraan_selesai)->format('d M') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="text-truncate" style="max-width: 150px;">
                                                        {{ $tagihan->tagihanPerusahaan?->kegiatan ?? $tagihan->kegiatan }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 150px;">
                                                    {{ $tagihan->tagihanPerusahaan?->tipe ?? $tagihan->kegiatan }} 
                                                </div>
                                            </td>
                                            <td>
                                                <span class="">
                                                    {{ $tagihan->nominal ? 'Rp. ' . number_format($tagihan->nominal, 0, ',', '.') : '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;">
                                                    {{ $tagihan->tracking ?? '-' }} 
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    {{ $tagihan->tanggal_selesai ? \Carbon\Carbon::parse($tagihan->tanggal_selesai)->format('d F') : '-' }} 
                                                </div>
                                            </td>
                                            <td class="text-center pe-4">
                                                @php
                                                    $statusConfig = [
                                                        'pending' => [
                                                            'color' => 'warning',
                                                            'icon' => 'bx-time-five',
                                                        ],
                                                        'proses' => [
                                                            'color' => 'primary',
                                                            'icon' => 'bx-loader-circle',
                                                        ],
                                                        'selesai' => [
                                                            'color' => 'success',
                                                            'icon' => 'bx-check-circle',
                                                        ],
                                                        'telat' => [
                                                            'color' => 'danger',
                                                            'icon' => 'bx-info-circle',
                                                        ],
                                                    ];
                                                    $config = $statusConfig[$tagihan->status] ?? [
                                                        'color' => 'secondary',
                                                        'icon' => 'bx-info-circle',
                                                    ];
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2 text-capitalize">
                                                    <i class="bx {{ $config['icon'] }} me-1"></i>
                                                    {{ $tagihan->status }}
                                                </span>
                                            </td>
                                            <td class="text-center pe-4 position-relative">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            type="button"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-boundary="viewport"
                                                            aria-expanded="false">
                                                        Aksi
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">

                                                        <li>
                                                            <button class="dropdown-item text-success btn-ajukan-tagihan" data-id="{{ $tagihan->id }}">
                                                                Ajukan Tagihan
                                                            </button>

                                                            <form id="form-ajukan-{{ $tagihan->id }}" method="POST" style="display:none;">
                                                                @csrf
                                                                @php
                                                                    $user = auth()->user();
                                                                    $karyawan = $user->karyawan;
                                                                @endphp
                                                                <input type="hidden" name="id_tagihan" value="{{ $tagihan->id }}">
                                                                <input name="id_karyawan" value="{{ $karyawan->id }}">
                                                                <input id="nama_karyawan" type="text" name="nama_karyawan" value="{{ $karyawan->nama_lengkap }}">
                                                                <input id="divisi" type="text" name="divisi" value="{{ $karyawan->divisi }}">
                                                                <input type="text" name="tipe" value="Tagihan Perusahaan">  
                                                                <input type="text" name="barang[nama_barang][]" value="{{ $tagihan->kegiatan ?? $tagihan->tagihanPerusahaan?->kegiatan }}">
                                                                <input type="number" name="barang[qty][]" value="1"> 
                                                                <input type="text" name="barang[harga_barang][]" value="{{ $tagihan->nominal ?? null }}">  
                                                                <input type="text" name="barang[keterangan][]" value="{{ $tagihan->keterangan ?? null }}">
                                                            </form>
                                                        </li>
                                                        
                                                        <li>
                                                            <a class="dropdown-item"
                                                            href="{{ route('detailTagihanPerusahaan', $tagihan->id) }}">
                                                                Detail
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <form action="{{ route('hapusTagihanPerusahaan', $tagihan->id) }}" method="POST"
                                                                onsubmit="return confirm('Yakin ingin menghapus?')">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bx bx-message-square-x text-muted"
                                                        style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-3 mb-0">Tidak ada tagihan untuk
                                                        ditampilkan
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mb-0 d-flex justify-content-center">
                                {{ $trackingTagihanPerusahaans->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    .custom-check {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #71DD37;
        border-radius: 5px;
        position: relative;
    }
    .check-blue {
        width: 20px;
        height: 20px;
        border: 2px solid #5B73E8;
        border-radius: 5px;
        position: relative;
    }

    .custom-check:checked {
        background-color: #71DD37;
    }

    .custom-check:checked::after {
        content: '✓';
        color: white;
        font-weight: bold;
        position: absolute;
        left: 2px;
        top: -2px;
    }
    .custom-fail:checked::after {
        content: '✖';
        color: white;
        font-weight: bold;
        position: absolute;
        left: 2px;
        top: -2px;
    }
    .custom-fail:checked {
        background-color: #FF3E1D;
    }
    .custom-fail {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #FF3E1D;
        border-radius: 5px;
        position: relative;
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // pengajuan tagihan perusahaan ke pengajuan barang
        $(document).on('click', '.btn-ajukan-tagihan', function () {
            let id = $(this).data('id');
            let form = $('#form-ajukan-' + id);
            $('#loadingModal').modal('show');

            $.ajax({
                url: "{{ route('pengajuanbarang.store') }}",
                type: "POST",
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#loadingModal').modal('hide');
                    alert("Berhasil membuat pengajuan barang!");
                    location.reload();
                },
                error: function (xhr) {
                    $('#loadingModal').modal('hide');
                    alert('Terjadi kesalahan');
                    console.log(xhr.responseText);
                }
            });
        });
        // end pengajuan tagihan perusahaan ke pengajuan barang

        // form edit tagihan
        $(document).on('click', '#edit-tagihan', function() {
            let id = $(this).data('id');

            // jika yang diklik checkbox → buka modal manual
            if ($(this).is(':checkbox')) {
                this.checked = false;
                $('#modalEditTagihan').modal('show');
            }

            $.ajax({
                url: '/office/data-tagihan/' + id,
                type: 'GET',
                success: function(res) {
                    let nominal = formatRupiah(parseInt(res.data.nominal))
                    // set action form
                    $('#formEditTagihan').attr('action', '/office/update-tagihan/' + id);

                    // set value input
                    $('#modalEditTagihan select[name="status"]').val(res.data.status);
                    $('#modalEditTagihan select[name="tracking"]').val(res.data.tracking);
                    $('#modalEditTagihan input[name="tanggal_selesai"]').val(res.data
                        .tanggal_selesai);
                    $('#modalEditTagihan textarea[name="keterangan"]').val(res.data
                        .keterangan);

                    if (res.data.status === 'selesai' || res.data.status === 'telat') {
                        $('#modalEditTagihan select[name="status"]').attr('disabled',
                            'disabled');
                    } else if (res.data.status === 'pending' || res.data.status ===
                        'proses') {
                        $('#modalEditTagihan select[name="status"]').attr('disabled',
                            false);
                    }

                    // format rupiah jika ada function
                    $('.format-rupiah').trigger('keyup');
                }
            });
        });
    });
</script>
@endsection
