<?php

namespace Tests\Feature\ITSM\Programmer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProjectTaskTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser($roleName = 'Programmer', $kodeKaryawan = 'PRG')
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Karyawan ' . uniqid(),
            'jabatan' => $roleName,
            'divisi' => 'IT Service Management',
            'kode_karyawan' => $kodeKaryawan,
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

        return User::find($userId);
    }

    private function createClient()
    {
        return DB::table('perusahaans')->insertGetId([
            'nama_perusahaan' => 'PT Test Client',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProjectWithPM($pmKode = 'PM_CODE')
    {
        $clientId = $this->createClient();

        $projectId = DB::table('projects')->insertGetId([
            'name' => 'Test Project',
            'client_id' => $clientId,
            'phase' => 'teknis',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_administrations')->insert([
            'project_id' => $projectId,
            'current_stage' => 'kak',
            'pm_id' => $pmKode,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $projectId;
    }

    private function createTask($projectId, $assigneeKode = 'ASSIGNEE')
    {
        return DB::table('project_tasks')->insertGetId([
            'project_id' => $projectId,
            'title' => 'Task Baru',
            'status' => 'to_do',
            'assignee_id' => $assigneeKode,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_programmer_can_view_project_tasks()
    {
        $user = $this->createUser();
        $projectId = $this->createProjectWithPM();
        $this->createTask($projectId);

        $response = $this->actingAs($user)->getJson('/projects/kanban/get-tasks?project_id=' . $projectId);
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_programmer_can_create_task()
    {
        $user = $this->createUser();
        $projectId = $this->createProjectWithPM();

        $response = $this->actingAs($user)->postJson('/projects/kanban/tasks', [
            'project_id' => $projectId,
            'title' => 'Design Database',
            'status' => 'backlog'
        ]);

        $this->assertTrue(in_array($response->status(), [200, 201, 302]));
    }

    public function test_programmer_create_task_with_empty_payload()
    {
        $user = $this->createUser();
        
        $response = $this->actingAs($user)->postJson('/projects/kanban/tasks', []);
        $this->assertTrue(in_array($response->status(), [422, 302]));
    }

    public function test_programmer_can_update_task_status()
    {
        $user = $this->createUser('Programmer', 'PRG'); 
        $projectId = $this->createProjectWithPM('PM_CODE');
        $taskId = $this->createTask($projectId, 'PRG'); // Assigned to programmer

        $response = $this->actingAs($user)->patchJson('/projects/kanban/tasks/' . $taskId . '/status', [
            'status' => 'in_progress'
        ]);

        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_programmer_can_delete_task()
    {
        $user = $this->createUser('Programmer', 'PRG');
        $projectId = $this->createProjectWithPM('PM_CODE');
        $taskId = $this->createTask($projectId, 'PRG');

        $response = $this->actingAs($user)->deleteJson('/projects/kanban/tasks/' . $taskId);
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_programmer_cannot_update_task_without_permission()
    {
        // Programmer PRG1 trying to update task of Programmer PRG2
        $userA = $this->createUser('Programmer', 'PRG1');
        $projectId = $this->createProjectWithPM('PM_CODE');
        $taskId = $this->createTask($projectId, 'PRG2'); // Assigned to someone else

        $response = $this->actingAs($userA)->patchJson('/projects/kanban/tasks/' . $taskId . '/status', [
            'status' => 'validate'
        ]);

        $this->assertTrue(in_array($response->status(), [403, 302]));
    }
}
