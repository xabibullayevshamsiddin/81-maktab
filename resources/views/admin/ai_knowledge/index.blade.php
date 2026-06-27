@extends('admin.layouts.main')

@section('title', 'AI Bilimlar Bazasi')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>AI Bilimlar Bazasi</h2>
          </div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">AI Bilimlar</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="tables-wrapper">
      <div class="row">
        <div class="col-lg-12">
          <div class="card-style mb-30">
            <div class="d-flex justify-content-between align-items-center mb-20">
              <div>
                <h6 class="mb-0">AI Bilimlar va Analytics</h6>
                <small class="text-muted">Yo'naltirish, unanswered savollar va supportga aylangan chatlar shu yerda.</small>
              </div>
              <div class="d-flex gap-2">
                @if(auth()->user()?->canManageInbox())
                  <a href="{{ route('admin.ai-reviews.index') }}" class="btn btn-outline-primary">AI Review</a>
                @endif
                <a href="{{ route('ai-knowledges.create') }}" class="btn btn-primary">Yangi qo'shish</a>
              </div>
            </div>

            @if (session('success'))
              <div class="alert-box success-alert mb-20">
                <div class="alert">{{ session('success') }}</div>
              </div>
            @endif

            <form method="GET" action="{{ route('ai-knowledges.index') }}" class="mb-20">
              <div class="row g-3">
                <div class="col-md-10">
                  <input type="search" class="form-control" name="q" value="{{ $q }}" placeholder="Savol, javob, keywords, synonyms yoki kategoriya bo'yicha qidirish">
                </div>
                <div class="col-md-2 d-grid">
                  <button type="submit" class="btn btn-outline-primary">Qidirish</button>
                </div>
              </div>
            </form>

            <div class="row g-3 mb-20">
              <div class="col-lg-3 col-sm-6">
                <div class="border rounded p-3 h-100">
                  <small class="text-muted d-block">Jami AI savollar</small>
                  <strong style="font-size:24px;">{{ number_format($analytics['total_questions']) }}</strong>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6">
                <div class="border rounded p-3 h-100">
                  <small class="text-muted d-block">Aniqlashtirish so'ralgan</small>
                  <strong style="font-size:24px;">{{ number_format($analytics['clarification_count']) }}</strong>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6">
                <div class="border rounded p-3 h-100">
                  <small class="text-muted d-block">Supportga aylangan</small>
                  <strong style="font-size:24px;">{{ number_format($analytics['support_converted_count']) }}</strong>
                </div>
              </div>
              <div class="col-lg-3 col-sm-6">
                <div class="border rounded p-3 h-100">
                  <small class="text-muted d-block">Feedback</small>
                  <strong style="font-size:24px;">{{ number_format($analytics['helpful_count']) }} / {{ number_format($analytics['unhelpful_count']) }}</strong>
                  <small class="text-muted d-block">foydali / foydasiz</small>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-25">
              <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                  <h6 class="mb-15">Eng ko'p savollar</h6>
                  @forelse($topQuestions as $item)
                    <div class="d-flex justify-content-between gap-3 mb-10">
                      <span>{{ \Illuminate\Support\Str::limit($item->normalized_question, 70) }}</span>
                      <strong>{{ $item->total }}</strong>
                    </div>
                  @empty
                    <p class="text-muted mb-0">Hali analytics ma'lumoti yo'q.</p>
                  @endforelse
                </div>
              </div>
              <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                  <h6 class="mb-15">Javob topilmagan savollar</h6>
                  @forelse($unansweredInteractions as $item)
                    <div class="mb-10">
                      <strong class="d-block">{{ \Illuminate\Support\Str::limit($item->question, 70) }}</strong>
                      @if(!empty($item->meta['feedback_reason']))
                        <small class="d-block text-danger">{{ $item->meta['feedback_reason'] }}</small>
                      @endif
                      <small class="text-muted">
                        {{ $item->created_at?->format('d.m.Y H:i') }}
                        @if($item->response_source)
                          | {{ $item->response_source }}
                        @endif
                      </small>
                    </div>
                  @empty
                    <p class="text-muted mb-0">Noaniq savollar hozircha yo'q.</p>
                  @endforelse
                </div>
              </div>
              <div class="col-lg-4">
                <div class="border rounded p-3 h-100">
                  <h6 class="mb-15">Supportga aylanganlar</h6>
                  @forelse($supportInteractions as $item)
                    <div class="mb-10">
                      <strong class="d-block">{{ \Illuminate\Support\Str::limit($item->question, 70) }}</strong>
                      <small class="text-muted">
                        {{ $item->contactMessage?->note ?: 'AI wizard murojaati' }}
                        | {{ $item->created_at?->format('d.m.Y H:i') }}
                      </small>
                    </div>
                  @empty
                    <p class="text-muted mb-0">Supportga aylangan chatlar hozircha yo'q.</p>
                  @endforelse
                </div>
              </div>
            </div>

            <div class="border rounded p-3 mb-25">
              <div class="d-flex justify-content-between align-items-center mb-15">
                <h6 class="mb-0">Oxirgi Muammoli AI Savol-Javoblar</h6>
                <div class="d-flex align-items-center gap-2">
                  <small class="text-muted">Faqat moderator ko'rib chiqishi kerak bo'lgan yozuvlar. Foydali deb belgilanganlar chiqmaydi.</small>
                  @if(auth()->user()?->canManageInbox())
                    <form
                      method="POST"
                      action="{{ route('admin.ai-reviews.destroy-unhelpful') }}"
                      data-confirm="Foydasiz deb belgilangan barcha AI review yozuvlari o‘chirilsinmi?"
                      data-confirm-title="Foydasiz AI reviewlarni tozalash"
                      data-confirm-variant="danger"
                      data-confirm-ok="Ha, o'chirish"
                    >
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger">Foydasizlarni tozalash</button>
                    </form>
                  @endif
                </div>
              </div>

              @forelse($recentInteractions as $item)
                <div class="border rounded p-3 mb-10">
                  <strong class="d-block mb-1">Savol:</strong>
                  <p class="mb-2">{{ $item->question }}</p>

                  <strong class="d-block mb-1">Javob:</strong>
                  <p class="mb-2">
                    {{ \Illuminate\Support\Str::limit($item->response_text ?: 'Javob saqlanmagan', 220) }}
                  </p>

                  @if(!empty($item->meta['feedback_reason']))
                    <small class="d-block text-danger mb-1">Sabab: {{ $item->meta['feedback_reason'] }}</small>
                  @endif

                  <small class="text-muted">
                    {{ $item->created_at?->format('d.m.Y H:i') }}
                    @if(!empty($item->response_source))
                      | source: {{ $item->response_source }}
                    @endif
                    @if($item->is_helpful === true)
                      | feedback: foydali
                    @elseif($item->is_helpful === false)
                      | feedback: foydasiz
                    @endif
                  </small>
                  @if(auth()->user()?->canManageInbox())
                    <form
                      method="POST"
                      action="{{ route('admin.ai-reviews.destroy', $item->id) }}"
                      class="mt-2"
                      data-confirm="Bu AI review yozuvi o‘chirilsinmi?"
                      data-confirm-title="AI reviewni o'chirish"
                      data-confirm-variant="danger"
                      data-confirm-ok="O'chirish"
                    >
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">Savol-javobni o‘chirish</button>
                    </form>
                  @endif
                </div>
              @empty
                <p class="text-muted mb-0">AI dialoglar hali saqlanmagan.</p>
              @endforelse
            </div>

            <div class="table-wrapper table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th><h6>Savol (Pattern)</h6></th>
                    <th><h6>Kalit so'zlar</h6></th>
                    <th><h6>Sinonimlar</h6></th>
                    <th><h6>Kategoriya</h6></th>
                    <th><h6>Priority</h6></th>
                    <th><h6>Holati</h6></th>
                    <th><h6>Amallar</h6></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($knowledges as $item)
                    <tr>
                      <td>
                        <p><strong>{{ $item->question }}</strong></p>
                        @if($item->question_en) <small class="text-muted">{{ $item->question_en }}</small> @endif
                      </td>
                      <td><p>{{ $item->keywords }}</p></td>
                      <td><p>{{ $item->synonyms ?: '-' }}</p></td>
                      <td><p>{{ $item->category ?? '-' }}</p></td>
                      <td><p>{{ $item->priority }}</p></td>
                      <td>
                        <span class="status-btn {{ $item->is_active ? 'success-btn' : 'close-btn' }}">
                          {{ $item->is_active ? 'Faol' : 'Faol emas' }}
                        </span>
                      </td>
                      <td>
                        <div class="action">
                          <a href="{{ route('ai-knowledges.edit', $item->id) }}" class="text-warning me-2" title="Tahrirlash">
                            <i class="lni lni-pencil-alt"></i>
                          </a>
                          <form action="{{ route('ai-knowledges.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Haqiqatan ham o\'chirmoqchimisiz?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger" title="O'chirish" style="background:none;border:none;padding:0;">
                              <i class="lni lni-trash-can"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center"><p>Hozircha ma'lumotlar yo'q.</p></td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @if($knowledges->hasPages())
              <div class="p-3">
                {{ $knowledges->links() }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
