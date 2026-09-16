$(document).ready(function() {
    $("#tahun").val(new Date().getFullYear());
    loadData();

    $("#tahun").on("change", function() {
        loadData();
    });
});

function loadData() {
    const tahun = $("#tahun").val();
    $('#dataform-penilaian-skeleton-container').show();
    $('#dataform-penilaian-real-container').hide();

    $.ajax({
        url: window.penilaianConfig.routes.getForm,
        type: 'get',
        data: { tahun: tahun },
        success: function(response) {
            const data = response.data ?? [];
            const content = $('#dataform-penilaian-tbody');
            content.empty();

            if ($.fn.DataTable.isDataTable('#table_penilaian')) {
                $('#table_penilaian').DataTable().destroy();
            }

            if (data.length === 0) {
                content.append(`
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                                <span class="text-muted">Tidak ada Form!</span>
                            </div>
                        </td>
                    </tr>
                `);
            } else {
                data.forEach(function(item, index) {
                    let evaluatedArr = [];
                    if (Array.isArray(item.evaluated)) {
                        evaluatedArr = item.evaluated.map(e => e.nama);
                    } else if (item.evaluated) {
                        evaluatedArr = [item.evaluated.nama];
                    }

                    let badgesHtml = '';
                    const maxShow = 3;
                    const showList = evaluatedArr.slice(0, maxShow);

                    showList.forEach(nama => {
                        badgesHtml += `<span class="dataform-penilaian-evaluated-badge">${nama}</span>`;
                    });

                    let moreLink = '';
                    if (evaluatedArr.length > maxShow) {
                        moreLink = `<a href="javascript:void(0)" class="dataform-penilaian-show-more-link dataform-penilaian-show-more" data-full='${JSON.stringify(evaluatedArr)}'>+${evaluatedArr.length - maxShow} lainnya</a>`;
                    }

                    content.append(`
                        <tr>
                            <td class="fw-semibold text-muted">${index + 1}</td>
                            <td><code class="bg-light px-2 py-1 rounded">${item.label_kode_form}</code></td>
                            <td>
                                <div class="dataform-penilaian-evaluated-list">
                                    ${badgesHtml}
                                    ${moreLink}
                                </div>
                            </td>
                            <td>${item.tahun}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a class="dataform-penilaian-btn-table-action" href="/penilaian/data-form/edit/${item.kode_form}" title="Edit Form">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button" class="dataform-penilaian-btn-table-action dataform-penilaian-btn-lihat-evaluator"
                                            data-kode="${item.kode_form}"
                                            data-label="${item.label_kode_form}"
                                            title="Lihat & Perbaiki Evaluator">
                                        <i class="fa-solid fa-users-gear"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                const dataTable = $('#table_penilaian').DataTable({
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                    responsive: true,
                    scrollX: true,
                    scrollCollapse: true,
                    autoWidth: false,
                    dom: "<'row mb-2'<'col-md-6 dataform-penilaian-dt-length'l><'col-md-6 text-end dataform-penilaian-dt-search'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row mt-2'<'col-md-5 dataform-penilaian-dt-info'i><'col-md-7 dataform-penilaian-dt-pagination'p>>",
                    language: {
                        search: "",
                        searchPlaceholder: "Cari data...",
                        lengthMenu: "_MENU_ per halaman",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
                        infoEmpty: "Tidak ada data",
                        zeroRecords: "Data tidak ditemukan",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "›",
                            previous: "‹"
                        }
                    }
                });

                setTimeout(function() {
                    dataTable.columns.adjust().draw(false);
                }, 50);
            }
            $('#dataform-penilaian-skeleton-container').hide();
            $('#dataform-penilaian-real-container').show();
        },
        error: function(xhr, status, error) {
            console.error('Error loading data:', error);
            $('#dataform-penilaian-skeleton-container').hide();
            $('#dataform-penilaian-real-container').show();
            $('#dataform-penilaian-tbody').html(`
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fa-solid fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <span class="text-danger">Gagal memuat data</span>
                        </div>
                    </td>
                </tr>
            `);
        }
    });
}

$(document).on("click", ".dataform-penilaian-show-more", function() {
    const full = JSON.parse($(this).attr("data-full"));
    let listHtml = '<ul class="dataform-penilaian-evaluated-modal-list">';
    full.forEach((nama, index) => {
        listHtml += `
            <li>
                <span class="dataform-penilaian-badge-number">${index + 1}</span>
                <span class="fw-semibold text-dark">${nama}</span>
            </li>
        `;
    });
    listHtml += "</ul>";
    $("#evaluatedContent").html(listHtml);
    $("#modalEvaluated").modal("show");
});

let currentListDivisi = [];

$(document).on('click', '.dataform-penilaian-btn-lihat-evaluator', function () {
    const kodeForm = $(this).data('kode');
    const label    = $(this).data('label');

    $('#modalKodeFormLabel').text(`(${label})`);
    $('#modalEvaluator').modal('show');

    $('#evaluatorLoading').removeClass('d-none');
    $('#evaluatorContent, #evaluatorEmpty').addClass('d-none');
    $('#bodyEvaluator').empty();

    $.ajax({
        url: window.penilaianConfig.routes.getEvaluators,
        type: 'GET',
        data: { kode_form: kodeForm },
        success: function (res) {
            $('#evaluatorLoading').addClass('d-none');
            currentListDivisi = res.list_divisi || [];

            if (!res.evaluators || res.evaluators.length === 0) {
                $('#evaluatorEmpty').removeClass('d-none');
                return;
            }

            $('#evaluatorContent').removeClass('d-none');

            res.evaluators.forEach((item, index) => {
                let options = `<option value="">-- Pilih Divisi --</option>`;
                currentListDivisi.forEach(div => {
                    const selected = div === item.divisi_evaluator ? 'selected' : '';
                    options += `<option value="${div}" ${selected}>${div}</option>`;
                });

                if (item.divisi_evaluator && !currentListDivisi.includes(item.divisi_evaluator)) {
                    options += `<option value="${item.divisi_evaluator}" selected>${item.divisi_evaluator} (lama)</option>`;
                }

                $('#bodyEvaluator').append(`
                    <tr data-id="${item.id}">
                        <td>${index + 1}</td>
                        <td>
                            <div class="fw-semibold">${item.nama_evaluator}</div>
                            <small class="text-muted">Evaluated: ${item.nama_evaluated}</small>
                        </td>
                        <td><span class="badge bg-secondary">${item.jabatan}</span></td>
                        <td><small>${item.jenis_penilaian}</small></td>
                        <td>
                            <span class="badge bg-primary dataform-penilaian-current-divisi">${item.divisi_evaluator || '-'}</span>
                        </td>
                        <td>
                            <select class="form-select form-select-sm dataform-penilaian-select-divisi">
                                ${options}
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-success dataform-penilaian-btn-simpan-divisi" title="Simpan">
                                <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        },
        error: function () {
            $('#evaluatorLoading').addClass('d-none');
            Swal.fire('Error', 'Gagal memuat data evaluator', 'error');
        }
    });
});

$(document).on('click', '.dataform-penilaian-btn-simpan-divisi', function () {
    const $row = $(this).closest('tr');
    const id = $row.data('id');
    const newDivisi = $row.find('.dataform-penilaian-select-divisi').val();

    if (!newDivisi) {
        Swal.fire('Peringatan', 'Pilih divisi terlebih dahulu', 'warning');
        return;
    }

    const $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

    $.ajax({
        url: window.penilaianConfig.routes.updateDivisiEvaluator,
        type: 'POST',
        data: {
            _token: window.penilaianConfig.csrfToken,
            id: id,
            divisi_evaluator: newDivisi
        },
        success: function (res) {
            if (res.success) {
                $row.find('.dataform-penilaian-current-divisi')
                    .text(newDivisi)
                    .removeClass('bg-primary')
                    .addClass('bg-success');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        },
        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Gagal menyimpan';
            Swal.fire('Error', msg, 'error');
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i>');
        }
    });
});