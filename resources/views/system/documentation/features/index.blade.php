@extends('system.documentation.layout')

@section('title', 'Feature Documentation')

@section('content')
    <div class="page-header animate-fade-in">
        <div>
            <h1 class="header-title"><i class="fas fa-layer-group"></i> Feature Documentation</h1>
            <p class="header-subtitle">Kelola dokumentasi fitur sistem Anda</p>
        </div>
        <button class="btn-primary-custom" onclick="openModal()"><i class="fas fa-plus"></i> Tambah Fitur</button>
    </div>

    <div class="stats-grid animate-fade-in"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card"
            style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow-soft); border-left: 4px solid var(--primary-navy);">
            <h3 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">{{ $features->total() }}
            </h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">Total Features</p>
        </div>
        <div class="stat-card"
            style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow-soft); border-left: 4px solid #48bb78;">
            <h3 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                {{ $features->where('status', 'production')->count() }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">Production</p>
        </div>
        <div class="stat-card"
            style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow-soft); border-left: 4px solid #ed8936;">
            <h3 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                {{ $features->where('status', 'development')->count() }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">Development</p>
        </div>
        <div class="stat-card"
            style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: var(--shadow-soft); border-left: 4px solid #e63946;">
            <h3 style="font-size: 2rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                {{ $features->where('status', 'draft')->count() }}</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">Draft</p>
        </div>
    </div>

    <div class="content-card animate-fade-in"
        style="background: white; border-radius: 16px; box-shadow: var(--shadow-soft); overflow: hidden;">
        <div
            style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-soft); display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
            <h5
                style="font-size: 1.1rem; font-weight: 600; color: var(--primary-navy); margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-list"></i> Daftar Fitur
            </h5>
            <input type="text" class="form-control-custom" placeholder="Cari fitur..." style="width: 250px;"
                id="searchInput">
        </div>

        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0;" id="featuresTable">
                <thead>
                    <tr>
                        <th
                            style="background: #f8f9fa; padding: 1rem 1.5rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border-soft);">
                            Nama Fitur</th>
                        <th
                            style="background: #f8f9fa; padding: 1rem 1.5rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border-soft);">
                            Kategori</th>
                        <th
                            style="background: #f8f9fa; padding: 1rem 1.5rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border-soft);">
                            Status</th>
                        <th
                            style="background: #f8f9fa; padding: 1rem 1.5rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border-soft); text-align: center;">
                            Manual Book</th>
                        <th
                            style="background: #f8f9fa; padding: 1rem 1.5rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border-soft); text-align: center;">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($features as $feature)
                        <tr style="transition: all 0.2s ease;">
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-soft);">
                                <div style="font-weight: 600; color: var(--primary-navy); font-size: 0.95rem;">
                                    {{ $feature->name }}</div>
                                <div style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">
                                    {{ Str::limit($feature->short_description, 60) }}</div>
                            </td>
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-soft);">
                                <span class="badge bg-light text-dark"
                                    style="padding: 0.5rem 1rem; border-radius: 8px; font-weight: 500; border: 1px solid var(--border-soft);">{{ $feature->category }}</span>
                            </td>
                            <td style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-soft);">
                                <span class="badge"
                                    style="background: rgba(30, 58, 95, 0.1); color: var(--primary-navy); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                    {{ ucfirst($feature->status) }}
                                </span>
                            </td>
                            <td
                                style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-soft); text-align: center;">
                                <a href="{{ route('documentation.features.manual', $feature->id) }}" class="btn btn-sm"
                                    style="background: #e63946; color: white; border-radius: 8px; font-size: 0.8rem; text-decoration: none;"
                                    target="_blank">
                                    <i class="fas fa-file-pdf me-1"></i> Download
                                </a>
                            </td>
                            <td
                                style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-soft); text-align: center;">
                                <button class="btn btn-sm btn-light me-1" onclick="editFeature({{ $feature->id }})"
                                    title="Edit"><i class="fas fa-edit text-primary"></i></button>
                                <a href="{{ route('documentation.codes.index', $feature->id) }}"
                                    class="btn btn-sm btn-light me-1" title="Code Docs"><i
                                        class="fas fa-code text-success"></i></a>
                                <button class="btn btn-sm btn-light" onclick="deleteFeature({{ $feature->id }})"
                                    title="Hapus"><i class="fas fa-trash text-danger"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                style="text-align: center; padding: 4rem 2rem; color: var(--text-secondary);">
                                <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                Belum ada dokumentasi fitur
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($features->hasPages())
            <div class="p-4" style="border-top: 1px solid var(--border-soft);">{{ $features->links() }}</div>
        @endif
    </div>

    <!-- Modal Form Lengkap -->
    <div class="modal fade" id="featureModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title modal-title-custom" id="modalTitle"><i class="fas fa-plus-circle"></i> Tambah
                        Feature Documentation</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModal()"></button>
                </div>
                <form id="featureForm">
                    @csrf
                    <div class="modal-body modal-body-custom">
                        <input type="hidden" id="featureId" name="id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Nama Fitur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control-custom" id="name" name="name"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control-custom" id="category" name="category"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Status <span class="text-danger">*</span></label>
                            <select class="form-select-custom" id="status" name="status" required>
                                <option value="draft">Draft</option>
                                <option value="development">Development</option>
                                <option value="production">Production</option>
                                <option value="deprecated">Deprecated</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Deskripsi Singkat <span class="text-danger">*</span></label>
                            <textarea class="form-control-custom" id="short_description" name="short_description" rows="2" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Tujuan <span class="text-danger">*</span></label>
                            <textarea class="form-control-custom" id="purpose" name="purpose" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Latar Belakang</label>
                                <textarea class="form-control-custom" id="background" name="background" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Masalah yang Diselesaikan</label>
                                <textarea class="form-control-custom" id="problem_solved" name="problem_solved" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Cara Kerja</label>
                                <textarea class="form-control-custom" id="how_it_works" name="how_it_works" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">Hak Akses Pengguna</label>
                                <textarea class="form-control-custom" id="user_access" name="user_access" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-custom">
                        <button type="button" class="btn-secondary-custom" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-primary-custom"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openModal() {
            $('#modalTitle').html('<i class="fas fa-plus-circle"></i> Tambah Feature Documentation');
            $('#featureForm')[0].reset();
            $('#featureId').val('');
            $('#featureModal').modal('show');
        }

        function closeModal() {
            $('#featureModal').modal('hide');
        }

        function editFeature(id) {
            $.ajax({
                url: `/system/documentation/features/${id}`,
                method: 'GET',
                success: function(response) {
                    $('#modalTitle').html('<i class="fas fa-edit"></i> Edit Feature Documentation');
                    $('#featureId').val(response.id);
                    $('#name').val(response.name);
                    $('#category').val(response.category);
                    $('#status').val(response.status);
                    $('#short_description').val(response.short_description);
                    $('#purpose').val(response.purpose);
                    $('#background').val(response.background || '');
                    $('#problem_solved').val(response.problem_solved || '');
                    $('#how_it_works').val(response.how_it_works || '');
                    $('#user_access').val(response.user_access || '');
                    $('#featureModal').modal('show');
                }
            });
        }

        function deleteFeature(id) {
            if (confirm('Apakah Anda yakin ingin menghapus dokumentasi ini?')) {
                $.ajax({
                    url: `/system/documentation/features/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) location.reload();
                    }
                });
            }
        }

        $('#featureForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = $('#featureId').val();
            const url = id ? `/system/documentation/features/${id}?_method=PUT` : '/system/documentation/features';

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) location.reload();
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan saat menyimpan data. Silakan cek kembali input Anda.');
                }
            });
        });

        $('#searchInput').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#featuresTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    </script>
@endpush
