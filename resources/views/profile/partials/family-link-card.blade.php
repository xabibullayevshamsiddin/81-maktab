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
                <form method="POST" action="{{ route('profile.family.generate-code') }}" style="margin-top: 10px;" id="regen-code-form">
                    @csrf
                    <button type="button" class="btn btn-sm btn-outline" onclick="openFamilyModal('regen')">
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
                        <button type="button" class="family-link-unlink-btn" onclick="openFamilyModal('unlink', '{{ $student->name }}', '{{ route('profile.family.unlink', $student->id) }}')">
                            <i class="fa-solid fa-xmark"></i> Uzish
                        </button>
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
                        <button type="button" class="family-link-unlink-btn" onclick="openFamilyModal('unlink', '{{ $parent->name }}', '{{ route('profile.family.unlink', $parent->id) }}')">
                            <i class="fa-solid fa-xmark"></i> Uzish
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif

{{-- ═══ CONFIRM MODAL ═══ --}}
<div id="family-confirm-modal" class="fl-modal-overlay" style="display:none;" onclick="closeFamilyModal(event)">
    <div class="fl-modal" onclick="event.stopPropagation()">
        <div class="fl-modal-icon" id="fl-modal-icon">
            <i class="fa-solid fa-link-slash"></i>
        </div>
        <h3 class="fl-modal-title" id="fl-modal-title">Bog'lanishni uzish</h3>
        <p class="fl-modal-text" id="fl-modal-text">Haqiqatan ham bog'lanishni uzmoqchimisiz?</p>
        <div class="fl-modal-actions">
            <button type="button" class="fl-modal-btn fl-modal-btn-cancel" onclick="closeFamilyModal()">
                <i class="fa-solid fa-xmark"></i> Bekor qilish
            </button>
            <form method="POST" id="fl-modal-form" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="fl-modal-btn fl-modal-btn-danger" id="fl-modal-submit">
                    <i class="fa-solid fa-check"></i> Ha, uzish
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* ═══ Family Link Card ═══ */
.family-link-card {
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
}
.profile-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.profile-card-title {
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.profile-card-badge {
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

/* ═══ Confirm Modal ═══ */
.fl-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: flFadeIn 0.2s ease;
}
.fl-modal {
    background: linear-gradient(145deg, #1e1b2e, #16132b);
    border: 1px solid rgba(129, 140, 248, 0.2);
    border-radius: 20px;
    padding: 32px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(129, 140, 248, 0.1);
    animation: flSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.fl-modal-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
    color: #ef4444;
    transition: transform 0.3s ease;
}
.fl-modal-icon.fl-icon-regen {
    background: rgba(99, 102, 241, 0.15);
    color: #818cf8;
}
.fl-modal-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #e2e8f0;
}
.fl-modal-text {
    font-size: 14px;
    color: #94a3b8;
    margin-bottom: 24px;
    line-height: 1.6;
}
.fl-modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}
.fl-modal-btn {
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}
.fl-modal-btn-cancel {
    background: rgba(148, 163, 184, 0.1);
    color: #94a3b8;
    border: 1px solid rgba(148, 163, 184, 0.2);
}
.fl-modal-btn-cancel:hover {
    background: rgba(148, 163, 184, 0.2);
    color: #e2e8f0;
}
.fl-modal-btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}
.fl-modal-btn-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
}
.fl-modal-btn-danger:active {
    transform: translateY(0);
}
.fl-modal-btn-regen {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
}
.fl-modal-btn-regen:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

@keyframes flFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes flSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<script>
(function() {
    var modal = document.getElementById('family-confirm-modal');
    if (!modal) return;

    window.openFamilyModal = function(type, name, url) {
        var icon = document.getElementById('fl-modal-icon');
        var title = document.getElementById('fl-modal-title');
        var text = document.getElementById('fl-modal-text');
        var form = document.getElementById('fl-modal-form');
        var submitBtn = document.getElementById('fl-modal-submit');

        if (type === 'unlink') {
            icon.innerHTML = '<i class="fa-solid fa-link-slash"></i>';
            icon.className = 'fl-modal-icon';
            title.textContent = "Bog'lanishni uzish";
            text.innerHTML = "<strong>" + name + "</strong> bilan bog'lanishni uzmoqchimisiz?<br><small style='color:#64748b;'>Bu amal bekor qilib bo'lmaydi.</small>";
            form.action = url;
            form.method = 'POST';
            // DELETE methodni qayta yozish
            var existingMethod = form.querySelector('input[name="_method"]');
            if (!existingMethod) {
                var methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
            }
            submitBtn.className = 'fl-modal-btn fl-modal-btn-danger';
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Ha, uzish';
        } else if (type === 'regen') {
            icon.innerHTML = '<i class="fa-solid fa-rotate"></i>';
            icon.className = 'fl-modal-icon fl-icon-regen';
            title.textContent = 'Kodni qayta generatsiya qilish';
            text.innerHTML = "Eski kod <strong>bekor bo'ladi</strong> va yangi kod yaratiladi.<br><small style='color:#64748b;'>Davom etasizmi?</small>";
            form.action = '{{ route("profile.family.generate-code") }}';
            form.method = 'POST';
            var existingMethod2 = form.querySelector('input[name="_method"]');
            if (existingMethod2) existingMethod2.remove();
            submitBtn.className = 'fl-modal-btn fl-modal-btn-regen';
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Ha, yangisini yaratish';
        }

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeFamilyModal = function(e) {
        if (e && e.target !== modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    // ESC tugmasi bilan yopish
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            window.closeFamilyModal();
        }
    });
})();
</script>
