<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Result;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileResultExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->firstOrCreate(
            ['name' => Role::NAME_USER],
            ['label' => 'Foydalanuvchi', 'level' => User::ROLE_HIERARCHY[User::ROLE_USER], 'is_system' => true]
        );
    }

    public function test_user_can_export_only_one_own_result_as_csv(): void
    {
        $user = User::factory()->create([
            'name' => 'Ali Valiyev',
        ]);

        $exam = Exam::query()->create([
            'title' => 'Algebra testi',
            'duration_minutes' => 45,
            'required_questions' => 20,
            'total_points' => 50,
            'passing_points' => 30,
            'is_active' => true,
            'available_from' => now()->subDay(),
            'created_by' => $user->id,
        ]);

        $result = Result::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'user_grade' => '8-A',
            'score' => 18,
            'points_earned' => 45,
            'points_max' => 50,
            'passed' => true,
            'total_questions' => 20,
            'started_at' => now()->subHour(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($user)->get(route('profile.results.single.export', $result));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Algebra testi', $content);
        $this->assertStringContainsString("O'tdi", $content);
        $this->assertStringContainsString('45', $content);
    }

    public function test_user_cannot_export_another_users_single_result(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $exam = Exam::query()->create([
            'title' => 'Geometriya testi',
            'duration_minutes' => 30,
            'required_questions' => 10,
            'total_points' => 20,
            'passing_points' => 10,
            'is_active' => true,
            'available_from' => now()->subDay(),
            'created_by' => $owner->id,
        ]);

        $result = Result::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $owner->id,
            'user_grade' => '7-B',
            'score' => 6,
            'points_earned' => 9,
            'points_max' => 20,
            'passed' => false,
            'total_questions' => 10,
            'started_at' => now()->subHour(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($otherUser)
            ->get(route('profile.results.single.export', $result))
            ->assertNotFound();
    }
}
