<x-loyouts.main title="Bildirishnomalar">
  <section class="news-hero notifications-hero">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">Bildirishnomalar</span>
        <h1 class="js-split-text">So'nggi yangilanishlar markazi</h1>
        <p>Kurs, imtihon, admin javobi va sizga tegishli tizim xabarlari shu yerda jamlanadi.</p>
      </div>
    </div>
  </section>

  <main class="notifications-page">
    <section class="container notifications-shell">
      <div class="notifications-summary prime-reveal">
        <article class="notifications-stat">
          <span class="notifications-stat-icon"><i class="fa-regular fa-bell"></i></span>
          <strong>{{ number_format($summary['total']) }}</strong>
          <span>Jami xabarlar</span>
        </article>
        <article class="notifications-stat notifications-stat--accent">
          <span class="notifications-stat-icon"><i class="fa-solid fa-bell"></i></span>
          <strong>{{ number_format($summary['unread']) }}</strong>
          <span>O'qilmagan</span>
        </article>
      </div>

      <div class="notifications-toolbar prime-reveal">
        <div class="notifications-filter-group">
          <a href="{{ route('notifications.index') }}" class="notifications-filter {{ $filter === 'all' ? 'is-active' : '' }}">Hammasi</a>
          <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="notifications-filter {{ $filter === 'unread' ? 'is-active' : '' }}">O'qilmagan</a>
        </div>

        @if($summary['unread'] > 0)
          <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">
              <i class="fa-solid fa-check-double"></i>
              Barchasini o'qildi deb belgilash
            </button>
          </form>
        @endif
      </div>

      <div class="notifications-list prime-stagger">
        @forelse($notifications as $notification)
          <article class="notification-card {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
            <div class="notification-card-icon notification-card-icon--{{ $notification->type }}">
              @switch($notification->type)
                @case(\App\Models\UserNotification::TYPE_ERROR)
                  <i class="fa-solid fa-circle-exclamation"></i>
                  @break
                @case(\App\Models\UserNotification::TYPE_WARNING)
                  <i class="fa-solid fa-triangle-exclamation"></i>
                  @break
                @case(\App\Models\UserNotification::TYPE_SUCCESS)
                  <i class="fa-solid fa-circle-check"></i>
                  @break
                @default
                  <i class="fa-solid fa-bell"></i>
              @endswitch
            </div>

            <div class="notification-card-main">
              <div class="notification-card-head">
                <h2>{{ $notification->title }}</h2>
                <span class="notification-card-date">{{ $notification->created_at?->diffForHumans() }}</span>
              </div>
              <p>{{ $notification->body }}</p>

              <div class="notification-card-meta">
                <span class="notification-card-state {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                  <i class="fa-solid {{ $notification->read_at ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                  {{ $notification->read_at ? "O'qilgan" : "Yangi" }}
                </span>

                @if(filled($notification->link))
                  <a href="{{ $notification->link }}" class="btn btn-sm">
                    O'tish
                    <i class="fa-solid fa-arrow-right"></i>
                  </a>
                @endif
              </div>
            </div>
          </article>
        @empty
          <div class="notification-empty">
            <i class="fa-regular fa-bell-slash"></i>
            <p>Hozircha siz uchun bildirishnoma yo'q.</p>
          </div>
        @endforelse
      </div>

      @if($notifications->hasPages())
        <div class="news-pagination notifications-pagination">
          @if ($notifications->onFirstPage())
            <span class="btn btn-sm btn-outline" aria-disabled="true">Oldingi</span>
          @else
            <a class="btn btn-sm btn-outline" href="{{ $notifications->previousPageUrl() }}">Oldingi</a>
          @endif

          <span class="news-page-info">
            {{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}
          </span>

          @if ($notifications->hasMorePages())
            <a class="btn btn-sm" href="{{ $notifications->nextPageUrl() }}">Keyingi</a>
          @else
            <span class="btn btn-sm" aria-disabled="true">Keyingi</span>
          @endif
        </div>
      @endif
    </section>
  </main>
</x-loyouts.main>
