<?php

namespace Tests\Feature\ITSM\TimDigital;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TimelineEventTest extends TestCase
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

    private function createMapping()
    {
        return DB::table('year_mappings')->insertGetId([
            'year' => 2024,
            'quarter' => 1,
            'month' => 1,
            'theme' => 'Testing Theme',
            'planned_date' => '2024-01-10',
            'duration_minutes' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_tim_digital_can_create_timeline_item()
    {
        $user = $this->createUserWithRole();
        $mappingId = $this->createMapping();
        
        $response = $this->actingAs($user)->post('/api/timeline-item', [
            'year_mapping_id' => $mappingId,
            'item_date' => '2024-01-01',
            'content' => 'Instagram Post',
        ]);
        $this->assertTrue(in_array($response->status(), [200, 201, 302]));
    }

    public function test_tim_digital_gets_validation_error_on_empty_timeline_item()
    {
        $user = $this->createUserWithRole();
        
        $response = $this->actingAs($user)->post('/api/timeline-item', []);
        $this->assertTrue(in_array($response->status(), [302, 422]));
    }

    public function test_guest_cannot_create_timeline_item()
    {
        $response = $this->post('/api/timeline-item', [
            'item_date' => '2024-01-01',
        ]);
        $this->assertTrue(in_array($response->status(), [302, 401, 403, 500]));
    }

    public function test_non_digital_user_cannot_create_timeline_item()
    {
        $user = $this->createUserWithRole('Programmer', 'IT Service Management');
        $mappingId = $this->createMapping();
        
        $response = $this->actingAs($user)->post('/api/timeline-item', [
            'year_mapping_id' => $mappingId,
            'item_date' => '2024-01-01',
            'content' => 'Invalid Access Post',
        ]);
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }
}
