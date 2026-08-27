<?php

namespace Tests\Unit;

use App\Models\PoExamSertifa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoExamSertifaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_read_po_exam_sertifa_record(): void
    {
        $record = PoExamSertifa::create([
            'id_materi' => 1,
            'id_rkm' => 2,
            'tanggal_exam' => '2026-08-10',
            'id_perusahaan' => 3,
            'pax' => 25,
            'harga' => 150000,
        ]);

        $this->assertDatabaseHas('po_exam_sertifa', [
            'id' => $record->id,
            'id_materi' => 1,
            'id_rkm' => 2,
            'pax' => 25,
            'harga' => 150000,
        ]);

        $found = PoExamSertifa::find($record->id);
        $this->assertNotNull($found);
        $this->assertSame('2026-08-10', $found->tanggal_exam);
    }
}
