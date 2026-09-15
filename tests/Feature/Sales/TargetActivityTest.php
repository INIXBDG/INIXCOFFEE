<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\TargetAktivitas;
use App\Models\Aktivitas;
use App\Models\TargetActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class TargetActivityTest extends TestCase
{
    public function test_target_activity_feature_exposes_controller_crud_routes_and_view(): void
    {
        $this->assertTrue(class_exists(TargetAktivitas::class));
        $this->assertTrue(method_exists(TargetAktivitas::class, 'index'));
        $this->assertTrue(method_exists(TargetAktivitas::class, 'store'));
        $this->assertTrue(method_exists(TargetAktivitas::class, 'update'));
        $this->assertTrue(method_exists(TargetAktivitas::class, 'delete'));
        $this->assertTrue(Route::has('index.target'));
        $this->assertTrue(Route::has('index.target.store'));
        $this->assertTrue(Route::has('index.target.update'));
        $this->assertTrue(Route::has('index.target.delete'));
        $this->assertTrue(View::exists('crm.target.index'));
    }

    public function test_target_activity_accepts_sales_activity_targets(): void
    {
        $attributes = [
            'id_sales' => 'SLS001',
            'Contact' => 10,
            'Call' => 20,
            'Visit' => 5,
            'Email' => 15,
            'Meet' => 8,
            'DB' => 4,
            'PA' => 3,
            'PI' => 6,
            'Incharge' => 2,
            'Telemarketing' => 12,
            'FormM' => 5,
            'FormK' => 5,
        ];

        $target = new TargetActivity($attributes);

        $this->assertSame($attributes, $target->only(array_keys($attributes)));
    }

    public function test_target_activity_has_many_sales_activities_by_sales_id(): void
    {
        $target = new TargetActivity;

        $this->assertInstanceOf(HasMany::class, $target->aktivitas());
        $this->assertSame(Aktivitas::class, $target->aktivitas()->getRelated()::class);
        $this->assertSame('id_sales', $target->aktivitas()->getForeignKeyName());
        $this->assertSame('id_sales', $target->aktivitas()->getLocalKeyName());
    }
}