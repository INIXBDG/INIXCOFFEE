@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Edit Rencana Kelas Mingguan') }}</h5>
                    <form method="POST" action="{{ route('rkm.update', $post->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row mb-3">
                            <label for="sales_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama Sales') }}</label>
                            <div class="col-md-6">
                                <select id="sales_key" disabled class="form-select @error('sales_key') is-invalid @enderror" name="sales_key" required autocomplete="sales_key">
                                    <option>Pilih Sales</option>
                                    @foreach ($sales as $salesis)
                                        <option value="{{ $salesis->kode_karyawan }}" @if ($post->sales->kode_karyawan == $salesis->kode_karyawan) selected @endif>
                                            {{ $salesis->kode_karyawan }} - {{ $salesis->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="sales_key"  value="{{$post->sales_key}}"/>
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
                                <select id="materi_key" class="form-select select2 @error('materi_key') is-invalid @enderror" name="materi_key" required autocomplete="materi_key">
                                    <option selected>Pilih Materi</option>
                                    @foreach ($materi as $materis)
                                        <option value="{{ $materis->id }}" @if ($post->materi_key == $materis->id) selected @endif>{{ $materis->nama_materi }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="materi_key"  value="{{$post->materi_key}}"/>
                                @error('materi_key')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="perusahaan_key" class="col-md-4 col-form-label text-md-start">{{ __('Nama Perusahaan') }}</label>
                            <div class="col-md-6">
                                <select class="form-select select2 @error('perusahaan_key') is-invalid @enderror" name="perusahaan_key" required autocomplete="perusahaan_key" id="perusahaan_key">
                                    <option selected>Pilih Perusahaan</option>
                                    @foreach ($perusahaan as $perusahaans)
                                        <option value="{{ $perusahaans->id }}" {{ $post->perusahaan_key == $perusahaans->id ? 'selected' : '' }}>{{ $perusahaans->nama_perusahaan }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="perusahaan_key"  value="{{$post->perusahaan_key}}"/>
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
                                @if (auth()->user()->jabatan == 'Customer Care' || auth()->user()->jabatan == 'Customer Service' || auth()->user()->jabatan == 'Admin Holding' || auth()->user()->jabatan == 'Technical Support')
                                <input disabled id="harga_jual" type="text" placeholder="Harga Jual" class="form-control @error('harga_jual') is-invalid @enderror" name="harga_jual" value="{{ old('harga_jual', $post->harga_jual) }}" autocomplete="harga_jual" autofocus>
                                <input type="hidden" name="harga_jual"  value="{{$post->harga_jual}}"/>
                                @else
                                <input id="harga_jual" type="text" placeholder="Harga Jual" class="form-control @error('harga_jual') is-invalid @enderror" name="harga_jual" value="{{ old('harga_jual', $post->harga_jual) }}" autocomplete="harga_jual" autofocus>
                                @endif
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
                                @if (auth()->user()->jabatan == 'Customer Care' || auth()->user()->jabatan == 'Customer Service' || auth()->user()->jabatan == 'Admin Holding' || auth()->user()->jabatan == 'Technical Support')
                                <input id="pax" disabled type="text" placeholder="PAX" class="form-control @error('pax') is-invalid @enderror" name="pax" value="{{ old('pax', $post->pax) }}" autocomplete="pax" autofocus>
                                <input type="hidden" name="pax"  value="{{$post->pax}}"/>
                                @else
                                <input id="pax" type="text" placeholder="PAX" class="form-control @error('pax') is-invalid @enderror" name="pax" value="{{ old('pax', $post->pax) }}" autocomplete="pax" autofocus>
                                @endif
                                @error('pax')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Awal') }}</label>
                            <div class="col-md-6">
                                <input id="tanggal_awal" type="date" placeholder="tanggal_awal" class="form-control @error('tanggal_awal') is-invalid @enderror" name="tanggal_awal" value="{{ old('tanggal_awal', $post->tanggal_awal) }}" autocomplete="tanggal_awal" autofocus>
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
                                <input id="tanggal_akhir" type="date" placeholder="tanggal_akhir" class="form-control @error('tanggal_akhir') is-invalid @enderror" name="tanggal_akhir" value="{{ old('tanggal_akhir', $post->tanggal_akhir) }}" autocomplete="tanggal_akhir" autofocus>
                                @error('tanggal_akhir')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="quarter" class="col-md-4 col-form-label text-md-start">{{ __('Kuartal') }}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="quarter" id="quarter" value="{{$post->quarter}}" >
                                @error('quarter')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="exam" class="col-md-4 col-form-label text-md-start">{{ __('Exam') }}</label>
                            <div class="col-md-6">
                                <input type="checkbox" class="form-checkbox" name="exam" id="exam" value="1" {{ $post->exam == 1 ? 'checked' : '' }}>
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
                                <input type="checkbox" class="form-checkbox" name="authorize" id="authorize" value="1" {{ $post->authorize == 1 ? 'checked' : '' }}>
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
                                <select class="form-select @error('metode_kelas') is-invalid @enderror" name="metode_kelas" value="{{ old('metode_kelas', $post->metode_kelas ) }}" required autocomplete="metode_kelas">
                                    <option value="Inhouse Bandung" @if ($post->metode_kelas == "Inhouse Bandung") selected @endif>Inhouse Bandung</option>
                                    <option value="Inhouse Luar Bandung" @if ($post->metode_kelas == "Inhouse Luar Bandung") selected @endif>Inhouse Luar Bandung</option>
                                    <option value="Offline" @if ($post->metode_kelas == "Offline") selected @endif>Offline</option>
                                    <option value="Virtual" @if ($post->metode_kelas == "Virtual") selected @endif>Virtual</option>
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
                                    <option value="Kelas" @if ($post->metode_kelas == "Kelas") selected @endif>Kelas</option>
                                    <option value="Workshop" @if ($post->metode_kelas == "Workshop") selected @endif>Workshop</option>
                                    <option value="Webinar" @if ($post->metode_kelas == "Webinar") selected @endif>Webinar</option>
                                    <option value="Narasumber" @if ($post->metode_kelas == "Narasumber") selected @endif>Narasumber</option>
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
                                @if (auth()->user()->jabatan == 'Customer Care' || auth()->user()->jabatan == 'Customer Service' || auth()->user()->jabatan == 'Admin Holding' || auth()->user()->jabatan == 'Technical Support')
                                <select disabled class="form-select @error('status') is-invalid @enderror" name="status" required autocomplete="status">
                                    <option {{ $post->status == null ? 'selected' : '' }} >Pilih Status</option>
                                    <option value="0" {{ $post->status == "0" ? 'selected' : '' }}>Merah</option>
                                    <option value="1" {{ $post->status == "1" ? 'selected' : '' }}>Biru</option>
                                    <option value="2" {{ $post->status == "2" ? 'selected' : '' }}>Hitam</option>
                                </select>
                                <input type="hidden" name="status"  value="{{$post->status}}"/>
                                @else
                                <select class="form-select @error('status') is-invalid @enderror" name="status" required autocomplete="status">
                                    <option {{ $post->status == null ? 'selected' : '' }} >Pilih Status</option>
                                    <option value="0" {{ $post->status == "0" ? 'selected' : '' }}>Merah</option>
                                    <option value="1" {{ $post->status == "1" ? 'selected' : '' }}>Biru</option>
                                    <option value="2" {{ $post->status == "2" ? 'selected' : '' }}>Hitam</option>
                                </select>
                                @endif
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

<script>
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
    $(document).ready(function() {
        $('#harga_jual').on('keyup', function(){
                    $(this).val(formatRupiah($(this).val(), 'Rp'));
        });
        $('select[name="status"]').change(function() {
                    toggleRegistForm();
                });
                toggleRegistForm();
        var perusahaan_key = '{{ $post->perusahaan_key }}'
        var materi_key = '{{ $post->materi_key }}'
        // var data = '{{ $post }}'
       // console.log(materi_key);
        $('#perusahaan_key').select2({
            placeholder: "Pilih Perusahaan",
            allowClear: true,
            ajax: {
                url: '{{route('getPerusahaan')}}',
                dataType: 'json',
                delay: 250,
                data: function (perusahaan_key) {
                    return {
                        q: $.trim(perusahaan_key.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.nama_perusahaan
                            };
                        })
                    };
                },
                cache: true
            }
        });
        function toggleRegistForm() {
            var status = $('select[name="status"]').val();
            if (status === '0') {
                $('#registform').show();
            } else {
                $('#registform').hide();
            }
        }
        $('#materi_key').select2({
            placeholder: "Pilih Materi",
            allowClear: true,
            ajax: {
                url: '{{ route('getMateris') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: $.trim(params.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.nama_materi
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#perusahaan_key, #materi_key').prop('disabled', true);
        $('#sales_key').prop('readonly', true);


    });
</script>

@endpush
@endsection
