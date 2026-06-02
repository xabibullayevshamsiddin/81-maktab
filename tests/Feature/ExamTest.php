<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\Result;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_ADMIN)->first()->id,
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

    private function createActiveExam(array $overrides = []): Exam
    {
        return Exam::factory()->create(array_merge([
            'is_active' => true,
            'duration_minutes' => 30,
            'required_questions' => 5,
            'total_points' => 100,
            'passing_points' => 60,
            'allowed_grades' => ['9-A', '9-B'],
        ], $overrides));
    }

    /**
     * Admin Exam CRUD
     */
    public function test_admin_can_create_exam(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/exams', [
            'title' => 'Matematika Test',
            'duration_minutes' => 45,
            'required_questions' => 10,
            'total_points' => 100,
            'passing_points' => 60,
            'allowed_grades' => ['9-A', '9-B'],
        ]);

        $this->assertDatabaseHas('exams', [
            'title' => 'Matematika Test',
            'duration_minutes' => 45,
            'required_questions' => 10,
            'total_points' => 100,
            'passing_points' => 60,
        ]);
    }

    public function test_exam_requires_title(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/exams', [
            'duration_minutes' => 45,
            'required_questions' => 10,
            'total_points' => 100,
            'passing_points' => 60,
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_passing_points_cannot_exceed_total_points(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/exams', [
            'title' => 'Test Exam',
            'duration_minutes' => 45,
            'required_questions' => 10,
            'total_points' => 100,
            'passing_points' => 150, // Greater than total!
        ]);

        $response->assertSessionHasErrors('passing_points');
    }

    public function test_regular_user_cannot_create_exam(): void
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->post('/admin/exams', [
            'title' => 'Test Exam',
            'duration_minutes' => 45,
            'required_questions' => 10,
            'total_points' => 100,
            'passing_points' => 60,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Exam Access
     */
    public function test_student_can_view_available_exams(): void
    {
        $student = $this->createStudent();
        $exam = $this->createActiveExam();

        $response = $this->actingAs($student)->get('/exams');

        $response->assertStatus(200);
    }

    public function test_student_with_wrong_grade_cannot_take_exam(): void
    {
        $student = User::factory()->create([
            'role_id' => Role::where('name', Role::NAME_USER)->first()->id,
            'grade' => '7-A', // Exam only allows 9-A, 9-B
        ]);

        $exam = $this->createActiveExam(['allowed_grades' => ['9-A', '9-B']]);

        $response = $this->actingAs($student)->get("/exams/{$exam->id}/start");

        // Should be forbidden or redirected
        $this->assertTrue(
            $response->getStatusCode() === 403 || $response->isRedirection(),
            'Student with wrong grade should not start exam'
        );
    }

    public function test_inactive_exam_is_not_accessible(): void
    {
        $student = $this->createStudent();
        $exam = Exam::factory()->create(['is_active' => false]);

        $response = $this->actingAs($student)->get("/exams/{$exam->id}/start");

        $this->assertTrue(
            $response->getStatusCode() === 404 || $response->getStatusCode() === 403 || $response->isRedirection(),
            'Inactive exam should not be accessible'
        );
    }

    /**
     * Result Access
     */
    public function test_student_can_view_own_result(): void
    {
        $student = $this->createStudent();
        $exam = $this->createActiveExam();
        
        $result = Result::factory()->create([
            'user_id' => $student->id,
            'exam_id' => $exam->id,
            'submitted_at' => now(),
            'score' => 80,
        ]);

        $response = $this->actingAs($student)->get("/results/{$result->id}");

        $response->assertStatus(200);
    }

    public function test_student_cannot_view_other_students_result(): void
    {
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $exam = $this->createActiveExam();

        $result = Result::factory()->create([
            'user_id' => $student1->id,
            'exam_id' => $exam->id,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($student2)->get("/results/{$result->id}");

        $response->assertStatus(403);
    }

    /**
     * Admin Results
     */
    public function test_admin_can_view_all_results(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/exams/results');

        $response->assertStatus(200);
    }
}
