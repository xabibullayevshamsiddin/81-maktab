@php
    $user = $authUser ?? auth()->user();
@endphp

@if($user->is_parent)
    {{-- ═══ OTA-ONA PROFILI ═══ --}}
    <div class="profile-card family-link-card">
        <div class="profile-card-header">
            <h3 class="profile-card-title">
                <i class="fa-solid fa-family"></i>
                Farzandlarimni bog'lash
            </h3>
            <span class="profile-card-badge">
                {{ $user->linkedStudents()->count() }} / {{ $user->familyLinkLimit() }}
            </span>
        </div>

        @if($user->family_link_code)
            <div class="family-link-code-box">
                <p class="family-link-code-label">Sizning bog'lanish kodingiz:</p>
                <div class="family-link-code">{{ $user->family_link_code }}</div>
                <p class="family-link-code-hint">Ushbu kodni farzandingizga bering — u profil bo'limida kiritsin.</p>
                <form method="POST" action="{{ route('profile.family.generate-code') }}" style="margin-top: 10px;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Eski kod bekor bo\'ladi. Yangisini yaratishga ishonchingiz komilmi?')">
                        <i class="fa-solid fa-rotate"></i> Qayta generatsiya qilish
                    </button>
                </form>
            </div>
        @else
            <div class="family-link-empty">
                <p>Hali bog'lanish kodi yaratilmagan.</p>
                <form method="POST" action="{{ route('profile.family.generate-code') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm">
                        <i class="fa-solid fa-plus"></i> Kod yaratish
                    </button>
                </form>
            </div>
        @endif

        @if($user->linkedStudents->count())
            <div class="family-link-list">
                <p class="family-link-list-title">Bog'langan farzandlar:</p>
                @foreach($user->linkedStudents as $student)
                    <div class="family-link-item">
                        <div class="family-link-item-info">
                            <span class="family-link-item-name">{{ $student->name }}</span>
                            @if($student->grade)
                                <span class="family-link-item-grade">{{ $student->grade }}-sinf</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('profile.family.unlink', $student->id) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="family-link-unlink-btn" onclick="return confirm('Bog\'lanishni uzishga ishonchingiz komilmi?')">
                                <i class="fa-solid fa-xmark"></i> Uzish
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@else
    {{-- ═══ O'QUVCHI PROFILI ═══ --}}
    <div class="profile-card family-link-card">
        <div class="profile-card-header">
            <h3 class="profile-card-title">
                <i class="fa-solid fa-family"></i>
                Ota-onamni bog'lash
            </h3>
        </div>

        <form method="POST" action="{{ route('profile.family.link') }}" class="family-link-form">
            @csrf
            <div class="family-link-input-row">
                <input
                    type="text"
                    name="code"
                    class="form-control family-link-input"
                    placeholder="Ota-ona kodi (8 ta harf)"
                    maxlength="8"
                    required
                    style="text-transform: uppercase; letter-spacing: 2px;"
                >
                <button type="submit" class="btn btn-sm">
                    <i class="fa-solid fa-link"></i> Bog'lash
                </button>
            </div>
        </form>

        @if($user->linkedParents->count())
            <div class="family-link-list">
                <p class="family-link-list-title">Bog'langan ota-onalar:</p>
                @foreach($user->linkedParents as $parent)
                    <div class="family-link-item">
                        <div class="family-link-item-info">
                            <span class="family-link-item-name">{{ $parent->name }}</span>
                            <span class="family-link-item-date">
                                {{ \Carbon\Carbon::parse($parent->pivot->linked_at)->format('d.m.Y') }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('profile.family.unlink', $parent->id) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="family-link-unlink-btn" onclick="return confirm('Bog\'lanishni uzishga ishonchingiz komilmi?')">
                                <i class="fa-solid fa-xmark"></i> Uzish
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif

<style>
.family-link-card {
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
}
.family-link-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.family-link-card-title {
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.family-link-badge {
    background: rgba(99, 102, 241, 0.1);
    color: #818cf8;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}
.family-link-code-box {
    background: rgba(99, 102, 241, 0.08);
    border: 1px dashed rgba(99, 102, 241, 0.3);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    margin-bottom: 16px;
}
.family-link-code-label {
    font-size: 13px;
    color: var(--muted, #64748b);
    margin-bottom: 8px;
}
.family-link-code {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: 4px;
    color: var(--primary, #3b82f6);
    font-family: 'Orbitron', monospace;
    margin-bottom: 8px;
    user-select: all;
}
.family-link-code-hint {
    font-size: 12px;
    color: var(--muted, #64748b);
}
.family-link-empty {
    text-align: center;
    padding: 16px;
    color: var(--muted, #64748b);
    font-size: 14px;
}
.family-link-form {
    margin-bottom: 16px;
}
.family-link-input-row {
    display: flex;
    gap: 10px;
    align-items: center;
}
.family-link-input {
    flex: 1;
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 3px;
}
.family-link-list {
    border-top: 1px solid var(--border, rgba(148, 163, 184, 0.2));
    padding-top: 12px;
}
.family-link-list-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--muted, #64748b);
    margin-bottom: 10px;
}
.family-link-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.04);
    margin-bottom: 6px;
}
.family-link-item-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.family-link-item-name {
    font-weight: 600;
    font-size: 14px;
}
.family-link-item-grade {
    font-size: 12px;
    color: var(--muted, #64748b);
    background: rgba(148, 163, 184, 0.1);
    padding: 2px 8px;
    border-radius: 999px;
}
.family-link-item-date {
    font-size: 12px;
    color: var(--muted, #64748b);
}
.family-link-unlink-btn {
    background: none;
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.family-link-unlink-btn:hover {
    background: rgba(239, 68, 68, 0.1);
}
</style>
