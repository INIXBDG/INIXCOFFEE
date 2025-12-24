<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\YearMapping;
use App\Models\Todo;
use Illuminate\Support\Facades\DB;

class CalendarSeeder extends Seeder
{
    public function run()
    {
        // 1. Reset Data (Agar tidak duplikat saat testing)
        // Karena tidak ada foreign key constraint, kita bisa truncate aman
        DB::table('event_todos')->truncate();
        DB::table('timeline_items')->truncate();
        DB::table('quarter_events')->truncate();
        DB::table('year_mappings')->truncate();
        DB::table('todos')->truncate();

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

            // KELOMPOK 3: PERINTILAN
            ['category' => 'Perintilan', 'task_name' => 'KONSUMSI PANITIA', 'sort_order' => 23],
        ];
        foreach($todos as $t) {
            Todo::create($t);
        }

        // 3. Isi Mapping Tahunan (Slot Bulan untuk Tahun 2025)
        // Kita buat setahun penuh (Q1 - Q4)
        $mappings = [];
        $themes = [
            1 => 'PMBOK Strategy', 2 => 'Scrum Basics', 3 => 'Agile Tools', // Q1
            4 => 'Mindset DevOps', 5 => 'Dockerization', 6 => 'CI/CD Pipeline', // Q2
            7 => 'Gen AI Intro', 8 => 'Machine Learning', 9 => 'Computer Vision', // Q3
            10 => 'Cyber Security', 11 => 'Ethical Hacking', 12 => 'Risk Management' // Q4
        ];

        for ($i = 1; $i <= 12; $i++) {
            $quarter = ceil($i / 3);
            YearMapping::create([
                'year' => 2025,
                'quarter' => $quarter,
                'month' => $i,
                'theme' => $themes[$i],
                'planned_date' => "2025-{$i}-25", // Asumsi tanggal 25 tiap bulan
                'duration_minutes' => 120
            ]);
        }
    }
}
