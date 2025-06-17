@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Pengajuan Izin') }}</h5>
                    <form method="POST" action="{{ route('pengajuanizin.store') }}" enctype="multipart/form-data">
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

                        <div class="row mb-3" id="tanggal-row">
                            <label for="tanggal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal" type="text" class="form-control @error('tanggal') is-invalid @enderror" value="{{ now()->format('d M Y') }}" name="tanggal" autocomplete="tanggal" autofocus readonly>
                                @error('tanggal')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="jam_mulai-row">
                            <label for="jam_mulai" class="col-md-4 col-form-label text-md-start">{{ __('Jam Mulai') }}</label>
                            <div class="col-md-6">
                                <input id="jam_mulai" type="time" class="form-control @error('jam_mulai') is-invalid @enderror" name="jam_mulai" required autocomplete="jam_mulai" autofocus min="">
                                @error('jam_mulai')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                <span id="jam_mulai-error" class="invalid-feedback d-none" role="alert">
                                    <strong>Waktu mulai tidak boleh kurang dari waktu saat ini.</strong>
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3" id="jam_selesai-row">
                            <label for="jam_selesai" class="col-md-4 col-form-label text-md-start">{{ __('Jam Selesai') }}</label>
                            <div class="col-md-6">
                                <input id="jam_selesai" type="time" class="form-control @error('jam_selesai') is-invalid @enderror" name="jam_selesai" autocomplete="jam_selesai" autofocus readonly>
                                @error('jam_selesai')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="alasan-row">
                            <label for="alasan" class="col-md-4 col-form-label text-md-start">{{ __('Alasan mengajukan cuti') }}</label>
                            <div class="col-md-6">
                                <textarea name="alasan" class="form-control" id="alasan" cols="51" rows="5" required></textarea>
                                @error('alasan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="durasi-row">
                            <label for="durasi" class="col-md-4 col-form-label text-md-start">{{ __('Durasi Hari') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" id="durasi" name="durasi" value="3">
                                <input readonly id="durasi_display" type="text" placeholder="Durasi" value="3" class="form-control @error('durasi') is-invalid @enderror" autocomplete="durasi" autofocus>
                                @error('durasi')
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
    
    document.addEventListener('DOMContentLoaded', function() {
        const jamMulaiInput = document.getElementById('jam_mulai');
        const errorMessage = document.getElementById('jam_mulai-error');

        function setMinTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hours}:${minutes}`;
            jamMulaiInput.setAttribute('min', currentTime);
            return currentTime;
        }

        setMinTime();

        jamMulaiInput.addEventListener('change', function() {
            const selectedTime = jamMulaiInput.value;
            const currentTime = setMinTime();

            if (selectedTime < currentTime) {
                jamMulaiInput.classList.add('is-invalid');
                errorMessage.classList.remove('d-none');
                jamMulaiInput.value = currentTime; 
            } else {
                jamMulaiInput.classList.remove('is-invalid');
                errorMessage.classList.add('d-none');
            }
        });

        setInterval(setMinTime, 60000); 
    });

    document.getElementById('jam_mulai').addEventListener('change', function() {
        const jamMulaiInput = this.value;
        if (!jamMulaiInput) return;

        const [jam, menit] = jamMulaiInput.split(':').map(Number);
        let jamSelesai = jam + 3;
        let menitSelesai = menit;

        if (jamSelesai >= 24) {
            jamSelesai = jamSelesai % 24;
        }

        const jamSelesaiStr = jamSelesai.toString().padStart(2, '0');
        const menitSelesaiStr = menitSelesai.toString().padStart(2, '0');

        document.getElementById('jam_selesai').value = `${jamSelesaiStr}:${menitSelesaiStr}`;

        const durasiJam = 3;
        document.getElementById('durasi').value = durasiJam;
        document.getElementById('durasi_display').value = `${durasiJam} Jam`;
    });


    $(document).ready(function() {
        $('#cuti-row, #surat_sakit-row').hide();
        var jabatan = "{{auth()->user()->jabatan}}";

        function calculateDuration() {
            var startDate = new Date($('#tanggal_awal').val());
            var endDate = new Date($('#tanggal_akhir').val());

            if (!isNaN(startDate) && !isNaN(endDate)) {
                var daysDifference = 0;
                var currentDate = startDate;

                while (currentDate <= endDate) {
                    var dayOfWeek = currentDate.getDay();

                    if (jabatan === "Technical Support" || jabatan === "Driver") {
                        if (dayOfWeek !== 0) {
                            daysDifference++;
                        }
                    } else if (jabatan === "Office Boy") {
                        daysDifference++;
                    } else {
                        if (dayOfWeek !== 6 && dayOfWeek !== 0) {
                            daysDifference++;
                        }
                    }

                    currentDate.setDate(currentDate.getDate() + 1);
                }

                $('#durasi').val(daysDifference > 0 ? daysDifference : 0);
            } else {
                $('#durasi').val('');
            }
        }


        $('#tanggal_awal, #tanggal_akhir').on('change', calculateDuration);

        $('#tipe').on('change', function() {
            var selectedType = $(this).val();

            switch (selectedType) {
                case 'Cuti':
                    $('#cuti-row').show();
                    $('#surat_sakit-row').hide();
                    $('#tanggal_awal').prop('readonly', false);
                    $('#tanggal_akhir').prop('readonly', false);
                    break;

                case 'Sakit':
                    $('#surat_sakit-row').show();
                    $('#cuti-row').hide();
                    $('#tanggal_awal').prop('readonly', false);
                    $('#tanggal_akhir').prop('readonly', false);
                    break;

                case 'Izin':
                    $('#cuti-row, #surat_sakit-row').hide();
                    $('#durasi').prop('readonly', true); // Allow editing the duration
                    $('#tanggal_awal').prop('readonly', false);
                    $('#tanggal_akhir').prop('readonly', false);
                    $('#durasi').val('');
                    break;

                case 'Menikah':
                    $('#cuti-row, #surat_sakit-row').hide();
                    $('#durasi').prop('readonly', true).val(3); // Duration fixed to 3 days for marriage
                    $('#tanggal_awal').prop('readonly', false);
                    $('#tanggal_akhir').prop('readonly', true); // Prevent manual editing of the end date

                    $('#tanggal_awal').on('change', function() {
                        var startDate = new Date($(this).val());
                        var daysCounted = 0;
                        var currentDate = new Date(startDate); // Copy startDate to avoid mutating it

                        while (daysCounted < 3) {
                            var dayOfWeek = currentDate.getDay();

                            // Only count the day if it's not Saturday (6) or Sunday (0)
                            if (dayOfWeek !== 6 && dayOfWeek !== 0) {
                                daysCounted++;
                            }

                            // Move to the next day, but only if the target days are not reached
                            if (daysCounted < 3) {
                                currentDate.setDate(currentDate.getDate() + 1);
                            }
                        }

                        $('#durasi').prop('readonly', true).val(3); // Duration fixed to 3 days for marriage
                        $('#tanggal_akhir').val(currentDate.toISOString().split('T')[0]); // Set end date based on calculated days
                    });
                    break;
                case 'Hamil & Melahirkan':
                    $('#cuti-row, #surat_sakit-row').hide();
                    $('#durasi').prop('readonly', true).val(90); // Durasi cuti 90 hari
                    $('#tanggal_awal').prop('readonly', false);
                    $('#tanggal_akhir').prop('readonly', true);

                    $('#tanggal_awal').off('change').on('change', function() {
                        var startDateStr = $(this).val();
                        if (!startDateStr) {
                            alert('Tanggal awal harus diisi!');
                            $('#tanggal_akhir').val('');
                            return;
                        }

                        var startDate = new Date(startDateStr);
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);
                        if (startDate < today) {
                            alert('Tanggal awal tidak boleh kurang dari hari ini!');
                            $('#tanggal_akhir').val('');
                            return;
                        }

                        // Hitung tanggal akhir dengan 90 hari kalender (termasuk Sabtu & Minggu)
                        var endDate = new Date(startDate);
                        endDate.setDate(endDate.getDate() + 89); // 89 karena tanggal mulai dihitung sebagai hari pertama

                        $('#durasi').val(90);
                        $('#tanggal_akhir').val(endDate.toISOString().split('T')[0]);
                    });
                    break;


                default:
                    $('#cuti-row, #surat_sakit-row').hide();
                    $('#durasi').val('');
                    break;
            }

        });
    });
</script>

@endsection