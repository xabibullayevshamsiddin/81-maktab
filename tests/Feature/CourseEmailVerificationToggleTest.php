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

    public function test_course_price_rejects_garbage_value_even_when_duration_is_free_text(): void
    {
        $admin = $this->adminUser();
        $teacher = $this->activeTeacher();

        config([
            'courses.require_email_verification' => true,
            'mail.enabled' => true,
            'mail.code_delivery_enabled' => false,
        ]);

        $response = $this->actingAs($admin)->from(route('teacher.courses.create'))->post(route('teacher.courses.store'), [
            'teacher_id' => $teacher->id,
            'title' => 'Kimyo kursi',
            'price' => 'fsd',
            'duration' => 'dsfds',
            'description' => 'Kimyo bo\'yicha tayyorlov kursi.',
            'start_date' => now()->addWeek()->toDateString(),
        ]);

        $response
            ->assertRedirect(route('teacher.courses.create'))
            ->assertSessionHasErrors(['price'])
            ->assertSessionDoesntHaveErrors(['duration']);

        $this->assertDatabaseMissing('courses', [
            'title' => 'Kimyo kursi',
        ]);
    }

    public function test_course_duration_accepts_free_text_values(): void
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
            'title' => 'Ingliz tili kursi',
            'price' => "500 000 so'm",
            'price_en' => '40 USD',
            'duration' => 'Har hafta, guruhga qarab kelishiladi',
            'duration_en' => 'Flexible schedule, depends on the group',
            'description' => 'Til kursi tavsifi.',
            'description_en' => 'Course description.',
            'start_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('courses'));

        $this->assertDatabaseHas('courses', [
            'title' => 'Ingliz tili kursi',
            'duration' => 'Har hafta, guruhga qarab kelishiladi',
            'duration_en' => 'Flexible schedule, depends on the group',
            'status' => Course::STATUS_PUBLISHED,
        ]);
    }

    public function test_teacher_without_teacher_profile_can_request_approval_and_create_one_course(): void
    {
        Mail::fake();

        $teacherUser = $this->teacherUser();

        config([
            'courses.require_email_verification' => true,
            'mail.enabled' => true,
            'mail.code_delivery_enabled' => false,
        ]);

        $requestReason = "O'quvchilar uchun robototexnika bo'yicha amaliy kurs ochmoqchiman.";

        $requestResponse = $this->actingAs($teacherUser)->post(route('teacher.courses.request'), [
            'reason' => $requestReason,
        ]);

        $requestResponse->assertRedirect(route('profile.show'));
        $this->assertDatabaseHas('users', [
            'id' => $teacherUser->id,
            'course_open_request_pending' => true,
            'course_open_request_reason' => $requestReason,
            'course_open_approved' => false,
        ]);
        $this->assertDatabaseMissing('teachers', [
            'user_id' => $teacherUser->id,
        ]);

        $teacherUser->refresh()->update([
            'course_open_request_pending' => false,
            'course_open_approved' => true,
            'course_open_approved_at' => now(),
        ]);

        $createResponse = $this->actingAs($teacherUser)->post(route('teacher.courses.store'), [
            'title' => 'Robototexnika kursi',
            'price' => "300 000 so'm",
            'duration' => '2 oy',
            'description' => 'Amaliy robototexnika bo\'yicha boshlang\'ich kurs.',
            'start_date' => now()->addWeek()->toDateString(),
        ]);

        $createResponse->assertRedirect(route('courses'));
        $this->assertDatabaseHas('courses', [
            'title' => 'Robototexnika kursi',
            'created_by' => $teacherUser->id,
            'teacher_id' => null,
            'status' => Course::STATUS_PUBLISHED,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $teacherUser->id,
            'course_open_request_pending' => false,
            'course_open_approved' => false,
        ]);

        $secondCreateResponse = $this->actingAs($teacherUser)->post(route('teacher.courses.store'), [
            'title' => 'Ikkinchi kurs',
            'price' => "300 000 so'm",
            'duration' => '2 oy',
            'description' => 'Limit tekshiruvi.',
            'start_date' => now()->addWeeks(2)->toDateString(),
        ]);

        $secondCreateResponse->assertRedirect(route('profile.show'));
        $this->assertDatabaseMissing('courses', [
            'title' => 'Ikkinchi kurs',
            'created_by' => $teacherUser->id,
        ]);
        Mail::assertNothingSent();
    }

    public function test_admin_approval_updates_teacher_course_open_flags(): void
    {
        $admin = $this->adminUser();
        $teacherUser = $this->teacherUser();
        $teacherUser->update([
            'course_open_request_pending' => true,
            'course_open_requested_at' => now(),
            'course_open_request_reason' => "Matematika bo'yicha tayyorlov kursi ochmoqchiman.",
            'course_open_approved' => false,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('user.course-open.approve', $teacherUser));

        $response->assertRedirect(route('admin.courses.requests'));

        $this->assertDatabaseHas('users', [
            'id' => $teacherUser->id,
            'course_open_request_pending' => false,
            'course_open_approved' => true,
        ]);
    }

    public function test_admin_rejection_updates_teacher_course_open_flags(): void
    {
        $admin = $this->adminUser();
        $teacherUser = $this->teacherUser();

        $teacherUser->update([
            'course_open_request_pending' => true,
            'course_open_requested_at' => now(),
            'course_open_request_reason' => "Fizika bo'yicha video darslar kursi ochmoqchiman.",
            'course_open_approved' => false,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('user.course-open.reject', $teacherUser));

        $response->assertRedirect(route('admin.courses.requests'));

        $this->assertDatabaseHas('users', [
            'id' => $teacherUser->id,
            'course_open_request_pending' => false,
            'course_open_approved' => false,
        ]);
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

    private function teacherUser(string $email = 'teacher@example.com', string $name = 'Teacher User'): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'phone' => '+998901234568',
            'password' => bcrypt('password'),
            'role_id' => Role::idByName(Role::NAME_TEACHER),
            'is_active' => true,
        ]);
    }
}
