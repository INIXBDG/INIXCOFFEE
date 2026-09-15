<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\checklistRKMController;
use App\Models\RKM;
use App\Models\checklistRKM;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ChecklistRKMTest extends TestCase
{
    public function test_checklist_rkm_feature_exposes_controller_routes_and_view(): void
    {
        $this->assertTrue(class_exists(checklistRKMController::class));
        $this->assertTrue(method_exists(checklistRKMController::class, 'getData'));
        $this->assertTrue(method_exists(checklistRKMController::class, 'updateChecklist'));
        $this->assertTrue(method_exists(checklistRKMController::class, 'updateMultiple'));
        $this->assertTrue(Route::has('crm.checklist-rkm.index'));
        $this->assertTrue(Route::has('crm.checklist-rkm.data'));
        $this->assertTrue(Route::has('crm.checklist-rkm.checklist.update'));
        $this->assertTrue(View::exists('crm.checklistRKM.index'));
    }

    public function test_checklist_rkm_belongs_to_rkm(): void
    {
        $checklist = new checklistRKM(['id_rkm' => 25, 'registrasi_form' => '1', 'PA' => '1']);

        $this->assertSame('1', $checklist->registrasi_form);
        $this->assertSame('1', $checklist->PA);
        $this->assertInstanceOf(BelongsTo::class, $checklist->rkm());
        $this->assertSame(RKM::class, $checklist->rkm()->getRelated()::class);
    }
}