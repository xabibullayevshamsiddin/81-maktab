@extends('admin.layouts.main')

@section('title', 'Sinflar boshqaruvi')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Sinflar boshqaruvi</h2>
          </div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sinflar</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    @if (session('success'))
      <div class="alert-box success-alert mb-20">
        <div class="alert">{{ session('success') }}</div>
      </div>
    @endif

    @if (session('error'))
      <div class="alert-box danger-alert mb-20">
        <div class="alert">{{ session('error') }}</div>
      </div>
    @endif

    <div class="row">
      <div class="col-lg-5">
        <div class="card-style mb-30">
          <h6 class="mb-10">Yangi sinf qo'shish</h6>
          <p class="text-sm mb-20">Masalan: `1-F`, `10-E`, `11-A`. O'chirilgan sinf qayta kiritilsa, u yana faollashadi.</p>

          <form action="{{ route('admin.school-classes.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-sm-5 mb-20">
                <label class="form-label">Sinf raqami</label>
                <select name="grade_number" class="form-select" required>
                  @for ($grade = 1; $grade <= 11; $grade++)
                    <option value="{{ $grade }}" @selected((int) old('grade_number', 1) === $grade)>{{ $grade }}</option>
                  @endfor
                </select>
              </div>
              <div class="col-sm-7 mb-20">
                <label class="form-label">Bo'lim harfi</label>
                <input type="text" name="section" value="{{ old('section') }}" class="form-control" maxlength="10" placeholder="F" required>
              </div>
            </div>
            <button type="submit" class="main-btn primary-btn btn-hover">Sinfni saqlash</button>
          </form>
        </div>

        <div class="card-style mb-30">
          <h6 class="mb-10">O'quv yilini ko'tarish</h6>
          <p class="text-sm mb-20">Avval `dry run` bilan tekshiring. Real ishga tushirilganda natija qayta yozilib ketmasligi uchun yil bo'yicha lock saqlanadi.</p>

          <form action="{{ route('admin.school-classes.promote') }}" method="POST" data-confirm="Sinf promotion jarayonini ishga tushirasizmi?" data-confirm-title="O'quv yilini ko'tarish" data-confirm-variant="warning" data-confirm-ok="Ishga tushirish">
            @csrf
            <div class="row">
              <div class="col-sm-6 mb-20">
                <label class="form-label">Joriy yil</label>
                <input type="number" name="from_year" value="{{ old('from_year', now()->year) }}" min="2020" max="2100" class="form-control" required>
              </div>
              <div class="col-sm-6 mb-20">
                <label class="form-label">Keyingi yil</label>
                <input type="number" name="to_year" value="{{ old('to_year', now()->year + 1) }}" min="2021" max="2101" class="form-control" required>
              </div>
            </div>
            <label class="d-flex align-items-center gap-2 mb-10">
              <input type="checkbox" name="dry_run" value="1" checked>
              <span>Dry run: faqat hisoblab ko'rsatadi</span>
            </label>
            <label class="d-flex align-items-center gap-2 mb-20">
              <input type="checkbox" name="force" value="1">
              <span>Force: shu yil promotioni oldin bajarilgan bo'lsa qayta ishlatadi</span>
            </label>
            <button type="submit" class="main-btn warning-btn btn-hover">Promotionni ishga tushirish</button>
          </form>

          @if ($latestPromotion)
            <hr>
            <p class="text-sm mb-0">
              Oxirgi real promotion:
              <strong>{{ $latestPromotion->from_year }}-{{ $latestPromotion->to_year }}</strong>,
              {{ $latestPromotion->executed_at?->format('d.m.Y H:i') }}.
            </p>
          @endif
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card-style mb-30">
          <h6 class="mb-10">Faol va o'chirilgan sinflar</h6>
          <p class="text-sm mb-20">Sinf o'chirilsa, unga ulangan o'quvchilar keyingi kirishda majburiy yangi sinf tanlaydi.</p>

          @foreach ($classes as $gradeNumber => $gradeClasses)
            <div class="mb-25">
              <h6 class="mb-10">{{ $gradeNumber }}-sinf</h6>
              <div class="table-wrapper table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th><h6>Sinf</h6></th>
                      <th><h6>Status</h6></th>
                      <th><h6>O'quvchi</h6></th>
                      <th><h6>Amal</h6></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($gradeClasses as $schoolClass)
                      <tr>
                        <td><p><strong>{{ $schoolClass->display_name }}</strong></p></td>
                        <td>
                          <span class="badge {{ $schoolClass->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $schoolClass->is_active ? 'Faol' : "O'chirilgan" }}
                          </span>
                        </td>
                        <td><p>{{ $studentCounts[$schoolClass->display_name] ?? 0 }}</p></td>
                        <td>
                          @if ($schoolClass->is_active)
                            <form action="{{ route('admin.school-classes.destroy', $schoolClass) }}" method="POST" data-confirm="{{ $schoolClass->display_name }} sinfini o'chirasizmi? Unga ulangan foydalanuvchilar sinfni qayta tanlashga majbur bo'ladi." data-confirm-title="Sinfni o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="text-danger" style="background:none;border:none;padding:0;">
                                <i class="lni lni-trash-can"></i>
                              </button>
                            </form>
                          @else
                            <span class="text-muted">Qayta qo'shish formasidan faollashtiring</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
