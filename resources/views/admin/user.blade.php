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
            <h6 class="mb-10">Barcha foydalanuvchilar</h6>
            <p class="text-sm mb-20">Ro'yxatda bazadagi barcha userlar ko'rsatiladi.</p>
            <p class="text-sm mb-20" style="color:#64748b;">
              <i class="lni lni-shield me-1"></i>
              Qalqon tugmasi foydalanuvchiga vaqtincha parol yaratadi va Telegram ga yuboradi.
            </p>
            
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
                          @if (auth()->id() !== $user->id && auth()->user()->isSuperAdmin() && ! $user->hasRole(\App\Models\User::ROLE_USER))
                            <form action="{{ route('user.demote', $user) }}" method="POST" style="display:inline;" data-confirm="{{ $user->name }} ni oddiy foydalanuvchiga tushirasizmi? Barcha maxfiy huquqlari bekor qilinadi." data-confirm-title="Darajani tushirish" data-confirm-variant="warning" data-confirm-ok="Tushirish">
                              @csrf
                              <button type="submit" class="text-warning" style="background:none;border:none;padding:0;cursor:pointer;" title="Oddiy foydalanuvchiga tushirish">
                                <i class="lni lni-arrow-down"></i>
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
