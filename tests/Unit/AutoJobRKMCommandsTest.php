<?php

namespace Tests\Unit;

use App\Console\Commands\AutoJobRKMCommands;
use App\Models\RKM;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AutoJobRKMCommandsTest extends TestCase
{
    public function test_matching_rkm_group_fills_each_empty_assignment_from_existing_values(): void
    {
        $instructorSource = new InMemoryRKM([
            'instruktur_key' => 'INS001',
            'instruktur_key2' => '',
            'asisten_key' => '-',
        ]);
        $assistantSource = new InMemoryRKM([
            'instruktur_key' => null,
            'instruktur_key2' => 'INS002',
            'asisten_key' => 'AST001',
        ]);
        $emptyRkm = new InMemoryRKM([
            'instruktur_key' => null,
            'instruktur_key2' => '',
            'asisten_key' => null,
        ]);

        $command = new TestableAutoJobRKMCommands;
        $filledColumns = $command->fillGroup(new Collection([
            $instructorSource,
            $assistantSource,
            $emptyRkm,
        ]));

        $this->assertSame(6, $filledColumns);
        $this->assertSame('INS001', $instructorSource->instruktur_key);
        $this->assertSame('INS002', $instructorSource->instruktur_key2);
        $this->assertSame('AST001', $instructorSource->asisten_key);
        $this->assertSame('INS001', $assistantSource->instruktur_key);
        $this->assertSame('INS001', $emptyRkm->instruktur_key);
        $this->assertSame('INS002', $emptyRkm->instruktur_key2);
        $this->assertSame('AST001', $emptyRkm->asisten_key);
        $this->assertTrue($instructorSource->wasSaved);
        $this->assertTrue($assistantSource->wasSaved);
        $this->assertTrue($emptyRkm->wasSaved);
    }

    public function test_rkm_group_key_uses_materi_start_date_method_and_room_only(): void
    {
        $command = new TestableAutoJobRKMCommands;
        $base = [
            'materi_key' => 'MAT001',
            'tanggal_awal' => '2026-11-23',
            'tanggal_akhir' => '2026-11-24',
            'metode_kelas' => 'In House',
            'ruang' => 'Ruang 1',
        ];
        $baseKey = $command->keyFor(new RKM($base));

        $sameGroup = $command->keyFor(new RKM(array_merge($base, [
            'tanggal_akhir' => '2026-11-25',
        ])));
        $this->assertSame($baseKey, $sameGroup);

        foreach ([
            'materi_key' => 'MAT002',
            'tanggal_awal' => '2026-11-24',
            'metode_kelas' => 'Online',
            'ruang' => 'Ruang 2',
        ] as $field => $differentValue) {
            $this->assertNotSame(
                $baseKey,
                $command->keyFor(new RKM(array_merge($base, [$field => $differentValue])))
            );
        }
    }

    public function test_group_without_any_existing_assignments_is_left_unchanged(): void
    {
        $rkm = new InMemoryRKM([
            'instruktur_key' => null,
            'instruktur_key2' => '',
            'asisten_key' => '-',
        ]);

        $filledColumns = (new TestableAutoJobRKMCommands)
            ->fillGroup(new Collection([$rkm]));

        $this->assertSame(0, $filledColumns);
        $this->assertFalse($rkm->wasSaved);
    }
}

class TestableAutoJobRKMCommands extends AutoJobRKMCommands
{
    public function fillGroup(Collection $group): int
    {
        return $this->fillMissingAssignments($group);
    }

    public function keyFor(RKM $rkm): string
    {
        return $this->assignmentGroupKey($rkm);
    }
}

class InMemoryRKM extends RKM
{
    public bool $wasSaved = false;

    public function saveQuietly(array $options = [])
    {
        $this->wasSaved = true;

        return true;
    }
}
