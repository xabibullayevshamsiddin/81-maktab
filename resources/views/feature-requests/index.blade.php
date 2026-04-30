<x-loyouts.main title="Taklif va ovoz berish">
  @push('page_styles')
    <style>
      .feature-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
      }
      .feature-item {
        border: 1px solid var(--border);
        background: var(--surface);
        border-radius: 16px;
        padding: 16px;
      }
      .feature-item-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
      }
      .feature-item-title {
        margin: 0 0 6px;
        color: var(--primary);
      }
      .feature-item-meta {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
      }
      .feature-item-body {
        margin: 12px 0 0;
        color: var(--text);
        white-space: pre-line;
      }
      .feature-controls {
        margin-top: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
      }
      .feature-inline-form {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
      }
      .feature-delete-btn {
        border-color: #ef4444 !important;
        color: #b91c1c !important;
      }
      .feature-replies-wrap {
        margin-top: 12px;
        border-top: 1px solid var(--border);
        padding-top: 12px;
      }
      .feature-replies-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: 8px;
      }
      .feature-replies-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }
      .feature-reply-card {
        border-radius: 12px;
        padding: 12px 14px;
        border: 1px solid rgba(13, 63, 120, 0.1);
        background: rgba(13, 63, 120, 0.04);
        animation: featureReplyPop .22s ease both;
      }
      .feature-reply-card.is-admin {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.16), rgba(2, 132, 199, 0.12));
        border-color: rgba(6, 182, 212, 0.45);
        box-shadow: 0 0 0 1px rgba(34, 211, 238, 0.22), 0 10px 24px rgba(8, 145, 178, 0.18);
        animation: featureReplyPop .22s ease both, adminGlow 2s ease-in-out infinite alternate;
      }
      .feature-reply-card.is-admin::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 12px;
        pointer-events: none;
        background: linear-gradient(115deg, transparent 25%, rgba(255,255,255,0.2) 45%, transparent 65%);
        transform: translateX(-120%);
        z-index: 0;
        animation: adminShine 3s ease-in-out infinite;
      }
      .feature-reply-card.is-admin > * {
        position: relative;
        z-index: 1;
      }
      .feature-reply-card.is-super-admin {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        background: linear-gradient(135deg, rgba(88, 28, 135, 0.16), rgba(30, 64, 175, 0.12));
        border-color: rgba(99, 102, 241, 0.45);
        box-shadow: 0 0 0 1px rgba(129, 140, 248, 0.22), 0 10px 24px rgba(67, 56, 202, 0.18);
        animation: featureReplyPop .22s ease both, superAdminGlow 1.8s ease-in-out infinite alternate;
      }
      .feature-reply-card.is-super-admin::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 12px;
        pointer-events: none;
        background: linear-gradient(115deg, transparent 25%, rgba(255,255,255,0.22) 45%, transparent 65%);
        transform: translateX(-120%);
        z-index: 0;
        animation: superAdminShine 2.8s ease-in-out infinite;
      }
      .feature-reply-card.is-super-admin > * {
        position: relative;
        z-index: 1;
      }
      .feature-reply-card.is-moderator {
        background: rgba(245, 158, 11, 0.08);
        border-color: rgba(217, 119, 6, 0.25);
      }
      .feature-reply-head {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 6px;
      }
      .feature-reply-role {
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 999px;
      }
      .feature-reply-meta {
        margin-left: auto;
        font-size: 12px;
        color: var(--muted);
      }
      .feature-reply-footer {
        margin-top: 8px;
        display: flex;
        justify-content: flex-end;
      }
      .feature-reply-message {
        margin: 2px 0 0;
        color: var(--text);
        line-height: 1.45;
      }
      .feature-reply-delete-btn {
        border-color: #ef4444 !important;
        color: #b91c1c !important;
        padding: 3px 8px !important;
        font-size: 12px !important;
        line-height: 1.2;
      }
      .feature-actions-tip {
        font-size: 12px;
        color: var(--muted);
      }
      @keyframes featureReplyPop {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0) scale(1); }
      }
      @keyframes superAdminGlow {
        from { box-shadow: 0 0 0 1px rgba(129, 140, 248, 0.18), 0 8px 20px rgba(67, 56, 202, 0.14); }
        to { box-shadow: 0 0 0 1px rgba(129, 140, 248, 0.35), 0 12px 28px rgba(67, 56, 202, 0.25); }
      }
      @keyframes superAdminShine {
        0% { transform: translateX(-120%); opacity: 0; }
        18% { opacity: .75; }
        40% { transform: translateX(120%); opacity: 0; }
        100% { transform: translateX(120%); opacity: 0; }
      }
      @keyframes adminGlow {
        from { box-shadow: 0 0 0 1px rgba(34, 211, 238, 0.18), 0 8px 20px rgba(8, 145, 178, 0.14); }
        to { box-shadow: 0 0 0 1px rgba(34, 211, 238, 0.35), 0 12px 28px rgba(8, 145, 178, 0.25); }
      }
      @keyframes adminShine {
        0% { transform: translateX(-120%); opacity: 0; }
        20% { opacity: .7; }
        45% { transform: translateX(120%); opacity: 0; }
        100% { transform: translateX(120%); opacity: 0; }
      }
    </style>
  @endpush

  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">Feature Voting</span>
        <h1 class="js-split-text">Qaysi funksiya kerak?</h1>
        <p>Yangi taklif yozing va foydalanuvchilar qaysi funksiyani ko'proq xohlashini ovozlar orqali ko'ring.</p>
      </div>
    </div>
  </section>

  <main>
    <section class="container courses-filter-section prime-reveal" style="padding-top: 40px; padding-bottom: 60px;">
      @auth
        <form method="POST" action="{{ route('feature-requests.store') }}" class="exam-filter-panel" style="margin-bottom: 30px;">
          @csrf
          <div class="exam-filter-row">
            <div class="exam-filter-field" style="flex:1;">
              <label class="exam-filter-label" for="feature-title">Taklif nomi</label>
              <input id="feature-title" type="text" name="title" class="exam-filter-input" maxlength="180" required value="{{ old('title') }}" placeholder="Masalan: Telegram bot orqali bildirishnoma">
            </div>
          </div>
          <div class="exam-filter-row" style="margin-top:10px;">
            <div class="exam-filter-field" style="flex:1;">
              <label class="exam-filter-label" for="feature-description">Qisqacha izoh (ixtiyoriy)</label>
              <textarea id="feature-description" name="description" class="exam-filter-input" rows="3" maxlength="3000" placeholder="Nima uchun kerakligini qisqacha yozing...">{{ old('description') }}</textarea>
            </div>
          </div>
          <div style="margin-top: 14px;">
            <button type="submit" class="btn btn-prime">
              <i class="fa-solid fa-plus"></i> Taklif qo'shish
            </button>
          </div>
        </form>
      @else
        <div class="empty-state" style="margin-bottom: 24px;">
          <p>Taklif qo'shish va ovoz berish uchun avval tizimga kiring.</p>
          <a href="{{ route('login') }}" class="btn btn-prime" style="margin-top: 10px;">Tizimga kirish</a>
        </div>
      @endauth

      <div class="section-head" style="text-align:left; margin-bottom: 20px;">
        <h2>Eng ko'p ovoz olgan takliflar</h2>
      </div>

      @if($featureRequests->count() === 0)
        <div class="empty-state">
          <p>Hozircha takliflar yo'q. Birinchi taklifni siz yozing.</p>
        </div>
      @else
        <div class="feature-list">
          @foreach($featureRequests as $requestItem)
            @php
              $authorName = trim((string) ($requestItem->user->first_name ?? '').' '.(string) ($requestItem->user->last_name ?? ''));
              if ($authorName === '') {
                  $authorName = $requestItem->user->name ?? 'Foydalanuvchi';
              }
              $hasVoted = in_array($requestItem->id, $votedRequestIds, true);
              $statusLabel = match ($requestItem->status) {
                \App\Models\FeatureRequest::STATUS_PLANNED => "Rejada",
                \App\Models\FeatureRequest::STATUS_IN_PROGRESS => "Jarayonda",
                \App\Models\FeatureRequest::STATUS_DONE => "Bajarildi",
                \App\Models\FeatureRequest::STATUS_REJECTED => "Rad etildi",
                default => "Ko'rib chiqilmoqda",
              };
              $statusStyle = match ($requestItem->status) {
                \App\Models\FeatureRequest::STATUS_PLANNED => 'background:rgba(59,130,246,.12);color:#1d4ed8;',
                \App\Models\FeatureRequest::STATUS_IN_PROGRESS => 'background:rgba(14,165,233,.12);color:#0369a1;',
                \App\Models\FeatureRequest::STATUS_DONE => 'background:rgba(16,185,129,.12);color:#047857;',
                \App\Models\FeatureRequest::STATUS_REJECTED => 'background:rgba(239,68,68,.12);color:#b91c1c;',
                default => 'background:rgba(245,158,11,.12);color:#b45309;',
              };
              $canVote = in_array((string) $requestItem->status, \App\Models\FeatureRequest::VOTABLE_STATUSES, true) && $requestItem->is_active;
              $canReply = auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasRole(\App\Models\User::ROLE_MODERATOR));
              $canModerate = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin());
              $canDelete = auth()->check() && ($canModerate || (int) auth()->id() === (int) $requestItem->user_id);
            @endphp
            <article class="feature-item">
              <div class="feature-item-head">
                <div>
                  <h3 class="feature-item-title">{{ $requestItem->title }}</h3>
                  <p class="feature-item-meta">
                    Muallif: {{ $authorName }} · {{ $requestItem->created_at?->format('d.m.Y H:i') }}
                  </p>
                </div>
                <span class="badge" style="background: rgba(245, 158, 11, 0.12); color:#b45309;">
                  <i class="fa-solid fa-arrow-up"></i> {{ (int) $requestItem->votes_count }} ovoz
                </span>
              </div>
              <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">
                <span class="badge" style="{{ $statusStyle }}">{{ $statusLabel }}</span>
                @if($requestItem->announced_at)
                  <span class="badge" style="background:rgba(15,23,42,.08); color:#334155;">
                    E'lon: {{ $requestItem->announced_at->format('d.m.Y H:i') }}
                  </span>
                @endif
              </div>

              @if($requestItem->description)
                <p class="feature-item-body">{{ $requestItem->description }}</p>
              @endif
              @if($requestItem->admin_note)
                <p style="margin:10px 0 0; color:#334155; background:rgba(15,23,42,.04); border-radius:10px; padding:10px;">
                  <strong>Admin izohi:</strong> {{ $requestItem->admin_note }}
                </p>
              @endif

              @auth
                <div class="feature-controls">
                  @if($canVote)
                    <form method="POST" action="{{ route('feature-requests.vote', $requestItem) }}">
                      @csrf
                      <button type="submit" class="btn {{ $hasVoted ? 'btn-outline' : 'btn-prime' }}">
                        @if($hasVoted)
                          <i class="fa-solid fa-check"></i> Ovoz berildi (bekor qilish)
                        @else
                          <i class="fa-solid fa-thumbs-up"></i> Ovozimni berish
                        @endif
                      </button>
                    </form>
                  @endif

                  @if($canReply)
                    <form method="POST" action="{{ route('feature-requests.replies.store', $requestItem) }}">
                      @csrf
                      <div class="feature-inline-form">
                        <input type="text" name="message" class="exam-filter-input" maxlength="3000" placeholder="Moderator/Admin javobi..." style="flex:1; min-width:220px;">
                        <button type="submit" class="btn btn-outline">
                          <i class="fa-solid fa-reply"></i> Javob yozish
                        </button>
                      </div>
                    </form>
                  @endif

                  @if($canDelete)
                    <form method="POST" action="{{ route('feature-requests.destroy', $requestItem) }}"
                      data-confirm="Taklifni o'chirishni tasdiqlaysizmi?"
                      data-confirm-title="Taklifni o'chirish"
                      data-confirm-variant="danger"
                      data-confirm-ok="O'chirish">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline feature-delete-btn">
                        <i class="fa-solid fa-trash"></i> Taklifni o'chirish
                      </button>
                    </form>
                    <span class="feature-actions-tip">Taklif muallifi yoki admin o'chira oladi.</span>
                  @endif
                </div>
              @endauth

              @if(($requestItem->replies ?? collect())->isNotEmpty())
                <div class="feature-replies-wrap">
                  <div class="feature-replies-title">Javoblar</div>
                  <div class="feature-replies-list">
                  @foreach($requestItem->replies as $reply)
                    @php
                      $replyAuthor = trim((string) ($reply->user->first_name ?? '').' '.(string) ($reply->user->last_name ?? ''));
                      if ($replyAuthor === '') {
                        $replyAuthor = $reply->user->name ?? 'Xodim';
                      }
                      $isSuperAdminReply = (bool) ($reply->user?->isSuperAdmin());
                      $isAdminReply = $isSuperAdminReply || (bool) ($reply->user?->isAdmin());
                      $isModeratorReply = (bool) ($reply->user?->hasRole(\App\Models\User::ROLE_MODERATOR));
                      $replyRoleLabel = $isSuperAdminReply
                        ? 'Super Admin'
                        : ($isAdminReply
                            ? 'Admin'
                            : ($isModeratorReply ? 'Moderator' : 'Foydalanuvchi'));
                      $replyBadgeStyle = $isAdminReply
                        ? 'background:rgba(6,182,212,.16); color:#0891b2;'
                        : ($isModeratorReply
                            ? 'background:rgba(217,119,6,.16); color:#92400e;'
                            : 'background:rgba(15,23,42,.08); color:#334155;');
                      $replyDate = $reply->created_at?->format('d.m.Y H:i');
                    @endphp
                    <article class="feature-reply-card {{ $isSuperAdminReply ? 'is-super-admin' : ($isAdminReply ? 'is-admin' : ($isModeratorReply ? 'is-moderator' : '')) }}">
                      <div class="feature-reply-head">
                        <span class="badge feature-reply-role" style="{{ $replyBadgeStyle }}">
                          {{ $replyRoleLabel }}
                        </span>
                        <span class="feature-reply-meta">
                          {{ $replyAuthor }} · {{ $replyDate }}
                        </span>
                      </div>
                      <p class="feature-reply-message">{{ $reply->message }}</p>
                      @auth
                        @php
                          $canDeleteReply = (int) auth()->id() === (int) $reply->user_id || (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin());
                        @endphp
                        @if($canDeleteReply)
                          <div class="feature-reply-footer">
                            <form method="POST" action="{{ route('feature-requests.replies.destroy', $reply) }}"
                              data-confirm="Javobni o'chirishni tasdiqlaysizmi?"
                              data-confirm-title="Javobni o'chirish"
                              data-confirm-variant="danger"
                              data-confirm-ok="O'chirish">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-outline btn-sm feature-reply-delete-btn">
                                <i class="fa-solid fa-trash"></i> O'chirish
                              </button>
                            </form>
                          </div>
                        @endif
                      @endauth
                    </article>
                  @endforeach
                  </div>
                </div>
              @endif
            </article>
          @endforeach
        </div>

        <div style="margin-top: 18px;">
          {{ $featureRequests->links() }}
        </div>
      @endif
    </section>
  </main>
</x-loyouts.main>
