<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function createPublishedCourse(): Course
    {
        $teacher = Teacher::factory()->create(['is_active' => true]);
        $teacherUser = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_TEACHER)->first()->id,
        ]);

        return Course::factory()->create([
            'status' => 'published',
            'teacher_id' => $teacher->id,
            'created_by' => $teacherUser->id,
        ]);
    }

    private function createStudent(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_USER)->first()->id,
            'is_active' => true,
            'grade' => '9-A',
        ]);
    }

    public function test_guest_cannot_enroll_in_course(): void
    {
        $course = $this->createPublishedCourse();

        $response = $this->post("/courses/{$course->id}/enroll");

        $response->assertRedirect('/login');
    }

    public function test_student_can_enroll_in_course(): void
    {
        $course = $this->createPublishedCourse();
        $student = $this->createStudent();

        $response = $this->actingAs($student)->post("/courses/{$course->id}/enroll", [
            'contact_phone' => '+998901234567',
            'grade' => '9-A',
        ]);

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_PENDING,
        ]);
    }

    public function test_student_cannot_enroll_twice_in_same_course(): void
    {
        $course = $this->createPublishedCourse();
        $student = $this->createStudent();

        // First enrollment
        CourseEnrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_PENDING,
            'contact_phone' => '+998901234567',
            'grade' => '9-A',
        ]);

        // Second enrollment attempt
        $response = $this->actingAs($student)->post("/courses/{$course->id}/enroll", [
            'contact_phone' => '+998901234567',
            'grade' => '9-A',
        ]);

        $enrollmentCount = CourseEnrollment::where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->count();

        $this->assertEquals(1, $enrollmentCount);
    }

    public function test_admin_can_approve_enrollment(): void
    {
        $course = $this->createPublishedCourse();
        $student = $this->createStudent();
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_ADMIN)->first()->id,
        ]);

        $enrollment = CourseEnrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_PENDING,
            'contact_phone' => '+998901234567',
            'grade' => '9-A',
        ]);

        $response = $this->actingAs($admin)->patch(
            "/admin/courses/{$course->id}/enrollments/{$enrollment->id}/approve"
        );

        $enrollment->refresh();
        $this->assertEquals(CourseEnrollment::STATUS_APPROVED, $enrollment->status);
    }

    public function test_admin_can_reject_enrollment(): void
    {
        $course = $this->createPublishedCourse();
        $student = $this->createStudent();
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_ADMIN)->first()->id,
        ]);

        $enrollment = CourseEnrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_PENDING,
            'contact_phone' => '+998901234567',
            'grade' => '9-A',
        ]);

        $response = $this->actingAs($admin)->patch(
            "/admin/courses/{$course->id}/enrollments/{$enrollment->id}/reject"
        );

        $enrollment->refresh();
        $this->assertEquals(CourseEnrollment::STATUS_REJECTED, $enrollment->status);
    }

    public function test_regular_user_cannot_approve_enrollment(): void
    {
        $course = $this->createPublishedCourse();
        $student = $this->createStudent();
        $regularUser = $this->createStudent();

        $enrollment = CourseEnrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => CourseEnrollment::STATUS_PENDING,
            'contact_phone' => '+998901234567',
            'grade' => '9-A',
        ]);

        $response = $this->actingAs($regularUser)->patch(
            "/admin/courses/{$course->id}/enrollments/{$enrollment->id}/approve"
        );

        $response->assertStatus(403);
    }
}
