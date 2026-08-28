@extends('layout_HR.app')

@section('content_HR')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Dashboard Template</li>
            </ol>
        </nav>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Daftar Template Laporan</h5>
                    <div>
                        <a href="{{ route('HR.reports.create') }}" class="btn btn-primary">
                            <span class="iconify me-2" data-icon="mdi:plus"></span>Buat Template Baru
                        </a>
                        <a href="{{ route('HR.reports.history') }}" class="btn btn-primary">
                            <span class="iconify me-2" data-icon="mdi:folders"></span>History
                        </a>                        
                    </div>

                </div>

                @if ($templates->isNotEmpty())
                    @foreach ($templates as $category => $items)
                        <h6 class="fw-bold text-uppercase text-muted mb-3 mt-4">{{ $category }}</h6>
                        <div class="row g-3">
                            @foreach ($items as $tpl)
                                <div class="col-md-4 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <span class="iconify text-primary" data-icon="mdi:file-document-outline"
                                                    style="font-size: 2rem;"></span>
                                            </div>
                                            <h6 class="card-title fw-bold mb-1">{{ $tpl->name }}</h6>
                                            <p class="text-muted small mb-3">
                                                {{ Str::limit($tpl->description ?? 'Tidak ada deskripsi', 50) }}</p>

                                            <div class="d-flex gap-2 mt-auto">
                                                <a href="{{ route('HR.reports.generate.form', $tpl) }}"
                                                    class="btn btn-sm btn-primary flex-grow-1">
                                                    <span class="iconify me-1" data-icon="mdi:file-pdf-box"></span> Generate
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDeleteTemplate({{ $tpl->id }}, '{{ $tpl->name }}')"
                                                        title="Hapus">
                                                    <span class="iconify" data-icon="mdi:delete"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <img src="{{ asset('svgundraw/emptydata.svg') }}" alt="" width="12%">
                        <h5 class="mt-3"></h5>
                        <a href="{{ route('HR.reports.create') }}" class="btn btn-primary mt-3">Buat Template Baru</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteTemplate(templateId, templateName) {
            const modalHtml = `
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header text-dark">
                                <h5 class="modal-title">
                                    <span class="iconify me-2" data-icon="mdi:alert-circle"></span>
                                    Konfirmasi Hapus
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <span class="iconify text-danger" data-icon="mdi:trash-can-outline" style="font-size: 48px;"></span>
                                </div>
                                <p class="mb-2">Apakah Anda yakin ingin menghapus template:</p>
                                <h5 class="text-danger fw-bold text-center">"${templateName}"</h5>
                                <div class="alert alert-warning small mt-3 mb-0">
                                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. 
                                    Semua placeholder dan konfigurasi terkait akan dihapus permanen.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <span class="iconify me-1" data-icon="mdi:close"></span>Batal
                                </button>
                                <button type="button" class="btn btn-danger" id="btnConfirmDelete" onclick="executeDeleteTemplate(${templateId})">
                                    <span class="iconify me-1" data-icon="mdi:delete"></span>Ya, Hapus Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Hapus modal lama jika ada
            const oldModal = document.getElementById('deleteConfirmModal');
            if (oldModal) {
                oldModal.remove();
            }
            
            // Tambahkan modal baru
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
            
            // Hapus modal dari DOM setelah ditutup
            document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        function executeDeleteTemplate(templateId) {
            const btn = document.getElementById('btnConfirmDelete');
            const originalText = btn.innerHTML;
            
            // Disable button dan tampilkan loading
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghapus...';
            
            fetch(`/HR-dashboard/reports/delete/${templateId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Tutup modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                    modal.hide();
                    
                    // Tampilkan pesan sukses
                    showToast(data.message, 'success');
                    
                    // Reload halaman setelah 1 detik
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Tampilkan pesan error
                    showToast(data.message || 'Gagal menghapus template', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan koneksi', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }

        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            const bgClass = type === 'error' ? 'bg-danger' : 'bg-success';
            const icon = type === 'error' ? 'mdi:alert-circle' : 'mdi:check-circle';
            
            const toastHtml = `
                <div class="toast align-items-center text-white border-0 ${bgClass}" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <span class="iconify me-2" data-icon="${icon}"></span>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1100';
            document.body.appendChild(container);
            return container;
        }
    </script>
@endsection
