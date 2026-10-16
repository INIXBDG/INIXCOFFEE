<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Laravel Log Viewer</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables -->
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <style>
    /* ─── RESET & BASE ─── */
    *, *::before, *::after { box-sizing: border-box; }

    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      font-size: 14px;
      background-color: #f0f2f5;
      color: #1a1d2e;
    }

    /* ─── LAYOUT SHELL ─── */
    #app-shell {
      display: flex;
      height: 100vh;
      width: 100%;
      overflow: hidden;
    }

    /* ─── SIDEBAR ─── */
    #sidebar {
      width: 260px;
      min-width: 260px;
      max-width: 260px;
      background-color: #1e2432;
      color: #cbd5e1;
      display: flex;
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
      flex-shrink: 0;
      transition: width 0.25s ease, min-width 0.25s ease;
    }

    .sidebar-head {
      padding: 20px 16px 16px;
      border-bottom: 1px solid #2d3748;
      flex-shrink: 0;
    }

    .brand-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 14px;
    }

    .brand-icon {
      width: 38px;
      height: 38px;
      background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 16px;
      flex-shrink: 0;
    }

    .brand-title {
      font-size: 14px;
      font-weight: 700;
      color: #f1f5f9;
      margin: 0;
      line-height: 1.2;
    }

    .brand-sub {
      font-size: 11px;
      color: #64748b;
    }

    .btn-back-sidebar {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
      padding: 8px 12px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.09);
      border-radius: 8px;
      color: #94a3b8;
      font-size: 12.5px;
      font-weight: 500;
      text-decoration: none;
      transition: background 0.2s, color 0.2s;
      cursor: pointer;
    }

    .btn-back-sidebar:hover {
      background: rgba(255,255,255,0.12);
      color: #e2e8f0;
      text-decoration: none;
    }

    .sidebar-section {
      padding: 14px 16px 6px;
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #475569;
      flex-shrink: 0;
    }

    .sidebar-files {
      flex: 1;
      overflow-y: auto;
      padding: 4px 8px 16px;
    }

    .sidebar-files::-webkit-scrollbar { width: 4px; }
    .sidebar-files::-webkit-scrollbar-track { background: transparent; }
    .sidebar-files::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

    .file-link {
      display: flex;
      align-items: flex-start;
      gap: 9px;
      padding: 9px 10px;
      border-radius: 8px;
      color: #94a3b8;
      font-size: 12.5px;
      text-decoration: none;
      margin-bottom: 2px;
      word-break: break-all;
      line-height: 1.35;
      transition: background 0.15s, color 0.15s;
    }

    .file-link:hover {
      background: #2a3347;
      color: #e2e8f0;
      text-decoration: none;
    }

    .file-link.active-file {
      background: #3b82f6;
      color: #ffffff;
      font-weight: 600;
    }

    .file-link .fa {
      margin-top: 2px;
      font-size: 12px;
      flex-shrink: 0;
    }

    .folder-group-label {
      display: flex;
      align-items: center;
      gap: 7px;
      padding: 10px 10px 5px;
      font-size: 10.5px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: #475569;
    }

    .folder-group-label .fa {
      font-size: 11px;
      color: #64748b;
    }

    .file-link-sub {
      padding-left: 22px;
    }

    /* ─── FOLDER ACCORDION ─── */
    .folder-accordion {
      margin-top: 6px;
    }

    .folder-toggle {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      padding: 9px 10px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 8px;
      color: #94a3b8;
      font-size: 12.5px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.15s, color 0.15s;
      text-align: left;
    }

    .folder-toggle:hover {
      background: #2a3347;
      color: #e2e8f0;
    }

    .folder-toggle.is-open {
      background: rgba(59,130,246,0.1);
      border-color: rgba(59,130,246,0.2);
      color: #93c5fd;
    }

    .folder-toggle-left {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .folder-icon {
      font-size: 13px;
      color: #f59e0b;
      flex-shrink: 0;
    }

    .folder-toggle.is-open .folder-icon {
      color: #fbbf24;
    }

    .folder-count {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,0.08);
      border-radius: 20px;
      padding: 1px 7px;
      font-size: 10px;
      font-weight: 600;
      color: #64748b;
      min-width: 20px;
    }

    .folder-toggle.is-open .folder-count {
      background: rgba(59,130,246,0.2);
      color: #93c5fd;
    }

    .folder-chevron {
      font-size: 10px;
      color: #475569;
      transition: transform 0.25s ease;
      flex-shrink: 0;
    }

    .folder-toggle.is-open .folder-chevron {
      transform: rotate(90deg);
      color: #60a5fa;
    }

    .folder-files {
      overflow: hidden;
      padding-top: 4px;
    }

    /* ─── FILE SELECT DROPDOWN ─── */
    .file-select-wrap {
      position: relative;
      margin: 0 0 12px;
    }

    .file-select-icon {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      font-size: 13px;
      pointer-events: none;
      z-index: 1;
    }

    .file-select {
      width: 100%;
      padding: 10px 32px 10px 34px;
      background: #111827;
      border: 1px solid #374151;
      border-radius: 10px;
      color: #e2e8f0;
      font-size: 12.5px;
      font-family: 'JetBrains Mono', monospace;
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
    }

    .file-select:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    }

    .file-select option, .file-select optgroup {
      background: #1e2432;
      color: #e2e8f0;
      font-size: 12.5px;
    }

    /* Light mode dropdown */
    body:not(.dark-mode) .file-select {
      background-color: #1e2432;
    }

    /* ─── ACTIVE FILE CARD ─── */
    .active-file-card {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      background: rgba(59,130,246,0.1);
      border: 1px solid rgba(59,130,246,0.25);
      border-radius: 10px;
      margin-bottom: 10px;
    }

    .active-file-icon {
      width: 32px;
      height: 32px;
      background: rgba(59,130,246,0.2);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #60a5fa;
      font-size: 13px;
      flex-shrink: 0;
    }

    .active-file-info { min-width: 0; }

    .active-file-label {
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #60a5fa;
      margin-bottom: 2px;
    }

    .active-file-name {
      font-size: 11.5px;
      font-family: 'JetBrains Mono', monospace;
      color: #e2e8f0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-weight: 500;
    }

    /* ─── FILE COUNT BADGE ─── */
    .file-count-badge {
      display: flex;
      align-items: center;
      gap: 7px;
      padding: 7px 10px;
      background: rgba(255,255,255,0.04);
      border-radius: 8px;
      font-size: 11.5px;
      color: #64748b;
    }

    .file-count-badge .fa {
      color: #475569;
      font-size: 12px;
    }

    .sidebar-foot {
      padding: 12px 16px;
      border-top: 1px solid #2d3748;
      flex-shrink: 0;
    }

    /* ─── DARK TOGGLE SWITCH ─── */
    .toggle-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 10px;
      background: rgba(255,255,255,0.04);
      border-radius: 8px;
    }

    .toggle-label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      color: #94a3b8;
    }

    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 38px;
      height: 22px;
    }

    .toggle-switch input { opacity: 0; width: 0; height: 0; }

    .toggle-track {
      position: absolute;
      inset: 0;
      background: #334155;
      border-radius: 22px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .toggle-track::before {
      content: '';
      position: absolute;
      width: 16px;
      height: 16px;
      background: #fff;
      border-radius: 50%;
      top: 3px;
      left: 3px;
      transition: transform 0.3s;
    }

    .toggle-switch input:checked + .toggle-track { background: #3b82f6; }
    .toggle-switch input:checked + .toggle-track::before { transform: translateX(16px); }

    /* ─── MAIN ─── */
    #main-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      min-width: 0;
      background-color: #f0f2f5;
    }

    /* ─── TOPBAR ─── */
    #topbar {
      background: #ffffff;
      border-bottom: 1px solid #e5e7eb;
      padding: 0 20px;
      height: 58px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06);
      gap: 12px;
      flex-wrap: wrap;
    }

    .file-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 12.5px;
      font-weight: 500;
      font-family: 'JetBrains Mono', monospace;
      color: #374151;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .file-badge .fa { color: #3b82f6; }

    .action-group {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }

    .act-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 6px 13px;
      border-radius: 8px;
      font-size: 12.5px;
      font-weight: 500;
      text-decoration: none;
      border: 1px solid #e5e7eb;
      background: #ffffff;
      color: #374151;
      transition: all 0.18s;
      cursor: pointer;
      white-space: nowrap;
    }

    .act-btn:hover {
      background: #f3f4f6;
      color: #111827;
      text-decoration: none;
    }

    .act-btn.danger:hover {
      color: #dc2626;
      border-color: #fca5a5;
      background: #fef2f2;
    }

    /* ─── CONTENT AREA ─── */
    #content-area {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
    }

    #content-area::-webkit-scrollbar { width: 6px; }
    #content-area::-webkit-scrollbar-track { background: transparent; }
    #content-area::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

    .log-card {
      background: #ffffff;
      border-radius: 14px;
      border: 1px solid #e5e7eb;
      box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
      overflow: hidden;
    }

    /* ─── DATATABLE OVERRIDES ─── */
    .dataTables_wrapper {
      padding: 0;
    }

    .dt-top-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 18px;
      border-bottom: 1px solid #f3f4f6;
      flex-wrap: wrap;
      gap: 8px;
    }

    .dataTables_length select,
    .dataTables_filter input {
      background: #f9fafb !important;
      border: 1px solid #e5e7eb !important;
      border-radius: 8px !important;
      color: #374151 !important;
      padding: 6px 10px !important;
      font-size: 12.5px !important;
      font-family: 'Inter', sans-serif !important;
      outline: none;
    }

    .dataTables_filter input:focus {
      border-color: #3b82f6 !important;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.12) !important;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
      font-size: 12.5px;
      color: #6b7280;
      padding: 12px 18px;
    }

    .dataTables_wrapper .dataTables_paginate {
      padding: 10px 18px 14px;
    }

    .dataTables_wrapper .page-link {
      background: #f9fafb;
      border-color: #e5e7eb;
      color: #374151;
      border-radius: 7px !important;
      margin: 0 2px;
      font-size: 12.5px;
      padding: 5px 11px;
    }

    .dataTables_wrapper .page-item.active .page-link {
      background: #3b82f6;
      border-color: #3b82f6;
      color: #fff;
    }

    .dataTables_wrapper .page-link:hover {
      background: #3b82f6;
      border-color: #3b82f6;
      color: #fff;
    }

    /* ─── TABLE ─── */
    #table-log {
      font-size: 13px;
      width: 100% !important;
    }

    #table-log thead th {
      background: #f8fafc;
      color: #6b7280;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      padding: 11px 14px;
      border-bottom: 1px solid #e5e7eb !important;
      border-top: none !important;
      white-space: nowrap;
    }

    #table-log tbody tr {
      border-bottom: 1px solid #f3f4f6;
      cursor: pointer;
      transition: background 0.12s;
    }

    #table-log tbody tr:last-child { border-bottom: none; }

    #table-log tbody tr:hover { background-color: #f0f7ff; }

    #table-log tbody td {
      padding: 11px 14px;
      vertical-align: top;
      border-top: none !important;
      color: #1a1d2e;
    }

    .col-date {
      white-space: nowrap;
      font-family: 'JetBrains Mono', monospace;
      font-size: 11.5px;
      color: #6b7280;
      min-width: 140px;
    }

    .col-content {
      word-break: break-word;
      line-height: 1.6;
    }

    .col-context {
      font-size: 12px;
      color: #6b7280;
      max-width: 120px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    /* ─── LEVEL BADGES ─── */
    .lvl-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 10.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      white-space: nowrap;
    }

    .lvl-emergency { background: #fef2f2; color: #dc2626; }
    .lvl-alert     { background: #fff7ed; color: #ea580c; }
    .lvl-critical  { background: #fdf2f8; color: #db2777; }
    .lvl-error     { background: #fef2f2; color: #ef4444; }
    .lvl-warning   { background: #fffbeb; color: #d97706; }
    .lvl-notice    { background: #eff6ff; color: #2563eb; }
    .lvl-info      { background: #ecfdf5; color: #059669; }
    .lvl-debug     { background: #f5f3ff; color: #7c3aed; }

    /* Row left-border by level */
    .row-emergency { border-left: 3px solid #dc2626; }
    .row-alert     { border-left: 3px solid #ea580c; }
    .row-critical  { border-left: 3px solid #db2777; }
    .row-error     { border-left: 3px solid #ef4444; }
    .row-warning   { border-left: 3px solid #d97706; }
    .row-notice    { border-left: 3px solid #2563eb; }
    .row-info      { border-left: 3px solid #059669; }
    .row-debug     { border-left: 3px solid #7c3aed; }

    /* ─── STACK TRACE ─── */
    .stack-box {
      display: none;
      margin-top: 10px;
      background: #f8fafc;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 14px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 11.5px;
      line-height: 1.7;
      white-space: pre-wrap;
      color: #4b5563;
      max-height: 320px;
      overflow: auto;
    }

    .btn-stack {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 3px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 500;
      border: 1px solid #e5e7eb;
      background: #f9fafb;
      color: #6b7280;
      cursor: pointer;
      float: right;
      margin: 0 0 6px 10px;
      transition: all 0.15s;
    }

    .btn-stack:hover,
    .btn-stack.is-open {
      border-color: #3b82f6;
      color: #3b82f6;
      background: #eff6ff;
    }

    /* ─── EMPTY STATE ─── */
    .empty-state {
      padding: 80px 20px;
      text-align: center;
      color: #9ca3af;
    }

    .empty-state .icon {
      font-size: 52px;
      margin-bottom: 18px;
      opacity: 0.3;
    }

    .empty-state h5 {
      font-size: 16px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 8px;
    }

    .empty-state p {
      font-size: 13.5px;
      max-width: 380px;
      margin: 0 auto;
    }

    /* ─── MOBILE SIDEBAR TOGGLE ─── */
    #sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.4);
      z-index: 200;
    }

    .btn-menu {
      display: none;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 8px;
      background: #f3f4f6;
      border: 1px solid #e5e7eb;
      color: #374151;
      cursor: pointer;
      font-size: 16px;
      flex-shrink: 0;
    }

    /* ══════════════════════════════════════════
       DARK MODE
    ══════════════════════════════════════════ */
    body.dark-mode {
      background-color: #0f1117;
      color: #e2e8f0;
    }

    body.dark-mode #main-panel { background-color: #0f1117; }

    body.dark-mode #topbar {
      background: #1a1d2e;
      border-color: #2d3748;
    }

    body.dark-mode .file-badge {
      background: #111827;
      border-color: #2d3748;
      color: #e2e8f0;
    }

    body.dark-mode .act-btn {
      background: #1a1d2e;
      border-color: #2d3748;
      color: #cbd5e1;
    }

    body.dark-mode .act-btn:hover {
      background: #2d3748;
      color: #f1f5f9;
    }

    body.dark-mode .act-btn.danger:hover {
      background: #1f1010;
      border-color: #7f1d1d;
      color: #ef4444;
    }

    body.dark-mode #content-area { background: #0f1117; }

    body.dark-mode .log-card {
      background: #1a1d2e;
      border-color: #2d3748;
    }

    body.dark-mode #table-log thead th {
      background: #111827;
      color: #94a3b8;
      border-color: #2d3748 !important;
    }


    /* ── Override Bootstrap 5 CSS vars on the table element ── */
    body.dark-mode #table-log {
      --bs-table-bg: transparent;
      --bs-table-striped-bg: rgba(255,255,255,0.03);
      --bs-table-hover-bg: rgba(59,130,246,0.08);
      --bs-table-color: #e2e8f0;
      --bs-table-border-color: #1f2937;
      color: #e2e8f0;
    }

    body.dark-mode #table-log tbody td,
    body.dark-mode #table-log tbody th {
      background-color: transparent !important;
      color: #e2e8f0 !important;
      border-color: #1f2937 !important;
    }

    body.dark-mode #table-log tbody tr {
      background-color: transparent !important;
      border-color: #1f2937 !important;
    }

    body.dark-mode #table-log tbody tr:hover > td {
      background-color: rgba(59,130,246,0.08) !important;
      color: #f1f5f9 !important;
    }

    body.dark-mode .col-date { color: #6b7280 !important; }
    body.dark-mode .col-context { color: #6b7280 !important; }

    body.dark-mode small[style*="color:#9ca3af"] { color: #4b5563 !important; }

    body.dark-mode .lvl-emergency { background: rgba(220,38,38,0.18); }
    body.dark-mode .lvl-alert     { background: rgba(234,88,12,0.18); }
    body.dark-mode .lvl-critical  { background: rgba(219,39,119,0.18); }
    body.dark-mode .lvl-error     { background: rgba(239,68,68,0.18); }
    body.dark-mode .lvl-warning   { background: rgba(217,119,6,0.18); }
    body.dark-mode .lvl-notice    { background: rgba(37,99,235,0.18); }
    body.dark-mode .lvl-info      { background: rgba(5,150,105,0.18); }
    body.dark-mode .lvl-debug     { background: rgba(124,58,237,0.18); }

    body.dark-mode .stack-box {
      background: #0f1117;
      border-color: #374151;
      color: #94a3b8;
    }

    body.dark-mode .btn-stack {
      background: #0f1117;
      border-color: #374151;
      color: #6b7280;
    }

    body.dark-mode .btn-stack:hover,
    body.dark-mode .btn-stack.is-open {
      border-color: #3b82f6;
      color: #60a5fa;
      background: rgba(59,130,246,0.1);
    }

    /* DataTable wrapper dark */
    body.dark-mode .dataTables_wrapper {
      color: #94a3b8;
    }

    body.dark-mode .dataTables_length label,
    body.dark-mode .dataTables_filter label,
    body.dark-mode .dataTables_info {
      color: #6b7280 !important;
    }

    body.dark-mode .dataTables_length select,
    body.dark-mode .dataTables_filter input {
      background: #0f1117 !important;
      border: 1px solid #374151 !important;
      border-radius: 8px !important;
      color: #e2e8f0 !important;
    }

    body.dark-mode .dataTables_wrapper .dataTables_info,
    body.dark-mode .dataTables_wrapper .dataTables_length,
    body.dark-mode .dataTables_wrapper .dataTables_filter {
      color: #6b7280;
    }

    body.dark-mode .dataTables_wrapper .page-link {
      background: #111827 !important;
      border-color: #374151 !important;
      color: #e2e8f0 !important;
    }

    body.dark-mode .dataTables_wrapper .page-item.active .page-link {
      background: #3b82f6 !important;
      border-color: #3b82f6 !important;
      color: #fff !important;
    }

    body.dark-mode .dataTables_wrapper .page-link:hover {
      background: #2563eb !important;
      border-color: #2563eb !important;
      color: #fff !important;
    }

    body.dark-mode .empty-state h5 { color: #e2e8f0; }

    body.dark-mode #content-area::-webkit-scrollbar-thumb { background: #334155; }

    /* ─── RESPONSIVE ─── */
    @media (max-width: 767.98px) {
      #sidebar {
        position: fixed;
        left: -260px;
        top: 0;
        z-index: 300;
        transition: left 0.28s ease;
      }

      #sidebar.sidebar-open {
        left: 0;
      }

      #sidebar-overlay.overlay-show {
        display: block;
      }

      .btn-menu {
        display: inline-flex;
      }

      #topbar {
        padding: 0 14px;
        height: auto;
        min-height: 56px;
        flex-wrap: wrap;
        gap: 8px;
        padding-top: 8px;
        padding-bottom: 8px;
      }

      .file-badge {
        font-size: 11.5px;
        max-width: 200px;
      }

      .action-group .act-btn span.label-text {
        display: none;
      }

      #content-area {
        padding: 12px;
      }
    }

    @media (min-width: 768px) and (max-width: 1024px) {
      #sidebar {
        width: 220px;
        min-width: 220px;
      }
    }
  </style>
</head>

<body>
<!-- Apply dark mode class before paint -->
<script>
  if (localStorage.getItem('llv-dark') === '1') {
    document.body.classList.add('dark-mode');
  }
</script>

<!-- Mobile overlay -->
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<div id="app-shell">

  <!-- ══════════ SIDEBAR ══════════ -->
  <aside id="sidebar">
    <div class="sidebar-head">
      <div class="brand-row">
        <div class="brand-icon">
          <i class="fa fa-terminal"></i>
        </div>
        <div>
          <div class="brand-title">Log Viewer</div>
          <div class="brand-sub">Laravel Application</div>
        </div>
      </div>
      <a href="/home" class="btn-back-sidebar">
        <i class="fa fa-arrow-left fa-xs"></i>
        Kembali ke Dashboard
      </a>
    </div>

    <div class="sidebar-section">Log Files</div>

    <div class="sidebar-files">

      {{-- Top-level log files --}}
      @foreach($files as $file)
        <a href="?l={{ \Illuminate\Support\Facades\Crypt::encrypt($file) }}"
           class="file-link @if($current_file == $file) active-file @endif">
          <i class="fa fa-file-lines"></i>
          <span>{{ $file }}</span>
        </a>
      @endforeach

      {{-- Folder-based log files (collapsible) --}}
      @foreach($folders as $folder)
        @if(isset($structure[$folder]) && is_array($structure[$folder]) && count($structure[$folder]))
          @php
            $folderName   = basename($folder);
            $folderId     = 'folder-' . Str::slug($folderName);
            $hasActiveFile = false;
            foreach($structure[$folder] as $ffile) {
              if (basename($ffile) === $current_file) { $hasActiveFile = true; break; }
            }
          @endphp
          <div class="folder-accordion">
            <button class="folder-toggle @if($hasActiveFile) is-open @endif"
                    onclick="toggleFolder('{{ $folderId }}', this)">
              <span class="folder-toggle-left">
                <i class="fa fa-folder{{ $hasActiveFile ? '-open' : '' }} folder-icon"></i>
                {{ $folderName }}
                <span class="folder-count">{{ count($structure[$folder]) }}</span>
              </span>
              <i class="fa fa-chevron-right folder-chevron"></i>
            </button>
            <div class="folder-files" id="{{ $folderId }}" style="{{ $hasActiveFile ? 'display:block;' : 'display:none;' }}">
              @foreach($structure[$folder] as $ffile)
                @php $fname = basename($ffile); @endphp
                <a href="?l={{ \Illuminate\Support\Facades\Crypt::encrypt($fname) }}&f={{ \Illuminate\Support\Facades\Crypt::encrypt($folderName) }}"
                   class="file-link file-link-sub @if($current_file == $fname) active-file @endif">
                  <i class="fa fa-file-lines"></i>
                  <span>{{ $fname }}</span>
                </a>
              @endforeach
            </div>
          </div>
        @endif
      @endforeach

    </div>

    <div class="sidebar-foot">
      <div class="toggle-row">
        <div class="toggle-label">
          <i class="fa fa-moon"></i>
          <span>Dark Mode</span>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" id="darkToggle" onchange="toggleDark(this)">
          <span class="toggle-track"></span>
        </label>
      </div>
    </div>
  </aside>

  <!-- ══════════ MAIN PANEL ══════════ -->
  <div id="main-panel">

    <!-- TOP BAR -->
    <div id="topbar">
      <div style="display:flex;align-items:center;gap:10px;min-width:0;">
        <!-- Mobile menu button -->
        <button class="btn-menu" onclick="openSidebar()" title="Menu">
          <i class="fa fa-bars"></i>
        </button>

        @if($current_file)
          <div class="file-badge">
            <i class="fa fa-file-lines"></i>
            {{ $current_file }}
          </div>
        @else
          <div class="file-badge" style="color:#9ca3af;">
            <i class="fa fa-folder-open"></i>
            Pilih file log dari sidebar
          </div>
        @endif
      </div>

      <div class="action-group">
        @if($current_file)
          <a href="?dl={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f='.\Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}"
             class="act-btn" title="Download">
            <i class="fa fa-download"></i>
            <span class="label-text">Download</span>
          </a>
          <a id="btn-clean"
             href="?clean={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f='.\Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}"
             class="act-btn" title="Bersihkan">
            <i class="fa fa-eraser"></i>
            <span class="label-text">Bersihkan</span>
          </a>
          <a id="btn-delete"
             href="?del={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ $current_folder ? '&f='.\Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}"
             class="act-btn danger" title="Hapus">
            <i class="fa fa-trash"></i>
            <span class="label-text">Hapus</span>
          </a>
          @if(count($files) > 1)
            <a id="btn-delete-all"
               href="?delall=true{{ $current_folder ? '&f='.\Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}"
               class="act-btn danger" title="Hapus Semua">
              <i class="fa fa-trash-can"></i>
              <span class="label-text">Hapus Semua</span>
            </a>
          @endif
        @endif
      </div>
    </div>

    <!-- CONTENT -->
    <div id="content-area">

      @if($logs === null)
        <div class="log-card">
          <div class="empty-state">
            <div class="icon"><i class="fa fa-triangle-exclamation"></i></div>
            <h5>File Terlalu Besar</h5>
            <p>File log melebihi 50MB dan tidak dapat ditampilkan.<br>Silakan download untuk membukanya.</p>
            @if($current_file)
              <a href="?dl={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}"
                 class="act-btn mt-4 d-inline-flex" style="margin-top:16px;">
                <i class="fa fa-download"></i> Download Log
              </a>
            @endif
          </div>
        </div>

      @elseif(empty($logs))
        <div class="log-card">
          <div class="empty-state">
            <div class="icon" style="opacity:1;color:#10b981;">
              <i class="fa fa-circle-check"></i>
            </div>
            <h5>File Log Kosong</h5>
            <p>Tidak ada entri log yang ditemukan dalam file ini.</p>
          </div>
        </div>

      @else
        <div class="log-card">
          <table id="table-log" class="table" data-ordering-index="{{ $standardFormat ? 2 : 0 }}">
            <thead>
              <tr>
                @if($standardFormat)
                  <th>Level</th>
                  <th>Context</th>
                  <th>Tanggal</th>
                @else
                  <th>No. Baris</th>
                @endif
                <th>Pesan Log</th>
              </tr>
            </thead>
            <tbody>
              @foreach($logs as $key => $log)
                @php $lvl = strtolower($log['level'] ?? 'debug'); @endphp
                <tr data-stack="stack-{{ $key }}" class="row-{{ $lvl }}">
                  @if($standardFormat)
                    <td class="nowrap">
                      <span class="lvl-badge lvl-{{ $lvl }}">
                        <i class="fa fa-{{ $log['level_img'] }}" style="font-size:9px;"></i>
                        {{ $log['level'] }}
                      </span>
                    </td>
                    <td class="col-context" title="{{ $log['context'] }}">{{ $log['context'] }}</td>
                  @endif
                  <td class="col-date">{{ $log['date'] }}</td>
                  <td class="col-content">
                    @if($log['stack'])
                      <button class="btn-stack" onclick="event.stopPropagation(); toggleStack(this, 'stack-{{ $key }}')">
                        <i class="fa fa-code"></i> Stack Trace
                      </button>
                    @endif
                    <span>{{ $log['text'] }}</span>
                    @if(isset($log['in_file']))
                      <br><small style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#9ca3af;">{{ $log['in_file'] }}</small>
                    @endif
                    @if($log['stack'])
                      <div class="stack-box" id="stack-{{ $key }}">{{ trim($log['stack']) }}</div>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

    </div><!-- /#content-area -->
  </div><!-- /#main-panel -->
</div><!-- /#app-shell -->

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
  /* ─── DARK MODE ─── */
  function toggleDark(checkbox) {
    if (checkbox.checked) {
      document.body.classList.add('dark-mode');
      localStorage.setItem('llv-dark', '1');
    } else {
      document.body.classList.remove('dark-mode');
      localStorage.removeItem('llv-dark');
    }
  }

  // Sync toggle state on load
  window.addEventListener('DOMContentLoaded', function () {
    const tog = document.getElementById('darkToggle');
    if (tog) tog.checked = localStorage.getItem('llv-dark') === '1';
  });

  /* ─── MOBILE SIDEBAR ─── */
  function openSidebar() {
    document.getElementById('sidebar').classList.add('sidebar-open');
    document.getElementById('sidebar-overlay').classList.add('overlay-show');
  }

  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('sidebar-open');
    document.getElementById('sidebar-overlay').classList.remove('overlay-show');
  }

  /* ─── FOLDER ACCORDION ─── */
  function toggleFolder(id, btn) {
    const panel = document.getElementById(id);
    if (!panel) return;

    const isOpen = panel.style.display === 'block';
    panel.style.display = isOpen ? 'none' : 'block';
    btn.classList.toggle('is-open', !isOpen);

    // swap folder icon closed ↔ open
    const icon = btn.querySelector('.folder-icon');
    if (icon) {
      if (isOpen) {
        icon.classList.remove('fa-folder-open');
        icon.classList.add('fa-folder');
      } else {
        icon.classList.remove('fa-folder');
        icon.classList.add('fa-folder-open');
      }
    }
  }

  /* ─── STACK TRACE TOGGLE ─── */
  function toggleStack(btn, id) {
    const el = document.getElementById(id);
    if (!el) return;
    const isOpen = el.style.display === 'block';
    el.style.display = isOpen ? 'none' : 'block';
    btn.classList.toggle('is-open', !isOpen);
    btn.innerHTML = isOpen
      ? '<i class="fa fa-code"></i> Stack Trace'
      : '<i class="fa fa-chevron-up"></i> Tutup';
  }

  /* ─── DATATABLE ─── */
  $(document).ready(function () {
    const orderIdx = parseInt($('#table-log').data('ordering-index') || 0);

    $('#table-log').DataTable({
      order: [orderIdx, 'desc'],
      stateSave: true,
      stateSaveCallback: function (s, data) { localStorage.setItem('llv-dt', JSON.stringify(data)); },
      stateLoadCallback: function () {
        const d = JSON.parse(localStorage.getItem('llv-dt') || 'null');
        if (d) d.start = 0;
        return d;
      },
      language: {
        search: '',
        searchPlaceholder: '🔍  Cari log...',
        lengthMenu: 'Tampilkan _MENU_ entri',
        info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
        infoEmpty: 'Tidak ada entri',
        infoFiltered: '(difilter dari _MAX_)',
        paginate: {
          first: '«',
          last: '»',
          previous: '‹',
          next: '›',
        },
        emptyTable: 'File log kosong',
        zeroRecords: 'Tidak ada log yang cocok',
      },
    });

    // Click row → toggle stack
    $('#content-area').on('click', '#table-log tbody tr', function () {
      const stackId = $(this).data('stack');
      const el = document.getElementById(stackId);
      if (!el || el.innerHTML.trim() === '') return;
      const isOpen = el.style.display === 'block';
      el.style.display = isOpen ? 'none' : 'block';
      const btn = this.querySelector('.btn-stack');
      if (btn) {
        btn.classList.toggle('is-open', !isOpen);
        btn.innerHTML = isOpen
          ? '<i class="fa fa-code"></i> Stack Trace'
          : '<i class="fa fa-chevron-up"></i> Tutup';
      }
    });

    // Confirm dialogs
    $('#btn-clean').on('click', function () { return confirm('Yakin ingin mengosongkan file log ini?'); });
    $('#btn-delete').on('click', function () { return confirm('Yakin ingin menghapus file log ini?'); });
    $('#btn-delete-all').on('click', function () { return confirm('Yakin menghapus SEMUA file log? Tidak dapat dibatalkan!'); });
  });
</script>
</body>
</html>
