@extends('layouts.app')

@section('content')
@php
    $jabatan = strtolower(optional(auth()->user())->jabatan ?? '');
    $editableJabatans = ['Education Manager', 'GM', 'SPV Sales', 'Office Manager', 'Koordinator Office', 'HRD', 'Koordinator ITSM'];
    $isEditable = in_array(auth()->user()->jabatan, $editableJabatans);
@endphp
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="/lembur" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Perintah Lembur') }}</h5>
                    <form method="POST" action="{{ route('lembur.updateKaryawan', $data->id) }}" enctype="multipart/form-data">
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

                        <div class="row mb-3" id="tanggal_spl">
                            <label for="tanggal_spl" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Perintah Lembur') }}</label>
                            <div class="col-md-6">
                                <input type="date"
                                    class="form-control"
                                    name="tanggal_spl"
                                    id="tanggal_spl"
                                    value="{{ $data->tanggal_spl }}"
                                    @unless($isEditable) readonly @endunless>
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
                                    {{-- Jika jabatan office boy atau driver, input tidak readonly --}}
                                    @if(!in_array($jabatan, ['office boy', 'driver','education manager',  'gm',  'spv sales',  'office manager',  'koordinator office',  'hrd',  'koordinator itsm' ])) readonly @endif
                                >
                                @error('jam_mulai')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            @if(!in_array(strtolower(auth()->user()->jabatan), ['office boy', 'driver', 'education manager',  'gm',  'spv sales',  'office manager',  'koordinator office',  'hrd',  'koordinator itsm']))
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalAbsen">
                                        Absen Lembur
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="row mb-3" id="jam_selesai">
                            <label for="jam_selesai" class="col-md-4 col-form-label text-md-start">{{ __('Jam Selesai') }}</label>
                            <div class="col-md-6">
                                <input
                                    type="time"
                                    class="form-control"
                                    name="jam_selesai"
                                    id="jam_selesai"
                                    value="{{ $data->jam_selesai }}"
                                    {{-- Jika jabatan office boy atau driver, input tidak readonly --}}
                                    @if(!in_array(strtolower(auth()->user()->jabatan), ['office boy', 'driver', 'education manager',  'gm',  'spv sales',  'office manager',  'koordinator office',  'hrd',  'koordinator itsm']))
                                        readonly
                                    @endif
                                >
                                @error('jam_selesai')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Input Foto Mulai dan Selesai untuk Office Boy dan Driver --}}
                        @if(in_array(strtolower(auth()->user()->jabatan), ['office boy', 'driver' , 'education manager',  'gm',  'spv sales',  'office manager',  'koordinator office',  'hrd',  'koordinator itsm']))
                            <div class="row mb-3">
                                <label for="foto_mulai" class="col-md-4 col-form-label text-md-start">Foto Mulai</label>
                                <div class="col-md-6">
                                    <input
                                        type="file"
                                        class="form-control @error('foto_mulai') is-invalid @enderror"
                                        name="foto_mulai"
                                        id="foto_mulai"
                                        accept="image/*"
                                        capture="camera"
                                    >
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
                                        capture="camera"
                                    >
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
                        @endif

                        <div class="row mb-3" id="uraian_tugas">
                            <label for="uraian_tugas" class="col-md-4 col-form-label text-md-start">{{ __('Uraian Tugas') }}</label>
                            <div class="col-md-6">
                                <textarea name="uraian_tugas"
                                        class="form-control"
                                        id="uraian_tugas"
                                        cols="51"
                                        rows="5"
                                        @unless($isEditable) disabled @endunless>{{ $data->uraian_tugas }}</textarea>
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
                                @error('waktu_lembur')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_lembur">
                            <label for="tanggal_lembur" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Lembur') }}</label>
                            <div class="col-md-6">
                                <input type="date"
                                    class="form-control"
                                    name="tanggal_lembur"
                                    id="tanggal_lembur"
                                    value="{{ $data->tanggal_lembur }}"
                                    @unless($isEditable) readonly @endunless>
                                @error('tanggal_lembur')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="keterangan">
                            <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Detail Tugas') }}</label>
                            <div class="col-md-6">
                                <textarea name="keterangan" class="form-control" id="keterangan" cols="51" rows="5">{{$data->keterangan}}</textarea>
                                @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="row mb-3" id="keterangan">
                            <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Disetujui Karyawan') }}</label>
                            <div class="col-md-6">
                                <div class="btn-group" role="group" aria-label="Approval Options">
                                    <input type="radio" class="btn-check" name="approval" id="approveYes" value="1" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="approveYes" onclick="toggleAlasanManager(false)">Ya</label>

                                    <input type="radio" class="btn-check" name="approval" id="approveNo" value="2" autocomplete="off">
                                    <label class="btn btn-outline-danger" for="approveNo" onclick="toggleAlasanManager(true)">Tidak</label>
                                </div>
                             </div>
                        </div> --}}

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
            <div class="modal fade" id="modalAbsen" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Absensi Lembur</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                      <!-- Kamera -->
                      <div id="camera" style="width: 320px; height: 320px; border: 2px solid #ddd; border-radius: 5px;"></div>

                      <br />

                      <!-- Tombol Mulai dan Selesai -->
                      <div class="d-flex flex-row justify-content-between w-100">
                        <button id="startOvertime" class="btn btn-success mx-2">Mulai Lembur</button>
                        <button id="endOvertime" class="btn btn-danger mx-2">Selesai Lembur</button>
                      </div>

                      <br />

                      <!-- Hasil Snapshot -->
                      <div id="result" style="width: 320px; text-align: center;"></div>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script src="{{ asset('js/webcam.js') }}"></script>
<script>
    let stream;

    Webcam.set({
        width: 320,
        height: 320,
        image_format: 'jpeg',
        jpeg_quality: 50,
        force_flash: false,
        flip_horiz: true,
        constraints: {
            facingMode: "user",
            width: { ideal: 320 },
            height: { ideal: 320 }
        }
    });


    // ✅ Attach webcam hanya saat modal dibuka
    $('#modalAbsen').on('shown.bs.modal', function () {
        Webcam.attach('#camera');
    });

    // ✅ Reset kamera ketika modal ditutup
    $('#modalAbsen').on('hidden.bs.modal', function () {
        Webcam.reset();
        $('#result').html(''); // Kosongkan hasil snapshot jika perlu
    });

    // Tombol Mulai Lembur
    $('#startOvertime').on('click', function () {
        Webcam.snap(function (data_uri) {
            $('#result').html('<p><b>Foto Mulai Lembur:</b></p><img src="' + data_uri + '" class="img-thumbnail"/>');

            const now = new Date();
            const tanggal = now.toISOString().split('T')[0];
            const jam_mulai = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

            const karyawan = "{{ auth()->user()->karyawan_id }}";
            const jabatan = "{{ auth()->user()->jabatan }}";

            $.ajax({
                url: "{{ route('lembur.masuk') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id_karyawan: karyawan,
                    jam_mulai: jam_mulai,
                    tanggal: tanggal,
                    jabatan: jabatan,
                    foto_mulai: data_uri
                },
                success: function (res) {
                    alert(res.success);
                    $('#modalAbsen').modal('hide');
                    location.reload();
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.error || 'Terjadi kesalahan');
                    location.reload();
                }
            });
        });
    });

    // Tombol Selesai Lembur
    $('#endOvertime').on('click', function () {
        Webcam.snap(function (data_uri) {
            $('#result').append('<p><b>Foto Selesai Lembur:</b></p><img src="' + data_uri + '" class="img-thumbnail mt-2"/>');

            const now = new Date();
            const tanggal = now.toISOString().split('T')[0];
            const jam_selesai = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');


            const karyawan = "{{ auth()->user()->karyawan_id }}";

            $.ajax({
                url: "{{ route('lembur.pulang') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id_karyawan: karyawan,
                    tanggal: tanggal,
                    jam_selesai: jam_selesai,
                    foto_selesai: data_uri
                },
                success: function (res) {
                    alert(res.success);
                    $('#modalAbsen').modal('hide');
                    location.reload();
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.error || 'Terjadi kesalahan');
                    location.reload();
                }
            });
        });
    });

    // Aktifkan tombol selesai lembur jika sudah lewat 1 jam
    document.addEventListener("DOMContentLoaded", function () {
        const jamMulaiValue = "{{ $data->jam_mulai }}";

        if (jamMulaiValue) {
            const now = new Date();
            const [jam, menit, detik] = jamMulaiValue.split(':');
            const waktuMulai = new Date(now.toDateString() + ' ' + jam + ':' + menit + ':' + detik);

            const selisihMs = now - waktuMulai;
            const satuJamMs = 60 * 60 * 1000;

            if (selisihMs >= satuJamMs) {
                document.getElementById('endOvertime').disabled = false;
            } else {
                const sisaWaktu = satuJamMs - selisihMs;
                setTimeout(() => {
                    document.getElementById('endOvertime').disabled = false;
                }, sisaWaktu);
            }
        }
    });
    </script>

@endsection
