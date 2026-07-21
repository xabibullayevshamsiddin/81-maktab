<?php

namespace Tests\Unit;

use App\Support\PublicStorage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    public function test_delete_handles_null(): void
    {
        PublicStorage::delete(null);
        $this->expectNotToPerformAssertions();
    }

    public function test_delete_file(): void
    {
        Storage::fake("public");

        Storage::disk("public")->put("teachers/avatar.jpg", "content");
        Storage::disk("public")->assertExists("teachers/avatar.jpg");

        PublicStorage::delete("teachers/avatar.jpg");
        Storage::disk("public")->assertMissing("teachers/avatar.jpg");
    }

    public function test_delete_legacy_storage_path(): void
    {
        Storage::fake("public");

        Storage::disk("public")->put("teachers/avatar.jpg", "content");

        PublicStorage::delete("storage/teachers/avatar.jpg");
        Storage::disk("public")->assertMissing("teachers/avatar.jpg");
    }

    public function test_delete_many(): void
    {
        Storage::fake("public");

        Storage::disk("public")->put("img/1.jpg", "a");
        Storage::disk("public")->put("img/2.jpg", "b");
        Storage::disk("public")->put("img/3.jpg", "c");

        PublicStorage::deleteMany(["img/1.jpg", "img/2.jpg", "img/3.jpg"]);

        Storage::disk("public")->assertMissing("img/1.jpg");
        Storage::disk("public")->assertMissing("img/2.jpg");
        Storage::disk("public")->assertMissing("img/3.jpg");
    }

    public function test_normalize_removes_prefix(): void
    {
        $this->assertSame("teachers/avatar.jpg", PublicStorage::normalize("storage/teachers/avatar.jpg"));
        $this->assertSame("teachers/avatar.jpg", PublicStorage::normalize("public/storage/teachers/avatar.jpg"));
    }

    public function test_normalize_returns_null_for_empty(): void
    {
        $this->assertNull(PublicStorage::normalize(""));
        $this->assertNull(PublicStorage::normalize("   "));
    }

    public function test_normalize_handles_backslashes(): void
    {
        $this->assertSame("teachers/avatar.jpg", PublicStorage::normalize("teachers\\avatar.jpg"));
    }

    public function test_candidate_paths(): void
    {
        $paths = PublicStorage::candidatePaths("teachers/photo.jpg");

        $this->assertCount(1, $paths);
        $this->assertSame("teachers/photo.jpg", $paths[0]);
    }
}