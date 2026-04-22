<x-loyouts.main title="81-IDUM | Kurs tasdiqlash">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>Kurs tasdiqlash</h1>
        <p><strong>{{ $course->title }}</strong> kursini email kod bilan tasdiqlang.</p>
      </div>
    </div>
  </section>

  <main class="news">
    <section class="container news reveal glass-section">
      <form action="{{ route('teacher.courses.verify', $course) }}" method="POST" class="comment-form" style="max-width: 520px;">
        @csrf
        <input type="text" name="code" class="comment-input" maxlength="6" placeholder="6 xonali kod" required>
        <button class="btn" type="submit">Tasdiqlash</button>
      </form>

      <form action="{{ route('teacher.courses.verify.resend', $course) }}" method="POST" style="margin-top:12px;">
        @csrf
        <button class="btn btn-outline" type="submit">Kodni qayta yuborish</button>
      </form>
    </section>
  </main>
</x-loyouts.main>

