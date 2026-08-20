@extends('layout_HR.app')

@section('content_HR')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <div id="report-generator-app" class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('HR.reports.index') }}" class="text-decoration-none">Report Generator</a>
                </li>
                <li class="breadcrumb-item active">Riwayat Generate</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <span class="iconify me-2" data-icon="mdi:history"></span>Riwayat Generate
                    </h5>
                    <a href="{{ route('HR.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                        <span class="iconify me-1" data-icon="mdi:arrow-left"></span>Kembali ke Daftar
                    </a>
                </div>

                <!-- Folder View -->
                <div id="folderView">
                    <div class="row g-3" id="folderList">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2 text-muted">Memuat folder...</span>
                        </div>
                    </div>
                </div>

                <!-- File View (Hidden by default) -->
                <div id="fileView" style="display: none;">
                    <div class="d-flex align-items-center mb-3">
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="showFolderView()">
                            <span class="iconify me-1" data-icon="mdi:arrow-left"></span>Kembali
                        </button>
                        <h6 class="mb-0" id="currentFolderName">
                            <span class="iconify me-2 text-warning" data-icon="mdi:folder-open"></span>
                            <span id="folderNameText"></span>
                        </h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%">Tanggal</th>
                                    <th width="35%">Judul Laporan</th>
                                    <th width="20%">Sumber Data</th>
                                    <th width="15%">User</th>
                                    <th width="10%">Status</th>
                                    <th width="5%" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="fileList">
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="spinner-border spinner-border-sm text-primary"></div>
                                        <span class="ms-2 text-muted">Memuat file...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="iconify me-2" data-icon="mdi:file-eye-outline"></span>
                        <span id="previewTitle">Preview Dokumen</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    <div id="previewLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3 text-muted">Memuat preview...</p>
                    </div>
                    <div id="previewContent" style="display: none; height: 100%;"></div>
                    <div id="previewError" class="text-center py-5" style="display: none;">
                        <span class="iconify text-danger" data-icon="mdi:alert-circle" style="font-size: 3rem;"></span>
                        <p class="mt-3 text-danger">Gagal memuat preview</p>
                        <a id="downloadLink" href="#" class="btn btn-primary" target="_blank">
                            <span class="iconify me-1" data-icon="mdi:download"></span>Download File
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="downloadBtn" href="#" class="btn btn-primary" target="_blank">
                        <span class="iconify me-1" data-icon="mdi:download"></span>Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit History -->
    <div class="modal fade" id="editHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span class="iconify me-2" data-icon="mdi:pencil-box-outline"></span>Edit
                        Riwayat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_history_id">
                    <input type="hidden" id="edit_template_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Laporan</label>
                        <input type="text" id="edit_report_title" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEditHistory"
                        onclick="executeEditHistory()">
                        <span class="iconify me-1" data-icon="mdi:content-save"></span>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Delete History -->
    <div class="modal fade" id="deleteHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-dark">
                    <h5 class="modal-title"><span class="iconify me-2" data-icon="mdi:alert-circle"></span>Konfirmasi
                        Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <span class="iconify text-danger" data-icon="mdi:trash-can-outline"
                            style="font-size: 48px;"></span>
                    </div>
                    <p class="mb-2">Apakah Anda yakin ingin menghapus riwayat:</p>
                    <h5 class="text-danger fw-bold text-center" id="delete_history_title"></h5>
                    <div class="alert alert-warning small mt-3 mb-0">
                        <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. File laporan juga akan dihapus
                        permanen.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <span class="iconify me-1" data-icon="mdi:close"></span>Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDeleteHistory"
                        onclick="executeDeleteHistory()">
                        <span class="iconify me-1" data-icon="mdi:delete"></span>Ya, Hapus Riwayat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.3.0/dist/docx-preview.min.js"></script>
    <script>
        let allHistoryData = {};
        // ✅ PERBAIKAN 1: Tambah variabel untuk track folder yang sedang dibuka
        let currentFolderKey = null;

        $(function() {
            loadAllHistory();
        });

        function loadAllHistory() {
            $.get('{{ route('HR.reports.history.data') }}', function(data) {
                if (data.success && data.data) {
                    // ✅ PERBAIKAN 2: Hapus duplikasi - hanya 1 blok ini saja
                    allHistoryData = {};

                    data.data.forEach(function(item) {
                        const templateKey = item.template_id || 'unknown';
                        const templateName = item.template_name || 'Template Tidak Diketahui';

                        if (!allHistoryData[templateKey]) {
                            allHistoryData[templateKey] = {
                                name: templateName,
                                files: []
                            };
                        }

                        allHistoryData[templateKey].files.push(item);
                    });

                    renderFolders();

                    // ✅ PERBAIKAN 3: Jika sedang di tampilan file, re-render agar data edit/hapus langsung update
                    if ($('#fileView').is(':visible') && currentFolderKey && allHistoryData[currentFolderKey]) {
                        renderFiles(allHistoryData[currentFolderKey].files);
                    } else if ($('#fileView').is(':visible') && currentFolderKey && !allHistoryData[
                            currentFolderKey]) {
                        // Kalau folder sudah kosong (semua history dihapus), kembali ke folder view
                        showFolderView();
                    }
                } else {
                    $('#folderList').html(
                        '<div class="col-12 text-center py-5 text-muted">Belum ada riwayat generate</div>');
                }
            }).fail(function() {
                $('#folderList').html(
                    '<div class="col-12 text-center py-5 text-danger">Gagal memuat riwayat</div>');
            });
        }

        function renderFolders() {
            let html = '';
            const folderKeys = Object.keys(allHistoryData);

            if (folderKeys.length === 0) {
                html = '<div class="col-12 text-center py-5 text-muted">Belum ada riwayat generate</div>';
            } else {
                folderKeys.forEach(function(key) {
                    const folder = allHistoryData[key];
                    const fileCount = folder.files.length;

                    html += `
                        <div class="col-md-3 col-lg-2">
                            <div class="card h-100 border-0 shadow-sm folder-card" onclick="openFolder('${key}')" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <span class="iconify text-warning" data-icon="mdi:folder" style="font-size: 4rem;"></span>
                                    </div>
                                    <h6 class="card-title text-truncate mb-2" title="${folder.name}">${folder.name}</h6>
                                    <small class="text-muted">${fileCount} file</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            $('#folderList').html(html);

            if (window.Iconify) {
                Iconify.renderSVG();
            }
        }

        function openFolder(templateKey) {
            const folder = allHistoryData[templateKey];
            if (!folder) return;

            // ✅ PERBAIKAN 4: Simpan key folder yang sedang dibuka
            currentFolderKey = templateKey;

            $('#folderNameText').text(folder.name);
            $('#folderView').hide();
            $('#fileView').show();

            renderFiles(folder.files);
        }

        function showFolderView() {
            // ✅ PERBAIKAN 5: Reset currentFolderKey saat kembali ke folder view
            currentFolderKey = null;
            $('#fileView').hide();
            $('#folderView').show();
        }

        function renderFiles(files) {
            let html = '';

            if (files.length === 0) {
                html = '<tr><td colspan="6" class="text-center py-5 text-muted">Folder kosong</td></tr>';
            } else {
                files.forEach(function(item) {
                    let badgeClass = 'bg-warning bg-opacity-10 text-warning';
                    let statusText = 'Pending';
                    if (item.status === 'completed') {
                        badgeClass = 'bg-success bg-opacity-10 text-success';
                        statusText = 'Sukses';
                    } else if (item.status === 'failed') {
                        badgeClass = 'bg-danger bg-opacity-10 text-danger';
                        statusText = 'Gagal';
                    }

                    const ext = (item.file_extension || '').toUpperCase();

                    let fileIcon = 'mdi:file-document-outline';
                    let fileColor = 'text-secondary';
                    let btnOutline = 'secondary';

                    if (ext === 'DOCX') {
                        fileIcon = 'mdi:microsoft-word';
                        fileColor = 'text-primary';
                        btnOutline = 'primary';
                    } else if (ext === 'PDF') {
                        fileIcon = 'mdi:file-pdf-box';
                        fileColor = 'text-danger';
                        btnOutline = 'danger';
                    }

                    html += `
                        <tr>
                            <td><small class="text-nowrap">${item.created_at}</small></td>
                            <td>
                                <small class="text-truncate d-block" style="max-width:300px" title="${item.report_title}">
                                    ${item.report_title}
                                </small>
                            </td>
                            <td>
                                <small class="text-uppercase fw-semibold">${item.source_type}</small>
                                <div class="text-muted small mt-1">
                                    <span class="iconify me-1" data-icon="mdi:account" style="font-size: 14px;"></span>
                                    ${item.source_name || 'ID: ' + item.source_id}
                                </div>
                            </td>
                            <td><small>${item.user_name || '-'}</small></td>
                            <td><span class="badge ${badgeClass}">${statusText}</span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="previewFile('${item.id}', '${(item.report_title||'').replace(/'/g,"\\'")}', '{{ route('HR.reports.preview', ':id') }}'.replace(':id', '${item.id}'), '${ext}')"
                                    title="Preview">
                                    <span class="iconify" data-icon="mdi:eye"></span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="openDeleteHistory(${item.id}, '${(item.report_title||'').replace(/'/g,"\\'")}')"
                                    title="Hapus">
                                    <span class="iconify" data-icon="mdi:delete"></span>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }

            $('#fileList').html(html);

            if (window.Iconify) {
                Iconify.renderSVG();
            }
        }

        function previewFile(fileId, title, downloadUrl, extension) {
            console.log('Preview file:', {
                fileId,
                title,
                downloadUrl,
                extension
            });

            const modal = new bootstrap.Modal(document.getElementById('previewModal'));

            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewLoading').style.display = 'block';
            document.getElementById('previewContent').style.display = 'none';
            document.getElementById('previewError').style.display = 'none';
            document.getElementById('downloadBtn').href = downloadUrl;
            document.getElementById('downloadLink').href = downloadUrl;

            modal.show();

            if (extension === 'DOCX') {
                if (!window.JSZip) {
                    console.error('JSZip library not loaded!');
                    showPreviewError('Library JSZip tidak tersedia. Refresh halaman.');
                    return;
                }
                if (!window.docx || typeof window.docx.renderAsync !== 'function') {
                    console.error('docx-preview library not loaded!');
                    showPreviewError('Library docx-preview tidak tersedia. Refresh halaman.');
                    return;
                }
            }

            fetch(downloadUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.arrayBuffer();
                })
                .then(arrayBuffer => {
                    console.log('File received:', arrayBuffer.byteLength, 'bytes');
                    document.getElementById('previewLoading').style.display = 'none';
                    const previewContainer = document.getElementById('previewContent');
                    previewContainer.style.display = 'block';
                    previewContainer.innerHTML = '';

                    if (extension === 'DOCX') {
                        console.log('Rendering DOCX...');
                        const docxContainer = document.createElement('div');
                        docxContainer.className = 'docx-container-preview';
                        previewContainer.appendChild(docxContainer);

                        window.docx.renderAsync(arrayBuffer, docxContainer, null, {
                                className: 'docx',
                                inWrapper: true,
                                ignoreWidth: false,
                                ignoreHeight: false,
                                breakPages: true,
                            })
                            .then(() => {
                                console.log('DOCX rendered successfully');
                            })
                            .catch(err => {
                                console.error('DOCX preview error:', err);
                                showPreviewError('Gagal render DOCX: ' + err.message);
                            });
                    } else if (extension === 'PDF') {
                        const blob = new Blob([arrayBuffer], {
                            type: 'application/pdf'
                        });
                        const url = URL.createObjectURL(blob);
                        const iframe = document.createElement('iframe');
                        iframe.src = url;
                        iframe.style.width = '100%';
                        iframe.style.height = '100%';
                        iframe.style.border = 'none';
                        previewContainer.appendChild(iframe);
                    } else {
                        showPreviewError('Format file tidak didukung: ' + extension);
                    }
                })
                .catch(error => {
                    console.error('Preview error:', error);
                    showPreviewError('Gagal memuat file: ' + error.message);
                });
        }

        function showPreviewError(message = 'Gagal memuat preview') {
            document.getElementById('previewLoading').style.display = 'none';
            document.getElementById('previewContent').style.display = 'none';
            document.getElementById('previewError').style.display = 'block';
            document.getElementById('previewError').querySelector('p').textContent = message;
        }

        // ============ EDIT HISTORY ============
        function openEditHistory(id, title, templateId) {
            document.getElementById('edit_history_id').value = id;
            document.getElementById('edit_report_title').value = title;
            document.getElementById('edit_template_id').value = templateId;

            const btn = document.getElementById('btnSaveEditHistory');
            btn.disabled = false;
            btn.innerHTML = '<span class="iconify me-1" data-icon="mdi:content-save"></span>Simpan';

            new bootstrap.Modal(document.getElementById('editHistoryModal')).show();
            if (window.Iconify) Iconify.renderSVG();
        }

        function executeEditHistory() {
            const id = document.getElementById('edit_history_id').value;
            const title = document.getElementById('edit_report_title').value.trim();
            const btn = document.getElementById('btnSaveEditHistory');
            const originalText = btn.innerHTML;

            if (!title) {
                showToast('Judul laporan wajib diisi!', 'error');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

            fetch(`/HR-dashboard/reports/history/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        report_title: title
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editHistoryModal')).hide();
                        showToast(data.message, 'success');
                        // ✅ PERBAIKAN 6: Panggil loadAllHistory() - otomatis re-render file yang sedang terbuka
                        setTimeout(() => {
                            loadAllHistory();
                        }, 800);
                    } else {
                        showToast(data.message || 'Gagal update', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(() => {
                    showToast('Error koneksi', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }

        // ============ DELETE HISTORY ============
        let pendingDeleteId = null;

        function openDeleteHistory(id, title) {
            pendingDeleteId = id;
            document.getElementById('delete_history_title').textContent = `"${title}"`;

            const btn = document.getElementById('btnConfirmDeleteHistory');
            btn.disabled = false;
            btn.innerHTML = '<span class="iconify me-1" data-icon="mdi:delete"></span>Ya, Hapus Riwayat';

            new bootstrap.Modal(document.getElementById('deleteHistoryModal')).show();
            if (window.Iconify) Iconify.renderSVG();
        }

        function executeDeleteHistory() {
            const btn = document.getElementById('btnConfirmDeleteHistory');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';

            fetch(`/HR-dashboard/reports/history/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('deleteHistoryModal')).hide();
                        showToast(data.message, 'success');
                        // Panggil loadAllHistory() - otomatis re-render file yang sedang terbuka atau kembali ke folder view jika folder kosong
                        setTimeout(() => {
                            loadAllHistory();
                        }, 800);
                    } else {
                        showToast(data.message || 'Gagal hapus', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(() => {
                    showToast('Error koneksi', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }

        // ============ TOAST ============
        function showToast(message, type = 'success') {
            const c = document.getElementById('toastContainer') || (() => {
                const d = document.createElement('div');
                d.id = 'toastContainer';
                d.className = 'toast-container position-fixed top-0 end-0 p-3';
                d.style.zIndex = '1100';
                document.body.appendChild(d);
                return d;
            })();
            const bg = type === 'error' ? 'bg-danger' : 'bg-success';
            const ic = type === 'error' ? 'mdi:alert-circle' : 'mdi:check-circle';
            c.insertAdjacentHTML('beforeend', `
                <div class="toast align-items-center text-white border-0 ${bg}" role="alert">
                    <div class="d-flex">
                        <div class="toast-body"><span class="iconify me-2" data-icon="${ic}"></span>${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`);
            const t = c.lastElementChild;
            new bootstrap.Toast(t, {
                delay: 3500
            }).show();
            t.addEventListener('hidden.bs.toast', () => t.remove());
            if (window.Iconify) Iconify.renderSVG();
        }
    </script>

    <style>
        .folder-card:hover {
            transform: translateY(-2px);
            transition: transform 0.2s;
        }

        .folder-card:hover .iconify {
            color: #ffc107 !important;
        }

        #previewContent {
            overflow-y: auto;
            background: #f8f9fa;
        }

        .docx-container-preview {
            background: #f8f9fa;
            padding: 20px;
            min-height: 100%;
        }

        .docx-container-preview .docx-wrapper {
            background: #f8f9fa !important;
            padding: 0;
        }

        .docx-container-preview .docx-wrapper>section.docx {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background: white;
            padding: 40px;
        }
    </style>
@endsection
