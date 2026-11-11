<?php

namespace Tests\Unit\Models;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_created(): void
    {
        $exam = Exam::query()->create(["title" => "Q Exam", "duration_minutes" => 30]);
        $question = $exam->questions()->create([
            "body" => "What is 2+2?",
            "points" => 5,
            "question_type" => "mcq",
        ]);

        $this->assertDatabaseHas("questions", ["body" => "What is 2+2?"]);
    }

    public function test_belongs_to_exam(): void
    {
        $exam = Exam::query()->create(["title" => "Parent", "duration_minutes" => 30]);
        $question = $exam->questions()->create([
            "body" => "Q?",
            "points" => 10,
            "question_type" => "mcq",
        ]);

        $this->assertInstanceOf(Exam::class, $question->exam);
    }

    public function test_has_options(): void
    {
        $exam = Exam::query()->create(["title" => "Options Test", "duration_minutes" => 30]);
        $question = $exam->questions()->create([
            "body" => "Choose?",
            "points" => 5,
            "question_type" => "mcq",
        ]);

        $question->options()->createMany([
            ["label" => "A", "body" => "A", "is_correct" => true],
            ["label" => "B", "body" => "B", "is_correct" => false],
            ["label" => "C", "body" => "C", "is_correct" => false],
        ]);

        $this->assertCount(3, $question->fresh()->options);
    }

    public function test_option_fields(): void
    {
        $exam = Exam::query()->create(["title" => "Opt", "duration_minutes" => 30]);
        $question = $exam->questions()->create([
            "body" => "Pick",
            "points" => 1,
            "question_type" => "mcq",
        ]);

        $option = $question->options()->create([
            "label" => "A", "body" => "Correct Answer",
            "is_correct" => true,
        ]);

        $this->assertTrue($option->is_correct);
        $this->assertSame("Correct Answer", $option->body);
    }

    public function test_image_path(): void
    {
        $exam = Exam::query()->create(["title" => "Img", "duration_minutes" => 30]);
        $question = $exam->questions()->create([
            "body" => "See image",
            "points" => 2,
            "question_type" => "mcq",
            "image_path" => "questions/diagram.png",
        ]);

        $this->assertSame("questions/diagram.png", $question->image_path);
    }
}