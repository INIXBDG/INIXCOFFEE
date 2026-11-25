<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class UpdateCutiKontrakTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_fills_cutis_after_six_months_for_contracts_due_this_year(): void
    {
        Carbon::setTestNow('2027-01-01 03:00:00');
        Log::shouldReceive('info')
            ->once()
            ->with('app:update-cuti-kontrak started', [
                'executed_at' => Carbon::now()->toIso8601String(),
                'contract_start_from' => '2026-06-01',
                'contract_start_until' => '2026-07-01',
            ]);
        Log::shouldReceive('info')
            ->once()
            ->with('app:update-cuti-kontrak completed', Mockery::on(function (array $context): bool {
                return $context['updated_rows'] === 3;
            }));
        $selectQuery = Mockery::mock();
        $selectQuery->shouldReceive('select')
            ->once()
            ->with('id', 'awal_kontrak', 'cuti')
            ->andReturnSelf();
        $selectQuery->shouldReceive('whereDate')
            ->once()
            ->with('awal_kontrak', '>=', '2026-06-01')
            ->andReturnSelf();
        $selectQuery->shouldReceive('whereDate')
            ->once()
            ->with('awal_kontrak', '<=', '2026-07-01')
            ->andReturnSelf();
        $selectQuery->shouldReceive('get')
            ->once()
            ->andReturn(collect([
                (object) ['id' => 1, 'awal_kontrak' => '2026-06-15', 'cuti' => null],
                (object) ['id' => 2, 'awal_kontrak' => '2026-07-01', 'cuti' => '-'],
                (object) ['id' => 3, 'awal_kontrak' => '2026-07-02', 'cuti' => ''],
                (object) ['id' => 4, 'awal_kontrak' => '2026-07-01', 'cuti' => 0],
                (object) ['id' => 5, 'awal_kontrak' => '2026-05-31', 'cuti' => null],
                (object) ['id' => 6, 'awal_kontrak' => '2027-01-01', 'cuti' => null],
                (object) ['id' => 7, 'awal_kontrak' => '2026-06-20', 'cuti' => ''],
            ]));

        $updateQueries = [
            $this->makeUpdateQuery(1, null),
            $this->makeUpdateQuery(2, '-'),
            $this->makeUpdateQuery(7, ''),
        ];

        DB::shouldReceive('table')
            ->times(4)
            ->with('karyawans')
            ->andReturn($selectQuery, ...$updateQueries);

        $this->artisan('app:update-cuti-kontrak')
            ->expectsOutput('Pemeriksaan cuti kontrak selesai. Total karyawan diperbarui: 3.')
            ->assertExitCode(0);
    }

    private function makeUpdateQuery(int $id, ?string $missingCuti): Mockery\MockInterface
    {
        $query = Mockery::mock();
        $query->shouldReceive('where')
            ->once()
            ->with('id', $id)
            ->andReturnSelf();

        if ($missingCuti === null) {
            $query->shouldReceive('whereNull')
                ->once()
                ->with('cuti')
                ->andReturnSelf();
        } else {
            $query->shouldReceive('where')
                ->once()
                ->with('cuti', $missingCuti)
                ->andReturnSelf();
        }

        $query->shouldReceive('update')
            ->once()
            ->with(Mockery::on(fn (array $values): bool => $values['cuti'] === 12))
            ->andReturn(1);

        return $query;
    }
}
