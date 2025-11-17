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

                        {{-- === INI BAGIAN YANG DITAMBAHKAN === --}}
                        @php
                            $jabatan = auth()->user()->karyawan->jabatan ?? '';
                        @endphp
                        @if ($jabatan == 'Finance & Accounting')
                            {{-- Dropdown Status Khusus Finance (Muncul saat 'Ya') --}}
                            <div class="row my-2" id="financeStatusBlock" style="display: none;">
                                <label for="status" class="form-label">Update Status Pencairan</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="Pengajuan sedang dalam proses Pencairan">Proses Pencairan</option>
                                    <option value="Pencairan Selesai">Pencairan Selesai</option>
                                </select>
                            </div>
                        @endif
                        {{-- ======================================= --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
            @php
                $jabatan = auth()->user()->karyawan->jabatan ?? '';
            @endphp

            {{-- Tombol Ajukan hanya untuk Customer Care --}}
            @if ($jabatan == 'Customer Care')
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
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Souvenir (Berjalan)') }}</h3>
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
                    <h3 class="card-title text-center my-1">{{ __('Data Pengajuan Souvenir (Selesai / Ditolak)') }}</h3>
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
    /* CSS Loader (dari contoh Anda) */
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
 $(document).ready(function(){
    // Selalu jalankan loadTables saat halaman dimuat
    loadTables();
});

function formatRupiah(angka) {
    if (!angka) return 'Rp 0';
    let number_string = angka.toString().replace(/[^,\d]/g, ''),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return 'Rp ' + rupiah;
}

function loadTables(){
    // Hancurkan tabel lama jika ada
    if ($.fn.DataTable.isDataTable('#tableOngoing')) {
        $('#tableOngoing').DataTable().destroy();
    }
    if ($.fn.MataTable.isDataTable('#tableSelesai')) {
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

            // Pisahkan data: Selesai/Ditolak vs Berjalan
            var dataSelesai = allData.filter(item =>
                item.tracking.tracking === 'Pencairan Selesai' ||
                item.tracking.tracking.includes("Ditolak")
            );

            var dataOngoing = allData.filter(item =>
                item.tracking.tracking !== 'Pencairan Selesai' &&
                !item.tracking.tracking.includes("Ditolak")
            );

            // Inisialisasi Tabel Ongoing
            $('#tableOngoing').DataTable({
                data: dataOngoing,
                processing: true,
                columns: [
                    {
                        data: "created_at",
                        render: function (data) {
                            moment.locale('id');
                            return moment(data).format('dddd, DD MMMM YYYY');
                        }
                    },
                    { data: "karyawan.nama_lengkap" },
                    { data: "pax" },
                    {
                        data: "harga_satuan",
                        render: function(data) { return formatRupiah(data); }
                    },
                    {
                        data: "harga_total",
                        render: function(data) { return formatRupiah(data); }
                    },
                    { data: "tracking.tracking" },
                    {
                        data: null,
                        render: function (data, type, row) {
                            var actions = "";
                            var userRole = '{{ auth()->user()->karyawan->jabatan ?? "" }}';
                            var userKaryawanId = {{ auth()->user()->karyawan_id }};
                            var trackingStatus = data.tracking.tracking;
                            var karyawanId = data.karyawan.id;

                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>';
                            actions += '<div class="dropdown-menu">';

                            // --- Tombol Approve ---
                            let canApprove = false;
                            if (userRole == 'GM' && trackingStatus == 'Diajukan, Menunggu Persetujuan GM') {
                                canApprove = true;
                            } else if (userRole == 'Finance & Accounting' && trackingStatus == 'Disetujui GM, Menunggu Pencairan Finance') {
                                canApprove = true;
                            }

                            if(canApprove) {
                                actions += `<button type="button" class="dropdown-item"
                                    onclick="openApproveModal(${row.id})">
                                    <img src="{{ asset('icon/check-circle.svg') }}"> Approve/Tolak</button>`;
                            }

                            // --- Tombol Detail ---
                            var detailUrl = "{{ url('/pengajuansouvenir') }}/" + row.id;
                            actions += `<a href="${detailUrl}" class="dropdown-item">
                                <img src="{{ asset('icon/clipboard-primary.svg') }}"> Detail</a>`;

                            // --- Tombol Hapus ---
                            // Hanya bisa hapus jika diajukan oleh CC, DAN status masih "Menunggu" atau "Ditolak"
                            if (userKaryawanId === karyawanId &&
                               (trackingStatus.includes('Menunggu') || trackingStatus.includes('Ditolak'))) {

                                actions += `<form onsubmit="return confirm('Apakah Anda Yakin Ingin Menghapus?');"
                                    action="{{ url('/pengajuansouvenir') }}/${row.id}" method="POST">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="dropdown-item">
                                        <img src="{{ asset('icon/trash-danger.svg') }}"> Hapus</button>
                                </form>`;
                            }

                            actions += '</div></div>';
                            return actions;
                        }
                    }
                ],
                order: [[0, 'desc']],
                columnDefs: [{ targets: [0], type: "date" }]
            });

            // Inisialisasi Tabel Selesai
            $('#tableSelesai').DataTable({
                data: dataSelesai,
                processing: true,
                columns: [
                    {
                        data: "created_at",
                        render: function (data) {
                            moment.locale('id');
                            return moment(data).format('dddd, DD MMMM YYYY');
                        }
                    },
                    { data: "karyawan.nama_lengkap" },
                    { data: "pax" },
                    {
                        data: "harga_satuan",
                        render: function(data) { return formatRupiah(data); }
                    },
                    {
                        data: "harga_total",
                        render: function(data) { return formatRupiah(data); }
                    },
                    { data: "tracking.tracking" },
                    {
                        data: null,
                        render: function (data, type, row) {
                            var detailUrl = "{{ url('/pengajuansouvenir') }}/" + row.id;
                            return `<a href="${detailUrl}" class="btn btn-sm btn-info">
                                        <img src="{{ asset('icon/clipboard-primary.svg') }}" style="filter: brightness(0) invert(1);"> Detail</a>`;
                        }
                    }
                ],
                order: [[0, 'desc']],
                columnDefs: [{ targets: [0], type: "date" }]
            });

            $('#loadingModal').modal('hide');
        },
        error: function(err) {
            console.error("Error AJAX:", err);
            $('#loadingModal').modal('hide');
            Swal.fire('Error', 'Gagal memuat data dari server.', 'error');
        }
    });
}

function openApproveModal(id) {
    var approveUrl = "{{ url('/pengajuansouvenir') }}/" + id;
    $('#approveForm').attr('action', approveUrl);

    // Reset modal
    $('#approveYes').prop('checked', true);
    toggleAlasanManager(false);

    $('#approveModal').modal('show');
}

function toggleApprovalForms(showAlasan) {
    const alasanInput = document.getElementById('alasanManagerInput');
    const financeBlock = document.getElementById('financeStatusBlock'); // Target dropdown finance
    var userRole = '{{ auth()->user()->karyawan->jabatan ?? "" }}';

    if (showAlasan) {
        // ----- Tombol 'TIDAK' ditekan -----
        alasanInput.style.display = 'block';
        if (financeBlock) {
            financeBlock.style.display = 'none'; // Sembunyikan status finance
        }
    } else {
        // ----- Tombol 'YA' ditekan -----
        alasanInput.style.display = 'none';
        document.getElementById('alasan_manager').value = ''; // Kosongkan alasan

        // Tampilkan block status HANYA jika user adalah Finance
        if (financeBlock && userRole === 'Finance & Accounting') {
            financeBlock.style.display = 'block';
        }
    }
}

 // Handle Form Submit Approval
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
            // Muat ulang kedua tabel
            loadTables();
        },
        error: function(err) {
            $('#loadingModal').modal('hide');
            Swal.fire('Error', 'Gagal menyimpan data, silakan coba lagi.', 'error');
        }
    });
});
</script>
@endpush
