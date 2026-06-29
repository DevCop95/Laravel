<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoReadonlyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);
    }

    public function test_mutations_are_blocked_in_readonly_demo(): void
    {
        config(['app.demo_readonly' => true]);

        $this->actingAs($this->admin)->post('/services', [
            'name' => 'Consulta',
            'service_type' => 'Consulta',
            'price' => 80000,
        ]);

        $this->assertDatabaseMissing('services', ['name' => 'Consulta']);
    }

    public function test_reads_are_allowed_in_readonly_demo(): void
    {
        config(['app.demo_readonly' => true]);

        $this->actingAs($this->admin)
            ->get('/servicios')
            ->assertStatus(200);
    }

    public function test_mutations_work_when_not_readonly(): void
    {
        config(['app.demo_readonly' => false]);

        $this->actingAs($this->admin)->post('/services', [
            'name' => 'Consulta',
            'service_type' => 'Consulta',
            'price' => 80000,
        ]);

        $this->assertDatabaseHas('services', ['name' => 'Consulta']);
    }
}
