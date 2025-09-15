@extends('layouts.app')

@section('content')
    <input type="hidden" name="id_rkm" id="id_rkm" value="{{ $id }}">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <a href="javascript:void(0);" onclick="window.history.back();" class="btn click-primary my-2">
                            <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                        </a>
                        <h5 class="card-title">Payment Advance</h5>
                        <div class="row">
                            <div class="col-md-5" id="content_data_utama">
                            </div>
                            <div class="col-md-7">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="col-md-12" style="display: flex; justify-content: space-between;">
                                            <h5 class="mx-2 card-title">Detail Data</h5>
                                            @php
                                                $jabatan = auth()->user()->jabatan;
                                                $id_karyawan = auth()->user()->karyawan_id;
                                            @endphp
                                            <div></div>
                                        </div>
                                        <div class="accordion" id="netSalesAccordion">
                                            <!-- Net Sales data will be populated here -->
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Status Approval</h5>
                                        <table class="table table-striped mb-3">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <th>Approver</th>
                                                    <th>Status</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_approved"></tbody>
                                        </table>
                                        <h5 class="card-title mt-3">Tracking</h5>
                                        <table class="table table-striped text-center">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Status</th>
                                                    <th>Tanggal</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_tracking"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media screen and (min-width: 769px) {
            #titikdua {
                display: none;
            }
        }

        @media screen and (max-width: 768px) {
            #titikdua {
                display: flex;
            }

            .card {
                padding: 15px;
                max-width: 100%;
            }

            .card-body .row {
                margin-bottom: 10px;
            }

            .col-xs-4,
            .col-sm-4 {
                margin: 0 !important;
                display: flex;
            }

            .col-xs-1 {
                display: none;
            }

            .col-xs-7 {
                width: 100%;
                text-align: left;
            }
        }

        .card {
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.45);
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(2px);
        }

        .click-primary {
            background: #355C7C;
            border-radius: 1000px;
            padding: 10px 20px;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            font: normal bold 18px/1 "Open Sans", sans-serif;
            text-align: center;
            transition: color 0.1s linear, background-color 0.2s linear;
        }

        .click-primary:hover {
            color: #A5C7EF;
            background-color: #2a4a66;
        }

        .accordion-button {
            font-weight: bold;
        }

        .accordion-body table {
            margin-bottom: 0;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            loadData();
        });

        function loadData() {
            let id = $('#id_rkm').val();
            $.ajax({
                url: "{{ route('netsales.data.detail.get') }}",
                method: 'POST',
                data: {
                    value: id,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {
                    console.log("Response dari API:", response);
                    let content_utama = $('#content_data_utama');
                    let accordion_net_sales = $('#netSalesAccordion');
                    let tbody_approved = $('#tbody_approved');
                    let tbody_tracking = $('#tbody_tracking');

                    content_utama.empty();
                    if (!response.dataRKM || Object.keys(response.dataRKM).length === 0) {
                        content_utama.append(`
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Nama Perusahaan</p><p id="titikdua">:</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>-</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Materi</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>-</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Nama Sales</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>-</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Harga Jual</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>-</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Pax</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>-</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Metode Kelas</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>-</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Durasi</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>-</p></div>
                            </div>
                        `);
                    } else {
                        let data = response.dataRKM;
                        content_utama.append(`
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Nama Perusahaan</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>${data.nama_perusahaan}</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Materi</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>${data.materi}</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Nama Sales</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>${data.sales}</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Harga Jual</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>Rp${new Intl.NumberFormat('id-ID').format(data.harga_jual)}</p>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Pax</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>${data.pax}</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Total Harga Jual</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>Rp${new Intl.NumberFormat('id-ID').format(data.harga_jual * data.pax)}</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Metode Kelas</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>${data.metode_kelas}</p></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><p>Durasi</p></div>
                                <div class="col-md-1 col-sm-1 col-xs-1"><p>:</p></div>
                                <div class="col-md-7 col-sm-7 col-xs-7"><p>${data.durasi_kelas} hari</p></div>
                            </div>
                        `);
                    }

                    accordion_net_sales.empty();
                    if (!response.dataNetSales || response.dataNetSales.length === 0) {
                        accordion_net_sales.append(`
                            <div class="alert alert-info" role="alert">
                                Tidak ada data Net Sales
                            </div>
                        `);
                    } else {
                        let formatRupiah = (angka) => `Rp${new Intl.NumberFormat('id-ID').format(angka)}`;

                        response.dataNetSales.forEach(function(data, index) {
                            let tanggalObj = new Date(data.tgl_pa);
                            const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat',
                                'Sabtu'
                            ];
                            const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                            ];
                            let namaHari = hari[tanggalObj.getDay()];
                            let tanggal = tanggalObj.getDate();
                            let namaBulan = bulan[tanggalObj.getMonth()];
                            let tahun = tanggalObj.getFullYear();
                            let jam = tanggalObj.getHours().toString().padStart(2, '0');
                            let menit = tanggalObj.getMinutes().toString().padStart(2, '0');
                            let tglPaFormatted =
                                `${namaHari}, ${tanggal} ${namaBulan} ${tahun} ${jam}:${menit}`;

                            accordion_net_sales.append(`
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading${data.id_netSales}">
                                        <button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${data.id_netSales}" aria-expanded="${index === 0 ? 'true' : 'false'}" aria-controls="collapse${data.id_netSales}">
                                        ${data.peserta}
                                        </button>
                                    </h2>
                                    <div id="collapse${data.id_netSales}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" aria-labelledby="heading${data.id_netSales}" data-bs-parent="#netSalesAccordion">
                                        <div class="accordion-body">
                                            <table class="table table-striped">
                                                <tbody>
                                                    <tr><th>Transportasi</th><td>${formatRupiah(data.transportasi)}</td></tr>
                                                    <tr><th>Penginapan</th><td>${formatRupiah(data.penginapan)}</td></tr>
                                                    <tr><th>Fresh Money</th><td>${formatRupiah(data.fresh_money)}</td></tr>
                                                    <tr><th>Entertaint</th><td>${formatRupiah(data.entertaint)}</td></tr>
                                                    <tr><th>Souvenir</th><td>${formatRupiah(data.souvenir)}</td></tr>
                                                    <tr><th>Harga Penawaran</th><td>${formatRupiah(data.harga_penawaran)}</td></tr>
                                                    <tr><th>Total Payment Advance</th><td>${formatRupiah(data.totalPa)}</td></tr>
                                                    <tr><th>Diskon</th><td>${formatRupiah(data.diskon)}</td></tr>
                                                    <tr><th>Cashback</th><td>${formatRupiah(data.cashback)}</td></tr>
                                                    <tr><th>Total</th><td>${formatRupiah(data.total)}</td></tr>
                                                    <tr><th>Tanggal Payment Advance</th><td>${tglPaFormatted}</td></tr>
                                                    <tr><th>Deskripsi</th><td>${data.desc || '-'}</td></tr>
                                                    <tr><th>Tipe Pembayaran</th><td>${data.tipe_pembayaran}</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });

                        accordion_net_sales.append(`
                        <div class="mt-3">
                            <p class="text-muted">NetSales : ${formatRupiah(response.grandTotal)}</p>
                        </div>
                    `);
                    }

                    tbody_approved.empty();
                    if (!response.dataNetSales || response.dataNetSales.length === 0 || !response.dataNetSales[
                            0].approved || response.dataNetSales[0].approved.length === 0) {
                        tbody_approved.append(`
                            <tr>
                                <td colspan="5">Tidak ada data approval</td>
                            </tr>
                        `);
                    } else {
                        let no = 1;
                        response.dataNetSales.forEach(function(netSale) {
                            netSale.approved.forEach(function(data) {
                                let status;
                                if (data.status === 1) {
                                    if (data.level_status === 'III' && data.keterangan !==
                                        'Selesai') {
                                        status = "Diproses";
                                    } else {
                                        status = "Disetujui";
                                    }
                                } else if (data.status === 0) {
                                    status = "Ditolak";
                                } else {
                                    status = "Belum diketahui";
                                }

                                let tanggalObj = new Date(data.tanggal);
                                const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis',
                                    'Jumat', 'Sabtu'
                                ];
                                const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei',
                                    'Juni', 'Juli', 'Agustus', 'September', 'Oktober',
                                    'November', 'Desember'
                                ];
                                let namaHari = hari[tanggalObj.getDay()];
                                let tanggal = tanggalObj.getDate();
                                let namaBulan = bulan[tanggalObj.getMonth()];
                                let tahun = tanggalObj.getFullYear();
                                let jam = tanggalObj.getHours().toString().padStart(2, '0');
                                let menit = tanggalObj.getMinutes().toString().padStart(2, '0');
                                let tanggalLengkap =
                                    `${namaHari}, ${tanggal} ${namaBulan} ${tahun} ${jam}:${menit}`;

                                let approver = '-';
                                if (data.level_status === 'I') {
                                    approver = 'SPV Sales';
                                } else if (data.level_status === 'II') {
                                    approver = 'GM';
                                } else if (data.level_status === 'III') {
                                    approver = 'Finance & Accounting';
                                }

                                tbody_approved.append(`
                                    <tr>
                                        <td>${no++}</td>
                                        <td>${tanggalLengkap}</td>
                                        <td>${approver}</td>
                                        <td>${status}</td>
                                        <td>${data.keterangan}</td>
                                    </tr>
                                `);
                            });
                        });
                    }

                    tbody_tracking.empty();
                    if (!response.dataTracking) {
                        tbody_tracking.append(`
                            <tr>
                                <td class="text-center">Tidak ada data tracking</td>
                            </tr>
                        `);
                    } else {
                        let no = 1;
                        tbody_tracking.append(`
                            <tr>
                                <td>${no++}</td>
                                <td>${response.dataTracking.status}</td>
                                <td>${response.dataTracking.tanggal}</td>
                            </tr>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    </script>
@endsection
