@extends('layout_HR.app')

@section('content_HR')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --secondary: #858796;
            --light: #f8f9fc;
            --dark: #5a5c69;
            --border: #e3e6f0;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .coming-soon-container {
            min-height: calc(100vh - 100px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .coming-soon-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem 2rem;
            max-width: 800px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .coming-soon-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--success), var(--info));
        }

        .illustration-wrapper {
            margin-bottom: 2rem;
            position: relative;
        }

        .illustration-wrapper img {
            max-width: 100%;
            height: auto;
            max-height: 350px;
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.1));
        }

        .coming-soon-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .coming-soon-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--success));
            border-radius: 2px;
        }

        .coming-soon-subtitle {
            font-size: 1.1rem;
            color: var(--secondary);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .feature-list {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .feature-item i {
            color: var(--success);
            font-size: 1.2rem;
        }

        .progress-container {
            max-width: 500px;
            margin: 0 auto 2rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--secondary);
        }

        .progress {
            height: 10px;
            border-radius: 10px;
            background: var(--light);
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary), var(--success));
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }

        .btn-back {
            padding: 0.75rem 2rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--info));
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(78, 115, 223, 0.4);
            color: white;
        }

        .pulse-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--success);
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }

        @media (max-width: 768px) {
            .coming-soon-card {
                padding: 2rem 1.5rem;
            }

            .coming-soon-title {
                font-size: 1.8rem;
            }

            .feature-list {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
        }
    </style>

    <div class="coming-soon-container">
        <div class="coming-soon-card">
            <div class="illustration-wrapper">
                <img src="https://42f2671d685f51e10fc6-b9fceez3lc7ez27lc32e6c6e8f8b9f2a6.ssl.cf1.rackcdn.com/illustrations/under_construction_rwj2.svg"
                    alt="Dashboard Coming Soon"
                    onerror="this.src='https://42f2671d685f51e10fc6-b9fceez3lc7ez27lc32e6c6e8f8b9f2a6.ssl.cf1.rackcdn.com/illustrations/waiting_qlne.svg'">
            </div>

            <h1 class="coming-soon-title">Dashboard Segera Hadir</h1>

            <div class="feature-list">
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Analitik Real-time</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-users"></i>
                    <span>Manajemen Karyawan</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Penghadiran Otomatis</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan Lengkap</span>
                </div>
            </div>

            <div class="progress-container">
                <div class="progress-label">
                    <span><span class="pulse-dot"></span>Progress Pengembangan</span>
                    <span class="fw-bold">60%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>
            </div>

            <a href="{{ url()->previous() }}" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
@endsection
