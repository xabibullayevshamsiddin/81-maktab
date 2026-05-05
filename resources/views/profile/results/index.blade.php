<x-loyouts.main title="Mening natijalarim">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-results.css') }}?v={{ filemtime(public_path('temp/css/profile-results.css')) }}">
@endpush

@php
    $summaryTotal = (int) ($resultSummary->total ?? 0);
    $summaryPassed = (int) ($resultSummary->passed_count ?? 0);
    $summaryFailed = (int) ($resultSummary->failed_count ?? 0);
    $summaryPending = max($summaryTotal - $summaryPassed - $summaryFailed, 0);
    $summaryPassRate = $summaryTotal > 0 ? round($summaryPassed / $summaryTotal * 100) : 0;
    $summaryAverage = $resultSummary->average_points !== null ? round((float) $resultSummary->average_points, 1) : null;
    $summaryBest = $resultSummary->best_points !== null ? round((float) $resultSummary->best_points, 1) : null;
@endphp

<div class="container exam-public-container">
    <div class="results-header">
        <div class="results-breadcrumb">
            <a href="{{ route('profile.show') }}">Profil</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px; opacity: 0.5; align-self: center;"></i>
            <span>Mening natijalarim</span>
        </div>
        <div class="results-header-actions">
            <div>
                <h1 class="results-title">Mening natijalarim</h1>
                <p class="text-muted">Topshirgan imtihonlaringiz, ballaringiz va batafsil tahlillar shu yerda alohida jamlangan.</p>
            </div>

            <div class="profile-results-actions">
                <button type="button" class="btn btn-sm btn-outline" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Chop etish
                </button>
                <a href="{{ route('profile.results.export') }}" class="btn btn-sm btn-outline">
                    <i class="fa-solid fa-file-csv"></i> Barchasini Excel (CSV)
                </a>
            </div>
        </div>
    </div>

    <div class="bento-grid">
        <div class="bento-card">
            <i class="fa-solid fa-list-check"></i>
            <span class="bento-label">Jami natijalar</span>
            <span class="bento-value">{{ $summaryTotal }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-circle-check"></i>
            <span class="bento-label">O'tganlar</span>
            <span class="bento-value">{{ $summaryPassed }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-circle-xmark"></i>
            <span class="bento-label">Yiqilganlar</span>
            <span class="bento-value">{{ $summaryFailed }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-hourglass-half"></i>
            <span class="bento-label">Tekshiruvda</span>
            <span class="bento-value">{{ $summaryPending }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-chart-line"></i>
            <span class="bento-label">O'tish darajasi</span>
            <span class="bento-value">{{ $summaryPassRate }}%</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-chart-simple"></i>
            <span class="bento-label">O'rtacha ball</span>
            <span class="bento-value">{{ $summaryAverage !== null ? $summaryAverage : '-' }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-trophy"></i>
            <span class="bento-label">Eng yaxshi ball</span>
            <span class="bento-value">{{ $summaryBest !== null ? $summaryBest : '-' }}</span>
        </div>
    </div>

    @if($results->isNotEmpty())
        <div class="profile-exam-results-list">
            @foreach($results as $result)
                @php
                    $resultPercent = $result->points_max > 0 ? round($result->points_earned / $result->points_max * 100) : 0;
                    $resultStatusLabel = match ($result->status) {
                        'expired' => 'Vaqt tugagan',
                        'submitted' => 'Topshirilgan',
                        default => 'Tekshiruvda',
                    };
                    $resultStateClass = $result->passed === true ? 'is-pass' : ($result->passed === false ? 'is-fail' : '');
                    $resultBadgeClass = $result->passed === true ? 'badge-pass' : ($result->passed === false ? 'badge-fail' : '');
                @endphp

                <div class="profile-exam-result-card {{ $resultStateClass }}">
                    <div class="profile-exam-result-top">
                        <div class="profile-exam-result-info">
                            <h4 class="profile-exam-result-title">{{ $result->exam->title ?? '-' }}</h4>
                            <span class="profile-exam-result-date">{{ $result->submitted_at?->format('d.m.Y H:i') ?? '-' }}</span>
                        </div>

                        <div class="profile-exam-result-badge {{ $resultBadgeClass }}">
                            @if($result->passed === true)
                                <i class="fa-solid fa-circle-check"></i> O'tdi
                            @elseif($result->passed === false)
                                <i class="fa-solid fa-circle-xmark"></i> Yiqildi
                            @else
                                <i class="fa-solid fa-hourglass-half"></i> Tekshiruvda
                            @endif
                        </div>
                    </div>

                    <div class="profile-exam-result-bottom">
                        <div class="profile-exam-result-metric">
                            <span class="profile-exam-result-metric-val">{{ $result->points_earned ?? 0 }}<small>/{{ $result->points_max ?? 0 }}</small></span>
                            <span class="profile-exam-result-metric-label">Ball</span>
                        </div>
                        <div class="profile-exam-result-metric">
                            <span class="profile-exam-result-metric-val">{{ $result->score }}<small>/{{ $result->total_questions }}</small></span>
                            <span class="profile-exam-result-metric-label">To'g'ri</span>
                        </div>
                        <div class="profile-exam-result-metric">
                            <span class="profile-exam-result-metric-val">{{ $resultPercent }}%</span>
                            <span class="profile-exam-result-metric-label">Foiz</span>
                        </div>
                        <div class="profile-exam-result-metric">
                            <span class="profile-exam-result-metric-val" style="font-size:12px;">{{ $resultStatusLabel }}</span>
                            <span class="profile-exam-result-metric-label">Holat</span>
                        </div>
                    </div>

                    <div class="profile-actions-row results-card-actions">
                        <a href="{{ route('profile.results.single.export', $result) }}" class="btn btn-sm btn-outline">
                            <i class="fa-solid fa-file-csv"></i> Faqat shu natija
                        </a>
                        <a href="{{ route('profile.exams.results.show', $result) }}" class="btn btn-outline btn-sm w-100" style="justify-content:center;">
                            <i class="fa-solid fa-chart-pie me-1"></i> Batafsil grafika va xatolarni ko'rish
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="results-pagination">
            {{ $results->links() }}
        </div>
    @else
        <div class="results-table-card">
            <div class="results-empty-state">
                <i class="fa-solid fa-folder-open"></i>
                <h3>Hali natija yo'q</h3>
                <p>Imtihon topshirganingizdan keyin barcha natijalar shu sahifada ko'rinadi.</p>
                <a href="{{ route('exam.index') }}" class="btn btn-sm">Imtihonlarga o'tish</a>
            </div>
        </div>
    @endif
</div>
</x-loyouts.main>
