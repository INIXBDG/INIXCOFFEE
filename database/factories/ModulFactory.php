<?php

namespace Database\Factories;

use App\Models\Materi;
use App\Models\Modul;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModulFactory extends Factory
{
    protected $model = Modul::class;

    public function definition(): array
    {
        $awalTraining = fake()->dateTimeBetween('-1 year', 'now');
        $akhirTraining = fake()->dateTimeBetween(
            $awalTraining,
            $awalTraining->format('Y-m-d') . ' +5 days'
        );

        $jumlah = fake()->numberBetween(1, 20);
        $hargaSatuan = fake()->numberBetween(500_000, 10_000_000);
        $total = $jumlah * $hargaSatuan;

        return [
            'no_modul'       => fake()->unique()->bothify('MOD-2026-####'),
            'id_materi'      => MateriFactory::new(),
            'kode_materi'    => fake()->unique()->bothify('MAT-###'),
            'nama_materi'    => fake()->sentence(2),
            'awal_training'  => $awalTraining->format('Y-m-d'),
            'akhir_training' => $akhirTraining->format('Y-m-d'),
            'jumlah'         => $jumlah,
            'harga_satuan'   => $hargaSatuan,
            'total'          => $total,
            'note'           => fake()->optional()->sentence(),
        ];
    }
}