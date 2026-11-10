@extends('layout_HR.app')

@push('styles')
    <style>
        :root {
            --pri: #4f46e5;
            --pri-dark: #3730a3;
            --pri-soft: #eef2ff;
            --ink: #111827;
            --ink-2: #374151;
            --mute: #6b7280;
            --faint: #9ca3af;
            --line: #e5e7eb;
            --wash: #f9fafb;
            --amber: #b45309;
            --amber-soft: #fef3c7;
            --ok: #059669;
            --ok-soft: #d1fae5;
            --bad: #dc2626;
            --bad-soft: #fee2e2;
            --info: #0284c7;
            --info-soft: #e0f2fe;
            --violet: #7c3aed;
            --violet-soft: #ede9fe;
            --r: 14px;
            --r-sm: 10px;
        }

        .mdk {
            color: var(--ink);
            font-family: inherit;
        }

        .modal-backdrop {
            --bs-backdrop-bg: #111827;
            --bs-backdrop-opacity: .45;
        }

        .modal-backdrop.show {
            opacity: .45;
        }

        .mdk .page-title {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -.02em;
            margin: 0;
            color: var(--ink);
        }

        .mdk .page-sub {
            color: var(--mute);
            font-size: .9rem;
            margin: .2rem 0 0;
        }

        .mdk h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: var(--ink);
        }

        .mdk .sub {
            color: var(--mute);
            font-size: .85rem;
        }

        .mdk .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--r);
        }

        .mdk .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1.4rem;
            flex-wrap: wrap;
        }

        .mdk .panel-head h2 {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: 1rem;
        }

        .mdk .panel-head h2 i {
            color: var(--pri);
        }

        .mdk .panel-body {
            padding: 0 1.4rem 1.2rem;
        }

        .mdk .panel-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .9rem 1.4rem;
            border-top: 1px solid var(--line);
        }

        .mdk .btn {
            border-radius: 999px;
            font-weight: 600;
            font-size: .85rem;
            padding: .45rem 1rem;
        }

        .mdk .btn-sm {
            padding: .35rem .85rem;
        }

        .mdk .btn-brand {
            background: var(--pri);
            color: #fff;
            border: 1px solid var(--pri);
        }

        .mdk .btn-brand:hover,
        .mdk .btn-brand:focus {
            background: var(--pri-dark);
            border-color: var(--pri-dark);
            color: #fff;
        }

        .mdk .btn-line {
            background: #fff;
            border: 1px solid var(--line);
            color: var(--ink-2);
        }

        .mdk .btn-line:hover {
            border-color: var(--pri);
            color: var(--pri);
            background: var(--pri-soft);
        }

        .mdk .btn-line.danger:hover {
            border-color: var(--bad);
            color: var(--bad);
            background: var(--bad-soft);
        }

        .mdk .btn:focus-visible,
        .mdk .form-control:focus,
        .mdk .form-select:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .18);
            border-color: var(--pri);
            outline: 0;
        }

        .mdk .form-control,
        .mdk .form-select {
            font-size: .875rem;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            color: var(--ink);
            padding: .55rem .85rem;
            background-color: #fff;
        }

        .mdk .form-select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 2.4rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='m6 9 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right .85rem center;
            background-size: 14px;
        }

        .mdk .form-select.pill {
            border-radius: 999px;
            padding-left: 1.1rem;
            background-color: var(--wash);
        }

        .mdk .form-label {
            font-size: .8rem;
            font-weight: 700;
            color: var(--ink-2);
            margin-bottom: .35rem;
        }

        .mdk .ol-req::after {
            content: ' *';
            color: var(--bad);
        }

        .mdk .form-check-input:checked {
            background-color: var(--pri);
            border-color: var(--pri);
        }

        .mdk .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .18);
            border-color: var(--pri);
        }

        .mdk .form-switch .form-check-input {
            width: 2.4em;
            height: 1.3em;
        }

        .mdk .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
        }

        .mdk .stat {
            display: flex;
            align-items: center;
            gap: .95rem;
            padding: 1.25rem 1.5rem;
            border-right: 1px solid var(--line);
        }

        .mdk .stat:last-child {
            border-right: 0;
        }

        .mdk .stat-ico {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 1.05rem;
            flex: none;
        }

        .mdk .t-pri {
            background: var(--pri-soft);
            color: var(--pri);
        }

        .mdk .t-amber {
            background: var(--amber-soft);
            color: var(--amber);
        }

        .mdk .t-bad {
            background: var(--bad-soft);
            color: var(--bad);
        }

        .mdk .t-violet {
            background: var(--violet-soft);
            color: var(--violet);
        }

        .mdk .stat .n {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -.02em;
        }

        .mdk .stat .l {
            color: var(--mute);
            font-size: .8rem;
            margin-top: .2rem;
        }

        @keyframes mdkShimmer {
            to {
                background-position: -200% 0;
            }
        }

        .mdk .summary.is-loading .n {
            color: transparent;
            width: 60px;
            border-radius: 6px;
            background: linear-gradient(90deg, var(--wash) 25%, var(--line) 50%, var(--wash) 75%);
            background-size: 200% 100%;
            animation: mdkShimmer 1.5s infinite;
        }

        .mdk .tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .74rem;
            font-weight: 700;
            padding: .2rem .65rem;
            border-radius: 999px;
            white-space: nowrap;
            text-decoration: none;
        }

        .mdk .tag-done {
            background: var(--ok-soft);
            color: var(--ok);
        }

        .mdk .tag-pending {
            background: var(--amber-soft);
            color: var(--amber);
        }

        .mdk .tag-info {
            background: var(--info-soft);
            color: var(--info);
        }

        .mdk .tag-muted {
            background: var(--wash);
            color: var(--mute);
            border: 1px solid var(--line);
        }

        .mdk .filters {
            padding: 0 1.4rem 1rem;
        }

        .mdk .search-wrap {
            position: relative;
            max-width: 460px;
        }

        .mdk .search-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--faint);
            font-size: .85rem;
            pointer-events: none;
        }

        .mdk .search-wrap .form-control {
            padding-left: 2.4rem;
            border-radius: 999px;
            background: var(--wash);
        }

        .mdk .search-wrap .form-control:focus {
            background: #fff;
        }

        .mdk table.tbl thead th {
            font-size: .76rem;
            color: var(--mute);
            font-weight: 700;
            background: transparent;
            white-space: nowrap;
            border-bottom: 1.5px solid var(--line);
            padding: .8rem 1rem;
        }

        .mdk table.tbl td {
            vertical-align: middle;
            font-size: .85rem;
            padding: .85rem 1rem;
            color: var(--ink-2);
            border-bottom: 1px solid var(--line);
        }

        .mdk table.tbl tbody tr:hover>td {
            background: var(--wash);
        }

        .mdk table.is-loading tbody {
            opacity: .4;
            pointer-events: none;
        }

        .mdk .ol-ket {
            max-width: 240px;
            white-space: normal;
        }

        .mdk .pagination .page-link {
            border: 0;
            color: var(--ink-2);
            font-size: .82rem;
            padding: .38rem .75rem;
            margin: 0 2px;
            border-radius: 999px;
            background: transparent;
        }

        .mdk .pagination .page-link:hover {
            background: var(--pri-soft);
            color: var(--pri);
        }

        .mdk .pagination .page-item.active .page-link {
            background: var(--pri);
            color: #fff;
        }

        .mdk .pagination .page-item.disabled .page-link {
            color: var(--faint);
        }

        .mdk .person {
            display: flex;
            align-items: center;
            gap: .75rem;
            min-width: 210px;
        }

        .mdk .avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: .8rem;
            flex: none;
        }

        .mdk .av-0 {
            background: var(--pri-soft);
            color: var(--pri);
        }

        .mdk .av-1 {
            background: var(--amber-soft);
            color: var(--amber);
        }

        .mdk .av-2 {
            background: var(--violet-soft);
            color: var(--violet);
        }

        .mdk .av-3 {
            background: var(--info-soft);
            color: var(--info);
        }

        .mdk .person .nm {
            font-weight: 700;
            line-height: 1.2;
            color: var(--ink);
        }

        .mdk .person .nip {
            color: var(--mute);
            font-size: .75rem;
        }

        .mdk .act .btn {
            padding: .3rem .65rem;
        }

        .mdk .empty {
            padding: 2.4rem 1rem;
            text-align: center;
            color: var(--faint);
            font-size: .85rem;
        }

        .mdk .empty i.big {
            display: block;
            font-size: 1.8rem;
            opacity: .5;
            margin-bottom: .5rem;
        }

        .mdk .empty.loading {
            color: var(--pri);
            font-weight: 600;
        }

        .mdk .modal-header {
            background: #fff;
            border-bottom: 1px solid var(--line);
            padding: 1.1rem 1.5rem;
        }

        .mdk .modal-header h2 {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: 1.08rem;
        }

        .mdk .modal-header h2 i {
            color: var(--pri);
        }

        .mdk .modal-footer {
            border-top: 1px solid var(--line);
            background: var(--wash);
            padding: .9rem 1.5rem;
        }

        .mdk .modal-content {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(17, 24, 39, .28);
            overflow: hidden;
        }

        .mdk-modal .profile-head {
            display: flex;
            gap: 1.1rem;
            align-items: center;
            padding: 1.5rem 1.6rem;
            background: #312e81;
            color: #fff;
        }

        .mdk-modal .profile-head h2 {
            color: #fff;
            font-size: 1.25rem;
            letter-spacing: -.01em;
        }

        .mdk-modal .profile-head .sub {
            color: rgba(255, 255, 255, .75);
        }

        .mdk-modal .profile-head .avatar {
            width: 62px;
            height: 62px;
            font-size: 1.2rem;
            border-radius: 18px;
            background: #fff;
            color: var(--pri);
        }

        .mdk-modal .profile-head .btn-line {
            border: 0;
        }

        .mdk-modal .profile-head .btn-close {
            filter: brightness(0) invert(1);
            opacity: .8;
        }

        .mdk-modal .profile-head .tag-done {
            background: rgba(255, 255, 255, .16);
            color: #c7d2fe;
        }

        .mdk-modal .profile-head .tag-pending {
            background: rgba(255, 255, 255, .16);
            color: #ffe2a8;
        }

        .mdk-modal .nav-tabs {
            border: 0;
            padding: .8rem 1.25rem;
            gap: .35rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            background: var(--wash);
            border-bottom: 1px solid var(--line);
        }

        .mdk-modal .nav-tabs .nav-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            border: 0;
            border-radius: 999px;
            color: var(--mute);
            font-weight: 700;
            font-size: .84rem;
            white-space: nowrap;
            padding: .5rem 1rem;
            background: none;
            margin: 0;
        }

        .mdk-modal .nav-tabs .nav-link:hover {
            color: var(--pri);
        }

        .mdk-modal .nav-tabs .nav-link.active {
            color: var(--pri);
            background: #fff;
            box-shadow: 0 1px 4px rgba(17, 24, 39, .12);
        }

        .mdk .count {
            background: var(--line);
            color: var(--ink-2);
            font-size: .72rem;
            padding: .05rem .5rem;
            border-radius: 999px;
        }

        .mdk .nav-link.active .count {
            background: var(--pri-soft);
            color: var(--pri);
        }

        .mdk .section-title {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .95rem;
            color: var(--ink);
            font-weight: 800;
            margin: 1.7rem 0 .9rem;
        }

        .mdk .section-title i {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            background: var(--pri-soft);
            color: var(--pri);
            font-size: .78rem;
        }

        .mdk .section-title:first-child {
            margin-top: 0;
        }

        .mdk .kv {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 2rem;
            margin: 0;
        }

        .mdk .kv>div {
            padding: .8rem 0;
            border-bottom: 1px solid var(--line);
        }

        .mdk .kv dt {
            font-size: .76rem;
            color: var(--mute);
            font-weight: 600;
            margin: 0 0 .2rem;
        }

        .mdk .kv dd {
            margin: 0;
            font-weight: 700;
            font-size: .92rem;
            color: var(--ink);
            word-break: break-word;
        }

        .mdk .note-box {
            background: var(--wash);
            border-radius: var(--r-sm);
            padding: .9rem 1.1rem;
            font-size: .86rem;
            color: var(--ink-2);
            white-space: pre-line;
            margin: 0;
        }

        .mdk .extend-box {
            background: var(--wash);
            border-radius: 16px;
            padding: 1.25rem;
        }

        .mdk-modal .modal-body {
            background: #fff;
        }

        .modal-dialog-scrollable {
            max-height: calc(100vh - 1.5rem);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 100%;
            overflow: hidden;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 220px);
        }

        .mdk .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        .mdk .drop {
            position: relative;
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .7rem .95rem;
            border: 1.5px dashed #d1d5db;
            border-radius: var(--r-sm);
            background: #fff;
            cursor: pointer;
            margin: 0;
            transition: border-color .15s, background .15s;
        }

        .mdk .drop:hover,
        .mdk .drop:focus-within {
            border-color: var(--pri);
            background: var(--pri-soft);
        }

        .mdk .drop input[type=file] {
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px;
            bottom: 0;
            left: 50%;
        }

        .mdk .drop>i {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: var(--pri-soft);
            color: var(--pri);
            flex: none;
        }

        .mdk .drop .drop-t {
            display: block;
            font-size: .85rem;
            font-weight: 700;
            color: var(--ink-2);
            word-break: break-all;
            line-height: 1.25;
        }

        .mdk .drop small {
            display: block;
            color: var(--mute);
            font-size: .74rem;
        }

        .mdk .drop.has-file {
            border-style: solid;
            border-color: var(--pri);
            background: var(--pri-soft);
        }

        .mdk .drop.has-file .drop-t {
            color: var(--pri-dark);
        }

        .mdk .seg {
            display: inline-flex;
            padding: 4px;
            background: #e5e7eb;
            border-radius: 999px;
            gap: 2px;
        }

        .mdk .seg button,
        .mdk .seg label span {
            border: 0;
            background: transparent;
            border-radius: 999px;
            padding: .42rem 1rem;
            font-size: .82rem;
            font-weight: 700;
            color: var(--mute);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .mdk .seg button.active,
        .mdk .seg input:checked+span {
            background: #fff;
            color: var(--pri);
            box-shadow: 0 1px 4px rgba(17, 24, 39, .14);
        }

        .mdk .seg label {
            margin: 0;
        }

        .mdk .seg input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .mdk .seg input:focus-visible+span {
            outline: 2px solid var(--pri);
        }

        .mdk .stepper {
            display: flex;
            align-items: center;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            background: #fff;
            overflow: hidden;
        }

        .mdk .stepper input {
            border: 0;
            text-align: center;
            font-weight: 700;
            min-width: 0;
            border-radius: 0;
            box-shadow: none !important;
            -moz-appearance: textfield;
        }

        .mdk .stepper input::-webkit-outer-spin-button,
        .mdk .stepper input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .mdk .stepper button {
            border: 0;
            background: var(--wash);
            width: 40px;
            align-self: stretch;
            color: var(--ink-2);
            font-weight: 800;
        }

        .mdk .stepper button:hover:not(:disabled) {
            background: var(--pri-soft);
            color: var(--pri);
        }

        .mdk .stepper button:disabled,
        .mdk .stepper input:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .mdk .money {
            display: flex;
            align-items: stretch;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            background: #fff;
            overflow: hidden;
        }

        .mdk .money:focus-within {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .18);
        }

        .mdk .money span {
            display: grid;
            place-items: center;
            padding: 0 .85rem;
            background: var(--amber-soft);
            color: var(--amber);
            font-weight: 800;
            font-size: .82rem;
        }

        .mdk .money input {
            border: 0;
            border-radius: 0;
            box-shadow: none !important;
            font-weight: 700;
        }

        .mdk .total-card {
            border-radius: var(--r-sm);
            background: #312e81;
            color: #fff;
            padding: .75rem 1.1rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .mdk .total-card .tl {
            font-size: .74rem;
            color: rgba(255, 255, 255, .7);
            font-weight: 600;
        }

        .mdk .total-card .ta {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.15;
        }

        .mdk .total-card .tf {
            font-size: .76rem;
            color: #c7d2fe;
        }

        .mdk .kp {
            background: #fff;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            overflow: hidden;
        }

        .mdk .kp-selected {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            padding: .7rem .85rem;
            background: var(--pri-soft);
            min-height: 50px;
            align-items: center;
        }

        .mdk .kp-empty {
            color: var(--mute);
            font-size: .82rem;
        }

        .mdk .chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .28rem .7rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            background: #fff;
            color: var(--ink-2);
            border: 1px solid var(--line);
            max-width: 100%;
        }

        .mdk .chip small {
            color: var(--mute);
            font-weight: 500;
            font-size: .74rem;
        }

        .mdk .chip-sel {
            border-color: #c7d2fe;
            color: var(--pri-dark);
            padding-right: .35rem;
        }

        .mdk .chip-x {
            border: 0;
            background: var(--pri-soft);
            color: var(--pri);
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: .65rem;
            padding: 0;
        }

        .mdk .chip-x:hover {
            background: var(--bad);
            color: #fff;
        }

        .mdk .kp-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem;
            padding: .65rem .85rem;
            border-bottom: 1px solid var(--line);
        }

        .mdk .kp-bar .search-wrap {
            flex: 1 1 180px;
            max-width: none;
        }

        .mdk .kp-bar .search-wrap .form-control {
            padding-top: .4rem;
            padding-bottom: .4rem;
        }

        .mdk .kp-link {
            border: 0;
            background: none;
            color: var(--pri);
            font-weight: 700;
            font-size: .8rem;
            padding: .3rem .5rem;
            border-radius: 8px;
        }

        .mdk .kp-link:hover {
            background: var(--pri-soft);
        }

        .mdk .kp-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: .55rem;
            padding: .75rem .85rem;
            max-height: 270px;
            overflow-y: auto;
        }

        .mdk .kp-card {
            position: relative;
            display: flex;
            gap: .7rem;
            padding: .7rem .8rem;
            border: 1.5px solid var(--line);
            border-radius: 12px;
            cursor: pointer;
            background: #fff;
            transition: border-color .12s, background .12s;
        }

        .mdk .kp-card:hover {
            border-color: #a5b4fc;
        }

        .mdk .kp-card:focus-visible {
            outline: 2px solid var(--pri);
            outline-offset: 1px;
        }

        .mdk .kp-card.is-on {
            border-color: var(--pri);
            background: var(--pri-soft);
        }

        .mdk .kp-check {
            width: 22px;
            height: 22px;
            border-radius: 7px;
            border: 1.5px solid #d1d5db;
            display: grid;
            place-items: center;
            font-size: .65rem;
            color: transparent;
            flex: none;
            margin-top: 2px;
            background: #fff;
        }

        .mdk .kp-card.is-on .kp-check {
            background: var(--pri);
            border-color: var(--pri);
            color: #fff;
        }

        .mdk .kp-body {
            min-width: 0;
        }

        .mdk .kp-t {
            font-weight: 700;
            font-size: .84rem;
            color: var(--ink);
            line-height: 1.25;
        }

        .mdk .kp-c {
            font-size: .78rem;
            color: var(--mute);
            margin-top: .1rem;
        }

        .mdk .kp-m {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem .75rem;
            margin-top: .4rem;
            font-size: .74rem;
            color: var(--ink-2);
            align-items: center;
        }

        .mdk .kp-m i {
            color: var(--faint);
            margin-right: .25rem;
        }

        .mdk .kp-none {
            grid-column: 1/-1;
            padding: 1.2rem;
            text-align: center;
            color: var(--faint);
            font-size: .84rem;
        }

        .mdk .pay {
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) auto auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--line);
        }

        .mdk .pay:first-child {
            padding-top: .2rem;
        }

        .mdk .pay-date {
            text-align: center;
            background: var(--amber-soft);
            color: var(--amber);
            border-radius: 14px;
            padding: .5rem 0;
            line-height: 1.1;
        }

        .mdk .pay-date b {
            display: block;
            font-size: 1.35rem;
            font-weight: 800;
        }

        .mdk .pay-date span {
            font-size: .7rem;
            font-weight: 700;
        }

        .mdk .pay-title {
            font-weight: 800;
            font-size: .94rem;
            color: var(--ink);
        }

        .mdk .pay-note {
            font-size: .78rem;
            color: var(--mute);
            margin-top: .1rem;
        }

        .mdk .pay-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .5rem;
        }

        .mdk .pay-calc {
            text-align: right;
        }

        .mdk .pay-amount {
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--ink);
            white-space: nowrap;
        }

        .mdk .pay-formula {
            font-size: .76rem;
            color: var(--mute);
            white-space: nowrap;
        }

        .mdk .pay-act {
            display: flex;
            gap: .35rem;
            align-items: center;
        }

        .mdk .pay-sum {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            padding: .9rem 1.2rem;
            border-radius: var(--r-sm);
            background: #312e81;
            color: #fff;
        }

        .mdk .pay-sum span {
            font-size: .85rem;
            color: rgba(255, 255, 255, .75);
            font-weight: 600;
        }

        .mdk .pay-sum b {
            font-size: 1.3rem;
            letter-spacing: -.02em;
        }

        .mdk .rkm-row {
            cursor: pointer;
            user-select: none;
        }

        .mdk table.tbl tbody tr.rkm-row.is-selected>td {
            background: var(--pri-soft);
        }

        .mdk table.tbl tbody tr.rkm-row.is-selected>td:first-child {
            box-shadow: inset 3px 0 0 var(--pri);
        }

        .mdk .rkm-row .chk {
            color: var(--faint);
            font-size: 1.05rem;
        }

        .mdk .rkm-row.is-selected .chk {
            color: var(--pri);
        }

        .mdk table.tbl tbody tr.rkm-row.is-added {
            opacity: .55;
            cursor: not-allowed;
        }

        .mdk .rkm-pick {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
            padding: .55rem .8rem;
            background: #fff;
            border: 1.5px dashed #d1d5db;
            border-radius: var(--r-sm);
        }

        .mdk .rkm-pick-label {
            flex: 1 1 200px;
            min-width: 0;
            word-break: break-word;
        }

        .mdk .switch-chip {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .45rem .9rem;
            border-radius: 999px;
            background: var(--wash);
            margin: 0;
            font-size: .82rem;
            font-weight: 600;
            color: var(--ink-2);
            cursor: pointer;
        }

        .mdk .switch-chip .form-check-input {
            margin: 0;
            float: none;
        }

        .mdk .xgrid {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .mdk .xchip {
            position: relative;
            margin: 0;
        }

        .mdk .xchip input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .mdk .xchip span {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .42rem .95rem;
            border-radius: 999px;
            border: 1.5px solid var(--line);
            font-size: .82rem;
            font-weight: 600;
            color: var(--mute);
            cursor: pointer;
            background: #fff;
        }

        .mdk .xchip span::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--line);
        }

        .mdk .xchip input:checked+span {
            border-color: var(--pri);
            background: var(--pri-soft);
            color: var(--pri-dark);
        }

        .mdk .xchip input:checked+span::before {
            background: var(--pri);
        }

        .mdk .xchip input:focus-visible+span {
            outline: 2px solid var(--pri);
            outline-offset: 1px;
        }

        @media (max-width:991px) {
            .mdk .summary {
                grid-template-columns: 1fr 1fr;
            }

            .mdk .stat:nth-child(2) {
                border-right: 0;
            }

            .mdk .stat:nth-child(-n+2) {
                border-bottom: 1px solid var(--line);
            }

            .mdk .kv {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width:767.98px) {
            .mdk .summary {
                grid-template-columns: 1fr;
            }

            .mdk .stat {
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .mdk .stat:last-child {
                border-bottom: 0;
            }

            .mdk table.tbl td,
            .mdk table.tbl th {
                padding: .5rem .4rem;
                font-size: .8rem;
            }

            .mdk .person {
                min-width: 140px;
            }

            .mdk .pay {
                grid-template-columns: 56px minmax(0, 1fr);
            }

            .mdk .pay-calc {
                text-align: left;
                grid-column: 2;
            }

            .mdk .pay-act {
                grid-column: 2;
            }
        }

        @media (prefers-reduced-motion:reduce) {
            .mdk .summary.is-loading .n {
                animation: none;
            }

            .mdk * {
                transition: none !important;
            }
        }
    </style>
@endpush

@section('content_HR')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css"
        integrity="sha512-QeR2VH+lsBE5LSAe1Q5EnTBbe7XTBubt8dG93Y7gidSgdMCr8nVqKcfKAMyN96SV8KDbZVTDXChatu5G2KQGzg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <div class="container-fluid px-4 py-4">
        <div class="mdk">

            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h1 class="page-title"><i class="fa-solid fa-chalkboard-user me-2" style="color:var(--pri)"></i>Data
                        Instruktur OL</h1>
                    <p class="page-sub">Data instruktur online, riwayat mengajar, pembayaran, silabus, dan materi.</p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-line btn-sm" id="btnExportList"><i
                            class="fa-solid fa-file-export me-1"></i>Export</button>
                    <button type="button" class="btn btn-line btn-sm" id="btnMigrasi"><i
                            class="fa-solid fa-right-left me-1"></i>Migrasi dari karyawan</button>
                    <button type="button" class="btn btn-brand btn-sm" id="btnTambah"><i
                            class="fa-solid fa-plus me-1"></i>Tambah instruktur</button>
                </div>
            </div>

            <div class="panel summary is-loading">
                <div class="stat">
                    <span class="stat-ico t-pri"><i class="fa-solid fa-users"></i></span>
                    <div>
                        <div class="n" id="sumTotal">0</div>
                        <div class="l">Instruktur terdaftar</div>
                    </div>
                </div>
                <div class="stat">
                    <span class="stat-ico t-amber"><i class="fa-regular fa-file-lines"></i></span>
                    <div>
                        <div class="n" id="sumCv">0</div>
                        <div class="l">CV belum diunggah</div>
                    </div>
                </div>
                <div class="stat">
                    <span class="stat-ico t-bad"><i class="fa-regular fa-id-card"></i></span>
                    <div>
                        <div class="n" id="sumKtp">0</div>
                        <div class="l">KTP belum diunggah</div>
                    </div>
                </div>
                <div class="stat">
                    <span class="stat-ico t-violet"><i class="fa-solid fa-hashtag"></i></span>
                    <div>
                        <div class="n" id="sumKode">0</div>
                        <div class="l">Kode karyawan kosong</div>
                    </div>
                </div>
            </div>

            <div class="panel mt-4">
                <div class="panel-head">
                    <h2><i class="fa-solid fa-table-list"></i>Daftar instruktur</h2>
                    <button type="button" class="btn btn-line btn-sm" id="btnReset"><i
                            class="fa-solid fa-rotate-left me-1"></i>Reset pencarian</button>
                </div>
                <form class="filters" id="formCari" onsubmit="return false">
                    <div class="search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="q" class="form-control"
                            placeholder="Cari kode, nama, core, atau no. HP" aria-label="Cari instruktur">
                    </div>
                </form>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table align-middle w-100 mb-0 tbl" id="tabelInstruktur">
                            <thead>
                                <tr>
                                    <th>Instruktur</th>
                                    <th>Core</th>
                                    <th>No. HP</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">CV</th>
                                    <th class="text-center">KTP</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-foot">
                    <small class="sub" id="paginationInfo"></small>
                    <div id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade mdk" id="modalTambah" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" id="formTambah" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h2><i class="fa-solid fa-user-plus"></i>Tambah instruktur OL</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger ol-errors d-none"></div>
                    <div class="section-title"><i class="fa-regular fa-id-card"></i>Data instruktur</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kode karyawan</label>
                            <input type="text" name="kode_karyawan" class="form-control" maxlength="20"
                                placeholder="Contoh: SP">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">Core</label>
                            <input type="text" name="core" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="section-title"><i class="fa-regular fa-folder-open"></i>Dokumen</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">CV</label>
                            <label class="drop" data-empty="Pilih file CV">
                                <input type="file" name="cv" accept=".pdf,.doc,.docx">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span><span class="drop-t">Pilih file CV</span><small>PDF, DOC, DOCX, maks. 10
                                        MB</small></span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">KTP</label>
                            <label class="drop" data-empty="Pilih file KTP">
                                <input type="file" name="ktp" accept=".pdf,.jpg,.jpeg,.png">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span><span class="drop-t">Pilih file KTP</span><small>PDF, JPG, PNG, maks. 10
                                        MB</small></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-brand">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade mdk" id="modalEdit" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" id="formEdit" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h2><i class="fa-solid fa-user-pen"></i>Ubah data instruktur</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger ol-errors d-none"></div>
                    <div class="section-title"><i class="fa-regular fa-id-card"></i>Data instruktur</div>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kode karyawan</label>
                            <input type="text" name="kode_karyawan" id="editKode" class="form-control"
                                maxlength="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">Nama</label>
                            <input type="text" name="nama" id="editNama" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">Core</label>
                            <input type="text" name="core" id="editCore" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label ol-req">No. HP</label>
                            <input type="text" name="no_hp" id="editHp" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="editKet" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="section-title"><i class="fa-regular fa-folder-open"></i>Dokumen</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ganti atau unggah CV</label>
                            <label class="drop" data-empty="Pilih file CV">
                                <input type="file" name="cv" accept=".pdf,.doc,.docx">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span><span class="drop-t">Pilih file CV</span><small>PDF, DOC, DOCX, maks. 10
                                        MB</small></span>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ganti atau unggah KTP</label>
                            <label class="drop" data-empty="Pilih file KTP">
                                <input type="file" name="ktp" accept=".pdf,.jpg,.jpeg,.png">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span><span class="drop-t">Pilih file KTP</span><small>PDF, JPG, PNG, maks. 10
                                        MB</small></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-brand">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade mdk" id="modalMigrasi" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2><i class="fa-solid fa-right-left"></i>Migrasi data OL dari tabel karyawan</h2>
                        <div class="sub mt-1">Pilih karyawan OL yang akan dimasukkan ke Data Instruktur OL.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none" id="migrasiErrors"></div>
                    <div class="alert alert-success d-none" id="migrasiOk"></div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 tbl">
                            <thead>
                                <tr>
                                    <th style="width:40px"><input type="checkbox" class="form-check-input"
                                            id="migrasiAll" aria-label="Pilih semua"></th>
                                    <th>Nama</th>
                                    <th>Jabatan</th>
                                    <th>No. HP</th>
                                    <th style="width:280px">Core <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody id="migrasiBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="sub me-auto"><span id="migrasiCount">0</span> dipilih</small>
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-brand" id="btnProsesMigrasi">Migrasi yang dipilih</button>
                </div>
            </div>
        </div>
    </div>

    @php
        $dokumen = [
            'silabus' => ['Silabus', 'fa-regular fa-file-powerpoint'],
            'materi' => ['Materi', 'fa-solid fa-book-open'],
        ];
    @endphp

    <div class="modal fade mdk mdk-modal" id="modalInstruktur" tabindex="-1" data-bs-backdrop="static"
        aria-labelledby="dNama" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="profile-head">
                    <div class="avatar" id="dAvatar"></div>
                    <div class="flex-grow-1">
                        <h2 id="dNama"></h2>
                        <div class="sub" id="dSub"></div>
                        <div class="mt-2 d-flex gap-2 flex-wrap" id="dTags"></div>
                    </div>
                    <div class="d-flex gap-2 align-self-start">
                        <button type="button" class="btn btn-sm btn-line btn-edit" id="dEdit"><i
                                class="fa-solid fa-pen me-2"></i>Ubah</button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#tabProfil" type="button" role="tab"><i
                                class="fa-regular fa-id-card"></i>Profil</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#paneRiwayat"
                            type="button" role="tab"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat mengajar
                            <span class="count" id="count-riwayat">0</span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panePembayaran"
                            type="button" role="tab"><i class="fa-solid fa-money-bill-wave"></i>Riwayat pembayaran
                            <span class="count" id="count-pembayaran">0</span></button></li>
                    @foreach ($dokumen as $j => [$label, $ico])
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#pane{{ ucfirst($j) }}" type="button" role="tab"><i
                                    class="{{ $ico }}"></i>{{ $label }} <span class="count"
                                    id="count-{{ $j }}">0</span></button></li>
                    @endforeach
                </ul>

                <div class="modal-body p-4">
                    <div class="tab-content">

                        <div class="tab-pane fade show active" id="tabProfil" role="tabpanel">
                            <div class="section-title"><i class="fa-solid fa-user"></i>Biodata</div>
                            <dl class="kv" id="dProfil"></dl>
                            <div class="section-title"><i class="fa-regular fa-note-sticky"></i>Keterangan</div>
                            <p class="note-box" id="dKet"></p>
                        </div>

                        <div class="tab-pane fade" id="paneRiwayat" role="tabpanel">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div class="sub">Kelas dipilih manual dari data RKM.</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-brand" id="btnPilihRiwayat"><i
                                            class="fa-solid fa-plus me-1"></i>Tambah dari RKM</button>
                                    <button type="button" class="btn btn-sm btn-line" id="btnExportRiwayat"><i
                                            class="fa-solid fa-file-export me-1"></i>Export riwayat</button>
                                </div>
                            </div>
                            <div id="riwayatList"></div>
                        </div>

                        <div class="tab-pane fade" id="panePembayaran" role="tabpanel">
                            <div id="pembayaranList"></div>

                            <div class="section-title"><i class="fa-solid fa-pen"></i><span id="bayarFormTitle">Tambah
                                    pembayaran</span></div>
                            <form id="formBayar" class="extend-box" enctype="multipart/form-data">
                                <div class="alert alert-danger ol-errors d-none"></div>
                                <input type="hidden" name="kali_pax" id="bayarKali" value="0">
                                <div id="kpHidden"></div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Kelas yang dibayar <small
                                                class="text-muted fw-normal">(opsional, boleh lebih dari satu dan lintas
                                                perusahaan)</small></label>
                                        <div class="kp">
                                            <div class="kp-selected" id="kpSelected"></div>
                                            <div class="kp-bar">
                                                <div class="search-wrap">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                    <input type="search" id="kpSearch" class="form-control"
                                                        placeholder="Cari materi atau perusahaan" aria-label="Cari kelas">
                                                </div>
                                                <button type="button" class="kp-link" id="kpAll">Pilih
                                                    semua</button>
                                                <button type="button" class="kp-link" id="kpClear">Kosongkan</button>
                                                <span class="sub ms-auto"><b id="kpCount">0</b> dipilih</span>
                                            </div>
                                            <div class="kp-grid" id="kpGrid"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label ol-req">Judul pembayaran</label>
                                        <input type="text" name="judul" id="bayarJudul" class="form-control"
                                            maxlength="255" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label ol-req">Harga</label>
                                        <div class="money">
                                            <span>Rp</span>
                                            <input type="number" name="harga" id="bayarHarga" class="form-control"
                                                min="0" step="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Perhitungan</label>
                                        <div>
                                            <div class="seg" id="bayarMode">
                                                <button type="button" class="active" data-v="0">Tarif tetap</button>
                                                <button type="button" data-v="1">Kali pax</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Jumlah pax</label>
                                        <div class="stepper">
                                            <button type="button" id="paxMin" aria-label="Kurangi"
                                                disabled>&minus;</button>
                                            <input type="number" name="pax" id="bayarPax" class="form-control"
                                                min="1" step="1" disabled>
                                            <button type="button" id="paxPlus" aria-label="Tambah" disabled>+</button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label ol-req">Tanggal bayar</label>
                                        <input type="date" name="tanggal_bayar" id="bayarTanggal"
                                            class="form-control" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Catatan</label>
                                        <input type="text" name="keterangan" id="bayarKet" class="form-control"
                                            maxlength="500" placeholder="Opsional">
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label">Bukti pembayaran</label>
                                        <label class="drop" data-empty="Pilih file bukti">
                                            <input type="file" name="bukti" accept=".pdf,.jpg,.jpeg,.png">
                                            <i class="fa-solid fa-receipt"></i>
                                            <span><span class="drop-t">Pilih file bukti</span><small
                                                    id="bayarBuktiHint">PDF, JPG, PNG, maks. 10 MB</small></span>
                                        </label>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="total-card">
                                            <div class="tl">Total dibayarkan</div>
                                            <div class="ta" id="bayarTotal">Rp 0</div>
                                            <div class="tf" id="bayarFormula">Tarif tetap, tidak dikalikan pax</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-brand btn-sm" id="bayarSubmit">Simpan
                                        pembayaran</button>
                                    <button type="button" class="btn btn-line btn-sm d-none" id="btnCancelBayar">Batal
                                        edit</button>
                                </div>
                            </form>
                        </div>

                        @foreach ($dokumen as $j => [$label, $ico])
                            <div class="tab-pane fade" id="pane{{ ucfirst($j) }}" role="tabpanel">
                                <div id="{{ $j }}List"></div>

                                <div class="section-title"><i class="fa-solid fa-cloud-arrow-up"></i><span
                                        id="{{ $j }}FormTitle">Upload {{ strtolower($label) }}</span></div>
                                <form id="form{{ ucfirst($j) }}" class="extend-box dok-form"
                                    data-jenis="{{ $j }}" enctype="multipart/form-data">
                                    <div class="alert alert-danger ol-errors d-none"></div>
                                    <input type="hidden" name="rkm_id" value="">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Kelas dari RKM <small
                                                    class="text-muted fw-normal">(opsional, boleh dikosongkan jika
                                                    mengunggah sendiri)</small></label>
                                            <div class="rkm-pick">
                                                <span class="rkm-pick-label sub">Belum ada kelas dipilih</span>
                                                <button type="button" class="btn btn-line btn-sm btn-pilih-rkm"
                                                    data-jenis="{{ $j }}"><i
                                                        class="fa-solid fa-hand-pointer me-1"></i>Pilih dari RKM</button>
                                                <button type="button"
                                                    class="btn btn-line danger btn-sm btn-lepas-rkm d-none"
                                                    data-jenis="{{ $j }}" title="Lepas kelas"><i
                                                        class="fa-solid fa-xmark"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label ol-req">Judul {{ strtolower($label) }}</label>
                                            <input type="text" name="judul" class="form-control" maxlength="255"
                                                required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Keterangan</label>
                                            <input type="text" name="keterangan" class="form-control" maxlength="500"
                                                placeholder="Opsional">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label ol-req"
                                                id="{{ $j }}FileLabel">File</label>
                                            <label class="drop" data-empty="Pilih file">
                                                <input type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx"
                                                    required>
                                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                                <span><span class="drop-t">Pilih file</span><small>PDF, DOC, PPT, maks. 10
                                                        MB</small></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mt-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-brand btn-sm"
                                            id="{{ $j }}Submit">Upload</button>
                                        <button type="button" class="btn btn-line btn-sm d-none"
                                            id="btnCancel{{ ucfirst($j) }}">Batal edit</button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade mdk" id="modalPilih" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2><i class="fa-solid fa-table-list"></i><span id="pilihTitle">Pilih kelas dari RKM</span></h2>
                        <div class="sub mt-1" id="pilihHint"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none" id="pilihErrors"></div>

                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-md-4">
                            <div class="search-wrap">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="search" id="pilihQ" class="form-control"
                                    placeholder="Cari materi atau perusahaan" aria-label="Cari RKM">
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <select id="pilihTahun" class="form-select pill" aria-label="Filter tahun">
                                <option value="">Semua tahun</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <select id="pilihMetode" class="form-select pill" aria-label="Filter metode kelas">
                                <option value="">Semua metode</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="switch-chip form-switch" for="pilihMilik">
                                <input type="checkbox" class="form-check-input" id="pilihMilik" role="switch">
                                Hanya kelas instruktur ini
                            </label>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 tbl" id="tabelPilih">
                            <thead>
                                <tr>
                                    <th style="width:40px"></th>
                                    <th>Nama materi</th>
                                    <th>Perusahaan</th>
                                    <th>Periode</th>
                                    <th>Metode kelas</th>
                                    <th class="text-center">Pax</th>
                                    <th>Peran instruktur</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="pilihBody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                        <small class="sub" id="pilihInfo"></small>
                        <div id="pilihPager"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="sub me-auto"><span id="pilihCount">0</span> dipilih</small>
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-brand" id="btnPilihOk">Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade mdk" id="modalExport" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fa-solid fa-file-export"></i><span id="exportTitle">Export data</span></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none" id="exportError">Pilih minimal 1 kolom untuk diexport.</div>
                    <div class="mb-4">
                        <label class="form-label d-block">Format file</label>
                        <div class="seg">
                            <label><input type="radio" name="exportFormat" id="fmtPdf" value="pdf"
                                    checked><span><i class="fa-regular fa-file-pdf"></i>PDF</span></label>
                            <label><input type="radio" name="exportFormat" id="fmtXlsx" value="xlsx"><span><i
                                        class="fa-regular fa-file-excel"></i>Excel</span></label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Kolom yang diexport</label>
                        <button type="button" class="kp-link" id="exportToggleAll"
                            style="border:0;background:none;color:var(--pri);font-weight:700;font-size:.8rem">Pilih atau
                            kosongkan semua</button>
                    </div>
                    <div class="xgrid" id="exportColumns"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-line" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-brand" id="btnDoExport">Export</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const KOLOM = {
                list: @json($kolomList),
                riwayat: @json($kolomRiwayat)
            };
            const ROUTES = {
                data: "{{ route('HR.instruktur-ol.data') }}",
                store: "{{ route('HR.instruktur-ol.store') }}",
                update: "{{ route('HR.instruktur-ol.update', '__ID__') }}",
                destroy: "{{ route('HR.instruktur-ol.destroy', '__ID__') }}",
                karyawanOl: "{{ route('HR.instruktur-ol.karyawan-ol') }}",
                migrasi: "{{ route('HR.instruktur-ol.migrasi-karyawan') }}",
                riwayat: "{{ route('HR.instruktur-ol.riwayat', '__ID__') }}",
                riwayatStore: "{{ route('HR.instruktur-ol.riwayat.store', '__ID__') }}",
                riwayatDestroy: "{{ route('HR.instruktur-ol.riwayat.destroy', '__ID__') }}",
                riwayatExport: "{{ route('HR.instruktur-ol.riwayat.export', '__ID__') }}",
                rkmPilihan: "{{ route('HR.instruktur-ol.rkm-pilihan', '__ID__') }}",
                export: "{{ route('HR.instruktur-ol.export') }}",
                pembayaran: "{{ route('HR.instruktur-ol.pembayaran.index', '__ID__') }}",
                pembayaranStore: "{{ route('HR.instruktur-ol.pembayaran.store', '__ID__') }}",
                pembayaranUpdate: "{{ route('HR.instruktur-ol.pembayaran.update', '__ID__') }}",
            };
            const DOK = {
                silabus: {
                    label: 'Silabus',
                    list: "{{ route('HR.instruktur-ol.silabus.index', '__ID__') }}",
                    store: "{{ route('HR.instruktur-ol.silabus.store', '__ID__') }}",
                    update: "{{ route('HR.instruktur-ol.silabus.update', '__ID__') }}"
                },
                materi: {
                    label: 'Materi',
                    list: "{{ route('HR.instruktur-ol.materi.index', '__ID__') }}",
                    store: "{{ route('HR.instruktur-ol.materi.store', '__ID__') }}",
                    update: "{{ route('HR.instruktur-ol.materi.update', '__ID__') }}"
                }
            };
            const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            let currentQ = '';
            let currentPage = 1;
            let currentId = null;
            let currentData = null;
            let editId = null;
            const INSTR = {};

            const modal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
            const cap = s => s.charAt(0).toUpperCase() + s.slice(1);

            function bukaModal(id) {
                const terbuka = $('.modal.show').not('#' + id)[0];
                if (terbuka) {
                    terbuka.addEventListener('hidden.bs.modal', () => modal(id).show(), {
                        once: true
                    });
                    bootstrap.Modal.getOrCreateInstance(terbuka).hide();
                } else {
                    modal(id).show();
                }
            }

            function esc(s) {
                return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            function url(tpl, map) {
                Object.keys(map).forEach(k => {
                    tpl = tpl.split(k).join(encodeURIComponent(map[k]));
                });
                return tpl;
            }

            function fmtDate(s) {
                return s ? String(s).substring(0, 10).split('-').reverse().join('-') : '-';
            }

            function rp(n) {
                return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
            }

            function initials(n) {
                const p = String(n || '').trim().split(/\s+/);
                return (((p[0] || '')[0] || '?') + (p[1] ? p[1][0] : '')).toUpperCase();
            }

            const avClass = id => 'av-' + (Number(id) || 0) % 4;

            function syncDrop($d) {
                const f = $d.find('input[type=file]')[0];
                const nama = f && f.files && f.files.length ? f.files[0].name : '';
                $d.toggleClass('has-file', !!nama).find('.drop-t').text(nama || $d.attr('data-empty'));
            }

            function syncAllDrops() {
                $('.drop').each(function() {
                    syncDrop($(this));
                });
            }

            $(document).on('change', '.drop input[type=file]', function() {
                syncDrop($(this).closest('.drop'));
            });

            $(document).on('reset', 'form', function() {
                setTimeout(syncAllDrops, 0);
            });

            function notifSukses(msg) {
                if (typeof Swal === 'undefined') return;
                Swal.fire({
                    icon: 'success',
                    title: msg,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }

            function notifGagal(msg) {
                if (typeof Swal === 'undefined') {
                    alert(msg);
                    return;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg || 'Terjadi kesalahan, coba lagi.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }

            function konfirmasi(teks, cb, tombol) {
                if (typeof Swal === 'undefined') {
                    if (confirm(teks)) cb();
                    return;
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Konfirmasi',
                    text: teks,
                    showCancelButton: true,
                    confirmButtonText: tombol || 'Ya, lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#4f46e5'
                }).then(r => {
                    if (r.isConfirmed) cb();
                });
            }

            function failMsg(xhr, fallback) {
                return (xhr.responseJSON && xhr.responseJSON.message) || fallback;
            }

            function showErrors($form, xhr) {
                let msgs = [];
                const j = xhr.responseJSON;
                if (j && j.errors) {
                    Object.values(j.errors).forEach(a => {
                        msgs = msgs.concat(a);
                    });
                } else {
                    msgs.push((j && j.message) || 'Terjadi kesalahan pada server.');
                }
                $form.find('.ol-errors').first().removeClass('d-none')
                    .html('<ul class="mb-0">' + msgs.map(m => `<li>${esc(m)}</li>`).join('') + '</ul>');
            }

            function ajaxForm($form, targetUrl, method) {
                const fd = new FormData($form[0]);
                if (method && method !== 'POST') fd.append('_method', method);
                $form.find('.ol-errors').addClass('d-none').empty();
                const $btn = $form.find('[type=submit]').prop('disabled', true);
                return $.ajax({
                        url: targetUrl,
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .fail(xhr => showErrors($form, xhr))
                    .always(() => $btn.prop('disabled', false));
            }

            function sendDelete(targetUrl) {
                return $.ajax({
                    url: targetUrl,
                    type: 'POST',
                    data: {
                        _method: 'DELETE'
                    }
                });
            }

            function sendJson(targetUrl, payload) {
                return $.ajax({
                    url: targetUrl,
                    type: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json'
                });
            }

            function fileTag(u) {
                return u ?
                    `<a href="${esc(u)}" target="_blank" rel="noopener" class="tag tag-done"><i class="fa-solid fa-circle-check"></i>Lihat</a>` :
                    '<span class="tag tag-muted">Belum</span>';
            }

            function pagerHtml(res) {
                if (res.last_page <= 1 || !res.links) return '';
                const items = res.links.map(l => {
                    const label = String(l.label).replace('Previous', 'Sebelumnya').replace('Next',
                        'Berikutnya');
                    const page = l.url ? new URL(l.url).searchParams.get('page') : '';
                    return `<li class="page-item ${l.active ? 'active' : ''} ${l.url ? '' : 'disabled'}"><a class="page-link" href="#" data-page="${page}">${label}</a></li>`;
                }).join('');
                return `<ul class="pagination pagination-sm mb-0">${items}</ul>`;
            }

            const emptyRow = (cols, html) => `<tr><td colspan="${cols}" class="empty">${html}</td></tr>`;

            function loadTable(page) {
                currentPage = page || currentPage || 1;
                $('#tabelInstruktur').addClass('is-loading');
                $('.summary').addClass('is-loading');
                $.get(ROUTES.data, {
                    q: currentQ,
                    page: currentPage
                }).done(res => {
                    if (!res.data.length && res.current_page > 1) {
                        loadTable(res.current_page - 1);
                        return;
                    }
                    renderSummary(res.summary);
                    renderTable(res);
                    renderPagination(res);
                }).fail(() => {
                    $('#tableBody').html(emptyRow(7,
                        '<span class="text-danger">Data instruktur gagal dimuat. Coba muat ulang halaman.</span>'
                        ));
                    $('#paginationContainer').empty();
                    $('#paginationInfo').empty();
                }).always(() => {
                    $('#tabelInstruktur').removeClass('is-loading');
                    $('.summary').removeClass('is-loading');
                });
            }

            function renderSummary(s) {
                if (!s) return;
                $('#sumTotal').text(s.total);
                $('#sumCv').text(s.tanpa_cv);
                $('#sumKtp').text(s.tanpa_ktp);
                $('#sumKode').text(s.tanpa_kode);
            }

            function renderTable(res) {
                if (!res.data.length) {
                    $('#tableBody').html(emptyRow(7,
                        '<i class="fa-regular fa-folder-open big"></i>Belum ada data instruktur.'));
                    return;
                }
                let html = '';
                res.data.forEach(i => {
                    INSTR[i.id] = i;
                    html += `<tr>
                        <td><div class="person"><span class="avatar ${avClass(i.id)}">${esc(initials(i.nama))}</span><div><div class="nm">${esc(i.nama)}</div><div class="nip">${i.kode_karyawan ? 'Kode ' + esc(i.kode_karyawan) : 'Kode belum diisi'}</div></div></div></td>
                        <td>${esc(i.core)}</td>
                        <td>${esc(i.no_hp)}</td>
                        <td class="ol-ket">${esc(i.keterangan || '-')}</td>
                        <td class="text-center">${fileTag(i.cv_url)}</td>
                        <td class="text-center">${fileTag(i.ktp_url)}</td>
                        <td class="act text-end"><div class="d-flex justify-content-end gap-1">
                            <button type="button" class="btn btn-sm btn-line btn-detail" data-id="${i.id}" title="Detail"><i class="fa-solid fa-eye"></i></button>
                            <button type="button" class="btn btn-sm btn-line btn-edit" data-id="${i.id}" title="Ubah"><i class="fa-solid fa-pen"></i></button>
                            <button type="button" class="btn btn-sm btn-line danger btn-hapus" data-id="${i.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                        </div></td>
                    </tr>`;
                });
                $('#tableBody').html(html);
            }

            function renderPagination(res) {
                if (!res.total) {
                    $('#paginationInfo').text('');
                    $('#paginationContainer').empty();
                    return;
                }
                $('#paginationInfo').text(`Menampilkan ${res.from} sampai ${res.to} dari ${res.total} instruktur`);
                $('#paginationContainer').html(pagerHtml(res));
            }

            loadTable(1);

            let jeda = null;
            $('#formCari [name=q]').on('input', function() {
                clearTimeout(jeda);
                jeda = setTimeout(() => {
                    currentQ = $.trim($(this).val());
                    loadTable(1);
                }, 400);
            });

            $('#btnReset').on('click', function() {
                currentQ = '';
                $('#formCari [name=q]').val('');
                loadTable(1);
            });

            $(document).on('click', '#paginationContainer a.page-link', function(e) {
                e.preventDefault();
                const page = $(this).attr('data-page');
                if (page) loadTable(parseInt(page, 10));
            });

            $('#btnTambah').on('click', function() {
                $('#formTambah')[0].reset();
                $('#formTambah .ol-errors').addClass('d-none').empty();
                syncAllDrops();
                modal('modalTambah').show();
            });

            $('#formTambah').on('submit', function(e) {
                e.preventDefault();
                ajaxForm($(this), ROUTES.store, 'POST').done(res => {
                    modal('modalTambah').hide();
                    loadTable(1);
                    notifSukses(res.message || 'Instruktur berhasil ditambahkan.');
                });
            });

            $(document).on('click', '.btn-edit', function() {
                const i = INSTR[$(this).attr('data-id')];
                if (!i) return;
                editId = i.id;
                $('#editKode').val(i.kode_karyawan || '');
                $('#editNama').val(i.nama);
                $('#editCore').val(i.core);
                $('#editHp').val(i.no_hp);
                $('#editKet').val(i.keterangan || '');
                $('#formEdit input[type=file]').val('');
                $('#formEdit .ol-errors').addClass('d-none').empty();
                syncAllDrops();
                bukaModal('modalEdit');
            });

            $('#formEdit').on('submit', function(e) {
                e.preventDefault();
                ajaxForm($(this), url(ROUTES.update, {
                    '__ID__': editId
                }), 'PUT').done(res => {
                    modal('modalEdit').hide();
                    loadTable();
                    notifSukses(res.message || 'Data berhasil diperbarui.');
                });
            });

            $(document).on('click', '.btn-hapus', function() {
                const i = INSTR[$(this).attr('data-id')];
                if (!i) return;
                konfirmasi(`Hapus instruktur "${i.nama}"?`, () => {
                    sendDelete(url(ROUTES.destroy, {
                            '__ID__': i.id
                        }))
                        .done(res => {
                            loadTable();
                            notifSukses(res.message || 'Data berhasil dihapus.');
                        })
                        .fail(xhr => notifGagal(failMsg(xhr, 'Gagal menghapus data.')));
                }, 'Ya, hapus');
            });

            function updateMigrasiCount() {
                $('#migrasiCount').text($('#migrasiBody .migrasi-check:checked').length);
            }

            function loadKaryawanOl() {
                $('#migrasiBody').html(emptyRow(5, '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...')
                    .replace('class="empty"', 'class="empty loading"'));
                $('#migrasiAll').prop('checked', false);
                updateMigrasiCount();
                $.get(ROUTES.karyawanOl).done(res => {
                    if (!res.data.length) {
                        $('#migrasiBody').html(emptyRow(5,
                            '<i class="fa-regular fa-circle-check big"></i>Tidak ada karyawan OL yang perlu dimigrasi.'
                            ));
                        return;
                    }
                    $('#migrasiBody').html(res.data.map(k => `<tr data-id="${esc(k.id)}">
                        <td><input type="checkbox" class="form-check-input migrasi-check"></td>
                        <td class="fw-semibold">${esc(k.nama)}</td>
                        <td>${esc(k.jabatan || '-')}</td>
                        <td>${esc(k.no_hp || '-')}</td>
                        <td><input type="text" class="form-control form-control-sm migrasi-core" placeholder="Isi core"></td>
                    </tr>`).join(''));
                }).fail(() => {
                    $('#migrasiBody').html(emptyRow(5,
                        '<span class="text-danger">Gagal memuat data.</span>'));
                });
            }

            $('#btnMigrasi').on('click', function() {
                $('#migrasiErrors, #migrasiOk').addClass('d-none').empty();
                modal('modalMigrasi').show();
                loadKaryawanOl();
            });

            $('#migrasiAll').on('change', function() {
                $('#migrasiBody .migrasi-check').prop('checked', this.checked);
                updateMigrasiCount();
            });

            $(document).on('change', '.migrasi-check', updateMigrasiCount);

            $(document).on('input', '.migrasi-core', function() {
                if ($.trim($(this).val())) {
                    $(this).closest('tr').find('.migrasi-check').prop('checked', true);
                    updateMigrasiCount();
                }
            });

            $('#btnProsesMigrasi').on('click', function() {
                const items = [];
                let kosong = false;
                $('#migrasiBody tr').each(function() {
                    const $tr = $(this);
                    if (!$tr.find('.migrasi-check').is(':checked')) return;
                    const core = $.trim($tr.find('.migrasi-core').val());
                    if (!core) {
                        kosong = true;
                        $tr.find('.migrasi-core').addClass('is-invalid');
                        return;
                    }
                    $tr.find('.migrasi-core').removeClass('is-invalid');
                    items.push({
                        karyawan_id: $tr.attr('data-id'),
                        core: core
                    });
                });

                $('#migrasiErrors, #migrasiOk').addClass('d-none').empty();

                if (kosong) {
                    $('#migrasiErrors').removeClass('d-none').text(
                        'Core wajib diisi untuk setiap karyawan yang dipilih.');
                    return;
                }
                if (!items.length) {
                    $('#migrasiErrors').removeClass('d-none').text('Pilih minimal 1 karyawan.');
                    return;
                }

                konfirmasi(`Migrasi ${items.length} karyawan ke Data Instruktur OL?`, () => {
                    const $btn = $('#btnProsesMigrasi').prop('disabled', true);
                    sendJson(ROUTES.migrasi, {
                            items: items
                        })
                        .done(res => {
                            $('#migrasiOk').removeClass('d-none').text(res.message);
                            if (res.gagal && res.gagal.length) {
                                $('#migrasiErrors').removeClass('d-none')
                                    .html('<ul class="mb-0">' + res.gagal.map(m =>
                                        `<li>${esc(m)}</li>`).join('') + '</ul>');
                            }
                            loadKaryawanOl();
                            loadTable(1);
                        })
                        .fail(xhr => {
                            const j = xhr.responseJSON || {};
                            const msgs = j.errors ? [].concat(...Object.values(j.errors)) :
                                (j.gagal && j.gagal.length ? j.gagal : [j.message ||
                                    'Terjadi kesalahan pada server.'
                                ]);
                            $('#migrasiErrors').removeClass('d-none')
                                .html('<ul class="mb-0">' + msgs.map(m => `<li>${esc(m)}</li>`)
                                    .join('') + '</ul>');
                        })
                        .always(() => $btn.prop('disabled', false));
                }, 'Ya, migrasi');
            });

            function renderProfil(d) {
                $('#dAvatar').text(initials(d.nama));
                $('#dNama').text(d.nama);
                $('#dSub').text('Kode ' + (d.kode_karyawan || '-') + ' \u00b7 ' + (d.core || '-'));
                $('#dTags').html(
                    `<span class="tag ${d.cv_url ? 'tag-done' : 'tag-pending'}">CV ${d.cv_url ? 'tersedia' : 'belum ada'}</span>` +
                    `<span class="tag ${d.ktp_url ? 'tag-done' : 'tag-pending'}">KTP ${d.ktp_url ? 'tersedia' : 'belum ada'}</span>`
                );
                $('#dEdit').attr('data-id', d.id);

                const dok = (u, label) => u ?
                    `<a href="${esc(u)}" target="_blank" rel="noopener" class="btn btn-sm btn-line"><i class="fa-solid fa-eye me-1"></i>Lihat ${label}</a>` :
                    `<span class="tag tag-muted">${label} belum diunggah</span>`;

                $('#dProfil').html(`
                    <div><dt>Kode karyawan</dt><dd>${esc(d.kode_karyawan || '-')}</dd></div>
                    <div><dt>Nama</dt><dd>${esc(d.nama)}</dd></div>
                    <div><dt>Core</dt><dd>${esc(d.core)}</dd></div>
                    <div><dt>No. HP</dt><dd>${esc(d.no_hp)}</dd></div>
                    <div><dt>CV</dt><dd>${dok(d.cv_url, 'CV')}</dd></div>
                    <div><dt>KTP</dt><dd>${dok(d.ktp_url, 'KTP')}</dd></div>`);
                $('#dKet').text(d.keterangan || '-');
            }

            $(document).on('click', '.btn-detail', function(e) {
                e.preventDefault();
                const d = INSTR[$(this).attr('data-id')];
                if (!d) return;
                currentId = d.id;
                currentData = d;
                renderProfil(d);

                $('#riwayatList, #pembayaranList, #silabusList, #materiList').empty();
                $('#count-riwayat, #count-pembayaran, #count-silabus, #count-materi').text('0');
                $('#btnExportRiwayat').prop('disabled', true);
                rkmOptions = [];
                resetBayarForm();
                resetDok('silabus');
                resetDok('materi');

                bootstrap.Tab.getOrCreateInstance($('#modalInstruktur .nav-tabs .nav-link')[0]).show();
                modal('modalInstruktur').show();

                loadRiwayat();
                loadPembayaran();
                loadDok('silabus');
                loadDok('materi');
            });

            function loadRiwayat() {
                const id = currentId;
                $('#riwayatList').html(
                    '<div class="empty loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...</div>'
                    );
                $.get(url(ROUTES.riwayat, {
                        '__ID__': id
                    }))
                    .done(res => {
                        if (id === currentId) renderRiwayat(res);
                    })
                    .fail(() => {
                        $('#riwayatList').html(
                            '<div class="alert alert-danger mb-0">Gagal memuat riwayat mengajar.</div>');
                    });
            }

            function renderRiwayat(res) {
                const items = res.data || [];
                $('#count-riwayat').text(items.length);
                $('#btnExportRiwayat').prop('disabled', !items.length);

                if (!items.length) {
                    $('#riwayatList').html(
                        '<div class="empty"><i class="fa-regular fa-calendar-xmark big"></i>Belum ada riwayat mengajar. Klik "Tambah dari RKM" untuk memilih kelas.</div>'
                        );
                    return;
                }

                const rows = items.map((r, i) => `<tr>
                    <td>${i + 1}</td>
                    <td class="fw-semibold">${esc(r.materi)}</td>
                    <td>${esc(r.perusahaan)}</td>
                    <td class="text-nowrap">${fmtDate(r.tanggal_awal)} s/d ${fmtDate(r.tanggal_akhir)}</td>
                    <td>${esc(r.metode_kelas)}</td>
                    <td>${r.peran && r.peran !== '-' ? `<span class="tag tag-info">${esc(r.peran)}</span>` : '<span class="text-muted">-</span>'}</td>
                    <td class="text-center">${esc(r.pax)}</td>
                    <td class="act text-end"><button type="button" class="btn btn-sm btn-line danger btn-del-riwayat" data-id="${r.id}" title="Hapus dari riwayat"><i class="fa-solid fa-trash"></i></button></td>
                </tr>`).join('');

                $('#riwayatList').html(`<div class="table-responsive"><table class="table align-middle mb-0 tbl">
                    <thead><tr><th style="width:50px">No.</th><th>Nama materi</th><th>Perusahaan</th><th>Periode</th><th>Metode kelas</th><th>Peran</th><th style="width:70px" class="text-center">Pax</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>${rows}</tbody></table></div>`);
            }

            $(document).on('click', '.btn-del-riwayat', function() {
                const id = $(this).attr('data-id');
                konfirmasi('Hapus kelas ini dari riwayat mengajar? Data RKM tidak ikut terhapus.', () => {
                    sendDelete(url(ROUTES.riwayatDestroy, {
                            '__ID__': id
                        }))
                        .done(res => {
                            renderRiwayat(res);
                            loadPembayaran();
                            notifSukses(res.message || 'Riwayat dihapus.');
                        })
                        .fail(xhr => notifGagal(failMsg(xhr, 'Gagal menghapus riwayat.')));
                }, 'Ya, hapus');
            });

            const pk = {
                ctx: null,
                selected: new Map(),
                cache: {},
                added: new Set(),
                page: 1,
                optsLoaded: false
            };

            const rkmLabel = r => `${r.materi} \u2014 ${r.perusahaan} (${fmtDate(r.tanggal_awal)})`;

            function updatePilihCount() {
                $('#pilihCount').text(pk.selected.size);
            }

            function syncPilihRows() {
                $('#pilihBody tr.rkm-row').each(function() {
                    const on = pk.selected.has(String($(this).attr('data-id')));
                    $(this).toggleClass('is-selected', on)
                        .find('.chk').attr('class', 'chk fa-regular ' + (on ? 'fa-square-check' :
                            'fa-square'));
                });
            }

            function bukaPicker(ctx) {
                pk.ctx = ctx;
                pk.selected = new Map();
                pk.cache = {};
                pk.added = new Set();
                pk.page = 1;

                $('#pilihTitle').text(ctx.title);
                $('#pilihHint').text(ctx.multi ?
                    'Klik 2 kali pada baris untuk memilih atau membatalkan. Pilihan tetap tersimpan saat pindah halaman.' :
                    'Klik 2 kali pada baris untuk memilih kelas.');
                $('#btnPilihOk').text(ctx.multi ? 'Tambahkan yang dipilih' : 'Pakai kelas ini');
                $('#pilihQ').val('');
                $('#pilihTahun').val('');
                $('#pilihMetode').val('');
                $('#pilihMilik').prop('checked', false);
                $('#pilihErrors').addClass('d-none').empty();
                $('#pilihBody').empty();
                $('#pilihInfo').text('');
                $('#pilihPager').empty();
                updatePilihCount();

                document.getElementById('modalInstruktur').addEventListener('hidden.bs.modal', () => {
                    modal('modalPilih').show();
                    loadPicker(1);
                }, {
                    once: true
                });
                modal('modalInstruktur').hide();
            }

            $('#modalPilih').on('hidden.bs.modal', function() {
                if (currentId) modal('modalInstruktur').show();
            });

            function loadPicker(page) {
                pk.page = page || 1;
                $('#tabelPilih').addClass('is-loading');
                $.get(url(ROUTES.rkmPilihan, {
                    '__ID__': currentId
                }), {
                    q: $.trim($('#pilihQ').val()),
                    tahun: $('#pilihTahun').val(),
                    metode: $('#pilihMetode').val(),
                    milik: $('#pilihMilik').is(':checked') ? 1 : 0,
                    page: pk.page
                }).done(res => {
                    if (!pk.optsLoaded) {
                        $('#pilihTahun').append((res.tahun_options || []).map(y =>
                            `<option value="${esc(y)}">${esc(y)}</option>`).join(''));
                        $('#pilihMetode').append((res.metode_options || []).map(m =>
                            `<option value="${esc(m)}">${esc(m)}</option>`).join(''));
                        pk.optsLoaded = true;
                    }
                    pk.added = new Set(pk.ctx.multi ? (res.added || []).map(String) : []);
                    renderPicker(res);
                }).fail(() => {
                    $('#pilihBody').html(emptyRow(8,
                        '<span class="text-danger">Data RKM gagal dimuat.</span>'));
                    $('#pilihInfo').text('');
                    $('#pilihPager').empty();
                }).always(() => $('#tabelPilih').removeClass('is-loading'));
            }

            function renderPicker(res) {
                if (!res.data.length) {
                    $('#pilihBody').html(emptyRow(8,
                        '<i class="fa-regular fa-folder-open big"></i>Tidak ada RKM yang cocok.'));
                    $('#pilihInfo').text('');
                    $('#pilihPager').empty();
                    return;
                }

                $('#pilihBody').html(res.data.map(r => {
                    const id = String(r.rkm_id);
                    pk.cache[id] = r;
                    const added = pk.added.has(id);
                    const on = pk.selected.has(id);
                    return `<tr class="rkm-row ${added ? 'is-added' : ''} ${on ? 'is-selected' : ''}" data-id="${esc(id)}">
                        <td>${added ? '' : `<i class="chk fa-regular ${on ? 'fa-square-check' : 'fa-square'}"></i>`}</td>
                        <td class="fw-semibold">${esc(r.materi)}</td>
                        <td>${esc(r.perusahaan)}</td>
                        <td class="text-nowrap">${fmtDate(r.tanggal_awal)} s/d ${fmtDate(r.tanggal_akhir)}</td>
                        <td>${esc(r.metode_kelas)}</td>
                        <td class="text-center">${esc(r.pax)}</td>
                        <td>${r.peran && r.peran !== '-' ? `<span class="tag tag-info">${esc(r.peran)}</span>` : '<span class="text-muted">-</span>'}</td>
                        <td>${added ? '<span class="tag tag-done">Sudah ditambahkan</span>' : ''}</td>
                    </tr>`;
                }).join(''));

                $('#pilihInfo').text(`Menampilkan ${res.from} sampai ${res.to} dari ${res.total} kelas`);
                $('#pilihPager').html(pagerHtml(res));
            }

            $(document).on('dblclick', '#pilihBody tr.rkm-row', function() {
                if ($(this).hasClass('is-added')) return;
                const id = String($(this).attr('data-id'));
                if (pk.selected.has(id)) {
                    pk.selected.delete(id);
                } else {
                    if (!pk.ctx.multi) pk.selected.clear();
                    pk.selected.set(id, pk.cache[id]);
                }
                if (window.getSelection) window.getSelection().removeAllRanges();
                $('#pilihErrors').addClass('d-none').empty();
                syncPilihRows();
                updatePilihCount();
            });

            $(document).on('click', '#pilihPager a.page-link', function(e) {
                e.preventDefault();
                const page = $(this).attr('data-page');
                if (page) loadPicker(parseInt(page, 10));
            });

            let jedaPilih = null;
            $('#pilihQ').on('input', function() {
                clearTimeout(jedaPilih);
                jedaPilih = setTimeout(() => loadPicker(1), 400);
            });
            $('#pilihTahun, #pilihMetode, #pilihMilik').on('change', () => loadPicker(1));

            $('#btnPilihOk').on('click', function() {
                if (!pk.selected.size) {
                    $('#pilihErrors').removeClass('d-none').text(
                        'Belum ada kelas dipilih. Klik 2 kali pada baris untuk memilih.');
                    return;
                }
                pk.ctx.onPick([...pk.selected.values()], $(this));
            });

            $('#btnPilihRiwayat').on('click', function() {
                bukaPicker({
                    title: 'Tambah riwayat mengajar dari RKM',
                    multi: true,
                    onPick: (items, $btn) => {
                        $btn.prop('disabled', true);
                        sendJson(url(ROUTES.riwayatStore, {
                                '__ID__': currentId
                            }), {
                                rkm_ids: items.map(r => r.rkm_id)
                            })
                            .done(res => {
                                modal('modalPilih').hide();
                                renderRiwayat(res);
                                loadPembayaran();
                                notifSukses(res.message || 'Riwayat ditambahkan.');
                            })
                            .fail(xhr => {
                                const j = xhr.responseJSON || {};
                                const msgs = j.errors ? [].concat(...Object.values(j
                                    .errors)) : [j.message ||
                                    'Terjadi kesalahan pada server.'
                                ];
                                $('#pilihErrors').removeClass('d-none')
                                    .html('<ul class="mb-0">' + msgs.map(m =>
                                        `<li>${esc(m)}</li>`).join('') + '</ul>');
                            })
                            .always(() => $btn.prop('disabled', false));
                    }
                });
            });

            let editingBayarId = null;
            let pembayaranData = [];
            let rkmOptions = [];
            let bayarSel = new Set();
            let kpQuery = '';
            let autoJudul = '';

            const modeBayar = () => $('#bayarKali').val() === '1';

            function sumPax() {
                return rkmOptions
                    .filter(o => bayarSel.has(String(o.id)))
                    .reduce((a, o) => a + (parseInt(o.pax, 10) || 0), 0);
            }

            function hitungTotal() {
                const harga = parseInt($('#bayarHarga').val(), 10) || 0;
                const kali = modeBayar();
                const pax = parseInt($('#bayarPax').val(), 10) || 0;
                $('#bayarTotal').text(rp(kali ? harga * pax : harga));
                $('#bayarFormula').text(kali ? `${rp(harga)} \u00d7 ${pax} pax` :
                    'Tarif tetap, tidak dikalikan pax');
            }

            function setMode(on) {
                $('#bayarKali').val(on ? '1' : '0');
                $('#bayarMode button').each(function() {
                    $(this).toggleClass('active', ($(this).attr('data-v') === '1') === on);
                });
                $('#bayarPax, #paxMin, #paxPlus').prop('disabled', !on);
                if (on && !$('#bayarPax').val()) $('#bayarPax').val(sumPax() || '');
                hitungTotal();
            }

            function paintKp() {
                $('#kpHidden').html([...bayarSel].map(id =>
                    `<input type="hidden" name="rkm_ids[]" value="${esc(id)}">`).join(''));
                $('#kpCount').text(bayarSel.size);

                if (!bayarSel.size) {
                    $('#kpSelected').html(
                        '<span class="kp-empty">Belum ada kelas dipilih. Pembayaran akan tercatat sebagai pembayaran umum.</span>'
                        );
                } else {
                    $('#kpSelected').html([...bayarSel].map(id => {
                        const o = rkmOptions.find(x => String(x.id) === id);
                        return o ?
                            `<span class="chip chip-sel">${esc(o.materi)}<small>${esc(o.perusahaan)}</small><button type="button" class="chip-x" data-id="${esc(id)}" aria-label="Lepas kelas"><i class="fa-solid fa-xmark"></i></button></span>` :
                            '';
                    }).join(''));
                }

                if (!rkmOptions.length) {
                    $('#kpGrid').html(
                        '<div class="kp-none">Belum ada riwayat mengajar. Tambahkan dulu di tab Riwayat mengajar.</div>'
                        );
                    return;
                }

                const q = kpQuery.toLowerCase();
                const list = rkmOptions.filter(o => !q || (o.materi + ' ' + o.perusahaan).toLowerCase().includes(
                q));

                if (!list.length) {
                    $('#kpGrid').html('<div class="kp-none">Tidak ada kelas yang cocok dengan pencarian.</div>');
                    return;
                }

                $('#kpGrid').html(list.map(o => {
                    const on = bayarSel.has(String(o.id));
                    return `<div class="kp-card ${on ? 'is-on' : ''}" data-id="${esc(o.id)}" role="checkbox" aria-checked="${on}" tabindex="0">
                        <span class="kp-check"><i class="fa-solid fa-check"></i></span>
                        <div class="kp-body">
                            <div class="kp-t">${esc(o.materi)}</div>
                            <div class="kp-c">${esc(o.perusahaan)}</div>
                            <div class="kp-m">
                                <span><i class="fa-regular fa-calendar"></i>${fmtDate(o.tanggal_awal)} s/d ${fmtDate(o.tanggal_akhir)}</span>
                                <span><i class="fa-solid fa-user-group"></i>${esc(o.pax || 0)} pax</span>
                                ${o.peran && o.peran !== '-' ? `<span class="tag tag-info">${esc(o.peran)}</span>` : ''}
                            </div>
                        </div>
                    </div>`;
                }).join(''));
            }

            function onKpChange() {
                paintKp();
                if (!bayarSel.size) {
                    hitungTotal();
                    return;
                }
                if (modeBayar()) $('#bayarPax').val(sumPax() || '');

                const materi = [...new Set(rkmOptions.filter(o => bayarSel.has(String(o.id))).map(o => o.materi))];
                const judul = ('Honor ' + materi.join(', ')).substring(0, 255);
                const cur = $.trim($('#bayarJudul').val());
                if (!cur || cur === autoJudul) {
                    $('#bayarJudul').val(judul);
                    autoJudul = judul;
                }
                hitungTotal();
            }

            function toggleKp(id) {
                id = String(id);
                if (bayarSel.has(id)) bayarSel.delete(id);
                else bayarSel.add(id);
                onKpChange();
            }

            $(document).on('click', '.kp-card', function() {
                toggleKp($(this).attr('data-id'));
            });

            $(document).on('keydown', '.kp-card', function(e) {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    toggleKp($(this).attr('data-id'));
                }
            });

            $(document).on('click', '.chip-x', function() {
                toggleKp($(this).attr('data-id'));
            });

            $('#kpSearch').on('input', function() {
                kpQuery = $.trim($(this).val());
                paintKp();
            });

            $('#kpAll').on('click', function() {
                const q = kpQuery.toLowerCase();
                rkmOptions.filter(o => !q || (o.materi + ' ' + o.perusahaan).toLowerCase().includes(q))
                    .forEach(o => bayarSel.add(String(o.id)));
                onKpChange();
            });

            $('#kpClear').on('click', function() {
                bayarSel.clear();
                onKpChange();
            });

            function resetBayarForm() {
                editingBayarId = null;
                $('#formBayar')[0].reset();
                bayarSel = new Set();
                kpQuery = '';
                autoJudul = '';
                $('#kpSearch').val('');
                $('#bayarPax').val('');
                setMode(false);
                paintKp();
                $('#bayarFormTitle').text('Tambah pembayaran');
                $('#bayarSubmit').text('Simpan pembayaran');
                $('#btnCancelBayar').addClass('d-none');
                $('#bayarBuktiHint').text('PDF, JPG, PNG, maks. 10 MB');
                $('#formBayar .ol-errors').addClass('d-none').empty();
                syncAllDrops();
                hitungTotal();
            }

            function loadPembayaran() {
                const id = currentId;
                $('#pembayaranList').html(
                    '<div class="empty loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...</div>'
                    );
                $.get(url(ROUTES.pembayaran, {
                        '__ID__': id
                    }))
                    .done(res => {
                        if (id === currentId) renderPembayaran(res);
                    })
                    .fail(() => {
                        $('#pembayaranList').html(
                            '<div class="alert alert-danger mb-0">Gagal memuat riwayat pembayaran.</div>');
                    });
            }

            function tglBlok(s) {
                const p = String(s || '').substring(0, 10).split('-');
                if (p.length !== 3) return '<div class="pay-date"><b>-</b><span>&nbsp;</span></div>';
                return `<div class="pay-date"><b>${esc(p[2])}</b><span>${BULAN[parseInt(p[1], 10) - 1] || ''} ${esc(p[0])}</span></div>`;
            }

            function renderPembayaran(res) {
                pembayaranData = res.data || [];
                rkmOptions = res.rkm_options || [];
                $('#count-pembayaran').text(pembayaranData.length);

                bayarSel = new Set([...bayarSel].filter(id => rkmOptions.some(o => String(o.id) === id)));
                paintKp();

                if (!pembayaranData.length) {
                    $('#pembayaranList').html(
                        '<div class="empty"><i class="fa-regular fa-credit-card big"></i>Belum ada riwayat pembayaran.</div>'
                        );
                    return;
                }

                const cards = pembayaranData.map(p => {
                    const kelas = (p.kelas || []).length ?
                        p.kelas.map(k =>
                            `<span class="chip">${esc(k.materi)}<small>${esc(k.perusahaan || '-')}</small></span>`
                            ).join('') :
                        '<span class="tag tag-muted">Pembayaran umum</span>';
                    return `<article class="pay">
                        ${tglBlok(p.tanggal_bayar)}
                        <div>
                            <div class="pay-title">${esc(p.judul)}</div>
                            ${p.keterangan ? `<div class="pay-note">${esc(p.keterangan)}</div>` : ''}
                            <div class="pay-chips">${kelas}</div>
                        </div>
                        <div class="pay-calc">
                            <div class="pay-amount">${rp(p.total)}</div>
                            <div class="pay-formula">${p.kali_pax ? `${rp(p.harga)} \u00d7 ${esc(p.pax)} pax` : 'Tarif tetap'}</div>
                        </div>
                        <div class="pay-act">
                            ${p.bukti_url ? `<a href="${esc(p.bukti_url)}" target="_blank" rel="noopener" class="btn btn-sm btn-line" title="Lihat bukti"><i class="fa-solid fa-receipt"></i></a>` : ''}
                            <button type="button" class="btn btn-sm btn-line btn-edit-bayar" data-id="${p.id}" title="Ubah"><i class="fa-solid fa-pen"></i></button>
                            <button type="button" class="btn btn-sm btn-line danger btn-del-bayar" data-id="${p.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </article>`;
                }).join('');

                $('#pembayaranList').html(
                    `${cards}<div class="pay-sum"><span>Total semua pembayaran</span><b>${rp(res.total)}</b></div>`
                    );
            }

            $('#bayarHarga, #bayarPax').on('input', hitungTotal);

            $('#bayarMode').on('click', 'button', function() {
                setMode($(this).attr('data-v') === '1');
            });

            $('#paxMin').on('click', function() {
                const v = parseInt($('#bayarPax').val(), 10) || 1;
                $('#bayarPax').val(Math.max(1, v - 1));
                hitungTotal();
            });

            $('#paxPlus').on('click', function() {
                const v = parseInt($('#bayarPax').val(), 10) || 0;
                $('#bayarPax').val(v + 1);
                hitungTotal();
            });

            $('#formBayar').on('submit', function(e) {
                e.preventDefault();
                const target = editingBayarId ?
                    url(ROUTES.pembayaranUpdate, {
                        '__ID__': editingBayarId
                    }) :
                    url(ROUTES.pembayaranStore, {
                        '__ID__': currentId
                    });
                ajaxForm($(this), target, editingBayarId ? 'PUT' : 'POST').done(res => {
                    renderPembayaran(res);
                    resetBayarForm();
                    notifSukses(res.message || 'Pembayaran tersimpan.');
                });
            });

            $(document).on('click', '.btn-edit-bayar', function() {
                const id = parseInt($(this).attr('data-id'), 10);
                const p = pembayaranData.find(x => x.id === id);
                if (!p) return;
                resetBayarForm();
                editingBayarId = id;
                bayarSel = new Set((p.rkm_ids || []).map(String));
                paintKp();
                $('#bayarJudul').val(p.judul);
                $('#bayarHarga').val(p.harga);
                setMode(!!p.kali_pax);
                $('#bayarPax').val(p.pax || '');
                $('#bayarTanggal').val(p.tanggal_bayar || '');
                $('#bayarKet').val(p.keterangan || '');
                $('#bayarFormTitle').text('Ubah pembayaran');
                $('#bayarSubmit').text('Simpan perubahan');
                $('#btnCancelBayar').removeClass('d-none');
                $('#bayarBuktiHint').text(p.bukti_url ? 'Kosongkan jika tidak ingin mengganti file bukti' :
                    'PDF, JPG, PNG, maks. 10 MB');
                hitungTotal();
                document.getElementById('formBayar').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });

            $('#btnCancelBayar').on('click', resetBayarForm);

            $(document).on('click', '.btn-del-bayar', function() {
                const id = $(this).attr('data-id');
                konfirmasi('Hapus data pembayaran ini?', () => {
                    sendDelete(url(ROUTES.pembayaranUpdate, {
                            '__ID__': id
                        }))
                        .done(res => {
                            renderPembayaran(res);
                            resetBayarForm();
                            notifSukses(res.message || 'Pembayaran dihapus.');
                        })
                        .fail(xhr => notifGagal(failMsg(xhr, 'Gagal menghapus pembayaran.')));
                }, 'Ya, hapus');
            });

            const dokState = {
                silabus: {
                    editing: null,
                    data: []
                },
                materi: {
                    editing: null,
                    data: []
                }
            };

            const $dokForm = j => $('#form' + cap(j));

            function syncDokFile(j) {
                const $f = $dokForm(j);
                const st = dokState[j];
                const adaRkm = !!$f.find('[name=rkm_id]').val();
                const $label = $('#' + j + 'FileLabel');

                if (st.editing) {
                    $f.find('[name=file]').prop('required', false);
                    $label.text('Ganti file (opsional)').removeClass('ol-req');
                } else if (adaRkm) {
                    $f.find('[name=file]').prop('required', false);
                    $label.text('File (opsional)').removeClass('ol-req');
                } else {
                    $f.find('[name=file]').prop('required', true);
                    $label.text('File').addClass('ol-req');
                }
            }

            function setDokRkm(j, id, label) {
                const $f = $dokForm(j);
                $f.find('[name=rkm_id]').val(id || '');
                $f.find('.rkm-pick-label')
                    .text(id ? (label || 'Kelas #' + id) : 'Belum ada kelas dipilih')
                    .toggleClass('fw-semibold', !!id);
                $f.find('.btn-lepas-rkm').toggleClass('d-none', !id);
                syncDokFile(j);
            }

            function resetDok(j) {
                dokState[j].editing = null;
                $dokForm(j)[0].reset();
                setDokRkm(j, null, null);
                $('#' + j + 'FormTitle').text('Upload ' + DOK[j].label.toLowerCase());
                $('#' + j + 'Submit').text('Upload');
                $('#btnCancel' + cap(j)).addClass('d-none');
                $dokForm(j).find('.ol-errors').addClass('d-none').empty();
                syncDokFile(j);
                syncAllDrops();
            }

            function loadDok(j) {
                const id = currentId;
                $('#' + j + 'List').html(
                    '<div class="empty loading"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data...</div>'
                    );
                $.get(url(DOK[j].list, {
                        '__ID__': id
                    }))
                    .done(res => {
                        if (id === currentId) renderDok(j, res.data);
                    })
                    .fail(() => {
                        $('#' + j + 'List').html(
                            `<div class="alert alert-danger mb-0">Gagal memuat ${esc(DOK[j].label.toLowerCase())}.</div>`
                            );
                    });
            }

            function renderDok(j, items) {
                dokState[j].data = items || [];
                const data = dokState[j].data;
                $('#count-' + j).text(data.length);

                if (!data.length) {
                    $('#' + j + 'List').html(
                        `<div class="empty"><i class="fa-regular fa-file-lines big"></i>Belum ada ${esc(DOK[j].label.toLowerCase())}.</div>`
                        );
                    return;
                }

                const rows = data.map((it, i) => `<tr>
                    <td>${i + 1}</td>
                    <td class="fw-semibold">${esc(it.judul)}</td>
                    <td>${it.kelas ? esc(it.kelas) : '<span class="text-muted">-</span>'}</td>
                    <td>${esc(it.keterangan || '-')}</td>
                    <td>${it.file_url ? `<a href="${esc(it.file_url)}" target="_blank" rel="noopener" class="tag tag-info"><i class="fa-regular fa-file"></i>${esc(it.file_nama_asli || 'Lihat file')}</a>` : '<span class="text-muted">-</span>'}</td>
                    <td class="text-nowrap">${fmtDate(it.created_at)}</td>
                    <td class="act text-end"><div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-sm btn-line btn-edit-dok" data-jenis="${j}" data-id="${it.id}" title="Ubah"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" class="btn btn-sm btn-line danger btn-del-dok" data-jenis="${j}" data-id="${it.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </div></td>
                </tr>`).join('');

                $('#' + j + 'List').html(`<div class="table-responsive"><table class="table align-middle mb-0 tbl">
                    <thead><tr><th style="width:50px">No.</th><th>Judul</th><th>Kelas (RKM)</th><th>Keterangan</th><th>File</th><th>Diunggah</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>${rows}</tbody></table></div>`);
            }

            $('.dok-form').on('submit', function(e) {
                e.preventDefault();
                const j = $(this).attr('data-jenis');
                const st = dokState[j];
                const target = st.editing ?
                    url(DOK[j].update, {
                        '__ID__': st.editing
                    }) :
                    url(DOK[j].store, {
                        '__ID__': currentId
                    });
                ajaxForm($(this), target, st.editing ? 'PUT' : 'POST').done(res => {
                    renderDok(j, res.data);
                    resetDok(j);
                    notifSukses(res.message || DOK[j].label + ' tersimpan.');
                });
            });

            $(document).on('click', '.btn-pilih-rkm', function() {
                const j = $(this).attr('data-jenis');
                bukaPicker({
                    title: 'Pilih kelas dari RKM untuk ' + DOK[j].label.toLowerCase(),
                    multi: false,
                    onPick: items => {
                        const r = items[0];
                        setDokRkm(j, r.rkm_id, rkmLabel(r));
                        const $judul = $dokForm(j).find('[name=judul]');
                        if (!$.trim($judul.val())) $judul.val(r.materi);
                        modal('modalPilih').hide();
                    }
                });
            });

            $(document).on('click', '.btn-lepas-rkm', function() {
                setDokRkm($(this).attr('data-jenis'), null, null);
            });

            $(document).on('click', '.btn-edit-dok', function() {
                const j = $(this).attr('data-jenis');
                const id = parseInt($(this).attr('data-id'), 10);
                const it = dokState[j].data.find(x => x.id === id);
                if (!it) return;
                resetDok(j);
                dokState[j].editing = id;
                const $f = $dokForm(j);
                $f.find('[name=judul]').val(it.judul);
                $f.find('[name=keterangan]').val(it.keterangan || '');
                setDokRkm(j, it.rkm_id, it.kelas);
                $('#' + j + 'FormTitle').text('Ubah ' + DOK[j].label.toLowerCase());
                $('#' + j + 'Submit').text('Simpan perubahan');
                $('#btnCancel' + cap(j)).removeClass('d-none');
                syncDokFile(j);
                $f[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });

            $('#btnCancelSilabus').on('click', () => resetDok('silabus'));
            $('#btnCancelMateri').on('click', () => resetDok('materi'));

            $(document).on('click', '.btn-del-dok', function() {
                const j = $(this).attr('data-jenis');
                const id = $(this).attr('data-id');
                konfirmasi(`Hapus ${DOK[j].label.toLowerCase()} ini?`, () => {
                    sendDelete(url(DOK[j].update, {
                            '__ID__': id
                        }))
                        .done(res => {
                            renderDok(j, res.data);
                            resetDok(j);
                            notifSukses(res.message || DOK[j].label + ' dihapus.');
                        })
                        .fail(xhr => notifGagal(failMsg(xhr,
                            `Gagal menghapus ${DOK[j].label.toLowerCase()}.`)));
                }, 'Ya, hapus');
            });

            let exportCtx = null;

            function openExport(ctx) {
                exportCtx = ctx;
                $('#exportTitle').text(ctx.title);
                $('#exportError').addClass('d-none');
                $('#fmtPdf').prop('checked', true);
                $('#exportColumns').html(Object.entries(KOLOM[ctx.scope]).map(([k, label]) =>
                    `<label class="xchip"><input class="exp-col" type="checkbox" value="${esc(k)}" checked><span>${esc(label)}</span></label>`
                    ).join(''));
                bukaModal('modalExport');
            }

            $('#btnExportList').on('click', function() {
                openExport({
                    scope: 'list',
                    title: 'Export data instruktur OL',
                    url: ROUTES.export,
                    params: {
                        q: currentQ
                    }
                });
            });

            $('#btnExportRiwayat').on('click', function() {
                openExport({
                    scope: 'riwayat',
                    title: 'Export riwayat mengajar: ' + (currentData ? currentData.nama : ''),
                    url: url(ROUTES.riwayatExport, {
                        '__ID__': currentId
                    }),
                    params: {}
                });
            });

            $('#exportToggleAll').on('click', function() {
                const $all = $('#exportColumns .exp-col');
                $all.prop('checked', $all.filter(':checked').length !== $all.length);
            });

            $('#btnDoExport').on('click', function() {
                const cols = $('#exportColumns .exp-col:checked').map((_, e) => e.value).get();
                if (!cols.length) {
                    $('#exportError').removeClass('d-none');
                    return;
                }
                const params = $.extend({}, exportCtx.params, {
                    format: $('input[name=exportFormat]:checked').val(),
                    columns: cols
                });
                window.location.href = exportCtx.url + '?' + $.param(params);
                modal('modalExport').hide();
            });

            syncAllDrops();
        });
    </script>
@endpush
