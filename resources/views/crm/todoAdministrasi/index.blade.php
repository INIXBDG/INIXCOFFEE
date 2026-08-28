@extends('layouts_crm.app')

@section('crm_contents')
<div class="container mt-3">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-4">
                <div>
                    <h3 class="mb-2 fw-bold text-dark">Data Todo List</h3>
                    <p class="text-muted fs-6 mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createTodoModal">
        <i class="bx bx-plus"></i> Tambah ToDo
    </button>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('todo-administrasi.index') }}" id="filterTodoForm">
                <div class="container-fluid">
                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label for="filterTodoTahun" class="mb-1 ms-1">Tahun</label>
                            <select id="filterTodoTahun" name="tahun" class="form-select filter-dropdown" data-filter="tahun">
                                <option value="default" disabled selected>Berdasarkan Tahun</option>
                                @php
                                    $tahun_sekarang = now()->year;
                                    for ($tahun = 2023; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                        echo "<option value=\"$tahun\">$tahun</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="filterTodoBulan" class="mb-1 ms-1">Bulan</label>
                            <select id="filterTodoBulan" name="bulan" class="form-select filter-dropdown" data-filter="bulan">
                                <option value="default" disabled selected>Berdasarkan Bulan</option>
                                @php
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        echo "<option value=\"$bulan\">{$nama_bulan[$bulan - 1]}</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="filterTodoTriwulan" class="mb-1 ms-1">Triwulan</label>
                            <select id="filterTodoTriwulan" name="triwulan" class="form-select filter-dropdown" data-filter="triwulan">
                                <option value="default" disabled selected>Berdasarkan Triwulan</option>
                                <option value="1">Quarter 1</option>
                                <option value="2">Quarter 2</option>
                                <option value="3">Quarter 3</option>
                                <option value="4">Quarter 4</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="mb-1 ms-1">&nbsp;</label>
                            <button type="button" class="btn btn-outline-primary w-100" id="customRangeBtn">
                                Custom Range
                            </button>
                        </div>
                    </div>
                    
                    <!-- Custom Range Section -->
                    <div id="customRangeSection" class="row g-3 mt-1">
                        <div class="col-lg-4">
                            <label for="filterTodoStartDate" class="mb-1 ms-1">Dari Tanggal</label>
                            <input type="date" id="filterTodoStartDate" name="start_date" class="form-control" disabled>
                        </div>
                        <div class="col-lg-4">
                            <label for="filterTodoEndDate" class="mb-1 ms-1">Sampai Tanggal</label>
                            <input type="date" id="filterTodoEndDate" name="end_date" class="form-control" disabled>
                        </div>
                        <div class="col-lg-4">
                            <label class="mb-1 ms-1">&nbsp;</label>
                            <button type="button" class="btn btn-success w-100" id="submitCustomRangeBtn" disabled>
                                <i class="bx bx-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0 fw-bold">Daftar Todo List</h5>
                    <span id="dateRangeDisplay" class="text-muted fs-6"></span>
                </div>
                <div class="d-flex gap-2">
                    Progres : <span class="badge bg-warning">{{ $todos->where('status', 'progres')->count() }}</span>
                    Selesai : <span class="badge bg-success">{{ $todos->where('status', 'selesai')->count() }}</span>
                    Gagal : <span class="badge bg-danger">{{ $todos->where('status', 'gagal')->count() }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>No</th>
                            <th>Case</th>
                            <th>Solution</th>
                            <th>Keterangan</th>
                            <th>Tanggal Dibuat</th>
                            <th>Tanggal Selesai</th>
                            <th>Dokumen</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($todos as $index => $todo)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $todo->case }}</td>
                            <td>{{ $todo->solusi ?? '-' }}</td>
                            <td>{{ $todo->catatan ?? '-' }}</td>
                            <td>{{ $todo->created_at ? $todo->created_at->format('d-m-Y') : '-' }}</td>
                            <td>{{ $todo->tanggal_selesai ? \Carbon\Carbon::parse($todo->tanggal_selesai)->format('d-m-Y') : '-' }}</td>
                            <td>
                                @if ($todo->dokumen)
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDokumen('{{ asset('storage/' . $todo->dokumen) }}', '{{ $todo->case }}')">
                                        Lihat Dokumen
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge 
                                    @if($todo->status == 'selesai') bg-success
                                    @elseif($todo->status == 'progres') bg-warning
                                    @else bg-danger
                                    @endif">
                                    {{ ucfirst($todo->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editTodo({{ $todo->id }}, '{{ $todo->case }}', '{{ $todo->solusi ?? '' }}', '{{ $todo->status }}', '{{ $todo->catatan ?? '' }}', '{{ $todo->tanggal_selesai ?? '' }}', '{{ $todo->dokumen ?? '' }}')" >Edit</button>
                                <form id="deleteForm{{ $todo->id }}" action="{{ route('todo-administrasi.delete', $todo->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $todo->id }})">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $todos->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Create --}}
    <div class="modal fade" id="createTodoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Todo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('todo-administrasi.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Case <span class="text-danger">*</span></label>
                                <input type="text" name="case" class="form-control" required>                                
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="catatan" class="form-control" rows="3"></textarea>
                            </div>
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

    {{-- Modal Edit --}}
    <div class="modal fade" id="editTodoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Todo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTodoForm" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Case <span class="text-danger">*</span></label>
                                <input type="text" name="case" id="editCase" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Solution</label>
                                <textarea name="solusi" id="editSolusi" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="editStatus" class="form-control" required>
                                    <option value="progres">Progres</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="gagal">Gagal</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="catatan" id="editCatatan" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="row col-md-12 mb-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" id="editTanggalSelesai" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Dokumen Saat Ini</label>
                                    <div id="currentDokumenContainer" class="mb-2">
                                        <small class="text-muted">Tidak ada dokumen</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Ganti Dokumen</label>
                                <input type="file" name="dokumen" id="editDokumen" class="form-control">
                                <small class="text-muted">Max 10 MB</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Lihat Dokumen --}}
    <div class="modal fade bg-transparent" id="viewDokumenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-body text-center" id="dokumenContent" style="min-height: 300px;">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus todo ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .preview-image {
            border: 2px solid transparent; 
            border-radius: 10px;       
            padding: 5px;              
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        let deleteTodoId = null;

        function confirmDelete(id) {
            deleteTodoId = id;
            $('#confirmDeleteModal').modal('show');
        }

        function editTodo(id, caseText, solusi, status, catatan, tanggalSelesai, dokumen) {
            $('#editCase').val(caseText);
            $('#editSolusi').val(solusi);
            $('#editStatus').val(status);
            $('#editCatatan').val(catatan);
            $('#editTanggalSelesai').val(tanggalSelesai);
            $('#editTodoForm').attr('action', '{{ route("todo-administrasi.update", ":id") }}'.replace(':id', id));
            
            const dokumenContainer = $('#currentDokumenContainer');
            if (dokumen && dokumen !== '') {
                const dokumenUrl = '{{ asset("storage/") }}' + '/' + dokumen;
                dokumenContainer.html(
                    '<button type="button" class="btn btn-sm btn-outline-primary" onclick="viewDokumen(\'' + dokumenUrl + '\', \'' + caseText.replace(/'/g, "\\'") + '\')">' +
                    '<i class="bx bx-show"></i> Lihat Dokumen Saat Ini' +
                    '</button>'
                );
            } else {
                dokumenContainer.html('<small class="text-muted">Tidak ada dokumen</small>');
            }
            
            new bootstrap.Modal($('#editTodoModal')[0]).show();
        }

        function viewDokumen(dokumenUrl, title) {
            const fileName = dokumenUrl.split('/').pop();
            const fileExt = fileName.split('.').pop().toLowerCase();

            $('#dokumenTitle').text(title);

            const container = $('#dokumenContent');

            container.html('');

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
                container.html(
                    '<img class="preview-image" src="' + dokumenUrl + '" style="max-width:100%; max-height:80vh; object-fit:contain;" />'
                );
            }
            else if (fileExt === 'pdf') {
                container.html(
                    '<iframe class="preview-image" src="' + dokumenUrl + '" width="100%" height="700rem" style="border:none;"></iframe>'
                );
            }
            else {
                container.html(
                    '<p class="text-muted text-white">Preview tidak tersedia</p>' +
                    '<a href="' + dokumenUrl + '" target="_blank" class="btn btn-primary">Download File</a>'
                );
            }

            new bootstrap.Modal($('#viewDokumenModal')[0]).show();
        }

        $(document).ready(function () {

            // Handle filter dropdown changes
            $('.filter-dropdown').on('change', function() {
                const filterType = $(this).data('filter');
                applyTodoFilter(filterType);
            });

            $(document).on('click', '#confirmDeleteBtn', function () {
                if (!deleteTodoId) return;
                $('#deleteForm' + deleteTodoId).submit();
            });

            // Handle custom range toggle
            $('#customRangeBtn').on('click', function(e) {
                e.preventDefault();
                const $customBtn = $(this);
                const $startDate = $('#filterTodoStartDate');
                const $endDate = $('#filterTodoEndDate');
                const $submitBtn = $('#submitCustomRangeBtn');
                const isDisabled = $startDate.prop('disabled');
                
                if (isDisabled) {
                    // Enable custom range inputs
                    $startDate.prop('disabled', false);
                    $endDate.prop('disabled', false);
                    $submitBtn.prop('disabled', false);
                    $customBtn.removeClass('btn-outline-primary').addClass('btn-primary');
                    
                    // Reset other filters
                    $('#filterTodoTahun').val('default');
                    $('#filterTodoBulan').val('default');
                    $('#filterTodoTriwulan').val('default');
                    
                    // Focus on start date
                    $startDate.focus();
                } else {
                    // Disable custom range inputs
                    $startDate.prop('disabled', true);
                    $endDate.prop('disabled', true);
                    $submitBtn.prop('disabled', true);
                    $customBtn.removeClass('btn-primary').addClass('btn-outline-primary');
                    
                    // Clear date inputs
                    $startDate.val('');
                    $endDate.val('');
                }
            });

            // Handle custom range submit
            $('#submitCustomRangeBtn').on('click', function(e) {
                e.preventDefault();
                const $startDate = $('#filterTodoStartDate');
                const $endDate = $('#filterTodoEndDate');
                
                if ($startDate.val() && $endDate.val()) {
                    submitCustomFilter();
                }
            });

            // Update date range display on date change
            $('#filterTodoStartDate, #filterTodoEndDate').on('change', function() {
                const $startDate = $('#filterTodoStartDate');
                const $endDate = $('#filterTodoEndDate');
                
                if ($startDate.val() && $endDate.val()) {
                    const startFormatted = new Date($startDate.val()).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                    const endFormatted = new Date($endDate.val()).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                    $('#dateRangeDisplay').text('(' + startFormatted + ' - ' + endFormatted + ')');
                } else if ($startDate.val() || $endDate.val()) {
                    $('#dateRangeDisplay').text('(Tanggal belum lengkap)');
                }
            });

        });

        function applyTodoFilter(filterType) {
            const $form = $('#filterTodoForm');
            const $tahunSelect = $('#filterTodoTahun');
            const $bulanSelect = $('#filterTodoBulan');
            const $triwulanSelect = $('#filterTodoTriwulan');
            const $customBtn = $('#customRangeBtn');
            const $startDate = $('#filterTodoStartDate');
            const $endDate = $('#filterTodoEndDate');
            const $submitBtn = $('#submitCustomRangeBtn');
            
            // Disable custom range inputs
            $startDate.prop('disabled', true);
            $endDate.prop('disabled', true);
            $submitBtn.prop('disabled', true);
            $startDate.val('');
            $endDate.val('');
            $customBtn.removeClass('btn-primary').addClass('btn-outline-primary');
            
            // Clear date range display
            $('#dateRangeDisplay').text('');
            
            // Reset other dropdowns
            if (filterType === 'tahun') {
                $bulanSelect.val('default');
                $triwulanSelect.val('default');
            } else if (filterType === 'bulan') {
                $tahunSelect.val('default');
                $triwulanSelect.val('default');
            } else if (filterType === 'triwulan') {
                $tahunSelect.val('default');
                $bulanSelect.val('default');
            }
            
            // Get selected value
            let selectedValue = '';
            if (filterType === 'tahun') {
                selectedValue = $tahunSelect.val();
            } else if (filterType === 'bulan') {
                selectedValue = $bulanSelect.val();
            } else if (filterType === 'triwulan') {
                selectedValue = $triwulanSelect.val();
            }
            
            // Submit only if value is selected
            if (selectedValue && selectedValue !== 'default') {
                // Add or update filter_type hidden input
                let $filterTypeInput = $form.find('input[name="filter_type"]');
                if ($filterTypeInput.length === 0) {
                    $form.append('<input type="hidden" name="filter_type" value="' + filterType + '">');
                } else {
                    $filterTypeInput.val(filterType);
                }
                
                $form.submit();
            }
        }

        function submitCustomFilter() {
            const $form = $('#filterTodoForm');
            const $startDate = $('#filterTodoStartDate');
            const $endDate = $('#filterTodoEndDate');
            
            if ($startDate.val() && $endDate.val()) {
                // Update date range display
                const startFormatted = new Date($startDate.val()).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                const endFormatted = new Date($endDate.val()).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                $('#dateRangeDisplay').text('(' + startFormatted + ' - ' + endFormatted + ')');
                
                // Add or update filter_type hidden input
                let $filterTypeInput = $form.find('input[name="filter_type"]');
                if ($filterTypeInput.length === 0) {
                    $form.append('<input type="hidden" name="filter_type" value="custom">');
                } else {
                    $filterTypeInput.val('custom');
                }
                
                $form.submit();
            }
        }
    </script>

</div>

@endsection
