<?php

namespace App\Http\Controllers;

use App\Models\AiInteraction;
use App\Models\AiKnowledge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminAiKnowledgeController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $knowledges = AiKnowledge::query()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($w) use ($q): void {
                    $w->where('question', 'like', '%'.$q.'%')
                        ->orWhere('question_en', 'like', '%'.$q.'%')
                        ->orWhere('answer', 'like', '%'.$q.'%')
                        ->orWhere('answer_en', 'like', '%'.$q.'%')
                        ->orWhere('keywords', 'like', '%'.$q.'%')
                        ->orWhere('synonyms', 'like', '%'.$q.'%')
                        ->orWhere('category', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('priority')
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $analytics = [
            'total_questions' => 0,
            'clarification_count' => 0,
            'support_converted_count' => 0,
            'helpful_count' => 0,
            'unhelpful_count' => 0,
        ];
        $topQuestions = collect();
        $unansweredInteractions = collect();
        $supportInteractions = collect();

        if (Schema::hasTable('ai_interactions')) {
            $analytics = [
                'total_questions' => AiInteraction::query()->count(),
                'clarification_count' => AiInteraction::query()->where('clarification_requested', true)->count(),
                'support_converted_count' => AiInteraction::query()->where('support_converted', true)->count(),
                'helpful_count' => AiInteraction::query()->where('is_helpful', true)->count(),
                'unhelpful_count' => AiInteraction::query()->where('is_helpful', false)->count(),
            ];

            $topQuestions = AiInteraction::query()
                ->select('normalized_question', DB::raw('COUNT(*) as total'))
                ->whereNotNull('normalized_question')
                ->where('normalized_question', '!=', '')
                ->groupBy('normalized_question')
                ->orderByDesc('total')
                ->limit(8)
                ->get();

            $unansweredInteractions = AiInteraction::query()
                ->where('is_unanswered', true)
                ->latest('id')
                ->limit(8)
                ->get(['id', 'question', 'response_source', 'created_at']);

            $supportInteractions = AiInteraction::query()
                ->with('contactMessage:id,note')
                ->where('support_converted', true)
                ->latest('id')
                ->limit(8)
                ->get(['id', 'question', 'contact_message_id', 'created_at']);
        }

        return view('admin.ai_knowledge.index', compact(
            'knowledges',
            'q',
            'analytics',
            'topQuestions',
            'unansweredInteractions',
            'supportInteractions',
        ));
    }

    public function create(): View
    {
        return view('admin.ai_knowledge.create');
    }

    public function store(Request $request): RedirectResponse
    {
        AiKnowledge::create($this->validatedData($request));

        return redirect()
            ->route('ai-knowledges.index')
            ->with('success', 'AI bilim bazasiga yangi javob qo\'shildi.');
    }

    public function edit(AiKnowledge $aiKnowledge): View
    {
        return view('admin.ai_knowledge.edit', compact('aiKnowledge'));
    }

    public function update(Request $request, AiKnowledge $aiKnowledge): RedirectResponse
    {
        $aiKnowledge->update($this->validatedData($request));

        return redirect()
            ->route('ai-knowledges.index')
            ->with('success', 'AI bilim bazasi yangilandi.');
    }

    public function destroy(AiKnowledge $aiKnowledge): RedirectResponse
    {
        $aiKnowledge->delete();

        return redirect()
            ->route('ai-knowledges.index')
            ->with('success', 'AI bilim bazasidagi yozuv o\'chirildi.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'question_en' => ['nullable', 'string', 'max:1000'],
            'answer' => ['required', 'string', 'max:12000'],
            'answer_en' => ['nullable', 'string', 'max:12000'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'synonyms' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'integer', 'min:-100000', 'max:100000'],
            'sort_order' => ['nullable', 'integer', 'min:-100000', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['priority'] = (int) ($validated['priority'] ?? 0);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
