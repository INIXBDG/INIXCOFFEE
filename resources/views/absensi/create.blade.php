@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Absensi') }}</h5>
                    <form method="POST" action="{{ route('absensi.manual') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="id_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <select name="id_karyawan" id="id_karyawan" class="form-select">
                                    <option value="-" selected>Pilih Karyawan</option>
                                    @foreach ($user as $item)
                                        <option value="{{$item->id}}">{{$item->karyawan->nama_lengkap}}</option>                                        
                                    @endforeach
                                </select>
                                @error('id_karyawan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal" type="date" placeholder="Masukan Nama Jabatan" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" value="{{ old('tanggal') }}" autocomplete="tanggal" autofocus>
                                @error('tanggal')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="jam_masuk" class="col-md-4 col-form-label text-md-start">{{ __('Jam Masuk') }}</label>
                            <div class="col-md-6">
                                <input id="jam_masuk" type="text" step="1" placeholder="00:00:00" class="form-control @error('jam_masuk') is-invalid @enderror" name="jam_masuk" value="{{ old('jam_masuk') }}" autocomplete="jam_masuk" autofocus>
                                @error('jam_masuk')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                            <div class="col-md-6">
                                <select name="keterangan" id="keterangan" class="form-select">
                                    <option value="-" selected>Pilih Keterangan</option>
                                    <option value="Izin">Izin</option>
                                    <option value="Masuk (Kantor)">Masuk (Kantor)</option>
                                    <option value="Telat (Kantor)">Telat (Kantor)</option>
                                    <option value="Masuk (Inhouse Bandung Raya)">Masuk (Inhouse Bandung Raya)</option>
                                    <option value="Telat (Inhouse Bandung Raya)">Telat (Inhouse Bandung Raya)</option>
                                    <option value="Masuk (SPJ)">Masuk (SPJ)</option>
                                    <option value="Telat (SPJ)">Telat (SPJ)</option>
                                </select>
                                @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="shift" class="col-md-4 col-form-label text-md-start">{{ __('shift') }}</label>
                            <div class="col-md-6">
                                <select name="shift" id="shift" class="form-select">
                                    <option value="-" selected>Pilih shift</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                </select>
                                @error('shift')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="waktu_keterlambatan" class="col-md-4 col-form-label text-md-start">{{ __('Waktu Keterlambatan') }}</label>
                            <div class="col-md-6">
                                <input id="waktu_keterlambatan" type="text" step="1" placeholder="Masukan Nama Materi" class="form-control @error('waktu_keterlambatan') is-invalid @enderror" name="waktu_keterlambatan" value="00:00:00" autocomplete="waktu_keterlambatan" autofocus>
                                @error('waktu_keterlambatan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="foto" class="col-md-4 col-form-label text-md-start">{{ __('Foto') }}</label>
                            <div class="col-md-6">
                                <div id="camera" style="width: 320px; height: 320px; border: 2px solid #ddd; border-radius: 5px;"></div>
                                <div id="result" class="mt-2" style="width: 320px; text-align: center;"></div>
                                <input type="hidden" name="foto" id="fotoInput"> <!-- Hidden input to store captured image data -->
                                <button id="takeSnapshot" type="button" class="btn btn-primary mx-2">Say Cheese!</button>
                                @error('foto')
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
<script src="{{ asset('js/webcam.js') }}"></script>
<script>
    $(document).ready(function() {
        let stream;
        Webcam.set({
            width: 320,
            height: 320,
            image_format: 'jpeg',
            jpeg_quality: 50,
            flip_horiz: true,
            constraints: {
                facingMode: "user",
                width: { ideal: 320 },
                height: { ideal: 320 }
            }
        });

        Webcam.attach('#camera');

        Webcam.on('live', function() {
            stream = Webcam.stream;
        });

        // Capture image on button click
        $('#takeSnapshot').on('click', function() {
            Webcam.snap(function(data_uri) {
                $('#result').html('<img src="' + data_uri + '" class="img-fluid"/>'); // Display captured image
                $('#fotoInput').val(data_uri); // Store image data in hidden input
            });
        });

        $('#waktu_keterlambatan').timepicker({
            timeFormat: 'HH:mm:ss',
            // defaultTime: '00',
            dynamic: true,
            dropdown: false,
            scrollbar: true,
            showMeridian: false,
        });

        $('#jam_masuk').timepicker({
            timeFormat: 'HH:mm:ss',
            defaultTime: '08',
            interval: 1,
            dynamic: true,
            dropdown: true,  // Mengaktifkan dropdown
            scrollbar: true,
            showMeridian: false,
        });
    });
</script>
@endsection
