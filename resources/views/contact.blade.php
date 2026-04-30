<x-loyouts.main title="81-IDUM | Aloqa">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
          <span class="badge">81-IDUM Aloqa</span>
          <h1 class="js-split-text">Biz bilan <strong>bog'laning</strong></h1>
          <p>
            Savolingiz, taklifingiz yoki texnik muammo bo'lsa, quyidagi forma orqali rasmiy murojaat qoldiring.
            Bu sahifa to'g'ridan-to'g'ri admin chat emas: yuborilgan murojaat ichki tartibda tegishli mas'ul xodimga yetkaziladi.
            Zarur bo'lsa siz bilan aloqa ma'lumotlaringiz orqali bog'laniladi.
          </p>
          <a href="#contact-main" class="btn btn-prime" style="margin-top:30px;"
            >Xabar yuborish
            <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i
          ></a>
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
                <h3>Manzil</h3>
                <p style="margin:0 0 6px;">81-IDUM maktab lokatsiyasi</p>
                <p style="margin:0 0 10px;color:#64748b;">Maktab joylashuvini Google Maps orqali ochib ko'ring.</p>
                <a
                  href="https://maps.app.goo.gl/erCMfrDY42DCogHL6"
                  class="btn btn-prime"
                  target="_blank"
                  rel="noopener"
                >
                  Xaritada ochish
                </a>
              </div>
            </article>

            <article class="contact-card">
              <div class="contact-card-icon">
                <i class="fa-solid fa-phone"></i>
              </div>
              <div class="contact-card-text">
                <h3>Telefon</h3>
                <p><a href="tel:+998711234567">+998 71 123 45 67</a></p>
              </div>
            </article>

            <article class="contact-card">
              <div class="contact-card-icon">
                <i class="fa-solid fa-envelope"></i>
              </div>
              <div class="contact-card-text">
                <h3>Email</h3>
                <p>
                  <a
                    href="{{ gmail_compose_url('info@school81.uz', '81-IDUM murojaati') }}"
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
                <h2 style="margin:0;">Rasmiy Murojaat</h2>
                <x-site-rule-items area="contact" />
              </div>

              <div class="contact-auth-info">
                  <i class="fa-solid fa-user-check" style="margin-right:6px; color:var(--primary);"></i>
                  Murojaat quyidagi ma'lumotlar bilan yuboriladi: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->phone }}). Xabar ichki tartibda ko'rib chiqiladi va zarur bo'lsa mas'ul xodim sizga javob beradi.
              </div>

              <form class="contact-form" id="contact-form" method="post" action="{{ route('contact.store') }}">
                @csrf
                <textarea
                  id="shikoyat"
                  name="note"
                  placeholder="Murojaat mavzusi (masalan: Texnik muammo, Parolni tiklash, Taklif)"
                  rows="2"
                  required
                >{{ old('note') }}</textarea>
                <textarea
                  id="message"
                  name="message"
                  rows="5"
                  placeholder="Xabaringiz matnini bu yerga yozing..."
                  required
                >{{ old('message') }}</textarea>
                <x-turnstile-field />
                <button class="btn btn-prime" type="submit">Yuborish</button>
                <p id="form-message" class="form-message" aria-live="polite"></p>
              </form>
            @else
              <div class="contact-auth-prompt">
                <div class="icon-wrap contact-auth-prompt-icon">
                  <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="contact-auth-prompt-title">Murojaat yuborish</h2>
                <p class="contact-auth-prompt-text">Xabar yuborish uchun tizimga kirishingiz lozim. Bu bizga siz bilan bog'lanishni osonlashtiradi.</p>
                <a href="{{ route('login') }}" class="btn btn-prime">
                  <i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>
                  Tizimga kirish
                </a>
              </div>
            @endauth
          </div>
        </div>
      </div>
    </main>

</x-loyouts.main>
