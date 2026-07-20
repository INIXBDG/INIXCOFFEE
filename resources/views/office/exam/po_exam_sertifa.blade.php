@extends('layouts_office.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('office_contents')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Data Exam Sertifa</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#poExamModal">Tambah Data</button>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" id="poExamTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Materi</th>
                                    <th>Tanggal Exam</th>
                                    <th>Perusahaan</th>
                                    <th>Pax</th>
                                    <th>Harga</th>
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

<div class="modal fade" id="poExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('office.certifa.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah PO Exam Sertifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">RKM</label>
                        <select name="id_rkm" id="id_rkm" class="form-select" required>
                            <option value="">- Pilih RKM -</option>
                            @foreach($rkms as $rkm)
                                <option value="{{ $rkm->id }}" 
                                        data-materi="{{ $rkm->materi_key }}" 
                                        data-perusahaan="{{ $rkm->perusahaan_key }}">
                                    {{ $rkm->id }} | {{ $rkm->materi->nama_materi ?? 'Materi Tidak Tersedia' }} - {{ $rkm->perusahaan->nama_perusahaan ?? 'Perusahaan Tidak Tersedia' }} | {{ $rkm->tanggal_awal ? $rkm->tanggal_awal->format('d M Y') : '-' }} s/d {{ $rkm->tanggal_akhir ? $rkm->tanggal_akhir->format('d M Y') : '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <input type="hidden" name="id_materi" id="id_materi" required>
                    <input type="hidden" name="id_perusahaan" id="id_perusahaan">
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Exam</label>
                        <input type="date" name="tanggal_exam" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pax</label>
                        <input type="number" name="pax" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="text" name="harga" id="harga" class="form-control">
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

<div class="modal fade" id="editPoExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditPoExam" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit PO Exam Sertifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">RKM</label>
                        <select name="id_rkm" id="edit_id_rkm" class="form-select" required>
                            <option value="">- Pilih RKM -</option>
                            @foreach($rkms as $rkm)
                                <option value="{{ $rkm->id }}" 
                                        data-materi="{{ $rkm->materi_key }}" 
                                        data-perusahaan="{{ $rkm->perusahaan_key }}">
                                    {{ $rkm->id }} | {{ $rkm->materi->nama_materi ?? 'Materi Tidak Tersedia' }} - {{ $rkm->perusahaan->nama_perusahaan ?? 'Perusahaan Tidak Tersedia' }} | {{ $rkm->tanggal_awal ? $rkm->tanggal_awal->format('d M Y') : '-' }} s/d {{ $rkm->tanggal_akhir ? $rkm->tanggal_akhir->format('d M Y') : '-' }}
                                </option>          
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="id_materi" id="edit_id_materi" required>
                    <input type="hidden" name="id_perusahaan" id="edit_id_perusahaan">
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Exam</label>
                        <input type="date" name="tanggal_exam" id="edit_tanggal_exam" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pax</label>
                        <input type="number" name="pax" id="edit_pax" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="text" name="harga" id="edit_harga" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Definisi fungsi untuk pemformatan string numerik menjadi nilai Rupiah
    function formatRupiah(angka) {
        if (!angka) return '';
        var number_string = angka.toString().replace(/[^,\d]/g, '');
        var split = number_string.split(',');
        var sisa = split[0].length % 3;
        var rupiah = split[0].substr(0, sisa);
        var ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    $(document).ready(function() {
        // Inisialisasi DataTables dengan pengambilan sumber data AJAX
        var table = $('#poExamTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('office.certifa.data') }}",
                type: "GET",
                dataSrc: "items" 
            },
            columns: [
                {
                    data: null,
                    searchable: false,
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'materi.nama_materi',
                    defaultContent: '-'
                },
                {
                    data: 'tanggal_exam',
                    render: function (data) {
                        if (!data) return '-';
                        var date = new Date(data);
                        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                    }
                },
                {
                    data: 'perusahaan.nama_perusahaan',
                    defaultContent: '-'
                },
                {
                    data: 'pax',
                    defaultContent: '-'
                },
                {
                    data: 'harga',
                    render: function (data) {
                        if (!data) return 'Rp 0';
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(data);
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        var updateUrl = "{{ route('office.certifa.update', ':id') }}".replace(':id', data);
                        var destroyUrl = "{{ route('office.certifa.destroy', ':id') }}".replace(':id', data);
                        var csrfToken = '{{ csrf_token() }}';

                        var btnEdit = `
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-url="${updateUrl}"
                                data-id_rkm="${row.id_rkm || ''}"
                                data-id_materi="${row.id_materi || ''}"
                                data-tanggal_exam="${row.tanggal_exam || ''}"
                                data-id_perusahaan="${row.id_perusahaan || ''}"
                                data-pax="${row.pax || ''}"
                                data-harga="${row.harga || ''}">
                                Edit
                            </button>
                        `;

                        var btnDelete = `
                            <form action="${destroyUrl}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        `;

                        return btnEdit + ' ' + btnDelete;
                    }
                }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            }
        });

        // Inisialisasi Select2 pada Modal Tambah Data
        $('#id_rkm').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#poExamModal'),
            width: '100%',
            placeholder: '- Pilih RKM -'
        });

        // Inisialisasi Select2 pada Modal Edit Data
        $('#edit_id_rkm').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#editPoExamModal'),
            width: '100%',
            placeholder: '- Pilih RKM -'
        });

        // Logika pengisian otomatis hidden input saat Select2 RKM diubah (Tambah Data)
        $('#id_rkm').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var idMateri = selectedOption.data('materi');
            var idPerusahaan = selectedOption.data('perusahaan');
            
            $('#id_materi').val(idMateri || '');
            $('#id_perusahaan').val(idPerusahaan || '');
        });

        // Logika pengisian otomatis hidden input saat Select2 RKM diubah (Edit Data)
        $('#edit_id_rkm').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var idMateri = selectedOption.data('materi');
            var idPerusahaan = selectedOption.data('perusahaan');
            
            $('#edit_id_materi').val(idMateri || '');
            $('#edit_id_perusahaan').val(idPerusahaan || '');
        });

        // Event listener format otomatis Rupiah pada pengetikan (keyup)
        $('#harga, #edit_harga').on('keyup', function() {
            $(this).val(formatRupiah($(this).val()));
        });

        // Event listener untuk memulihkan nilai string menjadi integer murni sebelum submit form
        $('#poExamModal form, #formEditPoExam').on('submit', function() {
            var inputHarga = $(this).find('input[name="harga"]');
            if (inputHarga.length > 0) {
                var rawValue = inputHarga.val().replace(/\./g, '');
                inputHarga.val(rawValue);
            }
        });

        // Event Delegate untuk pembukaan dan injeksi nilai ke dalam Modal Edit Data
        $('#poExamTable tbody').on('click', '.btn-edit', function () {
            var btn = $(this);
            
            $('#formEditPoExam').attr('action', btn.data('url'));
            
            // Pengaturan nilai Select2 untuk memicu pendengar kejadian 'change' pada hidden input
            $('#edit_id_rkm').val(btn.data('id_rkm')).trigger('change');
            
            $('#edit_tanggal_exam').val(btn.data('tanggal_exam'));
            $('#edit_pax').val(btn.data('pax'));
            
            // Injeksi dan manipulasi format data harga ke format Rupiah
            $('#edit_harga').val(formatRupiah(btn.data('harga')));
            
            $('#editPoExamModal').modal('show');
        });
    });
</script>
@endsection