<x-layouts.main :title="$attempt->test->title . ' — 81-IDUM'">
@push('page_styles')
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    .ielts-wrap { max-width: 860px; margin: 0 auto; padding: 40px 16px 80px; }
    [data-theme='dark'] .ielts-card { background: #1e293b; border-color: #334155; color: #f8fafc; }
    [data-theme='dark'] .ielts-passage-box { background: #0f172a; border-color: #1e293b; color: #cbd5e1; }
    [data-theme='dark'] .ielts-timer-badge { background: #0f172a; border-color: #334155; color: #38bdf8; }
    [data-theme='dark'] textarea.ielts-input { background: #0f172a; border-color: #334155; color: #f8fafc; }
</style>
@endpush

<div class="ielts-wrap" x-data="ieltsTest({{ $attempt->id }})" x-init="init()">
    <div class="flex items-center justify-between gap-4 mb-8 pb-4 border-b border-gray-200 dark:border-gray-700">
        <div>
            <a href="{{ route('ielts.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold uppercase tracking-wider hover:underline">
                <i class="fa-solid fa-arrow-left mr-1"></i> IELTS testlariga qaytish
            </a>
            <h1 class="text-xl sm:text-2xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ $attempt->test->title }}</h1>
        </div>
        <div class="ielts-timer-badge text-sm sm:text-base font-mono bg-indigo-50 dark:bg-slate-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-slate-700 rounded-xl px-4 py-2 flex items-center gap-2 shadow-sm font-bold whitespace-nowrap">
            <i class="fa-regular fa-clock text-indigo-500"></i>
            <span x-text="timeLeftLabel">00:00</span>
        </div>
    </div>

    @foreach ($attempt->test->sections as $section)
        <div class="mb-12">
            <div class="flex items-center gap-2.5 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                <h2 class="text-lg sm:text-xl font-bold capitalize text-gray-900 dark:text-white">{{ $section->skill }} bo'limi</h2>
            </div>

            @foreach ($section->passages as $passage)
                @if ($passage->content && $section->skill !== 'writing')
                    <div class="ielts-passage-box prose prose-sm max-w-none bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 mb-6 text-gray-800 dark:text-gray-200 leading-relaxed">
                        @if ($passage->title)
                            <h3 class="font-bold text-base mb-3 text-indigo-700 dark:text-indigo-300">{{ $passage->title }}</h3>
                        @endif
                        {!! nl2br(e($passage->content)) !!}
                    </div>
                @endif

                @if ($passage->audio_url)
                    <div class="bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-4 mb-6">
                        <audio controls class="w-full" controlsList="nodownload noplaybackrate">
                            <source src="{{ $passage->audio_url }}">
                            Brauzeringiz audio formatini qo'llab-quvvatlamaydi.
                        </audio>
                    </div>
                @endif

                @foreach ($passage->questions as $question)
                    <div class="ielts-card border border-gray-200 dark:border-gray-700 rounded-2xl p-5 sm:p-6 mb-5 bg-white dark:bg-slate-800 shadow-sm">
                        <p class="font-semibold text-base mb-4 text-gray-900 dark:text-white flex items-start gap-2">
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $loop->iteration }}.</span>
                            <span>{{ $question->question_text }}</span>
                        </p>

                        @if ($question->type === 'multiple_choice' || $question->type === 'true_false_ng')
                            <div class="space-y-3">
                                @foreach ($question->options ?? [] as $option)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 cursor-pointer transition">
                                        <input type="radio" name="q{{ $question->id }}" value="{{ $option }}"
                                            class="w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                                            @change="saveAnswer({{ $question->id }}, $event.target.value)">
                                        <span class="text-sm sm:text-base text-gray-800 dark:text-gray-200">{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($question->type === 'writing_task')
                            <textarea rows="11" class="ielts-input w-full border border-gray-300 dark:border-gray-600 rounded-xl p-4 text-sm sm:text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                                placeholder="Javobingizni shu yerga yozing (kamida 250 so'z)..."
                                @input.debounce.1000ms="saveAnswer({{ $question->id }}, $event.target.value)"></textarea>
                        @elseif ($question->type === 'speaking_task')
                            <button type="button" @click="toggleRecording({{ $question->id }})"
                                class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
                                <i class="fa-solid fa-microphone"></i>
                                <span x-text="recording ? 'To\'xtatish' : 'Ovoz yozishni boshlash'"></span>
                            </button>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    @endforeach

    <form method="POST" action="{{ route('ielts.submit', $attempt) }}" onsubmit="return confirm('Testni yakunlab, natijalarni hisoblashga rozimisiz?')">
        @csrf
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-bold w-full text-base sm:text-lg shadow-lg hover:shadow-indigo-500/25 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-circle-check"></i> Testni yakunlash
        </button>
    </form>
</div>

<script>
function ieltsTest(attemptId) {
    const answerBaseUrl = @json(url('ielts/attempt'));
    const csrfToken = @json(csrf_token());

    return {
        recording: false,
        secondsLeft: {{ (int) ($attempt->test->time_limit_minutes * 60) }},
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
            fetch(`${answerBaseUrl}/${attemptId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ielts_question_id: questionId, answer_text: value }),
            });
        },
        reportViolation(attemptId) {
            fetch(`${answerBaseUrl}/${attemptId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ielts_question_id: 0, rule_violation: true }),
            });
        },
        toggleRecording() {
            this.recording = !this.recording;
        },
    };
}
</script>
</x-layouts.main>
