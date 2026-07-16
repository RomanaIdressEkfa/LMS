import './bootstrap';

import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Alpine.plugin(persist);

/**
 * Global language store (EN / BN), persisted to localStorage so the choice
 * sticks across page loads — mirrors the old Next.js LanguageProvider.
 * Bilingual content is rendered with data-en / data-bn spans that react to
 * `$store.lang.current`.
 */
Alpine.store('lang', {
  current: Alpine.$persist('en').as('lms_lang'),
  set(l) { this.current = l; },
  toggle() { this.current = this.current === 'en' ? 'bn' : 'en'; },
  get isBn() { return this.current === 'bn'; },
});

window.Alpine = Alpine;
Alpine.start();
