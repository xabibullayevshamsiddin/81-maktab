<x-loyouts.main title="404 Not Found">
  <section class="container reveal" style="padding: 60px 0;">
    <div class="card-style" style="max-width: 720px; margin: 0 auto; text-align:center;">
      <h1 style="font-size: 72px; margin: 0; color: var(--primary); line-height: 1;">
        404
      </h1>
      <h2 style="margin-top: 10px;">Sahifa topilmadi</h2>
      <p style="margin-top: 12px;">
        Siz so‘ragan manzil mavjud emas yoki o‘chirilgan bo‘lishi mumkin.
      </p>

      <div style="margin-top: 20px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('home') }}" class="btn">Bosh sahifa</a>
        <a href="{{ route('courses') }}" class="btn btn-outline">Kurslar</a>
        <a href="{{ route('teacher') }}" class="btn btn-outline">Ustozlar</a>
      </div>
    </div>
  </section>
</x-loyouts.main>

