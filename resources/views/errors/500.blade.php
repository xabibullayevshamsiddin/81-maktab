<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Ichki server xatoligi | {{ config('app.name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --warning: #f59e0b;
            --warning-glow: rgba(245, 158, 11, 0.4);
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

        .glitch-wrapper {
            position: relative;
            text-align: center;
            padding: 50px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 40px;
            max-width: 600px;
            width: 90%;
        }

        .error-code {
            font-size: clamp(80px, 20vw, 150px);
            font-weight: 800;
            color: var(--warning);
            position: relative;
            animation: glitch 1s infinite;
        }

        @keyframes glitch {
            0% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
            100% { transform: translate(0); }
        }

        .error-icon {
            font-size: 60px;
            color: var(--warning);
            margin-bottom: 20px;
            animation: rotate 4s infinite linear;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        h1 { font-size: 28px; margin-bottom: 15px; }
        p { color: #94a3b8; margin-bottom: 35px; line-height: 1.6; }

        .btn-bomba {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 32px;
            background: var(--warning);
            color: #000;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-bomba:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px var(--warning-glow);
            filter: brightness(1.1);
        }

        .circuit-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.05;
            background-image: radial-gradient(#f59e0b 1px, transparent 1px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body>
    <div class="circuit-bg"></div>
    <div class="glitch-wrapper">
        <div class="error-icon">
            <i class="fa-solid fa-gears"></i>
        </div>
        <div class="error-code">500</div>
        <h1>Server biroz charchadi</h1>
        <p>Tizimda kutilmagan xatolik yuz berdi. Bizning muhandislarimiz allaqachon buni tuzatish ustida ishlashmoqda. Iltimos, birozdan so'ng qayta urinib ko'ring.</p>
        
        <a href="{{ url('/') }}" class="btn-bomba">
            <i class="fa-solid fa-rotate-right"></i>
            Qayta yuklash
        </a>
    </div>
</body>
</html>
