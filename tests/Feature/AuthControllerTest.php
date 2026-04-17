<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Log;use Tests\DatabaseTesting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use DatabaseTesting;

    /** @test */
    public function it_shows_login_form()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function it_logs_in_user_with_valid_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'username' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);

        // Vérifier que le log a été créé
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'login',
            'description' => 'Connexion au système'
        ]);
    }

    /** @test */
    public function it_logs_in_user_with_name_instead_of_email()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'username' => 'Test User',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_fails_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'username' => 'invalid@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /** @test */
    public function it_fails_login_with_inactive_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'inactive'
        ]);

        $response = $this->post('/login', [
            'username' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /** @test */
    public function it_redirects_admin_to_admin_dashboard()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $response = $this->actingAs($user)->post('/login', [
            'username' => 'admin@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/admin');
    }

    /** @test */
    public function it_redirects_gestionnaire_to_gestionnaire_dashboard()
    {
        $user = User::create([
            'name' => 'Gestionnaire User',
            'email' => 'gestionnaire@example.com',
            'password' => Hash::make('password123'),
            'role' => 'gestionnaire',
            'status' => 'active'
        ]);

        $response = $this->actingAs($user)->post('/login', [
            'username' => 'gestionnaire@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/gestionnaire');
    }

    /** @test */
    public function it_logs_out_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();

        // Vérifier que le log de déconnexion a été créé
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'logout',
            'description' => 'Déconnexion du système'
        ]);
    }
}