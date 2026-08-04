<x-layouts.main :title="__('public.layout.terms').' | 81-IDUM'">
  <section class="news-hero news-hero-v2" id="home">
    <div class="container">
      <div class="news-hero-grid prime-reveal">
        <div class="news-hero-text">
          <span class="badge">{{ __('public.legal.terms.badge') }}</span>
          <h1 class="js-split-text">{{ __('public.layout.terms') }}</h1>
          <p>{{ __('public.legal.terms.hero_text') }}</p>
        </div>
        <div class="news-hero-visual">
          <div class="news-hero-3d-scene">
            <svg viewBox="0 0 350 300" fill="none" xmlns="http://www.w3.org/2000/svg">
              <ellipse cx="175" cy="270" rx="130" ry="16" fill="url(#docGroundGlow)" opacity="0.5"/>
              <rect x="80" y="40" width="190" height="230" rx="12" fill="url(#docGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1.5"/>
              <rect x="80" y="40" width="190" height="230" rx="12" fill="url(#docGlow)" opacity="0.3"/>
              <rect x="80" y="40" width="190" height="40" rx="12" fill="url(#docHeaderGrad)"/>
              <rect x="80" y="68" width="190" height="12" rx="0" fill="url(#docHeaderGrad)"/>
              <circle cx="110" cy="60" r="8" fill="rgba(168,85,247,0.4)"/>
              <circle cx="135" cy="60" r="8" fill="rgba(56,189,248,0.3)"/>
              <circle cx="160" cy="60" r="8" fill="rgba(168,85,247,0.2)"/>
              <rect x="110" y="100" width="130" height="8" rx="4" fill="rgba(56,189,248,0.35)"/>
              <rect x="110" y="118" width="110" height="6" rx="3" fill="rgba(200,220,255,0.18)"/>
              <rect x="110" y="132" width="120" height="6" rx="3" fill="rgba(200,220,255,0.14)"/>
              <rect x="110" y="146" width="100" height="6" rx="3" fill="rgba(200,220,255,0.1)"/>
              <rect x="110" y="170" width="80" height="25" rx="6" fill="rgba(168,85,247,0.12)" stroke="rgba(168,85,247,0.2)" stroke-width="0.5"/>
              <circle cx="125" cy="182" r="5" fill="rgba(168,85,247,0.25)"/>
              <rect x="138" y="177" width="40" height="4" rx="2" fill="rgba(200,220,255,0.12)"/>
              <rect x="138" y="185" width="30" height="3" rx="1.5" fill="rgba(200,220,255,0.08)"/>
              <rect x="110" y="205" width="130" height="4" rx="2" fill="rgba(200,220,255,0.1)"/>
              <rect x="110" y="217" width="120" height="4" rx="2" fill="rgba(200,220,255,0.08)"/>
              <rect x="110" y="229" width="110" height="4" rx="2" fill="rgba(200,220,255,0.06)"/>
              <rect x="110" y="241" width="100" height="4" rx="2" fill="rgba(200,220,255,0.05)"/>
              <circle cx="80" cy="30" r="1.5" fill="rgba(56,189,248,0.6)"><animate attributeName="cy" values="30;15;30" dur="3s" repeatCount="indefinite"/></circle>
              <circle cx="280" cy="40" r="2" fill="rgba(168,85,247,0.5)"><animate attributeName="cy" values="40;20;40" dur="4s" repeatCount="indefinite"/></circle>
              <circle cx="60" cy="160" r="1" fill="rgba(56,189,248,0.4)"><animate attributeName="cy" values="160;145;160" dur="3.5s" repeatCount="indefinite"/></circle>
              <circle cx="300" cy="170" r="1.5" fill="rgba(168,85,247,0.4)"><animate attributeName="cy" values="170;150;170" dur="2.8s" repeatCount="indefinite"/></circle>
              <defs>
                <linearGradient id="docGrad" x1="80" y1="40" x2="270" y2="270">
                  <stop offset="0%" stop-color="rgba(15,40,80,0.9)"/>
                  <stop offset="100%" stop-color="rgba(10,25,55,0.95)"/>
                </linearGradient>
                <linearGradient id="docGlow" x1="175" y1="40" x2="175" y2="270">
                  <stop offset="0%" stop-color="rgba(168,85,247,0.3)"/>
                  <stop offset="100%" stop-color="rgba(56,189,248,0.1)"/>
                </linearGradient>
                <linearGradient id="docHeaderGrad" x1="80" y1="40" x2="270" y2="80">
                  <stop offset="0%" stop-color="rgba(100,60,180,0.7)"/>
                  <stop offset="100%" stop-color="rgba(60,40,120,0.8)"/>
                </linearGradient>
                <radialGradient id="docGroundGlow" cx="0.5" cy="0.5" r="0.5">
                  <stop offset="0%" stop-color="rgba(168,85,247,0.3)"/>
                  <stop offset="100%" stop-color="rgba(56,189,248,0)"/>
                </radialGradient>
              </defs>
            </svg>
            <div class="news-hero-glow"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <main style="padding: 4rem 0;">
    <section class="container prime-reveal">
      <div class="glass-section" style="max-width: 900px; margin: 0 auto; padding: 3rem; border-radius: 24px; background: rgba(30, 30, 35, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2); backdrop-filter: blur(20px);">
        
        <div class="document-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 2rem; margin-bottom: 2.5rem; text-align: center;">
          <h2 style="font-size: 2rem; color: var(--text-color); font-weight: 700; margin-bottom: 0.5rem;">{{ __('public.legal.terms.sections_title') }}</h2>
          <p style="color: var(--text-secondary, #9ca3af); font-size: 1rem;">{{ __('public.legal.terms.updated') }}</p>
        </div>

        <div class="bento-grid prime-stagger" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(99, 102, 241, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #818cf8; font-size: 1.2rem;">
              <i class="fas fa-check-circle"></i>
            </div>
            <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.8rem;">{{ __('public.legal.terms.general_title') }}</h3>
            <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem;">
              {{ __('public.legal.terms.general_text') }}
            </p>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
             <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #f87171; font-size: 1.2rem;">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.8rem;">{{ __('public.legal.terms.duties_title') }}</h3>
            <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem;">
              {{ __('public.legal.terms.duties_text') }}
            </p>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
             <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #34d399; font-size: 1.2rem;">
              <i class="fas fa-lock"></i>
            </div>
            <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.8rem;">{{ __('public.legal.terms.security_title') }}</h3>
            <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem;">
              {{ __('public.legal.terms.security_text') }}
            </p>
          </article>
        </div>

        <div class="document-content" style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 2rem;">
          
          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; display: flex; align-items: flex-start; gap: 1.5rem;">
            <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(99, 102, 241, 0.1); display: flex; align-items: center; justify-content: center; color: #818cf8; font-size: 1.2rem; flex-shrink: 0;">
              <i class="fas fa-info-circle"></i>
            </div>
            <div>
              <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.5rem;">{{ __('public.legal.terms.extra_title') }}</h3>
              <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem; margin: 0;">
                {{ __('public.legal.terms.extra_text') }}
              </p>
            </div>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; display: flex; align-items: flex-start; gap: 1.5rem;">
            <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #fbbf24; font-size: 1.2rem; flex-shrink: 0;">
              <i class="fas fa-edit"></i>
            </div>
            <div>
              <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.5rem;">{{ __('public.legal.terms.changes_title') }}</h3>
              <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem; margin: 0;">
                {{ __('public.legal.terms.changes_text') }}
              </p>
            </div>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
              <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #34d399; font-size: 1.2rem; flex-shrink: 0;">
                <i class="fas fa-envelope"></i>
              </div>
              <div>
                <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.3rem;">{{ __('public.legal.terms.contact_title') }}</h3>
                <p style="color: var(--text-secondary, #9ca3af); font-size: 0.95rem; margin: 0;">
                  {{ __('public.legal.terms.contact_text') }}
                </p>
              </div>
            </div>
            <a href="{{ route('contact') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; border: none; white-space: nowrap;">
              <span>{{ __('public.legal.terms.contact_button') }}</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </article>
        </div>

      </div>
    </section>
  </main>
</x-loyouts.main>
