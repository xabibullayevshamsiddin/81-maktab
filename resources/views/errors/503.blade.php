<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Texnik xizmat ko'rsatish | {{ config('app.name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.4);
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

        .maintenance-card {
            text-align: center;
            padding: 50px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 40px;
            max-width: 600px;
            width: 90%;
            position: relative;
        }

        .error-code {
            font-size: 100px;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--accent);
            opacity: 0.8;
        }

        .worker-icon {
            font-size: 70px;
            color: #60a5fa;
            margin-bottom: 25px;
            display: inline-block;
            animation: hammer 1s infinite alternate ease-in-out;
        }

        @keyframes hammer {
            from { transform: rotate(-10deg); }
            to { transform: rotate(20deg); }
        }

        h1 { font-size: 28px; margin-bottom: 15px; }
        p { color: #94a3b8; margin-bottom: 35px; line-height: 1.6; }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 40px;
        }

        .progress-fill {
            width: 70%;
            height: 100%;
            background: linear-gradient(90deg, var(--accent), #60a5fa);
            border-radius: 10px;
            animation: progressAnim 3s infinite ease-in-out;
        }

        @keyframes progressAnim {
            0% { width: 10%; }
            50% { width: 90%; }
            100% { width: 10%; }
        }

        .btn-bomba {
            padding: 16px 35px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid var(--accent);
            color: #fff;
            text-decoration: none;
            border-radius: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-bomba:hover {
            background: var(--accent);
            box-shadow: 0 10px 25px var(--accent-glow);
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <div class="worker-icon">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>
        <div class="error-code">503</div>
        <h1>Sayt yangilanmoqda</h1>
        <p>Biz hozirda saytni yanada yaxshiroq qilish uchun texnik ishlar olib bormoqdamiz. Tez orada qaytamiz va yanada ko'proq imkoniyatlarni taqdim etamiz.</p>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        
        <a href="javascript:location.reload()" class="btn-bomba">
            <i class="fa-solid fa-arrows-rotate"></i>
            Yana bir bor tekshirish
        </a>
    </div>
</body>
</html>
