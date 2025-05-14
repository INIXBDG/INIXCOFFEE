@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <h5 class="card-title text-center mb-4">{{ __('Rencana Kelas Mingguan') }}</h5>
                    <form method="POST" action="{{ route('rkm.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="sales_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama Sales') }}</label>
                            <div class="col-md-6">
                                @if (auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'Adm Sales' || auth()->user()->jabatan == 'Tim Digital')
                                <select class="form-select @error('sales_key') is-invalid @enderror" name="sales_key" required autocomplete="sales_key">
                                    <option value="">Pilih Sales</option>
                                    @foreach ($sales as $salesis)
                                       <option value="{{ $salesis->kode_karyawan }}">{{ $salesis->kode_karyawan }} - {{ $salesis->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                                @else
                                <select disabled class="form-select @error('sales_key') is-invalid @enderror" name="sales_key" required autocomplete="sales_key">
                                    <option value="">Pilih Sales</option>
                                    @foreach ($sales as $salesis)
                                        @if ($salesis->kode_karyawan == auth()->user()->id_sales)
                                            <option value="{{ $salesis->kode_karyawan }}" selected>{{ $salesis->kode_karyawan }} - {{ $salesis->nama_lengkap }}</option>
                                        @else
                                            <option value="{{ $salesis->kode_karyawan }}">{{ $salesis->kode_karyawan }} - {{ $salesis->nama_lengkap }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="hidden" name="sales_key" value="{{auth()->user()->id_sales}}"/>
                                @endif
                                @error('sales_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="materi_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                            <div class="col-md-6">
                                <select id="materi_key" class="form-select @error('materi_key') is-invalid @enderror" name="materi_key" value="{{ old('materi_key', ) }}" required autocomplete="materi_key">
                                </select>
                                @error('materi_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="perusahaan_key" class="col-md-4 col-form-label text-md-start">{{ __('Perusahaan / Instansi') }}</label>
                            <div class="col-md-6">
                                @if (auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'Adm Sales' || auth()->user()->jabatan == 'Tim Digital')
                                <select style="height: 30px" class="form-select @error('perusahaan_key') is-invalid @enderror" name="perusahaan_key" id="perusahaan_key">
                                </select>
                                @else
                                <select style="height: 30px" class="form-select @error('perusahaan_key') is-invalid @enderror" name="perusahaan_key" id="perusahaan_key_x">
                                </select>
                                @endif
                                @error('perusahaan_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harga_jual" class="col-md-4 col-form-label text-md-start">{{ __('Harga Jual') }}</label>
                            <div class="col-md-6">
                                <input id="harga_jual" type="text" placeholder="Harga Jual" class="form-control @error('harga_jual') is-invalid @enderror" name="harga_jual" value="{{ old('harga_jual') }}" autocomplete="harga_jual" autofocus>
                                @error('harga_jual')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('PAX') }}</label>
                            <div class="col-md-6">
                                <input id="pax" type="number" placeholder="PAX" class="form-control @error('pax') is-invalid @enderror" name="pax" value="{{ old('pax') }}" autocomplete="pax" autofocus>
                                @error('pax')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="quartal" class="col-md-4 col-form-label text-md-start">{{ __('Kuartal') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="tahun" value="{{$year}}">
                                <input type="hidden" name="durasi" value="">
                                {{-- <input type="text" name="bulan" id="bulan"> --}}
                                <input id="quartal" readonly type="text" placeholder="Kuartal" class="form-control @error('quartal') is-invalid @enderror" name="quartal" value="{{ old('quartal') }}" autocomplete="quartal" autofocus>
                                @error('quartal')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Awal') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_awal" type="date" placeholder="tanggal_awal" class="form-control @error('tanggal_awal') is-invalid @enderror" name="tanggal_awal" value="{{ old('tanggal_awal') }}" autocomplete="tanggal_awal" autofocus>
                                @error('tanggal_awal')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="tanggal_akhir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Akhir') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_akhir" type="date" placeholder="tanggal_akhir" class="form-control @error('tanggal_akhir') is-invalid @enderror" name="tanggal_akhir" value="{{ old('tanggal_akhir') }}" autocomplete="tanggal_akhir" autofocus>
                                @error('tanggal_akhir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="exam" class="col-md-4 col-form-label text-md-start">{{ __('Exam') }}</label>
                            <div class="col-md-6">
                                <input type="checkbox" class="form-checkbox" name="exam" id="exam" value="1">
                                @error('exam')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="authorize" class="col-md-4 col-form-label text-md-start">{{ __('Authorize') }}</label>
                            <div class="col-md-6">
                                <input type="checkbox" class="form-checkbox" name="authorize" id="authorize" value="1">
                                @error('authorize')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="metode_kelas" class="col-md-4 col-form-label text-md-start">{{ __('Metode Kelas') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('metode_kelas') is-invalid @enderror" name="metode_kelas" value="{{ old('metode_kelas', ) }}" required autocomplete="metode_kelas">
                                    <option selected>Pilih Metode Kelas</option>
                                    <option value="Inhouse Bandung">Inhouse Bandung</option>
                                    <option value="Inhouse Luar Bandung">Inhouse Luar Bandung</option>
                                    <option value="Offline">Offline</option>
                                    <option value="Virtual">Virtual</option>
                                </select>
                                @error('metode_kelas')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="event" class="col-md-4 col-form-label text-md-start">{{ __('Event') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('event') is-invalid @enderror" name="event" value="{{ old('event', ) }}" required autocomplete="event">
                                    <option selected>Pilih Event</option>
                                    <option value="Kelas">Kelas</option>
                                    <option value="Workshop">Workshop</option>
                                    <option value="Webinar">Webinar</option>
                                    <option value="Narasumber">Narasumber</option>
                                </select>
                                @error('event')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Status') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('status') is-invalid @enderror" name="status" value="{{ old('status', ) }}" required autocomplete="status">
                                    <option selected>Pilih Status</option>
                                    <option value="0">Merah</option>
                                    <option value="1">Biru</option>
                                    <option value="2">Hitam</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3" id="registform">
                            <label for="registrasi_form" class="col-md-4 col-form-label text-md-start">{{ __('Registrasi Form (PDF)') }}</label>
                            <div class="col-md-6">
                                <input type="file" accept="application/pdf" class="form-control @error('registrasi_form') is-invalid @enderror" name="registrasi_form">
                                @error('registrasi_form')
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
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4"></script>

    <script>
        function areDatesInSameWeek(date1, date2) {
            var startOfWeek1 = new Date(date1);
            startOfWeek1.setDate(startOfWeek1.getDate() - startOfWeek1.getDay()); // Get start of the week for date1

            var startOfWeek2 = new Date(date2);
            startOfWeek2.setDate(startOfWeek2.getDate() - startOfWeek2.getDay()); // Get start of the week for date2

            // If both dates have the same start of the week, they are in the same week
            return startOfWeek1.getTime() === startOfWeek2.getTime();
        }
        function toggleRegistForm() {
            var status = $('select[name="status"]').val();
            if (status === '0') {
                $('#registform').show();
            } else {
                $('#registform').hide();
            }
        }
        function formatRupiah(angka, prefix){
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa  = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

            if(ribuan){
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp' + rupiah : '');
        }
        function getQuarter(month) {
            if (month >= 1 && month <= 3) {
                return 'Q1';
            } else if (month >= 4 && month <= 6) {
                return 'Q2';
            } else if (month >= 7 && month <= 9) {
                return 'Q3';
            } else if (month >= 10 && month <= 12) {
                return 'Q4';
            }
            return '';
        }
        function addDaysToDate(date, days) {
            var result = new Date(date); // Buat salinan dari tanggal
            result.setDate(result.getDate() + parseInt(days-1)); // Tambahkan durasi (hari)
            return result;
        }

        $(document).ready(function() {
                $('select[name="status"]').change(function() {
                    toggleRegistForm();
                });
                toggleRegistForm();
                $('#harga_jual').on('keyup', function(){
                    $(this).val(formatRupiah($(this).val(), 'Rp'));
                });
                $('#perusahaan_key').select2({
                    placeholder: "Pilih Perusahaan",
                    allowClear: true,
                    ajax: {
                        url: '{{route('getPerusahaan')}}',
                        processResults: function({data}){
                            //console.log(data)
                            return{
                                results: $.map(data, function(item){
                                    return {
                                        id: item.id,
                                        text: item.nama_perusahaan
                                    }
                                })
                            }
                        }
                        // dataType: 'json'
                    },

                });
                $('#materi_key').select2({
                    placeholder: "Pilih Materi",
                    allowClear: true,
                    ajax: {
                        url: '{{route('getMateris')}}',
                        processResults: function({data}){
                        // console.log(data)
                            return{
                                results: $.map(data, function(item){
                                    return {
                                        id: item.id,
                                        text: item.nama_materi
                                    }
                                })
                            }
                        }
                        // dataType: 'json'
                    },

                });
                $('#perusahaan_key_x').select2({
                    placeholder: "Pilih Perusahaan",
                    allowClear: true,
                    ajax: {
                        url: '{{route('getPerusahaanById')}}',
                        processResults: function({data}){
                        // console.log(data)
                            return{
                                results: $.map(data, function(item){
                                    return {
                                        id: item.id,
                                        text: item.nama_perusahaan
                                    }
                                })
                            }
                        }
                        // dataType: 'json'
                    },

                });
                function calculateQuarter() {
                    let tanggalAwal = new Date($('#tanggal_awal').val());
                    let tanggalAkhir = new Date($('#tanggal_akhir').val());

                    // Ensure both dates are valid before continuing
                    if (!isNaN(tanggalAwal.getTime()) && !isNaN(tanggalAkhir.getTime())) {
                        // Check if the dates are in the same week
                        if (areDatesInSameWeek(tanggalAwal, tanggalAkhir)) {
                            let daysAwal = 7 - tanggalAwal.getDay(); // Days remaining in the week for tanggal_awal
                            let daysAkhir = tanggalAkhir.getDay() + 1; // Days in the week for tanggal_akhir

                            // Compare which date has more days in the week
                            if (daysAwal > daysAkhir) {
                                // More days in the week for tanggal_awal
                                let month = tanggalAwal.getMonth() + 1;  // Get month (0-11, so add 1)
                                $('#quartal').val(getQuarter(month));  // Set the quarter based on tanggal_awal
                            } else {
                                // More days in the week for tanggal_akhir
                                let month = tanggalAkhir.getMonth() + 1;  // Get month (0-11, so add 1)
                                $('#quartal').val(getQuarter(month));  // Set the quarter based on tanggal_akhir
                            }
                        } else {
                            // Handle cases where they are not in the same week
                            alert("Dates are not in the same week");
                            $('#quartal').val(''); // Clear the quartal input
                        }
                    }
                }
                // Trigger the quarter calculation when both dates change
                // $('#tanggal_awal').on('change', calculateQuarter);
                $('#tanggal_akhir').on('change', calculateQuarter);
                $('#materi_key').on('change', function() {
                    var materi_id = $(this).val(); // Ambil id materi yang dipilih
                    if (materi_id) {
                        // Lakukan AJAX request untuk mendapatkan data durasi berdasarkan id materi
                        $.ajax({
                            url: '/getMateri/' + materi_id, // Menggunakan materi_id dalam URL
                            method: 'GET',
                            success: function(response) {
                                // Isi input durasi dengan data durasi yang diterima
                                if (response.data && response.data.durasi) {
                                    $('#durasi').val(response.data.durasi); // Set value durasi ke input durasi
                                    console.log(response.data.durasi);
                                    window.durasi = response.data.durasi;
                                }
                            },
                            error: function() {
                                alert('Terjadi kesalahan saat mengambil data durasi.');
                            }
                        });
                    } else {
                        $('#durasi').val(''); // Kosongkan input durasi jika materi tidak dipilih
                    }
                });
                $('#tanggal_awal').on('change', function() {
                    var tanggal_awal = $(this).val();
                    var durasi = $('#durasi').val()
                    if (tanggal_awal && window.durasi) {
                        // Jika durasi ada, hitung tanggal akhir
                        var startDate = new Date(tanggal_awal);
                        var endDate = addDaysToDate(startDate, window.durasi); // Tambahkan durasi ke tanggal awal
                        
                        // Set tanggal akhir ke input
                        var endDateString = endDate.toISOString().split('T')[0]; // Format tanggal ke YYYY-MM-DD
                        $('#tanggal_akhir').val(endDateString);
                        calculateQuarter();
                    }
                });

        });
    </script>
@endpush
@endsection
