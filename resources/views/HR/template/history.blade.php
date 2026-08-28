@extends('layout_HR.app')

@section('content_HR')
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.3.0/dist/docx-preview.min.js"></script>
    <script>
        let allHistoryData = {};

        $(function() {
            loadAllHistory();
        });

        function loadAllHistory() {
            $.get('{{ route('HR.reports.history.data') }}', function(data) {
                if (data.success && data.data) {
                    // Group by template
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

            $('#folderNameText').text(folder.name);
            $('#folderView').hide();
            $('#fileView').show();

            renderFiles(folder.files);
        }

        function showFolderView() {
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

                    // FIX: Convert to uppercase for comparison
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
                                <span class="badge bg-light text-dark border ms-1">#${item.source_id}</span>
                            </td>
                            <td><small>${item.user_name || '-'}</small></td>
                            <td><span class="badge ${badgeClass}">${statusText}</span></td>
                            <td class="text-end">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-${btnOutline}" 
                                        onclick="previewFile('${item.id}', '${item.report_title}', '{{ route('HR.reports.preview', ':id') }}'.replace(':id', '${item.id}'), '${ext}')"
                                        title="Preview ${ext}">
                                    <span class="iconify ${fileColor}" data-icon="mdi:eye"></span>
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
            console.log('Preview file:', { fileId, title, downloadUrl, extension });
            
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));

            // Reset state
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewLoading').style.display = 'block';
            document.getElementById('previewContent').style.display = 'none';
            document.getElementById('previewError').style.display = 'none';
            document.getElementById('downloadBtn').href = downloadUrl;
            document.getElementById('downloadLink').href = downloadUrl;

            modal.show();

            // Check library availability
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

            // Fetch file dan preview
            fetch(downloadUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    // Gunakan arrayBuffer (sama seperti di create page)
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
                        
                        // Siapkan container
                        const docxContainer = document.createElement('div');
                        docxContainer.className = 'docx-container-preview';
                        previewContainer.appendChild(docxContainer);
                        
                        // Render menggunakan arrayBuffer (SAMA seperti create page)
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
                        // Untuk PDF, tetap gunakan blob
                        const blob = new Blob([arrayBuffer], { type: 'application/pdf' });
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

        .docx-container-preview .docx-wrapper > section.docx {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background: white;
            padding: 40px;
        }
    </style>
@endsection
