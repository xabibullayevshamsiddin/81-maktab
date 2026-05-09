<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>429 - Juda ko'p so'rovlar | {{ config('app.name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --info: #06b6d4;
            --info-glow: rgba(6, 182, 212, 0.4);
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

        .rate-limit-card {
            text-align: center;
            padding: 50px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(6, 182, 212, 0.2);
            border-radius: 40px;
            max-width: 600px;
            width: 90%;
            animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            70% { transform: scale(1.05); }
            100% { transform: scale(1); opacity: 1; }
        }

        .error-code {
            font-size: 100px;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--info);
            text-shadow: 0 0 20px var(--info-glow);
        }

        .traffic-icon {
            font-size: 70px;
            color: var(--info);
            margin-bottom: 25px;
            animation: pulseTraffic 1.5s infinite;
        }

        @keyframes pulseTraffic {
            0% { opacity: 0.5; transform: scale(0.9); }
            50% { opacity: 1; transform: scale(1.1); }
            100% { opacity: 0.5; transform: scale(0.9); }
        }

        h1 { font-size: 28px; margin-bottom: 15px; }
        p { color: #94a3b8; margin-bottom: 35px; line-height: 1.6; }

        .btn-bomba {
            padding: 16px 35px;
            background: rgba(6, 182, 212, 0.1);
            border: 1px solid var(--info);
            color: #fff;
            text-decoration: none;
            border-radius: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-bomba:hover {
            background: var(--info);
            box-shadow: 0 10px 25px var(--info-glow);
            transform: translateY(-3px);
            color: #000;
        }
    </style>
</head>
<body>
    <div class="rate-limit-card">
        <div class="traffic-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="error-code">429</div>
        <h1>Juda ko'p so'rovlar</h1>
        <p>Siz tizimga juda qisqa vaqt ichida ko'plab so'rovlar yubordingiz. Iltimos, biroz kutib turing va yana bir bor urinib ko'ring.</p>
        
        <a href="javascript:location.reload()" class="btn-bomba">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            Birozdan so'ng urinish
        </a>
    </div>
</body>
</html>
