{{-- Cloudflare Turnstile — .env: TURNSTILE_ENABLED=true + kalitlar --}}
@if(turnstile_enabled())
  <div class="turnstile-field-wrap" data-prime-turnstile>
    <div
      class="cf-turnstile"
      data-sitekey="{{ turnstile_site_key() }}"
      data-theme="auto"
    ></div>
  </div>
@endif
