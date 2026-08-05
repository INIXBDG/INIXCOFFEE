    @extends('layouts.app')

    @section('content')
    @php
        $jabatan = strtolower(optional(auth()->user())->jabatan ?? '');
        $editableJabatans = ['Education Manager', 'GM', 'SPV Sales', 'Office Manager', 'Koordinator Office', 'HRD', 'Koordinator ITSM'];
        $isEditable = in_array($jabatan, array_map('strtolower', $editableJabatans));
    @endphp
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body" id="card">
                    <a href="{{ route('lembur.index') }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Perintah Lembur') }}</h5>
                        <form method="POST" action="{{ route('lembur.updateKaryawan', $data->id) }}" enctype="multipart/form-data" id="formEditLembur">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <label for="backup_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                                <div class="col-md-6">
                                    <select name="id_karyawan" id="id_karyawan" class="form-select" disabled>
                                        <option value="-">Pilih Karyawan</option>
                                        @foreach ($karyawanall as $item)
                                            <option value="{{$item->id}}" @if($item->id == $data->id_karyawan) selected @endif>{{$item->nama_lengkap}}</option>
                                        @endforeach
                                    </select>
                                    @error('tipe')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                                <div class="col-md-6">
                                    <input disabled id="divisi" type="text" placeholder="Masukan Divisi" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                                    @error('divisi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3" id="row_tanggal_spl">
                                <label for="tanggal_spl" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Perintah Lembur') }}</label>
                                <div class="col-md-6">
                                    <input type="date"
                                        class="form-control"
                                        name="tanggal_spl"
                                        id="tanggal_spl"
                                        value="{{ $data->tanggal_spl }}"
                                        @unless($isEditable) readonly @endunless>
                                    <span class="invalid-feedback d-block" role="alert" id="error-tanggal_spl"></span>
                                    @error('tanggal_spl')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <label for="jam_mulai" class="col-md-4 col-form-label text-md-start">Jam Mulai</label>
                                <div class="col-md-6">
                                    <input
                                        type="time"
                                        class="form-control"
                                        name="jam_mulai"
                                        id="jam_mulai"
                                        value="{{ $data->jam_mulai }}"
                                    >
                                    <span class="invalid-feedback d-block" role="alert" id="error-jam_mulai"></span>
                                    @error('jam_mulai')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3" id="row_jam_selesai">
                                <label for="jam_selesai" class="col-md-4 col-form-label text-md-start">{{ __('Jam Selesai') }}</label>
                                <div class="col-md-6">
                                    <input
                                        type="time"
                                        class="form-control"
                                        name="jam_selesai"
                                        id="jam_selesai"
                                        value="{{ $data->jam_selesai }}"
                                    >
                                    <span class="invalid-feedback d-block" role="alert" id="error-jam_selesai"></span>
                                    @error('jam_selesai')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="foto_mulai" class="col-md-4 col-form-label text-md-start">Foto Mulai</label>
                                <div class="col-md-6">
                                    <input
                                        type="file"
                                        class="form-control @error('foto_mulai') is-invalid @enderror"
                                        name="foto_mulai"
                                        id="foto_mulai"
                                        accept="image/*"
                                    >
                                    <span class="invalid-feedback d-block" role="alert" id="error-foto_mulai"></span>
                                    @error('foto_mulai')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    @if(!empty($data->foto_masuk))
                                        <img src="{{ asset('storage/' . $data->foto_masuk) }}" alt="Foto Masuk" class="img-thumbnail mt-1" style="width: 150px; height: 100px; object-fit: cover;">
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="foto_selesai" class="col-md-4 col-form-label text-md-start">Foto Selesai</label>
                                <div class="col-md-6">
                                    <input
                                        type="file"
                                        class="form-control @error('foto_selesai') is-invalid @enderror"
                                        name="foto_selesai"
                                        id="foto_selesai"
                                        accept="image/*"
                                    >
                                    <span class="invalid-feedback d-block" role="alert" id="error-foto_selesai"></span>
                                    @error('foto_selesai')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    @if(!empty($data->foto_selesai))
                                        <img src="{{ asset('storage/' . $data->foto_selesai) }}" alt="Foto Selesai" class="img-thumbnail mt-1" style="width: 150px; height: 100px; object-fit: cover;">
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3" id="row_uraian_tugas">
                                <label for="uraian_tugas" class="col-md-4 col-form-label text-md-start">{{ __('Uraian Tugas') }}</label>
                                <div class="col-md-6">
                                    <textarea name="uraian_tugas"
                                            class="form-control"
                                            id="uraian_tugas"
                                            cols="51"
                                            rows="5"
                                            @unless($isEditable) disabled @endunless>{{ $data->uraian_tugas }}</textarea>
                                    <span class="invalid-feedback d-block" role="alert" id="error-uraian_tugas"></span>
                                    @error('uraian_tugas')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="waktu_lembur" class="col-md-4 col-form-label text-md-start">{{ __('Waktu Lembur') }}</label>
                                <div class="col-md-6">
                                    <select name="waktu_lembur" id="waktu_lembur" class="form-select" @unless($isEditable) disabled @endunless>
                                        <option value="-">Pilih Waktu Lembur</option>
                                        <option value="Kerja" @if($data->waktu_lembur == 'Kerja') selected @endif>Hari Kerja</option>
                                        <option value="Libur" @if($data->waktu_lembur == 'Libur') selected @endif>Hari Libur</option>
                                    </select>
                                    <span class="invalid-feedback d-block" role="alert" id="error-waktu_lembur"></span>
                                    @error('waktu_lembur')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3" id="row_tanggal_lembur">
                                <label for="tanggal_lembur" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Lembur') }}</label>
                                <div class="col-md-6">
                                    <input type="date"
                                        class="form-control"
                                        name="tanggal_lembur"
                                        id="tanggal_lembur"
                                        value="{{ $data->tanggal_lembur }}"
                                        @unless($isEditable) readonly @endunless>
                                    <span class="invalid-feedback d-block" role="alert" id="error-tanggal_lembur"></span>
                                    @error('tanggal_lembur')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3" id="row_keterangan">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Detail Tugas') }}</label>
                                <div class="col-md-6">
                                    <textarea name="keterangan" class="form-control" id="keterangan" cols="51" rows="5">{{$data->keterangan}}</textarea>
                                    <span class="invalid-feedback d-block" role="alert" id="error-keterangan"></span>
                                    @error('keterangan')
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>

    <script>
    $(document).ready(function() {

        //  Fungsi untuk membersihkan semua error inline
        function clearAllErrors(form) {
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('[id^="error-"]').html('').hide();
        }

        //  Fungsi untuk menampilkan error di bawah field tertentu
        function showError(form, fieldName, message) {
            const $field = form.find('[name="' + fieldName + '"]');
            const $errorSpan = form.find('#error-' + fieldName);

            // Tambahkan border merah pada field
            $field.addClass('is-invalid');

            // Tampilkan pesan error
            $errorSpan.html('<strong>' + message + '</strong>').show();
        }

        //  Fungsi untuk cek apakah field kosong
        function isEmpty(value) {
            if (value === null || value === undefined) return true;
            return value.toString().trim() === '' || value === '-';
        }

        // Event Handler Submit Form
        $('#formEditLembur').on('submit', function(e) {
            const form = $(this);

            // FIX PENTING: Hapus 'disabled' SEBELUM validasi agar nilai bisa dibaca
            // Field 'disabled' tidak punya value saat dibaca via jQuery
            form.find(':disabled').removeAttr('disabled');

            // Hapus semua error dari submit sebelumnya
            clearAllErrors(form);

            let isValid = true;
            let firstErrorField = null;
            const validationErrorMessages = [];

            // Ambil nilai berdasarkan atribut 'name' untuk menghindari bug ID duplikat
            const tanggalSpl = form.find('input[name="tanggal_spl"]').val();
            const jamMulai = form.find('input[name="jam_mulai"]').val();
            const jamSelesai = form.find('input[name="jam_selesai"]').val();
            const uraianTugas = form.find('textarea[name="uraian_tugas"]').val() ? form.find('textarea[name="uraian_tugas"]').val().trim() : '';
            const waktuLembur = form.find('select[name="waktu_lembur"]').val();
            const tanggalLembur = form.find('input[name="tanggal_lembur"]').val();
            const keterangan = form.find('textarea[name="keterangan"]').val() ? form.find('textarea[name="keterangan"]').val().trim() : '';

            // 1. Validasi Tanggal Perintah Lembur
            if (isEmpty(tanggalSpl)) {
                showError(form, 'tanggal_spl', 'Tanggal Perintah Lembur wajib diisi.');
                validationErrorMessages.push('Tanggal Perintah Lembur wajib diisi.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'tanggal_spl';
            }

            // 2. Validasi Jam Mulai
            if (isEmpty(jamMulai)) {
                showError(form, 'jam_mulai', 'Jam Mulai wajib diisi.');
                validationErrorMessages.push('Jam Mulai wajib diisi.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'jam_mulai';
            }

            // 3. Validasi Jam Selesai
            if (isEmpty(jamSelesai)) {
                showError(form, 'jam_selesai', 'Jam Selesai wajib diisi.');
                validationErrorMessages.push('Jam Selesai wajib diisi.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'jam_selesai';
            }

            // 4. Validasi Foto Mulai
            const fotoMulaiLama = form.find('input[name="foto_mulai"]').closest('.col-md-6').find('img').length > 0;
            const fotoMulaiBaru = form.find('input[name="foto_mulai"]')[0].files.length > 0;
            if (!fotoMulaiLama && !fotoMulaiBaru) {
                showError(form, 'foto_mulai', 'Foto Mulai wajib diunggah.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'foto_mulai';
            }

            // 5. Validasi Foto Selesai
            const fotoSelesaiLama = form.find('input[name="foto_selesai"]').closest('.col-md-6').find('img').length > 0;
            const fotoSelesaiBaru = form.find('input[name="foto_selesai"]')[0].files.length > 0;
            if (!fotoSelesaiLama && !fotoSelesaiBaru) {
                showError(form, 'foto_selesai', 'Foto Selesai wajib diunggah.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'foto_selesai';
            }

            // 6. Validasi Uraian Tugas
            if (isEmpty(uraianTugas)) {
                showError(form, 'uraian_tugas', 'Uraian Tugas wajib diisi.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'uraian_tugas';
            }

            // 7. Validasi Waktu Lembur
            if (isEmpty(waktuLembur)) {
                showError(form, 'waktu_lembur', 'Waktu Lembur wajib dipilih.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'waktu_lembur';
            }

            // 8. Validasi Tanggal Lembur
            if (isEmpty(tanggalLembur)) {
                showError(form, 'tanggal_lembur', 'Tanggal Lembur wajib diisi.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'tanggal_lembur';
            }

            // 9. Validasi Detail Tugas / Keterangan
            if (isEmpty(keterangan)) {
                showError(form, 'keterangan', 'Detail Tugas wajib diisi.');
                isValid = false;
                if (!firstErrorField) firstErrorField = 'keterangan';
            }

            //  Jika ada error, cegah submit, tampilkan popup, dan scroll ke error pertama
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
                        scrollTop: form.find('[name="' + firstErrorField + '"]').offset().top - 100
                    }, 500);
                }
                return false;
            }

            // Jika valid, form akan submit normal ke server
        });

        //  Hapus error real-time saat user mengisi field
        $('#formEditLembur').on('input change', 'input, select, textarea', function() {
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
        
    @endsection