<?php

namespace Tests\Feature;

use App\Http\Middleware\ApiBasicAuth;
use App\Http\Middleware\SecurityHeaders;
use App\Models\karyawan;
use App\Models\User;
use App\Policies\KaryawanPolicy;
use App\Support\CacheRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityAndPerformanceHardeningTest extends TestCase
{
    public function test_phpinfo_file_is_removed_from_public_directory(): void
    {
        $this->assertFileDoesNotExist(public_path('phpinfo.php'));
    }

    public function test_critical_admin_and_impersonation_routes_enforce_strict_middleware(): void
    {
        $devProtectedRoutes = [
            'change.user',
            'user.dropdown',
            'indexUser',
            'editUser',
            'updateUser',
            'dbstan.index',
        ];

        foreach ($devProtectedRoutes as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route [$routeName] harus terdaftar.");
            $middlewares = $route->gatherMiddleware();
            $this->assertContains('auth', $middlewares, "Route [$routeName] wajib memiliki middleware auth.");
            $this->assertContains(
                'permission:Akses Development',
                $middlewares,
                "Route [$routeName] wajib memiliki middleware permission:Akses Development."
            );
        }

        $queueRoute = Route::getRoutes()->getByName('queue-monitor::index');
        if ($queueRoute !== null) {
            $middlewares = $queueRoute->gatherMiddleware();
            $this->assertContains('auth', $middlewares);
            $this->assertContains('permission:Akses Development', $middlewares);
        }

        foreach (['user.register', 'user.registkaryawan'] as $adminRouteName) {
            $route = Route::getRoutes()->getByName($adminRouteName);
            $this->assertNotNull($route);
            $middlewares = $route->gatherMiddleware();
            $this->assertContains('auth', $middlewares);
            $this->assertContains('Admin', $middlewares);
        }
    }

    public function test_previously_exposed_routes_now_require_auth_and_safe_http_methods(): void
    {
        foreach (['cv-instruktur.index', 'cv-instruktur.data', 'cv-instruktur.show', 'cv-instruktur.pdf', 'perusahaan.merge'] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Route [$name] harus terdaftar.");
            $this->assertContains('auth', $route->gatherMiddleware(), "Route [$name] wajib dilindungi auth.");
        }

        $destructiveRoutes = [
            'hapus.laporanInsiden',
            'expensehub.destroy',
            'office.stockOpname.delete',
        ];

        foreach ($destructiveRoutes as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route);
            $this->assertContains('DELETE', $route->methods(), "Route [$name] wajib memakai method DELETE.");
            $this->assertNotContains('GET', $route->methods(), "Route [$name] tidak boleh memakai method GET.");
        }

        $this->assertNull(Route::getRoutes()->getByName('rkmAPI.index'), 'Endpoint terbuka rkmAPI harus sudah dihapus.');
    }

    public function test_all_routes_are_free_of_closures_for_route_cache_compatibility(): void
    {
        foreach (Route::getRoutes() as $route) {
            $uses = $route->getAction()['uses'] ?? null;
            $this->assertFalse(
                $uses instanceof Closure,
                sprintf('Route [%s] (%s) masih menggunakan Closure.', $route->uri(), implode(',', $route->methods()))
            );
        }
    }

    public function test_no_direct_env_calls_exist_in_app_or_views_for_config_cache_compatibility(): void
    {
        $directories = [app_path(), resource_path('views')];

        foreach ($directories as $dir) {
            foreach (File::allFiles($dir) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }
                if (str_contains($file->getPathname(), 'Console/Commands/Audit')) {
                    continue;
                }

                $contents = File::get($file->getPathname());
                $this->assertDoesNotMatchRegularExpression(
                    '/(?<![.>$\w])env\s*\(/',
                    $contents,
                    'Pemanggilan env() langsung ditemukan di luar config/: ' . $file->getRelativePathname()
                );
            }
        }
    }

    public function test_api_basic_auth_fails_closed_and_verifies_credentials_safely(): void
    {
        $middleware = new ApiBasicAuth();

        config(['services.internal_api.username' => '', 'services.internal_api.password' => '']);
        $requestWithoutConfig = Request::create('/api/Protected', 'GET');
        $responseEmptyConfig = $middleware->handle($requestWithoutConfig, fn () => new Response('OK', 200));
        $this->assertSame(500, $responseEmptyConfig->getStatusCode());

        config(['services.internal_api.username' => 'inix_api', 'services.internal_api.password' => 's3cr3t_p4ss']);

        $unauthenticatedRequest = Request::create('/api/Protected', 'GET');
        $responseUnauth = $middleware->handle($unauthenticatedRequest, fn () => new Response('OK', 200));
        $this->assertSame(401, $responseUnauth->getStatusCode());

        $validRequest = Request::create('/api/Protected', 'GET', [], [], [], [
            'PHP_AUTH_USER' => 'inix_api',
            'PHP_AUTH_PW' => 's3cr3t_p4ss',
        ]);
        $responseValid = $middleware->handle($validRequest, fn () => new Response('OK', 200));
        $this->assertSame(200, $responseValid->getStatusCode());
    }

    public function test_security_headers_middleware_attaches_hardening_headers(): void
    {
        $middleware = new SecurityHeaders();
        $request = Request::create('/login', 'GET');

        $response = $middleware->handle($request, fn () => new Response('OK', 200));

        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertNotEmpty($response->headers->get('Permissions-Policy'));
    }

    public function test_karyawan_policy_enforces_ownership_and_admin_privileges(): void
    {
        Cache::put(config('permission.cache.key', 'spatie.permission.cache'), [
            'alias' => [],
            'permissions' => [],
            'roles' => [],
        ], 60);

        $policy = new KaryawanPolicy();

        $targetKaryawan = new karyawan();
        $targetKaryawan->id = 42;

        $ownerUser = new User();
        $ownerUser->id = 100;
        $ownerUser->karyawan_id = 42;
        $ownerUser->jabatan = 'Instruktur';

        $otherUser = new User();
        $otherUser->id = 101;
        $otherUser->karyawan_id = 99;
        $otherUser->jabatan = 'Instruktur';
        $otherUser->setRelation('roles', collect());
        $otherUser->setRelation('permissions', collect());

        $hrdUser = new User();
        $hrdUser->id = 102;
        $hrdUser->karyawan_id = 1;
        $hrdUser->jabatan = 'HRD';

        $this->assertTrue($policy->update($ownerUser, $targetKaryawan, $ownerUser));
        $this->assertFalse($policy->update($otherUser, $targetKaryawan, $ownerUser));
        $this->assertTrue($policy->manageAll($hrdUser));
        $this->assertTrue($policy->update($hrdUser, $targetKaryawan, $ownerUser));
    }

    public function test_cache_registry_rotates_namespace_version_without_flushing_entire_cache(): void
    {
        Cache::put('unrelated_key', 'must_survive', 60);

        $v1 = CacheRegistry::version(CacheRegistry::RKM_VERSION_KEY);
        $key1 = CacheRegistry::rkmKey('month:2026:11');
        $v2 = CacheRegistry::bumpRkmVersion();
        $key2 = CacheRegistry::rkmKey('month:2026:11');

        $this->assertSame($v1 + 1, $v2);
        $this->assertNotSame($key1, $key2);
        $this->assertSame('must_survive', Cache::get('unrelated_key'));
    }
}
