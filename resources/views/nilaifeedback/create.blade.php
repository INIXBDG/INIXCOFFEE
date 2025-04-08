@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Pertanyaan Baru') }}</h5>
                    <form method="POST" action="{{ route('nilaifeedback.store') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('RKM') }}</label>
                            <div class="col-md-6">
                                <select class="form-select @error('id_rkm') is-invalid @enderror" name="id_rkm" id="id_rkm" value="{{ old('id_rkm' ) }}" required autocomplete="id_rkm">
                                    <option selected>Pilih RKM</option>
                                    @foreach ( $post as $rkm )
                                    <option value="{{ $rkm->id }}">{{ $rkm->materi->nama_materi }} - {{ $rkm->perusahaan->nama_perusahaan }} ({{ \Carbon\Carbon::parse($rkm->tanggal_awal)->translatedFormat('l, d F Y') }} - {{ \Carbon\Carbon::parse($rkm->tanggal_akhir)->translatedFormat('l, d F Y') }})</option>
                                    @endforeach
                                </select>
                                @error('id_rkm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="id_regist" class="col-md-4 col-form-label text-md-start">{{ __('Peserta') }}</label>
                            <div class="col-md-6">
                                <select id="id_regist" class="form-select @error('id_regist') is-invalid @enderror" name="id_regist" value="{{ old('id_regist' ) }}" required autocomplete="id_regist">
                                    <option>Pilih Peserta</option>
                                </select>
                                {{-- <input type="hidden" name="email" id="email"> --}}
                                @error('id_regist')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row" id="pertanyaan" style="height: 50vh; overflow-y:scroll; display:none;">
                            <div class="row">
                                <h4>{{ $materi[0]->kategori_feedback }}</h4>
                                @foreach ($materi as $data)
                                <div class="col-lg-6">
                                    <h2 class="h5">{{ $data->pertanyaan }}
                                    </h2>
                                    <p class="small">Pilih salah satu jawaban yang dikehendaki.</p>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class=" d-flex justify-content-between mb-2">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="1" id="{{ $data->key }}{{ $loop->iteration }}-1" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-1">Kurang</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="2" id="{{ $data->key }}{{ $loop->iteration }}-2" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-2">Cukup</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="3" id="{{ $data->key }}{{ $loop->iteration }}-3" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-3">Baik</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="4" id="{{ $data->key }}{{ $loop->iteration }}-4" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-4">Baik Sekali</label>
                                        </div>
                                    </div>
                                    <hr class="mt-4">
                                </div>
                                @endforeach
                                <hr>
                            </div>
                            <div class="row">
                                <h4>{{ $pelayanan[0]->kategori_feedback }}</h4>
                                @foreach ($pelayanan as $data)
                                <div class="col-lg-6">
                                    <h2 class="h5">{{ $data->pertanyaan }}
                                    </h2>
                                    <p class="small">Pilih salah satu jawaban yang dikehendaki.</p>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class=" d-flex justify-content-between mb-2">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="1" id="{{ $data->key }}{{ $loop->iteration }}-1" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-1">Kurang</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="2" id="{{ $data->key }}{{ $loop->iteration }}-2" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-2">Cukup</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="3" id="{{ $data->key }}{{ $loop->iteration }}-3" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-3">Baik</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="4" id="{{ $data->key }}{{ $loop->iteration }}-4" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-4">Baik Sekali</label>
                                        </div>
                                    </div>
                                    <hr class="mt-4">
                                </div>
                                @endforeach
                                <hr>
                            </div>
                            <div class="row">
                                <h4>{{ $fasilitas[0]->kategori_feedback }}</h4>

                                @foreach ($fasilitas as $data)
                                <div class="col-lg-6">
                                    <h2 class="h5">{{ $data->pertanyaan }}
                                    </h2>
                                    <p class="small">Pilih salah satu jawaban yang dikehendaki.</p>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class=" d-flex justify-content-between mb-2">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="1" id="{{ $data->key }}{{ $loop->iteration }}-1" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-1">Kurang</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="2" id="{{ $data->key }}{{ $loop->iteration }}-2" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-2">Cukup</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="3" id="{{ $data->key }}{{ $loop->iteration }}-3" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-3">Baik</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="4" id="{{ $data->key }}{{ $loop->iteration }}-4" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-4">Baik Sekali</label>
                                        </div>
                                    </div>
                                    <hr class="mt-4">
                                </div>
                                @endforeach
                                <hr>
                            </div>
                            <div class="row" id="instruktur">
                                <h4>{{ $instruktur[0]->kategori_feedback }}</h4>
                                @foreach ($instruktur as $data)
                                <div class="col-lg-6">
                                    <h2 class="h5">{{ $data->pertanyaan }}
                                    </h2>
                                    <p class="small">Pilih salah satu jawaban yang dikehendaki.</p>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class=" d-flex justify-content-between mb-2">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="1" id="{{ $data->key }}{{ $loop->iteration }}-1" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-1">Kurang</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="2" id="{{ $data->key }}{{ $loop->iteration }}-2" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-2">Cukup</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="3" id="{{ $data->key }}{{ $loop->iteration }}-3" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-3">Baik</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="4" id="{{ $data->key }}{{ $loop->iteration }}-4" name="{{ $data->key }}{{ $loop->iteration }}">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}-4">Baik Sekali</label>
                                    </div>
                                    </div>
                                        <hr class="mt-4">
                                </div>
                                    @endforeach
                                <hr>
                            </div>
                            <div class="row" id="instruktur2">
                                <h4>{{ $instruktur[0]->kategori_feedback }} #2</h4>
                                @foreach ($instruktur as $data)
                                <div class="col-lg-6">
                                    <h2 class="h5">{{ $data->pertanyaan }}</h2>
                                    <p class="small">Pilih salah satu jawaban yang dikehendaki.</p>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class=" d-flex justify-content-between mb-2">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="1" id="{{ $data->key }}{{ $loop->iteration }}b-1" name="{{ $data->key }}{{ $loop->iteration }}b">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}b-1">Kurang</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="2" id="{{ $data->key }}{{ $loop->iteration }}b-2" name="{{ $data->key }}{{ $loop->iteration }}b">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}b-2">Cukup</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="3" id="{{ $data->key }}{{ $loop->iteration }}b-3" name="{{ $data->key }}{{ $loop->iteration }}b">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}b-3">Baik</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="4" id="{{ $data->key }}{{ $loop->iteration }}b-4" name="{{ $data->key }}{{ $loop->iteration }}b">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}b-4">Baik Sekali</label>
                                        </div>
                                    </div>
                                        <hr class="mt-4">
                                </div>
                                @endforeach
                                <hr>
                            </div>
                            <div class="row" id="asisten">
                                <h4>Asisten</h4>
                                @foreach ($instruktur as $data)
                                <div class="col-lg-6">
                                    <h2 class="h5">{{ $data->pertanyaan }}</h2>
                                    <p class="small">Pilih salah satu jawaban yang dikehendaki.</p>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class=" d-flex justify-content-between mb-2">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="1" id="{{ $data->key }}{{ $loop->iteration }}as-1" name="{{ $data->key }}{{ $loop->iteration }}as">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}as-1">Kurang</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="2" id="{{ $data->key }}{{ $loop->iteration }}as-2" name="{{ $data->key }}{{ $loop->iteration }}as">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}as-2">Cukup</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="3" id="{{ $data->key }}{{ $loop->iteration }}as-3" name="{{ $data->key }}{{ $loop->iteration }}as">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}as-3">Baik</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" value="4" id="{{ $data->key }}{{ $loop->iteration }}as-4" name="{{ $data->key }}{{ $loop->iteration }}as">
                                            <label class="form-check-label" for="{{ $data->key }}{{ $loop->iteration }}as-4">Baik Sekali</label>
                                        </div>
                                    </div>
                                        <hr class="mt-4">
                                </div>
                                @endforeach
                                <hr>
                            </div>
                            <div class="row">
                                <h4>{{ $umum[0]->kategori_feedback }}</h4>
                                @foreach ($umum as $data)
                                <div class="col-lg-6">
                                    <h2 class="h5">{{ $data->pertanyaan }}</h2>
                                    <p class="small">Tulis Jawaban yang ingin disampaikan.</p>
                                </div>
                                <div class="col-lg-6 ">
                                    <div class=" d-flex justify-content-between mb-2">
                                        <textarea class="form-control" name="{{ $data->key }}{{ $loop->iteration }}" id="{{ $data->key }}{{ $loop->iteration }}" cols="12" rows="2"></textarea>
                                    </div>
                                        <hr class="mt-4">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-12 justify-content-end d-flex">
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
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    $(document).ready(function(){
        $('#id_rkm').select2({
            placeholder: 'Pilih RKM',
            allowClear: true
        });
        $('#id_rkm').on('change', function() {
            var id_rkm = $(this).val();
            // console.log(id_rkm);
            if(id_rkm) {
                $.ajax({
                    url: '{{ route("getRegistrasi") }}',
                    type: "GET",
                    data: {
                        id_rkm: id_rkm
                    },
                    dataType: "json",
                    success:function(data) {
                        // console.log(data);
                        $('#id_regist').empty();
                        cekRKM(id_rkm);
                        $.each(data.data, function(key, value) {
                            // console.log(value);
                            $('#id_regist').append('<option value="'+ value.id +'">'+ value.peserta.nama +'</option>');
                        });
                    }
                });
            } else {
                $('#id_regist').empty();
            }
        });
        function cekRKM(id_rkm){
            $('#pertanyaan').css('display', 'block');
            // console.log(id_rkm);
            if(id_rkm) {
                $.ajax({
                    url: '{{ route("getRKMDetail") }}',
                    type: "GET",
                    data: {
                        id_rkm: id_rkm
                    },
                    dataType: "json",
                    success:function(data) {
                        // console.log(data.rkm);
                        if (data.rkm.instruktur_key2 === '-') {
                            $('#instruktur2').css('display', 'none');
                        } else {
                            $('#instruktur2').css('display', 'block');
                        }

                        if (data.rkm.asisten_key === '-') {
                            $('#asisten').css('display', 'none');
                        } else {
                            $('#asisten').css('display', 'block');
                        }
                    }
                });
            } else {
                $('#id_regist').empty();
            }
        }

    });
</script>

@endsection
