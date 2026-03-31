<x-loyouts.main title="81-IDUM">
  <section class="hero" id="home">
    <video autoplay muted loop playsinline class="bg-video">
      <source
        src="{{ asset('temp/img/PixVerse_V5.6_Image_Text_540P_tiriltirib_ber.mp4') }}"
        type="video/mp4"
      />
    </video>
    <div class="overlay"></div>

    <div class="container">
      <div class="card-home">
        <div class="home-content">
          <h1 class="hero-title">
            <span>Har doim</span>
            <strong>YUQORI NATIJA</strong>
          </h1>
          <p>
            81-ixtisoslashtirilgan umumta'lim maktabi 1963-yilda tashkil
            etilgan. Maktabimiz matematika yo'nalishiga ixtisoslashgan bo'lib,
            ta'lim sifati, chet tillari va zamonaviy ko'nikmalarni
            rivojlantirishga alohida e'tibor qaratadi.
          </p>
        </div>
        <div class="home-btn">
          <a
            href="https://www.instagram.com/81_idum/"
            target="_blank"
            aria-label="Instagram"
          >
            <i class="fa-brands fa-instagram"></i>
          </a>
          <a
            href="https://www.facebook.com/groups/751099325082714"
            target="_blank"
            aria-label="Facebook"
          >
            <i class="fa-brands fa-facebook"></i>
          </a>
          <a
            href="https://t.me/tashabbus81IDUM"
            target="_blank"
            aria-label="Telegram"
          >
            <i class="fa-brands fa-telegram"></i>
          </a>
          <a
            href="https://www.youtube.com/@81-idum"
            target="_blank"
            aria-label="YouTube"
          >
            <i class="fa-brands fa-youtube"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <main>
    <section class="container reveal glass-section" id="about" style="padding-bottom: 50px">
      <div class="section-head">
        <h2>81-IDUM ga xush kelibsiz</h2>
        <p>Kelajak liderlarini tayyorlaydigan zamonaviy maktab muhiti</p>
      </div>
      <div class="about-modern">
        <article class="about-card">
          <h3>Fan va natija</h3>
          <p>
            Matematika, aniq fanlar va til yo'nalishida chuqurlashtirilgan
            darslar orqali o'quvchilarimiz har yili yuqori natijalarga
            erishmoqda.
          </p>
          <a href="#news" class="btn btn-sm">Yangiliklarni ko'rish</a>
        </article>
        <article class="about-card">
          <h3>Rivojlanish muhiti</h3>
          <p>
            Darsdan tashqari to'garaklar, mentorlik va zamonaviy
            laboratoriyalar yordamida o'quvchi salohiyati bosqichma-bosqich
            rivojlantiriladi.
          </p>
          <a href="{{ route('contact') }}" class="btn btn-sm">Bog'lanish</a>
        </article>
        <article class="about-highlight">
          <span class="badge">81-IDUM</span>
          <h3>Yangi avlod uchun kuchli poydevor</h3>
          <p>
            Intizom, sifatli ta'lim va raqamli ko'nikmalarni birlashtirgan
            holda har bir o'quvchini real hayotga tayyorlaymiz.
          </p>
        </article>
      </div>
    </section>

    <section class="container news reveal glass-section" id="news" style="margin-top: 50px">
      <div class="section-head">
        <h2>Yangiliklar</h2>
        <p>So'nggi voqealar va tadbirlar</p>
      </div>

      <div class="news-container">
        @forelse($posts as $post)
          <article class="news-card">
            <img
              src="{{ asset('storage/' . $post->image) }}"
              alt="{{ $post->title }}"
            />

            @if($post->category)
              <div style="padding: 12px 16px 0;">
                <span class="badge" style="margin-bottom: 0; background: rgba(21, 101, 192, 0.12); border: 1px solid rgba(21, 101, 192, 0.28); color: var(--primary);">
                  {{ $post->category->name }}
                </span>
              </div>
            @endif

            <h3>{{ $post->title }}</h3>
            <p>{{ $post->short_content }}</p>

            <div class="icon-links">
              <div class="icon-link">
                <span class="meta"><i class="fa-regular fa-eye"></i> {{ $post->views }}</span>
                <span class="meta"><i class="fa-regular fa-comment"></i> {{ $post->comments_count }}</span>
                <form action="{{ route('post.like', $post) }}" method="POST" class="js-like-form" style="margin-left: 4px;">
                  @csrf
                  <button class="like-btn" type="submit" aria-label="Yoqtirish">
                    <i class="fa-regular fa-heart"></i>
                    <span class="like-count">{{ $post->likes_count }}</span>
                  </button>
                </form>
              </div>
              <a href="{{ route('post.show', $post) }}" class="btn btn-sm">Batafsil</a>
            </div>
          </article>
        @empty
          <p>Hozircha yangiliklar yo‘q.</p>
        @endforelse
      </div>
    </section>

    <section class="teachers reveal" id="teachers">
      <div class="container teacher">
        <div class="teacher-content">
          <h2>Ustozlar jamoasi</h2>
          <p>
            Maktabimizda tajribali va malakali ustozlar faoliyat yuritadi.
            Ular fan bo'yicha chuqur bilim berish bilan birga, o'quvchilarni
            mustaqil fikrlash, jamoada ishlash va ijodkorlikka yo'naltiradi.
          </p>
          <a href="{{ route('teacher') }}" class="btn">Batafsil</a>
        </div>

        <article class="teacher-img">
          <img
            src="{{ asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
            alt="Ustozlar jamoasi"
          />
          <h3>Kasbiy yondashuv va zamonaviy metodika</h3>
          <p>
            Har bir darsda interaktiv usullar qo'llanadi. Bu yondashuv
            o'quvchilarni fanlarga qiziqtiradi va mustahkam natijaga olib
            keladi.
          </p>
          <a href="{{ route('teacher') }}" class="btn1">Batafsil</a>
        </article>
      </div>
    </section>
  </main>
</x-loyouts.main>
