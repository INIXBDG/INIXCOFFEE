<?php

namespace Database\Factories;

use App\Models\NomorModul;
use Illuminate\Database\Eloquent\Factories\Factory;

class NomorModulFactory extends Factory
{
    protected $model = NomorModul::class;

    public function definition(): array
    {
        $tanggalSubscode = fake()->dateTimeBetween('-1 year', 'now');
        $tanggalTenggat = fake()->dateTimeBetween(
            $tanggalSubscode,
            $tanggalSubscode->format('Y-m-d') . ' +30 days'
        );

        return [
            'no_modul'                => fake()->unique()->bothify('MOD-2026-####'),
            'type'                    => fake()->randomElement([
                'Regular',
                'Authorize',
            ]),
            'status'                  => fake()->randomElement([
                'Menunggu',
                'Disetujui',
                'Uploaded',
            ]),
            'delay'                   => fake()->randomElement([
                'client',
                'admin',
            ]),
            'keterangan'              => fake()->optional()->sentence(),
            'uploaded'                => fake()->optional()->dateTimeBetween(
                '-1 year',
                'now'
            ),
            'note_modul'              => fake()->optional()->sentence(),
            'note_peserta'            => fake()->optional()->sentence(),
            'status_subscode'         => fake()->boolean(),
            'tanggal_subscode_masuk'  => $tanggalSubscode,
            'tanggal_tenggat'         => $tanggalTenggat,
            'catatan'                 => fake()->optional()->sentence(),
        ];
    }
}