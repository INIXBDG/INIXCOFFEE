<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\PeluangController;
use App\Models\Aktivitas;
use App\Models\Materi;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\RegisForm;
use App\Models\RKM;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PeluangTest extends TestCase
{
    public function test_peluang_feature_exposes_controller_routes_and_view(): void
    {
        $this->assertTrue(class_exists(PeluangController::class));
        $this->assertTrue(method_exists(PeluangController::class, 'index'));
        $this->assertTrue(method_exists(PeluangController::class, 'store'));
        $this->assertTrue(method_exists(PeluangController::class, 'update'));
        $this->assertTrue(method_exists(PeluangController::class, 'delete'));
        $this->assertTrue(Route::has('index.peluang'));
        $this->assertTrue(Route::has('store.peluang'));
        $this->assertTrue(Route::has('edit.peluang'));
        $this->assertTrue(Route::has('delete.peluang'));
        $this->assertTrue(View::exists('crm.peluang.index'));
        $this->assertTrue(View::exists('crm.peluang.detail'));
    }

    public function test_peluang_accepts_sales_pipeline_attributes(): void
    {
        $attributes = [
            'id_contact' => 10,
            'id_sales' => 'SLS001',
            'materi' => 'Pelatihan Leadership',
            'harga' => 25000000,
            'netsales' => 22000000,
            'pax' => 20,
            'tahap' => 'biru',
            'tentatif' => false,
        ];

        $peluang = new Peluang($attributes);

        $this->assertSame($attributes, $peluang->only(array_keys($attributes)));
    }

    public function test_peluang_defines_company_and_rkm_relations(): void
    {
        $peluang = new Peluang;

        $this->assertInstanceOf(BelongsTo::class, $peluang->perusahaan());
        $this->assertSame(Perusahaan::class, $peluang->perusahaan()->getRelated()::class);
        $this->assertSame('id_contact', $peluang->perusahaan()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $peluang->rkm());
        $this->assertSame(RKM::class, $peluang->rkm()->getRelated()::class);
        $this->assertSame('id_rkm', $peluang->rkm()->getForeignKeyName());
    }

    public function test_peluang_defines_activity_materi_and_registration_relations(): void
    {
        $peluang = new Peluang;

        $this->assertInstanceOf(HasMany::class, $peluang->aktivitas());
        $this->assertSame(Aktivitas::class, $peluang->aktivitas()->getRelated()::class);
        $this->assertSame('id_peluang', $peluang->aktivitas()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $peluang->materiRelation());
        $this->assertSame(Materi::class, $peluang->materiRelation()->getRelated()::class);

        $this->assertInstanceOf(HasOne::class, $peluang->regis());
        $this->assertSame(RegisForm::class, $peluang->regis()->getRelated()::class);
    }
}