<x-loyouts.main title="81-IDUM | Kurslar">
  <section class="news-hero" id="home">

    <div class="container">
      <!-- Hero Content -->
      <div class="news-hero-content">
          <span class="badge">81-IDUM Kurslar</span>
          <h1>Bilim va kelajak shu yerda boshlanadi</h1>
          <p>
            Zamonaviy o'qitish metodikasi asosida tayyorlangan kurslarimiz
            orqali o'z salohiyatingizni oching.
          </p>
          <a href="#courses-list" class="btn" style="margin-top: 10px">Kurslarga o'tish<i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i></a>
      </div>
    </div>
  </section>

    <main>
      <!-- FILTER BAR -->
      <section class="container courses-filter-section" id="courses-list">
        <div class="section-head">
          <h2>Barcha kurslar</h2>
          <p>O'zingizga mos yo'nalishni tanlang</p>
        </div>

        <div class="filter-bar">
          <button class="filter-btn active" data-filter="all" type="button">
            Barchasi
          </button>
          <button class="filter-btn" data-filter="math" type="button">
            Matematika
          </button>
          <button class="filter-btn" data-filter="language" type="button">
            Tillar
          </button>
          <button class="filter-btn" data-filter="science" type="button">
            Fanlar
          </button>
          <button class="filter-btn" data-filter="tech" type="button">
            Texnologiya
          </button>
        </div>

        <!-- COURSE CARDS -->
        <div class="courses-grid" id="courses-grid">
          <article class="course-card course-card-v2 reveal" data-category="math">
            <div class="course-media">
              <img src="{{ asset('temp/img/0131(1).jpg') }}" alt="Chuqur matematika" />
              <div class="course-media-overlay">
                <div class="course-chip">
                  <i class="fa-solid fa-square-root-variable"></i>
                  <span>Matematika</span>
                </div>
              </div>
            </div>

            <div class="course-body-v2">
              <h3>Chuqur matematika</h3>
              <p>
                Olimpiada va imtihonlarga tayyorlanadigan kengaytirilgan
                matematik kurs.
              </p>
              <ul class="course-meta-v2">
                <li><i class="fa-regular fa-clock"></i> 6 oy</li>
                <li><i class="fa-solid fa-users"></i> 7–11-sinf</li>
                <li><i class="fa-solid fa-star"></i> 4.9</li>
              </ul>
              <a
                href="{{ route('courses', ['id' => 1]) }}"
                class="btn btn-sm details-btn course-cta"
                data-course-id="1"
                >Batafsil</a
              >
            </div>
          </article>

          <article class="course-card course-card-v2 reveal" data-category="language">
            <div class="course-media">
              <img src="{{ asset('temp/img/image.png') }}" alt="Ingliz tili (IELTS)" />
              <div class="course-media-overlay">
                <div class="course-chip">
                  <i class="fa-solid fa-language"></i>
                  <span>Tillar</span>
                </div>
              </div>
            </div>

            <div class="course-body-v2">
              <h3>Ingliz tili (IELTS)</h3>
              <p>
                IELTS imtihoniga maqsadli tayyorgarlik va chet el
                universitetlariga kirish.
              </p>
              <ul class="course-meta-v2">
                <li><i class="fa-regular fa-clock"></i> 4 oy</li>
                <li><i class="fa-solid fa-users"></i> 8–11-sinf</li>
                <li><i class="fa-solid fa-star"></i> 4.8</li>
              </ul>
              <a
                href="{{ route('courses', ['id' => 2]) }}"
                class="btn btn-sm details-btn course-cta"
                data-course-id="2"
                >Batafsil</a
              >
            </div>
          </article>

          <article class="course-card course-card-v2 reveal" data-category="science">
            <div class="course-media">
              <img src="{{ asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}" alt="Kimyo va biologiya" />
              <div class="course-media-overlay">
                <div class="course-chip">
                  <i class="fa-solid fa-flask"></i>
                  <span>Tabiiy fanlar</span>
                </div>
              </div>
            </div>

            <div class="course-body-v2">
              <h3>Kimyo va biologiya</h3>
              <p>
                Tibbiyot va tabiiy fanlar yo'nalishiga ixtisoslashtirilgan
                amaliy kurs.
              </p>
              <ul class="course-meta-v2">
                <li><i class="fa-regular fa-clock"></i> 5 oy</li>
                <li><i class="fa-solid fa-users"></i> 8–11-sinf</li>
                <li><i class="fa-solid fa-star"></i> 4.7</li>
              </ul>
              <a
                href="{{ route('courses', ['id' => 3]) }}"
                class="btn btn-sm details-btn course-cta"
                data-course-id="3"
                >Batafsil</a
              >
            </div>
          </article>

          <article class="course-card course-card-v2 reveal" data-category="tech">
            <div class="course-media">
              <img src="{{ asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}" alt="Dasturlash asoslari" />
              <div class="course-media-overlay">
                <div class="course-chip">
                  <i class="fa-solid fa-laptop-code"></i>
                  <span>Texnologiya</span>
                </div>
              </div>
            </div>

            <div class="course-body-v2">
              <h3>Dasturlash asoslari</h3>
              <p>
                Python va algoritmlar orqali zamonaviy dasturlash ko'nikmalarini
                egallash.
              </p>
              <ul class="course-meta-v2">
                <li><i class="fa-regular fa-clock"></i> 3 oy</li>
                <li><i class="fa-solid fa-users"></i> 6–11-sinf</li>
                <li><i class="fa-solid fa-star"></i> 4.9</li>
              </ul>
              <a
                href="{{ route('courses', ['id' => 4]) }}"
                class="btn btn-sm details-btn course-cta"
                data-course-id="4"
                >Batafsil</a
              >
            </div>
          </article>

          <article class="course-card course-card-v2 reveal" data-category="language">
            <div class="course-media">
              <img src="{{ asset('temp/img/0131(1).jpg') }}" alt="Rus tili" />
              <div class="course-media-overlay">
                <div class="course-chip">
                  <i class="fa-solid fa-globe"></i>
                  <span>Tillar</span>
                </div>
              </div>
            </div>

            <div class="course-body-v2">
              <h3>Rus tili</h3>
              <p>
                Muloqot, grammatika va rus adabiyotiga oid chuqurlashtirilgan
                kurs.
              </p>
              <ul class="course-meta-v2">
                <li><i class="fa-regular fa-clock"></i> 4 oy</li>
                <li><i class="fa-solid fa-users"></i> 5–11-sinf</li>
                <li><i class="fa-solid fa-star"></i> 4.6</li>
              </ul>
              <a
                href="{{ route('courses', ['id' => 5]) }}"
                class="btn btn-sm details-btn course-cta"
                data-course-id="5"
                >Batafsil</a
              >
            </div>
          </article>

          <article class="course-card course-card-v2 reveal" data-category="math">
            <div class="course-media">
              <img src="{{ asset('temp/img/image.png') }}" alt="Fizika va mexanika" />
              <div class="course-media-overlay">
                <div class="course-chip">
                  <i class="fa-solid fa-chart-line"></i>
                  <span>Matematika</span>
                </div>
              </div>
            </div>

            <div class="course-body-v2">
              <h3>Fizika va mexanika</h3>
              <p>
                Nazariy va amaliy fizikani birlashtirgan olimpiada darajasidagi
                kurs.
              </p>
              <ul class="course-meta-v2">
                <li><i class="fa-regular fa-clock"></i> 6 oy</li>
                <li><i class="fa-solid fa-users"></i> 9–11-sinf</li>
                <li><i class="fa-solid fa-star"></i> 4.8</li>
              </ul>
              <a
                href="{{ route('courses', ['id' => 6]) }}"
                class="btn btn-sm details-btn course-cta"
                data-course-id="6"
                >Batafsil</a
              >
            </div>
          </article>

          <article class="course-card course-card-v2 reveal" data-category="tech">
            <div class="course-media">
              <img src="{{ asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}" alt="Robototexnika" />
              <div class="course-media-overlay">
                <div class="course-chip">
                  <i class="fa-solid fa-robot"></i>
                  <span>Texnologiya</span>
                </div>
              </div>
            </div>

            <div class="course-body-v2">
              <h3>Robototexnika</h3>
              <p>
                Arduino va Lego Mindstorms asosida qurilmalar loyihalash va
                dasturlash.
              </p>
              <ul class="course-meta-v2">
                <li><i class="fa-regular fa-clock"></i> 4 oy</li>
                <li><i class="fa-solid fa-users"></i> 5–9-sinf</li>
                <li><i class="fa-solid fa-star"></i> 4.9</li>
              </ul>
              <a
                href="{{ route('courses', ['id' => 7]) }}"
                class="btn btn-sm details-btn course-cta"
                data-course-id="7"
                >Batafsil</a
              >
            </div>
          </article>

          <article class="course-card course-card-v2 reveal" data-category="science">
            <div class="course-media">
              <img src="{{ asset('temp/img/photo_2026-02-06_11-05-24-2.jpg') }}" alt="Geografiya va ekologiya" />
              <div class="course-media-overlay">
                <div class="course-chip">
                  <i class="fa-solid fa-atom"></i>
                  <span>Tabiiy fanlar</span>
                </div>
              </div>
            </div>

            <div class="course-body-v2">
              <h3>Geografiya va ekologiya</h3>
              <p>
                Dunyo geografiyasi, ekotizimlar va atrof-muhit ilmi bo'yicha
                kuch kurs.
              </p>
              <ul class="course-meta-v2">
                <li><i class="fa-regular fa-clock"></i> 3 oy</li>
                <li><i class="fa-solid fa-users"></i> 6–10-sinf</li>
                <li><i class="fa-solid fa-star"></i> 4.5</li>
              </ul>
              <a
                href="{{ route('courses', ['id' => 8]) }}"
                class="btn btn-sm details-btn course-cta"
                data-course-id="8"
                >Batafsil</a
              >
            </div>
          </article>
        </div>
      </section>
      <button
        class="mobile-courses-toggle"
        id="mobile-show-courses"
        type="button"
      >
        Barcha kurslar <i class="fa-solid fa-chevron-down"></i>
      </button>

      <!-- STATS SECTION -->
      <section class="courses-stats-section">
        <div class="container courses-stats">
          <div class="stat-item reveal">
            <span class="stat-num" data-target="1200">0</span>
            <p>O'quvchi</p>
          </div>
          <div class="stat-item reveal">
            <span class="stat-num" data-target="18">0</span>
            <p>Kurs yo'nalishi</p>
          </div>
          <div class="stat-item reveal">
            <span class="stat-num" data-target="40">0</span>
            <p>Tajribali ustoz</p>
          </div>
          <div class="stat-item reveal">
            <span class="stat-num" data-target="96">0</span>
            <p>% Muvaffaqiyat</p>
          </div>
        </div>
      </section>

      <!-- CTA SECTION -->
      <section class="container courses-cta-section reveal">
        <div class="courses-cta glass-section">
          <div class="cta-content">
            <h2>Kursga yozilishni xohlaysizmi?</h2>
            <p>
              Qo'shimcha ma'lumot olish yoki ro'yxatdan o'tish uchun biz bilan
              bog'laning.
            </p>
          </div>
          <a href="{{ route('contact') }}" class="btn"
            >Bog'lanish
            <i class="fa-solid fa-arrow-right" style="margin-left: 6px"></i
          ></a>
        </div>
      </section>

      <section
        class="course-details-modal"
        id="course-details-modal"
        aria-hidden="true"
      >
        <div class="course-details-overlay" data-close-modal="true"></div>
        <div
          class="course-details-dialog"
          role="dialog"
          aria-modal="true"
          aria-labelledby="course-details-title"
        >
          <button
            class="course-details-close"
            type="button"
            aria-label="Yopish"
            data-close-modal="true"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
          <div class="course-details-content" id="course-details-content"></div>
        </div>
      </section>
    </main>

</x-loyouts.main>
