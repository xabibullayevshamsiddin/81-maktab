<?php

namespace Tests\Unit\Models;

use App\Models\AiKnowledge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiKnowledgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_be_created(): void
    {
        $knowledge = AiKnowledge::query()->create([
            "question" => "Maktab direktori kim?",
            "answer" => "Maktab direktori - ...",
            "is_active" => true,
        ]);

        $this->assertDatabaseHas("ai_knowledges", ["question" => "Maktab direktori kim?"]);
    }

    public function test_active_scope(): void
    {
        AiKnowledge::query()->create([
            "question" => "Active", "answer" => "A", "is_active" => true,
        ]);
        AiKnowledge::query()->create([
            "question" => "Inactive", "answer" => "I", "is_active" => false,
        ]);

        $active = AiKnowledge::query()->where("is_active", true)->get();
        $this->assertCount(1, $active);
    }

    public function test_localized_fields(): void
    {
        $knowledge = AiKnowledge::query()->create([
            "question" => "Savol",
            "question_en" => "Question",
            "answer" => "Javob",
            "answer_en" => "Answer",
        ]);

        app()->setLocale("en");
        $this->assertSame("Question", localized_model_value($knowledge, "question"));
        $this->assertSame("Answer", localized_model_value($knowledge, "answer"));

        app()->setLocale("uz");
        $this->assertSame("Savol", localized_model_value($knowledge, "question"));
        $this->assertSame("Javob", localized_model_value($knowledge, "answer"));
    }

    public function test_sort_order(): void
    {
        $k1 = AiKnowledge::query()->create([
            "question" => "First", "answer" => "1", "sort_order" => 2,
        ]);
        $k2 = AiKnowledge::query()->create([
            "question" => "Second", "answer" => "2", "sort_order" => 1,
        ]);

        $ordered = AiKnowledge::query()->orderBy("sort_order")->get();
        $this->assertSame("Second", $ordered->first()->question);
    }

    public function test_synonyms(): void
    {
        $knowledge = AiKnowledge::query()->create([
            "question" => "Director",
            "answer" => "Director info",
            "synonyms" => "direktor, rahbar, maktab boshligi",
        ]);

        $this->assertSame("direktor, rahbar, maktab boshligi", $knowledge->synonyms);
    }
}