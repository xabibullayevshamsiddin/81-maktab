<x-layouts.main :title="__('public.layout.privacy_policy').' | 81-IDUM'">
  @push('page_styles')
    <link rel="stylesheet" href="{{ app_public_asset('temp/css/legal-pages.css') }}?v={{ app_asset_version('temp/css/legal-pages.css') }}">
  @endpush
  <section class="news-hero news-hero-v2 terms-hero-section" id="home">
    <div class="container">
      <div class="news-hero-grid prime-reveal">
        <div class="news-hero-text">
          <span class="badge">{{ __('public.legal.privacy.badge') }}</span>
          <h1 class="js-split-text">{{ __('public.layout.privacy_policy') }}</h1>
          <p>{{ __('public.legal.privacy.hero_text') }}</p>
        </div>
        <div class="news-hero-visual">
          <div class="news-hero-3d-scene">
            <svg viewBox="0 0 350 300" fill="none" xmlns="http://www.w3.org/2000/svg">
              <ellipse cx="175" cy="270" rx="130" ry="16" fill="url(#shieldGroundGlow)" opacity="0.5"/>
              <path d="M175 40 L280 80 L280 160 C280 220 220 260 175 280 C130 260 70 220 70 160 L70 80 Z" fill="url(#shieldGrad)" stroke="rgba(120,200,255,0.3)" stroke-width="1.5"/>
              <path d="M175 40 L280 80 L280 160 C280 220 220 260 175 280 C130 260 70 220 70 160 L70 80 Z" fill="url(#shieldGlow)" opacity="0.3"/>
              <circle cx="175" cy="150" r="40" fill="rgba(56,189,248,0.15)" stroke="rgba(100,200,255,0.3)" stroke-width="1.5"/>
              <path d="M160 150 L172 162 L195 138" stroke="rgba(56,189,248,0.8)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
              <circle cx="175" cy="150" r="40" fill="rgba(56,189,248,0.1)"/>
              <rect x="130" y="200" width="90" height="8" rx="4" fill="rgba(200,220,255,0.15)"/>
              <rect x="145" y="215" width="60" height="6" rx="3" fill="rgba(200,220,255,0.1)"/>
              <circle cx="90" cy="60" r="1.5" fill="rgba(56,189,248,0.6)"><animate attributeName="cy" values="60;45;60" dur="3s" repeatCount="indefinite"/></circle>
              <circle cx="270" cy="70" r="2" fill="rgba(168,85,247,0.5)"><animate attributeName="cy" values="70;50;70" dur="4s" repeatCount="indefinite"/></circle>
              <circle cx="60" cy="180" r="1" fill="rgba(56,189,248,0.4)"><animate attributeName="cy" values="180;165;180" dur="3.5s" repeatCount="indefinite"/></circle>
              <circle cx="300" cy="190" r="1.5" fill="rgba(168,85,247,0.4)"><animate attributeName="cy" values="190;170;190" dur="2.8s" repeatCount="indefinite"/></circle>
              <defs>
                <linearGradient id="shieldGrad" x1="175" y1="40" x2="175" y2="280">
                  <stop offset="0%" stop-color="rgba(20,50,100,0.9)"/>
                  <stop offset="100%" stop-color="rgba(10,30,60,0.95)"/>
                </linearGradient>
                <linearGradient id="shieldGlow" x1="175" y1="40" x2="175" y2="280">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0.1)"/>
                </linearGradient>
                <radialGradient id="shieldGroundGlow" cx="0.5" cy="0.5" r="0.5">
                  <stop offset="0%" stop-color="rgba(56,189,248,0.3)"/>
                  <stop offset="100%" stop-color="rgba(168,85,247,0)"/>
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
      <div class="glass-section terms-document">
        
        <div class="document-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 2rem; margin-bottom: 2.5rem; text-align: center;">
          <h2 style="font-size: 2rem; color: var(--text-color); font-weight: 700; margin-bottom: 0.5rem;">{{ __('public.legal.privacy.sections_title') }}</h2>
          <p style="color: var(--text-secondary, #9ca3af); font-size: 1rem;">{{ __('public.legal.privacy.sections_subtitle') }}</p>
        </div>

        <div class="bento-grid prime-stagger" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(139, 92, 246, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #a78bfa; font-size: 1.2rem;">
              <i class="fas fa-database"></i>
            </div>
            <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.8rem;">{{ __('public.legal.privacy.collect_title') }}</h3>
            <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem;">
              {{ __('public.legal.privacy.collect_text') }}
            </p>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
             <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #60a5fa; font-size: 1.2rem;">
              <i class="fas fa-cogs"></i>
            </div>
            <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.8rem;">{{ __('public.legal.privacy.use_title') }}</h3>
            <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem;">
              {{ __('public.legal.privacy.use_text') }}
            </p>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
             <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #fbbf24; font-size: 1.2rem;">
              <i class="fas fa-user-secret"></i>
            </div>
            <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.8rem;">{{ __('public.legal.privacy.third_party_title') }}</h3>
            <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem;">
              {{ __('public.legal.privacy.third_party_text') }}
            </p>
          </article>
        </div>

        <div class="document-content" style="display: flex; flex-direction: column; gap: 1.5rem; margin-top: 2rem;">
          
          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; display: flex; align-items: flex-start; gap: 1.5rem;">
            <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #a78bfa; font-size: 1.2rem; flex-shrink: 0;">
              <i class="fas fa-info-circle"></i>
            </div>
            <div>
              <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.5rem;">{{ __('public.legal.privacy.overview_title') }}</h3>
              <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem; margin: 0;">
                {{ __('public.legal.privacy.overview_text') }}
              </p>
            </div>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; display: flex; align-items: flex-start; gap: 1.5rem;">
            <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #60a5fa; font-size: 1.2rem; flex-shrink: 0;">
              <i class="fas fa-cookie-bite"></i>
            </div>
            <div>
              <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.5rem;">{{ __('public.legal.privacy.cookies_title') }}</h3>
              <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem; margin: 0;">
                {{ __('public.legal.privacy.cookies_text') }}
              </p>
            </div>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; border-radius: 16px; display: flex; align-items: flex-start; gap: 1.5rem;">
            <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); display: flex; align-items: center; justify-content: center; color: #a78bfa; font-size: 1.2rem; flex-shrink: 0;">
              <i class="fas fa-users"></i>
            </div>
            <div>
              <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.5rem;">{{ __('public.legal.privacy.family_title') }}</h3>
              <p style="color: var(--text-secondary, #9ca3af); line-height: 1.6; font-size: 0.95rem; margin: 0;">
                {{ __('public.legal.privacy.family_text') }}
              </p>
            </div>
          </article>

          <article class="bento-item prime-glow-hover" style="padding: 1.5rem; border-radius: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
              <div style="width: 45px; height: 45px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #34d399; font-size: 1.2rem; flex-shrink: 0;">
                <i class="fas fa-envelope"></i>
              </div>
              <div>
                <h3 style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 0.3rem;">{{ __('public.legal.privacy.contact_title') }}</h3>
                <p style="color: var(--text-secondary, #9ca3af); font-size: 0.95rem; margin: 0;">
                  {{ __('public.legal.privacy.contact_text') }}
                </p>
              </div>
            </div>
            <a href="{{ route('contact') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 0.8rem 1.5rem; border-radius: 12px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; border: none; white-space: nowrap;">
              <span>{{ __('public.legal.privacy.contact_button') }}</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </article>
        </div>

      </div>
    </section>
  </main>
</x-loyouts.main>
