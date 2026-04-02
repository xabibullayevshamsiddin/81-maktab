<x-loyouts.main title="81-IDUM | Maktab haqida">
  @php
    $passportFacts = [
        ['label' => 'Yuridik manzili', 'value' => "Uchtepa tumani, Paxtakor MFY, Ali Qushchi ko'chasi 3-uy"],
        ['label' => 'Direktor', 'value' => 'Xaydarova Ziyoda Tolipovna'],
        ['label' => 'Menejerlik sertifikati', 'value' => 'Mavjud'],
        ['label' => 'Telefon', 'value' => '+99890-958-00-67'],
        ['label' => 'Joylashuvi', 'value' => 'Shahar markazi, tuman markazidan 1,4 km'],
        ['label' => 'Qurilgan yili', 'value' => 'Asosiy bino 1963-yil'],
        ['label' => 'Taʼmir turi', 'value' => 'Rekonstruksiya'],
        ['label' => 'Umumiy yer maydoni', 'value' => '16 000 m2'],
    ];

    $educationFacts = [
        ['label' => "O'quvchi o'rni", 'value' => "960 o'rinli"],
        ['label' => 'Koeffitsiyenti', 'value' => '2,2'],
        ['label' => "Ta'lim tili", 'value' => "O'zbek, rus"],
        ['label' => "O'quvchilar soni", 'value' => '2097 nafar'],
        ['label' => "O'zbek sinflardagi o'quvchilar", 'value' => '1566 nafar'],
        ['label' => 'Rus sinflardagi o‘quvchilar', 'value' => '531 nafar'],
        ['label' => 'Sinflar soni', 'value' => '60 ta'],
        ['label' => "O'zbek sinflar", 'value' => '45 ta'],
        ['label' => 'Rus sinflar', 'value' => '15 ta'],
        ['label' => 'Navbatliligi', 'value' => '2 smenali'],
        ['label' => 'I smena', 'value' => "32 ta sinf / 1050 nafar o'quvchi"],
        ['label' => 'II smena', 'value' => "28 ta sinf / 1047 nafar o'quvchi"],
    ];

    $staffFacts = [
        ['label' => 'Pedagog xodimlar', 'value' => '90 nafar'],
        ['label' => "Oliy ma'lumotli", 'value' => '90 nafar'],
        ['label' => "O'rta maxsus", 'value' => '0 nafar'],
        ['label' => 'Oliy toifali', 'value' => '21 nafar'],
        ['label' => 'I toifa', 'value' => '24 nafar'],
        ['label' => 'II toifa', 'value' => '25 nafar'],
        ['label' => 'Mutaxassis', 'value' => '20 nafar'],
        ['label' => 'Milliy va xalqaro sertifikat', 'value' => '26 nafar pedagog'],
    ];

    $resultFacts = [
        ['label' => '11-sinf bitiruvchilari', 'value' => '2025-yilda 121 nafar'],
        ['label' => "OTMga kirish o'rtacha balli", 'value' => '82,9'],
        ['label' => 'OTMga kirganlar', 'value' => '74 nafar (61%)'],
        ['label' => "Ta'lim sifati past maktablar ro'yxati", 'value' => "Ro'yxatda yo'q"],
        ['label' => 'Yangi baholash tizimi', 'value' => 'Kiritilgan'],
    ];

    $facilityFacts = [
        [
            'title' => 'Maxsus fan xonalari',
            'items' => [
                'Fizika xonasi mavjud',
                'Kimyo xonasi mavjud',
                'Biologiya xonasi mavjud',
                "Qiz bolalar uchun mehnat xonasi mavjud",
                "O'g'il bolalar uchun mehnat xonasi mavjud",
            ],
        ],
        [
            'title' => 'Sport va tadbir maydonlari',
            'items' => [
                "18x30 o'lchamli sport zali, holati yaxshi",
                "150 o'rinli faollar zali, holati yaxshi",
                'Kutubxona holati yaxshi',
            ],
        ],
        [
            'title' => 'Raqamli infratuzilma',
            'items' => [
                '3 ta kompyuter sinfi mavjud',
                'Jami 45 ta kompyuter mavjud',
                "Kompyuterlar to'liq internetga ulangan",
            ],
        ],
        [
            'title' => 'Maishiy sharoitlar',
            'items' => [
                "120 o'rinli oshxona mavjud",
                'Bino ichkarisida 40 ta hojatxona, holati yaxshi',
                'Bino tashqarisida hojatxona mavjud emas',
                "Ichimlik suvi ta'minoti markazlashgan",
            ],
        ],
    ];
  @endphp

  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <span class="badge">81-maktab pasporti</span>
        <h1>Toshkent shahar Uchtepa tumani <strong>81-maktab haqida</strong></h1>
        <p>
          Uchtepa tumani, Paxtakor MFY, Ali Qushchi ko'chasi 3-uyda joylashgan
          81-maktab 1963-yildan buyon faoliyat yuritadi. Maktabda o'zbek va rus
          tillarida ta'lim beriladi, hozirda 2097 nafar o'quvchi va 90 nafar
          pedagog ta'lim jarayonida ishtirok etmoqda.
        </p>
        <a href="#overview" class="btn"
          >Ma'lumotlarga o'tish
          <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i
        ></a>
      </div>
    </div>
  </section>

  <main>
    <section class="container about-overview" id="overview">
      <div class="section-head">
        <h2>81-maktab haqida qisqacha</h2>
        <p>Rasmiy ko'rsatkichlar asosida yangilangan ma'lumotlar</p>
      </div>

      <div class="about-grid">
        <article class="about-info-card reveal">
          <h3>Joylashuv va boshqaruv</h3>
          <p>
            Maktab shahar markaziga yaqin hududda joylashgan. Ta'lim muassasasiga
            Xaydarova Ziyoda Tolipovna rahbarlik qiladi va menejerlik
            sertifikatiga ega.
          </p>
        </article>

        <article class="about-info-card reveal">
          <h3>Ta'lim jarayoni</h3>
          <p>
            Maktab 2 smenada ishlaydi, 60 ta sinfda o'zbek va rus tillarida
            ta'lim beriladi. Umumiy quvvat 960 o'rinli bo'lsa-da, 2097 nafar
            o'quvchi tahsil olmoqda.
          </p>
        </article>

        <article class="about-info-card reveal">
          <h3>Pedagogik salohiyat</h3>
          <p>
            90 nafar pedagogning barchasi oliy ma'lumotli. Ularning 26 nafari
            milliy yoki xalqaro sertifikatga ega bo'lib, turli toifadagi
            tajribali ustozlar jamoasi shakllangan.
          </p>
        </article>
      </div>

      <div class="glass-section" style="margin-top: 26px">
        <h3 style="margin-bottom: 12px; color: var(--primary)">
          Tezkor faktlar
        </h3>
        <ul class="fact-list">
          <li><strong>Yuridik manzil:</strong> Uchtepa tumani, Paxtakor MFY, Ali Qushchi ko'chasi 3-uy</li>
          <li><strong>Telefon:</strong> +99890-958-00-67</li>
          <li><strong>Umumiy yer maydoni:</strong> 16 000 m2</li>
          <li><strong>Ta'lim tillari:</strong> O'zbek va rus</li>
        </ul>
      </div>
    </section>

    <section class="about-stats-section">
      <div class="container about-stats">
        <div class="about-stat-item reveal">
          <strong>2097</strong>
          <span>nafar o'quvchi</span>
        </div>
        <div class="about-stat-item reveal">
          <strong>90</strong>
          <span>nafar pedagog</span>
        </div>
        <div class="about-stat-item reveal">
          <strong>60</strong>
          <span>ta sinf</span>
        </div>
        <div class="about-stat-item reveal">
          <strong>26</strong>
          <span>sertifikatli pedagog</span>
        </div>
      </div>
    </section>

    <section class="container milestone-section">
      <div class="section-head">
        <h2>Rasmiy ko'rsatkichlar</h2>
        <p>Maktab pasportidagi asosiy ma'lumotlar bo'limlar kesimida</p>
      </div>

      <div class="about-grid">
        <article class="about-info-card reveal">
          <h3>Maktab pasporti</h3>
          <ul class="fact-list">
            @foreach($passportFacts as $fact)
              <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
            @endforeach
          </ul>
        </article>

        <article class="about-info-card reveal">
          <h3>Ta'lim jarayoni</h3>
          <ul class="fact-list">
            @foreach($educationFacts as $fact)
              <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
            @endforeach
          </ul>
        </article>

        <article class="about-info-card reveal">
          <h3>Pedagogik tarkib</h3>
          <ul class="fact-list">
            @foreach($staffFacts as $fact)
              <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
            @endforeach
          </ul>
        </article>
      </div>

      <div class="glass-section reveal" style="margin-top: 26px">
        <h3 style="margin-bottom: 12px; color: var(--primary)">
          Bitiruvchilar va sifat ko'rsatkichlari
        </h3>
        <ul class="fact-list">
          @foreach($resultFacts as $fact)
            <li><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</li>
          @endforeach
        </ul>
      </div>
    </section>

    <section class="container milestone-section" style="padding-top: 0">
      <div class="section-head">
        <h2>Moddiy-texnik bazasi</h2>
        <p>O'quv va tarbiyaviy jarayon uchun yaratilgan infratuzilma</p>
      </div>

      <div class="about-grid">
        @foreach($facilityFacts as $facility)
          <article class="about-info-card reveal">
            <h3>{{ $facility['title'] }}</h3>
            <ul class="fact-list">
              @foreach($facility['items'] as $item)
                <li>{{ $item }}</li>
              @endforeach
            </ul>
          </article>
        @endforeach
      </div>
    </section>

    <section class="container about-cta reveal">
      <div class="glass-section about-cta-box">
        <div>
          <h2>Maktab bilan bog'lanmoqchimisiz?</h2>
          <p>
            Qabul, dars jarayoni yoki hamkorlik bo'yicha savollaringiz uchun
            aloqa sahifasi orqali murojaat qiling.
          </p>
        </div>
        <a href="{{ route('contact') }}" class="btn"
          >Aloqa
          <i class="fa-solid fa-arrow-right" style="margin-left: 6px"></i
        ></a>
      </div>
    </section>
  </main>

</x-loyouts.main>
