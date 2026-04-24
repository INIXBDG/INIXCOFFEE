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
        <div class="col-md-12 d-flex my-2 justify-content-end">
        </div>
        <div class="col-md-12">
            <div class="card m-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <h3 class="card-title text-center my-1">{{ __('Daftar Peserta Exam') }}</h3>
                        <table class="table table-striped table-bordered nowrap w-100" id="daftarPesertaExamTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Peserta</th>
                                    <th scope="col">Nama Materi</th>
                                    <th scope="col">Skor</th>
                                    <th scope="col">Keterangan Lulus</th>
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
    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script>
    function editData(id) {
        window.location.href = `{{ url('daftar-peserta-exam') }}/${id}/edit`;
    }

    function deleteData(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
           window.location.href = `/listexam/delete/${id}`;
        }
    }

    $(document).ready(function(){
        loadData();
    });

    function loadData() {
        $('#loadingModal').modal('show');

        $.ajax({
            url: "{{ route('daftar-peserta-exam.get-data') }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#loadingModal').modal('hide');

                if (response.success) {
                    let html = '';

                    if (response.data.length > 0) {
                        $.each(response.data, function(index, item) {

                            html += `
                                <tr>
                                    <td>${item.no}</td>
                                    <td>${item.nama_peserta}</td>
                                    <td>${item.nama_exam}</td>
                                    <td>
                                        ${
                                            item.skor !== '-' && item.skor !== null
                                            ? item.skor
                                            : ( item.dokumentasi
                                                ? `<a href="/storage/${item.dokumentasi}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i> Lihat File
                                                </a>`
                                                : '-'
                                            )
                                        }
                                    </td>
                                    <td class="text-capitalize">
                                        <span class="badge ${item.keterangan_lulus === 'lulus' ? 'bg-success' : (item.keterangan_lulus === 'Belum Exam' ? 'bg-warning' : 'bg-danger')}">
                                            ${item.keterangan_lulus}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-primary btn-sm" onclick="editData(${item.id})">
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html += `<tr><td colspan="6" class="text-center text-muted">Tidak ada data peserta exam</td></tr>`;
                    }

                    $('#daftarPesertaExamTable tbody').html(html);

                    if ($.fn.DataTable.isDataTable('#daftarPesertaExamTable')) {
                        $('#daftarPesertaExamTable').DataTable().destroy();
                    }

                    $('#daftarPesertaExamTable').DataTable({
                        scrollX: true,
                        autoWidth: false,
                        pageLength: 10,
                        language: {
                            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                        }
                    });

                } else {
                    $('#daftarPesertaExamTable tbody').html(`<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>`);
                }
            },
            error: function() {
                $('#loadingModal').modal('hide');
                $('#daftarPesertaExamTable tbody').html(`<tr><td colspan="6" class="text-center text-danger">Terjadi kesalahan server</td></tr>`);
            }
        });
    }
</script>
@endpush
@endsection
