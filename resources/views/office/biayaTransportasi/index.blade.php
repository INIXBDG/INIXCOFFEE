@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .table>:not(caption)>*>* {
            border-bottom-width: 0;
        }

        .table td,
        .table th {
            padding: 12px 10px;
            vertical-align: middle;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .card {
            overflow: visible;
        }

        .dropdown-menu {
            z-index: 1055 !important;
            min-width: 180px;
        }

        /* Pastikan modal berada di atas semua elemen (termasuk sidebar & navbar) */
        .modal {
            z-index: 1070 !important;
        }

        .modal-backdrop {
            z-index: 1060 !important;
        }

        .form-select,
        .form-control,
        .form-control:focus,
        .input-group-text {
            pointer-events: auto !important;
        }

        .biaya-item-row,
        .edit-item-row {
            position: relative;
            min-height: 120px;
        }

        .btn-remove-biaya,
        .btn-remove-item {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 10;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-image: none !important;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .modal-dialog {
                margin: 0.5rem;
            }

            .modal-lg,
            .modal-xl {
                max-width: 95% !important;
            }

            .table th,
            .table td {
                font-size: 0.875rem;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .d-grid.d-md-block {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            #dataCountBadge {
                margin-top: 0.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .row.g-2>div {
                margin-bottom: 0.75rem;
            }

            .biaya-item-row .row.g-2,
            .edit-item-row .row {
                flex-direction: column;
            }

            .biaya-item-row .col-md-4,
            .edit-item-row .col-md-3,
            .edit-item-row .col-md-4 {
                width: 100%;
            }
        }
    </style>

    <!-- BUTTON AJUKAN -->
    <div class="d-grid gap-2 d-md-flex justify-content-md-start mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalTambah">
            <i class="fas fa-plus me-1"></i> Ajukan Biaya
        </button>
    </div>

    <div class="container-fluid px-0 px-md-3">
        <div class="card shadow-sm border-0">
            <div
                class="card-header bg-white d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 py-3">
                <h5 class="mb-0 fw-semibold">Biaya Transportasi Driver</h5>
                <span id="dataCountBadge" class="badge bg-primary rounded-pill px-3 py-2">0 data</span>
            </div>
            <div class="card-body p-0 p-md-3">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Koordinasi</th>
                                <th>Tipe</th>
                                <th>Harga</th>
                                <th>Struk</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="content_body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Biaya Transportasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBukti" tabindex="-1" aria-labelledby="modalBuktiLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBuktiLabel">Bukti Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="buktiContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalEdit" tabindex="-1" aria-labelledby="ModalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formEdit" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_pickup" name="id_pickup_driver">

                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalEditLabel">Edit Biaya Transportasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Driver & Lokasi</label>
                            <input type="text" id="edit_pickup_label" class="form-control" disabled>
                        </div>

                        <hr>

                        <div id="editItemsContainer"></div>

                        <button type="button" id="btnAddItem" class="btn btn-success mt-3">
                            <i class="fas fa-plus me-1"></i> Tambah Item Biaya
                        </button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ModalTambah" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="ModalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formCreate" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalTambahLabel">Ajukan Biaya Transportasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Koordinasi Pickup</label>
                            <select name="id_pickup_driver" class="form-select" required>
                                <option value="">-- Pilih Pickup --</option>
                                @foreach ($dataPickup ?? [] as $pickup)
                                    <option value="{{ $pickup->id }}">
                                        {{ $pickup->karyawan->nama_lengkap }} -
                                        {{ $pickup->detailPickupDriver->first()->lokasi ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <hr class="my-4">
                        <label class="form-label fw-bold d-block mb-3">Rincian Biaya</label>
                        <div id="biayaItemsContainer">
                            <div class="biaya-item-row border rounded p-3 mb-3 position-relative">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-biaya"><i
                                        class="fas fa-trash"></i></button>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small">Tipe</label>
                                        <select name="biaya[${idx}][tipe]" class="form-select" required>
                                            <option value="BBM">BBM</option>
                                            <option value="TOL">TOL</option>
                                            <option value="Parkir">Parkir</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small">Harga</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control input-harga-visual"
                                                data-name="biaya[${idx}][harga]" placeholder="0" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small">Bukti (Max 2MB)</label>
                                        <input type="file" name="biaya[${idx}][bukti]" class="form-control"
                                            accept="image/*" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <input type="text" name="biaya[${idx}][keterangan]" class="form-control"
                                        placeholder="Keterangan (opsional)">
                                </div>
                            </div>
                        </div>
                        <button type="button" id="btnAddBiaya" class="btn btn-success mt-3">
                            <i class="fas fa-plus me-1"></i> Tambah Item Biaya
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ajukan Reimburst</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/locale/id.min.js"></script>

    <script>
        const rupiahFormat = new Intl.NumberFormat('id-ID');
        const currencyFormat = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });
        const dateFormat = d => moment(d).format('DD MMM YYYY');

        function loadData() {
            $.get("{{ route('office.biayaTransportasi.get') }}", res => {
                const tbody = $("#content_body").empty();
                const grouped = {};
                res.data.forEach(d => {
                    if (!grouped[d.id_pickup_driver]) grouped[d.id_pickup_driver] = [];
                    grouped[d.id_pickup_driver].push(d);
                });

                let no = 1;
                Object.entries(grouped).forEach(([pickup, items]) => {
                    const rowspan = items.length;
                    const images = items.map(i => i.bukti ? `{{ asset('storage') }}/${i.bukti}` : null)
                        .filter(Boolean);
                    const koordinasi =
                        `${items[0].pickup_driver.karyawan.nama_lengkap} | ${items[0].pickup_driver.detail_pickup_driver?.[0]?.lokasi ?? '-'}`;

                    items.forEach((d, idx) => {
                        const row = `
                    <tr>
                        ${idx===0 ? `<td rowspan="${rowspan}" class="text-center">${no++}</td><td rowspan="${rowspan}">${koordinasi}</td>` : ''}
                        <td>${d.tipe}</td>
                        <td class="text-end">${currencyFormat.format(d.harga)}</td>
                        ${idx===0 ? `<td rowspan="${rowspan}" class="text-center">
                                                    <button class="btn btn-secondary btn-sm lihat-bukti" data-images='${JSON.stringify(images)}'>
                                                        <i class="fas fa-image"></i> Lihat
                                                    </button>
                                                </td>` : ''}
                        <td>${d.pengajuan_barang?.tracking?.tracking ?? 'Menunggu'}</td>
                        ${idx===0 ? `
                                                    <td rowspan="${rowspan}">${dateFormat(d.created_at)}</td>
                                                    <td rowspan="${rowspan}" class="text-center" style="font-size: 20px">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button class="btn btn-info btn-detail" data-pickup="${pickup}">
                                                                <i class="fas fa-info-circle"></i> Detail
                                                            </button>
                                                            <button class="btn btn-primary btn-edit" data-pickup="${pickup}">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <button class="btn btn-danger btn-delete" data-pickup="${pickup}">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </button>
                                                        </div>
                                                    </td>` : ''}
                    </tr>`;
                        tbody.append(row);
                    });
                });

                $("#dataCountBadge").text(Object.keys(grouped).length + " data");
                window.groupedData = grouped;
            }).fail(() => Swal.fire('Error', 'Gagal memuat data', 'error'));
        }

        function formatRupiah(el) {
            let val = el.value.replace(/[^0-9]/g, '');
            $(el).data('clean-value', val);
            el.value = val ? rupiahFormat.format(val) : '';
        }

        function addCreateItem() {
            const idx = $('.biaya-item-row').length;
            const html = `
                <div class="biaya-item-row border rounded p-3 mb-3 position-relative">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-biaya"><i class="fas fa-trash"></i></button>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Tipe</label>
                            <select name="biaya[${idx}][tipe]" class="form-select" required>
                                <option value="BBM">BBM</option>
                                <option value="TOL">TOL</option>
                                <option value="Parkir">Parkir</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control input-harga-visual" data-name="biaya[${idx}][harga]" placeholder="0" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Bukti (Max 2MB)</label>
                            <input type="file" name="biaya[${idx}][bukti]" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <input type="text" name="biaya[${idx}][keterangan]" class="form-control" placeholder="Keterangan (opsional)">
                    </div>
                </div>`;
            $('#biayaItemsContainer').append(html);
            $('.input-harga-visual').last().on('input', function() {
                formatRupiah(this);
            });
        }

        function addEditItem() {
            const idx = $('.edit-item-row').length;

            const html = `
                <div class="edit-item-row border rounded p-3 mb-3 position-relative" data-idx="${idx}">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-item mb-">
                        <i class="fas fa-trash"></i> Hapus
                    </button>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tipe Biaya</label>
                            <select class="form-select" name="items[${idx}][tipe]" required>
                                <option value="BBM">BBM</option>
                                <option value="TOL">TOL</option>
                                <option value="Parkir">Parkir</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Harga</label>
                            <input type="number" class="form-control" name="items[${idx}][harga]" min="500" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <input type="text" class="form-control" name="items[${idx}][keterangan]">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Bukti</label>
                            <input type="file" class="form-control" name="items[${idx}][bukti]" accept="image/*">
                        </div>
                    </div>
                </div>`;

            $('#editItemsContainer').append(html);
        }

        function reindexCreateItems() {
            $('.biaya-item-row').each((i, el) => {
                $(el).find('[name^="biaya["]').each(function() {
                    let name = $(this).attr('name').replace(/biaya\[\d+\]/, `biaya[${i}]`);
                    $(this).attr('name', name);
                    if ($(this).hasClass('input-harga-visual')) $(this).attr('data-name', name);
                });
            });
        }

        function reindexEditItems() {
            $('.edit-item-row').each((i, el) => {
                $(el).attr('data-idx', i);
                $(el).find('[name^="items["]').each(function() {
                    let name = $(this).attr('name').replace(/items\[\d+\]/, `items[${i}]`);
                    $(this).attr('name', name);
                });
            });
        }

        $(document).ready(function() {
            loadData();

            $('#btnAddBiaya').click(addCreateItem);
            $('#btnAddItem').click(addEditItem);

            $(document).on('input', '.input-harga-visual', function() {
                formatRupiah(this);
            });

            $(document).on('click', '.btn-remove-biaya', function() {
                if ($('.biaya-item-row').length <= 1) return Swal.fire('Minimal 1 item', '', 'warning');
                $(this).closest('.biaya-item-row').remove();
                reindexCreateItems();
            });

            $(document).on('click', '.btn-remove-item', function() {
                if ($('.edit-item-row').length <= 1) return Swal.fire('Minimal 1 item', '', 'warning');
                $(this).closest('.edit-item-row').remove();
                reindexEditItems();
            });

            // ----------------- CREATE -----------------
            $('#formCreate').submit(function(e) {
                e.preventDefault();
                let valid = true;
                $('.input-harga-visual').each(function() {
                    let val = parseInt($(this).data('clean-value') || 0);
                    if (val < 500) {
                        valid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                if (!valid) return Swal.fire('Error', 'Minimal harga per item Rp 500', 'error');

                const formData = new FormData(this);
                $('.input-harga-visual').each(function() {
                    let val = $(this).data('clean-value') || '0';
                    formData.append($(this).data('name'), val);
                });

                $.ajax({
                    url: "{{ route('office.biayaTransportasi.create') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: () => {
                        bootstrap.Modal.getInstance($('#ModalTambah')[0])?.hide();
                        $('#formCreate')[0].reset();
                        $('#biayaItemsContainer').empty();
                        loadData();
                        Swal.fire('Sukses', 'Pengajuan berhasil dikirim', 'success');
                    },
                    error: xhr => {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan server';
                        if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON
                            .errors).flat().join('<br>');
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
            });

            $('#formEdit').on('submit', function(e) {
                e.preventDefault();

                const pickupId = $('#edit_pickup').val();
                const formData = new FormData(this);

                $.ajax({
                    url: `/office/biaya-transportasi/update/${pickupId}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        bootstrap.Modal.getInstance(document.getElementById('ModalEdit'))
                    .hide();
                        location.reload();
                    },
                    error: function(err) {
                        console.log(err.responseJSON);
                    }
                });
            });

            // ----------------- LIHAT BUKTI -----------------
            $(document).on('click', '.lihat-bukti', function() {
                const images = $(this).data('images') || [];
                let html = '<div class="row g-3 justify-content-center">';
                images.forEach(src => {
                    html +=
                        `<div class="col-12 col-md-6"><img src="${src}" class="img-fluid rounded shadow-sm" style="max-height:500px; object-fit:contain;"></div>`;
                });
                html += '</div>';
                $('#buktiContent').html(html);
                new bootstrap.Modal(document.getElementById('modalBukti')).show();
            });

            // ----------------- DETAIL -----------------
            $(document).on('click', '.btn-detail', function() {
                const pickup = $(this).data('pickup');
                const items = window.groupedData[pickup] || [];
                let rows = '',
                    total = 0,
                    tracking = 'Menunggu';

                items.forEach(i => {
                    total += Number(i.harga);
                    const bukti = i.bukti ?
                        `<a href="{{ asset('storage') }}/${i.bukti}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat</a>` :
                        '-';
                    rows += `<tr>
                <td>${i.pengajuan_barang?.tipe ?? '-'}</td>
                <td>${i.tipe}</td>
                <td class="text-end">${currencyFormat.format(i.harga)}</td>
                <td>${i.keterangan ?? '-'}</td>
                <td class="text-center">${bukti}</td>
                <td>${moment(i.created_at).format('DD MMM YYYY HH:mm')}</td>
            </tr>`;
                    if (i.pengajuan_barang?.tracking?.tracking) tracking = i.pengajuan_barang
                        .tracking.tracking;
                });

                $('#detailContent').html(`
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-light">
                        <tr><th>Jenis Pengajuan</th><th>Tipe</th><th>Harga</th><th>Keterangan</th><th>Bukti</th><th>Tanggal</th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                    <tfoot><tr><th colspan="5" class="text-end">Total</th><th class="text-end">${currencyFormat.format(total)}</th></tr></tfoot>
                </table>
            </div>
            <p class="mt-4 mb-2 fw-bold">Status Tracking</p>
            <div class="alert alert-secondary">${tracking}</div>
        `);

                new bootstrap.Modal(document.getElementById('detailModal')).show();
            });

            $(document).on('click', '.btn-edit', function() {
                const pickup = $(this).data('pickup');
                const items = window.groupedData[pickup] || [];
                const first = items[0] || {};

                $('#edit_pickup').val(pickup);
                $('#edit_pickup_label').val(
                    `${first.pickup_driver?.karyawan?.nama_lengkap ?? ''} | ${first.pickup_driver?.detail_pickup_driver?.[0]?.lokasi ?? '-'}`
                );

                $('#editItemsContainer').empty();

                items.forEach((item, idx) => {
                    const row = `
        <div class="edit-item-row border rounded p-3 mb-3 position-relative" data-idx="${idx}">
            <input type="hidden" name="items[${idx}][id]" value="${item.id}">

            <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                <i class="fas fa-trash"></i> Hapus
            </button>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tipe Biaya</label>
                    <select class="form-select" name="items[${idx}][tipe]" required>
                        <option value="BBM" ${item.tipe==='BBM'?'selected':''}>BBM</option>
                        <option value="TOL" ${item.tipe==='TOL'?'selected':''}>TOL</option>
                        <option value="Parkir" ${item.tipe==='Parkir'?'selected':''}>Parkir</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Harga</label>
                    <input type="number" class="form-control" name="items[${idx}][harga]" value="${item.harga}" min="500" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <input type="text" class="form-control" name="items[${idx}][keterangan]" value="${item.keterangan ?? ''}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Bukti</label>
                    <input type="file" class="form-control" name="items[${idx}][bukti]" accept="image/*">
                </div>
            </div>
        </div>`;

                    $('#editItemsContainer').append(row);
                });

                new bootstrap.Modal(document.getElementById('ModalEdit')).show();
            });
            // ----------------- DELETE -----------------
            $(document).on('click', '.btn-delete', function() {
                const pickup = $(this).data('pickup');
                const items = window.groupedData[pickup] || [];
                let list = items.map(i => `<li>${i.tipe}: ${currencyFormat.format(i.harga)}</li>`).join('');

                Swal.fire({
                    title: 'Hapus semua biaya ini?',
                    html: `<p class="text-muted small">Semua item akan dihapus:</p><ul class="text-start mb-0">${list}</ul>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545'
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: `/office/biaya-transportasi/delete/${pickup}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: () => {
                            loadData();
                            Swal.fire('Sukses', 'Data berhasil dihapus', 'success');
                        },
                        error: xhr => Swal.fire('Gagal', xhr.responseJSON?.message ||
                            'Gagal menghapus data', 'error')
                    });
                });
            });

            // Cleanup modal backdrop
            $('.modal').on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            });

            $('#ModalTambah').on('shown.bs.modal', function() {
                $(this).find('select[name="id_pickup_driver"]').focus();
            });
        });
    </script>
@endsection
