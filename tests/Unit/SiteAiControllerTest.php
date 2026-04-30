<?php

namespace Tests\Unit;

use App\Http\Controllers\SiteAiController;
use App\Services\Ai\AiService;
use App\Services\Ai\ConversationHistoryStore;
use Tests\TestCase;

class SiteAiControllerTest extends TestCase
{
    public function test_support_wizard_interrupts_for_new_question_during_issue_type_step(): void
    {
        $controller = new SiteAiController(new AiService(), new ConversationHistoryStore());

        $result = $this->invokePrivate($controller, 'shouldInterruptSupportWizard', [
            'Bugun qanday darslar bor?',
            ['step' => 'issue_type'],
        ]);

        $this->assertTrue($result);
    }

    public function test_support_wizard_keeps_valid_issue_type_answer(): void
    {
        $controller = new SiteAiController(new AiService(), new ConversationHistoryStore());

        $result = $this->invokePrivate($controller, 'shouldInterruptSupportWizard', [
            'Texnik xato',
            ['step' => 'issue_type'],
        ]);

        $this->assertFalse($result);
    }

    public function test_support_wizard_interrupts_for_general_question_during_details_step(): void
    {
        $controller = new SiteAiController(new AiService(), new ConversationHistoryStore());

        $result = $this->invokePrivate($controller, 'shouldInterruptSupportWizard', [
            'Natijamni qayerdan ko\'raman?',
            ['step' => 'details'],
        ]);

        $this->assertTrue($result);
    }

    private function invokePrivate(SiteAiController $controller, string $method, array $arguments = []): mixed
    {
        $caller = \Closure::bind(
            fn (string $targetMethod, array $targetArguments) => $this->{$targetMethod}(...$targetArguments),
            $controller,
            SiteAiController::class
        );

        return $caller($method, $arguments);
    }
}
