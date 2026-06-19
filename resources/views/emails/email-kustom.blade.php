@extends('emails.layouts.master')

@section('content')
    <p style="margin: 0 0 20px; color: #1f2937; font-size: 16px;">
        Halo <strong>{{ $pelamar->nama_lengkap }}</strong>,
    </p>

    <div style="color: #1f2937; font-size: 15px; line-height: 1.7; white-space: pre-line;">
        {!! nl2br(e($isiEmail)) !!}
    </div>

    <p style="margin: 30px 0 0; color: #1f2937; font-size: 14px;">
        Salam,<br>
        <strong>Tim Inixindo Bandung</strong>
    </p>
@endsection
