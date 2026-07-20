<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sahifa vaqtincha yopiq | {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f59e0b;
            --primary-glow: rgba(245, 158, 11, 0.35);
            --bg-dark: #0f172a;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(circle at 20% 30%, rgba(245, 158, 11, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(239, 68, 68, 0.10) 0%, transparent 50%);
            animation: meshMove 20s infinite alternate ease-in-out;
        }
        @keyframes meshMove {
            0% { transform: scale(1) rotate(0deg); }
            100% { transform: scale(1.2) rotate(5deg); }
        }
        .error-container {
            text-align: center;
            z-index: 1;
            padding: 48px 40px;
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            max-width: 620px;
            width: 90%;
            animation: containerFadeIn 0.8s cubic-bezier(0.23,1,0.32,1) both;
        }
        @keyframes containerFadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.9); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .lock-icon {
            font-size: 72px;
            margin-bottom: 16px;
            animation: float 3s infinite ease-in-out;
            display: block;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-12px); }
        }
        .badge-503 {
            display: inline-block;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--primary);
            background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.3);
            border-radius: 20px;
            padding: 4px 16px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #f8fafc;
        }
        .subtitle {
            font-size: 15px;
            color: #94a3b8;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .reason-box {
            background: rgba(245,158,11,0.08);
            border: 1px solid rgba(245,158,11,0.25);
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #fde68a;
            line-height: 1.6;
            text-align: left;
        }
        .reason-box strong {
            display: block;
            margin-bottom: 4px;
            color: #fbbf24;
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .time-box {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 10px 20px;
            font-size: 14px;
            color: #cbd5e1;
            margin-bottom: 32px;
        }
        .time-box span {
            color: #f8fafc;
            font-weight: 600;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.23,1,0.32,1);
            box-shadow: 0 10px 20px var(--primary-glow);
        }
        .btn-home:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px var(--primary-glow);
            filter: brightness(1.1);
        }
        .particle {
            position: absolute;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="mesh-bg"></div>

    <div class="error-container">
        <span class="lock-icon">🔒</span>
        <div class="badge-503">VAQTINCHA YOPIQ</div>

        <h1>{{ $page_name ?? 'Bu sahifa' }} vaqtincha yopiq</h1>
        <p class="subtitle">
            Ushbu bo'limda hozirda texnik ishlar olib borilmoqda yoki yangilanishlar amalga oshirilmoqda.
            Tez orada qayta ishga tushiriladi.
        </p>

        @if(!empty($reason))
            <div class="reason-box">
                <strong>📋 Sabab</strong>
                {{ $reason }}
            </div>
        @endif

        @if(!empty($locked_until))
            <div class="time-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Taxminan
                <span>{{ \Carbon\Carbon::parse($locked_until)->format('H:i') }}</span>
                gacha yopiq
                &nbsp;·&nbsp;
                <span>{{ \Carbon\Carbon::parse($locked_until)->diffForHumans() }}</span>
            </div>
        @endif

        <a href="{{ url('/') }}" class="btn-home">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Bosh sahifaga qaytish
        </a>
    </div>

    <script>
        for (let i = 0; i < 18; i++) {
            let p = document.createElement('div');
            p.className = 'particle';
            let size = Math.random() * 5 + 2;
            p.style.cssText = `width:${size}px;height:${size}px;left:${Math.random()*100}vw;top:${Math.random()*100}vh;opacity:${Math.random()*0.4}`;
            document.body.appendChild(p);
            p.animate([
                { transform: 'translate(0,0)' },
                { transform: `translate(${(Math.random()-.5)*80}px,${(Math.random()-.5)*80}px)` }
            ], { duration: Math.random()*8000+5000, iterations: Infinity, direction: 'alternate', easing: 'linear' });
        }
    </script>
</body>
</html>
