<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('public.errors.503_page_title') }} | {{ config('app.name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">

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
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
            </svg>
        </div>
        <div class="error-code">503</div>
        <h1>{{ __('public.errors.503_title') }}</h1>
        <p>{{ __('public.errors.503_text') }}</p>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        
        <a href="javascript:location.reload()" class="btn-bomba">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 4v6h-6"></path>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
            {{ __('public.errors.503_retry') }}
        </a>
    </div>
</body>
</html>
