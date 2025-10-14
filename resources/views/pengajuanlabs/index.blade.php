@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                @if ($tracking == 'tutup')
                    <button class="btn btn-md btn-secondary mx-4" disabled title="Tidak bisa mengajukan Lab/Subs karena status tidak 'Selesai'">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px"> Ajukan Lab/Subs
                    </button>
                @else
                    <a href="{{ route('pengajuanlabsdansubs.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Lab/Subs">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px"> Ajukan Lab/Subs
                    </a>
                @endif
            </div>

            <!-- Modal -->
            <div class="modal fade" id="approveRejectModal" tabindex="-1" aria-labelledby="approveRejectModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="approveRejectModalLabel">Konfirmasi Approval / Penolakan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="approvalForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <input type="hidden" name="id" id="modalId">
                                <input type="hidden" name="approval" id="modalApproval">

                                <!-- Alasan Penolakan -->
                                <div class="mb-3" id="reasonContainer" style="display: none;">
                                    <label for="alasan" class="form-label">Alasan Penolakan (Wajib jika menolak)</label>
                                    <textarea class="form-control" name="alasan" id="alasan" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                                </div>

                                <!-- Dropdown Khusus untuk Finance -->
                                <div class="mb-3" id="financeStatusContainer" style="display: none;">
                                    <label for="finance_status" class="form-label">Pilih Status Pencairan</label>
                                    <select class="form-select" id="finance_status" name="finance_status">
                                        <option value="">-- Pilih Status --</option>
                                        <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                        <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                        <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi</option>
                                        <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur Utama</option>
                                        <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam proses Pencairan</option>
                                        <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                </div>

                                <p>
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <span id="actionLabel"></span>
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Konfirmasi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-labelledby="uploadInvoiceLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="uploadInvoiceForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="uploadInvoiceId">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h5 class="modal-title">Upload Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                        <div class="mb-3">
                            <label for="invoice" class="form-label">Pilih File Invoice (PDF / JPG / PNG)</label>
                            <input type="file" name="invoice" id="invoice" class="form-control" required>
                        </div>
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>


            {{-- FILTER (khusus Finance) --}}
            @php
                $jabatan = auth()->user()->karyawan->jabatan ?? '';
            @endphp
            @if (in_array($jabatan, ['Finance & Accounting', 'GM', 'Koordinator ITSM', 'Technical Support']))
                <div class="card my-3">
                    <div class="card-body d-flex justify-content-center">
                        <div class="col-md-4 mx-1">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select id="tahun" class="form-select">
                                @php
                                    $tahun_sekarang = now()->year;
                                    for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                        $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                        echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-4 mx-1">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select id="bulan" class="form-select">
                                @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($i = 1; $i <= 12; $i++) {
                                        $selected = $i == $bulan_sekarang ? 'selected' : '';
                                        echo "<option value=\"$i\" $selected>{$nama_bulan[$i-1]}</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-4 mx-1">
                            <button onclick="loadPengajuan()" class="btn click-primary" style="margin-top: 32px">Cari Data</button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- TABEL DATA PENGAJUAN LAB/SUBS --}}
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Lab & Subs') }}</h3>
                    <table class="table table-striped" id="pengajuanLabSubsTable">
                        <thead>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <th>Nama Karyawan</th>
                                <th>Divisi</th>
                                <th>Jabatan</th>
                                <th>Jenis Pengajuan</th>
                                <th>Nama Lab/Subs</th>
                                <th>Status Tracking</th>
                                <th>RKM</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan dimuat via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Selesai') }}</h3>
                    <table class="table table-striped" id="pengajuanSelesaiTable">
                        <thead>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <th>Nama Karyawan</th>
                                <th>Divisi</th>
                                <th>Jabatan</th>
                                <th>Jenis Pengajuan</th>
                                <th>Nama Lab/Subs</th>
                                <th>Status Tracking</th>
                                <th>RKM</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        loadPengajuan();
        loadPengajuanSelesai();
    });

    function loadPengajuan() {
        console.log("loadPengajuan dipanggil...");
        let now = new Date();
        let month = $('#bulan').length ? $('#bulan').val() : (now.getMonth() + 1);
        let year  = $('#tahun').length ? $('#tahun').val() : now.getFullYear();

        $.ajax({
            url: "/getPengajuanLabSubs/" + month + "/" + year,
            type: "GET",
            beforeSend: function() {
                $("#pengajuanLabSubsTable tbody").html(`
                    <tr>
                        <td colspan="9" class="text-center">Loading...</td>
                    </tr>
                `);
            },
            success: function(response) {
                console.log(response);
                if (response.success && Array.isArray(response.data)) {
                    let rows = '';
                    $.each(response.data, function(index, item) {
                        let status = 'Belum Ada Tracking';
                        if (item.tracking && item.tracking.length > 0) {
                            status = item.tracking[item.tracking.length - 1].tracking;
                        }

                        if (status.includes('Selesai')) {
                            return; // lanjut ke item berikutnya
                        }

                        let userRole = '{{ auth()->user()->karyawan->jabatan ?? "" }}';
                        let userKaryawanId = {{ auth()->user()->karyawan->id ?? "null" }};
                        let isPengaju = item.karyawan_id == userKaryawanId;

                        let actionBtns = `
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                        `;

                        // Jika user adalah pengaju & status masih Diajukan
                        if (isPengaju && status.includes('Diajukan')) {
                            actionBtns += `
                                <li><button class="dropdown-item text-danger" onclick="deletePengajuan(${item.id})">
                                    <img src="{{ asset('icon/trash-danger.svg') }}" width="16"> Hapus</button></li>
                            `;
                        }

                        // Approve untuk setiap jabatan sesuai status
                        if (
                            (status === 'Diajukan dan Sedang Ditinjau oleh Education Manager' && userRole === 'Education Manager') ||
                            (status === 'Diajukan dan Sedang Ditinjau oleh SPV Sales' && userRole === 'SPV Sales') ||
                            (status === 'Diajukan dan Sedang Ditinjau oleh General Manager' && userRole === 'GM') ||
                            (
                                (status === 'Diajukan dan Sedang Ditinjau oleh Koordinator ITSM' && userRole === 'Koordinator ITSM') ||
                                (status.toLowerCase().includes('sedang ditinjau oleh koordinator itsm') && userRole === 'Koordinator ITSM')
                            ) &&
                            (
                                (item.subs && item.subs.nama_subs && item.subs.harga) ||
                                (item.lab && item.lab.nama_labs && item.lab.harga)
                            )
                        ) {
                            actionBtns += approveButton(item.id);
                            actionBtns += rejectButton(item.id);
                        }

                        // Role Technical Support atau Koordinator ITSM
                        if ((userRole === 'Technical Support' || userRole === 'Koordinator ITSM') &&
                            (status.includes('ditinjau oleh Koordinator ITSM'))) {
                            actionBtns += `
                                <li><button class="dropdown-item" onclick="editPengajuan(${item.id})">
                                    <img src="{{ asset('icon/edit-warning.svg') }}" width="16"> Edit</button></li>
                            `;
                            actionBtns += invoiceAction(item.id, item.invoice, item);
                        }

                        // Finance
                        const financeStatuses = [
                            'Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager',
                            'Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi',
                            'Finance Menunggu Approve Direksi',
                            'Membuat Permintaan Ke Direktur Utama',
                            'Telah disetujui oleh Koordinator ITSM dan sedang diproses oleh Finance',
                            'Pengajuan sedang dalam proses Pencairan',
                            'Pencairan Sudah Selesai'
                        ];

                        if (userRole === 'Finance &amp; Accounting' && financeStatuses.includes(status)) {
                            actionBtns += `
                                <li><button class="dropdown-item text-primary" onclick="openApproveRejectModal(${item.id}, 'finance-update')">
                                    <img src="{{ asset('icon/edit-warning.svg') }}" width="16"> Update Status Pencairan</button></li>
                                <li><button class="dropdown-item" onclick="viewDetail(${item.id})">
                                    <img src="{{ asset('icon/clipboard-primary.svg') }}" width="16"> Detail</button></li>
                            `;
                            actionBtns += invoiceAction(item.id, item.invoice, item);
                        }

                        // Default Detail jika tidak ada aksi
                        if (
                            !(
                                (isPengaju && status.includes('Diajukan')) ||
                                (status.includes('Diajukan dan Sedang Ditinjau oleh Education Manager') && userRole === 'Education Manager') ||
                                (status.includes('Diajukan dan Sedang Ditinjau oleh SPV Sales') && userRole === 'SPV Sales') ||
                                (status.includes('Diajukan dan Sedang Ditinjau oleh General Manager') && userRole === 'GM') ||
                                (status.includes('ditinjau oleh Koordinator ITSM') && userRole === 'Koordinator ITSM') ||
                                (userRole === 'Finance &amp; Accounting' && financeStatuses.includes(status))
                            )
                        ) {
                            actionBtns += `
                                <li><button class="dropdown-item" onclick="viewDetail(${item.id})">
                                    <img src="{{ asset('icon/clipboard-primary.svg') }}" width="16"> Detail</button></li>
                            `;
                            actionBtns += invoiceAction(item.id, item.invoice, item);
                        }

                        actionBtns += `</ul></div>`;

                        rows += `
                            <tr>
                                <td>${formatDate(item.created_at)}</td>
                                <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                                <td>${item.karyawan?.divisi ?? '-'}</td>
                                <td>${item.karyawan?.jabatan ?? '-'}</td>
                                <td>${item.lab ? 'Lab' : 'Subscription'}</td>
                                <td>${item.lab?.nama_labs ?? item.subs?.nama_subs ?? '-'}</td>
                                <td>${formatStatus(status)}</td>
                                <td>
                                    ${item.rkm
                                        ? `${item.rkm.perusahaan?.nama_perusahaan ?? '-'} (${item.rkm.materi?.nama_materi ?? '-'})
                                        <small class="text-muted">
                                            ${item.rkm.tanggal_awal ? moment(item.rkm.tanggal_awal).format('DD MMM YYYY') : '-'}
                                            –
                                            ${item.rkm.tanggal_akhir ? moment(item.rkm.tanggal_akhir).format('DD MMM YYYY') : '-'}
                                        </small>`
                                        : '-'
                                    }
                                </td>
                                <td>${actionBtns}</td>
                            </tr>
                        `;
                    });
                    $("#pengajuanLabSubsTable tbody").html(rows);
                    $("#pengajuanLabSubsTable").DataTable();
                } else {
                    $("#pengajuanLabSubsTable tbody").html(`
                        <tr><td colspan="9" class="text-center text-danger">Tidak ada data ditemukan.</td></tr>
                    `);
                }
            },
            error: function() {
                $("#pengajuanLabSubsTable tbody").html(`
                    <tr><td colspan="9" class="text-center text-danger">Gagal memuat data.</td></tr>
                `);
            }
        });
    }

    function loadPengajuanSelesai() {
        let now = new Date();
        let month = $('#bulan').length ? $('#bulan').val() : (now.getMonth() + 1);
        let year  = $('#tahun').length ? $('#tahun').val() : now.getFullYear();

        $.ajax({
            url: "/getPengajuanLabSubs/" + month + "/" + year,
            type: "GET",
            beforeSend: function() {
                $("#pengajuanSelesaiTable tbody").html(`
                    <tr>
                        <td colspan="9" class="text-center">Loading...</td>
                    </tr>
                `);
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    let selesaiData = response.data.filter(item => {
                        if (!item.tracking || item.tracking.length === 0) return false;
                        let lastStatus = item.tracking[item.tracking.length - 1].tracking;
                        return lastStatus.includes('Selesai');
                    });

                    if (selesaiData.length > 0) {
                        let rows = '';
                        $.each(selesaiData, function(index, item) {
                            let lastStatus = item.tracking[item.tracking.length - 1].tracking;

                            // 🔹 Siapkan tombol action dropdown
                            let actionBtns = `
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item" onclick="viewDetail(${item.id})">
                                                <img src="{{ asset('icon/clipboard-primary.svg') }}" width="16"> Detail
                                            </button>
                                        </li>
                                        ${invoiceAction(item.id, item.invoice, item)}
                                        <li><button class="dropdown-item" onclick="editPengajuan(${item.id})">
                                            <img src="{{ asset('icon/edit-warning.svg') }}" width="16"> Edit</button></li>
                                    </ul>
                                </div>
                            `;

                            // 🔹 Buat baris tabel
                            rows += `
                                <tr>
                                    <td>${formatDate(item.created_at)}</td>
                                    <td>${item.karyawan?.nama_lengkap ?? '-'}</td>
                                    <td>${item.karyawan?.divisi ?? '-'}</td>
                                    <td>${item.karyawan?.jabatan ?? '-'}</td>
                                    <td>${item.lab ? 'Lab' : 'Subscription'}</td>
                                    <td>${item.lab?.nama_labs ?? item.subs?.nama_subs ?? '-'}</td>
                                    <td>${formatStatus(lastStatus)}</td>
                                    <td>
                                        ${item.rkm
                                            ? `${item.rkm.perusahaan?.nama_perusahaan ?? '-'} (${item.rkm.materi?.nama_materi ?? '-'})
                                            <small class="text-muted d-block">
                                                ${item.rkm.tanggal_awal ? moment(item.rkm.tanggal_awal).format('DD MMM YYYY') : '-'}
                                                –
                                                ${item.rkm.tanggal_akhir ? moment(item.rkm.tanggal_akhir).format('DD MMM YYYY') : '-'}
                                            </small>`
                                            : '-'
                                        }
                                    </td>
                                    <td>${actionBtns}</td>
                                </tr>
                            `;
                        });
                        $("#pengajuanSelesaiTable tbody").html(rows);
                        $("#pengajuanSelesaiTable").DataTable();
                    } else {
                        $("#pengajuanSelesaiTable tbody").html(`
                            <tr><td colspan="9" class="text-center text-muted">Tidak ada pengajuan selesai bulan ini.</td></tr>
                        `);
                    }
                } else {
                    $("#pengajuanSelesaiTable tbody").html(`
                        <tr><td colspan="9" class="text-center text-danger">Gagal memuat data.</td></tr>
                    `);
                }
            },
            error: function() {
                $("#pengajuanSelesaiTable tbody").html(`
                    <tr><td colspan="9" class="text-center text-danger">Terjadi kesalahan saat memuat data.</td></tr>
                `);
            }
        });
    }

    // === Helper Button ===
    function approveButton(id) {
        return `
            <li><button class="dropdown-item" onclick="openApproveRejectModal(${id}, 'approve')">
                <img src="{{ asset('icon/check-circle.svg') }}" width="16"> Approve</button></li>
        `;
    }

    function rejectButton(id) {
        return `
            <li>
                <button class="dropdown-item text-danger" onclick="openApproveRejectModal(${id}, 'reject')">
                    <img src="{{ asset('icon/x-circle.svg') }}" width="16"> Reject
                </button>
            </li>
        `;
    }

    function invoiceAction(id, invoice, item) {
        let dataLengkap = false;

        if (item.subs) {
            dataLengkap = !!(
                item.subs.nama_subs &&
                item.subs.harga &&
                item.subs.mata_uang &&
                item.subs.kurs
            );
        } else if (item.lab) {
            dataLengkap = !!(
                item.lab.nama_labs &&
                item.lab.harga &&
                item.lab.mata_uang &&
                item.lab.kurs
            );
        }

        if (!dataLengkap) {
            return `
                <li>
                    <button class="dropdown-item text-muted" disabled>
                        <img src="{{ asset('icon/upload.svg') }}" width="16"> Upload Invoice (Data belum lengkap)
                    </button>
                </li>
            `;
        }

        if (invoice) {
            return `
                <li>
                    <a class="dropdown-item" href="/storage/pengajuanlabsubs/${invoice}" target="_blank">
                        <img src="{{ asset('icon/eye.svg') }}" width="16"> Lihat Invoice
                    </a>
                </li>
            `;
        } else {
            return `
                <li>
                    <button class="dropdown-item" onclick="openUploadInvoiceModal(${id})">
                        <img src="{{ asset('icon/upload.svg') }}" width="16"> Upload Invoice
                    </button>
                </li>
            `;
        }
    }


    // === Helper lainnya ===
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatStatus(status) {
        if (!status || typeof status !== 'string') {
            return `<span class="badge bg-secondary">Belum Ada Tracking</span>`;
        }
        if (status.includes('Diajukan')) {
            return `<span class="badge bg-warning text-dark">${status}</span>`;
        } else if (status.includes('disetujui')) {
            return `<span class="badge bg-success">${status}</span>`;
        } else if (status.includes('ditolak')) {
            return `<span class="badge bg-danger">${status}</span>`;
        }
        return `<span class="badge bg-info text-dark">${status}</span>`;
    }

    function openApproveRejectModal(id, action) {
        const userRole = '{{ auth()->user()->karyawan->jabatan ?? "" }}';

        $('#modalId').val(id);
        $('#modalApproval').val('');
        $('#alasan').val('');
        $('#reasonContainer').hide();
        $('#financeStatusContainer').hide();

        if (userRole === 'Finance &amp; Accounting' && action === 'finance-update') {
            $('#financeStatusContainer').show();
            $('#actionLabel').text('Pilih status proses pencairan pengajuan ini.');
        } else if (action === 'approve') {
            $('#modalApproval').val('1');
            $('#actionLabel').text('Anda akan menyetujui pengajuan ini.');
        } else if (action === 'reject') {
            $('#modalApproval').val('2');
            $('#reasonContainer').show();
            $('#actionLabel').text('Anda akan menolak pengajuan ini. Harap isi alasan.');
        }

        new bootstrap.Modal(document.getElementById('approveRejectModal')).show();
    }

    // === Submit form approve/reject ===
    $('#approvalForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = `/pengajuanlabsdansubs/${form.find('#modalId').val()}`;
        const userRole = '{{ auth()->user()->karyawan->jabatan ?? "" }}';
        let formData = form.serializeArray();

        if (userRole === 'Finance &amp; Accounting') {
            const financeStatus = $('#finance_status').val();
            formData = formData.filter(f => f.name !== 'approval');
            formData.push({ name: 'approval', value: financeStatus });
        }

        $.ajax({
            url,
            method: 'POST',
            data: $.param(formData),
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('approveRejectModal')).hide();
                    loadPengajuan();
                    Swal.fire('Berhasil!', res.message, 'success');
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Oops!', 'Terjadi kesalahan saat mengirim permintaan.', 'error');
            }
        });
    });

    // === Upload Invoice ===
    function openUploadInvoiceModal(id) {
        $('#uploadInvoiceId').val(id);
        $('#invoice').val('');
        new bootstrap.Modal(document.getElementById('uploadInvoiceModal')).show();
    }

    $('#uploadInvoiceForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#uploadInvoiceId').val();
        let formData = new FormData(this);

        $.ajax({
            url: `/pengajuanlabsdansubs/${id}/upload-invoice`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('uploadInvoiceModal')).hide();
                    Swal.fire('Berhasil!', res.message, 'success');
                    loadPengajuan();
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Oops!', 'Terjadi kesalahan saat upload invoice.', 'error');
            }
        });
    });

    // === Lain-lain ===
    function editPengajuan(id) {
        window.location.href = `/pengajuanlabsdansubs/${id}/edit`;
    }

    function viewDetail(id) {
        window.location.href = `/pengajuanlabsdansubs/${id}`;
    }

    function deletePengajuan(id) {
        if (confirm('Yakin ingin menghapus pengajuan ini?')) {
            $.ajax({
                url: `/pengajuanlabsdansubs/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    alert('Pengajuan dihapus!');
                    loadPengajuan();
                },
                error: function() {
                    alert('Gagal menghapus pengajuan.');
                }
            });
        }
    }
</script>
@endpush
@endsection
