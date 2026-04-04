{{--
  Qidiruv: GET ?q=
  $hidden — saqlanadigan boshqa query parametrlar (masalan type, status, exam_id)
--}}
@php
  $hidden = $hidden ?? [];
  $placeholder = $placeholder ?? 'Qidirish...';
  $clearQuery = request()->except('q');
@endphp
<form method="get" action="{{ $action ?? url()->current() }}" class="admin-search-bar mb-20" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
  @foreach($hidden as $name => $value)
    @if($value !== null && $value !== '')
      <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endif
  @endforeach
  <input
    type="search"
    name="q"
    value="{{ request('q') }}"
    placeholder="{{ $placeholder }}"
    autocomplete="off"
    class="form-control"
    style="max-width:360px;min-width:200px;flex:1;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;font-size:14px;"
  >
  <button type="submit" class="main-btn primary-btn btn-hover btn-sm">Qidirish</button>
  @if(request()->filled('q'))
    <a href="{{ request()->url() }}{{ count($clearQuery) ? '?' . http_build_query($clearQuery) : '' }}" class="main-btn dark-btn btn-hover btn-sm">Tozalash</a>
  @endif
</form>
