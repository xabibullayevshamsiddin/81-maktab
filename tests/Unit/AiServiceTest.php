<?php

namespace Tests\Unit;

use App\Models\AiKnowledge;
use App\Models\Course;
use App\Services\Ai\AiService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        AiKnowledge::flushColumnPresenceCache();

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
        $service = new AiService;

        $result = $this->invokePrivate($service, 'hasAnyExactToken', ['hayrli kun', ['hayr']]);

        $this->assertFalse($result);
    }

    public function test_simple_calculation_is_answered_locally(): void
    {
        $service = new AiService;

        $result = $this->invokePrivate($service, 'matchSimpleCalculation', ['2+2=?']);

        $this->assertSame('Javob: **4**.', $result);
    }

    public function test_percent_calculation_is_answered_locally(): void
    {
        $service = new AiService;

        $result = $service->generateResponse('80 dan 45 necha foiz');

        $this->assertTrue($result['success']);
        $this->assertSame('local_utility', $result['source']);
        $this->assertStringContainsString('56.3%', $result['text']);
    }

    public function test_local_utility_handles_insult_politely(): void
    {
        $service = new AiService;

        $result = $this->invokePrivate($service, 'matchLocalUtility', ['jalab']);

        $this->assertIsString($result);
        $this->assertStringContainsString('Hurmat bilan yozsangiz', $result);
    }

    public function test_local_utility_handles_false_goodbye_complaint(): void
    {
        $service = new AiService;

        $result = $this->invokePrivate($service, 'matchLocalUtility', ['men hayr demadim']);

        $this->assertIsString($result);
        $this->assertStringContainsString("Uzr, noto'g'ri tushundim", $result);
    }

    public function test_support_contact_reply_does_not_offer_direct_admin_contact(): void
    {
        $service = new AiService;

        $result = $this->invokePrivate($service, 'matchSupportContactQuery', ["admin bilan bog'lanish kerak"]);

        $this->assertIsString($result);
        $this->assertStringContainsString("to'g'ridan-to'g'ri", $result);
        $this->assertStringContainsString('ichki tartibda', $result);
    }

    public function test_common_school_help_questions_are_answered_locally(): void
    {
        $service = new AiService;

        $cases = [
            ["Parolimni esdan chiqardim, qanday tiklasam bo'ladi?", ['school_help', 'Parolni tiklash', 'forgot-password']],
            ['Profilimga rasmni qanday yuklayman?', ['school_help', 'Profil rasmini yuklash', 'profile']],
            ["Ism-familiyam xato yozilibdi, qanday to'g'rilayman?", ['school_help', "Ism-familiyani to'g'rilash"]],
            ['Saytdagi xatolik bug haqida kimga xabar berishim kerak?', ['school_help', 'Saytdagi xatolik', 'Aloqa']],
            ['Maktabga telefon yoki planshet olib kelish mumkinmi?', ['school_help', 'Telefon va planshet']],
            ['Kechikib kelsa nima bo\'ladi? Jazo bormi?', ['school_help', 'Kechikish tartibi']],
            ['Maktab formasi qoidalari qanaqa?', ['school_help', 'Maktab formasi']],
            ['Kutubxonadan kitob olish tartibi qanday?', ['school_help', 'Kutubxonadan foydalanish']],
            ['Dars paytida maktab hududidan tashqariga chiqish mumkinmi?', ['school_help', 'Dars vaqtida maktab hududidan chiqish']],
            ['Maktabda fan olimpiadalari qachon bo\'ladi?', ['school_help', 'Fan olimpiadalari']],
            ['Sport musobaqalariga futbol yoki shaxmatga qanday yozilsam bo\'ladi?', ['school_help', 'Sport musobaqalariga yozilish']],
            ['Boshqa maktabdan ko\'chirib o\'tish perevod tartibi qanday?', ['school_help', "ko'chirib o'tish"]],
            ['Ota-onalar majlisi qachon bo\'lishini qayerdan bilsam bo\'ladi?', ['school_help', 'Ota-onalar majlisi']],
            ['Farzandimning ustoziga qanday qilib savol bersam bo\'ladi?', ['school_help', 'Ustozga savol berish']],
            ['Maktabga qabul qilish pullikmi yoki bepulmi?', ['school_help', 'Maktabga qabul masalasi']],
            ['Pullik kurslar uchun to\'lovni qanday qilsam bo\'ladi Click Payme?', ['school_help', "Kurs to'lovi"]],
            ['Kursni tugatgach sertifikat beriladimi?', ['school_help', 'Kurs sertifikati']],
            ['Imtihondan yiqilsam qayta topshirish uchun qancha vaqt kutishim kerak?', ['school_help', 'qayta topshirish']],
            ['Mening rolim o\'quvchi bilan o\'zim ham kurs yarata olamanmi?', ['school_help', "O'quvchi kurs yarata oladimi"]],
        ];

        foreach ($cases as [$question, $expectations]) {
            $result = $service->generateResponse($question);

            $this->assertTrue($result['success'], $question);
            $this->assertSame($expectations[0], $result['source'], $question);

            foreach (array_slice($expectations, 1) as $snippet) {
                $this->assertStringContainsString($snippet, $result['text'], $question);
            }
        }
    }

    public function test_clarification_fallback_asks_for_context_instead_of_guessing(): void
    {
        $service = new AiService;

        $result = $this->invokePrivate($service, 'matchClarificationFallback', ['buni qayerdan qilaman']);

        $this->assertIsString($result);
        $this->assertStringContainsString('Aniqlashtirib yozing', $result);
        $this->assertStringContainsString('kurs', $result);
        $this->assertStringContainsString('imtihon', $result);
    }

    public function test_support_wizard_trigger_detects_problem_reports(): void
    {
        $service = new AiService;

        $this->assertTrue($service->shouldStartSupportWizard('Menda muammo bor, kurs sahifasi ishlamayapti'));
        $this->assertFalse($service->shouldStartSupportWizard('Menga kurslar ro\'yxatini ko\'rsating'));
        $this->assertFalse($service->shouldStartSupportWizard('Qaysi kusrlar bor?'));
    }

    public function test_generate_response_lists_published_courses_when_course_word_has_typo(): void
    {
        $this->recreateCourseCatalogTables();

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Course Creator',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('teachers')->insert([
            [
                'id' => 1,
                'full_name' => 'Aziz Ustoz',
                'slug' => 'aziz-ustoz',
                'subject' => 'Matematika',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'full_name' => 'Yopiq Ustoz',
                'slug' => 'yopiq-ustoz',
                'subject' => 'Informatika',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('courses')->insert([
            [
                'id' => 1,
                'teacher_id' => 1,
                'created_by' => 1,
                'title' => 'Matematika intensiv',
                'price' => "450 000 so'm",
                'duration' => '3 oy',
                'description' => 'Algebra va geometriya tayyorlov kursi.',
                'start_date' => now()->addWeek()->toDateString(),
                'status' => Course::STATUS_PUBLISHED,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'teacher_id' => 1,
                'created_by' => 1,
                'title' => 'Yashirin draft kurs',
                'price' => "100 000 so'm",
                'duration' => '1 oy',
                'description' => 'Draft kurs.',
                'start_date' => now()->addWeek()->toDateString(),
                'status' => Course::STATUS_DRAFT,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'teacher_id' => 2,
                'created_by' => 1,
                'title' => 'Nofaol ustoz kursi',
                'price' => "200 000 so'm",
                'duration' => '2 oy',
                'description' => 'Nofaol ustoz kursi.',
                'start_date' => now()->addWeek()->toDateString(),
                'status' => Course::STATUS_PUBLISHED,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $service = new AiService;

        foreach (['kusrlar', 'qaysi kusrlar bor', 'mavjud kurslarni sanab ber'] as $question) {
            $result = $service->generateResponse($question);

            $this->assertTrue($result['success']);
            $this->assertSame('dynamic_data', $result['source']);
            $this->assertStringContainsString('Matematika intensiv', $result['text']);
            $this->assertStringContainsString('Aziz Ustoz', $result['text']);
            $this->assertStringContainsString("450 000 so'm", $result['text']);
            $this->assertStringNotContainsString('Yashirin draft kurs', $result['text']);
            $this->assertStringNotContainsString('Nofaol ustoz kursi', $result['text']);
        }
    }

    public function test_course_typo_suggests_courses_action(): void
    {
        $service = new AiService;

        $actions = $service->suggestActions('qaysi kusrlar bor?');
        $routes = $this->extractRoutes($actions);

        $this->assertContains('courses', $routes);
    }

    public function test_student_actions_point_to_profile_and_courses(): void
    {
        $service = new AiService;
        $student = new class
        {
            public function isAdmin(): bool
            {
                return false;
            }

            public function canManageExams(): bool
            {
                return false;
            }

            public function isTeacher(): bool
            {
                return false;
            }
        };

        $actions = $service->suggestActions('natijam va kursga yozilish', $student);
        $routes = $this->extractRoutes($actions);

        $this->assertContains('profile.results.index', $routes);
        $this->assertContains('courses', $routes);
    }

    public function test_generate_response_returns_real_user_exam_results_instead_of_profile_instruction(): void
    {
        $this->recreateExamResultTables();

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Ali Valiyev',
            'email' => 'ali-results@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('exams')->insert([
            'id' => 1,
            'title' => 'Algebra testi',
            'total_points' => 50,
            'passing_points' => 30,
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('results')->insert([
            'id' => 1,
            'exam_id' => 1,
            'user_id' => 1,
            'score' => 18,
            'points_earned' => 45,
            'points_max' => 50,
            'passed' => true,
            'total_questions' => 20,
            'submitted_at' => now(),
            'status' => 'submitted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new AiService;
        $result = $service->generateResponse("Mening natijalarimni ko'rsat", (object) ['id' => 1]);

        $this->assertTrue($result['success']);
        $this->assertSame('dynamic_data', $result['source']);
        $this->assertStringContainsString('Algebra testi', $result['text']);
        $this->assertStringContainsString("45 / 50 ball", $result['text']);
        $this->assertStringContainsString("O'tdi", $result['text']);
        $this->assertStringNotContainsString("Profil", $result['text']);
    }

    public function test_teacher_actions_include_exam_creation(): void
    {
        $service = new AiService;
        $teacher = new class
        {
            public function isAdmin(): bool
            {
                return false;
            }

            public function canManageExams(): bool
            {
                return true;
            }

            public function isTeacher(): bool
            {
                return true;
            }

            public function hasReachedCourseOpenLimit(): bool
            {
                return false;
            }

            public function hasCourseOpenApproval(): bool
            {
                return true;
            }
        };

        $actions = $service->suggestActions('imtihon yaratmoqchiman', $teacher);
        $routes = $this->extractRoutes($actions);

        $this->assertContains('profile.exams.create', $routes);
        $this->assertContains('teacher.courses.create', $routes);
    }

    public function test_admin_actions_include_dashboard_route(): void
    {
        $service = new AiService;
        $admin = new class
        {
            public function isAdmin(): bool
            {
                return true;
            }

            public function canManageExams(): bool
            {
                return false;
            }

            public function isTeacher(): bool
            {
                return false;
            }
        };

        $actions = $service->suggestActions("panelga o'tish", $admin);
        $routes = $this->extractRoutes($actions);

        $this->assertContains('dashboard', $routes);
    }

    public function test_knowledge_base_works_with_legacy_schema_without_synonyms_and_priority(): void
    {
        $this->recreateLegacyAiKnowledgeTable();

        DB::table('ai_knowledges')->insert([
            'question' => 'legacy schema test savol',
            'question_en' => null,
            'answer' => 'Legacy schema bilan ham AI knowledge ishlaydi.',
            'answer_en' => null,
            'keywords' => 'legacy, schema',
            'category' => 'Test',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new AiService;
        $result = $service->generateResponse('legacy schema test savol');

        $this->assertTrue($result['success']);
        $this->assertSame('knowledge_base', $result['source']);
        $this->assertStringContainsString('Legacy schema bilan ham AI knowledge ishlaydi.', $result['text']);
    }

    public function test_metadata_noise_words_do_not_trigger_knowledge_base_answer(): void
    {
        $this->recreateLegacyAiKnowledgeTable();

        DB::table('ai_knowledges')->insert([
            [
                'question' => 'Kurs asosda davomiyligi boshlanishi',
                'question_en' => null,
                'answer' => 'BU JAVOB TASODIFIY SOZGA CHIQMASLIGI KERAK.',
                'answer_en' => null,
                'keywords' => 'asosda, davomiyligi, boshlanishi, narxi',
                'category' => 'Kurs',
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Maxsusanchor qoidalari',
                'question_en' => null,
                'answer' => 'Maxsus anchor bo\'yicha qoidalar mavjud.',
                'answer_en' => null,
                'keywords' => 'maxsusanchor',
                'category' => 'Maktab',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $service = new AiService;

        foreach (['asasda', 'asosda', 'davomiyligi', 'boshlanishi', 'narxi'] as $question) {
            $result = $service->generateResponse($question);

            $this->assertTrue($result['success']);
            $this->assertSame('clarification', $result['source']);
            $this->assertStringNotContainsString('BU JAVOB TASODIFIY SOZGA CHIQMASLIGI KERAK.', $result['text']);
        }

        $validResult = $service->generateResponse('maxsusanchor');

        $this->assertTrue($validResult['success']);
        $this->assertSame('knowledge_base', $validResult['source']);
        $this->assertStringContainsString('Maxsus anchor bo\'yicha qoidalar mavjud.', $validResult['text']);
    }

    public function test_knowledge_snippets_for_prompt_work_with_legacy_schema(): void
    {
        $this->recreateLegacyAiKnowledgeTable();

        DB::table('ai_knowledges')->insert([
            'question' => 'prompt uchun eski schema',
            'question_en' => null,
            'answer' => 'Eski schema snippet',
            'answer_en' => null,
            'keywords' => 'prompt',
            'category' => 'Prompt',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new AiService;
        $result = $this->invokePrivate($service, 'knowledgeSnippetsForPrompt');

        $this->assertIsString($result);
        $this->assertStringContainsString('prompt uchun eski schema', $result);
        $this->assertStringContainsString('Eski schema snippet', $result);
    }

    public function test_prepare_conversation_context_enriches_follow_up_with_recent_topic(): void
    {
        $service = new AiService;

        $context = $service->prepareConversationContext('u qaysi biri?', [
            ['role' => 'user', 'text' => 'Qaysi kurslar bor?'],
            ['role' => 'assistant', 'text' => 'Hozirgi faol kurslarimiz mavjud.', 'source' => 'dynamic_data'],
        ]);

        $this->assertSame('course', $context['recent_topic']);
        $this->assertTrue($context['context_applied']);
        $this->assertStringContainsString('kurs', $context['resolved_message']);
    }

    public function test_prepare_conversation_context_does_not_touch_simple_greeting(): void
    {
        $service = new AiService;

        $context = $service->prepareConversationContext('salom', [
            ['role' => 'user', 'text' => 'Qaysi kurslar bor?'],
            ['role' => 'assistant', 'text' => 'Hozirgi faol kurslarimiz mavjud.', 'source' => 'dynamic_data'],
        ]);

        $this->assertSame('salom', $context['resolved_message']);
        $this->assertFalse($context['context_applied']);
    }

    public function test_prepare_conversation_context_does_not_turn_date_into_course_question(): void
    {
        $service = new AiService;

        $context = $service->prepareConversationContext('25.04.2026', [
            ['role' => 'user', 'text' => 'Qaysi kurslar bor?'],
            ['role' => 'assistant', 'text' => 'Hozirgi faol kurslarimiz mavjud.', 'source' => 'dynamic_data'],
        ]);

        $this->assertSame('25.04.2026', $context['resolved_message']);
        $this->assertFalse($context['context_applied']);
    }

    public function test_date_follow_up_does_not_list_courses_from_previous_context(): void
    {
        $this->recreateCourseCatalogTables();

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Course Creator',
            'email' => 'creator-date@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('teachers')->insert([
            'id' => 1,
            'full_name' => 'Abdullayeva Kamolat Shuxratovna',
            'slug' => 'abdullayeva-kamolat',
            'subject' => 'Kimyo',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('courses')->insert([
            'id' => 1,
            'teacher_id' => 1,
            'created_by' => 1,
            'title' => 'kimyo',
            'price' => 'fsd',
            'duration' => 'dsfds',
            'description' => 'Kimyo kursi.',
            'start_date' => '2026-04-25',
            'status' => Course::STATUS_PUBLISHED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new AiService;
        $result = $service->generateResponse('25.04.2026', null, [
            ['role' => 'user', 'text' => 'Qaysi kurslar bor?'],
            ['role' => 'assistant', 'text' => 'Hozir saytda nashr qilingan kurslar: kimyo', 'source' => 'dynamic_data'],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('clarification', $result['source']);
        $this->assertStringNotContainsString('kimyo', $result['text']);
        $this->assertStringNotContainsString('Hozir saytda nashr qilingan kurslar', $result['text']);

        $gibberishResult = $service->generateResponse('fsd', null, [
            ['role' => 'user', 'text' => 'Qaysi kurslar bor?'],
            ['role' => 'assistant', 'text' => 'Hozir saytda nashr qilingan kurslar: kimyo', 'source' => 'dynamic_data'],
        ]);

        $this->assertTrue($gibberishResult['success']);
        $this->assertSame('clarification', $gibberishResult['source']);
        $this->assertStringNotContainsString('kimyo', $gibberishResult['text']);
        $this->assertStringNotContainsString('Hozir saytda nashr qilingan kurslar', $gibberishResult['text']);

        $catalogResult = $service->generateResponse('kusrlar');

        $this->assertTrue($catalogResult['success']);
        $this->assertSame('dynamic_data', $catalogResult['source']);
        $this->assertStringContainsString('kimyo', $catalogResult['text']);
        $this->assertStringNotContainsString('Narxi: fsd', $catalogResult['text']);
        $this->assertStringNotContainsString('Davomiyligi: dsfds', $catalogResult['text']);
    }

    public function test_generate_response_understands_assalomu_alaykum_variants(): void
    {
        $service = new AiService;

        foreach (['asalomu alekum', 'assalomualaykum', 'salom aleykum'] as $variant) {
            $result = $service->generateResponse($variant);

            $this->assertTrue($result['success']);
            $this->assertSame('static_knowledge', $result['source']);
            $this->assertStringContainsString('AI yordamchisiman', $result['text']);
        }
    }

    public function test_prepare_conversation_context_does_not_touch_greeting_variant(): void
    {
        $service = new AiService;

        $context = $service->prepareConversationContext('asalomu alekum', [
            ['role' => 'user', 'text' => 'Qaysi kurslar bor?'],
            ['role' => 'assistant', 'text' => 'Hozirgi faol kurslarimiz mavjud.', 'source' => 'dynamic_data'],
        ]);

        $this->assertSame('asalomu alekum', $context['resolved_message']);
        $this->assertFalse($context['context_applied']);
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

    private function recreateLegacyAiKnowledgeTable(): void
    {
        Schema::dropIfExists('ai_knowledges');

        Schema::create('ai_knowledges', function (Blueprint $table): void {
            $table->id();
            $table->string('question');
            $table->string('question_en')->nullable();
            $table->text('answer');
            $table->text('answer_en')->nullable();
            $table->text('keywords')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        AiKnowledge::flushColumnPresenceCache();
    }

    private function recreateCourseCatalogTables(): void
    {
        Schema::dropIfExists('courses');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('teachers', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('slug')->unique();
            $table->string('subject')->nullable();
            $table->string('subject_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('created_by');
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('price', 100);
            $table->string('price_en', 100)->nullable();
            $table->string('duration', 120);
            $table->string('duration_en', 120)->nullable();
            $table->text('description');
            $table->date('start_date')->nullable();
            $table->string('status', 40)->index();
            $table->timestamps();
        });
    }

    private function recreateExamResultTables(): void
    {
        Schema::dropIfExists('results');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->unsignedInteger('total_points')->nullable();
            $table->unsignedInteger('passing_points')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('results', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('points_earned')->nullable();
            $table->unsignedInteger('points_max')->nullable();
            $table->boolean('passed')->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->timestamps();
        });
    }
}
