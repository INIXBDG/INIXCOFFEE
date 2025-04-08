@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Tambah Peserta Baru') }}</h5>
                    <form method="POST" action="{{ route('registrasi.store') }}">
                        @csrf
                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('Rencana Kelas Mingguan') }}</label>
                            <div class="col-md-6">
                                <select style="height: 30px" class="form-select @error('id_rkm') is-invalid @enderror" name="id_rkm" id="id_rkm">
                                </select>
                                @error('id_rkm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div id="rkm-id" style="display: none;">
                            <div class="row mb-3">
                                <label for="nama_materi" class="col-md-4 col-form-label text-md-start">{{ __('Nama Materi') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="nama_materi" type="text"  class="form-control @error('nama_materi') is-invalid @enderror" name="nama_materi" value="{{ old('nama_materi') }}" autocomplete="nama_materi" autofocus>
                                    @error('nama_materi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_awal" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Awal') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="tanggal_awal" type="date" class="form-control @error('tanggal_awal') is-invalid @enderror" name="tanggal_awal" value="{{ old('tanggal_awal') }}" autocomplete="tanggal_awal" autofocus>
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
                                    <input readonly id="tanggal_akhir" type="date"  class="form-control @error('tanggal_akhir') is-invalid @enderror" name="tanggal_akhir" value="{{ old('tanggal_akhir') }}" autocomplete="tanggal_akhir" autofocus>
                                    @error('tanggal_akhir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-start">{{ __('Email') }}</label>
                            <div class="col-md-6">
                                    <input id="email" type="text" placeholder="Masukkan Email Peserta" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" autofocus>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                            </div>
                            <div class="col-md-1">
                                <button type="button" id="cek" class="btn btn-primary">Cek</button>
                            </div>
                        </div>

                        <div id="data-peserta" style="display: none;">
                            <div class="row mb-3">
                                <label for="nama" class="col-md-4 col-form-label text-md-start">{{ __('Nama Peserta') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="nama" type="text" placeholder="Masukan Nama Peserta" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama') }}" autocomplete="nama" autofocus>
                                    @error('nama')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="id_peserta" class="col-md-4 col-form-label text-md-start">{{ __('ID Peserta') }}</label>
                                <div class="col-md-6">
                                    <input type="text" readonly class="form-control" name="id_peserta" id="id_peserta" value="">
                                    @error('id_peserta')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="jenis_kelamin" class="col-md-4 col-form-label text-md-start">{{ __('Jenis Kelamin') }}</label>
                                <div class="col-md-6">
                                    <select readonly class="form-select" aria-label="jenis_kelamin" name="jenis_kelamin" id="jenis_kelamin">
                                        <option selected>Pilih Jenis Kelamin</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>@error('jenis_kelamin')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="no_hp" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Handphone') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="no_hp" type="text" placeholder="Masukan Nomor Handphone" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp') }}" autocomplete="no_hp" autofocus>
                                    @error('no_hp')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="alamat" class="col-md-4 col-form-label text-md-start">{{ __('Alamat') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="alamat" type="text" placeholder="Masukan Alamat" class="form-control @error('alamat') is-invalid @enderror" name="alamat" value="{{ old('alamat') }}" autocomplete="alamat" autofocus>
                                    @error('alamat')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="perusahaan_key" class="col-md-4 col-form-label text-md-start">{{ __('Perusahaan / Instansi') }}</label>
                                <div class="col-md-6">
                                    <select readonly style="height: 30px" class="form-select @error('perusahaan_key') is-invalid @enderror" name="perusahaan_key" id="perusahaan_key">
                                    </select>
                                    @error('perusahaan_key')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_lahir" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Lahir') }}</label>
                                <div class="col-md-6">
                                    <input readonly id="tanggal_lahir" type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" autocomplete="tanggal_lahir" autofocus>
                                    @error('tanggal_lahir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
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
.select2-container--default .select2-selection--single {
    height: 46px !important;
    padding: 10px 16px;
    font-size: 18px;
    line-height: 1.33;
    border-radius: 6px;
    width: 100%; /* Menetapkan lebar 100% */
}
.select2-container--default .select2-selection--single .select2-selection__arrow b {
    top: 85% !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px !important;
}
.select2-container--default .select2-selection--single {
    border: 1px solid #CCC !important;
    box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.075) inset;
    transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;
}

</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>

    <script>
           $(document).ready(function() {
            moment.locale('id');
                $('#id_rkm').select2({
                    placeholder: "Pilih RKM",
                    allowClear: true,
                    // theme: bootstrap,
                    ajax: {
                        url: '{{route('getRKMRegist')}}',
                        processResults: function({data}) {
                            // console.log(data);
                            return {
                                results: $.map(data, function(item) {
                                    // Use Moment.js to format the date
                                    var formattedDate = moment(item.tanggal_awal).format('DD-MM-YYYY');
                                    return {
                                        id: item.id,
                                        text: item.materi.nama_materi + ' - ' + item.nama_perusahaan + ' - ' + formattedDate
                                    };
                                })
                            };
                        }
                    }
                });
            });

              $('#cek').click(function () {
                    // e.preventDefault();
                    var email = $('#email').val();
                    $.get('{{ route('cekPeserta') }}', { email: email })
                        .done(function (response) {
                            if (response.peserta) {
                                var peserta = response.peserta;
                                $('#id_peserta').val(peserta.id);
                                $('#nama').val(peserta.nama);
                                $('#no_hp').val(peserta.no_hp);
                                $('#alamat').val(peserta.alamat);
                                $('#jenis_kelamin').val(peserta.jenis_kelamin);
                                $('#perusahaan_key').append('<option value="' + peserta.perusahaan.id + '">' + peserta.perusahaan.nama_perusahaan + '</option>');
                               if ( peserta.tanggal_lahir == null) {
                                $('#tanggal_lahir').val('');
                               } else {
                                $('#tanggal_lahir').val(peserta.tanggal_lahir);
                               }
                                $('#data-peserta').show(); // Menampilkan elemen div data peserta
                            } else {
                                $('#nama').val('');
                                $('#no_hp').val('');
                                $('#alamat').val('');
                                $('#perusahaan_key').empty();
                                $('#tanggal_lahir').val('');
                                $('#data-peserta').show(); // Menampilkan elemen div data peserta
                                $('#id_peserta').val('<?php echo $countPeserta; ?>'); // Menggunakan $countPeserta jika peserta tidak ditemukan
                                alert('Peserta tidak ditemukan');
                                $('#nama, #no_hp, #alamat, #perusahaan_key, #tanggal_lahir').removeAttr('readonly');

                            }
                        })
                        .fail(function (xhr, status, error) {
                            console.error(xhr.responseText);
                        });
            });
            $('#id_rkm').change(function () {
                var idRkm = $(this).val();
                // console.log(idRkm);
                if (idRkm) {
                    $.ajax({
                        type: 'GET',
                        url: '{{ route('getRKMDetail') }}',
                        data: { id_rkm: idRkm },
                        dataType: 'json',
                        success: function (response) {
                            if (response.rkm) {
                                var rkm = response.rkm;
                                $('#nama_materi').val(rkm.materi.nama_materi);
                                $('#tanggal_awal').val(rkm.tanggal_awal);
                                $('#tanggal_akhir').val(rkm.tanggal_akhir);
                                $('#rkm-id').show(); // Menampilkan elemen div data peserta

                            } else {
                                $('#nama_materi').val('');
                                $('#tanggal_awal').val('');
                                $('#tanggal_akhir').val('');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    $('#nama_materi').val('');
                    $('#tanggal_awal').val('');
                    $('#tanggal_akhir').val('');
                }
            });

            $('#perusahaan_key').select2({
                placeholder: "Pilih Perusahaan",
                allowClear: true,
                // theme: bootstrap,
                ajax: {
                    url: '{{route('getPerusahaan')}}',
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

    </script>
@endpush
@endsection
