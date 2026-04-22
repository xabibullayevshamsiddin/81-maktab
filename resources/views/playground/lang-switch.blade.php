<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Language Switch Animation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        .letter {
            display: inline-block;
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0;
            filter: blur(10px);
        }

        .letter.active {
            opacity: 1;
            transform: translate(0, 0) !important;
            filter: blur(0);
        }

        .letter.out {
            opacity: 0;
            filter: blur(10px);
            transform: scale(0.5);
        }

        /* Apple-style Easing */
        .apple-ease {
            transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
        }
    </style>
</head>
<body class="bg-[#050505] text-white min-h-screen flex flex-col items-center justify-center overflow-hidden">

    <!-- Global Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-600/10 blur-[120px] rounded-full"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-indigo-600/10 blur-[120px] rounded-full"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-2xl px-6 text-center">
        
        <!-- Animated Text Container -->
        <div id="text-container" class="text-6xl md:text-8xl font-extrabold tracking-tight h-32 flex items-center justify-center flex-wrap gap-x-2">
            <!-- Letters will be injected here -->
        </div>

        <!-- Controls -->
        <div class="mt-20 flex flex-col items-center gap-6">
            <p class="text-gray-400 text-sm tracking-widest uppercase">Select Language</p>
            
            <div class="flex items-center gap-4 bg-white/5 p-1.5 rounded-full border border-white/10 backdrop-blur-xl">
                <button 
                    onclick="switchLang('en')" 
                    id="btn-en"
                    class="px-8 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 apple-ease bg-white text-black shadow-lg"
                >
                    English
                </button>
                <button 
                    onclick="switchLang('uz')" 
                    id="btn-uz"
                    class="px-8 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 apple-ease hover:bg-white/10 text-gray-400"
                >
                    O'zbek
                </button>
            </div>
            
            <div class="mt-8">
                <p id="sub-text" class="text-gray-500 text-lg md:text-xl font-medium transition-opacity duration-500 italic">
                    Experience the future of education.
                </p>
            </div>
        </div>
    </div>

    <script>
        const texts = {
            en: {
                main: "Infinite Potential",
                sub: "Experience the future of education."
            },
            uz: {
                main: "Cheksiz Imkoniyat",
                sub: "Ta'lim kelajagini his eting."
            }
        };

        let currentLang = 'en';
        const container = document.getElementById('text-container');
        const subText = document.getElementById('sub-text');

        function getRandomPosition() {
            const range = 400; // Pixels to move from
            const positions = [
                { x: 0, y: -range }, // Top
                { x: 0, y: range },  // Bottom
                { x: -range, y: 0 }, // Left
                { x: range, y: 0 }   // Right
            ];
            return positions[Math.floor(Math.random() * positions.length)];
        }

        function createLetters(text) {
            // Clear existing letters with exit animation
            const oldLetters = container.querySelectorAll('.letter');
            oldLetters.forEach((l, i) => {
                const pos = getRandomPosition();
                l.classList.remove('active');
                l.style.transform = `translate(${pos.x}px, ${pos.y}px)`;
                setTimeout(() => l.remove(), 800);
            });

            // Wait a bit then inject new letters
            setTimeout(() => {
                container.innerHTML = '';
                const words = text.split(' ');
                
                words.forEach((word, wordIndex) => {
                    const wordSpan = document.createElement('span');
                    wordSpan.className = 'whitespace-nowrap';
                    
                    [...word].forEach((char, charIndex) => {
                        const span = document.createElement('span');
                        span.textContent = char;
                        span.className = 'letter';
                        
                        // Set random starting position
                        const startPos = getRandomPosition();
                        span.style.transform = `translate(${startPos.x}px, ${startPos.y}px)`;
                        
                        wordSpan.appendChild(span);
                        
                        // Stagger the activation
                        const delay = (wordIndex * 2 + charIndex) * 45;
                        setTimeout(() => {
                            span.classList.add('active');
                        }, delay);
                    });
                    
                    container.appendChild(wordSpan);
                    if (wordIndex < words.length - 1) {
                        const space = document.createElement('span');
                        space.innerHTML = '&nbsp;';
                        container.appendChild(space);
                    }
                });
            }, oldLetters.length > 0 ? 300 : 0);
        }

        function switchLang(lang) {
            if (lang === currentLang) return;
            currentLang = lang;

            // Update Buttons
            const btnEn = document.getElementById('btn-en');
            const btnUz = document.getElementById('btn-uz');
            
            if (lang === 'en') {
                btnEn.classList.add('bg-white', 'text-black', 'shadow-lg');
                btnEn.classList.remove('hover:bg-white/10', 'text-gray-400');
                btnUz.classList.remove('bg-white', 'text-black', 'shadow-lg');
                btnUz.classList.add('hover:bg-white/10', 'text-gray-400');
            } else {
                btnUz.classList.add('bg-white', 'text-black', 'shadow-lg');
                btnUz.classList.remove('hover:bg-white/10', 'text-gray-400');
                btnEn.classList.remove('bg-white', 'text-black', 'shadow-lg');
                btnEn.classList.add('hover:bg-white/10', 'text-gray-400');
            }

            // Animate Main Text
            createLetters(texts[lang].main);

            // Animate Sub Text
            subText.style.opacity = '0';
            setTimeout(() => {
                subText.textContent = texts[lang].sub;
                subText.style.opacity = '1';
            }, 600);
        }

        // Initialize
        window.addEventListener('DOMContentLoaded', () => {
            createLetters(texts.en.main);
        });
    </script>
</body>
</html>
