@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<div class="container text-center">
    <div class="row">
        <div class="col-10">
            <div class="container-fluid" style="margin: 0; padding: 0;">
                <div class="row justify-content-center" style="margin: 0; padding: 0;">
                    <div class="col-md-20">
                        <div class="d-flex justify-content-end">
                        </div>
                        <div class="card m-4">
                            <div class="card-body table-responsive">
                                <h3 class="card-title text-center my-1">{{ __('Data Surat Perjalanan') }}</h3>
                                <table class="table table-striped" id="jabatantable">
                                    <thead>
                                        <tr>
                                            <th scope="col">No</th>
                                            <th scope="col">Nama Karyawan</th>
                                            <th scope="col">Divisi</th>
                                            <th scope="col">Jabatan</th>
                                            <th scope="col">Tipe</th>
                                            <th scope="col">Tujuan</th>
                                            <th scope="col">Alasan</th>
                                            <th scope="col">Durasi</th>
                                            <th scope="col">Tanggal</th>
                                            <th scope="col">Status</th>
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
        </div>
        <div class="col-2">
            <select id="bulan_select" class="form-select m-2" aria-label="Pilih Bulan">
                <option selected disabled>Pilih Bulan</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                    @endfor
            </select>

            <button type="button" onclick="excelDownloadMonth();" class="btn btn-success m-2" style="width: 100%;">
                <i class="fa-solid fa-file-excel"></i> Download Per Bulan
            </button>
            <button type="button" onclick="pdfDownloadMonth();" class="btn btn-danger m-2" style="width: 100%;">
                <i class="fa-solid fa-file-pdf"></i> Download Per Bulan
            </button>

            <select id="tahun_Select" class="form-select m-2 mt-5" aria-label="Pilih Tahun">
                <option selected disabled>Pilih Tahun</option>
            </select>
            <button type="button" onclick="excelDownloadYear();" class="btn btn-success m-2" style="width: 100%;">
                <i class="fa-solid fa-file-excel"></i> Download Per tahun
            </button>
            <button type="button" onclick="pdfDownloadYear();" class="btn btn-danger m-2" style="width: 100%;">
                <i class="fa-solid fa-file-pdf"></i> Download Per Tahun
            </button>
        </div>
    </div>
</div>

<style>
    .loader {
        position: relative;
        text-align: center;
        margin: 15px auto 35px auto;
        z-index: 9999;
        display: block;
        width: 80px;
        height: 80px;
        border: 10px solid rgba(0, 0, 0, .3);
        border-radius: 50%;
        border-top-color: #000;
        animation: spin 1s ease-in-out infinite;
        -webkit-animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    @-webkit-keyframes spin {
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    .modal-content {
        border-radius: 0px;
        box-shadow: 0 0 20px 8px rgba(0, 0, 0, 0.7);
    }

    .modal-backdrop.show {
        opacity: 0.75;
    }

    .loader-txt {
        p {
            font-size: 13px;
            color: #666;

            small {
                font-size: 11.5px;
                color: #999;
            }
        }
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    $(document).ready(function() {
        var userRole = '{{ auth()->user()->jabatan}}';
        var user = '{{ auth()->user()->karyawan_id }}';
        //console.log(user);
        $('#jabatantable').DataTable({
            "ajax": {
                "url": "{{ route('getToPrint') }}", // URL API untuk mengambil data
                "type": "GET",
                "beforeSend": function() {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function() {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function() {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function() {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                }
            },
            "columns": [{
                    "data": "tanggal_berangkat",
                    "visible": false
                },
                {
                    "data": "karyawan.nama_lengkap"
                },
                {
                    "data": "karyawan.divisi"
                },
                {
                    "data": "karyawan.jabatan",
                    "visible": false
                },
                {
                    "data": "tipe"
                },
                {
                    "data": "tujuan"
                },
                {
                    "data": "alasan"
                },
                {
                    "data": null,
                    "render": function(data) {
                        return data.durasi + ' Hari';
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        moment.locale('id');
                        return moment(data.tanggal_berangkat).format('DD MMMM YYYY') + ' s/d ' + moment(data.tanggal_pulang).format('DD MMMM YYYY');
                    }
                },
                {
                    "data": null,
                    "render": function(data) {
                        if (data.approval_manager == '0' && data.approval_hrd == '0') {
                            return '<span class="badge bg-warning" style="color:black;"> Menunggu Persetujuan Manager Divisi </span>';
                        }

                        if (data.approval_manager == '1' && data.approval_hrd == '0') {
                            return '<span class="badge bg-warning"> Menunggu Rate dan Persetujuan HRD </span>';
                        }

                        if (data.approval_manager == '1' && data.approval_hrd == '1') {
                            if (data.tipe == "Internasional" && data.approval_direksi == '0') {
                                return '<span class="badge bg-warning"> Menunggu Persetujuan Direksi </span>';
                            } else if (data.tipe == "Internasional" && data.approval_direksi == '2') {
                                return '<span class="badge bg-danger"> Ditolak </span>';
                            } else {
                                return '<span class="badge bg-success"> Disetujui </span>';
                            }
                        }
                        if (data.approval_manager == '2') {
                            return '<span class="badge bg-danger"> Ditolak </span>';
                        }

                        return '<span class="badge bg-secondary"> Status Tidak Diketahui </span>';
                    },
                },
            ],
            "order": [
                [0, 'desc']
            ], // Ubah urutan menjadi descending untuk kolom ke-6
            "columnDefs": [{
                "targets": [0],
                "type": "date"
            }],
        });
    });

    function openApproveModal(id, jabatan) {
        // Set the action URL for the approval form
        //console.log(jabatan)
        if (jabatan === 'Manager') {
            $('#manager-row').show();
            $('#direksi-row').hide();
        } else if (jabatan === 'Direksi') {
            $('#manager-row').hide();
            $('#direksi-row').show();
        }
        var approveUrl = "{{ url('/suratperjalanan') }}/" + id + "/approval";
        $('#approveForm').attr('action', approveUrl);
        $('#approveModal').modal('show');
    }

    function toggleAlasanManager(show) {
        if (show) {
            document.getElementById('alasanManagerInput').style.display = 'block';
        } else {
            document.getElementById('alasanManagerInput').style.display = 'none';
            document.getElementById('alasan_manager').value = ''; // Clear the input if hidden
        }
    }

    function excelDownloadMonth() {
        const bulan = document.getElementById('bulan_select').value;

        if (!bulan || bulan === "Pilih Bulan") {
            alert("Silakan pilih bulan terlebih dahulu.");
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('getToExcelMonth') }}";
        form.style.display = 'none';

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = token;
        form.appendChild(csrf);

        const inputBulan = document.createElement('input');
        inputBulan.type = 'hidden';
        inputBulan.name = 'bulan';
        inputBulan.value = bulan;
        form.appendChild(inputBulan);

        document.body.appendChild(form);
        form.submit();

        setTimeout(() => {
            document.body.removeChild(form);
        }, 1000);
    }

    function excelDownloadYear() {

        const tahun = document.getElementById('tahun_Select').value;

        if (!tahun || tahun === "Pilih Tahun") {
            alert("Silakan pilih Tahun terlebih dahulu.");
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('getToExcelYear') }}";
        form.style.display = 'none';

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = token;
        form.appendChild(csrf);

        const inputTahun = document.createElement('input');
        inputTahun.type = 'hidden';
        inputTahun.name = 'tahun';
        inputTahun.value = tahun;
        form.appendChild(inputTahun);

        document.body.appendChild(form);
        form.submit();

        setTimeout(() => {
            document.body.removeChild(form);
        }, 1000);
    }

    function pdfDownloadMonth() {
        var bulan = document.getElementById('bulan_select').value;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('getToPdfMonth') }}";
        form.style.display = 'none';

        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = token;
        form.appendChild(csrf);

        var inputBulan = document.createElement('input');
        inputBulan.type = 'hidden';
        inputBulan.name = 'bulan';
        inputBulan.value = bulan;
        form.appendChild(inputBulan);

        document.body.appendChild(form);
        form.submit();

        setTimeout(() => {
            document.body.removeChild(form);
        }, 1000);
    }

    function pdfDownloadYear() {
        var tahun = document.getElementById('tahun_Select').value;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('getToPdfYear') }}";
        form.style.display = 'none';

        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = token;
        form.appendChild(csrf);

        var inputTahun = document.createElement('input');
        inputTahun.type = 'hidden';
        inputTahun.name = 'tahun';
        inputTahun.value = tahun;
        form.appendChild(inputTahun);

        document.body.appendChild(form);
        form.submit();

        setTimeout(() => {
            document.body.removeChild(form);
        }, 1000);
    }
</script>
<script>
    const tahunSelect = document.getElementById('tahun_Select');
    const startYear = 2024;
    const currentYear = new Date().getFullYear();

    for (let year = startYear; year <= currentYear; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        tahunSelect.appendChild(option);
    }
</script>
@endpush
@endsection