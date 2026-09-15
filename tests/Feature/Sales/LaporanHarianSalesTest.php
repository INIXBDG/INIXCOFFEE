<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\LaporanHarianSalesController;
use App\Models\CatatanClientSales;
use App\Models\CatatanMeetingSales;
use App\Models\LaporanHarianSales;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LaporanHarianSalesTest extends TestCase
{
    public function test_laporan_harian_feature_exposes_controller_crud_routes_and_views(): void
    {
        $this->assertTrue(class_exists(LaporanHarianSalesController::class));
        $this->assertTrue(method_exists(LaporanHarianSalesController::class, 'index'));
        $this->assertTrue(method_exists(LaporanHarianSalesController::class, 'store'));
        $this->assertTrue(method_exists(LaporanHarianSalesController::class, 'update'));
        $this->assertTrue(method_exists(LaporanHarianSalesController::class, 'delete'));
        $this->assertTrue(Route::has('laporan.harian'));
        $this->assertTrue(Route::has('laporan.harian.store'));
        $this->assertTrue(Route::has('laporan.harian.update'));
        $this->assertTrue(Route::has('laporan.harian.delete'));
        $this->assertTrue(View::exists('crm.laporanHarian.index'));
        $this->assertTrue(View::exists('crm.laporanHarian.create'));
    }

    public function test_laporan_harian_sales_accepts_meeting_report_attributes(): void
    {
        $attributes = [
            'tanggal_pelaksanaan' => '2026-09-03',
            'waktu_pelaksanaan' => '10:00',
            'tempat_or_media' => 'Google Meet',
            'jumlah_peserta_hadir' => 12,
            'jumlah_peserta_tidak_hadir' => 2,
            'jenis_meeting' => 'Client Meeting',
            'topic' => 'Kebutuhan pelatihan',
            'catatan' => 'Menunggu konfirmasi jadwal.',
            'is_draft' => false,
        ];

        $laporan = new LaporanHarianSales($attributes);

        $this->assertSame($attributes, $laporan->only(array_keys($attributes)));
    }

    public function test_laporan_harian_sales_defines_report_note_relations(): void
    {
        $laporan = new LaporanHarianSales;

        $this->assertInstanceOf(BelongsTo::class, $laporan->picMeeting());
        $this->assertSame('pic', $laporan->picMeeting()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $laporan->notulisMeeting());
        $this->assertSame('notulis', $laporan->notulisMeeting()->getForeignKeyName());

        $this->assertInstanceOf(HasMany::class, $laporan->catatanSales());
        $this->assertSame(CatatanMeetingSales::class, $laporan->catatanSales()->getRelated()::class);

        $this->assertInstanceOf(HasMany::class, $laporan->catatanClient());
        $this->assertSame(CatatanClientSales::class, $laporan->catatanClient()->getRelated()::class);
    }
}