@extends('admin.layouts.main')

@section('title', 'Foydalanuvchilar')

@section('content')
<section class="table-components">
  <div class="container-fluid">
    <div class="title-wrapper pt-30">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="title">
            <h2>Foydalanuvchilar</h2>
          </div>
        </div>
        <div class="col-md-6">
          <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                  Foydalanuvchilar
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <div class="tables-wrapper">
      <div class="row">
        <div class="col-lg-12">
          <div class="card-style mb-30">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;">
              <div>
                <h6 class="mb-5" style="font-size:1.1rem;font-weight:700;">Barcha foydalanuvchilar</h6>
                <p class="text-sm mb-5" style="color:var(--muted);">Ro'yxatda bazadagi barcha userlar ko'rsatiladi.</p>
                <p class="text-sm" style="color:var(--muted);margin:0;">
                  <i class="lni lni-shield me-1"></i>
                  Qalqon tugmasi foydalanuvchiga vaqtincha parol yaratadi va Telegram ga yuboradi.
                </p>
              </div>
              <div>
                <button type="button" class="main-btn info-btn btn-hover btn-sm" data-bs-toggle="modal" data-bs-target="#rulesGuideModal" style="display:inline-flex;align-items:center;gap:8px;border-radius:10px;font-weight:700;padding:9px 18px;box-shadow:0 4px 12px rgba(13,110,253,0.18);">
                  <i class="fa-solid fa-scale-balanced"></i> Bloklash mezonlari & qoidalari
                </button>
              </div>
            </div>
            
            @php
              $hasUserFilters = filled($q ?? '') || filled($selectedGrade ?? '') || filled($selectedStatus ?? '') || ((int) ($selectedRoleId ?? 0) > 0);
            @endphp

            <form method="get" action="{{ route('user') }}" class="admin-search-bar mb-20" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
              <input
                type="search"
                name="q"
                value="{{ $q ?? '' }}"
                placeholder="Ism, email yoki telefon bo'yicha..."
                autocomplete="off"
                class="form-control"
                style="max-width:320px;min-width:200px;flex:1;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;font-size:14px;"
              >
              <select name="grade" class="form-select" style="min-width:150px;max-width:170px;">
                <option value="">Barcha sinflar</option>
                @foreach (school_grade_grouped_options() as $groupLabel => $options)
                  <optgroup label="{{ $groupLabel }}">
                    @foreach ($options as $value => $label)
                      <option value="{{ $value }}" {{ ($selectedGrade ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
              <select name="status" class="form-select" style="min-width:150px;max-width:170px;">
                <option value="">Barcha statuslar</option>
                <option value="active" {{ ($selectedStatus ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="blocked" {{ ($selectedStatus ?? '') === 'blocked' ? 'selected' : '' }}>Block</option>
              </select>
              <select name="role_id" class="form-select" style="min-width:170px;max-width:200px;">
                <option value="">Barcha rollar</option>
                @foreach ($filterRoles as $roleFilter)
                  <option value="{{ $roleFilter->id }}" {{ (int) ($selectedRoleId ?? 0) === (int) $roleFilter->id ? 'selected' : '' }}>
                    {{ $roleFilter->label }}
                  </option>
                @endforeach
              </select>
              <button type="submit" class="main-btn primary-btn btn-hover btn-sm">Filtrlash</button>
              @if ($hasUserFilters)
                <a href="{{ route('user') }}" class="main-btn dark-btn btn-hover btn-sm">Tozalash</a>
              @endif
            </form>

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

            <div class="table-wrapper table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th><h6>ID</h6></th>
                    <th><h6>Ism</h6></th>
                    <th><h6>Email</h6></th>
                    <th><h6>Telefon</h6></th>
                    <th><h6>Sinf</h6></th>
                    <th><h6>Rol</h6></th>
                    <th><h6>Status</h6></th>
                    <th><h6>Sana</h6></th>
                    <th><h6>Amallar</h6></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($users as $user)
                    <tr>
                      <td><p>{{ $user->id }}</p></td>
                      <td>
                        <p><strong>{{ $user->name }}</strong></p>
                        @if ($user->id === auth()->id())
                          <span class="badge bg-primary">Siz</span>
                        @endif
                      </td>
                      <td><p>{{ $user->email }}</p></td>
                      <td><p>{{ $user->phone ?: '-' }}</p></td>
                      <td>
                        @if (auth()->id() !== $user->id && auth()->user()->canManage($user) && $user->hasRole(\App\Models\User::ROLE_USER))
                          <form action="{{ route('user.update', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <select name="grade" onchange="this.form.submit()" class="form-select form-select-sm" style="min-width: 120px;">
                              @if (! $user->grade)
                                <option value="" selected disabled>Sinf tanlang</option>
                              @endif
                              @foreach (school_grade_grouped_options() as $groupLabel => $options)
                                <optgroup label="{{ $groupLabel }}">
                                  @foreach ($options as $value => $label)
                                    <option value="{{ $value }}" {{ $user->grade === $value ? 'selected' : '' }}>
                                      {{ $label }}
                                    </option>
                                  @endforeach
                                </optgroup>
                              @endforeach
                            </select>
                          </form>
                        @else
                          <p>{{ $user->displayGrade('-') }}</p>
                        @endif
                      </td>
                      <td>
                        @if (auth()->id() !== $user->id && auth()->user()->canManage($user))
                          <form action="{{ route('user.update', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_active" value="{{ $user->is_active ? 1 : 0 }}">
                            <select name="role_id" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto;">
                              @foreach ($assignableRoles as $roleOption)
                                <option value="{{ $roleOption->id }}" {{ (int) $user->role_id === (int) $roleOption->id ? 'selected' : '' }}>
                                  {{ $roleOption->label }}
                                </option>
                              @endforeach
                            </select>
                          </form>
                        @else
                          <span class="badge {{ $user->admin_role_badge_class }}">
                            {{ $user->role_label }}
                          </span>
                        @endif
                      </td>
                      <td>
                        @if (auth()->id() !== $user->id && auth()->user()->canManage($user))
                          @if($user->isCurrentlyBlocked())
                            <span class="badge bg-danger" style="cursor:pointer;" onclick="openBlockModal('{{ $user->name }}', '{{ route('user.block', $user) }}')" title="Bloklangan: {{ $user->blocked_reason ?? 'Sababsiz' }} ({{ $user->blocked_until ? $user->blocked_until->diffForHumans() : 'Butun umr' }} gacha)">
                              <i class="lni lni-lock me-1"></i> Block
                            </span>
                          @else
                            <form action="{{ route('user.update', $user) }}" method="POST" class="d-inline">
                              @csrf
                              @method('PUT')
                              <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                              <select name="status_action" onchange="handleStatusChange(this, '{{ $user->name }}', '{{ route('user.block', $user) }}', '{{ route('user.unblock', $user) }}')" class="form-select form-select-sm" style="width: auto;">
                                <option value="active" selected>Active</option>
                                <option value="block">Block</option>
                              </select>
                            </form>
                          @endif
                        @else
                          <span class="badge {{ $user->isCurrentlyBlocked() ? 'bg-danger' : 'bg-success' }}">
                            {{ $user->isCurrentlyBlocked() ? 'Block' : 'Active' }}
                          </span>
                        @endif
                      </td>

                      <td><p>{{ $user->created_at?->format('Y-m-d H:i') }}</p></td>
                      <td>
                        <div class="action">
                          @if($user->telegram_chat_id)
                            <button type="button" class="text-primary me-2" style="background:none;border:none;padding:0;cursor:pointer;"
                              title="Telegram orqali xabar yuborish"
                              onclick="openTelegramModal('{{ $user->name }}', '{{ route('user.send-telegram', $user) }}')">
                              <i class="lni lni-telegram"></i>
                            </button>
                          @else
                            <span class="text-muted me-2" title="Telegram bog'lanmagan" style="cursor:not-allowed;">
                              <i class="lni lni-telegram"></i>
                            </span>
                          @endif                          @if (auth()->id() !== $user->id && auth()->user()->canManage($user) && $user->telegram_chat_id)
                            <form action="{{ route('user.temp-password.generate', $user) }}" method="POST" style="display:inline;"
                              data-confirm="{{ $user->name }} uchun vaqtincha parol yaratilsinmi? Barcha qurilmalar logout bo'ladi. Telegram ga yuboriladi."
                              data-confirm-title="Vaqtincha parol yaratish"
                              data-confirm-variant="warning"
                              data-confirm-ok="Yaratish">
                                @csrf
                                <button type="submit" class="text-warning me-2" style="background:none;border:none;padding:0;"
                                  title="Vaqtincha parol yaratish va Telegram ga yuborish">
                                  <i class="lni lni-shield"></i>
                                </button>
                              </form>
                          @endif
                          @if (auth()->id() !== $user->id && auth()->user()->canManage($user))
                            @if($user->isCurrentlyBlocked())
                              <form action="{{ route('user.unblock', $user) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="text-success me-2" style="background:none;border:none;padding:0;cursor:pointer;"
                                  title="Blokdarn chiqarish">
                                  <i class="lni lni-unlock"></i>
                                </button>
                              </form>
                            @else
                              <button type="button" class="text-danger me-2" style="background:none;border:none;padding:0;cursor:pointer;"
                                title="Bloklash"
                                onclick="openBlockModal('{{ $user->name }}', '{{ route('user.block', $user) }}')">
                                <i class="lni lni-lock"></i>
                              </button>
                            @endif
                          @endif
                          @if (auth()->id() !== $user->id && auth()->user()->canManage($user))
                            <form action="{{ route('user.destroy', $user->id) }}" method="POST" style="display:inline;" data-confirm="Foydalanuvchini o'chirishni xohlaysizmi?" data-confirm-title="Foydalanuvchini o'chirish" data-confirm-variant="danger" data-confirm-ok="O'chirish">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="text-danger" style="background:none;border:none;padding:0;" title="O'chirish">
                                <i class="lni lni-trash-can"></i>
                              </button>
                            </form>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="9"><p>Hozircha foydalanuvchilar yo'q.</p></td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @if($users->hasPages())
              <div class="p-3">
                {{ $users->links() }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
{{-- Telegram xabar yuborish modali --}}
<div id="telegramModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #0088cc, #005f8f); color: white;">
        <h5 class="modal-title">
          <i class="fa-brands fa-telegram me-2"></i> Telegram xabar
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="telegramForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-3">
            <strong>Kim uchun:</strong> <span id="telegramRecipient"></span>
          </p>
          <div class="mb-3">
            <label for="telegramMessage" class="form-label">Xabar matni</label>
            <textarea name="message" id="telegramMessage" class="form-control" rows="5"
              placeholder="Xabaringizni yozing..." required maxlength="4000"></textarea>
            <small class="text-muted">Maksimal 4000 belgi</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-brands fa-telegram me-1"></i> Yuborish
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Bloklash qoidalari va mezonlari modali --}}
<div id="rulesGuideModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.18);">
      <div class="modal-header" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; padding: 18px 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(59, 130, 246, 0.2); display: flex; align-items: center; justify-content: center; color: #60a5fa; font-size: 1.2rem;">
            <i class="fa-solid fa-scale-balanced"></i>
          </div>
          <div>
            <h5 class="modal-title" style="color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0;">Sayt bloklash mezonlari & intizomiy choralar</h5>
            <p style="color: #94a3b8; font-size: 0.82rem; margin: 2px 0 0 0;">Admin va moderatorlar uchun jazo choralari qo'llanmasi</p>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Yopish"></button>
      </div>
      <div class="modal-body" style="padding: 24px; background: #f8fafc;">
        
        <div style="margin-bottom: 20px; padding: 14px 18px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;">
          <div style="font-weight: 700; color: #1e40af; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-clock"></i> Saytda mavjud bloklash muddatlari:
          </div>
          <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.82rem; padding: 6px 12px; border-radius: 8px;">1 soat</span>
            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.82rem; padding: 6px 12px; border-radius: 8px;">1 kun</span>
            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.82rem; padding: 6px 12px; border-radius: 8px;">1 hafta</span>
            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.82rem; padding: 6px 12px; border-radius: 8px;">1 oy</span>
            <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 0.82rem; padding: 6px 12px; border-radius: 8px; font-weight: 700;">Butun umr</span>
          </div>
        </div>

        <div class="table-responsive" style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
          <table class="table" style="margin: 0; font-size: 0.88rem; vertical-align: middle;">
            <thead>
              <tr style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 12px 14px; font-weight: 700; color: #1e293b;">Qoidabuzarlik turi</th>
                <th style="padding: 12px 14px; font-weight: 700; color: #1e293b; min-width: 130px;">1-marta</th>
                <th style="padding: 12px 14px; font-weight: 700; color: #1e293b; min-width: 130px;">Takrorlanganda</th>
                <th style="padding: 12px 14px; font-weight: 700; color: #1e293b; min-width: 130px;">Og'ir holatda</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding: 12px 14px; font-weight: 600; color: #334155;">
                  💬 So'kinish, haqorat va janjal <br><small class="text-muted">Chat va izohlarda noo'rin so'zlar</small>
                </td>
                <td style="padding: 12px 14px;"><span class="badge bg-warning text-dark">1 soat</span></td>
                <td style="padding: 12px 14px;"><span class="badge" style="background: #fdba74; color: #7c2d12;">1 kun</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 hafta</span></td>
              </tr>
              <tr style="background: #f8fafc;">
                <td style="padding: 12px 14px; font-weight: 600; color: #334155;">
                  🚫 Spam, reklama va behayo kontent <br><small class="text-muted">Noo'rin linklar yoki reklama tarqatish</small>
                </td>
                <td style="padding: 12px 14px;"><span class="badge bg-warning text-dark">1 soat</span> <small class="d-block text-muted">+xabar o'chiriladi</small></td>
                <td style="padding: 12px 14px;"><span class="badge" style="background: #fdba74; color: #7c2d12;">1 kun</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 oy</span></td>
              </tr>
              <tr>
                <td style="padding: 12px 14px; font-weight: 600; color: #334155;">
                  🗣️ Ustozlar yoki adminlarni kamsitish <br><small class="text-muted">Xodimlarga nisbatan hurmatsizlik</small>
                </td>
                <td style="padding: 12px 14px;"><span class="badge" style="background: #fdba74; color: #7c2d12;">1 kun</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 hafta</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 oy</span></td>
              </tr>
              <tr style="background: #f8fafc;">
                <td style="padding: 12px 14px; font-weight: 600; color: #334155;">
                  📢 Yolg'on (feyk) ma'lumot yoki tuhmat <br><small class="text-muted">Saytda yolg'on e'lonlar tarqatish</small>
                </td>
                <td style="padding: 12px 14px;"><span class="badge bg-warning text-dark">1 soat</span> <small class="d-block text-muted">+post o'chiriladi</small></td>
                <td style="padding: 12px 14px;"><span class="badge" style="background: #fdba74; color: #7c2d12;">1 kun</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 hafta</span></td>
              </tr>
              <tr>
                <td style="padding: 12px 14px; font-weight: 600; color: #334155;">
                  📝 Imtihonlarda g'irromlik yoki testni buzish <br><small class="text-muted">Javoblarni ko'chirish/tarqatish</small>
                </td>
                <td style="padding: 12px 14px;"><span class="badge" style="background: #fdba74; color: #7c2d12;">1 kun</span> <small class="d-block text-muted">+ball bekor</small></td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 hafta</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 oy</span></td>
              </tr>
              <tr style="background: #f8fafc;">
                <td style="padding: 12px 14px; font-weight: 600; color: #334155;">
                  🔒 Boshqaning profiliga ruxsatsiz kirish <br><small class="text-muted">Parol o'g'irlash yoki hisobni egallash</small>
                </td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 hafta</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 oy</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-dark text-white font-monospace">Butun umr</span></td>
              </tr>
              <tr>
                <td style="padding: 12px 14px; font-weight: 600; color: #334155;">
                  🛡️ Xavfsizlikka hujum yoki virus tarqatish <br><small class="text-muted">Tizimni buzishga qasddan urinish</small>
                </td>
                <td style="padding: 12px 14px;"><span class="badge bg-danger">1 oy</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-dark text-white font-monospace">Butun umr</span></td>
                <td style="padding: 12px 14px;"><span class="badge bg-dark text-white font-monospace">Butun umr (IP)</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div style="margin-top: 18px; padding: 12px 16px; background: #fefce8; border: 1px solid #fef08a; border-radius: 10px; display: flex; align-items: flex-start; gap: 10px;">
          <span style="font-size: 1.1rem; color: #ca8a04;">💡</span>
          <p style="color: #713f12; font-size: 0.84rem; line-height: 1.45; margin: 0;">
            <strong>Eslatma:</strong> Foydalanuvchini bloklashda sababni (reason) aniq yozing. Foydalanuvchi tizimga kirishga harakat qilganda shu sabab va blok muddati uning ekranida ko'rsatiladi.
          </p>
        </div>

      </div>
      <div class="modal-footer" style="padding: 14px 24px; background: #f1f5f9;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">Yopish</button>
      </div>
    </div>
  </div>
</div>

<script>
function openTelegramModal(userName, actionUrl) {
  document.getElementById('telegramRecipient').textContent = userName;
  document.getElementById('telegramForm').action = actionUrl;
  document.getElementById('telegramMessage').value = '';
  var modal = new bootstrap.Modal(document.getElementById('telegramModal'));
  modal.show();
}

function handleStatusChange(select, userName, blockUrl, unblockUrl) {
  var value = select.value;
  if (value === 'block') {
    openBlockModal(userName, blockUrl);
  }
  // active tanlanganda hech narsa qilmaymiz — dropdown form submit bo'lmaydi
}

</script>

@endsection
