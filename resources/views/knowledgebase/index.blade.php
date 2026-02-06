@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
    <style>
        .page-title {
            text-align: center;
            margin: 1.5rem 0 1rem;
            color: #1a3a6c;
            font-size: 2rem;
            font-weight: 600;
        }

        .add-button {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }

        .divisi-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 2rem;
            transition: transform 0.3s;
        }

        .divisi-container:hover {
            transform: translateY(-5px);
        }

        .divisi-header {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
            color: #2c5282;
            font-size: 1.8rem;
        }

        .subdivisi-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .subdivisi-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .subdivisi-title {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
            color: #334155;
            font-size: 1.2rem;
        }

        .document-title {
            text-align: center;
            margin: 15px 0;
            font-weight: 500;
            color: #1e293b;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 0.8rem;
        }

        .btn-success {
            background-color: #10b981;
            border: none;
        }

        .btn-primary {
            background-color: #3b82f6;
            border: none;
        }

        .btn-danger {
            background-color: #ef4444;
            border: none;
        }

        .download-button {
            width: 100%;
            background-color: gainsboro;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.2s;
        }

        .download-button:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .modal-header {
            color: black;
            border-radius: 10px 10px 0 0 !important;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.3rem;
        }

        .modal-footer {
            border-top: none;
            padding: 15px 20px;
        }

        .form-label {
            font-weight: 500;
            color: #334155;
        }

        .icon-action {
            width: 16px;
            height: 16px;
            object-fit: contain;
        }

        .btn-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            cursor: pointer;
        }

        .btn-edit {
            background-color: #facc15;
        }

        .btn-delete {
            background-color: #ef4444;
        }

        .btn-download {
            background-color: #3b82f6;
        }
    </style>

    <div class="container-fluid py-4">
        <h1 class="page-title">Knowledge Base</h1>

        @if(Auth::user()->jabatan === 'HRD')
            <div class="container-fluid py-3 position-relative">
                <button type="button" class="btn btn-primary rounded-5 shadow-sm px-4 py-2" data-bs-toggle="modal"
                    data-bs-target="#createModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah
                </button>
            </div>
        @endif

        @if($divisiGroups->isEmpty())
            <div class="empty-state">
                <i class="bi bi-inbox empty-icon"></i>
                <h3>Belum ada dokumen tersedia</h3>
                <p class="text-muted">Dokumen akan muncul di sini setelah ditambahkan</p>
                @if(Auth::user()->jabatan === 'HRD')
                    <button type="button" class="btn btn-primary mt-3 px-4 py-2" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Dokumen
                    </button>
                @endif
            </div>
        @else
            <!-- Group by Divisi -->
            <div class="row g-4">
                @foreach($divisiGroups as $divisi => $subdivisiGroups)
                    <div class="col-lg-6 col-md-12">
                        <div class="divisi-container">
                            <h2 class="divisi-header">{{ $divisi ?? 'Tanpa Divisi' }}</h2>

                            <div class="row">
                                @foreach($subdivisiGroups as $subdivisi => $documents)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="subdivisi-card">
                                            <h3 class="subdivisi-title">{{ $subdivisi ?? 'Tanpa Subdivisi' }}</h3>

                                            @foreach($documents as $doc)
                                                <div class="document-title">{{ $doc->title }}</div>

                                                <div class="action-buttons">
                                                    @if(Auth::user()->jabatan === 'HRD')
                                                        <button type="button" class="btn-circle btn-edit" title="Edit Dokumen"
                                                            onclick="openEditModal({{ $doc->id }}, '{{ addslashes($doc->divisi) }}', '{{ addslashes($doc->subdivisi) }}', '{{ addslashes($doc->title) }}')">
                                                            <img src="{{ asset('icon/edit.svg') }}" class="icon-action"> </button>

                                                        <button class="btn-circle btn-delete" onclick="deleteDocument(
                                                                    {{ $doc->id }},
                                                                    '{{ route('knowledgebase.destroy', $doc->id) }}'
                                                                )">
                                                            <img src="{{ asset('icon/trash.svg') }}">
                                                        </button>

                                                        <a href="{{ route('knowledgebase.download', $doc) }}" class="btn-circle btn-download">
                                                            <img src="{{ asset('icon/download.svg') }}">
                                                        </a>
                                                    @else
                                                        <a href="{{ route('knowledgebase.download', $doc) }}" class="download-button flex justify-content-center align-items-center">
                                                            Unduh pdf
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

        @endif
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('knowledgebase.store') }}"
                enctype="multipart/form-data" id="createForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Dokumen</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Divisi</label>
                        <select class="form-select" name="divisi" id="createDivisi" required onchange="updateCreateSubdivisiOptions(this.value)">
                            <option value="">Pilih Divisi</option>
                            <option value="Office">Office</option>
                            <option value="Sales">Sales</option>
                            <option value="Education">Education</option>
                            <option value="ITSM">ITSM</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <select class="form-select" name="subdivisi" id="createSubdivisi" required>
                            <option value="">Pilih Jabatan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input class="form-control" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <input type="file" class="form-control" name="file" accept=".pdf,.xls,.xlsx" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" enctype="multipart/form-data" id="editForm">
                @csrf
                @method('PUT')

                <!-- Input hidden untuk ID -->
                <input type="hidden" name="id" id="editId">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Dokumen</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Divisi</label>
                        <select class="form-select" name="divisi" id="editDivisi" required onchange="updateSubdivisiOptions(this.value)">
                            <option value="">Pilih Divisi</option>
                            <option value="Office">Office</option>
                            <option value="Sales">Sales</option>
                            <option value="Education">Education</option>
                            <option value="ITSM">ITSM</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <select class="form-select" name="subdivisi" id="editSubdivisi" required>
                            <option value="">Pilih Jabatan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input class="form-control" name="title" id="editTitle" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ganti File (opsional)</label>
                        <input type="file" class="form-control" name="file" accept=".pdf,.xls,.xlsx">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-warning" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Mapping jabatan per divisi
        const jabatanPerDivisi = {
            'Office': [
                'Komisaris', 'Direktur Utama', 'Direktur', 'Office Manager', 
                'Admin Holding', 'Accounting', 'Finance & Accounting', 
                'HRD', 'Customer Service', 'Costumer Care', 
                'Office Boy', 'Driver', 'Koordinator Office', 'GM', 'Outsource'
            ],
            'Sales': [
                'SPV Sales', 'Adm Sales', 'Sales',
            ],
            'Education': [
                'Education Manager', 'Instruktur'
            ],
            'ITSM': [
                'Technical Support', 'Programmer', 'Koordinator ITSM', 'Tim Digital'
            ],
        };

        // Update dropdown subdivisi berdasarkan divisi yang dipilih (Edit Modal)
        function updateSubdivisiOptions(divisi) {
            const subdivisiSelect = document.getElementById('editSubdivisi');
            subdivisiSelect.innerHTML = '<option value="">Pilih Jabatan</option>';
            
            if (divisi && jabatanPerDivisi[divisi]) {
                jabatanPerDivisi[divisi].forEach(jabatan => {
                    const option = document.createElement('option');
                    option.value = jabatan;
                    option.textContent = jabatan;
                    subdivisiSelect.appendChild(option);
                });
            }
        }

        // Update dropdown subdivisi berdasarkan divisi yang dipilih (Create Modal)
        function updateCreateSubdivisiOptions(divisi) {
            const subdivisiSelect = document.getElementById('createSubdivisi');
            subdivisiSelect.innerHTML = '<option value="">Pilih Jabatan</option>';
            
            if (divisi && jabatanPerDivisi[divisi]) {
                jabatanPerDivisi[divisi].forEach(jabatan => {
                    const option = document.createElement('option');
                    option.value = jabatan;
                    option.textContent = jabatan;
                    subdivisiSelect.appendChild(option);
                });
            }
        }

        // Fungsi untuk membuka modal edit
        function openEditModal(id, divisi, subdivisi, title) {
            console.log('Edit clicked:', { id, divisi, subdivisi, title });

            // Set nilai form
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editDivisi').value = divisi;
            
            // Trigger update subdivisi options
            updateSubdivisiOptions(divisi);
            
            // Set subdivisi setelah options diupdate
            setTimeout(() => {
                document.getElementById('editSubdivisi').value = subdivisi;
            }, 100);

            // Set form action
            document.getElementById('editForm').action = '/knowledgebase/' + id;

            // Show modal
            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        // Fungsi delete
        function deleteDocument(id, actionUrl) {
            console.log('Delete clicked:', { id, actionUrl });

            if (confirm('Yakin ingin menghapus dokumen ini?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = actionUrl;

                var csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                var methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Handle submit form
        document.addEventListener('DOMContentLoaded', function () {
            // Handle create form submit
            var createForm = document.getElementById('createForm');
            if (createForm) {
                createForm.addEventListener('submit', function () {
                    var submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menyimpan...';
                    }
                });
            }

            // Handle edit form submit
            var editForm = document.getElementById('editForm');
            if (editForm) {
                editForm.addEventListener('submit', function () {
                    var submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Memperbarui...';
                    }
                });
            }
        });
    </script>
@endpush