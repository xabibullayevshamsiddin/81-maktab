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
            Toshkent shahar Uchtepa tumani, Paxtakor MFY, Ali Qushchi ko'chasi
            3-uyda joylashgan 81-maktab 1963-yildan buyon faoliyat yuritadi.
            Bugun maktabda o'zbek va rus tillarida 2097 nafar o'quvchi 90 nafar
            pedagog rahbarligida ta'lim olmoqda.
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
        <h2>81-maktabga xush kelibsiz</h2>
        <p>Rasmiy ko'rsatkichlar bilan tanishing</p>
      </div>
      <div class="about-modern">
        <article class="about-card">
          <h3>O'quvchilar va sinflar</h3>
          <p>
            Maktabda 2097 nafar o'quvchi 60 ta sinfda tahsil oladi. Ta'lim
            jarayoni 2 smenada tashkil etilgan bo'lib, o'zbek va rus tillarida
            olib boriladi.
          </p>
          <a href="{{ route('about') }}" class="btn btn-sm">Batafsil ko'rish</a>
        </article>
        <article class="about-card">
          <h3>Pedagoglar salohiyati</h3>
          <p>
            90 nafar pedagog xodimning barchasi oliy ma'lumotli. Ularning 26
            nafari milliy va xalqaro sertifikatlarga ega bo'lib, maktabda kuchli
            ta'lim jamoasi shakllangan.
          </p>
          <a href="{{ route('teacher') }}" class="btn btn-sm">Ustozlarni ko'rish</a>
        </article>
        <article class="about-highlight">
          <span class="badge">81-maktab</span>
          <h3>Barqaror infratuzilma va natija</h3>
          <p>
            16 000 m2 hudud, 960 o'rinli bino, 45 ta kompyuter, 120 o'rinli
            oshxona va yaxshi holatdagi sport zali maktabning kundalik ta'lim
            muhiti uchun xizmat qiladi.
          </p>
        </article>
      </div>
    </section>

    <section class="container news reveal glass-section" id="news" style="margin-top: 50px">
      <div
        class="section-head"
        style="display: flex; align-items: end; justify-content: space-between; gap: 16px; flex-wrap: wrap;"
      >
        <div>
          <h2>Yangiliklar</h2>
          <p>So'nggi voqealar va tadbirlar</p>
        </div>
        <a href="{{ route('post') }}" class="btn btn-sm">Barcha yangiliklar</a>
      </div>

      <div class="news-container">
        @php $likedPostIds = $likedPostIds ?? collect(); @endphp
        @forelse($posts as $post)
          <article class="news-card">
            <img
              src="{{ asset('storage/' . $post->image) }}"
              alt="{{ $post->title }}"
              class="js-image-zoom-trigger zoomable-image"
              data-zoom-src="{{ asset('storage/' . $post->image) }}"
              role="button"
              tabindex="0"
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
                  <button class="like-btn {{ $likedPostIds->contains($post->id) ? 'liked' : '' }}" type="submit" aria-label="Yoqtirish">
                    <i class="{{ $likedPostIds->contains($post->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
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

        @if(isset($featuredTeacher) && $featuredTeacher)
          <article class="teacher-img">
            <img
              src="{{ $featuredTeacher->image ? asset('storage/' . $featuredTeacher->image) : asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
              alt="{{ $featuredTeacher->full_name }} profil rasmi"
            />
            <h3>{{ $featuredTeacher->full_name }}</h3>
            <p>
              {{ $featuredTeacher->bio ?: ($featuredTeacher->subject . ' fani bo‘yicha tajribali ustoz.') }}
            </p>
            <p class="profile-muted" style="margin-top:8px;">
              {{ $featuredTeacher->subject }}
              @if($featuredTeacher->experience_years)
                · {{ $featuredTeacher->experience_years }} yil tajriba
              @endif
            </p>
            <a href="{{ route('teacher.show', $featuredTeacher) }}" class="btn1">Ustoz haqida</a>
          </article>
        @else
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
        @endif
      </div>
    </section>
  </main>
</x-loyouts.main>
