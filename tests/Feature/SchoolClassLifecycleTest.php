<?php

namespace Tests\Feature;

use App\Models\AcademicYearPromotion;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\SchoolClassLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class SchoolClassLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRole = Role::query()->firstOrCreate(
            ['name' => Role::NAME_USER],
            ['label' => 'Foydalanuvchi', 'level' => 1, 'is_system' => true],
        );
    }

    public function test_academic_year_promotion_updates_students_graduates_and_creates_missing_next_grade_class(): void
    {
        $service = app(SchoolClassLifecycleService::class);
        $service->upsertClass(10, 'X');

        $promoted = $this->student('student-8e@example.com', '8-E');
        $graduated = $this->student('student-11d@example.com', '11-D');
        $autoCreatedNextClass = $this->student('student-10x@example.com', '10-X');
        $needsSelection = $this->student('legacy-format@example.com', 'legacy');

        $summary = $service->promoteAcademicYear(2026, 2027);

        $this->assertSame(4, $summary['total']);
        $this->assertSame(2, $summary['promoted']);
        $this->assertSame(1, $summary['graduated']);
        $this->assertSame(1, $summary['selection_required']);

        $this->assertSame('9-E', $promoted->refresh()->grade);
        $this->assertFalse($promoted->grade_needs_selection);

        $this->assertNull($graduated->refresh()->grade);
        $this->assertTrue($graduated->is_parent);

        $this->assertSame('11-X', $autoCreatedNextClass->refresh()->grade);
        $this->assertFalse($autoCreatedNextClass->grade_needs_selection);
        $this->assertDatabaseHas('school_classes', [
            'grade_number' => 11,
            'section' => 'X',
            'name' => '11-X',
            'is_active' => true,
        ]);

        $this->assertNull($needsSelection->refresh()->grade);
        $this->assertTrue($needsSelection->grade_needs_selection);
        $this->assertNotNull($needsSelection->grade_selection_reason);

        $this->assertDatabaseHas('academic_year_promotions', [
            'from_year' => 2026,
            'to_year' => 2027,
            'promoted_count' => 2,
            'graduated_count' => 1,
            'selection_required_count' => 1,
        ]);
        $this->assertSame(4, UserNotification::query()->count());

        $this->expectException(LogicException::class);
        $service->promoteAcademicYear(2026, 2027);
    }

    public function test_disbanding_class_forces_linked_students_to_pick_a_new_class(): void
    {
        $service = app(SchoolClassLifecycleService::class);
        $student = $this->student('student-10e@example.com', '10-E');
        $schoolClass = SchoolClass::query()->where('name', '10-E')->firstOrFail();

        $summary = $service->disbandClass($schoolClass);

        $this->assertSame(1, $summary['affected_users']);
        $this->assertFalse($schoolClass->refresh()->is_active);
        $this->assertNull($student->refresh()->grade);
        $this->assertTrue($student->grade_needs_selection);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $student->id,
            'type' => 'warning',
            'title' => 'Sinfingiz qayta tanlanishi kerak',
        ]);
    }

    public function test_student_with_removed_class_is_redirected_until_a_valid_grade_is_selected(): void
    {
        $student = $this->student('forced-grade@example.com', null, [
            'grade_needs_selection' => true,
            'grade_selection_reason' => 'Sinfingiz o\'chirildi.',
        ]);

        $this->actingAs($student)
            ->get(route('home'))
            ->assertRedirect(route('profile.show'));

        $this->actingAs($student)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Sinfingizni tanlang')
            ->assertSee('Bu oynani sinf tanlamasdan yopib bo');

        $this->actingAs($student)
            ->put(route('profile.grade-selection.update'), [
                'grade' => '9-E',
            ])
            ->assertRedirect(route('profile.show'));

        $this->assertSame('9-E', $student->refresh()->grade);
        $this->assertFalse($student->grade_needs_selection);
        $this->assertNull($student->grade_selection_reason);
    }

    private function student(string $email, ?string $grade, array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Test Student',
            'email' => $email,
            'phone' => '+998901234567',
            'password' => 'password123',
            'grade' => $grade,
            'role_id' => $this->userRole->id,
            'is_parent' => false,
            'is_active' => true,
        ], $overrides));
    }
}
