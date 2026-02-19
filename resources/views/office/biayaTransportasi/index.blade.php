@extends('layouts_office.app')

@section('office_contents')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        .table>:not(caption)>*>* {
            border-bottom-width: 0
        }

        .table td,
        .table th {
            padding: 12px
        }

        .table-responsive {
            overflow: visible !important
        }

        .dropdown-menu {
            z-index: 1050
        }
    </style>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Biaya Transportasi</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBukti" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Pembayaran</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalEdit" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Biaya Transportasi</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEdit" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_pickup" name="id_pickup_driver">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Driver & Lokasi</label>
                            <input type="text" id="edit_pickup_label" class="form-control" disabled>
                        </div>

                        <hr>

                        <div id="editItemsContainer">
                        </div>

                        <button type="button" id="btnAddItem" class="btn btn-success w-100 mb-3">
                            <i class="fas fa-plus"></i> Tambah Item Biaya
                        </button>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Biaya Transportasi Driver</h5>
                <span id="dataCountBadge" class="badge bg-primary">0 data</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Koordinasi</th>
                                <th>Tipe</th>
                                <th>Harga</th>
                                <th>Struk</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="content_body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>

    <script>
        $(document).ready(loadData)

        function loadData() {
            $.get("{{ route('office.biayaTransportasi.get') }}", res => {
                const body = $("#content_body")
                body.empty()
                const grouped = {}
                res.data.forEach(d => {
                    if (!grouped[d.id_pickup_driver]) grouped[d.id_pickup_driver] = []
                    grouped[d.id_pickup_driver].push(d)
                })
                let no = 1
                Object.entries(grouped).forEach(([pickup, items]) => {
                    const rowspan = items.length
                    const images = items.map(i => i.bukti ? `{{ asset('storage') }}/${i.bukti}` : null)
                        .filter(Boolean)
                    items.forEach((d, i) => {
                        const harga = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(d.harga)
                        const tanggal = moment(d.created_at).format('DD MMM YYYY')
                        const koordinasi =
                            `${d.pickup_driver.karyawan.nama_lengkap} | ${d.pickup_driver.detail_pickup_driver?.[0]?.lokasi??'-'}`
                        body.append(`
<tr>
${i===0?`<td rowspan="${rowspan}">${no++}</td><td rowspan="${rowspan}">${koordinasi}</td>`:''}
<td>${d.tipe}</td>
<td>${harga}</td>
${i===0?`<td rowspan="${rowspan}"><button class="btn btn-secondary btn-sm lihat-bukti" data-images='${JSON.stringify(images)}'><i class="fas fa-image"></i> Lihat</button></td>`:''}
<td>${d.pengajuan_barang?.tracking?.tracking??'Menunggu'}</td>
${i===0?`
        <td rowspan="${rowspan}">${tanggal}</td>
        <td rowspan="${rowspan}">
        <div class="dropdown">
        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-cog"></i> Action
        </button>
        <ul class="dropdown-menu">
        <li><button class="dropdown-item btn-detail" data-pickup="${pickup}"><i class="fas fa-info-circle"></i> Detail</button></li>
        <li><button class="dropdown-item btn-edit" data-pickup="${pickup}"><i class="fas fa-edit"></i> Edit</button></li>
        <li><hr class="dropdown-divider"></li>
        <li><button class="dropdown-item text-danger btn-delete" data-pickup="${pickup}"><i class="fas fa-trash"></i> Hapus</button></li>
        </ul>
        </div>
        </td>`:''}
</tr>`)
                    })
                })
                $("#dataCountBadge").text(Object.keys(grouped).length + " data")
                window.groupedData = grouped
            })
        }

        $(document).on('click', '.lihat-bukti', function() {
            const images = $(this).data('images')
            let html = '<div class="row g-3">'
            images.forEach(i => html +=
                `<div class="col-md-6"><img src="${i}" class="img-fluid rounded border" style="max-height: 400px; object-fit: contain;"></div>`
                )
            html += '</div>'
            $("#modalBukti .modal-body").html(html)
            $("#modalBukti").modal('show')
        })

        $(document).on('click', '.btn-detail', function() {
            const pickup = $(this).data('pickup')
            const items = window.groupedData[pickup]
            let rows = ''
            let ticket = ''
            let total = 0
            items.forEach(i => {
                total += i.harga
                const keterangan = i.keterangan ?? '-'
                const bukti = i.bukti ?
                    `<a href="{{ asset('storage') }}/${i.bukti}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Lihat</a>` :
                    '-'
                rows +=
                    `<tr>
                        <td>${i.pengajuan_barang.tipe}</td>
                        <td>${i.tipe}</td>
                        <td>${new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(i.harga)}</td>
                        <td>${keterangan}</td>
                        <td>${bukti}</td>
                        <td>${new Date(i.pengajuan_barang.created_at).toISOString().slice(0, 16).replace('T', ' ')}</td>
                    </tr>`

                ticket +=
                `${i.pengajuan_barang.tracking.tracking}`
            })
            $('#detailContent').html(`
            <table class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Jenis Pengajuan</th>
                        <th>Tipe</th>
                        <th>Harga</th>
                        <th>Keterangan</th>
                        <th>Bukti</th>
                        <th>Tanggal Pengajuan</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5">Total</th>
                        <th>${new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(total)}</th>
                    </tr>
                </tfoot>
            </table>
            <div>
                <p>Tracking</p>
                <div class="alert alert-secondary" role="alert">
                    ${ticket}
                </div>    
            </div>
            `)
            $('#detailModal').modal('show')
        })

        $(document).on('click', '.btn-edit', function() {
            const pickup = $(this).data('pickup')
            const items = window.groupedData[pickup]
            const firstItem = items[0]

            // Set pickup driver info
            $('#edit_pickup').val(pickup)
            $('#edit_pickup_label').val(
                `${firstItem.pickup_driver.karyawan.nama_lengkap} | ${firstItem.pickup_driver.detail_pickup_driver?.[0]?.lokasi??'-'}`
            )

            // Clear existing rows
            $('#editItemsContainer').empty()

            // Add rows for each item
            items.forEach((item, index) => {
                const rowHtml = `
                <div class="row mb-3 edit-item-row border rounded p-3 bg-light" data-id="${item.id}">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tipe Biaya</label>
                        <select class="form-select edit-tipe" name="items[${index}][tipe]" required>
                            <option value="BBM" ${item.tipe === 'BBM' ? 'selected' : ''}>BBM</option>
                            <option value="TOL" ${item.tipe === 'TOL' ? 'selected' : ''}>TOL</option>
                            <option value="Parkir" ${item.tipe === 'Parkir' ? 'selected' : ''}>Parkir</option>
                            <option value="Lainnya" ${item.tipe === 'Lainnya' ? 'selected' : ''}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Harga</label>
                        <input type="number" class="form-control edit-harga" name="items[${index}][harga]" value="${item.harga}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <input type="text" class="form-control edit-keterangan" name="items[${index}][keterangan]" value="${item.keterangan ?? ''}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end pb-2">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-item w-100">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `
                $('#editItemsContainer').append(rowHtml)
            })

            $('#ModalEdit').modal('show')
        })

        $(document).on('click', '.btn-remove-item', function() {
            const row = $(this).closest('.edit-item-row')
            const itemId = row.data('id')

            Swal.fire({
                target: document.body,
                title: 'Hapus item ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(r => {
                if (r.isConfirmed) {
                    row.remove()
                    // Re-index the remaining items
                    $('.edit-item-row').each(function(index) {
                        $(this).find('[name^="items["]').each(function() {
                            const oldName = $(this).attr('name')
                            const newName = oldName.replace(/items\[\d+\]/,
                                `items[${index}]`)
                            $(this).attr('name', newName)
                        })
                    })
                }
            })
        })

        $('#btnAddItem').click(function() {
            const index = $('.edit-item-row').length
            const newRow = `
            <div class="row mb-3 edit-item-row border rounded p-3 bg-light">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tipe Biaya</label>
                    <select class="form-select edit-tipe" name="items[${index}][tipe]" required>
                        <option value="BBM">BBM</option>
                        <option value="TOL">TOL</option>
                        <option value="Parkir">Parkir</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Harga</label>
                    <input type="number" class="form-control edit-harga" name="items[${index}][harga]" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <input type="text" class="form-control edit-keterangan" name="items[${index}][keterangan]">
                </div>
                <div class="col-md-2 d-flex align-items-end pb-2">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-item w-100">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        `
            $('#editItemsContainer').append(newRow)
        })

        $('#formEdit').submit(function(e) {
            e.preventDefault()
            const pickup = $('#edit_pickup').val()

            if ($('.edit-item-row').length === 0) {
                Swal.fire({
                    target: document.body,
                    icon: 'error',
                    title: 'Error',
                    text: 'Minimal harus ada satu item biaya'
                })
                return
            }

            $.ajax({
                url: `/office/biaya-transportasi/update/${pickup}`,
                type: 'PUT',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: () => {
                    $('#ModalEdit').modal('hide')
                    loadData()
                    Swal.fire({
                        target: document.body,
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data berhasil diperbarui'
                    })
                },
                error: (xhr) => {
                    Swal.fire({
                        target: document.body,
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan data'
                    })
                }
            })
        })

        $(document).on('click', '.btn-delete', function() {
            const pickup = $(this).data('pickup')
            const items = window.groupedData[pickup]

            // Show confirmation with details
            let itemsList = ''
            items.forEach(item => {
                itemsList +=
                    `<li>${item.tipe}: ${new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(item.harga)}</li>`
            })

            Swal.fire({
                target: document.body,
                title: 'Hapus semua biaya pickup ini?',
                html: `<p class="text-muted">Semua data biaya dengan driver ini akan dihapus:</p><ul class="text-start">${itemsList}</ul>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus Semua',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
                confirmButtonColor: '#dc3545'
            }).then(r => {
                if (r.isConfirmed) {
                    $.ajax({
                        url: `/office/biaya-transportasi/delete/${pickup}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: () => {
                            loadData()
                            Swal.fire({
                                target: document.body,
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil dihapus'
                            })
                        },
                        error: (xhr) => {
                            Swal.fire({
                                target: document.body,
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'Terjadi kesalahan saat menghapus data'
                            })
                        }
                    })
                }
            })
        })
    </script>
@endsection
