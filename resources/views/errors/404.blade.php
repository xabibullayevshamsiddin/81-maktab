<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Sahifa topilmadi | {{ config('app.name') }}</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.4);
            --bg-dark: #0f172a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            perspective: 1000px;
        }

        /* Animated Background Mesh */
        .mesh-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(circle at 20% 30%, rgba(79, 70, 229, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(139, 92, 246, 0.15) 0%, transparent 50%);
            animation: meshMove 20s infinite alternate ease-in-out;
        }

        @keyframes meshMove {
            0% { transform: scale(1) rotate(0deg); }
            100% { transform: scale(1.2) rotate(5deg); }
        }

        .error-container {
            text-align: center;
            z-index: 1;
            padding: 40px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            width: 90%;
            animation: containerFadeIn 0.8s cubic-bezier(0.23, 1, 0.32, 1) both;
        }

        @keyframes containerFadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .error-code {
            font-size: clamp(80px, 20vw, 150px);
            font-weight: 800;
            line-height: 1;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
        }

        .error-code::after {
            content: '404';
            position: absolute;
            top: 0;
            left: 0;
            z-index: -1;
            filter: blur(20px);
            opacity: 0.5;
            background: linear-gradient(135deg, var(--primary) 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .error-icon {
            font-size: 50px;
            margin-bottom: 20px;
            color: var(--primary);
            animation: float 3s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #f8fafc;
        }

        p {
            font-size: 16px;
            color: #94a3b8;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .btn-bomba {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 32px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: #fff;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            box-shadow: 0 10px 20px var(--primary-glow);
        }

        .btn-bomba:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px var(--primary-glow);
            filter: brightness(1.1);
        }

        .btn-bomba i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .btn-bomba:hover i {
            transform: translateX(-5px);
        }

        /* Floating particles */
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="mesh-bg"></div>
    
    <div class="error-container">
        <div class="error-icon">
            <i class="fa-solid fa-satellite-dish"></i>
        </div>
        <div class="error-code">404</div>
        <h1>Voy! Sahifa koinotda yo'qoldi</h1>
        <p>Siz izlayotgan sahifa manzili o'zgargan yoki u butunlay o'chirib tashlangan bo'lishi mumkin. Xavotir olmang, biz sizga yo'lni ko'rsatamiz.</p>
        
        <a href="{{ url('/') }}" class="btn-bomba">
            <i class="fa-solid fa-house"></i>
            Bosh sahifaga qaytish
        </a>
    </div>

    <script>
        // Create random floating particles
        for(let i = 0; i < 20; i++) {
            let p = document.createElement('div');
            p.className = 'particle';
            let size = Math.random() * 5 + 2;
            p.style.width = size + 'px';
            p.style.height = size + 'px';
            p.style.left = Math.random() * 100 + 'vw';
            p.style.top = Math.random() * 100 + 'vh';
            p.style.opacity = Math.random() * 0.5;
            document.body.appendChild(p);
            
            animateParticle(p);
        }

        function animateParticle(el) {
            let x = (Math.random() - 0.5) * 100;
            let y = (Math.random() - 0.5) * 100;
            let duration = Math.random() * 10000 + 5000;
            
            el.animate([
                { transform: 'translate(0, 0)' },
                { transform: `translate(${x}px, ${y}px)` }
            ], {
                duration: duration,
                iterations: Infinity,
                direction: 'alternate',
                easing: 'linear'
            });
        }
    </script>
</body>
</html>
