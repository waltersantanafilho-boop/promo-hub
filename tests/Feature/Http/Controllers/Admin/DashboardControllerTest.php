<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirectToRoute('login');
    }

    public function test_user_without_access_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_role_without_access_permission_is_forbidden(): void
    {
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create()->assignRole($role);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_sees_the_dashboard_with_existing_catalog_counts(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create()->assignRole('admin');
        $categories = Category::factory()->count(3)->create();
        $brands = Brand::factory()->count(2)->create();
        $stores = Store::factory()->count(6)->create();
        $group = ProductGroup::factory()->for($categories[0])->for($brands[0])->create();
        $products = Product::factory()->count(4)
            ->for($categories[0])->for($brands[0])->for($group)->create();
        $source = StoreSource::factory()->for($stores[0])->create();
        Offer::factory()->count(5)->for($products[0])->for($source)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertViewIs('admin.dashboard')
            ->assertSeeText(['Dashboard', 'Resumo do catálogo', $user->name, $user->email, 'Sair'])
            ->assertSeeTextInOrder(['Resumo do catálogo', 'Categorias', '3', 'Marcas', '2', 'Lojas', '6', 'Grupos de Produtos', '1', 'Produtos', '4', 'Ofertas', '5']);
        $this->assertSame([
            'categories' => 3,
            'brands' => 2,
            'stores' => 6,
            'product_groups' => 1,
            'products' => 4,
            'offers' => 5,
        ], $response->viewData('counts'));
    }

    public function test_access_permission_allows_entry_without_an_admin_role(): void
    {
        $permission = Permission::create(['name' => 'access admin', 'guard_name' => 'web']);
        $user = User::factory()->create()->givePermissionTo($permission);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertSeeText('Resumo do catálogo');
        $this->assertSame([
            'categories' => 0,
            'brands' => 0,
            'stores' => 0,
            'product_groups' => 0,
            'products' => 0,
            'offers' => 0,
        ], $response->viewData('counts'));
    }

    public function test_editor_sees_catalog_navigation_but_not_commercial_navigation(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create()->assignRole('editor');

        $this->actingAs($user)->get('/admin')
            ->assertSeeText(['Catálogo', 'Grupos de Produtos', 'Produtos', 'Categorias', 'Marcas'])
            ->assertDontSeeText('Comercial');
    }

    public function test_implemented_modules_are_real_navigation_links(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertSeeText(['Catálogo', 'Comercial'])
            ->assertDontSeeText('Em breve')
            ->assertDontSee('aria-disabled="true"', false)
            ->assertSee('href="'.route('admin.product-groups.index').'"', false)
            ->assertSee('href="'.route('admin.products.index').'"', false)
            ->assertSee('href="'.route('admin.categories.index').'"', false)
            ->assertSee('href="'.route('admin.brands.index').'"', false)
            ->assertSee('href="'.route('admin.offers.index').'"', false)
            ->assertSee('href="'.route('admin.stores.index').'"', false);
    }

    public function test_user_name_and_email_are_escaped_in_the_layout(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create([
            'name' => '<script>alert("name")</script>',
            'email' => '<img src=x onerror=alert(1)>@example.test',
        ])->assignRole('admin');

        $this->actingAs($user)->get('/admin')
            ->assertSee([$user->name, $user->email])
            ->assertDontSee([$user->name, $user->email], false);
    }

    public function test_logout_ends_the_session_and_blocks_admin_access(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create()->assignRole('admin');

        $this->actingAs($user)->post(route('logout'))->assertRedirect('/');

        $this->assertGuest();
        $this->get('/admin')->assertRedirectToRoute('login');
    }

    public function test_registration_cannot_grant_admin_access_from_extra_fields(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->post(route('register'), [
            'name' => 'New user',
            'email' => 'new-user@example.test',
            'password' => 'test-password',
            'password_confirmation' => 'test-password',
            'role' => 'admin',
            'permissions' => ['access admin'],
        ])->assertRedirect('/home');

        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('model_has_roles', 0);
        $this->assertDatabaseCount('model_has_permissions', 0);
        $this->get('/admin')->assertForbidden();
    }

    public function test_authorized_user_sees_the_admin_link_after_login(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create()->assignRole('admin');

        $this->actingAs($user)->get('/home')
            ->assertSee('href="'.route('admin.dashboard').'"', false);
    }

    public function test_unauthorized_user_does_not_see_the_admin_link_after_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/home')
            ->assertDontSee('href="'.route('admin.dashboard').'"', false);
    }
}
