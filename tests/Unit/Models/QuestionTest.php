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
        $exam = Exam::query()->create(["title" => "Q Exam"]);
        $question = $exam->questions()->create([
            "question_text" => "What is 2+2?",
            "points" => 5,
            "type" => "single",
        ]);

        $this->assertDatabaseHas("questions", ["question_text" => "What is 2+2?"]);
    }

    public function test_belongs_to_exam(): void
    {
        $exam = Exam::query()->create(["title" => "Parent"]);
        $question = $exam->questions()->create([
            "question_text" => "Q?",
            "points" => 10,
            "type" => "single",
        ]);

        $this->assertInstanceOf(Exam::class, $question->exam);
    }

    public function test_has_options(): void
    {
        $exam = Exam::query()->create(["title" => "Options Test"]);
        $question = $exam->questions()->create([
            "question_text" => "Choose?",
            "points" => 5,
            "type" => "single",
        ]);

        $question->options()->createMany([
            ["option_text" => "A", "is_correct" => true],
            ["option_text" => "B", "is_correct" => false],
            ["option_text" => "C", "is_correct" => false],
        ]);

        $this->assertCount(3, $question->fresh()->options);
    }

    public function test_option_fields(): void
    {
        $exam = Exam::query()->create(["title" => "Opt"]);
        $question = $exam->questions()->create([
            "question_text" => "Pick",
            "points" => 1,
            "type" => "single",
        ]);

        $option = $question->options()->create([
            "option_text" => "Correct Answer",
            "is_correct" => true,
        ]);

        $this->assertTrue($option->is_correct);
        $this->assertSame("Correct Answer", $option->option_text);
    }

    public function test_image_path(): void
    {
        $exam = Exam::query()->create(["title" => "Img"]);
        $question = $exam->questions()->create([
            "question_text" => "See image",
            "points" => 2,
            "type" => "single",
            "image_path" => "questions/diagram.png",
        ]);

        $this->assertSame("questions/diagram.png", $question->image_path);
    }
}