function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
}

function initRevealAnimations() {
    const reduced = prefersReducedMotion();
    const isSmallScreen = () => window.matchMedia && window.matchMedia('(max-width: 768px)').matches;

    const explicitReveal = Array.from(document.querySelectorAll('[data-reveal]'));
    explicitReveal.forEach((el) => el.classList.add('al-reveal'));

    const staggerGroups = Array.from(document.querySelectorAll('[data-stagger]'));
    staggerGroups.forEach((group) => {
        const stepSeconds = Number.parseFloat(group.getAttribute('data-stagger') || '0.1');
        const items = Array.from(group.querySelectorAll('[data-stagger-item]'));
        items.forEach((item, index) => {
            item.classList.add('al-reveal');
            item.style.transitionDelay = `${Math.max(0, index) * Math.max(0, stepSeconds)}s`;
        });
    });

    // En pantallas pequeñas, revelar inmediatamente los ítems de Productos y Galería
    if (isSmallScreen()) {
        const fastSelectors = [
            '#productos [data-stagger-item]',
            '#galeria [data-stagger-item]',
            '#productos .al-reveal',
            '#galeria .al-reveal',
        ].join(', ');
        const fastItems = Array.from(document.querySelectorAll(fastSelectors));
        fastItems.forEach((el) => {
            el.classList.add('is-revealed');
            el.style.transitionDelay = '0s';
            el.style.transitionDuration = '120ms';
        });
    }

    const revealEls = Array.from(document.querySelectorAll('.al-reveal'));

    if (reduced) {
        revealEls.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -10% 0px' },
    );

    revealEls.forEach((el) => observer.observe(el));
}

function initCountUp() {
    const reduced = prefersReducedMotion();

    const candidates = Array.from(document.querySelectorAll('[data-countup], [data-countup-auto], .js-countup'));
    if (candidates.length === 0) return;

    const parseTextValue = (text) => {
        const match = String(text).match(/(-?\d+(?:[.,]\d+)?)/);
        if (!match) return null;

        const rawNumber = match[1];
        const startIndex = match.index ?? 0;
        const endIndex = startIndex + rawNumber.length;

        const normalized = rawNumber.replace(',', '.');
        const target = Number.parseFloat(normalized);
        if (!Number.isFinite(target)) return null;

        const decimals = (normalized.split('.')[1] || '').length;
        const prefix = String(text).slice(0, startIndex);
        const suffix = String(text).slice(endIndex);

        return { target, decimals, prefix, suffix };
    };

    const formatNumber = (value, decimals) => {
        return decimals > 0 ? value.toFixed(decimals) : String(Math.round(value));
    };

    const animate = (el) => {
        if (el.dataset.counted === '1') return;

        const spec =
            el.dataset.countup !== undefined
                ? (() => {
                      const raw = String(el.dataset.countup || '').trim();
                      const target = Number.parseFloat(raw.replace(',', '.'));
                      if (!Number.isFinite(target)) return null;
                      const decimals = (raw.split(/[.,]/)[1] || '').length;
                      const prefix = String(el.dataset.countupPrefix || '');
                      const suffix = String(el.dataset.countupSuffix || '');
                      return { target, decimals, prefix, suffix };
                  })()
                : parseTextValue(el.textContent || '');

        if (!spec) return;

        el.dataset.counted = '1';
        el.classList.add('tabular-nums');

        if (reduced) {
            el.textContent = `${spec.prefix}${formatNumber(spec.target, spec.decimals)}${spec.suffix}`;
            return;
        }

        const durationMs = Number.parseInt(el.dataset.countupDuration || '900', 10);
        const start = 0;
        const end = spec.target;

        const startedAt = performance.now();
        const step = (now) => {
            const elapsed = now - startedAt;
            const t = Math.min(1, Math.max(0, elapsed / Math.max(1, durationMs)));
            const eased = easeOutCubic(t);
            const current = start + (end - start) * eased;
            el.textContent = `${spec.prefix}${formatNumber(current, spec.decimals)}${spec.suffix}`;
            if (t < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                animate(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.35, rootMargin: '0px 0px -10% 0px' },
    );

    candidates.forEach((el) => observer.observe(el));
}

function initButtonIconMicroInteractions() {
    const buttons = Array.from(document.querySelectorAll('[data-icon-shift]'));
    buttons.forEach((btn) => btn.classList.add('al-icon-shift'));
}

document.addEventListener('DOMContentLoaded', () => {
    initRevealAnimations();
    initCountUp();
    initButtonIconMicroInteractions();
});
