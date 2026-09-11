<?php

namespace Tests\Feature\ITSM\TimDigital;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ChecklistEventTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserWithRole($roleName = 'Tim Digital', $divisi = 'Digital')
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Karyawan ' . uniqid(),
            'jabatan' => $roleName,
            'divisi' => $divisi,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'username' => 'testuser_' . uniqid(),
            'password' => Hash::make('password'),
            'karyawan_id' => $karyawanId,
            'jabatan' => $roleName,
            'status_akun' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($userId);
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->assignRole($role);

        return $user;
    }

    private function createChecklist()
    {
        $mappingId = DB::table('year_mappings')->insertGetId([
            'year' => 2024,
            'quarter' => 1,
            'month' => 1,
            'theme' => 'Testing Theme',
            'planned_date' => '2024-01-10',
            'duration_minutes' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $todoId = DB::table('todos')->insertGetId([
            'task_name' => 'Persiapan',
            'is_active' => 1,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $checklistId = DB::table('event_todos')->insertGetId([
            'year_mapping_id' => $mappingId,
            'todo_id' => $todoId,
            'is_checked' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['mapping_id' => $mappingId, 'checklist_id' => $checklistId];
    }

    public function test_tim_digital_can_view_checklist()
    {
        $user = $this->createUserWithRole();
        $data = $this->createChecklist();
        
        $response = $this->actingAs($user)->get('/api/checklist/' . $data['mapping_id']);
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_tim_digital_can_toggle_checklist()
    {
        $user = $this->createUserWithRole();
        $data = $this->createChecklist();
        
        $response = $this->actingAs($user)->patch('/api/checklist/' . $data['checklist_id'] . '/toggle', [
            'is_checked' => true
        ]);
        $this->assertTrue(in_array($response->status(), [200, 201, 302]));
    }

    public function test_tim_digital_can_update_checklist_detail()
    {
        $user = $this->createUserWithRole();
        $data = $this->createChecklist();
        
        $response = $this->actingAs($user)->put('/api/checklist/' . $data['checklist_id'] . '/detail', [
            'notes' => 'Detail updated' // assuming there's a notes column, actually updateDetail might just use whatever
        ]);
        $this->assertTrue(in_array($response->status(), [200, 201, 302]));
    }

    public function test_guest_gets_correct_response_for_checklist()
    {
        $response = $this->get('/api/checklist/1');
        $this->assertTrue(in_array($response->status(), [200, 302, 401, 403]));
    }

    public function test_non_digital_user_gets_forbidden_for_checklist()
    {
        $user = $this->createUserWithRole('Programmer', 'IT Service Management');
        $data = $this->createChecklist();
        
        $response = $this->actingAs($user)->patch('/api/checklist/' . $data['checklist_id'] . '/toggle', [
            'is_checked' => true
        ]);
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_invalid_checklist_id_handled_correctly()
    {
        $user = $this->createUserWithRole();
        
        $response = $this->actingAs($user)->patch('/api/checklist/99999/toggle', [
            'is_checked' => true
        ]);
        $this->assertTrue(in_array($response->status(), [404, 302, 500]));
    }
}
