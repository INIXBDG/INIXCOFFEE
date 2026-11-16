@extends('layouts.app')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #1a4971;
        --secondary: #5587bd;
    }
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .survey-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        background: white;
        margin: 60px auto;
        max-width: 650px;
    }
</style>

<div class="d-flex justify-content-between align-items-center me-3 mt-3 ms-3">
    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Tiket
    </a>
    @if ($isItsm ?? false)
        <a href="{{ route('surveyKepuasan.indexTable') }}" class="btn btn-primary">
            <i class="fa-solid fa-square-poll-vertical me-1"></i> Lihat Rekap Hasil Survey (ITSM)
        </a>
    @endif
</div>

<div class="container py-4">
    <div class="survey-card text-center py-5 px-4">
        <div class="mb-3">
            <span class="d-inline-flex justify-content-center align-items-center bg-success text-white rounded-circle shadow-sm" style="width: 55px; height: 55px;">
                <i class="fas fa-check" style="font-size: 1.6rem;"></i>
            </span>
        </div>
        <h4 class="fw-bold mb-2">Terima kasih sudah berpartisipasi!</h4>
        <p class="text-muted mb-0">
            @if ($latestSurvey)
                Anda telah mengisi survey pada <strong>{{ \Carbon\Carbon::parse($latestSurvey->created_at)->locale('id')->translatedFormat('l, d F Y') }}</strong>.
            @else
                Semua survey kepuasan untuk tiket Anda telah selesai diisi.
            @endif
        </p>
    </div>
</div>
@endsection
