<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledge;
use Illuminate\Http\Request;

class AdminAiKnowledgeController extends Controller
{
    public function index()
    {
        $knowledges = AiKnowledge::orderBy('sort_order')->orderByDesc('created_at')->paginate(20);
        return view('admin.ai_knowledge.index', compact('knowledges'));
    }

    public function create()
    {
        return view('admin.ai_knowledge.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'question_en' => 'nullable|string',
            'answer' => 'required|string',
            'answer_en' => 'nullable|string',
            'keywords' => 'nullable|string',
            'category' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        AiKnowledge::create($validated);

        return redirect()->route('admin.ai-knowledges.index')
            ->with('success', 'AI ma\'lumoti muvaffaqiyatli qo\'shildi.');
    }

    public function edit(AiKnowledge $aiKnowledge)
    {
        return view('admin.ai_knowledge.edit', compact('aiKnowledge'));
    }

    public function update(Request $request, AiKnowledge $aiKnowledge)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'question_en' => 'nullable|string',
            'answer' => 'required|string',
            'answer_en' => 'nullable|string',
            'keywords' => 'nullable|string',
            'category' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $aiKnowledge->update($validated);

        return redirect()->route('admin.ai-knowledges.index')
            ->with('success', 'AI ma\'lumoti yangilandi.');
    }

    public function destroy(AiKnowledge $aiKnowledge)
    {
        $aiKnowledge->delete();
        return redirect()->route('admin.ai-knowledges.index')
            ->with('success', 'AI ma\'lumoti o\'chirildi.');
    }
}
