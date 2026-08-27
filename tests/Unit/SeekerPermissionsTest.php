<?php

namespace Tests\Unit;

use Azuriom\Models\Permission;
use Azuriom\Models\Role;
use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Middleware\EnsureSeekerAccess;
use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Azuriom\Plugin\Seeker\Support\SeekerPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SeekerPermissionsTest extends TestCase
{
    public function test_seeker_role_permissions_are_registered(): void
    {
        $registered = Permission::permissionsWithName();

        $this->assertSame('seeker::admin.permissions.access', $registered[SeekerPermissions::ACCESS]);
        $this->assertSame('seeker::admin.permissions.publications_create', $registered[SeekerPermissions::CREATE_PUBLICATIONS]);
        $this->assertSame('seeker::admin.permissions.publications_delete', $registered[SeekerPermissions::DELETE_OWN_PUBLICATIONS]);
        $this->assertSame('seeker::admin.permissions.biography_edit', $registered[SeekerPermissions::EDIT_OWN_BIOGRAPHY]);
    }

    public function test_sensitive_routes_have_their_specific_permission_middleware(): void
    {
        $this->assertRouteHasMiddleware('seeker.publications.create', 'can:'.SeekerPermissions::CREATE_PUBLICATIONS);
        $this->assertRouteHasMiddleware('seeker.publications.store', 'can:'.SeekerPermissions::CREATE_PUBLICATIONS);
        $this->assertRouteHasMiddleware('seeker.publications.destroy', 'can:'.SeekerPermissions::DELETE_OWN_PUBLICATIONS);
        $this->assertRouteHasMiddleware('seeker.profiles.edit', 'can:'.SeekerPermissions::EDIT_OWN_BIOGRAPHY);
        $this->assertRouteHasMiddleware('seeker.profiles.update', 'can:'.SeekerPermissions::EDIT_OWN_BIOGRAPHY);

        $updateMiddleware = Route::getRoutes()->getByName('seeker.publications.update')->gatherMiddleware();
        $this->assertNotContains('can:'.SeekerPermissions::CREATE_PUBLICATIONS, $updateMiddleware);
        $this->assertNotContains('can:'.SeekerPermissions::DELETE_OWN_PUBLICATIONS, $updateMiddleware);
    }

    public function test_all_public_seeker_routes_use_the_general_access_middleware(): void
    {
        $seekerRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'seeker.'))
            ->reject(fn ($route) => str_starts_with((string) $route->getName(), 'seeker.admin.'));

        $this->assertNotEmpty($seekerRoutes);

        foreach ($seekerRoutes as $route) {
            $this->assertContains(EnsureSeekerAccess::class, $route->gatherMiddleware(), $route->getName());
        }
    }

    public function test_authenticated_users_need_the_general_access_permission(): void
    {
        $request = Request::create('/seeker');
        $request->setUserResolver(fn () => $this->userWithPermissions());

        try {
            $this->accessMiddleware()->handle($request, fn () => new Response('allowed'));
            $this->fail('The request should have been denied.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_guests_and_permitted_users_can_reach_seeker_routes(): void
    {
        $guestRequest = Request::create('/seeker');
        $userRequest = Request::create('/seeker');
        $userRequest->setUserResolver(fn () => $this->userWithPermissions(SeekerPermissions::ACCESS));
        $next = fn () => new Response('allowed');

        $this->assertSame('allowed', $this->accessMiddleware()->handle($guestRequest, $next)->getContent());
        $this->assertSame('allowed', $this->accessMiddleware()->handle($userRequest, $next)->getContent());
    }

    private function assertRouteHasMiddleware(string $routeName, string $middleware): void
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route);
        $this->assertContains($middleware, $route->gatherMiddleware());
    }

    private function accessMiddleware(): EnsureSeekerAccess
    {
        $settings = $this->createMock(SeekerSettings::class);
        $settings->method('enabled')->willReturn(true);
        $restrictions = $this->createMock(RestrictionService::class);
        $restrictions->method('restricted')
            ->with($this->anything(), UserRestriction::TYPE_ACCESS)
            ->willReturn(false);

        return new EnsureSeekerAccess($restrictions, $settings);
    }

    private function userWithPermissions(string ...$permissions): User
    {
        $role = new Role(['is_admin' => false]);
        $role->setRelation('permissions', collect(array_map(
            fn (string $permission) => new Permission(['permission' => $permission]),
            $permissions
        )));

        $user = new User;
        $user->setRelation('role', $role);

        return $user;
    }
}
