<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;use Tests\DatabaseTesting;
use Illuminate\Support\Facades\Hash;

class AdminControllerTest extends TestCase
{
    use DatabaseTesting;

    /** @test */
    public function it_shows_admin_dashboard()
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
        $response->assertViewIs('Admin.dashboard');
    }

    /** @test */
    public function it_shows_users_list()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('Admin.users');
        $response->assertViewHas('users');
    }

    /** @test */
    public function it_shows_add_user_form()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $response = $this->actingAs($admin)->get('/admin/users/add');

        $response->assertStatus(200);
        $response->assertViewIs('Admin.add-user');
    }

    /** @test */
    public function it_stores_new_user()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'gestionnaire',
            'telephone' => '0123456789',
            'company_name' => 'Test Company'
        ];

        $response = $this->actingAs($admin)->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'gestionnaire',
            'telephone' => '0123456789',
            'company_name' => 'Test Company',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_validates_user_creation()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'role' => 'invalid-role'
        ];

        $response = $this->actingAs($admin)->post('/admin/users', $invalidData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    /** @test */
    public function it_shows_edit_user_form()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $user = User::create([
            'name' => 'User to Edit',
            'email' => 'edit@example.com',
            'password' => Hash::make('password123'),
            'role' => 'marketeur',
            'status' => 'active'
        ]);

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('Admin.edit-user');
        $response->assertViewHas('user', $user);
    }

    /** @test */
    public function it_updates_user()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $user = User::create([
            'name' => 'User to Update',
            'email' => 'update@example.com',
            'password' => Hash::make('password123'),
            'role' => 'marketeur',
            'status' => 'active'
        ]);

        $updateData = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'role' => 'gestionnaire',
            'telephone' => '0987654321',
            'company_name' => 'Updated Company',
            'status' => 'active'
        ];

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", $updateData);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'role' => 'gestionnaire',
            'telephone' => '0987654321',
            'company_name' => 'Updated Company'
        ]);
    }

    /** @test */
    public function it_deletes_user()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $user = User::create([
            'name' => 'User to Delete',
            'email' => 'delete@example.com',
            'password' => Hash::make('password123'),
            'role' => 'marketeur',
            'status' => 'active'
        ]);

        $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_denies_access_to_non_admin_users()
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'gestionnaire',
            'status' => 'active'
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect('/gestionnaire');
    }
}