<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RekapInventaris;
use Carbon\Carbon;

class InventarisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('database/data/inventaris.csv');

        if (file_exists($filePath)) {
            $this->command->info("Menyemai data dari file CSV...");
            $file = fopen($filePath, 'r');
            $header = fgetcsv($file); // Lewati header

            while (($row = fgetcsv($file)) !== FALSE) {
                RekapInventaris::create([
                    'name' => $row[1],
                    'kategori' => 'Office',
                    'qty' => 1,
                    'total_harga' => str_replace(['Rp', '.', ','], '', $row[2]),
                    'waktu_pembelian' => date('Y-m-d', strtotime($row[3])),
                    'no_kk' => $row[5] ?? 'KK-GEN',
                ]);
            }
            fclose($file);
            $this->command->info("Data dari CSV berhasil disemai.");
            return;
        }

        $this->command->warn("File CSV tidak ditemukan di: " . $filePath);
        $this->command->info("Menyemai data tiruan (mock data) untuk testing...");

        // Mock data items
        $mockItems = [
            [
                'name' => 'Laptop ASUS ROG Zephyrus G14',
                'kategori' => 'ITSM',
                'qty' => 2,
                'total_harga' => 48000000.00,
                'waktu_pembelian' => '2026-01-15',
                'ruangan' => 'Ruang IT',
                'no_kk' => 'KK-001',
                'deskripsi' => 'Pengadaan laptop development tim digital'
            ],
            [
                'name' => 'Meja Kerja Ergonomis Office',
                'kategori' => 'Office',
                'qty' => 5,
                'total_harga' => 12500000.00,
                'waktu_pembelian' => '2026-02-10',
                'ruangan' => 'Ruang Kerja Staff',
                'no_kk' => 'KK-002',
                'deskripsi' => 'Meja kerja adjustable staff baru'
            ],
            [
                'name' => 'Kursi Ergonomis Office',
                'kategori' => 'Office',
                'qty' => 5,
                'total_harga' => 7500000.00,
                'waktu_pembelian' => '2026-02-12',
                'ruangan' => 'Ruang Kerja Staff',
                'no_kk' => 'KK-002',
                'deskripsi' => 'Kursi kerja ergonomis staff baru'
            ],
            [
                'name' => 'Smart TV LG 65 Inch 4K',
                'kategori' => 'Kelas',
                'qty' => 1,
                'total_harga' => 11000000.00,
                'waktu_pembelian' => '2026-03-05',
                'ruangan' => 'Ruang Kelas A',
                'no_kk' => 'KK-003',
                'deskripsi' => 'Display presentasi ruang kelas A'
            ],
            [
                'name' => 'Proyektor Epson EB-X06',
                'kategori' => 'Kelas',
                'qty' => 2,
                'total_harga' => 13600000.00,
                'waktu_pembelian' => '2025-06-20',
                'ruangan' => 'Ruang Kelas B',
                'no_kk' => 'KK-004',
                'deskripsi' => 'Proyektor ruang kelas B dan C'
            ],
            [
                'name' => 'Cisco Router ISR 4331',
                'kategori' => 'ITSM',
                'qty' => 1,
                'total_harga' => 35000000.00,
                'waktu_pembelian' => '2025-08-15',
                'ruangan' => 'Ruang Server IT',
                'no_kk' => 'KK-005',
                'deskripsi' => 'Upgrade router utama kantor pusat'
            ],
            [
                'name' => 'MacBook Pro M3 Pro 16 Inch',
                'kategori' => 'Education',
                'qty' => 1,
                'total_harga' => 42000000.00,
                'waktu_pembelian' => '2026-04-18',
                'ruangan' => 'Ruang Instruktur',
                'no_kk' => 'KK-006',
                'deskripsi' => 'Pengadaan laptop instruktur senior'
            ],
            [
                'name' => 'iPad Air M2 128GB',
                'kategori' => 'Sales/Tim Digital',
                'qty' => 3,
                'total_harga' => 36000000.00,
                'waktu_pembelian' => '2025-11-02',
                'ruangan' => 'Ruang Marketing',
                'no_kk' => 'KK-007',
                'deskripsi' => 'Alat demonstrasi sales sales kit'
            ],
            [
                'name' => 'Toyota Avanza Veloz (Cicilan)',
                'kategori' => 'Cicilan Kendaraan',
                'qty' => 1,
                'total_harga' => 8500000.00,
                'waktu_pembelian' => '2026-05-01',
                'ruangan' => 'Garasi Kantor',
                'no_kk' => 'KK-008',
                'deskripsi' => 'Cicilan bulanan kendaraan operasional'
            ],
            [
                'name' => 'Whiteboard Glass 120x240',
                'kategori' => 'Kelas',
                'qty' => 3,
                'total_harga' => 4500000.00,
                'waktu_pembelian' => '2025-03-12',
                'ruangan' => 'Ruang Kelas A',
                'no_kk' => 'KK-009',
                'deskripsi' => 'Pemasangan kaca whiteboard kelas'
            ],
            [
                'name' => 'Printer HP LaserJet Pro M404dn',
                'kategori' => 'Office',
                'qty' => 2,
                'total_harga' => 9800000.00,
                'waktu_pembelian' => '2024-05-14',
                'ruangan' => 'Ruang Kerja Staff',
                'no_kk' => 'KK-010',
                'deskripsi' => 'Printer administrasi HRD dan Finance'
            ],
            [
                'name' => 'AC Daikin 1.5 PK Split',
                'kategori' => 'Office',
                'qty' => 4,
                'total_harga' => 26000000.00,
                'waktu_pembelian' => '2024-07-22',
                'ruangan' => 'Ruang Kerja Staff',
                'no_kk' => 'KK-011',
                'deskripsi' => 'Penggantian AC ruangan staff'
            ]
        ];

        // Seed mock data
        foreach ($mockItems as $item) {
            $year = Carbon::parse($item['waktu_pembelian'])->format('y');
            $randomCode = strtoupper(substr($item['kategori'], 0, 3)) . rand(100, 999);
            $idbarang = sprintf('INX/%s/%s/%d', $year, $randomCode, rand(1, 10));

            RekapInventaris::create([
                'idbarang' => $idbarang,
                'name' => $item['name'],
                'kategori' => $item['kategori'],
                'qty' => $item['qty'],
                'total_harga' => $item['total_harga'],
                'waktu_pembelian' => $item['waktu_pembelian'],
                'ruangan' => $item['ruangan'],
                'no_kk' => $item['no_kk'],
                'deskripsi' => $item['deskripsi']
            ]);
        }

        $this->command->info("Sukses menyemai " . count($mockItems) . " data tiruan Rekap Inventaris.");
    }
}
