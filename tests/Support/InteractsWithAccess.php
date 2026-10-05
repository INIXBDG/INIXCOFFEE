<?php

namespace Tests\Support;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait InteractsWithAccess
{
    protected function setUpAccess(): void
    {
        // Pengaman: jangan pernah jalan di database selain testing.
        $this->assertSame('inixcoffee_testing', DB::getDatabaseName());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function userWithPermissions(array $permissions = []): User
    {
        $user = UserFactory::new()->create();

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $user->givePermissionTo($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    protected function userWithRole(string $role): User
    {
        $user = UserFactory::new()->create();
        $user->assignRole(Role::findByName($role, 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    protected function loginAs(User $user): static
    {
        return $this->actingAs($user)->withSession(['last_activity' => time()]);
    }

    // Bentuk penolakan: 403, atau redirect ke /login.
    // Perketat setelah kita lihat bentuk penolakan sebenarnya dari hasil test.
    protected function isDenied(TestResponse $r): bool
    {
        return $r->getStatusCode() === 403
            || ($r->isRedirect() && str_contains((string) $r->headers->get('Location'), '/login'));
    }

    protected function assertDenied(TestResponse $r): void
    {
        $this->assertTrue(
            $this->isDenied($r),
            "Seharusnya ditolak, tapi status {$r->getStatusCode()} (Location: {$r->headers->get('Location')})"
        );
    }

    protected function assertNotDenied(TestResponse $r): void
    {
        $this->assertFalse(
            $this->isDenied($r),
            "Seharusnya lolos, tapi ditolak: status {$r->getStatusCode()} (Location: {$r->headers->get('Location')})"
        );
    }
}