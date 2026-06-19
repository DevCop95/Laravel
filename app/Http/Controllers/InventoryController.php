<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(): Response
    {
        $items = InventoryItem::orderBy('name')->get();

        return Inertia::render('Inventory/Index', [
            'items' => $items->map(fn (InventoryItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category,
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'min_quantity' => $item->min_quantity,
                'unit_price' => (float) $item->unit_price,
                'unit' => $item->unit,
                'supplier' => $item->supplier,
                'notes' => $item->notes,
                'is_low_stock' => $item->isLowStock(),
                'total_value' => (float) ($item->unit_price * $item->quantity),
            ]),
            'categories' => $items->pluck('category')->unique()->filter()->values(),
            'stats' => [
                'total_items' => $items->count(),
                'total_value' => (float) $items->sum(fn ($i) => $i->unit_price * $i->quantity),
                'low_stock' => $items->filter->isLowStock()->count(),
                'categories' => $items->pluck('category')->unique()->filter()->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN ]+$/u'],
            'category' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN ]+$/u'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:inventory_items,sku'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        InventoryItem::create($validated);

        return back();
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN ]+$/u'],
            'category' => ['required', 'string', 'max:255', 'regex:/^[\pL\pN ]+$/u'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:inventory_items,sku,'.$item->id],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $item->update($validated);

        return back();
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();

        return back();
    }
}
