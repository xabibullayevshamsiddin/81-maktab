{{-- Markaziy tasdiqlash: data-confirm bilan formlar uchun (confirm-modal.js) --}}
<div id="prime-confirm-modal" class="prime-confirm prime-confirm--danger" role="presentation" aria-hidden="true">
  <div class="prime-confirm__backdrop" aria-hidden="true"></div>
  <div class="prime-confirm__dialog" role="dialog" aria-modal="true" aria-labelledby="prime-confirm-title">
    <div class="prime-confirm__icon" aria-hidden="true">
      <span class="prime-confirm__icon-inner">!</span>
    </div>
    <h2 id="prime-confirm-title" class="prime-confirm__title">Tasdiqlash</h2>
    <p class="prime-confirm__message" id="prime-confirm-message"></p>
    <div class="prime-confirm__actions">
      <button type="button" class="prime-confirm__btn prime-confirm__btn--ghost" data-prime-confirm-cancel>Bekor qilish</button>
      <button type="button" class="prime-confirm__btn prime-confirm__btn--danger" data-prime-confirm-ok>Ha, davom etish</button>
    </div>
  </div>
</div>
