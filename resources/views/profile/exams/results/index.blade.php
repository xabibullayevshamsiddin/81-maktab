<x-loyouts.main title="Imtihon Natijalari">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-results.css') }}?v={{ filemtime(public_path('temp/css/profile-results.css')) }}">
@endpush

<div class="container exam-public-container">
    <div class="results-header">
        <div class="results-breadcrumb">
            <a href="{{ route('profile.exams.index') }}">{{ __('public.layout.menu.exams') }}</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px; opacity: 0.5; align-self: center;"></i>
            <span>Natijalar</span>
        </div>
        <h1 class="results-title">Imtihon Natijalari</h1>
        <p class="text-muted">Barcha topshirilgan imtihonlar va o'quvchilar ko'rsatkichlari bu yerda jamlangan.</p>
    </div>

    <div class="results-filter-bar">
        <form method="get" action="{{ route('profile.exams.results') }}" class="d-flex flex-wrap gap-3 align-items-end" style="flex: 1;" id="results-filter-form">
            <div style="flex: 1; min-width: 180px;">
                <label class="form-label fw-bold small text-uppercase mb-2" style="color: var(--primary);">Imtihon</label>
                <select name="exam_id" class="form-control" onchange="this.form.submit()" style="border-radius: 12px;">
                    <option value="">— Barcha imtihonlar —</option>
                    @foreach($exams as $ex)
                        <option value="{{ $ex->id }}" {{ (string) $selectedExamId === (string) $ex->id ? 'selected' : '' }}>
                            {{ $ex->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 140px;">
                <label class="form-label fw-bold small text-uppercase mb-2" style="color: var(--primary);">Boshlanish</label>
                <input type="date" name="date_from" class="form-control" style="border-radius: 12px;" value="{{ request('date_from') }}" onchange="this.form.submit()" />
            </div>

            <div style="min-width: 140px;">
                <label class="form-label fw-bold small text-uppercase mb-2" style="color: var(--primary);">Tugash</label>
                <input type="date" name="date_to" class="form-control" style="border-radius: 12px;" value="{{ request('date_to') }}" onchange="this.form.submit()" />
            </div>

            @if(request()->filled('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
        </form>

        <div style="flex: 1; max-width: 400px;">
            @include('admin.partials.search-bar', [
                'placeholder' => 'Ism, email yoki telefon...',
                'action' => route('profile.exams.results'),
                'hidden' => array_filter(['exam_id' => $selectedExamId, 'date_from' => request('date_from'), 'date_to' => request('date_to')]),
            ])
        </div>
    </div>

    @php
      $exportParams = array_filter([
        'exam_id' => $selectedExamId,
        'date_from' => request('date_from'),
        'date_to' => request('date_to'),
      ]);
    @endphp
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
      <a href="{{ route('profile.exams.results.export', $exportParams) }}" class="btn btn-sm" style="gap:6px;">
        <i class="fa-solid fa-file-csv"></i> Excel (CSV) export
      </a>
      <button type="button" class="btn btn-sm btn-outline" onclick="window.print()" style="gap:6px;">
        <i class="fa-solid fa-print"></i> Chop etish
      </button>
    </div>

    <div class="results-table-card">
        <div class="table-responsive">
            <table class="results-table">
                <thead>
                    <tr>
                        <th>O'quvchi</th>
                        @if(!$selectedExamId)
                            <th>Imtihon</th>
                        @endif
                        <th>Ball (Jami)</th>
                        <th>Status</th>
                        <th>Natija</th>
                        <th>Sana</th>
                        <th style="text-align: right;">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                        @php
                            $statusClass = match($result->status) {
                                'submitted' => 'status-submitted',
                                'started' => 'status-started',
                                'expired' => 'status-expired',
                                default => ''
                            };
                            $statusLabel = match($result->status) {
                                'submitted' => 'Topshirildi',
                                'started' => 'Jarayonda',
                                'expired' => 'Vaqti o\'tdi',
                                default => $result->status
                            };
                            $initials = strtoupper(substr($result->user->name ?? 'U', 0, 1));
                        @endphp
                        <tr>
                            <td>
                                <div class="user-info-cell">
                                    <div class="user-avatar-placeholder">{{ $initials }}</div>
                                    <div class="user-data">
                                        <span class="user-name">{{ $result->user->name ?? 'Noma\'lum o\'quvchi' }}</span>
                                        <span class="user-meta">{{ $result->user->phone ?? $result->user->email ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            @if(!$selectedExamId)
                                <td>
                                    <span class="fw-bold" style="color: #4b6282;">{{ $result->exam->title ?? '—' }}</span>
                                </td>
                            @endif
                            <td>
                                <div class="score-badge" style="background: rgba(13, 63, 120, 0.05); color: #0d3f78;">
                                    <i class="fa-solid fa-chart-simple"></i>
                                    {{ $result->points_earned ?? 0 }} / {{ $result->points_max ?? 0 }}
                                </div>
                            </td>
                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    @if($result->status === 'submitted') <i class="fa-solid fa-circle-check"></i> @endif
                                    @if($result->status === 'started') <i class="fa-solid fa-hourglass-half"></i> @endif
                                    @if($result->status === 'expired') <i class="fa-solid fa-triangle-exclamation"></i> @endif
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td>
                                @if($result->passed === null)
                                    <span class="result-tag tag-pending"><i class="fa-solid fa-clock-rotate-left"></i> Tekshiruvda</span>
                                @elseif($result->passed)
                                    <span class="result-tag tag-pass"><i class="fa-solid fa-square-check"></i> O‘tdi</span>
                                @else
                                    <span class="result-tag tag-fail"><i class="fa-solid fa-circle-xmark"></i> Yiqildi</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column" style="font-size: 13px;">
                                    <span class="fw-bold">{{ $result->submitted_at?->format('d.m.Y') ?? '-' }}</span>
                                    <span class="text-muted">{{ $result->submitted_at?->format('H:i') ?? '' }}</span>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('profile.exams.results.show', $result) }}" class="btn btn-primary btn-sm px-4">
                                    <i class="fa-solid fa-eye me-1"></i> Ko'rish
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $selectedExamId ? 6 : 7 }}" class="py-5 text-center text-muted">
                                <i class="fa-solid fa-folder-open mb-3" style="font-size: 40px; opacity: 0.2;"></i>
                                <p>Hali birorta ham natija mavjud emas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $results->links() }}
    </div>
</div>
</x-loyouts.main>
