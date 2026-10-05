<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');
        $endDate = (clone $startDate)->modify('+2 days');

        return [
            'nomor_sertifikat'   => fake()->unique()->numerify('CERT-2026-####'),
            'rkm_id'             => RKMFactory::new(),
            'id_peserta'         => PesertaFactory::new(),
            'nama_peserta'       => fake()->name(),
            'nama_materi'        => fake()->sentence(3),
            'tanggal_pelatihan'  => $startDate->format('Y-m-d')
                . ' - '
                . $endDate->format('Y-m-d'),
            'tanggal_pelatihan2' => null,
            'pdf_path'           => null,
        ];
    }

    public function denganPdf(string $path = 'certificates/contoh.pdf'): static
    {
        return $this->state(fn () => [
            'pdf_path' => $path,
        ]);
    }
}
