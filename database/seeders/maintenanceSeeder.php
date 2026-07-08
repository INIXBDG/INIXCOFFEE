<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Maintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class maintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama jika ingin mengulang dari awal (opsional)
        // DB::table('maintenances')->truncate();

        $data = [
            // Status: On Progress
            [
                'kategori' => 'Hardware',
                'divisi' => 'IT',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'Server Rack A1',
                'tanggal_mulai' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'tanggal_selesai' => null,
                'no_voucher' => 'MNT-2026-001',
                'biaya' => 2500000,
                'keterangan' => 'Penggantian RAM pada server utama.',
                'status' => 'On Progress',
            ],
            [
                'kategori' => 'Software',
                'divisi' => 'Finance',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'Aplikasi ERP Keuangan',
                'tanggal_mulai' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'tanggal_selesai' => null,
                'no_voucher' => 'MNT-2026-002',
                'biaya' => 1500000,
                'keterangan' => 'Pembaruan lisensi tahunan.',
                'status' => 'On Progress',
            ],
            [
                'kategori' => 'Fasilitas',
                'divisi' => 'Gedung Utama',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'AC Sentral Lantai 1',
                'tanggal_mulai' => Carbon::now()->format('Y-m-d'),
                'tanggal_selesai' => null,
                'no_voucher' => 'MNT-2026-003',
                'biaya' => 750000,
                'keterangan' => 'Pembersihan filter dan isi freon.',
                'status' => 'On Progress',
            ],

            // Status: Selesai
            [
                'kategori' => 'Jaringan',
                'divisi' => 'Marketing',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'Router Access Point',
                'tanggal_mulai' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'tanggal_selesai' => Carbon::now()->subDays(9)->format('Y-m-d'),
                'no_voucher' => 'MNT-2026-004',
                'biaya' => 450000,
                'keterangan' => 'Router mati total, dilakukan perbaikan jaringan kabel.',
                'status' => 'Selesai',
            ],
            [
                'kategori' => 'Hardware',
                'divisi' => 'Finance',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'Printer Keuangan',
                'tanggal_mulai' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'tanggal_selesai' => Carbon::now()->subDays(19)->format('Y-m-d'),
                'no_voucher' => 'MNT-2026-005',
                'biaya' => 120000,
                'keterangan' => 'Tinta habis dan head printer buntu.',
                'status' => 'Selesai',
            ],
            [
                'kategori' => 'Fasilitas',
                'divisi' => 'Gedung Utama',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'Lampu Ruang Meeting',
                'tanggal_mulai' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'tanggal_selesai' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'no_voucher' => 'MNT-2026-006',
                'biaya' => 200000,
                'keterangan' => 'Penggantian 4 buah lampu LED.',
                'status' => 'Selesai',
            ],

            // Status: Mendatang
            [
                'kategori' => 'Software',
                'divisi' => 'IT',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'Sistem Informasi Akademik',
                'tanggal_mulai' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'tanggal_selesai' => null,
                'no_voucher' => 'MNT-2026-007',
                'biaya' => 5000000,
                'keterangan' => 'Audit keamanan sistem dan penambahan modul.',
                'status' => 'Mendatang',
            ],
            [
                'kategori' => 'Jaringan',
                'divisi' => 'IT',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'Kabel LAN Server',
                'tanggal_mulai' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'tanggal_selesai' => null,
                'no_voucher' => 'MNT-2026-008',
                'biaya' => 850000,
                'keterangan' => 'Maintenance rutin instalasi kabel.',
                'status' => 'Mendatang',
            ],
            [
                'kategori' => 'Hardware',
                'divisi' => 'Marketing',
                'teknisi' => 'Ifik Arifin',
                'nama_barang' => 'Laptop Presentasi',
                'tanggal_mulai' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'tanggal_selesai' => null,
                'no_voucher' => 'MNT-2026-009',
                'biaya' => 300000,
                'keterangan' => 'Install ulang Windows dan pembersihan fan.',
                'status' => 'Mendatang',
            ]
        ];

        foreach ($data as $item) {
            Maintenance::create($item);
        }
    }
}
