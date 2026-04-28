@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <a href="{{ route('pengajuanklaimmodul.index') }}" class="btn click-primary my-2">
                            <img src="{{ asset('icon/arrow-left.svg') }}" width="20px"> Back
                        </a>
                        <h5 class="card-title">Detail Klaim Modul</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Judul Modul</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        <p>{{ $data->module->title }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Kategori</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        <p>{{ $data->module->category }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Yang Mengajukan</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        <p>{{ $data->module->karyawan->nama_lengkap }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Pembuat</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        <p>
                                            @foreach($data->module->instructors as $instructor)
                                                {{ $instructor->karyawan->nama_lengkap ?? $instructor->username ?? '-' }}@if(!$loop->last),
                                                @endif
                                            @endforeach
                                        </p>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Deskripsi</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        <p>{{ $data->module->description ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Link Modul</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        @if($data->module->link)
                                            <a href="{{ $data->module->link }}" target="_blank"
                                                class="btn click-primary btn-sm">Buka Link</a>
                                        @else
                                            <p>-</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Status</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        <p>{{ $data->status }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Harga Modul</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        <p>{{ $data->price ? 'Rp ' . number_format($data->price, 0, ',', '.') : '-' }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <p>Tanggal Approval</p>
                                    </div>
                                    <div class="col-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-7">
                                        <p>{{ $data->approved_at ? \Carbon\Carbon::parse($data->approved_at)->translatedFormat('d F Y H:i') : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-end">
                            @php $jabatan = auth()->user()->karyawan->jabatan; @endphp
                            @if ($jabatan == 'Education Manager' && $data->status == 'Diajukan dan Sedang Ditinjau oleh Education Manager')
                                <button type="button" class="btn click-primary me-2"
                                    onclick="openApproveModal({{ $data->id }})">Approve</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="approveForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Klaim Modul</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Disetujui?</p>
                        <div class="btn-group">
                            <input type="radio" class="btn-check" name="approval" id="approveYes" value="1"
                                autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="approveYes"
                                onclick="togglePriceField(true)">Ya</label>
                            <input type="radio" class="btn-check" name="approval" id="approveNo" value="2"
                                autocomplete="off">
                            <label class="btn btn-outline-danger" for="approveNo"
                                onclick="togglePriceField(false)">Tidak</label>
                        </div>
                        <div class="mt-3" id="priceInput">
                            <label>Harga Modul (Rp)</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0">
                        </div>
                        <div class="mt-3" id="alasanInput" style="display: none;">
                            <label>Alasan Penolakan</label>
                            <textarea class="form-control" id="alasan" name="alasan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        @media screen and (min-width: 769px) {
            #titikdua {
                display: none;
            }
        }

        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
    </style>

    @push('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function openApproveModal(id) {
                $('#approveForm').attr('action', "{{ url('/klaimmodul') }}/" + id + "/approve");
                $('#approveModal').modal('show'); togglePriceField(true);
            }
            function togglePriceField(show) {
                document.getElementById('priceInput').style.display = show ? 'block' : 'none';
                document.getElementById('alasanInput').style.display = show ? 'none' : 'block';
                if (show) document.getElementById('price').setAttribute('required', 'required');
                else document.getElementById('price').removeAttribute('required');
            }
            $('#approveForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'), type: 'POST', data: $(this).serialize(),
                    success: function () {
                        $('#approveModal').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data berhasil diproses' }).then(() => window.location.reload());
                    },
                    error: function () { Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menyimpan data.' }); }
                });
            });
        </script>
    @endpush
@endsection