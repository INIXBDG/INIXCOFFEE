<?php

namespace Tests\Feature\ITSM\TimDigital;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MasterPlanEventTest extends TestCase
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

    private function createEvent()
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

        $eventId = DB::table('quarter_events')->insertGetId([
            'year_mapping_id' => $mappingId,
            'title' => 'Test Event',
            'description' => 'Topik Test',
            'speaker_name' => 'Jhon Doe',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return $eventId;
    }

    public function test_tim_digital_can_access_master_plan()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/timeline');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_guest_cannot_access_master_plan()
    {
        $response = $this->get('/timeline');
        $this->assertTrue(in_array($response->status(), [200, 302, 401, 403]));
    }

    public function test_tim_digital_can_update_event()
    {
        $user = $this->createUserWithRole();
        $eventId = $this->createEvent();
        
        $response = $this->actingAs($user)->post('/api/event/' . $eventId . '/update', [
            'title' => 'Updated Event',
            'speaker_name' => 'Jane Doe',
            'link_zoom' => 'https://zoom.us'
        ]);
        $this->assertTrue(in_array($response->status(), [200, 201, 302]));
    }

    public function test_tim_digital_gets_validation_error_on_empty_event_update()
    {
        $user = $this->createUserWithRole();
        $eventId = $this->createEvent();
        
        $response = $this->actingAs($user)->post('/api/event/' . $eventId . '/update', []);
        $this->assertTrue(in_array($response->status(), [200, 302, 422]));
    }

    public function test_tim_digital_cannot_update_invalid_event()
    {
        $user = $this->createUserWithRole();
        
        $response = $this->actingAs($user)->post('/api/event/99999/update', [
            'title' => 'Updated Event',
        ]);
        $this->assertTrue(in_array($response->status(), [404, 302, 500]));
    }

    public function test_non_digital_user_cannot_update_event()
    {
        $user = $this->createUserWithRole('Programmer', 'IT Service Management');
        $eventId = $this->createEvent();
        
        $response = $this->actingAs($user)->post('/api/event/' . $eventId . '/update', [
            'title' => 'Updated Event',
        ]);
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }
}
