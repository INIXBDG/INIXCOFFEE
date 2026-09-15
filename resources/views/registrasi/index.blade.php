@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
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
        {{-- <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a> --}}
        <div class="col-md-12">
            <div class="d-flex justify-content-end">
                @can('Create Registrasi')
                    <a href="{{ route('registrasi.create') }}" class="btn btn-md click-primary mx-4" data-toggle="tooltip" data-placement="top" title="Tambah registrasi"><img src="{{ asset('icon/plus.svg') }}" class="" width="30px"> Registrasi Peserta</a>
                @endcan
                @if (auth()->user()->jabatan == 'Customer Care' || auth()->user()->jabatan == 'Admin Holding' || auth()->user()->jabatan == 'Education Manager' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Koordinator Office' || auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'Adm Sales' || auth()->user()->jabatan == 'GM')
                    <a href="{{ route('registrasi.exportExcel') }}" class="btn btn-success">Export to Excel</a>
                    <a href="{{ route('registrasi.exportPDF') }}" class="btn btn-danger">Export to PDF</a>
                @endif
                @if (auth()->user()->jabatan == 'Sales' || auth()->user()->jabatan == 'Instruktur')
                    <a href="{{ route('registrasi.exportExcels') }}" class="btn btn-success">Export to Excel</a>
                    <a href="{{ route('registrasi.exportPDFs') }}" class="btn btn-danger">Export to PDF</a>
                @endif
            </div>
            <div class="card m-4">
                <div class="card-body table-responsive">
                    <h3 class="card-title text-center my-1">{{ __('Data Registrasi') }}</h3>
                    <table class="table table-striped" id="registrasitable">
                        <thead>
                          <tr>
                            <th scope="col">Nama Peserta</th>
                            <th scope="col">Perusahaan/Instansi</th>
                            <th scope="col">Materi Pelatihan</th>
                            <th scope="col">Periode Pelatihan</th>
                            <th scope="col">Instruktur</th>
                            <th scope="col">Sales</th>
                            <th scope="col">Souvenir</th>
                            <th scope="col">created_at</th>
                            <th scope="col">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalTambahSouvenir" tabindex="-1" aria-labelledby="modalTambahSouvenirLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('registrasi.storeSouvenir') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahSouvenirLabel">Isi Data Souvenir</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Input Hidden untuk relasi tabel -->
                            <input type="hidden" name="id_regist" id="souvenir_id_regist">
                            <input type="hidden" name="id_rkm" id="souvenir_id_rkm">

                            <div class="mb-3">
                                <label for="id_souvenir" class="form-label">Pilih Souvenir</label>
                                <select class="form-select" name="id_souvenir" id="id_souvenir" required>
                                    <option value="" selected disabled>-- Pilih Souvenir --</option>
                                    <!-- Pastikan variabel $listSouvenir di-passing dari method index controller -->
                                    @foreach($listSouvenir as $souvenir)
                                        <option value="{{ $souvenir->id }}">{{ $souvenir->nama_souvenir }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
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
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    function addSouvenir(idRegist, idRkm) {
        $('#souvenir_id_regist').val(idRegist);
        $('#souvenir_id_rkm').val(idRkm);
        $('#modalTambahSouvenir').modal('show');
    }

    $(document).ready(function(){
        $('#registrasitable').DataTable({
            "processing": true,
            "serverSide": true, // Aktifkan Server-Side Processing
            "dom": 'Bfrtip',
            "buttons": [
                // (Konfigurasi tombol export Anda tetap sama)
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    exportOptions: { columns: [ 0, 1, 2, 3, 4, 5 ] },
                    filename: 'Inixindo E-office Data Registrasi',
                },
                {
                    extend: 'pdf',
                    text: 'Export to PDF',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                    filename: 'Inixindo E-office Data Registrasi',
                    customize: function(doc) {
                        doc.content[1].table.widths = ['*', '*', '*', '*', '*', '*'];
                        doc.content.splice(0, 1, {
                            text: 'Inixindo E-Office Data Peserta',
                            fontSize: 12, alignment: 'center', margin: [0, 0, 0, 12]
                        });
                    }
                }
            ],
            "ajax": {
                "url": "{{ route('getRegistrasiall') }}",
                "type": "GET",
                // "beforeSend": function () {
                //     $('#loadingModal').modal('show');
                // },
                // "complete": function () {
                //     setTimeout(() => { $('#loadingModal').modal('hide'); }, 500);
                // }
            },
            "columns": [
                { "data": "nama_peserta" },
                { "data": "nama_perusahaan" },
                { "data": "nama_materi" },
                { "data": "periode" },
                { "data": "id_instruktur" },
                { "data": "id_sales" },
                {
                    "data": "nama_souvenir",
                    "render": function(data, type, row) {
                        if (data) {
                            return data;
                        } else {
                            var actionBtn = '-';
                            @can('Edit Registrasi')
                            actionBtn = '<button type="button" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 12px;" onclick="addSouvenir(' + row.id + ', ' + row.id_rkm + ')">+ Souvenir</button>';
                            @endcan
                            return actionBtn;
                        }
                    },
                    "orderable": false
                },
                { "data": "created_at", "visible": false },
                {
                    "data": "id",
                    "render": function(data, type, row) {
                        var actions = "";
                        @if (auth()->user()->can('Edit Registrasi') || auth()->user()->can('Delete Registrasi'))
                            actions += '<div class="dropdown">';
                            actions += '<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">Actions</button>';
                            actions += '<div class="dropdown-menu">';
                            @can('Edit Registrasi')
                            actions += '<a class="dropdown-item" href="{{ url('/registrasi') }}/' + row.id + '/edit">Edit</a>';
                            @endcan
                            @can('Delete Registrasi')
                            actions += '<form onsubmit="return confirm(\'Apakah Anda Yakin ?\');" action="{{ url('/registrasi') }}/' + row.id + '" method="POST">@csrf @method("DELETE") <button type="submit" class="dropdown-item">Hapus</button></form>';
                            @endcan
                            actions += '</div></div>';
                        @else
                            actions += '<button class="btn dropdown-toggle disabled" type="button">Actions</button>';
                        @endif
                        return actions;
                    },
                    "orderable": false
                },
            ],
            "order": [[7, 'desc']] // Urutkan default berdasarkan created_at
        });
    });
</script>
@endpush
@endsection
