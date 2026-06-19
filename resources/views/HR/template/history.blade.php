@extends('layout_HR.app')

@section('content_HR')
    <div id="report-generator-app" class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('HR.reports.index') }}" class="text-decoration-none">Report Generator</a>
                </li>
                @if ($template ?? null)
                    <li class="breadcrumb-item">
                        <a href="{{ route('HR.reports.index', ['template_id' => $template->id]) }}">{{ $template->name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active">Riwayat Generate</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <span class="iconify me-2" data-icon="mdi:history"></span>Riwayat Generate
                    </h5>
                    @if ($template ?? null)
                        <a href="{{ route('HR.reports.index', ['template_id' => $template->id]) }}"
                            class="btn btn-outline-secondary btn-sm">
                            <span class="iconify me-1" data-icon="mdi:arrow-left"></span>Kembali ke Template
                        </a>
                    @else
                        <a href="{{ route('HR.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                            <span class="iconify me-1" data-icon="mdi:arrow-left"></span>Kembali ke Daftar
                        </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="historyTable">
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
                        <tbody id="historyBody">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <span class="ms-2 text-muted">Memuat riwayat...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            function loadHistory() {
                const url =
                    '{{ $template ? route('HR.reports.history.data', $template) : route('HR.reports.history.data') }}';

                $.get(url, function(data) {
                    let html = '';
                    if (data.data && data.data.length) {
                        data.data.forEach(function(item) {
                            // Tentukan badge status
                            let badgeClass = 'bg-warning bg-opacity-10 text-warning';
                            let statusText = 'Pending';
                            if (item.status === 'completed') {
                                badgeClass = 'bg-success bg-opacity-10 text-success';
                                statusText = 'Sukses';
                            } else if (item.status === 'failed') {
                                badgeClass = 'bg-danger bg-opacity-10 text-danger';
                                statusText = 'Gagal';
                            }

                            // Tentukan ikon & warna berdasarkan ekstensi file
                            let fileIcon = 'mdi:file-document-outline';
                            let fileColor = 'text-secondary';
                            let btnOutline = 'secondary';

                            if (item.file_extension === 'DOCX') {
                                fileIcon = 'mdi:microsoft-word';
                                fileColor = 'text-primary';
                                btnOutline = 'primary';
                            } else if (item.file_extension === 'PDF') {
                                fileIcon = 'mdi:file-pdf-box';
                                fileColor = 'text-danger';
                                btnOutline = 'danger';
                            }

                            html += `<tr>
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
                                        <a href="${item.download_url}" class="btn btn-sm btn-outline-${btnOutline}" target="_blank" title="Download ${item.file_extension}">
                                            <span class="iconify ${fileColor}" data-icon="${fileIcon}"></span>
                                        </a>
                                    </td>
                                </tr>`;
                        });
                    } else {
                        html =
                            '<tr><td colspan="6" class="text-center py-5 text-muted">Belum ada riwayat generate</td></tr>';
                    }
                    $('#historyBody').html(html);

                    if (window.Iconify) {
                        Iconify.renderSVG();
                    }
                }).fail(function() {
                    $('#historyBody').html(
                        '<tr><td colspan="6" class="text-center py-5 text-danger">Gagal memuat riwayat. Silakan refresh halaman.</td></tr>'
                    );
                });
            }

            loadHistory();
        });
    </script>
@endsection
