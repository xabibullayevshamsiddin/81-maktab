<x-layouts.main :title="__('public.profile_results.my_page_title')">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-results.css') }}?v={{ app_asset_version('temp/css/profile-results.css') }}">
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
            <a href="{{ route('profile.show') }}">{{ __('public.profile_results.breadcrumb_profile') }}</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px; opacity: 0.5; align-self: center;"></i>
            <span>{{ __('public.profile_results.my_page_title') }}</span>
        </div>
        <div class="results-header-actions">
            <div>
                <h1 class="results-title">{{ __('public.profile_results.my_page_title') }}</h1>
                <p class="text-muted">{{ __('public.profile_results.intro') }}</p>
            </div>

            <div class="profile-results-actions">
                <button type="button" class="btn btn-sm btn-outline" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> {{ __('public.profile_results.print') }}
                </button>
                <a href="{{ route('profile.results.export') }}" class="btn btn-sm btn-outline">
                    <i class="fa-solid fa-file-csv"></i> {{ __('public.profile_results.export_all') }}
                </a>
            </div>
        </div>
    </div>

    <div class="bento-grid">
        <div class="bento-card">
            <i class="fa-solid fa-list-check"></i>
            <span class="bento-label">{{ __('public.profile_results.total_label') }}</span>
            <span class="bento-value">{{ $summaryTotal }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-circle-check"></i>
            <span class="bento-label">{{ __('public.profile_results.passed_count') }}</span>
            <span class="bento-value">{{ $summaryPassed }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-circle-xmark"></i>
            <span class="bento-label">{{ __('public.profile_results.failed_count') }}</span>
            <span class="bento-value">{{ $summaryFailed }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-hourglass-half"></i>
            <span class="bento-label">{{ __('public.profile_results.pending_count') }}</span>
            <span class="bento-value">{{ $summaryPending }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-chart-line"></i>
            <span class="bento-label">{{ __('public.profile_results.pass_rate') }}</span>
            <span class="bento-value">{{ $summaryPassRate }}%</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-chart-simple"></i>
            <span class="bento-label">{{ __('public.profile_results.average_score') }}</span>
            <span class="bento-value">{{ $summaryAverage !== null ? $summaryAverage : '-' }}</span>
        </div>
        <div class="bento-card">
            <i class="fa-solid fa-trophy"></i>
            <span class="bento-label">{{ __('public.profile_results.best_score') }}</span>
            <span class="bento-value">{{ $summaryBest !== null ? $summaryBest : '-' }}</span>
        </div>
    </div>

    @if($results->isNotEmpty())
        <div class="profile-exam-results-list">
            @foreach($results as $result)
                @php
                    $resultPercent = $result->points_max > 0 ? round($result->points_earned / $result->points_max * 100) : 0;
                    $resultStatusLabel = match ($result->status) {
                        'expired' => __('public.profile_results.status_expired'),
                        'submitted' => __('public.profile_results.status_submitted'),
                        default => __('public.profile_results.status_pending'),
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
                                <i class="fa-solid fa-circle-check"></i> {{ __('public.profile_results.status_passed') }}
                            @elseif($result->passed === false)
                                <i class="fa-solid fa-circle-xmark"></i> {{ __('public.profile_results.status_failed') }}
                            @else
                                <i class="fa-solid fa-hourglass-half"></i> {{ __('public.profile_results.status_pending') }}
                            @endif
                        </div>
                    </div>

                    <div class="profile-exam-result-bottom">
                        <div class="profile-exam-result-metric">
                            <span class="profile-exam-result-metric-val">{{ $result->points_earned ?? 0 }}<small>/{{ $result->points_max ?? 0 }}</small></span>
                            <span class="profile-exam-result-metric-label">{{ __('public.profile_results.points_metric') }}</span>
                        </div>
                        <div class="profile-exam-result-metric">
                            <span class="profile-exam-result-metric-val">{{ $result->score }}<small>/{{ $result->total_questions }}</small></span>
                            <span class="profile-exam-result-metric-label">{{ __('public.profile_results.correct_metric') }}</span>
                        </div>
                        <div class="profile-exam-result-metric">
                            <span class="profile-exam-result-metric-val">{{ $resultPercent }}%</span>
                            <span class="profile-exam-result-metric-label">{{ __('public.profile_results.percent_metric') }}</span>
                        </div>
                        <div class="profile-exam-result-metric">
                            <span class="profile-exam-result-metric-val" style="font-size:12px;">{{ $resultStatusLabel }}</span>
                            <span class="profile-exam-result-metric-label">{{ __('public.profile_results.status_metric') }}</span>
                        </div>
                    </div>

                    <div class="profile-actions-row results-card-actions">
                        <a href="{{ route('profile.results.single.export', $result) }}" class="btn btn-sm btn-outline">
                            <i class="fa-solid fa-file-csv"></i> {{ __('public.profile_results.export_single') }}
                        </a>
                        <a href="{{ route('profile.exams.results.show', $result) }}" class="btn btn-outline btn-sm w-100" style="justify-content:center;">
                            <i class="fa-solid fa-chart-pie me-1"></i> {{ __('public.profile_results.details_chart') }}
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
                <h3>{{ __('public.profile_results.empty_title') }}</h3>
                <p>{{ __('public.profile_results.empty_text') }}</p>
                <a href="{{ route('exam.index') }}" class="btn btn-sm">{{ __('public.profile_results.empty_action') }}</a>
            </div>
        </div>
    @endif
</div>
</x-loyouts.main>
