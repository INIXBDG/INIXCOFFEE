@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="modal fade" id="modalPemberitahuan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="col-md-12 d-flex justify-content-between">
                        <h5 class="modal-title" id="exampleModalLabel">Pengumuman</h5>
                        @if (auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Koordinator Office')
                        <a href="{{ route('notif.create') }}" class="btn btn-sm btn-custom mx-4"><img src="{{ asset('icon/plus.svg') }}" class="" width="20px"></a>
                        @endif
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                    </div>
                </div>
                <div class="modal-body" style="overflow-y: scroll; height:400px">
                    {{-- {{ $notifikasi }} --}}
                    @if ($notifikasi->isEmpty())
                    <p>Tidak ada notifikasi</p>
                    @endif
                    @foreach ($notifikasi->sortByDesc('created_at') as $notif)

                    <div class="card-body" id="notif">
                        <table>
                            <tr>
                                <td style="width:80%">
                                    <div class="card-title" style="text-transform: capitalize">Pengumuman <strong>{{ $notif->tipe_notifikasi }}</strong> Dari {{ $notif->id_user }} <b>{{ $notif->users->jabatan }}</b>
                                        <p> {{ $notif->isi_notifikasi }} </p>
                                        <p class="m-0">{{ \Carbon\Carbon::parse($notif->created_at)->translatedFormat('d F Y \J\a\m H:i:s') }}</p>
                                    </div>
                                </td>
                                <td style="width:20%">
                                    {{-- <a href="#" class="btn btn-primary">View</a> --}}
                                    @if (auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Koordinator Office')
                                    <a href="{{ route('notif.edit', $notif->id) }}" class="btn btn-warning" id="dismiss-notification"><img src="{{ asset('icon/edit.svg') }}" class="" width="20px"></a>
                                    @endif
                                    <a href="#" class="btn btn-danger" id="dismiss-notification" style="padding-left: 7px;padding-right: 7px;">x</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <hr class="m-0" id="hr">
                    @endforeach
                    @if (auth()->user()->jabatan == 'Programmer')
                    Diupdate pada tanggal Oktober 2024

                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalAbsen" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> <!-- Tombol X -->
                </div>
                <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                    <div id="camera" style="width: 320px; height: 320px; border: 2px solid #ddd; border-radius: 5px;"></div>
                    <br />
                    {{-- <div class="d-flex flex-wrap w-100" id="absen" style="margin: 0px;"> --}}
                    <div class="row g-3" style="height:120px">
                        <div class="col-6 d-flex align-items-center m-0">
                            <input type="radio" class="form-check-input custom-radio" name="keterangan" id="normal" autocomplete="off" value="Kantor">
                            <label class="form-check-label ms-2" for="normal">Absen Normal</label>
                        </div>
                        <div class="col-6 d-flex align-items-center m-0">
                            <input type="radio" class="form-check-input custom-radio" name="keterangan" id="inhouse" autocomplete="off" value="Inhouse Bandung">
                            <label class="form-check-label ms-2" for="inhouse">Absen Inhouse Bandung Raya</label>
                        </div>
                        <div class="col-6 d-flex align-items-center m-0">
                            <input type="radio" class="form-check-input custom-radio" name="keterangan" id="spj" autocomplete="off" value="SPJ">
                            <label class="form-check-label ms-2" for="spj">Absen SPJ</label>
                        </div>
                    </div>
                    {{-- </div> --}}

                    <br />
                    <div class="d-flex flex-row justify-content-between w-100">
                        <button id="takeSnapshot" class="btn btn-primary mx-2">Absen Masuk</button>
                        {{-- <button id="tipeabsen" class="btn btn-primary mx-2">Absen Masuk</button> --}}
                        <button id="pulang" class="btn btn-danger mx-2">Absen Pulang</button>
                    </div>

                    <br />
                    <div id="result" class="" style="width: 320px; text-align: center;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Tutup</button> <!-- Tombol Tutup -->
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-between">
        <div class="col-md-12 col-sm-12 col-xs-12 col-lg-6 col-xl-6">
            {{-- input tabs --}}
            <div class="row">
                {{-- karyawan --}}
                <div class="col-md-12 mt-1">
                    {{-- content --}}
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-center card-title">Karyawan</h5>
                            <div class="row">
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/user.svg') }}" class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/profile/{{ auth()->user()->id }}" class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">Profil Saya</h5>
                                                </a>
                                                <p class="card-text">Profil saya sebagai karyawan INIXINDO Bandung.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/users.svg') }}" class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/user" class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">Data Karyawan</h5>
                                                </a>
                                                <p class="card-text">Data lengkap semua karyawan.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if (auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Direktur' || auth()->user()->jabatan == 'Direktur Utama' || auth()->user()->jabatan == 'Koordinator Office')
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/award.svg') }}" class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/jabatan" class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">Jabatan</h5>
                                                </a>
                                                <p class="card-text">Data Jabatan.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/bell.svg') }}" class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="#" class="link stretched-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalPemberitahuan">
                                                    <h5 class="card-title">Pengumuman</h5>
                                                </a>
                                                <p class="card-text">Pemberitahuan.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/camera.svg') }}" class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="#" id="btnAbsen" class="link stretched-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalAbsen">
                                                    <h5 class="card-title">Absen</h5>
                                                </a>
                                                <p class="card-text">Absensi.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if (auth()->user()->jabatan == 'HRD')
                                <div class="col-sm-6 mt-2">
                                    <div class="card" id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/archive.svg') }}" class="img-responsive" width="30px">
                                            </div>
                                            <div class="col-md-10" style="margin-left: 10px">
                                                <a href="/rekapitulasiabsen" class="link stretched-link text-decoration-none">
                                                    <h5 class="card-title">Rekapitulasi Absensi</h5>
                                                </a>
                                                <p class="card-text">Data Rekapitulasi Absen Karyawan.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                {{-- @if (auth()->user()->jabatan == 'Programmer')
                                    <div class="col-sm-6 mt-2">
                                        <div class="card"  id="card-hover">
                                            <div class="card-body d-flex">
                                                <div class="col-md-2">
                                                    <img src="{{ asset('icon/bell.svg') }}" class="img-responsive" width="30px">
                            </div>
                            <div class="col-md-10" style="margin-left: 10px">
                                <a href="/user-dropdown" class="link stretched-link text-decoration-none">
                                    <h5 class="card-title">Shortcut</h5>
                                </a>
                                <p class="card-text">shortcut.</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif --}}
                <div class="col-sm-6 mt-2">
                    <div class="card" id="card-hover">
                        <div class="card-body d-flex">
                            <div class="col-md-2">
                                <img src="{{ asset('icon/calendar.svg') }}" class="img-responsive" width="30px">
                            </div>
                            <div class="col-md-10" style="margin-left: 10px">
                                <a href="/absensi/karyawan" class="link stretched-link text-decoration-none">
                                    <h5 class="card-title">Catatan Absensi
                                    </h5>
                                </a>
                                <p class="card-text">Absensi anda pada bulan ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 mt-2">
                    <div class="card" id="card-hover">
                        <div class="card-body d-flex">
                            <div class="col-md-2">
                                <img src="{{ asset('icon/clock.svg') }}" class="img-responsive" width="30px">
                            </div>
                            <div class="col-md-10" style="margin-left: 10px">
                                <a href="/pengajuancuti" class="link stretched-link text-decoration-none">
                                    <h5 class="card-title">Pengajuan Cuti</h5>
                                </a>
                                <p class="card-text">Klik disini untuk pengajuan cuti.</p>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-sm-6 mt-2">
                                    <div class="card"  id="card-hover">
                                        <div class="card-body d-flex">
                                            <div class="col-md-2">
                                                <img src="{{ asset('icon/feather.svg') }}" class="img-responsive" width="30px">
            </div>
            <div class="col-md-10" style="margin-left: 10px">
                <a href="/pengajuanbarang" class="link stretched-link text-decoration-none">
                    <h5 class="card-title">Pengajuan Barang</h5>
                </a>
                <p class="card-text">Klik disini untuk pengajuan barang.</p>
            </div>
        </div>
    </div>
</div> --}}
<div class="col-sm-6 mt-2">
    <div class="card" id="card-hover">
        <div class="card-body d-flex">
            <div class="col-md-2">
                <img src="{{ asset('icon/send.svg') }}" class="img-responsive" width="30px">
            </div>
            <div class="col-md-10" style="margin-left: 10px">
                <a href="/suratperjalanan" class="link stretched-link text-decoration-none">
                    <h5 class="card-title">Pengajuan SPJ</h5>
                </a>
                <p class="card-text">Klik disini untuk pengajuan SPJ.</p>
            </div>
        </div>
    </div>
</div>

</div>
</div>
</div>
</div>
{{-- end karyawan --}}
{{-- peserta --}}
@if ( auth()->user()->jabatan == 'Customer Care' ||
auth()->user()->jabatan == 'Adm Sales' ||
auth()->user()->jabatan == 'GM' ||
auth()->user()->jabatan == 'SPV Sales' ||
auth()->user()->jabatan == 'Sales' ||
auth()->user()->jabatan == 'Komisaris' ||
auth()->user()->jabatan == 'Direktur' ||
auth()->user()->jabatan == 'Direktur Utama' ||
auth()->user()->jabatan == 'Education Manager' ||
auth()->user()->jabatan == 'Instruktur' ||
auth()->user()->jabatan == 'Technical Support' ||
auth()->user()->jabatan == 'Finance & Accounting' ||
auth()->user()->jabatan == 'Customer Service' ||
auth()->user()->jabatan == 'Customer Care' ||
auth()->user()->jabatan == 'HRD' ||
auth()->user()->jabatan == 'Office Manager' ||
auth()->user()->jabatan == 'Admin Holding' )
<div class="col-md-12 mt-1">
    <div class="card">
        <div class="card-body">
            <h5 class="text-center card-title">Peserta</h5>
            <div class="row">
                <div class="col-sm-6 mt-2">
                    <div class="card" id="card-hover">
                        <div class="card-body d-flex">
                            <div class="col-md-2">
                                <img src="{{ asset('icon/table.svg') }}" class="img-responsive" width="30px">
                            </div>
                            <div class="col-md-10" style="margin-left: 10px">
                                <a href="/peserta" class="link stretched-link text-decoration-none">
                                    <h5 class="card-title">Data Peserta</h5>
                                </a>
                                <p class="card-text">Data Peserta yang mengikuti kelas.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 mt-2">
                    <div class="card" id="card-hover">
                        <div class="card-body d-flex">
                            <div class="col-md-2">
                                <img src="{{ asset('icon/user-check.svg') }}" class="img-responsive" width="30px">
                            </div>
                            <div class="col-md-10" style="margin-left: 10px" id="">
                                <a href="/registrasi" class="link stretched-link text-decoration-none">
                                    <h5 class="card-title">Registrasi</h5>
                                </a>
                                <p class="card-text">Registrasi peserta kelas.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 mt-2">
                    <div class="card" id="card-hover">
                        <div class="card-body d-flex">
                            <div class="col-md-2">
                                <img src="{{ asset('icon/briefcase.svg') }}" class="img-responsive" width="30px">
                            </div>
                            <div class="col-md-10" style="margin-left: 10px">
                                <a href="/perusahaan" class="link stretched-link text-decoration-none">
                                    <h5 class="card-title">Perusahaan</h5>
                                </a>
                                <p class="card-text">Data Perusahaan.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 mt-2">
                    <div class="card" id="card-hover">
                        <div class="card-body d-flex">
                            <div class="col-md-2">
                                <img src="{{ asset('icon/list-check.svg') }}" class="img-responsive" width="30px">
                            </div>
                            <div class="col-md-10" style="margin-left: 10px">
                                <a href="/registexam" class="link stretched-link text-decoration-none">
                                    <h5 class="card-title">Registrasi Exam</h5>
                                </a>
                                <p class="card-text">Data Registrasi Kelas Exam.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
{{-- end peserta --}}
</div>
{{-- endinput tabs --}}
</div>
@if ( auth()->user()->jabatan == 'Customer Care' ||
auth()->user()->jabatan == 'Adm Sales' ||
auth()->user()->jabatan == 'GM' ||
auth()->user()->jabatan == 'SPV Sales' ||
auth()->user()->jabatan == 'Sales' ||
auth()->user()->jabatan == 'Komisaris' ||
auth()->user()->jabatan == 'Direktur' ||
auth()->user()->jabatan == 'Direktur Utama' ||
auth()->user()->jabatan == 'Education Manager' ||
auth()->user()->jabatan == 'Instruktur' ||
auth()->user()->jabatan == 'Technical Support' ||
auth()->user()->jabatan == 'Finance & Accounting' ||
auth()->user()->jabatan == 'Customer Service' ||
auth()->user()->jabatan == 'Customer Care' ||
auth()->user()->jabatan == 'HRD' ||
auth()->user()->jabatan == 'Office Manager' ||
auth()->user()->jabatan == 'Tim Digital' ||
auth()->user()->jabatan == 'Admin Holding' )
<div class="col-md-12 col-sm-12 col-xs-12 col-lg-6 col-xl-6">
    {{-- input tabs --}}
    <div class="row">
        {{-- RKM --}}
        <div class="col-md-12 mt-1">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-center card-title">Rencana Kelas Mingguan</h5>
                    <div class="row">
                        @if (auth()->user()->jabatan == 'Tim Digital')
                        <div class="col-sm-6 mt-2">
                            <div class="card" id="card-hover">
                                <div class="card-body d-flex">
                                    <div class="col-md-2">
                                        <img src="{{ asset('icon/calendar.svg') }}" class="img-responsive" width="30px">
                                    </div>
                                    <div class="col-md-10" style="margin-left: 10px">
                                        <a href="/rkm" class="link stretched-link text-decoration-none">
                                            <h5 class="card-title">Rencana Kelas Mingguan</h5>
                                        </a>
                                        <p class="card-text">Rencana kelas Training.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="col-sm-6 mt-2">
                            <div class="card" id="card-hover">
                                <div class="card-body d-flex">
                                    <div class="col-md-2">
                                        <img src="{{ asset('icon/calendar.svg') }}" class="img-responsive" width="30px">
                                    </div>
                                    <div class="col-md-10" style="margin-left: 10px">
                                        <a href="/rkm" class="link stretched-link text-decoration-none">
                                            <h5 class="card-title">Rencana Kelas Mingguan</h5>
                                        </a>
                                        <p class="card-text">Rencana kelas Training.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 mt-2">
                            <div class="card" id="card-hover">
                                <div class="card-body d-flex">
                                    <div class="col-md-2">
                                        <img src="{{ asset('icon/book-open.svg') }}" class="img-responsive" width="30px">
                                    </div>
                                    <div class="col-md-10" style="margin-left: 10px">
                                        <a href="/materi" class="link stretched-link text-decoration-none">
                                            <h5 class="card-title">Materi</h5>
                                        </a>
                                        <p class="card-text">Data Materi.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 mt-2">
                            <div class="card" id="card-hover">
                                <div class="card-body d-flex">
                                    <div class="col-md-2">
                                        <img src="{{ asset('icon/file-text.svg') }}" class="img-responsive" width="30px">
                                    </div>
                                    <div class="col-md-10" style="margin-left: 10px">
                                        <a href="/feedback" class="link stretched-link text-decoration-none">
                                            <h5 class="card-title">Feedback</h5>
                                        </a>
                                        <p class="card-text">Feedback Pelayanan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 mt-2">
                            <div class="card" id="card-hover">
                                <div class="card-body d-flex">
                                    <div class="col-md-2">
                                        <img src="{{ asset('icon/assept-document.svg') }}" class="img-responsive" width="30px">
                                    </div>
                                    <div class="col-md-10" style="margin-left: 10px">
                                        <a href="/exam" class="link stretched-link text-decoration-none">
                                            <h5 class="card-title">Pengajuan Exam</h5>
                                        </a>
                                        <p class="card-text">Pengajuan Exam.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (auth()->user()->jabatan == 'Technical Support' || auth()->user()->jabatan == 'Education Manager' || auth()->user()->jabatan == 'Direktur' || auth()->user()->jabatan == 'Direktur Utama')
                        <div class="col-sm-6 mt-2">
                            <div class="card" id="card-hover">
                                <div class="card-body d-flex">
                                    <div class="col-md-2">
                                        <img src="{{ asset('icon/list-check.svg') }}" class="img-responsive" width="30px">
                                    </div>
                                    <div class="col-md-10" style="margin-left: 10px">
                                        <a href="/listexams" class="link stretched-link text-decoration-none">
                                            <h5 class="card-title">List Exam</h5>
                                        </a>
                                        <p class="card-text">Data Exam.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if (auth()->user()->jabatan == 'Education Manager' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'SPV Sales' || auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'GM')
                        <div class="col-sm-6 mt-2">
                            <div class="card" id="card-hover">
                                <div class="card-body d-flex">
                                    <div class="col-md-2">
                                        <img src="{{ asset('icon/stats.svg') }}" class="img-responsive" width="30px">
                                    </div>
                                    <div class="col-md-10" style="margin-left: 10px">
                                        <a href="/kelasanalisis" class="link stretched-link text-decoration-none">
                                            <h5 class="card-title">Kelas Analisis</h5>
                                        </a>
                                        <p class="card-text">Analisis Rencana Kelas Mingguan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        {{-- end RKM --}}
        {{-- input tabs --}}
        <div class="row">
            {{-- RKM --}}
            <div class="col-md-12 mt-1">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-center card-title">Finance</h5>
                        <div class="row">
                            @if (auth()->user()->jabatan == 'Finance & Accounting' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Technical Support')
                            <div class="col-sm-6 mt-2">
                                <div class="card" id="card-hover">
                                    <div class="card-body d-flex">
                                        <div class="col-md-2">
                                            <img src="{{ asset('icon/credit-card.svg') }}" class="img-responsive" width="30px">
                                        </div>
                                        <div class="col-md-10" style="margin-left: 10px">
                                            <a href="/creditcard" class="link stretched-link text-decoration-none">
                                                <h5 class="card-title">Credit Card</h5>
                                            </a>
                                            <p class="card-text">Data Credit Card.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if (auth()->user()->jabatan == 'Customer Care' || auth()->user()->jabatan == 'HRD' || auth()->user()->jabatan == 'Office Manager' || auth()->user()->jabatan == 'Direktur' || auth()->user()->jabatan == 'Direktur Utama' || auth()->user()->jabatan == 'GM' || auth()->user()->jabatan == 'Admin Holding')
                            <div class="col-sm-6 mt-2">
                                <div class="card" id="card-hover">
                                    <div class="card-body d-flex">
                                        <div class="col-md-2">
                                            <img src="{{ asset('icon/award.svg') }}" class="img-responsive" width="30px">
                                        </div>
                                        <div class="col-md-10" style="margin-left: 10px">
                                            <a href="/souvenir" class="link stretched-link text-decoration-none">
                                                <h5 class="card-title">Souvenir</h5>
                                            </a>
                                            <p class="card-text">Data Souvenir.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            {{-- end RKM --}}
        </div>
        {{-- endinput tabs --}}
    </div>
    {{-- endinput tabs --}}

</div>
@endif
{{-- col-6 akhir --}}
</div>
</div>
<style>
    .custom-radio {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #007bff;
        appearance: none;
        -webkit-appearance: none;
        outline: none;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .custom-radio:checked {
        background-color: #007bff;
    }

    .form-check-label {
        font-size: 14px;
        text-justify: inter-word;
        margin-left: 5px;
    }

    @media screen and (max-width: 768px) {
        a {
            width: auto;
            max-width: 100%;
        }
    }

    #notif {
        padding: 0.5rem;

        table {
            width: 100%;

            tr {
                display: flex;

                td {
                    a.btn {
                        font-size: 0.8rem;
                        padding: 3px;
                    }
                }

                td:nth-child(2) {
                    text-align: right;
                    justify-content: space-around;
                }
            }
        }

    }

    .btn-custom {
        background-color: #182F51;
        color: white;
    }

    .btn-custom:hover {
        background-color: #355C7C;
        color: white;
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{asset('js/webcam.js')}}"></script>

<script>
    $(document).ready(function() {
        handleNotificationDismissal()
        $('#modalPemberitahuan').modal('show');
        cekip();
    });

    function cekip() {
        $.ajax({
            url: "{{ route('cekip') }}", // Sesuaikan dengan route Anda
            type: 'GET',
            success: function(response) {
                var data = response.success;
                if (data === 'Absen Normal') {
                    // Disable the Inhouse and SPJ radio buttons
                    $('#inhouse').prop('disabled', true);
                    $('#spj').prop('disabled', true);
                    $('#normal').prop('checked', true);

                    // Enable the Normal radio button
                    $('#normal').prop('disabled', false);
                } else if (data === 'Absen Luar') {
                    // Disable the Normal radio button
                    $('#normal').prop('disabled', true);

                    // Enable the Inhouse and SPJ radio buttons
                    $('#inhouse').prop('disabled', false);
                    $('#spj').prop('disabled', false);
                } else {
                    // If some other value, enable all buttons
                    $('#normal').prop('disabled', false);
                    $('#inhouse').prop('disabled', false);
                    $('#spj').prop('disabled', false);
                }
                $('#absen').show();
            },
            error: function(xhr, status, error) {
                alert(xhr.responseJSON.error);
                // console.log(xhr.responseJSON.error);
            }
        });
    }

    $('#btnAbsen').on('click', function(e) {
        let stream;

        Webcam.set({
            width: 320, // Adjust the width as needed for mobile devices
            height: 320, // Adjust the height as needed for mobile devices
            image_format: 'jpeg',
            jpeg_quality: 50,
            force_flash: false,
            flip_horiz: true,
            constraints: {
                facingMode: "user", // Use "environment" for the rear camera
                width: {
                    ideal: 320
                }, // You can use ideal to fit the device's resolution
                height: {
                    ideal: 320
                }
            }
        });

        Webcam.attach('#camera');

        Webcam.on('live', function() {
            stream = Webcam.stream;
        });

        // Ambil foto ketika tombol ditekan
        $('#takeSnapshot').off('click').on('click', function() {
            Webcam.snap(function(data_uri) {
                // Display the captured image
                $('#result').html('<img src="' + data_uri + '"/>');

                const now = new Date();
                const tanggal = now.toISOString().split('T')[0]; // Extract date in yyyy-mm-dd format
                const jam_masuk = now.toTimeString().split(' ')[0];
                // const jam_pulang = now.toTimeString().split(' ')[0];

                var karyawan = "{{ auth()->user()->karyawan_id }}";
                var jabatan = "{{ auth()->user()->jabatan}}";
                var keterangan = $('input[name="keterangan"]:checked').val(); // Get the selected radio button value

                // Determine shift based on current hour
                // var now = new Date();
                var jamSekarang = now.getHours();
                var hariSekarang = now.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
                var shift = null;

                if (jabatan == 'Office Boy') {
                    if (hariSekarang === 6 || hariSekarang === 0) { // Saturday (6) and Sunday (0)
                        // Weekend Shift (Saturday and Sunday)
                        if (jamSekarang >= 3 && jamSekarang < 8) {
                            shift = 1; // Shift 1: 05:00 - 08:00
                        } else if (jamSekarang >= 8 && jamSekarang < 23) {
                            shift = 2; // Shift 2: 08:00 - 23:50
                        } else {
                            shift = 'Tidak Sesuai Shift'; // Outside of shift hours
                        }
                    } else {
                        // Weekday Shift (Monday to Friday)
                        if (jamSekarang >= 3 && jamSekarang < 10) {
                            shift = 1; // Shift 1: 05:00 - 10:00
                        } else if (jamSekarang >= 14 && jamSekarang < 23) {
                            shift = 2; // Shift 2: 16:00 - 23:50
                        } else {
                            shift = 'Tidak Sesuai Shift'; // Outside of shift hours
                        }
                    }
                } else {
                    shift = 1; // Default for other roles
                }


                // Send the captured image to the server
                $.ajax({
                    url: "{{ route('absensi.store') }}", // Adjust to your route
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_karyawan: karyawan,
                        tanggal: tanggal,
                        jabatan: jabatan,
                        jam_masuk: jam_masuk,
                        // jam_pulang: jam_pulang,
                        keterangan: keterangan, // Send the selected value
                        shift: shift, // Send shift value
                        foto: data_uri,
                    },
                    success: function(response) {
                        alert(response.success);
                        $('#modalPemberitahuan').modal('hide');
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert(xhr.responseJSON.error);
                        // console.log(xhr.responseJSON.error);
                        location.reload();
                    }
                });
            });
        });

        $('#pulang').off('click').on('click', function() {
            const now = new Date();
            const tanggal = now.toISOString().split('T')[0]; // Extract date in yyyy-mm-dd format
            const jam_pulang = now.toTimeString().split(' ')[0];
            var karyawan = "{{auth()->user()->karyawan_id}}";
            var jabatan = "{{ auth()->user()->jabatan}}";
            // Determine shift based on current hour
            // var jamSekarang = now.getHours();
            var shift = null;
            if (jabatan == 'Office Boy' || jabatan == 'Driver') {
                if (jam_pulang >= '14:00:00' && jam_pulang < '23:00:00') {
                    shift = 1;
                } else if (jam_pulang >= '01:00:00' && jam_pulang <= '12:00:00') {
                    shift = 2;
                } else {
                    shift = 'Tidak Sesuai Shift'; // Handle case if time is outside defined shift hours
                }
            } else {
                shift = 1;
            }
            // console.log(shift);

            // Kirim data absen pulang ke server
            $.ajax({
                url: "{{ route('absensi.update') }}", // Sesuaikan dengan route Anda untuk absen pulang
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id_karyawan: karyawan,
                    tanggal: tanggal,
                    jam_keluar: jam_pulang,
                    shift: shift,
                },
                success: function(response) {
                    alert(response.success);
                    $('#modalPemberitahuan').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert(xhr.responseJSON.error);
                    // console.log(xhr.responseJSON.error)
                    location.reload();
                }
            });
        });
    });

    // Hentikan kamera saat modal ditutup (baik melalui tombol X maupun Tutup)
    $('#modalAbsen').on('hidden.bs.modal', function() {
        Webcam.reset(); // Reset the webcam, stopping the stream
    });

    function handleNotificationDismissal() {
        // Prevent default action of the button
        // event.preventDefault();

        // Hide the closest card-body to the clicked button
        $(this).closest('.card-body').hide();

        // Check if there are any visible notifications left
        if ($('#modalPemberitahuan .card-body:visible').length == 0) {
            $('hr').hide();
            // $('#modalPemberitahuan .modal-body').append('<p>Tidak ada notifikasi</p>');
        }
    }

    // Attach the function to the click event of the .btn-danger element
    $('#modalPemberitahuan').on('click', '.btn-danger', handleNotificationDismissal);
</script>
@endsection