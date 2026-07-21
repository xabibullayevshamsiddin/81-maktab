<x-layouts.main :title="__('public.contact.page_title')">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">{{ __('public.contact.badge') }}</span>
        <h1 class="js-split-text">{!! __('public.contact.hero_title') !!}</h1>
        <p>{{ __('public.contact.hero_text') }}</p>
        <a href="#contact-main" class="btn btn-prime" style="margin-top:30px;">
          {{ __('public.contact.hero_button') }}
          <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i>
        </a>
      </div>
    </div>
  </section>

  <main id="contact-main" class="contact-main">
    <div class="container">
      <div class="contact-layout">
        <div class="contact-cards prime-stagger">
          <article class="contact-card">
            <div class="contact-card-icon">
              <i class="fa-solid fa-location-dot"></i>
            </div>
            <div class="contact-card-text">
              <h3>{{ __('public.contact.address_title') }}</h3>
              <p style="margin:0 0 6px;">{{ __('public.contact.address_line1') }}</p>
              <p style="margin:0 0 10px;color:#64748b;">{{ __('public.contact.address_line2') }}</p>
              <a
                href="https://maps.app.goo.gl/erCMfrDY42DCogHL6"
                class="btn btn-prime"
                target="_blank"
                rel="noopener"
              >
                {{ __('public.contact.map_button') }}
              </a>
            </div>
          </article>

          <article class="contact-card">
            <div class="contact-card-icon">
              <i class="fa-solid fa-phone"></i>
            </div>
            <div class="contact-card-text">
              <h3>{{ __('public.contact.phone_title') }}</h3>
              <p><a href="tel:+998711234567">+998 71 123 45 67</a></p>
            </div>
          </article>

          <article class="contact-card">
            <div class="contact-card-icon">
              <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="contact-card-text">
              <h3>{{ __('public.contact.email_title') }}</h3>
              <p>
                <a
                  href="{{ gmail_compose_url('info@school81.uz', __('public.contact.email_subject')) }}"
                  target="_blank"
                  rel="noopener"
                >
                  info@school81.uz
                </a>
              </p>
            </div>
          </article>
        </div>

        <div class="contact-form-wrap prime-reveal">
          @auth
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;">
              <h2 style="margin:0;">{{ __('public.contact.form_title') }}</h2>
              <x-site-rule-items area="contact" />
            </div>

            <div class="contact-auth-info">
              <i class="fa-solid fa-user-check" style="margin-right:6px; color:var(--primary);"></i>
              {!! __('public.contact.form_info', ['name' => '<strong>'.e(auth()->user()->name).'</strong>', 'phone' => e(auth()->user()->phone)]) !!}
            </div>

            @php
              $maxMb = 2;
              if (auth()->check() && auth()->user()->isDonor()) {
                  $rank = auth()->user()->donation_rank;
                  if ($rank === \App\Models\Donation::RANK_SUPPORTER) {
                      $maxMb = 5;
                  } elseif ($rank === \App\Models\Donation::RANK_PREMIUM) {
                      $maxMb = 10;
                  } elseif ($rank === \App\Models\Donation::RANK_VIP) {
                      $maxMb = 20;
                  }
              }
            @endphp
            <form class="contact-form" id="contact-form" method="post" action="{{ route('contact.store') }}" enctype="multipart/form-data">
              @csrf
              <textarea
                id="shikoyat"
                name="note"
                placeholder="{{ __('public.contact.note_placeholder') }}"
                rows="2"
                required
              >{{ old('note', request()->query('note')) }}</textarea>
              <textarea
                id="message"
                name="message"
                rows="5"
                placeholder="{{ __('public.contact.message_placeholder') }}"
                required
              >{{ old('message', request()->query('message')) }}</textarea>
              
              <div class="contact-file-upload" style="margin-bottom: 20px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:8px; color:var(--text);">
                  Rasm biriktirish (ixtiyoriy)
                </label>
                <div class="custom-file-upload-wrapper" style="position:relative; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:1.25rem 1rem; border:2px dashed var(--border); border-radius:12px; background:rgba(0,0,0,0.15); cursor:pointer; text-align:center; transition: border-color 0.2s, background-color 0.2s;">
                  <i class="fa-solid fa-cloud-arrow-up" style="font-size:1.8rem; color:var(--primary); margin-bottom:8px;"></i>
                  <span class="file-upload-text" style="font-size:0.82rem; font-weight:600; color:var(--text);">Rasm tanlash uchun bosing</span>
                  <span class="file-upload-hint" style="font-size:0.7rem; color:var(--muted); margin-top:4px;">
                    @if(auth()->check() && auth()->user()->isDonor())
                      Siz <strong>{{ auth()->user()->donorRankLabel() }}</strong> bo'lganingiz uchun maksimal: <strong>{{ $maxMb }} MB</strong> rasm yuklay olasiz.
                    @else
                      Maksimal ruxsat etilgan hajm: <strong>2 MB</strong>. Ko'proq yuklash uchun Donat qiling!
                    @endif
                  </span>
                  <input type="file" id="contact-image" name="image" accept="image/*" style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;" onchange="updateFileName(this)">
                </div>
                <div id="file-name-display" style="display:none; align-items:center; gap:8px; margin-top:8px; font-size:0.75rem; color:var(--primary); font-weight:600;">
                  <i class="fa-solid fa-paperclip"></i> <span id="file-name-text"></span>
                </div>
              </div>

              <script>
                function updateFileName(input) {
                  var display = document.getElementById('file-name-display');
                  var text = document.getElementById('file-name-text');
                  var wrapper = input.closest('.custom-file-upload-wrapper');
                  if (input.files && input.files.length > 0) {
                    text.textContent = input.files[0].name;
                    display.style.display = 'flex';
                    if (wrapper) {
                      wrapper.style.borderColor = 'var(--primary)';
                      wrapper.style.backgroundColor = 'rgba(99, 102, 241, 0.08)';
                    }
                  } else {
                    display.style.display = 'none';
                    if (wrapper) {
                      wrapper.style.borderColor = 'var(--border)';
                      wrapper.style.backgroundColor = 'rgba(0,0,0,0.15)';
                    }
                  }
                }
              </script>

              <x-turnstile-field />
              <button class="btn btn-prime" type="submit">{{ __('public.contact.submit') }}</button>
              <p id="form-message" class="form-message" aria-live="polite"></p>
            </form>
          @else
            <div class="contact-auth-prompt">
              <div class="icon-wrap contact-auth-prompt-icon">
                <i class="fa-solid fa-lock"></i>
              </div>
              <h2 class="contact-auth-prompt-title">{{ __('public.contact.guest_title') }}</h2>
              <p class="contact-auth-prompt-text">{{ __('public.contact.guest_text') }}</p>
              <a href="{{ route('login') }}" class="btn btn-prime">
                <i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>
                {{ __('public.contact.guest_login') }}
              </a>
            </div>
          @endauth
        </div>
      </div>
    </div>
  </main>
</x-loyouts.main>
