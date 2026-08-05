    @extends('layouts.app')

    @section('content')
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body" id="card">
                            <a href="/lembur" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}"
                                    class="img-responsive" width="20px"> Back</a>
                            <h5 class="card-title text-center mb-4">{{ __('Perintah Lembur') }}</h5>
                            <form method="POST" action="{{ route('lembur.store') }}" id="formCreateLembur">
                                @csrf
                                <div class="row mb-3">
                                    <label for="backup_karyawan"
                                        class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                                    <div class="col-md-6">
                                        <select name="id_karyawan" id="id_karyawan" class="form-select">
                                            <option value="-">Pilih Karyawan</option>
                                            @foreach ($karyawanall as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_lengkap }}</option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback d-block" role="alert" id="error-id_karyawan"></span>
                                        @error('tipe')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="divisi"
                                        class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                                    <div class="col-md-6">
                                        <input disabled id="divisi" type="text" placeholder="Masukan Divisi"
                                            class="form-control @error('divisi') is-invalid @enderror" name="divisi"
                                            value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                                        @error('divisi')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                @php
                                    $today = \Carbon\Carbon::now()->toDateString(); // Format tanggal menjadi YYYY-MM-DD
                                @endphp

                                <div class="row mb-3" id="row_tanggal_spl">
                                    <label for="tanggal_spl"
                                        class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Perintah Lembur') }}</label>
                                    <div class="col-md-6">
                                        <input type="date" readonly class="form-control" name="tanggal_spl"
                                            id="tanggal_spl" value="{{ $today }}">
                                        <span class="invalid-feedback d-block" role="alert" id="error-tanggal_spl"></span>
                                        @error('tanggal_spl')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="row mb-3" id="row_uraian_tugas">
                                    <label for="uraian_tugas"
                                        class="col-md-4 col-form-label text-md-start">{{ __('Uraian Tugas') }}</label>
                                    <div class="col-md-6">
                                        <textarea name="uraian_tugas" class="form-control" id="uraian_tugas" cols="51" rows="5"></textarea>
                                        <span class="invalid-feedback d-block" role="alert"
                                            id="error-uraian_tugas"></span>
                                        @error('uraian_tugas')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3" id="row_waktu_lembur">
                                    <label for="waktu_lembur"
                                        class="col-md-4 col-form-label text-md-start">{{ __('Waktu Lembur') }}</label>
                                    <div class="col-md-6">
                                        <select name="waktu_lembur" id="waktu_lembur" class="form-select">
                                            <option value="-">Pilih Waktu Lembur</option>
                                            <option value="Kerja">Hari Kerja</option>
                                            <option value="Libur">Hari Libur</option>
                                        </select>
                                        <span class="invalid-feedback d-block" role="alert"
                                            id="error-waktu_lembur"></span>
                                        @error('waktu_lembur')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3" id="row_tanggal_lembur">
                                    <label for="tanggal_lembur"
                                        class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Lembur') }}</label>
                                    <div class="col-md-6">
                                        <input type="date" class="form-control" name="tanggal_lembur" id="tanggal_lembur"
                                            value="">
                                        <span class="invalid-feedback d-block" role="alert"
                                            id="error-tanggal_lembur"></span>
                                        @error('tanggal_lembur')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="btn click-primary">
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
            $(document).ready(function() {

                // Fungsi untuk membersihkan semua error inline
                function clearAllErrors(form) {
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('[id^="error-"]').html('').hide();
                }

                // Fungsi untuk menampilkan error di bawah field tertentu
                function showError(form, fieldName, message) {
                    const $field = form.find('[name="' + fieldName + '"]');
                    const $errorSpan = form.find('#error-' + fieldName);

                    $field.addClass('is-invalid');
                    $errorSpan.html('<strong>' + message + '</strong>').show();
                }

                // Fungsi untuk cek apakah field kosong
                function isEmpty(value) {
                    if (value === null || value === undefined) return true;
                    return value.toString().trim() === '' || value === '-';
                }

                // Event Handler Submit Form
                $('#formCreateLembur').on('submit', function(e) {
                    const form = $(this);

                    // Hapus semua error dari submit sebelumnya
                    clearAllErrors(form);

                    let isValid = true;
                    let firstErrorField = null;
                    const validationErrorMessages = [];

                    // Ambil nilai berdasarkan atribut 'name' untuk menghindari bug ID duplikat
                    const idKaryawan = form.find('select[name="id_karyawan"]').val();
                    const tanggalSpl = form.find('input[name="tanggal_spl"]').val();
                    const uraianTugas = form.find('textarea[name="uraian_tugas"]').val() ? form.find(
                        'textarea[name="uraian_tugas"]').val().trim() : '';
                    const waktuLembur = form.find('select[name="waktu_lembur"]').val();
                    const tanggalLembur = form.find('input[name="tanggal_lembur"]').val();

                    // 1. Validasi Nama Karyawan
                    if (isEmpty(idKaryawan)) {
                        showError(form, 'id_karyawan', 'Nama Karyawan wajib dipilih.');
                        validationErrorMessages.push('Nama Karyawan wajib dipilih.');
                        isValid = false;
                        if (!firstErrorField) firstErrorField = 'id_karyawan';
                    }

                    // 2. Validasi Tanggal Perintah Lembur
                    if (isEmpty(tanggalSpl)) {
                        showError(form, 'tanggal_spl', 'Tanggal Perintah Lembur wajib diisi.');
                        validationErrorMessages.push('Tanggal Perintah Lembur wajib diisi.');
                        isValid = false;
                        if (!firstErrorField) firstErrorField = 'tanggal_spl';
                    }

                    // 3. Validasi Uraian Tugas
                    if (isEmpty(uraianTugas)) {
                        showError(form, 'uraian_tugas', 'Uraian Tugas wajib diisi.');
                        validationErrorMessages.push('Uraian Tugas wajib diisi.');
                        isValid = false;
                        if (!firstErrorField) firstErrorField = 'uraian_tugas';
                    }

                    // 4. Validasi Waktu Lembur
                    if (isEmpty(waktuLembur)) {
                        showError(form, 'waktu_lembur', 'Waktu Lembur wajib dipilih.');
                        validationErrorMessages.push('Waktu Lembur wajib dipilih.');
                        isValid = false;
                        if (!firstErrorField) firstErrorField = 'waktu_lembur';
                    }

                    // 5. Validasi Tanggal Lembur
                    if (isEmpty(tanggalLembur)) {
                        showError(form, 'tanggal_lembur', 'Tanggal Lembur wajib diisi.');
                        validationErrorMessages.push('Tanggal Lembur wajib diisi.');
                        isValid = false;
                        if (!firstErrorField) firstErrorField = 'tanggal_lembur';
                    }

                    // Jika ada error, cegah submit, tampilkan popup, dan scroll ke error pertama
                    if (!isValid) {
                        e.preventDefault();
                        if (validationErrorMessages.length) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: validationErrorMessages.map(msg => '<p>' + msg + '</p>').join(''),
                                confirmButtonText: 'Tutup'
                            });
                        }
                        if (firstErrorField) {
                            $('html, body').animate({
                                scrollTop: form.find('[name="' + firstErrorField + '"]').offset().top -
                                    100
                            }, 500);
                        }
                        return false;
                    }

                    // Jika valid, form akan submit normal ke server
                });

                // Hapus error real-time saat user mengisi field
                $('#formCreateLembur').on('input change', 'input, select, textarea', function() {
                    const $field = $(this);
                    if ($field.hasClass('is-invalid')) {
                        $field.removeClass('is-invalid');
                        const fieldName = $field.attr('name');
                        if (fieldName) {
                            $('#error-' + fieldName).html('').hide();
                        }
                    }
                });

                @if(session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: {!! json_encode(session('success')) !!},
                        confirmButtonText: 'Tutup'
                    });
                @endif

                @if(session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: {!! json_encode(session('error')) !!},
                        confirmButtonText: 'Tutup'
                    });
                @endif
            });
        </script>

        <style>

        </style>
    @endsection
