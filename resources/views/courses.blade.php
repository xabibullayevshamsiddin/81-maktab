<x-loyouts.main title="81-IDUM | Kurslar">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content">
        <span class="badge">81-IDUM Kurslar</span>
        <h1>Bilim va kelajak shu yerda boshlanadi</h1>
        <p>Ustozlar tomonidan ochilgan va tasdiqlangan kurslar ro'yxati.</p>
      </div>
    </div>
  </section>

  <main>
    <section class="container courses-filter-section" id="courses-list">
      <div class="section-head">
        <h2>Barcha kurslar</h2>
        <p>Ustozlarga bog'langan faol kurslar</p>
      </div>

      <div class="courses-grid" id="courses-grid">
        @forelse($courses as $course)
          <article class="course-card reveal">
            <div class="course-card-media">
              <img
                src="{{ $course->coverImageUrl() }}"
                alt="{{ $course->title }}"
                loading="lazy"
                width="640"
                height="360"
              />
            </div>
            <div class="course-body">
              <h3>{{ $course->title }}</h3>
              <p>{{ \Illuminate\Support\Str::limit(strip_tags($course->description), 220) }}</p>
              <ul class="course-meta">
                <li><i class="fa-solid fa-user"></i> {{ $course->teacher?->full_name ?: '-' }}</li>
                <li><i class="fa-regular fa-clock"></i> {{ $course->duration }}</li>
                <li><i class="fa-solid fa-money-bill"></i> {{ $course->price }}</li>
                <li><i class="fa-regular fa-calendar"></i> {{ $course->start_date?->format('Y-m-d') }}</li>
              </ul>
              <div class="course-card-actions">
                @auth
                  @php
                    $enrollmentByCourseId = $enrollmentByCourseId ?? collect();
                    $en = $enrollmentByCourseId->get($course->id);
                    $isOwnCourse = (int) $course->created_by === (int) auth()->id();
                  @endphp
                  @if($isOwnCourse)
                    <p class="course-enroll-hint" style="font-size:13px;margin:0;">Bu siz yaratgan kurs — o‘z kursingizga yozilmaysiz.</p>
                    @if($en)
                      <form action="{{ route('courses.enroll.cancel', $course) }}" method="POST" class="course-enroll-form" style="margin-top:10px;" onsubmit="return confirm('Yozilishni olib tashlaysizmi?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm">Yozilishni olib tashlash</button>
                      </form>
                    @endif
                  @elseif($en && $en->status === \App\Models\CourseEnrollment::STATUS_APPROVED)
                    <span class="course-enrolled-pill"><i class="fa-solid fa-check"></i> Qabul qilingansiz</span>
                    <form action="{{ route('courses.enroll.cancel', $course) }}" method="POST" class="course-enroll-form" onsubmit="return confirm('Yozilishni bekor qilasizmi?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline btn-sm">Yozilishni bekor qilish</button>
                    </form>
                  @elseif($en && $en->status === \App\Models\CourseEnrollment::STATUS_PENDING)
                    <span class="course-enrolled-pill" style="background:rgba(245,158,11,.2);color:#b45309;"><i class="fa-regular fa-clock"></i> Ariza kutilmoqda</span>
                    <p class="course-enroll-hint" style="font-size:13px;margin:8px 0;">Kurs muallifi maʼlumotlarni ko‘rib, tasdiqlaydi.</p>
                    <form action="{{ route('courses.enroll.cancel', $course) }}" method="POST" class="course-enroll-form" onsubmit="return confirm('Arizani bekor qilasizmi?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline btn-sm">Arizani bekor qilish</button>
                    </form>
                  @elseif($en && $en->status === \App\Models\CourseEnrollment::STATUS_REJECTED)
                    <span class="course-enrolled-pill" style="background:rgba(185,28,28,.12);color:#b91c1c;"><i class="fa-solid fa-xmark"></i> Rad etilgan</span>
                    <p class="course-enroll-hint" style="font-size:13px;">Qayta ariza yuborishingiz mumkin.</p>
                    <form action="{{ route('courses.enroll', $course) }}" method="POST" class="course-enroll-form">
                      @csrf
                      <label class="course-enroll-label" for="enroll-phone-{{ $course->id }}">Telefon *</label>
                      <input type="text" id="enroll-phone-{{ $course->id }}" name="contact_phone" class="course-enroll-note" maxlength="40" value="{{ old('contact_phone', $en->contact_phone) }}" placeholder="+998 …" required />
                      <label class="course-enroll-label" for="enroll-grade-{{ $course->id }}">Sinf *</label>
                      <input type="text" id="enroll-grade-{{ $course->id }}" name="grade" class="course-enroll-note" maxlength="32" value="{{ old('grade', $en->grade) }}" placeholder="Masalan: 9-A" required />
                      <label class="course-enroll-label" for="enroll-level-{{ $course->id }}">Fan darajasi *</label>
                      <input type="text" id="enroll-level-{{ $course->id }}" name="subject_level" class="course-enroll-note" maxlength="120" value="{{ old('subject_level', $en->subject_level) }}" placeholder="Masalan: boshlang‘ich / o‘rta" required />
                      <label class="course-enroll-label" for="enroll-note-{{ $course->id }}">Izoh (ixtiyoriy)</label>
                      <textarea id="enroll-note-{{ $course->id }}" name="note" class="course-enroll-note" rows="2" maxlength="500" placeholder="Qo‘shimcha">{{ old('note') }}</textarea>
                      @foreach (['contact_phone','grade','subject_level','note'] as $f)
                        @error($f)
                          <span class="form-message" style="color:#b91c1c;font-size:13px;">{{ $message }}</span>
                        @enderror
                      @endforeach
                      <button type="submit" class="btn course-enroll-submit">
                        <i class="fa-solid fa-paper-plane"></i> Qayta ariza yuborish
                      </button>
                    </form>
                  @else
                    <form action="{{ route('courses.enroll', $course) }}" method="POST" class="course-enroll-form">
                      @csrf
                      <label class="course-enroll-label" for="enroll-phone-{{ $course->id }}">Telefon *</label>
                      <input type="text" id="enroll-phone-{{ $course->id }}" name="contact_phone" class="course-enroll-note" maxlength="40" value="{{ old('contact_phone') }}" placeholder="+998 …" required />
                      <label class="course-enroll-label" for="enroll-grade-{{ $course->id }}">Sinf *</label>
                      <input type="text" id="enroll-grade-{{ $course->id }}" name="grade" class="course-enroll-note" maxlength="32" value="{{ old('grade') }}" placeholder="Masalan: 9-A" required />
                      <label class="course-enroll-label" for="enroll-level-{{ $course->id }}">Fan darajasi *</label>
                      <input type="text" id="enroll-level-{{ $course->id }}" name="subject_level" class="course-enroll-note" maxlength="120" value="{{ old('subject_level') }}" placeholder="Masalan: boshlang‘ich / o‘rta" required />
                      <label class="course-enroll-label" for="enroll-note-{{ $course->id }}">Izoh (ixtiyoriy)</label>
                      <textarea id="enroll-note-{{ $course->id }}" name="note" class="course-enroll-note" rows="2" maxlength="500" placeholder="Aloqa uchun qo‘shimcha">{{ old('note') }}</textarea>
                      @foreach (['contact_phone','grade','subject_level','note'] as $f)
                        @error($f)
                          <span class="form-message" style="color:#b91c1c;font-size:13px;">{{ $message }}</span>
                        @enderror
                      @endforeach
                      <button type="submit" class="btn course-enroll-submit">
                        <i class="fa-solid fa-pen-to-square"></i> Ariza yuborish
                      </button>
                    </form>
                  @endif
                @else
                  <p class="course-enroll-guest">
                    <a href="{{ route('login') }}" class="btn btn-outline">Kirish</a>
                    <a href="{{ route('register') }}" class="btn">Ro‘yxatdan o‘tish</a>
                    <span class="course-enroll-hint">Yozilish uchun hisob kerak</span>
                  </p>
                @endauth
              </div>
            </div>
          </article>
        @empty
          <p>Hozircha kurslar yo'q.</p>
        @endforelse
      </div>
    </section>
  </main>
</x-loyouts.main>
