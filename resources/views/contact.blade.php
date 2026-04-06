<x-loyouts.main title="81-IDUM | Aloqa">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
          <span class="badge">81-IDUM Aloqa</span>
          <h1>Biz bilan <strong>bog'laning</strong></h1>
          <p>
            Savolingiz, taklifingiz yoki murojaatingiz bo'lsa, quyidagi forma orqali bizga yozing.
            Parolni tiklash yoki maktabga oid boshqa masalalarda ham shu yerda murojaat qoldirishingiz mumkin.
            Tez orada siz bilan bog'lanamiz.
          </p>
          <a href="#contact-main" class="btn" style="margin-top:30px;"
            >Xabar yuborish
            <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i
          ></a>
      </div>
    </div>
  </section>

    <main id="contact-main" class="contact-main">
      <div class="container">
        <div class="contact-layout">
          <div class="contact-cards reveal">
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
                  class="btn"
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

          <div class="contact-form-wrap reveal">
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">
              <h2 style="margin:0;">Xabar yuborish</h2>
              <x-site-rule-items area="contact" />
            </div>
            <form class="contact-form" id="contact-form" method="post" action="{{ route('contact.store') }}">
              @csrf
              <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Ismingiz" required />
              <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Email" required />
              <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Telefon" required />
              <textarea
                id="shikoyat"
                name="note"
                placeholder="Shikoyat yoki qo'shimcha izoh (ixtiyoriy)"
                rows="2"
              >{{ old('note') }}</textarea>
              <textarea
                id="message"
                name="message"
                rows="5"
                placeholder="Xabaringiz"
                required
              >{{ old('message') }}</textarea>
              <button class="btn" type="submit">Yuborish</button>
              <p id="form-message" class="form-message" aria-live="polite"></p>
            </form>
          </div>
        </div>
      </div>
    </main>

</x-loyouts.main>
