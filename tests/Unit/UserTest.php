<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Tests\DatabaseTesting;

class UserTest extends TestCase
{
    use DatabaseTesting;

    /** @test */
    public function it_can_check_if_user_is_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'gestionnaire']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function it_can_check_if_user_is_gestionnaire()
    {
        $gestionnaire = User::factory()->create(['role' => 'gestionnaire']);
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($gestionnaire->isGestionnaire());
        $this->assertFalse($user->isGestionnaire());
    }

    /** @test */
    public function it_can_check_if_user_is_marketeur()
    {
        $marketeur = User::factory()->create(['role' => 'marketeur']);
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($marketeur->isMarketeur());
        $this->assertFalse($user->isMarketeur());
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'telephone' => '0123456789',
            'status' => 'active',
            'company_name' => 'Test Company'
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('0123456789', $user->telephone);
        $this->assertEquals('active', $user->status);
        $this->assertEquals('Test Company', $user->company_name);
    }
}