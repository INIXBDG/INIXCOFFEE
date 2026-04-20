@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card p-0">
                <div class="card-body">
                    <a href="/rkm" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title">Detail Rencana Kelas Mingguan</h5>
                    <div class="row">
                        <div class="col-md-12">
                            @can('Assign RKM Instruktur')
                            <div class="col-md-8 col-sm-8 col-xs-8 my-2">
                                <a class="btn click-primary mx-1" href="{{ route('editInstruktur', $params) }}">Assign Instruktur dan Kelas
                                </a>
                            </div>
                            @endcan
                            @can('Assign RKM Kelas')
                            <div class="col-md-8 col-sm-8 col-xs-8 my-2">
                                <a class="btn click-primary mx-1" href="{{ route('editInstruktur', $params) }}">Assign Ruangan
                                </a>
                            </div>
                            @endcan
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                @php
                                $user = auth()->user();
                                $karyawan = DB::table('users')
                                ->join('karyawans', 'users.karyawan_id', '=', 'karyawans.id')
                                ->where('users.karyawan_id', $user->id)
                                ->first();
                                $kode_karyawan = $karyawan->kode_karyawan;
                                @endphp
                                @foreach ($rkm as $post)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="kelas-tab-{{ $post->id }}" data-bs-toggle="tab"
                                        data-bs-target="#kelas{{ $post->id }}" type="button" role="tab"
                                        aria-controls="home" aria-selected="true">{{ $post->sales_key }}</button>
                                </li>
                                @endforeach
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                @foreach ($rkm as $post)
                                    <div class="tab-pane fade active" id="kelas{{ $post->id }}" role="tabpanel"
                                        aria-labelledby="kelas-tab-{{ $post->id }}">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="row">
                                                    <div class="col-md-12 col-sm-12 col-xs-12 d-flex my-2">
                                                        @can('RegistrasiForm RKM')
                                                        <div class="col-md-3 col-sm-5 col-xs-5">
                                                            <a class="btn click-primary mx-1" href="{{ route('createRegistForm', $post->id) }}">Upload Registrasi Form</a>
                                                        </div>
                                                        @endcan
                                                        <div class="col-md-2 col-sm-5 col-xs-5">
                                                            <a class="btn click-primary mx-1" href="{{ route('detail.peluang', $post->peluang->id) }}" target="_blank">CRM</a>
                                                        </div>
                                                        @if ($kode_karyawan == $post->sales_key || auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'Customer Care')
                                                        @can('Edit RKM')
                                                        <a class="btn click-primary mx-1" href="{{ route('rkm.edit', $post->id) }}">
                                                            <img src="{{ asset('icon/edit.svg') }}" class="img-responsive" width="20px"> Edit RKM
                                                        </a>
                                                        @endcan
                                                        @can('Delete RKM')
                                                        <form onsubmit="return confirm('Apakah Anda Yakin ?');" action="{{ route('rkm.destroy', $post->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn click-danger mx-1">
                                                                <img src="{{ asset('icon/trash.svg') }}" class="img-responsive" width="20px"> Hapus RKM
                                                            </button>
                                                        </form>
                                                        @endcan
                                                        @endif
                                                        @can('Absensi RKM')
                                                        <div class="col-md-4 col-sm-4 col-xs-4">
                                                            <a class="btn click-primary mx-1" href="{{ route('absensiPeserta', $post->id) }}">
                                                                <img src="{{ asset('icon/user-check-white.svg') }}" class="img-responsive" width="20px"> Absensi
                                                            </a>
                                                        </div>
                                                        @endcan
                                                        @if ($post->ruang == "Inhouse" || $post->metode_kelas == "Inhouse Bandung" || $post->metode_kelas == "Inhouse Luar Bandung" || $post->metode_kelas == "Virtual")
                                                        @can('Souvenir RKM')
                                                        <div class="col-md-4 col-sm-4 col-xs-4 ml-auto">
                                                            <a class="btn click-primary mx-1" href="{{ route('createSouvenirInhouse', $post->id) }}">
                                                                <img src="{{ asset('icon/tag-white.svg') }}" class="img-responsive" width="20px"> Souvenir
                                                            </a>
                                                        </div>
                                                        @endcan
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>ID RKM</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->id }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Periode</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>

                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        @if (isset($post->quartal))
                                                        <p>{{ $post->quartal }} | {{$post->bulan}} {{$post->tahun}}</p>
                                                        @else
                                                        <p>-</p>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Materi</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->materi->nama_materi }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Harga Jual Nett</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ formatRupiah(floatval($post->harga_jual)) }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Pax</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->pax }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Tanggal Awal</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ \Carbon\Carbon::parse($post->tanggal_awal)->translatedFormat('l, d F Y') }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Tanggal Akhir</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ \Carbon\Carbon::parse($post->tanggal_akhir)->translatedFormat('l, d F Y') }}
                                                        </p>
                                                    </div>
                                                    @php
                                                    // use Carbon\Carbon;

                                                    $awal = \Carbon\Carbon::parse($post->tanggal_awal);
                                                    $akhir = \Carbon\Carbon::parse($post->tanggal_akhir);
                                                    $hari = $akhir->diffInDays($awal) + 1;
                                                    @endphp

                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Total Hari</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $hari }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Perusahaan</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->perusahaan->nama_perusahaan }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Nama Sales</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->sales->nama_lengkap }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Instruktur 1</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->instruktur_key }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Instruktur 2</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->instruktur_key2 }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Asisten</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->asisten_key }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Exam</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        @if($post->exam == 0)
                                                        <p>Tidak</p>
                                                        @else
                                                        <p>Ya</p>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Authorize</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        @if($post->authorize == 0)
                                                        <p>Tidak</p>
                                                        @else
                                                        <p>Ya</p>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Metode Kelas</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p>{{ $post->metode_kelas }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Ruang</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        <p> {{ $post->ruang }}</p>
                                                    </div>
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Status</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        @if ($post->status == '0')
                                                        <p>Merah</p>
                                                        @elseif ($post->status == '1')
                                                        <p>Biru</p>
                                                        @elseif ($post->status == '3')
                                                        <p>Hijau</p>
                                                        @else
                                                        <p>Hitam</p>
                                                        @endif
                                                        {{-- <p>{{ $post->status }}</p> --}}
                                                    </div>
                                                    @if ($post->metode_kelas == "Inhouse Luar Bandung" || $post->metode_kelas == "Inhouse Bandung" || $post->metode_kelas == "Virtual")
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Souvenir</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        {{-- {{$souvenir}} --}}
                                                        @if (isset($souvenir))
                                                        <p>{{ $souvenir->nama_souvenir }}</p>
                                                        @else
                                                        <p>-</p>
                                                        @endif
                                                    </div>
                                                    @endif
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Rekomendasi Materi Lanjutan</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        @php
                                                            $hasRekomendasi = $rkm->filter(function ($item) {
                                                                return $item->rekomendasilanjutan != null;
                                                            })->isNotEmpty();
                                                        // dd($hasRekomendasi);
                                                        @endphp
                                                        @if ($hasRekomendasi == true)
                                                        <button type="button" class="btn btn-sm btn-primary click-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalDetailRekomendasi-{{ $post->id }}">
                                                            Lihat Rekomendasi
                                                        </button>
                                                        @else
                                                        <p>-</p>
                                                        @endif
                                                    </div>
                                                    @if ( auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'Admin Sales' || auth()->user()->jabatan == 'Education Manager' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Koordinator Office' || auth()->user()->jabatan == 'GM' || auth()->user()->jabatan == 'Finance & Accounting' || auth()->user()->jabatan == 'Adm Sales' || $kode_karyawan == $post->sales_key)
                                                    <div class="col-md-4 col-sm-4 col-xs-4">
                                                        <p>Registrasi Form</p>
                                                        <p id="titikdua"> :</p>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                                        <p>:</p>
                                                    </div>
                                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                                        @if (isset($post->registrasi_form))
                                                        <a href="{{ asset('storage/' . $post->registrasi_form) }}" class="btn click-primary" target="_blank">Lihat Registrasi Form</a>
                                                        @else
                                                        <p>-</p>
                                                        @endif
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-7 p-0">
                                                @php
                                                $startsAt = \Carbon\Carbon::parse($post->tanggal_awal)->startOfDay();
                                                $endsAt = \Carbon\Carbon::parse($post->tanggal_akhir)->startOfDay();
                                                $hariAwal = $startsAt->isoFormat('dddd');
                                                $hariAkhir = $endsAt->isoFormat('dddd');
                                                $range = [];
                                                for ($date = $startsAt->copy(); $date->lte($endsAt); $date->addDay()) {
                                                $range[] = $date->copy();
                                                }
                                                $daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                                                @endphp
                                                <div class="table-responsive">
                                                    <table class="table table-responsive text-center" id="tabel">
                                                        <thead>
                                                            <tr>
                                                                @foreach ($daysOfWeek as $day)
                                                                <th>{{ $day }}</th>
                                                                @endforeach
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);">Sabtu</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);">Minggu</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                @if ($hariAwal == 'Senin' && $hariAkhir == 'Selasa')
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Senin' && $hariAkhir == 'Rabu')
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Senin' && $hariAkhir == 'Kamis')
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Senin' && $hariAkhir == 'Jumat')
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Senin' && $hariAkhir == 'Sabtu')
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);">v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Selasa' && $hariAkhir == 'Rabu')
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Selasa' && $hariAkhir == 'Kamis')
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Selasa' && $hariAkhir == 'Jumat')
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Selasa' && $hariAkhir == 'Sabtu')
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Rabu' && $hariAkhir == 'Kamis')
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Rabu' && $hariAkhir == 'Jumat')
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Rabu' && $hariAkhir == 'Sabtu')
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);">v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Kamis' && $hariAkhir == 'Jumat')
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Kamis' && $hariAkhir == 'Sabtu')
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);">v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Jumat' && $hariAkhir == 'Sabtu')
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);">v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Senin' && $hariAkhir == 'Senin')
                                                                <th>v</th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Selasa' && $hariAkhir == 'Selasa')
                                                                <th></th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Rabu' && $hariAkhir == 'Rabu')
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Kamis' && $hariAkhir == 'Kamis')
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Jumat' && $hariAkhir == 'Jumat')
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th>v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @elseif ($hariAwal == 'Sabtu' && $hariAkhir == 'Sabtu')
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);">v</th>
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);"></th>
                                                                @else
                                                                <th style="background-color: rgba(255, 0, 0, 0.5);">v</th>
                                                                @endif
                                                            </tr>
                                                            {{-- <tr>
                                                                    @foreach ($range as $date)
                                                                        <th>v</th>
                                                                    @endforeach
                                                                </tr> --}}
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="container">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    @php $formGenerated = false; @endphp
                                                                    @foreach ($rkm as $rkms)
                                                                    @if (
                                                                    !$formGenerated &&
                                                                    ($kode_karyawan == $rkms->sales_key ||
                                                                    $kode_karyawan == $rkms->instruktur_key ||
                                                                    $kode_karyawan == $rkms->instruktur_key2 ||
                                                                    $kode_karyawan == $rkms->asisten_key ||
                                                                    auth()->user()->jabatan == 'GM' ||
                                                                    auth()->user()->jabatan == 'SPV Sales' ||
                                                                    auth()->user()->jabatan == 'Adm Sales' ||
                                                                    auth()->user()->jabatan == 'Office Manager' ||
                                                                    auth()->user()->jabatan == 'Education Manager'||
                                                                    auth()->user()->jabatan == 'Customer Service'||
                                                                    auth()->user()->jabatan == 'Admin Holding'||
                                                                    auth()->user()->jabatan == 'Customer Care'||
                                                                    auth()->user()->jabatan == 'HRD'||
                                                                    auth()->user()->jabatan == 'Koordinator Office'||
                                                                    auth()->user()->jabatan == 'Finance & Accounting'||
                                                                    auth()->user()->jabatan == 'Technical Support'

                                                                    ))
                                                                    @php
                                                                    $formGenerated = true;
                                                                    // $path = $this->path();
                                                                    @endphp
                                                                    <div class="row">
                                                                        <form method="POST"
                                                                            action="{{ route('comment.store') }}">
                                                                            @csrf

                                                                            <input type="hidden" name="rkm_key"
                                                                                value="{{ $rkms->id }}">
                                                                            <input type="hidden" name="path"
                                                                                value="{{ request()->path() }}">
                                                                            <input type="hidden" name="karyawan_key"
                                                                                value="{{ auth()->user()->karyawan_id }}">
                                                                            <input type="hidden" name="materi_key"
                                                                                value="{{ $rkms->materi->nama_materi }}">
                                                                            <div class="mb-2">
                                                                                <textarea class="form-control" id="content" name="content"
                                                                                    minlength="10" maxlength="250" required
                                                                                    placeholder="Tulis komentar Anda..."></textarea>
                                                                                <small id="counter" class="text-muted">250</small>
                                                                            </div>
                                                                            <!-- <small class="text-muted">Komentar minimal 1 karakter, maksimal 250 karakter.</small> -->
                                                                            <button class="btn click-primary float-end mt-2"
                                                                                type="submit">Kirim</button>
                                                                        </form>
                                                                    </div>
                                                                    @endif
                                                                    @endforeach
                                                                    <div class="row my-2">
                                                                        <h3>Komentar</h3>
                                                                        @foreach ($comments->sortByDesc('created_at') as $comment)
                                                                        <p>{{ \Carbon\Carbon::parse($comment->created_at)->translatedFormat('d F Y \J\a\m H:i:s') }}
                                                                            | {{ $comment->karyawan->nama_lengkap }} :
                                                                            {{ $comment->content }}
                                                                        </p>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach ($rkm as $post)
        @if ($post->rekomendasilanjutan)
            <div class="modal fade" id="modalDetailRekomendasi-{{ $post->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Rekomendasi Lanjutan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="card mb-3 shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block fw-bold">Materi yang Direkomendasikan:</small>
                                        <ul class="list-group list-group-flush mt-1">
                                            @if(isset($post->rekomendasilanjutan->list_materi_lanjutan) && count($post->rekomendasilanjutan->list_materi_lanjutan) > 0)
                                                @foreach($post->rekomendasilanjutan->list_materi_lanjutan as $materi)
                                                    <li class="list-group-item bg-transparent py-1 ps-0">
                                                        <i class="fas fa-check-circle text-success me-1"></i> {{ $materi->nama_materi }}
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="list-group-item bg-transparent py-1 ps-0 text-danger">Data tidak ditemukan.</li>
                                            @endif
                                        </ul>
                                    </div>

                                    <div>
                                        <small class="text-muted d-block fw-bold">Keterangan:</small>
                                        <p class="mb-0 text-dark">{{ $post->rekomendasilanjutan->keterangan ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>

        <style>
            
            @media screen and (min-width: 769px) {

                /* CSS untuk layar web */
                #titikdua {
                    display: none;
                    /* Menyembunyikan titikdua pada layar web */
                }
            }

            @media screen and (max-width: 768px) {
                #titikdua {
                    display: flex;
                    /* Menampilkan titikdua */
                }

                .card {
                    padding: 15px;
                    max-width: 100%;
                }

                .card-body .row {
                    margin-bottom: 10px;
                }

                .col-xs-4,
                .col-sm-4 {
                    margin: 0 !important;
                    display: flex;
                }

                .col-xs-1 {
                    display: none;
                }

                .col-xs-7 {
                    width: 100%;
                    text-align: left;
                }
            }

            .cardname {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .click-secondary-icon {
                background: #355C7C;
                border-radius: 1000px;
                width: 45px;
                height: 45px;
                color: #ffffff;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
                text-decoration: none;
            }

            .click-secondary-icon i {
                line-height: 45px;
            }

            .click-secondary {
                background: #355C7C;
                border-radius: 1000px;
                padding: 10px 25px;
                color: #ffffff;
                display: inline-block;
                font: normal bold 18px/1 "Open Sans", sans-serif;
                text-align: center;
                transition: color 0.1s linear, background-color 0.2s linear;
            }

            .click-secondary:hover {
                color: #A5C7EF;
                transition: color 0.1s linear, background-color 0.2s linear;
            }

            .click-warning {
                background: #f8be00;
                border-radius: 1000px;
                padding: 10px 20px;
                color: #000000;
                display: inline-block;
                font: normal bold 18px/1 "Open Sans", sans-serif;
                text-align: center;
                transition: color 0.1s linear, background-color 0.2s linear;/
            }

            .click-danger {
                background: #962D2D;
                border-radius: 5px;
                padding: 5px 10px;
                color: #ffffff;
                display: inline-block;
                font: normal bold 14px/1 "Open Sans", sans-serif;
                text-align: center;
                transition: color 0.1s linear, background-color 0.2s linear;/
            }

            .click-warning:hover {
                background: #A5C7EF;
                transition: color 0.1s linear, background-color 0.2s linear;
            }

            .card {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                width: auto;
                height: auto;
                border: 1px solid rgba(255, 255, 255, .25);
                border-radius: 20px;
                background-color: rgba(255, 255, 255, 0.45);
                box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
                backdrop-filter: blur(2px);
            }

            .checkmark {
                display: block;
                width: 25px;
                height: 25px;
                border: 1px solid #ccc;
                border-radius: 50%;
                position: relative;
                margin: 0 auto;
            }

            .checkmark:after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #22bb33;
                display: none;
            }

            tr.selected .checkmark:after {
                display: block;
            }
            
        </style>
            <!-- counter script -->
            @endsection
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const textarea = document.getElementById('content');
                    const counter = document.getElementById('counter');
                    const maxLength = textarea.getAttribute('maxlength');

                    textarea.addEventListener('input', function() {
                        const remaining = maxLength - textarea.value.length;
                        counter.textContent = remaining + ' karakter tersisa';
                    });
                });
            </script>