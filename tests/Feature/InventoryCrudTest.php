<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryCrudTest extends TestCase
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

    private function item(array $overrides = []): InventoryItem
    {
        return InventoryItem::create(array_merge([
            'name' => 'Jeringa 5ml',
            'category' => 'Insumos',
            'sku' => 'SKU-'.fake()->unique()->numerify('#####'),
            'quantity' => 50,
            'min_quantity' => 10,
            'unit_price' => 1200,
            'unit' => 'unidad',
        ], $overrides));
    }

    public function test_admin_can_access_inventory_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/inventario')
            ->assertStatus(200);
    }

    public function test_admin_can_create_inventory_item(): void
    {
        $this->actingAs($this->admin)->post('/inventario', [
            'name' => 'Gasa esteril',
            'category' => 'Curaciones',
            'sku' => 'GAS-001',
            'quantity' => 100,
            'min_quantity' => 20,
            'unit_price' => 500,
            'unit' => 'caja',
        ])->assertStatus(302);

        $this->assertDatabaseHas('inventory_items', ['name' => 'Gasa esteril', 'sku' => 'GAS-001']);
    }

    public function test_admin_can_update_inventory_item(): void
    {
        $item = $this->item();

        $this->actingAs($this->admin)->patch("/inventario/{$item->id}", [
            'name' => 'Jeringa 10ml',
            'category' => 'Insumos',
            'sku' => $item->sku,
            'quantity' => 30,
            'min_quantity' => 5,
            'unit_price' => 1500,
            'unit' => 'unidad',
        ]);

        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'name' => 'Jeringa 10ml', 'quantity' => 30]);
    }

    public function test_admin_can_delete_inventory_item(): void
    {
        $item = $this->item();

        $this->actingAs($this->admin)->delete("/inventario/{$item->id}");

        $this->assertDatabaseMissing('inventory_items', ['id' => $item->id]);
    }

    public function test_cannot_create_inventory_item_with_negative_quantity(): void
    {
        $this->actingAs($this->admin)->post('/inventario', [
            'name' => 'Item',
            'category' => 'Insumos',
            'quantity' => -5,
            'min_quantity' => 0,
            'unit_price' => 100,
            'unit' => 'unidad',
        ])->assertSessionHasErrors(['quantity']);
    }

    public function test_low_stock_is_flagged(): void
    {
        $item = $this->item(['quantity' => 5, 'min_quantity' => 10]);

        $this->assertTrue($item->isLowStock());
    }
}
