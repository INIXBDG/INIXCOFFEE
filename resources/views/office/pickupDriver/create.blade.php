@extends($extends)

@section($section)
    <style>
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            background-image: none !important;
            animation: shake 0.3s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @if ($section === 'crm_contents')
        <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 container-p-y">
            @else
                <div class="container-fluid">
    @endif

    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
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
        <form id="createForm" method="POST" action="{{ route('office.pickupDriver.store') }}"
            data-redirect-url="@if ($section === 'crm_contents') {{ route('CRM.index.koordinasi') }}@else{{ route('office.pickupDriver.index') }} @endif">
            @csrf
            <div class="col-md-12">
                <div class="card glass-force">
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
                                            {{ $data->nama_lengkap }} {{ !$bolehDipilih ? '(Sedang Bertugas)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">Kendaraan</label>
                            <div class="col-md-6">
                                <select name="kendaraan" id="kendaraan" class="form-select" required
                                    data-remaining-budget='@json($budgetPerKendaraan)'>
                                    <option selected disabled>Pilih Kendaraan</option>
                                    @forelse($kendaraan as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @empty
                                        <option value="" disabled>Tidak ada kendaraan tersedia</option>
                                    @endforelse
                                </select>

                                @if (empty($kendaraan))
                                    <small class="text-danger">
                                        <i class="fa fa-exclamation-triangle me-1"></i>
                                        Semua kendaraan sedang digunakan atau dalam perbaikan
                                    </small>
                                @endif
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
                                    <label class="col-md-4 col-form-label">Tipe Perjalanan</label>
                                    <div class="col-md-6">
                                        <select name="tipe[]" class="form-select tipe-select" required>
                                            <option selected disabled>Pilih Tipe</option>
                                            <option value="Operasional Kantor">Operasional Kantor</option>
                                            <option value="Mobile/Inhouse">Mobile/Inhouse</option>
                                            <option value="Kepentingan Direksi">Kepentingan Direksi</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label">Jenis Perjalanan</label>
                                    <div class="col-md-6">
                                        <select name="jenis[]" class="form-select" required>
                                            <option selected disabled>Pilih jenis</option>
                                            <option value="Pengantaran">Pengantaran</option>
                                            <option value="Penjemputan">Penjemputan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-md-4 col-form-label">Lokasi</label>
                                    <div class="col-md-6">
                                        <input type="text" name="lokasi[]" class="form-control"
                                            placeholder="jalan 123, Gedung AB" required>
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
                                        <textarea name="detail[]" class="form-control" rows="6">
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
                                <input type="text" id="budget_view" class="form-control"
                                    placeholder="contoh : 20.000 (by system jika tipe perjalanan Operasional Kantor)" readonly>
                                <small id="info_budget" class="text-muted"></small>
                                <input type="hidden" name="budget" id="budget">
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
    </div>

    @if ($section === 'crm_contents')
        </div>
        </div>
    @else
        </div>
    @endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const BASE_BUDGET = 1000000;
        const remainingBudgetMap = $('#kendaraan').data('remaining-budget') || {};

        function formatRupiah(number) {
            return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function parseRupiah(str) {
            return parseInt(str.replace(/[^0-9]/g, '')) || 0;
        }

        function getRemainingBudget(kendaraan) {
            return remainingBudgetMap[kendaraan] !== undefined ? remainingBudgetMap[kendaraan] : BASE_BUDGET;
        }

        function updateBudgetInfo() {
            const kendaraan = $('#kendaraan').val();
            const tipe = $('.tipe-select').last().val();
            const budgetView = $('#budget_view');
            const budgetHidden = $('#budget');
            const infoBudget = $('#info_budget');

            if (tipe === 'Operasional Kantor' && kendaraan) {
                const sisa = getRemainingBudget(kendaraan);
                infoBudget.text('Sisa budget minggu ini: ' + formatRupiah(sisa));
                budgetView.val(formatRupiah(sisa));
                budgetHidden.val(sisa);
                budgetView.removeClass('is-invalid').prop('readonly', true);
            } else {
                infoBudget.text('');
                budgetView.val('').prop('readonly', false);
                budgetHidden.val('');
            }
        }

        $('#kendaraan').on('change', function() {
            updateBudgetInfo();
        });

        $(document).on('change', '.tipe-select', function() {
            updateBudgetInfo();
        });

        $('#budget_view').on('input', function() {
            const kendaraan = $('#kendaraan').val();
            const tipe = $('.tipe-select').last().val();

            if (tipe !== 'Operasional Kantor' || !kendaraan) return;

            const sisa = getRemainingBudget(kendaraan);
            let value = parseRupiah($(this).val());

            if (value > sisa) {
                $(this).addClass('is-invalid');
                setTimeout(() => {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Budget Melebihi Limit!',
                        text: `Maksimal budget untuk ${kendaraan}: ${formatRupiah(sisa)}`,
                        timer: 2500,
                        showConfirmButton: false
                    });
                    $(this).val(formatRupiah(sisa));
                    $('#budget').val(sisa);
                    $(this).removeClass('is-invalid');
                }, 300);
            } else {
                $(this).removeClass('is-invalid');
                $('#budget').val(value);
            }

            const formatted = value ? formatRupiah(value) : '';
            $(this).val(formatted);
        });

        $(document).on('change', '.tipe-select, #kendaraan', function() {
            $('#budget_view').removeClass('is-invalid');
        });

        $('#createForm').on('submit', function(e) {
            e.preventDefault();

            if ($('.koordinasi-item').length === 0) {
                Swal.fire('Validasi', 'Minimal tambahkan 1 rute perjalanan', 'warning');
                return;
            }

            const formData = new FormData(this);
            const btnSubmit = $('#btnSubmit');
            const originalBtnText = btnSubmit.html();

            $('#loadingModal').modal('show');
            btnSubmit.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#loadingModal').modal('hide');

                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Lihat Data',
                            showCancelButton: true,
                            cancelButtonText: 'Tetap di Halaman'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const redirectUrl = $('#createForm').data('redirect-url');
                                window.location.href = redirectUrl;
                            } else {
                                $('#createForm')[0].reset();
                                $('#koordinasi-wrapper').html($(
                                        '#koordinasi-wrapper .koordinasi-item:first')
                                    .clone());
                                $('#info_budget').text('');
                                $('#budget_view').val('');
                                $('#budget').val('');
                            }
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    btnSubmit.prop('disabled', false).html(originalBtnText);

                    const response = xhr.responseJSON;

                    if (xhr.status === 422 && response?.errors) {
                        let errorMsg = '<ul class="mb-0 ps-3">';
                        Object.values(response.errors).forEach(err => {
                            errorMsg += `<li>${err[0]}</li>`;
                        });
                        errorMsg += '</ul>';

                        Swal.fire({
                            title: 'Validasi Gagal',
                            html: errorMsg,
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        const msg = response?.message || 'Terjadi kesalahan server';
                        Swal.fire('Error!', msg, 'error');
                    }
                }
            });
        });

        $(document).on('click', '#addRow', function() {
            let html = `
<div class="koordinasi-item border rounded p-3 mb-3">
    <div class="row mb-3">
        <label class="col-md-4 col-form-label">Tipe Perjalanan</label>
        <div class="col-md-6">
            <select name="tipe[]" class="form-select tipe-select" required>
                <option selected disabled>Pilih Tipe</option>
                <option value="Operasional Kantor">Operasional Kantor</option>
                <option value="Mobile/Inhouse">Mobile/Inhouse</option>
                <option value="Kepentingan Direksi">Kepentingan Direksi</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-md-4 col-form-label">Jenis Perjalanan</label>
        <div class="col-md-6">
            <select name="jenis[]" class="form-select" required>
                <option selected disabled>Pilih jenis</option>
                <option value="Pengantaran">Pengantaran</option>
                <option value="Penjemputan">Penjemputan</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-md-4 col-form-label">Lokasi</label>
        <div class="col-md-6">
            <input type="text" name="lokasi[]" class="form-control" placeholder="jalan 123, Gedung AB" required>
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
            <textarea name="detail[]" class="form-control" rows="6">
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
</div>`;
            $('#koordinasi-wrapper').append(html);
        });

        $(document).on('click', '.removeRow', function() {
            Swal.fire({
                title: 'Hapus rute ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).closest('.koordinasi-item').remove();
                }
            });
        });

        $(document).ready(function() {
            if ($('#kendaraan').val()) {
                $('#kendaraan').trigger('change');
            }
        });
    </script>
@endsection