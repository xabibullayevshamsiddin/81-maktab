<x-loyouts.main title="81-IDUM | Tizimga kirish">

    <section class="signin-hero">
        <div class="container">
            <h1>Tizimga kirish</h1>
            <p>Hisobingizga kiring yoki yangi hisob yarating</p>
        </div>
    </section>

    <main class="signin-section">
        <div class="container">
            <div class="signin-card">
                <div class="signin-card-icon">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <h2>Kirish</h2>
                <p class="signin-subtitle">Email va parolingizni kiriting</p>

                <form action="{{ route('authenticate') }}" method="POST" class="signin-form" id="signin-form-server">
                    @csrf
                    <label for="signin-email">Email</label>
                    <input type="email" id="signin-email" name="email" placeholder="ism@gmail.com" required
                        autocomplete="email" />
                    <label for="signin-password">Parol</label>
                    <div class="pw-wrap">
                        <input type="password" id="signin-password" name="password" placeholder="Parolingiz"
                            required autocomplete="current-password" />
                        <button type="button" class="pw-toggle" aria-label="Parolni ko'rsatish"
                            data-target="signin-password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('email')
                        <p class="form-message" style="color:#b91c1c;">{{ $message }}</p>
                    @enderror
                    <a href="{{ route('contact') }}" class="forgot">Parolni unutdingizmi?</a>
                    <button class="btn" type="submit">Kirish</button>
                    <p id="signin-message" class="form-message" aria-live="polite"></p>
                </form>

                <div class="signin-divider">
                    <span>yoki</span>
                </div>

                <p class="signin-register">
                    Hisobingiz yo'qmi? <a href="{{ route('register') }}">Ro'yxatdan o'tish</a>
                </p>
            </div>
        </div>
    </main>

</x-loyouts.main>
