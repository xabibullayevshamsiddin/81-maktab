<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExamAccessTest extends TestCase
{
    public function test_admin_and_super_admin_bypass_grade_restrictions(): void
    {
        $exam = new Exam([
            'allowed_grades' => ['5-A'],
        ]);

        $this->assertTrue($exam->allowsUser($this->makeUser(User::ROLE_ADMIN)));
        $this->assertTrue($exam->allowsUser($this->makeUser(User::ROLE_SUPER_ADMIN)));
    }

    public function test_teacher_still_needs_explicit_teacher_access(): void
    {
        $exam = new Exam([
            'allowed_grades' => ['5-A'],
        ]);

        $this->assertFalse($exam->allowsUser($this->makeUser(User::ROLE_TEACHER)));
    }

    public function test_admin_and_super_admin_bypass_future_start_time(): void
    {
        $exam = new Exam([
            'available_from' => Carbon::now()->addHour(),
        ]);

        $this->assertFalse($exam->isOpenForStarting());
        $this->assertTrue($exam->isOpenForStarting($this->makeUser(User::ROLE_ADMIN)));
        $this->assertTrue($exam->isOpenForStarting($this->makeUser(User::ROLE_SUPER_ADMIN)));
    }

    private function makeUser(string $roleName, ?string $grade = null): User
    {
        $user = new User([
            'grade' => $grade,
            'is_parent' => false,
        ]);

        $user->setRelation('roleRelation', new Role([
            'name' => $roleName,
            'level' => User::ROLE_HIERARCHY[$roleName] ?? 1,
        ]));

        return $user;
    }
}
