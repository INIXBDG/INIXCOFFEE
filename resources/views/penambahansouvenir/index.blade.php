@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- ================= MODAL LOADING ================= --}}
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

    <div class="row justify-content-center">
        <div class="col-md-12">

            {{-- Tombol Input Distribusi Baru --}}
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('penambahansouvenir.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" title="Input Distribusi Barang">
                    <img src="{{ asset('icon/plus.svg') }}" width="30px"> Input Distribusi
                </a>
            </div>

            {{-- Filter Data --}}
            <div class="card" style="width: 100%">
                <div class="card-body d-flex justify-content-center">
                    <div class="col-md-4 mx-1">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select id="tahun" class="form-select" aria-label="tahun">
                            <option disabled>Pilih Tahun</option>
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
                        <button type="button" onclick="loadTable()" class="btn click-primary" style="margin-top: 37px">Cari Data</button>
                    </div>
                </div>
            </div>

            {{-- TABEL RIWAYAT DISTRIBUSI --}}
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Riwayat Distribusi Souvenir') }}</h3>
                    <table class="table table-striped" id="tablePenambahan">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">RKM</th>
                                <th scope="col">Nama Souvenir</th>
                                <th scope="col">Penerima</th>
                                <th scope="col">Jabatan</th>
                                <th scope="col">Qty Keluar</th>
                                <th scope="col" class="text-center">Aksi</th>
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
    .cube { width: 64px; height: 64px; position: relative; transform-style: preserve-3d; transform: rotateX(-30deg) rotateY(30deg); animation: rotate 4s linear infinite; }
    .cube_item { width: 64px; height: 64px; position: absolute; background: #0d6efd; border: 2px solid white; opacity: 0.8; }
    .cube_x { transform: rotateY(90deg) translateZ(32px); }
    .cube_y { transform: rotateX(90deg) translateZ(32px); }
    .cube_z { transform: translateZ(32px); }
    @keyframes rotate { 0% { transform: rotateX(-30deg) rotateY(30deg); } 100% { transform: rotateX(-30deg) rotateY(390deg); } }
    .modal-dialog-centered { display: flex; align-items: center; min-height: calc(100% - 1rem); }
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
        loadTable();
    });

    function loadTable(){
        // Destroy table jika sudah ada untuk refresh
        if ($.fn.DataTable.isDataTable('#tablePenambahan')) {
            $('#tablePenambahan').DataTable().destroy();
        }

        $('#loadingModal').modal('show');
        var tahun = $('#tahun').val();
        var bulan = $('#bulan').val();

        // Pastikan Anda sudah membuat route 'getPenambahanSouvenir' di web.php
        // Route::get('/getPenambahanSouvenir/{month}/{year}', [PenambahanSouvenirController::class, 'getPenambahanSouvenir']);
        var url = "{{ url('/getPenambahanSouvenir') }}/" + bulan + "/" + tahun;

        $.ajax({
            url: url,
            type: "GET",
            success: function(response) {
                if (!response.success) {
                    console.error("Gagal mengambil data:", response.message);
                    $('#loadingModal').modal('hide');
                    return;
                }

                let data = response.data || [];

                $('#tablePenambahan').DataTable({
                    data: data,
                    processing: true,
                    columns: [
                        {
                            data: "tanggal",
                            render: function (data) {
                                moment.locale('id');
                                return moment(data).format('dddd, DD MMMM YYYY');
                            }
                        },
                    {
                        data: "rkm", // Relasi RKM
                        render: function(data) {
                            if (!data) return '-';

                            // Pastikan locale ID agar format bulan 'Jan', 'Feb', dst.
                            moment.locale('id');

                            // Format: 01 Jan
                            var start = moment(data.tanggal_awal).format('DD MMM');
                            // Format: 03 Jan 2025
                            var end = moment(data.tanggal_akhir).format('DD MMM YYYY');
                            // Ambil Nama Materi (handle jika null)
                            var namaMateri = (data.materi && data.materi.nama_materi)
                                ? data.materi.nama_materi
                                : 'Tanpa Materi';

                            // Return Format: [01 Jan - 03 Jan 2025] : Nama Materi
                            return `[${start} - ${end}] : ${namaMateri}`;
                        }
                    },
                        {
                            data: "souvenir", // Relasi Souvenir
                            render: function(data) {
                                return data ? data.nama_souvenir : '-';
                            }
                        },
                        { data: "nama" }, // Nama Penerima
                        { data: "jabatan" }, // Jabatan Penerima
                        {
                            data: "qty",
                            render: function(data) {
                                return `<strong>${data}</strong> Pcs`;
                            }
                        },
                        {
                            data: "id",
                            orderable: false, // Matikan sorting
                            searchable: false, // Matikan search
                            className: "text-center",
                            render: function(data, type, row) {
                                // URL Edit: /penambahansouvenir/{id}/edit
                                var editUrl = "{{ url('/penambahansouvenir') }}/" + data + "/edit";

                                return `
                                    <a href="${editUrl}" class="btn btn-sm btn-warning text-white" title="Edit Data">
                                        Edit
                                    </a>
                                `;
                            }
                        }
                    ],
                    order: [[0, 'desc']], // Urutkan berdasarkan tanggal terbaru
                    language: {
                        emptyTable: "Tidak ada data distribusi souvenir pada periode ini."
                    }
                });

                $('#loadingModal').modal('hide');
            },
            error: function(err) {
                console.error("Error AJAX:", err);
                $('#loadingModal').modal('hide');
                Swal.fire('Error', 'Gagal memuat data. Pastikan Anda memiliki akses.', 'error');
            }
        });
    }
</script>
@endpush
