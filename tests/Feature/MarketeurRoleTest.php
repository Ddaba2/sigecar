<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class MarketeurRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $marketeur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->marketeur = User::where('role', 'marketeur')->first();
    }

    public function test_marketeur_can_login()
    {
        $response = $this->post('/login', [
            'username' => 'petrobama@sigecar.com',
            'password' => 'mark123',
        ]);

        $response->assertRedirect('/marketeur');
        $this->assertAuthenticatedAs($this->marketeur);
    }

    public function test_marketeur_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->marketeur)->get('/marketeur');
        $response->assertStatus(200);
    }

    public function test_marketeur_can_access_operations()
    {
        $response = $this->actingAs($this->marketeur)->get('/marketeur/operations');
        $response->assertStatus(200);
    }

    public function test_marketeur_can_access_cessions()
    {
        $response = $this->actingAs($this->marketeur)->get('/marketeur/cessions');
        $response->assertStatus(200);
    }

    public function test_non_marketeur_cannot_access_marketeur_pages()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/marketeur');
        $this->assertTrue(in_array($response->status(), [403, 302, 401]));
    }
}
