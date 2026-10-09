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
        ['id' => 'pengajuan_subs', 'title' => 'Pengajuan Subs', 'desc' => 'pengajuan dan manajemen subs', 'icon' => 'fa-solid fa-server', 'is_svg' => false],
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
                        <div
                            class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-10 text-black small mb-2 border border-white border-opacity-15">
                            <span class="status-indicator bg-success"></span>
                            <span class="fw-semibold text-black">GLOBAL DASHBOARD CONFIGURATOR</span>
                        </div>
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
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
                            <a href="{{ route('home') }}"
                                class="btn btn-outline-light px-3 py-2.5 rounded-pill fw-semibold shadow-sm action-btn">
                                <i class="fa-solid fa-arrow-left me-1.5"></i> Dashboard
                            </a>
                            <button type="button"
                                class="btn btn-outline-info px-3 py-2.5 fw-semibold rounded-pill shadow-sm action-btn"
                                data-bs-toggle="modal" data-bs-target="#previewModal">
                                <i class="fa-solid fa-eye me-1.5"></i> Preview
                            </button>
                            <button type="button"
                                class="btn btn-outline-warning px-3 py-2.5 fw-semibold rounded-pill shadow-sm action-btn btn-reset-action"
                                id="header-reset-btn">
                                <i class="fa-solid fa-rotate-left me-1.5"></i> <span id="header-reset-btn-text">Reset
                                    Default</span>
                            </button>
                            <button type="button"
                                class="btn btn-primary px-4 py-2.5 fw-bold rounded-pill shadow-md action-btn btn-save-action"
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
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <label for="role-filter-select"
                                class="d-flex align-items-center gap-2 mb-0 fw-bold text-dark small flex-shrink-0">
                                <span
                                    class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle"
                                    style="width: 28px; height: 28px;">
                                    <i class="fa-solid fa-user-shield" style="font-size: 0.85rem;"></i>
                                </span>
                                <span>Filter Role:</span>
                            </label>
                            <div style="min-width: 220px; max-width: 280px;">
                                <select
                                    class="form-select form-select-sm rounded-pill fw-semibold border-secondary-subtle"
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
                            {{-- <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 d-none"
                                id="btn-reset-role-filter" onclick="executeResetDefault()"
                                title="Reset default urutan untuk role ini">
                                <i class="fa-solid fa-rotate-left me-1"></i> <span id="role-reset-btn-text">Reset
                                    Default</span>
                            </button> --}}
                        </div>

                        <!-- Kanan: Switcher Tampilan & Counter -->
                        <div class="d-flex align-items-center justify-content-md-end flex-wrap gap-2">
                            <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 small fw-semibold"
                                id="role-filter-counter-badge">
                                <i class="fa-solid fa-layer-group text-primary me-1"></i> Menampilkan <span
                                    id="visible-sections-count">{{ $totalSections }}</span>/{{ $totalSections }} Section
                                (Global)
                            </span>

                            <div class="btn-group p-1 bg-light rounded-pill border" role="group"
                                aria-label="Tampilan Section">
                                <button type="button"
                                    class="btn btn-sm rounded-pill px-3 py-1 fw-bold active subview-toggle-btn"
                                    id="btn-subview-grid" onclick="switchSectionDisplay('grid')"
                                    title="Tampilkan Section dalam bentuk Grid">
                                    <i class="fa-solid fa-grip me-1 text-primary"></i> Grid Section
                                </button>
                                <button type="button"
                                    class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary subview-toggle-btn"
                                    id="btn-subview-table" onclick="switchSectionDisplay('table')"
                                    title="Tampilkan Section dalam bentuk Tabel">
                                    <i class="fa-solid fa-list-check me-1"></i> Tabel Section
                                </button>
                                <button type="button"
                                    class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary subview-toggle-btn"
                                    id="btn-subview-cards" onclick="switchSectionDisplay('cards')"
                                    title="Atur Kartu dalam Setiap Section">
                                    <i class="fa-solid fa-layer-group me-1"></i> Detail Card
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

                            <div class="card-body p-3.5 d-flex align-items-start gap-3">

                                <!-- Left Block: Drag Handle + Number Badge + Icon -->
                                <div class="d-flex align-items-center gap-2.5 flex-shrink-0 pt-0.5">
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
                                <div class="flex-grow-1 overflow-hidden">
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
                                    class="d-flex flex-column align-items-end gap-1.5 flex-shrink-0 ms-1 control-actions">

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
                                        <span class="badge rounded-pill bg-light text-secondary border small">{{ $totalSectionCards }} Kartu</span>
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
                                    <div class="col-sm-6 mt-2 card-sortable-item" data-id="{{ $card['id'] }}"
                                        data-section="{{ $sKey }}">
                                        <div class="card dashboard-card-item h-100 position-relative"
                                            style="border-radius: 16px; background: #ffffff; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 14px rgba(24,47,81,0.05); transition: all 0.2s ease;">
                                            <div class="card-body d-flex align-items-center justify-content-between p-2.5"
                                                style="min-width: 0;">

                                                <!-- Left: Icon & Text Info -->
                                                <div class="d-flex align-items-center flex-grow-1 overflow-hidden me-2"
                                                    style="min-width: 0;">
                                                    <div class="drag-handle-card text-muted flex-shrink-0 me-2"
                                                        style="cursor: grab;" title="Tahan dan geser untuk memindahkan urutan">
                                                        <i class="fa-solid fa-grip-vertical"></i>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                                                        style="width: 32px;">
                                                        @if(isset($card['is_svg']) && $card['is_svg'])
                                                        <img src="{{ asset('icon/' . $card['icon']) }}"
                                                            class="img-responsive" width="24px" alt="{{ $card['title'] }}">
                                                        @else
                                                        <i class="{{ $card['icon'] }}"
                                                            style="font-size: 20px; color: #182F51;"></i>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden"
                                                        style="margin-left: 10px; min-width: 0;">
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
                                                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0"
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

<style>
    body {
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: #f8fafc;
    }

    .layout-setting-wrapper {
        min-height: calc(100vh - 60px);
    }

    /* Undo & Redo */
    .undo-redo-group {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    .undo-redo-group button {
        width: 36px;
        height: 36px;
        padding: 0;
        border: none;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #64748b;
        color: #ffffff;
        transition: none !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .undo-redo-group button:hover,
    .undo-redo-group button:focus,
    .undo-redo-group button:active {
        background: #64748b !important;
        color: #ffffff !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .undo-redo-group button:disabled {
        opacity: 1 !important;
        background: #64748b !important;
        color: #ffffff !important;
        cursor: not-allowed;
    }

    /* Header Banner */
    .header-banner {
        border-radius: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f172a 100%);
        box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.35) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .header-glow {
        position: absolute;
        width: 320px;
        height: 320px;
        top: -100px;
        right: -80px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.28) 0%, rgba(147, 51, 234, 0.12) 60%, transparent 80%);
        filter: blur(40px);
        pointer-events: none;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px rgba(34, 197, 94, 0.8);
    }

    .action-btn {
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    /* Sub-view Switcher */
    .subview-toggle-btn {
        border: none;
        transition: all 0.2s ease;
    }

    .subview-toggle-btn.active {
        background-color: #0f172a !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
    }

    /* Section Cards */
    .section-card {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.05) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        overflow: hidden;
        user-select: none;
    }

    .card-accent-strip {
        height: 3.5px;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
    }

    .section-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px -4px rgba(15, 23, 42, 0.12) !important;
        border-color: var(--accent-color) !important;
    }

    .drag-handle {
        color: #94a3b8;
        font-size: 1.15rem;
        cursor: grab;
        padding: 4px;
        transition: color 0.15s ease, transform 0.15s ease;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .section-card:hover .drag-handle {
        color: var(--accent-color);
        transform: scale(1.1);
    }

    .category-icon-box {
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
        border-radius: 12px;
        width: 44px;
        height: 44px;
    }

    .section-card:hover .category-icon-box {
        transform: scale(1.06);
    }

    .feature-tag {
        font-size: 0.72rem;
        padding: 2px 8px;
        background: #f1f5f9;
        color: #475569;
        border-radius: 6px;
        font-weight: 500;
        border: 1px solid #e2e8f0;
    }

    .position-select {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1e293b;
        background-color: #f8fafc;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        padding: 4px 30px 4px 10px;
        min-width: 105px;
        transition: all 0.15s ease;
    }

    .control-actions {
        gap: 8px !important;
    }

    .card-position-select {
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e293b;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 50rem;
        height: 28px;
        padding: 2px 24px 2px 10px;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .card-position-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        outline: none;
    }

    .quick-stepper .btn {
        transition: all 0.15s ease;
    }

    .quick-stepper .btn:hover {
        background-color: #0f172a;
        color: #ffffff !important;
    }

    /* SortableJS Visual States */
    .sortable-ghost {
        opacity: 0.45 !important;
    }

    .sortable-ghost .section-card,
    .sortable-ghost.table-sortable-row {
        background-color: #f0fdf4 !important;
        border: 2px dashed #22c55e !important;
        box-shadow: none !important;
    }

    .sortable-chosen .section-card,
    .sortable-chosen.table-sortable-row {
        cursor: grabbing !important;
        transform: scale(1.02);
        box-shadow: 0 20px 30px -8px rgba(15, 23, 42, 0.25) !important;
        border-color: #3b82f6 !important;
        z-index: 9999;
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Sticky Save Bar */
    .sticky-save-bar {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        z-index: 1050;
        max-width: 580px;
        width: calc(100% - 32px);
    }

    .sticky-save-bar.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }

    .backdrop-blur {
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .pulse-dot {
        width: 10px;
        height: 10px;
        background-color: #f59e0b;
        border-radius: 50%;
        display: inline-block;
        animation: pulseAnimation 1.5s infinite;
    }

    @keyframes pulseAnimation {
        0% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
        }
    }

    .btn-save-action.dirty {
        animation: saveGlow 2s infinite;
    }

    @keyframes saveGlow {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.6);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
        }
    }

    /* Dashboard Live Edit Card Styles */
    .dashboard-card-item {
        cursor: grab !important;
        user-select: none;
    }

    .dashboard-card-item:active {
        cursor: grabbing !important;
    }

    .dashboard-card-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px rgba(24, 47, 81, 0.12) !important;
        border-color: #3b82f6 !important;
    }

    .card-stepper-pill button {
        border-radius: 50% !important;
        transition: all 0.15s ease;
        line-height: 1;
    }

    .card-stepper-pill button:hover {
        background-color: #f1f5f9;
        transform: scale(1.15);
    }

    .card-sortable-item.sortable-ghost .dashboard-card-item {
        background-color: #f0fdf4 !important;
        border: 2px dashed #22c55e !important;
        opacity: 0.5;
    }

    .card-sortable-item.sortable-chosen .dashboard-card-item {
        cursor: grabbing !important;
        transform: scale(1.03);
        box-shadow: 0 18px 30px rgba(24, 47, 81, 0.2) !important;
        border-color: #3b82f6 !important;
        z-index: 9999;
    }

    .drag-handle-card {
        cursor: grab;
        color: #94a3b8;
        padding: 2px 4px;
        transition: color 0.15s ease, transform 0.15s ease;
    }

    .drag-handle-card:hover {
        color: #3b82f6;
        transform: scale(1.15);
    }

    .card-order-badge {
        font-weight: 700;
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
    }
</style>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    /* =========================================================
     * DATA DARI SERVER
     * ========================================================= */
    const sectionMetadata = @json($sectionMeta);
    const rolePermissionsMap = @json($rolePermissionsMap ?? []);
    const factoryDefaultCardOrder = @json($defaultCardOrdersMap ?? []);
    // layoutStateMap = { all: {sections:[], cards:{}}, "<Role>": {sections:[], cards:{}} }
    // 'all' = layout global. Tiap role = layout global + override khusus role itu.
    let layoutStateMap = @json($layoutStateMap ?? []);

    const defaultSectionOrder = [
        'karyawan', 'peserta', 'itsm', 'rkm', 'finance', 'performance',
        'education', 'office', 'crm', 'management', 'project'
    ];

    /* =========================================================
     * STATE
     * ========================================================= */
    let sortableGridEl = null;
    let sortableTableBodyEl = null;
    let gridSortableInstance = null;
    let tableSortableInstance = null;
    let cardSortableInstances = {};
    let initialOrder = [];
    let initialCardOrder = {};
    let isDirty = false;
    let historyStack = [];
    let historyIndex = -1;
    let isApplyingHistory = false;
    let currentRoleFilter = 'all';

    function byId(id) {
        return document.getElementById(id);
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /* =========================================================
     * SWITCH TAMPILAN (Grid / Tabel / Detail Card)
     * ========================================================= */
    function switchSectionDisplay(subMode) {
        const views = {
            grid: byId('section-subview-grid'),
            table: byId('table-view-container'),
            cards: byId('cards-view-container')
        };
        const btns = {
            grid: byId('btn-subview-grid'),
            table: byId('btn-subview-table'),
            cards: byId('btn-subview-cards')
        };

        Object.keys(views).forEach(key => {
            if (views[key]) views[key].classList.toggle('d-none', key !== subMode);
            if (btns[key]) {
                btns[key].classList.toggle('active', key === subMode);
                btns[key].classList.toggle('text-secondary', key !== subMode);
            }
        });

        if (subMode === 'cards') {
            updateCardsRoleFilterView(currentRoleFilter);
        }
    }

    /* =========================================================
     * SNAPSHOT / HISTORY (UNDO - REDO)
     * ========================================================= */
    function getSnapshot() {
        const sections = sortableGridEl
            ? Array.from(sortableGridEl.querySelectorAll('.sortable-item')).map(el => el.dataset.id)
            : [];
        const cards = {};
        document.querySelectorAll('.card-sortable-list').forEach(listEl => {
            const secKey = listEl.dataset.section;
            if (secKey) {
                cards[secKey] = Array.from(listEl.querySelectorAll('.card-sortable-item')).map(el => el.dataset.id);
            }
        });
        return { sections, cards };
    }

    function updateHistoryButtons() {
        const undoBtn = byId('btn-undo-action');
        const redoBtn = byId('btn-redo-action');
        if (undoBtn) undoBtn.disabled = (historyIndex <= 0);
        if (redoBtn) redoBtn.disabled = (historyIndex >= historyStack.length - 1);
    }

    function recordHistory() {
        if (isApplyingHistory) return;
        const snapshot = getSnapshot();
        const currentSnap = historyStack[historyIndex];
        if (currentSnap && JSON.stringify(currentSnap) === JSON.stringify(snapshot)) {
            return;
        }
        if (historyIndex < historyStack.length - 1) {
            historyStack = historyStack.slice(0, historyIndex + 1);
        }
        historyStack.push(snapshot);
        historyIndex = historyStack.length - 1;
        updateHistoryButtons();
    }

    function applySnapshot(snapshot) {
        if (!snapshot) return;
        isApplyingHistory = true;

        try {
            if (snapshot.sections && snapshot.sections.length > 0) {
                snapshot.sections.forEach(id => {
                    if (sortableGridEl) {
                        const item = sortableGridEl.querySelector(`.sortable-item[data-id="${id}"]`);
                        if (item) sortableGridEl.appendChild(item);
                    }
                    if (sortableTableBodyEl) {
                        const row = sortableTableBodyEl.querySelector(`.table-sortable-row[data-id="${id}"]`);
                        if (row) sortableTableBodyEl.appendChild(row);
                    }
                });
                updateAllBadgesAndSelects();
            }

            if (snapshot.cards) {
                Object.keys(snapshot.cards).forEach(secKey => {
                    const listEl = document.querySelector(`.card-sortable-list[data-section="${secKey}"]`);
                    if (listEl) {
                        snapshot.cards[secKey].forEach(cardId => {
                            const cardItem = listEl.querySelector(`.card-sortable-item[data-id="${cardId}"]`);
                            if (cardItem) listEl.appendChild(cardItem);
                        });
                        updateCardBadgesForSection(secKey);
                    }
                });
            }
        } finally {
            isApplyingHistory = false;
        }

        checkDirtyState();
        updateHistoryButtons();
    }

    function undo() {
        if (historyIndex > 0) {
            historyIndex--;
            applySnapshot(historyStack[historyIndex]);
        }
    }

    function redo() {
        if (historyIndex < historyStack.length - 1) {
            historyIndex++;
            applySnapshot(historyStack[historyIndex]);
        }
    }

    /* =========================================================
     * KARTU: BADGE, PINDAH POSISI
     * ========================================================= */
    function updateCardBadgesForSection(sectionKey) {
        const container = document.querySelector(`.card-sortable-list[data-section="${sectionKey}"]`);
        if (!container) return;

        container.querySelectorAll('.card-sortable-item').forEach((item, index) => {
            const pos = index + 1;
            const select = item.querySelector('.card-position-select');
            if (select) select.value = pos;
            const badge = item.querySelector('.card-order-badge');
            if (badge) badge.textContent = '#' + pos;
        });
    }

    function moveCardToIndex(sectionKey, cardId, targetIndex1Based) {
        const listEl = document.querySelector(`.card-sortable-list[data-section="${sectionKey}"]`);
        if (!listEl) return;

        const cards = Array.from(listEl.querySelectorAll('.card-sortable-item'));
        const currentCard = listEl.querySelector(`.card-sortable-item[data-id="${cardId}"]`);
        if (!currentCard) return;

        const currentIndex = cards.indexOf(currentCard);
        const targetIndex = Math.max(0, Math.min(cards.length - 1, targetIndex1Based - 1));
        if (currentIndex === -1 || currentIndex === targetIndex) return;

        if (targetIndex >= cards.length - 1) {
            listEl.appendChild(currentCard);
        } else if (targetIndex > currentIndex) {
            listEl.insertBefore(currentCard, cards[targetIndex].nextSibling);
        } else {
            listEl.insertBefore(currentCard, cards[targetIndex]);
        }

        updateCardBadgesForSection(sectionKey);
        checkDirtyState();
        recordHistory();
    }

    /* =========================================================
     * SECTION: SINKRON GRID <-> TABEL, BADGE, PINDAH POSISI
     * ========================================================= */
    function syncFromGridToTable() {
        if (!sortableGridEl || !sortableTableBodyEl) return;
        Array.from(sortableGridEl.querySelectorAll('.sortable-item')).map(el => el.dataset.id).forEach(id => {
            const row = sortableTableBodyEl.querySelector(`.table-sortable-row[data-id="${id}"]`);
            if (row) sortableTableBodyEl.appendChild(row);
        });
    }

    function syncFromTableToGrid() {
        if (!sortableGridEl || !sortableTableBodyEl) return;
        Array.from(sortableTableBodyEl.querySelectorAll('.table-sortable-row')).map(el => el.dataset.id).forEach(id => {
            const item = sortableGridEl.querySelector(`.sortable-item[data-id="${id}"]`);
            if (item) sortableGridEl.appendChild(item);
        });
    }

    function buildPositionOptions(total, pos) {
        let html = '';
        for (let i = 1; i <= total; i++) {
            html += `<option value="${i}" ${i === pos ? 'selected' : ''}>Urutan #${i}</option>`;
        }
        return html;
    }

    function updateAllBadgesAndSelects() {
        if (!sortableGridEl || !sortableTableBodyEl) return;

        // 1. Grid (hanya item yang tampil)
        const visibleGridItems = Array.from(sortableGridEl.querySelectorAll('.sortable-item:not(.d-none)'));
        const totalVisible = visibleGridItems.length;

        visibleGridItems.forEach((item, index) => {
            const pos = index + 1;
            const isLeft = (pos % 2 !== 0);
            const targetColor = isLeft ? '#2563eb' : '#059669';
            const card = item.querySelector('.section-card');

            if (card) {
                card.style.setProperty('border', `2px solid ${targetColor}`, 'important');
                const accentStrip = card.querySelector('.card-accent-strip');
                if (accentStrip) accentStrip.style.backgroundColor = targetColor;
            }

            const badge = item.querySelector('.order-badge');
            if (badge) {
                badge.textContent = '#' + pos;
                badge.style.setProperty('background-color', targetColor, 'important');
            }

            const colIndicator = item.querySelector('.column-indicator');
            if (colIndicator) {
                colIndicator.textContent = isLeft ? 'KIRI' : 'KANAN';
                colIndicator.className = 'column-indicator badge rounded-pill px-1.5 py-0.5 mt-1 d-block border ' +
                    (isLeft ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-success-subtle text-success border-success-subtle');
            }

            const select = item.querySelector('.position-select');
            if (select) {
                select.innerHTML = buildPositionOptions(totalVisible, pos);
                select.value = pos;
            }
        });

        // 2. Tabel (hanya baris yang tampil)
        const visibleTableRows = Array.from(sortableTableBodyEl.querySelectorAll('.table-sortable-row:not(.d-none)'));
        visibleTableRows.forEach((row, index) => {
            const pos = index + 1;
            const isLeft = (pos % 2 !== 0);
            const targetColor = isLeft ? '#2563eb' : '#059669';

            const badge = row.querySelector('.order-badge');
            if (badge) {
                badge.textContent = '#' + pos;
                badge.style.setProperty('background-color', targetColor, 'important');
            }

            const colIndicator = row.querySelector('.column-indicator');
            if (colIndicator) {
                colIndicator.textContent = isLeft ? 'Kolom Kiri' : 'Kolom Kanan';
                colIndicator.className = 'column-indicator badge rounded-pill px-2 py-1 border ' +
                    (isLeft ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-success-subtle text-success border-success-subtle');
            }

            const select = row.querySelector('.position-select');
            if (select) {
                select.innerHTML = buildPositionOptions(totalVisible, pos);
                select.value = pos;
            }
        });

        // 3. Detail Card
        syncFromGridToCards();
    }

    function moveItemToIndex(itemId, targetVisibleIndex1Based) {
        if (!sortableGridEl || !sortableTableBodyEl) return;

        const visibleGridItems = Array.from(sortableGridEl.querySelectorAll('.sortable-item:not(.d-none)'));
        const currentItem = sortableGridEl.querySelector(`.sortable-item[data-id="${itemId}"]`);
        if (!currentItem) return;

        const currentVisibleIdx = visibleGridItems.indexOf(currentItem);
        const targetIdx = Math.max(0, Math.min(visibleGridItems.length - 1, targetVisibleIndex1Based - 1));
        if (currentVisibleIdx === -1 || currentVisibleIdx === targetIdx) return;

        const referenceItem = visibleGridItems[targetIdx];
        if (targetIdx > currentVisibleIdx) {
            if (referenceItem.nextSibling) {
                sortableGridEl.insertBefore(currentItem, referenceItem.nextSibling);
            } else {
                sortableGridEl.appendChild(currentItem);
            }
        } else {
            sortableGridEl.insertBefore(currentItem, referenceItem);
        }

        syncFromGridToTable();
        updateAllBadgesAndSelects();
        checkDirtyState();
        recordHistory();
    }

    /* =========================================================
     * SINKRON GRID -> DETAIL CARD
     * ========================================================= */
    function syncFromGridToCards() {
        const grid = byId('sortable-list');
        const cardsContainer = byId('cards-sections-container');
        if (!grid || !cardsContainer) return;

        const wrapperMap = {};
        cardsContainer.querySelectorAll('.dashboard-section-wrapper').forEach(w => {
            if (w.dataset.section) wrapperMap[w.dataset.section] = w;
        });
        const totalCount = Object.keys(wrapperMap).length || 11;

        Array.from(grid.querySelectorAll('.sortable-item')).forEach(item => {
            const wrapper = wrapperMap[item.dataset.id];
            if (!wrapper) return;
            cardsContainer.appendChild(wrapper);
            wrapper.classList.toggle('d-none', item.classList.contains('d-none'));
        });

        const visibleWrappers = Array.from(cardsContainer.querySelectorAll('.dashboard-section-wrapper:not(.d-none)'));
        visibleWrappers.forEach((wrapper, index) => {
            const pos = index + 1;
            const isLeft = (pos % 2 !== 0);
            const accentColor = isLeft ? '#2563eb' : '#059669';

            const box = wrapper.querySelector('.dashboard-section-box');
            if (box) box.style.setProperty('border', `2px solid ${accentColor}`, 'important');

            const badge = wrapper.querySelector('.section-pos-badge');
            if (badge) {
                badge.textContent = `Section #${pos} (${isLeft ? 'Kiri' : 'Kanan'})`;
                badge.style.setProperty('background-color', accentColor, 'important');
            }
        });

        const visibleCount = visibleWrappers.length;
        const countBadgeEl = byId('cards-section-count-badge');
        if (countBadgeEl) {
            countBadgeEl.textContent = currentRoleFilter === 'all'
                ? `Menampilkan ${totalCount}/${totalCount} Section`
                : `Menampilkan ${visibleCount}/${totalCount} Section (${currentRoleFilter})`;
        }

        const emptyStateEl = byId('cards-empty-state');
        if (emptyStateEl) {
            if (visibleCount === 0) {
                emptyStateEl.classList.remove('d-none');
                const emptyText = emptyStateEl.querySelector('.empty-role-name');
                if (emptyText) emptyText.textContent = currentRoleFilter;
            } else {
                emptyStateEl.classList.add('d-none');
            }
        }
    }

    function updateCardsRoleFilterView(role) {
        const roleNameEl = byId('cards-role-name');
        if (roleNameEl) {
            roleNameEl.textContent = (role === 'all') ? 'Semua Role (Global)' : role;
        }
        syncFromGridToCards();
    }

    /* =========================================================
     * TERAPKAN LAYOUT (GLOBAL / ROLE) KE DOM
     * ========================================================= */
    function applyLayoutState(state) {
        if (!state || !sortableGridEl) return;

        isApplyingHistory = true;
        try {
            // Urutan section (grid + tabel)
            (state.sections || []).forEach(id => {
                const item = sortableGridEl.querySelector(`.sortable-item[data-id="${id}"]`);
                if (item) sortableGridEl.appendChild(item);
                if (sortableTableBodyEl) {
                    const row = sortableTableBodyEl.querySelector(`.table-sortable-row[data-id="${id}"]`);
                    if (row) sortableTableBodyEl.appendChild(row);
                }
            });

            // Urutan card per section (null = default bawaan)
            Object.keys(factoryDefaultCardOrder).forEach(secKey => {
                const listEl = document.querySelector(`.card-sortable-list[data-section="${secKey}"]`);
                if (!listEl) return;

                const factory = factoryDefaultCardOrder[secKey] || [];
                const saved = (state.cards && Array.isArray(state.cards[secKey])) ? state.cards[secKey] : [];
                const fullOrder = [
                    ...saved.filter(id => factory.includes(id)),
                    ...factory.filter(id => !saved.includes(id))
                ];

                fullOrder.forEach(cId => {
                    const item = listEl.querySelector(`.card-sortable-item[data-id="${cId}"]`);
                    if (item) listEl.appendChild(item);
                });
                updateCardBadgesForSection(secKey);
            });
        } finally {
            isApplyingHistory = false;
        }

        updateAllBadgesAndSelects();

        // State yang baru dimuat dianggap "sudah tersimpan" -> jadikan baseline
        const snap = getSnapshot();
        initialOrder = [...snap.sections];
        initialCardOrder = JSON.parse(JSON.stringify(snap.cards));
        historyStack = [snap];
        historyIndex = 0;
        checkDirtyState();
    }

    function applyRoleLayoutState(role) {
        const state = layoutStateMap[role] || layoutStateMap['all'];
        if (state) applyLayoutState(state);
        updateAllBadgesAndSelects();
    }

    /* =========================================================
     * FILTER ROLE
     * ========================================================= */
    function handleRoleFilterChange(role) {
        if (isDirty) {
            Swal.fire({
                icon: 'warning',
                title: 'Ada perubahan belum disimpan',
                text: 'Pindah filter role akan membuang perubahan tersebut.',
                showCancelButton: true,
                confirmButtonText: 'Buang & Pindah',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    doRoleFilterChange(role);
                } else {
                    const sel = byId('role-filter-select');
                    if (sel) sel.value = currentRoleFilter;
                }
            });
            return;
        }
        doRoleFilterChange(role);
    }

    function doRoleFilterChange(role) {
        currentRoleFilter = role;
        const isAll = (role === 'all');

        const gridItems = document.querySelectorAll('#sortable-list .sortable-item');
        const tableRows = document.querySelectorAll('#table-sortable-body .table-sortable-row');
        const allowedMap = isAll ? null : (rolePermissionsMap[role] || {});
        const totalCount = gridItems.length || 11;
        let visibleCount = 0;

        gridItems.forEach(el => {
            const show = isAll || !!allowedMap[el.dataset.id];
            el.classList.toggle('d-none', !show);
            if (show) visibleCount++;
        });
        tableRows.forEach(el => {
            const show = isAll || !!allowedMap[el.dataset.id];
            el.classList.toggle('d-none', !show);
        });

        const filterAlert = byId('role-filter-alert');
        const selectedRoleNameEl = byId('selected-role-name');
        // const resetBtn = byId('btn-reset-role-filter');
        const counterBadge = byId('role-filter-counter-badge');
        const headerResetText = byId('header-reset-btn-text');

        if (isAll) {
            if (filterAlert) filterAlert.classList.add('d-none');
            // if (resetBtn) resetBtn.classList.add('d-none');
            if (headerResetText) headerResetText.textContent = 'Reset Default';
            if (counterBadge) {
                counterBadge.className = 'badge bg-light text-secondary border rounded-pill px-3 py-2 small fw-semibold';
                counterBadge.innerHTML = `<i class="fa-solid fa-layer-group text-primary me-1"></i> Menampilkan <span id="visible-sections-count">${totalCount}</span>/${totalCount} Section (Global)`;
            }
        } else {
            if (filterAlert) filterAlert.classList.remove('d-none');
            if (selectedRoleNameEl) selectedRoleNameEl.textContent = role;
            // if (resetBtn) resetBtn.classList.remove('d-none');
            if (headerResetText) headerResetText.textContent = 'Reset Role Ini';
            if (counterBadge) {
                counterBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 small fw-bold';
                counterBadge.innerHTML = `<i class="fa-solid fa-filter me-1"></i> Menampilkan <span id="visible-sections-count">${visibleCount}</span>/${totalCount} Section (${role})`;
            }
        }

        // Muat urutan section & card milik role ini (atau global jika 'all')
        applyRoleLayoutState(role);
        updateCardsRoleFilterView(role);
    }

    function resetRoleFilter() {
        const selectEl = byId('role-filter-select');
        if (selectEl) selectEl.value = 'all';
        handleRoleFilterChange('all');
    }

    /* =========================================================
     * RESET (GLOBAL & PER ROLE)
     *  - role 'all'  -> hanya tabel global (dashboard_layouts)
     *  - role tertentu -> hanya override role itu (dashboard_role_layouts)
     * ========================================================= */
    function runReset(type, role) {
        const isRole = !!role && role !== 'all';
        const label = (type === 'sections') ? 'Urutan Section' : 'Urutan Card';

        Swal.fire({
            title: `Reset ${label}${isRole ? ` untuk Role: ${role}` : ' (Global)'}?`,
            text: isRole
                ? `Hanya role "${role}" yang dikembalikan ke default global. Role lain dan pengaturan global tidak terpengaruh.`
                : 'Pengaturan global dikembalikan ke default. Role yang punya pengaturan sendiri tidak ikut berubah.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Mereset...',
                text: 'Mengembalikan ke posisi default.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('{{ route("admin.layout-setting.reset") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ type: type, role: isRole ? role : 'all' })
            })
            .then(r => r.ok ? r.json() : r.json().then(e => { throw e; }))
            .then(data => {
                if (data.layout_state) layoutStateMap = data.layout_state;
                applyRoleLayoutState(isRole ? role : 'all');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil di-Reset!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            })
            .catch(error => {
                console.error('Error reset layout:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: (error && error.message) || 'Terjadi kesalahan saat mereset.'
                });
            });
        });
    }

    // Reset khusus role yang sedang difilter (pilih: section atau card)
    function executeResetDefault() {
        if (currentRoleFilter === 'all') {
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: 'Anda sedang di mode Global. Gunakan tombol "Reset Default" di header untuk reset global.'
            });
            return;
        }

        const roleToReset = currentRoleFilter;

        Swal.fire({
            title: `Reset Default untuk Role: ${roleToReset}`,
            html: `<p class="text-muted small mb-3">Pilih yang ingin di-reset khusus untuk role <strong class="text-primary">${roleToReset}</strong>:</p>
                   <div class="d-grid gap-2">
                     <button type="button" id="swal-reset-section-btn" class="btn btn-outline-warning rounded-pill fw-semibold py-2">
                       <i class="fa-solid fa-layer-group me-2"></i> Reset Urutan Section (role ini saja)
                     </button>
                     <button type="button" id="swal-reset-cards-btn" class="btn btn-outline-primary rounded-pill fw-semibold py-2">
                       <i class="fa-solid fa-id-card me-2"></i> Reset Urutan Card (role ini saja)
                     </button>
                   </div>`,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Batal',
            cancelButtonColor: '#6c757d',
            didOpen: () => {
                byId('swal-reset-section-btn').addEventListener('click', () => {
                    Swal.close();
                    setTimeout(() => runReset('sections', roleToReset), 200);
                });
                byId('swal-reset-cards-btn').addEventListener('click', () => {
                    Swal.close();
                    setTimeout(() => runReset('cards', roleToReset), 200);
                });
            }
        });
    }

    /* =========================================================
     * DIRTY STATE (perubahan belum disimpan)
     * ========================================================= */
    function checkDirtyState() {
        let sectionDirty = false;
        let cardsDirty = false;

        if (sortableGridEl) {
            const currentOrder = Array.from(sortableGridEl.querySelectorAll('.sortable-item')).map(el => el.dataset.id);
            sectionDirty = JSON.stringify(currentOrder) !== JSON.stringify(initialOrder);
        }

        document.querySelectorAll('.card-sortable-list').forEach(listEl => {
            const secKey = listEl.dataset.section;
            const currentCards = Array.from(listEl.querySelectorAll('.card-sortable-item')).map(el => el.dataset.id);
            if (initialCardOrder[secKey] && JSON.stringify(currentCards) !== JSON.stringify(initialCardOrder[secKey])) {
                cardsDirty = true;
            }
        });

        isDirty = sectionDirty || cardsDirty;
        const hasRedo = (historyIndex < historyStack.length - 1);

        const stickySaveBar = byId('sticky-save-bar');
        const stickyTitle = byId('sticky-title');
        const stickyDesc = byId('sticky-desc');
        const stickyPulse = byId('sticky-pulse-dot');
        const saveBtns = document.querySelectorAll('.btn-save-action');
        const saveBtnSticky = stickySaveBar ? stickySaveBar.querySelector('.btn-save-action') : null;

        if (isDirty) {
            saveBtns.forEach(btn => {
                btn.classList.add('dirty');
                btn.disabled = false;
            });
            if (stickyTitle) stickyTitle.textContent = 'Urutan Berubah';
            if (stickyDesc) stickyDesc.textContent = currentRoleFilter === 'all'
                ? 'Klik Simpan untuk menerapkan tata letak global.'
                : `Klik Simpan untuk menerapkan tata letak role "${currentRoleFilter}".`;
            if (stickyPulse) stickyPulse.style.backgroundColor = '#f59e0b';
            if (stickySaveBar) stickySaveBar.classList.add('show');
        } else {
            saveBtns.forEach(btn => btn.classList.remove('dirty'));

            if (hasRedo) {
                if (stickyTitle) stickyTitle.textContent = 'Kembali ke Posisi Awal';
                if (stickyDesc) stickyDesc.textContent = 'Klik → untuk memajukan perubahan kembali.';
                if (stickyPulse) stickyPulse.style.backgroundColor = '#3b82f6';
                if (saveBtnSticky) saveBtnSticky.disabled = true;
                if (stickySaveBar) stickySaveBar.classList.add('show');
            } else if (stickySaveBar) {
                stickySaveBar.classList.remove('show');
            }
        }

        updateHistoryButtons();
    }

    /* =========================================================
     * SIMPAN
     *  - role 'all'  -> simpan ke tabel global
     *  - role tertentu -> simpan ke override role itu saja
     * ========================================================= */
    function saveLayout() {
        if (!sortableGridEl) return;

        const snap = getSnapshot();
        const currentOrder = snap.sections;
        const currentCardsOrder = snap.cards;
        const activeRole = (currentRoleFilter && currentRoleFilter !== 'all') ? currentRoleFilter : null;
        const saveBtns = document.querySelectorAll('.btn-save-action');

        saveBtns.forEach(b => { b.disabled = true; });

        Swal.fire({
            title: 'Menyimpan Pengaturan Layout...',
            text: activeRole
                ? `Menyimpan tata letak khusus untuk role "${activeRole}". Role lain tidak terpengaruh.`
                : 'Menerapkan tata letak global section dan kartu.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        const payload = { order: currentOrder, cards_order: currentCardsOrder };
        if (activeRole) payload.role = activeRole;

        fetch('{{ route("admin.layout-setting.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.layout_state) layoutStateMap = data.layout_state;

            initialOrder = [...currentOrder];
            initialCardOrder = JSON.parse(JSON.stringify(currentCardsOrder));
            historyStack = [getSnapshot()];
            historyIndex = 0;
            checkDirtyState();

            Swal.fire({
                icon: 'success',
                title: 'Berhasil Disimpan!',
                text: data.message || 'Urutan section dan kartu dashboard berhasil disimpan.',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
        })
        .catch(error => {
            console.error('Error saving dashboard layout:', error);
            const errMsg = (error && error.message) ||
                (error && error.errors ? Object.values(error.errors).flat().join(', ') : 'Terjadi kesalahan saat menyimpan urutan.');
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan!',
                text: errMsg,
                showConfirmButton: true
            });
        })
        .finally(() => {
            saveBtns.forEach(b => { b.disabled = false; });
            checkDirtyState();
        });
    }

    /* =========================================================
     * PREVIEW MODAL
     * ========================================================= */
    function populatePreviewModal() {
        const leftCol = byId('preview-left-column');
        const rightCol = byId('preview-right-column');
        const modalLabel = byId('previewModalLabel');
        if (!leftCol || !rightCol || !sortableGridEl) return;

        leftCol.innerHTML = '';
        rightCol.innerHTML = '';

        if (modalLabel) {
            modalLabel.innerHTML = currentRoleFilter !== 'all'
                ? `Simulasi Tampilan Dashboard Home <span class="badge bg-primary fs-6 fw-normal ms-2">Role: ${currentRoleFilter}</span>`
                : `Simulasi Tampilan Dashboard Home <span class="badge bg-secondary fs-6 fw-normal ms-2">Semua Role (Global)</span>`;
        }

        const currentOrder = Array.from(sortableGridEl.querySelectorAll('.sortable-item')).map(el => el.dataset.id);
        const allowedSections = (currentRoleFilter !== 'all') ? (rolePermissionsMap[currentRoleFilter] || {}) : null;

        let displayPos = 0;
        currentOrder.forEach(id => {
            if (allowedSections && !allowedSections[id]) return;
            displayPos++;
            const pos = displayPos;
            const isLeft = (pos % 2 !== 0);
            const color = isLeft ? '#2563eb' : '#059669';
            const meta = sectionMetadata[id] || { name: id, icon: 'users.svg', color: '#475569', desc: '' };

            const cardHtml = `
                <div class="card border-0 shadow-xs p-3 rounded-3" style="border: 2px solid ${color} !important; background: #ffffff;">
                    <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                        <span class="badge rounded-pill text-white fw-bold px-2 py-1" style="background-color: ${color}; font-size: 0.75rem;">#${pos}</span>
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; background-color: ${color}15; border: 1px solid ${color}30;">
                            <img src="/icon/${meta.icon}" alt="${meta.name}" width="18" height="18">
                        </div>
                        <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                            <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.88rem;">${meta.name}</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">${meta.desc}</p>
                        </div>
                    </div>
                </div>`;

            if (isLeft) {
                leftCol.innerHTML += cardHtml;
            } else {
                rightCol.innerHTML += cardHtml;
            }
        });
    }

    /* =========================================================
     * INISIALISASI
     * ========================================================= */
    document.addEventListener('DOMContentLoaded', function () {
        sortableGridEl = byId('sortable-list');
        sortableTableBodyEl = byId('table-sortable-body');

        // Sortable: grid section
        if (sortableGridEl) {
            gridSortableInstance = new Sortable(sortableGridEl, {
                animation: 250,
                draggable: '.sortable-item',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle: '.drag-handle, .category-icon-box',
                onEnd: function () {
                    syncFromGridToTable();
                    updateAllBadgesAndSelects();
                    checkDirtyState();
                    recordHistory();
                }
            });
        }

        // Sortable: tabel section
        if (sortableTableBodyEl) {
            tableSortableInstance = new Sortable(sortableTableBodyEl, {
                animation: 250,
                draggable: '.table-sortable-row',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle: '.drag-handle, .category-icon-box',
                onEnd: function () {
                    syncFromTableToGrid();
                    updateAllBadgesAndSelects();
                    checkDirtyState();
                    recordHistory();
                }
            });
        }

        // Sortable: card di tiap section
        document.querySelectorAll('.card-sortable-list').forEach(listEl => {
            const sectionKey = listEl.dataset.section;

            cardSortableInstances[sectionKey] = new Sortable(listEl, {
                animation: 200,
                draggable: '.card-sortable-item',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function () {
                    updateCardBadgesForSection(sectionKey);
                    checkDirtyState();
                    recordHistory();
                }
            });
        });

        // Baseline awal = kondisi halaman saat dimuat (layout global dari server)
        const baseSnap = getSnapshot();
        initialOrder = [...baseSnap.sections];
        initialCardOrder = JSON.parse(JSON.stringify(baseSnap.cards));
        historyStack = [baseSnap];
        historyIndex = 0;
        updateHistoryButtons();

        // Undo / Redo
        const undoBtn = byId('btn-undo-action');
        const redoBtn = byId('btn-redo-action');
        if (undoBtn) undoBtn.addEventListener('click', e => { e.preventDefault(); undo(); });
        if (redoBtn) redoBtn.addEventListener('click', e => { e.preventDefault(); redo(); });

        document.addEventListener('keydown', function (e) {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
            const key = e.key.toLowerCase();
            if ((e.ctrlKey || e.metaKey) && key === 'z') {
                e.preventDefault();
                e.shiftKey ? redo() : undo();
            } else if ((e.ctrlKey || e.metaKey) && key === 'y') {
                e.preventDefault();
                redo();
            }
        });

        // Dropdown posisi (section & card)
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('position-select')) {
                moveItemToIndex(e.target.dataset.id, parseInt(e.target.value, 10));
            } else if (e.target.classList.contains('card-position-select')) {
                moveCardToIndex(e.target.dataset.section, e.target.dataset.id, parseInt(e.target.value, 10));
            }
        });

        // Tombol panah cepat (section & card)
        document.addEventListener('click', function (e) {
            const topBtn = e.target.closest('.btn-move-top');
            const bottomBtn = e.target.closest('.btn-move-bottom');
            const upBtn = e.target.closest('.btn-move-up');
            const downBtn = e.target.closest('.btn-move-down');
            const sectionBtn = topBtn || bottomBtn || upBtn || downBtn;

            if (sectionBtn) {
                const container = sectionBtn.closest('.sortable-item, .table-sortable-row');
                if (container && sortableGridEl) {
                    const itemId = container.dataset.id;
                    const visibleItems = Array.from(sortableGridEl.querySelectorAll('.sortable-item:not(.d-none)'));
                    const currentItem = sortableGridEl.querySelector(`.sortable-item[data-id="${itemId}"]`);
                    const curIdx = visibleItems.indexOf(currentItem) + 1;

                    if (curIdx > 0) {
                        if (topBtn) moveItemToIndex(itemId, 1);
                        else if (bottomBtn) moveItemToIndex(itemId, visibleItems.length);
                        else if (upBtn) moveItemToIndex(itemId, Math.max(1, curIdx - 1));
                        else if (downBtn) moveItemToIndex(itemId, Math.min(visibleItems.length, curIdx + 1));
                    }
                    return;
                }
            }

            const cTop = e.target.closest('.btn-card-move-top');
            const cBottom = e.target.closest('.btn-card-move-bottom');
            const cUp = e.target.closest('.btn-card-move-up');
            const cDown = e.target.closest('.btn-card-move-down');
            const cardBtn = cTop || cBottom || cUp || cDown;

            if (cardBtn) {
                const cardItem = cardBtn.closest('.card-sortable-item');
                if (!cardItem) return;

                const sectionKey = cardItem.dataset.section;
                const cardId = cardItem.dataset.id;
                const listEl = cardItem.closest('.card-sortable-list');
                if (!listEl) return;

                const currentOrder = Array.from(listEl.querySelectorAll('.card-sortable-item')).map(el => el.dataset.id);
                const curIdx = currentOrder.indexOf(cardId) + 1;

                if (cTop) moveCardToIndex(sectionKey, cardId, 1);
                else if (cBottom) moveCardToIndex(sectionKey, cardId, currentOrder.length);
                else if (cUp) moveCardToIndex(sectionKey, cardId, Math.max(1, curIdx - 1));
                else if (cDown) moveCardToIndex(sectionKey, cardId, Math.min(currentOrder.length, curIdx + 1));
            }
        });

        // Tombol Simpan (header + sticky bar)
        document.querySelectorAll('.btn-save-action').forEach(btn => {
            btn.addEventListener('click', saveLayout);
        });

        // Tombol Reset header:
        //  - filter role aktif  -> reset PER ROLE (pilih section / card)
        //  - global             -> reset GLOBAL sesuai tab aktif (section / card)
        document.querySelectorAll('.btn-reset-action').forEach(btn => {
            btn.addEventListener('click', function () {
                if (currentRoleFilter && currentRoleFilter !== 'all') {
                    executeResetDefault();
                    return;
                }
                const cardsContainer = byId('cards-view-container');
                const isCardsMode = cardsContainer && !cardsContainer.classList.contains('d-none');
                runReset(isCardsMode ? 'cards' : 'sections', 'all');
            });
        });

        // Preview modal
        const previewModalEl = byId('previewModal');
        if (previewModalEl) {
            previewModalEl.addEventListener('show.bs.modal', populatePreviewModal);
        }

        // Inisialisasi tampilan awal
        updateAllBadgesAndSelects();
        checkDirtyState();
    });
</script>
@endsection