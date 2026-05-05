@extends('layouts.app')

@section('content')
@php
    // Variabel dikirim dari controller
    $daftarPeserta = $perusahaan ? $perusahaan->peserta : [];
    $idPerusahaan = $perusahaan ? $perusahaan->id : '';
@endphp

<div class="container-fluid">
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="spinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="cube">
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_y"></div>
                <div class="cube_item cube_x"></div>
                <div class="cube_item cube_z"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahPesertaModal" tabindex="-1" aria-labelledby="tambahPesertaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahPesertaModalLabel">Tambah Peserta Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formTambahPeserta">
                        @csrf
                        <input type="hidden" name="id_perusahaan" value="{{ $idPerusahaan }}">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tabelPesertaBaru">
                                <thead>
                                    <tr>
                                        <th>Nama Lengkap <span class="text-danger">*</span></th>
                                        <th>Jenis Kelamin <span class="text-danger">*</span></th>
                                        <th>Email <span class="text-danger">*</span></th>
                                        <th>No. HP <span class="text-danger">*</span></th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="peserta[0][nama]" class="form-control" required></td>
                                        <td>
                                            <select name="peserta[0][jenis_kelamin]" class="form-control" required>
                                                <option value="">Pilih</option>
                                                <option value="L">Laki-laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </td>
                                        <td><input type="email" name="peserta[0][email]" class="form-control" required></td>
                                        <td><input type="text" name="peserta[0][no_hp]" class="form-control" required></td>
                                        <td><button type="button" class="btn btn-danger btn-sm btn-hapus-baris" disabled><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" id="btnTambahBaris"><i class="fas fa-plus"></i> Tambah Baris</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanPeserta">Simpan & Pilih</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card m-4 p-3">
                <div class="card-body">
                    <h3 class="card-title text-center my-3">{{ __('Daftarkan Peserta Exam') }}</h3>

                    <form action="{{ route('daftar-peserta-exam.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_exam" value="{{ $exam->id }}">

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label fw-bold" for="selectAll">
                                        {{ __('Pilih Semua') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#tambahPesertaModal">
                                    <i class="fas fa-user-plus"></i> Tambah Peserta Baru
                                </button>
                            </div>
                            <div class="col-md-12"><hr></div>
                        </div>

                        <div class="row" id="containerPeserta">
                            @forelse ($daftarPeserta as $peserta)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input peserta-checkbox"
                                            type="checkbox"
                                            name="peserta_id[]"
                                            value="{{ $peserta->id }}"
                                            id="peserta_{{ $peserta->id }}"
                                        >
                                        <label class="form-check-label" for="peserta_{{ $peserta->id }}">
                                            {{ $peserta->nama }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-md-12" id="emptyStateContainer">
                                    <div class="alert alert-info">
                                        {{ __('Tidak ada peserta yang tersedia pada perusahaan ini. Silakan tambah peserta baru.') }}
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> {{ __('Daftarkan ke Exam') }}
                                </button>
                                <a href="{{ redirect()->back()->getTargetUrl() }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> {{ __('Batal') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const containerPeserta = document.getElementById('containerPeserta');
    let pesertaCheckboxes = document.querySelectorAll('.peserta-checkbox');

    function attachCheckboxEvents() {
        pesertaCheckboxes = document.querySelectorAll('.peserta-checkbox');
        pesertaCheckboxes.forEach(checkbox => {
            checkbox.removeEventListener('change', updateSelectAllState);
            checkbox.addEventListener('change', updateSelectAllState);
        });
    }

    function updateSelectAllState() {
        if (pesertaCheckboxes.length === 0) return;
        const allChecked = Array.from(pesertaCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(pesertaCheckboxes).some(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
    }

    selectAllCheckbox.addEventListener('change', function() {
        pesertaCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    attachCheckboxEvents();

    let barisIndex = 1;

    $('#btnTambahBaris').click(function() {
        let newRow = `
            <tr>
                <td><input type="text" name="peserta[${barisIndex}][nama]" class="form-control" required></td>
                <td>
                    <select name="peserta[${barisIndex}][jenis_kelamin]" class="form-control" required>
                        <option value="">Pilih</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </td>
                <td><input type="email" name="peserta[${barisIndex}][email]" class="form-control" required></td>
                <td><input type="text" name="peserta[${barisIndex}][no_hp]" class="form-control" required></td>
                <td><button type="button" class="btn btn-danger btn-sm btn-hapus-baris"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#tabelPesertaBaru tbody').append(newRow);
        barisIndex++;
    });

    $(document).on('click', '.btn-hapus-baris', function() {
        $(this).closest('tr').remove();
    });

    $('#btnSimpanPeserta').click(function() {
        let form = $('#formTambahPeserta');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        let formData = form.serialize();

        $.ajax({
            url: "{{ route('daftar-peserta-exam.storeAjax') }}",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $('#loadingModal').modal('show');
                $('#tambahPesertaModal').modal('hide');
            },
            success: function(response) {
                if(response.success) {
                    $('#emptyStateContainer').remove();

                    response.data.forEach(function(peserta) {
                        let newCheckbox = `
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="form-check">
                                    <input
                                        class="form-check-input peserta-checkbox"
                                        type="checkbox"
                                        name="peserta_id[]"
                                        value="${peserta.id}"
                                        id="peserta_${peserta.id}"
                                        checked
                                    >
                                    <label class="form-check-label" for="peserta_${peserta.id}">
                                        ${peserta.nama}
                                    </label>
                                </div>
                            </div>
                        `;
                        $('#containerPeserta').append(newCheckbox);
                    });

                    form[0].reset();
                    $('#tabelPesertaBaru tbody tr:not(:first)').remove();
                    barisIndex = 1;

                    attachCheckboxEvents();
                    updateSelectAllState();
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Terjadi kesalahan pada server. Pastikan format email unik dan data benar.');
            },
            complete: function() {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                }, 500);
            }
        });
    });
});
</script>
@endsection
