<?php

namespace Tests\Unit\Models;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Result;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerTest extends TestCase
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

    private function createExam(): Exam
    {
        return Exam::query()->create([
            "title" => "Test Exam",
            "duration_minutes" => 60,
            "required_questions" => 1,
            "total_points" => 10,
            "passing_points" => 5,
        ]);
    }

    public function test_can_be_created(): void
    {
        $user = User::query()->create([
            "name" => "U",
            "email" => "ans-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $exam = $this->createExam();
        $question = $exam->questions()->create([
            "question_text" => "Q?",
            "points" => 10,
            "type" => "single",
        ]);
        $result = Result::query()->create([
            "exam_id" => $exam->id,
            "user_id" => $user->id,
        ]);

        $answer = Answer::query()->create([
            "result_id" => $result->id,
            "question_id" => $question->id,
            "selected_option_id" => 1,
        ]);

        $this->assertDatabaseHas("answers", ["result_id" => $result->id]);
    }

    public function test_belongs_to_result_and_question(): void
    {
        $user = User::query()->create([
            "name" => "U2",
            "email" => "a2-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $exam = $this->createExam();
        $exam->update(["title" => "E2"]);
        $question = $exam->questions()->create([
            "question_text" => "Q2?", "points" => 5, "type" => "single",
        ]);
        $result = Result::query()->create(["exam_id" => $exam->id, "user_id" => $user->id]);

        $answer = Answer::query()->create([
            "result_id" => $result->id,
            "question_id" => $question->id,
        ]);

        $this->assertInstanceOf(Result::class, $answer->result);
        $this->assertInstanceOf(Question::class, $answer->question);
    }

    public function test_text_answer(): void
    {
        $user = User::query()->create([
            "name" => "U3",
            "email" => "a3-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $exam = $this->createExam();
        $exam->update(["title" => "E3"]);
        $question = $exam->questions()->create([
            "question_text" => "Essay?",
            "points" => 20,
            "type" => "text",
        ]);
        $result = Result::query()->create(["exam_id" => $exam->id, "user_id" => $user->id]);

        $answer = Answer::query()->create([
            "result_id" => $result->id,
            "question_id" => $question->id,
            "text_answer" => "This is my essay answer.",
        ]);

        $this->assertSame("This is my essay answer.", $answer->text_answer);
    }
}