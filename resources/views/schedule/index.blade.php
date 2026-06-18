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
    <div class="container-fluid">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4>Log Eksekusi Scheduler</h4>
                    
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-danger mx-4" id="btnClearAll">
                            Clear All
                        </button>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <table class="table table-bordered" id="tableSchedule" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th>Nama Perintah</th>
                                        <th>Status</th>
                                        <th>Pesan Galat</th>
                                        <th>Tanggal Eksekusi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Data diisi secara asinkron melalui AJAX DataTables --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function(){
        var userRole = '{{ auth()->user()->jabatan }}';

        var table = $('#tableSchedule').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("schedule.data") }}',
                type: 'GET'
            },
            columns: [
                {
                    data: null,
                    searchable: false,
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { 
                    data: 'command_name',
                    render: function (data, type, row) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'status',
                    render: function (data, type, row) {
                        if (!row || !row.status) return '-';
                        
                        if (row.status === 'Success') {
                            return '<span class="badge bg-success" style="background-color: #198754; color: #fff; padding: 5px 10px; border-radius: 4px;">Success</span>';
                        } else {
                            return '<span class="badge bg-danger" style="background-color: #dc3545; color: #fff; padding: 5px 10px; border-radius: 4px;">Failed</span>';
                        }
                    }
                },
                { 
                    data: 'error_message',
                    render: function (data, type, row) {
                        if (!row) return '-';
                        return data ? '<span class="text-danger">' + data + '</span>' : '-';
                    }
                },
                { 
                    data: 'execution_date',
                    render: function (data, type, row) {
                        return data ? data : '-';
                    }
                }
            ],
            order: [[4, 'desc']]
        });

        $('#btnClearAll').on('click', function () {
            if (confirm('Apakah Anda yakin ingin menghapus seluruh log eksekusi?')) {
                $.ajax({
                    url: '{{ route("schedule.clear") }}',
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        alert('Gagal menghapus log eksekusi.');
                    }
                });
            }
        });
    });
</script>
@endsection