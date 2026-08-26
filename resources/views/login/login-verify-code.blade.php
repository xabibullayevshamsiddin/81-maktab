<x-layouts.main :title="'Tizimga kirish - Tasdiqlash'">
    @push('page_styles')
        <style>
            .verify-hero {
                text-align: center;
                padding: 130px 1rem 48px;
                background: linear-gradient(135deg, #0a2f5e 0%, #14559b 50%, #1a6bb5 100%);
                color: #fff;
            }
            .verify-hero h1 {
                font-size: clamp(28px, 4vw, 36px);
                font-weight: 700;
                color: #fff;
                margin-bottom: 8px;
            }
            .verify-hero p {
                color: #d8e7ff;
                font-size: 1rem;
                max-width: 500px;
                margin: 0 auto;
            }
            .verify-card {
                max-width: 420px;
                margin: 2rem auto 4rem;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 1.25rem;
                padding: 2.5rem 2rem;
                text-align: center;
            }
            .verify-card-icon {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                background: linear-gradient(135deg, #6366f1, #4f46e5);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.25rem;
                font-size: 2rem;
                color: #fff;
                box-shadow: 0 8px 24px rgba(99, 102, 241, 0.25);
            }
            .verify-card h2 {
                font-size: 1.35rem;
                font-weight: 700;
                color: var(--text);
                margin-bottom: 0.5rem;
            }
            .verify-card .verify-subtitle {
                color: var(--muted);
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
                line-height: 1.6;
            }
            .code-input-group {
                display: flex;
                gap: 8px;
                justify-content: center;
                margin-bottom: 1.5rem;
            }
            .code-input {
                width: 50px;
                height: 60px;
                text-align: center;
                font-size: 1.5rem;
                font-weight: 700;
                border: 2px solid var(--border);
                border-radius: 12px;
                background: var(--bg);
                color: var(--text);
                outline: none;
                transition: all 0.2s ease;
            }
            .code-input:focus {
                border-color: #6366f1;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            }
            .verify-btn {
                display: block;
                width: 100%;
                padding: 14px 24px;
                border-radius: 999px;
                background: linear-gradient(135deg, #6366f1, #4f46e5);
                color: #fff;
                font-size: 1rem;
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
                box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
                border: none;
                margin-bottom: 1rem;
            }
            .verify-btn:hover {
                transform: translateY(-3px) scale(1.02);
                box-shadow: 0 10px 28px rgba(99, 102, 241, 0.45);
            }
            .verify-btn:active {
                transform: translateY(-1px) scale(0.98);
            }
            .resend-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 10px 16px;
                border-radius: 999px;
                background: rgba(99, 102, 241, 0.1);
                color: #6366f1;
                font-size: 0.9rem;
                font-weight: 600;
                text-decoration: none;
                border: 1px solid rgba(99, 102, 241, 0.2);
                transition: all 0.2s ease;
            }
            .resend-btn:hover {
                background: rgba(99, 102, 241, 0.2);
                border-color: rgba(99, 102, 241, 0.4);
            }
            .verify-info {
                margin-top: 1.5rem;
                padding: 1rem;
                border-radius: 12px;
                background: rgba(59, 130, 246, 0.08);
                border: 1px solid rgba(59, 130, 246, 0.2);
                text-align: left;
            }
            .verify-info p {
                margin: 0;
                font-size: 0.85rem;
                color: var(--muted);
                line-height: 1.6;
            }
            .verify-info strong {
                color: var(--text);
            }
            .verify-back {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                margin-top: 1.25rem;
                color: var(--muted);
                font-size: 0.85rem;
                text-decoration: none;
                font-weight: 600;
            }
            .verify-back:hover {
                color: var(--text);
            }
            .verify-alert-success {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                margin: 0 0 16px;
                padding: 12px 14px;
                border-radius: 12px;
                border: 1px solid rgba(22, 163, 74, 0.25);
                background: rgba(22, 163, 74, 0.1);
                color: #15803d;
                font-size: 14px;
                line-height: 1.5;
                text-align: left;
            }
            .verify-alert-success i {
                margin-top: 2px;
                color: #16a34a;
                flex-shrink: 0;
            }
            :root[data-theme='dark'] .verify-info {
                background: rgba(59, 130, 246, 0.15);
                border-color: rgba(59, 130, 246, 0.3);
            }
            :root[data-theme='dark'] .verify-alert-success {
                background: rgba(22, 163, 74, 0.18);
                border-color: rgba(22, 163, 74, 0.35);
                color: #4ade80;
            }
            :root[data-theme='dark'] .verify-alert-success i {
                color: #4ade80;
            }
        </style>
    @endpush

    <section class="verify-hero">
        <div class="container">
            <h1>Tizimga kirish</h1>
            <p>Telegramga yuborilgan 6 xonali tasdiqlash kodini kiriting</p>
        </div>
    </section>

    <main class="signin-section">
        <div class="container">
            <div class="verify-card">
                <div class="verify-card-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h2>Xavfsizlik tasdiqlashi</h2>
                <p class="verify-subtitle">Telegram botimizga yuborilgan 6 xonali kodni kiriting</p>

                @if (session('error'))
                    <div class="signin-alert" role="alert">
                        <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div class="verify-alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.verify.check') }}" method="POST" class="signin-form" id="otp-form">
                    @csrf
                    <input type="hidden" name="code" id="real-code-input" value="">
                    <div class="code-input-group">
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off" autofocus>
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    </div>
                    @error('code')
                        <p class="form-message" style="color:#b91c1c;margin-bottom:1rem;">{{ $message }}</p>
                    @enderror
                    <button class="verify-btn" type="submit">
                        <i class="fa-solid fa-check"></i> Tasdiqlash
                    </button>
                </form>

                <form action="{{ route('login.verify.resend') }}" method="POST" style="margin-bottom:1rem;">
                    @csrf
                    <button type="submit" class="resend-btn" id="login-resend-btn" disabled style="opacity:0.5;cursor:not-allowed;">
                        <i class="fa-solid fa-rotate"></i> <span id="login-resend-text">Kodni qayta yuborish</span>
                    </button>
                </form>
                <div id="login-resend-countdown" style="font-size:0.8rem;color:var(--muted);margin-bottom:1rem;"></div>

                <div class="verify-info">
                    <p>
                        <strong>Qanday ishlaydi?</strong><br>
                        1. Telegram botimizga 6 xonali kod yuborildi<br>
                        2. Kodni yuqoridagi maydonlarga kiriting<br>
                        3. "Tasdiqlash" tugmasini bosing
                    </p>
                </div>

                <a href="{{ route('login') }}" class="verify-back">
                    <i class="fa-solid fa-arrow-left"></i> Orqaga qaytish
                </a>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.code-input');
        const hiddenInput = document.getElementById('real-code-input');
        const form = document.getElementById('otp-form');

        function syncCode() {
            let code = '';
            inputs.forEach(input => {
                code += input.value;
            });
            if (hiddenInput) {
                hiddenInput.value = code;
            }
            return code;
        }

        inputs.forEach((input, index) => {
            input.addEventListener('focus', function() {
                this.select();
            });

            input.addEventListener('input', function(e) {
                let val = this.value.replace(/[^0-9]/g, '');

                if (val.length > 1) {
                    const digits = val.split('');
                    digits.forEach((digit, i) => {
                        if (inputs[index + i]) {
                            inputs[index + i].value = digit;
                        }
                    });
                    const nextIndex = Math.min(index + digits.length, inputs.length - 1);
                    inputs[nextIndex].focus();
                } else {
                    this.value = val;
                    if (val && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                }

                const currentCode = syncCode();
                if (currentCode.length === 6 && form) {
                    setTimeout(function() {
                        form.submit();
                    }, 100);
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace') {
                    if (!this.value && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                        syncCode();
                    }
                } else if (e.key === 'ArrowLeft' && index > 0) {
                    inputs[index - 1].focus();
                } else if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text') || '';
                const digits = pastedText.replace(/[^0-9]/g, '').slice(0, 6).split('');

                digits.forEach((digit, i) => {
                    if (inputs[i]) {
                        inputs[i].value = digit;
                    }
                });

                if (digits.length > 0) {
                    const targetIdx = Math.min(digits.length, inputs.length - 1);
                    inputs[targetIdx].focus();
                }

                const currentCode = syncCode();
                if (currentCode.length === 6 && form) {
                    setTimeout(function() {
                        form.submit();
                    }, 100);
                }
            });
        });

        if (form) {
            form.addEventListener('submit', function(e) {
                const code = syncCode();
                if (code.length < 6) {
                    e.preventDefault();
                }
            });
        }

        // ========== RESEND COOLDOWN (60 soniya) ==========
        var resendBtn = document.getElementById('login-resend-btn');
        var resendText = document.getElementById('login-resend-text');
        var resendCountdown = document.getElementById('login-resend-countdown');
        var COOLDOWN = 60;
        var remaining = COOLDOWN;

        function tickResend() {
            if (remaining <= 0) {
                resendBtn.disabled = false;
                resendBtn.style.opacity = '1';
                resendBtn.style.cursor = 'pointer';
                resendText.textContent = 'Kodni qayta yuborish';
                resendCountdown.textContent = '';
                return;
            }
            resendBtn.disabled = true;
            resendBtn.style.opacity = '0.5';
            resendBtn.style.cursor = 'not-allowed';
            resendText.textContent = 'Kodni qayta yuborish';
            resendCountdown.textContent = remaining + ' soniyadan keyin qayta yuborishingiz mumkin';
            remaining--;
            setTimeout(tickResend, 1000);
        }
        tickResend();
    });
    </script>
</x-layouts.main>
