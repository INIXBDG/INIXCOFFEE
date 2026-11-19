(function() {
    'use strict';

    let dataTableInstance = null;

    function formatNumber(value) {
        if (!value && value !== '0') return '';
        const raw = String(value).replace(/[^0-9]/g, '');
        if (!raw) return '';
        return new Intl.NumberFormat('id-ID').format(raw);
    }

    function getRawNumber(value) {
        if (!value) return '';
        return String(value).replace(/[^0-9]/g, '');
    }

    function updateEditNilaiPlaceholder() {
        const tipe = $('#edit_tipe_target').val();
        const input = $('#edit_nilai_target');
        if (tipe === 'rupiah') {
            input.attr('placeholder', 'Contoh: 10000000');
        } else if (tipe === 'persen') {
            input.attr('placeholder', 'Contoh: 85');
        } else {
            input.attr('placeholder', 'Contoh: 100');
        }
    }

    function openModalEdit(item) {
        $('#editDataTargetId').val(item.id);
        $('#edit_asistant_route').val(item.asistant_route);
        $('#edit_jangka_target').val(item.jangka_target);
        $('#edit_tipe_target').val(item.tipe_target);
        $('#edit_nilai_target').val(item.nilai_target);
        updateEditNilaiPlaceholder();
        $('#modalEditDataTarget').modal('show');
    }

    function confirmDelete(id, name) {
        if (typeof Swal === 'undefined') {
            if (confirm(`Anda akan menghapus konfigurasi "${name}". Tindakan ini tidak dapat diurungkan.`)) {
                performDelete(id);
            }
            return;
        }

        Swal.fire({
            title: 'Hapus Data Target?',
            text: `Anda akan menghapus konfigurasi "${name}". Tindakan ini tidak dapat diurungkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                performDelete(id);
            }
        });
    }

    function performDelete(id) {
        $.ajax({
            url: window.KPI_CONFIG.deleteRoute.replace('REPLACE_ID', id),
            type: 'DELETE',
            data: {
                _token: window.KPI_CONFIG.csrfToken
            },
            success: function(res) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                } else {
                    alert(res.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal menghapus data';
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', msg, 'error');
                } else {
                    alert(msg);
                }
            }
        });
    }

    $(document).ready(function() {
        if ($.fn.DataTable) {
            dataTableInstance = $('#tableDataTarget').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                columnDefs: [{
                    orderable: false,
                    targets: [5]
                }],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data yang tersedia",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Berikutnya"
                    },
                    emptyTable: "Belum ada data target yang dikonfigurasi",
                    zeroRecords: "Tidak ditemukan data yang cocok"
                },
                initComplete: function() {
                    const skeleton = document.getElementById('skeletonDataTarget');
                    if (skeleton) skeleton.classList.add('d-none');
                }
            });
        } else {
            setTimeout(() => {
                const skeleton = document.getElementById('skeletonDataTarget');
                if (skeleton) skeleton.classList.add('d-none');
            }, 800);
        }

        $('#formImportData').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Memproses Import',
                    text: 'Sedang mengimport data, silakan tunggu...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            $.ajax({
                url: window.KPI_CONFIG.importRoute,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    } else {
                        alert(res.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal mengimport data';
                    if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', msg, 'error');
                    } else {
                        alert(msg);
                    }
                }
            });
        });

        $('#formDataTargetEdit').on('submit', function(e) {
            e.preventDefault();
            const id = $('#editDataTargetId').val();
            const formData = $(this).serialize();

            $.ajax({
                url: window.KPI_CONFIG.updateRoute.replace('REPLACE_ID', id),
                type: 'PUT',
                data: formData,
                success: function(res) {
                    $('#modalEditDataTarget').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    } else {
                        alert(res.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan';
                    if (xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', msg, 'error');
                    } else {
                        alert(msg);
                    }
                }
            });
        });

        $('#edit_nilai_target').on('input', function() {
            const tipe = $('#edit_tipe_target').val();
            let val = $(this).val().replace(/\D/g, '');
            if (!val) {
                $(this).val('');
                return;
            }
            $(this).val(new Intl.NumberFormat('id-ID').format(parseInt(val)));
        });

        $(document).on('click', '.btn-edit-target', function() {
            const item = $(this).data('item');
            openModalEdit(item);
        });

        $(document).on('click', '.btn-delete-target', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            confirmDelete(id, name);
        });
    });

    window.openModalEdit = openModalEdit;
    window.confirmDelete = confirmDelete;
    window.updateEditNilaiPlaceholder = updateEditNilaiPlaceholder;
})();