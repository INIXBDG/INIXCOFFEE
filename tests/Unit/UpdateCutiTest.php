<?php

namespace Tests\Unit;

use App\Console\Commands\UpdateCuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class UpdateCutiTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_logs_when_update_is_skipped_outside_february(): void
    {
        Carbon::setTestNow('2026-11-25 11:00:00');
        Log::shouldReceive('info')
            ->once()
            ->with('app:update-cuti started', Mockery::type('array'));
        Log::shouldReceive('info')
            ->once()
            ->with('app:update-cuti skipped', [
                'reason' => 'outside_february',
                'month' => 11,
            ]);
        DB::shouldReceive('table')->never();

        $this->artisan('app:update-cuti')
            ->expectsOutput('Bukan bulan Februari, update cuti dibatalkan.')
            ->assertExitCode(UpdateCuti::SUCCESS);
    }

    public function test_it_updates_cuti_and_logs_the_result_in_february(): void
    {
        Carbon::setTestNow('2027-02-01 00:01:00');
        Log::shouldReceive('info')
            ->once()
            ->with('app:update-cuti started', Mockery::type('array'));
        Log::shouldReceive('info')
            ->once()
            ->with('app:update-cuti completed', Mockery::on(function (array $context): bool {
                return $context['updated_rows'] === 3
                    && Carbon::parse($context['executed_at'])->equalTo(Carbon::now());
            }));

        $query = Mockery::mock();
        $this->expectSixMonthEligibilityScope($query, '2026-08-01');
        $query->shouldReceive('update')
            ->once()
            ->with(Mockery::on(fn (array $values): bool => $values['cuti'] === 12))
            ->andReturn(3);
        DB::shouldReceive('table')->once()->with('karyawans')->andReturn($query);

        $this->artisan('app:update-cuti')
            ->expectsOutput('Jatah cuti berhasil di update. Total karyawan diperbarui: 3.')
            ->assertExitCode(UpdateCuti::SUCCESS);
    }

    public function test_it_logs_and_rethrows_update_failures(): void
    {
        Carbon::setTestNow('2027-02-01 00:01:00');
        Log::shouldReceive('info')
            ->once()
            ->with('app:update-cuti started', Mockery::type('array'));
        Log::shouldReceive('error')
            ->once()
            ->with('app:update-cuti failed', Mockery::on(function (array $context): bool {
                return $context['error'] === 'database unavailable';
            }));

        $query = Mockery::mock();
        $this->expectSixMonthEligibilityScope($query, '2026-08-01');
        $query->shouldReceive('update')->once()->andThrow(new \RuntimeException('database unavailable'));
        DB::shouldReceive('table')->once()->with('karyawans')->andReturn($query);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('database unavailable');

        $this->artisan('app:update-cuti');
    }

    private function expectSixMonthEligibilityScope($query, string $cutoffDate): void
    {
        $scope = Mockery::mock();
        $scope->shouldReceive('whereNull')
            ->once()
            ->with('awal_kontrak')
            ->andReturnSelf();
        $scope->shouldReceive('orWhereDate')
            ->once()
            ->with('awal_kontrak', '<=', $cutoffDate)
            ->andReturnSelf();

        $query->shouldReceive('where')
            ->once()
            ->with(Mockery::on(function (callable $constraint) use ($scope): bool {
                $constraint($scope);

                return true;
            }))
            ->andReturnSelf();
    }
}
