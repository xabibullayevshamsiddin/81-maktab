<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserNotification;
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

    public function test_admin_approval_creates_private_course_open_notification(): void
    {
        $admin = $this->adminUser();
        $teacherUser = $this->teacherUser();
        $otherTeacher = $this->teacherUser('other-teacher@example.com', 'Other Teacher');

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
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $teacherUser->id,
            'type' => UserNotification::TYPE_SUCCESS,
            'title' => 'Kurs ochish ruxsati berildi',
            'read_at' => null,
        ]);

        $this->actingAs($otherTeacher)
            ->getJson(route('notifications.pending'))
            ->assertOk()
            ->assertJsonCount(0, 'notifications');

        $notificationResponse = $this->actingAs($teacherUser)
            ->getJson(route('notifications.pending'));

        $notificationResponse
            ->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.type', 'success')
            ->assertJsonPath('notifications.0.title', 'Kurs ochish ruxsati berildi');

        $notification = UserNotification::query()
            ->where('user_id', $teacherUser->id)
            ->firstOrFail();

        $this->assertNotNull($notification->read_at);

        $this->actingAs($teacherUser)
            ->getJson(route('notifications.pending'))
            ->assertOk()
            ->assertJsonCount(0, 'notifications');
    }

    public function test_admin_rejection_creates_private_course_open_notification(): void
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
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $teacherUser->id,
            'type' => UserNotification::TYPE_WARNING,
            'title' => "Kurs ochish so'rovi rad etildi",
            'read_at' => null,
        ]);

        $this->actingAs($teacherUser)
            ->getJson(route('notifications.pending'))
            ->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.type', 'warning')
            ->assertJsonPath('notifications.0.title', "Kurs ochish so'rovi rad etildi");
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
