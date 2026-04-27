<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CourseEmailVerificationToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_create_form_hides_email_code_button_when_code_delivery_is_disabled(): void
    {
        $admin = $this->adminUser();
        $this->activeTeacher();

        config([
            'courses.require_email_verification' => true,
            'mail.enabled' => true,
            'mail.code_delivery_enabled' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('teacher.courses.create'));

        $response->assertOk();
        $response->assertSee('Kursni joylash');
        $response->assertDontSee('Email kod yuborish');
    }

    public function test_course_is_published_immediately_when_code_delivery_is_disabled(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $teacher = $this->activeTeacher();

        config([
            'courses.require_email_verification' => true,
            'mail.enabled' => true,
            'mail.code_delivery_enabled' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('teacher.courses.store'), [
            'teacher_id' => $teacher->id,
            'title' => 'Matematika kursi',
            'price' => "450 000 so'm",
            'duration' => '3 oy',
            'description' => 'Algebra va geometriya bo‘yicha tayyorlov kursi.',
            'start_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('courses'));

        $this->assertDatabaseHas('courses', [
            'title' => 'Matematika kursi',
            'status' => Course::STATUS_PUBLISHED,
            'publish_code' => null,
        ]);

        Mail::assertNothingSent();
    }

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '+998901234567',
            'password' => bcrypt('password'),
            'role_id' => Role::idByName(Role::NAME_ADMIN),
            'is_active' => true,
        ]);
    }

    private function activeTeacher(): Teacher
    {
        return Teacher::create([
            'full_name' => 'Test Teacher',
            'slug' => 'test-teacher',
            'subject' => 'Matematika',
            'experience_years' => 5,
            'is_active' => true,
        ]);
    }
}
