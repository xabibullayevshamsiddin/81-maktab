<x-layouts.main :title="__('public.layout.terms').' | 81-IDUM'">
  <section class="news-hero" id="home">
    <div class="container">
      <div class="news-hero-content prime-reveal">
        <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: var(--text-color); border: 1px solid rgba(255, 255, 255, 0.2); padding: 6px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 500; display: inline-block; margin-bottom: 1rem;"><i class="fas fa-file-contract"></i> {{ __('public.legal.terms.badge') }}</span>
        <h1 class="js-split-text" style="font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 800; line-height: 1.1; margin-bottom: 1rem; background: var(--text-gradient, linear-gradient(135deg, #fff, #a5b4fc)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ __('public.layout.terms') }}</h1>
        <p style="font-size: 1.1rem; color: var(--text-secondary, #9ca3af); max-width: 600px; margin: 0 auto; line-height: 1.6;">{{ __('public.legal.terms.hero_text') }}</p>
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
