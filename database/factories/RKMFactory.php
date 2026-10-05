<?php

namespace Database\Factories;

use App\Models\RKM;
use Illuminate\Database\Eloquent\Factories\Factory;

class RKMFactory extends Factory
{
    protected $model = RKM::class;

    public function definition(): array
    {
        $awal = now()->addDays(fake()->numberBetween(1, 30));

        return [
            'sales_key'      => fake()->bothify('SLS-###'),
            'materi_key'     => fake()->bothify('MTR-###'),
            'perusahaan_key' => fake()->bothify('PRS-###'),
            'harga_jual'     => '5000000',
            'pax'            => '5',
            'isi_pax'        => '5',
            'tanggal_awal'   => $awal->toDateString(),
            'tanggal_akhir'  => $awal->copy()->addDays(2)->toDateString(),
            'status'         => '0',
            'exam'           => '0',
            'authorize'      => '0',
            // hide, hide_perusahaan, hide_materi, makanan: sudah punya default di database
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function workshop(): static
    {
        return $this->state(fn () => ['event' => 'Workshop']);
    }
}