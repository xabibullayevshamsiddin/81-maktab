@extends('admin.layouts.main')

@section('title', 'AI Review')

@section('content')
@php
  $filterKind = $kind ?? 'all';
@endphp
<div class="row">
  <div class="col-lg-12">
    <div class="card-style mb-30">
      <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;">
        <div>
          <h6 class="mb-5">AI Review</h6>
          <p class="text-sm mb-0" style="color:#64748b;">Moderator uchun alohida bo'lim. Bu yerda faqat muammoli AI javoblar chiqadi. Foydali deb belgilanganlar ko'rsatilmaydi.</p>
        </div>
      </div>

      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}</div>
      @endif

      <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
        <span class="text-sm text-muted me-1">Filtr:</span>
        @foreach ([
          'all' => 'Barchasi',
          'unhelpful' => 'Foydasiz',
          'unanswered' => 'Javob topilmagan',
          'support' => 'Supportga aylangan',
        ] as $key => $label)
          <a
            href="{{ route('admin.ai-reviews.index', array_filter(['kind' => $key === 'all' ? null : $key, 'q' => request('q')])) }}"
            class="btn btn-sm {{ $filterKind === $key ? 'btn-primary' : 'btn-outline-secondary' }}"
          >{{ $label }}</a>
        @endforeach
      </div>

      @include('admin.partials.search-bar', [
        'placeholder' => 'Savol, javob, source yoki sabab bo\'yicha...',
        'action' => route('admin.ai-reviews.index'),
        'hidden' => array_filter(['kind' => ($filterKind === 'all' ? null : $filterKind)]),
      ])

      <div class="table-wrapper table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Sana</th>
              <th>Savol</th>
              <th>Javob</th>
              <th>Holat</th>
              <th>Source</th>
              <th>Amallar</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reviews as $row)
              @php
                $feedbackReason = $row->meta['feedback_reason'] ?? null;
              @endphp
              <tr>
                <td>{{ $row->id }}</td>
                <td><span class="text-sm">{{ $row->created_at?->format('d.m.Y H:i') }}</span></td>
                <td style="min-width:220px;white-space:pre-wrap;word-break:break-word;">
                  <strong>{{ $row->question }}</strong>
                  @if(!empty($row->user_role))
                    <br><small class="text-muted">Rol: {{ $row->user_role }}</small>
                  @endif
                </td>
                <td style="min-width:260px;white-space:pre-wrap;word-break:break-word;">
                  @if(!empty($row->response_text))
                    <details>
                      <summary>Javobni ko'rish</summary>
                      <div class="mt-2">{{ $row->response_text }}</div>
                    </details>
                  @else
                    <span class="text-muted">Javob saqlanmagan</span>
                  @endif
                  @if($feedbackReason)
                    <div class="mt-2 text-danger"><strong>Sabab:</strong> {{ $feedbackReason }}</div>
                  @endif
                </td>
                <td>
                  @if($row->support_converted ?? false)
                    <span class="badge bg-info">Support</span><br>
                  @endif
                  @if($row->is_unanswered ?? false)
                    <span class="badge bg-warning text-dark">Unanswered</span><br>
                  @endif
                  @if($row->is_helpful === false)
                    <span class="badge bg-danger">Foydasiz</span>
                  @else
                    <span class="badge bg-secondary">Ko'rib chiqilsin</span>
                  @endif
                </td>
                <td>{{ $row->response_source ?: '—' }}</td>
                <td style="min-width:140px;">
                  <form method="POST" action="{{ route('admin.ai-reviews.destroy', $row->id) }}" class="d-inline" data-confirm="AI review yozuvi o‘chirilsinmi?" data-confirm-title="AI reviewni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="kind" value="{{ $filterKind === 'all' ? '' : $filterKind }}">
                    <button type="submit" class="btn btn-sm btn-danger w-100">O‘chirish</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Hozircha ko'rib chiqiladigan AI yozuvlari yo'q.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($reviews->hasPages())
        <div class="mt-3">{{ $reviews->links() }}</div>
      @endif
    </div>
  </div>
</div>
@endsection
