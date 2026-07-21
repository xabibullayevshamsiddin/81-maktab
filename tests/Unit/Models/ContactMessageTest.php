<?php

namespace Tests\Unit\Models;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_created(): void
    {
        $msg = ContactMessage::query()->create([
            "name" => "Ali Valiyev",
            "email" => "ali@example.com",
            "message" => "Saytda xatolik bor.",
        ]);

        $this->assertDatabaseHas("contact_messages", ["email" => "ali@example.com"]);
    }

    public function test_read_and_block_status(): void
    {
        $msg = ContactMessage::query()->create([
            "name" => "Test",
            "email" => "test@example.com",
            "message" => "Test message",
        ]);

        $this->assertNull($msg->read_at);

        $msg->update(["read_at" => now()]);
        $this->assertNotNull($msg->fresh()->read_at);

        $msg->update(["is_blocked" => true]);
        $this->assertTrue($msg->fresh()->is_blocked);
    }

    public function test_name_email_validation(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        ContactMessage::query()->create([
            "name" => null,
            "email" => null,
            "message" => null,
        ]);
    }
}