<x-layouts.main :title="__('public.donation.library_badge').'  — 81-IDUM'">

@push('page_styles')
<style>
.lib-hero{position:relative;padding:140px 1.5rem 5rem;text-align:center;overflow:hidden;background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#0f2744 100%);color:#fff;border-radius:0 0 3rem 3rem;margin-bottom:3rem}
.lib-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 15% 30%,rgba(99,102,241,.3),transparent 50%),radial-gradient(ellipse at 85% 20%,rgba(14,165,233,.25),transparent 50%),radial-gradient(ellipse at 50% 90%,rgba(139,92,246,.2),transparent 50%);animation:libBgPulse 8s ease-in-out infinite alternate}
@keyframes libBgPulse{from{opacity:.6}to{opacity:1}}
.lib-hero-inner{position:relative;z-index:2}
.lib-hero-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .9rem;border-radius:999px;background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2);font-size:.75rem;font-weight:700;margin-bottom:1rem;animation:fadeSlideDown .6s ease both}
.lib-hero h1{font-size:clamp(2rem,5vw,3.5rem);font-weight:900;margin-bottom:.75rem;background:linear-gradient(90deg,#a5b4fc,#38bdf8,#c4b5fd,#a5b4fc);background-size:200% auto;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;animation:libGradShift 5s linear infinite,fadeSlideDown .7s ease both}
@keyframes libGradShift{to{background-position:200% center}}
.lib-hero p{font-size:1.05rem;opacity:.85;max-width:560px;margin:0 auto 1.5rem;line-height:1.6;animation:fadeSlideDown .8s ease both}
.lib-stats{display:flex;justify-content:center;gap:2rem;flex-wrap:wrap;animation:fadeSlideDown .9s ease both}
.lib-stat{display:flex;flex-direction:column;align-items:center;background:rgba(255,255,255,.08);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:1rem;padding:.75rem 1.5rem}
.lib-stat strong{font-size:1.5rem;font-weight:800}
.lib-stat span{font-size:.75rem;opacity:.75}
.lib-filter-bar{background:var(--surface);border:1px solid var(--border);border-radius:1.25rem;padding:1.25rem 1.5rem;margin-bottom:2rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end}
.lib-filter-bar .lib-search{flex:1;min-width:200px;display:flex;align-items:center;gap:.5rem;background:var(--bg);border:1.5px solid var(--border);border-radius:.75rem;padding:.5rem .85rem;transition:border-color .2s}
.lib-filter-bar .lib-search:focus-within{border-color:var(--primary)}
.lib-filter-bar .lib-search i{color:var(--muted);font-size:.85rem}
.lib-filter-bar .lib-search input{border:none;background:transparent;outline:none;color:var(--text);font-size:.9rem;width:100%}
.lib-select{padding:.5rem .75rem;border:1.5px solid var(--border);border-radius:.75rem;background:var(--bg);color:var(--text);font-size:.85rem;outline:none;cursor:pointer;transition:border-color .2s}
.lib-select:focus{border-color:var(--primary)}
.lib-filter-btn{padding:.5rem 1.1rem;border-radius:.75rem;background:var(--primary);color:#fff;border:none;font-weight:600;font-size:.85rem;cursor:pointer;transition:opacity .2s}
.lib-filter-btn:hover{opacity:.88}
.lib-filter-clear{padding:.5rem .9rem;border-radius:.75rem;background:transparent;color:var(--muted);border:1.5px solid var(--border);font-size:.85rem;cursor:pointer;text-decoration:none;transition:border-color .2s,color .2s}
.lib-filter-clear:hover{border-color:var(--primary);color:var(--primary)}
.lib-sort-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem}
.lib-sort-tab{padding:.4rem .9rem;border-radius:999px;border:1.5px solid var(--border);background:var(--surface);color:var(--muted);font-size:.8rem;font-weight:600;text-decoration:none;transition:all .2s}
.lib-sort-tab:hover,.lib-sort-tab.active{border-color:var(--primary);color:var(--primary);background:color-mix(in srgb,var(--primary) 8%,transparent)}
.lib-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.5rem}
.book-card{position:relative;border-radius:1.25rem;overflow:hidden;background:var(--surface);border:1px solid var(--border);transition:transform .3s cubic-bezier(.34,1.56,.64,1),box-shadow .3s;cursor:pointer;animation:bookCardIn .5s ease both}
.book-card:hover{transform:translateY(-8px) scale(1.02);box-shadow:0 20px 50px rgba(0,0,0,.15)}
@keyframes bookCardIn{from{opacity:0;transform:translateY(24px) scale(.96)}to{opacity:1;transform:none}}
.book-card-cover{position:relative;aspect-ratio:3/4;overflow:hidden;background:linear-gradient(135deg,#1e1b4b,#312e81)}
.book-card-cover img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease}
.book-card:hover .book-card-cover img{transform:scale(1.06)}
.book-card-cover-placeholder{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,.6);gap:.5rem}
.book-card-cover-placeholder i{font-size:2.5rem}
.book-card-cover-placeholder span{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
.book-card-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.85) 0%,rgba(0,0,0,.2) 50%,transparent 100%);display:flex;flex-direction:column;justify-content:flex-end;padding:1rem;opacity:0;transition:opacity .3s}
.book-card:hover .book-card-overlay{opacity:1}
.book-card-overlay-btns{display:flex;gap:.5rem;flex-wrap:wrap}
.book-overlay-btn{display:inline-flex;align-items:center;gap:.3rem;padding:.45rem .85rem;border-radius:999px;font-size:.72rem;font-weight:700;text-decoration:none;border:none;cursor:pointer;transition:transform .15s}
.book-overlay-btn:hover{transform:scale(1.05)}
.book-overlay-btn--read{background:var(--primary);color:#fff}
.book-overlay-btn--download{background:rgba(255,255,255,.15);color:#fff;backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.3)}
.book-card-badge{position:absolute;top:.6rem;left:.6rem;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;padding:.18rem .55rem;border-radius:999px;background:var(--primary);color:#fff;z-index:2}
.book-card-body{padding:.85rem}
.book-card-cat{font-size:.65rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem}
.book-card-title{font-size:.88rem;font-weight:700;color:var(--text);line-height:1.35;margin-bottom:.3rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.book-card-author{font-size:.72rem;color:var(--muted);margin-bottom:.5rem}
.book-card-meta{display:flex;align-items:center;gap:.5rem;font-size:.65rem;color:var(--muted);flex-wrap:wrap}
.book-card-meta span{display:flex;align-items:center;gap:.2rem}
.lib-empty{text-align:center;padding:4rem 1rem;color:var(--muted)}
.lib-empty i{font-size:3rem;opacity:.3;display:block;margin-bottom:1rem}
:root[data-theme='dark'] .book-card:hover{box-shadow:0 20px 50px rgba(0,0,0,.4)}
@keyframes fadeSlideDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:none}}
.book-card:nth-child(1){animation-delay:.05s}.book-card:nth-child(2){animation-delay:.10s}.book-card:nth-child(3){animation-delay:.15s}.book-card:nth-child(4){animation-delay:.20s}.book-card:nth-child(5){animation-delay:.25s}.book-card:nth-child(6){animation-delay:.30s}.book-card:nth-child(7){animation-delay:.35s}.book-card:nth-child(8){animation-delay:.40s}.book-card:nth-child(9){animation-delay:.45s}.book-card:nth-child(10){animation-delay:.50s}.book-card:nth-child(11){animation-delay:.55s}.book-card:nth-child(12){animation-delay:.60s}
@media(max-width:640px){.lib-grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:1rem}.lib-hero{padding:120px 1rem 3rem}.lib-stats{gap:1rem}}
</style>
@endpush

<section class="lib-hero">
  <div class="lib-hero-inner">
    <span class="lib-hero-badge"><i class="fa-solid fa-book-open"></i> {{ __('public.donation.library_badge') }}</span>
    <h1>{{ __('public.donation.library_hero_title') }}</h1>
    <p>{{ __('public.donation.library_hero_text') }}</p>
    <div class="lib-stats">
      <div class="lib-stat">
        <strong>{{ \App\Models\Book::active()->count() }}</strong>
        <span>{{ __('public.donation.library_stat_books') }}</span>
      </div>
      <div class="lib-stat">
        <strong>{{ \App\Models\BookCategory::count() }}</strong>
        <span>{{ __('public.donation.library_stat_cats') }}</span>
      </div>
      <div class="lib-stat">
        <strong>{{ number_format(\App\Models\Book::active()->sum('download_count')) }}</strong>
        <span>{{ __('public.donation.library_stat_dl') }}</span>
      </div>
    </div>
  </div>
</section>

<div class="container" style="max-width:1200px; padding:0 1rem 4rem;">

  <form method="GET" action="{{ route('books.index') }}" class="lib-filter-bar">
    <div class="lib-search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="q" value="{{ $q }}" placeholder="{{ __('public.donation.library_search_ph') }}">
    </div>
    <select name="category" class="lib-select">
      <option value="">{{ __('public.donation.library_all_cats') }}</option>
      @foreach($categories as $cat)
        <option value="{{ $cat->id }}" {{ $catId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
      @endforeach
    </select>
    @if($grades->isNotEmpty())
      <select name="grade" class="lib-select">
        <option value="">{{ __('public.donation.library_all_grades') }}</option>
        @foreach($grades as $g)
          <option value="{{ $g }}" {{ $grade == $g ? 'selected' : '' }}>{{ $g }}{{ __('public.donation.library_grade_suffix') }}</option>
        @endforeach
      </select>
    @endif
    @if($subjects->isNotEmpty())
      <select name="subject" class="lib-select">
        <option value="">{{ __('public.donation.library_all_subjects') }}</option>
        @foreach($subjects as $s)
          <option value="{{ $s }}" {{ $subject == $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
      </select>
    @endif
    @if($years->isNotEmpty())
      <select name="year" class="lib-select">
        <option value="">{{ __('public.donation.library_all_years') }}</option>
        @foreach($years as $y)
          <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
      </select>
    @endif
    <input type="hidden" name="sort" value="{{ $sort }}">
    <button type="submit" class="lib-filter-btn"><i class="fa-solid fa-filter"></i> {{ __('public.donation.library_search_btn') }}</button>
    @if($q || $catId || $grade || $subject || $year)
      <a href="{{ route('books.index') }}" class="lib-filter-clear">{{ __('public.donation.library_clear') }}</a>
    @endif
  </form>

  <div class="lib-sort-tabs">
    @foreach([
      'newest'    => __('public.donation.library_sort_newest'),
      'oldest'    => __('public.donation.library_sort_oldest'),
      'popular'   => __('public.donation.library_sort_popular'),
      'downloads' => __('public.donation.library_sort_dl'),
    ] as $key => $label)
      <a href="{{ request()->fullUrlWithQuery(['sort' => $key]) }}"
         class="lib-sort-tab {{ $sort === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
    <span style="margin-left:auto;font-size:.8rem;color:var(--muted);align-self:center;">
      {{ __('public.donation.library_count', ['count' => $books->total()]) }}
    </span>
  </div>

  @if($books->isEmpty())
    <div class="lib-empty">
      <i class="fa-solid fa-book-open"></i>
      <p>{{ __('public.donation.library_empty') }}</p>
    </div>
  @else
    <div class="lib-grid">
      @foreach($books as $book)
        <div class="book-card">
          @if($book->grade)
            <span class="book-card-badge">{{ $book->grade }}{{ __('public.donation.library_grade_suffix') }}</span>
          @endif
          <div class="book-card-cover">
            @if($book->cover_image)
              <img src="{{ $book->coverImageUrl() }}" alt="{{ $book->title }}" loading="lazy">
            @else
              <div class="book-card-cover-placeholder">
                <i class="fa-solid fa-book"></i>
                <span>PDF</span>
              </div>
            @endif
            <div class="book-card-overlay">
              <div class="book-card-overlay-btns">
                <a href="{{ route('books.show', $book) }}" class="book-overlay-btn book-overlay-btn--read">
                  <i class="fa-solid fa-eye"></i> {{ __('public.donation.library_read') }}
                </a>
                @if($book->allow_download)
                  <a href="{{ route('books.download', $book) }}" class="book-overlay-btn book-overlay-btn--download">
                    <i class="fa-solid fa-download"></i>
                  </a>
                @endif
              </div>
            </div>
          </div>
          <div class="book-card-body">
            @if($book->category)
              <div class="book-card-cat">{{ $book->category->name }}</div>
            @endif
            <div class="book-card-title">{{ $book->title }}</div>
            @if($book->author)
              <div class="book-card-author"><i class="fa-solid fa-user-pen" style="font-size:.6rem;"></i> {{ $book->author }}</div>
            @endif
            <div class="book-card-meta">
              @if($book->subject)<span><i class="fa-solid fa-book-open"></i> {{ $book->subject }}</span>@endif
              @if($book->year)<span><i class="fa-regular fa-calendar"></i> {{ $book->year }}</span>@endif
              <span><i class="fa-solid fa-eye"></i> {{ $book->view_count }}</span>
              <span>{{ $book->fileSizeLabel() }}</span>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    @if($books->hasPages())
      <div style="margin-top:2rem;display:flex;justify-content:center;">{{ $books->links() }}</div>
    @endif
  @endif
</div>

</x-loyouts.main>
