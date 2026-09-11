<?php

namespace App\Http\Controllers;

use App\Models\IeltsAnswer;
use App\Models\IeltsAttempt;
use App\Models\IeltsPassage;
use App\Models\IeltsQuestion;
use App\Models\IeltsResult;
use App\Models\IeltsSection;
use App\Models\IeltsTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminIeltsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = IeltsTest::withCount(['sections', 'attempts'])
            ->with(['sections.passages.questions']);

        if ($q !== '') {
            $query->where('title', 'like', '%' . $q . '%');
        }

        $tests = $query->latest('id')->paginate(10)->appends($request->query());

        return view('admin.ielts.index', compact('tests', 'q'));
    }

    public function create()
    {
        return view('admin.ielts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:placement,full_mock',
            'time_limit_minutes' => 'required|integer|min:5|max:240',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $test = IeltsTest::create($data);

        // Standard IELTS sections avtomatik yaratiladi
        $sections = [
            ['skill' => 'reading', 'order' => 1, 'time_limit_minutes' => 15],
            ['skill' => 'writing', 'order' => 2, 'time_limit_minutes' => 20],
            ['skill' => 'listening', 'order' => 3, 'time_limit_minutes' => 15],
            ['skill' => 'speaking', 'order' => 4, 'time_limit_minutes' => 15],
        ];

        foreach ($sections as $s) {
            $test->sections()->create($s);
        }

        return redirect()->route('admin.ielts.show', $test)
            ->with('success', 'Yangi IELTS testi yaratildi! Endi bo\'limlarga matn va savollar qo\'shishingiz mumkin.');
    }

    public function show(IeltsTest $test)
    {
        $test->load([
            'sections.passages.questions' => fn ($q) => $q->orderBy('order'),
            'attempts' => fn ($q) => $q->latest()->take(5),
        ]);

        return view('admin.ielts.show', compact('test'));
    }

    public function edit(IeltsTest $test)
    {
        return view('admin.ielts.edit', compact('test'));
    }

    public function update(Request $request, IeltsTest $test)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:placement,full_mock',
            'time_limit_minutes' => 'required|integer|min:5|max:240',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $test->update($data);

        return redirect()->route('admin.ielts.index')
            ->with('success', 'IELTS test ma\'lumotlari yangilandi.');
    }

    public function destroy(IeltsTest $test)
    {
        $test->delete();

        return redirect()->route('admin.ielts.index')
            ->with('success', 'IELTS testi muvaffaqiyatli o\'chirildi.');
    }

    public function storePassage(Request $request, IeltsSection $section)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'audio_url' => 'nullable|string|max:1000',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac|max:30720',
        ]);

        $audioUrl = $data['audio_url'] ?? null;

        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('ielts/audio', 'public');
            $audioUrl = asset('storage/' . $path);
        }

        $section->passages()->create([
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
            'audio_url' => $audioUrl,
        ]);

        return redirect()->route('admin.ielts.show', $section->ielts_test_id)
            ->with('success', 'Yangi matn/vazifa qo\'shildi.');
    }

    public function updatePassage(Request $request, IeltsPassage $passage)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'audio_url' => 'nullable|string|max:1000',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac|max:30720',
        ]);

        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('ielts/audio', 'public');
            $data['audio_url'] = asset('storage/' . $path);
        }

        unset($data['audio_file']);
        $passage->update($data);

        return redirect()->route('admin.ielts.show', $passage->section->ielts_test_id)
            ->with('success', 'Matn/vazifa muvaffaqiyatli yangilandi.');
    }

    public function destroyPassage(IeltsPassage $passage)
    {
        $testId = $passage->section->ielts_test_id;
        $passage->delete();

        return redirect()->route('admin.ielts.show', $testId)
            ->with('success', 'Matn va uning barcha savollari o\'chirildi.');
    }

    public function storeQuestion(Request $request, IeltsPassage $passage)
    {
        $data = $request->validate([
            'type' => 'required|in:multiple_choice,true_false_ng,gap_fill,writing_task,speaking_task',
            'question_text' => 'required|string',
            'options_raw' => 'nullable|string',
            'correct_answer' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        $options = null;

        if ($data['type'] === 'multiple_choice') {
            if (!empty($data['options_raw'])) {
                $options = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $data['options_raw'])))));
            }
        } elseif ($data['type'] === 'true_false_ng') {
            $options = ['True', 'False', 'Not Given'];
        }

        $maxOrder = $passage->questions()->max('order') ?? 0;

        $passage->questions()->create([
            'type' => $data['type'],
            'question_text' => $data['question_text'],
            'options' => $options,
            'correct_answer' => $data['correct_answer'] ?? null,
            'order' => !empty($data['order']) ? (int) $data['order'] : ($maxOrder + 1),
        ]);

        return redirect()->route('admin.ielts.show', $passage->section->ielts_test_id)
            ->with('success', 'Savol muvaffaqiyatli qo\'shildi!');
    }

    public function updateQuestion(Request $request, IeltsQuestion $question)
    {
        $data = $request->validate([
            'type' => 'required|in:multiple_choice,true_false_ng,gap_fill,writing_task,speaking_task',
            'question_text' => 'required|string',
            'options_raw' => 'nullable|string',
            'correct_answer' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        $options = null;

        if ($data['type'] === 'multiple_choice') {
            if (!empty($data['options_raw'])) {
                $options = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $data['options_raw'])))));
            }
        } elseif ($data['type'] === 'true_false_ng') {
            $options = ['True', 'False', 'Not Given'];
        }

        $question->update([
            'type' => $data['type'],
            'question_text' => $data['question_text'],
            'options' => $options,
            'correct_answer' => $data['correct_answer'] ?? null,
            'order' => !empty($data['order']) ? (int) $data['order'] : $question->order,
        ]);

        return redirect()->route('admin.ielts.show', $question->passage->section->ielts_test_id)
            ->with('success', 'Savol ma\'lumotlari yangilandi!');
    }

    public function destroyQuestion(IeltsQuestion $question)
    {
        $testId = $question->passage->section->ielts_test_id;
        $question->delete();

        return redirect()->route('admin.ielts.show', $testId)
            ->with('success', 'Savol o\'chirildi.');
    }

    public function results(Request $request, IeltsTest $test)
    {
        $attempts = IeltsAttempt::with(['user', 'result'])
            ->where('ielts_test_id', $test->id)
            ->latest('id')
            ->paginate(20);

        return view('admin.ielts.results', compact('test', 'attempts'));
    }
}
