(function () {
    'use strict';
    const config = window.rwConfig;
    if (!config || !config.enabled || window.__recipeWarningLoaded) return;
    const ttl = Math.min(604800000, Math.max(1, Number(config.ttl) || 604800000));
    const strings = config.strings || {};
    const ua = navigator.userAgent;
    const mobileFirefox = /FxiOS\//i.test(ua) || (/Firefox\//i.test(ua) && /Android/i.test(ua));
    if (!mobileFirefox) return;
    window.__recipeWarningLoaded = true;

    function hasRecipe(value) {
        const pending = [value];
        let visited = 0;
        while (pending.length && visited < 10000) {
            const node = pending.pop();
            visited += 1;
            if (!node || typeof node !== 'object') continue;
            const types = [].concat(node['@type'] || []);
            if (types.some(type => typeof type === 'string' && /(^|[/#])Recipe$/.test(type))) return true;
            const values = Object.values(node);
            for (let i = values.length - 1; i >= 0 && pending.length < 10000; i -= 1) {
                if (values[i] && typeof values[i] === 'object') pending.push(values[i]);
            }
        }
        return false;
    }
    function isRecipe() {
        if (config.recipeHint) return true;
        if (document.querySelector('[itemtype~="https://schema.org/Recipe"], [itemtype~="http://schema.org/Recipe"], .wprm-recipe-container, .tasty-recipes, .wp-block-wpzoom-recipe-card-block-recipe-card')) return true;
        const scripts = document.querySelectorAll('script[type="application/ld+json"]');
        let scanned = 0;
        for (let i = 0; i < scripts.length && i < 64; i += 1) {
            const text = scripts[i].textContent;
            if (text.length > 262144) continue;
            if (scanned + text.length > 1048576) continue;
            scanned += text.length;
            try { if (hasRecipe(JSON.parse(text))) return true; } catch (_) { /* Ignore malformed metadata and inspect the next block. */ }
        }
        return false;
    }
    function bypassed() {
        try {
            const stored = localStorage.getItem(config.storageKey);
            const expires = Number(stored);
            const now = Date.now();
            const valid = Number.isFinite(expires) && expires > now && expires <= now + ttl;
            if (stored !== null && !valid) localStorage.removeItem(config.storageKey);
            return valid;
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
        label.textContent = strings.label || 'A note for Firefox readers';
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
        copy.textContent = strings.copy || 'Copy link for another browser';
        const bypass = document.createElement('button');
        bypass.type = 'button';
        bypass.className = 'rw-secondary';
        bypass.textContent = strings.bypass || 'I’ve disabled summaries';
        const proceed = document.createElement('button');
        proceed.type = 'button';
        proceed.className = 'rw-link';
        proceed.textContent = strings.proceed || 'Continue to the original recipe';
        const note = document.createElement('p');
        note.className = 'rw-note';
        note.textContent = strings.note || 'Your choice is remembered on this device for 7 days when you confirm summaries are disabled.';
        const status = document.createElement('p');
        status.className = 'rw-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        const fallback = document.createElement('input');
        fallback.type = 'text';
        fallback.readOnly = true;
        fallback.hidden = true;
        fallback.setAttribute('aria-label', strings.linkLabel || 'Recipe link to copy');
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
                status.textContent = strings.copied || 'Link copied. Paste it into another browser.';
            } catch (_) {
                fallback.hidden = false;
                fallback.value = window.location.href;
                fallback.focus();
                fallback.select();
                status.textContent = strings.copyFallback || 'Copy the selected link, then paste it into another browser.';
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
            if (!controls.length) return;
            const current = controls.indexOf(document.activeElement);
            const next = (current + (event.shiftKey ? -1 : 1) + controls.length) % controls.length;
            event.preventDefault();
            controls[next].focus();
        });
        actions.append(copy, bypass, proceed);
        inner.append(label, title, message, actions, note, status, fallback);
        dialog.append(inner);
        document.body.append(dialog);
        if (typeof dialog.showModal !== 'function') { dialog.remove(); return; }
        try { dialog.showModal(); } catch (_) { dialog.remove(); return; }
        // Respect theme colors when they provide readable contrast.
        const style = getComputedStyle(dialog);
        function luminance(color) {
            const match = color.match(/^rgba?\(([^)]+)\)$/);
            if (!match) return null;
            const channels = match[1].split(/[ ,/]+/).map(Number);
            if (channels.length < 3 || channels.some(Number.isNaN) || (channels.length > 3 && channels[3] < 1)) return null;
            const linear = channels.slice(0, 3).map(value => { value /= 255; return value <= .04045 ? value / 12.92 : Math.pow((value + .055) / 1.055, 2.4); });
            return linear[0] * .2126 + linear[1] * .7152 + linear[2] * .0722;
        }
        const ink = luminance(style.color);
        const paper = luminance(style.backgroundColor);
        if (ink === null || paper === null || (Math.max(ink, paper) + .05) / (Math.min(ink, paper) + .05) < 4.5) {
            dialog.style.setProperty('--rw-ink', '#20251f');
            dialog.style.setProperty('--rw-paper', '#faf8f2');
        }
        copy.focus();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
    else start();
})();
