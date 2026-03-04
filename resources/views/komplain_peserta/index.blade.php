@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
            <div class="card-body">
                <div class="text-end mb-4">
                    <a href="{{ route('createKomplain') }}" class="btn btn-primary px-2"><i class="fa-solid fa-plus"></i> Buat</a>
                </div>
                
                <div id="content">
                    <div class="card">
                        <div class="card-body mt-3 table-responsive">
                            <h3 class="card-title text-center mb-4 ps-4">{{ __('Komplain Peserta') }}</h3>
                            <table id="komplainTable" class="table table-striped w-100 text-capitalize">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Komplain</th>
                                        <th>Kategori</th>
                                        <th>Feedback</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Status</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Aksi</th>
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
</div>
@endsection
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function () {
        var tableIndex1 = 1;

        $('#komplainTable').DataTable({
            "scrollX": true,
            "ajax": {
                "url": "{{ route('dataKomplain') }}",
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
                {
                    data: "komplain",
                    render: function (data) {
                        return data.map((item, i) => `${item}`).join('<br><hr style="margin: 8px 0 8px 0;">');
                    }
                },
                {
                    data: "kategori_feedback",
                    render: function (data) {
                        return data.map((item, i) => `${item}`).join('<br><hr style="margin: 8px 0 8px 0;">');
                    }
                },
                {"data": "detail_feedback"},
                {
                    data: "created_at",
                    render: function (data, type, row) {
                        if (!data) return '-';

                        const date = new Date(data);
                        return date.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        });
                    }
                },
                {
                    data: "status",
                    render: function (data) {
                        if (!data) return '-';

                        let statuses = Array.isArray(data) ? data : [data];

                        return statuses.map(item => {
                            if (item === 'on progress') {
                                return `<span class="badge bg-primary py-2">On Progress</span>`;
                            } else if (item === 'completed') {
                                return `<span class="badge bg-success py-2">Completed</span>`;
                            } else {
                                return `<span class="badge bg-warning py-2">Delayed</span>`;
                            }
                        }).join('<br><hr style="margin: 8px 0 8px 0;">');
                    }
                },
                {
                    data: "tanggal_selesai",
                    render: function (data) {
                        if (!data) return '-';

                        let dates = Array.isArray(data) ? data : [data];

                        return dates.map(item => {
                            return item ? item : '-';
                        }).join('<br><hr style="margin: 8px 0 8px 0;">');
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        var actions = "";
                                actions += '<div class="dropdown">';
                                actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>';
                                actions += '<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
                                actions += '<a class="dropdown-item" disabled href="{{ url('/komplain-peserta') }}/' + row.id + '/edit" data-toggle="tooltip" data-placement="top" title="Edit List Komplain"><img src="{{ asset('icon/edit-warning.svg') }}" class=""> Edit</a>';
                                actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/komplain-peserta/delete') }}/' + row.id + '" method="POST">';
                                actions += '@csrf';
                                actions += '@method('POST')';
                                actions += '<button type="submit"  class="dropdown-item"><img src="{{ asset('icon/trash-danger.svg') }}" class=""> Hapus</button>';
                                actions += '</form>';
                                actions += '</div>';
                                actions += '</div>';
                        return actions;
                    }
                }
            ]
        })
    });
</script>
@endpush