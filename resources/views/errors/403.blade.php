<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Kirish taqiqlangan | {{ config('app.name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --danger: #ef4444;
            --danger-glow: rgba(239, 68, 68, 0.4);
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

        .mesh-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 50% 50%, rgba(239, 68, 68, 0.1) 0%, transparent 60%);
        }

        .error-container {
            text-align: center;
            z-index: 1;
            padding: 50px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 68, 68, 0.2);
            border-radius: 40px;
            max-width: 600px;
            width: 90%;
            animation: shakeContainer 0.8s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shakeContainer {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .error-code {
            font-size: clamp(80px, 20vw, 150px);
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--danger);
            text-shadow: 0 0 30px var(--danger-glow);
        }

        .lock-icon {
            font-size: 60px;
            margin-bottom: 30px;
            color: #f87171;
            animation: pulseLock 2s infinite;
        }

        @keyframes pulseLock {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        h1 { font-size: 28px; margin-bottom: 15px; }
        p { color: #94a3b8; margin-bottom: 35px; line-height: 1.6; }

        .btn-bomba {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 32px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            color: #fff;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-bomba:hover {
            background: var(--danger);
            box-shadow: 0 10px 20px var(--danger-glow);
            transform: translateY(-3px);
        }

        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--danger);
            box-shadow: 0 0 15px var(--danger);
            opacity: 0.3;
            animation: scan 3s infinite linear;
            pointer-events: none;
        }

        @keyframes scan {
            0% { top: 0; }
            100% { top: 100%; }
        }
    </style>
</head>
<body>
    <div class="mesh-bg"></div>
    <div class="error-container">
        <div class="scan-line"></div>
        <div class="lock-icon">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="error-code">403</div>
        <h1>Kirish taqiqlangan</h1>
        <p>Sizda ushbu sahifani ko'rish uchun yetarli huquqlar mavjud emas. Iltimos, tizim administratoriga murojaat qiling yoki o'z hisobingizga kiring.</p>
        
        <a href="{{ url('/') }}" class="btn-bomba">
            <i class="fa-solid fa-shield-halved"></i>
            Xavfsiz joyga qaytish
        </a>
    </div>
</body>
</html>
