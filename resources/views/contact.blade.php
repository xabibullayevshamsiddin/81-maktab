<x-loyouts.main title="81-IDUM | Aloqa">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
          <span class="badge">81-IDUM Aloqa</span>
          <h1>Biz bilan <strong>bog'laning</strong></h1>
          <p>
            Savol, taklif yoki murojaat bo'lsa, quyidagi forma orqali bizga yozing.
            Tez orada siz bilan bog'lanamiz.
          </p>
          <a href="#contact-main" class="btn"
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
                <a
                  href="https://yandex.uz/maps/org/51913117189/?ll=69.190318%2C41.306955&z=16"
                  target="_blank"
                  rel="noopener"
                >
                  Toshkent, Maktab No. 81
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
                <p><a href="mailto:info@school81.uz">info@school81.uz</a></p>
              </div>
            </article>
          </div>

          <div class="contact-form-wrap reveal">
            <h2>Xabar yuborish</h2>
            <form class="contact-form" id="contact-form">
              <input type="text" id="name" placeholder="Ismingiz" required />
              <input type="email" id="email" placeholder="Email" required />
              <input type="tel" id="phone" placeholder="Telefon" required />
              <textarea
                id="shikoyat"
                placeholder="Shikoyat yoki qo'shimcha izoh (ixtiyoriy)"
                rows="2"
              ></textarea>
              <textarea
                id="message"
                rows="5"
                placeholder="Xabaringiz"
                required
              ></textarea>
              <button class="btn" type="submit">Yuborish</button>
              <p id="form-message" class="form-message" aria-live="polite"></p>
            </form>
          </div>
        </div>
      </div>
    </main>

</x-loyouts.main>
