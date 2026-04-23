<?php

namespace Tests\Unit;

use App\Services\Ai\AiService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        Cache::forget('site_settings_all');
    }

    public function test_exact_farewell_token_does_not_match_hayrli_phrase(): void
    {
        $service = new AiService();

        $result = $this->invokePrivate($service, 'hasAnyExactToken', ['hayrli kun', ['hayr']]);

        $this->assertFalse($result);
    }

    public function test_simple_calculation_is_answered_locally(): void
    {
        $service = new AiService();

        $result = $this->invokePrivate($service, 'matchSimpleCalculation', ['2+2=?']);

        $this->assertSame('Javob: **4**.', $result);
    }

    public function test_percent_calculation_is_answered_locally(): void
    {
        $service = new AiService();

        $result = $service->generateResponse('80 dan 45 necha foiz');

        $this->assertTrue($result['success']);
        $this->assertSame('local_utility', $result['source']);
        $this->assertStringContainsString('56.3%', $result['text']);
    }

    public function test_local_utility_handles_insult_politely(): void
    {
        $service = new AiService();

        $result = $this->invokePrivate($service, 'matchLocalUtility', ['jalab']);

        $this->assertIsString($result);
        $this->assertStringContainsString('Hurmat bilan yozsangiz', $result);
    }

    public function test_local_utility_handles_false_goodbye_complaint(): void
    {
        $service = new AiService();

        $result = $this->invokePrivate($service, 'matchLocalUtility', ['men hayr demadim']);

        $this->assertIsString($result);
        $this->assertStringContainsString("Uzr, noto'g'ri tushundim", $result);
    }

    public function test_support_contact_reply_does_not_offer_direct_admin_contact(): void
    {
        $service = new AiService();

        $result = $this->invokePrivate($service, 'matchSupportContactQuery', ["admin bilan bog'lanish kerak"]);

        $this->assertIsString($result);
        $this->assertStringContainsString("to'g'ridan-to'g'ri", $result);
        $this->assertStringContainsString("ichki tartibda", $result);
    }

    public function test_clarification_fallback_asks_for_context_instead_of_guessing(): void
    {
        $service = new AiService();

        $result = $this->invokePrivate($service, 'matchClarificationFallback', ['buni qayerdan qilaman']);

        $this->assertIsString($result);
        $this->assertStringContainsString('Aniqlashtirib yozing', $result);
        $this->assertStringContainsString('kurs', $result);
        $this->assertStringContainsString('imtihon', $result);
    }

    public function test_support_wizard_trigger_detects_problem_reports(): void
    {
        $service = new AiService();

        $this->assertTrue($service->shouldStartSupportWizard("Menda muammo bor, kurs sahifasi ishlamayapti"));
        $this->assertFalse($service->shouldStartSupportWizard('Menga kurslar ro\'yxatini ko\'rsating'));
    }

    public function test_student_actions_point_to_profile_and_courses(): void
    {
        $service = new AiService();
        $student = new class
        {
            public function isAdmin(): bool { return false; }
            public function canManageExams(): bool { return false; }
            public function isTeacher(): bool { return false; }
        };

        $actions = $service->suggestActions('natijam va kursga yozilish', $student);
        $routes = $this->extractRoutes($actions);

        $this->assertContains('profile.show', $routes);
        $this->assertContains('courses', $routes);
    }

    public function test_teacher_actions_include_exam_creation(): void
    {
        $service = new AiService();
        $teacher = new class
        {
            public function isAdmin(): bool { return false; }
            public function canManageExams(): bool { return true; }
            public function isTeacher(): bool { return true; }
            public function hasLinkedActiveTeacherProfile(): bool { return true; }
            public function hasReachedCourseOpenLimit(): bool { return false; }
            public function hasCourseOpenApproval(): bool { return true; }
        };

        $actions = $service->suggestActions('imtihon yaratmoqchiman', $teacher);
        $routes = $this->extractRoutes($actions);

        $this->assertContains('profile.exams.create', $routes);
        $this->assertContains('teacher.courses.create', $routes);
    }

    public function test_admin_actions_include_dashboard_route(): void
    {
        $service = new AiService();
        $admin = new class
        {
            public function isAdmin(): bool { return true; }
            public function canManageExams(): bool { return false; }
            public function isTeacher(): bool { return false; }
        };

        $actions = $service->suggestActions("panelga o'tish", $admin);
        $routes = $this->extractRoutes($actions);

        $this->assertContains('dashboard', $routes);
    }

    private function invokePrivate(AiService $service, string $method, array $arguments = []): mixed
    {
        $caller = \Closure::bind(
            fn (string $targetMethod, array $targetArguments) => $this->{$targetMethod}(...$targetArguments),
            $service,
            AiService::class
        );

        return $caller($method, $arguments);
    }

    private function extractRoutes(array $actions): array
    {
        return array_values(array_filter(array_map(
            static fn (array $action): ?string => $action['route'] ?? null,
            $actions
        )));
    }
}
