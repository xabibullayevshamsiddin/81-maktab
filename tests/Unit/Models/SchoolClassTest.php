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
            "name" => "10-Z",
            "grade" => 10,
            "section" => "Z",
        ]);

        $this->assertDatabaseHas("school_classes", ["name" => "10-Z"]);
    }

    public function test_is_active_by_default(): void
    {
        $cls = SchoolClass::query()->create([
            "name" => "5-Z",
            "grade" => 5,
            "section" => "Z",
        ]);

        $this->assertTrue($cls->is_active);
    }

    public function test_can_be_deactivated(): void
    {
        $cls = SchoolClass::query()->create([
            "name" => "11-Z",
            "grade" => 11,
            "section" => "Z",
        ]);
        $cls->update(["is_active" => false]);

        $this->assertFalse($cls->fresh()->is_active);
    }
}