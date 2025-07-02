@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Pengajuan Cuti') }}</h5>
                    <form method="POST" action="{{ route('pengajuancuti.store') }}" enctype="multipart/form-data">
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
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Jenis Cuti') }}</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select">
                                    <option value="-">Pilih Jenis Cuti</option>
                                    <option value="Cuti">Cuti</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Izin">Izin</option>
                                    <option value="Berduka">Berduka</option>
                                    <option value="Menikah">Menikah</option>
                                    <option value="Hamil & Melahirkan">Hamil & Melahirkan</option>
                                </select>
                                @error('tipe')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="kontak-row">
                            <label for="kontak" class="col-md-4 col-form-label text-md-start">{{ __('Kontak yang bisa dihubungi') }}</label>
                            <div class="col-md-6">
                                <input id="kontak" type="text" placeholder="Kontak yang bisa dihubungi" class="form-control @error('kontak') is-invalid @enderror" name="kontak" autocomplete="kontak" autofocus>
                                @error('kontak')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="alasan-row">
                            <label for="alasan" class="col-md-4 col-form-label text-md-start">{{ __('Alasan mengajukan cuti') }}</label>
                            <div class="col-md-6">
                                <textarea name="alasan" class="form-control" id="alasan" cols="51" rows="5"></textarea>
                                @error('alasan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="backup_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Di Backup Oleh ') }}</label>
                            <div class="col-md-6">
                                <select name="backup_karyawan1" id="backup_karyawan1" class="form-select">
                                    <option value="-">Pilih Karyawan</option>
                                    @foreach ($karyawanall as $item)
                                        <option value="{{$item->kode_karyawan}}">{{$item->nama_lengkap}}</option>
                                    @endforeach
                                </select>    
                                <select name="backup_karyawan2" id="backup_karyawan1" class="form-select">
                                    <option value="-">Pilih Karyawan</option>
                                    @foreach ($karyawanall as $item)
                                        <option value="{{$item->kode_karyawan}}">{{$item->nama_lengkap}}</option>
                                    @endforeach
                                </select>                                 
                                @error('tipe')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3" id="tanggal_awal-row">
                            <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Mulai Cuti') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="tanggal_awal" id="tanggal_awal">
                                @error('tanggal_awal')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="tanggal_akhir-row">
                            <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Selesai Cuti') }}</label>
                            <div class="col-md-6">
                                <input type="date" class="form-control" name="tanggal_akhir" id="tanggal_akhir">
                                @error('tanggal_akhir')
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

                        <div class="row mb-3" id="cuti-row">
                            <label for="cuti" class="col-md-4 col-form-label text-md-start">{{ __('Cuti anda yang dapat digunakan') }}</label>
                            <div class="col-md-6">
                                <input readonly id="cuti" type="text" placeholder="cuti" class="form-control @error('cuti') is-invalid @enderror" name="cuti" value="{{ $karyawan->cuti }}" autocomplete="cuti" autofocus>
                                @error('cuti')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="surat_sakit-row">
                            <label for="surat_sakit" class="col-md-4 col-form-label text-md-start">{{ __('Surat Sakit') }}</label>
                            <div class="col-md-6">
                                <input type="file" name="surat_sakit" id="surat_sakit" class="form-control">
                                @error('surat_sakit')
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
        var jabatan = "{{auth()->user()->jabatan}}";
        function calculateDuration() {
            var startDate = new Date($('#tanggal_awal').val());
            var endDate = new Date($('#tanggal_akhir').val());

            if (!isNaN(startDate) && !isNaN(endDate)) {
                var daysDifference = 0;
                var currentDate = startDate;

                while (currentDate <= endDate) {
                    var dayOfWeek = currentDate.getDay(); // 0 = Minggu, 6 = Sabtu

                    if (jabatan === "Technical Support" || jabatan === "Driver") {
                        // Jika jabatan adalah Technical Support atau Driver, hitung Senin sampai Sabtu (exclude Minggu)
                        if (dayOfWeek !== 0) { // Exclude Minggu (0)
                            daysDifference++;
                        }
                    } else if (jabatan === "Office Boy") {
                        // Jika jabatan adalah Office Boy, hitung Sabtu dan Minggu juga
                        daysDifference++; // Hitung semua hari termasuk Sabtu dan Minggu
                    } else {
                        // Untuk jabatan lainnya, hitung hanya Senin sampai Jumat (exclude Sabtu dan Minggu)
                        if (dayOfWeek !== 6 && dayOfWeek !== 0) { // Exclude Sabtu (6) dan Minggu (0)
                            daysDifference++;
                        }
                    }

                    currentDate.setDate(currentDate.getDate() + 1); // Pindah ke hari berikutnya
                }

                $('#durasi').val(daysDifference > 0 ? daysDifference : 0);
            } else {
                $('#durasi').val('');
            }
        }


        $('#tanggal_awal, #tanggal_akhir').on('change', calculateDuration);

        $('#tipe').on('change', function () {
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

                    $('#tanggal_awal').on('change', function () {
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
                        $('#durasi').prop('readonly', true).val(90);  // Durasi cuti 90 hari
                        $('#tanggal_awal').prop('readonly', false);
                        $('#tanggal_akhir').prop('readonly', true);

                        $('#tanggal_awal').off('change').on('change', function () {
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
