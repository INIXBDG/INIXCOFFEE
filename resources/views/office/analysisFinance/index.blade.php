@extends('layouts_office.app')

@section('office_contents')
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

        .analysis-wrap .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity .7s ease, transform .7s cubic-bezier(.2, .8, .2, 1);
        }

        .analysis-wrap .reveal.in {
            opacity: 1;
            transform: none;
        }

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

        .analysis-wrap.is-refreshing [data-live],
        .analysis-wrap.is-refreshing .chart-box {
            opacity: .5 !important;
            pointer-events: none;
            transition: opacity .2s ease;
        }

        .analysis-wrap [data-live].swapped *:not(.q-card.active) {
            animation: none !important;
        }

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
        $stateQ = ($mode === 'tahun' && $tahun == now()->year) ? $curQ : $endQ;
        $stateActive = $tahun == now()->year && $stateQ == $curQ;
        $stateText = $stateActive
            ? 'ACTIVE'
            : ($tahun > now()->year || ($tahun == now()->year && $stateQ > $curQ)
                ? 'PROYEKSI'
                : 'SELESAI');
        $periodShort = $mode === 'q' ? 'TR ' . $q : strtoupper($periodLabel);

        $pageData = [
            'state' => [
                'tahun' => $tahun,
                'mode' => $mode,
                'bulan' => $bulan,
                'q' => $q,
                'periode' => $periodKey,
                'periodLabel' => $periodLabel,
            ],
            'fin' => [
                'q' => array_values($finQuarterly),
                'm' => array_values($finMonthly),
            ],
            'internal' => array_values($internal),
            'ide' => $analysis['ide'],
            'quarters' => $quarters,
            'changes' => $changes->map(fn($c) => ['k' => $c->keterangan, 'a' => $c->nilai_lalu, 'b' => $c->nilai_ini])->values(),
        ];
    @endphp

    <script type="application/json" id="page-data">@json($pageData)</script>

    <div class="analysis-wrap" id="analysisRoot">

        <div class="hero mb-3">
            <svg class="hero-road" viewBox="0 0 420 220" preserveAspectRatio="xMaxYMid slice" xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true">
                <g fill="none" stroke="#231f20" stroke-width="36" stroke-linecap="butt" stroke-linejoin="round">
                    <path d="M150 -30 C172 55 235 108 320 110 L440 110" />
                    <path d="M55 255 C105 175 195 118 320 110 L440 110" />
                </g>
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
                    <h1 class="hero-title" id="live-title" data-live>KPI Finance {{ $tahun }}</h1>
                    <p class="hero-sub">Monitoring Kinerja &amp; Efektivitas Pengelolaan Keuangan</p>
                </div>
                <div class="score-box" id="live-hero" data-live>
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

        <div class="card mb-3 reveal">
            <div class="card-body py-3">
                <form method="GET" id="filterForm" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">Periode</label>
                        <select name="mode" id="fMode" class="form-select form-select-sm">
                            <option value="tahun" @selected($mode === 'tahun')>Semua (Satu Tahun)</option>
                            <option value="q" @selected($mode === 'q')>Triwulan</option>
                            <option value="bulan" @selected($mode === 'bulan')>Bulan</option>
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

        <div class="analysis-section">Skema Penilaian Finance</div>
        <div class="row g-3" id="live-skema" data-live>
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

        <div class="analysis-section">
            KPI Finance Overview
            @if ($canEdit)
                <span class="analysis-tools">
                    <button type="button" class="btn btn-sm btn-outline-warning" data-refresh="overview"
                        data-label="KPI Finance Overview">Refresh Data</button>
                    <a class="btn btn-sm btn-outline-secondary" data-tpl="overview"
                        href="{{ route('analysis-finance.template', ['type' => 'overview', 'tahun' => $tahun, 'mode' => $mode, 'bulan' => $bulan, 'q' => $q]) }}">Template</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-import="overview"
                        data-label="Realisasi &amp; Pencapaian">Import</button>
                </span>
            @endif
        </div>
        <form method="POST" action="{{ route('analysis-finance.overview') }}" class="card" id="live-overview"
            data-live data-ajax>
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

        <div class="analysis-section">
            Financial Perspective
            @if ($canEdit)
                <span class="analysis-tools">
                    <div class="btn-group btn-group-sm" role="group" id="finToggle">
                        <button type="button" class="btn btn-primary" data-fin-view="q">Triwulan</button>
                        <button type="button" class="btn btn-outline-primary" data-fin-view="m">Bulanan</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning" data-refresh="financial"
                        data-label="Financial Perspective">Refresh Data</button>
                </span>
            @endif
        </div>
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body" id="live-fin" data-live>
                        <h6 class="fw-bold">Ringkasan Tagihan Keuangan <span class="text-muted fw-normal">·
                                {{ $periodLabel }}</span></h6>

                        @foreach (['q' => $finQuarterly, 'm' => $finMonthly] as $key => $set)
                            <div class="table-responsive fin-view" data-fin-set="{{ $key }}"
                                style="{{ $key === 'q' ? '' : 'display:none' }}">
                                <table class="analysis-table table">
                                    <thead>
                                        <tr>
                                            <th>{{ $key === 'q' ? 'Triwulan' : 'Bulan' }}</th>
                                            <th class="num">Total</th>
                                            <th class="num">On Time</th>
                                            <th class="num">Telat</th>
                                            <th class="num">%</th>
                                            <th class="text-end">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($set as $m)
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
                                                {{ number_format(array_sum(array_column($set, 'nominal')), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endforeach

                        <div class="note-box note-info mt-3">
                            @if ($analysis['fin']['persen'] >= $setting->target_tagihan)
                                Tingkat ketepatan pembayaran berada di atas ambang batas
                                (≥{{ rtrim(rtrim(number_format($setting->target_tagihan, 2), '0'), '.') }}%).
                            @else
                                Tingkat ketepatan pembayaran masih di bawah ambang batas
                                (≥{{ rtrim(rtrim(number_format($setting->target_tagihan, 2), '0'), '.') }}%).
                            @endif
                            Data dihitung otomatis dari Tagihan Perusahaan.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="fw-bold">Visualisasi Status Tagihan <span class="text-muted fw-normal" id="finChartLabel">· per Triwulan</span></h6>
                        <div class="chart-box"><canvas id="chFinance"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="analysis-section int">
            Internal Process Perspective
            @if ($canEdit)
                <span class="analysis-tools">
                    <button type="button" class="btn btn-sm btn-outline-warning" data-refresh="internal"
                        data-label="Internal Process">Refresh Data</button>
                    <a class="btn btn-sm btn-outline-secondary" data-tpl="internal"
                        href="{{ route('analysis-finance.template', ['type' => 'internal', 'tahun' => $tahun]) }}">Template</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-import="internal"
                        data-label="Internal Process">Import</button>
                </span>
            @endif
        </div>
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <form method="POST" action="{{ route('analysis-finance.internal') }}" class="card h-100"
                    id="live-internal" data-live data-ajax>
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

        <div class="analysis-section learn">
            Learning &amp; Growth Perspective
            @if ($canEdit)
                <span class="analysis-tools">
                    <a class="btn btn-sm btn-outline-secondary" data-tpl="initiatives"
                        href="{{ route('analysis-finance.template', ['type' => 'initiatives', 'tahun' => $tahun]) }}">Template</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-import="initiatives"
                        data-label="Inisiatif Efisiensi">Import</button>
                </span>
            @endif
        </div>

        <div id="live-learning" data-live>
            <form id="iniForm" method="POST" action="{{ route('analysis-finance.initiatives') }}" data-ajax>@csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">{!! $keepInputs !!}
            </form>
            <form id="iniAddForm" method="POST" action="{{ route('analysis-finance.initiatives.add') }}"
                data-ajax>@csrf
                <input type="hidden" name="tahun" value="{{ $tahun }}">{!! $keepInputs !!}
            </form>

            <div class="card mb-3">
                <div class="card-body pb-2">
                    <h6 class="fw-bold mb-0">Tabel 1: Daftar Inisiatif Efisiensi Keuangan</h6>
                    <div class="small text-muted">Edit langsung di tabel lalu klik Simpan. Untuk menambah data baru, isi
                        baris biru di paling bawah.</div>
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
                                        <button type="button" class="row-del" title="Hapus"
                                            data-url="{{ route('analysis-finance.initiatives.destroy', $ini->id) }}">✕</button>
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="row-add">
                                <td class="fw-bold text-primary">+</td>
                                <td><input form="iniAddForm" class="cell" name="initiative"
                                        placeholder="Initiative baru" required></td>
                                <td><input form="iniAddForm" class="cell" name="description"
                                        placeholder="Description"></td>
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
                                <td><button type="button" id="btnAddInitiative" class="btn btn-sm btn-primary">Tambah</button></td>
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
                    <button type="submit" form="iniForm" class="btn btn-sm btn-primary">Simpan Perubahan Tabel
                        1</button>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-body" id="live-ide" data-live>
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
                        <button type="button" class="btn btn-sm btn-outline-warning" data-refresh="quarterly"
                            data-label="Rekapitulasi Triwulan">Refresh Data</button>
                        <a class="btn btn-sm btn-outline-light" data-tpl="quarterly"
                            href="{{ route('analysis-finance.template', ['type' => 'quarterly', 'tahun' => $tahun]) }}">Template</a>
                        <button type="button" class="btn btn-sm btn-outline-light" data-import="quarterly"
                            data-label="Rekap Triwulan">Import</button>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                            data-bs-target="#modalQuarterly">Tambah / Edit Laporan Custom</button>
                    </div>
                @endif
            </div>

            <div class="row g-3 mb-4" id="live-qcards" data-live style="position:relative;z-index:2">
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
                    <div class="panel-light h-100" id="live-qtable" data-live>
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

            <div class="row g-3 mt-1" style="position:relative;z-index:2">
                <div class="col-12">
                    <div class="panel-light" id="live-chg" data-live>
                        <form method="POST" action="{{ route('analysis-finance.changes') }}" data-ajax>
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

    @if ($canEdit)
        <div class="modal fade" id="modalQuarterly" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <form class="modal-content analysis-wrap" method="POST"
                    action="{{ route('analysis-finance.quarterly') }}" data-ajax>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Laporan Triwulan Custom <span class="js-tahun">{{ $tahun }}</span>
                        </h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="live-qmodal" data-live>
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
                    action="" data-ajax>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Import <span id="importLabel"></span> <span
                                class="js-tahun">{{ $tahun }}</span></h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal"></button>
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

        <div class="modal fade" id="modalRefresh" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" id="refreshForm" action="" data-ajax>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Refresh Data <span id="refreshLabel"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2" id="refreshDesc"></p>
                        <p class="small text-muted mb-0">Data lama akan dihapus dan diganti dengan hasil hitung terbaru
                            dari Tagihan Perusahaan. Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-warning">Ya, Refresh Data</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
    <script>
        $(function() {
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const $root = $('#analysisRoot');
            const csrf = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val();

            function collectFormData(form) {
                const fd = new FormData();

                Array.from(form.elements).forEach(el => {
                    if (!el.name || el.disabled) return;
                    if (el.type === 'file') return;

                    if (el.type === 'checkbox' || el.type === 'radio') {
                        if (el.checked) fd.append(el.name, el.value || '1');
                    } else {
                        fd.append(el.name, el.value);
                    }
                });

                const formId = form.id;
                if (formId) {
                    document.querySelectorAll(`[form="${formId}"]`).forEach(el => {
                        if (!el.name || el.disabled) return;
                        if (el.type === 'file') return;

                        if (el.type === 'checkbox' || el.type === 'radio') {
                            if (el.checked) {
                                fd.delete(el.name);
                                fd.append(el.name, el.value || '1');
                            }
                        } else {
                            fd.set(el.name, el.value); 
                        }
                    });
                }

                console.log('=== FormData yang dikirim ===');
                for (let [key, value] of fd.entries()) {
                    console.log(key, '=', value);
                }

                return fd;
            }

            $(document).on('submit', 'form[data-ajax]', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                const btn = e.originalEvent && e.originalEvent.submitter;
                const formData = collectFormData(this);

                send($(this).attr('action'), formData, btn);
            });

            // cegah Enter di baris tambah men-submit form kosong
            $(document).on('keydown', 'tr.row-add input', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $('#btnAddInitiative').trigger('click');
                }
            });

            let DATA = JSON.parse($('#page-data').text());
            let finKey = 'q';
            let refreshXhr = null;
            let chgIdx = 0;
            const charts = {};

            const tplBase = @json(route('analysis-finance.template', ['type' => '__TYPE__']));
            const importBase = @json(route('analysis-finance.import', ['type' => '__TYPE__']));
            const refreshBase = @json(route('analysis-finance.refresh', ['type' => '__TYPE__']));

            const hints = {
                overview: 'Kolom: no (1-3), realisasi, pencapaian. Sel kosong = hitung otomatis. Berlaku untuk periode yang sedang dipilih di filter.',
                internal: 'Kolom: bulan (1-12 atau nama bulan), pengajuan_barang, bukti_kas, catatan. Sel kosong = pakai data tagihan.',
                initiatives: 'Kolom: no, initiative, description, keterangan, estimated_impact, status (Diterapkan/Belum). Seluruh daftar tahun ini akan DIGANTI.',
                quarterly: 'Kolom: triwulan (TR 1-4), pencapaian_persen, status, catatan, keterangan. Sel kosong = hitung otomatis.'
            };

            const esc = s => (s ?? '').toString().replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g,
                '&lt;');

            function notify(type, msg) {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: type,
                        title: msg,
                        showConfirmButton: false,
                        timer: type === 'error' ? 4500 : 2500,
                        timerProgressBar: true
                    });
                } else {
                    alert(msg);
                }
            }

            function confirmBox(text) {
                if (window.Swal) {
                    return Swal.fire({
                        title: 'Konfirmasi',
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal'
                    }).then(r => r.isConfirmed);
                }
                return Promise.resolve(confirm(text));
            }

            function errMsg(xhr) {
                const j = xhr.responseJSON;
                if (j && j.errors) return Object.values(j.errors).flat().join('\n');
                if (j && j.message) return j.message;
                if (xhr.status === 419) return 'Sesi berakhir. Muat ulang halaman lalu coba lagi.';
                return 'Terjadi kesalahan. Silakan coba lagi.';
            }

            function setLoading(btn, on) {
                if (btn) $(btn).toggleClass('is-loading', on);
            }

            function addState(fd) {
                const s = DATA.state;
                ['tahun', 'mode', 'bulan', 'q', 'periode'].forEach(k => {
                    if (!fd.has(k)) fd.append(k, s[k]);
                });
            }

            function closeModals() {
                document.querySelectorAll('.modal.show').forEach(m => {
                    const inst = bootstrap.Modal.getInstance(m);
                    if (inst) inst.hide();
                });
            }

            function send(url, fd, btn) {
                addState(fd);
                setLoading(btn, true);
                return $.ajax({
                        url: url,
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': csrf
                        }
                    })
                    .done(res => {
                        notify('success', res.message || 'Berhasil disimpan.');
                        closeModals();
                        refreshLive();
                    })
                    .fail(xhr => notify('error', errMsg(xhr)))
                    .always(() => setLoading(btn, false));
            }

            $(document).on('click', '#btnAddInitiative', function () {
                const btn = this;
                const $row = $(this).closest('tr.row-add');

                const initiative = ($row.find('input[name="initiative"]').val() || '').trim();
                if (!initiative) {
                    notify('error', 'Kolom Initiative wajib diisi.');
                    $row.find('input[name="initiative"]').focus();
                    return;
                }

                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('tahun', DATA.state.tahun);
                fd.append('initiative', initiative);
                fd.append('description', $row.find('input[name="description"]').val() || '');
                fd.append('keterangan', $row.find('input[name="keterangan"]').val() || '');
                fd.append('analysis_target', $row.find('input[name="analysis_target"]').val() || '1');
                fd.append('estimated_impact', $row.find('input[name="estimated_impact"]').val() || '');

                if ($row.find('input[name="is_implemented"]').is(':checked')) {
                    fd.append('is_implemented', '1');
                }

                // Debug
                console.log('=== Manual Add FormData ===');
                for (let [k, v] of fd.entries()) {
                    console.log(k, '=', v);
                }

                const url = $('#iniAddForm').attr('action');
                if (!url) {
                    notify('error', 'Form add tidak ditemukan.');
                    return;
                }

                send(url, fd, btn);
            });

            $(document).on('click', '.row-del[data-url]', async function() {
                if (!(await confirmBox('Hapus inisiatif ini?'))) return;
                const fd = new FormData();
                fd.append('_method', 'DELETE');
                fd.append('_token', csrf);
                send($(this).data('url'), fd, this);
            });

            function refreshLive(url) {
                url = url || location.href;
                if (refreshXhr) refreshXhr.abort();
                $root.addClass('is-refreshing');

                refreshXhr = $.ajax({
                        url: url,
                        method: 'GET',
                        dataType: 'html',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .done(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');

                        $('[data-live]').each(function() {
                            const src = doc.getElementById(this.id);
                            if (!src) return;
                            const $sy = $(this).find('.scroll-y');
                            const st = $sy.length ? $sy.scrollTop() : 0;
                            this.innerHTML = src.innerHTML;
                            $(this).addClass('swapped');
                            if (st) $(this).find('.scroll-y').scrollTop(st);
                        });

                        const dataEl = doc.getElementById('page-data');
                        if (dataEl) DATA = JSON.parse(dataEl.textContent);

                        afterRefresh();
                    })
                    .fail((xhr, status) => {
                        if (status !== 'abort') notify('error', 'Gagal memuat ulang data.');
                    })
                    .always(() => {
                        $root.removeClass('is-refreshing');
                    });

                return refreshXhr;
            }

            const toggleFilter = () => {
                const m = $('#fMode').val();
                $('#fQWrap').toggle(m === 'q');
                $('#fBulanWrap').toggle(m === 'bulan');
            };

            function applyState() {
                const s = DATA.state;
                $('.js-tahun').text(s.tahun);
                $('a[data-tpl]').each(function() {
                    const t = $(this).data('tpl');
                    const p = t === 'overview' ? {
                        tahun: s.tahun,
                        mode: s.mode,
                        bulan: s.bulan,
                        q: s.q
                    } : {
                        tahun: s.tahun
                    };
                    this.href = tplBase.replace('__TYPE__', t) + '?' + $.param(p);
                });
                $('#fMode').val(s.mode);
                $('#filterForm [name=q]').val(s.q);
                $('#filterForm [name=bulan]').val(s.bulan);
                $('#filterForm [name=tahun]').val(s.tahun);
                toggleFilter();
            }

            $('#fMode').on('change', toggleFilter);
            toggleFilter();

            function applyFilter() {
                const url = location.pathname + '?' + $('#filterForm').serialize();
                history.pushState({}, '', url);
                refreshLive(url);
            }
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                applyFilter();
            });
            $('#filterForm select').on('change', applyFilter);
            window.addEventListener('popstate', () => refreshLive(location.href));

            $(document).on('click', '[data-import]', function() {
                const t = $(this).data('import');
                $('#importForm').attr('action', importBase.replace('__TYPE__', t));
                $('#importForm')[0].reset();
                $('#importLabel').text($(this).data('label'));
                $('#importHint').text(hints[t]);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalImport')).show();
            });

            $(document).on('click', '[data-refresh]', function() {
                const t = $(this).data('refresh');
                const s = DATA.state;
                const desc = {
                    overview: 'Isian manual Realisasi dan Pencapaian indikator 1 dan 2 untuk periode ' + s
                        .periodLabel + ' akan dihapus.',
                    financial: 'Isian manual Realisasi dan Pencapaian indikator Pembayaran Tagihan untuk periode ' +
                        s.periodLabel + ' akan dihapus.',
                    internal: 'Seluruh isian manual Internal Process tahun ' + s.tahun +
                        ' (12 bulan) dan isian manual indikator 2 periode ' + s.periodLabel +
                        ' akan dihapus.',
                    quarterly: 'Isian manual Pencapaian, Skor, dan Status triwulan tahun ' + s.tahun +
                        ' akan dihapus. Catatan dan Keterangan tetap disimpan.'
                } [t];
                $('#refreshForm').attr('action', refreshBase.replace('__TYPE__', t));
                $('#refreshLabel').text('— ' + $(this).data('label'));
                $('#refreshDesc').text(desc);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRefresh')).show();
            });

            function applyFinView() {
                $('.fin-view').each(function() {
                    $(this).toggle($(this).data('finSet') === finKey);
                });
                $('[data-fin-view]').each(function() {
                    const on = $(this).data('finView') === finKey;
                    $(this).toggleClass('btn-primary', on).toggleClass('btn-outline-primary', !on);
                });
                $('#finChartLabel').text(finKey === 'q' ? '· per Triwulan' : '· per Bulan');
            }

            $(document).on('click', '[data-fin-view]', function() {
                finKey = $(this).data('finView');
                applyFinView();
                updateCharts();
            });

            const calc = (c, d) => d ? (c - d) / d * 100 : null;
            const fmtPct = v => v === null ? '-' : v.toFixed(1) + '%';
            const fmtNum = v => v.toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });
            const bump = el => {
                el.classList.add('bump');
                setTimeout(() => el.classList.remove('bump'), 220);
            };

            function chgRecalc() {
                const $b = $('#chgBody');
                if (!$b.length) return;
                let sa = 0,
                    sb = 0;
                $b.find('tr:not(.row-out)').each(function() {
                    const a = parseFloat($(this).find('.in-a').val());
                    const b = parseFloat($(this).find('.in-b').val());
                    sa += isNaN(a) ? 0 : a;
                    sb += isNaN(b) ? 0 : b;
                    const out = $(this).find('.out')[0];
                    const txt = (isNaN(a) || isNaN(b)) ? '-' : fmtPct(calc(a, b));
                    if (out.textContent !== txt) {
                        out.textContent = txt;
                        bump(out);
                    }
                });
                $('#chgSumA').text(fmtNum(sa));
                $('#chgSumB').text(fmtNum(sb));
                const tot = document.getElementById('chgTotal');
                tot.textContent = fmtPct(calc(sa, sb));
                bump(tot);
            }

            function chgAdd(d = {}, animate = false) {
                const i = chgIdx++;
                const $tr = $('<tr>');
                if (animate) $tr.addClass('row-anim').css('animationDelay', '0s');
                $tr.html(`
                    <td><input class="cell" name="items[${i}][keterangan]" value="${esc(d.k)}"></td>
                    <td><input type="number" step="any" class="cell in-a text-end" name="items[${i}][nilai_lalu]" value="${d.a ?? ''}"></td>
                    <td><input type="number" step="any" class="cell in-b text-end" name="items[${i}][nilai_ini]" value="${d.b ?? ''}"></td>
                    <td class="out">-</td>
                    <td class="act"><button type="button" class="row-del" title="Hapus baris">✕</button></td>`);
                $('#chgBody').append($tr);
                if (animate) $tr.find('input').first().trigger('focus');
            }

            function initChg(animate) {
                chgIdx = 0;
                $('#chgBody').empty();
                (DATA.changes || []).forEach((r, n) => {
                    chgAdd(r);
                    if (animate && !reduce) {
                        const tr = $('#chgBody tr').last();
                        tr.addClass('row-anim').css('animationDelay', (n * 50) + 'ms');
                    }
                });
                if (!(DATA.changes || []).length) chgAdd();
                chgRecalc();
            }

            $(document).on('input', '#chgBody', chgRecalc);
            $(document).on('click', '#chgAdd', () => chgAdd({}, true));
            $(document).on('click', '#chgBody .row-del', function() {
                const $tr = $(this).closest('tr');
                $tr.removeClass('row-anim').addClass('row-out');
                setTimeout(() => {
                    $tr.remove();
                    chgRecalc();
                }, 300);
            });

            const C = {
                blue: '#0284c7',
                red: '#e11d48',
                green: '#10b981',
                amber: '#f59e0b',
                slate: '#64748b'
            };

            const finLabels = () => DATA.fin[finKey].map(r => finKey === 'm' ? r.nama.substring(0, 3) : r.nama);
            const finSets = () => [{
                    label: 'Total',
                    data: DATA.fin[finKey].map(r => r.total),
                    backgroundColor: C.blue,
                    borderRadius: 5
                },
                {
                    label: 'On Time',
                    data: DATA.fin[finKey].map(r => r.ontime),
                    backgroundColor: C.green,
                    borderRadius: 5
                },
                {
                    label: 'Telat',
                    data: DATA.fin[finKey].map(r => r.telat),
                    backgroundColor: C.red,
                    borderRadius: 5
                }
            ];
            const internalLabels = () => DATA.internal.map(r => r.nama.substring(0, 3));
            const internalSets = () => [{
                    label: 'Pengajuan Barang',
                    data: DATA.internal.map(r => r.pengajuan),
                    backgroundColor: C.blue,
                    borderRadius: 5
                },
                {
                    label: 'Bukti Kas',
                    data: DATA.internal.map(r => r.bukti),
                    backgroundColor: C.amber,
                    borderRadius: 5
                }
            ];
            const ideLabels = ['Target Ide/Thn', 'Total Ide Masuk', 'Total Implementasi'];
            const ideSets = () => [{
                data: [DATA.ide.target, DATA.ide.total, DATA.ide.impl],
                backgroundColor: [C.slate, C.blue, C.green],
                borderRadius: 8
            }];

            function setData(chart, labels, sets) {
                chart.data.labels = labels;
                sets.forEach((s, i) => {
                    if (chart.data.datasets[i]) chart.data.datasets[i].data = s.data;
                    else chart.data.datasets[i] = s;
                });
                chart.data.datasets.length = sets.length;
                chart.update();
            }

            function updateCharts() {
                if (!charts.fin) return;
                setData(charts.fin, finLabels(), finSets());
                setData(charts.internal, internalLabels(), internalSets());
                setData(charts.ide, ideLabels, ideSets());

                const qs = DATA.quarters;
                const t = charts.trend;
                t.data.labels = qs.map(q => 'TR ' + q.quarter);
                t.data.datasets[0].data = qs.map(q => q.pct);
                t.data.datasets[0].pointBackgroundColor = qs.map(q => q.projection ? '#cbd5e1' : C.blue);
                t.data.datasets[1].data = qs.map(() => 100);
                t.update();
            }

            function buildCharts() {
                if (typeof Chart === 'undefined') return;

                const anim = {
                    duration: reduce ? 0 : 1300,
                    easing: 'easeOutQuart',
                    delay: ctx => (ctx.type === 'data' && ctx.mode === 'default') ? ctx.dataIndex * 110 + ctx
                        .datasetIndex * 150 : 0
                };

                const bar = (id, labels, datasets) => new Chart(document.getElementById(id), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets
                    },
                    options: {
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
                    }
                });

                charts.fin = bar('chFinance', finLabels(), finSets());
                charts.internal = bar('chInternal', internalLabels(), internalSets());
                charts.ide = bar('chIdeas', ideLabels, ideSets());

                const qs = DATA.quarters;
                charts.trend = new Chart(document.getElementById('chTrend'), {
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
                                borderDash: ctx => (DATA.quarters[ctx.p1DataIndex] || {}).projection ? [6, 6] : undefined
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
            }

            function afterRefresh() {
                applyState();
                applyFinView();
                initChg(false);
                updateCharts();
            }

            if (!reduce && 'IntersectionObserver' in window) {
                const io = new IntersectionObserver(entries => entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('in');
                        io.unobserve(e.target);
                    }
                }), {
                    threshold: .08
                });
                $root.find('.analysis-section, .card, .recap, .q-card').each(function(i) {
                    if ($(this).closest('.hero').length) return;
                    this.classList.add('reveal');
                    this.style.transitionDelay = Math.min((i % 4) * 80, 240) + 'ms';
                    io.observe(this);
                });
            }

            if (!reduce) {
                $root.find('.analysis-table tbody').each(function() {
                    if (this.id === 'chgBody') return;
                    $(this).children('tr').each(function(i) {
                        this.classList.add('row-anim');
                        this.style.animationDelay = (250 + Math.min(i, 14) * 45) + 'ms';
                    });
                });

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

            document.addEventListener('input', e => {
                if (e.target.classList && e.target.classList.contains('cell')) e.target.classList.add('dirty');
            });
            document.addEventListener('change', e => {
                if (e.target.classList && e.target.classList.contains('cell')) e.target.classList.add('dirty');
            });

            initChg(true);
            buildCharts();
        });
    </script>
@endsection