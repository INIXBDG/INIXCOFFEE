@extends('layouts.app')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    :root {
        --primary: #1a4971;
        --secondary: #5587bd;
        --success: #28a745;
        --light: #f8f9fa;
        --gray: #6c757d;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .survey-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        background: white;
        margin: 20px auto;
        max-width: 800px;
    }

    .survey-header {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        padding: 2rem;
        text-align: center;
        border-radius: 20px 20px 0 0;
    }

    .survey-header h4 {
        margin: 0;
        font-weight: 700;
        font-size: 1.8rem;
    }

    .star-rating {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 1rem 0;
    }

    .star-rating i {
        font-size: 2rem;
        color: #d3d3d3;
        cursor: pointer;
        transition: transform 0.25s ease, color 0.3s ease;
    }

    .star-rating i.active {
        color: #ffc107;
        transform: scale(1.2);
    }

    .btn-submit {
        background: var(--success);
        color: white;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        border-radius: 16px;
        width: 100%;
    }

    textarea {
        resize: none;
    }

    .choice-buttons {
        user-select: none;
    }

    .choice-option {
        border: 2px solid #d0d7e2;
        border-radius: 14px;
        padding: 1rem 2.5rem;
        background: #f8f9fa;
        color: #333;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .choice-option i {
        font-size: 1.3rem;
    }

    .choice-option:hover {
        background: #e9f2ff;
        border-color: var(--secondary);
        transform: translateY(-2px);
    }

    .choice-option.active {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border-color: transparent;
        box-shadow: 0 5px 15px rgba(26, 73, 113, 0.3);
    }
</style>

@php
    // Cek apakah ticket_id ini sudah pernah mengisi survey
    $alreadySurveyed = \App\Models\SurveyKepuasan::where('ticket_id', $ticket->ticket_id)->exists();
    
    // User bisa isi survey JIKA belum pernah mengisi untuk ticket_id tersebut
    $canSurvey = !$alreadySurveyed;
@endphp

@if (auth()->user()->karyawan->divisi === "IT Service Management")
<div class="text-end me-3 mt-3">
    <a href="{{ route('surveyKepuasan.indexTable') }}" class="btn btn-primary">Lihat Hasil Survey</a>
</div>
@endif

<div class="container py-4">
    @if ($canSurvey)
    {{-- FORM SURVEY --}}
    <div class="survey-card">
        <div class="survey-header">
            <h4>Survey Kepuasan Kinerja ITSM</h4>
            <p>Berikan penilaian jujur Anda untuk membantu kami meningkatkan layanan</p>
        </div>

        <div class="card-body p-4">
            <form id="surveyForm" action="{{ route('surveykepuasan.store') }}" method="post">
                @csrf
                <input type="hidden" name="ticket_id" value="{{ $ticket->ticket_id }}">
                <div class="mb-5">
                    <label class="form-label fw-semibold">
                        1. Bagaimana Anda menilai kecepatan respon tim ITSM dalam menangani insiden Anda?
                    </label>
                    <div class="star-rating" data-rating-target="q1">
                        <i class="fas fa-star" data-rating="1"></i>
                        <i class="fas fa-star" data-rating="2"></i>
                        <i class="fas fa-star" data-rating="3"></i>
                        <i class="fas fa-star" data-rating="4"></i>
                    </div>
                    <input type="hidden" name="q1" id="q1" value="0">
                </div>

                <div class="mb-5">
                    <label class="form-label fw-semibold">
                        2. Apakah insiden Anda teratasi dengan efektif?
                    </label>
                    <div class="choice-buttons d-flex justify-content-center gap-3 mt-2">
                        <div class="choice-option" data-value="Ya">
                            <i class="fas fa-check-circle"></i>
                            <span>Ya</span>
                        </div>
                        <div class="choice-option" data-value="Tidak">
                            <i class="fas fa-times-circle"></i>
                            <span>Tidak</span>
                        </div>
                    </div>
                    <input type="hidden" name="q2" id="q2" value="">
                </div>

                <div class="mb-5">
                    <label class="form-label fw-semibold">
                        3. Jika tidak, apa yang dapat dilakukan untuk memperbaiki proses penyelesaian insiden?
                    </label>
                    <textarea class="form-control" id="q3" name="q3" rows="3" placeholder="Tulis jawaban Anda di sini..."></textarea>
                </div>

                <div class="mb-5">
                    <label class="form-label fw-semibold">
                        4. Bagaimana Anda menilai kualitas layanan yang diberikan oleh tim ITSM dalam memenuhi request Anda?
                    </label>
                    <div class="star-rating" data-rating-target="q4">
                        <i class="fas fa-star" data-rating="1"></i>
                        <i class="fas fa-star" data-rating="2"></i>
                        <i class="fas fa-star" data-rating="3"></i>
                        <i class="fas fa-star" data-rating="4"></i>
                    </div>
                    <input type="hidden" name="q4" id="q4" value="0">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        5. Apakah ada sesuatu yang dapat dilakukan oleh tim ITSM untuk meningkatkan kualitas layanan?
                    </label>
                    <textarea class="form-control" id="q5" name="q5" rows="3" placeholder="Tulis saran Anda di sini..."></textarea>
                </div>

                <button type="submit" class="btn btn-success btn-submit">
                    <i class="fas fa-paper-plane"></i> Kirim Survey
                </button>
            </form>
        </div>
    </div>
    @else
    {{-- PESAN SUDAH MENGISI --}}
    <div class="survey-card text-center py-5">
        <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
        <h4 class="fw-bold">Terima kasih sudah berpartisipasi!</h4>
        <p class="text-muted mb-0">
            Anda telah mengisi survey pada
            <strong>{{ \Carbon\Carbon::parse($latestSurvey->created_at)->locale('id')->translatedFormat('l, d F Y') }}</strong>.
        </p>
    </div>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(function() {
        $(document).on('click', '.star-rating i', function() {
            const rating = $(this).data('rating');
            const $group = $(this).closest('.star-rating');
            const target = $group.data('rating-target');
            $group.find('i').removeClass('active');
            $group.find('i').each(function(index) {
                if (index < rating) $(this).addClass('active');
            });
            $('#' + target).val(rating);
        });

        $(document).on('click', '.choice-option', function() {
            $('.choice-option').removeClass('active');
            $(this).addClass('active');
            const value = $(this).data('value');
            $('#q2').val(value);
        });

        $('#surveyForm').on('submit', function(e) {
            const q1 = $('#q1').val();
            const q2 = $('#q2').val();
            const q4 = $('#q4').val();
            if (q1 == "0" || !q2 || q4 == "0") {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Harap Lengkapi!',
                    text: 'Mohon isi semua pertanyaan sebelum mengirim.',
                    confirmButtonColor: '#1a4971'
                });
            }
        });

        const flashData = {
            success: @json(session('success')),
            errors: @json($errors->all())
        };

        if (flashData.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: flashData.success,
                confirmButtonColor: '#28a745'
            });
        }

        if (flashData.errors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan!',
                html: flashData.errors.join('<br>'),
                confirmButtonColor: '#d33'
            });
        }
    });
</script>
@endsection