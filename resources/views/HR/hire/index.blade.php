@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --primary: #0062ff;
            --primary-soft: #e8f0ff;
            --success: #00b573;
            --warning: #ff9800;
            --info: #00bcd4;
            --danger: #f44336;
            --purple: #7c3aed;
            --teal: #0d9488;
            --secondary: #6b7280;
            --light: #f9fafb;
            --dark: #1f2937;
            --border: #e5e7eb;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark);
        }

        .hire-page .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .hire-page .page-subtitle {
            font-size: .9rem;
            color: var(--secondary);
        }

        .hire-page .card {
            border: none;
            border-radius: .75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
            background: #fff;
        }

        .hire-page .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .hire-page .card-body {
            padding: 1.25rem;
        }

        .hire-tabs {
            display: flex;
            gap: .25rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.25rem;
            padding-left: 0;
            list-style: none;
        }

        .hire-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: none;
            color: var(--secondary);
            font-weight: 600;
            font-size: .9rem;
            padding: .65rem 1rem;
        }

        .hire-tabs .nav-link:hover {
            color: var(--primary);
        }

        .hire-tabs .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: none;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: .75rem;
            padding: 1rem 1.1rem;
            height: 100%;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--c, var(--primary));
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: color-mix(in srgb, var(--c, var(--primary)) 14%, #fff);
            color: var(--c, var(--primary));
            margin-bottom: .6rem;
        }

        .stat-label {
            font-size: .78rem;
            color: var(--secondary);
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-desc {
            font-size: .72rem;
            color: var(--secondary);
        }

        .filter-bar {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: .75rem;
            padding: .85rem 1rem;
            margin-bottom: 1rem;
        }

        .filter-bar .form-control,
        .filter-bar .form-select {
            font-size: .875rem;
            border: 1px solid var(--border);
            border-radius: .5rem;
        }

        .filter-bar .form-control:focus,
        .filter-bar .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 98, 255, .1);
        }

        .search-wrapper {
            position: relative;
        }

        .search-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
            font-size: .85rem;
        }

        .search-wrapper .form-control {
            padding-left: 2.25rem;
        }

        .hire-layout {
            display: flex;
            gap: 1.25rem;
            align-items: flex-start;
        }

        .hire-main-content {
            flex: 1;
            min-width: 0;
        }

        .folder-sidebar {
            width: 250px;
            flex-shrink: 0;
            background: #fff;
            border-radius: .75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
            padding: 1rem;
            position: sticky;
            top: 20px;
        }

        .folder-sidebar-title {
            font-size: .78rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: .6rem;
            padding: 0 .5rem;
        }

        .folder-list,
        .folder-list-nested {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .folder-list-item {
            margin-bottom: 2px;
        }

        .folder-list-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .7rem;
            border-radius: .5rem;
            cursor: pointer;
            color: var(--dark);
            font-size: .85rem;
            text-decoration: none;
            transition: background .15s;
        }

        .folder-list-link:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .folder-list-link.active {
            background: var(--primary);
            color: #fff;
            font-weight: 600;
        }

        .folder-icon {
            color: #ffc107;
        }

        .inbox-link .folder-icon {
            color: var(--info);
        }

        .folder-list-link.active .folder-icon,
        .folder-list-link.active .pin-icon {
            color: #fff !important;
        }

        .folder-label {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .folder-count {
            background: rgba(0, 0, 0, .08);
            padding: 1px 8px;
            border-radius: 10px;
            font-size: .7rem;
            font-weight: 600;
        }

        .folder-list-link.active .folder-count {
            background: rgba(255, 255, 255, .25);
        }

        .pin-icon {
            color: #ffc107;
            font-size: .7rem;
        }

        .folder-sidebar-divider {
            height: 1px;
            background: var(--border);
            margin: .75rem 0;
        }

        .folder-list-nested {
            border-left: 1px dashed var(--border);
            margin: 2px 0 2px 18px;
            padding-left: 12px;
        }

        .folder-list-nested .folder-list-link {
            font-size: .8rem;
            padding: .4rem .6rem;
        }

        .folder-toggle {
            width: 16px;
            display: inline-flex;
            justify-content: center;
            cursor: pointer;
            color: #9ca3af;
            font-size: .65rem;
            transition: transform .2s;
        }

        .folder-toggle.expanded {
            transform: rotate(90deg);
        }

        .folder-actions {
            display: none;
            gap: 3px;
        }

        .folder-list-link:hover .folder-actions {
            display: flex;
        }

        .folder-list-link:hover .folder-count {
            display: none;
        }

        .folder-action-btn {
            width: 22px;
            height: 22px;
            border: none;
            border-radius: 4px;
            background: #fff;
            color: var(--secondary);
            font-size: .7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .folder-action-btn:hover {
            background: var(--primary);
            color: #fff;
        }

        .folder-action-btn.danger:hover {
            background: #dc2626;
        }

        .folder-action-btn.archive:hover {
            background: #f59e0b;
        }

        .folder-list-link.drag-over {
            background: #dcfce7 !important;
            color: #16a34a !important;
            outline: 1px dashed #16a34a;
        }

        .folder-active-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: var(--primary-soft);
            color: var(--primary);
            padding: .25rem .75rem;
            border-radius: 1rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .folder-active-badge button {
            background: none;
            border: none;
            color: var(--primary);
            padding: 0;
        }

        .table-applicant {
            font-size: .875rem;
            margin-bottom: 0;
        }

        .table-applicant thead th {
            background: #fafbfc;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            font-size: .78rem;
            color: var(--secondary);
            padding: .8rem 1rem;
            white-space: nowrap;
        }

        .table-applicant tbody td {
            padding: .9rem 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table-applicant tbody tr:hover {
            background: #f9fafb;
        }

        .table-applicant tbody tr[draggable="true"] {
            cursor: grab;
        }

        .table-applicant tbody tr.dragging {
            opacity: .4;
        }

        .applicant-info {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .applicant-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .applicant-name {
            font-weight: 600;
        }

        .applicant-email,
        .position-dept {
            font-size: .75rem;
            color: var(--secondary);
        }

        .av-0 {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .av-1 {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .av-2 {
            background: linear-gradient(135deg, #4facfe, #00c6d4);
        }

        .av-3 {
            background: linear-gradient(135deg, #43c97b, #2bb5a0);
        }

        .av-4 {
            background: linear-gradient(135deg, #fa709a, #f6b73c);
        }

        .av-5 {
            background: linear-gradient(135deg, #a18cd1, #e59bc8);
        }

        .source-badge {
            display: inline-block;
            font-size: .75rem;
            font-weight: 500;
            padding: .28rem .6rem;
            border-radius: .375rem;
            background: #f3f4f6;
            color: var(--secondary);
        }

        .source-badge.linkedin {
            background: #e0f2fe;
            color: #0a66c2;
        }

        .source-badge.jobstreet {
            background: #fee2e2;
            color: #e11d48;
        }

        .source-badge.website-perusahaan {
            background: #dbeafe;
            color: #2563eb;
        }

        .source-badge.referral-karyawan {
            background: #dcfce7;
            color: #16a34a;
        }

        .source-badge.glints {
            background: #fef3c7;
            color: #d97706;
        }

        .source-badge.kalibrr {
            background: #ede9fe;
            color: #7c3aed;
        }

        .stage-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .74rem;
            font-weight: 600;
            padding: .3rem .7rem;
            border-radius: 1rem;
            white-space: nowrap;
        }

        .stage-badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .stage-applied {
            background: #dbeafe;
            color: #2563eb;
        }

        .stage-screening {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .stage-psikotest {
            background: #ccfbf1;
            color: #0f766e;
        }

        .stage-interview {
            background: #fef3c7;
            color: #d97706;
        }

        .stage-offer {
            background: #ede9fe;
            color: #7c3aed;
        }

        .stage-hired {
            background: #dcfce7;
            color: #16a34a;
        }

        .stage-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .table-rating {
            color: #ffc107;
            letter-spacing: 1px;
        }

        .table-rating .off {
            color: #e5e7eb;
        }

        .muted-small {
            font-size: .72rem;
            color: var(--secondary);
        }

        .muted-italic {
            font-size: .8rem;
            font-style: italic;
            color: #9ca3af;
        }

        .pt-chips {
            display: flex;
            gap: 4px;
        }

        .pt-chip {
            font-size: .66rem;
            font-weight: 600;
            padding: .15rem .4rem;
            border-radius: .3rem;
            background: #f3f4f6;
            color: #9ca3af;
        }

        .pt-chip.on {
            background: #ccfbf1;
            color: #0f766e;
        }

        .action-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-quick-action,
        .btn-action-dropdown {
            padding: .32rem .6rem;
            font-size: .85rem;
            border-radius: .375rem;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--dark);
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .btn-quick-action:hover,
        .btn-action-dropdown:hover,
        .btn-action-dropdown.show {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .action-dropdown-menu {
            min-width: 220px;
            padding: .4rem;
            border: 1px solid var(--border);
            border-radius: .5rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
        }

        .action-dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .5rem .8rem;
            font-size: .85rem;
            border-radius: .375rem;
            cursor: pointer;
        }

        .action-dropdown-menu .dropdown-item i {
            width: 18px;
            text-align: center;
        }

        .action-dropdown-menu .dropdown-item:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .action-dropdown-menu .dropdown-item.danger {
            color: var(--danger);
        }

        .action-dropdown-menu .dropdown-item.danger:hover {
            background: #fee2e2;
        }

        .action-dropdown-menu .dropdown-divider {
            margin: .3rem 0;
        }

        .pagination .page-link {
            border: 1px solid var(--border);
            color: var(--secondary);
            font-size: .85rem;
            padding: .35rem .7rem;
            margin: 0 2px;
            border-radius: .375rem;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .pagination-info {
            font-size: .85rem;
            color: var(--secondary);
        }

        .chart-box {
            position: relative;
            height: 280px;
        }

        .chart-box.tall {
            height: 340px;
        }

        .chart-box.is-empty canvas {
            display: none;
        }

        .chart-box.is-empty::after {
            content: "Belum ada data";
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: .85rem;
        }

        .section-heading {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 1.75rem 0 .75rem;
        }

        .info-box {
            background: #f9fafb;
            border-left: 3px solid var(--teal);
            border-radius: .4rem;
            padding: .8rem 1rem;
            font-size: .83rem;
            line-height: 1.55;
        }

        .info-box dt {
            font-weight: 600;
            margin-top: .45rem;
        }

        .info-box dt:first-child {
            margin-top: 0;
        }

        .info-box dd {
            margin: 0;
            color: #4b5563;
        }

        .dim-row {
            margin-bottom: 1rem;
        }

        .dim-label {
            display: flex;
            justify-content: space-between;
            font-size: .8rem;
            font-weight: 600;
        }

        .dim-bar {
            height: 8px;
            border-radius: 4px;
            background: #fde68a;
            overflow: hidden;
            margin: .3rem 0;
        }

        .dim-bar span {
            display: block;
            height: 100%;
            background: var(--teal);
        }

        .list-kecil {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: .83rem;
        }

        .list-kecil li {
            padding: .45rem 0;
            border-bottom: 1px solid var(--border);
        }

        .list-kecil li:last-child {
            border-bottom: none;
        }

        .modal-content {
            border: none;
            border-radius: .75rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
            overflow: hidden;
        }

        .modal-dialog-scrollable .modal-content {
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 3.5rem);
        }

        .modal-dialog-scrollable .modal-content>form {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            min-height: 0;
            overflow: hidden;
        }

        .modal-dialog-scrollable .modal-content>form .modal-body,
        .modal-dialog-scrollable .modal-body {
            flex-grow: 1;
            min-height: 0;
            overflow-y: auto;
        }

        .modal-header {
            background: linear-gradient(135deg, #f8faff, #eef4ff);
            border-bottom: 1px solid var(--border);
            padding: 1.1rem 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .modal-title i {
            color: var(--primary);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border);
            padding: .9rem 1.5rem;
            background: #fafbfc;
        }

        .modal-lg-custom {
            max-width: 720px;
        }

        .modal-xl-custom {
            max-width: 900px;
        }

        .form-label-custom {
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: .35rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .form-label-custom .required {
            color: var(--danger);
        }

        .unit-hint {
            font-size: .7rem;
            color: var(--secondary);
            margin-left: auto;
            background: var(--primary-soft);
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 500;
        }

        .form-control-custom,
        .form-select-custom {
            font-size: .875rem;
            padding: .55rem .85rem;
            border: 1px solid var(--border);
            border-radius: .5rem;
            background: #fff;
            width: 100%;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 98, 255, .1);
            outline: none;
        }

        textarea.form-control-custom {
            resize: vertical;
            min-height: 70px;
        }

        .form-hint {
            font-size: .72rem;
            color: var(--secondary);
            margin-top: .25rem;
        }

        .form-section-title {
            font-size: .85rem;
            font-weight: 700;
            color: var(--primary);
            padding-bottom: .4rem;
            border-bottom: 1px solid var(--border);
            margin-top: .5rem;
        }

        .scan-box {
            background: linear-gradient(135deg, #f0f7ff, #f5f0ff);
            border: 1px dashed var(--primary);
            border-radius: .75rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .scan-box-title {
            font-weight: 700;
            font-size: .9rem;
            margin-bottom: .25rem;
        }

        .scan-box-title i {
            color: var(--purple);
        }

        .scan-status {
            font-size: .8rem;
            margin-top: .6rem;
            display: none;
        }

        .scan-status.loading,
        .scan-status.ok,
        .scan-status.error {
            display: block;
        }

        .scan-status.loading {
            color: var(--primary);
        }

        .scan-status.ok {
            color: var(--success);
        }

        .scan-status.error {
            color: var(--danger);
        }

        .scan-filled {
            border-color: var(--success) !important;
            background-color: #f0fdf4 !important;
        }

        .file-info {
            font-size: .72rem;
            margin-top: .25rem;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 2rem 1.5rem;
            color: #fff;
            text-align: center;
            position: relative;
        }

        .profile-avatar-lg {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .25);
            border: 3px solid rgba(255, 255, 255, .4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }

        .profile-name {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .profile-position {
            opacity: .9;
            margin-bottom: 1rem;
        }

        .profile-contact {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: .8rem;
        }

        .profile-body-scroll {
            overflow-y: auto;
            flex-grow: 1;
            min-height: 0;
        }

        .profile-section {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .profile-section:last-child {
            border-bottom: none;
        }

        .profile-section-title {
            font-size: .85rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: .8rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .85rem;
        }

        .profile-item-label {
            font-size: .72rem;
            color: var(--secondary);
            margin-bottom: .15rem;
        }

        .profile-item-value {
            font-size: .875rem;
            font-weight: 500;
        }

        .skill-tag {
            display: inline-block;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: .72rem;
            font-weight: 600;
            padding: .25rem .6rem;
            border-radius: 1rem;
            margin: .15rem .15rem .15rem 0;
        }

        .timeline-item {
            display: flex;
            gap: .75rem;
            padding-bottom: .85rem;
            position: relative;
        }

        .timeline-item:not(:last-child)::before {
            content: "";
            position: absolute;
            left: 11px;
            top: 24px;
            bottom: 0;
            width: 2px;
            background: var(--border);
        }

        .timeline-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary-soft);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            flex-shrink: 0;
            z-index: 1;
        }

        .timeline-dot.active {
            background: var(--primary);
            color: #fff;
        }

        .timeline-dot.done {
            background: var(--success);
            color: #fff;
        }

        .timeline-title {
            font-size: .85rem;
            font-weight: 600;
        }

        .timeline-desc {
            font-size: .75rem;
            color: var(--secondary);
        }

        .timeline-feedback {
            background: #f9fafb;
            border-left: 3px solid var(--primary);
            padding: .5rem .75rem;
            margin-top: .5rem;
            border-radius: .25rem;
            font-size: .8rem;
        }

        .penilaian-summary {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-radius: .75rem;
            padding: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 1rem;
            text-align: center;
        }

        .penilaian-summary-value {
            font-size: 1.9rem;
            font-weight: 700;
            color: #92400e;
            line-height: 1;
        }

        .penilaian-summary-label {
            font-size: .75rem;
            color: #78350f;
            margin-top: 4px;
        }

        .penilaian-card {
            background: #f9fafb;
            border-left: 4px solid var(--primary);
            border-radius: .5rem;
            padding: 1rem;
            margin-bottom: .75rem;
        }

        .penilaian-folder-badge {
            background: var(--primary-soft);
            color: var(--primary);
            font-size: .7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: .5rem;
        }

        .penilaian-catatan-box {
            background: #fff;
            padding: .75rem;
            border-radius: .375rem;
            font-size: .85rem;
            margin-top: .5rem;
            border: 1px solid var(--border);
        }

        .penilaian-empty {
            text-align: center;
            padding: 1.5rem 1rem;
            color: var(--secondary);
        }

        .pt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: .6rem 0;
            border-bottom: 1px solid var(--border);
        }

        .pt-row:last-child {
            border-bottom: none;
        }

        .pt-row-title {
            font-weight: 600;
            font-size: .85rem;
        }

        .confirm-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #fee2e2;
            color: var(--danger);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
        }

        .confirm-title {
            font-size: 1.1rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: .5rem;
        }

        .confirm-desc {
            font-size: .875rem;
            color: var(--secondary);
            text-align: center;
        }

        .stepper {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .stepper::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: var(--border);
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .35rem;
            position: relative;
            flex: 1;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--border);
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 600;
        }

        .step.active .step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .step.done .step-circle {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .step-label {
            font-size: .7rem;
            color: var(--secondary);
        }

        .pv-box {
            border: 1px solid var(--border);
            border-radius: .6rem;
            background: #f3f4f6;
            min-height: 420px;
            height: 100%;
            overflow: auto;
            position: relative;
        }

        .pv-frame {
            width: 100%;
            height: 68vh;
            border: none;
            background: #fff;
        }

        .pv-img {
            padding: 1rem;
            text-align: center;
        }

        .pv-img img {
            max-width: 100%;
            height: auto;
            border-radius: .4rem;
        }

        .pv-docx {
            padding: 1rem;
        }

        .pv-pesan {
            text-align: center;
            color: var(--secondary);
            padding: 3rem 1.5rem;
            font-size: .88rem;
        }

        .pv-pesan i {
            font-size: 2.2rem;
            opacity: .35;
            display: block;
            margin-bottom: .75rem;
        }

        .pt-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-bottom: 1rem;
        }

        .pt-tab {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 2rem;
            padding: .35rem .9rem;
            font-size: .82rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .pt-tab.active {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff;
        }

        .pt-tab .fa-circle-check {
            color: var(--success);
            margin-left: .3rem;
        }

        .pt-tab.active .fa-circle-check {
            color: #fff;
        }

        #toastContainer {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .toast-msg {
            background: #1f2937;
            color: #fff;
            padding: .75rem 1.25rem;
            border-radius: .5rem;
            font-size: .85rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
            animation: slideIn .3s ease;
        }

        .toast-msg.success {
            background: var(--success);
        }

        .toast-msg.danger {
            background: var(--danger);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 992px) {
            .hire-layout {
                flex-direction: column;
            }

            .folder-sidebar {
                width: 100%;
                position: static;
            }
        }

        @media (max-width: 767px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .stat-value {
                font-size: 1.4rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .toast-msg {
                animation: none;
            }
        }

        /* =========================================================
               DETAIL PELAMAR
               ========================================================= */
        .pd-dialog {
            max-width: 1060px;
        }

        .pd-shell {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            height: min(720px, calc(100vh - 3.5rem));
            min-height: 0;
        }

        /* ---- kolom kiri: identitas ---- */
        .pd-side {
            background: #f8fafc;
            border-right: 1px solid var(--border);
            padding: 1.75rem 1.4rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .pd-id {
            text-align: center;
        }

        .pd-avatar {
            width: 88px;
            height: 88px;
            border-radius: 26px;
            margin: 0 auto .9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.9rem;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(79, 70, 229, .25);
        }

        .pd-name {
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .pd-role {
            font-size: .85rem;
            color: var(--secondary);
            margin: .15rem 0 .75rem;
        }

        .pd-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: .75rem;
            font-size: .8rem;
            color: var(--secondary);
        }

        .pd-contact {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: .55rem;
            font-size: .83rem;
        }

        .pd-contact li {
            display: flex;
            gap: .65rem;
            align-items: flex-start;
            word-break: break-word;
        }

        .pd-contact i {
            width: 16px;
            margin-top: .2rem;
            text-align: center;
            color: var(--primary);
        }

        .pd-contact a {
            color: inherit;
            text-decoration: none;
        }

        .pd-contact a:hover {
            color: var(--primary);
        }

        .pd-side-actions {
            margin-top: auto;
            display: grid;
            gap: .5rem;
        }

        .pd-side-actions .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            font-size: .85rem;
        }

        /* ---- kolom kanan ---- */
        .pd-main {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
            background: #fff;
        }

        .pd-main-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem .25rem;
        }

        .pd-main-head h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        /* ---- tahap rekrutmen ---- */
        .pd-pipeline {
            display: flex;
            padding: 1rem 1.5rem 1.1rem;
            border-bottom: 1px solid var(--border);
        }

        .pd-step {
            flex: 1;
            text-align: center;
            position: relative;
            font-size: .72rem;
            color: var(--secondary);
        }

        .pd-step::before {
            content: "";
            position: absolute;
            top: 13px;
            left: -50%;
            width: 100%;
            height: 2px;
            background: var(--border);
        }

        .pd-step:first-child::before {
            display: none;
        }

        .pd-step.done::before,
        .pd-step.now::before {
            background: var(--success);
        }

        .pd-dot {
            position: relative;
            z-index: 1;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            margin: 0 auto .4rem;
            background: #fff;
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
        }

        .pd-step.done .pd-dot {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .pd-step.now .pd-dot {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 0 4px var(--primary-soft);
        }

        .pd-step.now {
            color: var(--primary);
            font-weight: 700;
        }

        .pd-rejected {
            margin: 1rem 1.5rem 0;
            padding: .7rem 1rem;
            border-radius: .6rem;
            background: #fef2f2;
            color: #b91c1c;
            font-size: .85rem;
            display: flex;
            gap: .6rem;
            align-items: center;
        }

        /* ---- tab ---- */
        .pd-tabs {
            display: flex;
            gap: .25rem;
            padding: 0 1.25rem;
            margin: 0;
            list-style: none;
            border-bottom: 1px solid var(--border);
            overflow-x: auto;
        }

        .pd-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: none;
            padding: .75rem .85rem;
            font-size: .85rem;
            font-weight: 600;
            color: var(--secondary);
            white-space: nowrap;
        }

        .pd-tabs .nav-link:hover {
            color: var(--primary);
        }

        .pd-tabs .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .pd-count {
            display: inline-block;
            min-width: 20px;
            margin-left: .35rem;
            padding: 0 .4rem;
            border-radius: 10px;
            background: #eef2f7;
            font-size: .68rem;
            line-height: 18px;
            text-align: center;
        }

        .pd-tabs .nav-link.active .pd-count {
            background: var(--primary-soft);
        }

        .pd-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 1.4rem 1.5rem 1.75rem;
        }

        /* ---- ringkasan ---- */
        .pd-facts {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
            margin-bottom: 1.6rem;
        }

        .pd-fact {
            padding: .85rem 1rem;
            border-radius: .7rem;
            background: #f8fafc;
            border: 1px solid var(--border);
        }

        .pd-fact-val {
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.25;
        }

        .pd-fact-lbl {
            font-size: .74rem;
            color: var(--secondary);
            margin-top: .15rem;
        }

        .pd-group {
            margin-bottom: 1.6rem;
        }

        .pd-group:last-child {
            margin-bottom: 0;
        }

        .pd-group-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .9rem;
            font-weight: 700;
            margin-bottom: .6rem;
        }

        .pd-group-title i {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: var(--primary-soft);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
        }

        .pd-list {
            margin: 0;
        }

        .pd-row {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 1rem;
            padding: .6rem 0;
            border-bottom: 1px solid #f0f2f5;
            font-size: .85rem;
        }

        .pd-row:last-child {
            border-bottom: none;
        }

        .pd-row dt {
            font-weight: 500;
            color: var(--secondary);
        }

        .pd-row dd {
            margin: 0;
            font-weight: 500;
            word-break: break-word;
        }

        .pd-empty {
            color: #b6bcc6;
            font-weight: 400;
            font-style: italic;
        }

        .pd-note {
            padding: .85rem 1rem;
            border-radius: .6rem;
            background: #fffbeb;
            border-left: 3px solid var(--warning);
            font-size: .85rem;
            line-height: 1.55;
        }

        /* ---- psikotest ---- */
        .pd-test {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .9rem 1rem;
            border: 1px solid var(--border);
            border-radius: .7rem;
            margin-bottom: .6rem;
        }

        .pd-test.kosong {
            background: #fafbfc;
            border-style: dashed;
        }

        .pd-test-title {
            font-weight: 600;
            font-size: .88rem;
        }

        .pd-test-meta {
            font-size: .78rem;
            color: var(--secondary);
            margin-top: .1rem;
        }

        .pd-test-score {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--teal);
            min-width: 56px;
            text-align: right;
        }

        /* ---- dokumen ---- */
        .pd-docs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .75rem;
        }

        .pd-doc {
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .85rem 1rem;
            border: 1px solid var(--border);
            border-radius: .7rem;
        }

        .pd-doc.kosong {
            background: #fafbfc;
            border-style: dashed;
            color: #9ca3af;
        }

        .pd-doc-ico {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 10px;
            background: var(--primary-soft);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pd-doc.kosong .pd-doc-ico {
            background: #f1f3f6;
            color: #b6bcc6;
        }

        .pd-doc-name {
            font-weight: 600;
            font-size: .86rem;
        }

        .pd-doc-act {
            margin-left: auto;
            display: flex;
            gap: .35rem;
        }

        /* ---- responsive ---- */
        @media (max-width: 991px) {
            .pd-shell {
                grid-template-columns: 1fr;
                height: auto;
                max-height: calc(100vh - 3.5rem);
                overflow-y: auto;
            }

            .pd-side {
                border-right: none;
                border-bottom: 1px solid var(--border);
                overflow: visible;
            }

            .pd-body {
                overflow: visible;
            }

            .pd-facts {
                grid-template-columns: repeat(2, 1fr);
            }

            .pd-docs {
                grid-template-columns: 1fr;
            }

            .pd-row {
                grid-template-columns: 1fr;
                gap: .1rem;
            }
        }

        /* =========================================================
               POLISH UMUM
               ========================================================= */
        .hire-page .btn:focus-visible,
        .hire-page .nav-link:focus-visible,
        .folder-list-link:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }

        .hire-tabs {
            gap: .5rem;
        }

        .hire-tabs .nav-link {
            padding: .75rem 1.1rem;
        }

        .filter-bar {
            box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
        }

        .hire-page .card {
            border: 1px solid var(--border);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .hire-page .card-header {
            padding: 1.1rem 1.4rem;
        }

        .table-applicant thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            letter-spacing: .02em;
        }

        .table-applicant tbody td {
            padding: 1rem;
        }

        .table-applicant tbody tr {
            transition: background .15s;
        }

        .table-applicant tbody tr:hover {
            background: #f5f8ff;
        }

        .applicant-info.btn-lihat-profil {
            cursor: pointer;
        }

        .applicant-info.btn-lihat-profil:hover .applicant-name {
            color: var(--primary);
        }

        .stage-badge {
            padding: .35rem .8rem;
        }

        .btn-quick-action,
        .btn-action-dropdown {
            width: 34px;
            height: 34px;
            justify-content: center;
            padding: 0;
        }

        .folder-sidebar {
            border: 1px solid var(--border);
            box-shadow: none;
        }

        .folder-list-link {
            padding: .55rem .75rem;
        }

        .folder-list-link.active {
            box-shadow: 0 4px 12px rgba(0, 98, 255, .25);
        }

        .modal-header {
            background: #fff;
        }

        .modal-footer {
            background: #fff;
        }

        /* ===== Modal hasil penilaian ===== */
        .pn-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
            border-radius: .75rem;
            background: #f8fafc;
        }

        .pn-avg {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1;
        }

        .pn-avg-lbl {
            font-size: .75rem;
            color: var(--secondary);
            margin-top: .25rem;
        }

        .pn-stage-list {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem 1.25rem;
            font-size: .8rem;
            margin-left: auto;
        }

        .pn-stage-list b {
            font-weight: 700;
        }

        .pn-filter {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-bottom: 1rem;
        }

        .pn-chip {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 2rem;
            padding: .3rem .85rem;
            font-size: .8rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .pn-chip.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .pn-item {
            border: 1px solid var(--border);
            border-radius: .75rem;
            padding: 1rem 1.15rem;
            margin-bottom: .75rem;
            background: #fff;
        }

        .pn-head {
            display: flex;
            align-items: center;
            gap: .8rem;
            flex-wrap: wrap;
        }

        .pn-ava {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
        }

        .pn-who {
            min-width: 0;
            flex: 1;
        }

        .pn-who-name {
            font-weight: 600;
            font-size: .9rem;
        }

        .pn-meta {
            font-size: .76rem;
            color: var(--secondary);
        }

        .pn-score {
            text-align: right;
        }

        .pn-score .table-rating {
            font-size: 1.05rem;
        }

        .pn-note {
            margin-top: .75rem;
            padding: .7rem .9rem;
            border-radius: .55rem;
            background: #f8fafc;
            border-left: 3px solid var(--primary);
            font-size: .85rem;
            line-height: 1.55;
            white-space: pre-line;
        }

        .pn-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem;
            margin-top: .75rem;
        }

        .pn-foot .muted-small {
            margin-right: auto;
        }

        .rating-cell-link {
            cursor: pointer;
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .rating-cell-link:hover .muted-small {
            color: var(--primary);
            text-decoration: underline;
        }
    </style>

    <div id="toastContainer"></div>

    <div class="container-fluid hire-page">
        <div class="d-sm-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="page-title">Pelamar Baru</h1>
                <p class="page-subtitle mb-0">Kelola seluruh pelamar yang masuk pada proses rekrutmen</p>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <a href="{{ route('HR.hire.export') }}" id="btnExport" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-file-export me-1"></i> Export
                </a>
                <button type="button" class="btn btn-primary" id="btnTambahPelamar">
                    <i class="fa-solid fa-user-plus me-1"></i> Tambah Pelamar
                </button>
            </div>
        </div>

        <ul class="hire-tabs" role="tablist">
            <li><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabDaftar">Daftar
                    pelamar</button></li>
            <li><button type="button" class="nav-link" id="tabAnalitikBtn" data-bs-toggle="tab"
                    data-bs-target="#tabAnalitik">Analitik</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tabDaftar">
                <div class="filter-bar">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-4">
                            <div class="search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="fSearch" class="form-control"
                                    placeholder="Cari nama, email, atau posisi...">
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <select id="fJabatan" class="form-select">
                                <option value="">Semua posisi</option>
                                @foreach ($dataJabatan as $jab)
                                    <option value="{{ $jab }}">{{ $jab }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-6">
                            <select id="fTahap" class="form-select">
                                <option value="">Semua tahap</option>
                                @foreach ($tahapList as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-6">
                            <select id="fSumber" class="form-select">
                                <option value="">Semua sumber</option>
                                @foreach ($sumberList as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-6 text-end">
                            <button type="button" class="btn btn-outline-secondary" id="btnResetFilter"
                                title="Reset filter">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="hire-layout">
                    <aside class="folder-sidebar" id="hireSidebar">
                        <div class="folder-sidebar-title"><i class="fa-solid fa-folder-tree me-1"></i> Kategori</div>
                        <ul class="folder-list">
                            <li class="folder-list-item">
                                <a class="folder-list-link inbox-link active" data-folder-id="all"
                                    data-nama="Semua Pelamar">
                                    <i class="fa-solid fa-users folder-icon"></i>
                                    <span class="folder-label">Semua Pelamar</span>
                                    <span class="folder-count" id="countAll">-</span>
                                </a>
                            </li>
                            <li class="folder-list-item">
                                <a class="folder-list-link inbox-link" data-folder-id="unassigned"
                                    data-nama="Belum Masuk Folder">
                                    <i class="fa-solid fa-inbox folder-icon"></i>
                                    <span class="folder-label">Belum Masuk Folder</span>
                                    <span class="folder-count" id="countUnassigned">-</span>
                                </a>
                            </li>
                            <li class="folder-list-item">
                                <a class="folder-list-link inbox-link"
                                    href="{{ route('HR.arsip.index', ['layout' => 'hr']) }}">
                                    <i class="fa-solid fa-box-archive folder-icon"></i>
                                    <span class="folder-label">Data Arsip</span>
                                </a>
                            </li>
                        </ul>

                        <div class="folder-sidebar-divider"></div>

                        <div class="folder-sidebar-title"><i class="fa-solid fa-folder me-1"></i> Folder</div>
                        <ul class="folder-list" id="hireFolderTree">
                            <li class="text-center text-muted py-3" style="font-size:.8rem;">
                                <i class="fa-solid fa-spinner fa-spin"></i> Memuat...
                            </li>
                        </ul>
                    </aside>

                    <div class="hire-main-content">
                        <div class="mb-3" id="activeFolderIndicator" style="display:none;">
                            <span class="folder-active-badge">
                                <i class="fa-solid fa-folder-open"></i>
                                <span id="activeFolderName">-</span>
                                <button type="button" id="btnResetFolder" title="Tampilkan semua"><i
                                        class="fa-solid fa-xmark"></i></button>
                            </span>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">Daftar pelamar</div>
                                    <small class="text-muted fw-normal" id="tableInfo">Memuat data...</small>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalCreateFolder">
                                    <i class="fa fa-plus me-2"></i> Buat Folder Baru
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-applicant">
                                        <thead>
                                            <tr>
                                                <th>Pelamar</th>
                                                <th>Posisi dilamar</th>
                                                <th>Sumber</th>
                                                <th>Tanggal melamar</th>
                                                <th>Penilaian</th>
                                                <th>Psikotest</th>
                                                <th>Tahapan</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabelBody"></tbody>
                                    </table>
                                </div>
                                <div
                                    class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top gap-2">
                                    <div class="pagination-info" id="paginationInfo"></div>
                                    <nav>
                                        <ul class="pagination mb-0" id="paginationNav"></ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tabAnalitik">
                <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
                    <div>
                        <label class="form-label-custom">Melamar dari</label>
                        <input type="date" id="aDari" class="form-control-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">Sampai</label>
                        <input type="date" id="aSampai" class="form-control-custom">
                    </div>
                    <button type="button" class="btn btn-primary" id="btnAnalitikTerapkan">Terapkan</button>
                    <button type="button" class="btn btn-outline-secondary" id="btnAnalitikReset">Reset</button>
                    <span class="text-muted small ms-1" id="analitikStatus"></span>
                </div>

                <div class="row g-3 mb-3" id="kartuAnalitik"></div>

                <div class="row g-3 mb-3">
                    <div class="col-lg-5">
                        <div class="card h-100">
                            <div class="card-header">Funnel rekrutmen</div>
                            <div class="card-body">
                                <div class="chart-box"><canvas id="chFunnel"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card h-100">
                            <div class="card-header">Tren lamaran per bulan</div>
                            <div class="card-body">
                                <div class="chart-box"><canvas id="chTrend"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">Sumber lamaran</div>
                            <div class="card-body">
                                <div class="chart-box"><canvas id="chSumber"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">Divisi dilamar</div>
                            <div class="card-body">
                                <div class="chart-box"><canvas id="chDivisi"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">Pendidikan terakhir</div>
                            <div class="card-body">
                                <div class="chart-box"><canvas id="chPendidikan"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>

                <h2 class="section-heading">Psikotest dan kepribadian</h2>
                <div class="row g-3 mb-3" id="ptCakupan"></div>

                <div class="card mb-3">
                    <div class="card-header">MBTI — gaya kepribadian</div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="chart-box"><canvas id="chMbti"></canvas></div>
                            </div>
                            <div class="col-lg-5">
                                <div id="mbtiDimensi"></div>
                            </div>
                            <div class="col-lg-7">
                                <ul class="list-kecil" id="mbtiTipe"></ul>
                            </div>
                            <div class="col-lg-5">
                                <dl class="info-box mb-0" id="infoMbti"></dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">RIASEC — minat karier</div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="muted-small mb-1">Rata-rata skor per huruf</div>
                                <div class="chart-box"><canvas id="chRiasecRadar"></canvas></div>
                            </div>
                            <div class="col-lg-6">
                                <div class="muted-small mb-1">Jumlah pelamar menurut huruf dominan</div>
                                <div class="chart-box"><canvas id="chRiasecDominan"></canvas></div>
                            </div>
                            <div class="col-lg-7">
                                <ul class="list-kecil" id="riasecHuruf"></ul>
                            </div>
                            <div class="col-lg-5">
                                <dl class="info-box mb-0" id="infoRiasec"></dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">Logika numerik</div>
                            <div class="card-body">
                                <div class="row text-center mb-3" id="statNumerik"></div>
                                <div class="chart-box"><canvas id="chNumerik"></canvas></div>
                                <dl class="info-box mt-3 mb-0" id="infoNumerik"></dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">Analogi verbal</div>
                            <div class="card-body">
                                <div class="row text-center mb-3" id="statVerbal"></div>
                                <div class="chart-box"><canvas id="chVerbal"></canvas></div>
                                <dl class="info-box mt-3 mb-0" id="infoVerbal"></dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Tren rata-rata skor per bulan</div>
                    <div class="card-body">
                        <div class="chart-box"><canvas id="chTrenSkor"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFormPelamar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus" id="pfIcon"></i> <span
                            id="pfTitle">Tambah Pelamar Manual</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formPelamar" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="pelamarId">
                    <div class="modal-body">
                        <div class="scan-box">
                            <div class="scan-box-title"><i class="fa-solid fa-wand-magic-sparkles"></i> Isi otomatis dari
                                dokumen</div>
                            <div class="form-hint mb-2" style="margin-top:0;">
                                Unggah CV (PDF/DOCX) atau foto KTP/Ijazah (JPG/PNG). Data yang dikenali akan mengisi form
                                dan tetap bisa diubah.
                            </div>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label-custom">Jenis dokumen</label>
                                    <select id="scanJenis" class="form-select-custom">
                                        <option value="cv">CV / Resume</option>
                                        <option value="ktp">KTP</option>
                                        <option value="ijazah">Ijazah / Transkrip</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label-custom">File dokumen</label>
                                    <input type="file" id="scanFile" class="form-control-custom"
                                        accept=".pdf,.docx,.jpg,.jpeg,.png">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary w-100" id="btnScanDokumen">
                                        <i class="fa-solid fa-expand me-1"></i> Scan dan isi form
                                    </button>
                                </div>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="scanTimpa">
                                <label class="form-check-label" for="scanTimpa" style="font-size:.8rem;">Timpa kolom yang
                                    sudah terisi</label>
                            </div>
                            <div class="scan-status" id="scanStatus"></div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-section-title">Data lamaran</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Nama lengkap <span class="required">*</span></label>
                                <input type="text" name="nama_lengkap" class="form-control-custom"
                                    placeholder="Nama lengkap pelamar" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Email <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control-custom"
                                    placeholder="email@contoh.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">No. telepon <span class="required">*</span></label>
                                <input type="text" name="no_telepon" class="form-control-custom"
                                    placeholder="+62 812-xxxx-xxxx" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Tanggal melamar <span class="required">*</span></label>
                                <input type="date" name="tanggal_melamar" class="form-control-custom"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Divisi <span class="required">*</span></label>
                                <select name="divisi" class="form-select-custom" required>
                                    <option value="">Pilih divisi</option>
                                    @foreach ($dataDivisi as $div)
                                        <option value="{{ $div }}">{{ $div }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Posisi dilamar <span class="required">*</span></label>
                                <select name="jabatan" class="form-select-custom" required>
                                    <option value="">Pilih posisi</option>
                                    @foreach ($dataJabatan as $jab)
                                        <option value="{{ $jab }}">{{ $jab }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Detail posisi <small
                                        class="text-muted fw-normal">(opsional)</small></label>
                                <input type="text" name="detail_jabatan" class="form-control-custom"
                                    placeholder="Contoh: Frontend Developer, Staff Admin...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Sumber lamaran <span class="required">*</span></label>
                                <input type="text" name="sumber_lamaran" class="form-control-custom"
                                    list="sumberList" placeholder="Pilih atau ketik sumber..." required>
                                <datalist id="sumberList">
                                    @foreach ($sumberList as $src)
                                        <option value="{{ $src }}">
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-12">
                                <div class="form-section-title">Data pribadi <small
                                        class="text-muted fw-normal">(opsional)</small></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">NIK (KTP)</label>
                                <input type="text" name="nik_ktp" class="form-control-custom" maxlength="20"
                                    placeholder="16 digit NIK">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">LinkedIn</label>
                                <input type="text" name="linkedin_url" class="form-control-custom"
                                    placeholder="https://linkedin.com/in/...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Tempat lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control-custom"
                                    placeholder="Kota kelahiran">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Tanggal lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control-custom">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Jenis kelamin</label>
                                <select name="jenis_kelamin" class="form-select-custom">
                                    <option value="">-- Pilih --</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Status pernikahan</label>
                                <select name="status_pernikahan" class="form-select-custom">
                                    <option value="">-- Pilih --</option>
                                    <option value="Belum Kawin">Belum Kawin</option>
                                    <option value="Kawin">Kawin</option>
                                    <option value="Cerai Hidup">Cerai Hidup</option>
                                    <option value="Cerai Mati">Cerai Mati</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Agama</label>
                                <select name="agama" class="form-select-custom">
                                    <option value="">-- Pilih --</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Domisili</label>
                                <input type="text" name="domisili" class="form-control-custom"
                                    placeholder="Kota, Provinsi">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Alamat lengkap</label>
                                <textarea name="alamat" class="form-control-custom" rows="2"
                                    placeholder="Alamat sesuai KTP / tempat tinggal saat ini"></textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-section-title">Pendidikan dan pengalaman <small
                                        class="text-muted fw-normal">(opsional)</small></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Pendidikan terakhir</label>
                                <select name="pendidikan_terakhir" class="form-select-custom">
                                    <option value="">-- Pilih --</option>
                                    @foreach (\App\Models\Pelamar::PENDIDIKAN as $pend)
                                        <option value="{{ $pend }}">{{ $pend }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-custom">Institusi</label>
                                <input type="text" name="institusi" class="form-control-custom"
                                    placeholder="Nama universitas / sekolah">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-custom">IPK</label>
                                <input type="number" name="ipk" class="form-control-custom" step="0.01"
                                    min="0" max="4" placeholder="3.50">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Jurusan</label>
                                <input type="text" name="jurusan" class="form-control-custom"
                                    placeholder="Contoh: Teknik Informatika">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Pengalaman kerja <span
                                        class="unit-hint">tahun</span></label>
                                <input type="number" name="pengalaman_tahun" class="form-control-custom" min="0"
                                    max="50" placeholder="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Gaji diharapkan <span class="unit-hint">/
                                        bulan</span></label>
                                <input type="text" name="gaji_diharapkan" class="form-control-custom input-rupiah"
                                    placeholder="Rp 8.000.000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Notice period</label>
                                <input type="text" name="notice_period" class="form-control-custom"
                                    placeholder="Contoh: Bisa langsung / 1 bulan">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Keahlian utama</label>
                                <input type="text" name="keahlian" class="form-control-custom"
                                    placeholder="Pisahkan dengan koma, contoh: React, JavaScript, Tailwind">
                            </div>

                            <div class="col-12">
                                <div class="form-section-title">Dokumen <small
                                        class="text-muted fw-normal">(opsional)</small></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">CV</label>
                                <input type="file" name="cv" class="form-control-custom"
                                    accept=".pdf,.doc,.docx">
                                <div class="form-hint">PDF, DOC, DOCX. Maks. 5MB.</div>
                                <div class="file-info" id="info_cv"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Portofolio</label>
                                <input type="file" name="portofolio" class="form-control-custom"
                                    accept=".pdf,.doc,.docx,.zip">
                                <div class="form-hint">PDF, DOC, DOCX, ZIP. Maks. 10MB.</div>
                                <div class="file-info" id="info_portofolio"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Foto</label>
                                <input type="file" name="foto" class="form-control-custom" accept="image/*">
                                <div class="form-hint">Maks. 3MB.</div>
                                <div class="file-info" id="info_foto"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">KTP</label>
                                <input type="file" name="ktp" class="form-control-custom"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-hint">PDF/JPG/PNG. Maks. 5MB.</div>
                                <div class="file-info" id="info_ktp"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom">Ijazah</label>
                                <input type="file" name="ijazah" class="form-control-custom"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-hint">PDF/JPG/PNG. Maks. 5MB.</div>
                                <div class="file-info" id="info_ijazah"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Catatan HR</label>
                                <textarea name="catatan_hr" class="form-control-custom" rows="2"
                                    placeholder="Catatan tambahan tentang pelamar..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanPelamar">
                            <i class="fa-solid fa-save me-1"></i> <span id="pfSubmitLabel">Simpan pelamar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLihatProfil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog pd-dialog modal-dialog-centered">
            <div class="modal-content p-0" id="profilContent"></div>
        </div>
    </div>

    <div class="modal fade" id="modalPsikotest" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-brain"></i> Psikotest — <span id="ptNama">-</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="pt-tabs" id="ptTabs"></div>
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <form id="formPsikotest" enctype="multipart/form-data">@csrf<div id="ptFormWrap"></div>
                            </form>
                        </div>
                        <div class="col-lg-7">
                            <div class="pv-box" id="ptPreview"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#1f2937,#374151);color:#fff;">
                    <h5 class="modal-title" style="color:#fff;"><i class="fa-solid fa-file-lines"
                            style="color:#fff;"></i> <span id="pvJudul">Dokumen</span></h5>
                    <div class="d-flex gap-2">
                        <a href="#" id="pvUnduh" class="btn btn-sm btn-outline-light" target="_blank"><i
                                class="fa-solid fa-download"></i> Unduh</a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <div class="pv-box" id="pvBody" style="height:75vh;border:none;border-radius:0;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalJadwalInterview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-regular fa-calendar"></i> Jadwalkan interview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formJadwalInterview">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="interviewPelamarId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-custom">Nama pelamar</label>
                                <input type="text" id="interviewNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Tahap interview <span class="required">*</span></label>
                                <select name="tahap_interview" class="form-select-custom" required>
                                    @foreach (\App\Models\Pelamar::TAHAP_INTERVIEW as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Tanggal <span class="required">*</span></label>
                                <input type="date" name="tanggal" class="form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Waktu <span class="required">*</span></label>
                                <input type="time" name="waktu" class="form-control-custom" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Metode interview <span class="required">*</span></label>
                                <select name="metode_interview" class="form-select-custom" id="metodeInterview" required>
                                    @foreach (\App\Models\Pelamar::METODE_INTERVIEW as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12" id="linkMeetingWrapper">
                                <label class="form-label-custom">Link meeting</label>
                                <input type="url" name="link_meeting" class="form-control-custom"
                                    placeholder="https://zoom.us/j/...">
                            </div>
                            <div class="col-12" id="alamatWrapper" style="display:none;">
                                <label class="form-label-custom">Alamat / ruangan</label>
                                <input type="text" name="lokasi_interview" class="form-control-custom"
                                    placeholder="Lantai 3, Ruang Meeting A">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Interviewer <span class="required">*</span></label>
                                <select name="interviewer[]" class="form-select-custom" multiple required
                                    style="min-height:120px;">
                                    @foreach ($Interviewer as $hrUser)
                                        <option value="{{ $hrUser->nama_lengkap }}">{{ $hrUser->nama_lengkap }} -
                                            {{ $hrUser->jabatan }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint">Tekan Ctrl/Cmd untuk memilih lebih dari satu interviewer</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Catatan (opsional)</label>
                                <textarea name="catatan" class="form-control-custom" rows="2" placeholder="Catatan untuk pelamar..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notif_email"
                                        id="notifInterview" value="1" checked>
                                    <label class="form-check-label" for="notifInterview" style="font-size:.85rem;">Kirim
                                        notifikasi email ke pelamar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan
                            jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKirimOffer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-file-signature"></i> Kirim penawaran (offering)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formKirimOffer" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="offerPelamarId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Nama pelamar</label>
                                <input type="text" id="offerNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Posisi ditawarkan</label>
                                <input type="text" id="offerJabatan" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <div class="form-section-title">Komponen gaji</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Gaji pokok (bulanan) <span
                                        class="required">*</span></label>
                                <input type="text" name="gaji_ditawarkan" class="form-control-custom input-rupiah"
                                    placeholder="Rp 8.000.000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Status karyawan</label>
                                <select name="status_kepegawaian" class="form-select-custom">
                                    @foreach (\App\Models\Pelamar::STATUS_KEPEGAWAIAN as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-section-title">Tunjangan wajib (harian)</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Tunjangan makan <span class="required">*</span> <span
                                        class="unit-hint">/ hari</span></label>
                                <input type="text" name="tunjangan_makan" class="form-control-custom input-rupiah"
                                    placeholder="Rp 25.000" required>
                                <div class="form-hint">Nominal per hari kerja.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Tunjangan transportasi <span class="required">*</span>
                                    <span class="unit-hint">/ hari</span></label>
                                <input type="text" name="tunjangan_transport" class="form-control-custom input-rupiah"
                                    placeholder="Rp 20.000" required>
                                <div class="form-hint">Nominal per hari kerja.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Tanggal mulai kerja <span
                                        class="required">*</span></label>
                                <input type="date" name="tanggal_mulai_kerja" class="form-control-custom" required>
                            </div>
                            <div class="col-12">
                                <div class="form-section-title">Benefit dan pesan (opsional)</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Benefit lainnya</label>
                                <textarea name="benefit_lainnya" class="form-control-custom" rows="2"
                                    placeholder="BPJS Kesehatan, BPJS Ketenagakerjaan, Tunjangan Pulsa, dll..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Pesan tambahan</label>
                                <textarea name="pesan_tambahan" class="form-control-custom" rows="2"
                                    placeholder="Selamat! Kami sangat terkesan dengan kualifikasi Anda..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Lampiran surat offer (PDF) <span
                                        class="required">*</span></label>
                                <input type="file" name="lampiran_offer" class="form-control-custom" accept=".pdf"
                                    required>
                                <div class="form-hint">Pastikan file PDF sudah dikunci dengan password sebelum diunggah.
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Password dokumen <span class="required">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="password_offer" id="passwordOffer"
                                        class="form-control-custom"
                                        placeholder="Password yang Anda gunakan untuk mengunci PDF" required>
                                    <button type="button" class="btn btn-outline-secondary" id="btnAcakPassword"
                                        style="border-radius:0 .5rem .5rem 0;">
                                        <i class="fa-solid fa-shuffle"></i> Acak
                                    </button>
                                </div>
                                <div class="form-hint">Password ini ditampilkan di body email agar pelamar bisa membuka
                                    dokumen.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> Kirim
                            offering</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalOnboarding" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus"></i> Proses onboarding karyawan baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formOnboarding">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="onboardPelamarId">
                    <div class="modal-body">
                        <div class="stepper">
                            <div class="step done">
                                <div class="step-circle"><i class="fa-solid fa-check"></i></div>
                                <div class="step-label">Lamaran</div>
                            </div>
                            <div class="step done">
                                <div class="step-circle"><i class="fa-solid fa-check"></i></div>
                                <div class="step-label">Interview</div>
                            </div>
                            <div class="step done">
                                <div class="step-circle"><i class="fa-solid fa-check"></i></div>
                                <div class="step-label">Offer</div>
                            </div>
                            <div class="step active">
                                <div class="step-circle">4</div>
                                <div class="step-label">Onboarding</div>
                            </div>
                            <div class="step">
                                <div class="step-circle">5</div>
                                <div class="step-label">Aktif</div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">NIK karyawan</label>
                                <input type="text" name="nik_karyawan" id="onboardNik" class="form-control-custom"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Tanggal mulai kerja <span
                                        class="required">*</span></label>
                                <input type="date" name="tanggal_mulai_kerja" id="onboardTanggalMulai"
                                    class="form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Divisi</label>
                                <input type="text" id="onboardDivisi" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Jabatan</label>
                                <input type="text" id="onboardJabatan" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Atasan langsung <span class="required">*</span></label>
                                <input type="text" name="atasan_langsung" class="form-control-custom"
                                    placeholder="Nama atasan langsung" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Status kepegawaian</label>
                                <select name="status_kepegawaian" class="form-select-custom">
                                    @foreach (\App\Models\Pelamar::STATUS_KEPEGAWAIAN as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Checklist onboarding</label>
                                <div class="border rounded p-3" style="background:#fafbfc;">
                                    @foreach (\App\Models\Pelamar::CHECKLIST_ONBOARDING_DEFAULT as $key => $label)
                                        <div class="form-check {{ !$loop->last ? 'mb-2' : '' }}">
                                            <input class="form-check-input" type="checkbox" name="checklist_onboarding[]"
                                                value="{{ $key }}" id="ob_{{ $key }}"
                                                {{ in_array($key, ['kontrak_ditandatangani', 'dokumen_pribadi']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ob_{{ $key }}"
                                                style="font-size:.85rem;">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-user-check me-1"></i> Proses
                            jadi karyawan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLanjutTahap" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-arrow-right-to-bracket"></i> Lanjut ke tahapan
                        berikutnya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formLanjutTahap">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="lanjutPelamarId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-custom">Pelamar</label>
                                <input type="text" id="lanjutNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Tahap saat ini</label>
                                <input type="text" id="lanjutTahapSaatIni" class="form-control-custom" readonly
                                    style="background:#fef3c7;color:#d97706;font-weight:600;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Lanjut ke tahap <span class="required">*</span></label>
                                <select name="tahap_ke" id="lanjutTahapKe" class="form-select-custom" required>
                                    @foreach (\App\Models\Pelamar::TAHAP as $key => $label)
                                        @if (!in_array($key, ['applied', 'rejected']))
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Penilaian (opsional)</label>
                                <div class="d-flex gap-1">
                                    @for ($i = 1; $i <= 4; $i++)
                                        <input type="radio" class="btn-check" name="rating"
                                            id="r{{ $i }}" value="{{ $i }}" autocomplete="off">
                                        <label class="btn btn-outline-warning btn-sm"
                                            for="r{{ $i }}">{{ $i }}</label>
                                    @endfor
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Catatan / feedback (opsional)</label>
                                <textarea name="keterangan" class="form-control-custom" rows="2" placeholder="Tulis catatan evaluasi...">{{ auth()->user()->username }} - {{ auth()->user()->jabatan }} : </textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notif_email" id="notifPelamar"
                                        value="1">
                                    <label class="form-check-label" for="notifPelamar" style="font-size:.85rem;">Kirim
                                        notifikasi email ke pelamar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-right me-1"></i>
                            Lanjutkan tahapan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKirimEmail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-regular fa-envelope"></i> Kirim email ke pelamar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formKirimEmail" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="emailPelamarId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">Penerima</label>
                                <input type="text" id="emailNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Email</label>
                                <input type="email" id="emailAlamat" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Subjek <span class="required">*</span></label>
                                <input type="text" name="subjek" class="form-control-custom"
                                    placeholder="Subjek email..." required>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Isi email <span class="required">*</span></label>
                                <textarea name="isi_email" class="form-control-custom" rows="6" placeholder="Tulis isi email di sini..."
                                    required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Lampiran (opsional)</label>
                                <input type="file" name="lampiran[]" class="form-control-custom" multiple>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>
                            Kirim email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTolak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-circle-xmark" style="color:var(--danger);"></i>
                        Tolak lamaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTolak">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="tolakPelamarId">
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <div class="confirm-icon"><i class="fa-solid fa-user-xmark"></i></div>
                            <div class="confirm-title">Yakin ingin menolak pelamar ini?</div>
                            <div class="confirm-desc">Status pelamar akan berubah menjadi "Ditolak".</div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <label class="form-label-custom">Pelamar</label>
                                <input type="text" id="tolakNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Alasan penolakan (opsional)</label>
                                <select name="alasan_penolakan" class="form-select-custom" id="alasanTolak">
                                    <option value="">-- Pilih alasan --</option>
                                    @foreach (\App\Models\Pelamar::ALASAN_PENOLAKAN as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12" id="alasanLainWrapper" style="display:none;">
                                <label class="form-label-custom">Alasan lainnya</label>
                                <textarea name="alasan_lainnya" class="form-control-custom" rows="2"
                                    placeholder="Jelaskan alasan penolakan..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Catatan internal (opsional)</label>
                                <textarea name="catatan_internal" class="form-control-custom" rows="2"
                                    placeholder="Catatan untuk tim HR..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notif_email"
                                        id="kirimNotifTolak" value="1">
                                    <label class="form-check-label" for="kirimNotifTolak"
                                        style="font-size:.85rem;">Kirim email notifikasi penolakan ke pelamar</label>
                                </div>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="simpan_talent_pool"
                                        id="simpanTalent" value="1">
                                    <label class="form-check-label" for="simpanTalent"
                                        style="font-size:.85rem;">Simpan ke talent pool untuk lowongan di masa
                                        depan</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-xmark me-1"></i> Tolak
                            lamaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="confirm-icon"><i class="fa-solid fa-trash"></i></div>
                    <div class="confirm-title">Hapus data pelamar?</div>
                    <div class="confirm-desc mb-3">Data pelamar <strong id="hapusNama">-</strong> akan dihapus permanen
                        dan tidak dapat dikembalikan.</div>
                </div>
                <div class="modal-footer justify-content-center border-top-0 pt-0">
                    <input type="hidden" id="hapusPelamarId">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiHapus"><i
                            class="fa-solid fa-trash me-1"></i> Ya, hapus</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNilaiPelamar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-star text-warning"></i> Beri penilaian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNilaiPelamar">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="nilaiPelamarId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Nama pelamar</label>
                            <input type="text" id="nilaiNama" class="form-control-custom" readonly
                                style="background:#f9fafb;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Masukkan ke folder <span class="required">*</span></label>
                            <select name="folder_id" id="nilaiFolderId" class="form-select-custom" required>
                                <option value="">-- Pilih folder --</option>
                            </select>
                            <div class="form-hint">Pelamar akan dimasukkan ke folder ini. Jika Anda sudah menilai di tahap yang sama, penilaian Anda diperbarui.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Penilaian (1–4 bintang) <span
                                    class="required">*</span></label>
                            <div class="d-flex gap-2 fs-2" style="cursor:pointer;" id="starContainerNilai">
                                <span class="star-nilai" data-value="1" style="color:#ddd;">☆</span>
                                <span class="star-nilai" data-value="2" style="color:#ddd;">☆</span>
                                <span class="star-nilai" data-value="3" style="color:#ddd;">☆</span>
                                <span class="star-nilai" data-value="4" style="color:#ddd;">☆</span>
                            </div>
                            <input type="hidden" name="rating" id="ratingInputNilai" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Catatan</label>
                            <textarea name="catatan" class="form-control-custom" rows="3" placeholder="Catatan evaluasi pelamar..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">File penilaian (PDF/DOC)</label>
                            <input type="file" name="file_penilaian" class="form-control-custom"
                                accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan
                            penilaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCreateFolder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-folder-plus"></i> Buat folder baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCreateFolder">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Nama folder <span class="required">*</span></label>
                            <input type="text" name="nama" class="form-control-custom"
                                placeholder="Masukkan nama folder" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Parent folder (opsional)</label>
                            <select name="parent_id" id="parentFolderSelectCreate" class="form-select-custom">
                                <option value="">-- Root folder --</option>
                            </select>
                            <div class="form-hint">Kosongkan jika ingin membuat folder di root.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Buat
                            folder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditFolder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square"></i> Edit folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditFolder">
                    @csrf
                    <input type="hidden" name="folder_id" id="editFolderId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Nama folder <span class="required">*</span></label>
                            <input type="text" name="nama" id="editFolderName" class="form-control-custom"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Parent folder (opsional)</label>
                            <select name="parent_id" id="parentFolderSelectEdit" class="form-select-custom">
                                <option value="">-- Root folder --</option>
                            </select>
                            <div class="form-hint">Kosongkan jika ingin memindahkan folder ke root.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan
                            perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDeleteFolder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="confirm-icon"><i class="fa-solid fa-trash"></i></div>
                    <div class="confirm-title">Hapus folder?</div>
                    <div class="confirm-desc mb-3">Folder <strong id="deleteFolderName">-</strong> akan dihapus
                        permanen.</div>
                </div>
                <div class="modal-footer justify-content-center border-top-0 pt-0">
                    <input type="hidden" id="deleteFolderId">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusFolder"><i
                            class="fa-solid fa-trash me-1"></i> Ya, hapus</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPenilaian" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-clipboard-list"></i> Hasil penilaian - <span id="pnNama">-</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="pnBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnPnTambah"><i class="fa-regular fa-star me-1"></i> Tambah penilaian</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = '{{ url('HR-dashboard/hire') }}';
        const URL_EXPORT = '{{ route('HR.hire.export') }}';
        const URL_FOLDER_LIST = '{{ route('HR.hire.folder-list') }}';
        const URL_FOLDER_STORE = '{{ route('HR.folders.store') }}';
        const URL_PELAMAR_MOVE = '{{ route('HR.folders.pelamar.move') }}';
        const KATEGORI = @json($kategoriPsikotest);
        const INFO = @json($infoPsikotest);
        const SKRIP = {
            chart: 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
            jszip: 'https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js',
            docx: 'https://cdn.jsdelivr.net/npm/docx-preview@0.3.3/dist/docx-preview.min.js',
            tesseract: 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js'
        };
        const WARNA = ['#0062ff', '#7c3aed', '#00b573', '#ff9800', '#00bcd4', '#f44336', '#0d9488', '#db2777', '#64748b'];
        const WARNA_TAHAP = {
            applied: '#2563eb',
            screening: '#4f46e5',
            psikotest: '#0d9488',
            interview: '#d97706',
            offer: '#7c3aed',
            hired: '#16a34a',
            rejected: '#dc2626'
        };
        const PT_SINGKAT = {
            mbti: 'MBTI',
            riasec: 'RIA',
            logika_numerik: 'LN',
            analogi_verbal: 'AV'
        };
        const AKSI_LABEL = {
            created: 'Pelamar ditambahkan',
            updated: 'Data diperbarui',
            moved: 'Pindah tahap',
            rejected: 'Lamaran ditolak',
            interview_scheduled: 'Interview dijadwalkan',
            offer_sent: 'Offer dikirim',
            onboarded: 'Menjadi karyawan',
            email_sent: 'Email dikirim',
            note: 'Catatan ditambahkan',
            rated: 'Penilaian diberikan',
            psikotest: 'Hasil psikotest',
            psikotest_dihapus: 'Hasil psikotest dihapus'
        };

        const S = {
            folder: 'all',
            folderName: 'Semua Pelamar',
            page: 1,
            analitikStale: true
        };
        let daftarFolder = [];
        let charts = {};
        let skripCache = {};
        let PT = {
            pelamarId: null,
            data: {},
            aktif: 'mbti'
        };
        let previewToken = 0;
        let blobAktif = null;

        function esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[c]);
        }

        function toast(msg, type = 'success') {
            const icon = type === 'success' ? 'circle-check' : 'circle-xmark';
            const el = $(`<div class="toast-msg ${type}"><i class="fa-solid fa-${icon}"></i> ${esc(msg)}</div>`);
            $('#toastContainer').append(el);
            setTimeout(() => el.fadeOut(300, function() {
                $(this).remove();
            }), 4000);
        }

        function bukaModal(id) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
        }

        function tutupModal(id) {
            const m = bootstrap.Modal.getInstance(document.getElementById(id));
            if (m) m.hide();
        }

        function gantiModal(dariId, aksi) {
            const el = document.getElementById(dariId);
            if (el && el.classList.contains('show')) {
                $(el).one('hidden.bs.modal', aksi);
                bootstrap.Modal.getInstance(el).hide();
            } else {
                aksi();
            }
        }

        function debounce(fn, ms) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), ms);
            };
        }

        function pesanError(xhr) {
            if (xhr && xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                    const pertama = Object.values(xhr.responseJSON.errors)[0];
                    if (pertama && pertama[0]) return pertama[0];
                }
                if (xhr.responseJSON.message) return xhr.responseJSON.message;
            }
            return 'Terjadi kesalahan pada server.';
        }

        function rupiah(n) {
            return n || n === 0 ? 'Rp ' + Number(n).toLocaleString('id-ID') : '';
        }

        function tanggalIndo(s) {
            if (!s) return '';
            const [y, m, d] = String(s).split('-');
            return new Date(y, m - 1, d).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }

        function bintang(n) {
            const penuh = Math.max(0, Math.min(4, Math.round(n)));
            return '★'.repeat(penuh) + `<span class="off">${'☆'.repeat(4 - penuh)}</span>`;
        }

        function levelSkor(v) {
            if (v === null || v === undefined || v === '' || isNaN(v)) return '';
            const l = INFO.level.find(x => Number(v) >= x.min);
            return l ? l.label : '';
        }

        function tipeDariNama(nama) {
            const ext = String(nama || '').split('?')[0].split('.').pop().toLowerCase();
            if (ext === 'pdf') return 'pdf';
            if (['jpg', 'jpeg', 'png'].includes(ext)) return 'gambar';
            if (ext === 'docx') return 'docx';
            if (ext === 'doc') return 'doc';
            return null;
        }

        function muatSkrip(src) {
            if (!skripCache[src]) {
                skripCache[src] = new Promise((resolve, reject) => {
                    const s = document.createElement('script');
                    s.src = src;
                    s.onload = resolve;
                    s.onerror = () => {
                        delete skripCache[src];
                        reject(new Error('Gagal memuat pustaka. Periksa koneksi internet.'));
                    };
                    document.head.appendChild(s);
                });
            }
            return skripCache[src];
        }

        function postForm(form, url, modalId, sukses) {
            const $b = $(form).find('button[type="submit"]');
            const html = $b.html();
            $b.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
            const fd = new FormData(form);
            if (!fd.has('_token')) fd.append('_token', $('meta[name="csrf-token"]').attr('content'));

            return $.ajax({
                    url: url,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                })
                .done(res => {
                    if (res.success) {
                        toast(res.message);
                        if (modalId) tutupModal(modalId);
                        (sukses || refreshSemua)(res);
                    } else {
                        toast(res.message || 'Terjadi kesalahan.', 'danger');
                    }
                })
                .fail(xhr => toast(pesanError(xhr), 'danger'))
                .always(() => $b.prop('disabled', false).html(html));
        }

        function refreshSemua() {
            muatData(S.page);
            loadFolderList();
            S.analitikStale = true;
            if ($('#tabAnalitik').hasClass('active')) muatAnalitik();
        }

        function filterSaatIni() {
            return {
                search: $('#fSearch').val(),
                jabatan: $('#fJabatan').val(),
                tahap: $('#fTahap').val(),
                sumber: $('#fSumber').val()
            };
        }

        function adaFilter() {
            return Object.values(filterSaatIni()).some(v => v);
        }

        function barisHtml(r) {
            const kelasSumber = String(r.sumber_lamaran || '').toLowerCase().replace(/[^a-z]+/g, '-');
            const dasarRating = `data-id="${r.id}" data-nama="${esc(r.nama_lengkap)}"`;
            const rating = r.avg_rating ?
                `<a href="#" class="rating-cell-link btn-lihat-penilaian" ${dasarRating}><div class="table-rating">${bintang(r.avg_rating)}</div><div class="muted-small">${r.avg_rating} · ${r.total_penilai} penilai</div></a>` :
                '<span class="muted-italic">Belum dinilai</span>';
            const chips = Object.keys(KATEGORI).map(k =>
                `<span class="pt-chip ${r.psikotest.includes(k) ? 'on' : ''}" title="${esc(KATEGORI[k])}">${PT_SINGKAT[k]}</span>`
            ).join('');
            const t = r.tahap_rekrutmen;
            const dasar = `data-id="${r.id}" data-nama="${esc(r.nama_lengkap)}"`;

            let menu = `
                <a class="dropdown-item btn-lihat-cv" href="#" ${dasar} data-cv="${esc(r.cv_url || '')}"><i class="fa-regular fa-file-pdf"></i> Lihat CV</a>
                <a class="dropdown-item btn-nilai-pelamar" href="#" ${dasar}><i class="fa-regular fa-star"></i> Beri penilaian</a>
                <a class="dropdown-item btn-lihat-penilaian" href="#" ${dasar}><i class="fa-solid fa-clipboard-list"></i> Lihat hasil penilaian</a>
                <a class="dropdown-item btn-psikotest" href="#" ${dasar}><i class="fa-solid fa-brain"></i> Hasil psikotest</a>
                <div class="dropdown-divider"></div>`;

            if (['applied', 'screening', 'psikotest', 'interview'].includes(t)) {
                menu +=
                    `<a class="dropdown-item btn-jadwal-interview" href="#" ${dasar}><i class="fa-regular fa-calendar"></i> Jadwalkan interview</a>`;
            }
            if (r.bisa_lanjut) {
                menu +=
                    `<a class="dropdown-item btn-lanjut-tahap" href="#" ${dasar} data-tahap-saat-ini="${esc(r.tahap_label)}" data-tahap-berikutnya="${esc(r.tahap_berikutnya || '')}"><i class="fa-solid fa-arrow-right"></i> Lanjut tahap</a>`;
            }
            if (['interview', 'offer'].includes(t)) {
                menu +=
                    `<a class="dropdown-item btn-kirim-offer" href="#" ${dasar} data-jabatan="${esc(r.jabatan)}"><i class="fa-solid fa-file-signature"></i> Kirim offer</a>`;
            }
            if (t === 'offer') {
                menu +=
                    `<a class="dropdown-item btn-onboarding" href="#" ${dasar} data-divisi="${esc(r.divisi)}" data-jabatan="${esc(r.jabatan)}" data-mulai="${esc(r.mulai_kerja || '')}"><i class="fa-solid fa-user-check"></i> Proses onboarding</a>`;
            }
            menu += `
                <a class="dropdown-item btn-kirim-email" href="#" ${dasar} data-email="${esc(r.email)}"><i class="fa-regular fa-envelope"></i> Kirim email</a>
                <a class="dropdown-item btn-edit-pelamar" href="#" data-id="${r.id}"><i class="fa-regular fa-pen-to-square"></i> Edit data</a>
                <div class="dropdown-divider"></div>`;
            if (r.bisa_lanjut) {
                menu +=
                    `<a class="dropdown-item danger btn-tolak" href="#" ${dasar}><i class="fa-solid fa-user-xmark"></i> Tolak lamaran</a>`;
            }
            menu +=
                `<a class="dropdown-item danger btn-hapus" href="#" ${dasar}><i class="fa-regular fa-trash-can"></i> Hapus</a>`;

            return `
                <tr draggable="true" data-id="${r.id}">
                    <td>
                        <div class="applicant-info btn-lihat-profil" data-id="${r.id}" role="button" tabindex="0">
                            <div class="applicant-avatar av-${r.id % 6}">${esc(r.inisial)}</div>
                            <div>
                                <div class="applicant-name">${esc(r.nama_lengkap)}</div>
                                <div class="applicant-email">${esc(r.email)}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">${esc(r.jabatan)}</div>
                        <div class="position-dept">${esc(r.detail_jabatan || r.divisi || '')}</div>
                    </td>
                    <td><span class="source-badge ${kelasSumber}">${esc(r.sumber_lamaran || '-')}</span></td>
                    <td>${esc(r.tanggal_melamar || '-')}</td>
                    <td>${rating}</td>
                    <td><div class="pt-chips">${chips}</div></td>
                    <td><span class="stage-badge stage-${esc(t)}">${esc(r.tahap_label)}</span></td>
                    <td>
                        <div class="action-cell">
                            <button type="button" class="btn-quick-action btn-lihat-profil" data-id="${r.id}" title="Lihat profil"><i class="fa-regular fa-eye"></i></button>
                            <div class="dropdown">
                                <button type="button" class="btn-action-dropdown" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <div class="dropdown-menu dropdown-menu-end action-dropdown-menu">${menu}</div>
                            </div>
                        </div>
                    </td>
                </tr>`;
        }

        function renderPaginasi(meta) {
            const $nav = $('#paginationNav').empty();
            $('#paginationInfo').html(meta.total > 0 ?
                `Menampilkan <span class="fw-semibold">${meta.from}</span>–<span class="fw-semibold">${meta.to}</span> dari <span class="fw-semibold">${meta.total}</span> pelamar` :
                'Tidak ada pelamar');

            if (meta.last_page <= 1) return;

            const item = (label, page, aktif, mati) =>
                `<li class="page-item ${aktif ? 'active' : ''} ${mati ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${page}">${label}</a></li>`;

            $nav.append(item('&laquo;', meta.current_page - 1, false, meta.current_page === 1));

            let terakhir = 0;
            for (let p = 1; p <= meta.last_page; p++) {
                if (p === 1 || p === meta.last_page || Math.abs(p - meta.current_page) <= 2) {
                    if (p - terakhir > 1) $nav.append(
                        '<li class="page-item disabled"><span class="page-link">…</span></li>');
                    $nav.append(item(p, p, p === meta.current_page, false));
                    terakhir = p;
                }
            }

            $nav.append(item('&raquo;', meta.current_page + 1, false, meta.current_page === meta.last_page));
        }

        function muatData(page) {
            S.page = page || 1;
            const filter = filterSaatIni();
            $('#btnExport').attr('href', URL_EXPORT + '?' + $.param(filter));
            $('#tabelBody').html(
                '<tr><td colspan="8" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Memuat data pelamar...</td></tr>'
            );

            return $.getJSON(`${BASE_URL}/data`, Object.assign({
                    folder: S.folder,
                    page: S.page
                }, filter))
                .done(res => {
                    if (res.meta.last_page >= 1 && S.page > res.meta.last_page) {
                        muatData(res.meta.last_page);
                        return;
                    }

                    if (!res.data.length) {
                        $('#tabelBody').html(
                            '<tr><td colspan="8" class="text-center py-5 text-muted">Belum ada pelamar yang sesuai.</td></tr>'
                        );
                    } else {
                        $('#tabelBody').html(res.data.map(barisHtml).join(''));
                    }

                    renderPaginasi(res.meta);
                    const lokasi = S.folder === 'all' ? '' : ` di "${esc(S.folderName)}"`;
                    $('#tableInfo').html(`<span class="fw-semibold">${res.meta.total}</span> pelamar${lokasi}`);

                    if (S.folder === 'all' && !adaFilter()) $('#countAll').text(res.meta.total);
                })
                .fail(xhr => {
                    $('#tabelBody').html(
                        `<tr><td colspan="8" class="text-center py-5 text-danger">${esc(pesanError(xhr))}</td></tr>`
                    );
                });
        }

        function urutFolder(a, b) {
            if (a.is_pinned && !b.is_pinned) return -1;
            if (!a.is_pinned && b.is_pinned) return 1;
            return a.nama.localeCompare(b.nama);
        }

        function keturunanFolder(id, folders) {
            const hasil = [String(id)];
            folders.filter(f => f.parent_id == id).forEach(c => hasil.push(...keturunanFolder(c.id, folders)));
            return hasil;
        }

        function isiOpsiFolder(selector, folders, excludeId) {
            const select = $(selector);
            select.find('option:not(:first)').remove();
            const abaikan = excludeId ? keturunanFolder(excludeId, folders) : [];

            const tambah = (list, level) => {
                list.slice().sort((a, b) => a.nama.localeCompare(b.nama)).forEach(f => {
                    if (abaikan.includes(String(f.id))) return;
                    select.append($('<option>').val(f.id).text('—'.repeat(level) + (level ? ' ' : '') + f
                        .nama));
                    tambah(folders.filter(c => c.parent_id == f.id), level + 1);
                });
            };

            tambah(folders.filter(f => !f.parent_id), 0);
        }

        function loadFolderList() {
            return $.getJSON(URL_FOLDER_LIST)
                .done(res => {
                    if (res.success) {
                        daftarFolder = res.data || [];
                        renderFolderSidebar(daftarFolder, res.unassigned_count);
                    }
                })
                .fail(() => {
                    $('#hireFolderTree').html(
                        '<li class="text-center text-danger py-2" style="font-size:.8rem;">Gagal memuat folder</li>'
                    );
                });
        }

        function renderFolderSidebar(folders, unassignedCount) {
            $('#countUnassigned').text(unassignedCount);
            const tree = $('#hireFolderTree').empty();

            if (!folders.length) {
                tree.html(
                    `<li class="text-center text-muted py-3" style="font-size:.8rem;"><i class="fa-solid fa-folder-open"></i><br>Belum ada folder<br><small><a href="{{ route('HR.folders.index') }}">Buat folder baru</a></small></li>`
                );
                return;
            }

            renderFolderTree(folders.filter(f => !f.parent_id).sort(urutFolder), folders, tree);

            if (S.folder !== 'all' && S.folder !== 'unassigned') {
                $(`#hireFolderTree .folder-list-link[data-folder-id="${S.folder}"]`).addClass('active');
            }
        }

        function renderFolderTree(current, all, container) {
            current.forEach(folder => {
                const children = all.filter(f => f.parent_id == folder.id).sort(urutFolder);
                const has = children.length > 0;
                const nama = esc(folder.nama);
                const toggle = has ?
                    `<span class="folder-toggle expanded" data-folder-toggle="${folder.id}"><i class="fa-solid fa-chevron-right"></i></span>` :
                    '<span style="width:16px;display:inline-block;"></span>';

                container.append(`
                    <li class="folder-list-item">
                        <a class="folder-list-link" data-folder-id="${folder.id}" data-nama="${nama}" data-parent="${folder.parent_id || ''}">
                            ${toggle}
                            <i class="fa-solid fa-folder folder-icon"></i>
                            ${folder.is_pinned ? '<i class="fa-solid fa-thumbtack pin-icon"></i>' : ''}
                            <span class="folder-label" title="${nama}">${nama}</span>
                            <span class="folder-count">${folder.pelamars_count || 0}</span>
                            <span class="folder-actions">
                                <button type="button" class="folder-action-btn btn-pin-folder" title="Pin/Unpin"><i class="fa-solid fa-thumbtack"></i></button>
                                <button type="button" class="folder-action-btn btn-edit-folder" title="Edit folder"><i class="fa-solid fa-pen"></i></button>
                                <button type="button" class="folder-action-btn danger btn-del-folder" title="Hapus folder"><i class="fa-solid fa-trash"></i></button>
                                <button type="button" class="folder-action-btn archive btn-arsip-folder" title="Arsipkan folder"><i class="fa-solid fa-box-archive"></i></button>
                            </span>
                        </a>
                        ${has ? `<ul class="folder-list-nested" id="folder-children-hire-${folder.id}"></ul>` : ''}
                    </li>`);

                if (has) renderFolderTree(children, all, $(`#folder-children-hire-${folder.id}`));
            });
        }

        function expandParentFolders(folderId, all) {
            const folder = all.find(f => f.id == folderId);
            if (!folder || !folder.parent_id) return;

            const nested = $(`#folder-children-hire-${folder.parent_id}`);
            if (nested.length) {
                nested.show();
                nested.closest('li').find('> .folder-list-link .folder-toggle').addClass('expanded');
            }

            expandParentFolders(folder.parent_id, all);
        }

        function filterByFolder(folderId, folderName) {
            S.folder = String(folderId);
            S.folderName = folderName;
            $('#hireSidebar .folder-list-link').removeClass('active');
            $(`#hireSidebar .folder-list-link[data-folder-id="${S.folder}"]`).addClass('active');
            $('#activeFolderName').text(folderName);
            $('#activeFolderIndicator').toggle(S.folder !== 'all');
            expandParentFolders(S.folder, daftarFolder);
            muatData(1);
        }

        function resetFolderFilter() {
            filterByFolder('all', 'Semua Pelamar');
        }

        function movePelamarToFolder(pelamarId, folderId) {
            $.post(URL_PELAMAR_MOVE, {
                    pelamar_id: pelamarId,
                    folder_id: folderId
                })
                .done(res => {
                    if (res.success) {
                        toast(res.message);
                        refreshSemua();
                    } else {
                        toast(res.message || 'Gagal memindahkan', 'danger');
                    }
                })
                .fail(xhr => toast(pesanError(xhr), 'danger'));
        }

        function tampilPesanPreview(target, ikon, teks, unduh) {
            $(target).html(
                `<div class="pv-pesan"><i class="fa-solid ${ikon}"></i>${teks}${unduh ? `<div class="mt-3"><a href="${esc(unduh)}" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-download me-1"></i> Unduh dokumen</a></div>` : ''}</div>`
            );
        }

        async function tampilkanPreview(target, o) {
            const $t = $(target);
            const token = ++previewToken;

            if (blobAktif) {
                URL.revokeObjectURL(blobAktif);
                blobAktif = null;
            }

            if (!o || (!o.url && !o.file)) {
                tampilPesanPreview(target, 'fa-file-circle-question', 'Belum ada dokumen untuk dipratinjau.');
                return;
            }

            const tipe = o.tipe || tipeDariNama(o.nama || (o.file && o.file.name));
            let src = o.url;
            if (o.file && (tipe === 'pdf' || tipe === 'gambar')) {
                blobAktif = URL.createObjectURL(o.file);
                src = blobAktif;
            }

            if (tipe === 'pdf') {
                $t.html(`<iframe class="pv-frame" src="${esc(src)}"></iframe>`);
            } else if (tipe === 'gambar') {
                $t.html(`<div class="pv-img"><img src="${esc(src)}" alt="Pratinjau dokumen"></div>`);
            } else if (tipe === 'docx') {
                $t.html(
                    '<div class="pv-pesan"><span class="spinner-border spinner-border-sm me-2"></span> Menyiapkan pratinjau dokumen Word...</div>'
                );
                try {
                    await muatSkrip(SKRIP.jszip);
                    await muatSkrip(SKRIP.docx);
                    const buf = o.file ? await o.file.arrayBuffer() : await (await fetch(o.url, {
                        credentials: 'same-origin'
                    })).arrayBuffer();
                    if (token !== previewToken) return;
                    $t.html('<div class="pv-docx"></div>');
                    await window.docx.renderAsync(buf, $t.find('.pv-docx')[0], null, {
                        inWrapper: true,
                        ignoreWidth: false
                    });
                } catch (e) {
                    if (token !== previewToken) return;
                    tampilPesanPreview(target, 'fa-file-word', 'Dokumen Word tidak dapat dipratinjau.', o.url);
                }
            } else if (tipe === 'doc') {
                tampilPesanPreview(target, 'fa-file-word',
                    'Format .doc lama tidak bisa dipratinjau di browser. Gunakan .docx atau PDF untuk pratinjau langsung.',
                    o.url);
            } else {
                tampilPesanPreview(target, 'fa-file', 'Pratinjau tidak tersedia untuk format ini.', o.url);
            }
        }

        function bukaPreview(judul, url, tipe) {
            $('#pvJudul').text(judul);
            $('#pvUnduh').attr('href', url + (url.includes('/psikotest/') ? '?unduh=1' : ''));
            bukaModal('modalPreview');
            tampilkanPreview('#pvBody', {
                url: url,
                tipe: tipe
            });
        }

        function setScanStatus(tipe, html) {
            $('#scanStatus').attr('class', 'scan-status ' + tipe).html(html);
        }

        async function ocrGambar(file) {
            await muatSkrip(SKRIP.tesseract);
            const hasil = await Tesseract.recognize(file, 'ind+eng', {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        setScanStatus('loading',
                            `<i class="fa-solid fa-spinner fa-spin"></i> Membaca teks gambar... ${Math.round(m.progress * 100)}%`
                        );
                    }
                }
            });
            return hasil.data.text || '';
        }

        function isiDariScan(data, timpa) {
            let terisi = 0;

            Object.entries(data).forEach(([key, nilai]) => {
                if (nilai === null || nilai === '') return;
                const $el = $(`#formPelamar [name="${key}"]`);
                if (!$el.length) return;

                const lama = String($el.val() || '').trim();
                if (lama && !timpa) return;

                if ($el.is('select')) {
                    const cocok = $el.find('option').filter(function() {
                        return $(this).val().toLowerCase() === String(nilai).toLowerCase();
                    }).first();
                    if (!cocok.length) return;
                    $el.val(cocok.val());
                } else {
                    $el.val(nilai);
                }

                $el.addClass('scan-filled');
                terisi++;
            });

            return terisi;
        }

        function lampirkanBerkas(jenis, file) {
            const ext = file.name.split('.').pop().toLowerCase();
            const diizinkan = {
                cv: ['pdf', 'docx'],
                ktp: ['pdf', 'jpg', 'jpeg', 'png'],
                ijazah: ['pdf', 'jpg', 'jpeg', 'png']
            };

            if (!diizinkan[jenis] || !diizinkan[jenis].includes(ext)) return;

            const target = $(`#formPelamar input[name="${jenis}"]`)[0];
            if (target && !target.files.length) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    target.files = dt.files;
                } catch (e) {}
            }
        }

        async function scanDokumen() {
            const file = $('#scanFile')[0].files[0];
            const jenis = $('#scanJenis').val();

            if (!file) {
                setScanStatus('error', 'Pilih file dokumen terlebih dahulu.');
                return;
            }

            const $btn = $('#btnScanDokumen');
            $btn.prop('disabled', true);
            setScanStatus('loading', '<i class="fa-solid fa-spinner fa-spin"></i> Membaca dokumen...');

            const fd = new FormData();
            fd.append('jenis', jenis);
            fd.append('_token', $('meta[name="csrf-token"]').attr('content'));

            try {
                const ext = file.name.split('.').pop().toLowerCase();

                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    fd.append('teks', await ocrGambar(file));
                } else {
                    fd.append('dokumen', file);
                }

                const res = await $.ajax({
                    url: `${BASE_URL}/scan-dokumen`,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                });

                const terisi = isiDariScan(res.data, $('#scanTimpa').is(':checked'));
                lampirkanBerkas(jenis, file);
                setScanStatus('ok',
                    `<i class="fa-solid fa-circle-check"></i> ${terisi} kolom terisi otomatis. Periksa kembali sebelum menyimpan.`
                );
            } catch (e) {
                const pesan = e && e.responseJSON ? pesanError(e) : (e && e.message ? e.message :
                    'Gagal memproses dokumen.');
                setScanStatus('error', `<i class="fa-solid fa-circle-exclamation"></i> ${esc(pesan)}`);
            } finally {
                $btn.prop('disabled', false);
            }
        }

        function resetFormPelamar() {
            $('#formPelamar')[0].reset();
            $('#formPelamar .scan-filled').removeClass('scan-filled');
            $('#pelamarId').val('');
            $('#scanStatus').attr('class', 'scan-status').html('');
            $('.file-info').html('');
        }

        function bukaFormTambah() {
            resetFormPelamar();
            $('#pfTitle').text('Tambah Pelamar Manual');
            $('#pfIcon').attr('class', 'fa-solid fa-user-plus');
            $('#pfSubmitLabel').text('Simpan pelamar');
            bukaModal('modalFormPelamar');
        }

        function bukaFormEdit(id) {
            resetFormPelamar();
            $('#pelamarId').val(id);
            $('#pfTitle').text('Edit Data Pelamar');
            $('#pfIcon').attr('class', 'fa-solid fa-pen-to-square');
            $('#pfSubmitLabel').text('Simpan perubahan');

            $.getJSON(`${BASE_URL}/show/${id}`)
                .done(json => {
                    Object.entries(json.data.form).forEach(([key, nilai]) => {
                        const $el = $(`#formPelamar [name="${key}"]`);
                        if (!$el.length || nilai === null || nilai === undefined) return;

                        let isi = nilai;
                        if (key === 'gaji_diharapkan') isi = rupiah(nilai);

                        if ($el.is('select') && isi !== '' && !$el.find('option').filter(function() {
                                return $(this).val() === String(isi);
                            }).length) {
                            $el.append($('<option>').val(isi).text(isi));
                        }

                        $el.val(isi);
                    });

                    const label = {
                        cv: 'CV',
                        portofolio: 'Portofolio',
                        foto: 'Foto',
                        ktp: 'KTP',
                        ijazah: 'Ijazah'
                    };

                    Object.entries(json.data.dokumen).forEach(([key, url]) => {
                        if (url) {
                            $(`#info_${key}`).html(
                                `<a href="${esc(url)}" target="_blank"><i class="fa-solid fa-paperclip"></i> ${label[key]} saat ini</a> · unggah file baru untuk mengganti`
                            );
                        }
                    });

                    bukaModal('modalFormPelamar');
                })
                .fail(xhr => toast(pesanError(xhr), 'danger'));
        }

        function itemProfil(label, nilai) {
            return `<div><div class="profile-item-label">${label}</div><div class="profile-item-value">${nilai ? nilai : '-'}</div></div>`;
        }

        const TAHAP_URUT = [
            ['applied', 'Lamaran masuk'],
            ['screening', 'Screening'],
            ['psikotest', 'Psikotest'],
            ['interview', 'Interview'],
            ['offer', 'Offer'],
            ['hired', 'Diterima']
        ];

        function pdRow(label, nilai) {
            const ada = nilai !== null && nilai !== undefined && String(nilai).trim() !== '';
            return `<div class="pd-row"><dt>${label}</dt><dd>${ada ? nilai : '<span class="pd-empty">Belum diisi</span>'}</dd></div>`;
        }

        function pdGroup(ikon, judul, isi) {
            return `<section class="pd-group"><div class="pd-group-title"><i class="fa-solid ${ikon}"></i>${judul}</div>${isi}</section>`;
        }

        function pdFact(nilai, label) {
            return `<div class="pd-fact"><div class="pd-fact-val">${nilai ? nilai : '-'}</div><div class="pd-fact-lbl">${label}</div></div>`;
        }

        function renderProfil(d) {
            const p = d.pelamar;
            const f = d.form;
            const tahap = p.tahap_rekrutmen || d.tahap_rekrutmen || '';

            /* ---------- tahap rekrutmen ---------- */
            const idx = TAHAP_URUT.findIndex(x => x[0] === tahap);
            const ditolak = tahap === 'rejected';
            const pipeline = ditolak ?
                `<div class="pd-rejected"><i class="fa-solid fa-circle-xmark"></i><div>Lamaran ini sudah ditolak.</div></div>` :
                `<div class="pd-pipeline">${TAHAP_URUT.map(([k, label], i) => {
                    const kelas = idx === -1 ? '' : (i < idx || (idx === TAHAP_URUT.length - 1) ? 'done' : (i === idx ? 'now' : ''));
                    const isi = kelas === 'done' ? '<i class="fa-solid fa-check"></i>' : (i + 1);
                    return `<div class="pd-step ${kelas}"><div class="pd-dot">${isi}</div>${label}</div>`;
                }).join('')}</div>`;

            /* ---------- ringkasan ---------- */
            const usia = f.tanggal_lahir && d.usia ? ` (${d.usia} tahun)` : '';
            const ttl = [f.tempat_lahir, f.tanggal_lahir ? tanggalIndo(f.tanggal_lahir) + usia : ''].filter(Boolean).join(
                ', ');
            const pendidikan = [f.pendidikan_terakhir, f.jurusan].filter(Boolean).join(' - ');
            const jk = f.jenis_kelamin === 'L' ? 'Laki-laki' : (f.jenis_kelamin === 'P' ? 'Perempuan' : '');
            const linkedin = f.linkedin_url ?
                `<a href="${esc(f.linkedin_url)}" target="_blank" rel="noopener">${esc(f.linkedin_url)}</a>` : '';
            const pengalaman = f.pengalaman_tahun !== null && f.pengalaman_tahun !== '' && f.pengalaman_tahun !==
                undefined ? f.pengalaman_tahun + ' tahun' : '';

            const keahlianHtml = f.keahlian ?
                f.keahlian.split(',').map(k => k.trim()).filter(Boolean).map(k =>
                    `<span class="skill-tag">${esc(k)}</span>`).join('') :
                '<span class="pd-empty">Belum diisi</span>';

            const ringkasan =
                `
                <div class="pd-facts">
                    ${pdFact(esc(pengalaman), 'Pengalaman kerja')}
                    ${pdFact(esc(f.ipk), 'IPK')}
                    ${pdFact(esc(rupiah(f.gaji_diharapkan)), 'Gaji diharapkan')}
                    ${pdFact(esc(f.notice_period), 'Notice period')}
                </div>
                ${pdGroup('fa-briefcase', 'Lamaran', `<dl class="pd-list">
                            ${pdRow('Posisi', esc(f.jabatan))}
                            ${pdRow('Divisi', esc(f.divisi))}
                            ${pdRow('Detail posisi', esc(f.detail_jabatan))}
                            ${pdRow('Sumber lamaran', esc(f.sumber_lamaran))}
                            ${pdRow('Tanggal melamar', tanggalIndo(f.tanggal_melamar))}
                        </dl>`)}
                ${pdGroup('fa-graduation-cap', 'Pendidikan', `<dl class="pd-list">
                            ${pdRow('Pendidikan terakhir', esc(pendidikan))}
                            ${pdRow('Institusi', esc(f.institusi))}
                        </dl>`)}
                ${pdGroup('fa-user', 'Data pribadi', `<dl class="pd-list">
                            ${pdRow('NIK', esc(f.nik_ktp))}
                            ${pdRow('Tempat, tanggal lahir', esc(ttl))}
                            ${pdRow('Jenis kelamin', jk)}
                            ${pdRow('Status pernikahan', esc(f.status_pernikahan))}
                            ${pdRow('Agama', esc(f.agama))}
                            ${pdRow('LinkedIn', linkedin)}
                            ${pdRow('Alamat', esc(f.alamat))}
                        </dl>`)}
                ${pdGroup('fa-code', 'Keahlian', `<div>${keahlianHtml}</div>`)}
                ${f.catatan_hr ? pdGroup('fa-message', 'Catatan HR', `<div class="pd-note">${esc(f.catatan_hr)}</div>`) : ''}`;

            /* ---------- psikotest ---------- */
            const psikotestHtml = Object.entries(KATEGORI).map(([k, label]) => {
                const t = d.psikotests ? d.psikotests[k] : null;
                if (!t) {
                    return `<div class="pd-test kosong"><div><div class="pd-test-title">${esc(label)}</div><div class="pd-test-meta">Belum ada hasil</div></div></div>`;
                }
                const skor = t.hasil ? esc(t.hasil) : (t.skor !== null && t.skor !== undefined ? esc(t.skor) : '-');
                const ket = t.hasil && INFO.mbti[t.hasil] ? INFO.mbti[t.hasil].nama : (t.level || '');
                const meta = [ket, t.tanggal_tes ? 'Tes ' + tanggalIndo(t.tanggal_tes) : ''].filter(Boolean).map(
                    esc).join(' · ');
                const tombol = t.file_url ?
                    `<button type="button" class="btn btn-sm btn-outline-secondary btn-pv-file" data-judul="${esc(label)} - ${esc(p.nama_lengkap)}" data-url="${esc(t.file_url)}" data-tipe="${esc(t.preview_tipe || '')}"><i class="fa-regular fa-eye me-1"></i> Pratinjau</button>` :
                    '';
                return `<div class="pd-test">
                    <div class="pd-test-score">${skor}</div>
                    <div class="me-auto"><div class="pd-test-title">${esc(label)}</div><div class="pd-test-meta">${meta || '-'}</div></div>
                    ${tombol}
                </div>`;
            }).join('');
            const jumlahPt = d.psikotests ? Object.values(d.psikotests).filter(Boolean).length : 0;

            /* ---------- riwayat (terbaru di atas) ---------- */
            let riwayatHtml =
                '<div class="penilaian-empty"><i class="fa-solid fa-route d-block fs-3 opacity-25 mb-2"></i>Belum ada riwayat</div>';
            if (d.riwayat && d.riwayat.length) {
                riwayatHtml = d.riwayat.slice().reverse().map((r, i) => {
                    const tgl = new Date(r.created_at).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    });
                    const rating = r.rating > 0 ? `<div class="table-rating mt-1">${bintang(r.rating)}</div>` : '';
                    const feedback = r.keterangan && String(r.keterangan).trim() !== '' ?
                        `<div class="timeline-feedback">${esc(r.keterangan)}</div>` : '';
                    return `<div class="timeline-item">
                        <div class="timeline-dot ${i === 0 ? 'active' : 'done'}"><i class="fa-solid ${i === 0 ? 'fa-user-tie' : 'fa-check'}"></i></div>
                        <div>
                            <div class="timeline-title">${esc(AKSI_LABEL[r.aksi] || r.aksi)}</div>
                            <div class="timeline-desc">${tgl} · oleh ${esc(r.oleh ?? '-')}</div>
                            ${rating}${feedback}
                        </div>
                    </div>`;
                }).join('');
            }

            /* ---------- penilaian ---------- */
            let penilaianHtml =
                '<div class="penilaian-empty"><i class="fa-regular fa-star d-block fs-3 opacity-25 mb-2"></i>Belum ada penilaian dari interviewer</div>';
            if (d.penilaians && d.penilaians.length) {
                const ringkas = d.avg_rating ? `
                    <div class="penilaian-summary">
                        <div><div class="penilaian-summary-value">${d.avg_rating}</div><div class="penilaian-summary-label">Rata-rata nilai</div></div>
                        <div><div class="penilaian-summary-value table-rating">${bintang(d.avg_rating)}</div><div class="penilaian-summary-label">dari 4 bintang</div></div>
                        <div><div class="penilaian-summary-value">${d.total_penilai}</div><div class="penilaian-summary-label">Penilai</div></div>
                    </div>` : '';

                penilaianHtml = ringkas + d.penilaians.map(pf => {
                    const berkas = pf.file_penilaian ?
                        `<a href="/storage/${esc(pf.file_penilaian)}" target="_blank" class="btn btn-sm btn-outline-secondary mt-2"><i class="fa-solid fa-file-pdf text-danger me-1"></i> Lihat file penilaian</a>` :
                        '';
                    const catatan = pf.catatan && String(pf.catatan).trim() !== '' ?
                        `<div class="penilaian-catatan-box">${esc(pf.catatan)}</div>` : '';
                    return `<div class="penilaian-card">
                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-1">
                            <div>
                                <div class="fw-semibold">${esc(pf.interviewer_nama)} <span class="penilaian-folder-badge"><i class="fa-solid fa-folder"></i> ${esc(pf.folder_nama)}</span></div>
                                <div class="muted-small">${esc(pf.interviewer_jabatan)}</div>
                            </div>
                            <div class="muted-small"><i class="fa-regular fa-clock"></i> ${esc(pf.tanggal_dinilai || '-')}</div>
                        </div>
                        <div class="table-rating">${pf.rating ? bintang(pf.rating) : '<em class="text-muted">Belum dinilai</em>'}</div>
                        ${catatan}${berkas}
                    </div>`;
                }).join('');
            }

            /* ---------- dokumen ---------- */
            const labelDok = {
                cv: 'CV',
                portofolio: 'Portofolio',
                foto: 'Foto',
                ktp: 'KTP',
                ijazah: 'Ijazah'
            };
            const ikonDok = {
                cv: 'fa-file-lines',
                portofolio: 'fa-folder-open',
                foto: 'fa-image',
                ktp: 'fa-id-card',
                ijazah: 'fa-graduation-cap'
            };
            const dokumen = d.dokumen || {};
            const jumlahDok = Object.values(dokumen).filter(Boolean).length;
            const dokHtml = Object.keys(labelDok).map(k => {
                const url = dokumen[k];
                if (!url) {
                    return `<div class="pd-doc kosong"><div class="pd-doc-ico"><i class="fa-solid ${ikonDok[k]}"></i></div><div><div class="pd-doc-name">${labelDok[k]}</div><div class="muted-small">Belum diunggah</div></div></div>`;
                }
                const tipe = tipeDariNama(url);
                const lihat = tipe ?
                    `<button type="button" class="btn btn-sm btn-outline-secondary btn-pv-file" title="Pratinjau" data-judul="${labelDok[k]} - ${esc(p.nama_lengkap)}" data-url="${esc(url)}" data-tipe="${esc(tipe)}"><i class="fa-regular fa-eye"></i></button>` :
                    '';
                return `<div class="pd-doc"><div class="pd-doc-ico"><i class="fa-solid ${ikonDok[k]}"></i></div><div><div class="pd-doc-name">${labelDok[k]}</div></div>
                    <div class="pd-doc-act">${lihat}<a href="${esc(url)}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Buka"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></div></div>`;
            }).join('');

            /* ---------- kolom kiri ---------- */
            const rating = d.avg_rating ?
                `<div class="pd-rating"><span class="table-rating">${bintang(d.avg_rating)}</span><span>${d.avg_rating} dari ${d.total_penilai} penilai</span></div>` :
                '<div class="pd-rating">Belum dinilai</div>';

            const telp = f.no_telepon ? `<a href="tel:${esc(f.no_telepon)}">${esc(f.no_telepon)}</a>` : '-';
            const sisi = `
                <aside class="pd-side">
                    <div class="pd-id">
                        <div class="pd-avatar av-${p.id % 6}">${esc(d.inisial)}</div>
                        <div class="pd-name">${esc(p.nama_lengkap)}</div>
                        <div class="pd-role">${esc(f.jabatan ?? '-')}${f.divisi ? ' - ' + esc(f.divisi) : ''}</div>
                        <span class="stage-badge stage-${esc(tahap)}">${esc(d.tahap_label)}</span>
                        ${rating}
                    </div>
                    <ul class="pd-contact">
                        <li><i class="fa-regular fa-envelope"></i><a href="mailto:${esc(f.email)}">${esc(f.email)}</a></li>
                        <li><i class="fa-solid fa-phone"></i>${telp}</li>
                        <li><i class="fa-solid fa-location-dot"></i>${esc(f.domisili || '-')}</li>
                        <li><i class="fa-regular fa-calendar"></i>Melamar ${esc(tanggalIndo(f.tanggal_melamar) || '-')}</li>
                        <li><i class="fa-solid fa-bullhorn"></i>${esc(f.sumber_lamaran || '-')}</li>
                    </ul>
                    <div class="pd-side-actions">
                        <button type="button" class="btn btn-primary btn-edit-pelamar" data-id="${p.id}"><i class="fa-regular fa-pen-to-square"></i> Edit data</button>
                        <button type="button" class="btn btn-outline-secondary btn-profil-email" data-id="${p.id}" data-nama="${esc(p.nama_lengkap)}" data-email="${esc(f.email)}"><i class="fa-regular fa-envelope"></i> Kirim email</button>
                        <button type="button" class="btn btn-outline-secondary btn-psikotest" data-id="${p.id}" data-nama="${esc(p.nama_lengkap)}"><i class="fa-solid fa-brain"></i> Kelola psikotest</button>
                    </div>
                </aside>`;

            /* ---------- kolom kanan ---------- */
            const jumlahRiwayat = d.riwayat ? d.riwayat.length : 0;
            const jumlahNilai = d.penilaians ? d.penilaians.length : 0;

            const utama = `
                <div class="pd-main">
                    <div class="pd-main-head">
                        <h5>Detail pelamar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    ${pipeline}
                    <ul class="pd-tabs" role="tablist">
                        <li><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#pdRingkasan">Ringkasan</button></li>
                        <li><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#pdPsikotest">Psikotest<span class="pd-count">${jumlahPt}/4</span></button></li>
                        <li><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#pdPenilaian">Penilaian<span class="pd-count">${jumlahNilai}</span></button></li>
                        <li><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#pdRiwayat">Riwayat<span class="pd-count">${jumlahRiwayat}</span></button></li>
                        <li><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#pdDokumen">Dokumen<span class="pd-count">${jumlahDok}/5</span></button></li>
                    </ul>
                    <div class="pd-body tab-content">
                        <div class="tab-pane fade show active" id="pdRingkasan">${ringkasan}</div>
                        <div class="tab-pane fade" id="pdPsikotest">${psikotestHtml}</div>
                        <div class="tab-pane fade" id="pdPenilaian">
                            <div class="text-end mb-3"><button type="button" class="btn btn-sm btn-outline-primary btn-lihat-penilaian" data-id="${p.id}" data-nama="${esc(p.nama_lengkap)}"><i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> Buka tampilan lengkap</button></div>
                            ${penilaianHtml}
                        </div>
                        <div class="tab-pane fade" id="pdRiwayat">${riwayatHtml}</div>
                        <div class="tab-pane fade" id="pdDokumen"><div class="pd-docs">${dokHtml}</div></div>
                    </div>
                </div>`;

            $('#profilContent').html(`<div class="pd-shell">${sisi}${utama}</div>`);
        }

        function updateBintang(nilai) {
            $('#starContainerNilai .star-nilai').each(function() {
                const aktif = $(this).data('value') <= nilai;
                $(this).css('color', aktif ? '#ffc107' : '#ddd').text(aktif ? '★' : '☆');
            });
        }

        let PN = { id: null, nama: '', data: null, filter: 'all', reopen: false, kembali: false };

        function bukaPenilaian(id, nama) {
            PN = { id: id, nama: nama, data: null, filter: 'all', reopen: false, kembali: false };
            $('#pnNama').text(nama);
            $('#pnBody').html('<div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Memuat penilaian...</div>');
            bukaModal('modalPenilaian');
            muatPenilaian();
        }

        function muatPenilaian() {
            return $.getJSON(`${BASE_URL}/${PN.id}/penilaian`)
                .done(res => {
                    PN.data = res;
                    renderPenilaian();
                })
                .fail(xhr => $('#pnBody').html(`<div class="text-center py-5 text-danger">${esc(pesanError(xhr))}</div>`));
        }

        function kartuPenilaian(n) {
            const inisial = (n.penilai_nama || '?').split(' ').slice(0, 2).map(x => x[0] || '').join('').toUpperCase();
            const warna = (n.penilai_nama || '').length % 6;
            const tahapInterview = n.tahap_interview_label ? ` · ${esc(n.tahap_interview_label)}` : '';

            const catatan = n.catatan && String(n.catatan).trim() !== '' ?
                `<div class="pn-note">${esc(n.catatan)}</div>` :
                '<div class="muted-small mt-2">Tidak ada catatan.</div>';

            let berkas = '<span class="muted-small">Tanpa dokumen</span>';
            if (n.file_url) {
                const judul = `Penilaian ${n.penilai_nama} - ${PN.nama}`;
                const lihat = n.file_tipe ?
                    `<button type="button" class="btn btn-sm btn-outline-secondary btn-pn-file" data-judul="${esc(judul)}" data-url="${esc(n.file_url)}" data-tipe="${esc(n.file_tipe)}"><i class="fa-regular fa-eye me-1"></i> Pratinjau</button>` : '';
                berkas = `${lihat}<a class="btn btn-sm btn-outline-secondary" href="${esc(n.file_url)}" download target="_blank"><i class="fa-solid fa-download me-1"></i> Unduh ${esc((n.file_ext || '').toUpperCase())}</a>`;
            }

            return `
                <div class="pn-item">
                    <div class="pn-head">
                        <div class="pn-ava av-${warna}">${esc(inisial)}</div>
                        <div class="pn-who">
                            <div class="pn-who-name">${esc(n.penilai_nama)}</div>
                            <div class="pn-meta">${esc(n.penilai_jabatan)}</div>
                        </div>
                        <div class="pn-score">
                            <div class="table-rating">${bintang(n.rating)}</div>
                            <div class="muted-small">${n.rating} dari 4</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                        <span class="stage-badge stage-${esc(n.tahap || 'applied')}">${esc(n.tahap_label)}</span>
                        <span class="muted-small">${tahapInterview.replace(' · ', '')}</span>
                        <span class="penilaian-folder-badge ms-0"><i class="fa-solid fa-folder"></i> ${esc(n.folder_nama)}</span>
                    </div>
                    ${catatan}
                    <div class="pn-foot">
                        <span class="muted-small"><i class="fa-regular fa-clock"></i> ${esc(n.tanggal_dinilai || '-')}</span>
                        ${berkas}
                    </div>
                </div>`;
        }

        function renderPenilaian() {
            const d = PN.data;

            if (!d.items.length) {
                $('#pnBody').html('<div class="penilaian-empty py-5"><i class="fa-regular fa-star d-block fs-2 opacity-25 mb-2"></i>Belum ada penilaian untuk pelamar ini.<div class="mt-1" style="font-size:.8rem;">Gunakan tombol "Tambah penilaian" di bawah.</div></div>');
                return;
            }

            const ringkasTahap = d.per_tahap.map(t => `<span><b>${esc(t.label)}</b> ${t.rata} (${t.jumlah})</span>`).join('');

            const chip = (kode, label, jumlah) =>
                `<button type="button" class="pn-chip ${PN.filter === kode ? 'active' : ''}" data-filter="${esc(kode)}">${esc(label)} (${jumlah})</button>`;

            const filter = chip('all', 'Semua tahap', d.total) +
                d.per_tahap.map(t => chip(t.tahap, t.label, t.jumlah)).join('');

            const tampil = d.items.filter(n => PN.filter === 'all' || (n.tahap || 'tidak_tercatat') === PN.filter);

            $('#pnBody').html(`
                <div class="pn-summary">
                    <div><div class="pn-avg">${d.rata}</div><div class="pn-avg-lbl">Rata-rata dari 4</div></div>
                    <div><div class="table-rating fs-5">${bintang(d.rata)}</div><div class="pn-avg-lbl">${d.total} penilaian</div></div>
                    <div class="pn-stage-list">${ringkasTahap}</div>
                </div>
                <div class="pn-filter">${filter}</div>
                ${tampil.map(kartuPenilaian).join('')}`);
        }

        function bukaPsikotest(id, nama) {
            PT = {
                pelamarId: id,
                data: {},
                aktif: 'mbti'
            };
            $('#ptNama').text(nama);
            $('#ptFormWrap').html(
                '<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Memuat...</div>'
            );
            tampilPesanPreview('#ptPreview', 'fa-file-circle-question',
                'Pilih file hasil tes untuk melihat pratinjau di sini.');
            bukaModal('modalPsikotest');
            muatPsikotest();
        }

        function muatPsikotest(fokus) {
            return $.getJSON(`${BASE_URL}/${PT.pelamarId}/psikotest`)
                .done(res => {
                    PT.data = res.data;
                    if (fokus) PT.aktif = fokus;
                    renderPsikotestTabs();
                    renderPsikotestForm();
                })
                .fail(xhr => toast(pesanError(xhr), 'danger'));
        }

        function renderPsikotestTabs() {
            $('#ptTabs').html(Object.entries(KATEGORI).map(([k, label]) =>
                `<button type="button" class="pt-tab ${PT.aktif === k ? 'active' : ''}" data-kategori="${k}">${esc(label)}${PT.data[k] ? '<i class="fa-solid fa-circle-check"></i>' : ''}</button>`
            ).join(''));
        }

        function hurufTeratas(form) {
            const nilai = [];
            form.find('.rs-skor').each(function() {
                const v = parseFloat($(this).val());
                if (!isNaN(v)) nilai.push([$(this).data('huruf'), v]);
            });
            if (nilai.length < 3) return '';
            return nilai.sort((a, b) => b[1] - a[1]).slice(0, 3).map(x => x[0]).join('');
        }

        function renderPsikotestForm() {
            const k = PT.aktif;
            const d = PT.data[k] || {};
            let inti = '';

            if (k === 'mbti') {
                inti = `
                    <label class="form-label-custom">Tipe MBTI <span class="required">*</span></label>
                    <select name="hasil" class="form-select-custom" required>
                        <option value="">-- Pilih tipe --</option>
                        ${Object.entries(INFO.mbti).map(([kode, v]) => `<option value="${kode}" ${d.hasil === kode ? 'selected' : ''}>${kode} — ${esc(v.nama)}</option>`).join('')}
                    </select>`;
            } else if (k === 'riasec') {
                const detail = d.skor_detail || {};
                inti = `
                    <label class="form-label-custom">Kode RIASEC <span class="required">*</span></label>
                    <input type="text" name="hasil" id="rsKode" class="form-control-custom" maxlength="6" placeholder="Contoh: RIA" value="${esc(d.hasil || '')}" required style="text-transform:uppercase;">
                    <div class="form-hint">1–6 huruf dari R, I, A, S, E, C tanpa mengulang. Huruf pertama adalah yang dominan.</div>
                    <label class="form-label-custom mt-3">Skor per huruf <small class="text-muted fw-normal">(opsional)</small></label>
                    <div class="row g-2">
                        ${Object.keys(INFO.riasec).map(h => `
                                        <div class="col-4">
                                            <div class="muted-small mb-1" title="${esc(INFO.riasec[h].nama)}">${h} · ${esc(INFO.riasec[h].nama)}</div>
                                            <input type="number" min="0" step="0.1" name="skor_detail[${h.toLowerCase()}]" data-huruf="${h}" class="form-control-custom rs-skor" value="${detail[h.toLowerCase()] ?? ''}">
                                        </div>`).join('')}
                    </div>
                    <div class="form-hint">Jika kode dikosongkan, kode otomatis diisi dari tiga skor tertinggi.</div>`;
            } else {
                inti = `
                    <label class="form-label-custom">Skor <span class="required">*</span></label>
                    <input type="number" name="skor" id="ptSkor" class="form-control-custom" min="0" step="0.01" value="${d.skor ?? ''}" required>
                    <div class="form-hint" id="ptLevel">${d.skor !== undefined && d.skor !== null ? 'Kategori: ' + esc(levelSkor(d.skor)) : 'Kategori skor akan tampil otomatis.'}</div>`;
            }

            const ada = d && d.file_url;
            const html = `
                <input type="hidden" name="kategori" value="${k}">
                <div class="info-box mb-3"><div class="fw-semibold mb-1">${esc(INFO.kategori[k].judul)}</div><div>${esc(INFO.kategori[k].mengukur)}</div></div>
                <div class="mb-3">${inti}</div>
                <div class="mb-3">
                    <label class="form-label-custom">Tanggal tes</label>
                    <input type="date" name="tanggal_tes" class="form-control-custom" value="${esc(d.tanggal_tes || '')}">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Dokumen hasil tes ${ada ? '' : '<span class="required">*</span>'}</label>
                    <input type="file" name="file" id="ptFile" class="form-control-custom" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <div class="form-hint">PDF, JPG, PNG, DOC, DOCX. Maks. 10MB.${ada ? ' Kosongkan jika tidak ingin mengganti file <strong>' + esc(d.file_nama || '') + '</strong>.' : ''}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Catatan</label>
                    <textarea name="catatan" class="form-control-custom" rows="2" placeholder="Catatan hasil tes...">${esc(d.catatan || '')}</textarea>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan hasil</button>
                    ${ada ? `<a class="btn btn-outline-secondary" href="${esc(d.file_url)}?unduh=1"><i class="fa-solid fa-download me-1"></i> Unduh</a>
                                <button type="button" class="btn btn-outline-danger" id="btnHapusPsikotest"><i class="fa-regular fa-trash-can me-1"></i> Hapus</button>` : ''}
                </div>`;

            $('#ptFormWrap').html(html);

            if (ada) {
                tampilkanPreview('#ptPreview', {
                    url: d.file_url,
                    tipe: d.preview_tipe,
                    nama: d.file_nama
                });
            } else {
                tampilPesanPreview('#ptPreview', 'fa-file-circle-question',
                    'Pilih file hasil tes untuk melihat pratinjau di sini.');
            }
        }

        function renderInfo() {
            const isi = k =>
                `<dt>Yang diukur</dt><dd>${esc(INFO.kategori[k].mengukur)}</dd><dt>Cara membaca</dt><dd>${esc(INFO.kategori[k].cara_baca)}</dd><dt>Catatan</dt><dd>${esc(INFO.kategori[k].catatan)}</dd>`;
            $('#infoMbti').html(isi('mbti'));
            $('#infoRiasec').html(isi('riasec'));
            $('#infoNumerik').html(isi('logika_numerik'));
            $('#infoVerbal').html(isi('analogi_verbal'));
        }

        function buatChart(id, config, ada) {
            if (charts[id]) {
                charts[id].destroy();
                delete charts[id];
            }
            const el = document.getElementById(id);
            if (!el) return;
            const $box = $(el).closest('.chart-box');
            $box.toggleClass('is-empty', !ada);
            if (!ada) return;
            config.options = Object.assign({
                responsive: true,
                maintainAspectRatio: false
            }, config.options || {});
            charts[id] = new Chart(el, config);
        }

        const KARTU = [
            ['total', 'Total lamaran', 'fa-inbox', '#0062ff', 'Semua tahap aktif'],
            ['screening', 'Screening', 'fa-clipboard-check', '#00bcd4', 'Menunggu review'],
            ['psikotest', 'Psikotest', 'fa-brain', '#0d9488', 'Sedang menjalani tes'],
            ['interview', 'Interview', 'fa-user-tie', '#ff9800', 'Dalam proses'],
            ['offer', 'Offer', 'fa-file-signature', '#7c3aed', 'Menunggu respons'],
            ['hired', 'Diterima', 'fa-circle-check', '#00b573', 'Siap onboarding'],
            ['rejected', 'Ditolak', 'fa-circle-xmark', '#f44336', 'Tidak lanjut'],
            ['rasio_diterima', 'Rasio diterima', 'fa-percent', '#64748b', 'Diterima dari total']
        ];

        function renderStatSkor(sel, d) {
            const kotak = (label, nilai) =>
                `<div class="col-3"><div class="stat-value" style="font-size:1.3rem;">${nilai ?? '-'}</div><div class="muted-small">${label}</div></div>`;
            $(sel).html(kotak('Jumlah tes', d.jumlah) + kotak('Rata-rata', d.rata) + kotak('Tertinggi', d.tertinggi) +
                kotak('Terendah', d.terendah));
        }

        function renderAnalitik(d) {
            $('#kartuAnalitik').html(KARTU.map(([k, label, ikon, warna, desc]) => `
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="--c:${warna};">
                        <div class="stat-icon"><i class="fa-solid ${ikon}"></i></div>
                        <div class="stat-label">${label}</div>
                        <div class="stat-value">${d.kartu[k]}${k === 'rasio_diterima' ? '%' : ''}</div>
                        <div class="stat-desc">${desc}</div>
                    </div>
                </div>`).join(''));

            const adaFunnel = d.funnel.nilai.some(v => v > 0);
            buatChart('chFunnel', {
                type: 'bar',
                data: {
                    labels: d.funnel.label,
                    datasets: [{
                        data: d.funnel.nilai,
                        backgroundColor: Object.keys(WARNA_TAHAP).map(k => WARNA_TAHAP[k]),
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            }, adaFunnel);

            buatChart('chTrend', {
                type: 'line',
                data: {
                    labels: d.trend.label,
                    datasets: [{
                            label: 'Lamaran',
                            data: d.trend.lamaran,
                            borderColor: '#0062ff',
                            backgroundColor: '#0062ff',
                            tension: .3
                        },
                        {
                            label: 'Diterima',
                            data: d.trend.diterima,
                            borderColor: '#00b573',
                            backgroundColor: '#00b573',
                            tension: .3
                        },
                        {
                            label: 'Ditolak',
                            data: d.trend.ditolak,
                            borderColor: '#f44336',
                            backgroundColor: '#f44336',
                            tension: .3
                        }
                    ]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
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
            }, d.trend.label.length > 0);

            buatChart('chSumber', {
                type: 'doughnut',
                data: {
                    labels: d.sumber.label,
                    datasets: [{
                        data: d.sumber.nilai,
                        backgroundColor: WARNA
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            }, d.sumber.nilai.length > 0);

            const batang = (id, data) => buatChart(id, {
                type: 'bar',
                data: {
                    labels: data.label,
                    datasets: [{
                        data: data.nilai,
                        backgroundColor: '#0062ff',
                        borderRadius: 4
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
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
            }, data.nilai.length > 0);

            batang('chDivisi', d.divisi);
            batang('chPendidikan', d.pendidikan);
            renderAnalitikPsikotest(d.psikotest);
        }

        function renderAnalitikPsikotest(p) {
            $('#ptCakupan').html(p.cakupan.map((c, i) => {
                    const persen = p.total_pelamar > 0 ? Math.round(c.jumlah / p.total_pelamar * 100) : 0;
                    return `
                    <div class="col-6 col-md-3">
                        <div class="stat-card" style="--c:${WARNA[i + 5] || '#0d9488'};">
                            <div class="stat-label">${esc(c.label)}</div>
                            <div class="stat-value">${c.jumlah}<span class="muted-small fw-normal"> / ${p.total_pelamar}</span></div>
                            <div class="progress mt-2" style="height:5px;"><div class="progress-bar bg-success" style="width:${persen}%"></div></div>
                            <div class="stat-desc mt-1">${persen}% pelamar sudah punya hasil</div>
                        </div>
                    </div>`;
                }).join('') +
                `<div class="col-12"><div class="muted-small">${p.lengkap} pelamar sudah memiliki keempat hasil tes.</div></div>`
            );

            buatChart('chMbti', {
                type: 'bar',
                data: {
                    labels: p.mbti.tipe.map(t => `${t.kode} · ${t.nama}`),
                    datasets: [{
                        data: p.mbti.tipe.map(t => t.jumlah),
                        backgroundColor: '#0d9488',
                        borderRadius: 4
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
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
            }, p.mbti.tipe.length > 0);

            $('#mbtiDimensi').html(p.mbti.dimensi.map(x => {
                const total = x.jumlah_kiri + x.jumlah_kanan;
                const kiri = total ? Math.round(x.jumlah_kiri / total * 100) : 50;
                return `
                    <div class="dim-row">
                        <div class="muted-small mb-1">${esc(x.nama)}</div>
                        <div class="dim-label"><span>${x.kiri}</span><span>${x.kanan}</span></div>
                        <div class="dim-bar"><span style="width:${kiri}%"></span></div>
                        <div class="dim-label muted-small fw-normal"><span>${x.jumlah_kiri} pelamar${total ? ` (${kiri}%)` : ''}</span><span>${x.jumlah_kanan} pelamar${total ? ` (${100 - kiri}%)` : ''}</span></div>
                    </div>`;
            }).join(''));

            $('#mbtiTipe').html(p.mbti.tipe.length ?
                p.mbti.tipe.map(t =>
                    `<li><strong>${esc(t.kode)}</strong> · ${esc(t.nama)} <span class="badge bg-light text-dark border ms-1">${t.jumlah} pelamar</span><div class="muted-small">${esc(t.ringkas)}</div></li>`
                ).join('') :
                '<li class="text-muted">Belum ada hasil MBTI.</li>');

            const adaRata = p.riasec.huruf.some(h => h.rata !== null);
            buatChart('chRiasecRadar', {
                type: 'radar',
                data: {
                    labels: p.riasec.huruf.map(h => `${h.kode} · ${h.nama}`),
                    datasets: [{
                        label: 'Rata-rata skor',
                        data: p.riasec.huruf.map(h => h.rata ?? 0),
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124,58,237,.18)'
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        r: {
                            beginAtZero: true
                        }
                    }
                }
            }, adaRata);

            buatChart('chRiasecDominan', {
                type: 'bar',
                data: {
                    labels: p.riasec.huruf.map(h => `${h.kode} · ${h.nama}`),
                    datasets: [{
                        data: p.riasec.huruf.map(h => h.dominan),
                        backgroundColor: '#7c3aed',
                        borderRadius: 4
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
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
            }, p.riasec.jumlah > 0);

            $('#riasecHuruf').html(p.riasec.huruf.map(h =>
                `<li><strong>${h.kode}</strong> · ${esc(h.nama)} <span class="badge bg-light text-dark border ms-1">dominan: ${h.dominan}</span>${h.rata !== null ? `<span class="badge bg-light text-dark border ms-1">rata-rata: ${h.rata}</span>` : ''}<div class="muted-small">${esc(h.ringkas)}</div></li>`
            ).join(''));

            const skor = (idChart, idStat, data, warna) => {
                renderStatSkor(idStat, data);
                buatChart(idChart, {
                    type: 'bar',
                    data: {
                        labels: data.level.label,
                        datasets: [{
                            data: data.level.nilai,
                            backgroundColor: warna,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
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
                }, data.jumlah > 0);
            };

            skor('chNumerik', '#statNumerik', p.logika_numerik, '#ff9800');
            skor('chVerbal', '#statVerbal', p.analogi_verbal, '#00bcd4');

            buatChart('chTrenSkor', {
                type: 'line',
                data: {
                    labels: p.tren_skor.label,
                    datasets: [{
                            label: 'Logika numerik',
                            data: p.tren_skor.logika_numerik,
                            borderColor: '#ff9800',
                            backgroundColor: '#ff9800',
                            tension: .3,
                            spanGaps: true
                        },
                        {
                            label: 'Analogi verbal',
                            data: p.tren_skor.analogi_verbal,
                            borderColor: '#00bcd4',
                            backgroundColor: '#00bcd4',
                            tension: .3,
                            spanGaps: true
                        }
                    ]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        }
                    }
                }
            }, p.tren_skor.label.length > 0);
        }

        async function muatAnalitik() {
            $('#analitikStatus').html('<span class="spinner-border spinner-border-sm me-1"></span> Memuat...');
            try {
                await muatSkrip(SKRIP.chart);
                const res = await $.getJSON(`${BASE_URL}/analitik`, {
                    tanggal_dari: $('#aDari').val(),
                    tanggal_sampai: $('#aSampai').val()
                });
                renderAnalitik(res.data);
                S.analitikStale = false;
                $('#analitikStatus').text('');
            } catch (e) {
                $('#analitikStatus').text('');
                toast(e && e.responseJSON ? pesanError(e) : (e && e.message ? e.message : 'Gagal memuat analitik.'),
                    'danger');
            }
        }

        $(document).ready(function() {
            renderInfo();
            loadFolderList();
            muatData(1);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#fSearch').on('input', debounce(() => muatData(1), 350));
            $('#fJabatan, #fTahap, #fSumber').on('change', () => muatData(1));
            $('#btnResetFilter').on('click', function() {
                $('#fSearch, #fJabatan, #fTahap, #fSumber').val('');
                muatData(1);
            });

            $(document).on('click', '#paginationNav .page-link', function(e) {
                e.preventDefault();
                const page = parseInt($(this).attr('data-page'));
                if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active'))
                    muatData(page);
            });

            $('#tabAnalitikBtn').on('shown.bs.tab', function() {
                if (S.analitikStale) {
                    muatAnalitik();
                } else {
                    Object.values(charts).forEach(c => c.resize());
                }
            });

            $('#btnAnalitikTerapkan').on('click', muatAnalitik);
            $('#btnAnalitikReset').on('click', function() {
                $('#aDari, #aSampai').val('');
                muatAnalitik();
            });

            $('#hireSidebar').on('click', '.folder-list-link[data-folder-id]', function() {
                filterByFolder($(this).attr('data-folder-id'), $(this).attr('data-nama'));
            });

            $('#btnResetFolder').on('click', resetFolderFilter);

            $(document).on('click', '.btn-lihat-penilaian', function(e) {
                e.preventDefault();
                const id = $(this).attr('data-id');
                const nama = $(this).attr('data-nama');
                gantiModal('modalLihatProfil', () => bukaPenilaian(id, nama));
            });

            $(document).on('click', '.pn-chip', function() {
                PN.filter = $(this).attr('data-filter');
                renderPenilaian();
            });

            $(document).on('click', '.btn-pn-file', function() {
                const judul = $(this).attr('data-judul');
                const url = $(this).attr('data-url');
                const tipe = $(this).attr('data-tipe') || null;
                PN.kembali = true;
                gantiModal('modalPenilaian', () => bukaPreview(judul, url, tipe));
            });

            $('#modalPreview').on('hidden.bs.modal', function() {
                if (PN.kembali) {
                    PN.kembali = false;
                    bukaModal('modalPenilaian');
                }
            });

            $('#btnPnTambah').on('click', function() {
                PN.reopen = true;
                gantiModal('modalPenilaian', () => {
                    $('#formNilaiPelamar')[0].reset();
                    $('#nilaiPelamarId').val(PN.id);
                    $('#nilaiNama').val(PN.nama);
                    $('#ratingInputNilai').val(0);
                    updateBintang(0);
                    isiOpsiFolder('#nilaiFolderId', daftarFolder);
                    bukaModal('modalNilaiPelamar');
                });
            });

            $(document).on('click', '[data-folder-toggle]', function(e) {
                e.stopPropagation();
                $(`#folder-children-hire-${$(this).attr('data-folder-toggle')}`).slideToggle(200);
                $(this).toggleClass('expanded');
            });

            $(document).on('click', '.btn-pin-folder', function(e) {
                e.stopPropagation();
                $.post(
                        `/HR-dashboard/folders/${$(this).closest('.folder-list-link').attr('data-folder-id')}/pin`
                    )
                    .done(res => {
                        if (res.success) toast(res.message);
                        loadFolderList();
                    })
                    .fail(() => toast('Gagal mengubah pin folder', 'danger'));
            });

            $(document).on('click', '.btn-edit-folder', function(e) {
                e.stopPropagation();
                const a = $(this).closest('.folder-list-link');
                $('#editFolderId').val(a.attr('data-folder-id'));
                $('#editFolderName').val(a.attr('data-nama'));
                isiOpsiFolder('#parentFolderSelectEdit', daftarFolder, a.attr('data-folder-id'));
                $('#parentFolderSelectEdit').val(a.attr('data-parent') || '');
                bukaModal('modalEditFolder');
            });

            $(document).on('click', '.btn-del-folder', function(e) {
                e.stopPropagation();
                const a = $(this).closest('.folder-list-link');
                $('#deleteFolderId').val(a.attr('data-folder-id'));
                $('#deleteFolderName').text(a.attr('data-nama'));
                bukaModal('modalDeleteFolder');
            });

            $(document).on('click', '.btn-arsip-folder', function(e) {
                e.stopPropagation();
                const a = $(this).closest('.folder-list-link');
                const id = a.attr('data-folder-id');
                const nama = a.attr('data-nama');

                if (!confirm(
                        `Yakin ingin mengarsipkan folder "${nama}"?\n\nFolder beserta semua subfolder akan dipindahkan ke Arsip.`
                    )) return;

                $.post(`/HR-dashboard/folders/${id}/archive`)
                    .done(res => {
                        if (res.success) {
                            toast(res.message);
                            if (S.folder == id) resetFolderFilter();
                            refreshSemua();
                        } else {
                            toast(res.message || 'Gagal mengarsipkan folder', 'danger');
                        }
                    })
                    .fail(xhr => toast(pesanError(xhr), 'danger'));
            });

            $(document).on('dragstart', '.table-applicant tbody tr[draggable="true"]', function(e) {
                e.originalEvent.dataTransfer.setData('pelamar_id', $(this).attr('data-id'));
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                $(this).addClass('dragging');
            });

            $(document).on('dragend', '.table-applicant tbody tr[draggable="true"]', function() {
                $(this).removeClass('dragging');
                $('.folder-list-link').removeClass('drag-over');
            });

            $(document).on('dragover', '.folder-list-link', function(e) {
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                $(this).addClass('drag-over');
            });

            $(document).on('dragleave', '.folder-list-link', function() {
                $(this).removeClass('drag-over');
            });

            $(document).on('drop', '.folder-list-link', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');

                const target = $(this).attr('data-folder-id');
                const pelamarId = e.originalEvent.dataTransfer.getData('pelamar_id');

                if (pelamarId && target && target !== 'all' && target !== 'unassigned') {
                    movePelamarToFolder(pelamarId, target);
                }
            });

            $('#modalCreateFolder').on('show.bs.modal', function() {
                $('#formCreateFolder')[0].reset();
                isiOpsiFolder('#parentFolderSelectCreate', daftarFolder);
            });

            $('#formCreateFolder').on('submit', function(e) {
                e.preventDefault();
                postForm(this, URL_FOLDER_STORE, 'modalCreateFolder', loadFolderList);
            });

            $('#formEditFolder').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editFolderId').val();

                $.ajax({
                        url: `/HR-dashboard/folders/${id}/rename`,
                        type: 'PUT',
                        data: {
                            nama: $('#editFolderName').val()
                        }
                    })
                    .done(() => {
                        $.post(`/HR-dashboard/folders/${id}/move`, {
                                parent_id: $('#parentFolderSelectEdit').val() || ''
                            })
                            .done(() => toast('Folder berhasil diperbarui'))
                            .fail(() => toast('Nama berhasil diubah, tapi gagal memindahkan folder',
                                'danger'))
                            .always(() => {
                                tutupModal('modalEditFolder');
                                refreshSemua();
                            });
                    })
                    .fail(() => toast('Gagal mengubah nama folder', 'danger'));
            });

            $('#btnKonfirmasiHapusFolder').on('click', function() {
                const id = $('#deleteFolderId').val();

                $.post(`/HR-dashboard/folders/${id}`, {
                        _method: 'DELETE'
                    })
                    .done(res => {
                        if (res.success) {
                            toast(res.message || 'Folder berhasil dihapus');
                            tutupModal('modalDeleteFolder');
                            if (S.folder == id) resetFolderFilter();
                            refreshSemua();
                        } else {
                            toast(res.message || 'Gagal menghapus folder', 'danger');
                        }
                    })
                    .fail(() => toast('Terjadi kesalahan pada server', 'danger'));
            });

            $('#metodeInterview').on('change', function() {
                $('#linkMeetingWrapper').toggle(this.value !== 'offline');
                $('#alamatWrapper').toggle(this.value === 'offline');
            });

            $('#alasanTolak').on('change', function() {
                $('#alasanLainWrapper').toggle(this.value === 'lainnya');
            });

            $(document).on('input', '.input-rupiah', function() {
                const digits = this.value.replace(/\D/g, '');
                this.value = digits ? 'Rp ' + Number(digits).toLocaleString('id-ID') : '';
            });

            $('#btnAcakPassword').on('click', function() {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                let pw = '';
                for (let i = 0; i < 6; i++) pw += chars.charAt(Math.floor(Math.random() * chars.length));
                $('#passwordOffer').val(pw);
            });

            $('#btnTambahPelamar').on('click', bukaFormTambah);
            $('#btnScanDokumen').on('click', scanDokumen);

            $(document).on('click', '.btn-edit-pelamar', function(e) {
                e.preventDefault();
                const id = $(this).attr('data-id');
                gantiModal('modalLihatProfil', () => bukaFormEdit(id));
            });

            $('#formPelamar').on('submit', function(e) {
                e.preventDefault();
                const id = $('#pelamarId').val();
                postForm(this, id ? `${BASE_URL}/${id}/update` : `${BASE_URL}/store`, 'modalFormPelamar');
            });

            $(document).on('click', '.btn-lihat-cv', function(e) {
                e.preventDefault();
                const cv = $(this).attr('data-cv');
                if (!cv) {
                    toast('CV tidak tersedia', 'danger');
                    return;
                }
                bukaPreview('CV - ' + $(this).attr('data-nama'), cv, tipeDariNama(cv));
            });

            $(document).on('click', '.btn-pv-file', function() {
                const judul = $(this).attr('data-judul');
                const url = $(this).attr('data-url');
                const tipe = $(this).attr('data-tipe') || null;
                gantiModal('modalLihatProfil', () => bukaPreview(judul, url, tipe));
            });

            $('#modalPreview').on('hidden.bs.modal', function() {
                previewToken++;
                $('#pvBody').empty();
            });

            $(document).on('click', '.btn-psikotest', function(e) {
                e.preventDefault();
                const id = $(this).attr('data-id');
                const nama = $(this).attr('data-nama');
                gantiModal('modalLihatProfil', () => bukaPsikotest(id, nama));
            });

            $('#modalPsikotest').on('hidden.bs.modal', function() {
                previewToken++;
                if (blobAktif) {
                    URL.revokeObjectURL(blobAktif);
                    blobAktif = null;
                }
                $('#ptPreview').empty();
                refreshSemua();
            });

            $(document).on('click', '.pt-tab', function() {
                PT.aktif = $(this).attr('data-kategori');
                renderPsikotestTabs();
                renderPsikotestForm();
            });

            $(document).on('input', '#rsKode', function() {
                this.value = this.value.toUpperCase().replace(/[^RIASEC]/g, '');
                $(this).data('manual', this.value !== '');
            });

            $(document).on('input', '.rs-skor', function() {
                const $kode = $('#rsKode');
                if ($kode.data('manual')) return;
                $kode.val(hurufTeratas($('#formPsikotest')));
            });

            $(document).on('input', '#ptSkor', function() {
                const lv = levelSkor($(this).val());
                $('#ptLevel').text(lv ? 'Kategori: ' + lv : 'Kategori skor akan tampil otomatis.');
            });

            $(document).on('change', '#ptFile', function() {
                const file = this.files[0];
                if (!file) {
                    const d = PT.data[PT.aktif];
                    if (d && d.file_url) {
                        tampilkanPreview('#ptPreview', {
                            url: d.file_url,
                            tipe: d.preview_tipe,
                            nama: d.file_nama
                        });
                    } else {
                        tampilPesanPreview('#ptPreview', 'fa-file-circle-question',
                            'Pilih file hasil tes untuk melihat pratinjau di sini.');
                    }
                    return;
                }

                const ext = file.name.split('.').pop().toLowerCase();
                if (!['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'].includes(ext)) {
                    toast('Format file tidak didukung.', 'danger');
                    this.value = '';
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    toast('Ukuran file maksimal 10MB.', 'danger');
                    this.value = '';
                    return;
                }

                tampilkanPreview('#ptPreview', {
                    file: file,
                    tipe: tipeDariNama(file.name),
                    nama: file.name
                });
            });

            $('#formPsikotest').on('submit', function(e) {
                e.preventDefault();
                postForm(this, `${BASE_URL}/${PT.pelamarId}/psikotest`, null, function() {
                    muatPsikotest(PT.aktif);
                });
            });

            $(document).on('click', '#btnHapusPsikotest', function() {
                if (!confirm('Hapus hasil psikotest ' + KATEGORI[PT.aktif] + ' beserta dokumennya?'))
                    return;

                $.post(`${BASE_URL}/${PT.pelamarId}/psikotest/${PT.aktif}`, {
                        _method: 'DELETE'
                    })
                    .done(res => {
                        toast(res.message, res.success ? 'success' : 'danger');
                        if (res.success) muatPsikotest(PT.aktif);
                    })
                    .fail(xhr => toast(pesanError(xhr), 'danger'));
            });

            $(document).on('click', '.btn-nilai-pelamar', function(e) {
                e.preventDefault();
                $('#formNilaiPelamar')[0].reset();
                $('#nilaiPelamarId').val($(this).attr('data-id'));
                $('#nilaiNama').val($(this).attr('data-nama'));
                $('#ratingInputNilai').val(0);
                updateBintang(0);
                isiOpsiFolder('#nilaiFolderId', daftarFolder);
                bukaModal('modalNilaiPelamar');
            });

            $(document).on('click', '.star-nilai', function() {
                const value = $(this).data('value');
                $('#ratingInputNilai').val(value);
                updateBintang(value);
            });

            $(document).on('mouseover', '.star-nilai', function() {
                const value = $(this).data('value');
                $('#starContainerNilai .star-nilai').each(function() {
                    $(this).css('color', $(this).data('value') <= value ? '#ffc107' : '#ddd');
                });
            });

            $(document).on('mouseleave', '#starContainerNilai', function() {
                updateBintang(parseInt($('#ratingInputNilai').val()) || 0);
            });

            $('#formNilaiPelamar').on('submit', function(e) {
                e.preventDefault();

                if (!(parseInt($('#ratingInputNilai').val()) > 0)) {
                    toast('Pilih rating terlebih dahulu', 'danger');
                    return;
                }
                if (!$('#nilaiFolderId').val()) {
                    toast('Pilih folder terlebih dahulu', 'danger');
                    return;
                }

                postForm(this, `${BASE_URL}/penilaian`, 'modalNilaiPelamar', () => {
                    refreshSemua();
                    if (PN.reopen) {
                        PN.reopen = false;
                        const id = PN.id, nama = PN.nama;
                        $('#modalNilaiPelamar').one('hidden.bs.modal', () => bukaPenilaian(id, nama));
                    }
                });
            });

            $(document).on('click', '.btn-lihat-profil', function() {
                const id = $(this).attr('data-id');
                $('#profilContent').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                bukaModal('modalLihatProfil');

                $.getJSON(`${BASE_URL}/show/${id}`)
                    .done(json => renderProfil(json.data))
                    .fail(() => $('#profilContent').html(
                        '<div class="text-center py-5 text-danger">Gagal memuat data profil.</div>'));
            });

            $(document).on('click', '.btn-jadwal-interview', function(e) {
                e.preventDefault();
                $('#formJadwalInterview')[0].reset();
                $('#interviewPelamarId').val($(this).attr('data-id'));
                $('#interviewNama').val($(this).attr('data-nama'));
                $('#metodeInterview').trigger('change');
                bukaModal('modalJadwalInterview');
            });

            $('#formJadwalInterview').on('submit', function(e) {
                e.preventDefault();
                postForm(this, `${BASE_URL}/${$('#interviewPelamarId').val()}/jadwal-interview`,
                    'modalJadwalInterview');
            });

            $(document).on('click', '.btn-kirim-offer', function(e) {
                e.preventDefault();
                $('#formKirimOffer')[0].reset();
                $('#offerPelamarId').val($(this).attr('data-id'));
                $('#offerNama').val($(this).attr('data-nama'));
                $('#offerJabatan').val($(this).attr('data-jabatan'));
                bukaModal('modalKirimOffer');
            });

            $('#formKirimOffer').on('submit', function(e) {
                e.preventDefault();
                postForm(this, `${BASE_URL}/${$('#offerPelamarId').val()}/kirim-offer`, 'modalKirimOffer');
            });

            $(document).on('click', '.btn-onboarding', function(e) {
                e.preventDefault();
                $('#formOnboarding')[0].reset();
                $('#onboardPelamarId').val($(this).attr('data-id'));
                $('#onboardDivisi').val($(this).attr('data-divisi'));
                $('#onboardJabatan').val($(this).attr('data-jabatan'));
                if ($(this).attr('data-mulai')) $('#onboardTanggalMulai').val($(this).attr('data-mulai'));
                $('#onboardNik').val('EMP-' + new Date().getFullYear() + '-' + String($(this).attr(
                    'data-id')).padStart(3, '0'));
                bukaModal('modalOnboarding');
            });

            $('#formOnboarding').on('submit', function(e) {
                e.preventDefault();
                postForm(this, `${BASE_URL}/${$('#onboardPelamarId').val()}/onboarding`, 'modalOnboarding');
            });

            $(document).on('click', '.btn-lanjut-tahap', function(e) {
                e.preventDefault();
                $('#lanjutPelamarId').val($(this).attr('data-id'));
                $('#lanjutNama').val($(this).attr('data-nama'));
                $('#lanjutTahapSaatIni').val($(this).attr('data-tahap-saat-ini'));
                if ($(this).attr('data-tahap-berikutnya')) $('#lanjutTahapKe').val($(this).attr(
                    'data-tahap-berikutnya'));
                bukaModal('modalLanjutTahap');
            });

            $('#formLanjutTahap').on('submit', function(e) {
                e.preventDefault();
                postForm(this, `${BASE_URL}/${$('#lanjutPelamarId').val()}/lanjut-tahap`,
                    'modalLanjutTahap');
            });

            $(document).on('click', '.btn-kirim-email', function(e) {
                e.preventDefault();
                $('#formKirimEmail')[0].reset();
                $('#emailPelamarId').val($(this).attr('data-id'));
                $('#emailNama').val($(this).attr('data-nama'));
                $('#emailAlamat').val($(this).attr('data-email'));
                bukaModal('modalKirimEmail');
            });

            $('#formKirimEmail').on('submit', function(e) {
                e.preventDefault();
                postForm(this, `${BASE_URL}/${$('#emailPelamarId').val()}/kirim-email`, 'modalKirimEmail',
                    () => {});
            });

            $(document).on('click', '.btn-tolak', function(e) {
                e.preventDefault();
                $('#formTolak')[0].reset();
                $('#alasanLainWrapper').hide();
                $('#tolakPelamarId').val($(this).attr('data-id'));
                $('#tolakNama').val($(this).attr('data-nama'));
                bukaModal('modalTolak');
            });

            $('#formTolak').on('submit', function(e) {
                e.preventDefault();
                postForm(this, `${BASE_URL}/${$('#tolakPelamarId').val()}/tolak`, 'modalTolak');
            });

            $(document).on('click', '.btn-hapus', function(e) {
                e.preventDefault();
                $('#hapusPelamarId').val($(this).attr('data-id'));
                $('#hapusNama').text($(this).attr('data-nama'));
                bukaModal('modalHapus');
            });

            $('#btnKonfirmasiHapus').on('click', function() {
                $.post(`${BASE_URL}/delete/${$('#hapusPelamarId').val()}`, {
                        _method: 'DELETE'
                    })
                    .done(res => {
                        if (res.success) {
                            toast(res.message);
                            tutupModal('modalHapus');
                            refreshSemua();
                        } else {
                            toast(res.message, 'danger');
                        }
                    })
                    .fail(xhr => toast(pesanError(xhr), 'danger'));
            });
        });
    </script>
@endsection
