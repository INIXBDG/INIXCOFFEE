@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Pengajuan Surat Perjalanan') }}</h5>
                    <form method="POST" action="{{ route('suratperjalanan.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="nama_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">
                                <input disabled id="nama_karyawan" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('nama_karyawan') is-invalid @enderror" name="nama_karyawan" value="{{ $karyawan->nama_lengkap }}" autocomplete="nama_karyawan" autofocus>
                                @error('nama_karyawan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input disabled id="divisi" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                                @error('divisi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="jenis_travel" class="col-md-4 col-form-label text-md-start">{{ __('Jenis Dinas') }}</label>
                            <div class="col-md-6">
                                <select name="jenis_dinas" id="jenis_dinas" class="form-select" required>
                                    <option value="" selected disabled>Pilih Jenis Dinas</option>
                                    <option value="Perjalanan Dinas">Perjalanan Dinas</option>
                                    <option value="InHouse Luar Bandung">Inhouse Luar Bandung</option>
                                </select>
                                @error('tipe')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div id="jadwal_rkm_section" class="row mb-3">
                            <label for="jadwal_rkm" class="col-md-4 col-form-label text-md-start">{{ __('Jadwal RKM') }}</label>
                            <div class="col-md-6">
                                <select name="jadwal_RKM" id="jadwal_rkm" class="form-select">
                                    <option></option> 
                                    @foreach ($data_rkm as $tanggal => $rkms)
                                    <optgroup label="{{ $tanggal }}">
                                        @foreach ($rkms as $rkm)
                                        <option value="{{ $rkm->id }}">
                                            {{ $rkm->materi->nama_materi }} - {{ $rkm->perusahaan->nama_perusahaan }}
                                        </option>
                                        @endforeach
                                    </optgroup>
                                    @endforeach
                                </select>
                                @error('jadwal_rkm')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Jenis Travel') }}</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select">
                                    <option value="-">Pilih Jenis Travel</option>
                                    <option value="Domestik">Travel Domestik</option>
                                    <option value="Internasional">Travel Internasional</option>
                                </select>
                                @error('tipe')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="kontak-row">
                            <label for="tujuan" class="col-md-4 col-form-label text-md-start">{{ __('Tujuan') }}</label>
                            <div class="col-md-6">
                                <input id="tujuan" type="text" placeholder="Kota yang dituju" class="form-control @error('tujuan') is-invalid @enderror" name="tujuan">
                                @error('tujuan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_berangkat-row">
                            <label for="tanggal_berangkat" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Berangkat') }}</label>
                            <div class="col-md-6">
                                <input type="datetime-local" class="form-control" name="tanggal_berangkat" id="tanggal_berangkat">
                                @error('tanggal_berangkat')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_pulang-row">
                            <label for="tanggal_pulang" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Kedatangan') }}</label>
                            <div class="col-md-6">
                                <input type="datetime-local" class="form-control" name="tanggal_pulang" id="tanggal_pulang">
                                @error('tanggal_pulang')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="durasi-row">
                            <label for="durasi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi Hari') }}</label>
                            <div class="col-md-6">
                                <input readonly id="durasi" type="text" placeholder="Durasi" class="form-control @error('durasi') is-invalid @enderror" name="durasi" autocomplete="durasi" autofocus>
                                @error('durasi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="alasan" class="col-md-4 col-form-label text-md-start">{{ __('Alasan Perjalanan') }}</label>
                            <div class="col-md-6">
                                <textarea id="alasan" type="text" placeholder="Alasan" class="form-control @error('alasan') is-invalid @enderror" name="alasan" autocomplete="alasan" autofocus></textarea>
                                @error('alasan')
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
        // Initial hide of fields based on the default value of the select
        $('#cuti-row, #surat_sakit-row').hide();

        function calculateDuration() {
            var startDate = new Date($('#tanggal_berangkat').val());
            var endDate = new Date($('#tanggal_pulang').val());

            if (!isNaN(startDate) && !isNaN(endDate)) {
                var daysDifference = 0;
                var currentDate = startDate;

                while (currentDate <= endDate) {
                    var dayOfWeek = currentDate.getDay(); // 0 = Minggu, 6 = Sabtu
                    daysDifference++; // Hitung semua hari termasuk Sabtu dan Minggu
                    currentDate.setDate(currentDate.getDate() + 1); // Move to the next day
                }

                $('#durasi').val(daysDifference > 0 ? daysDifference : 0);
            } else {
                $('#durasi').val('');
            }
        }

        $('#tanggal_berangkat, #tanggal_pulang').on('change', calculateDuration);

        $('#tipe').on('change', function() {
            var selectedType = $(this).val();

            if (selectedType === 'Cuti') {
                $('#cuti-row').show();
                $('#surat_sakit-row').hide();
                $('#tanggal_berangkat').prop('readonly', false);
                $('#tanggal_pulang').prop('readonly', false);

            } else if (selectedType === 'Sakit') {
                $('#surat_sakit-row').show();
                $('#cuti-row').hide();
                $('#tanggal_berangkat').prop('readonly', false);
                $('#tanggal_pulang').prop('readonly', false);

            } else if (selectedType === 'Izin') {
                $('#cuti-row, #surat_sakit-row').hide();
                $('#durasi').prop('readonly', true); // Allow editing the duration
                $('#tanggal_berangkat').prop('readonly', false);
                $('#tanggal_pulang').prop('readonly', false);
                $('#durasi').val('');

            } else if (selectedType === 'Menikah') {
                $('#cuti-row, #surat_sakit-row').hide();
                $('#tanggal_berangkat').prop('readonly', false);
                $('#tanggal_pulang').prop('readonly', false);
                $('#durasi').prop('readonly', true).val(3); // Duration fixed to 3 days for marriage
                $('#tanggal_berangkat').on('change', function() {
                    var startDate = new Date($(this).val());
                    var endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 2); // Add 2 days to the start date

                    $('#tanggal_pulang').val(endDate.toISOString().split('T')[0]); // Set end date to 2 days after start date
                    $('#durasi').prop('readonly', true).val(3); // Duration fixed to 3 days for marriage
                });
            } else {
                $('#cuti-row, #surat_sakit-row').hide();
                $('#durasi').val('');
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisTravel = document.getElementById('jenis_dinas');
        const jadwalRKMSection = document.getElementById('jadwal_rkm_section');

        function toggleJadwalRKM() {
            if (jenisTravel.value === 'InHouse Luar Bandung') {
                jadwalRKMSection.style.display = 'flex';
            } else {
                jadwalRKMSection.style.display = 'none';
            }
        }

        toggleJadwalRKM();

        jenisTravel.addEventListener('change', toggleJadwalRKM);
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#jadwal_rkm').select2({
            placeholder: 'Pilih Jadwal RKM',
            allowClear: true,
            width: '100%'
        });
    });
</script>

@endsection