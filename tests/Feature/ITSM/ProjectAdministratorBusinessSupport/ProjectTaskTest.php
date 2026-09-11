<?php

namespace Tests\Feature\ITSM\ProjectAdministratorBusinessSupport;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProjectTaskTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser($roleName = 'Project Administrator & Business Support', $kodeKaryawan = 'AD')
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

    private function createProjectWithPM($pmKode = 'AD')
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

    public function test_user_dapat_melihat_daftar_task_proyek()
    {
        $user = $this->createUser();
        $projectId = $this->createProjectWithPM();
        $this->createTask($projectId);

        $response = $this->actingAs($user)->getJson('/projects/kanban/get-tasks?project_id=' . $projectId);
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_user_dapat_membuat_task()
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

    public function test_user_mengirim_data_kosong_saat_membuat_task()
    {
        $user = $this->createUser();
        
        $response = $this->actingAs($user)->postJson('/projects/kanban/tasks', []);
        $this->assertTrue(in_array($response->status(), [422, 302]));
    }

    public function test_user_dapat_mengubah_status_task()
    {
        $user = $this->createUser('Project Administrator & Business Support', 'AD'); 
        $projectId = $this->createProjectWithPM('AD');
        $taskId = $this->createTask($projectId, 'AD');

        $response = $this->actingAs($user)->patchJson('/projects/kanban/tasks/' . $taskId . '/status', [
            'status' => 'in_progress'
        ]);

        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_user_dapat_menghapus_task()
    {
        $user = $this->createUser();
        $projectId = $this->createProjectWithPM('AD');
        $taskId = $this->createTask($projectId, 'AD');

        $response = $this->actingAs($user)->deleteJson('/projects/kanban/tasks/' . $taskId);
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_user_tanpa_permission_tidak_dapat_mengubah_task()
    {
        $userA = $this->createUser('Programmer', 'KOD_A');
        $projectId = $this->createProjectWithPM('PM_CODE');
        $taskId = $this->createTask($projectId, 'ASSIGNEE_B');

        $response = $this->actingAs($userA)->patchJson('/projects/kanban/tasks/' . $taskId . '/status', [
            'status' => 'validate'
        ]);

        $this->assertTrue(in_array($response->status(), [403, 302]));
    }
}
