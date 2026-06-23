/**
 * 81-IDUM — Auth Character Controller v5
 * Karakter karta chap yonida, tashqarida turadi.
 * Karta balandligi o'zgarsa → karakter ham kattalashadi.
 * Mobilda (≤850px) umuman ko'rinmaydi.
 */
(function () {
  'use strict';

  /* ═══════════════════════════════════════════════════════════════
     SVG — TIKKA TURGAN O'QUVCHI
     viewBox="0 0 120 300"  (keng va baland)
  ═══════════════════════════════════════════════════════════════ */
  var CHAR_SVG = `<svg class="auth-char" id="auth-char-svg"
     viewBox="0 0 120 300"
     width="120" height="300"
     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <defs>
    <radialGradient id="cFace" cx="50%" cy="38%" r="58%">
      <stop offset="0%"   stop-color="#fef3c7"/>
      <stop offset="100%" stop-color="#fde68a"/>
    </radialGradient>
    <radialGradient id="cEye" cx="38%" cy="32%" r="62%">
      <stop offset="0%"   stop-color="#60a5fa"/>
      <stop offset="100%" stop-color="#1d4ed8"/>
    </radialGradient>
    <linearGradient id="cPant" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#1e3a8a"/>
      <stop offset="100%" stop-color="#1e1b4b"/>
    </linearGradient>
    <linearGradient id="cShirt" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"   stop-color="#2563eb"/>
      <stop offset="100%" stop-color="#1565c0"/>
    </linearGradient>
    <filter id="cSh">
      <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#0002"/>
    </filter>
  </defs>

  <!-- ── YER SOYASI ─────────────────────────────────────────── -->
  <ellipse cx="60" cy="297" rx="30" ry="5" fill="#00000012"/>

  <!-- ══════════════════════════════════
       PORTFEL (chap qo'lda)
  ══════════════════════════════════ -->
  <g id="ch-bag">
    <!-- Tana -->
    <rect x="4" y="162" width="32" height="38" rx="7"
          fill="#92400e" filter="url(#cSh)"/>
    <!-- Qopqoq -->
    <rect x="4" y="162" width="32" height="14" rx="6" fill="#a16207"/>
    <!-- Qopqoq cheti -->
    <rect x="4" y="174" width="32" height="2" fill="#78350f" opacity="0.4"/>
    <!-- Tutqich -->
    <path d="M14 162 Q20 153 28 162"
          fill="none" stroke="#78350f" stroke-width="3.5"
          stroke-linecap="round"/>
    <!-- Qulf -->
    <rect x="16" y="175" width="10" height="8" rx="2.5" fill="#f59e0b"/>
    <circle cx="21" cy="179" r="2" fill="#92400e"/>
    <!-- Chiziqlar -->
    <line x1="8"  y1="180" x2="33" y2="180" stroke="#78350f" stroke-width="0.8" opacity="0.4"/>
    <line x1="8"  y1="186" x2="33" y2="186" stroke="#78350f" stroke-width="0.8" opacity="0.35"/>
    <line x1="8"  y1="192" x2="33" y2="192" stroke="#78350f" stroke-width="0.8" opacity="0.3"/>
  </g>

  <!-- ══════════════════════════════════
       OYOQLAR
  ══════════════════════════════════ -->
  <!-- Chap oyoq -->
  <path d="M43 212 Q42 250 41 280 Q41 288 50 290 Q56 290 57 282 Q58 256 58 220"
        fill="url(#cPant)"/>
  <!-- O'ng oyoq -->
  <path d="M77 212 Q78 250 79 280 Q79 288 70 290 Q64 290 63 282 Q62 256 62 220"
        fill="url(#cPant)"/>

  <!-- Poyabzallar -->
  <!-- Chap -->
  <ellipse cx="47" cy="290" rx="18" ry="7.5" fill="#1e1b4b"/>
  <ellipse cx="43" cy="289" rx="11" ry="4.5" fill="#2d2b6b" opacity="0.7"/>
  <rect x="32" y="285" width="22" height="5" rx="2.5" fill="#111827" opacity="0.5"/>
  <!-- O'ng -->
  <ellipse cx="73" cy="290" rx="18" ry="7.5" fill="#1e1b4b"/>
  <ellipse cx="69" cy="289" rx="11" ry="4.5" fill="#2d2b6b" opacity="0.7"/>
  <rect x="66" y="285" width="22" height="5" rx="2.5" fill="#111827" opacity="0.5"/>

  <!-- ══════════════════════════════════
       BADAN (KO'YLAK)
  ══════════════════════════════════ -->
  <!-- Asosiy tana -->
  <path d="M32 122 Q26 128 25 142 L24 214 Q60 220 96 214 L95 142 Q94 128 88 122 Z"
        fill="url(#cShirt)"/>

  <!-- Ko'ylak yoqa (oq) -->
  <path d="M52 122 Q60 134 68 122" fill="white" opacity="0.95"/>
  <polygon points="52,122 48,136 60,130 72,136 68,122"
           fill="white" opacity="0.35"/>

  <!-- Ko'ylak chok chizig'i -->
  <line x1="60" y1="134" x2="60" y2="212"
        stroke="white" stroke-width="0.7"
        stroke-dasharray="5 7" opacity="0.15"/>

  <!-- Tugmalar -->
  <circle cx="60" cy="142" r="2.2" fill="white" opacity="0.55"/>
  <circle cx="60" cy="154" r="2.2" fill="white" opacity="0.48"/>
  <circle cx="60" cy="166" r="2.2" fill="white" opacity="0.40"/>

  <!-- "81" logosi -->
  <rect x="44" y="178" width="32" height="20" rx="5"
        fill="white" opacity="0.14"/>
  <text x="60" y="192" text-anchor="middle" font-size="11"
        font-weight="900" fill="white" opacity="0.85"
        font-family="'Outfit',Arial,sans-serif">81</text>

  <!-- ── CHAP YEN (portfel qo'l) ── -->
  <path d="M32 122 Q18 130 14 150 Q12 165 17 174
           Q22 178 26 172 Q28 160 30 147 L33 132 Z"
         fill="url(#cShirt)"/>
  <!-- Qo'l chap -->
  <ellipse cx="14" cy="176" rx="9.5" ry="7"
           fill="#fde68a" transform="rotate(-22 14 176)"/>
  <!-- Barmoqlar -->
  <path d="M7 174 Q5 169 8 168"   stroke="#f59e0b" stroke-width="1.6"
        fill="none" stroke-linecap="round" opacity="0.8"/>
  <path d="M7 178 Q4 173 6 172"   stroke="#f59e0b" stroke-width="1.6"
        fill="none" stroke-linecap="round" opacity="0.75"/>

  <!-- ── O'NG YEN (ko'tarilgan / salomlash) ── -->
  <g id="ch-arm-r">
    <path d="M88 122 Q104 126 112 140 Q118 154 112 162
             Q107 166 103 160 Q99 148 96 136 L90 128 Z"
           fill="url(#cShirt)"/>
    <!-- Qo'l o'ng -->
    <ellipse cx="111" cy="161" rx="10" ry="7"
             fill="#fde68a" transform="rotate(24 111 161)"/>
    <!-- Barmoqlar -->
    <path d="M118 157 Q122 153 120 151" stroke="#f59e0b" stroke-width="1.6"
          fill="none" stroke-linecap="round" opacity="0.85"/>
    <path d="M119 162 Q123 158 121 156" stroke="#f59e0b" stroke-width="1.6"
          fill="none" stroke-linecap="round" opacity="0.80"/>
    <path d="M117 167 Q120 163 119 161" stroke="#f59e0b" stroke-width="1.6"
          fill="none" stroke-linecap="round" opacity="0.75"/>
  </g>

  <!-- Bo'yin -->
  <rect x="52" y="110" width="16" height="15" rx="7" fill="#fde68a"/>

  <!-- ══════════════════════════════════
       BOSH
  ══════════════════════════════════ -->
  <g id="ch-head" style="transform-origin:60px 75px">

    <!-- Quloqlar -->
    <ellipse cx="25" cy="78" rx="7.5" ry="9"   fill="#fde68a"/>
    <ellipse cx="95" cy="78" rx="7.5" ry="9"   fill="#fde68a"/>
    <ellipse cx="25" cy="78" rx="4"   ry="5.5" fill="#f59e0b" opacity="0.4"/>
    <ellipse cx="95" cy="78" rx="4"   ry="5.5" fill="#f59e0b" opacity="0.4"/>

    <!-- Bosh -->
    <ellipse cx="60" cy="72" rx="36" ry="35" fill="url(#cFace)"/>

    <!-- SOCH -->
    <path d="M26 64 Q24 38 44 27 Q60 18 76 27 Q96 38 94 64"
          fill="#92400e"/>
    <!-- Soch tolalari -->
    <path d="M44 27 Q48 17 52 27" stroke="#7c3aed" stroke-width="2.8"
          fill="none" stroke-linecap="round" opacity="0.9"/>
    <path d="M60 21 Q63 11 67 22" stroke="#7c3aed" stroke-width="2.8"
          fill="none" stroke-linecap="round" opacity="0.9"/>
    <path d="M74 27 Q78 17 80 27" stroke="#7c3aed" stroke-width="2.3"
          fill="none" stroke-linecap="round" opacity="0.75"/>
    <!-- Yon chiziq -->
    <path d="M27 60 Q26 45 32 36"
          stroke="#78350f" stroke-width="1.5"
          fill="none" stroke-linecap="round" opacity="0.45"/>

    <!-- ── QOSHLAR ── -->
    <g id="ch-brow-normal">
      <path d="M36 56 Q47 50 57 53" stroke="#78350f" stroke-width="2.6"
            fill="none" stroke-linecap="round"/>
      <path d="M63 53 Q73 50 84 56" stroke="#78350f" stroke-width="2.6"
            fill="none" stroke-linecap="round"/>
    </g>
    <g id="ch-brow-sad" style="opacity:0">
      <path d="M36 53 Q47 58 57 56" stroke="#78350f" stroke-width="2.6"
            fill="none" stroke-linecap="round"/>
      <path d="M63 56 Q73 58 84 53" stroke="#78350f" stroke-width="2.6"
            fill="none" stroke-linecap="round"/>
    </g>
    <g id="ch-brow-happy" style="opacity:0">
      <path d="M35 51 Q47 44 57 49" stroke="#78350f" stroke-width="2.6"
            fill="none" stroke-linecap="round"/>
      <path d="M63 49 Q73 44 85 51" stroke="#78350f" stroke-width="2.6"
            fill="none" stroke-linecap="round"/>
    </g>
    <g id="ch-brow-think" style="opacity:0">
      <path d="M36 56 Q47 50 57 53" stroke="#78350f" stroke-width="2.6"
            fill="none" stroke-linecap="round"/>
      <path d="M63 48 Q73 43 84 50" stroke="#78350f" stroke-width="2.6"
            fill="none" stroke-linecap="round"/>
    </g>

    <!-- ── CHAP KO'Z ── -->
    <g id="ch-eye-l">
      <ellipse cx="48" cy="74" rx="10" ry="9.5" fill="white"/>
      <ellipse cx="48" cy="74" rx="7.5" ry="7.5" fill="url(#cEye)"/>
      <ellipse id="ch-pupil-l" cx="48" cy="74" rx="4"   ry="4"   fill="#1e1b4b"/>
      <ellipse cx="50" cy="72" rx="1.8" ry="1.6" fill="white" opacity="0.95"/>
      <!-- Kirpiklar -->
      <path d="M38 68 Q39.5 63 41.5 67"  stroke="#78350f" stroke-width="1.4"
            fill="none" stroke-linecap="round" opacity="0.5"/>
      <path d="M48 65 Q48.5 60 50 65"    stroke="#78350f" stroke-width="1.4"
            fill="none" stroke-linecap="round" opacity="0.5"/>
      <path d="M57.5 68 Q59 63 58.5 67"  stroke="#78350f" stroke-width="1.4"
            fill="none" stroke-linecap="round" opacity="0.5"/>
    </g>

    <!-- ── O'NG KO'Z ── -->
    <g id="ch-eye-r">
      <ellipse cx="72" cy="74" rx="10" ry="9.5" fill="white"/>
      <ellipse cx="72" cy="74" rx="7.5" ry="7.5" fill="url(#cEye)"/>
      <ellipse id="ch-pupil-r" cx="72" cy="74" rx="4"   ry="4"   fill="#1e1b4b"/>
      <ellipse cx="74" cy="72" rx="1.8" ry="1.6" fill="white" opacity="0.95"/>
      <!-- Kirpiklar -->
      <path d="M62 68 Q63.5 63 65.5 67"  stroke="#78350f" stroke-width="1.4"
            fill="none" stroke-linecap="round" opacity="0.5"/>
      <path d="M72 65 Q72.5 60 74 65"    stroke="#78350f" stroke-width="1.4"
            fill="none" stroke-linecap="round" opacity="0.5"/>
      <path d="M81.5 68 Q83 63 82.5 67"  stroke="#78350f" stroke-width="1.4"
            fill="none" stroke-linecap="round" opacity="0.5"/>
    </g>

    <!-- ── KO'Z QOPQOQLARI (peek) ── -->
    <g id="ch-cover-l" style="opacity:0; transition:opacity 0.22s ease">
      <ellipse cx="48" cy="74" rx="10" ry="9.5" fill="#fde68a"/>
      <path d="M38 74 Q48 83 58 74" stroke="#d97706"
            stroke-width="2.5" fill="none" stroke-linecap="round"/>
    </g>
    <g id="ch-cover-r" style="opacity:0; transition:opacity 0.22s ease">
      <ellipse cx="72" cy="74" rx="10" ry="9.5" fill="#fde68a"/>
      <path d="M62 74 Q72 83 82 74" stroke="#d97706"
            stroke-width="2.5" fill="none" stroke-linecap="round"/>
    </g>

    <!-- Burun -->
    <ellipse cx="60" cy="86" rx="4" ry="2.8" fill="#f59e0b" opacity="0.45"/>

    <!-- ── OG'IZ ── -->
    <path id="ch-mouth-smile" d="M47 96 Q60 107 73 96"
          stroke="#92400e" stroke-width="2.8" fill="none" stroke-linecap="round"/>
    <path id="ch-mouth-sad"   d="M48 103 Q60 95 72 103"
          stroke="#92400e" stroke-width="2.8" fill="none" stroke-linecap="round"
          style="opacity:0"/>
    <ellipse id="ch-mouth-wow" cx="60" cy="99" rx="8" ry="7"
             fill="#92400e" style="opacity:0"/>
    <path id="ch-mouth-think" d="M53 97 Q60 100 67 97"
          stroke="#92400e" stroke-width="2.2" fill="none" stroke-linecap="round"
          style="opacity:0"/>

    <!-- Yonoqlar -->
    <ellipse cx="33" cy="89" rx="9" ry="5.5" fill="#fca5a5" opacity="0.38"/>
    <ellipse cx="87" cy="89" rx="9" ry="5.5" fill="#fca5a5" opacity="0.38"/>

    <!-- O'ylash belgilari -->
    <g id="ch-think-dots" style="opacity:0; transition:opacity 0.3s ease">
      <circle cx="93" cy="56" r="3.5" fill="#cbd5e1"/>
      <circle cx="103" cy="44" r="5"   fill="#94a3b8"/>
      <circle cx="115" cy="30" r="7.5" fill="#64748b"/>
      <text x="115" y="33.5" text-anchor="middle" font-size="9"
            fill="white" font-family="Arial,sans-serif" font-weight="800">?</text>
    </g>

  </g><!-- /ch-head -->
</svg>`;

  /* ═══════════════════════════════════════════════════════════════
     XABARLAR
  ═══════════════════════════════════════════════════════════════ */
  var M = {
    idleLogin:   "Salom! 👋 Keling tizimga kiramiz!",
    idleReg:     "Salom! 🎓 Ro'yxatdan o'tamiz!",
    emailFocus:  "📧 Email manzilingiz?",
    pwFocus:     "🙈 Ko'zlarimni yumdim, yozing!",
    pwSeen:      "👀 Parolni ko'ryapsizmi? 😄",
    nameFocus:   "✏️ Ismingiz nima?",
    lastFocus:   "✏️ Familiyangiz?",
    phoneFocus:  "📱 Telefon raqamingiz?",
    gradeFocus:  "🏫 Sinfingizni tanlang",
    pwWeak:      "🔓 Juda zaif! Kuchaytiring!",
    pwFair:      "🔑 Yaxshiroq, davom eting",
    pwGood:      "✅ Yaxshi parol!",
    pwStrong:    "🔐 Super kuchli! 💪",
    shake:       "❌ Xato bor, tekshiring!",
    shakeServer: "⚠️ Xato ma'lumot! Qaytadan!",
    nod:         "✅ Zo'r, to'g'ri!",
    happy:       "🎉 Yuborilmoqda! Omad! 🚀",
    pwMatch:     "✅ Parollar mos keldi!",
    pwMismatch:  "❌ Parollar bir xil emas!",
  };

  /* ═══════════════════════════════════════════════════════════════
     ASOSIY KARAKTER SINFI
  ═══════════════════════════════════════════════════════════════ */
  function AuthChar(card, mode) {
    this.card      = card;
    this.mode      = mode || 'login';
    this._bubTimer = null;
    this._isPeek   = false;
    this._ro       = null;   /* ResizeObserver */
    this._build();
    this._bind();
    this._watchSize();
  }

  /* ── DOM QURILMASI ─────────────────────────────────────────── */
  AuthChar.prototype._build = function () {
    var card = this.card;

    /* 1. Karta atrofida .auth-card-wrapper wrapper yaratamiz */
    var wrapper = document.createElement('div');
    wrapper.className = 'auth-card-wrapper auth-card-wrapper--' + this.mode;
    card.parentNode.insertBefore(wrapper, card);
    wrapper.appendChild(card);
    this.wrapper = wrapper;

    /* 2. Karakter outer — wrapper ichida, lekin karta tashqarisida (chapda) */
    var outer = document.createElement('div');
    outer.className = 'auth-char-outer';
    this.outer = outer;

    /* SVG */
    var tmp = document.createElement('div');
    tmp.innerHTML = CHAR_SVG.trim();
    var svgEl = tmp.firstElementChild;
    outer.appendChild(svgEl);

    /* Bubble */
    var bub = document.createElement('div');
    bub.className = 'auth-char-bubble';
    outer.appendChild(bub);

    wrapper.appendChild(outer);

    /* Eski icon yashirish */
    var icon = card.querySelector('.signin-card-icon, .register-card-icon');
    if (icon) icon.style.display = 'none';

    /* Referanslar */
    this.svg       = svgEl;
    this.bubble    = bub;
    this.head      = this.svg.querySelector('#ch-head');
    this.eyeL      = this.svg.querySelector('#ch-eye-l');
    this.eyeR      = this.svg.querySelector('#ch-eye-r');
    this.covL      = this.svg.querySelector('#ch-cover-l');
    this.covR      = this.svg.querySelector('#ch-cover-r');
    this.pupL      = this.svg.querySelector('#ch-pupil-l');
    this.pupR      = this.svg.querySelector('#ch-pupil-r');
    this.browN     = this.svg.querySelector('#ch-brow-normal');
    this.browS     = this.svg.querySelector('#ch-brow-sad');
    this.browH     = this.svg.querySelector('#ch-brow-happy');
    this.browT     = this.svg.querySelector('#ch-brow-think');
    this.mSmile    = this.svg.querySelector('#ch-mouth-smile');
    this.mSad      = this.svg.querySelector('#ch-mouth-sad');
    this.mWow      = this.svg.querySelector('#ch-mouth-wow');
    this.mThink    = this.svg.querySelector('#ch-mouth-think');
    this.thinkDots = this.svg.querySelector('#ch-think-dots');
    this.armR      = this.svg.querySelector('#ch-arm-r');

    /* Qo'l silkitish */
    setTimeout(this._waveArm.bind(this), 500);
    /* Boshlang'ich xabar */
    setTimeout(function () {
      this._say(this.mode === 'register' ? M.idleReg : M.idleLogin, 5000);
    }.bind(this), 800);
  };

  /* ── O'LCHAM KUZATUVCHI ────────────────────────────────────── */
  AuthChar.prototype._watchSize = function () {
    var self = this;

    function update() {
      /* Mobil/planshet → hech narsa qilma */
      if (window.innerWidth <= 850) return;

      var cardH  = self.card.offsetHeight;
      var winW   = window.innerWidth;

      /* Karakter balandligi: kartaning heighti ning 87.5% i, lekin min:300, max:580 */
      var charH;
      if (winW <= 1024) {
        charH = Math.min(320, Math.max(220, cardH * 0.65));
      } else {
        charH = Math.min(580, Math.max(300, cardH * 0.875));
      }

      /* CSS o'zgaruvchilari — global (:root) ga va wrapperga yoziladi */
      document.documentElement.style.setProperty('--char-height', charH + 'px');
      self.wrapper.style.setProperty('--char-height', charH + 'px');
      self.wrapper.classList.add('auth-card-wrapper--ready');

      /* SVG o'lchamlari inline tarzda beriladi (brauzer mosligi uchun) */
      if (self.svg) {
        self.svg.style.height = charH + 'px';
        self.svg.style.width  = (charH * 0.4) + 'px';
      }
    }

    /* Birinchi hisoblash */
    update();

    /* ResizeObserver — karta o'lchami o'zgarsak yangilash */
    if (typeof ResizeObserver !== 'undefined') {
      this._ro = new ResizeObserver(update);
      this._ro.observe(this.card);
    }

    /* Oyna o'lchami o'zgarganda ham */
    window.addEventListener('resize', update);
  };

  /* ── INPUTLARNI BOG'LASH ────────────────────────────────────── */
  AuthChar.prototype._bind = function () {
    var self = this;
    var card = this.card;
    var form = card.querySelector('form');
    if (!form) return;

    form.querySelectorAll('input, select').forEach(function (el) {
      el.addEventListener('focus',   function () { self._onFocus(el); });
      el.addEventListener('blur',    function () { self._onBlur(el); });
      el.addEventListener('input',   function () { self._onInput(el); });
      el.addEventListener('change',  function () { self._onInput(el); });
      el.addEventListener('invalid', function (e) {
        e.preventDefault();
        self._doShake(M.shake);
      });
    });

    /* Parolni ko'rish tugmasi */
    form.querySelectorAll('.pw-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var t = document.getElementById(btn.getAttribute('data-target'));
        if (!t) return;
        if (t.type === 'text') { self._unpeek(); self._say(M.pwSeen, 2000); }
        else                   { self._doPeek(); self._say(M.pwFocus, 0);   }
      });
    });

    form.addEventListener('submit', function () {
      if (!form.checkValidity()) self._doShake(M.shake);
      else                       self._doHappy();
    });

    /* Server-side xato */
    if (card.querySelector('.signin-alert, .register-alert')) {
      setTimeout(function () { self._doShake(M.shakeServer); }, 750);
    }
  };

  /* ── FOKUS ─────────────────────────────────────────────────── */
  AuthChar.prototype._onFocus = function (el) {
    var id = el.id || '', type = el.type, tag = el.tagName.toLowerCase();
    if (type === 'password') {
      this._doPeek(); this._say(M.pwFocus, 0);
    } else if (type === 'email') {
      this._unpeek(); this._look(3.5, -1); this._say(M.emailFocus, 0);
    } else if (id.includes('first')) {
      this._unpeek(); this._look(-3, -1); this._say(M.nameFocus, 0);
    } else if (id.includes('last')) {
      this._unpeek(); this._look(-3, -1); this._say(M.lastFocus, 0);
    } else if (type === 'tel') {
      this._unpeek(); this._look(3.5, 0); this._say(M.phoneFocus, 0);
    } else if (tag === 'select') {
      this._unpeek(); this._look(2, 1.5); this._say(M.gradeFocus, 0);
    } else {
      this._unpeek(); this._look(0, 0); this._hideBubble();
    }
  };

  AuthChar.prototype._onBlur = function (el) {
    this._unpeek(); this._look(0, 0);
    if (!el.value) { this._hideBubble(); return; }
    if (!el.validity.valid) this._doShake(M.shake);
    else                    this._doNod();
  };

  AuthChar.prototype._onInput = function (el) {
    var id = el.id || '', type = el.type;
    if (type === 'password' && !id.includes('confirm')) {
      var str = this._strength(el.value);
      this._renderBar(el, str.level);
      if (el.value) {
        this._say(M['pw' + str.cap], 0);
        if (str.score <= 1)     this._setBrows('sad');
        else if (str.score >= 4){ this._setBrows('happy'); setTimeout(function(){ this._setBrows('normal'); }.bind(this), 900); }
        else                     this._setBrows('normal');
      }
    }
    if (id.includes('confirm')) {
      var main = this.card.querySelector('#reg-password, input[name="password"]');
      if (main && el.value) {
        if (el.value === main.value) { this._say(M.pwMatch, 0);    this._doNod(); }
        else                          { this._say(M.pwMismatch, 0); this._setBrows('sad'); }
      }
    }
  };

  /* ── ANIMATSIYA HOLATLARI ──────────────────────────────────── */

  AuthChar.prototype._waveArm = function () {
    if (!this.armR) return;
    var arm = this.armR, t = 0;
    (function wave() {
      if (t >= 9) { arm.style.transform = ''; return; }
      arm.style.transform = 'rotate(' + Math.sin(t * 1.3) * 20 + 'deg)';
      arm.style.transformOrigin = '88px 122px';
      t += 0.18;
      requestAnimationFrame(wave);
    })();
  };

  AuthChar.prototype._doPeek = function () {
    if (this._isPeek) return;
    this._isPeek = true;
    this.covL.style.opacity = '1'; this.covR.style.opacity = '1';
    this.eyeL.style.opacity = '0'; this.eyeR.style.opacity = '0';
    this._setBrows('happy');
  };
  AuthChar.prototype._unpeek = function () {
    this._isPeek = false;
    this.covL.style.opacity = '0'; this.covR.style.opacity = '0';
    this.eyeL.style.opacity = '1'; this.eyeR.style.opacity = '1';
    this._setBrows('normal');
  };

  AuthChar.prototype._doShake = function (msg) {
    var self = this;
    this.head.classList.remove('ch-shake');
    void this.head.offsetWidth;
    this.head.classList.add('ch-shake');
    this._setMouth('sad'); this._setBrows('sad');
    if (msg) this._say(msg, 2800);
    setTimeout(function () {
      self.head.classList.remove('ch-shake');
      self._setMouth('smile'); self._setBrows('normal');
    }, 860);
  };

  AuthChar.prototype._doNod = function () {
    var self = this;
    this.head.classList.remove('ch-nod');
    void this.head.offsetWidth;
    this.head.classList.add('ch-nod');
    this._say(M.nod, 1600);
    setTimeout(function () { self.head.classList.remove('ch-nod'); }, 700);
  };

  AuthChar.prototype._doHappy = function () {
    var self = this;
    this._unpeek();
    this.svg.classList.remove('ch-happy');
    void this.svg.offsetWidth;
    this.svg.classList.add('ch-happy');
    this._setMouth('wow'); this._setBrows('happy');
    this._say(M.happy, 2200);
    setTimeout(function () {
      self.svg.classList.remove('ch-happy');
      self._setMouth('smile'); self._setBrows('normal');
    }, 950);
  };

  AuthChar.prototype._look = function (dx, dy) {
    dy = dy || 0;
    if (this.pupL) this.pupL.style.transform = 'translate('+dx+'px,'+dy+'px)';
    if (this.pupR) this.pupR.style.transform = 'translate('+dx+'px,'+dy+'px)';
  };

  AuthChar.prototype._setBrows = function (s) {
    if (this.browN) this.browN.style.opacity = s==='normal'?'1':'0';
    if (this.browS) this.browS.style.opacity = s==='sad'   ?'1':'0';
    if (this.browH) this.browH.style.opacity = s==='happy' ?'1':'0';
    if (this.browT) this.browT.style.opacity = s==='think' ?'1':'0';
  };

  AuthChar.prototype._setMouth = function (s) {
    if (this.mSmile) this.mSmile.style.opacity = s==='smile'?'1':'0';
    if (this.mSad)   this.mSad.style.opacity   = s==='sad'  ?'1':'0';
    if (this.mWow)   this.mWow.style.opacity   = s==='wow'  ?'1':'0';
    if (this.mThink) this.mThink.style.opacity = s==='think'?'1':'0';
  };

  AuthChar.prototype._say = function (txt, dur) {
    clearTimeout(this._bubTimer);
    this.bubble.textContent = txt;
    this.bubble.classList.add('auth-char-bubble--show');
    if (dur > 0) this._bubTimer = setTimeout(this._hideBubble.bind(this), dur);
  };
  AuthChar.prototype._hideBubble = function () {
    clearTimeout(this._bubTimer);
    this.bubble.classList.remove('auth-char-bubble--show');
  };

  AuthChar.prototype._strength = function (v) {
    if (!v) return { level:'weak', score:0, cap:'Weak' };
    var s = 0;
    if (v.length >= 8)          s++;
    if (v.length >= 12)         s++;
    if (/[A-Z]/.test(v))        s++;
    if (/[0-9]/.test(v))        s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    var t = [{level:'weak',cap:'Weak'},{level:'weak',cap:'Weak'},
             {level:'fair',cap:'Fair'},{level:'good',cap:'Good'},
             {level:'good',cap:'Good'},{level:'strong',cap:'Strong'}];
    return Object.assign({score:s}, t[Math.min(s,5)]);
  };

  AuthChar.prototype._renderBar = function (el, level) {
    var field = el.closest('.register-field,.signin-field') ||
                el.parentElement.parentElement;
    if (!field) return;
    var bar = field.querySelector('.auth-pw-strength__bar');
    if (!bar) {
      var w = document.createElement('div');
      w.className = 'auth-pw-strength';
      w.innerHTML = '<div class="auth-pw-strength__bar"></div>';
      field.appendChild(w);
      bar = w.querySelector('.auth-pw-strength__bar');
    }
    bar.className = 'auth-pw-strength__bar auth-pw-strength__bar--' + level;
  };

  /* ═══════════════════════════════════════════════════════════════
     KEYFRAME CSS
  ═══════════════════════════════════════════════════════════════ */
  function injectKF() {
    if (document.getElementById('auth-char-kf')) return;
    var s = document.createElement('style');
    s.id = 'auth-char-kf';
    s.textContent = `
@keyframes chShake{0%{transform:rotate(0)}12%{transform:rotate(-14deg)}
26%{transform:rotate(14deg)}40%{transform:rotate(-10deg)}
55%{transform:rotate(8deg)}70%{transform:rotate(-5deg)}
85%{transform:rotate(3deg)}100%{transform:rotate(0)}}
.ch-shake{animation:chShake .78s cubic-bezier(.36,.07,.19,.97);
          transform-origin:60px 120px;}

@keyframes chNod{0%{transform:rotate(0)translateY(0)}
20%{transform:rotate(9deg)translateY(2px)}
45%{transform:rotate(-5deg)translateY(-1px)}
70%{transform:rotate(4deg)translateY(1px)}
100%{transform:rotate(0)translateY(0)}}
.ch-nod{animation:chNod .62s ease;transform-origin:60px 120px;}

@keyframes chHappy{0%{transform:translateY(0)scale(1)}
25%{transform:translateY(-22px)scale(1.07,.92)}
48%{transform:translateY(0)scale(.94,1.07)}
68%{transform:translateY(-10px)scale(1.04,.96)}
86%{transform:translateY(0)scale(.98,1.02)}
100%{transform:translateY(0)scale(1)}}
.ch-happy{animation:chHappy .85s cubic-bezier(.34,1.56,.64,1);}`;
    document.head.appendChild(s);
  }

  /* ═══════════════════════════════════════════════════════════════
     ISHGA TUSHIRISH
  ═══════════════════════════════════════════════════════════════ */
  function init() {
    /* Mobil/planshet bo'lsa hech narsa qilma */
    if (window.innerWidth <= 850) return;

    injectKF();
    var signinCard   = document.querySelector('.signin-section .signin-card');
    var registerCard = document.querySelector('.register-section .register-card');
    if (signinCard)   new AuthChar(signinCard,   'login');
    if (registerCard) new AuthChar(registerCard, 'register');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
