@extends('layouts.app')
@section('content')
<div class="container-fluid">
    {{-- Modal Loading --}}
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

    {{-- Modal Approval --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Konfirmasi Persetujuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="approveForm" method="POST">
                        @csrf
                        @method('PUT')
                        <p>Apakah Anda menyetujui pengajuan ini?</p>
                        <div id="manager-row">
                            <div class="btn-group" role="group" aria-label="Approval Options">
                                <input type="radio" class="btn-check" name="approval" id="approveYes" value="1" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="approveYes" onclick="toggleApprovalForms(false)">Ya</label>

                                <input type="radio" class="btn-check" name="approval" id="approveNo" value="2" autocomplete="off">
                                <label class="btn btn-outline-danger" for="approveNo" onclick="toggleApprovalForms(true)">Tidak</label>
                            </div>

                            {{-- Input Alasan Penolakan (Muncul saat 'Tidak') --}}
                            <div class="mt-3" id="alasanManagerInput" style="display: none;">
                                <label for="alasan_manager" class="form-label">Alasan Penolakan</label>
                                <textarea class="form-control" id="alasan_manager" name="alasan" rows="3"></textarea>
                            </div>
                        </div>
                        @php
                            $jabatan = auth()->user()->karyawan->jabatan ?? '';
                        @endphp
                        @if ($jabatan == 'Finance & Accounting')
                            <div class="row my-2" id="financeStatusBlock" style="display: none;">
                                <label for="status" class="form-label">Update Status Pencairan</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager">Sedang Dikonfirmasi oleh Bagian Finance kepada General Manager</option>
                                    <option value="Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi">Sedang Dikonfirmasi oleh Bagian Finance kepada Direksi</option>
                                    <option value="Finance Menunggu Approve Direksi">Finance Menunggu Approve Direksi</option>
                                    <option value="Membuat Permintaan Ke Direktur Utama">Membuat Permintaan Ke Direktur Utama</option>
                                    <option value="Pengajuan sedang dalam proses Pencairan">Pengajuan sedang dalam proses Pencairan</option>
                                    <option value="Pencairan Sudah Selesai">Pencairan Sudah Selesai</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>
                        @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Upload Invoice --}}
    <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-labelledby="uploadInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadInvoiceModalLabel">Upload Bukti Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="uploadInvoiceForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="invoiceFile" class="form-label">Pilih File Invoice (PDF/JPG/PNG)</label>
                            <input class="form-control" type="file" id="invoiceFile" name="invoice" required accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Maksimal ukuran file 5MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
            @php
                $jabatan = auth()->user()->karyawan->jabatan ?? '';
                $canCreate = ($jabatan == 'Customer Care' || $jabatan == 'Admin Holding'); // Perubahan di sini
            @endphp

            {{-- Tombol Ajukan hanya untuk Customer Care ATAU Admin Holding --}}
            @if ($canCreate)
                @if ($tracking == 'tutup')
                    <button class="btn btn-md btn-secondary mx-4" disabled title="Tidak bisa mengajukan karena pengajuan sebelumnya belum Selesai">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px"> Ajukan Souvenir
                    </button>
                @else
                    <a href="{{ route('pengajuansouvenir.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Ajukan Souvenir">
                        <img src="{{ asset('icon/plus.svg') }}" width="30px"> Ajukan Souvenir
                    </a>
                @endif
            @endif
            </div>

            {{-- Filter Bulan & Tahun untuk Semua Role --}}
            <div class="card" style="width: 100%">
                <div class="card-body d-flex justify-content-center">
                    <div class="col-md-4 mx-1">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select id="tahun" class="form-select" aria-label="tahun">
                            <option disabled>Pilih Tahun</option>
                            @php
                            $tahun_sekarang = now()->year;
                            for ($tahun = 2020; $tahun <= $tahun_sekarang   + 2; $tahun++) {
                                $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                echo "<option value=\"$tahun\" $selected>$tahun</option>";
                            }
                            @endphp
                        </select>

                    </div>
                    <div class="col-md-4 mx-1">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select id="bulan" class="form-select" aria-label="bulan">
                            <option disabled>Pilih Bulan</option>
                            @php
                            $bulan_sekarang = now()->month;
                            $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            for ($bulan = 1; $bulan <= 12; $bulan++) {
                                $bulan_awal = $nama_bulan[$bulan - 1];
                                $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                echo "<option value=\"$bulan\" $selected>$bulan_awal</option>";
                            }
                            @endphp
                        </select>
                    </div>

                    <div class="col-md-4 mx-1">
                        <button type="button" onclick="loadTables()" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                    </div>
                </div>
            </div>

            {{-- TABEL 1: Data Pengajuan (Ongoing) --}}
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Pengajuan Souvenir') }}</h3>
                    <table class="table table-striped" id="tableOngoing">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Pax</th>
                                <th scope="col">Harga Satuan</th>
                                <th scope="col">Total Pengajuan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TABEL 2: Data Selesai --}}
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Pengajuan Souvenir (Selesai / Ditolak)') }}</h3>
                    <table class="table table-striped" id="tableSelesai">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Nama Karyawan</th>
                                <th scope="col">Pax</th>
                                <th scope="col">Harga Satuan</th>
                                <th scope="col">Total Pengajuan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
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
<style>
    .cube {
        width: 64px;
        height: 64px;
        position: relative;
        transform-style: preserve-3d;
        transform: rotateX(-30deg) rotateY(30deg);
        animation: rotate 4s linear infinite;
    }
    .cube_item {
        width: 64px;
        height: 64px;
        position: absolute;
        background: #0d6efd;
        border: 2px solid white;
        opacity: 0.8;
    }
    .cube_x { transform: rotateY(90deg) translateZ(32px); }
    .cube_y { transform: rotateX(90deg) translateZ(32px); }
    .cube_z { transform: translateZ(32px); }
    @keyframes rotate {
        0% { transform: rotateX(-30deg) rotateY(30deg); }
        100% { transform: rotateX(-30deg) rotateY(390deg); }
    }
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }

</style>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Variabel global untuk role
var userRole = '{{ auth()->user()->karyawan->jabatan ?? "" }}';
// Variabel global untuk pengecekan role yang sama dengan CC/Admin Holding
var isCCorAdminHolding = (userRole === 'Customer Care' || userRole === 'Admin Holding');


 $(document).ready(function(){
    loadTables();
});

// --- FUNGSI HELPER (Tetap Sama) ---
function cleanDecimalString(value) {
    if (typeof value === 'undefined' || value === null) return 0;

    let stringValue = String(value);

    if (stringValue.includes('.') || stringValue.includes(',')) {
        let clean = stringValue.split(/[.,]/)[0];
        return parseInt(clean.replace(/[^0-9]/g, '')) || 0;
    }

    return parseInt(stringValue.replace(/[^0-9]/g, '')) || 0;
}

function formatRupiah(angka) {
    if (!angka) return 'Rp 0';

    let numericAngka = cleanDecimalString(angka);
    let number_string = numericAngka.toString();

    let sisa = number_string.length % 3,
        rupiah = number_string.substr(0, sisa),
        ribuan = number_string.substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    return 'Rp ' + (rupiah || '0');
}
// --- Akhir Fungsi Helper ---


function loadTables(){
    if ($.fn.DataTable.isDataTable('#tableOngoing')) {
        $('#tableOngoing').DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable('#tableSelesai')) {
        $('#tableSelesai').DataTable().destroy();
    }

    $('#loadingModal').modal('show');
    var tahun = $('#tahun').val();
    var bulan = $('#bulan').val();

    $.ajax({
        url: "{{ route('getPengajuanSouvenir', ['month' => ':month', 'year' => ':year'] ) }}"
             .replace(':month', bulan).replace(':year',tahun),
        type: "GET",
        success: function(response) {
            if (!response.success) {
                console.error("Gagal mengambil data:", response.message);
                $('#loadingModal').modal('hide');
                return;
            }

            let allData = response.data || [];

            var dataSelesai = allData.filter(item =>
                item.tracking.tracking === 'Selesai' ||
                item.tracking.tracking.includes("Ditolak")
            );

            var dataOngoing = allData.filter(item =>
                item.tracking.tracking !== 'Selesai' &&
                !item.tracking.tracking.includes("Ditolak")
            );

            // --- FUNGSI RENDER AKSI ---
            function renderActions(data, type, row) {
                var actions = "";
                var userKaryawanId = {{ auth()->user()->karyawan_id }};
                var trackingStatus = row.tracking.tracking;
                var karyawanId = row.karyawan.id;

                actions += '<div class="dropdown">';
                actions += '<button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>';
                actions += '<div class="dropdown-menu">';

                // 1. LOGIKA TOMBOL APPROVE (Tidak berubah)
                let canApprove = false;
                if (userRole == 'GM' && trackingStatus == 'Diajukan dan Sedang Ditinjau oleh General Manager') {
                    canApprove = true;
                }
                else if (userRole == 'Finance & Accounting' &&
                         trackingStatus != 'Selesai' &&
                         !trackingStatus.includes('Ditolak') &&
                         trackingStatus != 'Diajukan dan Sedang Ditinjau oleh General Manager'
                ) {
                    canApprove = true;
                }

                if(canApprove) {
                    actions += `<button type="button" class="dropdown-item"
                        onclick="openApproveModal(${row.id})">
                        <img src="{{ asset('icon/check-circle.svg') }}"> Approve/Update</button>`;
                }

                // 2. LOGIKA TOMBOL INVOICE (PERBAIKAN)
                if (row.invoice) {
                    var invoiceUrl = "{{ asset('storage') }}/" + row.invoice;
                    actions += `<a href="${invoiceUrl}" target="_blank" class="dropdown-item">
                        <img src="{{ asset('icon/file-text.svg') }}"> Lihat Invoice</a>`;
                } else {
                    // Hanya Pemilik (CC atau Admin Holding) yang bisa upload
                    if (isCCorAdminHolding && userKaryawanId === karyawanId) {
                        actions += `<button type="button" class="dropdown-item" onclick="openUploadModal(${row.id})">
                            <img src="{{ asset('icon/upload.svg') }}"> Upload Invoice</button>`;
                    }
                }

                // 3. LOGIKA TOMBOL DETAIL (Tidak berubah)
                var detailUrl = "{{ url('/pengajuansouvenir') }}/" + row.id;
                actions += `<a href="${detailUrl}" class="dropdown-item">
                    <img src="{{ asset('icon/clipboard-primary.svg') }}"> Detail</a>`;

                // 4. LOGIKA TOMBOL HAPUS (PERBAIKAN: Hanya Pemilik (CC/Admin Holding))
                if (isCCorAdminHolding && userKaryawanId === karyawanId &&
                   (trackingStatus.includes('Menunggu') || trackingStatus.includes('Ditolak'))) {
                    actions += `<form onsubmit="return confirm('Apakah Anda Yakin Ingin Menghapus?');"
                        action="{{ url('/pengajuansouvenir') }}/${row.id}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item">
                            <img src="{{ asset('icon/trash-danger.svg') }}"> Hapus</button>
                    </form>`;
                }

                actions += '</div></div>';
                return actions;
            }
            // --- AKHIR FUNGSI RENDER AKSI ---


            // --- Inisialisasi Tabel Ongoing (Sama dengan perbaikan terakhir) ---
            $('#tableOngoing').DataTable({
                data: dataOngoing,
                processing: true,
                columns: [
                    { data: "created_at", render: function (data) { moment.locale('id'); return moment(data).format('dddd, DD MMMM YYYY'); } },
                    { data: "karyawan.nama_lengkap" },
                    {
                        data: "detail",
                        render: function(data) {
                            if (!data || !Array.isArray(data)) return '-';
                            return data.map(item =>
                                `${item.souvenir ? item.souvenir.nama_souvenir : 'N/A'} (${item.pax} pax)`
                            ).join('<hr style="margin:4px 0;">');
                        }
                    },
                    {
                        data: "detail",
                        render: function(data) {
                            if (!data || !Array.isArray(data) || data.length === 0) return '-';
                            return data.map(item =>
                                formatRupiah(item.harga_satuan)
                            ).join('<hr style="margin:4px 0;">');
                        }
                    },
                    { data: "total_keseluruhan", render: function(data) { return formatRupiah(data); } },
                    { data: "tracking.tracking" },
                    { data: null, render: renderActions }
                ],
                order: [[0, 'desc']],
                columnDefs: [{ targets: [0], type: "date" }]
            });

            // --- Inisialisasi Tabel Selesai (Sama dengan perbaikan terakhir) ---
            $('#tableSelesai').DataTable({
                data: dataSelesai,
                processing: true,
                columns: [
                    { data: "created_at", render: function (data) { moment.locale('id'); return moment(data).format('dddd, DD MMMM YYYY'); } },
                    { data: "karyawan.nama_lengkap" },
                    {
                        data: "detail",
                        render: function(data) {
                            if (!data || !Array.isArray(data)) return '-';
                            return data.map(item =>
                                `${item.souvenir ? item.souvenir.nama_souvenir : 'N/A'} (${item.pax} pax)`
                            ).join('<hr style="margin:4px 0;">');
                        }
                    },
                    {
                        data: "detail",
                        render: function(data) {
                            if (!data || !Array.isArray(data) || data.length === 0) return '-';
                            return data.map(item =>
                                formatRupiah(item.harga_satuan)
                            ).join('<hr style="margin:4px 0;">');
                        }
                    },
                    { data: "total_keseluruhan", render: function(data) { return formatRupiah(data); } },
                    { data: "tracking.tracking" },
                    { data: null, render: renderActions }
                ],
                order: [[0, 'desc']],
                columnDefs: [{ targets: [0], type: "date" }]
            });

            $('#loadingModal').modal('hide');
        },
        error: function(err) { /* error handling */ }
    });
}

function openApproveModal(id) {
    var approveUrl = "{{ url('/pengajuansouvenir') }}/" + id;
    $('#approveForm').attr('action', approveUrl);

    if (userRole === 'Finance & Accounting') {
        $('#manager-row').hide();
        $('#financeStatusBlock').show();
        $('#approveYes').prop('checked', true);
        $('#approveNo').prop('checked', false);
        $('#alasanManagerInput').hide();
        document.getElementById('alasan_manager').value = '';
    } else {
        $('#manager-row').show();
        $('#financeStatusBlock').hide();
        $('#approveYes').prop('checked', true);
        toggleApprovalForms(false);
    }

    $('#approveModal').modal('show');
}

function openUploadModal(id) {
    var url = "{{ route('pengajuansouvenir.updateInvoice', ':id') }}";
    url = url.replace(':id', id);

    $('#uploadInvoiceForm').attr('action', url);
    $('#invoiceFile').val('');
    $('#uploadInvoiceModal').modal('show');
}

function toggleApprovalForms(showAlasan) {
    const alasanInput = document.getElementById('alasanManagerInput');
    const financeBlock = document.getElementById('financeStatusBlock');

    if (showAlasan) {
        alasanInput.style.display = 'block';
        if (financeBlock) financeBlock.style.display = 'none';
    } else {
        alasanInput.style.display = 'none';
        document.getElementById('alasan_manager').value = '';
        if (financeBlock && userRole === 'Finance & Accounting') {
            financeBlock.style.display = 'block';
        }
    }
}

 $('#approveForm').on('submit', function(e) {
    e.preventDefault();
    let form = $(this);
    let actionUrl = form.attr('action');
    $('#loadingModal').modal('show');
    $('#approveModal').modal('hide');

    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: form.serialize(),
        success: function(res) {
            $('#loadingModal').modal('hide');
            Swal.fire('Sukses', 'Status pengajuan berhasil diperbarui.', 'success');
            loadTables();
        },
        error: function(err) {
            $('#loadingModal').modal('hide');
            Swal.fire('Error', 'Gagal menyimpan data, silakan coba lagi.', 'error');
        }
    });
});

$('#uploadInvoiceForm').on('submit', function(e) {
    $('#loadingModal').modal('show');
});
</script>
@endpush
