<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\AktivitasController;
use App\Models\Aktivitas;
use App\Models\Contact;
use App\Models\Peluang;
use App\Models\Peserta;
use App\Models\Perusahaan;
use App\Models\TargetActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AktivitasTest extends TestCase
{
    public function test_aktivitas_feature_exposes_controller_crud_routes_and_view(): void
    {
        $this->assertTrue(class_exists(AktivitasController::class));
        $this->assertTrue(method_exists(AktivitasController::class, 'index'));
        $this->assertTrue(method_exists(AktivitasController::class, 'storeNew'));
        $this->assertTrue(method_exists(AktivitasController::class, 'update'));
        $this->assertTrue(method_exists(AktivitasController::class, 'delete'));
        $this->assertTrue(Route::has('index.aktivitas'));
        $this->assertTrue(Route::has('store.aktivitas.new'));
        $this->assertTrue(Route::has('update.aktivitas'));
        $this->assertTrue(Route::has('delete.aktivitas'));
        $this->assertTrue(View::exists('crm.aktivitas.index'));
    }

    public function test_aktivitas_accepts_sales_activity_attributes(): void
    {
        $attributes = [
            'id_sales' => 'SLS001',
            'id_contact' => 10,
            'id_peluang' => 20,
            'aktivitas' => 'Visit',
            'subject' => 'Kunjungan client',
            'deskripsi' => 'Membahas kebutuhan pelatihan',
            'waktu_aktivitas' => '2026-09-03 10:00:00',
            'pax' => 15,
            'total' => 25000000,
        ];

        $aktivitas = new Aktivitas($attributes);

        $this->assertSame($attributes, $aktivitas->only(array_keys($attributes)));
    }

    public function test_aktivitas_defines_opportunity_contact_and_participant_relations(): void
    {
        $aktivitas = new Aktivitas;

        $this->assertInstanceOf(BelongsTo::class, $aktivitas->peluang());
        $this->assertSame(Peluang::class, $aktivitas->peluang()->getRelated()::class);
        $this->assertSame('id_peluang', $aktivitas->peluang()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $aktivitas->contact());
        $this->assertSame(Contact::class, $aktivitas->contact()->getRelated()::class);
        $this->assertSame('id_contact', $aktivitas->contact()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $aktivitas->peserta());
        $this->assertSame(Peserta::class, $aktivitas->peserta()->getRelated()::class);
        $this->assertSame('id_peserta', $aktivitas->peserta()->getForeignKeyName());
    }

    public function test_aktivitas_defines_sales_target_and_company_relations(): void
    {
        $aktivitas = new Aktivitas;

        $this->assertInstanceOf(BelongsTo::class, $aktivitas->target());
        $this->assertSame(TargetActivity::class, $aktivitas->target()->getRelated()::class);
        $this->assertSame('id_sales', $aktivitas->target()->getForeignKeyName());
        $this->assertSame('id_sales', $aktivitas->target()->getOwnerKeyName());

        $this->assertInstanceOf(BelongsTo::class, $aktivitas->perusahaanLangsung());
        $this->assertSame(Perusahaan::class, $aktivitas->perusahaanLangsung()->getRelated()::class);
        $this->assertSame('id_contact', $aktivitas->perusahaanLangsung()->getForeignKeyName());
    }
}