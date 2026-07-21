<?php

namespace Tests\Unit\Models;

use App\Models\Exam;
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

        foreach (Role::DEFAULT_ROLES as $role) {
            if (!Role::query()->where("name", $role["name"])->exists()) {
                Role::query()->create($role);
            }
        }
    }

    private function makeUser(string $roleName): User
    {
        $role = Role::query()->where("name", $roleName)->first();

        return User::query()->create([
            "name" => "Test",
            "email" => $roleName . "-" . uniqid() . "@example.com",
            "password" => bcrypt("password"),
            "role_id" => $role->id,
            "is_active" => true,
        ]);
    }

    public function test_can_be_created(): void
    {
        $exam = Exam::query()->create([
            "title" => "Matematika imtihoni",
            "duration_minutes" => 60,
            "required_questions" => 5,
            "total_points" => 50,
            "passing_points" => 25,
        ]);

        $this->assertDatabaseHas("exams", ["title" => "Matematika imtihoni"]);
    }

    public function test_becomes_active_when_ready(): void
    {
        $exam = Exam::query()->create([
            "title" => "Fizika",
            "duration_minutes" => 45,
            "required_questions" => 2,
            "total_points" => 20,
            "passing_points" => 10,
        ]);

        $exam->questions()->createMany([
            ["question_text" => "Savol 1?", "points" => 10, "type" => "single"],
            ["question_text" => "Savol 2?", "points" => 10, "type" => "single"],
        ]);

        $exam->syncActiveFromQuestions();
        $this->assertTrue($exam->fresh()->is_active);
    }

    public function test_inactive_when_questions_insufficient(): void
    {
        $exam = Exam::query()->create([
            "title" => "Ingliz tili",
            "duration_minutes" => 30,
            "required_questions" => 5,
            "total_points" => 50,
            "passing_points" => 25,
        ]);

        $exam->questions()->createMany([
            ["question_text" => "Savol 1?", "points" => 10, "type" => "single"],
        ]);

        $exam->syncActiveFromQuestions();
        $this->assertFalse($exam->fresh()->is_active);
    }

    public function test_grade_restrictions(): void
    {
        $exam = new Exam(["allowed_grades" => ["5-A", "5-B"]]);

        $this->assertTrue($exam->hasGradeRestrictions());
        $this->assertSame(["5-A", "5-B"], $exam->allowedGradeItems());
    }

    public function test_no_grade_restrictions(): void
    {
        $exam = new Exam(["allowed_grades" => null]);

        $this->assertFalse($exam->hasGradeRestrictions());
    }

    public function test_admin_bypasses_grade_restrictions(): void
    {
        $exam = new Exam(["allowed_grades" => ["5-A"]]);

        $this->assertTrue($exam->allowsUser($this->makeUser(User::ROLE_ADMIN)));
        $this->assertTrue($exam->allowsUser($this->makeUser(User::ROLE_SUPER_ADMIN)));
    }

    public function test_teacher_needs_explicit_flag(): void
    {
        $exam = new Exam(["allowed_grades" => ["5-A"]]);

        $this->assertFalse($exam->allowsUser($this->makeUser(User::ROLE_TEACHER)));
    }

    public function test_teacher_allowed_with_flag(): void
    {
        $exam = new Exam(["allowed_grades" => ["TEACHER"]]);

        $this->assertTrue($exam->allowsUser($this->makeUser(User::ROLE_TEACHER)));
    }

    public function test_open_without_time_restriction(): void
    {
        $exam = new Exam(["available_from" => null]);

        $this->assertTrue($exam->isOpenForStarting());
    }

    public function test_not_open_before_time(): void
    {
        $exam = new Exam(["available_from" => now()->addHour()]);

        $this->assertFalse($exam->isOpenForStarting());
    }

    public function test_open_after_time(): void
    {
        $exam = new Exam(["available_from" => now()->subHour()]);

        $this->assertTrue($exam->isOpenForStarting());
    }

    public function test_sum_question_points(): void
    {
        $exam = Exam::query()->create(["title" => "Test", "total_points" => 30]);
        $exam->questions()->createMany([
            ["question_text" => "Q1", "points" => 10, "type" => "single"],
            ["question_text" => "Q2", "points" => 15, "type" => "single"],
            ["question_text" => "Q3", "points" => 5, "type" => "single"],
        ]);

        $this->assertSame(30, $exam->sumQuestionPoints());
    }

    public function test_question_quota(): void
    {
        $exam = Exam::query()->create(["title" => "Test", "required_questions" => 3]);
        $this->assertFalse($exam->isQuestionQuotaFilled());

        $exam->questions()->createMany([
            ["question_text" => "Q1", "points" => 10, "type" => "single"],
            ["question_text" => "Q2", "points" => 10, "type" => "single"],
            ["question_text" => "Q3", "points" => 10, "type" => "single"],
        ]);

        $this->assertTrue($exam->fresh()->isQuestionQuotaFilled());
    }

    public function test_soft_deletes(): void
    {
        $exam = Exam::query()->create(["title" => "Delete test"]);
        $exam->delete();

        $this->assertSoftDeleted($exam);
    }

    public function test_has_results(): void
    {
        $exam = Exam::query()->create(["title" => "Results test"]);
        $this->assertCount(0, $exam->results);
    }

    public function test_teacher_owns_exam(): void
    {
        $teacher = $this->makeUser(User::ROLE_TEACHER);
        $exam = Exam::query()->create(["title" => "Owned", "created_by" => $teacher->id]);

        $this->assertTrue($exam->ownsExam($teacher));
        $this->assertFalse($exam->ownsExam($this->makeUser(User::ROLE_USER)));
    }

    public function test_admin_owns_any_exam(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $exam = Exam::query()->create(["title" => "Any exam"]);

        $this->assertTrue($exam->ownsExam($admin));
    }

    public function test_allowed_grades_label(): void
    {
        $exam = new Exam(["allowed_grades" => ["5-A", "5-B"]]);
        $label = $exam->allowedGradesLabel();

        $this->assertStringContainsString("5-A", $label);
        $this->assertStringContainsString("5-B", $label);
    }

    public function test_allowed_grades_label_fallback(): void
    {
        $exam = new Exam(["allowed_grades" => []]);
        $this->assertSame("Barcha sinflar", $exam->allowedGradesLabel());
    }

    public function test_available_from_label_null(): void
    {
        $exam = new Exam(["available_from" => null]);
        $this->assertNull($exam->availableFromLabel());
    }

    public function test_available_from_label_set(): void
    {
        $exam = Exam::query()->create([
            "title" => "Timed",
            "available_from" => now()->addDay(),
        ]);

        $this->assertNotNull($exam->availableFromLabel());
    }
}