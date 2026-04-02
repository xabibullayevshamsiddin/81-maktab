<x-loyouts.main title="81-IDUM | Kod tasdiqlash">
  <section class="signin-hero">
    <div class="container">
      <h1>Kod tasdiqlash</h1>
      <p>{{ $email }} manziliga yuborilgan 6 xonali kodni kiriting</p>
    </div>
  </section>

  <main class="signin-section">
    <div class="container">
      <div class="signin-card">
        <div class="signin-card-icon">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h2>Tasdiqlash kodi</h2>
        <p class="signin-subtitle">Kod 10 daqiqa amal qiladi</p>

        <form
          action="{{ $mode === 'login' ? route('login.verify') : route('register.verify') }}"
          method="POST"
          class="signin-form"
        >
          @csrf
          <label for="otp-code">6 xonali kod</label>
          <input
            type="text"
            id="otp-code"
            name="code"
            placeholder="123456"
            maxlength="6"
            required
            autocomplete="one-time-code"
          />
          @error('code')
            <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
          @enderror
          <button class="btn" type="submit">Tasdiqlash</button>
        </form>

        <form action="{{ $mode === 'login' ? route('login.verify.resend') : route('register.verify.resend') }}" method="POST" style="margin-top:10px;">
          @csrf
          <button class="btn btn-outline" type="submit">Kodni qayta yuborish</button>
        </form>
      </div>
    </div>
  </main>
</x-loyouts.main>

