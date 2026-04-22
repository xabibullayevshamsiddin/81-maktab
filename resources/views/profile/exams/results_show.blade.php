<x-loyouts.main title="Imtihon Natijasi">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-results.css') }}?v={{ filemtime(public_path('temp/css/profile-results.css')) }}">
@endpush

<div class="container exam-public-container">
    <div class="results-header">
        <div class="results-breadcrumb">
            <a href="{{ route('profile.exams.results') }}">{{ __('public.layout.menu.exams') }}</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px; opacity: 0.5; align-self: center;"></i>
            <span>Natija tafsilotlari</span>
        </div>
        <h1 class="results-title">
            {{ $result->user->name }} — {{ $exam->title }}
            @if($exam?->trashed())
                <span class="badge bg-danger ms-2" style="font-size: 12px; vertical-align: middle;">O'chirilgan imtihon</span>
            @endif
        </h1>
        <p class="text-muted">Imtihon topshirish jarayoni va batafsil tahlili. Sinf: <strong>{{ $result->user_grade ?? $result->user->grade ?? '—' }}</strong></p>
    </div>

    <!-- Bento Stats Grid -->
    <div class="bento-grid">
        <div class="bento-card">
            <i class="fa-solid fa-calendar-check"></i>
            <span class="bento-label">Boshlangan vaqt</span>
            <span class="bento-value">{{ $result->started_at?->format('d.m.Y H:i') }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span class="bento-label">Tugallangan vaqt</span>
            <span class="bento-value">{{ $result->submitted_at?->format('d.m.Y H:i') ?? 'Tugallanmagan' }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-graduation-cap"></i>
            <span class="bento-label">Sinf</span>
            <span class="bento-value">{{ $result->user_grade ?? $result->user->grade ?? '—' }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-circle-info"></i>
            <span class="bento-label">Holat</span>
            <span class="bento-value">
                @php
                    $statusLabel = match($result->status) {
                        'submitted' => 'Topshirildi',
                        'expired' => 'Vaqti o\'tdi',
                        'started' => 'Jarayonda',
                        default => $result->status
                    };
                @endphp
                {{ $statusLabel }}
            </span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-award"></i>
            <span class="bento-label">To'plangan ball</span>
            <span class="bento-value">{{ $result->points_earned }} / {{ $result->points_max ?? ($exam?->total_points ?? 0) }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-shield-virus"></i>
            <span class="bento-label">Qoidabuzarliklar</span>
            <span class="bento-value">{{ $result->rule_violation_count }} ta</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-check-double"></i>
            <span class="bento-label">Yakuniy natija</span>
            <span class="bento-value">
                @if($result->passed === null)
                    <span class="tag-pending">Tekshiruvda</span>
                @elseif($result->passed)
                    <span class="tag-pass">O'tdi</span>
                @else
                    <span class="tag-fail">Yiqildi</span>
                @endif
            </span>
        </div>
    </div>

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h4 class="mb-0 fw-bold" style="color: var(--primary);">Batafsil javoblar</h4>
        <a href="{{ route('profile.exams.results') }}?exam_id={{ $exam->id }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Ortga qaytish
        </a>
    </div>

    @foreach($result->answers as $index => $answer)
        @php
            $question = $answer->question;
            $isCorrect = $answer->isCorrectAnswer();
            $borderClass = $isCorrect ? 'border-success' : 'border-danger';
            if ($question->isTextType() && $answer->is_correct_override === null) {
                $borderClass = 'border-warning';
            }
        @endphp
        
        <div class="q-preview-card border-top border-4 {{ $borderClass }}">
            <div class="q-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="q-number-badge">{{ $index + 1 }}</span>
                    <span class="text-uppercase fw-bold small text-muted">Savol</span>
                </div>
                <div class="badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $isCorrect ? 'text-success' : 'text-danger' }}">
                    {{ $isCorrect ? $question->points : 0 }} / {{ $question->points }} ball
                </div>
            </div>

            <div class="q-body">
                {!! nl2br(e($question->body)) !!}
            </div>

            @if($question->isTextType())
                <div class="answer-comparison">
                    <div class="answer-box box-student">
                        <span class="answer-box-label">O'quvchi javobi</span>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $answer->text_answer ?? 'Javob berilmagan' }}</p>
                    </div>
                    @if($question->model_answer)
                        <div class="answer-box box-correct">
                            <span class="answer-box-label">Namunaviy javob</span>
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $question->model_answer }}</p>
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-top">
                    @php
                        $isGraded = $answer->is_correct_override !== null;
                        $gradedAt = $answer->updated_at;
                        $lockMinutes = 10;
                        $isLocked = $isGraded && $gradedAt && $gradedAt->addMinutes($lockMinutes)->isPast();
                    @endphp

                    <form action="{{ route('profile.exams.grade', [$result, $answer]) }}" method="POST">
                        @csrf
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <div>
                                <label class="fw-bold text-muted small text-uppercase mb-2 d-block">Baholash:</label>
                                <div class="grading-options">
                                    <label class="grading-radio-label {{ $isLocked ? 'disabled' : '' }}">
                                        <input type="radio" name="is_correct" value="1" 
                                            {{ $answer->is_correct_override === true ? 'checked' : '' }} 
                                            {{ $isLocked ? 'disabled' : '' }} required>
                                        <div class="grading-radio-card card-correct">
                                            <i class="fa-solid fa-check"></i> To'g'ri
                                        </div>
                                    </label>
                                    <label class="grading-radio-label {{ $isLocked ? 'disabled' : '' }}">
                                        <input type="radio" name="is_correct" value="0" 
                                            {{ $answer->is_correct_override === false ? 'checked' : '' }} 
                                            {{ $isLocked ? 'disabled' : '' }} required>
                                        <div class="grading-radio-card card-incorrect">
                                            <i class="fa-solid fa-xmark"></i> Noto'g'ri
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="align-self-end">
                                @if(!$isLocked)
                                    <button type="submit" class="btn btn-primary btn-sm px-4 py-2">
                                        <i class="fa-solid fa-floppy-disk me-2"></i> {{ $isGraded ? 'Yangilash' : 'Saqlash' }}
                                    </button>
                                @else
                                    <div class="radio-lock-info text-danger fw-bold">
                                        <i class="fa-solid fa-lock"></i> 
                                        {{ $lockMinutes }} daqiqadan so'ng tahrirlash yopildi
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($isGraded && !$isLocked)
                            <div class="radio-lock-info mt-2">
                                <i class="fa-solid fa-clock"></i> 
                                Tahrirlash uchun {{ 10 - $gradedAt->diffInMinutes(now()) }} daqiqa qoldi
                            </div>
                        @endif
                    </form>
                </div>
            @else
                <div class="answer-box box-student">
                    <span class="answer-box-label">Tanlangan variant</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold">{{ $answer->option?->label ?? '-' }}.</span>
                        <span>{{ $answer->option?->body }}</span>
                        @if($isCorrect)
                            <i class="fa-solid fa-circle-check text-success ms-auto"></i>
                        @else
                            <i class="fa-solid fa-circle-xmark text-danger ms-auto"></i>
                        @endif
                    </div>
                </div>
                @if(!$isCorrect)
                    <p class="mt-2 text-danger small fw-bold">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> Noto'g'ri javob berilgan.
                    </p>
                @endif
            @endif
        </div>
    @endforeach

    <div class="mt-5 mb-5 text-center">
        <a href="{{ route('profile.exams.results') }}?exam_id={{ $exam->id }}" class="btn btn-primary px-5 py-3">
            <i class="fa-solid fa-arrow-left me-2"></i> Ro'yxatga qaytish
        </a>
    </div>
</div>
</x-loyouts.main>
