<?php

namespace Tests\Feature\Domain\Catalog;

use App\Domain\Catalog\Category;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_parent_and_children_expose_the_category_hierarchy(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->for($parent, 'parent')->create([
            'is_active' => 1,
        ]);

        $this->assertTrue($child->parent->is($parent));
        $this->assertTrue($parent->children->contains($child));
        $this->assertTrue($child->is_active);
    }

    public function test_deleting_a_parent_keeps_children_as_root_categories(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->for($parent, 'parent')->create();

        $parent->delete();

        $this->assertNull($child->refresh()->parent_id);
    }

    public function test_duplicate_slugs_are_rejected(): void
    {
        Category::factory()->create(['slug' => 'eletronicos']);

        $this->expectException(QueryException::class);

        Category::factory()->create(['slug' => 'eletronicos']);
    }
}
