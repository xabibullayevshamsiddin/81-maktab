<?php

namespace Tests\Unit;

use App\Services\Ai\ConversationHistoryStore;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ConversationHistoryStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ai.history_store' => 'array',
            'ai.history_ttl_minutes' => 180,
            'ai.history_max_items' => 6,
        ]);

        Cache::store('array')->flush();
    }

    public function test_remember_turn_stores_question_and_answer_in_cache(): void
    {
        $store = new ConversationHistoryStore();

        $store->rememberTurn(12, 'Assalomu alaykum', [
            'text' => 'Va alaykum assalom!',
            'source' => 'static_knowledge',
        ]);

        $history = $store->historyForUser(12);

        $this->assertCount(2, $history);
        $this->assertSame('user', $history[0]['role']);
        $this->assertSame('Assalomu alaykum', $history[0]['text']);
        $this->assertSame('assistant', $history[1]['role']);
        $this->assertSame('Va alaykum assalom!', $history[1]['text']);
    }

    public function test_support_wizard_turns_are_not_persisted(): void
    {
        $store = new ConversationHistoryStore();

        $store->rememberTurn(9, 'Muammo bor', [
            'text' => 'Muammo turi qaysi?',
            'source' => 'support_wizard',
        ]);

        $this->assertSame([], $store->historyForUser(9));
    }

    public function test_history_is_trimmed_to_configured_window(): void
    {
        config(['ai.history_max_items' => 4]);

        $store = new ConversationHistoryStore();

        $store->rememberTurn(5, 'birinchi', ['text' => 'javob 1', 'source' => 'api']);
        $store->rememberTurn(5, 'ikkinchi', ['text' => 'javob 2', 'source' => 'api']);
        $store->rememberTurn(5, 'uchinchi', ['text' => 'javob 3', 'source' => 'api']);

        $history = $store->historyForUser(5);

        $this->assertCount(4, $history);
        $this->assertSame('ikkinchi', $history[0]['text']);
        $this->assertSame('javob 2', $history[1]['text']);
        $this->assertSame('uchinchi', $history[2]['text']);
        $this->assertSame('javob 3', $history[3]['text']);
    }
}
