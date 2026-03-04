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

            {{-- Tombol Input Penukaran Baru --}}
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('penukaransouvenir.create') }}" class="btn btn-warning btn-md mx-4" data-toggle="tooltip" title="Input Penukaran Barang">
                    <i class="bi bi-arrow-left-right"></i> Form Penukaran
                </a>
            </div>

            {{-- Filter Data (Tahun & Bulan) --}}

                    {{-- Menangani format tanggal yang berbeda (tanggal_pengajuan vs tanggal) --}}
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

            {{-- TABEL RIWAYAT PENUKARAN --}}
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Riwayat Penukaran Souvenir') }}</h3>
                    <table class="table table-striped table-hover" id="tableRiwayat">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal Tukar</th>
                                <th scope="col">RKM</th>
                                <th scope="col">Nama Peserta</th>
                                <th scope="col" class="text-danger">Souvenir Awal (Kembali)</th>
                                <th scope="col"></th>
                                <th scope="col" class="text-success">Souvenir Baru (Keluar)</th>
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
        // Hapus instance DataTable lama jika ada untuk mencegah inisialisasi ganda
        if ($.fn.DataTable.isDataTable('#tableRiwayat')) {
            $('#tableRiwayat').DataTable().destroy();
        }

        $('#loadingModal').modal('show');
        var tahun = $('#tahun').val();
        var bulan = $('#bulan').val();

        // URL API
        var url = "{{ url('/penukaransouvenir/getRiwayat') }}/" + bulan + "/" + tahun;

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

                $('#tableRiwayat').DataTable({
                    data: data,
                    processing: true,
                    columns: [
                        {
                            // Kolom Tanggal Tukar
                            data: "tanggal_tukar",
                            render: function (data) {
                                moment.locale('id');
                                return `
                                    ${moment(data).format('DD MMM YYYY')}<br>
                                    <small class="text-muted">${moment(data).format('H:mm')} WIB</small>
                                `;
                            }
                        },
                        {
                            // Kolom RKM
                            data: "rkm",
                            render: function(data) {
                                if (!data) return '-';
                                moment.locale('id');

                                // Format Tanggal Awal & Akhir
                                var tglAwal = moment(data.tanggal_awal).format('DD MMM');
                                var tglAkhir = moment(data.tanggal_akhir).format('DD MMM');

                                // Logika Nama Materi/Program
                                var nama = (data.materi && data.materi.nama_materi)
                                            ? data.materi.nama_materi
                                            : (data.nama_program || '-');

                                // Output: Nama Materi (baris 1) + Tanggal Awal - Tanggal Akhir (baris 2)
                                return `<span class="fw-bold text-primary">${nama}</span><br><small>${tglAwal} - ${tglAkhir}</small>`;
                            }
                        },
                        {
                            // Kolom Nama Peserta
                            data: "regist",
                            render: function(data) {
                                if(!data || !data.peserta) return 'Peserta Tidak Ditemukan';
                                var nama = data.peserta.nama;
                                var instansi = data.peserta.perusahaan ? data.peserta.perusahaan.nama_perusahaan : '-';
                                return `<strong>${nama}</strong><br><small class="text-muted">${instansi}</small>`;
                            }
                        },
                        {
                            // Kolom Souvenir Lama (UPDATED KEY: souvenir_old)
                            data: "souvenir_old",
                            className: "bg-light text-danger",
                            render: function(data) {
                                // data bisa null jika relasi soft-deleted/hilang
                                return data ? `<i class="bi bi-box-arrow-in-left"></i> ${data.nama_souvenir}` : 'Data Terhapus';
                            }
                        },
                        {
                            // Kolom Panah (Dekorasi)
                            data: null,
                            className: "text-center",
                            orderable: false,
                            searchable: false,
                            render: function() {
                                return '<i class="bi bi-arrow-right text-primary"></i>';
                            }
                        },
                        {
                            // Kolom Souvenir Baru (UPDATED KEY: souvenir_new)
                            data: "souvenir_new",
                            className: "bg-light text-success fw-bold",
                            render: function(data) {
                                return data ? `<i class="bi bi-box-arrow-right"></i> ${data.nama_souvenir}` : 'Data Terhapus';
                            }
                        }
                    ],
                    order: [[0, 'desc']], // Urutkan tanggal terbaru
                    language: {
                        emptyTable: "Tidak ada riwayat penukaran pada periode ini."
                    }
                });

                $('#loadingModal').modal('hide');
            },
            error: function(err) {
                console.error("Error AJAX:", err);
                $('#loadingModal').modal('hide');
                Swal.fire('Error', 'Gagal memuat data.', 'error');
            }
        });
    }
</script>
@endpush
