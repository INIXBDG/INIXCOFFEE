@extends('layouts.app')

@section('content')
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
                                <input  id="tujuan" type="text" placeholder="Kota yang dituju" class="form-control @error('tujuan') is-invalid @enderror" name="tujuan">
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
                                <input  type="datetime-local" class="form-control" name="tanggal_berangkat" id="tanggal_berangkat">
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
                                <input  type="datetime-local" class="form-control" name="tanggal_pulang" id="tanggal_pulang">
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
                                <input id="alasan" type="text" placeholder="Alasan" class="form-control @error('alasan') is-invalid @enderror" name="alasan" autocomplete="alasan" autofocus>
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
    $(document).ready(function () {
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

        $('#tipe').on('change', function () {
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
                $('#tanggal_berangkat').on('change', function () {
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

@endsection
