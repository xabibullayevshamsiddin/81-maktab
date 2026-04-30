<x-loyouts.main :title="__('public.layout.terms').' | 81-IDUM'">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge">81-IDUM</span>
        <h1 class="js-split-text">{{ __('public.layout.terms') }}</h1>
        <p>Platformadan foydalanish qoidalari va foydalanuvchi majburiyatlari.</p>
      </div>
    </div>
  </section>

  <main>
    <section class="container about-overview prime-reveal">
      <div class="section-head">
        <h2 class="js-split-text">Foydalanish bo'yicha asosiy qoidalar</h2>
        <p>Platformadan xavfsiz va tartibli foydalanish uchun muhim bandlar.</p>
      </div>

      <div class="about-grid prime-stagger">
        <article class="about-card prime-glow-hover">
          <h3>1. Umumiy qoidalar</h3>
          <p>
            Foydalanuvchi platformadan foydalanish orqali ushbu shartlarga rozilik bildiradi.
          </p>
        </article>

        <article class="about-card prime-glow-hover">
          <h3>2. Foydalanuvchi majburiyatlari</h3>
          <p>
            Tizimda haqorat, spam, yolg'on ma'lumot tarqatish yoki boshqa foydalanuvchilar huquqlarini buzish taqiqlanadi.
          </p>
        </article>

        <article class="about-card prime-glow-hover">
          <h3>3. Hisob xavfsizligi</h3>
          <p>
            Login ma'lumotlarini saqlash uchun foydalanuvchi shaxsan javob beradi.
          </p>
        </article>
      </div>

      <article class="glass-section prime-reveal" style="padding: 24px; margin-top: 24px;">
        <h3>Qo'shimcha bandlar</h3>
        <p style="margin-bottom: 10px;">
          Ushbu qoidalar 81-IDUM sayt va xizmatlaridan foydalanish tartibini belgilaydi.
        </p>
        <h3>4. O'zgarishlar</h3>
        <p style="margin-bottom: 12px;">
          Platforma ma'muriyati ushbu shartlarni istalgan vaqtda yangilash huquqiga ega.
        </p>
        <h3>5. Bog'lanish</h3>
        <p>
          Savollar uchun <a href="{{ route('contact') }}">Aloqa</a> sahifasidan foydalaning.
        </p>
      </article>
    </section>
  </main>
</x-loyouts.main>
