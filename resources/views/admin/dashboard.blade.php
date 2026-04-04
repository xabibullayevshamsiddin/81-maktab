@extends('admin.layouts.main')

@section('title', 'Dashboard | Admin Panel')

@section('content')
@php
  $dashboardUser = auth()->user();
  $canManageContent = $dashboardUser->canManageContent();
  $canManageInbox = $dashboardUser->canManageInbox();
  $canManageEducation = $dashboardUser->canManageEducation();
  $canManageSystem = $dashboardUser->canManageSystem();
@endphp

<style>
  .dashboard-note {
    color: #6b7280;
    font-size: 14px;
  }

  .dashboard-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
  }

  .dashboard-card-head h6 {
    margin-bottom: 6px;
  }

  .dashboard-link {
    color: #365cf5;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
  }

  .dashboard-list {
    display: grid;
    gap: 14px;
  }

  .dashboard-list-item {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid #eef2f7;
  }

  .dashboard-list-item:last-child {
    padding-bottom: 0;
    border-bottom: 0;
  }

  .dashboard-list-item strong {
    display: block;
    color: #1a2142;
    margin-bottom: 4px;
  }

  .dashboard-meta {
    color: #9aa4ca;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
  }

  .dashboard-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
  }

  .dashboard-badge.info {
    color: #365cf5;
    background: rgba(54, 92, 245, 0.12);
  }

  .dashboard-badge.success {
    color: #059669;
    background: rgba(5, 150, 105, 0.12);
  }

  .dashboard-badge.warning {
    color: #b45309;
    background: rgba(245, 158, 11, 0.16);
  }

  .dashboard-badge.danger {
    color: #dc2626;
    background: rgba(220, 38, 38, 0.12);
  }

  .dashboard-badge.dark {
    color: #1f2937;
    background: rgba(15, 23, 42, 0.08);
  }

  .dashboard-kpi {
    display: grid;
    gap: 12px;
  }

  .dashboard-kpi-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    background: #f8fafc;
  }

  .dashboard-kpi-row strong {
    color: #1a2142;
    display: block;
    margin-bottom: 2px;
  }

  .dashboard-empty {
    color: #9aa4ca;
    font-size: 14px;
    padding: 10px 0;
  }

</style>

<section class="section">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-8">
          <div class="title">
            <h2>Boshqaruv paneli</h2>
            <p class="dashboard-note mb-0">Saytga qo'shilgan ma'lumotlar va so'nggi faoliyat bir joyda.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="breadcrumb-wrapper">
            <div class="text-md-end">
              <span class="dashboard-badge info">{{ $dashboardUser->role_label }}</span>
              <p class="dashboard-note mb-0 mt-2">{{ now()->format('d.m.Y H:i') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xl-3 col-lg-4 col-sm-6">
        <div class="icon-card mb-30">
          <div class="icon primary">
            <i class="mdi mdi-account-group-outline"></i>
          </div>
          <div class="content">
            <h6 class="mb-10">Foydalanuvchilar</h6>
            <h3 class="text-bold mb-10">{{ number_format($stats['users']) }}</h3>
            <p class="text-sm text-gray mb-0">Ustozlar: {{ number_format($stats['teachers']) }}</p>
          </div>
        </div>
      </div>

      @if($canManageContent)
        <div class="col-xl-3 col-lg-4 col-sm-6">
          <div class="icon-card mb-30">
            <div class="icon success">
              <i class="mdi mdi-newspaper-variant-outline"></i>
            </div>
            <div class="content">
              <h6 class="mb-10">Yangiliklar</h6>
              <h3 class="text-bold mb-10">{{ number_format($stats['posts']) }}</h3>
              <p class="text-sm text-gray mb-0">Kategoriyalar: {{ number_format($stats['categories']) }}</p>
            </div>
          </div>
        </div>
      @endif

      @if($canManageInbox)
        <div class="col-xl-3 col-lg-4 col-sm-6">
          <div class="icon-card mb-30">
            <div class="icon orange">
              <i class="mdi mdi-comment-text-multiple-outline"></i>
            </div>
            <div class="content">
              <h6 class="mb-10">Izohlar</h6>
              <h3 class="text-bold mb-10">{{ number_format($stats['comments']) }}</h3>
              <p class="text-sm text-gray mb-0">Kutilmoqda: {{ number_format($stats['pending_comments']) }}</p>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-sm-6">
          <div class="icon-card mb-30">
            <div class="icon purple">
              <i class="mdi mdi-email-outline"></i>
            </div>
            <div class="content">
              <h6 class="mb-10">Aloqa xabarlari</h6>
              <h3 class="text-bold mb-10">{{ number_format($stats['contact_messages']) }}</h3>
              <p class="text-sm text-gray mb-0">Bugun: {{ number_format($stats['today_messages']) }}</p>
            </div>
          </div>
        </div>
      @endif

      @if($canManageEducation)
        <div class="col-xl-3 col-lg-4 col-sm-6">
          <div class="icon-card mb-30">
            <div class="icon success">
              <i class="mdi mdi-book-open-page-variant-outline"></i>
            </div>
            <div class="content">
              <h6 class="mb-10">Kurslar</h6>
              <h3 class="text-bold mb-10">{{ number_format($stats['courses']) }}</h3>
              <p class="text-sm text-gray mb-0">Nashrda: {{ number_format($stats['published_courses']) }}</p>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-sm-6">
          <div class="icon-card mb-30">
            <div class="icon primary">
              <i class="mdi mdi-file-document-edit-outline"></i>
            </div>
            <div class="content">
              <h6 class="mb-10">Imtihonlar</h6>
              <h3 class="text-bold mb-10">{{ number_format($stats['exams']) }}</h3>
              <p class="text-sm text-gray mb-0">Faol: {{ number_format($stats['active_exams']) }}</p>
            </div>
          </div>
        </div>
      @endif
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card-style mb-30">
          <div class="dashboard-card-head">
            <div>
              <h6 class="text-medium mb-0">Tezkor nazorat</h6>
              <p class="dashboard-note mb-0">Hozir e'tibor talab qilayotgan asosiy ko'rsatkichlar.</p>
            </div>
          </div>

          <div class="dashboard-kpi">
            @if($canManageInbox)
              <div class="dashboard-kpi-row">
                <div>
                  <strong>Moderatsiya kutayotgan izohlar</strong>
                  <span class="dashboard-note">Post va ustoz sahifalaridagi izohlar yig'indisi</span>
                </div>
                <span class="dashboard-badge warning">{{ $stats['pending_comments'] }}</span>
              </div>
            @endif

            @if($canManageInbox)
              <div class="dashboard-kpi-row">
                <div>
                  <strong>Bugungi aloqa xabarlari</strong>
                  <span class="dashboard-note">Kontakt formasidan kelgan yangi murojaatlar</span>
                </div>
                <span class="dashboard-badge info">{{ $stats['today_messages'] }}</span>
              </div>
            @endif

            @if($canManageEducation)
              <div class="dashboard-kpi-row">
                <div>
                  <strong>Kutilayotgan kurs arizalari</strong>
                  <span class="dashboard-note">Tasdiq yoki rad etish kutilayotgan yozilishlar</span>
                </div>
                <span class="dashboard-badge warning">{{ $stats['pending_enrollments'] }}</span>
              </div>

              <div class="dashboard-kpi-row">
                <div>
                  <strong>Nashrga tayyor kurslar</strong>
                  <span class="dashboard-note">Hozir saytda ko'rinayotgan kurslar soni</span>
                </div>
                <span class="dashboard-badge success">{{ $stats['published_courses'] }}</span>
              </div>

              <div class="dashboard-kpi-row">
                <div>
                  <strong>Imtihon natijalari</strong>
                  <span class="dashboard-note">Topshirilgan natijalar va o'tganlar ulushi</span>
                </div>
                <span class="dashboard-badge dark">{{ $stats['exam_results'] }} / {{ $stats['passed_results'] }}</span>
              </div>
            @endif

            @if($canManageContent)
              <div class="dashboard-kpi-row">
                <div>
                  <strong>Kontent hajmi</strong>
                  <span class="dashboard-note">Yangiliklar va kategoriyalar holati</span>
                </div>
                <span class="dashboard-badge success">{{ $stats['posts'] }} / {{ $stats['categories'] }}</span>
              </div>
            @endif
          </div>
        </div>
      </div>

    </div>

    <div class="row">
      @if($canManageContent)
        <div class="col-lg-6">
          <div class="card-style mb-30 h-100">
            <div class="dashboard-card-head">
              <div>
                <h6 class="text-medium mb-0">So'nggi yangiliklar</h6>
                <p class="dashboard-note mb-0">Oxirgi qo'shilgan postlar.</p>
              </div>
              <a href="{{ route('posts.index') }}" class="dashboard-link">Barchasi</a>
            </div>

            <div class="dashboard-list">
              @forelse($recentPosts as $post)
                <div class="dashboard-list-item">
                  <div>
                    <strong>{{ \Illuminate\Support\Str::limit($post->title, 60) }}</strong>
                    <p class="dashboard-note mb-0">
                      {{ $post->category?->name ?: "Kategoriya yo'q" }}
                      | Ko'rishlar: {{ (int) ($post->views ?? 0) }}
                    </p>
                  </div>
                  <span class="dashboard-meta">{{ $post->created_at?->format('d.m H:i') }}</span>
                </div>
              @empty
                <p class="dashboard-empty">Hozircha postlar yo'q.</p>
              @endforelse
            </div>
          </div>
        </div>
      @endif

      @if($canManageInbox)
        <div class="col-lg-6">
          <div class="card-style mb-30 h-100">
            <div class="dashboard-card-head">
              <div>
                <h6 class="text-medium mb-0">So'nggi aloqa xabarlari</h6>
                <p class="dashboard-note mb-0">Kontakt bo'limidan kelgan yangi murojaatlar.</p>
              </div>
              <a href="{{ route('admin.contact-messages.index') }}" class="dashboard-link">Barchasi</a>
            </div>

            <div class="dashboard-list">
              @forelse($recentMessages as $message)
                <div class="dashboard-list-item">
                  <div>
                    <strong>{{ $message->name }}</strong>
                    <p class="dashboard-note mb-0">{{ \Illuminate\Support\Str::limit($message->message, 78) }}</p>
                    <p class="dashboard-note mb-0">{{ $message->phone }} | {{ $message->email }}</p>
                  </div>
                  <span class="dashboard-meta">{{ $message->created_at?->format('d.m H:i') }}</span>
                </div>
              @empty
                <p class="dashboard-empty">Hozircha aloqa xabarlari yo'q.</p>
              @endforelse
            </div>
          </div>
        </div>
      @endif
    </div>

    @if($canManageEducation)
      <div class="row">
        <div class="col-lg-6">
          <div class="card-style mb-30 h-100">
            <div class="dashboard-card-head">
              <div>
                <h6 class="text-medium mb-0">So'nggi kurs arizalari</h6>
                <p class="dashboard-note mb-0">Oxirgi kelgan yozilishlar va ularning holati.</p>
              </div>
              <a href="{{ route('admin.course-enrollments.index') }}" class="dashboard-link">Barchasi</a>
            </div>

            <div class="dashboard-list">
              @forelse($recentEnrollments as $row)
                @php
                  $enrollmentBadgeClass = match ($row->status) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    default => 'warning',
                  };
                  $enrollmentLabel = match ($row->status) {
                    'approved' => 'Tasdiqlangan',
                    'rejected' => 'Rad etilgan',
                    default => 'Kutilmoqda',
                  };
                @endphp
                <div class="dashboard-list-item">
                  <div>
                    <strong>{{ $row->user?->name ?: 'Foydalanuvchi' }}</strong>
                    <p class="dashboard-note mb-0">{{ $row->course?->title ?: "Kurs nomi yo'q" }}</p>
                  </div>
                  <div class="text-end">
                    <span class="dashboard-badge {{ $enrollmentBadgeClass }}">{{ $enrollmentLabel }}</span>
                    <div class="dashboard-meta mt-2">{{ $row->created_at?->format('d.m H:i') }}</div>
                  </div>
                </div>
              @empty
                <p class="dashboard-empty">Hozircha kurs arizalari yo'q.</p>
              @endforelse
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card-style mb-30 h-100">
            <div class="dashboard-card-head">
              <div>
                <h6 class="text-medium mb-0">So'nggi imtihon natijalari</h6>
                <p class="dashboard-note mb-0">Oxirgi topshirilgan imtihonlar bo'yicha natijalar.</p>
              </div>
              <a href="{{ route('admin.exams.results') }}" class="dashboard-link">Barchasi</a>
            </div>

            <div class="dashboard-list">
              @forelse($recentResults as $result)
                @php
                  $resultValue = ($result->points_earned !== null && $result->points_max !== null)
                    ? ($result->points_earned . ' / ' . $result->points_max)
                    : ($result->score . ' / ' . $result->total_questions);
                @endphp
                <div class="dashboard-list-item">
                  <div>
                    <strong>{{ $result->user?->name ?: 'Foydalanuvchi' }}</strong>
                    <p class="dashboard-note mb-0">{{ $result->exam?->title ?: 'Imtihon' }}</p>
                    <p class="dashboard-note mb-0">Natija: {{ $resultValue }}</p>
                  </div>
                  <div class="text-end">
                    <span class="dashboard-badge {{ $result->passed ? 'success' : ($result->status === 'expired' ? 'warning' : 'danger') }}">
                      @if($result->passed)
                        O'tgan
                      @elseif($result->status === 'expired')
                        Vaqti tugagan
                      @else
                        Yiqilgan
                      @endif
                    </span>
                    <div class="dashboard-meta mt-2">{{ $result->created_at?->format('d.m H:i') }}</div>
                  </div>
                </div>
              @empty
                <p class="dashboard-empty">Hozircha imtihon natijalari yo'q.</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    @endif

    @if($canManageSystem)
      <div class="row">
        <div class="col-lg-6">
          <div class="card-style mb-30 h-100">
            <div class="dashboard-card-head">
              <div>
                <h6 class="text-medium mb-0">So'nggi foydalanuvchilar</h6>
                <p class="dashboard-note mb-0">Oxirgi qo'shilgan akkauntlar ro'yxati.</p>
              </div>
              <a href="{{ route('user') }}" class="dashboard-link">Barchasi</a>
            </div>

            <div class="dashboard-list">
              @forelse($recentUsers as $user)
                <div class="dashboard-list-item">
                  <div>
                    <strong>{{ $user->name }}</strong>
                    <p class="dashboard-note mb-0">{{ $user->email }}</p>
                    <p class="dashboard-note mb-0">{{ $user->role_label }}</p>
                  </div>
                  <span class="dashboard-meta">{{ $user->created_at?->format('d.m H:i') }}</span>
                </div>
              @empty
                <p class="dashboard-empty">Hozircha foydalanuvchilar yo'q.</p>
              @endforelse
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card-style mb-30 h-100">
            <div class="dashboard-card-head">
              <div>
                <h6 class="text-medium mb-0">Tizim ko'rsatkichlari</h6>
                <p class="dashboard-note mb-0">Asosiy boshqaruv bo'limlari bo'yicha umumiy holat.</p>
              </div>
            </div>

            <div class="dashboard-kpi">
              <div class="dashboard-kpi-row">
                <div>
                  <strong>Foydalanuvchi bazasi</strong>
                  <span class="dashboard-note">Barcha ro'yxatdan o'tgan foydalanuvchilar va ustozlar</span>
                </div>
                <span class="dashboard-badge info">{{ $stats['users'] }} / {{ $stats['teachers'] }}</span>
              </div>

              <div class="dashboard-kpi-row">
                <div>
                  <strong>Kurslar holati</strong>
                  <span class="dashboard-note">Nashrdagi va tekshiruvdagi kurslar soni</span>
                </div>
                <span class="dashboard-badge dark">{{ $stats['published_courses'] }} / {{ $stats['pending_courses'] }}</span>
              </div>

              <div class="dashboard-kpi-row">
                <div>
                  <strong>Imtihonlar holati</strong>
                  <span class="dashboard-note">Faol imtihonlar va yig'ilgan natijalar</span>
                </div>
                <span class="dashboard-badge success">{{ $stats['active_exams'] }} / {{ $stats['exam_results'] }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>
</section>
@endsection
