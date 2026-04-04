<x-loyouts.main title="81-IDUM | Parolni tiklash">
  <section class="signin-hero">
    <div class="container">
      <h1>Parolni tiklash</h1>
      <p>Email manzilingizni kiriting, biz sizga 6 xonali tiklash kodini yuboramiz.</p>
    </div>
  </section>

  <main class="signin-section">
    <div class="container">
      <div class="signin-card">
        <div class="signin-card-icon">
          <i class="fa-solid fa-unlock-keyhole"></i>
        </div>
        <h2>Parolni qayta o'rnatish</h2>
        <p class="signin-subtitle">Hisobingizga biriktirilgan email orqali yangi parol qo'yasiz.</p>

        <div class="signin-helper-box">
          <strong>Qanday ishlaydi?</strong>
          <p>Email yozasiz, kod olasiz, keyin shu kod bilan yangi parol kiritasiz.</p>
        </div>

        <form action="{{ route('password.forgot.send') }}" method="POST" class="signin-form">
          @csrf
          <label for="forgot-email">Email</label>
          <input
            type="email"
            id="forgot-email"
            name="email"
            value="{{ old('email', $email ?? '') }}"
            placeholder="ism@gmail.com"
            required
            autocomplete="email"
          />
          @error('email')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror

          <button class="btn" type="submit">Tiklash kodini yuborish</button>
        </form>

        <div class="signin-divider">
          <span>yoki</span>
        </div>

        <p class="signin-register">
          Parolingiz esingizdami? <a href="{{ route('login') }}">Tizimga kirish</a>
        </p>
      </div>
    </div>
  </main>
</x-loyouts.main>
