<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_can_be_submitted(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'message' => 'Bu test xabar',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'test@example.com',
            'message' => 'Bu test xabar',
        ]);
    }

    public function test_contact_form_requires_name(): void
    {
        $response = $this->post('/contact', [
            'email' => 'test@example.com',
            'message' => 'Bu test xabar',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_contact_form_requires_email(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'message' => 'Bu test xabar',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_contact_form_requires_message(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_contact_form_validates_email_format(): void
    {
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'message' => 'Bu test xabar',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_contact_form_sanitizes_input(): void
    {
        $response = $this->post('/contact', [
            'name' => '<b>Bold Name</b>',
            'email' => 'test@example.com',
            'phone' => '+998901234567',
            'message' => 'Clean message',
        ]);

        $message = ContactMessage::latest()->first();

        if ($message) {
            $this->assertStringNotContainsString('<b>', $message->name);
        }
    }
}
