(function () {
    'use strict';
    const config = window.rwConfig;
    if (!config || !config.enabled) return;
    const ttl = Number(config.ttl) || 604800000;
    const ua = navigator.userAgent;
    const mobileFirefox = /FxiOS\//i.test(ua) || (/Firefox\//i.test(ua) && /Android/i.test(ua));
    if (!mobileFirefox) return;

    function hasRecipe(value) {
        if (!value || typeof value !== 'object') return false;
        const types = [].concat(value['@type'] || []);
        if (types.some(type => typeof type === 'string' && /(^|[/#])Recipe$/.test(type))) return true;
        return Object.values(value).some(v => Array.isArray(v) ? v.some(hasRecipe) : hasRecipe(v));
    }
    function isRecipe() {
        if (config.recipeHint) return true;
        if (document.querySelector('[itemtype$="schema.org/Recipe"], .wprm-recipe-container, .tasty-recipes, .wp-block-wpzoom-recipe-card-block-recipe-card')) return true;
        return Array.from(document.querySelectorAll('script[type="application/ld+json"]')).some(script => {
            try { return hasRecipe(JSON.parse(script.textContent)); } catch (_) { return false; }
        });
    }
    function bypassed() {
        try {
            const expires = Number(localStorage.getItem(config.storageKey));
            return Number.isFinite(expires) && expires > Date.now() && expires <= Date.now() + ttl;
        } catch (_) { return false; }
    }
    function start() {
        if (!isRecipe() || bypassed() || document.getElementById('recipe-warning')) return;
        const previousFocus = document.activeElement;
        const dialog = document.createElement('dialog');
        dialog.id = 'recipe-warning';
        dialog.className = 'rw-dialog';
        dialog.setAttribute('aria-labelledby', 'rw-title');
        dialog.setAttribute('aria-describedby', 'rw-message');
        const inner = document.createElement('div');
        inner.className = 'rw-inner';
        const label = document.createElement('p');
        label.className = 'rw-label';
        label.textContent = 'A note for Firefox readers';
        const title = document.createElement('h2');
        title.id = 'rw-title';
        title.textContent = config.title;
        const message = document.createElement('p');
        message.id = 'rw-message';
        message.textContent = config.message;
        const actions = document.createElement('div');
        actions.className = 'rw-actions';
        const copy = document.createElement('button');
        copy.type = 'button';
        copy.className = 'rw-primary';
        copy.textContent = 'Copy link for another browser';
        const bypass = document.createElement('button');
        bypass.type = 'button';
        bypass.className = 'rw-secondary';
        bypass.textContent = 'I’ve disabled summaries';
        const proceed = document.createElement('button');
        proceed.type = 'button';
        proceed.className = 'rw-link';
        proceed.textContent = 'Continue to the original recipe';
        const note = document.createElement('p');
        note.className = 'rw-note';
        note.textContent = 'Your choice is remembered on this device for 7 days when you confirm summaries are disabled.';
        const status = document.createElement('p');
        status.className = 'rw-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        const fallback = document.createElement('input');
        fallback.type = 'text';
        fallback.readOnly = true;
        fallback.hidden = true;
        fallback.setAttribute('aria-label', 'Recipe link to copy');
        fallback.className = 'rw-url';
        function dismiss() {
            dialog.close();
            dialog.remove();
            if (previousFocus && previousFocus !== document.body && previousFocus.isConnected) previousFocus.focus();
            else {
                const target = document.querySelector('main h1, .entry-title, main');
                if (target) { const tabindex = target.getAttribute('tabindex'); target.setAttribute('tabindex', '-1'); target.focus({ preventScroll: true }); target.addEventListener('blur', () => tabindex === null ? target.removeAttribute('tabindex') : target.setAttribute('tabindex', tabindex), { once: true }); }
            }
        }
        copy.addEventListener('click', async () => {
            try {
                if (!navigator.clipboard || !window.isSecureContext) throw new Error('Clipboard unavailable');
                await navigator.clipboard.writeText(window.location.href);
                status.textContent = 'Link copied. Paste it into another browser.';
            } catch (_) {
                fallback.hidden = false;
                fallback.value = window.location.href;
                fallback.focus();
                fallback.select();
                status.textContent = 'Copy the selected link, then paste it into another browser.';
            }
        });
        bypass.addEventListener('click', () => {
            try { localStorage.setItem(config.storageKey, String(Date.now() + ttl)); } catch (_) { /* The current page can still be read when storage is unavailable. */ }
            dismiss();
        });
        proceed.addEventListener('click', dismiss);
        dialog.addEventListener('cancel', event => { event.preventDefault(); dismiss(); });
        dialog.addEventListener('keydown', event => {
            if (event.key !== 'Tab') return;
            const controls = Array.from(dialog.querySelectorAll('button, input')).filter(element => !element.hidden && !element.disabled);
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
        actions.append(copy, bypass, proceed);
        inner.append(label, title, message, actions, note, status, fallback);
        dialog.append(inner);
        document.body.append(dialog);
        if (typeof dialog.showModal !== 'function') { dialog.remove(); return; }
        dialog.showModal();
        copy.focus();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
})();
