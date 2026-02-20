@extends('layouts.app')

@section('content')
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                @if($assignMode === 'exam' && $examData)
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-clipboard-check"></i> Mode Assign Ruangan untuk Exam</h5>
                        <div class="row">
                            <div class="col-md-3"><strong>Materi:</strong> {{ $examData['materi'] }}</div>
                            <div class="col-md-3"><strong>Perusahaan:</strong> {{ $examData['perusahaan'] }}</div>
                            <div class="col-md-3"><strong>Pax:</strong> {{ $examData['pax'] }}</div>
                            <div class="col-md-3"><strong>Invoice:</strong> {{ $examData['invoice'] }}</div>
                        </div>
                        <p class="mt-2 mb-0">Pilih ruangan yang kosong untuk jadwal exam ini</p>
                    </div>
                @endif

                <h2 class="mb-4 fw-semibold">
                    @if($assignMode === 'exam')
                        Pilih Ruang untuk Exam
                    @else
                        Daftar Ruang Kelas Offline
                    @endif
                </h2>

                <div class="row justify-content-center mb-5">
                    <div class="col-md-4 mb-2">
                        <input type="date" id="filterStart" name="filter" class="form-control shadow-sm rounded-pill px-4"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    @if($assignMode === 'exam')
                        <div class="col-md-2">
                            <a href="{{ route('exam.index') }}" class="btn btn-secondary rounded-pill px-4">
                                <i class="fas fa-arrow-left"></i> Kembali ke Exam
                            </a>
                        </div>
                    @endif
                </div>

                <div class="row g-4 justify-content-center" id="ruangList">
                    @php
                        $ruangs = ['1', '2', '3', '4', '5', '6', 'ADOC'];
                    @endphp
                    @foreach($ruangs as $r)
                        <div id="container-ruang-{{ $r }}" class="col-md-4 col-sm-6 ruang-container">
                            <div class="ruang {{ $assignMode === 'exam' ? 'exam-assign-mode' : '' }}">
                                <div class="d-flex row justify-content-between g-2 mb-3">
                                    <div class="col-6 text-start">
                                        <span class="w-100 py-2" style="font-size: medium;">-</span>
                                    </div>
                                    <div class="col-4 text-end">
                                        <input type="date" class="form-control form-control-sm ruang-filter"
                                            style="font-size: small;">
                                    </div>
                                </div>

                                <div class="ruang-title mb-2 fw-bold" style="font-size: 40px;">Ruang {{ $r }}</div>
                                <input type="hidden" class="ruang-nama" value="{{ $r }}">

                                <div class="text-center">
                                    <div class="fw-semibold" style="font-size: 15px;">Kosong</div>
                                    <div class="small text-light" style="font-size: 15px;"></div>
                                </div>
                                <div class="mt-3 text-end tombol-container">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailRKM" tabindex="-1" aria-labelledby="modalDetailRKMLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailRKMLabel">Detail RKM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bodyContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKelolaManagement" tabindex="-1" aria-labelledby="modalKelolaManagementLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formKelolaManagement" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalKelolaManagementLabel">
                            @if($assignMode === 'exam')
                                Assign Exam ke Ruangan
                            @else
                                Kelola Ruangan
                            @endif
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            @if($assignMode === 'exam')
                                <i class="fas fa-check"></i> Assign Exam
                            @else
                                Simpan
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        body {
            background: #f5f7fa;
        }

        .ruang {
            user-select: none;
            background: linear-gradient(145deg, #ffffff, #f1f3f6);
            border-radius: 20px;
            padding: 40px 25px;
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.4s ease;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            animation: zoomFade 0.8s ease both;
            position: relative;
        }

        .ruang.exam-assign-mode:not(.ruang-ada) {
            border: 3px dashed #28a745;
            background: linear-gradient(145deg, #f8fff9, #e8f5e8);
        }

        .ruang.exam-assign-mode:not(.ruang-ada):hover {
            background: linear-gradient(145deg, #e8f5e8, #d4edda);
            transform: scale(1.02);
        }

        @keyframes zoomFade {
            0% {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .ruang-filter {
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .ruang-ada {
            color: #fff !important;
        }

        .ruang-ada.manajemen {
            background: #2C3E50 !important;
            color: #ffffff !important;
        }

        .ruang-ada.rkm {
            background: #2C3E50 !important;
            color: #ffffff !important;
        }

        .exam-assign-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .small {}
    </style>

    @push('js')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment-with-locales.min.js"></script>
        <script>
            let kelolaModal;
            let containerFilters = {};
            const isExamMode = {{ $assignMode === 'exam' ? 'true' : 'false' }};

            function getContainerId(d) {
                if (d.ruang === "-") return d.ruangan.replace('Ruang ', '');
                if (d.ruangan === "-") return d.ruang.replace('Ruang ', '');
                return '';
            }

            $(document).ready(function () {
                moment.locale('id');

                const filterUtama = $("#filterStart").val();
                loadAllContainers(filterUtama);

                $("#filterStart").on("change", function () {
                    loadAllContainers($(this).val());
                });

                kelolaModal = new bootstrap.Modal(
                    document.getElementById('modalKelolaManagement'),
                    { backdrop: true }
                );

                $(document).on("click", ".ruang-filter", function (e) {
                    e.stopPropagation();
                });

                $(document).on("change", ".ruang-filter", function () {
                    let parent = $(this).closest(".ruang-container");
                    let ruang = parent.find(".ruang-nama").val();
                    let tanggalRuang = $(this).val();

                    if (!containerFilters[ruang]) containerFilters[ruang] = [];
                    if (tanggalRuang && !containerFilters[ruang].includes(tanggalRuang)) containerFilters[ruang].push(tanggalRuang);

                    loadContainer(parent, ruang, containerFilters[ruang]);
                });

                $(document).on("click", ".ruang:not(.ruang-ada)", function () {
                    let ruang = $(this).find(".ruang-nama").val();
                    if (isExamMode) {
                        openModalKelolaExam(ruang);
                    } else {
                        openModalKelola(ruang);
                    }
                });

                $(document).on("click", ".ruang.ruang-ada", function (e) {
                    if (isExamMode) {
                        e.preventDefault();
                        alert('Ruangan ini sudah terisi. Silakan pilih ruangan lain.');
                        return false;
                    }
                });

                $('#formKelolaManagement').on('submit', function (e) {
                    e.preventDefault();
                    const form = $(this);
                    const formData = form.serialize();
                    const actionUrl = form.attr('action');
                    const method = form.find('input[name="_method"]').val() || 'POST';

                    console.log('=== FORM SUBMIT DEBUG ===');
                    console.log('URL:', actionUrl);
                    console.log('Method:', method);
                    console.log('Data:', formData);
                    console.log('CSRF Token:', $('input[name="_token"]').val());
                    console.log('=========================');

                    // ✅ HELPER: Fungsi restore button - dipanggil di SEMUA case
                    function restoreButton() {
                        const btn = form.find('button[type="submit"]');
                        const originalText = btn.data('original-text') || 'Simpan';
                        btn.prop('disabled', false).html(originalText);
                    }

                    $.ajax({
                        url: actionUrl,
                        type: method,
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function () {
                            const btn = form.find('button[type="submit"]');
                            // ✅ SIMPAN original text button
                            btn.data('original-text', btn.html());
                            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                        },
                        success: function (res) {
                            console.log('✅ Success Response:', res);

                            // Deteksi jika response HTML (fallback)
                            if (typeof res === 'string' && res.trim().startsWith('<!doctype')) {
                                console.warn('⚠️ Server mengembalikan HTML');
                                if (res.includes('Ruangan berhasil dijadwalkan')) {
                                    alert('✅ Data berhasil disimpan!');
                                } else {
                                    alert('⚠️ Terjadi kesalahan format response');
                                }
                                restoreButton();  // ✅ RESTORE sebelum hide modal
                                kelolaModal.hide();
                                loadAllContainers($("#filterStart").val());
                                return;
                            }

                            if (res.success) {
                                alert(res.message || 'Data berhasil disimpan!');
                                // ✅ RESTORE button SEBELUM hide modal (penting!)
                                restoreButton();
                                kelolaModal.hide();
                                loadAllContainers($("#filterStart").val());
                            } else {
                                alert('Error: ' + (res.message || 'Terjadi kesalahan pada server'));
                                restoreButton();  // ✅ RESTORE di error branch juga
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log('❌ Error Response:', {
                                status: xhr.status,
                                responseText: xhr.responseText,
                                responseJSON: xhr.responseJSON,
                                error: error
                            });

                            // Deteksi HTML response
                            if (xhr.responseText && xhr.responseText.trim().startsWith('<!doctype')) {
                                if (xhr.responseText.includes('Ruangan berhasil dijadwalkan')) {
                                    alert('✅ Data berhasil disimpan! (Response HTML detected)');
                                    restoreButton();
                                    kelolaModal.hide();
                                    loadAllContainers($("#filterStart").val());
                                    return;
                                }
                            }

                            let errorMsg = 'Terjadi kesalahan sistem';

                            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                                const errors = xhr.responseJSON.errors;
                                errorMsg = Object.values(errors).flat().join('\n');
                                alert('⚠️ Validasi Gagal:\n\n' + errorMsg);
                            }
                            else if (xhr.status === 419) {
                                alert('⚠️ Session expired. Silakan refresh halaman.');
                                location.reload();
                                return;
                            }
                            else if (xhr.status === 500) {
                                errorMsg = 'Server error. Cek log: storage/logs/laravel.log';
                                alert('❌ ' + errorMsg);
                            }
                            else {
                                errorMsg = xhr.responseJSON?.message || xhr.responseText || error;
                                alert('❌ Error: ' + errorMsg);
                            }

                            restoreButton();  // ✅ RESTORE di semua error case
                        }
                    });
                });
            });

            function loadAllContainers(filterUtama) {
                $.ajax({
                    url: "{{ route('managementKelas.get') }}",
                    type: "GET",
                    data: {
                        filter_utama: filterUtama
                    },
                    success: function (response) {
                        $(".ruang-container").each(function () {
                            const tanggalUtama = $("#filterStart").val();
                            $(this).find(".row .col-6 span").text(tanggalUtama);
                            $(this).find(".fw-semibold").text("Kosong");
                            $(this).find(".small").text("");
                            $(this).find(".tombol-container").empty();
                            $(this).find(".ruang").removeClass("ruang-ada manajemen rkm").css("background", "");
                        });

                        response.forEach(d => {
                            let containerId = getContainerId(d);
                            if (!containerId) return;
                            let container = $(`#container-ruang-${containerId}`);
                            updateContainer(container, [d]);
                        });
                    }
                });
            }

            function loadContainer(container, ruang, tanggalArray = null) {
                $.ajax({
                    url: "{{ route('managementKelas.get') }}",
                    type: "GET",
                    traditional: true,
                    data: {
                        ruang: ruang,
                        tanggal_ruang: tanggalArray
                    },
                    success: function (response) {
                        updateContainer(container, response);
                    },
                    error: function () {
                        updateContainer(container, []);
                    }
                });
            }

            function updateContainer(container, data) {
                if (!container || container.length === 0) return;

                let materi = container.find(".fw-semibold");
                let info = container.find(".small");
                let kelola = container.find(".tombol-container");
                let ruangBox = container.find(".ruang");
                let tanggalSpan = container.find(".row .col-6 span");
                const filterUtama = $("#filterStart").val();

                if (!data || data.length === 0) {
                    materi.text("Kosong");
                    info.text("");
                    kelola.empty();
                    tanggalSpan.text(filterUtama);
                    ruangBox.removeClass("ruang-ada manajemen rkm").css("background", "");

                    if (isExamMode) {
                        info.html('<span class="text-success"><i class="fas fa-check"></i> Tersedia untuk Exam</span>');
                    }
                    return;
                }

                let manajemen = data.find(d => d.ruang === "-");
                let rkm = data.find(d => d.ruangan === "-");

                if (manajemen) {
                    ruangBox.addClass("ruang-ada manajemen").css("background", "#dc3545");
                    materi.html(`<ul><li>${manajemen.tanggal} - ${manajemen.jam_mulai} s/d ${manajemen.jam_selesai}</li></ul>`);
                    info.text("Kebutuhan: " + (manajemen.kebutuhan || "-"));
                    tanggalSpan.text(manajemen.tanggal || filterUtama);

                    if (!isExamMode) {
                        let btnKelola = `<button type="button" class="btn btn-primary btn-kelola" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalKelolaManagement" 
                                                    data-ruang="${manajemen.ruangan}"
                                                    data-id="${manajemen.id || ''}"
                                                    data-tanggal="${manajemen.tanggal || ''}"
                                                    data-jam-mulai="${manajemen.jam_mulai || ''}"
                                                    data-jam-selesai="${manajemen.jam_selesai || ''}"
                                                    data-kebutuhan="${manajemen.kebutuhan || ''}"
                                                    data-keterangan="${manajemen.keterangan || ''}"
                                                    data-is-exam="${manajemen.is_exam || 0}"
                                                    data-exam-id="${manajemen.exam_id || ''}"
                                                    >Kelola</button>`;

                        const today = new Date().toISOString().split('T')[0];
                        if (manajemen.tanggal >= today && manajemen.id) {
                            btnKelola += `<button type="button" class="btn btn-danger btn-batalkan ms-2" 
                                                        data-id="${manajemen.id}" 
                                                        data-ruang="${manajemen.ruangan}"
                                                        data-tanggal="${manajemen.tanggal}">
                                                        <i class="fas fa-ban"></i> Batalkan</button>`;
                        }
                        kelola.html(btnKelola);
                    }
                    return;
                }

                if (rkm) {
                    ruangBox.addClass("ruang-ada rkm").css("background", "#28a745");
                    materi.text(rkm.materi || "-");
                    info.text(`Sales: ${rkm.sales || "-"} • Instruktur: ${rkm.instruktur || "-"}` +
                        (rkm.instruktur2 ? " • Instruktur2: " + rkm.instruktur2 : "") +
                        (rkm.asisten ? " • Asisten: " + rkm.asisten : ""));
                    tanggalSpan.text(rkm.tanggal_awal + " s/d " + rkm.tanggal_akhir || filterUtama);

                    if (!isExamMode) {
                        kelola.html(`<button type="button" class="btn btn-info btn-detail" data-bs-toggle="modal" data-bs-target="#modalDetailRKM"
                                                    data-ruang="${rkm.ruang}" 
                                                    data-tanggal-awal="${rkm.tanggal_awal}" 
                                                    data-tanggal-akhir="${rkm.tanggal_akhir}" 
                                                    data-materi="${rkm.materi}" 
                                                    data-sales="${rkm.sales}" 
                                                    data-instruktur="${rkm.instruktur}" 
                                                    data-instruktur2="${rkm.instruktur2}" 
                                                    data-asisten="${rkm.asisten}" 
                                                    data-perusahaan="${rkm.perusahaan}" 
                                                    data-pax="${rkm.pax}" 
                                                    data-exam="${rkm.exam}" 
                                                    data-authorize="${rkm.authorize}" 
                                                    >Detail</button>`);
                    }
                    return;
                }
            }

            function openModalKelola(ruang, existingData = null) {
                const isEdit = existingData && existingData.id;

                $("#modalKelolaManagementLabel").text(isEdit ? "Edit Kelola Ruang " + ruang : "Kelola Ruang " + ruang);

                const val = (key, fallback = '') => existingData ? (existingData[key] || fallback) : fallback;

                // Set form action dinamis
                const form = $("#formKelolaManagement");
                if (isEdit) {
                    form.attr('action', `/management-kelas/${existingData.id}`);
                    if (!form.find('input[name="_method"]').length) {
                        form.append('<input type="hidden" name="_method" value="PUT">');
                    }
                } else {
                    form.attr('action', "{{ route('managementKelas.store') }}");
                    form.find('input[name="_method"]').remove();
                }

                $("#modalKelolaManagement .modal-body").html(`
                                            <input type="hidden" name="id" value="${val('id', '')}">
                                            <input type="hidden" name="ruang" value="${ruang}">
                                            <input type="hidden" name="is_exam" value="${val('is_exam', 0)}">
                                            <input type="hidden" name="exam_id" value="${val('exam_id', '')}">

                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Nama Ruangan</label>
                                                <input type="text" class="form-control" value="Ruang ${ruang}" readonly>
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="tanggal" value="${val('tanggal')}" required>
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                                <input type="time" class="form-control" name="jam_mulai" id="jamMulai" value="${val('jam_mulai')}" required>
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                                <input type="time" class="form-control" name="jam_selesai" id="jamSelesai" value="${val('jam_selesai')}" required>
                                            </div>
                                            <small id="errorMsg" class="text-danger d-none">Jam selesai harus minimal 1 jam setelah jam mulai</small>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Kebutuhan</label>
                                                <textarea name="kebutuhan" class="form-control" rows="3">${val('kebutuhan', '')}</textarea>
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Keterangan</label>
                                                <textarea name="keterangan" class="form-control" rows="3">${val('keterangan', '')}</textarea>
                                            </div>
                                        `);

                if (val('jam_mulai') && val('jam_selesai')) {
                    $("#jamMulai, #jamSelesai").trigger('change');
                }

                kelolaModal.show();
            }

            function openModalKelolaExam(ruang, existingData = null) {
                const isEdit = existingData && existingData.id;

                $("#modalKelolaManagementLabel").text(isEdit ? "Edit Assign Exam ke Ruang " + ruang : "Assign Exam ke Ruang " + ruang);

                const val = (key, fallback = '') => existingData ? (existingData[key] || fallback) : fallback;

                const form = $("#formKelolaManagement");
                if (isEdit) {
                    form.attr('action', `/management-kelas/${existingData.id}`);
                    if (!form.find('input[name="_method"]').length) {
                        form.append('<input type="hidden" name="_method" value="PUT">');
                    }
                } else {
                    form.attr('action', "{{ route('managementKelas.store') }}");
                    form.find('input[name="_method"]').remove();
                }

                $("#modalKelolaManagement .modal-body").html(`
                                            <input type="hidden" name="id" value="${val('id', '')}">
                                            <input type="hidden" name="ruang" value="${ruang}">
                                            <input type="hidden" name="is_exam" value="1">
                                            <input type="hidden" name="exam_id" value="${val('exam_id', '')}">

                                            <div class="alert alert-info text-start">
                                                <strong>Exam Details:</strong><br>
                                                @if($examData)
                                                    Materi: {{ $examData['materi'] }}<br>
                                                    Perusahaan: {{ $examData['perusahaan'] }}<br>
                                                    Pax: {{ $examData['pax'] }}<br>
                                                    Invoice: {{ $examData['invoice'] }}
                                                @endif
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Nama Ruangan</label>
                                                <input type="text" class="form-control" value="Ruang ${ruang}" readonly>
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Tanggal Exam <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="tanggal" value="${val('tanggal')}" required>
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                                <input type="time" class="form-control" name="jam_mulai" id="jamMulai" value="${val('jam_mulai')}" required>
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                                <input type="time" class="form-control" name="jam_selesai" id="jamSelesai" value="${val('jam_selesai')}" required>
                                            </div>
                                            <small id="errorMsg" class="text-danger d-none">Jam selesai harus minimal 1 jam setelah jam mulai</small>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Kebutuhan (Optional)</label>
                                                <textarea name="kebutuhan" class="form-control" placeholder="Akan diisi otomatis jika kosong" rows="2">${val('kebutuhan', `Exam - {{ $examData['materi'] ?? '' }}`)}</textarea>
                                                <small class="form-text text-muted">Default: Exam - [Nama Materi]</small>
                                            </div>
                                            <div class="form-group mt-3 text-start">
                                                <label class="form-label">Keterangan (Optional)</label>
                                                <textarea name="keterangan" class="form-control" placeholder="Akan diisi otomatis jika kosong" rows="2">${val('keterangan', `Exam untuk {{ $examData['perusahaan'] ?? '' }} (Pax: {{ $examData['pax'] ?? '' }})`)}</textarea>
                                                <small class="form-text text-muted">Default: Exam untuk [Perusahaan] (Pax: [Jumlah])</small>
                                            </div>
                                        `);

                if (val('jam_mulai') && val('jam_selesai')) {
                    $("#jamMulai, #jamSelesai").trigger('change');
                }

                kelolaModal.show();
            }

            function openModalDetail(data) {
                $("#modalDetailRKMLabel").text("Detail Ruang " + data.ruang);

                const formatTanggal = (tanggal) => {
                    if (!tanggal || tanggal === '-') return '-';
                    const m = moment(tanggal);
                    return m.isValid() ? m.format('dddd, D MMMM YYYY') : '-';
                };

                const tanggalAwal = formatTanggal(data.tanggal_awal);
                const tanggalAkhir = formatTanggal(data.tanggal_akhir);

                $("#modalDetailRKM #bodyContent").html(`
                                                <div class="container-fluid">
                                                    <div class="row g-3">
                                                        ${field("Nama Ruangan", `Ruang ${data.ruang}`)}
                                                        ${field("Tanggal Awal", tanggalAwal)}
                                                        ${field("Tanggal Akhir", tanggalAkhir)}
                                                        ${field("Materi", data.materi)}
                                                        ${field("Sales", data.sales)}
                                                        ${field("Instruktur 1", data.instruktur)}
                                                        ${field("Instruktur 2", data.instruktur2)}
                                                        ${field("Asisten", data.asisten)}
                                                        ${field("Perusahaan", data.perusahaan)}
                                                        ${field("Pax", data.pax)}
                                                        ${field("Exam", data.exam)}
                                                        ${field("Authorize", data.authorize)}
                                                    </div>
                                                </div>
                                            `);
            }

            function field(label, value) {
                return `
                                            <div class="col-md-6">
                                                <label class="form-label text-muted small">${label}</label>
                                                <input type="text" class="form-control form-control-sm" value="${value ?? '-'}" readonly>
                                            </div>
                                        `;
            }

            $(document).on("change", "#jamMulai, #jamSelesai", function () {
                let mulai = $("#jamMulai").val();
                let selesai = $("#jamSelesai").val();
                if (mulai && selesai) {
                    let start = moment(mulai, "HH:mm");
                    let end = moment(selesai, "HH:mm");
                    let diff = end.diff(start, "minutes");
                    if (diff < 60) {
                        $("#errorMsg").removeClass("d-none");
                        $("#jamSelesai").val("");
                    } else {
                        $("#errorMsg").addClass("d-none");
                    }
                }
            });

            $(document).on("click", ".btn-kelola", function () {
                let ruang = $(this).data("ruang");

                let existingData = {
                    id: $(this).data("id") || null,
                    tanggal: $(this).data("tanggal") || null,
                    jam_mulai: $(this).data("jam-mulai") || null,
                    jam_selesai: $(this).data("jam-selesai") || null,
                    kebutuhan: $(this).data("kebutuhan") || null,
                    keterangan: $(this).data("keterangan") || null,
                    is_exam: $(this).data("is-exam") || 0,
                    exam_id: $(this).data("exam-id") || null
                };

                if (isExamMode) {
                    openModalKelolaExam(ruang, existingData);
                } else {
                    openModalKelola(ruang, existingData);
                }
            });

            $(document).on("click", ".btn-detail", function () {
                let data = {
                    ruang: $(this).data("ruang"),
                    tanggal_awal: $(this).data("tanggal-awal"),
                    tanggal_akhir: $(this).data("tanggal-akhir"),
                    materi: $(this).data("materi"),
                    sales: $(this).data("sales"),
                    instruktur: $(this).data("instruktur"),
                    instruktur2: $(this).data("instruktur2"),
                    asisten: $(this).data("asisten"),
                    perusahaan: $(this).data("perusahaan"),
                    pax: $(this).data("pax"),
                    exam: $(this).data("exam"),
                    authorize: $(this).data("authorize")
                };
                openModalDetail(data);
            });

            $(document).on("click", ".btn-batalkan", function () {
                const id = $(this).data("id");
                const ruang = $(this).data("ruang");
                const tanggal = $(this).data("tanggal");

                if (!confirm(`Batalkan penggunaan Ruang ${ruang} pada ${tanggal}?\nTindakan ini tidak dapat dibatalkan.`)) return;

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

                $.ajax({
                    url: `/management-kelas/${id}/batalkan`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            const container = $(`#container-ruang-${ruang}`);
                            loadContainer(container, ruang, [$("#filterStart").val()]);
                        } else {
                            alert('Error: ' + res.message);
                            btn.prop('disabled', false).html('<i class="fas fa-ban"></i> Batalkan');
                        }
                    },
                    error: function (xhr) {
                        alert('Gagal: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan sistem'));
                        btn.prop('disabled', false).html('<i class="fas fa-ban"></i> Batalkan');
                    }
                });
            });

            $('#modalKelolaManagement').on('hidden.bs.modal', function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('#formKelolaManagement')[0].reset();
                $('#formKelolaManagement').attr('action', "{{ route('managementKelas.store') }}");
                $('#formKelolaManagement').find('input[name="_method"]').remove();
            });

        </script>
    @endpush
@endsection