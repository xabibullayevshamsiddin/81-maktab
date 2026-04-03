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
                          <span class="badge 
                            @if($user->role === 'super_admin') bg-dark
                            @elseif($user->role === 'admin') bg-danger
                            @elseif($user->role === 'editor') bg-warning
                            @elseif($user->role === 'moderator') bg-info
                            @else bg-secondary
                            @endif">
                            {{ $user->role_label }}
                          </span>
                        @endif
                      </td>
                      <td>
                        @if (auth()->id() !== $user->id && auth()->user()->canManage($user))
                          <form action="{{ route('user.update', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                            <select name="is_active" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto;">
                              <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                              <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Block</option>
                            </select>
                          </form>
                        @else
                          <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $user->is_active ? 'Active' : 'Block' }}
                          </span>
                        @endif
                      </td>
                      <td><p>{{ $user->created_at?->format('Y-m-d H:i') }}</p></td>
                      <td>
                        <div class="action">
                          <a href="mailto:{{ $user->email }}" class="text-primary me-2" title="Email yuborish">
                            <i class="lni lni-envelope"></i>
                          </a>
                          @if (auth()->id() !== $user->id && auth()->user()->canManage($user))
                            <form action="{{ route('user.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Foydalanuvchini o\'chirishni xohlaysizmi?');">
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
                      <td colspan="8"><p>Hozircha foydalanuvchilar yo'q.</p></td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
