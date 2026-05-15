@php
  $isSaved = $isSaved ?? false;
  $toggleUrl = $toggleUrl ?? null;
  $ariaLabel = $ariaLabel ?? __('public.bookmark.aria_default');
  $loginHint = $loginHint ?? __('public.bookmark.login_hint');
@endphp
@auth
  @if($toggleUrl)
    <form action="{{ $toggleUrl }}" method="POST" class="js-bookmark-form">
      @csrf
      <button
        type="submit"
        class="bookmark-btn {{ $isSaved ? 'is-saved' : '' }}"
        aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
        aria-label="{{ $ariaLabel }}"
      >
        <i class="{{ $isSaved ? 'fa-solid' : 'fa-regular' }} fa-bookmark"></i>
        <span class="bookmark-btn__text">{{ __('public.bookmark.label') }}</span>
      </button>
    </form>
  @endif
@else
  <a
    href="{{ route('login') }}"
    class="bookmark-btn bookmark-btn--guest"
    title="{{ $loginHint }}"
  >
    <i class="fa-regular fa-bookmark"></i>
    <span class="bookmark-btn__text">{{ __('public.bookmark.label') }}</span>
  </a>
@endauth
