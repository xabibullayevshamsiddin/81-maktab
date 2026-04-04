<x-loyouts.main title="81-IDUM | Ustozlar">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
          <span class="badge">81-IDUM Ustozlar</span>
          <h1>Kasbiy tajriba va <strong>zamonaviy metodika</strong></h1>
          <p>
            Ustozlar jamoamiz fan bo'yicha chuqur bilim, amaliy yondashuv va
            individual qo'llab-quvvatlash orqali o'quvchilarning natijasini
            oshirishga xizmat qiladi.
          </p>
          <a href="#teachers-list" class="btn"
            >Jamoani ko'rish
            <i class="fa-solid fa-arrow-down" style="margin-left: 6px"></i
          ></a>
      </div>
    </div>
  </section>

    <main>
      <section class="container teachers-section" id="teachers-list">
        <div class="section-head">
          <h2>Ustozlar jamoasi</h2>
          <p>Fan yo'nalishlari bo'yicha tajribali pedagoglar</p>
        </div>

        <div class="teachers-grid">
          @forelse($teachers as $teacher)
            <article class="teacher-card reveal">
              <div class="teacher-photo-wrap">
                <img
                  src="{{ $teacher->image ? asset('storage/' . $teacher->image) : asset('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
                  alt="{{ $teacher->full_name }} profil rasmi"
                  class="teacher-photo"
                />
              </div>
              <div class="teacher-top">
                <div>
                  <h3>{{ $teacher->full_name }}</h3>
                  <p class="teacher-role">{{ $teacher->subject }}</p>
                </div>
              </div>
              <p class="teacher-desc">
                {{ $teacher->bio ?: 'Ushbu ustoz haqida batafsil ma\'lumotni ochib ko\'rishingiz mumkin.' }}
              </p>
              <ul class="teacher-meta">
                <li><i class="fa-solid fa-award"></i> {{ $teacher->experience_years }} yil tajriba</li>
                <li><i class="fa-solid fa-users"></i> {{ $teacher->grades ?: 'Barcha sinflar' }}</li>
              </ul>
              @if(filled($teacher->achievements))
                <p class="teacher-achievements-preview"><i class="fa-solid fa-trophy"></i> {{ \Illuminate\Support\Str::limit(trim(strtok($teacher->achievements, "\n")), 100) }}</p>
              @endif
              <div class="teacher-actions">
                @php $likedTeacherIds = $likedTeacherIds ?? collect(); @endphp
                @auth
                  <form action="{{ route('teacher.like', $teacher) }}" method="POST" class="js-like-form" style="display:inline;">
                    @csrf
                    <button class="like-btn {{ $likedTeacherIds->contains($teacher->id) ? 'liked' : '' }}" type="submit" aria-label="Ustozni yoqtirish">
                      <i class="{{ $likedTeacherIds->contains($teacher->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                      <span class="like-count">{{ $teacher->likes_count ?? 0 }}</span>
                    </button>
                  </form>
                @endauth
                <a href="{{ route('teacher.show', $teacher) }}" class="btn btn-sm">Batafsil</a>
              </div>
            </article>
          @empty
            <p>Hozircha ustozlar qo‘shilmagan.</p>
          @endforelse
        </div>
      </section>

      <section class="teaching-approach">
        <div class="container approach-grid">
          <article class="approach-card reveal">
            <h3>Ta'lim yondashuvi</h3>
            <p>
              Har bir fan bo'yicha darslar nazariya, amaliy topshiriqlar va
              individual kuzatuv asosida tashkil qilinadi.
            </p>
            <ul>
              <li><i class="fa-solid fa-check"></i> Interaktiv dars jarayoni</li>
              <li><i class="fa-solid fa-check"></i> Muntazam oraliq monitoring</li>
              <li><i class="fa-solid fa-check"></i> Ota-ona bilan muntazam aloqa</li>
            </ul>
          </article>

          <article class="approach-image-card reveal">
            <img
              src="{{ ('temp/img/how-to-be-teacher-malaysia-feature.png') }}"
              alt="81-IDUM ustozlar jamoasi"
            />
            <div class="approach-caption">
              <h3>Jamoaviy kuch va natija</h3>
              <p>Ustozlar hamkorligi o'quvchi natijasini barqaror oshiradi.</p>
            </div>
          </article>
        </div>
      </section>

      <section class="teachers-stats-section">
        <div class="container teachers-stats">
          <div class="teachers-stat-item reveal">
            <strong data-target="40" class="stat-num">0</strong>
            <span>Tajribali ustoz</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong data-target="18" class="stat-num">0</strong>
            <span>Fan yo'nalishi</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong data-target="1200" class="stat-num">0</strong>
            <span>O'quvchi</span>
          </div>
          <div class="teachers-stat-item reveal">
            <strong data-target="96" class="stat-num">0</strong>
            <span>% Qoniqish darajasi</span>
          </div>
        </div>
      </section>

      <section class="container teachers-cta-section reveal">
        <div class="glass-section teachers-cta">
          <div>
            <h2>Ustozlar bilan bog'lanish</h2>
            <p>
              Darslar, maslahat yoki ro'yxatdan o'tish bo'yicha qo'shimcha
              ma'lumot olish uchun bizga murojaat qiling.
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

