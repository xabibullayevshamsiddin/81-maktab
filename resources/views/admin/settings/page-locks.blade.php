@extends('admin.layouts.main')

@section('title', 'Sahifalar boshqaruvi | Admin Panel')

@section('content')
<div class="row">
  <div class="col-lg-9">

    @if(session('success'))
      <div class="alert-box success-alert mb-20">
        <div class="alert"><p>{{ session('success') }}</p></div>
      </div>
    @endif

    {{-- Yangi blok qo'shish --}}
    <div class="card-style mb-30">
      <h6 class="mb-10">Sahifani bloklash</h6>
      <p class="text-sm" style="color:#64748b;margin-bottom:20px;">
        Tanlangan sahifa belgilangan vaqt davomida oddiy foydalanuvchilarga yopiladi.
        Admin, editor va moderatorlar sahifaga kira oladi.
      </p>

      <form action="{{ route('admin.settings.page-locks.lock') }}" method="POST">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr 2fr auto;gap:12px;align-items:end;">
          <div class="input-style-1">
            <label>Sahifa</label>
            <select name="page" class="form-control" style="padding:10px 12px;border-radius:8px;" required>
              <option value="">— tanlang —</option>
              @foreach($pages as $key => $label)
                <option value="{{ $key }}" {{ old('page') === $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('page') <p class="text-danger" style="font-size:12px;">{{ $message }}</p> @enderror
          </div>

          <div class="input-style-1">
            <label>Muddat (daqiqa)</label>
            <input type="number" name="duration" min="1" max="10080"
              value="{{ old('duration', 30) }}"
              placeholder="30"
              style="padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;width:100%;" required />
            @error('duration') <p class="text-danger" style="font-size:12px;">{{ $message }}</p> @enderror
          </div>

          <div class="input-style-1">
            <label>Sabab / xabar (ixtiyoriy)</label>
            <input type="text" name="reason" maxlength="300"
              value="{{ old('reason') }}"
              placeholder="Masalan: Texnik ishlar olib borilmoqda"
              style="padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;width:100%;" />
            @error('reason') <p class="text-danger" style="font-size:12px;">{{ $message }}</p> @enderror
          </div>

          <div>
            <button type="submit" class="main-btn danger-btn btn-hover" style="white-space:nowrap;">
              <i class="mdi mdi-lock me-1"></i> Bloklash
            </button>
          </div>
        </div>

        {{-- Tezkor muddat tugmalari --}}
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
          @foreach([15 => '15 daqiqa', 30 => '30 daqiqa', 60 => '1 soat', 180 => '3 soat', 1440 => '1 kun'] as $min => $label)
            <button type="button" class="main-btn light-btn btn-hover"
              style="font-size:12px;padding:4px 12px;"
              onclick="document.querySelector('[name=duration]').value='{{ $min }}'">
              {{ $label }}
            </button>
          @endforeach
        </div>
      </form>
    </div>

    {{-- Hozirgi bloklangan sahifalar --}}
    <div class="card-style mb-30">
      <h6 class="mb-20">Hozirgi holat</h6>

      <table class="table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="border-bottom:2px solid #e2e8f0;">
            <th style="padding:10px 12px;text-align:left;font-size:13px;color:#64748b;">Sahifa</th>
            <th style="padding:10px 12px;text-align:left;font-size:13px;color:#64748b;">Holat</th>
            <th style="padding:10px 12px;text-align:left;font-size:13px;color:#64748b;">Blok tugaydi</th>
            <th style="padding:10px 12px;text-align:left;font-size:13px;color:#64748b;">Sabab</th>
            <th style="padding:10px 12px;text-align:left;font-size:13px;color:#64748b;">Kim blokladi</th>
            <th style="padding:10px 12px;text-align:left;font-size:13px;color:#64748b;">Amal</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pages as $key => $label)
            @php
              $lock = $locks[$key] ?? null;
              $isLocked = $lock && now()->lt($lock['locked_until']);
            @endphp
            <tr style="border-bottom:1px solid #f1f5f9;">
              <td style="padding:12px;font-weight:500;">{{ $label }}</td>
              <td style="padding:12px;">
                @if($isLocked)
                  <span style="display:inline-flex;align-items:center;gap:4px;background:#fef2f2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                    <i class="mdi mdi-lock"></i> Bloklangan
                  </span>
                @else
                  <span style="display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                    <i class="mdi mdi-lock-open-variant"></i> Ochiq
                  </span>
                @endif
              </td>
              <td style="padding:12px;font-size:13px;color:#64748b;">
                @if($isLocked)
                  {{ \Carbon\Carbon::parse($lock['locked_until'])->format('d.m.Y H:i') }}
                  <br>
                  <span style="font-size:11px;color:#94a3b8;">
                    ({{ \Carbon\Carbon::parse($lock['locked_until'])->diffForHumans() }})
                  </span>
                @else
                  —
                @endif
              </td>
              <td style="padding:12px;font-size:13px;color:#64748b;max-width:200px;">
                {{ $isLocked && $lock['reason'] ? $lock['reason'] : '—' }}
              </td>
              <td style="padding:12px;font-size:13px;color:#64748b;">
                {{ $isLocked && isset($lock['locked_by_name']) ? $lock['locked_by_name'] : '—' }}
              </td>
              <td style="padding:12px;">
                @if($isLocked)
                  <form action="{{ route('admin.settings.page-locks.unlock') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="page" value="{{ $key }}" />
                    <button type="submit" class="main-btn success-btn btn-hover" style="font-size:12px;padding:5px 14px;">
                      <i class="mdi mdi-lock-open-variant me-1"></i> Ochish
                    </button>
                  </form>
                @else
                  <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>

  {{-- Yon panel: yordam --}}
  <div class="col-lg-3">
    <div class="card-style mb-30" style="background:#fffbeb;border:1px solid #fde68a;">
      <h6 class="mb-10" style="color:#92400e;"><i class="mdi mdi-information-outline me-1"></i> Qanday ishlaydi?</h6>
      <ul style="font-size:13px;color:#78350f;line-height:1.8;padding-left:16px;">
        <li>Bloklangan sahifaga oddiy foydalanuvchilar kira olmaydi</li>
        <li>Admin, editor va moderatorlar sahifani ko'ra oladi</li>
        <li>Muddat tugagach sahifa avtomatik ochiladi</li>
        <li>Istalgan vaqt "Ochish" tugmasi bilan blokni olib tashlash mumkin</li>
      </ul>
    </div>

    <div class="card-style mb-30" style="background:#eff6ff;border:1px solid #bfdbfe;">
      <h6 class="mb-10" style="color:#1e40af;"><i class="mdi mdi-clock-outline me-1"></i> Tezkor muddatlar</h6>
      <ul style="font-size:13px;color:#1e3a8a;line-height:2;padding-left:0;list-style:none;">
        <li>15 daqiqa — qisqa texnik ish</li>
        <li>30 daqiqa — standart</li>
        <li>1 soat — uzoqroq ish</li>
        <li>3 soat — katta yangilanish</li>
        <li>1 kun — to'liq texnik ish</li>
      </ul>
    </div>
  </div>
</div>
@endsection
