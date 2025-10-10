<?php

namespace Database\Seeders;

use App\Models\lokasi as ModelsLokasi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Lokasi extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            [
                'lokasi' => 'Nanggroe Aceh Darussalam',
                'latitude' => 4.36855,
                'longitude' => 97.0253
            ],
            [
                'lokasi' => 'Sumatera Utara',
                'latitude' => 2.19235,
                'longitude' => 99.38122
            ],
            [
                'lokasi' => 'Sumatera Barat',
                'latitude' => -1.34225,
                'longitude' => 100.0761
            ],
            [
                'lokasi' => 'Riau',
                'latitude' => 0.50041,
                'longitude' => 101.54758
            ],
            [
                'lokasi' => 'Jambi',
                'latitude' => -1.61667,
                'longitude' => 103.61667
            ],
            [
                'lokasi' => 'Sumatera Selatan',
                'latitude' => -3.00000,
                'longitude' => 103.00000
            ],
            [
                'lokasi' => 'Bengkulu',
                'latitude' => -3.80000,
                'longitude' => 102.25000
            ],
            [
                'lokasi' => 'Lampung',
                'latitude' => -5.00000,
                'longitude' => 105.00000
            ],
            [
                'lokasi' => 'Kepulauan Bangka Belitung',
                'latitude' => -2.00000,
                'longitude' => 106.00000
            ],
            [
                'lokasi' => 'DKI Jakarta',
                'latitude' => -6.20000,
                'longitude' => 106.81667
            ],
            [
                'lokasi' => 'Jawa Barat',
                'latitude' => -6.91474,
                'longitude' => 107.60981
            ],
            [
                'lokasi' => 'Jawa Tengah',
                'latitude' => -7.00000,
                'longitude' => 110.00000
            ],
            [
                'lokasi' => 'Daerah Istimewa Yogyakarta',
                'latitude' => -7.79558,
                'longitude' => 110.36950
            ],
            [
                'lokasi' => 'Jawa Timur',
                'latitude' => -7.25000,
                'longitude' => 112.75000
            ],
            [
                'lokasi' => 'Banten',
                'latitude' => -6.30000,
                'longitude' => 106.15000
            ],
            [
                'lokasi' => 'Bali',
                'latitude' => -8.34000,
                'longitude' => 115.09000
            ],
            [
                'lokasi' => 'Nusa Tenggara Barat',
                'latitude' => -8.58000,
                'longitude' => 116.08000
            ],
            [
                'lokasi' => 'Nusa Tenggara Timur',
                'latitude' => -9.43000,
                'longitude' => 119.85000
            ],
            [
                'lokasi' => 'Kalimantan Barat',
                'latitude' => -0.50000,
                'longitude' => 112.00000
            ],
            [
                'lokasi' => 'Kalimantan Tengah',
                'latitude' => -2.00000,
                'longitude' => 113.91667
            ],
            [
                'lokasi' => 'Kalimantan Selatan',
                'latitude' => -3.31900,
                'longitude' => 114.59200
            ],
            [
                'lokasi' => 'Kalimantan Timur',
                'latitude' => 0.50000,
                'longitude' => 117.00000
            ],
            [
                'lokasi' => 'Kalimantan Utara',
                'latitude' => 3.00000,
                'longitude' => 117.00000
            ],
            [
                'lokasi' => 'Sulawesi Utara',
                'latitude' => 0.75000,
                'longitude' => 124.25000
            ],
            [
                'lokasi' => 'Sulawesi Tengah',
                'latitude' => -1.00000,
                'longitude' => 120.00000
            ],
            [
                'lokasi' => 'Sulawesi Selatan',
                'latitude' => -3.00000,
                'longitude' => 120.00000
            ],
            [
                'lokasi' => 'Sulawesi Tenggara',
                'latitude' => -4.00000,
                'longitude' => 122.00000
            ],
            [
                'lokasi' => 'Gorontalo',
                'latitude' => 0.54000,
                'longitude' => 123.05000
            ],
            [
                'lokasi' => 'Sulawesi Barat',
                'latitude' => -2.75000,
                'longitude' => 119.00000
            ],
            [
                'lokasi' => 'Maluku',
                'latitude' => -3.00000,
                'longitude' => 129.00000
            ],
            [
                'lokasi' => 'Maluku Utara',
                'latitude' => 1.00000,
                'longitude' => 128.00000
            ],
            [
                'lokasi' => 'Papua Barat',
                'latitude' => -1.00000,
                'longitude' => 133.00000
            ],
            [
                'lokasi' => 'Papua',
                'latitude' => -4.00000,
                'longitude' => 138.00000
            ],
            [
                'lokasi' => 'Kepulauan Riau',
                'latitude' => 0.50000,
                'longitude' => 104.50000
            ],
        ];

        foreach ($provinces as $province) {
            ModelsLokasi::create($province);
        }
    }
}
