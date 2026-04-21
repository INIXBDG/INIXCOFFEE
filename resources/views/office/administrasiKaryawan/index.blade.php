@extends('layouts_office.app')

@section('office_contents')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-end align-items-center mb-5">
            <small class="text-muted fw-medium">{{ now()->translatedFormat('l, d F Y') }}</small>
        </div>
         @if (session('success_administrasi'))
            <div class="alert alert-success">{{ session('success_administrasi') }}</div>
        @endif

        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditAdministrasi" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5">Edit Administrasi Karyawan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form method="post" id="formEditAdministrasi" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Nama Administrasi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_administrasi" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dateline</label>
                                <input type="date" name="dateline" class="form-control">
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">* Status selesai dan terlambat otomatis terupdate dari sistem</small>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="proses">Proses</option>
                                    <option value="selesai" disabled hidden>Selesai</option>
                                    <option value="terlambat" disabled hidden>Terlambat</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Bukti Transfer</label>
                                <small id="pathBuktiTransfer" class="text-muted"></small>
                                <input type="file" name="bukti_transfer" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan"></textarea>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">
                                    Simpan
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEksportAdministrasi" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow">

                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold">
                            <i class="bi bi-download me-2"></i> Eksport Data Administrasi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-3">
                        <form method="post" id="formEksportAdministrasi"
                            action="{{ route('administrasi.karyawan.eksport') }}">
                            @csrf

                            <div class="mb-4">
                                <h6 class="text-muted mb-3">Berdasarkan Periode</h6>

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label small">Tahun</label>
                                        <select name="tahun" id="eksportTahunanAdminis" class="form-select">
                                            <option value="" selected>Pilih Tahun</option>
                                            @php
                                                $tahun_sekarang = now()->year;
                                                for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                                    echo "<option value=\"$tahun\">$tahun</option>";
                                                }
                                            @endphp
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small">Bulan</label>
                                        <select name="bulan" id="eksportBulananAdminis" class="form-select">
                                            <option value="" selected>Pilih Bulan</option>
                                            @php
                                                $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                for ($bulan = 1; $bulan <= 12; $bulan++) {
                                                    echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                                }
                                            @endphp
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small">Triwulan</label>
                                        <select name="quartal" id="eksportQuartalAdminis" class="form-select">
                                            <option value="" selected>Pilih Triwulan</option>
                                            <option value="1">Q1 (Jan - Mar)</option>
                                            <option value="2">Q2 (Apr - Jun)</option>
                                            <option value="3">Q3 (Jul - Sep)</option>
                                            <option value="4">Q4 (Okt - Des)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-muted mb-3">Berdasarkan Rentang Tanggal</h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">Dari Tanggal</label>
                                        <input type="date" name="start_date" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small">Sampai Tanggal</label>
                                        <input type="date" name="end_date" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 pt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-download me-1"></i> Eksport
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between">
                        <h4 class="mb-0 fw-semibold text-dark d-flex align-items-center">
                            <i class="bx bx-task text-primary me-2" style="font-size: 1.5rem;"></i>
                            Administrasi Karyawan
                        </h4>
                        <div class="d-flex gap-4 align-items-center">
                            <h6 class="mb-0">Export : </h6>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalEksportAdministrasi" class="btn btn-outline-secondary btn-sm pdfBtn">
                            PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4 pb-0 mb-4 h-100 " style="height: 320px;">

                        <div class="table-responsive mb-4">
                            <table class="table align-middle mb-4">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="border-0 ps-4"></th>
                                        <th class="border-0" style="min-width: 160px;">Administrasi Karyawan</th>
                                        <th class="border-0" style="min-width: 180px;">Tanggal Dateline</th>
                                        <th class="border-0" style="min-width: 150px;">Tanggal Selesai</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Status</th>
                                        <th class="border-0" style="min-width: 120px;">Progress</th>
                                        <th class="border-0 text-center pe-4" style="min-width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($administrasis as $administrasi)
                                        <tr class="border-bottom ">
                                            @if ($administrasi->status === 'selesai')
                                                <td class="text-center ps-4"><input class="custom-check" type="checkbox" checked disabled></td>
                                            @elseif ($administrasi->status === 'terlambat')
                                                <td class="text-center ps-4"><input class="custom-fail" type="checkbox" checked disabled></td>
                                            @else
                                                <td class="text-center ps-4"><input class="check-blue edit-administrasi" data-id="{{ $administrasi->id }}" type="checkbox"></td>
                                            @endif
                                            <td>
                                                {{ $administrasi->nama_administrasi }}
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($administrasi->dateline)->format('l, d F Y') }}
                                            </td>
                                            <td>
                                                {{ $administrasi->tanggal_selesai ? \Carbon\Carbon::parse($administrasi->tanggal_selesai)->format('l, d F Y') : '-'}}
                                            </td>
                                            <td class="text-center pe-4">
                                                @php
                                                    $statusConfig = [
                                                        'pending' => [
                                                            'color' => 'warning',
                                                            'icon' => 'bx-time-five',
                                                        ],
                                                        'proses' => [
                                                            'color' => 'primary',
                                                            'icon' => 'bx-loader-circle',
                                                        ],
                                                        'selesai' => [
                                                            'color' => 'success',
                                                            'icon' => 'bx-check-circle',
                                                        ],
                                                        'terlambat' => [
                                                            'color' => 'danger',
                                                            'icon' => 'bx-info-circle',
                                                        ],
                                                    ];
                                                    $config = $statusConfig[$administrasi->status] ?? [
                                                        'color' => 'secondary',
                                                        'icon' => 'bx-info-circle',
                                                    ];
                                                @endphp
                                                <span
                                                    class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} px-3 py-2 text-capitalize">
                                                    <i class="bx {{ $config['icon'] }} me-1"></i>
                                                    {{ $administrasi->status }}
                                                </span>
                                            </td>
                                            <td class="text-center pe-4">
                                                @php
                                                    if ($administrasi->tanggal_selesai) {
                                                        $diff = \Carbon\Carbon::parse($administrasi->dateline)
                                                            ->diffInDays(\Carbon\Carbon::parse($administrasi->tanggal_selesai), false);

                                                        if ($diff <= 0 || $administrasi->status === 'selesai') {
                                                            $progress = 100;
                                                            $color = 'success';
                                                        } elseif ($diff <= 3) {
                                                            $progress = 80;
                                                            $color = 'warning';
                                                        } elseif ($diff <= 7) {
                                                            $progress = 60;
                                                            $color = 'warning';
                                                        } else {
                                                            $progress = 0;
                                                            $color = 'danger';
                                                        }
                                                    } else {
                                                        $progress = 0;
                                                        $color = 'danger';
                                                    }
                                                @endphp

                                                <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">
                                                    {{ $progress }}%
                                                </span>
                                            </td>
                                            <td class="text-center pe-4 position-relative">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                                            type="button"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-boundary="viewport"
                                                            aria-expanded="false">
                                                        Aksi
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <button class="dropdown-item edit-administrasi"
                                                                    data-id="{{ $administrasi->id }}"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditAdministrasi">
                                                                Edit
                                                            </button>
                                                        </li>

                                                       <li>
                                                            @if($administrasi->bukti_transfer)
                                                                <a class="dropdown-item" href="{{ asset('storage/'.$administrasi->bukti_transfer) }}" target="_blank">
                                                                    Lihat Bukti Transfer
                                                                </a>
                                                            @endif
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                            href="{{ route('administrasi.karyawan.edit', $administrasi->id) }}">
                                                                Detail
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('administrasi.karyawan.destroy', $administrasi->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Yakin ingin menghapus administrasi ini?')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="bx bx-trash me-2"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bx bx-message-square-x text-muted"
                                                        style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-3 mb-0">Tidak ada administrasi untuk
                                                        ditampilkan
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mb-0 d-flex justify-content-center">
                                {{ $administrasis->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 h-100 rounded-4 overflow-hidden glass-force">
                    <div class="card-body p-4 mb-4">
                        <div class="row g-4">

                            <div class="col-md-3">
                                <div class="card-body">

                                    <div class="card border-0 mb-3 glass-force">
                                        <div class="card-body">

                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-list-check fs-4 text-primary me-2"></i>
                                                <h6 class="mb-0 fw-bold">Skema Penilaian</h6>
                                            </div>

                                            <ul class="list-unstyled ps-2 mb-0">

                                                <li class="d-flex align-items-center mb-2">
                                                    <span class="badge bg-success me-2">100%</span>
                                                    <small>On time / lebih cepat</small>
                                                </li>

                                                <li class="d-flex align-items-center mb-2">
                                                    <span class="badge bg-warning text-dark me-2">80%</span>
                                                    <small>Terlambat 1–3 hari</small>
                                                </li>

                                                <li class="d-flex align-items-center mb-2">
                                                    <span class="badge text-white me-2" style="background:#fd7e14;">
                                                        60%
                                                    </span>
                                                    <small>Terlambat 4–7 hari</small>
                                                </li>

                                                <li class="d-flex align-items-center">
                                                    <span class="badge bg-danger me-2">0%</span>
                                                    <small>Terlambat >7 hari</small>
                                                </li>

                                            </ul>

                                        </div>
                                    </div>

                                    <div class="card shadow-sm border-0 glass-force">
                                        <div class="card-body">

                                            <div class="d-flex align-items-center mb-3">
                                                <i class="bx bx-bar-chart-alt-2 fs-4 text-success me-2"></i>
                                                <h6 class="mb-0 fw-bold">Summary Penilaian</h6>
                                            </div>

                                            <div class="row text-center">

                                                <div class="col-6 mb-3">
                                                    <div class="border rounded py-2">
                                                        <h6 class="mb-0 text-success fw-bold" id="count100">0</h6>
                                                        <small class="text-muted">100%</small>
                                                    </div>
                                                </div>

                                                <div class="col-6 mb-3">
                                                    <div class="border rounded py-2">
                                                        <h6 class="mb-0 text-warning fw-bold" id="count80">0</h6>
                                                        <small class="text-muted">80%</small>
                                                    </div>
                                                </div>

                                                <div class="col-6">
                                                    <div class="border rounded py-2">
                                                        <h6 class="mb-0 fw-bold" style="color:#fd7e14;" id="count60">0</h6>
                                                        <small class="text-muted">60%</small>
                                                    </div>
                                                </div>

                                                <div class="col-6">
                                                    <div class="border rounded py-2">
                                                        <h6 class="mb-0 text-danger fw-bold" id="count0">0</h6>
                                                        <small class="text-muted">0%</small>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="card border-0 shadow-sm h-100 glass-force">

                                    <div class="card-body">

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0">Grafik Progress Administrasi</h6>
                                        </div>

                                        <canvas id="grafikProgress" height="120"></canvas>

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
    .custom-check {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #71DD37;
        border-radius: 5px;
        position: relative;
    }
    .check-blue {
        width: 20px;
        height: 20px;
        border: 2px solid #5B73E8;
        border-radius: 5px;
        position: relative;
    }

    .custom-check:checked {
        background-color: #71DD37;
    }

    .custom-check:checked::after {
        content: '✓';
        color: white;
        font-weight: bold;
        position: absolute;
        left: 2px;
        top: -2px;
    }
    .custom-fail:checked::after {
        content: '✖';
        color: white;
        font-weight: bold;
        position: absolute;
        left: 2px;
        top: -2px;
    }
    .custom-fail:checked {
        background-color: #FF3E1D;
    }
    .custom-fail {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #FF3E1D;
        border-radius: 5px;
        position: relative;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {  
            $(document).on('click', '.edit-administrasi', function () {
                let id = $(this).data('id');

                // reset checkbox supaya tidak tercentang
                if ($(this).is(':checkbox')) {
                    this.checked = false;
                }

                $('#modalEditAdministrasi').modal('show');

                $.ajax({
                    url: '/office/data-administrasi/' + id,
                    type: 'GET',

                    success: function(res) {

                        // set action form
                        $('#formEditAdministrasi').attr('action', '/office/administrasi-karyawan/update/' + id);

                        // isi input
                        $('#modalEditAdministrasi input[name="nama_administrasi"]').val(res.nama_administrasi);
                        $('#modalEditAdministrasi input[name="dateline"]').val(res.dateline).attr("disabled", "disabled");
                        $('#modalEditAdministrasi select[name="status"]').val(res.status);
                        $('#modalEditAdministrasi input[name="tanggal_selesai"]').val(res.tanggal_selesai);
                        $('#modalEditAdministrasi textarea[name="keterangan"]').val(res.keterangan);

                        if (res.status === 'selesai' || res.status === 'terlambat' ) {
                            $('#modalEditAdministrasi select[name="status"]').attr("disabled", "disabled");
                        }
                        
                        if(res.bukti_transfer){
                            $('#pathBuktiTransfer').html(
                                `<a href="/storage/${res.bukti_transfer}" target="_blank">
                                    Lihat Bukti Transfer
                                </a>`
                            );
                        }else{
                            $('#pathBuktiTransfer').html(
                                `<span class="text-muted">Tidak ada bukti transfer</span>`
                            );
                        }

                    }
                });

            });

            // Filter eksport administrasi
            function resetAll() {
                $('#eksportTahunanAdminis, #eksportBulananAdminis, #eksportQuartalAdminis')
                    .prop('disabled', false);
                $('input[name="start_date"], input[name="end_date"]')
                    .prop('disabled', false);
            }

            function disablePeriode() {
                $('#eksportTahunanAdminis, #eksportBulananAdminis, #eksportQuartalAdminis')
                    .prop('disabled', true);
            }

            function disableTanggal() {
                $('input[name="start_date"], input[name="end_date"]')
                    .prop('disabled', true);
            }

            function handleFilter() {
                let tahun = $('#eksportTahunanAdminis').val();
                let bulan = $('#eksportBulananAdminis').val();
                let quartal = $('#eksportQuartalAdminis').val();
                let start = $('input[name="start_date"]').val();
                let end = $('input[name="end_date"]').val();

                resetAll();

                if (start || end) {
                    disablePeriode();
                    return;
                }

                if (bulan && !tahun) {
                    $('#eksportTahunanAdminis').val(new Date().getFullYear());
                    tahun = $('#eksportTahunanAdminis').val();
                }

                if (quartal && !tahun) {
                    $('#eksportTahunanAdminis').val(new Date().getFullYear());
                    tahun = $('#eksportTahunanAdminis').val();
                }

                if (tahun && bulan) {
                    $('#eksportQuartalAdminis').prop('disabled', true);
                    disableTanggal();
                }

                if (tahun && quartal) {
                    $('#eksportBulananAdminis').prop('disabled', true);
                    disableTanggal();
                }

            }

            $('#eksportTahunanAdminis, #eksportBulananAdminis, #eksportQuartalAdminis').on('change', function () {
                handleFilter();
            });

            $('input[name="start_date"], input[name="end_date"]').on('change', function () {
                handleFilter();
            });

            // end filter eksport administrasi

            const ctx = document.getElementById('grafikProgress').getContext('2d');
            const rawData = @json($progressData);
            const grouped = groupProgress(rawData);

            const labels = Object.keys(grouped);
            const values = Object.values(grouped);
            
            function groupProgress(data) {
                let result = {
                    '100%': 0,
                    '80%': 0,
                    '60%': 0,
                    '0%': 0
                };

                data.forEach(item => {
                    if (item == 100) result['100%']++;
                    else if (item == 80) result['80%']++;
                    else if (item == 60) result['60%']++;
                    else result['0%']++;
                });

                return result;
            }

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Data',
                        data: values,
                        borderColor: '#5B73E8',
                        backgroundColor: 'rgba(91, 115, 232, 0.2)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#5B73E8',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw} data`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            function hitungSummary(data) {
                let count100 = 0, count80 = 0, count60 = 0, count0 = 0;
                let total = data.length;

                data.forEach(item => {

                    if (item == 100) count100++;
                    else if (item == 80) count80++;
                    else if (item == 60) count60++;
                    else count0++;

                });

                $('#count100').text(count100);
                $('#count80').text(count80);
                $('#count60').text(count60);
                $('#count0').text(count0);
            }

            hitungSummary(@json($progressData));
        });
</script>
@endsection