<x-loyouts.main :title="__('public.layout.privacy_policy').' | 81-IDUM'">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">81-IDUM</span>
        <h1 class="js-split-text">{{ __('public.layout.privacy_policy') }}</h1>
        <p>Shaxsiy ma'lumotlaringizni qanday himoya qilishimiz va qayta ishlashimiz haqida.</p>
      </div>
    </div>
  </section>

  <main>
    <section class="container about-overview prime-reveal">
      <div class="section-head">
        <h2 class="js-split-text">Maxfiylik bo'yicha asosiy bandlar</h2>
        <p>Ma'lumotlar xavfsizligi va ulardan foydalanish tartibi quyidagicha.</p>
      </div>

      <div class="about-grid prime-stagger">
        <article class="about-card prime-glow-hover">
          <h3>1. Yig'iladigan ma'lumotlar</h3>
          <p>
            Ro'yxatdan o'tish, profil to'ldirish, kurs va imtihonlardan foydalanish jarayonida ism, email, telefon va faoliyat ma'lumotlari qayta ishlanishi mumkin.
          </p>
        </article>

        <article class="about-card prime-glow-hover">
          <h3>2. Ma'lumotlardan foydalanish</h3>
          <p>
            Ma'lumotlar hisobingizni yuritish, ta'lim xizmatini ko'rsatish, xavfsizlikni ta'minlash va platformani yaxshilash maqsadida ishlatiladi.
          </p>
        </article>

        <article class="about-card prime-glow-hover">
          <h3>3. Uchinchi tomonlarga uzatish</h3>
          <p>
            Qonunchilik talab qilgan holatlardan tashqari, ma'lumotlar ruxsatsiz uchinchi tomonlarga berilmaydi.
          </p>
        </article>
      </div>

      <article class="glass-section prime-reveal" style="padding: 24px; margin-top: 24px;">
        <h3>Umumiy izoh</h3>
        <p style="margin-bottom: 10px;">
          Ushbu sahifa 81-IDUM platformasida foydalanuvchi ma'lumotlari qanday yig'ilishi, saqlanishi va ishlatilishini tushuntiradi.
        </p>
        <h3>4. Bog'lanish</h3>
        <p>
          Savollar bo'lsa, <a href="{{ route('contact') }}">Aloqa</a> sahifasi orqali murojaat qiling.
        </p>
      </article>
    </section>
  </main>
</x-loyouts.main>
