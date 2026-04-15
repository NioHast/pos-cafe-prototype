<?php

namespace Tests\Feature\Inventory;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryFoundationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_foundation_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('ingredients'));
        $this->assertTrue(Schema::hasTable('ingredient_batches'));
        $this->assertTrue(Schema::hasTable('menu_ingredients'));
        $this->assertTrue(Schema::hasTable('stock_adjustments'));
        $this->assertTrue(Schema::hasTable('stock_movements'));
    }

    public function test_menu_ingredients_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('menu_ingredients', [
            'menu_id',
            'ingredient_id',
            'quantity_used',
        ]));
    }
}
