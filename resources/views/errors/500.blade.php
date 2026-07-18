<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('public.errors.500_page_title') }} | {{ config('app.name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --warning: #f59e0b;
            --warning-glow: rgba(245, 158, 11, 0.4);
            --bg-dark: #0f172a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-dark);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: auto; /* Allow scrolling if necessary */
            /* circuit background directly on body */
            background-image: radial-gradient(#f59e0b 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .glitch-wrapper {
            position: relative;
            text-align: center;
            padding: 50px;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 40px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 0 60px rgba(245, 158, 11, 0.08);
        }

        .error-code {
            font-size: clamp(80px, 20vw, 150px);
            font-weight: 800;
            color: var(--warning);
            display: block;
            text-shadow: 0 0 40px var(--warning-glow);
        }

        .error-icon {
            font-size: 60px;
            color: var(--warning);
            margin-bottom: 20px;
        }

        .error-icon svg {
            display: block;
            margin: 0 auto;
        }

        h1 { font-size: 28px; margin-bottom: 15px; font-weight: 600; }
        p { color: #94a3b8; margin-bottom: 35px; line-height: 1.6; font-size: 16px; max-width: 400px; margin-left: auto; margin-right: auto; }

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
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-bomba:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px var(--warning-glow);
            filter: brightness(1.1);
        }

        .btn-bomba svg {
            display: block;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="glitch-wrapper">
        <div class="error-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </div>
        <span class="error-code">500</span>
        <h1>{{ __('public.errors.500_title') }}</h1>
        <p>{{ __('public.errors.500_text') }}</p>
        
        <a href="{{ url('/') }}" class="btn-bomba">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 4v6h-6"></path>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
            {{ __('public.errors.500_reload') }}
        </a>
    </div>
</body>
</html>
