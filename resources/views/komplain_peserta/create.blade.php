@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card mb-5">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Tambah Komplain Peserta') }}</h5>
                    <form method="POST" action="{{ route('storeKomplain') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="feedback" class="col-md-3 col-form-label text-md-start">{{ __('Feedback') }}</label>
                            <div class="col-md-6">
                                <select class="form-select" name="feedback" id="feedback">
                                    <option value="" disabled selected hidden></option>
                                    @foreach ($feedbacks as $feedback)
                                        <option value="{{ $feedback->nilaifeedback_id }}">{{ $feedback->nama_perusahaan }} || {{ $feedback->nama_materi }} || {{ $feedback->tanggal_akhir }}</option>
                                    @endforeach
                                </select>
                                @error('feedback')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="detailFeedbackWrapper" style="display:none;">
                            <label for="created_at" class="col-md-3 col-form-label text-md-start">{{ __('Detail Feedback') }}</label>
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table id="detailFeedback" class="table table-striped table-hover table-bordered table-sm">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Materi</th>
                                                <th>Pelayanan</th>
                                                <th>Fasilitas</th>
                                                <th>Instruktur</th>
                                                <th>Instruktur 2</th>
                                                <th>Asisten</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="umum1" class="col-md-3 col-form-label text-md-start">{{ __('Umum 1') }}</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="umum1" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <label for="umum2" class="col-md-3 col-form-label text-md-start">{{ __('Umum 2') }}</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="umum2" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="contaniner" id="wrapper">
                            <div class="row mb-3">
                                <label for="kategori[]" class="col-md-3 col-form-label text-md-start">{{ __('Kategori') }}</label>
                                <div class="col-md-6">
                                    <select class="form-select" name="kategori[]" id="kategori[]">
                                        <option value="" disabled selected hidden></option>
                                        <option value="Materi">Materi</option>
                                        <option value="Pelayanan">Pelayanan</option>
                                        <option value="Fasilitas Laboratorium">Fasilitas Laboratorium</option>
                                        <option value="Instruktur">Instruktur</option>
                                        <option value="Umum">Umum</option>
                                        <option value="Sales">Sales</option>
                                        <option id="lainnya" value="lainnya">Lainnya</option>
                                    </select>
                                    @error('kategori[]')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3" id="kategoriLainnyaWrapper" style="display:none;">
                                <label for="kategori_lainnya[]" class="col-md-3 col-form-label text-md-start">{{ __('Kategori Lainnya') }}</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="kategori_lainnya[]" id="kategori_lainnya[]">
                                    @error('kategori_lainnya[]')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="komplain[]" class="col-md-3 col-form-label text-md-start">{{ __('Komplain') }}</label>
                                <div class="col-md-6">
                                    <textarea type="text" required class="form-control komplain" name="komplain[]" id="komplain[]" rows="2" cols="20" wrap="off" style="overflow: hidden; resize: horizontal"> </textarea>
                                    @error('komplain[]')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4 d-flex gap-2 justify-content-between">
                                <button type="button" id="tambahKomplain" class="btn click-primary">
                                    {{ __('+ Komplain') }}
                                </button>

                                <button type="submit" class="btn click-primary p-2">
                                    {{ __('Simpan') }}
                                </button>
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
    document.addEventListener('DOMContentLoaded', function () {
        const kategoriSelect = document.getElementById('kategori[]');
        const kategoriLainnya = document.getElementById('kategoriLainnyaWrapper');

        kategoriSelect.addEventListener('change', function () {
            if (this.value === 'lainnya') {
                kategoriLainnya.style.display = 'flex';
            } else {
                kategoriLainnya.style.display = 'none';
                document.getElementById('kategori_lainnya[]').value = '';
            }
        });

        $(document).ready(function() {
            $('#tambahKomplain').click(function() {
                addKomplain();
            });

            function addKomplain() {
                const index = $('.komplain-item').length;

                const newKomplain = `
                    <div class="komplain-item position-relative">
                        <div class="d-flex justify-content-end position-absolute" style="right: 5%">
                            <button type="button" class="btn btn-sm btn-danger btn-hapus">
                                Hapus
                            </button>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Kategori</label>
                            <div class="col-md-6">
                                <select class="form-select kategori-select" name="kategori[]">
                                    <option value="" disabled selected hidden></option>
                                    <option value="Materi">Materi</option>
                                    <option value="Pelayanan">Pelayanan</option>
                                    <option value="Fasilitas Laboratorium">Fasilitas Laboratorium</option>
                                    <option value="Instruktur">Instruktur</option>
                                    <option value="Umum">Umum</option>
                                    <option value="Sales">Sales</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3 kategori-lainnya-wrapper" style="display:none;">
                            <label class="col-md-3 col-form-label">Kategori Lainnya</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="kategori_lainnya[]">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label">Komplain</label>
                            <div class="col-md-6">
                                <textarea class="form-control komplain" name="komplain[]" rows="2" required></textarea>
                            </div>
                        </div>
                    </div>
                `;

                $('#wrapper').append(newKomplain);
            }

            $(document).on('click', '.btn-hapus', function () {
                $(this).closest('.komplain-item').remove();
            });

            $(document).on('change', '.kategori-select', function () {
                const wrapper = $(this).closest('.komplain-item')
                    .find('.kategori-lainnya-wrapper');

                if ($(this).val() === 'lainnya') {
                    wrapper.show();
                } else {
                    wrapper.hide();
                    wrapper.find('input').val('');
                }
            });

        })

        $('#feedback').on('change', function () {
            let id = $(this).val();
            if (!id) return;

            $.ajax({
                url: "{{ route('dataNilaiPenilaian', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function (res) {

                    let row = `
                        <tr>
                            <td>${res.materi ?? '-'}</td>
                            <td>${res.pelayanan ?? '-'}</td>
                            <td>${res.fasilitas ?? '-'}</td>
                            <td>${res.instruktur ?? '-'}</td>
                            <td>${res.instruktur2 ?? '-'}</td>
                            <td>${res.asisten ?? '-'}</td>
                        </tr>
                    `;

                    $('#detailFeedback tbody').html(row);
                    $('#detailFeedbackWrapper').slideDown();
                    $('#umum1').val(res.umum1 ?? '');
                    $('#umum2').val(res.umum2 ?? '');
                },
                error: function () {
                    $('#detailFeedback tbody').html(`
                        <tr>
                            <td colspan="6" class="text-center text-danger">
                                Gagal memuat data
                            </td>
                        </tr>
                    `);
                }
            });
        });
    });
</script>
@endsection
