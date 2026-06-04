@extends('layout_HR.app')

@section('content_HR')
    <div id="report-generator-app" class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('HR.reports.index') }}" class="text-decoration-none">Report Generator</a>
                </li>
                <li class="breadcrumb-item">
                    <a
                        href="{{ route('HR.reports.index', ['template_id' => $template->id, 'view' => 'index']) }}">{{ $template->name }}</a>
                </li>
                <li class="breadcrumb-item active">Edit Template</li>
            </ol>
        </nav>

        <div class="row">
            <!-- Kolom Kiri: Informasi Dasar -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <span class="iconify me-2" data-icon="mdi:cog"></span>Pengaturan Dasar
                        </h5>

                        <form id="formEditTemplate" action="{{ route('HR.reports.update', $template) }}" method="POST">
                            @csrf @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Nama Template</label>
                                <input type="text" name="name" class="form-control" value="{{ $template->name }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3">{{ $template->description }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" {{ $template->is_active ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ !$template->is_active ? 'selected' : '' }}>Non-Aktif</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">File Template Saat Ini</label>
                                <div class="p-2 bg-light border rounded d-flex align-items-center">
                                    <span class="iconify me-2 text-primary" data-icon="mdi:microsoft-word"></span>
                                    <span
                                        class="flex-grow-1 text-truncate">{{ basename($template->template_file_path) }}</span>
                                    <a href="{{ asset('storage/' . $template->template_file_path) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary ms-2">Download</a>
                                </div>
                                <small class="text-muted d-block mt-1">Untuk mengganti file, buat template baru.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kategori & Sumber</label>
                                <input type="text" class="form-control mb-2"
                                    value="Kategori: {{ ucfirst($template->category) }}" readonly>
                                <input type="text" class="form-control"
                                    value="Tabel: {{ ucfirst($template->source_table) }}" readonly>
                            </div>

                            <div class="d-grid gap-2 pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <span class="iconify me-2" data-icon="mdi:content-save"></span>Simpan Pengaturan
                                </button>
                                <a href="{{ route('HR.reports.index', ['template_id' => $template->id]) }}"
                                    class="btn btn-secondary">
                                    <span class="iconify me-2" data-icon="mdi:arrow-left"></span>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Manajemen Placeholder / Field -->
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0">
                            <span class="iconify me-2" data-icon="mdi:form-textbox"></span>Kelola Field & Placeholder
                        </h5>
                        <button type="button" class="btn btn-sm btn-success" onclick="openPlaceholderModal()">
                            <span class="iconify me-1" data-icon="mdi:plus"></span> Tambah Field Manual
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info small mb-3">
                            <strong>Info:</strong> Field di bawah ini diekstrak dari file DOCX Anda atau ditambahkan secara
                            manual.
                            Pastikan <strong>Source Column</strong> sesuai dengan nama kolom di database agar data terisi
                            otomatis.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="placeholderTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Placeholder Key</th>
                                        <th>Label Tampilan</th>
                                        <th>Source Column (DB)</th>
                                        <th>Tipe</th>
                                        <th>Manual?</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="placeholderTableBody">
                                    @forelse ($placeholders as $ph)
                                        <tr data-id="{{ $ph->id }}">
                                            <td><code class="text-primary">{{ $ph->placeholder_key }}</code></td>
                                            <td>{{ $ph->placeholder_label }}</td>
                                            <td>
                                                @if ($ph->is_manual)
                                                    <span class="badge bg-secondary">N/A (Manual)</span>
                                                @else
                                                    <span class="badge bg-info text-dark">{{ $ph->source_column }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $icons = [
                                                        'text' => 'mdi:format-text',
                                                        'textarea' => 'mdi:format-text-rotation-none',
                                                        'date' => 'mdi:calendar',
                                                        'select' => 'mdi:menu-down',
                                                        'checkbox' => 'mdi:checkbox-marked',
                                                        'number' => 'mdi:numeric',
                                                        'currency' => 'mdi:currency-idr',
                                                    ];
                                                @endphp
                                                <span class="iconify me-1"
                                                    data-icon="{{ $icons[$ph->field_type] ?? 'mdi:help' }}"></span>
                                                {{ ucfirst($ph->field_type) }}
                                            </td>
                                            <td>
                                                @if ($ph->is_manual)
                                                    <span class="iconify text-warning" data-icon="mdi:check-circle"></span>
                                                    Ya
                                                @else
                                                    <span class="iconify text-muted" data-icon="mdi:close-circle"></span>
                                                    Tidak
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="editPlaceholder({{ $ph->id }})" title="Edit">
                                                    <span class="iconify" data-icon="mdi:pencil"></span>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deletePlaceholder({{ $ph->id }}, '{{ $ph->placeholder_key }}')"
                                                    title="Hapus">
                                                    <span class="iconify" data-icon="mdi:delete"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                Belum ada field yang dikonfigurasi. Tambahkan field manual atau upload ulang
                                                template DOCX.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Placeholder -->
    <div class="modal fade" id="placeholderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Field Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPlaceholder">
                    <div class="modal-body">
                        <input type="hidden" name="placeholder_id" id="placeholder_id">
                        <input type="hidden" name="template_id" value="{{ $template->id }}">

                        <div class="mb-3">
                            <label class="form-label">Placeholder Key *</label>
                            <input type="text" name="placeholder_key" id="placeholder_key" class="form-control"
                                placeholder="contoh: catatan_tambahan" pattern="^[a-z0-9_]+$" required>
                            <small class="text-muted">Huruf kecil, angka, dan underscore saja. Tanpa spasi.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Label Tampilan *</label>
                            <input type="text" name="placeholder_label" id="placeholder_label" class="form-control"
                                placeholder="contoh: Catatan Tambahan" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Data *</label>
                            <select name="field_type" id="field_type" class="form-select" required>
                                <option value="text">Text (Pendek)</option>
                                <option value="textarea">Textarea (Panjang)</option>
                                <option value="date">Tanggal</option>
                                <option value="number">Angka</option>
                                <option value="currency">Mata Uang (Rupiah)</option>
                                <option value="select">Dropdown (Pilihan)</option>
                                <option value="checkbox">Checkbox (Ya/Tidak)</option>
                            </select>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_manual" id="is_manual"
                                value="1">
                            <label class="form-check-label" for="is_manual">Ini adalah Field Manual (Input oleh user saat
                                generate)</label>
                        </div>

                        <div class="mb-3" id="source_column_group">
                            <label class="form-label">Source Column (Dari Database)</label>
                            <select name="source_column" id="source_column" class="form-select">
                                <option value="">-- Tidak Ada (Kosong) --</option>
                                @foreach ($availableColumns as $col)
                                    <option value="{{ $col }}">{{ $col }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih kolom database yang nilainya akan mengisi placeholder
                                ini.</small>
                        </div>

                        <div class="mb-3" id="options_group" style="display: none;">
                            <label class="form-label">Opsi Dropdown (Pisahkan dengan koma)</label>
                            <input type="text" name="options" id="options" class="form-control"
                                placeholder="contoh: Opsi 1, Opsi 2, Opsi 3">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nilai Default (Opsional)</label>
                            <input type="text" name="default_value" id="default_value" class="form-control"
                                placeholder="Nilai awal saat form generate">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSavePlaceholder">Simpan Field</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer" style="z-index: 1100;"></div>

    <script>
        $(function() {
            // 1. Simpan Pengaturan Dasar Template
            $('#formEditTemplate').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const original = btn.html();

                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success !== false) {
                            showToast('Pengaturan template berhasil diperbarui', 'success');
                        } else {
                            showToast(res.message || 'Gagal menyimpan', 'error');
                        }
                        btn.prop('disabled', false).html(original);
                    },
                    error: function() {
                        showToast('Terjadi kesalahan koneksi', 'error');
                        btn.prop('disabled', false).html(original);
                    }
                });
            });

            // 2. Logika Modal Placeholder
            const modalEl = document.getElementById('placeholderModal');
            const modal = new bootstrap.Modal(modalEl);
            let isEditing = false;

            // Toggle field berdasarkan tipe dan status manual
            $('#is_manual').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#source_column').prop('disabled', true).val('');
                } else {
                    $('#source_column').prop('disabled', false);
                }
            });

            $('#field_type').on('change', function() {
                if ($(this).val() === 'select') {
                    $('#options_group').show();
                } else {
                    $('#options_group').hide();
                    $('#options').val('');
                }
            });

            window.openPlaceholderModal = function() {
                isEditing = false;
                $('#modalTitle').text('Tambah Field Manual');
                $('#formPlaceholder')[0].reset();
                $('#placeholder_id').val('');
                $('#placeholder_key').prop('readonly', false).val('');
                $('#source_column').prop('disabled', false);
                $('#options_group').hide();
                modal.show();
            };

            window.editPlaceholder = function(id) {
                isEditing = true;
                $('#modalTitle').text('Edit Field');

                const row = $(`tr[data-id="${id}"]`);
                const key = row.find('td:eq(0) code').text();
                const label = row.find('td:eq(1)').text();
                const type = row.find('td:eq(3)').text().trim().toLowerCase();
                const isManual = row.find('td:eq(4)').text().includes('Ya');
                const sourceCol = isManual ? '' : row.find('td:eq(2) .badge').text();

                $('#placeholder_id').val(id);
                $('#placeholder_key').val(key).prop('readonly', true); // Key tidak bisa diedit
                $('#placeholder_label').val(label);
                $('#field_type').val(type).trigger('change');
                $('#is_manual').prop('checked', isManual).trigger('change');

                if (!isManual) {
                    $('#source_column').val(sourceCol);
                }

                modal.show();
            };

            // 3. Simpan / Update Placeholder via AJAX
            $('#formPlaceholder').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btnSavePlaceholder');
                const original = btn.html();
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

                const id = $('#placeholder_id').val();
                const url = isEditing ?
                    `{{ route('HR.reports.placeholder.update', ':id') }}`.replace(':id', id) :
                    `{{ route('HR.reports.placeholder.add') }}`;

                const type = isEditing ? 'POST' :
                    'POST'; // Update menggunakan POST dengan _method PUT di Laravel, tapi kita bisa handle di route atau kirim _method

                let formData = new FormData(this);
                if (isEditing) {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            showToast(res.message, 'success');
                            modal.hide();
                            setTimeout(() => location.reload(),
                                1000); // Reload untuk refresh tabel dengan data baru
                        } else {
                            showToast(res.message || 'Gagal menyimpan field', 'error');
                            btn.prop('disabled', false).html(original);
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                        showToast(msg, 'error');
                        btn.prop('disabled', false).html(original);
                    }
                });
            });

            // 4. Hapus Placeholder
            window.deletePlaceholder = function(id, key) {
                if (!confirm(`Yakin ingin menghapus field "${key}"? Tindakan ini tidak dapat dibatalkan.`))
                    return;

                $.ajax({
                    url: `{{ route('HR.reports.placeholder.delete', ':id') }}`.replace(':id', id),
                    type: 'POST',
                    data: {
                        _method: 'DELETE'
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            showToast('Field berhasil dihapus', 'success');
                            $(`tr[data-id="${id}"]`).fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            showToast('Gagal menghapus field', 'error');
                        }
                    },
                    error: function() {
                        showToast('Terjadi kesalahan koneksi', 'error');
                    }
                });
            };

            // Fungsi Toast
            function showToast(msg, type = 'success') {
                const bgClass = type === 'error' ? 'bg-danger' : 'bg-success';
                const toast = $(`
                        <div class="toast align-items-center text-white border-0 ${bgClass}" role="alert">
                            <div class="d-flex">
                                <div class="toast-body">${msg}</div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>
                    `);
                $('#toastContainer').append(toast);
                new bootstrap.Toast(toast[0], {
                    delay: 4000
                }).show();
                toast.on('hidden.bs.toast', () => toast.remove());
            }
        });
    </script>
@endsection
