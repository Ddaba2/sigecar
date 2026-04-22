<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure DatabaseSeeder is run so we have the roles and permissions if needed
        $this->seed();
        
        $this->admin = User::where('role', 'admin')->first();
    }

    public function test_admin_can_login()
    {
        $response = $this->post('/login', [
            'username' => 'admin@sigecar.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_admin_dashboard_is_accessible()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Admin'); // Just a check that something related to admin loads
    }

    public function test_admin_can_access_users_page()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_depot_page()
    {
        $response = $this->actingAs($this->admin)->get('/admin/depot');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_transport_page()
    {
        $response = $this->actingAs($this->admin)->get('/admin/transport');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_cessions_page()
    {
        $response = $this->actingAs($this->admin)->get('/admin/cessions');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_cannot_access_admin_pages()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin_pages()
    {
        $gestionnaire = User::where('role', 'gestionnaire')->first();
        $response = $this->actingAs($gestionnaire)->get('/admin');
        // It might return 403 Forbidden or redirect depending on middleware setup
        // Given typically role middleware throws 403 or redirects:
        $this->assertTrue(in_array($response->status(), [403, 302, 401]));
    }
}
