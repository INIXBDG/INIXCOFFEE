@extends('layouts.app')

@section('content')
<div class="container-fluid">
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
            <div class="d-flex justify-content-end">
                @if ( auth()->user()->jabatan == 'Technical Support' || auth()->user()->jabatan == 'Education Manager')
                    <a href="{{ route('listexams.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah User"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> List Exam</a>
                @endif
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('List Harga Exam') }}</h3>
                    <table class="table table-striped w-100" id="listexamtable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Provider</th>
                                <th scope="col">Vendor</th>
                                <th scope="col">Nama Exam</th>
                                <th scope="col">Kode Exam</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Status Data</th>
                                <th scope="col">Last Update</th>
                                <th scope="col">Estimasi Durasi Booking</th>
                                {{-- <th scope="col">sales</th> --}}
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>

<script>
    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan}}';
        var tableIndex1 = 1;
        var tableIndex2 = 1;

        function formatCurrencyIntl(value, currency) {
            const map = {
                Rupiah: { locale: 'id-ID', currency: 'IDR' },
                Dollar: { locale: 'en-US', currency: 'USD' },
                Euro: { locale: 'de-DE', currency: 'EUR' },
                Poundsterling: { locale: 'en-GB', currency: 'GBP' },
                'Franc Swiss': { locale: 'de-CH', currency: 'CHF' }
            };

            const cfg = map[currency];
            if (!cfg) return value;

            return new Intl.NumberFormat(cfg.locale, {
                style: 'currency',
                currency: cfg.currency,
                minimumFractionDigits: 0
            }).format(value);
        }

        $('#listexamtable').DataTable({
            "scrollX": true,
            "ajax": {
                "url": "{{ route('getListExam') }}", // URL API untuk mengambil data
                "type": "GET",
                "beforeSend": function () {
                    $('#loadingModal').modal('show');
                    $('#loadingModal').on('show.bs.modal', function () {
                        $('#loadingModal').removeAttr('inert');
                    });
                },
                "complete": function () {
                    setTimeout(() => {
                        $('#loadingModal').modal('hide');
                        $('#loadingModal').on('hidden.bs.modal', function () {
                            $('#loadingModal').attr('inert', true);
                        });
                    }, 1000);
                }
            },
            "columns": [
                {   "data": null,
                    "render": function (data){
                        return tableIndex1++
                    }
                },
                {"data": "provider"},
                {"data": "vendor"},
                {"data": "nama_exam"},
                {"data": "kode_exam"},
                {
                    "data": "harga_exam",
                    "render": function (data, type, row) {
                        if (!data) return '-';

                        return `<div>${formatCurrencyIntl(data, row.mata_uang)}</div>`;
                    }
                },
                {
                    "data": "valid_until",
                    "render": function(data, type, row) {
                        if(!data) {
                            return `-`;
                        }

                        const tanggalSekarang = new Date().setHours(0,0,0,0);
                        const validUntil = new Date(data).setHours(0,0,0,0);

                        return validUntil < tanggalSekarang
                            ? '<span class="badge bg-warning py-2">Expired</span>'
                            : '<span class="badge bg-success py-2">Valid</span>';
                    }
                },
                {
                    "data": 'updated_at',
                    "render": data =>
                        data
                            ? new Date(data).toLocaleDateString('id-ID')
                            : '-'
                },
                {"data": "estimasi_durasi_booking"},
                // {
                //     "data": null,
                //     "render": function (data, type, row) {
                //         if (data.tanggal_awal && data.tanggal_akhir) {
                //             var tanggalAwal = moment(data.tanggal_awal).format('LL'); // Format Tanggal dalam Bahasa Indonesia
                //             var tanggalAkhir = moment(data.tanggal_akhir).format('LL'); // Format Tanggal dalam Bahasa Indonesia
                //             return tanggalAwal + " s/d " + tanggalAkhir;
                //         } else {
                //             return "";
                //         }
                //     }
                // },
                // {"data": "perusahaan.nama_perusahaan"},
                // {"data": "pax"},
                // {
                //         "data": "sales_key",
                //         "visible": false
                // },
                {
                    "data": "valid_until",
                    "render": function(data, type, row) {

                        const tanggalSekarang = new Date().setHours(0,0,0,0);
                        const validUntil = new Date(data).setHours(0,0,0,0);

                        var actions = "";
                                actions += '<div class="btn">';
                                    if (validUntil < tanggalSekarang) {
                                        actions += '<a class="dropdown-item bg-warning rounded-2" disabled href="{{ url('/pengajuanUpdateExam') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Edit List Exam"><img src="{{ asset('icon/send.svg') }}" class="">Minta Update</a>';
                                    } else {
                                        actions += '<a class="dropdown-item" disabled href="{{ url('/detailHargaExam') }}/' + row.id + '" data-toggle="tooltip" data-placement="top" title="Edit List Exam"><img src="{{ asset('icon/file-text.svg') }}" class="">Detail</a>';
                                    }
                                actions += '</div>';
                        return actions;
                    }
                }

            ]
        });
    });
</script>
@endpush
@endsection
