<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Instruktur - {{ optional($user->karyawan)->nama_lengkap ?? 'Unknown' }}</title>
    <style>
        @page {
            margin: 0px;
        }
        body {
            font-family: sans-serif;
            font-size: 13.5px; /* Ukuran font dasar ditingkatkan */
            font-weight: 500;  /* Ketebalan font dasar ditingkatkan */
            color: #000;
            margin: 40px 40px 80px 40px;
        }
        .bg-template {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
        }
        .container { width: 100%; margin: 0 auto; }

        .section-title {
            font-size: 16px; /* Ukuran judul sesi ditingkatkan proporsional */
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 15px;
            margin-top: 35px;
            page-break-after: avoid;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }

        tr {
            page-break-inside: avoid;
        }

        .table-borderless td, .table-borderless th { border: none; padding: 4px 0; vertical-align: top; }
        .table-bordered td, .table-bordered th { border: 1px solid #000; padding: 6px; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .w-25 { width: 25%; }
        .w-35 { width: 35%; }
        .w-75 { width: 75%; }
        .mb-2 { margin-bottom: 8px; }
        ul { margin-top: 0; padding-left: 20px; }

        .page-break {
            page-break-before: always;
        }
        .attachment-container {
            width: 100%;
            text-align: center;
            padding-top: 20px;
        }
        .attachment-image {
            width: 100%;
            max-height: 950px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <img src="{{ public_path('assets/img/backgrounds/kop.png') }}" class="bg-template" alt="Template KOP">

    <div class="container">

        <table class="table-borderless">
            <tr>
                <td class="w-25 text-center">
                    @if(optional($user->karyawan)->foto)
                        <img src="{{ public_path('storage/posts/'.$user->karyawan->foto) }}" alt="Foto Profile" style="width: 150px; height: 200px; object-fit: cover; border: 1px solid #ccc;">
                    @else
                        <div style="width: 150px; height: 200px; border: 1px solid #ccc; line-height: 200px; text-align: center;">No Image</div>
                    @endif
                </td>
                <td style="padding-left: 20px;">
                    <div class="section-title" style="margin-top: 0;">Personal Information</div>
                    <table class="table-borderless w-75">
                        <tr>
                            <td class="w-35">Name:</td>
                            <td>{{ optional($user->karyawan)->nama_lengkap ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Gender:</td>
                            <td>
                                {{ match(optional($user->karyawan)->gender) {
                                    'Laki-laki' => 'Male',
                                    'Perempuan' => 'Female',
                                    default => '-'
                                } }}
                            </td>
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
                    </table>
                </td>
            </tr>
        </table>

        <div class="section-title">Contact Information</div>
        <table class="table-borderless w-75">
            <tr>
                <td class="w-25">Address:</td>
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
                <td>{{ optional($user->karyawan)->email ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Educational Background</div>
        <table class="table-borderless w-75">
            <tr>
                <td class="w-25">Academic Qualification</td>
                <td>
                    @if(optional($user->karyawan)->educations && $user->karyawan->educations->isNotEmpty())
                        @foreach($user->karyawan->educations as $education)
                            <div class="mb-2">: {{ $education->name }}</div>
                        @endforeach
                    @else
                        : -
                    @endif
                </td>
            </tr>
        </table>

        <div class="section-title">Certification</div>
        <ul>
            @if(isset($user->sertifikasis) && $user->sertifikasis->isNotEmpty())
                @foreach($user->sertifikasis as $sertifikat)
                    @php
                        $isRetired = !empty($sertifikat->tanggal_berlaku_sampai) && \Carbon\Carbon::parse($sertifikat->tanggal_berlaku_sampai)->isPast();
                    @endphp
                    <li class="mb-2">
                        <strong>{{ $sertifikat->nama_sertifikat }}</strong> {{ $isRetired ? '(retired)' : '' }}
                    </li>
                @endforeach
            @else
                <li>-</li>
            @endif
        </ul>

        <div class="section-title">Specialization Area</div>
        <ul>
            @if(optional($user->karyawan)->specializations && $user->karyawan->specializations->isNotEmpty())
                @foreach($user->karyawan->specializations as $sp)
                    <li class="mb-2">
                        <strong>{{ $sp->specialization }}</strong>
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

        <div class="section-title">Seminar and Event Experience</div>
        <table class="table-bordered text-center">
            <thead>
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
                        <td colspan="5" class="text-center">Belum ada data seminar/event.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="section-title">Teaching Experience</div>
        <table class="table-bordered">
            <thead>
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
                        <td colspan="3" class="text-center">Belum ada data teaching experience.</td>
                    </tr>
                @endif
            </tbody>
        </table>

    </div>

    @if(isset($user->sertifikasis) && $user->sertifikasis->isNotEmpty())
        @foreach($user->sertifikasis as $sertifikat)
            @if($sertifikat->bukti_sertifikasi)
                <div class="page-break attachment-container">
                    <img src="{{ public_path('storage/' . $sertifikat->bukti_sertifikasi) }}" class="attachment-image" alt="Bukti Sertifikasi">
                </div>
            @endif
        @endforeach
    @endif
</body>
</html>
