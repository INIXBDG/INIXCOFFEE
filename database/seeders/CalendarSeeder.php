<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Todo;
use App\Models\YearMapping;

class CalendarSeeder extends Seeder
{
    public function run()
    {
        // 1. Reset Data (Hanya gunakan TRUNCATE jika ini benar-benar untuk testing/fresh install awal)
        // Jika Anda ingin mempertahankan data Q1-Q3 yang sudah ada, JANGAN jalankan truncate di bawah ini.
        // DB::table('event_todos')->truncate();
        // DB::table('timeline_items')->truncate();
        // DB::table('quarter_events')->truncate();
        // DB::table('year_mappings')->truncate();
        // DB::table('todos')->truncate();

        // 2. Isi Master Todo (Pekerjaan Standar)
        $todos = [
            // KELOMPOK 1: JOBDESK
            ['category' => 'Jobdesk', 'task_name' => 'MC', 'sort_order' => 1],
            ['category' => 'Jobdesk', 'task_name' => 'Pemateri', 'sort_order' => 2],
            ['category' => 'Jobdesk', 'task_name' => 'Moderator', 'sort_order' => 3],
            ['category' => 'Jobdesk', 'task_name' => 'Link Pendaftaran', 'sort_order' => 4],
            ['category' => 'Jobdesk', 'task_name' => 'Link Feed Back', 'sort_order' => 5],
            ['category' => 'Jobdesk', 'task_name' => 'Link Zoom & Youtube', 'sort_order' => 6],
            ['category' => 'Jobdesk', 'task_name' => 'Akun E-Learning', 'sort_order' => 7],
            ['category' => 'Jobdesk', 'task_name' => 'Teknis & Ruangan', 'sort_order' => 8],
            ['category' => 'Jobdesk', 'task_name' => 'Blast Link Pendaftaran WA', 'sort_order' => 9],
            ['category' => 'Jobdesk', 'task_name' => 'Blast Link Pendaftaran Email', 'sort_order' => 10],
            ['category' => 'Jobdesk', 'task_name' => 'Blast Link Zoom', 'sort_order' => 11],
            ['category' => 'Jobdesk', 'task_name' => 'Sertifikat Webinar', 'sort_order' => 12],

            // KELOMPOK 2: KEBUTUHAN
            ['category' => 'Kebutuhan', 'task_name' => 'Flyer', 'sort_order' => 13],
            ['category' => 'Kebutuhan', 'task_name' => 'Flyer Promo Class', 'sort_order' => 14],
            ['category' => 'Kebutuhan', 'task_name' => 'Konten Reels', 'sort_order' => 15],
            ['category' => 'Kebutuhan', 'task_name' => 'Background Zoom', 'sort_order' => 16],
            ['category' => 'Kebutuhan', 'task_name' => 'Kuis di Zoom', 'sort_order' => 17],
            ['category' => 'Kebutuhan', 'task_name' => 'Kuis di Instagram', 'sort_order' => 18],
            ['category' => 'Kebutuhan', 'task_name' => 'Rekaman Zoom', 'sort_order' => 19],
            ['category' => 'Kebutuhan', 'task_name' => 'Admin Zoom', 'sort_order' => 20],
            ['category' => 'Kebutuhan', 'task_name' => 'Hadiah Doorprize', 'sort_order' => 21],
            ['category' => 'Kebutuhan', 'task_name' => 'Hadiah Doorprize Instagram', 'sort_order' => 22],

            // --- TAMBAHAN BARU ---
            ['category' => 'Kebutuhan', 'task_name' => 'Voucher webinar', 'sort_order' => 23],

            // KELOMPOK LAMA (DIPERTAHANKAN) - Agar data event kuartal sebelum Q4 tidak hilang/rusak
            ['category' => 'Perintilan', 'task_name' => 'KONSUMSI PANITIA', 'sort_order' => 24],

            // KELOMPOK BARU (AKTIVITAS SETELAH WEBINAR - Berlaku mulai Q4 2026)
            ['category' => 'Aktivitas Setelah Webinar', 'task_name' => 'KONSUMSI PANITIA', 'sort_order' => 25],
            ['category' => 'Aktivitas Setelah Webinar', 'task_name' => 'Konfirmasi dan Pengiriman hadiah kuis', 'sort_order' => 26],
            ['category' => 'Aktivitas Setelah Webinar', 'task_name' => 'Rekap aktifitas webinar', 'sort_order' => 27],
        ];

        // Gunakan firstOrCreate agar tidak error duplikat dan tidak perlu truncate
        foreach($todos as $t) {
            Todo::firstOrCreate(
                ['task_name' => $t['task_name'], 'category' => $t['category']],
                ['sort_order' => $t['sort_order']]
            );
        }

        $themes = [
            1 => 'PMBOK Strategy', 2 => 'Scrum Basics', 3 => 'Agile Tools', // Q1
            4 => 'Mindset DevOps', 5 => 'Dockerization', 6 => 'CI/CD Pipeline', // Q2
            7 => 'Gen AI Intro', 8 => 'Machine Learning', 9 => 'Computer Vision', // Q3
            10 => 'Cyber Security', 11 => 'Ethical Hacking', 12 => 'Risk Management' // Q4
        ];

        for ($i = 1; $i <= 12; $i++) {
            $quarter = ceil($i / 3);

            // Menggunakan updateOrCreate agar aman dari duplikat
            YearMapping::updateOrCreate(
                [
                    'year' => 2026,
                    'month' => $i,
                ],
                [
                    'quarter' => $quarter,
                    'theme' => $themes[$i],
                    'planned_date' => "2026-{$i}-25",
                    'duration_minutes' => 120
                ]
            );
        }
    }
}
