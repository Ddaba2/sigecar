<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;use Tests\DatabaseTesting;
use Illuminate\Support\Facades\Hash;

class RoleMiddlewareTest extends TestCase
{
    use DatabaseTesting;

    /** @test */
    public function it_allows_admin_to_access_admin_routes()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_allows_gestionnaire_to_access_gestionnaire_routes()
    {
        $gestionnaire = User::create([
            'name' => 'Gestionnaire User',
            'email' => 'gestionnaire@example.com',
            'password' => Hash::make('password123'),
            'role' => 'gestionnaire',
            'status' => 'active'
        ]);

        $response = $this->actingAs($gestionnaire)->get('/gestionnaire');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_allows_marketeur_to_access_marketeur_routes()
    {
        $marketeur = User::create([
            'name' => 'Marketeur User',
            'email' => 'marketeur@example.com',
            'password' => Hash::make('password123'),
            'role' => 'marketeur',
            'status' => 'active'
        ]);

        $response = $this->actingAs($marketeur)->get('/marketeur');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_denies_gestionnaire_access_to_admin_routes()
    {
        $gestionnaire = User::create([
            'name' => 'Gestionnaire User',
            'email' => 'gestionnaire@example.com',
            'password' => Hash::make('password123'),
            'role' => 'gestionnaire',
            'status' => 'active'
        ]);

        $response = $this->actingAs($gestionnaire)->get('/admin');

        $response->assertRedirect('/gestionnaire');
    }

    /** @test */
    public function it_denies_marketeur_access_to_admin_routes()
    {
        $marketeur = User::create([
            'name' => 'Marketeur User',
            'email' => 'marketeur@example.com',
            'password' => Hash::make('password123'),
            'role' => 'marketeur',
            'status' => 'active'
        ]);

        $response = $this->actingAs($marketeur)->get('/admin');

        $response->assertRedirect('/marketeur');
    }

    /** @test */
    public function it_redirects_unauthenticated_users_to_login()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }
}