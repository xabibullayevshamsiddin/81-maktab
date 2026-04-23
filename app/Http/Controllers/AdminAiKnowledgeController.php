<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                        ->orWhere('category', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.ai_knowledge.index', compact('knowledges', 'q'));
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
            'category' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:-100000', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
