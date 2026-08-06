<x-layouts.main :title="$book->title.' — '.__('public.donation.library_badge')">

@push('page_styles')
<link rel="stylesheet" href="{{ app_public_asset('temp/css/book-viewer.css') }}?v={{ app_asset_version('temp/css/book-viewer.css') }}">
@endpush

{{-- ═══════════════════════════════════════════
     HERO — Cover + Info
═══════════════════════════════════════════ --}}
<section class="bv-hero">
  <div class="bv-hero-inner">

    {{-- Cover card --}}
    <div class="bv-cover-card">
      <div class="bv-cover-img">
        @if($book->cover_image)
          <img src="{{ $book->coverImageUrl() }}" alt="{{ $book->title }}">
        @else
          <div class="bv-cover-placeholder">
            <i class="fa-solid fa-book"></i>
          </div>
        @endif
      </div>

      <div class="bv-cover-meta">
        @if($book->author)
          <div class="bv-meta-row">
            <i class="fa-solid fa-user-pen"></i>
            <span><strong>{{ __('public.donation.library_author') }}:</strong> {{ $book->author }}</span>
          </div>
        @endif
        @if($book->subject)
          <div class="bv-meta-row">
            <i class="fa-solid fa-book-open"></i>
            <span><strong>{{ __('public.donation.library_subject') }}:</strong> {{ $book->subject }}</span>
          </div>
        @endif
        @if($book->grade)
          <div class="bv-meta-row">
            <i class="fa-solid fa-graduation-cap"></i>
            <span><strong>{{ __('public.donation.library_grade') }}:</strong> {{ $book->grade }}{{ __('public.donation.library_grade_suffix') }}</span>
          </div>
        @endif
        @if($book->year)
          <div class="bv-meta-row">
            <i class="fa-regular fa-calendar"></i>
            <span><strong>{{ __('public.donation.library_year') }}:</strong> {{ $book->year }}</span>
          </div>
        @endif
        <div class="bv-meta-row">
          <i class="fa-solid fa-file-pdf"></i>
          <span>{{ $book->fileSizeLabel() }}</span>
        </div>
        <div class="bv-meta-row">
          <i class="fa-solid fa-eye"></i>
          <span>{{ number_format($book->view_count) }} {{ __('public.donation.library_views') }}</span>
        </div>
      </div>
    </div>

    {{-- Info --}}
    <div class="bv-info">
      @if($book->category)
        <div class="bv-cat">
          <i class="fa-solid fa-folder"></i> {{ $book->category->name }}
        </div>
      @endif

      <h1 class="bv-title">{{ $book->title }}</h1>

      @if($book->author)
        <p class="bv-author">{{ $book->author }}</p>
      @endif

      @if($book->localized_description)
        <div class="bv-description">{{ $book->localized_description }}</div>
      @endif

      <div class="bv-actions">
        <a href="#bv-root" class="bv-btn bv-btn--primary" onclick="document.getElementById('bv-root').scrollIntoView({behavior:'smooth'});return false;">
          <i class="fa-solid fa-book-open-reader"></i>
          {{ __('public.donation.library_read_online') }}
        </a>
        @if($book->allow_download)
          <a href="{{ route('books.download', $book) }}" class="bv-btn bv-btn--download">
            <i class="fa-solid fa-download"></i>
            {{ __('public.donation.library_download') }}
          </a>
        @endif
        <a href="{{ route('books.index') }}" class="bv-btn bv-btn--ghost">
          <i class="fa-solid fa-arrow-left"></i>
          {{ __('public.donation.library_back') }}
        </a>
      </div>
    </div>

  </div>
</section>

{{-- ═══════════════════════════════════════════
     PDF VIEWER
═══════════════════════════════════════════ --}}
<div class="bv-section">

  {{-- Root element — JS reads data-pdf-url --}}
  <div id="bv-root" data-pdf-url="{{ route('books.stream', $book) }}">

    {{-- ── Toolbar ── --}}
    <div class="bv-toolbar">

      {{-- Left: icon + title --}}
      <div class="bv-toolbar-left">
        <div class="bv-toolbar-icon">
          <i class="fa-solid fa-file-pdf"></i>
        </div>
        <div class="bv-toolbar-title">
          {{ Illuminate\Support\Str::limit($book->title, 42) }}
          <span>PDF &bull; {{ $book->fileSizeLabel() }}</span>
        </div>
      </div>

      {{-- Center: controls --}}
      <div class="bv-toolbar-controls">
        <button id="bv-btn-first" class="bv-ctrl-btn" title="{{ __('public.donation.library_first_page') }}">
          <i class="fa-solid fa-backward-step"></i>
        </button>

        {{-- Navigatsiya strelkasi: PREV --}}
        <button id="bv-btn-prev" class="bv-ctrl-btn bv-ctrl-btn--nav" title="{{ __('public.donation.library_prev_page') }}">
          <i class="fa-solid fa-chevron-left"></i>
          <span>{{ __('public.donation.library_prev_page') }}</span>
        </button>

        <span id="bv-page-info" class="bv-page-info" title="Sahifa raqamini kiriting"
          style="cursor:pointer;"
          onclick="this.style.display='none';document.getElementById('bv-page-input-inline').style.display='inline-flex';document.getElementById('bv-page-input-inline').focus();"
        >— / —</span>
        <input
          id="bv-page-input-inline"
          type="number"
          class="bv-zoom-info"
          min="1"
          step="1"
          placeholder="#"
          style="display:none;width:64px;"
          title="Sahifa raqamini kiriting va Enter bosing"
        />

        {{-- Navigatsiya strelkasi: NEXT --}}
        <button id="bv-btn-next" class="bv-ctrl-btn bv-ctrl-btn--nav" title="{{ __('public.donation.library_next_page') }}">
          <span>{{ __('public.donation.library_next_page') }}</span>
          <i class="fa-solid fa-chevron-right"></i>
        </button>
        <button id="bv-btn-last" class="bv-ctrl-btn" title="{{ __('public.donation.library_last_page') }}">
          <i class="fa-solid fa-forward-step"></i>
        </button>

        <div style="width:1px;height:22px;background:rgba(255,255,255,.12);margin:0 .2rem;"></div>

        <button id="bv-btn-zoom-out" class="bv-ctrl-btn" title="{{ __('public.donation.library_zoom_out') }}">
          <i class="fa-solid fa-magnifying-glass-minus"></i>
        </button>
        <input
          id="bv-zoom-info"
          type="number"
          class="bv-zoom-info"
          value="100"
          min="30"
          max="400"
          step="10"
          title="Zoom foizini kiriting"
          style="cursor:text;"
        />
        <button id="bv-btn-zoom-in" class="bv-ctrl-btn" title="{{ __('public.donation.library_zoom_in') }}">
          <i class="fa-solid fa-magnifying-glass-plus"></i>
        </button>

        <div style="width:1px;height:22px;background:rgba(255,255,255,.12);margin:0 .2rem;"></div>

        <button id="bv-btn-fullscreen" class="bv-ctrl-btn bv-ctrl-btn--accent" title="{{ __('public.donation.library_fullscreen') }}">
          <i class="fa-solid fa-expand"></i>
          <span>{{ __('public.donation.library_fullscreen') }}</span>
        </button>

        {{-- Bookmark tugmasi --}}
        <button id="bv-btn-bookmark" class="bv-ctrl-btn bv-ctrl-btn--bookmark" title="{{ __('public.donation.library_bookmark_page') }}">
          <i class="fa-regular fa-bookmark" id="bv-bm-icon"></i>
          <span>{{ __('public.donation.library_bookmark_page') }}</span>
        </button>

        @if($book->allow_download)
          <a href="{{ route('books.download', $book) }}" class="bv-ctrl-btn bv-ctrl-btn--primary" title="{{ __('public.donation.library_download') }}">
            <i class="fa-solid fa-download"></i>
            <span>{{ __('public.donation.library_download') }}</span>
          </a>
        @endif
      </div>

    </div>

    {{-- ── Stage (book area) ── --}}
    <div class="bv-stage" id="bv-stage">

      {{-- Loading overlay --}}
      <div id="bv-loading" class="bv-loading">
        <div class="bv-loading-ring"></div>
        <div class="bv-loading-text">{{ __('public.donation.library_loading') }}</div>
      </div>

      {{-- Error --}}
      <div id="bv-error" style="display:none;position:absolute;inset:0;align-items:center;justify-content:center;flex-direction:column;gap:1rem;color:#f87171;font-size:.9rem;font-weight:600;z-index:40;background:rgba(10,14,26,.9);border-radius:0 0 1.25rem 1.25rem;text-align:center;padding:2rem;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:2.5rem;"></i>
        <span id="bv-error-text"></span>
      </div>

      {{-- ── DESKTOP: Double-page open book ── --}}
      <div class="bv-book-wrap" id="bv-book-wrap">

        {{-- Left page --}}
        <div class="bv-page-left">
          <div class="bv-page-canvas">
            <canvas id="bv-canvas-left"></canvas>
          </div>
          <div class="bv-dog-ear" id="bv-dog-ear-left" title="{{ __('public.donation.library_prev_page') }}"></div>
          <span class="bv-page-num" id="bv-pnum-left"></span>
        </div>

        {{-- Spine --}}
        <div class="bv-spine"></div>

        {{-- Right page --}}
        <div class="bv-page-right">
          <div class="bv-page-canvas">
            <canvas id="bv-canvas-right"></canvas>
          </div>
          <div class="bv-dog-ear" id="bv-dog-ear-right" title="{{ __('public.donation.library_next_page') }}"></div>
          <span class="bv-page-num" id="bv-pnum-right"></span>
        </div>

        {{-- Flip leaf (3D animatsiya uchun) --}}
        <div class="bv-flip-container" aria-hidden="true">
          <div class="bv-flip-leaf bv-flip-leaf--right" id="bv-flip-leaf">
            <div class="bv-flip-leaf-front" id="bv-flip-front">
              <canvas id="bv-canvas-flip-front"></canvas>
              <div class="bv-flip-shadow-front"></div>
            </div>
            <div class="bv-flip-leaf-back" id="bv-flip-back">
              <canvas id="bv-canvas-flip-back"></canvas>
              <div class="bv-flip-shadow-back"></div>
            </div>
          </div>
        </div>

        {{-- Book shadow --}}
        <div class="bv-book-shadow"></div>
      </div>

      {{-- ── MOBILE: Single page ── --}}
      <div class="bv-single-page" id="bv-single-page">
        <div class="bv-page-canvas">
          <canvas id="bv-canvas-single"></canvas>
        </div>
        <div class="bv-dog-ear bv-page-right" id="bv-dog-ear-right-m" style="position:absolute;bottom:0;right:0;" title="{{ __('public.donation.library_next_page') }}"
          onclick="window.bvGoNext && window.bvGoNext()"></div>
        <span class="bv-page-num" id="bv-pnum-single" style="right:18px;bottom:10px;position:absolute;"></span>
      </div>

      </div>

    </div>{{-- /bv-stage --}}

    {{-- ── Dedicated Fixed Footer Bar (Tugmalar va Progress) ── --}}
    <div class="bv-footer-bar">
      <div class="bv-bottom-nav">
        <button class="bv-nav-btn bv-nav-prev" onclick="window.bvGoPrev && window.bvGoPrev()">
          <i class="fa-solid fa-chevron-left"></i>
          {{ __('public.donation.library_prev_page') }}
        </button>
        <button class="bv-nav-btn bv-nav-btn--primary bv-nav-next" onclick="window.bvGoNext && window.bvGoNext()">
          {{ __('public.donation.library_next_page') }}
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>

      <div class="bv-progress-wrap">
        <div class="bv-progress-track">
          <div class="bv-progress-fill" id="bv-progress-fill" style="width:0%"></div>
        </div>
        <div class="bv-progress-label">
          <span id="bv-progress-label">0% o'qildi</span>
          <span style="opacity:.5;">{{ __('public.donation.library_use_arrows') }}</span>
        </div>
      </div>
    </div>

    {{-- ── Tabs: Sahifalar / Izohlar / Belgilar ── --}}
    <div class="bv-tabs-bar">
      <button class="bv-tab active" data-tab="pages">
        <i class="fa-solid fa-grip"></i>
        <span>{{ __('public.donation.library_pages') }}</span>
      </button>
      <button class="bv-tab" data-tab="notes">
        <i class="fa-solid fa-note-sticky"></i>
        <span>{{ __('public.donation.library_notes') }}</span>
      </button>
      <button class="bv-tab" data-tab="bookmarks">
        <i class="fa-solid fa-bookmark"></i>
        <span>{{ __('public.donation.library_bookmarks') }}</span>
      </button>
    </div>

    {{-- Tab: Sahifalar (thumbnail strip) --}}
    <div class="bv-tab-panel" id="bv-tab-pages">
      <div class="bv-thumbs" id="bv-thumbs"></div>
    </div>

    {{-- Tab: Izohlar --}}
    <div class="bv-tab-panel bv-tab-panel--hidden" id="bv-tab-notes">
      <div class="bv-tab-empty">
        <i class="fa-regular fa-note-sticky"></i>
        <p>{{ __('public.donation.library_notes_empty') }}</p>
      </div>
    </div>

    {{-- Tab: Belgilar --}}
    <div class="bv-tab-panel bv-tab-panel--hidden" id="bv-tab-bookmarks">
      <div class="bv-tab-empty" id="bv-bm-empty">
        <i class="fa-regular fa-bookmark"></i>
        <p>{{ __('public.donation.library_bookmarks_empty') }}</p>
      </div>
      <div class="bv-bm-list" id="bv-bm-list"></div>
    </div>

  </div>
</div>

@push('page_scripts')
<script src="{{ app_public_asset('temp/js/book-viewer.js') }}?v={{ app_asset_version('temp/js/book-viewer.js') }}"></script>
@endpush

</x-layouts.main>
