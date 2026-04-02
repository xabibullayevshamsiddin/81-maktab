<x-loyouts.main title="81-IDUM | Kurs ochish">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content reveal">
        <h1>Kurs ochish</h1>
        <p>Ustoz/Admin kurs ma'lumotlarini kiriting, email kod bilan tasdiqlang.</p>
      </div>
    </div>
  </section>

  <main class="news">
    <section class="container news reveal glass-section">
      <form action="{{ route('teacher.courses.store') }}" method="POST" class="comment-form" style="max-width: 720px;">
        @csrf

        <select name="teacher_id" class="form-control" required>
          <option value="">Ustozni tanlang</option>
          @foreach($teachers as $teacher)
            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
              {{ $teacher->full_name }} - {{ $teacher->subject }}
            </option>
          @endforeach
        </select>

        <input type="text" name="title" class="comment-input" placeholder="Kurs nomi" value="{{ old('title') }}" required>
        <input type="text" name="price" class="comment-input" placeholder="Narxi (masalan: 450 000 so'm)" value="{{ old('price') }}" required>
        <input type="text" name="duration" class="comment-input" placeholder="Davomiyligi (masalan: 3 oy)" value="{{ old('duration') }}" required>
        <input type="date" name="start_date" class="comment-input" value="{{ old('start_date') }}" required>
        <textarea name="description" rows="5" class="comment-input" placeholder="Kurs tavsifi" required>{{ old('description') }}</textarea>

        <button class="btn" type="submit">
          <i class="fa-solid fa-paper-plane"></i> Email kod yuborish
        </button>
      </form>
    </section>
  </main>
</x-loyouts.main>

