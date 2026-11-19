@extends('layouts.app')

@section('content')
@php
$sectionMeta = [
    'karyawan' => [
        'name' => 'Karyawan',
        'icon' => 'users.svg',
        'color' => '#2563eb',
        'desc' => 'Profil Saya, Data Karyawan, Aktivitas Harian, Management Ruangan, Jabatan, Pengumuman, Absen, SPJ, Cuti, Gaji & Lembur',
        'tags' => ['Profil Saya', 'Data Karyawan', 'Absensi & Cuti', 'Gaji & Lembur'],
    ],
    'peserta' => [
        'name' => 'Peserta',
        'icon' => 'user-check.svg',
        'color' => '#059669',
        'desc' => 'Data Peserta, Registrasi, Perusahaan, Registrasi Exam',
        'tags' => ['Data Peserta', 'Registrasi', 'Perusahaan', 'Registrasi Exam'],
    ],
    'itsm' => [
        'name' => 'IT Service Management (ITSM)',
        'icon' => 'terminal.svg',
        'color' => '#4f46e5',
        'desc' => 'Timeline Webinar, Content Harian, Rekap Penilaian Exam, Registry Feature, Laporan Insiden, Papan Kanban, SLA, Helpdesk, Survey, Knowledge, Kolaborasi',
        'tags' => ['Helpdesk Ticketing', 'SLA Management', 'Papan Kanban', 'Webinar'],
    ],
    'rkm' => [
        'name' => 'Rencana Kelas Mingguan (RKM)',
        'icon' => 'calendar-days.svg',
        'color' => '#d97706',
        'desc' => 'Rencana Kelas Mingguan, Kelas Setting, Materi, Feedback, Pengajuan Exam, Upload, List Exam, Daftar Peserta Exam, Harga Exam, Kelas Analisis, Komplain Peserta',
        'tags' => ['Jadwal RKM', 'Kelas Setting', 'Materi & Feedback', 'List Exam'],
    ],
    'finance' => [
        'name' => 'Finance & Accounting',
        'icon' => 'wallet.svg',
        'color' => '#0d9488',
        'desc' => 'Invoice, Laporan Laba Rugi, Credit Card, Hitung Tunjangan, Hitung Lembur, Souvenir, Outstanding, Payment Advance',
        'tags' => ['Invoice', 'Laba Rugi', 'Hitung Tunjangan', 'Outstanding'],
    ],
    'performance' => [
        'name' => 'Performance Assessment',
        'icon' => 'trending-up.svg',
        'color' => '#7c3aed',
        'desc' => 'Penilaian, Target Divisi, Overview Departement, Overview Personal, Penilaian Anda, Form Penilaian',
        'tags' => ['Penilaian', 'Target Divisi', 'Overview Personal', 'Form Penilaian'],
    ],
    'education' => [
        'name' => 'Education',
        'icon' => 'book-open.svg',
        'color' => '#db2777',
        'desc' => 'Sertifikasi & Pelatihan, Tunjangan Education, Pengajuan Lab, Pengajuan Subs, CV Instruktur, Rekap Mengajar, Klaim Modul, Rekomendasi Training, Activity Report',
        'tags' => ['Sertifikasi & Pelatihan', 'Tunjangan Education', 'Rekap Mengajar', 'Klaim Modul'],
    ],
    'office' => [
        'name' => 'Office Management',
        'icon' => 'briefcase.svg',
        'color' => '#0284c7',
        'desc' => 'Dashboard Office, INIX HR, New Hire, Struktur Organisasi, Inventaris, Pengajuan Klaim, Pengajuan Catering, Rencana Pembelian',
        'tags' => ['Dashboard Office', 'INIX HR', 'Inventaris', 'Pengajuan Klaim'],
    ],
    'crm' => [
        'name' => 'CRM (Customer Relationship)',
        'icon' => 'contact.svg',
        'color' => '#ea580c',
        'desc' => 'Fitur CRM, Pengajuan Diluar PA',
        'tags' => ['Fitur CRM', 'Pengajuan Diluar PA'],
    ],
    'management' => [
        'name' => 'Management',
        'icon' => 'target.svg',
        'color' => '#dc2626',
        'desc' => 'Set Target',
        'tags' => ['Set Target'],
    ],
    'project' => [
        'name' => 'Project Management',
        'icon' => 'layout.svg',
        'color' => '#475569',
        'desc' => 'Administrasi, Lead Projek, Kanban, Laporan Penjualan Projek, Aktivitas Visit Projek',
        'tags' => ['Administrasi', 'Lead Projek', 'Kanban', 'Laporan Penjualan'],
    ],
];

$sectionCards = [
    'karyawan' => [
        ['id' => 'profil_saya', 'title' => 'Profil Saya', 'desc' => 'Profil saya sebagai karyawan INIXINDO Bandung.', 'icon' => 'user.svg', 'is_svg' => true],
        ['id' => 'data_karyawan', 'title' => 'Data Karyawan', 'desc' => 'Data lengkap semua karyawan.', 'icon' => 'users.svg', 'is_svg' => true],
        ['id' => 'aktivitas_harian', 'title' => 'Aktivitas Harian', 'desc' => 'aktivitas per hari untuk masing-masing divisi', 'icon' => 'clipboard.svg', 'is_svg' => true],
        ['id' => 'management_ruangan', 'title' => 'Management Ruangan', 'desc' => 'Management Ruangan yang ada di kantor Inixindo.', 'icon' => 'layout-grid.svg', 'is_svg' => true],
        ['id' => 'jabatan', 'title' => 'Jabatan', 'desc' => 'Data Jabatan.', 'icon' => 'award.svg', 'is_svg' => true],
        ['id' => 'pengumuman', 'title' => 'Pengumuman', 'desc' => 'Pemberitahuan.', 'icon' => 'bell.svg', 'is_svg' => true],
        ['id' => 'absen', 'title' => 'Absen', 'desc' => 'Absensi.', 'icon' => 'camera.svg', 'is_svg' => true],
        ['id' => 'rekapitulasi_absensi', 'title' => 'Rekapitulasi Absensi', 'desc' => 'Data Rekapitulasi Absen Karyawan.', 'icon' => 'archive.svg', 'is_svg' => true],
        ['id' => 'catatan_absensi', 'title' => 'Catatan Absensi', 'desc' => 'Absensi anda pada bulan ini.', 'icon' => 'calendar.svg', 'is_svg' => true],
        ['id' => 'pengajuan_cuti', 'title' => 'Pengajuan Cuti', 'desc' => 'Klik disini untuk pengajuan cuti.', 'icon' => 'clock.svg', 'is_svg' => true],
        ['id' => 'pengajuan_barang', 'title' => 'Pengajuan Barang', 'desc' => 'Klik disini untuk pengajuan barang.', 'icon' => 'feather.svg', 'is_svg' => true],
        ['id' => 'pengajuan_spj', 'title' => 'Pengajuan SPJ', 'desc' => 'Klik disini untuk pengajuan SPJ.', 'icon' => 'send.svg', 'is_svg' => true],
        ['id' => 'gaji_tunjangan', 'title' => 'Gaji & Tunjangan', 'desc' => 'Gaji & Tunjangan Karyawan.', 'icon' => 'dollar-sign.svg', 'is_svg' => true],
        ['id' => 'update_gaji', 'title' => 'Update Gaji Karyawan', 'desc' => 'Update Gaji Karyawan.', 'icon' => 'hand-coins.svg', 'is_svg' => true],
        ['id' => 'pengajuan_izin', 'title' => 'Pengajuan Izin', 'desc' => 'Pengajuan Izin 3 Jam.', 'icon' => 'paperclip.svg', 'is_svg' => true],
        ['id' => 'lembur', 'title' => 'Lembur', 'desc' => 'Lembur.', 'icon' => 'aperture.svg', 'is_svg' => true],
    ],
    'peserta' => [
        ['id' => 'data_peserta', 'title' => 'Data Peserta', 'desc' => 'Data Peserta yang mengikuti kelas.', 'icon' => 'table.svg', 'is_svg' => true],
        ['id' => 'registrasi', 'title' => 'Registrasi', 'desc' => 'Registrasi peserta kelas.', 'icon' => 'user-check.svg', 'is_svg' => true],
        ['id' => 'perusahaan', 'title' => 'Perusahaan', 'desc' => 'Data Perusahaan.', 'icon' => 'briefcase.svg', 'is_svg' => true],
        ['id' => 'registrasi_exam', 'title' => 'Registrasi Exam', 'desc' => 'Data Registrasi Kelas Exam.', 'icon' => 'check-circle.svg', 'is_svg' => true],
    ],
    'itsm' => [
        ['id' => 'timeline_webinar', 'title' => 'Timeline Webinar', 'desc' => 'mapping webinar pertahun dan timeline.', 'icon' => 'fa-solid fa-timeline', 'is_svg' => false],
        ['id' => 'content_harian', 'title' => 'Content Harian', 'desc' => 'merekap konten harian', 'icon' => 'fa-solid fa-newspaper', 'is_svg' => false],
        ['id' => 'rekap_penilaian_exam', 'title' => 'Rekap Penilaian Exam', 'desc' => 'melihat penilaian pelayanan exam.', 'icon' => 'fa-solid fa-comment-dots', 'is_svg' => false],
        ['id' => 'registry_feature', 'title' => 'Registry Feature', 'desc' => 'feature registry.', 'icon' => 'fa-solid fa-book-bookmark', 'is_svg' => false],
        ['id' => 'laporan_insiden', 'title' => 'Laporan Insiden', 'desc' => 'Laporkan Insiden dan Risiko disekitar anda.', 'icon' => 'fa-regular fa-file', 'is_svg' => false],
        ['id' => 'papan_kanban', 'title' => 'Papan Kanban', 'desc' => 'untuk menejemen projek.', 'icon' => 'layout-grid.svg', 'is_svg' => true],
        ['id' => 'sla_management', 'title' => 'SLA Management', 'desc' => 'Pencapaian SLA ITSM.', 'icon' => 'fa-solid fa-chart-line', 'is_svg' => false],
        ['id' => 'it_helpdesk', 'title' => 'IT Helpdesk (Ticketing)', 'desc' => 'Laporkan Insiden dan Risiko yang anda alami.', 'icon' => 'fa-solid fa-headset', 'is_svg' => false],
        ['id' => 'pengajuan_subs', 'title' => 'Pengajuan Subs', 'desc' => 'pengajuan dan manajemen subscriptions', 'icon' => 'fa-solid fa-receipt', 'is_svg' => false],
        ['id' => 'survey_kepuasan', 'title' => 'Survey Kepuasan', 'desc' => 'Survey kepuasan pelayanan ITSM.', 'icon' => 'fa-solid fa-square-poll-vertical', 'is_svg' => false],
        ['id' => 'documentation_fitur', 'title' => 'Documentation Fitur', 'desc' => 'Documentation Fitur.', 'icon' => 'fa-solid fa-book', 'is_svg' => false],
        ['id' => 'knowledge_management', 'title' => 'Knowledge Management', 'desc' => 'Kelola SOP, FAQ, Tutorial, dan Panduan Instalasi ITSM.', 'icon' => 'fa-solid fa-book-open-reader', 'is_svg' => false],
        ['id' => 'kolaborasi', 'title' => 'Kolaborasi', 'desc' => 'Kolaborasi dengan Partner.', 'icon' => 'fa-solid fa-handshake', 'is_svg' => false],
    ],
    'rkm' => [
        ['id' => 'rencana_kelas_mingguan', 'title' => 'Rencana Kelas Mingguan', 'desc' => 'Rencana kelas Training.', 'icon' => 'calendar-days.svg', 'is_svg' => true],
        ['id' => 'kelas_setting', 'title' => 'Kelas Setting', 'desc' => 'Setting seluruh kebutuhan kelas mingguan.', 'icon' => 'cog.svg', 'is_svg' => true],
        ['id' => 'materi', 'title' => 'Materi', 'desc' => 'Data Materi.', 'icon' => 'book-open.svg', 'is_svg' => true],
        ['id' => 'feedback', 'title' => 'Feedback', 'desc' => 'Feedback Pelayanan.', 'icon' => 'file-text.svg', 'is_svg' => true],
        ['id' => 'pengajuan_exam', 'title' => 'Pengajuan Exam', 'desc' => 'Pengajuan Exam.', 'icon' => 'assept-document.svg', 'is_svg' => true],
        ['id' => 'upload_rkm', 'title' => 'Upload', 'desc' => 'Upload PDF Absensi & Sertifikat Peserta.', 'icon' => 'upload.svg', 'is_svg' => true],
        ['id' => 'list_exam', 'title' => 'List Exam', 'desc' => 'Data Exam.', 'icon' => 'list-check.svg', 'is_svg' => true],
        ['id' => 'daftar_peserta_exam', 'title' => 'Daftar Peserta Exam', 'desc' => 'Daftar peserta exam dan dokumentasi.', 'icon' => 'circle-user-round.svg', 'is_svg' => true],
        ['id' => 'harga_exam', 'title' => 'Harga Exam', 'desc' => 'Data Harga Exam.', 'icon' => 'tag.svg', 'is_svg' => true],
        ['id' => 'kelas_analisis', 'title' => 'Kelas Analisis', 'desc' => 'Analisis Rencana Kelas Mingguan.', 'icon' => 'stats.svg', 'is_svg' => true],
        ['id' => 'komplain_peserta', 'title' => 'Komplain Peserta', 'desc' => 'Komplain peserta.', 'icon' => 'fa fa-comment', 'is_svg' => false],
    ],
    'finance' => [
        ['id' => 'invoice', 'title' => 'Invoice', 'desc' => 'Data Invoice.', 'icon' => 'credit-card.svg', 'is_svg' => true],
        ['id' => 'laporan_laba_rugi', 'title' => 'Laporan Laba Rugi', 'desc' => 'Laporan Laba Rugi Inixindo Bandung.', 'icon' => 'chart-bar.svg', 'is_svg' => true],
        ['id' => 'credit_card', 'title' => 'Credit Card', 'desc' => 'Data Credit Card.', 'icon' => 'wallet.svg', 'is_svg' => true],
        ['id' => 'hitung_tunjangan', 'title' => 'Hitung Tunjangan', 'desc' => 'Data Tunjangan Karyawan', 'icon' => 'calculator.svg', 'is_svg' => true],
        ['id' => 'hitung_lembur', 'title' => 'Hitung Lembur', 'desc' => 'Data Lembur Karyawan', 'icon' => 'timer.svg', 'is_svg' => true],
        ['id' => 'souvenir', 'title' => 'Souvenir', 'desc' => 'Data Souvenir.', 'icon' => 'gift.svg', 'is_svg' => true],
        ['id' => 'outstanding', 'title' => 'Outstanding', 'desc' => 'Data Outstanding.', 'icon' => 'bookmark.svg', 'is_svg' => true],
        ['id' => 'payment_advance', 'title' => 'Payment Advance', 'desc' => 'Pengajuan Payment Advance.', 'icon' => 'fa fa-cart-shopping', 'is_svg' => false],
    ],
    'performance' => [
        ['id' => 'penilaian', 'title' => 'Penilaian', 'desc' => 'Dashboard Database Penilaian.', 'icon' => 'fa fa-ranking-star', 'is_svg' => false],
        ['id' => 'target_divisi', 'title' => 'Target Divisi', 'desc' => 'Data target divisi.', 'icon' => 'fa fa-bullseye', 'is_svg' => false],
        ['id' => 'overview_departement', 'title' => 'Overview Departement', 'desc' => 'Seluruh Perkembangan Target KPI Divisi', 'icon' => 'fa-chart-line', 'is_svg' => false],
        ['id' => 'overview_personal', 'title' => 'Overview Personal', 'desc' => 'Seluruh Progress Target KPI Anda.', 'icon' => 'fa-chart-line', 'is_svg' => false],
        ['id' => 'penilaian_anda', 'title' => 'Penilaian Anda', 'desc' => 'Data Hasil Penilaian 360 Anda.', 'icon' => 'fa fa-user-check', 'is_svg' => false],
        ['id' => 'form_penilaian', 'title' => 'Form Penilaian', 'desc' => 'Form penilaian untuk anda.', 'icon' => 'fa fa-file-pen', 'is_svg' => false],
    ],
    'education' => [
        ['id' => 'sertifikasi_pelatihan', 'title' => 'Sertifikasi & Pelatihan', 'desc' => 'untuk menejemen sertifikat dan Pelatihan Instruktur.', 'icon' => 'book.svg', 'is_svg' => true],
        ['id' => 'tunjangan_education', 'title' => 'Tunjangan Education', 'desc' => 'Data Tunjangan Education.', 'icon' => 'layout-freeform.svg', 'is_svg' => true],
        ['id' => 'pengajuan_lab', 'title' => 'Pengajuan Lab', 'desc' => 'pengajuan dan manajemen labs', 'icon' => 'fa-solid fa-flask', 'is_svg' => false],
        ['id' => 'cv_instruktur', 'title' => 'CV Instruktur', 'desc' => 'untuk melihat dan export cv instruktur.', 'icon' => 'fa-solid fa-file-lines', 'is_svg' => false],
        ['id' => 'rekap_mengajar', 'title' => 'Rekap Mengajar Instruktur', 'desc' => 'Data rekapan mengajar instruktur.', 'icon' => 'target.svg', 'is_svg' => true],
        ['id' => 'klaim_modul', 'title' => 'Klaim Modul', 'desc' => 'Klaim pembuatan/pengajuan modul.', 'icon' => 'edit.svg', 'is_svg' => true],
        ['id' => 'rekomendasi_lanjutan', 'title' => 'Rekomendasi Training Lanjutan', 'desc' => 'rekomendasi untuk peserta.', 'icon' => 'trending-up.svg', 'is_svg' => true],
        ['id' => 'activity_report', 'title' => 'Activity Report', 'desc' => 'Activity Report Instruktur.', 'icon' => 'activity.svg', 'is_svg' => true],
    ],
    'office' => [
        ['id' => 'dashboard_office', 'title' => 'Dashboard Office', 'desc' => 'Dashboard Office Inixindo.', 'icon' => 'layout-dashboard.svg', 'is_svg' => true],
        ['id' => 'inix_hr', 'title' => 'INIX HR', 'desc' => 'Arsip dan trend data perkembangan perusahaan.', 'icon' => 'pie-chart.svg', 'is_svg' => true],
        ['id' => 'new_hire', 'title' => 'New Hire', 'desc' => 'Data pelamar baru dan jadwal rekrut.', 'icon' => 'user-plus.svg', 'is_svg' => true],
        ['id' => 'struktur_organisasi', 'title' => 'Struktur Organisasi', 'desc' => 'Lihat struktur organisasi perusahaan', 'icon' => 'sliders.svg', 'is_svg' => true],
        ['id' => 'inventaris', 'title' => 'Inventaris', 'desc' => 'Data Inventaris Inixindo.', 'icon' => 'box.svg', 'is_svg' => true],
        ['id' => 'pengajuan_klaim', 'title' => 'Pengajuan Klaim', 'desc' => 'Pengajuan Absen, Jam Kerja, & Cuti', 'icon' => 'file-minus.svg', 'is_svg' => true],
        ['id' => 'pengajuan_catering', 'title' => 'Pengajuan Catering', 'desc' => 'Pengajuan Catering', 'icon' => 'truck.svg', 'is_svg' => true],
        ['id' => 'rencana_pembelian', 'title' => 'Rencana Pembelian', 'desc' => 'Pengajuan Rencana Pembelian.', 'icon' => 'fa-solid fa-cart-plus', 'is_svg' => false],
    ],
    'crm' => [
        ['id' => 'fitur_crm', 'title' => 'Fitur CRM', 'desc' => 'Masuk Fitur CRM', 'icon' => 'contact.svg', 'is_svg' => true],
        ['id' => 'pengajuan_diluar_pa', 'title' => 'Pengajuan Diluar PA', 'desc' => 'Pengajuan entertaint, reimburst, dan oleh-oleh.', 'icon' => 'fa-solid fa-basket-shopping', 'is_svg' => false],
    ],
    'management' => [
        ['id' => 'set_target', 'title' => 'Set Target', 'desc' => 'Manajemen Target.', 'icon' => 'crosshair.svg', 'is_svg' => true],
    ],
    'project' => [
        ['id' => 'administrasi', 'title' => 'Administrasi', 'desc' => 'Fitur Administrasi Projek yang akan dilakukan.', 'icon' => 'folder.svg', 'is_svg' => true],
        ['id' => 'lead_projek', 'title' => 'Lead Projek', 'desc' => 'Fitur Lead Projek yang akan dilakukan.', 'icon' => 'layout.svg', 'is_svg' => true],
        ['id' => 'kanban_project', 'title' => 'Kanban', 'desc' => 'Kanban Teknis untuk Projek.', 'icon' => 'grid-3x3.svg', 'is_svg' => true],
        ['id' => 'laporan_penjualan_project', 'title' => 'Laporan Penjualan Projek', 'desc' => 'Dashboard Penjualan Projek.', 'icon' => 'chart-no-axes-column.svg', 'is_svg' => true],
        ['id' => 'aktivitas_visit_projek', 'title' => 'Aktivitas Visit Projek', 'desc' => 'Aktivitas Visit Projek.', 'icon' => 'visit.svg', 'is_svg' => true],
    ],
];

// Urutan default bawaan (factory) sebelum ditimpa layout dari database.
$defaultSectionCards = $sectionCards;

// Urutan kartu dari tabel global (dashboard_layouts)
foreach ($layouts as $l) {
    $secKey = $l->section_key;
    $cOrder = $l->card_order;
    if (is_string($cOrder)) {
        $cOrder = json_decode($cOrder, true);
    }
    if (!empty($cOrder) && is_array($cOrder) && isset($sectionCards[$secKey])) {
        $ordered = [];
        $existingMap = [];
        foreach ($sectionCards[$secKey] as $c) {
            $existingMap[$c['id']] = $c;
        }
        foreach ($cOrder as $cId) {
            if (isset($existingMap[$cId])) {
                $ordered[] = $existingMap[$cId];
                unset($existingMap[$cId]);
            }
        }
        foreach ($existingMap as $c) {
            $ordered[] = $c;
        }
        $sectionCards[$secKey] = $ordered;
    }
}

$defaultCardOrdersMap = [];
foreach ($defaultSectionCards as $sKey => $cards) {
    $defaultCardOrdersMap[$sKey] = array_map(fn($c) => $c['id'], $cards);
}

$totalSections = count($layouts);
@endphp

<!-- Font Awesome 6 & Google Font Outfit -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="layout-setting-wrapper py-4 px-3 px-md-4">
    <div class="container-fluid" style="max-width: 1440px;">

        <!-- Premium Header Banner -->
            <div class="header-banner card border-0 shadow-lg mb-4 overflow-hidden position-relative">
                <div class="header-glow"></div>
                <div class="card-body p-4 p-md-5 position-relative z-1">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-7">

                            <h2 class="fw-bold text-white mb-2 tracking-tight display-6"
                                style="font-family: 'Outfit', sans-serif;">
                                Setting Layout Dashboard
                            </h2>
                            <p class="text-white-50 mb-0 lead-text"
                                style="font-size: 0.95rem; line-height: 1.6; max-width: 680px;">
                                Pilih nomor urutan langsung via <strong class="text-white">Dropdown</strong>, tombol <strong
                                    class="text-white">Panah Cepat</strong>, atau <strong class="text-white">Drag &
                                    Drop</strong>. Urutan ini menjadi urutan global yang otomatis disaring sesuai permission
                                setiap user. Pilih <strong class="text-white">Filter Role</strong> untuk mengatur urutan khusus satu role saja.
                            </p>
                        </div>

                        <div class="col-lg-5 text-lg-end">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center header-action-group">
                                <a href="{{ route('home') }}"
                                    class="btn btn-outline-light px-3 py-2.5 rounded-pill fw-semibold shadow-sm action-btn">
                                    <i class="fa-solid fa-arrow-left me-1.5"></i> Dashboard
                                </a>
                                <button type="button"
                                    class="btn btn-outline-light px-3 py-2.5 rounded-pill fw-semibold shadow-sm action-btn"
                                    data-bs-toggle="modal" data-bs-target="#previewModal">
                                    <i class="fa-solid fa-eye me-1.5"></i> Preview
                                </button>
                                <button type="button"
                                    class="btn btn-outline-light px-3 py-2.5 rounded-pill fw-semibold shadow-sm action-btn btn-reset-action"
                                    id="header-reset-btn">
                                    <i class="fa-solid fa-rotate-left me-1.5"></i> <span id="header-reset-btn-text">Reset
                                        Default</span>
                                </button>
                                <button type="button"
                                    class="btn btn-outline-light px-3 py-2.5 rounded-pill fw-semibold shadow-sm action-btn btn-save-action"
                                    id="main-save-btn">
                                    <i class="fa-solid fa-cloud-arrow-up me-1.5"></i> Simpan Urutan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- VIEW 1: Grid Section (Membawahi Tampilan Grid & Tampilan Tabel dengan Filter Role Dinamis) -->
        <div id="grid-view-container">

            <!-- Sub-Control Toolbar: Filter Role Dinamis & Switcher Tampilan -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3 p-md-3.5">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

                        <!-- Kiri: Filter Role Dinamis -->
                        <div class="d-flex align-items-center flex-wrap gap-2 role-filter-container">
                            <label for="role-filter-select"
                                class="d-flex align-items-center gap-2 mb-0 fw-bold text-dark small flex-shrink-0">
                                
                                <span>Filter Role:</span>
                            </label>
                            <div class="role-filter-box" style="min-width: 220px; max-width: 280px;">
                                <select
                                    class="form-select form-select-sm rounded-pill fw-semibold border-secondary-subtle w-100"
                                    id="role-filter-select" onchange="handleRoleFilterChange(this.value)">
                                    <option value="all">Semua Role / Global</option>
                                    @if(isset($allRoleList) && count($allRoleList) > 0)
                                    <optgroup label="Pilih Jabatan / Role">
                                        @foreach($allRoleList as $roleOption)
                                        <option value="{{ $roleOption }}">👤 {{ $roleOption }}</option>
                                        @endforeach
                                    </optgroup>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Kanan: Switcher Tampilan & Counter -->
                        <div class="d-flex align-items-center justify-content-md-end flex-wrap gap-2 toolbar-right-group">
                            <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 small fw-semibold"
                                id="role-filter-counter-badge">
                                <i class="fa-solid fa-layer-group text-primary me-1"></i> Menampilkan <span
                                    id="visible-sections-count">{{ $totalSections }}</span>/{{ $totalSections }} Section
                                (Global)
                            </span>

                            <div class="btn-group p-1 bg-light rounded-pill border view-switcher-group" role="group"
                                aria-label="Tampilan Section">
                                <button type="button"
                                    class="btn btn-sm rounded-pill px-3 py-1 fw-bold active subview-toggle-btn"
                                    id="btn-subview-grid" onclick="switchSectionDisplay('grid')"
                                    title="Tampilkan Section dalam bentuk Grid">
                                    <i class="fa-solid fa-grip me-1 text-primary"></i> <span>Grid Section</span>
                                </button>
                                <button type="button"
                                    class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary subview-toggle-btn"
                                    id="btn-subview-table" onclick="switchSectionDisplay('table')"
                                    title="Tampilkan Section dalam bentuk Tabel">
                                    <i class="fa-solid fa-list-check me-1"></i> <span>Tabel Section</span>
                                </button>
                                <button type="button"
                                    class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary subview-toggle-btn"
                                    id="btn-subview-cards" onclick="switchSectionDisplay('cards')"
                                    title="Atur Kartu dalam Setiap Section">
                                    <i class="fa-solid fa-layer-group me-1"></i> <span>Detail Card</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Alert info ketika filter role aktif -->
                    <div id="role-filter-alert" class="d-none mt-3 pt-2.5 border-top">
                        <div class="d-flex align-items-center gap-2 text-secondary small">
                            <i class="fa-solid fa-circle-info text-primary fs-6 flex-shrink-0"></i>
                            <span>Mengatur layout khusus role: <strong class="text-primary fw-bold"
                                    id="selected-role-name">-</strong>. Perubahan, simpan, dan reset hanya berlaku untuk role ini.
                                Section tanpa izin otomatis disembunyikan.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUB-VIEW 1: Mode Grid Section (Default) -->
            <div id="section-subview-grid">
                <div class="row g-3" id="sortable-list">
                    @foreach($layouts as $index => $layout)
                    @php
                    $meta = $sectionMeta[$layout->section_key] ?? [
                        'name' => ucfirst($layout->section_key),
                        'icon' => 'users.svg',
                        'color' => '#475569',
                        'desc' => 'Section ' . $layout->section_key,
                        'tags' => ['Fitur ' . ucfirst($layout->section_key)],
                    ];
                    $posNumber = $index + 1;
                    $isLeft = ($posNumber % 2 !== 0);
                    @endphp
                    <div class="col-12 col-md-6 sortable-item" data-id="{{ $layout->section_key }}"
                        data-key="{{ $layout->section_key }}">
                        <div class="card h-100 shadow-sm border-0 section-card position-relative"
                            style="--accent-color: {{ $meta['color'] }}; border: 2px solid {{ $isLeft ? '#2563eb' : '#059669' }} !important;">

                            <!-- Top Accent Line -->
                            <div class="card-accent-strip"
                                style="background-color: {{ $isLeft ? '#2563eb' : '#059669' }};">
                            </div>

                            <div class="card-body p-3.5 d-flex align-items-start gap-3 section-card-body">

                                <!-- Left Block: Drag Handle + Number Badge + Icon -->
                                <div class="d-flex align-items-center gap-2.5 flex-shrink-0 pt-0.5 section-card-left">
                                    <div class="drag-handle" title="Tahan dan geser kartu">
                                        <i class="fa-solid fa-grip-vertical"></i>
                                    </div>

                                    <div class="position-badge-box text-center">
                                        <span class="order-badge badge rounded-pill px-2 py-1 text-white fw-bold"
                                            style="background-color: {{ $isLeft ? '#2563eb' : '#059669' }}; font-size: 0.85rem; min-width: 34px;">
                                            #{{ $posNumber }}
                                        </span>
                                        <span
                                            class="column-indicator badge {{ $isLeft ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-success-subtle text-success border-success-subtle' }} rounded-pill px-1.5 py-0.5 mt-1 d-block border"
                                            style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px;">
                                            {{ $isLeft ? 'KIRI' : 'KANAN' }}
                                        </span>
                                    </div>

                                    <div class="category-icon-box shadow-xs"
                                        style="background-color: {{ $meta['color'] }}18; border: 1px solid {{ $meta['color'] }}30;">
                                        <img src="{{ asset('icon/' . $meta['icon']) }}" alt="{{ $meta['name'] }}"
                                            width="24" height="24" style="object-fit: contain;">
                                    </div>
                                </div>

                                <!-- Middle Block: Title + Description + Tags -->
                                <div class="flex-grow-1 overflow-hidden section-card-middle">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate section-title"
                                            style="font-family: 'Outfit', sans-serif; font-size: 0.98rem;">
                                            {{ $meta['name'] }}
                                        </h6>
                                    </div>
                                    <p class="text-secondary small mb-2 text-truncate-2"
                                        style="font-size: 0.82rem; line-height: 1.45;">
                                        {{ $meta['desc'] }}
                                    </p>

                                    <div class="d-flex flex-wrap gap-1.5">
                                        @foreach($meta['tags'] as $tag)
                                        <span class="feature-tag">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Right Block: Direct Position Dropdown & Stepper Buttons -->
                                <div
                                    class="d-flex flex-column align-items-end gap-1.5 flex-shrink-0 ms-1 control-actions section-card-right">

                                    <div class="position-select-wrapper" title="Ubah urutan langsung">
                                        <select class="form-select form-select-sm position-select"
                                            data-id="{{ $layout->section_key }}">
                                            @for($i = 1; $i <= $totalSections; $i++)
                                            <option value="{{ $i }}" {{ $posNumber == $i ? 'selected' : '' }}>Urutan #{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div
                                        class="btn-group btn-group-sm rounded-pill border shadow-2xs overflow-hidden bg-white quick-stepper">
                                        <button type="button"
                                            class="btn btn-light btn-move-top py-0.5 px-2 text-primary"
                                            title="Pindah ke Paling Atas (#1)">
                                            <i class="fa-solid fa-angles-up" style="font-size: 0.68rem;"></i>
                                        </button>
                                        <button type="button" class="btn btn-light btn-move-up py-0.5 px-2"
                                            title="Naik 1 Tingkat">
                                            <i class="fa-solid fa-chevron-up" style="font-size: 0.68rem;"></i>
                                        </button>
                                        <button type="button" class="btn btn-light btn-move-down py-0.5 px-2"
                                            title="Turun 1 Tingkat">
                                            <i class="fa-solid fa-chevron-down" style="font-size: 0.68rem;"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-light btn-move-bottom py-0.5 px-2 text-danger"
                                            title="Pindah ke Paling Bawah">
                                            <i class="fa-solid fa-angles-down" style="font-size: 0.68rem;"></i>
                                        </button>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div><!-- End #section-subview-grid -->

            <!-- SUB-VIEW 2: Mode Tabel Section -->
            <div id="table-view-container" class="d-none">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="table-responsive w-100">
                        <table class="table table-hover align-middle mb-0 w-100" id="sortable-table"
                            style="width: 100% !important; min-width: 100% !important;">
                            <thead class="bg-light text-secondary small text-uppercase"
                                style="font-size: 0.78rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="text-center py-3" style="width: 50px;">Grip</th>
                                    <th class="text-center py-3" style="width: 80px;">Urutan</th>
                                    <th class="py-3" style="width: 120px;">Distribusi</th>
                                    <th class="py-3" style="width: 22%; min-width: 180px;">Section Dashboard</th>
                                    <th class="py-3 d-none d-md-table-cell">Deskripsi & Fitur</th>
                                    <th class="text-center py-3" style="width: 150px;">Pilih Posisi</th>
                                    <th class="text-end py-3 pe-4" style="width: 160px;">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody id="table-sortable-body">
                                @foreach($layouts as $index => $layout)
                                @php
                                $meta = $sectionMeta[$layout->section_key] ?? [
                                    'name' => ucfirst($layout->section_key),
                                    'icon' => 'users.svg',
                                    'color' => '#475569',
                                    'desc' => 'Section ' . $layout->section_key,
                                    'tags' => ['Fitur ' . ucfirst($layout->section_key)],
                                ];
                                $posNumber = $index + 1;
                                $isLeft = ($posNumber % 2 !== 0);
                                @endphp
                                <tr class="table-sortable-row" data-id="{{ $layout->section_key }}"
                                    data-key="{{ $layout->section_key }}">
                                    <td class="text-center">
                                        <div class="drag-handle text-muted cursor-grab" title="Geser baris">
                                            <i class="fa-solid fa-grip-vertical"></i>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="order-badge badge rounded-pill text-white fw-bold px-2.5 py-1.5"
                                            style="background-color: {{ $isLeft ? '#2563eb' : '#059669' }}; font-size: 0.85rem; min-width: 36px;">
                                            #{{ $posNumber }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="column-indicator badge {{ $isLeft ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-success-subtle text-success border-success-subtle' }} rounded-pill px-2 py-1 border"
                                            style="font-size: 0.72rem; font-weight: 700;">
                                            {{ $isLeft ? 'Kolom Kiri' : 'Kolom Kanan' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="category-icon-box flex-shrink-0"
                                                style="width: 36px; height: 36px; background-color: {{ $meta['color'] }}18; border: 1px solid {{ $meta['color'] }}30; border-radius: 10px;">
                                                <img src="{{ asset('icon/' . $meta['icon']) }}"
                                                    alt="{{ $meta['name'] }}" width="20" height="20"
                                                    style="object-fit: contain;">
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.93rem;">{{ $meta['name'] }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <p class="text-muted small mb-1 text-truncate" style="max-width: 380px;">{{ $meta['desc'] }}</p>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(array_slice($meta['tags'], 0, 3) as $tag)
                                            <span class="feature-tag" style="font-size: 0.68rem; padding: 1px 6px;">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <select class="form-select form-select-sm position-select mx-auto"
                                            style="max-width: 130px;" data-id="{{ $layout->section_key }}">
                                            @for($i = 1; $i <= $totalSections; $i++)
                                            <option value="{{ $i }}" {{ $posNumber == $i ? 'selected' : '' }}>Urutan #{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div
                                            class="btn-group btn-group-sm rounded-pill border shadow-2xs overflow-hidden bg-white">
                                            <button type="button"
                                                class="btn btn-light btn-move-top py-1 px-2 text-primary"
                                                title="Ke Paling Atas">
                                                <i class="fa-solid fa-angles-up" style="font-size: 0.7rem;"></i>
                                            </button>
                                            <button type="button" class="btn btn-light btn-move-up py-1 px-2"
                                                title="Naik">
                                                <i class="fa-solid fa-chevron-up" style="font-size: 0.7rem;"></i>
                                            </button>
                                            <button type="button" class="btn btn-light btn-move-down py-1 px-2"
                                                title="Turun">
                                                <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem;"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-light btn-move-bottom py-1 px-2 text-danger"
                                                title="Ke Paling Bawah">
                                                <i class="fa-solid fa-angles-down" style="font-size: 0.7rem;"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- End #table-view-container -->

            <!-- SUB-VIEW 3: Detail Card Mode -->
            <div id="cards-view-container" class="cards-subview-wrapper d-none">

                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary rounded-pill px-2.5 py-1">Mode Live Edit Card</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold"
                                id="cards-active-role-badge">
                                <i class="fa-solid fa-user-shield me-1"></i> Filter: <span id="cards-role-name">Semua Role (Global)</span>
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small fw-semibold"
                                id="cards-section-count-badge">
                                Menampilkan {{ $totalSections }}/{{ $totalSections }} Section
                            </span>
                        </div>
                        <div class="text-muted small">
                            <i class="fa-solid fa-arrows-up-down-left-right text-primary me-1"></i> Atur urutan card via <strong>Drag & Drop</strong>, <strong>Dropdown Posisi</strong>, atau <strong>Tombol Panah</strong>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="cards-empty-state" class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-4 text-center d-none">
                    <div class="py-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3"
                            style="width: 56px; height: 56px;">
                            <i class="fa-solid fa-shield-halved fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Tidak Ada Section untuk Role Ini</h5>
                        <p class="text-secondary small mb-3">Role <strong class="text-primary empty-role-name">-</strong> belum memiliki hak akses ke modul/section dashboard manapun.</p>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5" onclick="resetRoleFilter()">
                            <i class="fa-solid fa-rotate-left me-1"></i> Kembali ke Semua Role (Global)
                        </button>
                    </div>
                </div>

                <div class="row g-4" id="cards-sections-container">
                    @foreach($layouts as $index => $layout)
                    @php
                    $pos = $index + 1;
                    $sKey = $layout->section_key;
                    $sMeta = $sectionMeta[$sKey] ?? [
                        'name' => ucfirst($sKey),
                        'icon' => 'user.svg',
                        'color' => '#2563eb',
                        'desc' => 'Section ' . $sKey
                    ];
                    $cards = $sectionCards[$sKey] ?? [];
                    $totalSectionCards = count($cards);
                    $isLeft = ($pos % 2 !== 0);
                    $accentColor = $isLeft ? '#2563eb' : '#059669';
                    @endphp
                    <div class="col-12 col-lg-6 dashboard-section-wrapper" data-section="{{ $sKey }}">
                        <div class="dashboard-section-box card border-0 shadow-sm h-100" data-section="{{ $sKey }}"
                            style="border-radius: 18px; background: linear-gradient(180deg, rgba(255,255,255,0.7) 0%, rgba(240,245,255,0.5) 100%); backdrop-filter: blur(4px); border: 2px solid {{ $accentColor }} !important;">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                            style="width: 28px; height: 28px; background-color: {{ $sMeta['color'] }}18; color: {{ $sMeta['color'] }};">
                                            <i class="fa-solid fa-layer-group" style="font-size: 0.8rem;"></i>
                                        </span>
                                        <h5 class="card-title fw-bold mb-0"
                                            style="color: #182F51; font-size: 1.1rem; font-family: 'Outfit', sans-serif;">
                                            {{ $sMeta['name'] }}
                                        </h5>
                                        <span class="badge rounded-pill bg-light text-secondary border small section-card-count-badge">{{ $totalSectionCards }} Kartu</span>
                                    </div>
                                    <span class="badge rounded-pill text-white fw-bold px-2.5 py-1 section-pos-badge"
                                        style="background-color: {{ $accentColor }}; font-size: 0.72rem;">
                                        Section #{{ $pos }} ({{ $isLeft ? 'Kiri' : 'Kanan' }})
                                    </span>
                                </div>
                                <div class="row card-sortable-list" data-section="{{ $sKey }}">
                                    @foreach($cards as $cIndex => $card)
                                    @php
                                    $cardPos = $cIndex + 1;
                                    @endphp
                                    <div class="col-12 col-sm-6 mt-2 card-sortable-item" data-id="{{ $card['id'] }}"
                                        data-section="{{ $sKey }}">
                                        <div class="card dashboard-card-item h-100 position-relative"
                                            style="border-radius: 16px; background: #ffffff; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 14px rgba(24,47,81,0.05); transition: all 0.2s ease;">
                                            <div class="card-body d-flex align-items-center justify-content-between p-2 p-sm-2.5"
                                                style="min-width: 0;">

                                                <!-- Left: Icon & Text Info -->
                                                <div class="d-flex align-items-center flex-grow-1 overflow-hidden me-1 me-sm-2 card-info-left"
                                                    style="min-width: 0;">
                                                    <div class="drag-handle-card text-muted flex-shrink-0 me-1.5"
                                                        style="cursor: grab;" title="Tahan dan geser untuk memindahkan urutan">
                                                        <i class="fa-solid fa-grip-vertical"></i>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                                                        style="width: 30px;">
                                                        @if(isset($card['is_svg']) && $card['is_svg'])
                                                        <img src="{{ asset('icon/' . $card['icon']) }}"
                                                            class="img-responsive" width="22px" alt="{{ $card['title'] }}">
                                                        @else
                                                        <i class="{{ $card['icon'] }}"
                                                            style="font-size: 19px; color: #182F51;"></i>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden"
                                                        style="margin-left: 8px; min-width: 0;">
                                                        <div class="d-flex align-items-center gap-1.5 mb-0.5">
                                                            <span class="badge bg-light text-secondary border rounded-pill card-order-badge px-1.5 py-0.5"
                                                                style="font-size: 0.68rem;">#{{ $cardPos }}</span>
                                                            <h5 class="card-title fw-bold text-truncate mb-0"
                                                                style="color: #182F51; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"
                                                                title="{{ $card['title'] }}">
                                                                {{ $card['title'] }}
                                                            </h5>
                                                        </div>
                                                        <p class="card-text text-secondary small mb-0 text-truncate"
                                                            style="font-size: 0.74rem; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"
                                                            title="{{ $card['desc'] }}">
                                                            {{ $card['desc'] }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Right: Controls -->
                                                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0 card-controls-col"
                                                    style="width: 105px;">
                                                    <select
                                                        class="form-select form-select-sm card-position-select text-center fw-bold text-dark w-100"
                                                        data-section="{{ $sKey }}" data-id="{{ $card['id'] }}"
                                                        title="Pilih urutan kartu">
                                                        @for($k = 1; $k <= $totalSectionCards; $k++)
                                                        <option value="{{ $k }}" {{ $cardPos == $k ? 'selected' : '' }}>Urutan #{{ $k }}</option>
                                                        @endfor
                                                    </select>

                                                    <div class="d-flex align-items-center justify-content-around w-100 bg-white border card-stepper-pill"
                                                        style="border-color: #cbd5e1 !important; border-radius: 50rem; height: 26px; padding: 0 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                                        <button type="button"
                                                            class="btn btn-sm btn-link p-0 text-primary btn-card-move-top text-decoration-none d-flex align-items-center justify-content-center"
                                                            title="Pindah ke Paling Atas" style="width: 20px; height: 20px;">
                                                            <i class="fa-solid fa-angles-up" style="font-size: 0.7rem; color: #2563eb;"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-link p-0 text-dark btn-card-move-up text-decoration-none d-flex align-items-center justify-content-center"
                                                            title="Naik 1 Tingkat" style="width: 20px; height: 20px;">
                                                            <i class="fa-solid fa-chevron-up" style="font-size: 0.7rem; color: #1e293b;"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-link p-0 text-dark btn-card-move-down text-decoration-none d-flex align-items-center justify-content-center"
                                                            title="Turun 1 Tingkat" style="width: 20px; height: 20px;">
                                                            <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; color: #1e293b;"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-link p-0 text-danger btn-card-move-bottom text-decoration-none d-flex align-items-center justify-content-center"
                                                            title="Pindah ke Paling Bawah" style="width: 20px; height: 20px;">
                                                            <i class="fa-solid fa-angles-down" style="font-size: 0.7rem; color: #ef4444;"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div><!-- End #cards-view-container -->
        </div><!-- End #grid-view-container -->

        <!-- Sticky Floating Save Bar -->
        <div class="sticky-save-bar" id="sticky-save-bar">
            <div
                class="d-flex align-items-center justify-content-between gap-3 px-4 py-3 bg-dark bg-opacity-95 text-white rounded-pill shadow-2xl border border-white border-opacity-20 backdrop-blur">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="pulse-dot" id="sticky-pulse-dot"></span>
                    <div>
                        <div class="fw-bold small text-white" id="sticky-title">Urutan Berubah</div>
                        <div class="text-white-50" id="sticky-desc" style="font-size: 0.75rem;">Klik Simpan untuk
                            menerapkan tata letak.
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="undo-redo-group" role="group" aria-label="Undo Redo Urutan">
                        <button type="button" class="btn-undo-action" id="btn-undo-action"
                            title="Kembalikan Urutan Sebelumnya (Undo) [Ctrl+Z]" disabled>
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <button type="button" class="btn-redo-action" id="btn-redo-action"
                            title="Maju ke Urutan Berikutnya (Redo) [Ctrl+Y]" disabled>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>

                    <button type="button"
                        class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold btn-save-action">
                        <i class="fa-solid fa-check me-1"></i> Simpan Sekarang
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Live Preview Dashboard -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-xl overflow-hidden">
            <div class="modal-header bg-dark text-white p-4 border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="previewModalLabel">
                        Simulasi Tampilan Dashboard Home
                    </h5>
                    <p class="text-white-50 small mb-0">
                        Ini adalah gambaran nyata bagaimana tersusun di layar user (Kiri & Kanan).
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-3" id="preview-columns-container">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <span class="badge bg-primary px-2.5 py-1 rounded-pill">KOLOM KIRI</span>
                            <span class="text-muted small fw-medium">Section urutan ganjil (#1, #3, #5, #7, #9, #11)</span>
                        </div>
                        <div class="d-flex flex-column gap-3" id="preview-left-column"></div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <span class="badge bg-success px-2.5 py-1 rounded-pill">KOLOM KANAN</span>
                            <span class="text-muted small fw-medium">Section urutan genap (#2, #4, #6, #8, #10)</span>
                        </div>
                        <div class="d-flex flex-column gap-3" id="preview-right-column"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
                <span class="text-muted small">
                    <i class="fa-solid fa-info-circle me-1"></i> User yang tidak memiliki permission suatu modul
                    otomatis dilewati tanpa menyisakan celah kosong.
                </span>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup
                    Preview</button>
            </div>
        </div>
    </div>
</div>


@push('styles')
    @vite(['resources/css/pages/layout-setting.css'])
@endpush

@push('scripts')
    <!-- SortableJS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        window.layoutSettingConfig = {
            sectionMeta: @json($sectionMeta),
            rolePermissionsMap: @json($rolePermissionsMap ?? []),
            defaultCardOrdersMap: @json($defaultCardOrdersMap ?? []),
            layoutStateMap: @json($layoutStateMap ?? []),
            urls: {
                reset: "{{ route('admin.layout-setting.reset') }}",
                update: "{{ route('admin.layout-setting.update') }}"
            }
        };
    </script>
    @vite(['resources/js/pages/layout-setting.js'])
@endpush
@endsection
