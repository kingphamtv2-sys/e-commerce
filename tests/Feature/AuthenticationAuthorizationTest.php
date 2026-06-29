<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_login_redirects_to_account(): void
    {
        $customer = User::factory()->create();

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('account.index'));
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['status' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_each_admin_role_can_access_admin_dashboard(): void
    {
        foreach (['super_admin', 'admin', 'staff'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/admin/dashboard')->assertOk();
            $this->app['auth']->forgetGuards();
        }
    }

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_admin_dashboard_renders_shared_layout(): void
    {
        $admin = User::factory()->create([
            'name' => 'Layout Admin',
            'email' => 'layout@example.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response
            ->assertOk()
            ->assertSee('<title>Dashboard - E-commerce System</title>', false)
            ->assertSee('Layout Admin')
            ->assertSee('layout@example.com')
            ->assertSee('aria-current="page"', false)
            ->assertSeeInOrder([
                'Dashboard',
                'Categories',
                'Products',
                'Inventory',
                'Orders',
                'Coupons',
                'Customers',
                'Banners',
                'System Settings',
                'Online Payment',
                'Languages',
                'Currencies',
                'Tax Classes',
                'Tax Rates',
                'Reports',
            ]);

        foreach (['home', 'cog', 'globe', 'banknote', 'receipt', 'percent', 'folder', 'cube', 'archive', 'shopping-bag', 'users', 'ticket', 'image', 'chart-bar'] as $icon) {
            $response->assertSee('data-admin-icon="'.$icon.'"', false);
        }
    }

    public function test_super_admin_seeder_is_idempotent(): void
    {
        $this->seed(SuperAdminSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame(1, User::query()->where('email', 'admin@example.com')->count());
        $this->assertSame('Super Admin', $admin->name);
        $this->assertSame('super_admin', $admin->role);
        $this->assertTrue($admin->status);
        $this->assertTrue(Hash::check('password', $admin->password));
    }
}
