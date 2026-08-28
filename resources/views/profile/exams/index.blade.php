<x-layouts.main :title="__('public.profile_exams.page_title')">
@push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/profile-exams.css') }}?v={{ app_asset_version('temp/css/profile-exams.css') }}">
@endpush
<div class="container exam-public-container"><div class="row"><div class="col-12">
<div class="row">
  <div class="col-lg-12">
    <div class="exam-public-card mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
        <h6 class="mb-10">{{ __('public.profile_exams.title') }}</h6>
        <div>
          <a href="{{ route('profile.exams.results') }}" class="btn btn-outline mb-2 me-2">{{ __('public.profile_exams.general_results') }}</a>
          <a href="{{ route('profile.exams.create') }}" class="btn mb-2">{{ __('public.profile_exams.new_exam') }}</a>
        </div>
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => __('public.profile_exams.search_placeholder'),
        'action' => route('profile.exams.index'),
      ])

      <div class="table-wrapper exam-public-table-responsive mt-20">
        <table class="exam-public-table">
          <thead>
            <tr>
              <th>#</th>
              <th>{{ __('public.profile_exams.col_name') }}</th>
              <th>{{ __('public.profile_exams.col_questions') }}</th>
              <th>{{ __('public.profile_exams.col_points') }}</th>
              <th>{{ __('public.profile_exams.col_passing') }}</th>
              <th>{{ __('public.profile_exams.col_start_planned') }}</th>
              <th>{{ __('public.profile_exams.col_grades') }}</th>
              <th>Ishtirokchilar</th>
              <th>{{ __('public.profile_exams.col_actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @if($exams->isEmpty())
              <tr>
                <td colspan="9">{{ __('public.profile_exams.empty') }}</td>
              </tr>
            @else
              @foreach($exams as $exam)
                <tr>
                  <td data-label="#">{{ $exam->id }}</td>
                  <td data-label="{{ __('public.profile_exams.col_name') }}">{{ $exam->title }}</td>
                  <td data-label="{{ __('public.profile_exams.col_questions') }}">{{ $exam->questions_count }} / {{ $exam->required_questions }}</td>
                  <td data-label="{{ __('public.profile_exams.col_points') }}">{{ $exam->total_points }}</td>
                  <td data-label="{{ __('public.profile_exams.col_passing') }}">{{ $exam->passing_points ?? '-' }}</td>
                  <td data-label="{{ __('public.profile_exams.col_start_planned') }}">{{ $exam->availableFromLabel() ?? '—' }}</td>
                  <td data-label="{{ __('public.profile_exams.col_grades') }}" title="{{ $exam->allowedGradesLabel() }}">{{ $exam->allowedGradesLabel() }}</td>
                  <td data-label="Ishtirokchilar" style="{{ $exam->hasParticipantLimit() && $exam->isParticipantLimitReached() ? 'color:#dc2626;font-weight:600;' : '' }}">{{ $exam->participantLimitLabel() }}</td>
                  <td data-label="{{ __('public.profile_exams.col_actions') }}" style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('profile.exams.results', ['exam_id' => $exam->id]) }}" class="btn btn-info btn-sm">{{ __('public.profile_exams.results_btn') }}</a>
                    <a href="{{ route('profile.exams.questions.index', $exam) }}" class="btn btn-primary btn-sm">{{ __('public.profile_exams.questions_btn') }}</a>
                    <a href="{{ route('profile.exams.edit', $exam) }}" class="btn btn-warning btn-sm">{{ __('public.profile_exams.edit_btn') }}</a>
                    <form method="POST" action="{{ route('profile.exams.destroy', $exam) }}">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-danger btn-sm" type="submit">{{ __('public.profile_exams.delete_btn') }}</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      </div>
      @if($exams->hasPages())
        <div class="p-3">
          {{ $exams->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
</div></div></div>
</x-loyouts.main>
