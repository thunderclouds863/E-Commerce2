/* Guspart Autoshop — Shared UI Interactions & Accessibility Helpers */

function toggleMenu() {
    const nav = document.querySelector('.site-nav, nav');
    const hamburger = document.querySelector('.hamburger');
    if (!nav) return;
    const isOpen = nav.classList.toggle('show');
    if (hamburger) {
        hamburger.classList.toggle('is-open', isOpen);
        hamburger.setAttribute('aria-expanded', String(isOpen));
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.site-header');
    if (header) {
        const onScroll = () => {
            header.classList.toggle('scrolled', window.scrollY > 12);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Touch / click support for user dropdown menu
    document.querySelectorAll('.user-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const menu = trigger.closest('.user-menu');
            if (menu) {
                const open = menu.classList.toggle('open');
                trigger.setAttribute('aria-expanded', String(open));
            }
        });
    });

    // Password visibility toggles
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.getAttribute('data-target');
            const input = targetId ? document.getElementById(targetId) : btn.previousElementSibling;
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            btn.setAttribute('aria-pressed', String(isHidden));
        });
    });

    // Form submit loading state (opt-in via data-loading-text)
    document.querySelectorAll('form[data-loading-text]').forEach(function (form) {
        form.addEventListener('submit', function () {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.classList.add('is-loading');
                btn.disabled = true;
                btn.textContent = form.getAttribute('data-loading-text') || 'Processing...';
            }
        });
    });

    // Close menus/modals on outside click
    document.addEventListener('click', function (e) {
        document.querySelectorAll('.user-menu.open').forEach(function (menu) {
            if (!menu.contains(e.target)) {
                menu.classList.remove('open');
                const trigger = menu.querySelector('.user-trigger');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            }
        });

        const nav = document.querySelector('.site-nav.show');
        const hamburger = document.querySelector('.hamburger');
        if (nav && !nav.contains(e.target) && hamburger && !hamburger.contains(e.target)) {
            nav.classList.remove('show');
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });

    // Close modals and menus on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.popup.active').forEach(function (popup) {
            popup.classList.remove('active');
        });
        document.querySelectorAll('.user-menu.open').forEach(function (menu) {
            menu.classList.remove('open');
        });
        const nav = document.querySelector('.site-nav.show');
        const hamburger = document.querySelector('.hamburger');
        if (nav) nav.classList.remove('show');
        if (hamburger) {
            hamburger.classList.remove('is-open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
    });

    // Safe smooth scrolling for valid in-page anchors
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (!href || href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
                const nav = document.querySelector('.site-nav.show');
                const hamburger = document.querySelector('.hamburger');
                if (nav) nav.classList.remove('show');
                if (hamburger) {
                    hamburger.classList.remove('is-open');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            }
        });
    });
});
