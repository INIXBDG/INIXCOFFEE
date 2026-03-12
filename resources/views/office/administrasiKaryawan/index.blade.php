@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h4 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                <i class="bx bx-task text-primary me-2" style="font-size: 1.5rem;"></i>
                Tagihan Perusahaan 
            </h4>
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>
         @if (session('success_administrasi'))
            <div class="alert alert-success">{{ session('success_administrasi') }}</div>
        @endif

        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden">
                    <div class="card-body p-4 mb-4 h-100 " style="height: 320px;">

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEditAdministrasi" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5">Tambah Administrasi Karyawan</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <form method="post" id="formEditAdministrasi" enctype="multipart/form-data">
                                            @csrf

                                            <div class="mb-3">
                                                <label class="form-label">Nama Administrasi <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_administrasi" class="form-control">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Dateline</label>
                                                <input type="date" name="dateline" class="form-control">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="pending">Pending</option>
                                                    <option value="proses">Proses</option>
                                                    <option value="selesai">Selesai</option>
                                                    <option value="terlambat">Terlambat</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Bukti Transfer</label>
                                                <small id="pathBuktiTransfer" class="text-muted"></small>
                                                <input type="file" name="bukti_transfer" class="form-control">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Tanggal Selesai</label>
                                                <input type="date" name="tanggal_selesai" class="form-control">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Keterangan</label>
                                                <textarea class="form-control" name="keterangan"></textarea>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">
                                                    Simpan
                                                </button>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-4" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="border-0 ps-4"></th>
                                        <th class="border-0" style="min-width: 160px;">Administrasi Karyawan</th>
                                        <th class="border-0" style="min-width: 180px;">Tanggal Dateline</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Status</th>
                                        <th class="border-0" style="min-width: 150px;">Tanggal Selesai</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($administrasis as $administrasi)
                                        <tr class="border-bottom ">
                                            @if ($administrasi->status === 'selesai')
                                                <td class="text-center ps-4"><input class="custom-check" type="checkbox" checked disabled></td>
                                            @elseif ($administrasi->status === 'terlambat')
                                                <td class="text-center ps-4"><input class="custom-fail" type="checkbox" checked disabled></td>
                                            @else
                                                <td class="text-center ps-4"><input class="check-blue edit-administrasi" data-id="{{ $administrasi->id }}" type="checkbox"></td>
                                            @endif
                                            <td>
                                                {{ $administrasi->nama_administrasi }}
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($administrasi->dateline)->format('l, d F Y') }}
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
                                                        'terlambat' => [
                                                            'color' => 'danger',
                                                            'icon' => 'bx-info-circle',
                                                        ],
                                                    ];
                                                    $config = $statusConfig[$administrasi->status] ?? [
                                                        'color' => 'secondary',
                                                        'icon' => 'bx-info-circle',
                                                    ];
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2 text-capitalize">
                                                    <i class="bx {{ $config['icon'] }} me-1"></i>
                                                    {{ $administrasi->status }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $administrasi->tanggal_selesai ? \Carbon\Carbon::parse($administrasi->tanggal_selesai)->format('l, d F Y') : '-'}}
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
                                                            <button class="dropdown-item edit-administrasi"
                                                                    data-id="{{ $administrasi->id }}"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditAdministrasi">
                                                                Edit
                                                            </button>
                                                        </li>

                                                       <li>
                                                            @if($administrasi->bukti_transfer)
                                                                <a class="dropdown-item" href="{{ asset('storage/'.$administrasi->bukti_transfer) }}" target="_blank">
                                                                    Lihat Bukti Transfer
                                                                </a>
                                                            @endif
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                            href="{{ route('administrasi.karyawan.edit', $administrasi->id) }}">
                                                                Detail
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('administrasi.karyawan.destroy', $administrasi->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Yakin ingin menghapus administrasi ini?')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="bx bx-trash me-2"></i> Hapus
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
                                                    <p class="text-muted mt-3 mb-0">Tidak ada administrasi untuk
                                                        ditampilkan
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {  
            $(document).on('click', '.edit-administrasi', function () {
                let id = $(this).data('id');

                // reset checkbox supaya tidak tercentang
                if ($(this).is(':checkbox')) {
                    this.checked = false;
                }

                $('#modalEditAdministrasi').modal('show');

                $.ajax({
                    url: '/office/data-administrasi/' + id,
                    type: 'GET',

                    success: function(res) {

                        // set action form
                        $('#formEditAdministrasi').attr('action', '/office/administrasi-karyawan/update/' + id);

                        // isi input
                        $('#modalEditAdministrasi input[name="nama_administrasi"]').val(res.nama_administrasi);
                        $('#modalEditAdministrasi input[name="dateline"]').val(res.dateline);
                        $('#modalEditAdministrasi select[name="status"]').val(res.status);
                        $('#modalEditAdministrasi input[name="tanggal_selesai"]').val(res.tanggal_selesai);
                        $('#modalEditAdministrasi textarea[name="keterangan"]').val(res.keterangan);

                        if(res.bukti_transfer){
                            $('#pathBuktiTransfer').html(
                                `<a href="/storage/${res.bukti_transfer}" target="_blank">
                                    Lihat Bukti Transfer
                                </a>`
                            );
                        }else{
                            $('#pathBuktiTransfer').html(
                                `<span class="text-muted">Tidak ada bukti transfer</span>`
                            );
                        }

                    }
                });

            });
        });
</script>
@endsection