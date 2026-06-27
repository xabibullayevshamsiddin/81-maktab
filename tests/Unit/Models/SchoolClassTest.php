<?php

namespace Tests\Unit\Models;

use App\Models\SchoolClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_created(): void
    {
        $cls = SchoolClass::query()->create([
            "name" => "10-A",
            "grade" => 10,
        ]);

        $this->assertDatabaseHas("school_classes", ["name" => "10-A"]);
    }

    public function test_is_active_by_default(): void
    {
        $cls = SchoolClass::query()->create(["name" => "5-B", "grade" => 5]);

        $this->assertTrue($cls->is_active);
    }

    public function test_can_be_deactivated(): void
    {
        $cls = SchoolClass::query()->create(["name" => "11-A", "grade" => 11]);
        $cls->update(["is_active" => false]);

        $this->assertFalse($cls->fresh()->is_active);
    }
}