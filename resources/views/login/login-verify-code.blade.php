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
            :root[data-theme='dark'] .verify-info {
                background: rgba(59, 130, 246, 0.15);
                border-color: rgba(59, 130, 246, 0.3);
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
                    <div style="display:flex;align-items:flex-start;gap:10px;margin:0 0 16px;padding:12px 14px;border-radius:12px;border:1px solid rgba(22,163,74,0.18);background:rgba(242,252,246,0.95);color:#166534;font-size:14px;line-height:1.5;">
                        <i class="fa-solid fa-circle-check" style="margin-top:2px;color:#16a34a;"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ route('login.verify.check') }}" method="POST" class="signin-form">
                    @csrf
                    <div class="code-input-group">
                        <input type="text" class="code-input" name="code" maxlength="1" inputmode="numeric" pattern="[0-9]" required autofocus>
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
                        <input type="text" class="code-input" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
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
                    <button type="submit" class="resend-btn">
                        <i class="fa-solid fa-rotate"></i> Kodni qayta yuborish
                    </button>
                </form>

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
        const form = document.querySelector('form');
        
        inputs.forEach((input, index) => {
            // Faqat raqamlarni qabul qilish
            input.addEventListener('input', function(e) {
                const value = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = value;
                
                if (value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                
                // Barcha kod kiritilgandan keyin avtomatik yuborish
                if (index === inputs.length - 1 && value) {
                    const code = Array.from(inputs).map(i => i.value).join('');
                    if (code.length === 6) {
                        // Hidden inputga kodni qo'shish
                        let hiddenInput = document.querySelector('input[name="code"]');
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'code';
                            form.appendChild(hiddenInput);
                        }
                        hiddenInput.value = code;
                        form.submit();
                    }
                }
            });
            
            // Orqaga qaytish
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Paste qo'llab-quvvatlash
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                
                pastedData.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                    }
                });
                
                if (pastedData.length > 0) {
                    const nextIndex = Math.min(pastedData.length, inputs.length - 1);
                    inputs[nextIndex].focus();
                    
                    if (pastedData.length === 6) {
                        let hiddenInput = document.querySelector('input[name="code"]');
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'code';
                            form.appendChild(hiddenInput);
                        }
                        hiddenInput.value = pastedData;
                        form.submit();
                    }
                }
            });
        });
    });
    </script>
</x-layouts.main>
