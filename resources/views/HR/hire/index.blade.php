@extends('layout_HR.app')
@section('content_HR')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0062ff;
            --primary-soft: #e8f0ff;
            --success: #00b573;
            --warning: #ff9800;
            --info: #00bcd4;
            --danger: #f44336;
            --purple: #7c3aed;
            --secondary: #6b7280;
            --light: #f9fafb;
            --dark: #1f2937;
            --border: #e5e7eb;
        }

        body {
            background-color: #f5f7fb;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--dark);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            font-size: 0.9rem;
            color: var(--secondary);
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.25rem;
            background: #fff;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        .funnel-card {
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 1.25rem;
            background: #fff;
            height: 100%;
            transition: all 0.2s ease;
            cursor: default;
            position: relative;
            overflow: hidden;
        }

        .funnel-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .funnel-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        }

        .funnel-card.success::before {
            background: var(--success);
        }

        .funnel-card.warning::before {
            background: var(--warning);
        }

        .funnel-card.info::before {
            background: var(--info);
        }

        .funnel-card.purple::before {
            background: var(--purple);
        }

        .funnel-card.danger::before {
            background: var(--danger);
        }

        .funnel-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }

        .funnel-icon.bg-primary-soft {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .funnel-icon.bg-success-soft {
            background: #d1fae5;
            color: var(--success);
        }

        .funnel-icon.bg-warning-soft {
            background: #fef3c7;
            color: var(--warning);
        }

        .funnel-icon.bg-info-soft {
            background: #cffafe;
            color: var(--info);
        }

        .funnel-icon.bg-purple-soft {
            background: #ede9fe;
            color: var(--purple);
        }

        .funnel-icon.bg-danger-soft {
            background: #fee2e2;
            color: var(--danger);
        }

        .funnel-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
            margin-bottom: 0.25rem;
        }

        .funnel-label {
            font-size: 0.8rem;
            color: var(--secondary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        /* ===== DROPDOWN ACTION BUTTON ===== */
        .btn-action-dropdown {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border);
            background: white;
            color: var(--dark);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.15s;
        }

        .btn-action-dropdown:hover,
        .btn-action-dropdown.show {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-action-dropdown::after {
            margin-left: 0.25rem;
        }

        .action-dropdown-menu {
            min-width: 200px;
            padding: 0.35rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            animation: fadeIn 0.15s ease;
        }

        .action-dropdown-menu .dropdown-item {
            padding: 0.5rem 0.75rem;
            font-size: 0.825rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark);
            cursor: pointer;
        }

        .action-dropdown-menu .dropdown-item i {
            width: 18px;
            text-align: center;
            font-size: 0.85rem;
        }

        .action-dropdown-menu .dropdown-item:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .action-dropdown-menu .dropdown-item.text-danger:hover {
            background: #fee2e2;
            color: var(--danger);
        }

        .action-dropdown-menu .dropdown-item.text-success:hover {
            background: #dcfce7;
            color: var(--success);
        }

        .action-dropdown-menu .dropdown-item.text-warning:hover {
            background: #fef3c7;
            color: var(--warning);
        }

        .action-dropdown-menu .dropdown-item.text-info:hover {
            background: #cffafe;
            color: var(--info);
        }

        .action-dropdown-menu .dropdown-divider {
            margin: 0.25rem 0;
            border-color: var(--border);
        }

        .table-applicant tbody tr[draggable="true"] {
            cursor: grab;
        }

        .table-applicant tbody tr[draggable="true"]:active {
            cursor: grabbing;
        }

        .table-applicant tbody tr.dragging {
            opacity: 0.4;
            background: var(--primary-soft) !important;
        }

        .folder-list-link.drag-over {
            background: #dcfce7 !important;
            color: #16a34a !important;
            border: 1px dashed #16a34a;
        }

        .folder-list-link.drag-over-invalid {
            background: #fee2e2 !important;
            color: #dc2626 !important;
            border: 1px dashed #dc2626;
        }

        .btn-quick-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border);
            background: white;
            transition: all 0.15s;
        }

        .btn-quick-action:hover {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary);
        }

        .action-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .funnel-trend {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .filter-bar {
            background: #fff;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            border: 1px solid var(--border);
            margin-bottom: 1.25rem;
        }

        .filter-bar .form-control,
        .filter-bar .form-select {
            font-size: 0.875rem;
            padding: 0.5rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            background-color: #fff;
        }

        .filter-bar .form-control:focus,
        .filter-bar .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 98, 255, 0.1);
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
            font-size: 0.85rem;
        }

        .search-wrapper .form-control {
            padding-left: 2.25rem;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #0052d4;
            border-color: #0052d4;
        }

        .btn-outline-secondary {
            color: var(--secondary);
            border-color: var(--border);
        }

        .btn-outline-secondary:hover {
            background-color: var(--light);
            color: var(--dark);
            border-color: var(--border);
        }

        .table-applicant {
            font-size: 0.875rem;
            color: var(--dark);
            margin-bottom: 0;
        }

        .table-applicant thead th {
            background-color: #fafbfc;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            color: var(--secondary);
            letter-spacing: 0.5px;
            padding: 0.85rem 1rem;
            white-space: nowrap;
        }

        .table-applicant tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table-applicant tbody tr {
            transition: background 0.15s;
        }

        .table-applicant tbody tr:hover {
            background-color: #f9fafb;
        }

        .applicant-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .applicant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .applicant-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .applicant-email {
            font-size: 0.75rem;
            color: var(--secondary);
        }

        .position-tag {
            display: inline-block;
            font-weight: 500;
            color: var(--dark);
        }

        .position-dept {
            font-size: 0.75rem;
            color: var(--secondary);
        }

        .av-1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .av-2 {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .av-3 {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .av-4 {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
        }

        .av-5 {
            background: linear-gradient(135deg, #fa709a, #fee140);
        }

        .av-6 {
            background: linear-gradient(135deg, #a18cd1, #fbc2eb);
        }

        .source-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.3rem 0.6rem;
            border-radius: 0.375rem;
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

        .source-badge.website,
        .source-badge.website-perusahaan {
            background: #dbeafe;
            color: #2563eb;
        }

        .source-badge.referral,
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
            gap: 0.35rem;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.35rem 0.7rem;
            border-radius: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
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

        .pagination-info {
            font-size: 0.85rem;
            color: var(--secondary);
        }

        .pagination .page-link {
            border: 1px solid var(--border);
            color: var(--secondary);
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            margin: 0 2px;
            border-radius: 0.375rem;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .modal-content {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
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

        .modal-dialog-scrollable .modal-content>form .modal-footer,
        .modal-dialog-scrollable .modal-footer {
            flex-shrink: 0;
        }

        .modal-header {
            background: linear-gradient(135deg, #f8faff, #eef4ff);
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 1.5rem;
            flex-shrink: 0;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-title i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .modal-header .btn-close {
            opacity: 0.5;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border);
            padding: 1rem 1.5rem;
            background: #fafbfc;
        }

        .modal-lg-custom {
            max-width: 720px;
        }

        .modal-xl-custom {
            max-width: 900px;
        }

        .form-label-custom {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .form-label-custom i {
            color: var(--primary);
            font-size: 0.75rem;
        }

        .form-label-custom .required {
            color: var(--danger);
            margin-left: 2px;
        }

        .form-label-custom .unit-hint {
            font-size: 0.7rem;
            color: var(--secondary);
            font-weight: 500;
            margin-left: auto;
            background: var(--primary-soft);
            padding: 2px 8px;
            border-radius: 10px;
        }

        .form-control-custom,
        .form-select-custom {
            font-size: 0.875rem;
            padding: 0.55rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            background-color: #fff;
            width: 100%;
            transition: all 0.15s;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 98, 255, 0.1);
            outline: none;
        }

        textarea.form-control-custom {
            resize: vertical;
            min-height: 80px;
        }

        .form-hint {
            font-size: 0.72rem;
            color: var(--secondary);
            margin-top: 0.25rem;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 2rem 1.5rem;
            color: #fff;
            text-align: center;
            position: relative;
            flex-shrink: 0;
        }

        .profile-avatar-lg {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 2rem;
            margin: 0 auto 1rem;
            border: 3px solid rgba(255, 255, 255, 0.4);
        }

        .profile-name {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .profile-position {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .profile-contact {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.8rem;
            opacity: 0.95;
        }

        .profile-contact span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .profile-body-scroll {
            overflow-y: auto;
            flex-grow: 1;
            min-height: 0;
        }

        .profile-section {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .profile-section:last-child {
            border-bottom: none;
        }

        .profile-section-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.85rem;
        }

        .profile-item-label {
            font-size: 0.7rem;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0.15rem;
        }

        .profile-item-value {
            font-size: 0.875rem;
            color: var(--dark);
            font-weight: 500;
        }

        .skill-tag {
            display: inline-block;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 1rem;
            margin: 0.15rem 0.15rem 0.15rem 0;
        }

        .timeline-item {
            display: flex;
            gap: 0.75rem;
            padding-bottom: 0.85rem;
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
            font-size: 0.7rem;
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

        .timeline-content {
            flex: 1;
            padding-top: 2px;
        }

        .timeline-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.15rem;
        }

        .timeline-desc {
            font-size: 0.75rem;
            color: var(--secondary);
        }

        .timeline-rating {
            display: flex;
            gap: 2px;
            margin-top: 0.35rem;
        }

        .timeline-rating .star {
            color: #fbbf24;
            font-size: 0.85rem;
        }

        .timeline-rating .star.empty {
            color: #d1d5db;
        }

        .timeline-feedback {
            background: #f9fafb;
            border-left: 3px solid var(--primary);
            padding: 0.5rem 0.75rem;
            margin-top: 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            color: var(--dark);
            line-height: 1.5;
        }

        .timeline-feedback::before {
            content: "💬 ";
            margin-right: 0.25rem;
        }

        .confirm-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
        }

        .confirm-icon.danger {
            background: #fee2e2;
            color: var(--danger);
        }

        .confirm-icon.warning {
            background: #fef3c7;
            color: var(--warning);
        }

        .confirm-icon.success {
            background: #d1fae5;
            color: var(--success);
        }

        .confirm-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .confirm-desc {
            font-size: 0.875rem;
            color: var(--secondary);
            text-align: center;
            line-height: 1.5;
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
            z-index: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            position: relative;
            z-index: 1;
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
            font-size: 0.8rem;
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
            font-size: 0.7rem;
            color: var(--secondary);
            font-weight: 500;
            text-align: center;
        }

        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }

        #toastContainer {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .toast-msg {
            background: #1f2937;
            color: #fff;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease;
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

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        @media (max-width: 767px) {
            .funnel-value {
                font-size: 1.4rem;
            }

            .applicant-info {
                gap: 0.5rem;
            }

            .applicant-avatar {
                width: 36px;
                height: 36px;
                font-size: 0.75rem;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        .dropdown-menu {
            border-radius: 0.5rem;
            padding: 0.5rem 0;
            animation: fadeIn 0.15s ease;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: all 0.15s;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover {
            background-color: var(--primary-soft);
            color: var(--primary);
        }

        .dropdown-item.text-danger:hover {
            background-color: #fee2e2;
            color: var(--danger);
        }

        .dropdown-item i {
            width: 16px;
            text-align: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== FOLDER SIDEBAR ===== */
        .hire-layout {
            display: flex;
            gap: 20px;
        }

        .folder-sidebar {
            width: 260px;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 1rem;
            flex-shrink: 0;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .folder-sidebar-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--secondary);
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
            padding: 0 0.5rem;
        }

        .folder-list {
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
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.15s;
            color: var(--dark);
            font-size: 0.85rem;
            text-decoration: none;
            gap: 0.5rem;
        }

        .folder-list-link:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .folder-list-link.active {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }

        .folder-list-link i.folder-icon {
            color: #ffc107;
            font-size: 1rem;
        }

        .folder-list-link.inbox-link i.folder-icon {
            color: var(--info);
        }

        .folder-list-link.active i.folder-icon {
            color: white !important;
        }

        .folder-list-link .folder-label {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .folder-list-link .folder-count {
            background: rgba(0, 0, 0, 0.08);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .folder-list-link.active .folder-count {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        .folder-list-link .pin-icon {
            color: #ffc107;
            font-size: 0.7rem;
        }

        .folder-list-link.active .pin-icon {
            color: white;
        }

        .folder-sidebar-divider {
            height: 1px;
            background: var(--border);
            margin: 0.75rem 0;
        }

        .hire-main-content {
            flex: 1;
            min-width: 0;
        }

        .folder-active-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: var(--primary-soft);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .folder-active-badge button {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            padding: 0;
            margin-left: 0.25rem;
            font-size: 0.85rem;
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

        /* ===== RATING DI TABEL ===== */
        .table-rating {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #ffc107;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .table-rating .star-empty {
            color: #e5e7eb;
        }

        .table-rating-info {
            font-size: 0.7rem;
            color: var(--secondary);
            margin-top: 2px;
        }

        .table-rating-empty {
            color: #d1d5db;
            font-size: 0.8rem;
            font-style: italic;
        }

        /* ===== SECTION PENILAIAN DI MODAL PROFIL ===== */
        .penilaian-section {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .penilaian-section:last-child {
            border-bottom: none;
        }

        .penilaian-summary {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 0.75rem;
            padding: 1.25rem;
            text-align: center;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .penilaian-summary-item {
            text-align: center;
        }

        .penilaian-summary-value {
            font-size: 2rem;
            font-weight: 700;
            color: #92400e;
            line-height: 1;
        }

        .penilaian-summary-stars {
            color: #f59e0b;
            font-size: 1.3rem;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        .penilaian-summary-label {
            font-size: 0.75rem;
            color: #78350f;
            margin-top: 4px;
            font-weight: 500;
        }

        .penilaian-card {
            background: #f9fafb;
            border-left: 4px solid var(--primary);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.15s;
        }

        .penilaian-card:hover {
            background: #f3f4f6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .penilaian-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .penilaian-interviewer-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .penilaian-interviewer-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0062ff, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .penilaian-interviewer-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
            line-height: 1.2;
        }

        .penilaian-interviewer-role {
            font-size: 0.7rem;
            color: var(--secondary);
        }

        .penilaian-date {
            font-size: 0.7rem;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .penilaian-rating-display {
            color: #ffc107;
            font-size: 1rem;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .penilaian-rating-display .star-empty {
            color: #e5e7eb;
        }

        .penilaian-catatan-box {
            background: white;
            padding: 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.85rem;
            color: #4b5563;
            line-height: 1.5;
            margin-top: 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .penilaian-catatan-box strong {
            color: var(--dark);
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .penilaian-folder-badge {
            display: inline-block;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 0.5rem;
        }

        .penilaian-file-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--primary);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 0.5rem;
            padding: 4px 10px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            transition: all 0.15s;
        }

        .penilaian-file-link:hover {
            background: var(--primary-soft);
            text-decoration: none;
        }

        .penilaian-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--secondary);
        }

        .penilaian-empty i {
            font-size: 2.5rem;
            opacity: 0.3;
            margin-bottom: 0.75rem;
            display: block;
        }

        .btn-action-dropdown {
            padding: 0.35rem 0.6rem;
            font-size: 0.85rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border);
            background: white;
            color: var(--dark);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.15s;
            cursor: pointer;
        }

        .btn-action-dropdown:hover,
        .btn-action-dropdown.show {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-action-dropdown i {
            font-size: 0.9rem;
        }

        .action-dropdown-menu {
            min-width: 220px;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            animation: dropdownFadeIn 0.15s ease;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .action-dropdown-menu .dropdown-item {
            padding: 0.6rem 0.85rem;
            font-size: 0.85rem;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--dark);
            cursor: pointer;
            transition: all 0.15s;
            margin-bottom: 2px;
        }

        .action-dropdown-menu .dropdown-item i {
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .action-dropdown-menu .dropdown-item:hover {
            background: var(--primary-soft);
            color: var(--primary);
        }

        /* Color variants for dropdown items */
        .action-dropdown-menu .dropdown-item.dropdown-item-danger {
            color: var(--danger);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-danger:hover {
            background: #fee2e2;
            color: var(--danger);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-success {
            color: var(--success);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-success:hover {
            background: #dcfce7;
            color: var(--success);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-warning {
            color: var(--warning);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-warning:hover {
            background: #fef3c7;
            color: var(--warning);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-info {
            color: var(--info);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-info:hover {
            background: #cffafe;
            color: var(--info);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-purple {
            color: var(--purple);
        }

        .action-dropdown-menu .dropdown-item.dropdown-item-purple:hover {
            background: #ede9fe;
            color: var(--purple);
        }

        /* Divider in dropdown */
        .action-dropdown-menu .dropdown-divider {
            margin: 0.4rem 0;
            border-color: var(--border);
            opacity: 0.6;
        }

        /* Quick action button (Lihat Profil) */
        .btn-quick-action {
            padding: 0.35rem 0.6rem;
            font-size: 0.85rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border);
            background: white;
            color: var(--dark);
            transition: all 0.15s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-quick-action:hover {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-quick-action i {
            font-size: 0.9rem;
        }

        /* Action cell container */
        .action-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        /* Drag and drop styles */
        .table-applicant tbody tr[draggable="true"] {
            cursor: grab;
        }

        .table-applicant tbody tr[draggable="true"]:active {
            cursor: grabbing;
        }

        .table-applicant tbody tr.dragging {
            opacity: 0.4;
            background: var(--primary-soft) !important;
        }

        .folder-list-link.drag-over {
            background: #dcfce7 !important;
            color: #16a34a !important;
            border: 1px dashed #16a34a;
        }

        .folder-list-link.drag-over-invalid {
            background: #fee2e2 !important;
            color: #dc2626 !important;
            border: 1px dashed #dc2626;
        }

        .folder-list-nested {
            list-style: none;
            padding-left: 20px;
            margin: 0;
            border-left: 1px dashed #e5e7eb;
            margin-left: 18px;
            margin-top: 2px;
            margin-bottom: 2px;
        }

        .folder-list-nested .folder-list-link {
            padding: 0.4rem 0.65rem;
            font-size: 0.8rem;
        }

        .folder-list-nested .folder-list-link i.folder-icon {
            font-size: 0.9rem;
        }

        .folder-list-nested .folder-list-link .folder-count {
            font-size: 0.65rem;
            padding: 1px 6px;
        }

        .folder-toggle {
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #9ca3af;
            font-size: 0.65rem;
            transition: transform 0.2s;
            flex-shrink: 0;
            margin-right: 2px;
        }

        .folder-toggle.expanded {
            transform: rotate(90deg);
        }

        .folder-toggle:hover {
            color: var(--primary);
        }

        .folder-list-link .folder-actions {
            display: none;
            gap: 3px;
            margin-left: 5px;
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
            border-radius: 4px;
            border: none;
            background: white;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            transition: all 0.15s;
        }

        .folder-action-btn:hover {
            background: var(--primary);
            color: white;
        }

        .folder-action-btn.danger:hover {
            background: #dc2626;
        }

        .folder-action-btn.archive:hover {
            background: #f59e0b;
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div id="toastContainer"></div>

    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="page-title">Pelamar Baru</h1>
                <p class="page-subtitle mb-0">Kelola seluruh pelamar yang masuk pada proses rekrutmen</p>
            </div>
            <div>
                <a href="{{ route('HR.hire.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}"
                    class="btn btn-outline-secondary me-2">
                    <i class="fa-solid fa-file-export me-1"></i> Export
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPelamar">
                    <i class="fa-solid fa-user-plus me-1"></i> Tambah Pelamar
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4 col-6">
                <div class="funnel-card">
                    <div class="funnel-icon bg-primary-soft"><i class="fa-solid fa-inbox"></i></div>
                    <div class="funnel-label">Total Lamaran</div>
                    <div class="funnel-value" id="funnel-total">{{ $funnel['total'] }}</div>
                    <div class="funnel-trend"><i class="fa-solid fa-users"></i> Semua tahap aktif</div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="funnel-card info">
                    <div class="funnel-icon bg-info-soft"><i class="fa-solid fa-clipboard-check"></i></div>
                    <div class="funnel-label">Sedang Screening</div>
                    <div class="funnel-value" id="funnel-screening">{{ $funnel['screening'] }}</div>
                    <div class="funnel-trend"><i class="fa-solid fa-magnifying-glass"></i> Menunggu review</div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="funnel-card warning">
                    <div class="funnel-icon bg-warning-soft"><i class="fa-solid fa-user-tie"></i></div>
                    <div class="funnel-label">Jadwal Interview</div>
                    <div class="funnel-value" id="funnel-interview">{{ $funnel['interview'] }}</div>
                    <div class="funnel-trend"><i class="fa-solid fa-calendar"></i> Dalam proses</div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="funnel-card purple">
                    <div class="funnel-icon bg-purple-soft"><i class="fa-solid fa-file-signature"></i></div>
                    <div class="funnel-label">Tahap Offer</div>
                    <div class="funnel-value" id="funnel-offer">{{ $funnel['offer'] }}</div>
                    <div class="funnel-trend"><i class="fa-solid fa-file-invoice"></i> Menunggu respons</div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="funnel-card success">
                    <div class="funnel-icon bg-success-soft"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="funnel-label">Diterima</div>
                    <div class="funnel-value" id="funnel-hired">{{ $funnel['hired'] }}</div>
                    <div class="funnel-trend"><i class="fa-solid fa-user-check"></i> Siap onboarding</div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="funnel-card danger">
                    <div class="funnel-icon bg-danger-soft"><i class="fa-solid fa-circle-xmark"></i></div>
                    <div class="funnel-label">Ditolak</div>
                    <div class="funnel-value" id="funnel-rejected">{{ $funnel['rejected'] }}</div>
                    <div class="funnel-trend"><i class="fa-solid fa-xmark"></i> Tidak lanjut</div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('HR.hire.index') }}" id="filterForm">
            <div class="filter-bar">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="search-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nama pelamar, email, atau posisi..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <select name="jabatan" class="form-select">
                            <option value="">Semua Posisi</option>
                            @foreach ($dataJabatan as $jab)
                                <option value="{{ $jab }}" {{ request('jabatan') == $jab ? 'selected' : '' }}>
                                    {{ $jab }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select name="tahap" class="form-select">
                            <option value="">Semua Tahap</option>
                            @foreach ($tahapList as $key => $label)
                                <option value="{{ $key }}" {{ request('tahap') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select name="sumber" class="form-select">
                            <option value="">Semua Sumber</option>
                            @foreach ($sumberList as $src)
                                <option value="{{ $src }}" {{ request('sumber') == $src ? 'selected' : '' }}>
                                    {{ $src }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 text-end">
                        <a href="{{ route('HR.hire.index') }}" class="btn btn-outline-secondary me-1" title="Reset">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                        <button type="submit" class="btn btn-primary" title="Filter">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="hire-layout">

            <aside class="folder-sidebar">
                <div class="folder-sidebar-title">
                    <i class="fa-solid fa-folder-tree me-1"></i> Kategori
                </div>
                <ul class="folder-list" id="hireFolderList">
                    <li class="folder-list-item">
                        <a class="folder-list-link inbox-link active" data-folder-id="all"
                            onclick="filterByFolder('all', 'Semua Pelamar')">
                            <i class="fa-solid fa-users folder-icon"></i>
                            <span class="folder-label">Semua Pelamar</span>
                            <span class="folder-count" id="countAll">{{ $pelamars->total() }}</span>
                        </a>
                    </li>
                    <li class="folder-list-item">
                        <a class="folder-list-link inbox-link" data-folder-id="unassigned"
                            onclick="filterByFolder('unassigned', 'Belum Masuk Folder')">
                            <i class="fa-solid fa-inbox folder-icon"></i>
                            <span class="folder-label">Belum Masuk Folder</span>
                            <span class="folder-count" id="countUnassigned">-</span>
                        </a>
                    </li>
                    <li class="folder-list-item">
                        <a class="folder-list-link inbox-link"
                            href="{{ route('HR.arsip.index', ['layout' => 'hr']) }}">
                            <i class="fa-solid fa-inbox folder-icon"></i>
                            <span class="folder-label">Data Arsip</span>
                        </a>
                    </li>
                </ul>

                <div class="folder-sidebar-divider"></div>

                <div class="folder-sidebar-title">
                    <i class="fa-solid fa-folder me-1"></i> Folder
                </div>
                <ul class="folder-list" id="hireFolderTree">
                    <li class="text-center text-muted py-3" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-spinner fa-spin"></i> Memuat...
                    </li>
                </ul>
            </aside>

            {{-- MAIN CONTENT --}}
            <div class="hire-main-content">

                {{-- Active Folder Indicator --}}
                <div class="mb-3" id="activeFolderIndicator" style="display: none;">
                    <span class="folder-active-badge">
                        <i class="fa-solid fa-folder-open"></i>
                        <span id="activeFolderName">-</span>
                        <button onclick="resetFolderFilter()" title="Tampilkan semua">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </span>
                </div>

                <form method="GET" action="{{ route('HR.hire.index') }}" id="filterForm">
                </form>

                <div class="card" id="pelamarTableCard">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="m-0 fw-bold">
                                <i class="fa-solid fa-users-rectangle me-2 text-primary"></i>Daftar Pelamar
                            </h6>
                            <small class="text-muted" id="tableInfo">
                                Menampilkan <span class="fw-semibold">{{ $pelamars->count() }}</span>
                                dari <span class="fw-semibold">{{ $pelamars->total() }}</span> pelamar
                            </small>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateFolder">
                                <i class="fa fa-plus me-3"></i> Buat Folder Baru
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" id="pelamarTableWrapper">
                            @include('HR.hire._pelamar_table', ['pelamars' => $pelamars])
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top"
                            id="paginationWrapper">
                            <div class="pagination-info">
                                Menampilkan
                                <span class="fw-semibold">{{ $pelamars->firstItem() ?? 0 }}</span>–<span
                                    class="fw-semibold">{{ $pelamars->lastItem() ?? 0 }}</span>
                                dari <span class="fw-semibold">{{ $pelamars->total() }}</span> pelamar
                            </div>
                            <nav>{{ $pelamars->withQueryString()->links('pagination::bootstrap-5') }}</nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahPelamar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus"></i> Tambah Pelamar Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTambahPelamar" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-light border d-flex align-items-center gap-2 mb-3"
                            style="font-size:0.85rem;">
                            <i class="fa-solid fa-circle-info text-primary"></i>
                            <div>Form ini digunakan untuk memasukkan data pelamar secara manual (misal dari walk-in, email
                                langsung, atau sumber offline).</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-user"></i> Nama Lengkap <span
                                        class="required">*</span></label>
                                <input type="text" name="nama_lengkap" class="form-control-custom"
                                    placeholder="Nama lengkap pelamar" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-at"></i> Email <span
                                        class="required">*</span></label>
                                <input type="email" name="email" class="form-control-custom"
                                    placeholder="email@contoh.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-phone"></i> No. Telepon <span
                                        class="required">*</span></label>
                                <input type="text" name="no_telepon" class="form-control-custom"
                                    placeholder="+62 812-xxxx-xxxx" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-location-dot"></i> Domisili</label>
                                <input type="text" name="domisili" class="form-control-custom"
                                    placeholder="Kota, Provinsi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-building"></i> Divisi <span
                                        class="required">*</span></label>
                                <select name="divisi" class="form-select-custom" required>
                                    <option value="">Pilih Divisi</option>
                                    @foreach ($dataDivisi as $div)
                                        <option value="{{ $div }}">{{ $div }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-briefcase"></i> Posisi Dilamar
                                    <span class="required">*</span></label>
                                <select name="jabatan" class="form-select-custom" required>
                                    <option value="">Pilih Posisi</option>
                                    @foreach ($dataJabatan as $jab)
                                        <option value="{{ $jab }}">{{ $jab }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-id-badge"></i> Detail Posisi <small
                                        class="text-muted">(Opsional)</small></label>
                                <input type="text" name="detail_jabatan" class="form-control-custom"
                                    placeholder="Contoh: Frontend Developer, Staff Admin...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-calendar-day"></i> Tanggal Melamar
                                    <span class="required">*</span></label>
                                <input type="date" name="tanggal_melamar" class="form-control-custom"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-circle-nodes"></i> Sumber Lamaran
                                    <span class="required">*</span></label>
                                <input type="text" name="sumber_lamaran" class="form-control-custom"
                                    list="sumberList" placeholder="Pilih atau ketik sumber..." required>
                                <datalist id="sumberList">
                                    @foreach ($sumberList as $src)
                                        <option value="{{ $src }}">
                                    @endforeach
                                </datalist>
                                <div class="form-hint">Pilih dari daftar atau ketik manual jika sumber tidak tersedia.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-graduation-cap"></i> Pendidikan
                                    Terakhir</label>
                                <select name="pendidikan_terakhir" class="form-select-custom">
                                    @foreach (\App\Models\Pelamar::PENDIDIKAN as $pend)
                                        <option value="{{ $pend }}" {{ $pend === 'S1' ? 'selected' : '' }}>
                                            {{ $pend }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-code"></i> Keahlian Utama</label>
                                <input type="text" name="keahlian" class="form-control-custom"
                                    placeholder="Pisahkan dengan koma, contoh: React, JavaScript, Tailwind">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-paperclip"></i> Upload CV</label>
                                <input type="file" name="cv" class="form-control-custom"
                                    accept=".pdf,.doc,.docx">
                                <div class="form-hint">Format: PDF, DOC, DOCX. Maks. 5MB.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-message"></i> Catatan</label>
                                <textarea name="catatan_hr" class="form-control-custom" rows="2"
                                    placeholder="Catatan tambahan tentang pelamar..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanPelamar">
                            <i class="fa-solid fa-save me-1"></i> Simpan Pelamar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLihatProfil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content p-0" id="profilContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalJadwalInterview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-regular fa-calendar"></i> Jadwalkan Interview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formJadwalInterview">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="interviewPelamarId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-user"></i> Nama Pelamar</label>
                                <input type="text" id="interviewNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-layer-group"></i> Tahap Interview
                                    <span class="required">*</span></label>
                                <select name="tahap_interview" class="form-select-custom" required>
                                    @foreach (\App\Models\Pelamar::TAHAP_INTERVIEW as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-calendar-day"></i> Tanggal <span
                                        class="required">*</span></label>
                                <input type="date" name="tanggal" class="form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-clock"></i> Waktu <span
                                        class="required">*</span></label>
                                <input type="time" name="waktu" class="form-control-custom" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-video"></i> Metode Interview <span
                                        class="required">*</span></label>
                                <select name="metode_interview" class="form-select-custom" id="metodeInterview" required>
                                    @foreach (\App\Models\Pelamar::METODE_INTERVIEW as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12" id="linkMeetingWrapper">
                                <label class="form-label-custom"><i class="fa-solid fa-link"></i> Link Meeting</label>
                                <input type="url" name="link_meeting" class="form-control-custom"
                                    placeholder="https://zoom.us/j/...">
                            </div>
                            <div class="col-12" id="alamatWrapper" style="display:none;">
                                <label class="form-label-custom"><i class="fa-solid fa-location-dot"></i> Alamat /
                                    Ruangan</label>
                                <input type="text" name="lokasi_interview" class="form-control-custom"
                                    placeholder="Lantai 3, Ruang Meeting A">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-users"></i> Interviewer <span
                                        class="required">*</span></label>
                                <select name="interviewer[]" class="form-select-custom" multiple required
                                    style="min-height: 120px;">
                                    @foreach ($Interviewer as $hrUser)
                                        <option value="{{ $hrUser->nama_lengkap }}">
                                            {{ $hrUser->nama_lengkap }} - {{ $hrUser->jabatan }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-hint">Tekan Ctrl/Cmd untuk memilih lebih dari satu interviewer</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-message"></i> Catatan
                                    (Opsional)</label>
                                <textarea name="catatan" class="form-control-custom" rows="2" placeholder="Catatan untuk pelamar..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notif_email"
                                        id="notifInterview" value="1" checked>
                                    <label class="form-check-label" for="notifInterview" style="font-size:0.85rem;">
                                        Kirim notifikasi email ke pelamar
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKirimOffer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-file-signature"></i> Kirim Penawaran (Offering)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formKirimOffer" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="offerPelamarId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-user"></i> Nama Pelamar</label>
                                <input type="text" id="offerNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-briefcase"></i> Posisi
                                    Ditawarkan</label>
                                <input type="text" id="offerJabatan" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>

                            <div class="col-12">
                                <hr class="my-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fa-solid fa-money-bill-trend-up text-primary"></i>
                                    <strong style="font-size:0.85rem;">Komponen Gaji</strong>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">
                                    <i class="fa-solid fa-sack-dollar"></i> Gaji Pokok (Bulanan)
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="gaji_ditawarkan" class="form-control-custom"
                                    placeholder="Rp 8.000.000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">
                                    <i class="fa-solid fa-file-contract"></i> Status Karyawan
                                </label>
                                <select name="status_kepegawaian" class="form-select-custom">
                                    @foreach (\App\Models\Pelamar::STATUS_KEPEGAWAIAN as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <hr class="my-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fa-solid fa-hand-holding-dollar text-success"></i>
                                    <strong style="font-size:0.85rem;">Tunjangan Wajib (Harian)</strong>
                                    <span class="form-label-custom unit-hint">Dihitung per hari kerja</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">
                                    <i class="fa-solid fa-utensils"></i> Tunjangan Makan
                                    <span class="required">*</span>
                                    <span class="unit-hint">/ hari</span>
                                </label>
                                <input type="text" name="tunjangan_makan" class="form-control-custom"
                                    placeholder="Rp 25.000" required>
                                <div class="form-hint">Nominal tunjangan makan yang diterima pelamar per hari kerja.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">
                                    <i class="fa-solid fa-car-side"></i> Tunjangan Transportasi
                                    <span class="required">*</span>
                                    <span class="unit-hint">/ hari</span>
                                </label>
                                <input type="text" name="tunjangan_transport" class="form-control-custom"
                                    placeholder="Rp 20.000" required>
                                <div class="form-hint">Nominal tunjangan transportasi yang diterima pelamar per hari kerja.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom">
                                    <i class="fa-solid fa-calendar-check"></i> Tanggal Mulai Kerja
                                    <span class="required">*</span>
                                </label>
                                <input type="date" name="tanggal_mulai_kerja" class="form-control-custom" required>
                            </div>

                            <div class="col-12">
                                <hr class="my-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fa-solid fa-gift text-warning"></i>
                                    <strong style="font-size:0.85rem;">Benefit & Pesan (Opsional)</strong>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-gift"></i> Benefit Lainnya</label>
                                <textarea name="benefit_lainnya" class="form-control-custom" rows="2"
                                    placeholder="BPJS Kesehatan, BPJS Ketenagakerjaan, Tunjangan Pulsa, dll..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-message"></i> Pesan
                                    Tambahan</label>
                                <textarea name="pesan_tambahan" class="form-control-custom" rows="2"
                                    placeholder="Selamat! Kami sangat terkesan dengan kualifikasi Anda..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">
                                    <i class="fa-solid fa-paperclip"></i> Lampiran Surat Offer (PDF)
                                    <span class="required">*</span>
                                </label>
                                <input type="file" name="lampiran_offer" class="form-control-custom" accept=".pdf" required>
                                <div class="form-hint">
                                    💡 Pastikan file PDF sudah dikunci dengan password sebelum diupload.
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom">
                                    <i class="fa-solid fa-key"></i> Password Dokumen
                                    <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" name="password_offer" id="passwordOffer" 
                                        class="form-control-custom" 
                                        placeholder="Masukkan password yang Anda gunakan untuk mengunci PDF" 
                                        required>
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="generateRandomPassword()" 
                                            title="Generate password acak"
                                            style="border-radius: 0 0.5rem 0.5rem 0; border-left: none;">
                                        <i class="fa-solid fa-shuffle"></i> Acak
                                    </button>
                                </div>
                                <div class="form-hint">
                                    🔐 Password ini akan ditampilkan di body email agar pelamar bisa membuka dokumen.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Kirim Offering
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalOnboarding" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-user-plus"></i> Proses Onboarding Karyawan Baru</h5>
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
                                <label class="form-label-custom"><i class="fa-solid fa-id-card"></i> NIK Karyawan</label>
                                <input type="text" name="nik_karyawan" id="onboardNik" class="form-control-custom"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-calendar-check"></i> Tanggal Mulai
                                    Kerja <span class="required">*</span></label>
                                <input type="date" name="tanggal_mulai_kerja" id="onboardTanggalMulai"
                                    class="form-control-custom" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-briefcase"></i> Divisi</label>
                                <input type="text" id="onboardDivisi" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-user-tie"></i> Jabatan</label>
                                <input type="text" id="onboardJabatan" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-user-shield"></i> Atasan Langsung
                                    <span class="required">*</span></label>
                                <input type="text" name="atasan_langsung" class="form-control-custom"
                                    placeholder="Nama atasan langsung" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-file-contract"></i> Status
                                    Kepegawaian</label>
                                <select name="status_kepegawaian" class="form-select-custom">
                                    @foreach (\App\Models\Pelamar::STATUS_KEPEGAWAIAN as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-clipboard-list"></i> Checklist
                                    Onboarding</label>
                                <div class="border rounded p-3" style="background:#fafbfc;">
                                    @foreach (\App\Models\Pelamar::CHECKLIST_ONBOARDING_DEFAULT as $key => $label)
                                        <div class="form-check {{ !$loop->last ? 'mb-2' : '' }}">
                                            <input class="form-check-input" type="checkbox" name="checklist_onboarding[]"
                                                value="{{ $key }}" id="ob_{{ $key }}"
                                                {{ in_array($key, ['kontrak_ditandatangani', 'dokumen_pribadi']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ob_{{ $key }}"
                                                style="font-size:0.85rem;">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-user-check me-1"></i> Proses Jadi Karyawan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLanjutTahap" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-arrow-right-to-bracket"></i> Lanjut ke Tahapan
                        Berikutnya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formLanjutTahap">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="lanjutPelamarId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-user"></i> Pelamar</label>
                                <input type="text" id="lanjutNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-flag"></i> Tahap Saat Ini</label>
                                <input type="text" id="lanjutTahapSaatIni" class="form-control-custom" readonly
                                    style="background:#fef3c7; color:#d97706; font-weight:600;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-arrow-right"></i> Lanjut ke Tahap
                                    <span class="required">*</span></label>
                                <select name="tahap_ke" id="lanjutTahapKe" class="form-select-custom" required>
                                    @foreach (\App\Models\Pelamar::TAHAP as $key => $label)
                                        @if (!in_array($key, ['applied', 'rejected']))
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-star"></i> Penilaian
                                    (Opsional)</label>
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
                                <label class="form-label-custom"><i class="fa-solid fa-comment"></i> Catatan / Feedback
                                    (Opsional)</label>
                                <textarea name="keterangan" class="form-control-custom" rows="2" placeholder="Tulis catatan evaluasi...">{{ auth()->user()->username }} - {{ auth()->user()->jabatan }} : </textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notif_email" id="notifPelamar"
                                        value="1">
                                    <label class="form-check-label" for="notifPelamar" style="font-size:0.85rem;">
                                        Kirim notifikasi email ke pelamar
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-arrow-right me-1"></i> Lanjutkan Tahapan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKirimEmail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-regular fa-envelope"></i> Kirim Email ke Pelamar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formKirimEmail" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="emailPelamarId">
                    <div class="modal-body">
                        <div class="alert alert-light border d-flex align-items-center gap-2 mb-3"
                            style="font-size:0.85rem;">
                            <i class="fa-solid fa-circle-info text-primary"></i>
                            <div>Fitur ini bersifat <strong>opsional</strong>. Gunakan jika Anda ingin mengirim email kustom
                                ke pelamar.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-user"></i> Penerima</label>
                                <input type="text" id="emailNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="fa-solid fa-at"></i> Email</label>
                                <input type="email" id="emailAlamat" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-heading"></i> Subjek <span
                                        class="required">*</span></label>
                                <input type="text" name="subjek" class="form-control-custom"
                                    placeholder="Subjek email..." required>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-align-left"></i> Isi Email <span
                                        class="required">*</span></label>
                                <textarea name="isi_email" class="form-control-custom" rows="6" placeholder="Tulis isi email di sini..."
                                    required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-paperclip"></i> Lampiran
                                    (Opsional)</label>
                                <input type="file" name="lampiran[]" class="form-control-custom" multiple>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Kirim Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTolak" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-circle-xmark" style="color:var(--danger);"></i> Tolak
                        Lamaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTolak">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="tolakPelamarId">
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <div class="confirm-icon danger"><i class="fa-solid fa-user-xmark"></i></div>
                            <div class="confirm-title">Yakin ingin menolak pelamar ini?</div>
                            <div class="confirm-desc">Tindakan ini akan mengubah status pelamar menjadi "Ditolak".</div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-user"></i> Pelamar</label>
                                <input type="text" id="tolakNama" class="form-control-custom" readonly
                                    style="background:#f9fafb;">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-circle-exclamation"></i> Alasan
                                    Penolakan (Opsional)</label>
                                <select name="alasan_penolakan" class="form-select-custom" id="alasanTolak">
                                    <option value="">-- Pilih alasan --</option>
                                    @foreach (\App\Models\Pelamar::ALASAN_PENOLAKAN as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12" id="alasanLainWrapper" style="display:none;">
                                <label class="form-label-custom"><i class="fa-solid fa-pen"></i> Alasan Lainnya</label>
                                <textarea name="alasan_lainnya" class="form-control-custom" rows="2"
                                    placeholder="Jelaskan alasan penolakan..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom"><i class="fa-solid fa-comment"></i> Catatan Internal
                                    (Opsional)</label>
                                <textarea name="catatan_internal" class="form-control-custom" rows="2" placeholder="Catatan untuk tim HR..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notif_email"
                                        id="kirimNotifTolak" value="1">
                                    <label class="form-check-label" for="kirimNotifTolak" style="font-size:0.85rem;">
                                        Kirim email notifikasi penolakan ke pelamar
                                    </label>
                                </div>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" name="simpan_talent_pool"
                                        id="simpanTalent" value="1">
                                    <label class="form-check-label" for="simpanTalent" style="font-size:0.85rem;">
                                        Simpan ke talent pool untuk lowongan di masa depan
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-xmark me-1"></i> Tolak Lamaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="confirm-icon danger"><i class="fa-solid fa-trash"></i></div>
                    <div class="confirm-title">Hapus Data Pelamar?</div>
                    <div class="confirm-desc mb-3">
                        Data pelamar <strong id="hapusNama">-</strong> akan dihapus permanen dan tidak dapat dikembalikan.
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-top-0 pt-0">
                    <input type="hidden" id="hapusPelamarId">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiHapus">
                        <i class="fa-solid fa-trash me-1"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL CV VIEWER ===== --}}
    <div class="modal fade" id="modalCVViewer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1f2937, #374151); color: white;">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-file-pdf me-2"></i>
                        <span id="cvTitle">CV Pelamar</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="#" id="cvDownloadBtn" class="btn btn-sm btn-outline-light" target="_blank">
                            <i class="fa-solid fa-download"></i> Download
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0">
                    <div id="cvViewerContainer" style="width: 100%; height: 75vh; background: #f3f4f6;">
                        <div class="text-center py-5">
                            <i class="fa-solid fa-spinner fa-spin fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Memuat CV...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL PENILAIAN ===== --}}
    <div class="modal fade" id="modalNilaiPelamar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-star text-warning"></i> Beri Penilaian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNilaiPelamar">
                    @csrf
                    <input type="hidden" name="pelamar_id" id="nilaiPelamarId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-user"></i> Nama Pelamar</label>
                            <input type="text" id="nilaiNama" class="form-control-custom" readonly
                                style="background:#f9fafb;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-folder"></i> Masukkan ke Folder
                                <span class="required">*</span></label>
                            <select name="folder_id" id="nilaiFolderId" class="form-select-custom" required>
                                <option value="">-- Pilih Folder --</option>
                            </select>
                            <div class="form-hint">Pelamar akan dimasukkan ke folder ini untuk dinilai.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-star"></i> Penilaian (1-4 Bintang)
                                <span class="required">*</span></label>
                            <div class="d-flex gap-2 fs-2" style="cursor: pointer;" id="starContainerNilai">
                                <span class="star-nilai" data-value="1" style="color: #ddd;">☆</span>
                                <span class="star-nilai" data-value="2" style="color: #ddd;">☆</span>
                                <span class="star-nilai" data-value="3" style="color: #ddd;">☆</span>
                                <span class="star-nilai" data-value="4" style="color: #ddd;">☆</span>
                            </div>
                            <input type="hidden" name="rating" id="ratingInputNilai" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-comment"></i> Catatan</label>
                            <textarea name="catatan" class="form-control-custom" rows="3" placeholder="Catatan evaluasi pelamar..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-paperclip"></i> File Penilaian
                                (PDF/DOC)</label>
                            <input type="file" name="file_penilaian" class="form-control-custom"
                                accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i> Simpan Penilaian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCreateFolder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-folder-plus text-primary"></i> Buat Folder Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCreateFolder">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-folder"></i> Nama Folder <span class="required">*</span></label>
                            <input type="text" name="nama" class="form-control-custom" placeholder="Masukkan nama folder" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-sitemap"></i> Parent Folder (Opsional)</label>
                            <select name="parent_id" id="parentFolderSelectCreate" class="form-select-custom">
                                <option value="">-- Root Folder --</option>
                            </select>
                            <div class="form-hint">Kosongkan jika ingin membuat folder di root.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Buat Folder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditFolder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-primary"></i> Edit Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditFolder">
                    @csrf
                    <input type="hidden" name="folder_id" id="editFolderId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-folder"></i> Nama Folder <span class="required">*</span></label>
                            <input type="text" name="nama" id="editFolderName" class="form-control-custom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="fa-solid fa-sitemap"></i> Parent Folder (Opsional)</label>
                            <select name="parent_id" id="parentFolderSelectEdit" class="form-select-custom">
                                <option value="">-- Root Folder --</option>
                            </select>
                            <div class="form-hint">Kosongkan jika ingin memindahkan folder ke root.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDeleteFolder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="confirm-icon danger"><i class="fa-solid fa-trash"></i></div>
                    <div class="confirm-title">Hapus Folder?</div>
                    <div class="confirm-desc mb-3">
                        Folder <strong id="deleteFolderName">-</strong> akan dihapus permanen.
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-top-0 pt-0">
                    <input type="hidden" id="deleteFolderId">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusFolder">
                        <i class="fa-solid fa-trash me-1"></i> Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentFolderId = 'all';
        let currentFolderName = 'Semua Pelamar';
        let draggedPelamarId = null;

        $(document).ready(function() {
            loadFolderList();
            setupDragAndDrop();
        });

        function loadFolderList() {
            $.ajax({
                url: '{{ route('HR.hire.folder-list') }}',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        renderFolderSidebar(res.data, res.unassigned_count);
                        loadFolderOptionsForNilai(res.data); // 🆕 BARU
                    }
                },
                error: function() {
                    $('#hireFolderTree').html(
                        '<li class="text-center text-danger py-2" style="font-size:0.8rem;">Gagal memuat folder</li>'
                    );
                }
            });
        }

        function renderFolderSidebar(folders, unassignedCount) {
            $('#countUnassigned').text(unassignedCount);
            const tree = $('#hireFolderTree');
            tree.empty();

            if (folders.length === 0) {
                tree.html(`
                    <li class="text-center text-muted py-3" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-folder-open"></i><br>
                        Belum ada folder<br>
                        <small><a href="{{ route('HR.folders.index') }}" style="font-size:0.75rem;">Buat folder baru</a></small>
                    </li>
                `);
                return;
            }

            // Filter hanya root folder
            const rootFolders = folders.filter(f => 
                f.parent_id == null || f.parent_id == 0 || f.parent_id === ''
            );

            rootFolders.sort((a, b) => {
                if (a.is_pinned && !b.is_pinned) return -1;
                if (!a.is_pinned && b.is_pinned) return 1;
                return a.nama.localeCompare(b.nama);
            });

            renderFolderTree(rootFolders, folders, tree, 0);
        }

        function generateRandomPassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; 
            let password = '';
            for (let i = 0; i < 6; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('passwordOffer').value = password;
        }

        function renderFolderTree(currentFolders, allFolders, container, level) {
            currentFolders.forEach(folder => {
                const escapedName = folder.nama.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                
                // Cari children
                const children = allFolders.filter(f => f.parent_id == folder.id);
                children.sort((a, b) => a.nama.localeCompare(b.nama));
                
                const hasChildren = children.length > 0;
                const toggleHtml = hasChildren 
                    ? `<span class="folder-toggle expanded" onclick="event.stopPropagation(); toggleFolderChildren(this, ${folder.id})"><i class="fa-solid fa-chevron-right"></i></span>`
                    : `<span style="width:18px; display:inline-block;"></span>`;

                const li = $(`
                    <li class="folder-list-item">
                        <a class="folder-list-link" data-folder-id="${folder.id}"
                            onclick="filterByFolder(${folder.id}, '${escapedName}')">
                            ${toggleHtml}
                            <i class="fa-solid fa-folder folder-icon"></i>
                            ${folder.is_pinned ? '<i class="fa-solid fa-thumbtack pin-icon"></i>' : ''}
                            <span class="folder-label" title="${folder.nama}">${folder.nama}</span>
                            <span class="folder-count">${folder.pelamars_count || 0}</span>
                            <span class="folder-actions">
                                <button class="folder-action-btn" onclick="event.stopPropagation(); togglePinFolder(${folder.id})" title="Pin/Unpin">
                                    <i class="fa-solid fa-thumbtack"></i>
                                </button>
                                <button class="folder-action-btn" onclick="event.stopPropagation(); showEditFolderModal(${folder.id}, '${escapedName}', '${folder.parent_id || ''}')" title="Edit Folder">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="folder-action-btn danger" onclick="event.stopPropagation(); showDeleteFolderModal(${folder.id}, '${escapedName}')" title="Hapus Folder">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <button class="folder-action-btn archive" onclick="event.stopPropagation(); archiveFolderFromHire(${folder.id}, '${escapedName}')" title="Arsipkan Folder">
                                    <i class="fa-solid fa-box-archive"></i>
                                </button>
                            </span>
                        </a>
                        ${hasChildren ? `<ul class="folder-list-nested" id="folder-children-hire-${folder.id}"></ul>` : ''}
                    </li>
                `);

                container.append(li);

                // Render children secara recursive
                if (hasChildren) {
                    const nestedContainer = $(`#folder-children-hire-${folder.id}`);
                    renderFolderTree(children, allFolders, nestedContainer, level + 1);
                }
            });
        }

        function toggleFolderChildren(toggleEl, folderId) {
            const nested = $(`#folder-children-hire-${folderId}`);
            const toggle = $(toggleEl);
            nested.slideToggle(200);
            toggle.toggleClass('expanded');
        }
        
        function loadFolderOptionsForNilai() {
            $.ajax({
                url: '{{ route('HR.hire.folder-list') }}',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        const select = $('#nilaiFolderId');
                        select.find('option:not(:first)').remove();
                        
                        const rootFolders = (res.data || []).filter(f => 
                            f.parent_id == null || f.parent_id == 0 || f.parent_id === ''
                        );
                        
                        function addOptions(currentFolders, allFolders, level = 0) {
                            currentFolders.sort((a, b) => a.nama.localeCompare(b.nama));
                            currentFolders.forEach(f => {
                                const indent = '—'.repeat(level) + ' ';
                                select.append(`<option value="${f.id}">${indent}${f.nama}</option>`);
                                
                                // Cari children
                                const children = allFolders.filter(cf => cf.parent_id == f.id);
                                if (children.length > 0) {
                                    addOptions(children, allFolders, level + 1);
                                }
                            });
                        }
                        
                        addOptions(rootFolders, res.data);
                    }
                }
            });
        }

        function setupDragAndDrop() {
            let draggedPelamarId = null;

            $(document).on('dragstart', '.table-applicant tbody tr[draggable="true"]', function(e) {
                draggedPelamarId = $(this).data('id');
                e.originalEvent.dataTransfer.setData('pelamar_id', draggedPelamarId);
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                $(this).addClass('dragging');
            });

            $(document).on('dragend', '.table-applicant tbody tr[draggable="true"]', function() {
                $(this).removeClass('dragging');
                $('.folder-list-link').removeClass('drag-over drag-over-invalid');
                draggedPelamarId = null;
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

                const targetFolderId = $(this).data('folder-id');
                const pelamarId = e.originalEvent.dataTransfer.getData('pelamar_id');

                if (pelamarId && targetFolderId && targetFolderId !== 'all' && targetFolderId !== 'unassigned') {
                    movePelamarToFolder(pelamarId, targetFolderId);
                }
            });
        }

        function movePelamarToFolder(pelamarId, folderId) {
            $.ajax({
                url: '{{ route('HR.folders.pelamar.move') }}',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    pelamar_id: pelamarId,
                    folder_id: folderId
                },
                success: function(res) {
                    if (res.success) {
                        toast(res.message, 'success');
                        reloadPage();
                    } else {
                        toast(res.message || 'Gagal memindahkan', 'danger');
                    }
                },
                error: function() {
                    toast('Terjadi kesalahan saat memindahkan pelamar', 'danger');
                }
            });
        }

        function showToastGlobal(msg, type = 'success') {
            const icon = type === 'success' ? 'circle-check' : 'circle-xmark';
            const el = $(`<div class="toast-msg ${type}"><i class="fa-solid fa-${icon}"></i> ${msg}</div>`);
            $('#toastContainer').append(el);
            setTimeout(() => el.fadeOut(300, function() { $(this).remove(); }), 4000);
        }

        function loadParentFolderOptions(selectId, excludeFolderId, callback) {
            const select = $(`#${selectId}`);
            select.find('option:not(:first)').remove();
            
            $.ajax({
                url: '{{ route('HR.hire.folder-list') }}',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        const rootFolders = (res.data || []).filter(f => 
                            f.parent_id == null || f.parent_id == 0 || f.parent_id === ''
                        );
                        
                        function addOptions(currentFolders, allFolders, level = 0) {
                            currentFolders.sort((a, b) => a.nama.localeCompare(b.nama));
                            currentFolders.forEach(f => {
                                if (excludeFolderId && f.id == excludeFolderId) return;
                                const indent = '—'.repeat(level) + ' ';
                                select.append(`<option value="${f.id}">${indent}${f.nama}</option>`);
                                const children = allFolders.filter(cf => cf.parent_id == f.id);
                                if (children.length > 0) {
                                    addOptions(children, allFolders, level + 1);
                                }
                            });
                        }
                        addOptions(rootFolders, res.data);
                        if (typeof callback === 'function') callback();
                    }
                }
            });
        }

        function showEditFolderModal(folderId, folderName, currentParentId) {
            $('#editFolderId').val(folderId);
            $('#editFolderName').val(folderName);
            
            loadParentFolderOptions('parentFolderSelectEdit', folderId, function() {
                if (currentParentId) {
                    $('#parentFolderSelectEdit').val(currentParentId);
                } else {
                    $('#parentFolderSelectEdit').val('');
                }
            });
            
            new bootstrap.Modal(document.getElementById('modalEditFolder')).show();
        }

        function showDeleteFolderModal(folderId, folderName) {
            $('#deleteFolderId').val(folderId);
            $('#deleteFolderName').text(folderName);
            new bootstrap.Modal(document.getElementById('modalDeleteFolder')).show();
        }

        $('#formCreateFolder').on('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            $.ajax({
                url: '{{ route('HR.folders.store') }}',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        showToastGlobal(res.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalCreateFolder')).hide();
                        $('#formCreateFolder')[0].reset();
                        loadFolderList();
                    } else {
                        showToastGlobal(res.message || 'Gagal membuat folder', 'danger');
                    }
                },
                error: function() {
                    showToastGlobal('Terjadi kesalahan pada server', 'danger');
                }
            });
        });

        $('#formEditFolder').on('submit', function(e) {
            e.preventDefault();
            const folderId = $('#editFolderId').val();
            const newName = $('#editFolderName').val();
            const newParentId = $('#parentFolderSelectEdit').val() || '';
            
            $.ajax({
                url: `/HR-dashboard/folders/${folderId}/rename`,
                type: 'PUT',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    nama: newName
                },
                success: function() {
                    $.ajax({
                        url: `/HR-dashboard/folders/${folderId}/move`,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            parent_id: newParentId
                        },
                        success: function() {
                            showToastGlobal('Folder berhasil diperbarui', 'success');
                            bootstrap.Modal.getInstance(document.getElementById('modalEditFolder')).hide();
                            loadFolderList();
                        },
                        error: function() {
                            showToastGlobal('Nama berhasil diubah, tapi gagal memindahkan folder', 'danger');
                            bootstrap.Modal.getInstance(document.getElementById('modalEditFolder')).hide();
                            loadFolderList();
                        }
                    });
                },
                error: function() {
                    showToastGlobal('Gagal mengubah nama folder', 'danger');
                }
            });
        });

        $('#btnKonfirmasiHapusFolder').on('click', function() {
            const folderId = $('#deleteFolderId').val();
            $.ajax({
                url: `/HR-dashboard/folders/${folderId}`,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        showToastGlobal(res.message || 'Folder berhasil dihapus', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalDeleteFolder')).hide();
                        if (currentFolderId == folderId) {
                            resetFolderFilter();
                        }
                        loadFolderList();
                    } else {
                        showToastGlobal(res.message || 'Gagal menghapus folder', 'danger');
                    }
                },
                error: function() {
                    showToastGlobal('Terjadi kesalahan pada server', 'danger');
                }
            });
        });

        $('#modalCreateFolder').on('show.bs.modal', function () {
            $('#formCreateFolder')[0].reset();
            loadParentFolderOptions('parentFolderSelectCreate', null, null);
        });

        function filterByFolder(folderId, folderName) {
            currentFolderId = folderId;
            currentFolderName = folderName;
            $('.folder-list-link').removeClass('active');
            $(`.folder-list-link[data-folder-id="${folderId}"]`).addClass('active');

            if (folderId === 'all') {
                $('#activeFolderIndicator').hide();
            } else {
                $('#activeFolderName').text(folderName);
                $('#activeFolderIndicator').show();
                
                $.ajax({
                    url: '{{ route('HR.hire.folder-list') }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            expandParentFolders(folderId, res.data);
                        }
                    }
                });
            }

            $('#pelamarTableWrapper').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat data pelamar...</p>
                </div>
            `);
            $('#paginationWrapper').hide();

            $.ajax({
                url: `{{ url('HR-dashboard/hire/by-folder') }}/${folderId}`,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        renderFilteredTable(res.data, folderName);
                    }
                },
                error: function() {
                    $('#pelamarTableWrapper').html(`
                        <div class="text-center py-5 text-danger">
                            <i class="fa-solid fa-exclamation-circle fa-2x mb-2"></i>
                            <p>Gagal memuat data</p>
                        </div>
                    `);
                }
            });
        }

        function togglePinFolder(folderId) {
            $.ajax({
                url: `/HR-dashboard/folders/${folderId}/pin`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    loadFolderList();
                    if (res.success) {
                        toast(res.message, 'success');
                        loadFolderList();
                    }
                },
                error: function() {
                    toast('Gagal mengubah pin folder', 'danger');
                }
            });
        }

        function archiveFolderFromHire(folderId, folderName) {
            if (!confirm(`Yakin ingin mengarsipkan folder "${folderName}"?\n\nFolder beserta semua subfolder akan dipindahkan ke Arsip.`)) {
                return;
            }

            $.ajax({
                url: `/HR-dashboard/folders/${folderId}/archive`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    loadFolderList();
                    if (res.success) {
                        toast(res.message, 'success');
                        
                        if (currentFolderId == folderId) {
                            resetFolderFilter();
                        }
                        
                        loadFolderList();
                    } else {
                        toast(res.message || 'Gagal mengarsipkan folder', 'danger');
                    }
                },
                error: function(xhr) {
                    const res = xhr.responseJSON;
                    toast(res?.message || 'Terjadi kesalahan saat mengarsipkan', 'danger');
                }
            });
        }

        function expandParentFolders(folderId, allFolders) {
            if (!allFolders) return;
            
            const folder = allFolders.find(f => f.id == folderId);
            if (!folder || folder.parent_id == null || folder.parent_id == 0 || folder.parent_id === '') return;
            
            const nested = $(`#folder-children-hire-${folder.parent_id}`);
            if (nested.length) {
                nested.show();
                nested.prev('li').find('.folder-toggle').addClass('expanded');
            }
            
            expandParentFolders(folder.parent_id, allFolders);
        }

        function renderFilteredTable(data, folderName) {
            if (data.length === 0) {
                $('#pelamarTableWrapper').html(`
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                        Belum ada pelamar di "${folderName}"
                    </div>
                `);
                $('#tableInfo').html(`Menampilkan <span class="fw-semibold">0</span> pelamar`);
                $('#paginationWrapper').hide();
                return;
            }
            let rows = '';
            data.forEach(p => {
                const sumberIcon = getSumberIcon(p.sumber_lamaran);
                const ratingHtml = renderRatingCell(p.avg_rating, p.totalPenilai);
                const cvUrl = p.cv_path ? `/storage/${p.cv_path}` : '';
                rows += `
                    <tr draggable="true" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-email="${p.email}"
                        data-tahap="${p.tahap_rekrutmen}" data-jabatan="${p.jabatan}" data-divisi="${p.divisi}"
                        data-cv="${cvUrl}">
                        <td><input type="checkbox" class="form-check-input check-row" value="${p.id}"></td>
                        <td>
                            <div class="applicant-info">
                                <div class="applicant-avatar ${p.avClass}">${p.inisial}</div>
                                <div>
                                    <div class="applicant-name">${p.nama_lengkap}</div>
                                    <div class="applicant-email">${p.email}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="position-tag">${p.jabatan}</span>
                            <div class="position-dept">${p.divisi}</div>
                        </td>
                        <td>
                            <span class="source-badge ${sumberIcon.class}">
                                <i class="${sumberIcon.icon}"></i> ${p.sumber_lamaran || 'Lainnya'}
                            </span>
                        </td>
                        <td>${p.tanggal_melamar || '-'}</td>
                        <td>${ratingHtml}</td>
                        <td>
                            <span class="stage-badge stage-${p.tahap_rekrutmen}">${p.tahap_label}</span>
                        </td>
                        <td>
                            ${renderActionButtons(p)}
                        </td>
                    </tr>
                `;
            });
            $('#pelamarTableWrapper').html(`
                <table class="table table-applicant align-middle">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                            <th>Pelamar</th>
                            <th>Posisi Dilamar</th>
                            <th>Sumber</th>
                            <th>Tanggal</th>
                            <th>Penilaian</th>
                            <th>Tahapan</th>
                            <th class="text-center" style="width:140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            `);
            $('#tableInfo').html(`Menampilkan <span class="fw-semibold">${data.length}</span> pelamar di "${folderName}"`);
            $('#paginationWrapper').hide();
        }

        function renderDropdownActions(p) {
            let items = '';
            const tahap = p.tahap_rekrutmen;

            if (['applied', 'screening'].includes(tahap)) {
                items +=
                    `<li><a class="dropdown-item text-info btn-lanjut-tahap" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-tahap-saat-ini="${p.tahap_label}" data-tahap-berikutnya="${p.tahap_berikutnya || ''}"><i class="fa-solid fa-arrow-right"></i> Lanjut Tahapan</a></li>`;
                items +=
                    `<li><a class="dropdown-item text-danger btn-tolak" data-id="${p.id}" data-nama="${p.nama_lengkap}"><i class="fa-solid fa-xmark"></i> Tolak Lamaran</a></li>`;
            } else if (tahap === 'interview') {
                items +=
                    `<li><a class="dropdown-item text-warning btn-jadwal-interview" data-id="${p.id}" data-nama="${p.nama_lengkap}"><i class="fa-regular fa-calendar"></i> Jadwalkan Interview</a></li>`;
                items +=
                    `<li><a class="dropdown-item text-success btn-lanjut-tahap" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-tahap-saat-ini="${p.tahap_label}" data-tahap-berikutnya="offer"><i class="fa-solid fa-arrow-right"></i> Lanjutkan</a></li>`;
                items +=
                    `<li><a class="dropdown-item text-danger btn-tolak" data-id="${p.id}" data-nama="${p.nama_lengkap}"><i class="fa-solid fa-xmark"></i> Tolak Lamaran</a></li>`;
            } else if (tahap === 'offer') {
                items +=
                    `<li><a class="dropdown-item text-info btn-kirim-offer" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-jabatan="${p.jabatan}"><i class="fa-solid fa-file-invoice"></i> Kirim Offer</a></li>`;
                items +=
                    `<li><a class="dropdown-item text-success btn-lanjut-tahap" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-tahap-saat-ini="${p.tahap_label}" data-tahap-berikutnya="hired"><i class="fa-solid fa-check"></i> Diterima (Hired)</a></li>`;
                items +=
                    `<li><a class="dropdown-item text-danger btn-tolak" data-id="${p.id}" data-nama="${p.nama_lengkap}"><i class="fa-solid fa-xmark"></i> Tolak Lamaran</a></li>`;
            } else if (tahap === 'hired') {
                items +=
                    `<li><a class="dropdown-item text-success btn-onboarding" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-divisi="${p.divisi}" data-jabatan="${p.jabatan}" data-mulai="${p.tanggal_mulai_kerja || ''}"><i class="fa-solid fa-user-plus"></i> Proses Onboarding</a></li>`;
            } else if (tahap === 'rejected') {
                items +=
                    `<li><a class="dropdown-item btn-kirim-email" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-email="${p.email}"><i class="fa-regular fa-envelope"></i> Kirim Email</a></li>`;
                items +=
                    `<li><a class="dropdown-item text-danger btn-hapus" data-id="${p.id}" data-nama="${p.nama_lengkap}"><i class="fa-solid fa-trash"></i> Hapus Data</a></li>`;
            }

            return items;
        }

        function renderRatingCell(avgRating, totalPenilai) {
            if (!avgRating || totalPenilai === 0) {
                return `<span class="table-rating-empty">Belum dinilai</span>`;
            }
            const fullStars = Math.floor(avgRating);
            const emptyStars = 4 - fullStars;
            let starsHtml = '';
            for (let i = 0; i < fullStars; i++) starsHtml += '<i class="fa-solid fa-star"></i>';
            for (let i = 0; i < emptyStars; i++) starsHtml += '<i class="fa-solid fa-star star-empty"></i>';
            return `
                <div class="table-rating">${starsHtml}</div>
                <div class="table-rating-info">${avgRating} · ${totalPenilai} interviewer</div>
            `;
        }

        function getSumberIcon(sumber) {
            const map = {
                'linkedin': {
                    icon: 'fa-brands fa-linkedin-in',
                    class: 'linkedin'
                },
                'jobstreet': {
                    icon: 'fa-solid fa-briefcase',
                    class: 'jobstreet'
                },
                'website perusahaan': {
                    icon: 'fa-solid fa-globe',
                    class: 'website'
                },
                'referral karyawan': {
                    icon: 'fa-solid fa-user-group',
                    class: 'referral'
                },
                'glints': {
                    icon: 'fa-solid fa-graduation-cap',
                    class: 'glints'
                },
                'kalibrr': {
                    icon: 'fa-solid fa-star',
                    class: 'kalibrr'
                },
            };
            return map[(sumber || '').toLowerCase()] || {
                icon: 'fa-solid fa-circle-question',
                class: 'other'
            };
        }

        function renderActionButtons(p) {
            const cvUrl = p.cv_path ? `/storage/${p.cv_path}` : '';

            let html = `
                <div class="action-cell">
                    <button class="btn-quick-action btn-lihat-profil" title="Lihat Profil" data-id="${p.id}">
                        <i class="fa-solid fa-eye text-primary"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn-action-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu">
            `;

            // Lihat CV
            if (p.cv_path) {
                html += `
                    <li><a class="dropdown-item btn-lihat-cv" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-cv="${cvUrl}">
                        <i class="fa-solid fa-file-pdf text-danger"></i> Lihat CV
                    </a></li>
                `;
            }

            // Beri Penilaian
            html += `
                <li><a class="dropdown-item btn-nilai-pelamar" data-id="${p.id}" data-nama="${p.nama_lengkap}">
                    <i class="fa-solid fa-star text-warning"></i> Beri Penilaian
                </a></li>
                <li><hr class="dropdown-divider"></li>
            `;

            // Actions berdasarkan tahap
            if (['applied', 'screening'].includes(p.tahap_rekrutmen)) {
                html += `
                    <li><a class="dropdown-item dropdown-item-info btn-lanjut-tahap" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-tahap-saat-ini="${p.tahap_label}" data-tahap-berikutnya="${p.tahap_berikutnya || ''}">
                        <i class="fa-solid fa-arrow-right"></i> Lanjut Tahapan
                    </a></li>
                    <li><a class="dropdown-item dropdown-item-danger btn-tolak" data-id="${p.id}" data-nama="${p.nama_lengkap}">
                        <i class="fa-solid fa-xmark"></i> Tolak Lamaran
                    </a></li>
                `;

            } else if (p.tahap_rekrutmen === 'interview') {
                html += `<li><a class="dropdown-item dropdown-item-warning btn-jadwal-interview" 
                                data-id="${p.id}" data-nama="${p.nama_lengkap}">
                            <i class="fa-regular fa-calendar"></i> Jadwalkan Interview
                        </a></li>`;

                html += `<li><a class="dropdown-item dropdown-item-purple btn-kirim-offer" 
                                data-id="${p.id}" data-nama="${p.nama_lengkap}" data-jabatan="${p.jabatan}">
                            <i class="fa-solid fa-file-invoice"></i> Kirim Offer
                        </a></li>`;

                html += `<li><a class="dropdown-item dropdown-item-danger btn-tolak" 
                                data-id="${p.id}" data-nama="${p.nama_lengkap}">
                            <i class="fa-solid fa-xmark"></i> Tolak Lamaran
                        </a></li>`;

            } else if (p.tahap_rekrutmen === 'offer') {
                html += `
                    <li><a class="dropdown-item dropdown-item-purple btn-kirim-offer" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-jabatan="${p.jabatan}">
                        <i class="fa-solid fa-file-invoice"></i> Kirim Offer
                    </a></li>
                    <li><a class="dropdown-item dropdown-item-success btn-lanjut-tahap" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-tahap-saat-ini="${p.tahap_label}" data-tahap-berikutnya="hired">
                        <i class="fa-solid fa-check"></i> Diterima (Hired)
                    </a></li>
                    <li><a class="dropdown-item dropdown-item-danger btn-tolak" data-id="${p.id}" data-nama="${p.nama_lengkap}">
                        <i class="fa-solid fa-xmark"></i> Tolak Lamaran
                    </a></li>
                `;
            } else if (p.tahap_rekrutmen === 'hired') {
                html += `
                    <li><a class="dropdown-item dropdown-item-success btn-onboarding" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-divisi="${p.divisi}" data-jabatan="${p.jabatan}" data-mulai="">
                        <i class="fa-solid fa-user-plus"></i> Proses Onboarding
                    </a></li>
                `;
            } else if (p.tahap_rekrutmen === 'rejected') {
                html += `
                    <li><a class="dropdown-item btn-kirim-email" data-id="${p.id}" data-nama="${p.nama_lengkap}" data-email="${p.email}">
                        <i class="fa-regular fa-envelope"></i> Kirim Email
                    </a></li>
                    <li><a class="dropdown-item dropdown-item-danger btn-hapus" data-id="${p.id}" data-nama="${p.nama_lengkap}">
                        <i class="fa-solid fa-trash"></i> Hapus Data
                    </a></li>
                `;
            }

            html += `
                        </ul>
                    </div>
                </div>
            `;

            return html;
        }

        function resetFolderFilter() {
            filterByFolder('all', 'Semua Pelamar');
            setTimeout(() => location.reload(), 300);
        }

        (function($) {
            const BASE_URL = '{{ url('HR-dashboard/hire') }}';
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function toast(msg, type = 'success') {
                const icon = type === 'success' ? 'circle-check' : 'circle-xmark';
                const el = $(`<div class="toast-msg ${type}"><i class="fa-solid fa-${icon}"></i> ${msg}</div>`);
                $('#toastContainer').append(el);
                setTimeout(() => el.fadeOut(300, function() {
                    $(this).remove();
                }), 4000);
            }

            function closeModal(id) {
                const modalEl = document.getElementById(id);
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            }

            function reloadPage() {
                setTimeout(() => location.reload(), 800);
            }

            $('#checkAll').on('change', function() {
                $('.check-row').prop('checked', this.checked);
            });

            $('#metodeInterview').on('change', function() {
                if (this.value === 'online') {
                    $('#linkMeetingWrapper').show();
                    $('#alamatWrapper').hide();
                } else if (this.value === 'offline') {
                    $('#linkMeetingWrapper').hide();
                    $('#alamatWrapper').show();
                } else {
                    $('#linkMeetingWrapper').show();
                    $('#alamatWrapper').hide();
                }
            });

            $('#alasanTolak').on('change', function() {
                $('#alasanLainWrapper').toggle(this.value === 'lainnya');
            });

            $(document).on('click', '.btn-lihat-cv', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const nama = $(this).data('nama');
                const cvUrl = $(this).data('cv');

                if (!cvUrl) {
                    toast('CV tidak tersedia', 'danger');
                    return;
                }

                // Buat modal CV viewer dinamis
                let modalHtml = `
                    <div class="modal fade" id="modalCVViewer" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header" style="background: linear-gradient(135deg, #1f2937, #374151); color: white;">
                                    <h5 class="modal-title">
                                        <i class="fa-solid fa-file-pdf me-2"></i>
                                        <span>CV - ${nama}</span>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div id="cvViewerContainer" style="width: 100%; height: 75vh; background: #f3f4f6;">
                                        <div class="text-center py-5">
                                            <i class="fa-solid fa-spinner fa-spin fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Memuat CV...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Hapus modal lama jika ada
                $('#modalCVViewer').remove();
                $('body').append(modalHtml);

                const modal = new bootstrap.Modal(document.getElementById('modalCVViewer'));
                modal.show();

                // Load CV content
                setTimeout(() => {
                    if (cvUrl.toLowerCase().endsWith('.pdf')) {
                        $('#cvViewerContainer').html(
                            `<iframe src="${cvUrl}" style="width:100%;height:75vh;border:none;"></iframe>`
                            );
                    } else {
                        $('#cvViewerContainer').html(`
                            <div class="text-center py-5">
                                <i class="fa-solid fa-file fa-3x text-muted mb-3"></i>
                                <h5>Preview tidak tersedia untuk format ini</h5>
                                <p class="text-muted">Silakan download untuk melihat dokumen</p>
                                <a href="${cvUrl}" class="btn btn-primary mt-3" target="_blank">
                                    <i class="fa-solid fa-download"></i> Download Dokumen
                                </a>
                            </div>
                        `);
                    }
                }, 300);

                // Cleanup saat modal ditutup
                $('#modalCVViewer').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            });

            $(document).on('click', '.btn-nilai-pelamar', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const id = $(this).data('id');
                const nama = $(this).data('nama');

                let modalHtml = `
                    <div class="modal fade" id="modalNilaiPelamar" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fa-solid fa-star text-warning"></i> Beri Penilaian</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form id="formNilaiPelamar">
                                    @csrf
                                    <input type="hidden" name="pelamar_id" id="nilaiPelamarId" value="${id}">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label-custom"><i class="fa-solid fa-user"></i> Nama Pelamar</label>
                                            <input type="text" id="nilaiNama" class="form-control-custom" value="${nama}" readonly style="background:#f9fafb;">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label-custom"><i class="fa-solid fa-folder"></i> Masukkan ke Folder <span class="required">*</span></label>
                                            <select name="folder_id" id="nilaiFolderId" class="form-select-custom" required>
                                                <option value="">-- Pilih Folder --</option>
                                            </select>
                                            <div class="form-hint">Pelamar akan dimasukkan ke folder ini untuk dinilai.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label-custom"><i class="fa-solid fa-star"></i> Penilaian (1-4 Bintang) <span class="required">*</span></label>
                                            <div class="d-flex gap-2 fs-2" style="cursor: pointer;" id="starContainerNilai">
                                                <span class="star-nilai" data-value="1" style="color: #ddd; font-size: 2rem;">☆</span>
                                                <span class="star-nilai" data-value="2" style="color: #ddd; font-size: 2rem;">☆</span>
                                                <span class="star-nilai" data-value="3" style="color: #ddd; font-size: 2rem;">☆</span>
                                                <span class="star-nilai" data-value="4" style="color: #ddd; font-size: 2rem;">☆</span>
                                            </div>
                                            <input type="hidden" name="rating" id="ratingInputNilai" value="0">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label-custom"><i class="fa-solid fa-comment"></i> Catatan</label>
                                            <textarea name="catatan" class="form-control-custom" rows="3" placeholder="Catatan evaluasi pelamar..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label-custom"><i class="fa-solid fa-paperclip"></i> File Penilaian (PDF/DOC)</label>
                                            <input type="file" name="file_penilaian" class="form-control-custom" accept=".pdf,.doc,.docx">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa-solid fa-save me-1"></i> Simpan Penilaian
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;

                // Hapus modal lama jika ada
                $('#modalNilaiPelamar').remove();
                $('body').append(modalHtml);

                const modal = new bootstrap.Modal(document.getElementById('modalNilaiPelamar'));
                modal.show();

                // Load folder list
                loadFolderOptionsForNilai();

                // Star rating handlers
                $(document).on('click', '.star-nilai', function() {
                    const value = $(this).data('value');
                    $('#ratingInputNilai').val(value);
                    $('#starContainerNilai .star-nilai').each(function() {
                        if ($(this).data('value') <= value) {
                            $(this).css('color', '#ffc107').text('★');
                        } else {
                            $(this).css('color', '#ddd').text('☆');
                        }
                    });
                });

                $(document).on('mouseover', '.star-nilai', function() {
                    const value = $(this).data('value');
                    $('#starContainerNilai .star-nilai').each(function() {
                        $(this).css('color', $(this).data('value') <= value ? '#ffc107' :
                            '#ddd');
                    });
                });

                $(document).on('mouseleave', '#starContainerNilai', function() {
                    const currentValue = parseInt($('#ratingInputNilai').val());
                    $('#starContainerNilai .star-nilai').each(function() {
                        $(this).css('color', $(this).data('value') <= currentValue ? '#ffc107' :
                            '#ddd');
                    });
                });

                // Form submit
                $('#formNilaiPelamar').on('submit', function(e) {
                    e.preventDefault();
                    if ($('#ratingInputNilai').val() == 0) {
                        toast('Pilih rating terlebih dahulu', 'danger');
                        return;
                    }
                    if (!$('#nilaiFolderId').val()) {
                        toast('Pilih folder terlebih dahulu', 'danger');
                        return;
                    }

                    const fd = new FormData(this);
                    $.ajax({
                        url: `${BASE_URL}/penilaian`,
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                toast(res.message, 'success');
                                modal.hide();
                                reloadPage();
                            } else {
                                toast(res.message || 'Gagal menyimpan penilaian', 'danger');
                            }
                        },
                        error: function() {
                            toast('Terjadi kesalahan pada server', 'danger');
                        }
                    });
                });

                // Cleanup saat modal ditutup
                $('#modalNilaiPelamar').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            });

            $(document).on('click', '.star-nilai', function() {
                const value = $(this).data('value');
                $('#ratingInputNilai').val(value);
                $('#starContainerNilai .star-nilai').each(function() {
                    if ($(this).data('value') <= value) {
                        $(this).css('color', '#ffc107').text('★');
                    } else {
                        $(this).css('color', '#ddd').text('☆');
                    }
                });
            });

            $(document).on('mouseover', '.star-nilai', function() {
                const value = $(this).data('value');
                $('#starContainerNilai .star-nilai').each(function() {
                    $(this).css('color', $(this).data('value') <= value ? '#ffc107' : '#ddd');
                });
            });

            $(document).on('mouseleave', '#starContainerNilai', function() {
                const currentValue = parseInt($('#ratingInputNilai').val());
                $('#starContainerNilai .star-nilai').each(function() {
                    $(this).css('color', $(this).data('value') <= currentValue ? '#ffc107' : '#ddd');
                });
            });

            $('#formNilaiPelamar').on('submit', function(e) {
                e.preventDefault();
                if ($('#ratingInputNilai').val() == 0) {
                    toast('Pilih rating terlebih dahulu', 'danger');
                    return;
                }
                if (!$('#nilaiFolderId').val()) {
                    toast('Pilih folder terlebih dahulu', 'danger');
                    return;
                }

                const fd = new FormData(this);
                $.ajax({
                    url: '{{ route('HR.folders.pelamar.penilaian') }}',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toast(res.message, 'success');
                            closeModal('modalNilaiPelamar');
                            reloadPage();
                        } else {
                            toast(res.message || 'Gagal menyimpan penilaian', 'danger');
                        }
                    },
                    error: function() {
                        toast('Terjadi kesalahan pada server', 'danger');
                    }
                });
            });

            $(document).on('click', '.btn-lihat-profil', function() {
                const id = $(this).data('id');
                const $modal = $('#modalLihatProfil');
                const modal = new bootstrap.Modal($modal[0]);

                $('#profilContent').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                modal.show();

                $.ajax({
                    url: `${BASE_URL}/show/${id}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(json) {
                        const d = json.data;
                        const p = d.pelamar;
                        const keahlianHtml = (p.keahlian && p.keahlian.length) ?
                            p.keahlian.map(k => `<span class="skill-tag">${k}</span>`).join('') :
                            '<span class="text-muted">-</span>';

                        let riwayatHtml = '';
                        if (d.riwayat && d.riwayat.length) {
                            d.riwayat.forEach((r, i) => {
                                const isLast = i === d.riwayat.length - 1;
                                const dotClass = isLast ? 'active' : 'done';
                                const icon = isLast ? 'fa-solid fa-user-tie' :
                                    'fa-solid fa-check';
                                const dateStr = new Date(r.created_at).toLocaleDateString(
                                    'id-ID', {
                                        day: 'numeric',
                                        month: 'short',
                                        year: 'numeric'
                                    });
                                let ratingHtml = '';
                                if (r.rating && r.rating > 0) {
                                    ratingHtml = '<div class="timeline-rating">';
                                    for (let s = 1; s <= 5; s++) {
                                        const starClass = s <= r.rating ? 'star' :
                                            'star empty';
                                        ratingHtml +=
                                            `<i class="fa-solid fa-star ${starClass}"></i>`;
                                    }
                                    ratingHtml += '</div>';
                                }
                                let feedbackHtml = '';
                                if (r.keterangan && r.keterangan.trim() !== '') {
                                    feedbackHtml =
                                        `<div class="timeline-feedback">${r.keterangan}</div>`;
                                }
                                riwayatHtml += `
                                    <div class="timeline-item">
                                        <div class="timeline-dot ${dotClass}"><i class="${icon}"></i></div>
                                        <div class="timeline-content">
                                            <div class="timeline-title">${r.aksi}</div>
                                            <div class="timeline-desc">${dateStr} · oleh ${r.oleh ?? '-'}</div>
                                            ${ratingHtml}
                                            ${feedbackHtml}
                                        </div>
                                    </div>`;
                            });
                        } else {
                            riwayatHtml =
                                '<p class="text-muted mb-0" style="font-size:0.85rem;">Belum ada riwayat.</p>';
                        }

                        let penilaianSectionHtml = '';
                        if (d.penilaians && d.penilaians.length > 0) {
                            let summaryHtml = '';
                            if (d.avg_rating) {
                                const fullStars = Math.floor(d.avg_rating);
                                const emptyStars = 4 - fullStars;
                                let starsHtml = '';
                                for (let i = 0; i < fullStars; i++) starsHtml += '★';
                                for (let i = 0; i < emptyStars; i++) starsHtml += '☆';
                                summaryHtml = `
                                    <div class="penilaian-summary">
                                        <div class="penilaian-summary-item">
                                            <div class="penilaian-summary-value">${d.avg_rating}</div>
                                            <div class="penilaian-summary-label">Rata-rata Nilai</div>
                                        </div>
                                        <div class="penilaian-summary-item">
                                            <div class="penilaian-summary-stars">${starsHtml}</div>
                                            <div class="penilaian-summary-label">dari 4 bintang</div>
                                        </div>
                                        <div class="penilaian-summary-item">
                                            <div class="penilaian-summary-value">${d.total_penilai}</div>
                                            <div class="penilaian-summary-label">Interviewer</div>
                                        </div>
                                    </div>
                                `;
                            }

                            let listHtml = d.penilaians.map(pf => {
                                const ratingStars = pf.rating ?
                                    '★'.repeat(pf.rating) + '<span class="star-empty">' +
                                    '☆'.repeat(4 - pf.rating) + '</span>' :
                                    '<em class="text-muted">Belum dinilai</em>';
                                const interviewerInitial = (pf.interviewer_nama || '?')
                                    .split(' ').slice(0, 2).map(n => n[0] || '').join('')
                                    .toUpperCase();
                                let fileLink = '';
                                if (pf.file_penilaian) {
                                    fileLink = `
                                        <a href="/storage/${pf.file_penilaian}" target="_blank" class="penilaian-file-link">
                                            <i class="fa-solid fa-file-pdf text-danger"></i> Lihat File Penilaian
                                        </a>
                                    `;
                                }
                                let catatanBox = '';
                                if (pf.catatan && pf.catatan.trim() !== '') {
                                    catatanBox = `
                                        <div class="penilaian-catatan-box">
                                            <strong><i class="fa-solid fa-comment"></i> Catatan:</strong>
                                            ${pf.catatan}
                                        </div>
                                    `;
                                }
                                return `
                                    <div class="penilaian-card">
                                        <div class="penilaian-card-header">
                                            <div class="penilaian-interviewer-info">
                                                <div class="penilaian-interviewer-avatar">${interviewerInitial}</div>
                                                <div>
                                                    <div class="penilaian-interviewer-name">
                                                        ${pf.interviewer_nama}
                                                        <span class="penilaian-folder-badge">
                                                            <i class="fa-solid fa-folder"></i> ${pf.folder_nama}
                                                        </span>
                                                    </div>
                                                    <div class="penilaian-interviewer-role">${pf.interviewer_jabatan}</div>
                                                </div>
                                            </div>
                                            <div class="penilaian-date">
                                                <i class="fa-regular fa-clock"></i> ${pf.tanggal_dinilai || '-'}
                                            </div>
                                        </div>
                                        <div class="penilaian-rating-display">${ratingStars}</div>
                                        ${catatanBox}
                                        ${fileLink}
                                    </div>
                                `;
                            }).join('');

                            penilaianSectionHtml = `
                                <div class="penilaian-section">
                                    <div class="profile-section-title">
                                        <i class="fa-solid fa-star"></i> Hasil Penilaian Interviewer
                                    </div>
                                    ${summaryHtml}
                                    ${listHtml}
                                </div>
                            `;
                        } else {
                            penilaianSectionHtml = `
                                <div class="penilaian-section">
                                    <div class="profile-section-title">
                                        <i class="fa-solid fa-star"></i> Hasil Penilaian Interviewer
                                    </div>
                                    <div class="penilaian-empty">
                                        <i class="fa-regular fa-star"></i>
                                        <p>Belum ada penilaian dari interviewer</p>
                                    </div>
                                </div>
                            `;
                        }

                        const cvBtn = p.cv_path ?
                            `<a href="/storage/${p.cv_path}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-file-pdf me-1 text-danger"></i> Lihat CV</a>` :
                            '<span class="text-muted" style="font-size:0.85rem;">CV belum diupload</span>';

                        const usiaText = p.tanggal_lahir && d.usia ? ` (${d.usia} tahun)` : '';
                        const pendidikanText = (p.pendidikan_terakhir ?? '-') + (p.jurusan ? ' - ' +
                            p.jurusan : '');
                        const pengalamanText = p.pengalaman_tahun ? p.pengalaman_tahun + ' tahun' :
                            '-';

                        $('#profilContent').html(`
                            <div class="profile-header">
                                <div class="profile-avatar-lg">${d.inisial}</div>
                                <div class="profile-name">${p.nama_lengkap}</div>
                                <div class="profile-position">${p.jabatan ?? '-'} · ${p.divisi ?? '-'}</div>
                                <div class="profile-contact">
                                    <span><i class="fa-solid fa-envelope"></i> ${p.email}</span>
                                    <span><i class="fa-solid fa-phone"></i> ${p.no_telepon ?? '-'}</span>
                                    <span><i class="fa-solid fa-location-dot"></i> ${p.domisili ?? '-'}</span>
                                </div>
                                <button type="button" class="btn-close btn-close-white position-absolute" style="top:1rem;right:1rem;" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="profile-body-scroll">
                                <div class="profile-section">
                                    <div class="profile-section-title"><i class="fa-solid fa-user"></i> Informasi Pribadi</div>
                                    <div class="profile-grid">
                                        <div><div class="profile-item-label">Tanggal Lahir</div><div class="profile-item-value">${p.tanggal_lahir ? p.tanggal_lahir + usiaText : '-'}</div></div>
                                        <div><div class="profile-item-label">Pendidikan</div><div class="profile-item-value">${pendidikanText}</div></div>
                                        <div><div class="profile-item-label">Pengalaman</div><div class="profile-item-value">${pengalamanText}</div></div>
                                        <div><div class="profile-item-label">Sumber Lamaran</div><div class="profile-item-value">${p.sumber_lamaran ?? '-'}</div></div>
                                        <div><div class="profile-item-label">Gaji Diharapkan</div><div class="profile-item-value">${d.pelamar.gaji_diharapkan_format ?? '-'}</div></div>
                                        <div><div class="profile-item-label">Tahapan</div><div class="profile-item-value">${d.tahap_label}</div></div>
                                    </div>
                                </div>
                                <div class="profile-section">
                                    <div class="profile-section-title"><i class="fa-solid fa-code"></i> Keahlian</div>
                                    <div>${keahlianHtml}</div>
                                </div>
                                <div class="profile-section">
                                    <div class="profile-section-title"><i class="fa-solid fa-route"></i> Riwayat Tahapan Rekrutmen</div>
                                    ${riwayatHtml}
                                </div>
                                <div class="profile-section">
                                    ${penilaianSectionHtml}
                                </div>
                                <div class="profile-section">
                                    <div class="profile-section-title"><i class="fa-solid fa-paperclip"></i> Dokumen</div>
                                    <div class="d-flex flex-wrap gap-2">${cvBtn}</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        `);
                    },
                    error: function() {
                        $('#profilContent').html(
                            '<div class="text-center py-5 text-danger">Gagal memuat data profil.</div>'
                        );
                    }
                });
            });

            $(document).on('click', '.btn-jadwal-interview', function() {
                $('#interviewPelamarId').val($(this).data('id'));
                $('#interviewNama').val($(this).data('nama'));
                new bootstrap.Modal(document.getElementById('modalJadwalInterview')).show();
            });

            $('#formJadwalInterview').on('submit', function(e) {
                e.preventDefault();
                const id = $('#interviewPelamarId').val();
                const fd = new FormData(this);
                $.ajax({
                    url: `${BASE_URL}/${id}/jadwal-interview`,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toast(res.message);
                            closeModal('modalJadwalInterview');
                            reloadPage();
                        } else {
                            toast(res.message, 'danger');
                        }
                    },
                    error: function() {
                        toast('Terjadi kesalahan pada server.', 'danger');
                    }
                });
            });

            $(document).on('click', '.btn-kirim-offer', function() {
                $('#offerPelamarId').val($(this).data('id'));
                $('#offerNama').val($(this).data('nama'));
                $('#offerJabatan').val($(this).data('jabatan'));
                new bootstrap.Modal(document.getElementById('modalKirimOffer')).show();
            });

            $('#formKirimOffer').on('submit', function(e) {
                e.preventDefault();
                const id = $('#offerPelamarId').val();
                const fd = new FormData(this);
                $.ajax({
                    url: `${BASE_URL}/${id}/kirim-offer`,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toast(res.message);
                            closeModal('modalKirimOffer');
                            reloadPage();
                        } else {
                            toast(res.message, 'danger');
                        }
                    },
                    error: function() {
                        toast('Terjadi kesalahan pada server.', 'danger');
                    }
                });
            });

            $(document).on('click', '.btn-onboarding', function() {
                $('#onboardPelamarId').val($(this).data('id'));
                $('#onboardDivisi').val($(this).data('divisi'));
                $('#onboardJabatan').val($(this).data('jabatan'));
                if ($(this).data('mulai')) $('#onboardTanggalMulai').val($(this).data('mulai'));
                $('#onboardNik').val('EMP-' + new Date().getFullYear() + '-' + String($(this).data('id'))
                    .padStart(3, '0'));
                new bootstrap.Modal(document.getElementById('modalOnboarding')).show();
            });

            $('#formOnboarding').on('submit', function(e) {
                e.preventDefault();
                const id = $('#onboardPelamarId').val();
                const fd = new FormData(this);
                $.ajax({
                    url: `${BASE_URL}/${id}/onboarding`,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toast(res.message);
                            closeModal('modalOnboarding');
                            reloadPage();
                        } else {
                            toast(res.message, 'danger');
                        }
                    },
                    error: function() {
                        toast('Terjadi kesalahan pada server.', 'danger');
                    }
                });
            });

            $(document).on('click', '.btn-lanjut-tahap', function() {
                $('#lanjutPelamarId').val($(this).data('id'));
                $('#lanjutNama').val($(this).data('nama'));
                $('#lanjutTahapSaatIni').val($(this).data('tahap-saat-ini'));
                if ($(this).data('tahap-berikutnya')) $('#lanjutTahapKe').val($(this).data('tahap-berikutnya'));
                new bootstrap.Modal(document.getElementById('modalLanjutTahap')).show();
            });

            $('#formLanjutTahap').on('submit', function(e) {
                e.preventDefault();
                const id = $('#lanjutPelamarId').val();
                const fd = new FormData(this);
                $.ajax({
                    url: `${BASE_URL}/${id}/lanjut-tahap`,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toast(res.message);
                            closeModal('modalLanjutTahap');
                            reloadPage();
                        } else {
                            toast(res.message, 'danger');
                        }
                    },
                    error: function() {
                        toast('Terjadi kesalahan pada server.', 'danger');
                    }
                });
            });

            $(document).on('click', '.btn-kirim-email', function() {
                $('#emailPelamarId').val($(this).data('id'));
                $('#emailNama').val($(this).data('nama'));
                $('#emailAlamat').val($(this).data('email'));
                new bootstrap.Modal(document.getElementById('modalKirimEmail')).show();
            });

            $('#formKirimEmail').on('submit', function(e) {
                e.preventDefault();
                const id = $('#emailPelamarId').val();
                const fd = new FormData(this);
                $.ajax({
                    url: `${BASE_URL}/${id}/kirim-email`,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toast(res.message);
                            closeModal('modalKirimEmail');
                        } else {
                            toast(res.message, 'danger');
                        }
                    },
                    error: function() {
                        toast('Terjadi kesalahan pada server.', 'danger');
                    }
                });
            });

            $(document).on('click', '.btn-tolak', function() {
                $('#tolakPelamarId').val($(this).data('id'));
                $('#tolakNama').val($(this).data('nama'));
                new bootstrap.Modal(document.getElementById('modalTolak')).show();
            });

            $('#formTolak').on('submit', function(e) {
                e.preventDefault();
                const id = $('#tolakPelamarId').val();
                const fd = new FormData(this);
                $.ajax({
                    url: `${BASE_URL}/${id}/tolak`,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toast(res.message);
                            closeModal('modalTolak');
                            reloadPage();
                        } else {
                            toast(res.message, 'danger');
                        }
                    },
                    error: function() {
                        toast('Terjadi kesalahan pada server.', 'danger');
                    }
                });
            });

            $(document).on('click', '.btn-hapus', function() {
                $('#hapusPelamarId').val($(this).data('id'));
                $('#hapusNama').text($(this).data('nama'));
                new bootstrap.Modal(document.getElementById('modalHapus')).show();
            });

            $('#btnKonfirmasiHapus').on('click', function() {
                const id = $('#hapusPelamarId').val();
                $.ajax({
                    url: `${BASE_URL}/delete/${id}`,
                    type: 'POST',
                    data: {
                        _method: 'DELETE'
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            toast(res.message);
                            closeModal('modalHapus');
                            reloadPage();
                        } else {
                            toast(res.message, 'danger');
                        }
                    },
                    error: function() {
                        toast('Terjadi kesalahan pada server.', 'danger');
                    }
                });
            });

            $('#formTambahPelamar').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#btnSimpanPelamar');
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                const fd = new FormData(this);
                $.ajax({
                    url: `${BASE_URL}/store`,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $btn.prop('disabled', false).html(originalHtml);
                        if (res.success) {
                            toast(res.message);
                            closeModal('modalTambahPelamar');
                            $('#formTambahPelamar')[0].reset();
                            reloadPage();
                        } else {
                            toast(res.message ?? 'Terjadi kesalahan.', 'danger');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                        toast('Terjadi kesalahan pada server.', 'danger');
                    }
                });
            });

        })(jQuery);
    </script>
@endsection
