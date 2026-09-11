@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4" x-data="ieltsTest({{ $attempt->id }})" x-init="init()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold">{{ $attempt->test->title }}</h1>
        <div class="text-sm font-mono bg-gray-100 rounded px-3 py-1" x-text="timeLeftLabel"></div>
    </div>

    @foreach ($attempt->test->sections as $section)
        <div class="mb-10">
            <h2 class="text-lg font-semibold mb-3 capitalize">{{ $section->skill }}</h2>

            @foreach ($section->passages as $passage)
                @if ($passage->content && $section->skill !== 'writing')
                    <div class="prose prose-sm max-w-none bg-gray-50 border rounded-lg p-4 mb-4">
                        {!! nl2br(e($passage->content)) !!}
                    </div>
                @endif

                @if ($passage->audio_url)
                    <audio controls class="w-full mb-4" controlsList="nodownload noplaybackrate">
                        <source src="{{ $passage->audio_url }}">
                    </audio>
                @endif

                @foreach ($passage->questions as $question)
                    <div class="border rounded-lg p-4 mb-3">
                        <p class="font-medium mb-3">{{ $question->question_text }}</p>

                        @if ($question->type === 'multiple_choice' || $question->type === 'true_false_ng')
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="q{{ $question->id }}" value="{{ $option }}"
                                            @change="saveAnswer({{ $question->id }}, $event.target.value)">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($question->type === 'writing_task')
                            <textarea rows="10" class="w-full border rounded-lg p-3"
                                placeholder="Javobingizni shu yerga yozing (kamida 250 so'z)..."
                                @input.debounce.1000ms="saveAnswer({{ $question->id }}, $event.target.value)"></textarea>
                        @elseif ($question->type === 'speaking_task')
                            <button type="button" @click="toggleRecording({{ $question->id }})"
                                class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm">
                                <span x-text="recording ? 'To\'xtatish' : 'Ovoz yozishni boshlash'"></span>
                            </button>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    @endforeach

    <form method="POST" action="{{ route('ielts.submit', $attempt) }}">
        @csrf
        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold w-full">
            Testni yakunlash
        </button>
    </form>
</div>

<script>
// Alpine.js component — matches your existing Alpine-based frontend pattern.
// Anti-cheat hook (tab switch / blur) mirrors ExamController's rule-violation counter.
function ieltsTest(attemptId) {
    return {
        recording: false,
        secondsLeft: {{ $attempt->test->time_limit_minutes * 60 }},
        timeLeftLabel: '',
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) this.reportViolation(attemptId);
            });
        },
        tick() {
            if (this.secondsLeft <= 0) return;
            this.secondsLeft--;
            const m = Math.floor(this.secondsLeft / 60).toString().padStart(2, '0');
            const s = (this.secondsLeft % 60).toString().padStart(2, '0');
            this.timeLeftLabel = `${m}:${s}`;
        },
        saveAnswer(questionId, value) {
            fetch(`/ielts/attempt/${attemptId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ ielts_question_id: questionId, answer_text: value }),
            });
        },
        reportViolation(attemptId) {
            fetch(`/ielts/attempt/${attemptId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ ielts_question_id: 0, rule_violation: true }),
            });
        },
        toggleRecording() {
            this.recording = !this.recording;
            // TODO: hook up MediaRecorder API here for Speaking answers.
        },
    };
}
</script>
@endsection
