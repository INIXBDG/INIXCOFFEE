<?php

namespace Tests\Feature;

use App\Http\Controllers\TicketController;
use App\Models\Tickets;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TicketDeletionAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(string $jabatan, string $divisi): User
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Delete Test '.uniqid(),
            'jabatan' => $jabatan,
            'divisi' => $divisi,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'username' => 'delete_test_'.uniqid(),
            'password' => Hash::make('password'),
            'karyawan_id' => $karyawanId,
            'jabatan' => $jabatan,
            'status_akun' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($userId);
    }

    private function createTicket(): int
    {
        return DB::table('tickets')->insertGetId([
            'ticket_id' => 'DEL-'.uniqid(),
            'nama_karyawan' => 'Delete Test User',
            'divisi' => 'IT',
            'kategori' => 'Hardware',
            'keperluan' => 'Testing',
            'detail_kendala' => 'Ticket deletion test',
            'timestamp' => now(),
            'status' => 'Menunggu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_itsm_coordinator_can_delete_ticket(): void
    {
        $user = $this->createUser('Koordinator ITSM', 'IT Service Management');
        $ticketId = $this->createTicket();

        $this->actingAs($user);
        $response = app(TicketController::class)->destroy(Tickets::findOrFail($ticketId));

        $this->assertSame(route('tickets.index'), $response->getTargetUrl());
        $this->assertDatabaseMissing('tickets', ['id' => $ticketId]);
    }

    public function test_non_coordinator_cannot_delete_ticket(): void
    {
        $user = $this->createUser('Programmer', 'IT Service Management');
        $ticketId = $this->createTicket();

        $response = $this->withoutMiddleware()->actingAs($user)->delete('/tickets/'.$ticketId);

        $response->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $ticketId]);
    }
}