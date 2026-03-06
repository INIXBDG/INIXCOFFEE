@extends('layouts_office.app')

@section('office_contents')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #pickupDriverCondition {
            display: none;
        }

        #pickupDriverCondition .hidePickup {
            display: none;
        }
    </style>
    <div class="container-fluid py-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Daftar Tugas Office Boy</h4>
            </div>
            <button class="btn btn-primary px-4 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal"
                data-bs-target="#createModal">
                Buat Kategori Baru
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-5">
                        <h5 class="mb-0 fw-semibold" id="dynamicTitle">
                            Daftar Tugas Harian - {{ now()->translatedFormat('l, d F Y') }}
                        </h5>
                    </div>
                    <div class="col-md-7">
                        <div class="row">
                            <div class="col"></div>
                            <div class="col"></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                            <select id="filterTipe" class="form-select form-select-sm" style="width: auto;">
                                <option value="Harian" selected>Harian</option>
                                <option value="Mingguan">Mingguan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Quartal">Quartal</option>
                                <option value="Semester">Semester</option>
                                <option value="Tahunan">Tahunan</option>
                            </select>

                            <input type="date" id="filterTanggal" class="form-control form-control-sm"
                                style="width: auto;" value="{{ now()->format('Y-m-d') }}">

                            <button class="btn btn-outline-secondary btn-sm" id="btnResetFilter">
                                <i class="bx bx-reset"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-header bg-white border-0 py-3">
                <div class="row align-items-center">
                    <div class="col text-end" style="overflow-x: scrool">
                        <div class="row">
                            <div class="col">Update Tugas</div>
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-primary btn-update-tugas"
                                    data-route="{{ route('office.DaftarTugas.UpdateTugasHarian') }}">
                                    Harian
                                </button>
                                <button type="button" class="btn btn-primary btn-update-tugas"
                                    data-route="{{ route('office.DaftarTugas.UpdateTugasMingguan') }}">
                                    Mingguan
                                </button>
                                <button type="button" class="btn btn-primary btn-update-tugas"
                                    data-route="{{ route('office.DaftarTugas.UpdateTugasBulanan') }}">
                                    Bulanan
                                </button>
                                <button type="button" class="btn btn-primary btn-update-tugas"
                                    data-route="{{ route('office.DaftarTugas.UpdateTugasQuartal') }}">
                                    Quartal
                                </button>
                                <button type="button" class="btn btn-primary btn-update-tugas"
                                    data-route="{{ route('office.DaftarTugas.UpdateTugasSemester') }}">
                                    Semester
                                </button>
                                <button type="button" class="btn btn-primary btn-update-tugas"
                                    data-route="{{ route('office.DaftarTugas.UpdateTugasTahunan') }}">
                                    Tahunan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="text-dark fw-semibold small bg-light">
                            <tr>
                                <th class="ps-4 border-0" style="width: 5%;">Chacklist</th>
                                <th class="border-0" style="width: 25%;">Tugas</th>
                                <th class="border-0" style="width: 25%;">Tipe</th>
                                <th class="border-0" style="width: 15%;">Deadline</th>
                                <th class="border-0 text-center" style="width: 18%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form action="{{ route('office.DaftarTugas.store') }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Kategori Tugas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Tugas</label>
                            <input type="text" placeholder="Contoh : Payroll/Gaji Karyawan" class="form-control"
                                required name="tugas">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe</label>
                            <select name="Tipe" required class="form-control">
                                <option selected disabled>Pilih Tipe</option>
                                <option value="Harian">Harian</option>
                                <option value="Mingguan">Mingguan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Quartal">Quartal</option>
                                <option value="Semester">Semester</option>
                                <option value="Tahunan">Tahunan</option>
                            </select>
                        </div>

                        <hr>

                        <h6 class="mb-3">Daftar Kategori</h6>

                        <div style="max-height:250px; overflow-y:auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tugas</th>
                                        <th width="120">Tipe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dataKategori as $data)
                                        <tr>
                                            <td>{{ $data->judul_kategori }}</td>
                                            <td>{{ $data->Tipe }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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

    <div class="modal fade" id="modalUploadBukti" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formUploadBukti" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">📎 Upload Bukti Pelaksanaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="tugas_id" id="uploadTugasId">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tugas</label>
                            <input type="text" class="form-control" id="uploadTugasNama" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">File Bukti <small class="text-muted">(Max:
                                    5MB)</small></label>
                            <input type="file" class="form-control" name="bukti_file" id="inputBuktiFile"
                                accept="image/*,.pdf,.doc,.docx" required>
                            <small class="text-muted">Format: JPG, PNG, PDF, DOC</small>
                        </div>

                        <div id="previewContainer" class="d-none text-center">
                            <img id="imagePreview" src="" class="img-fluid rounded border"
                                style="max-height: 200px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitUpload">
                            <span class="spinner-border spinner-border-sm d-none" id="uploadSpinner"></span>
                            <span id="btnUploadText">Upload</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filterTanggal').val(new Date().toISOString().split('T')[0]);

            loadData();

            $('#filterTipe, #filterTanggal').on('change', function() {
                updateTitle();
                loadData();
            });

            $('#btnResetFilter').on('click', function() {
                $('#filterTipe').val('Harian');
                $('#filterTanggal').val(new Date().toISOString().split('T')[0]);
                updateTitle();
                loadData();
            });
        });

        function updateTitle() {
            const tipe = $('#filterTipe').val();
            const tanggal = $('#filterTanggal').val();

            const dateObj = new Date(tanggal + 'T00:00:00');
            const formattedDate = dateObj.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            $('#dynamicTitle').text(`Daftar Tugas ${tipe} - ${formattedDate}`);
        }

        function loadData() {
            const tipe = $('#filterTipe').val();
            const tanggal = $('#filterTanggal').val();

            $.ajax({
                url: "{{ route('office.DaftarTugas.get') }}",
                type: 'GET',
                data: {
                    tipe: tipe,
                    tanggal: tanggal
                },
                success: function(response) {
                    const tbody = $('#tbody');
                    tbody.empty();

                    if (response.data.length === 0) {
                        tbody.append(`
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center gap-3">
                                        <i class="fas fa-clipboard-list text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="text-muted mb-1">Belum ada Tugas untuk filter ini</h5>
                                        <small class="text-muted">Coba ubah tipe atau tanggal filter</small>
                                    </div>
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    response.data.forEach(function(item) {
                        let kategori = item.kategori_daftar_tugas ? item.kategori_daftar_tugas
                            .judul_kategori : '-';
                        let Tipe = item.kategori_daftar_tugas ? item.kategori_daftar_tugas.Tipe : '-';
                        let deadline = item.Deadline_Date ?? '-';
                        let checked = item.status == 1 ? 'checked' : '';
                        let doneClass = item.status == 1 ? 'text-decoration-line-through text-muted' :
                            '';

                        let btnBukti;
                        let buktiUrl = item.bukti ? '/storage/' + item.bukti : null;

                        if (item.bukti) {
                            btnBukti = `<button class="btn btn-success btn-sm btn-viewBukti" 
                                            data-bukti="${buktiUrl}" 
                                            data-judul="${kategori}">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>`;
                        } else {
                            btnBukti = `<button class="btn btn-primary btn-sm btn-uploadBukti" 
                                            data-id="${item.id}">
                                            <i class="fas fa-upload"></i> Bukti
                                        </button>`;
                        }

                        tbody.append(`
                            <tr>
                                <td>
                                    <input class="form-check-input checkStatus" 
                                        type="checkbox" 
                                        data-id="${item.id}"
                                        ${checked}>
                                </td>
                                <td class="task-text ${doneClass}">${kategori}</td>
                                <td class="task-text ${doneClass}">${Tipe}</td>
                                <td class="task-text ${doneClass}">${deadline}</td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm btn-hapus" data-id="${item.id}">
                                        Hapus
                                    </button>
                                    ${btnBukti}
                                </td>
                            </tr>
                        `);
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    $('#tbody').html(`
                        <tr>
                            <td colspan="5" class="text-center py-4 text-danger">
                                Gagal memuat data. Silakan refresh halaman.
                            </td>
                        </tr>
                    `);
                }
            });
        }

        $(document).on('click', '.btn-hapus', function() {
            const btn = $(this);
            const id = btn.data('id');
            const row = btn.closest('tr');

            if (!confirm('Yakin ingin menghapus tugas ini?')) {
                return;
            }

            const originalText = btn.text();
            btn.prop('disabled', true).text('Menghapus...');

            $.ajax({
                url: "{{ route('office.DaftarTugas.delete', '') }}/" + id,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    row.fadeOut(300, function() {
                        $(this).remove();

                        if ($('#tbody tr').length === 0) {
                            $('#tbody').html(`
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <i class="fas fa-clipboard-list text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mb-1">Belum ada Tugas untuk filter ini</h5>
                                    <small class="text-muted">Coba ubah tipe atau tanggal filter</small>
                                </div>
                            </td>
                        </tr>
                    `);
                        }
                    });

                    showNotification('Berhasil', 'Data berhasil dihapus', 'success');
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    alert('Gagal menghapus data. Silakan coba lagi.');
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });

        $(document).on('click', '.btn-viewBukti', function(e) {
            e.preventDefault();
            let fileUrl = $(this).data('bukti');
            let judul = $(this).data('judul');
            let ext = fileUrl.split('.').pop().toLowerCase();

            let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
            let isPdf = ext === 'pdf';

            let contentPreview = '';

            if (isImage) {
                contentPreview = `<img src="${fileUrl}" class="img-fluid rounded" style="max-height: 70vh;">`;
            } else if (isPdf) {
                contentPreview =
                    `<iframe src="${fileUrl}" width="100%" height="600px" style="border:none;"></iframe>`;
            } else {
                window.open(fileUrl, '_blank');
                return;
            }

            let modalHtml = `
                <div class="modal fade" id="modalPreviewBukti" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">📄 ${judul}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center bg-light">
                                ${contentPreview}
                            </div>
                            <div class="modal-footer">
                                <a href="${fileUrl}" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-external-link-alt"></i> Buka di Tab Baru
                                </a>
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#modalPreviewBukti').remove();
            $('body').append(modalHtml);

            var previewModal = new bootstrap.Modal(document.getElementById('modalPreviewBukti'));
            previewModal.show();

            $('#modalPreviewBukti').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        });

        $(document).on('click', '.btn-hapus', function() {
            const btn = $(this);
            const id = btn.data('id');
            const row = btn.closest('tr');

            if (!confirm('Yakin ingin menghapus tugas ini?')) {
                return;
            }

            const originalText = btn.text();
            btn.prop('disabled', true).text('Menghapus...');

            $.ajax({
                url: "{{ route('office.DaftarTugas.delete', '') }}/" + id,
                method: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    row.fadeOut(300, function() {
                        $(this).remove();

                        if ($('#tbody tr').length === 0) {
                            $('#tbody').html(`
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <i class="fas fa-clipboard-list text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mb-1">Belum ada Tugas untuk filter ini</h5>
                                    <small class="text-muted">Coba ubah tipe atau tanggal filter</small>
                                </div>
                            </td>
                        </tr>
                    `);
                        }
                    });

                    showNotification('Berhasil', 'Data berhasil dihapus', 'success');
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    alert('Gagal menghapus data. Silakan coba lagi.');
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });

        $(document).on('change', '#inputBuktiFile', function(e) {
            const file = e.target.files[0];
            const previewContainer = $('#previewContainer');
            const imagePreview = $('#imagePreview');

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.attr('src', e.target.result);
                    previewContainer.removeClass('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                previewContainer.addClass('d-none');
            }
        });

        $(document).on('click', '.btn-uploadBukti', function() {
            const btn = $(this);
            const id = btn.data('id');
            const row = btn.closest('tr');
            const tugasNama = row.find('.task-text').first().text().trim();

            $('#uploadTugasId').val(id);
            $('#uploadTugasNama').val(tugasNama);
            $('#inputBuktiFile').val('');
            $('#previewContainer').addClass('d-none');
            $('textarea[name="catatan"]').val('');

            const modal = new bootstrap.Modal(document.getElementById('modalUploadBukti'));
            modal.show();
        });

        $('#formUploadBukti').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const tugasId = $('#uploadTugasId').val();
            const btnSubmit = $('#btnSubmitUpload');
            const spinner = $('#uploadSpinner');
            const btnText = $('#btnUploadText');

            const fileInput = $('#inputBuktiFile')[0].files[0];
            if (fileInput && fileInput.size > 5 * 1024 * 1024) {
                alert('Ukuran file maksimal 5MB!');
                return;
            }

            btnSubmit.prop('disabled', true);
            spinner.removeClass('d-none');
            btnText.text('Mengupload...');

            $.ajax({
                url: "{{ route('office.DaftarTugas.uploadBukti') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    const modalEl = document.getElementById('modalUploadBukti');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();

                    const row = $(`.btn-uploadBukti[data-id="${tugasId}"]`).closest('tr');
                    const btnBukti = row.find('.btn-uploadBukti');
                    if (!btnBukti.hasClass('btn-success')) {
                        btnBukti.removeClass('btn-primary').addClass('btn-success')
                            .html('<i class="fas fa-check"></i> Terupload');
                    }

                    showNotification('Berhasil!', 'Bukti berhasil diupload.', 'success');

                    $('#formUploadBukti')[0].reset();
                    $('#previewContainer').addClass('d-none');
                },
                error: function(xhr) {
                    console.error('Upload error:', xhr.responseText);
                    let errorMsg = 'Gagal mengupload bukti.';
                    if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                },
                complete: function() {
                    btnSubmit.prop('disabled', false);
                    spinner.addClass('d-none');
                    btnText.text('Upload');
                }
            });
        });

        function showNotification(title, message, type = 'info') {
            const alertHtml = `
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
                    <div class="alert alert-${type} alert-dismissible fade show shadow-sm rounded-4" role="alert">
                        <strong>${title}</strong> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            `;
            $('body').append(alertHtml);

            setTimeout(() => {
                $('.alert').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }

        $(document).on('change', '.checkStatus', function() {

            let checkbox = $(this);
            let id = checkbox.data('id');
            let row = checkbox.closest('tr');

            let status = checkbox.is(':checked') ? 1 : 0;

            updateStatus(id, status);

            if (status == 1) {
                row.find('.task-text').addClass('text-decoration-line-through text-muted');
            } else {
                row.find('.task-text').removeClass('text-decoration-line-through text-muted');
            }

        });

        function updateStatus(id, status) {
            $.ajax({
                url: "{{ route('office.DaftarTugas.updateStatus') }}",
                method: 'POST',
                data: {
                    id: id,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            });
        }

        $(document).on('click', '.btn-update-tugas', function(e) {
            e.preventDefault();
            const routeUrl = $(this).data('route');
            const btn = $(this);

            const originalText = btn.text();
            btn.prop('disabled', true).text('Memproses...');

            $.ajax({
                url: routeUrl,
                method: 'get',
                success: function(response) {
                    console.log(response);
                    loadData();
                    btn.prop('disabled', false).text(originalText);
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    alert('Terjadi kesalahan saat memproses permintaan.');
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });
    </script>
@endsection
