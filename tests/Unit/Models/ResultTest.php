<?php

namespace Tests\Unit\Models;

use App\Models\Exam;
use App\Models\Result;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultTest extends TestCase
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

    public function test_can_be_created(): void
    {
        $exam = Exam::query()->create(["title" => "Final"]);
        $user = User::query()->create([
            "name" => "U",
            "email" => "r-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $result = Result::query()->create([
            "exam_id" => $exam->id,
            "user_id" => $user->id,
        ]);

        $this->assertDatabaseHas("results", ["exam_id" => $exam->id, "user_id" => $user->id]);
    }

    public function test_score_and_passing(): void
    {
        $exam = Exam::query()->create(["title" => "Scored", "total_points" => 100, "passing_points" => 50]);
        $user = User::query()->create([
            "name" => "U2",
            "email" => "r2-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $result = Result::query()->create([
            "exam_id" => $exam->id,
            "user_id" => $user->id,
            "score" => 80,
            "points_earned" => 80,
            "points_max" => 100,
            "passed" => true,
        ]);

        $this->assertSame(80, $result->score);
        $this->assertTrue($result->passed);
    }

    public function test_belongs_to_exam_and_user(): void
    {
        $exam = Exam::query()->create(["title" => "Rel"]);
        $user = User::query()->create([
            "name" => "U3",
            "email" => "r3-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $result = Result::query()->create([
            "exam_id" => $exam->id,
            "user_id" => $user->id,
        ]);

        $this->assertInstanceOf(Exam::class, $result->exam);
        $this->assertInstanceOf(User::class, $result->user);
    }

    public function test_has_answers(): void
    {
        $exam = Exam::query()->create(["title" => "AnsRel"]);
        $user = User::query()->create([
            "name" => "U4",
            "email" => "r4-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $result = Result::query()->create([
            "exam_id" => $exam->id,
            "user_id" => $user->id,
        ]);

        $this->assertCount(0, $result->answers);
    }

    public function test_violation_count(): void
    {
        $exam = Exam::query()->create(["title" => "V"]);
        $user = User::query()->create([
            "name" => "U5",
            "email" => "r5-" . uniqid() . "@test.com",
            "password" => bcrypt("pw"),
            "role_id" => Role::idByName(Role::NAME_USER),
            "is_active" => true,
        ]);

        $result = Result::query()->create([
            "exam_id" => $exam->id,
            "user_id" => $user->id,
            "rule_violation_count" => 2,
        ]);

        $this->assertSame(2, $result->rule_violation_count);
    }
}