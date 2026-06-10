<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class GestionnaireRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $gestionnaire;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->gestionnaire = User::where('role', 'gestionnaire')->first();
    }

    public function test_gestionnaire_can_login()
    {
        $response = $this->post('/login', [
            'username' => 'gestionnaire@sigecar.com',
            'password' => 'gest123',
        ]);

        $response->assertRedirect('/gestionnaire');
        $this->assertAuthenticatedAs($this->gestionnaire);
    }

    public function test_gestionnaire_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->gestionnaire)->get('/gestionnaire');
        $response->assertStatus(200);
    }

    public function test_gestionnaire_can_access_operations()
    {
        $response = $this->actingAs($this->gestionnaire)->get('/gestionnaire/operations');
        $response->assertStatus(200);
    }

    public function test_gestionnaire_can_access_stocks()
    {
        $response = $this->actingAs($this->gestionnaire)->get('/gestionnaire/stocks');
        $response->assertStatus(200);
    }
    
    public function test_gestionnaire_can_access_rapports()
    {
        $response = $this->actingAs($this->gestionnaire)->get('/gestionnaire/rapports');
        $response->assertStatus(200);
    }

    public function test_gestionnaire_can_access_parametres()
    {
        $response = $this->actingAs($this->gestionnaire)->get('/gestionnaire/parametres');
        $response->assertStatus(200);
    }

    public function test_non_gestionnaire_cannot_access_gestionnaire_pages()
    {
        $marketeur = User::where('role', 'marketeur')->first();
        $response = $this->actingAs($marketeur)->get('/gestionnaire');
        $this->assertTrue(in_array($response->status(), [403, 302, 401]));
    }
}
