@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">

                <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                    <h3 class="mb-0">{{ __('Detail CV Instruktur') }}</h3>
                    <div>
                        <a href="{{ route('cv-instruktur.pdf', $user->id) }}" class="btn btn-primary btn-sm me-2" target="_blank">
                            Download PDF
                        </a>
                        <a href="{{ route('cv-instruktur.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-5 bg-white">

                        <div class="row mb-5">
                            <div class="col-md-3 text-center">
                                @if(optional($user->karyawan)->foto)
                                    <img src="{{ asset('storage/posts/'.$user->karyawan->foto) }}" alt="Foto Profile" class="img-fluid border" style="width: 200px; height: 260px; object-fit: cover;">
                                @else
                                    <div class="bg-light border d-flex justify-content-center align-items-center" style="width: 200px; height: 260px; margin: 0 auto;">
                                        <span class="text-muted">No Image</span>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-9">
                                <h4 class="mb-3"><strong class="text-decoration-underline">Personal Information</strong></h4>
                                <table class="table table-borderless table-sm w-75">
                                    <tbody>
                                        <tr>
                                            <td style="width: 35%;">Name:</td>
                                            <td>{{ optional($user->karyawan)->nama_lengkap ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Gender:</td>
                                            <td>{{ optional($user->karyawan)->gender ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Place of Birth:</td>
                                            <td>{{ optional($user->karyawan)->tempat_lahir ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Date of Birth:</td>
                                            <td>
                                                @if(optional($user->karyawan)->tanggal_lahir)
                                                    {{ \Carbon\Carbon::parse($user->karyawan->tanggal_lahir)->format('d F Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Status:</td>
                                            <td>Single</td>
                                        </tr>
                                        <tr>
                                            <td>Religion:</td>
                                            <td>{{ optional($user->karyawan)->religion ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Nationality:</td>
                                            <td>Indonesian</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-12 px-4">
                                <h4 class="mb-3"><strong class="text-decoration-underline">Contact Information</strong></h4>
                                <table class="table table-borderless table-sm w-75">
                                    <tbody>
                                        <tr>
                                            <td style="width: 26%;">Address:</td>
                                            <td>{{ optional($user->karyawan)->alamat_lengkap ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>City:</td>
                                            <td>{{ optional($user->karyawan)->kota ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Province:</td>
                                            <td>{{ optional($user->karyawan)->provinsi ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Country:</td>
                                            <td>Indonesia</td>
                                        </tr>
                                        <tr>
                                            <td>Postal Code:</td>
                                            <td>-</td>
                                        </tr>
                                        <tr>
                                            <td>Mobile Phone Number:</td>
                                            <td>{{ optional($user->karyawan)->whatsapp ?? (optional($user->karyawan)->telepon ?? '-') }}</td>
                                        </tr>
                                        <tr>
                                            <td>e-mail Address:</td>
                                            <td>
                                                @if(optional($user->karyawan)->email)
                                                    <a href="mailto:{{ $user->karyawan->email }}" class="text-primary text-decoration-underline">
                                                        {{ $user->karyawan->email }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-12 px-4">
                                <h4 class="mb-3"><strong class="text-decoration-underline">Educational Background</strong></h4>
                                <table class="table table-borderless table-sm w-75">
                                    <tbody>
                                        <tr>
                                            <td style="width: 26%;">Academic Qualification</td>
                                            <td>
                                                @if(optional($user->karyawan)->educations && $user->karyawan->educations->isNotEmpty())
                                                    @foreach($user->karyawan->educations as $education)
                                                        <div>: {{ $education->name }}</div>
                                                    @endforeach
                                                @else
                                                    : -
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-12 px-4">
                                <h4 class="mb-3"><strong class="text-decoration-underline">Certification</strong></h4>
                                <ul style="list-style-type: disc; padding-left: 40px;">
                                    @if(isset($user->sertifikasis) && $user->sertifikasis->isNotEmpty())
                                        @foreach($user->sertifikasis as $sertifikat)
                                            @php
                                                $isRetired = !empty($sertifikat->tanggal_berlaku_sampai) && \Carbon\Carbon::parse($sertifikat->tanggal_berlaku_sampai)->isPast();
                                            @endphp
                                            <li>
                                                {{ $sertifikat->nama_sertifikat }} {{ $isRetired ? '(retired)' : '' }}
                                            </li>
                                        @endforeach
                                    @else
                                        <li>-</li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-12 px-4">
                                <h4 class="mb-3"><strong class="text-decoration-underline">Specialization Area</strong></h4>
                                <ul style="list-style-type: disc; padding-left: 40px;">
                                    @if(optional($user->karyawan)->specializations && $user->karyawan->specializations->isNotEmpty())
                                        @foreach($user->karyawan->specializations as $sp)
                                            <li class="mb-2">
                                                {{ $sp->specialization }}
                                                @if($sp->detail_specialization)
                                                    <br>
                                                    {{ $sp->detail_specialization }}
                                                @endif
                                            </li>
                                        @endforeach
                                    @else
                                        <li>-</li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-12 px-4">
                                <h4 class="mb-3"><strong class="text-decoration-underline">Seminar and Event Experience</strong></h4>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle text-center">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-start">Event Name</th>
                                                <th>Type</th>
                                                <th>Role</th>
                                                <th>Organizer</th>
                                                <th>Year</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(optional($user->karyawan)->seminar_and_events && $user->karyawan->seminar_and_events->isNotEmpty())
                                                @foreach($user->karyawan->seminar_and_events as $eventData)
                                                    <tr>
                                                        <td class="text-start">{{ optional($eventData->materi)->nama_materi ?? 'Unknown Event' }}</td>
                                                        <td>{{ $eventData->event ?? '-' }}</td>
                                                        <td>Speaker</td>
                                                        <td>Inixindo</td>
                                                        <td>
                                                            {{ $eventData->tanggal_awal ? \Carbon\Carbon::parse($eventData->tanggal_awal)->format('Y') : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Belum ada data seminar/event.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 px-4">
                                <h4 class="mb-3"><strong class="text-decoration-underline">Teaching Experience</strong></h4>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-start">Course Name</th>
                                                <th class="text-start">Company Name</th>
                                                <th class="text-center">Year Period</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(optional($user->karyawan)->teaching_experiences && $user->karyawan->teaching_experiences->isNotEmpty())
                                                @foreach($user->karyawan->teaching_experiences as $teaching)
                                                    <tr>
                                                        <td class="text-start">{{ $teaching->course_name }}</td>
                                                        <td class="text-start">
                                                            @foreach($teaching->companies as $company)
                                                                {{ $company }}<br>
                                                            @endforeach
                                                        </td>
                                                        <td class="text-center">{{ $teaching->year_period }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Belum ada data teaching experience.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
