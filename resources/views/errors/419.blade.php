<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 - Sessiya muddati tugagan | {{ config('app.name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --secondary: #10b981;
            --secondary-glow: rgba(16, 185, 129, 0.4);
            --bg-dark: #0f172a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .expired-container {
            text-align: center;
            padding: 50px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 40px;
            max-width: 600px;
            width: 90%;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .error-code {
            font-size: 100px;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--secondary);
            text-shadow: 0 0 20px var(--secondary-glow);
        }

        .clock-icon {
            font-size: 70px;
            color: var(--secondary);
            margin-bottom: 25px;
            animation: tick 2s infinite steps(2);
        }

        @keyframes tick {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(15deg); }
            100% { transform: rotate(0deg); }
        }

        h1 { font-size: 28px; margin-bottom: 15px; }
        p { color: #94a3b8; margin-bottom: 35px; line-height: 1.6; }

        .btn-bomba {
            padding: 16px 35px;
            background: var(--secondary);
            color: #fff;
            text-decoration: none;
            border-radius: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px var(--secondary-glow);
        }

        .btn-bomba:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px var(--secondary-glow);
            filter: brightness(1.1);
        }
    </style>
</head>
<body>
    <div class="expired-container">
        <div class="clock-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 22h14"></path>
                <path d="M5 2h14"></path>
                <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"></path>
                <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path>
            </svg>
        </div>
        <div class="error-code">419</div>
        <h1>Sessiya muddati tugadi</h1>
        <p>Xavfsizlik nuqtai nazaridan sessiyangiz muddati tugagan bo'lishi mumkin. Iltimos, sahifani yangilab, ma'lumotlarni qayta yuboring.</p>
        
        <a href="javascript:location.reload()" class="btn-bomba">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 4v6h-6"></path>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
            Sahifani yangilash
        </a>
    </div>
</body>
</html>
