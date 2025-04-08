@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Edit Absensi Karyawan') }}</h5>
                    <form method="POST" action="{{ route('rekapitulasiabsen.update', $post->id) }}">
                        @csrf
                        @method('PUT')
                        {{-- {{$post}} --}}
                        <div class="row mb-3">
                            <label for="nama_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input disabled id="nama_karyawan" type="text" placeholder="Masukan Waktu" class="form-control @error('nama_karyawan') is-invalid @enderror" name="nama_karyawan" value="{{ old('nama_karyawan', $post->karyawan->nama_lengkap) }}" autocomplete="nama_karyawan" autofocus>
                                <input type="hidden" name="id_karyawan" value="{{$post->id_karyawan}}">
                                @error('nama_karyawan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal') }}</label>
                            <div class="col-md-6">
                                <input disabled id="tanggal" type="date" placeholder="Masukan Kategori Materi" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" value="{{ old('tanggal', $post->tanggal) }}" autocomplete="tanggal" autofocus>
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
                                <input id="jam_masuk" type="text" step="1" placeholder="Masukan Waktu" class="form-control @error('jam_masuk') is-invalid @enderror" name="jam_masuk" value="{{ old('jam_masuk', $post->jam_masuk) }}" autocomplete="jam_masuk" autofocus>
                                @error('jam_masuk')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="jam_keluar" class="col-md-4 col-form-label text-md-start">{{ __('Jam Keluar') }}</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="jam_keluar" type="text" step="1" placeholder="Masukan Waktu" class="form-control @error('jam_keluar') is-invalid @enderror" name="jam_keluar" value="{{ old('jam_keluar', $post->jam_keluar) }}" autocomplete="jam_keluar" autofocus>
                                    <button type="button" class="btn btn-danger" id="clear-jam-keluar"><img src="{{ asset('icon/trash.svg') }}" class="img-responsive"></button>
                                </div>
                                @error('jam_keluar')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="keterangan" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan Masuk') }}</label>
                            <div class="col-md-6">
                                <select name="keterangan" id="keterangan" class="form-select">
                                    <option value="Izin" @if($post->keterangan == 'Izin') selected @endif>Izin</option>
                                    <option value="Masuk (Kantor)" @if($post->keterangan == 'Masuk (Kantor)') selected @endif>Masuk (Kantor)</option>
                                    <option value="Telat (Kantor)" @if($post->keterangan == 'Telat (Kantor)') selected @endif>Telat (Kantor)</option>
                                    <option value="Masuk (Inhouse Bandung Raya)" @if($post->keterangan == 'Masuk (Inhouse Bandung Raya)') selected @endif>Masuk (Inhouse Bandung Raya)</option>
                                    <option value="Telat (Inhouse Bandung Raya)" @if($post->keterangan == 'Telat (Inhouse Bandung Raya)') selected @endif>Telat (Inhouse Bandung Raya)</option>
                                    <option value="Masuk (SPJ)" @if($post->keterangan == 'Masuk (SPJ)') selected @endif>Masuk (SPJ)</option>
                                    <option value="Telat (SPJ)" @if($post->keterangan == 'Telat (SPJ)') selected @endif>Telat (SPJ)</option>
                                </select>
                                @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="keterangan_pulang" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan Pulang') }}</label>
                            <div class="col-md-6">
                                <select name="keterangan_pulang" id="keterangan_pulang" class="form-select">
                                    <option value="">Pilih Keterangan</option>
                                    <option value="Pulang (Kantor)" @if($post->keterangan_pulang == 'Pulang (Kantor)') selected @endif>Pulang (Kantor)</option>
                                    <option value="Pulang (Inhouse Bandung Raya)" @if($post->keterangan_pulang == 'Pulang (Inhouse Bandung Raya)') selected @endif>Pulang (Inhouse Bandung Raya)</option>
                                    <option value="Pulang (SPJ)" @if($post->keterangan_pulang == 'Pulang (SPJ)') selected @endif>Pulang (SPJ)</option>
                                </select>
                                @error('keterangan_pulang')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="waktu_keterlambatan" class="col-md-4 col-form-label text-md-start">{{ __('Waktu Keterlambatan') }}</label>
                            <div class="col-md-6">
                                <input id="waktu_keterlambatan" disabled type="text" step="1" placeholder="Masukan Waktu" class="form-control @error('waktu_keterlambatan') is-invalid @enderror" name="waktu_keterlambatan" value="{{ old('waktu_keterlambatan', $post->waktu_keterlambatan) }}" autocomplete="waktu_keterlambatan" autofocus>
                                @error('waktu_keterlambatan')
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
<style>

</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#keterangan').change(function() {
            if ($(this).val() === 'Izin') {
                $('#waktu_keterlambatan').val('00:00:00');
            }
        });
        $('#clear-jam-keluar').click(function(){
            $('#jam_keluar').val(null);

        })

        $('#waktu_keterlambatan').timepicker({
            timeFormat: 'HH:mm:ss',
            // defaultTime: '00',
            dynamic: true,
            dropdown: false,
            scrollbar: true,
            showMeridian: false,
        });

        $('#jam_keluar').timepicker({
            timeFormat: 'HH:mm:ss',
            interval: 1,
            dynamic: true,
            dropdown: true,  // Mengaktifkan dropdown
            scrollbar: true,
            showMeridian: false,
        });

        $('#jam_masuk').timepicker({
            timeFormat: 'HH:mm:ss',
            interval: 1,
            dynamic: true,
            dropdown: true,  // Mengaktifkan dropdown
            scrollbar: true,
            showMeridian: false,
        });
    });
</script>
@endsection
