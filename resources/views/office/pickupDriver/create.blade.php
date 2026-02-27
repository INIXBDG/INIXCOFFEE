@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <div class="container-fluid">
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="cube">
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_y"></div>
                    <div class="cube_item cube_x"></div>
                    <div class="cube_item cube_z"></div>
                </div>
            </div>
        </div>


        <div class="row justify-content-center">
            <form method="POST" action="{{ route('office.pickupDriver.store') }}">
                @csrf
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body" id="card">
                            <a href="{{ url()->previous() }}" class="btn btn-danger my-2">
                                <img src="{{ asset('icon/arrow-left.svg') }}" width="20"> Back
                            </a>

                            <h5 class="card-title text-center mb-4">Buat Koordinasi</h5>

                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label">Pilih Driver</label>
                                <div class="col-md-6">
                                    <select name="id_driver" class="form-select" required>
                                        <option selected disabled>Pilih Driver</option>
                                        @foreach ($dataDriver as $data)
                                            @php
                                                $status = $data->pickupDriver->first()->status_driver ?? 'Ready';
                                                $bolehDipilih = in_array($status, ['Ready', 'Selesai, Driver Ready']);
                                            @endphp

                                            <option value="{{ $data->id }}" {{ $bolehDipilih ? '' : 'disabled' }}>
                                                {{ $data->nama_lengkap }}
                                                {{ !$bolehDipilih ? '(Sedang Bertugas)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            <div class="mb-3 text-end">
                                <button type="button" class="btn btn-success" id="addRow">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                            <div id="koordinasi-wrapper">
                                <div class="koordinasi-item border rounded p-3 mb-3">
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label">Tipe</label>
                                        <div class="col-md-6">
                                            <select name="tipe[]" class="form-select" required>
                                                <option selected disabled>Pilih Tipe</option>
                                                <option value="Pengantaran">Pengantaran</option>
                                                <option value="Penjemputan">Penjemputan</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label">Lokasi</label>
                                        <div class="col-md-6">
                                            <input type="text" name="lokasi[]" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label">Tanggal Keberangkatan</label>
                                        <div class="col-md-6">
                                            <input type="date" name="tanggal[]" class="form-control"
                                                value="{{ now()->format('Y-m-d') }}" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label">Waktu Keberangkatan</label>
                                        <div class="col-md-6">
                                            <input type="time" name="waktu[]" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label">Detail Penjemputan</label>
                                        <div class="col-md-6">
                                            <textarea name="detail[]" id="detail" cols="40" class="form-control" rows="10">
1. nama lengkap : 
2. Nomor Telepon : 
3. Detail Lokasi: 
4. Detail Tambahan: 
                                            </textarea>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="button" class="btn btn-danger removeRow">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label">Budget Transportasi</label>
                                <div class="col-md-6">
                                    <input type="text" id="budget_view" class="form-control">

                                    <input type="hidden" name="budget" id="budget">
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Simpan
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).on('click', '#addRow', function() {
            let html = `
                <div class="koordinasi-item border rounded p-3 mb-3">
                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label">Tipe</label>
                        <div class="col-md-6">
                            <select name="tipe[]" class="form-select" required>
                                <option selected disabled>Pilih Tipe</option>
                                <option value="Pengantaran">Pengantaran</option>
                                <option value="Penjemputan">Penjemputan</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label">Lokasi</label>
                        <div class="col-md-6">
                            <input type="text" name="lokasi[]" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label">Tanggal Keberangkatan</label>
                        <div class="col-md-6">
                            <input type="date" name="tanggal[]" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label">Waktu Keberangkatan</label>
                        <div class="col-md-6">
                            <input type="time" name="waktu[]" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label">Detail Penjemputan</label>
                        <div class="col-md-6">
                            <textarea name="detail[]" id="detail" cols="40" class="form-control" rows="10">
1. nama lengkap : 
2. Nomor Telepon : 
3. Detail Lokasi: 
4. Detail Tambahan: 
                            </textarea>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-danger removeRow"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
                `;
            $('#koordinasi-wrapper').append(html);
        });

        $(document).on('click', '.removeRow', function() {
            $(this).closest('.koordinasi-item').remove();
        });

        const budgetView = document.getElementById('budget_view');
        const budgetReal = document.getElementById('budget');

        budgetView.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');

            budgetReal.value = value;

            this.value = formatRupiah(value);
        });

        function formatRupiah(angka) {
            return 'Rp ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
@endsection
