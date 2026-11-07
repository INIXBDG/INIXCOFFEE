@extends('layouts_office.app')

@section('css')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        .analysis-wrap {
            --analysis-ink: #0f172a;
            --analysis-muted: #64748b;
            --analysis-line: #e2e8f0;
            --analysis-soft: #f8fafc;
            --analysis-fin: #0284c7;
            --analysis-int: #d97706;
            --analysis-learn: #e11d48;
            --analysis-ok: #059669;
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        /* ================= KEYFRAMES ================= */
        @keyframes aFadeUp {
            from {
                opacity: 0;
                transform: translateY(26px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes aFadeLeft {
            from {
                opacity: 0;
                transform: translateX(-30px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes aPop {
            0% {
                opacity: 0;
                transform: scale(.6)
            }

            70% {
                transform: scale(1.06)
            }

            100% {
                opacity: 1;
                transform: scale(1)
            }
        }

        @keyframes aFloat {

            0%,
            100% {
                transform: translateY(0) rotate(-2deg)
            }

            50% {
                transform: translateY(-12px) rotate(3deg)
            }
        }

        @keyframes aGlow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(14, 165, 233, .0), 0 10px 30px rgba(15, 23, 42, .06)
            }

            50% {
                box-shadow: 0 0 0 8px rgba(14, 165, 233, .10), 0 14px 36px rgba(14, 165, 233, .18)
            }
        }

        @keyframes aRoadIn {
            from {
                opacity: 0;
                transform: translate(60px, -60px) scale(.85)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes aRoadSway {

            0%,
            100% {
                transform: scale(1) rotate(0)
            }

            50% {
                transform: scale(1.04) rotate(-1.5deg)
            }
        }

        @keyframes aDot {

            0%,
            100% {
                transform: scale(1);
                opacity: 1
            }

            50% {
                transform: scale(1.8);
                opacity: .35
            }
        }

        @keyframes aGrow {
            from {
                width: 0
            }

            to {
                width: var(--w)
            }
        }

        @keyframes aRowIn {
            from {
                opacity: 0;
                transform: translateX(-18px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes aRowOut {
            to {
                opacity: 0;
                transform: translateX(40px);
                height: 0
            }
        }

        @keyframes aRipple {
            to {
                transform: scale(4);
                opacity: 0
            }
        }

        @keyframes aSpin {
            to {
                transform: rotate(360deg)
            }
        }

        @keyframes aSweep {
            from {
                background-position: 200% 0
            }

            to {
                background-position: -200% 0
            }
        }

        @keyframes aTwinkle {

            0%,
            100% {
                opacity: .2;
                transform: scale(.6) rotate(0)
            }

            50% {
                opacity: 1;
                transform: scale(1.2) rotate(90deg)
            }
        }

        @keyframes aShake {

            0%,
            100% {
                transform: none
            }

            25% {
                transform: translateX(-4px)
            }

            75% {
                transform: translateX(4px)
            }
        }

        /* ================= HERO ================= */
        .analysis-wrap .hero {
            position: relative;
            overflow: hidden;
            background: #fff;
            border-radius: 26px;
            padding: 2.1rem 2.6rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
            border: 1px solid #eef2f7;
            animation: aFadeUp .7s cubic-bezier(.2, .8, .2, 1) both;
        }

        .analysis-wrap .hero-road {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            height: 100%;
            width: 420px;
            max-width: 60%;
            pointer-events: none;
            animation: aRoadSlide 1.1s cubic-bezier(.2, .8, .2, 1) both .15s;
        }

        .analysis-wrap .hero-road .road-dash {
            animation: aDash 1.2s linear infinite;
        }

        @keyframes aRoadSlide {
            from {
                opacity: 0;
                transform: translateX(90px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes aDash {
            to {
                stroke-dashoffset: -22
            }
        }

        .analysis-wrap .hero-inner {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .analysis-wrap .hero-coin-wrap {
            position: relative;
            width: 150px;
            flex: 0 0 150px;
            animation: aPop .9s cubic-bezier(.2, .8, .2, 1) both .2s;
        }

        .analysis-wrap .hero-coin {
            width: 100%;
            display: block;
            animation: aFloat 5s ease-in-out infinite 1s;
            filter: drop-shadow(0 14px 14px rgba(245, 158, 11, .28));
        }

        .analysis-wrap .hero-coin-wrap .spark {
            position: absolute;
            width: 12px;
            height: 12px;
            background: #fbbf24;
            clip-path: polygon(50% 0, 62% 38%, 100% 50%, 62% 62%, 50% 100%, 38% 62%, 0 50%, 38% 38%);
            animation: aTwinkle 2.4s ease-in-out infinite;
        }

        .analysis-wrap .hero-coin-wrap .spark.s1 {
            top: 6%;
            left: 4%;
        }

        .analysis-wrap .hero-coin-wrap .spark.s2 {
            top: 18%;
            right: 0;
            animation-delay: .8s;
        }

        .analysis-wrap .hero-coin-wrap .spark.s3 {
            bottom: 8%;
            left: 12%;
            animation-delay: 1.5s;
        }

        .analysis-wrap .hero-text {
            flex: 1 1 360px;
            min-width: 280px;
            animation: aFadeLeft .8s cubic-bezier(.2, .8, .2, 1) both .3s;
        }

        .analysis-wrap .brand {
            display: flex;
            align-items: center;
            gap: .7rem;
            flex-wrap: wrap;
            margin-bottom: .35rem;
        }

        .analysis-wrap .brand .logo {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1;
        }

        .analysis-wrap .brand .logo .l1 {
            color: #1d3f8f;
        }

        .analysis-wrap .brand .logo .l2 {
            color: #e11d48;
        }

        .analysis-wrap .brand .brand-logo {
            height: 32px;
            width: auto;
            display: block;
        }

        .analysis-wrap .brand .sep {
            width: 1px;
            height: 22px;
            background: #cbd5e1;
        }

        .analysis-wrap .brand .tagline {
            color: #e11d48;
            font-weight: 700;
            font-size: .82rem;
        }

        .analysis-wrap .hero-title {
            margin: 0;
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 800;
            letter-spacing: -.045em;
            line-height: 1.05;
            background: linear-gradient(100deg, #0f172a 35%, #0284c7 50%, #0f172a 65%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: aSweep 3.2s ease-in-out 1 .9s both;
        }

        .analysis-wrap .hero-sub {
            color: #475569;
            margin: .6rem 0 0;
            font-weight: 500;
        }

        .analysis-wrap .score-box {
            border: 1px solid #dbe4ee;
            border-radius: 16px;
            padding: .95rem 1.4rem;
            background: rgba(248, 250, 252, .8);
            backdrop-filter: blur(6px);
            text-align: center;
            min-width: 230px;
            margin-left: auto;
            animation: aPop .9s cubic-bezier(.2, .8, .2, 1) both .5s, aGlow 3.6s ease-in-out infinite 1.6s;
            transition: transform .3s ease;
        }

        .analysis-wrap .score-box:hover {
            transform: translateY(-4px) scale(1.03);
        }

        .analysis-wrap .score-box .big {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--analysis-ink);
            line-height: 1.1;
            letter-spacing: -.03em;
        }

        .analysis-wrap .status-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .25rem .75rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .04em;
            background: #d1fae5;
            color: #047857;
        }

        .analysis-wrap .status-pill.off {
            background: #e2e8f0;
            color: #475569;
        }

        .analysis-wrap .status-pill::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
            animation: aDot 1.4s ease-in-out infinite;
        }

        @media (max-width: 767px) {
            .analysis-wrap .hero {
                padding: 1.4rem;
            }

            .analysis-wrap .hero-coin-wrap {
                width: 100px;
                flex-basis: 100px;
            }

            .analysis-wrap .score-box {
                margin-left: 0;
                width: 100%;
            }

            .analysis-wrap .hero-road {
                opacity: .5;
                width: 260px;
            }
        }

        /* ================= UMUM ================= */
        .analysis-wrap .card {
            border-radius: 16px;
            border: 1px solid #eef2f7;
            box-shadow: 0 6px 20px rgba(15, 23, 42, .04);
            transition: box-shadow .3s ease, transform .3s ease;
        }

        .analysis-wrap .card:hover {
            box-shadow: 0 14px 34px rgba(15, 23, 42, .09);
        }

        .analysis-wrap .analysis-section {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin: 2rem 0 1rem;
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--analysis-ink);
            flex-wrap: wrap;
        }

        .analysis-wrap .analysis-section::before {
            content: "";
            width: 5px;
            height: 1.5rem;
            border-radius: 3px;
            background: var(--analysis-fin);
            transform-origin: top;
            animation: aGrowY .7s ease both;
        }

        @keyframes aGrowY {
            from {
                transform: scaleY(0)
            }

            to {
                transform: scaleY(1)
            }
        }

        .analysis-wrap .analysis-section.int::before {
            background: var(--analysis-int);
        }

        .analysis-wrap .analysis-section.learn::before {
            background: var(--analysis-learn);
        }

        .analysis-wrap .analysis-section .analysis-tools {
            margin-left: auto;
            display: flex;
            gap: .4rem;
            flex-wrap: wrap;
        }

        .analysis-wrap .pill {
            display: inline-block;
            padding: .2rem .65rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            transition: transform .25s ease;
        }

        .analysis-wrap .pill:hover {
            transform: scale(1.1);
        }

        .analysis-wrap .pill.success {
            background: #d1fae5;
            color: #047857;
        }

        .analysis-wrap .pill.warning {
            background: #fef3c7;
            color: #b45309;
        }

        .analysis-wrap .pill.danger {
            background: #ffe4e6;
            color: #be123c;
        }

        .analysis-wrap .pill.info {
            background: #e0f2fe;
            color: #0369a1;
        }

        .analysis-wrap .pill.muted {
            background: #e2e8f0;
            color: #475569;
        }

        .analysis-wrap table.analysis-table {
            width: 100%;
            font-size: .86rem;
            margin: 0;
        }

        .analysis-wrap table.analysis-table th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--analysis-muted);
            background: var(--analysis-soft);
            padding: .7rem .8rem;
            white-space: nowrap;
        }

        .analysis-wrap table.analysis-table td {
            padding: .6rem .8rem;
            vertical-align: middle;
            border-top: 1px solid var(--analysis-line);
        }

        .analysis-wrap table.analysis-table tbody tr {
            transition: background .2s ease, transform .2s ease;
        }

        .analysis-wrap table.analysis-table tbody tr:hover {
            background: #f0f9ff;
        }

        .analysis-wrap table.analysis-table tfoot td {
            background: var(--analysis-soft);
            font-weight: 700;
            border-top: 2px solid var(--analysis-line);
        }

        .analysis-wrap table.analysis-table.dark thead th {
            background: #0f172a;
            color: #fff;
        }

        .analysis-wrap table.analysis-table.dark tfoot td.total {
            background: #d1fae5;
            color: #047857;
            font-size: 1rem;
        }

        .analysis-wrap .row-anim {
            animation: aRowIn .5s cubic-bezier(.2, .8, .2, 1) both;
        }

        .analysis-wrap .row-out {
            animation: aRowOut .3s ease forwards;
            overflow: hidden;
        }

        .analysis-wrap .num {
            text-align: center;
        }

        .analysis-wrap .good {
            color: var(--analysis-ok);
            font-weight: 700;
        }

        .analysis-wrap .bad {
            color: var(--analysis-learn);
            font-weight: 700;
        }

        .analysis-wrap tr.out-of-period td {
            opacity: .45;
        }

        .analysis-wrap .src {
            display: inline-block;
            font-size: .62rem;
            font-weight: 700;
            border-radius: 4px;
            padding: 0 .3rem;
            margin-left: .25rem;
            vertical-align: middle;
        }

        .analysis-wrap .src.manual {
            background: #fde68a;
            color: #92400e;
        }

        .analysis-wrap .chart-box {
            position: relative;
            height: 260px;
        }

        .analysis-wrap .scroll-y {
            max-height: 420px;
            overflow-y: auto;
        }

        .analysis-wrap .note-box {
            border-radius: 10px;
            padding: .7rem .9rem;
            font-size: .82rem;
        }

        .analysis-wrap .note-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #075985;
        }

        .analysis-wrap .note-ok {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        /* ================= INPUT INLINE ================= */
        .analysis-wrap .cell {
            width: 100%;
            border: 1px solid var(--analysis-line);
            border-radius: 8px;
            padding: .28rem .5rem;
            font-size: .82rem;
            background: #fff;
            transition: border-color .2s, box-shadow .25s, transform .2s, background .3s;
        }

        .analysis-wrap .cell:hover {
            border-color: #94a3b8;
        }

        .analysis-wrap .cell:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, .15);
            transform: scale(1.02);
        }

        .analysis-wrap .cell.dirty {
            background: #fffbeb;
            border-color: #f59e0b;
        }

        .analysis-wrap .cell.num-in {
            text-align: center;
            min-width: 80px;
        }

        .analysis-wrap tr.row-add td {
            background: #f0f9ff;
            border-top: 2px dashed #bae6fd;
        }

        .analysis-wrap .row-del {
            cursor: pointer;
            color: #be123c;
            border: 0;
            background: none;
            font-weight: 800;
            transition: transform .2s ease, color .2s;
        }

        .analysis-wrap .row-del:hover {
            transform: scale(1.4) rotate(90deg);
            color: #9f1239;
        }

        /* ================= TABEL PERSENTASE PERUBAHAN ================= */
        .analysis-wrap table.chg th {
            background: #e5e7eb;
            color: #374151;
            text-align: center;
        }

        .analysis-wrap table.chg .out {
            transition: color .3s, transform .3s;
        }

        .analysis-wrap table.chg .out.bump {
            transform: scale(1.25);
        }

        /* ================= REKAP TRIWULAN ================= */
        .analysis-wrap .recap {
            background: #2364ae;
            color: #e2e8f0;
            border-radius: 20px;
            padding: 1.6rem;
            position: relative;
            overflow: hidden;
        }

        .analysis-wrap .recap::after {
            content: "";
            position: absolute;
            width: 380px;
            height: 380px;
            right: -140px;
            top: -160px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .16), transparent 65%);
            animation: aFloat 9s ease-in-out infinite;
            pointer-events: none;
        }

        .analysis-wrap .recap h4 {
            color: #fff;
            font-weight: 800;
            margin: 0;
        }

        .analysis-wrap .recap .sub {
            color: #bfdbfe;
            font-size: .85rem;
        }

        .analysis-wrap .q-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 1rem;
            height: 100%;
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s;
        }

        .analysis-wrap .q-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 30px rgba(0, 0, 0, .3);
            border-color: #38bdf8;
        }

        .analysis-wrap .q-card.active {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 1px #0ea5e9;
            animation: aGlow 3s ease-in-out infinite;
        }

        .analysis-wrap .q-card.proj {
            border-style: dashed;
            background: transparent;
            color: #94a3b8;
        }

        .analysis-wrap .q-card .label {
            color: #38bdf8;
            font-weight: 700;
            font-size: .85rem;
        }

        .analysis-wrap .q-card.proj .label {
            color: #94a3b8;
        }

        .analysis-wrap .q-card .val {
            font-size: 1.8rem;
            font-weight: 800;
            color: #fff;
            margin: .4rem 0;
        }

        .analysis-wrap .q-card.active .val {
            color: #34d399;
        }

        .analysis-wrap .q-card.proj .val {
            color: #64748b;
        }

        .analysis-wrap .q-bar {
            height: 6px;
            border-radius: 3px;
            background: #334155;
            overflow: hidden;
        }

        .analysis-wrap .q-bar>span {
            display: block;
            height: 100%;
            width: var(--w);
            background: #38bdf8;
            animation: aGrow 1.4s cubic-bezier(.2, .8, .2, 1) both .4s;
        }

        .analysis-wrap .q-card.active .q-bar>span {
            background: #34d399;
        }

        .analysis-wrap .recap .panel-light {
            background: #fff;
            color: #0f172a;
            border-radius: 14px;
            padding: 1rem;
        }

        .analysis-wrap .trend-up {
            color: var(--analysis-ok);
            font-weight: 700;
        }

        .analysis-wrap .trend-down {
            color: var(--analysis-learn);
            font-weight: 700;
        }

        /* ================= REVEAL SAAT SCROLL ================= */
        .analysis-wrap .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity .7s ease, transform .7s cubic-bezier(.2, .8, .2, 1);
        }

        .analysis-wrap .reveal.in {
            opacity: 1;
            transform: none;
        }

        /* ================= TOMBOL ================= */
        .analysis-wrap .btn,
        .modal .btn {
            position: relative;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .25s ease, background .2s;
        }

        .analysis-wrap .btn:hover,
        .modal .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, .15);
        }

        .analysis-wrap .btn:active,
        .modal .btn:active {
            transform: translateY(0) scale(.95);
        }

        .btn .ripple {
            position: absolute;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            background: rgba(255, 255, 255, .55);
            transform: scale(0);
            animation: aRipple .6s ease-out forwards;
            pointer-events: none;
        }

        .btn.is-loading {
            pointer-events: none;
            opacity: .85;
        }

        .btn.is-loading::after {
            content: "";
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-left: .5rem;
            vertical-align: -2px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: aSpin .7s linear infinite;
        }

        .analysis-wrap .ind-card {
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .analysis-wrap .ind-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 34px rgba(15, 23, 42, .12);
        }

        .analysis-wrap .ind-card .formula {
            background: var(--analysis-soft);
            border: 1px solid var(--analysis-line);
            border-radius: 8px;
            padding: .55rem .7rem;
            font-family: ui-monospace, Menlo, Consolas, monospace;
            font-size: .78rem;
            color: #334155;
        }

        .analysis-wrap .ind-card .real {
            font-size: 1.6rem;
            font-weight: 800;
        }

        .analysis-wrap .alert {
            animation: aShake .5s ease both, aFadeUp .5s ease both;
        }

        /* ================= MODAL ================= */
        .modal.fade .modal-dialog {
            transform: scale(.82) translateY(40px);
            opacity: 0;
            transition: transform .45s cubic-bezier(.2, .9, .3, 1.2), opacity .3s ease;
        }

        .modal.show .modal-dialog {
            transform: none;
            opacity: 1;
        }

        .modal-backdrop.show {
            backdrop-filter: blur(3px);
        }

        @media (prefers-reduced-motion: reduce) {

            .analysis-wrap *,
            .modal * {
                animation: none !important;
                transition: none !important;
            }

            .analysis-wrap .reveal {
                opacity: 1;
                transform: none;
            }
        }
    </style>
@endsection

@section('office_contents')
    @php
        $f1 = fn($v) => number_format((float) $v, 1, '.', ',');
        $f2 = fn($v) => number_format((float) $v, 2, '.', ',');
        $canEdit = true;
        $ind = $analysis['indicators'];

        $imgCoin = asset('assets/img/coin.png');

        $keepInputs = collect(['mode' => $mode, 'bulan' => $bulan, 'q' => $q])
            ->map(fn($v, $k) => '<input type="hidden" name="' . $k . '" value="' . e($v) . '">')
            ->implode('');

        $curQ = (int) ceil(now()->month / 3);
        $stateActive = $tahun == now()->year && $endQ == $curQ;
        $stateText = $stateActive
            ? 'ACTIVE'
            : ($tahun > now()->year || ($tahun == now()->year && $endQ > $curQ)
                ? 'PROYEKSI'
                : 'SELESAI');
        $periodShort = $mode === 'q' ? 'TR ' . $q : strtoupper($periodLabel);
    @endphp

    <div class="analysis-wrap">

        {{-- ================= HERO ================= --}}
        <div class="hero mb-3">
            <svg class="hero-road" viewBox="0 0 420 220" preserveAspectRatio="xMaxYMid slice" xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true">
                {{-- badan jalan: cabang atas + cabang bawah menyatu ke tepi kanan --}}
                <g fill="none" stroke="#231f20" stroke-width="36" stroke-linecap="butt" stroke-linejoin="round">
                    <path d="M150 -30 C172 55 235 108 320 110 L440 110" />
                    <path d="M55 255 C105 175 195 118 320 110 L440 110" />
                </g>
                {{-- marka putih putus-putus (mengalir) --}}
                <g fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-dasharray="10 12"
                    class="road-dash">
                    <path d="M150 -30 C172 55 235 108 320 110 L440 110" />
                    <path d="M55 255 C105 175 195 118 320 110" />
                </g>
            </svg>
            <div class="hero-inner">
                <div class="hero-coin-wrap">
                    <span class="spark s1"></span><span class="spark s2"></span><span class="spark s3"></span>
                    <img class="hero-coin" src="{{ $imgCoin }}" alt="Finance">
                </div>
                <div class="hero-text">
                    <div class="brand">
                        <img class="brand-logo" src="{{ asset('assets/img/inixs.png') }}" alt="Inixindo">
                        <span class="sep"></span>
                        <span class="tagline">"Continuous Learning, Keep Up To Date"</span>
                    </div>
                    <h1 class="hero-title">KPI Finance {{ $tahun }}</h1>
                    <p class="hero-sub">Monitoring Kinerja &amp; Efektivitas Pengelolaan Keuangan</p>
                </div>
                <div class="score-box">
                    <span class="status-pill {{ $stateActive ? '' : 'off' }}">STATUS {{ $periodShort }}:
                        {{ $stateText }}</span>
                    <div class="text-muted small mt-2">Skor Kinerja {{ $mode === 'q' ? 'Kumulatif' : '' }}</div>
                    <div class="big"><span data-count="{{ $analysis['total'] }}"
                            data-dec="2">{{ $f2($analysis['total']) }}</span>
                        <small class="{{ $analysis['persen'] >= 100 ? 'good' : 'text-warning' }} fs-6">(<span
                                data-count="{{ $analysis['persen'] }}" data-dec="1"
                                data-suffix="%">{{ $f1($analysis['persen']) }}%</span>)</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= FILTER ================= --}}
        <div class="card mb-3 reveal">
            <div class="card-body py-3">
                <form method="GET" id="filterForm" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">Periode</label>
                        <select name="mode" id="fMode" class="form-select form-select-sm">
                            <option value="q" @selected($mode === 'q')>Triwulan (Overview)</option>
                            <option value="bulan" @selected($mode === 'bulan')>Bulan</option>
                            <option value="tahun" @selected($mode === 'tahun')>Tahun</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2" id="fQWrap">
                        <label class="form-label small mb-1">Triwulan</label>
                        <select name="q" class="form-select form-select-sm">
                            @for ($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" @selected($q === $i)>TR {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-6 col-md-2" id="fBulanWrap">
                        <label class="form-label small mb-1">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm">
                            @foreach ($listBulan as $n => $nama)
                                <option value="{{ $n }}" @selected($bulan === $n)>{{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">Tahun</label>
                        <select name="tahun" class="form-select form-select-sm">
                            @foreach ($listTahun as $th)
                                <option value="{{ $th }}" @selected($th == $tahun)>{{ $th }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button class="btn btn-sm btn-primary">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif

        {{-- ================= SKEMA PENILAIAN ================= --}}
        <div class="analysis-section">Skema Penilaian Finance</div>
        <div class="row g-3">
            @foreach ($ind as $i)
                @php
                    $tCls = [1 => 'info', 2 => 'warning', 3 => 'success'][$i['no']] ?? 'info';
                    $icon =
                        $i['status'] === 'Melampaui'
                            ? '★'
                            : ($i['status_class'] === 'success'
                                ? '✓'
                                : ($i['status_class'] === 'warning'
                                    ? '◷'
                                    : '!'));
                @endphp
                <div class="col-12 col-lg-4">
                    <div class="card ind-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="text-muted small fw-semibold">INDIKATOR {{ $i['no'] }}</span>
                                <span class="pill {{ $tCls }}">Target:
                                    {{ str_replace(' ide', ' Ide/Tahun', $i['target_label']) }}</span>
                            </div>
                            <h6 class="fw-bold mb-3">{{ $i['analysis'] }}</h6>
                            <div class="formula mb-3"><strong>Formula:</strong><br>{{ $i['formula'] }}</div>
                            <div class="text-muted small">Realisasi {{ $periodLabel }}</div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="real {{ $i['ach'] >= 100 ? 'good' : '' }}">
                                    {{ $i['unit'] === '%' ? $f1($i['realisasi']) . '%' : (int) $i['realisasi'] . ' Ide' }}
                                </div>
                                <span class="pill {{ $i['status_class'] }}">{{ $icon }}
                                    {{ $i['status'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ================= KPI FINANCE OVERVIEW ================= --}}
        <div class="analysis-section">
            KPI Finance Overview
            @if ($canEdit)
                <span class="analysis-tools">
                    <a class="btn btn-sm btn-outline-secondary"
                        href="{{ route('analysis-finance.template', ['type' => 'overview', 'tahun' => $tahun, 'mode' => $mode, 'bulan' => $bulan, 'q' => $q]) }}">Template</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-import="overview"
                        data-label="Realisasi &amp; Pencapaian">Import</button>
                </span>
            @endif
        </div>
        <form method="POST" action="{{ route('analysis-finance.overview') }}" class="card">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="periode" value="{{ $periodKey }}">
            {!! $keepInputs !!}
            <div class="table-responsive">
                <table class="analysis-table dark table">
                    <thead>
                        <tr>
                            <th>Perspektif</th>
                            <th>KPI</th>
                            <th style="min-width:240px">Deskripsi</th>
                            <th class="num">Target</th>
                            <th class="num" style="min-width:150px">Realisasi</th>
                            <th class="num" style="min-width:150px">Pencapaian %</th>
                            <th class="num">Bobot %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ind as $i)
                            <tr>
                                <td class="fw-bold"
                                    style="color:var(--analysis-{{ $i['class'] === 'financial' ? 'fin' : ($i['class'] === 'internal' ? 'int' : 'learn') }})">
                                    {{ $i['perspektif'] }}</td>
                                <td class="fw-semibold">{{ $i['analysis'] }}</td>
                                <td class="text-muted">{{ $i['deskripsi'] }}</td>
                                <td class="num">{{ $i['target_label'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" min="0" step="0.1" class="cell num-in"
                                            name="rows[{{ $i['no'] }}][realisasi]"
                                            value="{{ $i['in_realisasi'] !== null ? $i['in_realisasi'] + 0 : '' }}"
                                            placeholder="{{ $i['unit'] === '%' ? $f1($i['auto_realisasi']) : (int) $i['auto_realisasi'] }}">
                                        <span class="small text-muted">{{ $i['unit'] === '%' ? '%' : 'ide' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" min="0" step="0.1" class="cell num-in"
                                            name="rows[{{ $i['no'] }}][pencapaian]"
                                            value="{{ $i['in_ach'] !== null ? $i['in_ach'] + 0 : '' }}"
                                            placeholder="{{ $f1($i['auto_ach']) }}">
                                        <span class="small text-muted">%</span>
                                    </div>
                                </td>
                                <td class="num">{{ rtrim(rtrim(number_format($i['bobot'], 2), '0'), '.') }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end">TOTAL BOBOT KPI KEUANGAN — {{ $periodLabel }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format($analysis['bobot_total'], 2), '0'), '.') }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <span class="small text-muted">Kosongkan Realisasi / Pencapaian untuk memakai hitungan otomatis (angka
                    abu-abu). Isian berlaku untuk periode {{ $periodLabel }}.</span>
                <button class="btn btn-sm btn-primary">Simpan</button>
            </div>
        </form>

        {{-- ================= C. FINANCIAL ================= --}}
        <div class="analysis-section">Financial Perspective</div>
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">Ringkasan Tagihan Keuangan <span class="text-muted fw-normal">·
                                {{ $periodLabel }}</span></h6>
                        <div class="table-responsive">
                            <table class="analysis-table table">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th class="num">Total</th>
                                        <th class="num">On Time</th>
                                        <th class="num">Telat</th>
                                        <th class="num">%</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($finMonthly as $m)
                                        @php $pc = $m['total'] > 0 ? $m['ontime'] / $m['total'] * 100 : null; @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $m['nama'] }}</td>
                                            <td class="num">{{ $m['total'] }}</td>
                                            <td class="num good">{{ $m['ontime'] }}</td>
                                            <td class="num bad">{{ $m['telat'] }}</td>
                                            <td class="num">{{ $pc === null ? '-' : $f1($pc) . '%' }}</td>
                                            <td class="text-end">Rp {{ number_format($m['nominal'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>Total</td>
                                        <td class="num">{{ $analysis['fin']['total'] }}</td>
                                        <td class="num good">{{ $analysis['fin']['ontime'] }}</td>
                                        <td class="num bad">{{ $analysis['fin']['telat'] }}</td>
                                        <td class="num">{{ $f1($analysis['fin']['persen']) }}%</td>
                                        <td class="text-end">Rp
                                            {{ number_format(array_sum(array_column($finMonthly, 'nominal')), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="note-box note-info mt-3">
                            @if ($analysis['fin']['persen'] >= $setting->target_tagihan)
                                Tingkat ketepatan pembayaran berada di atas ambang batas
                                (≥{{ rtrim(rtrim(number_format($setting->target_tagihan, 2), '0'), '.') }}%).
                            @else
                                Tingkat ketepatan pembayaran masih di bawah ambang batas
                                (≥{{ rtrim(rtrim(number_format($setting->target_tagihan, 2), '0'), '.') }}%).
                            @endif
                            Data dihitung otomatis dari Tagihan Perusahaan; tagihan yang belum jatuh tempo tidak dihitung.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">Visualisasi Status Tagihan</h6>
                        <div class="chart-box"><canvas id="chFinance"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= D. INTERNAL PROCESS ================= --}}
        <div class="analysis-section int">
            Internal Process Perspective
            @if ($canEdit)
                <span class="analysis-tools">
                    <a class="btn btn-sm btn-outline-secondary"
                        href="{{ route('analysis-finance.template', ['type' => 'internal', 'tahun' => $tahun]) }}">Template</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-import="internal"
                        data-label="Internal Process">Import</button>
                </span>
            @endif
        </div>
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <form method="POST" action="{{ route('analysis-finance.internal') }}" class="card h-100">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    {!! $keepInputs !!}
                    <div class="card-body pb-2">
                        <h6 class="fw-bold">Rekapitulasi Bulanan Operasional (12 Bulan)</h6>
                        <div class="small text-muted mb-2">
                            Edit langsung di tabel. Kosongkan sel untuk memakai data tagihan (angka abu-abu)
                            &nbsp;·&nbsp; baris redup di luar periode {{ $periodLabel }}
                        </div>
                    </div>
                    <div class="scroll-y">
                        <table class="analysis-table table">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th class="num">Pengajuan Barang</th>
                                    <th class="num">Bukti Kas</th>
                                    <th class="num">% Kesesuaian</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sumP = $sumB = 0; @endphp
                                @foreach ($internal as $r)
                                    @php
                                        $in = $r['bulan'] >= $sm && $r['bulan'] <= $em;
                                        if ($in) {
                                            $sumP += $r['pengajuan'];
                                            $sumB += $r['bukti'];
                                        }
                                    @endphp
                                    <tr class="{{ $in ? '' : 'out-of-period' }}">
                                        <td class="fw-semibold">{{ $r['nama'] }}</td>
                                        <td><input type="number" min="0" class="cell num-in"
                                                name="rows[{{ $r['bulan'] }}][pengajuan_barang]"
                                                value="{{ $r['in_p'] }}" placeholder="{{ $r['shadow_p'] }}"></td>
                                        <td><input type="number" min="0" class="cell num-in"
                                                name="rows[{{ $r['bulan'] }}][bukti_kas]" value="{{ $r['in_b'] }}"
                                                placeholder="{{ $r['shadow_b'] }}"></td>
                                        <td class="num {{ $r['persen'] !== null && $r['persen'] >= 98 ? 'good' : '' }}">
                                            {{ $r['persen'] === null ? '-' : $f1($r['persen']) . '%' }}</td>
                                        <td><input type="text" class="cell"
                                                name="rows[{{ $r['bulan'] }}][catatan]" value="{{ $r['catatan'] }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Total {{ $periodLabel }}</td>
                                    <td class="num">{{ $sumP }}</td>
                                    <td class="num">{{ $sumB }}</td>
                                    <td class="num good">{{ $sumP > 0 ? $f1(($sumB / $sumP) * 100) . '%' : '-' }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="card-footer bg-transparent text-end">
                        <button class="btn btn-sm btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">Tren Pengajuan vs Bukti Pengeluaran Kas</h6>
                        <div class="chart-box" style="height:340px"><canvas id="chInternal"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= E. LEARNING & GROWTH ================= --}}
        <div class="analysis-section learn">
            Learning &amp; Growth Perspective
            @if ($canEdit)
                <span class="analysis-tools">
                    <a class="btn btn-sm btn-outline-secondary"
                        href="{{ route('analysis-finance.template', ['type' => 'initiatives', 'tahun' => $tahun]) }}">Template</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-import="initiatives"
                        data-label="Inisiatif Efisiensi">Import</button>
                </span>
            @endif
        </div>

        <form id="iniForm" method="POST" action="{{ route('analysis-finance.initiatives') }}">@csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">{!! $keepInputs !!}
        </form>
        <form id="iniAddForm" method="POST" action="{{ route('analysis-finance.initiatives.add') }}">@csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">{!! $keepInputs !!}
        </form>
        <form id="iniDelForm" method="POST">@csrf @method('DELETE')
            <input type="hidden" name="tahun" value="{{ $tahun }}">{!! $keepInputs !!}
        </form>

        <div class="card mb-3">
            <div class="card-body pb-2">
                <h6 class="fw-bold mb-0">Tabel 1: Daftar Inisiatif Efisiensi Keuangan</h6>
                <div class="small text-muted">Edit langsung di tabel lalu klik Simpan. Untuk menambah data baru, isi baris
                    biru di paling bawah.</div>
            </div>
            <div class="table-responsive">
                <table class="analysis-table table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th style="min-width:200px">Initiative</th>
                            <th style="min-width:220px">Description</th>
                            <th style="min-width:220px">Keterangan</th>
                            <th style="min-width:200px">Estimated Impact</th>
                            <th class="num">Diterapkan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($initiatives as $ini)
                            @php $n = $loop->index; @endphp
                            <tr>
                                <td>{{ $ini->no }}</td>
                                <td><input form="iniForm" class="cell" name="items[{{ $n }}][initiative]"
                                        value="{{ $ini->initiative }}"></td>
                                <td><input form="iniForm" class="cell" name="items[{{ $n }}][description]"
                                        value="{{ $ini->description }}"></td>
                                <td>
                                    <input form="iniForm" class="cell" name="items[{{ $n }}][keterangan]"
                                        value="{{ $ini->keterangan }}" placeholder="Tujuan initiative">
                                    <input form="iniForm" type="hidden"
                                        name="items[{{ $n }}][analysis_target]"
                                        value="{{ $ini->analysis_target }}">
                                </td>
                                <td><input form="iniForm" class="cell"
                                        name="items[{{ $n }}][estimated_impact]"
                                        value="{{ $ini->estimated_impact }}"></td>
                                <td class="num"><input form="iniForm" type="checkbox" class="form-check-input"
                                        name="items[{{ $n }}][is_implemented]" value="1"
                                        @checked($ini->is_implemented)></td>
                                <td>
                                    <button type="submit" form="iniDelForm" class="row-del" title="Hapus"
                                        formaction="{{ route('analysis-finance.initiatives.destroy', $ini->id) }}"
                                        onclick="return confirm('Hapus inisiatif ini?')">✕</button>
                                </td>
                            </tr>
                        @endforeach

                        <tr class="row-add">
                            <td class="fw-bold text-primary">+</td>
                            <td><input form="iniAddForm" class="cell" name="initiative" placeholder="Initiative baru"
                                    required></td>
                            <td><input form="iniAddForm" class="cell" name="description" placeholder="Description">
                            </td>
                            <td>
                                <input form="iniAddForm" class="cell" name="keterangan"
                                    placeholder="Tujuan initiative">
                                <input form="iniAddForm" type="hidden" name="analysis_target"
                                    value="{{ $endQ }}">
                            </td>
                            <td><input form="iniAddForm" class="cell" name="estimated_impact"
                                    placeholder="Estimated impact"></td>
                            <td class="num"><input form="iniAddForm" type="checkbox" class="form-check-input"
                                    name="is_implemented" value="1"></td>
                            <td><button type="submit" form="iniAddForm" class="btn btn-sm btn-primary">Tambah</button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end">Total inisiatif / diterapkan (seluruh tahun)</td>
                            <td class="num">{{ $initiatives->count() }} /
                                {{ $initiatives->where('is_implemented', true)->count() }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-transparent text-end">
                <button type="submit" form="iniForm" class="btn btn-sm btn-primary">Simpan Perubahan Tabel 1</button>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">Tabel 2: Hasil Rekap Inisiatif <span class="text-muted fw-normal">· s.d. TR
                                {{ $endQ }}</span></h6>
                        <table class="analysis-table table">
                            <thead>
                                <tr>
                                    <th>Target</th>
                                    <th class="num">Total Ide</th>
                                    <th class="num">Total Implementasi</th>
                                    <th class="num">% Achieved</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">{{ $analysis['ide']['target'] }} Ide/Tahun</td>
                                    <td class="num fw-bold">{{ $analysis['ide']['total'] }} Ide</td>
                                    <td class="num good">{{ $analysis['ide']['impl'] }} Ide</td>
                                    <td class="num good">{{ $f1($analysis['ide']['ach']) }}%</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Selisih terhadap target</td>
                                    <td></td>
                                    <td class="num">
                                        {{ $analysis['ide']['impl'] - $analysis['ide']['target'] >= 0 ? '+' : '' }}{{ $analysis['ide']['impl'] - $analysis['ide']['target'] }}
                                        Ide</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="note-box note-ok mt-3">
                            @if ($analysis['ide']['impl'] >= $analysis['ide']['target'])
                                Inovasi tim sudah memenuhi target tahunan sampai TR {{ $endQ }}.
                            @else
                                Masih kurang {{ $analysis['ide']['target'] - $analysis['ide']['impl'] }} ide untuk
                                mencapai target tahunan.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">Visualisasi Pencapaian Inisiatif</h6>
                        <div class="chart-box"><canvas id="chIdeas"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= F. REKAP TRIWULAN ================= --}}
        <div class="recap mt-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3"
                style="position:relative;z-index:2">
                <div>
                    <span class="pill danger mb-2">Customizable Area</span>
                    <h4>Rekapitulasi Laporan Triwulan</h4>
                    <div class="sub">Nilai dihitung otomatis dari data tagihan; isi manual hanya bila perlu menimpa.
                    </div>
                </div>
                @if ($canEdit)
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-sm btn-outline-light"
                            href="{{ route('analysis-finance.template', ['type' => 'quarterly', 'tahun' => $tahun]) }}">Template</a>
                        <button type="button" class="btn btn-sm btn-outline-light" data-import="quarterly"
                            data-label="Rekap Triwulan">Import</button>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                            data-bs-target="#modalQuarterly">Tambah / Edit Laporan Custom</button>
                    </div>
                @endif
            </div>

            <div class="row g-3 mb-4" style="position:relative;z-index:2">
                @foreach ($quarters as $qr)
                    <div class="col-6 col-lg-3">
                        <div
                            class="q-card {{ $qr['status'] === 'Berjalan' ? 'active' : '' }} {{ $qr['projection'] ? 'proj' : '' }}">
                            <div class="d-flex justify-content-between">
                                <span class="label">{{ $qr['label'] }}</span>
                                <span class="pill muted">{{ $qr['status'] }}</span>
                            </div>
                            <div class="val"><span data-count="{{ $qr['pct'] }}" data-dec="1"
                                    data-suffix="%">{{ $f1($qr['pct']) }}%</span></div>
                            <div class="q-bar"><span style="--w:{{ min(100, max(0, $qr['pct'])) }}%"></span></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3" style="position:relative;z-index:2">
                <div class="col-12 col-xl-7">
                    <div class="panel-light h-100">
                        <h6 class="fw-bold">Tabel Rekapitulasi &amp; Catatan</h6>
                        <div class="table-responsive">
                            <table class="analysis-table table">
                                <thead>
                                    <tr>
                                        <th>Triwulan</th>
                                        <th>Status</th>
                                        <th class="num">Pencapaian</th>
                                        <th class="num">Tren</th>
                                        <th>Catatan</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($quarters as $qr)
                                        <tr>
                                            <td class="fw-semibold">{{ $qr['label'] }}</td>
                                            <td><span
                                                    class="pill {{ $qr['status'] === 'Selesai' ? 'success' : ($qr['status'] === 'Berjalan' ? 'info' : 'muted') }}">{{ $qr['status'] }}</span>
                                            </td>
                                            <td class="num">{{ $f1($qr['pct']) }}%@if ($qr['in_pct'] !== null)
                                                    <span class="src manual">M</span>
                                                @endif
                                            </td>
                                            <td class="num">
                                                @if ($qr['delta'] === null)
                                                    -
                                                @elseif ($qr['delta'] >= 0)
                                                    <span class="trend-up">▲ {{ $f1($qr['delta']) }}</span>
                                                @else
                                                    <span class="trend-down">▼ {{ $f1(abs($qr['delta'])) }}</span>
                                                @endif
                                            </td>
                                            <td class="small">{{ $qr['catatan'] ?: '-' }}</td>
                                            <td class="small text-muted">{{ $qr['keterangan'] ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                @php $done = collect($quarters)->where('projection', false); @endphp
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Rata-rata (non-proyeksi)</td>
                                        <td class="num">{{ $done->count() ? $f1($done->avg('pct')) . '%' : '-' }}</td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-5">
                    <div class="panel-light h-100">
                        <h6 class="fw-bold">Tren Pencapaian per Triwulan</h6>
                        <div class="chart-box"><canvas id="chTrend"></canvas></div>
                    </div>
                </div>
            </div>

            {{-- ===== Tabel 3: Persentase Perubahan (di dalam Rekapitulasi Laporan Triwulan) ===== --}}
            <div class="row g-3 mt-1" style="position:relative;z-index:2">
                <div class="col-12">
                    <div class="panel-light">
                        <form method="POST" action="{{ route('analysis-finance.changes') }}">
                            @csrf
                            <input type="hidden" name="tahun" value="{{ $tahun }}">
                            <input type="hidden" name="quarter" value="{{ $endQ }}">
                            {!! $keepInputs !!}
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <h6 class="fw-bold mb-0">Tabel 3: Persentase Perubahan <span
                                        class="text-muted fw-normal">· TR {{ $endQ }} {{ $tahun - 1 }} vs TR
                                        {{ $endQ }} {{ $tahun }}</span></h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="chgAdd">+ Tambah
                                    baris</button>
                            </div>
                            <div class="table-responsive">
                                <table class="analysis-table table chg">
                                    <thead>
                                        <tr>
                                            <th style="min-width:240px">Keterangan</th>
                                            <th>TR {{ $endQ }} {{ $tahun - 1 }}</th>
                                            <th>TR {{ $endQ }} {{ $tahun }}</th>
                                            <th style="min-width:120px">Perbandingan</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="chgBody"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td>Total Presentasi Perubahan</td>
                                            <td class="text-end" id="chgSumA">-</td>
                                            <td class="text-end" id="chgSumB">-</td>
                                            <td class="text-center" id="chgTotal">-</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="small text-muted">Perbandingan = (TR {{ $tahun - 1 }} − TR
                                    {{ $tahun }}) ÷ TR {{ $tahun }} × 100%, sesuai rumus di Excel.</span>
                                <button class="btn btn-sm btn-primary">Simpan Tabel 3</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL ================= --}}
    @if ($canEdit)
        <div class="modal fade" id="modalQuarterly" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <form class="modal-content analysis-wrap" method="POST"
                    action="{{ route('analysis-finance.quarterly') }}">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    {!! $keepInputs !!}
                    <div class="modal-header">
                        <h5 class="modal-title">Laporan Triwulan Custom {{ $tahun }}</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="note-box note-info mb-3">Kosongkan untuk memakai hasil hitung otomatis
                            (placeholder). Isi untuk menimpa.</div>
                        <div class="table-responsive">
                            <table class="analysis-table table">
                                <thead>
                                    <tr>
                                        <th>Triwulan</th>
                                        <th>Pencapaian %</th>
                                        <th>Status</th>
                                        <th>Catatan</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($quarters as $qr)
                                        <tr>
                                            <td class="fw-semibold">{{ $qr['label'] }}</td>
                                            <td><input type="number" step="0.01" class="cell"
                                                    name="rows[{{ $qr['quarter'] }}][nilai_persen]"
                                                    value="{{ $qr['in_pct'] }}"
                                                    placeholder="{{ $f1($qr['auto_pct']) }}"></td>
                                            <td>
                                                <select class="cell" name="rows[{{ $qr['quarter'] }}][status]">
                                                    <option value="">Otomatis ({{ $qr['auto_status'] }})</option>
                                                    @foreach (['Selesai', 'Berjalan', 'Proyeksi'] as $st)
                                                        <option value="{{ $st }}" @selected($qr['in_status'] === $st)>
                                                            {{ $st }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <textarea rows="2" class="cell" name="rows[{{ $qr['quarter'] }}][catatan]">{{ $qr['catatan'] }}</textarea>
                                            </td>
                                            <td>
                                                <textarea rows="2" class="cell" name="rows[{{ $qr['quarter'] }}][keterangan]">{{ $qr['keterangan'] }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" enctype="multipart/form-data" id="importForm"
                    action="">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    {!! $keepInputs !!}
                    <div class="modal-header">
                        <h5 class="modal-title">Import <span id="importLabel"></span> {{ $tahun }}</h5><button
                            type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">Unduh <strong>Template</strong> lebih dulu (sudah berisi data
                            saat ini), edit di Excel, lalu simpan sebagai <strong>CSV</strong>.</p>
                        <input type="file" name="file" accept=".csv,.txt" class="form-control" required>
                        <div class="form-text" id="importHint"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Import</button></div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            /* ---- filter periode ---- */
            const mode = document.getElementById('fMode');
            const toggle = () => {
                document.getElementById('fQWrap').style.display = mode.value === 'q' ? '' : 'none';
                document.getElementById('fBulanWrap').style.display = mode.value === 'bulan' ? '' : 'none';
            };
            mode.addEventListener('change', toggle);
            toggle();
            document.querySelectorAll('#filterForm select').forEach(s => s.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            }));

            /* ---- import modal ---- */
            const importUrl = @json(route('analysis-finance.import', ['type' => '__TYPE__']));
            const hints = {
                overview: 'Kolom: no (1-3), realisasi, pencapaian. Sel kosong = hitung otomatis. Berlaku untuk periode yang sedang dipilih di filter.',
                internal: 'Kolom: bulan (1-12 atau nama bulan), pengajuan_barang, bukti_kas, catatan. Sel kosong = pakai data tagihan.',
                initiatives: 'Kolom: no, initiative, description, keterangan, estimated_impact, status (Diterapkan/Belum). Seluruh daftar tahun ini akan DIGANTI.',
                quarterly: 'Kolom: triwulan (TR 1-4), pencapaian_persen, status, catatan, keterangan. Sel kosong = hitung otomatis.'
            };
            document.querySelectorAll('[data-import]').forEach(btn => btn.addEventListener('click', () => {
                const t = btn.dataset.import;
                document.getElementById('importForm').action = importUrl.replace('__TYPE__', t);
                document.getElementById('importLabel').textContent = btn.dataset.label;
                document.getElementById('importHint').textContent = hints[t];
                new bootstrap.Modal(document.getElementById('modalImport')).show();
            }));

            /* ================= ANIMASI ================= */

            // 1. reveal saat scroll
            const wrap = document.querySelector('.analysis-wrap');
            if (!reduce && 'IntersectionObserver' in window) {
                const io = new IntersectionObserver(entries => entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('in');
                        io.unobserve(e.target);
                    }
                }), {
                    threshold: .08
                });
                wrap.querySelectorAll('.analysis-section, .card, .recap, .q-card').forEach((el, i) => {
                    if (el.closest('.hero')) return;
                    el.classList.add('reveal');
                    el.style.transitionDelay = Math.min((i % 4) * 80, 240) + 'ms';
                    io.observe(el);
                });
            }

            // 2. baris tabel masuk berurutan
            if (!reduce) {
                wrap.querySelectorAll('.analysis-table tbody').forEach(tb => {
                    if (tb.id === 'chgBody') return;
                    tb.querySelectorAll(':scope > tr').forEach((tr, i) => {
                        tr.classList.add('row-anim');
                        tr.style.animationDelay = (250 + Math.min(i, 14) * 45) + 'ms';
                    });
                });
            }

            // 3. count-up angka
            if (!reduce) {
                document.querySelectorAll('[data-count]').forEach(el => {
                    const to = parseFloat(el.dataset.count) || 0,
                        dec = +el.dataset.dec || 0,
                        suf = el.dataset.suffix || '',
                        dur = 1500;
                    let st = null;
                    const step = t => {
                        st = st ?? t;
                        const p = Math.min((t - st) / dur, 1),
                            e = 1 - Math.pow(1 - p, 4);
                        el.textContent = (to * e).toFixed(dec) + suf;
                        if (p < 1) requestAnimationFrame(step);
                    };
                    requestAnimationFrame(step);
                });
            }

            // 4. ripple tombol + loading saat submit
            document.addEventListener('click', e => {
                const b = e.target.closest('.btn');
                if (!b) return;
                const r = b.getBoundingClientRect(),
                    s = document.createElement('span');
                s.className = 'ripple';
                s.style.left = (e.clientX - r.left) + 'px';
                s.style.top = (e.clientY - r.top) + 'px';
                b.appendChild(s);
                setTimeout(() => s.remove(), 650);
            });
            document.querySelectorAll('form').forEach(f => f.addEventListener('submit', e => {
                const b = e.submitter;
                if (b && b.classList.contains('btn')) b.classList.add('is-loading');
            }));

            // 5. tandai sel yang diubah
            document.addEventListener('input', e => {
                if (e.target.classList && e.target.classList.contains('cell')) e.target.classList.add(
                    'dirty');
            });
            document.addEventListener('change', e => {
                if (e.target.classList && e.target.classList.contains('cell')) e.target.classList.add(
                    'dirty');
            });

            /* ---- tabel Persentase Perubahan ---- */
            const chgBody = document.getElementById('chgBody');
            if (chgBody) {
                const rows = @json($changes->map(fn($c) => ['k' => $c->keterangan, 'a' => $c->nilai_lalu, 'b' => $c->nilai_ini])->values());
                const esc = s => (s ?? '').toString().replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g,
                    '&lt;');
                // Rumus Excel: =(C-D)/D*100  (C = tahun lalu, D = tahun ini)
                const calc = (c, d) => d ? (c - d) / d * 100 : null;
                const fmtPct = v => v === null ? '-' : v.toFixed(1) + '%';
                const fmtNum = v => v.toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                });
                let idx = 0;

                const bump = el => {
                    el.classList.add('bump');
                    setTimeout(() => el.classList.remove('bump'), 220);
                };

                const recalc = () => {
                    let sa = 0,
                        sb = 0;
                    chgBody.querySelectorAll('tr:not(.row-out)').forEach(tr => {
                        const a = parseFloat(tr.querySelector('.in-a').value);
                        const b = parseFloat(tr.querySelector('.in-b').value);
                        sa += isNaN(a) ? 0 : a;
                        sb += isNaN(b) ? 0 : b;
                        const out = tr.querySelector('.out');
                        const txt = (isNaN(a) || isNaN(b)) ? '-' : fmtPct(calc(a, b));
                        if (out.textContent !== txt) {
                            out.textContent = txt;
                            bump(out);
                        }
                    });
                    document.getElementById('chgSumA').textContent = fmtNum(sa);
                    document.getElementById('chgSumB').textContent = fmtNum(sb);
                    const tot = document.getElementById('chgTotal');
                    tot.textContent = fmtPct(calc(sa, sb));
                    bump(tot);
                };

                const addChg = (d = {}, animate = false) => {
                    const i = idx++;
                    const tr = document.createElement('tr');
                    if (animate || !reduce) tr.className = 'row-anim';
                    tr.style.animationDelay = animate ? '0s' : (i * 50) + 'ms';
                    tr.innerHTML = `
                        <td><input class="cell" name="items[${i}][keterangan]" value="${esc(d.k)}"></td>
                        <td><input type="number" step="any" class="cell in-a text-end" name="items[${i}][nilai_lalu]" value="${d.a ?? ''}"></td>
                        <td><input type="number" step="any" class="cell in-b text-end" name="items[${i}][nilai_ini]" value="${d.b ?? ''}"></td>
                        <td class="out">-</td>
                        <td class="act"><button type="button" class="row-del" title="Hapus baris">✕</button></td>`;
                    tr.querySelector('.row-del').addEventListener('click', () => {
                        tr.classList.remove('row-anim');
                        tr.classList.add('row-out');
                        setTimeout(() => {
                            tr.remove();
                            recalc();
                        }, 300);
                    });
                    chgBody.appendChild(tr);
                    if (animate) tr.querySelector('input').focus();
                };

                rows.forEach(r => addChg(r));
                if (!rows.length) addChg();
                chgBody.addEventListener('input', recalc);
                document.getElementById('chgAdd').addEventListener('click', () => addChg({}, true));
                recalc();
            }

            /* ---- chart ---- */
            if (typeof Chart === 'undefined') return;
            const C = {
                blue: '#0284c7',
                red: '#e11d48',
                green: '#10b981',
                amber: '#f59e0b',
                slate: '#64748b'
            };
            const anim = {
                duration: reduce ? 0 : 1300,
                easing: 'easeOutQuart',
                delay: ctx => (ctx.type === 'data' && ctx.mode === 'default') ? ctx.dataIndex * 110 + ctx
                    .datasetIndex * 150 : 0
            };
            const bar = (id, labels, datasets, extra = {}) => new Chart(document.getElementById(id), {
                type: 'bar',
                data: {
                    labels,
                    datasets
                },
                options: Object.assign({
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: anim,
                    plugins: {
                        legend: {
                            display: datasets.length > 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }, extra)
            });

            const fin = @json($analysis['fin']);
            bar('chFinance', ['Total Tagihan', 'Total Telat', 'Total On Time'], [{
                data: [fin.total, fin.telat, fin.ontime],
                backgroundColor: [C.blue, C.red, C.green],
                borderRadius: 8
            }]);

            const internal = @json(array_values($internal));
            bar('chInternal', internal.map(r => r.nama.substring(0, 3)), [{
                    label: 'Pengajuan Barang',
                    data: internal.map(r => r.pengajuan),
                    backgroundColor: C.blue,
                    borderRadius: 5
                },
                {
                    label: 'Bukti Kas',
                    data: internal.map(r => r.bukti),
                    backgroundColor: C.amber,
                    borderRadius: 5
                }
            ]);

            const ide = @json($analysis['ide']);
            bar('chIdeas', ['Target Ide/Thn', 'Total Ide Masuk', 'Total Implementasi'], [{
                data: [ide.target, ide.total, ide.impl],
                backgroundColor: [C.slate, C.blue, C.green],
                borderRadius: 8
            }]);

            const qs = @json($quarters);
            new Chart(document.getElementById('chTrend'), {
                type: 'line',
                data: {
                    labels: qs.map(q => 'TR ' + q.quarter),
                    datasets: [{
                        label: 'Pencapaian %',
                        data: qs.map(q => q.pct),
                        borderColor: C.blue,
                        backgroundColor: 'rgba(2,132,199,.12)',
                        fill: true,
                        tension: .35,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        pointBackgroundColor: qs.map(q => q.projection ? '#cbd5e1' : C.blue),
                        segment: {
                            borderDash: ctx => qs[ctx.p1DataIndex].projection ? [6, 6] : undefined
                        }
                    }, {
                        label: 'Target 100%',
                        data: qs.map(() => 100),
                        borderColor: C.slate,
                        borderDash: [4, 4],
                        pointRadius: 0,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: reduce ? 0 : 1800,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        });
    </script>
@endsection
