<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    public function test_category_name_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Category::create(['name' => null]);
    }

    public function test_category_has_posts(): void
    {
        $category = Category::create(['name' => 'Test Category']);

        $this->assertEmpty($category->posts);
    }

    public function test_category_scope_order_by_name(): void
    {
        Category::create(['name' => 'Zebra']);
        Category::create(['name' => 'Apple']);
        Category::create(['name' => 'Mango']);

        $categories = Category::orderByName()->get();

        $this->assertEquals('Apple', $categories->first()->name);
        $this->assertEquals('Mango', $categories->skip(1)->first()->name);
        $this->assertEquals('Zebra', $categories->last()->name);
    }
}
