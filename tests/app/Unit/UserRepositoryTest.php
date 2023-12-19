<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DTApi\Repository\UserRepository;
use DTApi\Models\User;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository(new User);
    }

    /** @test */
    public function it_can_create_a_user()
    {
        $data = [
            'role' => 'admin',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'department_id' => 'dept_123',
            'company_id' => 'comp_123',
            'mobile' => '031'
        ];

        $user = $this->userRepository->createOrUpdate(null, $data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['mobile'], $user->email);
    }

    /** @test */
    public function it_can_update_a_user()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            // Add other fields as necessary
        ];

        $updatedUser = $this->userRepository->createOrUpdate($user->id, $data);

        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertEquals($data['name'], $updatedUser->name);
        $this->assertEquals($data['email'], $updatedUser->email);
    }

    /** @test */
    public function it_can_create_a_user_with_customer_role()
    {
        $data = [
            'role' => env('CUSTOMER_ROLE_ID'),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'consumer_type' => 'paid',
        ];

        $user = $this->userRepository->createOrUpdate(null, $data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);

    }
    public function it_can_create_a_user_with_translator_role()
    {
        $data = [
            'role' => env('TRANSLATOR_ROLE_ID'),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'consumer_type' => 'paid',
        ];

        $user = $this->userRepository->createOrUpdate(null, $data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);

    }
}